<?php
// AYE-CODE-GEMINI: REFACTORED CPT_Settings_Manager

namespace AyeCode\GeoDirectory\Admin;

use AyeCode\GeoDirectory\Admin\Pages\DynamicCptSettings;
use AyeCode\GeoDirectory\Core\Lifecycle;

final class CptSettingsManager {

	public function init(): void {
		$this->create_settings_pages();
	}

	public function create_settings_pages(): void {
		// Get all public CPTs created by GeoDirectory.
		// You might need to adjust this logic to get the specific CPTs you want.
		$post_types = \geodir_get_posttypes( 'object' );

		// This filter is important! It lets you (or other addons) exclude certain CPTs.
		// For example: unset( $post_types['gd_event'] );
		$post_types = apply_filters( 'geodir_cpt_with_settings_pages', $post_types );

		if ( empty( $post_types ) ) {
			return;
		}

		foreach ( $post_types as $cpt_slug => $cpt ) {
			new DynamicCptSettings( $cpt_slug, $cpt );
		}
	}

	/**
	 * Gets the settings for a SINGLE post type.
	 *
	 * @param string $post_type The post type slug (e.g., 'gd_place').
	 * @return array|null The settings for the specified CPT, or null if not found.
	 */
	public static function get_cpt_settings( string $post_type ): ?array {
		$all_settings = self::get_all_settings();
		return $all_settings[ $post_type ] ?? null;
	}

	/**
	 * Updates the settings for a SINGLE post type in the database.
	 *
	 * @param string $post_type The post type slug being updated.
	 * @param array  $new_data  The new settings data for this CPT.
	 * @return bool True on success, false on failure.
	 */
	public static function update_cpt_settings( string $post_type, array $new_data ): bool {
		// First, get all current CPT settings.
		$all_settings = self::get_all_settings();

		// Sanitize and update the data for the specified post type.
		$all_settings[ $post_type ] = self::sanitize_cpt( $post_type, $new_data );

		// Now, save the entire block of CPT settings back to the master option.
		return self::save_all_settings( $all_settings );
	}

	/**
	 * Gets ALL CPT settings, applying defaults on the first run.
	 * Useful for system-wide operations like registering post types.
	 *
	 * @return array The complete array of all CPT settings.
	 */
	public static function get_all_settings(): array {
		$cpt_settings   =  geodirectory()->settings->get( 'post_types', [] );

		if ( empty( $cpt_settings ) ) {
			return self::get_defaults();
		}

		return $cpt_settings;
	}

	/**
	 * Private helper to save the entire CPT settings array.
	 *
	 * @param array $all_cpts_data The complete array of all CPT settings.
	 * @return bool True on success.
	 */
	private static function save_all_settings( array $all_cpts_data ): bool {

		$success = geodirectory()->settings->update( 'post_types', $all_cpts_data );

		if ( $success ) {
			// ... (post-save actions like hooks and cache clearing)
			foreach ( $all_cpts_data as $post_type => $args ) {
				do_action( 'geodir_post_type_saved', $post_type, $args );
			}
			if ( function_exists('geodir_cache_flush_group') ) {
				geodir_cache_flush_group( 'geodir_cpt_templates' );
			}
		}

		return $success;
	}

	// ... (private sanitize_cpt and get_defaults methods remain the same)

