<?php
/**
 * Comment Email Hooks
 *
 * Manages all WordPress comment filter/action hooks for email notifications.
 * Replaces legacy functions from inc/comments-functions.php.
 *
 * @package GeoDirectory
 * @since   3.0.0
 */

declare(strict_types=1);

namespace AyeCode\GeoDirectory\Core;

use AyeCode\GeoDirectory\Core\Services\Email;
use AyeCode\GeoDirectory\Support\Hookable;

/**
 * Comment Email Hooks Class
 *
 * Registers all WordPress comment-related email hooks and delegates
 * email sending to the Email service.
 *
 * @since 3.0.0
 */
class CommentEmailHooks {
	use Hookable;

	/**
	 * Email service instance.
	 *
	 * @var Email
	 */
	private $email;

	/**
	 * Constructor.
	 *
	 * @param Email $email Email service instance.
	 */
	public function __construct( Email $email ) {
		$this->email = $email;
	}

	/**
	 * Register all comment email hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		// Admin comment moderation filters
		$this->filter( 'notify_moderator', [ $this, 'check_notify_moderator' ], 99999, 2 );
		$this->filter( 'comment_moderation_recipients', [ $this, 'comment_moderation_recipients' ], 10, 2 );
		$this->filter( 'comment_moderation_subject', [ $this, 'comment_moderation_subject' ], 10, 2 );
		$this->filter( 'comment_moderation_text', [ $this, 'comment_moderation_text' ], 10, 2 );
		$this->filter( 'comment_moderation_headers', [ $this, 'comment_moderation_headers' ], 10, 2 );

		// Disable default post author notification for GD posts
		$this->filter( 'notify_post_author', [ $this, 'check_notify_post_author' ], 99999, 2 );

		// Comment approval notification
		$this->on( 'comment_unapproved_to_approved', [ $this, 'notify_on_comment_approved' ], 10, 2 );

		// New comment notification to listing owner
		$this->on( 'comment_post', [ $this, 'new_comment_notify_postauthor' ], 99999, 1 );
	}

	/**
	 * Check if moderator should be notified.
	 *
	 * @param bool $maybe_notify Whether to notify.
	 * @param int  $comment_id   Comment ID.
	 * @return bool
	 */
	public function check_notify_moderator( bool $maybe_notify, int $comment_id ): bool {
		$comment = get_comment( $comment_id );

		if ( ! empty( $comment->comment_post_ID ) && geodir_is_gd_post_type( get_post_type( $comment->comment_post_ID ) ) ) {
			$maybe_notify = '0' === $comment->comment_approved && $this->email->is_enabled( 'admin_moderate_comment' );
		}

		return $maybe_notify;
	}

	/**
	 * Filter comment moderation recipients.
	 *
	 * @param array $emails     Email addresses.
	 * @param int   $comment_id Comment ID.
	 * @return array
	 */
	public function comment_moderation_recipients( array $emails, int $comment_id ): array {
		$comment = get_comment( $comment_id );

		if ( ! empty( $comment->comment_post_ID ) && geodir_is_gd_post_type( get_post_type( $comment->comment_post_ID ) ) && $this->email->is_enabled( 'admin_moderate_comment' ) ) {
			$emails = [ $this->email->get_admin_email() ];
		}

		return $emails;
	}

	/**
	 * Filter comment moderation subject.
	 *
	 * @param string $subject    Email subject.
	 * @param int    $comment_id Comment ID.
	 * @return string
	 */
	public function comment_moderation_subject( string $subject, int $comment_id ): string {
		$comment = get_comment( $comment_id );

		if ( ! empty( $comment->comment_post_ID ) && geodir_is_gd_post_type( get_post_type( $comment->comment_post_ID ) ) && $this->email->is_enabled( 'admin_moderate_comment' ) ) {
			$gd_post = geodir_get_post_info( $comment->comment_post_ID );

			$email_vars = [
				'email_name' => 'admin_moderate_comment',
				'comment'    => $comment,
				'post'       => $gd_post
			];

			$subject = $this->email->get_subject( 'admin_moderate_comment', $email_vars );
		}

		return $subject;
	}

