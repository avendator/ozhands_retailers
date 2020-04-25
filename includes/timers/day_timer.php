<?php
/*
* Ozhands Retailers plugin
* Check all retailers packagesю Send email to Admin and Retailer if package ends in 3 day
*/
add_action( 'ozh_task_hook', 'ozh_day_timer' );
function ozh_day_timer() {
	
	$retailers = get_users( [
		'role'         => 'retailer',
	] );
	foreach ( $retailers as $retailer ) {
		$retailer_time_limit = ozh_get_retailer_time_limit( $retailer->ID );
		
		// if ( $retailer->ID == 3 ) {
		if ( $retailer_time_limit == 3 ) {
			$email = get_userdata($retailer->ID)->user_email;
			$emails = WC()->mailer()->get_emails();
			$emails['OZH_package_ends']->trigger( $email );
		}
		else {
			if ( $package_ended = ozh_package_has_expired( $retailer->ID ) ) {
				$email = get_userdata($retailer->ID)->user_email;
				$emails = WC()->mailer()->get_emails();
				$emails['OZH_package_ended']->trigger( $email );
			}
		}
	}
}

/**
 * Checks if package has expired today
 * @return bool
 */
function ozh_package_has_expired( $user_id ) {

	$result = false;
	$today = strtotime( wp_date('d-m-Y') );
	$package_data = end( ozh_get_retailer_packages_data( $user_id ) );

	if ( $package_data ) {
		if ( (int)$package_data['finish_date'] == (int)$today ) {
			return $result = true;
		}
	}
	else {
		$trial = get_user_meta( $user_id, 'ozh_trial_finish_date', true );
		if ( $trial ) {
			if ( (int)$today == (int)$trial ) {
				return $result = true;
			}
		}
	}
	return $result;
}