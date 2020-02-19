<section class="section section--logos">

	<div class="shell">

		<header class="section__head">
			<h2 class="heading"><?php the_sub_field( 'logos_row_title' ); ?></h2>
		</header><!-- /.section__head -->

		<div class="section__body">

			<div class="company-logos">

				<div class="company-logos__placeholder">

					<?php $images = get_sub_field('logos_row_logos'); ?>
					<?php if( $images ): foreach( $images as $image ): ?>
						<img src="<?php echo $image['url']; ?>" alt="" width="180" height="107">
					<?php endforeach; endif; ?>

				</div><!-- /.company-logos__placeholder -->

				<div class="company-logos__body"></div><!-- /.company-logos__body -->

				<template id="company-logo">

					<div class="company-logos__logo">
						<img src="" alt="">
					</div><!-- /.company-logos__logo -->

				</template>

			</div><!-- /.company-logos -->

		</div><!-- /.section__body -->

	</div><!-- /.shell -->

</section><!-- /.section section-/-logos -->
