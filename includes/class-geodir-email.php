<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Transactional Emails Controller
 *
 * GeoDirectory Emails Class which handles the sending on transactional emails and email templates. This class loads in available emails.
 *
 * @class        GeoDir_Email
 * @version        2.0.0
 * @package        GeoDirectory/Classes/Emails
 * @category    Class
 * @author        GeoDIrectory
 */
class GeoDir_Email {

	/**
	 * Setup anything neded for later emails.
	 */
	public static function init() {
		// add the email header and footer
		add_action( 'geodir_email_header', array( __CLASS__, 'email_header' ), 10, 5 );
		add_action( 'geodir_email_footer', array( __CLASS__, 'email_footer' ), 10, 4 );

		// hooks email actions
		add_action( 'geodir_send_to_friend_email', array( __CLASS__, 'send_to_friend_email' ), 10, 2 );
		add_action( 'geodir_send_enquiry_email', array( __CLASS__, 'send_enquiry_email' ), 10, 2 );

		// frontend post save emails
		add_action( 'geodir_ajax_post_saved', array( __CLASS__, 'send_email_on_post_saved' ), 10, 2 );


	}

	/**
	 * Get the email logo.
	 *
	 * @return string Logo url.
	 */
	public static function get_email_logo( $size = 'full' ) {
		$attachment_id = geodir_get_option( 'email_logo' );

		$email_logo = '';
		if ( ! empty( $attachment_id ) ) {
			$email_logo = wp_get_attachment_image( $attachment_id, $size );
		}

		return apply_filters( 'geodir_get_email_logo', $email_logo, $attachment_id, $size );
	}

	/**
	 * Get the email header template.
	 *
	 * @param string $email_heading
	 * @param string $email_name
	 * @param array $email_vars
	 * @param bool $plain_text
	 * @param bool $sent_to_admin
	 */
	public static function email_header( $email_heading = '', $email_name = '', $email_vars = array(), $plain_text = false, $sent_to_admin = false ) {
		if ( $plain_text ) {
			geodir_get_template( 'emails/plain/geodir-email-header.php', array(
				'email_heading' => $email_heading,
				'email_name'    => $email_name,
				'email_vars'    => $email_vars,
				'plain_text'    => $plain_text,
				'sent_to_admin' => $sent_to_admin
			) );
		} else {
			geodir_get_template( 'emails/geodir-email-header.php', array(
				'email_heading' => $email_heading,
				'email_name'    => $email_name,
				'email_vars'    => $email_vars,
				'plain_text'    => $plain_text,
				'sent_to_admin' => $sent_to_admin
			) );
		}
	}

	/**
	 * Get the email footer template.
	 *
	 * @param string $email_name
	 * @param array $email_vars
	 * @param bool $plain_text
	 * @param bool $sent_to_admin
	 */
	public static function email_footer( $email_name = '', $email_vars = array(), $plain_text = false, $sent_to_admin = false ) {
		if ( $plain_text ) {
			geodir_get_template( 'emails/plain/geodir-email-footer.php', array(
				'email_name'    => $email_name,
				'email_vars'    => $email_vars,
				'plain_text'    => $plain_text,
				'sent_to_admin' => $sent_to_admin
			) );
		} else {
			geodir_get_template( 'emails/geodir-email-footer.php', array(
				'email_name'    => $email_name,
				'email_vars'    => $email_vars,
				'plain_text'    => $plain_text,
				'sent_to_admin' => $sent_to_admin
			) );
		}
	}

	/**
	 * Get the email message wraped in the header and footer.
	 *
	 * @param $message
	 * @param string $email_name
	 * @param array $email_vars
	 * @param string $email_heading
	 * @param bool $plain_text
	 * @param bool $sent_to_admin
	 *
	 * @return string
	 */
	public static function email_wrap_message( $message, $email_name = '', $email_vars = array(), $email_heading = '', $plain_text = false, $sent_to_admin = false ) {
		// Buffer
		ob_start();

		do_action( 'geodir_email_header', $email_heading, $email_name, $email_vars, $plain_text, $sent_to_admin );

		if ( $plain_text ) {
			echo wp_strip_all_tags( $message );
		} else {
			echo wpautop( wptexturize( $message ) );
		}

		do_action( 'geodir_email_footer', $email_name, $email_vars, $plain_text, $sent_to_admin );

		// Get contents
		$message = ob_get_clean();

		return $message;
	}

