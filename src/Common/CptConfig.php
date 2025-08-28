<?php
/**
 * CPT Configuration
 *
 * Provides the configuration arrays for custom post types and taxonomies.
 * It will generate and save default configurations if none are present.
 *
 * @package GeoDirectory\Common
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Common;

use AyeCode\GeoDirectory\Core\Utils\Settings;

final class CptConfig {
	private Settings $settings;

	public function __construct( Settings $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Gets the post type registration arrays.
	 *
	 * @return array
	 */
	public function get_post_types(): array {
		$post_types = $this->settings->get( 'post_types', [] );

		if ( empty( $post_types ) ) {
			$listing_slug = 'places';
			$labels = [
				'name'               => 'Places',
				'singular_name'      => 'Place',
				'add_new'            => 'Add New',
				'add_new_item'       => 'Add New Place',
				'edit_item'          => 'Edit Place',
				'new_item'           => 'New Place',
				'view_item'          => 'View Place',
				'search_items'       => 'Search Places',
				'not_found'          => 'No Place Found',
				'not_found_in_trash' => 'No Place Found In Trash'
			];

			$post_types['gd_place'] = [
				'labels'       => $labels,
				'public'       => true,
				'has_archive'  => $listing_slug,
				'rewrite'      => [ 'slug' => $listing_slug, 'with_front' => false ],
				'supports'     => [ 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'revisions' ],
				'taxonomies'   => [ 'gd_placecategory', 'gd_place_tags' ],
				'menu_icon'    => 'dashicons-location-alt',
			];
			geodir_update_option( 'post_types', $post_types ); // Save defaults
		}
		return $post_types;
	}

	/**
	 * Gets the taxonomy registration arrays.
	 *
	 * @return array
	 */
	public function get_taxonomies(): array {
		$taxonomies = $this->settings->get( 'taxonomies', [] );
		$post_types = $this->get_post_types();

		if ( empty( $taxonomies ) ) {
			$listing_slug  = $post_types['gd_place']['rewrite']['slug'] ?? 'places';
			$singular_name = $post_types['gd_place']['labels']['singular_name'] ?? 'Place';

			// Default Place Category
			$taxonomies['gd_placecategory'] = [
				'object_type' => 'gd_place',
				'args'        => [
					'public'       => true,
					'hierarchical' => true,
					'rewrite'      => [ 'slug' => $listing_slug, 'with_front' => false, 'hierarchical' => true ],
					'query_var'    => true,
					'labels'       => $this->generate_taxonomy_labels( $singular_name, 'Category' )
				],
			];

			// Default Place Tags
			$taxonomies['gd_place_tags'] = [
				'object_type' => 'gd_place',
				'args'        => [
					'public'       => true,
					'hierarchical' => false,
					'rewrite'      => [ 'slug' => $listing_slug . '/tags', 'with_front' => false ],
					'query_var'    => true,
					'labels'       => $this->generate_taxonomy_labels( $singular_name, 'Tag', false )
				],
			];
			geodir_update_option( 'taxonomies', $taxonomies ); // Save defaults
		}

		return $taxonomies;
	}

	/**
	 * Helper to generate taxonomy labels dynamically.
	 *
	 * @param string $cpt_singular_name Singular name of the CPT (e.g., "Place").
	 * @param string $tax_singular_name Singular name of the Taxonomy (e.g., "Category" or "Tag").
	 * @param bool   $is_hierarchical   Is the taxonomy hierarchical?
	 * @return array The generated labels array.
	 */
	private function generate_taxonomy_labels( string $cpt_singular_name, string $tax_singular_name, bool $is_hierarchical = true ): array {
		$plural_name = $tax_singular_name . 's'; // Simple pluralization
		return [
			'name'              => sprintf( __( '%s %s', 'geodirectory' ), $cpt_singular_name, $plural_name ),
			'singular_name'     => sprintf( __( '%s %s', 'geodirectory' ), $cpt_singular_name, $tax_singular_name ),
			'search_items'      => sprintf( __( 'Search %s', 'geodirectory' ), $plural_name ),
			'all_items'         => sprintf( __( 'All %s', 'geodirectory' ), $plural_name ),
			'parent_item'       => $is_hierarchical ? sprintf( __( 'Parent %s', 'geodirectory' ), $tax_singular_name ) : null,
			'parent_item_colon' => $is_hierarchical ? sprintf( __( 'Parent %s:', 'geodirectory' ), $tax_singular_name ) : null,
			'edit_item'         => sprintf( __( 'Edit %s', 'geodirectory' ), $tax_singular_name ),
			'update_item'       => sprintf( __( 'Update %s', 'geodirectory' ), $tax_singular_name ),
			'add_new_item'      => sprintf( __( 'Add New %s', 'geodirectory' ), $tax_singular_name ),
			'new_item_name'     => sprintf( __( 'New %s Name', 'geodirectory' ), $tax_singular_name ),
			'menu_name'         => $plural_name,
		];
	}
}
