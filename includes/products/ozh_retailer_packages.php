<?php

/**
 * Display of retailer packages on page "Retailer Packages"
 */
add_shortcode('retailer_packages', 'ozh_show_retailer_packages');
function ozh_show_retailer_packages() {
	if ( is_user_logged_in() ) {
		$cuser = wp_get_current_user();
		if ( $cuser->roles[0] == 'retailer' || $cuser->roles[0] == 'administrator' ) {
			wp_enqueue_script('package-purchase', home_url().'/wp-content/plugins/ozhands_retailers/js/package-purchase.js', array('jquery'), '', true );
			// if the retailer already bought the package before 
			$packages_data = ozh_get_retailer_packages_data( $cuser->ID );
			$trial = ozh_get_trial_date( $cuser->ID );
			if ( $packages_data || $trial ) {
				$meta = array(
					array(
						'key'     => 'trial',
						'value'   => '0'
					)					
				);
				// if hie did not yet ...
			} else {
				$meta = array(
					array(
						'key'     => 'price',
					)					
				);
			}
			$args = array(
				'post_type' => 'package',
				'orderby'   => 'meta_value',
				'order'     => 'ASC',
				'meta_query' => $meta			
			);					
			$query = new WP_Query( $args );

			$attr = array(
				'class' => "attachment-woocommerce_thumbnail size-woocommerce_thumbnail",
				'alt'   => "",
			);
			$attach_id = get_option( 'ozh_clear_image_id' );
			$src = wp_get_attachment_image_src( $attach_id);

			while( $query->have_posts() ): ?>
				<?php $query->the_post();
				$trial = get_post_meta( get_the_ID(), 'trial', true );
				?>
				<div class="retailer-package-container">
					<?php
					the_title( '<h2 class="package-title">', '</h2>' );
					?>
					<div class="ozh-post-thumbnail">
					<?php 
					if( has_post_thumbnail() ) {
						the_post_thumbnail( 'thumbnail', $attr );
					}
					else {
						echo '<img src="'.plugins_url().'/ozhands_retailers/img/list.png" />';
					}
					?>
					</div><!-- .post-thumbnail -->

					<div class="ozh-packages-content">
						<?php echo get_the_content();
							echo '<span class="trial">Free: '.$trial.' Days</span>';
						?>				
					</div><!-- .custom-entry-content -->

					<div class="retailer-price">
						<span class="retailer-price-amount">
							<?php echo get_post_meta( get_the_ID(), 'price', true ) .'$'; ?>							
						</span>
						<?php
						// if the retailer already bought the package before 
						if ( ozh_get_retailer_product_limit( $cuser->ID , get_the_ID() ) !== 0 ) { ?>
							<a href="?checkout" rel="nofollow" data-renewal="renewal" data-package-id="<?= get_the_ID(); ?>" class="link-buy-package">
								<span class="renewal">Renewal</span>
							</a>
						<?php } else {	
							if ( $packages_data ) { ?>
								<a href="?checkout" rel="nofollow" data-package-id="<?= get_the_ID(); ?>" class="link-buy-package">
									<span class="buy-package">Buy</span>
								<?php 
							} // if hie did not yet ...							 
							else {
								if ( $trial ) { ?>
									<a href="?checkout" rel="nofollow" data-trial="<?= $trial; ?>" data-package-id="<?= get_the_ID(); ?>" class="link-buy-package">
										<span class="buy-free">Start Free</span>
								<?php } else { ?>
									<a href="?checkout" rel="nofollow" data-package-id="<?= get_the_ID(); ?>" class="link-buy-package">
										<span class="buy-package">Buy</span>
								<?php }
							}
						} ?>
						</a>
					</div>
				</div>
		 
			<?php endwhile; ?>
			
			<?php wp_reset_postdata();
		}
	}	
}

/**
 * set session-data for Woocommerce cart & add product ID to cart
 */
add_action('wp_ajax_'.'buy_retailer_package', 'ozh_buy_retailer_package');
add_action('wp_ajax_'.'buy_retailer_package', 'ozh_buy_retailer_package');
function ozh_buy_retailer_package() {
	$package_id = $_POST['data']['package_id'];
	$renewal = $_POST['data']['renewal'];
	$trial = $_POST['data']['trial'];
	$product_name = get_the_title( $package_id );
	$user = get_current_user_id();
	if ( $trial ) {
		$start = strtotime( wp_date('d-m-Y') );
		$trial = $trial * 86400;
		$finish_date = $start + $trial;
		$limit = get_post_meta( $package_id, 'quantity_of_products', true );
	    update_user_meta( $user, 'ozh_trial_start_date', $start );
	    update_user_meta( $user, 'ozh_trial_finish_date', $finish_date );
	    update_user_meta( $user, 'ozh_package_id', $package_id );
	    update_user_meta( $user, 'ozh_package_product_limit', $limit );
		echo 'thank-you-page';
		die();
	}	
	else {
		$price = get_post_meta( $package_id, 'price', true );
	}
	WC()->session->cleanup_sessions();
	WC()->session->set('package_price', $price);
	WC()->session->set('package_id', $package_id);
	WC()->session->set('package_name', $product_name);
	WC()->session->set('package_renewal', $renewal);
	WC()->session->set('package_trial', $trial);
	WC()->cart->empty_cart();
	$product_id = get_option('ozh_retailer_subscription');
	WC()->cart->add_to_cart($product_id, 1);

	echo 'checkout';
	die();
}

