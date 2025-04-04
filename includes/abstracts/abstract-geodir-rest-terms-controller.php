<?php
/**
 * REST API GEoDirectory Terms controller
 *
 * Handles requests to the categories endpoint.
 *
 * @author   GeoDirectory
 * @category API
 * @package  GeoDirectory/API
 * @since    2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Core class used to managed terms associated with a taxonomy via the REST API.
 *
 * @since 2.0.0
 *
 * @see Geodir_REST_Terms_Controller
 */
class Geodir_REST_Terms_Controller extends WP_REST_Terms_Controller {

	/**
	 * Taxonomy key.
	 *
	 * @var string
	 */
	protected $taxonomy;

	/**
	 * Instance of a term meta fields object.
	 *
	 * @var WP_REST_Term_Meta_Fields
	 */
	protected $meta;

	/**
	 * Column to have the terms be sorted by.
	 *
	 * @var string
	 */
	protected $sort_column;

	/**
	 * Number of terms that were found.
	 *
	 * @var int
	 */
	protected $total_terms;

	/**
	 * Constructor.
	 *
	 * @param string $taxonomy Taxonomy key.
	 */
	public function __construct( $taxonomy ) {
		$this->taxonomy = $taxonomy;
		$this->namespace = GEODIR_REST_SLUG . '/v' . GEODIR_REST_API_VERSION;
		$tax_obj = get_taxonomy( $taxonomy );
		$this->rest_base = ! empty( $tax_obj->rest_base ) ? $tax_obj->rest_base : $tax_obj->name;

		$this->meta = new WP_REST_Term_Meta_Fields( $taxonomy );
	}

