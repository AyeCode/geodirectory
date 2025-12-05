<?php
/**
 * ORDER BY Clause Builder
 *
 * Builds ORDER BY clauses for GeoDirectory post queries including
 * distance sorting, rating sorting, custom field sorting, and search relevance.
 *
 * @package GeoDirectory\Database\Query\Builders
 * @since   3.0.0
 * @author  AyeCode Ltd
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Database\Query\Builders;

use AyeCode\GeoDirectory\Core\Services\QueryVars;
use AyeCode\GeoDirectory\Database\Query\Interfaces\SqlBuilderInterface;
use AyeCode\GeoDirectory\Database\Repository\SortRepository;

/**
 * ORDER BY clause builder.
 *
 * @since 3.0.0
 */
final class OrderByBuilder implements SqlBuilderInterface {
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
	 * Sort repository.
	 *
	 * @var SortRepository
	 */
	private SortRepository $sort_repository;

	/**
	 * Constructor.
	 *
	 * @param QueryVars      $query_vars       Query variables service.
	 * @param SortRepository $sort_repository  Sort repository.
	 */
	public function __construct( QueryVars $query_vars, SortRepository $sort_repository ) {
		global $wpdb;
		$this->db = $wpdb;
		$this->query_vars = $query_vars;
		$this->sort_repository = $sort_repository;
	}

	/**
	 * Build ORDER BY clause.
	 *
	 * @param object $query     WP_Query object.
	 * @param string $post_type Post type.
	 * @return string ORDER BY SQL.
	 */
	public function build( $query, string $post_type ): string {
		global $geodirectory, $s;

		$support_location = $post_type && geodirectory()->post_types->supports( $post_type, 'location' ) ;
		$orderby = ' ';
		$sort_by = $this->query_vars->get_sort_by();

		// Determine default sort
		if ( $sort_by == '' ) {
			if ( $support_location && ( $latlon = $this->query_vars->get_latlon() ) ) {
				$sort_by = 'distance_asc';
			} elseif ( is_search() && isset( $_REQUEST['geodir_search'] ) && $s && trim( $s ) != '' ) {
				$sort_by = 'search_best';
			} else {
				$default_sort = geodir_get_posts_default_sort( $post_type );

				if ( ! empty( $default_sort ) ) {
					$sort_by = $default_sort;
				}
			}
		}

		$table = geodir_db_cpt_table( $post_type );

		$orderby = $this->sort_by_sql( $sort_by, $post_type, $query );
		$orderby = $this->sort_by_children( $orderby, $sort_by, $post_type, $query );

		/**
		 * Filter order by SQL.
		 *
		 * @since 1.0.0
		 *
		 * @param string   $orderby   The orderby query string.
		 * @param string   $sort_by   Sortby query string.
		 * @param string   $table     Listing table name.
		 * @param WP_Query $query     The WP_Query.
		 */
		$orderby = apply_filters( 'geodir_posts_order_by_sort', $orderby, $sort_by, $table, $query );

		return $orderby;
	}

