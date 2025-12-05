<?php
/**
 * JOIN Clause Builder
 *
 * Builds JOIN clauses for GeoDirectory post queries.
 *
 * @package GeoDirectory\Database\Query\Builders
 * @since   3.0.0
 * @author  AyeCode Ltd
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Database\Query\Builders;

use AyeCode\GeoDirectory\Database\Query\Interfaces\SqlBuilderInterface;

/**
 * JOIN clause builder.
 *
 * @since 3.0.0
 */
final class JoinBuilder implements SqlBuilderInterface {
	/**
	 * WordPress database instance.
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
	}

	/**
	 * Build JOIN clause.
	 *
	 * @param object $query     WP_Query object.
	 * @param string $post_type Post type.
	 * @return string JOIN SQL.
	 */
	public function build( $query, string $post_type ): string {
		$table = geodir_db_cpt_table( $post_type );

		$join = " INNER JOIN {$table} ON ({$table}.post_id = {$this->db->posts}.ID) ";

		/**
		 * Filter the JOIN clause.
		 *
		 * @since 2.0.0
		 *
		 * @param string $join  JOIN SQL.
		 * @param object $query WP_Query object.
		 */
		return apply_filters( 'geodir_posts_join', $join, $query );
	}
}
