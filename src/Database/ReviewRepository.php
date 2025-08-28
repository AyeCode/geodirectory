<?php
/**
 * Review Repository
 *
 * Handles all database interactions for the custom reviews table.
 *
 * @package GeoDirectory\Database
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Database;

final class ReviewRepository {
	private \wpdb $db;
	private string $table_name;

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->db         = $wpdb;
		$this->table_name = $this->db->prefix . 'geodir_post_review';
	}

	/**
	 * Finds a single review by its comment ID.
	 *
	 * @param int $comment_id The WordPress comment ID.
	 * @return object|null The review data row, or null if not found.
	 */
	public function find( int $comment_id ): ?object {
		return $this->db->get_row(
			$this->db->prepare( "SELECT * FROM {$this->table_name} WHERE comment_id = %d", $comment_id )
		);
	}

	/**
	 * Gets just the rating value for a comment, with caching.
	 *
	 * @param int $comment_id The WordPress comment ID.
	 * @return float|null The rating value, or null if not found.
	 */
	public function get_rating( int $comment_id ): ?float {
		$cache_key = 'gd_comment_rating_' . $comment_id;
		$rating = wp_cache_get( $cache_key, 'gd_comment_rating' );

		if ( false === $rating ) {
			$rating = $this->db->get_var(
				$this->db->prepare( "SELECT rating FROM {$this->table_name} WHERE comment_id = %d", $comment_id )
			);
			wp_cache_set( $cache_key, $rating, 'gd_comment_rating' );
		}

		return $rating ? (float) $rating : null;
	}

	/**
	 * Deletes a review from the custom table.
	 *
	 * @param int $comment_id The WordPress comment ID.
	 * @return void
	 */
	public function delete( int $comment_id ): void {
		$this->db->delete( $this->table_name, [ 'comment_id' => $comment_id ], [ '%d' ] );
		wp_cache_delete( 'gd_comment_rating_' . $comment_id, 'gd_comment_rating' );
	}

	/**
	 * Creates a new review record in the custom table.
	 *
	 * @param array $data The data to insert. Keys should be column names.
	 * @return int|false The number of rows inserted, or false on error.
	 */
	public function create( array $data ) {
		return $this->db->insert( $this->table_name, $data );
	}

	/**
	 * Updates an existing review record.
	 *
	 * @param int   $comment_id The WordPress comment ID.
	 * @param array $data       The data to update. Keys should be column names.
	 * @return int|false The number of rows updated, or false on error.
	 */
	public function update( int $comment_id, array $data ) {
		wp_cache_delete( 'gd_comment_rating_' . $comment_id, 'gd_comment_rating' );
		return $this->db->update( $this->table_name, $data, [ 'comment_id' => $comment_id ] );
	}

	/**
	 * Calculates the average rating for a given post from approved comments.
	 *
	 * @param int $post_id The post ID.
	 * @return float The average rating.
	 */
	public function get_average_rating_for_post( int $post_id ): float {
		$query = $this->db->prepare(
			"SELECT COALESCE(avg(r.rating),0) FROM {$this->table_name} AS r JOIN {$this->db->comments} AS cmt ON cmt.comment_ID = r.comment_id WHERE r.post_id = %d AND cmt.comment_approved = '1' AND r.rating > 0",
			$post_id
		);
		return (float) $this->db->get_var( $query );
	}

	/**
	 * Gets the total number of approved reviews for a post, with caching.
	 *
	 * @param int $post_id The post ID.
	 * @return int The total number of reviews.
	 */
	public function get_count_for_post( int $post_id ): int {
		$cache_key = 'gd_post_review_count_total_' . $post_id;
		$count     = wp_cache_get( $cache_key, 'gd_post_review_count_total' );

		if ( false === $count ) {
			$query = $this->db->prepare(
				"SELECT COUNT(r.rating) FROM {$this->table_name} AS r JOIN {$this->db->comments} AS cmt ON cmt.comment_ID = r.comment_id WHERE r.post_id = %d AND cmt.comment_approved = '1' AND r.rating > 0",
				$post_id
			);
			$count = (int) $this->db->get_var( $query );
			wp_cache_set( $cache_key, $count, 'gd_post_review_count_total' );
		}

		return (int) $count;
	}

	/**
	 * Counts reviews for a specific post by a specific user.
	 *
	 * @param int $post_id The post ID.
	 * @param int $user_id The user ID.
	 * @param string $author_email The author's email address.
	 * @return int The number of reviews found.
	 */
	public function count_user_reviews_for_post( int $post_id, int $user_id = 0, string $author_email = '' ): int {
		if ( empty( $user_id ) && empty( $author_email ) ) {
			return 0;
		}

		$where_clauses = [ "r.post_id = %d", "cmt.comment_approved = '1'" ];
		$params        = [ $post_id ];

		if ( $user_id > 0 ) {
			$where_clauses[] = "cmt.user_id = %d";
			$params[]        = $user_id;
		}
		if ( ! empty( $author_email ) ) {
			$where_clauses[] = "cmt.comment_author_email = %s";
			$params[]        = $author_email;
		}

		$where_sql = implode( ' AND ', $where_clauses );
		$query     = $this->db->prepare( "SELECT COUNT(r.comment_id) FROM {$this->table_name} AS r JOIN {$this->db->comments} AS cmt ON cmt.comment_ID = r.comment_id WHERE {$where_sql}", $params );

		return (int) $this->db->get_var( $query );
	}
}
