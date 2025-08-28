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
	return \AyeCode\GeoDirectory\Core\Utils\PostTypes::get_posttypes( $output );
}


function geodir_get_option($key = '', $default = false ){
	return ( new AyeCode\GeoDirectory\Core\Utils\Settings )->get($key, $default);
}