	/**
	 * Generate SQL for sort by parameter.
	 *
	 * @param string $sort_by   Sort by parameter.
	 * @param string $post_type Post type.
	 * @param object $wp_query  WP_Query object.
	 * @return string Order by SQL.
	 */
	public function sort_by_sql( string $sort_by = 'post_title_asc', string $post_type = 'gd_place', $wp_query = null ): string {
		$orderby = '';
		$table = geodir_db_cpt_table( $post_type );
		$order_by_parts = array();

		switch ( $sort_by ) {
			case 'distance':
			case 'distance_asc':
				$order_by_parts[] = 'distance ASC';
				$order_by_parts[] = $this->search_sort( '', $sort_by, $wp_query );
				break;
			case 'distance_desc':
				$order_by_parts[] = 'distance DESC';
				$order_by_parts[] = $this->search_sort( '', $sort_by, $wp_query );
				break;
			case 'search_best':
				$order_by_parts[] = $this->search_sort( '', $sort_by, $wp_query );
				break;
			case 'post_status_desc':
			case 'random':
				$rand_seed = $this->get_rand_seed();
				$order_by_parts[] = "rand($rand_seed)";
				break;
			case 'az':
			case 'post_title_asc':
			case 'title_asc':
				$order_by_parts[] = "{$this->db->posts}.post_title asc";
				break;
			case 'za':
			case 'post_title_desc':
			case 'title_desc':
				$order_by_parts[] = "{$this->db->posts}.post_title desc";
				break;
			case 'add_date_asc':
				$order_by_parts[] = "{$this->db->posts}.post_date asc";
				break;
			case 'latest':
			case 'add_date_desc':
				$order_by_parts[] = "{$this->db->posts}.post_date desc";
				break;
			case 'review_asc':
				$order_by_parts[] = $table . '.rating_count ASC';
				$order_by_parts[] = $table . '.overall_rating ASC';
				break;
			case 'high_review':
			case 'review_desc':
				$order_by_parts[] = $table . '.rating_count DESC';
				$order_by_parts[] = $table . '.overall_rating DESC';
				break;
			case 'rating_asc':
			case 'rating_desc':
			case 'high_rating':
				$order_by_parts[] = $this->build_rating_sort( $sort_by, $post_type, $table );
				break;
			default:
				$default_sort = geodir_get_posts_default_sort( $post_type );

				if ( $default_sort == '' && $sort_by == $default_sort ) {
					$order_by_parts[] = "{$this->db->posts}.post_date desc";
				} else {
					$order_by_parts[] = $this->custom_sort( $orderby, $sort_by, $table, $post_type, $wp_query );
				}
				break;
		}

		if ( ! empty( $order_by_parts ) ) {
			$orderby = implode( ', ', array_filter( $order_by_parts ) );
		}

		return $orderby;
	}

	/**
	 * Build rating sort SQL with optional Bayesian averaging.
	 *
	 * @param string $sort_by   Sort by parameter.
	 * @param string $post_type Post type.
	 * @param string $table     Detail table name.
	 * @return string Rating sort SQL.
	 */
	private function build_rating_sort( string $sort_by, string $post_type, string $table ): string {
		if ( $sort_by == 'high_rating' ) {
			$sort_by = 'rating_desc';
		}

		$rating_order = $sort_by == 'rating_asc' ? 'ASC' : 'DESC';
		$use_bayesian = apply_filters( 'geodir_use_bayesian', true, $table );

		if ( $use_bayesian ) {
			$statuses = geodir_get_post_stati( 'public', array( 'post_type' => $post_type ) );

			if ( count( $statuses ) > 1 ) {
				$post_status_where = "WHERE post_status IN( '" . implode( "', '", $statuses ) . "' )";
			} else {
				$post_status_where = "WHERE post_status = '{$statuses[0]}'";
			}

			$avg_num_votes = get_transient( 'gd_avg_num_votes_' . $table );

			if ( $avg_num_votes === false ) {
				$avg_num_votes = (int) $this->db->get_var( "SELECT SUM( rating_count ) FROM {$table} {$post_status_where}" );
				$avg_rating = false;

				set_transient( 'gd_avg_num_votes_' . $table, $avg_num_votes, 12 * HOUR_IN_SECONDS );
			} else {
				$avg_num_votes = (int) $avg_num_votes;
				$avg_rating = get_transient( 'gd_avg_rating_' . $table );
			}

			if ( $avg_rating === false ) {
				if ( $avg_num_votes > 0 ) {
					$avg_rating = $this->db->get_var( "SELECT SUM( overall_rating ) FROM {$table} {$post_status_where}" ) / $avg_num_votes;
				} else {
					$avg_rating = 0;
				}

				set_transient( 'gd_avg_rating_' . $table, $avg_rating, 12 * HOUR_IN_SECONDS );
			} else {
				$avg_rating = geodir_sanitize_float( $avg_rating );
			}

			return " ( ( ( $avg_num_votes * $avg_rating ) + ( {$table}.rating_count * {$table}.overall_rating ) )  / ( $avg_num_votes + {$table}.rating_count ) ) $rating_order, {$table}.overall_rating $rating_order";
		} else {
			return "{$table}.overall_rating $rating_order, {$table}.rating_count $rating_order";
		}
	}