	/**
	 * Send to friend email.
	 *
	 * @param $post
	 * @param $data
	 *
	 * @return bool|void
	 */
	public static function send_to_friend_email( $post, $data ) {
		$email_name = 'send_friend';

		if ( ! self::is_email_enabled( $email_name ) ) {
			return false;
		}

		$defaults           = array(
			'from_name'  => '',
			'from_email' => '',
			'to_name'    => '',
			'to_email'   => '',
			'subject'    => '',
			'comments'   => ''
		);
		$email_vars         = wp_parse_args( $data, $defaults );
		$email_vars['post'] = $post;

		if ( empty( $post ) || ! is_email( $email_vars['to_email'] ) ) {
			return;
		}

		$recipient = $email_vars['to_email'];

		do_action( 'geodir_pre_send_to_friend_email', $email_name, $email_vars );

		$subject      = self::get_subject( $email_name, $email_vars );
		$message_body = self::get_content( $email_name, $email_vars );
		$headers      = self::get_headers( $email_name, $email_vars, $email_vars['from_email'], $email_vars['from_name'] );
		$attachments  = self::get_attachments( $email_name, $email_vars );

		$plain_text = self::get_email_type() != 'html' ? true : false;
		$template   = $plain_text ? 'emails/plain/geodir-email-' . $email_name . '.php' : 'emails/geodir-email-' . $email_name . '.php';

		$content = geodir_get_template_html( $template, array(
			'email_name'    => $email_name,
			'email_vars'    => $email_vars,
			'sent_to_admin' => false,
			'plain_text'    => $plain_text,
			'message_body'  => $message_body,
		) );

		$sent = self::send( $recipient, $subject, $content, $headers, $attachments );

		if ( self::is_admin_bcc_active( $email_name ) ) {
			$recipient = self::get_admin_email();
			$subject .= ' - ADMIN BCC COPY';
			self::send( $recipient, $subject, $content, $headers, $attachments );
		}

		do_action( 'geodir_post_send_to_friend_email', $email_name, $email_vars );

		return $sent;
	}

	/**
	 * Check if the email is enabled for the email type.
	 *
	 * @param $email_name
	 *
	 * @return mixed|void
	 */
	public static function is_email_enabled( $email_name ) {
		switch ( $email_name ) {
			// TODO add some cases
			default:
				$active = geodir_get_option( 'email_' . $email_name );
				$active = $active == 'yes' || $active == '1' ? true : false;
				break;
		}

		return apply_filters( 'geodir_email_is_enabled', $active, $email_name );
	}

	/**
	 * Get the email subject by type.
	 *
	 * @param string $email_name
	 * @param array $email_vars
	 *
	 * @return mixed|void
	 */
	public static function get_subject( $email_name = '', $email_vars = array() ) {
		switch ( $email_name ) {
			// TODO some custom options
			default:
				$subject = geodir_get_option( 'email_' . $email_name . '_subject' );
				break;
		}

		$subject = self::replace_variables( __( $subject, 'geodirectory' ), $email_name, $email_vars );

		return apply_filters( 'geodir_email_subject', $subject, $email_name, $email_vars );
	}

