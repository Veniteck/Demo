<?php
/*
Plugin Name: SJ Shipping API Integration
Plugin URI: -
Description: A custom plugin to integrate SJ Display with the Shipping/Freight API
Version: 1.0
Author: Digital Thing
Author URI: http://digitalthing.com.au
License: GPL2
*/

defined('ABSPATH') or die('No script kiddies please!');

define('SJ_SHIPPING_VERSION', '1.0');
define('SJ_SHIPPING_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SJ_SHIPPING_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SJ_SHIPPING_ASSETS_URL', plugin_dir_url(__FILE__) . 'assets/');
define('SJ_SHIPPING_LOG_DIR', plugin_dir_path(__FILE__) . 'logs/');
define('SJ_SHIPPING_API_NAMESPACE', 'sj-shipping/v1');

if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

//custom shipping method via class extension
add_action('woocommerce_shipping_init', function () {
    require_once(plugin_dir_path(__FILE__) . 'class.wc-sj-openfreight-shipping-method.php');
});

//add custom shipping method to available methods
add_filter('woocommerce_shipping_methods', function ($methods) {
    $methods['openfreight'] = 'WC_SJ_Openfreight_Shipping_Method';
    return $methods;
});

//custom openfreight API integration class
require_once(plugin_dir_path(__FILE__) . 'class.sj-openfreight-integration.php');
add_action('init', array('SJ_Openfreight_Integration', 'init'));

