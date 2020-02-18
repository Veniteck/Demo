<?php

class SJ_Openfreight_Integration
{
    private static $initiated = false;
    private static $api_url = 'https://api.openfreight.com.au';
    private static $api_user = 'SJAPI';
    private static $api_key = 'c7a4ec852ee61e11eabab6d635b6649b0d14e6b6dffae33fe9e68b98eb1ca4b6';
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

        //filter shipping method label on frontend
        add_filter('woocommerce_cart_shipping_method_full_label', array('SJ_Openfreight_Integration',
            'custom_cart_shipping_method_full_label'), 10, 1);

        //filter shipped_via text out
        add_filter('woocommerce_order_shipping_to_display_shipped_via', array('SJ_Openfreight_Integration',
            'custom_order_shipping_to_display_shipped_via'), 10, 1);

        //custom meta boxes
        add_action('add_meta_boxes', array('SJ_Openfreight_Integration', 'custom_meta_boxes'));

        //custom pre cart notification message area
        add_action('woocommerce_before_cart', array('SJ_Openfreight_Integration', 'custom_before_cart_messages'));

        //custom checkout notification message area after checkout submit button
        add_action('woocommerce_review_order_after_payment', array('SJ_Openfreight_Integration', 'custom_before_checkout_messages'));

        //woo order thankyou page (store openfreight meta data for meta box display on order view)
        add_action('woocommerce_thankyou', array('SJ_Openfreight_Integration', 'custom_woocommerce_thankyou'), 10, 1);

