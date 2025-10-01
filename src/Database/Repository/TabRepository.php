<?php
/**
 * Manages all database interactions for the CPT tabs layout table.
 *
 * @package GeoDirectory\Database\Repository
 */

namespace AyeCode\GeoDirectory\Database\Repository;

// Make sure to import the new Schema class.
use AyeCode\GeoDirectory\Database\Schema\TabSchema;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages all database interactions for the CPT tabs table.
 */
final class TabRepository {

	/**
	 * @var \wpdb The WordPress database object.
	 */
	private $db;

	/**
	 * @var string The name of the tabs table.
	 */
	private $table_name;

	public function __construct() {
		global $wpdb;
		$this->db         = $wpdb;
		$this->table_name = geodirectory()->tables->get( 'tabs_layout' );
	}

	/**
	 * Gets all tabs for a given post type, ordered by sort_order.
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
	 * Synchronizes the tabs in the database with the provided data array.
	 *
	 * This method intelligently handles updates for existing tabs, inserts for new tabs,
	 * and deletes any tabs that are no longer present. It is now driven by the
	 * TabSchema class to ensure data integrity.
	 *
	 * @param string $post_type The post type slug.
	 * @param array $tabs The numerically indexed array of tabs to save.
	 * @return bool True on success.
	 */
	public function sync_by_post_type( string $post_type, array $tabs ): bool {

//		print_r($tabs);exit;
		// 1. Get schema information. This is our single source of truth.
		$schema         = new TabSchema();
		$schema_columns = $schema->get_column_names();
		$schema_formats = $schema->get_formats();

		// 2. Get the current state of tab IDs from the database.
		$existing_ids = $this->db->get_col(
			$this->db->prepare( "SELECT id FROM {$this->table_name} WHERE post_type = %s", $post_type )
		);
		$existing_ids = array_map( 'intval', $existing_ids ); // Ensure they are integers.

		$processed_ids = [];
		// Map to track temporary frontend UIDs to their new permanent DB IDs.
		$temp_to_real_id_map = [];

		// 3. Loop through incoming data to handle inserts and updates.
		if ( ! empty( $tabs ) ) {
			foreach ( $tabs as $sort_order => $tab_data ) {
				$data_to_save = [];

				// We map the incoming data to the columns defined in our schema.
				foreach ( $schema_columns as $column ) {
					if ( isset( $tab_data[ $column ] ) ) {
						$data_to_save[ $column ] = $tab_data[ $column ];
					}
				}

				if ( empty( $data_to_save ) ) {
					continue;
				}

				// --- THE FIX: PART 1 ---
				// Handle parent ID mapping first, using the original incoming data.
				if (isset($tab_data['_parent_id']) && is_string($tab_data['_parent_id']) && strpos($tab_data['_parent_id'], 'new_') === 0) {
					$temp_parent_id = $tab_data['_parent_id'];
					if (isset($temp_to_real_id_map[$temp_parent_id])) {
						// Set the correct database parent key.
						$data_to_save['tab_parent'] = $temp_to_real_id_map[$temp_parent_id];
					} else {
						$data_to_save['tab_parent'] = 0;
					}
				}

				// Override specific values controlled by the sync logic.
				$data_to_save['post_type']  = $post_type;
				$data_to_save['sort_order'] = $sort_order + 1;
				// Calculate tab_level based on the (now correct) parent ID.
				$data_to_save['tab_level']  = !empty($data_to_save['tab_parent']) ? 1 : 0;

				// Never try to write the primary key.
				unset( $data_to_save['id'] );

				$tab_id = isset( $tab_data['id'] ) ? absint( $tab_data['id'] ) : 0;

				// Create a new formats array that is correctly ordered to match $data_to_save.
				$ordered_formats = [];
				foreach ($data_to_save as $key => $value) {
					if (isset($schema_formats[$key])) {
						$ordered_formats[] = $schema_formats[$key];
					}
				}

				if ( $tab_id > 0 && in_array( $tab_id, $existing_ids, true ) ) {
					// This is an UPDATE.
					$this->db->update(
						$this->table_name,
						$data_to_save,
						[ 'id' => $tab_id ],
						$ordered_formats,
						[ '%d' ]
					);
					$processed_ids[] = $tab_id;
				} else {
					// This is an INSERT.
					$this->db->insert(
						$this->table_name,
						$data_to_save,
						$ordered_formats
					);

					$newly_inserted_id = $this->db->insert_id;
					$processed_ids[] = $newly_inserted_id;

					// --- THE FIX: PART 2 ---
					// If the original tab from the frontend had a temporary UID, map it to the new real ID.
					if ( isset($tab_data['_uid']) && is_string($tab_data['_uid']) && strpos($tab_data['_uid'], 'new_') === 0 ) {
						$temp_uid = $tab_data['_uid'];
						$temp_to_real_id_map[$temp_uid] = $newly_inserted_id;
					}
				}
			}
		}

		// 4. Determine which tabs to delete.
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
	 * Deletes a single tab by its primary ID.
	 *
	 * @param int $tab_id The ID of the tab to delete.
	 * @return bool True on success, false on failure.
	 */
	public function delete_tab( int $tab_id ): bool {
		if ( $tab_id <= 0 ) {
			return false;
		}

		$result = $this->db->delete( $this->table_name, [ 'id' => $tab_id ], [ '%d' ] );

		return $result !== false;
	}

	/**
	 * Installs the default set of tabs for a given post type.
	 *
	 * @param string $post_type
	 */
	public function install_default_tabs( string $post_type ): void {
		$default_tabs = [
			[ 'tab_name' => 'Profile', 'tab_icon' => 'fas fa-home', 'tab_key' => 'post_content', 'tab_type' => 'meta' ],
			[ 'tab_name' => 'Photos', 'tab_icon' => 'fas fa-image', 'tab_key' => 'post_images', 'tab_content' => '[gd_post_images]' ],
			[ 'tab_name' => 'Map', 'tab_icon' => 'fas fa-globe-americas', 'tab_key' => 'post_map', 'tab_content' => '[gd_map]' ],
			[ 'tab_name' => 'Reviews', 'tab_icon' => 'fas fa-comments', 'tab_key' => 'reviews' ],
		];

		// This now uses our refactored, schema-aware sync method.
		$this->sync_by_post_type( $post_type, $default_tabs );
	}
}
