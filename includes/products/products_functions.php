<?PHP
/*
* Ozhands Retailers plugin
* Products functions
*/

/*
* Add product to retailer list by Retailer Product ID
*/
function ozh_add_product( $user_id, $product_data ) {
	
	$user_status = ozh_get_user_status( $user_id );
	
	$action_data['notice_user'] = $user_status['notice'];
	
	if ( $user_status['status'] == 'not_active' || $user_status['status'] == 'reached' ) {
		$action_data['notice_type'] = 'error';
		$action_data['notice_text'] = "Product <strong>".$product_data->name."</strong> not added to the Ozhands list";

		wp_send_json_success( $action_data );
	}
	
	// Check product in list
	if ( ozh_is_product_in_list( $user_id, $product_data->retailer_product_id ) ) {
		$action_data['notice_type'] = 'error';
		$action_data['notice_text'] = "Product <strong>".$product_data->name."</strong> already included in the Ozhands list";
		wp_send_json_success( $action_data );
	}
	
	$action_data['limit'] = $user_status['limit'];
	
	// New product data from API Request
	$data = [
        'name'               => $product_data->name,
        'type'               => 'simple',
        'description'        => $product_data->description,
        'short_description'  => $product_data->short_description,
		'regular_price'      => wc_format_decimal( $product_data->regular_price ),
		'sale_price'         => $product_data->sale_price === '' ? '' : wc_format_decimal( $product_data->sale_price ),
		'catalog_visibility' => 'visible',
        'featured_image_id'  => absint( get_option( 'ozh_clear_image_id' ) ), // clear image id
    ];
	
	// Create new product
    $product = dokan()->product->create( $data );
	$product_id = $product->get_id();
	
	// Add additional product data
	// Tags
	wp_set_object_terms($product_id, $product_data->tag_names, 'product_tag');
	
	// Categories
	wp_set_object_terms($product_id, $product_data->category_names, 'product_cat');

	// Author
	$arg = array(
		'ID' => $product_id,
		'post_author' => $user_id,
	);
	wp_update_post( $arg );
	
	// Real image URL on retail site
	// update_post_meta( $product_id, 'ozh_image_url', $product_data->image_url );
	update_post_meta( $product_id, 'ozh_image_src', $product_data->image_src );
	update_post_meta( $product_id, 'ozh_image_srcset', $product_data->image_srcset );
	
	// Real image gallery URLs on retail site
	update_post_meta( $product_id, 'ozh_gallery_image_urls', json_encode( $product_data->gallery_image_urls ) );
	
	// Single Product URL
	update_post_meta( $product_id, 'ozh_single_product_url', $product_data->single_product_url );
	
	// Retailer Product ID
	update_post_meta( $product_id, '_original_id', ozh_create_original_id( $user_id, $product_data->retailer_product_id ) );

	// Product data for create additional Woocommerce postmeta in function ozhands_add_data_to_new_api_products
	// (in this moment Woocommerce functions not active). 
	update_post_meta( $product_id, 'ozh_new_retailer_product', json_encode( $product_data) );
	
	// SKU
	update_post_meta( $product_id, '_sku', $product_data->sku );
	
	// Product successfully added Notice
	$action_data['notice_type'] = 'success';
	$action_data['notice_text'] = "Product <strong>".$product_data->name."</strong> successfully added to your Ozhands store | Used ".ozh_get_retailer_product_number( $user_id )." positions";
	wp_send_json_success( $action_data );
}

