<?php
/**
 * Review Hooks
 *
 * Registers all WordPress actions and filters related to the review system.
 *
 * @package GeoDirectory\Frontend
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Frontend;

use AyeCode\GeoDirectory\Core\Services\Reviews;
use AyeCode\GeoDirectory\Support\Hookable;

final class ReviewHooks {
	use Hookable;

	private Reviews $reviews_service;
	private ReviewForm $review_form;

	/**
	 * Constructor.
	 *
	 * @param Reviews $reviews_service The main reviews service.
	 * @param ReviewForm $review_form  The review form renderer.
	 */
	public function __construct(
		Reviews $reviews_service,
		ReviewForm $review_form
	) {
		$this->review_form     = $review_form;
		$this->reviews_service = $reviews_service;
	}

	/**
	 * Registers all the hooks related to reviews and comments.
	 */
	public function register(): void {
		// Form Modifications
		$this->filter( 'comment_form_defaults', [ $this->review_form, 'modify_form_defaults' ], 11 );
		$this->on( 'comment_form_logged_in_after', [ $this->review_form, 'render_rating_input' ] );
		$this->on( 'comment_form_before_fields', [ $this->review_form, 'render_rating_input' ] );

		// Comment Lifecycle Actions
		$this->on( 'comment_post', [ $this->reviews_service, 'handle_new_comment' ] );
		$this->on( 'edit_comment', [ $this->reviews_service, 'handle_edited_comment' ] );
		$this->on( 'delete_comment', [ $this->reviews_service, 'handle_deleted_comment' ] );
		$this->on( 'wp_set_comment_status', [ $this->reviews_service, 'handle_status_change' ], 10, 2 );

		// Frontend Display Filters
		$this->filter( 'comments_template', [ $this, 'override_comments_template' ] );
		$this->filter( 'get_comments_number', [ $this, 'filter_review_count' ], 10, 2 );
		$this->filter( 'comments_open', [ $this, 'filter_comments_open' ], 20, 2 );
		$this->filter( 'get_comments_link', [ $this, 'filter_comments_link_hash' ], 15, 2 );
	}

	/**
	 * Overrides the theme's comment template with the GeoDirectory reviews template for GD CPTs.
	 *
	 * @param string $template_path The path to the theme's comments.php file.
	 * @return string The path to the GeoDirectory reviews.php template.
	 */
	public function override_comments_template( string $template_path ): string {
		global $post;

		if ( is_singular() && geodir_is_gd_post_type( $post->post_type ?? '' ) && ! geodir_cpt_has_rating_disabled( $post->post_type ) ) {
			// @todo This should use a modern template loader service.
			$gd_template = geodir_get_template_part( 'reviews' );
			if ( $gd_template ) {
				return $gd_template;
			}
		}

		return $template_path;
	}

	/**
	 * Filters the comment count to show only the review count (excluding replies).
	 *
	 * @param int $count The original comment count.
	 * @param int $post_id The post ID.
	 * @return int The filtered review count.
	 */
	public function filter_review_count( int $count, int $post_id ): int {
		if ( is_admin() ) {
			return $count;
		}

		$post_type = get_post_type( $post_id );
		if ( ! $post_type || ! geodir_is_gd_post_type( $post_type ) ) {
			return $count;
		}

		return $this->reviews_service->repository->get_count_for_post( $post_id );
	}

	/**
	 * Checks if the current post should be open for reviews.
	 *
	 * @param bool $is_open Whether comments are open.
	 * @param int $post_id The post ID.
	 * @return bool True if open for reviews, false otherwise.
	 */
	public function filter_comments_open( bool $is_open, int $post_id ): bool {
		if ( ! $is_open ) {
			return $is_open;
		}

		$post_type = get_post_type( $post_id );
		if ( ! $post_type || ! geodir_is_gd_post_type( $post_type ) ) {
			return $is_open;
		}

		return $this->reviews_service->can_user_submit_review( $post_id );
	}

	/**
	 * Changes the #comments hash in the permalink to #reviews for GD CPTs.
	 *
	 * @param string $comments_link The original comments link.
	 * @param int $post_id The post ID.
	 * @return string The modified link.
	 */
	public function filter_comments_link_hash( string $comments_link, int $post_id ): string {
		$post_type = get_post_type( $post_id );
		if ( ! $post_type || ! geodir_is_gd_post_type( $post_type ) ) {
			return $comments_link;
		}

		return str_replace( '#comments', '#reviews', $comments_link );
	}
}
