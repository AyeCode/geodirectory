<?php
/**
 * Favorites Service
 *
 * Handles favorite/wishlist functionality for GeoDirectory posts.
 * Uses a custom database table for better performance and scalability.
 *
 * @package GeoDirectory\Core\Services
 * @since   3.0.0
 * @author  AyeCode Ltd
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core\Services;

use WP_Error;

/**
 * Service for favorites operations.
 *
 * @since 3.0.0
 */
final class Favorites {
	private Tables $tables;

	/**
	 * Constructor.
	 *
	 * @param Tables $tables Tables service.
	 */
	public function __construct( Tables $tables ) {
		$this->tables = $tables;
	}

	/**
	 * Add a post to user's favorites.
	 *
	 * @param int      $post_id Post ID to add.
	 * @param int|null $user_id User ID. Null for current user.
	 * @return true|WP_Error True on success, WP_Error on failure.
	 */
	public function add( int $post_id, ?int $user_id = null ): bool|WP_Error {
		if ( $user_id === null ) {
			$user_id = get_current_user_id();
		}

		if ( ! $user_id ) {
			return new WP_Error(
				'login_required',
				__( 'You must be logged in to add favorites.', 'geodirectory' ),
				array( 'status' => 401 )
			);
		}

		if ( ! $post_id ) {
			return new WP_Error(
				'invalid_post',
				__( 'Invalid post ID.', 'geodirectory' ),
				array( 'status' => 400 )
			);
		}

		$post = get_post( $post_id );
		if ( ! $post ) {
			return new WP_Error(
				'post_not_found',
				__( 'Post not found.', 'geodirectory' ),
				array( 'status' => 404 )
			);
		}

		// Check if already favorited.
		if ( $this->is_favorite( $post_id, $user_id ) ) {
			return new WP_Error(
				'already_favorited',
				__( 'This listing is already in your favorites.', 'geodirectory' ),
				array( 'status' => 400 )
			);
		}

		global $wpdb;
		$table = $this->tables->get( 'favorites' );
		$site_id = is_multisite() ? get_current_blog_id() : 1;

		$result = $wpdb->insert(
			$table,
			array(
				'user_id' => $user_id,
				'post_id' => $post_id,
				'post_type' => $post->post_type,
				'site_id' => $site_id,
				'date_added' => current_time( 'mysql' ),
			),
			array( '%d', '%d', '%s', '%d', '%s' )
		);

		if ( ! $result ) {
			return new WP_Error(
				'db_error',
				__( 'Failed to add favorite.', 'geodirectory' ),
				array( 'status' => 500 )
			);
		}

		// Clear cache.
		wp_cache_delete( 'geodir_favorites_' . $user_id, 'gd_favorites' );

		/**
		 * Fires after adding a post to favorites.
		 *
		 * @since 2.0.0
		 * @since 3.0.0 Moved to Favorites service.
		 *
		 * @param int $post_id The post ID.
		 * @param int $user_id The user ID.
		 */
		do_action( 'geodir_add_fav_true', $post_id, $user_id );

		return true;
	}