/*
* Update product to retailer list by Retailer Product ID
*/
function ozh_update_product( $user_id, $product_data ) {
	
	$user_status = ozh_get_user_status( $user_id );
	
	$action_data['notice_user'] = $user_status['notice'];
	
	if ( $user_status['status'] == 'not_active' || $user_status['status'] == 'reached' ) {
		$action_data['notice_type'] = 'error';
		$action_data['notice_text'] = "Product <strong>".$product_data->name."</strong> not updated on the Ozhands list";

		wp_send_json_success( $action_data );
	}
	
	// Check product in list
	$product_id = ozh_is_product_in_list( $user_id, $product_data->retailer_product_id );
	if ( !$product_id ) {
		$action_data['notice_type'] = 'error';
		$action_data['notice_text'] = "Product <strong>".$product_data->name."</strong> not exist in the Ozhands list";
		wp_send_json_success( $action_data );
	}
	
	$action_data['limit'] = $user_status['limit'];
	
	// Product data from API Request
	// Main data
	$data = [
        'ID'             	 => $product_id,
		'post_title'         => $product_data->post_title,
        'post_content'       => $product_data->post_content,
        'post_expert'		 => $product_data->post_expert,
		'post_name'	 		 => $product_data->post_name,
		'guid'				 => $product_data->guid,
    ];
	wp_update_post( wp_slash( $data ) );

	// Product prices
	update_post_meta( $product_id, '_regular_price', $product_data->regular_price );
	update_post_meta( $product_id, '_sale_price', $product_data->sale_price );
	update_post_meta( $product_id, '_price', $product_data->price );
	
	// Add additional product data
	// Tags
	wp_set_object_terms($product_id, $product_data->tag_names, 'product_tag');
	
	// Categories
	wp_set_object_terms($product_id, $product_data->category_names, 'product_cat');

	
	// Real image URL on retail site
	// update_post_meta( $product_id, 'ozh_image_url', $product_data->image_url );
	update_post_meta( $product_id, 'ozh_image_src', $product_data->image_src );
	update_post_meta( $product_id, 'ozh_image_srcset', $product_data->image_srcset );
	
	// Real image gallery URLs on retail site
	update_post_meta( $product_id, 'ozh_gallery_image_urls', json_encode( $product_data->gallery_image_urls ) );
	
	// Single Product URL
	update_post_meta( $product_id, 'ozh_single_product_url', $product_data->single_product_url );
	
	// SKU
	update_post_meta( $product_id, '_sku', $product_data->sku );
	
	// Product successfully added Notice
	$action_data['notice_type'] = 'success';
	$action_data['notice_text'] = "Product <strong>".$product_data->name."</strong> successfully updated in your Ozhands store | Used ".ozh_get_retailer_product_number( $user_id )." positions";
	wp_send_json_success( $action_data );
}

/*
* Delete product from retailer list by Retailer Product ID
*/
function ozh_delete_product( $user_id, $product_id ) {
	
	$user_status = ozh_get_user_status( $user_id );
	
	$action_data['notice_user'] = $user_status['notice'];
	
	if ( $user_status == 'not_active' ) {
		$action_data['notice_type'] = 'error';
		$action_data['notice_text'] = "Product not deleted from the Ozhands list";

		wp_send_json_success( $action_data );
	}
	
	$action_data['limit'] = $user_status['limit'];
	
	// Get retailer products
	$args = array(
		'posts_per_page' => -1,
		'paged'          => 1,
		'author'         => $user_id,
		'tax_query'      => array(
			array(
				'taxonomy' => 'product_type',
				'field'    => 'slug',
				'terms'    => array(),
				'operator' => 'NOT IN',
			),
		),
	);
	$product_query = dokan()->product->all( $args );
	
	// Get Product ID
	if ( $products = $product_query->posts ) {
		foreach ( $products as $product ) {
			if ( get_post_meta( $product->ID, '_original_id', true ) == ozh_create_original_id( $user_id, $product_id ) ) {
				$product_id = $product->ID;
				$product_name = $product->post_title;
				break;
			}
		}
	}
	
	// Product delete
	if ( $product_id ) {
		global $wpdb;
		
		$wpdb->delete( $wpdb->prefix.'posts', array( 'ID' => $product_id ) );
		$wpdb->delete( $wpdb->prefix.'postmeta', array( 'post_id' => $product_id ) );
		
		// dokan()->product->delete( $product_id, true );
		$action_data['notice_type'] = 'warning';
		$action_data['notice_text'] = "Product <strong>".$product_name."</strong> successfully deleted from your Ozhands store | Used ".ozh_get_retailer_product_number( $user_id );
		wp_send_json_success( $action_data );
	}
	else {
		wp_send_json_error('Invalid ID '.$product_id);
	}
}

