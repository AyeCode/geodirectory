<?php
/**
 * Post Drafts Service
 *
 * Handles draft, revision, and auto-save operations for GeoDirectory posts.
 * Manages the temporary post creation and preview system used during post editing.
 *
 * @package GeoDirectory\Core\Services
 * @since   3.0.0
 * @author  AyeCode Ltd
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core\Services;

use WP_Error;

/**
 * Service for post draft and revision operations.
 *
 * @since 3.0.0
 */
final class PostDrafts {
	private PostPermissions $permissions;

	/**
	 * Constructor.
	 *
	 * @param PostPermissions $permissions PostPermissions service.
	 */
	public function __construct( PostPermissions $permissions ) {
		$this->permissions = $permissions;
	}

	/**
	 * Get user's auto-draft posts for a specific post type.
	 *
	 * Returns any auto-draft posts created by the user for the given post type.
	 * Used to continue editing a previously started listing.
	 *
	 * @param int    $user_id     The user ID. Use 0 for logged-out users.
	 * @param string $post_type   The post type to search for.
	 * @param int    $post_parent Optional. Parent post ID for revisions. Default 0.
	 * @return array Array of WP_Post objects, or empty array if none found.
	 */
	public function get_user_auto_drafts( int $user_id, string $post_type = '', int $post_parent = 0 ): array {
		if ( ! $post_type || ! geodir_is_gd_post_type( $post_type ) ) {
			return [];
		}

		$args = [
			'post_type'      => $post_type,
			'post_status'    => 'auto-draft',
			'posts_per_page' => 1,
			'orderby'        => 'modified',
			'order'          => 'DESC',
		];

		// For logged-in users, filter by author.
		if ( $user_id ) {
			$args['author'] = $user_id;
		}
		// For logged-out users, get all and filter by cookie later.
		else {
			$args['posts_per_page'] = 50; // Get more to search through.
		}

		// Filter by parent if provided.
		if ( $post_parent ) {
			$args['post_parent'] = $post_parent;
		}

		$posts = get_posts( $args );

		// For logged-out users, filter by cookie ownership.
		if ( ! $user_id && ! empty( $posts ) ) {
			$cookie_value = geodir_getcookie( '_gd_logged_out_post_author' );
			if ( $cookie_value ) {
				$posts = array_filter( $posts, function( $post ) use ( $cookie_value ) {
					$post_cookie = get_post_meta( $post->ID, '_gd_logged_out_post_author', true );
					return $post_cookie === $cookie_value;
				} );
				$posts = array_values( $posts ); // Re-index array.
			}
		}

		return $posts;
	}

	/**
	 * Create a new auto-draft post for editing.
	 *
	 * Creates an auto-draft post that will be used as a temporary container
	 * for the user's listing data while they're filling out the form.
	 *
	 * @param string $post_type The post type to create.
	 * @return object|null The created post object, or null on failure.
	 */
	public function create_auto_draft( string $post_type ): ?object {
		if ( ! $post_type || ! geodir_is_gd_post_type( $post_type ) ) {
			return null;
		}

		$user_id = get_current_user_id();

		// Get the CPT singular name for the title.
		$post_type_info = geodir_get_posttype_info( $post_type );
		$singular_name  = isset( $post_type_info['labels']['singular_name'] ) ? __( $post_type_info['labels']['singular_name'], 'geodirectory' ) : __( 'Listing', 'geodirectory' );

		$post_data = [
			'post_title'  => sprintf( __( 'Auto Draft %s', 'geodirectory' ), $singular_name ),
			'post_type'   => $post_type,
			'post_status' => 'auto-draft',
		];

		if ( $user_id ) {
			$post_data['post_author'] = $user_id;
		}

		/**
		 * Filter auto-draft post data before creation.
		 *
		 * @since 3.0.0
		 *
		 * @param array  $post_data Post data array.
		 * @param string $post_type The post type being created.
		 * @param int    $user_id   The user ID creating the post.
		 */
		$post_data = apply_filters( 'geodir_create_auto_draft_data', $post_data, $post_type, $user_id );

		$post_id = wp_insert_post( $post_data, true );

		if ( is_wp_error( $post_id ) ) {
			return null;
		}

		// For logged-out users, store cookie-based ownership.
		if ( ! $user_id ) {
			$cookie_value = geodir_getcookie( '_gd_logged_out_post_author' );
			if ( ! $cookie_value ) {
				$cookie_value = wp_generate_password( 32, false );
				geodir_setcookie( '_gd_logged_out_post_author', $cookie_value );
			}
			if ( $cookie_value ) {
				update_post_meta( $post_id, '_gd_logged_out_post_author', $cookie_value );
			}
		}

		return get_post( $post_id );
	}

