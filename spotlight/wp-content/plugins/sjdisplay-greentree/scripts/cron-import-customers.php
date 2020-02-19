<?php
//load wp and greentree class
$base = explode('wp-content/plugins/sjdisplay-greentree/scripts', dirname(__FILE__));
require_once($base[0] . '/wp-load.php');
require_once('../class.sjdisplay-greentree.php');

//logging pre call
$current_script = basename(__FILE__, '.php');
SJDisplay_Greentree::log_action($current_script, 'Attempting to run script ' . $current_script . '.php');

//first grab parent customer from config
$parent_customer = esc_attr(get_option('greentree_customer_code'));
if (!$parent_customer) {
    exit('No parent customer specified in site config!');
}

//base args and config
$import_run = true;
$page = 1;
$pageSize = 30;
$parent_customer_import = esc_attr(get_option('greentree_parent_store_import'));

//echo out which customer we're grabbing data for
echo '<pre>';
echo 'Current Site Parent Customer Code: ' . $parent_customer . '<br><br>';

//API Calls till finished
while($import_run) {

    //check if parent customer store import is enabled
    if ($parent_customer_import == 'on' && $page == 1) {
        echo 'Parent Customer Import enabled, grabbing parent customer data first...<br>';

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

    //if no results
    if (sizeof($customers) == 0) {
        echo 'No results returned, finishing script...<br>';
        $import_run = false;
    } else {
        foreach ($customers as $customer) {
            SJDisplay_Greentree::import_customer($customer, $parent_customer, true, $current_script);
            echo '--<br><br>';
        }
    }

    //increment
    $page++;
}

echo '<br>cron_finished';
echo '</pre>';
