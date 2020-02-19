<?php

class SJDisplay_Greentree_Woo_Emails
{
    private static $initiated = false;
    protected static $instance = null;
    public static $source = 'class.sjdisplay-greentree-woo-emails';

    protected function __construct()
    {
    }

    public static function get_instance()
    {
        null === self::$instance and self::$instance = new self;
        return self::$instance;
    }

    public static function init()
    {
        if (!self::$initiated) {
            self::init_hooks();
        }
    }

    /*
     * Init all hooks and WP endpoints
     */
    private static function init_hooks()
    {
        self::$initiated = true;

        //custom email subjects
        add_filter('woocommerce_email_subject_new_order', array('SJDisplay_Greentree_Woo_Emails', 'custom_email_subject_new_order'), 10, 2);
        add_filter('woocommerce_email_subject_customer_on_hold_order', array('SJDisplay_Greentree_Woo_Emails', 'custom_email_subject_on_hold'), 10, 2);
        add_filter('woocommerce_email_subject_customer_completed_order', array('SJDisplay_Greentree_Woo_Emails', 'custom_email_subject_completed'), 10, 2);

        //custom email recipient hooks
        add_filter('woocommerce_email_recipient_customer_completed_order', array('SJDisplay_Greentree_Woo_Emails', 'custom_email_cc_area_managers_and_admin'), 10, 2);
        add_filter('woocommerce_email_recipient_customer_processing_order', array('SJDisplay_Greentree_Woo_Emails', 'custom_email_cc_area_managers_and_admin'), 10, 2);
        add_filter('woocommerce_email_recipient_cancelled_order', array('SJDisplay_Greentree_Woo_Emails', 'custom_email_cc_area_managers_and_admin'), 10, 2);

        //final wp_mail filter to conditionally apply logic to emails
        add_filter('wp_mail', array('SJDisplay_Greentree_Woo_Emails', 'custom_conditional_mail_logic'));

        //disable the default Woo processing email across the board for any site config
        update_option('woocommerce_customer_processing_order_settings', array('enabled' => 'no'));
    }

    public static function custom_conditional_mail_logic($args)
    {
        //do not send the area manager new order notification (as it's an amendment)
        if ($args['subject'] == 'SJ Order Amended Order Details') {
            $args["message"] = '';
        }

        return $args;
    }

    public static function get_order_id_for_email_subject($order)
    {
        //get GT reference ID and set if exists
        $order_greentree_reference_id = get_post_meta($order->get_id(), 'order_greentree_reference_id', true);
        if ($order_greentree_reference_id != '') {
            $subject_order_id = $order_greentree_reference_id;
        } else {
            $subject_order_id = $order->get_id();
        }

        return $subject_order_id;
    }

    public static function custom_email_subject_on_hold($formated_subject, $order)
    {
        $subject_order_id = SJDisplay_Greentree_Woo_Emails::get_order_id_for_email_subject($order);
        if (get_option('greentree_approval_enabled')) {
            //store manager subject line vs area manager subject line (when area manager makes amendment 
            //and is sent an 'on hold' notificaiton) - also checks if not a group manager (store_manager + area_manager)
            if (
                SJDisplay_Greentree::is_user_manager(get_current_user_id(), 'area_manager') &&
                SJDisplay_Greentree::is_user_manager(get_current_user_id(), 'store_manager') == false
                ) {
                //area manager notification subject - used to be caught at custom_conditional_mail_logic() filter and not sent
                $subject = 'SJ Order Amended Order Details';
            } else {
                $subject = 'SJ Order #' . $subject_order_id . ' - Approval Requested';
            }
        } else {
            $subject = 'SJ Order #' . $subject_order_id . ' - Created';
        }

        return $subject;
    }

    public static function custom_email_subject_new_order($formated_subject, $order)
    {
        return 'SJ Order #' . SJDisplay_Greentree_Woo_Emails::get_order_id_for_email_subject($order) . ' - Created';
    }

    public static function custom_email_subject_completed($formated_subject, $order)
    {
        return 'SJ Order #' . SJDisplay_Greentree_Woo_Emails::get_order_id_for_email_subject($order) . ' - Completed';
    }

    public static function custom_email_cc_area_managers_and_admin($recipient, $object)
    {   
        //if approval system enabled, add area manager to email recipients
        if (get_option('greentree_approval_enabled') && !is_null($object)) {
            //get the user id's associated store CPT
            $user_store_id = get_field('associated_stores', $object->get_user());

            //ensure we just get the first store id the user is associated with
            if (is_array($user_store_id)) {
                $user_associated_store_id = $user_store_id[0];
            } else {
                $user_associated_store_id = $user_store_id;
            }

            //find area managers associated with this store id
            $store_area_managers = SJDisplay_Greentree::get_store_area_managers($user_associated_store_id);

            //if we have some, iterate through and add them to recipients
            if ($store_area_managers) {
                foreach ($store_area_managers as $store_area_manager) {
                    $recipient .= ', ' . $store_area_manager->data->user_email;
                }
            }
        }

        //add admin
        $recipient .= ', ' . get_bloginfo('admin_email');

        return $recipient;
    }

