<?php
/**
 * Email Defaults Service
 *
 * Provides default values for email templates including subjects, bodies,
 * sender name, and sender email address.
 *
 * @package GeoDirectory\Core\Services
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core\Services;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email defaults service class.
 *
 * Centralized location for all email-related default values. This replaces
 * the legacy GeoDir_Defaults class for email functionality in v3.
 *
 * @since 3.0.0
 */
final class EmailDefaults {

	/**
	 * Cached defaults array.
	 *
	 * @var array|null
	 */
	private ?array $defaults = null;

	/**
	 * Get email subject default for a specific email type.
	 *
	 * @since 3.0.0
	 *
	 * @param string $email_name Email type identifier (e.g., 'user_publish_post').
	 * @return string Default subject or empty string if not found.
	 */
	public function get_subject( string $email_name ): string {
		$defaults = $this->get_defaults();
		$key      = 'subject_' . $email_name;

		return isset( $defaults[ $key ] ) ? (string) $defaults[ $key ] : '';
	}

	/**
	 * Get email body default for a specific email type.
	 *
	 * @since 3.0.0
	 *
	 * @param string $email_name Email type identifier (e.g., 'user_publish_post').
	 * @return string Default body or empty string if not found.
	 */
	public function get_body( string $email_name ): string {
		$defaults = $this->get_defaults();
		$key      = 'body_' . $email_name;

		return isset( $defaults[ $key ] ) ? (string) $defaults[ $key ] : '';
	}

	/**
	 * Get default email sender name.
	 *
	 * @since 3.0.0
	 *
	 * @return string Default sender name (site name).
	 */
	public function get_from_name(): string {
		return get_bloginfo( 'name' );
	}

	/**
	 * Get default email sender address.
	 *
	 * @since 3.0.0
	 *
	 * @return string Default sender email (admin email).
	 */
	public function get_from_email(): string {
		return get_bloginfo( 'admin_email' );
	}

	/**
	 * Get all email defaults.
	 *
	 * Returns an associative array of all default email values.
	 * Results are cached in memory after first call for performance.
	 *
	 * @since 3.0.0
	 *
	 * @return array All email defaults.
	 */
	private function get_defaults(): array {
		if ( $this->defaults !== null ) {
			return $this->defaults;
		}

		$this->defaults = [
			// User - Pending Post
			'subject_user_pending_post' => __( '[[#site_name#]] Your listing has been submitted for approval', 'geodirectory' ),
			'body_user_pending_post'    => __( "Dear [#client_name#],

You submitted the below listing information. This email is just for your information.

[#listing_link#]

Thank you for your contribution.", 'geodirectory' ),

			// User - Publish Post
			'subject_user_publish_post' => __( '[[#site_name#]] Listing Published Successfully', 'geodirectory' ),
			'body_user_publish_post'    => __( "Dear [#client_name#],

Your listing [#listing_link#] has been published. This email is just for your information.

[#listing_link#]

Thank you for your contribution.", 'geodirectory' ),

			// Owner - Comment Submit
			'subject_owner_comment_submit' => __( '[[#site_name#]] A new comment has been submitted on your listing [#listing_title#]', 'geodirectory' ),
			'body_owner_comment_submit'    => __( "Dear [#client_name#],

A new comment has been submitted on your listing [#listing_link#].

Author: [#comment_author#] ( IP: [#comment_author_IP#] )
Email: [#comment_author_email#]
Listing: [#listing_url#]
Date: [#comment_date#]
Comment: [#comment_content#]

Approve it: [#comment_approve_link#]
Trash it: [#comment_trash_link#]
Spam it: [#comment_spam_link#]

Thank You.", 'geodirectory' ),

			// Owner - Comment Approved
			'subject_owner_comment_approved' => __( '[[#site_name#]] A comment on your listing [#listing_title#] has been approved', 'geodirectory' ),
			'body_owner_comment_approved'    => __( "Dear [#client_name#],

A new comment has been submitted on your listing [#listing_link#].

Author: [#comment_author#] ( IP: [#comment_author_IP#] )
Email: [#comment_author_email#]
Listing: [#listing_url#]
Date: [#comment_date#]
Comment: [#comment_content#]

Approve it: [#comment_approve_link#]
Trash it: [#comment_trash_link#]
Spam it: [#comment_spam_link#]

Thank You.", 'geodirectory' ),

			// Author - Comment Approved
			'subject_author_comment_approved' => __( '[[#site_name#]] Your comment on listing [#listing_title#] has been approved', 'geodirectory' ),
			'body_author_comment_approved'    => __( "Dear [#comment_author#],

Your comment on listing [#listing_link#] has been approved.

Comment: [#comment_content#]

Thank You.", 'geodirectory' ),

			// Admin - Pending Post
			'subject_admin_pending_post' => __( '[[#site_name#]] A new listing has been submitted for review', 'geodirectory' ),
			'body_admin_pending_post'    => __( "Dear Admin,

A new listing has been submitted [#listing_link#]. This email is just for your information.

Thank you,
[#site_name_url#]", 'geodirectory' ),

			// Admin - Post Edit
			'subject_admin_post_edit' => __( '[[#site_name#]] Listing edited by Author', 'geodirectory' ),
			'body_admin_post_edit'    => __( "Dear Admin,

A listing [#listing_link#] has been edited by its author [#post_author_name#].

Listing Details:
Listing ID: [#post_id#]
Listing URL: [#listing_link#]
Date: [#current_date#]

This email is just for your information.", 'geodirectory' ),

			// Admin - Moderate Comment
			'subject_admin_moderate_comment' => __( '[[#site_name#]] A new comment is waiting for your approval', 'geodirectory' ),
			'body_admin_moderate_comment'    => __( "Dear Admin,

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

Thank You.", 'geodirectory' ),

			// Admin - Report Post
			'subject_admin_report_post' => __( '[[#site_name#]] Someone has reported a post!', 'geodirectory' ),
			'body_admin_report_post'    => __( "Dear Admin,

Someone has reported a post [#listing_link#].

Details:
Post: [#listing_title#] (Post ID: [#post_id#])
Post Url: [#listing_url#]
Reporter Name: [#report_post_user_name#] (User ID: [#report_post_user_id#])
Reporter Email: [#report_post_user_email#]
Reporter IP: [#report_post_user_ip#]
Date: [#report_post_date#]
Reason: [#report_post_reason#]
Message: [#report_post_message#]

---
Please visit the report post section for more details: [#report_post_section_link#]

Thank You.", 'geodirectory' ),
		];

		/**
		 * Filters the email defaults array.
		 *
		 * @since 3.0.0
		 *
		 * @param array $defaults Email defaults array.
		 */
		$this->defaults = apply_filters( 'geodir_email_defaults', $this->defaults );

		return $this->defaults;
	}
}
