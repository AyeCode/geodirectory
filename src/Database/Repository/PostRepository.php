<?php
/**
 * Post Repository
 *
 * Handles all database interactions for GeoDirectory posts and post meta.
 * All SQL queries are isolated here with proper prepared statements.
 *
 * @package GeoDirectory\Database\Repository
 * @since   3.0.0
 * @author  AyeCode Ltd
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Database\Repository;

/**
 * Repository for GeoDirectory post data operations.
 *
 * @since 3.0.0
 */
final class PostRepository {
	/**
	 * WordPress database instance.
	 *
	 * @var \wpdb
	 */
	private \wpdb $db;

	/**
	 * GeoDirectory plugin table prefix.
	 *
	 * @var string
	 */
	private string $plugin_prefix;

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $wpdb, $plugin_prefix;
		$this->db            = $wpdb;
		$this->plugin_prefix =  $this->db->prefix.'geodir_';
//		echo '###'.$this->plugin_prefix;exit;
	}

	/**
	 * Get post custom fields from detail table.
	 *
	 * @param int $post_id The post ID.
	 * @return object|null Returns full post details as an object or null if not found.
	 */
	public function get_post_info( int $post_id ): ?object {
		$post_type = get_post_type( $post_id );

		if ( $post_type === 'revision' ) {
			$post_type = get_post_type( wp_get_post_parent_id( $post_id ) );
		}

		if ( ! $post_type || ! function_exists( 'geodir_is_gd_post_type' ) || ! geodir_is_gd_post_type( $post_type ) ) {
			return null;
		}

		$table = function_exists( 'geodir_db_cpt_table' ) ? geodir_db_cpt_table( $post_type ) : $this->plugin_prefix . $post_type . '_detail';

		/**
		 * Filter to modify the post info query.
		 *
		 * @since 1.0.0
		 *
		 * @param string $query The SQL query.
		 */
		$query = apply_filters(
			'geodir_post_info_query',
			$this->db->prepare(
				"SELECT p.*, pd.* FROM {$this->db->posts} p, {$table} pd WHERE p.ID = pd.post_id AND pd.post_id = %d",
				$post_id
			)
		);

		$post_detail = $this->db->get_row( $query );

		return $post_detail ?: null;
	}

	/**
	 * Get post custom meta value.
	 *
	 * @param int    $post_id   The post ID.
	 * @param string $meta_key  The meta key to retrieve.
	 * @param string $post_type The post type.
	 * @return mixed|null The meta value or null if not found.
	 */
	public function get_meta( int $post_id, string $meta_key, string $post_type ) {
		$table = $this->plugin_prefix . $post_type . '_detail';

		if ( ! $table || ! $meta_key ) {
			return null;
		}

		$meta_value = $this->db->get_var(
			$this->db->prepare(
				"SELECT `{$meta_key}` FROM {$table} WHERE post_id = %d",
				$post_id
			)
		);

		// Handle empty values vs non-existent columns.
		if ( ( $meta_value || $meta_value === '0' ) && $meta_value !== '' ) {
			return maybe_serialize( $meta_value );
		}

		return null;
	}

	/**
	 * Save or update post custom meta.
	 *
	 * @param int    $post_id   The post ID.
	 * @param string $meta_key  Detail table column name.
	 * @param mixed  $meta_value Detail table column value.
	 * @param string $post_type The post type.
	 * @return bool True on success, false on failure.
	 */
	public function save_meta( int $post_id, string $meta_key, $meta_value, string $post_type ): bool {
		if ( ! $meta_key || ! $post_id ) {
			return false;
		}

		$table = $this->plugin_prefix . $post_type . '_detail';

		if ( ! $this->column_exists( $table, $meta_key ) ) {
			return false;
		}

		if ( is_array( $meta_value ) ) {
			$meta_value = implode( ',', $meta_value );
		}

		// Check if post exists in detail table.
		$exists = $this->db->get_var(
			$this->db->prepare(
				"SELECT post_id FROM {$table} WHERE post_id = %d",
				$post_id
			)
		);

		if ( $exists ) {
			// Update existing record.
			$result = $this->db->update(
				$table,
				[ $meta_key => $meta_value ],
				[ 'post_id' => $post_id ],
				[ '%s' ],
				[ '%d' ]
			);
		} else {
			// Insert new record.
			$result = $this->db->insert(
				$table,
				[
					'post_id' => $post_id,
					$meta_key => $meta_value,
				],
				[ '%d', '%s' ]
			);
		}

		return $result !== false;
	}

	/**
	 * Delete post custom meta.
	 *
	 * @param int          $post_id   The post ID.
	 * @param string|array $meta_keys Detail table column name(s).
	 * @param string       $post_type The post type.
	 * @return bool True on success, false on failure.
	 */
	public function delete_meta( int $post_id, $meta_keys, string $post_type ): bool {
		if ( ! $post_id || empty( $meta_keys ) ) {
			return false;
		}

		$table = $this->plugin_prefix . $post_type . '_detail';

		if ( is_array( $meta_keys ) ) {
			$post_meta_set_query = '';

			foreach ( $meta_keys as $meta_key ) {
				if ( $meta_key !== '' ) {
					$post_meta_set_query .= $meta_key . " = '', ";
				}
			}

			$post_meta_set_query = trim( $post_meta_set_query, ', ' );

			if ( empty( $post_meta_set_query ) || trim( $post_meta_set_query ) === '' ) {
				return false;
			}

			// Verify at least one column exists.
			$column_exists = false;
			foreach ( $meta_keys as $meta_key ) {
				if ( $this->column_exists( $table, $meta_key ) ) {
					$column_exists = true;
					break;
				}
			}

			if ( ! $column_exists ) {
				return false;
			}

			$result = $this->db->query(
				$this->db->prepare(
					"UPDATE {$table} SET {$post_meta_set_query} WHERE post_id = %d",
					$post_id
				)
			);
		} else {
			if ( ! $this->column_exists( $table, $meta_keys ) ) {
				return false;
			}

			$result = $this->db->update(
				$table,
				[ $meta_keys => '' ],
				[ 'post_id' => $post_id ],
				[ '%s' ],
				[ '%d' ]
			);
		}

		return $result !== false;
	}

	/**
	 * Check if a column exists in a table.
	 *
	 * @param string $table  The table name.
	 * @param string $column The column name.
	 * @return bool True if column exists, false otherwise.
	 */
	public function column_exists( string $table, string $column ): bool {
		if ( ! $table || ! $column || ! function_exists( 'geodir_column_exist' ) ) {
			return false;
		}

		return geodir_column_exist( $table, $column );
	}
}
