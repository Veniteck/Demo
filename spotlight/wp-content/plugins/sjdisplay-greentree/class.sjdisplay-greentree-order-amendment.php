<?php

class SJDisplay_Greentree_Order_Amendment
{
    private static $initiated = false;
    protected static $instance = null;
    public static $source = 'class.sjdisplay-greentree-order-amendment';

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

        //order status set to "approval" to "on-hold" (Pending approval to Being processed)
        add_action('woocommerce_order_status_approval_to_on-hold', array('SJDisplay_Greentree_Order_Amendment', 'custom_order_status_approval_to_on_hold'), 10, 1);

        //order status set to "cancelled"
        add_action('woocommerce_order_status_cancelled', array('SJDisplay_Greentree_Order_Amendment', 'custom_order_status_cancelled'), 10, 1);

        //action hook for cart view pre template render to check if order amendment logic needs to occur
        add_action('template_include', array('SJDisplay_Greentree_Order_Amendment', 'custom_cart_amendment_load'), 10, 1);

        //action hook for checkout view pre template render to check if order amendment logic needs to occur
        add_action('template_include', array('SJDisplay_Greentree_Order_Amendment', 'custom_checkout_amendment_load'), 10, 1);

        //woo order thankyou page (check if order is an amendment order and execute amendment based logic)
        add_action('woocommerce_thankyou', array('SJDisplay_Greentree_Order_Amendment', 'custom_woocommerce_thankyou'), 20, 1);

        //woo before cart hook, used to display amendment message/notification if it is an amendment session
        add_action('woocommerce_before_cart', array('SJDisplay_Greentree_Order_Amendment', 'custom_woocommerce_before_cart'), 10, 1);

        //woo before checkout hook, used to display amendment message/notification if it is an amendment session
        add_action('woocommerce_before_checkout_form', array('SJDisplay_Greentree_Order_Amendment', 'custom_woocommerce_before_checkout'), 10, 1);

