<?php
/**
 * GeoDirectory Email Settings
 *
 * @author   AyeCode
 * @category Admin
 * @package  GeoDirectory/Admin
 * @version  2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'GeoDir_Settings_Emails', false ) ) :

	/**
	 * GeoDir_Settings_Products.
	 */
	class GeoDir_Settings_Emails extends GeoDir_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {

			$this->id    = 'emails';
			$this->label = __( 'Emails', 'geodirectory' );

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
				''          	=> __( 'Email options', 'geodirectory' ),
				'admin_emails'       => __( 'Admin emails', 'geodirectory' ),
				'client_emails' 	=> __( 'User emails', 'geodirectory' ),
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

			if($current_section == 'client_emails'){
				$settings = apply_filters( 'geodir_user_email_settings', array(


					array('name' => __('Pending listing submitted', 'geodirectory'), 'type' => 'title', 'desc' => '', 'id' => 'admin_user_pending_post_settings'),

					array(
						'name' => __('Enable email', 'geodirectory'),
						'desc' => __('Send an email to user when their submitted listing is under pending review.', 'geodirectory'),
						'id' => 'email_user_pending_post',
						'type' => 'checkbox',
						'default' => 1,
					),
					array(
						'name' => __('Subject', 'geodirectory'),
						'desc' => __('The email subject.', 'geodirectory'),
						'id' => 'email_user_pending_post_subject',
						'type' => 'text',
						'class' => 'active-placeholder',
						'desc_tip' => true,
						'placeholder' => GeoDir_Defaults::email_user_pending_post_subject(),
						'advanced' => true
					),
					array(
						'name' => __('Body', 'geodirectory'),
						'desc' => __('The email body, this can be text or HTML.', 'geodirectory'),
						'id' => 'email_user_pending_post_body',
						'type' => 'textarea',
						'class' => 'code gd-email-body',
						'desc_tip' => true,
						'advanced' => true,
						'placeholder' => GeoDir_Defaults::email_user_pending_post_body(),
						'custom_desc' => __('Available template tags:', 'geodirectory') . ' ' . $this->user_pending_post_email_tags()
					),

					array('type' => 'sectionend', 'id' => 'user_email_submitted_settings'),


					array('name' => __('Listing published', 'geodirectory'), 'type' => 'title', 'desc' => '', 'id' => 'user_email_published_settings'),


					array(
						'name' => __('Enable email', 'geodirectory'),
						'desc' => __('Send an email to user when their listing is published.', 'geodirectory'),
						'id' => 'email_user_publish_post',
						'type' => 'checkbox',
						'default' => 1,
					),
					array(
						'name' => __('Subject', 'geodirectory'),
						'desc' => __('The email subject.', 'geodirectory'),
						'id' => 'email_user_publish_post_subject',
						'type' => 'text',
						'class' => 'active-placeholder',
						'desc_tip' => true,
						'placeholder' => GeoDir_Defaults::email_user_publish_post_subject(),
						'advanced' => true
					),
					array(
						'name' => __('Body', 'geodirectory'),
						'desc' => __('The email body, this can be text or HTML.', 'geodirectory'),
						'id' => 'email_user_publish_post_body',
						'type' => 'textarea',
						'class' => 'code gd-email-body',
						'desc_tip' => true,
						'advanced' => true,
						'placeholder' => GeoDir_Defaults::email_user_publish_post_body(),
						'custom_desc' => __('Available template tags:', 'geodirectory') . ' ' . $this->user_publish_post_email_tags()
					),

					array('type' => 'sectionend', 'id' => 'user_email_published_settings'),

					array('name' => __('Listing owner comment submitted', 'geodirectory'), 'type' => 'title', 'desc' => '', 'id' => 'owner_email_comment_settings'),
					array(
						'name' => __('Enable email', 'geodirectory'),
						'desc' => __('Send an email to the listing owner when a comment is submitted.', 'geodirectory'),
						'id' => 'email_owner_comment_submit',
						'type' => 'checkbox',
						'default' => 0,
					),
					array(
						'name' => __('Subject', 'geodirectory'),
						'desc' => __('The email subject.', 'geodirectory'),
						'id' => 'email_owner_comment_submit_subject',
						'type' => 'text',
						'class' => 'active-placeholder',
						'desc_tip' => true,
						'placeholder' => GeoDir_Defaults::email_owner_comment_submit_subject(),
						'advanced' => true
					),
					array(
						'name' => __('Body', 'geodirectory'),
						'desc' => __('The email body, this can be text or HTML.', 'geodirectory'),
						'id' => 'email_owner_comment_submit_body',
						'type' => 'textarea',
						'class' => 'code gd-email-body',
						'desc_tip' => true,
						'advanced' => true,
						'placeholder' => GeoDir_Defaults::email_owner_comment_submit_body(),
						'custom_desc' => __('Available template tags:', 'geodirectory') . ' ' . $this->owner_comment_submit_email_tags()
					),
					array('type' => 'sectionend', 'id' => 'owner_email_comment_settings'),


					array('name' => __('Listing owner comment approved', 'geodirectory'), 'type' => 'title', 'desc' => '', 'id' => 'owner_email_comment_approved_settings'),
					array(
						'name' => __('Enable email', 'geodirectory'),
						'desc' => __('Send an email to the listing owner when a comment is approved.', 'geodirectory'),
						'id' => 'email_owner_comment_approved',
						'type' => 'checkbox',
						'default' => 1,
					),
					array(
						'name' => __('Subject', 'geodirectory'),
						'desc' => __('The email subject.', 'geodirectory'),
						'id' => 'email_owner_comment_approved_subject',
						'type' => 'text',
						'class' => 'active-placeholder',
						'desc_tip' => true,
						'placeholder' => GeoDir_Defaults::email_owner_comment_approved_subject(),
						'advanced' => true
					),
					array(
						'name' => __('Body', 'geodirectory'),
						'desc' => __('The email body, this can be text or HTML.', 'geodirectory'),
						'id' => 'email_owner_comment_approved_body',
						'type' => 'textarea',
						'class' => 'code gd-email-body',
						'desc_tip' => true,
						'advanced' => true,
						'placeholder' => GeoDir_Defaults::email_owner_comment_approved_body(),
						'custom_desc' => __('Available template tags:', 'geodirectory') . ' ' . $this->owner_comment_approved_email_tags()
					),
					array('type' => 'sectionend', 'id' => 'owner_email_comment_approved_settings'),

					array('name' => __('Comment author comment approved', 'geodirectory'), 'type' => 'title', 'desc' => '', 'id' => 'author_email_comment_approved_settings'),
					array(
						'name' => __('Enable email', 'geodirectory'),
						'desc' => __('Send an email to the comment author when a comment is approved.', 'geodirectory'),
						'id' => 'email_author_comment_approved',
						'type' => 'checkbox',
						'default' => 1,
					),
					array(
						'name' => __('Subject', 'geodirectory'),
						'desc' => __('The email subject.', 'geodirectory'),
						'id' => 'email_author_comment_approved_subject',
						'type' => 'text',
						'class' => 'active-placeholder',
						'desc_tip' => true,
						'placeholder' => GeoDir_Defaults::email_author_comment_approved_subject(),
						'advanced' => true
					),
					array(
						'name' => __('Body', 'geodirectory'),
						'desc' => __('The email body, this can be text or HTML.', 'geodirectory'),
						'id' => 'email_author_comment_approved_body',
						'type' => 'textarea',
						'class' => 'code gd-email-body',
						'desc_tip' => true,
						'advanced' => true,
						'placeholder' => GeoDir_Defaults::email_author_comment_approved_body(),
						'custom_desc' => __('Available template tags:', 'geodirectory') . ' ' . $this->author_comment_approved_email_tags()
					),
					array('type' => 'sectionend', 'id' => 'author_email_comment_approved_settings'),

				));
			}elseif($current_section == 'admin_emails'){
				$settings = apply_filters( 'geodir_admin_email_settings', array(


					array('name' => __('Pending listing submitted', 'geodirectory'), 'type' => 'title', 'desc' => '', 'id' => 'admin_email_submitted_settings'),

					array(
						'name' => __('Enable email', 'geodirectory'),
						'desc' => __('Send an email to admin when the listing submitted by a user is under pending review.', 'geodirectory'),
						'id' => 'email_admin_pending_post',
						'type' => 'checkbox',
						'default' => 1,
					),
					array(
						'name' => __('Subject', 'geodirectory'),
						'desc' => __('The email subject.', 'geodirectory'),
						'id' => 'email_admin_pending_post_subject',
						'type' => 'text',
						'class' => 'active-placeholder',
						'desc_tip' => true,
						'placeholder' => GeoDir_Defaults::email_admin_pending_post_subject(),
						'advanced' => true
					),
					array(
						'name' => __('Body', 'geodirectory'),
						'desc' => __('The email body, this can be text or HTML.', 'geodirectory'),
						'id' => 'email_admin_pending_post_body',
						'type' => 'textarea',
						'class' => 'code gd-email-body',
						'desc_tip' => true,
						'advanced' => true,
						'placeholder' => GeoDir_Defaults::email_admin_pending_post_body(),
						'custom_desc' => __('Available template tags:', 'geodirectory') . ' ' . $this->admin_pending_post_email_tags()
					),

					array('type' => 'sectionend', 'id' => 'admin_email_submitted_settings'),


					array('name' => __('Listing edited', 'geodirectory'), 'type' => 'title', 'desc' => '', 'id' => 'admin_email_edited_settings'),


					array(
						'name' => __('Enable email', 'geodirectory'),
						'desc' => __('Send an email when a listing is edited by a user. (this will not send email when edited by an admin)', 'geodirectory'),
						'id' => 'email_admin_post_edit',
						'type' => 'checkbox',
						'default' => 0,
					),
					array(
						'name' => __('Subject', 'geodirectory'),
						'desc' => __('The email subject.', 'geodirectory'),
						'id' => 'email_admin_post_edit_subject',
						'type' => 'text',
						'class' => 'active-placeholder',
						'desc_tip' => true,
						'placeholder' => GeoDir_Defaults::email_admin_post_edit_subject(),
						'advanced' => true
					),
					array(
						'name' => __('Body', 'geodirectory'),
						'desc' => __('The email body, this can be text or HTML.', 'geodirectory'),
						'id' => 'email_admin_post_edit_body',
						'type' => 'textarea',
						'class' => 'code gd-email-body',
						'desc_tip' => true,
						'advanced' => true,
						'placeholder' => GeoDir_Defaults::email_admin_post_edit_body(),
						'custom_desc' => __('Available template tags:', 'geodirectory') . ' ' . $this->admin_post_edit_email_tags()
					),

					array('type' => 'sectionend', 'id' => 'admin_email_edited_settings'),

					array('name' => __('Moderate comment', 'geodirectory'), 'type' => 'title', 'desc' => '', 'id' => 'admin_email_comment_settings'),


					array(
						'name' => __('Enable email', 'geodirectory'),
						'desc' => __('Send an email when a comment/review needs approval. This will takeover the standard WordPress email.', 'geodirectory'),
						'id' => 'email_admin_moderate_comment',
						'type' => 'checkbox',
						'default' => 0,
					),
					array(
						'name' => __('Subject', 'geodirectory'),
						'desc' => __('The email subject.', 'geodirectory'),
						'id' => 'email_admin_moderate_comment_subject',
						'type' => 'text',
						'class' => 'active-placeholder',
						'desc_tip' => true,
						'placeholder' => GeoDir_Defaults::email_admin_moderate_comment_subject(),
						'advanced' => true
					),
					array(
						'name' => __('Body', 'geodirectory'),
						'desc' => __('The email body, this can be text or HTML.', 'geodirectory'),
						'id' => 'email_admin_moderate_comment_body',
						'type' => 'textarea',
						'class' => 'code gd-email-body',
						'desc_tip' => true,
						'advanced' => true,
						'placeholder' => GeoDir_Defaults::email_admin_moderate_comment_body(),
						'custom_desc' => __('Available template tags:', 'geodirectory') . ' ' . $this->admin_moderate_comment_email_tags()
					),

					array('type' => 'sectionend', 'id' => 'admin_email_comment_settings'),

				));
			}else{
				$settings = apply_filters( 'geodir_email_settings', array(


					array('name' => __('Email sender options', 'geodirectory'), 'type' => 'title', 'desc' => '', 'id' => 'email_settings'),

					array(
						'name' => __('Sender name', 'geodirectory'),
						'desc' => __('How the sender name appears in outgoing GeoDirectory emails.', 'geodirectory'),
						'id' => 'email_name',
						'type' => 'text',
						'placeholder' => GeoDir_Defaults::email_name(),
						'desc_tip' => true,
					),
					array(
						'name' => __('Email address', 'geodirectory'),
						'desc' => __('How the sender email appears in outgoing GeoDirectory emails.', 'geodirectory'),
						'id' => 'email_address',
						'type' => 'email',
						'placeholder' => GeoDir_Defaults::email_address(),
						'desc_tip' => true,
					),
					array('type' => 'sectionend', 'id' => 'email_settings'),

					array('name' => __('Email BCC options', 'geodirectory'), 'type' => 'title', 'desc' => '', 'id' => 'email_settings_bcc'),
					array(
						'name' => __('Listing pending approval', 'geodirectory'),
						'desc' => __('This will send a BCC email to the site admin on listing submitted for approval.', 'geodirectory'),
						'id' => 'email_bcc_user_pending_post',
						'type' => 'checkbox',
						'default' => 0,
					),
					array(
						'name' => __('Listing published', 'geodirectory'),
						'desc' => __('This will send a BCC email to the site admin on listing published.', 'geodirectory'),
						'id' => 'email_bcc_user_publish_post',
						'type' => 'checkbox',
						'default' => 0,
					),
					array(
						'name' => __('Listing owner comment submitted', 'geodirectory'),
						'desc' => __('This will send a BCC email to the site admin on listing owner comment submitted.', 'geodirectory'),
						'id' => 'email_bcc_owner_comment_submit',
						'type' => 'checkbox',
						'default' => 0,
					),
					array(
						'name' => __('Send enquiry', 'geodirectory'),
						'desc' => __('This will send a BCC email to the site admin on send enquiry submit.', 'geodirectory'),
						'id' => 'email_bcc_send_enquiry',
						'type' => 'checkbox',
						'default' => 0,
					),


					array('type' => 'sectionend', 'id' => 'email_settings_bcc'),

				));
			}



			return apply_filters( 'geodir_get_settings_' . $this->id, $settings, $current_section );
		}






        /**
         * Global email tags.
         *
         * @since  2.0.0
         *
         * @param bool $inline Optional. Email tag inline value. Default true.
         * @return array|string $tags.
         */
		public function global_email_tags( $inline = true ) {
			$tags = array( '[#blogname#]', '[#site_name#]', '[#site_url#]', '[#site_name_url#]', '[#login_url#]', '[#login_link#]', '[#date#]', '[#time#]', '[#date_time#]', '[#current_date#]', '[#to_name#]', '[#to_email#]', '[#from_name#]', '[#from_email#]' );

			$tags = apply_filters( 'geodir_email_global_email_tags', $tags );

			if ( $inline ) {
				$tags = '<code>' . implode( '</code> <code>', $tags ) . '</code>';
			}

			return $tags;
		}


        /**
         * User pending post email tags.
         *
         * @since  2.0.0
         *
         * @param bool $inline Optional. Email tag inline value. Default true.
         *
         * @return array|string $tags.
         */
		public function user_pending_post_email_tags( $inline = true ) {
			$global_tags = $this->global_email_tags( false );

			$tags = array_merge( $global_tags, array( '[#post_id#]', '[#post_status#]', '[#post_date#]', '[#post_author_ID#]', '[#post_author_name#]', '[#client_name#]', '[#listing_title#]', '[#listing_url#]', '[#listing_link#]' ) );

			$tags = apply_filters( 'geodir_email_user_pending_post_email_tags', $tags );

			if ( $inline ) {
				$tags = '<code>' . implode( '</code> <code>', $tags ) . '</code>';
			}

			return $tags;
		}

        /**
         * User publish post email tags.
         *
         * @since  2.0.0
         *
         * @param bool $inline Optional. Email tag inline value. Default true.
         *
         * @return array|string $tags.
         */
		public function user_publish_post_email_tags( $inline = true ) {
			$global_tags = $this->global_email_tags( false );

			$tags = array_merge( $global_tags, array( '[#post_id#]', '[#post_status#]', '[#post_date#]', '[#post_author_ID#]', '[#post_author_name#]', '[#client_name#]', '[#listing_title#]', '[#listing_url#]', '[#listing_link#]' ) );

			$tags = apply_filters( 'geodir_email_user_publish_post_email_tags', $tags );

			if ( $inline ) {
				$tags = '<code>' . implode( '</code> <code>', $tags ) . '</code>';
			}

			return $tags;
		}

        /**
         * Owner submit comment email tags.
         *
         * @since  2.0.0
         *
         * @param bool $inline Optional. Email tag inline value. Default true.
         *
         * @return array|string $tags.
         */
		public function owner_comment_submit_email_tags( $inline = true ) {
			$global_tags = $this->global_email_tags( false );

			$tags = array_merge( $global_tags, array( '[#post_id#]', '[#post_status#]', '[#post_date#]', '[#post_author_ID#]', '[#post_author_name#]', '[#client_name#]', '[#listing_title#]', '[#listing_url#]', '[#listing_link#]', '[#comment_ID#]', '[#comment_author#]', '[#comment_author_IP#]', '[#comment_author_email#]', '[#comment_date#]', '[#comment_content#]', '[#comment_post_ID#]', '[#comment_post_title#]', '[#comment_post_url#]', '[#comment_approve_link#]', '[#comment_trash_link#]', '[#comment_spam_link#]', '[#review_rating_star#]', '[#review_rating_title#]', '[#review_city#]', '[#review_region#]', '[#review_country#]', '[#review_latitude#]', '[#review_longitude#]' ) );

			$tags = apply_filters( 'geodir_email_owner_comment_submit_email_tags', $tags );

			if ( $inline ) {
				$tags = '<code>' . implode( '</code> <code>', $tags ) . '</code>';
			}

			return $tags;
		}

        /**
         * Owner approved comment email tags.
         *
         * @since  2.0.0
         *
         * @param bool $inline Optional. Email tag inline value. Default true.
         *
         * @return array|string $tags.
         */
		public function owner_comment_approved_email_tags( $inline = true ) {
			$global_tags = $this->global_email_tags( false );

			$tags = array_merge( $global_tags, array( '[#post_id#]', '[#post_status#]', '[#post_date#]', '[#post_author_ID#]', '[#post_author_name#]', '[#client_name#]', '[#listing_title#]', '[#listing_url#]', '[#listing_link#]', '[#comment_ID#]', '[#comment_author#]', '[#comment_author_IP#]', '[#comment_author_email#]', '[#comment_date#]', '[#comment_content#]', '[#comment_post_ID#]', '[#comment_post_title#]', '[#comment_post_url#]', '[#review_rating_star#]', '[#review_rating_title#]', '[#review_city#]', '[#review_region#]', '[#review_country#]', '[#review_latitude#]', '[#review_longitude#]' ) );

			$tags = apply_filters( 'geodir_email_owner_comment_approved_email_tags', $tags );

			if ( $inline ) {
				$tags = '<code>' . implode( '</code> <code>', $tags ) . '</code>';
			}

			return $tags;
		}

        /**
         * Author approved comment email tags.
         *
         * @since  2.0.0
         *
         * @param bool $inline Optional. Email tag inline value. Default true.
         *
         * @return array|string $tags.
         */
		public function author_comment_approved_email_tags( $inline = true ) {
			$global_tags = $this->global_email_tags( false );

			$tags = array_merge( $global_tags, array( '[#post_id#]', '[#post_status#]', '[#post_date#]', '[#post_author_ID#]', '[#post_author_name#]', '[#client_name#]', '[#listing_title#]', '[#listing_url#]', '[#listing_link#]', '[#comment_ID#]', '[#comment_author#]', '[#comment_author_IP#]', '[#comment_author_email#]', '[#comment_date#]', '[#comment_content#]', '[#comment_url#]', '[#comment_post_ID#]', '[#comment_post_title#]', '[#comment_post_url#]', '[#comment_post_link#]', '[#review_rating_star#]', '[#review_rating_title#]', '[#review_city#]', '[#review_region#]', '[#review_country#]', '[#review_latitude#]', '[#review_longitude#]' ) );

			$tags = apply_filters( 'geodir_email_author_comment_approved_email_tags', $tags );

			if ( $inline ) {
				$tags = '<code>' . implode( '</code> <code>', $tags ) . '</code>';
			}

			return $tags;
		}

        /**
         * Admin pending post email tags.
         *
         * @since  2.0.0
         *
         * @param bool $inline Optional. Email tag inline value. Default true.
         *
         * @return array|string $tags.
         */
		public function admin_pending_post_email_tags( $inline = true ) {
			$global_tags = $this->global_email_tags( false );

			$tags = array_merge( $global_tags, array( '[#post_id#]', '[#post_status#]', '[#post_date#]', '[#post_author_ID#]', '[#post_author_name#]', '[#client_name#]', '[#listing_title#]', '[#listing_url#]', '[#listing_link#]' ) );

			$tags = apply_filters( 'geodir_email_admin_pending_post_email_tags', $tags );

			if ( $inline ) {
				$tags = '<code>' . implode( '</code> <code>', $tags ) . '</code>';
			}

			return $tags;
		}

        /**
         * Admin edit post email tags.
         *
         * @since  2.0.0
         *
         * @param bool $inline Optional. Email tag inline value. Default true.
         *
         * @return array|string $tags.
         */
		public function admin_post_edit_email_tags( $inline = true ) {
			$global_tags = $this->global_email_tags( false );

			$tags = array_merge( $global_tags, array( '[#post_id#]', '[#post_status#]', '[#post_date#]', '[#post_author_ID#]', '[#post_author_name#]', '[#client_name#]', '[#listing_title#]', '[#listing_url#]', '[#listing_link#]' ) );

			$tags = apply_filters( 'geodir_email_admin_post_edit_email_tags', $tags );

			if ( $inline ) {
				$tags = '<code>' . implode( '</code> <code>', $tags ) . '</code>';
			}

			return $tags;
		}

        /**
         * Admin moderate comment email tags.
         *
         * @since  2.0.0
         *
         * @param bool $inline Optional. Email tag inline value. Default true.
         *
         * @return array|string $tags.
         */
		public function admin_moderate_comment_email_tags( $inline = true ) {
			$global_tags = $this->global_email_tags( false );

			$tags = array_merge( $global_tags, array( '[#post_id#]', '[#post_status#]', '[#post_date#]', '[#post_author_ID#]', '[#post_author_name#]', '[#client_name#]', '[#listing_title#]', '[#listing_url#]', '[#listing_link#]', '[#comment_ID#]', '[#comment_author#]', '[#comment_author_IP#]', '[#comment_author_email#]', '[#comment_date#]', '[#comment_content#]', '[#comment_post_ID#]', '[#comment_post_title#]', '[#comment_post_url#]', '[#comment_approve_link#]', '[#comment_trash_link#]', '[#comment_spam_link#]', '[#comment_moderation_link#]', '[#review_rating_star#]', '[#review_rating_title#]', '[#review_city#]', '[#review_region#]', '[#review_country#]', '[#review_latitude#]', '[#review_longitude#]' ) );

			$tags = apply_filters( 'geodir_email_admin_moderate_comment_email_tags', $tags );

			if ( $inline ) {
				$tags = '<code>' . implode( '</code> <code>', $tags ) . '</code>';
			}

			return $tags;
		}
	}

endif;

return new GeoDir_Settings_Emails();
