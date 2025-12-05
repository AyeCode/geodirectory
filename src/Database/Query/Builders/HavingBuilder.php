<?php
/**
 * HAVING Clause Builder
 *
 * Builds HAVING clauses for GeoDirectory post queries, primarily
 * for distance-based filtering.
 *
 * @package GeoDirectory\Database\Query\Builders
 * @since   3.0.0
 * @author  AyeCode Ltd
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Database\Query\Builders;

use AyeCode\GeoDirectory\Core\Services\QueryVars;

/**
 * HAVING clause builder.
 *
 * @since 3.0.0
 */
final class HavingBuilder {
	/**
	 * WordPress database instance.
	 *
	 * @var \wpdb
	 */
	private \wpdb $db;

	/**
	 * Query variables service.
	 *
	 * @var QueryVars
	 */
	private QueryVars $query_vars;

	/**
	 * Constructor.
	 *
	 * @param QueryVars $query_vars Query variables service.
	 */
	public function __construct( QueryVars $query_vars ) {
		global $wpdb;
		$this->db = $wpdb;
		$this->query_vars = $query_vars;
	}

	/**
	 * Build HAVING clause within posts_clauses.
	 *
	 * This modifies the clauses array to add HAVING for distance filtering.
	 *
	 * @param array  $clauses   Posts clauses array.
	 * @param object $query     WP_Query object.
	 * @param string $post_type Post type.
	 * @return array Modified clauses array.
	 */
	public function build_clauses( array $clauses, $query, string $post_type ): array {
		if ( defined( 'GEODIR_MAP_SEARCH' ) ) {
			return $clauses;
		}

		$support_location = $post_type && geodirectory()->post_types->supports( $post_type, 'location' ) ;

		if ( ! $support_location ) {
			return $clauses;
		}

		$latlon = $this->query_vars->get_latlon();
		if ( ! $latlon ) {
			return $clauses;
		}

		/*
		 * The HAVING clause is often used with the GROUP BY clause to filter groups based on a specified condition.
		 * If the GROUP BY clause is omitted, the HAVING clause behaves like the WHERE clause.
		 */
		if ( strpos( $clauses['where'], ' HAVING ' ) === false && strpos( $clauses['groupby'], ' HAVING ' ) === false ) {
			$dist = $this->query_vars->get( 'dist' ) ? geodir_sanitize_float( $this->query_vars->get( 'dist' ) ) : geodir_get_option( 'search_radius', 5 );

			if ( geodirectory()->post_types->supports( $post_type, 'service_distance' ) ) {
				$_table = geodir_db_cpt_table( $post_type );
				$having = $this->db->prepare( " HAVING ( ( `{$_table}`.`service_distance` > 0 AND distance <= `{$_table}`.`service_distance` ) OR ( ( `{$_table}`.`service_distance` <= 0 OR `{$_table}`.`service_distance` IS NULL ) AND distance <= %f ) )", $dist );
			} else {
				$having = $this->db->prepare( " HAVING distance <= %f ", $dist );
			}

			if ( trim( $clauses['groupby'] ) != '' ) {
				$clauses['groupby'] .= $having;
			} else {
				$clauses['where'] .= $having;
			}
		}

		return $clauses;
	}
}
