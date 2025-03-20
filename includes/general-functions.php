<?php
/**
 * Plugin general functions
 *
 * @since   1.0.0
 * @package GeoDirectory
 */

/**
 * Get ajax url.
 *
 * @since 2.0.0
 *
 * @return string
 */
function geodir_get_ajax_url() {
	return admin_url( 'admin-ajax.php' );
}

/**
 * Return the plugin url.
 *
 * Return the plugin folder url WITHOUT TRAILING SLASH.
 *
 * @since   1.0.0
 * @since   1.6.18 Fix: GD Booster causes problem when used http in urls on SSL enabled site.
 * @package GeoDirectory
 * @return string example url eg: http://wpgeo.directory/wp-content/plugins/geodirectory
 */
function geodir_plugin_url() {
	return GEODIRECTORY_PLUGIN_URL;
}

/**
 * Return the plugin path.
 *
 * Return the plugin folder path WITHOUT TRAILING SLASH.
 *
 * @since   1.0.0
 * @package GeoDirectory
 * @return string example url eg: /home/geo/public_html/wp-content/plugins/geodirectory
 */
function geodir_plugin_path() {
	if ( defined( 'GEODIRECTORY_PLUGIN_DIR' ) ) {
		return GEODIRECTORY_PLUGIN_DIR;
	}

	if ( defined( 'GD_TESTING_MODE' ) && GD_TESTING_MODE ) {
		return dirname( dirname( __FILE__ ) );
	} else {
		return WP_PLUGIN_DIR . "/" . plugin_basename( dirname( dirname( __FILE__ ) ) );
	}
}

/**
 * Check if a GeoDirectory addon plugin is active.
 *
 * @since   1.0.0
 * @package GeoDirectory
 *
 * @param string $plugin plugin uri.
 *
 * @return bool true or false.
 * @todo    check if this is faster than normal WP check and remove if not.
 */