	/**
	 * Remove a post from user's favorites.
	 *
	 * @param int      $post_id Post ID to remove.
	 * @param int|null $user_id User ID. Null for current user.
	 * @return true|WP_Error True on success, WP_Error on failure.
	 */
	public function remove( int $post_id, ?int $user_id = null ): bool|WP_Error {
		if ( $user_id === null ) {
			$user_id = get_current_user_id();
		}

		if ( ! $user_id ) {
			return new WP_Error(
				'login_required',
				__( 'You must be logged in to remove favorites.', 'geodirectory' ),
				array( 'status' => 401 )
			);
		}

		if ( ! $post_id ) {
			return new WP_Error(
				'invalid_post',
				__( 'Invalid post ID.', 'geodirectory' ),
				array( 'status' => 400 )
			);
		}

		// Check if it's favorited.
		if ( ! $this->is_favorite( $post_id, $user_id ) ) {
			return new WP_Error(
				'not_favorited',
				__( 'This listing is not in your favorites.', 'geodirectory' ),
				array( 'status' => 400 )
			);
		}

		global $wpdb;
		$table = $this->tables->get( 'favorites' );
		$site_id = is_multisite() ? get_current_blog_id() : 1;

		$result = $wpdb->delete(
			$table,
			array(
				'user_id' => $user_id,
				'post_id' => $post_id,
				'site_id' => $site_id,
			),
			array( '%d', '%d', '%d' )
		);

		if ( $result === false ) {
			return new WP_Error(
				'db_error',
				__( 'Failed to remove favorite.', 'geodirectory' ),
				array( 'status' => 500 )
			);
		}

		// Clear cache.
		wp_cache_delete( 'geodir_favorites_' . $user_id, 'gd_favorites' );

		/**
		 * Fires after removing a post from favorites.
		 *
		 * @since 2.0.0
		 * @since 3.0.0 Moved to Favorites service.
		 *
		 * @param int $post_id The post ID.
		 * @param int $user_id The user ID.
		 */
		do_action( 'geodir_remove_fav_true', $post_id, $user_id );

		return true;
	}

	/**
	 * Get user's favorite post IDs.
	 *
	 * @param int|null $user_id    User ID. Null for current user.
	 * @param string   $post_type  Optional. Filter by post type.
	 * @param int      $limit      Optional. Limit number of results. Default 0 (all).
	 * @param int      $offset     Optional. Offset for pagination. Default 0.
	 * @return array Array of post IDs.
	 */
	public function get_user_favorites( ?int $user_id = null, string $post_type = '', int $limit = 0, int $offset = 0 ): array {
		if ( $user_id === null ) {
			$user_id = get_current_user_id();
		}

		if ( ! $user_id ) {
			return array();
		}

		// Check cache.
		$cache_key = 'geodir_favorites_' . $user_id . '_' . $post_type . '_' . $limit . '_' . $offset;
		$favorites = wp_cache_get( $cache_key, 'gd_favorites' );
		if ( $favorites !== false ) {
			return $favorites;
		}

		global $wpdb;
		$table = $this->tables->get( 'favorites' );
		$site_id = is_multisite() ? get_current_blog_id() : 1;

		$sql = "SELECT post_id FROM {$table} WHERE user_id = %d AND site_id = %d";
		$sql_args = array( $user_id, $site_id );

		if ( $post_type ) {
			$sql .= " AND post_type = %s";
			$sql_args[] = $post_type;
		}

		$sql .= " ORDER BY date_added DESC";

		if ( $limit > 0 ) {
			$sql .= " LIMIT %d";
			$sql_args[] = $limit;

			if ( $offset > 0 ) {
				$sql .= " OFFSET %d";
				$sql_args[] = $offset;
			}
		}

		$results = $wpdb->get_col( $wpdb->prepare( $sql, $sql_args ) );
		$favorites = $results ? array_map( 'absint', $results ) : array();

		// Cache for 1 hour.
		wp_cache_set( $cache_key, $favorites, 'gd_favorites', HOUR_IN_SECONDS );

		return $favorites;
	}

