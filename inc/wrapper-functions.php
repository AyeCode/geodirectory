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
