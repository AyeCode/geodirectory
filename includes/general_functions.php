<?php
/**
 * Plugin general functions
 *
 * @since   1.0.0
 * @package GeoDirectory
 */


/**
 * Get All Plugin functions from WordPress
 */
//include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

/*-----------------------------------------------------------------------------------*/
/* Helper functions */
/*-----------------------------------------------------------------------------------*/


function geodir_get_ajax_url()
{
    return admin_url('admin-ajax.php');
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
 * @param string $url                  The main url to be used.
 * @param array $params                The arguments array.
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
 * Returns add listing page link.
 *
 * @since   1.0.0
 * @package GeoDirectory
 * @global object $wpdb     WordPress Database object.
 *
 * @param string $post_type The post type.
 *
 * @return string Listing page url if valid. Otherwise home url will be returned.
 */
function geodir_get_addlisting_link( $post_type = '' ) {
	global $wpdb;

	//$check_pkg  = $wpdb->get_var("SELECT pid FROM ".GEODIR_PRICE_TABLE." WHERE post_type='".$post_type."' and status != '0'");
	$check_pkg = 1;
	if ( post_type_exists( $post_type ) && $check_pkg ) {

		$add_listing_link = get_page_link( geodir_add_listing_page_id() );

		return esc_url( add_query_arg( array( 'listing_type' => $post_type ), $add_listing_link ) );
	} else {
		return get_bloginfo( 'url' );
	}
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
	$pageURL = 'http';
	if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on')) {
		$pageURL .= "s";
	}
	$pageURL .= "://";
	
	/*
	 * Since we are assigning the URI from the server variables, we first need
	 * to determine if we are running on apache or IIS.  If PHP_SELF and REQUEST_URI
	 * are present, we will assume we are running on apache.
	 */
	if (!empty($_SERVER['PHP_SELF']) && !empty($_SERVER['REQUEST_URI'])) {
		// To build the entire URI we need to prepend the protocol, and the http host
		// to the URI string.
		$pageURL .= $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	} else {
		/*
		 * Since we do not have REQUEST_URI to work with, we will assume we are
		 * running on IIS and will therefore need to work some magic with the SCRIPT_NAME and
		 * QUERY_STRING environment variables.
		 *
		 * IIS uses the SCRIPT_NAME variable instead of a REQUEST_URI variable... thanks, MS
		 */
		$pageURL .= $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
		
		// If the query string exists append it to the URI string
		if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
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
		__( 'Sunday', 'geodirectory' ),
		__( 'Monday', 'geodirectory' ),
		__( 'Tuesday', 'geodirectory' ),
		__( 'Wednesday', 'geodirectory' ),
		__( 'Thursday', 'geodirectory' ),
		__( 'Friday', 'geodirectory' ),
		__( 'Saturday', 'geodirectory' )
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
 * @since   1.0.0
 * @since   1.5.6 Added to check GD invoices and GD checkout pages.
 * @since   1.5.7 Updated to validate buddypress dashboard listings page as a author page.
 * @package GeoDirectory
 * @global object $wp_query WordPress Query object.
 * @global object $post     The current post object.
 *
 * @param string $gdpage    The page type.
 *
 * @return bool If valid returns true. Otherwise false.
 */
function geodir_is_page( $gdpage = '' ) {

	global $wp_query, $post, $wp;
	//if(!is_admin()):
	$page_id = '';// get_query_var( 'page_id' ) ? get_query_var( 'page_id' ) : '';
	//echo $page_id .'xxx';
	if(empty($page_id) && $wp_query->is_page && $wp_query->queried_object_id){
		$page_id = $wp_query->queried_object_id;
	}
//echo $gdpage.'###'.$page_id ;
	//print_r($wp_query);

	switch ( $gdpage ):
		case 'add-listing':

			if ( is_page() && $page_id == geodir_add_listing_page_id() ) {
				return true;
			} elseif ( is_page() && isset( $post->post_content ) && has_shortcode( $post->post_content, 'gd_add_listing' ) ) {
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
		case 'listing-success':// @depreciated
			if ( is_page() && $page_id === geodir_success_page_id() ) {
				return true;
			}
			break;
		case 'single':
		case 'detail': // @depreciated
			$post_type = get_query_var( 'post_type' );
			if ( is_array( $post_type ) ) {
				$post_type = reset( $post_type );
			}
			if ( is_single() && in_array( $post_type, geodir_get_posttypes() ) ) {
				return true;
			}
			break;
		case 'post_type':
		case 'pt': // @depreciated
			$post_type = get_query_var( 'post_type' );
			if ( is_array( $post_type ) ) {
				$post_type = reset( $post_type );
			}
			if ( is_post_type_archive() && in_array( $post_type, geodir_get_posttypes() ) && ! is_tax() ) {
				return true;
			}

			break;
		case 'archive':
		case 'listing':// @depreciated
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
			if ( is_author()) {
				return true;
			}

			if ( function_exists( 'bp_loggedin_user_id' ) && function_exists( 'bp_displayed_user_id' ) && $my_id = (int) bp_loggedin_user_id() ) {
				if ( ( (bool) bp_is_current_component( 'listings' ) || (bool) bp_is_current_component( 'favorites' ) ) && $my_id > 0 && $my_id == (int) bp_displayed_user_id() ) {
					return true;
				}
			}
			break;
		case 'search':
//			if ( is_search() && isset( $_REQUEST['geodir_search'] ) ) {
//				return true;
//			}
//			if ( (is_page() && $page_id == geodir_search_page_id()) || (is_archive() && $page_id == geodir_search_page_id()) ) {
//				return true;
//			}

			if ( isset( $_REQUEST['geodir_search'] ) ) {
				return true;
			}
			break;
		case 'info': // @depreciated
			if ( is_page() && $page_id && $page_id == geodir_info_page_id() ) {
				return true;
			}
			break;
		case 'login': // @depreciated
			if ( is_page() && $page_id === geodir_login_page_id() ) {
				return true;
			}
			break;
		case 'checkout':
			if ( is_page() && function_exists( 'geodir_payment_checkout_page_id' ) && $page_id == geodir_payment_checkout_page_id() ) {
				return true;
			}
			break;
		case 'invoices':
			if ( is_page() && function_exists( 'geodir_payment_invoices_page_id' ) && $page_id == geodir_payment_invoices_page_id() ) {
				return true;
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
		//$wp->query_vars['gd_is_geodir_page'] = false;
		//print_r()
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
				$wp->query_vars['page_id'] == geodir_add_listing_page_id()
				|| $wp->query_vars['page_id'] == geodir_preview_page_id()
				|| $wp->query_vars['page_id'] == geodir_success_page_id()
				|| $wp->query_vars['page_id'] == geodir_location_page_id()
				|| ( function_exists( 'geodir_payment_checkout_page_id' ) && $wp->query_vars['page_id'] == geodir_payment_checkout_page_id() )
				|| ( function_exists( 'geodir_payment_invoices_page_id' ) && $wp->query_vars['page_id'] == geodir_payment_invoices_page_id() )
			) {
				$wp->query_vars['gd_is_geodir_page'] = true;
			}
		}

		if ( ! isset( $wp->query_vars['gd_is_geodir_page'] ) && isset( $wp->query_vars['pagename'] ) ) {
			$page = get_page_by_path( $wp->query_vars['pagename'] );

			if ( ! empty( $page ) && (
					$page->ID == geodir_add_listing_page_id()
					|| $page->ID == geodir_preview_page_id()
					|| $page->ID == geodir_success_page_id()
					|| $page->ID == geodir_location_page_id()
					|| ( isset( $wp->query_vars['page_id'] ) && function_exists( 'geodir_payment_checkout_page_id' ) && $wp->query_vars['page_id'] == geodir_payment_checkout_page_id() )
					|| ( isset( $wp->query_vars['page_id'] ) && function_exists( 'geodir_payment_invoices_page_id' ) && $wp->query_vars['page_id'] == geodir_payment_invoices_page_id() )
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


//check if homepage
		if ( ! isset( $wp->query_vars['gd_is_geodir_page'] )
		     && ! isset( $wp->query_vars['page_id'] )
		     && ! isset( $wp->query_vars['pagename'] )
		     && is_page_geodir_home()
		) {
			$wp->query_vars['gd_is_geodir_page'] = true;
		}
		//echo $wp->query_vars['gd_is_geodir_page'] ;
		/*echo "<pre>" ;
		print_r($wp) ;
		echo "</pre>" ;
	//	exit();
			*/
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


if ( ! function_exists( 'createRandomString' ) ) {
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
}

if ( ! function_exists( 'geodir_getDistanceRadius' ) ) {
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

		return $earthMeanRadius;
	}
}


if ( ! function_exists( 'geodir_calculateDistanceFromLatLong' ) ) {
	/**
	 * Calculate the great circle distance between two points identified by longitude and latitude.
	 *
	 * @since   1.0.0
	 * @package GeoDirectory
	 *
	 * @param array $point1 Latitude and Longitude of point 1.
	 * @param array $point2 Latitude and Longitude of point 2.
	 * @param string $uom   Unit of measurement.
	 *
	 * @return float The distance.
	 */
	function geodir_calculateDistanceFromLatLong( $point1, $point2, $uom = 'km' ) {
//	Use Haversine formula to calculate the great circle distance between two points identified by longitude and latitude

		$earthMeanRadius = geodir_getDistanceRadius( $uom );

		$deltaLatitude  = deg2rad( (float) $point2['latitude'] - (float) $point1['latitude'] );
		$deltaLongitude = deg2rad( (float) $point2['longitude'] - (float) $point1['longitude'] );
		$a              = sin( $deltaLatitude / 2 ) * sin( $deltaLatitude / 2 ) +
		                  cos( deg2rad( (float) $point1['latitude'] ) ) * cos( deg2rad( (float) $point2['latitude'] ) ) *
		                  sin( $deltaLongitude / 2 ) * sin( $deltaLongitude / 2 );
		$c              = 2 * atan2( sqrt( $a ), sqrt( 1 - $a ) );
		$distance       = $earthMeanRadius * $c;

		return $distance;

	}
}


/**
 * Generates breadcrumb for taxonomy (category, tags etc.) pages.
 *
 * @since   1.0.0
 * @package GeoDirectory
 */
function geodir_taxonomy_breadcrumb() {

	$term   = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
	$parent = $term->parent;

	while ( $parent ):
		$parents[]  = $parent;
		$new_parent = get_term_by( 'id', $parent, get_query_var( 'taxonomy' ) );
		$parent     = $new_parent->parent;
	endwhile;

	if ( ! empty( $parents ) ):
		$parents = array_reverse( $parents );

		foreach ( $parents as $parent ):
			$item = get_term_by( 'id', $parent, get_query_var( 'taxonomy' ) );
			$url  = get_term_link( $item, get_query_var( 'taxonomy' ) );
			echo '<li> > <a href="' . $url . '">' . $item->name . '</a></li>';
		endforeach;

	endif;

	echo '<li> > ' . $term->name . '</li>';
}

function geodir_wpml_post_type_archive_link($link, $post_type){
	if (function_exists('icl_object_id')) {
		$post_types   = geodir_get_posttypes();
		
		if ( isset( $post_types[ $post_type ] ) ) {
			$slug = $post_types[ $post_type ]['rewrite']['slug'];

			// Alter the CPT slug if WPML is set to do so
			if ( geodir_wpml_is_post_type_translated( $post_type ) ) {
				if ( geodir_wpml_slug_translation_turned_on( $post_type ) && $language_code = geodir_wpml_get_lang_from_url( $link) ) {

					$org_slug = $slug;
					$slug     = apply_filters( 'wpml_translate_single_string',
						$slug,
						'WordPress',
						'URL slug: ' . $slug,
						$language_code );
                    
					if ( ! $slug ) {
						$slug = $org_slug;
					} else {
						$link = str_replace( $org_slug, $slug, $link );
					}
				}
			}
		}
	}

	return $link;
}
add_filter( 'post_type_archive_link','geodir_wpml_post_type_archive_link', 1000, 2);



/**
 * Move Images from a remote url to upload directory.
 *
 * @since   1.0.0
 * @package GeoDirectory
 *
 * @param string $url The remote image url.
 *
 * @return array|WP_Error The uploaded data as array. When failure returns error.
 */
function fetch_remote_file( $url ) {
	exit; //@todo this function shoudl no longer be needed
	// extract the file name and extension from the url
	require_once( ABSPATH . 'wp-includes/pluggable.php' );
	$file_name = basename( $url );
	if ( strpos( $file_name, '?' ) !== false ) {
		list( $file_name ) = explode( '?', $file_name );
	}
	$dummy        = false;
	$add_to_cache = false;
	$key          = null;
	if ( strpos( $url, '/dummy/' ) !== false ) {
		$dummy = true;
		$key   = "dummy_" . str_replace( '.', '_', $file_name );
		$value = get_transient( 'cached_dummy_images' );
		if ( $value ) {
			if ( isset( $value[ $key ] ) ) {
				return $value[ $key ];
			} else {
				$add_to_cache = true;
			}
		} else {
			$add_to_cache = true;
		}
	}

	// get placeholder file in the upload dir with a unique, sanitized filename

	$post_upload_date = isset( $post['upload_date'] ) ? $post['upload_date'] : '';

	$upload = wp_upload_bits( $file_name, 0, '', $post_upload_date );
	if ( $upload['error'] ) {
		return new WP_Error( 'upload_dir_error', $upload['error'] );
	}


	sleep( 0.3 );// if multiple remote file this can cause the remote server to timeout so we add a slight delay

	// fetch the remote url and write it to the placeholder file
	$headers = wp_remote_get( $url, array( 'stream' => true, 'filename' => $upload['file'] ) );

	$log_message = '';
	if ( is_wp_error( $headers ) ) {
		geodir_error_log( $headers->get_error_message(), 'import_file_error (' . $url . ')', __FILE__, __LINE__ );

		return new WP_Error( 'import_file_error', $headers->get_error_message() );
	}

	// clear cache to make compat with EWWW Image Optimizer
	if(defined( 'EWWW_IMAGE_OPTIMIZER_PLUGIN_FILE')){
		clearstatcache();
	}
	$filesize = filesize( $upload['file'] );
	// request failed
	if ( ! $headers ) {
		$log_message = __( 'Remote server did not respond', 'geodirectory' );
	} // make sure the fetch was successful
	elseif ( $headers['response']['code'] != '200' ) {
		$log_message = sprintf( __( 'Remote server returned error response %1$d %2$s', 'geodirectory' ), esc_html( $headers['response'] ), get_status_header_desc( $headers['response'] ) );
	} elseif ( isset( $headers['headers']['content-length'] ) && $filesize != $headers['headers']['content-length'] ) {
		$log_message = __( 'Remote file is incorrect size', 'geodirectory' );
	} elseif ( 0 == $filesize ) {
		$log_message = __( 'Zero size file downloaded', 'geodirectory' );
	}

	if ( $log_message ) {
		$del = unlink( $upload['file'] );
		if ( ! $del ) {
			geodir_error_log( __( 'GeoDirectory: fetch_remote_file() failed to delete temp file.', 'geodirectory' ) );
		}

		return new WP_Error( 'import_file_error', $log_message );
	}

	if ( $dummy && $add_to_cache && is_array( $upload ) ) {
		$images = get_transient( 'cached_dummy_images' );
		if ( is_array( $images ) ) {
			$images[ $key ] = $upload;
		} else {
			$images = array( $key => $upload );
		}

		//setting the cache using the WP Transient API
		set_transient( 'cached_dummy_images', $images, 60 * 10 ); //10 minutes cache
	}

	return $upload;
}

/**
 * Get maximum file upload size.
 *
 * @since   1.0.0
 * @package GeoDirectory
 * @return string|void Max upload size.
 */
function geodir_max_upload_size() {
	$max_filesize = (float) geodir_get_option( 'upload_max_filesize', 2 );

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
 * Check if dummy folder exists or not.
 *
 * Check if dummy folder exists or not , if not then fetch from live url.
 *
 * @since   1.0.0
 * @package GeoDirectory
 * @return bool If dummy folder exists returns true, else false.
 */
function geodir_dummy_folder_exists() {
	$path = geodir_plugin_path() . '/includes/admin/dummy/';
	if ( ! is_dir( $path ) ) {
		return false;
	} else {
		return true;
	}

}

/**
 * Get the author info.
 *
 * @since   1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 *
 * @param int $aid      The author ID.
 *
 * @return object Author info.
 */
function geodir_get_author_info( $aid ) {
	global $wpdb;
	/*$infosql = "select * from $wpdb->users where ID=$aid";*/
	$infosql = $wpdb->prepare( "select * from $wpdb->users where ID=%d", array( $aid ) );
	$info    = $wpdb->get_results( $infosql );
	if ( $info ) {
		return $info[0];
	}
}



/*
Language translation helper functions
*/

/**
 * Function to get the translated category id's.
 *
 * @since   1.0.0
 * @package GeoDirectory
 *
 * @param array $ids_array Category IDs.
 * @param string $type     Category taxonomy.
 *
 * @return array Category IDs.
 */
function geodir_lang_object_ids( $ids_array, $type ) {
	if ( function_exists( 'icl_object_id' ) ) {
		$res = array();
		foreach ( $ids_array as $id ) {
			$xlat = icl_object_id( $id, $type, false );
			if ( ! is_null( $xlat ) ) {
				$res[] = $xlat;
			}
		}

		return $res;
	} else {
		return $ids_array;
	}
}


/**
 * function to add class to body when multi post type is active.
 *
 * @since   1.0.0
 * @since   1.5.6 Add geodir-page class to body for all gd pages.
 * @package GeoDirectory
 * @global object $wpdb  WordPress Database object.
 *
 * @param array $classes Body CSS classes.
 *
 * @return array Modified Body CSS classes.
 */
function geodir_custom_posts_body_class( $classes ) {
	global $wpdb, $wp;
	$post_types = geodir_get_posttypes( 'object' );
	if ( ! empty( $post_types ) && count( (array) $post_types ) > 1 ) {
		$classes[] = 'geodir_custom_posts';
	}

	// fix body class for signup page
	if ( geodir_is_page( 'login' ) ) {
		$new_classes   = array();
		$new_classes[] = 'signup page-geodir-signup';
		if ( ! empty( $classes ) ) {
			foreach ( $classes as $class ) {
				if ( $class && $class != 'home' && $class != 'blog' ) {
					$new_classes[] = $class;
				}
			}
		}
		$classes = $new_classes;
	}

	if ( geodir_is_geodir_page() ) {
		$classes[] = 'geodir-page';
	}

	if ( geodir_is_page('search') ) {
		$classes[] = 'geodir-page-search';
	}

	return $classes;
}

add_filter( 'body_class', 'geodir_custom_posts_body_class' ); // let's add a class to the body so we can style the new addition to the search


/**
 * Returns available map zoom levels.
 *
 * @since   1.0.0
 * @package GeoDirectory
 * @return array Available map zoom levels.
 */
function geodir_map_zoom_level() {
	/**
	 * Filter GD map zoom level.
	 *
	 * @since 1.0.0
	 */
	return apply_filters( 'geodir_map_zoom_level', array(
		1,
		2,
		3,
		4,
		5,
		6,
		7,
		8,
		9,
		10,
		11,
		12,
		13,
		14,
		15,
		16,
		17,
		18,
		19
	) );

}


/**
 * This function takes backup of an option so they can be restored later.
 *
 * @since   1.0.0
 * @package GeoDirectory
 *
 * @param string $geodir_option_name Option key.
 */
function geodir_option_version_backup( $geodir_option_name ) {
	$version_date  = time();
	$geodir_option = geodir_get_option( $geodir_option_name );

	if ( ! empty( $geodir_option ) ) {
		add_option( $geodir_option_name . '_' . $version_date, $geodir_option );
	}
}

/**
 * display add listing page for wpml.
 *
 * @since   1.0.0
 * @package GeoDirectory
 *
 * @param int $page_id The page ID.
 *
 * @return int Page ID.
 */
function get_page_id_geodir_add_listing_page( $page_id ) {
	if ( geodir_wpml_multilingual_status() ) {
		$post_type = 'post_page';
		$page_id   = geodir_get_wpml_element_id( $page_id, $post_type );
	}

	return $page_id;
}

/**
 * Returns wpml multilingual status.
 *
 * @since   1.0.0
 * @package GeoDirectory
 * @return bool Returns true when sitepress multilingual CMS active. else returns false.
 */
function geodir_wpml_multilingual_status() {
	if ( function_exists( 'icl_object_id' ) ) {
		return true;
	}

	return false;
}

/**
 * Returns WPML element ID.
 *
 * @since   1.0.0
 * @package GeoDirectory
 *
 * @param int $page_id      The page ID.
 * @param string $post_type The post type.
 *
 * @return int Element ID when exists. Else the page id.
 */
function geodir_get_wpml_element_id( $page_id, $post_type ) {
	global $sitepress;
	if ( geodir_wpml_multilingual_status() && ! empty( $sitepress ) && isset( $sitepress->queries ) ) {
		$trid = $sitepress->get_element_trid( $page_id, $post_type );

		if ( $trid > 0 ) {
			$translations = $sitepress->get_element_translations( $trid, $post_type );

			$lang = $sitepress->get_current_language();
			$lang = $lang ? $lang : $sitepress->get_default_language();

			if ( ! empty( $translations ) && ! empty( $lang ) && isset( $translations[ $lang ] ) && isset( $translations[ $lang ]->element_id ) && ! empty( $translations[ $lang ]->element_id ) ) {
				$page_id = $translations[ $lang ]->element_id;
			}
		}
	}

	return $page_id;
}

/**
 * WPML check element ID.
 *
 * @since      1.0.0
 * @package    GeoDirectory
 * @deprecated 1.4.6 No longer needed as we handle translating GD pages as normal now.
 */
function geodir_wpml_check_element_id() {
	global $sitepress;
	if ( geodir_wpml_multilingual_status() && ! empty( $sitepress ) && isset( $sitepress->queries ) ) {
		$el_type      = 'post_page';
		$el_id        = geodir_get_option( 'geodir_add_listing_page' );
		$default_lang = $sitepress->get_default_language();
		$el_details   = $sitepress->get_element_language_details( $el_id, $el_type );

		if ( ! ( $el_id > 0 && $default_lang && ! empty( $el_details ) && isset( $el_details->language_code ) && $el_details->language_code == $default_lang ) ) {
			if ( ! $el_details->source_language_code ) {
				$sitepress->set_element_language_details( $el_id, $el_type, '', $default_lang );
				$sitepress->icl_translations_cache->clear();
			}
		}
	}
}

/**
 * Returns orderby SQL using the given query args.
 *
 * @since   1.0.0
 * @since   1.6.18 Allow order by custom field in widget listings results sorting.
 * @package GeoDirectory
 * @global object $wpdb          WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 *
 * @param array $query_args      The query array.
 *
 * @return string Orderby SQL.
 */
function geodir_widget_listings_get_order( $query_args ) {
	global $wpdb, $plugin_prefix, $gd_query_args_widgets;

	$query_args = $gd_query_args_widgets;
	if ( empty( $query_args ) || empty( $query_args['is_geodir_loop'] ) ) {
		return $wpdb->posts . ".post_date DESC, ";
	}

	$post_type = empty( $query_args['post_type'] ) ? 'gd_place' : $query_args['post_type'];
	$table     = $plugin_prefix . $post_type . '_detail';

	$sort_by = ! empty( $query_args['order_by'] ) ? $query_args['order_by'] : '';

	switch ( $sort_by ) {
		case 'latest':
		case 'newest':
			$orderby = $wpdb->posts . ".post_date DESC, ";
			break;
		case 'featured':
			$orderby = $table . ".is_featured ASC, ". $wpdb->posts . ".post_date DESC, ";
			break;
		case 'az':
			$orderby = $wpdb->posts . ".post_title ASC, ";
			break;
		case 'high_review':
			$orderby = $table . ".rating_count DESC, " . $table . ".overall_rating DESC, ";
			break;
		case 'high_rating':
			$orderby = "( " . $table . ".overall_rating  ) DESC, ";
			break;
		case 'random':
			$orderby = "RAND(), ";
			break;
		default:
			if ( $custom_orderby = GeoDir_Query::prepare_sort_order( $sort_by, $table ) ) {
				$orderby = $custom_orderby . ", ";
			} else {
				$orderby = $wpdb->posts . ".post_title ASC, ";
			}
			break;
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
 * @global object $wpdb          WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 * @global string $table_prefix  WordPress Database Table prefix.
 *
 * @param array $query_args      The query array.
 * @param  int|bool $count_only  If true returns listings count only, otherwise returns array
 *
 * @return mixed Result object.
 */
function geodir_get_widget_listings( $query_args = array(), $count_only = false ) {
	global $wpdb, $plugin_prefix, $table_prefix;
	$GLOBALS['gd_query_args_widgets'] = $query_args;
	$gd_query_args_widgets            = $query_args;

	$post_type = empty( $query_args['post_type'] ) ? 'gd_place' : $query_args['post_type'];
	$table     = $plugin_prefix . $post_type . '_detail';
	$supports_wpml = geodir_wpml_is_post_type_translated( $post_type );

	$fields = $wpdb->posts . ".*, " . $table . ".*";
	/**
	 * Filter widget listing fields string part that is being used for query.
	 *
	 * @since 1.0.0
	 *
	 * @param string $fields    Fields string.
	 * @param string $table     Table name.
	 * @param string $post_type Post type.
	 */
	$fields = apply_filters( 'geodir_filter_widget_listings_fields', $fields, $table, $post_type );

	$join = "INNER JOIN " . $table . " ON (" . $table . ".post_id = " . $wpdb->posts . ".ID)";

	########### WPML ###########

	if ( $supports_wpml ) {
		global $sitepress;
		$lang_code = ICL_LANGUAGE_CODE;
		if ( $lang_code ) {
			$join .= " JOIN " . $table_prefix . "icl_translations icl_t ON icl_t.element_id = " . $table_prefix . "posts.ID";
		}
	}

	########### WPML ###########

	/**
	 * Filter widget listing join clause string part that is being used for query.
	 *
	 * @since 1.0.0
	 *
	 * @param string $join      Join clause string.
	 * @param string $post_type Post type.
	 */
	$join = apply_filters( 'geodir_filter_widget_listings_join', $join, $post_type );

	$post_status = is_super_admin() ? " OR " . $wpdb->posts . ".post_status = 'private'" : '';

	$where = " AND ( " . $wpdb->posts . ".post_status = 'publish' " . $post_status . " ) AND " . $wpdb->posts . ".post_type = '" . $post_type . "'";

	########### WPML ###########
	if ( $supports_wpml ) {
		if ( $lang_code ) {
			$where .= " AND icl_t.language_code = '$lang_code' AND icl_t.element_type = 'post_$post_type' ";
		}
	}
	########### WPML ###########
	/**
	 * Filter widget listing where clause string part that is being used for query.
	 *
	 * @since 1.0.0
	 *
	 * @param string $where     Where clause string.
	 * @param string $post_type Post type.
	 */
	$where = apply_filters( 'geodir_filter_widget_listings_where', $where, $post_type );
	$where = $where != '' ? " WHERE 1=1 " . $where : '';

	$groupby = " GROUP BY $wpdb->posts.ID "; //@todo is this needed? faster without
	/**
	 * Filter widget listing groupby clause string part that is being used for query.
	 *
	 * @since 1.0.0
	 *
	 * @param string $groupby   Group by clause string.
	 * @param string $post_type Post type.
	 */
	$groupby = apply_filters( 'geodir_filter_widget_listings_groupby', $groupby, $post_type );

	if ( $count_only ) {
		$sql  = "SELECT COUNT(DISTINCT " . $wpdb->posts . ".ID) AS total FROM " . $wpdb->posts . "
			" . $join . "
			" . $where;
		$rows = (int) $wpdb->get_var( $sql );
	} else {
		$orderby = geodir_widget_listings_get_order( $query_args );
		/**
		 * Filter widget listing orderby clause string part that is being used for query.
		 *
		 * @since 1.0.0
		 *
		 * @param string $orderby   Order by clause string.
		 * @param string $table     Table name.
		 * @param string $post_type Post type.
		 */
		$orderby = apply_filters( 'geodir_filter_widget_listings_orderby', $orderby, $table, $post_type );
		
		$second_orderby = array();
		if ( strpos( $orderby, strtolower( $table . ".is_featured" )  ) === false ) {
			$second_orderby[] = $table . ".is_featured ASC";
		}
		
		if ( strpos( $orderby, strtolower( $wpdb->posts . ".post_date" )  ) === false ) {
			$second_orderby[] = $wpdb->posts . ".post_date DESC";
		}
		
		if ( strpos( $orderby, strtolower( $wpdb->posts . ".post_title" )  ) === false ) {
			$second_orderby[] = $wpdb->posts . ".post_title ASC";
		}
		
		if ( !empty( $second_orderby ) ) {
			$orderby .= implode( ', ', $second_orderby );
		}
		
		if ( !empty( $orderby ) ) {
			$orderby = trim( $orderby );
			$orderby = rtrim( $orderby, "," );
		}
		
		$orderby = $orderby != '' ? " ORDER BY " . $orderby : '';

		$limit = ! empty( $query_args['posts_per_page'] ) ? $query_args['posts_per_page'] : 5;
		/**
		 * Filter widget listing limit that is being used for query.
		 *
		 * @since 1.0.0
		 *
		 * @param int $limit        Query results limit.
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
		$rows = $wpdb->get_results( $sql );
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
 * @global object $wpdb          WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 *
 * @param string $fields         Fields SQL.
 *
 * @return string Modified fields SQL.
 */
function geodir_function_widget_listings_fields( $fields ) {
	global $wpdb, $plugin_prefix, $gd_query_args_widgets;

	$query_args = $gd_query_args_widgets;
	if ( empty( $query_args ) || empty( $query_args['is_geodir_loop'] ) ) {
		return $fields;
	}

	return $fields;
}

/**
 * Listing query join clause SQL part for widgets.
 *
 * @since   1.0.0
 * @package GeoDirectory
 * @global object $wpdb          WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 *
 * @param string $join           Join clause SQL.
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

/**
 * Listing query where clause SQL part for widgets.
 *
 * @since   1.0.0
 * @since   1.6.18 New attributes added in gd_listings shortcode to filter user favorite listings.
 * @package GeoDirectory
 * @global object $wpdb          WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 *
 * @param string $where          Where clause SQL.
 *
 * @return string Modified where clause SQL.
 */
function geodir_function_widget_listings_where( $where ) {
	global $wpdb, $plugin_prefix, $gd_query_args_widgets;

	$query_args = $gd_query_args_widgets;
	if ( empty( $query_args ) || empty( $query_args['is_geodir_loop'] ) ) {
		return $where;
	}
	$post_type = empty( $query_args['post_type'] ) ? 'gd_place' : $query_args['post_type'];
	$table     = $plugin_prefix . $post_type . '_detail';

	if ( ! empty( $query_args ) ) {
		if ( ! empty( $query_args['gd_location'] ) && function_exists( 'geodir_default_location_where' ) ) {
			$where = geodir_default_location_where( $where, $table );
		}

		if ( ! empty( $query_args['post_author'] ) ) {
			$where .= " AND " . $wpdb->posts . ".post_author = " . (int) $query_args['post_author'];
		}

		if ( ! empty( $query_args['show_featured_only'] ) ) {
			$where .= " AND " . $table . ".is_featured = '1'";
		}

		if ( ! empty( $query_args['show_special_only'] ) ) {
			$where .= " AND ( " . $table . ".geodir_special_offers != '' AND " . $table . ".geodir_special_offers IS NOT NULL )";
		}

		if ( ! empty( $query_args['with_pics_only'] ) ) {
			$where .= " AND " . GEODIR_ATTACHMENT_TABLE . ".ID IS NOT NULL ";
		}

		if ( ! empty( $query_args['featured_image_only'] ) ) {
			$where .= " AND " . $table . ".featured_image IS NOT NULL AND " . $table . ".featured_image!='' ";
		}

		if ( ! empty( $query_args['with_videos_only'] ) ) {
			$where .= " AND ( " . $table . ".geodir_video != '' AND " . $table . ".geodir_video IS NOT NULL )";
		}
        
		if ( ! empty( $query_args['show_favorites_only'] ) ) {
			$user_favorites = '-1';
			
			if ( !empty( $query_args['favorites_by_user'] ) ) {
				$user_favorites = geodir_get_user_favourites( (int)$query_args['favorites_by_user']);
				$user_favorites = !empty($user_favorites) && is_array($user_favorites) ? implode("','", $user_favorites) : '-1';
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

/**
 * Listing query orderby clause SQL part for widgets.
 *
 * @since   1.0.0
 * @package GeoDirectory
 * @global object $wpdb          WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 *
 * @param string $orderby        Orderby clause SQL.
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

/**
 * Listing query limit for widgets.
 *
 * @since   1.0.0
 * @package GeoDirectory
 * @global object $wpdb          WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 *
 * @param int $limit             Query limit.
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

/**
 * WP media large width.
 *
 * @since   1.0.0
 * @package GeoDirectory
 *
 * @param int $default         Default width.
 * @param string|array $params Image parameters.
 *
 * @return int Large size width.
 */
function geodir_media_image_large_width( $default = 800, $params = '' ) {
	$large_size_w = get_option( 'large_size_w' );
	$large_size_w = $large_size_w > 0 ? $large_size_w : $default;
	$large_size_w = absint( $large_size_w );

	if ( ! geodir_get_option( 'geodir_use_wp_media_large_size' ) ) {
		$large_size_w = 800;
	}

	/**
	 * Filter large image width.
	 *
	 * @since 1.0.0
	 *
	 * @param int $large_size_w    Large image width.
	 * @param int $default         Default width.
	 * @param string|array $params Image parameters.
	 */
	$large_size_w = apply_filters( 'geodir_filter_media_image_large_width', $large_size_w, $default, $params );

	return $large_size_w;
}

/**
 * WP media large height.
 *
 * @since   1.0.0
 * @package GeoDirectory
 *
 * @param int $default   Default height.
 * @param string $params Image parameters.
 *
 * @return int Large size height.
 */
function geodir_media_image_large_height( $default = 800, $params = '' ) {
	$large_size_h = get_option( 'large_size_h' );
	$large_size_h = $large_size_h > 0 ? $large_size_h : $default;
	$large_size_h = absint( $large_size_h );

	if ( ! geodir_get_option( 'geodir_use_wp_media_large_size' ) ) {
		$large_size_h = 800;
	}

	/**
	 * Filter large image height.
	 *
	 * @since 1.0.0
	 *
	 * @param int $large_size_h    Large image height.
	 * @param int $default         Default height.
	 * @param string|array $params Image parameters.
	 */
	$large_size_h = apply_filters( 'geodir_filter_media_image_large_height', $large_size_h, $default, $params );

	return $large_size_h;
}

/**
 * Sanitize location name.
 *
 * @since   1.0.0
 * @package GeoDirectory
 *
 * @param string $type    Location type. Can be gd_country, gd_region, gd_city.
 * @param string $name    Location name.
 * @param bool $translate Do you want to translate the name? Default: true.
 *
 * @return string Sanitized name.
 */
function geodir_sanitize_location_name( $type, $name, $translate = true ) {
	if ( $name == '' ) {
		return null;
	}

	$type = $type == 'gd_country' ? 'country' : $type;
	$type = $type == 'gd_region' ? 'region' : $type;
	$type = $type == 'gd_city' ? 'city' : $type;

	$return = $name;
	if ( function_exists( 'get_actual_location_name' ) ) {
		$return = get_actual_location_name( $type, $name, $translate );
	} else {
		$return = preg_replace( '/-(\d+)$/', '', $return );
		$return = preg_replace( '/[_-]/', ' ', $return );
		$return = geodir_ucwords( $return );
		$return = $translate ? __( $return, 'geodirectory' ) : $return;
	}

	return $return;
}




/**
 * Checks whether the current page is geodirectory home page or not.
 *
 * @since   1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @return bool If current page is GD home page returns true, else false.
 */
function is_page_geodir_home() {
	global $wpdb;
	$cur_url = str_replace( array( "https://", "http://", "www." ), array( '', '', '' ), geodir_curPageURL() );
	if ( function_exists( 'geodir_location_geo_home_link' ) ) {
		remove_filter( 'home_url', 'geodir_location_geo_home_link', 100000 );
	}
	$home_url = home_url( '', 'http' );
	if ( function_exists( 'geodir_location_geo_home_link' ) ) {
		add_filter( 'home_url', 'geodir_location_geo_home_link', 100000, 2 );
	}
	$home_url = str_replace( "www.", "", $home_url );
	if ( ( strpos( $home_url, $cur_url ) !== false || strpos( $home_url . '/', $cur_url ) !== false ) && ( 'page' == get_option( 'show_on_front' ) && get_option( 'page_on_front' ) && get_option( 'page_on_front' ) == geodir_get_option( 'geodir_home_page' ) ) ) {
		return true;
	} elseif ( get_query_var( 'page_id' ) == get_option( 'page_on_front' ) && 'page' == get_option( 'show_on_front' ) && get_option( 'page_on_front' ) && get_option( 'page_on_front' ) == geodir_get_option( 'geodir_home_page' ) ) {
		return true;
	} else {
		return false;
	}

}


/**
 * Returns homepage canonical url for SEO plugins.
 *
 * @since   1.0.0
 * @package GeoDirectory
 * @global object $post The current post object.
 *
 * @param string $url   The old url.
 *
 * @return string The canonical URL.
 */
function geodir_wpseo_homepage_canonical( $url ) {
	global $post;

	if ( is_page_geodir_home() ) {
		return home_url();
	}

	return $url;
}

add_filter( 'wpseo_canonical', 'geodir_wpseo_homepage_canonical', 10 );
add_filter( 'aioseop_canonical_url', 'geodir_wpseo_homepage_canonical', 10 );

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
	if ( ! str_replace( 'libraries=places', '', $extra ) && ( geodir_is_page( 'detail' ) || $add_google_places_api ) ) {
		$extra .= "&amp;libraries=places";
	}

	return $extra;
}

add_filter( 'geodir_googlemap_script_extra', 'geodir_googlemap_script_extra_details_page', 101, 1 );


/**
 * Generates popular post category HTML.
 *
 * @since   1.0.0
 * @since   1.5.1 Added option to set default post type.
 * @since   1.6.9 Added option to show parent categories only.
 * @since   1.6.18 Added option to show parent categories only.
 * @package GeoDirectory
 * @global object $wpdb                     WordPress Database object.
 * @global string $plugin_prefix            Geodirectory plugin table prefix.
 * @global string $geodir_post_category_str The geodirectory post category.
 *
 * @param array|string $args                Display arguments including before_title, after_title, before_widget, and
 *                                          after_widget.
 * @param array|string $instance            The settings for the particular instance of the widget.
 */
function geodir_popular_post_category_output( $args = '', $instance = '' ) {
	// prints the widget
	global $wpdb, $plugin_prefix, $geodir_post_category_str;
	extract( $args, EXTR_SKIP );

	echo $before_widget;

	/** This filter is documented in includes/widget/class-geodir-widget-advance-search.php.php */
	$title = empty( $instance['title'] ) ? __( 'Popular Categories', 'geodirectory' ) : apply_filters( 'widget_title', __( $instance['title'], 'geodirectory' ) );

	$gd_post_type = geodir_get_current_posttype();

	$category_limit = isset( $instance['category_limit'] ) && $instance['category_limit'] > 0 ? (int) $instance['category_limit'] : 15;
	if (!isset($category_restrict)) {
		$category_restrict = false;
	}
	if ( ! empty( $gd_post_type ) ) {
		$default_post_type = $gd_post_type;
	} elseif ( isset( $instance['default_post_type'] ) && gdsc_is_post_type_valid( $instance['default_post_type'] ) ) {
		$default_post_type = $instance['default_post_type'];
	} else {
		$all_gd_post_type  = geodir_get_posttypes();
		$default_post_type = ( isset( $all_gd_post_type[0] ) ) ? $all_gd_post_type[0] : '';
	}
	$parent_only = !empty( $instance['parent_only'] ) ? true : false;

	$taxonomy = array();
	if ( ! empty( $gd_post_type ) ) {
		$taxonomy[] = $gd_post_type . "category";
	} else {
		$taxonomy = geodir_get_taxonomies( $gd_post_type );
	}

	$taxonomy = apply_filters('geodir_pp_category_taxonomy', $taxonomy);

	$term_args = array( 'taxonomy' => $taxonomy );
	if ( $parent_only ) {
		$term_args['parent'] = 0;
	}

	$terms   = get_terms( $term_args );
	$a_terms = array();
	$b_terms = array();

	foreach ( $terms as $term ) {
		if ( $term->count > 0 ) {
			$a_terms[ $term->taxonomy ][] = $term;
		}
	}

	if ( ! empty( $a_terms ) ) {
		// Sort CPT taxonomies in categories widget.
		if ( !empty( $taxonomy ) && is_array( $taxonomy ) && count( $taxonomy ) > 1 ) {
			$gd_post_types = geodir_get_posttypes();
			$sort_taxonomies = array();
			
			foreach ( $gd_post_types as $gd_post_type ) {
				$taxonomy_name = $gd_post_type . 'category';
				
				if ( !empty( $a_terms[$taxonomy_name] ) ) {
					$sort_taxonomies[$taxonomy_name] = $a_terms[$taxonomy_name];
				}
			}
			$a_terms = !empty( $sort_taxonomies ) ? $sort_taxonomies : $a_terms;
		}
		
		foreach ( $a_terms as $b_key => $b_val ) {
			$b_terms[ $b_key ] = geodir_sort_terms( $b_val, 'count' );
		}

		$default_taxonomy = $default_post_type != '' && isset( $b_terms[ $default_post_type . 'category' ] ) ? $default_post_type . 'category' : '';

		$tax_change_output = '';
		if ( count( $b_terms ) > 1 ) {
			$tax_change_output .= "<select data-limit='$category_limit' data-parent='" . (int)$parent_only . "' class='geodir-cat-list-tax'  onchange='geodir_get_post_term(this);'>";
			foreach ( $b_terms as $key => $val ) {
				$ptype    = get_post_type_object( str_replace( "category", "", $key ) );
				$cpt_name = __( $ptype->labels->singular_name, 'geodirectory' );
				$tax_change_output .= "<option value='$key' " . selected( $key, $default_taxonomy, false ) . ">" . sprintf( __( '%s Categories', 'geodirectory' ), $cpt_name ) . "</option>";
			}
			$tax_change_output .= "</select>";
		}

		if ( ! empty( $b_terms ) ) {
			$terms = $default_taxonomy != '' && isset( $b_terms[ $default_taxonomy ] ) ? $b_terms[ $default_taxonomy ] : reset( $b_terms );// get the first array
			global $cat_count;//make global so we can change via function
			$cat_count = 0;
			?>
			<div class="geodir-category-list-in clearfix">
				<div class="geodir-cat-list clearfix">
					<?php
					echo $before_title . __( $title ) . $after_title;

					echo $tax_change_output;

					echo '<ul class="geodir-popular-cat-list">';

					geodir_helper_cat_list_output( $terms, $category_limit, $category_restrict);

					echo '</ul>';
					?>
				</div>
				<?php
				if ( empty( $category_restrict ) ) { 
					$hide = '';
					if ( $cat_count < $category_limit ) {
						$hide = 'style="display:none;"';
					}
					echo "<div class='geodir-cat-list-more' $hide >";
					echo '<a href="javascript:void(0)" class="geodir-morecat geodir-showcat">' . __( 'More Categories', 'geodirectory' ) . '</a>';
					echo '<a href="javascript:void(0)" class="geodir-morecat geodir-hidecat geodir-hide">' . __( 'Less Categories', 'geodirectory' ) . '</a>';
					echo "</div>";
				}
				/* add scripts */
				add_action( 'wp_footer', 'geodir_popular_category_add_scripts', 100 );
				?>
			</div>
			<?php
		}
	}
	echo $after_widget;
}

/**
 * Generates category list HTML.
 *
 * @since   1.0.0
 * @package GeoDirectory
 * @global string $geodir_post_category_str The geodirectory post category.
 *
 * @param array $terms                      An array of term objects.
 * @param int $category_limit               Number of categories to display by default.
 * @param bool $category_restrict           If the cat limit should be hidden or not shown.
 */
function geodir_helper_cat_list_output( $terms, $category_limit , $category_restrict=false) {
	global $geodir_post_category_str, $cat_count;
	$term_icons = geodir_get_term_icon();

	$geodir_post_category_str = array();


	foreach ( $terms as $cat ) {
		$post_type     = str_replace( "category", "", $cat->taxonomy );
		$term_icon_url = ! empty( $term_icons ) && isset( $term_icons[ $cat->term_id ] ) ? $term_icons[ $cat->term_id ] : '';

		$cat_count ++;

		$geodir_post_category_str[] = array( 'posttype' => $post_type, 'termid' => $cat->term_id );

		$class_row  = $cat_count > $category_limit ? 'geodir-pcat-hide geodir-hide' : 'geodir-pcat-show';
		if($category_restrict && $cat_count > $category_limit ){
			continue;
		}
		$total_post = $cat->count;

		$term_link = get_term_link( $cat, $cat->taxonomy );
		/**
		 * Filer the category term link.
		 *
		 * @since 1.4.5
		 *
		 * @param string $term_link The term permalink.
		 * @param int $cat          ->term_id The term id.
		 * @param string $post_type Wordpress post type.
		 */
		$term_link = apply_filters( 'geodir_category_term_link', $term_link, $cat->term_id, $post_type );

		echo '<li class="' . $class_row . '"><a href="' . $term_link . '">';
		echo '<img alt="' . esc_attr( $cat->name ) . ' icon" style="height:20px;vertical-align:middle;" src="' . $term_icon_url . '"/> <span class="cat-link">';
		echo $cat->name . '</span> <span class="geodir_term_class geodir_link_span geodir_category_class_' . $post_type . '_' . $cat->term_id . '">(' . $total_post . ')</span> ';
		echo '</a></li>';
	}
}

/**
 * Generates listing slider HTML.
 *
 * @since   1.0.0
 * @package GeoDirectory
 * @global object $post          The current post object.
 *
 * @param array|string $args     Display arguments including before_title, after_title, before_widget, and after_widget.
 * @param array|string $instance The settings for the particular instance of the widget.
 */
function geodir_listing_slider_widget_output( $args = '', $instance = '' ) {
	// prints the widget
	extract( $args, EXTR_SKIP );

	echo $before_widget;

	/** This filter is documented in geodirectory_widgets.php */
	$title = empty( $instance['title'] ) ? '' : apply_filters( 'widget_title', __( $instance['title'], 'geodirectory' ) );
	/**
	 * Filter the widget post type.
	 *
	 * @since 1.0.0
	 *
	 * @param string $instance ['post_type'] Post type of listing.
	 */
	$post_type = empty( $instance['post_type'] ) ? 'gd_place' : apply_filters( 'widget_post_type', $instance['post_type'] );
	/**
	 * Filter the widget's term.
	 *
	 * @since 1.0.0
	 *
	 * @param string $instance ['category'] Filter by term. Can be any valid term.
	 */
	$category = empty( $instance['category'] ) ? '0' : apply_filters( 'widget_category', $instance['category'] );
	/**
	 * Filter widget's "add_location_filter" value.
	 *
	 * @since 1.0.0
	 *
	 * @param string|bool $instance ['add_location_filter'] Do you want to add location filter? Can be 1 or 0.
	 */
	$add_location_filter = empty( $instance['add_location_filter'] ) ? '0' : apply_filters( 'widget_add_location_filter', $instance['add_location_filter'] );
	/**
	 * Filter the widget listings limit.
	 *
	 * @since 1.0.0
	 *
	 * @param string $instance ['post_number'] Number of listings to display.
	 */
	$post_number = empty( $instance['post_number'] ) ? '5' : apply_filters( 'widget_post_number', $instance['post_number'] );
	/**
	 * Filter the widget listings limit shown at one time.
	 *
	 * @since 1.5.0
	 *
	 * @param string $instance ['max_show'] Number of listings to display on screen.
	 */
	$max_show = empty( $instance['max_show'] ) ? '1' : apply_filters( 'widget_max_show', $instance['max_show'] );
	/**
	 * Filter the widget slide width.
	 *
	 * @since 1.5.0
	 *
	 * @param string $instance ['slide_width'] Width of the slides shown.
	 */
	$slide_width = empty( $instance['slide_width'] ) ? '' : apply_filters( 'widget_slide_width', $instance['slide_width'] );
	/**
	 * Filter widget's "show title" value.
	 *
	 * @since 1.0.0
	 *
	 * @param string|bool $instance ['show_title'] Do you want to display title? Can be 1 or 0.
	 */
	$show_title = empty( $instance['show_title'] ) ? '' : apply_filters( 'widget_show_title', $instance['show_title'] );
	/**
	 * Filter widget's "slideshow" value.
	 *
	 * @since 1.0.0
	 *
	 * @param int $instance ['slideshow'] Setup a slideshow for the slider to animate automatically.
	 */
	$slideshow = empty( $instance['slideshow'] ) ? 0 : apply_filters( 'widget_slideshow', $instance['slideshow'] );
	/**
	 * Filter widget's "animationLoop" value.
	 *
	 * @since 1.0.0
	 *
	 * @param int $instance ['animationLoop'] Gives the slider a seamless infinite loop.
	 */
	$animationLoop = empty( $instance['animationLoop'] ) ? 0 : apply_filters( 'widget_animationLoop', $instance['animationLoop'] );
	/**
	 * Filter widget's "directionNav" value.
	 *
	 * @since 1.0.0
	 *
	 * @param int $instance ['directionNav'] Enable previous/next arrow navigation?. Can be 1 or 0.
	 */
	$directionNav = empty( $instance['directionNav'] ) ? 0 : apply_filters( 'widget_directionNav', $instance['directionNav'] );
	/**
	 * Filter widget's "slideshowSpeed" value.
	 *
	 * @since 1.0.0
	 *
	 * @param int $instance ['slideshowSpeed'] Set the speed of the slideshow cycling, in milliseconds.
	 */
	$slideshowSpeed = empty( $instance['slideshowSpeed'] ) ? 5000 : apply_filters( 'widget_slideshowSpeed', $instance['slideshowSpeed'] );
	/**
	 * Filter widget's "animationSpeed" value.
	 *
	 * @since 1.0.0
	 *
	 * @param int $instance ['animationSpeed'] Set the speed of animations, in milliseconds.
	 */
	$animationSpeed = empty( $instance['animationSpeed'] ) ? 600 : apply_filters( 'widget_animationSpeed', $instance['animationSpeed'] );
	/**
	 * Filter widget's "animation" value.
	 *
	 * @since 1.0.0
	 *
	 * @param string $instance ['animation'] Controls the animation type, "fade" or "slide".
	 */
	$animation = empty( $instance['animation'] ) ? 'slide' : apply_filters( 'widget_animation', $instance['animation'] );
	/**
	 * Filter widget's "list_sort" type.
	 *
	 * @since 1.0.0
	 *
	 * @param string $instance ['list_sort'] Listing sort by type.
	 */
	$list_sort          = empty( $instance['list_sort'] ) ? 'latest' : apply_filters( 'widget_list_sort', $instance['list_sort'] );
	$show_featured_only = ! empty( $instance['show_featured_only'] ) ? 1 : null;

	wp_enqueue_script( 'geodirectory-jquery-flexslider-js' );
	?>
		<script type="text/javascript">
		jQuery(window).load(function () {
			// chrome 53 introduced a bug, so we need to repaint the slider when shown.
			jQuery('#geodir_widget_carousel .geodir-slides').addClass('flexslider-fix-rtl');
			jQuery('#geodir_widget_slider .geodir-slides').addClass('flexslider-fix-rtl');
			
			jQuery('#geodir_widget_carousel').flexslider({
				animation: "slide",
				selector: ".geodir-slides > li",
				namespace: "geodir-",
				controlNav: false,
				directionNav: false,
				animationLoop: false,
				slideshow: false,
				itemWidth: 75,
				itemMargin: 5,
				asNavFor: '#geodir_widget_slider',
				rtl: <?php echo( is_rtl() ? 'true' : 'false' ); /* fix rtl issue */ ?>,
				start: function (slider) {
					// chrome 53 introduced a bug, so we need to repaint the slider when shown.
					jQuery('.geodir-slides', jQuery(slider)).removeClass('flexslider-fix-rtl');
				},
			});
			
			jQuery('#geodir_widget_slider').flexslider({
				animation: "<?php echo $animation;?>",
				selector: ".geodir-slides > li",
				namespace: "geodir-",
				controlNav: true,
				animationLoop: <?php echo $animationLoop;?>,
				slideshow: <?php echo $slideshow;?>,
				slideshowSpeed: <?php echo $slideshowSpeed;?>,
				animationSpeed: <?php echo $animationSpeed;?>,
				directionNav: <?php echo $directionNav;?>,
				maxItems: <?php echo $max_show;?>,
				move: 1,
				<?php if ( $slide_width ) {
				echo "itemWidth: " . $slide_width . ",";
			}?>
				sync: "#geodir_widget_carousel",
				start: function (slider) {
					// chrome 53 introduced a bug, so we need to repaint the slider when shown.
					jQuery('.geodir-slides', jQuery(slider)).removeClass('flexslider-fix-rtl');
					
					jQuery('.geodir-listing-flex-loader').hide();
					jQuery('#geodir_widget_slider').css({'visibility': 'visible'});
					jQuery('#geodir_widget_carousel').css({'visibility': 'visible'});
				},
				rtl: <?php echo( is_rtl() ? 'true' : 'false' ); /* fix rtl issue */ ?>
			});
		});
	</script>
	<?php
	$query_args = array(
		'posts_per_page' => $post_number,
		'is_geodir_loop' => true,
		'gd_location'    => $add_location_filter ? true : false,
		'post_type'      => $post_type,
		'order_by'       => $list_sort
	);

	if ( $show_featured_only ) {
		$query_args['show_featured_only'] = 1;
	}

	if ( $category != 0 || $category != '' ) {
		$category_taxonomy = geodir_get_taxonomies( $post_type );
		$tax_query         = array(
			'taxonomy' => $category_taxonomy[0],
			'field'    => 'id',
			'terms'    => $category
		);

		$query_args['tax_query'] = array( $tax_query );
	}

	// we want listings with featured image only
	$query_args['featured_image_only'] = 1;

	if ( $post_type == 'gd_event' ) {
		$query_args['gedir_event_listing_filter'] = 'upcoming';
	}// show only upcoming events

	$widget_listings = geodir_get_widget_listings( $query_args );
	if ( ! empty( $widget_listings ) || ( isset( $with_no_results ) && $with_no_results ) ) {
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}

		global $post;

		$current_post = $post;// keep current post info

		$widget_main_slides = '';
		$nav_slides         = '';
		$widget_slides      = 0;

		foreach ( $widget_listings as $widget_listing ) {
			global $gd_widget_listing_type;
			$post         = $widget_listing;
			$widget_image = geodir_get_featured_image( $post->ID, 'thumbnail', geodir_get_option( 'geodir_listing_no_img' ) );

			if ( ! empty( $widget_image ) ) {
				if ( $widget_image->height >= 200 ) {
					$widget_spacer_height = 0;
				} else {
					$widget_spacer_height = ( ( 200 - $widget_image->height ) / 2 );
				}

				$widget_main_slides .= '<li class="geodir-listing-slider-widget"><img class="geodir-listing-slider-spacer" src="' . geodir_plugin_url() . "/assets/images/spacer.gif" . '" alt="' . $widget_image->title . '" title="' . $widget_image->title . '" style="max-height:' . $widget_spacer_height . 'px !important;margin:0 auto;" width="100" />';

				$title = '';
				if ( $show_title ) {
					$title_html     = '<div class="geodir-slider-title"><a href="' . get_permalink( $post->ID ) . '">' . get_the_title( $post->ID ) . '</a></div>';
					$post_id        = $post->ID;
					$post_permalink = get_permalink( $post->ID );
					$post_title     = get_the_title( $post->ID );
					/**
					 * Filter the listing slider widget title.
					 *
					 * @since 1.6.1
					 *
					 * @param string $title_html     The html output of the title.
					 * @param int $post_id           The post id.
					 * @param string $post_permalink The post permalink url.
					 * @param string $post_title     The post title text.
					 */
					$title = apply_filters( 'geodir_listing_slider_title', $title_html, $post_id, $post_permalink, $post_title );
				}

				$widget_main_slides .= $title . '<a href="' . get_permalink( $post->ID ) . '"><img src="' . $widget_image->src . '" alt="' . $widget_image->title . '" title="' . $widget_image->title . '" style="max-height:200px;margin:0 auto;" /></a></li>';
				$nav_slides .= '<li><img src="' . $widget_image->src . '" alt="' . $widget_image->title . '" title="' . $widget_image->title . '" style="max-height:48px;margin:0 auto;" /></li>';
				$widget_slides ++;
			}
		}
		?>
		<div class="flex-container" style="min-height:200px;">
			<div class="geodir-listing-flex-loader"><i class="fa fa-refresh fa-spin"></i></div>
			<div id="geodir_widget_slider" class="geodir_flexslider">
				<ul class="geodir-slides clearfix"><?php echo $widget_main_slides; ?></ul>
			</div>
			<?php if ( $widget_slides > 1 ) { ?>
				<div id="geodir_widget_carousel" class="geodir_flexslider">
					<ul class="geodir-slides clearfix"><?php echo $nav_slides; ?></ul>
				</div>
			<?php } ?>
		</div>
		<?php
		$GLOBALS['post'] = $current_post;
		setup_postdata( $current_post );
	}
	echo $after_widget;
}




/**
 * Generates popular postview HTML.
 *
 * @since   1.0.0
 * @since   1.5.1 View all link fixed for location filter disabled.
 * @since   1.6.24 View all link should go to search page with near me selected.
 * @package GeoDirectory
 * @global object $post                    The current post object.
 * @global string $gridview_columns_widget The girdview style of the listings for widget.
 * @global bool $geodir_is_widget_listing  Is this a widget listing?. Default: false.
 * @global object $gd_session              GeoDirectory Session object.
 *
 * @param array|string $args               Display arguments including before_title, after_title, before_widget, and
 *                                         after_widget.
 * @param array|string $instance           The settings for the particular instance of the widget.
 */
function geodir_popular_postview_output( $args = '', $instance = '' ) {
	global $gd_session;


	// prints the widget
	extract( $args, EXTR_SKIP );

	/** This filter is documented in includes/widget/class-geodir-widget-advance-search.php.php */
	$title = empty( $instance['title'] ) ? geodir_ucwords( $instance['category_title'] ) : apply_filters( 'widget_title', __( $instance['title'], 'geodirectory' ) );
	/**
	 * Filter the widget post type.
	 *
	 * @since 1.0.0
	 *
	 * @param string $instance ['post_type'] Post type of listing.
	 */
	$post_type = empty( $instance['post_type'] ) ? 'gd_place' : apply_filters( 'widget_post_type', $instance['post_type'] );
	/**
	 * Filter the widget's term.
	 *
	 * @since 1.0.0
	 *
	 * @param string $instance ['category'] Filter by term. Can be any valid term.
	 */
	$category = empty( $instance['category'] ) ? '0' : apply_filters( 'widget_category', $instance['category'] );
	/**
	 * Filter the widget listings limit.
	 *
	 * @since 1.0.0
	 *
	 * @param string $instance ['post_number'] Number of listings to display.
	 */
	$post_number = empty( $instance['post_number'] ) ? '5' : apply_filters( 'widget_post_number', $instance['post_number'] );
	/**
	 * Filter widget's "layout" type.
	 *
	 * @since 1.0.0
	 *
	 * @param string $instance ['layout'] Widget layout type.
	 */
	$layout = empty( $instance['layout'] ) ? 'gridview_onehalf' : apply_filters( 'widget_layout', $instance['layout'] );
	/**
	 * Filter widget's "add_location_filter" value.
	 *
	 * @since 1.0.0
	 *
	 * @param string|bool $instance ['add_location_filter'] Do you want to add location filter? Can be 1 or 0.
	 */
	$add_location_filter = empty( $instance['add_location_filter'] ) ? '0' : apply_filters( 'widget_add_location_filter', $instance['add_location_filter'] );
	/**
	 * Filter widget's listing width.
	 *
	 * @since 1.0.0
	 *
	 * @param string $instance ['listing_width'] Listing width.
	 */
	$listing_width = empty( $instance['listing_width'] ) ? '' : apply_filters( 'widget_listing_width', $instance['listing_width'] );
	/**
	 * Filter widget's "list_sort" type.
	 *
	 * @since 1.0.0
	 *
	 * @param string $instance ['list_sort'] Listing sort by type.
	 */
	$list_sort             = empty( $instance['list_sort'] ) ? 'latest' : apply_filters( 'widget_list_sort', $instance['list_sort'] );
	/**
	 * Filter widget's "title_tag" type.
	 *
	 * @since 1.6.26
	 *
	 * @param string $instance ['title_tag'] Listing title tag.
	 */
	$title_tag            = empty( $instance['title_tag'] ) ? 'h3' : apply_filters( 'widget_title_tag', $instance['title_tag'] );
	$use_viewing_post_type = ! empty( $instance['use_viewing_post_type'] ) ? true : false;

	// set post type to current viewing post type
	if ( $use_viewing_post_type ) {
		$current_post_type = geodir_get_current_posttype();
		if ( $current_post_type != '' && $current_post_type != $post_type ) {
			$post_type = $current_post_type;
			$category  = array(); // old post type category will not work for current changed post type
		}
	}
	// replace widget title dynamically
	$posttype_plural_label   = __( get_post_type_plural_label( $post_type ), 'geodirectory' );
	$posttype_singular_label = __( get_post_type_singular_label( $post_type ), 'geodirectory' );

	$title = str_replace( "%posttype_plural_label%", $posttype_plural_label, $title );
	$title = str_replace( "%posttype_singular_label%", $posttype_singular_label, $title );
    
	$categories = $category;
	if ( ! empty( $category ) && $category[0] != '0' ) {
		$category_taxonomy = geodir_get_taxonomies( $post_type );
		
		######### WPML #########
		if ( geodir_wpml_is_taxonomy_translated( $category_taxonomy[0] ) ) {
			$category = geodir_lang_object_ids( $category, $category_taxonomy[0] );
		}
		######### WPML #########
	}

	if ( isset( $instance['character_count'] ) ) {
		/**
		 * Filter the widget's excerpt character count.
		 *
		 * @since 1.0.0
		 *
		 * @param int $instance ['character_count'] Excerpt character count.
		 */
		$character_count = apply_filters( 'widget_list_character_count', $instance['character_count'] );
	} else {
		$character_count = '';
	}

	if ( empty( $title ) || $title == 'All' ) {
		$title .= ' ' . __( get_post_type_plural_label( $post_type ), 'geodirectory' );
	}

	$location_url = array();
	$city         = get_query_var( 'gd_city' );
	if ( ! empty( $city ) ) {
		$country = get_query_var( 'gd_country' );
		$region  = get_query_var( 'gd_region' );

		$geodir_show_location_url = geodir_get_option( 'geodir_show_location_url' );

		if ( $geodir_show_location_url == 'all' ) {
			if ( $country != '' ) {
				$location_url[] = $country;
			}

			if ( $region != '' ) {
				$location_url[] = $region;
			}
		} else if ( $geodir_show_location_url == 'country_city' ) {
			if ( $country != '' ) {
				$location_url[] = $country;
			}
		} else if ( $geodir_show_location_url == 'region_city' ) {
			if ( $region != '' ) {
				$location_url[] = $region;
			}
		}

		$location_url[] = $city;
	}

	$location_allowed = function_exists( 'geodir_cpt_no_location' ) && geodir_cpt_no_location( $post_type ) ? false : true;
	$location_url  = implode( '/', $location_url );
	$skip_location = false;
	if ( ! $add_location_filter && $gd_session->get( 'gd_multi_location' ) ) {
		$skip_location = true;
		$gd_session->un_set( 'gd_multi_location' );
	}

	if ( $location_allowed && $add_location_filter && $gd_session->get( 'all_near_me' ) && geodir_is_page( 'location' ) ) {
		$viewall_url = add_query_arg( array( 
			'geodir_search' => 1, 
			'stype' => $post_type,
			's' => '',
			'snear' => __( 'Near:', 'geodiradvancesearch' ) . ' ' . __( 'Me', 'geodiradvancesearch' ),
			'sgeo_lat' => $gd_session->get( 'user_lat' ),
			'sgeo_lon' => $gd_session->get( 'user_lon' )
		), geodir_search_page_base_url() );

		if ( ! empty( $category ) && !in_array( '0', $category ) ) {
			$viewall_url = add_query_arg( array( 's' . $post_type . 'category' => $category ), $viewall_url );
		}
	} else {
		if ( get_option( 'permalink_structure' ) ) {
			$viewall_url = get_post_type_archive_link( $post_type );
		} else {
			$viewall_url = get_post_type_archive_link( $post_type );
		}

		if ( ! empty( $category ) && $category[0] != '0' ) {
			global $geodir_add_location_url;

			$geodir_add_location_url = '0';

			if ( $add_location_filter != '0' ) {
				$geodir_add_location_url = '1';
			}

			$viewall_url = get_term_link( (int) $category[0], $post_type . 'category' );

			$geodir_add_location_url = null;
		}
	}

	if ( $skip_location ) {
		$gd_session->set( 'gd_multi_location', 1 );
	}

	if ( is_wp_error( $viewall_url ) ) {
		$viewall_url = '';
	}

	$query_args = array(
		'posts_per_page' => $post_number,
		'is_geodir_loop' => true,
		'gd_location'    => $add_location_filter ? true : false,
		'post_type'      => $post_type,
		'order_by'       => $list_sort
	);

	if ( $character_count ) {
		$query_args['excerpt_length'] = $character_count;
	}

	if ( ! empty( $instance['show_featured_only'] ) ) {
		$query_args['show_featured_only'] = 1;
	}

	if ( ! empty( $instance['show_special_only'] ) ) {
		$query_args['show_special_only'] = 1;
	}

	if ( ! empty( $instance['with_pics_only'] ) ) {
		$query_args['with_pics_only']      = 0;
		$query_args['featured_image_only'] = 1;
	}

	if ( ! empty( $instance['with_videos_only'] ) ) {
		$query_args['with_videos_only'] = 1;
	}
	$hide_if_empty = ! empty( $instance['hide_if_empty'] ) ? true : false;

	if ( ! empty( $categories ) && $categories[0] != '0' && !empty( $category_taxonomy ) ) {
		$tax_query = array(
			'taxonomy' => $category_taxonomy[0],
			'field'    => 'id',
			'terms'    => $category
		);

		$query_args['tax_query'] = array( $tax_query );
	}

	global $gridview_columns_widget, $geodir_is_widget_listing;

	$widget_listings = geodir_get_widget_listings( $query_args );
    
	if ( $hide_if_empty && empty( $widget_listings ) ) {
		return;
	}
    
	echo $before_widget;

	?>
	<div class="geodir_locations geodir_location_listing">

		<?php
		/**
		 * Called before the div containing the title and view all link in popular post view widget.
		 *
		 * @since 1.0.0
		 */
		do_action( 'geodir_before_view_all_link_in_widget' ); ?>
		<div class="geodir_list_heading clearfix">
			<?php echo $before_title . $title . $after_title; ?>
			<a href="<?php echo $viewall_url; ?>"
			   class="geodir-viewall"><?php _e( 'View all', 'geodirectory' ); ?></a>
		</div>
		<?php
		/**
		 * Called after the div containing the title and view all link in popular post view widget.
		 *
		 * @since 1.0.0
		 */
		do_action( 'geodir_after_view_all_link_in_widget' ); ?>
		<?php
		if ( strstr( $layout, 'gridview' ) ) {
			$listing_view_exp        = explode( '_', $layout );
			$gridview_columns_widget = $layout;
			$layout                  = $listing_view_exp[0];
		} else {
			$gridview_columns_widget = '';
		}

		if ( ! isset( $character_count ) ) {
			/**
			 * Filter the widget's excerpt character count.
			 *
			 * @since 1.0.0
			 *
			 * @param int $instance ['character_count'] Excerpt character count.
			 */
			$character_count = $character_count == '' ? 50 : apply_filters( 'widget_character_count', $character_count );
		}

		global $post, $map_jason, $map_canvas_arr;

		$current_post             = $post;
		$current_map_jason        = $map_jason;
		$current_map_canvas_arr   = $map_canvas_arr;
		$geodir_is_widget_listing = true;

		geodir_get_template( 'widget-listing-listview.php', array( 'title_tag'=>$title_tag, 'widget_listings' => $widget_listings, 'character_count' => $character_count, 'gridview_columns_widget' => $gridview_columns_widget, 'before_widget' => $before_widget ) );

		$geodir_is_widget_listing = false;

		$GLOBALS['post'] = $current_post;
		if ( ! empty( $current_post ) ) {
			setup_postdata( $current_post );
		}
		$map_jason      = $current_map_jason;
		$map_canvas_arr = $current_map_canvas_arr;
		?>
	</div>
	<?php
	echo $after_widget;
}


/*-----------------------------------------------------------------------------------*/
/*  Review count functions
/*-----------------------------------------------------------------------------------*/
/**
 * Count reviews by term ID.
 *
 * @since   1.0.0
 * @since   1.5.1 Added filter to change SQL.
 * @package GeoDirectory
 * @global object $wpdb          WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 *
 * @param int $term_id           The term ID.
 * @param int $taxonomy          The taxonomy Id.
 * @param string $post_type      The post type.
 *
 * @return int Reviews count.
 */
function geodir_count_reviews_by_term_id( $term_id, $taxonomy, $post_type ) {
	global $wpdb, $plugin_prefix;

	$detail_table = $plugin_prefix . $post_type . '_detail';

	$sql = "SELECT COALESCE(SUM(rating_count),0) FROM " . $detail_table . " WHERE post_status = 'publish' AND rating_count > 0 AND FIND_IN_SET(" . $term_id . ", " . $detail_table . ".post_category)";

	/**
	 * Filter count review sql query.
	 *
	 * @since 1.5.1
	 *
	 * @param string $sql       Database sql query..
	 * @param int $term_id      The term ID.
	 * @param int $taxonomy     The taxonomy Id.
	 * @param string $post_type The post type.
	 */
	$sql = apply_filters( 'geodir_count_reviews_by_term_sql', $sql, $term_id, $taxonomy, $post_type );

	$count = $wpdb->get_var( $sql );

	return $count;
}

/**
 * Count reviews by terms.
 *
 * @since   1.0.0
 * @since   1.6.1 Fixed add listing page load time.
 * @package GeoDirectory
 *
 * @global object $gd_session GeoDirectory Session object.
 *
 * @param bool $force_update  Force update option value?. Default.false.
 *
 * @return array Term array data.
 */
function geodir_count_reviews_by_terms( $force_update = false, $post_ID = 0 ) {
	/**
	 * Filter review count option data.
	 *
	 * @since 1.0.0
	 * @since 1.6.1 Added $post_ID param.
	 *
	 * @param bool $force_update Force update option value?. Default.false.
	 * @param int $post_ID       The post id to update if any.
	 */
	$option_data = apply_filters( 'geodir_count_reviews_by_terms_before', '', $force_update, $post_ID );
	if ( ! empty( $option_data ) ) {
		return $option_data;
	}

	$option_data = geodir_get_option( 'geodir_global_review_count' );

	if ( ! $option_data || $force_update ) {
		if ( (int) $post_ID > 0 ) { // Update reviews count for specific post categories only.
			global $gd_session;
			$term_array = (array) $option_data;
			$post_type  = get_post_type( $post_ID );
			$taxonomy   = $post_type . 'category';
			$terms      = wp_get_object_terms( $post_ID, $taxonomy, array( 'fields' => 'ids' ) );

			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term_id ) {
					$count                  = geodir_count_reviews_by_term_id( $term_id, $taxonomy, $post_type );
					$children               = get_term_children( $term_id, $taxonomy );
					$term_array[ $term_id ] = $count;
				}
			}

			$session_listing = $gd_session->get( 'listing' );

			$terms = array();
			if ( isset( $_POST['post_category'][ $taxonomy ] ) ) {
				$terms = (array) $_POST['post_category'][ $taxonomy ];
			} else if ( ! empty( $session_listing ) && isset( $session_listing['post_category'][ $taxonomy ] ) ) {
				$terms = (array) $session_listing['post_category'][ $taxonomy ];
			}

			if ( ! empty( $terms ) ) {
				foreach ( $terms as $term_id ) {
					if ( $term_id > 0 ) {
						$count                  = geodir_count_reviews_by_term_id( $term_id, $taxonomy, $post_type );
						$children               = get_term_children( $term_id, $taxonomy );
						$term_array[ $term_id ] = $count;
					}
				}
			}
		} else { // Update reviews count for all post categories.
			$term_array = array();
			$post_types = geodir_get_posttypes();
			foreach ( $post_types as $post_type ) {

				$taxonomy = geodir_get_taxonomies( $post_type );
				$taxonomy = $taxonomy[0];

				$args = array(
					'hide_empty' => false
				);

				$terms = get_terms( $taxonomy, $args );

				foreach ( $terms as $term ) {
					$count    = geodir_count_reviews_by_term_id( $term->term_id, $taxonomy, $post_type );
					$children = get_term_children( $term->term_id, $taxonomy );
					/*if ( is_array( $children ) ) {
                        foreach ( $children as $child_id ) {
                            $child_count = geodir_count_reviews_by_term_id($child_id, $taxonomy, $post_type);
                            $count = $count + $child_count;
                        }
                    }*/
					$term_array[ $term->term_id ] = $count;
				}
			}
		}

		geodir_update_option( 'geodir_global_review_count', $term_array );
		//clear cache
		wp_cache_delete( 'geodir_global_review_count' );

		return $term_array;
	} else {
		return $option_data;
	}
}

/**
 * Force update review count.
 *
 * @since   1.0.0
 * @since   1.6.1 Fixed add listing page load time.
 * @package GeoDirectory
 * @return bool
 */
function geodir_term_review_count_force_update( $new_status, $old_status = '', $post = '' ) {
	if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'geodir_import_export' ) {
		return; // do not run if importing listings
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	$post_ID = 0;
	if ( ! empty( $post ) ) {
		if ( isset( $post->post_type ) && strpos( $post->post_type, 'gd_' ) !== 0 ) {
			return;
		}

		if ( $new_status == 'auto-draft' && $old_status == 'new' ) {
			return;
		}

		if ( ! empty( $post->ID ) ) {
			$post_ID = $post->ID;
		}
	}

	if ( $new_status != $old_status ) {
		geodir_count_reviews_by_terms( true, $post_ID );
	}

	return true;
}

function geodir_term_review_count_force_update_single_post( $post_id ) {
	geodir_count_reviews_by_terms( true, $post_id );
}

/*-----------------------------------------------------------------------------------*/
/*  Term count functions
/*-----------------------------------------------------------------------------------*/
/**
 * Count posts by term.
 *
 * @since   1.0.0
 * @package GeoDirectory
 *
 * @param array $data  Count data array.
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
	usort( $terms, "geodir_sort_by_count_obj" );

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
	usort( $terms, "geodir_sort_by_review_count_obj" );

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
	if ( $sort == 'count' ) {
		return geodir_sort_terms_by_count( $terms );
	}
	if ( $sort == 'review_count' ) {
		return geodir_sort_terms_by_review_count( $terms );
	}
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
	return $a->count < $b->count;
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
	return $a->review_count < $b->review_count;
}

/**
 * Load language strings in to file to translate via po editor
 *
 * @since   1.4.2
 * @package GeoDirectory
 *
 * @global null|object $wp_filesystem WP_Filesystem object.
 *
 * @return bool True if file created otherwise false
 */
function geodirectory_load_db_language() {
	global $wp_filesystem;
	if ( empty( $wp_filesystem ) ) {
		require_once( ABSPATH . '/wp-admin/includes/file.php' );
		WP_Filesystem();
		global $wp_filesystem;
	}

	$language_file = geodir_plugin_path() . '/db-language.php';

	if ( is_file( $language_file ) && ! is_writable( $language_file ) ) {
		return false;
	} // Not possible to create.

	if ( ! is_file( $language_file ) && ! is_writable( dirname( $language_file ) ) ) {
		return false;
	} // Not possible to create.

	$contents_strings = array();

	/**
	 * Filter the language string from database to translate via po editor
	 *
	 * @since 1.4.2
	 * @since 1.6.16 Register the string for WPML translation.
	 *
	 * @param array $contents_strings Array of strings.
	 */
	$contents_strings = apply_filters( 'geodir_load_db_language', $contents_strings );

	$contents_strings = array_unique( $contents_strings );

	$contents_head   = array();
	$contents_head[] = "<?php";
	$contents_head[] = "/**";
	$contents_head[] = " * Translate language string stored in database. Ex: Custom Fields";
	$contents_head[] = " *";
	$contents_head[] = " * @package GeoDirectory";
	$contents_head[] = " * @since 1.4.2";
	$contents_head[] = " */";
	$contents_head[] = "";
	$contents_head[] = "// Language keys";

	$contents_foot   = array();
	$contents_foot[] = "";
	$contents_foot[] = "";

	$contents = implode( PHP_EOL, $contents_head );

	if ( ! empty( $contents_strings ) ) {
		foreach ( $contents_strings as $string ) {
			if ( is_scalar( $string ) && $string != '' ) {
				$string = str_replace( "'", "\'", $string );
				geodir_wpml_register_string( $string );
				$contents .= PHP_EOL . "__('" . $string . "', 'geodirectory');";
			}
		}
	}

	$contents .= implode( PHP_EOL, $contents_foot );

	if ( $wp_filesystem->put_contents( $language_file, $contents, FS_CHMOD_FILE ) ) {
		return false;
	} // Failure; could not write file.

	return true;
}

/**
 * Get the custom fields texts for translation
 *
 * @since   1.4.2
 * @since   1.5.7 Option values are translatable via db translation.
 * @since   1.6.11 Some new labels translation for advance custom fields.
 * @package GeoDirectory
 *
 * @global object $wpdb             WordPress database abstraction object.
 *
 * @param  array $translation_texts Array of text strings.
 *
 * @return array Translation texts.
 */
function geodir_load_custom_field_translation( $translation_texts = array() ) {
	global $wpdb;

	// Custom fields table
	$sql  = "SELECT admin_title, frontend_desc, frontend_title, clabels, required_msg, default_value, option_values, validation_msg FROM " . GEODIR_CUSTOM_FIELDS_TABLE;
	$rows = $wpdb->get_results( $sql );

	if ( ! empty( $rows ) ) {
		foreach ( $rows as $row ) {
			if ( ! empty( $row->admin_title ) ) {
				$translation_texts[] = stripslashes_deep( $row->admin_title );
			}

			if ( ! empty( $row->frontend_desc ) ) {
				$translation_texts[] = stripslashes_deep( $row->frontend_desc );
			}

			if ( ! empty( $row->frontend_title ) ) {
				$translation_texts[] = stripslashes_deep( $row->frontend_title );
			}

			if ( ! empty( $row->clabels ) ) {
				$translation_texts[] = stripslashes_deep( $row->clabels );
			}

			if ( ! empty( $row->required_msg ) ) {
				$translation_texts[] = stripslashes_deep( $row->required_msg );
			}
            
			if ( ! empty( $row->validation_msg ) ) {
				$translation_texts[] = stripslashes_deep( $row->validation_msg );
			}

			if ( ! empty( $row->default_value ) ) {
				$translation_texts[] = stripslashes_deep( $row->default_value );
			}

			if ( ! empty( $row->option_values ) ) {
				$option_values = geodir_string_values_to_options( stripslashes_deep( $row->option_values ) );

				if ( ! empty( $option_values ) ) {
					foreach ( $option_values as $option_value ) {
						if ( ! empty( $option_value['label'] ) ) {
							$translation_texts[] = $option_value['label'];
						}
					}
				}
			}
		}
	}

	// Custom sorting fields table
	$sql  = "SELECT frontend_title, asc_title, desc_title FROM " . GEODIR_CUSTOM_SORT_FIELDS_TABLE;
	$rows = $wpdb->get_results( $sql );

	if ( ! empty( $rows ) ) {
		foreach ( $rows as $row ) {
			if ( ! empty( $row->frontend_title ) ) {
				$translation_texts[] = stripslashes_deep( $row->frontend_title );
			}

			if ( ! empty( $row->asc_title ) ) {
				$translation_texts[] = stripslashes_deep( $row->asc_title );
			}

			if ( ! empty( $row->desc_title ) ) {
				$translation_texts[] = stripslashes_deep( $row->desc_title );
			}
		}
	}

	// Advance search filter fields table
	if ( defined( 'GEODIR_ADVANCE_SEARCH_TABLE' ) ) {
		$sql  = "SELECT field_site_name, front_search_title, first_search_text, last_search_text, field_desc FROM " . GEODIR_ADVANCE_SEARCH_TABLE;
		$rows = $wpdb->get_results( $sql );

		if ( ! empty( $rows ) ) {
			foreach ( $rows as $row ) {
				if ( ! empty( $row->field_site_name ) ) {
					$translation_texts[] = stripslashes_deep( $row->field_site_name );
				}

				if ( ! empty( $row->front_search_title ) ) {
					$translation_texts[] = stripslashes_deep( $row->front_search_title );
				}

				if ( ! empty( $row->first_search_text ) ) {
					$translation_texts[] = stripslashes_deep( $row->first_search_text );
				}

				if ( ! empty( $row->last_search_text ) ) {
					$translation_texts[] = stripslashes_deep( $row->last_search_text );
				}

				if ( ! empty( $row->field_desc ) ) {
					$translation_texts[] = stripslashes_deep( $row->field_desc );
				}
			}
		}
	}

	$translation_texts = ! empty( $translation_texts ) ? array_unique( $translation_texts ) : $translation_texts;

	return $translation_texts;
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
/*
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
	 * @param array $vars           The page title variables.
	 */
	$location_array  = apply_filters( 'geodir_filter_title_variables_location_arr_seo', $location_array, $vars );


	$location_replace_vars = geodir_location_replace_vars($location_array, NULL, '');
	$vars = $vars + $location_replace_vars;

	/**
	 * Filter the title variables after standard ones have been filtered for wpseo.
	 *
	 * @since   1.5.7
	 * @package GeoDirectory
	 *
	 * @param string $vars          The title with variables.
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
 * %%pagenumber%%                Replaced with the current page number
 *
 * @since   1.5.7
 * @package GeoDirectory
 *
 * @global object $wp     WordPress object.
 * @global object $post   The current post object.
 *
 * @param string $title   The title with variables.
 * @param string $gd_page The page being filtered.
 * @param string $sep     The separator, default: `|`.
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
		if ( $post_type && $singular_name = get_post_type_singular_label( $post_type ) ) {
			$singular_name = __( $singular_name, 'geodirectory' );
		}

		$title = str_replace( "%%pt_single%%", $singular_name, $title );
	}

	if ( strpos( $title, '%%pt_plural%%' ) !== false ) {
		$plural_name = '';
		if ( $post_type && $plural_name = get_post_type_plural_label( $post_type ) ) {
			$plural_name = __( $plural_name, 'geodirectory' );
		}

		$title = str_replace( "%%pt_plural%%", $plural_name, $title );
	}

	if ( strpos( $title, '%%category%%' ) !== false ) {
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
		$title = str_replace( "%%category%%", $cat_name, $title );
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
	 * @param string $title         The title with variables..
	 * @param string $gd_page       The page being filtered.
	 * @param string $sep           The separator, default: `|`.
	 */
	$location_array  = apply_filters( 'geodir_filter_title_variables_location_arr', $location_array, $title, $gd_page, $sep );
	
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
	 * @param string $title         The title with variables.
	 * @param array $location_array The array of location variables.
	 * @param string $gd_page       The page being filtered.
	 * @param string $sep           The separator, default: `|`.
	 */
	$title = apply_filters( 'geodir_replace_location_variables', $title, $location_array, $gd_page, $sep );
	
	if ( strpos( $title, '%%search_term%%' ) !== false ) {
		$search_term = '';
		if ( isset( $_REQUEST['s'] ) ) {
			$search_term = esc_attr( $_REQUEST['s'] );
		}
		$title = str_replace( "%%search_term%%", $search_term, $title );
	}

	if ( strpos( $title, '%%search_near%%' ) !== false ) {
		$search_term = '';
		if ( isset( $_REQUEST['snear'] ) ) {
			$search_term = esc_attr( $_REQUEST['snear'] );
		}
		$title = str_replace( "%%search_near%%", $search_term, $title );
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

	$title = wptexturize( $title );
	$title = convert_chars( $title );
	$title = esc_html( $title );

	/**
	 * Filter the title variables after standard ones have been filtered.
	 *
	 * @since   1.5.7
	 * @package GeoDirectory
	 *
	 * @param string $title         The title with variables.
	 * @param array $location_array The array of location variables.
	 * @param string $gd_page       The page being filtered.
	 * @param string $sep           The separator, default: `|`.
	 */

	return apply_filters( 'geodir_filter_title_variables_vars', $title, $location_array, $gd_page, $sep );
}

/**
 * Get the cpt texts for translation.
 *
 * @since   1.5.5
 * @package GeoDirectory
 *
 * @param  array $translation_texts Array of text strings.
 *
 * @return array Translation texts.
 */
function geodir_load_cpt_text_translation( $translation_texts = array() ) {
	$gd_post_types = geodir_get_posttypes( 'array' );

	if ( ! empty( $gd_post_types ) ) {
		foreach ( $gd_post_types as $post_type => $cpt_info ) {
			$labels      = isset( $cpt_info['labels'] ) ? $cpt_info['labels'] : '';
			$description = isset( $cpt_info['description'] ) ? $cpt_info['description'] : '';
			$seo         = isset( $cpt_info['seo'] ) ? $cpt_info['seo'] : '';

			if ( ! empty( $labels ) ) {
				if ( $labels['name'] != '' && ! in_array( $labels['name'], $translation_texts ) ) {
					$translation_texts[] = $labels['name'];
				}
				if ( $labels['singular_name'] != '' && ! in_array( $labels['singular_name'], $translation_texts ) ) {
					$translation_texts[] = $labels['singular_name'];
				}
				if ( $labels['add_new'] != '' && ! in_array( $labels['add_new'], $translation_texts ) ) {
					$translation_texts[] = $labels['add_new'];
				}
				if ( $labels['add_new_item'] != '' && ! in_array( $labels['add_new_item'], $translation_texts ) ) {
					$translation_texts[] = $labels['add_new_item'];
				}
				if ( $labels['edit_item'] != '' && ! in_array( $labels['edit_item'], $translation_texts ) ) {
					$translation_texts[] = $labels['edit_item'];
				}
				if ( $labels['new_item'] != '' && ! in_array( $labels['new_item'], $translation_texts ) ) {
					$translation_texts[] = $labels['new_item'];
				}
				if ( $labels['view_item'] != '' && ! in_array( $labels['view_item'], $translation_texts ) ) {
					$translation_texts[] = $labels['view_item'];
				}
				if ( $labels['search_items'] != '' && ! in_array( $labels['search_items'], $translation_texts ) ) {
					$translation_texts[] = $labels['search_items'];
				}
				if ( $labels['not_found'] != '' && ! in_array( $labels['not_found'], $translation_texts ) ) {
					$translation_texts[] = $labels['not_found'];
				}
				if ( $labels['not_found_in_trash'] != '' && ! in_array( $labels['not_found_in_trash'], $translation_texts ) ) {
					$translation_texts[] = $labels['not_found_in_trash'];
				}
				if ( isset( $labels['label_post_profile'] ) && $labels['label_post_profile'] != '' && ! in_array( $labels['label_post_profile'], $translation_texts ) ) {
					$translation_texts[] = $labels['label_post_profile'];
				}
				if ( isset( $labels['label_post_info'] ) && $labels['label_post_info'] != '' && ! in_array( $labels['label_post_info'], $translation_texts ) ) {
					$translation_texts[] = $labels['label_post_info'];
				}
				if ( isset( $labels['label_post_images'] ) && $labels['label_post_images'] != '' && ! in_array( $labels['label_post_images'], $translation_texts ) ) {
					$translation_texts[] = $labels['label_post_images'];
				}
				if ( isset( $labels['label_post_map'] ) && $labels['label_post_map'] != '' && ! in_array( $labels['label_post_map'], $translation_texts ) ) {
					$translation_texts[] = $labels['label_post_map'];
				}
				if ( isset( $labels['label_reviews'] ) && $labels['label_reviews'] != '' && ! in_array( $labels['label_reviews'], $translation_texts ) ) {
					$translation_texts[] = $labels['label_reviews'];
				}
				if ( isset( $labels['label_related_listing'] ) && $labels['label_related_listing'] != '' && ! in_array( $labels['label_related_listing'], $translation_texts ) ) {
					$translation_texts[] = $labels['label_related_listing'];
				}
			}

			if ( $description != '' && ! in_array( $description, $translation_texts ) ) {
				$translation_texts[] = normalize_whitespace( $description );
			}

			if ( ! empty( $seo ) ) {
				if ( isset( $seo['meta_keyword'] ) && $seo['meta_keyword'] != '' && ! in_array( $seo['meta_keyword'], $translation_texts ) ) {
					$translation_texts[] = normalize_whitespace( $seo['meta_keyword'] );
				}

				if ( isset( $seo['meta_description'] ) && $seo['meta_description'] != '' && ! in_array( $seo['meta_description'], $translation_texts ) ) {
					$translation_texts[] = normalize_whitespace( $seo['meta_description'] );
				}
			}
		}
	}
	$translation_texts = ! empty( $translation_texts ) ? array_unique( $translation_texts ) : $translation_texts;

	return $translation_texts;
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
	$location_manager = defined( 'POST_LOCATION_TABLE' ) ? true : false;

	if ( ! empty( $location_terms ) && $location_manager ) {
		$hide_country_part = geodir_get_option( 'geodir_location_hide_country_part' );
		$hide_region_part  = geodir_get_option( 'geodir_location_hide_region_part' );

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
	 * @param array $location_terms The array of location terms.
	 */
	return apply_filters('geodir_remove_location_terms',$location_terms);
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
	$replacement = null;

	$max = geodir_title_meta_pagenumbering( 'max' );
	if ( isset( $max ) && $max > 0 ) {
		$replacement = (string) $max;
	}

	return $replacement;
}

/**
 * Determine the page numbering of the current post/page/cpt.
 *
 * @param string $request   'nr'|'max' - whether to return the page number or the max number of pages.
 *
 * @since   1.6.0
 * @package GeoDirectory
 *
 * @global object $wp_query WordPress Query object.
 * @global object $post     The current post object.
 *
 * @return int|null The current page numbering.
 */
function geodir_title_meta_pagenumbering( $request = 'nr' ) {
	global $wp_query, $post;
	$max_num_pages = null;
	$page_number   = null;

	$max_num_pages = 1;

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

	$return = null;

	switch ( $request ) {
		case 'nr':
			$return = $page_number;
			break;
		case 'max':
			$return = $max_num_pages;
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
			 * @param object $term  The term object.
			 */
			$return = apply_filters( 'geodir_filter_empty_terms_filter', $return, $term );
		}
	}

	return $return;
}


/**
 * Remove the hentry class structured data from details pages.
 *
 * @since 1.6.5
 *
 * @param $class
 *
 * @return array
 */
function geodir_remove_hentry( $class ) {
	if ( geodir_is_page( 'detail' ) ) {
		$class = array_diff( $class, array( 'hentry' ) );
	}

	return $class;
}

//add_filter( 'post_class', 'geodir_remove_hentry' ); //@todo we dont seem to need to remove this with new template system

/**
 * Registers a individual text string for WPML translation.
 *
 * @since 1.6.16 Details page add locations to the term links.
 * @package GeoDirectory
 *
 * @param string $string The string that needs to be translated.
 * @param string $domain The plugin domain. Default geodirectory.
 * @param string $name The name of the string which helps to know what's being translated.
 */
function geodir_wpml_register_string( $string, $domain = 'geodirectory', $name = '' ) {
    do_action( 'wpml_register_single_string', $domain, $name, $string );
}

/**
 * Retrieves an individual WPML text string translation.
 *
 * @since 1.6.16 Details page add locations to the term links.
 * @package GeoDirectory
 *
 * @param string $string The string that needs to be translated.
 * @param string $domain The plugin domain. Default geodirectory.
 * @param string $name The name of the string which helps to know what's being translated.
 * @param string $language_code Return the translation in this language. Default is NULL which returns the current language.
 * @return string The translated string.
 */
function geodir_wpml_translate_string( $string, $domain = 'geodirectory', $name = '', $language_code = NULL ) {
    return apply_filters( 'wpml_translate_single_string', $string, $domain, $name, $language_code );
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

function geodir_theme_compatibility_file() {
    $theme = geodir_wp_theme_name();
    
    $compatibility_file = GEODIRECTORY_PLUGIN_DIR . 'includes/compatibility/class-geodir-' . geodir_strtolower( sanitize_file_name( $theme ) ) . '.php' ;

    return apply_filters( 'geodir_theme_compatibility_file', $compatibility_file, $theme );
}

function geodir_get_blogname() {
    $blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
    
    return apply_filters( 'geodir_get_blogname', $blogname );
}

function geodir_get_blogurl() {
    $blogurl = home_url( '/' );
    
    return apply_filters( 'geodir_get_blogurl', $blogurl );
}