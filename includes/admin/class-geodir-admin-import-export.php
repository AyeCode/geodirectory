<?php
/**
 * Handle import and exports.
 *
 * @author   AyeCode
 * @category Admin
 * @package  GeoDirectory/Admin
 * @version  2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * GeoDir_Admin_Import_Export Class.
 */
class GeoDir_Admin_Import_Export {

    /**
     * Start import export.
     *
     * @since 2.0.0
     *
     * @global object $wp_filesystem Wordpress file system object.
     *
     * @return bool|WP_Error
     */
	public static function start_import_export() {
		global $wp_filesystem;


		// Set doing import constant.
		if ( ! defined( 'GEODIR_DOING_IMPORT' ) ) {
			define( 'GEODIR_DOING_IMPORT', true );
		}

		//extra security check
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'gd-no-auth', __( "You don't have permission to do this.", "geodirectory" ) );
		}

		// add filter for dates
		add_filter('geodir_get_posts_count', array( __CLASS__ , 'filter_where_query' ), 10, 2);
		add_filter('geodir_get_export_posts', array( __CLASS__ , 'filter_where_query' ), 10, 2);
		add_filter('geodir_ajax_prepare_export_reviews', array( __CLASS__ , 'prepare_export_reviews' ));
		add_filter('geodir_ajax_export_reviews', array( __CLASS__ , 'export_reviews' ));

		// set the task
		//$task = isset( $_POST['task'] ) ? esc_attr( $_POST['task'] ) : '';
		$task = isset( $_REQUEST['task'] ) ? esc_attr( $_REQUEST['task'] ) : '';

		// If we dont have a task then bail
		if ( ! $task ) {
			return new WP_Error( 'gd-no-task', __( "No task is set", "geodirectory" ) );
		}

		// defer term counting
		wp_defer_term_counting( true );

		// set higher PHP limits
		self::set_php_limits();

		// check if we have access to the file system
		$wp_filesystem = geodir_init_filesystem();
		if ( ! empty( $wp_filesystem ) && isset( $wp_filesystem->errors ) && is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->get_error_code() ) {
			return new WP_Error( 'gd-no-filesystem', __( "Filesystem ERROR: " . $wp_filesystem->errors->get_error_message(), "geodirectory" ) );
		} elseif ( ! $wp_filesystem ) {
			return new WP_Error( 'gd-no-filesystem', __( "There was a problem accessing the filesystem.", "geodirectory" ) );
		}

		// create the cache directory if it does not already exist.
		$csv_file_dir = self::import_export_cache_path( false );
		if ( ! $wp_filesystem->is_dir( $csv_file_dir ) ) {
			if ( ! $wp_filesystem->mkdir( $csv_file_dir, FS_CHMOD_DIR ) ) {
				return new WP_Error( 'gd-no-filesystem', __( "ERROR: Could not create cache directory. This is usually due to inconsistent file permissions.", "geodirectory" ) );
			}
		}

		if ( $wp_filesystem->is_dir( $csv_file_dir ) && ! $wp_filesystem->exists( $csv_file_dir . '/index.php' ) ) {
			$wp_filesystem->copy( GEODIRECTORY_PLUGIN_DIR . 'assets/index.php', $csv_file_dir . '/index.php' );
		}

		switch ( $task ) {
			case "prepare_import":
				return self::validate_csv();// validate CSV
				break;
			case "import_post":
				return self::import_posts();
				break;
			case "export_posts":
				return self::export_posts();
				break;
			case "export_cats":
				return self::export_categories();
				break;
			case "import_cat":
				return self::import_categories();
				break;
			case "export_settings":
				self::export_settings();
				break;
			case "import_settings":
				return self::import_settings();
				break;
			case "prepare_export":
				if ( ! empty( $_POST['_export'] ) && has_filter( 'geodir_ajax_prepare_export_' . sanitize_key( $_POST['_export'] ) ) ) {
					return apply_filters( 'geodir_ajax_prepare_export_' . sanitize_key( $_POST['_export'] ), array() );
				} else {
					return new WP_Error( 'gd-error', __( "Your favorite color is neither red, blue, nor green!", "geodirectory" ) );
				}
				break;
			case "import":
				if ( ! empty( $_POST['_import'] ) && has_filter( 'geodir_ajax_import_' . sanitize_key( $_POST['_import'] ) ) ) {
					return apply_filters( 'geodir_ajax_import_' . sanitize_key( $_POST['_import'] ), array() );
				} else {
					return new WP_Error( 'gd-error', __( "Your favorite color is neither red, blue, nor green!", "geodirectory" ) );
				}
				break;
			case "export":
				if ( ! empty( $_POST['_export'] ) && has_filter( 'geodir_ajax_export_' . sanitize_key( $_POST['_export'] ) ) ) {
					return apply_filters( 'geodir_ajax_export_' . sanitize_key( $_POST['_export'] ), array() );
				} else {
					return new WP_Error( 'gd-error', __( "Your favorite color is neither red, blue, nor green!", "geodirectory" ) );
				}
				break;
			case "import_review":
				return self::import_reviews();
				break;
			case 'import_finish': {
				/**
				 * Run an action when an import finishes.
				 *
				 * This action can be used to fire functions after an import ends.
				 *
				 * @since 1.5.3
				 * @package GeoDirectory
				 */
				do_action( 'geodir_import_finished' );
			}
				break;
			default:
				if ( has_filter( 'geodir_ajax_imex_' . sanitize_key( $task ) ) ) {
					return apply_filters( 'geodir_ajax_imex_' . sanitize_key( $task ), array() );
				} else {
					return new WP_Error( 'gd-error', __( "Your favorite color is neither red, blue, nor green!", "geodirectory" ) );
				}
				break;
		}

		wp_defer_term_counting( false );

		return false;
	}

	/**
	 * Try to set higher limits on the fly
	 */
	public static function set_php_limits() {
		if ( ! ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ) {
			error_reporting( 0 );
		}
		/** @scrutinizer ignore-unhandled */ @ini_set( 'display_errors', 0 );

		// try to set higher limits for import
		$max_input_time     = ini_get( 'max_input_time' );
		$max_execution_time = ini_get( 'max_execution_time' );
		$memory_limit       = ini_get( 'memory_limit' );

		if ( $max_input_time !== 0 && $max_input_time != -1 && ( ! $max_input_time || $max_input_time < 3000 ) ) {
			ini_set( 'max_input_time', 3000 ); // @codingStandardsIgnoreLine
		}

		if ( $max_execution_time !== 0 && ( ! $max_execution_time || $max_execution_time < 3000 ) ) {
			ini_set( 'max_execution_time', 3000 ); // @codingStandardsIgnoreLine
		}

		if ( $memory_limit && str_replace( 'M', '', $memory_limit ) ) {
			if ( str_replace( 'M', '', $memory_limit ) < 256 ) {
				ini_set( 'memory_limit', '256M' ); // @codingStandardsIgnoreLine
			}
		}

		/*
		 * The `auto_detect_line_endings` setting has been deprecated in PHP 8.1,
		 * but will continue to work until PHP 9.0.
		 * For now, we're silencing the deprecation notice as there may still be
		 * translation files around which haven't been updated in a long time and
		 * which still use the old MacOS standalone `\r` as a line ending.
		 * This fix should be revisited when PHP 9.0 is in alpha/beta.
		 */
		@ini_set( 'auto_detect_line_endings', true ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	}

	/**
	 * Check the CSV is valid.
	 *
	 * @return bool|WP_Error
	 */
	public static function validate_csv() {
		global $wp_filesystem;

		$json        = array();
		$uploads     = wp_upload_dir();
		$uploads_dir = $uploads['basedir'];

		$csv_file = isset( $_POST['_file'] ) ? $_POST['_file'] : null;

		$csv_file_arr = explode( '/', $csv_file );
		$csv_filename = end( $csv_file_arr );
		$target_path  = $uploads_dir . '/geodir_temp/' . $csv_filename;


		$json['file']  = $csv_file;
		$json['error'] = __( 'The uploaded file is not a valid csv file. Please try again.', 'geodirectory' );
		$file          = array();

		if ( $csv_file && $wp_filesystem->is_file( $target_path ) && $wp_filesystem->exists( $target_path ) ) {
			$wp_filetype = wp_check_filetype_and_ext( $target_path, $csv_filename );

			if ( ! empty( $wp_filetype ) && isset( $wp_filetype['ext'] ) && geodir_strtolower( $wp_filetype['ext'] ) == 'csv' ) {
				$json['error'] = null;

				$lc_all = setlocale( LC_ALL, 0 ); // Fix issue of fgetcsv ignores special characters when they are at the beginning of line
				setlocale( LC_ALL, 'en_US.UTF-8' );
				if ( ( $handle = fopen( $target_path, "r" ) ) !== false ) {
					while ( ( $data = fgetcsv( $handle, 100000, "," ) ) !== false ) {
						if ( ! empty( $data ) && count( $data ) > 1 ) {
							$file[] = $data;
						}
					}
					fclose( $handle );
				}
				setlocale( LC_ALL, $lc_all );

				$json['rows'] = ( ! empty( $file ) && count( $file ) > 1 ) ? count( $file ) - 1 : 0;

				if ( ! $json['rows'] > 0 ) {
					$json['error'] = __( "No data found in csv file.", "geodirectory" );
				}
			}
		}

		return $json;
	}

	/**
	 * Insert or Update the post info.
	 *
	 * @return array|string
	 */
	public static function import_posts() {

		$limit     = isset( $_POST['limit'] ) && $_POST['limit'] ? (int) $_POST['limit'] : 1;
		$processed = isset( $_POST['processed'] ) ? (int) $_POST['processed'] : 0;

		$processed ++; // add 1 to account for the csv header row

		$csv_row = $processed;


		$rows = self::get_csv_rows( $processed, $limit );

		if ( ! empty( $rows ) ) {
			$created         = 0;
			$updated         = 0;
			$skipped         = 0;
			$invalid         = 0;
			$invalid_address = 0;
			$images          = 0;
			$errors          = array();

			$update_or_skip = isset( $_POST['_ch'] ) && $_POST['_ch'] == 'update' ? 'update' : 'skip';
			$log_error = __( 'GD IMPORT LISTING [ROW %d]:', 'geodirectory' );

			foreach ( $rows as $post_info ) {
				$csv_row++;
				if ( $update_or_skip == 'skip' && isset( $post_info['ID'] ) && $post_info['ID'] ) {
					$skipped ++;
					continue;
				}
				$line_error = wp_sprintf( $log_error, $csv_row );

				$temp_title = isset($post_info['post_title']) ? esc_attr($post_info['post_title']) :'' ;

				$post_info = self::validate_post( $post_info );

				// set if there are images to upload
				if ( isset( $post_info['_post_images_to_upload'] ) && $post_info['_post_images_to_upload'] ) {
					$images = $images + $post_info['_post_images_to_upload'];
				}

				if ( is_array($post_info) ) {
					/**
					 * @since 2.0.0.68
					 */
					do_action( 'geodir_import_post_before', $post_info );

					$result = false;

					// Update
					if ( ! empty( $post_info['ID'] ) ) {
						$result = wp_update_post( $post_info, true ); // we hook into the save_post hook
						if ( $result && ! is_wp_error( $result ) ) {
							$updated ++;
						} else {
							$invalid ++;
							$errors[$csv_row] = sprintf( esc_attr__('Row %d Error: %s', 'geodirectory'), $csv_row, esc_attr($result->get_error_message()) );
							geodir_error_log( $line_error . ' ' . $result->get_error_message() );
						}

						// insert
					} else {
						$result = wp_insert_post( $post_info, true ); // we hook into the save_post hook
						if ( $result && ! is_wp_error( $result ) ) {
							$created ++;
						} else {
							$invalid ++;
							$errors[$csv_row] = sprintf( esc_attr__('Row %d Error: %s', 'geodirectory'), $csv_row, esc_attr($result->get_error_message()) );
							geodir_error_log( $line_error . ' ' . $result->get_error_message() );
						}
					}

					/**
					 * @since 2.0.0.68
					 */
					do_action( 'geodir_import_post_after', $post_info, $result );

				} else {
					$invalid ++;
					$errors[$csv_row] = sprintf( esc_attr__('Row %d Error: %s', 'geodirectory'), $csv_row, esc_attr($post_info) );
				}
			}
		} else {
			return new WP_Error( 'gd-csv-empty', __( "No data found in csv file.", "geodirectory" ) );
		}

		return array(
			"processed" => $processed,
			"created"   => $created,
			"updated"   => $updated,
			"skipped"   => $skipped,
			"invalid"   => $invalid,
			"images"    => $images,
			//"ID"        => $post_info['ID'],
			"errors"    => $errors
		);
	}

	/**
	 * Get specific rows from a CSV file.
	 *
	 * @param int $row Optional. The row to start on.
	 * @param int $count Optional. The number of rows to get.
	 *
	 * @return array
	 */
	public static function get_csv_rows( $row = 0, $count = 0 ) {

		$csv_file = isset( $_POST['_file'] ) ? $_POST['_file'] : null;

		$uploads      = wp_upload_dir();
		$uploads_dir  = $uploads['basedir'];
		$csv_file_arr = explode( '/', $csv_file );
		$csv_filename = end( $csv_file_arr );
		$target_path  = $uploads_dir . '/geodir_temp/' . $csv_filename;

		//echo '###'.$target_path;

		$file   = array();
		$lc_all = setlocale( LC_ALL, 0 ); // Fix issue of fgetcsv ignores special characters when they are at the beginning of line
		setlocale( LC_ALL, 'en_US.UTF-8' );
		$l       = 0; // loop count
		$f       = 0; // file count
		$headers = array();
		if ( ( $handle = fopen( $target_path, "r" ) ) !== false ) {
			while ( ( $data = fgetcsv( $handle, 100000, "," ) ) !== false ) {

				// get headers
				if ( $l === 0 ) {
					$headers = $data;
					$l ++;
					continue;
				}
				// only get the rows needed
				if ( $row && $count ) {

					// if we have everything we need then break;
					if ( $l == $row + $count ) {
						break;

						// if its less than the start row then continue;
					} elseif ( $l && $l < $row ) {
						$l ++;
						continue;

						// if we have the count we need then break;
					} elseif ( $f > $count ) {
						break;
					}
				}

				if ( ! empty( $data ) ) {
					//$file[] = $data;
					$file[] = array_combine( $headers, $data ); // replace the keys with the CSV headers.
					$f ++;
					$l ++;
				}
			}
			fclose( $handle );
		}
		setlocale( LC_ALL, $lc_all );

		return $file;

	}

	/**
	 * Validate the post info.
	 *
	 * @todo make this validate more of the post info.
	 *
	 * @param $post_info
	 *
	 * @return array
	 */
	public static function validate_post( $row ) {
		$post_info = $row;

		$post_info = array_map( 'trim', $post_info );

		// Validate post_type
		if ( ! empty( $post_info['post_type'] ) ) {
			$post_type = esc_attr( $post_info['post_type'] );

			if ( ! geodir_is_gd_post_type( $post_type ) ) {
				return esc_attr( wp_sprintf( __( 'Invalid post type - %s', 'geodirectory' ), $post_type ) );
			}
		} else {
			return esc_attr__( 'Post type missing', 'geodirectory' );
		}

		// validate title
		if ( isset( $post_info['post_title'] ) && empty($post_info['post_title']) ) {
			return esc_attr__('Title missing','geodirectory');
		}

		// Convert date in mysql format
		if ( ! empty( $post_info['post_date'] ) && strpos( $post_info['post_date'], '/' ) !== false ) {
			$post_info['post_date'] = geodir_date( $post_info['post_date'], 'Y-m-d H:i:s' );
		}

		if ( ! empty( $post_info['post_modified'] ) && strpos( $post_info['post_modified'], '/' ) !== false ) {
			$post_info['post_modified'] = geodir_date( $post_info['post_modified'], 'Y-m-d H:i:s' );
		}

		// change post_category to an array()
		if ( isset( $post_info['post_category'] ) ) {
			if ( empty( $post_info['post_category'] ) ) {
				$post_info['tax_input'][$post_type.'category'] = array();
			} else {
				$post_info['tax_input'][$post_type.'category'] = array_map( 'trim', explode( ',', $post_info['post_category'] ) );
			}
			unset($post_info['post_category']);
		}

		// change post_tags to an array()
		if ( isset( $post_info['post_tags'] ) ) {
			if ( empty( $post_info['post_tags'] ) ) {
				$post_info['tax_input'][$post_type.'_tags'] = array();
			} else {
				$post_info['tax_input'][$post_type.'_tags'] = array_map( 'trim', explode( ',', $post_info['post_tags'] ) );
			}
			unset($post_info['post_tags']);
		}

		// check if we have post images to upload
		if ( isset( $post_info['post_images'] ) && $post_info['post_images'] ) {
			$images = explode( "::", $post_info['post_images'] );
			$i      = 0;
			if ( ! empty( $images ) ) {
				foreach ( $images as $image ) {
					if ( geodir_is_full_url( $image ) || strpos( $image, '#' ) === 0 ) {
						// It starts with 'http'
					} else {
						$i ++;
					}
				}
				if ( $i ) {
					$post_info['_post_images_to_upload'] = $i;
				}
			}
		}


		if ( GeoDir_Post_types::supports( $post_type, 'location' ) && geodir_cpt_requires_address( $post_type ) ) {
			// Fill in the GPS info from address if missing
			if ( ( isset( $post_info['latitude'] ) && empty( $post_info['latitude'] ) ) || ( isset( $post_info['longitude'] ) && empty( $post_info['longitude'] ) ) ) {
				$post_info = self::get_post_gps_from_address( $post_info );
				// Fill in the address if ONLY the GPS is provided
			} elseif (
				( isset( $post_info['city'] ) && empty( $post_info['city'] ) ) ||
				( isset( $post_info['region'] ) && empty( $post_info['region'] ) ) ||
				( isset( $post_info['country'] ) && empty( $post_info['country'] ) )
			) {
				//$post_info = self::get_post_address_from_gps($post_info);
				$post_info = esc_attr__( 'Address city, region or country missing', 'geodirectory' );
			}
		}

		if ( isset( $post_info['post_status'] ) ) {
			// Set post status publish for published status
			if ( $post_info['post_status'] == 'published' ) {
				$post_info['post_status'] = 'publish';
			}

			$statuses = geodir_get_post_stati( 'import', $post_info );

			// Set post status pending for non-standard status
			if ( ! in_array( $post_info['post_status'], $statuses ) ) {
				$post_info['post_status'] = 'pending';
			}
		}

		return apply_filters( 'geodir_import_validate_post', $post_info, $row );
	}

	/**
	 * Get the GPS from a post address.
	 *
	 * @param $post_info
	 *
	 * @return array|bool
	 */
	public static function get_post_gps_from_address( $post_info ) {
		$gps = geodir_get_gps_from_address( $post_info, true );

		if ( is_array( $gps ) && ! empty( $gps['latitude'] ) && ! empty( $gps['longitude'] ) ) {
			$post_info['latitude'] = $gps['latitude'];
			$post_info['longitude'] = $gps['longitude'];
		} else {
			if ( is_wp_error( $gps ) ) {
				return $gps->get_error_message();
			} else {
				return esc_attr__( 'Failed to retrieve GPS data from a address using API.', 'geodirectory' );
			}
		}

		return apply_filters( 'geodir_get_post_gps_from_address', $post_info, $gps );
	}

	/**
	 * Get the post address from the GPS info.
	 *
	 * @param $post_info
	 *
	 * @return array|bool
	 */
	public static function get_post_address_from_gps( $post_info ) {

		// @todo if users require a higher limit we should look at https://locationiq.org/

		$api_url = "https://maps.googleapis.com/maps/api/geocode/json?address=";
		$api_key = GeoDir_Maps::google_geocode_api_key();


		// if we don't have either the street or zip then we can't get an accurate address
		if( ( isset( $post_info['latitude'] ) && $post_info['latitude'] ) && ( isset( $post_info['longitude'] ) && $post_info['longitude'] ) ){}
		else{return esc_attr__('Not enough GPS info for address.','geodirectory');}

		$request_url = $api_url.$post_info['latitude'].",".$post_info['longitude'];

		// add the api key if we have it, it helps with limits
		if($api_key){
			$request_url .= "&key=".$api_key;
		}

		global $wp_version;
		$args = array(
			'timeout'     => 5,
			'redirection' => 5,
			'httpversion' => '1.0',
			'user-agent'  => 'WordPress/' . $wp_version . '; ' . home_url(),
			'blocking'    => true,
			'decompress'  => true,
			'sslverify'   => false,
		);
		$response = wp_remote_get( $request_url , $args );

		// Check for errors
		if ( is_wp_error( $response ) ) {
			return esc_attr__('Failed to reach Google geocode server.','geodirectory');
		}

		$body = wp_remote_retrieve_body( $response );
		$json = json_decode( $body, true );

		//print_r($json);exit;

		if(isset($json['status']) && $json['status']=='OK'){

			$post_info = self::get_address_from_geocode($post_info,$json['results']);


		}else{
			if(isset($json['status'])){
				return sprintf( esc_attr__('Google geocode failed: %s', 'geodirectory'),  esc_attr($json['status']) );

			}else{
				return esc_attr__('Failed to reach Google geocode server.','geodirectory');
			}

		}

		return $post_info;
	}


	//@todo maybe make this work in future after v2 release.
	public static function get_address_from_geocode($post_info,$geocodes){

//
//
//		// @todo, lets just do the first one for now, its most accurate.
//		$geocodes = $geocodes[0]['address_components'];
//
//		$address_components = array();
//		foreach($geocodes as $geocode){
//			$type = $geocode['types'][0];
//			$address_components[$type] = $geocode['long_name'];
//			if($type=='country'){
//				$address_components['country_code'] = $geocode['short_name'];
//			}
//
//		}
//
//		print_r($address_components );exit;
//
//		// check we have an address
//		if( isset( $post_info['street'] ) && $post_info['street'] ){ $address[] = $post_info['street'];  }
//		if( isset( $post_info['city'] ) && $post_info['city'] ){ $address[] = $post_info['city'];  }
//		if( isset( $post_info['region'] ) && $post_info['region'] ){ $address[] = $post_info['region'];  }
//		if( isset( $post_info['country'] ) && $post_info['country'] ){ $address[] = $post_info['country'];  }
//		if( isset( $post_info['zip'] ) && $post_info['zip'] ){ $address[] = $post_info['zip'];  }
//
//
//
//		return $post_info;
	}

	/**
	 * Export posts to CSV.
	 */
	public static function export_posts() {

		global $wp_filesystem;

		$nonce = isset( $_REQUEST['_nonce'] ) ? $_REQUEST['_nonce'] : null;

		$post_type      = isset( $_REQUEST['_pt'] ) ? $_REQUEST['_pt'] : null;
		$csv_file_dir   = self::import_export_cache_path( false );
		$chunk_per_page = isset( $_REQUEST['_n'] ) ? absint( $_REQUEST['_n'] ) : null;
		$chunk_per_page = $chunk_per_page < 50 || $chunk_per_page > 100000 ? 5000 : $chunk_per_page;
		$chunk_page_no  = isset( $_REQUEST['_p'] ) ? absint( $_REQUEST['_p'] ) : 1;

		do_action( 'geodir_export_posts_set_globals', $post_type );

		if ( $post_type == 'gd_event' ) {
			//add_filter( 'geodir_imex_export_posts_query', 'geodir_imex_get_events_query', 10, 2 ); // @todo this should be done from events plugin
		}
		$filters = ! empty( $_REQUEST['gd_imex'] ) && is_array( $_REQUEST['gd_imex'] ) ? $_REQUEST['gd_imex'] : null;

		$file_name = $post_type . '_' . date( 'dmyHi' );
		if ( $filters && isset( $filters['start_date'] ) && isset( $filters['end_date'] ) ) {
			$file_name = $post_type . '_' . date_i18n( 'dmy', strtotime( $filters['start_date'] ) ) . '_' . date_i18n( 'dmy', strtotime( $filters['end_date'] ) );
		}
		$posts_count    = geodir_get_posts_count( $post_type );
		$file_url_base  = self::import_export_cache_path() . '/';
		$file_url       = $file_url_base . $file_name . '.csv';
		$file_path      = $csv_file_dir . '/' . $file_name . '.csv';
		$file_path_temp = $csv_file_dir . '/' . $post_type . '_' . $nonce . '.csv';

		$chunk_file_paths = array();

		if ( isset( $_REQUEST['_c'] ) ) {
			$json['total'] = $posts_count;

			do_action( 'geodir_export_posts_reset_globals', $post_type );

			wp_send_json( $json );
			geodir_die();
		} else if ( isset( $_REQUEST['_st'] ) ) {
			$line_count = (int) self::file_line_count( $file_path_temp );
			$percentage = count( $posts_count ) > 0 && $line_count > 0 ? ceil( $line_count / $posts_count ) * 100 : 0;
			$percentage = min( $percentage, 100 );

			$json['percentage'] = $percentage;

			do_action( 'geodir_export_posts_reset_globals', $post_type );

			wp_send_json( $json );
			geodir_die();
		} else {
			if ( ! $posts_count > 0 ) {
				$json['error'] = __( 'No records to export.', 'geodirectory' );
			} else {
				$total_posts = $posts_count;
				if ( $chunk_per_page > $total_posts ) {
					$chunk_per_page = $total_posts;
				}
				$chunk_total_pages = ceil( $total_posts / $chunk_per_page );

				$j                = $chunk_page_no;
				$chunk_save_posts = self::get_posts_csv( $post_type, $chunk_per_page, $j );

				$per_page = 500;
				if ( $per_page > $chunk_per_page ) {
					$per_page = $chunk_per_page;
				}
				$total_pages = ceil( $chunk_per_page / $per_page );

				for ( $i = 0; $i <= $total_pages; $i ++ ) {
					$save_posts = array_slice( $chunk_save_posts, ( $i * $per_page ), $per_page );

					$clear = $i == 0 ? true : false;
					self::save_csv_data( $file_path_temp, $save_posts, $clear );
				}

				if ( $wp_filesystem->exists( $file_path_temp ) ) {
					$chunk_page_no   = $chunk_total_pages > 1 ? '_' . $j : '';
					$chunk_file_name = $file_name . $chunk_page_no . '_' . substr( geodir_rand_hash(), 0, 8 ) . '.csv';
					$file_path       = $csv_file_dir . '/' . $chunk_file_name;
					$wp_filesystem->move( $file_path_temp, $file_path, true );

					$file_url           = $file_url_base . $chunk_file_name;
					$chunk_file_paths[] = array(
						'i' => $j . '.',
						'u' => $file_url,
						's' => size_format( filesize( $file_path ), 2 )
					);
				}

				if ( ! empty( $chunk_file_paths ) ) {
					$json['total'] = $posts_count;
					$json['files'] = $chunk_file_paths;
				} else {
					if ( $j > 1 ) {
						$json['total'] = $posts_count;
						$json['files'] = array();
					} else {
						$json['error'] = __( 'ERROR: Could not create csv file. This is usually due to inconsistent file permissions.', 'geodirectory' );
					}
				}
			}

			do_action( 'geodir_export_posts_reset_globals', $post_type );
		}

		return $json;
	}

	/**
	 * Retrieves the posts for the current post type.
	 *
	 * @since 1.4.6
	 * @since 1.5.1 Updated to import & export recurring events.
	 * @since 1.5.3 Fixed to get wpml original post id.
	 * @since 1.5.7 $per_page & $page_no parameters added.
	 * @since 1.6.11 alive_days column added in exported csv.
	 * @package GeoDirectory
	 *
	 * @global object $wp_filesystem WordPress FileSystem object.
	 *
	 * @param string $post_type Post type.
	 * @param int $per_page Per page limit. Default 0.
	 * @param int $page_no Page number. Default 0.
	 *
	 * @return array Array of posts data.
	 */
	public static function get_posts_csv( $post_type, $per_page = 0, $page_no = 0 ) {
		global $wp_filesystem;

		$posts = self::get_export_posts( $post_type, $per_page, $page_no );

		$csv_rows = array();

		if ( ! empty( $posts ) ) {
			$i = 0; // posts processes

			foreach ( $posts as $post_info ) {
				// add the post_images column
				$post_info['post_images'] = GeoDir_Media::get_field_edit_string( $post_info['ID'], 'post_images', '', '', true );

				// fill in the CSV header
				if ( $i === 0 ) {
					$columns = array_keys( $post_info );
					$csv_rows[] = apply_filters( 'geodir_export_posts_csv_columns', $columns, $post_type );
				}

				// Business Hours Timezone
				if ( ! empty( $post_info['business_hours'] ) ) {
					$post_info['business_hours'] = geodir_sanitize_business_hours( $post_info['business_hours'], ( ! empty( $post_info['country'] ) ? $post_info['country'] : '' ) );
				}

				$csv_rows[] = apply_filters( 'geodir_export_posts_csv_row', $post_info, $post_info['ID'], $post_type );
				$i ++;
			}
		}

		return $csv_rows;

	}

	/**
	 * Retrieves the posts for the current post type.
	 *
	 * @since 1.4.6
	 * @since 1.5.7 $per_page & $page_no parameters added.
	 * @package GeoDirectory
	 *
	 * @global object $wpdb WordPress Database object.
	 * @global string $plugin_prefix Geodirectory plugin table prefix.
	 *
	 * @param string $post_type Post type.
	 * @param int $per_page Per page limit. Default 0.
	 * @param int $page_no Page number. Default 0.
	 *
	 * @return array Array of posts data.
	 */
	public static function get_export_posts( $post_type, $per_page = 0, $page_no = 0 ) {
		global $wpdb, $plugin_prefix;

		if ( ! post_type_exists( $post_type ) ) {
			return new stdClass;
		}

		$table = $plugin_prefix . $post_type . '_detail';

		$limit = '';
		if ( $per_page > 0 && $page_no > 0 ) {
			$offset = ( $page_no - 1 ) * $per_page;

			if ( $offset > 0 ) {
				$limit = " LIMIT " . $offset . "," . $per_page;
			} else {
				$limit = " LIMIT " . $per_page;
			}
		}

		// Skip listing with statuses trash, auto-draft etc...
		$skip_statuses  = geodir_imex_export_skip_statuses();
		$where_statuses = '';
		if ( ! empty( $skip_statuses ) && is_array( $skip_statuses ) ) {
			$where_statuses = "AND `" . $wpdb->posts . "`.`post_status` NOT IN('" . implode( "','", $skip_statuses ) . "')";
		}

		/**
		 * Filter the SQL where clause part to filter posts in import/export.
		 *
		 * @since 1.6.4
		 * @package GeoDirectory
		 *
		 * @param string $where SQL where clause part.
		 */
		$where_statuses = apply_filters( 'geodir_get_export_posts', $where_statuses, $post_type );

		$columns = array();

		$columns[] = "{$wpdb->posts}.ID";
		$columns[] = "{$wpdb->posts}.post_title";
		$columns[] = "{$wpdb->posts}.post_content";
		$columns[] = "{$wpdb->posts}.post_status";
		$columns[] = "{$wpdb->posts}.post_author";
		$columns[] = "{$wpdb->posts}.post_type";
		$columns[] = "{$wpdb->posts}.post_date";
		$columns[] = "{$wpdb->posts}.post_modified";

		// set the table fields
		$cpt_exclude_columns = array(
			'post_id',
			'post_title',
			'_search_title',
			'post_status',
			'submit_ip',
			'overall_rating',
			'rating_count',
			'mapview',
			'mapzoom',
			'post_dummy',
			'featured_image',
		);
		$cols_sql            = "DESCRIBE $table";
		$all_objects         = $wpdb->get_results( $cols_sql );
		if ( ! empty( $all_objects ) ) {
			foreach ( $all_objects as $column_schema ) {
				if ( ! in_array( $column_schema->Field, $cpt_exclude_columns ) ) {
					$columns[] = "`" . $column_schema->Field . "`";
				}
			}
		}


		/**
		 * Filter the SQL SELECT columns clause part to filter posts in import/export.
		 *
		 * @since 2.0.0
		 * @package GeoDirectory
		 *
		 * @param array $select SQL where clause part.
		 */
		$columns = apply_filters( 'geodir_get_export_posts_columns', $columns, $post_type );

		$columns = implode( ",", $columns );

		$query = $wpdb->prepare( "SELECT {$columns} FROM {$wpdb->posts} INNER JOIN {$table} ON {$table}.post_id = {$wpdb->posts}.ID WHERE {$wpdb->posts}.post_type = %s " . $where_statuses . " ORDER BY {$wpdb->posts}.ID ASC" . $limit, $post_type );

		/**
		 * Modify returned posts SQL query for the current post type.
		 *
		 * @since 1.4.6
		 * @package GeoDirectory
		 *
		 * @param int $query The SQL query.
		 * @param string $post_type Post type.
		 */
		$query   = apply_filters( 'geodir_imex_export_posts_query', $query, $post_type );
		$results = (array) $wpdb->get_results( $query, ARRAY_A );

		/**
		 * Modify returned post results for the current post type.
		 *
		 * @since 1.4.6
		 * @package GeoDirectory
		 *
		 * @param object $results An object containing all post ids.
		 * @param string $post_type Post type.
		 */
		return apply_filters( 'geodir_export_posts', $results, $post_type );
	}

	/**
	 * Save the data in CSV file to export.
	 *
	 * @since 1.4.6
	 * @package GeoDirectory
	 *
	 * @global null|object $wp_filesystem WP_Filesystem object.
	 *
	 * @param  string $file_path Full path to file.
	 * @param  array $csv_data Array of csv data.
	 * @param  bool $clear If true then it overwrite data otherwise add rows at the end of file.
	 *
	 * @return bool true if success otherwise false.
	 */
	public static function save_csv_data( $file_path, $csv_data = array(), $clear = true ) {
		if ( empty( $csv_data ) ) {
			return false;
		}

		global $wp_filesystem;

		$mode = $clear ? 'w+' : 'a+';

		if ( function_exists( 'fputcsv' ) ) {
			$file = fopen( $file_path, $mode );
			foreach ( $csv_data as $csv_row ) {
				// Escape data to prevent injection.
				$csv_row = array_map( 'geodir_escape_csv_data', $csv_row );
				$write_successful = fputcsv( $file, $csv_row, ",", $enclosure = '"' );
			}
			fclose( $file );
		} else {
			foreach ( $csv_data as $csv_row ) {
				// Escape data to prevent injection.
				$csv_row = array_map( 'geodir_escape_csv_data', $csv_row );
				$wp_filesystem->put_contents( $file_path, $csv_row );
			}
		}

		return true;
	}

	/**
	 * Export categories.
	 */
	public static function export_categories() {
		global $wp_filesystem;

		$nonce          = isset( $_REQUEST['_nonce'] ) ? sanitize_text_field( $_REQUEST['_nonce'] ) : null;
		$post_type      = isset( $_REQUEST['_pt'] ) ? sanitize_text_field( $_REQUEST['_pt'] ) : null;
		$chunk_per_page = isset( $_REQUEST['_n'] ) ? absint( $_REQUEST['_n'] ) : null;
		$chunk_per_page = $chunk_per_page < 50 || $chunk_per_page > 100000 ? 5000 : $chunk_per_page;
		$chunk_page_no  = isset( $_REQUEST['_p'] ) ? absint( $_REQUEST['_p'] ) : 1;
		$csv_file_dir   = self::import_export_cache_path( false );

		do_action( 'geodir_export_categories_set_globals', $post_type );

		$file_name = $post_type . 'category_' . date( 'dmyHi' );

		$terms_count    = geodir_get_terms_count( $post_type );
		$file_url_base  = self::import_export_cache_path() . '/';
		$file_url       = $file_url_base . $file_name . '.csv';
		$file_path      = $csv_file_dir . '/' . $file_name . '.csv';
		$file_path_temp = $csv_file_dir . '/' . $post_type . 'category_' . $nonce . '.csv';

		$chunk_file_paths = array();

		if ( isset( $_REQUEST['_st'] ) ) {
			$line_count = (int) self::file_line_count( $file_path_temp );
			$percentage = count( $terms_count ) > 0 && $line_count > 0 ? ceil( $line_count / $terms_count ) * 100 : 0;
			$percentage = min( $percentage, 100 );

			$json['percentage'] = $percentage;

			do_action( 'geodir_export_categories_reset_globals', $post_type );

			wp_send_json( $json );
		} else {
			if ( ! $terms_count > 0 ) {
				$json['error'] = __( 'No records to export.', 'geodirectory' );
			} else {
				$total_terms = $terms_count;
				if ( $chunk_per_page > $terms_count ) {
					$chunk_per_page = $terms_count;
				}
				$chunk_total_pages = ceil( $total_terms / $chunk_per_page );

				$j                = $chunk_page_no;
				$chunk_save_terms = self::get_categories( $post_type, $chunk_per_page, $j );

				$per_page = 500;
				if ( $per_page > $chunk_per_page ) {
					$per_page = $chunk_per_page;
				}
				$total_pages = ceil( $chunk_per_page / $per_page );

				for ( $i = 0; $i <= $total_pages; $i ++ ) {
					$save_terms = array_slice( $chunk_save_terms, ( $i * $per_page ), $per_page );

					$clear = $i == 0 ? true : false;
					self::save_csv_data( $file_path_temp, $save_terms, $clear );
				}

				if ( $wp_filesystem->exists( $file_path_temp ) ) {
					$chunk_page_no   = $chunk_total_pages > 1 ? '_' . $j : '';
					$chunk_file_name = $file_name . $chunk_page_no . '_' . substr( geodir_rand_hash(), 0, 8 ) . '.csv';
					$file_path       = $csv_file_dir . '/' . $chunk_file_name;
					$wp_filesystem->move( $file_path_temp, $file_path, true );

					$file_url           = $file_url_base . $chunk_file_name;
					$chunk_file_paths[] = array(
						'i' => $j . '.',
						'u' => $file_url,
						's' => size_format( filesize( $file_path ), 2 )
					);
				}

				if ( ! empty( $chunk_file_paths ) ) {
					$json['total'] = $terms_count;
					$json['files'] = $chunk_file_paths;
				} else {
					$json['error'] = __( 'ERROR: Could not create csv file. This is usually due to inconsistent file permissions.', 'geodirectory' );
				}
			}

			do_action( 'geodir_export_categories_reset_globals', $post_type );
		}

		return $json;
	}

	/**
	 * Retrieve terms for given post type.
	 *
	 * @since 1.4.6
	 * @since 1.5.7 $per_page & $page_no parameters added.
	 * @package GeoDirectory
	 *
	 * @param  string $post_type The post type.
	 * @param int $per_page Per page limit. Default 0.
	 * @param int $page_no Page number. Default 0.
	 *
	 * @return array Array of terms data.
	 */
	public static function get_categories( $post_type, $per_page = 0, $page_no = 0 ) {
		$args = array( 'hide_empty' => 0, 'orderby' => 'id' );

		remove_all_filters( 'get_terms' );

		$post_type = sanitize_text_field( $post_type );
		$taxonomy = $post_type . 'category';

		if ( $per_page > 0 && $page_no > 0 ) {
			$args['offset'] = ( $page_no - 1 ) * $per_page;
			$args['number'] = $per_page;
		}

		$terms = get_terms( $taxonomy, $args );

		$csv_rows = array();

		if ( ! empty( $terms ) ) {
			$csv_row   = array();
			$csv_row[] = 'cat_id';
			$csv_row[] = 'cat_name';
			$csv_row[] = 'cat_slug';
			$csv_row[] = 'cat_posttype';
			$csv_row[] = 'cat_parent';
			$csv_row[] = 'cat_schema';
			$csv_row[] = 'cat_font_icon';
			$csv_row[] = 'cat_color';
			$csv_row[] = 'cat_description';
			$csv_row[] = 'cat_top_description';
			$csv_row[] = 'cat_bottom_description';
			$csv_row[] = 'cat_image';
			$csv_row[] = 'cat_icon';

			$csv_rows[] = apply_filters( 'geodir_export_categories_csv_columns', $csv_row, $post_type );

			foreach ( $terms as $term ) {
				$cat_icon  = geodir_get_cat_icon( $term->term_id, true );
				$cat_image = geodir_get_cat_image( $term->term_id, true );

				$cat_parent = '';
				if ( isset( $term->parent ) && (int) $term->parent > 0 && term_exists( (int) $term->parent, $taxonomy ) ) {
					$parent_term = (array) get_term_by( 'id', (int) $term->parent, $taxonomy );
					$cat_parent  = ! empty( $parent_term ) && isset( $parent_term['name'] ) ? $parent_term['name'] : '';
				}

				$csv_row   = array();
				$csv_row[] = $term->term_id;
				$csv_row[] = $term->name;
				$csv_row[] = $term->slug;
				$csv_row[] = $post_type;
				$csv_row[] = $cat_parent;
				$csv_row[] = get_term_meta( $term->term_id, 'ct_cat_schema', true );
				$csv_row[] = get_term_meta( $term->term_id, 'ct_cat_font_icon', true );
				$csv_row[] = get_term_meta( $term->term_id, 'ct_cat_color', true );
				$csv_row[] = $term->description;
				$csv_row[] = get_term_meta( $term->term_id, 'ct_cat_top_desc', true );
				$csv_row[] = get_term_meta( $term->term_id, 'ct_cat_bottom_desc', true );
				$csv_row[] = $cat_image;
				$csv_row[] = $cat_icon;

				$csv_rows[] = apply_filters( 'geodir_export_categories_csv_row', $csv_row, $term->term_id, $post_type );
			}
		}

		return $csv_rows;
	}

	/**
	 * Import categories.
	 *
	 * @return array|string
	 */
	public static function import_categories() {

		$limit     = isset( $_POST['limit'] ) && $_POST['limit'] ? (int) $_POST['limit'] : 1;
		$processed = isset( $_POST['processed'] ) ? (int) $_POST['processed'] : 0;

		$processed ++;
		$rows = self::get_csv_rows( $processed, $limit );

		if ( ! empty( $rows ) ) {
			$created = 0;
			$updated = 0;
			$skipped = 0;
			$invalid = 0;
			$images  = 0;

			$update_or_skip = isset( $_POST['_ch'] ) && $_POST['_ch'] == 'update' ? 'update' : 'skip';

			foreach ( $rows as $cat_info ) {

				$cat_info = self::validate_cat( $cat_info );

				if ( $update_or_skip == 'skip' && isset( $cat_info['term_id'] ) && $cat_info['term_id'] ) {
					$skipped ++;
					continue;
				}


				//print_r($cat_info );exit;

				if ( $cat_info ) {
					do_action( 'geodir_import_category_set_globals', $cat_info );

					// Update
					if ( isset( $cat_info['term_id'] ) && $cat_info['term_id'] ) {

						$result = self::update_term( $cat_info['taxonomy'], $cat_info );

						if ( $result ) {
							$updated ++;
						} else {
							$invalid ++;
						}

						// insert
					} else {
						$result = self::insert_term( $cat_info['taxonomy'], $cat_info );
						if ( $result ) {
							$created ++;
						} else {
							$invalid ++;
						}
					}


					////////////////////////////////////////////////////////// update term meta
					if ( $result ) {
						$term_data       = $cat_info;
						$term_id         = $result;
						$taxonomy        = $cat_info['taxonomy'];
						$uploads         = wp_upload_dir();

						do_action( 'geodir_category_imported', $term_id, $term_data );

						if ( isset( $term_data['cat_top_description'] ) ) {
							update_term_meta( $term_id, 'ct_cat_top_desc', $term_data['cat_top_description'] );
						}

						if ( isset( $term_data['cat_bottom_description'] ) ) {
							update_term_meta( $term_id, 'ct_cat_bottom_desc', $term_data['cat_bottom_description'] );
						}

						if ( isset( $term_data['cat_schema'] ) ) {
							update_term_meta( $term_id, 'ct_cat_schema', $term_data['cat_schema'] );
						}

						// Category font awesome icon
						if ( isset( $term_data['cat_font_icon'] ) ) {
							update_term_meta( $term_id, 'ct_cat_font_icon', $term_data['cat_font_icon'] );
						}

						// Category color
						if ( isset( $term_data['cat_color'] ) ) {
							update_term_meta( $term_id, 'ct_cat_color', $term_data['cat_color'] );
						}

						$attachment = false;
						if ( isset( $term_data['image'] ) && $term_data['image'] != '' ) {
							$cat_image = geodir_get_cat_image( $term_id );

							if ( empty( $cat_image ) || ( ! empty( $cat_image ) && basename( $cat_image ) != $term_data['image'] ) ) {
								$attachment = true;
								$image_id = 'image';
								$image_url = trim( $uploads['subdir'] . '/' . $term_data['image'], '/\\' );

								if ( geodir_is_full_url( $term_data['cat_image'] ) ) {
									$attachment_id = self::generate_attachment_id( $term_data['cat_image'] );
									if ( $attachment_id && ( $attachment_url = wp_get_attachment_url( $attachment_id ) ) ) {
										$image_id = $attachment_id;
										$image_url = geodir_file_relative_url( $attachment_url );
									}
								}

								update_term_meta( $term_id, 'ct_cat_default_img', array(
									'id'  => $image_id,
									'src' => $image_url
								) );
							}
						}

						if ( isset( $term_data['icon'] ) && $term_data['icon'] != '' ) {
							$cat_icon = geodir_get_cat_icon( $term_id );

							if ( empty( $cat_icon ) || ( ! empty( $cat_icon ) && basename( $cat_icon ) != $term_data['icon'] ) ) {
								$attachment = true;
								$image_id = 'icon';
								$image_url = trim( $uploads['subdir'] . '/' . $term_data['icon'], '/\\' );

								if ( geodir_is_full_url( $term_data['cat_icon'] ) ) {
									$attachment_id = self::generate_attachment_id( $term_data['cat_icon'] );
									if ( $attachment_id && ( $attachment_url = wp_get_attachment_url( $attachment_id ) ) ) {
										$image_id = $attachment_id;
										$image_url = geodir_file_relative_url( $attachment_url );
									}
								}

								update_term_meta( $term_id, 'ct_cat_icon', array(
									'id'  => $image_id,
									'src' => $image_url
								) );
							}
						}

						if ( $attachment ) {
							$images ++;
						}
					}
					///////////////////////////////////////////////////////////////////// update term meta end

					do_action( 'geodir_import_category_reset_globals', $cat_info );

				} else {
					$invalid ++;
				}

			}

		} else {
			return new WP_Error( 'gd-csv-empty', __( "No data found in csv file.", "geodirectory" ) );
		}

		return array(
			"processed" => $processed,
			"created"   => $created,
			"updated"   => $updated,
			"skipped"   => $skipped,
			"invalid"   => $invalid,
			"images"    => $images,
			"ID"        => isset($cat_info['cat_id']) ? $cat_info['cat_id'] : 0,
		);
	}

	/**
	 * Validate the cat info.
	 *
	 * @todo make this actually validate the cat info.
	 *
	 * @param $cat_info
	 *
	 * @return array
	 */
	public static function validate_cat( $cat_info ) {
		$cat_info = array_map( 'trim', $cat_info );

		$cat_info_fixed = array();

		// fix column names
		$cat_info_fixed['taxonomy']            = isset( $cat_info['cat_posttype'] ) && $cat_info['cat_posttype'] ? esc_attr( $cat_info['cat_posttype'] . "category" ) : '';
		$cat_info_fixed['term_id']             = isset( $cat_info['cat_id'] ) && $cat_info['cat_id'] ? absint( $cat_info['cat_id'] ) : '';
		$cat_info_fixed['name']                = isset( $cat_info['cat_name'] ) && $cat_info['cat_name'] ? esc_attr( $cat_info['cat_name'] ) : '';
		$cat_info_fixed['slug']                = isset( $cat_info['cat_slug'] ) && $cat_info['cat_slug'] ? esc_attr( $cat_info['cat_slug'] ) : '';
		$cat_info_fixed['parent']              = isset( $cat_info['cat_parent'] ) && $cat_info['cat_parent'] ? $cat_info['cat_parent'] : '';
		$cat_info_fixed['description']         = isset( $cat_info['cat_description'] ) && $cat_info['cat_description'] ? geodir_sanitize_textarea_field( $cat_info['cat_description'] ) : '';
		$cat_info_fixed['cat_schema']          = isset( $cat_info['cat_schema'] ) && $cat_info['cat_schema'] ? esc_attr( $cat_info['cat_schema'] ) : '';
		$cat_info_fixed['cat_font_icon']       = isset( $cat_info['cat_font_icon'] ) && $cat_info['cat_font_icon'] ? esc_attr( $cat_info['cat_font_icon'] ) : '';
		$cat_info_fixed['cat_color']           = isset( $cat_info['cat_color'] ) && $cat_info['cat_color'] ? esc_attr( $cat_info['cat_color'] ) : '';
		$cat_info_fixed['cat_top_description'] = isset( $cat_info['cat_top_description'] ) && $cat_info['cat_top_description'] ? geodir_sanitize_html_field( $cat_info['cat_top_description'] ) : '';
		$cat_info_fixed['cat_bottom_description'] = isset( $cat_info['cat_bottom_description'] ) && $cat_info['cat_bottom_description'] ? geodir_sanitize_html_field( $cat_info['cat_bottom_description'] ) : '';
		$cat_info_fixed['cat_image']           = isset( $cat_info['cat_image'] ) && $cat_info['cat_image'] ? $cat_info['cat_image'] : '';
		$cat_info_fixed['cat_icon']            = isset( $cat_info['cat_icon'] ) && $cat_info['cat_icon'] ? $cat_info['cat_icon'] : '';

		// validate @todo validate the info

		// temp image fix
		$cat_info_fixed['image'] 				= $cat_info_fixed['cat_image'] != '' ? basename( $cat_info_fixed['cat_image'] ) : '';
		$cat_info_fixed['icon']  				= $cat_info_fixed['cat_icon'] != '' ? basename( $cat_info_fixed['cat_icon'] ) : '';

		if ( ! empty( $cat_info_fixed['parent'] ) ) {
			$parent = 0;
			if ( $term = get_term_by( 'id', $cat_info_fixed['parent'], $cat_info_fixed['taxonomy'] ) ) {
				$parent = $term->term_id;
			} else if ( $term = get_term_by( 'slug', $cat_info_fixed['parent'], $cat_info_fixed['taxonomy'] ) ) {
				$parent = $term->term_id;
			} else if ( $term = get_term_by( 'name', $cat_info_fixed['parent'], $cat_info_fixed['taxonomy'] ) ) {
				$parent = $term->term_id;
			}
			$cat_info_fixed['parent'] = $parent;
		}

		return apply_filters( 'geodir_import_category_validate_item', $cat_info_fixed, $cat_info );
	}

	/**
	 * Validate the review info.
	 *
	 * @todo make this actually validate the review info.
	 *
	 * @param $review_info
	 *
	 * @return array
	 */
	public static function validate_review( $data ) {
		global $gd_cache_user;

		$data = array_map( 'trim', $data );

		$review_data 							= array();
		$review_data['comment_ID'] 				= isset( $data['comment_ID'] ) ? absint( $data['comment_ID'] ) : '';
		$review_data['comment_post_ID'] 		= isset( $data['comment_post_ID'] ) ? absint( $data['comment_post_ID'] ) : '';
		$review_data['rating'] 					= isset( $data['rating'] ) ? absint( $data['rating'] ) : '';
		$review_data['comment_content'] 		= isset( $data['comment_content'] ) ? $data['comment_content'] : '';
		$review_data['comment_date'] 			= isset( $data['comment_date'] ) ? $data['comment_date'] : '';
		$review_data['comment_date_gmt'] 		= isset( $data['comment_date_gmt'] ) ? $data['comment_date_gmt'] : '';
		$review_data['comment_approved'] 		= isset( $data['comment_approved'] ) ? $data['comment_approved'] : 0;
		$review_data['user_id'] 				= isset( $data['user_id'] ) ? absint( $data['user_id'] ) : 0;
		$review_data['comment_author'] 			= isset( $data['comment_author'] ) ? $data['comment_author'] : '';
		$review_data['comment_author_email']	= isset( $data['comment_author_email'] ) && is_email( $data['comment_author_email'] ) ? $data['comment_author_email'] : '';
		$review_data['comment_author_url'] 		= isset( $data['comment_author_url'] ) ? $data['comment_author_url'] : '';
		$review_data['comment_author_IP'] 		= isset( $data['comment_author_IP'] ) ? $data['comment_author_IP'] : '';

		if ( empty( $gd_cache_user ) ) {
			$gd_cache_user = array();
		}

		$user_ID = $review_data['user_id'];
		$user = NULL;
		if ( ! empty( $user_ID ) ) {
			if ( ! empty( $gd_cache_user[ $user_ID ] ) ) {
				$user = $gd_cache_user[ $user_ID ];
			} else {
				$user = get_user_by( 'id', $user_ID );
				if ( ! empty( $user ) ) {
					if ( empty( $user->display_name ) ) {
						$user->display_name = $user->user_login;
					}
					$gd_cache_user[ $user_ID ] = $user;
				}
			}
		}

		if ( ! empty( $user ) ) {
			if ( empty( $review_data['comment_author'] ) ) {
				$review_data['comment_author'] = $user->display_name;
			}

			if ( empty( $review_data['comment_author_email'] ) ) {
				$review_data['comment_author_email'] = $user->user_email;
			}

			if ( empty( $review_data['comment_author_url'] ) ) {
				$review_data['comment_author_url'] = $user->user_url;
			}
		}

		if ( $review_data['comment_approved'] == 'approve' || $review_data['comment_approved'] == 'approved' ) {
			$review_data['comment_approved'] = 1;
		}

		if ( $review_data['comment_approved'] == 'pending' || $review_data['comment_approved'] == 'unapproved' || $review_data['comment_approved'] == 'hold' ) {
			$review_data['comment_approved'] = 0;
		}

		if ( ! empty( $review_data['comment_date'] ) ) {
			$review_data['comment_date'] = geodir_date( $review_data['comment_date'], 'Y-m-d H:i:s' );
		}

		if ( ! empty( $review_data['comment_date_gmt'] ) ) {
			$review_data['comment_date_gmt'] = geodir_date( $review_data['comment_date_gmt'], 'Y-m-d H:i:s' );
		}

		if ( empty( $review_data['comment_date'] ) ) {
			$review_data['comment_date'] = current_time( 'mysql' );
		}

		if ( empty( $review_data['comment_date_gmt'] ) ) {
			$review_data['comment_date_gmt'] = get_gmt_from_date( $review_data['comment_date'] );
		}

		$review_data = wp_filter_comment( $review_data );

		if ( ! empty( $review_data['comment_ID'] ) ) {
			$unsets = array( 'comment_date', 'comment_date_gmt', 'comment_agent', 'comment_author_IP' );

			foreach ( $unsets as $unset ) {
				if ( empty( $review_data[ $unset ] ) && isset( $review_data[ $unset ] ) ) {
					unset( $review_data[ $unset ] );
				}
			}
		}

		return apply_filters( 'validate_review', $review_data, $data );
	}

	/**
	 * Update the post term.
	 *
	 * @since 1.4.6
	 * @package GeoDirectory
	 *
	 * @param string $taxonomy Post taxonomy.
	 * @param array $term_data {
	 *    Attributes of term data.
	 *
	 * @type string $term_id Term ID.
	 * @type string $name Term name.
	 * @type string $slug Term slug.
	 * @type string $description Term description.
	 * @type string $top_description Term top description.
	 * @type string $image Default Term image.
	 * @type string $icon Default Term icon.
	 * @type string $taxonomy Term taxonomy.
	 * @type int $parent Term parent ID.
	 *
	 * }
	 * @return int|bool Term id when success, false when fail.
	 */
	public static function update_term( $taxonomy, $term_data ) {
		if ( empty( $taxonomy ) || empty( $term_data ) ) {
			return false;
		}

		$term_id = isset( $term_data['term_id'] ) && ! empty( $term_data['term_id'] ) ? $term_data['term_id'] : 0;

		if ( $term_id > 0 && $term_info = (array) get_term( $term_id, $taxonomy ) ) {
			$term_data['term_id'] = $term_info['term_id'];

			$result = wp_update_term( $term_data['term_id'], $taxonomy, $term_data );

			if ( ! is_wp_error( $result ) ) {
				return isset( $result['term_id'] ) ? $result['term_id'] : 0;
			}
		} else if ( $term_data['slug'] != '' && $term_info = (array) term_exists( $term_data['slug'], $taxonomy ) ) {
			$term_data['term_id'] = $term_info['term_id'];

			$result = wp_update_term( $term_data['term_id'], $taxonomy, $term_data );

			if ( ! is_wp_error( $result ) ) {
				return isset( $result['term_id'] ) ? $result['term_id'] : 0;
			}
		} else {
			return self::insert_term( $taxonomy, $term_data );
		}

		return false;
	}

	/**
	 * Create new the post term.
	 *
	 * @since 1.4.6
	 * @package GeoDirectory
	 *
	 * @param string $taxonomy Post taxonomy.
	 * @param array $term_data {
	 *    Attributes of term data.
	 *
	 * @type string $name Term name.
	 * @type string $slug Term slug.
	 * @type string $description Term description.
	 * @type string $top_description Term top description.
	 * @type string $bottom_description Term bottom description.
	 * @type string $image Default Term image.
	 * @type string $icon Default Term icon.
	 * @type string $taxonomy Term taxonomy.
	 * @type int $parent Term parent ID.
	 *
	 * }
	 * @return int|bool Term id when success, false when fail.
	 */
	public static function insert_term( $taxonomy, $term_data ) {
		if ( empty( $taxonomy ) || empty( $term_data ) ) {
			return false;
		}


		$term                = isset( $term_data['name'] ) && ! empty( $term_data['name'] ) ? $term_data['name'] : '';
		$args                = array();
		$args['description'] = isset( $term_data['description'] ) ? $term_data['description'] : '';
		$args['slug']        = isset( $term_data['slug'] ) ? $term_data['slug'] : '';
		$args['parent']      = isset( $term_data['parent'] ) ? (int) $term_data['parent'] : '';

		if ( ( ! empty( $args['slug'] ) && term_exists( $args['slug'], $taxonomy ) ) || empty( $args['slug'] ) ) {
			$term_args    = array_merge( $term_data, $args );
			$defaults     = array( 'alias_of' => '', 'description' => '', 'parent' => 0, 'slug' => '' );
			$term_args    = wp_parse_args( $term_args, $defaults );
			$term_args    = sanitize_term( $term_args, $taxonomy, 'db' );
			$args['slug'] = wp_unique_term_slug( $args['slug'], (object) $term_args );
		}

		if ( ! empty( $term ) ) {
			$result = wp_insert_term( $term, $taxonomy, $args );
			if ( ! is_wp_error( $result ) ) {
				return isset( $result['term_id'] ) ? $result['term_id'] : 0;
			}
		}

		return false;
	}

	/**
	 * Export the GD settings to a time stamped .json file.
	 */
	public static function export_settings() {
		$settings = geodir_get_settings();

		// unset taxonomies and post_types, maybe we will allow this at a later stage
		unset( $settings['taxonomies'] );
		unset( $settings['post_types'] );

		//print_r($settings );exit; // for testing

		$filename = "geodirectory-settings-" . time();
		header( 'Content-disposition: attachment; filename=' . $filename . '.json' );
		header( 'Content-type: application/json' );
		echo json_encode( $settings );
		exit;
	}

	/**
	 * Import GD settings.
	 *
	 * @return array|WP_Error
	 */
	public static function import_settings() {
		$json_file = isset( $_POST['_file'] ) ? $_POST['_file'] : null;

		$settings  = self::validate_json( $json_file );

		if ( is_wp_error( $settings ) ) {
			return $settings;
		}

		if ( $settings === false ) {
			return new WP_Error( 'gd-invalid-json', __( "json file is not valid.", "geodirectory" ) );
		} elseif ( empty( $settings ) ) {
			return new WP_Error( 'gd-empty-json', __( "json file is empty.", "geodirectory" ) );
		}

		$i = 0;
		foreach ( $settings as $key => $setting ) {
			geodir_update_option( $key, $setting );
			$i ++;
		}

		return array(
			'success' => true,
			'updated' => $i,
			'data'    => __( 'Settings updated.', 'geodirectory' )
		);
	}

	/**
	 * Check the CSV is valid.
	 *
	 * @return bool|WP_Error
	 */
	public static function validate_json( $json_file ) {
		global $wp_filesystem;

		if ( is_wp_error( $json_file ) ) {
			return $json_file;
		}

		$json          = array();
		$uploads       = wp_upload_dir();
		$uploads_dir   = $uploads['basedir'];
		$json_file_arr = explode( '/', $json_file );
		$json_filename = end( $json_file_arr );
		$target_path   = $uploads_dir . '/geodir_temp/' . $json_filename;

		if ( $json_file && $wp_filesystem->is_file( $target_path ) && $wp_filesystem->exists( $target_path ) ) {
			add_filter( 'upload_mimes', array(
				'GeoDir_Admin_Import_Export',
				'allow_json_mime'
			) ); // make it recognise json files
			add_filter( 'wp_check_filetype_and_ext', array(
				'GeoDir_Admin_Import_Export',
				'set_filetype_and_ext'
			), 10, 4 ); // set file type & extension, it may returns any of from text/plain & application/json.
			$wp_filetype = wp_check_filetype_and_ext( $target_path, $json_filename );

			if ( ! empty( $wp_filetype ) && isset( $wp_filetype['ext'] ) && geodir_strtolower( $wp_filetype['ext'] ) == 'json' ) {
				$json['error'] = null;

				$file_contents = $wp_filesystem->get_contents( $target_path );

				if ( $json = json_decode( $file_contents, true ) ) {
					if ( is_array( $json ) ) {
						return $json;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Adds the .json file extension to the WP allowed file types on the fly.
	 *
	 * @param array $mimes The currently allowed file mime types.
	 *
	 * @return array The new array of allowed file types with json added.
	 */
	public static function allow_json_mime( $mimes ) {
		$mimes['json'] = 'application/json';

		return $mimes;
	}

	/**
	 * Get the SQL where clause part to filter posts in import/export.
	 *
	 * @global object $wpdb WordPress Database object.
	 *
	 * @param string $where The SQL where clause part. Default empty.
	 * @param string $post_type The post type.
	 * @return string SQL where clause part.
	 */
	public static function filter_where_query($where = '', $post_type = '') {
		global $wpdb;

		$filters = !empty( $_REQUEST['gd_imex'] ) && is_array( $_REQUEST['gd_imex'] ) ? $_REQUEST['gd_imex'] : NULL;

		if ( !empty( $filters ) ) {
			foreach ( $filters as $field => $value ) {
				switch ($field) {
					case 'start_date':
						$where .= " AND `" . $wpdb->posts . "`.`post_date` >= '" . sanitize_text_field( $value ) . " 00:00:00'";
						break;
					case 'end_date':
						$where .= " AND `" . $wpdb->posts . "`.`post_date` <= '" . sanitize_text_field( $value ) . " 23:59:59'";
						break;
				}
			}
		}

		return $where;
	}

	/**
	 * Get the path of cache directory.
	 *
	 * @param  bool $relative True for relative path & False for absolute path.
	 * @return string Path to the cache directory.
	 */
	public static function import_export_cache_path( $relative = true ) {
		$upload_dir = wp_upload_dir();

		return $relative ? $upload_dir['baseurl'] . '/cache' : $upload_dir['basedir'] . '/cache';
	}

	/**
	 * Count the number of line from file.
	 *
	 * @since 1.4.6
	 * @package GeoDirectory
	 *
	 * @global null|object $wp_filesystem WP_Filesystem object.
	 *
	 * @param  string $file Full path to file.
	 * @return int No of file rows.
	 */
	public static function file_line_count( $file ) {
		global $wp_filesystem;

		if ( $wp_filesystem->is_file( $file ) && $wp_filesystem->exists( $file ) ) {
			$contents = $wp_filesystem->get_contents_array( $file );

			if ( !empty( $contents ) && is_array( $contents ) ) {
				return count( $contents ) - 1;
			}
		}

		return NULL;
	}

    /**
     * Switch locale.
     *
     * @since 2.0.0
     *
     * @param string $locale Switch Locale value.
     * @return string $active_lang.
     */
	public static function switch_locale( $locale ) {
		return apply_filters( 'geodir_switch_locale', $locale );
	}

    /**
     * Restore locale.
     *
     * @since 2.0.0
     *
     * @param string $locale Restore locale value.
     * @return bool
     */
	public static function restore_locale( $locale ) {
		return apply_filters( 'geodir_restore_locale', $locale );
	}

	/**
	 * Prepare export reviews.
	 */
	public static function get_comment_args( $count = false ) {
		global $wpdb;

		$post_types = geodir_get_posttypes();

		$comment_args = array(
			'fields'     => 'ids',
			'count'      => $count,
			'parent'     => 0,
			'status'	 => 'any'
		);

		// post type
		if ( ! empty( $_REQUEST['gd_imex']['post_type'] ) && in_array( $_REQUEST['gd_imex']['post_type'], $post_types ) ) {
			$comment_args['post_type'] = sanitize_text_field( $_REQUEST['gd_imex']['post_type'] );
		} else {
			$comment_args['post_type'] = $post_types;
		}

		// date
		if ( ! empty( $_REQUEST['gd_imex']['start_date'] ) || ! empty( $_REQUEST['gd_imex']['end_date'] ) ) {
			$date_query = array(
				'inclusive' => true
			);
			if ( ! empty( $_REQUEST['gd_imex']['start_date'] ) ) {
				$date_query['after'] = $_REQUEST['gd_imex']['start_date'];
			}
			if ( ! empty( $_REQUEST['gd_imex']['end_date'] ) ) {
				$date_query['before'] = $_REQUEST['gd_imex']['end_date'];
			}
			$comment_args['date_query'] = array( $date_query );
		}

		// status
		if ( ! empty( $_REQUEST['gd_imex']['status'] ) ) {
			$comment_args['status'] = sanitize_text_field( $_REQUEST['gd_imex']['status'] );
		}

		return apply_filters( 'geodir_export_reviews_comment_args', $comment_args );
	}

	/**
	 * Prepare export reviews.
	 */
	public static function filter_reviews( $clauses, $comment_query ) {
		global $wpdb;

		if ( empty( $comment_query->query_vars['count'] ) ) {
			$clauses['fields'] = "{$wpdb->comments}.*, r.*";
		}

		$clauses['join'] .= " INNER JOIN " . GEODIR_REVIEW_TABLE . " AS r ON r.comment_id = {$wpdb->comments}.comment_ID";

		$where = array( "r.rating > 0" );
		if ( ! empty( $_REQUEST['gd_imex']['min_rating'] ) ) {
			$where[] = "r.rating >= " . absint( $_REQUEST['gd_imex']['min_rating'] );
		}
		if ( ! empty( $_REQUEST['gd_imex']['max_rating'] ) ) {
			$where[] = "r.rating <= " . absint( $_REQUEST['gd_imex']['max_rating'] );
		}
		$clauses['join'] .= " AND " . implode( " AND ", $where );

		return $clauses;
	}

	/**
	 * Retrieve reviews.
	 *
	 */
	public static function get_reviews( $per_page = 0, $page_no = 0 ) {
		global $wpdb;

		$comment_args = self::get_comment_args();
		if ( $per_page > 0 && $page_no > 0 ) {
			$comment_args['offset'] = ( $page_no - 1 ) * $per_page;
			$comment_args['number'] = $per_page;
		}

		$comment_query = new WP_Comment_Query();
		$comment_query->query( $comment_args );

		$items = ! empty( $comment_query->request ) ? $wpdb->get_results( $comment_query->request ) : array();

		$csv_rows = array();
		if ( ! empty( $items ) ) {
			$csv_row   = array();
			$csv_row[] = 'comment_ID';
			$csv_row[] = 'comment_post_ID';
			$csv_row[] = 'rating';
			$csv_row[] = 'comment_content';
			$csv_row[] = 'comment_date';
			$csv_row[] = 'comment_approved';
			$csv_row[] = 'user_id';
			$csv_row[] = 'comment_author';
			$csv_row[] = 'comment_author_email';
			$csv_row[] = 'comment_author_url';
			$csv_row[] = 'comment_author_IP';
			$csv_row[] = 'post_type';
			$csv_row[] = 'city';
			$csv_row[] = 'region';
			$csv_row[] = 'country';
			$csv_row[] = 'latitude';
			$csv_row[] = 'longitude';

			$csv_rows[] = $csv_row;

			foreach ( $items as $item ) {
				$csv_row   = array();
				$csv_row[] = $item->comment_ID;
				$csv_row[] = $item->comment_post_ID;
				$csv_row[] = $item->rating;
				$csv_row[] = $item->comment_content;
				$csv_row[] = $item->comment_date;
				$csv_row[] = $item->comment_approved;
				$csv_row[] = $item->user_id;
				$csv_row[] = $item->comment_author;
				$csv_row[] = $item->comment_author_email;
				$csv_row[] = $item->comment_author_url;
				$csv_row[] = $item->comment_author_IP;
				$csv_row[] = $item->post_type;
				$csv_row[] = $item->city;
				$csv_row[] = $item->region;
				$csv_row[] = $item->country;
				$csv_row[] = $item->latitude;
				$csv_row[] = $item->longitude;

				$csv_rows[] = $csv_row;
			}
		}

		return $csv_rows;
	}

	/**
	 * Prepare export reviews.
	 */
	public static function prepare_export_reviews() {
		$locale = self::switch_locale( 'all' );

		add_filter( 'comments_clauses', array( __CLASS__ , 'filter_reviews' ), 10, 2);

		$comment_args = self::get_comment_args( true );
		$comment_query = new WP_Comment_Query();

		$json = array();
		$json['total'] = (int)$comment_query->query( $comment_args );

		self::restore_locale( $locale );

		return $json;
	}

	/**
	 * Set filter reviews.
	 */
	public static function export_reviews() {
		global $wp_filesystem;

		$filters 		= ! empty( $_REQUEST['gd_imex'] ) && is_array( $_REQUEST['gd_imex'] ) ? $_REQUEST['gd_imex'] : null;
		$nonce          = isset( $_REQUEST['_nonce'] ) ? $_REQUEST['_nonce'] : null;
		$count 			= isset( $_REQUEST['_c'] ) ? absint( $_REQUEST['_c'] ) : 0;
		$chunk_per_page = !empty( $_REQUEST['_n'] ) ? absint( $_REQUEST['_n'] ) : 5000;
		$chunk_page_no  = isset( $_REQUEST['_p'] ) ? absint( $_REQUEST['_p'] ) : 1;
		$csv_file_dir   = self::import_export_cache_path( false );

		$locale = self::switch_locale( 'all' );

		$file_name = 'geodir_reviews_' . date( 'dmyHi' );

		if ( ! empty( $filters ) && ! empty( $filters['start_date'] ) && ! empty( $filters['end_date'] ) ) {
			$file_name = 'geodir_reviews_' . date_i18n( 'dmy', strtotime( $filters['start_date'] ) ) . '_' . date_i18n( 'dmy', strtotime( $filters['end_date'] ) );
		}

		$file_url_base  = self::import_export_cache_path() . '/';
		$file_url       = $file_url_base . $file_name . '.csv';
		$file_path      = $csv_file_dir . '/' . $file_name . '.csv';
		$file_path_temp = $csv_file_dir . '/geodir_reviews_' . $nonce . '.csv';

		$chunk_file_paths = array();

		if ( isset( $_REQUEST['_st'] ) ) {
			$line_count = (int) self::file_line_count( $file_path_temp );
			$percentage = count( $count ) > 0 && $line_count > 0 ? ceil( $line_count / $count ) * 100 : 0;
			$percentage = min( $percentage, 100 );

			$json['percentage'] = $percentage;

			self::restore_locale( $locale );

			return $json;
		} else {
			if ( ! $count > 0 ) {
				$json['error'] = __( 'No records to export.', 'geodirectory' );
			} else {
				add_filter( 'comments_clauses', array( __CLASS__ , 'filter_reviews' ), 10, 2);

				$total = $count;
				if ( $chunk_per_page > $count ) {
					$chunk_per_page = $count;
				}
				$chunk_total_pages = ceil( $total / $chunk_per_page );

				$j      = $chunk_page_no;
				$rows 	= self::get_reviews( $chunk_per_page, $j );

				$per_page = 500;
				if ( $per_page > $chunk_per_page ) {
					$per_page = $chunk_per_page;
				}
				$total_pages = ceil( $chunk_per_page / $per_page );

				for ( $i = 0; $i <= $total_pages; $i ++ ) {
					$save_rows = array_slice( $rows, ( $i * $per_page ), $per_page );

					$clear = $i == 0 ? true : false;
					self::save_csv_data( $file_path_temp, $save_rows, $clear );
				}

				if ( $wp_filesystem->exists( $file_path_temp ) ) {
					$chunk_page_no   = $chunk_total_pages > 1 ? '_' . $j : '';
					$chunk_file_name = $file_name . $chunk_page_no . '_' . substr( geodir_rand_hash(), 0, 8 ) . '.csv';
					$file_path       = $csv_file_dir . '/' . $chunk_file_name;
					$wp_filesystem->move( $file_path_temp, $file_path, true );

					$file_url           = $file_url_base . $chunk_file_name;
					$chunk_file_paths[] = array(
						'i' => $j . '.',
						'u' => $file_url,
						's' => size_format( filesize( $file_path ), 2 )
					);
				}

				if ( ! empty( $chunk_file_paths ) ) {
					$json['total'] = $count;
					$json['files'] = $chunk_file_paths;
				} else {
					$json['error'] = __( 'ERROR: Could not create csv file. This is usually due to inconsistent file permissions.', 'geodirectory' );
				}
			}
		}

		self::restore_locale( $locale );

		return $json;
	}

	/**
	 * Import reviews.
	 *
	 * @return array|string
	 */
	public static function import_reviews() {
		global $user_ID;
		$limit     = isset( $_POST['limit'] ) && $_POST['limit'] ? (int) $_POST['limit'] : 1;
		$processed = isset( $_POST['processed'] ) ? (int) $_POST['processed'] : 0;

		$processed ++;
		$rows = self::get_csv_rows( $processed, $limit );

		if ( ! empty( $rows ) ) {
			$created = 0;
			$updated = 0;
			$skipped = 0;
			$invalid = 0;
			$images  = 0;

			$update_or_skip = isset( $_POST['_ch'] ) && $_POST['_ch'] == 'update' ? 'update' : 'skip';
			$log_error = __( 'GD IMPORT REVIEW [ROW %d]:', 'geodirectory' );

			foreach ( $rows as $i => $row ) {
				$line_no = $processed + $i + 1;
				$line_error = wp_sprintf( $log_error, $line_no );
				$row = self::validate_review( $row );

				if ( empty( $row ) ) {
					geodir_error_log( $line_error . ' ' . __( 'data is empty.', 'geodirectory' ) );
					$invalid++;
					continue;
				}

				if ( $update_or_skip == 'skip' && isset( $row['comment_ID'] ) && $row['comment_ID'] ) {
					$skipped++;
					continue;
				}

				$valid = true;
				if ( empty( $row['comment_content'] ) ) {
					$valid = false;
					geodir_error_log( $line_error . ' ' . __( 'invalid comment content.', 'geodirectory' ) );
				}
				if ( !( ! empty( $row['comment_post_ID'] ) && geodir_is_gd_post_type( get_post_type( $row['comment_post_ID'] ) ) ) ) {
					$valid = false;
					geodir_error_log( $line_error . ' ' . __( 'invalid comment post ID.', 'geodirectory' ) );
				}
				if ( empty( $row['user_id'] ) && ( empty( $row['comment_author'] ) || empty( $row['comment_author_email'] ) ) ) {
					$valid = false;
					geodir_error_log( $line_error . ' ' . __( 'invalid user details(user id or author name, author email).', 'geodirectory' ) );
				}
				if ( empty( $row['rating'] ) ) {
					$valid = false;
					geodir_error_log( $line_error . ' ' . __( 'invalid rating.', 'geodirectory' ) );
				}

				if ( ! $valid ) {
					$invalid++;
					continue;
				}

				$user_ID = $row['user_id'];
				$_REQUEST['geodir_overallrating'] = $row['rating'];

				do_action( 'geodir_pre_import_review_data', $row );

				$success = false;

				// update
				if ( ! empty( $row['comment_ID'] ) ) {
					$save_id = wp_update_comment( $row );

					if ( $save_id !== false ) { // updated
						$save_id = $row['comment_ID'];
						GeoDir_Comments::edit_comment( $save_id );

						$updated++;
					} else { // error
						$invalid++;
						geodir_error_log( $line_error . ' ' . __( 'invalid data.', 'geodirectory' ) );
					}

				// insert
				} else {
					if ( isset( $row['comment_ID'] ) ) {
						unset( $row['comment_ID'] );
					}

					$save_id = wp_insert_comment( $row );

					if ( ! is_wp_error( $save_id ) && $save_id > 0 ) { // inserted
						GeoDir_Comments::save_rating( $save_id );

						$created++;
					} else { // error
						$invalid++;
						geodir_error_log( $line_error . ' ' . __( 'invalid data.', 'geodirectory' ) );
					}
				}
			}

		} else {
			return new WP_Error( 'gd-csv-empty', __( "No data found in csv file.", "geodirectory" ) );
		}

		return array(
			"processed" => $processed,
			"created"   => $created,
			"updated"   => $updated,
			"skipped"   => $skipped,
			"invalid"   => $invalid,
			"images"    => $images,
			"ID"        => 0,
		);
	}

	/**
	 * Set the file type of the given file.
	 *
	 * @param array  $wp_check_filetype_and_ext File data array containing 'ext', 'type', and
	 *                                          'proper_filename' keys.
	 * @param string $file                      Full path to the file.
	 * @param string $filename                  The name of the file (may differ from $file due to
	 *                                          $file being in a tmp directory).
	 * @param array  $mimes                     Key is the file extension with value as the mime type.
	 * @return array Filtered file type data.
	 */
	public static function set_filetype_and_ext( $data, $file, $filename, $mimes ) {
		$wp_filetype = wp_check_filetype( $filename, $mimes );

		if ( empty( $wp_filetype['type'] ) ) {
			return $data;
		}

		$ext = $wp_filetype['ext'];
		$type = $wp_filetype['type'];
		$proper_filename = $data['proper_filename'];

		return compact( 'ext', 'type', 'proper_filename' );
	}

	public static function generate_attachment_id( $image_url ) {
		if ( empty( $image_url ) ) {
			return '';
		}

		$image_url = str_replace( 'geodirectory-assets/', 'assets/', $image_url );
		$image_url = str_replace( 'geodirectory-functions/map-functions/icons', 'assets/images', $image_url );

		$upload = GeoDir_Media::upload_image_from_url( $image_url );

		if ( ! empty( $upload ) && ! is_wp_error( $upload ) && ! empty( $upload['file'] ) ) {
			$attachment_id = GeoDir_Media::set_uploaded_image_as_attachment( $upload );

			if ( ! empty( $attachment_id ) && ! is_wp_error( $attachment_id ) ) {
				return $attachment_id;
			}
		}

		return false;
	}
}