/*
* Get retailer products SKU list by site_ur
*/
function ozh_get_products_list( $user_id ) {
	global $wpdb;
	
	$user_status = ozh_get_user_status( $user_id );
	
	$action_data['notice_user'] = $user_status['notice'];
	
	if ( $user_status == 'not_active' ) {
		$action_data['notice_type'] = 'error';
		$action_data['notice_text'] = "";

		wp_send_json_success( $action_data );
	}
	
	// Get retailer products
	$args = array(
		'posts_per_page' => -1,
		'paged'          => 1,
		'author'         => $user_id,
		'tax_query'      => array(
			array(
				'taxonomy' => 'product_type',
				'field'    => 'slug',
				'terms'    => array(),
				'operator' => 'NOT IN',
			),
		),
	);
	$product_query = dokan()->product->all( $args );
	
	// Create responce
	$action_data['notice_text'] = '';
	if ( $products = $product_query->posts ) {
		foreach ( $products as $product ) {
			$retailer_product_id = ozh_get_retailer_product_id( (int)get_post_meta( $product->ID, '_original_id', true ) );
			if ( $retailer_product_id ) {
				$action_data['notice_text'] .= ','.$retailer_product_id;
			}
		}
		$action_data['notice_text'] = substr( $action_data['notice_text'], 1 );
	}
	$action_data['notice_type'] = 'success';
	$action_data['limit'] = $user_status['limit'];
	
	wp_send_json_success( $action_data );
}

/*
* Get retailer products use number
*/
function ozh_get_retailer_product_number( $user_id ) {
	global $wpdb;
	
	// Get retailer products
	$args = array(
		'posts_per_page' => -1,
		'paged'          => 1,
		'author'         => $user_id,
		'tax_query'      => array(
			array(
				'taxonomy' => 'product_type',
				'field'    => 'slug',
				'terms'    => array(),
				'operator' => 'NOT IN',
			),
		),
	);
	$product_query = dokan()->product->all( $args );
	
	if ( $product_query->posts ) {
		return count($product_query->posts);
	}
	return 0;
}

/*
* Is product in list
*/
function ozh_is_product_in_list( $user_id, $product_id ) {
	global $wpdb;
	
	// Get retailer products
	$args = array(
		'posts_per_page' => -1,
		'paged'          => 1,
		'author'         => $user_id,
		'tax_query'      => array(
			array(
				'taxonomy' => 'product_type',
				'field'    => 'slug',
				'terms'    => array(),
				'operator' => 'NOT IN',
			),
		),
	);
	$product_query = dokan()->product->all( $args );
	
	if ( $products = $product_query->posts ) {
		foreach ( $products as $product ) {
			if ( $product_id == ozh_get_retailer_product_id( get_post_meta( $product->ID, '_original_id', true ) ) ) {
				return $product->ID;
			}
		}
	}
	return FALSE;
}

/**
 * return array of orders id's by user id
 */
function ozh_get_orders_ids( $user_id ) {

    $customer_orders = get_posts(array(
        'numberposts' => -1,
        'meta_key' => '_customer_user',
        'meta_value' => $user_id,
        'orderby' => 'date',
        'order' => 'ASC',        
        'post_type' => 'shop_order',
        'post_status' => 'wc-processing'
    ));
    $order_array = [];
    if ( $customer_orders ) {

	    foreach ($customer_orders as $customer_order) {
	        $order = wc_get_order($customer_order);
	        $order_array[] = ['ID' => $order->get_id()];
	    }
	}
	return $order_array;
}

/**
 * @param integer $user_id
 * @param integer $package_id (Retailer Package)
 * @return mixed:
 * 				integer (quantity products active subcription)
 * 				string "unlimited" if quantity == unlimited
 */
