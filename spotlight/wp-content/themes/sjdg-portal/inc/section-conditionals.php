<?php
/**
* Section conditionals
*/

// News
function dt_is_news_section() {
	if ( is_home() || is_category() || is_singular( 'post' ) ) {
		return true;
	}
	return false;
}

// Resources
function dt_is_resources_section() {
	if ( is_post_type_archive( 'resource' ) || is_singular( 'resource' ) ) {
		return true;
	}
	return false;
}

// Tenders
function dt_is_tenders_section() {
	if ( is_post_type_archive( 'tender' ) || is_singular( 'tender' ) ) {
		return true;
	}
	return false;
}
