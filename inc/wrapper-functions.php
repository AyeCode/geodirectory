<?php
/**
 * Helper wrapper functions while we convert.
 *
 * @package GeoDirectory
 * @since   3.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get list of geodirectory Post Types.
 *
 * @param string $output The output Type.
 *
 * @return array|object|string Post Types.
 * @since 2.0.0 options-plural option added.
 * @package GeoDirectory
 * @since 1.0.0
 * @since 1.5.1 options case added to get post type options array.
 */
function geodir_get_posttypes( $output = 'names' ) {
//	return \AyeCode\GeoDirectory\Core\Services\PostTypes::get_all( $output );
	return geodirectory()->post_types->get_all( $output );
}


/**
 * Get custom field using key and value.
 *
 * @since 1.0.0
 * @since 2.0.0 Returns array instead of object.
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @param string $key The key you want to look for.
 * @param string $value The value of the key you want to look for.
 * @param string $post_type The post type.
 * @param bool $stripslashes Return with stripslashes. Default True.
 * @return bool|mixed Returns field info when available. otherwise returns false.
 */
function geodir_get_field_infoby( $key = '', $value = '', $post_type = '', $stripslashes = true ) {
	return geodirectory()->fields->get_field_info( $key, $value, $post_type, $stripslashes );
}


/**
 * Returns custom fields based on page type. (detail page, listing page).
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @param int|string $package_id The package ID.
 * @param string $default Optional. When set to "default" it will display only default fields.
 * @param string $post_type Optional. The wordpress post type.
 * @param string $fields_location Optional. Where exactly are you going to place this custom fields?.
 * @return array|mixed|void Returns custom fields.
 */
function geodir_post_custom_fields( $package_id = '', $default = 'all', $post_type = 'gd_place', $fields_location = 'none' ) {
	return geodirectory()->fields->get_custom_fields( $package_id, $default, $post_type, $fields_location );
}
