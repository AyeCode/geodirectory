<?php
/**
 * V3 Email Settings for GeoDirectory
 *
 * This file has been updated to use the 'group' field type for a card-based layout.
 *
 * @package     GeoDirectory
 * @since       3.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'id'          => 'emails',
	'name'        => __( 'Emails', 'geodirectory' ),
	'icon'        => 'fa-solid fa-envelope-open-text',
	'description' => __( 'Configure email templates, sender options, and notifications sent by GeoDirectory.', 'geodirectory' ),
	'subsections' => array(

		/**
		 * Subsection: Sender Options (Non-Grouped)
		 */
		array(
			'id'          => 'sender_options',
			'name'        => __( 'Sender Options', 'geodirectory' ),
			'description' => __( 'Configure the default sender name and email address for all outgoing GeoDirectory emails.', 'geodirectory' ),
			'fields'      => array(
				array(
					'id'      => 'email_name',
					'type'    => 'text',
					'label'   => __( 'Sender Name', 'geodirectory' ),
					'description' => __( 'The name that emails will appear to be sent from.', 'geodirectory' ),
					'placeholder' => get_bloginfo('name'),
					'searchable' => array('email', 'sender', 'from', 'name'),
				),
				array(
					'id'      => 'email_address',
					'type'    => 'email',
					'label'   => __( 'Sender Email Address', 'geodirectory' ),
					'description' => __( 'The email address that emails will be sent from.', 'geodirectory' ),
					'placeholder' => get_option('admin_email'),
					'searchable' => array('email', 'sender', 'from', 'address'),
				),
			),
		),

		/**
		 * Subsection: Admin Notifications (Grouped)
		 */
		array(
			'id'          => 'admin_notifications',
			'name'        => __( 'Admin Notifications', 'geodirectory' ),
			'description' => __( 'Manage email notifications sent to the site administrator.', 'geodirectory' ),
			'fields'      => array(
				// Group 1: Pending Listing
				array(
					'type'        => 'group',
					'label'       => __( 'Pending Listing', 'geodirectory' ),
					'description' => __( 'Sent when a new listing is submitted and requires admin review.', 'geodirectory' ),
					'fields'      => array(
						array(
							'id'      => 'email_admin_pending_post',
							'type'    => 'toggle',
							'label'   => __( 'Enable this notification', 'geodirectory' ),
							'default' => true,
						),
						array(
							'id'      => 'email_admin_pending_post_subject',
							'type'    => 'text',
							'label'   => __( 'Subject', 'geodirectory' ),
							'placeholder' => 'New listing submitted on [#site_name#]',
							'active_placeholder' => true,
							'show_if'   => '[%email_admin_pending_post%]',
						),
						array(
							'id'      => 'email_admin_pending_post_body',
							'type'    => 'textarea',
							'label'   => __( 'Body', 'geodirectory' ),
							'description' => __( 'The content of the email. @todo: Add an interactive tag selector.', 'geodirectory' ),
							'rows'    => 8,
							'placeholder' => 'A new listing, [#listing_title#], has been submitted and is waiting for approval.',
							'active_placeholder' => true,
							'custom_desc'   => $this->get_email_tags_html( 'admin_pending_post' ),
							'show_if'   => '[%email_admin_pending_post%]',
						),
					)
				),
				// Group 2: Edited Listing
				array(
					'type'        => 'group',
					'label'       => __( 'Edited Listing', 'geodirectory' ),
					'description' => __( 'Sent when a user edits an existing listing.', 'geodirectory' ),
					'fields'      => array(
						array(
							'id'      => 'email_admin_post_edit',
							'type'    => 'toggle',
							'label'   => __( 'Enable this notification', 'geodirectory' ),
							'default' => false,
						),
						array(
							'id'      => 'email_admin_post_edit_subject',
							'type'    => 'text',
							'label'   => __( 'Subject', 'geodirectory' ),
							'placeholder' => 'Listing "[#listing_title#]" has been updated',
							'active_placeholder' => true,
							'show_if'   => '[%email_admin_post_edit%]',
						),
						array(
							'id'      => 'email_admin_post_edit_body',
							'type'    => 'textarea',
							'label'   => __( 'Body', 'geodirectory' ),
							'description' => __( 'The content of the email. @todo: Add an interactive tag selector.', 'geodirectory' ),
							'rows'    => 8,
							'placeholder' => 'The listing, [#listing_title#], has been edited by [#client_name#].',
							'active_placeholder' => true,
							'custom_desc'   => $this->get_email_tags_html( 'admin_post_edit' ),
							'show_if'   => '[%email_admin_post_edit%]',
						),
					)
				),
				// Group 3: New Comment
				array(
					'type'        => 'group',
					'label'       => __( 'New Comment for Moderation', 'geodirectory' ),
					'description' => __( 'Sent when a new comment or review is submitted and needs moderation.', 'geodirectory' ),
					'fields'      => array(
						array(
							'id'      => 'email_admin_moderate_comment',
							'type'    => 'toggle',
							'label'   => __( 'Enable this notification', 'geodirectory' ),
							'default' => false,
						),
						array(
							'id'      => 'email_admin_moderate_comment_subject',
							'type'    => 'text',
							'label'   => __( 'Subject', 'geodirectory' ),
							'placeholder' => 'New comment on "[#comment_post_title#]"',
							'active_placeholder' => true,
							'show_if'   => '[%email_admin_moderate_comment%]',
						),
						array(
							'id'      => 'email_admin_moderate_comment_body',
							'type'    => 'textarea',
							'label'   => __( 'Body', 'geodirectory' ),
							'description' => __( 'The content of the email. @todo: Add an interactive tag selector.', 'geodirectory' ),
							'rows'    => 8,
							'placeholder' => 'A new comment has been posted on [#comment_post_title#].',
							'active_placeholder' => true,
							'custom_desc'   => $this->get_email_tags_html( 'admin_moderate_comment' ),
							'show_if'   => '[%email_admin_moderate_comment%]',
						),
					)
				),
			)
		),

		/**
		 * Subsection: User Notifications (Grouped)
		 */
		array(
			'id'          => 'user_notifications',
			'name'        => __( 'User Notifications', 'geodirectory' ),
			'description' => __( 'Manage email notifications sent to your users and listing owners.', 'geodirectory' ),
			'fields'      => array(
				// Group 1: User Pending Listing
				array(
					'type'        => 'group',
					'label'       => __( 'Pending Listing Confirmation', 'geodirectory' ),
					'description' => __( 'Sent to the user when their listing is submitted for review.', 'geodirectory' ),
					'fields'      => array(
						array(
							'id'      => 'email_user_pending_post',
							'type'    => 'toggle',
							'label'   => __( 'Enable this notification', 'geodirectory' ),
							'default' => true,
						),
						array(
							'id'      => 'email_user_pending_post_subject',
							'type'    => 'text',
							'label'   => __( 'Subject', 'geodirectory' ),
							'placeholder' => 'Your listing submission has been received',
							'active_placeholder' => true,
							'show_if'   => '[%email_user_pending_post%]',
						),
						array(
							'id'      => 'email_user_pending_post_body',
							'type'    => 'textarea',
							'label'   => __( 'Body', 'geodirectory' ),
							'description' => __( 'The content of the email. @todo: Add an interactive tag selector.', 'geodirectory' ),
							'rows'    => 8,
							'placeholder' => 'Hi [#client_name#], we have received your submission for "[#listing_title#]". We will review it shortly.',
							'active_placeholder' => true,
							'custom_desc'   => $this->get_email_tags_html( 'user_pending_post' ),
							'show_if'   => '[%email_user_pending_post%]',
						),
					)
				),
				// Group 2: User Published Listing
				array(
					'type'        => 'group',
					'label'       => __( 'Published Listing Confirmation', 'geodirectory' ),
					'description' => __( 'Sent to the user once their listing has been approved and published.', 'geodirectory' ),
					'fields'      => array(
						array(
							'id'      => 'email_user_publish_post',
							'type'    => 'toggle',
							'label'   => __( 'Enable this notification', 'geodirectory' ),
							'default' => true,
						),
						array(
							'id'      => 'email_user_publish_post_subject',
							'type'    => 'text',
							'label'   => __( 'Subject', 'geodirectory' ),
							'placeholder' => 'Your listing "[#listing_title#]" is now live!',
							'active_placeholder' => true,
							'show_if'   => '[%email_user_publish_post%]',
						),
						array(
							'id'      => 'email_user_publish_post_body',
							'type'    => 'textarea',
							'label'   => __( 'Body', 'geodirectory' ),
							'description' => __( 'The content of the email. @todo: Add an interactive tag selector.', 'geodirectory' ),
							'rows'    => 8,
							'placeholder' => 'Hi [#client_name#], your listing "[#listing_title#]" has been published. You can view it here: [#listing_link#]',
							'active_placeholder' => true,
							'custom_desc'   => $this->get_email_tags_html( 'user_publish_post' ),
							'show_if'   => '[%email_user_publish_post%]',
						),
					)
				),
			)
		),

		/**
		 * Subsection: BCC Options (Non-Grouped)
		 */
		array(
			'id'          => 'bcc_options',
			'name'        => __( 'BCC Options', 'geodirectory' ),
			'description' => __( 'Optionally send a Blind Carbon Copy of certain user-facing emails to the site administrator for record-keeping.', 'geodirectory' ),
			'fields'      => array(
				array(
					'id'      => 'email_bcc_user_pending_post',
					'type'    => 'toggle',
					'label'   => __( 'BCC on Pending Listing', 'geodirectory' ),
					'description' => __( 'Send a copy of the "Pending Listing" email to the admin.', 'geodirectory' ),
					'default' => false,
				),
				array(
					'id'      => 'email_bcc_user_publish_post',
					'type'    => 'toggle',
					'label'   => __( 'BCC on Published Listing', 'geodirectory' ),
					'description' => __( 'Send a copy of the "Published Listing" email to the admin.', 'geodirectory' ),
					'default' => false,
				),
				array(
					'id'      => 'email_bcc_owner_comment_submit',
					'type'    => 'toggle',
					'label'   => __( 'BCC on Owner Comment Notification', 'geodirectory' ),
					'description' => __( 'Send a copy of the "New Comment" notification email to the admin.', 'geodirectory' ),
					'default' => false,
				),
				array(
					'id'      => 'email_bcc_send_enquiry',
					'type'    => 'toggle',
					'label'   => __( 'BCC on Listing Enquiry Form', 'geodirectory' ),
					'description' => __( 'Send a copy of emails sent via the listing enquiry form to the admin.', 'geodirectory' ),
					'default' => false,
				),
			)
		),

		/**
		 * Subsection: Email Template (Non-Grouped)
		 */
		array(
			'id'          => 'email_template',
			'name'        => __( 'Email Template', 'geodirectory' ),
			'description' => __( 'Customize the design of HTML emails sent by GeoDirectory.', 'geodirectory' ),
			'fields'      => array(
				array(
					'id'      => 'email_type',
					'type'    => 'select',
					'label'   => __( 'Email Format', 'geodirectory' ),
					'description' => __( 'Choose whether to send emails as HTML or plain text.', 'geodirectory' ),
					'options' => array(
						'html'  => __( 'HTML', 'geodirectory' ),
						'plain' => __( 'Plain Text', 'geodirectory' ),
					),
					'default' => 'html',
				),
				array(
					'id'      => 'email_logo',
					'type'    => 'image',
					'label'   => __( 'Email Header Logo', 'geodirectory' ),
					'description' => __( 'Upload a logo to display at the top of HTML emails.', 'geodirectory' ),
				),
				array(
					'id'      => 'email_footer_text',
					'type'    => 'textarea',
					'label'   => __( 'Email Footer Text', 'geodirectory' ),
					'description' => __( 'The text to appear in the footer of all emails.', 'geodirectory' ),
					'placeholder' => get_bloginfo('name') . ' - Powered by GeoDirectory',
				),
				array(
					'id'      => 'email_base_color',
					'type'    => 'color',
					'label'   => __( 'Base Color', 'geodirectory' ),
					'description' => __( 'The primary color used for accents and links in HTML emails.', 'geodirectory' ),
					'default' => '#557da2',
				),
				array(
					'id'      => 'email_background_color',
					'type'    => 'color',
					'label'   => __( 'Background Color', 'geodirectory' ),
					'description' => __( 'The outer background color of the email template.', 'geodirectory' ),
					'default' => '#f5f5f5',
				),
				array(
					'id'      => 'email_body_background_color',
					'type'    => 'color',
					'label'   => __( 'Body Background Color', 'geodirectory' ),
					'description' => __( 'The background color of the main content area.', 'geodirectory' ),
					'default' => '#fdfdfd',
				),
				array(
					'id'      => 'email_text_color',
					'type'    => 'color',
					'label'   => __( 'Body Text Color', 'geodirectory' ),
					'description' => __( 'The color of the main body text.', 'geodirectory' ),
					'default' => '#505050',
				),
			),
		),
	)
);
