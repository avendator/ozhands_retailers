<?php

/**
 * Retailer Registration
 */
class Registration {

	public function __construct() {
		// validate registration
		add_action( 'dokan_registration_form_role', array( $this, 'retailer_registration_form_role' ) );
		add_filter( 'dokan_register_user_role', array( $this, 'oz_register_retailer_role' ) );
		add_filter( 'woocommerce_process_registration_errors', array( $this, 'oz_validate_registration', 11 ) );
		add_filter( 'woocommerce_process_registration_errors', array( $this, 'oz_validate_registration', 11 ) );
        // after registration
        add_filter( 'woocommerce_new_customer_data', array( $this, 'set_new_retailer_names', 11 ) );
        add_action( 'woocommerce_created_customer', array( $this, 'save_retailer_info' ), 11, 2 );
	}

	/**
	 * Add retailer radio button to registration form
	 */
	public function retailer_registration_form_role() {
	    ?>
	    <label class="radio">
	        <input type="radio" name="role" value="retailer"<?php checked( $role, 'retailer' ); ?>>
	        <?php echo 'I am a retailer'; ?>
	    </label>
	    <?php
	}

	/**
	 * Add retailer role
	 * @param array $allowed_roles
	 * @return array $allowed_roles
	 */
	public function oz_register_retailer_role( $allowed_roles ) {
		$allowed_roles = array( 'customer', 'seller', 'retailer' );
		return $allowed_roles;
	}

    /**
     * Validate retailer registration
     * @param  \WP_Error $error
     * @return \WP_Error
     */
	public function oz_validate_registration( $error ) {
        if ( is_checkout() ) {
            return $error;
        }
        if ( defined( 'WP_CLI' ) || defined( 'REST_REQUEST' ) ) {
            return $error;
        }
        $post_data   = wp_unslash( $_POST );
        $nonce_check = true;

        if ( $nonce_check ) {
            $nonce_value = isset( $post_data['_wpnonce'] ) ? $post_data['_wpnonce'] : '';
            $nonce_value = isset( $post_data['woocommerce-register-nonce'] ) ? $post_data['woocommerce-register-nonce'] : $nonce_value;

            if ( ! wp_verify_nonce( $nonce_value, 'woocommerce-register' ) ) {
                return new WP_Error( 'nonce_verification_failed', 'Nonce verification failed' );
            }
        }
        $allowed_roles = array( 'customer', 'seller', 'retailer' );
        // is the role name allowed or user is trying to manipulate?
        if ( isset( $post_data['role'] ) && !in_array( $post_data['role'], $allowed_roles ) ) {
            return new WP_Error( 'role-error', 'Cheating, eh?' );
        }
        $role = $post_data['role'];

        $required_fields = array(
            'fname'    => 'Please enter your first name.',
            'lname'    => 'Please enter your last name.',
            'phone'    => 'Please enter your phone number.',
            'shopname' => 'Please provide a shop name.',
        );
        if ( $role == 'retailer' ) {
            foreach ( $required_fields as $field => $msg ) {
                if ( empty( trim( $post_data[$field] ) ) ) {
                    return new WP_Error( "$field-error", $msg );
                }
            }
        }
        return $error;
	}

	/**
     * Inject first and last name to WooCommerce for new vendor registraion
	 * @param array $data
	 * @return array $data
	 */
	public function set_new_retailer_names( $data ) {
        $post_data = wp_unslash( $_POST ); // WPCS: CSRF ok.

        $allowed_roles = array( 'customer', 'seller', 'retailer' );
        $role          = ( isset( $post_data['role'] ) && in_array( $post_data['role'], $allowed_roles ) ) ? $post_data['role'] : 'customer';

        $data['role'] = $role;

        if ( $role == 'seller' || $role == 'retailer' ) {
	        $data['first_name']    = strip_tags( $post_data['fname'] );
	        $data['last_name']     = strip_tags( $post_data['lname'] );
	        $data['user_nicename'] = sanitize_user( $post_data['shopurl'] );
        }
        return $data;
	}

	/**
	 * Adds default dokan store settings when a new retailer registers
	 * @param int $user_id
     * @param array $data
	 * @return void
	 */
	public function save_retailer_info( $user_id, $data ) {
		# ...code...
	}
}

