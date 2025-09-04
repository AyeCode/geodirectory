<?php

namespace AyeCode\GeoDirectory\Admin\Settings\Handlers;

use AyeCode\GeoDirectory\Admin\Settings\PersistenceHandlerInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class GeneralSettingsHandler
 *
 * Handles the saving and retrieving of general CPT settings.
 * These are typically simple key-value pairs stored in the WordPress options table.
 *
 * @package AyeCode\GeoDirectory\Admin\Settings\Handlers
 * @since 2.1.0
 */
class GeneralSettingsHandler implements PersistenceHandlerInterface {

	/**
	 * Retrieves general settings for a CPT.
	 *
	 * @param string $post_type The post type slug.
	 *
	 * @return array The general settings.
	 */
	public function get( string $post_type ): array {

		$settings  = $this->get_cpt_settings( $post_type );

//		print_r( $settings );exit;

		if ( ! empty( $settings ) ) {
			$settings = $this->formate_settings_for_page($post_type, $settings );
		}

		return $settings;

	}

	/**
	 * Saves general settings for a CPT.
	 *
	 * @param string $post_type The post type slug.
	 * @param array  $data      The settings data to save.
	 *
	 * @return bool Result of the update_option call.
	 */
	public function save( string $post_type, array $data ): bool {



		// First, get all current CPT settings.
		$all_settings = $this->get_all_settings();

		// Sanitize and update the data for the specified post type.
		$all_settings[ $post_type ] = $this->sanitize_cpt( $post_type, $data );

//		echo $post_type.'###';print_r($data);
//		print_r( $all_settings[ $post_type ] );
//		exit;

		// Now, save the entire block of CPT settings back to the master option.
		return $this->save_all_settings( $all_settings );
	}

