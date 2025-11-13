<?php
/**
 * Location functions
 *
 * These functions act as thin wrappers around the Core classes.
 * For direct access, use: geodirectory()->locations, geodirectory()->geolocation, geodirectory()->location_formatter
 *
 * @since 1.0.0
 * @package GeoDirectory
 */

/**
 * Returns location slug using location string.
 *
 * @since 2.0.0
 * @package GeoDirectory
 * @param string $location_string The location string.
 * @return string The location slug.
 */
function geodir_create_location_slug( $location_string ) {
	return geodirectory()->locations->create_location_slug( $location_string );
}

/**
 * Returns country selection dropdown box.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @param string $post_country The dropdown default selected country.
 * @param string $prefix Not yet implemented.
 * @param bool $return_array Return array instead of HTML.
 * @return string|array Country dropdown HTML or array.
 */
function geodir_get_country_dl( $post_country = '', $prefix = '', $return_array = false ) {
	return geodirectory()->locations->get_country_dropdown( $post_country, $prefix, $return_array );
}

/**
 * Returns an array of all countries from wp country database.
 *
 * @since 2.2.14
 *
 * @param array $args The parameters. Default empty.
 * @param bool  $split Split the country like UK when enabled. Default True.
 * @return array Countries array.
 */
function geodir_wp_countries( $args = array(), $split = true ) {
	return geodirectory()->locations->get_countries( $args, $split );
}

/**
 * Returns an array of all countries by key val where the key is the country name untranslated and the val is the country name translated.
 *
 * @since 2.0.0
 *
 * @package GeoDirectory
 *
 * @return array $countries Countries array.
 */
function geodir_get_countries() {
	return geodirectory()->locations->get_countries_list();
}

/**
 * Returns address using latitude and longitude.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @param string $lat Latitude string.
 * @param string $lng Longitude string.
 * @return string|bool Returns address on success.
 */
function geodir_get_address_by_lat_lan( $lat, $lng ) {
	return geodirectory()->geolocation->get_address_by_lat_lng( $lat, $lng );
}

/**
 * Returns current location terms.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wp WordPress object.
 *
 * @param string $location_array_from Place to look for location array. Default: 'session'. @deprecated
 * @param string $gd_post_type The post type.
 * @return array The location term array.
 */
function geodir_get_current_location_terms( $location_array_from = null, $gd_post_type = '' ) {
	return geodirectory()->locations->get_current_location_terms( $location_array_from, $gd_post_type );
}

/**
 * Get location name from slug.
 *
 * @since 1.0.0
 * @package GeoDirectory
 *
 * @param string $slug Location slug.
 * @param string $type Location type (country, region, city, neighbourhood).
 * @return string Location name.
 */
function geodir_location_name_from_slug( $slug, $type ) {
	// Stub function - to be implemented if needed
	return $slug;
}

/**
 * Returns location link based on location type.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @param string $which_location Location link type. Default: 'current'.
 * @return bool|string
 */
function geodir_get_location_link( $which_location = 'current' ) {
	return geodirectory()->location_formatter->get_location_link( $which_location );
}

/**
 * Returns openstreetmap address using latitude and longitude.
 *
 * @since 1.6.5
 * @package GeoDirectory
 * @param string $lat Latitude string.
 * @param string $lng Longitude string.
 * @return array|bool Returns address on success.
 */
function geodir_get_osm_address_by_lat_lan( $lat, $lng ) {
	return geodirectory()->geolocation->get_osm_address_by_lat_lng( $lat, $lng );
}

/**
 * Replace the location variables.
 *
 * @since   1.6.16
 * @package GeoDirectory
 *
 * @global object $wp WordPress object.
 *
 * @param string $content       The content with variables.
 * @param array $location_array The array of location variables.
 * @param string $sep           The separator, Optional.
 * @param string $gd_page       The page being filtered. Optional.
 * @return string Filtered content.
 */
function geodir_replace_location_variables( $content, $location_array = array(), $sep = null, $gd_page = '' ) {
	return geodirectory()->location_formatter->replace_location_variables( $content, $location_array, $sep, $gd_page );
}
add_filter( 'geodir_replace_location_variables', 'geodir_replace_location_variables' );

/**
 * Function for replace location values.
 *
 * @since 2.0.0
 *
 * @param array $location_array Optional. The array of location variables. Default array().
 * @param string $sep Optional. The separator. Default null.
 * @param string $gd_page Optional.The page being filtered. Default null.
 * @return array $location_replace_vars.
 */