	/**
	 * Filter comment moderation text (email body).
	 *
	 * @param string $message    Email message.
	 * @param int    $comment_id Comment ID.
	 * @return string
	 */
	public function comment_moderation_text( string $message, int $comment_id ): string {
		$comment = get_comment( $comment_id );

		if ( ! empty( $comment->comment_post_ID ) && geodir_is_gd_post_type( get_post_type( $comment->comment_post_ID ) ) && $this->email->is_enabled( 'admin_moderate_comment' ) ) {
			$gd_post    = geodir_get_post_info( $comment->comment_post_ID );
			$email_name = 'admin_moderate_comment';

			$email_vars = [
				'comment' => $comment,
				'post'    => $gd_post
			];

			$message_body = $this->email->get_content( $email_name, $email_vars );

			$plain_text = $this->email->get_email_type() !== 'html';
			$template   = $plain_text ? 'emails/plain/geodir-email-' . $email_name . '.php' : 'emails/geodir-email-' . $email_name . '.php';

			$message = geodir_get_template_html( $template, [
				'email_name'    => $email_name,
				'email_vars'    => $email_vars,
				'email_heading' => '',
				'sent_to_admin' => true,
				'plain_text'    => $plain_text,
				'message_body'  => $message_body,
			] );
			$message = $this->email->style_body( $message, $email_name, $email_vars );
			$message = apply_filters( 'geodir_mail_content', $message, $email_name, $email_vars );

			if ( $plain_text ) {
				$message = wp_strip_all_tags( $message );
			}
		}

		return $message;
	}

	/**
	 * Filter comment moderation headers.
	 *
	 * @param string $headers    Email headers.
	 * @param int    $comment_id Comment ID.
	 * @return string
	 */
	public function comment_moderation_headers( string $headers, int $comment_id ): string {
		$comment = get_comment( $comment_id );

		if ( ! empty( $comment->comment_post_ID ) && geodir_is_gd_post_type( get_post_type( $comment->comment_post_ID ) ) && $this->email->is_enabled( 'admin_moderate_comment' ) ) {
			$gd_post = geodir_get_post_info( $comment->comment_post_ID );

			$email_vars = [
				'email_name' => 'admin_moderate_comment',
				'comment'    => $comment,
				'post'       => $gd_post
			];

			$headers = $this->email->get_headers( 'admin_moderate_comment', $email_vars );
		}

		return $headers;
	}

	/**
	 * Check if post author should be notified.
	 *
	 * Disables default WordPress notification for GD posts
	 * as we handle it with our own system.
	 *
	 * @param bool $maybe_notify Whether to notify.
	 * @param int  $comment_id   Comment ID.
	 * @return bool
	 */
	public function check_notify_post_author( bool $maybe_notify, int $comment_id ): bool {
		$comment = get_comment( $comment_id );

		if ( ! empty( $comment->comment_post_ID ) && geodir_is_gd_post_type( get_post_type( $comment->comment_post_ID ) ) ) {
			return false;
		}

		return $maybe_notify;
	}

	/**
	 * Notify relevant parties when a comment is approved.
	 *
	 * @param \WP_Comment $comment Comment object.
	 * @return void
	 */
	public function notify_on_comment_approved( $comment ): void {
		if ( ! ( ! empty( $comment->comment_post_ID ) && geodir_is_gd_post_type( get_post_type( $comment->comment_post_ID ) ) ) ) {
			return;
		}

		$notify_comment_author = $this->should_notify_comment_author( $comment );
		$notify_listing_author = $this->should_notify_listing_author( $comment );

		if ( ! ( $notify_comment_author || $notify_listing_author ) ) {
			return;
		}

		// Notify comment author
		if ( $notify_comment_author ) {
			update_comment_meta( $comment->comment_ID, 'gd_comment_author_notified', current_time( 'timestamp', 1 ) );
			$this->email->send_author_comment_approved_email( $comment );
		}

		// Notify listing author
		if ( $notify_listing_author ) {
			update_comment_meta( $comment->comment_ID, 'gd_listing_author_notified', current_time( 'timestamp', 1 ) );
			$this->email->send_owner_comment_approved_email( $comment );
		}
	}

