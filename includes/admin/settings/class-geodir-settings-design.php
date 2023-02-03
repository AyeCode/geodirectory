<?php
/**
 * GeoDirectory Design Settings
 *
 * @author   AyeCode
 * @category Admin
 * @package  GeoDirectory/Admin
 * @version  2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'GeoDir_Settings_Design', false ) ) :

	/**
	 * GeoDir_Settings_Products.
	 */
	class GeoDir_Settings_Design extends GeoDir_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {

			$this->id    = 'design';
			$this->label = __( 'Design', 'geodirectory' );

			add_filter( 'geodir_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'geodir_settings_' . $this->id, array( $this, 'output' ) );
//			add_action( 'geodir_sections_' . $this->id, array( $this, 'output_toggle_advanced' ) );

			add_action( 'geodir_settings_save_' . $this->id, array( $this, 'save' ) );
			add_action( 'geodir_sections_' . $this->id, array( $this, 'output_sections' ) );
		}

		/**
		 * Get sections.
		 *
		 * @return array
		 */
		public function get_sections() {

			$sections = array(
				''          		=> __( 'Archives', 'geodirectory' ),
//				'archives'      	=> __( 'Archives', 'geodirectory' ),
				'details' 	        => __( 'Details', 'geodirectory' ),
				'reviews' 		    => __( 'Reviews', 'geodirectory' ),
				'email_template' 	=> __( 'Email Template', 'geodirectory' ),
			);

			return apply_filters( 'geodir_get_sections_' . $this->id, $sections );
		}

		/**
		 * Output the settings.
		 */
		public function output() {
			global $current_section;

			$settings = $this->get_settings( $current_section );

			GeoDir_Admin_Settings::output_fields( $settings );
		}

		/**
		 * Save settings.
		 */
		public function save() {
			global $current_section;

			$settings = $this->get_settings( $current_section );
			GeoDir_Admin_Settings::save_fields( $settings );
		}

		/**
		 * Get settings array.
		 *
		 * @return array
		 */
		public function get_settings( $current_section = '' ) {

			if($current_section == 'reviews'){
				$settings = apply_filters( 'geodir_reviews_settings', array(


					array('name' => __('Reviews', 'geodirectory'), 'type' => 'title', 'desc' => '', 'id' => 'admin_reviews_settings'),

					array(
						'id'   => 'rating_color',
						'name' => __( 'Rating color', 'geodirectory' ),
						'desc' => __( 'The colour for the rating stars.', 'geodirectory' ),
						'default' => '#ff9900',
						'type' => 'color',
						'desc_tip' => true,
						'advanced' => false,
					),

					array(
						'id'   => 'rating_color_off',
						'name' => __( 'Rating color off', 'geodirectory' ),
						'desc' => __( 'The colour for the rating stars that are not selected.', 'geodirectory' ),
						'default' => '#afafaf',
						'type' => 'color',
						'desc_tip' => true,
						'advanced' => false,
					),


					array(
						'id' => 'rating_type',
						'type' => 'select',
						'name' => __('Rating type', 'geodirectory'),
						'desc' => __('Select the rating type to use, font-awesome or transparent image.', 'geodirectory'),
						'class' => 'geodir-select',
						'options' => array(
							'font-awesome'  => __( 'Font Awesome', 'geodirectory' ),
							'image'  => __( 'Transparent Image', 'geodirectory' ),
						),
						'default' => 'font-awesome',
						'desc_tip' => true,
						'advanced' => true,
					),

					array(
						'id'   => 'rating_icon',
						'name' => __( 'Rating icon', 'geodirectory' ),
						'desc' => __( 'Select the font awesome icon to use for ratings.', 'geodirectory' ),
						'class' => '',
						'default' => 'fas fa-star',
						'type' => 'font-awesome',
						'desc_tip' => true,
						'advanced' => true,
						'custom_attributes' => array(
							'data-fa-icons' => true,
							'data-fa-color' => geodir_get_option('rating_color', '#ff9900')
						)
					),

					array(
						'id'   => 'rating_icon_fw',
						'name' => __( 'FA fixed width', 'geodirectory' ),
						'desc' => __( 'This can add more spacing between font awesome icons if they are tight.', 'geodirectory' ),
						'type' => 'checkbox',
						'default'  => '1',
						'desc_tip' => true,
						'advanced' => true,
					),

					array(
						'name' => __('Rating transparent image', 'geodirectory'),
						'desc' => __('Used only if the transparent image option is set, this image will be used to select ratings.', 'geodirectory'),
						'id' => 'rating_image',
						'type' => 'image',
						'image_size' => 'full',
						'desc_tip' => true,
						'advanced' => true,
					),

					array(
						'name' => __('Rating text 1', 'geodirectory'),
						'desc' => __('This is the text shown when a 1 star rating is selected.', 'geodirectory'),
						'id' => 'rating_text_1',
						'type' => 'text',
						'placeholder' => GeoDir_Comments::rating_texts_default()[1],
						'desc_tip' => true,
						'default'  => '',
						'advanced' => true
					),

					array(
						'name' => __('Rating text 2', 'geodirectory'),
						'desc' => __('This is the text shown when the star rating is selected.', 'geodirectory'),
						'id' => 'rating_text_2',
						'type' => 'text',
						'placeholder' => GeoDir_Comments::rating_texts_default()[2],
						'desc_tip' => true,
						'default'  => '',
						'advanced' => true
					),

					array(
						'name' => __('Rating text 3', 'geodirectory'),
						'desc' => __('This is the text shown when the star rating is selected.', 'geodirectory'),
						'id' => 'rating_text_3',
						'type' => 'text',
						'placeholder' => GeoDir_Comments::rating_texts_default()[3],
						'desc_tip' => true,
						'default'  => '',
						'advanced' => true
					),

					array(
						'name' => __('Rating text 4', 'geodirectory'),
						'desc' => __('This is the text shown when the star rating is selected.', 'geodirectory'),
						'id' => 'rating_text_4',
						'type' => 'text',
						'placeholder' => GeoDir_Comments::rating_texts_default()[4],
						'desc_tip' => true,
						'default'  => '',
						'advanced' => true
					),

					array(
						'name' => __('Rating text 5', 'geodirectory'),
						'desc' => __('This is the text shown when the star rating is selected.', 'geodirectory'),
						'id' => 'rating_text_5',
						'type' => 'text',
						'placeholder' => GeoDir_Comments::rating_texts_default()[5],
						'desc_tip' => true,
						'default'  => '',
						'advanced' => true
					),


					array('type' => 'sectionend', 'id' => 'admin_reviews_settings'),


				));
			}
			elseif($current_section == 'details'){
				$settings = apply_filters( 'geodir_details_settings', array(


					array('name' => __('Details page', 'geodirectory'), 'type' => 'title', 'desc' => '', 'id' => 'admin_design_details_settings'),

					array(
						'name' => __( 'Disable theme feature image output?', 'geodirectory' ),
						'desc' => __( 'This will try to disable the theme featured image output, this can be useful if you are seeing double images on the details page.', 'geodirectory' ),
						'id'   => 'details_disable_featured',
						'type' => 'checkbox',
						'default'  => '0',
						'advanced' => false
					),

					array(
						'type' => 'select',
						'id' => 'details_page_template',
						'name' => __('Page template file override', 'geodirectory'),
						'desc' => __('Select the page template to use for the details page output, usually this is set in the page template settings but you can override it here if needed.', 'geodirectory'),
						'class' => 'geodir-select',
						'options' => $this->single_page_templates(),
						'default' => '',
						'desc_tip' => true,
						'advanced' => false,
					),

					array('type' => 'sectionend', 'id' => 'admin_design_details_settings'),



				));
			}elseif($current_section == 'admin_emails'){
				$settings = apply_filters( 'geodir_admin_email_settings', array(


					array('name' => __('Listing submitted', 'geodirectory'), 'type' => 'title', 'desc' => '', 'id' => 'admin_email_submitted_settings'),



					array('type' => 'sectionend', 'id' => 'admin_email_submitted_settings'),


					array('name' => __('Listing edited', 'geodirectory'), 'type' => 'title', 'desc' => '', 'id' => 'admin_email_edited_settings'),




					array('type' => 'sectionend', 'id' => 'admin_email_edited_settings'),

					array('name' => __('Moderate comment', 'geodirectory'), 'type' => 'title', 'desc' => '', 'id' => 'admin_email_comment_settings'),




					array('type' => 'sectionend', 'id' => 'admin_email_comment_settings'),

				));
			} elseif ($current_section == 'email_template') {
				$settings = apply_filters( 'geodir_email_template_settings', array(
					array('name' => __('Email Template', 'geodirectory'), 'type' => 'title', 'desc' => wp_sprintf( __( 'This section lets you customize the GeoDirectory emails. <a href="%s" target="_blank">Click here to preview your email template</a>.', 'geodirectory' ), wp_nonce_url( admin_url( '?geodir_preview_mail=true' ), 'geodir-preview-mail' ) ), 'id' => 'email_template_settings'),

					array(
						'type' => 'select',
						'id' => 'email_type',
						'name' => __('Email type', 'geodirectory'),
						'desc' => __('Select format of the email to send.', 'geodirectory'),
						'class' => 'geodir-select',
						'options' => $this->get_email_type_options(),
						'default' => 'html',
						'desc_tip' => true,
						'advanced' => true,
					),
					array(
						'name' => __('Logo', 'geodirectory'),
						'desc' => __('Upload a logo to be displayed at the top of the emails. Displayed on HTML emails only.', 'geodirectory'),
						'id' => 'email_logo',
						'type' => 'image',
						'image_size' => 'full',
						'desc_tip' => true,
					),
					array(
						'name' => __('Footer Text', 'geodirectory'),
						'desc' => __('The text to appear in the footer of all GeoDirectory emails.', 'geodirectory'),
						'id' => 'email_footer_text',
						'type' => 'textarea',
						'class' => 'code',
						'desc_tip' => true,
						'placeholder' => $this->email_footer_text()
					),
					'email_base_color' => array(
                        'id'   => 'email_base_color',
                        'name' => __( 'Base Color', 'geodirectory' ),
                        'desc' => __( 'The base color for invoice email template. Default <code>#557da2</code>.', 'geodirectory' ),
                        'default' => '#557da2',
                        'type' => 'color',
						'desc_tip' => true,
						'advanced' => true,
                    ),
                    'email_background_color' => array(
                        'id'   => 'email_background_color',
                        'name' => __( 'Background Color', 'geodirectory' ),
                        'desc' => __( 'The background color of email template. Default <code>#f5f5f5</code>.', 'geodirectory' ),
                        'default' => '#f5f5f5',
                        'type' => 'color',
						'desc_tip' => true,
						'advanced' => true,
                    ),
					'email_body_background_color' => array(
                        'id'   => 'email_body_background_color',
                        'name' => __( 'Body Background Color', 'geodirectory' ),
                        'desc' => __( 'The main body background color of email template. Default <code>#fdfdfd</code>.', 'geodirectory' ),
                        'default' => '#fdfdfd',
                        'type' => 'color',
						'desc_tip' => true,
						'advanced' => true,
                    ),
                    'email_text_color' => array(
                        'id'   => 'email_text_color',
                        'name' => __( 'Body Text Color', 'geodirectory' ),
                        'desc' => __( 'The main body text color. Default <code>#505050</code>.', 'geodirectory' ),
                        'default' => '#505050',
                        'type' => 'color',
						'desc_tip' => true,
						'advanced' => true,
                    ),
					'email_header_background_color' => array(
                        'id'   => 'email_header_background_color',
                        'name' => __( 'Header Background Color', 'geodirectory' ),
                        'desc' => __( 'The header background color of email template. Default <code>#555555</code>.', 'geodirectory' ),
                        'default' => '#555555',
                        'type' => 'color',
						'desc_tip' => true,
						'advanced' => true,
                    ),
					'email_header_text_color' => array(
                        'id'   => 'email_header_text_color',
                        'name' => __( 'Header Text Color', 'geodirectory' ),
                        'desc' => __( 'The footer text color. Default <code>#ffffff</code>.', 'geodirectory' ),
                        'default' => '#ffffff',
                        'type' => 'color',
						'desc_tip' => true,
						'advanced' => true,
                    ),
					'email_footer_background_color' => array(
                        'id'   => 'email_footer_background_color',
                        'name' => __( 'Footer Background Color', 'geodirectory' ),
                        'desc' => __( 'The footer background color of email template. Default <code>#666666</code>.', 'geodirectory' ),
                        'default' => '#666666',
                        'type' => 'color',
						'desc_tip' => true,
						'advanced' => true,
                    ),
					'email_footer_text_color' => array(
                        'id'   => 'email_footer_text_color',
                        'name' => __( 'Footer Text Color', 'geodirectory' ),
                        'desc' => __( 'The footer text color. Default <code>#dddddd</code>.', 'geodirectory' ),
                        'default' => '#dddddd',
                        'type' => 'color',
						'desc_tip' => true,
						'advanced' => true,
                    ),

					array('type' => 'sectionend', 'id' => 'email_template_settings'),

				));
			}else{
				$settings = apply_filters( 'geodir_design_settings', array(


					array('name' => __('Image Settings', 'geodirectory'), 'type' => 'title', 'desc' => '', 'id' => 'design_settings'),

					array(
						'type' => 'select',
						'id' => 'archive_page_template',
						'name' => __('Page template file override', 'geodirectory'),
						'desc' => __('Select the page template to use for the archive page output, usually this is set in the page template settings but you can override it here if needed.', 'geodirectory'),
						'class' => 'geodir-select',
						'options' => $this->single_page_templates(),
						'default' => '',
						'desc_tip' => true,
						'advanced' => false,
					),

					array(
						'name' => __('Listing default image', 'geodirectory'),
						'desc' => __('This will be used for listings that have no images uploaded. This can be made more specific by adding a default category image. ', 'geodirectory'),
						'id' => 'listing_default_image',
						'type' => 'image',
						'default' => 0,
						'desc_tip' => true,
					),
					array('type' => 'sectionend', 'id' => 'design_settings'),


				));
			}



			return apply_filters( 'geodir_get_settings_' . $this->id, $settings, $current_section );
		}

		/**
		 * The default email name text.
		 *
		 * @since 2.0.0
		 * @return string
		 */
		public function email_name(){
			return get_bloginfo('name');
		}

		/**
		 * The default email footer text.
		 *
		 * @since 2.0.0
		 * @return string
		 */
		public function email_footer_text(){
			return apply_filters('geodir_email_footer_text',
				wp_sprintf( __( '%s - Powered by GeoDirectory', 'geodirectory' ), get_bloginfo( 'name', 'display' ) )
			);
		}

		/**
		 * Email type options.
		 *
		 * @since 2.0.0
		 * @return array
		 */
		public function get_email_type_options() {
			$types = array();
			if ( class_exists( 'DOMDocument' ) ) {
				$types['html'] = __( 'HTML', 'geodirectory' );
			}
			$types['plain'] = __( 'Plain text', 'geodirectory' );

			return $types;
		}

		/**
		 * Get the array of possible single page templates to use.
		 *
		 * @return array
		 */
		public function single_page_templates(){

			$templates = array(
				''          =>  __("Auto","geodirectory"),
			);

			// check single.php
			if(locate_template( 'single.php' )){
				$templates['single.php'] = 'single.php';
			}

			// check singular.php
			if(locate_template( 'singular.php' )){
				$templates['singular.php'] = 'singular.php';
			}

			// check index.php
			if(locate_template( 'index.php' )){
				$templates['index.php'] = 'index.php';
			}

			// check page.php
			if(locate_template( 'page.php' )){
				$templates['page.php'] = 'page.php';
			}

			$page_templates = get_page_templates();
			if(!empty($page_templates)){
				foreach($page_templates as $name => $page){
					$templates[$page] = $page." ( ".$name." )";
				}
			}

			return $templates;
		}


	}

endif;

return new GeoDir_Settings_Design();
