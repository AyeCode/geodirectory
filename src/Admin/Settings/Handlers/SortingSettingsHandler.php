<?php

namespace AyeCode\GeoDirectory\Admin\Settings\Handlers;

use AyeCode\GeoDirectory\Admin\Settings\PersistenceHandlerInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SortingSettingsHandler
 *
 * Handles persistence for the sorting options builder.
 *
 * @package AyeCode\GeoDirectory\Admin\Settings\Handlers
 * @since 2.1.0
 */
class SortingSettingsHandler implements PersistenceHandlerInterface {
	/**
	 * Retrieves sorting settings for a CPT.
	 *
	 * @param string $post_type The post type slug.
	 *
	 * @return array The sorting settings.
	 */
	public function get( string $post_type ): array {
		// Sorting options are stored as a single option array, prefixed for the CPT.
		// Example option name: 'geodir_sorting_gd_place'
		$options = get_option( 'geodir_sorting_' . $post_type, array() );
		return is_array( $options ) ? $options : array();
	}

	/**
	 * Saves sorting settings for a CPT.
	 *
	 * @param string $post_type The post type slug.
	 * @param array  $data      The settings data to save.
	 *
	 * @return bool Result of the update_option call.
	 */
	public function save( string $post_type, array $data ): bool {
		return update_option( 'geodir_sorting_' . $post_type, $data );
	}
}
