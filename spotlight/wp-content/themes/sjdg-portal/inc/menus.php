<?php
/**
* Menus
*/

add_theme_support('menus');

register_nav_menus(
	array(
		'primary' => 'Primary',
		'footer'  => 'Footer',
	)
);


/**
* Menu filters
*/
function dt_filter_nav_menu_item_args( $args, $item, $depth ) {

	if ( 'primary' == $args->theme_location ) {

		if( in_array( 'menu-item-has-children', $item->classes ) ){
			$args->link_before = '<span>';
			$args->link_after = '</span><span class="nav__arrow"><i class="ico-arrow-orange"></i></span>';
		} else {
			$args->link_before = '';
			$args->link_after = '';
		}

	}

	return $args;
}
add_filter( 'nav_menu_item_args', 'dt_filter_nav_menu_item_args', 10, 3 );
