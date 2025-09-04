<?php

namespace AyeCode\GeoDirectory\Admin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Defines a strict contract for all settings persistence handlers.
 *
 * @package AyeCode\GeoDirectory\Admin\Settings
 * @since 2.1.0
 */
interface PersistenceHandlerInterface {
	/**
	 * Retrieves the settings for a specific post type.
	 *
	 * @param string $post_type The post type slug (e.g., 'gd_place').
	 * @return array The array of settings for this handler.
	 */
	public function get( string $post_type ): array;

	/**
	 * Saves the settings for a specific post type.
	 *
	 * @param string $post_type The post type slug.
	 * @param array  $data      The data to be saved.
	 * @return bool True on success, false on failure.
	 */
	public function save( string $post_type, array $data ): bool;
}
