<?php
/*
* Ozhands Retailers plugin
* admin content functions
*/

/**
 * displaying WooCommerce Product Custom Fields
 */
add_action('woocommerce_product_options_general_product_data', 'woocommerce_product_custom_fields');

function woocommerce_product_custom_fields () {
	global $woocommerce, $post;
	echo '<div class="product_custom_field">';
	// field of select variant subscription
    woocommerce_wp_select(
        array(
            'id' => '_subscription_for_retailer',
            'placeholder' => '',
            'label' => __('Subscription Type', 'woocommerce'),
            'options' => array(
            	'one' => __('Product Sinchronization', 'woocommerce'),
            	'two' => __('Upload CSV', 'woocommerce')
            )
        )
    );
    // field of product count 
    woocommerce_wp_text_input(
        array(
            'id' => '_retailer_product_count',
            'placeholder' => '',
            'label' => __('Product Count', 'woocommerce'),
            'value'       => get_post_meta( $post->ID, '_retailer_product_count', true ),
            'type' => 'number',
            'custom_attributes' => array(
                'step' => 'any',
                'min' => '0'
            )
        )
    );
	echo '</div>';
}

/**
 * saves  WooCommerce Product Custom Fields
 */
add_action( 'woocommerce_process_product_meta', 'woocommerce_product_custom_fields_save' );
function woocommerce_product_custom_fields_save($post_id) {
	global $post;

	$product = wc_get_product( $post_id );

	$subscription_for_retailer = $_POST['_subscription_for_retailer'];
	if (!empty($subscription_for_retailer)) {
		update_post_meta( $post_id, '_subscription_for_retailer', esc_attr($subscription_for_retailer) );
	}

	$retailer_product_count = $_POST['_retailer_product_count'];
	if (!empty($retailer_product_count)) {
		update_post_meta( $post_id, '_retailer_product_count', esc_attr($retailer_product_count) );
	}
}

/**
 * display link "retailer store block" in Admin Users table
 */
add_filter( 'user_row_actions', 'ozh_add_retailer_action', 10, 2);
function ozh_add_retailer_action( $actions, $user ) {
	if($user->roles[0] == 'retailer') {
		if ( get_user_meta( $user->ID, 'ozh_retailer_store_block', true ) == 'un_block' ) {

			$actions['ozhands_action'] = '<a href="/wp-admin/users.php?ozhands_action=block&user_id='.$user->ID.'"><span style="color: red;">Store Block</span></a>';
		}
		else {
			$actions['ozhands_action'] = '<a href="/wp-admin/users.php?ozhands_action=unblock&user_id='.$user->ID.'">Store Unblock</a>';
		}
	}
	return $actions;
}

/**
 * retailer store block/unblock actions
 */
add_action( 'admin_init', 'ozh_retailer_store_block' );
function ozh_retailer_store_block() {
	if ( substr( $_SERVER["REQUEST_URI"], 0, 19 ) == '/wp-admin/users.php' ) {
		if ( isset($_GET['ozhands_action']) && isset($_GET['user_id']) ) {
			if ( $_GET['ozhands_action'] == 'block' ) {
				update_user_meta( $_GET['user_id'], 'ozh_retailer_store_block', 'block' );
			}
			if ( $_GET['ozhands_action'] == 'unblock' ) {
				update_user_meta( $_GET['user_id'], 'ozh_retailer_store_block', 'un_block' );
			}
		}
	}
}

/*
 * Add 3 columns to Admin Users table
 */
add_filter( 'manage_users_columns', 'ozh_users_add_column', 30, 1 );
function ozh_users_add_column( $columns ) {
	$columns['product_limit'] = 'Limit';
	$columns['product_number'] = 'Number';
    return $columns;
}

