<?php
/**
 * Reviews Service
 *
 * Handles the business logic for reviews, acting as a coordinator
 * between WordPress hooks and the database repository.
 *
 * @package GeoDirectory\Core
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core\Services;

use AyeCode\GeoDirectory\Core\Interfaces\LocationsInterface;
use AyeCode\GeoDirectory\Core\Services\Settings;
use AyeCode\GeoDirectory\Database\Repository\ReviewRepository;

final class Reviews {
	private \wpdb $db;

	/**
	 * Constructor.
	 *
	 * All dependencies are "injected" here, so the class has the tools it needs.
	 *
	 * @param ReviewRepository   $repository The repository for database access.
	 * @param Settings           $settings   The settings utility.
	 * @param LocationsInterface $locations  The locations service.
	 */
	public function __construct(
		private ReviewRepository $repository,
		private Settings $settings,
		private LocationsInterface $locations
	) {
		global $wpdb;
		$this->db = $wpdb;
	}

	/**
	 * Handles the creation of a new review when a comment is posted.
	 *
	 * @param int $comment_id The newly created WordPress comment ID.
	 * @return void
	 */
	public function handle_new_comment( int $comment_id ): void {
		if ( ! isset( $_POST['geodir_overallrating'] ) ) {
			return;
		}

		$comment = get_comment( $comment_id );
		if ( ! $comment || $comment->comment_parent != 0 ) {
			return;
		}

		$post_location = $this->locations->get_for_post( (int) $comment->comment_post_ID );

		$data = [
			'post_id'    => $comment->comment_post_ID,
			'post_type'  => get_post_type( $comment->comment_post_ID ),
			'user_id'    => $comment->user_id,
			'comment_id' => $comment_id,
			'rating'     => absint( $_POST['geodir_overallrating'] ),
			'city'       => $post_location->city,
			'region'     => $post_location->region,
			'country'    => $post_location->country,
			'latitude'   => $post_location->latitude,
			'longitude'  => $post_location->longitude,
		];

		$this->repository->create( $data );
		$this->update_overall_post_rating( (int) $comment->comment_post_ID );
	}

	/**
	 * Handles the update of a review when a comment is edited.
	 *
	 * @param int $comment_id The WordPress comment ID.
	 * @return void
	 */
	public function handle_edited_comment( int $comment_id ): void {
		if ( ! isset( $_REQUEST['geodir_overallrating'] ) ) {
			return;
		}

		$comment = get_comment( $comment_id );
		if ( ! $comment || $comment->comment_parent != 0 ) {
			return;
		}

		$review = $this->repository->find( $comment_id );
		$rating = absint( $_REQUEST['geodir_overallrating'] );

		if ( $review ) {
			// Review exists, so update it.
			$this->repository->update( $comment_id, [ 'rating' => $rating ] );
		} else {
			// A rating was added to a comment that didn't have one before.
			$this->handle_new_comment( $comment_id );
			return; // The new comment handler also updates the post rating.
		}

		$this->update_overall_post_rating( (int) $comment->comment_post_ID );
	}

	/**
	 * Handles the deletion of a review when a comment is deleted.
	 *
	 * @param int $comment_id The WordPress comment ID.
	 * @return void
	 */
	public function handle_deleted_comment( int $comment_id ): void {
		$review = $this->repository->find( $comment_id );

		if ( $review ) {
			$post_id = (int) $review->post_id;
			$this->repository->delete( $comment_id );
			$this->update_overall_post_rating( $post_id );
		}
	}

	/**
	 * Recalculates and updates a post's overall rating when a comment status changes.
	 *
	 * @param int    $comment_id The WordPress comment ID.
	 * @param string $status     The new status (e.g., 'approve', 'hold').
	 * @return void
	 */
	public function handle_status_change( int $comment_id, string $status ): void {
		$comment = get_comment( $comment_id );
		if ( $comment ) {
			$this->update_overall_post_rating( (int) $comment->comment_post_ID );
		}
	}

	/**
	 * Recalculates and saves the overall rating and count for a post.
	 *
	 * @param int $post_id The post ID.
	 * @return void
	 */
	public function update_overall_post_rating( int $post_id ): void {
		$post_type = get_post_type( $post_id );
		if ( ! $post_type || ! str_starts_with( $post_type, 'gd_' ) ) {
			return;
		}

		$new_rating = $this->repository->get_average_rating_for_post( $post_id );
		$new_count  = $this->repository->get_count_for_post( $post_id );

		// @todo This should be moved to a `PostRepository` in the future.
		$details_table = $this->db->prefix . 'geodir_' . str_replace( 'gd_', '', $post_type ) . '_details';
		$this->db->update(
			$details_table,
			[ 'overall_rating' => $new_rating, 'rating_count' => $new_count ],
			[ 'post_id' => $post_id ],
			[ '%f', '%d' ],
			[ '%d' ]
		);

		// Clear related caches and transients.
		wp_cache_delete( 'gd_post_' . $post_id, 'gd_post' );
		delete_transient( 'gd_avg_num_votes_' . $details_table );
		delete_transient( 'gd_avg_rating_' . $details_table );

		do_action( 'geodir_update_post_rating', $post_id, $post_type, $new_rating, $new_count );
	}

	/**
	 * Checks if a user is allowed to submit a review for a given post.
	 *
	 * @param int $post_id The post ID.
	 * @return bool True if the user can review, false otherwise.
	 */
	public function can_user_submit_review( int $post_id ): bool {
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		// @todo Refactor GeoDir_Post_types::supports into a new service.
		if ( \GeoDir_Post_types::supports( get_post_type( $post_id ), 'single_review' ) ) {
			$user_id      = get_current_user_id();
			$author_email = '';

			if ( ! $user_id ) {
				$commenter = wp_get_current_commenter();
				$author_email = $commenter['comment_author_email'] ?? '';
			}

			if ( $this->repository->count_user_reviews_for_post( $post_id, $user_id, $author_email ) > 0 ) {
				return false; // User has already reviewed.
			}
		}

		return true;
	}
}
