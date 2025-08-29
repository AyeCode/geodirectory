<?php
/**
 * GeoDirectory Dynamic CPT Settings Page Handler
 */

namespace AyeCode\GeoDirectory\Admin\Pages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AyeCode\GeoDirectory\Admin\CPT_Settings_Manager;
use AyeCode\SettingsFramework\Settings_Framework;
use WP_Error;

final class Dynamic_CPT_Settings extends Settings_Framework {

	private $cpt;
	private $cpt_slug;

	public function __construct( $cpt_slug, $cpt_object ) {
		$this->cpt_slug = $cpt_slug;
		$this->cpt      = $cpt_object;
//		print_r( $cpt_object );exit;
		$this->option_name = 'geodir_' . $cpt_slug . '_settings';
		$this->parent_slug = 'edit.php?post_type=' . $cpt_slug;
		$this->page_slug   = $cpt_slug . '-settings';
		$this->page_title  = sprintf( __( '%s Settings', 'geodirectory' ), $this->cpt->labels->singular_name );
		$this->menu_title  = __( 'Settings', 'geodirectory' );
		$this->plugin_name = '<span class="fa-stack fa-1x me-1"><i class="fas fa-circle fa-stack-2x text-light"></i><i style="color: #ff8333 !important;" class="fas fa-globe-americas fa-stack-2x text-primary "></i></span> <span class="fw-normal fs-4"><span style="color: #ff8333 !important;">Geo</span>Directory</span>';

		parent::__construct();
	}

	protected function get_config() {
		// Define the list of settings files to be included.
		$settings_files = [
			'general' => 'config/cpt-settings/general.php',
			'fields'  => 'config/cpt-settings/fields.php',
			'sorting' => 'config/cpt-settings/sorting.php',
			'tabs'    => 'config/cpt-settings/tabs.php',
		];

		$sections = [];

		// Define the base path for the settings files.
		$base_path = dirname( __FILE__ ) . '/../';

		// Loop through the files, include them, and collect their returned section arrays.
		foreach ( $settings_files as $file_path ) {
			$full_path = $base_path . $file_path;
			if ( file_exists( $full_path ) ) {
				$sections[] = include( $full_path );
			}
		}

//		print_r( $sections );exit;
		// The final configuration array required by the framework.
		return [ 'sections' => $sections ];
	}

	/**
	 * Retrieves settings from the database.
	 *
	 * @return array Current settings.
	 */
	public function get_settings() {

		$post_type = $this->cpt_slug;
		$settings  = CPT_Settings_Manager::get_cpt_settings( $post_type );

//		print_r( $settings );exit;

		if ( ! empty( $settings ) ) {
			$settings = $this->formate_settings_for_page( $settings );
		}

		return $settings;
	}

	public function formate_settings_for_page( $post_type_option ): array {
		$post_type = $this->cpt_slug;
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
		}

