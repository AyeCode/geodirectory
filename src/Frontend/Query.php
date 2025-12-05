<?php
/**
 * Frontend Query Manager
 *
 * Integrates GeoDirectory queries with WordPress by hooking into WP_Query
 * and modifying queries for GeoDirectory pages.
 *
 * @package GeoDirectory\Frontend
 * @since   3.0.0
 * @author  AyeCode Ltd
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Frontend;

use AyeCode\GeoDirectory\Database\Query\QueryBuilder;

/**
 * Frontend query integration class.
 *
 * @since 3.0.0
 */
final class Query {
	/**
	 * Query builder.
	 *
	 * @var QueryBuilder
	 */
	private QueryBuilder $query_builder;

	/**
	 * Query vars to add to WP.
	 *
	 * @var array
	 */
	public array $query_vars = array();

	/**
	 * Constructor.
	 *
	 * @param QueryBuilder $query_builder Query builder.
	 */
	public function __construct( QueryBuilder $query_builder ) {
		$this->query_builder = $query_builder;
		$this->init_query_vars();
	}

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		if ( ! is_admin() ) {
			add_action( 'wp_loaded', array( $this, 'get_errors' ), 20 );
			add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );
			add_action( 'parse_request', array( $this, 'parse_request' ), 0 );
			add_action( 'pre_get_posts', array( $this, 'set_globals' ) );
			add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
			add_filter( 'pre_handle_404', array( $this, 'pre_handle_404' ), 20, 2 );
		}

		add_filter( 'split_the_query', array( $this, 'split_the_query' ), 100, 2 );
		add_action( 'wp', array( $this, 'set_wp_the_query' ), 1, 1 );

		add_filter( 'geodir_main_query_posts_where', array( $this, 'main_query_posts_where' ), 10, 3 );
		add_filter( 'geodir_posts_order_by_sort', array( $this, 'posts_order_by_sort' ), 10, 4 );
	}

	/**
	 * Init query vars.
	 *
	 * @return void
	 */
	private function init_query_vars(): void {
		$this->query_vars = array(
			'gd_is_geodir_page' => 'gd_is_geodir_page',
			'listing_type' => 'listing_type',
			'pid' => 'pid'
		);
	}

	/**
	 * Add query vars.
	 *
	 * @param array $vars WP query vars.
	 * @return array Modified query vars.
	 */
	public function add_query_vars( array $vars ): array {
		foreach ( $this->get_query_vars() as $key => $var ) {
			$vars[] = $key;
		}

		return $vars;
	}

	/**
	 * Get query vars.
	 *
	 * @return array Query vars.
	 */
	public function get_query_vars(): array {
		return apply_filters( 'geodirectory_get_query_vars', $this->query_vars );
	}

	/**
	 * Set globals on pre_get_posts.
	 *
	 * @param \WP_Query $q Query object.
	 * @return void
	 */
	public function set_globals( $q ): void {
		global $wp_query, $geodir_post_type;

		if ( empty( $wp_query ) ) {
			$wp_query = $q;
		}

		$geodir_post_type = geodir_get_current_posttype();
	}

	/**
	 * Hook into pre_get_posts to modify GD queries.
	 *
	 * @param \WP_Query $q Query object.
	 * @return void
	 */
	public function pre_get_posts( $q ): void {
		// We only want to affect the main query
		if ( ! $q->is_main_query() ) {
			return;
		}

		// We only want to affect GD pages
		if ( ! geodir_is_geodir_page() ) {
			// Exclude GD templates from WP search
			$exclude_posts = ! empty( $q->is_search ) && ( ! is_admin() || wp_doing_ajax() ) ? true : false;
			$exclude_posts = apply_filters( 'geodir_wp_search_exclude_posts', $exclude_posts, $q );

			if ( $exclude_posts ) {
				$exclude_ids = \GeoDir_SEO::get_noindex_page_ids();

				if ( ! empty( $exclude_ids ) && is_array( $exclude_ids ) ) {
					$q->set( 'post__not_in', $exclude_ids );
				}
			}

			return;
		}

		// Remove all pre filters, controversial but should only affect our own queries
		remove_all_filters( 'query' );
		remove_all_filters( 'posts_search' );
		remove_all_filters( 'posts_fields' );
		remove_all_filters( 'posts_join' );
		remove_all_filters( 'posts_groupby' );
		remove_all_filters( 'posts_orderby' );
		remove_all_filters( 'posts_where' );

		// If post_type or archive then add query filters
		if ( geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) ) {
			add_filter( 'posts_fields', array( $this, 'posts_fields' ), 10, 2 );
			add_filter( 'posts_join', array( $this, 'posts_join' ), 10, 2 );
			add_filter( 'posts_where', array( $this, 'posts_where' ), 10, 2 );
			add_filter( 'posts_where', array( $this, 'author_where' ), 10, 2 );
			add_filter( 'posts_groupby', array( $this, 'posts_groupby' ), 10, 2 );
			add_filter( 'posts_orderby', array( $this, 'posts_orderby' ), 10, 2 );
		} elseif ( geodir_is_page( 'search' ) ) {
			// Some page builders break editor
			if (
				( ( function_exists( 'et_divi_load_scripts_styles' ) || function_exists( 'dbp_filter_bfb_enabled' ) ) && ! empty( $_REQUEST['et_fb'] ) && ! empty( $_REQUEST['et_bfb'] ) )
				|| (
					class_exists( 'Brizy_Editor' ) &&
					(
						( isset( $_GET[ \Brizy_Editor::prefix( '-edit' ) ] ) || isset( $_GET[ \Brizy_Editor::prefix( '-edit-iframe' ) ] ) ) ||
						\Brizy_Editor_Entity::isBrizyEnabled( geodir_search_page_id() )
					)
				)
			) {
			} else if ( ! isset( $_REQUEST['elementor-preview'] ) ) {
				$q->is_page = false;
				$q->is_singular = false;
			}

			$q->is_search = true;
			$q->is_archive = true;
			$q->is_paged = true;

			add_filter( 'posts_join', array( $this, 'posts_join' ), 1, 2 );
			add_filter( 'posts_fields', array( $this, 'posts_fields' ), 1, 2 );
			add_filter( 'posts_where', array( $this, 'posts_where' ), 1, 2 );
			add_filter( 'posts_groupby', array( $this, 'posts_groupby' ), 1, 2 );
			add_filter( 'posts_orderby', array( $this, 'posts_orderby' ), 1, 2 );
			add_filter( 'posts_clauses', array( $this, 'posts_having' ), 99999, 2 );
		} else if ( is_author() ) {
			add_filter( 'posts_where', array( $this, 'author_where' ), 10, 2 );
		}

		// Remove the pre_get_posts hook
		$this->remove_product_query();
	}

	/**
	 * Posts fields filter.
	 *
	 * @param string $fields Fields SQL.
	 * @param array  $query  Query array.
	 * @return string Modified fields SQL.
	 */
	public function posts_fields( string $fields, $query = array() ): string {
		global $geodir_post_type;
		return $this->query_builder->build_fields( $fields, $query, $geodir_post_type );
	}

	/**
	 * Posts join filter.
	 *
	 * @param string $join  Join SQL.
	 * @param array  $query Query array.
	 * @return string Modified join SQL.
	 */
	public function posts_join( string $join, $query = array() ): string {
		global $geodir_post_type;
		return $this->query_builder->build_join( $join, $query, $geodir_post_type );
	}

	/**
	 * Posts where filter.
	 *
	 * @param string $where Where SQL.
	 * @param array  $query Query array.
	 * @return string Modified where SQL.
	 */
	public function posts_where( string $where, $query = array() ): string {
		global $geodir_post_type;
		return $this->query_builder->build_where( $where, $query, $geodir_post_type );
	}

	/**
	 * Author where filter.
	 *
	 * @param string $where Where SQL.
	 * @param array  $query Query array.
	 * @return string Modified where SQL.
	 */
	public function author_where( string $where, $query = array() ): string {
		global $geodir_post_type;
		return $this->query_builder->build_author_where( $where, $query, $geodir_post_type );
	}

	/**
	 * Posts group by filter.
	 *
	 * @param string $groupby Group by SQL.
	 * @param array  $query   Query array.
	 * @return string Modified group by SQL.
	 */
	public function posts_groupby( string $groupby, $query = array() ): string {
		global $geodir_post_type;
		return $this->query_builder->build_groupby( $groupby, $query, $geodir_post_type );
	}

	/**
	 * Posts order by filter.
	 *
	 * @param string $orderby Order by SQL.
	 * @param array  $query   Query array.
	 * @return string Modified order by SQL.
	 */
	public function posts_orderby( string $orderby, $query = array() ): string {
		global $geodir_post_type;
		return $this->query_builder->build_orderby( $orderby, $query, $geodir_post_type );
	}

	/**
	 * Posts having filter (via posts_clauses).
	 *
	 * @param array $clauses Posts clauses.
	 * @param array $query   Query array.
	 * @return array Modified clauses.
	 */
	public function posts_having( array $clauses, $query = array() ): array {
		global $geodir_post_type;
		return $this->query_builder->build_having_clauses( $clauses, $query, $geodir_post_type );
	}

	/**
	 * Main query posts where filter.
	 *
	 * @param string $where     Where SQL.
	 * @param object $query     WP_Query object.
	 * @param string $post_type Post type.
	 * @return string Modified where SQL.
	 */
	public function main_query_posts_where( string $where, $query, string $post_type ): string {
		return $this->query_builder->main_query_posts_where( $where, $query, $post_type );
	}

	/**
	 * Posts order by sort filter.
	 *
	 * @param string $orderby  Order by SQL.
	 * @param string $sort_by  Sort by parameter.
	 * @param string $table    Detail table name.
	 * @param object $query    WP_Query object.
	 * @return string Modified order by SQL.
	 */
	public function posts_order_by_sort( string $orderby, string $sort_by, string $table, $query ): string {
		return $this->query_builder->posts_order_by_sort( $orderby, $sort_by, $table, $query );
	}

	/**
	 * Remove product query.
	 *
	 * @return void
	 */
	public function remove_product_query(): void {
		remove_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
	}

	/**
	 * Parse request.
	 *
	 * @return void
	 */
	public function parse_request(): void {
		global $wp;

		// Set add listing query parameters
		if ( ! empty( $wp ) && ! empty( $wp->query_vars['pagename'] ) && ! empty( $wp->query_vars['listing_type'] ) ) {
			if ( geodir_is_gd_post_type( $wp->query_vars['listing_type'] ) ) {
				$_REQUEST['listing_type'] = geodir_clean( $wp->query_vars['listing_type'] );

				if ( ! empty( $wp->query_vars['pid'] ) ) {
					$_REQUEST['pid'] = absint( $wp->query_vars['pid'] );
				}
			}
		}

		if ( isset( $_REQUEST['fl_builder'] ) ) {
			return; // Fix for BB not working on search page
		}

		$this->set_is_geodir_page( $wp );

		// Map query vars to their keys
		foreach ( $this->get_query_vars() as $key => $var ) {
			if ( isset( $_GET[ $var ] ) ) {
				$wp->query_vars[ $key ] = $_GET[ $var ];
			} else if ( isset( $wp->query_vars[ $var ] ) ) {
				$wp->query_vars[ $key ] = $wp->query_vars[ $var ];
			}
		}
	}

	/**
	 * Set is GeoDirectory page.
	 *
	 * @param object $wp WordPress object.
	 * @return void
	 */
	public function set_is_geodir_page( $wp ): void {
		if ( is_admin() ) {
			return;
		}

		// Post attachment
		if ( ! empty( $wp->query_vars['attachment'] ) && ! empty( $wp->query_vars['post_type'] ) && geodir_is_gd_post_type( $wp->query_vars['post_type'] ) ) {
			if ( isset( $wp->query_vars[ $wp->query_vars['post_type'] ] ) ) {
				unset( $wp->query_vars[ $wp->query_vars['post_type'] ] );
			}
			unset( $wp->query_vars['post_type'] );
			if ( isset( $wp->query_vars['name'] ) ) {
				unset( $wp->query_vars['name'] );
			}
			return;
		}

		if ( empty( $wp->query_vars ) || ! array_diff( array_keys( $wp->query_vars ), array( 'preview', 'page', 'paged', 'cpage' ) ) ) {
			if ( geodir_is_page( 'home' ) ) {
				$wp->query_vars['gd_is_geodir_page'] = true;
			}
		}

		if ( ! isset( $wp->query_vars['gd_is_geodir_page'] ) && isset( $wp->query_vars['page_id'] ) ) {
			if (
				geodir_is_page_id( $wp->query_vars['page_id'], 'add' )
				|| $wp->query_vars['page_id'] == geodir_preview_page_id()
				|| $wp->query_vars['page_id'] == geodir_success_page_id()
				|| $wp->query_vars['page_id'] == geodir_location_page_id()
			) {
				$wp->query_vars['gd_is_geodir_page'] = true;
			}
		}

		if ( ! isset( $wp->query_vars['gd_is_geodir_page'] ) && isset( $wp->query_vars['pagename'] ) ) {
			$page = get_page_by_path( $wp->query_vars['pagename'] );

			if ( ! empty( $page ) && (
				geodir_is_page_id( $page->ID, 'add' )
				|| $page->ID == geodir_preview_page_id()
				|| $page->ID == geodir_success_page_id()
				|| $page->ID == geodir_location_page_id()
				|| $page->ID == geodir_search_page_id()
			) ) {
				$wp->query_vars['gd_is_geodir_page'] = true;
			}
		}

		if ( ! isset( $wp->query_vars['gd_is_geodir_page'] ) && isset( $wp->query_vars['post_type'] ) && $wp->query_vars['post_type'] != '' ) {
			$requested_post_type = $wp->query_vars['post_type'];
			$post_type_array = geodir_get_posttypes();
			if ( in_array( $requested_post_type, $post_type_array ) ) {
				$wp->query_vars['gd_is_geodir_page'] = true;

				// Set embed
				if ( empty( $wp->query_vars['embed'] ) && ! empty( $wp->query_vars[ $requested_post_type ] ) && ! empty( $wp->request ) && strpos( $wp->request, '/' . $wp->query_vars[ $requested_post_type ] . '/embed' ) > 0 ) {
					$wp->query_vars['embed'] = true;
				}
			}
		}

		if ( ! isset( $wp->query_vars['gd_is_geodir_page'] ) ) {
			$geodir_taxonomies = geodir_get_taxonomies( '', true );
			if ( ! empty( $geodir_taxonomies ) ) {
				foreach ( $geodir_taxonomies as $taxonomy ) {
					if ( array_key_exists( $taxonomy, $wp->query_vars ) ) {
						$wp->query_vars['gd_is_geodir_page'] = true;
						break;
					}
				}
			}
		}

		// Author pages
		if ( ! isset( $wp->query_vars['gd_is_geodir_page'] ) && isset( $wp->query_vars['author_name'] ) && isset( $_REQUEST['geodir_dashbord'] ) ) {
			$wp->query_vars['gd_is_geodir_page'] = true;
		}
		if ( ! isset( $wp->query_vars['gd_is_geodir_page'] ) && isset( $wp->query_vars['gd_favs'] ) ) {
			$wp->query_vars['gd_is_geodir_page'] = true;
		}

		if ( ! isset( $wp->query_vars['gd_is_geodir_page'] ) && isset( $_REQUEST['geodir_search'] ) ) {
			$wp->query_vars['gd_is_geodir_page'] = true;
		}
	}

	/**
	 * Pre handle 404 filter.
	 *
	 * @param bool      $preempt  Whether to short-circuit.
	 * @param \WP_Query $wp_query WP_Query object.
	 * @return bool|null Modified preempt value.
	 */
	public function pre_handle_404( $preempt, $wp_query ) {
		if ( ! is_admin() && ! empty( $wp_query ) && is_object( $wp_query ) && $wp_query->is_main_query() && geodir_is_page( 'search' ) ) {
			// Don't 404 for search queries
			status_header( 200 );
			return;
		}

		return $preempt;
	}

	/**
	 * Split the query filter.
	 *
	 * @param bool      $split_the_query Whether to split.
	 * @param \WP_Query $wp_query        WP_Query object.
	 * @return bool Modified split value.
	 */
	public function split_the_query( bool $split_the_query, $wp_query ): bool {
		if ( $split_the_query && ! empty( $wp_query->request ) && strpos( $wp_query->request, 'geodir_gd_' ) !== false && strpos( $wp_query->request, '`_search_title`' ) !== false ) {
			$split_the_query = false;
		}

		return $split_the_query;
	}

	/**
	 * Set WP the query global.
	 *
	 * @param object $the_wp WP object.
	 * @return void
	 */
	public function set_wp_the_query( $the_wp ): void {
		global $wp_the_query, $gd_wp_the_query;

		if ( ! empty( $wp_the_query ) && isset( $wp_the_query->posts ) && $wp_the_query->is_main_query() && $this->query_builder->is_gd_main_query( $wp_the_query ) ) {
			if ( empty( $wp_the_query->posts ) || ( ! empty( $wp_the_query->posts[0]->post_type ) && geodir_is_gd_post_type( $wp_the_query->posts[0]->post_type ) ) ) {
				$gd_wp_the_query = $wp_the_query;
				$gd_wp_the_query->the_posts = $wp_the_query->posts;
			}
		}
	}

	/**
	 * Get errors from query string.
	 *
	 * @return void
	 */
	public function get_errors(): void {
		if ( ! empty( $_GET['gd_error'] ) && ( $error = sanitize_text_field( $_GET['gd_error'] ) ) ) {
			// @todo add some error notice
		}
	}
}
