<?php
/**
 * GeoDirectory CPT Settings
 *
 * @author      AyeCode
 * @category    Admin
 * @package     GeoDirectory/Admin
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'GeoDir_Settings_Cpt', false ) ) :

	/**
	 * GeoDir_Admin_Settings_General.
	 */
	class GeoDir_Settings_Cpt extends GeoDir_Settings_Page {

		/**
		 * Post type.
		 *
		 * @var string
		 */
		private static $post_type = '';

		/**
		 * Sub tab.
		 *
		 * @var string
		 */
		private static $sub_tab = '';


		/**
		 * Constructor.
		 */
		public function __construct() {

			self::$post_type = ( ! empty( $_REQUEST['post_type'] ) && is_scalar( $_REQUEST['post_type'] ) ) ? sanitize_title( $_REQUEST['post_type'] ) : 'gd_place';
			self::$sub_tab   = ! empty( $_REQUEST['tab'] ) ? sanitize_title( $_REQUEST['tab'] ) : 'general';

			$this->id    = 'cpt';
			$this->label = __( 'General', 'geodirectory' );

			add_filter( 'geodir_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'geodir_settings_' . $this->id, array( $this, 'output' ) );
//			add_action( 'geodir_sections_' . $this->id, array( $this, 'output_toggle_advanced' ) );

			add_action( 'geodir_settings_save_' . $this->id, array( $this, 'save' ) );
			add_action( 'geodir_sections_' . $this->id, array( $this, 'output_sections' ) );

			add_filter( 'geodir_get_settings_'.$this->id , array( $this, 'set_current_values' ) );

			add_action( 'geodir_post_type_saved', array( $this, 'clear_post_type_cache' ), 1, 2 );
		}

		/**
		 * Get sections.
		 *
		 * @return array
		 */
		public function get_sections() {
			$sections = array();
			return apply_filters( 'geodir_get_sections_' . $this->id, $sections );
		}

		/**
		 * Output the settings.
		 */
		public function output() {
			global $current_section;

			$settings = $this->get_settings( $current_section );

			//print_r($settings);echo '######';

			GeoDir_Admin_Settings::output_fields( $settings );
		}

		/**
		 * Save settings.
		 */
		public function save() {
			global $current_section, $geodir_settings_error;

			$cpt = self::sanatize_post_type( $_POST );

			if ( is_wp_error( $cpt ) ) {
				$geodir_settings_error = $cpt->get_error_message();
				return;
			}

			$settings = $this->get_settings( $current_section );

			/**
			 * Bypass the normal GD post save action.
			 *
			 * This is used when we are using the settings screens for a non GD listing CPT.
			 */
			if ( apply_filters('geodir_post_type_save_bypass', false, $cpt, $current_section ) ) {
				return;
			}

			$post_types = geodir_get_option( 'post_types', array() );

			if ( empty( $post_types ) ) {
				$post_types = $cpt;
			} else {
				$post_types = array_merge( $post_types, $cpt );
			}

			// Update custom post types
			geodir_update_option( 'post_types', $post_types );

			foreach ( $cpt as $post_type => $args ) {
				do_action( 'geodir_post_type_saved', $post_type, $args );
			}

			// Run the create tables function to add our new columns.
			GeoDir_Admin_Install::create_tables();
		}

		/**
		 * Get settings array.
		 *
		 * @return array
		 */
		public function get_settings( $current_section = '' ) {

			$post_type = self::$post_type;

			$post_types = geodir_get_option('post_types', array());
			$post_type_option = ! empty( $post_types[ $post_type ] ) && is_array( $post_types[ $post_type ] ) ? $post_types[ $post_type ] : array();

			$post_type_option = apply_filters('geodir_cpt_settings_cpt_options',$post_type_option,$post_type);

			$post_type_labels = ! empty( $post_type_option['labels'] ) && is_array( $post_type_option['labels'] ) ? $post_type_option['labels'] : array();

			$post_type_values = $post_type_option;
			if ( ! empty( $post_type_labels ) ) {
				$post_type_values = array_merge( $post_type_labels, $post_type_values );
			}

			$post_type_values = wp_parse_args( $post_type_values, array(
				'post_type' => $post_type,
				'slug' => ( ! empty( $post_type_option['has_archive'] ) ? $post_type_option['has_archive'] : '' ),
				'menu_icon' => '',
				'description' => '',

				// Labels
				'name' => '',
				'singular_name' => '',
				'add_new' => '',
				'add_new_item' => '',
				'edit_item' => '',
				'new_item' => '',
				'view_item' => '',
				'view_items' => '',
				'search_items' => '',
				'not_found' => '',
				'not_found_in_trash' => '',
				'listing_owner' => '',
				'parent_item_colon' => '',
				'all_items' => '',
				'archives' => '',
				'attributes' => '',
				'insert_into_item' => '',
				'uploaded_to_this_item' => '',
				'featured_image' => '',
				'set_featured_image' => '',
				'remove_featured_image' => '',
				'use_featured_image' => '',
				'filter_items_list' => '',
				'items_list_navigation' => '',
				'items_list' => '',

				'default_image' => '',
				'disable_reviews' => '0',
				'disable_favorites' => '0',
				'disable_frontend_add' => '0',
				// author
				'author_posts_private' => '0',
				'author_favorites_private' => '0',
				'limit_posts' => '',
				// Page template
				'page_add' => '0',
				'page_details' => '0',
				'page_archive' => '0',
				'page_archive_item' => '0',
				'template_add' => '0',
				'template_details' => '0',
				'template_archive' => '0'
			) );

			$post_type_values['order'] = ( isset( $post_type_option['listing_order'] ) ? $post_type_option['listing_order'] : '' );

			// SEO
			$post_type_values['title'] = ( ! empty( $post_type_option['seo']['title'] ) ? $post_type_option['seo']['title'] : '' );
			$post_type_values['meta_title'] = ( ! empty( $post_type_option['seo']['meta_title'] ) ? $post_type_option['seo']['meta_title'] : '' );
			$post_type_values['meta_description'] = ( ! empty( $post_type_option['seo']['meta_description'] ) ? $post_type_option['seo']['meta_description'] : '' );

			$cpt_page_settings = array(
				array(
					'title'    => __( 'Template Page Settings', 'geodirectory' ),
					'type'     => 'title',
					'desc'     => __( 'Template pages are used to design the respective pages and should never be linked to directly.', 'geodirectory' ),
					'id'       => 'cpt_settings_page',
					'desc_tip' => true,
					'advanced' => true,
				),
				array(
					'name'     => __( 'Add listing page', 'geodirectory' ),
					'desc'     => __( 'Select the page to use as the GD add listing page template', 'geodirectory' ),
					'id'       => 'page_add',
					'type'     => 'single_select_page',
					'class'    => 'geodir-select',
					'desc_tip' => true,
					'advanced' => true,
					'value'	   => $post_type_values['page_add'],
					'view_page_args' => array(
						'listing_type' => $post_type
					),
					'args'     => array(
						'show_option_none' => wp_sprintf( __( 'Default (%s)', 'geodirectory' ), get_the_title( geodir_get_option( 'page_add' ) ) ),
						'option_none_value' => '0',
						'sort_column' => 'post_title',
					)
				),
				array(
					'name'     => __( 'Details Page', 'geodirectory' ),
					'desc'     => __( 'Select the page to use as the GD details page template', 'geodirectory' ),
					'id'       => 'page_details',
					'type'     => 'single_select_page',
					'is_template_page' => true,
					'class'    => 'geodir-select',
					'desc_tip' => true,
					'advanced' => true,
					'value'	   => $post_type_values['page_details'],
					'args'     => array(
						'show_option_none' => wp_sprintf( __( 'Default (%s)', 'geodirectory' ), get_the_title( geodir_get_option( 'page_details' ) ) ),
						'option_none_value' => '0',
						'sort_column' => 'post_title',
					)
				),
				array(
					'name'     => __( 'Archive page', 'geodirectory' ),
					'desc'     => __( 'Select the page to use for GD archives such as taxonomy and CPT pages', 'geodirectory' ),
					'id'       => 'page_archive',
					'type'     => 'single_select_page',
					'is_template_page' => true,
					'class'    => 'geodir-select',
					'desc_tip' => true,
					'advanced' => true,
					'value'	   => $post_type_values['page_archive'],
					'args'     => array(
						'show_option_none' => wp_sprintf( __( 'Default (%s)', 'geodirectory' ), get_the_title( geodir_get_option( 'page_archive' ) ) ),
						'option_none_value' => '0',
						'sort_column' => 'post_title',
					)
				),
				array(
					'name'     => __( 'Archive item page', 'geodirectory' ),
					'desc'     => __( 'Select the page to use for GD archive items, this is the item template used on taxonomy and CPT pages', 'geodirectory' ),
					'id'       => 'page_archive_item',
					'type'     => 'single_select_page',
					'is_template_page' => true,
					'class'    => 'geodir-select',
					'desc_tip' => true,
					'advanced' => true,
					'value'	   => $post_type_values['page_archive_item'],
					'args'     => array(
						'show_option_none' => wp_sprintf( __( 'Default (%s)', 'geodirectory' ), get_the_title( geodir_get_option( 'page_archive_item' ) ) ),
						'option_none_value' => '0',
						'sort_column' => 'post_title',
					)
				),
				array(
					'type' => 'sectionend',
					'id' => 'cpt_settings_page'
				)
			);
			$cpt_page_settings = apply_filters( 'geodir_cpt_page_options', $cpt_page_settings, $post_type_values, $post_type );

			// we need to trick the settings to show the current values
			$settings  = apply_filters( "geodir_cpt_settings_{$post_type}", array_merge(
				array(
					array(
						'name' => __( 'Post Type Settings', 'geodirectory' ),
						'type' => 'sectionstart',
						'id'   => 'cpt_settings'
					),

					array(
						'name'     => __( 'Post type', 'geodirectory' ),
						'desc'     => __( 'The new post type system name ( max. 17 characters ). Lower-case characters and underscores only. Min 2 letters. Once added the post type system name cannot be changed. <b>Usually Singular.</b>', 'geodirectory' ),
						'id'       => 'post_type',
						'type'     => 'text',
						'std'      => '',
						'desc_tip' => true,
						'advanced' => false,
						'custom_attributes' => array(
							'required' => 'required',
							'maxlength' => 17
						),
						'value'	   => $post_type_values['post_type']
					),
					array(
						'name'     => __( 'Slug', 'geodirectory' ),
						'desc'     => __( 'The listing slug name ( max. 20 characters ). Alphanumeric lower-case characters and underscores and hyphen(-) only. Min 2 letters. <b>Usually Plural.</b>', 'geodirectory' ),
						'id'       => 'slug',
						'type'     => 'text',
						'std'      => '',
						'desc_tip' => true,
						'advanced' => false,
						'custom_attributes' => array(
							'required' => 'required',
							'maxlength' => 20
						),
						'value'	   => $post_type_values['slug']
					),
					array(
						'name'     => __( 'Order in post type list', 'geodirectory' ),
						'desc'     => __( 'Position at which this post type will appear in post type list everywhere on the website.
	<b>Note: If the entered value is already an order of other post type then this will not make any effect.</b>', 'geodirectory' ),
						'id'       => 'order',
						'type'     => 'number',
						'std'      => '',
						'desc_tip' => true,
						'advanced' => true,
						'value'	   => $post_type_values['order']
					),
					array(
						'name'     => __( 'Default image', 'geodirectory' ),
						'desc'     => __( 'Upload default post type image.  This will be used in some areas if the listing has no image and the category has no default image.', 'geodirectory' ),
						'id'       => 'default_image',
						'type'     => 'image',
						'default'  => '',
						'desc_tip' => true,
						'advanced' => true,
						'value'	   => $post_type_values['default_image']
					),
					array(
						'name'     => __( 'Menu icon', 'geodirectory' ),
						'desc'     => __( 'The icon to be used in the admin menu from the post type.', 'geodirectory' ),
						'class'    => 'geodir-select',
						'id'       => 'menu_icon',
						'type'     => 'dashicon',
						'default'  => 'admin-site',
						'desc_tip' => true,
						'advanced' => true,
						'value'	   => $post_type_values['menu_icon'],
						'custom_attributes' => array(
							'data-dashicons' => true
						)
					),

					array(
						'name' => __( 'Disable comments', 'geodirectory' ),
						'desc' => __( 'Disable comments for all posts for this post type.', 'geodirectory' ),
						'id' => 'disable_comments',
						'type' => 'checkbox',
						'std' => '0',
						'advanced' => true,
						'value' => ( isset( $post_type_values['disable_comments'] ) ? $post_type_values['disable_comments'] : 0 )
					),

					array(
						'name' => __( 'Disable ratings', 'geodirectory' ),
						'desc' => __( 'Disable review stars without disabling comments.', 'geodirectory' ),
						'id'   => 'disable_reviews',
						'type' => 'checkbox',
						'std'  => '0',
						'advanced' => true,
						'value'	   => $post_type_values['disable_reviews']
					),

					array(
						'name' => __( 'Single review', 'geodirectory' ),
						'desc' => __( 'Restrict user to leave more than one review per post.', 'geodirectory' ),
						'id' => 'single_review',
						'type' => 'checkbox',
						'std' => '0',
						'advanced' => true,
						'value' => ( isset( $post_type_values['single_review'] ) && $post_type_values['single_review'] ? absint( $post_type_values['single_review'] ) : 0 )
					),

					array(
						'name' => __( 'Disable favorites', 'geodirectory' ),
						'desc' => __( 'Disable favorites for this post type?', 'geodirectory' ),
						'id'   => 'disable_favorites',
						'type' => 'checkbox',
						'std'  => '0',
						'advanced' => true,
						'value'	   => $post_type_values['disable_favorites']
					),

					array(
						'name' => __( 'Disable frontend add', 'geodirectory' ),
						'desc' => __( 'Prevent this post type from being added from the frontend?', 'geodirectory' ),
						'id'   => 'disable_frontend_add',
						'type' => 'checkbox',
						'std'  => '0',
						'advanced' => true,
						'value'	   => $post_type_values['disable_frontend_add']
					),

					array( 'type' => 'sectionend', 'id' => 'cpt_settings' ),

					// author settings
					array(
						'title'    => __( 'Author page', 'geodirectory' ),
						'type'     => 'title',
						'desc'     => 'Settings for the author page url.',
						'id'       => 'cpt_settings_author',
						'desc_tip' => true,
					),
					array(
						'name' => __( 'Authors posts', 'geodirectory' ),
						'desc' => __( 'Select the visibility of the authors posts on the authors posts url.', 'geodirectory' ),
						'id'   => 'author_posts_private',
						'type' => 'select',
						'options'  => array(
							"0"   => __( 'Public', 'geodirectory' ),
							"1" => __( 'Private', 'geodirectory' ),
						),
						'desc_tip' => true,
						'advanced' => true,
						'value'	   => $post_type_values['author_posts_private']
					),
					array(
						'name' => __( 'Authors favorites', 'geodirectory' ),
						'desc' => __( 'Select the visibility of the authors favorites posts on the authors favorites url.', 'geodirectory' ),
						'id'   => 'author_favorites_private',
						'type' => 'select',
						'options'  => array(
							"0"   => __( 'Public', 'geodirectory' ),
							"1" => __( 'Private', 'geodirectory' ),
						),
						'desc_tip' => true,
						'advanced' => true,
						'value'	   => $post_type_values['author_favorites_private']
					),
					array(
						'type' => 'number',
						'id' => 'limit_posts',
						'name' => __( 'Limit Posts Per User', 'geodirectory' ),
						'desc' => __( 'Limit total posts allowed per user. Leave blank or enter 0 to allow unlimited posts.', 'geodirectory' ),
						'std' => '',
						'placeholder' => __( 'Unlimited', 'geodirectory' ),
						'value' => ( (int) $post_type_values['limit_posts'] === 0 ? '' : ( (int) $post_type_values['limit_posts'] < 0 ? -1 : (int) $post_type_values['limit_posts'] ) ),
						'custom_attributes' => array(
							'min' => '-1',
							'step' => '1'
						),
						'desc_tip' => true,
						'advanced' => true
					),
					array( 'type' => 'sectionend', 'id' => 'cpt_settings_author' ),


					array(
						'title'    => __( 'Labels', 'geodirectory' ),
						'type'     => 'title',
						'desc'     => 'Labels are used around WordPress to describe the post type and its actions.',
						'id'       => 'cpt_settings_labels',
						'desc_tip' => true,
					),

					array(
						'name'     => __( 'Name', 'geodirectory' ),
						'desc'     => __( 'General name for the post type, <b>Usually Plural.</b>', 'geodirectory' ),
						'id'       => 'name',
						'type'     => 'text',
						'std'      => '',
						'desc_tip' => true,
						'advanced' => false,
						'custom_attributes' => array(
							'required' => 'required'
						),
						'value'	   => $post_type_values['name']
					),
					array(
						'name'     => __( 'Singular name', 'geodirectory' ),
						'desc'     => __( 'Name for one object of this post type. Defaults to value of name.', 'geodirectory' ),
						'id'       => 'singular_name',
						'type'     => 'text',
						'std'      => '',
						'desc_tip' => true,
						'advanced' => false,
						'custom_attributes' => array(
							'required' => 'required'
						),
						'value'	   => $post_type_values['singular_name']
					),
					array(
						'name'     => __( 'Add new', 'geodirectory' ),
						'desc'     => __( 'The add new text. The default is Add New for both hierarchical and non-hierarchical types.', 'geodirectory' ),
						'id'       => 'add_new',
						'type'     => 'text',
						'std'      => '',
						'desc_tip' => true,
						'advanced' => true,
						'value'	   => $post_type_values['add_new']
					),
					array(
						'name'     => __( 'Add new item', 'geodirectory' ),
						'desc'     => __( 'The add new item text. Default is Add New Post/Add New Page.', 'geodirectory' ),
						'id'       => 'add_new_item',
						'type'     => 'text',
						'std'      => '',
						'desc_tip' => true,
						'advanced' => true,
						'value'	   => $post_type_values['add_new_item']
					),
					array(
						'name'     => __( 'Edit item', 'geodirectory' ),
						'desc'     => __( 'The edit item text. Default is Edit Post/Edit Page.', 'geodirectory' ),
						'id'       => 'edit_item',
						'type'     => 'text',
						'std'      => '',
						'desc_tip' => true,
						'advanced' => true,
						'value'	   => $post_type_values['edit_item']
					),
					array(
						'name'     => __( 'New item', 'geodirectory' ),
						'desc'     => __( 'The new item text. Default is New Post/New Page.', 'geodirectory' ),
						'id'       => 'new_item',
						'type'     => 'text',
						'std'      => '',
						'desc_tip' => true,
						'advanced' => true,
						'value'	   => $post_type_values['new_item']
					),
					array(
						'name'     => __( 'View item', 'geodirectory' ),
						'desc'     => __( 'The view item text. Default is View Post/View Page.', 'geodirectory' ),
						'id'       => 'view_item',
						'type'     => 'text',
						'std'      => '',
						'desc_tip' => true,
						'advanced' => true,
						'value'	   => $post_type_values['view_item']
					),
					array(
						'name'     => __( 'Search items', 'geodirectory' ),
						'desc'     => __( 'The search items text. Default is Search Posts/Search Pages.', 'geodirectory' ),
						'id'       => 'search_items',
						'type'     => 'text',
						'std'      => '',
						'desc_tip' => true,
						'advanced' => true,
						'value'	   => $post_type_values['search_items']
					),
					array(
						'name'     => __( 'Not found', 'geodirectory' ),
						'desc'     => __( 'The not found text. Default is No posts found/No pages found.', 'geodirectory' ),
						'id'       => 'not_found',
						'type'     => 'text',
						'std'      => '',
						'desc_tip' => true,
						'advanced' => true,
						'value'	   => $post_type_values['not_found']
					),
					array(
						'name'     => __( 'Not found in trash', 'geodirectory' ),
						'desc'     => __( 'The not found in trash text. Default is No posts found in Trash/No pages found in Trash.', 'geodirectory' ),
						'id'       => 'not_found_in_trash',
						'type'     => 'text',
						'std'      => '',
						'desc_tip' => true,
						'advanced' => true,
						'value'	   => $post_type_values['not_found_in_trash']
					),
					array(
						'name'        => __( 'Listing owner', 'geodirectory' ),
						'desc'        => __( 'The listing owner label. Default is Listing Owner.', 'geodirectory' ),
						'id'          => 'label_listing_owner',
						'placeholder' => __( 'Listing Owner', 'geodirectory' ),
						'type'        => 'text',
						'std'         => '',
						'desc_tip'    => true,
						'advanced'    => true,
						'value'	      => $post_type_values['listing_owner']
					),

					array( 'type' => 'sectionend', 'id' => 'cpt_settings_labels' ),

					array(
						'title'    => __( 'Description', 'geodirectory' ),
						'type'     => 'title',
						'desc'     => '',
						'id'       => 'cpt_settings_description',
						'desc_tip' => false,
						'advanced' => true,
					),
					array(
						'name'     => __( 'Description', 'geodirectory' ),
						'desc'     => __( 'A short descriptive summary of what the post type is.', 'geodirectory' ),
						'id'       => 'description',
						'type'     => 'textarea',
						'class'    => 'active-placeholder',
						'desc_tip' => true,
						'advanced' => true,
						'value'	   => $post_type_values['description']
					),
					array( 'type' => 'sectionend', 'id' => 'cpt_settings_description' ),

					array(
						'title'    => __( 'SEO Overrides', 'geodirectory' ),
						'type'     => 'title',
						'desc'     => __( 'Main settings are set from the General>Titles & Meta settings, here you can override those per CPT.', 'geodirectory' ),
						'id'       => 'cpt_settings_seo',
						'desc_tip' => true,
						'advanced' => true,
					),
					array(
						'name'     => __( 'Title', 'geodirectory' ),
						'desc'     => __( 'The page title will appear on the post type archive page.', 'geodirectory' ),
						'id'       => 'title',
						'type'     => 'text',
						'class'    => 'active-placeholder',
						'desc_tip' => true,
						'advanced' => true,
						'value'	   => $post_type_values['title']
					),
					array(
						'name'     => __( 'Meta Title', 'geodirectory' ),
						'desc'     => __( 'Meta title will appear in head tag of this post type archive page.', 'geodirectory' ),
						'id'       => 'meta_title',
						'type'     => 'text',
						'class'    => 'active-placeholder',
						'desc_tip' => true,
						'advanced' => true,
						'value'	   => $post_type_values['meta_title']
					),
					array(
						'name'     => __( 'Meta Description', 'geodirectory' ),
						'desc'     => __( 'Meta description will appear in head tag of this post type archive page.', 'geodirectory' ),
						'id'       => 'meta_description',
						'type'     => 'textarea',
						'class'    => 'active-placeholder',
						'desc_tip' => true,
						'advanced' => true,
						'value'	   => $post_type_values['meta_description']
					),

					// Page template
					array(
						'type' => 'sectionend',
						'id' => 'cpt_settings_seo'
					)
				),
				$cpt_page_settings
				)
			);

			//set_current_values()

			return apply_filters( 'geodir_get_settings_' . $this->id, $settings, $current_section, $post_type_values );
		}

        /**
         * Set GeoDir current values.
         *
         * @since 2.0.0
         *
         * @param array $settings {
         *      An array of settings.
         *
         *      @type string $id Settings id.
         *      @type string $default Settings default id.
         *      @type string $custom_attributes Settings custom attributes.
         * }
         * @return array $settings.
         */
		public static function set_current_values($settings){

			if(self::$post_type){
				$post_types = geodir_get_option('post_types', array());

				if(isset($post_types[self::$post_type])){
					$cpt = $post_types[self::$post_type];
					foreach($settings as $key => $setting){
						if(isset($setting['id']) && !isset($setting['default'])){

							// check standard fields
							if(array_key_exists($setting['id'],$cpt)){
								$settings[$key]['default'] =  $cpt[$setting['id']];
							}else{ // might be in an array value
								foreach($cpt as $cpt_val){
									if(is_array($cpt_val)){
										if(array_key_exists($setting['id'],$cpt_val)){
											$settings[$key]['default'] =  $cpt_val[$setting['id']];
										}
									}
								}

							}

							// set the post type
							if($setting['id']=='post_type' && !isset($setting['default'])){
								$settings[$key]['default'] = self::$post_type;
								$settings[$key]['custom_attributes'] = array('disabled'=>'disabled');

							}

						}
					}
				}
			}

			return $settings;
		}

        /**
         * Sanatize post type.
         *
         * @since 2.0.0
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
         */
		public static function sanatize_post_type( $raw ) {
			$output = array();

			$post_types = geodir_get_option( 'post_types', array() );
			$raw = stripslashes_deep( $raw );
			$post_type = isset($raw['new_post_type']) && $raw['new_post_type'] ? str_replace("-","_",sanitize_key($raw['new_post_type'])) : self::$post_type;
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

			if ( ! empty( $raw['new_post_type'] ) && ! empty( $post_types[ $raw['new_post_type'] ] ) ) {
				return new WP_Error( 'invalid_post_type', __( 'Post type already exists.', 'geodirectory' ) );
			}

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
			$output[$post_type]['limit_posts'] = isset( $raw['limit_posts'] ) && $raw['limit_posts'] ? (int) $raw['limit_posts'] : 0;

			// seo content
			$output[$post_type]['seo']['title'] = isset($raw['title']) && $raw['title'] ? sanitize_text_field($raw['title']) : '';
			$output[$post_type]['seo']['meta_title'] = isset($raw['meta_title']) && $raw['meta_title'] ? sanitize_text_field($raw['meta_title']) : '';
			$output[$post_type]['seo']['meta_description'] = isset($raw['meta_description']) && $raw['meta_description'] ? sanitize_text_field($raw['meta_description']) : '';

			$output[$post_type]['menu_icon'] = !empty( $raw['menu_icon'] ) ? GeoDir_Post_types::sanitize_menu_icon( $raw['menu_icon'] ) : 'dashicons-admin-post';
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
		 * Clear post type cache on save post type.
		 *
		 * @since 2.3.72
		 *
		 * @param string $post_type Saved post type.
		 * @param array  $args Post types args.
		 * @return mixed
		 */
		public function clear_post_type_cache( $post_type, $args ) {
			// Clear CPT templates cache.
			geodir_cache_flush_group( 'geodir_cpt_templates' );
		}
	}


endif;

return new GeoDir_Settings_Cpt();
