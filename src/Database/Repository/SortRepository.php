<?php

namespace AyeCode\GeoDirectory\Database\Repository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages all database interactions for the CPT tabs table.
 */
final class SortRepository {

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
		$this->db = $wpdb;
		$this->table_name = geodirectory()->tables->get( 'custom_sort_fields' );
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
	 * Gets active sort options for a given post type.
	 *
	 * Returns sort fields that are active, not address type, and not child fields.
	 *
	 * @param string $post_type The post type slug.
	 * @return array Array of sort field objects.
	 */
	public function get_active_sort_options( string $post_type ): array {
		$results = $this->db->get_results(
			$this->db->prepare(
				"SELECT * FROM {$this->table_name} WHERE post_type = %s AND is_active = %d AND field_type != 'address' AND tab_parent = '0' ORDER BY sort_order ASC",
				$post_type,
				1
			),
			OBJECT
		);
		return $results ? $results : [];
	}

	/**
	 * Gets the default sort field for a given post type.
	 *
	 * @param string $post_type The post type slug.
	 * @return object|null The default sort field object or null if not found.
	 */
	public function get_default_sort_field( string $post_type ): ?object {
		$result = $this->db->get_row(
			$this->db->prepare(
				"SELECT field_type, htmlvar_name, sort FROM {$this->table_name} WHERE post_type = %s AND is_active = %d AND is_default = %d",
				$post_type,
				1,
				1
			)
		);
		return $result ? $result : null;
	}

	/**
	 * Gets the parent sort field ID by htmlvar_name, sort order, post type, and parent ID.
	 *
	 * @param string $htmlvar_name The HTML variable name.
	 * @param string $order        The sort order ('asc' or 'desc').
	 * @param string $post_type    The post type slug.
	 * @param int    $parent       The parent ID. Default 0.
	 * @return int|null The parent sort field ID or null if not found.
	 */
	public function get_parent_id_by_htmlvar( string $htmlvar_name, string $order, string $post_type, int $parent = 0 ): ?int {
		$result = $this->db->get_var(
			$this->db->prepare(
				"SELECT id FROM {$this->table_name} WHERE htmlvar_name = %s AND sort = %s AND post_type = %s AND tab_parent = %d",
				$htmlvar_name,
				$order,
				$post_type,
				$parent
			)
		);
		return $result ? (int) $result : null;
	}

	/**
	 * Gets child sort fields for a given post type and parent ID.
	 *
	 * @param string $post_type The post type slug.
	 * @param int    $parent_id The parent sort field ID.
	 * @return array Array of child sort field objects.
	 */
	public function get_children_by_parent( string $post_type, int $parent_id ): array {
		$results = $this->db->get_results(
			$this->db->prepare(
				"SELECT * FROM {$this->table_name} WHERE post_type = %s AND tab_parent = %d ORDER BY sort_order ASC",
				$post_type,
				$parent_id
			),
			OBJECT
		);
		return $results ? $results : [];
	}

	/**
	 * Synchronizes the tabs in the database with the provided data array.
	 *
	 * This method intelligently handles updates for existing tabs, inserts for new tabs,
	 * and deletes any tabs that are no longer present.
	 *
	 * @param string $post_type The post type slug.
	 * @param array $tabs The numerically indexed array of tabs to save.
	 * @return bool True on success.
	 */
	public function sync_by_post_type( string $post_type, array $tabs ): bool {
		// 1. Get the current state of tab IDs from the database.
		$existing_ids = $this->db->get_col(
			$this->db->prepare( "SELECT id FROM {$this->table_name} WHERE post_type = %s", $post_type )
		);
		$existing_ids = array_map( 'intval', $existing_ids ); // Ensure they are integers.

		$processed_ids = [];
		// --- THE FIX: PART 1 ---
		// Map to track temporary frontend UIDs to their new permanent DB IDs.
		$temp_to_real_id_map = [];

		// 2. Loop through incoming data to handle inserts and updates.
		if ( !empty( $tabs ) ) {
			foreach ( $tabs as $sort_order => $tab_data ) {

				// --- THE FIX: PART 2 ---
				// Handle parent ID mapping first.
				$tab_parent_id = $tab_data['tab_parent'] ?? 0;
				if (isset($tab_data['_parent_id']) && is_string($tab_data['_parent_id']) && strpos($tab_data['_parent_id'], 'new_') === 0) {
					$temp_parent_id = $tab_data['_parent_id'];
					if (isset($temp_to_real_id_map[$temp_parent_id])) {
						$tab_parent_id = $temp_to_real_id_map[$temp_parent_id];
					} else {
						$tab_parent_id = 0; // Fallback
					}
				}

				$data_to_save = [
					'post_type'   => $post_type,
					'data_type'     => sanitize_text_field( $tab_data['data_type'] ?? '' ),
					'field_type'     => sanitize_key( $tab_data['field_type'] ?? '' ),
					'frontend_title'     =>  sanitize_text_field( $tab_data['frontend_title'] ?? '' ),
					'htmlvar_name'     => sanitize_key( $tab_data['htmlvar_name'] ?? '' ),
					'sort_order'  => $sort_order + 1,
					'tab_parent'  => absint( $tab_parent_id ), // Use the corrected parent ID
					'tab_level'   => !empty($tab_parent_id) ? 1 : 0, // Calculate level based on the correct parent ID
					'is_active'  => absint( $tab_data['is_active'] ?? 0 ),
					'is_default'  => $sort_order === 0 ? 1 : 0,
					'sort'     => sanitize_key( $tab_data['sort'] ?? 'asc' ),
				];

				$tab_id = isset( $tab_data['id'] ) ? absint( $tab_data['id'] ) : 0;

				if ( $tab_id > 0 && in_array( $tab_id, $existing_ids, true ) ) {
					// This is an UPDATE.
					$this->db->update(
						$this->table_name,
						$data_to_save,
						[ 'id' => $tab_id ],
						[ '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%s' ]
					);
					$processed_ids[] = $tab_id;
				} else {
					// This is an INSERT.
					$this->db->insert(
						$this->table_name,
						$data_to_save,
						[ '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%s' ]
					);

					$newly_inserted_id = $this->db->insert_id;
					$processed_ids[] = $newly_inserted_id;

					// --- THE FIX: PART 3 ---
					// If the original tab had a temporary UID, map it to the new real ID.
					if ( isset($tab_data['_uid']) && is_string($tab_data['_uid']) && strpos($tab_data['_uid'], 'new_') === 0 ) {
						$temp_uid = $tab_data['_uid'];
						$temp_to_real_id_map[$temp_uid] = $newly_inserted_id;
					}
				}
			}
		}

		// 3. Determine which tabs to delete.
		$ids_to_delete = array_diff( $existing_ids, $processed_ids );

		// 4. Execute deletion if necessary.
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
	 * Installs the default set of tabs for a given post type. @todo check this
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

		// We now use the sync method for installation as well.
		$this->sync_by_post_type( $post_type, $default_tabs );
	}
}
