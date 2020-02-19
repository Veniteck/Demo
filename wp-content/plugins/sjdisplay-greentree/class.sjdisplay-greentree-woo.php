<?php

class SJDisplay_Greentree_Woo
{
    private static $initiated = false;
    protected static $instance = null;
    public static $source = 'class.sjdisplay-greentree-woo';

    protected function __construct()
    {
    }

    public static function get_instance()
    {
        null === self::$instance and self::$instance = new self;
        return self::$instance;
    }

    public static function init()
    {
        if (!self::$initiated) {
            self::init_hooks();
        }
    }

    /*
     * Init all hooks and WP endpoints
     */
    private static function init_hooks()
    {
        self::$initiated = true;

        //enqueue scripts
        add_action('wp_enqueue_scripts', array('SJDisplay_Greentree_Woo', 'custom_enqueue_assets'));

        //enqueue admin scripts
        add_action('admin_enqueue_scripts', array('SJDisplay_Greentree_Woo', 'custom_enqueue_admin_assets'));

        //woo create order (send order to GT as order confirmation OR quote)
        add_action('woocommerce_checkout_update_order_meta', array('SJDisplay_Greentree_Woo', 'custom_checkout_update_order_meta'));

        //woo order thankyou page (send approval notifications)
        add_action('woocommerce_thankyou', array('SJDisplay_Greentree_Woo', 'custom_woocommerce_thankyou'), 30, 1);

        //woo order number filtering
        add_action('woocommerce_order_number', array('SJDisplay_Greentree_Woo', 'custom_woo_order_number'));

        //woo custom search fields for orders
        add_filter('woocommerce_shop_order_search_fields', array('SJDisplay_Greentree_Woo', 'custom_shop_order_search_fields'));

        //woo checkout field filter
        add_filter('woocommerce_checkout_fields', array('SJDisplay_Greentree_Woo', 'custom_checkout_fields'));

        //custom checkout form field values
        add_filter('woocommerce_checkout_get_value', array('SJDisplay_Greentree_Woo', 'custom_checkout_form_values'), 10, 2);

        //register custom order status and attach it
        SJDisplay_Greentree_Woo::register_custom_order_status();
        add_filter('wc_order_statuses', array('SJDisplay_Greentree_Woo', 'custom_order_status_integration'));

        //custom order admin info
        add_action('woocommerce_admin_order_data_after_order_details', array('SJDisplay_Greentree_Woo', 'custom_order_data_in_admin'));

        //custom meta boxes
        add_action('add_meta_boxes', array('SJDisplay_Greentree_Woo', 'custom_meta_boxes'));

        //before checkout form area manager store selection hook
        add_filter('woocommerce_before_checkout_billing_form', array('SJDisplay_Greentree_Woo', 'custom_before_checkout_billing_form'));

        //custom checkout processing (for purchase order validation and other pre-submit validation)
        add_filter('woocommerce_checkout_process', array('SJDisplay_Greentree_Woo', 'custom_checkout_process'));

        //custom woo checkout validation filtering
        add_filter('woocommerce_add_error', array('SJDisplay_Greentree_Woo', 'custom_woo_error_filter'));

        //custom woo general settings inputs (for store address data pulled from parent)
        add_filter('woocommerce_general_settings', array('SJDisplay_Greentree_Woo', 'custom_woo_general_settings'));

        //action hook for order view to output custom partial with packing slip data
        add_action('woocommerce_view_order', array('SJDisplay_Greentree_Woo', 'custom_my_account_view_order'), 10, 1);

        //action hook for order view pre template render to query latest order data from GT
        add_action('template_include', array('SJDisplay_Greentree_Woo', 'custom_my_account_view_order_sync'), 10, 1);

        //action hook for order view pre page render on WP/Woo order edit screen to query latest order data from GT
        add_action('current_screen', array('SJDisplay_Greentree_Woo', 'custom_woo_order_edit_order_sync'));

        //action hook for ACF data save
        add_action('acf/save_post', array('SJDisplay_Greentree_Woo', 'custom_acf_data_save'));
    }

    public static function custom_enqueue_assets()
    {
        wp_enqueue_script(
            'custom_frontend_js',
            SJDISPLAY_GREENTREE_ASSETS_URL . 'js/sj-greentree-front.js',
            array('jquery'),
            SJDISPLAY_GREENTREE_VERSION,
            true
        );
    }