	/**
	 * Send notification to listing owner when new comment is posted.
	 *
	 * @param int $comment_ID Comment ID.
	 * @return bool True on success, false on failure.
	 */
	public function new_comment_notify_postauthor( int $comment_ID ): bool {
		$comment = get_comment( $comment_ID );

		$maybe_notify = get_option( 'comments_notify' );

		if ( $maybe_notify && ! empty( $comment->comment_post_ID ) && geodir_is_gd_post_type( get_post_type( $comment->comment_post_ID ) ) ) {
			$maybe_notify = $this->email->is_enabled( 'owner_comment_submit' );
		}

		// Only send notifications for approved or pending comments.
		if ( $maybe_notify && ! ( in_array( 'comment_approved', array_keys( (array) $comment ), true ) && ( $comment->comment_approved === '0' || $comment->comment_approved === 'hold' || $comment->comment_approved === '1' || $comment->comment_approved === 'approve' ) ) ) {
			$maybe_notify = false;
		}

		/**
		 * Filter whether to send the post author new comment notification emails.
		 *
		 * @since 3.0.0
		 *
		 * @param bool $maybe_notify Whether to notify the post author about the new comment.
		 * @param int  $comment_ID   The ID of the comment for the notification.
		 */
		$maybe_notify = apply_filters( 'geodir_comment_notify_post_author', $maybe_notify, $comment_ID );

		if ( ! $maybe_notify ) {
			return false;
		}

		return $this->email->send_owner_comment_submit_email( $comment );
	}

	/**
	 * Check if comment author should be notified.
	 *
	 * @param object|int $comment Comment object or ID.
	 * @return bool
	 */
	private function should_notify_comment_author( $comment ): bool {
		if ( is_object( $comment ) && isset( $comment->comment_ID ) ) {
			$comment_id = $comment->comment_ID;
		} else {
			$comment_id = $comment;
		}

		$notify      = $this->email->is_enabled( 'author_comment_approved' );
		$notify_sent = get_comment_meta( $comment_id, 'gd_comment_author_notified', true );

		if ( ! empty( $notify ) && empty( $notify_sent ) ) {
			$notify = true;
		} else {
			$notify = false;
		}

		/**
		 * Filter whether to notify comment author on approval.
		 *
		 * @since 3.0.0
		 *
		 * @param bool $notify      Whether to notify.
		 * @param int  $comment_id  Comment ID.
		 */
		return apply_filters( 'geodir_should_notify_comment_author', $notify, $comment_id );
	}

	/**
	 * Check if listing author should be notified.
	 *
	 * @param object|int $comment Comment object or ID.
	 * @return bool
	 */
	private function should_notify_listing_author( $comment ): bool {
		if ( is_object( $comment ) && isset( $comment->comment_ID ) ) {
			$comment_id = $comment->comment_ID;
		} else {
			$comment_id = $comment;
		}

		$notify      = $this->email->is_enabled( 'owner_comment_approved' );
		$notify_sent = get_comment_meta( $comment_id, 'gd_listing_author_notified', true );

		if ( ! empty( $notify ) && empty( $notify_sent ) ) {
			$notify = true;
		} else {
			$notify = false;
		}

		/**
		 * Filter whether to notify listing author on comment approval.
		 *
		 * @since 3.0.0
		 *
		 * @param bool $notify      Whether to notify.
		 * @param int  $comment_id  Comment ID.
		 */
		return apply_filters( 'geodir_should_notify_listing_author', $notify, $comment_id );
	}
}
