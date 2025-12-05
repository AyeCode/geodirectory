<?php
/**
 * Query Variables Service
 *
 * Handles parsing and sanitizing query variables from WordPress and HTTP requests.
 * Provides a centralized way to access location, search, and GeoDirectory-specific
 * query parameters.
 *
 * @package GeoDirectory\Core\Services
 * @since   3.0.0
 * @author  AyeCode Ltd
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core\Services;

/**
 * Service for parsing query variables and request parameters.
 *
 * @since 3.0.0
 */
final class QueryVars {
	/**
	 * Get latitude and longitude from query vars or request.
	 *
	 * Checks for lat/lon in:
	 * 1. WP query var 'latlon' (comma-separated)
	 * 2. $_REQUEST params 'sgeo_lat' and 'sgeo_lon'
	 *
	 * @since 3.0.0 (Migrated from GeoDir_Location::get_latlon)
	 *
	 * @return array Array with 'lat' and 'lon' keys, empty strings if not found.
	 */
	public function get_latlon(): array {
		global $wp;

		$latlon = array();

		if ( ! empty( $wp->query_vars['latlon'] ) ) {
			$gps = explode( ',', $wp->query_vars['latlon'] );
			$latlon['lat'] = isset( $gps[0] ) ? filter_var( $gps[0], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION ) : '';
			$latlon['lon'] = isset( $gps[1] ) ? filter_var( $gps[1], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION ) : '';
		} elseif ( ! empty( $_REQUEST['sgeo_lat'] ) && ! empty( $_REQUEST['sgeo_lon'] ) ) {
			$latlon['lat'] = isset( $_REQUEST['sgeo_lat'] ) ? filter_var( $_REQUEST['sgeo_lat'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION ) : '';
			$latlon['lon'] = isset( $_REQUEST['sgeo_lon'] ) ? filter_var( $_REQUEST['sgeo_lon'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION ) : '';
		}

		return $latlon;
	}

	/**
	 * Retrieve a query variable from WP query vars or $_REQUEST.
	 *
	 * Checks WP query vars first, then falls back to $_REQUEST.
	 *
	 * @since 3.0.0 (Migrated from GeoDir_Query::get_query_var)
	 *
	 * @param string $var     The variable key to retrieve.
	 * @param mixed  $default Optional. Value to return if not found. Default empty string.
	 * @return mixed The query variable value, sanitized if from $_REQUEST.
	 */
	public function get( string $var, $default = '' ) {
		global $wp;

		if ( ! empty( $wp ) && ! empty( $wp->query_vars ) && isset( $wp->query_vars[ $var ] ) ) {
			$value = $wp->query_vars[ $var ];
		} elseif ( isset( $_REQUEST[ $var ] ) ) {
			$value = geodir_clean( $_REQUEST[ $var ] );
		} else {
			$value = $default;
		}

		return $value;
	}

	/**
	 * Get search distance from request or settings.
	 *
	 * @since 3.0.0
	 *
	 * @return float Search radius distance.
	 */
	public function get_search_distance(): float {
		if ( isset( $_REQUEST['dist'] ) ) {
			$dist = ( $_REQUEST['dist'] != '0' && $_REQUEST['dist'] != '' ) ? geodir_sanitize_float( $_REQUEST['dist'] ) : 25000;
		} elseif ( geodir_get_option( 'search_radius' ) != '' ) {
			$dist = (float) geodir_get_option( 'search_radius' );
		} else {
			$dist = 25000;
		}

		return $dist;
	}

	/**
	 * Get search near location value.
	 *
	 * @since 3.0.0
	 *
	 * @return string Near location search term.
	 */
	public function get_search_near(): string {
		$snear = '';

		if ( isset( $_REQUEST['snear'] ) ) {
			$snear = trim( esc_attr( $_REQUEST['snear'] ) );
		}

		return $snear;
	}

	/**
	 * Get search keyword.
	 *
	 * @since 3.0.0
	 *
	 * @return string Search keyword.
	 */
	public function get_search_term(): string {
		$s = '';

		if ( isset( $_REQUEST['s'] ) ) {
			$s = get_search_query();
			if ( $s != '' ) {
				$s = str_replace( array( '%E2%80%99', "'" ), array( '%27', "'" ), $s ); // apple suck
			}
			$s = trim( esc_attr( wp_strip_all_tags( $s ) ) );
		}

		return $s;
	}

	/**
	 * Check if search is an exact match search (wrapped in quotes).
	 *
	 * @since 3.0.0
	 *
	 * @param string $search_term The search term to check.
	 * @return bool True if exact search.
	 */
	public function is_exact_search( string $search_term ): bool {
		if ( $search_term == '' ) {
			return false;
		}

		$search_keyword = trim( wp_specialchars_decode( stripslashes( $search_term ), ENT_QUOTES ), '"' );
		$match_keyword = wp_specialchars_decode( stripslashes( $search_term ), ENT_QUOTES );

		if ( strpos( $match_keyword, '"' ) !== false && ( '"' . $search_keyword . '"' == $match_keyword ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get post categories from search request.
	 *
	 * @since 3.0.0
	 *
	 * @return array Array of category IDs.
	 */
	public function get_search_categories(): array {
		$_post_category = array();

		if ( geodir_is_page( 'search' ) && isset( $_REQUEST['spost_category'] ) ) {
			if ( ( is_array( $_REQUEST['spost_category'] ) && ! empty( $_REQUEST['spost_category'][0] ) ) ||
			     ( ! is_array( $_REQUEST['spost_category'] ) && ! empty( $_REQUEST['spost_category'] ) ) ) {
				if ( is_array( $_REQUEST['spost_category'] ) ) {
					$_post_category = array_map( 'absint', $_REQUEST['spost_category'] );
				} else {
					$_post_category = array( absint( $_REQUEST['spost_category'] ) );
				}
			}
		}

		return $_post_category;
	}

	/**
	 * Get post type from search request.
	 *
	 * @since 3.0.0
	 *
	 * @return string Post type slug.
	 */
	public function get_search_post_type(): string {
		if ( isset( $_REQUEST['stype'] ) ) {
			$post_type = esc_attr( wp_strip_all_tags( $_REQUEST['stype'] ) );
		} else {
			$post_type = 'gd_place';
		}

		return $post_type;
	}

	/**
	 * Get sort by parameter.
	 *
	 * @since 3.0.0
	 *
	 * @return string Sort by parameter.
	 */
	public function get_sort_by(): string {
		$sort_by = '';

		if ( get_query_var( 'order_by' ) ) {
			$sort_by = get_query_var( 'order_by' );
		}

		if ( isset( $_REQUEST['sort_by'] ) && $_REQUEST['sort_by'] != '' && is_main_query() ) {
			$sort_by = esc_attr( $_REQUEST['sort_by'] );
		}

		return $sort_by;
	}
}
