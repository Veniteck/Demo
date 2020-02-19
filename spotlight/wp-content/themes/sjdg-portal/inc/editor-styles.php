<?php
/**
* Theme Editor Styles
*/

function turbo_theme_editor_styles() {
	add_editor_style( 'assets/css/editor_style.css' );
}
add_action('admin_init', 'turbo_theme_editor_styles' );
