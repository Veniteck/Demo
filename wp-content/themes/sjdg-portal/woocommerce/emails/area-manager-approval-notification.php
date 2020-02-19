<?php
/**
 * Area Manager approval notification email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/area-manager-approval-notification.php.
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

/**
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action('woocommerce_email_header', 'SJ Order # ' . $subject_order_id . ' - Approval Requested', $email);

global $woocommerce;
?>

<p>Approval required for below Order. Please action the Business Approver process by selecting one of the following options:</p>

<table id="approval-buttons-table">
    <tr>
        <td>
            <a href="<?=get_bloginfo('url')?>/approve-order/?order_id=<?=$order->get_id()?>&view=approve-order" class="approval-button approve">
                Approve Order
            </a>
        </td>
        <td>
            <a href="<?=$woocommerce->cart->get_cart_url()?>?order_id=<?=$order->get_id()?>&view=cart&amend_order" class="approval-button amend">
                Amend Order
            </a>
        </td>
        <td>
            <a href="<?=get_bloginfo('url')?>/cancel-order/?order_id=<?=$order->get_id()?>&view=cancel-order" class="approval-button decline">
                Decline Order
            </a>
        </td>
    </tr>
</table>

<br>

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