        //woo thank you message filter if amendment message/notification is required
        add_filter('woocommerce_thankyou_order_received_text', array('SJDisplay_Greentree_Order_Amendment', 'custom_woocommerce_thankyou_text'), 10, 2);
    }

    //function that fires on the 'approve' order landing page which is accessed via approval notification emails
    public static function custom_order_email_action_approve($order_id)
    {
        //make sure user is area-manager
        if (!SJDisplay_Greentree::is_user_manager(get_current_user_id(), 'area_manager')) {
            return array(
                'result' => false,
                'message' => 'Only area managers can approve orders!'
            );
        }

        //first check the status of the order
        $order = new WC_Order($order_id);

        if ($order->has_status('on-hold')) {
            //order is already approved
            return array(
                'result' => false,
                'message' => 'Order has already been approved.'
            );
        } elseif ($order->has_status('approval')) {
            //order not yet approved, update the status, this will trigger order status change hooks like
            //woocommerce_order_status_approval_to_on-hold which runs custom_order_status_approval_to_on_hold function
            //we only allow approval to happen if the current status is 'wc-approval' or 'approval' if using has_status() like above
            $order->update_status('on-hold');

            return array(
                'result' => true,
                'message' => 'Order has been approved!'
            );

        } else {
            //order is not processing (on-hold) or waiting approval ('approval') so we can just ignore this order action now
            return array(
                'result' => false,
                'message' => 'Order cannot be approved because of current status.'
            );
        }
    }

    //function that fires when Woo order status wc-approval to wc-on-hold (from pending approval to approved)
    //the order is first deleted in GT and then resent using the new status and the same reference ID as the deleted order
    //this is because GT does not allow us to edit order status, so instead we delete the order and recreate it with the new status
    public static function custom_order_status_approval_to_on_hold($order_id)
    {
        //get reference id and status if they exist for this order
        $order_greentree_reference_id = get_post_meta($order_id, 'order_greentree_reference_id', true);
        $order_greentree_status = get_post_meta($order_id, 'order_greentree_status', true);

        //if approval system in play and we have order reference ID and quote status, delete the order first
        if (get_option('greentree_approval_enabled')
            && $order_greentree_status == 'Quote'
            && $order_greentree_reference_id
        ) {
            //delete the order from GT first
            SJDisplay_Greentree_Woo::delete_order_from_greentree($order_greentree_reference_id);

            //resend the order to GT
            SJDisplay_Greentree_Woo::send_order_to_greentree($order_id, 'wc-greentree', 'Order Confirmation');
        }

        //send notification to store manager
        SJDisplay_Greentree_Woo_Emails::send_order_update_notification($order_id, 'approved');
    }

    //function that handles custom cart loading for the order amendment workflow
    public static function custom_cart_amendment_load($template)
    {
        //if we are on the cart page and we have an order_id and amend_order query strings
        if (is_cart() && isset($_GET['order_id']) && $_GET['order_id'] != '' && isset($_GET['amend_order'])) {

            //make sure user is area-manager
            if (!SJDisplay_Greentree::is_user_manager(get_current_user_id(), 'area_manager')) {
                if(isset($_SESSION['order_amendment'])) {
                    unset($_SESSION['order_amendment']);
                }
                wp_die('Only area managers are allowed to amend orders');
            }

            //order amendment logic starts here by first getting the original order object
            $order_id = esc_attr($_GET['order_id']);
            $order = new WC_Order($order_id);

            //if the order status is not 'pending approval' (wc-approval or 'approval' when using has_status()) then exit
            if (!$order->has_status('approval')) {
                if(isset($_SESSION['order_amendment'])) {
                    unset($_SESSION['order_amendment']);
                }
                WC()->cart->empty_cart();
                wp_die('This order can not be amended!');
            }

            //get the store that placed this order
            $order_user_id = $order->get_user_id();
            $order_data_sent_to_gt = json_decode(get_post_meta($order_id, 'order_data_to_greentree', true));
            $order_store_post_id = SJDisplay_Greentree::get_customer_by_code($order_data_sent_to_gt->customer_code);

            //check the current logged in user is an approver for this store
            $current_user_associated_stores = get_field('associated_stores', 'user_' . get_current_user_id());

            if (in_array($order_store_post_id[0]->ID, $current_user_associated_stores)) {
                //store some session flags for this amendment process
                session_start();
                $_SESSION['order_amendment'] = array(
                    'order_id' => esc_attr($_GET['order_id']),
                    'user_id' => $order_user_id,
                    'store_code' => $order_data_sent_to_gt->customer_code,
                    'store_post_id' => $order_store_post_id[0]->ID,
                    'order_po_number' => get_post_meta($order_id, 'order_po_number', true),
                    'order_first_name' => $order->get_billing_first_name(),
                    'order_last_name' => $order->get_billing_last_name(),
                    'order_note' => $order->get_customer_note()
                );

                //first clear the current cart
                WC()->cart->empty_cart();

                //load the cart with the same order items
                foreach ($order->get_items() as $order_item_id => $order_item) {
                    WC()->cart->add_to_cart($order_item->get_product_id(), $order_item->get_quantity(), $order_item->get_variation_id());
                }

                //redirect, to trigger calcs and also clear query string which allows modifications right from the cart page
                wp_redirect(wc_get_cart_url());

            } else {
                //kill the order amendment flag/session as this user is not allowed to amend orders for the store that ordered
                if(isset($_SESSION['order_amendment'])) {
                    unset($_SESSION['order_amendment']);
                }

                //die, do not allow user to amend this order as they are not associated with the store that made the order
                wp_die('You are not associated with the store that placed this order and cannot amend it');
            }
        } else {
            //kill the order_amendment flag/session as this isn't a cart amendment process
            if(isset($_SESSION['order_amendment'])) {
                unset($_SESSION['order_amendment']);
            }
        }

        //if we are on the cart page and a cancel_amend link was clicked
        if (is_cart() && isset($_GET['cancel_amendment'])) {
            session_start();

            //kill the 'amendment' session details
            if(isset($_SESSION['order_amendment'])) {
                unset($_SESSION['order_amendment']);
            }

            //empty the cart
            WC()->cart->empty_cart();
        }

        return $template;
    }

    //function that handles custom checkout loading for the order amendment workflow
    public static function custom_checkout_amendment_load($template)
    {
        //if we are on the checkout page and we have amendment flag session data
        //also ensure that we are not on the order received page
        session_start();
        if (is_checkout() && isset($_SESSION['order_amendment']) && !is_order_received_page()) {

            //make sure user is area-manager
            if (!SJDisplay_Greentree::is_user_manager(get_current_user_id(), 'area_manager')) {
                wp_die('Only area managers are allowed to amend orders');
            }

            //set 'store-selected' get query string var to be the store code of the original order
            //this will trigger the logic in SJDisplay_Greentree_Woo::custom_checkout_form_values() to grab
            //billing and shipping field values from the store CPT
            if (!isset($_GET['store-selected'])) {
                wp_redirect(wc_get_checkout_url() . '?store-selected=' . $_SESSION['order_amendment']['store_code']);
            }
        }

        return $template;
    }

    //function that handles custom message/notification on cart page prior to cart display
    public static function custom_woocommerce_before_cart()
    {
        //check if this is an amendment session and display message accordingly
        if (isset($_SESSION['order_amendment'])) {
            $original_order_ref = get_post_meta($_SESSION['order_amendment']['order_id'], 'order_greentree_reference_id', true);
            $cancel_amend_url = wc_get_cart_url() . '?cancel_amendment';
            $amendment_notice = 'Cart contents loaded from order ' . $original_order_ref . ' for amendment.';
            $amendment_notice .= ' <a href="' . $cancel_amend_url . '">Click here</a> to abandon amendment process and clear cart.';
            echo '<p class="woocommerce-notice woo-amendment-warning"><strong>' . $amendment_notice . '</strong></p>';
        }
    }

    //function that handles custom message/notification on checkout page prior to form
    public static function custom_woocommerce_before_checkout()
    {
        //check if this is an amendment session and display message accordingly
        if (isset($_SESSION['order_amendment'])) {
            $original_order_ref = get_post_meta($_SESSION['order_amendment']['order_id'], 'order_greentree_reference_id', true);
            $cancel_amend_url = wc_get_cart_url() . '?cancel_amendment';
            $amendment_notice = 'You are about to place an amendment order for order ' . $original_order_ref . '.';
            $amendment_notice .= ' <a href="' . $cancel_amend_url . '">Click here</a> to abandon amendment process and clear cart.';
            echo '<p class="woocommerce-notice woo-amendment-warning"><strong>' . $amendment_notice . '</strong></p>';
        }
    }

    //function that handles custom message/notification on thankyou page
    public static function custom_woocommerce_thankyou_text($message, $order)
    {
        if (isset($_SESSION['order_amendment'])) {
            $original_order_ref = get_post_meta($_SESSION['order_amendment']['order_id'], 'order_greentree_reference_id', true);
            $message = 'Amendment order placed for order ' . $original_order_ref . '. The original order has been cancelled.';
        }

        return $message;
    }

    //function that fires before thank you page loads, here we can execute amendment based logic
    //such as assigning the order to the correct user and cancelling the previous order
    public static function custom_woocommerce_thankyou($order_id)
    {
        //if this is an amendment session
        if (isset($_SESSION['order_amendment'])) {
            //assign the new order to the original orderer user id
            update_post_meta($order_id, '_customer_user', $_SESSION['order_amendment']['user_id']);

            //assign the new order email address to the original ordered email address
            update_post_meta(
                $order_id,
                '_billing_email',
                get_post_meta($_SESSION['order_amendment']['order_id'], '_billing_email', true)
            );

            //cancel the first order in Woo
            $original_order = new WC_Order($_SESSION['order_amendment']['order_id']);
            $original_order->update_status('cancelled');

            //add meta in new order which keeps track of the old order id
            update_post_meta($order_id, 'amended_order_post_id', $_SESSION['order_amendment']['order_id']);

            //add cancellation note to first order
            SJDisplay_Greentree_Order_Amendment::custom_order_add_cancellation_note($_SESSION['order_amendment']['order_id'], 'Cancelled via order amendment');

            //cancel the original order in GT via 'action=cancel' and 'CancelStatus' param in order API call
            $original_order_greentree_reference_id = get_post_meta($_SESSION['order_amendment']['order_id'], 'order_greentree_reference_id', true);
            SJDisplay_Greentree_Woo::cancel_order_in_greentree($_SESSION['order_amendment']['order_id'], $original_order_greentree_reference_id);

            //send the original a customer notification of the new order (amendment)
            SJDisplay_Greentree_Woo_Emails::send_order_update_notification($order_id, 'updated');

            //kill the session
            unset($_SESSION['order_amendment']);
        }
    }

    //function that fires on the 'cancel' order landing page which is accessed via approval notification emails
    public static function custom_order_email_action_cancel($order_id)
    {
        //get the order object
        $order = new WC_Order($order_id);

        //get the GT reference ID
        $greentree_reference_id = get_post_meta($order_id, 'order_greentree_reference_id', true);

        if ($order->has_status('cancelled')) {
            //order is already cancelled
            return array(
                'result' => false,
                'message' => 'Order has already been cancelled.'
            );

        } elseif ($order->has_status('approval')) {
            //change the order status
            $order->update_status('cancelled');

            //cancel the original order in GT
            SJDisplay_Greentree_Woo::cancel_order_in_greentree($order_id, $greentree_reference_id);

            //send notification to store
            SJDisplay_Greentree_Woo_Emails::send_order_update_notification($order_id, 'cancelled');

            return array(
                'result' => true,
                'message' => 'Order has been cancelled!'
            );
        } else {
            //order is not cancelled or waiting approval so we can just ignore this order action now
            return array(
                'result' => false,
                'message' => 'Order cannot be cancelled.'
            );
        }
    }

    //function that fires when Woo order status is set to cancelled and stores an admin cancellation note
    public static function custom_order_status_cancelled($order_id)
    {
        if (isset($_POST['order-cancellation-note']) && $_POST['order-cancellation-note'] != '') {
            update_post_meta($order_id, 'order_cancellation_note', esc_attr($_POST['order-cancellation-note']));
        }
    }

    //function that is fired from the 'cancel' order landing page if a cancellation note form submission is made
    public static function custom_order_add_cancellation_note($order_id, $cancellation_note)
    {
        //get the order object first
        $order = new WC_Order($order_id);

        //add cancellation note to order notes
        $create_cancellation_order_note = $order->add_order_note('Cancellation Note: ' . $cancellation_note);

        //add cancellation note to custom order meta (used for notifications in previous amendment system)
        $cancellation_meta_update = update_post_meta($order_id, 'order_cancellation_note', $cancellation_note);

        //send the cancellation note to GT
        SJDisplay_Greentree_Woo::send_order_to_greentree(
            $order_id,
            $order->get_status(),
            get_post_meta($order_id, 'order_greentree_status', true),
            array('StandardText' => $cancellation_note)
        );

        //response
        if ($create_cancellation_order_note && $cancellation_meta_update) {
            return array(
                'result' => true,
                'message' => 'Cancellation note has been added, thank you.'
            );
        } else {
            return array(
                'result' => false,
                'message' => 'Cancellation note could note be added, check your comment and try again.'
            );
        }
    }
}
