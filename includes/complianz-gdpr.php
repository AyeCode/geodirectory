<?php
/**
 * Geodirectory Complianz GDPR integration functions
 *
 *
 * @package     GeoDirectory
 * @category    Core
 * @author      AyeCode
 * @since       2.1.0.17
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Add services to the list of detected items, so it will get set as default, and will be added to the notice about it.
 *
 * @param $services
 *
 * @return array
 */
function cmplz_geodirectory_detected_services( $services ) {
	$map = GeoDir_Maps::active_map();

	if ( in_array( $map, array( 'auto', 'google' ) ) && ! in_array( 'google-maps', $services ) ) {
		$services[] = 'google-maps';
	}

	if ( in_array( $map, array( 'auto', 'osm' ) ) && ! in_array( 'openstreetmaps', $services ) ) {
		$services[] = 'openstreetmaps';
	}

	return $services;
}

add_filter( 'cmplz_detected_services', 'cmplz_geodirectory_detected_services', 21, 1 );

/**
 * Complianz GDPR set lazy load map.
 *
 * @since 2.1.0.17
 *
 * @param string $lazy_load Lazy load type.
 * @return string Filtered lazy load map.
 */
function cmplz_geodirectory_lazy_load_map( $lazy_load = '' ) {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return $lazy_load;
	}

	if ( ! ( isset( $_COOKIE['cmplz_marketing'] ) && $_COOKIE['cmplz_marketing'] == 'allow' ) ) {
		$lazy_load = 'click';
	}

	return $lazy_load;
}
add_filter( 'geodir_lazy_load_map', 'cmplz_geodirectory_lazy_load_map', 999, 1 );

/**
 * Filter lazy load map params.
 *
 * @since 2.1.0.17
 *
 * @param array $params Map params.
 * @return array Filtered map params.
 */
function cmplz_geodirectory_map_params( $params ) {
	// Add class to accepts the complianz banner
	if ( ! empty( $params ) && ! empty( $params['lazyLoadButton'] ) && ! ( isset( $_COOKIE['cmplz_marketing'] ) && $_COOKIE['cmplz_marketing'] == 'allow' ) ) {
		$params['lazyLoadButton'] = str_replace( 'btn ', 'btn cmplz-accept-marketing ', $params['lazyLoadButton'] );
	}

	return $params;
}
add_filter( 'geodir_map_params', 'cmplz_geodirectory_map_params', 21, 1 );