<?php
/**
* Query
*/

function dt_pre_get_posts( WP_Query $query ){

	if( is_admin() ){
		return;
	}

	if( $query->is_main_query() && ! defined( 'REST_REQUEST' ) ){

		if( isset( $_POST['search_category'] ) && ! empty( $_POST['search_category'] ) ){

            $query->set( 'tax_query', [
                [
                    'taxonomy' => 'product_cat',
                    'terms'    => intval( $_POST[ 'search_category' ] )
                ]
            ] );

        }

	}

}
add_action( 'pre_get_posts', 'dt_pre_get_posts' );

/**
 * Extend WordPress search to include custom fields
 *
 * https://adambalee.com
 */

/**
 * Join posts and postmeta tables
 *
 * http://codex.wordpress.org/Plugin_API/Filter_Reference/posts_join
 */
function cf_search_join( $join, $query ) {
	global $wpdb;

	if ( $query->is_search ) {
		$join .=' LEFT JOIN '.$wpdb->postmeta. ' ON '. $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ';
	}

	return $join;
}
add_filter('posts_join', 'cf_search_join', 10, 2 );

/**
 * Modify the search query with posts_where
 *
 * http://codex.wordpress.org/Plugin_API/Filter_Reference/posts_where
 */
function cf_search_where( $where, $query ) {
	global $pagenow, $wpdb;

	if ( $query->is_search ) {
		$where = preg_replace(
			"/\(\s*".$wpdb->posts.".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
			"(".$wpdb->posts.".post_title LIKE $1) OR (".$wpdb->postmeta.".meta_value LIKE $1)", $where );
	}

	return $where;
}
add_filter( 'posts_where', 'cf_search_where', 10, 2 );

/**
 * Prevent duplicates
 *
 * http://codex.wordpress.org/Plugin_API/Filter_Reference/posts_distinct
 */
function cf_search_distinct( $where, $query ) {
	global $wpdb;

	if ( $query->is_search ) {
		return "DISTINCT";
	}

	return $where;
}
add_filter( 'posts_distinct', 'cf_search_distinct', 10, 2 );