	/**
	 * Generate search relevance sorting SQL.
	 *
	 * @param string $orderby  Current orderby string.
	 * @param string $sort_by  Sort by parameter.
	 * @param object $wp_query WP_Query object.
	 * @return string Order by SQL.
	 */
	private function search_sort( string $orderby = '', string $sort_by = '', $wp_query = null ): string {
		$s = $this->query_vars->get_search_term();
		$gd_exact_search = $this->query_vars->is_exact_search( $s );

		if ( is_search() && isset( $_REQUEST['geodir_search'] ) && $s && trim( $s ) != '' && ( ! empty( $wp_query ) && $this->is_gd_main_query( $wp_query ) ) ) {
			if ( $gd_exact_search ) {
				$keywords = array( $s );
			} else {
				$keywords = explode( ' ', $s );

				if ( is_array( $keywords ) && ( $klimit = (int) geodir_get_option( 'search_word_limit' ) ) ) {
					foreach ( $keywords as $kkey => $kword ) {
						if ( geodir_utf8_strlen( $kword ) <= $klimit ) {
							unset( $keywords[ $kkey ] );
						}
					}
				}
			}

			if ( count( $keywords ) > 1 ) {
				$orderby = '( gd_titlematch * 2  + gd_exacttitle * 10 + gd_alltitlematch_part * 100 + gd_titlematch_part * 50 + gd_content * 1.5) DESC';
			} else {
				$orderby = '( gd_titlematch * 2  + gd_exacttitle * 10 + gd_content * 1.5) DESC';
			}
		}

		return $orderby;
	}

	/**
	 * Generate SQL for custom field sorting.
	 *
	 * @param string $orderby   Current orderby string.
	 * @param string $sort_by   Sort by parameter.
	 * @param string $table     Listing table name.
	 * @param string $post_type Post type.
	 * @param object $wp_query  WP_Query object.
	 * @return string Order by SQL.
	 */
	private function custom_sort( string $orderby, string $sort_by, string $table, string $post_type = '', $wp_query = null ): string {
		if ( $sort_by != '' && ( ! is_search() || ( isset( $_REQUEST['s'] ) && isset( $_REQUEST['snear'] ) && $_REQUEST['snear'] == '' && ( $_REQUEST['s'] == '' || $_REQUEST['s'] == ' ' ) ) ) ) {
			if ( substr( strtolower( $sort_by ), -5 ) == '_desc' ) {
				$order = 'desc';
				$sort_key = substr( $sort_by, 0, strlen( $sort_by ) - 5 );
			} else if ( substr( strtolower( $sort_by ), -4 ) == '_asc' ) {
				$order = 'asc';
				$sort_key = substr( $sort_by, 0, strlen( $sort_by ) - 4 );
			} else {
				$sort_key = '';
			}

			if ( $sort_key ) {
				$sort_by = $sort_key;

				switch ( $sort_by ) {
					case 'post_date':
					case 'comment_count':
						$orderby = "{$this->db->posts}.{$sort_by} {$order}, {$table}.overall_rating {$order}";
						break;
					case 'post_images':
						$orderby = "{$table}.featured_image {$order}";
						break;
					case 'distance':
						$orderby = "{$sort_by} {$order}";
						break;
					case 'overall_rating':
						$orderby = $this->build_rating_sort( $sort_by . '_' . $order, $post_type, $table );
						break;
					default:
						/**
						 * Filters custom key sort.
						 *
						 * @since 2.0.0.74
						 *
						 * @param string $_orderby  Custom key default orderby query string. Default NULL.
						 * @param string $sort_by   Sortby query string.
						 * @param string $order     Sortby order.
						 * @param string $orderby   The orderby query string.
						 * @param string $table     Listing table name.
						 * @param string $post_type Post type.
						 * @param object $wp_query  WP_Query object.
						 */
						$orderby = apply_filters( 'geodir_custom_key_orderby', '', $sort_by, $order, $orderby, $table, $post_type, $wp_query );

						if ( empty( $orderby ) ) {
							if ( $this->column_exist( $table, $sort_by ) ) {
								$orderby = "{$table}.{$sort_by} {$order}";
							} else {
								$orderby = "{$this->db->posts}.post_date desc";
							}
						}
						break;
				}
			}
		}

		/**
		 * Filters custom orderby.
		 *
		 * @since 2.0.0.74
		 *
		 * @param string $orderby   The orderby query string.
		 * @param string $sort_by   Sortby query string.
		 * @param string $table     Listing table name.
		 * @param string $post_type Post type.
		 * @param object $wp_query  WP_Query object.
		 */
		return apply_filters( 'geodir_orderby_custom_sort', $orderby, $sort_by, $table, $post_type, $wp_query );
	}

