<?php
/**
 * GeoDirectory Post List Widgets Feature
 *
 * @package GeoDirectory\Admin\Features
 * @since   3.0.0
 * @author  AyeCode Ltd
 */

// Use strict types for better code quality.
declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Admin\Features;

/**
 * Loads GeoDirectory widgets on CPT list pages.
 *
 * This class ensures that specific GD widgets (like the Post Images widget)
 * are loaded on the post list admin pages.
 *
 * @since 3.0.0
 */
final class PostListWidgets {
	/**
	 * Registers the necessary WordPress hooks for this feature.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		// Filter to exclude edit.php from pagenow exclude list.
		add_filter( 'sd_pagenow_exclude', [ $this, 'sd_pagenow_exclude' ], 10, 1 );

		// Filter to load specific widgets on CPT list pages.
		add_filter( 'geodir_get_widgets', [ $this, 'sd_get_widgets' ], 99, 1 );
	}

	/**
	 * Load GD widgets on CPT list page.
	 *
	 * This removes edit.php from the pagenow exclude list when viewing
	 * a GeoDirectory post type, allowing widgets to load.
	 *
	 * @param array $pagenow_exclude Exclude pagenow list.
	 *
	 * @return array Filtered pagenow list.
	 */
	public function sd_pagenow_exclude( array $pagenow_exclude ): array {
		global $pagenow;

		if ( $pagenow !== 'edit.php' || empty( $pagenow_exclude ) ) {
			return $pagenow_exclude;
		}

		// Check if we're on a GD post type list page.
		$post_type = isset( $_REQUEST['post_type'] ) ? sanitize_text_field( $_REQUEST['post_type'] ) : '';
		if ( empty( $post_type ) || ! $this->is_gd_post_type( $post_type ) ) {
			return $pagenow_exclude;
		}

		// Remove edit.php from the exclude list.
		$key = array_search( 'edit.php', $pagenow_exclude, true );
		if ( $key !== false ) {
			unset( $pagenow_exclude[ $key ] );
		}

		return $pagenow_exclude;
	}

	/**
	 * Load images widget on CPT list page.
	 *
	 * This ensures the Post Images widget is loaded on GD CPT list pages.
	 *
	 * @param array $widgets Widget list.
	 *
	 * @return array GD widgets to load on edit.php page.
	 */
	public function sd_get_widgets( array $widgets ): array {
		global $pagenow;

		if ( $pagenow !== 'edit.php' ) {
			return $widgets;
		}

		// Check if we're on a GD post type list page.
		$post_type = isset( $_REQUEST['post_type'] ) ? sanitize_text_field( $_REQUEST['post_type'] ) : '';
		if ( empty( $post_type ) || ! $this->is_gd_post_type( $post_type ) ) {
			return $widgets;
		}

		// Load only the Post Images widget on this page.
		return [ 'GeoDir_Widget_Post_Images' ];
	}

	/**
	 * Checks if a post type is a GeoDirectory post type.
	 *
	 * @param string $post_type The post type to check.
	 *
	 * @return bool True if it's a GD post type.
	 */
	private function is_gd_post_type( string $post_type ): bool {
		return function_exists( 'geodir_is_gd_post_type' ) && geodir_is_gd_post_type( $post_type );
	}
}
