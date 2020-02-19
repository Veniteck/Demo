<?php
/**
 * Email Addresses
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/email-addresses.php.
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
 * @version     3.2.1
 */

if (!defined('ABSPATH')) {
    exit;
}

$text_align = is_rtl() ? 'right' : 'left';

$customer = get_user_by('id', $order->get_customer_id());

$stores = get_field('associated_stores', $customer);

$_stores = [];

if ($stores) {
    if (is_array($stores)) {
        foreach ($stores as $store) {
            $_stores[] = get_the_title($store);
        }
        $stores_display = join(', ', $_stores);
        $stores_display = '';
    } else {
        $stores_display = get_the_title($stores);
    }
}
?>
<table id="addresses" cellspacing="0" cellpadding="0"
       style="width: 100%; vertical-align: top; margin-bottom: 40px; padding:0;" border="0">
    <tr>
        <td style="text-align:<?php echo $text_align; ?>; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; border:0; padding:0;"
            valign="top" width="100%">
            <h2><?php _e('Shipping address', 'woocommerce'); ?> <?= $stores_display ?></h2>

            <address class="address">
                <?php echo ($address = $order->get_formatted_billing_address()) ? $address : __('N/A', 'woocommerce'); ?>
                <?php if ($order->get_billing_phone()) : ?>
                    <br/><?php echo esc_html($order->get_billing_phone()); ?>
                <?php endif; ?>
                <?php if ($order->get_billing_email()) : ?>
                    <p><?php echo esc_html($order->get_billing_email()); ?></p>
                <?php endif; ?>
            </address>
        </td>
    </tr>
</table>