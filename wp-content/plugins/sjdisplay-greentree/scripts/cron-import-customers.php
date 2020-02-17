<?php
//load wp and greentree class
$base = explode('wp-content/plugins/sjdisplay-greentree/scripts', dirname(__FILE__));
require_once($base[0] . '/wp-load.php');
require_once('../class.sjdisplay-greentree.php');

//config
$config = array(
    'auto-redirect' => true,
    'echo-xml' => false
);

//customers call and base args
$page = 1;
if (isset($_GET['page']) && $_GET['page'] != '') {
    $page = sanitize_text_field($_GET['page']);
}
$pageSize = 30;
if (isset($_GET['pageSize']) && $_GET['pageSize'] != '') {
    $pageSize = sanitize_text_field($_GET['pageSize']);
}

//logging pre call
$current_script = basename(__FILE__, '.php');
SJDisplay_Greentree::log_action($current_script, 'Attempting to run script ' . $current_script . '.php');

//first grab parent customer from config
$parent_customer = esc_attr(get_option('greentree_customer_code'));
if (!$parent_customer) {
    exit('No parent customer specified in site config!');
}

//iterate data and write to DB
echo '<pre>';

echo 'Current Site Parent Customer Code: ' . $parent_customer . '<br><br>';

//check if parent customer store import is enabled
$parent_customer_import = esc_attr(get_option('greentree_parent_store_import'));
if ($parent_customer_import == 'on' && $page == 1) {
    //import parent customer if enabled and on first page of bulk import
    $request_args = array(
        'page' => 1,
        'pageSize' => 1,
        'includeAttachments' => 'true'
    );
    $customer = SJDisplay_Greentree::api_request('Customer/' . $parent_customer, $request_args, $current_script);

    SJDisplay_Greentree::import_customer($customer, $parent_customer, true, $current_script);
    echo '--<br><br>';
}

//get all customers by parent code via API request
$request_args = array(
    'page' => $page,
    'pageSize' => $pageSize,
    'parentCustomer' => $parent_customer,
    'includeAttachments' => 'true'
);
$customers = SJDisplay_Greentree::api_request('Customer', $request_args, $current_script);

if ($config['echo-xml']) {
    print_r($customers);
}

if (sizeof($customers) == 0) {
    exit('No results returned, finishing.');
}

foreach ($customers as $customer) {
    SJDisplay_Greentree::import_customer($customer, $parent_customer, true, $current_script);
    echo '--<br><br>';
}
echo '</pre>';

//redirect page and force pagination
if ($config['auto-redirect']) {
    if (sizeof($customers) < $pageSize) {
        echo 'End of results... finished import';
    } else {
        sleep(3);
        $next_page_query = array(
            'page' => $page + 1,
            'pageSize' => $pageSize
        );
        $redirect_url = $_SERVER['PHP_SELF'] . '?' . http_build_query($next_page_query);

        echo '<meta http-equiv="refresh" content="0;URL=\'' . $redirect_url . '\'">';
    }
}
