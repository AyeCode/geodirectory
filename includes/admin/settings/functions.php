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
 * @since 2.0.0
 *
 * @global array $geodir_options Array of all the GD options.
 *
 * @param string $option Name of option to retrieve.
 * @param mixed $default Optional. Default value to return if the option does not exist.
 *
 * @return mixed
 */
function geodir_get_option( $key = '', $default = false ) {
	global $geodir_options;

	$value = isset( $geodir_options[ $key ] ) ? $geodir_options[ $key ] : $default;

	$value = apply_filters( 'geodir_get_option', $value, $key, $default );

	return apply_filters( 'geodir_get_option_' . $key, $value, $key, $default );
}

/**
 * Update an option.
 *
 * Updates an gd setting value in both the db and the global variable.
 * Warning: Passing in an empty, false or null string value will remove
 *          the key from the geodir_options array.
 *
 * @since 2.0.0
 *
 * @global array $geodir_options Array of all the GD options.
 *
 * @param string $key The Key to update.
 * @param string|bool|int $value The value to set the key to.
 *
 * @return boolean True if updated, false if not.
 */
function geodir_update_option( $key = '', $value = false ) {
	if ( empty( $key ) ) {
		return false;
	}

	$options = get_option( 'geodir_settings' );
	if ( empty( $options ) ) {
		$options = array();
	}

	$value = apply_filters( 'geodir_update_option', $value, $key );

	$options[ $key ] = $value;
	$updated         = update_option( 'geodir_settings', $options );

	if ( $updated ) {
		global $geodir_options;
		$geodir_options[ $key ] = $value;
	}

	return $updated;
}

/**
 * Remove an option.
 *
 * Removes an GD setting value in both the db and the global variable.
 *
 * @since 2.0.0
 *
 * @global array $geodir_options Array of all the GD options.
 *
 * @param string $key The Key to delete.
 *
 * @return boolean True if removed, false if not.
 */
function geodir_delete_option( $key = '' ) {
	if ( empty( $key ) ) {
		return false;
	}

	$options = get_option( 'geodir_settings' );
	if ( empty( $options ) ) {
		$options = array();
	}

	if ( isset( $options[ $key ] ) ) {
		unset( $options[ $key ] );
	}

	$updated = update_option( 'geodir_settings', $options );

	if ( $updated ) {
		global $geodir_options;
		$geodir_options = $options;
	}

	return $updated;
}

/**
 * Get GD Settings.
 *
 * Retrieves all plugin settings.
 *
 * @since 2.0.0
 *
 * @return array GD settings
 */
function geodir_get_settings() {
	$settings = get_option( 'geodir_settings' );

	if ( empty( $settings ) ) {
		// Update old settings with new single option.
		$settings = array();

		update_option( 'geodir_settings', $settings );
	}

	return apply_filters( 'geodir_get_settings', $settings );
}

