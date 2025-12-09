<?php
/**
 * Users Service
 *
 * Handles user-related operations including favorites, listings, and permissions.
 *
 * @package GeoDirectory\Core\Services
 * @since   3.0.0
 * @author  AyeCode Ltd
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core\Services;

use AyeCode\GeoDirectory\Database\Repository\PostRepository;
use AyeCode\GeoDirectory\Core\Services\Settings;
use AyeCode\GeoDirectory\Core\Services\PostTypes;

/**
 * Service for GeoDirectory user operations.
 *
 * @since 3.0.0
 */
final class Users {
	private PostRepository $post_repository;
	private Settings $settings;
	private PostTypes $post_types;
	private Posts $posts;

	/**
	 * Constructor.
	 *
	 * All dependencies are injected here via the DI container.
	 *
	 * @param PostRepository $post_repository The post repository for database access.
	 * @param Settings       $settings        The settings service.
	 * @param PostTypes      $post_types      The post types service.
	 * @param Posts          $posts           The posts service.
	 */
	public function __construct(
		PostRepository $post_repository,
		Settings $settings,
		PostTypes $post_types,
		Posts $posts
	) {
		$this->post_repository = $post_repository;
		$this->settings        = $settings;
		$this->post_types      = $post_types;
		$this->posts           = $posts;
	}

	/**
	 * Add a post to the user's favorites list.
	 *
	 * @param int $post_id The post ID to add.
	 * @param int $user_id Optional. The user ID. Defaults to current user.
	 * @return bool True on success, false on failure.
	 */
	public function add_favorite( int $post_id, int $user_id = 0 ): bool {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		// If we have no user then bail.
		if ( ! $user_id ) {
			return false;
		}

		$user_favs = $this->get_favorites( $user_id );

		if ( empty( $user_favs ) || ! in_array( $post_id, $user_favs ) ) {
			$user_favs[] = $post_id;
		}

		$meta_key = $this->get_favorites_meta_key();
		$result   = update_user_meta( $user_id, $meta_key, $user_favs );

		if ( ! $result ) {
			return false;
		}

		/**
		 * Called after adding the post to favorites.
		 *
		 * @since 2.0.0
		 * @param int $post_id The post ID.
		 * @param int $user_id The user ID.
		 */
		do_action( 'geodir_add_fav_true', $post_id, $user_id );

		return true;
	}

	/**
	 * Remove a post from the user's favorites list.
	 *
	 * @param int $post_id The post ID to remove.
	 * @param int $user_id Optional. The user ID. Defaults to current user.
	 * @return bool True on success, false on failure.
	 */
	public function remove_favorite( int $post_id, int $user_id = 0 ): bool {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		// If we have no user then bail.
		if ( ! $user_id ) {
			return false;
		}

		$user_favs = $this->get_favorites( $user_id );

		if ( ! empty( $user_favs ) ) {
			$key = array_search( $post_id, $user_favs );
			if ( false !== $key ) {
				unset( $user_favs[ $key ] );
			}
		}

		$meta_key = $this->get_favorites_meta_key();
		$result   = update_user_meta( $user_id, $meta_key, $user_favs );

		if ( ! $result ) {
			return false;
		}

		/**
		 * Called after removing the post from favorites.
		 *
		 * @since 2.0.0
		 * @param int $post_id The post ID.
		 * @param int $user_id The user ID.
		 */
		do_action( 'geodir_remove_fav_true', $post_id, $user_id );

		return true;
	}

	/**
	 * Get the user's favorite posts.
	 *
	 * @param int $user_id Optional. The user ID. Defaults to current user.
	 * @return array Array of post IDs that are favorited.
	 */
	public function get_favorites( int $user_id = 0 ): array {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		// If we have no user then bail.
		if ( ! $user_id ) {
			return array();
		}

		$meta_key  = $this->get_favorites_meta_key();
		$user_favs = get_user_meta( $user_id, $meta_key, true );

		if ( ! $user_favs || ! is_array( $user_favs ) ) {
			return array();
		}

		return $user_favs;
	}

	/**
	 * Get the favorite counts per post type for a user.
	 *
	 * @param int $user_id Optional. The user ID. Defaults to current user.
	 * @return array Array of post types with their favorite counts.
	 */
	public function get_favorite_counts( int $user_id = 0 ): array {
		global $wpdb;

		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( ! $user_id ) {
			return array();
		}

		$post_types     = geodir_fav_allowed_post_types();
		$user_favorites = $this->get_favorites( $user_id );
		$user_listing   = array();

		if ( is_array( $post_types ) && ! empty( $post_types ) && ! empty( $user_favorites ) ) {
			$user_favorites_placeholders = implode( ',', array_fill( 0, count( $user_favorites ), '%d' ) );

			foreach ( $post_types as $ptype ) {
				$query = $wpdb->prepare(
					"SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish' AND ID IN ($user_favorites_placeholders)",
					array_merge( array( $ptype ), $user_favorites )
				);

				$total_posts = $wpdb->get_var( $query );

				if ( $total_posts > 0 ) {
					$user_listing[ $ptype ] = (int) $total_posts;
				}
			}
		}

		return $user_listing;
	}

