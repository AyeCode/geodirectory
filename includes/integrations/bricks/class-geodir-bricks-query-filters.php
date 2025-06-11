<?php
/**
 * GeoDirectory Bricks
 *
 * Adds compatibility for Bricks builder.
 *
 * @author   AyeCode
 * @category Compatibility
 * @package  GeoDirectory
 * @since    2.3.33
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GeoDir_Bricks_Query_Filters {

	/**
	 * Init.
	 *
	 * @since 2.3.33
	 */
	public static function init() {

		// conditions to run our filters
		if ( ! is_admin() || ! empty( $_REQUEST['bricks-is-builder'] ) || ( function_exists( 'bricks_is_builder_call' ) && bricks_is_builder_call() ) ) {

			add_filter( 'bricks/posts/query_vars', array( __CLASS__, 'add_query_vars' ), 10, 4 );

			add_action( 'pre_get_posts', array( __CLASS__, 'pre_get_posts' ), 1000 );

			add_filter( 'posts_selection', array( __CLASS__, 'remove_filters' ), 1000, 2 );
		}


		// Add our query loop options to the elements, section
		add_filter( 'bricks/elements/slider/controls', array( __CLASS__, 'add_element_controls' ) );
		add_filter( 'bricks/elements/section/controls', array( __CLASS__, 'add_element_controls' ) );
		add_filter( 'bricks/elements/carousel/controls', array( __CLASS__, 'add_element_controls' ) );
		add_filter( 'bricks/elements/accordion/controls', array( __CLASS__, 'add_element_controls' ) );
		add_filter( 'bricks/elements/container/controls', array( __CLASS__, 'add_element_controls' ) );
		add_filter( 'bricks/elements/div/controls', array( __CLASS__, 'add_element_controls' ) );
		add_filter( 'bricks/elements/block/controls', array( __CLASS__, 'add_element_controls' ) );

	}

	/**
	 * Maybe add our filters to the query.
	 *
	 * @param $query
	 *
	 * @return void
	 */
	public static function pre_get_posts( $query ) {

		if ( self::is_gd_post_type( $query ) ) {

			self::apply_filters();

		}

	}

	/**
	 * Join post filters.
	 *
	 * @param $join
	 * @param $query
	 *
	 * @return mixed|string
	 */
	public static function posts_join( $join, $query = array() ) {
		global $wpdb;

		if ( self::is_gd_post_type( $query ) ) {
			$post_type = self::query_post_type( $query->query_vars );

			$table = geodir_db_cpt_table( $post_type );
			$_join = " INNER JOIN " . $table . " ON (" . $table . ".post_id = $wpdb->posts.ID) ";

			if ( strpos( $join, $_join ) === false ) {
				$join .= $_join;
			}
		}

		return $join;
	}

	/**
	 * Get the Query post type.
	 *
	 * @since 2.8.106
	 *
	 * @param array $query_vars Query vars.
	 * @return string The post type.
	 */
	public static function query_post_type( $query_vars ) {
		$post_type = '';

		if ( empty( $query_vars['post_type'] ) && !isset( $query_vars['gd_is_geodir_page'] ) ) {
			return $post_type;
		}

		if ( !empty( $query_vars['post_type'] ) && is_array( $query_vars['post_type'] ) && count( $query_vars['post_type'] ) === 1 ) {
			$post_type = sanitize_key( $query_vars['post_type'][0] );
		} else if ( !empty( $query_vars['post_type'] ) &&  is_scalar( $query_vars['post_type'] ) ) {
			$post_type = sanitize_key( $query_vars['post_type'] );
		} else if ( geodir_is_page( 'search' ) && ! empty( $_REQUEST['stype'] ) ) {
			$post_type = geodir_is_gd_post_type( $_REQUEST['stype'] ) ? sanitize_key( $_REQUEST['stype'] ) : '';
		}

		return $post_type;
	}

	/**
	 * Add query vars if set to do so.
	 *
	 * @param $query_vars
	 * @param $settings
	 * @param $element_id
	 * @param $element_name
	 *
	 * @return mixed
	 */
	public static function add_query_vars( $query_vars, $settings, $element_id, $element_name ) {
		global $geodirectory;

		$post_type = self::query_post_type( $query_vars );

		if (
			! empty( $settings['hasLoop'] ) && ! empty( $settings['isGDLoop'] ) &&
			! empty( $post_type ) && geodir_is_gd_post_type( $post_type ) // ONE post type only
		) {

			$query_vars['gd_is_geodir_page'] = 1;
			$query_vars['is_geodir_loop']    = 1;
			$query_vars['gd_location']       = 1;

			$query_vars['is_bricks_geodir_loop'] = 1;

			// maybe set location query vars
			$location = $geodirectory->location;
			if ( ! empty( $location ) && defined( 'GEODIRLOCATION_VERSION' ) && empty( $settings['disableGDLocationLoop'] ) ) {
				if ( ! empty( $location->country ) && ! isset( $query_vars['country'] ) ) {
					$query_vars['country'] = esc_attr( $location->country );
				}

				if ( ! empty( $location->region ) && ! isset( $query_vars['region'] ) ) {
					$query_vars['region'] = esc_attr( $location->region );
				}

				if ( ! empty( $location->city ) && ! isset( $query_vars['city'] ) ) {
					$query_vars['city'] = esc_attr( $location->city );
				}

				if ( ! empty( $location->neighbourhood ) && ! isset( $query_vars['neighbourhood'] ) ) {
					$query_vars['neighbourhood'] = esc_attr( $location->neighbourhood );
				}
			}
		}


		return $query_vars;
	}

	/**
	 * Maybe add our own post fields.
	 *
	 * @param $fields
	 * @param $query
	 *
	 * @return mixed|string
	 */
	public static function posts_fields( $fields, $query ) {

		$post_type = self::is_gd_post_type( $query );

		if ( $post_type ) {
			$table = geodir_db_cpt_table( $post_type );

			$fields .= ', ' . $table . '.*';
		}

		return $fields;
	}

	/**
	 * Check if the query is a GD post type and set to use our tables.
	 *
	 * @param $query
	 *
	 * @return string
	 */
	public static function is_gd_post_type( $query ) {
		// Get the post type only if 1
		$post_type = '';

		if ( ! empty( $query->query_vars['is_bricks_geodir_loop'] ) && ! empty( $query->query_vars['is_geodir_loop'] ) || (!empty($query->query_vars['gd_is_geodir_page']) && geodir_is_page( 'search' ) ) ) {
			$post_type = self::query_post_type( $query->query_vars );

			// Check its a GD post type
			if ( $post_type && ! geodir_is_gd_post_type( $post_type ) ) {
				$post_type = '';
			}
		}

		return $post_type;
	}

	/**
	 * Maybe filter the posts where.
	 *
	 * @param $where
	 * @param $query
	 *
	 * @return mixed|string
	 */
	public static function posts_where( $where, $query ) {
		global $geodirectory, $wpdb;

		$post_type = self::is_gd_post_type( $query );

		if ( $post_type ) {
			$table = geodir_db_cpt_table( $post_type );

			// Filter by location
			if ( function_exists( 'geodir_location_posts_where' ) ) {
				$location_where = geodir_location_posts_where( $post_type, $query );

				if ( ! empty( $location_where ) ) {
					$where .= " AND {$location_where} ";
				}
			}

			if ( ! empty( $query->query['meta_query'] ) ) {
				$where .= self::convert_meta_query_to_sql( $query->query['meta_query'], $table );
			}
		}

		return $where;
	}

	/**
	 * Blank the meta query where and join so we can add our own in.
	 *
	 * @param $sql
	 * @param $queries
	 * @param $type
	 * @param $primary_table
	 * @param $primary_id_column
	 * @param $context
	 *
	 * @return string[]
	 */
	public static function get_meta_sql( $sql, $queries, $type, $primary_table, $primary_id_column, $context ) {

		return array(
			'join'  => '',
			'where' => '',
		);
	}

	/**
	 * Maybe add our meta posts order by.
	 *
	 * @param $orderby
	 * @param $query
	 *
	 * @return array|mixed|string|string[]
	 */
	public static function posts_orderby( $orderby, $query ) {
		global $geodirectory, $wpdb;

		$post_type = self::is_gd_post_type( $query );

		if ( $post_type ) {
			$table = geodir_db_cpt_table( $post_type );

			if ( ! empty( $query->query['meta_key'] ) && strpos( $orderby, $wpdb->prefix . 'postmeta.meta_value' ) === 0 ) {
				$key     = esc_sql( $query->query['meta_key'] );
				$orderby = str_replace( $wpdb->prefix . 'postmeta.meta_value', $table . '.' . $key, $orderby );
			}

		}


		return $orderby;
	}

	/**
	 * @param $limits
	 * @param $query
	 *
	 * @return mixed
	 */
	public static function post_limits( $limits, $query ) {
		global $wpdb;

		if ( ! $limits && $query->query_vars['gd_is_geodir_page'] && !empty($query->query_vars['posts_per_page']) && $query->query_vars['posts_per_page'] > 1 ) {
			return $wpdb->prepare( 'LIMIT %d, %d', $query->query_vars['paged'], $query->query_vars['posts_per_page'] );
		}

		return $limits;
	}

	/**
	 * Add filters to affect certain queries for the bricks query loop.
	 *
	 * @return void
	 */
	public static function apply_filters() {
		add_filter( 'posts_fields', array( __CLASS__, 'posts_fields' ), 10, 2 );
		add_filter( 'posts_join', array( __CLASS__, 'posts_join' ), 10, 2 );
		add_filter( 'posts_where', array( __CLASS__, 'posts_where' ), 10, 2 );
		add_filter( 'posts_orderby', array( __CLASS__, 'posts_orderby' ), 10, 2 );
		add_filter( 'post_limits', array( __CLASS__, 'post_limits' ), 10, 2 );


		add_filter( 'get_meta_sql', array( __CLASS__, 'get_meta_sql' ), 10, 6 );

	}

	/**
	 * Remove all the filters we added so it does not affect other queries.
	 *
	 * @return void
	 */
	public static function remove_filters() {
		remove_filter( 'posts_fields', array( __CLASS__, 'posts_fields' ), 10 );
		remove_filter( 'posts_join', array( __CLASS__, 'posts_join' ), 10 );
		remove_filter( 'posts_where', array( __CLASS__, 'posts_where' ), 10 );
		remove_filter( 'posts_orderby', array( __CLASS__, 'posts_orderby' ), 10 );
		remove_filter( 'post_limits', array( __CLASS__, 'post_limits' ), 10 );


		remove_filter( 'get_meta_sql', array( __CLASS__, 'get_meta_sql' ), 10 );


	}


	/**
	 * Build the meta query where SQL string from the query vars to work with the GD table.
	 *
	 * @param $meta_query
	 * @param $table_alias
	 *
	 * @return string
	 */
	public static function convert_meta_query_to_sql( $meta_query, $table_alias = 'wp_geodir_gd_place_detail' ) {
		global $wpdb;
		$sql_parts = [];

		foreach ( $meta_query as $query ) {
			// Skip if it's a relation parameter
			if ( isset( $query['relation'] ) ) {
				continue;
			}

			$column  = isset( $query['key'] ) ? $query['key'] : '';
			$compare = isset( $query['compare'] ) ? strtoupper( $query['compare'] ) : '=';
			$value   = isset( $query['value'] ) ? $query['value'] : '';

			// Sanitize column name
			$column = preg_replace( '/[^a-zA-Z0-9_]/', '', $column );

			// Build the column reference
			$column_ref = $table_alias . '.' . $column;

			// Handle different comparison types
			switch ( $compare ) {
				case 'EXISTS':
					$sql_parts[] = $wpdb->prepare( "(%s IS NOT NULL AND %s != '')",
						$column_ref,
						$column_ref
					);
					break;

				case 'NOT EXISTS':
					$sql_parts[] = $wpdb->prepare( "(%s IS NULL OR %s = '')",
						$column_ref,
						$column_ref
					);
					break;

				case 'BETWEEN':
					if ( is_array( $value ) && count( $value ) == 2 ) {
						$sql_parts[] = $wpdb->prepare(
							"($column_ref BETWEEN %s AND %s)",
							$value[0],
							$value[1]
						);
					}
					break;

				case 'IN':
					if ( is_array( $value ) && ! empty( $value ) ) {
						// Create placeholders for each value
						$placeholders = array_fill( 0, count( $value ), '%s' );
						$in_clause    = implode( ',', $placeholders );

						// Prepare the SQL with all values
						$sql_parts[] = $wpdb->prepare(
							"$column_ref IN ($in_clause)",
							...$value
						);
					}
					break;

				case 'LIKE':
					$sql_parts[] = $wpdb->prepare(
						"$column_ref LIKE %s",
						'%' . $wpdb->esc_like( $value ) . '%'
					);
					break;

				default:
					if ( $value !== '' ) {
						$sql_parts[] = $wpdb->prepare(
							"$column_ref $compare %s",
							$value
						);
					}
					break;
			}
		}

		// Join all parts with AND
		return ! empty( $sql_parts ) ? ' AND ' . implode( ' AND ', $sql_parts ) : '';
	}

	/**
	 * Add GD query loop options to the element controls
	 *
	 * @param $controls
	 *
	 * @return mixed
	 */
	public static function add_element_controls( $controls ) {
		$pos = array_search( 'query', array_keys( $controls ) ); // Find position of 'query'

		if ( $pos !== false ) {
			$pos ++; // Move to the next position

			$new_controls = array(
				'isGDLoop'              => array(
					'tab'      => 'content',
					'label'    => esc_html__( 'GeoDirectory Query', 'geodirectory' ),
					'type'     => 'checkbox',
					'required' => array(
						'hasLoop',
						'!=',
						''
					)
				),
				'disableGDLocationLoop' => array( // Example of adding multiple new elements
					'tab'      => 'content',
					'label'    => esc_html__( 'Disable GD Location Filtering', 'geodirectory' ),
					'type'     => 'checkbox',
					'required' => array(
						'isGDLoop',
						'!=',
						''
					)
				)
			);

			$controls = array_merge(
				array_slice( $controls, 0, $pos, true ), // First part
				$new_controls, // Insert multiple new elements
				array_slice( $controls, $pos, null, true ) // Remaining part
			);
		}


		return $controls;
	}

}

GeoDir_Bricks_Query_Filters::init();
