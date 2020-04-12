<?PHP
/*
* Ozhands Retailers plugin
* Products functions
*/

/*
* Add product to retailer list by Product ID (ID from retailer site)
*/
function ozh_add_product( $user_id, $product_data ) {
	
	// Check retailer product limit
	$retailer_product_limit = ozh_get_retailer_product_limit( $user_id );
	if ( ozh_get_retailer_product_number( $user_id ) >= $retailer_product_limit ) {
		$action_data['notice_type'] = 'error';
		$action_data['notice_text'] = 'Your Ozhands limit '.$retailer_product_limit.' products has been reached';
		wp_send_json_success( $action_data );
	}
	
	// Check product in list
	if ( ozh_is_product_in_list( $user_id, $product_data->sku ) ) {
		$action_data['notice_type'] = 'error';
		$action_data['notice_text'] = 'Product SKU '.$product_data->sku.' already in the Ozhands list';
		wp_send_json_success( $action_data );
	}
	
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

	// Product data for create additional Woocommerce postmeta in function ozhands_add_data_to_new_api_products
	// (in this moment Woocommerce functions not active). 
	update_post_meta( $product_id, 'ozh_new_retailer_product', json_encode( $product_data) );
	
	// SKU
	update_post_meta( $product_id, '_sku', $product_data->sku );
	
	// Product successfully added Notice
	$action_data['notice_type'] = 'success';
	$action_data['notice_text'] = 'Product SKU '.$product_data->sku.' successfully added to your Ozhands store. Your limit is '.$retailer_product_limit.', used '.ozh_get_retailer_product_number( $user_id );
	wp_send_json_success( $action_data );
}

/*
* Delete product from retailer list by Product SKU
*/
function ozh_delete_product( $user_id, $product_sku ) {
	
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
			$sku = get_post_meta( $product->ID, '_sku', true );
			if ( get_post_meta( $product->ID, '_sku', true ) == $product_sku ) {
				$product_id = $product->ID;
				break;
			}
		}
	}
	
	// Product delete
	if ( $product_id ) {
		dokan()->product->delete( $product_id, true );
		$action_data['notice_type'] = 'warning';
		$action_data['notice_text'] = 'Product '.$product_sku.' deleted from your Ozhands store. Your limit is '.ozh_get_retailer_product_limit( $user_id ).', used '.ozh_get_retailer_product_number( $user_id );
		wp_send_json_success( $action_data );
	}
	else {
		wp_send_json_error('Invalid SKU '.$product_sku);
	}
}

/*
* Get retailer products SKU list by site_ur
*/
function ozh_get_sku_list( $user_id ) {
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
	
	// Create responce
	$products_sku = array();
	if ( $products = $product_query->posts ) {
		foreach ( $products as $product ) {
			$sku = get_post_meta( $product->ID, '_sku', true );
			if ( $sku && $sku != '' ) {
				$products_sku[] = $sku;
			}
		}
	}
	wp_send_json_success( $products_sku );
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
function ozh_is_product_in_list( $user_id, $product_sku ) {
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
			if ( $product_sku == get_post_meta( $product->ID, '_sku', true ) ) {
				return TRUE;
			}
		}
	}
	return FALSE;
}

/**
 * @param integer $user_id
 * @param integer $package_id (Retailer Package)
 * @return mixed:
 * 				integer (quantity products active subcription)
 * 				string "unlimited" if quantity unlimited
 */
function ozh_get_retailer_product_limit( $user_id, $package_id = 0 ) {
	
    $product_limit = 0;
    $customer_orders = get_posts(array(
        'numberposts' => -1,
        'meta_key' => '_customer_user',
        'meta_value' => $user_id,
        'orderby' => 'date',
        'order' => 'DESC',        
        'post_type' => 'shop_order',
        'post_status' => 'wc-processing'
    ));
    $order_array = [];

    foreach ($customer_orders as $customer_order) {
        $orderq = wc_get_order($customer_order);
        $order_array[] = [
            'ID' => $orderq->get_id(),
            'Date' => $orderq->get_date_paid()->date_i18n('d-m-Y'),          
        ];
    }
    if ( $order_array ) {

        foreach ( $order_array as $order ) {
            $start = explode('-', $order['Date']);
            $finish = array();
            $this_month = new DateTime('last day of this month');
            $this_month = explode( '-', $this_month->format('d-m-Y') );

            $next_month = new DateTime('last day of next month');
            $next_month = explode( '-', $next_month->format('d-m-Y') );

            $finish[0] = $start[0];
            $finish[1] = $next_month[1];
            $finish[2] = $next_month[2];

            if ( $start[0] == $this_month[0] ) {
                $finish[0] = $next_month[0];
            }
            $today = strtotime( date('d-m-Y') );
            $finish_date = strtotime( implode( '-', $finish) );

            if ( $today < $finish_date ) {
                if ( $package_id ) {
                    if ( get_post_meta( $order['ID'], 'package_id', true ) == $package_id ) {
                        $product_limit = get_post_meta( $order['ID'], 'package_product_limit', true );
                        $product_limit = $product_limit == 'unlimited' ? 'unlimited' : (int)$product_limit;
                        return $product_limit;
                    }  
                }
                elseif ( get_post_meta( $order['ID'], 'package_product_limit', true ) == 'unlimited' ) {
                	$product_limit = 'unlimited';
                	return $product_limit;
                }
                else {
                	$product_limit += (int)get_post_meta( $order['ID'], 'package_product_limit', true );
                }      
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
 * retailer ban (do not display products on the site)
 */
add_action( 'posts_where', 'block_retailer_posts', 10, 2 );
function block_retailer_posts( $where, \WP_Query $query  ) {

	if( !is_admin() || !$query->is_main_query() ) {		

		global $wpdb;
	    $users = get_users( ['role' => 'retailer'] );

	    foreach ($users as $user ) {
	        $author_id = $user->data->ID;
	        $product_limit =  ozh_get_retailer_product_limit($author_id);
	        if ( get_user_meta( $author_id, 'ozh_retailer_store_block', true ) === 'block'  || $product_limit == 0 ) {

	            $post_id = $wpdb->get_col( "SELECT ID FROM ".$wpdb->prefix."posts WHERE post_author = $author_id AND post_status = 'publish' AND post_type = 'product'");
	            $post_ids[] =  $post_id;
	        }      
	    }
	    if ( is_array($post_ids) ) {
	    	if ( $post_ids ) {
				$str = '';

			    foreach ($post_ids as $post_id) {
			        $post_id = implode(',', $post_id);
			        $str .= $post_id.',';
			    }
			    $pattern = "/^[^0-9]*/";
			    // delete "," in start of string
	            $str = preg_replace($pattern, "", $str);
	        	$pattern = "/.*(\d)/";
	        	$pos = preg_match($pattern, $str, $matches, PREG_OFFSET_CAPTURE);
	        	if ($pos === 1) {
	        		// string without "," in finish 
	        		$str = $matches[0][0];
		        	if ( $str !== '' ) {
		            	$where .= " AND ".$wpdb->prefix."posts.ID NOT IN ($str)";
		            }
	        	}
	    	}
	    }
	}
    return $where;
    
/**
 * just test for GIT-settings
 */
function for_git_test() {
	
}