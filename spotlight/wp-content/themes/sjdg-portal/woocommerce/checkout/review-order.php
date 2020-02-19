<?php
/**
 * Review order table
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/review-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="table-cart table-cart--checkout woocommerce-checkout-review-order-table">
    <table>
        <thead>
        <tr>
            <th>Product</th>

            <th>Qty</th>

            <th>Total</th>
        </tr>
        </thead>

        <tbody>

		<?php foreach( WC()->cart->get_cart() as $cart_item_key => $cart_item ): ?>

            <?php $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key ); ?>

            <?php if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ): ?>


                <tr>
                    <td data-label="Product">
                        <div class="table__content">
                            <h4><a href="<?php echo $_product->get_permalink(); ?>"><?php echo $_product->get_name(); ?></a></h4>

                            <span>Code: <?php echo $_product->get_sku(); ?></span>

                            <?php echo wc_get_formatted_cart_item_data($cart_item); ?>
                        </div><!-- /.table__content -->
                    </td>

                    <td data-label="Qty"><?php echo $cart_item['quantity']; ?></td>

                    <td data-label="Total">
                        <strong><?php echo WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ); ?></strong>
                    </td>
                </tr>


            <?php endif; ?>

		<?php endforeach; ?>

        </tbody>

        <tfoot>
        <tr>
            <td colspan="2">
                <strong>Cart SubTotal:</strong>
            </td>

            <td>
                <strong><?php wc_cart_totals_subtotal_html(); ?></strong>
            </td>
        </tr>

        <?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>

	        <?php do_action( 'woocommerce_review_order_before_shipping' ); ?>

	        <?php wc_cart_totals_shipping_html(); ?>

	        <?php do_action( 'woocommerce_review_order_after_shipping' ); ?>

        <?php endif; ?>

        <?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
            <tr class="fee">
                <td colspan="2"><strong><?php echo esc_html( $fee->name ); ?>:</strong></td>
                <td><?php wc_cart_totals_fee_html( $fee ); ?></td>
            </tr>
        <?php endforeach; ?>

        <?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
	        <?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
		        <?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : ?>
                    <tr class="tax-rate tax-rate-<?php echo sanitize_title( $code ); ?>">
                        <td colspan="2"><strong><?php echo esc_html( $tax->label ); ?>:</strong></td>
                        <td><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
                    </tr>
		        <?php endforeach; ?>
	        <?php else : ?>
                <tr class="tax-total">
                    <td colspan="2"><strong><?php echo esc_html( WC()->countries->tax_or_vat() ); ?>:</strong></td>
                    <td><?php wc_cart_totals_taxes_total_html(); ?></td>
                </tr>
	        <?php endif; ?>
        <?php endif; ?>

        <tr>
            <td colspan="2">ORDER TOTAL:</td>

            <td><?php wc_cart_totals_order_total_html(); ?></td>
        </tr>

        </tfoot>
    </table>
</div><!-- /.table-cart -->