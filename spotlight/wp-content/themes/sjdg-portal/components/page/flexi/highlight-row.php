<section class="section section--highlight">

    <div class="shell">

        <div class="section__inner">

            <div class="section__video">

	            <?php $video_type = ( get_sub_field( 'highlight_row_video_type' ) === 'youtube' ) ? 'youtube' : 'vimeo' ; ?>

                <div class="video video--<?php echo $video_type; ?>">

                    <div class="video__placeholder" style="background-image: url(<?php the_sub_field( 'highlight_row_video_placeholder_image_url' ); ?>);">
                        <div class="play"></div><!-- /.play -->
                    </div><!-- /.video__placeholder -->

                    <div class="video__media" id="video-demo" data-video-id="<?php the_sub_field( 'highlight_row_video_id' ); ?>"></div><!-- /.video__media -->

                </div><!-- /.video -->

            </div><!-- /.section__video -->


            <div class="section__body">

                <div class="section__body-inner">

                    <h2><?php the_sub_field( 'highlight_row_title' ); ?></h2>

	                <?php the_sub_field( 'highlight_row_description' ); ?>

	                <?php $btns = get_sub_field( 'highlight_row_buttons' ); ?>

                    <div class="btn-group">

                        <?php
                        if( ! empty( $btns ) ):
	                        foreach( $btns as $btn ){
		                        printf( '<a target="%s" href="%s" class="btn">%s</a>', $btn['button']['target'], $btn['button']['url'], $btn['button']['title'] );
	                        }
                        endif;
                        ?>

                    </div>

                </div><!-- /.section__body-inner -->

            </div><!-- /.section__body -->

        </div><!-- /.section__inner -->

    </div><!-- /.shell -->

</section><!-- /.section section-/-highlight -->