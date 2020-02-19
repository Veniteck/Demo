<?php
/**
 * Simple product add to cart
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/add-to-cart/simple.php.
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
 * @version     3.0.0
 */
if (!defined('ABSPATH')) {
    exit;
}

global $product;

if (!$product->is_purchasable()) {
    return;
}

echo wc_get_stock_html($product);

if ($product->is_in_stock()) : ?>

    <?php do_action('woocommerce_before_add_to_cart_form'); ?>

    <form class="cart" action="<?php echo esc_url(get_permalink()); ?>" method="post" enctype='multipart/form-data'>

        <?php do_action('woocommerce_before_add_to_cart_quantity'); ?>

        <span class="product__quantity">
            <span>QTY</span>
            <?php
            woocommerce_quantity_input(array(
                'min_value' => apply_filters('woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product),
                'max_value' => apply_filters('woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product),
                'input_value' => isset($_POST['quantity']) ? wc_stock_amount($_POST['quantity']) : $product->get_min_purchase_quantity(),
            ));
            ?>
            <!--            <input type="text" class="product__field" value="1">-->
        </span>

		<?php $group_of = get_post_meta( get_the_ID(), 'group_of_quantity', true ); ?>

		<?php if( $group_of && $group_of > 0 ): ?>
            <p class="group-of--text">
				<?php echo sprintf( 'Can only be ordered in groups of %s', $group_of ); ?>
            </p>
		<?php endif; ?>

        <?php do_action('woocommerce_after_add_to_cart_quantity'); ?>

        <?php do_action('woocommerce_before_add_to_cart_button'); ?>

        <button type="submit" name="add-to-cart" value="<?php echo esc_attr($product->get_id()); ?>"
                class="btn btn--small"><?php echo esc_html($product->single_add_to_cart_text()); ?></button>

        <?php do_action('woocommerce_after_add_to_cart_button'); ?>

        <!--        <a href="#" class="btn btn--small">Add To Cart</a>-->

    </form>

    <?php do_action('woocommerce_after_add_to_cart_form'); ?>

<?php endif; ?>