        //woo order thankyou header text filter if shipping error
        add_filter('woocommerce_thankyou_order_received_text', array('SJ_Openfreight_Integration', 'custom_thankyou_order_received_text'));
    }

    //filter hook to remove 'shipped via' text in Woo
    public static function custom_order_shipping_to_display_shipped_via($shipped_via)
    {
        return '';
    }

    //filter hook to clean up label display (remove the label title) on the front-end
    public static function custom_cart_shipping_method_full_label($label)
    {
        if (is_checkout() || is_cart()) {
            $label_explode = explode(': ', $label);
            return $label_explode[1];
        } else {
            return $label;
        }
    }

    public static function custom_before_cart_messages()
    {
        //check our openfreight session data to see if we an error message to display
        $openfreight_session_data = WC()->session->get('openfreight_data');

        //if the API failed and payment system is enabled, show error notification area
        if ($openfreight_session_data['api_failed'] && get_option('greentree_payment_enabled')) {
            //empty for now, use this partial for API error notifications on cart if required 
        }
    }

    public static function custom_before_checkout_messages()
    {
        //check our openfreight session data to see if we an error message to display
        $openfreight_session_data = WC()->session->get('openfreight_data');

        //if the api failed and payment system is enabled, show error notification area
        if ($openfreight_session_data['api_failed'] && get_option('greentree_payment_enabled')) {
            echo '<div class="sj-shipping-checkout-notification">
                <h4>Note: Shipping Charge TBC: Please press "PLACE ORDER"</h4>
                <p>Your order will be entered and we will be in touch to finalise shipping charges.</p>
                <p>Once shipping charges are agreed to, we will finalise payment</p>
            </div>';
        }
    }

    public static function custom_thankyou_order_received_text($text)
    {
        //check our openfreight session data to see if we an error message to display
        $openfreight_session_data = WC()->session->get('openfreight_data');

        //if there is no quote there was an error and payment system is enabled, update the thankyou notice text
        if (!$openfreight_session_data['quote'] && $openfreight_session_data['api_failed'] && get_option('greentree_payment_enabled')) {
            $text = '<strong>Thank you. Your order has been received but the Shipping Charge TBC:</strong><br>';
            $text .= 'Your order has been entered and we will be in touch to finalise shipping charges. ';
            $text .= 'Once shipping charges are agreed to, we will finalise payment';
        }

        return $text;
    }

    /*
     * This function checks the default WC() session shipping data in order to compare our
     * OpenFreight session data package hash, which allows us to know if any changes to the cart/shipping
     * were made that warrant a call to OpenFreight
     * It also checks to ensure the payment system is enabled, if the payment system is not enabled then
     * it returns $0 as these sites require Z-FREIGHT to be sent as $0
     * Payment system enable/disable is set via options "get_option('greentree_payment_enabled')"
     */
    public static function is_updated_quote_required($session_shipping_data)
    {
        $update_required = false;
        $session_openfreight_data = WC()->session->get('openfreight_data');
        $payment_system_enabled = get_option('greentree_payment_enabled');

        //ensure payment system is enabled first
        if($payment_system_enabled) {

            //if we have OpenFreight session data (the API has been called at least once before)
            if (isset($session_openfreight_data)) {
                if ($session_openfreight_data['package_hash'] != $session_shipping_data['package_hash']) {
                    //if the OpenFreight session package hash doesn't match the session shipping data package hash
                    //then it means the cart has changed and we should call OpenFreight again to get an updated quote
                    $update_required = true;
                }

                //if the api call failed in an earlier attempt, force an update call
                if(isset($session_openfreight_data['api_failed']) && $session_openfreight_data['api_failed']) {
                    $update_required = true;
                }
            } else {
                //OpenFreight has not been called yet, do it now
                $update_required = true;
            }
            
        }

        return $update_required;
    }

    /*
     * Custom meta box registration for orders and wherever else required in Woo
     */
    public static function custom_meta_boxes()
    {
        //OpenFreight order data meta
        add_meta_box(
            'openfreight-quote-meta-box',
            'OpenFreight Data',
            array('SJ_Openfreight_Integration', 'custom_meta_box_openfreight_quote_data'),
            'shop_order',
            'normal',
            'default'
        );
    }

    public static function custom_meta_box_openfreight_quote_data()
    {
        include_once('partials/woo-admin-order-openfreight-data.php');
    }

    /*
     * Wrapper function which prepares everything to make the call to GetCostEstimate via API and then
     * stores all response and passes back to the shipping method. It first uses is_updated_quote_required()
     * to make sure we only prepare and call the API when absolutely necessary
     */
    public static function get_quote($rate, $margin)
    {
        //get session shipping data and check if new quote is required
        $session_shipping_data = WC()->session->get('shipping_for_package_0');
        if (SJ_Openfreight_Integration::is_updated_quote_required($session_shipping_data)) {

            //default params for API
            //qty is hardcoded to 1 as all items will be bundled into 1 x package with all weights and dimensions
            //calculated using functions below (as per supplied formula from excel)
            $total_qty = 0;
            $total_weight = 0;
            $total_cubic_meters = 0;
            $package_length = 0;
            $package_width = 0;
            $package_height = 0;
            $all_measurements = array();

            //iterate through package contents and add to item arrays
            foreach ($rate['package']['contents'] as $package_content) {
                //add qty to total qty counter
                $total_qty += $package_content['quantity'];

                //calculate the weight for this item (qty * weight) and add it to the total weight variable
                $total_weight += ($package_content['quantity'] * $package_content['data']->get_weight());

                //calculate the total cubic meters by adding this items cubic meters to the total cubic variable
                //our data is in cm, so we divide each measurement by 100 to get meters value, multiply by qty
                $item_cubic_m = $package_content['quantity'] * ($package_content['data']->get_length() / 100) *
                    ($package_content['data']->get_width() / 100) *
                    ($package_content['data']->get_height() / 100);

                $total_cubic_meters += $item_cubic_m;

                //add length, width and height to all measurements tracking array so we can determine the max
                //measurement which is used as part of the package height calculation formula
                $all_measurements[] = $package_content['data']->get_length();
                $all_measurements[] = $package_content['data']->get_width();
                $all_measurements[] = $package_content['data']->get_height();
            }

            //calculate the height of the package by grabbing the longest measurement (length, width, or height) from
            //all the times we have, and then add 2cm to this measurement
            $package_height = max($all_measurements) + 2;

            //calculate the length of the package by using the following formula from excel
            //Excel function = =SQRT(SUMPRODUCT(C6:C12,H6:H12)*1000000/C18)+2

            //mapped these are:
            //C6:C12 is the sum of qty column = $items_qty array
            //C18 = our $package_height calculation
            //H6:H12 is the sum of the m3 column = $total_cubic_meters

            //translating Excel functions to PHP functions
            //SUMPRODUCT(C6:C12,H6:H12) = $total_cubic_meters
            //this is because $total_cubic_meters is calculated for each line item above by multiplying the qty and the m3
            //rather than the way Excel does things using SUMPRODUCT

            //Our Final PHP translation is
            $package_length = sqrt(($total_cubic_meters * 1000000) / $package_height) + 2;

            //package width is same as length calc
            $package_width = $package_length;

            //package cubic cm now calculated off of the above length width and height calcs
            $package_cubic_cm = number_format($package_length * $package_width * $package_height);
            $package_cubic_m = number_format(($package_length * $package_width * $package_height) / 100, 2);

            //receiver data from package
            $receiver_town = $rate['package']['destination']['city'];
            $receiver_postcode = $rate['package']['destination']['postcode'];
            $sender_town = 'Knoxfield';
            $sender_postcode = 3180;

            //setup quote params for API call
            $quote_params = array(
                'request' => 'GetCostEstimate',
                'estimate_date' => date('Y-m-d H:i:s', time()),
                'sender_town' => $sender_town,
                'sender_postcode' => $sender_postcode,
                'receiver_town' => $receiver_town,
                'receiver_postcode' => $receiver_postcode,
                'service_code' => '',
                'autobest_only' => true,
                'items_qty' => array(1),
                'items_length' => array(number_format($package_length, 2)),
                'items_width' => array(number_format($package_width, 2)),
                'items_height' => array($package_height),
                'items_weight' => array($total_weight),
                'items_cube' => array($package_cubic_m),
                'items_isdg' => array(false)
            );

            //GetCostEstimate call to OpenFreight API for quote
            $quote = SJ_Openfreight_Integration::api_request(
                $quote_params,
                'class.sj-openfreight-integration.php'
            );

            //if API call success set the rate cost
            if (isset($quote->success) && $quote->success) {
                //get the object key of the best quote (since this will be dynamic)
                $autobest_code_key = key($quote->cost_estimate);

                //get quote cost and calculate any margin total
                $quote_cost = $quote->cost_estimate->$autobest_code_key->est_cost;
                if ($margin == 0 || $margin == '') {
                    $quote_total = $quote_cost;
                } else {
                    $quote_total = number_format(($quote_cost / $margin), 2);
                }

                $quote_return = array(
                    'total' => $quote_total,
                    'meta_data' => array(
                        'Carrier' => $quote->cost_estimate->$autobest_code_key->carrier_name,
                        'Service' => $quote->cost_estimate->$autobest_code_key->service_name,
                        'Quote' => $quote->cost_estimate->$autobest_code_key->est_cost,
                        'Est Cost Tax' => $quote->cost_estimate->$autobest_code_key->est_cost_tax,
                        'Est Cost Fuel' => $quote->cost_estimate->$autobest_code_key->est_cost_fuel,
                        'Est Cost Fees' => $quote->cost_estimate->$autobest_code_key->est_cost_fees
                    )
                );

                //update session
                WC()->session->set('openfreight_data',
                    array(
                        'package_hash' => $session_shipping_data['package_hash'],
                        'openfreight_sent' => $quote_params,
                        'quote' => $quote_return,
                        'api_failed' => false,
                    )
                );
            } else {
                //email SJ that error has occurred
                $message = '<p>There was an error returned from OpenFreight for the following data.</p>';
                $message .= '<p><strong>Data Sent:</strong></p>';
                $message .= '<pre>';
                $message .= print_r($quote_params, true);
                $message .= '</pre>';
                $message .= '<p><strong>Data Returned:</strong></p>';
                $message .= '<pre>';
                $message .= print_r($quote, true);
                $message .= '</pre>';
                SJ_Openfreight_Integration::error_notification_email('Shipping API Error', $message);

                //update session
                WC()->session->set('openfreight_data',
                    array(
                        'package_hash' => $session_shipping_data['package_hash'],
                        'openfreight_sent' => $quote_params,
                        'quote' => false,
                        'api_failed' => true,
                    )
                );

                $quote_return = false;
            }

        } else {
            //else new quote not required because shipping session hash hasn't changed - so just stored data
            $openfreight_session_data = WC()->session->get('openfreight_data');
            $quote_return = $openfreight_session_data['quote'];
        }

        return $quote_return;
    }

    /*
     * API call wrapper
     */
    public static function api_request($params, $source = '')
    {
        //log action
        self::log_action($source, 'Attempting API call ' . $params['request'] . ' with params: ' . json_encode($params));

        //append authentication
        $params['username'] = self::$api_user;
        $params['key'] = self::$api_key;

        //basic request args, content type must be application/json, method must be POST
        $request_args = array(
            'headers' => array(
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($params),
            'method' => 'POST'
        );

        //make the call
        $response = wp_remote_post(self::$api_url, $request_args);

        if (!is_wp_error($response)) {
            //call OK, log response
            if ($response['response']['code'] == 200) {
                $log_message = 'API call for ' . $params['request'] . ' OK! ';

            } else {
                //not 200, failed, log response
                $log_message = 'API call for ' . $params['request'] . ' failed! ';
            }

            //log first part (HTTP Response code and messaging)
            $log_message .= 'Response code: ' . $response['response']['code'] . '. ';
            $log_message .= 'Response message: ' . $response['response']['message'];
            self::log_action($source, $log_message);

            if ($response['response']['code'] == 200) {
                //API response OK, check response success/error to see if any issues
                $body = json_decode(wp_remote_retrieve_body($response));
                if ($body->error_code) {
                    //error
                    $log_message = 'API returned an error!';
                    $log_message .= ' API Error Code: ' . $body->error_code;
                    $log_message .= ' API Error Message: ' . $body->error_message;
                } else {
                    //success
                    $log_message = 'API returned success! Results: ' . $body->results;
                }

                //log API response
                self::log_action($source, $log_message);

                $return = $body;
            } else {
                $return = $response['response'];
            }
        } else {
            //wp_remote_post error
            self::log_action($source, 'WP Remote Get Error for call ' . $params['request'] . ': ' . $response->get_error_message());
            $return = $response;
        }

        return $return;
    }

    /*
     * Woo order thank-you page hook
     * Note: this is useful for any functionality that must run last during order creation journey
     */
    public static function custom_woocommerce_thankyou($order_id)
    {
        //check OpenFreight session data and write it to the order
        $openfreight_session_data = WC()->session->get('openfreight_data');

        //if we have data, write it
        if ($openfreight_session_data['openfreight_sent']) {
            update_post_meta($order_id, 'order_data_to_openfreight', json_encode($openfreight_session_data['openfreight_sent']));
        }

        if ($openfreight_session_data['quote']) {
            update_post_meta($order_id, 'order_openfreight_response', json_encode($openfreight_session_data['quote']));
        }
    }

    /*
     * Helper function to write log files
     */
    public static function log_action($source, $message)
    {
        error_log(
            date('Y-m-d h:i:sa') . " - " . $message . "\n",
            3,
            SJ_SHIPPING_LOG_DIR . $source . '.log'
        );
    }

    /*
     * Helper function to send emails error notifications
     */
    public static function error_notification_email($subject, $body)
    {
        $to = 'web@sjdg.global';
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        //disabling for now
        //wp_mail($to, $subject, $body, $headers);
    }
}
