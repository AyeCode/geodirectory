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
				''          	=> __( 'Design options', 'woocommerce' ),
				'admin_emails'       => __( 'Archives', 'woocommerce' ),
				'client_emails' 	=> __( 'Details', 'woocommerce' ),
				'other_emails' 	=> __( 'Reviews', 'woocommerce' ),
			);

			return apply_filters( 'woocommerce_get_sections_' . $this->id, $sections );
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


					array('name' => __('Send to friend', 'geodirectory'), 'type' => 'title', 'desc' => '', 'id' => 'admin_other_friend_settings'),



					array('type' => 'sectionend', 'id' => 'user_other_friend_settings'),

					array('name' => __('Send enquiry', 'geodirectory'), 'type' => 'title', 'desc' => '', 'id' => 'admin_other_enquiry_settings'),



					array('type' => 'sectionend', 'id' => 'user_other_enquiry_settings'),

				));
			}
			elseif($current_section == 'client_emails'){
				$settings = apply_filters( 'woocommerce_user_email_settings', array(


					array('name' => __('Listing submitted', 'geodirectory'), 'type' => 'title', 'desc' => '', 'id' => 'admin_user_submitted_settings'),



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

	}

endif;

return new GeoDir_Settings_Design();
