<?php
/**
 * Register Settings
 *
 * @package     GeoDirectory
 * @since       2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get an option.
 *
 * Wrapper for the Settings service. Maintains backwards compatibility
 * while using the v3 Settings service internally.
 *
 * @since 2.0.0
 * @since 3.0.0 Updated to use Settings service.
 *
 * @param string $key     Name of option to retrieve.
 * @param mixed  $default Optional. Default value to return if the option does not exist.
 * @return mixed The option value.
 */
function geodir_get_option( $key = '', $default = false ) {
	return geodirectory()->settings->get( $key, $default );
}

/**
 * Update an option.
 *
 * Wrapper for the Settings service. Maintains backwards compatibility
 * while using the v3 Settings service internally.
 *
 * @since 2.0.0
 * @since 3.0.0 Updated to use Settings service.
 *
 * @param string          $key   The key to update.
 * @param string|bool|int $value The value to set the key to.
 * @return bool True if updated, false if not.
 */
function geodir_update_option( $key = '', $value = false ) {
	return geodirectory()->settings->update( $key, $value );
}

/**
 * Remove an option.
 *
 * Wrapper for the Settings service. Maintains backwards compatibility
 * while using the v3 Settings service internally.
 *
 * @since 2.0.0
 * @since 3.0.0 Updated to use Settings service.
 *
 * @param string $key The key to delete.
 * @return bool True if removed, false if not.
 */
function geodir_delete_option( $key = '' ) {
	return geodirectory()->settings->delete( $key );
}

/**
 * Get GD Settings.
 *
 * Wrapper for the Settings service. Maintains backwards compatibility
 * while using the v3 Settings service internally.
 *
 * @since 2.0.0
 * @since 3.0.0 Updated to use Settings service.
 *
 * @return array GD settings array.
 */
function geodir_get_settings() {
	return geodirectory()->settings->get_all();
}

