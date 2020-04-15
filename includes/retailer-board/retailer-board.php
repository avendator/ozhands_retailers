<?php

wp_enqueue_style('ozh-retailer-style', home_url().'/wp-content/plugins/ozhands_retailers/css/dashboard.css');

add_filter('dokan_get_template_part', 'dashboard_get_template_part', 10, 3);
function dashboard_get_template_part($template, $slug, $name) {
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
    return $template;
}

add_filter( 'dokan_get_dashboard_nav', 'retailer_dashboard_nav' );
function retailer_dashboard_nav( $urls ) {
    
    unset( $urls['orders'] );
    unset( $urls['withdraw'] );
    unset( $urls['settings']['sub']['payment']);
    return $urls;
}

/**
 * return array (subscriptions data) of current user
 */
function get_subscriptions_data( $user_id ) {

    $status = ozh_get_retailer_time_limit( $user_id );
    $package_data = end( ozh_get_retailer_packages_data( $user_id ) );

    if ( $package_data ) {
        $today = strtotime( wp_date('d-m-Y') );

        if ( $today < $package_data['finish_date'] ) {
            $time = ( $package_data['finish_date'] - $today ) / 86400;
        }
        $data['start'] = date_i18n( 'j F Y', $package_data['start_date'] );
        $data['finish'] = date_i18n( 'j F Y', $package_data['finish_date'] );
        $data['product_id'] = $package_data['package_id'];
        $data['products_limit'] = ozh_get_retailer_product_limit( $user_id, $data['product_id'] );

        if ( $time !== 0 && get_user_meta( $user_id, 'ozh_retailer_store_block', true ) != 'block' ) {
            $data['status'] = 'active';
        }
        elseif ( $time !== 0 && get_user_meta( $user_id, 'ozh_retailer_store_block', true ) == 'block' ) {
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
add_filter( 'woocommerce_checkout_fields' , 'retailer_checkout_fields', 99 );
function retailer_checkout_fields( $fields ) {

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

    $fields['billing']['billing_company']['label'] = 'Site name';
    $fields['billing']['billing_company']['placeholder'] = $current_user->user_url;
    $fields['billing']['billing_company']['required'] = true;
    $fields['billing']['billing_first_name']['placeholder'] = $current_user->user_firstname;
    $fields['billing']['billing_last_name']['placeholder'] = $current_user->user_lastname;
    $fields['billing']['billing_phone']['placeholder'] = get_user_meta( $user_id, 'phone', true );
   
    return $fields;
}

/**
 * add account field
 */
add_filter('woocommerce_edit_account_form_start', 'retailer_add_field_edit_account_form');
function retailer_add_field_edit_account_form() {
    woocommerce_form_field(
            'user_url',
        array(
            'type'        => 'text',
            'required'    => true, // this doesn't make the field required, just adds an "*"
            'label'       => 'Site name',
        ),
        get_retailer_url( get_current_user_id() )
    );
}

/**
 * save account field value
 */
add_action( 'woocommerce_save_account_details', 'retailer_save_account_details' );
function retailer_save_account_details( $user_id ) {
 
    wp_update_user( array(
        'ID' => $user_id,
        'user_url' => $_POST[ 'user_url' ]
   ) ); 
}

/**
 * make account field required
 */
add_filter('woocommerce_save_account_details_required_fields', 'retailer_make_field_required');
function retailer_make_field_required( $required_fields ){
 
    $required_fields['user_url'] = 'Site name';
    return $required_fields; 
}

/**
 * return retailer url
 */
function get_retailer_url( $user_id ) {
    $user = get_user_by( 'id', $user_id );
    return $user->user_url;
}