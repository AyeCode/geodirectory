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
			$this->label = __( 'Design', 'woocommerce' );

			add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
			add_action( 'woocommerce_sections_' . $this->id, array( $this, 'output_toggle_advanced' ) );

			add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
			add_action( 'woocommerce_sections_' . $this->id, array( $this, 'output_sections' ) );
		}

		/**
		 * Get sections.
		 *
		 * @return array
		 */
		public function get_sections() {

			$sections = array(
				''          		=> __( 'Design options', 'geodirectory' ),
				'admin_emails'  	=> __( 'Archives', 'geodirectory' ),
				'client_emails' 	=> __( 'Details', 'geodirectory' ),
				'other_emails' 		=> __( 'Reviews', 'geodirectory' ),
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

			if($current_section == 'other_emails'){
				$settings = apply_filters( 'woocommerce_other_email_settings', array(


					array('name' => __('Send to friend', 'geodirectory'), 'type' => 'title', 'desc' => '', 'id' => 'admin_send_friend_settings'),



					array('type' => 'sectionend', 'id' => 'user_send_friend_settings'),

					array('name' => __('Send enquiry', 'geodirectory'), 'type' => 'title', 'desc' => '', 'id' => 'admin_send_enquiry_settings'),



					array('type' => 'sectionend', 'id' => 'user_send_enquiry_settings'),

				));
			}
			elseif($current_section == 'client_emails'){
				$settings = apply_filters( 'woocommerce_user_email_settings', array(


					array('name' => __('Listing submitted', 'geodirectory'), 'type' => 'title', 'desc' => '', 'id' => 'admin_user_pending_post_settings'),



					array('type' => 'sectionend', 'id' => 'user_email_submitted_settings'),


					array('name' => __('Listing published', 'geodirectory'), 'type' => 'title', 'desc' => '', 'id' => 'user_email_published_settings'),




					array('type' => 'sectionend', 'id' => 'user_email_published_settings'),

					array('name' => __('Listing owner comment submitted', 'geodirectory'), 'type' => 'title', 'desc' => '', 'id' => 'owner_email_comment_settings'),

					array('type' => 'sectionend', 'id' => 'owner_email_comment_settings'),


					array('name' => __('Listing owner comment approved', 'geodirectory'), 'type' => 'title', 'desc' => '', 'id' => 'owner_email_comment_approved_settings'),

					array('type' => 'sectionend', 'id' => 'owner_email_comment_approved_settings'),

					array('name' => __('Comment author comment approved', 'geodirectory'), 'type' => 'title', 'desc' => '', 'id' => 'author_email_comment_approved_settings'),

					array('type' => 'sectionend', 'id' => 'author_email_comment_approved_settings'),

				));
			}elseif($current_section == 'admin_emails'){
				$settings = apply_filters( 'woocommerce_admin_email_settings', array(


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
				$settings = apply_filters( 'woocommerce_design_settings', array(


					array('name' => __('Image Settings', 'geodirectory'), 'type' => 'title', 'desc' => '', 'id' => 'design_settings'),

					array(
						'name' => __('Listing default image', 'geodirectory'),
						'desc' => __('This will be used for listings that have no images uploaded. This can be made more specific by adding a default category image. ', 'geodirectory'),
						'id' => 'listing_default_image',
						'type' => 'image',
						'default' => 0,
						'desc_tip' => true,
					),
					array('type' => 'sectionend', 'id' => 'design_settings'),

					array('name' => __('Email BCC options', 'geodirectory'), 'type' => 'title', 'desc' => '', 'id' => 'email_settings_bcc'),




					array('type' => 'sectionend', 'id' => 'email_settings_bcc'),

				));
			}



			return apply_filters( 'woocommerce_get_settings_' . $this->id, $settings, $current_section );
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
				get_bloginfo( 'name', 'display' ) . ' - ' . __( 'Powered by GeoDirectory', 'geodirectory' ) 
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

	}

endif;

return new GeoDir_Settings_Design();
