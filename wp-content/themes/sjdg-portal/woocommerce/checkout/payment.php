<?php
/**
 * Checkout Payment Section
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/payment.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see        https://docs.woocommerce.com/document/template-structure/
 * @author        WooThemes
 * @package    WooCommerce/Templates
 * @version     3.3.0
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!is_ajax()) {
    do_action('woocommerce_review_order_before_payment');
}
?>

    <div id="payment" class="woocommerce-checkout-payment">


        <?php if (WC()->cart->needs_payment()) : ?>

            <?php if (!empty($available_gateways)): ?>

                <?php
                if(get_option('greentree_payment_enabled')) {
                    $payment_methods_hide = '';
                } else {
                    $payment_methods_hide = 'hidden';
                }
                ?>

                <div class="payment_methods_wrapper <?= $payment_methods_hide ?>">

                    <ul class="list-radios payment_methods">

                        <?php foreach ($available_gateways as $gateway): ?>

                            <li class="wc_payment_method payment_method_<?php echo $gateway->id; ?>">

                                <div class="radio">

                                    <input id="payment_method_<?php echo $gateway->id; ?>" type="radio" class="input-radio"
                                           name="payment_method"
                                           value="<?php echo esc_attr($gateway->id); ?>" <?php checked($gateway->chosen, true); ?>
                                           data-order_button_text="<?php echo esc_attr($gateway->order_button_text); ?>"/>

                                    <label for="payment_method_<?php echo $gateway->id; ?>">
                                        <?php echo $gateway->get_title(); ?><?php echo $gateway->get_icon(); ?>
                                    </label>

                                </div>

                            </li>

                        <?php endforeach ?>

                    </ul>

                    <?php foreach ($available_gateways as $gateway): ?>

                        <?php if ($gateway->has_fields() || $gateway->get_description()) : ?>
                            <div class="payment_box form__row payment_method_<?php echo $gateway->id; ?>"
                                 <?php if (!$gateway->chosen) : ?>style="display:none;"<?php endif; ?>>
                                <?php $gateway->payment_fields(); ?>
                            </div>
                        <?php endif; ?>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <p>
                    <?php
                    echo apply_filters('woocommerce_no_available_payment_methods_message',
                        WC()->customer->get_billing_country() ?
                            esc_html__('Sorry, it seems that there are no available payment methods for your state. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce') :
                            esc_html__('Please fill in your details above to see available payment methods.', 'woocommerce')
                    )
                    ?>
                </p>

            <?php endif; ?>

        <?php endif; ?>


        <?php wc_get_template('checkout/terms.php'); ?>

        <?php do_action('woocommerce_pay_order_before_submit'); ?>

        <?php echo apply_filters('woocommerce_pay_order_button_html', '<button type="submit" class="btn btn--orange" id="place_order" value="' . esc_attr($order_button_text) . '" data-value="' . esc_attr($order_button_text) . '">' . esc_html($order_button_text) . '</button>'); // @codingStandardsIgnoreLine ?>

        <?php do_action('woocommerce_pay_order_after_submit'); ?>

        <?php wp_nonce_field('woocommerce-process_checkout'); ?>

    </div>
<?php
if (!is_ajax()) {
    do_action('woocommerce_review_order_after_payment');
}
