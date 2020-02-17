<?php
//load wp and greentree class
$base = explode('wp-content/plugins/sjdisplay-greentree/scripts', dirname(__FILE__));
require_once($base[0] . '/wp-load.php');
require_once('../class.sjdisplay-greentree.php');
require_once('../class.sjdisplay-greentree-woo.php');

//config
$config = array(
    'auto-redirect' => true
);

//stock items call and base args
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

//iterate data and write to DB
echo '<pre>';

//first grab parent customer from config
$parent_customer = esc_attr(get_option('greentree_customer_code'));
if (!$parent_customer) {
    exit('No parent customer specified in site config!');
}

echo 'Grabbing products for parent customer: ' . $parent_customer . '<br>';
echo 'Page ' . $page . ' (' . $pageSize . ' items per page)<br>';
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

if (!$stock_items) {
    echo 'API Request Error! View log below.<br>';
    echo SJDISPLAY_GREENTREE_PLUGIN_URL . 'logs/' . $current_script . '.log';
    exit();
}

//Check products returned
if (sizeof($stock_items) == 0) {
    exit('No results returned...');

} else {
    foreach ($stock_items as $item) {
        SJDisplay_Greentree::import_product($item, $parent_customer, true, $current_script);
        echo '--<br><br>';
    }
}

echo '</pre>';

//redirect page and force pagination
if ($config['auto-redirect']) {
    sleep(1);
    $next_page_query = array(
        'page' => $page + 1,
        'pageSize' => $pageSize,
        'parentCustomer' => $parent_customer
    );
    $redirect_url = $_SERVER['PHP_SELF'] . '?' . http_build_query($next_page_query);
    echo '<meta http-equiv="refresh" content="0;URL=\'' . $redirect_url . '\'">';
}
