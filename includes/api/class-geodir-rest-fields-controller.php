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
    public function __construct( $post_type = '' ) {
		$this->namespace = GEODIR_REST_SLUG . '/v' . GEODIR_REST_API_VERSION;
		$this->rest_base = 'fields';
		$this->object_type = 'field';

		$this->post_type    	= $post_type;
		$this->post_type_obj    = array();
		$this->post_type_slug	= '';

		if ( ! empty( $this->post_type ) ) {
			$this->post_type_obj 	= get_post_type_object( $post_type );
			if ( ! empty( $this->post_type_obj ) ) {
				$this->post_type_slug = ! empty( $this->post_type_obj->rest_base ) ? $this->post_type_obj->rest_base : $this->post_type_obj->name;
				$this->rest_base = $this->post_type_slug . '/fields';
			}
		}
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

		$schema = $this->get_item_schema();
		$get_item_args = array(
			'context'  => $this->get_context_param( array( 'default' => 'view' ) ),
		);
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
			'name'           => 'htmlvar_name',
			'post_type'      => 'post_type',
			'status'         => 'is_active',
			'post'			 => 'post',
			'package'	     => 'packages',
			'default'		 => 'is_default',
			'access'		 => 'for_admin_use', // 1 = admin, 0 = public, NULL or all = all
			'location'		 => 'show_in',
			'order'          => 'order',
			'orderby'        => 'orderby',
			'page'           => 'paged',
			'search'         => 'search',
			'per_page'		 => 'per_page',
			'country'		 => 'country',
			'region'		 => 'region',
			'city'		     => 'city',
			'neighbourhood'  => 'neighbourhood',
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



		if ( isset( $args['per_page'] ) ) {
			$args['per_page'] = absint( $args['per_page'] );
		} else {
			$args['per_page'] = $this->items_per_page();
		}

		if ( isset( $registered['offset'] ) && ! empty( $request['offset'] ) ) {
			$args['offset'] = $request['offset'];
		} else {
			$args['offset']  = $args['per_page'] > 0 ? ( $request['page'] - 1 ) * $args['per_page'] : ( $request['page'] - 1 );
		}

		if ( ! empty( $args['search'] ) ) {
			$args['search'] = '*' . $args['search'] . '*';
		}

		if ( empty( $args['packages'] ) && ! empty( $args['post'] ) && ( $package_id = geodir_get_post_package_id( $args['post'], get_post_type( $this->post_type ) ) ) ) {
			$args['packages'] = $package_id;
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

		$query_args['count_total'] = true;

        $results = $this->get_fields( $query_args );

        $fields = array();
		if ( ! empty( $results['results'] ) ) {
			foreach ( $results['results'] as $row ) {
				if ( ! $this->check_read_permission( $row ) ) {
					continue;
				}

				$item   = $this->prepare_item_for_response( $row, $request );
				$fields[] = $this->prepare_response_for_collection( $item );
			}
		}

        $page = (int) $query_args['paged'];
		$total_fields = ! empty( $results['total_rows'] ) ? $results['total_rows'] : 0;

		$max_pages = $total_fields > 0 ? ( ! empty( $args['per_page'] ) ? ceil( $total_fields / $args['per_page'] ) : 1 ) : 0;

		if ( $page > $max_pages && $total_fields > 0 ) {
			return new WP_Error( 'geodir_rest_field_invalid_page_number', __( 'The page number requested is larger than the number of pages available.', 'geodirectory' ), array( 'status' => 400 ) );
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

		if ( isset( $query_args['orderby'] ) && isset( $request['orderby'] ) ) {
			$orderby_mappings = $this->orderby_options();

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
            return new WP_Error( 'rest_cannot_view', __( 'Sorry, you are not allowed to view countries.', 'geodirectory' ), array( 'status' => rest_authorization_required_code() ) );
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
        $data   = array();
		$schema = $this->get_item_schema();

		$admin_title = trim( $field->admin_title );

		if ( ! empty( $schema['properties']['id'] ) ) {
			$data['id'] = $field->id;
		}

		if ( ! empty( $schema['properties']['type'] ) ) {
			$data['type'] = $field->post_type;
		}

		if ( ! empty( $schema['properties']['name'] ) ) {
			$data['name'] = $field->htmlvar_name;
		}

		if ( ! empty( $schema['properties']['title'] ) ) {
			$title = trim( $field->frontend_title );
			if ( empty( $title ) ) {
				$title = $admin_title;
			}

			$data['title'] = __( $title, 'geodirectory' );
		}

		if ( ! empty( $schema['properties']['admin_title'] ) ) {
			$data['admin_title'] = __( $admin_title, 'geodirectory' );
		}

		if ( ! empty( $schema['properties']['description'] ) ) {
			$description = trim( $field->frontend_desc );
			$data['description'] = ! empty( $description ) ? __( $description, 'geodirectory' ) : $description;
		}

		if ( ! empty( $schema['properties']['data_type'] ) ) {
			$data['data_type'] = $field->data_type;
		}

		if ( ! empty( $schema['properties']['field_type'] ) ) {
			$data['field_type'] = $field->field_type;
		}

		if ( ! empty( $schema['properties']['field_type_key'] ) ) {
			$data['field_type_key'] = $field->field_type_key;
		}

		if ( ! empty( $schema['properties']['decimal_point'] ) ) {
			$data['decimal_point'] = $field->data_type == 'FLOAT' ? absint( $field->decimal_point ) : '';
		}

		if ( ! empty( $schema['properties']['default_value'] ) ) {
			$data['default_value'] = $field->default_value;
		}

		if ( ! empty( $schema['properties']['placeholder'] ) ) {
			$placeholder = trim( $field->placeholder_value );
			if ( ! empty( $placeholder ) ) {
				$placeholder = __( $placeholder, 'geodirectory' );
			}
			$data['placeholder'] = $placeholder;
		}

		if ( ! empty( $schema['properties']['required'] ) ) {
			$data['required'] = (bool) $field->is_required;
		}

		if ( ! empty( $schema['properties']['required_msg'] ) ) {
			$data['required_msg'] = ! empty( $field->required_msg ) ? __( $field->required_msg, 'geodirectory' ) : '';
		}

		if ( ! empty( $schema['properties']['validation_pattern'] ) ) {
			$data['validation_pattern'] = $field->validation_pattern;
		}

		if ( ! empty( $schema['properties']['validation_msg'] ) ) {
			$data['validation_msg'] = ! empty( $field->validation_msg ) ? __( $field->validation_msg, 'geodirectory' ) : '';
		}

		if ( ! empty( $schema['properties']['option_values'] ) ) {
			$data['option_values'] = ! empty( $field->option_values ) ? stripslashes_deep( geodir_string_values_to_options( $field->option_values, true ) ) : '';
		}

		if ( ! empty( $schema['properties']['location'] ) ) {
			$data['location'] = ! empty( $field->show_in ) ? explode( ',', str_replace( array( '[', ']' ), '', $field->show_in ) ) : '';
		}

		if ( ! empty( $schema['properties']['order'] ) ) {
			$data['order'] = absint( $field->sort_order );
		}

		if ( ! empty( $schema['properties']['icon'] ) ) {
			$data['icon'] = $field->field_icon;
		}

		if ( ! empty( $schema['properties']['class'] ) ) {
			$data['class'] = $field->css_class;
		}

		if ( ! empty( $schema['properties']['default'] ) ) {
			$data['default'] = (bool) $field->is_default;
		}

		if ( ! empty( $schema['properties']['private'] ) ) {
			$data['private'] = (bool) $field->for_admin_use;
		}

		if ( ! empty( $schema['properties']['status'] ) ) {
			$data['status'] = absint( $field->is_active );
		}

		if ( ! empty( $schema['properties']['packages'] ) ) {
			$data['packages'] = ! empty( $field->packages ) ? explode( ',', $field->packages ) : '';
		}

		if ( ! empty( $schema['properties']['tab_parent'] ) ) {
			$data['tab_parent'] = $field->tab_parent;
		}

		if ( ! empty( $schema['properties']['tab_level'] ) ) {
			$data['tab_level'] = absint( $field->tab_level );
		}

		if ( ! empty( $schema['properties']['sorting'] ) ) {
			$data['sorting'] = (bool) $field->cat_sort;
		}

		if ( ! empty( $schema['properties']['searching'] ) ) {
			$data['searching'] = (bool) $field->cat_filter;
		}

		if ( ! empty( $schema['properties']['extra_fields'] ) ) {
			$data['extra_fields'] = ! empty( $field->extra_fields ) ? maybe_unserialize( $field->extra_fields ) : '';
		}

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
					'description' => __( 'The field id.', 'geodirectory' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'type'            => array(
					'description' => __( 'Field post type.', 'geodirectory' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
				),
                'name'            => array(
					'description' => __( 'Field name. A unique identifier used in the database and HTML, it MUST NOT contain spaces or special characters.', 'geodirectory' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
				),
                'title'            => array(
                    'description'  => __( 'Field title.', 'geodirectory' ),
                    'type'         => 'string',
                    'context'      => array( 'view' ),
                ),
                'admin_title'      => array(
                    'description'  => __( 'Field title for backend use.', 'geodirectory' ),
                    'type'         => 'string',
                    'context'      => array( 'view' ),
                ),
				'description'      => array(
                    'description'  => __( 'Field description displayed on add listing form.', 'geodirectory' ),
                    'type'         => 'string',
                    'context'      => array( 'view' ),
                ),
                'data_type'       => array(
					'description' => __( 'Field data type. Eg: VARCHAR, TEXT etc', 'geodirectory' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
				),
                'field_type'      => array(
					'description' => __( 'Field type. Eg: text, url, select, multiselect, radio, checkbox, etc', 'geodirectory' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
				),
                'field_type_key'  => array(
					'description' => __( 'Field type key. Eg: text, url, select, multiselect, radio, checkbox, etc', 'geodirectory' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
				),
				'decimal_point'       => array(
					'description' => __( 'The number of decimal points for float value.', 'geodirectory' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
				),
                'default_value'   => array(
					'description' => __( 'Field default value, usually blank', 'geodirectory' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
				),
                'placeholder'     => array(
					'description' => __( 'Field placeholder text.', 'geodirectory' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
				),
                'required'        => array(
					'description' => __( 'Is required field?', 'geodirectory' ),
					'type'        => 'boolean',
					'context'     => array( 'view' ),
				),
                'required_msg'    => array(
					'description' => __( 'Error message for required field.', 'geodirectory' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
				),
                'validation_pattern'    => array(
					'description' => __( 'Regex expression for HTML5 pattern validation.', 'geodirectory' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
				),
                'validation_msg'    => array(
					'description' => __( 'Validation message to show to the user if validation fails.', 'geodirectory' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
				),
				'option_values'		  => array(
					'description' => __( 'Option values for select, multiselect, radio, checkbox.', 'geodirectory' ),
					'type'        => 'array',
					'context'     => array( 'view' ),
				),
				'location'		  => array(
					'description' => __( 'Field output locations.', 'geodirectory' ),
					'type'        => 'array',
					'context'     => array( 'view' ),
				),
                'order'           => array(
					'description' => __( 'Field display order.', 'geodirectory' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
				),
				'icon'           => array(
					'description' => __( 'Field icon. Eg: "fas fa-home"', 'geodirectory' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
				),
				'class'           => array(
					'description' => __( 'Custom css class for field custom style.', 'geodirectory' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
				),
				'default'          => array(
					'description' => __( 'Is GeoDirectory default field?', 'geodirectory' ),
					'type'        => 'boolean',
					'context'     => array( 'view' ),
				),
				'private'         => array(
					'description' => __( 'Is admin use only?', 'geodirectory' ),
					'type'        => 'boolean',
					'context'     => array( 'view' ),
				),
                'status'          => array(
					'description' => __( 'Field status.', 'geodirectory' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
				),
				'packages'          => array(
					'description' => __( 'Field packages.', 'geodirectory' ),
					'type'        => 'array',
					'context'     => array( 'view' ),
				),
				'sorting'         => array(
					'description' => __( 'Show field in post sorting?', 'geodirectory' ),
					'type'        => 'boolean',
					'context'     => array( 'view' ),
				),
				'searching'       => array(
					'description' => __( 'Show field in post search?', 'geodirectory' ),
					'type'        => 'boolean',
					'context'     => array( 'view' ),
				),
				'extra_fields'       => array(
					'description' => __( 'Field extra setting', 'geodirectory' ),
					'type'        => 'array',
					'context'     => array( 'view' ),
				),
                'tab_parent'      => array(
					'description' => __( 'Field parent tab.', 'geodirectory' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
				),
                'tab_level'       => array(
					'description' => __( 'Field tab level.', 'geodirectory' ),
					'type'        => 'interer',
					'context'     => array( 'view' ),
				)
            ),
        );

		$schema = $this->add_additional_fields_schema( $schema );

        return apply_filters( 'geodir_rest_field_item_schema', $schema, $this );
    }

    /**
     * Retrieves the query params for collections.
     *
     * @access public
     *
     * @return array Collection parameters.
     */
    public function get_collection_params() {
        $query_params = parent::get_collection_params();

		$query_params['context']['default'] = 'view';

		$query_params['status'] = array(
			'description'        => __( 'Field status.', 'geodirectory' ),
			'type'               => 'string',
			'default'            => '1',
		);

		$query_params['name'] = array(
			'description'        => __( 'Filter by field key.', 'geodirectory' ),
			'type'               => 'string',
			'default'            => '',
		);

		if ( empty( $this->post_type ) ) {
			$query_params['post_type'] = array(
				'description'        => __( 'Filter by post type.', 'geodirectory' ),
				'type'               => 'string',
				'default'            => '',
			);
		}

		$query_params['post'] = array(
			'description'        => __( 'Filter by post.', 'geodirectory' ),
			'type'               => 'integer',
			'default'            => '0',
		);

		$query_params['package'] = array(
			'description'        => __( 'Filter by package.', 'geodirectory' ),
			'type'               => 'integer',
			'default'            => '0',
		);

		$query_params['default'] = array(
			'description'        => __( 'Filter default fields.', 'geodirectory' ),
			'type'               => 'string',
			'default'            => 'all',
			'enum'               => array( '1', '0', 'all' ),
		);

		$query_params['access'] = array(
			'description'        => __( 'Filter by admin use only fields.', 'geodirectory' ),
			'type'               => 'string',
			'default'            => 'all',
			'enum'               => array( '1', '0', 'all' ),
		);

		$query_params['location'] = array(
			'description'        => __( 'Filter by fields location.', 'geodirectory' ),
			'type'               => 'string',
			'default'            => 'none',
			'enum'               => array( 'detail', 'listing', 'mapbubble', 'none' ),
		);

		$query_params['order'] = array(
			'description'        => __( 'Order sort attribute ascending or descending.', 'geodirectory' ),
			'type'               => 'string',
			'default'            => 'asc',
			'enum'               => array( 'asc', 'desc' ),
		);

		$query_params['orderby'] = array(
			'description'        => __( 'Sort collection by object attribute.', 'geodirectory' ),
			'type'               => 'string',
			'default'            => 'order',
			'enum'               => array_keys( $this->orderby_options() ),
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

		$query_fields = '*';
		if ( isset( $args['count_total'] ) && $args['count_total'] ) {
			$query_fields = 'SQL_CALC_FOUND_ROWS ' . $query_fields;
		}
		$query_from = "FROM " . GEODIR_CUSTOM_FIELDS_TABLE;
		$query_where = "WHERE 1=1";

		// nicename
		if ( ! empty( $args['htmlvar_name'] ) ) {
			$query_where .= $wpdb->prepare( ' AND htmlvar_name = %s', $args['htmlvar_name'] );
		}

		if ( ! empty( $args['post_type'] ) ) {
			$query_where .= $wpdb->prepare( ' AND post_type = %s', $args['post_type'] );
		}

		if ( ! empty( $args['show_in'] ) && $args['show_in'] != 'none' ) {
			$query_where .= " AND show_in LIKE '%[" . $args['show_in'] . "]%'";
		}

		if ( ! empty( $args['packages'] ) ) {
			$query_where .= $wpdb->prepare( ' FIND_IN_SET( %d, packages )', array( $args['packages'] ) );
		}

		if ( isset( $args['is_active'] ) && $args['is_active'] !== '' ) {
			$query_where .= $wpdb->prepare( ' AND is_active = %d', (int) $args['is_active'] );
		}

		if ( isset( $args['is_default'] ) && $args['is_default'] !== '' && $args['is_default'] != 'all' ) {
			$query_where .= $wpdb->prepare( ' AND is_default = %d', (int) $args['is_default'] );
		}

		if ( isset( $args['for_admin_use'] ) && $args['for_admin_use'] !== '' && $args['for_admin_use'] !== 'all' ) {
			$query_where .= $wpdb->prepare( ' AND for_admin_use = %d', (int) $args['for_admin_use'] );
		}

		$orderby_array = array();
		if ( ! empty( $args['orderby'] ) ) {
			$orderby_array[] = $args['orderby'] . ' ' . ( ! empty( $args['order'] ) && strtolower( $args['order'] ) == 'asc' ? 'ASC' : 'DESC' );
		}
		$orderby_array[] = 'frontend_title ASC';
		$query_orderby = 'ORDER BY ' . implode( ', ', $orderby_array );

		// limit
		if ( isset( $args['per_page'] ) && $args['per_page'] > 0 ) {
			if ( $args['offset'] ) {
				$query_limit = $wpdb->prepare("LIMIT %d, %d", $args['offset'], $args['per_page']);
			} else {
				$query_limit = $wpdb->prepare( "LIMIT %d, %d", $args['per_page'] * ( $args['paged'] - 1 ), $args['per_page'] );
			}
		} else {
			$query_limit = '';
		}

		$search = '';
		if ( isset( $args['search'] ) ) {
			$search = trim( $args['search'] );
		}

		if ( $search ) {
			$leading_wild = ( ltrim($search, '*') != $search );
			$trailing_wild = ( rtrim($search, '*') != $search );
			if ( $leading_wild && $trailing_wild )
				$wild = 'both';
			elseif ( $leading_wild )
				$wild = 'leading';
			elseif ( $trailing_wild )
				$wild = 'trailing';
			else
				$wild = false;
			if ( $wild )
				$search = trim($search, '*');

			$search_columns = array();
			if ( is_numeric( $search ) ) {
				$search_columns = array( 'id' );
			} else {
				$search_columns = array( 'id', 'frontend_title', 'htmlvar_name', 'frontend_desc', 'admin_title', 'post_type' );
			}

			$query_where .= $this->get_search_sql( $search, $search_columns, $wild );
		}

		$sql = "SELECT $query_fields $query_from $query_where $query_orderby $query_limit";

		$results = $wpdb->get_results( $sql );

		$total_rows = 0;
		if ( isset( $args['count_total'] ) && $args['count_total'] ) {
			$total_rows = (int) $wpdb->get_var( 'SELECT FOUND_ROWS()' );
		}

		return array( 'results' => $results, 'total_rows' => $total_rows );
	}

	protected function get_search_sql( $string, $cols, $wild = false ) {
		global $wpdb;

		$searches = array();
		$leading_wild = ( 'leading' == $wild || 'both' == $wild ) ? '%' : '';
		$trailing_wild = ( 'trailing' == $wild || 'both' == $wild ) ? '%' : '';
		$like = $leading_wild . $wpdb->esc_like( $string ) . $trailing_wild;

		foreach ( $cols as $col ) {
			if ( 'id' == $col || 'htmlvar_name' == $col ) {
				$searches[] = $wpdb->prepare( "$col = %s", $string );
			} else {
				$searches[] = $wpdb->prepare( "$col LIKE %s", $like );
			}
		}

		return ' AND (' . implode(' OR ', $searches) . ')';
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

		$error = new WP_Error( 'geodir_rest_field_invalid_id', __( 'Invalid field ID.', 'geodirectory' ), array( 'status' => 404 ) );
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

	public function orderby_options() {
		return $orderby = array(
			'id'             => 'id',
			'type'      	 => 'post_type',
			'title' 		 => 'frontend_title',
			'name' 			 => 'htmlvar_name',
			'order'     	 => 'sort_order',
		);
	}

}
