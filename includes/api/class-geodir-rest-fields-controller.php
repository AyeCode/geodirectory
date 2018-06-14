<?php

/**
 * Core class to access fields via the REST API.
 *
 * @since 2.0.0
 *
 * @see GeoDir_REST_Fields_Controller
 */
class GeoDir_REST_Fields_Controller extends WP_REST_Controller {

    /**
     * Constructor.
     *
     * @access public
     */
    public function __construct() {
        $this->namespace = GEODIR_REST_SLUG . '/v' . GEODIR_REST_API_VERSION;
		$this->rest_base = 'fields';
		$this->object_type = 'field';
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
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => $this->get_collection_params(),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
			'args' => array(
				'id' => array(
					'description' => __( 'Unique identifier for the object.', 'geodirectory' ),
					'type'        => 'integer',
				),
			),
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
				'args'                => $get_item_args,
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );
    }

    /**
     * Checks whether a given request has permission to read fields.
     *
     * @access public
     *
     * @param WP_REST_Request $request Full details about the request.
     * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
     */
    public function get_items_permissions_check( $request ) {
        if ( ! $this->show_in_rest()) {
            return new WP_Error( 'rest_cannot_view', __( 'Sorry, you are not allowed to view fields.', 'geodirectory' ), array( 'status' => rest_authorization_required_code() ) );
        }
        return true;
    }

    /**
     * Retrieves all public countries.
     *
     * @access public
     *
     * @param WP_REST_Request $request Full details about the request.
     * @return WP_REST_Response Response object on success, or WP_Error object on failure.
     */
    public function get_items( $request ) {

        // Retrieve the list of registered collection query parameters.
        $registered = $this->get_collection_params();
        
        $args = array();

		/*
		 * This array defines mappings between public API query parameters whose
		 * values are accepted as-passed, and their internal WP_Query parameter
		 * name equivalents (some are the same). Only values which are also
		 * present in $registered will be set.
		 */
		$parameter_mappings = array(
			'post_type'      => 'post_type',
			'title'          => 'frontend_title',
			'name'           => 'htmlvar_name',
			'status'         => 'is_active',
			'order'          => 'order',
			'orderby'        => 'orderby',
			'page'           => 'paged',
			'search'         => 's',
		);

		/*
		 * For each known parameter which is both registered and present in the request,
		 * set the parameter's value on the query $args.
		 */
		foreach ( $parameter_mappings as $api_param => $wp_param ) {
			if ( isset( $registered[ $api_param ], $request[ $api_param ] ) ) {
				$args[ $wp_param ] = $request[ $api_param ];
			}
		}

		/**
		 * Filters the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for a post collection request.
		 *
		 * @since 2.0.0
		 *
		 * @param array           $args    Key value array of query var to query value.
		 * @param WP_REST_Request $request The request used.
		 */
		$args = apply_filters( "geodir_rest_fields_query", $args, $request );
		$query_args = $this->prepare_items_query( $args, $request );
		
        $items = $this->get_fields( $query_args );

        $fields = array();
        foreach ( $items as $row ) {
            if ( ! $this->check_read_permission( $row ) ) {
				continue;
			}

			$item   = $this->prepare_item_for_response( $row, $request );
            $fields[] = $this->prepare_response_for_collection( $item );
        }

        $page = (int) $query_args['paged'];
		$total_fields = 10;

		$max_pages = ceil( $total_fields / $this->items_per_page() );

		if ( $page > $max_pages && $total_fields > 0 ) {
			return new WP_Error( 'geodir_rest_field_invalid_page_number', __( 'The page number requested is larger than the number of pages available.' ), array( 'status' => 400 ) );
		}

		$response  = rest_ensure_response( $fields );

		$response->header( 'X-WP-Total', (int) $total_fields );
		$response->header( 'X-WP-TotalPages', (int) $max_pages );

		$request_params = $request->get_query_params();
		$base = add_query_arg( $request_params, rest_url( sprintf( '%s/%s', $this->namespace, $this->rest_base ) ) );

		if ( $page > 1 ) {
			$prev_page = $page - 1;

			if ( $prev_page > $max_pages ) {
				$prev_page = $max_pages;
			}

			$prev_link = add_query_arg( 'page', $prev_page, $base );
			$response->link_header( 'prev', $prev_link );
		}
		if ( $max_pages > $page ) {
			$next_page = $page + 1;
			$next_link = add_query_arg( 'page', $next_page, $base );

			$response->link_header( 'next', $next_link );
		}

		return $response;
    }

	/**
	 * Determines the allowed query_vars for a get_items() response and prepares
	 * them for WP_Query.
	 *
	 * @since 2.0.0
	 *
	 * @param array           $prepared_args Optional. Prepared WP_Query arguments. Default empty array.
	 * @param WP_REST_Request $request       Optional. Full details about the request.
	 * @return array Items query arguments.
	 */
	protected function prepare_items_query( $prepared_args = array(), $request = null ) {
		$query_args = array();

		foreach ( $prepared_args as $key => $value ) {
			/**
			 * Filters the query_vars used in get_items() for the constructed query.
			 *
			 * The dynamic portion of the hook name, `$key`, refers to the query_var key.
			 *
			 * @since 2.0.0
			 *
			 * @param string $value The query_var value.
			 */
			$query_args[ $key ] = apply_filters( "geodir_rest_field_query_var-{$key}", $value );
		}

		if ( 'post' !== $this->post_type || ! isset( $query_args['ignore_sticky_posts'] ) ) {
			$query_args['ignore_sticky_posts'] = true;
		}

		if ( isset( $query_args['orderby'] ) && isset( $request['orderby'] ) ) {
			$orderby_mappings = array(
				'id'             => 'id',
				'post_type'      => 'post_type',
				'title' 		 => 'frontend_title',
				'name' 			 => 'htmlvar_name',
				'sort_order'     => 'sort_order',
			);

			if ( isset( $orderby_mappings[ $request['orderby'] ] ) ) {
				$query_args['orderby'] = $orderby_mappings[ $request['orderby'] ];
			}
		}

		return $query_args;
	}

    /**
     * Checks if a given request has access to a field.
     *
     * @access public
     *
     * @param  WP_REST_Request $request Full details about the request.
     * @return true|WP_Error True if the request has read access for the item, otherwise false or WP_Error object.
     */
    public function get_item_permissions_check( $request ) {

        if ( ! $this->show_in_rest() ) {
            return new WP_Error( 'rest_cannot_view', __( 'Sorry, you are not allowed to view countries.' ), array( 'status' => rest_authorization_required_code() ) );
        }

        return true;
    }

	/**
	 * Retrieves a single field.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_item( $request ) {
		$field = $this->get_field( $request['id'] );
		if ( is_wp_error( $field ) ) {
			return $field;
		}

		$data     = $this->prepare_item_for_response( $field, $request );
		$response = rest_ensure_response( $data );

		return $response;
	}

    /**
     * Prepares a field object for serialization.
     *
     * @access public
     *
     * @param stdClass        $field Field data.
     * @param WP_REST_Request $request  Full details about the request.
     * @return WP_REST_Response Response object.
     */
    public function prepare_item_for_response( $field, $request ) {
        $data = $field;

        $context    = 'view';
        $data       = $this->add_additional_fields_to_object( $data, $request );
        $data       = $this->filter_response_by_context( $data, $context );

        // Wrap the data in a response object.
        $response = rest_ensure_response( $data );

        $response->add_links( $this->prepare_links( $field ) );

        /**
         * Filters a field returned from the REST API.
         *
         * @param WP_REST_Response $response The response object.
         * @param object           $field  The original field object.
         * @param WP_REST_Request  $request  Request used to generate the response.
         */
        return apply_filters( 'geodir_rest_prepare_field', $response, $field, $request );
    }

	/**
	 * Prepares links for the request.
	 *
	 * @since 2.0.0
	 *
	 * @param object $field Field object.
	 * @return array Links for the given field.
	 */
	protected function prepare_links( $field ) {
		$base = sprintf( '%s/%s', $this->namespace, $this->rest_base );

		// Entity meta.
		$links = array(
            'self'	=> array(
                'href'	=> rest_url( sprintf( '%s/%s/%d', $this->namespace, $this->rest_base, $field->id ) ),
            ),
            'collection' => array(
                'href' => rest_url( sprintf( '%s/%s', $this->namespace, $this->rest_base ) ),
            ),
        );

		return $links;
	}

    /**
     * Retrieves the field's schema, conforming to JSON Schema.
     *
     * @access public
     *
     * @return array Item schema data.
     */
    public function get_item_schema() {
        $schema = array(
            '$schema'              => 'http://json-schema.org/schema#',
            'title'                => $this->object_type,
            'type'                 => 'object',
            'properties'           => array(
                'id'              => array(
					'description' => __( 'Unique identifier for the object.' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
                'name'             => array(
					'description' => __( 'An alphabetic identifier for the object unique to its type.' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
				),
                'title'             => array(
                    'description'  => __( 'The field title.' ),
                    'type'         => 'string',
                    'context'      => array( 'view' ),
                ),
                'type'            => array(
					'description' => __( 'The post type.' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				)
            ),
        );
        return $this->add_additional_fields_schema( $schema );
    }

    /**
     * Retrieves the query params for collections.
     *
     * @access public
     *
     * @return array Collection parameters.
     */
    public function get_collection_params() {
        $query_params = array();

		$query_params['context']['default'] = 'view';

		$query_params['offset'] = array(
			'description'        => __( 'Offset the result set by a specific number of items.' ),
			'type'               => 'integer',
		);

		$query_params['order'] = array(
			'description'        => __( 'Order sort attribute ascending or descending.' ),
			'type'               => 'string',
			'default'            => 'desc',
			'enum'               => array( 'asc', 'desc' ),
		);

		$query_params['orderby'] = array(
			'description'        => __( 'Sort collection by object attribute.' ),
			'type'               => 'string',
			'default'            => 'order',
			'enum'               => array(
				'id',
				'name',
				'title',
				'order',
			),
		);

		/**
		 * Filter collection parameters for the fields controller.
		 *
		 * @since 2.0.0
		 *
		 * @param array        $query_params JSON Schema-formatted collection parameters.
		 */
		return apply_filters( "geodir_rest_field_collection_params", $query_params );
    }

    /**
     * Show in rest.
     *
     * @since 2.0.0
     *
     * @return bool
     */
    public function show_in_rest() {
        return apply_filters( 'geodir_rest_fields_show_in_rest', true, $this );
    }

	/**
     * No. of fields per page.
     *
     * @since 2.0.0
     *
     * @return bool
     */
    public function items_per_page() {
        return (int) apply_filters( 'geodir_rest_fields_per_page', 10, $this );
    }

    /**
     * Get countries.
     *
     * @since 2.0.0
     *
     * @return array $countries.
     */
	public function get_fields( $args ) {
		global $wpdb;

		$fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . GEODIR_CUSTOM_FIELDS_TABLE . " WHERE is_active = %d ORDER BY sort_order ASC, frontend_title ASC", array( 1 ) ) );

		return $fields;
	}

    /**
	 * Get the post, if the ID is valid.
	 *
	 * @since 2.0.0
	 *
	 * @param int $id Supplied ID.
	 * @return object|WP_Error Field object if ID is valid, WP_Error otherwise.
	 */
	protected function get_field( $id ) {
		global $wpdb;

		$error = new WP_Error( 'geodir_rest_field_invalid_id', __( 'Invalid field ID.' ), array( 'status' => 404 ) );
		if ( (int) $id <= 0 ) {
			return $error;
		}

		$field = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . GEODIR_CUSTOM_FIELDS_TABLE . " WHERE id = %d", array( $id ) ) );
		if ( empty( $field ) ) {
			return $error;
		}

		return $field;
	}

	/**
	 * Checks if a field can be read.
	 *
	 *
	 * @since 2.0.0
	 *
	 * @param object $field Field object.
	 * @return bool Whether the field can be read.
	 */
	public function check_read_permission( $field ) {
		return true;
	}

}
