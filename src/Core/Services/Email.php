<?php
/**
 * Email Service
 *
 * Handles transactional emails, templates, and email delivery for GeoDirectory.
 * This service manages email sending, template rendering, variable replacement,
 * and email configuration.
 *
 * @package GeoDirectory\Core\Services
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core\Services;

use AyeCode\GeoDirectory\Core\Data\EmailMessage;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email service class.
 *
 * Manages all email-related functionality including sending, template rendering,
 * and variable replacement. Uses dependency injection for better testability.
 *
 * @since 3.0.0
 */
final class Email {

	/**
	 * Templates service instance.
	 *
	 * @var Templates
	 */
	private Templates $templates;

	/**
	 * Settings service instance.
	 *
	 * @var Settings
	 */
	private Settings $settings;

	/**
	 * Debug service instance.
	 *
	 * @var Debug
	 */
	private Debug $debug;

	/**
	 * Email defaults service instance.
	 *
	 * @var EmailDefaults
	 */
	private EmailDefaults $defaults;

	/**
	 * Constructor.
	 *
	 * @param Templates     $templates Templates service.
	 * @param Settings      $settings  Settings service.
	 * @param Debug         $debug     Debug service.
	 * @param EmailDefaults $defaults  Email defaults service.
	 */
	public function __construct( Templates $templates, Settings $settings, Debug $debug, EmailDefaults $defaults ) {
		$this->templates = $templates;
		$this->settings  = $settings;
		$this->debug     = $debug;
		$this->defaults  = $defaults;
	}

	/**
	 * Get the email logo.
	 *
	 * @since 3.0.0
	 *
	 * @param string $size Image size. Default 'full'.
	 * @return string Logo HTML markup or empty string.
	 */
	public function get_logo( string $size = 'full' ): string {
		$attachment_id = $this->settings->get( 'email_logo' );

		$logo = '';
		if ( ! empty( $attachment_id ) ) {
			$logo = wp_get_attachment_image( $attachment_id, $size );
		}

		/**
		 * Filters the email logo HTML.
		 *
		 * @since 2.0.0
		 *
		 * @param string $logo          Logo HTML markup.
		 * @param int    $attachment_id Logo attachment ID.
		 * @param string $size          Image size.
		 */
		return apply_filters( 'geodir_get_email_logo', $logo, $attachment_id, $size );
	}

	/**
	 * Get the email header text/logo.
	 *
	 * @since 3.0.0
	 *
	 * @return string Header text or logo HTML.
	 */
	public function get_header_text(): string {
		$header_text = $this->get_logo();

		if ( empty( $header_text ) ) {
			$header_text = geodir_get_blogname();
		}

		/**
		 * Filters the email header text.
		 *
		 * @since 2.0.0
		 *
		 * @param string $header_text Header text or logo HTML.
		 */
		return apply_filters( 'geodir_email_header_text', $header_text );
	}

	/**
	 * Get the email footer text.
	 *
	 * @since 3.0.0
	 *
	 * @return string Footer text.
	 */
	public function get_footer_text(): string {
		$footer_text = $this->settings->get( 'email_footer_text' );

		if ( empty( $footer_text ) ) {
			$footer_text = wp_sprintf( __( '%s - Powered by GeoDirectory', 'geodirectory' ), get_bloginfo( 'name', 'display' ) );
		}

		/**
		 * Filters the email footer text.
		 *
		 * @since 2.0.0
		 *
		 * @param string $footer_text Footer text.
		 */
		return apply_filters( 'geodir_email_footer_text', $footer_text );
	}

	/**
	 * Render the email header.
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
	public function render_header(
		string $email_heading = '',
		string $email_name = '',
		array $email_vars = [],
		bool $plain_text = false,
		bool $sent_to_admin = false
	): void {
		if ( $plain_text ) {
			return;
		}

		$header_text = $this->get_header_text();
		$header_text = $header_text ? wpautop( wp_kses_post( wptexturize( $header_text ) ) ) : '';

		geodir_get_template( 'emails/header.php', [
			'email_heading' => $email_heading,
			'email_name'    => $email_name,
			'email_vars'    => $email_vars,
			'plain_text'    => $plain_text,
			'header_text'   => $header_text,
			'sent_to_admin' => $sent_to_admin,
		] );
	}

	/**
	 * Render the email footer.
	 *
	 * @since 3.0.0
	 *
	 * @param string $email_name    Email type identifier.
	 * @param array  $email_vars    Email template variables.
	 * @param bool   $plain_text    Whether this is plain text email.
	 * @param bool   $sent_to_admin Whether this is sent to admin.
	 * @return void
	 */
	public function render_footer(
		string $email_name = '',
		array $email_vars = [],
		bool $plain_text = false,
		bool $sent_to_admin = false
	): void {
		if ( $plain_text ) {
			return;
		}

		$footer_text = $this->get_footer_text();
		$footer_text = $footer_text ? wpautop( wp_kses_post( wptexturize( $footer_text ) ) ) : '';

		geodir_get_template( 'emails/footer.php', [
			'email_name'    => $email_name,
			'email_vars'    => $email_vars,
			'plain_text'    => $plain_text,
			'footer_text'   => $footer_text,
			'sent_to_admin' => $sent_to_admin,
		] );
	}

