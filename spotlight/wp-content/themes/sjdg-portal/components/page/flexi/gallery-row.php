<section class="section section--gallery section--gallery-product">

	<div class="shell">

		<div class="section__body">

			<div class="slider slider--gallery">

				<div class="swiper-container">

					<div class="swiper-wrapper">

						<?php $gallery_items = get_sub_field( 'gallery_row_items' ); ?>

						<?php if( ! empty( $gallery_items ) ):  foreach( $gallery_items as $gallery_item ): ?>
								<div class="swiper-slide" style="background-image: url(<?php echo $gallery_item['url']; ?> );"></div><!-- /.swiper-slide -->
						<?php endforeach;  endif; ?>

					</div><!-- /.swiper-wrapper -->

				</div><!-- /.swiper-container -->

				<div class="swiper-pagination"></div><!-- /.swiper-pagination -->

                <div class="swiper-button-next swiper-button-white"></div>
                <div class="swiper-button-prev swiper-button-white"></div>

			</div><!-- /.slider -->

		</div><!-- /.section__body -->

	</div><!-- /.shell -->

</section><!-- /.section section-/-gallery -->