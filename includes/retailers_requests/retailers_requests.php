<?php
/*
* Ozhands Retailers plugin
* requests to retailers sites
*/

/*
* Get Product data from Retailer site
*/
function ozh_get_retailer_product( $site_url, $product_id ) {
	
	$url = $site_url.'wp-json/get_data/ozhands/product_data/'.$product_id;
	return ozh_get_contents( $url );
}

/*
* Get Products data from Retailer site by Products IDs Array
*/
function ozh_get_retailer_products( $site_url, $products_ids ) {
	
	$ids = '';
	foreach ( $products_ids as $product_id ) {
		$ids .= $product_id.'_';
	}
	$url = $site_url.'wp-json/get_data/ozhands/products_data/'.substr( $ids, 0, -1 );
	return ozh_get_contents( $url );
}

/*
* Get Products categories from Retailer site
*/
function ozh_get_retailer_categories( $site_url ) {
	
	$url = $site_url.'wp-json/get_data/ozhands/product_categories/0';
	return ozh_get_contents( $url );
}

function ozh_get_contents( $url ){

	$header = array();
	$header[] = 'Authorization: '.( strtotime('yesterday') * 11 +23 );

	$curl = curl_init();
	curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.186 Safari/537.36');
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