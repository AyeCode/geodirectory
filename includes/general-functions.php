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
 * Returns add listing page link.
 *
 * @since   1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
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
	if ( isset( $_SERVER['HTTPS'] ) && ! empty( $_SERVER['HTTPS'] ) && ( strtolower( $_SERVER['HTTPS'] ) == 'on' ) ) {
		$pageURL .= "s";
	}
	$pageURL .= "://";

	/*
	 * Since we are assigning the URI from the server variables, we first need
	 * to determine if we are running on apache or IIS.  If PHP_SELF and REQUEST_URI
	 * are present, we will assume we are running on apache.
	 */
	if ( ! empty( $_SERVER['PHP_SELF'] ) && ! empty( $_SERVER['REQUEST_URI'] ) ) {
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
 * @since   1.0.0
 * @since   1.5.6 Added to check GD invoices and GD checkout pages.
 * @since   1.5.7 Updated to validate buddypress dashboard listings page as a author page.
 * @package GeoDirectory
 * @global object $wp_query WordPress Query object.
 * @global object $post The current post object.
 *
 * @param string $gdpage The page type.
 *
 * @return bool If valid returns true. Otherwise false.
 */
function geodir_is_page( $gdpage = '' ) {

	global $wp_query, $post, $wp;
	//if(!is_admin()):
	$page_id = '';// get_query_var( 'page_id' ) ? get_query_var( 'page_id' ) : '';

	if ( empty( $page_id ) && $wp_query->is_page && $wp_query->queried_object_id ) {
		$page_id = $wp_query->queried_object_id;
	}

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
			if ( is_author() ) {
				return true;
			}

			if ( function_exists( 'bp_loggedin_user_id' ) && function_exists( 'bp_displayed_user_id' ) && $my_id = (int) bp_loggedin_user_id() ) {
				if ( ( (bool) bp_is_current_component( 'listings' ) || (bool) bp_is_current_component( 'favorites' ) ) && $my_id > 0 && $my_id == (int) bp_displayed_user_id() ) {
					return true;
				}
			}
			break;
		case 'search':
			if ( isset( $_REQUEST['geodir_search'] ) || ( is_page() && $page_id == geodir_search_page_id() ) ) {
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

	return $earthMeanRadius;
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

	$deltaLatitude  = deg2rad( (float) $point2['latitude'] - (float) $point1['latitude'] );
	$deltaLongitude = deg2rad( (float) $point2['longitude'] - (float) $point1['longitude'] );
	$a              = sin( $deltaLatitude / 2 ) * sin( $deltaLatitude / 2 ) +
	                  cos( deg2rad( (float) $point1['latitude'] ) ) * cos( deg2rad( (float) $point2['latitude'] ) ) *
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
 * function to add class to body when multi post type is active.
 *
 * @since   1.0.0
 * @since   1.5.6 Add geodir-page class to body for all gd pages.
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
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

	if ( geodir_is_page( 'search' ) ) {
		$classes[] = 'geodir-page-search';
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
 * @param array $query_args The query array.
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

	$sort_by = ! empty( $query_args['order_by'] ) ? $query_args['order_by'] : '';

	$orderby = GeoDir_Query::sort_by_sql( $sort_by, $post_type );

//	echo '###'.$orderby.'###';
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
	global $wpdb, $plugin_prefix, $table_prefix;
	$GLOBALS['gd_query_args_widgets'] = $query_args;
	$gd_query_args_widgets            = $query_args;

	$post_type = empty( $query_args['post_type'] ) ? 'gd_place' : $query_args['post_type'];
	$table     = $plugin_prefix . $post_type . '_detail';

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

	$join = "INNER JOIN " . $table . " ON (" . $table . ".post_id = " . $wpdb->posts . ".ID)";

	/**
	 * Filter widget listing join clause string part that is being used for query.
	 *
	 * @since 1.0.0
	 *
	 * @param string $join Join clause string.
	 * @param string $post_type Post type.
	 */
	$join = apply_filters( 'geodir_filter_widget_listings_join', $join, $post_type );

	$post_status = is_super_admin() ? " OR " . $wpdb->posts . ".post_status = 'private'" : '';

	$where = " AND ( " . $wpdb->posts . ".post_status = 'publish' " . $post_status . " ) AND " . $wpdb->posts . ".post_type = '" . $post_type . "'";

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

	$groupby = " GROUP BY $wpdb->posts.ID "; //@todo is this needed? faster without
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
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 *
 * @param string $fields Fields SQL.
 *
 * @return string Modified fields SQL.
 */
function geodir_function_widget_listings_fields( $fields ) {
	global $wpdb, $plugin_prefix, $gd_query_args_widgets, $gd_post;

	$query_args = $gd_query_args_widgets;
	if ( empty( $query_args ) || empty( $query_args['is_geodir_loop'] ) ) {
		return $fields;
	}

	if ( ! empty( $query_args['distance_to_post'] ) && ! empty( $gd_post->latitude ) && ! empty( $gd_post->longitude ) ) {
		$post_type = $gd_post->post_type != 'revision' ? $gd_post->post_type : get_post_type( wp_get_post_parent_id( $gd_post->ID ) );
		$table     = $plugin_prefix . $post_type . '_detail';

		$radius = geodir_getDistanceRadius( geodir_get_option( 'search_distance_long' ) );

		$fields .= ", ( {$radius} * 2 * ASIN( SQRT( POWER( SIN( ( ABS( {$gd_post->latitude} ) - ABS( {$table}.latitude ) ) * PI() / 180 / 2 ), 2 ) + COS( ABS( {$gd_post->latitude} ) * PI() / 180 ) * COS( ABS( {$table}.latitude ) * PI() / 180 ) * POWER( SIN( ( {$gd_post->longitude} - {$table}.longitude ) * PI() / 180 / 2 ), 2 ) ) ) ) AS distance";
	}

	return $fields;
}

add_filter( 'geodir_filter_widget_listings_fields', 'geodir_function_widget_listings_fields' );


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
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 *
 * @param string $where Where clause SQL.
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
			$where .= " AND " . $table . ".featured = '1'";
		}

		if ( ! empty( $query_args['show_special_only'] ) ) {
			$where .= " AND ( " . $table . ".special_offers != '' AND " . $table . ".special_offers IS NOT NULL )";
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

		if ( ! empty( $query_args['show_favorites_only'] ) ) {
			$user_favorites = '-1';

			if ( ! empty( $query_args['favorites_by_user'] ) ) {
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
 * Sanitize location name.
 *
 * @since   1.0.0
 * @package GeoDirectory
 *
 * @param string $type Location type. Can be gd_country, gd_region, gd_city.
 * @param string $name Location name.
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
	if ( ! str_replace( 'libraries=places', '', $extra ) && ( geodir_is_page( 'detail' ) || $add_google_places_api ) ) {
		$extra .= "&amp;libraries=places";
	}

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