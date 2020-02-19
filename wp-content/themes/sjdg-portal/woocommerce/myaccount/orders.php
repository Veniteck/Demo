<?php
/**
 * Orders
 *
 * Shows orders on the account page.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/orders.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see    https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.2.0
 */

if (!defined('ABSPATH')) {
    exit;
}

do_action('woocommerce_before_account_orders', $has_orders); ?>

<?php if ($has_orders) : ?>

    <div class="table table--orders">
        <div class="table__head">
            <h4>RECENT ORDERS</h4>
        </div><!-- /.table__head -->

        <div class="table__body">
            <table>
                <tr>
                    <th>Order #</th>
                    <th>DATE</th>
                    <th>Status</th>
                    <th>total</th>
                    <th colspan="3">items</th>
                </tr>

                <?php foreach ($customer_orders->orders as $customer_order) :
                    $order = wc_get_order($customer_order);

                    //first grab the default product item count (WC_Order_Item_Product objects)
                    $item_count = $order->get_item_count();

                    //next check if there are any custom items (WC_Order_Item_Fee objects)
                    $item_fee_count = sizeof($order->get_fees());

                    //count fees using "NUM X FeeName" split
                    $item_fee_total_qty = 0;
                    foreach($order->get_fees() as $custom_fee) {
                        $custom_fee_name = $custom_fee->get_name();
                        $custom_fee_name_split = explode(" x ", $custom_fee_name);
                        $custom_fee_qty = $custom_fee_name_split[0];
                        $item_fee_total_qty += $custom_fee_qty;
                    }

                    //add them up for the total
                    $final_item_count = $item_count + $item_fee_total_qty;

                    if(isset($_SESSION['gt_order_list_sync_error_' . $customer_order->get_id()])) {
                        $order_list_sync_error = $_SESSION['gt_order_list_sync_error_' . $customer_order->get_id()];
                    } else {
                        $order_list_sync_error = false;
                    }
                    ?>

                    <tr class="order">
                        <td data-title="order #">
                            <?php echo _x('#', 'hash before order number', 'woocommerce') . $order->get_order_number(); ?>
                        </td>
                        <td data-title="date">
                            <time
                                datetime="<?php echo esc_attr($order->get_date_created()->date('c')); ?>"><?php echo esc_html(wc_format_datetime($order->get_date_created())); ?></time>
                        </td>
                        <td data-title="status">
                            <?php echo esc_html(wc_get_order_status_name($order->get_status())); ?>
                            <?php if($order_list_sync_error): ?>
                                <br><strong>Warning! Order data could not be synced!</strong>
                            <?php endif ?>
                        </td>
                        <td data-title="items">
                            <?php echo $order->get_formatted_order_total(); ?>
                        </td>
                        <td data-title="total">
                            <?php echo $final_item_count; ?>
                        </td>
                        <td>
                            <a href="javascript:void(0);" class="link-more">
                                <span>VIEW ITEMS</span>

                                <span class="arrow"></span>
                            </a>
                        </td>
                        <td>
                            <ul>

                                <?php
                                $actions = wc_get_account_orders_actions($order);

                                if (!empty($actions)) {
                                    foreach ($actions as $key => $action) {
                                        echo '<li><a href="' . esc_url($action['url']) . '" class="woocommerce-button button ' . sanitize_html_class($key) . '">' . esc_html($action['name']) . '</a></li>';
                                    }
                                }
                                ?>

                                <?php /*
                                <li>
                                    <a href="#" class="btn">VIEW ORDER</a>
                                </li>

                                <li>
                                    <a href="#" class="btn btn--grey">REORDER</a>
                                </li> */ ?>
                            </ul>
                        </td>
                    </tr>

                    <tr class="order-details">
                        <td colspan="7">
                            <div class="table">
                                <div class="table__head">
                                    <h5>your order contains</h5>
                                </div><!-- /.table__head -->
                                <div class="table__body">
                                    <table>
                                        <tr>
                                            <th>Product</th>
                                            <th>ITEM PRICE</th>
                                            <th>QTY</th>
                                            <th>TOTAL</th>
                                        </tr>

                                        <?php
                                        /**
                                         * @var WC_Order_Item $item
                                         */
                                        ?>
                                        <?php foreach ($order->get_items() as $item): ?>

                                            <?php $data = $item->get_data(); ?>

                                            <tr>
                                                <td>
                                                    <?php echo $item->get_name(); ?>

                                                    <span>Code: <?php echo get_metadata('order_item', $item->get_id(), '_sku', true); ?></span>
                                                </td>
                                                <td data-title="ITEM PRICE"><?php echo wc_price(get_metadata('order_item', $item->get_id(), '_product_price', true)); ?></td>
                                                <td data-title="QTY"><?php echo $data['quantity']; ?></td>
                                                <td data-title="TOTAL"><?php echo wc_price(floatval($data['total'])); ?></td>
                                            </tr>

                                        <?php endforeach; ?>

                                        <?php if ($fees = $order->get_fees()) : ?>
                                            <?php foreach ($fees as $fee) :
                                                $fee_code = explode(' x ', $fee->get_name());
                                                ?>

                                                <tr>
                                                    <td>
                                                        <?= $fee->get_name() ?>
                                                        <span>Code: <?= $fee_code[1] ?></span>
                                                    </td>
                                                    <td data-title="ITEM PRICE"></td>
                                                    <td data-title="QTY"><?= $fee_code[0] ?></td>
                                                    <td data-title="TOTAL"><?= wc_price($fee->get_total()) ?></td>
                                                </tr>

                                            <?php endforeach ?>
                                        <?php endif ?>

                                    </table>

                                </div><!-- /.table__body -->

                            </div><!-- /.table -->

                        </td>

                    </tr>

                <?php endforeach; ?>

            </table>

        </div><!-- /.table__body -->

    </div><!-- /.table -->

    <?php do_action('woocommerce_before_account_orders_pagination'); ?>

    <?php if (1 < $customer_orders->max_num_pages) : ?>
        <div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
            <?php if (1 !== $current_page) : ?>
                <a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button"
                   href="<?php echo esc_url(wc_get_endpoint_url('orders', $current_page - 1)); ?>"><?php _e('Previous', 'woocommerce'); ?></a>
            <?php endif; ?>

            <?php if (intval($customer_orders->max_num_pages) !== $current_page) : ?>
                <a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button"
                   href="<?php echo esc_url(wc_get_endpoint_url('orders', $current_page + 1)); ?>"><?php _e('Next', 'woocommerce'); ?></a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

<?php else : ?>
    <div
        class="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info">
        <a class="woocommerce-Button button"
           href="<?php echo esc_url(apply_filters('woocommerce_return_to_shop_redirect', wc_get_page_permalink('shop'))); ?>">
            <?php _e('Go shop', 'woocommerce') ?>
        </a>
        <?php _e('No order has been made yet.', 'woocommerce'); ?>
    </div>
<?php endif; ?>

<?php do_action('woocommerce_after_account_orders', $has_orders); ?>