	/**
	 * Get the preview/revision ID for a given parent post.
	 *
	 * When editing a post, WordPress creates a revision. This method finds
	 * the most recent revision for preview purposes.
	 *
	 * @param int $parent_id The parent post ID.
	 * @return int The revision ID if found, otherwise the parent ID.
	 */
	public function get_preview_id( int $parent_id ): int {
		if ( ! $parent_id ) {
			return 0;
		}

		global $wpdb;

		$sql = "SELECT {$wpdb->posts}.ID
			FROM {$wpdb->posts}
			WHERE 1=1
			AND {$wpdb->posts}.post_parent = %d
			AND {$wpdb->posts}.post_type = 'revision'
			AND {$wpdb->posts}.post_status = 'inherit'
			ORDER BY {$wpdb->posts}.post_date DESC, {$wpdb->posts}.ID DESC
			LIMIT 1";

		$post_id = $wpdb->get_var( $wpdb->prepare( $sql, $parent_id ) );

		return $post_id ? absint( $post_id ) : $parent_id;
	}

	/**
	 * Get the preview link for a post.
	 *
	 * Generates a preview URL that allows viewing a draft/pending post
	 * before it's published.
	 *
	 * @param object $post The post object to get preview link for.
	 * @return string The preview URL.
	 */
	public function get_preview_link( object $post ): string {
		if ( empty( $post->ID ) ) {
			return '';
		}

		$preview_link = get_permalink( $post->ID );
		$preview_link = add_query_arg( 'preview', 'true', $preview_link );

		// Create preview nonce for logged-out users.
		if ( ! get_current_user_id() ) {
			$cookie_value = geodir_getcookie( '_gd_logged_out_post_author' );
			if ( $cookie_value ) {
				$preview_nonce = wp_hash( $cookie_value . $post->ID . 'preview' );
				$preview_link  = add_query_arg( 'preview_nonce', $preview_nonce, $preview_link );
			}
		}

		/**
		 * Filter the post preview link.
		 *
		 * @since 3.0.0
		 *
		 * @param string $preview_link The preview URL.
		 * @param object $post         The post object.
		 */
		return apply_filters( 'geodir_post_preview_link', $preview_link, $post );
	}

	/**
	 * Delete a post revision or auto-draft.
	 *
	 * Permanently deletes a revision/draft post. Only deletes if user has permission.
	 *
	 * @param array $post_data Post data array containing 'ID' and optionally 'post_parent'.
	 * @return bool True if deleted successfully, false otherwise.
	 */
	public function delete_revision( array $post_data ): bool {
		if ( empty( $post_data['ID'] ) ) {
			return false;
		}

		$post_id = absint( $post_data['ID'] );
		$post    = get_post( $post_id );

		if ( ! $post ) {
			return false;
		}

		// Only allow deletion of revisions and auto-drafts.
		if ( ! in_array( $post->post_type, [ 'revision' ], true ) && ! in_array( $post->post_status, [ 'auto-draft' ], true ) ) {
			return false;
		}

		// Permission check - must own the post or its parent.
		$user_id   = get_current_user_id();
		$parent_id = wp_get_post_parent_id( $post_id );

		$has_permission = false;

		// Check if they own the revision/draft itself.
		$author_id = get_post_field( 'post_author', $post_id );
		if ( $user_id && $author_id == $user_id ) {
			$has_permission = true;
		}

		// Check if they own the parent.
		if ( ! $has_permission && $parent_id ) {
			$parent_author = get_post_field( 'post_author', $parent_id );
			if ( $user_id && $parent_author == $user_id ) {
				$has_permission = true;
			}
		}

		// Check logged-out cookie ownership.
		if ( ! $has_permission && ! $user_id ) {
			$cookie_value = geodir_getcookie( '_gd_logged_out_post_author' );
			$post_cookie  = get_post_meta( $post_id, '_gd_logged_out_post_author', true );
			if ( $cookie_value && $post_cookie === $cookie_value ) {
				$has_permission = true;
			}
		}

		if ( ! $has_permission ) {
			return false;
		}

		// Delete the post.
		$result = wp_delete_post( $post_id, true );

		return (bool) $result;
	}

