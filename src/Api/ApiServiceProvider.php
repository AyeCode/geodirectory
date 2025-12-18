<?php
/**
 * API Service Provider
 *
 * Registers REST API routes and initializes controllers.
 *
 * @package GeoDirectory\Api
 * @since   3.0.0
 * @author  AyeCode Ltd
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Api;

use AyeCode\GeoDirectory\Api\Controllers\PostsController;
use AyeCode\GeoDirectory\Api\Controllers\FavoritesController;
use AyeCode\GeoDirectory\Api\Controllers\MediaController;
use AyeCode\GeoDirectory\Core\Container;

/**
 * API Service Provider.
 *
 * @since 3.0.0
 */
final class ApiServiceProvider {

	/**
	 * Register services.
	 *
	 * @param Container $container DI container.
	 */
	public function register( Container $container ): void {
		// Hook into rest_api_init to register routes.
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );

		// Add CORS headers for REST API.
		add_action( 'rest_api_init', array( $this, 'add_cors_headers' ) );
	}

	/**
	 * Register REST API routes.
	 */
	public function register_routes(): void {
		// Get container.
		$container = geodirectory()->container();

		// Register Favorites controller.
		$favorites_controller = new FavoritesController(
			$container->get( \AyeCode\GeoDirectory\Core\Services\Favorites::class )
		);
		$favorites_controller->register_routes();

		// Register Media controller.
		$media_controller = new MediaController();
		$media_controller->register_routes();

		// Register Posts controllers for each GeoDirectory post type.
		$post_types = geodir_get_posttypes();

		foreach ( $post_types as $post_type ) {
			$posts_controller = new PostsController(
				$post_type,
				$container->get( \AyeCode\GeoDirectory\Core\Services\PostPermissions::class ),
				$container->get( \AyeCode\GeoDirectory\Core\Services\PostDrafts::class ),
				$container->get( \AyeCode\GeoDirectory\Core\Services\PostSaveService::class )
			);
			$posts_controller->register_routes();
		}

		/**
		 * Fires after GeoDirectory REST API routes are registered.
		 *
		 * Allows addons to register their own routes.
		 *
		 * @since 3.0.0
		 *
		 * @param Container $container DI container.
		 */
		do_action( 'geodir_rest_api_routes_registered', $container );
	}

	/**
	 * Add CORS headers for REST API.
	 *
	 * Allows cross-origin requests for the REST API.
	 */
	public function add_cors_headers(): void {
		// Only add headers if explicitly enabled.
		if ( ! apply_filters( 'geodir_rest_api_cors_enabled', false ) ) {
			return;
		}

		remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );
		add_filter( 'rest_pre_serve_request', array( $this, 'send_cors_headers' ) );
	}

	/**
	 * Send CORS headers.
	 *
	 * @param bool $served Whether request has already been served.
	 * @return bool
	 */
	public function send_cors_headers( $served ): bool {
		$origin = get_http_origin();

		if ( $origin ) {
			header( 'Access-Control-Allow-Origin: ' . esc_url_raw( $origin ) );
			header( 'Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS' );
			header( 'Access-Control-Allow-Credentials: true' );
			header( 'Access-Control-Allow-Headers: X-WP-Nonce, Authorization, Content-Type' );
		}

		return $served;
	}
}
