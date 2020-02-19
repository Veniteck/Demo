<?php
/**
 * Checkout billing information form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-billing.php.
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

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/** @global WC_Checkout $checkout */
?>

<?php do_action('woocommerce_before_checkout_billing_form', $checkout); ?>

<div class="woocommerce-billing-fields">

    <div class="woocommerce-billing-fields__fields_wrapper">

        <div class="form__controls">

            <h3>Special instructions</h3>

            <div class="woocommerce-additional-fields__field-wrapper">
                <?php foreach ($checkout->get_checkout_fields('order') as $key => $field) : ?>
                    <?php woocommerce_form_field($key, $field, $checkout->get_value($key)); ?>
                <?php endforeach; ?>
            </div>

        </div><!-- /.form__controls -->

        <div class="checkout-billing-wrap hidden">
            <?php
            $fields = $checkout->get_checkout_fields('billing');

            foreach ($fields as $key => $field) {

                if (!in_array('form-row-last', $field['class'])) {
                    echo '<div class="form__row">';
                }

                if (isset($field['country_field'], $fields[$field['country_field']])) {
                    $field['country'] = $checkout->get_value($field['country_field']);
                }

                if(isset($field['type'])) {
                    if ($field['type'] === 'country' || $field['type'] === 'state') {
                        $field['type'] = 'text';
                    }
                }

				/**
				 * Billing names are earlier in the form, duplicating them causes issues with submission.
				 */
                if ($key === 'billing_first_name' || $key === 'billing_last_name') {
                    //$field['type'] = 'hidden';

                    if ( ! in_array('form-row-first', $field['class'] ) ) {
						echo '</div>';
					}

                    continue;
                }

                woocommerce_form_field($key, $field, $checkout->get_value($key));

                if ( ! in_array('form-row-first', $field['class'] ) ) {
                    echo '</div>';
                }

            }
            ?>
        </div>

        <div class="form__row">
            <div class="form__col">
                <h2 class="form__title">Shipping Details</h2><!-- /.form__title -->
            </div><!-- /.form__col -->
        </div><!-- /.form__row -->

        <div class="checkout-custom-shipping-billing-details">
            <br>
            <p>
                <?php echo $checkout->get_value('billing_company') ?><br>
                <?php echo $checkout->get_value('billing_address_1') ?><br>
                <?php echo $checkout->get_value('billing_address_2') ?><br>
                <?php echo $checkout->get_value('billing_city') ?> <?php echo $checkout->get_value('billing_state') ?>
                <?php echo $checkout->get_value('billing_country') ?> <?php echo $checkout->get_value('billing_postcode') ?><br>
                <?php echo $checkout->get_value('billing_email') ?>
            </p>
        </div>

        <p id="billing-fields--intro">If you need to update these details please <a href="mailto:sales@sjdg.com.au">contact
                support</a></p>

    </div>

</div>

<?php do_action('woocommerce_after_checkout_billing_form', $checkout); ?>