function ozh_get_retailer_product_limit( $user_id, $package_id = 0 ) {
	
    $product_limit = 0;
	$order_array = ozh_get_orders_ids( $user_id );
	$trial = ozh_get_trial_date( $user_id );
	// if the retailer already bought the trial package before	
	if ( !$order_array && $trial ) {
		if ( $package_id ) {
			if ( get_user_meta( $user_id, 'ozh_package_id', $package_id ) == $package_id ) {
				$product_limit = get_user_meta( $user_id, 'ozh_package_product_limit', true );
				return (int)$product_limit;			
			}			
		}
		else {
			$product_limit = get_user_meta( $user_id, 'ozh_package_product_limit', true );
			return (int)$product_limit;
		}
	}				
    if ( $order_array ) {
    	$today = strtotime( wp_date('d-m-Y') );

    	$finish_dates = [];
    	if ( $package_id ) {

	        foreach ( $order_array as $order ) {
				if ( get_post_meta( $order['ID'], 'package_id', true ) == $package_id ) {
		        	$finish_dates[] = [
		        		'finish_date' => get_post_meta( $order['ID'], 'finish_date', true ),
		        		'order_id' => $order['ID']
	        		];								
				}
			}
	    }
	    else {
	        foreach ( $order_array as $order ) {
	        	$finish_date = get_post_meta( $order['ID'], 'finish_date', true );
	        	if ( $finish_date ) {	            
		        	$finish_dates[] = [
		        		'finish_date' => $finish_date,
		        		'order_id' => $order['ID']
	        		];				
	            }
        	}
        }

        if ( $finish_dates ) {
	    	$finish_date = end($finish_dates);

	    	if ( $today < $finish_date ) {
	    		$product_limit = get_post_meta( $finish_date['order_id'], 'package_product_limit', true );
	    		$product_limit = $product_limit == 'unlimited' ? 'unlimited' : (int)$product_limit;	    			
	    	}       	
        }
    }
    return $product_limit;
}

/**
 * User Store blocked Warning
 */
function ozh_is_store_blocked( $user_id ) {
	
	if ( get_user_meta( $user_id, 'ozh_retailer_store_block', true ) != 'un_block' ) {
		return '<strong>Your Ozhands store is locked</strong>';
    }
    return '';
}

/**
 * retailer ban (do not display products on the site if retailer in block or do not payd for Usage)
 */
add_action( 'posts_where', 'ozh_block_retailer_posts', 10, 2 );
function ozh_block_retailer_posts( $where, \WP_Query $query  ) {

	if( !is_admin() ) {	

		global $wpdb;
	    $users = get_users( ['role' => 'retailer'] );
		// do not dispay this product never
		$product_subscription = get_option('ozh_retailer_subscription');
	    $post_ids = [];

	    foreach ($users as $user ) {
	        $author_id = $user->data->ID;
	        $product_limit =  ozh_get_retailer_product_limit($author_id);
	        if ( get_user_meta( $author_id, 'ozh_retailer_store_block', true ) === 'block'  || $product_limit == 0 ) {

	            $post_id = $wpdb->get_col( "SELECT ID FROM ".$wpdb->prefix."posts WHERE post_author = $author_id AND post_status = 'publish' AND post_type = 'product'");
	            $post_ids[] =  $post_id;
	        }      
	    }
    	if ( $post_ids ) {
			$str = '';

            foreach ($post_ids as $post_id) {
            	if ( is_array($post_id) ) {
	            	if ( $post_id ) {
		                $post_id = implode(',', $post_id);
	                    $str .= $post_id.',';          		
	            	}             		
            	}             
            }
        	if ( $str !== '' ) {
        		$str .= $product_subscription;
            	$where .= " AND ".$wpdb->prefix."posts.ID NOT IN ($str)";
            	
            } else {
            	$where .= " AND ".$wpdb->prefix."posts.ID NOT IN ($product_subscription)";
            }	        	
    	}
	}
    return $where;
}

