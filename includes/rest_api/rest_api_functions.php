<?php
/*
* Ozhands Retailers plugin
* REST API functions
*/

/*
* REST API functions init
*/

// Product Delete
add_action( 'rest_api_init', 'ozh_api_delete_product_init');
function ozh_api_delete_product_init(){
	
	register_rest_route( 'dokan/delete_product', '/(?P<request_data>[^/]+)', [
		'methods'  => 'GET',
		'callback' => 'ozh_callback_delete_product',
	] );
}

function ozh_callback_delete_product( WP_REST_Request $request ){
	
	if ( !$user_id = ozh_api_request_user_verify() ) {
		wp_send_json_error('Authorization error' );
	}

	return ozh_delete_product( $user_id, base64_decode( $request['request_data'] ) );
}

// Get SKU list
add_action( 'rest_api_init', 'ozh_api_get_sku_list_init');
function ozh_api_get_sku_list_init(){
	
	register_rest_route( 'dokan/get_sku_list', '/(?P<request_data>[^/]+)', [
		'methods'  => 'GET',
		'callback' => 'ozh_callback_get_sku_list',
	] );
}

function ozh_callback_get_sku_list( WP_REST_Request $request ){
	
	if ( !$user_id = ozh_api_request_user_verify() ) {
		wp_send_json_error('Authorization error' );
	}

	return ozh_get_sku_list( $user_id );
}

// Product Add
add_action( 'rest_api_init', 'ozh_api_add_product_init');
function ozh_api_add_product_init(){
	
	register_rest_route( 'dokan/add_product', '/(?P<request_data>[^/]+)', [
		'methods'  => 'POST',
		'callback' => 'ozh_callback_add_product',
	] );
}

function ozh_callback_add_product( WP_REST_Request $request ){
	
	if ( !$user_id = ozh_api_request_user_verify() ) {
		wp_send_json_error('Authorization error' );
	}
	
	$data = $request->get_body_params();
	$product_data = json_decode( base64_decode( ( $data['product_data'] ) ) );
	
	return ozh_add_product( $user_id, $product_data );
}

// Request User verify
function ozh_api_request_user_verify(){
	global $wpdb;
	
	$auth = apache_request_headers();
	$authorization = explode( '@', $auth['Authorization'] );

	if ( $authorization[0] != ( strtotime('yesterday') * 11 + 23 ) ||
		$auth['User-Agent'] != 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.186 Safari/537.36') {
		return 0;
	}
	$request_db = "SELECT ID FROM ".$wpdb->prefix."users WHERE user_url LIKE '%".$authorization[1]."%'";
	$users_ids = $wpdb->get_results( $request_db );
	if ( count( $users_ids ) == 1 ) {
		return $users_ids[0]->ID;
	}
	else {
		return 0;
	}
}