add_filter( 'manage_users_custom_column', 'ozh_users_custom_column', 10, 3 );
function ozh_users_custom_column( $output, $column_name, $user_id ) {
	
	$user_meta = get_userdata( $user_id );
	if ( $user_meta->roles[0] == 'retailer' ) {
		if ($column_name == 'product_limit') {
			$output = ozh_get_retailer_product_limit( $user_id );
			if ( get_user_meta( $user_id, 'ozh_retailer_store_block', true ) != 'un_block' ) {
				$output .= ' &#9940;';
			}
		}
		if ($column_name == 'product_number') {
			$output = ozh_get_retailer_product_number( $user_id );
		}
	}
    return $output;
}
/** 
 * Admin Retailer Packages Table
 *
 *
 * Create new post type "package" and add to Menu
 */
add_action( 'init', 'retailer_packages' );
function retailer_packages() {
	$args = array(
		'labels'	=>	array(
				'name' => 'Packages',
				'all_items' => 'Packages',
				'add_new' => 'Add Package',
				'add_new_item' => 'Add new Package',
				'menu_name' =>	'Packages',
				'singular_name' => 'Package',
				'edit_item' => 'Edit Package',
				'new_item'  => 'New Package',
				'view_item' => 'View Package',
				'items_archive' => 'Package Archive',
				'search_items' => 'Search Package',
				'not_found' => 'No Package found',
				'not_found_in_trash' =>	'No Package found in trash'
			),
		'supports' => array( 'title', 'editor', 'author', 'page-attributes', 'thumbnail' ),
		'public' =>	true,
		'menu_position'	=>	35,
		'menu_icon' => 'dashicons-cart'
	);
	register_post_type( 'package', $args );
}

/**
 * Add the form with package meta-fields
 */
add_action( 'edit_form_after_editor', 'ozh_add_form_after_editor' );
function ozh_add_form_after_editor( $post ) {
	if ( $post->post_type === 'package' ) {
		require plugin_dir_path(__FILE__) . 'admin-packages-form.php';
	}
}

/**
 * save or update meta-data of post type "package"
 */
add_action('wp_ajax_'.'update_retailer_package_meta', 'update_retailer_package_meta');
add_action('wp_ajax_'.'update_retailer_package_meta', 'update_retailer_package_meta');
function update_retailer_package_meta() {
	$quantity = $_POST['data']['quantity'];
	$price = $_POST['data']['price'];
	$trial = $_POST['data']['trial'];
	$post_id = $_POST['data']['postID'];
	$quantity = $quantity === 'unlimited' ? 'unlimited' : (int)( $quantity );

	update_post_meta( $post_id, 'quantity_of_products', $quantity );
	update_post_meta( $post_id, 'price', (int)( $price ) );
	update_post_meta( $post_id, 'trial', (int)( $trial ) );
	$data = array( 'quantity' => $quantity, 'price' => $price, 'trial' => $trial );
	echo json_encode($data);
	die();
}

/**
 * Add courses columns to the Packages table
 */
add_filter( 'manage_package_posts_columns', 'add_packages_column' );
function add_packages_column($columns) {
	$n_columns = array();

	foreach($columns as $key => $value) {
		if ($key == 'author'){
			$n_columns['quantity'] = 'Quantity';
			$n_columns['price'] = 'Price';
			$n_columns['unlimited'] = 'Unlimited';			
			$n_columns['trial'] = 'Trial';
		}
		$n_columns[$key] = $value;
	}
	unset( $n_columns['wpseo-score'] );
	unset( $n_columns['wpseo-score-readability'] );
	unset( $n_columns['wpseo-links'] );
	return $n_columns;
}

/**
 * Change data packages column in Packages table
 */
add_action( 'manage_package_posts_custom_column' , 'add_packages_column_data', 10, 2 );
function add_packages_column_data( $column, $post_id ) {
	if ($column == 'quantity') {
		echo get_post_meta($post_id, 'quantity_of_products', true);
    }
	if ($column == 'price') {
		echo get_post_meta($post_id, 'price', true);
    }
	if ($column == 'unlimited') {
		if ( get_post_meta($post_id, 'quantity_of_products', true) == 'unlimited' ) {
			echo 'YES';
		} else {
			echo 'NO';
		}
    }
	if ($column == 'trial') {
		echo get_post_meta($post_id, 'trial', true);
    }
}