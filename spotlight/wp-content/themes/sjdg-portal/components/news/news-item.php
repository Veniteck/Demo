<?php
$featured_image = get_the_post_thumbnail( $post->ID, 'news-thumbnail', array( 'class' => 'card__image' ) );
$excerpt = get_the_excerpt();
$read_time = get_field('read_time');
$category_objs = get_the_category();
?>
<div class="card">
    <div class="card__container">
        <a href="<?php the_permalink(); ?>">
            <?php if ( $featured_image ) :
                echo $featured_image;
            else : ?>
                <img src="<?php echo get_template_directory_uri() ?>/assets/build/css/images/temp/news-thumb-default.jpg">
            <?php endif; ?>
        </a>

        <div class="card__content">
            <div class="card__title"><?php the_title(); ?></div><!-- /.card__title -->

            <?php if ( $excerpt ) : ?>
                <p><?php echo $excerpt;  ?></p>
            <?php endif;

            if ( $read_time ) : ?>
                <div class="card__meta"><?php echo $read_time; ?></div><!-- /.card__meta -->
            <?php endif; ?>

            <div class="card__actions">

                <a href="<?php the_permalink(); ?>" class="btn btn--primary">Read More</a>

                <?php // Only display first category
                if ( !empty( $category_objs ) ) : ?>
                    <a href="<?php echo esc_url( get_category_link( $category_objs[0]->term_id ) ); ?>" class="tag"><?php echo esc_html( $category_objs[0]->name ); ?></a>
                <? endif; ?>

            </div><!-- /.card__actions -->
        </div><!-- /.card__content -->
    </div><!-- /.card__container -->
</div><!-- /.card -->