    /*
     * Function to send email notification to area manager and also
     * write order note once completed. Status will be set to on-hold
     */
    public static function send_area_manager_approval_notification($area_managers, $order_id)
    {
        //load the mailer class
        $mailer = WC()->mailer();

        //get order object
        $order = wc_get_order($order_id);

        //get order id for subject
        $subject_order_id = SJDisplay_Greentree_Woo_Emails::get_order_id_for_email_subject($order);
        $subject = 'SJ Order #' . $subject_order_id . ' - Approval Requested';

        //iterate through area_managers and send
        foreach ($area_managers as $area_manager) {
            //format the email
            $content = wc_get_template_html('emails/area-manager-approval-notification.php', array(
                'order' => $order,
                'email_heading' => $subject,
                'sent_to_admin' => false,
                'plain_text' => false,
                'email' => $mailer,
            ));

            //send the email through wordpress
            $headers = "Content-Type: text/html\r\n";
            $send_wp_mail = $mailer->send($area_manager->data->user_email, $subject, $content, $headers);

            if ($send_wp_mail) {
                $order = wc_get_order($order_id);
                $order->add_order_note('Approval request sent to area manager ' . $area_manager->data->user_email);
            }
        }
    }

    /**
     * Function to send email notification when an area manager 'creates' an order on behalf of a store
     */
    public static function send_area_manager_created_email($order_id) {
        //get order object
        $order = wc_get_order($order_id);
        
        //send 'created' email in this instance to the area manager, admin and to the store that the order is on behalf of
        //first get the store_ordered_for meta of this order
        $store_ordered_for = get_post_meta($order_id, 'store_ordered_for', true);

        //next get the associated store_manager (can be multiple) for this store
        $store_post_id = SJDisplay_Greentree::get_customer_by_code($store_ordered_for);
        $store_managers = SJDisplay_Greentree::get_store_store_managers($store_post_id[0]->ID);
        
        //get the area manager email that made the order
        $order_user = $order->get_user();
        $area_manager_email = $order_user->data->user_email;

        $mailer = WC()->mailer();

        //get order id for subject
        $subject_order_id = SJDisplay_Greentree_Woo_Emails::get_order_id_for_email_subject($order);
        $subject = 'SJ Order #' . $subject_order_id . ' - Created';

        //format the email
        $content = wc_get_template_html('emails/customer-on-hold-order.php', array(
            'order' => $order,
            'email_heading' => $subject,
            'sent_to_admin' => true,
            'plain_text' => false,
            'email' => $mailer,
            'action' => 'created'
        ));

        //prepare recipients
        $recipients = $area_manager_email;
        foreach($store_managers as $store_manager) {
            $recipients .= ', ' . $store_manager->data->user_email;
        }
        $recipients .= ', ' . get_bloginfo('admin_email');

        //send the email through wordpress
        $headers = "Content-Type: text/html\r\n";
        $send_wp_mail = $mailer->send($recipients, $subject, $content, $headers);
    }

    /*
     * Function to send email notifications to store manager when action (approve, cancel) is taken
     */
    public static function send_order_update_notification($order_id, $action)
    {
        //get the full order object
        $order = wc_get_order($order_id);
        $subject_order_id = SJDisplay_Greentree_Woo_Emails::get_order_id_for_email_subject($order);

        //get current user data and customer data
        $current_user_id = get_current_user_id();
        $current_user = get_userdata($current_user_id);
        $customer = $order->get_user();

        $to[] = $customer->data->user_email;
        $subject = 'SJ Order # ' . $subject_order_id;

        //check if customer was a store manager and approval system active
        if (SJDisplay_Greentree::is_user_manager($customer->data->ID, 'store_manager') && get_option('greentree_approval_enabled')) {
            if ($action == 'cancelled') {
                $subject .= ' - Declined by Approver';
            } elseif ($action == 'approved') {
                $subject .= ' - Approved';
            } elseif ($action == 'updated') {
                $subject .= ' - Amended by Approver';
            }
        } else {
            if ($action == 'cancelled-by-sj') {
                $subject .= ' - Cancelled by SJ';
            } elseif ($action == 'backorder') {
                $subject .= ' - On Backorder';
            }
        }

        //load the mailer class
        $mailer = WC()->mailer();

        //format the email
        $content = wc_get_template_html('emails/customer-approval-status-changed.php', array(
            'order' => $order,
            'email_heading' => $subject,
            'sent_to_admin' => false,
            'plain_text' => false,
            'email' => $mailer,
            'action' => $action,
        ));

        //also send the email notification to admin
        $to[] = get_bloginfo('admin_email');

        //first get the users store CPT id
        $user_store_id = get_field('associated_stores', $customer);

        //ensure we just get the first store id the user is associated with
        if (is_array($user_store_id)) {
            $user_associated_store_id = $user_store_id[0];
        } else {
            $user_associated_store_id = $user_store_id;
        }

        //next find area managers associated with this store id
        $store_area_managers = SJDisplay_Greentree::get_store_area_managers($user_associated_store_id);

        //if we have some, iterate through and add them to recipients
        if ($store_area_managers) {
            foreach ($store_area_managers as $store_area_manager) {
                $to[] = $store_area_manager->data->user_email;
            }
        }

        //send the email through wordpress
        $headers = "Content-Type: text/html\r\n";
        $send_wp_mail = $mailer->send($to, $subject, $content, $headers);

        //add custom order note
        if ($send_wp_mail) {
            if ($current_user_id == 1) {
                $order->add_order_note('Order ' . $action);
            } else {
                $order->add_order_note('Order ' . $action . ' by Area Manager: ' . $current_user->data->display_name);
            }
        }
    }
}
