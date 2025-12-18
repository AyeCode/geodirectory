<?php
/**
 * REST API Favorites Controller
 *
 * Handles REST API endpoints for user favorites.
 *
 * @package GeoDirectory\Api\Controllers
 * @since   3.0.0
 * @author  AyeCode Ltd
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Api\Controllers;

use AyeCode\GeoDirectory\Core\Services\Favorites;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * REST API Favorites controller.
 *
 * @since 3.0.0
 */
final class FavoritesController extends WP_REST_Controller {
	private Favorites $favorites;

	/**
	 * Namespace for API routes.
	 */
	protected $namespace = 'geodir/v3';

	/**
	 * REST base.
	 */
	protected $rest_base = 'favorites';

	/**
	 * Constructor.
	 *
	 * @param Favorites $favorites Favorites service.
	 */
	public function __construct( Favorites $favorites ) {
		$this->favorites = $favorites;
	}

	/**
	 * Register routes.
	 */
	public function register_routes(): void {
		// Add favorite.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
					'args'                => array(
						'post_id' => array(
							'type'              => 'integer',
							'required'          => true,
							'description'       => __( 'Post ID to add to favorites.', 'geodirectory' ),
							'sanitize_callback' => 'absint',
							'validate_callback' => function( $param ) {
								return is_numeric( $param ) && $param > 0;
							},
						),
					),
				),
			)
		);

		// Remove favorite.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<post_id>[\d]+)',
			array(
				array(
					'methods'             => 'DELETE',
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => array( $this, 'delete_item_permissions_check' ),
					'args'                => array(
						'post_id' => array(
							'type'              => 'integer',
							'required'          => true,
							'description'       => __( 'Post ID to remove from favorites.', 'geodirectory' ),
							'sanitize_callback' => 'absint',
						),
					),
				),
			)
		);

		// Get user favorites.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => array(
						'post_type' => array(
							'type'              => 'string',
							'description'       => __( 'Filter by post type.', 'geodirectory' ),
							'sanitize_callback' => 'sanitize_key',
						),
						'limit' => array(
							'type'              => 'integer',
							'default'           => 20,
							'description'       => __( 'Limit number of results.', 'geodirectory' ),
							'sanitize_callback' => 'absint',
						),
						'offset' => array(
							'type'              => 'integer',
							'default'           => 0,
							'description'       => __( 'Offset for pagination.', 'geodirectory' ),
							'sanitize_callback' => 'absint',
						),
					),
				),
			)
		);
	}

	/**
	 * Check if user can add favorites.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return true|WP_Error
	 */
	public function create_item_permissions_check( $request ) {
		if ( ! get_current_user_id() ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'You must be logged in to add favorites.', 'geodirectory' ),
				array( 'status' => 401 )
			);
		}

		return true;
	}

	/**
	 * Add a favorite.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_item( $request ) {
		$post_id = (int) $request['post_id'];

		// Add to favorites.
		$result = $this->favorites->add( $post_id );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Get updated count.
		$count = $this->favorites->get_count( $post_id );

		return rest_ensure_response( array(
			'success' => true,
			'message' => __( 'Added to favorites.', 'geodirectory' ),
			'is_favorite' => true,
			'favorite_count' => $count,
		) );
	}

	/**
	 * Check if user can remove favorites.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return true|WP_Error
	 */
	public function delete_item_permissions_check( $request ) {
		if ( ! get_current_user_id() ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'You must be logged in to remove favorites.', 'geodirectory' ),
				array( 'status' => 401 )
			);
		}

		return true;
	}

	/**
	 * Remove a favorite.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_item( $request ) {
		$post_id = (int) $request['post_id'];

		// Remove from favorites.
		$result = $this->favorites->remove( $post_id );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Get updated count.
		$count = $this->favorites->get_count( $post_id );

		return rest_ensure_response( array(
			'success' => true,
			'message' => __( 'Removed from favorites.', 'geodirectory' ),
			'is_favorite' => false,
			'favorite_count' => $count,
		) );
	}

	/**
	 * Check if user can get favorites.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return true|WP_Error
	 */
	public function get_items_permissions_check( $request ) {
		if ( ! get_current_user_id() ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'You must be logged in to view favorites.', 'geodirectory' ),
				array( 'status' => 401 )
			);
		}

		return true;
	}

	/**
	 * Get user favorites.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_items( $request ) {
		$post_type = $request['post_type'] ?? '';
		$limit = (int) $request['limit'];
		$offset = (int) $request['offset'];

		// Get favorites.
		$favorites = $this->favorites->get_user_favorites( null, $post_type, $limit, $offset );

		// Get total count.
		$total = $this->favorites->get_user_total( null, $post_type );

		return rest_ensure_response( array(
			'favorites' => $favorites,
			'total' => $total,
			'limit' => $limit,
			'offset' => $offset,
		) );
	}
}
