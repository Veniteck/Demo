<?php
/**
 * The template for displaying a default page
 */

get_header();
the_post();
?>

<?php //get_template_part( 'components/global/header-image' ); ?>

<?php //dt_breadcrumbs() ?>

<main class="main main--portal">

    <?php get_template_part( 'components/page/page-title-area' ); ?>



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

            <div class="main__body">

                <?php the_content(); ?>

            </div><!-- /.main__body -->

        </div><!-- /.shell -->

    </div><!-- /.main__inner -->

</main><!-- /.main -->

<?php get_footer(); ?>
