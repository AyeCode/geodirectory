<?php
/**
 * Email Hooks Registration
 *
 * Registers all hooks related to email functionality.
 * Separates hook registration from business logic following v3 patterns.
 *
 * @package GeoDirectory\Core
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core;

use AyeCode\GeoDirectory\Core\Services\Email;
use AyeCode\GeoDirectory\Support\Hookable;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * EmailHooks class.
 *
 * Manages all WordPress hooks related to email functionality.
 * Uses the Hookable trait for easy hook management and removal.
 *
 * @since 3.0.0
 */
final class EmailHooks {

	use Hookable;

	/**
	 * Email service instance.
	 *
	 * @var Email
	 */
	private Email $email;

	/**
	 * Constructor.
	 *
	 * @param Email $email Email service instance.
	 */
	public function __construct( Email $email ) {
		$this->email = $email;
	}

	/**
	 * Register all email-related hooks.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function register(): void {
		// Email header and footer rendering
		$this->on( 'geodir_email_header', [ $this, 'render_email_header' ], 10, 5 );
		$this->on( 'geodir_email_footer', [ $this, 'render_email_footer' ], 10, 4 );

		// Post save emails
		$this->on( 'geodir_ajax_post_saved', [ $this, 'send_email_on_post_saved' ], 10, 2 );

		// Post published email
		$this->on( 'geodir_post_published', [ $this, 'send_user_publish_post' ], 999, 2 );
	}

	/**
	 * Render email header.
	 *
	 * @since 3.0.0
	 *
	 * @param string $email_heading Email heading text.
	 * @param string $email_name    Email type identifier.
	 * @param array  $email_vars    Email template variables.
	 * @param bool   $plain_text    Whether this is plain text email.
	 * @param bool   $sent_to_admin Whether this is sent to admin.
	 * @return void
	 */
	public function render_email_header(
		string $email_heading = '',
		string $email_name = '',
		array $email_vars = [],
		bool $plain_text = false,
		bool $sent_to_admin = false
	): void {
		$this->email->render_header( $email_heading, $email_name, $email_vars, $plain_text, $sent_to_admin );
	}

	/**
	 * Render email footer.
	 *
	 * @since 3.0.0
	 *
	 * @param string $email_name    Email type identifier.
	 * @param array  $email_vars    Email template variables.
	 * @param bool   $plain_text    Whether this is plain text email.
	 * @param bool   $sent_to_admin Whether this is sent to admin.
	 * @return void
	 */
	public function render_email_footer(
		string $email_name = '',
		array $email_vars = [],
		bool $plain_text = false,
		bool $sent_to_admin = false
	): void {
		$this->email->render_footer( $email_name, $email_vars, $plain_text, $sent_to_admin );
	}

	/**
	 * Send emails when a post is saved.
	 *
	 * @since 3.0.0
	 *
	 * @global array $gd_notified_edited Tracks which posts have triggered edit emails.
	 *
	 * @param array $post_data Post data array with ID key.
	 * @param bool  $update    Whether this is an update.
	 * @return void
	 */
	public function send_email_on_post_saved( array $post_data, bool $update = false ): void {
		global $gd_notified_edited;

		if ( empty( $post_data['ID'] ) ) {
			return;
		}

		$post_ID = $post_data['ID'];
		if ( wp_is_post_revision( $post_ID ) ) {
			$post_ID = wp_get_post_parent_id( $post_ID );
		}

		$gd_post = geodir_get_post_info( $post_ID );

		if ( empty( $gd_post ) ) {
			return;
		}

		// Handle post edit email
		if ( $update ) {
			$user_id = (int) get_current_user_id();

			if (
				$user_id > 0 &&
				! empty( $gd_post->post_author ) &&
				$user_id === (int) $gd_post->post_author &&
				! current_user_can( 'manage_options' ) &&
				empty( $gd_notified_edited[ $post_ID ] ) &&
				$this->email->is_enabled( 'admin_post_edit' )
			) {
				if ( empty( $gd_notified_edited ) ) {
					$gd_notified_edited = [];
				}
				$gd_notified_edited[ $post_ID ] = true;

				$this->send_admin_post_edit_email( $gd_post );
			}
		}

		// Get post status if not set
		if ( ! isset( $post_data['post_status'] ) ) {
			$post_data['post_status'] = get_post_status( $post_ID );
		}

		// Handle pending post emails
		if ( isset( $post_data['post_status'] ) && $post_data['post_status'] === 'pending' ) {
			$this->send_admin_pending_post_email( $gd_post );
			$this->send_user_pending_post_email( $gd_post );
		}
	}

	/**
	 * Send user publish post email.
	 *
	 * @since 3.0.0
	 *
	 * @param object $post Post object.
	 * @param array  $data Additional data.
	 * @return void
	 */
	public function send_user_publish_post( $post, array $data = [] ): void {
		$this->email->send_user_publish_post_email( $post, $data );
	}

