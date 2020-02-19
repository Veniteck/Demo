<?php $hero_type = get_sub_field( 'hero_row_type' ); ?>

<?php if ( 'slider' == $hero_type ) : ?>

	<div class="banner">
		<div class="shell">
			<div class="banner__inner">

				<div class="banner__content">
					<h1><?php the_sub_field( 'hero_row_title'); ?></h1>
				</div><!-- /.banner__content -->

				<div class="banner__slider">
					<div class="slider slider--banners">
						<div class="swiper-container">
							<div class="swiper-wrapper">

								<?php if ( have_rows( 'hero_slider' ) ) : ?>

									<?php while ( have_rows( 'hero_slider' ) ) : the_row(); ?>

										<div class="swiper-slide" style="background-image: url(<?php the_sub_field( 'hero_slide_background_image_url' ); ?>);"></div>
										<!-- /.swiper-slide -->

									<?php endwhile; ?>

								<?php endif; ?>

							</div><!-- /.swiper-wrapper -->
						</div><!-- /.swiper-container -->
					</div><!-- /.slider -->
				</div><!-- /.banner__slider -->

			</div><!-- /.banner__inner -->
		</div><!-- /.shell -->
	</div><!-- /.banner -->

<?php elseif ( 'video' == $hero_type ) : ?>

	<div class="banner">
		<div class="shell">
			<div class="banner__inner">

				<div class="banner__content">
					<h1><?php the_sub_field( 'hero_row_title'); ?></h1>
				</div><!-- /.banner__content -->

				<div class="banner__video" style="background-image: url(<?php the_sub_field( 'hero_row_background_video_fallback_image_url' ); ?>);">

					<?php $hero_background_video_type = get_sub_field( 'hero_row_background_video_type' ); ?>

					<?php if ( 'vimeo' == $hero_background_video_type ) : ?>

						<div class="video-bg video-bg--vimeo" id="bg-video-vimeo" data-video-id="<?php the_sub_field( 'hero_row_background_video_id' ); ?>"></div><!-- /.video-bg -->

					<?php elseif ( 'youtube' == $hero_background_video_type ) : ?>

	                    <div class="video-bg video-bg--youtube" id="bg-video-youtube" data-video-id="<?php the_sub_field( 'hero_row_background_video_id' ); ?>"></div><!-- /.video-bg -->

					<?php endif; ?>

				</div>

			</div><!-- /.banner__inner -->
		</div><!-- /.shell -->
	</div><!-- /.banner -->

<?php endif; ?>
