<?php

/**
 * Display of retailer packages on page "Retailer Packages"
 */
add_shortcode('retailer_packages', 'show_retailer_packages');
function show_retailer_packages() {
	if ( is_user_logged_in() ) {
		$cuser = wp_get_current_user();
		if ( $cuser->roles[0] == 'retailer' || $cuser->roles[0] == 'administrator' ) {
			wp_enqueue_style('ozh-retailer-packages', home_url().'/wp-content/plugins/ozhands_retailers/css/retailer-packages.css');
			wp_enqueue_script('package-purchase', home_url().'/wp-content/plugins/ozhands_retailers/js/package-purchase.js', array('jquery'), '', true );

			$query = new WP_Query( array(
				'post_type' => 'package',
				'orderby' => 'meta_value',
				'meta_key' => 'price',
				'order'    => 'ASC',
				'posts_per_page' => '5'
			) );

			$attr = array(
				'class' => "attachment-woocommerce_thumbnail size-woocommerce_thumbnail",
				'alt'   => "",
			);
			$attach_id = get_option( 'ozh_clear_image_id' );
			$src = wp_get_attachment_image_src( $attach_id);

			while( $query->have_posts() ):
				$query->the_post(); ?>
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
						echo '<img src="'.$src[0].'" />';
					}
					?>
					</div><!-- .post-thumbnail -->

					<div class="ozh-packages-content">
						<? echo get_the_content(); ?>				
					</div><!-- .custom-entry-content -->

					<div class="retailer-price">
						<span class="retailer-price-amount">
							<?php 
							if ( !get_post_meta( get_the_ID(), 'trial', true ) ) {
								echo get_post_meta( get_the_ID(), 'price', true ) .'$'; 
							} ?>							
						</span>						
						<a href="?checkout" rel="nofollow" data-package-id="<?= get_the_ID(); ?>" class="link-buy-package">
							<?php 
							if ( ozh_get_retailer_product_limit( $cuser->ID , get_the_ID() ) !== 0 ) {
								echo '<span class="buy-package">Renew Package</span>'; 
							}
							elseif ( get_post_meta( get_the_ID(), 'trial', true ) ) {
								echo '<span class="buy-package">Start Free Trial</span>';
							}
							else {
								echo '<span class="buy-package">Buy</span>';
							} ?>							
						</a>
					</div>
				</div>
		 
			<?php endwhile; ?>
			
			<div class="pagination-container">
				<?php
				echo paginate_links( array(
					'prev_next' => true,
					'prev_text' => __( '&laquo; Previous' ),
					'next_text' => __( 'Next &raquo;' ),
					'total' => $query->max_num_pages,
					'current' => $paged,
				) ); ?>
			</div>
			
			<?php wp_reset_postdata();
		}
	}	
}

/**
 * set session-data for Woocommerce cart & add product ID to cart
 */
add_action('wp_ajax_'.'buy_retailer_package', 'buy_retailer_package');
add_action('wp_ajax_'.'buy_retailer_package', 'buy_retailer_package');
function buy_retailer_package() {
	$package_id = $_POST['data'];
	$product_name = get_the_title( $package_id );
	$price = get_post_meta( $package_id, 'price', true );
	WC()->session->cleanup_sessions();
	WC()->session->set('package_id', $package_id);
	WC()->session->set('package_price', $price);
	WC()->session->set('package_name', $product_name);
	WC()->cart->empty_cart();
	$product_id = wc_get_product_id_by_sku( 'ta-15488ks' );
	WC()->cart->add_to_cart($product_id, 1);

	echo 'checkout';
	die();
}

/**
 * get the price of retailer package
 */
add_action('woocommerce_get_price','retailer_get_price', 10, 2);
function retailer_get_price( $price, $product ){
	$current_user = wp_get_current_user();
	$product_id = wc_get_product_id_by_sku( 'ta-15488ks' );
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
add_action( 'woocommerce_before_calculate_totals', 'retailer_cart_items_prices', 10, 1 );
function retailer_cart_items_prices( $cart ) {

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
add_action('woocommerce_checkout_create_order', 'before_checkout_create_order', 20, 2);
function before_checkout_create_order( $order, $data ) {

	$package_id = WC()->session->get('package_id');
	$limit = get_post_meta( $package_id, 'quantity_of_products', true );
	$order->update_meta_data( 'package_id', $package_id );
	$order->update_meta_data( 'package_product_limit', $limit );
}

/**
 * adding to user meta package ID & date of start actions subscription (retailer package)
 */
// add_action( 'woocommerce_payment_complete', 'ozh_payment_complete' );
// function ozh_payment_complete( $order_id ){

// 	$order = wc_get_order( $order_id );
// 	$date =  date('Y-m-d H:i:s');
// 	$data = array(
// 		'start' => get_date_from_gmt($date, 'j n Y'),
// 		'order_id' => $order_id
// 	);
// 	add_user_meta( get_current_user_id(), 'retailer_package_dates', $data );
// }