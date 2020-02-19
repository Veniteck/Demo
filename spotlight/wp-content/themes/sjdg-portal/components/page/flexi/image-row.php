<section class="article__section article__section--image">
    <figure class="article__attachment">
        <?php
        $image_row_image = get_sub_field( 'image_row_image' );
        $image_row_caption = get_sub_field( 'image_row_caption' );
        $size = 'video-thumbnail';
        $imagethumb = $image_row_image['sizes'][ $size ];

        // Image
        if ( $image_row_image ) : ?>
			<img src="<?php echo $imagethumb; ?>" alt="<?php echo $image_row_image['alt']; ?>" />
		<?php endif;

        // Caption
        if ( !empty( $image_row_caption ) ) : ?>
            <figcaption><?php echo $image_row_caption; ?></figcaption>
        <?php endif; ?>
    </figure><!-- /.article__attachment -->
</section><!-- /.article__section article__section-/-image -->
