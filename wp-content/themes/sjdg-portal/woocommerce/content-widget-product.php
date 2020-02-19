<?php
/**
 * The template for displaying product widget entries.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-widget-product.php.
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

/**
 * @var WC_Product $product
 */
global $product;
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

                <h4 class="product__title">
                    <a href="<?php echo $product->get_permalink(); ?>"><?php echo $product->get_name(); ?></a>
                </h4>

                <span>Code: <?php echo $product->get_sku(); ?></span>

                <span class="product__price"><?php echo $product->get_price_html(); ?> <br />each</span>
            </div><!-- /.product__content -->
        </div><!-- /.product__body -->

        <div class="product__actions">
            <a href="<?php echo $product->get_permalink(); ?>" class="btn btn--xsmall">View More</a>

            <a href="<?php echo $product->add_to_cart_url(); ?>" class="btn btn--xsmall btn--grey btn--add-to-cart">Add To Cart</a>

            <div class="product__popup">

                <?php woocommerce_template_loop_add_to_cart(); ?>

            </div><!-- /.product__popup -->

        </div><!-- /.product__actions -->

    </div><!-- /.product__inner -->

</div><!-- /.product -->
