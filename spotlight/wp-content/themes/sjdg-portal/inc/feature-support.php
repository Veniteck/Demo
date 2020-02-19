<?php
/**
* Feature support
*/

// Pages: remove featured image and excerpt support
add_action( 'init', 'dt_post_type_support' );
function dt_post_type_support() {
	remove_post_type_support( 'page', 'thumbnail' ); // Featured Image
	remove_post_type_support( 'page', 'excerpt' );
}