/**
 * get the price of retailer package
 */
add_action('woocommerce_get_price','ozh_retailer_get_price', 10, 2);
function ozh_retailer_get_price( $price, $product ) {

	$current_user = wp_get_current_user();
	$product_id = get_option('ozh_retailer_subscription');
	if ( $current_user->roles[0] == 'retailer' && $product->id == $product_id && WC()->session->get('package_price') ) {
		return WC()->session->get('package_price');
	}
	else {
		return $price;
	}
}

/**
 * change the name of retailer package in the cart
 */
add_action( 'woocommerce_before_calculate_totals', 'ozh_retailer_cart_items_prices', 10, 1 );
function ozh_retailer_cart_items_prices( $cart ) {

    if ( is_admin() && ! defined( 'DOING_AJAX' ) )
        return;
    if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 )
        return;

    // Loop through cart items
    foreach ( $cart->get_cart() as $cart_item ) {
        // Get an instance of the WC_Product object
        $product = $cart_item['data'];
        // Get the product name (Added Woocommerce 3+ compatibility)
        $original_name = method_exists( $product, 'get_name' ) ? $product->get_name() : $product->post->post_title;
        // Set the new name
        $new_name = WC()->session->get('package_name');
        // Set the new name (WooCommerce versions 2.5.x to 3+)
        if( method_exists( $product, 'set_name' ) )
            $product->set_name( $new_name );
        else
            $product->post->post_title = $new_name;
    }
}

/**
 * adding package ID & subscription limit (retailer package) to order meta-data
 */
add_action('woocommerce_checkout_create_order', 'ozh_before_checkout_create_order', 20, 2);
function ozh_before_checkout_create_order( $order, $data ) {

	$package_id = WC()->session->get('package_id');
	$limit = get_post_meta( $package_id, 'quantity_of_products', true );
	$order->update_meta_data( 'package_id', $package_id );
	$order->update_meta_data( 'package_product_limit', $limit );
}

/**
 * adding to user meta "start_date / finish_date" and "package_renewal" if it is a renewal of package
 */
add_action( 'woocommerce_payment_complete', 'ozh_payment_complete' );
function ozh_payment_complete( $order_id ) {

	$user = get_current_user_id();
	// if the retailer already bought the trial package before
	$trial = ozh_get_trial_date( $user );

	$start = wp_date('d-m-Y');
    $start = explode('-', $start);
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
    // start date in milliseconds (the paid-day)
    $start = strtotime( implode( '-', $start) );
    $finish = strtotime( implode( '-', $finish) );
    // one month active of subscription in milliseconds
    $finish = $finish - $start;
    // end date in milliseconds
    $finish_date = $finish + $start;
    // all orders id's by user id
    $order_array = ozh_get_orders_ids( $user );

	if ( $order_array ) {
	    $finish_dates = [];
	    $renewal = WC()->session->get('package_renewal');

		if ( $renewal ) {
			$package_id = WC()->session->get('package_id');

	        foreach ( $order_array as $order ) {

				if ( get_post_meta( $order['ID'], 'package_id', true ) == $package_id ) {
		        	$finish_dates[] = [
		        		'finish_date' => get_post_meta( $order['ID'], 'finish_date', true )
	        		];				
				}
	    	}
		    $finish_date = $finish_dates[count($finish_dates) -2];
		    // the last date action of previos subscription + one month of the next action
		    $finish_date = $finish_date['finish_date'] + $finish;

			update_post_meta( $order_id, 'package_renewal', $renewal );		
		}
		else {
	        foreach ( $order_array as $order ) {
	        	// if this not first purchase of package
				if ( get_post_meta( $order['ID'], 'package_id', true ) ) {
		        	$finish_dates[] = [
		        		'finish_date' => get_post_meta( $order['ID'], 'finish_date', true )
	        		];				
				} // if it's first...
				else {
					$finish_date = $start + $finish;
				}
			}
		} // if is array not empty
		if ( !$finish_date ) {
			$finish_date = $finish_dates[count($finish_dates) -2];
	        if ( $start < $finish_date['finish_date'] ) {
	            $finish_date = ( $finish_date['finish_date'] - $start ) + $start + $finish;
	        } // if it's first purchase of package
	        else {
	        	$finish_date = $start + $finish;
	        }			
		}
	}
    update_post_meta( $order_id, 'start_date', $start );
    update_post_meta( $order_id, 'finish_date', $finish_date + $trial );
}
