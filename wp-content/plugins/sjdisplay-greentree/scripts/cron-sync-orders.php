<?php
/*
 * Script to sync data between GreenTree and WooCommerce
 * This script will first grab all the sub-site IDs and their sub-site 'customer_code' ACF data
 * it will then iterate through the sub-sites where customer_code is present and grab all orders
 * where a GreenTree reference ID is present. It then polls the GreenTree API SOSalesOrder
 * endpoint with those reference ID's to see if any changes have occurred on the GT side.
 * Finally, the script sync's these changes back across to the Woo side
 */

//load wp and greentree class
$base = explode('wp-content/plugins/sjdisplay-greentree/scripts', dirname(__FILE__));
require_once($base[0] . '/wp-load.php');
require_once('../class.sjdisplay-greentree.php');

//logging pre call
$current_script = basename(__FILE__, '.php');
SJDisplay_Greentree::log_action($current_script, 'Attempting to run script ' . $current_script . '.php');

echo '<pre>';

//get orders where greentree reference is present
$order_query = new WP_Query(
    array(
        'post_type' => 'shop_order',
        'post_status' => array('wc-on-hold', 'wc-pending', 'wc-approval', 'wc-processing'),
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key' => 'order_greentree_reference_id',
                'compare' => 'EXISTS',
            ),
            array(
                'key' => 'order_greentree_reference_id',
                'value' => '',
                'compare' => '!=',
            ),
        ),
    )
);

//orders exist, iterate through them and make calls to the API to get the latest info
if ($order_query->have_posts()) {
    echo 'Orders with GreenTree references exist...<br>';

    foreach ($order_query->posts as $order) {

        $sync_order = SJDisplay_Greentree::sync_order($order, true, $current_script);

        echo '   ---<br>';
    }
} else {
    echo 'No orders with GreenTree references exist!<br>';
}

echo 'sync_finished';