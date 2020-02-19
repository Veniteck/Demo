<?php
/**
* Custom Widgets
*/

//require get_template_directory() . '/inc/widgets/featured-content.php';
//require get_template_directory() . '/inc/widgets/call-to-action.php';
require get_template_directory() . '/inc/widgets/shop-archive-filters.php';


// Register Custom Widgets
$widgets = array(
//    'featured_content_widget',
//    'call_to_action_widget',
	'shop_archive_filters_widget'
);

foreach ( $widgets as $widget ) {
    register_widget( $widget );
};