	/**
	 * Send admin post edit email.
	 *
	 * @since 3.0.0
	 *
	 * @param object $post Post object.
	 * @param array  $data Additional data.
	 * @return bool Whether email was sent.
	 */
	private function send_admin_post_edit_email( $post, array $data = [] ): bool {
		$email_name = 'admin_post_edit';

		if ( ! $this->email->is_enabled( $email_name ) ) {
			return false;
		}

		$recipient = $this->email->get_admin_email();

		if ( empty( $post ) || ! is_email( $recipient ) ) {
			return false;
		}

		$email_vars             = $data;
		$email_vars['post']     = $post;
		$email_vars['to_email'] = $recipient;

		if ( apply_filters( 'geodir_skip_email_send', false, $email_name, $email_vars ) === true ) {
			return false;
		}

		do_action( 'geodir_pre_admin_post_edit_email', $email_name, $email_vars );

		$subject      = $this->email->get_subject( $email_name, $email_vars );
		$message_body = $this->email->get_content( $email_name, $email_vars );
		$headers      = $this->email->get_headers( $email_name, $email_vars );
		$attachments  = $this->email->get_attachments( $email_name, $email_vars );

		$plain_text = $this->email->get_email_type() !== 'html';
		$template   = $plain_text ? 'emails/plain/geodir-email-' . $email_name . '.php' : 'emails/geodir-email-' . $email_name . '.php';

		$content = geodir_get_template_html( $template, [
			'email_name'    => $email_name,
			'email_vars'    => $email_vars,
			'email_heading' => '',
			'sent_to_admin' => true,
			'plain_text'    => $plain_text,
			'message_body'  => $message_body,
		] );

		$sent = $this->email->send( $recipient, $subject, $content, $headers, $attachments, $email_name, $email_vars );

		do_action( 'geodir_post_admin_post_edit_email', $email_name, $email_vars );

		return $sent;
	}

	/**
	 * Send admin pending post email.
	 *
	 * @since 3.0.0
	 *
	 * @param object $post Post object.
	 * @param array  $data Additional data.
	 * @return bool Whether email was sent.
	 */
	private function send_admin_pending_post_email( $post, array $data = [] ): bool {
		$email_name = 'admin_pending_post';

		if ( ! $this->email->is_enabled( $email_name ) ) {
			return false;
		}

		$recipient = $this->email->get_admin_email();

		if ( empty( $post ) || ! is_email( $recipient ) ) {
			return false;
		}

		$email_vars             = $data;
		$email_vars['post']     = $post;
		$email_vars['to_email'] = $recipient;

		if ( apply_filters( 'geodir_skip_email_send', false, $email_name, $email_vars ) === true ) {
			return false;
		}

		do_action( 'geodir_pre_admin_pending_post_email', $email_name, $email_vars );

		$subject      = $this->email->get_subject( $email_name, $email_vars );
		$message_body = $this->email->get_content( $email_name, $email_vars );
		$headers      = $this->email->get_headers( $email_name, $email_vars );
		$attachments  = $this->email->get_attachments( $email_name, $email_vars );

		$plain_text = $this->email->get_email_type() !== 'html';
		$template   = $plain_text ? 'emails/plain/geodir-email-' . $email_name . '.php' : 'emails/geodir-email-' . $email_name . '.php';

		$content = geodir_get_template_html( $template, [
			'email_name'    => $email_name,
			'email_vars'    => $email_vars,
			'email_heading' => '',
			'sent_to_admin' => true,
			'plain_text'    => $plain_text,
			'message_body'  => $message_body,
		] );

		$sent = $this->email->send( $recipient, $subject, $content, $headers, $attachments, $email_name, $email_vars );

		do_action( 'geodir_post_admin_pending_post_email', $email_name, $email_vars );

		return $sent;
	}

	/**
	 * Send user pending post email.
	 *
	 * @since 3.0.0
	 *
	 * @param object $post Post object.
	 * @param array  $data Additional data.
	 * @return bool Whether email was sent.
	 */
	private function send_user_pending_post_email( $post, array $data = [] ): bool {
		$email_name = 'user_pending_post';

		if ( ! $this->email->is_enabled( $email_name ) ) {
			return false;
		}

		$author_data = get_userdata( $post->post_author );
		if ( empty( $author_data ) ) {
			return false;
		}

		$recipient = ! empty( $author_data->user_email ) ? $author_data->user_email : '';

		if ( empty( $post ) || ! is_email( $recipient ) ) {
			return false;
		}

		$email_vars             = $data;
		$email_vars['post']     = $post;
		$email_vars['to_name']  = geodir_get_client_name( $post->post_author );
		$email_vars['to_email'] = $recipient;

		if ( apply_filters( 'geodir_skip_email_send', false, $email_name, $email_vars ) === true ) {
			return false;
		}

		do_action( 'geodir_pre_user_pending_post_email', $email_name, $email_vars );

		$subject      = $this->email->get_subject( $email_name, $email_vars );
		$message_body = $this->email->get_content( $email_name, $email_vars );
		$headers      = $this->email->get_headers( $email_name, $email_vars );
		$attachments  = $this->email->get_attachments( $email_name, $email_vars );

		$plain_text = $this->email->get_email_type() !== 'html';
		$template   = $plain_text ? 'emails/plain/geodir-email-' . $email_name . '.php' : 'emails/geodir-email-' . $email_name . '.php';

		$content = geodir_get_template_html( $template, [
			'email_name'    => $email_name,
			'email_vars'    => $email_vars,
			'email_heading' => '',
			'sent_to_admin' => false,
			'plain_text'    => $plain_text,
			'message_body'  => $message_body,
		] );

		$sent = $this->email->send( $recipient, $subject, $content, $headers, $attachments, $email_name, $email_vars );

		if ( $this->email->is_admin_bcc_active( $email_name ) ) {
			$admin_recipient = $this->email->get_admin_email();
			$admin_subject   = $subject . ' - ADMIN BCC COPY';
			$this->email->send( $admin_recipient, $admin_subject, $content, $headers, $attachments, $email_name, $email_vars );
		}

		do_action( 'geodir_post_user_pending_post_email', $email_name, $email_vars );

		return $sent;
	}
}
