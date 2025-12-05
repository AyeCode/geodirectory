<?php
/**
 * Query Builder Orchestrator
 *
 * Main orchestrator that coordinates all SQL clause builders to construct
 * complete GeoDirectory post queries.
 *
 * @package GeoDirectory\Database\Query
 * @since   3.0.0
 * @author  AyeCode Ltd
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Database\Query;

use AyeCode\GeoDirectory\Database\Query\Builders\FieldsBuilder;
use AyeCode\GeoDirectory\Database\Query\Builders\JoinBuilder;
use AyeCode\GeoDirectory\Database\Query\Builders\WhereBuilder;
use AyeCode\GeoDirectory\Database\Query\Builders\GroupByBuilder;
use AyeCode\GeoDirectory\Database\Query\Builders\OrderByBuilder;
use AyeCode\GeoDirectory\Database\Query\Builders\HavingBuilder;

/**
 * Main query builder orchestrator.
 *
 * @since 3.0.0
 */
final class QueryBuilder {
	/**
	 * Fields builder.
	 *
	 * @var FieldsBuilder
	 */
	private FieldsBuilder $fields_builder;

	/**
	 * Join builder.
	 *
	 * @var JoinBuilder
	 */
	private JoinBuilder $join_builder;

	/**
	 * Where builder.
	 *
	 * @var WhereBuilder
	 */
	private WhereBuilder $where_builder;

	/**
	 * Group by builder.
	 *
	 * @var GroupByBuilder
	 */
	private GroupByBuilder $groupby_builder;

	/**
	 * Order by builder.
	 *
	 * @var OrderByBuilder
	 */
	private OrderByBuilder $orderby_builder;

	/**
	 * Having builder.
	 *
	 * @var HavingBuilder
	 */
	private HavingBuilder $having_builder;

	/**
	 * Constructor.
	 *
	 * @param FieldsBuilder  $fields_builder  Fields builder.
	 * @param JoinBuilder    $join_builder    Join builder.
	 * @param WhereBuilder   $where_builder   Where builder.
	 * @param GroupByBuilder $groupby_builder Group by builder.
	 * @param OrderByBuilder $orderby_builder Order by builder.
	 * @param HavingBuilder  $having_builder  Having builder.
	 */
	public function __construct(
		FieldsBuilder $fields_builder,
		JoinBuilder $join_builder,
		WhereBuilder $where_builder,
		GroupByBuilder $groupby_builder,
		OrderByBuilder $orderby_builder,
		HavingBuilder $having_builder
	) {
		$this->fields_builder = $fields_builder;
		$this->join_builder = $join_builder;
		$this->where_builder = $where_builder;
		$this->groupby_builder = $groupby_builder;
		$this->orderby_builder = $orderby_builder;
		$this->having_builder = $having_builder;
	}

	/**
	 * Build SELECT fields clause.
	 *
	 * @param string $fields    Current fields.
	 * @param object $query     WP_Query object.
	 * @param string $post_type Post type.
	 * @return string Fields SQL.
	 */
	public function build_fields( string $fields, $query, string $post_type ): string {
		// Only modify for main GD queries, not archive/post_type pages
		if ( ! $this->is_gd_main_query( $query ) || geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) ) {
			return $fields;
		}

