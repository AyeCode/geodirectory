<?php
/**
 * Post Statuses Service
 *
 * Manages all logic related to custom and core post statuses for GeoDirectory listings.
 *
 * @package GeoDirectory\Core
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core\Services;

final class Statuses {
	/**
	 * Gets the arguments needed to register the custom post statuses.
	 *
	 * This is used by the PostStatuses registrar class.
	 *
	 * @return array The array of status arguments for `register_post_status()`.
	 */
	public function get_registration_args(): array {
		$statuses = [
			'gd-closed' => [
				'label'                     => _x( 'Closed down', 'Listing status', 'geodirectory' ),
				'public'                    => false,
				'exclude_from_search'       => true,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Closed down <span class="count">(%s)</span>', 'Closed down <span class="count">(%s)</span>', 'geodirectory' ),
			]
		];

		return apply_filters( 'geodir_register_post_statuses', $statuses );
	}

	/**
	 * Gets a simple array of custom status keys and their labels.
	 *
	 * @param string $post_type The post type context.
	 * @return array A `[ 'status_key' => 'Status Label' ]` array.
	 */
	public function get_custom( string $post_type = '' ): array {
		$custom_statuses = [
			'gd-closed' => _x( 'Closed down', 'Listing status', 'geodirectory' )
		];

		return apply_filters( 'geodir_listing_custom_statuses', $custom_statuses, $post_type );
	}

	/**
	 * Gets a merged array of all WordPress and GeoDirectory statuses.
	 *
	 * @param string $post_type The post type context.
	 * @return array An array of all available post statuses.
	 */
	public function get_all( string $post_type = '' ): array {
		$default_statuses = get_post_statuses();
		$custom_statuses  = $this->get_custom( $post_type );

		$statuses = array_merge( $default_statuses, $custom_statuses );

		return apply_filters( 'geodir_post_statuses', $statuses );
	}

	/**
	 * Gets the list of statuses that are considered "published".
	 *
	 * @return array An array of status keys.
	 */
	public function get_publishable(): array {
		return apply_filters( 'geodir_get_publish_statuses', [ 'publish' ] );
	}

	/**
	 * Gets the list of statuses that are considered "pending".
	 *
	 * @return array An array of status keys.
	 */
	public function get_pending(): array {
		return apply_filters( 'geodir_get_pending_statuses', [ 'pending' ] );
	}

	/**
	 * Get a list of post statuses for a specific context.
	 *
	 * @param string $context The context (e.g., 'search', 'map', 'author-archive', 'widget-listings').
	 * @param array  $args    Optional arguments (e.g., ['post_type' => 'gd_place']).
	 * @return array Array of status keys for the given context.
	 */
	public function get_stati_for_context( string $context, array $args = [] ): array {
		$statuses         = [];
		$publish_statuses = $this->get_publishable();

		switch ( $context ) {
			case 'author-archive':
			case 'widget-listings-author':
				$custom_statuses = $this->get_registration_args();

				if ( ! empty( $custom_statuses ) ) {
					$publish_statuses = array_merge( $publish_statuses, array_keys( $custom_statuses ) );
				}

				$statuses = array_merge( $publish_statuses, [ 'pending', 'draft', 'private', 'future' ] );
				break;

			case 'search':
				$statuses = $publish_statuses;
				break;

			case 'single-map':
				$statuses = array_merge( $publish_statuses, [ 'pending', 'draft', 'inherit', 'auto-draft' ] );

				$non_public_statuses = $this->get_stati_for_context( 'non-public', $args );

				if ( ! empty( $non_public_statuses ) && is_array( $non_public_statuses ) ) {
					$statuses = array_merge( $statuses, $non_public_statuses );
				}
				break;

			case 'map':
				$statuses = $publish_statuses;
				break;

			case 'non-public':
				$custom_statuses = $this->get_registration_args();

				foreach ( $custom_statuses as $status => $data ) {
					if ( isset( $data['public'] ) && $data['public'] === false ) {
						$statuses[] = $status;
					}
				}
				break;

			case 'widget-listings':
				$statuses = $publish_statuses;

				if ( current_user_can( 'manage_options' ) ) {
					// Private posts can be shown to admins if needed (commented out for performance).
					// $statuses[] = 'private';
				}
				break;

			case 'import':
				$post_type = ! empty( $args['post_type'] ) ? $args['post_type'] : '';
				$statuses  = array_keys( $this->get_all( $post_type ) );
				break;

			case 'posts-count-live':
				$statuses = $publish_statuses;
				break;

			case 'posts-count-offline':
				$statuses = $this->get_stati_for_context( 'non-public', $args );
				$statuses = array_merge( $statuses, [ 'pending', 'draft', 'private', 'future' ] );
				break;

			case 'unpublished':
				$statuses = [ 'pending', 'draft', 'auto-draft', 'trash' ];
				break;

			default:
				$statuses = $publish_statuses;
				break;
		}

		/**
		 * Filter post statuses for a specific context.
		 *
		 * @since 2.1.1.5
		 * @since 3.0.0 Moved to Statuses service.
		 *
		 * @param array  $statuses Array of status keys.
		 * @param string $context  The context.
		 * @param array  $args     Optional arguments.
		 */
		$statuses = apply_filters( 'geodir_get_post_stati', $statuses, $context, $args );

		if ( ! empty( $statuses ) ) {
			$statuses = array_unique( $statuses );
		}

		return $statuses;
	}

	/**
	 * Get the nice name for a post status.
	 *
	 * @param string $status The status key.
	 * @return string The human-readable status name.
	 */
	public function get_status_name( string $status ): string {
		$statuses = $this->get_all();

		if ( ! empty( $statuses ) && isset( $statuses[ $status ] ) ) {
			$status_name = $statuses[ $status ];
		} else {
			$status_object = get_post_status_object( $status );
			if ( ! empty( $status_object->label ) ) {
				$status_name = $status_object->label;
			} else {
				$status_name = $status;
			}
		}

		return $status_name;
	}

	/**
	 * Check if a post is closed.
	 *
	 * @param object|int $post Post object or post ID.
	 * @return bool True if the post is closed, false otherwise.
	 */
	public function is_post_closed( $post ): bool {
		if ( empty( $post ) ) {
			return false;
		}

		$status = ! empty( $post->post_status ) ? $post->post_status : get_post_status( $post );
		$closed = $status === 'gd-closed';

		/**
		 * Filter whether a post is closed.
		 *
		 * @since 2.0.0
		 * @since 3.0.0 Moved to Statuses service.
		 *
		 * @param bool       $closed True if closed, false otherwise.
		 * @param object|int $post   Post object or post ID.
		 */
		return apply_filters( 'geodir_post_is_closed', $closed, $post );
	}
}