	/**
	 * Registers the routes for the objects of the controller.
	 *
	 * @since 2.0.0
	 *
	 * @see register_rest_route()
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				'args' => array(
					'id' => array(
						'description' => __( 'Unique identifier for the term.' ),
						'type'        => 'integer',
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
					'args'                => array(
						'context' => $this->get_context_param( array( 'default' => 'view' ) ),
					),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'update_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
				),
			)
		);
	}

	/**
	 * Checks if a request has access to read terms in the specified taxonomy.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool|WP_Error True if the request has read access, otherwise false or WP_Error object.
	 */
	public function get_items_permissions_check( $request ) {
		$tax_obj = get_taxonomy( $this->taxonomy );
		if ( ! $tax_obj || ! $this->check_is_taxonomy_allowed( $this->taxonomy ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Retrieves terms associated with a taxonomy.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {

		// Retrieve the list of registered collection query parameters.
		$registered = $this->get_collection_params();

		/*
		 * This array defines mappings between public API query parameters whose
		 * values are accepted as-passed, and their internal WP_Query parameter
		 * name equivalents (some are the same). Only values which are also
		 * present in $registered will be set.
		 */
		$parameter_mappings = array(
			'exclude'    => 'exclude',
			'include'    => 'include',
			'order'      => 'order',
			'orderby'    => 'orderby',
			'post'       => 'post',
			'hide_empty' => 'hide_empty',
			'per_page'   => 'number',
			'search'     => 'search',
			'slug'       => 'slug',
		);

		$prepared_args = array();

		/*
		 * For each known parameter which is both registered and present in the request,
		 * set the parameter's value on the query $prepared_args.
		 */
		foreach ( $parameter_mappings as $api_param => $wp_param ) {
			if ( isset( $registered[ $api_param ], $request[ $api_param ] ) ) {
				$prepared_args[ $wp_param ] = $request[ $api_param ];
			}
		}

		if ( isset( $prepared_args['orderby'] ) && isset( $request['orderby'] ) ) {
			$orderby_mappings = array(
				'include_slugs' => 'slug__in',
			);

			if ( isset( $orderby_mappings[ $request['orderby'] ] ) ) {
				$prepared_args['orderby'] = $orderby_mappings[ $request['orderby'] ];
			}
		}

		if ( isset( $registered['offset'] ) && ! empty( $request['offset'] ) ) {
			$prepared_args['offset'] = $request['offset'];
		} else {
			$prepared_args['offset'] = ( $request['page'] - 1 ) * $prepared_args['number'];
		}

		$taxonomy_obj = get_taxonomy( $this->taxonomy );

		if ( $taxonomy_obj->hierarchical && isset( $registered['parent'], $request['parent'] ) ) {
			if ( 0 === $request['parent'] ) {
				// Only query top-level terms.
				$prepared_args['parent'] = 0;
			} else {
				if ( $request['parent'] ) {
					$prepared_args['parent'] = $request['parent'];
				}
			}
		}

		/**
		 * Filters the query arguments before passing them to get_terms().
		 *
		 * The dynamic portion of the hook name, `$this->taxonomy`, refers to the taxonomy slug.
		 *
		 * Enables adding extra arguments or setting defaults for a terms
		 * collection request.
		 *
		 * @since 2.0.0
		 *
		 * @link https://developer.wordpress.org/reference/functions/get_terms/
		 *
		 * @param array           $prepared_args Array of arguments to be
		 *                                       passed to get_terms().
		 * @param WP_REST_Request $request       The current request.
		 */
		$prepared_args = apply_filters( "rest_{$this->taxonomy}_query", $prepared_args, $request );

		if ( ! empty( $prepared_args['post'] )  ) {
			$query_result = wp_get_object_terms( $prepared_args['post'], $this->taxonomy, $prepared_args );

			// Used when calling wp_count_terms() below.
			$prepared_args['object_ids'] = $prepared_args['post'];
		} else {
			$query_result = get_terms( $this->taxonomy, $prepared_args );
		}

		$query_result = apply_filters( "geodir_rest_{$this->taxonomy}_query_result", $query_result, $prepared_args, $request );

		$count_args = $prepared_args;

		unset( $count_args['number'], $count_args['offset'] );

		$total_terms = wp_count_terms( $this->taxonomy, $count_args );

		// wp_count_terms can return a falsy value when the term has no children.
		if ( ! $total_terms ) {
			$total_terms = 0;
		}

		$response = array();

		foreach ( $query_result as $term ) {
			$data = $this->prepare_item_for_response( $term, $request );
			$response[] = $this->prepare_response_for_collection( $data );
		}

		$response = rest_ensure_response( $response );

		// Store pagination values for headers.
		$per_page = (int) $prepared_args['number'];
		$page     = ceil( ( ( (int) $prepared_args['offset'] ) / $per_page ) + 1 );

		$response->header( 'X-WP-Total', (int) $total_terms );

		$max_pages = ceil( $total_terms / $per_page );

		$response->header( 'X-WP-TotalPages', (int) $max_pages );

		$base = add_query_arg( $request->get_query_params(), rest_url( $this->namespace . '/' . $this->rest_base ) );
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
	 * Checks if a request has access to read or edit the specified term.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool|WP_Error True if the request has read access for the item, otherwise false or WP_Error object.
	 */
	public function get_item_permissions_check( $request ) {
		$term = $this->get_term( $request['id'] );
		if ( is_wp_error( $term ) ) {
			return $term;
		}

		return true;
	}

	/**
	 * Gets a single term from a taxonomy.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_item( $request ) {
		$term = $this->get_term( $request['id'] );
		if ( is_wp_error( $term ) ) {
			return $term;
		}

		$response = $this->prepare_item_for_response( $term, $request );

		return rest_ensure_response( $response );
	}

	/**
	 * Prepares links for the request.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Term $term Term object.
	 * @param WP_REST_Request $request Request object.
	 * @return array Links for the given term.
	 */
	protected function prepare_links( $term, $request = array() ) {
		$base = $this->namespace . '/' . $this->rest_base;
		$links = array(
			'self'       => array(
				'href' => rest_url( trailingslashit( $base ) . $term->term_id ),
			),
			'collection' => array(
				'href' => rest_url( $base ),
			),
			'about'      => array(
				'href' => rest_url( sprintf( '%s/taxonomies/%s', $this->namespace, $this->taxonomy ) ),
			),
		);

		if ( $term->parent ) {
			$parent_term = get_term( (int) $term->parent, $term->taxonomy );

			if ( $parent_term ) {
				$links['up'] = array(
					'href'       => rest_url( trailingslashit( $base ) . $parent_term->term_id ),
					'embeddable' => true,
				);
			}
		}

		$taxonomy_obj = get_taxonomy( $term->taxonomy );

		if ( empty( $taxonomy_obj->object_type ) ) {
			return $links;
		}

		$post_type_links = array();

		foreach ( $taxonomy_obj->object_type as $type ) {
			$post_type_object = get_post_type_object( $type );

			if ( empty( $post_type_object->show_in_rest ) ) {
				continue;
			}

			$rest_base = ! empty( $post_type_object->rest_base ) ? $post_type_object->rest_base : $post_type_object->name;
			$post_type_links[] = array(
				'href' => add_query_arg( $this->taxonomy, $term->term_id, rest_url( sprintf( '%s/%s', $this->namespace, $rest_base ) ) ),
			);
		}

		if ( ! empty( $post_type_links ) ) {
			$links['https://api.w.org/post_type'] = $post_type_links;
		}

		/**
		 * Prepares links for the request.
		 *
		 * @since 2.8.105
		 *
		 * @param array   $links Links for the given term.
		 * @param WP_Term $term Term object.
		 * @param WP_REST_Request $request Request object.
		 */
		return apply_filters( "geodir_rest_{$this->taxonomy}_item_links", $links, $term, $request );
	}

	/**
	 * Retrieves the term's schema, conforming to JSON Schema.
	 *
	 * @since 2.0.0
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => $this->taxonomy,
			'type'       => 'object',
			'properties' => array(
				'id'          => array(
					'description'  => __( 'Unique identifier for the term.' ),
					'type'         => 'integer',
					'context'      => array( 'view' ),
					'readonly'     => true,
				),
				'count'       => array(
					'description'  => __( 'Number of published posts for the term.' ),
					'type'         => 'integer',
					'context'      => array( 'view' ),
					'readonly'     => true,
				),
				'description' => array(
					'description'  => __( 'HTML description of the term.' ),
					'type'         => 'string',
					'context'      => array( 'view' ),
				),
				'link'        => array(
					'description'  => __( 'URL of the term.' ),
					'type'         => 'string',
					'format'       => 'uri',
					'context'      => array( 'view' ),
					'readonly'     => true,
				),
				'name'        => array(
					'description'  => __( 'HTML title for the term.' ),
					'type'         => 'string',
					'context'      => array( 'view' ),
					'arg_options'  => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
					'required'     => true,
				),
				'slug'        => array(
					'description'  => __( 'An alphanumeric identifier for the term unique to its type.' ),
					'type'         => 'string',
					'context'      => array( 'view' ),
					'arg_options'  => array(
						'sanitize_callback' => array( $this, 'sanitize_slug' ),
					),
				),
				'taxonomy'    => array(
					'description'  => __( 'Type attribution for the term.' ),
					'type'         => 'string',
					'enum'         => array( $this->taxonomy ),
					'context'      => array( 'view' ),
					'readonly'     => true,
				),
			),
		);

		$taxonomy = get_taxonomy( $this->taxonomy );

		if ( $taxonomy->hierarchical ) {
			$schema['properties']['parent'] = array(
				'description'  => __( 'The parent term ID.' ),
				'type'         => 'integer',
				'context'      => array( 'view' ),
			);
		}

		$schema['properties']['meta'] = $this->meta->get_field_schema();

		return $this->add_additional_fields_schema( $schema );
	}

	/**
	 * Retrieves the query params for collections.
	 *
	 * @since 2.0.0
	 *
	 * @return array Collection parameters.
	 */
	public function get_collection_params() {
		$query_params = parent::get_collection_params();
		$taxonomy = get_taxonomy( $this->taxonomy );

		$query_params['context'] = array(
			'description'       => __( 'Scope under which the request is made; determines fields present in response.' ),
			'type'              => 'string',
			'default'           => 'view',
			'enum'              => array(
				'view'
			),
		);

		$query_params['exclude'] = array(
			'description'       => __( 'Ensure result set excludes specific IDs.' ),
			'type'              => 'array',
			'items'             => array(
				'type'          => 'integer',
			),
			'default'           => array(),
		);

		$query_params['include'] = array(
			'description'       => __( 'Limit result set to specific IDs.' ),
			'type'              => 'array',
			'items'             => array(
				'type'          => 'integer',
			),
			'default'           => array(),
		);

		if ( ! $taxonomy->hierarchical ) {
			$query_params['offset'] = array(
				'description'       => __( 'Offset the result set by a specific number of items.' ),
				'type'              => 'integer',
			);
		}

		$query_params['order'] = array(
			'description'       => __( 'Order sort attribute ascending or descending.' ),
			'type'              => 'string',
			'default'           => 'asc',
			'enum'              => array(
				'asc',
				'desc',
			),
		);

		$query_params['orderby'] = array(
			'description'       => __( 'Sort collection by term attribute.' ),
			'type'              => 'string',
			'default'           => 'name',
			'enum'              => array(
				'id',
				'name',
				'slug',
				'count',
			),
		);

		$query_params['hide_empty'] = array(
			'description'       => __( 'Whether to hide terms not assigned to any posts.' ),
			'type'              => 'boolean',
			'default'           => false,
		);

		if ( $taxonomy->hierarchical ) {
			$query_params['parent'] = array(
				'description'       => __( 'Limit result set to terms assigned to a specific parent.' ),
				'type'              => 'integer',
			);
		}

		$query_params['post'] = array(
			'description'       => __( 'Limit result set to terms assigned to a specific post.' ),
			'type'              => 'integer',
			'default'           => null,
		);

		$query_params['slug'] = array(
			'description'       => __( 'Limit result set to terms with one or more specific slugs.' ),
			'type'              => 'array',
			'items'             => array(
				'type'          => 'string'
			),
		);

		/**
		 * Filter collection parameters for the terms controller.
		 *
		 * The dynamic part of the filter `$this->taxonomy` refers to the taxonomy
		 * slug for the controller.
		 *
		 * This filter registers the collection parameter, but does not map the
		 * collection parameter to an internal WP_Term_Query parameter.  Use the
		 * `rest_{$this->taxonomy}_query` filter to set WP_Term_Query parameters.
		 *
		 * @since 2.0.0
		 *
		 * @param array       $query_params JSON Schema-formatted collection parameters.
		 * @param WP_Taxonomy $taxonomy     Taxonomy object.
		 */
		return apply_filters( "rest_{$this->taxonomy}_collection_params", $query_params, $taxonomy );
	}

	/**
	 * Checks that the taxonomy is valid.
	 *
	 * @since 2.0.0
	 *
	 * @param string $taxonomy Taxonomy to check.
	 * @return bool Whether the taxonomy is allowed for REST management.
	 */
	protected function check_is_taxonomy_allowed( $taxonomy ) {
		$taxonomy_obj = get_taxonomy( $taxonomy );
		if ( $taxonomy_obj && ! empty( $taxonomy_obj->show_in_rest ) ) {
			return true;
		}
		return false;
	}
}