		return $fields . $this->fields_builder->build( $query, $post_type );
	}

	/**
	 * Build JOIN clause.
	 *
	 * @param string $join      Current join.
	 * @param object $query     WP_Query object.
	 * @param string $post_type Post type.
	 * @return string Join SQL.
	 */
	public function build_join( string $join, $query, string $post_type ): string {
		if ( ! $this->is_gd_main_query( $query ) ) {
			return $join;
		}

		return $join . $this->join_builder->build( $query, $post_type );
	}

	/**
	 * Build WHERE clause.
	 *
	 * @param string $where     Current where.
	 * @param object $query     WP_Query object.
	 * @param string $post_type Post type.
	 * @return string Where SQL.
	 */
	public function build_where( string $where, $query, string $post_type ): string {
		if ( ! $this->is_gd_main_query( $query ) ) {
			return $where;
		}

		return $where . $this->where_builder->build( $query, $post_type );
	}

	/**
	 * Build author WHERE clause.
	 *
	 * @param string $where     Current where.
	 * @param object $query     WP_Query object.
	 * @param string $post_type Post type.
	 * @return string Where SQL.
	 */
	public function build_author_where( string $where, $query, string $post_type ): string {
		if ( ! $this->is_gd_main_query( $query ) ) {
			return $where;
		}

		return $this->where_builder->build_author_where( $where, $query, $post_type );
	}

	/**
	 * Build GROUP BY clause.
	 *
	 * @param string $groupby   Current group by.
	 * @param object $query     WP_Query object.
	 * @param string $post_type Post type.
	 * @return string Group by SQL.
	 */
	public function build_groupby( string $groupby, $query, string $post_type ): string {
		if ( ! $this->is_gd_main_query( $query ) ) {
			return $groupby;
		}

		return $groupby . $this->groupby_builder->build( $query, $post_type );
	}

	/**
	 * Build ORDER BY clause.
	 *
	 * @param string $orderby   Current order by.
	 * @param object $query     WP_Query object.
	 * @param string $post_type Post type.
	 * @return string Order by SQL.
	 */
	public function build_orderby( string $orderby, $query, string $post_type ): string {
		if ( ! $this->is_gd_main_query( $query ) ) {
			return $orderby;
		}

		return $this->orderby_builder->build( $query, $post_type );
	}

	/**
	 * Build HAVING clause within posts_clauses.
	 *
	 * @param array  $clauses   Posts clauses.
	 * @param object $query     WP_Query object.
	 * @param string $post_type Post type.
	 * @return array Modified clauses.
	 */
	public function build_having_clauses( array $clauses, $query, string $post_type ): array {
		if ( ! $this->is_gd_main_query( $query ) ) {
			return $clauses;
		}

		return $this->having_builder->build_clauses( $clauses, $query, $post_type );
	}

	/**
	 * Check if this is a GeoDirectory main query.
	 *
	 * @param object $query WP_Query object.
	 * @return bool True if GD main query.
	 */
	public function is_gd_main_query( $query ): bool {
		$is_main_query = false;

		if ( ( isset( $query->query->gd_is_geodir_page ) || isset( $query->query['gd_is_geodir_page'] ) ) && geodir_is_page( 'search' ) && ! isset( $_REQUEST['geodir_search'] ) ) {
			// If it's a search page with no queries then we don't add our filters
			$is_main_query = false;
		} elseif ( isset( $query->query->gd_is_geodir_page ) && $query->query->gd_is_geodir_page ) {
			$is_main_query = true;
		} elseif ( isset( $query->query['gd_is_geodir_page'] ) && $query->query['gd_is_geodir_page'] ) {
			$is_main_query = true;
		}

		return $is_main_query;
	}

	/**
	 * Get A-Z search WHERE clause.
	 *
	 * @param string $where     Current WHERE.
	 * @param object $query     WP_Query object.
	 * @param string $post_type Post type.
	 * @return string WHERE SQL.
	 */
	public function main_query_posts_where( string $where, $query, string $post_type ): string {
		global $wpdb;

		// A-Z Search value
		$value = geodir_az_search_value();

		if ( $value != '' ) {
			$where .= $wpdb->prepare( " AND `{$wpdb->posts}`.`post_title` LIKE %s ", $wpdb->esc_like( $value ) . '%' );
		}

		return $where;
	}

	/**
	 * Modify orderby for A-Z search.
	 *
	 * @param string $orderby   Current orderby.
	 * @param string $sort_by   Sort by parameter.
	 * @param string $table     Detail table.
	 * @param object $query     WP_Query object.
	 * @return string Orderby SQL.
	 */
	public function posts_order_by_sort( string $orderby, string $sort_by, string $table, $query ): string {
		global $wpdb;

		$value = geodir_az_search_value();

		if ( $value != '' ) {
			$_orderby = "`{$wpdb->posts}`.`post_title` ASC";

			if ( trim( $orderby ) != '' ) {
				$_orderby .= ',' . $orderby;
			}

			$orderby = $_orderby;
		}

		return $orderby;
	}
}