	/**
	 * Replace variables in the email text.
	 *
	 * @param $content
	 * @param string $email_name
	 * @param array $email_vars
	 *
	 * @return mixed|void
	 */
	public static function replace_variables( $content, $email_name = '', $email_vars = array() ) {
		$site_url        = home_url();
		$blogname        = geodir_get_blogname();
		$email_from_anme = self::get_mail_from_name();
		$login_url       = geodir_login_url();
		$timestamp       = current_time( 'timestamp' );
		$date            = date_i18n( get_option( 'date_format' ), $timestamp );
		$time            = date_i18n( get_option( 'time_format' ), $timestamp );
		$date_time       = $date . ' ' . $time;

		$replace_array = array(
			'[#blogname#]'      => $blogname,
			'[#site_url#]'      => $site_url,
			'[#site_name_url#]' => '<a href="' . esc_url( $site_url ) . '">' . $site_url . '</a>',
			'[#site_link#]'     => '<a href="' . esc_url( $site_url ) . '">' . $blogname . '</a>',
			'[#site_name#]'     => $email_from_anme,
			'[#login_url#]'     => $login_url,
			'[#login_link#]'    => '<a href="' . esc_url( $login_url ) . '">' . __( 'Login', 'geodirectory' ) . '</a>',
			'[#current_date#]'  => date_i18n( 'Y-m-d H:i:s', $timestamp ),
			'[#date#]'          => $date,
			'[#time#]'          => $time,
			'[#date_time#]'     => $date_time,
			'[#from_name#]'     => self::get_mail_from_name(),
			'[#from_email#]'    => self::get_mail_from(),
		);

		$post = ! empty( $email_vars['post'] ) ? $email_vars['post'] : null;
		if ( empty( $post ) && ! empty( $email_vars['post_id'] ) ) {
			$post = geodir_get_post_info( $email_vars['post_id'] );
		}

		if ( ! empty( $post ) ) {
			$post_id          = $post->ID;
			$post_author_name = geodir_get_client_name( $post->post_author );

			$replace_array['[#post_id#]']          = $post_id;
			$replace_array['[#post_status#]']      = $post->post_status;
			$replace_array['[#post_date#]']        = $post->post_date;
			$replace_array['[#post_author_ID#]']   = $post->post_author;
			$replace_array['[#post_author_name#]'] = $post_author_name;
			$replace_array['[#client_name#]']      = $post_author_name;
			$replace_array['[#listing_title#]']    = get_the_title( $post_id );
			$replace_array['[#listing_url#]']      = get_permalink( $post_id );
			$replace_array['[#listing_link#]']     = '<a href="' . esc_url( $replace_array['[#listing_url#]'] ) . '">' . $replace_array['[#listing_title#]'] . '</a>';
		}

		$comment = ! empty( $email_vars['comment'] ) ? $email_vars['comment'] : null;
		if ( empty( $comment ) && ! empty( $email_vars['comment_ID'] ) ) {
			$comment = get_comment( $email_vars['comment_ID'] );
		}
		if ( ! empty( $comment ) ) {
			$comment_ID = $comment->comment_ID;

			$replace_array['[#comment_ID#]']              = $comment_ID;
			$replace_array['[#comment_author#]']          = $comment->comment_author;
			$replace_array['[#comment_author_IP#]']       = $comment->comment_author_IP;
			$replace_array['[#comment_author_email#]']    = $comment->comment_author_email;
			$replace_array['[#comment_date#]']            = $comment->comment_date;
			$replace_array['[#comment_content#]']         = wp_specialchars_decode( $comment->comment_content );
			$replace_array['[#comment_approve_link#]']    = admin_url( "comment.php?action=approve&c={$comment_ID}#wpbody-content" );
			$replace_array['[#comment_trash_link#]']      = admin_url( "comment.php?action=trash&c={$comment_ID}#wpbody-content" );
			$replace_array['[#comment_spam_link#]']       = admin_url( "comment.php?action=spam&c={$comment_ID}#wpbody-content" );
			$replace_array['[#comment_moderation_link#]'] = admin_url( "edit-comments.php?comment_status=moderated#wpbody-content" );
		}

		foreach ( $email_vars as $key => $value ) {
			if ( is_scalar( $value ) ) {
				$replace_array[ '[#' . $key . '#]' ] = $value;
			}
		}

		$replace_array = apply_filters( 'geodir_email_wild_cards', $replace_array, $content, $email_name, $email_vars );

		foreach ( $replace_array as $key => $value ) {
			$content = str_replace( $key, $value, $content );
		}

		return apply_filters( 'geodir_email_content_replace_vars', $content, $email_name, $email_vars );
	}

	/**
	 * Get the from name for outgoing emails.
	 *
	 * @return string Site name.
	 */
	public static function get_mail_from_name() {
		$mail_from_name = geodir_get_option( 'email_name' );

		if ( ! $mail_from_name ) {
			$mail_from_name = geodir_get_blogname();
		}

		return apply_filters( 'geodir_get_mail_from_name', stripslashes( $mail_from_name ) );
	}

	/**
	 * Get the from address for outgoing emails.
	 *
	 * @return string|mixed|void The email ID.
	 */
	public static function get_mail_from() {
		$mail_from = geodir_get_option( 'email_address' );

		if ( ! $mail_from ) {
			$mail_from = self::get_admin_email();
		}

		return apply_filters( 'geodir_get_mail_from', $mail_from );
	}

	/**
	 * Get the site admin email.
	 *
	 * @return mixed|void
	 */
	public static function get_admin_email() {
		$admin_email = get_option( 'admin_email' );

		return apply_filters( 'geodir_admin_email', $admin_email );
	}

	/**
	 * Get the email content by type.
	 *
	 * @param string $email_name
	 * @param array $email_vars
	 *
	 * @return mixed|void
	 */
	public static function get_content( $email_name = '', $email_vars = array() ) {
		switch ( $email_name ) {
			// TODO some custom options
			default:
				$content = geodir_get_option( 'email_' . $email_name . '_body' );
				break;
		}

		$content = self::replace_variables( __( $content, 'geodirectory' ), $email_name, $email_vars );

		return apply_filters( 'geodir_email_content', $content, $email_name, $email_vars );
	}

	/**
	 * Get the email headers for sending.
	 *
	 * @param $email_name
	 * @param array $email_vars
	 * @param string $from_email
	 * @param string $from_name
	 *
	 * @return mixed|void
	 */
	public static function get_headers( $email_name, $email_vars = array(), $from_email = '', $from_name = '' ) {
		$from_email = ! empty( $from_email ) ? $from_email : self::get_mail_from();
		$from_name  = ! empty( $from_name ) ? $from_name : self::get_mail_from_name();
		$reply_to   = ! empty( $email_vars['reply_to'] ) ? $email_vars['reply_to'] : $from_email;

		$headers = "From: " . stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) . " <$from_email>\r\n";
		$headers .= "Reply-To: " . $reply_to . "\r\n";
		$headers .= "Content-Type: " . self::get_content_type() . "; charset=\"" . get_option( 'blog_charset' ) . "\"\r\n";

