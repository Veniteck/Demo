<?php
/**
 * Checkout Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
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
 * @version     2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wc_print_notices();

do_action( 'woocommerce_before_checkout_form', $checkout );

// If checkout registration is disabled and not logged in, the user cannot checkout
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) );
	return;
}
?>

<div class="form-checkout">

    <form class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" method="post" >

        <?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

        <div class="form__row">

            <div class="form__col form__col--1of2">

                <div class="form__section">

	                <?php do_action( 'woocommerce_checkout_billing' ); ?>

                </div><!-- /.form__section -->

            </div><!-- /.form__col form__col-/-1of2 -->

            <div class="form__col form__col--2of2">

                <div class="form__section">

                    <div class="form__row">

                        <div class="form__col">
                            <h2 class="form__title">Order Details</h2><!-- /.form__title -->
                        </div><!-- /.form__col -->

                    </div><!-- /.form__row -->

                    <div class="form__row">

                        <div class="form__col">

                            <?php woocommerce_order_review(); ?>

                        </div><!-- /.form__col -->

                    </div><!-- /.form__row -->

                    <div class="form__row">
                        <div class="form__col">
                            <?php woocommerce_checkout_payment(); ?>
                        </div>
                    </div>

                </div><!-- /.form__section -->

            </div><!-- /.form__col form__col-/-1of2 -->

        </div><!-- /.form__row -->

        <div class="form__row hidden">
            <div class="form__col form__col--1of2">

	            <?php do_action( 'woocommerce_checkout_shipping' ); ?>

            </div><!-- /.form__col form__col-/-1of2 -->

            <div class="form__col form__col--2of2">

                <?php /*
                <div class="form__section">

                    <div class="form__row">
                        <div class="form__col">
                            <h2 class="form__title">Payment</h2><!-- /.form__title -->
                        </div><!-- /.form__col -->
                    </div><!-- /.form__row -->




                </div><!-- /.form__section -->
 */ ?>
            </div><!-- /.form__col form__col-/-1of2 -->

        </div><!-- /.form__row -->

    </form>

</div><!-- /.form-main -->
