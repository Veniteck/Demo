<?php
//load wp and greentree class
$base = explode('wp-content/plugins/sjdisplay-greentree/scripts', dirname(__FILE__));
require_once($base[0] . '/wp-load.php');
require_once('../class.sjdisplay-greentree.php');
require_once('../class.sjdisplay-greentree-woo.php');

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

//echo out which customer we're grabbing data for
echo '<pre>';
echo 'Grabbing products for parent customer: ' . $parent_customer . '<br>';

//API Calls till finished
while($import_run) {
    
    echo '<br>';
    echo 'API Call for page ' . $page . ' (' . $pageSize . ' items per page)<br>';
    echo '-------<br><br>';

    //API request args
    $request_args = array(
        'page' => $page,
        'pageSize' => $pageSize,
        'isActive' => 'true',
        'customer' => $parent_customer
    );

    //API call
    $stock_items = SJDisplay_Greentree::api_request('StockItem', $request_args, $current_script);

    //check no error in return data
    if (!$stock_items) {
        echo 'API Request Error! View log below.<br>';
        echo SJDISPLAY_GREENTREE_PLUGIN_URL . 'logs/' . $current_script . '.log';
        exit();
    }

    //Check products returned
    if (sizeof($stock_items) == 0) {
        //if nothing returned, finish
        echo 'No results returned, finishing script...<br>';
        $import_run = false;

    } else {
        foreach ($stock_items as $item) {
            SJDisplay_Greentree::import_product($item, $parent_customer, true, $current_script);
            echo '--<br><br>';
        }
    }

    //increment
    $page++;
} 

echo '<br>cron_finished';
echo '</pre>';