		return apply_filters( 'geodir_email_headers', $headers, $email_name, $email_vars, $from_email, $from_name );
	}

	/**
	 * Get the content type of the email html or plain.
	 *
	 * @param string $content_type
	 * @param string $email_type
	 *
	 * @return string
	 */
	public static function get_content_type( $content_type = 'text/html', $email_type = '' ) {
		if ( empty( $email_type ) ) {
			$email_type = self::get_email_type();
		}

		switch ( $email_type ) {
			case 'plain' :
				$content_type = 'text/plain';
				break;
			case 'multipart' :
				$content_type = 'multipart/alternative';
				break;
		}

		return $content_type;
	}

	/**
	 * Get the email type from settings, html/plain.
	 *
	 * @return mixed|void
	 */
	public static function get_email_type() {
		$email_type = geodir_get_option( 'email_type' );

		if ( empty( $email_type ) ) {
			$email_type = 'html';
		}

		return apply_filters( 'geodir_get_email_type', $email_type );
	}

	/**
	 * Get the email attachments per type.
	 *
	 * @param string $email_name
	 * @param array $email_vars
	 *
	 * @return mixed|void
	 */
	public static function get_attachments( $email_name = '', $email_vars = array() ) {
		$attachments = array();

		return apply_filters( 'geodir_email_attachments', $attachments, $email_name, $email_vars );
	}

	/**
	 * @param $to
	 * @param $subject
	 * @param $message
	 * @param $headers
	 * @param array $attachments
	 * @param string $email_name
	 * @param array $email_vars
	 *
	 * @return bool
	 */
	public static function send( $to, $subject, $message, $headers, $attachments = array(), $email_name = '', $email_vars = array() ) {
		add_filter( 'wp_mail_from', array( __CLASS__, 'get_mail_from' ) );
		add_filter( 'wp_mail_from_name', array( __CLASS__, 'get_mail_from_name' ) );
		add_filter( 'wp_mail_content_type', array( __CLASS__, 'get_content_type' ) );

		$message = self::style_body( $message, $email_name, $email_vars );
		$message = apply_filters( 'geodir_mail_content', $message, $email_name, $email_vars );

		$sent = wp_mail( $to, $subject, $message, $headers, $attachments );

		if ( ! $sent ) {
			$log_message = wp_sprintf( __( "\nTime: %s\nTo: %s\nSubject: %s\n", 'geodirectory' ), date_i18n( 'F j Y H:i:s', current_time( 'timestamp' ) ), ( is_array( $to ) ? implode( ', ', $to ) : $to ), $subject );
			geodir_error_log( $log_message, __( "Email from GeoDirectory plugin failed to send", 'geodirectory' ), __FILE__, __LINE__ );
		}

		remove_filter( 'wp_mail_from', array( __CLASS__, 'geodir_get_mail_from' ) );
		remove_filter( 'wp_mail_from_name', array( __CLASS__, 'geodir_get_mail_from_name' ) );
		remove_filter( 'wp_mail_content_type', array( __CLASS__, 'geodir_mail_get_content_type' ) );

		return $sent;
	}

	/**
	 * Style the body of the email content.
	 *
	 * @param $content
	 * @param string $email_name
	 * @param array $email_vars
	 *
	 * @return string
	 */
	public static function style_body( $content, $email_name = '', $email_vars = array() ) {
		// make sure we only inline CSS for html emails
		if ( in_array( self::get_email_type(), array( 'html', 'multipart' ) ) && class_exists( 'DOMDocument' ) ) {
			// include css inliner
			if ( ! class_exists( 'Emogrifier' ) ) {
				include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/libraries/class-emogrifier.php' );
			}

			ob_start();
			geodir_get_template( 'emails/geodir-email-styles.php', array(
				'email_name' => $email_name,
				'email_vars' => $email_vars
			) );
			$css = apply_filters( 'geodir_email_styles', ob_get_clean() );

			// apply CSS styles inline for picky email clients
			try {
				$emogrifier = new Emogrifier( $content, $css );
				$content    = $emogrifier->emogrify();
			} catch ( Exception $e ) {
				geodir_error_log( $e->getMessage(), 'emogrifier' );
			}
		}

		return $content;
	}



	/******************************************* Emails ***************************************/

	/**
	 * Check if the admin BCC is active for the email type.
	 *
	 * @param string $email_name
	 *
	 * @return mixed|void
	 */
	public static function is_admin_bcc_active( $email_name = '' ) {
		switch ( $email_name ) {
			// TODO add some cases
			default:
				$active = geodir_get_option( 'email_bcc_' . $email_name );
				$active = $active == 'yes' || $active == '1' ? true : false;
				break;
		}

		return apply_filters( 'geodir_mail_admin_bcc_active', $active, $email_name );
	}

	/**
	 * Send enquiry email.
	 *
	 * @param $post
	 * @param $data
	 *
	 * @return bool
	 */
	public static function send_enquiry_email( $post, $data ) {
		$email_name = 'send_enquiry';

		if ( ! self::is_email_enabled( $email_name ) ) {
			return false;
		}

		$defaults           = array(
			'from_name'  => '',
			'from_email' => '',
			'phone'      => '',
			'comments'   => ''
		);
		$email_vars         = wp_parse_args( $data, $defaults );
		$email_vars['post'] = $post;

		$email_vars['to_name']  = geodir_get_client_name( $post->post_author );
		$email_vars['to_email'] = geodir_get_post_meta( $post->ID, 'geodir_email', true );
		if ( empty( $email_vars['to_email'] ) ) {
			$author_data            = get_userdata( $post->post_author );
			$email_vars['to_email'] = ! empty( $author_data->user_email ) ? $author_data->user_email : '';
		}

		if ( empty( $post ) || ! is_email( $email_vars['to_email'] ) ) {
			return;
		}

		$recipient = $email_vars['to_email'];

		do_action( 'geodir_pre_send_enquiry_email', $email_name, $email_vars );

		$subject      = self::get_subject( $email_name, $email_vars );
		$message_body = self::get_content( $email_name, $email_vars );
		$headers      = self::get_headers( $email_name, $email_vars, $email_vars['from_email'], $email_vars['from_name'] );
		$attachments  = self::get_attachments( $email_name, $email_vars );

		$plain_text = self::get_email_type() != 'html' ? true : false;
		$template   = $plain_text ? 'emails/plain/geodir-email-' . $email_name . '.php' : 'emails/geodir-email-' . $email_name . '.php';

		$content = geodir_get_template_html( $template, array(
			'email_name'    => $email_name,
			'email_vars'    => $email_vars,
			'sent_to_admin' => false,
			'plain_text'    => $plain_text,
			'message_body'  => $message_body,
		) );

		$sent = self::send( $recipient, $subject, $content, $headers, $attachments );

		if ( self::is_admin_bcc_active( $email_name ) ) {
			$recipient = self::get_admin_email();
			$subject .= ' - ADMIN BCC COPY';
			self::send( $recipient, $subject, $content, $headers, $attachments );
		}

		do_action( 'geodir_post_send_enquiry_email', $email_name, $email_vars );

		return $sent;
	}

	/**
	 * Send the user an email when their post is published.
	 *
	 * @param $post
	 * @param array $data
	 *
	 * @return bool
	 */
	public static function send_user_publish_post_email( $post, $data = array() ) {
		$email_name = 'user_publish_post';

		if ( ! self::is_email_enabled( $email_name ) ) {
			return false;
		}

		$author_data = get_userdata( $post->post_author );
		if ( empty( $author_data ) ) {
			return false;
		}

		$recipient = ! empty( $author_data->user_email ) ? $author_data->user_email : '';

		if ( empty( $post ) || ! is_email( $recipient ) ) {
			return;
		}

		$email_vars             = $data;
		$email_vars['post']     = $post;
		$email_vars['to_name']  = geodir_get_client_name( $post->post_author );
		$email_vars['to_email'] = $recipient;

		do_action( 'geodir_pre_user_publish_post_email', $email_name, $email_vars );

		$subject      = self::get_subject( $email_name, $email_vars );
		$message_body = self::get_content( $email_name, $email_vars );
		$headers      = self::get_headers( $email_name, $email_vars );
		$attachments  = self::get_attachments( $email_name, $email_vars );

		$plain_text = self::get_email_type() != 'html' ? true : false;
		$template   = $plain_text ? 'emails/plain/geodir-email-' . $email_name . '.php' : 'emails/geodir-email-' . $email_name . '.php';

		$content = geodir_get_template_html( $template, array(
			'email_name'    => $email_name,
			'email_vars'    => $email_vars,
			'sent_to_admin' => false,
			'plain_text'    => $plain_text,
			'message_body'  => $message_body,
		) );

		$sent = self::send( $recipient, $subject, $content, $headers, $attachments );

		if ( self::is_admin_bcc_active( $email_name ) ) {
			$recipient = self::get_admin_email();
			$subject .= ' - ADMIN BCC COPY';
			self::send( $recipient, $subject, $content, $headers, $attachments );
		}

		do_action( 'geodir_post_user_publish_post_email', $email_name, $email_vars );

		return $sent;
	}

	/**
	 * Send some email son post save.
	 *
	 * @param $post_data
	 * @param bool $update
	 */
	public static function send_email_on_post_saved( $post_data, $update = false ) {
		global $gd_notified_edited;

		if ( empty( $post_data['ID'] ) ) {
			return;
		}

		$post_ID = $post_data['ID'];
		$post    = geodir_get_post_info( $post_ID );
		if ( empty( $post ) ) {
			return;
		}
		if ( $update ) {
			$user_id = (int) get_current_user_id();

			if ( $user_id > 0 && ! empty( $post->post_author ) && $user_id == $post->post_author && ! is_super_admin() && empty( $gd_notified_edited[ $post_ID ] ) && self::is_email_enabled( 'admin_post_edit' ) ) {
				if ( empty( $gd_notified_edited ) ) {
					$gd_notified_edited = array();
				}
				$gd_notified_edited[ $post_ID ] = true;

				self::send_admin_post_edit_email( $post );
			}
		}

		if ( $post_data['post_status'] == 'pending' ) {
			// Send email to admin
			self::send_admin_pending_post_email( $post );

			// Send email to user
			self::send_user_pending_post_email( $post );
		}
	}

	/**
	 * Send the admin an email when a post is edited.
	 *
	 * @param $post
	 * @param array $data
	 *
	 * @return bool|void
	 */
	public static function send_admin_post_edit_email( $post, $data = array() ) {
		$email_name = 'admin_post_edit';

		if ( ! self::is_email_enabled( $email_name ) ) {
			return false;
		}

		$recipient = self::get_admin_email();

		if ( empty( $post ) || ! is_email( $recipient ) ) {
			return;
		}

		$email_vars             = $data;
		$email_vars['post']     = $post;
		$email_vars['to_email'] = self::get_admin_email();

		do_action( 'geodir_pre_admin_post_edit_email', $email_name, $email_vars );

		$subject      = self::get_subject( $email_name, $email_vars );
		$message_body = self::get_content( $email_name, $email_vars );
		$headers      = self::get_headers( $email_name, $email_vars );
		$attachments  = self::get_attachments( $email_name, $email_vars );

		$plain_text = self::get_email_type() != 'html' ? true : false;
		$template   = $plain_text ? 'emails/plain/geodir-email-' . $email_name . '.php' : 'emails/geodir-email-' . $email_name . '.php';

		$content = geodir_get_template_html( $template, array(
			'email_name'    => $email_name,
			'email_vars'    => $email_vars,
			'sent_to_admin' => true,
			'plain_text'    => $plain_text,
			'message_body'  => $message_body,
		) );

		$sent = self::send( $recipient, $subject, $content, $headers, $attachments );

		do_action( 'geodir_post_admin_post_edit_email', $email_name, $email_vars );

		return $sent;
	}

	/**
	 * Send the admin the post pending review email.
	 *
	 * @param $post
	 * @param array $data
	 *
	 * @return bool|void
	 */
	public static function send_admin_pending_post_email( $post, $data = array() ) {
		$email_name = 'admin_pending_post';

		if ( ! self::is_email_enabled( $email_name ) ) {
			return false;
		}

		$recipient = self::get_admin_email();

		if ( empty( $post ) || ! is_email( $recipient ) ) {
			return;
		}

		$email_vars             = $data;
		$email_vars['post']     = $post;
		$email_vars['to_email'] = self::get_admin_email();

		do_action( 'geodir_pre_admin_pending_post_email', $email_name, $email_vars );

		$subject      = self::get_subject( $email_name, $email_vars );
		$message_body = self::get_content( $email_name, $email_vars );
		$headers      = self::get_headers( $email_name, $email_vars );
		$attachments  = self::get_attachments( $email_name, $email_vars );

		$plain_text = self::get_email_type() != 'html' ? true : false;
		$template   = $plain_text ? 'emails/plain/geodir-email-' . $email_name . '.php' : 'emails/geodir-email-' . $email_name . '.php';

		$content = geodir_get_template_html( $template, array(
			'email_name'    => $email_name,
			'email_vars'    => $email_vars,
			'sent_to_admin' => true,
			'plain_text'    => $plain_text,
			'message_body'  => $message_body,
		) );

		$sent = self::send( $recipient, $subject, $content, $headers, $attachments );

		do_action( 'geodir_post_admin_pending_post_email', $email_name, $email_vars );

		return $sent;
	}

	/**
	 * Send the user an email about their post pending review.
	 *
	 * @param $post
	 * @param array $data
	 *
	 * @return bool
	 */
	public static function send_user_pending_post_email( $post, $data = array() ) {
		$email_name = 'user_pending_post';

		if ( ! self::is_email_enabled( $email_name ) ) {
			return false;
		}

		$author_data = get_userdata( $post->post_author );
		if ( empty( $author_data ) ) {
			return false;
		}

		$recipient = ! empty( $author_data->user_email ) ? $author_data->user_email : '';

		if ( empty( $post ) || ! is_email( $recipient ) ) {
			return;
		}

		$email_vars             = $data;
		$email_vars['post']     = $post;
		$email_vars['to_name']  = geodir_get_client_name( $post->post_author );
		$email_vars['to_email'] = $recipient;

		do_action( 'geodir_pre_user_pending_post_email', $email_name, $email_vars );

		$subject      = self::get_subject( $email_name, $email_vars );
		$message_body = self::get_content( $email_name, $email_vars );
		$headers      = self::get_headers( $email_name, $email_vars );
		$attachments  = self::get_attachments( $email_name, $email_vars );

		$plain_text = self::get_email_type() != 'html' ? true : false;
		$template   = $plain_text ? 'emails/plain/geodir-email-' . $email_name . '.php' : 'emails/geodir-email-' . $email_name . '.php';

		$content = geodir_get_template_html( $template, array(
			'email_name'    => $email_name,
			'email_vars'    => $email_vars,
			'sent_to_admin' => false,
			'plain_text'    => $plain_text,
			'message_body'  => $message_body,
		) );

		$sent = self::send( $recipient, $subject, $content, $headers, $attachments );

		if ( self::is_admin_bcc_active( $email_name ) ) {
			$recipient = self::get_admin_email();
			$subject .= ' - ADMIN BCC COPY';
			self::send( $recipient, $subject, $content, $headers, $attachments );
		}

		do_action( 'geodir_post_user_pending_post_email', $email_name, $email_vars );

		return $sent;
	}

	/**
	 * Send the listing owner a notification about a new comment.
	 *
	 * @param $comment
	 * @param array $data
	 *
	 * @return bool
	 */
	public static function send_owner_comment_submit_email( $comment, $data = array() ) {
		$email_name = 'owner_comment_submit';

		if ( ! self::is_email_enabled( $email_name ) ) {
			return false;
		}

		if ( empty( $comment ) || empty( $comment->comment_post_ID ) ) {
			return false;
		}

		$post = geodir_get_post_info( $comment->comment_post_ID );
		if ( empty( $post ) ) {
			return false;
		}

		$author = get_userdata( $post->post_author );
		if ( empty( $author ) ) {
			return false;
		}

		$recipient = $author->user_email;
		$to_name   = geodir_get_client_name( $post->post_author );

		if ( empty( $comment ) || ! is_email( $recipient ) ) {
			return;
		}
		$comment_ID = $comment->comment_ID;

		$email_vars              = $data;
		$email_vars['post']      = $post;
		$email_vars['comment']   = $comment;
		$email_vars['to_name']   = $to_name;
		$email_vars['to_email']  = $recipient;
		$email_vars['from_name'] = ! empty( $comment->comment_author ) ? $comment->comment_author : '';
		$email_vars['reply_to']  = ! empty( $comment->comment_author_email ) ? $comment->comment_author_email : '';

		// Author does not allowed to moderate comments.
		if ( ! $author->has_cap( 'moderate_comments' ) ) {
			$home_url                              = trailingslashit( home_url() );
			$email_vars['comment_approve_link']    = add_query_arg( array(
				'_gd_action' => 'approve_comment',
				'c'          => $comment_ID,
				'_nonce'     => md5( 'approve_comment_' . $comment_ID )
			), $home_url );
			$email_vars['comment_trash_link']      = add_query_arg( array(
				'_gd_action' => 'trash_comment',
				'c'          => $comment_ID,
				'_nonce'     => md5( 'trash_comment_' . $comment_ID )
			), $home_url );
			$email_vars['comment_spam_link']       = add_query_arg( array(
				'_gd_action' => 'spam_comment',
				'c'          => $comment_ID,
				'_nonce'     => md5( 'spam_comment_' . $comment_ID )
			), $home_url );
			$email_vars['comment_moderation_link'] = '';
		}

		do_action( 'geodir_pre_owner_comment_submit_email', $email_name, $email_vars );

		$subject      = self::get_subject( $email_name, $email_vars );
		$message_body = self::get_content( $email_name, $email_vars );
		$headers      = self::get_headers( $email_name, $email_vars, '', $email_vars['from_name'] );
		$attachments  = self::get_attachments( $email_name, $email_vars );

		$plain_text = self::get_email_type() != 'html' ? true : false;
		$template   = $plain_text ? 'emails/plain/geodir-email-' . $email_name . '.php' : 'emails/geodir-email-' . $email_name . '.php';

		$content = geodir_get_template_html( $template, array(
			'email_name'    => $email_name,
			'email_vars'    => $email_vars,
			'sent_to_admin' => false,
			'plain_text'    => $plain_text,
			'message_body'  => $message_body,
		) );

		$sent = self::send( $recipient, $subject, $content, $headers, $attachments );

		if ( self::is_admin_bcc_active( $email_name ) ) {
			$recipient = self::get_admin_email();
			$subject .= ' - ADMIN BCC COPY';
			self::send( $recipient, $subject, $content, $headers, $attachments );
		}

		do_action( 'geodir_post_owner_comment_submit_email', $email_name, $email_vars );

		return $sent;
	}

	/**
	 * Send the listing owner notification when comment approved.
	 *
	 * @param $comment
	 * @param array $data
	 *
	 * @return bool
	 */
	public static function send_owner_comment_approved_email( $comment, $data = array() ) {
		$email_name = 'owner_comment_approved';

		if ( ! self::is_email_enabled( $email_name ) ) {
			return false;
		}

		$post = geodir_get_post_info( $comment->comment_post_ID );
		if ( empty( $post ) || empty( $comment ) ) {
			return;
		}

		$author_data = get_userdata( $post->post_author );
		if ( empty( $author_data ) ) {
			return false;
		}

		$recipient = ! empty( $author_data->user_email ) ? $author_data->user_email : '';

		if ( empty( $post ) || ! is_email( $recipient ) ) {
			return;
		}

		$email_vars             = $data;
		$email_vars['post']     = $post;
		$email_vars['comment']  = $comment;
		$email_vars['to_name']  = geodir_get_client_name( $post->post_author );
		$email_vars['to_email'] = $recipient;

		do_action( 'geodir_pre_owner_comment_approved_email', $email_name, $email_vars );

		$subject      = self::get_subject( $email_name, $email_vars );
		$message_body = self::get_content( $email_name, $email_vars );
		$headers      = self::get_headers( $email_name, $email_vars );
		$attachments  = self::get_attachments( $email_name, $email_vars );

		$plain_text = self::get_email_type() != 'html' ? true : false;
		$template   = $plain_text ? 'emails/plain/geodir-email-' . $email_name . '.php' : 'emails/geodir-email-' . $email_name . '.php';

		$content = geodir_get_template_html( $template, array(
			'email_name'    => $email_name,
			'email_vars'    => $email_vars,
			'sent_to_admin' => false,
			'plain_text'    => $plain_text,
			'message_body'  => $message_body,
		) );

		$sent = self::send( $recipient, $subject, $content, $headers, $attachments );

		if ( self::is_admin_bcc_active( $email_name ) ) {
			$recipient = self::get_admin_email();
			$subject .= ' - ADMIN BCC COPY';
			self::send( $recipient, $subject, $content, $headers, $attachments );
		}

		do_action( 'geodir_post_owner_comment_approved_email', $email_name, $email_vars );

		return $sent;
	}

	/**
	 * Send the comment author an email when their comment is approved.
	 *
	 * @param $comment
	 * @param array $data
	 *
	 * @return bool
	 */
	public static function send_author_comment_approved_email( $comment, $data = array() ) {
		$email_name = 'author_comment_approved';

		if ( ! self::is_email_enabled( $email_name ) ) {
			return false;
		}

		$recipient = ! empty( $comment->comment_author_email ) ? $comment->comment_author_email : '';
		$to_name   = ! empty( $comment->comment_author ) ? $comment->comment_author : __( 'Author', 'geodirectory' );

		if ( empty( $comment ) || ! is_email( $recipient ) ) {
			return;
		}

		$post = geodir_get_post_info( $comment->comment_post_ID );
		if ( empty( $post ) ) {
			return;
		}

		$email_vars             = $data;
		$email_vars['post']     = $post;
		$email_vars['comment']  = $comment;
		$email_vars['to_name']  = $to_name;
		$email_vars['to_email'] = $recipient;

		do_action( 'geodir_pre_author_comment_approved_email', $email_name, $email_vars );

		$subject      = self::get_subject( $email_name, $email_vars );
		$message_body = self::get_content( $email_name, $email_vars );
		$headers      = self::get_headers( $email_name, $email_vars );
		$attachments  = self::get_attachments( $email_name, $email_vars );

		$plain_text = self::get_email_type() != 'html' ? true : false;
		$template   = $plain_text ? 'emails/plain/geodir-email-' . $email_name . '.php' : 'emails/geodir-email-' . $email_name . '.php';

		$content = geodir_get_template_html( $template, array(
			'email_name'    => $email_name,
			'email_vars'    => $email_vars,
			'sent_to_admin' => false,
			'plain_text'    => $plain_text,
			'message_body'  => $message_body,
		) );

		$sent = self::send( $recipient, $subject, $content, $headers, $attachments );

		if ( self::is_admin_bcc_active( $email_name ) ) {
			$recipient = self::get_admin_email();
			$subject .= ' - ADMIN BCC COPY';
			self::send( $recipient, $subject, $content, $headers, $attachments );
		}

		do_action( 'geodir_post_author_comment_approved_email', $email_name, $email_vars );

		return $sent;
	}

}
