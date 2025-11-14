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
}
