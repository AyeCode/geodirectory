<?php
/**
 * Custom Field Repository
 *
 * Handles all database interactions for the custom fields table.
 *
 * @package GeoDirectory\Database
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Database;

final class CustomFieldRepository {
	private \wpdb $db;
	private string $table_name;

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->db         = $wpdb;
		$this->table_name = $wpdb->prefix . 'geodir_custom_fields';
	}

	/**
	 * Finds a single custom field by its ID.
	 *
	 * @param int $field_id The ID of the custom field.
	 * @return object|null The field data row, or null if not found.
	 */
	public function find( int $field_id ): ?object {
		return $this->db->get_row(
			$this->db->prepare( "SELECT * FROM {$this->table_name} WHERE fid = %d", $field_id )
		);
	}

	/**
	 * Gets all custom fields for a given post type.
	 *
	 * @param string $post_type The post type slug.
	 * @return array An array of field data rows.
	 */
	public function find_by_post_type( string $post_type ): array {
		return $this->db->get_results(
			$this->db->prepare( "SELECT * FROM {$this->table_name} WHERE post_type = %s ORDER BY sort_order ASC", $post_type )
		);
	}

	// Add other methods here for creating, updating, or deleting fields...
}
