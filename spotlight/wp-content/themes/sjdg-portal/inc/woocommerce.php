<?php

add_theme_support( 'woocommerce' );
//add_theme_support( 'wc-product-gallery-zoom' );

add_action( 'after_setup_theme', 'sj_wc_after_setup_theme' );

function sj_wc_after_setup_theme() {
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}

add_filter( 'woocommerce_before_widget_product_list', function(){ return '<div class="products">'; } );
add_filter( 'woocommerce_after_widget_product_list', function(){ return '</div>'; } );

function dt_filter_woocommerce_form_field_args ( $args ){

	$args['input_class'][] = 'field';

	$args['class'][] = 'form__col';

//	$key = array_search( 'form-row', $args['class'] );
//
//	if( $key !== false ){
//		unset( $args['class'][$key] );
//	}

	if( in_array( 'form-row-first', $args['class'] ) ){
		$args['class'][] = 'form__col--1of2';
	}

	if( in_array( 'form-row-last', $args['class'] ) ){
		$args['class'][] = 'form__col--1of2';
	}

	if( $args['type'] === 'country' ){
		$args['input_class'][] = 'select--enhanced';
	}

	if( $args['type'] === 'textarea' ){
		$args['input_class'][] = 'field--textarea';
	}

	return $args;

}
add_filter( 'woocommerce_form_field_args', 'dt_filter_woocommerce_form_field_args' );

function dt_store_extra_data_with_order_item_meta( $item_id, $item, $order_id ){


	if( is_a( $item, 'WC_Order_Item_Product' ) ){

		$product = $item->get_product();

		update_metadata( 'order_item', $item_id, '_sku', $product->get_sku() );
		update_metadata( 'order_item', $item_id, '_product_price', $product->get_price() );

	}

}
add_action( 'woocommerce_new_order_item', 'dt_store_extra_data_with_order_item_meta', 10, 3 );

//function dt_filter_woocommerce_pagination_args( $args ){
//
//	$args['next_text'] = 'Next';
//	$args['prev_text'] = 'Prev';
//
//	return $args;
//
//}
//add_filter( 'woocommerce_pagination_args', 'dt_filter_woocommerce_pagination_args' );

//wc_price_args
function dt_filter_wc_price_args( $args ){

    if( ! isset( $args['ex_tax_label'] ) ){

        if( ! is_cart() && ! is_checkout() && ! is_admin() && ! is_account_page() ){
            $args['ex_tax_label'] = true;
        }

    }

    return $args;

}
add_filter( 'wc_price_args', 'dt_filter_wc_price_args' );

function dt_filter_woocommerce_countries_ex_tax_or_vat( $return ){

    return in_array( WC()->countries->get_base_country(), array_merge( WC()->countries->get_european_union_countries( 'eu_vat' ), array( 'NO' ) ) ) ? __( '(ex. VAT)', 'woocommerce' ) : __( 'ex GST', 'woocommerce' );

}
add_filter( 'woocommerce_countries_ex_tax_or_vat', 'dt_filter_woocommerce_countries_ex_tax_or_vat' );

function dt_woocommerce_procesing_order_email_extras( $email_heading, $email ){
    if( isset($email->id) && $email->id === 'customer_invoice' ){
        echo '<p>Freight costs will be calculated and applied if required</p>';
    }
}
add_action( 'woocommerce_email_header', 'dt_woocommerce_procesing_order_email_extras', 20, 2 );

function dt_filter_woocommerce_account_menu_items( $items ){

    if( isset( $items['downloads'] ) ){

		$url = get_field( 'downloads_page', 'option' );

		if( ! $url ){
			unset( $items['downloads'] );
		}

    }

    if( isset( $items['edit-address'] ) ){
        unset( $items['edit-address'] );
    }

    if( isset( $items['edit-account'] ) ){
        unset( $items['edit-account'] );
    }

    if( isset( $items['dashboard'] ) ){
        unset( $items['dashboard'] );
    }

    return $items;

}
add_filter( 'woocommerce_account_menu_items', 'dt_filter_woocommerce_account_menu_items' );

