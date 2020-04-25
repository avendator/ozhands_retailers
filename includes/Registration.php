<?php

/**
 * Registration new Retailer
 */
function ozh_new_retailer_registration() {

	$user_url = trim ( mb_strtolower( $_POST['shopurl'] ) );
	
    if ( ozh_check_unique_url( $_POST['shopurl'] ) === false ) {
		$error = new WP_Error( 'not_unique_url', __( 'Sorry, this url address is already used!' ) );
		return $error->get_error_message();	
    }

	$userdata = array(
		'user_pass'  => $_POST['password'],
		'user_email' => $_POST['email'],
		'first_name' => $_POST['fname'],
		'last_name'  => $_POST['lname'],
		'user_login' => $_POST['shopname'],
		'user_url'	 => $_POST['shopurl'],
		'role' 		 => $_POST['role'],
		'show_admin_bar_front' => 'false'
 	);
 	$user_id = wp_insert_user( $userdata );

	if( ! is_wp_error( $user_id ) ) {
		update_user_meta($user_id, 'billing_phone', $_POST['phone']);
		return true;
	} else {
		return $user_id->get_error_message();
	} 
}

add_shortcode('retailer_registration', 'ozh_retailer_registration');
function ozh_retailer_registration() {

	if( isset($_POST['retailer_register']) ) {
		$register = ozh_new_retailer_registration();
	}
	if ( $register === true ) {
		echo "<script>window.location.assign('/my-account')</script>";
	}
	require_once plugin_dir_path( __FILE__ ).'retailer-board/templates/registration-form.php';
}

/**
 * Check the user url or Uniqueness
 * @return false or true
 */
function ozh_check_unique_url( $user_url ) {
    global $wpdb;

	$user_url = trim ( mb_strtolower( $user_url ) );
    $patterns = array('#https://#', '#http://#', '#www#');
    $user_url = preg_replace($patterns, '', $user_url);
    $pos = stripos( $user_url, '/' );

    if ( $pos !== false ) {
        $user_url = substr($user_url, 0, $pos);
    }

    $result = true;

    $user_urls = $wpdb->get_results( "SELECT user_url FROM ".$wpdb->prefix."users");

    foreach ( $user_urls as $url ) {
        $url = preg_replace($patterns, '', $url->user_url);
        $pos = stripos( $url, '/' );
        if ( $pos !== false ) {
            $url = substr($url, 0, $pos);
        }
        if ( $user_url === mb_strtolower($url) ) {
        	$result = false;
            return $result;
        }
    }
    return $result;
}

/**
 * Add Retailer usermeta
 */
function ozh_add_retailer_meta( $user_id, $new ) {
	
	$user_meta = get_userdata($user_id);
	$user_roles = $user_meta->roles;
	
	if ( $user_roles[0] == 'retailer' && !get_user_meta( $user_id, 'ozh_zip_name', true ) ) {
	
		update_user_meta($user_id, 'billing_first_name', $user_meta->user_firstname);
		update_user_meta($user_id, 'billing_last_name', $user_meta->user_lastname);
		update_user_meta($user_id, 'dokan_enable_selling', 'yes');
		update_user_meta($user_id, 'dokan_publishing', 'yes');

		$file = plugin_dir_path(__FILE__).'zip_create/ozhands_connection/ozhands_token.php';

		$files_to_zip = array(
			plugin_dir_path(__FILE__).'zip_create/ozhands_connection/ozhands_token.php',
			plugin_dir_path(__FILE__).'zip_create/ozhands_connection/ozhands_connection.php',
			plugin_dir_path(__FILE__).'zip_create/ozhands_connection/includes/admin_functions.php',
			plugin_dir_path(__FILE__).'zip_create/ozhands_connection/includes/requests_functions.php',
			plugin_dir_path(__FILE__).'zip_create/ozhands_connection/img/ajax-loader.gif'
		);
		
		$zip = new zip_create();
		$zip_data = $zip->get_zip_data( $file, $files_to_zip );
		if( !$zip_data ) {
			return false;
		}
		update_user_meta( $user_id, 'ozh_user_token', $zip_data['token'] );
		update_user_meta( $user_id, 'ozh_zip_name', $zip_data['name'] );
		
		if ( $new ) {
			update_user_meta($user_id, 'ozh_retailer_store_block', 'un_block');
		}
		
		return true;
	}
}