	private static function sanitize_cpt( string $cpt_slug, array $data ): array {
		// ... (same logic as before)
		$output = [];
		$slug   = sanitize_key( $data['has_archive'] ?? $cpt_slug );
		$name   = sanitize_text_field( $data['labels']['name'] ?? '' );
		$singular_name = sanitize_text_field( $data['labels']['singular_name'] ?? '' );

		$output['labels'] = [
			'name'               => $name,
			'singular_name'      => $singular_name,
			'add_new'            => sanitize_text_field( $data['labels']['add_new'] ?? '' ),
			'add_new_item'       => sanitize_text_field( $data['labels']['add_new_item'] ?? "Add New {$singular_name}" ),
			'edit_item'          => sanitize_text_field( $data['labels']['edit_item'] ?? "Edit {$singular_name}" ),
			'new_item'           => sanitize_text_field( $data['labels']['new_item'] ?? "New {$singular_name}" ),
			'view_item'          => sanitize_text_field( $data['labels']['view_item'] ?? "View {$singular_name}" ),
			'search_items'       => sanitize_text_field( $data['labels']['search_items'] ?? "Search {$name}" ),
			'not_found'          => sanitize_text_field( $data['labels']['not_found'] ?? "No {$name} found" ),
			'not_found_in_trash' => sanitize_text_field( $data['labels']['not_found_in_trash'] ?? "No {$name} found in trash" ),
			'listing_owner'      => sanitize_text_field( $data['labels']['listing_owner'] ?? '' ),
		];

		$output['description']     = trim( $data['description'] ?? '' );
		$output['public']          = true;
		$output['has_archive']     = $slug;
		$output['hierarchical']    = false;
		$output['supports']        = $data['supports'] ?? ['title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'comments', 'revisions'];
		$output['capability_type'] = 'post';
		$output['rewrite']         = ['slug' => $slug, 'with_front' => false, 'hierarchical' => true, 'feeds' => true ];
		$output['taxonomies']      = [$cpt_slug . "category", $cpt_slug . "_tags"];
		$output['menu_icon']       = sanitize_text_field($data['menu_icon'] ?? 'dashicons-location-alt');

		$output['listing_order']          = absint( $data['listing_order'] ?? 0 );
		$output['default_image']          = esc_url_raw( $data['default_image'] ?? '' );
		$output['disable_comments']       = absint( $data['disable_comments'] ?? 0 );
		$output['disable_reviews']        = absint( $data['disable_reviews'] ?? 0 );
		$output['single_review']          = absint( $data['single_review'] ?? 0 );
		$output['disable_favorites']      = absint( $data['disable_favorites'] ?? 0 );
		$output['disable_frontend_add']   = absint( $data['disable_frontend_add'] ?? 0 );
		$output['author_posts_private']   = absint( $data['author_posts_private'] ?? 0 );
		$output['author_favorites_private'] = absint( $data['author_favorites_private'] ?? 0 );
		$output['limit_posts']            = !empty($data['limit_posts']) ? absint( $data['limit_posts']) :'';

		$output['seo']['title']            = sanitize_text_field( $data['seo']['title'] ?? '' );
		$output['seo']['meta_title']       = sanitize_text_field( $data['seo']['meta_title'] ?? '' );
		$output['seo']['meta_description'] = sanitize_text_field( $data['seo']['meta_description'] ?? '' );

		$output['page_add']          = absint( $data['page_add'] ?? 0 );
		$output['page_details']      = absint( $data['page_details'] ?? 0 );
		$output['page_archive']      = absint( $data['page_archive'] ?? 0 );
		$output['page_archive_item'] = absint( $data['page_archive_item'] ?? 0 );

		return apply_filters('geodir_save_post_type', $output, $cpt_slug, $data);
	}

	private static function get_defaults(): array {
		// ... (same logic as before)
		$listing_slug = 'places';
		$labels = [
			'name'               => __( 'Places', 'geodirectory' ),
			'singular_name'      => __( 'Place', 'geodirectory' ),
			'add_new'            => __( 'Add New', 'geodirectory' ),
			'add_new_item'       => __( 'Add New Place', 'geodirectory' ),
			'edit_item'          => __( 'Edit Place', 'geodirectory' ),
			'new_item'           => __( 'New Place', 'geodirectory' ),
			'view_item'          => __( 'View Place', 'geodirectory' ),
			'search_items'       => __( 'Search Places', 'geodirectory' ),
			'not_found'          => __( 'No Place Found', 'geodirectory' ),
			'not_found_in_trash' => __( 'No Place Found In Trash', 'geodirectory' )
		];

		$place_default = [
			'labels'          => $labels,
			'can_export'      => true,
			'capability_type' => 'post',
			'description'     => 'Place post type.',
			'has_archive'     => $listing_slug,
			'hierarchical'    => false,
			'map_meta_cap'    => true,
			'menu_icon'       => 'dashicons-location-alt',
			'public'          => true,
			'query_var'       => true,
			'rewrite'         => [
				'slug'         => $listing_slug,
				'with_front'   => false,
				'hierarchical' => true,
				'feeds'        => true
			],
			'supports'        => [ 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'comments', 'revisions' ],
			'taxonomies'      => [ 'gd_placecategory', 'gd_place_tags' ]
		];

		return ['gd_place' => $place_default];
	}
}
