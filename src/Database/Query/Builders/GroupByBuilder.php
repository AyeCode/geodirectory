<?php
/**
 * GROUP BY Clause Builder
 *
 * Builds GROUP BY clauses for GeoDirectory post queries.
 *
 * @package GeoDirectory\Database\Query\Builders
 * @since   3.0.0
 * @author  AyeCode Ltd
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Database\Query\Builders;

use AyeCode\GeoDirectory\Database\Query\Interfaces\SqlBuilderInterface;

/**
 * GROUP BY clause builder.
 *
 * @since 3.0.0
 */
final class GroupByBuilder implements SqlBuilderInterface {
	/**
	 * Build GROUP BY clause.
	 *
	 * @param object $query     WP_Query object.
	 * @param string $post_type Post type.
	 * @return string GROUP BY SQL.
	 */
	public function build( $query, string $post_type ): string {
		$groupby = '';

		/**
		 * Filter the GROUP BY clause.
		 *
		 * @since 2.0.0
		 *
		 * @param string $groupby  GROUP BY SQL.
		 * @param object $query    WP_Query object.
		 */
		return apply_filters( 'geodir_posts_groupby', $groupby, $query );
	}
}
