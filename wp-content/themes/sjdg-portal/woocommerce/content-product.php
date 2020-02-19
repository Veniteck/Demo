<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
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
 * @version 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * @var WC_Product $product
 */
global $product;

// Ensure visibility
if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}
?>

<div class="product">

    <div class="product__inner">

        <div class="product__body">

            <div class="product__image">

                <a href="<?php echo $product->get_permalink(); ?>">
                    <?php echo $product->get_image(); ?>
                </a>

            </div><!-- /.product__image -->

            <div class="product__content">

                <div class="product__content-inner">

                    <h4 class="product__title">
                        <a href="<?php echo $product->get_permalink(); ?>"><?php echo $product->get_name(); ?></a>
                    </h4>

                    <span>Code: <?php echo $product->get_sku(); ?></span>

                </div><!-- /.product__content-inner -->

                <a href="<?php echo $product->get_permalink(); ?>" class="btn-more">More</a>

                <span class="product__price"><?php echo $product->get_price_html(); ?> <br />each</span>

            </div><!-- /.product__content -->

        </div><!-- /.product__body -->

        <div class="product__actions">

            <a href="<?php echo $product->get_permalink(); ?>" class="btn btn--orange">More</a>

	        <?php woocommerce_template_loop_add_to_cart(); ?>

        </div><!-- /.product__actions -->

        <a href="<?php echo $product->get_permalink(); ?>" class="btn-more">More</a>

    </div><!-- /.product__inner -->

</div><!-- /.product -->