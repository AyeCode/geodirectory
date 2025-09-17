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
//		echo '###'.$post_type;print_r( $tabs );exit;
		// 1. Get the current state of tab IDs from the database.
		$existing_ids = $this->db->get_col(
			$this->db->prepare( "SELECT id FROM {$this->table_name} WHERE post_type = %s", $post_type )
		);
		$existing_ids = array_map( 'intval', $existing_ids ); // Ensure they are integers.

		$processed_ids = [];

		// 2. Loop through incoming data to handle inserts and updates.
		if ( !empty( $tabs ) ) {
			foreach ( $tabs as $sort_order => $tab_data ) {
				$data_to_save = [
					'post_type'   => $post_type,
					'data_type'     => sanitize_text_field( $tab_data['data_type'] ?? '' ),
					'field_type'     => sanitize_key( $tab_data['field_type'] ?? '' ),
					'frontend_title'     =>  sanitize_text_field( $tab_data['frontend_title'] ?? '' ),
					'htmlvar_name'     => sanitize_key( $tab_data['htmlvar_name'] ?? '' ),
					'sort_order'  => $sort_order + 1,
					'tab_parent'  => absint( $tab_data['tab_parent'] ?? 0 ),
					'tab_level'   => !empty($tab_data['tab_parent']) ? 1 : 0,
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
						[ '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%s' ],
					);
					$processed_ids[] = $tab_id;
				} else {
//					echo $this->table_name.'###'.$tab_id;
//					print_r( $data_to_save );
					// This is an INSERT.
					$r = $this->db->insert(
						$this->table_name,
						$data_to_save,
						[ '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%s' ],
					);
//					print_r( $r );exit;
					// We don't add the new ID to processed_ids because it wasn't in the original $existing_ids list.
				}
			}
		}

//		print_r( $processed_ids );exit;
		// 3. Determine which tabs to delete.
		$ids_to_delete = array_diff( $existing_ids, $processed_ids );

		// 4. Execute deletion if necessary.
		if ( ! empty( $ids_to_delete ) ) {
			// Create a string of placeholders for the IN clause.
			$placeholders = implode( ', ', array_fill( 0, count( $ids_to_delete ), '%d' ) );

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
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
