<?php
/**
* Advanced Custom Fields
*/

// Add options pages
if( function_exists('acf_add_options_page') ) {

	acf_add_options_page(array(
		'page_title' 	=> 'Theme General Settings',
		'menu_title'	=> 'Theme Settings',
		'menu_slug' 	=> 'theme-general-settings',
		'capability'	=> 'edit_posts',
		'redirect'		=> false
	));

}

function dt_filter_tertiary_menu_options( $field ) {

	// reset choices
	$field['choices'] = array();

	$menus = wp_get_nav_menus();

	$field['choices']['none'] = 'None';

	// loop through array and add to field 'choices'
	if( is_array($menus) ) {

		foreach( $menus as $menu ) {

			$field['choices'][ $menu->term_id ] = $menu->name;

		}

	}

	// return the field
	return $field;

}

add_filter('acf/load_field/name=tertiary_menu', 'dt_filter_tertiary_menu_options');
