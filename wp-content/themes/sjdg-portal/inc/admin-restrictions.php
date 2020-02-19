<?php
/**
* Admin restrictions
*/

// Hide admin menu items for certain URLs
function remove_admin_menu_items() {

	// get the current site url
    $site_url = get_bloginfo( 'url' );

    // an array of protected site urls
    $protected_urls = array(
		DT_PRODUCTION_HTTP_URL,
        DT_PRODUCTION_HTTPS_URL,
        DT_STAGING_URL
    );

    // Check if the current site url is in the protected urls array
    if ( in_array( $site_url, $protected_urls ) ) {

        // Hide menu items

		// remove_menu_page( 'index.php' ); // Dashboard
		// remove_menu_page( 'edit.php' ); // Posts
		// remove_menu_page( 'upload.php' ); // Media
		// remove_menu_page( 'edit.php?post_type=page' ); // Pages
		// remove_menu_page( 'edit-comments.php' ); // Comments
		// remove_menu_page( 'themes.php' ); // Appearance
		// remove_menu_page( 'plugins.php' ); // Plugins
		// remove_menu_page( 'users.php' ); // Users
		// remove_menu_page( 'tools.php' ); // Tools
		// remove_menu_page( 'options-general.php' ); // Settings

		// Legacy code
        /*
		global $menu;
		$restricted = array( __( 'Tools' )  );
		end( $menu );
		while ( prev( $menu ) ) {
			$value = explode( ' ', $menu[ key( $menu ) ][0] );
			if ( in_array( $value[0] != null ? $value[0] : "", $restricted ) ) {
				unset( $menu[ key( $menu ) ] );
			}
		} */

    }

}
add_action( 'admin_menu', 'remove_admin_menu_items' );


// Hide ACF in admin menu for certain URLs
function dt_hide_acf_admin() {

    // Get the current site url
    $site_url = get_bloginfo( 'url' );

    // An array of protected site urls
    $protected_urls = array(
		DT_PRODUCTION_HTTP_URL,
        DT_PRODUCTION_HTTPS_URL,
        DT_STAGING_URL
    );

    // Check if the current site url is in the protected urls array
    if ( in_array( $site_url, $protected_urls ) ) {

        // Hide the ACF menu item
        return false;

    } else {

        // Show the ACF menu item
        return true;

    }

}
//add_filter('acf/settings/show_admin', 'dt_hide_acf_admin');
