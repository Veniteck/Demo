<?php
/**
 * Order details table shown in emails.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/email-order-details.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates/Emails
 * @version 3.3.1
 */

/**
 * @var WC_Order $order
 */

if (!defined('ABSPATH')) {
    exit;
}

$text_align = is_rtl() ? 'right' : 'left';

do_action('woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text, $email); ?>

<table class="td" cellspacing="0" cellpadding="6"
       style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
    <tbody>
    <tr>
        <th class="td" scope="col" style="text-align:<?php echo esc_attr($text_align); ?>;">ORDER NUMBER:</th>
        <td class="td"
            style="text-align:<?php echo esc_attr($text_align); ?>; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
            <?php
            if ($sent_to_admin) {
                echo '<a class="link" href="' . esc_url($order->get_edit_order_url()) . '">' . $order->get_order_number() . '</a>';
                echo ' <small style="font-size: 10px;">(Click on Order # to view your status anytime)</small>';
            } else {
                echo '<a class="link" href="' . esc_url($order->get_view_order_url()) . '">' . $order->get_order_number() . '</a>';
                echo ' <small style="font-size: 10px;">(Click on Order # to edit/view this order)</small>';
            }
            ?>
        </td>
    </tr>
    <tr>
        <th class="td" scope="col" style="text-align:<?php echo esc_attr($text_align); ?>;">DATE:</th>
        <td class="td"
            style="text-align:<?php echo esc_attr($text_align); ?>; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;"><?= wc_format_datetime($order->get_date_created()) ?></td>
    </tr>
    <tr>
        <th class="td" scope="col" style="text-align:<?php echo esc_attr($text_align); ?>;">EMAIL:</th>
        <td class="td"
            style="text-align:<?php echo esc_attr($text_align); ?>; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;"><?= $order->get_billing_email() ?></td>
    </tr>
    <tr>
        <th class="td" scope="col" style="text-align:<?php echo esc_attr($text_align); ?>;">PURCHASE ORDER NO:</th>
        <td class="td"
            style="text-align:<?php echo esc_attr($text_align); ?>; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
            <?php
            $order_num = get_post_meta($order->get_id(), 'order_po_number', true);
            if ($order_num) {
                echo $order_num;
            }
            ?>
        </td>
    </tr>
    <tr>
        <th class="td" scope="col" style="text-align:<?php echo esc_attr($text_align); ?>;">SHIPPING TRACKING NO:</th>
        <td class="td"
            style="text-align:<?php echo esc_attr($text_align); ?>; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
            <?php if ($order_packing_slips = json_decode(get_post_meta($order->get_id(), 'order_greentree_packing_slip', true))) :
				$tracking_base_url = 'https://sjdg.global/track?consignment=';
                ?>
                <section class="woocommerce-packing-slip-details">

                    <table class="woocommerce-table woocommerce-table--order-details shop_table order_details">
                        <tr>
                            <th>Reference</th>
                            <th>Data</th>
                            <th>Tracking</th>
                        </tr>
                        <?php foreach ($order_packing_slips as $packing_slip) :
                            if (is_array($packing_slip->LineItems->LineItem)) {
                                $line_items = $packing_slip->LineItems->LineItem;
                            } else {
                                $line_items[] = $packing_slip->LineItems->LineItem;
                            }
                            ?>
                            <?php foreach ($line_items as $line_item) :
                            if ($line_item->StockItem == 'Z-FREIGHT') :
                                $consignment_number_array = (array)$line_item->ConsignNumber;
                                if (!empty($consignment_number_array)) :
                                    ?>
                                    <tr>
                                        <td><?= $packing_slip->Reference ?></td>
                                        <td>
                                            <strong>Consignment Number: </strong>
                                            <a href="<?= $tracking_base_url . strval($line_item->ConsignNumber) ?>"
                                               target="_blank">
                                                <?= $line_item->ConsignNumber ?>
                                            </a>
                                        </td>
                                        <td>
                                            <a href="<?= $tracking_base_url . strval($line_item->ConsignNumber) ?>"
                                               target="_blank">
                                                View tracking status
                                            </a>
                                        </td>
                                    </tr>
                                <?php endif; endif;
                        endforeach ?>
                        <?php endforeach ?>
                    </table>

                </section>
            <?php else: ?>
                TBC
            <?php endif ?>
        </td>
    </tr>
    </tbody>
</table>

<br>

<?php
/**
 * @hooked WC_Emails::customer_details() Shows customer details
 * @hooked WC_Emails::email_address() Shows email address
 */
do_action('woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email);
?>

<div style="margin-bottom: 40px;">
    <table class="td" cellspacing="0" cellpadding="6"
           style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
        <thead>
        <tr>
            <th class="td" scope="col" style="text-align:<?php echo esc_attr($text_align); ?>;">Code</th>
            <th class="td" scope="col" style="text-align:<?php echo esc_attr($text_align); ?>;">Description</th>
            <th class="td" scope="col" style="text-align:<?php echo esc_attr($text_align); ?>;">Quantity</th>
            <th class="td" scope="col" style="text-align:<?php echo esc_attr($text_align); ?>;">Price</th>
        </tr>
        </thead>
        <tbody>
        <?php
        echo wc_get_email_order_items($order, array( // WPCS: XSS ok.
            'show_sku' => $sent_to_admin,
            'show_image' => false,
            'image_size' => array(32, 32),
            'plain_text' => $plain_text,
            'sent_to_admin' => $sent_to_admin,
        ));
        ?>

        <?php
        $totals = $order->get_order_item_totals();

        //get shipping and fee line items
        $shipping_total = '';
        $item_fees = array();
        foreach ($totals as $key => $total) {
            if ($key === 'shipping') {
                $shipping_total = $total['value'];
            }
            if (strpos($key, 'fee_') === 0) {
                $fee_label_data = explode(' x ', $total['label']);
                $fee_qty = $fee_label_data[0];
                $fee_item = rtrim($fee_label_data[1], ':');
                ?>
                <tr class="">
                    <td class="td" style="text-align:<?php echo esc_attr($text_align); ?>;
                        vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
                        <?= $fee_item ?>
                    </td>
                    <td class="td" style="text-align:<?php echo esc_attr($text_align); ?>;
                        vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
                        Custom item
                    </td>
                    <td class="td"
                        style="text-align:<?php echo esc_attr($text_align); ?>; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
                        <?= $fee_qty ?>
                    </td>
                    <td class="td"
                        style="text-align:<?php echo esc_attr($text_align); ?>; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
                        <?= $total['value'] ?>
                    </td>
                </tr>
                <?php
            }
        }
        ?>
        <tr class="<?php if(isset($item)) { echo esc_attr(apply_filters('woocommerce_order_item_class', 'order_item', $item, $order)); } ?>">
            <td class="td" style="text-align:<?php echo esc_attr($text_align); ?>;
                vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
                Z-FREIGHT
            </td>
            <td class="td" style="text-align:<?php echo esc_attr($text_align); ?>;
                vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
                Freight
            </td>
            <td class="td"
                style="text-align:<?php echo esc_attr($text_align); ?>; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
                1
            </td>
            <td class="td"
                style="text-align:<?php echo esc_attr($text_align); ?>; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
                <?= $shipping_total ?>
            </td>
        </tr>
        </tbody>
        <tfoot>
        <?php
        if ($totals) {
            $i = 0;
            foreach ($totals as $key => $total) {
                $i++;

                if (strpos($key, 'fee_') === 0 || strpos($key, 'shipping') === 0) {
                    //ignore 'fee' (custom line items - which are usually products not in the system that are in the GT order
                    //and shipping, which is being output above in order items as 'Z Freight'
                } else {
                    ?>
                    <tr>
                        <th class="td" scope="row" colspan="2"
                            style="text-align:<?php echo esc_attr($text_align); ?>; <?php echo (1 === $i) ? 'border-top-width: 4px;' : ''; ?>"></th>
                        <th class="td" scope="row"
                            style="text-align:<?php echo esc_attr($text_align); ?>; <?php echo (1 === $i) ? 'border-top-width: 4px;' : ''; ?>"><?php echo wp_kses_post($total['label']); ?></th>
                        <td class="td"
                            style="text-align:<?php echo esc_attr($text_align); ?>; <?php echo (1 === $i) ? 'border-top-width: 4px;' : ''; ?>"><?php echo wp_kses_post($total['value']); ?></td>
                    </tr>
                    <?php
                }
            }
        }
        ?>
        </tfoot>
    </table>

    <?php
    if ($order->get_customer_note()) {
        ?>
        <br>
        <h2>ORDER INSTRUCTIONS / COMMENTS</h2>
        <p id="customer-note">
            <?php echo wp_kses_post(wptexturize($order->get_customer_note())); ?>
        </p>
        <?php
    }
    ?>
</div>

<?php do_action('woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email); ?>
