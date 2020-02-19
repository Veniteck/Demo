<?php
/**
 * Gravity Forms
 */

// Changes the default Gravity Forms AJAX spinner
function dt_io_custom_gforms_spinner( $src ) {
	return get_stylesheet_directory_uri() . '/assets/css/images/svg/dt_loader.svg';

}
add_filter( 'gform_ajax_spinner_url', 'dt_io_custom_gforms_spinner' );
