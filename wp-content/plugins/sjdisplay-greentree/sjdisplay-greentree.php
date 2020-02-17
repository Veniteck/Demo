<?php
/*
Plugin Name: Green Tree
Plugin URI: -
Description: A custom plugin to integrate SJ Display with the Green Tree API
Version: 1.0
Author: Digital Thing
Author URI: http://digitalthing.com.au
License: GPL2
*/

defined('ABSPATH') or die('No script kiddies please!');

define('SJDISPLAY_GREENTREE_VERSION', '1.0');
define('SJDISPLAY_GREENTREE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SJDISPLAY_GREENTREE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SJDISPLAY_GREENTREE_SCRIPTS_URL', plugin_dir_url(__FILE__) . 'scripts/');
define('SJDISPLAY_GREENTREE_ASSETS_URL', plugin_dir_url(__FILE__) . 'assets/');
define('SJDISPLAY_GREENTREE_LOG_DIR', plugin_dir_path(__FILE__) . 'logs/');
define('SJDISPLAY_GREENTREE_API_NAMESPACE', 'green-tree/v1');

if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

require_once(plugin_dir_path(__FILE__) . 'class.sjdisplay-greentree.php');
require_once(plugin_dir_path(__FILE__) . 'class.sjdisplay-greentree-woo.php');
require_once(plugin_dir_path(__FILE__) . 'class.sjdisplay-greentree-stripe.php');

add_action('init', array('SJDisplay_Greentree', 'init'));
add_action('init', array('SJDisplay_Greentree_Woo', 'init'));
add_action('init', array('SJDisplay_Greentree_Stripe', 'init'));

if (is_admin()) {
    require_once(plugin_dir_path(__FILE__) . 'class.sjdisplay-greentree-admin.php');
    add_action('init', array('SJDisplay_Greentree_Admin', 'init'));
    register_activation_hook(__FILE__, array('SJDisplay_Greentree_Admin', 'plugin_activation'));
    register_deactivation_hook(__FILE__, array('SJDisplay_Greentree_Admin', 'plugin_deactivation'));
}
