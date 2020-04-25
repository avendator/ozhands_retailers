<?php
/*
* Ozhands Connection plugin
* admin content functions
*/

/*
* Update and Get Ozhands Products Ids after init page Product
*/
add_action( 'admin_init', 'ozh_product_edit_init' );
function ozh_product_edit_init() {
	
	global $ozhands_products_ids;
	global $ozhands_notice;
	
	if ( substr( $_SERVER["REQUEST_URI"], 0, 36 ) == '/wp-admin/edit.php?post_type=product' ) {

		if ( isset( $_GET['ozhands_action'] ) && isset( $_GET['product_id']) ) {
			if ( $_GET['ozhands_action'] == 'add' ) {
				$action_data = ozh_add_product_to_ozhands( (int)$_GET['product_id'] );
				
			}
			if ( $_GET['ozhands_action'] == 'delete' ) {
				$action_data = ozh_delete_product_from_ozhands( (int)$_GET['product_id'] );
			}
			if ( $action_data->success ) {
				$ozhands_notice = $action_data->data;
			}
			else {
				$ozhands_notice->notice_type = 'error';
				$ozhands_notice->notice_text = '<span style="color: red;">Error</span>';
			}
		}
		$action_data = ozh_get_ozhands_products_list( );
		if ( $action_data->success ) {
			if ( $action_data->data->notice_text != '' ) {
				$ozhands_products_ids = explode( ',', $action_data->data->notice_text );
				if ( !is_array( $ozhands_products_ids ) ) {
					$ozhands_products_ids[] = $action_data->data->notice_text;
					$notice_text = "Used 1 position";
				}
				else {
					$notice_text = "Used ".count( $ozhands_products_ids )." positions";
				}
			}
			else {
				$ozhands_products_ids[] = '';
				$notice_text = "";
			}
			if ( !$ozhands_notice ) {
				$ozhands_notice = $action_data->data;
				$ozhands_notice->notice_text = $notice_text;
			}
		}
		else {
			$ozhands_notice = $action_data->data;
		}
	}
}

/*
* Add Ozhands column to the Product table (/wp-admin/edit.php?post_type=product)
*/
add_filter( 'manage_product_posts_columns', 'ozh_posts_add_column' );
function ozh_posts_add_column($columns) {
	global $ozhands_name;
	
	$columns['in_ozhands'] = $ozhands_name;
	return $columns;
}

/*
* Display Ozhands marker in column in the Product table
*/
add_action( 'manage_product_posts_custom_column' , 'ozh_product_custom_column', 10, 2 );
function ozh_product_custom_column( $column, $post_id ) {
	if ($column == 'in_ozhands') {
		if ( ozh_is_product_in_ozhands( $post_id ) ) {
			// Marker symbol
			echo '&#9989;';
		}
    }
}

/*
* Ozhands add or Ozhands delete link to Product table column Name
*/
add_filter( 'post_row_actions', 'ozh_add_in_ozhands_action', 20, 2 );
function ozh_add_in_ozhands_action( $actions, $post ) {
	global $ozhands_name;
	
	if ( $post->post_type == "product" ) {
		if ( ozh_is_product_in_ozhands( $post->ID ) ) {
			$actions['ozhands_action'] = '<a href="/wp-admin/edit.php?post_type=product&ozhands_action=delete&product_id='.$post->ID.'"><span style="color: red;">'.$ozhands_name.' delete</span></a>';
		}
		else {
			$actions['ozhands_action'] = '<a href="/wp-admin/edit.php?post_type=product&ozhands_action=add&product_id='.$post->ID.' ">'.$ozhands_name.' add</a>';
		}
	}
	return $actions;
}

/*
* Check product Ozhands list by product ID
*/
function ozh_is_product_in_ozhands( $product_id ) {
	global $ozhands_products_ids;
	if ( !is_array( $ozhands_products_ids) ) {
		return FALSE;
	}
	return in_array( $product_id, $ozhands_products_ids);
}

/*
* Display admin notice from WP option ozh_admin_notice
*/
add_action( 'admin_notices', 'ozh_display_admin_notice' );
function ozh_display_admin_notice() {
    global $pagenow;
	global $ozhands_notice;
	global $ozhands_limit;
	global $ozhands_name;
	
    if ( $pagenow == 'edit.php' && $_GET['post_type'] == 'product' && $ozhands_notice ) {
		echo '
		<div class="notice notice-'.$ozhands_notice->notice_type.'">
			<div id="ozhands_limit" data-value="'.$ozhands_notice->limit.'" style="display: none"></div>
			<p style="font-size: 115%">'.str_replace( 'Ozhands', $ozhands_name, $ozhands_notice->notice_text.' '.$ozhands_notice->notice_user );
				if ( $ozhands_notice->limit ) {
					echo '<button id="add_all_ozhands_btn" class="button" style="color: red; margin-left: 30px;">Add all Products to '.$ozhands_name.'</button>
					<img id="loader_gif" style="display: none" src="'.plugins_url('ozhands_connection/img/ajax-loader.gif').'">';						
				}
			echo '</p>			
		</div>

		<script>						
			jQuery(function(){	
				let loader_gif = jQuery("#loader_gif")
				jQuery("#add_all_ozhands_btn").on("click", function() {
					let ozhands_limit = jQuery("#ozhands_limit").data("value")				
					let confirm_add = confirm("Limit of added products: " + ozhands_limit + "\nAdding products can take a long time.")
					if(confirm_add){
						jQuery(this).attr("disabled", true)
						loader_gif.show()
						jQuery.post("/wp-admin/admin-ajax.php", { action: "ozh_add_all_ozhands" }, function(response) {
							loader_gif.hide()
							alert("Products successfully updated!")
							window.location.assign("/wp-admin/edit.php?post_type=product")
						})
					}
				})
			})	
		</script>';			
    }
}

/**
 * Add all products to the site
 */
add_action('wp_ajax_ozh_add_all_ozhands', 'ozh_add_all_ozhands');
add_action('wp_ajax_nopriv_ozh_add_all_ozhands', 'ozh_add_all_ozhands');
function ozh_add_all_ozhands() {
	$args = array(
		'status' => 'publish',
		'limit' => -1,
		'return' => 'ids',
	);
	$products = wc_get_products( $args );

	foreach( $products as $product ){
		ozh_add_product_to_ozhands( $product );
	}
	die();
}

/**
 * Delete Retailer Product ID from Ozhands list before Product delete
 */
add_action( 'wp_trash_post', 'ozh_dete_original_id' );
add_action( 'before_delete_post', 'ozh_dete_original_id' );
function ozh_dete_original_id( $post_id ) {
	delete_post_meta( $post_id, '_original_id' );
}

/**
 * Update Retailer Product data in Ozhands after Product update
 */
add_action( 'woocommerce_update_product', 'ozh_product_update_after_edit' );
function ozh_product_update_after_edit( $post_id ) {
	global $ozhands_notice;
	
	if ( get_post_type( $post_id ) == 'product' ) {
		$action_data = ozh_update_product_in_ozhands( $post_id );
		if ( $action_data->success ) {
			$ozhands_notice = $action_data->data;
		}
	}
}
