<?php
/*
* Ozhands Connection plugin
* requests to https://www.ozhands.com.au/ REST API functions
*/

/*
* Add product to Ozhands list by product ID
*/
function ozh_add_product_to_ozhands( $product_id ) {
	global $ozhands_site;
	
	$product = wc_get_product( $product_id )->get_data();

	$gallery_image_urls = array();
	if ( $product['gallery_image_ids'] ) {
		foreach ( $product['gallery_image_ids'] as $gallery_image_id ) {
			$gallery_image_urls[] = wp_get_attachment_image_url( $gallery_image_id  );
		}
	}
	
	$tag_names = array();
	if ( $product['tag_ids'] ) {
		foreach ( $product['tag_ids'] as $tag_id ) {
			$tag_names[] = get_term_by( 'id', $tag_id, 'product_tag' )->name;
		}
	}
	
	$category_names = array();
	if ( $product['category_ids'] ) {
		foreach ( $product['category_ids'] as $category_id ) {
			$category_names[] = get_term_by( 'id', $category_id, 'product_cat' )->name;
		}
	}
	
	$image_src_srcset[0] = wp_get_attachment_image_url( $product['image_id'], 'full' );
	$image_src_srcset[1] = '';
	if ( wp_get_attachment_image_srcset( $product['image_id'], 'full' ) ) {
		$image_src_srcset[1] = wp_get_attachment_image_srcset( $product['image_id'], 'full' );
	}
	
	$product_data = [
        'name'              	=> $product['name'],
		'sku'					=> $product['sku'],
        'description'       	=> $product['description'],
        'short_description' 	=> $product['short_description'],
		'regular_price'     	=> $product['regular_price'],
		'sale_price'			=> $product['sale_price'],
		'stock_status'			=> $product['stock_status'],
		'stock_quantity'		=> $product['stock_quantity'],
		'image_src'				=> wp_get_attachment_image_url( $product['image_id'], 'full' ),
		'image_srcset'			=> wp_get_attachment_image_srcset( $product['image_id'], 'full' ),
		'gallery_image_urls'	=> $gallery_image_urls,
		'tag_names'				=> $tag_names,
		'category_names'		=> $category_names,
		'single_product_url'	=> site_url().'/product/'.$product['slug'],
		'retailer_product_id'	=> $product_id
    ];

	$url = $ozhands_site.'/wp-json/dokan/add_product/v1';

	return ozh_get_contents( $url, base64_encode(json_encode( $product_data ) ) );
}

/*
* Update product to Ozhands list by product ID
*/
function ozh_update_product_in_ozhands( $product_id ) {
	global $ozhands_site;
	
	$product = wc_get_product( $product_id )->get_data();

	$gallery_image_urls = array();
	if ( $product['gallery_image_ids'] ) {
		foreach ( $product['gallery_image_ids'] as $gallery_image_id ) {
			$gallery_image_urls[] = wp_get_attachment_image_url( $gallery_image_id  );
		}
	}
	
	$tag_names = array();
	if ( $product['tag_ids'] ) {
		foreach ( $product['tag_ids'] as $tag_id ) {
			$tag_names[] = get_term_by( 'id', $tag_id, 'product_tag' )->name;
		}
	}
	
	$category_names = array();
	if ( $product['category_ids'] ) {
		foreach ( $product['category_ids'] as $category_id ) {
			$category_names[] = get_term_by( 'id', $category_id, 'product_cat' )->name;
		}
	}
	
	$image_src_srcset[0] = wp_get_attachment_image_url( $product['image_id'], 'full' );
	$image_src_srcset[1] = '';
	if ( wp_get_attachment_image_srcset( $product['image_id'], 'full' ) ) {
		$image_src_srcset[1] = wp_get_attachment_image_srcset( $product['image_id'], 'full' );
	}
	
	$product_data = [
		'retailer_product_id'	=> $product_id,
		'post_title'           	=> $product['name'],
		'post_content'         	=> $product['description'],
		'post_expert'         	=> $product['short_description'],
		'post_name'				=> $product['slug'],
		'guid'					=> $product['name'],
		'regular_price'     	=> $product['regular_price'],
		'sale_price'			=> $product['sale_price'],
		'price'					=> $product['price'],
		'sku'					=> $product['sku'],
		'image_src'				=> wp_get_attachment_image_url( $product['image_id'], 'full' ),
		'image_srcset'			=> wp_get_attachment_image_srcset( $product['image_id'], 'full' ),
		'gallery_image_urls'	=> $gallery_image_urls,
		'tag_names'				=> $tag_names,
		'category_names'		=> $category_names,
		'single_product_url'	=> site_url().'/product/'.$product['slug'],
    ];

	$url = $ozhands_site.'/wp-json/dokan/update_product/v1';

	return ozh_get_contents( $url, base64_encode(json_encode( $product_data ) ) );
}


/*
* Delete product from Ozhands list by product ID
*/
function ozh_delete_product_from_ozhands( $product_id ) {
	global $ozhands_site;
	
	$url = $ozhands_site.'/wp-json/dokan/delete_product/'.base64_encode( $product_id );
	return ozh_get_contents( $url );
}

/*
* Get Ozhands Products SKU list
*/
function ozh_get_ozhands_products_list( ) {
	global $ozhands_site;
	
	$url = $ozhands_site.'/wp-json/dokan/get_products_list/v1';
	return ozh_get_contents( $url );
}

/*
* Get data from 
*/
function ozh_get_contents( $url, $post_data = '' ){
	global $ozhands_token;

	$site_url = explode( '://', site_url() );
	$header = array();
	$header[] = 'Authorization: '.$ozhands_token.'@'.str_ireplace( 'www.', '', $site_url[1]);

	$curl = curl_init();
	
	curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.186 Safari/537.36');
	
	if ( $post_data != '' ) {
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, array( "product_data" => $post_data ) );
	}
	curl_setopt($curl, CURLOPT_HTTPHEADER , $header);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($curl, CURLOPT_URL, $url);
	$data = curl_exec($curl);
	
	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	if( $status !== 200 ){
		$data = new stdClass();
		$data->success = FALSE;
	}
	else {
		$data = json_decode( wp_unslash( $data ) );
	}
	
	curl_close($curl);

	return $data;
}