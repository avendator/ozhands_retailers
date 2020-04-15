<?php

/**
 * Registration new Retailer
 */
function new_retailer_registration() {

	$userdata = array(
		'user_email' => $_POST['email'],
		'user_pass'  => $_POST['password'],
		'first_name' => $_POST['fname'],
		'last_name'  => $_POST['lname'],
		'user_login' => $_POST['shopname'],
		'user_url'	 => $_POST['shopurl'],
		'phone'      => $_POST['phone'],
		'role' 		 => $_POST['role'],
		'show_admin_bar_front' => 'false'
 	);

	$user_id = wp_insert_user( $userdata ) ;

	if( ! is_wp_error( $user_id ) ) {
		update_user_meta($user_id, 'phone', $_POST['phone']);
		update_user_meta($user_id, 'dokan_enable_selling', 'yes');
		update_user_meta($user_id, 'dokan_publishing', 'yes');
		return true;
	} else {
		return $user_id->get_error_message();
	} 
}