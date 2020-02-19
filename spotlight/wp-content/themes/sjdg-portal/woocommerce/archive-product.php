<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
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

/**
 * @var WP_Query $wp_query
 */
global $wp_query;

get_header();

/**
 * Hook: woocommerce_before_main_content.
 *
 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
 * @hooked woocommerce_breadcrumb - 20
 * @hooked WC_Structured_Data::generate_website_data() - 30
 */
//do_action( 'woocommerce_before_main_content' );

?>
    <main class="main main--portal">

        <section class="section-intro">

            <div class="shell">

                <header class="section__head">
                    <h3>Welcome <?php echo wp_get_current_user()->get( 'display_name' ); ?></h3>
                </header><!-- /.section__head -->

                <div class="section__body">
                    <?php the_field( 'products_archive_description', 'option' ); ?>
                </div><!-- /.section__body -->

            </div><!-- /.shell -->

        </section><!-- /.section-intro -->

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

                <header class="main__head">
                    <h1><?php woocommerce_page_title( true ); ?></h1>
                </header><!-- /.main__head -->

                <div class="main__body" id="product--archive-wrapper" data-page="<?php echo wc_get_loop_prop( 'current_page' ); ?>">

	                <?php wc_print_notices(); ?>

                    <aside class="sidebar">

                        <ul class="widgets">

	                        <?php //get_template_part( 'components/products/sidebar-search' ); ?>

	                        <?php dynamic_sidebar( 'sidebar-products' ); ?>

                        </ul><!-- /.widgets -->

                    </aside><!-- /.sidebar -->

                    <div class="content">

                        <section class="section-products">

                            <header class="section__head">

                                <?php woocommerce_catalog_ordering(); ?>

                                <div class="grid">

                                    <span>Layout</span>

                                    <a href="#" class="btn-grid">
                                        <i class="ico-grid"></i>
                                        <i class="ico-grid-hover"></i>
                                    </a>

                                    <a href="#" class="btn-grid btn-grid--inline">
                                        <i class="ico-grid-inline"></i>
                                        <i class="ico-grid-inline-hover"></i>
                                    </a>

                                </div><!-- /.grid -->

                            </header><!-- /.section__head -->

                            <div class="section__body">

                                <div id="products_wrapper" class="products">

                                    <?php if( have_posts() ): ?>

                                        <?php while( have_posts() ): the_post(); ?>

                                            <?php
                                            /**
                                             * Hook: woocommerce_shop_loop.
                                             *
                                             * @hooked WC_Structured_Data::generate_product_data() - 10
                                             */
                                            do_action( 'woocommerce_shop_loop' );

                                            wc_get_template_part( 'content', 'product' );
                                            ?>

                                        <?php endwhile; ?>

                                    <?php else: ?>

                                        <p class="not-found">
                                            Nothing was found for your search. Please try again.
                                        </p>

                                    <?php endif; ?>

                                </div><!-- /.products -->

                            </div><!-- /.section__body -->

                            <footer class="section__foot">

                            </footer><!-- /.section__foot -->

                        </section><!-- /.section-products -->

                    </div><!-- /.content -->

                </div><!-- /.main__body -->

            </div><!-- /.shell -->

        </div><!-- /.main__inner -->

    </main><!-- /.main -->
<?php
get_footer();
