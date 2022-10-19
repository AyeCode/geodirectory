<?php
/**
 * REST API GeoDirectory Posts controller
 *
 * Handles requests to the posts endpoint.
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
 * REST API GeoDirectory Posts controller class.
 *
 * @package GeoDirectory/API
 * @extends GeoDir_REST_Posts_Controller
 */
class GeoDir_REST_Posts_Controller extends WP_REST_Posts_Controller {

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type;

	/**
	 * Instance of a post meta fields object.
	 *
	 * @var WP_REST_Post_Meta_Fields
	 */
	protected $meta;

	/**
	 * Constructor.
	 *
	 * @param string $post_type Post type.
	 */
	public function __construct( $post_type ) {
		$this->post_type = $post_type;
		$this->namespace = GEODIR_REST_SLUG . '/v' . GEODIR_REST_API_VERSION;
		$obj = get_post_type_object( $post_type );
		$this->rest_base = ! empty( $obj->rest_base ) ? $obj->rest_base : $obj->name;
		$this->cat_taxonomy = $this->post_type . 'category';
		$this->tag_taxonomy = $this->post_type . '_tags';

		$this->meta = new WP_REST_Post_Meta_Fields( $this->post_type );
	}

	/**
	 * Registers the routes for the objects of the controller.
	 *
	 * @since 2.0.0
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
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'create_item_permissions_check' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );

		$schema = $this->get_item_schema();
		$get_item_args = array(
			'context'  => $this->get_context_param( array( 'default' => 'view' ) ),
		);
		if ( isset( $schema['properties']['password'] ) ) {
			$get_item_args['password'] = array(
				'description' => __( 'The password for the post if it is password protected.' ),
				'type'        => 'string',
			);
		}
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
			'args' => array(
				'id' => array(
					'description' => __( 'Unique identifier for the object.' ),
					'type'        => 'integer',
				),
			),
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
				'args'                => $get_item_args,
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_item' ),
				'permission_callback' => array( $this, 'update_item_permissions_check' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_item' ),
				'permission_callback' => array( $this, 'delete_item_permissions_check' ),
				'args'                => array(
					'force' => array(
						'type'        => 'boolean',
						'default'     => false,
						'description' => __( 'Whether to bypass trash and force deletion.' ),
					),
				),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );
	}

	/**
	 * Checks if a given request has access to read posts.
	 *
	 * @since 2.0.0
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function get_items_permissions_check( $request ) {

		$post_type = get_post_type_object( $this->post_type );

		if ( 'edit' === $request['context'] && ! current_user_can( $post_type->cap->edit_posts ) ) {
			return new WP_Error( 'rest_forbidden_context', __( 'Sorry, you are not allowed to edit posts in this post type.' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Retrieves a collection of posts.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {

		// Ensure a search string is set in case the orderby is set to 'relevance'.
		if ( ! empty( $request['orderby'] ) && 'relevance' === $request['orderby'] && empty( $request['search'] ) ) {
			return new WP_Error( 'rest_no_search_term_defined', __( 'You need to define a search term to order by relevance.' ), array( 'status' => 400 ) );
		}

		// Ensure an include parameter is set in case the orderby is set to 'include'.
		if ( ! empty( $request['orderby'] ) && 'include' === $request['orderby'] && empty( $request['include'] ) ) {
			return new WP_Error( 'rest_orderby_include_missing_include', __( 'You need to define an include parameter to order by include.' ), array( 'status' => 400 ) );
		}

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
			'author'         => 'author__in',
			'author_exclude' => 'author__not_in',
			'exclude'        => 'post__not_in',
			'include'        => 'post__in',
			'offset'         => 'offset',
			'order'          => 'order',
			'orderby'        => 'orderby',
			'page'           => 'paged',
			'parent'         => 'post_parent__in',
			'parent_exclude' => 'post_parent__not_in',
			'search'         => 's',
			'slug'           => 'post_name__in',
			'status'         => 'post_status',
		);

		// add location terms
		global $geodirectory;
		$location_terms = $geodirectory->location->allowed_query_variables();
		foreach($location_terms as $location_term){
			$parameter_mappings[$location_term] = $location_term;
		}


		/*
		 * For each known parameter which is both registered and present in the request,
		 * set the parameter's value on the query $args.
		 */
		foreach ( $parameter_mappings as $api_param => $wp_param ) {
			if ( isset( $registered[ $api_param ], $request[ $api_param ] ) ) {
				$args[ $wp_param ] = $request[ $api_param ];
			}
		}

		// Check for & assign any parameters which require special handling or setting.
		$args['date_query'] = array();

		// Set before into date query. Date query must be specified as an array of an array.
		if ( isset( $registered['before'], $request['before'] ) ) {
			$args['date_query'][0]['before'] = $request['before'];
		}

		// Set after into date query. Date query must be specified as an array of an array.
		if ( isset( $registered['after'], $request['after'] ) ) {
			$args['date_query'][0]['after'] = $request['after'];
		}

		// Ensure our per_page parameter overrides any provided posts_per_page filter.
		if ( isset( $registered['per_page'] ) ) {
			$args['posts_per_page'] = $request['per_page'];
		}

		if ( isset( $registered['sticky'], $request['sticky'] ) ) {
			$sticky_posts = get_option( 'sticky_posts', array() );
			if ( ! is_array( $sticky_posts ) ) {
				$sticky_posts = array();
			}
			if ( $request['sticky'] ) {
				/*
				 * As post__in will be used to only get sticky posts,
				 * we have to support the case where post__in was already
				 * specified.
				 */
				$args['post__in'] = $args['post__in'] ? array_intersect( $sticky_posts, $args['post__in'] ) : $sticky_posts;

				/*
				 * If we intersected, but there are no post ids in common,
				 * WP_Query won't return "no posts" for post__in = array()
				 * so we have to fake it a bit.
				 */
				if ( ! $args['post__in'] ) {
					$args['post__in'] = array( 0 );
				}
			} elseif ( $sticky_posts ) {
				/*
				 * As post___not_in will be used to only get posts that
				 * are not sticky, we have to support the case where post__not_in
				 * was already specified.
				 */
				$args['post__not_in'] = array_merge( $args['post__not_in'], $sticky_posts );
			}
		}

		// Force the post_type argument, since it's not a user input variable.
		$args['post_type'] = $this->post_type;

		/**
		 * Filters the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for a post collection request.
		 *
		 * @since 2.0.0
		 *
		 * @link https://developer.wordpress.org/reference/classes/wp_query/
		 *
		 * @param array           $args    Key value array of query var to query value.
		 * @param WP_REST_Request $request The request used.
		 */
		$args = apply_filters( "rest_{$this->post_type}_query", $args, $request );
		$query_args = $this->prepare_items_query( $args, $request );

		$taxonomies = wp_list_filter( get_object_taxonomies( $this->post_type, 'objects' ), array( 'show_in_rest' => true ) );

		foreach ( $taxonomies as $taxonomy ) {
			$base = $taxonomy->name;
			$tax_exclude = $base . '_exclude';

			if ( ! empty( $request[ $base ] ) ) {
				$query_args['tax_query'][] = array(
					'taxonomy'         => $taxonomy->name,
					'field'            => 'term_id',
					'terms'            => $request[ $base ],
					'include_children' => false,
				);
			}

			if ( ! empty( $request[ $tax_exclude ] ) ) {
				$query_args['tax_query'][] = array(
					'taxonomy'         => $taxonomy->name,
					'field'            => 'term_id',
					'terms'            => $request[ $tax_exclude ],
					'include_children' => false,
					'operator'         => 'NOT IN',
				);
			}
		}

		global $wp_query;

		$posts_query  = new WP_Query();
		$query_result = $posts_query->query( $query_args );

		// Allow access to all password protected posts if the context is edit.
		if ( 'edit' === $request['context'] ) {
			add_filter( 'post_password_required', '__return_false' );
		}

		$posts = array();

		foreach ( $query_result as $post ) {
			if ( ! $this->check_read_permission( $post ) ) {
				continue;
			}

			$data    = $this->prepare_item_for_response( $post, $request );
			$posts[] = $this->prepare_response_for_collection( $data );
		}

		// Reset filter.
		if ( 'edit' === $request['context'] ) {
			remove_filter( 'post_password_required', '__return_false' );
		}

		$page = (int) $query_args['paged'];
		$total_posts = $posts_query->found_posts;

		if ( $total_posts < 1 ) {
			// Out-of-bounds, run the query again without LIMIT for total count.
			unset( $query_args['paged'] );

			$count_query = new WP_Query();
			$count_query->query( $query_args );
			$total_posts = $count_query->found_posts;
		}

		$max_pages = ceil( $total_posts / (int) $posts_query->query_vars['posts_per_page'] );

		if ( $page > $max_pages && $total_posts > 0 ) {
			return new WP_Error( 'rest_post_invalid_page_number', __( 'The page number requested is larger than the number of pages available.' ), array( 'status' => 400 ) );
		}

		$response  = rest_ensure_response( $posts );

		$response->header( 'X-WP-Total', (int) $total_posts );
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
	 * Get the post, if the ID is valid.
	 *
	 * @since 4.7.2
	 *
	 * @param int $id Supplied ID.
	 * @return WP_Post|WP_Error Post object if ID is valid, WP_Error otherwise.
	 */
	protected function get_post( $id ) {
		$error = new WP_Error( 'rest_post_invalid_id', __( 'Invalid post ID.' ), array( 'status' => 404 ) );
		if ( (int) $id <= 0 ) {
			return $error;
		}

		$post = get_post( (int) $id );
		if ( empty( $post ) || empty( $post->ID ) || $this->post_type !== $post->post_type ) {
			return $error;
		}

		return $post;
	}

	/**
	 * Checks if a given request has access to read a post.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool|WP_Error True if the request has read access for the item, WP_Error object otherwise.
	 */
	public function get_item_permissions_check( $request ) {
		$post = $this->get_post( $request['id'] );
		if ( is_wp_error( $post ) ) {
			return $post;
		}

		if ( 'edit' === $request['context'] && $post && ! $this->check_update_permission( $post ) ) {
			return new WP_Error( 'rest_forbidden_context', __( 'Sorry, you are not allowed to edit this post.' ), array( 'status' => rest_authorization_required_code() ) );
		}

		if ( $post && ! empty( $request['password'] ) ) {
			// Check post password, and return error if invalid.
			if ( ! hash_equals( $post->post_password, $request['password'] ) ) {
				return new WP_Error( 'rest_post_incorrect_password', __( 'Incorrect post password.' ), array( 'status' => 403 ) );
			}
		}

		// Allow access to all password protected posts if the context is edit.
		if ( 'edit' === $request['context'] ) {
			add_filter( 'post_password_required', '__return_false' );
		}

		if ( $post ) {
			return $this->check_read_permission( $post );
		}

		return true;
	}

