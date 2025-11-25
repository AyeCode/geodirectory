<?php
/**
 * Post Save Hooks
 *
 * Registers WordPress hooks for the post save system.
 * Uses the Hookable trait for easy hook management.
 *
 * @package GeoDirectory\Core
 * @since   3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core;

use AyeCode\GeoDirectory\Core\Services\PostSaveService;
use AyeCode\GeoDirectory\Support\Hookable;

final class PostSaveHooks {

	use Hookable;

	/**
	 * The post save service.
	 *
	 * @var PostSaveService
	 */
	private PostSaveService $service;

	/**
	 * Constructor.
	 *
	 * @param PostSaveService $service The post save service.
	 */
	public function __construct( PostSaveService $service ) {
		$this->service = $service;
	}

	/**
	 * Register all hooks.
	 *
	 * @return void
	 */
	public function hook(): void {
		$this->filter( 'wp_insert_post_data', [ $this, 'filter_post_data' ], 10, 2 );
		$this->on( 'save_post', [ $this, 'save_post' ], 10, 3 );
	}

	/**
	 * Filter post data before WordPress inserts/updates it.
	 *
	 * @param array $data    Post data to be inserted/updated.
	 * @param array $postarr Unmodified post data array.
	 * @return array Modified post data.
	 */
	public function filter_post_data( $data, $postarr ) {
		return $this->service->filter_insert_post_data( $data, $postarr );
	}

	/**
	 * Handle post save action.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 * @param bool     $update  Whether this is an update.
	 * @return void
	 */
	public function save_post( $post_id, $post, $update ) {
		$this->service->handle_save_post( $post_id, $post, $update );
	}
}
