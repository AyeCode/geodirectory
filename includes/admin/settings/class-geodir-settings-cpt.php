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
			$this->label = __( 'CPT Settings', 'woocommerce' );

			add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
			add_action( 'woocommerce_sections_' . $this->id, array( $this, 'output_toggle_advanced' ) );

			add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
			add_action( 'woocommerce_sections_' . $this->id, array( $this, 'output_sections' ) );

			add_filter( 'geodir_get_settings_'.$this->id , array( $this, 'set_current_values' ) );
		}

		/**
		 * Get sections.
		 *
		 * @return array
		 */
		public function get_sections() {

			$sections = array(
				''             => __( 'General', 'woocommerce' ),
				'display'      => __( 'Display', 'woocommerce' ),
				'inventory'    => __( 'Inventory', 'woocommerce' ),
				'downloadable' => __( 'Downloadable products', 'woocommerce' ),
			);
			$sections = array();

			return apply_filters( 'woocommerce_get_sections_' . $this->id, $sections );
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
			
			$cpt = self::sanatize_post_type( $_POST );
			//print_r( $_POST );
			//echo $current_section;
			$settings = $this->get_settings( $current_section );
			//print_r($cpt );
			//exit;
			if(is_wp_error( $cpt) ){
				$cpt->get_error_message(); exit;
			}
			$post_types = geodir_get_option('post_types', array());
			if(empty($post_types)){
				$post_types = $cpt;
			}else{
				$post_types = array_merge($post_types,$cpt);
			}


			//print_r($post_types );exit;


			//Update custom post types
			geodir_update_option( 'post_types', $post_types );

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
				),
				array(
					'name'     => __( 'Slug', 'geodirectory' ),
					'desc'     => __( 'The listing slug name ( max. 20 characters ). Alphanumeric lower-case characters and underscores and hyphen(-) only. Min 2 letters. <b>Usually Plural.</b>', 'geodirectory' ),
					'id'       => 'slug',
					'type'     => 'text',
					'std'      => '',
					'desc_tip' => true,
					'advanced' => false
				),
				array(
					'name'     => __( 'Order in post type list', 'geodirectory' ),
					'desc'     => __( 'Position at which this post type will appear in post type list everywhere on the website.
<b>Note: If the entered value is already an order of other post type then this will not make any effect.</b>', 'geodirectory' ),
					'id'       => 'order',
					'type'     => 'number',
					'std'      => '',
					'desc_tip' => true,
					'advanced' => true
				),
				array(
					'name'     => __( 'Default image', 'geodirectory' ),
					'desc'     => __( 'Upload default post type image.  This will be used in some areas if the listing has no image and the category has no default image.', 'geodirectory' ),
					'id'       => 'default_image',
					'type'     => 'image',
					'default'  => '',
					'desc_tip' => true,
					'advanced' => true
				),
				array(
					'name'     => __( 'Menu icon', 'geodirectory' ),
					'desc'     => __( 'The image to be used as the menu icon (16px x 16px recommended)', 'geodirectory' ),
					'id'       => 'menu_icon',
					'type'     => 'image',
					'default'  => '',
					'desc_tip' => true,
					'advanced' => true
				),

				array(
					'name' => __( 'Disable ratings', 'geodirectory' ),
					'desc' => __( 'Disable review stars without disabling comments.', 'geodirectory' ),
					'id'   => 'disable_reviews',
					'type' => 'checkbox',
					'std'  => '0',
					'advanced' => true
				),

				array(
					'name' => __( 'Disable favorites', 'geodirectory' ),
					'desc' => __( 'Disable favorites for this post type?', 'geodirectory' ),
					'id'   => 'disable_favorites',
					'type' => 'checkbox',
					'std'  => '0',
					'advanced' => true
				),

				array(
					'name' => __( 'Disable frontend add', 'geodirectory' ),
					'desc' => __( 'Prevent this post type from being added from the frontend?', 'geodirectory' ),
					'id'   => 'disable_frontend_add',
					'type' => 'checkbox',
					'std'  => '0',
					'advanced' => true
				),
				
				array(
					'name' => __( 'Enable opening hours', 'geodirectory' ),
					'desc' => __( 'Enable to display business hours on the listing.', 'geodirectory' ),
					'id'   => 'opening_hours',
					'type' => 'checkbox',
					'std'  => '0',
					'advanced' => true
				),

				array( 'type' => 'sectionend', 'id' => 'cpt_settings' ),


				array(
					'title'    => __( 'Labels', 'woocommerce' ),
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
					'advanced' => false
				),
				array(
					'name'     => __( 'Singular name', 'geodirectory' ),
					'desc'     => __( 'Name for one object of this post type. Defaults to value of name.', 'geodirectory' ),
					'id'       => 'singular_name',
					'type'     => 'text',
					'std'      => '',
					'desc_tip' => true,
					'advanced' => false
				),
				array(
					'name'     => __( 'Add new', 'geodirectory' ),
					'desc'     => __( 'The add new text. The default is Add New for both hierarchical and non-hierarchical types.', 'geodirectory' ),
					'id'       => 'add_new',
					'type'     => 'text',
					'std'      => '',
					'desc_tip' => true,
					'advanced' => true
				),
				array(
					'name'     => __( 'Add new item', 'geodirectory' ),
					'desc'     => __( 'The add new item text. Default is Add New Post/Add New Page.', 'geodirectory' ),
					'id'       => 'add_new_item',
					'type'     => 'text',
					'std'      => '',
					'desc_tip' => true,
					'advanced' => true
				),
				array(
					'name'     => __( 'Edit item', 'geodirectory' ),
					'desc'     => __( 'The edit item text. Default is Edit Post/Edit Page.', 'geodirectory' ),
					'id'       => 'edit_item',
					'type'     => 'text',
					'std'      => '',
					'desc_tip' => true,
					'advanced' => true
				),
				array(
					'name'     => __( 'New item', 'geodirectory' ),
					'desc'     => __( 'The new item text. Default is New Post/New Page.', 'geodirectory' ),
					'id'       => 'new_item',
					'type'     => 'text',
					'std'      => '',
					'desc_tip' => true,
					'advanced' => true
				),
				array(
					'name'     => __( 'View item', 'geodirectory' ),
					'desc'     => __( 'The view item text. Default is View Post/View Page.', 'geodirectory' ),
					'id'       => 'view_item',
					'type'     => 'text',
					'std'      => '',
					'desc_tip' => true,
					'advanced' => true
				),
				array(
					'name'     => __( 'Search items', 'geodirectory' ),
					'desc'     => __( 'The search items text. Default is Search Posts/Search Pages.', 'geodirectory' ),
					'id'       => 'search_items',
					'type'     => 'text',
					'std'      => '',
					'desc_tip' => true,
					'advanced' => true
				),
				array(
					'name'     => __( 'Not found', 'geodirectory' ),
					'desc'     => __( 'The not found text. Default is No posts found/No pages found.', 'geodirectory' ),
					'id'       => 'not_found',
					'type'     => 'text',
					'std'      => '',
					'desc_tip' => true,
					'advanced' => true
				),
				array(
					'name'     => __( 'Not found in trash', 'geodirectory' ),
					'desc'     => __( 'The not found in trash text. Default is No posts found in Trash/No pages found in Trash.', 'geodirectory' ),
					'id'       => 'not_found_in_trash',
					'type'     => 'text',
					'std'      => '',
					'desc_tip' => true,
					'advanced' => true
				),
				// tab labels
				array(
					'name'     => __( 'Profile tab label', 'geodirectory' ),
					'desc'     => __( 'Text label for "Profile" tab on post detail page.(optional)', 'geodirectory' ),
					'id'       => 'label_post_profile',
					'type'     => 'text',
					'std'      => '',
					'desc_tip' => true,
					'advanced' => true
				),
				array(
					'name'     => __( 'More Info tab label', 'geodirectory' ),
					'desc'     => __( 'Text label for "More Info" tab on post detail page.(optional)', 'geodirectory' ),
					'id'       => 'label_post_info',
					'type'     => 'text',
					'std'      => '',
					'desc_tip' => true,
					'advanced' => true
				),
				array(
					'name'     => __( 'Photos tab label', 'geodirectory' ),
					'desc'     => __( 'Text label for "Photos" tab on post detail page.(optional)', 'geodirectory' ),
					'id'       => 'label_post_images',
					'type'     => 'text',
					'std'      => '',
					'desc_tip' => true,
					'advanced' => true
				),
				array(
					'name'     => __( 'Map tab label', 'geodirectory' ),
					'desc'     => __( 'Text label for "Map" tab on post detail page.(optional)', 'geodirectory' ),
					'id'       => 'label_post_map',
					'type'     => 'text',
					'std'      => '',
					'desc_tip' => true,
					'advanced' => true
				),
				array(
					'name'     => __( 'Reviews tab label', 'geodirectory' ),
					'desc'     => __( 'Text label for "Reviews" tab on post detail page.(optional)', 'geodirectory' ),
					'id'       => 'label_reviews',
					'type'     => 'text',
					'std'      => '',
					'desc_tip' => true,
					'advanced' => true
				),
				array(
					'name'     => __( 'Related Listing tab label', 'geodirectory' ),
					'desc'     => __( 'Text label for "Related Listing" tab on post detail page.(optional)', 'geodirectory' ),
					'id'       => 'label_related_listing',
					'type'     => 'text',
					'std'      => '',
					'desc_tip' => true,
					'advanced' => true
				),


				array( 'type' => 'sectionend', 'id' => 'cpt_settings_labels' ),


				array(
					'title'    => __( 'SEO', 'woocommerce' ),
					'type'     => 'title',
					'desc'     => '',
					'id'       => 'cpt_settings_seo',
					//'desc_tip' => true,
					'advanced' => true,
				),

				array(
					'name'     => __( 'Meta Keywords', 'geodirectory' ),
					'desc'     => __( 'Meta keywords will appear in head tag of this post type listing page.', 'geodirectory' ),
					'id'       => 'meta_keyword',
					'type'     => 'textarea',
					'class'    => '',
					'desc_tip' => true,
					'advanced' => true,
				),
				array(
					'name'     => __( 'Meta Description', 'geodirectory' ),
					'desc'     => __( 'Meta description will appear in head tag of this post type listing page.', 'geodirectory' ),
					'id'       => 'meta_description',
					'type'     => 'textarea',
					'class'    => '',
					'desc_tip' => true,
					'advanced' => true,
				),


				array( 'type' => 'sectionend', 'id' => 'cpt_settings_seo' ),


			) );

			//set_current_values()

			return apply_filters( 'geodir_get_settings_' . $this->id, $settings, $current_section );
		}


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

		public static function sanatize_post_type( $raw ) {
			$output = array();

			$post_type = isset($raw['new_post_type']) && $raw['new_post_type'] ? str_replace("-","_",sanitize_key($raw['new_post_type'])) : self::$post_type;
			$name = isset($raw['name']) && $raw['name'] ? sanitize_text_field($raw['name']) : null;
			$singular_name = isset($raw['singular_name']) && $raw['singular_name'] ? sanitize_text_field($raw['singular_name']) : null;
			$slug = isset($raw['slug']) && $raw['slug'] ? str_replace("-","_",sanitize_key($raw['slug'])) : $post_type;

			if(!$post_type || !$name || !$slug || !$singular_name){
				return new WP_Error('invalid_post_type', __('Invalid or missing post type', 'geodirectory'));
			}

			// check the CPT is "gd_"prepended
			if (strpos($post_type, 'gd_') === 0) {
				// all good
			}else{
				$post_type = "gd_".$post_type;
			}

			// Set the labels
			$output[$post_type]['labels'] = array(
				'name' => $name,
				'singular_name' => $singular_name,
				'add_new' => isset($raw['add_new']) && $raw['add_new'] ? sanitize_text_field($raw['add_new']) : '',
				'add_new_item' => isset($raw['add_new_item']) && $raw['add_new_item'] ? sanitize_text_field($raw['add_new_item']) : '',
				'edit_item' => isset($raw['edit_item']) && $raw['edit_item'] ? sanitize_text_field($raw['edit_item']) : '',
				'new_item' => isset($raw['new_item']) && $raw['new_item'] ? sanitize_text_field($raw['new_item']) : '',
				'view_item' => isset($raw['view_item']) && $raw['view_item'] ? sanitize_text_field($raw['view_item']) : '',
				'search_items' => isset($raw['search_items']) && $raw['search_items'] ? sanitize_text_field($raw['search_items']) : '',
				'not_found' => isset($raw['not_found']) && $raw['not_found'] ? sanitize_text_field($raw['not_found']) : '',
				'not_found_in_trash' => isset($raw['not_found_in_trash']) && $raw['not_found_in_trash'] ? sanitize_text_field($raw['not_found_in_trash']) : '',
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
			
			// enable features
			$output[$post_type]['opening_hours'] = ! empty( $raw['opening_hours'] ) ? 1 : 0;

			// seo content
			$output[$post_type]['seo']['meta_keyword'] = isset($raw['meta_keyword']) && $raw['meta_keyword'] ? sanitize_text_field($raw['meta_keyword']) : '';
			$output[$post_type]['seo']['meta_description'] = isset($raw['meta_description']) && $raw['meta_description'] ? sanitize_text_field($raw['meta_description']) : '';

			// menu icon @todo do we need this?




			return apply_filters('geodir_save_post_type',$output,$post_type);

		}
	}


endif;

return new GeoDir_Settings_Cpt();
