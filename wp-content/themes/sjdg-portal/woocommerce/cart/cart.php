<?php
/**
 * Cart Page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wc_print_notices();

do_action( 'woocommerce_before_cart' ); ?>

<form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
	<?php //do_action( 'woocommerce_before_cart_table' ); ?>

    <div class="cart">

        <div class="cols">

            <div class="col col--size-1">

                <div class="table-cart">

                    <table>
                        <thead>
                        <tr>
                            <th>Product</th>

                            <th>Price</th>

                            <th>Qty</th>

                            <th>Total</th>
                        </tr>
                        </thead>

                        <tbody>

                        <?php foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ): ?>

                            <?php
	                        $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
	                        $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
                            ?>

                            <?php
	                        /**
	                         * @var WC_Product $_product
	                         */
                            if (
                                $_product &&
                                $_product->exists() &&
                                $cart_item['quantity'] > 0 &&
                                apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key )
                            ): ?>

                                <tr class="cart_item">

                                    <td data-label="Product">
                                        <div class="table__image">
                                            <a href="<?php echo $_product->get_permalink(); ?>"><?php echo $_product->get_image(); ?></a>
                                        </div><!-- /.table__image -->

                                        <div class="table__content">

                                            <h4><a href="<?php echo $_product->get_permalink(); ?>"><?php echo $_product->get_title(); ?></a></h4>

                                            <span>Code: <?php echo $_product->get_sku(); ?></span>

                                            <?php echo wc_get_formatted_cart_item_data($cart_item); ?>

                                        </div><!-- /.table__content -->
                                    </td>

                                    <td data-label="Price"><?php echo $_product->get_price_html(); ?></td>

                                    <td data-label="Qty">

                                        <?php
                                        if ( $_product->is_sold_individually() ) {
	                                        $product_quantity = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
                                        } else {
	                                        $product_quantity = woocommerce_quantity_input( array(
		                                        'input_name'    => "cart[{$cart_item_key}][qty]",
		                                        'input_value'   => $cart_item['quantity'],
		                                        'max_value'     => $_product->get_max_purchase_quantity(),
		                                        'min_value'     => '0',
		                                        'product_name'  => $_product->get_name(),
	                                        ), $_product, false );
                                        }

                                        echo $product_quantity;

                                        ?>

                                    </td>

                                    <td data-label="Total">
                                        <strong>
	                                        <?php
	                                            echo WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] );
	                                        ?>
                                        </strong>

                                        <a href="<?php echo esc_url( wc_get_cart_remove_url( $cart_item_key ) ); ?>"
                                           class="remove btn-close"
                                           data-product_id="<?php esc_attr_e( $product_id ); ?>"
                                           data-product_sku="<?php esc_attr_e( $_product->get_sku() ); ?>"
                                        >
                                            <i class="ico-cross"></i>
                                        </a>
                                    </td>
                                </tr>

                            <?php endif; ?>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                </div><!-- /.table-cart -->

	            <?php if ( wc_coupons_enabled() ): ?>

                    <div class="form-promotion">

<!--                        <form action="?" method="post">-->

                            <header class="form__head">
                                <h3>Promotion Code</h3>
                            </header><!-- /.form__head -->

                            <div class="form__body">
                                <div class="form__row">
                                    <div class="form__controls">
                                        <input type="text" name="coupon_code" class="input-text field" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Coupon code', 'woocommerce' ); ?>" />
                                    </div><!-- /.form__controls -->
                                </div><!-- /.form__row -->
                            </div><!-- /.form__body -->

                            <div class="form__actions">
                                <input type="submit" class="btn btn--xsmall" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>" />
                                <!--                                <a href="#" class="">Apply Code</a>-->
                            </div><!-- /.form__actions -->

<!--                        </form>-->

                    </div><!-- /.form-promotion -->

	            <?php endif; ?>

            </div><!-- /.col col-/-size-1 -->

            <div class="col col--size-2">

                <div class="cart__box">

                    <div class="table-checkout">

                        <?php woocommerce_cart_totals(); ?>

                        <button class="btn btn--small btn--bordered-white" name="update_cart" value="1" type="submit" disabled="disabled">Update Cart</button>

                        <a href="<?php echo wc_get_page_permalink( 'checkout' ); ?>" class="btn btn--small btn--bordered-white">Checkout</a>

	                    <?php wp_nonce_field( 'woocommerce-cart' ); ?>

                    </div>

                </div><!-- /.cart__box -->

            </div><!-- /.col col-/-size-2 -->

        </div><!-- /.cols -->

    </div><!-- /.cart -->

</form>

<?php //do_action( 'woocommerce_after_cart' ); ?>
