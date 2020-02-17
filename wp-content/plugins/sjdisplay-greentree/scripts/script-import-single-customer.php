<?php
//load wp and greentree class
$base = explode('wp-content/plugins/sjdisplay-greentree/scripts', dirname(__FILE__));
require_once($base[0] . '/wp-load.php');
require_once('../class.sjdisplay-greentree.php');

//query string check for single SKU import testing
$single_customer_import = false;
if (isset($_GET['customer']) && $_GET['customer'] != '') {
    $single_customer_import = $_GET['customer'];
} else {
    echo 'You must supply a customer code as the "customer" query string parameter...';
    exit();
}

//logging pre call
$current_script = basename(__FILE__, '.php');
SJDisplay_Greentree::log_action($current_script, 'Attempting to run script ' . $current_script . '.php');

//first grab parent customer from config
$parent_customer = esc_attr(get_option('greentree_customer_code'));
if (!$parent_customer) {
    exit('No parent customer specified in site config!');
}

//check if parent customer store import is disabled
$parent_customer_import_disabled = esc_attr(get_option('greentree_parent_store_import_disable'));

//API request
$request_args = array(
    'page' => 1,
    'pageSize' => 1,
    'includeAttachments' => 'true'
);
$customer = SJDisplay_Greentree::api_request('Customer/' . $single_customer_import, $request_args, $current_script);

//iterate data and write to DB
echo '<pre>';

if (sizeof($customer) == 0) {
    exit('No results returned, finishing.');
}

echo 'Current Site Parent Customer Code: ' . $parent_customer . '<br><br>';

SJDisplay_Greentree::import_customer($customer, $parent_customer, true, $current_script);

echo '</pre>';