	/**
	 * Get or create a post for the add listing form.
	 *
	 * This is a convenience method that handles the logic of finding an existing
	 * draft/revision or creating a new one for the add listing form.
	 *
	 * @param int    $post_id   Optional. Post ID for editing. Default 0.
	 * @param string $post_type The post type.
	 * @param int    $user_id   The user ID.
	 * @return array Array with 'post', 'post_id', 'post_parent', and 'user_notes'.
	 */
	public function get_or_create_for_form( int $post_id, string $post_type, int $user_id ): array {
		$post        = null;
		$post_parent = 0;
		$user_notes  = [];

		// Editing existing post.
		if ( $post_id ) {
			$post = geodir_get_post_info( $post_id );

			if ( ! $post ) {
				return [
					'post'        => null,
					'post_id'     => 0,
					'post_parent' => 0,
					'user_notes'  => [ 'gd-error' => __( 'Post not found.', 'geodirectory' ) ],
				];
			}

			// Check for existing revisions.
			$post_revisions = wp_get_post_revisions( $post_id, [
				'check_enabled' => false,
				'author'        => $user_id,
			] );

			if ( ! empty( $post_revisions ) ) {
				// Use existing revision.
				$revision    = reset( $post_revisions );
				$post_parent = $post_id;
				$post_id     = absint( $revision->ID );
				$post        = geodir_get_post_info( $post_id );

				$user_notes['has-revision'] = sprintf(
					__( 'Hey, we found some unsaved changes from earlier and are showing them below. If you would prefer to start again then please %sclick here%s to remove this revision.', 'geodirectory' ),
					"<a href='javascript:void(0)' onclick='geodir_delete_revision();'>",
					'</a>'
				);
			} else {
				// Create new revision.
				$revision_id = _wp_put_post_revision( $post );
				$post_parent = $post_id;
				$post_id     = absint( $revision_id );
				$post        = geodir_get_post_info( $post_id );
			}
		}
		// Creating new post.
		else {
			$auto_drafts = $this->get_user_auto_drafts( $user_id, $post_type );

			if ( ! empty( $auto_drafts ) && isset( $auto_drafts[0] ) ) {
				// Use existing auto-draft.
				$post        = $auto_drafts[0];
				$post_parent = 0;
				$post_id     = absint( $post->ID );
				$post        = geodir_get_post_info( $post_id );
				if ( $post->post_modified_gmt !== '0000-00-00 00:00:00' ) {
					$user_notes['has-auto-draft'] = sprintf(
						__( 'Hey, we found a post you started earlier and are showing it below. If you would prefer to start again then please %sclick here%s to remove this revision.', 'geodirectory' ),
						"<a href='javascript:void(0)' onclick='geodir_delete_revision();'>",
						'</a>'
					);
				}
			} else {
				// Create new auto-draft.
				$post = $this->create_auto_draft( $post_type );
				$post_id = $post ? absint( $post->ID ) : 0;
				if ( $post_id ) {
					$post = geodir_get_post_info( $post_id );
				}
			}
		}

		return [
			'post'        => $post,
			'post_id'     => $post_id,
			'post_parent' => $post_parent,
			'user_notes'  => $user_notes,
		];
	}