		return $post_type_values;
	}

	/**
	 * Sanatize post type.
	 *
	 * @param array $raw {
	 *      An array sanatize post type.
	 *
	 * @type string $new_post_type New sanatize post type.
	 * @type string $name New post type name.
	 * @type string $singular_name New Post type singular name.
	 * @type string $slug New post type slug.
	 * }
	 *
	 * @return array $output.
	 *@since 2.0.0
	 *
	 */
	public function sanitize_post_type_for_save( array $raw ) {
		$output = array();

//		print_r( $raw );
		$post_types = CPT_Settings_Manager::get_all_settings();

//		print_r( $post_types );
		$raw = stripslashes_deep( $raw );
		$post_type = isset($raw['post_type']) && $raw['post_type'] ? str_replace("-","_",sanitize_key($raw['post_type'])) : '';
		$name = isset($raw['name']) && $raw['name'] ? sanitize_text_field($raw['name']) : null;
		$singular_name = isset($raw['singular_name']) && $raw['singular_name'] ? sanitize_text_field($raw['singular_name']) : null;
		//$slug = isset($raw['slug']) && $raw['slug'] ? str_replace("-","_",sanitize_key($raw['slug'])) : $post_type;
		$slug = isset($raw['slug']) && $raw['slug'] ? sanitize_key($raw['slug']) : $post_type;

		if ( ! $post_type || !$name || !$slug || ! $singular_name ) {
			return new WP_Error( 'invalid_post_type', __( 'Invalid or missing post type', 'geodirectory' ) );
		}

		// check the CPT is "gd_"prepended
		if ( strpos( $post_type, 'gd_' ) === 0 ) {
			// all good
		} else {
			$post_type = "gd_" . $post_type;
		}

//		if ( ! empty( $raw['post_type'] ) && ! empty( $post_types[ $raw['post_type'] ] ) ) {
//			return new WP_Error( 'invalid_post_type', __( 'Post type already exists.', 'geodirectory' ) );
//		}

		if ( ! empty( $post_types ) ) {
			foreach ( $post_types as $key => $data ) {
				if ( ! empty( $data['has_archive'] ) && $data['has_archive'] == $slug && $post_type != $key ) {
					return new WP_Error( 'invalid_post_type', __( 'Post type slug already exists.', 'geodirectory' ) );
				}
			}
		}

		// Set the labels
		$output[$post_type]['labels'] = array(
			'name' => $name,
			'singular_name' => $singular_name,
			'add_new' => isset($raw['add_new']) && $raw['add_new'] ? sanitize_text_field($raw['add_new']) : _x( 'Add New', $post_type, 'geodirectory' ),
			'add_new_item' => isset($raw['add_new_item']) && $raw['add_new_item'] ? sanitize_text_field($raw['add_new_item']) : __( 'Add New ' . $singular_name, 'geodirectory' ),
			'edit_item' => isset($raw['edit_item']) && $raw['edit_item'] ? sanitize_text_field($raw['edit_item']) : __( 'Edit ' . $singular_name, 'geodirectory' ),
			'new_item' => isset($raw['new_item']) && $raw['new_item'] ? sanitize_text_field($raw['new_item']) : __( 'New ' . $singular_name, 'geodirectory' ),
			'view_item' => isset($raw['view_item']) && $raw['view_item'] ? sanitize_text_field($raw['view_item']) : __( 'View ' . $singular_name, 'geodirectory' ),
			'search_items' => isset($raw['search_items']) && $raw['search_items'] ? sanitize_text_field($raw['search_items']) : __( 'Search ' . $name, 'geodirectory' ),
			'not_found' => isset($raw['not_found']) && $raw['not_found'] ? sanitize_text_field($raw['not_found']) : __( 'No ' . $name . ' found.', 'geodirectory' ),
			'not_found_in_trash' => isset($raw['not_found_in_trash']) && $raw['not_found_in_trash'] ? sanitize_text_field($raw['not_found_in_trash']) : __( 'No ' . $name . ' found in trash.', 'geodirectory' ),
			'listing_owner' => ! empty( $raw['label_listing_owner'] ) ? sanitize_text_field( $raw['label_listing_owner'] ) : ''
		);
		// Post type description
		$output[$post_type]['description'] = ! empty( $raw['description'] ) ? trim( $raw['description'] ) : '';

		// defaults that likely wont change
		$output[$post_type]['can_export'] = true;
		$output[$post_type]['capability_type'] = 'post';
		$output[$post_type]['has_archive'] = $slug;
		$output[$post_type]['hierarchical'] = false;
		$output[$post_type]['map_meta_cap'] = true;
		$output[$post_type]['public'] = true;
		$output[$post_type]['query_var'] = true;
		$output[$post_type]['show_in_nav_menus'] = true;
		$output[$post_type]['rewrite'] = array(
			'slug' => $slug,
			'with_front' => false,
			'hierarchical' => true,
			'feeds' => true
		);
		$output[$post_type]['supports'] = array(
			'title',
			'editor',
			'author',
			'thumbnail',
			'excerpt',
			'custom-fields',
			'comments',
			'revisions'
		);
		$output[$post_type]['taxonomies'] = array(
			$post_type . "category",
			$post_type . "_tags"
		);

		// list order
		$output[$post_type]['listing_order'] = isset($raw['order']) && $raw['order'] ? absint($raw['order']) : 0;

		// disable features
		$output[$post_type]['disable_comments'] = isset($raw['disable_comments']) && $raw['disable_comments'] ? absint($raw['disable_comments']) : 0;
		$output[$post_type]['disable_reviews'] = isset($raw['disable_reviews']) && $raw['disable_reviews'] ? absint($raw['disable_reviews']) : 0;
		$output[$post_type]['single_review'] = isset( $raw['single_review'] ) && $raw['single_review'] ? absint( $raw['single_review'] ) : 0;
		$output[$post_type]['disable_favorites'] = isset($raw['disable_favorites']) && $raw['disable_favorites'] ? absint($raw['disable_favorites']) : 0;
		$output[$post_type]['disable_frontend_add'] = isset($raw['disable_frontend_add']) && $raw['disable_frontend_add'] ? absint($raw['disable_frontend_add']) : 0;

		// author
		$output[$post_type]['author_posts_private'] = isset($raw['author_posts_private']) && $raw['author_posts_private'] ? absint($raw['author_posts_private']) : 0;
		$output[$post_type]['author_favorites_private'] = isset($raw['author_favorites_private']) && $raw['author_favorites_private'] ? absint($raw['author_favorites_private']) : 0;
		$output[$post_type]['limit_posts'] = isset( $raw['limit_posts'] ) && $raw['limit_posts'] ? absint($raw['limit_posts']) : '';

		// seo content
		$output[$post_type]['seo']['title'] = isset($raw['title']) && $raw['title'] ? sanitize_text_field($raw['title']) : '';
		$output[$post_type]['seo']['meta_title'] = isset($raw['meta_title']) && $raw['meta_title'] ? sanitize_text_field($raw['meta_title']) : '';
		$output[$post_type]['seo']['meta_description'] = isset($raw['meta_description']) && $raw['meta_description'] ? sanitize_text_field($raw['meta_description']) : '';

		$output[$post_type]['menu_icon'] = !empty( $raw['menu_icon'] ) ? sanitize_text_field( esc_attr( $raw['menu_icon'] ) ) : 'dashicons-admin-post';
		$output[$post_type]['default_image'] = !empty( $raw['default_image'] ) ? $raw['default_image'] : '';

		// Page template
		$output[$post_type]['page_add'] = isset( $raw['page_add'] ) ? (int)$raw['page_add'] : 0;
		$output[$post_type]['page_details'] = isset( $raw['page_details'] ) ? (int)$raw['page_details'] : 0;
		$output[$post_type]['page_archive'] = isset( $raw['page_archive'] ) ? (int)$raw['page_archive'] : 0;
		$output[$post_type]['page_archive_item'] = isset( $raw['page_archive_item'] ) ? (int)$raw['page_archive_item'] : 0;
		$output[$post_type]['template_add'] = isset( $raw['template_add'] ) ? (int)$raw['template_add'] : 0;
		$output[$post_type]['template_details'] = isset( $raw['template_details'] ) ? (int)$raw['template_details'] : 0;
		$output[$post_type]['template_archive'] = isset( $raw['template_archive'] ) ? (int)$raw['template_archive'] : 0;

		return apply_filters('geodir_save_post_type', $output, $post_type, $raw);

	}

	/**
	 * Save custom post type settings.
	 *
	 * @param array $new_settings {
	 *      An array of new settings to be saved.
	 *
	 * @type array $fields_form_builder Data related to fields form builder (if applicable).
	 * @type array $sorting_form_builder Data related to sorting form builder (if applicable).
	 * @type array $tabs_form_builder Data related to tabs form builder (if applicable).
	 * @type mixed $other_settings Additional custom post type settings to be sanitized and saved.
	 * }
	 *
	 * @return bool True if the settings were successfully saved, false otherwise.
	 */
	public function save_settings( $new_settings ) {
//		print_r( $new_settings );
		$saved = false;
		$post_type = $this->cpt_slug;
		// Fields form builder
		if ( ! empty( $new_settings['fields_form_builder'] ) ) {
			//$new_settings['fields_form_builder'] = json_encode($new_settings['fields_form_builder']);
			print_r( $new_settings['fields_form_builder'] );exit;
		} elseif ( ! empty( $new_settings['sorting_form_builder'] ) ) {

		} elseif ( ! empty( $new_settings['tabs_form_builder'] ) ) {

		}else{
			// normal settings
			unset( $new_settings['fields_form_builder'], $new_settings['sorting_form_builder'], $new_settings['tabs_form_builder'] );

			unset( $new_settings['has_archive'] );

			$new_settings = $this->sanitize_post_type_for_save( $new_settings );

			if(!empty($new_settings[$post_type])){
//				echo '#############';
				$saved = CPT_Settings_Manager::update_cpt_settings( $post_type, $new_settings[$post_type] );
			}


		}
//
//		var_dump($saved);
//
//		echo '###';
//		print_r( $new_settings );
//		exit;
		return $saved;
	}

	public static function get_page_options(){
		return get_pages( array(
			'number' => 100,
		));
	}
}