function dt_filter_woocommerce_shipping_fields( $fields ){

    foreach( $fields as $key => $field ){

//        if( ! isset( $fields[$key]['custom_attributes'] ) ){
//            $fields[$key]['custom_attributes'] = [];
//        }
//
//        $fields[$key]['custom_attributes']['readonly'] = 'readonly';

		if( ! in_array( $key, [ 'billing_first_name', 'billing_last_name' ] ) && $field['required'] ){
			//$fields[$key]['required'] = false;
		}


    }

    return $fields;

}
add_filter( 'woocommerce_shipping_fields', 'dt_filter_woocommerce_shipping_fields' );
add_filter( 'woocommerce_billing_fields', 'dt_filter_woocommerce_shipping_fields' );

function dt_filter_woocommerce_get_endpoint_url( $url, $endpoint, $value, $permalink ){

    if( $endpoint === 'customer-logout' ){
        $url = wp_nonce_url( $url, 'customer-logout' );
    }

    if( $endpoint == 'downloads' ){
    	$url = get_field( 'downloads_page', 'option' );
    }

    return $url;

}
add_filter( 'woocommerce_get_endpoint_url', 'dt_filter_woocommerce_get_endpoint_url', 10, 4 );

function dt_woocommerce_hidden_field( $field, $key, $args, $value ){

    return sprintf( '<input type="hidden" name="%s" value="%s" />', $key, $value );

}
add_filter( 'woocommerce_form_field_hidden', 'dt_woocommerce_hidden_field', 10, 4 );

function dt_filter_wc_notices( $message ){

	if( $message === 'The cart has been filled with the items from your previous order.' ){

		$message = 'THE CART HAS BEEN FILLED WITH THE ITEMS FROM YOUR PREVIOUS ORDER - (NON-CATALOGUE ITEMS NOT INCLUDED)';

	}

	return $message;

}
add_filter( 'woocommerce_add_success', 'dt_filter_wc_notices' );

/**
 * Disable woocommerce_checkout_update_customer_data
 */
add_filter( 'woocommerce_checkout_update_customer_data', '__return_false' );

function sj_validate_checkout(){

	parse_str( $_POST['required_fields'], $required_fields );
	parse_str( $_POST['all_fields'], $all_fields );

	array_walk_recursive( $required_fields, 'wc_clean' );
	array_walk_recursive( $all_fields, 'wc_clean' );

	$errors = new WP_Error();

	if( strlen( $all_fields['billing_first_name'] ) > 30 ){
		$errors->add( 'validation', 'Billing First Name is too long. Must be a limit of 30 characters.' );
	}

	if( empty( $all_fields['billing_last_name'] ) ){
		$errors->add( 'validation', 'Billing Last Name cannot be empty' );
	}

	if( strlen( $all_fields['billing_last_name'] ) > 30 ){
		$errors->add( 'validation', 'Billing Last Name is too long. Must be a limit of 30 characters.' );
	}

	if( count( $errors->errors ) > 0 ){

		foreach ( $errors->get_error_messages() as $message ) {
			wc_add_notice( $message, 'error' );
		}

		if ( is_ajax() ) {
			// only print notices if not reloading the checkout, otherwise they're lost in the page reload.
			if ( ! isset( WC()->session->reload_checkout ) ) {
				ob_start();
				wc_print_notices();
				$messages = ob_get_clean();
			}

			$response = array(
				'result'   => 'failure',
				'messages' => isset( $messages ) ? $messages : '',
				'refresh'  => isset( WC()->session->refresh_totals ),
				'reload'   => isset( WC()->session->reload_checkout ),
			);

			unset( WC()->session->refresh_totals, WC()->session->reload_checkout );

			wp_send_json( $response );

			exit;
		}

	}

	$wat = 1;

}
//add_action( 'woocommerce_checkout_process', 'sj_validate_checkout', 10 );

//add_action( 'wc_ajax_wc_stripe_validate_checkout', array( $this, 'validate_checkout' ) );
add_action( 'wc_ajax_wc_stripe_validate_checkout', 'sj_validate_checkout', 5 );

/**
 * Change number of products that are displayed per page (shop page)
 */
add_filter( 'loop_shop_per_page', 'new_loop_shop_per_page', 20 );

function new_loop_shop_per_page( $cols ) {
  // $cols contains the current number of products per page based on the value stored on Options -> Reading
  // Return the number of products you wanna show per page.
  $cols = -1;
  return $cols;
}