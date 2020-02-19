<?php
// News section
if ( dt_is_news_section() ) {
	$header_image = get_field( 'news_header_image', 'option' );
}
// Resources section
elseif ( dt_is_resources_section() ) {
	$header_image = get_field( 'resources_header_image', 'option' );
}
// Tenders section
elseif ( dt_is_tenders_section() ) {
	$header_image = get_field( 'tenders_header_image', 'option' );
}
// Other pages
elseif ( is_page() ) {
	$header_image = get_field( 'header_image' );
}

if ( !empty( $header_image ) ) :
	$imagethumb = $header_image['sizes']['page-header']; ?>
	    <div class="main__head fullsize-background" id="header-breakpoint" style="background-image: url(<?php echo $imagethumb; ?>);"></div><!-- /.main__head fullsize-background -->
<?php else : // display fall back image ?>
    <div class="main__head fullsize-background" id="header-breakpoint" style="background-image: url(<?php bloginfo('template_url'); ?>/assets/build/css/images/temp/photo8.jpg);"></div><!-- /.main__head fullsize-background -->
<?php endif; ?>