function ozh_get_user_status( $user_id ) {
	
	// Retailer package has been blocked by the administrator
	if ( get_user_meta( $user_id, 'ozh_retailer_store_block', true ) == 'block' ) {
		
		$user_status['status'] = 'not_active';
		$user_status['notice'] = "<span style='color: red;'> | Your package has been blocked by the Ozhands administrator</span>";
	}
	
	$retailer_product_limit = ozh_get_retailer_product_limit( $user_id );
	
	// Retailer don't have active package
	if ( !$retailer_product_limit && $retailer_product_limit != 'unlimited' ) {
		
		$user_status['status'] = 'not_active';
		$user_status['notice'] = "<span style='color: red;'> | You don't have active retailer package in Ozhands </span>";
	}
	
	// Retailer packege has been reached
	if ( ozh_get_retailer_product_number( $user_id ) >= (int)$retailer_product_limit && $retailer_product_limit != 'unlimited' ) {
		
		$user_status['status'] = 'reached';
		$user_status['notice'] = "<span style='color: red;'> | Your Ozhands limit ".$retailer_product_limit." products has been reached</span>";
	}
	
	// Retailer packege active
	if ( !$user_status ) {
		$user_status['status'] = 'active';
		$user_status['notice'] = ' | Your limit is '.$retailer_product_limit;
	}
	
	// Retailer packege time limit < 3 days
	if ( $user_status['status'] == 'active' ) {
		$time_limit = ozh_get_retailer_time_limit( $user_id );
		if ( $time_limit < 4 ) {
			$user_status['notice'] .= "<span style='color: red;'> | Your Ozhands package ends in ".$time_limit." days</span>";
		}
	}
	
	$user_status['notice'] .= "<a href='http://sashatest.upsite.top/dashboard' class='button' style='margin-left: 20px;' target='_blank'>Ozhands dashboard</a>";
	
	if ( get_user_meta( $user_id, 'ozh_retailer_store_block', true ) == 'block' ) {
		$user_status['limit'] = 0;
	}
	else {
		$user_status['limit'] = $retailer_product_limit;
	}
	
	return $user_status;
}

/**
 * return array of retailer package data
 */
function ozh_get_retailer_packages_data( $user_id ) {

	$packages_data = [];

    $order_array = ozh_get_orders_ids( $user_id );

    if ( $order_array ) {
        foreach ( $order_array as $order ) {
            $package_id = get_post_meta( $order['ID'], 'package_id', true );
            if ( $package_id ) {               
                $packages_data[] = [
                	'package_id' => $package_id,
                	'finish_date' => get_post_meta( $order['ID'], 'finish_date', true ),
                	'start_date' => get_post_meta( $order['ID'], 'start_date', true ),
                	'order_id' => $order['ID']
                ];              
            }
        }        
    }
    return $packages_data;
}

/**
 * return active retailer package id
 */
function ozh_get_retailer_package_id( $user_id ) {

	$packages_data = ozh_get_retailer_packages_data( $user_id );

	if ( $packages_data ) {
		$package_id = end($packages_data);
		$package_id = $package_id['package_id'];
	}
	return $package_id;
}

/**
 * Get Retailer time limit. Days to package ended
 */
function ozh_get_retailer_time_limit( $user_id ) {

	$time = 0;
	// if the retailer already bought the trial package before
    $trial = ozh_get_trial_date( $user_id );
	$packages_data = ozh_get_retailer_packages_data( $user_id );

	if ( !$packages_data && $trial ) {
		$time = $trial / 86400;
	} 
	if ( $packages_data ) {
		$today = strtotime( wp_date('d-m-Y') );
		$finish_date = end($packages_data);
		$finish_date = $finish_date['finish_date'] + $trial;

        if ( $today < $finish_date) {
            $time = ( $finish_date - $today ) / 86400;
        } 
	}
	return (int)$time;
}

/**
 * Create original id 
 */
function ozh_create_original_id( $user_id, $retailer_product_id ) {

	$original_id = 1000000 + (int)$user_id + ( (int)$retailer_product_id * 10000000 );
	return $original_id;
}

/**
 * Get retailer product id 
 */
function ozh_get_retailer_product_id( $original_id ) {

	$retailer_product_id = (int)($original_id / 10000000);
	return $retailer_product_id;
}

/**
 * Return the days number of action of the trial package
 */
function ozh_get_trial_date( $user_id ) {

	$trial = get_user_meta( $user_id, 'ozh_trial_finish_date', true );

	if ( $trial ) {
		$start = strtotime( wp_date('d-m-Y') );
		// ...and active days left
		if ( $start < $trial ) {
			$trial = $trial - $start;
		}
		else {
			$trial = 0;
		}
	}
	return (int)$trial;
}