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

	// ========================================================================
	// Schema Management (DDL Operations)
	// ========================================================================

	/**
	 * Get the CPT-specific table name.
	 *
	 * @param string $post_type The post type slug (e.g., 'gd_place', 'gd_event').
	 * @return string|false The full table name or false if invalid.
	 */
	public function get_table_name( string $post_type ) {
		if ( empty( $post_type ) ) {
			return false;
		}

		if ( function_exists( 'geodir_db_cpt_table' ) ) {
			return geodir_db_cpt_table( $post_type );
		}

		return $this->plugin_prefix . $post_type . '_detail';
	}

	/**
	 * Check if a CPT table exists.
	 *
	 * @param string $post_type The post type slug.
	 * @return bool True if table exists, false otherwise.
	 */
	public function table_exists( string $post_type ): bool {
		$table_name = $this->get_table_name( $post_type );

		if ( ! $table_name ) {
			return false;
		}

		$result = $this->db->get_var( $this->db->prepare( 'SHOW TABLES LIKE %s', $table_name ) );

		return $result === $table_name;
	}

	/**
	 * Add a column to a CPT table based on custom field data.
	 *
	 * @param string $post_type  The post type slug.
	 * @param array  $field_data The custom field data array.
	 * @return bool True on success, false on failure.
	 */
	public function add_column( string $post_type, array $field_data ): bool {
		$table_name = $this->get_table_name( $post_type );

		if ( ! $table_name || empty( $field_data['htmlvar_name'] ) ) {
			return false;
		}

		$column_name = $field_data['htmlvar_name'];

		// Don't add if column already exists.
		if ( $this->column_exists( $table_name, $column_name ) ) {
			return true;
		}

		$column_definition = $this->get_column_definition( $field_data );

		if ( ! $column_definition ) {
			return false;
		}

		/**
		 * Filter the column definition before adding.
		 *
		 * @param string $column_definition The MySQL column definition.
		 * @param array  $field_data        The custom field data.
		 * @param string $post_type         The post type slug.
		 */
		$column_definition = apply_filters( 'geodir_post_repo_add_column_definition', $column_definition, $field_data, $post_type );

		/**
		 * Fires before adding a column to a CPT table.
		 *
		 * @param string $table_name   The table name.
		 * @param string $column_name  The column name.
		 * @param array  $field_data   The custom field data.
		 * @param string $post_type    The post type slug.
		 */
		do_action( 'geodir_before_add_custom_field_column', $table_name, $column_name, $field_data, $post_type );

		$result = $this->db->query(
			"ALTER TABLE `{$table_name}` ADD `{$column_name}` {$column_definition}"
		);

		/**
		 * Fires after adding a column to a CPT table.
		 *
		 * @param bool   $success      Whether the operation was successful.
		 * @param string $table_name   The table name.
		 * @param string $column_name  The column name.
		 * @param array  $field_data   The custom field data.
		 * @param string $post_type    The post type slug.
		 */
		do_action( 'geodir_after_add_custom_field_column', $result !== false, $table_name, $column_name, $field_data, $post_type );

		return $result !== false;
	}

	/**
	 * Remove a column from a CPT table.
	 *
	 * @param string $post_type   The post type slug.
	 * @param string $column_name The column name to remove.
	 * @return bool True on success, false on failure.
	 */
	public function remove_column( string $post_type, string $column_name ): bool {
		$table_name = $this->get_table_name( $post_type );

		if ( ! $table_name || empty( $column_name ) ) {
			return false;
		}

		// Don't attempt to remove if column doesn't exist.
		if ( ! $this->column_exists( $table_name, $column_name ) ) {
			return true;
		}

		/**
		 * Fires before removing a column from a CPT table.
		 *
		 * @param string $table_name  The table name.
		 * @param string $column_name The column name.
		 * @param string $post_type   The post type slug.
		 */
		do_action( 'geodir_before_remove_custom_field_column', $table_name, $column_name, $post_type );

		$result = $this->db->query(
			"ALTER TABLE `{$table_name}` DROP COLUMN `{$column_name}`"
		);

		/**
		 * Fires after removing a column from a CPT table.
		 *
		 * @param bool   $success     Whether the operation was successful.
		 * @param string $table_name  The table name.
		 * @param string $column_name The column name.
		 * @param string $post_type   The post type slug.
		 */
		do_action( 'geodir_after_remove_custom_field_column', $result !== false, $table_name, $column_name, $post_type );

		return $result !== false;
	}

	/**
	 * Update/modify an existing column in a CPT table.
	 *
	 * @param string $post_type  The post type slug.
	 * @param array  $field_data The custom field data array.
	 * @return bool True on success, false on failure.
	 */
	public function update_column( string $post_type, array $field_data ): bool {
		$table_name = $this->get_table_name( $post_type );

		if ( ! $table_name || empty( $field_data['htmlvar_name'] ) ) {
			return false;
		}

		$column_name = $field_data['htmlvar_name'];

		// Only update if column exists.
		if ( ! $this->column_exists( $table_name, $column_name ) ) {
			return false;
		}

		$column_definition = $this->get_column_definition( $field_data );

		if ( ! $column_definition ) {
			return false;
		}

		/**
		 * Filter the column definition before updating.
		 *
		 * @param string $column_definition The MySQL column definition.
		 * @param array  $field_data        The custom field data.
		 * @param string $post_type         The post type slug.
		 */
		$column_definition = apply_filters( 'geodir_post_repo_update_column_definition', $column_definition, $field_data, $post_type );

		/**
		 * Fires before updating a column in a CPT table.
		 *
		 * @param string $table_name   The table name.
		 * @param string $column_name  The column name.
		 * @param array  $field_data   The custom field data.
		 * @param string $post_type    The post type slug.
		 */
		do_action( 'geodir_before_update_custom_field_column', $table_name, $column_name, $field_data, $post_type );

		$result = $this->db->query(
			"ALTER TABLE `{$table_name}` CHANGE `{$column_name}` `{$column_name}` {$column_definition}"
		);

		/**
		 * Fires after updating a column in a CPT table.
		 *
		 * @param bool   $success      Whether the operation was successful.
		 * @param string $table_name   The table name.
		 * @param string $column_name  The column name.
		 * @param array  $field_data   The custom field data.
		 * @param string $post_type    The post type slug.
		 */
		do_action( 'geodir_after_update_custom_field_column', $result !== false, $table_name, $column_name, $field_data, $post_type );

		return $result !== false;
	}

	/**
	 * Get the MySQL column definition for a custom field.
	 *
	 * Maps field types and data types to appropriate MySQL column definitions.
	 *
	 * @param array $field_data The custom field data array.
	 * @return string The MySQL column definition (e.g., "VARCHAR(254) NULL DEFAULT ''").
	 */
	protected function get_column_definition( array $field_data ): string {
		$field_type = $field_data['field_type'] ?? 'text';
		$data_type  = $field_data['data_type'] ?? '';
		$default    = $field_data['default_value'] ?? '';

		$definition = '';

		// Handle specific field types.
		switch ( $field_type ) {
			case 'text':
				if ( $data_type === 'DECIMAL' || $data_type === 'FLOAT' ) {
					$decimal_places = isset( $field_data['decimal_point'] ) ? absint( $field_data['decimal_point'] ) : 2;
					$definition = "DECIMAL(" . ( 14 + $decimal_places ) . ", {$decimal_places})";
					$definition .= $default !== '' ? $this->db->prepare( ' DEFAULT %f', (float) $default ) : ' DEFAULT NULL';
				} elseif ( $data_type === 'INT' ) {
					$definition = 'BIGINT(20)';
					$definition .= $default !== '' ? $this->db->prepare( ' DEFAULT %d', (int) $default ) : ' DEFAULT NULL';
				} else {
					$definition = 'VARCHAR(254) NULL';
					$definition .= $default !== '' ? $this->db->prepare( ' DEFAULT %s', $default ) : ' DEFAULT NULL';
				}
				break;

			case 'datepicker':
				$definition = 'DATE DEFAULT NULL';
				break;

			case 'textarea':
			case 'html':
			case 'url':
				$definition = 'TEXT NULL';
				break;

			case 'checkbox':
				$definition = 'TINYINT(1) DEFAULT 0';
				break;

			case 'select':
			case 'multiselect':
			case 'radio':
				$definition = 'VARCHAR(254) NULL DEFAULT NULL';
				break;

			case 'email':
			case 'phone':
				$definition = 'VARCHAR(254) NULL DEFAULT NULL';
				break;

			case 'file':
			case 'taxonomy':
				$definition = 'TEXT NULL';
				break;

			case 'fieldset':
			case 'categories':
				// These don't create columns.
				return '';

			default:
				$definition = 'VARCHAR(254) NULL DEFAULT NULL';
				break;
		}

		/**
		 * Filter the column definition for a custom field.
		 *
		 * @param string $definition The MySQL column definition.
		 * @param array  $field_data The custom field data.
		 */
		return apply_filters( 'geodir_post_repo_column_definition', $definition, $field_data );
	}

	// ========================================================================
	// Post Data Save Operations
	// ========================================================================

	/**
	 * Save or update post data in the CPT detail table.
	 *
	 * This method handles both INSERT (new post) and UPDATE (existing post) operations.
	 *
	 * @param int    $post_id   The post ID.
	 * @param array  $data      The data to save (column => value pairs).
	 * @param string $post_type The post type slug.
	 * @param bool   $update    Whether this is an update (true) or insert (false).
	 * @return bool True on success, false on failure.
	 */
	public function save_post_data( int $post_id, array $data, string $post_type, bool $update ): bool {
		$table_name = $this->get_table_name( $post_type );

		if ( ! $table_name ) {
			return false;
		}


		// Prepare format array (all as strings by default).
		$format = array_fill( 0, count( $data ), '%s' );

		if ( $update ) {
			//@todo clanup debuggin
//			global $wpdb;
//			$wpdb->show_errors();
//			$wpdb->print_error();

			// Update existing record.
			$result = $this->db->update(
				$table_name,
				$data,
				array( 'post_id' => $post_id ),
				$format,
				array( '%d' )
			);

//			print_r( $wpdb->last_error );
//			print_r( $wpdb );
//			print_r( $data );
//			print_r($format );
//			var_dump($result);
//			print_r( $result );echo $result.'###1'.$table_name;exit;
			return $result !== false;
		} else {
			// Insert new record.
			$result = $this->db->insert(
				$table_name,
				$data,
				$format
			);
//			print_r( $result );echo '###2';exit;
			return $result !== false;
		}



//		print_r($data);exit;
	}
}
