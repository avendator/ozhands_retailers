<?php

/**
 *
 * @wordpress-plugin
 * Plugin Name:       Ozhands Retailers
 * Description:       Plugin by https://www.ozhands.com.au/ to work with retailers
 * Version:           1.0.0
 * Author:            upsite.top
 * Author URI:        /team
 */
 
global $ozhands_name;
$ozhands_name = 'Ozhands'; // Name in Admin Panel

require_once plugin_dir_path(__FILE__) . 'includes/retailers_requests/retailers_requests.php';
require_once plugin_dir_path(__FILE__) . 'includes/rest_api/rest_api_functions.php';
require_once plugin_dir_path(__FILE__) . 'includes/products/products_functions.php';
require_once plugin_dir_path(__FILE__) . 'includes/csv_import/csv_import.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/admin_functions.php';
require_once plugin_dir_path(__FILE__) . 'includes/registration.php';
require_once plugin_dir_path(__FILE__) . 'includes/products/ozh_retailer_packages.php';
require_once plugin_dir_path(__FILE__) . 'includes/emails/custom_emails.php';
require_once plugin_dir_path(__FILE__) . 'includes/timers/day_timer.php';
require_once plugin_dir_path(__FILE__) . 'includes/zip_create/zip_create.php';

$ozh_custom_emails = new OZH_custom_emails;

if ( is_admin() ) {
	wp_enqueue_style('ozh-retailer-admin', home_url().'/wp-content/plugins/ozhands_retailers/css/retailer-admin.css');
}
wp_enqueue_style('retailer-registration-form', home_url().'/wp-content/plugins/ozhands_retailers/css/retailer-registration-form.css');
wp_enqueue_style('ozh-retailer-packages', home_url().'/wp-content/plugins/ozhands_retailers/css/retailer-packages.css');

register_activation_hook( __FILE__, function() {

    if ( ! function_exists( 'get_plugins' ) ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    $all_plugins = get_plugins();

    foreach ($all_plugins as $key => $value) {

        if ( $pos = stripos( $key, '/dokan.php') ) {
            $version = substr( $key, 0, $pos );
            $version = substr( $version, 6 );
        }
    }
    if ( $version === NULL ) {
        $version = 'lite';
    }
	if ( !is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
		die('It requires WooCommerce in order to work.');
	} 
	elseif ( !is_plugin_active( 'dokan-'.$version.'/dokan.php' ) ) {
		die('It requires Dokan in order to work.'); 
	} 
	else {
		$seller = get_role( 'seller' );
		add_role('retailer', 'Retailer', $seller->capabilities);
		ozh_creating_reatiler_posts();
		ozh_upload_image();
		
		if ( ! wp_next_scheduled( 'ozh_task_hook' ) ) {
		    $day = '1 day';
    		$time = (new DateTime('now 00:00:00'))->modify($day)->getTimestamp();
			wp_schedule_event( $time, 'daily', 'ozh_task_hook' );
		}
	}
});

register_deactivation_hook( __FILE__, 'ozh_retailers_deactivation' );
function ozh_retailers_deactivation() {
    remove_role( 'retailer' );
}

add_action ('plugins_loaded', 'ozh_retailers_init');
function ozh_retailers_init() {

	$cuser = wp_get_current_user();
	if($cuser->roles[0] == 'retailer') {
		require_once plugin_dir_path(__FILE__) . 'includes/retailer-board/retailer-board.php';
	}	
}

/**
 * add image for ratailer products to media library
 */
function ozh_upload_image() {

	if ( !get_option('ozh_clear_image_id') ) {	
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

	    $file = plugin_dir_url( __FILE__ ).'img/no-retailer-image.png';
	    $src = media_sideload_image( $file, 0, 'no-retailer-image', 'src');
		if( is_wp_error($src) ){
			echo $src->get_error_message();
		}
		else {
			$path = wp_upload_dir();
			$filename =  $path['path'].'/no-retailer-image.png';
			$wp_upload_dir = wp_upload_dir();
		    $attachment = array(
		    	'guid' => $wp_upload_dir['url'] . '/' . basename( $filename ),
		    	'post_mime_type' => 'image/png',
		    	'post_title' => 'no-retailer-image',
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
		// $attr['srcset'] = get_post_meta( $post->ID, 'ozh_image_srcset', true );
		unset( $attr['srcset'] );
	}
	return $attr;
}

/**
 * Replace link on Sign UP
 */
add_filter('dokan_get_template_part', 'ozh_retailer_get_template_part', 10, 2);
function ozh_retailer_get_template_part( $template, $slug ) {
    if( $slug == 'global/header-menu' ) {
        $template = plugin_dir_path(__FILE__) . 'includes/retailer-board/templates/header-menu.php';        
    }
    return $template;
}

/**
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

/**
 * Creating posts, pages and shortcodes
 */
function ozh_creating_reatiler_posts() {
	if ( !get_page_by_path('retailer-registration') ) {
		wp_insert_post( array(
			'post_name'    => 'retailer-registration',
			'post_content'  => '[retailer_registration]',
			'post_status'   => 'publish',
			'post_author'   => 1,
			'post_type' 	=> 'page',
			'post_title' 	=> 'Register'
		) );
	}
	if ( !get_page_by_path('thank-you-page') ) {
		wp_insert_post( array(
			'post_name'    => 'thank-you-page',
			'post_content'  => 'Congratulations on activating the trial version of the Package!',
			'post_status'   => 'publish',
			'post_author'   => 1,
			'post_type' 	=> 'page',
			'post_title' 	=> 'Congratulations!'
		) );
	}

	if ( !get_page_by_path('csv-import') ) {
		wp_insert_post( array(
			'post_name'    => 'csv-import',
			'post_content'  => '[csv_import]',
			'post_status'   => 'publish',
			'post_author'   => 1,
			'post_type' 	=> 'page',
			'post_title' 	=> 'CSV import'
		) );
	}

	if ( !get_page_by_path('retailer-packages') ) {
		wp_insert_post( array(
			'post_name'    => 'retailer-packages',
			'post_content'  => '[retailer_packages]',
			'post_status'   => 'publish',
			'post_author'   => 1,
			'post_type' 	=> 'page',
			'post_title' 	=> 'Retailer Packages'
		) );
	}

	$product_id = get_option('ozh_retailer_subscription');
	$product = get_post($product_id);
	if ( !$product ) {
		$product_id = wp_insert_post( array(
			'post_title'    => 'retailer-subscription',
			'post_content'  => '',
			'post_status'   => 'publish',
			'post_author'   => 1,
			'post_type' 	=> 'product',
			'meta_input'    => [
				'_price' => '0',
				'_stock_status' => 'instock',
				'_virtual' => 'yes'
			],
		) );
		update_option( 'ozh_retailer_subscription', $product_id );		
	}	
}


/**
 * Add usermeta to new Retailer
 */
add_action('user_register', 'ozh_user_register', 5);
function ozh_user_register( $user_id) {

	ozh_add_retailer_meta( $user_id, true );
}

/**
 * Add Retailer usermeta after user edit
 */
add_action('profile_update', 'ozh_profile_update', 5);
function ozh_profile_update( $user_id) {

	ozh_add_retailer_meta( $user_id, false );
}