<?php
//load wp and greentree class
$base = explode('wp-content/plugins/sjdisplay-greentree/scripts', dirname(__FILE__));
require_once($base[0] . '/wp-load.php');
require_once('../class.sjdisplay-greentree.php');
require_once('../class.sjdisplay-greentree-woo.php');

//query string check for single SKU import testing
$single_sku_import = false;
if (isset($_GET['sku']) && $_GET['sku'] != '') {
    $single_sku_import = $_GET['sku'];
} else {
    exit('You must supply a product SKU as the "sku" query string parameter...');
}

//first grab parent customer from config
$parent_customer = esc_attr(get_option('greentree_customer_code'));
if (!$parent_customer) {
    exit('No parent customer specified in site config!');
}

//logging pre call
$current_script = basename(__FILE__, '.php');
SJDisplay_Greentree::log_action($current_script, 'Attempting to run script ' . $current_script . '.php');

$parent_customer = 20706;

//API request
$request_args = array(
    'page' => 1,
    'pageSize' => 1,
    'customer' => $parent_customer
);
$item = SJDisplay_Greentree::api_request('StockItem/' . $single_sku_import, $request_args, $current_script);

//iterate data and write to DB
echo '<pre>';

//first grab parent customer from config
$parent_customer = esc_attr(get_option('greentree_customer_code'));
if (!$parent_customer) {
    exit('No parent customer specified in site config!');
}

if ($item instanceof SimpleXMLElement) {
    SJDisplay_Greentree::import_product($item, $parent_customer, true, $current_script);
} else {
    exit('No results returned, finishing.');
}

echo '</pre>';
