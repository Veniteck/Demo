<?php
/**
 * Customer on-hold order email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/customer-on-hold-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see        https://docs.woocommerce.com/document/template-structure/
 * @author        WooThemes
 * @package    WooCommerce/Templates/Emails
 * @version     2.5.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$subject_order_id = SJDisplay_Greentree_Woo_Emails::get_order_id_for_email_subject($order);

if (get_option('greentree_approval_enabled')) {
    //if this is an area manager then use a different header, since area managers wouldn't (and shouldn't) see this notification
    //but the issue is that on-hold is always sent to the new order creator (area manager when making an amendment) and there is no
    //easy way around this without disabling ALL on-hold emails and triggering them custom - so this is easier
    //now also checks for group manager role (area_manager + store_manager) and sets their subject to the same as a store_manager
    if (
        SJDisplay_Greentree::is_user_manager(get_current_user_id(), 'area_manager') &&
        SJDisplay_Greentree::is_user_manager(get_current_user_id(), 'store_manager') == false
        ) {
        if($action == 'created') {
            $email_header = 'SJ Order #' . $subject_order_id . ' - Created';
        } else {
            $email_header = 'SJ Order #' . $subject_order_id . ' - Amended Order Details';
        }
    } else {
        $email_header = 'SJ Order #' . $subject_order_id . ' - Approval Requested';
    }
    
} else {
    $email_header = 'SJ Order #' . $subject_order_id . ' - Created';
}

/**
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action('woocommerce_email_header', $email_header, $email);?>

<p>Thank you for your order.</p>  

<?php if (get_option('greentree_approval_enabled')): ?>
    <?php  if (
        SJDisplay_Greentree::is_user_manager(get_current_user_id(), 'area_manager') &&
        SJDisplay_Greentree::is_user_manager(get_current_user_id(), 'store_manager') == false
        ) : ?>
        <p>Order details are below.</p>
    <?php else : ?>
        <p>Your order status has changed to Approval Requested. Order details are below.</p>
    <?php endif ?>
<?php else: ?>
    <p>Our experienced team will begin processing your order. Order details are below.</p>
<?php endif?>

<?php if (get_option('greentree_payment_enabled')): ?>
    <p>We will not process your credit card payment until your order is ready for despatched.</p>
<?php endif?>

<?php

/**
 * @hooked WC_Emails::order_details() Shows the order details table.
 * @hooked WC_Structured_Data::generate_order_data() Generates structured data.
 * @hooked WC_Structured_Data::output_structured_data() Outputs structured data.
 * @since 2.5.0
 */
do_action('woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email);

/**
 * @hooked WC_Emails::order_meta() Shows order meta data.
 */
do_action('woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email);

/**
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action('woocommerce_email_footer', $email);
