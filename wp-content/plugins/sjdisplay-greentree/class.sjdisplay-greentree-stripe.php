<?php

class SJDisplay_Greentree_Stripe
{
    private static $initiated = false;
    protected static $instance = null;
    public static $source = 'class.sjdisplay-greentree-stripe';

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

        //full list of hooks at https://docs.woocommerce.com/document/stripe/

        //custom manual charge capture
        add_action('woocommerce_stripe_process_manual_capture',
            array('SJDisplay_Greentree_Stripe', 'custom_process_manual_capture'), 10, 2);

        //custom validaiton to make sure PO is entered prior to Stripe submit
        add_filter('wc_stripe_validate_checkout_required_fields',
            array('SJDisplay_Greentree_Stripe', 'custom_stripe_required_fields'), 10, 1);

        //custom return true function to enable Stripe receipt emails as requested by Andrew
        add_filter('wc_stripe_send_stripe_receipt', '__return_true');
    }

    public static function custom_stripe_required_fields($required)
    {
        //parse all posted fields into array
        parse_str($_POST['all_fields'], $all_fields);

        //check if order_po_number is empty and if it's required for this customer
        if ($all_fields['order_po_number'] == '' &&
            SJDisplay_Greentree::get_customer_po_number_required(get_current_user_id())
        ) {

            //if so, set it as a required variable so Stripe returns an error before CC input
            $required['order_po_number'] = '';
        }

        return $required;
    }

    //function to capture payment
    //order is a default WC_Order object returned for the order post in question
    public static function custom_process_manual_capture($order, $result)
    {
        SJDisplay_Greentree::log_action(self::$source, 'Capturing Stripe charge!');

        //store stripe result
        update_post_meta($order->get_id(), 'order_stripe_result', json_encode($result));

        //if there was a Stripe Error log it and do nothing else
        if ($result->error) {
            SJDisplay_Greentree::log_action(self::$source, 'Error processing Stripe capture: ' . $result->error->message);

        } else {
            //otherwise, log success and proceed to send AR Receipt call to GT
            SJDisplay_Greentree::log_action(self::$source, 'Stripe charge captured! ID: ' . $result->id);

            //get AR invoice data for this order
            $order_invoice_data = json_decode(get_post_meta($order->get_id(), 'order_greentree_invoice', true));

            //if invoice data exists and stripe result is OK
            if ($order_invoice_data && $result->captured == 1) {

                //send API request
                $receipt_response = SJDisplay_Greentree::api_request(
                    'Receipt',
                    array(
                        'order' => $order,
                        'order_invoice' => $order_invoice_data,
                    ),
                    self::$source,
                    'POST'
                );

                //store receipt response
                update_post_meta($order->ID, 'order_receipt_response', json_encode($receipt_response));
            }
        }
    }
}
