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
add_action( 'rest_api_init', 'ozh_api_get_products_list_init');
function ozh_api_get_products_list_init(){
	
	register_rest_route( 'dokan/get_products_list', '/(?P<request_data>[^/]+)', [
		'methods'  => 'GET',
		'callback' => 'ozh_callback_get_products_list',
	] );
}

function ozh_callback_get_products_list( WP_REST_Request $request ){
	
	if ( !$user_id = ozh_api_request_user_verify() ) {
		wp_send_json_error('Authorization error' );
	}

	return ozh_get_products_list( $user_id );
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

// Product Update
add_action( 'rest_api_init', 'ozh_api_update_product_init');
function ozh_api_update_product_init(){
	
	register_rest_route( 'dokan/update_product', '/(?P<request_data>[^/]+)', [
		'methods'  => 'POST',
		'callback' => 'ozh_callback_update_product',
	] );
}

function ozh_callback_update_product( WP_REST_Request $request ){
	
	if ( !$user_id = ozh_api_request_user_verify() ) {
		wp_send_json_error('Authorization error' );
	}
	
	$data = $request->get_body_params();
	$product_data = json_decode( base64_decode( ( $data['product_data'] ) ) );
	
	return ozh_update_product( $user_id, $product_data );
}

// Request User verify
function ozh_api_request_user_verify(){
	global $wpdb;
	
	$auth = apache_request_headers();
	$authorization = explode( '@', $auth['Authorization'] );
	
	// Authorization format 'token'@'site_url'
	if ( count( $authorization ) != 2 ) {
		return 0;
	}
	
	// Get user ID by token
	$query = "SELECT user_id FROM ".$wpdb->prefix."usermeta WHERE meta_key = 'ozh_user_token' AND meta_value = '".base64_decode( $authorization[0] )."'";
	if ( !$users_id = $wpdb->get_var( $query ) ) {
		return 0;
	}
	
	// Check site URL
	$query = "SELECT user_url FROM ".$wpdb->prefix."users WHERE ID = '".$users_id."'";
	$user_url = $wpdb->get_var( $query );
	if ( strpos( $user_url, $authorization[1] ) === FALSE ) {
		return 0;
	}
	return $users_id;
}

