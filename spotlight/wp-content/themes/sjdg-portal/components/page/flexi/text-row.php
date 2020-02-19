<?php $class = ( is_single() && get_post_type() === 'resource' || get_sub_field( 'display_full_width' ) ) ? 'section section--text section--full-width' : 'section section--text' ; ?>
<section class="<?php echo $class; ?>">

    <div class="shell">

        <article class="article">

            <div class="article__entry">

	            <?php the_sub_field( 'text_row_content' ); ?>

            </div><!-- /.article__entry -->

        </article><!-- /.article -->

    </div><!-- /.shell -->

</section><!-- /.section section-/-text -->