	/**
	 * Retrieves a single post.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_item( $request ) {
		$post = $this->get_post( $request['id'] );
		if ( is_wp_error( $post ) ) {
			return $post;
		}

		$data     = $this->prepare_item_for_response( $post, $request );
		$response = rest_ensure_response( $data );

		if ( is_post_type_viewable( get_post_type_object( $post->post_type ) ) ) {
			$response->link_header( 'alternate',  get_permalink( $post->ID ), array( 'type' => 'text/html' ) );
		}

		return $response;
	}

	/**
	 * Checks if a given request has access to create a post.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has access to create items, WP_Error object otherwise.
	 */
	public function create_item_permissions_check( $request ) {
		if ( ! empty( $request['id'] ) ) {
			return new WP_Error( 'rest_post_exists', __( 'Cannot create existing post.' ), array( 'status' => 400 ) );
		}

		$post_type = get_post_type_object( $this->post_type );

		if ( ! empty( $request['author'] ) && get_current_user_id() !== $request['author'] && ! current_user_can( $post_type->cap->edit_others_posts ) ) {
			return new WP_Error( 'rest_cannot_edit_others', __( 'Sorry, you are not allowed to create posts as this user.' ), array( 'status' => rest_authorization_required_code() ) );
		}

		if ( ! empty( $request['sticky'] ) && ! current_user_can( $post_type->cap->edit_others_posts ) ) {
			return new WP_Error( 'rest_cannot_assign_sticky', __( 'Sorry, you are not allowed to make posts sticky.' ), array( 'status' => rest_authorization_required_code() ) );
		}

		if ( ! current_user_can( $post_type->cap->create_posts ) ) {
			return new WP_Error( 'rest_cannot_create', __( 'Sorry, you are not allowed to create posts as this user.' ), array( 'status' => rest_authorization_required_code() ) );
		}

		if ( ! $this->check_assign_terms_permission( $request ) ) {
			return new WP_Error( 'rest_cannot_assign_term', __( 'Sorry, you are not allowed to assign the provided terms.' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Creates a single post.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function create_item( $request ) {
		if ( ! empty( $request['id'] ) ) {
			return new WP_Error( 'rest_post_exists', __( 'Cannot create existing post.' ), array( 'status' => 400 ) );
		}

		$prepared_post = $this->prepare_item_for_database( $request );

		if ( is_wp_error( $prepared_post ) ) {
			return $prepared_post;
		}

		$prepared_post->post_type = $this->post_type;

		$post_id = wp_insert_post( wp_slash( (array) $prepared_post ), true );

		if ( is_wp_error( $post_id ) ) {

			if ( 'db_insert_error' === $post_id->get_error_code() ) {
				$post_id->add_data( array( 'status' => 500 ) );
			} else {
				$post_id->add_data( array( 'status' => 400 ) );
			}

			return $post_id;
		}

		$post = get_post( $post_id );

		/**
		 * Fires after a single post is created or updated via the REST API.
		 *
		 * The dynamic portion of the hook name, `$this->post_type`, refers to the post type slug.
		 *
		 * @since 2.0.0
		 *
		 * @param WP_Post         $post     Inserted or updated post object.
		 * @param WP_REST_Request $request  Request object.
		 * @param bool            $creating True when creating a post, false when updating.
		 */
		do_action( "rest_insert_{$this->post_type}", $post, $request, true );

		$schema = $this->get_item_schema();

		if ( ! empty( $schema['properties']['sticky'] ) ) {
			if ( ! empty( $request['sticky'] ) ) {
				stick_post( $post_id );
			} else {
				unstick_post( $post_id );
			}
		}

		if ( ! empty( $schema['properties']['featured_media'] ) && isset( $request['featured_media'] ) ) {
			$this->handle_featured_media( $request['featured_media'], $post_id );
		}

		if ( ! empty( $schema['properties']['format'] ) && ! empty( $request['format'] ) ) {
			set_post_format( $post, $request['format'] );
		}

		$terms_update = $this->handle_terms( $post_id, $request );

		if ( is_wp_error( $terms_update ) ) {
			return $terms_update;
		}

		if ( ! empty( $schema['properties']['meta'] ) && isset( $request['meta'] ) ) {
			$meta_update = $this->meta->update_value( $request['meta'], $post_id );

			if ( is_wp_error( $meta_update ) ) {
				return $meta_update;
			}
		}

		$post = get_post( $post_id );
		$fields_update = $this->update_additional_fields_for_object( $post, $request );

		if ( is_wp_error( $fields_update ) ) {
			return $fields_update;
		}

		$request->set_param( 'context', 'edit' );

		$response = $this->prepare_item_for_response( $post, $request );
		$response = rest_ensure_response( $response );

		$response->set_status( 201 );
		$response->header( 'Location', rest_url( sprintf( '%s/%s/%d', $this->namespace, $this->rest_base, $post_id ) ) );

		return $response;
	}

	/**
	 * Checks if a given request has access to update a post.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has access to update the item, WP_Error object otherwise.
	 */
	public function update_item_permissions_check( $request ) {
		$post = $this->get_post( $request['id'] );
		if ( is_wp_error( $post ) ) {
			return $post;
		}

		$post_type = get_post_type_object( $this->post_type );

		if ( $post && ! $this->check_update_permission( $post ) ) {
			return new WP_Error( 'rest_cannot_edit', __( 'Sorry, you are not allowed to edit this post.' ), array( 'status' => rest_authorization_required_code() ) );
		}

		if ( ! empty( $request['author'] ) && get_current_user_id() !== $request['author'] && ! current_user_can( $post_type->cap->edit_others_posts ) ) {
			return new WP_Error( 'rest_cannot_edit_others', __( 'Sorry, you are not allowed to update posts as this user.' ), array( 'status' => rest_authorization_required_code() ) );
		}

		if ( ! empty( $request['sticky'] ) && ! current_user_can( $post_type->cap->edit_others_posts ) ) {
			return new WP_Error( 'rest_cannot_assign_sticky', __( 'Sorry, you are not allowed to make posts sticky.' ), array( 'status' => rest_authorization_required_code() ) );
		}

		if ( ! $this->check_assign_terms_permission( $request ) ) {
			return new WP_Error( 'rest_cannot_assign_term', __( 'Sorry, you are not allowed to assign the provided terms.' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Updates a single post.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_item( $request ) {
		$valid_check = $this->get_post( $request['id'] );
		if ( is_wp_error( $valid_check ) ) {
			return $valid_check;
		}

		$post = $this->prepare_item_for_database( $request );

		if ( is_wp_error( $post ) ) {
			return $post;
		}

		// convert the post object to an array, otherwise wp_update_post will expect non-escaped input.
		$post_id = wp_update_post( wp_slash( (array) $post ), true );

		if ( is_wp_error( $post_id ) ) {
			if ( 'db_update_error' === $post_id->get_error_code() ) {
				$post_id->add_data( array( 'status' => 500 ) );
			} else {
				$post_id->add_data( array( 'status' => 400 ) );
			}
			return $post_id;
		}

		$post = get_post( $post_id );

		/** This action is documented in includes/api/class-geodir-rest-posts-controller.php */
		do_action( "rest_insert_{$this->post_type}", $post, $request, false );

		$schema = $this->get_item_schema();

		if ( ! empty( $schema['properties']['format'] ) && ! empty( $request['format'] ) ) {
			set_post_format( $post, $request['format'] );
		}

		if ( ! empty( $schema['properties']['featured_media'] ) && isset( $request['featured_media'] ) ) {
			$this->handle_featured_media( $request['featured_media'], $post_id );
		}

		if ( ! empty( $schema['properties']['sticky'] ) && isset( $request['sticky'] ) ) {
			if ( ! empty( $request['sticky'] ) ) {
				stick_post( $post_id );
			} else {
				unstick_post( $post_id );
			}
		}

		$terms_update = $this->handle_terms( $post->ID, $request );

		if ( is_wp_error( $terms_update ) ) {
			return $terms_update;
		}

		if ( ! empty( $schema['properties']['meta'] ) && isset( $request['meta'] ) ) {
			$meta_update = $this->meta->update_value( $request['meta'], $post->ID );

			if ( is_wp_error( $meta_update ) ) {
				return $meta_update;
			}
		}

		$post = get_post( $post_id );
		$fields_update = $this->update_additional_fields_for_object( $post, $request );

		if ( is_wp_error( $fields_update ) ) {
			return $fields_update;
		}

		$request->set_param( 'context', 'edit' );

		$response = $this->prepare_item_for_response( $post, $request );

		return rest_ensure_response( $response );
	}

	/**
	 * Checks if a given request has access to delete a post.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has access to delete the item, WP_Error object otherwise.
	 */
	public function delete_item_permissions_check( $request ) {
		$post = $this->get_post( $request['id'] );
		if ( is_wp_error( $post ) ) {
			return $post;
		}

		if ( $post && ! $this->check_delete_permission( $post ) ) {
			return new WP_Error( 'rest_cannot_delete', __( 'Sorry, you are not allowed to delete this post.' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Deletes a single post.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function delete_item( $request ) {
		$post = $this->get_post( $request['id'] );
		if ( is_wp_error( $post ) ) {
			return $post;
		}

		$id    = $post->ID;
		$force = (bool) $request['force'];

		$supports_trash = ( EMPTY_TRASH_DAYS > 0 );

		/**
		 * Filters whether a post is trashable.
		 *
		 * The dynamic portion of the hook name, `$this->post_type`, refers to the post type slug.
		 *
		 * Pass false to disable trash support for the post.
		 *
		 * @since 2.0.0
		 *
		 * @param bool    $supports_trash Whether the post type support trashing.
		 * @param WP_Post $post           The Post object being considered for trashing support.
		 */
		$supports_trash = apply_filters( "rest_{$this->post_type}_trashable", $supports_trash, $post );

		if ( ! $this->check_delete_permission( $post ) ) {
			return new WP_Error( 'rest_user_cannot_delete_post', __( 'Sorry, you are not allowed to delete this post.' ), array( 'status' => rest_authorization_required_code() ) );
		}

		$request->set_param( 'context', 'edit' );


		// If we're forcing, then delete permanently.
		if ( $force ) {
			$previous = $this->prepare_item_for_response( $post, $request );
			$result = wp_delete_post( $id, true );
			$response = new WP_REST_Response();
			$response->set_data( array( 'deleted' => true, 'previous' => $previous->get_data() ) );
		} else {
			// If we don't support trashing for this type, error out.
			if ( ! $supports_trash ) {
				/* translators: %s: force=true */
				return new WP_Error( 'rest_trash_not_supported', sprintf( __( "The post does not support trashing. Set '%s' to delete." ), 'force=true' ), array( 'status' => 501 ) );
			}

			// Otherwise, only trash if we haven't already.
			if ( 'trash' === $post->post_status ) {
				return new WP_Error( 'rest_already_trashed', __( 'The post has already been deleted.' ), array( 'status' => 410 ) );
			}

			// (Note that internally this falls through to `wp_delete_post` if
			// the trash is disabled.)
			$result = wp_trash_post( $id );
			$post = get_post( $id );
			$response = $this->prepare_item_for_response( $post, $request );
		}

		if ( ! $result ) {
			return new WP_Error( 'rest_cannot_delete', __( 'The post cannot be deleted.' ), array( 'status' => 500 ) );
		}

		/**
		 * Fires immediately after a single post is deleted or trashed via the REST API.
		 *
		 * They dynamic portion of the hook name, `$this->post_type`, refers to the post type slug.
		 *
		 * @since 2.0.0
		 *
		 * @param object           $post     The deleted or trashed post.
		 * @param WP_REST_Response $response The response data.
		 * @param WP_REST_Request  $request  The request sent to the API.
		 */
		do_action( "rest_delete_{$this->post_type}", $post, $response, $request );

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
			$query_args[ $key ] = apply_filters( "rest_query_var-{$key}", $value );
		}

		if ( 'post' !== $this->post_type || ! isset( $query_args['ignore_sticky_posts'] ) ) {
			$query_args['ignore_sticky_posts'] = true;
		}

		// Map to proper WP_Query orderby param.
		if ( isset( $query_args['orderby'] ) && isset( $request['orderby'] ) ) {
			$orderby_mappings = array(
				'id'            => 'ID',
				'include'       => 'post__in',
				'slug'          => 'post_name',
			);

			if ( isset( $orderby_mappings[ $request['orderby'] ] ) ) {
				$query_args['orderby'] = $orderby_mappings[ $request['orderby'] ];
			}
		}

		return $query_args;
	}

	/**
	 * Checks the post_date_gmt or modified_gmt and prepare any post or
	 * modified date for single post output.
	 *
	 * @since 2.0.0
	 *
	 * @param string      $date_gmt GMT publication time.
	 * @param string|null $date     Optional. Local publication time. Default null.
	 * @return string|null ISO8601/RFC3339 formatted datetime.
	 */
	protected function prepare_date_response( $date_gmt, $date = null ) {
		// Use the date if passed.
		if ( isset( $date ) ) {
			return mysql_to_rfc3339( $date );
		}

		// Return null if $date_gmt is empty/zeros.
		if ( '0000-00-00 00:00:00' === $date_gmt ) {
			return null;
		}

		// Return the formatted datetime.
		return mysql_to_rfc3339( $date_gmt );
	}

	/**
	 * Prepares a single post for create or update.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return stdClass|WP_Error Post object or WP_Error.
	 */
	protected function prepare_item_for_database( $request ) {
		$prepared_post = (object) $request->get_params();

		// Post ID.
		if ( isset( $request['id'] ) ) {
			$existing_post = $this->get_post( $request['id'] );
			if ( is_wp_error( $existing_post ) ) {
				return $existing_post;
			}

			$prepared_post->ID = $existing_post->ID;
		}

		$schema = $this->get_item_schema();

		// Post title.
		if ( ! empty( $schema['properties']['title'] ) && isset( $request['title'] ) ) {
			if ( is_string( $request['title'] ) ) {
				$prepared_post->post_title = $request['title'];
			} elseif ( ! empty( $request['title']['raw'] ) ) {
				$prepared_post->post_title = $request['title']['raw'];
			}
		}

		// Post content.
		if ( ! empty( $schema['properties']['content'] ) && isset( $request['content'] ) ) {
			if ( is_string( $request['content'] ) ) {
				$prepared_post->post_content = $request['content'];
			} elseif ( isset( $request['content']['raw'] ) ) {
				$prepared_post->post_content = $request['content']['raw'];
			}
		}

		// Post excerpt.
		if ( ! empty( $schema['properties']['excerpt'] ) && isset( $request['excerpt'] ) ) {
			if ( is_string( $request['excerpt'] ) ) {
				$prepared_post->post_excerpt = $request['excerpt'];
			} elseif ( isset( $request['excerpt']['raw'] ) ) {
				$prepared_post->post_excerpt = $request['excerpt']['raw'];
			}
		}

		$prepared_post->tax_input = array();

		// Post tags
		if ( ! empty( $schema['properties']['post_tags'] ) && isset( $request['post_tags'] ) ) {
			if ( is_string( $request['post_tags'] ) ) {
				$post_tags = $request['post_tags'];
			} elseif ( isset( $request['post_tags']['raw'] ) ) {
				$post_tags = $request['post_tags']['raw'];
			} else {
				$post_tags = $request['post_tags'];
			}

			$prepared_post->tax_input[ $this->post_type . '_tags' ] = $post_tags;
		}

		// Post categories
		if ( ! empty( $schema['properties']['post_category'] ) && isset( $request['post_category'] ) ) {
			if ( is_string( $request['post_category'] ) ) {
				$post_category = $request['post_category'];
			} elseif ( isset( $request['post_category']['raw'] ) ) {
				$post_category = $request['post_category']['raw'];
			} else {
				$post_category = $request['post_category'];
			}

			$prepared_post->tax_input[ $this->post_type . 'category' ] = $post_category;
		}

		// Post type.
		if ( empty( $request['id'] ) ) {
			// Creating new post, use default type for the controller.
			$prepared_post->post_type = $this->post_type;
		} else {
			// Updating a post, use previous type.
			$prepared_post->post_type = get_post_type( $request['id'] );
		}

		$post_type = get_post_type_object( $prepared_post->post_type );

		// Post status.
		if ( ! empty( $schema['properties']['status'] ) && isset( $request['status'] ) ) {
			$status = $this->handle_status_param( $request['status'], $post_type );

			if ( is_wp_error( $status ) ) {
				return $status;
			}

			$prepared_post->post_status = $status;
		}

		// Post date.
		if ( ! empty( $schema['properties']['date'] ) && ! empty( $request['date'] ) ) {
			$date_data = rest_get_date_with_gmt( $request['date'] );

			if ( ! empty( $date_data ) ) {
				list( $prepared_post->post_date, $prepared_post->post_date_gmt ) = $date_data;
				$prepared_post->edit_date = true;
			}
		} elseif ( ! empty( $schema['properties']['date_gmt'] ) && ! empty( $request['date_gmt'] ) ) {
			$date_data = rest_get_date_with_gmt( $request['date_gmt'], true );

			if ( ! empty( $date_data ) ) {
				list( $prepared_post->post_date, $prepared_post->post_date_gmt ) = $date_data;
				$prepared_post->edit_date = true;
			}
		}

		// Post slug.
		if ( ! empty( $schema['properties']['slug'] ) && isset( $request['slug'] ) ) {
			$prepared_post->post_name = $request['slug'];
		}

		// Author.
		if ( ! empty( $schema['properties']['author'] ) && ! empty( $request['author'] ) ) {
			$post_author = (int) $request['author'];

			if ( get_current_user_id() !== $post_author ) {
				$user_obj = get_userdata( $post_author );

				if ( ! $user_obj ) {
					return new WP_Error( 'rest_invalid_author', __( 'Invalid author ID.' ), array( 'status' => 400 ) );
				}
			}

			$prepared_post->post_author = $post_author;
		}

		// Post password.
		if ( ! empty( $schema['properties']['password'] ) && isset( $request['password'] ) ) {
			$prepared_post->post_password = $request['password'];

			if ( '' !== $request['password'] ) {
				if ( ! empty( $schema['properties']['sticky'] ) && ! empty( $request['sticky'] ) ) {
					return new WP_Error( 'rest_invalid_field', __( 'A post can not be sticky and have a password.' ), array( 'status' => 400 ) );
				}

				if ( ! empty( $prepared_post->ID ) && is_sticky( $prepared_post->ID ) ) {
					return new WP_Error( 'rest_invalid_field', __( 'A sticky post can not be password protected.' ), array( 'status' => 400 ) );
				}
			}
		}

		if ( ! empty( $schema['properties']['sticky'] ) && ! empty( $request['sticky'] ) ) {
			if ( ! empty( $prepared_post->ID ) && post_password_required( $prepared_post->ID ) ) {
				return new WP_Error( 'rest_invalid_field', __( 'A password protected post can not be set to sticky.' ), array( 'status' => 400 ) );
			}
		}

		// Parent.
		if ( ! empty( $schema['properties']['parent'] ) && isset( $request['parent'] ) ) {
			if ( 0 === (int) $request['parent'] ) {
				$prepared_post->post_parent = 0;
			} else {
				$parent = get_post( (int) $request['parent'] );
				if ( empty( $parent ) ) {
					return new WP_Error( 'rest_post_invalid_id', __( 'Invalid post parent ID.' ), array( 'status' => 400 ) );
				}
				$prepared_post->post_parent = (int) $parent->ID;
			}
		}

		// Comment status.
		if ( ! empty( $schema['properties']['comment_status'] ) && ! empty( $request['comment_status'] ) ) {
			$prepared_post->comment_status = $request['comment_status'];
		}

		// Ping status.
		if ( ! empty( $schema['properties']['ping_status'] ) && ! empty( $request['ping_status'] ) ) {
			$prepared_post->ping_status = $request['ping_status'];
		}

		/**
		 * Filters a post before it is inserted via the REST API.
		 *
		 * The dynamic portion of the hook name, `$this->post_type`, refers to the post type slug.
		 *
		 * @since 2.0.0
		 *
		 * @param stdClass        $prepared_post An object representing a single post prepared
		 *                                       for inserting or updating the database.
		 * @param WP_REST_Request $request       Request object.
		 */
		return apply_filters( "rest_pre_insert_{$this->post_type}", $prepared_post, $request );

	}

	/**
	 * Determines validity and normalizes the given status parameter.
	 *
	 * @since 2.0.0
	 *
	 * @param string $post_status Post status.
	 * @param object $post_type   Post type.
	 * @return string|WP_Error Post status or WP_Error if lacking the proper permission.
	 */
	protected function handle_status_param( $post_status, $post_type ) {

		switch ( $post_status ) {
			case 'draft':
			case 'pending':
				break;
			case 'private':
				if ( ! current_user_can( $post_type->cap->publish_posts ) ) {
					return new WP_Error( 'rest_cannot_publish', __( 'Sorry, you are not allowed to create private posts in this post type.' ), array( 'status' => rest_authorization_required_code() ) );
				}
				break;
			case 'publish':
			case 'future':
				if ( ! current_user_can( $post_type->cap->publish_posts ) ) {
					return new WP_Error( 'rest_cannot_publish', __( 'Sorry, you are not allowed to publish posts in this post type.' ), array( 'status' => rest_authorization_required_code() ) );
				}
				break;
			default:
				if ( ! get_post_status_object( $post_status ) ) {
					$post_status = 'draft';
				}
				break;
		}

		return $post_status;
	}

	/**
	 * Determines the featured media based on a request param.
	 *
	 * @since 2.0.0
	 *
	 * @param int $featured_media Featured Media ID.
	 * @param int $post_id        Post ID.
	 * @return bool|WP_Error Whether the post thumbnail was successfully deleted, otherwise WP_Error.
	 */
	protected function handle_featured_media( $featured_media, $post_id ) {

		$featured_media = (int) $featured_media;
		if ( $featured_media ) {
			$result = set_post_thumbnail( $post_id, $featured_media );
			if ( $result ) {
				return true;
			} else {
				return new WP_Error( 'rest_invalid_featured_media', __( 'Invalid featured media ID.' ), array( 'status' => 400 ) );
			}
		} else {
			return delete_post_thumbnail( $post_id );
		}

	}

	/**
	 * Updates the post's terms from a REST request.
	 *
	 * @since 2.0.0
	 *
	 * @param int             $post_id The post ID to update the terms form.
	 * @param WP_REST_Request $request The request object with post and terms data.
	 * @return null|WP_Error WP_Error on an error assigning any of the terms, otherwise null.
	 */
	protected function handle_terms( $post_id, $request ) {
		$taxonomies = wp_list_filter( get_object_taxonomies( $this->post_type, 'objects' ), array( 'show_in_rest' => true ) );

		foreach ( $taxonomies as $taxonomy ) {
			$base = $taxonomy->name;

			if ( ! isset( $request[ $base ] ) ) {
				continue;
			}

			$result = wp_set_object_terms( $post_id, $request[ $base ], $taxonomy->name );

			if ( is_wp_error( $result ) ) {
				return $result;
			}
		}
	}

	/**
	 * Checks whether current user can assign all terms sent with the current request.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_REST_Request $request The request object with post and terms data.
	 * @return bool Whether the current user can assign the provided terms.
	 */
	protected function check_assign_terms_permission( $request ) {
		$taxonomies = wp_list_filter( get_object_taxonomies( $this->post_type, 'objects' ), array( 'show_in_rest' => true ) );
		foreach ( $taxonomies as $taxonomy ) {
			$base = $taxonomy->name;

			if ( ! isset( $request[ $base ] ) ) {
				continue;
			}

			foreach ( $request[ $base ] as $term_id ) {
				// Invalid terms will be rejected later.
				if ( ! get_term( $term_id, $taxonomy->name ) ) {
					continue;
				}

				if ( ! current_user_can( 'assign_term', (int) $term_id ) ) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Checks if a given post type can be viewed or managed.
	 *
	 * @since 2.0.0
	 *
	 * @param object|string $post_type Post type name or object.
	 * @return bool Whether the post type is allowed in REST.
	 */
	protected function check_is_post_type_allowed( $post_type ) {
		if ( ! is_object( $post_type ) ) {
			$post_type = get_post_type_object( $post_type );
		}

		if ( ! empty( $post_type ) && ! empty( $post_type->show_in_rest ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Checks if a post can be read.
	 *
	 * Correctly handles posts with the inherit status.
	 *
	 * @since 2.0.0
	 *
	 * @param object $post Post object.
	 * @return bool Whether the post can be read.
	 */
	public function check_read_permission( $post ) {
		$post_type = get_post_type_object( $post->post_type );
		if ( ! $this->check_is_post_type_allowed( $post_type ) ) {
			return false;
		}

		// Is the post readable?
		if ( 'publish' === $post->post_status || current_user_can( $post_type->cap->read_post, $post->ID ) ) {
			return true;
		}

		$post_status_obj = get_post_status_object( $post->post_status );
		if ( $post_status_obj && $post_status_obj->public ) {
			return true;
		}

		// Can we read the parent if we're inheriting?
		if ( 'inherit' === $post->post_status && $post->post_parent > 0 ) {
			$parent = get_post( $post->post_parent );
			if ( $parent ) {
				return $this->check_read_permission( $parent );
			}
		}

		/*
		 * If there isn't a parent, but the status is set to inherit, assume
		 * it's published (as per get_post_status()).
		 */
		if ( 'inherit' === $post->post_status ) {
			return true;
		}

		return false;
	}

	/**
	 * Checks if a post can be edited.
	 *
	 * @since 2.0.0
	 *
	 * @param object $post Post object.
	 * @return bool Whether the post can be edited.
	 */
	protected function check_update_permission( $post ) {
		$post_type = get_post_type_object( $post->post_type );

		if ( ! $this->check_is_post_type_allowed( $post_type ) ) {
			return false;
		}

		return current_user_can( $post_type->cap->edit_post, $post->ID );
	}

	/**
	 * Checks if a post can be created.
	 *
	 * @since 2.0.0
	 *
	 * @param object $post Post object.
	 * @return bool Whether the post can be created.
	 */
	protected function check_create_permission( $post ) {
		$post_type = get_post_type_object( $post->post_type );

		if ( ! $this->check_is_post_type_allowed( $post_type ) ) {
			return false;
		}

		return current_user_can( $post_type->cap->create_posts );
	}

	/**
	 * Checks if a post can be deleted.
	 *
	 * @since 2.0.0
	 *
	 * @param object $post Post object.
	 * @return bool Whether the post can be deleted.
	 */
	protected function check_delete_permission( $post ) {
		$post_type = get_post_type_object( $post->post_type );

		if ( ! $this->check_is_post_type_allowed( $post_type ) ) {
			return false;
		}

		return current_user_can( $post_type->cap->delete_post, $post->ID );
	}

	/**
	 * Prepares a single post output for response.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post         $the_post    Post object.
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public function prepare_item_for_response( $the_post, $request ) {
		global $gd_post, $post;

		if ( isset( $the_post->default_category ) ) {
			$post = get_post( $the_post->ID );
			$gd_post = $the_post;
		} else {
			$post = $the_post;
			$gd_post = geodir_get_post_info( $the_post->ID );
		}
		geodir_setup_postdata( $gd_post );

		$schema = $this->get_item_schema();

		$data = $this->get_post_data( $gd_post, $request, $post );

		// Default fields
		if ( ! empty( $schema['properties']['parent'] ) ) {
			$data['parent'] = (int) $gd_post->post_parent;
		}

		if ( ! empty( $schema['properties']['comment_status'] ) ) {
			$data['comment_status'] = $gd_post->comment_status;
		}

		if ( ! empty( $schema['properties']['ping_status'] ) ) {
			$data['ping_status'] = $gd_post->ping_status;
		}

		if ( ! empty( $schema['properties']['sticky'] ) ) {
			$data['sticky'] = is_sticky( $gd_post->ID );
		}

		if ( ! empty( $schema['properties']['format'] ) ) {
			$data['format'] = get_post_format( $gd_post->ID );

			// Fill in blank post format.
			if ( empty( $data['format'] ) ) {
				$data['format'] = 'standard';
			}
		}

		if ( ! empty( $schema['properties']['meta'] ) ) {
			$data['meta'] = $this->meta->get_value( $gd_post->ID, $request );
		}

		if ( ! empty( $schema['properties']['guid'] ) ) {
			$data['guid'] = array(
				/** This filter is documented in wp-includes/post-template.php */
				'rendered' => apply_filters( 'get_the_guid', $gd_post->guid, $post->ID ),
				'raw'      => $gd_post->guid,
			);
		}

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );

		$response->add_links( $this->prepare_links( $post ) );

		/**
		 * Filters the post data for a response.
		 *
		 * The dynamic portion of the hook name, `$this->post_type`, refers to the post type slug.
		 *
		 * @since 2.0.0
		 *
		 * @param WP_REST_Response $response The response object.
		 * @param WP_Post          $post     Post object.
		 * @param WP_REST_Request  $request  Request object.
		 */
		return apply_filters( "rest_prepare_{$this->post_type}", $response, $post, $request );
	}

	/**
	 * Overwrites the default protected title format.
	 *
	 * By default, WordPress will show password protected posts with a title of
	 * "Protected: %s", as the REST API communicates the protected status of a post
	 * in a machine readable format, we remove the "Protected: " prefix.
	 *
	 * @since 2.0.0
	 *
	 * @return string Protected title format.
	 */
	public function protected_title_format() {
		return '%s';
	}

	/**
	 * Prepares links for the request.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post $post Post object.
	 * @return array Links for the given post.
	 */
	protected function prepare_links( $post ) {
		$base = sprintf( '%s/%s', $this->namespace, $this->rest_base );

		// Entity meta.
		$links = array(
			'self' => array(
				'href'   => rest_url( trailingslashit( $base ) . $post->ID ),
			),
			'collection' => array(
				'href'   => rest_url( $base ),
			),
			'about'      => array(
				'href'   => rest_url( $this->namespace . '/types/' . $this->post_type ),
			),
		);

		if ( ( post_type_supports( $post->post_type, 'author' ) )
			&& ! empty( $post->post_author ) ) {
			$links['author'] = array(
				'href'       => rest_url( 'wp/v2/users/' . $post->post_author ),
				'embeddable' => true,
			);
		}

		if ( post_type_supports( $post->post_type, 'comments' ) ) {
			$replies_url = rest_url( $this->namespace . '/reviews' );
			$replies_url = add_query_arg( 'post', $post->ID, $replies_url );

			$links['replies'] = array(
				'href'       => $replies_url,
				'embeddable' => true,
			);
		}

		if ( post_type_supports( $post->post_type, 'revisions' ) ) {
			$links['version-history'] = array(
				'href' => rest_url( trailingslashit( $base ) . $post->ID . '/revisions' ),
			);
		}

		$post_type_obj = get_post_type_object( $post->post_type );

		if ( $post_type_obj->hierarchical && ! empty( $post->post_parent ) ) {
			$links['up'] = array(
				'href'       => rest_url( trailingslashit( $base ) . (int) $post->post_parent ),
				'embeddable' => true,
			);
		}

		// If we have a featured media, add that.
		if ( $featured_media = get_post_thumbnail_id( $post->ID ) ) {
			$image_url = rest_url( 'wp/v2/media/' . $featured_media );

			$links['https://api.w.org/featuredmedia'] = array(
				'href'       => $image_url,
				'embeddable' => true,
			);
		}

		if ( ! in_array( $post->post_type, array( 'attachment', 'nav_menu_item', 'revision' ), true ) ) {
			$attachments_url = rest_url( 'wp/v2/media' );
			$attachments_url = add_query_arg( 'parent', $post->ID, $attachments_url );

			$links['https://api.w.org/attachment'] = array(
				'href' => $attachments_url,
			);
		}

		$taxonomies = get_object_taxonomies( $post->post_type );

		if ( ! empty( $taxonomies ) ) {
			$links['https://api.w.org/term'] = array();

			foreach ( $taxonomies as $tax ) {
				$taxonomy_obj = get_taxonomy( $tax );

				// Skip taxonomies that are not public.
				if ( empty( $taxonomy_obj->show_in_rest ) ) {
					continue;
				}

				$tax_base = $taxonomy_obj->rest_base;

				$terms_url = add_query_arg(
					'post',
					$post->ID,
					rest_url( $this->namespace . '/' . $tax_base )
				);

				$links['https://api.w.org/term'][] = array(
					'href'       => $terms_url,
					'taxonomy'   => $tax,
					'embeddable' => true,
				);
			}
		}

		return $links;
	}

	/**
	 * Retrieves the post's schema, conforming to JSON Schema.
	 *
	 * @since 2.0.0
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {

		$post_type_obj = get_post_type_object( $this->post_type );

		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => $this->post_type,
			'type'       => 'object',
			// Base properties for every Post.
			'properties' => array(
				'id'              => array(
					'description' => __( 'Unique identifier for the object.' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				)
			),
		);
		
		$custom_fields_schema = $this->get_custom_fields_schema();
		if ( ! empty( $custom_fields_schema ) ) {
			$schema['properties'] = array_merge( $schema['properties'], $custom_fields_schema );
		}
		
		/*
		if ( post_type_supports( $this->post_type, 'title' ) ) {
			$schema['properties']['title'] = array(
				'description' => __( 'The title for the object.' ),
				'type'        => 'object',
				'context'     => array( 'view', 'edit' ),
				'arg_options' => array(
					'sanitize_callback' => null, // Note: sanitization implemented in self::prepare_item_for_database()
					'validate_callback' => null, // Note: validation implemented in self::prepare_item_for_database()
				),
				'properties'  => array(
					'raw' => array(
						'description' => __( 'Title for the object, as it exists in the database.' ),
						'type'        => 'string',
						'context'     => array( 'edit' ),
					),
					'rendered' => array(
						'description' => __( 'HTML title for the object, transformed for display.' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
				),
			);
		} */

		/*
		if ( post_type_supports( $this->post_type, 'editor' ) ) {
			$schema['properties']['content'] = array(
				'description' => __( 'The content for the object.' ),
				'type'        => 'object',
				'context'     => array( 'view', 'edit' ),
				'arg_options' => array(
					'sanitize_callback' => null, // Note: sanitization implemented in self::prepare_item_for_database()
					'validate_callback' => null, // Note: validation implemented in self::prepare_item_for_database()
				),
				'properties'  => array(
					'raw' => array(
						'description' => __( 'Content for the object, as it exists in the database.' ),
						'type'        => 'string',
						'context'     => array( 'edit' ),
					),
					'rendered' => array(
						'description' => __( 'HTML content for the object, transformed for display.' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'protected'       => array(
						'description' => __( 'Whether the content is protected with a password.' ),
						'type'        => 'boolean',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
				),
			);
		}*/
		
		$taxonomies = wp_list_filter( get_object_taxonomies( $this->post_type, 'objects' ), array( 'show_in_rest' => true ) );

		foreach ( $taxonomies as $taxonomy ) {
			if ( ( $taxonomy->name == $this->post_type . 'category' || $taxonomy->name == $this->post_type . '_tags' ) && ! empty( $taxonomy->rest_base ) ) {
				if ( isset( $query_params['properties'][ $taxonomy->rest_base ] ) ) {
					unset( $query_params['properties'][ $taxonomy->rest_base ] );
				}
				
				if ( isset( $query_params['properties'][ $taxonomy->rest_base . '_exclude' ] ) ) {
					unset( $query_params['properties'][ $taxonomy->rest_base . '_exclude' ] );
				}
			}

			if ( $taxonomy->name == $this->post_type . 'category' ) {
				$schema['properties'][ $this->post_type . 'category' ] = array(
					'description' => __( 'List of categories.', 'geodirectory' ),
					'type'        => 'array',
					'context'     => array( 'view', 'edit' ),
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'id' => array(
								'description' => __( 'Category ID.', 'geodirectory' ),
								'type'        => 'integer',
								'context'     => array( 'view', 'edit' ),
							),
							'name' => array(
								'description' => __( 'Category name.', 'geodirectory' ),
								'type'        => 'string',
								'context'     => array( 'view', 'edit' ),
								'readonly'    => true,
							),
							'slug' => array(
								'description' => __( 'Category slug.', 'geodirectory' ),
								'type'        => 'string',
								'context'     => array( 'view', 'edit' ),
								'readonly'    => true,
							),
						),
					),
				);
			} else if ( $taxonomy->name == $this->post_type . '_tags' ) {
				$schema['properties'][ $this->post_type . '_tags' ] = array(
					'description' => __( 'List of tags.', 'geodirectory' ),
					'type'        => 'array',
					'context'     => array( 'view', 'edit' ),
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'id' => array(
								'description' => __( 'Tag ID.', 'geodirectory' ),
								'type'        => 'integer',
								'context'     => array( 'view', 'edit' ),
							),
							'name' => array(
								'description' => __( 'Tag name.', 'geodirectory' ),
								'type'        => 'string',
								'context'     => array( 'view', 'edit' ),
								'readonly'    => true,
							),
							'slug' => array(
								'description' => __( 'Tag slug.', 'geodirectory' ),
								'type'        => 'string',
								'context'     => array( 'view', 'edit' ),
								'readonly'    => true,
							),
						),
					),
				);
			} else {
				$schema['properties'][ $taxonomy->name ] = array(
					/* translators: %s: taxonomy name */
					'description' => sprintf( __( 'The terms assigned to the object in the %s taxonomy.' ), $taxonomy->name ),
					'type'        => 'array',
					'items'       => array(
						'type'    => 'integer',
					),
					'context'     => array( 'view', 'edit' ),
				);
			}
		}
		
		unset($schema['properties'][ $this->post_type . '_category' ]);
		unset($schema['properties'][ $this->post_type . '_tags' ]);
		
		$schema['properties']['slug'] = array(
			'description' => __( 'An alphanumeric identifier for the object unique to its type.' ),
			'type'        => 'string',
			'context'     => array( 'view', 'edit' ),
			'arg_options' => array(
				'sanitize_callback' => array( $this, 'sanitize_slug' ),
			),
		);

		$schema['properties']['type'] = array(
			'description' => __( 'Type of Post for the object.' ),
			'type'        => 'string',
			'context'     => array( 'view', 'edit' ),
			'readonly'    => true,
		);

		$schema['properties']['status'] = array(
			'description' => __( 'A named status for the object.' ),
			'type'        => 'string',
			'enum'        => array_keys( get_post_stati( array( 'internal' => false ) ) ),
			'context'     => array( 'view', 'edit' ),
		);

		if ( post_type_supports( $this->post_type, 'author' ) ) {
			$schema['properties']['author'] = array(
				'description' => __( 'The ID for the author of the object.' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' ),
			);
		}
		$schema['properties']['date'] = array(
			'description' => __( "The date the object was published, in the site's timezone." ),
			'type'        => 'string',
			'format'      => 'date-time',
			'context'     => array( 'view', 'edit' ),
		);

		$schema['properties']['date_gmt'] = array(
			'description' => __( 'The date the object was published, as GMT.' ),
			'type'        => 'string',
			'format'      => 'date-time',
			'context'     => array( 'view', 'edit' ),
		);

		$schema['properties']['modified'] = array(
			'description' => __( "The date the object was last modified, in the site's timezone." ),
			'type'        => 'string',
			'format'      => 'date-time',
			'context'     => array( 'view', 'edit' ),
			'readonly'    => true,
		);

		$schema['properties']['modified_gmt'] = array(
			'description' => __( 'The date the object was last modified, as GMT.' ),
			'type'        => 'string',
			'format'      => 'date-time',
			'context'     => array( 'view', 'edit' ),
			'readonly'    => true,
		);

		if ( post_type_supports( $this->post_type, 'thumbnail' ) ) {
			$schema['properties']['featured_media'] = array(
				'description' => __( 'The ID of the featured media for the object.' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' ),
			);
		}
		
		/*
		$schema['properties']['images'] = array(
			'description' => __( 'List of images.', 'geodirectory' ),
			'type'        => 'object',
			'context'     => array( 'view', 'edit' ),
			'items'       => array(
				'type'       => 'object',
				'properties' => array(
					'id' => array(
						'description' => __( 'Image ID.', 'geodirectory' ),
						'type'        => 'integer',
						'context'     => array( 'view', 'edit' ),
					),
					'title' => array(
						'description' => __( 'Image title.', 'geodirectory' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
					),
					'src' => array(
						'description' => __( 'Image URL.', 'geodirectory' ),
						'type'        => 'string',
						'format'      => 'uri',
						'context'     => array( 'view', 'edit' ),
					),
					'thumbnail' => array(
						'description' => __( 'Thumbnail URL.', 'geodirectory' ),
						'type'        => 'string',
						'format'      => 'uri',
						'context'     => array( 'view', 'edit' ),
					),
					'position' => array(
						'description' => __( 'Image position. 0 means that the image is featured.', 'geodirectory' ),
						'type'        => 'integer',
						'context'     => array( 'view', 'edit' ),
					),
					'featured' => array(
						'description' => __( "Image is featured.", 'geodirectory' ),
						'type'        => 'boolean',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
				),
			),
		);
		*/

		if ( post_type_supports( $this->post_type, 'custom-fields' ) ) {
			$schema['properties']['meta'] = $this->meta->get_field_schema();
		}

		$schema['properties']['link'] = array(
			'description' => __( 'URL to the object.' ),
			'type'        => 'string',
			'format'      => 'uri',
			'context'     => array( 'view', 'edit' ),
			'readonly'    => true,
		);

		if ( post_type_supports( $this->post_type, 'comments' ) ) {
			$schema['properties']['comment_status'] = array(
				'description' => __( 'Whether or not comments are open on the object.' ),
				'type'        => 'string',
				'enum'        => array( 'open', 'closed' ),
				'context'     => array( 'view', 'edit' ),
			);
			$schema['properties']['ping_status'] = array(
				'description' => __( 'Whether or not the object can be pinged.' ),
				'type'        => 'string',
				'enum'        => array( 'open', 'closed' ),
				'context'     => array( 'view', 'edit' ),
			);
		}

		$schema['properties']['guid'] = array(
			'description' => __( 'The globally unique identifier for the object.' ),
			'type'        => 'object',
			'context'     => array( 'view', 'edit' ),
			'readonly'    => true,
			'properties'  => array(
				'raw'      => array(
					'description' => __( 'GUID for the object, as it exists in the database.' ),
					'type'        => 'string',
					'context'     => array( 'edit' ),
					'readonly'    => true,
				),
				'rendered' => array(
					'description' => __( 'GUID for the object, transformed for display.' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
			),
		);

		$schema['properties']['password'] = array(
			'description' => __( 'A password to protect access to the content and excerpt.' ),
			'type'        => 'string',
			'context'     => array( 'edit' ),
		);
		
		if ( $post_type_obj->hierarchical ) {
			$schema['properties']['parent'] = array(
				'description' => __( 'The ID for the parent of the object.' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' ),
			);
		}
		
		$post_type_attributes = array(
			'title',
			'editor',
			'author',
			'excerpt',
			'revisions',
		);

		foreach ( $post_type_attributes as $attribute ) {
			if ( isset( $schema['properties'][ $attribute ] ) && ! post_type_supports( $this->post_type, $attribute ) ) {
				unset( $schema['properties'][ $attribute ] );
			}
		}

		return $this->add_additional_fields_schema( $schema );
	}

	/**
	 * Retrieves the query params for the posts collection.
	 *
	 * @since 2.0.0
	 *
	 * @return array Collection parameters.
	 */
	public function get_collection_params() {
		$query_params = parent::get_collection_params();

		$query_params['context']['default'] = 'view';

		$taxonomies = wp_list_filter( get_object_taxonomies( $this->post_type, 'objects' ), array( 'show_in_rest' => true ) );

		foreach ( $taxonomies as $taxonomy ) {
			if ( ( $taxonomy->name == $this->post_type . 'category' || $taxonomy->name == $this->post_type . '_tags' ) && ! empty( $taxonomy->rest_base ) ) {
				if ( isset( $query_params[ $taxonomy->rest_base ] ) ) {
					unset( $query_params[ $taxonomy->rest_base ] );
				}
				
				if ( isset( $query_params[ $taxonomy->rest_base . '_exclude' ] ) ) {
					unset( $query_params[ $taxonomy->rest_base . '_exclude' ] );
				}
			}

			$query_params[ $taxonomy->name ] = array(
				/* translators: %s: taxonomy name */
				'description'       => sprintf( __( 'Limit result set to all items that have the specified term assigned in the %s taxonomy.' ), $taxonomy->name ),
				'type'              => 'array',
				'items'             => array(
					'type'          => 'integer',
				),
				'default'           => array(),
			);

			$query_params[ $taxonomy->name . '_exclude' ] = array(
				/* translators: %s: taxonomy name */
				'description' => sprintf( __( 'Limit result set to all items except those that have the specified term assigned in the %s taxonomy.' ), $taxonomy->name ),
				'type'        => 'array',
				'items'       => array(
					'type'    => 'integer',
				),
				'default'           => array(),
			);
		}

		$query_params['status'] = array(
			'default'           => 'publish',
			'description'       => __( 'Limit result set to posts assigned one or more statuses.' ),
			'type'              => 'array',
			'items'             => array(
				'enum'          => array_merge( array_keys( get_post_stati() ), array( 'any' ) ),
				'type'          => 'string',
			),
			'sanitize_callback' => array( $this, 'sanitize_post_statuses' ),
		);

		if ( post_type_supports( $this->post_type, 'author' ) ) {
			$query_params['author'] = array(
				'description'         => __( 'Limit result set to posts assigned to specific authors.' ),
				'type'                => 'array',
				'items'               => array(
					'type'            => 'integer',
				),
				'default'             => array(),
			);
			$query_params['author_exclude'] = array(
				'description'         => __( 'Ensure result set excludes posts assigned to specific authors.' ),
				'type'                => 'array',
				'items'               => array(
					'type'            => 'integer',
				),
				'default'             => array(),
			);
		}

		$query_params['after'] = array(
			'description'        => __( 'Limit response to posts published after a given ISO8601 compliant date.' ),
			'type'               => 'string',
			'format'             => 'date-time',
		);

		$query_params['before'] = array(
			'description'        => __( 'Limit response to posts published before a given ISO8601 compliant date.' ),
			'type'               => 'string',
			'format'             => 'date-time',
		);

		$query_params['include'] = array(
			'description'        => __( 'Limit result set to specific IDs.' ),
			'type'               => 'array',
			'items'              => array(
				'type'           => 'integer',
			),
			'default'            => array(),
		);

		$query_params['exclude'] = array(
			'description'        => __( 'Ensure result set excludes specific IDs.' ),
			'type'               => 'array',
			'items'              => array(
				'type'           => 'integer',
			),
			'default'            => array(),
		);

		$query_params['offset'] = array(
			'description'        => __( 'Offset the result set by a specific number of items.' ),
			'type'               => 'integer',
		);

		$default_order = 'desc';
		$default_orderby = 'post_date';
		$orderby_options = array();

		$sort_options  = geodir_rest_post_sort_options( $this->post_type );
		if ( ! empty( $sort_options ) ) {
			if ( ! empty( $sort_options['default_order'] ) ) {
				$default_order = $sort_options['default_order'];
			}
			if ( ! empty( $sort_options['default_orderby'] ) ) {
				$default_orderby = $sort_options['default_orderby'];
			}
			if ( ! empty( $sort_options['orderby_options'] ) ) {
				$orderby_options = array_keys( $sort_options['orderby_options'] );
			}
		}
		if ( $default_orderby != 'random' ) {
			$default_orderby = $default_orderby . '_' . $default_order;
		}
		if ( ! in_array( $default_orderby, $orderby_options ) ) {
			$orderby_options[] = $default_orderby;
		}

		$query_params['order'] = array(
			'description'        => __( 'Order sort attribute ascending or descending.' ),
			'type'               => 'string',
			'default'            => $default_order,
			'enum'               => array( 'asc', 'desc' ),
		);

		$query_params['orderby'] = array(
			'description'        => __( 'Sort collection by object attribute.' ),
			'type'               => 'string',
			'default'            => $default_orderby,
			'enum'               => $orderby_options,
		);

		$post_type = get_post_type_object( $this->post_type );

		if ( $post_type->hierarchical ) {
			$query_params['parent'] = array(
				'description'       => __( 'Limit result set to items with particular parent IDs.' ),
				'type'              => 'array',
				'items'             => array(
					'type'          => 'integer',
				),
				'default'           => array(),
			);
			$query_params['parent_exclude'] = array(
				'description'       => __( 'Limit result set to all items except those of a particular parent ID.' ),
				'type'              => 'array',
				'items'             => array(
					'type'          => 'integer',
				),
				'default'           => array(),
			);
		}

		$query_params['slug'] = array(
			'description'       => __( 'Limit result set to posts with one or more specific slugs.' ),
			'type'              => 'array',
			'items'             => array(
				'type'          => 'string',
			),
			'sanitize_callback' => 'wp_parse_slug_list',
		);

		/**
		 * Filter collection parameters for the posts controller.
		 *
		 * The dynamic part of the filter `$this->post_type` refers to the post
		 * type slug for the controller.
		 *
		 * This filter registers the collection parameter, but does not map the
		 * collection parameter to an internal WP_Query parameter. Use the
		 * `rest_{$this->post_type}_query` filter to set WP_Query parameters.
		 *
		 * @since 2.0.0
		 *
		 * @param array        $query_params JSON Schema-formatted collection parameters.
		 * @param WP_Post_Type $post_type    Post type object.
		 */
		return apply_filters( "rest_{$this->post_type}_collection_params", $query_params, $post_type );
	}

	/**
	 * Sanitizes and validates the list of post statuses, including whether the
	 * user can query private statuses.
	 *
	 * @since 2.0.0
	 *
	 * @param  string|array    $statuses  One or more post statuses.
	 * @param  WP_REST_Request $request   Full details about the request.
	 * @param  string          $parameter Additional parameter to pass to validation.
	 * @return array|WP_Error A list of valid statuses, otherwise WP_Error object.
	 */
	public function sanitize_post_statuses( $statuses, $request, $parameter ) {
		$statuses = wp_parse_slug_list( $statuses );

		// The default status is different in WP_REST_Attachments_Controller
		$attributes = $request->get_attributes();
		$default_status = $attributes['args']['status']['default'];

		foreach ( $statuses as $status ) {
			if ( $status === $default_status ) {
				continue;
			}

			$post_type_obj = get_post_type_object( $this->post_type );

			if ( current_user_can( $post_type_obj->cap->edit_posts ) ) {
				$result = rest_validate_request_arg( $status, $request, $parameter );
				if ( is_wp_error( $result ) ) {
					return $result;
				}
			} else {
				return new WP_Error( 'rest_forbidden_status', __( 'Status is forbidden.' ), array( 'status' => rest_authorization_required_code() ) );
			}
		}

		return $statuses;
	}

    /**
     * Get custom fields schema.
     *
     * @since 2.0.0
     *
     * @param string $package_id Optional. Package id. Default null.
     * @param string $default Optional. Default. Default all.
     * @return array $schema.
     */
	public function get_custom_fields_schema( $package_id = '', $default = 'all' ) {
		global $geodirectory;
        $custom_fields  = geodir_post_custom_fields( $package_id, $default, $this->post_type );
        
        $schema = array();
        
        foreach ( $custom_fields as $id => $field ) {
            $admin_use              = (bool)$field['for_admin_use'];
            
            if ( $admin_use ) {
                continue;
            }
            
            $name                   = $field['htmlvar_name'];
            $data_type              = $field['data_type'];
            $field_type             = $field['field_type'];
            $title                  = $field['frontend_title'] ? stripslashes( __( $field['frontend_title'], 'geodirectory' ) ) : stripslashes( __( $field['admin_title'], 'geodirectory' ) );
            $description            = stripslashes( __( $field['desc'], 'geodirectory' ) );
            $required               = $field['is_required'];
            $default                = $field['default'];
            $extra_fields           = !empty( $field['extra_fields'] ) ? stripslashes_deep( maybe_unserialize( $field['extra_fields'] ) ) : NULL;
            $options                = !empty( $field['option_values'] ) ? stripslashes_deep( $field['options'] ) : array();
            $rendered_options       = !empty( $field['option_values'] ) ? stripslashes_deep( geodir_string_values_to_options( $field['option_values'], true ) ) : array();
            $enum                   = $rendered_options ? geodir_rest_get_enum_values( $rendered_options ) : array();
            $arg_options            = array( 
                'validate_callback' => 'geodir_rest_validate_request_arg' 
            );
            
            $args                   = array();
            $args['type']           = 'string';
            $args['context']        = array( 'view', 'edit' );
            $args['title']          = $title;
            $args['description']    = !empty( $description ) ? $description : $title;
            $args['required']       = (bool)$required;
            if ( $name == 'package_id' ) {
                $default = (int) $package_id > 0 ? (int) $package_id : (int) geodir_get_post_package_id( array(), $this->post_type );
            }
            if ( (bool)$required || $default !== '' ) {
                $args['default']    = $default;
            }
            
            $continue = false;
            
            switch ( $field_type ) {
                case 'address':
                    $name       		= 'street';
                    $default_location   = $geodirectory->location->get_default_location();
                    $country    		= !empty( $default_location->country ) ? $default_location->country : '';
                    $region     		= !empty( $default_location->region ) ? $default_location->region : '';
                    $city       		= !empty( $default_location->city ) ? $default_location->city : '';
                    $latitude   		= !empty( $default_location->latitude ) ? $default_location->latitude : '';
                    $longitude  		= !empty( $default_location->longitude ) ? $default_location->longitude : '';
                    
                    $schema[ $name ] 	= $args;

					$schema[ 'country' ] = array(
						'type'          => 'string',
						'field_type'   => $field_type,
						'data_type'    => $data_type,
						'context'       => array( 'view', 'edit' ),
						'title'         => !empty( $extra_fields['country_lable'] ) ? __( $extra_fields['country_lable'], 'geodirectory' ) : __( 'Country', 'geodirectory' ),
						'description'   => __( 'Choose a country', 'geodirectory' ),
						'required'      => (bool) ( $required && !empty( $extra_fields['show_region'] ) ),
						'default'       => $country,
						'extra_fields'  => array(
							'show' => (bool) ! empty( $extra_fields['show_country'] )
						)
					);

					$schema[ 'region' ] = array(
						'type'          => 'string',
						'field_type'   => $field_type,
						'data_type'    => $data_type,
						'context'       => array( 'view', 'edit' ),
						'title'         => !empty( $extra_fields['region_lable'] ) ? __( $extra_fields['region_lable'], 'geodirectory' ) : __( 'Region', 'geodirectory' ),
						'description'   => __( 'Choose a region', 'geodirectory' ),
						'required'      => (bool) ( $required && !empty( $extra_fields['show_region'] ) ),
						'default'       => $region,
						'extra_fields'  => array(
							'show' => (bool) ! empty( $extra_fields['show_region'] )
						)
					);

					$schema[ 'city' ] = array(
						'type'          => 'string',
						'field_type'   => $field_type,
						'data_type'    => $data_type,
						'context'       => array( 'view', 'edit' ),
						'title'         => !empty( $extra_fields['city_lable'] ) ? __( $extra_fields['city_lable'], 'geodirectory' ) : __( 'City', 'geodirectory' ),
						'description'   => __( 'Choose a city', 'geodirectory' ),
						'required'      => (bool) ( $required && !empty( $extra_fields['show_city'] ) ),
						'default'       => $city,
						'extra_fields'  => array(
							'show' => (bool) ! empty( $extra_fields['show_city'] )
						)
					);
				
					$schema[ 'zip' ] = array(
						'type'          => 'string',
						'field_type'   => $field_type,
						'data_type'    => $data_type,
						'context'       => array( 'view', 'edit' ),
						'title'         => !empty( $extra_fields['zip_lable'] ) ? __( $extra_fields['zip_lable'], 'geodirectory' ) : __( 'Zip/Post Code', 'geodirectory' ),
						'description'   => __( 'Zip/Post Code', 'geodirectory' ),
						'required'      => (bool) ( ! empty( $extra_fields['zip_required'] ) && ! empty( $extra_fields['show_zip'] ) ),
						'extra_fields'  => array(
							'show' => (bool) ! empty( $extra_fields['show_zip'] )
						)
					);
				
					$schema[ 'map' ] = array(
						'type'          => 'string',
						'field_type'   => $field_type,
						'data_type'    => $data_type,
						'context'       => array( 'view', 'edit' ),
						'title'         => !empty( $extra_fields['map_lable'] ) ? __( $extra_fields['map_lable'], 'geodirectory' ) : __( 'Map', 'geodirectory' ),
						'description'   => __( 'Click on "Set Address on Map" and then you can also drag pinpoint to locate the correct address', 'geodirectory' ),
						'readonly'      => true,
						'extra_fields'  => array(
							'show' => (bool) ! empty( $extra_fields['show_map'] )
						)
					);
				
					$schema[ 'latitude' ] = array(
						'type'          => 'string',
						'field_type'   => $field_type,
						'data_type'    => $data_type,
						'context'       => array( 'view', 'edit' ),
						'title'         => __( 'Address Latitude', 'geodirectory' ),
						'description'   => __( 'Please enter latitude for google map perfection. eg. : <b>39.955823048131286</b>', 'geodirectory' ),
						'required'      => (bool) ( $required && !empty( $extra_fields['show_latlng'] ) ),
						'default'       => $latitude,
						'readonly'      => empty( $extra_fields['show_latlng'] ) ? true : false,
						'extra_fields'  => array(
							'show' => (bool) ! empty( $extra_fields['show_latlng'] )
						)
					);
					
					$schema[ 'longitude' ] = array(
						'type'          => 'string',
						'field_type'   => $field_type,
						'data_type'    => $data_type,
						'context'       => array( 'view', 'edit' ),
						'title'         => __( 'Address Longitude', 'geodirectory' ),
						'description'   => __( 'Please enter longitude for google map perfection. eg. : <b>-75.14408111572266</b>', 'geodirectory' ),
						'required'      => (bool) ( $required && !empty( $extra_fields['show_latlng'] ) ),
						'default'       => $longitude,
						'readonly'      => empty( $extra_fields['show_latlng'] ) ? true : false,
						'extra_fields'  => array(
							'show' => (bool) ! empty( $extra_fields['show_latlng'] )
						)
					);
                    
					$schema[ 'mapview' ] = array(
						'type'          => 'string',
						'field_type'   => $field_type,
						'data_type'    => $data_type,
						'context'       => array( 'view', 'edit' ),
						'title'         => !empty( $extra_fields['mapview_lable'] ) ? __( $extra_fields['mapview_lable'], 'geodirectory' ) : __( 'Map View', 'geodirectory' ),
						'default'       => 'ROADMAP',
						'enum'          => array( 'ROADMAP', 'SATELLITE', 'HYBRID', 'TERRAIN' ),
						'extra_fields'  => array(
							'show' => (bool) ! empty( $extra_fields['show_mapview'] )
						)
					);
                    
					$schema[ 'mapzoom' ] = array(
						'type'          => 'string',
						'field_type'   => $field_type,
						'data_type'    => $data_type,
						'context'       => array( 'view', 'edit' ),
						'title'         => __( 'Map Zoom', 'geodirectory' ),
						'readonly'      => true,
						'extra_fields'  => array(
							'show' => (bool) ! empty( $extra_fields['show_mapzoom'] )
						)
					);
                    break;
				case 'business_hours':
                    $args['type']       = 'string';
                    $args['properties'] = array(
                        'raw' => array(
                            'description' => __( 'Field for the object, as it exists in the database.' ),
                            'type'        => 'string',
                            'context'     => array( 'view', 'edit' ),
                        ),
                        'rendered' => array(
                            'description' => __( 'Field for the object, transformed for display.' ),
                            'type'        => 'string',
                            'context'     => array( 'view', 'edit' ),
                            'readonly'    => true,
                        ),
                    );
                    break;
                case 'checkbox':
                    $args['type']   = geodir_rest_data_type_to_field_type( $data_type );
                    break;
                case 'datepicker':
                    $args['type']       = 'string';
                    $args['format']     = 'date-time';
                    $args['properties'] = array(
                        'raw' => array(
                            'description' => __( 'Date for the object, as it exists in the database.' ),
                            'type'        => 'string',
                            'context'     => array( 'view', 'edit' ),
                        ),
                        'rendered' => array(
                            'description' => __( 'Date for the object, transformed for display.' ),
                            'type'        => 'string',
                            'context'     => array( 'view', 'edit' ),
                            'readonly'    => true,
                        ),
                    );
                    $arg_options['date_format'] = 'Y-m-d';
                    break;
                case 'email':
                    $args['format'] = 'email';
                    break;
                case 'fieldset':
                    $args['readonly'] = true;
                    break;
                case 'file':
                    $args['type']       = 'object';
                    $args['properties'] = array(
                        'raw' => array(
                            'description' => __( 'File for the object, as it exists in the database.' ),
                            'type'        => 'string',
                            'context'     => array( 'view', 'edit' ),
                        ),
                        'rendered' => array(
                            'description' => __( 'File for the object, transformed for display.' ),
                            'type'        => 'string',
                            'context'     => array( 'view', 'edit' ),
                            'readonly'    => true,
                        ),
                    );
                    
                    if ( !empty( $extra_fields['gd_file_types'] ) ) {
                        $arg_options['file_types'] = $extra_fields['gd_file_types'];
                    }
                    break;
                case 'html':
                    $args['type']       = 'string';
                    $args['format']     = 'html-field';
                    $args['properties'] = array(
                        'raw' => array(
                            'description' => __( 'Content for the object, as it exists in the database.' ),
                            'type'        => 'string',
                            'context'     => array( 'view', 'edit' ),
                        ),
                        'rendered' => array(
                            'description' => __( 'Content for the object, transformed for display.' ),
                            'type'        => 'string',
                            'context'     => array( 'view', 'edit' ),
                            'readonly'    => true,
                        ),
                    );

					$arg_options        = array( 
						'sanitize_callback' => 'geodir_rest_sanitize_request_arg' 
					);
                    break;
				case 'images' :
					$args['type']       = 'strings';
					break;
                case 'multiselect':
                    $args['type']       = 'array';
                    $args['enum']       = $enum;
                    $args['items']      = array( 'type' => 'string' );
                    $args['properties'] = array(
                        'raw' => array(
                            'description' => __( 'Field for the object, as it exists in the database.' ),
                            'type'        => 'string',
                            'context'     => array( 'view', 'edit' ),
                        ),
                        'rendered' => array(
                            'description' => __( 'Field for the object, transformed for display.' ),
                            'type'        => 'string',
                            'context'     => array( 'view', 'edit' ),
                            'readonly'    => true,
                        ),
                    );
                    $arg_options['display_type']        = !empty( $extra_fields ) ? $extra_fields : 'select';
                    break;
                case 'radio':
                    $args['type']           = 'string';
                    $args['enum']           = $enum;
                    $args['items']          = array( 'type' => 'string' );
                    break;
                case 'select':
                    $args['type']       = 'string';
                    $args['enum']       = $enum;
                    $args['items']      = array( 'type' => 'string' );
                    $args['properties'] = array(
                        'raw' => array(
                            'description' => __( 'Field for the object, as it exists in the database.' ),
                            'type'        => 'string',
                            'context'     => array( 'view', 'edit' ),
                        ),
                        'rendered' => array(
                            'description' => __( 'Field for the object, transformed for display.' ),
                            'type'        => 'string',
                            'context'     => array( 'view', 'edit' ),
                            'readonly'    => true,
                        ),
                    );
                    break;
                case 'tags':
					$args['type']   = 'string';
				break;
				case 'categories':
					$args['type']   = 'array';
					$args['items']  = array( 'type' => 'integer' );

					$schema[ 'default_category' ] = array(
						'title'		  => __( 'Default category.' ),
						'description' => __( 'Select default category.' ),
						'type'        => 'integer',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
						'field_type'  => $field_type,
						'data_type'   => 'TEXT'
					);
				break;
                case 'phone':
                case 'text':
					if ( $name == 'post_title' ) {
						if ( post_type_supports( $this->post_type, 'title' ) ) {
							$name 					= 'title';
							$args['type'] 			= 'string';
							/*
							$args['arg_options'] 	= array(
								'sanitize_callback' => null, // Note: sanitization implemented in self::prepare_item_for_database()
								'validate_callback' => null, // Note: validation implemented in self::prepare_item_for_database()
							);
							$args['properties']  	= array(
								'raw' => array(
									'description' => __( 'Title for the object, as it exists in the database.' ),
									'type'        => 'string',
									'context'     => array( 'edit' ),
								),
								'rendered' => array(
									'description' => __( 'HTML title for the object, transformed for display.' ),
									'type'        => 'string',
									'context'     => array( 'view', 'edit' ),
									'readonly'    => true,
								),
							);*/
						} else {
							$continue = true;
						}
					} else {
						$args['type']   = geodir_rest_data_type_to_field_type( $data_type );
					}

                    break;
                case 'textarea':
					if ( $name == 'post_content' ) {
						$name 		= 'content';
					}
					$args['type']   = 'string';
					$args['format'] = 'textarea-field';
                    break;
                case 'time':
                    $args['type']   = 'string';
                    break;
                case 'url':
                    $args['format'] = 'uri';
                    break;
                default:
					if ( has_filter( 'geodir_rest_post_custom_fields_schema' ) ) {
						$args = apply_filters( 'geodir_rest_post_custom_fields_schema', $args, $this->post_type, $field, $custom_fields, $package_id, $default );
						if ( empty( $args ) ) {
							$continue = true;
						}
					} else {
						$continue = true;
					}
                    break;
            }
            
            if ( $continue ) {
                continue;
            }
            
            if ( !empty( $rendered_options ) ) {
                $arg_options['rendered_options']   = $rendered_options;
            }
            
            $args['field_type']     = $field_type;
            $args['data_type']      = $data_type;
            $args['extra_fields']   = $extra_fields;
            if ( !empty( $options ) ) {
                $args['field_options']   = $options;
            }
            
            $args['arg_options']   = $arg_options;
            
            $schema[ $name ]    = apply_filters( 'geodir_listing_fields_args', $args, $field );
        }

        return apply_filters( 'geodir_listing_item_schema', $schema, $this->post_type, $package_id, $default );
    }
	
	/**
	 * Get taxonomy terms.
	 *
	 * @param object $product  Post instance.
	 * @param string     $taxonomy Taxonomy slug.
	 * @return array
	 */
	public function get_taxonomy_terms( $post, $taxonomy ) {
		$post_terms = get_the_terms( $post, $taxonomy );

		$terms = array();
		if ( ! empty( $post_terms ) && ! is_wp_error( $post_terms ) ) {
			foreach ( $post_terms as $term ) {
				$terms[] = array(
					'id'   => $term->term_id,
					'name' => $term->name,
					'slug' => $term->slug,
				);
			}
		}

		return $terms;
	}

    /**
     * Get featured image.
     *
     * @since 2.0.0
     *
     * @param object $gd_post GD post object.
     * @return array $featured_image.
     */
	public function get_featured_image( $gd_post ) {
		$images = geodir_get_images($gd_post->ID,1);

		$featured_image = array();
		if ( ! empty( $images ) && ! empty( $images[0] ) ) {
			$image = $images[0];
			if ( ! empty( $image->metadata ) ) {
				$image->metadata = maybe_unserialize( $image->metadata );
			}
			$featured_image['id'] = $image->ID;
			$featured_image['title'] = stripslashes_deep( $image->title );
			$featured_image['src'] = geodir_get_image_src( $image, 'original' );
			$featured_image['thumbnail'] = geodir_get_image_src( $image, 'thumbnail' );
			$featured_image['width'] = ! empty( $image->metadata ) && isset( $image->metadata['width'] ) ? $image->metadata['width'] : '';
			$featured_image['height'] = ! empty( $image->metadata ) && isset( $image->metadata['height'] ) ? $image->metadata['height'] : '';
		}
		return $featured_image;
	}

    /**
     * Get Post image.
     *
     * @since 2.0.0
     *
     * @param object $gd_post GD post Object.
     * @return array $images
     */
	public function get_post_images( $gd_post ) {
		$post_images = geodir_get_images( $gd_post->ID );
		
		$images = array();
		if ( ! empty( $post_images ) ) {
			foreach ( $post_images as $image ) {
				$row = array();
				$row['id'] = $image->ID;
				$row['title'] = stripslashes_deep( $image->title );
				$row['src'] = geodir_get_image_src( $image, 'original' );
				$row['thumbnail'] = geodir_get_image_src( $image, 'thumbnail' );
				$row['featured'] = (bool) $image->featured;
				$row['position'] = $image->menu_order;

				$images[] = $row;
			}
		}
		return $images;
	}

    /**
     * Get taxonomies.
     *
     * @since 2.0.0
     *
     * @return array $taxonomies.
     */
	public function get_taxonomies() {
		$taxonomies = wp_list_filter( get_object_taxonomies( $this->post_type, 'objects' ), array( 'show_in_rest' => true ) );

		return $taxonomies;
	}

    /**
     * Get post data.
     *
     * @since 2.0.0
     *
     * @param object $gd_post GD post object.
     * @param string $request Request.
     * @param object $post Post object.
     * @return array $data
     */
	public function get_post_data( $gd_post, $request, $post ) {
		$data = array();

		$schema 		= $this->get_item_schema();
		$taxonomies 	= $this->get_taxonomies();
		$post_fields 	= array_keys( (array) $gd_post );

		// ID
		if ( ! empty( $schema['properties']['id'] ) ) {
			$data['id'] = $gd_post->ID;
		}
		
		// Title
		if ( ! empty( $schema['properties']['title'] ) ) {
			add_filter( 'protected_title_format', array( $this, 'protected_title_format' ) );

			$data['title'] = array(
				'raw'      => $gd_post->post_title,
				'rendered' => get_the_title( $gd_post->ID ),
			);

			remove_filter( 'protected_title_format', array( $this, 'protected_title_format' ) );
		}
		
		// Slug
		if ( ! empty( $schema['properties']['slug'] ) ) {
			$data['slug'] = $gd_post->post_name;
		}
		
		// Link
		if ( ! empty( $schema['properties']['link'] ) ) {
			$data['link'] = get_permalink( $gd_post->ID );
		}
		
		// Status
		if ( ! empty( $schema['properties']['status'] ) ) {
			$data['status'] = $gd_post->post_status;
		}

		// Type
		if ( ! empty( $schema['properties']['type'] ) ) {
			$data['type'] = $gd_post->post_type;
		}
		
		// Author
		if ( ! empty( $schema['properties']['author'] ) ) {
			$data['author'] = (int) $gd_post->post_author;
		}

		// Date
		if ( ! empty( $schema['properties']['date'] ) ) {
			$data['date'] = $this->prepare_date_response( $gd_post->post_date_gmt, $gd_post->post_date );
		}

		// Date GMT
		if ( ! empty( $schema['properties']['date_gmt'] ) ) {
			// For drafts, `post_date_gmt` may not be set, indicating that the
			// date of the draft should be updated each time it is saved (see
			// #38883).  In this case, shim the value based on the `post_date`
			// field with the site's timezone offset applied.
			if ( '0000-00-00 00:00:00' === $gd_post->post_date_gmt ) {
				$post_date_gmt = get_gmt_from_date( $gd_post->post_date );
			} else {
				$post_date_gmt = $gd_post->post_date_gmt;
			}
			$data['date_gmt'] = $this->prepare_date_response( $post_date_gmt );
		}

		// Modified Date
		if ( ! empty( $schema['properties']['modified'] ) ) {
			$data['modified'] = $this->prepare_date_response( $gd_post->post_modified_gmt, $gd_post->post_modified );
		}

		// Modified Date GMT
		if ( ! empty( $schema['properties']['modified_gmt'] ) ) {
			// For drafts, `post_modified_gmt` may not be set (see
			// `post_date_gmt` comments above).  In this case, shim the value
			// based on the `post_modified` field with the site's timezone
			// offset applied.
			if ( '0000-00-00 00:00:00' === $gd_post->post_modified_gmt ) {
				$post_modified_gmt = date( 'Y-m-d H:i:s', strtotime( $gd_post->post_modified ) - ( get_option( 'gmt_offset' ) * 3600 ) );
			} else {
				$post_modified_gmt = $gd_post->post_modified_gmt;
			}
			$data['modified_gmt'] = $this->prepare_date_response( $post_modified_gmt );
		}
		
		// Content
		$has_password_filter = false;

		if ( $this->can_access_password_content( $post, $request ) ) {
			// Allow access to the post, permissions already checked before.
			add_filter( 'post_password_required', '__return_false' );

			$has_password_filter = true;
		}

		if (  ! empty( $schema['properties']['content'] ) ) {
			$data['content'] = array(
				'raw'       => $gd_post->post_content,
				/** This filter is documented in wp-includes/post-template.php */
				'rendered'  => post_password_required( $post ) ? '' : apply_filters( 'the_content', $gd_post->post_content ),
				'protected' => (bool) $gd_post->post_password,
			);
		}

		if ( ! empty( $schema['properties']['excerpt'] ) ) {
			/** This filter is documented in wp-includes/post-template.php */
			$excerpt = apply_filters( 'the_excerpt', apply_filters( 'get_the_excerpt', $gd_post->post_excerpt, $post ) );
			$data['excerpt'] = array(
				'raw'       => $gd_post->post_excerpt,
				'rendered'  => post_password_required( $post ) ? '' : $excerpt,
				'protected' => (bool) $gd_post->post_password,
			);
		}

		if ( $has_password_filter ) {
			// Reset filter.
			remove_filter( 'post_password_required', '__return_false' );
		}
		
		// Categories
		if ( isset( $taxonomies[ $this->cat_taxonomy ] ) && ! empty( $schema['properties']['post_category'] ) ) {
			$data['default_category'] = $gd_post->default_category;
			$data['post_category'] = $this->get_taxonomy_terms( $post, $this->cat_taxonomy );
		}
		
		// Tags
		if ( isset( $taxonomies[ $this->tag_taxonomy ] ) && ! empty( $schema['properties']['post_tags'] ) ) {
			$data['post_tags'] = $this->get_taxonomy_terms( $post, $this->tag_taxonomy );
		}
		
		// Custom fields
		foreach ( $schema['properties'] as $field_name => $field_info ) {
			if ( empty( $field_name ) || empty( $field_info['field_type'] ) ) {
				continue;
			}
			
			if ( in_array( $field_name, array( 'title', 'post_title', 'content', 'post_content', 'post_tags', 'post_category', 'images', 'post_images' ) ) || ! in_array( $field_name, $post_fields ) ) {
				continue;
			}

			$extra_fields 	= ! empty( $field_info['extra_fields'] ) ? $field_info['extra_fields'] : NULL;
			$option_values 	= ! empty( $field_info['arg_options']['rendered_options'] ) ? $field_info['arg_options']['rendered_options'] : NULL;
			$field_value 	= $gd_post->{$field_name};

			switch ( $field_info['field_type'] ) {
				case 'business_hours':
					if ( ! empty( $field_value ) ) {
						$data[ $field_name ] = array(
							'raw'		=> stripslashes( $field_value ),
							'rendered' 	=> geodir_get_business_hours( $field_value, ( ! empty( $gd_post->country ) ? $gd_post->country : '' ) )
						);
					} else {
						$data[ $field_name ] = NULL;
					}
					break;
				case 'checkbox':
					$data[ $field_name ] = array(
						'raw'		=> $field_value,
						'rendered' 	=> $field_value == 1 ? __( 'Yes', 'geodirectory' ) : __( 'No', 'geodirectory' )
					);
					break;
				case 'datepicker':
					if ( ! empty( $field_value ) ) {
						$date_format = ! empty( $extra_fields['date_format'] ) ? $extra_fields['date_format'] : geodir_date_format();
						$data[ $field_name ] = array(
							'raw'		=> $field_value,
							'rendered' 	=> date_i18n( $date_format, strtotime( $field_value ) )
						);
					} else {
						$data[ $field_name ] = NULL;
					}
					break;
				case 'multiselect':
					$rendered_value = array();
					$field_values = ! empty( $field_value ) ? explode( ',', trim( $field_value, ',' ) ) : array();
					if ( is_array( $field_values ) ) {
						$field_values = array_map( 'trim', array_values( $field_values ) );
					}

					if ( ! empty( $option_values ) ) {
						foreach ( $option_values as $option_value ) {
							if ( isset ( $option_value['value'] ) && in_array( $option_value['value'], $field_values ) ) {
								$rendered_value[] = $option_value['label'];
							}
						}
					}
			
					$data[ $field_name ] = array(
						'raw'		=> $field_value,
						'rendered' 	=> $rendered_value
					);
					break;
				case 'radio':
					$rendered_value = $field_value;
					$_rendered_value = NULL;

					if ( ! empty( $option_values ) ) {
						foreach ( $option_values as $option_value ) {
							if ( isset ( $option_value['value'] ) && $option_value['value'] == $rendered_value ) {
								$_rendered_value = $option_value['label'];
							}
						}
					}

					if ( $_rendered_value != NULL ) {
						$rendered_value = $_rendered_value;
					} else {
						if ( $rendered_value == 'f' || $rendered_value == '0' ) {
							$rendered_value = __( 'No', 'geodirectory' );
						} else if ( $rendered_value == 't' || $rendered_value == '1' ) {
							$rendered_value = __( 'Yes', 'geodirectory' );
						}
					}

					$data[ $field_name ] = array(
						'raw'		=> $field_value,
						'rendered' 	=> $rendered_value
					);
					break;
				case 'select':
					$rendered_value = $field_value;

					if ( ! empty( $option_values ) ) {
						foreach ( $option_values as $option_value ) {
							if ( isset ( $option_value['value'] ) && $option_value['value'] == $rendered_value ) {
								$rendered_value = $option_value['label'];
							}
						}
					}
			
					$data[ $field_name ] = array(
						'raw'		=> $field_value,
						'rendered' 	=> $rendered_value
					);
					break;
				case 'time':
					if ( ! empty( $field_value ) ) {
						$time_format = ! empty( $extra_fields['time_format'] ) ? $extra_fields['time_format'] : geodir_time_format();
						$data[ $field_name ] = array(
							'raw'		=> $field_value,
							'rendered' 	=> date_i18n( $time_format, strtotime( $field_value ) )
						);
					} else {
						$data[ $field_name ] = NULL;
					}
					break;
				case 'address':
					$data[ $field_name ] = geodir_post_address( $field_value, $field_name, $gd_post );
					break;
				case 'email':
				case 'file':
				case 'html':
				case 'phone':
                case 'text':
				case 'textarea':
				case 'url':
				default:
					$data[ $field_name ] = $field_value;
					break;
			}

		}
		
		// Extra fields
		$data['featured'] 		= (bool) $gd_post->featured;
		$data['rating'] 		= geodir_sanitize_float( $gd_post->overall_rating );
		$data['rating_count'] 	= (int) $gd_post->rating_count;
		
		// Featured image
		if ( ! empty( $schema['properties']['featured_media'] ) ) {
			$data['featured_media'] = (int) get_post_thumbnail_id( $gd_post->ID );
			$data['featured_image'] = $this->get_featured_image( $gd_post );
		}
		
		// Images
		if ( ! empty( $schema['properties']['post_images'] ) ) {
			$data['images'] = $this->get_post_images( $gd_post );
		}

		return apply_filters( 'geodir_rest_get_post_data', $data, $gd_post, $request, $this );
	}
}
