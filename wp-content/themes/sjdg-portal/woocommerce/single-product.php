<?php
/**
 * The Template for displaying all single products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product.php.
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
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

the_post();

/**
 * @var WC_Product $product
 */
global $product;

get_header( 'shop' ); ?>

	<main class="main main--portal">

		<div class="main__inner">

			<div class="shell">

				<?php woocommerce_breadcrumb( [
					'wrap_before' => '<nav class="breadcrumbs"><ul>',
					'wrap_after' => '</ul></nav>',
					'home' => 'Products',
					'before' => '<li>',
					'after' => '</li>',
					'delimiter' => ''
				] ); ?>

				<div id="product--wrapper" class="main__body">

					<?php wc_print_notices(); ?>

                    <aside class="sidebar">

                        <ul class="widgets">

							<?php //get_template_part( 'components/products/sidebar-search' ); ?>

							<?php dynamic_sidebar( 'sidebar-products' ); ?>

                        </ul><!-- /.widgets -->

                    </aside><!-- /.sidebar -->

                    <div class="content">

                        <div class="product product--single">

                            <aside class="product__aside">

                                <?php woocommerce_show_product_images(); ?>

                                <?php /*

                                <div class="slider slider--owl">

									<?php
									$images = [];

									$featured_thumb = get_post_thumbnail_id();

									if( $featured_thumb ){
										$images[] = wp_get_attachment_image_src( $featured_thumb, 'full' );
									}

									$image_ids = $product->get_gallery_image_ids();

									foreach( $image_ids as $image_id ){
										$images[] = wp_get_attachment_image_src( $image_id, 'full' );
									}
									?>

                                    <div class="slider__slides slider__slides--main owl-carousel">

										<?php $i = 1; ?>
										<?php foreach( $images as $image ): ?>
                                            <div class="slider__slide woocommerce-product-gallery__image" data-thumb="<?php echo $image[0]; ?>" data-hash="<?php echo $i; ?>">
                                                <figure class="slider__slide-image">
                                                    <img
                                                            class="attachment-shop_single size-shop_single wp-post-image"
                                                            src="<?php echo $image[0]; ?>"
                                                            alt=""
                                                            data-src="<?php echo $image[0]; ?>" data-large_image="<?php echo $image[0]; ?>" data-large_image_width="<?php echo $image[1]; ?>" data-large_image_height="<?php echo $image[2]; ?>"
                                                    >
                                                </figure><!-- /.slider__slide-image -->
                                            </div><!-- /.slider__slide -->
											<?php $i++; ?>
										<?php endforeach; ?>

                                    </div><!-- /.slider__slides slider__slides-/-main owl-carousel -->

                                    <div class="slider__slides slider__slides--thumbs owl-carousel">

										<?php $i = 1; ?>
										<?php foreach( $images as $image ): ?>
                                            <div class="slider__slide owl-hash-changer" data-hash="<?php echo $i; ?>">
                                                <figure class="slider__slide-image">
                                                    <img src="<?php echo $image[0]; ?>" alt="">
                                                </figure><!-- /.slider__slide-image -->
                                            </div><!-- /.slider__slide owl-hash-changer -->
											<?php $i++; ?>
										<?php endforeach; ?>

                                    </div><!-- /.slider__slides slider__slides-/-thumbs owl-carousel -->

                                </div><!-- /.slider slider-/-owl --> */ ?>

                            </aside><!-- /.product__aside -->

                            <div class="product__details">
                                <h1><?php the_title(); ?></h1>

                                <h3>Code: <?php echo $product->get_sku(); ?></h3>

                                <span class="product__price"><?php echo $product->get_price_html(); ?></span>

								<?php woocommerce_template_single_add_to_cart(); ?>

                                <h2>Product Details</h2>

								<?php the_content(); ?>

                            </div><!-- /.product__details -->

                        </div><!-- /.product product-/-single -->

                        <section class="section-related-products">

                            <header class="section__head">
                                <h3>Related Products</h3>
                            </header><!-- /.section__head -->

                            <div class="section__body">

                                <div class="products">

									<?php $related = wc_get_related_products( $product->get_id() ); ?>

									<?php if( $related ): ?>

										<?php foreach( $related as $related_product ): ?>

											<?php setup_postdata( $related_product ); ?>

											<?php wc_get_template_part( 'content-product' ); ?>

										<?php endforeach; ?>

										<?php wp_reset_postdata(); ?>

									<?php endif; ?>

                                </div><!-- /.products -->

                            </div><!-- /.section__body -->

                        </section><!-- /.section -->

                    </div>

				</div><!-- /.main__body -->

			</div><!-- /.shell -->

		</div><!-- /.main__inner -->

	</main><!-- /.main -->

<?php get_footer( 'shop' );

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */
