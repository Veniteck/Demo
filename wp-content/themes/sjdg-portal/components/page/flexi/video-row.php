<section class="section section--video">

    <div class="shell">

        <div class="video video--<?php the_sub_field( 'video_row_type' ); ?>">

            <div class="video__placeholder" style="background-image: url( <?php the_sub_field( 'video_row_placeholder' ); ?> );">
                <div class="play"></div><!-- /.play -->
            </div><!-- /.video__placeholder -->

            <div class="video__media" id="video-<?php the_sub_field( 'video_id' ); ?>" data-video-id="<?php the_sub_field( 'video_id' ); ?>"></div><!-- /.video__media -->

        </div><!-- /.video -->

    </div><!-- /.shell -->

</section><!-- /.section section-/-video -->