function geodir_is_plugin_active( $plugin ) {
	$active_plugins = get_option( 'active_plugins' );
	foreach ( $active_plugins as $key => $active_plugin ) {
		if ( strstr( $active_plugin, $plugin ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Return a formatted link with parameters.
 *
 * Returns a link with new parameters and currently used parameters regardless of ? or & in the $url parameter.
 *
 * @since   1.0.0
 * @package GeoDirectory
 *
 * @param string $url The main url to be used.
 * @param array $params The arguments array.
 * @param bool $use_existing_arguments Do you want to use existing arguments? Default: false.
 *
 * @return string Formatted link.
 */
function geodir_getlink( $url, $params = array(), $use_existing_arguments = false ) {
	if ( $use_existing_arguments ) {
		$params = $params + $_GET;
	}
	if ( ! $params ) {
		return $url;
	}
	$link = $url;
	if ( strpos( $link, '?' ) === false ) {
		$link .= '?';
	} //If there is no '?' add one at the end
	elseif ( strpos( $link, '//maps.google.com/maps/api/js?language=' ) ) {
		$link .= '&amp;';
	} //If there is no '&' at the END, add one.
	elseif ( ! preg_match( '/(\?|\&(amp;)?)$/', $link ) ) {
		$link .= '&';
	} //If there is no '&' at the END, add one.

	$params_arr = array();
	foreach ( $params as $key => $value ) {
		if ( gettype( $value ) == 'array' ) { //Handle array data properly
			foreach ( $value as $val ) {
				$params_arr[] = $key . '[]=' . urlencode( $val );
			}
		} else {
			$params_arr[] = $key . '=' . urlencode( $value );
		}
	}
	$link .= implode( '&', $params_arr );

	return $link;
}

/**
 * Returns add listing page url.
 *
 * @since 2.0.0.97
 * @since 2.3.18 Added $post_id parameter.
 *
 * @param string $post_type The post type.
 * @param int $post_id The post ID. DEfault 0.
 * @return string Add listing page url.
 */
function geodir_add_listing_page_url( $post_type = '', $post_id = 0 ) {
	global $wpdb;

	if ( empty( $post_type ) && ! empty( $post_id ) ) {
		$post_type = get_post_type( $post_id );
	}

	$page_id = geodir_add_listing_page_id( $post_type );

	$base_link = get_page_link( $page_id );

	if ( get_option( 'permalink_structure' ) != '' ) {
		$page_link = trailingslashit( $base_link ) . geodir_cpt_permalink_rewrite_slug( $post_type ) . '/';

		if ( $post_id > 0 ) {
			$page_link .= $post_id . '/';
		}
	} else {
		$args = array();
		$args['listing_type'] = $post_type;

		if ( $post_id > 0 ) {
			$args['pid'] = $post_id;
		}

		$page_link = add_query_arg( $args, $base_link );
	}

	return apply_filters( 'geodir_add_listing_page_url', $page_link, $post_type, $post_id );
}

/**
 * Get the current page URL.
 *
 * @since   1.0.0
 * @package GeoDirectory
 * @since   1.4.2 Removed the port number from the URL if port 80 is not being used.
 * @since   1.6.9 Fix $_SERVER['SERVER_NAME'] with nginx server.
 * @return string The current URL.
 */
function geodir_curPageURL() {
	$pageURL = is_ssl() ? 'https://' : 'http://';

	// Host
	if ( isset( $_SERVER['HTTP_HOST'] ) ) {
		$host = wp_unslash( $_SERVER['HTTP_HOST'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	} else {
		$host = wp_parse_url( home_url(), PHP_URL_HOST );
	}

	/*
	 * Since we are assigning the URI from the server variables, we first need
	 * to determine if we are running on apache or IIS.  If PHP_SELF and REQUEST_URI
	 * are present, we will assume we are running on apache.
	 */
	if ( ! empty( $_SERVER['PHP_SELF'] ) && ! empty( $_SERVER['REQUEST_URI'] ) ) {
		// To build the entire URI we need to prepend the protocol, and the http host
		// to the URI string.
		$pageURL .= $host . $_SERVER['REQUEST_URI'];
	} else {
		/*
		 * Since we do not have REQUEST_URI to work with, we will assume we are
		 * running on IIS and will therefore need to work some magic with the SCRIPT_NAME and
		 * QUERY_STRING environment variables.
		 *
		 * IIS uses the SCRIPT_NAME variable instead of a REQUEST_URI variable... thanks, MS
		 */
		$pageURL .= $host . $_SERVER['SCRIPT_NAME'];

		// If the query string exists append it to the URI string
		if ( isset( $_SERVER['QUERY_STRING'] ) && ! empty( $_SERVER['QUERY_STRING'] ) ) {
			$pageURL .= '?' . $_SERVER['QUERY_STRING'];
		}
	}

	/**
	 * Filter the current page URL returned by function geodir_curPageURL().
	 *
	 * @since 1.4.1
	 *
	 * @param string $pageURL The URL of the current page.
	 */
	return apply_filters( 'geodir_curPageURL', $pageURL );
}

/**
 * Get Week Days list.
 *
 * @since   1.0.0
 * @package GeoDirectory
 * @return array Week days.
 */
function geodir_get_weekday() {
	return array(
		__( 'Sunday' ),
		__( 'Monday' ),
		__( 'Tuesday' ),
		__( 'Wednesday' ),
		__( 'Thursday' ),
		__( 'Friday' ),
		__( 'Saturday' )
	);
}

/**
 * Get Weeks lists.
 *
 * @since   1.0.0
 * @package GeoDirectory
 * @return array Weeks.
 */
function geodir_get_weeks() {
	return array(
		__( 'First', 'geodirectory' ),
		__( 'Second', 'geodirectory' ),
		__( 'Third', 'geodirectory' ),
		__( 'Fourth', 'geodirectory' ),
		__( 'Last', 'geodirectory' )
	);
}

/**
 * Check that page is.
 *
 * @since 1.0.0
 *
 * @global object $wp_query WordPress Query object.
 * @global object $gd_loop_wp_query WordPress Query object from GD loop.
 *
 * @param string $gdpage The page type.
 *
 * @return bool If valid returns true. Otherwise false.
 */
function geodir_is_page( $gdpage = '' ) {
	global $wp_query, $gd_loop_wp_query;

	// Backup $wp_query;
	if ( ! empty( $gd_loop_wp_query ) ) {
		$backup_wp_query = $wp_query;
		$wp_query = $gd_loop_wp_query;
	}

	$is_page = geodir_check_page( $gdpage );

	// Release $wp_query;
	if ( ! empty( $gd_loop_wp_query ) && isset( $backup_wp_query ) ) {
		$wp_query = $backup_wp_query;
	}

	return $is_page;
}

/**
 * Check that page is.
 *
 * @since 2.1.0.6
 *
 * @global object $wp_query WordPress Query object.
 * @global object $post The current post object.
 *
 * @param string $gdpage The page type.
 *
 * @return bool If valid returns true. Otherwise false.
 */
function geodir_check_page( $gdpage = '' ) {
	global $wp_query, $post, $wp;

	if ( empty( $wp_query ) ) {
		return false;
	}

	//if(!is_admin()):
	$page_id = '';// get_query_var( 'page_id' ) ? get_query_var( 'page_id' ) : '';

	if ( empty( $page_id ) && isset( $wp_query->is_page ) && $wp_query->is_page && isset( $wp_query->queried_object_id ) && $wp_query->queried_object_id ) {
		$page_id = $wp_query->queried_object_id;
	}

	//echo 'xxxx'.$page_id;
	switch ( $gdpage ):
		case 'add-listing':

			if ( is_page() && geodir_is_page_id( $page_id, 'add' ) ) {
				return true;
			} elseif ( is_page() && isset( $post->post_content ) && has_shortcode( $post->post_content, 'gd_add_listing' ) ) {
				return true;
			}

			break;
		case 'edit-listing':
			if ( ! empty( $_REQUEST['pid'] ) && geodir_is_gd_post_type( get_post_type( absint( $_REQUEST['pid'] ) ) ) && geodir_is_page( 'add-listing' ) ) {
				return true;
			}
			break;
		case 'preview':
			if ( ( is_page() && $page_id === geodir_preview_page_id() ) && isset( $_REQUEST['listing_type'] )
			     && in_array( $_REQUEST['listing_type'], geodir_get_posttypes() )
			) {
				return true;
			}
			break;
		case 'listing-success':// @deprecated
			if ( is_page() && $page_id === geodir_success_page_id() ) {
				return true;
			}
			break;
		case 'single':
		case 'detail': // @deprecated
			$post_type = get_query_var( 'post_type' );
			if ( is_array( $post_type ) ) {
				$post_type = reset( $post_type );
			}
			if ( is_single() && in_array( $post_type, geodir_get_posttypes() ) && ! is_attachment() ) {
				return true;
			}
			break;
		case 'post_type':
		case 'pt': // @deprecated
			$post_type = get_query_var( 'post_type' );
			if ( is_array( $post_type ) ) {
				$post_type = reset( $post_type );
			}
			if ( is_post_type_archive() && in_array( $post_type, geodir_get_posttypes() ) && ! is_tax() ) {
				return true;
			}

			break;
		case 'archive':
		case 'listing':// @deprecated
			if ( is_tax() && geodir_get_taxonomy_posttype() ) {
				global $current_term, $taxonomy, $term;

				return true;
			}
			$post_type = get_query_var( 'post_type' );
			if ( is_array( $post_type ) ) {
				$post_type = reset( $post_type );
			}
			if ( is_post_type_archive() && in_array( $post_type, geodir_get_posttypes() ) ) {
				return true;
			}

			break;
		case 'location':
			if ( is_page() && $page_id == geodir_location_page_id() ) {
				return true;
			}
			break;
		case 'author':
			if ( is_author() ) {
				return true;
			}

			if ( function_exists( 'bp_loggedin_user_id' ) && function_exists( 'bp_displayed_user_id' ) && function_exists('bp_is_current_component') && $my_id = (int) bp_loggedin_user_id() ) {
				if ( ( (bool) bp_is_current_component( 'listings' ) || (bool) bp_is_current_component( 'favorites' ) ) && $my_id > 0 && $my_id == (int) bp_displayed_user_id() ) {
					return true;
				}
			}
			break;
		case 'search':
			if ( isset( $_REQUEST['geodir_search'] ) || ( is_page() && $page_id == geodir_search_page_id() ) ) {
				return true;
			}elseif(isset($wp->query_vars['pagename']) && $page = get_page_by_path( $wp->query_vars['pagename'] )  ){
				if( isset($page->ID) && $page->ID==geodir_search_page_id()){
					return true;
				}
			}
			break;
		default:
			return false;
			break;

	endswitch;

	//endif;

	return false;
}

/**
 * Check that page id is.
 *
 * @since   2.0.0.97
 *
 * @param int $page_id The page id.
 * @param string $gdpage The page type.
 *
 * @return bool If valid returns true. Otherwise false.
 */
function geodir_is_page_id( $page_id, $gdpage ) {
	global $wp_query, $post, $wp;

	if ( empty( $page_id ) || empty( $gdpage ) ) {
		return false;
	}

	switch ( $gdpage ) {
		case 'add':
		case 'add-listing':
			if ( $page_id == geodir_add_listing_page_id() ) {
				return true;
			} elseif ( geodir_is_cpt_template_page( $page_id, 'add' ) ) {
				return true;
			}
			break;
		default:
			return false;
			break;
	}

	return false;
}

/**
 * Sets a key and value in $wp object if the current page is a geodir page.
 *
 * @since   1.0.0
 * @since   1.5.4 Added check for new style GD homepage.
 * @since   1.5.6 Added check for GD invoices and GD checkout page.
 * @package GeoDirectory
 *
 * @param object $wp WordPress object.
 */
function geodir_set_is_geodir_page( $wp ) {
	if ( ! is_admin() ) {

		if ( empty( $wp->query_vars ) || ! array_diff( array_keys( $wp->query_vars ), array(
				'preview',
				'page',
				'paged',
				'cpage'
			) )
		) {
			if ( geodir_is_page( 'home' ) ) {
				$wp->query_vars['gd_is_geodir_page'] = true;
			}
		}

		if ( ! isset( $wp->query_vars['gd_is_geodir_page'] ) && isset( $wp->query_vars['page_id'] ) ) {
			if (
				geodir_is_page_id( $wp->query_vars['page_id'], 'add' )
				|| $wp->query_vars['page_id'] == geodir_preview_page_id()
				|| $wp->query_vars['page_id'] == geodir_success_page_id()
				|| $wp->query_vars['page_id'] == geodir_location_page_id()
			) {
				$wp->query_vars['gd_is_geodir_page'] = true;
			}
		}

		if ( ! isset( $wp->query_vars['gd_is_geodir_page'] ) && isset( $wp->query_vars['pagename'] ) ) {
			$page = get_page_by_path( $wp->query_vars['pagename'] );

			if ( ! empty( $page ) && (
					geodir_is_page_id( $page->ID, 'add' )
					|| $page->ID == geodir_preview_page_id()
					|| $page->ID == geodir_success_page_id()
					|| $page->ID == geodir_location_page_id()
				)
			) {
				$wp->query_vars['gd_is_geodir_page'] = true;
			}
		}


		if ( ! isset( $wp->query_vars['gd_is_geodir_page'] ) && isset( $wp->query_vars['post_type'] ) && $wp->query_vars['post_type'] != '' ) {
			$requested_post_type = $wp->query_vars['post_type'];
			// check if this post type is geodirectory post types
			$post_type_array = geodir_get_posttypes();
			if ( in_array( $requested_post_type, $post_type_array ) ) {
				$wp->query_vars['gd_is_geodir_page'] = true;
			}
		}

		if ( ! isset( $wp->query_vars['gd_is_geodir_page'] ) ) {
			$geodir_taxonomis = geodir_get_taxonomies( '', true );
			if ( ! empty( $geodir_taxonomis ) ) {
				foreach ( $geodir_taxonomis as $taxonomy ) {
					if ( array_key_exists( $taxonomy, $wp->query_vars ) ) {
						$wp->query_vars['gd_is_geodir_page'] = true;
						break;
					}
				}
			}

		}

		if ( ! isset( $wp->query_vars['gd_is_geodir_page'] ) && isset( $wp->query_vars['author_name'] ) && isset( $_REQUEST['geodir_dashbord'] ) ) {
			$wp->query_vars['gd_is_geodir_page'] = true;
		}


		if ( ! isset( $wp->query_vars['gd_is_geodir_page'] ) && isset( $_REQUEST['geodir_search'] ) ) {
			$wp->query_vars['gd_is_geodir_page'] = true;
		}

	} // end of is admin
}

/**
 * Checks whether the current page is a GD page or not.
 *
 * @since   1.0.0
 * @package GeoDirectory
 * @global object $wp WordPress object.
 * @return bool If the page is GD page returns true. Otherwise false.
 */
function geodir_is_geodir_page() {
	global $wp;
	if ( isset( $wp->query_vars['gd_is_geodir_page'] ) && $wp->query_vars['gd_is_geodir_page'] ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Checks whether a page id is a GD page or not.
 *
 * @since   2.0.0.22
 * @package GeoDirectory

 * @return bool If the page is GD page returns true. Otherwise false.
 */
function geodir_is_geodir_page_id($id) {
	global $geodirectory;
	if(!empty($geodirectory->settings['page_add']) && $geodirectory->settings['page_add'] == $id ){
		$is_geodir_page = true;
	}elseif(!empty($geodirectory->settings['page_location']) && $geodirectory->settings['page_location'] == $id ){
		$is_geodir_page = true;
	}elseif(!empty($geodirectory->settings['page_search']) && $geodirectory->settings['page_search'] == $id ){
		$is_geodir_page = true;
	}elseif(!empty($geodirectory->settings['page_terms_conditions']) && $geodirectory->settings['page_terms_conditions'] == $id ){
		$is_geodir_page = true;
	}elseif(!empty($geodirectory->settings['page_details']) && $geodirectory->settings['page_details'] == $id ){
		$is_geodir_page = true;
	}elseif(!empty($geodirectory->settings['page_archive']) && $geodirectory->settings['page_archive'] == $id ){
		$is_geodir_page = true;
	}elseif(!empty($geodirectory->settings['page_archive_item']) && $geodirectory->settings['page_archive_item'] == $id ){
		$is_geodir_page = true;
	}elseif( geodir_is_cpt_template_page( $id ) ){
		$is_geodir_page = true;
	}else{
		$is_geodir_page = false;
	}

	/**
	 * Filter to check whether a page id is a GD page or not.
	 *
	 * @since 2.0.0
	 *
	 * @param bool $is_geodir_page If the page is GD page returns true. Otherwise false..
	 * @param int $id The page ID.
	 */
	$is_geodir_page = apply_filters( 'geodir_is_geodir_page_id', $is_geodir_page, $id );

	return $is_geodir_page;
}

if ( ! function_exists( 'geodir_get_imagesize' ) ) {
	/**
	 * Get image size using the size key .
	 *
	 * @since   1.0.0
	 * @package GeoDirectory
	 *
	 * @param string $size The image size key.
	 *
	 * @return array|mixed|void|WP_Error If valid returns image size. Else returns error.
	 */
	function geodir_get_imagesize( $size = '' ) {

		$imagesizes = array(
			'list-thumb'   => array( 'w' => 283, 'h' => 188 ),
			'thumbnail'    => array( 'w' => 125, 'h' => 125 ),
			'widget-thumb' => array( 'w' => 50, 'h' => 50 ),
			'slider-thumb' => array( 'w' => 100, 'h' => 100 )
		);

		/**
		 * Filter the image sizes array.
		 *
		 * @since 1.0.0
		 *
		 * @param array $imagesizes Image size array.
		 */
		$imagesizes = apply_filters( 'geodir_imagesizes', $imagesizes );

		if ( ! empty( $size ) && array_key_exists( $size, $imagesizes ) ) {
			/**
			 * Filters image size of the passed key.
			 *
			 * @since 1.0.0
			 *
			 * @param array $imagesizes [$size] Image size array of the passed key.
			 */
			return apply_filters( 'geodir_get_imagesize_' . $size, $imagesizes[ $size ] );

		} elseif ( ! empty( $size ) ) {

			return new WP_Error( 'geodir_no_imagesize', __( "Given image size is not valid", 'geodirectory' ) );

		}

		return $imagesizes;
	}
}

/**
 * Get an image size
 *
 * Variable is filtered by geodir_get_image_size_{image_size}
 */
/*
function geodir_get_image_size( $image_size ) {
	$return = '';
	switch ($image_size) :
		case "list_thumbnail_size" : $return = geodir_get_option('geodirectory_list_thumbnail_size'); break;
	endswitch;
	return apply_filters( 'geodir_get_image_size_'.$image_size, $return );
}
*/


/**
 * Creates random string.
 *
 * @since   1.0.0
 * @package GeoDirectory
 * @return string Random string.
 */
function createRandomString() {
	$chars = "abcdefghijkmlnopqrstuvwxyz1023456789";
	srand( (double) microtime() * 1000000 );
	$i       = 0;
	$rstring = '';
	while ( $i <= 25 ) {
		$num     = rand() % 33;
		$tmp     = substr( $chars, $num, 1 );
		$rstring = $rstring . $tmp;
		$i ++;
	}

	return $rstring;
}


/**
 * Calculates the distance radius.
 *
 * @since   1.0.0
 * @package GeoDirectory
 *
 * @param string $uom Measurement unit type.
 *
 * @return float The mean radius.
 */
function geodir_getDistanceRadius( $uom = 'km' ) {
//	Use Haversine formula to calculate the great circle distance between two points identified by longitude and latitude
	switch ( geodir_strtolower( $uom ) ):
		case 'km'    :
			$earthMeanRadius = 6371.009; // km
			break;
		case 'm'    :
		case 'meters'    :
			$earthMeanRadius = 6371.009 * 1000; // km
			break;
		case 'miles'    :
			$earthMeanRadius = 3958.761; // miles
			break;
		case 'yards'    :
		case 'yds'    :
			$earthMeanRadius = 3958.761 * 1760; // yards
			break;
		case 'feet'    :
		case 'ft'    :
			$earthMeanRadius = 3958.761 * 1760 * 3; // feet
			break;
		case 'nm'    :
			$earthMeanRadius = 3440.069; //  miles
			break;
		default:
			$earthMeanRadius = 3958.761; // miles
			break;
	endswitch;

	return geodir_sanitize_float( $earthMeanRadius );
}

function geodir_get_between_latlon( $lat, $lon, $dist = '', $unit = '' ) {
	global $wp;

	if ( $unit != 'km' && $unit != 'miles' ) {
		$unit = geodir_get_option( 'search_distance_long', 'miles' );
	}

	if ( ! $dist ) {
		if ( get_query_var( 'dist' ) ) {
			$dist = get_query_var('dist');
		} else if ( wp_doing_ajax() && ! empty( $wp->query_vars['dist'] ) ) {
			$dist = $wp->query_vars['dist'];
		} else {
			$dist = geodir_get_option( 'search_radius', 5 ); // seems to work in miles
			$unit = geodir_get_option( 'search_distance_long', 'miles' );
		}
	}

	$dist = geodir_sanitize_float( $dist ) > 0 ? geodir_sanitize_float( $dist ) : 5;

	// Convert distance to miles
	if ( $unit != 'miles' ) {
		$dist = $dist * 0.6213711922;
	}

	// sanatize just in case
	$lat = filter_var( $lat, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
	$lon = filter_var( $lon, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );

	$lat1 = $lat - ( $dist / 69 );
	$lat2 = $lat + ( $dist / 69 );
	$lon1 = $lon - ( $dist / abs( cos( deg2rad( $lat ) ) * 69 ) );
	$lon2 = $lon + ( $dist / abs( cos( deg2rad( $lat ) ) * 69 ) );

	$rlat1 = min( $lat1, $lat2 );
	$rlat2 = max( $lat1, $lat2 );
	$rlon1 = min( $lon1, $lon2 );
	$rlon2 = max( $lon1, $lon2 );

	if ( ! is_numeric( $rlat1 ) ) {
		$rlat1 = '';
	}
	if ( ! is_numeric( $rlat2 ) ) {
		$rlat2 = '';
	}
	if ( ! is_numeric( $rlon1 ) ) {
		$rlon1 = '';
	}
	if ( ! is_numeric( $rlon2 ) ) {
		$rlon2 = '';
	}

	return array(
		'lat1' => $rlat1,
		'lat2' => $rlat2,
		'lon1' => $rlon1,
		'lon2' => $rlon2,
	);
}



/**
 * Calculate the great circle distance between two points identified by longitude and latitude.
 *
 * @since   1.0.0
 * @package GeoDirectory
 *
 * @param array $point1 Latitude and Longitude of point 1.
 * @param array $point2 Latitude and Longitude of point 2.
 * @param string $uom Unit of measurement.
 *
 * @return float The distance.
 */
function geodir_calculateDistanceFromLatLong( $point1, $point2, $uom = 'km' ) {
//	Use Haversine formula to calculate the great circle distance between two points identified by longitude and latitude

	$earthMeanRadius = geodir_getDistanceRadius( $uom );

	$deltaLatitude  = deg2rad( geodir_sanitize_float( $point2['latitude'] ) - geodir_sanitize_float( $point1['latitude'] ) );
	$deltaLongitude = deg2rad( geodir_sanitize_float( $point2['longitude'] ) - geodir_sanitize_float( $point1['longitude'] ) );
	$a              = sin( $deltaLatitude / 2 ) * sin( $deltaLatitude / 2 ) +
	                  cos( deg2rad( geodir_sanitize_float( $point1['latitude'] ) ) ) * cos( deg2rad( geodir_sanitize_float( $point2['latitude'] ) ) ) *
	                  sin( $deltaLongitude / 2 ) * sin( $deltaLongitude / 2 );
	$c              = 2 * atan2( sqrt( $a ), sqrt( 1 - $a ) );
	$distance       = $earthMeanRadius * $c;

	return $distance;

}

/**
 * Get maximum file upload size.
 *
 * @since   1.0.0
 * @package GeoDirectory
 * @return string|void Max upload size.
 */
function geodir_max_upload_size() {
	$max_filesize = geodir_sanitize_float( geodir_get_option( 'upload_max_filesize', 2 ) );

	if ( $max_filesize > 0 && $max_filesize < 1 ) {
		$max_filesize = (int) ( $max_filesize * 1024 ) . 'kb';
	} else {
		$max_filesize = $max_filesize > 0 ? $max_filesize . 'mb' : '2mb';
	}

	/**
	 * Filter default image upload size limit.
	 *
	 * @since 1.0.0
	 *
	 * @param string $max_filesize Max file upload size. Ex. 10mb, 512kb.
	 */
	return apply_filters( 'geodir_default_image_upload_size_limit', $max_filesize );
}

/**
 * function to add class to body when multi post type is active.
 *
 * @since   1.0.0
 * @since   1.5.6 Add geodir-page class to body for all gd pages.
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @global object $wp_query WordPress Query object.
 *
 * @param array $classes Body CSS classes.
 *
 * @return array Modified Body CSS classes.
 */
function geodir_custom_posts_body_class( $classes ) {
	global $wpdb, $wp, $wp_query, $gd_post;

	$post_types = geodir_get_posttypes( 'object' );

	if ( ! empty( $post_types ) && count( (array) $post_types ) > 1 ) {
		$classes[] = 'geodir_custom_posts';
	}

	if ( geodir_is_geodir_page() ) {
		$classes[] = 'geodir-page';

		// Add post type to class.
		$_post_type = geodir_get_current_posttype();
		$classes[] = 'geodir-page-cpt-' . $_post_type;
	}

	$is_post_type = geodir_is_page( 'post_type' );
	$is_search = geodir_is_page( 'search' );

	if ( $is_search ) {
		$classes[] = 'geodir-page-search';
	}

	if ( $is_post_type || geodir_is_page( 'archive' ) || geodir_is_page( 'author' ) || $is_search ) {
		$post_type = geodir_get_current_posttype();

		if ( is_tax() ) {
			$classes[] = 'geodir-page-term';

			if ( $queried_object = get_queried_object() ) {
				if ( ! empty( $queried_object->term_id ) ) {
					$classes[] = 'geodir-page-term-' . (int) $queried_object->term_id;
				}

				if ( ! empty( $queried_object->taxonomy ) ) {
					$classes[] = 'geodir-page-' . sanitize_html_class( $queried_object->taxonomy );
				}
			}
		} else if ( $is_post_type ) {
			$classes[] = 'geodir-page-cpt';
		}

		$classes[] = 'geodir-archive';

		if ( isset( $post_types->{$post_type} ) && isset( $post_types->{$post_type}->disable_location ) && $post_types->{$post_type}->disable_location ) {
			$classes[] = 'geodir-archive-locationless';
		}

		// No results.
		if ( ! empty( $wp_query ) && empty( $wp_query->found_posts ) ) {
			$classes[] = 'geodir-no-results';
		}
	}

	if ( geodir_is_page( 'single' ) ) { // Single page
		if ( ! empty( $gd_post->default_category ) ) {
			$classes[] = 'geodir-post-cat-'.absint( $gd_post->default_category );
		}
		if ( ! empty( $gd_post->featured ) ){
			$classes[] = 'geodir-featured';
		}
		$classes[] = 'geodir-page-single';
	} elseif ( geodir_is_page( 'add-listing' ) ) { // Add listing page
		$classes[] = 'geodir-page-add';

		$post_type = ! empty( $_REQUEST['listing_type'] ) ? sanitize_text_field( $_REQUEST['listing_type'] ) : '';

		// Edit listing page
		if ( geodir_is_page( 'edit-listing' ) ) {
			$classes[] = 'geodir-page-edit';

			if ( ! empty( $_REQUEST['pid'] ) && ( $_post_type = get_post_type( absint( $_REQUEST['pid'] ) ) ) ) {
				$post_type = $_post_type;
			}
		}

		// Add/edit post type
		if ( $post_type ) {
			$classes[] = 'geodir-form-' . $post_type;
		}
	} elseif ( geodir_is_page( 'location' ) ) { // Location page
		$classes[] = 'geodir-page-location';
	}

	return $classes;
}

add_filter( 'body_class', 'geodir_custom_posts_body_class' ); // let's add a class to the body so we can style the new addition to the search

/**
 * Returns orderby SQL using the given query args.
 *
 * @since   1.0.0
 * @since   1.6.18 Allow order by custom field in widget listings results sorting.
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 *
 * @param array $deprecated The query array.
 *
 * @return string Orderby SQL.
 */
function geodir_widget_listings_get_order( $deprecated = '') {
	global $wpdb, $plugin_prefix, $gd_query_args_widgets;

	$query_args = $gd_query_args_widgets;
	if ( empty( $query_args ) || empty( $query_args['is_geodir_loop'] ) ) {
		return $wpdb->posts . ".post_date DESC, ";
	}

	$post_type = empty( $query_args['post_type'] ) ? 'gd_place' : $query_args['post_type'];

	$sort_by = ! empty( $query_args['order_by'] ) ? $query_args['order_by'] : '';

	$orderby = GeoDir_Query::sort_by_sql( $sort_by, $post_type );
	$orderby = GeoDir_Query::sort_by_children( $orderby, $sort_by, $post_type );

	// Add secondary sort for nearest filter.
	if ( ! empty( $query_args['nearby_gps'] ) && strpos( $orderby, 'distance ' ) === false ) {
		if ( ! empty( $orderby ) ) {
			$orderby .= ", ";
		}
		$orderby .= "distance ASC";
	}

	return $orderby;
}

/**
 * Retrieve listings/count using requested filter parameters.
 *
 * @since   1.0.0
 * @package GeoDirectory
 * @since   1.4.2 New parameter $count_only added
 * @since   1.6.11 FIXED: GD listings query returns wrong total when category has sub categories.
 * @since   1.6.18 Allow order by custom field in widget listings results sorting.
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 * @global string $table_prefix WordPress Database Table prefix.
 *
 * @param array $query_args The query array.
 * @param  int|bool $count_only If true returns listings count only, otherwise returns array
 *
 * @return mixed Result object.
 */
function geodir_get_widget_listings( $query_args = array(), $count_only = false ) {
	global $wp,$wpdb, $plugin_prefix, $table_prefix,$geodirectory;

	$post_type = empty( $query_args['post_type'] ) ? 'gd_place' : $query_args['post_type'];
	$table     = geodir_db_cpt_table( $post_type );

	// check if this is a GPS filtered query
	$query_args['supports_location'] = $post_type  && GeoDir_Post_types::supports( $post_type , 'location' );

	if ( ! empty( $query_args['supports_location'] ) && ( $latlon = $geodirectory->location->get_latlon() ) && ! empty( $query_args['gd_location'] ) && function_exists( 'geodir_default_location_where' )  ) {
		$query_args['is_gps_query'] = true;
		$query_args['order_by'] = 'distance_asc';
	}

	$GLOBALS['gd_query_args_widgets'] = $query_args;
	$gd_query_args_widgets            = $query_args;
	$cache_group = 'widget_listings_' . $post_type;

	$fields = $wpdb->posts . ".*, " . $table . ".*";
	/**
	 * Filter widget listing fields string part that is being used for query.
	 *
	 * @since 1.0.0
	 *
	 * @param string $fields Fields string.
	 * @param string $table Table name.
	 * @param string $post_type Post type.
	 */
	$fields = apply_filters( 'geodir_filter_widget_listings_fields', $fields, $table, $post_type );

	$join = $join_original = "INNER JOIN " . $table . " ON (" . $table . ".post_id = " . $wpdb->posts . ".ID)";



	/**
	 * Filter widget listing join clause string part that is being used for query.
	 *
	 * @since 1.0.0
	 *
	 * @param string $join Join clause string.
	 * @param string $post_type Post type.
	 */
	$join = apply_filters( 'geodir_filter_widget_listings_join', $join, $post_type );

	// Show posts with some post statuses on author page.
	if ( ! empty( $query_args['post_author'] ) && $query_args['post_author'] > 0 && ! empty( $query_args['is_gd_author'] ) && $query_args['post_author'] == (int) get_current_user_id() ) {
		$context = 'widget-listings-author';
	} else {
		$context = 'widget-listings';
	}

	$statuses = geodir_get_post_stati( $context, $query_args );


	if ( count( $statuses ) > 1 ) {
		$where = "AND {$wpdb->posts}.post_status IN( '" . implode( "', '", $statuses ) . "' )";
	} else {
		$where = "AND {$wpdb->posts}.post_status = '{$statuses[0]}'";
	}

	$where .= " AND " . $wpdb->posts . ".post_type = '" . $post_type . "'";

	// in / not in
	if ( ! empty( $query_args['post__in'] ) ) {
		if ( ! is_array( $query_args['post__in'] ) ) {
			$query_args['post__in'] = explode( ",", $query_args['post__in'] ); // convert to array if not an array
		}
		$post__in = implode( ',', array_map( 'absint', $query_args['post__in'] ) );
		$where .= " AND {$wpdb->posts}.ID IN ($post__in)";
	} elseif ( ! empty( $query_args['post__not_in'] ) ) {
		if ( ! is_array( $query_args['post__not_in'] ) ) {
			$query_args['post__not_in'] = explode( ",",$query_args['post__not_in'] ); // convert to array if not an array
		}
		$post__not_in = implode( ',',  array_map( 'absint', $query_args['post__not_in'] ) );
		$where .= " AND {$wpdb->posts}.ID NOT IN ($post__not_in)";
	}

	/**
	 * Filter widget listing where clause string part that is being used for query.
	 *
	 * @since 1.0.0
	 *
	 * @param string $where Where clause string.
	 * @param string $post_type Post type.
	 */
	$where = apply_filters( 'geodir_filter_widget_listings_where', $where, $post_type );
	$where = $where != '' ? " WHERE 1=1 " . $where : '';

	/**
	 * @todo is this needed? faster without (commented out by stiofan 2024-02-16, if it breaks anything we can add it back here with a comment).
	 * UPDATE: this is required when joining the taxonomy table for filtering, maybe we can look into not joining the table and just filtering by our custom fields?
	 * For now we add it back if joining another table.
	 */
	$groupby = $join !== $join_original ? " GROUP BY $wpdb->posts.ID " : '';

	/**
	 * Filter widget listing groupby clause string part that is being used for query.
	 *
	 * @since 1.0.0
	 *
	 * @param string $groupby Group by clause string.
	 * @param string $post_type Post type.
	 */
	$groupby = apply_filters( 'geodir_filter_widget_listings_groupby', $groupby, $post_type );

	if ( $count_only ) {
		$fields = "COUNT(DISTINCT " . $wpdb->posts . ".ID) AS total";
		/**
		 * Filter widget listing fields string part that is being used to count results.
		 *
		 * @since 2.0.0.71
		 *
		 * @param string $fields Fields string.
		 * @param string $table Table name.
		 * @param string $post_type Post type.
		 */
		$fields = apply_filters( 'geodir_filter_widget_listings_count_fields', $fields, $table, $post_type );

		$sql  = "SELECT " . $fields . " FROM " . $wpdb->posts . "
			" . $join . "
			" . $where;

		/**
		 * Filters the listings count SQL query before sending.
		 *
		 * @since 2.0.0.71
		 *
		 * @param string $sql The SQL query.
		 * @param string $post_type The post type.
		 */
		$sql = apply_filters( 'geodir_filter_widget_listings_count_sql', $sql, $post_type );
		$sql = str_replace( "WHERE 1=1 AND ", "WHERE ", $sql );

		$cache_key = wp_hash( $sql );

		$rows = geodir_cache_get( $cache_key, $cache_group );

		$rows = apply_filters('geodir_widget_listings_rows_count_pre_query', $rows, $post_type, $count_only, $sql, $gd_query_args_widgets);

		if ( $rows === false ) {
			$rows = (int) $wpdb->get_var( $sql );

			geodir_cache_set( $cache_key, $rows, $cache_group );
		}
	} else {
		/// ADD THE HAVING TO LIMIT TO THE EXACT RADIUS
		if ( ! empty( $query_args['is_gps_query'] ) || ! empty( $query_args['nearby_gps'] ) ) {
			/*
			 * The HAVING clause is often used with the GROUP BY clause to filter groups based on a specified condition.
			 * If the GROUP BY clause is omitted, the HAVING clause behaves like the WHERE clause.
			 */
			if ( strpos( $where, ' HAVING ') === false && strpos( $groupby, ' HAVING ') === false &&  strpos( $fields, 'AS distance' ) ) {
				$dist = get_query_var( 'dist' ) ? geodir_sanitize_float( get_query_var( 'dist' ) ) : geodir_get_option( 'search_radius', 5 );
				if ( wp_doing_ajax() && ! empty( $wp->query_vars['dist'] ) ) {
					$dist = geodir_sanitize_float( $wp->query_vars['dist'] );
				}

				if ( GeoDir_Post_types::supports( $post_type, 'service_distance' ) ) {
					$having = $wpdb->prepare( " HAVING ( ( `{$table}`.`service_distance` > 0 AND distance <= `{$table}`.`service_distance` ) OR ( ( `{$table}`.`service_distance` <= 0 OR `{$table}`.`service_distance` IS NULL ) AND distance <= %f ) )", $dist );
				} else {
					$having = $wpdb->prepare( " HAVING distance <= %f ", $dist );
				}

				if ( trim( $groupby ) != '' ) {
					$groupby .= $having;
				} else {
					$where .= $having;
				}
			}
		}
		/// ADD THE HAVING TO LIMIT TO THE EXACT RADIUS

		$orderby = geodir_widget_listings_get_order(); // query args passed as global

		/**
		 * Filter widget listing orderby clause string part that is being used for query.
		 *
		 * @since 1.0.0
		 *
		 * @param string $orderby Order by clause string.
		 * @param string $table Table name.
		 * @param string $post_type Post type.
		 */
		$orderby = apply_filters( 'geodir_filter_widget_listings_orderby', $orderby, $table, $post_type );

		$orderby = $orderby != '' ? " ORDER BY " . $orderby : '';

		$limit = ! empty( $query_args['posts_per_page'] ) ? $query_args['posts_per_page'] : 5;
		/**
		 * Filter widget listing limit that is being used for query.
		 *
		 * @since 1.0.0
		 *
		 * @param int $limit Query results limit.
		 * @param string $post_type Post type.
		 */
		$limit = apply_filters( 'geodir_filter_widget_listings_limit', $limit, $post_type );

		$page = ! empty( $query_args['pageno'] ) ? absint( $query_args['pageno'] ) : 1;
		if ( ! $page ) {
			$page = 1;
		}

		$limit = (int) $limit > 0 ? " LIMIT " . absint( ( $page - 1 ) * (int) $limit ) . ", " . (int) $limit : "";

		//@todo removed SQL_CALC_FOUND_ROWS from below as don't think it is needed and query is faster without
		$sql  = "SELECT " . $fields . " FROM " . $wpdb->posts . "
			" . $join . "
			" . $where . "
			" . $groupby . "
			" . $orderby . "
			" . $limit;

		/**
		 * Filters the listings SQL query before sending.
		 *
		 * @since 2.0.0.71
		 *
		 * @param string $sql The SQL query.
		 * @param string $post_type The post type.
		 */
		$sql = apply_filters( 'geodir_filter_widget_listings_sql', $sql, $post_type );
		$sql = str_replace( "WHERE 1=1 AND ", "WHERE ", $sql );

		$cache_key = wp_hash( $sql );

		$rows = geodir_cache_get( $cache_key, $cache_group );

		// maybe short-circuit
		$rows = apply_filters('geodir_widget_listings_rows_pre_query', $rows, $post_type, $count_only, $sql, $gd_query_args_widgets);

		if ( $rows === false ) {
			$rows = $wpdb->get_results( $sql );

			geodir_cache_set( $cache_key, $rows, $cache_group );
		}
	}

	unset( $GLOBALS['gd_query_args_widgets'] );
	unset( $gd_query_args_widgets );

	return $rows;
}

/**
 * Listing query fields SQL part for widgets.
 *
 * @since   1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 *
 * @param string $fields Fields SQL.
 * @param string $table Table name.
 * @param string $post_type Post type.
 *
 * @return string Modified fields SQL.
 */
function geodir_function_widget_listings_fields( $fields, $table, $post_type ) {
	global $wpdb, $geodirectory, $gd_query_args_widgets, $gd_post;

	$query_args = $gd_query_args_widgets;

	if ( empty( $query_args ) || empty( $query_args['is_geodir_loop'] ) || ! empty( $query_args['count_only'] ) ) {
		return $fields;
	}

	if ( ! empty( $query_args['distance_to_post'] ) && ! empty( $gd_post->latitude ) && ! empty( $gd_post->longitude ) ) {
		$latitude = $gd_post->latitude;
		$longitude = $gd_post->longitude;
	} else if ( ! empty( $query_args['supports_location'] ) && ( ! empty( $query_args['is_gps_query'] ) || empty( $query_args['nearby_gps'] ) ) && ( $latlon = $geodirectory->location->get_latlon() ) ) {
		$latitude = $latlon['lat'];
		$longitude = $latlon['lon'];
	} else if ( ! empty( $query_args['nearby_gps'] ) && ! empty( $query_args['nearby_gps']['latitude'] ) && ! empty( $query_args['nearby_gps']['longitude'] ) ) {
		$latitude = geodir_sanitize_float( $query_args['nearby_gps']['latitude'] );
		$longitude = geodir_sanitize_float( $query_args['nearby_gps']['longitude'] );
		$fields .= ", '" . $latitude . "' AS gps_latitude, '" . $longitude . "' AS gps_longitude";
	} else {
		$latitude = '';
		$longitude = '';
	}

	if ( $query_part = geodir_gps_query_part( $latitude, $longitude, $table ) ) {
		$fields .= ", " . $query_part . " AS distance";
	}

	return $fields;
}

add_filter( 'geodir_filter_widget_listings_fields', 'geodir_function_widget_listings_fields', 10, 3 );

/**
 * Get the GPS query part.
 *
 * @since 2.2.9
 *
 * @param string $latitude Latitude.
 * @param string $longitude Longitude.
 * @param string $table Detail database table name.
 * @param string $radius Distance radius.
 *
 * @return string Modified join clause SQL.
 */
function geodir_gps_query_part( $latitude, $longitude, $table = '', $radius = '' ) {
	if ( empty( $latitude ) || empty( $longitude ) ) {
		return "";
	}

	if ( empty( $radius ) ) {
		$radius = geodir_getDistanceRadius( geodir_get_option( 'search_distance_long' ) );
	}

	$prefix = $table ? $table . '.' : '';

	$query = "( {$radius} * 2 * ASIN( SQRT( POWER( SIN( ( ( {$latitude} ) - ( {$prefix}`latitude` ) ) * PI() / 180 / 2 ), 2 ) + COS( ( {$latitude} ) * PI() / 180 ) * COS( ( {$prefix}`latitude` ) * PI() / 180 ) * POWER( SIN( ( {$longitude} - {$prefix}`longitude` ) * PI() / 180 / 2 ), 2 ) ) ) )";

	return $query;
}

/**
 * Listing query join clause SQL part for widgets.
 *
 * @since   1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 *
 * @param string $join Join clause SQL.
 *
 * @return string Modified join clause SQL.
 */
function geodir_function_widget_listings_join( $join ) {
	global $wpdb, $plugin_prefix, $gd_query_args_widgets;

	$query_args = $gd_query_args_widgets;
	if ( empty( $query_args ) || empty( $query_args['is_geodir_loop'] ) ) {
		return $join;
	}

	$post_type = empty( $query_args['post_type'] ) ? 'gd_place' : $query_args['post_type'];
	$table     = $plugin_prefix . $post_type . '_detail';

	if ( ! empty( $query_args['with_pics_only'] ) ) {
		$join .= " LEFT JOIN " . GEODIR_ATTACHMENT_TABLE . " ON ( " . GEODIR_ATTACHMENT_TABLE . ".post_id=" . $table . ".post_id AND " . GEODIR_ATTACHMENT_TABLE . ".mime_type LIKE '%image%' )";
	}

	if ( ! empty( $query_args['tax_query'] ) ) {
		$tax_queries = get_tax_sql( $query_args['tax_query'], $wpdb->posts, 'ID' );
		if ( ! empty( $tax_queries['join'] ) && ! empty( $tax_queries['where'] ) ) {
			$join .= $tax_queries['join'];
		}
	}

	return $join;
}

add_filter( 'geodir_filter_widget_listings_join', 'geodir_function_widget_listings_join' );


/**
 * Listing query where clause SQL part for widgets.
 *
 * @since   1.0.0
 * @since   1.6.18 New attributes added in gd_listings shortcode to filter user favorite listings.
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @global object $geodirectory GeoDirectory object.
 * @global array  $gd_query_args_widgets Widget args.
 *
 * @param string $where Where clause SQL.
 *
 * @return string Modified where clause SQL.
 */
function geodir_function_widget_listings_where( $where ) {
	global $wp, $wpdb, $geodirectory, $gd_query_args_widgets, $gd_post;

	$query_args = $gd_query_args_widgets;
	if ( empty( $query_args ) || empty( $query_args['is_geodir_loop'] ) ) {
		return $where;
	}
	$post_type = empty( $query_args['post_type'] ) ? 'gd_place' : $query_args['post_type'];
	$table     = geodir_db_cpt_table( $post_type );

	if ( ! empty( $query_args ) ) {
		if ( ! empty( $query_args['gd_location'] ) && function_exists( 'geodir_default_location_where' ) ) {
			$where = geodir_default_location_where( $where, $table );
		}

		if ( GeoDir_Post_types::supports( $post_type, 'location' ) ) {
			// Private address filter
			if ( $geodirectory->location->get_latlon() && GeoDir_Post_types::supports( $post_type, 'private_address' ) ) {
				$where .= " AND ( `{$table}`.`private_address` IS NULL OR `{$table}`.`private_address` <> 1 ) ";
			}

			if ( ! empty( $query_args['count_only'] ) ) {
				if ( ! empty( $query_args['distance_to_post'] ) && ! empty( $gd_post->latitude ) && ! empty( $gd_post->longitude ) ) {
					$latitude = $gd_post->latitude;
					$longitude = $gd_post->longitude;
				} else if ( empty( $query_args['is_gps_query'] ) && ! empty( $query_args['nearby_gps'] ) && ! empty( $query_args['nearby_gps']['latitude'] ) && ! empty( $query_args['nearby_gps']['longitude'] ) ) {
					$latitude = $query_args['nearby_gps']['latitude'];
					$longitude = $query_args['nearby_gps']['longitude'];
				} else {
					$latitude = '';
					$longitude = '';
				}

				if ( $query_part = geodir_gps_query_part( $latitude, $longitude, $table ) ) {
					if ( $_dist = get_query_var( 'dist' ) ) {
						$dist = geodir_sanitize_float( $_dist );
					} else {
						$dist = geodir_get_option( 'search_radius', 5 );
					}

					if ( wp_doing_ajax() && ! empty( $wp->query_vars['dist'] ) ) {
						$dist = geodir_sanitize_float( $wp->query_vars['dist'] );
					}

					if ( GeoDir_Post_types::supports( $post_type, 'service_distance' ) ) {
						$where .= $wpdb->prepare( " AND ( ( `service_distance` > 0 AND " . $query_part . " <= `service_distance` ) OR ( ( `service_distance` <= 0 OR `service_distance` IS NULL ) AND " . $query_part . " <= %f ) )", $dist );
					} else {
						$where .= $wpdb->prepare( " AND " . $query_part . " <= %f", $dist );
					}
				}
			}
		}

		if ( ! empty( $query_args['post_author'] ) ) {
			$where .= " AND " . $wpdb->posts . ".post_author = " . (int) $query_args['post_author'];
		}

		if ( ! empty( $query_args['show_featured_only'] ) && GeoDir_Post_types::supports( $post_type, 'featured' ) ) {
			$where .= " AND " . $table . ".featured = '1'";
		}

		// Special offers
		if ( ! empty( $query_args['show_special_only'] ) && GeoDir_Post_types::supports( $post_type, 'special_offers' ) ) {
			$where .= " AND ( " . $table . ".special_offers != '' AND " . $table . ".special_offers IS NOT NULL AND " . $table . ".special_offers !='0' )";
		}

		if ( ! empty( $query_args['with_pics_only'] ) ) {
			$where .= " AND " . GEODIR_ATTACHMENT_TABLE . ".ID IS NOT NULL ";
		}

		if ( ! empty( $query_args['featured_image_only'] ) ) {
			$where .= " AND " . $table . ".featured_image IS NOT NULL AND " . $table . ".featured_image!='' ";
		}

		if ( ! empty( $query_args['with_videos_only'] ) ) {
			$where .= " AND ( " . $table . ".video != '' AND " . $table . ".video IS NOT NULL )";
		}

		if ( ! empty( $query_args['favorites_by_user'] ) ) {
			$user_favorites = '-1';

			if ( (int) $query_args['favorites_by_user'] > 0 ) {
				$user_favorites = geodir_get_user_favourites( (int) $query_args['favorites_by_user'] );
				$user_favorites = ! empty( $user_favorites ) && is_array( $user_favorites ) ? implode( "','", $user_favorites ) : '-1';
			}

			$where .= " AND `" . $wpdb->posts . "`.`ID` IN('" . $user_favorites . "')";
		}

		if ( ! empty( $query_args['tax_query'] ) ) {
			$tax_queries = get_tax_sql( $query_args['tax_query'], $wpdb->posts, 'ID' );

			if ( ! empty( $tax_queries['join'] ) && ! empty( $tax_queries['where'] ) ) {
				$where .= $tax_queries['where'];
			}
		}
	}

	return $where;
}

add_filter( 'geodir_filter_widget_listings_where', 'geodir_function_widget_listings_where' );


/**
 * Listing query orderby clause SQL part for widgets.
 *
 * @since   1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 *
 * @param string $orderby Orderby clause SQL.
 *
 * @return string Modified orderby clause SQL.
 */
function geodir_function_widget_listings_orderby( $orderby ) {
	global $wpdb, $plugin_prefix, $gd_query_args_widgets;

	$query_args = $gd_query_args_widgets;
	if ( empty( $query_args ) || empty( $query_args['is_geodir_loop'] ) ) {
		return $orderby;
	}

	return $orderby;
}

add_filter( 'geodir_filter_widget_listings_orderby', 'geodir_function_widget_listings_orderby' );


/**
 * Listing query limit for widgets.
 *
 * @since   1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 *
 * @param int $limit Query limit.
 *
 * @return int Query limit.
 */
function geodir_function_widget_listings_limit( $limit ) {
	global $wpdb, $plugin_prefix, $gd_query_args_widgets;

	$query_args = $gd_query_args_widgets;
	if ( empty( $query_args ) || empty( $query_args['is_geodir_loop'] ) ) {
		return $limit;
	}

	if ( ! empty( $query_args ) && ! empty( $query_args['posts_per_page'] ) ) {
		$limit = (int) $query_args['posts_per_page'];
	}

	return $limit;
}

add_filter( 'geodir_filter_widget_listings_limit', 'geodir_function_widget_listings_limit' );

/**
 * Add extra fields to google maps script call.
 *
 * @since   1.0.0
 * @package GeoDirectory
 * @global object $post The current post object.
 *
 * @param string $extra Old extra string.
 *
 * @return string Modified extra string.
 */
function geodir_googlemap_script_extra_details_page( $extra ) {
	global $post;
	$add_google_places_api = false;
	if ( isset( $post->post_content ) && has_shortcode( $post->post_content, 'gd_add_listing' ) ) {
		$add_google_places_api = true;
	}
	$add_google_places_api = true; // @todo New maps may require places libraries on non details pages as well for map_type = post widgets.
	if ( ( ( $extra && ! str_replace( 'libraries=places', '', $extra ) ) || ! $extra ) && ( geodir_is_page( 'detail' ) || $add_google_places_api ) ) {
		$extra .= "&amp;libraries=places";
	}

	// Since January 2023 Google made callback as a required parameter.
	$extra .= '&amp;callback=geodirInitGoogleMap';

	return $extra;
}

add_filter( 'geodir_googlemap_script_extra', 'geodir_googlemap_script_extra_details_page', 101, 1 );

/*-----------------------------------------------------------------------------------*/
/*  Term count functions
/*-----------------------------------------------------------------------------------*/
/**
 * Count posts by term.
 *
 * @since   1.0.0
 * @package GeoDirectory
 *
 * @param array $data Count data array.
 * @param object $term The term object.
 *
 * @return int Post count.
 */
function geodir_count_posts_by_term( $data, $term ) {

	if ( $data ) {
		if ( isset( $data[ $term->term_id ] ) ) {
			return $data[ $term->term_id ];
		} else {
			return 0;
		}
	} else {
		return $term->count;
	}
}

/**
 * Sort terms object by post count.
 *
 * @since   1.0.0
 * @package GeoDirectory
 * param array $terms An array of term objects.
 * @return array Sorted terms array.
 */
function geodir_sort_terms_by_count( $terms ) {
	if ( ! empty( $terms ) ) {
		usort( $terms, "geodir_sort_by_count_obj" );
	}

	return $terms;
}

/**
 * Sort terms object by review count.
 *
 * @since   1.0.0
 * @package GeoDirectory
 *
 * @param array $terms An array of term objects.
 *
 * @return array Sorted terms array.
 */
function geodir_sort_terms_by_review_count( $terms ) {
	if ( ! empty( $terms ) ) {
		usort( $terms, "geodir_sort_by_review_count_obj" );
	}

	return $terms;
}

/**
 * Sort terms either by post count or review count.
 *
 * @since   1.0.0
 * @package GeoDirectory
 *
 * @param array $terms An array of term objects.
 * @param string $sort The sort type. Can be count (Post Count) or review_count. Default. count.
 *
 * @return array Sorted terms array.
 */
function geodir_sort_terms( $terms, $sort = 'count' ) {

	if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
		if ( $sort == 'count' ) {
			return geodir_sort_terms_by_count( $terms );
		}
		if ( $sort == 'review_count' ) {
			return geodir_sort_terms_by_review_count( $terms );
		}
	}

	return array();
}

/*-----------------------------------------------------------------------------------*/
/*  Utils
/*-----------------------------------------------------------------------------------*/
/**
 * Compares post count from array for sorting.
 *
 * @since   1.0.0
 * @package GeoDirectory
 *
 * @param array $a The left side array to compare.
 * @param array $b The right side array to compare.
 *
 * @return bool
 */
function geodir_sort_by_count( $a, $b ) {
	return $a['count'] < $b['count'];
}

/**
 * Compares post count from object for sorting.
 *
 * @since   1.0.0
 * @package GeoDirectory
 *
 * @param object $a The left side object to compare.
 * @param object $b The right side object to compare.
 *
 * @return bool
 */
function geodir_sort_by_count_obj( $a, $b ) {
	if ( $a->count == $b->count ) {
		return 0;
	}
	return ( $a->count < $b->count ) ? 1 : -1;
}

/**
 * Compares review count from object for sorting.
 *
 * @since   1.0.0
 * @package GeoDirectory
 *
 * @param object $a The left side object to compare.
 * @param object $b The right side object to compare.
 *
 * @return bool
 */
function geodir_sort_by_review_count_obj( $a, $b ) {
	if ( $a->review_count == $b->review_count ) {
		return 0;
	}
	return ( $a->review_count < $b->review_count ) ? 1 : -1;
}

/**
 * Retrieve list of mime types and file extensions allowed for file upload.
 *
 * @since   1.4.7
 * @package GeoDirectory
 *
 * @return array Array of mime types.
 */
function geodir_allowed_mime_types() {
	/**
	 * Filter the list of mime types and file extensions allowed for file upload.
	 *
	 * @since   1.4.7
	 * @package GeoDirectory
	 *
	 * @param array $geodir_allowed_mime_types and file extensions.
	 */
	return apply_filters( 'geodir_allowed_mime_types', array(
			'Image'       => array( // Image formats.
				'jpg'  => 'image/jpeg',
				'jpe'  => 'image/jpeg',
				'jpeg' => 'image/jpeg',
				'gif'  => 'image/gif',
				'png'  => 'image/png',
				'bmp'  => 'image/bmp',
				'ico'  => 'image/x-icon',
				'webp' => 'image/webp',
				'avif' => 'image/avif'
			),
			'Video'       => array( // Video formats.
				'asf'  => 'video/x-ms-asf',
				'avi'  => 'video/avi',
				'flv'  => 'video/x-flv',
				'mkv'  => 'video/x-matroska',
				'mp4'  => 'video/mp4',
				'mpeg' => 'video/mpeg',
				'mpg'  => 'video/mpeg',
				'wmv'  => 'video/x-ms-wmv',
				'3gp'  => 'video/3gpp',
			),
			'Audio'       => array( // Audio formats.
				'ogg' => 'audio/ogg',
				'mp3' => 'audio/mpeg',
				'wav' => 'audio/wav',
				'wma' => 'audio/x-ms-wma',
			),
			'Text'        => array( // Text formats.
				'css'  => 'text/css',
				'csv'  => 'text/csv',
				'htm'  => 'text/html',
				'html' => 'text/html',
				'txt'  => 'text/plain',
				'rtx'  => 'text/richtext',
				'vtt'  => 'text/vtt',
			),
			'Application' => array( // Application formats.
				'doc'  => 'application/msword',
				'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
				'exe'  => 'application/x-msdownload',
				'js'   => 'application/javascript',
				'odt'  => 'application/vnd.oasis.opendocument.text',
				'pdf'  => 'application/pdf',
				'pot'  => 'application/vnd.ms-powerpoint',
				'ppt'  => 'application/vnd.ms-powerpoint',
				'pptx' => 'application/vnd.ms-powerpoint',
				'psd'  => 'application/octet-stream',
				'rar'  => 'application/rar',
				'rtf'  => 'application/rtf',
				'swf'  => 'application/x-shockwave-flash',
				'tar'  => 'application/x-tar',
				'xls'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
				'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
				'zip'  => 'application/zip',
				'gpx'  => 'application/gpx',
			)
		)
	);
}

/**
 * Retrieves the list of allowed image extensions.
 *
 * @since 2.3.47.
 *
 * @return array Array of image extensions.
 */
function geodir_image_extensions() {
	$gd_mime_types = geodir_allowed_mime_types();

	$image_exts = ! empty( $gd_mime_types['Image'] ) ? array_keys( $gd_mime_types['Image'] ) : array();

	/**
	 * Filters the list of allowed image extensions.
	 *
	 * @since 2.3.47.
	 *
	 * @param array $image_exts Array of image extensions.
	 */
	return apply_filters( 'geodir_allowed_post_image_exts', $image_exts );
}

/**
 * Retrieves the list of allowed image mime types.
 *
 * @since 2.3.47.
 *
 * @return array Array of image mime types.
 */
function geodir_image_mime_types() {
	$gd_mime_types = geodir_allowed_mime_types();

	$mime_types = ! empty( $gd_mime_types['Image'] ) ? array_values( $gd_mime_types['Image'] ) : array();

	if ( in_array( 'image/jpeg', $mime_types ) ) {
		$mime_types[] = 'image/jpe';
		$mime_types[] = 'image/jpg';
	}

	if ( ! empty( $mime_types ) ) {
		$mime_types = array_unique( $mime_types );
	}

	/**
	 * Filters the list of allowed image mime types.
	 *
	 * @since 2.3.47.
	 *
	 * @param array $mime_types Array of image mime types.
	 */
	return apply_filters( 'geodir_allowed_image_mime_types', $mime_types );
}

/**
 * Retrieve list of user display name for user id.
 *
 * @since 1.5.0
 *
 * @param  string $user_id The WP user id.
 *
 * @return string User display name.
 */
function geodir_get_client_name( $user_id ) {
	$client_name = '';

	$user_data = get_userdata( $user_id );

	if ( ! empty( $user_data ) ) {
		if ( isset( $user_data->display_name ) && trim( $user_data->display_name ) != '' ) {
			$client_name = trim( $user_data->display_name );
		} else if ( isset( $user_data->user_nicename ) && trim( $user_data->user_nicename ) != '' ) {
			$client_name = trim( $user_data->user_nicename );
		} else {
			$client_name = trim( $user_data->user_login );
		}
	}

	return $client_name;
}


add_filter( 'wpseo_replacements', 'geodir_wpseo_replacements', 10, 1 );
/**
 * Add location variables to wpseo replacements.
 *
 * @since 1.5.4
 */
function geodir_wpseo_replacements( $vars ) {

	// location variables
	$gd_post_type   = geodir_get_current_posttype();
	$location_array = geodir_get_current_location_terms( 'query_vars', $gd_post_type );
	/**
	 * Filter the title variables location variables array
	 *
	 * @since   1.5.5
	 * @package GeoDirectory
	 *
	 * @param array $location_array The array of location variables.
	 * @param array $vars The page title variables.
	 */
	$location_array = apply_filters( 'geodir_filter_title_variables_location_arr_seo', $location_array, $vars );


	$location_replace_vars = geodir_location_replace_vars( $location_array, null, '' );
	$vars                  = $vars + $location_replace_vars;

	/**
	 * Filter the title variables after standard ones have been filtered for wpseo.
	 *
	 * @since   1.5.7
	 * @package GeoDirectory
	 *
	 * @param string $vars The title with variables.
	 * @param array $location_array The array of location variables.
	 */
	return apply_filters( 'geodir_wpseo_replacements_vars', $vars, $location_array );
}


add_filter( 'geodir_seo_meta_title', 'geodir_filter_title_variables', 10, 3 );
add_filter( 'geodir_seo_page_title', 'geodir_filter_title_variables', 10, 2 );
add_filter( 'geodir_seo_meta_description_pre', 'geodir_filter_title_variables', 10, 3 );

/**
 * Filter the title variables.
 *
 * %%date%%                        Replaced with the date of the post/page
 * %%title%%                    Replaced with the title of the post/page
 * %%sitename%%                    The site's name
 * %%sitedesc%%                    The site's tagline / description
 * %%excerpt%%                    Replaced with the post/page excerpt (or auto-generated if it does not exist)
 * %%tag%%                        Replaced with the current tag/tags
 * %%category%%                    Replaced with the post categories (comma separated)
 * %%category_description%%        Replaced with the category description
 * %%tag_description%%            Replaced with the tag description
 * %%term_description%%            Replaced with the term description
 * %%term_title%%                Replaced with the term name
 * %%searchphrase%%                Replaced with the current search phrase
 * %%sep%%                        The separator defined in your theme's wp_title() tag.
 *
 * ADVANCED
 * %%pt_single%%                Replaced with the post type single label
 * %%pt_plural%%                Replaced with the post type plural label
 * %%modified%%                    Replaced with the post/page modified time
 * %%id%%                        Replaced with the post/page ID
 * %%name%%                        Replaced with the post/page author's 'nicename'
 * %%userid%%                    Replaced with the post/page author's userid
 * %%page%%                        Replaced with the current page number (i.e. page 2 of 4)
 * %%pagetotal%%                Replaced with the current page total
 * %%postcount%%                Replaced with the current post found
 *
 * %%pagenumber%%                Replaced with the current page number
 *
 * @since   1.5.7
 * @package GeoDirectory
 *
 * @global object $wp WordPress object.
 * @global object $post The current post object.
 *
 * @param string $title The title with variables.
 * @param string $gd_page The page being filtered.
 * @param string $sep The separator, default: `|`.
 *
 * @return string Title after filtered variables.
 */
function geodir_filter_title_variables( $title, $gd_page, $sep = '' ) {
	global $wp, $post;

	if ( ! $gd_page || ! $title ) {
		return $title; // if no a GD page then bail.
	}

	if ( $sep == '' ) {
		/**
		 * Filter the page title separator.
		 *
		 * @since   1.0.0
		 * @package GeoDirectory
		 *
		 * @param string $sep The separator, default: `|`.
		 */
		$sep = apply_filters( 'geodir_page_title_separator', '|' );
	}

	if ( strpos( $title, '%%title%%' ) !== false ) {
		$title = str_replace( "%%title%%", $post->post_title, $title );
	}

	if ( strpos( $title, '%%sitename%%' ) !== false ) {
		$title = str_replace( "%%sitename%%", get_bloginfo( 'name' ), $title );
	}

	if ( strpos( $title, '%%sitedesc%%' ) !== false ) {
		$title = str_replace( "%%sitedesc%%", get_bloginfo( 'description' ), $title );
	}

	if ( strpos( $title, '%%excerpt%%' ) !== false ) {
		$title = str_replace( "%%excerpt%%", strip_tags( get_the_excerpt() ), $title );
	}

	if ( $gd_page == 'search' || $gd_page == 'author' ) {
		$post_type = isset( $_REQUEST['stype'] ) ? sanitize_text_field( $_REQUEST['stype'] ) : '';
	} else if ( $gd_page == 'add-listing' ) {
		$post_type = ( isset( $_REQUEST['listing_type'] ) ) ? sanitize_text_field( $_REQUEST['listing_type'] ) : '';
		$post_type = ! $post_type && ! empty( $_REQUEST['pid'] ) ? get_post_type( (int) $_REQUEST['pid'] ) : $post_type;
	} else if ( isset( $post->post_type ) && $post->post_type && in_array( $post->post_type, geodir_get_posttypes() ) ) {
		$post_type = $post->post_type;
	} else {
		$post_type = get_query_var( 'post_type' );
	}

	if ( strpos( $title, '%%pt_single%%' ) !== false ) {
		$singular_name = '';
		if ( $post_type && $singular_name = geodir_get_post_type_singular_label( $post_type ) ) {
			$singular_name = __( $singular_name, 'geodirectory' );
		}

		$title = str_replace( "%%pt_single%%", $singular_name, $title );
	}

	if ( strpos( $title, '%%pt_plural%%' ) !== false ) {
		$plural_name = '';
		if ( $post_type && $plural_name = geodir_get_post_type_plural_label( $post_type ) ) {
			$plural_name = __( $plural_name, 'geodirectory' );
		}

		$title = str_replace( "%%pt_plural%%", $plural_name, $title );
	}

	if ( strpos( $title, '%%category%%' ) !== false || strpos( $title, '%%in_category%%' ) !== false ) {
		$cat_name = '';

		if ( $gd_page == 'detail' ) {
			if ( $post->default_category ) {
				$cat      = get_term( $post->default_category, $post->post_type . 'category' );
				$cat_name = ( isset( $cat->name ) ) ? $cat->name : '';
			}
		} else if ( $gd_page == 'listing' ) {
			$queried_object = get_queried_object();
			if ( isset( $queried_object->name ) ) {
				$cat_name = $queried_object->name;
			}
		} else if ( $gd_page == 'search' ) {
			$cat_name = GeoDir_SEO::get_searched_category_name( $post_type . 'category' );
		}

		$in_cat_name = $cat_name ? wp_sprintf( _x( 'in %s', 'in category', 'geodirectory' ), $cat_name ) : '';

		$title = str_replace( "%%category%%", $cat_name, $title );
		$title = str_replace( "%%in_category%%", $in_cat_name, $title );
	}

	if ( strpos( $title, '%%tag%%' ) !== false ) {
		$cat_name = '';

		if ( $gd_page == 'detail' ) {
			if ( $post->default_category ) {
				$cat      = get_term( $post->default_category, $post->post_type . 'category' );
				$cat_name = ( isset( $cat->name ) ) ? $cat->name : '';
			}
		} else if ( $gd_page == 'listing' ) {
			$queried_object = get_queried_object();
			if ( isset( $queried_object->name ) ) {
				$cat_name = $queried_object->name;
			}
		}
		$title = str_replace( "%%tag%%", $cat_name, $title );
	}

	if ( strpos( $title, '%%id%%' ) !== false ) {
		$ID    = ( isset( $post->ID ) ) ? $post->ID : '';
		$title = str_replace( "%%id%%", $ID, $title );
	}

	if ( strpos( $title, '%%sep%%' ) !== false ) {
		$title = str_replace( "%%sep%%", $sep, $title );
	}

	// location variables
	$gd_post_type   = geodir_get_current_posttype();
	$location_array = geodir_get_current_location_terms( 'query_vars', $gd_post_type );

	/**
	 * Filter the title variables location variables array
	 *
	 * @since   1.5.5
	 * @package GeoDirectory
	 *
	 * @param array $location_array The array of location variables.
	 * @param string $title The title with variables..
	 * @param string $gd_page The page being filtered.
	 * @param string $sep The separator, default: `|`.
	 */
	$location_array = apply_filters( 'geodir_filter_title_variables_location_arr', $location_array, $title, $gd_page, $sep );

	if ( $gd_page == 'location' && get_query_var( 'gd_country_full' ) ) {
		if ( get_query_var( 'gd_country_full' ) ) {
			$location_array['gd_country'] = get_query_var( 'gd_country_full' );
		}
		if ( get_query_var( 'gd_region_full' ) ) {
			$location_array['gd_region'] = get_query_var( 'gd_region_full' );
		}
		if ( get_query_var( 'gd_city_full' ) ) {
			$location_array['gd_city'] = get_query_var( 'gd_city_full' );
		}
		if ( get_query_var( 'gd_neighbourhood_full' ) ) {
			$location_array['gd_neighbourhood'] = get_query_var( 'gd_neighbourhood_full' );
		}
	}

	/**
	 * Filter the location terms variables.
	 *
	 * @since   1.6.16
	 * @package GeoDirectory
	 *
	 * @param string $title The title with variables.
	 * @param array $location_array The array of location variables.
	 * @param string $gd_page The page being filtered.
	 * @param string $sep The separator, default: `|`.
	 */
	$title = apply_filters( 'geodir_replace_location_variables', $title, $location_array, $gd_page, $sep );

	$search_term = '';
	if ( isset( $_REQUEST['s'] ) ) {
		$search_term = esc_attr( $_REQUEST['s'] );
		$search_term = str_replace( array( "%E2%80%99", "’" ), array( "%27", "'" ), $search_term ); // apple suck
		$search_term = trim( stripslashes( $search_term ) );
	}

	// %%search_term%%
	if ( strpos( $title, '%%search_term%%' ) !== false ) {
		$title = str_replace( "%%search_term%%", $search_term, $title );
	}

	// %%for_search_term%%
	if ( strpos( $title, '%%for_search_term%%' ) !== false ) {
		$for_search_term = $search_term != '' ? wp_sprintf( __( 'for %s', 'geodirectory' ), $search_term ) : '';

		$title = str_replace( "%%for_search_term%%", $for_search_term, $title );
	}

	$search_near_term = '';
	$search_near = '';
	if ( isset( $_REQUEST['snear'] ) ) {
		$search_near_term = esc_attr( $_REQUEST['snear'] );
		$search_near_term = str_replace( array( "%E2%80%99", "’" ), array( "%27", "'" ), $search_near_term ); // apple suck
		$search_near_term = trim( stripslashes( $search_near_term ) );

		if ( $search_near_term != '' ) {
			$search_near = wp_sprintf( __( 'near %s', 'geodirectory' ), $search_near_term );
		}
	}

	// %%search_near_term%%
	if ( strpos( $title, '%%search_near_term%%' ) !== false ) {
		$title = str_replace( "%%search_near_term%%", $search_near_term, $title );
	}

	// %%search_near%%
	if ( strpos( $title, '%%search_near%%' ) !== false ) {
		$title = str_replace( "%%search_near%%", $search_near, $title );
	}

	if ( strpos( $title, '%%name%%' ) !== false ) {
		if ( is_author() ) {
			$curauth     = ( get_query_var( 'author_name' ) ) ? get_user_by( 'slug', get_query_var( 'author_name' ) ) : get_userdata( get_query_var( 'author' ) );
			$author_name = $curauth->display_name;
		} else {
			$author_name = get_the_author();
		}
		if ( ! $author_name || $author_name === '' ) {
			$queried_object = get_queried_object();

			if ( isset( $queried_object->data->user_nicename ) ) {
				$author_name = $queried_object->data->display_name;
			}
		}
		$title = str_replace( "%%name%%", $author_name, $title );
	}

	if ( strpos( $title, '%%page%%' ) !== false ) {
		$page  = geodir_title_meta_page( $sep );
		$title = str_replace( "%%page%%", $page, $title );
	}
	if ( strpos( $title, '%%pagenumber%%' ) !== false ) {
		$pagenumber = geodir_title_meta_pagenumber();
		$title      = str_replace( "%%pagenumber%%", $pagenumber, $title );
	}
	if ( strpos( $title, '%%pagetotal%%' ) !== false ) {
		$pagetotal = geodir_title_meta_pagetotal();
		$title     = str_replace( "%%pagetotal%%", $pagetotal, $title );
	}
	if ( strpos( $title, '%%postcount%%' ) !== false ) {
		$postcount = geodir_title_meta_postcount();
		$title     = str_replace( "%%postcount%%", $postcount, $title );
	}

	// Prevents replace - to &#8211;
	$title = str_replace( '-', 'GEODIRENDASH', $title );

	$title = wptexturize( $title );
	$title = convert_chars( $title );
	$title = esc_html( $title );

	$title = str_replace( 'GEODIRENDASH', '-', $title );

	/**
	 * Filter the title variables after standard ones have been filtered.
	 *
	 * @since   1.5.7
	 * @package GeoDirectory
	 *
	 * @param string $title The title with variables.
	 * @param array $location_array The array of location variables.
	 * @param string $gd_page The page being filtered.
	 * @param string $sep The separator, default: `|`.
	 */

	return apply_filters( 'geodir_filter_title_variables_vars', $title, $location_array, $gd_page, $sep );
}


/**
 * Remove the location terms to hide term from location url.
 *
 * @since   1.5.5
 * @package GeoDirectory
 *
 * @param  array $location_terms Array of location terms.
 *
 * @return array Location terms.
 */
function geodir_remove_location_terms( $location_terms = array() ) {
	$location_manager = defined( 'GEODIR_LOCATIONS_TABLE' ) ? true : false;

	if ( ! empty( $location_terms ) && $location_manager ) {
		$hide_country_part = geodir_get_option( 'lm_hide_country_part' );
		$hide_region_part  = geodir_get_option( 'lm_hide_region_part' );

		if ( $hide_region_part && $hide_country_part ) {
			if ( isset( $location_terms['gd_country'] ) ) {
				unset( $location_terms['gd_country'] );
			}
			if ( isset( $location_terms['gd_region'] ) ) {
				unset( $location_terms['gd_region'] );
			}
		} else if ( $hide_region_part && ! $hide_country_part ) {
			if ( isset( $location_terms['gd_region'] ) ) {
				unset( $location_terms['gd_region'] );
			}
		} else if ( ! $hide_region_part && $hide_country_part ) {
			if ( isset( $location_terms['gd_country'] ) ) {
				unset( $location_terms['gd_country'] );
			}
		}
	}

	/**
	 * Filter the remove location terms array.
	 *
	 * @since 1.6.22
	 *
	 * @param array $location_terms The array of location terms.
	 */
	return apply_filters( 'geodir_remove_location_terms', $location_terms );
}

/**
 * Retrieve the current page start & end numbering with context (i.e. 'page 2 of 4') for use as replacement string.
 *
 * @since   1.6.0
 * @package GeoDirectory
 *
 * @param string $sep The separator tag.
 *
 * @return string|null The current page start & end numbering.
 */
function geodir_title_meta_page( $sep ) {
	$replacement = null;

	$max = geodir_title_meta_pagenumbering( 'max' );
	$nr  = geodir_title_meta_pagenumbering( 'nr' );

	if ( $max > 1 && $nr > 1 ) {
		$replacement = sprintf( $sep . ' ' . __( 'Page %1$d of %2$d', 'geodirectory' ), $nr, $max );
	}

	return $replacement;
}

/**
 * Retrieve the current page number for use as replacement string.
 *
 * @since   1.6.0
 * @package GeoDirectory
 *
 * @return string|null The current page number.
 */
function geodir_title_meta_pagenumber() {
	$replacement = null;

	$nr = geodir_title_meta_pagenumbering( 'nr' );
	if ( isset( $nr ) && $nr > 0 ) {
		$replacement = (string) $nr;
	}

	return $replacement;
}

/**
 * Retrieve the current page total for use as replacement string.
 *
 * @since   1.6.0
 * @package GeoDirectory
 *
 * @return string|null The current page total.
 */
function geodir_title_meta_pagetotal() {
	$replacement = 0;

	$max = geodir_title_meta_pagenumbering( 'max' );
	if ( isset( $max ) && $max > 0 ) {
		$replacement = (string) $max;
	}

	return $replacement;
}

/**
 * Retrieve the total post found for use as replacement string.
 *
 * @since   1.6.0
 * @package GeoDirectory
 *
 * @return string|null The total post found.
 */
function geodir_title_meta_postcount() {
	$replacement = 0;

	$postcount = geodir_title_meta_pagenumbering( 'postcount' );
	if ( isset( $postcount ) && $postcount > 0 ) {
		$replacement = (string) $postcount;
	}

	return $replacement;
}

/**
 * Determine the page numbering of the current post/page/cpt.
 *
 * @param string $request 'nr'|'max' - whether to return the page number or the max number of pages.
 *
 * @since   1.6.0
 * @package GeoDirectory
 *
 * @global object $wp_query WordPress Query object.
 * @global object $post The current post object.
 *
 * @return int|null The current page numbering.
 */
function geodir_title_meta_pagenumbering( $request = 'nr' ) {
	global $wp_query, $post;
	$max_num_pages = null;
	$page_number   = null;

	$max_num_pages = 1;
	$found_posts = 0;

	if ( ! is_singular() ) {
		$page_number = get_query_var( 'paged' );
		if ( $page_number === 0 || $page_number === '' ) {
			$page_number = 1;
		}

		if ( isset( $wp_query->max_num_pages ) && ( $wp_query->max_num_pages != '' && $wp_query->max_num_pages != 0 ) ) {
			$max_num_pages = $wp_query->max_num_pages;
		}

	} else {
		$page_number = get_query_var( 'page' );
		if ( $page_number === 0 || $page_number === '' ) {
			$page_number = 1;
		}

		if ( isset( $post->post_content ) ) {
			$max_num_pages = ( substr_count( $post->post_content, '<!--nextpage-->' ) + 1 );
		}
	}

	if ( isset( $wp_query->found_posts ) && ( $wp_query->found_posts != '' && $wp_query->found_posts != 0 ) ) {
		$found_posts = $wp_query->found_posts;
	}

	$return = null;

	switch ( $request ) {
		case 'nr':
			$return = $page_number;
			break;
		case 'max':
			$return = $max_num_pages;
			break;
		case 'postcount':
			$return = $found_posts;
			break;
	}

	return $return;
}

/**
 * Filter the terms with count empty.
 *
 * @since 1.5.4
 *
 * @param array $terms Terms array.
 *
 * @return array Terms.
 */
function geodir_filter_empty_terms( $terms ) {
	if ( empty( $terms ) ) {
		return $terms;
	}

	$return = array();
	foreach ( $terms as $term ) {
		if ( isset( $term->count ) && $term->count > 0 ) {
			$return[] = $term;
		} else {
			/**
			 * Allow to filter terms with no count.
			 *
			 * @since 1.6.6
			 *
			 * @param array $return The array of terms to return.
			 * @param object $term The term object.
			 */
			$return = apply_filters( 'geodir_filter_empty_terms_filter', $return, $term );
		}
	}

	return $return;
}

/**
 * Get current WP theme name.
 *
 * @since 2.0.0
 * @package GeoDirectory
 */
function geodir_wp_theme_name() {
	$theme = wp_get_theme();

	return $theme->name;
}

/**
 * Get blog name.
 *
 * @since 2.0.0
 *
 * @return string $blogname.
 */
function geodir_get_blogname() {
	$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

	return apply_filters( 'geodir_get_blogname', $blogname );
}

/**
 * Get blog url
 *
 * @since 2.0.0
 *
 * @return string $blogurl.
 */
function geodir_get_blogurl() {
	$blogurl = home_url( '/' );

	return apply_filters( 'geodir_get_blogurl', $blogurl );
}

/**
 * Checks whether a page id is a GD CPT template page or not.
 *
 * @since   2.0.0.28
 * @since   2.0.0.97 Added $page parameter.
 *
 * @param int $id Page ID.
 * @param string $page Page type. Default empty.
 * @return bool If the page is a GD CPT template page returns true. Otherwise false.
 */
function geodir_is_cpt_template_page( $id, $page = '' ) {
	$cache_key = 'geodir_check_cpt_template_page:' . $id . ': ' . $page;
	$return = wp_cache_get( $cache_key, 'geodir_cpt_template_page' );

	if ( $return !== false ) {
		return (bool)$return;
	}

	$return = 0;

	if ( ! empty( $id ) && ( $pages = geodir_cpt_template_pages( $page ) ) ) {
		if ( in_array( $id, $pages ) ) {
			$return = true;
		}
	}

	$return = apply_filters( 'geodir_is_cpt_template_page', $return, $id, $page );

	wp_cache_set( $cache_key, $return, 'geodir_cpt_template_page' );

	return $return;
}

/**
 * Get CPT template page ids.
 *
 * @since   2.0.0.28
 * @since   2.0.0.97 Added $page parameter.
 *
 * @param string $page Page type. Default empty.
 * @return array CPT template page ids.
 */
function geodir_cpt_template_pages( $page = '' ) {
	if ( ! empty( $page ) ) {
		$page = strpos( $page, 'page_' ) === 0 ? $page : 'page_' . $page;
	}

	$cache_key = 'geodir_cpt_template_pages:' . $page;
	$page_ids = wp_cache_get( $cache_key, 'geodir_cpt_template_page' );

	if ( $page_ids !== false ) {
		return (array) $page_ids;
	}

	$post_types = geodir_get_posttypes( 'object' );
	$pages = array( 'page_add', 'page_details', 'page_archive', 'page_archive_item' );
	if ( ! empty( $page ) ) {
		$pages = array( $page );
	}

	$page_ids = array();

	foreach ( $post_types as $post_type => $post_type_obj ) {
		foreach ( $pages as $_page ) {
			if ( ! empty( $post_type_obj->{$_page} ) ) {
				$page_ids[] = absint( $post_type_obj->{$_page} );
			}
		}
	}

	$page_ids = apply_filters( 'geodir_cpt_template_pages', $page_ids, $page );

	if ( ! empty( $page_ids ) ) {
		$page_ids = array_unique( $page_ids );
	}

	wp_cache_set( $cache_key, $page_ids, 'geodir_cpt_template_page' );

	return $page_ids;
}

/**
 * Get CPT templage page for the CPT.
 *
 * @since   2.0.0.30
 * @package GeoDirectory

 * @return int The page id.
 */
function geodir_cpt_template_page( $page, $post_type ) {
	$default = geodir_get_option( $page );
	$page_id = $default ? $default : '';
	$pages = array( 'page_add', 'page_details', 'page_archive', 'page_archive_item' );

	// Bail if its not a CPT template
	if ( in_array( $page, $pages ) ) {
		$post_types = geodir_get_posttypes( 'array' );

		if ( isset( $post_types[ $post_type ][ $page ] ) && $post_types[ $post_type ][ $page ] ) {
			$page_id = $post_types[ $post_type ][ $page ];
		}
	}

	return $page_id;
}

/**
 * Get post type of the CPT template page id.
 *
 * @since   2.0.0.97 Added $page parameter.
 *
 * @param int $page_id Page id.
 * @param string $page Page type. Default empty.
 * @return string Post type.
 */
function geodir_cpt_template_post_type( $page_id, $page = '' ) {
	if ( ! empty( $page ) ) {
		$page = strpos( $page, 'page_' ) === 0 ? $page : 'page_' . $page;
	}

	$cache_key = 'geodir_cpt_template_post_type:' . $page_id . ':' . $page;
	$post_type = wp_cache_get( $cache_key, 'geodir_cpt_template_post_type' );

	if ( $post_type ) {
		return $post_type;
	}

	$pages = array( 'page_add', 'page_details', 'page_archive', 'page_archive_item' );
	if ( ! empty( $page ) ) {
		$pages = array( $page );
	}

	$post_types = geodir_get_posttypes( 'object' );
	foreach ( $post_types as $_post_type => $post_type_obj ) {
		if ( $post_type ) {
			break;
		}

		foreach ( $pages as $_page ) {
			if ( ! empty( $post_type_obj->{$_page} ) ) {
				$_page_id = absint( $post_type_obj->{$_page} );
				$page_ids = apply_filters( 'geodir_cpt_template_pages', array( $_page_id ), $page );

				if ( in_array( $page_id, $page_ids ) ) {
					$post_type = $_post_type;
					break;
				}
			}
		}
	}

	$post_type = apply_filters( 'geodir_cpt_template_post_type', $post_type, $page_id, $page );

	wp_cache_set( $cache_key, $post_type, 'geodir_cpt_template_post_type' );

	return $post_type;
}

/**
 * Check if we are in an empty archive.
 */
function geodir_is_empty_archive(){
	if(geodir_is_page('archive') || geodir_is_page('post_type')) {
		global $wp_query;

		/*if(
			$wp_query->post_count == 1
			&& ( empty( $wp_query->posts ) || (isset($wp_query->post->post_type) && $wp_query->post->post_type=='page'))
		){*/ // Does not validate for one result.
		if ( empty( $wp_query->found_posts ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Localizes the jQuery UI timepicker.
 *
 * @since 2.0.0.0
 *
 * @global WP_Locale $wp_locale The WordPress date and time locale object.
 */
function geodir_localize_jquery_ui_timepicker() {
	global $wp_locale;

	if ( ! wp_script_is( 'geodir-jquery-ui-timepicker', 'enqueued' ) ) {
		return;
	}

	$timepicker_defaults = wp_json_encode( array(
		'timeOnlyTitle' => __( 'Choose Time', 'geodirectory' ),
		'timeText' => __( 'Time', 'geodirectory' ),
		'hourText' => __( 'Hour', 'geodirectory' ),
		'minuteText' => __( 'Minute', 'geodirectory' ),
		'secondText' => __( 'Second', 'geodirectory' ),
		'millisecText' => __( 'Millisecond', 'geodirectory' ),
		'microsecText' => __( 'Microsecond', 'geodirectory' ),
		'timezoneText' => __( 'Time Zone', 'geodirectory' ),
		'currentText' => __( 'Now', 'geodirectory' ),
		'closeText' => __( 'Done', 'geodirectory' ),
		'amNames' => array( __( 'AM' ), 'A' ),
		'pmNames' => array( __( 'PM' ), 'P' ),
		'isRTL' => (bool) $wp_locale->is_rtl(),
	) );

	$timepicker_defaults = apply_filters( 'geodir_localize_jquery_ui_timepicker_defaults', $timepicker_defaults );
	if ( empty( $timepicker_defaults ) ) {
		return;
	}

	wp_add_inline_script( 'geodir-jquery-ui-timepicker', 'jQuery(function($){$.timepicker.setDefaults(' . $timepicker_defaults . ');});' );
}

/**
 * Get the listing layout view options.
 *
 * @since 2.0.0.60
 * @return mixed|void
 */
/**
 * Get the listing layout view options.
 *
 * @since 2.0.0.60
 * @param bool $frontend If the call is for frontend user select.
 *
 * @return mixed|void
 */
function geodir_get_layout_options($frontend = false){
	$layouts = array(
		"1"        =>  $frontend ? __( 'View: Grid 1', 'geodirectory') : __('Grid View (One Column)', 'geodirectory'),
		"2"        =>  $frontend ? __( 'View: Grid 2', 'geodirectory' ) : __('Grid View (Two Columns)', 'geodirectory'),
		"3"        =>  $frontend ? __( 'View: Grid 3', 'geodirectory' ) : __('Grid View (Three Columns)', 'geodirectory'),
		"4"        =>  $frontend ? __( 'View: Grid 4', 'geodirectory' ) : __('Grid View (Four Columns)', 'geodirectory'),
		"5"        =>  $frontend ? __( 'View: Grid 5', 'geodirectory' ) : __('Grid View (Five Columns)', 'geodirectory'),
		"0"        =>  $frontend ? __( 'View: List', 'geodirectory') : __('List view', 'geodirectory'),
	);

	return apply_filters('geodir_layout_options',$layouts,$frontend);
}


/**
 * If hints are active and shouled be shown.
 *
 * @return bool
 */
function geodir_show_hints(){
	$show = false;
	if(geodir_get_option('enable_hints',1)){
		if(current_user_can('administrator')) {
			$show = true;
		}
	}

	return $show;
}

function geodir_output_hint($hints, $docs_url = '',$video_url = '',$feedback_id = ''){
	$html = geodir_format_hints($hints, $docs_url,$video_url,$feedback_id);
	$notifications = array();
	$notifications[$feedback_id] = array(
		'type'  =>  'info',
		'note'  =>  $html,
		'dismissible'  =>  true
	);
	return geodir_notification( $notifications );
}

function geodir_format_hints($hints, $docs_url = '',$video_url = '',$feedback_id = ''){
	$text = '';

	if(is_array($hints)){
		$text .= "<ul class='gd-hints-list' style='margin: 0;padding: 0 2em;'>";
		foreach($hints as $hint){
			$text .= "<li>".$hint."</li>";
		}
		$text .= "</ul>";
	}else{
		$text .= $hints;
	}

	$feedback_url = $feedback_id ? "https://wpgeodirectory.com/support/forum/geodirectory-core-plugin-forum/general-discussion/?feedback=$feedback_id#new-post" : "https://wpgeodirectory.com/support/forum/geodirectory-core-plugin-forum/general-discussion/#new-post";

	if($text){
		$help_links = array();
		$help_links[] = "<a href='".admin_url( 'admin.php?page=gd-settings&tab=general&section=developer#enable_hints' )."'>".__("Disable hints","geodirectory")."</a>";
		$docs_url ? $help_links[] = "<i class=\"fas fa-book\"></i> <a href='$docs_url' target='_blank'>".__("Documentation","geodirectory")."</a>" : '';
		$video_url ? $help_links[] = "<i class=\"fas fa-video\"></i> <a href='$video_url' target='_blank' data-lity>".__("Video","geodirectory")."</a>" : '';
		$feedback_id ? $help_links[] = "<i class=\"fas fa-comment\"></i> <a href='$feedback_url' target='_blank'>".__("Feedback","geodirectory")."</a>" : '';
		$text = "<b>".__("Admin Hints:","geodirectory")."</b> " . implode(" | ",$help_links) . $text;
	}

	return $text;
}

/**
 * @since 2.0.0.74
 */
function geodir_create_nonce( $action = -1 ) {
	do_action( 'geodir_create_nonce_before', $action );

	$nonce = wp_create_nonce( $action );

	do_action( 'geodir_create_nonce_after', $action, $nonce );

	return apply_filters( 'geodir_create_nonce', $nonce, $action );
}

/**
 * @since 2.0.0.74
 */
function geodir_nonce_token( $action = -1, $uid = 0 ) {
	$token = wp_get_session_token();
	$i = wp_nonce_tick();

	return substr( wp_hash( $i . '|' . $action . '|' . $uid . '|' . $token, 'nonce' ), -12, 10 );
}

/**
 * Show post distance with unit.
 *
 * @since 2.0.0.96
 *
 * @param float $distance Distance.
 * @param string $short_unit Short distance unit. meters or feet.
 * @return string Distance with unit.
 */
function geodir_show_distance( $distance, $short_unit = '' ) {
	$unit = geodir_get_option( 'search_distance_long' );

	if ( $unit != 'km' && $unit != 'miles' ) {
		$unit = 'miles';
	}

	if ( $short_unit != 'feet' && $short_unit != 'meters' ) {
		$short_unit = geodir_get_option( 'search_distance_short' );

		if ( $short_unit != 'feet' && $short_unit != 'meters' ) {
			$short_unit = 'feet';
		}
	}

	$_distance = $distance;
	$_unit = $unit;

	if ( $unit == 'km' ) {
		if ( round( $distance, 3 ) < 0.1 ) {
			if ( $short_unit == 'meters' ) {
				$_distance = round( round( $distance * 1000, 3 ) ); // km => meters
				$_unit = __( 'meters', 'geodirectory' );
			} elseif ( $short_unit == 'feet' ) {
				$_distance = round( round( $distance * 3280.8399, 3 ) ); // km => feet
				$_unit = __( 'feet', 'geodirectory' );
			} else {
				$_distance =  round( $distance, 2 );
			}
		} else {
			$_distance =  round( $distance, 2 );
		}
	} elseif ( $unit == 'miles' ) {
		if ( round( $distance, 3 ) < 0.1 ) {
			if ( $short_unit == 'meters' ) {
					$_distance = round( round( $distance * 1609.344, 3 ) ); // miles => meters
					$_unit = __( 'meters', 'geodirectory' );
			} elseif ( $short_unit == 'feet' ) {
				$_distance = round( round( $distance * 5280, 3 ) ); // miles => feet
				$_unit = __( 'feet', 'geodirectory' );
			} else {
				$_distance =  round( $distance, 2 );
			}
		} else {
			$_distance =  round( $distance, 2 );
		}
	} else {
		$_distance =  round( $distance, 2 );
	}

	/**
	* Filter to change format of distance.
	*
	* @param $_distance distance value
	* @param $_unit unit of distance.
	*/
	$content = apply_filters( 'geodir_show_distance', $_distance . ' ' .  $_unit, $_distance, $_unit );

	return $content;
}

/**
 * Get the design style for the site.
 *
 * This determines what template files are used.
 *
 * @since 2.1.0
 * @return mixed
 */
function geodir_design_style(){
	return geodir_get_option("design_style",'bootstrap');
}

/**
 * Check if Gutenberg is in use.
 *
 * @since 2.1.0
 * @return bool True if site uses Gutenberg else False.
 */
function geodir_is_gutenberg() {
	global $wp_version;

	$is_gutenberg = true;

	// If less than v5.
	if ( version_compare( $wp_version, '5.0.0', '<' ) ) {
		$is_gutenberg = false;
	}

	if ( class_exists( 'Classic_Editor' ) ) {
		$is_gutenberg = false; // Classic Editor plugin is active.
	} else if ( geodir_is_classicpress() ) {
		$is_gutenberg = false; // Site is using ClassicPress.
	}

	return $is_gutenberg;
}

/**
 * Check if ClassicPress is in use.
 *
 * @since 2.3.87
 * @return bool True if site uses ClassicPress else False.
 */
function geodir_is_classicpress() {
	if ( function_exists( 'classicpress_version' ) ) {
		$is_classicpress = true;
	} else {
		$is_classicpress = false;
	}

	return $is_classicpress;
}

/**
 * Map lazy load..
 *
 * @since 2.1.0.0
 *
 * @return string Map lazy load type.
 */
function geodir_lazy_load_map() {
	return GeoDir_Maps::lazy_load_map();
}

/**
 * Array of map parameters.
 *
 * @since 2.1.0.0
 *
 * @return array Map params array.
 */
function geodir_map_params() {
	return GeoDir_Maps::get_map_params();
}

/**
 * Get the cache prefix.
 *
 * @since 2.3.5
 *
 * @return string Cache prefix.
 */
function geodir_cache_prefix() {
	$cache_prefix = 'geodir_cache_';

	return apply_filters( 'geodir_cache_prefix', $cache_prefix );
}

/**
 * Saves the data to the cache.
 *
 * @since 2.3.5
 *
 * @param int|string $key    The cache key to use for retrieval later.
 * @param mixed      $data   The contents to store in the cache.
 * @param string     $group  Optional. Where to group the cache contents. Enables the same key
 *                           to be used across groups. Default empty.
 * @param int        $expire Optional. When to expire the cache contents, in seconds.
 *                           Default 0 (no expiration).
 * @return bool True on success, false on failure.
 */
function geodir_cache_set( $key, $data, $group = '', $expire = 0 ) {
	$key = geodir_cache_prefix() . $key;

	if ( ! ( function_exists( 'wp_cache_supports' ) && wp_cache_supports( 'flush_group' ) ) ) {
		$groups = wp_cache_get( 'geodir_cache_groups' );

		if ( ! is_array( $groups ) ) {
			$groups = array();
		}

		$_group = ! empty( $group ) ? $group : 'geodir_no_group';

		if ( empty( $groups[ $_group ] ) ) {
			$groups[ $_group ] = array();
		}

		if ( ! in_array( $key , $groups[ $_group ] ) ) {
			$groups[ $_group ][] = $key ;

			wp_cache_set( 'geodir_cache_groups', $groups );
		}
	}

	return wp_cache_set( $key, $data, $group, $expire );
}

/**
 * Retrieves the cache contents from the cache by key and group.
 *
 * @since 2.3.5
 *
 * @param int|string $key   The key under which the cache contents are stored.
 * @param string     $group Optional. Where the cache contents are grouped. Default empty.
 * @param bool       $force Optional. Whether to force an update of the local cache
 *                          from the persistent cache. Default false.
 * @param bool       $found Optional. Whether the key was found in the cache (passed by reference).
 *                          Disambiguates a return of false, a storable value. Default null.
 * @return mixed|false The cache contents on success, false on failure to retrieve contents.
 */
function geodir_cache_get( $key, $group = '', $force = false, &$found = null ) {
	$key = geodir_cache_prefix() . $key;

	return wp_cache_get( $key, $group, $force, $found );
}

/**
 * Removes the cache contents matching key and group.
 *
 * @since 2.3.5
 *
 * @param int|string $key   What the contents in the cache are called.
 * @param string     $group Optional. Where the cache contents are grouped. Default empty.
 * @return bool True on successful removal, false on failure.
 */
function geodir_cache_delete( $key, $group = '' ) {
	$key = geodir_cache_prefix() . $key;

	return wp_cache_delete( $key, $group );
}

/**
 * Removes all cache items.
 *
 * @since 2.3.5
 *
 * @return bool True on success, false on failure.
 */
function geodir_cache_flush() {
	return wp_cache_flush();
}

/**
 * Removes all cache items in a group, if the object cache implementation supports it.
 *
 * @since 2.3.5
 *
 * @param string $group Name of group to remove from cache.
 * @return bool True if group was flushed, false otherwise.
 */
function geodir_cache_flush_group( $group ) {
	if ( function_exists( 'wp_cache_supports' ) && wp_cache_supports( 'flush_group' ) ) {
		return wp_cache_flush_group( $group );
	} else {
		$groups = wp_cache_get( 'geodir_cache_groups' );
		$_group = ! empty( $group ) ? $group : 'geodir_no_group';

		if ( ! empty( $groups ) && is_array( $groups ) && ! empty( $groups[ $_group ] ) && is_array( $groups[ $_group ] ) ) {
			foreach ( $groups[ $_group ] as $cache_key ) {
				if ( $cache_key ) {
					wp_cache_delete( $cache_key, $group );
				}
			}
		}
	}
}

/**
 * Merge missing categories in details table.
 *
 * @since 2.3.57
 *
 * @return int No. of updated items.
 */
function geodir_merge_missing_terms( $post_types = array() ) {
	global $wpdb;

	$post_types = ! empty( $post_types ) && is_array( $post_types ) ? $post_types : geodir_get_posttypes();
	$updated = 0;

	foreach ( $post_types as $post_type ) {
		$table = geodir_db_cpt_table( $post_type );

		$results = $wpdb->get_results( $wpdb->prepare( "SELECT p.ID, pd.post_category, pd.default_category, pd.post_tags FROM {$wpdb->posts} AS p INNER JOIN {$table} pd ON pd.post_id = p.ID WHERE p.post_type = %s AND p.post_status NOT IN( 'draft', 'auto-draft', 'trash', 'inherit' ) AND ( pd.post_category IS NULL OR pd.post_category = '' ) ORDER BY p.ID ASC", $post_type ) );

		if ( ! empty( $results ) ) {
			foreach ( $results as $k => $row ) {
				$_results = $wpdb->get_results( $wpdb->prepare( "SELECT t.term_id, t.name, tt.taxonomy FROM {$wpdb->term_relationships} AS tr LEFT JOIN {$wpdb->term_taxonomy} AS tt ON tt.term_taxonomy_id = tr.term_taxonomy_id LEFT JOIN {$wpdb->terms} AS t ON t.term_id = tt.term_id WHERE ( tt.taxonomy = %s OR tt.taxonomy = %s ) AND tr.object_id = %d ORDER BY t.name ASC", $post_type . 'category', $post_type . '_tags', $row->ID ) );

				if ( ! empty( $_results ) ) {
					$cats = array();
					$tags = array();

					foreach ( $_results as $_k => $_row ) {
						if ( $_row->taxonomy == $post_type . 'category' ) {
							$cats[] = $_row->term_id;
						} else if ( $_row->taxonomy == $post_type . '_tags' ) {
							$tags[] = $_row->name;
						}
					}

					$data = array();
					$format = array();

					if ( ! empty( $cats ) ) {
						$data['post_category'] = ',' . implode( ",", $cats ) . ',';
						$format[] = '%s';

						if ( empty( $row->default_category ) ) {
							$data['default_category'] = $cats[0];
							$format[] = '%d';
						}
					}

					if ( ! empty( $tags ) ) {
						$post_tags = implode( ",", $tags );

						if ( $post_tags != $post_tags ) {
							$data['post_tags'] = $row->post_tags;
							$format[] = '%s';
						}
					}

					if ( ! empty( $data ) ) {
						$_updated = $wpdb->update( $table, $data, array( 'post_id' => $row->ID ), $format, array( '%d' ) );

						if ( $_updated ) {
							$updated++;

							clean_post_cache( $row->ID );
						}
					}
				}
			}
		}
	}

	return $updated;
}

/**
 * Check to load map scripts on call.
 *
 * @since 2.3.87
 *
 * @return bool True to enable load map scripts on call else false.
 */
function geodir_load_scripts_on_call() {
	global $ayecode_ui_settings;

	// Disable scripts on call.
	if ( geodir_get_option( 'disable_scripts_on_call' ) && empty( $_REQUEST['_force_oncall_scripts'] ) ) {
		return;
	}

	$active = wp_doing_ajax() || wp_doing_cron() || ! is_admin();

	if ( $active ) {
		if ( ! ( ! empty( $ayecode_ui_settings ) && is_object( $ayecode_ui_settings ) ) && class_exists( 'AyeCode_UI_Settings' ) ) {
			$ayecode_ui_settings = AyeCode_UI_Settings::instance();
		}

		if ( ! empty( $ayecode_ui_settings ) && is_callable( array( $ayecode_ui_settings, 'is_preview' ) ) && $ayecode_ui_settings->is_preview() ) {
			$active = false;
		}
	}

	if ( ! empty( $_REQUEST['_force_load_scripts'] ) ) {
		$active = false; // @todo for debug.
	}

	return apply_filters( 'geodir_load_scripts_on_call', $active );
}

/**
 * Enqueue widget scripts from widget call.
 *
 * @since 2.3.87
 *
 * @return mixed
 */
function geodir_widget_enqueue_scripts( $args, $widget = array(), $extra = array() ) {
	do_action( 'geodir_enqueue_scripts_on_call_before', $args, $widget, $extra );

	if ( ! empty( $widget ) && is_object( $widget ) && ! empty( $widget->id_base ) ) {
		$design_style = geodir_design_style();
		$lazy_load_map = geodir_lazy_load_map();
		$lazy_load_scripts = geodir_load_scripts_on_call();
		$map_api = GeoDir_Maps::active_map();

		do_action( 'geodir_widget_enqueue_scripts_on_call_before', $lazy_load_scripts, $args, $widget, $extra );

		if ( $widget->id_base == 'gd_map' ) {
			// GD > Map
			if ( $lazy_load_map ) {
				// Lazy Load
				wp_enqueue_script( 'geodir-map' );
				wp_localize_script( 'geodir-map', $args['map_canvas'], $args );
				wp_add_inline_script( 'geodir-map', GeoDir_Maps::google_map_callback(), 'before' );
			} else {
				if ( $lazy_load_scripts ) {
					if ( in_array( $map_api, array( 'auto', 'google' ) ) ) {
						if ( ! wp_script_is( 'geodir-google-maps', 'enqueued' ) ) {
							GeoDir_Frontend_Scripts::enqueue_script( 'geodir-google-maps' );
							GeoDir_Frontend_Scripts::enqueue_script( 'geodir-g-overlappingmarker' );

							wp_add_inline_script( 'geodir-google-maps', GeoDir_Maps::google_map_callback(), 'before' );
						}
					} else if ( $map_api == 'osm' ) {
						if ( ! wp_style_is( 'leaflet', 'enqueued' ) ) {
							GeoDir_Frontend_Scripts::enqueue_style( 'leaflet' );
						}

						if ( ! wp_script_is( 'geodir-leaflet', 'enqueued' ) ) {
							GeoDir_Frontend_Scripts::enqueue_script( 'geodir-leaflet' );
							GeoDir_Frontend_Scripts::enqueue_script( 'geodir-leaflet-geo' );
						}

						if ( ! empty( $args['map_directions'] ) && $args['map_type'] == 'post' && ! wp_script_is( 'leaflet-routing-machine', 'enqueued' ) ) {
							GeoDir_Frontend_Scripts::enqueue_style( 'leaflet-routing-machine' );
							GeoDir_Frontend_Scripts::enqueue_script( 'leaflet-routing-machine' );
						}

						if ( ! wp_script_is( 'geodir-o-overlappingmarker', 'enqueued' ) ) {
							GeoDir_Frontend_Scripts::enqueue_script( 'geodir-o-overlappingmarker' );
						}
					}

					do_action( 'geodir_widget_map_scripts_on_call', $args );

					if ( $map_api != 'none' && ! wp_script_is( 'geodir-goMap', 'enqueued' ) ) {
						GeoDir_Frontend_Scripts::enqueue_script( 'geodir-goMap' );

						if ( $footer_script = GeoDir_Maps::footer_script() ) {
							wp_add_inline_script( 'geodir-goMap', $footer_script, 'before' );
						}
					}
				}

				if ( ! wp_script_is( 'geodir-map-widget', 'enqueued' ) ) {
					GeoDir_Frontend_Scripts::enqueue_script( 'geodir-map-widget' );
				}

				wp_localize_script( 'geodir-map-widget', $args['map_canvas'], $args );
			}
		} else if ( $widget->id_base == 'gd_search' || $widget->id_base == 'gd_location_switcher' ) {
			// GD > Search, GD > Location Switcher
			if ( ! $lazy_load_map && $lazy_load_scripts ) {
				// Auto / Google
				if ( in_array( $map_api, array( 'auto', 'google' ) ) ) {
					if ( ! wp_script_is( 'geodir-google-maps', 'enqueued' ) ) {
						GeoDir_Frontend_Scripts::enqueue_script( 'geodir-google-maps' );
						GeoDir_Frontend_Scripts::enqueue_script( 'geodir-g-overlappingmarker' );

						wp_add_inline_script( 'geodir-google-maps', GeoDir_Maps::google_map_callback(), 'before' );
					}
				} else if ( $map_api == 'osm' ) {
					if ( ! wp_style_is( 'leaflet', 'enqueued' ) ) {
						GeoDir_Frontend_Scripts::enqueue_style( 'leaflet' );
					}

					if ( ! wp_script_is( 'geodir-leaflet', 'enqueued' ) ) {
						GeoDir_Frontend_Scripts::enqueue_script( 'geodir-leaflet' );
						GeoDir_Frontend_Scripts::enqueue_script( 'geodir-leaflet-geo' );
					}
				}

				if ( $map_api != 'none' && ! wp_script_is( 'geodir-goMap', 'enqueued' ) ) {
					GeoDir_Frontend_Scripts::enqueue_script( 'geodir-goMap' );

					if ( $footer_script = GeoDir_Maps::footer_script() ) {
						wp_add_inline_script( 'geodir-goMap', $footer_script, 'before' );
					}
				}
			}
		} else if ( $widget->id_base == 'gd_add_listing' ) {
			if ( ! isset( $_REQUEST['ct_builder'] ) ) {
				if ( ! wp_script_is( 'geodir-plupload', 'enqueued' ) ) {
					GeoDir_Frontend_Scripts::enqueue_script( 'geodir-plupload' );
				}

				if ( ! wp_script_is( 'geodir-add-listing', 'enqueued' ) ) {
					GeoDir_Frontend_Scripts::enqueue_script( 'geodir-add-listing' );
				}

				if ( ! $design_style ) {
					GeoDir_Frontend_Scripts::enqueue_script( 'geodir-jquery-ui-timepicker' );
					wp_enqueue_script( 'jquery-ui-autocomplete' );
				}
			}

			// GD > Add Listing// GD > Add Listing
			if ( $lazy_load_map ) {
				if ( $map_api != 'none' && ! wp_script_is( 'geodir-map', 'enqueued' ) ) {
					GeoDir_Frontend_Scripts::enqueue_script( 'geodir-map' );

					wp_add_inline_script( 'geodir-map', GeoDir_Maps::google_map_callback(), 'before' );
				}
			} else if ( ! $lazy_load_map && $lazy_load_scripts ) {
				// Auto, Google
				if ( in_array( $map_api, array( 'auto', 'google' ) ) ) {
					if ( ! wp_script_is( 'geodir-google-maps', 'enqueued' ) ) {
						GeoDir_Frontend_Scripts::enqueue_script( 'geodir-google-maps' );
						GeoDir_Frontend_Scripts::enqueue_script( 'geodir-g-overlappingmarker' );

						wp_add_inline_script( 'geodir-google-maps', GeoDir_Maps::google_map_callback(), 'before' );
					}
				} else if ( $map_api == 'osm' ) {
					if ( ! wp_style_is( 'leaflet', 'enqueued' ) ) {
						GeoDir_Frontend_Scripts::enqueue_style( 'leaflet' );
					}

					if ( ! wp_script_is( 'geodir-leaflet', 'enqueued' ) ) {
						GeoDir_Frontend_Scripts::enqueue_script( 'geodir-leaflet' );
						GeoDir_Frontend_Scripts::enqueue_script( 'geodir-leaflet-geo' );
					}
				}

				if ( $map_api != 'none' && ! wp_script_is( 'geodir-goMap', 'enqueued' ) ) {
					GeoDir_Frontend_Scripts::enqueue_script( 'geodir-goMap' );

					if ( $footer_script = GeoDir_Maps::footer_script() ) {
						wp_add_inline_script( 'geodir-goMap', $footer_script, 'before' );
					}
				}
			}
		}

		do_action( 'geodir_widget_enqueue_scripts_on_call_after', $lazy_load_scripts, $args, $widget, $extra );
	}

	do_action( 'geodir_enqueue_scripts_on_call_after', $args, $widget, $extra );
}
