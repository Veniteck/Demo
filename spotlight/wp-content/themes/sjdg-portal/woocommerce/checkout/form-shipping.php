<?php
/**
 * Checkout shipping information form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-shipping.php.
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
 * @version 3.0.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<div class="form__section woocommerce-shipping-fields">

	<?php if ( true === WC()->cart->needs_shipping_address() ) : ?>

    <div class="form__row">
        <div class="form__col">
            <h2 class="form__title">Shipping Details</h2><!-- /.form__title -->
        </div><!-- /.form__col -->
    </div><!-- /.form__row -->

    <div class="form__row">

        <div class="form__col">

            <?php $checked = apply_filters( 'woocommerce_ship_to_different_address_checked', 'shipping' === get_option( 'woocommerce_ship_to_destination' ) ? 1 : 0 ) ? 'checked="checked"' : '' ; ?>

            <div id="ship-to-different-address" style="display: none">
                <input id="ship_to_different_address_hidden" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="ship_to_different_address" value="1" <?php echo $checked; ?> type="checkbox" />
            </div>

            <div class="checkbox" id="ship-to-same-address">
                <input
                        type="checkbox"
                        id="ship-to-same-address-input"
                        class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox"
                        <?php checked( apply_filters( 'woocommerce_ship_to_different_address_checked', 'shipping' === get_option( 'woocommerce_ship_to_destination' ) ? 0 : 1 ), 1 ); ?>
                        name="ship_to_same_address"
                        value="1"
                />

                <label for="ship-to-same-address-input">Use Billing details for Shipping.</label>
            </div><!-- /.checkbox -->

            <div class="shipping_address" style="display:none;">

	            <?php
	            $fields = $checkout->get_checkout_fields( 'shipping' );

	            foreach ( $fields as $key => $field ) {

		            $wat = 1;

		            if( ! in_array( 'form-row-last', $field['class'] ) ){
			            echo '<div class="form__row">';
		            }

                    if ( isset( $field['country_field'], $fields[ $field['country_field'] ] ) ) {
                        $field['country'] = $checkout->get_value( $field['country_field'] );
                    }

                    if(isset($field['type'])) {
                        if( $field['type'] === 'country' || $field['type'] === 'state' ){
                            $field['type'] = 'text';
                        }
                    }

		            woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );

		            if( ! in_array( 'form-row-first', $field['class'] ) ){
			            echo '</div>';
		            }

	            }
	            ?>

            </div>

        </div><!-- /.form__col -->

    </div><!-- /.form__row -->

    <?php endif; ?>

</div><!-- /.form__section -->


