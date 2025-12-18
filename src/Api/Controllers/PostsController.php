<?php
/**
 * REST API Posts Controller
 *
 * Handles REST API endpoints for GeoDirectory posts (create, update, autosave).
 *
 * @package GeoDirectory\Api\Controllers
 * @since   3.0.0
 * @author  AyeCode Ltd
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Api\Controllers;

use AyeCode\GeoDirectory\Core\Services\PostPermissions;
use AyeCode\GeoDirectory\Core\Services\PostDrafts;
use AyeCode\GeoDirectory\Core\Services\PostSaveService;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * REST API Posts controller.
 *
 * @since 3.0.0
 */
final class PostsController extends WP_REST_Controller {
	private PostPermissions $permissions;
	private PostDrafts $drafts;
	private PostSaveService $save_service;

	/**
	 * Namespace for API routes.
	 */
	protected $namespace = 'geodir/v3';

	/**
	 * Post type for this controller.
	 */
	protected $post_type;

	/**
	 * Constructor.
	 *
	 * @param string          $post_type     Post type slug.
	 * @param PostPermissions $permissions   Permissions service.
	 * @param PostDrafts      $drafts        Drafts service.
	 * @param PostSaveService $save_service  Save service.
	 */
	public function __construct(
		string $post_type,
		PostPermissions $permissions,
		PostDrafts $drafts,
		PostSaveService $save_service
	) {
		$this->post_type = $post_type;
		$this->permissions = $permissions;
		$this->drafts = $drafts;
		$this->save_service = $save_service;

		$obj = get_post_type_object( $post_type );
		$this->rest_base = ! empty( $obj->rest_base ) ? $obj->rest_base : $obj->name;
	}