	/**
	 * Auto-save post data from REST API (with change detection).
	 *
	 * Only saves if changes are detected compared to current post data.
	 *
	 * @param int      $post_id     The post ID to autosave.
	 * @param array    $post_data   Post data to save.
	 * @param int|null $user_id     User ID. Null for current user.
	 * @return array|WP_Error Success data or WP_Error on failure.
	 */
	public function autosave( int $post_id, array $post_data, ?int $user_id = null ): array|WP_Error {
		if ( $user_id === null ) {
			$user_id = get_current_user_id();
		}

		// Permission check.
		$can_edit = $this->permissions->can_edit_api( $post_id, $user_id );
		if ( is_wp_error( $can_edit ) ) {
			return $can_edit;
		}

		$post = get_post( $post_id );
		if ( ! $post ) {
			return new WP_Error(
				'post_not_found',
				__( 'Post not found.', 'geodirectory' ),
				array( 'status' => 404 )
			);
		}

		// Check if we received a revision ID as input.
		$received_revision = false;
		$parent_id = 0;

		if ( $post->post_type === 'revision' && $post->post_parent ) {
			// We received a revision - update it directly.
			$received_revision = true;
			$parent_id = $post->post_parent;
			$parent_post = get_post( $parent_id );

			// make sure we dont set this post type
			unset( $post_data['post_type'] );

			// Use parent for change comparison but keep revision ID for updating.
			$compare_post = $parent_post;
		} elseif ( $post->post_parent && geodir_is_gd_post_type( get_post_type( $post->post_parent ) ) ) {
			// Post has a parent but is not a revision - switch to parent.
			$parent_id = $post->post_parent;
			$parent_post = get_post( $parent_id );
			if ( $parent_post ) {
				$post_id = $parent_id;
				$post = $parent_post;
			}
			$compare_post = $post;
		} else {
			$compare_post = $post;
		}

		// Check if changes exist (compare with current post).
		if ( ! $this->has_changes( $compare_post, $post_data ) ) {
			return array(
				'success' => true,
				'message' => __( 'No changes to save.', 'geodirectory' ),
				'autosaved' => false,
				'post_id' => $parent_id ? $parent_id : $post_id,
			);
		}

		// Handle revisions for published posts.
		$is_revision = false;

		if ( $received_revision ) {
			// We received a revision ID - just update it.
			$post_data['ID'] = $post_id;
			$is_revision = true;
		} elseif ( in_array( $post->post_status, array( 'publish', 'pending' ), true ) ) {
			// Create or get existing revision.
			$revision_id = wp_get_post_autosave( $post_id, $user_id );

			if ( $revision_id ) {
				// Update existing autosave.
				$post_data['ID'] = $revision_id->ID;
				$is_revision = true;
				$parent_id = $post_id;
			} else {
				// Create new revision.
				$post_data['post_parent'] = $post_id;
				$post_data['post_type'] = 'revision';
				$post_data['post_name'] = $post_id . '-autosave-v1';
				$post_data['post_status'] = 'inherit';
				$is_revision = true;
				$parent_id = $post_id;
			}
		} else {
			// For auto-draft/draft posts, update the post itself.
			$post_data['ID'] = $post_id;
		}

		// Store file metadata temporarily if present.
		$file_meta = array();
		if ( isset( $post_data['post_images'] ) ) {
			$file_meta['post_images'] = $post_data['post_images'];
			unset( $post_data['post_images'] );
		}

		// Apply autosave filter.
		$post_data = apply_filters( 'geodir_autosave_post_data', $post_data, $post_id );

		// Save the post.
		if ( isset( $post_data['ID'] ) ) {
			$result = wp_update_post( $post_data, true );
		} else {
			$result = wp_insert_post( $post_data, true );
		}

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Store file meta on parent post if it's a revision.
		if ( ! empty( $file_meta ) && $parent_id ) {
			update_post_meta( $parent_id, '__' . $result, $file_meta );
		}

		// Clean up old autosaves (keep only latest one).
		$this->cleanup_old_autosaves( $parent_id ?: $post_id, 1 );

		/**
		 * Fires after successful autosave.
		 *
		 * @since 3.0.0
		 *
		 * @param int   $result     The saved post ID.
		 * @param array $post_data  The post data.
		 * @param bool  $is_revision True if saved as revision.
		 */
		do_action( 'geodir_post_autosaved', $result, $post_data, $is_revision );

		return array(
			'success' => true,
			'message' => __( 'Draft saved.', 'geodirectory' ),
			'autosaved' => true,
			'post_id' => $result,
			'parent_id' => $parent_id,
		);
	}

	/**
	 * Check if post data has changes compared to existing post.
	 *
	 * @param \WP_Post $post      Current post object.
	 * @param array    $post_data New post data.
	 * @return bool True if changes detected, false otherwise.
	 */
	private function has_changes( \WP_Post $post, array $post_data ): bool {
		// Check title.
		if ( isset( $post_data['post_title'] ) && $post_data['post_title'] !== $post->post_title ) {
			return true;
		}

		// Check content.
		if ( isset( $post_data['post_content'] ) && $post_data['post_content'] !== $post->post_content ) {
			return true;
		}

		// Check custom fields (basic check - can be enhanced).
		$gd_post = geodir_get_post_info( $post->ID );
		if ( $gd_post ) {
			foreach ( $post_data as $key => $value ) {
				// Skip WP core fields.
				if ( in_array( $key, array( 'ID', 'post_title', 'post_content', 'post_status', 'post_type', 'post_parent' ), true ) ) {
					continue;
				}

				// Check if custom field changed.
				if ( isset( $gd_post->$key ) && $gd_post->$key != $value ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Cleanup old autosaves for a post.
	 *
	 * @param int $post_id    The post ID.
	 * @param int $keep_count Number of autosaves to keep. Default 1.
	 * @return void
	 */
	public function cleanup_old_autosaves( int $post_id, int $keep_count = 1 ): void {
		if ( ! $post_id ) {
			return;
		}

		$revisions = wp_get_post_revisions( $post_id, array(
			'check_enabled' => false,
		) );

		if ( count( $revisions ) <= $keep_count ) {
			return;
		}

		// Keep the newest ones, delete the rest.
		$revisions = array_slice( $revisions, $keep_count, null, true );

		foreach ( $revisions as $revision ) {
			wp_delete_post_revision( $revision->ID );
		}
	}
}