function geodir_location_replace_vars( $location_array = array(), $sep = null, $gd_page = '' ) {
	return geodirectory()->location_formatter->location_replace_vars( $location_array, $sep, $gd_page );
}

/**
 * Check location slug.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @global string $table_prefix WordPress Database Table prefix.
 * @param string $slug Term slug.
 * @return string Modified term slug.
 */
function geodir_location_slug_check( $slug ) {
	return geodirectory()->location_formatter->location_slug_check( $slug );
}
add_filter( 'geodir_location_slug_check', 'geodir_location_slug_check' );

/**
 * Get location info via from IP.
 *
 * @since 2.0.0.60 Changed to new site for IP information.
 * @param string $ip
 *
 * @return mixed|void
 */
function geodir_geo_by_ip( $ip = '' ) {
	return geodirectory()->geolocation->geo_by_ip( $ip );
}

/**
 * Get location data from ip via geoplugin.net
 *
 * @deprecated 2.0.0.60
 * @param string $ip
 *
 * @return array|mixed|null|void
 */
function geodir_geoplugin_data( $ip = '' ) {
	// Deprecated - kept for backwards compatibility
	return geodirectory()->geolocation->ip_api_data( $ip );
}

/**
 * Get location data from ip via ip-api.com.
 *
 * @param string $ip
 * @since 2.0.0.60
 * @return array|bool|mixed|null|object|void
 */
function geodir_ip_api_data( $ip = '' ) {
	return geodirectory()->geolocation->ip_api_data( $ip );
}

/**
 * Get timezone data from via timezone api service.
 *
 * @since 2.0.0.66
 *
 * @param string $latitude Latitude
 * @param string $longitude Longitude
 * @param int $timestamp Timestamp
 * @return array|WP_Error
 */
function geodir_get_timezone_by_lat_lon( $latitude, $longitude, $timestamp = 0 ) {
	return geodirectory()->geolocation->get_timezone_by_lat_lng( $latitude, $longitude, $timestamp );
}

/**
 * Get the GPS from a post address.
 *
 * @param array|string $address Address array or string.
 * @param bool $wp_error Whether to return WP_Error on failure.
 *
 * @return WP_Error|array|bool
 */
function geodir_get_gps_from_address( $address = array(), $wp_error = false ) {
	return geodirectory()->geolocation->get_gps_from_address( $address, $wp_error );
}

/**
 * Get GPS info for the address using Google Geocode API.
 *
 * @since 2.0.0.66
 *
 * @param array|string $address Array of address element or full address.
 * @param bool $wp_error Optional. Whether to return a WP_Error on failure. Default false.
 * @return bool|WP_Error GPS data or WP_Error on failure.
 */
function geodir_google_get_gps_from_address( $address, $wp_error = false ) {
	return geodirectory()->geolocation->google_get_gps_from_address( $address, $wp_error );
}

/**
 * Get GPS info for the address using OpenStreetMap Nominatim API.
 *
 * @since 2.0.0.66
 *
 * @param array|string $address Array of address element or full address.
 * @param bool $wp_error Optional. Whether to return a WP_Error on failure. Default false.
 * @return bool|WP_Error GPS data or WP_Error on failure.
 */
function geodir_osm_get_gps_from_address( $address, $wp_error = false ) {
	return geodirectory()->geolocation->osm_get_gps_from_address( $address, $wp_error );
}

/**
 * Check multi city active or not.
 *
 * @since 2.1.0.7
 *
 * @return bool True if multi-city is active.
 */
function geodir_core_multi_city() {
	return geodirectory()->locations->core_multi_city();
}

/**
 * Render the post address field value.
 *
 * @since 2.1.1.9
 *
 * @param string $value Field value.
 * @param string $key Field key.
 * @param int|object $post The post.
 * @param mixed $default Whether to use default value.
 * @return string Filtered field value.
 */
function geodir_post_address( $value, $key, $post, $default = null ) {
	return geodirectory()->location_formatter->post_address( $value, $key, $post, $default );
}

/**
 * Check split of UK into England, Northern Ireland, Scotland & Wales.
 *
 * @since 2.2.14
 *
 * @return bool True to split otherwise False.
 */
function geodir_split_uk() {
	return geodirectory()->locations->split_uk();
}