	/**
	 * Wrap email message with header and footer.
	 *
	 * @since 3.0.0
	 *
	 * @param string $message       Email message body.
	 * @param string $email_name    Email type identifier.
	 * @param array  $email_vars    Email template variables.
	 * @param string $email_heading Email heading text.
	 * @param bool   $plain_text    Whether this is plain text email.
	 * @param bool   $sent_to_admin Whether this is sent to admin.
	 * @return string Wrapped email message.
	 */
	public function wrap_message(
		string $message,
		string $email_name = '',
		array $email_vars = [],
		string $email_heading = '',
		bool $plain_text = false,
		bool $sent_to_admin = false
	): string {
		ob_start();

		if ( $plain_text ) {
			echo wp_strip_all_tags( $message );
		} else {
			/**
			 * Fires before email header rendering.
			 *
			 * @since 2.0.0
			 *
			 * @param string $email_heading Email heading.
			 * @param string $email_name    Email type.
			 * @param array  $email_vars    Email variables.
			 * @param bool   $plain_text    Is plain text.
			 * @param bool   $sent_to_admin Sent to admin.
			 */
			do_action( 'geodir_email_header', $email_heading, $email_name, $email_vars, $plain_text, $sent_to_admin );

			echo wpautop( wptexturize( $message ) );

			/**
			 * Fires before email footer rendering.
			 *
			 * @since 2.0.0
			 *
			 * @param string $email_name    Email type.
			 * @param array  $email_vars    Email variables.
			 * @param bool   $plain_text    Is plain text.
			 * @param bool   $sent_to_admin Sent to admin.
			 */
			do_action( 'geodir_email_footer', $email_name, $email_vars, $plain_text, $sent_to_admin );
		}

		return ob_get_clean();
	}

	/**
	 * Check if an email type is enabled.
	 *
	 * @since 3.0.0
	 *
	 * @param string $email_name Email type identifier.
	 * @param mixed  $default    Default value if setting not found.
	 * @return bool Whether email is enabled.
	 */
	public function is_enabled( string $email_name, $default = '' ): bool {
		$active = $this->settings->get( 'email_' . $email_name, $default );
		$active = $active === 'yes' || $active === '1' || $active === 1 || $active === true;

		/**
		 * Filters whether an email type is enabled.
		 *
		 * @since 2.0.0
		 *
		 * @param bool   $active     Whether email is enabled.
		 * @param string $email_name Email type identifier.
		 */
		return apply_filters( 'geodir_email_is_enabled', $active, $email_name );
	}

	/**
	 * Get email subject for a specific email type.
	 *
	 * @since 3.0.0
	 *
	 * @param string $email_name Email type identifier.
	 * @param array  $email_vars Email template variables.
	 * @return string Email subject with variables replaced.
	 */
	public function get_subject( string $email_name = '', array $email_vars = [] ): string {
		$subject = $this->settings->get( 'email_' . $email_name . '_subject' );

		// Get default if empty
		if ( ! $subject ) {
			$subject = $this->defaults->get_subject( $email_name );
		}

		if ( $subject ) {
			$subject = $this->replace_variables( __( $subject, 'geodirectory' ), $email_name, $email_vars );
		}

		/**
		 * Filters the email subject.
		 *
		 * @since 2.0.0
		 *
		 * @param string $subject    Email subject.
		 * @param string $email_name Email type identifier.
		 * @param array  $email_vars Email variables.
		 */
		return apply_filters( 'geodir_email_subject', $subject, $email_name, $email_vars );
	}

	/**
	 * Get email content body for a specific email type.
	 *
	 * @since 3.0.0
	 *
	 * @param string $email_name Email type identifier.
	 * @param array  $email_vars Email template variables.
	 * @return string Email body with variables replaced.
	 */
	public function get_content( string $email_name = '', array $email_vars = [] ): string {
		$content = $this->settings->get( 'email_' . $email_name . '_body' );

		// Get default if empty
		if ( ! $content ) {
			$content = $this->defaults->get_body( $email_name );
		}

		if ( $content ) {
			$content = $this->replace_variables( __( $content, 'geodirectory' ), $email_name, $email_vars );
		}

		/**
		 * Filters the email content body.
		 *
		 * @since 2.0.0
		 *
		 * @param string $content    Email body.
		 * @param string $email_name Email type identifier.
		 * @param array  $email_vars Email variables.
		 */
		return apply_filters( 'geodir_email_content', $content, $email_name, $email_vars );
	}

