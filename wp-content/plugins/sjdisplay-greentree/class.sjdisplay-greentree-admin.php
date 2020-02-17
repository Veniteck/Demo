<?php

class SJDisplay_Greentree_Admin
{
    private static $initiated = false;
    private static $admin_page = 'sjdisplay-greentree-settings';
    private static $secret_mask = '********';

    public static function init()
    {
        if (!self::$initiated) {
            self::init_hooks();
        }
    }

    public static function init_hooks()
    {
        self::$initiated = true;

        add_action('admin_menu', array('SJDisplay_Greentree_Admin', 'admin_menu'));
        add_action('admin_init', array('SJDisplay_Greentree_Admin', 'register_plugin_settings'));
        add_action('admin_enqueue_scripts', array('SJDisplay_Greentree_Admin', 'custom_enqueue_assets'));

        add_action('update_option_greentree_api_key',
            array('SJDisplay_Greentree_Admin', 'custom_update_greentree_private_setting'), 10, 3);

        add_action('update_option_greentree_username',
            array('SJDisplay_Greentree_Admin', 'custom_update_greentree_private_setting'), 10, 3);

        add_action('update_option_greentree_password',
            array('SJDisplay_Greentree_Admin', 'custom_update_greentree_private_setting'), 10, 3);

        add_action('update_option_greentree_payment_enabled',
            array('SJDisplay_Greentree_Admin', 'custom_update_option_payment'), 10, 2);
    }

    public static function admin_menu()
    {
        add_menu_page(
            'Green Tree',
            'Green Tree',
            'administrator',
            'sjdisplay-api-settings',
            array('SJDisplay_Greentree_Admin', 'api_settings_page'),
            'dashicons-palmtree'
        );

        add_submenu_page(
            'sjdisplay-api-settings',
            'Green Tree',
            'API Settings',
            'administrator',
            'sjdisplay-api-settings',
            array('SJDisplay_Greentree_Admin', 'api_settings_page'),
            'dashicons-palmtree'
        );

        add_submenu_page(
            'sjdisplay-api-settings',
            'Green Tree',
            'Site Configuration',
            'administrator',
            'sjdisplay-site-config',
            array('SJDisplay_Greentree_Admin', 'site_config_page'),
            'dashicons-palmtree'
        );

        add_submenu_page(
            'sjdisplay-api-settings',
            'Green Tree',
            'Scripts & Endpoints',
            'administrator',
            'sjdisplay-scripts-endpoints',
            array('SJDisplay_Greentree_Admin', 'scripts_endpoints_page'),
            'dashicons-palmtree'
        );
    }

    public static function register_plugin_settings()
    {
        register_setting('greentree-api-settings', 'greentree_endpoint_url');
        register_setting('greentree-api-settings', 'greentree_api_key');
        register_setting('greentree-api-settings', 'greentree_username');
        register_setting('greentree-api-settings', 'greentree_password');
        register_setting('greentree-site-config', 'greentree_customer_code');
        register_setting('greentree-site-config', 'greentree_approval_enabled');
        register_setting('greentree-site-config', 'greentree_payment_enabled');
        register_setting('greentree-site-config', 'greentree_order_api_disable');
        register_setting('greentree-site-config', 'greentree_parent_store_import');
        register_setting('greentree-site-config', 'greentree_area_manager_approval_email_subject');
        register_setting('greentree-site-config', 'greentree_store_manager_approval_email_subject');
    }

    public static function custom_enqueue_assets()
    {
        wp_enqueue_style(
            'custom_wp_admin_css',
            SJDISPLAY_GREENTREE_ASSETS_URL . '/css/admin.css',
            false,
            SJDISPLAY_GREENTREE_VERSION
        );
    }

    public static function get_secret_mask()
    {
        return self::$secret_mask;
    }

    /*
     * Validation functions for GreenTree API settings page changes to help keep secrets masked
     */
    public static function custom_update_greentree_private_setting($old_value, $new_value, $option)
    {
        if ($new_value == SJDisplay_Greentree_Admin::get_secret_mask()) {
            update_option($option, $old_value);
        }
    }

    /*
     * Function to trigger some Woo payment gateway/checkout related changes on sub-site
     * toggle of payment system from the GreenTree plugin settings page
     */
    public static function custom_update_option_payment($old_value, $new_value)
    {
        //toggle check
        if ($new_value == 'on') {
            //payment enabled (turn on Stripe)
            update_option('woocommerce_cheque_settings', array());
            update_option('woocommerce_cod_settings', array());
            update_option('woocommerce_stripe_settings', array(
                'enabled' => 'yes',
                'email' => 'hello@digitalthing.com.au',
                'apple_pay_domain_set' => 'yes',
                'title' => 'Credit Card (Stripe)',
                'description' => 'Pay with your credit card via Stripe.',
                'testmode' => 'yes',
                'test_publishable_key' => 'pk_test_3NyrQFImThiXQkiVY187hfZU',
                'test_secret_key' => 'sk_test_oNRscY5lTEYpoYDin6oQJjqw',
                'inline_cc_form' => 'no',
                'capture' => 'no',
                'three_d_secure' => 'no',
                'stripe_checkout' => 'yes',
                'stripe_checkout_locale' => 'en',
                'stripe_bitcoin' => 'no',
                'payment_request' => 'yes',
                'payment_request_button_type' => 'buy',
                'payment_request_button_theme' => 'dark',
                'payment_request_button_height' => "44",
                'saved_cards' => 'yes',
                'logging' => 'yes',
                'publishable_key' => '',
                'secret_key' => '',
                'statement_descriptor' => '',
                'stripe_checkout_image' => ''
            ));
            update_option('woocommerce_paypal_settings', array());
        } else {
            //payment disabled (turn off Stripe and everything except 'Cheque' - renamed to 'On Account')
            update_option('woocommerce_cheque_settings', array(
                'enabled' => 'yes',
                'title' => 'On Account',
                'description' => 'Order will be paid via account'
            ));
            update_option('woocommerce_cod_settings', array());
            update_option('woocommerce_stripe_settings', array());
            update_option('woocommerce_paypal_settings', array());
        }
    }

    public static function api_settings_page()
    {
        include_once('views/api-settings.php');
    }

    public static function site_config_page()
    {
        include_once('views/site-config.php');
    }

    public static function scripts_endpoints_page()
    {
        include_once('views/scripts-endpoints.php');
    }

    public static function plugin_activation()
    {
    }

    public static function plugin_deactivation()
    {
    }
}
