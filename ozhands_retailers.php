<?php

/**
 *
 * @wordpress-plugin
 * Plugin Name:       Ozhands Retailers
 * Description:       Plugin by https://www.ozhands.com.au/ to work with retaikers
 * Version:           1.0.0
 * Author:            upsite.top
 * Author URI:        /team
 */
 
// require_once plugin_dir_path(__FILE__) . 'includes/admin_functions.php';
// require_once plugin_dir_path(__FILE__) . 'includes/requests_functions.php';
// require_once plugin_dir_path(__FILE__) . 'includes/rest_api_functions.php';

// global $ozhands_products_ids;

require_once plugin_dir_path(__FILE__) . 'includes/retailers_requests/retailers_requests.php';
require_once plugin_dir_path(__FILE__) . 'includes/rest_api/rest_api_functions.php';
require_once plugin_dir_path(__FILE__) . 'includes/products/products_functions.php';
require_once plugin_dir_path(__FILE__) . 'includes/csv_import/csv_import.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/admin_functions.php';
require plugin_dir_path(__FILE__) . 'includes/Registration.php';
require plugin_dir_path(__FILE__) . 'includes/products/ozh_retailer_packages.php';
if ( is_admin() ) {
	wp_enqueue_style('ozh-retailer-admin', home_url().'/wp-content/plugins/ozhands_retailers/css/retailer-admin.css');
	wp_enqueue_script('ozh-retailer-adm', home_url().'/wp-content/plugins/ozhands_retailers/js/admin.js', array('jquery'), '', true );
}

// Alex test page
add_shortcode('alex_test', 'alex_test');
function alex_test() {
	// global $wpdb;
	
	// $request = "SELECT post_id, meta_value FROM ".$wpdb->prefix."postmeta WHERE meta_key = 'ozh_new_retailer_product' AND meta_value != ''";
	// $new_api_products = $wpdb->get_results( $request );
	// foreach ( $new_api_products as $new_api_product ) {
		// $new_product_data = json_decode( $new_api_product->meta_value );
		// if ( is_object( $new_product_data ) ) {
			// echo '<br>'.$new_api_product->post_id.'___';
			// print_r($new_product_data);
			// $product = new WC_Product( $new_api_product->post_id );
			// $product->set_stock_status( $new_product_data->stock_status );
			// $product->set_stock_quantity( $new_product_data->stock_quantity );
			// $product->set_sku( $new_product_data->sku );
			// echo '<br>+++++'.$product->get_id().'++++++'.$product->get_sku();
			// update_post_meta( $new_api_product->post_id, 'ozh_new_retailer_product', '' );
		// }
	// }
}

register_activation_hook( __FILE__, 'ozhands_retailers_instal' );
function ozhands_retailers_instal(){
    $seller = get_role( 'seller' );
    add_role('retailer', 'Retailer', $seller->capabilities);  
}

register_deactivation_hook( __FILE__, 'ozhands_retailers_deactivation' );
function ozhands_retailers_deactivation() {
    remove_role( 'retailer' );
}

add_action ('plugins_loaded', 'ozhands_retailers_init');
function ozhands_retailers_init() {

	$cuser = wp_get_current_user();
	if($cuser->roles[0] == 'retailer') {
		require_once plugin_dir_path(__FILE__) . 'includes/retailer-board/retailer-board.php';
	}	
}

add_action ('init', 'ozhands_upload_image');
function ozhands_upload_image() {

	if ( !get_option('ozh_clear_image_id') ) {	
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

	    $file = plugin_dir_url( __FILE__ ).'img/no-image.png';
	    $src = media_sideload_image( $file, 0, 'no-image', 'src');
		if( is_wp_error($src) ){
			echo $src->get_error_message();
		}
		else {
			$path = wp_upload_dir();
			$filename =  $path['path'].'/no-image.png';
			$wp_upload_dir = wp_upload_dir();
		    $attachment = array(
		    	'guid' => $wp_upload_dir['url'] . '/' . basename( $filename ),
		    	'post_mime_type' => 'image/png',
		    	'post_title' => 'no-image',
		    	'post_content' => '',
		    	'post_status' => 'inherit'
	    	);
		    $attach_id = wp_insert_attachment( $attachment, $filename );
		    update_option( 'ozh_clear_image_id', $attach_id );
		}
	}
}

// add product metadata
// add_action ('init', 'ozhands_add_data_to_new_api_products');
function ozhands_add_data_to_new_api_products() {
	global $wpdb;
	
	$request = "SELECT post_id, meta_value FROM ".$wpdb->prefix."postmeta WHERE meta_key = 'ozh_new_retailer_product' AND meta_value != ''";
	$new_api_products = $wpdb->get_results( $request );
	foreach ( $new_api_products as $new_api_product ) {
		$new_product_data = json_decode( $new_api_product->meta_value );
		if ( is_object( $new_product_data ) ) {
			$product = new WC_Product( $new_api_product->post_id );
			$product->set_stock_status( $new_product_data->stock_status );
			$product->set_stock_quantity( $new_product_data->stock_quantity );
			update_post_meta( $new_api_product->post_id, 'ozh_new_retailer_product', '' );
		}
	}
}

/*
* Change ozhands image to real image from retailer site
*/
add_filter( 'wp_get_attachment_image_attributes', 'ozh_get_attachment_image_attributes', 10, 3 );
function ozh_get_attachment_image_attributes( $attr, $attachment, $size ) {

	global $post;
	$user_meta = get_userdata( $post->post_author );
	if ( $user_meta->roles[0] == 'retailer' && $attachment->ID == (int)get_option( 'ozh_clear_image_id' ) ) {
		$attr['src'] = get_post_meta( $post->ID, 'ozh_image_src', true );
		$attr['srcset'] = get_post_meta( $post->ID, 'ozh_image_srcset', true );
	}
	return $attr;
}

/*
* Set Purchasable false for retailers products
*/
add_filter( 'woocommerce_is_purchasable', 'ozh_hide_add_to_cart_button', 10, 2 );
function ozh_hide_add_to_cart_button ( $is_purchasable = true, $product ) {
	
	global $post;
	$user_meta = get_userdata( $post->post_author );
	$user_roles = $user_meta->roles;
	if ( $user_roles[0] == 'retailer' ) {
		return false;
	}
	else {
		return $is_purchasable;
	}
}

/*
* Add Button Purchase from Retailer for retailers Single products
*/
add_action( 'woocommerce_product_meta_start', 'ozh_product_meta_start' );
function ozh_product_meta_start() {
	global $post;

	$user_meta = get_userdata( $post->post_author );
	$user = get_user_by( 'id', $post->post_author );
	$user_roles = $user_meta->roles;
	if ( $user_roles[0] == 'retailer' ) {
		if ( $ozh_single_product_url = get_post_meta( $post->ID, 'ozh_single_product_url', true ) ) {
			$user_site_url = $ozh_single_product_url;
		}
		else {
			$user_site_url = preg_replace( "#/$#", "", $user->user_url ).'/product/'.$post->post_name;
		}
		echo '<a href="'.$user_site_url.'">
			<input type="button" class="dokan-btn-theme" id="purchase-from-retailer-btn" value="Purchase from Retailer">
		</a>';
	}
}

// woocommerce_after_add_to_cart_quantity

// add_filter( 'dokan_dashboard_nav_common_link', 'test_dashboard_nav_common_link' );
// function test_dashboard_nav_common_link( $common_links ) {
// 	return '';
// }