	/**
	 * Register routes.
	 */
	public function register_routes(): void {
		// Create post.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( 'POST' ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		// Update post.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				array(
					'methods'             => 'PUT',
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'update_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( 'PUT' ),
				),
			)
		);

		// Autosave post.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/autosave',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'autosave_item' ),
					'permission_callback' => array( $this, 'update_item_permissions_check' ),
					'args'                => array(), // No required params for autosave
				),
			)
		);

		// Delete post.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				array(
					'methods'             => 'DELETE',
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => array( $this, 'delete_item_permissions_check' ),
					'args'                => array(
						'force' => array(
							'type'        => 'boolean',
							'default'     => false,
							'description' => __( 'Whether to bypass trash and force deletion.', 'geodirectory' ),
						),
					),
				),
			)
		);
	}

	/**
	 * Check if user can create posts.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return true|WP_Error
	 */
	public function create_item_permissions_check( $request ) {
		return $this->permissions->can_create( $this->post_type );
	}

	/**
	 * Create a post.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_item( $request ) {
		// Prepare post data.
		$post_data = $this->prepare_item_for_save( $request );

		if ( is_wp_error( $post_data ) ) {
			return $post_data;
		}

		// SECURITY: Remove user-submitted values for critical fields.
		// These must be set by the system, not trusted from user input.
		unset( $post_data['post_type'], $post_data['post_status'], $post_data['post_author'], $post_data['ID'] );

		// Set post type (from controller, not user input).
		$post_data['post_type'] = $this->post_type;

		// Set default status for new posts (from settings, not user input).
		$post_data['post_status'] = geodir_new_post_default_status();

		// Set author (from session, not user input).
		$user_id = get_current_user_id();
		if ( $user_id ) {
			$post_data['post_author'] = $user_id;
		}

		// Give ALL data to PostSaveService.
		// PostSaveService will handle:
		// 1. Sanitizing core WP fields via filter_insert_post_data hook
		// 2. Processing all custom GeoDirectory fields via handle_save_post hook
		$this->save_service->set_post_data( $post_data );

		// Call wp_insert_post with minimal data.
		// PostSaveService hooks will add/sanitize everything else.
		$post_id = wp_insert_post(
			array(
				'post_type'   => $post_data['post_type'],
				'post_status' => $post_data['post_status'],
				'post_author' => $post_data['post_author'] ?? 0,
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		// Prepare response.
		$response = $this->prepare_create_response( $post_id, $post_data );

		return rest_ensure_response( $response );
	}

	/**
	 * Check if user can update post.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return true|WP_Error
	 */
	public function update_item_permissions_check( $request ) {
		$post_id = (int) $request['id'];
		return $this->permissions->can_edit_api( $post_id );
	}

	/**
	 * Update a post.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_item( $request ) {
		$post_id = (int) $request['id'];

		// Check if post exists.
		$post = get_post( $post_id );
		if ( ! $post ) {
			return new WP_Error(
				'post_not_found',
				__( 'Post not found.', 'geodirectory' ),
				array( 'status' => 404 )
			);
		}

		// Prepare post data.
		$post_data = $this->prepare_item_for_save( $request );

		if ( is_wp_error( $post_data ) ) {
			return $post_data;
		}

		// SECURITY: Remove user-submitted values for critical fields.
		// These must be set by the system, not trusted from user input.
		unset( $post_data['post_type'], $post_data['post_status'], $post_data['post_author'], $post_data['ID'] );

		// Handle revisions: If the post being updated is a revision, update the parent instead.
		// Check post_parent from request data (user submitted) and current post.
		$parent_id = 0;
		if ( isset( $post_data['post_parent'] ) && $post_data['post_parent'] ) {
			$parent_id = absint( $post_data['post_parent'] );
		} elseif ( $post->post_parent ) {
			$parent_id = $post->post_parent;
		}

		// If this is a revision, update the parent post instead.
		if ( $parent_id && $parent_id !== $post_id ) {
			$parent_post = get_post( $parent_id );
			if ( $parent_post && geodir_is_gd_post_type( $parent_post->post_type ) ) {
				// Switch to parent post
				$post_id = $parent_id;
				$post = $parent_post;

				// Delete the revision after we've extracted data from it
				wp_delete_post( (int) $request['id'], true );
			}
		}

		// Remove post_parent from data - we don't want to change parent relationships.
		unset( $post_data['post_parent'] );

		// Set post ID for update (from URL parameter or parent, not user input).
		$post_data['ID'] = $post_id;

		// Maintain post type (from controller, not user input).
		// This ensures we use the correct type even if updating a revision.
		$post_data['post_type'] = $this->post_type;

		// Determine post status (from system logic, not user input).
		// If current post is auto-draft, transition to default status.
		// Otherwise, keep existing status (users can't change status via API).
		if ( $post->post_status === 'auto-draft' ) {
			$post_data['post_status'] = geodir_get_option( 'default_status', 'publish' );
		} else {
			// Keep existing status - users cannot elevate their own posts.
			$post_data['post_status'] = $post->post_status;
		}

		// Give ALL data to PostSaveService.
		// PostSaveService will handle:
		// 1. Sanitizing core WP fields via filter_insert_post_data hook
		// 2. Processing all custom GeoDirectory fields via handle_save_post hook
		$this->save_service->set_post_data( $post_data );

		// Build minimal wp_update_post data.
//		$wp_post_data = array(
//			'ID'          => $post_id,
//			'post_status' => $post_data['post_status'],
//		);

		$post_data['ID'] = $post_id;
//		print_r($post_data);echo '@@@';//exit;
		// Update post. PostSaveService hooks will add/sanitize everything else.
//		$result = wp_update_post( $wp_post_data, true );


		remove_action( 'post_updated', 'wp_save_post_revision', 10 );
		$result = wp_update_post( $post_data, true );
		add_action( 'post_updated', 'wp_save_post_revision', 10, 1 );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Prepare response.
		$response = $this->prepare_update_response( $post_id, $post_data );

		return rest_ensure_response( $response );
	}

	/**
	 * Autosave a post.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function autosave_item( $request ) {
		$post_id = (int) $request['id'];

		// Prepare post data.
		$post_data = $this->prepare_item_for_save( $request );

		if ( is_wp_error( $post_data ) ) {
			return $post_data;
		}

		// Autosave via PostDrafts service.
		$result = $this->drafts->autosave( $post_id, $post_data );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return rest_ensure_response( $result );
	}

	/**
	 * Check if user can delete post.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return true|WP_Error
	 */
	public function delete_item_permissions_check( $request ) {
		$post_id = (int) $request['id'];
		return $this->permissions->can_delete_api( $post_id );
	}

	/**
	 * Delete a post.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_item( $request ) {
		$post_id = (int) $request['id'];
		$force = (bool) $request['force'];

		$post = get_post( $post_id );
		if ( ! $post ) {
			return new WP_Error(
				'post_not_found',
				__( 'Post not found.', 'geodirectory' ),
				array( 'status' => 404 )
			);
		}

		// Delete the post.
		$result = $force ? wp_delete_post( $post_id, true ) : wp_trash_post( $post_id );

		if ( ! $result ) {
			return new WP_Error(
				'delete_failed',
				__( 'Failed to delete post.', 'geodirectory' ),
				array( 'status' => 500 )
			);
		}

		return rest_ensure_response( array(
			'success' => true,
			'message' => $force
				? __( 'Post permanently deleted.', 'geodirectory' )
				: __( 'Post moved to trash.', 'geodirectory' ),
			'deleted' => true,
		) );
	}

	/**
	 * Prepare item for save.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return array|WP_Error Post data array or error.
	 */
	private function prepare_item_for_save( $request ) {
		// Get ALL parameters from request.
		$params = $request->get_json_params() ?: $request->get_body_params();

		// Remove fields that are handled separately by the controller.
		unset( $params['ID'], $params['id'] );

		// Map REST API style field names to WordPress style if needed.
		// This allows the API to accept both formats.
		if ( isset( $params['title'] ) && ! isset( $params['post_title'] ) ) {
			$params['post_title'] = $params['title'];
			unset( $params['title'] );
		}

		if ( isset( $params['content'] ) && ! isset( $params['post_content'] ) ) {
			$params['post_content'] = $params['content'];
			unset( $params['content'] );
		}

		if ( isset( $params['excerpt'] ) && ! isset( $params['post_excerpt'] ) ) {
			$params['post_excerpt'] = $params['excerpt'];
			unset( $params['excerpt'] );
		}

		if ( isset( $params['status'] ) && ! isset( $params['post_status'] ) ) {
			$params['post_status'] = $params['status'];
			unset( $params['status'] );
		}

		/**
		 * Filter post data before save.
		 *
		 * Allows modification of ALL post data (core fields + custom fields) before
		 * passing to PostSaveService. PostSaveService handles sanitization via its
		 * filter_insert_post_data and process_custom_fields methods.
		 *
		 * @since 3.0.0
		 *
		 * @param array           $params    All request parameters.
		 * @param WP_REST_Request $request   Request object.
		 * @param string          $post_type Post type.
		 */
		return apply_filters( 'geodir_rest_prepare_post_for_save', $params, $request, $this->post_type );
	}

	/**
	 * Prepare create response.
	 *
	 * @param int   $post_id   Created post ID.
	 * @param array $post_data Post data.
	 * @return array Response data.
	 */
	private function prepare_create_response( int $post_id, array $post_data ): array {
		$post = get_post( $post_id );
		$is_published = in_array( $post->post_status, array( 'publish' ), true );

		$response = array(
			'success' => true,
			'post_id' => $post_id,
			'status' => $post->post_status,
		);

		if ( $is_published ) {
			$response['message'] = __( 'Listing published successfully.', 'geodirectory' );
			$response['permalink'] = get_permalink( $post_id );
		} else {
			$response['message'] = __( 'Listing submitted successfully. It may need review before going live.', 'geodirectory' );
			$response['preview_link'] = $this->drafts->get_preview_link( $post );
		}

		/**
		 * Filter to add payment URL or other data to response.
		 *
		 * Addons can use this to add payment links, redirect URLs, etc.
		 *
		 * @since 3.0.0
		 *
		 * @param string $payment_url Empty by default.
		 * @param int    $post_id     Post ID.
		 * @param array  $post_data   Post data.
		 */
		$payment_url = apply_filters( 'geodir_post_save_payment_url', '', $post_id, $post_data );
		if ( $payment_url ) {
			$response['payment_url'] = $payment_url;
		}

		return $response;
	}

	/**
	 * Prepare update response.
	 *
	 * @param int   $post_id   Updated post ID.
	 * @param array $post_data Post data.
	 * @return array Response data.
	 */
	private function prepare_update_response( int $post_id, array $post_data ): array {
		$post = get_post( $post_id );
		$is_published = in_array( $post->post_status, array( 'publish' ), true );

		$response = array(
			'success' => true,
			'post_id' => $post_id,
			'status' => $post->post_status,
		);

		if ( $is_published ) {
			$response['message'] = __( 'Listing updated successfully.', 'geodirectory' );
			$response['permalink'] = get_permalink( $post_id );
		} else {
			$response['message'] = __( 'Listing updated. Changes may need review before going live.', 'geodirectory' );
			$response['preview_link'] = $this->drafts->get_preview_link( $post );
		}

		return $response;
	}

	/**
	 * Get endpoint args for item schema.
	 *
	 * @param string $method HTTP method.
	 * @return array
	 */
	public function get_endpoint_args_for_item_schema( $method = 'POST' ): array {
		return array(
			'title' => array(
				'type' => 'string',
				'required' => $method === 'POST',
				'description' => __( 'Listing title.', 'geodirectory' ),
				'sanitize_callback' => 'sanitize_text_field',
			),
			'content' => array(
				'type' => 'string',
				'description' => __( 'Listing description.', 'geodirectory' ),
				'sanitize_callback' => 'wp_kses_post',
			),
			'status' => array(
				'type' => 'string',
				'description' => __( 'Post status.', 'geodirectory' ),
				'enum' => array( 'publish', 'pending', 'draft' ),
			),
			'default_category' => array(
				'type' => 'integer',
				'description' => __( 'Default category ID.', 'geodirectory' ),
				'sanitize_callback' => 'absint',
			),
			'street' => array(
				'type' => 'string',
				'description' => __( 'Street address.', 'geodirectory' ),
				'sanitize_callback' => 'sanitize_text_field',
			),
			'city' => array(
				'type' => 'string',
				'description' => __( 'City.', 'geodirectory' ),
				'sanitize_callback' => 'sanitize_text_field',
			),
			'latitude' => array(
				'type' => 'number',
				'description' => __( 'Latitude.', 'geodirectory' ),
			),
			'longitude' => array(
				'type' => 'number',
				'description' => __( 'Longitude.', 'geodirectory' ),
			),
		);
	}

	/**
	 * Get public item schema.
	 *
	 * @return array
	 */
	public function get_public_item_schema(): array {
		return array(
			'$schema' => 'http://json-schema.org/draft-04/schema#',
			'title' => $this->post_type,
			'type' => 'object',
			'properties' => array(
				'id' => array(
					'description' => __( 'Post ID.', 'geodirectory' ),
					'type' => 'integer',
					'readonly' => true,
				),
				'title' => array(
					'description' => __( 'Listing title.', 'geodirectory' ),
					'type' => 'string',
				),
				'content' => array(
					'description' => __( 'Listing description.', 'geodirectory' ),
					'type' => 'string',
				),
			),
		);
	}
}
