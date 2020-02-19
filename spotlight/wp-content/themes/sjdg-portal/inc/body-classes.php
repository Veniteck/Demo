<?php
/**
* Body classes
*/

// Add slug as body class
function turbo_body_class( $classes ) {
	global $post;
	if( is_home() ) {
		$key = array_search( 'blog', $classes );
		if($key > -1) {
			unset( $classes[$key] );
		};
	} elseif( is_page() ) {
		if( is_object( $post ) ){
			$classes[] = sanitize_html_class( $post->post_name );
		}
	} elseif(is_singular()) {
		if( is_object( $post ) ){
			$classes[] = sanitize_html_class( $post->post_name );
		}
	};
	return $classes;
}
add_filter('body_class', 'turbo_body_class' );
