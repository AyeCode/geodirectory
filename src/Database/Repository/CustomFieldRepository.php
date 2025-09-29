<?php
/**
 * Custom Field Repository
 *
 * Handles all database interactions for the custom fields table.
 *
 * @package GeoDirectory\Database\Repository
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Database\Repository;

// Make sure to import the new Schema class.
use AyeCode\GeoDirectory\Database\Schema\CustomFieldSchema;

final class CustomFieldRepository {

	/**
	 * @var \wpdb The WordPress database object.
	 */
	private $db;

	/**
	 * @var string The name of the custom_fields table.
	 */
	private $table_name;

	public function __construct() {
		global $wpdb;
		$this->db         = $wpdb;
		$this->table_name = geodirectory()->tables->get( 'custom_fields' );
	}

	/**
	 * Gets all custom fields for a given post type, ordered by sort_order.
	 *
	 * @param string $post_type The post type slug.
	 * @return array The raw data from the database.
	 */
	public function get_by_post_type( string $post_type ): array {
		$results = $this->db->get_results(
			$this->db->prepare(
				"SELECT * FROM {$this->table_name} WHERE post_type = %s ORDER BY sort_order ASC",
				$post_type
			),
			ARRAY_A
		);

		return $results ? $results : [];
	}

	/**
	 * Synchronizes the custom fields in the database with the provided data array.
	 *
	 * This method intelligently handles updates for existing fields, inserts for new fields,
	 * and deletes any fields that are no longer present. It is now driven by the
	 * CustomFieldSchema class to ensure data integrity.
	 *
	 * @param string $post_type The post type slug.
	 * @param array $fields The numerically indexed array of fields to save.
	 * @return bool True on success.
	 */
	public function sync_by_post_type( string $post_type, array $fields ): bool {
		// 1. Get schema information. This is our single source of truth.
		$schema         = new CustomFieldSchema();
		$schema_columns = $schema->get_column_names();
		$schema_formats = $schema->get_formats();

		// 2. Get the current state of field IDs from the database.
		$existing_ids = $this->db->get_col(
			$this->db->prepare( "SELECT id FROM {$this->table_name} WHERE post_type = %s", $post_type )
		);
		$existing_ids = array_map( 'intval', $existing_ids ); // Ensure they are integers.

		$processed_ids = [];
		$temp_to_real_id_map = []; // Map to track temporary UIDs to new DB IDs

		// 3. Loop through incoming data to handle inserts and updates.
		if ( ! empty( $fields ) ) {

			// Get the schema defaults ONCE before the loop.
			$schema_defaults = $schema->get_defaults();

			foreach ( $fields as $sort_order => $field_data ) {
				// Start with the full set of schema defaults.
				$data_to_save = $schema_defaults;

				// Merge the submitted data over the defaults.
				foreach ( $field_data as $key => $value ) {
					if ( array_key_exists( $key, $data_to_save ) ) {
						$data_to_save[ $key ] = $value;
					}
				}

				if ( empty( $data_to_save ) ) {
					continue;
				}

				// --- THE FIX: PART 1 ---
				// Handle parent ID mapping first. We check the original `$field_data`
				// because it reliably contains the temporary `_parent_id`.
				if (isset($field_data['_parent_id']) && is_string($field_data['_parent_id']) && strpos($field_data['_parent_id'], 'new_') === 0) {
					$temp_parent_id = $field_data['_parent_id'];
					if (isset($temp_to_real_id_map[$temp_parent_id])) {
						// Set the correct database parent key.
						$data_to_save['tab_parent'] = $temp_to_real_id_map[$temp_parent_id];
					} else {
						// This should not happen if parents are always first in the array.
						$data_to_save['tab_parent'] = 0;
					}
				}

				// Override specific values controlled by the sync logic.
				$data_to_save['post_type']  = $post_type;
				$data_to_save['sort_order'] = $sort_order + 1;
				// --- THE FIX: PART 2 ---
				// Calculate `tab_level` based on the potentially updated `tab_parent` in `$data_to_save`.
				$data_to_save['tab_level']  = ! empty( $data_to_save['tab_parent'] ) ? 1 : 0;
				$data_to_save['field_type_key']  = ! empty( $field_data['field_type_key'] ) ?  esc_attr( $field_data['field_type_key'] ) :  esc_attr( $field_data['field_type'] );

				// Never try to write the primary key.
				unset( $data_to_save['id'] );

				$field_id = isset( $field_data['id'] ) ? absint( $field_data['id'] ) : 0;

				// Create a new formats array that is correctly ordered to match $data_to_save.
				$ordered_formats = [];
				foreach ($data_to_save as $key => $value) {
					if (isset($schema_formats[$key])) {
						$ordered_formats[] = $schema_formats[$key];
					}
				}

				if ( $field_id > 0 && in_array( $field_id, $existing_ids, true ) ) {
					// This is an UPDATE.
					$this->db->update(
						$this->table_name,
						$data_to_save,
						[ 'id' => $field_id ],
						$ordered_formats,
						[ '%d' ]
					);
					$processed_ids[] = $field_id;
				} else {
					// This is an INSERT.
					$this->db->insert(
						$this->table_name,
						$data_to_save,
						$ordered_formats
					);

					$newly_inserted_id = $this->db->insert_id;
					$processed_ids[] = $newly_inserted_id;

					// If the original field from the frontend had a temporary UID, map it to the new real ID.
					if ( isset($field_data['_uid']) && is_string($field_data['_uid']) && strpos($field_data['_uid'], 'new_') === 0 ) {
						$temp_uid = $field_data['_uid'];
						$temp_to_real_id_map[$temp_uid] = $newly_inserted_id;
					}
				}
			}
		}

		// 4. Determine which fields to delete.
		$ids_to_delete = array_diff( $existing_ids, $processed_ids );

		// 5. Execute deletion if necessary.
		if ( ! empty( $ids_to_delete ) ) {
			$placeholders = implode( ', ', array_fill( 0, count( $ids_to_delete ), '%d' ) );
			$this->db->query(
				$this->db->prepare( "DELETE FROM {$this->table_name} WHERE id IN ( $placeholders )", $ids_to_delete )
			);
		}

		return true;
	}

	/**
	 * Deletes a single field by its primary ID.
	 *
	 * @param int $field_id The ID of the field to delete.
	 * @return bool True on success, false on failure.
	 */
	public function delete_field( int $field_id ): bool {
		if ( $field_id <= 0 ) {
			return false;
		}

		$result = $this->db->delete( $this->table_name, [ 'id' => $field_id ], [ '%d' ] );

		return $result !== false;
	}

	/**
	 * Installs the default set of fields for a given post type.
	 *
	 * @param string $post_type
	 */
	public function install_defaults( string $post_type ): void {
		$default_fields = [
			// Example:
			// [ 'frontend_title' => 'Business Name', 'htmlvar_name' => 'post_title', 'field_type' => 'text', 'is_active' => 1 ],
			// [ 'frontend_title' => 'Business Description', 'htmlvar_name' => 'post_content', 'field_type' => 'textarea', 'is_active' => 1 ],
		];

		if (!empty($default_fields)) {
			$this->sync_by_post_type( $post_type, $default_fields );
		}
	}
}