	/**
	 * Replace variables in email content.
	 *
	 * @since 3.0.0
	 *
	 * @param string $content    Content with variables.
	 * @param string $email_name Email type identifier.
	 * @param array  $email_vars Email template variables.
	 * @return string Content with variables replaced.
	 */
	public function replace_variables( string $content, string $email_name = '', array $email_vars = [] ): string {
		$site_url        = home_url();
		$blogname        = geodir_get_blogname();
		$email_from_name = $this->get_from_name();
		$login_url       = geodir_login_url( false );
		$timestamp       = current_time( 'timestamp' );
		$date            = date_i18n( get_option( 'date_format' ), $timestamp );
		$time            = date_i18n( get_option( 'time_format' ), $timestamp );
		$date_time       = $date . ' ' . $time;

		$replace_array = [
			'[#blogname#]'      => $blogname,
			'[#site_url#]'      => $site_url,
			'[#site_name_url#]' => '<a href="' . esc_url( $site_url ) . '">' . $site_url . '</a>',
			'[#site_link#]'     => '<a href="' . esc_url( $site_url ) . '">' . $blogname . '</a>',
			'[#site_name#]'     => $email_from_name,
			'[#login_url#]'     => $login_url,
			'[#login_link#]'    => '<a href="' . esc_url( $login_url ) . '">' . __( 'Login', 'geodirectory' ) . '</a>',
			'[#current_date#]'  => date_i18n( 'Y-m-d H:i:s', $timestamp ),
			'[#date#]'          => $date,
			'[#time#]'          => $time,
			'[#date_time#]'     => $date_time,
			'[#from_name#]'     => $this->get_from_name(),
			'[#from_email#]'    => $this->get_from(),
		];

		// Post-related variables
		$gd_post = ! empty( $email_vars['post'] ) ? $email_vars['post'] : null;
		if ( empty( $gd_post ) && ! empty( $email_vars['post_id'] ) ) {
			$gd_post = geodir_get_post_info( $email_vars['post_id'] );
		}

		if ( ! empty( $gd_post ) ) {
			$post_id          = $gd_post->ID;
			$post_author_name = geodir_get_client_name( $gd_post->post_author );

			$replace_array['[#post_id#]']          = (string) $post_id;
			$replace_array['[#post_status#]']      = $gd_post->post_status;
			$replace_array['[#post_date#]']        = $gd_post->post_date;
			$replace_array['[#posted_date#]']      = $gd_post->post_date;
			$replace_array['[#post_author_ID#]']   = (string) $gd_post->post_author;
			$replace_array['[#post_author_name#]'] = $post_author_name;
			$replace_array['[#client_name#]']      = $post_author_name;
			$replace_array['[#listing_title#]']    = html_entity_decode( get_the_title( $post_id ), ENT_COMPAT, 'UTF-8' );
			$replace_array['[#listing_url#]']      = get_permalink( $post_id );
			$replace_array['[#listing_link#]']     = '<a href="' . esc_url( $replace_array['[#listing_url#]'] ) . '">' . $replace_array['[#listing_title#]'] . '</a>';
		}

		// Comment-related variables
		$comment = ! empty( $email_vars['comment'] ) ? $email_vars['comment'] : null;
		if ( empty( $comment ) && ! empty( $email_vars['comment_ID'] ) ) {
			$comment = get_comment( $email_vars['comment_ID'] );
		}

		if ( ! empty( $comment ) ) {
			$comment_ID = (int) $comment->comment_ID;

			$replace_array['[#comment_ID#]']              = (string) $comment_ID;
			$replace_array['[#comment_author#]']          = $comment->comment_author;
			$replace_array['[#comment_author_IP#]']       = $comment->comment_author_IP;
			$replace_array['[#comment_author_email#]']    = $comment->comment_author_email;
			$replace_array['[#comment_date#]']            = $comment->comment_date;
			$replace_array['[#comment_content#]']         = wp_specialchars_decode( $comment->comment_content );
			$replace_array['[#comment_url#]']             = get_comment_link( $comment );
			$replace_array['[#comment_post_ID#]']         = (string) (int) $comment->comment_post_ID;
			$replace_array['[#comment_post_title#]']      = html_entity_decode( get_the_title( (int) $comment->comment_post_ID ), ENT_COMPAT, 'UTF-8' );
			$replace_array['[#comment_post_url#]']        = get_permalink( (int) $comment->comment_post_ID );
			$replace_array['[#comment_post_link#]']       = '<a href="' . esc_url( $replace_array['[#comment_url#]'] ) . '">' . $replace_array['[#comment_post_title#]'] . '</a>';
			$replace_array['[#comment_approve_link#]']    = admin_url( "comment.php?action=approve&c={$comment_ID}#wpbody-content" );
			$replace_array['[#comment_trash_link#]']      = admin_url( "comment.php?action=trash&c={$comment_ID}#wpbody-content" );
			$replace_array['[#comment_spam_link#]']       = admin_url( "comment.php?action=spam&c={$comment_ID}#wpbody-content" );
			$replace_array['[#comment_moderation_link#]'] = admin_url( "edit-comments.php?comment_status=moderated#wpbody-content" );

			// Review rating data
			if ( ! geodir_cpt_has_rating_disabled( get_post_type( (int) $comment->comment_post_ID ) ) && class_exists( 'GeoDir_Comments' ) ) {
				$rating = \GeoDir_Comments::get_review( $comment_ID );
				if ( $rating ) {
					$rating_titles = \GeoDir_Comments::rating_texts();
					$rating_star   = absint( $rating->rating );

					$replace_array['[#review_rating_star#]']  = (string) $rating_star;
					$replace_array['[#review_rating_title#]'] = isset( $rating_titles[ $rating_star ] ) ? html_entity_decode( $rating_titles[ $rating_star ], ENT_COMPAT, 'UTF-8' ) : '';
					$replace_array['[#review_city#]']         = ! empty( $rating->city ) ? html_entity_decode( $rating->city, ENT_COMPAT, 'UTF-8' ) : '';
					$replace_array['[#review_region#]']       = ! empty( $rating->region ) ? html_entity_decode( $rating->region, ENT_COMPAT, 'UTF-8' ) : '';
					$replace_array['[#review_country#]']      = ! empty( $rating->country ) ? html_entity_decode( $rating->country, ENT_COMPAT, 'UTF-8' ) : '';
					$replace_array['[#review_latitude#]']     = ! empty( $rating->latitude ) ? (string) $rating->latitude : '';
					$replace_array['[#review_longitude#]']    = ! empty( $rating->longitude ) ? (string) $rating->longitude : '';
				}
			}

			// Set empty defaults for review fields if not set
			if ( ! isset( $replace_array['[#review_rating_star#]'] ) ) {
				$replace_array['[#review_rating_star#]']  = '';
				$replace_array['[#review_rating_title#]'] = '';
				$replace_array['[#review_city#]']         = '';
				$replace_array['[#review_region#]']       = '';
				$replace_array['[#review_country#]']      = '';
				$replace_array['[#review_latitude#]']     = '';
				$replace_array['[#review_longitude#]']    = '';
			}
		}

		// Add scalar values from email_vars directly
		foreach ( $email_vars as $key => $value ) {
			if ( is_scalar( $value ) ) {
				$replace_array[ '[#' . $key . '#]' ] = (string) $value;
			}
		}

		/**
		 * Filters the email variable replacement array.
		 *
		 * @since 2.0.0
		 *
		 * @param array  $replace_array Variable replacement array.
		 * @param string $content       Content being processed.
		 * @param string $email_name    Email type identifier.
		 * @param array  $email_vars    Email variables.
		 */
		$replace_array = apply_filters( 'geodir_email_wild_cards', $replace_array, $content, $email_name, $email_vars );

		// Perform replacement
		foreach ( $replace_array as $key => $value ) {
			$content = str_replace( $key, $value, $content );
		}

		/**
		 * Filters the content after variable replacement.
		 *
		 * @since 2.0.0
		 *
		 * @param string $content    Content with variables replaced.
		 * @param string $email_name Email type identifier.
		 * @param array  $email_vars Email variables.
		 */
		return apply_filters( 'geodir_email_content_replace_vars', $content, $email_name, $email_vars );
	}

