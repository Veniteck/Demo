<?php
/**
* Custom API endpoints for News and Resources functionality
*/

/**
* News endpoint
*/
function dt_filter_products_endpoint( $data ){

	$query_args = [
		'post_type'   => 'product',
		'posts_per_page' => 15
	];

	$page = ( isset( $_GET['page'] ) ) ? intval( $_GET['page'] ) : false ;

	if( $page ){
		$query_args['paged'] = $page;
	}

	$categories = ( isset( $_REQUEST['filters'] ) ) ? $_REQUEST['filters'] : false ;

	if( $categories ){

		if( $categories[0] !== '' ){

			$query_args['tax_query'] = [
				[
					'taxonomy' => 'product_cat',
					'terms' => $categories,
					'operator' => 'AND'
				]
			];

		}

	}

	$wc_query = new WC_Query();

	$ordering  = $wc_query->get_catalog_ordering_args();

	if( isset ( $ordering['orderby'] ) ){
		$query_args['orderby'] = $ordering['orderby' ];
	}

	if( isset ( $ordering['order'] ) ){
		$query_args['order'] = $ordering['order'];
	}

	if( isset ( $ordering['meta_key'] ) ){
		$query_args['meta_key'] = $ordering['meta_key'];
	}



	$query = new WP_Query( $query_args );

	$page = ( $query->get( 'paged' ) > 0 ) ? $query->get( 'paged' ) : 1;

	ob_start();

	if( $query->have_posts() ):

		while( $query->have_posts() ): $query->the_post();

			do_action( 'woocommerce_shop_loop' );

			wc_get_template_part( 'content', 'product' );

		endwhile;

	else :

		get_template_part( 'components/products/no-results' );

	endif;

	$html = ob_get_clean();

	$response = [
		//'posts'    => $query->posts,
		'html'            => $html,
		'page'            => $page,
		'has_more'        => $query->max_num_pages > $page,
//		'pagination_html' => $pagination_html
	];

	return new WP_REST_Response( $response, 200 );

}

function dt_autocomplete_search_endpoint() {

    $query = wp_kses( $_POST[ 'query' ], array() );
    $type = wp_kses( $_POST[ 'type' ], array() );

    $query_args = array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        's'              => $query,
        'posts_per_page' => 20,
//		'meta_query' => [
//			[
//				'key' => '_sku',
//				'value' => $query,
//				'compare' => 'LIKE',
//			]
//		]
    );

    if ( $type !== '' ) {
        $query_args[ 'tax_query' ] = array(
            array(
                'taxonomy' => 'product_cat',
                'terms'    => $type,
            )
        );
    }

    $_query = new WP_Query( $query_args );

    $results = array(
        'suggestions' => array()
    );

    $posts = ( $_query->have_posts() ) ? $_query->posts : array();

    if ( !empty( $posts ) ) {
        foreach ( $posts as &$post ) {

            $post->permalink = get_permalink( $post );

            $arr = array(
                'value' => $post->post_title . ' - ' . get_post_meta( $post->ID, '_sku', true ),
                'data'  => $post
            );

            $results[ 'suggestions' ][] = $arr;

        }
    }

    return new WP_REST_Response( $results, 200 );

}

function dt_load_more_endpoint( WP_REST_Request $request ) {

//    $wat = dt_get_products_from_wc_json_api( $_POST['orderby'], intval( $_POST[ 'page' ] ) , $_POST['filters'] );
//
//    if( is_user_logged_in() ){
//
//    	$_wat = 1;
//
//	}

//	$products = wc_get_products( [
//		'sort' => $_POST['sort']
//	] );
//
//	$wat = 1;

    global $wp_the_query;

    /**
     * Initialize WC Query.
     */
    $wc_query = new WC_Query();

    $page = intval( $_POST[ 'page' ] );

    $query_args = [
        'post_type'      => 'product',
        'posts_per_page' => 16,
        'paged'          => $page,
        //'orderby'        => 'menu_order'
    ];

	$query_args['tax_query'] = [];

    if( isset( $_POST['sort'] ) ){

		switch ( $_POST['sort'] ) {

			case 'date':

				$query_args['orderby'] = 'date';
				$query_args['order'] = 'DESC';

				break;

			case 'price':

				$query_args['orderby'] = 'meta_value_num';
				$query_args['orderby_meta_key'] = '_price';
				$query_args['order'] = 'ASC';

				break;

			case 'price-desc':

				$query_args['orderby'] = 'meta_value_num';
				$query_args['orderby_meta_key'] = '_price';
				$query_args['order'] = 'DESC';

				break;

			case 'popularity':

				$query_args['orderby'] = 'meta_value_num';
				$query_args['orderby_meta_key'] = 'total_sales';
				$query_args['order'] = 'DESC';

				break;

			case 'rating':

				$query_args['orderby'] = 'meta_value_num';
				$query_args['orderby_meta_key'] = 'average_rating';
				$query_args['order'] = 'DESC';

				break;

		}

	}

    if ( isset( $_POST[ 'filters' ] ) && !empty( $_POST[ 'filters' ] ) ) {

        $query_args[ 'tax_query' ] = [
            [
                'taxonomy' => 'product_cat',
                'terms'    => (array)$_POST[ 'filters' ]
            ]
        ];

    }

    $query = $wp_the_query = new WP_Query( $query_args );

    ob_start();

    if ( $query->have_posts() ): while ( $query->have_posts() ): $query->the_post();

        wc_get_template_part( 'content', 'product' );

    endwhile; endif;

    $html = ob_get_clean();

    $response = [
        'html'     => $html,
        'page'     => $page,
        'has_more' => $query->max_num_pages > $page
    ];

    return new WP_REST_Response( $response );

}

function dt_get_products_from_wc_json_api( $orderby, $page, $categories ){

	$user = wp_get_current_user();

	$url = add_query_arg( [
		'orderby'  => $orderby,
		'page'     => $page,
		'per_page' => 12,
		'category' => $categories,
		'user_id' => $user->get( 'id' ),
		'auth' => md5( 'auth-' . $user->user_email )
	], get_woocommerce_api_url( 'products' ) );

	$ch = curl_init( $url );

	//key ck_cf5e1b224456d6403c13a3ea1bc153ae90143c0b
	//secret cs_6e6c19ff92d457d15d43ce2a3b0baff81b3e1bb3

	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'X-WP-Nonce: ' . wp_create_nonce( 'wp_rest' ),
	));

	curl_setopt($ch,CURLOPT_URL, $url );
	curl_setopt($ch,CURLOPT_RETURNTRANSFER, true );
//    curl_setopt($ch,CURLOP, true );
	$result = curl_exec($ch);

	curl_close($ch);



    return $result;

}

/**
* Register REST API Routes
*/
add_action( 'rest_api_init', function () {

	// News
	register_rest_route( 'sjgroup/', '/filter_products', array(
		'methods' => [ 'GET', 'POST' ],
		'callback' => 'dt_filter_products_endpoint',
	) );

    register_rest_route( 'sjgroup', '/autocomplete_search', array(
        'methods'  => 'POST',
        'callback' => 'dt_autocomplete_search_endpoint',
    ) );

    register_rest_route( 'sjgroup', '/load_products', array(
        'methods'  => 'POST',
        'callback' => 'dt_load_more_endpoint',
    ) );

} );

//eturn apply_filters( 'woocommerce_rest_check_permissions', $permission, $context, $object_id, $post_type );
function make_read_products_public( $permission, $context, $object_id, $post_type ){

    var_dump( $context );
    exit;

    return $permission;

}
add_filter( 'woocommerce_rest_check_permissions', 'make_read_products_public' );
