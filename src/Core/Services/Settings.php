<?php
/**
 * GeoDirectory Settings Service
 *
 * @package     GeoDirectory\Core\Utils
 * @since       3.0.0
 * @author      AyeCode Ltd
 */

// Use strict types for better code quality.
declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core\Services;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages the plugin's settings, acting as a wrapper for WordPress options.
 *
 * This class is instantiated as a singleton by the container. It loads all
 * plugin options into memory once for efficient access.
 *
 * @since 3.0.0
 */
final class Settings {
	/**
	 * The array of all GeoDirectory settings loaded from the database.
	 *
	 * @var array
	 */
	private array $options = [];

	/**
	 * Constructor.
	 *
	 * Loads all settings from the 'geodir_settings' option upon instantiation.
	 */
	public function __construct() {
		$this->options = get_option( 'geodir_settings', [] );
	}

	/**
	 * Get a specific setting value.
	 *
	 * @param string $key     Name of the setting to retrieve.
	 * @param mixed  $default Optional. Default value to return if the setting does not exist.
	 * @return mixed The value of the setting.
	 */
	public function get( string $key, $default = false ) {
		$value = $this->options[ $key ] ?? $default;

		$value = apply_filters( 'geodir_get_option', $value, $key, $default );

		return apply_filters( 'geodir_get_option_' . $key, $value, $key, $default );
	}

	/**
	 * Update a specific setting.
	 *
	 * Updates the value in the database and in the current instance.
	 *
	 * @param string $key   The key of the setting to update.
	 * @param mixed  $value The new value.
	 * @return bool True if the option was updated, false otherwise.
	 */
	public function update( string $key, $value ): bool {
		if ( empty( $key ) ) {
			return false;
		}

		$value = apply_filters( 'geodir_update_option', $value, $key );

		// Update the value in our in-memory array.
		$this->options[ $key ] = $value;
//		print_r( $value );
//		echo $key.'>>>>>>>>>>>>>>>>>>>>>>';print_r( $this->options );exit;

		// Save the entire options array back to the database.
		return update_option( 'geodir_settings', $this->options );
	}



	/**
	 * Delete a specific setting.
	 *
	 * @param string $key The key of the setting to delete.
	 * @return bool True if the option was updated, false otherwise.
	 */
	public function delete( string $key ): bool {
		if ( empty( $key ) || ! isset( $this->options[ $key ] ) ) {
			return false;
		}

		// Remove the key from our in-memory array.
		unset( $this->options[ $key ] );

		// Save the entire options array back to the database.
		return update_option( 'geodir_settings', $this->options );
	}

	/**
	 * Get the entire array of settings.
	 *
	 * @return array All GeoDirectory settings.
	 */
	public function get_all(): array {
		return apply_filters( 'geodir_get_settings', $this->options );
	}
}