	/**
	 * Get the from name for outgoing emails.
	 *
	 * @since 3.0.0
	 *
	 * @return string From name.
	 */
	public function get_from_name(): string {
		$from_name = $this->settings->get( 'email_name' );

		if ( ! $from_name ) {
			$from_name = $this->defaults->get_from_name();
		}

		/**
		 * Filters the email from name.
		 *
		 * @since 2.0.0
		 *
		 * @param string $from_name From name.
		 */
		return apply_filters( 'geodir_get_mail_from_name', stripslashes( (string) $from_name ) );
	}

	/**
	 * Get the from address for outgoing emails.
	 *
	 * @since 3.0.0
	 *
	 * @return string From email address.
	 */
	public function get_from(): string {
		$from = $this->settings->get( 'email_address' );

		if ( ! $from ) {
			$from = $this->defaults->get_from_email();
		}

		/**
		 * Filters the email from address.
		 *
		 * @since 2.0.0
		 *
		 * @param string $from From email address.
		 */
		return apply_filters( 'geodir_get_mail_from', (string) $from );
	}

	/**
	 * Get the site admin email address.
	 *
	 * @since 3.0.0
	 *
	 * @return string Admin email address.
	 */
	public function get_admin_email(): string {
		$admin_email = get_option( 'admin_email' );

		/**
		 * Filters the admin email address.
		 *
		 * @since 2.0.0
		 *
		 * @param string $admin_email Admin email address.
		 */
		return apply_filters( 'geodir_admin_email', (string) $admin_email );
	}

	/**
	 * Get email headers.
	 *
	 * @since 3.0.0
	 *
	 * @param string $email_name  Email type identifier.
	 * @param array  $email_vars  Email template variables.
	 * @param string $from_email  Optional. From email override.
	 * @param string $from_name   Optional. From name override.
	 * @return string Email headers.
	 */
	public function get_headers(
		string $email_name,
		array $email_vars = [],
		string $from_email = '',
		string $from_name = ''
	): string {
		$from_email = ! empty( $from_email ) ? $from_email : $this->get_from();
		$from_name  = ! empty( $from_name ) ? $from_name : $this->get_from_name();
		$reply_to   = ! empty( $email_vars['reply_to'] ) ? $email_vars['reply_to'] : $from_email;

		$headers  = 'From: ' . stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) . " <$from_email>\r\n";
		$headers .= 'Reply-To: ' . $reply_to . "\r\n";
		$headers .= 'Content-Type: ' . $this->get_content_type() . '; charset="' . get_option( 'blog_charset' ) . "\"\r\n";

