<?php
/**
* Images
*/

// Allow SVG & OGG
function cc_mime_types($mimes) {
	$mimes['svg'] = 'image/svg+xml';
	$mimes['ogg'] = 'application/ogg';
	return $mimes;
}
add_filter('upload_mimes', 'cc_mime_types');


// Change JPEG quality
function dt_custom_jpeg_quality( $quality ) {
   return 100;
}
add_filter( 'jpeg_quality', 'dt_custom_jpeg_quality' );


// Responsive image set max width
function ar_max_srcset_image_width() {
	return 2000;
}
add_filter( 'max_srcset_image_width', 'ar_max_srcset_image_width', 10 , 2 );


// Default image sizes - remove
function ar_remove_default_image_sizes( $sizes) {
	unset( $sizes['thumbnail']);
	// unset( $sizes['medium']); // medium image size is used by WP Media Library for thumbnails
	unset( $sizes['medium_large']);
	unset( $sizes['large']);
	return $sizes;
}
add_filter('intermediate_image_sizes_advanced', 'ar_remove_default_image_sizes');


// Add Theme Thumbnails and custom image sizes
if ( function_exists( 'add_theme_support' ) ) {

	add_theme_support('post-thumbnails');

	// Image row
	// add_image_size('4x3-crop-548', 548, 411, true);
	// add_image_size('4x3-crop-737', 737, 553, true);

	// Text and image row
	// add_image_size('4x3-resize-548', 548, '', false);
	// add_image_size('4x3-resize-737', 737, '', false);

	// Video row
	// add_image_size('16x9-crop-986', 986, 555, true);

	//set_post_thumbnail_size( 167, 9999, false );
}


// Add instructions to featured image in News section
function dt_add_news_featured_image_instruction( $content ) {

	if ( !is_admin() ) {
		return false;
	}

	$screen = get_current_screen();

	if ( 'post' == $screen->post_type ) {
		return '<p class="howto">Add a featured image. JPG or PNG ___px x ___px.</p>' . $content; // Update dimensions
	}

	return $content;

}
// add_filter( 'admin_post_thumbnail_html', 'dt_add_news_featured_image_instruction' );
