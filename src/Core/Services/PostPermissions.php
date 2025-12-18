<?php
/**
 * Post Permissions Service
 *
 * Handles permission checks for GeoDirectory posts, including ownership verification,
 * edit permissions, and logged-out user authorization via cookies.
 *
 * @package GeoDirectory\Core\Services
 * @since   3.0.0
 * @author  AyeCode Ltd
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core\Services;

use WP_Error;

/**
 * Service for post permission operations.
 *
 * @since 3.0.0
 */
final class PostPermissions {
	private Settings $settings;

	/**
	 * Constructor.
	 *
	 * @param Settings $settings Settings service.
	 */
	public function __construct( Settings $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Check if a user owns a specific post.
	 *
	 * For logged-in users, checks post_author field.
	 * For logged-out users, checks cookie-based temporary ownership.
	 *
	 * @param int $post_id The post ID to check.
	 * @param int $user_id The user ID to check ownership for.
	 * @return bool True if user owns the post, false otherwise.
	 */
	public function is_owner( int $post_id, int $user_id ): bool {
		if ( ! $post_id ) {
			return false;
		}

		$author_id = get_post_field( 'post_author', $post_id );

		// Check for logged-out users via cookie.
		if ( ! $user_id ) {
			$post_current_nonce = get_post_meta( $post_id, '_gd_logged_out_post_author', true );
			if ( $post_current_nonce && $post_current_nonce === geodir_getcookie( '_gd_logged_out_post_author' ) ) {
				return true;
			}
			return false;
		}

		// Check if user is the author.
		if ( $author_id == $user_id ) {
			return true;
		}

		// Check if user can edit others' posts.
		if ( current_user_can( 'edit_others_posts' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if a user can edit a specific post.
	 *
	 * Verifies ownership and handles special cases like revisions, auto-drafts,
	 * and parent post relationships.
	 *
	 * @param int $post_id   The post ID to check.
	 * @param int $user_id   The user ID attempting to edit.
	 * @param int $parent_id Optional. Parent post ID for revisions/drafts. Default 0.
	 * @return bool True if user can edit the post, false otherwise.
	 */
	public function can_edit( int $post_id, int $user_id, int $parent_id = 0 ): bool {
		$can_edit = false;

		if ( ! $post_id ) {
			return false;
		}

		$post = get_post( $post_id );

		if ( empty( $post ) ) {
			return false;
		}

		// If there's a parent, check ownership of parent instead.
		if ( $parent_id && $this->is_owner( $parent_id, $user_id ) ) {
			$can_edit = true;
		}
		// Check ownership of the post itself.
		elseif ( $this->is_owner( $post_id, $user_id ) ) {
			$can_edit = true;
		}
		// Allow editing auto-drafts if logged out posting is enabled.
		elseif ( ! $user_id && $post->post_status === 'auto-draft' && geodir_get_option( 'post_logged_out' ) ) {
			$can_edit = true;
		}

		/**
		 * Filter whether user can edit a post.
		 *
		 * @since 2.0.0
		 * @since 3.0.0 Moved to PostPermissions service.
		 *
		 * @param bool $can_edit  True if user can edit, false otherwise.
		 * @param int  $post_id   The post ID.
		 * @param int  $user_id   The user ID.
		 * @param int  $parent_id The parent post ID.
		 */
		return apply_filters( 'geodir_post_can_edit', $can_edit, $post_id, $user_id, $parent_id );
	}

	/**
	 * Check if a user can delete a specific post.
	 *
	 * @param int $post_id The post ID to check.
	 * @param int $user_id The user ID attempting to delete.
	 * @return bool True if user can delete the post, false otherwise.
	 */
	public function can_delete( int $post_id, int $user_id ): bool {
		if ( ! $post_id ) {
			return false;
		}

		// Check ownership first.
		if ( ! $this->is_owner( $post_id, $user_id ) ) {
			return false;
		}

		/**
		 * Filter whether user can delete a post.
		 *
		 * @since 3.0.0
		 *
		 * @param bool $can_delete True if user can delete, false otherwise.
		 * @param int  $post_id    The post ID.
		 * @param int  $user_id    The user ID.
		 */
		return apply_filters( 'geodir_post_can_delete', true, $post_id, $user_id );
	}

	/**
	 * Check if logged-out user is authorized via cookie.
	 *
	 * Verifies the temporary cookie-based authorization system for users
	 * who are allowed to post without being logged in.
	 *
	 * @param array $post_data Post data array containing post information.
	 * @return bool True if logged-out user is authorized, false otherwise.
	 */
	public function check_logged_out_author( array $post_data ): bool {
		if ( ! isset( $post_data['ID'] ) || ! $post_data['ID'] ) {
			return false;
		}

		$post_id = absint( $post_data['ID'] );
		$post    = get_post( $post_id );

		if ( empty( $post ) || ! in_array( $post->post_status, [ 'auto-draft', 'draft' ], true ) ) {
			return false;
		}

		// Check if logged-out posting is enabled.
		if ( ! geodir_get_option( 'post_logged_out' ) ) {
			return false;
		}

		// Verify cookie ownership.
		return $this->is_owner( $post_id, 0 );
	}

	/**
	 * Check if user can create a post of given type (REST API version).
	 *
	 * Returns WP_Error for use in REST API responses.
	 *
	 * @param string   $post_type The post type to check.
	 * @param int|null $user_id   User ID to check. Null for current user.
	 * @return true|WP_Error True if can create, WP_Error otherwise.
	 */
	public function can_create( string $post_type, ?int $user_id = null ): bool|WP_Error {
		if ( $user_id === null ) {
			$user_id = get_current_user_id();
		}

		// Check if it's a GeoDirectory post type.
		if ( ! function_exists( 'geodir_is_gd_post_type' ) || ! geodir_is_gd_post_type( $post_type ) ) {
			return new WP_Error(
				'invalid_post_type',
				__( 'Invalid post type.', 'geodirectory' ),
				array( 'status' => 400 )
			);
		}

		$post_type_object = get_post_type_object( $post_type );
		if ( ! $post_type_object ) {
			return new WP_Error(
				'invalid_post_type',
				__( 'Invalid post type.', 'geodirectory' ),
				array( 'status' => 400 )
			);
		}

		// Logged-in user.
		if ( $user_id > 0 ) {
			// Check capability.
			if ( ! current_user_can( $post_type_object->cap->create_posts ) ) {
				return new WP_Error(
					'cannot_create',
					__( 'You do not have permission to create this listing.', 'geodirectory' ),
					array( 'status' => 403 )
				);
			}

			// Check post limits.
			$limit_check = $this->check_post_limit( $post_type, $user_id );
			if ( is_wp_error( $limit_check ) ) {
				return $limit_check;
			}

			return true;
		}

		// Guest user - check if guest posting is enabled.
		if ( ! $this->settings->get( 'post_logged_out' ) || ! get_option( 'users_can_register' ) ) {
			return new WP_Error(
				'login_required',
				__( 'You must be logged in to create a listing.', 'geodirectory' ),
				array( 'status' => 401 )
			);
		}

		// Guest posting is allowed.
		return true;
	}

	/**
	 * Check if user can edit post (REST API version).
	 *
	 * Returns WP_Error for use in REST API responses.
	 *
	 * @param int      $post_id   Post ID to check.
	 * @param int|null $user_id   User ID to check. Null for current user.
	 * @param int      $parent_id Optional. Parent post ID for revision checks.
	 * @return true|WP_Error True if can edit, WP_Error otherwise.
	 */
	public function can_edit_api( int $post_id, ?int $user_id = null, int $parent_id = 0 ): bool|WP_Error {
		if ( $user_id === null ) {
			$user_id = get_current_user_id();
		}

		if ( $this->can_edit( $post_id, $user_id, $parent_id ) ) {
			return true;
		}

		return new WP_Error(
			'cannot_edit',
			__( 'You do not have permission to edit this listing.', 'geodirectory' ),
			array( 'status' => 403 )
		);
	}

	/**
	 * Check if user can delete post (REST API version).
	 *
	 * Returns WP_Error for use in REST API responses.
	 *
	 * @param int      $post_id Post ID to check.
	 * @param int|null $user_id User ID to check. Null for current user.
	 * @return true|WP_Error True if can delete, WP_Error otherwise.
	 */
	public function can_delete_api( int $post_id, ?int $user_id = null ): bool|WP_Error {
		if ( $user_id === null ) {
			$user_id = get_current_user_id();
		}

		if ( $this->can_delete( $post_id, $user_id ) ) {
			return true;
		}

		return new WP_Error(
			'cannot_delete',
			__( 'You do not have permission to delete this listing.', 'geodirectory' ),
			array( 'status' => 403 )
		);
	}

	/**
	 * Check post limits for user.
	 *
	 * @param string $post_type  The post type.
	 * @param int    $user_id    The user ID.
	 * @param int    $package_id Optional. Package ID to check limits for.
	 * @return true|WP_Error True if within limits, WP_Error otherwise.
	 */
	public function check_post_limit( string $post_type, int $user_id, int $package_id = 0 ): bool|WP_Error {
		// Check if post limit class exists.
		if ( ! class_exists( 'GeoDir_Post_Limit' ) ) {
			return true;
		}

		$args = array(
			'post_type' => $post_type,
			'post_author' => $user_id,
		);

		if ( $package_id > 0 ) {
			$args['package_id'] = $package_id;
		}

		$can_add = \GeoDir_Post_Limit::user_can_add_post( $args, true );

		if ( is_wp_error( $can_add ) ) {
			return new WP_Error(
				'post_limit_reached',
				$can_add->get_error_message(),
				array( 'status' => 403 )
			);
		}

		return true;
	}

	/**
	 * Get guest user cookie value.
	 *
	 * @return string|null Cookie value or null if not set.
	 */
	public function get_guest_cookie(): ?string {
		if ( ! function_exists( 'geodir_getcookie' ) ) {
			return null;
		}

		$cookie = geodir_getcookie( '_gd_logged_out_post_author' );
		return $cookie ?: null;
	}

	/**
	 * Set guest user cookie.
	 *
	 * Creates a secure random hash and stores it in a cookie for guest user identification.
	 *
	 * @return string The cookie value.
	 */
	public function set_guest_cookie(): string {
		// Check if cookie already exists.
		$existing = $this->get_guest_cookie();
		if ( $existing ) {
			return $existing;
		}

		// Generate secure random hash.
		$hash = wp_generate_password( 32, false );

		// Set cookie (24 hour expiry).
		if ( function_exists( 'geodir_setcookie' ) ) {
			geodir_setcookie( '_gd_logged_out_post_author', $hash, time() + DAY_IN_SECONDS );
		}

		return $hash;
	}
}