		/**
		 * Filters the email headers.
		 *
		 * @since 2.0.0
		 *
		 * @param string $headers    Email headers.
		 * @param string $email_name Email type identifier.
		 * @param array  $email_vars Email variables.
		 * @param string $from_email From email address.
		 * @param string $from_name  From name.
		 */
		return apply_filters( 'geodir_email_headers', $headers, $email_name, $email_vars, $from_email, $from_name );
	}

	/**
	 * Get email content type.
	 *
	 * @since 3.0.0
	 *
	 * @param string $content_type Optional. Default content type.
	 * @param string $email_type   Optional. Email type override.
	 * @return string Content type.
	 */
	public function get_content_type( string $content_type = 'text/html', string $email_type = '' ): string {
		if ( empty( $email_type ) ) {
			$email_type = $this->get_email_type();
		}

		switch ( $email_type ) {
			case 'plain':
				$content_type = 'text/plain';
				break;
			case 'multipart':
				$content_type = 'multipart/alternative';
				break;
		}

		return $content_type;
	}

	/**
	 * Get the email type from settings.
	 *
	 * @since 3.0.0
	 *
	 * @return string Email type (html, plain, multipart).
	 */
	public function get_email_type(): string {
		$email_type = $this->settings->get( 'email_type' );

		if ( empty( $email_type ) ) {
			$email_type = 'html';
		}

		/**
		 * Filters the email type.
		 *
		 * @since 2.0.0
		 *
		 * @param string $email_type Email type.
		 */
		return apply_filters( 'geodir_get_email_type', (string) $email_type );
	}

	/**
	 * Get email attachments for a specific email type.
	 *
	 * @since 3.0.0
	 *
	 * @param string $email_name Email type identifier.
	 * @param array  $email_vars Email template variables.
	 * @return array Attachments array.
	 */
	public function get_attachments( string $email_name = '', array $email_vars = [] ): array {
		$attachments = [];

		/**
		 * Filters the email attachments.
		 *
		 * @since 2.0.0
		 *
		 * @param array  $attachments Attachments array.
		 * @param string $email_name  Email type identifier.
		 * @param array  $email_vars  Email variables.
		 */
		return apply_filters( 'geodir_email_attachments', $attachments, $email_name, $email_vars );
	}

	/**
	 * Send an email.
	 *
	 * @since 3.0.0
	 *
	 * @param string|array $to          Recipient email address(es).
	 * @param string       $subject     Email subject.
	 * @param string       $message     Email message body.
	 * @param string       $headers     Email headers.
	 * @param array        $attachments Email attachments.
	 * @param string       $email_name  Email type identifier.
	 * @param array        $email_vars  Email template variables.
	 * @return bool Whether email was sent successfully.
	 */
	public function send(
		$to,
		string $subject,
		string $message,
		string $headers,
		array $attachments = [],
		string $email_name = '',
		array $email_vars = []
	): bool {
		add_filter( 'wp_mail_from', [ $this, 'get_from' ] );
		add_filter( 'wp_mail_from_name', [ $this, 'get_from_name' ] );
		add_filter( 'wp_mail_content_type', [ $this, 'get_content_type' ] );

		$message = $this->style_body( $message, $email_name, $email_vars );

		/**
		 * Filters the email content before sending.
		 *
		 * @since 2.0.0
		 *
		 * @param string       $message    Email message body.
		 * @param string       $email_name Email type identifier.
		 * @param array        $email_vars Email variables.
		 * @param string|array $to         Recipient address(es).
		 * @param string       $subject    Email subject.
		 */
		$message = apply_filters( 'geodir_mail_content', $message, $email_name, $email_vars, $to, $subject );

		$sent = $message ? wp_mail( $to, $subject, $message, $headers, $attachments ) : false;

		if ( ! $sent ) {
			$log_message = wp_sprintf(
				__( "\nTime: %s\nTo: %s\nSubject: %s\n", 'geodirectory' ),
				date_i18n( 'F j Y H:i:s', current_time( 'timestamp' ) ),
				( is_array( $to ) ? implode( ', ', $to ) : $to ),
				$subject
			);
			geodir_error_log( $log_message, __( 'Email from GeoDirectory plugin failed to send', 'geodirectory' ), __FILE__, __LINE__ );
		}

		remove_filter( 'wp_mail_from', [ $this, 'get_from' ] );
		remove_filter( 'wp_mail_from_name', [ $this, 'get_from_name' ] );
		remove_filter( 'wp_mail_content_type', [ $this, 'get_content_type' ] );

		return $sent;
	}

	/**
	 * Style email body with inline CSS.
	 *
	 * @since 3.0.0
	 *
	 * @param string $content    Email content.
	 * @param string $email_name Email type identifier.
	 * @param array  $email_vars Email template variables.
	 * @return string Styled email content.
	 */
	public function style_body( string $content, string $email_name = '', array $email_vars = [] ): string {
		// Only inline CSS for HTML emails
		if ( ! in_array( $this->get_email_type(), [ 'html', 'multipart' ], true ) || ! class_exists( 'DOMDocument' ) ) {
			return $content;
		}

		// Include CSS inliner
		if ( ! class_exists( 'Emogrifier' ) ) {
			include_once GEODIRECTORY_PLUGIN_DIR . 'includes/libraries/class-emogrifier.php';
		}

		ob_start();
		geodir_get_template( 'emails/styles.php', [
			'email_name' => $email_name,
			'email_vars' => $email_vars,
		] );

		/**
		 * Filters the email styles.
		 *
		 * @since 2.0.0
		 *
		 * @param string $css Email CSS styles.
		 */
		$css = apply_filters( 'geodir_email_styles', ob_get_clean() );

		// Apply CSS styles inline
		try {
			if ( class_exists( 'Emogrifier' ) ) {
				$emogrifier = new \Emogrifier( $content, $css );
				$content    = $emogrifier->emogrify();
			}
		} catch ( \Exception $e ) {
			geodir_error_log( $e->getMessage(), 'emogrifier' );
		}

		return $content;
	}

	/**
	 * Check if admin BCC is active for an email type.
	 *
	 * @since 3.0.0
	 *
	 * @param string $email_name Email type identifier.
	 * @return bool Whether admin BCC is active.
	 */
	public function is_admin_bcc_active( string $email_name = '' ): bool {
		$active = $this->settings->get( 'email_bcc_' . $email_name );
		$active = $active === 'yes' || $active === '1' || $active === 1 || $active === true;

		/**
		 * Filters whether admin BCC is active.
		 *
		 * @since 2.0.0
		 *
		 * @param bool   $active     Whether BCC is active.
		 * @param string $email_name Email type identifier.
		 */
		return apply_filters( 'geodir_mail_admin_bcc_active', $active, $email_name );
	}

	/**
	 * Send user publish post email.
	 *
	 * @since 3.0.0
	 *
	 * @param object $post Post object.
	 * @param array  $data Additional data.
	 * @return bool Whether email was sent.
	 */
	public function send_user_publish_post_email( $post, array $data = [] ): bool {
		$email_name = 'user_publish_post';

		if ( ! $this->is_enabled( $email_name ) ) {
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

		/**
		 * Skip email send filter.
		 *
		 * @since 2.3.58
		 *
		 * @param bool   $skip       Whether to skip sending.
		 * @param string $email_name Email type identifier.
		 * @param array  $email_vars Email variables.
		 */
		if ( apply_filters( 'geodir_skip_email_send', false, $email_name, $email_vars ) === true ) {
			return false;
		}

		/**
		 * Fires before user publish post email is sent.
		 *
		 * @since 2.0.0
		 *
		 * @param string $email_name Email type identifier.
		 * @param array  $email_vars Email variables.
		 */
		do_action( 'geodir_pre_user_publish_post_email', $email_name, $email_vars );

		$subject      = $this->get_subject( $email_name, $email_vars );
		$message_body = $this->get_content( $email_name, $email_vars );
		$headers      = $this->get_headers( $email_name, $email_vars );
		$attachments  = $this->get_attachments( $email_name, $email_vars );

		$plain_text = $this->get_email_type() !== 'html';

		// Try to locate specific email template, fallback to default.php
		$template_dir = $plain_text ? 'emails/plain/' : 'emails/';
		$specific_template = $template_dir . 'geodir-email-' . $email_name . '.php';

		// Check if specific template exists (in theme or plugin)
		$located = geodir_locate_template( $specific_template );

		// Fallback to default.php if specific template not found
		if ( ! $located || ! file_exists( $located ) ) {
			$template = $template_dir . 'default.php';
		} else {
			$template = $specific_template;
		}

		$content = geodir_get_template_html( $template, [
			'email_name'    => $email_name,
			'email_vars'    => $email_vars,
			'email_heading' => '',
			'sent_to_admin' => false,
			'plain_text'    => $plain_text,
			'message_body'  => $message_body,
		] );

		$sent = $this->send( $recipient, $subject, $content, $headers, $attachments, $email_name, $email_vars );

		if ( $this->is_admin_bcc_active( $email_name ) ) {
			$admin_recipient = $this->get_admin_email();
			$admin_subject   = $subject . ' - ADMIN BCC COPY';
			$this->send( $admin_recipient, $admin_subject, $content, $headers, $attachments, $email_name, $email_vars );
		}

		/**
		 * Fires after user publish post email is sent.
		 *
		 * @since 2.0.0
		 *
		 * @param string $email_name Email type identifier.
		 * @param array  $email_vars Email variables.
		 */
		do_action( 'geodir_post_user_publish_post_email', $email_name, $email_vars );

		return $sent;
	}

	/**
	 * Send listing owner notification about a new comment.
	 *
	 * @since 3.0.0
	 *
	 * @param object $comment Comment object.
	 * @param array  $data    Additional data.
	 * @return bool Whether email was sent.
	 */
	public function send_owner_comment_submit_email( $comment, array $data = [] ): bool {
		$email_name = 'owner_comment_submit';

		if ( ! $this->is_enabled( $email_name ) ) {
			return false;
		}

		if ( empty( $comment ) || empty( $comment->comment_post_ID ) ) {
			return false;
		}

		if ( ! geodir_is_gd_post_type( get_post_type( $comment->comment_post_ID ) ) ) {
			return false;
		}

		$gd_post = geodir_get_post_info( $comment->comment_post_ID );
		if ( empty( $gd_post ) ) {
			return false;
		}

		$author = get_userdata( $gd_post->post_author );
		if ( empty( $author ) ) {
			return false;
		}

		$recipient = $author->user_email;
		$to_name   = geodir_get_client_name( $gd_post->post_author );

		if ( empty( $comment ) || ! is_email( $recipient ) ) {
			return false;
		}

		$comment_ID = $comment->comment_ID;

		$email_vars              = $data;
		$email_vars['post']      = $gd_post;
		$email_vars['comment']   = $comment;
		$email_vars['to_name']   = $to_name;
		$email_vars['to_email']  = $recipient;
		$email_vars['from_name'] = ! empty( $comment->comment_author ) ? $comment->comment_author : '';
		$email_vars['reply_to']  = ! empty( $comment->comment_author_email ) ? $comment->comment_author_email : '';

		// Author does not have permission to moderate comments
		if ( ! $author->has_cap( 'moderate_comments' ) ) {
			$home_url                              = trailingslashit( home_url() );
			$email_vars['comment_approve_link']    = add_query_arg( [
				'_gd_action' => 'approve_comment',
				'c'          => $comment_ID,
				'_nonce'     => md5( 'approve_comment_' . $comment_ID ),
			], $home_url );
			$email_vars['comment_trash_link']      = add_query_arg( [
				'_gd_action' => 'trash_comment',
				'c'          => $comment_ID,
				'_nonce'     => md5( 'trash_comment_' . $comment_ID ),
			], $home_url );
			$email_vars['comment_spam_link']       = add_query_arg( [
				'_gd_action' => 'spam_comment',
				'c'          => $comment_ID,
				'_nonce'     => md5( 'spam_comment_' . $comment_ID ),
			], $home_url );
			$email_vars['comment_moderation_link'] = '';
		}

		/**
		 * Skip email send filter.
		 *
		 * @since 2.3.58
		 *
		 * @param bool   $skip       Whether to skip sending.
		 * @param string $email_name Email type identifier.
		 * @param array  $email_vars Email variables.
		 */
		if ( apply_filters( 'geodir_skip_email_send', false, $email_name, $email_vars ) === true ) {
			return false;
		}

		/**
		 * Fires before owner comment submit email is sent.
		 *
		 * @since 2.0.0
		 *
		 * @param string $email_name Email type identifier.
		 * @param array  $email_vars Email variables.
		 */
		do_action( 'geodir_pre_owner_comment_submit_email', $email_name, $email_vars );

		$subject      = $this->get_subject( $email_name, $email_vars );
		$message_body = $this->get_content( $email_name, $email_vars );
		$headers      = $this->get_headers( $email_name, $email_vars, '', $email_vars['from_name'] );
		$attachments  = $this->get_attachments( $email_name, $email_vars );

		$plain_text = $this->get_email_type() !== 'html';

		// Try to locate specific email template, fallback to default.php
		$template_dir = $plain_text ? 'emails/plain/' : 'emails/';
		$specific_template = $template_dir . 'geodir-email-' . $email_name . '.php';

		// Check if specific template exists (in theme or plugin)
		$located = geodir_locate_template( $specific_template );

		// Fallback to default.php if specific template not found
		if ( ! $located || ! file_exists( $located ) ) {
			$template = $template_dir . 'default.php';
		} else {
			$template = $specific_template;
		}



		$content = geodir_get_template_html( $template, [
			'email_name'    => $email_name,
			'email_vars'    => $email_vars,
			'email_heading' => '',
			'sent_to_admin' => false,
			'plain_text'    => $plain_text,
			'message_body'  => $message_body,
		] );
//		echo '$message_body:'.$message_body."<br><br>";
//		echo '$recipient:'.$recipient."<br><br>";
//		echo '$subject:'.$subject."<br><br>";
//		echo $content.'###'.$template.'###';exit;
		$sent = $this->send( $recipient, $subject, $content, $headers, $attachments, $email_name, $email_vars );

		if ( $this->is_admin_bcc_active( $email_name ) ) {
			$admin_recipient = $this->get_admin_email();
			$admin_subject   = $subject . ' - ADMIN BCC COPY';
			$this->send( $admin_recipient, $admin_subject, $content, $headers, $attachments, $email_name, $email_vars );
		}

		/**
		 * Fires after owner comment submit email is sent.
		 *
		 * @since 2.0.0
		 *
		 * @param string $email_name Email type identifier.
		 * @param array  $email_vars Email variables.
		 */
		do_action( 'geodir_post_owner_comment_submit_email', $email_name, $email_vars );

		return $sent;
	}

	/**
	 * Send listing owner notification when comment is approved.
	 *
	 * @since 3.0.0
	 *
	 * @param object $comment Comment object.
	 * @param array  $data    Additional data.
	 * @return bool Whether email was sent.
	 */
	public function send_owner_comment_approved_email( $comment, array $data = [] ): bool {
		$email_name = 'owner_comment_approved';

		if ( ! $this->is_enabled( $email_name ) ) {
			return false;
		}

		$gd_post = geodir_get_post_info( $comment->comment_post_ID );
		if ( empty( $gd_post ) || empty( $comment ) ) {
			return false;
		}

		$author_data = get_userdata( $gd_post->post_author );
		if ( empty( $author_data ) ) {
			return false;
		}

		$recipient = ! empty( $author_data->user_email ) ? $author_data->user_email : '';

		if ( empty( $gd_post ) || ! is_email( $recipient ) ) {
			return false;
		}

		$email_vars             = $data;
		$email_vars['post']     = $gd_post;
		$email_vars['comment']  = $comment;
		$email_vars['to_name']  = geodir_get_client_name( $gd_post->post_author );
		$email_vars['to_email'] = $recipient;

		/**
		 * Skip email send filter.
		 *
		 * @since 2.3.58
		 *
		 * @param bool   $skip       Whether to skip sending.
		 * @param string $email_name Email type identifier.
		 * @param array  $email_vars Email variables.
		 */
		if ( apply_filters( 'geodir_skip_email_send', false, $email_name, $email_vars ) === true ) {
			return false;
		}

		/**
		 * Fires before owner comment approved email is sent.
		 *
		 * @since 2.0.0
		 *
		 * @param string $email_name Email type identifier.
		 * @param array  $email_vars Email variables.
		 */
		do_action( 'geodir_pre_owner_comment_approved_email', $email_name, $email_vars );

		$subject      = $this->get_subject( $email_name, $email_vars );
		$message_body = $this->get_content( $email_name, $email_vars );
		$headers      = $this->get_headers( $email_name, $email_vars );
		$attachments  = $this->get_attachments( $email_name, $email_vars );

		$plain_text = $this->get_email_type() !== 'html';

		// Try to locate specific email template, fallback to default.php
		$template_dir = $plain_text ? 'emails/plain/' : 'emails/';
		$specific_template = $template_dir . 'geodir-email-' . $email_name . '.php';

		// Check if specific template exists (in theme or plugin)
		$located = geodir_locate_template( $specific_template );

		// Fallback to default.php if specific template not found
		if ( ! $located || ! file_exists( $located ) ) {
			$template = $template_dir . 'default.php';
		} else {
			$template = $specific_template;
		}

		$content = geodir_get_template_html( $template, [
			'email_name'    => $email_name,
			'email_vars'    => $email_vars,
			'email_heading' => '',
			'sent_to_admin' => false,
			'plain_text'    => $plain_text,
			'message_body'  => $message_body,
		] );

		$sent = $this->send( $recipient, $subject, $content, $headers, $attachments, $email_name, $email_vars );

		if ( $this->is_admin_bcc_active( $email_name ) ) {
			$admin_recipient = $this->get_admin_email();
			$admin_subject   = $subject . ' - ADMIN BCC COPY';
			$this->send( $admin_recipient, $admin_subject, $content, $headers, $attachments, $email_name, $email_vars );
		}

		/**
		 * Fires after owner comment approved email is sent.
		 *
		 * @since 2.0.0
		 *
		 * @param string $email_name Email type identifier.
		 * @param array  $email_vars Email variables.
		 */
		do_action( 'geodir_post_owner_comment_approved_email', $email_name, $email_vars );

		return $sent;
	}

	/**
	 * Send comment author notification when their comment is approved.
	 *
	 * @since 3.0.0
	 *
	 * @param object $comment Comment object.
	 * @param array  $data    Additional data.
	 * @return bool Whether email was sent.
	 */
	public function send_author_comment_approved_email( $comment, array $data = [] ): bool {
		$email_name = 'author_comment_approved';

		if ( ! $this->is_enabled( $email_name ) ) {
			return false;
		}

		$recipient = ! empty( $comment->comment_author_email ) ? $comment->comment_author_email : '';
		$to_name   = ! empty( $comment->comment_author ) ? $comment->comment_author : __( 'Author', 'geodirectory' );

		if ( empty( $comment ) || ! is_email( $recipient ) ) {
			return false;
		}

		$gd_post = geodir_get_post_info( $comment->comment_post_ID );
		if ( empty( $gd_post ) ) {
			return false;
		}

		$email_vars             = $data;
		$email_vars['post']     = $gd_post;
		$email_vars['comment']  = $comment;
		$email_vars['to_name']  = $to_name;
		$email_vars['to_email'] = $recipient;

		/**
		 * Skip email send filter.
		 *
		 * @since 2.3.58
		 *
		 * @param bool   $skip       Whether to skip sending.
		 * @param string $email_name Email type identifier.
		 * @param array  $email_vars Email variables.
		 */
		if ( apply_filters( 'geodir_skip_email_send', false, $email_name, $email_vars ) === true ) {
			return false;
		}

		/**
		 * Fires before author comment approved email is sent.
		 *
		 * @since 2.0.0
		 *
		 * @param string $email_name Email type identifier.
		 * @param array  $email_vars Email variables.
		 */
		do_action( 'geodir_pre_author_comment_approved_email', $email_name, $email_vars );

		$subject      = $this->get_subject( $email_name, $email_vars );
		$message_body = $this->get_content( $email_name, $email_vars );
		$headers      = $this->get_headers( $email_name, $email_vars );
		$attachments  = $this->get_attachments( $email_name, $email_vars );

		$plain_text = $this->get_email_type() !== 'html';

		// Try to locate specific email template, fallback to default.php
		$template_dir = $plain_text ? 'emails/plain/' : 'emails/';
		$specific_template = $template_dir . 'geodir-email-' . $email_name . '.php';

		// Check if specific template exists (in theme or plugin)
		$located = geodir_locate_template( $specific_template );

		// Fallback to default.php if specific template not found
		if ( ! $located || ! file_exists( $located ) ) {
			$template = $template_dir . 'default.php';
		} else {
			$template = $specific_template;
		}

		$content = geodir_get_template_html( $template, [
			'email_name'    => $email_name,
			'email_vars'    => $email_vars,
			'email_heading' => '',
			'sent_to_admin' => false,
			'plain_text'    => $plain_text,
			'message_body'  => $message_body,
		] );

		$sent = $this->send( $recipient, $subject, $content, $headers, $attachments, $email_name, $email_vars );

		if ( $this->is_admin_bcc_active( $email_name ) ) {
			$admin_recipient = $this->get_admin_email();
			$admin_subject   = $subject . ' - ADMIN BCC COPY';
			$this->send( $admin_recipient, $admin_subject, $content, $headers, $attachments, $email_name, $email_vars );
		}

		/**
		 * Fires after author comment approved email is sent.
		 *
		 * @since 2.0.0
		 *
		 * @param string $email_name Email type identifier.
		 * @param array  $email_vars Email variables.
		 */
		do_action( 'geodir_post_author_comment_approved_email', $email_name, $email_vars );

		return $sent;
	}
}
