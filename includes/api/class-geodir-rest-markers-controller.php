<?php

/**
 * Core class to access map markers data via the REST API.
 *
 * @since 2.0.0
 *
 * @see WP_REST_Taxonomies_Controller
 */
class GeoDir_REST_Markers_Controller extends WP_REST_Controller {

    /**
     * Constructor.
     *
     * @access public
     */
    public function __construct() {
        $this->namespace = GEODIR_REST_SLUG . '/v' . GEODIR_REST_API_VERSION;
		$this->rest_base = 'markers';

		add_filter( 'geodir_rest_markers_query_where', array( $this, 'set_query_where' ), 10, 2 );
    }

    /**
     * Registers the routes for the objects of the controller.
     *
     * @access public
     *
     * @see register_rest_route()
     */
    public function register_routes() {

        register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
				'args'            	  => $this->get_collection_params(),
			)
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
			'args' => array(
				'id' => array(
					'description' => __( 'Unique identifier for the object.' ),
					'type'        => 'integer',
				),
			),
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_marker_item' ),
				'permission_callback' => array( $this, 'get_marker_item_permissions_check' ),
			)
		) );
    }

	/**
	 * Checks if a given request has access to read and manage markers.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool True if the request has read access for the item, otherwise false.
	 */
	public function get_item_permissions_check( $request ) {
		return $this->show_in_rest();
	}

	/**
	 * Retrieves the query params for markers collections.
	 *
	 * @since 2.0.0
	 *
	 * @return array Markers collection parameters.
	 */
	public function get_collection_params() {
		$query_params = array();

		$query_params['post_type'] = array(
			'type'              => 'string',
			'description'       => __( 'Limit results to specific post type.' ),
			'sanitize_callback' => 'sanitize_key',
		);

		$query_params['term']   = array(
			'type'              => 'array',
			'default'           => array(),
			'description'       => __( 'Limit results to specific term IDs.' ),
			'items'             => array(
				'type'          => 'integer',
			),
		);

		$query_params['tag']   = array(
			'type'              => 'array',
			'default'           => array(),
			'description'       => __( 'Limit results to specific tags.' ),
			'items'             => array(
				'type'          => 'string',
			),
		);

		$query_params['post']   = array(
			'type'              => 'array',
			'default'           => array(),
			'description'       => __( 'Limit results to specific post IDs.' ),
			'items'             => array(
				'type'          => 'integer',
			),
		);

		$query_params['search']	= array(
			'type'               => 'string',
			'description'        => __( 'Limit results to those matching post title.' ),
			'sanitize_callback'  => 'sanitize_text_field',
			'validate_callback'  => 'rest_validate_request_arg',
		);

		/**
		 * Filter collection parameters for the markers controller.
		 *
		 * @since 2.0.0
		 *
		 * @param array $query_params JSON Schema-formatted collection parameters.
		 */
		return apply_filters( 'geodir_rest_markers_collection_params', $query_params );
	}

    /**
     * Show in rest.
     *
     * @since 2.0.0
     *
     * @return bool
     */
    public function show_in_rest() {
        return apply_filters( 'geodir_rest_markers_show_in_rest', true, $this );
    }

	/**
	 * Retrieves the makers.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return array|WP_Error Array on success, or WP_Error object on failure.
	 */
	public function get_item( $request ) {
		global $geodir_icon_basedir, $geodir_rest_cache_icons;
		if ( empty( $geodir_rest_cache_icons ) ) {
			$geodir_rest_cache_icons = array();
		}

		if ( ! $this->show_in_rest() ) {
			return new WP_Error( 'rest_invalid_access', __( 'Cannot view markers.' ) );
		}

		if ( ! ( ! empty( $request['post_type'] ) && geodir_is_gd_post_type( $request['post_type'] ) ) ) {
			return new WP_Error( 'rest_invalid_param', __( 'Enter a valid GD post type for post_type parameter.' ) );
		}

		$wp_upload_dir = wp_upload_dir();
		$geodir_icon_basedir = $wp_upload_dir['basedir'];

		$response = array();

		if ( ! empty( $request['output'] ) && $request['output'] == 'terms' ) {
			$response[ 'terms_filter' ] = $this->get_map_terms_filter( $request );
		} else {
			$items = $this->get_markers( $request );

			if ( is_wp_error( $items ) ) {
				return $items;
			}

			if ( ! empty( $items ) ) {
				$response[ 'total' ] 		= count( $items );
				$response[ 'baseurl' ] 		= $wp_upload_dir['baseurl'];
				$response[ 'content_url' ] 	= trailingslashit( WP_CONTENT_URL );
				$response[ 'icons' ] 		= $geodir_rest_cache_icons;
				$response[ 'items' ] 		= $items;
			}
		}
		/**
		 * Filter the markers response.
		 *
		 * @since 2.0.0
		 *
		 * @param array $response The response array.
		 * @param WP_REST_Request $request The request object.
		 */
		$response = apply_filters( 'geodir_rest_markers_response', $response, $request );

		return rest_ensure_response( $response );
	}

    /**
     * Get Markers.
     *
     * @since 2.0.0
     *
     * @param array $request {
     *      An array request post details.
     *
     *      @type string $post_type Post type.
     *      @type array $post Post array.
     * }
     * @return array $items.
     */
	public function get_markers( $request ) {
		global $wpdb;

		// Filter by latitude/longitude.
		$latitude = '';
		$longitude = '';
		if ( ! empty( $request['lat'] ) || ! empty( $request['lon'] ) ) {
			$latitude = ! empty( $request['lat'] ) && geodir_is_valid_lat( $request['lat'] ) ? filter_var( $request['lat'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION ) : '';
			$longitude = ! empty( $request['lon'] ) && geodir_is_valid_lon( $request['lon'] ) ? filter_var( $request['lon'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION ) : '';

			if ( empty( $latitude ) || empty( $longitude ) ) {
				return new WP_Error( 'rest_invalid_param', __( 'Invalid latitude/longitude.' ) );
			}
		}

		$table = geodir_db_cpt_table( $request['post_type'] );

		$fields = "p.ID, p.post_title, pd.default_category, pd.latitude, pd.longitude";
		if ( GeoDir_Post_types::supports( $request['post_type'], 'service_distance' ) ) {
			$fields .= ", pd.service_distance";
		}
		if ( $latitude && $longitude ) {
			$radius = geodir_getDistanceRadius( geodir_get_option( 'search_distance_long' ) );
			$fields .= ", ( {$radius} * 2 * ASIN( SQRT( POWER( SIN( ( ( {$latitude} ) - ( pd.latitude ) ) * PI() / 180 / 2 ), 2 ) + COS( ( {$latitude} ) * PI() / 180 ) * COS( ( pd.latitude ) * PI() / 180 ) * POWER( SIN( ( {$longitude} - pd.longitude ) * PI() / 180 / 2 ), 2 ) ) ) ) AS distance ";
		}

		$fields = apply_filters( 'geodir_rest_markers_query_fields', $fields, $request );

		$join = "LEFT JOIN {$table} AS pd ON pd.post_id = p.ID";
		$join = apply_filters( 'geodir_rest_markers_query_join', $join, $request );

		if ( ! empty( $request['post'] ) && is_array( $request['post'] ) && count( $request['post'] ) == 1 ) {
			$where = $wpdb->prepare( "pd.post_id IS NOT NULL AND ( p.post_type = %s OR p.post_type = %s )", array( $request['post_type'],'revision' ) );

			$statuses = geodir_get_post_stati( 'single-map', $request );

			if ( ! empty( $statuses ) ) {
				$where .= " AND p.post_status IN( '" . implode( "', '", $statuses ) . "' )";
			}
		} else {
			$where = $wpdb->prepare( "pd.post_id IS NOT NULL AND p.post_type = %s", array( $request['post_type'] ) );

			$status = geodir_get_post_stati( 'map', $request );

			if ( count( $status ) > 1 ) {
				$where .= " AND p.post_status IN( '" . implode( "', '", $status ) . "' )";
			} else {
				$where .= " AND p.post_status = '{$status[0]}'";
			}
		}
		$where = apply_filters( 'geodir_rest_markers_query_where', $where, $request );

		if ( $where ) {
			$where = "WHERE {$where}";
		}

		$group_by = apply_filters( 'geodir_rest_markers_query_group_by', "", $request );
		if ( $group_by ) {
			$group_by = "GROUP BY {$group_by}";
		}

		$order_by = apply_filters( 'geodir_rest_markers_query_order_by', "", $request );
		if ( $order_by ) {
			$order_by = "ORDER BY {$order_by}";
		}

		$limit = apply_filters( 'geodir_rest_markers_query_limit', "", $request );
		if ( $limit ) {
			$limit = "LIMIT {$limit}";
		}

		// ADD THE HAVING TO LIMIT TO THE EXACT RADIUS
		if ( $latitude && $longitude ) {
			/*
			 * The HAVING clause is often used with the GROUP BY clause to filter groups based on a specified condition.
			 * If the GROUP BY clause is omitted, the HAVING clause behaves like the WHERE clause.
			 */
			if ( strpos( $where, ' HAVING ' ) === false && strpos( $group_by, ' HAVING ' ) === false && strpos( $fields, 'AS distance' ) ) {
				$distance = ! empty( $request['dist'] ) ? geodir_sanitize_float( $request['dist'] ) : geodir_get_option( 'search_radius', 5 );

				if ( GeoDir_Post_types::supports( $request['post_type'], 'service_distance' ) ) {
					$having = $wpdb->prepare( " HAVING ( ( `pd`.`service_distance` > 0 AND distance <= `pd`.`service_distance` ) OR ( ( `pd`.`service_distance` <= 0 OR `pd`.`service_distance` IS NULL ) AND distance <= %f ) )", $distance );
				} else {
					$having = $wpdb->prepare( " HAVING distance <= %f ", $distance );
				}

				if ( trim( $group_by ) != '' ) {
					$group_by .= $having;
				} else {
					$where .= $having;
				}
			}
		}
		// ADD THE HAVING TO LIMIT TO THE EXACT RADIUS

		$sql = "SELECT {$fields} FROM {$wpdb->posts} AS p {$join} {$where} {$group_by} {$order_by} {$limit}";

		$sql = apply_filters( 'geodir_rest_markers_query', trim( $sql ), $request );

		$results = $wpdb->get_results( $sql );

		$items = array();
		if ( ! empty( $results ) ) {
			foreach ( $results as $item ) {
				if ( ! empty( $item->latitude ) && ! empty( $item->longitude ) ) {
					$items[] = $this->prepare_item_for_response( $item, $request );
				}
			}
		}

		return apply_filters( 'geodir_rest_get_markers', $items, $request );
	}

	/*
	icons
	array(
		'34' => array(						icon id
			'i' => '/2014/08/Feature.png',	icon relative url
			'w' => 36,						icon width
			'h' => 45,						icon height
		)
	);
	items
	array(
		'p' => 145,						post id
		'm' => 145,						marker id / post id
		'lt' => '12.9773088',			latitude
		'ln' => '77.57075069999996',	longitude
		't' => 'Longwood Gardens',		post title
		'i' => '34'						icon id
	);
	*/
    /**
     * Prepare item for response.
     *
     * @since 2.0.0
     *
     * @param object $item Request marker data.
     * @param WP_REST_Request $request  Request used to generate the response.
     * @return mixed|void|WP_Error|WP_REST_Response
     */
	public function prepare_item_for_response( $item, $request ) {
		global $geodir_icon_basedir, $geodir_rest_cache_marker, $geodir_rest_cache_icons;
		if ( empty( $geodir_rest_cache_marker ) ) {
			$geodir_rest_cache_marker = array();
		}

		$default_category = ! empty( $item->default_category ) ? $item->default_category : '';

		$post_title = $item->post_title;
		// @todo need to check for special chars
		/*
		if ( ! empty( $post_title ) ) {
			$post_title = htmlentities( $post_title, ENT_QUOTES, get_option( 'blog_charset' ) ); // Quotes in csv title import break maps
			$post_title = wp_specialchars_decode( $post_title ); // Fixed #post-320722 on 2016-12-08
		}
		*/

		$response = array();
		$response['m'] = (string) absint( $item->ID );
		$response['lt'] = $item->latitude;
		$response['ln'] = $item->longitude;
		$response['t'] 	= $post_title;

		$icon_id = ! empty( $default_category ) ? absint( $default_category ) : 'd'; // d = default

		if ( empty( $geodir_rest_cache_icons[ $icon_id ] ) ) {
			$icon_url 		= '';
			$icon_width 	= 36;
			$icon_height 	= 45;

			if ( ! empty( $geodir_rest_cache_marker ) && ! empty( $geodir_rest_cache_marker[ $default_category ]['i'] ) ) {
				$icon_url 	= $geodir_rest_cache_marker[ $default_category ]['i'];
			} else {
				$icon_url = geodir_get_cat_icon( $default_category, false, true );
				if ( empty( $icon_url ) ) {
					$icon_id = 'd';
					$icon_url = GeoDir_Maps::default_marker_icon( false );
				}

				if ( $default_category ) {
					$geodir_rest_cache_marker[ $default_category ]['i'] = $icon_url;
				}
			}

			if ( ! empty( $icon_url ) ) {
				if ( ! empty( $geodir_rest_cache_marker ) && ! empty( $geodir_rest_cache_marker[ $icon_url ]['w'] ) ) {
					$icon_width 	= $geodir_rest_cache_marker[ $icon_url ]['w'];
					$icon_height 	= $geodir_rest_cache_marker[ $icon_url ]['h'];
				} else {
					$icon_size = GeoDir_Maps::get_marker_size( trailingslashit( $geodir_icon_basedir ) . trim( $icon_url, '/\\' ) );
					if ( ! empty( $icon_size ) ) {
						$icon_width 	= $icon_size['w'];
						$icon_height 	= $icon_size['h'];
					}

					$geodir_rest_cache_marker[ $icon_url ]['w'] = $icon_width;
					$geodir_rest_cache_marker[ $icon_url ]['h'] = $icon_height;
				}
			}

			$_item = array(
				'i' => $icon_url,
				'w' => $icon_width,
				'h' => $icon_height
			);

			// Icon alt text.
			$alt = geodir_get_cat_icon_alt( $icon_id );

			if ( $alt != '' ) {
				$_item['a'] = $alt;
			}

			$geodir_rest_cache_icons[ $icon_id ] = $_item;
		}
		$response['i'] 	= $icon_id;

		/**
		 * Filters a marker data returned from the API.
		 *
		 * @param WP_REST_Response $response The response object.
		 * @param object           $item     The original marker data.
		 * @param WP_REST_Request  $request  Request used to generate the response.
		 */
		return apply_filters( 'geodir_rest_prepare_marker', $response, $item, $request );
	}

    /**
     * Set Where Query.
     *
     * @since 2.0.0
     *
     * @param string $where Where.
     * @param array $request {
     *      An array of query request.
     *
     *      @type string $search Search.
     *      @type array $post Post array.
     *      @type array $term Term array.
     *      @type array $tag Tag array.
     * }
     * @return string $where
     */
	public function set_query_where( $where, $request ) {
		global $wpdb;

		if ( ! empty( $request['search'] ) ) {
			$where .= " AND p.post_title LIKE '%" . addslashes( $request['search'] ) . "%'";
		}

		if ( ! empty( $request['post'] ) && is_array( $request['post'] ) ) {
			$where .= " AND p.ID IN( '" . implode( "','", $request['post'] ) . "' )";
		}

		if ( ! empty( $request['term'] ) && is_array( $request['term'] ) ) {
			$terms_include_where = array();
			$terms_exclude_where = array();

			foreach ( $request['term'] as $term_id ) {
				if ( ! empty( $term_id ) ) {
					$term_id = trim( $term_id );
					$term = get_term( absint( $term_id ) );

					if ( is_wp_error( $term ) || empty( $term ) ) {
						continue;
					}

					$req_term_id = absint( $term->term_id );

					if ( $term->taxonomy == $request['post_type'] . 'category' && $req_term_id > 0 ) {
						if ( (int) $term_id > 0 ) {
							// Include category.
							$terms_include_where[] = $wpdb->prepare( "FIND_IN_SET( %d, pd.post_category )", array( $req_term_id ) );

							// Include child for parent term.
							$children = geodir_get_term_children( $req_term_id, $term->taxonomy );

							if ( ! empty( $children ) ) {
								foreach ( $children as $id => $child_term ) {
									if ( ! empty( $child_term->count ) ) {
										$terms_include_where[] = $wpdb->prepare( "FIND_IN_SET( %d, pd.post_category )", array( $child_term->term_id ) );
									}
								}
							}
						} else {
							// Exclude category.
							$terms_exclude_where[] = $wpdb->prepare( "NOT FIND_IN_SET( %d, pd.post_category )", array( $req_term_id ) );

							// Exclude child for parent term.
							$children = geodir_get_term_children( $req_term_id, $term->taxonomy );

							if ( ! empty( $children ) ) {
								foreach ( $children as $id => $child_term ) {
									if ( ! empty( $child_term->count ) ) {
										$terms_exclude_where[] = $wpdb->prepare( "NOT FIND_IN_SET( %d, pd.post_category )", array( $child_term->term_id ) );
									}
								}
							}
						}
					} else if ( $term->taxonomy == $request['post_type'] . '_tags' ) {
						if ( (int) $term_id > 0 ) {
							// Include tag.
							$terms_include_where[] = $wpdb->prepare( "FIND_IN_SET( %s, pd.post_tags )", array( $term->name ) );
						} else {
							// Exclude tag.
							$terms_exclude_where[] = $wpdb->prepare( "NOT FIND_IN_SET( %s, pd.post_tags )", array( $term->name ) );
						}
					}
				}
			}

			if ( ! empty( $terms_include_where ) ) {
				$terms_include_where = array_unique( $terms_include_where );
				$where .= count( $terms_include_where ) > 1 ? " AND ( " . implode( " OR ", $terms_include_where ) . " )" : " AND " . implode( " OR ", $terms_include_where );
			}

			if ( ! empty( $terms_exclude_where ) ) {
				$terms_exclude_where = array_unique( $terms_exclude_where );
				$where .= count( $terms_exclude_where ) > 1 ? " AND ( " . implode( " AND ", $terms_exclude_where ) . " )" : " AND " . implode( " AND ", $terms_exclude_where );
			}
		}

		// Tags
		if ( ! empty( $request['tag'] ) && is_array( $request['tag'] ) ) {
			$tags_where = array();
			foreach ( $request['tag'] as $tag ) {
				if ( ! empty( $tag ) ) {
					$tags_where[] = $wpdb->prepare( "FIND_IN_SET( %s, pd.post_tags )", array( $tag ) );
				}
			}
			if ( ! empty( $tags_where ) ) {
				$where .= " AND ( " . implode( " OR ", $tags_where ) . " )";
			}
		}

		// locations
		global $geodirectory;

		// Private Address
		if ( GeoDir_Post_types::supports( $request['post_type'], 'private_address' ) ) {
			$single_post = 0;

			if ( ! empty( $request['post'] ) ) {
				$_single_post = is_array( $request['post'] ) ? $request['post'] : array_filter( array_map( explode( ',', $request['post'] ) ) );

				if ( is_array( $_single_post ) && count( $_single_post ) == 1 && (int) $_single_post[0] > 0 ) {
					$single_post = (int) $_single_post[0];
				}
			}

			if ( ! ( ! empty( $single_post ) && geodir_user_can( 'see_private_address', array( 'post' => $single_post ) ) ) ) {
				$where .= " AND ( `pd`.`private_address` IS NULL OR `pd`.`private_address` <> 1 ) ";
			}
		}

		if ( ! empty( $request['country'] ) ) {
			$country = $geodirectory->location->get_country_name_from_slug( $request['country'] );
			$where .= $wpdb->prepare( " AND pd.country = %s ", $country );
		}
		if ( ! empty( $request['region'] ) ) {
			$region = $geodirectory->location->get_region_name_from_slug( $request['region'] );
			$where .= $wpdb->prepare( " AND pd.region = %s ", $region );
		}
		if ( ! empty( $request['city'] ) ) {
			$city = $geodirectory->location->get_city_name_from_slug( $request['city'] );
			$where .= $wpdb->prepare( " AND pd.city = %s ", $city );
		}
		if ( ! empty( $request['neighbourhood'] ) ) {
			$neighbourhood = sanitize_title( $request['neighbourhood'] );
			$where .= $wpdb->prepare( " AND pd.neighbourhood = %s ", $neighbourhood );
		}

		// Limited to area
		if ( ! empty( $request['lat'] ) && ! empty( $request['lon'] ) ) {
			$latitude = filter_var( $request['lat'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
			$longitude = filter_var( $request['lon'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );

			if ( ! empty( $request['dist'] ) ) {
				$between = geodir_get_between_latlon( $latitude, $longitude, $request['dist'] );
			} else {
				$between = geodir_get_between_latlon( $latitude, $longitude );
			}
			$where .= $wpdb->prepare( " AND pd.latitude BETWEEN %f AND %f", $between['lat1'], $between['lat2'] );
			$where .= $wpdb->prepare( " AND pd.longitude BETWEEN %f AND %f", $between['lon1'], $between['lon2'] );
		}

		return $where;
	}

	/**
	 * Retrieves the makers.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return array|WP_Error Array on success, or WP_Error object on failure.
	 */
	public function get_marker_item( $request ) {
		$id = ! empty( $request['id'] ) ? (int)$request['id'] : 0;

		$response		  = array();
		$response['html'] = $id > 0 ? $this->marker_content( $id ) : '';

		return $response;
	}

    /**
     * Get marker content.
     *
     * @since 2.0.0
     *
     * @param int $id Id.
     * @return string $content.
     */
	public function marker_content( $id ) {
		$content = GeoDir_Maps::marker_popup_content( $id );

		return $content;
	}

	/**
	 * Checks if a given request has access to read and manage markers.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool True if the request has read access for the item, otherwise false.
	 */
	public function get_marker_item_permissions_check( $request ) {
		return $this->show_in_rest();
	}

	public function get_map_terms_filter( $request ) {
		$tick_terms = ! empty( $request['tick_terms'] ) ? $request['tick_terms'] : '';
		ob_start();
		echo GeoDir_Maps::get_categories_filter( $request['post_type'], 0, true, 0, $request['map_canvas'], absint( $request['child_collapse'] ), $request['terms'], true, $tick_terms );
		$output = ob_get_clean();

		return $output;
	}
}
