<?php

class SJDisplay_Greentree
{
    private static $initiated = false;
    protected static $instance = null;

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

        //custom user roles
        SJDisplay_Greentree::register_custom_user_roles();

        //custom post types
        SJDisplay_Greentree::register_custom_post_types();

        add_filter('pre_update_option_greentree_api_key',
            array('SJDisplay_Greentree', 'custom_option_filter_encrypt'), 10, 3);

        add_filter('pre_update_option_greentree_username',
            array('SJDisplay_Greentree', 'custom_option_filter_encrypt'), 10, 3);

        add_filter('pre_update_option_greentree_password',
            array('SJDisplay_Greentree', 'custom_option_filter_encrypt'), 10, 3);

        add_filter('option_greentree_api_key', array('SJDisplay_Greentree', 'custom_option_filter_decrypt'));
        add_filter('option_greentree_username', array('SJDisplay_Greentree', 'custom_option_filter_decrypt'));
        add_filter('option_greentree_password', array('SJDisplay_Greentree', 'custom_option_filter_decrypt'));        
    }

    public static function custom_option_filter_encrypt($value, $old_value, $option)
    {
        if ($value == SJDisplay_Greentree_Admin::get_secret_mask()) {
            return self::custom_crypt($old_value, 'e');
        } else {
            return self::custom_crypt($value, 'e');
        }
    }

    public static function custom_option_filter_decrypt($option)
    {
        return self::custom_crypt($option, 'd');
    }

    private static function custom_crypt($string, $action = 'e')
    {
        $secret_key = 'greentree_key';
        $secret_iv = 'greentree_iv';

        $output = false;
        $encrypt_method = 'AES-256-CBC';
        $key = hash('sha256', $secret_key);
        $iv = substr(hash('sha256', $secret_iv), 0, 16);

        if ($action == 'e') {
            $output = base64_encode(openssl_encrypt($string, $encrypt_method, $key, 0, $iv));
        } else if ($action == 'd') {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }

        return $output;
    }

    public static function register_custom_user_roles()
    {
        add_role(
            'store_manager',
            'Store Manager',
            array()
        );

        add_role(
            'area_manager',
            'Area Manager',
            array()
        );
    }

    public static function register_custom_post_types()
    {
        $types = array(
            array(
                'the_type' => 'store',
                'single' => 'Store',
                'plural' => 'Stores',
                'icon' => 'dashicons-cart',
                'public' => true,
                'supports' => array('title', 'excerpt', 'thumbnail'),
                'has_archive' => true,
            ),
        );

        foreach ($types as $type) {
            $labels = array(
                'name' => _x($type['plural'], 'post type general name'),
                'singulturbo_name' => _x($type['single'], 'post type singular name'),
                'add_new' => _x('Add New', $type['single']),
                'add_new_item' => __('Add New ' . $type['single']),
                'edit_item' => __('Edit ' . $type['single']),
                'new_item' => __('New ' . $type['single']),
                'view_item' => __('View ' . $type['single']),
                'search_items' => __('Search ' . $type['plural']),
                'not_found' => __('No ' . $type['plural'] . ' found'),
                'not_found_in_trash' => __('No ' . $type['plural'] . ' found in Trash'),
                'parent_item_colon' => '',
            );

            $args = array(
                'labels' => $labels,
                'public' => $type['public'],
                'has_archive' => $type['has_archive'],
                'publicly_queryable' => true,
                'show_ui' => true,
                'query_var' => true,
                'rewrite' => true,
                'capability_type' => 'post',
                'hierarchical' => true,
                'menu_position' => 20,
                'menu_icon' => $type['icon'],
                'show_in_menu' => true,
                'show_in_nav_menus' => true,
                'supports' => $type['supports'],
            );
            register_post_type($type['the_type'], $args);
        }
    }

    /*
     * Green Tree API call wrapper
     *
     * Main Calls to use:
     * List of all Customers = Customer
     * Item info = StockItem
     * Order placed = SOSalesOrder
     * Invoices = ARInvoice
     * Delivery Info / CRON status checks = SOPackingSlip
     */
    public static function api_request($call, $request_query, $source = '', $method = 'GET')
    {
        //log action
        self::log_action($source, 'Attempting API call ' . $call . ' with params: ' . http_build_query($request_query));

        //grab greentree endpoint config from master site
        $endpoint = get_option('greentree_endpoint_url');
        $api_key = get_option('greentree_api_key');
        $username = get_option('greentree_username');
        $password = get_option('greentree_password');

        if (!$endpoint) {
            self::log_action($source, 'API endpoint not set in plugin config!');
            exit();
        }

        if (!$api_key) {
            self::log_action($source, 'API key not set in plugin config!');
            exit();
        }

        if (!$username || !$password) {
            self::log_action($source, 'API credentials not set in plugin config!');
            exit();
        }

        //request setup
        $request_url = $endpoint . $call . '?ApiKey=' . $api_key;

        $request_args = array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode($username . ':' . $password),
            ),
            'method' => $method,
            'timeout' => 60,
        );

        //if making a POST call we have to sort out the XML packet and handle the API request differently
        if ($method == 'POST') {
            $request_args['headers']['Content-Type'] = 'text/xml';

            //for order creation we need to prepare the order data as XML
            if ($call == 'SOSalesOrder') {
                //check if it's a cancellation
                if (isset($request_query['action']) && $request_query['action'] == 'cancel') {
                    //update the cancel request URL
                    $request_url = $endpoint . $call . '/' . $request_query['reference'] . '?ApiKey=' . $api_key . '&action=cancel';

                    $request_args['body'] = SJDisplay_Greentree::create_order_cancel_xml($request_query);
                } else {
                    //if its an update of an existing order, adjust the request url
                    if (isset($request_query['update_data'])) {
                        $request_url = $endpoint . $call . '/' . $request_query['reference'] . '?ApiKey=' . $api_key;
                    }

                    //it's a standard order create call
                    $request_args['body'] = SJDisplay_Greentree::create_order_xml($request_query);
                    
                    SJDisplay_Greentree::log_xml($request_args['body'], $request_query['id'], 'so-sales-order');
                }
            }

            //for receipt creation we need to prepare the receipt data as XML
            if ($call == 'Receipt') {
                $request_args['body'] = SJDisplay_Greentree::create_receipt_xml($request_query);

                SJDisplay_Greentree::log_xml($request_args['body'], $request_query['order']->get_id(), 'ar-receipt');
            }

            $response = wp_remote_post($request_url, $request_args);
        }

        //if making a DELETE call
        if ($method == 'DELETE') {
            $response = wp_remote_request($request_url, $request_args);
        }

        //standard GET call
        if ($method == 'GET') {
            if (sizeof($request_query) > 0) {
                $request_url .= '&' . urldecode(http_build_query($request_query));
            }

            $response = wp_remote_get($request_url, $request_args);
        }

        if (!is_wp_error($response)) {
            if ($response['response']['code'] == 200) {
                $body = wp_remote_retrieve_body($response);
                $return = simplexml_load_string($body);
                $log_message = 'API call for ' . $call . ' OK! ';
            } else {
                $log_message = 'API call for ' . $call . ' failed! ';
            }

            $log_message .= 'Response code: ' . $response['response']['code'] . '. ';
            $log_message .= 'Response message: ' . $response['response']['message'];
            self::log_action($source, $log_message);

            //if there is an error, also output the body into logs and send email notifications
            if ($response['response']['code'] != 200) {
                $response_body_xml = simplexml_load_string($response['body']);

                $log_error_body_xml_message = 'Error Body XML - Message: ' . strval($response_body_xml->Message);
                self::log_action($source, $log_error_body_xml_message);

                $log_error_body_xml_status_phrase = 'Error Body XML - Message: ' . strval($response_body_xml->StatusPhrase);
                self::log_action($source, $log_error_body_xml_status_phrase);

                //send corresponding email error notification for Receipt call
                if ($call == 'Receipt') {
                    $message = '<p>There was an error sending receipt data to GreenTree</p>';
                    $error_email_subject = 'GT API error creating Receipt';
                } else if ($call == 'SOSalesOrder') {
                    //send email error notification for SOSalesOrder call (order creation)
                    $message = '<p>There was an error sending order data to GreenTree</p>';
                    $error_email_subject = 'GT API error creating Order';
                } else {
                    //default API error notice
                    $message = '<p>There was an error sending data to GreenTree</p>';
                    $message .= '<p>Error for call: ' . $call . '</p>';
                    $error_email_subject = 'GT API error for ' . $call . ' call';
                }

                //default data passed to email for error notification
                $message .= '<p><strong>API Query Args:</strong></p>';
                $message .= '<pre>';
                $message .= print_r($request_args, true);
                $message .= '</pre>';
                if (isset($request_args['body'])) {
                    $message .= '<p><strong>Body XML Only:</strong></p>';
                    $message .= '<pre>';
                    $message .= print_r(simplexml_load_string($request_args['body']), true);
                    $message .= '</pre>';
                }
                $message .= '<p><strong>Response:</strong></p>';
                $message .= '<pre>';
                $message .= print_r($response, true);
                $message .= '</pre>';

                //only send email if call was for SOSalesOrder
                if($call == 'SOSalesOrder') {
                    SJDisplay_Greentree::error_notification_email($error_email_subject, $message);
                }

                $return = array(
                    'response' => $response['response'],
                    'body' => $response_body_xml,
                );
            }
        } else {
            self::log_action($source, 'WP Remote Get Error for call ' . $call . ': ' . $response->get_error_message());

            //send API error notification for WP Remote call error
            $message = '<p>There was an error making a WP_Remote request</p>';
            $message .= '<p><strong>API Call: </strong>' . $call . '</p>';
            $message .= '<p><strong>Response: </strong>' . $response->get_error_message() . '</p>';
            //SJDisplay_Greentree::error_notification_email('WP_Remote Error', $message);

            $return = $response;
        }

        return $return;
    }

    public static function create_order_xml($order_data)
    {
        //if updating existing order vs creating new order (default reference empty for new order)
        $reference = '';
        if (isset($order_data['reference'])) {
            $reference = $order_data['reference'];
        }

        //update logic - if updating standardtext/note
        if (isset($order_data['update_data']['StandardText'])) {
            $standard_text = $order_data['update_data']['StandardText'];
            $instructions = $standard_text;
        } else {
            $standard_text = 'Contact: ' . $order_data['contact'] . ' Note: ' . $order_data['customer_message'];
            $instructions = 'Contact: ' . $order_data['contact'] . ' Note: ' . $order_data['customer_message'];
        }

        //setup the XML packet
        $order_xml = '<?xml version="1.0" encoding="UTF-8"?>
                        <SOSalesOrder>
                            <Reference>' . $reference . '</Reference>
                            <CurrencyCode>' . $order_data['currency'] . '</CurrencyCode>
                            <Status>' . $order_data['status'] . '</Status>
                            <Customer>' . $order_data['customer_code'] . '</Customer>
                            <NetAmount>' . $order_data['net_amount'] . '</NetAmount>
                            <TaxAmount>' . $order_data['tax_amount'] . '</TaxAmount>
                            <AddressName>' . SJDisplay_Greentree::clean_string_for_xml($order_data['address_name']) . '</AddressName>
                            <StandardText>' . SJDisplay_Greentree::clean_string_for_xml($standard_text) . '</StandardText>
                            <DeliveryAddress>
                                <Contact>' . SJDisplay_Greentree::clean_string_for_xml($order_data['contact']) . '</Contact>
                                <Address1>' . SJDisplay_Greentree::clean_string_for_xml($order_data['address_1']) . '</Address1>
                                <Address2>' . SJDisplay_Greentree::clean_string_for_xml($order_data['address_2']) . '</Address2>
                                <Address3>' . SJDisplay_Greentree::clean_string_for_xml($order_data['address_3']) . '</Address3>
                                <Instructions>' . SJDisplay_Greentree::clean_string_for_xml($instructions) . '</Instructions>
                                <Suburb>' . SJDisplay_Greentree::clean_string_for_xml($order_data['suburb']) . '</Suburb>
                                <Postcode>' . $order_data['postcode'] . '</Postcode>
                                <State>' . $order_data['state'] . '</State>
                                <Country>' . $order_data['country'] . '</Country>
                                <PhoneBH>' . $order_data['phone_bh'] . '</PhoneBH>
                                <PhoneAH>' . $order_data['phone_ah'] . '</PhoneAH>
                                <Fax>' . $order_data['fax'] . '</Fax>
                                <Email>' . $order_data['email'] . '</Email>
                                <Web>' . $order_data['web'] . '</Web>
                                <Mobile>' . $order_data['mobile'] . '</Mobile>
                            </DeliveryAddress>
                            <CustomerOrderNumber>' . $order_data['customer_order_number'] . '</CustomerOrderNumber>';

        //items total is the cart items + 1 (for Z-FREIGHT)
        $items_total = sizeof($order_data['items']) + 1;
        $order_xml .= "<LineItems collection='true' count='" . $items_total . "'>";

        $item_count = 1;

        //iterate and add product line items
        foreach ($order_data['items'] as $item) {
            $order_xml .= '<LineItem>
                                <LineType>SOSOINLineItem</LineType>
                                <LineNumber>' . $item_count . '</LineNumber>
                                <Quantity>' . $item['quantity'] . '</Quantity>
                                <Amount>' . $item['amount'] . '</Amount>
                                <TaxAmount>' . $item['tax_amount'] . '</TaxAmount>
                                <TaxPercentage>' . $item['tax_percentage'] . '</TaxPercentage>
                                <UnitPrice>' . $item['unit_price'] . '</UnitPrice>
                                <StockItem>' . $item['stock_item_code'] . '</StockItem>
                                <isHoldingPrice>true</isHoldingPrice>
                                <isHoldingDisc>true</isHoldingDisc>
                            </LineItem>';
            $item_count++;
        }

        //only send shipping total in sites with payment system enabled
        if (get_option('greentree_payment_enabled')) {
            $shipping_total = $order_data['shipping_total'];
        } else {
            //else send $0 value in Z-FREIGHT
            $shipping_total = 0;
        }

        //add Z-FREIGHT line item XML to order packet
        $order_xml .= '<LineItem>
                            <LineType>SOSOINLineItem</LineType>
                            <LineNumber>' . $item_count . '</LineNumber>
                            <Quantity>1</Quantity>
                            <Amount>' . $shipping_total . '</Amount>
                            <TaxAmount>' . $order_data['shipping_tax'] . '</TaxAmount>
                            <TaxPercentage>' . $order_data['shipping_tax_percentage'] . '</TaxPercentage>
                            <UnitPrice>' . $shipping_total . '</UnitPrice>
                            <StockItem>Z-FREIGHT</StockItem>
                            <isHoldingPrice>true</isHoldingPrice>
                            <isHoldingDisc>true</isHoldingDisc>
                        </LineItem>';

        $order_xml .= '</LineItems></SOSalesOrder>';

        return $order_xml;
    }

    public static function create_order_cancel_xml($order_data)
    {
        //get the order cancellation note that is stored
        $order_cancellation_note = get_post_meta($order_data['order_id'], 'order_cancellation_note', true);

        //setup the XML packet
        $order_xml = '<?xml version="1.0" encoding="UTF-8"?>
                        <SOSalesOrder>
                            <Reference>' . $order_data['reference'] . '</Reference>
                            <CancelStatus>' . $order_data['cancel_status'] . '</CancelStatus>
                            <StandardText>' . $order_cancellation_note . '</StandardText>
                        </SOSalesOrder>';

        return $order_xml;
    }

    public static function create_receipt_xml($receipt_data)
    {
        //view data
        $current_date = date('Y-m-d', time());
        $order_greentree_data = json_decode(get_post_meta($receipt_data['order']->get_id(), 'order_data_to_greentree', true));
        $order_greentree_reference = get_post_meta($receipt_data['order']->get_id(), 'order_greentree_reference_id', true);

        $receipt_total_net = $receipt_data['order']->get_total() - $receipt_data['order']->get_total_tax();
        $receipt_total_tax = $receipt_data['order']->get_total_tax();
        
        //setup the AppliedTransactions XML object depending on number of invoices attached to the order
        if (is_array($receipt_data['order_invoice'])) {
            $applied_transactions_count = sizeof($receipt_data['order_invoice']);
            $applied_transactions = '';

            $applied_transaction_amount_total = 0;
            $applied_transaction_tax_amount_total = 0;

            foreach ($receipt_data['order_invoice'] as $order_invoice) {

                $applied_transaction_amount = $order_invoice->NetAmount;

                $applied_transaction_amount_total += $order_invoice->NetAmount;
                $applied_transaction_tax_amount_total += $order_invoice->TaxAmount;

                $applied_transactions .= '<AppliedTransaction>
                                        <CurrencyRate>1.00000000</CurrencyRate>
                                        <Amount>' . $order_invoice->NetAmount . '</Amount>
                                        <CancelledAmount>0</CancelledAmount>
                                        <DiscountAmount>0.00</DiscountAmount>
                                        <TaxAmount>' . $order_invoice->TaxAmount . '</TaxAmount>
                                        <WitholdingTaxAmount>0.00</WitholdingTaxAmount>
                                        <IsCancelled>false</IsCancelled>
                                        <IsAppliedUnapplied>false</IsAppliedUnapplied>
                                        <IsCurrencyRateMultiply>false</IsCurrencyRateMultiply>
                                        <Narration/>
                                        <Transaction>
                                                <Reference>' . $order_invoice->Reference . '</Reference>
                                                <TransactionType>ARInvoice</TransactionType>
                                        </Transaction>
                                    </AppliedTransaction>';
            }

            //determine the ARUnappliedTransaction totals based on the sum totals above minus the Woo order total
            $unapplied_transaction_amount = round($receipt_total_net - $applied_transaction_amount_total, 2);
            $unapplied_transaction_tax = round($receipt_total_tax - $applied_transaction_tax_amount_total, 2);

            //add outstanding amount AppliedTransaction
            $applied_transactions .= '<AppliedTransaction>
                                        <CurrencyRate>1.00000000</CurrencyRate>
                                        <Amount>' . $unapplied_transaction_amount . '</Amount>
                                        <CancelledAmount>0</CancelledAmount>
                                        <DiscountAmount>0.00</DiscountAmount>
                                        <TaxAmount>' . $unapplied_transaction_tax . '</TaxAmount>
                                        <WitholdingTaxAmount>0.00</WitholdingTaxAmount>
                                        <IsCancelled>false</IsCancelled>
                                        <IsAppliedUnapplied>false</IsAppliedUnapplied>
                                        <IsCurrencyRateMultiply>true</IsCurrencyRateMultiply>
                                        <Narration/>
                                        <Transaction>
                                            <Reference/>
                                            <TransactionType>ARUnappliedTransaction</TransactionType>
                                        </Transaction>
                                    </AppliedTransaction>';
        } else {
            $applied_transactions_count = 1;
            $applied_transactions = '<AppliedTransaction>
                                        <CurrencyRate>1.00000000</CurrencyRate>
                                        <Amount>' . $receipt_data['order_invoice']->NetAmount . '</Amount>
                                        <CancelledAmount>0</CancelledAmount>
                                        <DiscountAmount>0.00</DiscountAmount>
                                        <TaxAmount>' . $receipt_data['order_invoice']->TaxAmount . '</TaxAmount>
                                        <WitholdingTaxAmount>0.00</WitholdingTaxAmount>
                                        <IsCancelled>false</IsCancelled>
                                        <IsAppliedUnapplied>false</IsAppliedUnapplied>
                                        <IsCurrencyRateMultiply>false</IsCurrencyRateMultiply>
                                        <Narration/>
                                        <Transaction>
                                                <Reference>' . $receipt_data['order_invoice']->Reference . '</Reference>
                                                <TransactionType>ARInvoice</TransactionType>
                                        </Transaction>
                                    </AppliedTransaction>';
        }

        //setup the XML packet
        $receipt_xml = '<?xml version="1.0" encoding="UTF-8"?>
                        <Receipt>
                            <DocumentDate>' . $current_date . '</DocumentDate>
                            <NetAmount>' . $receipt_total_net . '</NetAmount>
                            <TaxAmount>' . $receipt_total_tax . '</TaxAmount>
                            <IsCurrencyRateMultiply>false</IsCurrencyRateMultiply>
                            <Branch>03</Branch>
                            <Currency>AUD</Currency>
                            <PostingDate>' . $current_date . '</PostingDate>
                            <Reference>' . $order_greentree_reference . '</Reference>
                            <Customer>' . $order_greentree_data->customer_code . '</Customer>
                            <BankAccount>CLEA</BankAccount>
                            <ReceiptType>Banked Deposit</ReceiptType>
                            <BankCurrencyRate>1.00000000</BankCurrencyRate>
                            <IsBankCurrencyRateMultiply>false</IsBankCurrencyRateMultiply>
                            <AppliedTransactions collection="true" count="' . $applied_transactions_count . '">
                                ' . $applied_transactions . '
                            </AppliedTransactions>
                    </Receipt>';

        return $receipt_xml;
    }

    /*
     * Return Attachment URL
     */
    public static function get_attachment_url($item_code, $attachment_name)
    {
        return self::$green_tree_endpoint . 'StockItem/' . $item_code . '?action=attachment&name=' . $attachment_name;
    }

    /*
     * Get store by Customer Code
     */
    public static function get_customer_by_code($customer_code)
    {
        global $wpdb;

        $customers_with_code = $wpdb->get_results("SELECT post_id FROM $wpdb->postmeta WHERE meta_key='customer_code' AND meta_value='$customer_code'");

        if (!empty($customers_with_code)) {

            $post_id_array = array();
            foreach ($customers_with_code as $customer) {
                $post_id_array[] = $customer->post_id;
            }

            //double check posts exist
            $store_query = new WP_Query(
                array(
                    'post_type' => 'store',
                    'post__in' => $post_id_array,
                )
            );

            if (!empty($store_query->posts)) {
                return $store_query->posts;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /*
     * Get customer by parent code
     */
    public static function get_customers_by_parent($parent)
    {
        $customers_with_parent_code = new WP_Query(
            array(
                'post_type' => 'store',
                'posts_per_page' => -1,
                'meta_query' => array(
                    array(
                        'key' => 'parent_customer',
                        'value' => $parent,
                        'compare' => '=',
                    ),
                ),
            )
        );

        if ($customers_with_parent_code->have_posts()) {
            $child_customers_array = array();
            foreach ($customers_with_parent_code->posts as $customer) {
                $child_customers_array[] = get_field('customer_code', $customer->ID);
            }
            return $child_customers_array;
        } else {
            return false;
        }
    }

    /*
     * Get parent customer/company term by code
     */
    public static function get_company_term_by_code($code)
    {
        global $wpdb;

        $sql_query = "
            SELECT DISTINCT t.*, tt.*, tm.meta_value FROM wp_terms AS t INNER JOIN wp_termmeta
            ON ( t.term_id = wp_termmeta.term_id ) INNER JOIN wp_term_taxonomy AS tt ON t.term_id = tt.term_id
            LEFT JOIN wp_termmeta AS tm ON (t.term_id = tm.term_id AND tm.meta_key = 'order')
            WHERE tt.taxonomy IN ('parent_customer') AND ( ( wp_termmeta.meta_key = 'company_code'
            AND wp_termmeta.meta_value = '$code' ) ) GROUP BY t.term_id ORDER BY tm.meta_value+0 ASC, t.name ASC
            ";

        $results = $wpdb->get_results($sql_query);

        if ($results) {
            return $results[0];
        } else {
            return false;
        }
    }

    /*
     * Simple helper to detect if user has a type of manager role (store_manager, area_manager etc)
     */
    public static function is_user_manager($user_id, $type)
    {
        $user_data = get_userdata($user_id);

        if ($user_data->roles) {
            foreach ($user_data->roles as $user_role) {
                if ($user_role == $type) {
                    return true;
                }
            }
        }

        return false;
    }

    /*
     * Function to return store_manager users for a store (store_manager role only)
     * We could combine this and the get_store_area_manager() func into a single function later but will
     * need to refactor and find/replace all references of it which is a little dangerous at the moment
     */
    public static function get_store_store_managers($store_id)
    {
        $store_managers = false;

        $store_managers_query = new WP_User_Query(array(
            'role' => 'store_manager',
            'role__not_in' => array('area_manager')
        ));

        if (!empty($store_managers_query->get_results())) {
            foreach ($store_managers_query->get_results() as $store_manager) {
                //get the store managers associated stores
                $associated_stores = get_field('associated_stores', $store_manager);

                //if passed in store id is associated with this store_manager, add them to the return list
                if (is_array($associated_stores)) {
                    if (in_array($store_id, $associated_stores)) {
                        $store_managers[] = $store_manager;
                    }
                } else {
                    if ($store_id == $associated_stores) {
                        $store_managers[] = $store_manager;
                    }
                }
            }
        }

        return $store_managers;
    }

    /*
     * Function to return area_manager users for a store (area_manager role only)
     */
    public static function get_store_area_managers($store_id)
    {
        $store_area_managers = false;

        $area_managers_query = new WP_User_Query(array(
            'role' => 'area_manager',
            'role__not_in' => array('store_manager')
        ));

        if (!empty($area_managers_query->get_results())) {
            foreach ($area_managers_query->get_results() as $area_manager) {
                //get the area managers associated stores
                $associated_stores = get_field('associated_stores', $area_manager);

                //if passed in store id is associated with this area_manager, add them to the return list
                if (is_array($associated_stores)) {
                    if (in_array($store_id, $associated_stores)) {
                        $store_area_managers[] = $area_manager;
                    }
                } else {
                    if ($store_id == $associated_stores) {
                        $store_area_managers[] = $area_manager;
                    }
                }
            }
        }

        return $store_area_managers;
    }

    /*
     * Function to return array of stores associated with user
     * will return their post ids, titles, and codes
     */
    public static function get_stores_associated_with_user($user_id)
    {
        $associated_stores = get_field('associated_stores', 'user_' . $user_id);

        if ($associated_stores) {
            $return_stores = array();

            if (is_array($associated_stores)) {
                foreach ($associated_stores as $store_id) {
                    $store_query = new WP_Query(
                        array(
                            'post_type' => 'store',
                            'p' => $store_id,
                        )
                    );

                    if (!empty($store_query->post)) {
                        $return_stores[] = array(
                            'store_post_id' => $store_query->post->ID,
                            'store_post_name' => $store_query->post->post_title,
                            'store_post_slug' => $store_query->post->post_name,
                            'store_customer_code' => get_field('customer_code', $store_query->post->ID),
                        );
                    }
                }
            } else {
                $store_query = new WP_Query(
                    array(
                        'post_type' => 'store',
                        'p' => $associated_stores,
                    )
                );

                if (!empty($store_query->post)) {
                    $return_stores[] = array(
                        'store_post_id' => $store_query->post->ID,
                        'store_post_name' => $store_query->post->post_title,
                        'store_post_slug' => $store_query->post->post_name,
                        'store_customer_code' => get_field('customer_code', $store_query->post->ID),
                    );
                }
            }

            return $return_stores;
        } else {
            return false;
        }
    }

    /*
     * Determine if user requires po number (true/false) when placing order
     */
    public static function get_customer_po_number_required($user_id)
    {
        $store_to_check = false;
        $po_number_required = false;

        //if store manager or area manager
        if (
            SJDisplay_Greentree::is_user_manager(get_current_user_id(), 'store_manager') && 
            !SJDisplay_Greentree::is_user_manager(get_current_user_id(), 'area_manager')
        ) {
            //get associated store
            $current_user_associated_stores = get_field('associated_stores', 'user_' . $user_id);
            if (is_array($current_user_associated_stores)) {
                $store_to_check = $current_user_associated_stores[0];
            } else {
                $store_to_check = $current_user_associated_stores;
            }
        } else {
            $selected_store = false;

            if (isset($_POST['store_id']) && $_POST['store_id'] != '') {
                $selected_store = $_POST['store_id'];
            } elseif (isset($_GET['store-selected']) && $_GET['store-selected'] != '') {
                $selected_store = $_GET['store-selected'];
            }

            if ($selected_store) {
                $selected_store_post = SJDisplay_Greentree::get_customer_by_code($selected_store);
                $store_to_check = $selected_store_post[0]->ID;
            }
        }

        if($store_to_check) {
            //determine if PO is mandatory
            $po_number_required = get_field('po_number_required', $store_to_check);
        }

        return $po_number_required;
    }

    /*
     * Sync order data from GreenTree to WooCommerce Order
     */
    public static function sync_order($order, $verbose = false, $script)
    {
        //accept WP_Post or Woo Order Object
        if (is_a($order, 'WP_Post')) {
            $order_id = $order->ID;
            $order_post = get_post($order);
            $order = wc_get_order($order_id);
        } else {
            $order_id = $order->get_id();
            $order_post = get_post($order_id);
        }

        $order_greentree_reference_id = get_post_meta($order_id, 'order_greentree_reference_id', true);
        $order_greentree_status = get_post_meta($order_id, 'order_greentree_status', true);
        $order_greentree_modified_timestamp = get_post_meta($order_id, 'order_greentree_modified_timestamp', true);
        $order_greentree_modified_user = get_post_meta($order_id, 'order_greentree_modified_user', true);

        if ($verbose) {
            echo '   Woo order ID: ' . $order_id . '<br>';
            echo '   Woo order status: ' . $order_post->post_status . '<br>';
            echo '   Stored order reference: ' . $order_greentree_reference_id . '<br>';
            echo '   Stored order status: ' . $order_greentree_status . '<br>';
            echo '   Stored order modified timestamp: ' . $order_greentree_modified_timestamp . '<br>';
            echo '   Stored order modified user: ' . $order_greentree_modified_user . '<br>';
            echo '      Requesting data from API for this order...<br>';
        }

        //api call and default error state
        $api_call_error = false;
        $live_order_data = SJDisplay_Greentree::api_request(
            'SOSalesOrder/' . $order_greentree_reference_id,
            array(),
            $script
        );

        if ($live_order_data['response']['code'] == 500 || $live_order_data['response']['code'] == 501) {
            if ($verbose) {
                echo '      Error requesting order data!<br>   ---<br>';
            }

            $api_call_error = true;
        }

        if ($live_order_data['response']['code'] == 404) {
            if ($verbose) {
                echo '      Order not found on GreenTree!<br>   ---<br>';
            }

            $api_call_error = true;
        }

        //if API call error, return false and send email notification
        if ($api_call_error) {
            $message = '<p>There was an error syncing order from GT for order ID: ' . $order_greentree_reference_id . '</p>';
            $message .= '<p><strong>Response:</strong></p>';
            $message .= '<pre>';
            $message .= print_r($live_order_data, true);
            $message .= '</pre>';
            SJDisplay_Greentree::error_notification_email('GT API error syncing order', $message);

            return false;
        }

        //if OK, output some of the return data
        if ($verbose) {
            echo '      Live status: ' . $live_order_data->Status . '<br>';
            echo '      Live modified timestamp: ' . $live_order_data->ModifiedTimeStamp . '<br>';
            echo '      Live modified user: ' . $live_order_data->ModifiedUser . '<br>';
        }

        //default update notification required state
        $update_notification_required = false;

        //compare the main differences to see if we need to update the order
        if ($live_order_data->ModifiedUser != $order_greentree_modified_user) {
            if ($verbose) {
                echo '      - Modified user difference detected!<br>';
            }
            $update_notification_required = true;
        }
        if ($live_order_data->Status != $order_greentree_status) {
            if ($verbose) {
                echo '      - Order status difference detected!<br>';
            }
            $update_notification_required = true;
        }

        //update the order always
        if ($verbose) {
            echo '      -- Updating order!<br>';
        }

        //prepare to update delivery address with latest data
        $delivery_address = array(
            'company' => strval($live_order_data->AddressName),
            'email' => strval($live_order_data->DeliveryAddress->Contact),
            'phone' => strval($live_order_data->DeliveryAddress->PhoneBH),
            'address_1' => strval($live_order_data->DeliveryAddress->Address1),
            'address_2' => strval($live_order_data->DeliveryAddress->Address2),
            'city' => strval($live_order_data->DeliveryAddress->Suburb),
            'state' => strval($live_order_data->DeliveryAddress->State),
            'postcode' => strval($live_order_data->DeliveryAddress->Postcode),
            'country' => strval($live_order_data->DeliveryAddress->Country),
        );

        //split delivery address name from single contact field
        $split_deliver_address_name = explode(' ', strval($live_order_data->DeliveryAddress->Contact));

        if (isset($split_deliver_address_name[0])) {
            $delivery_address['first_name'] = $split_deliver_address_name[0];
        }

        if (isset($split_deliver_address_name[1])) {
            $delivery_address['last_name'] = $split_deliver_address_name[1];
        }

        //update delivery address with latest data
        $order->set_address($delivery_address, 'shipping');

        //get current order items in Woo
        $order_items = $order->get_items();

        //prep live order sku array to compare and remove any items in Woo that aren't in GT
        $live_order_skus = array();

        //if GT live order data line items exist
        if ($live_order_data->LineItems->LineItem) {

            //set live order base Z-Freight values because there may be multiple live z-freight line items
            $live_order_z_freight_total = 0;
            $live_order_z_freight_tax = 0;
            $live_z_freight_data_exists = false;

            //iterate through live data line items and look for changes in stored Woo order data
            foreach ($live_order_data->LineItems->LineItem as $line_item) {

                $line_item_sku = strval($line_item->StockItem);
                $line_item_qty = number_format(strval($line_item->Quantity));
                $line_item_price = strval($line_item->UnitPrice);
                $line_item_total = strval($line_item->Amount);
                $line_item_tax = strval($line_item->TaxAmount);

                //add line item to array for comparison and removal of items in Woo that arent in GT
                $live_order_skus[] = $line_item_sku;

                //update Z-FREIGHT total as per details from API
                if ($line_item_sku == 'Z-FREIGHT') {
                    $live_z_freight_data_exists = true;

                    if ($verbose) {
                        echo '      -- Add Z-FREIGHT line item total and tax to temporary data...<br>';
                    }

                    //prepare to set to live shipping amount and shipping tax (from Z-FREIGHT line item out of live data)
                    $live_order_z_freight_total += strval($line_item->Amount);
                    $live_order_z_freight_tax += strval($line_item->TaxAmount);

                    if ($verbose) {
                        echo '         -- Current line item Z-FREIGHT Total: ' . strval($line_item->Amount) . '<br>';
                        echo '         -- Current line item Z-FREIGHT Tax: ' . strval($line_item->TaxAmount) . '<br>';
                    }

                    //continue and skip reset of this process
                    continue;
                }

                if ($verbose) {
                    echo '      -- Looking for ' . $line_item_sku . ' in order...';
                }

                //scan the order items to see if this live order item is there
                $item_found = false;
                foreach ($order_items as $item_id => $item) {
                    $product = $item->get_product();
                    if ($product) {
                        if ($product->get_sku() == $line_item_sku) {
                            $item_found = $item_id;
                        }
                    }
                }

                //if found then check the details (quantity) are correct
                if ($item_found) {
                    if ($verbose) {
                        echo ' Item found!';
                    }

                    $found_item_product_price = wc_get_order_item_meta($item_found, '_product_price', true);
                    $found_item_qty = $order_items[$item_found]->get_quantity();
                    $found_item_tax = $order_items[$item_found]->get_total_tax();
                    $found_item_total = $order_items[$item_found]->get_total();

                    //update quantity if there is a mismatch
                    if ($found_item_qty == $line_item_qty) {
                        if ($verbose) {
                            echo ' Quantity matches!<br>';
                        }
                    } else {
                        if ($verbose) {
                            echo ' Quantity mismatch, updating...<br>';
                        }

                        $update_notification_required = true;

                        //update qty
                        $order_items[$item_found]->set_quantity($line_item_qty);

                        //also update price as this doesn't automatically happen on quantity change
                        $order_items[$item_found]->set_subtotal($line_item_qty * $line_item_price);
                        $order_items[$item_found]->set_total($line_item_qty * $line_item_price);
                    }

                    if ($verbose) {
                        echo '         -- Comparing Woo order item price ' . $found_item_product_price . ' with GT order item price ' . $line_item_price;
                    }

                    //update price if there is a mismatch
                    if ($found_item_product_price == $line_item_price) {
                        if ($verbose) {
                            echo ' - Individual price matches!<br>';
                        }
                    } else {
                        if ($verbose) {
                            echo ' - Individual price mismatch, updating...<br>';
                        }

                        $update_notification_required = true;

                        //update individual price
                        wc_update_order_item_meta($item_found, '_product_price', $line_item_price);

                        //also update price as this doesn't automatically happen on quantity change
                        $order_items[$item_found]->set_subtotal($line_item_qty * $line_item_price);
                        $order_items[$item_found]->set_total($line_item_qty * $line_item_price);
                    }

                    if ($verbose) {
                        echo '         -- Comparing Woo order item tax ' . $found_item_tax . ' with GT order item tax ' . $line_item_tax;
                    }

                    //update line item tax if there is a mismatch
                    if ($found_item_tax == $line_item_tax) {
                        if ($verbose) {
                            echo ' - Item tax matches!<br>';
                        }
                    } else {
                        if ($verbose) {
                            echo ' - Item tax mismatch, updating...<br>';
                        }

                        //do not send order update email notifications on tax changes because of the disparity between
                        //woo and GT in decimal point calculation of tax
                        //see discussion: https://trello.com/c/cji9tP7b/254-shipping-feedback for notes

                        //update item tax
                        $order_items[$item_found]->set_total_tax($line_item_tax);

                        //also update price as this doesn't automatically happen on quantity change
                        $order_items[$item_found]->set_subtotal($line_item_qty * $line_item_price);
                        $order_items[$item_found]->set_total($line_item_qty * $line_item_price);
                    }

                } else {
                    //if not found then check the SKU exists and add it to the order
                    if ($verbose) {
                        echo ' Item not found! Adding item to order...<br>';
                    }

                    //check the product exists
                    $product_exists = SJDisplay_Greentree_Woo::get_woo_product_by_sku($line_item_sku);

                    //if it exists, just add product object to order, easy
                    if ($product_exists) {
                        if ($verbose) {
                            echo '      --- Item exists in system, adding product order!<br>';
                        }
                        $product_to_add = wc_get_product($product_exists[0]);
                        $order->add_product($product_to_add, $line_item_qty);

                        $update_notification_required = true;

                    } else {
                        //if it does not exist, check that the custom line item fee isn't already added
                        if ($verbose) {
                            echo '      --- Item does not exist in system, checking to see if already in order as custom fee...<br>';
                        }

                        //iterate through all order fees
                        $fee_found = false;
                        foreach ($order->get_fees() as $fee) {
                            $fee_get_name = explode(' x ', $fee->get_name());
                            $fee_name = $fee_get_name[1];
                            $fee_qty = $fee_get_name[0];
                            $fee_total = $fee->get_total();

                            //see if SKU exists as fee item
                            if ($fee_name == $line_item_sku) {
                                $fee_found = true;

                                if ($verbose) {
                                    echo '      --- Item found in fee data!';
                                }

                                //check quantity is the same
                                if ($fee_qty == $line_item_qty) {
                                    if ($verbose) {
                                        echo ' Quantity matched!';
                                    }
                                } else {
                                    $fee_set_name = $line_item_qty . ' x ' . $line_item_sku;
                                    $fee->set_name($fee_set_name);

                                    if ($verbose) {
                                        echo ' Quantity updated!';
                                    }

                                    $update_notification_required = true;
                                }

                                //check total is the same
                                if ($fee_total == $line_item->Amount) {
                                    if ($verbose) {
                                        echo ' Total matched!';
                                    }
                                } else {
                                    $fee->set_total(strval($line_item->Amount));
                                    $fee->set_total_tax(strval($line_item->TaxAmount));
                                    $fee->set_taxes(array(
                                        'total' => array(
                                            '1' => strval($line_item->TaxAmount),
                                        ),
                                    ));
                                    if ($verbose) {
                                        echo ' Total and taxes updated!';
                                    }

                                    $update_notification_required = true;
                                }

                                if ($verbose) {
                                    echo '<br>';
                                }
                                break;
                            }
                        }

                        //if it was found as a fee, just save the checks/changes made above
                        if ($fee_found) {
                            $fee->save();
                        } else {
                            //if not found, add it as a new custom fee
                            if ($verbose) {
                                echo '      --- Item not found in fee data, creating custom line item and adding to order!<br>';
                            }

                            $fee_item = new WC_Order_Item_Fee();
                            $fee_item->set_name($line_item_qty . ' x ' . $line_item_sku);
                            $fee_item->set_order_id($order->get_id());
                            $fee_item->set_amount(strval($line_item->Amount));
                            $fee_item->set_total(strval($line_item->Amount));
                            $fee_item->set_total_tax(strval($line_item->TaxAmount));
                            $fee_item->set_taxes(array(
                                'total' => array(
                                    '1' => strval($line_item->TaxAmount),
                                ),
                            ));
                            $fee_item->set_tax_status('taxable');
                            $fee_item->save();
                            $order->add_item($fee_item);

                            $update_notification_required = true;
                        }
                    }
                }
            }
        }

        //update the Woo order z-freight totals if we have some z-freight data from the order
        if ($live_z_freight_data_exists) {
            if ($verbose) {
                echo '   Live order item Z-Freight data was found with a total of...<br>';
                echo '   -- Live Z Freight Total: ' . $live_order_z_freight_total . '<br>';
                echo '   -- Live Z Freight Tax: ' . $live_order_z_freight_tax . '<br>';
                echo '   -- Attempting to update order Z-Freight data...<br>';
            }

            //find the openfreight shipping order item
            foreach ($order->get_items('shipping') as $item_id => $shipping_item_obj) {
                if ($shipping_item_obj->get_method_id() == 'openfreight') {
                    //get current cost and total tax of the openfreight line item (shipping)
                    $current_z_freight_shipping_total_tax = number_format($shipping_item_obj->get_total_tax(), 2);
                    $current_z_freight_shipping_total = $shipping_item_obj->get_total();

                    //update cost and total_tax of the openfreight line item (shipping) if there is a difference
                    if ($current_z_freight_shipping_total != $live_order_z_freight_total) {
                        if ($verbose) {
                            echo '         -- Current Z-FREIGHT Total: ' . $current_z_freight_shipping_total . ' does not match, updating...<br>';
                        }
                        $shipping_item_obj->set_total($live_order_z_freight_total);

                        $update_notification_required = true;
                    } else {
                        if ($verbose) {
                            echo '         -- Current Z-FREIGHT Total: ' . $current_z_freight_shipping_total . ' matches, skipping...<br>';
                        }
                    }

                    if ($current_z_freight_shipping_total_tax != $live_order_z_freight_tax) {
                        if ($verbose) {
                            echo '         -- Current Z-FREIGHT Tax: ' . $current_z_freight_shipping_total_tax . ' does not match, updating...<br>';
                        }
                        $shipping_item_obj->set_taxes(array('total' => array(number_format($live_order_z_freight_tax, 2))));

                        $update_notification_required = true;
                    } else {
                        if ($verbose) {
                            echo '         -- Current Z-FREIGHT Tax: ' . $current_z_freight_shipping_total_tax . ' matches, skipping...<br>';
                        }
                    }
                }
            }
        }

        //compare Woo order items against GT order item skus
        if ($verbose) {
            echo '   Iterating through Woo order items and removing any not in the GT order...<br>';
        }

        foreach ($order_items as $item_id => $item) {
            $product = $item->get_product();
            if ($product) {
                $sku = $product->get_sku();
            } else {
                $sku = '';
            }

            if ($verbose) {
                echo '      SKU in Woo: ' . $sku;
            }

            if (in_array($sku, $live_order_skus)) {
                if ($verbose) {
                    echo ' - Found in GT order itmes! Skipping...<br>';
                }
            } else {
                if ($verbose) {
                    echo ' - Not Found in GT order items! Removing from Woo order!<br>';
                }

                //remove item from cart since it's not in the GT order data
                $order->remove_item($item_id);
                $update_notification_required = true;
            }
        }

        //compare Woo order item fees (custom items) against GT order item skus
        if ($verbose) {
            echo '   Iterating through Woo order item fees (custom items) and removing any not in the GT order...<br>';
        }

        foreach ($order->get_fees() as $order_item_fee) {
            $custom_product = $order_item_fee->get_name();
            $custom_product_sku = explode(" x ", $custom_product);

            if ($verbose) {
                echo '      Custom SKU in Woo: ' . $custom_product_sku[1];
            }

            if (in_array($custom_product_sku[1], $live_order_skus)) {
                if ($verbose) {
                    echo ' - Found in GT order item fees! Skipping...<br>';
                }
            } else {
                if ($verbose) {
                    echo ' - Not Found in GT order items! Removing from Woo order!<br>';
                }

                //remove item from cart since it's not in the GT order data
                $order->remove_item($order_item_fee->get_id());
                $update_notification_required = true;
            }
        }

        //update GT modified user, timestamp and status data on Woo side
        update_post_meta($order_id, 'order_greentree_status', strval($live_order_data->Status));
        update_post_meta($order_id, 'order_greentree_modified_timestamp', strval($live_order_data->ModifiedTimeStamp));
        update_post_meta($order_id, 'order_greentree_modified_user', strval($live_order_data->ModifiedUser));

        //current woo status as default
        $previous_woo_order_status = $order_post->post_status;
        $woo_order_status = $previous_woo_order_status;

        //GT to Woo status mapping
        switch (strval($live_order_data->Status)) {
            case 'Quote':
                $woo_order_status = 'approval';
                break;
            case 'On Backorder':
                $woo_order_status = 'processing';
                break;
            case 'Order Confirmation':
            case 'Entered':
                $woo_order_status = 'on-hold';
                break;
            case 'Invoiced':
                $woo_order_status = 'completed';
                break;
            case 'No Backorders':
            case 'Cancel Always':
            case 'Cancelled':
                $woo_order_status = 'cancelled';
                break;
        }

        //logic to query invoice data if this order is completed and store it in order meta
        //we're checking to see if the order has been completed or is on 'back order' (processing)
        //which are statuses we know will have invoices created
        if ($woo_order_status == 'completed' || $woo_order_status == 'processing') {
            //API call to get AR Invoice data by sales order
            $order_invoice = SJDisplay_Greentree::api_request(
                'ARInvoice',
                array(
                    'salesorder' => $order_greentree_reference_id,
                ),
                $script
            );

            //check if multiple invoices
            $order_invoice_data = false;
            if (sizeof($order_invoice->ARInvoice) > 1) {
                //store all invoices against the order as JSON objects
                foreach ($order_invoice->ARInvoice as $ar_invoice) {
                    $order_invoice_data[] = $ar_invoice;
                }
            } else {
                $order_invoice_data[] = $order_invoice->ARInvoice;
            }

            //store parsed invoice data against the order as JSON object
            update_post_meta($order_id, 'order_greentree_invoice', json_encode($order_invoice_data));
        }

        //logic to query packingslip data if this order has invoice data and store it in order meta
        //packing slip data will contain freight consignment identifiers that we can use on the front-end
        //to query OpenFreight API about tracking/shipping details if need be
        $stored_order_invoices = json_decode(get_post_meta($order_id, 'order_greentree_invoice', true));
        if ($stored_order_invoices) {
            foreach ($stored_order_invoices as $stored_order_invoice) {

                //if Reference exists and not an empty object
                if (!empty($stored_order_invoice->Reference)) {
                    //API call to get SOPackingSlip data by order invoice
                    $packing_slip_data[] = SJDisplay_Greentree::api_request(
                        'SOPackingSlip/' . $stored_order_invoice->Reference,
                        array(),
                        $script
                    );
                }

            }

            //store returned packingslip data against the order as JSON object
            update_post_meta($order_id, 'order_greentree_packing_slip', json_encode($packing_slip_data));
        }

        //update status from mapped values above
        $order->update_status($woo_order_status);

        //recalculate totals & save data
        $order->calculate_totals();
        $order->save();

        //send custom notification of update to user if required
        //this only really cares about 'backorder' status change or 'cancelled' (previously 'wc-on-hold') status
        //completed email triggers are done by default through standard Woo hooks/emails
        if ($update_notification_required) {
            //if order status is on backorder then send the partial delivery/backorder email notification
            if(strval($live_order_data->Status) == 'On Backorder') {
                SJDisplay_Greentree_Woo_Emails::send_order_update_notification($order_id, 'backorder');
            } elseif (strval($live_order_data->Status) == 'Cancelled' && $previous_woo_order_status == 'wc-on-hold') {
                //elseif the new status is 'cancelled' and the old status (woo) was 'on-hold' (GT order confirmation)
                SJDisplay_Greentree_Woo_Emails::send_order_update_notification($order_id, 'cancelled-by-sj');
            }
        }

        return true;
    }

    public static function import_product($item, $parent_customer, $verbose = false, $script)
    {
        if ($verbose) {
            echo 'Item Code (SKU): ' . $item->Code . '<br>';
            echo 'Item Oid String: ' . $item->OidString . '<br>';
            echo 'Item Description: ' . $item->Description . '<br>';
            echo 'Is Active: ' . $item->IsActive . '<br>';
            echo 'Modified Timestamp: ' . $item->ModifiedTimeStamp . '<br>';
            echo 'Quantity Last Updated Timestamp: ' . $item->QtyLastUpdatedTime . '<br>';
            echo 'Analysis Code: ' . $item->AnalysisCode . '<br>';
            echo 'Notes: ' . $item->Notes . '<br>';
            echo 'Image URL: ' . $item->ImageURL . '<br>';
            echo 'Link URL: ' . $item->LinkURL . '<br>';
            echo 'Custom1: ' . $item->Customers->Customer->Custom1 . '<br>';
        }

        $item_height = false;
        $item_length = false;
        $item_width = false;
        $item_weight = false;

        $product_category_data_1 = false;
        $product_category_data_2 = false;
        $online_item = 'false';

        //first try to see if default weight data present
        if (strval($item->Weight) != 0) {
            $item_weight = strval($item->Weight);
            if ($verbose) {
                echo 'Weight: ' . $item->Weight . '<br>';
            }
        }

        //iterating user defined fields and output the name and value
        foreach ($item->UserDefinedFields->UserDefinedField as $item_user_data) {
            if ($verbose) {
                echo $item_user_data->Name . ': ' . $item_user_data->Value . '<br>';
            }

            //if the UDF is Online Item, store this so we can check whether to import this or not
            if ($item_user_data->Name == 'Online Item' && $item_user_data->Value != '') {
                $online_item = strval($item_user_data->Value);
            }

            //map certain fields if they are available
            if ($item_user_data->Name == 'Web Height (mm)') {
                $item_height = strval($item_user_data->Value) / 10;
            }

            if ($item_user_data->Name == 'Web Length (mm)') {
                $item_length = strval($item_user_data->Value) / 10;
            }

            if ($item_user_data->Name == 'Web Width (mm)') {
                $item_width = strval($item_user_data->Value) / 10;
            }

            if ($item_user_data->Name == 'Web Weight (kg)') {
                $item_weight = strval($item_user_data->Value);
            }

            //category mapping (category 1 - this is a product_cat parent)
            if ($item_user_data->Name == 'Customer Category 1' && $item_user_data->Value != '') {
                $product_category_data_1 = strval($item_user_data->Value);
            }

            //category mapping (category 2 - this is a product_cat child)
            if ($item_user_data->Name == 'Customer Category 2' && $item_user_data->Value != '') {
                $product_category_data_2 = strval($item_user_data->Value);
            }
        }

        if ($online_item == 'false') {
            if ($verbose) {
                echo 'Not set as online item! skipping import...<br><br>';
            }
            return false;
        }

        if ($item->Customers->Customer->Custom1 != 'Online') {
            if ($verbose) {
                echo 'Custom1 not set to "Online" Skipping import...<br><br>';
            }
            return false;
        }

        //Product category 1 creation/checking
        if ($product_category_data_1) {
            $product_category_term_1 = get_term_by('name', $product_category_data_1, 'product_cat');
            if ($product_category_term_1) {
                if ($verbose) {
                    echo 'Term exists for this product category: ' . $product_category_term_1->term_id . '<br>';
                }
            } else {
                $product_category_term_1 = wp_insert_term($product_category_data_1, 'product_cat');
                if ($verbose) {
                    echo 'Term does not exist for this product category! Creating it!<br>';
                    echo 'Term ID ' . $product_category_term_1->term_id . ' created!<br>';
                }
            }
        }

        //Product category 2 creation/checking
        if ($product_category_data_2) {
            $product_category_term_2 = get_term_by('name', $product_category_data_2, 'product_cat');
            if ($product_category_term_2) {
                if ($verbose) {
                    echo 'Term exists for this product category: ' . $product_category_term_2->term_id . '<br>';
                }
            } else {
                $product_category_term_2 = wp_insert_term(
                    $product_category_data_2,
                    'product_cat',
                    array(
                        'parent' => $product_category_term_1->term_id,
                    )
                );
                if ($verbose) {
                    echo 'Term does not exist for this product category! Creating it!<br>';
                    echo 'Term ID ' . $product_category_term_2->term_id . ' created!<br>';
                }
            }
        }

        //API sub-request for price data
        $sub_request_args = array(
            'customer' => $parent_customer,
            'action' => 'sellingPrice',
        );
        $stock_item_price = SJDisplay_Greentree::api_request('StockItem/' . $item->Code, $sub_request_args, $script);

        echo 'Customer Price: ' . $stock_item_price->Price . '<br>';

        //default description is item description
        $current_item_parent_customer_description = strval($item->Description);

        //check the customer object for this item to grab the parent customer description and pack size
        $current_item_parent_customer_pack_size = 0;
        if (sizeof($item->Customers->Customer) > 0) {
            foreach ($item->Customers->Customer as $customer_object) {

                //if we have a customer code match to our site
                if ($customer_object->Code == $parent_customer) {
                    $current_item_parent_customer_description = strval(addslashes($customer_object->ItemDescription));
                    $current_item_parent_customer_pack_size = strval($customer_object->PackSize);
                    if ($verbose) {
                        echo 'Customer Description: ' . $current_item_parent_customer_description . '<br>';
                        echo 'Customer Pack Size: ' . $current_item_parent_customer_pack_size . '<br>';
                    }
                }
            }
        }

        //determine if we already have this product in the system
        $product_exists = SJDisplay_Greentree_Woo::get_woo_product_by_sku($item->Code);

        //set default params
        $product_category_1_exists_in_array = false;
        $product_category_2_exists_in_array = false;
        $existing_product_categories = array();

        if (!$product_exists) {
            if ($verbose) {
                echo 'Product does not exist yet...<br>';
            }
            $product = array(
                'post_status' => 'publish',
                'post_title' => strval($item->Description),
                'post_type' => 'product',
            );
            $post_id = wp_insert_post($product);
            $action = 'created';
        } else {
            if (sizeof($product_exists) > 1) {
                if ($verbose) {
                    echo '!!! WARNING !!!<br>Multiple products exist with this SKU! They include:<br>';
                }
                $latest_from_duplicates = null;
                foreach ($product_exists as $product) {
                    if ($verbose) {
                        echo '   Product Post ID: ' . $product->ID . '<br>';
                    }
                    if ($latest_from_duplicates == null) {
                        $latest_from_duplicates = $product->ID;
                    }
                }
                if ($verbose) {
                    echo '   Deleting duplicate posts except ' . $latest_from_duplicates . '...<br>';
                }
                foreach ($product_exists as $product) {
                    if ($product->ID != $latest_from_duplicates) {
                        wp_delete_post($product->ID, true);
                        delete_post_meta($product->ID, '_sku');
                        if ($verbose) {
                            echo '   Product Post ID: ' . $product->ID . ' deleted!<br>';
                        }
                    }
                }
                $post_id = $latest_from_duplicates;
            } else {
                if ($verbose) {
                    echo 'Matching Post ID for this SKU found: ' . $product_exists[0]->ID . '<br>';
                }
                $post_id = $product_exists[0]->ID;
            }

            //check the product_cat terms assigned for this product
            $existing_product_categories = get_the_terms($post_id, 'product_cat');
            if ($existing_product_categories) {
                if ($verbose) {
                    echo '   Product category terms exist, checking array...<br>';
                }
                foreach ($existing_product_categories as $existing_product_category) {
                    if (isset($product_category_term_1)) {
                        if ($existing_product_category->term_id == $product_category_term_1->term_id) {
                            $product_category_1_exists_in_array = true;
                            if ($verbose) {
                                echo '      Product category 1 already exists in this array, skipping...<br>';
                            }
                        }
                    }

                    if (isset($product_category_term_2)) {
                        if ($existing_product_category->term_id == $product_category_term_2->term_id) {
                            $product_category_2_exists_in_array = true;
                            if ($verbose) {
                                echo '      Product category 2 already exists in this array, skipping...<br>';
                            }
                        }
                    }
                }
            } else {
                if ($verbose) {
                    echo '   No product categories exist for this product yet...<br>';
                }
            }

            //check modified timestamp to see if any changes need to be made
            $modified_timestamp = get_post_meta($post_id, 'modified_timestamp', true);
            echo '   Modified timestamp data present: ' . $modified_timestamp . '<br>';
            if (strtotime($item->ModifiedTimeStamp) > strtotime($modified_timestamp)) {
                if ($verbose) {
                    echo '   Timestamp from API data is newer, updating our records...<br>';
                }
                $action = 'updated';
            } else {
                //echo 'No timestamp difference, skipping update...<br>';
                //$action = 'skipped';
                if ($verbose) {
                    echo '   Timestamp difference detection disabled...<br>';
                }
                $action = 'updated';
            }
        }

        if ($action != 'skipped') {
            update_post_meta($post_id, '_sku', strval($item->Code));
            update_post_meta($post_id, '_visibility', 'visible');
            update_post_meta($post_id, '_tax_status', 'taxable');
            update_post_meta($post_id, '_price', strval($stock_item_price->Price));
            update_post_meta($post_id, '_regular_price', strval($stock_item_price->Price));

            //update post title
            wp_update_post(array(
                'ID' => $post_id,
                'post_title' => $current_item_parent_customer_description,
            ));

            if ($item_weight) {
                update_post_meta($post_id, '_weight', $item_weight);
            }

            if ($item_length) {
                update_post_meta($post_id, '_length', $item_length);
            }

            if ($item_width) {
                update_post_meta($post_id, '_width', $item_width);
            }

            if ($item_height) {
                update_post_meta($post_id, '_height', $item_height);
            }

            if ($current_item_parent_customer_pack_size && $current_item_parent_customer_pack_size > 1) {
                update_post_meta($post_id, 'group_of_quantity', $current_item_parent_customer_pack_size);
            }

            update_post_meta($post_id, 'modified_timestamp', strval($item->ModifiedTimeStamp));

            //custom product categories
            if ($product_category_data_1 && !$product_category_1_exists_in_array) {
                if ($verbose) {
                    echo '   Adding product category 1 to this product...<br>';
                }

                if ($existing_product_categories) {
                    foreach ($existing_product_categories as $existing_product_category) {
                        $product_category_terms[] = $existing_product_category->term_id;
                    }
                    $product_category_terms[] = $product_category_term_1->term_id;
                } else {
                    $product_category_terms = array($product_category_term_1->term_id);
                }

                wp_set_post_terms($post_id, $product_category_terms, 'product_cat');
            }

            if ($product_category_data_2 && !$product_category_2_exists_in_array) {
                if ($verbose) {
                    echo '   Adding product category 2 to this product...<br>';
                }

                if ($existing_product_categories) {
                    foreach ($existing_product_categories as $existing_product_category) {
                        $product_category_terms[] = $existing_product_category->term_id;
                    }
                    $product_category_terms[] = $product_category_term_2->term_id;
                } else {
                    $product_category_terms = array($product_category_term_2->term_id);
                }

                wp_set_post_terms($post_id, $product_category_terms, 'product_cat');
            }
        }

        if ($verbose) {
            echo '[Product ID ' . $post_id . ' ' . $action . ']<br>';
        }

        return true;
    }

    public static function import_customer($customer, $parent_customer, $verbose = false, $script)
    {
        echo 'Customer: ' . $customer->Name . '<br>';
        echo 'Customer Code: ' . $customer->Code . '<br>';
        echo 'Parent Customer: ' . $customer->ParentCustomer . '<br>';

        //make sure that user doesn't accidentally import a customer to a site they shouldn't be imported to
        $parent_customer_being_imported = false;
        if ($customer->ParentCustomer != $parent_customer) {
            //check to see if customer code matches the sites parent code config since parent customer import is not disabled
            if ($customer->Code != $parent_customer) {
                if ($verbose) {
                    echo 'Customer code does not match parent customer code config, skipping...<br><br>';
                }
                return false;
            } else {
                $parent_customer_being_imported = true;
            }
        } else {
            $parent_customer_being_imported = true;
        }

        if ($verbose) {
            echo 'Status: ' . $customer->Status . '<br>';
        }

        if ($customer->Status == 'Inactive') {
            if ($verbose) {
                echo 'Inactive! Skipping this customer...<br>';
            }
            return false;
        }

        if ($verbose) {
            echo 'Iterating through Delivery Addresses object...<br>';
        }

        //set default address first
        $customer_contact = strval($customer->Address->Contact);
        $customer_address_1 = strval($customer->Address->Address1);
        $customer_address_2 = strval($customer->Address->Address2);
        $customer_address_3 = strval($customer->Address->Address3);
        $customer_suburb = strval($customer->Address->Suburb);
        $customer_postcode = strval($customer->Address->Postcode);
        $customer_state = strval($customer->Address->State);
        $customer_country = strval($customer->Address->Country);
        $customer_phone_bh = strval($customer->Address->PhoneBH);
        $customer_phone_ah = strval($customer->Address->PhoneAH);
        $customer_fax = strval($customer->Address->Fax);
        $customer_email = strval($customer->Address->Email);
        $customer_web = strval($customer->Address->Web);
        $customer_mobile = strval($customer->Address->Mobile);

        //iterate through delivery addresses and look for primary
        foreach ($customer->DeliveryAddresses->DeliveryAddress as $delivery_address) {
            if ($verbose) {
                echo '   Delivery Address ' . $delivery_address->AddressNumber . '<br>';
            }

            if ($delivery_address->IsPrimaryAddress == 'true') {
                if ($verbose) {
                    echo '   Primary Address found, using delivery address data!<br>';
                }
                $customer_contact = strval($delivery_address->Contact);
                $customer_address_1 = strval($delivery_address->Address1);
                $customer_address_2 = strval($delivery_address->Address2);
                $customer_address_3 = strval($delivery_address->Address3);
                $customer_suburb = strval($delivery_address->Suburb);
                $customer_postcode = strval($delivery_address->Postcode);
                $customer_state = strval($delivery_address->State);
                $customer_country = strval($delivery_address->Country);
                $customer_phone_bh = strval($delivery_address->PhoneBH);
                $customer_phone_ah = strval($delivery_address->PhoneAH);
                $customer_fax = strval($delivery_address->Fax);
                $customer_email = strval($delivery_address->Email);
                $customer_web = strval($delivery_address->Web);
                $customer_mobile = strval($delivery_address->Mobile);
                break;
            } else {
                if ($verbose) {
                    echo '   Not a primary address...<br>';
                }
            }
        }

        if ($verbose) {
            echo 'Contact: ' . $customer_contact . '<br>';
            echo 'Address 1: ' . $customer_address_1 . '<br>';
            echo 'Address 2: ' . $customer_address_2 . '<br>';
            echo 'Address 3: ' . $customer_address_3 . '<br>';
            echo 'Suburb: ' . $customer_suburb . '<br>';
            echo 'Postcode: ' . $customer_postcode . '<br>';
            echo 'State: ' . $customer_state . '<br>';
            echo 'Country: ' . $customer_country . '<br>';
            echo 'Phone Business Hours: ' . $customer_phone_bh . '<br>';
            echo 'Phone After Hours: ' . $customer_phone_ah . '<br>';
            echo 'Fax: ' . $customer_fax . '<br>';
            echo 'Email: ' . $customer_email . '<br>';
            echo 'Web: ' . $customer_web . '<br>';
            echo 'Mobile: ' . $customer_mobile . '<br>';
        }

        //check customer user defined object for user data
        $user_data_array = false;
        if ($customer->UserDefinedFields) {
            if ($verbose) {
                echo 'Customer user defined data present...<br>';
            }
            //store user data in array for JSON
            foreach ($customer->UserDefinedFields->UserDefinedField as $user_data) {
                if ($verbose) {
                    echo '   ' . $user_data->Name . ': ' . $user_data->Value . '<br>';
                }
                $user_data_name_key = 'user_data_' . strtolower(str_replace(' ', '_', str_replace('.', '', strval($user_data->Name))));
                $user_data_array[$user_data_name_key] = array(
                    'name' => strval($user_data->Name),
                    'value' => addslashes(strval($user_data->Value)),
                );
            }
        } else {
            if ($verbose) {
                echo 'No customer user defined data present!<br>';
            }
        }

        $customer_exists = SJDisplay_Greentree::get_customer_by_code($customer->Code);

        if (!$customer_exists) {
            $insert_customer = array(
                'post_status' => 'publish',
                'post_title' => strval($customer->Name),
                'post_type' => 'store',
            );
            $post_id = wp_insert_post($insert_customer);
            $action = 'created';

            if ($verbose) {
                echo '[Customer post created with ID: ' . $post_id . ']<br>';
            }
        } else {
            if (sizeof($customer_exists) > 1) {
                if ($verbose) {
                    echo '!!! WARNING !!!<br>Multiple stores exist with this customer code! They include:<br>';
                }
                $latest_from_duplicates = null;
                foreach ($customer_exists as $existing_customer) {
                    if ($verbose) {
                        echo '   Store Post ID: ' . $existing_customer->ID . '<br>';
                    }
                    if ($latest_from_duplicates == null) {
                        $latest_from_duplicates = $existing_customer->ID;
                    }
                }
                if ($verbose) {
                    echo '   Deleting duplicate posts except ' . $latest_from_duplicates . '...<br>';
                }
                foreach ($customer_exists as $existing_customer) {
                    if ($existing_customer->ID != $latest_from_duplicates) {
                        wp_delete_post($existing_customer->ID, true);
                        delete_post_meta($existing_customer->ID, 'customer_code');
                        if ($verbose) {
                            echo '   Store Post ID: ' . $existing_customer->ID . ' deleted!<br>';
                        }
                    }
                }
                $post_id = $latest_from_duplicates;
            } else {
                if ($verbose) {
                    echo 'Matching Post ID for this customer code found: ' . $customer_exists[0]->ID . '<br>';
                }
                $post_id = $customer_exists[0]->ID;
            }

            $action = 'updated';

            if ($verbose) {
                echo '[Customer exists with post ID: ' . $post_id . ']<br>';
            }
        }

        //update post data (title)
        wp_update_post(array(
            'ID' => $post_id,
            'post_title' => strval($customer->Name),
        ));

        //update ACFs
        update_post_meta($post_id, 'customer_code', strval($customer->Code));
        update_post_meta($post_id, 'parent_customer', strval($customer->ParentCustomer));
        update_post_meta($post_id, 'status', strval($customer->Status));
        update_post_meta($post_id, 'contact', $customer_contact);
        update_post_meta($post_id, 'address', $customer_address_1);
        update_post_meta($post_id, 'address_2', $customer_address_2);
        update_post_meta($post_id, 'address_3', $customer_address_3);
        update_post_meta($post_id, 'suburb', $customer_suburb);
        update_post_meta($post_id, 'postcode', $customer_postcode);
        update_post_meta($post_id, 'state', $customer_state);
        update_post_meta($post_id, 'country', $customer_country);
        update_post_meta($post_id, 'phone_bh', $customer_phone_bh);
        update_post_meta($post_id, 'phone_ah', $customer_phone_ah);
        update_post_meta($post_id, 'fax', $customer_fax);
        update_post_meta($post_id, 'web', $customer_web);
        update_post_meta($post_id, 'mobile', $customer_mobile);

        if ($user_data_array) {
            if (array_key_exists('user_data_web_username', $user_data_array)) {
                update_post_meta($post_id, 'email', $user_data_array['user_data_web_username']['value']);
            }

            if (array_key_exists('user_data_web_password', $user_data_array)) {
                update_post_meta($post_id, 'password', $user_data_array['user_data_web_password']['value']);
            }

            if (array_key_exists('user_data_approval_email_1', $user_data_array)) {
                update_post_meta($post_id, 'approval_email_1', $user_data_array['user_data_approval_email_1']['value']);
            }

            if (array_key_exists('user_data_approval_email_2', $user_data_array)) {
                update_post_meta($post_id, 'approval_email_2', $user_data_array['user_data_approval_email_2']['value']);
            }

            if (array_key_exists('user_data_approval_email_3', $user_data_array)) {
                update_post_meta($post_id, 'approval_email_3', $user_data_array['user_data_approval_email_3']['value']);
            }

            if (array_key_exists('user_data_online_po_number_required', $user_data_array)) {
                if ($user_data_array['user_data_online_po_number_required']['value'] == 'false') {
                    $user_po_number_required = 0;
                } else {
                    $user_po_number_required = 1;
                }
                update_post_meta($post_id, 'po_number_required', $user_po_number_required);
            }

            update_post_meta($post_id, 'user_defined_data', json_encode($user_data_array));
        }

        //if this is a parent customer store, update the Woo settings with the store details
        if ($parent_customer_being_imported) {
            update_option('woocommerce_store_company', strval($customer->Name));
            update_option('woocommerce_store_contact', $customer_contact);
            update_option('woocommerce_store_address', $customer_address_1);
            update_option('woocommerce_store_address_2', $customer_address_2);
            update_option('woocommerce_store_address_3', $customer_address_3);
            update_option('woocommerce_store_city', $customer_suburb);
            update_option('woocommerce_store_postcode', $customer_postcode);
            update_option('woocommerce_store_state', $customer_state);
            update_option('woocommerce_store_phone_bh', $customer_phone_bh);
            update_option('woocommerce_store_phone_ah', $customer_phone_ah);
            update_option('woocommerce_store_fax', $customer_fax);
            update_option('woocommerce_store_email', $customer_email);
            update_option('woocommerce_store_web', $customer_web);
            update_option('woocommerce_store_mobile', $customer_mobile);
        }

        //store manager creation
        if ($user_data_array['user_data_web_username']['value'] != '') {
            $store_manager_user_id = username_exists($user_data_array['user_data_web_username']['value']);
            if (!$store_manager_user_id and email_exists($user_data_array['user_data_web_username']['value']) == false) {
                if ($verbose) {
                    echo '   Store manager does not exist for this store, creating store manager<br>';
                }

                //create user
                $store_manager_user_id = wp_create_user(
                    $user_data_array['user_data_web_username']['value'],
                    $user_data_array['user_data_web_password']['value'],
                    $user_data_array['user_data_web_username']['value']
                );

                //set store_manager role
                $store_manager = new WP_User($store_manager_user_id);
                $store_manager->set_role('store_manager');
            } else {
                if ($verbose) {
                    echo '   Store manager exists for this store with ID: ' . $store_manager_user_id . '<br>';
                }
            }
        }

        //associate store with this user
        if ($verbose) {
            echo '   Updating store associated with this store manager...<br>';
        }
        update_user_meta($store_manager_user_id, 'associated_stores', $post_id);

        //check if area_manager user exists for all 3 approval user fields
        $approval_users = array(
            $user_data_array['user_data_approval_email_1']['value'],
            $user_data_array['user_data_approval_email_2']['value'],
            $user_data_array['user_data_approval_email_3']['value'],
        );

        //create area managers and/or link them to this store
        foreach ($approval_users as $approval_user) {
            if ($approval_user != '') {
                $area_manager_user_id = username_exists($approval_user);
                if (!$area_manager_user_id and email_exists($approval_user) == false) {
                    if ($verbose) {
                        echo '   Area manager does not exist for this store, creating area manager<br>';
                    }

                    //create user
                    $random_password = wp_generate_password(8, false);
                    $area_manager_user_id = wp_create_user($approval_user, $random_password, $approval_user);

                    //set the current store to this users list of managed stores
                    add_user_meta($area_manager_user_id, 'associated_stores', $post_id);

                    //set store_manager role
                    $area_manager = new WP_User($area_manager_user_id);
                    $area_manager->set_role('area_manager');
                } else {
                    if ($verbose) {
                        echo '   Area manager exists for this store with ID: ' . $area_manager_user_id . '<br>';
                    }

                    //get existing associated stores and check to see if we need to append
                    $associated_stores = get_user_meta($area_manager_user_id, 'associated_stores', true);

                    if ($associated_stores) {
                        if ($verbose) {
                            echo '   Stores already associated, checking if current store in array...<br>';
                        }

                        if ((is_array($associated_stores) && in_array($post_id, $associated_stores))
                            || $post_id == $associated_stores
                        ) {
                            if ($verbose) {
                                echo '   Store in array, skipping...<br>';
                            }
                        } else {
                            if ($verbose) {
                                echo '   Store not in array, adding to associated stores<br>';
                            }

                            if (is_array($associated_stores)) {
                                $associated_stores[] = $post_id;
                            } else {
                                $associated_stores_arr = array(
                                    $associated_stores,
                                    $post_id,
                                );
                                $associated_stores = $associated_stores_arr;
                            }

                            //update the associated stores ACF
                            update_user_meta($area_manager_user_id, 'associated_stores', $associated_stores);
                        }
                    } else {
                        if ($verbose) {
                            echo '   No stores associated yet, adding current store<br>';
                        }
                        update_user_meta($area_manager_user_id, 'associated_stores', $post_id);
                    }
                }
            }
        }

        if ($verbose) {
            echo '[Customer ID ' . $post_id . ' ' . $action . ']<br>';
        }

        return true;
    }

    /*
     * Helper function to remove everything except letters and numbers from a string
     */
    public static function clean_string_for_xml($string)
    {
        //clean up hyphens
        $clean_string = str_replace('–', '-', $string);

        //special chars
        $clean_string = htmlspecialchars($clean_string, ENT_SUBSTITUTE|ENT_QUOTES, 'UTF-8');

        //convert special chars encoded ampersand as requested (rather than &amp; via htmlspecialchars)
        $clean_string = str_replace('&amp;', '&#038;', $clean_string);
        
        return $clean_string;
    }

    /*
     * Helper function to write log files
     */
    public static function log_action($source, $message)
    {
        error_log(
            date('Y-m-d h:i:sa') . " - " . $message . "\n",
            3,
            SJDISPLAY_GREENTREE_LOG_DIR . $source . '.log'
        );
    }

    /*
    * Helper function to write log XML that is sent to GT right before send
    */
    public static function log_xml($xml, $order_id, $call) 
    {
        //store the body XML in logs
        error_log(
            $xml,
            3,
            SJDISPLAY_GREENTREE_LOG_DIR . 'xml/' . $order_id . '-' . $call . '-' . time() . '.xml'
        );
    }

    /*
     * Helper function to send emails error notifications
     */
    public static function error_notification_email($subject, $body)
    {
        $to = 'web@sjdg.global';
        $headers = array('Content-Type: text/html; charset=UTF-8');
        wp_mail($to, $subject, $body, $headers);
    }
}
