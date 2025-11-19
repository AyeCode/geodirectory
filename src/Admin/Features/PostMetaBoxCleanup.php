<?php
/**
 * GeoDirectory Post Meta Box Cleanup Feature
 *
 * @package GeoDirectory\Admin\Features
 * @since   3.0.0
 * @author  AyeCode Ltd
 */

// Use strict types for better code quality.
declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Admin\Features;

/**
 * Removes default WordPress meta boxes from GeoDirectory CPTs.
 *
 * This class handles the removal of WP's default featured image,
 * revisions, author, and taxonomy meta boxes on GD post types.
 *
 * @since 3.0.0
 */
final class PostMetaBoxCleanup {
	/**
	 * Registers the necessary WordPress hooks for this feature.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		// Remove default WordPress meta boxes.
		add_action( 'do_meta_boxes', [ $this, 'remove_wp_meta_boxes' ] );

		// Remove default category/taxonomy meta boxes.
		add_action( 'admin_menu', [ $this, 'remove_taxonomy_meta_boxes' ] );
	}

	/**
	 * Removes default thumbnail, revisions, and author meta boxes on GD post types.
	 *
	 * @return void
	 */
	public function remove_wp_meta_boxes(): void {
		global $post;

		if ( empty( $post ) || ! $this->is_gd_post_type( $post->post_type ) ) {
			return;
		}

		$post_type = $post->post_type;

		// Remove featured image meta box (we have our own attachments box).
		remove_meta_box( 'postimagediv', $post_type, 'side' );

		// Remove revisions meta box.
		remove_meta_box( 'revisionsdiv', $post_type, 'normal' );

		// Remove author meta box (we have our own owner box).
		remove_meta_box( 'authordiv', $post_type, 'normal' );
	}

	/**
	 * Removes taxonomy meta boxes for GeoDirectory post types.
	 *
	 * GeoDirectory handles categories and tags through custom fields,
	 * so we remove the default WordPress taxonomy meta boxes.
	 *
	 * @return void
	 */
	public function remove_taxonomy_meta_boxes(): void {
		$post_types = $this->get_post_types();

		if ( empty( $post_types ) ) {
			return;
		}

		foreach ( $post_types as $post_type => $post_type_info ) {
			$taxonomies = $this->get_taxonomies( $post_type );

			if ( empty( $taxonomies ) ) {
				continue;
			}

			foreach ( $taxonomies as $taxonomy ) {
				// Remove the taxonomy meta box.
				remove_meta_box( $taxonomy . 'div', $post_type, 'normal' );
			}
		}
	}

	/**
	 * Gets the list of GeoDirectory post types.
	 *
	 * @return array Post types configuration array.
	 */
	private function get_post_types(): array {
		return function_exists( 'geodir_get_option' ) ? (array) geodir_get_option( 'post_types' ) : [];
	}

	/**
	 * Gets the list of taxonomies for a post type.
	 *
	 * @param string $post_type The post type.
	 *
	 * @return array List of taxonomy slugs.
	 */
	private function get_taxonomies( string $post_type ): array {
		return function_exists( 'geodir_get_taxonomies' ) ? (array) geodir_get_taxonomies( $post_type ) : [];
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