    public static function custom_enqueue_admin_assets()
    {
        wp_enqueue_script(
            'custom_admin_js',
            SJDISPLAY_GREENTREE_ASSETS_URL . 'js/sj-greentree-admin.js',
            array('jquery'),
            SJDISPLAY_GREENTREE_VERSION,
            true
        );

        //inline scripts, for things like session vars set in the functions below
        if (isset($_SESSION['gt_order_admin_sync_error'])) {
            $gt_order_admin_sync_error = $_SESSION['gt_order_admin_sync_error'];
        } else {
            $gt_order_admin_sync_error = '';
        }
        wp_add_inline_script('custom_admin_js', 'var order_admin_sync_error="' . $gt_order_admin_sync_error . '";');
    }

    public static function register_custom_order_status()
    {
        register_post_status('wc-approval', array(
            'label' => 'Pending approval',
            'public' => true,
            'exclude_from_search' => false,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
        ));
    }

    public static function custom_order_status_integration($order_statuses)
    {
        $new_order_statuses = array();
        foreach ($order_statuses as $key => $status) {
            //default statuses passed through
            $new_order_statuses[$key] = $status;

            //insert 'Pending approval' custom status
            if ($key === 'wc-pending') {
                $new_order_statuses['wc-approval'] = 'Pending approval';
            }

            //rename 'On hold' to 'Being processed'
            if ($key == 'wc-on-hold') {
                $new_order_statuses['wc-on-hold'] = 'Being processed';
            }
        }
        return $new_order_statuses;
    }

