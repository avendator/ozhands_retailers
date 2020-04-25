<?php
/**
 * @package Import CSV
 * @version 1.7.1
 */
/*
Plugin Name: Import SCV
*/

add_shortcode( 'csv_import', 'csv_import' );
function csv_import() {
	
	if( substr( $_SERVER['REQUEST_URI'], 0, 12 ) == '/csv-import/' ) {
		
		include_once $_SERVER['DOCUMENT_ROOT'] . '/wp-admin/includes/file.php';
		include_once $_SERVER['DOCUMENT_ROOT'] . '/wp-content/plugins/woocommerce/includes/import/class-wc-product-csv-importer.php';
		// include_once dirname( dirname( __FILE__ ) ) . '/csv_import/import/class-wc-product-csv-importer.php';
		include_once $_SERVER['DOCUMENT_ROOT']. '/wp-content/plugins/woocommerce/includes/admin/importers/class-wc-product-csv-importer-controller.php';
		
		wp_register_script('wc-product-import', home_url().'/wp-content/plugins/ozhands_retailers/includes/csv_import/ozh-wc-product-import.js', array(), null, true);

		// If second step
		if ( $_GET['step'] == 'mapping' && $_GET['file'] ) {
			$user_id = get_current_user_id();
			if ( $user_id ) {
				// Get retailer products number from CSV file
				$file = wc_clean( wp_unslash( $_GET['file'] ) );
				$row = 0;
				$file_name = explode( '/', $_GET['file'] );
				if (($handle = fopen( wc_clean( wp_unslash( $_GET['file'] ) ) , "r")) !== FALSE) {
					$file_name_temp = str_replace( '.csv', '_temp.csv', $_GET['file'] );
					$file_temp = fopen( $file_name_temp, 'w' );
					while ( ( $row_data = fgetcsv( $handle, 1000, "," ) ) !== FALSE ) {
						if ( $row && (int)$row_data[0] ) {
							$row_data[0] = ozh_create_original_id( $user_id, (int)$row_data[0] );
							$csv_ids[] = $row_data[0];
						}
						fputcsv( $file_temp, $row_data);
						$row++;
					}
					fclose($file_temp);
				}
				else {
					echo "<h2 class='entry-header'>".$file_name[count($file_name) - 1]." File not open</h2>";
				}
				fclose( $handle );
				unlink( $file );
				rename( $file_name_temp, $file );
				
				$retailer_product_csv = $row - 1;
				
				// Get retailer products limit from subscription
				$retailer_product_limit = ozh_get_retailer_product_limit( $user_id );
				
				// Get number of retailer products on site
				$retailer_product_number  = ozh_get_retailer_product_number( $user_id );
				
				// Retailer has no right to import this CSV
				if ( $retailer_product_number > $retailer_product_limit ) {
					echo "<h2 class='entry-header'>The number of already downloaded products - ".$retailer_product_number." exceeds Your limit - ".$retailer_product_limit."</h2>";
					return;
				}
				if ( $retailer_product_csv > $retailer_product_limit ) {
					echo "<h2 class='entry-header'>The number of products in file ".$file_name[count($file_name) - 1]." - ".$retailer_product_csv." exceeds Your limit - ".$retailer_product_limit."</h2>";
					return;
				}
				if ( $retailer_product_number + count( $csv_ids ) > $retailer_product_limit ) {
					$sum_product_number = $retailer_product_number;
					foreach ( $csv_ids as $original_id ) {
						if ( !ozh_is_product_in_list( $user_id, $original_id ) ) {
							$sum_product_number++;
						}
					}
					if ( $sum_product_number > $retailer_product_limit ) {
						$new_csv_products = $sum_product_number - $retailer_product_number;
						echo "<h2 class='entry-header'>The number of already downloaded products ".$retailer_product_number." plus number of new products in CSV file ".$file_name[count($file_name) - 1]." - ".$new_csv_products." exceeds Your limit - ".$retailer_product_limit."</h2>";
					return;
					}
				}
			}
		}
		
		$importer = new WC_Product_CSV_Importer_Controller();
		$importer->dispatch();

	}
}

// Redirect intercept by wp_redirect() so that there is no duplicate header error on the front end
add_filter( 'wp_redirect', 'ozh_csv_redirect' );
function ozh_csv_redirect( $location ) {
	$location_url = explode ( '/', $location );
	if ( $location_url[1] == 'csv-import' ) {
		$user_meta = get_userdata( get_current_user_id() );
		if ( $user_meta->roles[0] == 'retailer' ) {
			echo "<script>document.location.href = '".site_url().$location."'</script>";
		}
	}
	return $location;
}