	/**
	 * Get the user's post listing counts.
	 *
	 * @param int  $user_id     Optional. The user ID. Defaults to current user.
	 * @param bool $unpublished Optional. Include unpublished posts. Default false.
	 * @return array Array of post types with their listing counts.
	 */
	public function get_listing_counts( int $user_id = 0, bool $unpublished = false ): array {
		global $wpdb;

		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( ! $user_id ) {
			return array();
		}

		$all_posttypes = geodir_get_posttypes();
		$user_listing  = array();

		foreach ( $all_posttypes as $post_type ) {
			$statuses = geodir_get_post_stati( 'posts-count-live', array( 'post_type' => $post_type ) );

			if ( $unpublished ) {
				$statuses = array_merge(
					$statuses,
					geodir_get_post_stati( 'posts-count-offline', array( 'post_type' => $post_type ) )
				);
			}

			$statuses = array_unique( $statuses );

			// Build placeholders for prepared statement.
			$placeholders = implode( ', ', array_fill( 0, count( $statuses ), '%s' ) );

			$query = $wpdb->prepare(
				"SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_author = %d AND post_type = %s AND post_status IN ($placeholders)",
				array_merge( array( $user_id, $post_type ), $statuses )
			);

			$total_posts = $wpdb->get_var( $query );

			if ( $total_posts > 0 ) {
				$user_listing[ $post_type ] = (int) $total_posts;
			}
		}

		return $user_listing;
	}

	/**
	 * Delete a user's post.
	 *
	 * @param int $post_id The post ID to delete.
	 * @return bool|\WP_Error True on success, WP_Error on failure.
	 */
	public function delete_post( int $post_id ) {
		if ( ! geodir_listing_belong_to_current_user( $post_id ) ) {
			return new \WP_Error( 'gd-delete-failed', __( 'You do not have permission to delete this post.', 'geodirectory' ) );
		}

		$force_delete = geodir_get_option( 'user_trash_posts' ) == 1 ? false : true;

		if ( $force_delete ) {
			$result = wp_delete_post( $post_id, $force_delete );
		} else {
			$result = wp_trash_post( $post_id );
		}

		if ( false === $result ) {
			return new \WP_Error( 'gd-delete-failed', __( 'Delete post failed.', 'geodirectory' ) );
		}

		return true;
	}

	/**
	 * Check if the current user has a specific capability.
	 *
	 * @param string $capability Capability name.
	 * @param array  $args       Optional. Further parameters.
	 * @return bool Whether the current user has the given capability.
	 */
	public function user_can( string $capability, array $args = array() ): bool {
		$_args   = $args;
		$has_cap = false;

		switch ( $capability ) {
			case 'see_private_address':
				$has_cap = true;

				$defaults = array(
					'post'   => null,
					'author' => true,
				);

				$_args = wp_parse_args( $args, $defaults );

				if ( $this->posts->has_private_address( $_args['post'] ) ) {
					if ( ! empty( $_args['author'] ) ) {
						if ( is_scalar( $_args['post'] ) ) {
							$post_ID = absint( $_args['post'] );
						} elseif ( is_object( $_args['post'] ) ) {
							$post_ID = $_args['post']->ID;
						} else {
							$post_ID = 0;
						}

						if ( ! geodir_listing_belong_to_current_user( $post_ID ) ) {
							$has_cap = false;
						}
					} else {
						$has_cap = false;
					}
				}
				break;

			default:
				$has_cap = false;
				break;
		}

		/**
		 * Filters whether the current user has the specified capability.
		 *
		 * @since 2.1.1.9
		 * @param bool   $has_cap    Whether the current user has the given capability.
		 * @param string $capability Capability name.
		 * @param array  $_args      Parsed parameters.
		 * @param array  $args       Original parameters.
		 * @return bool Whether the current user has the given capability.
		 */
		return apply_filters( 'geodir_current_user_can', $has_cap, $capability, $_args, $args );
	}

	/**
	 * Get the user meta key for favorites, accounting for multisite.
	 *
	 * @return string The meta key for user favorites.
	 */
	private function get_favorites_meta_key(): string {
		$site_id = '';

		if ( is_multisite() ) {
			$blog_id = get_current_blog_id();
			if ( $blog_id && '1' !== (string) $blog_id ) {
				$site_id = '_' . $blog_id;
			}
		}

		return 'gd_user_favourite_post' . $site_id;
	}
}
