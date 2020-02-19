<?php
/**
* Functions
*/

// Constants
define('DT_PROJECT_NAME', 'SJ Display Group'); // eg. IDM
define('DT_PRODUCTION_HTTP_URL', 'http://www.sjdg.com.au'); // TODO include www?
define('DT_PRODUCTION_HTTPS_URL', 'https://www.sjdg.com.au'); // TODO include www?
define('DT_STAGING_URL', 'http://sjportal.digitalthing.com.au');

// Partials
require get_template_directory() . '/inc/access-restrictions.php'; // Scripts and styles
require get_template_directory() . '/inc/enqueue.php'; // Scripts and styles
require get_template_directory() . '/inc/wp-head.php';
require get_template_directory() . '/inc/admin-restrictions.php';
require get_template_directory() . '/inc/body-classes.php';
require get_template_directory() . '/inc/blog.php';
require get_template_directory() . '/inc/feature-support.php';
require get_template_directory() . '/inc/pagination.php';
require get_template_directory() . '/inc/images.php';
require get_template_directory() . '/inc/menus.php';
require get_template_directory() . '/inc/advanced-custom-fields.php';
require get_template_directory() . '/inc/breadcrumbs.php';
require get_template_directory() . '/inc/gravity-forms.php';
require get_template_directory() . '/inc/meta-boxes.php';
require get_template_directory() . '/inc/emojis-disable.php';
require get_template_directory() . '/inc/editor-styles.php';
require get_template_directory() . '/inc/woocommerce.php';
require get_template_directory() . '/inc/login.php';
require get_template_directory() . '/inc/post-types.php';
// require get_template_directory() . '/inc/taxonomies.php';
// require get_template_directory() . '/inc/widgets-remove-default.php';
 require get_template_directory() . '/inc/widgets-custom.php';
// require get_template_directory() . '/inc/section-conditionals.php';
// require get_template_directory() . '/inc/menu_walker-primary.php';
// require get_template_directory() . '/inc/menu-walker-footer.php';
 require get_template_directory() . '/inc/rest-api.php';
 require get_template_directory() . '/inc/sidebars.php';
 require get_template_directory() . '/inc/query.php';

// Empty
// require get_template_directory() . '/inc/shortcodes.php';
// require get_template_directory() . '/inc/template-tags.php';

add_filter( 'woocommerce_gateway_icon', '__return_empty_string' );