// Adapted analog of Woocommerce function WC_Admin_Importers::do_ajax_product_import()
add_action( 'wp_ajax_nopriv_ozh_do_ajax_product_import', 'ozh_do_ajax_product_import' );
add_action( 'wp_ajax_ozh_do_ajax_product_import', 'ozh_do_ajax_product_import' );
function ozh_do_ajax_product_import() {
	global $wpdb;
	global $csv_original_ids;

	check_ajax_referer( 'wc-product-import', 'security' );

	if ( ! isset( $_POST['file'] ) ) { // PHPCS: input var ok.
		wp_send_json_error( array( 'message' => __( 'Insufficient privileges to import products.', 'woocommerce' ) ) );
	}

	include_once $_SERVER['DOCUMENT_ROOT'] . '/wp-content/plugins/woocommerce/includes/admin/importers/class-wc-product-csv-importer-controller.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/wp-content/plugins/woocommerce/includes/import/class-wc-product-csv-importer.php';

	$file   = wc_clean( wp_unslash( $_POST['file'] ) ); // PHPCS: input var ok.
	$params = array(
		'delimiter'       => ! empty( $_POST['delimiter'] ) ? wc_clean( wp_unslash( $_POST['delimiter'] ) ) : ',', // PHPCS: input var ok.
		'start_pos'       => isset( $_POST['position'] ) ? absint( $_POST['position'] ) : 0, // PHPCS: input var ok.
		'mapping'         => isset( $_POST['mapping'] ) ? (array) wc_clean( wp_unslash( $_POST['mapping'] ) ) : array(), // PHPCS: input var ok.
		'update_existing' => isset( $_POST['update_existing'] ) ? (bool) $_POST['update_existing'] : false, // PHPCS: input var ok.
		'lines'           => apply_filters( 'woocommerce_product_import_batch_size', 30 ),
		'parse'           => true,
	);

	// Log failures.
	if ( 0 !== $params['start_pos'] ) {
		$error_log = array_filter( (array) get_user_option( 'product_import_error_log' ) );
	} else {
		$error_log = array();
	}

	$importer         = WC_Product_CSV_Importer_Controller::get_importer( $file, $params );
	$results          = $importer->import();
	$percent_complete = $importer->get_percent_complete();
	$error_log        = array_merge( $error_log, $results['failed'], $results['skipped'] );

	update_user_option( get_current_user_id(), 'product_import_error_log', $error_log );

	if ( 100 === $percent_complete ) {
		// @codingStandardsIgnoreStart.
		$wpdb->delete( $wpdb->postmeta, array( 'meta_key' => '_original_id' ) );
		$wpdb->delete( $wpdb->posts, array(
			'post_type'   => 'product',
			'post_status' => 'importing',
		) );
		$wpdb->delete( $wpdb->posts, array(
			'post_type'   => 'product_variation',
			'post_status' => 'importing',
		) );
		// @codingStandardsIgnoreEnd.

		// Clean up orphaned data.
		$wpdb->query(
			"
			DELETE {$wpdb->posts}.* FROM {$wpdb->posts}
			LEFT JOIN {$wpdb->posts} wp ON wp.ID = {$wpdb->posts}.post_parent
			WHERE wp.ID IS NULL AND {$wpdb->posts}.post_type = 'product_variation'
		"
		);
		$wpdb->query(
			"
			DELETE {$wpdb->postmeta}.* FROM {$wpdb->postmeta}
			LEFT JOIN {$wpdb->posts} wp ON wp.ID = {$wpdb->postmeta}.post_id
			WHERE wp.ID IS NULL
		"
		);
		// @codingStandardsIgnoreStart.
		$wpdb->query( "
			DELETE tr.* FROM {$wpdb->term_relationships} tr
			LEFT JOIN {$wpdb->posts} wp ON wp.ID = tr.object_id
			LEFT JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
			WHERE wp.ID IS NULL
			AND tt.taxonomy IN ( '" . implode( "','", array_map( 'esc_sql', get_object_taxonomies( 'product' ) ) ) . "' )
		" );
		// @codingStandardsIgnoreEnd.
	}
	
	// Add Original Retailer Ids to New Products postmeta
	foreach ( $csv_original_ids as $product_id => $original_id ) {
		update_post_meta( (int)$product_id, '_original_id', (int)$original_id );
	}
	
	wp_send_json_success(
		array(
			'position'   => $importer->get_file_position(),
			'percentage' => $percent_complete,
			'url'        => site_url().'/dashboard/',
			'imported'   => count( $results['imported'] ),
			'failed'     => count( $results['failed'] ),
			'updated'    => count( $results['updated'] ),
			'skipped'    => count( $results['skipped'] ),
		)
	);
}


// Create Array of Products IDs and Original Retailer Products IDs (3 hooks)
add_action( 'woocommerce_product_import_before_import', 'ozh_product_import_before_import' );
function ozh_product_import_before_import( $data ) {
	global $csv_options;
	global $csv_original_ids;
	
	$k = $csv_options['k'];
	
	$product_id = (int)$data['id'];
	$csv_original_ids[$product_id] = $csv_options[ $csv_options['k'] ]['_original_id'];

	$csv_options['k']++;
	
	update_option( 'test_csv_original_ids_'.random_int(1, 1000), $csv_original_ids );
}

add_action( 'woocommerce_product_importer_before_set_parsed_data', 'ozh_product_importer_before_set_parsed_data', 10, 2 );
function ozh_product_importer_before_set_parsed_data( $row, $mapped_keys ) {
	global $csv_options;
	
	for ( $i = 0; $i < count( $mapped_keys ); $i++ ) {
		if ( $mapped_keys[$i] == 'id' ) {
			$csv_options[]['_original_id'] = $row[$i];
			break;
		}
	}
}

add_filter( 'woocommerce_product_importer_formatting_callbacks', 'ozh_product_importer_formatting_callbacks', 10, 2 );
function ozh_product_importer_formatting_callbacks( $callbacks, $importer ) {
	global $wpdb;
	global $csv_options;
	global $csv_original_ids;
	
	$csv_options = array();
	$csv_original_ids = array();
	$csv_options['k'] = 0;
	
	$cur_user_id = get_current_user_id();
	
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
			$retailer_product_id = (int)get_post_meta( $product->ID, '_original_id', true );
			if ( $retailer_product_id ) {
				$csv_original_ids[$product->ID] = $retailer_product_id;
			}
		}
	}

	return $callbacks;
}



	
