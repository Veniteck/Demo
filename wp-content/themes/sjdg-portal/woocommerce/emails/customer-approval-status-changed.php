<?php
/**
 * Customer approval status changed
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/customer-approval-status-changed.php.
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

//get status of order to determine what has changed
$order_status = $order->get_status();
if ($action == 'cancelled') {
    $email_heading = 'SJ Order # ' . $subject_order_id . ' - Declined by Approver';
}  elseif ($action == 'cancelled-by-sj') {
    $email_heading = 'SJ Order # ' . $subject_order_id . ' - Cancelled by SJ';
} elseif ($action == 'approved') {
    $email_heading = 'SJ Order # ' . $subject_order_id . ' - Approved';
} elseif ($action == 'updated') {
    $email_heading = 'SJ Order # ' . $subject_order_id . ' - Amended by Approver';
}  elseif ($action == 'backorder') {
    $email_heading = 'SJ Order # ' . $subject_order_id . ' - On Backorder';
} else {
    $email_heading = 'SJ Order # ' . $subject_order_id;
}

/**
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action('woocommerce_email_header', $email_heading, $email);?>

<?php if ($action == 'cancelled'): ?>
    <p>Your order status has changed to Declined by Approver. Order details are below.</p>
<?php elseif ($action == 'cancelled-by-sj'): ?>
    <p>Your order status has changed to Cancelled by SJ. Order details are below</p>
<?php elseif ($action == 'approved'): ?>
    <p>Your order status has changed to Approved. Order details are below.</p>
<?php elseif ($action == 'updated'): ?>
    <p>Your order status has changed to Amended by Approver. Order details are below.</p>
<?php elseif ($action == 'backorder'): ?>
    <p>Your order status has changed to On Backorder and your order will be shipped as discussed. Order details are below.</p>
<?php endif?>

<?php if ($cancellation_note = get_post_meta($order->get_id(), 'order_cancellation_note', true)): ?>
    <p><strong>Comment:</strong> <?=$cancellation_note?></p>
<?php endif?>

<?php if($action == 'updated') :
    //get the old order id
    $amended_order_post_id = get_post_meta($order->get_id(), 'amended_order_post_id', true);
    $amended_order_id = get_post_meta($amended_order_post_id, 'order_greentree_reference_id', true);
    if ($amended_order_id == '') {
        $amended_order_id = $amended_order_post_id;
    }
    ?>
    <p><strong>Note:</strong> Previous order <?= $amended_order_id ?> has been cancelled</p>
<?php endif ?>

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
