<?php
/**
 * GeoDirectory Database Table Manager
 *
 * Provides a central, safe way to retrieve the names of custom database tables.
 *
 * @package GeoDirectory\Core
 * @since 3.0.0
 */

declare( strict_types=1 );

namespace AyeCode\GeoDirectory\Core;

final class Tables {
	/**
	 * An array holding all registered custom table names.
	 *
	 * @var array
	 */
	private array $tables = [];

	/**
	 * WordPress database object.
	 *
	 * @var \wpdb
	 */
	private \wpdb $db;

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->db = $wpdb;

		// We define all our "static" custom table names here.
		$this->tables = [
			'reviews'            => $this->db->prefix . 'geodir_post_review',
			'custom_fields'      => $this->db->prefix . 'geodir_custom_fields',
			'custom_sort_fields' => $this->db->prefix . 'geodir_custom_sort_fields',
			'attachments'        => $this->db->prefix . 'geodir_attachments',
			'api_keys'           => $this->db->prefix . 'geodir_api_keys',
			'tabs_layout'        => $this->db->prefix . 'geodir_tabs_layout',
			'post_reports'       => $this->db->prefix . 'geodir_post_reports',
			'api_keys'           => $this->db->prefix . 'geodir_api_keys',
		];

		/**
		 * Allows addons to register their own custom tables with the manager.
		 *
		 * @param array $tables The array of registered tables.
		 */
		$this->tables = apply_filters( 'geodirectory_register_tables', $this->tables );
	}

	/**
	 * Gets the full, prefixed name of a registered "static" table.
	 *
	 * @param string $key The short name of the table (e.g., 'reviews').
	 *
	 * @return string|null The full table name, or null if not found.
	 */
	public function get( string $key ): ?string {
		return $this->tables[ $key ] ?? null;
	}

	/**
	 * Generates the table name for a CPT's details table.
	 *
	 * @param string $post_type The post type slug (e.g., 'gd_place').
	 *
	 * @return string The full, prefixed details table name.
	 */
	public function get_cpt_details_table( string $post_type ): string {

		// Construct the table name.
		$table_name = sanitize_key( $this->db->prefix . 'geodir_' . esc_attr( $post_type ) . '_detail' );

		/**
		 * Allows filtering of a CPT details table name.
		 *
		 * @param string $table_name The generated table name.
		 * @param string $post_type The original post type slug.
		 */
		return apply_filters( 'geodirectory_cpt_details_table', $table_name, $post_type );
	}
}
