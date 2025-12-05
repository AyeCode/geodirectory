<?php
/**
 * SQL Builder Interface
 *
 * Contract for SQL clause builders (WHERE, JOIN, ORDER BY, etc.).
 *
 * @package GeoDirectory\Database\Query\Interfaces
 * @since   3.0.0
 * @author  AyeCode Ltd
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Database\Query\Interfaces;

/**
 * Interface for SQL builders.
 *
 * @since 3.0.0
 */
interface SqlBuilderInterface {
	/**
	 * Build SQL clause for the given query.
	 *
	 * @param object $query     WP_Query object.
	 * @param string $post_type Post type.
	 * @return string SQL clause.
	 */
	public function build( $query, string $post_type ): string;
}