	/**
	 * Handle sorting with children (composite sorts).
	 *
	 * @param string $orderby   Current orderby SQL.
	 * @param string $sort_by   Sort by parameter.
	 * @param string $post_type Post type.
	 * @param object $wp_query  WP_Query object.
	 * @param int    $parent    Parent sort ID.
	 * @return string Order by SQL.
	 */
	public function sort_by_children( string $orderby, string $sort_by, string $post_type, $wp_query = null, int $parent = 0 ): string {
		if ( substr( strtolower( $sort_by ), -5 ) == '_desc' ) {
			$order = 'desc';
			$htmlvar_name = substr( $sort_by, 0, strlen( $sort_by ) - 5 );
		} else if ( substr( strtolower( $sort_by ), -4 ) == '_asc' ) {
			$order = 'asc';
			$htmlvar_name = substr( $sort_by, 0, strlen( $sort_by ) - 4 );
		} else {
			$htmlvar_name = '';
		}

		if ( ! empty( $orderby ) && $htmlvar_name ) {
			$parent_id = $this->sort_repository->get_parent_id_by_htmlvar( $htmlvar_name, $order, $post_type, $parent );

			if ( $parent_id ) {
				$children = $this->sort_repository->get_children_by_parent( $post_type, $parent_id );

				if ( $children ) {
					$orderby_parts = array();

					foreach ( $children as $child ) {
						if ( $child->field_type == 'random' ) {
							$child_sort_by = 'random';
						} else {
							$child_sort_by = $child->htmlvar_name . '_' . $child->sort;
						}
						$child_sort = $this->sort_by_sql( $child_sort_by, $post_type, $wp_query );

						if ( ! empty( $child_sort ) ) {
							$orderby_parts[] = $child_sort;
						}
					}

					if ( ! empty( $orderby_parts ) ) {
						if ( ! empty( $orderby ) ) {
							$orderby .= ', ';
						}

						$orderby .= implode( ', ', array_filter( $orderby_parts ) );
					}
				}
			}
		}

		return $orderby;
	}

	/**
	 * Get a seed value for RAND() sort order (set for 24 hours).
	 *
	 * @return int Random seed value.
	 */
	private function get_rand_seed(): int {
		$rand_seed = get_transient( 'geodir_rand_seed' );

		if ( ! $rand_seed ) {
			$rand_seed = time();
			set_transient( 'geodir_rand_seed', $rand_seed, 24 * HOUR_IN_SECONDS );
		}

		$rand_seed = absint( $rand_seed );

		return apply_filters( 'geodir_rand_seed', $rand_seed );
	}

	/**
	 * Check if table column exists.
	 *
	 * @param string $db     The table name.
	 * @param string $column The column name.
	 * @return bool True if column exists.
	 */
	private function column_exist( string $db, string $column ): bool {
		$exists = false;
		$columns = $this->db->get_col( "SHOW COLUMNS FROM {$db}" );

		foreach ( $columns as $c ) {
			if ( $c == $column ) {
				$exists = true;
				break;
			}
		}

		return $exists;
	}

	/**
	 * Check if this is a GeoDirectory main query.
	 *
	 * @param object $query WP_Query object.
	 * @return bool True if GD main query.
	 */
	private function is_gd_main_query( $query ): bool {
		$is_main_query = false;

		if ( ( isset( $query->query->gd_is_geodir_page ) || isset( $query->query['gd_is_geodir_page'] ) ) && geodir_is_page( 'search' ) && ! isset( $_REQUEST['geodir_search'] ) ) {
			$is_main_query = false;
		} elseif ( isset( $query->query->gd_is_geodir_page ) && $query->query->gd_is_geodir_page ) {
			$is_main_query = true;
		} elseif ( isset( $query->query['gd_is_geodir_page'] ) && $query->query['gd_is_geodir_page'] ) {
			$is_main_query = true;
		}

		return $is_main_query;
	}
}
