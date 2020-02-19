<?php
/**
* Enqueue
*/

// Scripts
function dt_enqueue_js_global() {

	if ( ! is_admin() ) {

//		wp_enqueue_script('app-js', get_template_directory_uri() . '/assets/build/assets/js/bundle.js', [ 'jquery' ], '1.0', true );
		wp_enqueue_script('sj-main', get_template_directory_uri() . '/assets/build/assets/js/main.js', [ 'jquery' ], '1.0', true );
//		wp_enqueue_script('youtube-iframe-api', '//www.youtube.com/iframe_api', '', true, true );

		wp_localize_script( 'sj-main', 'wpApiSettings', array( 'root' => esc_url_raw( rest_url() ), 'nonce' => wp_create_nonce( 'wp_rest' ) ) );


	}

	if ( class_exists( 'woocommerce' ) ) {

		/**
		 * self::enqueue_script( 'selectWoo' );
		self::enqueue_style( 'select2' );

		 */

		wp_dequeue_style( 'select2' );
		wp_deregister_style( 'select2' );

		wp_dequeue_script( 'selectWoo');
		wp_deregister_script('selectWoo');
//
//		wp_dequeue_script( 'wc-address-i18n');
//		wp_deregister_script( 'wc-address-i18n' );

	}

}
add_action( 'wp_enqueue_scripts', 'dt_enqueue_js_global' , 100 );


// Styles
function dt_enqueue_css() {

	wp_enqueue_style( 'app-css', get_template_directory_uri() . '/assets/build/assets/css/bundle.css', array(), null, 'all');

}
add_action( 'wp_enqueue_scripts', 'dt_enqueue_css' );
