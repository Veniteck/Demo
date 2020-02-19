<?php
/**
* Meta boxes
*/

// Move All In One SEO Pack meta box below ACF meta boxes
function dt_aioseop_post_metabox_priority() {
	return 'low' ;
}
add_filter( 'aioseop_post_metabox_priority', 'dt_wpseo_metabox_prio' );

// Move Yoast SEO meta box below ACF meta boxes
function dt_wpseo_metabox_prio() {
	return 'low' ;
}
//add_filter( 'wpseo_metabox_prio', 'dt_wpseo_metabox_prio' );
