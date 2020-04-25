<?php

wp_enqueue_style('ozh-retailer-style', home_url().'/wp-content/plugins/ozhands_retailers/css/dashboard.css');

add_filter('dokan_get_template_part', 'ozh_dashboard_get_template_part', 10, 3);
function ozh_dashboard_get_template_part($template, $slug, $name) {
    if( $slug == 'dashboard/sales-chart-widget' ) {
        $template = plugin_dir_path(__FILE__) . 'templates/right-widget.php';
        return $template;
    }
    if( $slug == 'dashboard/big-counter-widget' ) {
        $template = plugin_dir_path(__FILE__) . 'templates/retailer-packages-widget.php';
        return $template;
    }
    if( $slug == 'dashboard/orders-widget' ) {
        $template = plugin_dir_path(__FILE__) . 'templates/subscriptions-widget.php';
    }
    if( $slug == 'dashboard/products-widget' ) {
        $template = plugin_dir_path(__FILE__) . 'templates/products-widget.php';
        return $template;
    }
    if ( $slug == 'products/products-listing' ) {
        $template = plugin_dir_path(__FILE__) . 'templates/products-listing.php';
    }
    if ( $slug == 'products/listing-filter' ) {
        $template = plugin_dir_path(__FILE__) . 'templates/listing-filter.php';
    }
    if ( $slug == 'settings/store-form' ) {
        $template = plugin_dir_path(__FILE__) . 'templates/store-form.php';
    }
    if ( $slug == 'dashboard/announcement-widget' ) {
        $template = '';
    }
    return $template;
}

add_filter( 'dokan_get_dashboard_nav', 'ozh_retailer_dashboard_nav', 99 );
function ozh_retailer_dashboard_nav( $urls ) {
    
    unset( $urls['orders'] );
    unset( $urls['withdraw'] );
    unset( $urls['coupons'] );
    unset( $settings['followers'] ); 
    unset( $urls['reports'] );
    unset( $settings['followers'] ); 
    unset( $urls['reviews'] );
    unset( $settings['followers'] ); 
    unset( $urls['followers'] );
    unset( $settings['followers'] );
    unset( $urls['support'] );
    unset( $urls['return-request'] );
    unset( $urls['settings']['sub']['payment']);
    unset( $urls['settings']['sub']['shipping']);
    unset( $urls['settings']['sub']['social']);
    unset( $urls['settings']['sub']['rma']);
    unset( $urls['settings']['sub']['seo']);
    return $urls;
}

/**
 * return array (subscriptions data) of current user
 */
function ozh_get_subscriptions_data( $user_id ) {

    $package_data = end( ozh_get_retailer_packages_data( $user_id ) );
    $today = strtotime( wp_date('d-m-Y') );
    $trial = ozh_get_trial_date( $user_id );

    if ( !$package_data && $trial ) {

        $data['start'] = date_i18n( 'j F Y',  get_user_meta( $user_id, 'ozh_trial_start_date', true ) );
        $data['finish'] = date_i18n( 'j F Y', $trial + $today );
        $data['product_id'] = get_user_meta( $user_id, 'ozh_package_id', true );
        $data['products_limit'] = ozh_get_retailer_product_limit( $user_id, $data['product_id'] );

        if ( get_user_meta( $user_id, 'ozh_retailer_store_block', true ) != 'block' ) {
            $data['status'] = 'active';
        }
        else {
            $data['status'] = 'block'; 
        }
        return $data;
    }
    if ( $package_data ) {

        $finish_date = $package_data['finish_date'] + $trial;
        $time = 0;
        $data['status'] = 'inactive';
        
        if ( $today < $finish_date ) {
            $time = ( $finish_date - $today ) / 86400;
        }
        $data['start'] = date_i18n( 'j F Y', $package_data['start_date'] );
        $data['finish'] = date_i18n( 'j F Y', $finish_date );
        $data['product_id'] = $package_data['package_id'];
        $data['products_limit'] = ozh_get_retailer_product_limit( $user_id, $data['product_id'] );

        if ( $time > 0 && get_user_meta( $user_id, 'ozh_retailer_store_block', true ) != 'block' ) {
            $data['status'] = 'active';
        }
        elseif ( $time > 0 && get_user_meta( $user_id, 'ozh_retailer_store_block', true ) == 'block' ) {
            $data['status'] = 'block';   
        }
    } 
    else {
        $data['status'] = 'inactive';
        $data['start'] = '-';
        $data['finish'] = '-';
        $data['product_id'] = '';
        $data['products_limit'] = '0';
    }       
    return $data;
}

/**
 * Removes Order Notes Title - Additional Information & Notes Field
 */
add_filter( 'woocommerce_enable_order_notes_field', '__return_false', 9999 );

/**
 * checkout fields editing
 */
add_filter( 'woocommerce_checkout_fields' , 'ozh_retailer_checkout_fields', 99 );
function ozh_retailer_checkout_fields( $fields ) {

    unset( $fields['billing']['billing_country'] );
    unset( $fields['billing']['billing_city'] );
    unset( $fields['billing']['billing_address_1'] );
    unset( $fields['billing']['billing_address_2'] );    
    unset( $fields['billing']['billing_city'] );     
    unset( $fields['billing']['billing_postcode'] );
    unset( $fields['billing']['billing_country'] );
    unset( $fields['billing']['billing_state'] );
    unset( $fields['order']['order_comments']['placeholder'] );
    unset( $fields['order']['order_comments']['label'] );

    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
    unset( $fields['billing']['billing_company'] );
    unset( $fields['billing']['billing_company']['label'] );
    unset( $fields['billing']['billing_company']['placeholder'] );
   
    return $fields;
}

/**
 * add account field
 */
add_filter('woocommerce_edit_account_form_start', 'ozh_retailer_add_field_edit_account_form');
function ozh_retailer_add_field_edit_account_form() {
    woocommerce_form_field(
            'user_url',
        array(
            'type'        => 'text',
            'required'    => true, // this doesn't make the field required, just adds an "*"
            'label'       => 'Site url',
        ),
        ozh_get_retailer_url( get_current_user_id() )
    );
}

/**
 * save account field value
 */
add_action( 'woocommerce_save_account_details', 'ozh_retailer_save_account_details' );
function ozh_retailer_save_account_details( $user_id ) {

    wp_update_user( array(
        'ID' => $user_id,
        'user_url' => $_POST[ 'user_url' ]
    ) );        
}

/**
 * errors handler
 */
add_action( 'woocommerce_save_account_details_errors','ozh_validate_custom_field', 10, 1 );
function ozh_validate_custom_field( $args ) {

    if ( isset($_POST[ 'user_url']) ) {
        if ( ozh_check_unique_url( $_POST[ 'user_url'] ) === false ) {
            $args->add( 'error', __( 'Sorry, this url address is already used!', 'woocommerce' ),'' );
        }
    }
}

/**
 * make account field required
 */
add_filter('woocommerce_save_account_details_required_fields', 'ozh_retailer_make_field_required');
function ozh_retailer_make_field_required( $required_fields ){
 
    $required_fields['user_url'] = 'Site url';
    return $required_fields; 
}

/**
 * return retailer url
 */
function ozh_get_retailer_url( $user_id ) {
    $user = get_user_by( 'id', $user_id );
    return $user->user_url;
}