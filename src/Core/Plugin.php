<?php
/**
 * GeoDirectory Core Plugin Helper
 *
 * @package GeoDirectory\Core
 * @since   3.0.0
 * @author  AyeCode Ltd
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core;

/**
 * Provides access to core plugin paths and URLs.
 *
 * This class acts as a central, reliable source for file paths and URLs,
 * replacing the need for global constants throughout the codebase.
 *
 * @since 3.0.0
 */
final class Plugin {
	/**
	 * The plugin's base file path.
	 *
	 * @var string
	 */
	private static string $path;

	/**
	 * The plugin's base URL.
	 *
	 * @var string
	 */
	private static string $url;

	/**
	 * Initializes the class with the main plugin file path.
	 *
	 * This should be called once when the plugin boots up.
	 *
	 * @param string $plugin_file The full path to the main plugin file (__FILE__).
	 *
	 * @return void
	 */
	public static function init( string $plugin_file ): void {
		self::$path = plugin_dir_path( $plugin_file );
		self::$url  = plugin_dir_url( $plugin_file );
	}

	/**
	 * Gets the full file path to the plugin's root directory.
	 *
	 * @param string $sub_path Optional. A sub-path to append.
	 *
	 * @return string The full file path.
	 */
	public static function path( string $sub_path = '' ): string {
		return self::$path . $sub_path;
	}

	/**
	 * Gets the full URL to the plugin's root directory.
	 *
	 * @param string $sub_path Optional. A sub-path to append.
	 *
	 * @return string The full URL.
	 */
	public static function url( string $sub_path = '' ): string {
		return self::$url . $sub_path;
	}
}
