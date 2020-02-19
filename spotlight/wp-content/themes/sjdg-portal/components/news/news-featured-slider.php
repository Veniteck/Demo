<?php
$featured_query_args = array();

if ( 1 == get_field( 'featured_article', 'option' ) ) {
    // Show only latest post
    $featured_query_args['posts_per_page'] = 1;
} else {
    // Show selected posts
    $post_ids = array();
    $post_objects = get_field( 'select_article', 'option' );
    if ( $post_objects ) {
    	foreach ( $post_objects as $post ) {
            $post_ids[] = $post->ID;
    	}
    }
    $featured_query_args['post__in'] = $post_ids;
}

$featured_query = new WP_Query( $featured_query_args );

if ( $featured_query->have_posts() ) : ?>

    <div class="section__content">
        <div class="slider-articles">
            <div class="swiper-container">
                <div class="swiper-wrapper">

                    <?php
                    while ( $featured_query->have_posts() ) :

                        $featured_query->the_post();
                        $featured_image = get_the_post_thumbnail( $post->ID, 'news-slider');
                        $featured_excerpt = get_the_excerpt();
                        $featured_read_time = get_field('read_time');
                        ?>

                        <div class="swiper-slide">
                            <div class="article-preview">

                                <?php
                                if ( $featured_image ) :
                                    echo $featured_image;
                                else : ?>
                                    <img src="<?php echo get_template_directory_uri() ?>/assets/build/css/images/temp/news-slider-default.jpg">
                                <?php endif;
                                ?>

                                <div class="article-preview__description">
                                    <div class="article-preview__title"><?php the_title(); ?></div><!-- /.article-preview__title -->

                                    <?php // Excerpt
                                    if ( $featured_excerpt ) : ?>
                                        <p><?php echo $featured_excerpt;  ?></p>
                                    <?php endif; ?>

                                    <div class="article-preview__actions">
                                        <a href="<?php the_permalink(); ?>" class="btn btn--primary">Read Article</a>

                                        <?php // Read time
                                        if ( $featured_read_time ) : ?>
                                            <div class="article-preview__meta"><?php echo $featured_read_time; ?></div><!-- /.article-preview__meta -->
                                        <?php endif; ?>
                                    </div><!-- /.article-preview__actions -->

                                </div><!-- /.article-preview__description -->

                            </div><!-- /.article-preview -->
                        </div><!-- /.swiper-slide -->

                    <?php endwhile; ?>

                </div><!-- /.swiper-wrapper -->
            </div><!-- /.swiper-container -->
        </div><!-- /.slider-articles -->
    </div><!-- /.section__content -->

<?php endif;
wp_reset_postdata(); ?>
