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

			self::$post_type = ! empty( $_REQUEST['post_type'] ) ? sanitize_title( $_REQUEST['post_type'] ) : 'gd_place';
			self::$sub_tab   = ! empty( $_REQUEST['tab'] ) ? sanitize_title( $_REQUEST['tab'] ) : 'general';

			$this->id    = 'cpt';
			$this->label = __( 'CPT Settings', 'geodirectory' );

			add_filter( 'geodir_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'geodir_settings_' . $this->id, array( $this, 'output' ) );
			add_action( 'geodir_sections_' . $this->id, array( $this, 'output_toggle_advanced' ) );

			add_action( 'geodir_settings_save_' . $this->id, array( $this, 'save' ) );
			add_action( 'geodir_sections_' . $this->id, array( $this, 'output_sections' ) );

			add_filter( 'geodir_get_settings_'.$this->id , array( $this, 'set_current_values' ) );
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
			global $current_section;

			//echo '###';
			
			$cpt = self::sanatize_post_type( $_POST );geodir_error_log( $_POST, 'POST', __FILE__, __LINE__ );geodir_error_log( $cpt, 'cpt', __FILE__, __LINE__ );
			//print_r( $_POST );
			//echo $current_section;
			$settings = $this->get_settings( $current_section );
			//print_r($cpt );
			//exit;
			if(is_wp_error( $cpt) ){
				$cpt->get_error_message(); exit;
			}
			$post_types = geodir_get_option('post_types', array());geodir_error_log( $post_types, 'post_types 1', __FILE__, __LINE__ );
			if(empty($post_types)){
				$post_types = $cpt;
			}else{
				$post_types = array_merge($post_types,$cpt);
			}geodir_error_log( $post_types, 'post_types 2', __FILE__, __LINE__ );


			//print_r($post_types );exit;


			//Update custom post types
			geodir_update_option( 'post_types', $post_types );

			do_action( 'geodir_post_type_saved', $cpt );

			//$settings = $this->get_settings( $current_section );
			//GeoDir_Admin_Settings::save_fields( $settings );
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
			$post_type_labels = ! empty( $post_type_option['labels'] ) && is_array( $post_type_option['labels'] ) ? $post_type_option['labels'] : array();

			$post_type_values = $post_type_option;
			if ( ! empty( $post_type_labels ) ) {
				$post_type_values = array_merge( $post_type_labels, $post_type_values );
			}

			$post_type_values = wp_parse_args( $post_type_values, array(
				'post_type' => $post_type,
				'slug' => ( ! empty( $post_type_option['has_archive'] ) ? $post_type_option['has_archive'] : '' ),
				'menu_icon' => '',

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
			) );

			$post_type_values['order'] = ( isset( $post_type_option['listing_order'] ) ? $post_type_option['listing_order'] : '' );

			// SEO
			$post_type_values['title'] = ( ! empty( $post_type_option['seo']['title'] ) ? $post_type_option['seo']['title'] : '' );
			$post_type_values['meta_title'] = ( ! empty( $post_type_option['seo']['meta_title'] ) ? $post_type_option['seo']['meta_title'] : '' );
			$post_type_values['meta_description'] = ( ! empty( $post_type_option['seo']['meta_description'] ) ? $post_type_option['seo']['meta_description'] : '' );
			geodir_error_log( $post_type_values, 'post_type_values', __FILE__, __LINE__ );
			// we need to trick the settings to show the current values

			$settings  = apply_filters( "geodir_cpt_settings_{$post_type}", array(


				array(
					'name' => __( 'Post Type', 'geodirectory' ),
					'type' => 'title',
					'desc' => '',
					'id'   => 'cpt_settings'
				),

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
						'required' => 'required'
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
						'required' => 'required'
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
					'desc'     => __( 'The image to be used as the menu icon (16px x 16px recommended)', 'geodirectory' ),
					'id'       => 'menu_icon',
					'type'     => 'image',
					'default'  => '',
					'desc_tip' => true,
					'advanced' => true,
					'value'	   => $post_type_values['menu_icon']
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
				


				array( 'type' => 'sectionend', 'id' => 'cpt_settings_labels' ),


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
					'class'    => 'large-text',
					'desc_tip' => true,
					'advanced' => true,
					'value'	   => $post_type_values['title']
				),
				array(
					'name'     => __( 'Meta Title', 'geodirectory' ),
					'desc'     => __( 'Meta title will appear in head tag of this post type archive page.', 'geodirectory' ),
					'id'       => 'meta_title',
					'type'     => 'text',
					'class'    => 'large-text',
					'desc_tip' => true,
					'advanced' => true,
					'value'	   => $post_type_values['meta_title']
				),
				array(
					'name'     => __( 'Meta Description', 'geodirectory' ),
					'desc'     => __( 'Meta description will appear in head tag of this post type archive page.', 'geodirectory' ),
					'id'       => 'meta_description',
					'type'     => 'textarea',
					'class'    => 'large-text',
					'desc_tip' => true,
					'advanced' => true,
					'value'	   => $post_type_values['meta_description']
				),


				array( 'type' => 'sectionend', 'id' => 'cpt_settings_seo' ),


			) );

			//set_current_values()

			return apply_filters( 'geodir_get_settings_' . $this->id, $settings, $current_section );
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
         *      An array sanatize posttype.
         *
         * @type string $new_post_type New sanatize posttype.
         * @type string $name New posttype name.
         * @type string $singular_name New Posttype singular name.
         * @type string $slug New posttype slug.
         * }
         *
         * @return array $output.
         */
		public static function sanatize_post_type( $raw ) {
			$output = array();

			$post_types = geodir_get_option( 'post_types', array() );
			$post_type = isset($raw['new_post_type']) && $raw['new_post_type'] ? str_replace("-","_",sanitize_key($raw['new_post_type'])) : self::$post_type;
			$name = isset($raw['name']) && $raw['name'] ? sanitize_text_field($raw['name']) : null;
			$singular_name = isset($raw['singular_name']) && $raw['singular_name'] ? sanitize_text_field($raw['singular_name']) : null;
			$slug = isset($raw['slug']) && $raw['slug'] ? str_replace("-","_",sanitize_key($raw['slug'])) : $post_type;

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
				'add_new' => isset($raw['add_new']) && $raw['add_new'] ? sanitize_text_field($raw['add_new']) : _x( 'Add New', $post_type, 'geodir_custom_posts' ),
				'add_new_item' => isset($raw['add_new_item']) && $raw['add_new_item'] ? sanitize_text_field($raw['add_new_item']) : __( 'Add New ' . $singular_name, 'geodir_custom_posts' ),
				'edit_item' => isset($raw['edit_item']) && $raw['edit_item'] ? sanitize_text_field($raw['edit_item']) : __( 'Edit ' . $singular_name, 'geodir_custom_posts' ),
				'new_item' => isset($raw['new_item']) && $raw['new_item'] ? sanitize_text_field($raw['new_item']) : __( 'New ' . $singular_name, 'geodir_custom_posts' ),
				'view_item' => isset($raw['view_item']) && $raw['view_item'] ? sanitize_text_field($raw['view_item']) : __( 'View ' . $singular_name, 'geodir_custom_posts' ),
				'search_items' => isset($raw['search_items']) && $raw['search_items'] ? sanitize_text_field($raw['search_items']) : __( 'Search ' . $name, 'geodir_custom_posts' ),
				'not_found' => isset($raw['not_found']) && $raw['not_found'] ? sanitize_text_field($raw['not_found']) : __( 'No ' . $name . ' found.', 'geodir_custom_posts' ),
				'not_found_in_trash' => isset($raw['not_found_in_trash']) && $raw['not_found_in_trash'] ? sanitize_text_field($raw['not_found_in_trash']) : __( 'No ' . $name . ' found in Trash.', 'geodir_custom_posts' ),
				'label_post_profile' => isset($raw['label_post_profile']) && $raw['label_post_profile'] ? sanitize_text_field($raw['label_post_profile']) : '',
				'label_post_info' => isset($raw['label_post_info']) && $raw['label_post_info'] ? sanitize_text_field($raw['label_post_info']) : '',
				'label_post_images' => isset($raw['label_post_images']) && $raw['label_post_images'] ? sanitize_text_field($raw['label_post_images']) : '',
				'label_post_map' => isset($raw['label_post_map']) && $raw['label_post_map'] ? sanitize_text_field($raw['label_post_map']) : '',
				'label_reviews' => isset($raw['label_reviews']) && $raw['label_reviews'] ? sanitize_text_field($raw['label_reviews']) : '',
				'label_related_listing' => isset($raw['label_related_listing']) && $raw['label_related_listing'] ? sanitize_text_field($raw['label_related_listing']) : ''
			);

			// defaults that likely wont change
			$output[$post_type]['can_export'] = true;
			$output[$post_type]['capability_type'] = 'post';
			$output[$post_type]['has_archive'] = $slug;
			$output[$post_type]['hierarchical'] = false;
			$output[$post_type]['map_meta_cap'] = true;
			$output[$post_type]['public'] = true;
			$output[$post_type]['query_var'] = true;
			$output[$post_type]['show_in_nav_menus'] = true;
			$output[$post_type]['rewrite'] = array('slug' => $slug);
			$output[$post_type]['supports'] = array('title','editor','author','thumbnail','excerpt','custom-fields','comments');
			$output[$post_type]['taxonomies'] = array($post_type."category",$post_type."_tags");
			//$output[$post_type][''] = '';
			//$output[$post_type][''] = '';


			// list order
			$output[$post_type]['listing_order'] = isset($raw['order']) && $raw['order'] ? absint($raw['order']) : 0;

			// disable features
			$output[$post_type]['disable_reviews'] = isset($raw['disable_reviews']) && $raw['disable_reviews'] ? absint($raw['disable_reviews']) : 0;
			$output[$post_type]['disable_favorites'] = isset($raw['disable_favorites']) && $raw['disable_favorites'] ? absint($raw['disable_favorites']) : 0;
			$output[$post_type]['disable_frontend_add'] = isset($raw['disable_frontend_add']) && $raw['disable_frontend_add'] ? absint($raw['disable_frontend_add']) : 0;

			// seo content
			$output[$post_type]['seo']['title'] = isset($raw['title']) && $raw['title'] ? sanitize_text_field($raw['title']) : '';
			$output[$post_type]['seo']['meta_title'] = isset($raw['meta_title']) && $raw['meta_title'] ? sanitize_text_field($raw['meta_title']) : '';
			$output[$post_type]['seo']['meta_description'] = isset($raw['meta_description']) && $raw['meta_description'] ? sanitize_text_field($raw['meta_description']) : '';

			// menu icon @todo do we need this?

			return apply_filters('geodir_save_post_type', $output, $post_type, $raw);

		}
	}


endif;

return new GeoDir_Settings_Cpt();
