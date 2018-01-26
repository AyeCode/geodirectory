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
			$this->label = __( 'Emails', 'woocommerce' );

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
				''          	=> __( 'Email options', 'woocommerce' ),
				'admin_emails'       => __( 'Admin emails', 'woocommerce' ),
				'client_emails' 	=> __( 'User emails', 'woocommerce' ),
				'other_emails' 	=> __( 'Other emails', 'woocommerce' ),
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


					array('name' => __('Send to friend', 'geodirectory'), 'type' => 'title', 'desc' => '', 'id' => 'admin_send_friend_settings'),

					array(
						'name' => __('Enable email', 'geodirectory'),
						'desc' => __('This will enable the "Send to friend" option for the details page.', 'geodirectory'),
						'id' => 'email_send_friend',
						'type' => 'checkbox',
						'default' => 1,
					),
					array(
						'name' => __('Subject', 'geodirectory'),
						'desc' => __('The email subject.', 'geodirectory'),
						'id' => 'email_send_friend_subject',
						'type' => 'text',
						'desc_tip' => true,
						'class' => 'large-text',
						'placeholder' => $this->send_friend_subject(),
						'advanced' => true
					),
					array(
						'name' => __('Body', 'geodirectory'),
						'desc' => __('The email body, this can be text or HTML.', 'geodirectory'),
						'id' => 'email_send_friend_body',
						'type' => 'textarea',
						'class' => 'code gd-email-body',
						'desc_tip' => true,
						'advanced' => true,
						'placeholder' => $this->send_friend_body(),
						'custom_desc' => __('Available template tags:', 'geodirectory') . ' ' . $this->send_friend_email_tags()
					),

					array('type' => 'sectionend', 'id' => 'user_send_friend_settings'),

					array('name' => __('Send enquiry', 'geodirectory'), 'type' => 'title', 'desc' => '', 'id' => 'admin_send_enquiry_settings'),

					array(
						'name' => __('Enable email', 'geodirectory'),
						'desc' => __('This will enable the "Send enquiry" option for the details page.', 'geodirectory'),
						'id' => 'email_send_enquiry',
						'type' => 'checkbox',
						'default' => 1,
					),
					array(
						'name' => __('Subject', 'geodirectory'),
						'desc' => __('The email subject.', 'geodirectory'),
						'id' => 'email_send_enquiry_subject',
						'type' => 'text',
						'class' => 'large-text',
						'desc_tip' => true,
						'placeholder' => $this->send_enquiry_subject(),
						'advanced' => true
					),
					array(
						'name' => __('Body', 'geodirectory'),
						'desc' => __('The email body, this can be text or HTML.', 'geodirectory'),
						'id' => 'email_send_enquiry_body',
						'type' => 'textarea',
						'class' => 'code gd-email-body',
						'desc_tip' => true,
						'advanced' => true,
						'placeholder' => $this->send_enquiry_body(),
						'custom_desc' => __('Available template tags:', 'geodirectory') . ' ' . $this->send_enquiry_email_tags()
					),

					array('type' => 'sectionend', 'id' => 'user_send_enquiry_settings'),

				));
			}
			elseif($current_section == 'client_emails'){
				$settings = apply_filters( 'woocommerce_user_email_settings', array(


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
						'class' => 'large-text',
						'desc_tip' => true,
						'placeholder' => $this->user_pending_post_subject(),
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
						'placeholder' => $this->user_pending_post_body(),
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
						'class' => 'large-text',
						'desc_tip' => true,
						'placeholder' => $this->user_publish_post_subject(),
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
						'placeholder' => $this->user_publish_post_body(),
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
						'class' => 'large-text',
						'desc_tip' => true,
						'placeholder' => $this->owner_comment_submit_subject(),
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
						'placeholder' => $this->owner_comment_submit_body(),
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
						'class' => 'large-text',
						'desc_tip' => true,
						'placeholder' => $this->owner_comment_approved_subject(),
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
						'placeholder' => $this->owner_comment_approved_body(),
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
						'class' => 'large-text',
						'desc_tip' => true,
						'placeholder' => $this->author_comment_approved_subject(),
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
						'placeholder' => $this->author_comment_approved_body(),
						'custom_desc' => __('Available template tags:', 'geodirectory') . ' ' . $this->author_comment_approved_email_tags()
					),
					array('type' => 'sectionend', 'id' => 'author_email_comment_approved_settings'),

				));
			}elseif($current_section == 'admin_emails'){
				$settings = apply_filters( 'woocommerce_admin_email_settings', array(


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
						'class' => 'large-text',
						'desc_tip' => true,
						'placeholder' => $this->admin_pending_post_subject(),
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
						'placeholder' => $this->admin_pending_post_body(),
						'custom_desc' => __('Available template tags:', 'geodirectory') . ' ' . $this->admin_pending_post_email_tags()
					),

					array('type' => 'sectionend', 'id' => 'admin_email_submitted_settings'),


					array('name' => __('Listing edited', 'geodirectory'), 'type' => 'title', 'desc' => '', 'id' => 'admin_email_edited_settings'),


					array(
						'name' => __('Enable email', 'geodirectory'),
						'desc' => __('Send an email when a listing is edited by a user.', 'geodirectory'),
						'id' => 'email_admin_post_edit',
						'type' => 'checkbox',
						'default' => 0,
					),
					array(
						'name' => __('Subject', 'geodirectory'),
						'desc' => __('The email subject.', 'geodirectory'),
						'id' => 'email_admin_post_edit_subject',
						'type' => 'text',
						'class' => 'large-text',
						'desc_tip' => true,
						'placeholder' => $this->admin_post_edit_subject(),
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
						'placeholder' => $this->admin_post_edit_body(),
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
						'class' => 'large-text',
						'desc_tip' => true,
						'placeholder' => $this->admin_moderate_comment_subject(),
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
						'placeholder' => $this->admin_moderate_comment_body(),
						'custom_desc' => __('Available template tags:', 'geodirectory') . ' ' . $this->admin_moderate_comment_email_tags()
					),

					array('type' => 'sectionend', 'id' => 'admin_email_comment_settings'),

				));
			}else{
				$settings = apply_filters( 'woocommerce_email_settings', array(


					array('name' => __('Email sender options', 'geodirectory'), 'type' => 'title', 'desc' => '', 'id' => 'email_settings'),

					array(
						'name' => __('Sender name', 'geodirectory'),
						'desc' => __('How the sender name appears in outgoing GeoDirectory emails.', 'geodirectory'),
						'id' => 'email_name',
						'type' => 'text',
						'placeholder' => $this->email_name(),
						'desc_tip' => true,
					),
					array(
						'name' => __('Email address', 'geodirectory'),
						'desc' => __('How the sender email appears in outgoing GeoDirectory emails.', 'geodirectory'),
						'id' => 'email_address',
						'type' => 'text',
						'placeholder' => $this->email_address(),
						'desc_tip' => true,
					),
					array('type' => 'sectionend', 'id' => 'email_settings'),

					array('name' => __('Email BCC options', 'geodirectory'), 'type' => 'title', 'desc' => '', 'id' => 'email_settings_bcc'),
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
						'name' => __('Send to friend', 'geodirectory'),
						'desc' => __('This will send a BCC email to the site admin on send to friend submit.', 'geodirectory'),
						'id' => 'email_bcc_send_friend',
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
		 * The default email address.
		 *
		 * @since 2.0.0
		 * @return string
		 */
		public function email_address(){
			return get_bloginfo('admin_email');
		}

		/**
		 * The default client published email subject text.
		 *
		 * @since 2.0.0
		 * @return string
		 */
		public function user_publish_post_subject(){
			return apply_filters('geodir_email_user_publish_post_subject',__("[[#site_name#]] Listing Published Successfully","geodirectory"));
		}

		/**
		 * The default client published email body text.
		 *
		 * @since 2.0.0
		 * @return string
		 */
		public function user_publish_post_body(){
			return apply_filters('geodir_email_user_publish_post_body',
				__("Dear [#client_name#],

Your listing [#listing_link#] has been published. This email is just for your information.

[#listing_link#]

Thank you for your contribution.","geodirectory"
				)
			);
		}

		/**
		 * The default client submitted email subject text.
		 *
		 * @since 2.0.0
		 * @return string
		 */
		public function user_pending_post_subject(){
			return apply_filters('geodir_email_user_pending_post_subject',__("[[#site_name#]] Your listing has been submitted for approval","geodirectory"));
		}

		/**
		 * The default client submitted email body text.
		 *
		 * @since 2.0.0
		 * @return string
		 */
		public function user_pending_post_body(){
			return apply_filters('geodir_email_user_pending_post_body',
				__("Dear [#client_name#],

You submitted the below listing information. This email is just for your information.

[#listing_link#]

Thank you for your contribution.","geodirectory"
				)
			);
		}

		/**
		 * The default admin submitted email subject text.
		 *
		 * @since 2.0.0
		 * @return string
		 */
		public function admin_pending_post_subject(){
			return apply_filters('geodir_email_admin_pending_post_subject',__("[[#site_name#]] A new listing has been submitted for review","geodirectory"));
		}

		/**
		 * The default admin submitted email body text.
		 *
		 * @since 2.0.0
		 * @return string
		 */
		public function admin_pending_post_body(){
			return apply_filters('geodir_email_admin_pending_post_body',
				__("Dear Admin,

A new listing has been submitted [#listing_link#]. This email is just for your information.

Thank you,

[#site_name_url#]","geodirectory")
			);
		}

		/**
		 * The default admin edited email subject text.
		 *
		 * @since 2.0.0
		 * @return string
		 */
		public function admin_post_edit_subject(){
			return apply_filters('geodir_email_admin_post_edit_subject',__("[[#site_name#]] Listing edited by Author","geodirectory"));
		}

		/**
		 * The default admin edited email body text.
		 *
		 * @since 2.0.0
		 * @return string
		 */
		public function admin_post_edit_body(){
			return apply_filters('geodir_email_admin_post_edit_body',
				__("Dear Admin,
				
A listing [#listing_link#] has been edited by it's author [#post_author_name#].

Listing Details:
Listing ID: [#post_id#]
Listing URL: [#listing_link#]
Date: [#current_date#]

This email is just for your information.","geodirectory"
				)
			);
		}

		/**
		 * The default admin comment submitted email subject text.
		 *
		 * @since 2.0.0
		 * @return string
		 */
		public function admin_moderate_comment_subject(){
			return apply_filters('geodir_email_admin_moderate_comment_subject',__("[[#site_name#]] A new comment is waiting for your approval","geodirectory"));
		}

		/**
		 * The default admin comment submitted email body text.
		 *
		 * @since 2.0.0
		 * @return string
		 */
		public function admin_moderate_comment_body(){
			return apply_filters('geodir_email_admin_moderate_comment_body',
				__("Dear Admin,
				
A new comment has been submitted on the listing [#listing_link#] and it is waiting for your approval.

Author: [#comment_author#] ( IP: [#comment_author_IP#] )
Email: [#comment_author_email#]
Listing: [#listing_url#]
Date: [#comment_date#]
Comment: [#comment_content#]

Approve it: [#comment_approve_link#]
Trash it: [#comment_trash_link#]
Spam it: [#comment_spam_link#]

Please visit the moderation panel for more details: [#comment_moderation_link#]

Thank You.","geodirectory"
				)
			);
		}

		/**
		 * The default owner comment submitted email subject text.
		 *
		 * @since 2.0.0
		 * @return string
		 */
		public function owner_comment_submit_subject(){
			return apply_filters('geodir_email_owner_comment_submit_subject',__("[[#site_name#]] A new comment has been submitted on your listing [#listing_title#]","geodirectory"));
		}

		/**
		 * The default owner comment submitted email body text.
		 *
		 * @since 2.0.0
		 * @return string
		 */
		public function owner_comment_submit_body(){
			return apply_filters('geodir_email_owner_comment_submit_body',
				__("Dear [#client_name#],

A new comment has been submitted on your listing [#listing_link#].

Author: [#comment_author#] ( IP: [#comment_author_IP#] )
Email: [#comment_author_email#]
Listing: [#listing_url#]
Date: [#comment_date#]
Comment: [#comment_content#]

Approve it: [#comment_approve_link#]
Trash it: [#comment_trash_link#]
Spam it: [#comment_spam_link#]

Thank You.","geodirectory"
				)
			);
		}

		/**
		 * The default owner comment submitted email subject text.
		 *
		 * @since 2.0.0
		 * @return string
		 */
		public function owner_comment_approved_subject(){
			return apply_filters('geodir_email_owner_comment_approved_subject',__("[[#site_name#]] A comment on your listing [#listing_title#] has been approved","geodirectory"));
		}

		/**
		 * The default owner comment submitted email body text.
		 *
		 * @since 2.0.0
		 * @return string
		 */
		public function owner_comment_approved_body(){
			return apply_filters('geodir_email_owner_comment_approved_body',
				__("Dear [#client_name#],

A comment on your listing [#listing_link#] has been approved.

Author: [#comment_author#] ( IP: [#comment_author_IP#] )
Email: [#comment_author_email#]
Listing: [#listing_url#]
Comment: [#comment_content#]

Thank You.","geodirectory"
				)
			);
		}

		/**
		 * The default author comment submitted email subject text.
		 *
		 * @since 2.0.0
		 * @return string
		 */
		public function author_comment_approved_subject(){
			return apply_filters('geodir_email_author_comment_approved_subject',__("[[#site_name#]] Your comment on listing [#listing_title#] has been approved","geodirectory"));
		}

		/**
		 * The default author comment submitted email body text.
		 *
		 * @since 2.0.0
		 * @return string
		 */
		public function author_comment_approved_body(){
			return apply_filters('geodir_email_author_comment_approved_body',
				__("Dear [#comment_author#],

Your comment on listing [#listing_link#] has been approved.

Comment: [#comment_content#]

Thank You.","geodirectory"
				)
			);
		}

		/**
		 * The default send to friend email subject text.
		 *
		 * @since 2.0.0
		 * @return string
		 */
		public function send_friend_subject(){
			return apply_filters('geodir_email_send_friend_subject',__("[[#site_name#]] thought you might be interested in.","geodirectory"));
		}

		/**
		 * The default send to friend email body text.
		 *
		 * @since 2.0.0
		 * @return string
		 */
		public function send_friend_body(){
			return apply_filters('geodir_email_send_friend_body',
				__("Dear [#to_name#],

Your friend has sent you a message from <b>[#site_name#]</b>

===============================

<b>Subject : [#subject#]</b>

[#comments#] [#listing_link#]

===============================

Thank you,

[#site_name_url#].","geodirectory"
				)
			);
		}

		/**
		 * The default send enquiry email subject text.
		 *
		 * @since 2.0.0
		 * @return string
		 */
		public function send_enquiry_subject(){
			return apply_filters('geodir_email_send_enquiry_subject',__("[[#site_name#]] Website Enquiry","geodirectory"));
		}

		/**
		 * The default send enquiry email body text.
		 *
		 * @since 2.0.0
		 * @return string
		 */
		public function send_enquiry_body(){
			return apply_filters('geodir_email_send_enquiry_body',
				__("Dear [#to_name#],

An enquiry has been sent from <b>[#listing_link#]</b>

===============================

[#comments#]

From : [#from_name#]
Email : [#from_email#]
Phone : [#phone#]

Sent from - <b>{site_link}</b>.

===============================

Thank you,
[#site_name_url#].","geodirectory"
				)
			);
		}
		
		public function global_email_tags( $inline = true ) { 
			$tags = array( '[#blogname#]', '[#site_name#]', '[#site_url#]', '[#site_name_url#]', '[#login_url#]', '[#login_link#]', '[#date#]', '[#time#]', '[#date_time#]', '[#current_date#]', '[#to_name#]', '[#to_email#]', '[#from_name#]', '[#from_email#]' );
			
			$tags = apply_filters( 'geodir_email_global_email_tags', $tags );

			if ( $inline ) {
				$tags = '<code>' . implode( '</code> <code>', $tags ) . '</code>';
			}
			
			return $tags;
		}
		
		public function send_friend_email_tags( $inline = true ) { 
			$global_tags = $this->global_email_tags( false );

			$tags = array_merge( $global_tags, array( '[#post_id#]', '[#post_status#]', '[#post_date#]', '[#post_author_ID#]', '[#post_author_name#]', '[#client_name#]', '[#listing_title#]', '[#listing_url#]', '[#listing_link#]', '[#subject#]', '[#comments#]' ) );
			
			$tags = apply_filters( 'geodir_email_send_friend_email_tags', $tags );

			if ( $inline ) {
				$tags = '<code>' . implode( '</code> <code>', $tags ) . '</code>';
			}
			
			return $tags;
		}
		
		public function send_enquiry_email_tags( $inline = true ) { 
			$global_tags = $this->global_email_tags( false );

			$tags = array_merge( $global_tags, array( '[#post_id#]', '[#post_status#]', '[#post_date#]', '[#post_author_ID#]', '[#post_author_name#]', '[#client_name#]', '[#listing_title#]', '[#listing_url#]', '[#listing_link#]', '[#phone#]', '[#comments#]' ) );
			
			$tags = apply_filters( 'geodir_email_send_enquiry_email_tags', $tags );

			if ( $inline ) {
				$tags = '<code>' . implode( '</code> <code>', $tags ) . '</code>';
			}
			
			return $tags;
		}
		
		public function user_pending_post_email_tags( $inline = true ) { 
			$global_tags = $this->global_email_tags( false );

			$tags = array_merge( $global_tags, array( '[#post_id#]', '[#post_status#]', '[#post_date#]', '[#post_author_ID#]', '[#post_author_name#]', '[#client_name#]', '[#listing_title#]', '[#listing_url#]', '[#listing_link#]' ) );
			
			$tags = apply_filters( 'geodir_email_user_pending_post_email_tags', $tags );

			if ( $inline ) {
				$tags = '<code>' . implode( '</code> <code>', $tags ) . '</code>';
			}
			
			return $tags;
		}
		
		public function user_publish_post_email_tags( $inline = true ) { 
			$global_tags = $this->global_email_tags( false );

			$tags = array_merge( $global_tags, array( '[#post_id#]', '[#post_status#]', '[#post_date#]', '[#post_author_ID#]', '[#post_author_name#]', '[#client_name#]', '[#listing_title#]', '[#listing_url#]', '[#listing_link#]' ) );
			
			$tags = apply_filters( 'geodir_email_user_publish_post_email_tags', $tags );

			if ( $inline ) {
				$tags = '<code>' . implode( '</code> <code>', $tags ) . '</code>';
			}
			
			return $tags;
		}
		
		public function owner_comment_submit_email_tags( $inline = true ) { 
			$global_tags = $this->global_email_tags( false );

			$tags = array_merge( $global_tags, array( '[#post_id#]', '[#post_status#]', '[#post_date#]', '[#post_author_ID#]', '[#post_author_name#]', '[#client_name#]', '[#listing_title#]', '[#listing_url#]', '[#listing_link#]', '[#comment_ID#]', '[#comment_author#]', '[#comment_author_IP#]', '[#comment_author_email#]', '[#comment_date#]', '[#comment_content#]', '[#comment_approve_link#]', '[#comment_trash_link#]', '[#comment_spam_link#]' ) );
			
			$tags = apply_filters( 'geodir_email_owner_comment_submit_email_tags', $tags );

			if ( $inline ) {
				$tags = '<code>' . implode( '</code> <code>', $tags ) . '</code>';
			}
			
			return $tags;
		}
		
		public function owner_comment_approved_email_tags( $inline = true ) { 
			$global_tags = $this->global_email_tags( false );

			$tags = array_merge( $global_tags, array( '[#post_id#]', '[#post_status#]', '[#post_date#]', '[#post_author_ID#]', '[#post_author_name#]', '[#client_name#]', '[#listing_title#]', '[#listing_url#]', '[#listing_link#]', '[#comment_ID#]', '[#comment_author#]', '[#comment_author_IP#]', '[#comment_author_email#]', '[#comment_date#]', '[#comment_content#]' ) );
			
			$tags = apply_filters( 'geodir_email_owner_comment_approved_email_tags', $tags );

			if ( $inline ) {
				$tags = '<code>' . implode( '</code> <code>', $tags ) . '</code>';
			}
			
			return $tags;
		}
		
		public function author_comment_approved_email_tags( $inline = true ) { 
			$global_tags = $this->global_email_tags( false );

			$tags = array_merge( $global_tags, array( '[#post_id#]', '[#post_status#]', '[#post_date#]', '[#post_author_ID#]', '[#post_author_name#]', '[#client_name#]', '[#listing_title#]', '[#listing_url#]', '[#listing_link#]', '[#comment_ID#]', '[#comment_author#]', '[#comment_author_IP#]', '[#comment_author_email#]', '[#comment_date#]', '[#comment_content#]' ) );
			
			$tags = apply_filters( 'geodir_email_author_comment_approved_email_tags', $tags );

			if ( $inline ) {
				$tags = '<code>' . implode( '</code> <code>', $tags ) . '</code>';
			}
			
			return $tags;
		}
		
		public function admin_pending_post_email_tags( $inline = true ) { 
			$global_tags = $this->global_email_tags( false );

			$tags = array_merge( $global_tags, array( '[#post_id#]', '[#post_status#]', '[#post_date#]', '[#post_author_ID#]', '[#post_author_name#]', '[#client_name#]', '[#listing_title#]', '[#listing_url#]', '[#listing_link#]' ) );
			
			$tags = apply_filters( 'geodir_email_admin_pending_post_email_tags', $tags );

			if ( $inline ) {
				$tags = '<code>' . implode( '</code> <code>', $tags ) . '</code>';
			}
			
			return $tags;
		}
		
		public function admin_post_edit_email_tags( $inline = true ) { 
			$global_tags = $this->global_email_tags( false );

			$tags = array_merge( $global_tags, array( '[#post_id#]', '[#post_status#]', '[#post_date#]', '[#post_author_ID#]', '[#post_author_name#]', '[#client_name#]', '[#listing_title#]', '[#listing_url#]', '[#listing_link#]' ) );
			
			$tags = apply_filters( 'geodir_email_admin_post_edit_email_tags', $tags );

			if ( $inline ) {
				$tags = '<code>' . implode( '</code> <code>', $tags ) . '</code>';
			}
			
			return $tags;
		}
		
		public function admin_moderate_comment_email_tags( $inline = true ) { 
			$global_tags = $this->global_email_tags( false );

			$tags = array_merge( $global_tags, array( '[#post_id#]', '[#post_status#]', '[#post_date#]', '[#post_author_ID#]', '[#post_author_name#]', '[#client_name#]', '[#listing_title#]', '[#listing_url#]', '[#listing_link#]', '[#comment_ID#]', '[#comment_author#]', '[#comment_author_IP#]', '[#comment_author_email#]', '[#comment_date#]', '[#comment_content#]', '[#comment_approve_link#]', '[#comment_trash_link#]', '[#comment_spam_link#]', '[#comment_moderation_link#]' ) );
			
			$tags = apply_filters( 'geodir_email_admin_moderate_comment_email_tags', $tags );

			if ( $inline ) {
				$tags = '<code>' . implode( '</code> <code>', $tags ) . '</code>';
			}
			
			return $tags;
		}
	}

endif;

return new GeoDir_Settings_Emails();