	/**
	 * Private helper to save the entire CPT settings array.
	 *
	 * @param array $all_cpts_data The complete array of all CPT settings.
	 * @return bool True on success.
	 */
	private function save_all_settings( array $all_cpts_data ): bool {

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

	/**
	 * @param string $cpt_slug
	 * @param array $data
	 *
	 * @return array
	 */
	private function sanitize_cpt( string $cpt_slug, array $data ): array {
		// ... (same logic as before)
		$output = [];
		$slug   = sanitize_key( $data['has_archive'] ?? $cpt_slug );
		$name   = sanitize_text_field( $data['name'] ?? '' );
		$singular_name = sanitize_text_field( $data['singular_name'] ?? '' );

		$output['labels'] = [
			'name'               => $name,
			'singular_name'      => $singular_name,
			'add_new'            => sanitize_text_field( $data['add_new'] ?? '' ),
			'add_new_item'       => sanitize_text_field( $data['add_new_item'] ?? "Add New {$singular_name}" ),
			'edit_item'          => sanitize_text_field( $data['edit_item'] ?? "Edit {$singular_name}" ),
			'new_item'           => sanitize_text_field( $data['new_item'] ?? "New {$singular_name}" ),
			'view_item'          => sanitize_text_field( $data['view_item'] ?? "View {$singular_name}" ),
			'search_items'       => sanitize_text_field( $data['search_items'] ?? "Search {$name}" ),
			'not_found'          => sanitize_text_field( $data['not_found'] ?? "No {$name} found" ),
			'not_found_in_trash' => sanitize_text_field( $data['not_found_in_trash'] ?? "No {$name} found in trash" ),
			'listing_owner'      => sanitize_text_field( $data['listing_owner'] ?? '' ),
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

		$output['listing_order']          = absint( $data['order'] ?? 0 );
		$output['default_image']          = esc_url_raw( $data['default_image'] ?? '' );
		$output['disable_comments']       = absint( $data['disable_comments'] ?? 0 );
		$output['disable_reviews']        = absint( $data['disable_reviews'] ?? 0 );
		$output['single_review']          = absint( $data['single_review'] ?? 0 );
		$output['disable_favorites']      = absint( $data['disable_favorites'] ?? 0 );
		$output['disable_frontend_add']   = absint( $data['disable_frontend_add'] ?? 0 );
		$output['author_posts_private']   = absint( $data['author_posts_private'] ?? 0 );
		$output['author_favorites_private'] = absint( $data['author_favorites_private'] ?? 0 );
		$output['limit_posts']            = !empty($data['limit_posts']) ? absint( $data['limit_posts']) :'';

		$output['seo']['title']            = sanitize_text_field( $data['title'] ?? '' );
		$output['seo']['meta_title']       = sanitize_text_field( $data['meta_title'] ?? '' );
		$output['seo']['meta_description'] = sanitize_text_field( $data['meta_description'] ?? '' );

		$output['page_add']          = absint( $data['page_add'] ?? 0 );
		$output['page_details']      = absint( $data['page_details'] ?? 0 );
		$output['page_archive']      = absint( $data['page_archive'] ?? 0 );
		$output['page_archive_item'] = absint( $data['page_archive_item'] ?? 0 );

		return apply_filters('geodir_save_post_type', $output, $cpt_slug, $data);
	}

	/**
	 * Gets the settings for a SINGLE post type.
	 *
	 * @param string $post_type The post type slug (e.g., 'gd_place').
	 * @return array|null The settings for the specified CPT, or null if not found.
	 */
	public function get_cpt_settings( string $post_type ): ?array {
		$all_settings = $this->get_all_settings();
		return $all_settings[ $post_type ] ?? null;
	}

	/**
	 * Gets ALL CPT settings, applying defaults on the first run.
	 * Useful for system-wide operations like registering post types.
	 *
	 * @return array The complete array of all CPT settings.
	 */
	public function get_all_settings(): array {
		$cpt_settings   =  geodirectory()->settings->get( 'post_types', [] );

		if ( empty( $cpt_settings ) ) {
			return $this->get_defaults();
		}

		return $cpt_settings;
	}

	public function formate_settings_for_page($post_type, $post_type_option ): array {

		if ( ! empty( $post_type_option ) ) {

			$post_type_option = apply_filters( 'geodir_cpt_settings_cpt_options', $post_type_option, $post_type );

			$post_type_labels = ! empty( $post_type_option['labels'] ) && is_array( $post_type_option['labels'] ) ? $post_type_option['labels'] : array();

			$post_type_values = $post_type_option;
			if ( ! empty( $post_type_labels ) ) {
				$post_type_values = array_merge( $post_type_labels, $post_type_values );
			}

			$post_type_values = wp_parse_args( $post_type_values, array(
				'post_type'             => $post_type,
				'slug'                  => ( ! empty( $post_type_option['has_archive'] ) ? $post_type_option['has_archive'] : '' ),
				'menu_icon'             => '',
				'description'           => '',

				// Labels
				'name'                  => '',
				'singular_name'         => '',
				'add_new'               => '',
				'add_new_item'          => '',
				'edit_item'             => '',
				'new_item'              => '',
				'view_item'             => '',
				'view_items'            => '',
				'search_items'          => '',
				'not_found'             => '',
				'not_found_in_trash'    => '',
				'listing_owner'         => '',
				'parent_item_colon'     => '',
				'all_items'             => '',
				'archives'              => '',
				'attributes'            => '',
				'insert_into_item'      => '',
				'uploaded_to_this_item' => '',
				'featured_image'        => '',
				'set_featured_image'    => '',
				'remove_featured_image' => '',
				'use_featured_image'    => '',
				'filter_items_list'     => '',
				'items_list_navigation' => '',
				'items_list'            => '',

				'default_image'            => '',
				'disable_reviews'          => '0',
				'disable_favorites'        => '0',
				'disable_frontend_add'     => '0',
				// author
				'author_posts_private'     => '0',
				'author_favorites_private' => '0',
				'limit_posts'              => '',
				// Page template
				'page_add'                 => '0',
				'page_details'             => '0',
				'page_archive'             => '0',
				'page_archive_item'        => '0',
				'template_add'             => '0',
				'template_details'         => '0',
				'template_archive'         => '0'
			) );

			$post_type_values['order'] = ( isset( $post_type_option['listing_order'] ) ? $post_type_option['listing_order'] : '' );

			// SEO
			$post_type_values['title']            = ( ! empty( $post_type_option['seo']['title'] ) ? $post_type_option['seo']['title'] : '' );
			$post_type_values['meta_title']       = ( ! empty( $post_type_option['seo']['meta_title'] ) ? $post_type_option['seo']['meta_title'] : '' );
			$post_type_values['meta_description'] = ( ! empty( $post_type_option['seo']['meta_description'] ) ? $post_type_option['seo']['meta_description'] : '' );

			unset( $post_type_values['labels'] );
			unset( $post_type_values['rewrite'] );
			unset( $post_type_values['supports'] );
			unset( $post_type_values['taxonomies'] );
			unset( $post_type_values['seo'] );
		}

		return $post_type_values;
	}

	private function get_defaults(): array {
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
