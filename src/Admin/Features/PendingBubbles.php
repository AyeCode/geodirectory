<?php
/**
 * GeoDirectory Pending Bubbles Feature
 *
 * @package GeoDirectory\Admin\Features
 * @since   3.0.0
 * @author  AyeCode Ltd
 */

// Use strict types for better code quality.
declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Admin\Features;

/**
 * Adds "pending count" notification bubbles to the GeoDirectory CPT admin menus.
 *
 * @since 3.0.0
 */
final class PendingBubbles {
	/**
	 * Registers the necessary WordPress hooks for this feature.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		// This filter allows us to add custom classes and content to menu items.
		add_filter( 'add_menu_classes', [ $this, 'add_bubbles' ] );
	}

	/**
	 * Scans the admin menu for GeoDirectory CPTs and adds a pending count bubble.
	 *
	 * @param array $menu The unfiltered admin menu array.
	 *
	 * @return array The filtered admin menu array.
	 */
	public function add_bubbles( array $menu ): array {
		if ( empty( $menu ) ) {
			return $menu;
		}

		// @todo These dependencies should be injected via the constructor in the future.
		$cpts   = function_exists( 'geodir_get_posttypes' ) ? (array) geodir_get_posttypes() : [];
		$counts = wp_cache_get( 'geodir_post_counts', 'geodirectory' );

		// If counts are not in the cache, we generate and set them.
		if ( $counts === false ) {
			$counts = [];
			foreach ( $cpts as $cpt ) {
				$post_counts = wp_count_posts( $cpt, 'readable' );
				$counts[ $cpt ] = $post_counts->pending ?? 0;
			}
			wp_cache_set( 'geodir_post_counts', $counts, 'geodirectory' );
		}

		if ( ! $counts ) {
			return $menu;
		}

		// Loop through the menu to find our CPTs.
		foreach ( $menu as $i => $data ) {
			// Check if the menu item URL matches the pattern for a GeoDirectory CPT.
			if ( isset( $data[2] ) && str_starts_with( (string) $data[2], 'edit.php?post_type=gd_' ) ) {
				$parts = explode( '=', (string) $data[2] );
				$pt    = $parts[1] ?? '';

				if ( $pt && isset( $counts[ $pt ] ) && in_array( $pt, $cpts, true ) ) {
					$count = (int) $counts[ $pt ];
					if ( $count > 0 ) {
						// Append the bubble HTML to the menu title.
						$bubble_html = " <span class='awaiting-mod count-{$count}'><span class='pending-count'>" . number_format_i18n( $count ) . '</span></span>';
						$menu[ $i ][0] .= $bubble_html;
					}
				}
			}
		}

		return $menu;
	}
}
