<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract Rest Posts Controller Class
 *
 * @author   GeoDirectory
 * @category API
 * @package  GeoDirectory/Abstracts
 * @version  2.0.0
 */
class GeoDir_REST_Posts_Controller extends WP_REST_Posts_Controller {

	/**
	 * Post type.
	 *
	 * @access protected
	 * @var string
	 */
	protected $post_type;

	/**
	 * Instance of a post meta fields object.
	 *
	 * @access protected
	 * @var WP_REST_Post_Meta_Fields
	 */
	protected $meta;

	/**
	 * Constructor.
	 *
	 * @access public
	 *
	 * @param string $post_type Post type.
	 */

    public function __construct( $post_type ) {
        $this->post_type    = $post_type;
        $this->namespace    = GEODIR_REST_SLUG . '/v' . GEODIR_REST_API_VERSION;
        $obj                = get_post_type_object( $post_type );
        $this->rest_base    = ! empty( $obj->rest_base ) ? $obj->rest_base : $obj->name;

        $this->meta         = new WP_REST_Post_Meta_Fields( $this->post_type );

        $this->cat_taxonomy = $this->post_type . 'category';
        $this->tag_taxonomy = $this->post_type . '_tags';
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
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'create_item_permissions_check' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
				'args'                => array(
					'context'  => $this->get_context_param( array( 'default' => 'view' ) ),
					'password' => array(
						'description' => __( 'The password for the post if it is password protected.' ),
						'type'        => 'string',
					),
				),
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
	 * Checks if a given post type can be viewed or managed.
	 *
	 * @access protected
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
	 * Retrieves the query params for the posts collection.
	 *
	 * @access public
	 *
	 * @return array Collection parameters.
	 */
	public function get_collection_params() {
		$params = parent::get_collection_params();

		$params['context']['default'] = 'view';

		if ( post_type_supports( $this->post_type, 'author' ) ) {
			$params['author'] = array(
				'description'         => __( 'Limit result set to posts assigned to specific authors.' ),
				'type'                => 'array',
				'items'               => array(
					'type'            => 'integer',
				),
				'default'             => array(),
			);
			$params['author_exclude'] = array(
				'description'         => __( 'Ensure result set excludes posts assigned to specific authors.' ),
				'type'                => 'array',
				'items'               => array(
					'type'            => 'integer',
				),
				'default'             => array(),
			);
		}

		$params['before'] = array(
			'description'        => __( 'Limit response to posts published before a given ISO8601 compliant date.' ),
			'type'               => 'string',
			'format'             => 'date-time',
		);

		$params['exclude'] = array(
			'description'        => __( 'Ensure result set excludes specific IDs.' ),
			'type'               => 'array',
			'items'              => array(
				'type'           => 'integer',
			),
			'default'            => array(),
		);

		$params['include'] = array(
			'description'        => __( 'Limit result set to specific IDs.' ),
			'type'               => 'array',
			'items'              => array(
				'type'           => 'integer',
			),
			'default'            => array(),
		);

		if ( 'page' === $this->post_type || post_type_supports( $this->post_type, 'page-attributes' ) ) {
			$params['menu_order'] = array(
				'description'        => __( 'Limit result set to posts with a specific menu_order value.' ),
				'type'               => 'integer',
			);
		}

		$params['offset'] = array(
			'description'        => __( 'Offset the result set by a specific number of items.' ),
			'type'               => 'integer',
		);

		$params['order'] = array(
			'description'        => __( 'Order sort attribute ascending or descending.' ),
			'type'               => 'string',
			'default'            => 'desc',
			'enum'               => array( 'asc', 'desc' ),
		);

		$params['orderby'] = array(
			'description'        => __( 'Sort collection by object attribute.' ),
			'type'               => 'string',
			'default'            => 'date',
			'enum'               => array(
				'date',
				'relevance',
				'id',
				'include',
				'title',
				'slug',
			),
		);

		if ( 'page' === $this->post_type || post_type_supports( $this->post_type, 'page-attributes' ) ) {
			$params['orderby']['enum'][] = 'menu_order';
		}

		$post_type_obj = get_post_type_object( $this->post_type );

		if ( $post_type_obj->hierarchical || 'attachment' === $this->post_type ) {
			$params['parent'] = array(
				'description'       => __( 'Limit result set to those of particular parent IDs.' ),
				'type'              => 'array',
				'items'             => array(
					'type'          => 'integer',
				),
				'default'           => array(),
			);
			$params['parent_exclude'] = array(
				'description'       => __( 'Limit result set to all items except those of a particular parent ID.' ),
				'type'              => 'array',
				'items'             => array(
					'type'          => 'integer',
				),
				'default'           => array(),
			);
		}

		$params['slug'] = array(
			'description'       => __( 'Limit result set to posts with one or more specific slugs.' ),
			'type'              => 'array',
			'items'             => array(
				'type'          => 'string',
			),
			'sanitize_callback' => 'wp_parse_slug_list',
		);

		$params['status'] = array(
			'default'           => 'publish',
			'description'       => __( 'Limit result set to posts assigned one or more statuses.' ),
			'type'              => 'array',
			'items'             => array(
				'enum'          => array_merge( array_keys( get_post_stati() ), array( 'any' ) ),
				'type'          => 'string',
			),
			'sanitize_callback' => array( $this, 'sanitize_post_statuses' ),
		);

		$taxonomies = wp_list_filter( get_object_taxonomies( $this->post_type, 'objects' ), array( 'show_in_rest' => true ) );

		foreach ( $taxonomies as $taxonomy ) {
			$base = ! empty( $taxonomy->rest_base ) ? $taxonomy->rest_base : $taxonomy->name;

			$params[ $base ] = array(
				/* translators: %s: taxonomy name */
				'description'       => sprintf( __( 'Limit result set to all items that have the specified term assigned in the %s taxonomy.' ), $base ),
				'type'              => 'array',
				'items'             => array(
					'type'          => 'integer',
				),
				'default'           => array(),
			);

			$params[ $base . '_exclude' ] = array(
				/* translators: %s: taxonomy name */
				'description' => sprintf( __( 'Limit result set to all items except those that have the specified term assigned in the %s taxonomy.' ), $base ),
				'type'        => 'array',
				'items'       => array(
					'type'    => 'integer',
				),
				'default'           => array(),
			);
		}

		if ( 'post' === $this->post_type ) {
			$params['sticky'] = array(
				'description'       => __( 'Limit result set to items that are sticky.' ),
				'type'              => 'boolean',
			);
		}

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
		 *
		 * @param $params JSON Schema-formatted collection parameters.
		 * @param WP_Post_Type $post_type_obj Post type object.
		 */
		return apply_filters( "rest_{$this->post_type}_collection_params", $params, $post_type_obj );
	}

	/**
	 * Prepares links for the request.
	 *
	 * @since 4.7.0
	 * @access protected
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

		if ( ( in_array( $post->post_type, array( 'post', 'page' ), true ) || post_type_supports( $post->post_type, 'author' ) )
			&& ! empty( $post->post_author ) ) {
			$links['author'] = array(
				'href'       => rest_url( 'wp/v2/users/' . $post->post_author ),
				'embeddable' => true,
			);
		}

        if ( in_array( $post->post_type, array( 'post', 'page' ), true ) || post_type_supports( $post->post_type, 'comments' ) ) {
            $reviews_url = rest_url( $this->namespace . '/reviews' );
            $reviews_url = add_query_arg( 'post', $post->ID, $reviews_url );

            $links['reviews'] = array(
                'href'       => $reviews_url,
                'embeddable' => true,
            );
        }

		if ( in_array( $post->post_type, array( 'post', 'page' ), true ) || post_type_supports( $post->post_type, 'revisions' ) ) {
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

				$tax_base = ! empty( $taxonomy_obj->rest_base ) ? $taxonomy_obj->rest_base : $tax;

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

        if ( !empty( $post->post_location_id ) && geodir_rest_is_active( 'location' ) && $location = geodir_get_location_by_id( NULL, $post->post_location_id ) ) {
            $location_links = array();

            $location_links[] = array(
                'href'          => rest_url( sprintf( '%s/%s/%s/%s', $this->namespace, 'locations', 'countries', $location->country_slug ) ),
                'location'      => 'country',
                'embeddable'    => true,
            );

            $add_query_args = array( 'country' => $location->country_slug );

            $location_links[] = array(
                'href'          => add_query_arg( $add_query_args, rest_url( sprintf( '%s/%s/%s/%s', $this->namespace, 'locations', 'regions', $location->region_slug ) ) ),
                'location'      => 'region',
                'embeddable'    => true,
            );

            $add_query_args['region'] = $location->region_slug;

            $location_links[] = array(
                'href'          => add_query_arg( $add_query_args, rest_url( sprintf( '%s/%s/%s/%s', $this->namespace, 'locations', 'cities', $location->city_slug ) ) ),
                'location'      => 'city',
                'embeddable'    => true,
            );

            if ( !empty( $post->gd_neighbourhood ) && geodir_rest_is_active( 'neighbourhood' ) ) {
                $add_query_args['city'] = $location->city_slug;

                $location_links[] = array(
                    'href'          => add_query_arg( $add_query_args, rest_url( sprintf( '%s/%s/%s/%s', $this->namespace, 'locations', 'neighbourhoods', $post->gd_neighbourhood ) ) ),
                    'location'      => 'neighbourhood',
                    'embeddable'    => true,
                );
            }

            $links['https://api.w.org/location'] = $location_links;
        }

		return $links;
	}

	/**
	 * Creates a single post.
	 *
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function create_item( $request ) {
        global $post, $wpdb, $gd_permalink_cache;

		if ( ! empty( $request['id'] ) ) {
			return new WP_Error( 'rest_post_exists', __( 'Cannot create existing post.' ), array( 'status' => 400 ) );
		}

		$prepared_post = $this->prepare_item_for_database( $request );

		if ( is_wp_error( $prepared_post ) ) {
			return $prepared_post;
		}

		$prepared_post->post_type = $this->post_type;

		$post_id = wp_insert_post( wp_slash( (array) $prepared_post ), true );

        if ( !is_wp_error( $post_id ) ) {
            if ( !empty( $gd_permalink_cache ) && isset( $gd_permalink_cache[$post_id] ) ) {
                unset( $gd_permalink_cache[$post_id] );
            }
            $post = get_post( $post_id );

            $gd_post = $this->prepare_item_for_geodir_database( $request, $post_id );

            $post_id = geodir_save_listing( $gd_post, null, true );
        }

		if ( is_wp_error( $post_id ) ) {

			if ( 'db_insert_error' === $post_id->get_error_code() ) {
				$post_id->add_data( array( 'status' => 500 ) );
			} else {
				$post_id->add_data( array( 'status' => 400 ) );
			}

			return $post_id;
		} else {
            $wpdb->update( $wpdb->posts, array( 'guid' => get_permalink( $post_id ) ), array( 'ID' => $post_id ) );
            clean_post_cache( $post_id );
        }

		$post = get_post( $post_id );

		/**
		 * Fires after a single post is created or updated via the REST API.
		 *
		 * The dynamic portion of the hook name, `$this->post_type`, refers to the post type slug.
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

		if ( ! empty( $schema['properties']['template'] ) && isset( $request['template'] ) ) {
			$this->handle_template( $request['template'], $post_id );
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
	 * Updates a single post.
	 *
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_item( $request ) {
        global $post, $wpdb;

		$id   = (int) $request['id'];
		$post = get_post( $id );

		if ( empty( $id ) || empty( $post->ID ) || $this->post_type !== $post->post_type ) {
			return new WP_Error( 'rest_post_invalid_id', __( 'Invalid post ID.' ), array( 'status' => 404 ) );
		}

		$post = $this->prepare_item_for_database( $request );

		if ( is_wp_error( $post ) ) {
			return $post;
		}

		// convert the post object to an array, otherwise wp_update_post will expect non-escaped input.
		$post_id = wp_update_post( wp_slash( (array) $post ), true );

        if ( !is_wp_error( $post_id ) ) {
            $gd_post = $this->prepare_item_for_geodir_database( $request, $post_id );

            $post_id = geodir_save_listing( $gd_post, null, true );
        }

		if ( is_wp_error( $post_id ) ) {
			if ( 'db_update_error' === $post_id->get_error_code() ) {
				$post_id->add_data( array( 'status' => 500 ) );
			} else {
				$post_id->add_data( array( 'status' => 400 ) );
			}
			return $post_id;
		} else {
            $wpdb->update( $wpdb->posts, array( 'guid' => get_permalink( $post_id ) ), array( 'ID' => $post_id ) );
            clean_post_cache( $post_id );
        }

		$post = get_post( $post_id );

		/* This action is documented in lib/endpoints/class-wp-rest-controller.php */
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

		if ( ! empty( $schema['properties']['template'] ) && isset( $request['template'] ) ) {
			$this->handle_template( $request['template'], $post->ID );
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
	 * Prepares a single post for create or update.
	 *
	 * @access protected
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return stdClass|WP_Error Post object or WP_Error.
	 */
	protected function prepare_item_for_geodir_database( $request, $post_id = 0 ) {
        $prepared_post = $request->get_params();

        // Post ID.
        if ( isset( $request['id'] ) ) {
            $prepared_post['post_id'] = absint( $request['id'] );
        }

        $schema = $this->get_item_schema();

        // Post title.
        if ( ! empty( $schema['properties']['title'] ) && isset( $request['title'] ) ) {
            if ( is_string( $request['title'] ) ) {
                $prepared_post['post_title'] = $request['title'];
            } elseif ( ! empty( $request['title']['raw'] ) ) {
                $prepared_post['post_title'] = $request['title']['raw'];
            }
        }

        // Post content.
        if ( ! empty( $schema['properties']['content'] ) && isset( $request['content'] ) ) {
            if ( is_string( $request['content'] ) ) {
                $prepared_post['content'] = $request['content'];
            } elseif ( isset( $request['content']['raw'] ) ) {
                $prepared_post['content'] = $request['content']['raw'];
            }
        }

        // Post category.
        if ( isset( $request[ $this->cat_taxonomy ] ) ) {
            if ( empty( $request[ $this->cat_taxonomy ] ) ) {
                $post_category = '';
            } elseif ( is_array( $request[ $this->cat_taxonomy ] ) ) {
                $post_category = implode( ',', $request[ $this->cat_taxonomy ] );
            } else {
                $post_category = $request[ $this->cat_taxonomy ];
            }

            $prepared_post['post_category'][ $this->cat_taxonomy ] = $post_category;
        }

        // Post tags.
        if ( isset( $request[ $this->tag_taxonomy ] ) ) {
            $post_tags = empty( $request[ $this->tag_taxonomy ] ) || is_array( $request[ $this->tag_taxonomy ] ) ? $request[ $this->tag_taxonomy ] : explode( ',', $request[ $this->tag_taxonomy ] );

            $prepared_post['post_tags'] = $post_tags;

            wp_set_object_terms( $post_id, $prepared_post['post_tags'], $this->tag_taxonomy );
        }

        /**
         * Filters a post before it is inserted via the REST API.
         *
         * The dynamic portion of the hook name, `$this->post_type`, refers to the post type slug.
         *
         *
         * @param array        $prepared_post An object representing a single post prepared
         *                                       for inserting or updating the database.
         * @param WP_REST_Request $request       Request object.
         */
        $prepared_post = apply_filters( "geodir_rest_pre_insert_{$this->post_type}", $prepared_post, $request );

        /**
         * Filters a post before it is inserted via the REST API.
         *
         * The dynamic portion of the hook name, `$this->post_type`, refers to the post type slug.
         *
         *
         * @param array        $prepared_post An object representing a single post prepared
         *                                       for inserting or updating the database.
         * @param WP_REST_Request $request       Request object.
         */
        return apply_filters( "geodir_rest_pre_insert_listing", $prepared_post, $this->post_type, $request );
	}

    /**
     * Register GeoDir listing fields.
     *
     * @since 2.0.0
     */
    public function register_listing_fields() {
        $listing_schema = $this->geodir_get_item_schema();

        foreach ( $listing_schema as $name => $schema ) {
            $args = array();
            //$args['get_callback']      = array( $this, 'geodir_get_callback' );
            //$args['update_callback']   = array( $this, 'geodir_update_callback' );
            $args['schema']            = $schema;

            register_rest_field( $this->post_type, $name, $args );
        }
    }

    /**
     * GeoDir get item schema.
     *
     * @since 2.0.0
     *
     * @param string $package_id Optional. Package id. Default null.
     * @param string $default Optional. Custom field default value. Default all.
     * @return array Item schema array values.
     */
    public function geodir_get_item_schema( $package_id = '', $default = 'all' ) {
	    global $geodirectory;
        $custom_fields  = geodir_post_custom_fields( $package_id, $default, $this->post_type );

        $schema = array();

        $schema[ 'post_images' ] = array(
            'type'          => 'string',
            'context'       => array( 'view', 'edit' ),
            'title'         => __( 'Listing images', 'geodirectory' ),
            'description'   => __( 'Comma separated list of listing images urls. First image will be set as a featured image for the listing.', 'geodirectory' ),
        );

        foreach ( $custom_fields as $id => $field ) {
            $admin_use              = (bool)$field['for_admin_use'];

            if ( $admin_use ) {
                continue;
            }

            $name                   = $field['htmlvar_name'];
            $data_type              = $field['data_type'];
            $field_type             = $field['field_type'];
            $title                  = $field['site_title'] ? stripslashes( __( $field['site_title'], 'geodirectory' ) ) : stripslashes( __( $field['admin_title'], 'geodirectory' ) );
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
            $prefix                 = '';

            $args                   = array();
            $args['type']           = 'string';
            $args['context']        = array( 'view', 'edit' );
            $args['title']          = $title;
            $args['description']    = !empty( $description ) ? $description : $title;
            $args['required']       = (bool)$required;
            $args['default']        = $default;

            $continue = false;

            switch ( $field_type ) {
                case 'address':
                    $prefix     = $name . '_';
                    $name       = 'address';
                    $location   = $geodirectory->location->get_default_location();
                    $country    = !empty( $location->country ) ? $location->country : '';
                    $region     = !empty( $location->region ) ? $location->region : '';
                    $city       = !empty( $location->city ) ? $location->city : '';
                    $latitude   = !empty( $location->latitude ) ? $location->latitude : '';
                    $longitude  = !empty( $location->longitude ) ? $location->longitude : '';

                    $schema[ $prefix . $name ]    = $args;

                    $schema[ $prefix . 'country' ] = array(
                        'type'          => 'string',
                        'context'       => array( 'view', 'edit' ),
                        'title'         => __( 'Country', 'geodirectory' ),
                        'description'   => __( 'Choose a country', 'geodirectory' ),
                        'required'      => (bool)$required,
                        'default'       => $country,
                    );

                    $schema[ $prefix . 'region' ] = array(
                        'type'          => 'string',
                        'context'       => array( 'view', 'edit' ),
                        'title'         => __( 'Region', 'geodirectory' ),
                        'description'   => __( 'Choose a region', 'geodirectory' ),
                        'required'      => (bool)$required,
                        'default'       => $region,
                    );

                    $schema[ $prefix . 'city' ] = array(
                        'type'          => 'string',
                        'context'       => array( 'view', 'edit' ),
                        'title'         => __( 'City', 'geodirectory' ),
                        'description'   => __( 'Choose a city', 'geodirectory' ),
                        'required'      => (bool)$required,
                        'default'       => $city,
                    );

                    if ( geodir_rest_is_active( 'neighbourhood' ) ) {
                        $schema[ $prefix . 'neighbourhood' ] = array(
                            'type'          => 'string',
                            'context'       => array( 'view', 'edit' ),
                            'title'         => __( 'Neighbourhood', 'geodirectory' ),
                            'description'   => __( 'Choose a neighbourhood', 'geodirectory' ),
                            'required'      => (bool)$required,
                        );
                    }

                    if ( !empty( $extra_fields['show_zip'] ) ) {
                        $schema[ $prefix . 'zip' ] = array(
                            'type'          => 'string',
                            'context'       => array( 'view', 'edit' ),
                            'title'         => !empty( $extra_fields['zip_lable'] ) ? __( $extra_fields['zip_lable'], 'geodirectory' ) : __( 'Zip/Post Code', 'geodirectory' ),
                            'required'      => ! empty( $extra_fields['zip_required'] ) ? true : false,
                        );
                    }

                    if ( !empty( $extra_fields['show_map'] ) ) {
                        $schema[ $prefix . 'map' ] = array(
                            'type'          => 'string',
                            'context'       => array( 'view', 'edit' ),
                            'title'         => !empty( $extra_fields['map_lable'] ) ? __( $extra_fields['map_lable'], 'geodirectory' ) : __( 'Map', 'geodirectory' ),
                            'description'   => __( 'Click on "Set Address on Map" and then you can also drag pinpoint to locate the correct address', 'geodirectory' ),
                            'readonly'      => true,
                        );

                        $schema[ $prefix . 'latitude' ] = array(
                            'type'          => 'string',
                            'context'       => array( 'view', 'edit' ),
                            'title'         => __( 'Address Latitude', 'geodirectory' ),
                            'description'   => __( 'Please enter latitude for google map perfection. eg. : <b>39.955823048131286</b>', 'geodirectory' ),
                            'required'      => (bool)$required,
                            'default'       => $latitude,
                            'readonly'      => empty( $extra_fields['show_latlng'] ) ? true : false,
                        );

                        $schema[ $prefix . 'longitude' ] = array(
                            'type'          => 'string',
                            'context'       => array( 'view', 'edit' ),
                            'title'         => __( 'Address Longitude', 'geodirectory' ),
                            'description'   => __( 'Please enter longitude for google map perfection. eg. : <b>-75.14408111572266</b>', 'geodirectory' ),
                            'required'      => (bool)$required,
                            'default'       => $longitude,
                            'readonly'      => empty( $extra_fields['show_latlng'] ) ? true : false,
                        );
                    }

                    if ( !empty( $extra_fields['show_mapview'] ) ) {
                        $schema[ $prefix . 'mapview' ] = array(
                            'type'          => 'string',
                            'context'       => array( 'view', 'edit' ),
                            'title'         => !empty( $extra_fields['mapview_lable'] ) ? __( $extra_fields['mapview_lable'], 'geodirectory' ) : __( 'Map View', 'geodirectory' ),
                            'default'       => 'ROADMAP',
                            'enum'          => array( 'ROADMAP', 'SATELLITE', 'HYBRID', 'TERRAIN' ),
                        );
                    }

                    if ( !empty( $extra_fields['show_mapzoom'] ) ) {
                        $schema[ $prefix . 'mapzoom' ] = array(
                            'type'          => 'string',
                            'context'       => array( 'view', 'edit' ),
                            'title'         => __( 'Map Zoom', 'geodirectory' ),
                            'readonly'      => true,
                        );
                    }
                    break;
                case 'checkbox':
                    $args['type']   = geodir_rest_data_type_to_field_type( $data_type );
                    break;
                case 'datepicker':
                    $args['type']       = 'object';
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
                    if ( !empty( $extra_fields['date_format'] ) ) {
                        $arg_options['date_format'] = $extra_fields['date_format'];
                    }
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
                    $args['type']       = 'object';
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
                    $args['type']           = 'object';
                    $args['enum']           = $enum;
                    $args['items']          = array( 'type' => 'string' );
                    break;
                case 'select':
                    $args['type']       = 'object';
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
                case 'taxonomy':
                    if ( $this->cat_taxonomy == $name ) {
                        $args['type']        = 'array';
                        $args['items']       = array( 'type' => 'integer' );
                    }
                    break;
                case 'phone':
                case 'text':
                    $args['type']   = geodir_rest_data_type_to_field_type( $data_type );
                    break;
                case 'textarea':
                    $args['type']   = 'string';
                    break;
                case 'time':
                    $args['type']   = 'string';
                    break;
                case 'url':
                    $args['format'] = 'uri';
                    break;
                default:
                    $continue = true;
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

            $schema[ $prefix . $name ]    = apply_filters( 'geodir_listing_fields_args', $args, $field );
        }

        return apply_filters( 'geodir_listing_item_schema', $schema, $this->post_type, $package_id, $default );
    }

    /**
     * GeoDir get item callback.
     *
     * @since 2.0.0
     *
     * @param object $object Item object.
     * @param string $field_name Field name.
     * @param string $request Item request.
     * @param string $object_type Object type.
     * @return object $object.
     */
    public function geodir_get_callback( $object, $field_name, $request, $object_type ) {
        return $object;
    }

    /**
     * GeoDir update item callback.
     *
     * @param string $value Item value.
     * @param object $object Item object.
     * @param string $field_name Field name.
     * @param string $request Item request.
     * @param string $object_type Object type.
     * @return bool.
     */
    public function geodir_update_callback( $value, $object, $field_name, $request, $object_type ) {
        return true;
    }
}
