<?php
// Main posts query
$feed_count = get_field( 'feed_count', 'option' );
$news_query_args = array( 'posts_per_page' => $feed_count );

// If category archive, get category id and add to query
if ( is_category() ) {
    $queried_obj = get_queried_object();
    $category_id = $queried_obj->term_id; // used in data attribute
    $news_query_args['cat'] = $category_id;
} else {
    $category_id = ''; // used in data attribute
}

$news_query = new WP_Query( $news_query_args );

if ( $news_query->have_posts() ) : ?>

    <div class="section-news" data-page="1" data-category="<?php echo $category_id; ?>">

        <?php while ( $news_query->have_posts() ) :

            $news_query->the_post(); ?>

            <?php get_template_part( 'components/news/news-item' ); ?>

        <?php endwhile; ?>

    </div><!-- /.section-news -->

    <?php // If there's more pages to load
    if ( $news_query->max_num_pages > 1 ) : ?>

        <div class="section-more">
            <a href="javascript:void(0);" id="news__load_more" class="btn btn--pointer">
                <svg>
                    <use xlink:href="#button-arrow-down" />
                </svg>

                <span>Load More</span>
            </a>
        </div><!-- /.section-more -->

    <?php endif; ?>

<?php else:

    get_template_part( 'components/news/news-no-results' );

endif;
wp_reset_postdata(); ?>