	/**
	 * Check if a post is favorited by user.
	 *
	 * @param int      $post_id Post ID to check.
	 * @param int|null $user_id User ID. Null for current user.
	 * @return bool True if favorited, false otherwise.
	 */
	public function is_favorite( int $post_id, ?int $user_id = null ): bool {
		if ( $user_id === null ) {
			$user_id = get_current_user_id();
		}

		if ( ! $user_id || ! $post_id ) {
			return false;
		}

		global $wpdb;
		$table = $this->tables->get( 'favorites' );
		$site_id = is_multisite() ? get_current_blog_id() : 1;

		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE user_id = %d AND post_id = %d AND site_id = %d",
				$user_id,
				$post_id,
				$site_id
			)
		);

		return $count > 0;
	}

	/**
	 * Get favorite count for a post.
	 *
	 * @param int $post_id Post ID.
	 * @return int Number of users who favorited this post.
	 */
	public function get_count( int $post_id ): int {
		if ( ! $post_id ) {
			return 0;
		}

		// Check cache.
		$cache_key = 'geodir_favorite_count_' . $post_id;
		$count = wp_cache_get( $cache_key, 'gd_favorites' );
		if ( $count !== false ) {
			return (int) $count;
		}

		global $wpdb;
		$table = $this->tables->get( 'favorites' );
		$site_id = is_multisite() ? get_current_blog_id() : 1;

		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE post_id = %d AND site_id = %d",
				$post_id,
				$site_id
			)
		);

		$count = $count ? (int) $count : 0;

		// Cache for 1 hour.
		wp_cache_set( $cache_key, $count, 'gd_favorites', HOUR_IN_SECONDS );

		return $count;
	}

	/**
	 * Get total number of favorites for a user.
	 *
	 * @param int|null $user_id User ID. Null for current user.
	 * @param string   $post_type Optional. Filter by post type.
	 * @return int Total favorites count.
	 */
	public function get_user_total( ?int $user_id = null, string $post_type = '' ): int {
		if ( $user_id === null ) {
			$user_id = get_current_user_id();
		}

		if ( ! $user_id ) {
			return 0;
		}

		global $wpdb;
		$table = $this->tables->get( 'favorites' );
		$site_id = is_multisite() ? get_current_blog_id() : 1;

		$sql = "SELECT COUNT(*) FROM {$table} WHERE user_id = %d AND site_id = %d";
		$sql_args = array( $user_id, $site_id );

		if ( $post_type ) {
			$sql .= " AND post_type = %s";
			$sql_args[] = $post_type;
		}

		$count = $wpdb->get_var( $wpdb->prepare( $sql, $sql_args ) );

		return $count ? (int) $count : 0;
	}

	/**
	 * Clear all favorites for a post (when post is deleted).
	 *
	 * @param int $post_id Post ID.
	 * @return bool True on success, false on failure.
	 */
	public function clear_post_favorites( int $post_id ): bool {
		if ( ! $post_id ) {
			return false;
		}

		global $wpdb;
		$table = $this->tables->get( 'favorites' );

		$result = $wpdb->delete( $table, array( 'post_id' => $post_id ), array( '%d' ) );

		// Clear cache.
		wp_cache_delete( 'geodir_favorite_count_' . $post_id, 'gd_favorites' );

		return $result !== false;
	}

	/**
	 * Migrate favorites from user meta to custom table.
	 *
	 * This is a one-time migration method for upgrading from v2.
	 *
	 * @param int $user_id User ID to migrate.
	 * @return int Number of favorites migrated.
	 */
	public function migrate_from_user_meta( int $user_id ): int {
		if ( ! $user_id ) {
			return 0;
		}

		$site_id = '';
		if ( is_multisite() ) {
			$blog_id = get_current_blog_id();
			if ( $blog_id && $blog_id != '1' ) {
				$site_id = '_' . $blog_id;
			}
		}

		$old_favorites = get_user_meta( $user_id, 'gd_user_favourite_post' . $site_id, true );

		if ( empty( $old_favorites ) || ! is_array( $old_favorites ) ) {
			return 0;
		}

		$migrated = 0;
		foreach ( $old_favorites as $post_id ) {
			$post_id = absint( $post_id );
			if ( ! $post_id ) {
				continue;
			}

			// Check if already migrated.
			if ( $this->is_favorite( $post_id, $user_id ) ) {
				continue;
			}

			// Add to new table.
			$result = $this->add( $post_id, $user_id );
			if ( ! is_wp_error( $result ) ) {
				$migrated++;
			}
		}

		return $migrated;
	}
}