    /*
     * Get product by SKU
     */
    public static function get_woo_product_by_sku($sku)
    {
        global $wpdb;

        $products_with_sku = $wpdb->get_results("SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='$sku'");

        if (!empty($products_with_sku)) {

            $post_id_array = array();
            foreach ($products_with_sku as $product) {
                $post_id_array[] = $product->post_id;
            }

            //double check posts exist
            $product_query = new WP_Query(
                array(
                    'post_type' => 'product',
                    'post__in' => $post_id_array,
                )
            );

            if (!empty($product_query->posts)) {
                return $product_query->posts;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /*
     * Custom checkout form values based on store_managers associated store data
     */
    public static function custom_checkout_form_values($value, $input)
    {
        //default return
        $return = $value;

        //get current user data and check if they are a store_manager
        $current_user_id = get_current_user_id();

        //if they are a store_manager, get the associated single store data
        if (
            SJDisplay_Greentree::is_user_manager($current_user_id, 'store_manager') &&
            !SJDisplay_Greentree::is_user_manager($current_user_id, 'area_manager')
        ) {
            $current_user_associated_stores = get_field('associated_stores', 'user_' . $current_user_id);

            //if they have associated stores, use the first one (store_manager should only have one anyway)
            if ($current_user_associated_stores) {
                //if array then get from first associated store
                if (is_array($current_user_associated_stores)) {
                    $current_user_associated_store = $current_user_associated_stores[0];
                } else {
                    $current_user_associated_store = $current_user_associated_stores;
                }

                //set mapped input values from first associated store
                if ($return_mapped = SJDisplay_Greentree_Woo::get_woo_mapped_store_acf_value($input, $current_user_associated_store)) {
                    $return = $return_mapped;
                }
            }
        } elseif (SJDisplay_Greentree::is_user_manager($current_user_id, 'area_manager')) {
            //if they are a area_manager, check to make sure they've selected a store to order for
            if (isset($_GET['store-selected']) && $_GET['store-selected'] != '') {
                $store_selected_code = esc_attr($_GET['store-selected']);
                $store_selected = SJDisplay_Greentree::get_customer_by_code($store_selected_code);
                if ($return_mapped = SJDisplay_Greentree_Woo::get_woo_mapped_store_acf_value($input, $store_selected[0]->ID)) {
                    $return = $return_mapped;
                }
            } else {
                //if store not selected, return empty for now
                $return = '';
            }
        }

        return $return;
    }

    /*
     * Filter default checkout form fields
     */
    public static function custom_checkout_fields($fields)
    {
        $fields['billing']['billing_first_name']['label'] = 'Order Contact Name';
        $fields['billing']['billing_first_name']['maxlength'] = 30;
        $fields['billing']['billing_last_name']['required'] = false;
        $fields['billing']['billing_phone']['required'] = false;
        $fields['shipping']['shipping_last_name']['required'] = false;
        return $fields;
    }

    /*
     * Helper function to set address input field value for checkout
     * Used in custom_checkout_form_values() if store_manager is making order to get the store posts data
     * Also used in custom_checkout_form_values() if area_manager making order for single store to get store posts data
     * Data is mapped to fields from the store posts ACFs
     */
    public static function get_woo_mapped_store_acf_value($input, $store_post_id)
    {
        $value = false;

        //billing values taken from store data by default
        switch ($input) {
            case 'billing_first_name':
                //if this is an order amendment, grab original order first and last details
                if (isset($_SESSION['order_amendment'])) {
                    $value = $_SESSION['order_amendment']['order_first_name'];
                } else {
                    $value = '';
                }
                break;
            case 'billing_last_name':
                //if this is an order amendment, grab original order first and last details
                if (isset($_SESSION['order_amendment'])) {
                    $value = $_SESSION['order_amendment']['order_last_name'];
                } else {
                    $value = '';
                }
                break;
            case 'billing_company':
                $value = get_the_title($store_post_id);
                break;
            case 'billing_address_1':
                $value = get_field('address', $store_post_id);
                break;
            case 'billing_address_2':
                $value = get_field('address_2', $store_post_id);
                break;
            case 'billing_city':
                $value = get_field('suburb', $store_post_id);
                break;
            case 'billing_state':
                $value = get_field('state', $store_post_id);
                break;
            case 'billing_postcode':
                $value = get_field('postcode', $store_post_id);
                break;
            case 'billing_phone':
                $value = get_field('phone_bh', $store_post_id);
                break;
            case 'order_comments':
                if (isset($_SESSION['order_amendment'])) {
                    $value = $_SESSION['order_amendment']['order_note'];
                } else {
                    $value = '';
                }
                break;
        }

        return $value;
    }

    /*
     * Checkout process hook for order pre-submit validation logic
     */
    public static function custom_checkout_process()
    {
        //if no PO number supplied but current user requires PO number
        if (!$_POST['order_po_number'] && SJDisplay_Greentree::get_customer_po_number_required(get_current_user_id())) {
            wc_add_notice('Your order requires a PO Number', 'error');
        }
    }

    /*
     * Custom woo error/notice filtering to make changes to things like default Woo error notices
     */
    public static function custom_woo_error_filter($error)
    {
        //overwrite some default errors
        if ($error == '<strong>Billing Order Contact Name</strong> is a required field.') {
            $error = '<strong>Order Contact Name</strong> is a required field.';
        }

        return $error;
    }

    /*
     * Fired after woocommerce_checkout_create_order hook, after order is initially well and truly saved
     * And during the AJAX checkout function, perfect spot to send order to GT
     */
    public static function custom_checkout_update_order_meta($order_id)
    {
        //default status set on order creation
        $creation_gt_status = 'Order Confirmation';
        $creation_woo_status = 'wc-on-hold';

        //if approval system active
        if (get_option('greentree_approval_enabled')) {

            //if ordered by store_manager
            if (
                SJDisplay_Greentree::is_user_manager(get_current_user_id(), 'store_manager') &&
                !SJDisplay_Greentree::is_user_manager(get_current_user_id(), 'area_manager')
            ) {
                $creation_gt_status = 'Quote';
                $creation_woo_status = 'wc-approval';
            } else {
                //save the store id ordered for (if approval manager ordering for a store)
                if (isset($_POST['store_id']) && $_POST['store_id'] != '') {
                    update_post_meta($order_id, 'store_ordered_for', esc_attr($_POST['store_id']));
                }

                //if the user is a 'group manager' (area manager and store manager), create the order as a 'quote' in GT
                if (
                    SJDisplay_Greentree::is_user_manager(get_current_user_id(), 'store_manager') &&
                    SJDisplay_Greentree::is_user_manager(get_current_user_id(), 'area_manager')
                ) {
                    $creation_gt_status = 'Quote';
                    update_post_meta($order_id, 'store_ordered_for', esc_attr($_POST['store_id']));
                }
            }
        }

        //PO number logic
        if (isset($_POST['order_po_number']) && $_POST['order_po_number'] != '') {
            update_post_meta($order_id, 'order_po_number', esc_attr($_POST['order_po_number']));
        }

        //creation of order, send request to GreenTree immediately
        SJDisplay_Greentree_Woo::send_order_to_greentree($order_id, $creation_woo_status, $creation_gt_status);

        return $order_id;
    }

    /*
     * Woo order thank-you page hook
     * Note: this is useful for any functionality that must run last during order creation journey
     */
    public static function custom_woocommerce_thankyou($order_id)
    {
        //get order object
        $order = wc_get_order($order_id);

        //check if approval system enabled and current user is store manager
        if (get_option('greentree_approval_enabled')) {
            //if user is only a store manager
            if (
                SJDisplay_Greentree::is_user_manager(get_current_user_id(), 'store_manager')
            ) {
                $order->update_status('approval');

                //get area_manager for the store associated with current_store manager
                $current_user_associated_stores = get_field('associated_stores', 'user_' . get_current_user_id());

                //if they have associated stores, use the first one (store_manager should only have one anyway)
                if ($current_user_associated_stores) {
                    if (is_array($current_user_associated_stores)) {
                        $current_user_associated_store_id = $current_user_associated_stores[0];
                    } else {
                        $current_user_associated_store_id = $current_user_associated_stores;
                    }

                    //get the area_manager for this store_id
                    $associated_area_managers = SJDisplay_Greentree::get_store_area_managers($current_user_associated_store_id);

                    //if area manager found, continue approval logic
                    if ($associated_area_managers) {
                        SJDisplay_Greentree_Woo_Emails::send_area_manager_approval_notification($associated_area_managers, $order_id);
                    }
                }
            } elseif (SJDisplay_Greentree::is_user_manager(get_current_user_id(), 'area_manager')) {
                //if user is only an area manager send the 'created' email out if this is a new order (not an amendment)
                $amended_order_check = get_post_meta($order_id, 'amended_order_post_id', true);
                if ($amended_order_check == '') {
                    SJDisplay_Greentree_Woo_Emails::send_area_manager_created_email($order_id);
                }
            }
        }
    }

    /*
     * Function to delete order from GreenTree by using DELETE method on SOSalesOrder API endpoint
     */
    public static function delete_order_from_greentree($greentree_order_id)
    {
        //check if order api disable override is active on sub-site level
        if (get_option('greentree_order_api_disable')) {
            return false;
        }

        SJDisplay_Greentree::log_action(self::$source, 'Attempted to delete order from GreenTree via API call!');

        return SJDisplay_Greentree::api_request(
            'SOSalesOrder/' . $greentree_order_id,
            array(),
            self::$source,
            'DELETE'
        );
    }

    /*
     * Function to cancel an order in GreenTree by passing a query string (?action=cancel) and 'CancelStatus' XML object
     * with the value of 'Cancelled'
     */
    public static function cancel_order_in_greentree($order_id, $greentree_order_reference)
    {
        //check if order api disable override is active on sub-site level
        if (get_option('greentree_order_api_disable')) {
            return false;
        }

        SJDisplay_Greentree::log_action(self::$source, 'Attempted to cancel order from GreenTree via API call!');

        return SJDisplay_Greentree::api_request(
            'SOSalesOrder',
            array(
                'action' => 'cancel',
                'cancel_status' => 'Cancelled',
                'reference' => $greentree_order_reference,
                'order_id' => $order_id,
            ),
            self::$source,
            'POST'
        );
    }

    /*
     * Function that sends orders to GreenTree using SJDisplay_GreenTree class function
     * update data var at end allows an order to be updated by passing an array of specific fields that
     * need to be updated, for example "StandardText", this is because we cannot simple hit GT API with 1 field
     * and the order ID, we need to resend the entire order packet
     */
    public static function send_order_to_greentree($order_id, $woo_status, $gt_status, $update_data = false)
    {
        //check if order api disable override is active on sub-site level
        if (get_option('greentree_order_api_disable')) {
            return false;
        }

        //prepare to send the data to GreenTree
        SJDisplay_Greentree::log_action(self::$source, 'Attempted to hook ' . $woo_status . ' order status and send API call!');

        $order = wc_get_order($order_id);
        $order_data = $order->get_data();
        $customer = $order->get_user();
        $order_items = array();

        $wc_session_customer = WC()->session->get('customer');
        $wc_find_rates = WC_Tax::find_rates($wc_session_customer);
        $get_current_tax_rate = array_shift($wc_find_rates);

        foreach ($order->get_items() as $item_id => $item_data) {
            //get the woo product object for this woo cart item
            $product = $item_data->get_product();

            //individual product price (ex gst)
            $product_unit_price = $product->get_price();

            //order item price (get_total) which will be qty x price
            $order_item_price = $item_data->get_total();

            //order item tax (based on total qty of the item)
            $order_item_tax = $item_data->get_total_tax();

            //if taxable, get the current tax rate
            if ($product->get_tax_status() == 'taxable') {
                $tax_percentage = $get_current_tax_rate['rate'];
            } else {
                $tax_percentage = 0;
            }

            $order_items[] = array(
                'quantity' => $item_data->get_quantity(),
                'amount' => $order_item_price,
                'tax_amount' => $order_item_tax,
                'tax_percentage' => $tax_percentage,
                'unit_price' => $product_unit_price,
                'stock_item_code' => $product->get_sku(),
            );
        }

        $order_total = $order->get_total();
        $order_tax = $order->get_total_tax();
        $order_net = $order->get_subtotal();

        //get stores associated with this user and use first store (for store managers they'll only be associated to one)
        $customer_associated_stores = SJDisplay_Greentree::get_stores_associated_with_user($customer->data->ID);
        $order_customer_code = $customer_associated_stores[0]['store_customer_code'];

        //pull this in from the store CPT because it is not a field in Woo (address 3) but is required to send to GT
        $order_store_address_3 = get_post_meta($customer_associated_stores[0]['store_post_id'], 'address_3', true);

        //if this is an order amendment or an area manager placing an order on behalf of a store
        if (isset($_POST['store_id']) && $_POST['store_id'] != '') {
            $order_customer_code = esc_attr($_POST['store_id']);
        }

        //get order po_number
        if (get_post_meta($order_id, 'order_po_number', true)) {
            $order_po_number = get_post_meta($order_id, 'order_po_number', true);
        } else {
            $order_po_number = 'SJ' . $order_id;
        }

        //if this is an approval for an order on behalf of another store
        if ($store_ordered_for = get_post_meta($order_id, 'store_ordered_for', true)) {
            $order_customer_code = $store_ordered_for;
        }

        //prepare order packet
        $order_data = array(
            'reference' => '',
            'currency' => 'AUD',
            'customer_code' => $order_customer_code,
            'status' => $gt_status,
            'net_amount' => $order_net,
            'tax_amount' => $order_tax,
            'address_name' => $order_data['billing']['company'],
            'contact' => substr($order_data['billing']['first_name'], 0, 30),
            'address_1' => $order_data['billing']['address_1'],
            'address_2' => $order_data['billing']['address_2'],
            'address_3' => $order_store_address_3,
            'suburb' => $order_data['billing']['city'],
            'postcode' => $order_data['billing']['postcode'],
            'state' => $order_data['billing']['state'],
            'country' => 'AUS',
            'phone_bh' => $order_data['billing']['phone'],
            'phone_ah' => '',
            'fax' => '',
            'email' => $order_data['billing']['email'],
            'web' => '',
            'mobile' => '',
            'customer_order_number' => $order_po_number,
            'customer_message' => htmlspecialchars_decode($order->get_customer_note()),
            'items' => $order_items,
        );

        //prepare other order data for request
        $current_order_greentree_reference_id = get_post_meta($order_id, 'order_greentree_reference_id', true);
        if ($current_order_greentree_reference_id) {
            $order_data['reference'] = $current_order_greentree_reference_id;
        }
        $order_data['id'] = $order_id;
        $order_data['shipping_total'] = $order->get_shipping_total();
        $order_data['shipping_tax'] = $order->get_shipping_tax();
        $order_data['shipping_tax_percentage'] = $tax_percentage;

        if ($update_data) {
            $order_data['update_data'] = $update_data;
        }

        //store order data as post meta on the order and timestamp
        update_post_meta($order_id, 'order_data_to_greentree', json_encode($order_data));
        update_post_meta($order_id, 'order_data_sent_timestamp', current_time('mysql'));
        update_post_meta($order_id, 'order_greentree_status', $gt_status);

        //send the order data via API request
        $send_order = SJDisplay_Greentree::api_request('SOSalesOrder', $order_data, self::$source, 'POST');

        //if there is a WP error from send_order
        if (is_wp_error($send_order)) {
            return false;
        } else {
            //if no WP error, store response
            update_post_meta($order_id, 'order_greentree_response', json_encode($send_order));

            //store custom order note
            if ($send_order['code'] == 500 || $send_order['code'] == 503) {
                $order->add_order_note('GreenTree Order API call failed!' . $send_order['message']);

                return false;
            } else {
                $order->add_order_note('GreenTree Order created with ID: ' . strval($send_order->Reference));

                update_post_meta($order_id, 'order_greentree_reference_id', strval($send_order->Reference));
                update_post_meta($order_id, 'order_greentree_modified_timestamp', strval($send_order->ModifiedTimeStamp));
                update_post_meta($order_id, 'order_greentree_modified_user', strval($send_order->ModifiedUser));

                return strval($send_order->Reference);
            }
        }

    }

    /*
     * Custom meta box registration for orders and wherever else required in Woo
     */
    public static function custom_meta_boxes()
    {
        //GreenTree order data meta
        add_meta_box(
            'greentree-order-data-meta-box',
            'GreenTree Data',
            array('SJDisplay_Greentree_Woo', 'custom_meta_box_greentree_order_data'),
            'shop_order',
            'normal',
            'default'
        );

        //cancellation note meta box
        add_meta_box(
            'order-cancellation-meta-box',
            'Cancellation Notes',
            array('SJDisplay_Greentree_Woo', 'custom_meta_box_order_cancellation'),
            'shop_order',
            'normal',
            'default'
        );
    }

    /*
     * Function to filter the order ID returned, if we have a GT ID, display that instead
     */
    public static function custom_woo_order_number($id)
    {
        //get the greentree reference ID if we have one
        $greentree_reference_id = get_post_meta($id, 'order_greentree_reference_id', true);
        if ($greentree_reference_id) {
            return $greentree_reference_id;
        } else {
            return $id;
        }
    }

    /*
     * Add ability to search woo orders by custom fields
     */
    public static function custom_shop_order_search_fields($search_fields)
    {
        $search_fields[] = 'order_greentree_reference_id';

        return $search_fields;
    }

    /*
     * Add custom general settings to the Woo settings area for imported parent customer address data
     */
    public static function custom_woo_general_settings($settings)
    {
        $updated_settings = array();

        foreach ($settings as $section) {
            //add to the bottom of the General Options section
            if (isset($section['id']) && 'store_address' == $section['id'] &&
                isset($section['type']) && 'sectionend' == $section['type']
            ) {
                $updated_settings[] = array(
                    'name' => 'Company',
                    'id' => 'woocommerce_store_company',
                    'type' => 'text',
                );

                $updated_settings[] = array(
                    'name' => 'Contact',
                    'id' => 'woocommerce_store_contact',
                    'type' => 'text',
                );

                $updated_settings[] = array(
                    'name' => 'Address line 3',
                    'id' => 'woocommerce_store_address_3',
                    'type' => 'text',
                );

                $updated_settings[] = array(
                    'name' => 'Phone (Business Hours)',
                    'id' => 'woocommerce_store_phone_bh',
                    'type' => 'text',
                );

                $updated_settings[] = array(
                    'name' => 'Phone (After Hours)',
                    'id' => 'woocommerce_store_phone_ah',
                    'type' => 'text',
                );

                $updated_settings[] = array(
                    'name' => 'Fax',
                    'id' => 'woocommerce_store_fax',
                    'type' => 'text',
                );

                $updated_settings[] = array(
                    'name' => 'Email',
                    'id' => 'woocommerce_store_email',
                    'type' => 'text',
                );

                $updated_settings[] = array(
                    'name' => 'Web',
                    'id' => 'woocommerce_store_web',
                    'type' => 'text',
                );

                $updated_settings[] = array(
                    'name' => 'Mobile',
                    'id' => 'woocommerce_store_mobile',
                    'type' => 'text',
                );
            }

            $updated_settings[] = $section;
        }

        return $updated_settings;
    }

    public static function custom_meta_box_order_cancellation()
    {
        include_once 'partials/woo-admin-order-cancellation.php';
    }

    public static function custom_meta_box_greentree_order_data()
    {
        include_once 'partials/woo-admin-order-greentree-data.php';
    }

    public static function custom_order_data_in_admin($order)
    {
        include_once 'partials/woo-admin-order-additional-data.php';
    }

    public static function custom_before_checkout_billing_form()
    {
        //display store selection on product (before add to cart) if user is area_manager
        if (SJDisplay_Greentree::is_user_manager(get_current_user_id(), 'area_manager')) {
            include_once 'partials/woo-order-store-selection.php';
        }

        include_once get_template_directory() . '/woocommerce/checkout/form-contact-name.php';

        include_once 'partials/woo-order-purchase-order-input.php';
    }

    public static function custom_my_account_view_order($order_id)
    {
        //include some custom order details
        include_once 'partials/woo-my-account-view-order.php';

        return $order_id;
    }

    //function that handles order syncing prior to template/page load for my account single and list order views
    public static function custom_my_account_view_order_sync($template)
    {
        //if we are on a my-account view order page
        if (get_query_var('view-order') && is_view_order_page()) {
            //sync the order via GT API call if we have a GT reference ID to sync with
            $order_id = get_query_var('view-order');
            $order = wc_get_order($order_id);

            //if the order has a GT reference ID and is not cancelled, we can sync it
            if (!$order->has_status('cancelled') && $order_greentree_reference_id = get_post_meta($order_id, 'order_greentree_reference_id', true)) {
                $sync_order = SJDisplay_Greentree::sync_order($order, false, 'class.sjdisplay-greentree-woo');

                if (!$sync_order) {
                    //set session var so we can hide the order and display a message on my account order view
                    $_SESSION['gt_order_sync_error'] = true;
                } else {
                    $_SESSION['gt_order_sync_error'] = false;
                }
            }
        }

        //if we are on the my-account order list page
        global $wp;
        if (is_account_page() && isset($wp->query_vars['orders'])) {
            //get the orders on the page
            $customer_orders = get_posts(apply_filters('woocommerce_my_account_my_orders_query', array(
                'meta_key' => '_customer_user',
                'meta_value' => get_current_user_id(),
                'post_type' => wc_get_order_types('view-orders'),
                'post_status' => array_keys(wc_get_order_statuses()),
            )));

            foreach ($customer_orders as $customer_order) {
                $order = wc_get_order($customer_order->ID);

                //if the order has a GT reference ID and is not cancelled, we can sync it
                if (!$order->has_status('cancelled') && $order_greentree_reference_id = get_post_meta($customer_order->ID, 'order_greentree_reference_id', true)) {
                    $sync_order = SJDisplay_Greentree::sync_order($order, false, 'class.sjdisplay-greentree-woo');

                    if (!$sync_order) {
                        //set session var so we can hide the order and display a message on my account order view
                        $_SESSION['gt_order_list_sync_error_' . $customer_order->ID] = true;
                    } else {
                        $_SESSION['gt_order_list_sync_error_' . $customer_order->ID] = false;
                    }
                }
            }
        }

        return $template;
    }

    public static function custom_woo_order_edit_order_sync($current_screen)
    {
        //if we're on a shop_order post edit screen
        if ($current_screen->post_type === 'shop_order' && $current_screen->base === 'post') {
            //get the order id (post id) and run an order sync if we have an order reference ID
            $order_id = $_GET['post'];
            if ($order_id) {
                $order = wc_get_order($order_id);

                //if we have an order reference ID and the order isn't cancelled then sync it from GT
                if (!$order->has_status('cancelled') && $order_greentree_reference_id = get_post_meta($order_id, 'order_greentree_reference_id', true)) {
                    $sync_order = SJDisplay_Greentree::sync_order($order, false, 'class.sjdisplay-greentree-woo');

                    if (!$sync_order) {
                        //set session var so we can display JS error alert on admin
                        $_SESSION['gt_order_admin_sync_error'] = true;
                    } else {
                        $_SESSION['gt_order_admin_sync_error'] = false;
                    }
                }
            }
        }
    }

    public static function custom_acf_data_save($post_id)
    {
        //if some ACF data passed via submit
        if (!empty($_POST['acf'])) {
            //if we're updating a shop order
            if ($_POST['post_type'] == 'shop_order' && $_POST['action'] == 'editpost') {
                //if a GT reference ID override has been passed via ACF, save this over the gt reference ID we are using
                //GT reference ID override
                //field name: gt_order_id_override
                //field key: field_5bfa42f7c8730
                if ($_POST['acf']['field_5bfa42f7c8730']) {
                    $order_id = $_POST['post_ID'];
                    $gt_reference_override = $_POST['acf']['field_5bfa42f7c8730'];
                    update_post_meta($order_id, 'order_greentree_reference_id', $gt_reference_override);
                }
            }
        }
    }
}
