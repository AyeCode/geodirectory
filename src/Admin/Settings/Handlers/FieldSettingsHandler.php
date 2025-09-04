<?php

namespace AyeCode\GeoDirectory\Admin\Settings\Handlers;

use AyeCode\GeoDirectory\Admin\Settings\PersistenceHandlerInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FieldSettingsHandler
 *
 * Handles the persistence of the custom fields for a CPT. This data comes
 * from the form builder and is typically stored in a custom database table.
 *
 * @package AyeCode\GeoDirectory\Admin\Settings\Handlers
 * @since 2.1.0
 */
class FieldSettingsHandler implements PersistenceHandlerInterface {

	/**
	 * Retrieves the custom field settings for a CPT from a custom table.
	 *
	 * @global \wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param string $post_type The post type slug.
	 *
	 * @return array An array of field settings, unserialized.
	 */
	public function get( string $post_type ): array {
		global $wpdb;

		// **ASSUMPTION**: You have a custom table for fields.
		// Replace `{$wpdb->prefix}geodir_custom_fields` with your actual table name.
		$table_name = $wpdb->prefix . 'geodir_custom_fields';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT fields FROM {$table_name} WHERE post_type = %s",
				$post_type
			)
		);

		if ( empty( $result ) || ! is_serialized( $result ) ) {
			return array();
		}

		// Data is stored serialized, so we unserialize it before returning.
		$fields = maybe_unserialize( $result );
		return is_array( $fields ) ? $fields : array();
	}

	/**
	 * Saves the complex field data to a custom database table.
	 *
	 * @global \wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param string $post_type The post type slug.
	 * @param array  $data      The array of field data from the form builder.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function save( string $post_type, array $data ): bool {
		global $wpdb;

		// **ASSUMPTION**: You have a custom table for fields.
		// Replace `{$wpdb->prefix}geodir_custom_fields` with your actual table name.
		$table_name = $wpdb->prefix . 'geodir_custom_fields';

		$serialized_data = maybe_serialize( $data );

		// Use REPLACE to either insert a new row or update an existing one.
		// This assumes `post_type` is a unique key in your custom table.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->replace(
			$table_name,
			array(
				'post_type' => $post_type,
				'fields'    => $serialized_data,
			),
			array( '%s', '%s' )
		);

		return false !== $result;
	}
}
