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
		add_action( 'geodir_send_enquiry_email', array( __CLASS__, 'send_enquiry_email' ), 10, 2 );

		// frontend post save emails
		add_action( 'geodir_ajax_post_saved', array( __CLASS__, 'send_email_on_post_saved' ), 10, 2 );

		// Send post published email.
		add_action( 'geodir_post_published', array( __CLASS__, 'send_user_publish_post_email' ), 999, 2 );
	}

	/**
	 * Get the email logo.
     *
     * @since 2.0.0
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
	 * The default email footer text.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public static function email_header_text(){
		$header_text = self::get_email_logo();

		if ( empty( $header_text ) ) {
			$header_text = geodir_get_blogname();
		}

		return apply_filters( 'geodir_email_header_text', $header_text );
	}

	/**
	 * Get the email header template.
	 *
     * @since 2.0.0
     *
	 * @param string $email_heading
	 * @param string $email_name
	 * @param array $email_vars
	 * @param bool $plain_text
	 * @param bool $sent_to_admin
	 */
	public static function email_header( $email_heading = '', $email_name = '', $email_vars = array(), $plain_text = false, $sent_to_admin = false ) {
		if ( ! $plain_text ) {
			$header_text = self::email_header_text();
			$header_text = $header_text ? wpautop( wp_kses_post( wptexturize( $header_text ) ) ) : '';

			geodir_get_template( 'emails/geodir-email-header.php', array(
				'email_heading' => $email_heading,
				'email_name'    => $email_name,
				'email_vars'    => $email_vars,
				'plain_text'    => $plain_text,
				'header_text' 	=> $header_text,
				'sent_to_admin' => $sent_to_admin
			) );
		}
	}

	/**
	 * The default email footer text.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public static function email_footer_text(){
		$footer_text = geodir_get_option( 'email_footer_text' );

		if ( empty( $footer_text ) ) {
			$footer_text = wp_sprintf( __( '%s - Powered by GeoDirectory', 'geodirectory' ), get_bloginfo( 'name', 'display' ) );
		}

		return apply_filters( 'geodir_email_footer_text', $footer_text );
	}

	/**
	 * Get the email footer template.
     *
     * @since 2.0.0
	 *
	 * @param string $email_name
	 * @param array $email_vars
	 * @param bool $plain_text
	 * @param bool $sent_to_admin
	 */
	public static function email_footer( $email_name = '', $email_vars = array(), $plain_text = false, $sent_to_admin = false ) {
		if ( ! $plain_text ) {
			$footer_text = self::email_footer_text();
			$footer_text = $footer_text ? wpautop( wp_kses_post( wptexturize( $footer_text ) ) ) : '';

			geodir_get_template( 'emails/geodir-email-footer.php', array(
				'email_name'    => $email_name,
				'email_vars'    => $email_vars,
				'email_heading'	=> '',
				'plain_text'    => $plain_text,
				'footer_text' 	=> $footer_text,
				'sent_to_admin' => $sent_to_admin
			) );
		}
	}

	/**
	 * Get the email message wraped in the header and footer.
     *
     * @since 2.0.0
	 *
	 * @param string $message Message.
	 * @param string $email_name Optional. Email name. Default null.
	 * @param array $email_vars Optional. Email vars. Default array.
	 * @param string $email_heading Optional. Email header. Default null.
	 * @param bool $plain_text Optional. Plain text. Default false.
	 * @param bool $sent_to_admin Optional. Send to admin. Default false.
	 *
	 * @return string $message.
	 */
	public static function email_wrap_message( $message, $email_name = '', $email_vars = array(), $email_heading = '', $plain_text = false, $sent_to_admin = false ) {
		// Buffer
		ob_start();

		if ( $plain_text ) {
			echo wp_strip_all_tags( $message );
		} else {
			do_action( 'geodir_email_header', $email_heading, $email_name, $email_vars, $plain_text, $sent_to_admin );

			echo wpautop( wptexturize( $message ) );

			do_action( 'geodir_email_footer', $email_name, $email_vars, $plain_text, $sent_to_admin );
		}

		// Get contents
		$message = ob_get_clean();

		return $message;
	}

	/**
	 * Check if the email is enabled for the email type.
     *
     * @since 2.0.0
	 *
	 * @param string $email_name Email name.
	 *
	 * @return mixed|void
	 */
	public static function is_email_enabled( $email_name, $default = '' ) {
		switch ( $email_name ) {
			// TODO add some cases
			default:
				$active = geodir_get_option( 'email_' . $email_name , $default);
				$active = $active === 'yes' || $active == '1' ? true : false;
				break;
		}

		return apply_filters( 'geodir_email_is_enabled', $active, $email_name );
	}

	/**
	 * Get the email subject by type.
     *
     * @since 2.0.0
	 *
	 * @param string $email_name Optional. Email name. Default null.
	 * @param array $email_vars Optional. Email vars. Default array.
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

		// Get the default text is empty
		if(!$subject && method_exists('GeoDir_Defaults','email_' . $email_name . '_subject')){
			$method = 'email_' . $email_name . '_subject';
			$subject = GeoDir_Defaults::$method();
		}

		if ( $subject ) {
			$subject = self::replace_variables( __( $subject, 'geodirectory' ), $email_name, $email_vars );
		}

		return apply_filters( 'geodir_email_subject', $subject, $email_name, $email_vars );
	}

	/**
	 * Replace variables in the email text.
     *
     * @since 2.0.0
	 *
	 * @param string $content Content.
	 * @param string $email_name Optional. Email name. Default null.
	 * @param array $email_vars Optional. Email vars. Default array.
	 *
	 * @return mixed|void
	 */
	public static function replace_variables( $content, $email_name = '', $email_vars = array() ) {
		$site_url        = home_url();
		$blogname        = geodir_get_blogname();
		$email_from_anme = self::get_mail_from_name();
		$login_url       = geodir_login_url( false ); // 'false' to prevent adding redirect_to to login url.
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

		$gd_post = ! empty( $email_vars['post'] ) ? $email_vars['post'] : null;
		if ( empty( $gd_post ) && ! empty( $email_vars['post_id'] ) ) {
			$gd_post = geodir_get_post_info( $email_vars['post_id'] );
		}

		if ( ! empty( $gd_post ) ) {
			$post_id          = $gd_post->ID;
			$post_author_name = geodir_get_client_name( $gd_post->post_author );

			$replace_array['[#post_id#]']          = $post_id;
			$replace_array['[#post_status#]']      = $gd_post->post_status;
			$replace_array['[#post_date#]']        = $gd_post->post_date;
			$replace_array['[#posted_date#]']      = $gd_post->post_date;
			$replace_array['[#post_author_ID#]']   = $gd_post->post_author;
			$replace_array['[#post_author_name#]'] = $post_author_name;
			$replace_array['[#client_name#]']      = $post_author_name;
			$replace_array['[#listing_title#]']    = html_entity_decode( get_the_title( $post_id ), ENT_COMPAT, 'UTF-8' );
			$replace_array['[#listing_url#]']      = get_permalink( $post_id );
			$replace_array['[#listing_link#]']     = '<a href="' . esc_url( $replace_array['[#listing_url#]'] ) . '">' . $replace_array['[#listing_title#]'] . '</a>';
		}

		$comment = ! empty( $email_vars['comment'] ) ? $email_vars['comment'] : null;
		if ( empty( $comment ) && ! empty( $email_vars['comment_ID'] ) ) {
			$comment = get_comment( $email_vars['comment_ID'] );
		}
		if ( ! empty( $comment ) ) {
			$comment_ID = (int) $comment->comment_ID;

			$replace_array['[#comment_ID#]']              = $comment_ID;
			$replace_array['[#comment_author#]']          = $comment->comment_author;
			$replace_array['[#comment_author_IP#]']       = $comment->comment_author_IP;
			$replace_array['[#comment_author_email#]']    = $comment->comment_author_email;
			$replace_array['[#comment_date#]']            = $comment->comment_date;
			$replace_array['[#comment_content#]']         = wp_specialchars_decode( $comment->comment_content );
			$replace_array['[#comment_url#]']             = get_comment_link( $comment );
			$replace_array['[#comment_post_ID#]']         = (int) $comment->comment_post_ID;
			$replace_array['[#comment_post_title#]']      = html_entity_decode( get_the_title( (int) $comment->comment_post_ID ), ENT_COMPAT, 'UTF-8' );
			$replace_array['[#comment_post_url#]']        = get_permalink( (int) $comment->comment_post_ID );
			$replace_array['[#comment_post_link#]']       = '<a href="' . esc_url( $replace_array['[#comment_url#]'] ) . '">' . $replace_array['[#comment_post_title#]'] . '</a>';
			$replace_array['[#comment_approve_link#]']    = admin_url( "comment.php?action=approve&c={$comment_ID}#wpbody-content" );
			$replace_array['[#comment_trash_link#]']      = admin_url( "comment.php?action=trash&c={$comment_ID}#wpbody-content" );
			$replace_array['[#comment_spam_link#]']       = admin_url( "comment.php?action=spam&c={$comment_ID}#wpbody-content" );
			$replace_array['[#comment_moderation_link#]'] = admin_url( "edit-comments.php?comment_status=moderated#wpbody-content" );

			// Review Rating data
			if ( ! geodir_cpt_has_rating_disabled( get_post_type( (int) $comment->comment_post_ID ) ) && ( $rating = GeoDir_Comments::get_review( $comment_ID ) ) ) {
				$rating_titles = GeoDir_Comments::rating_texts();
				$rating_star = absint( $rating->rating );

				$replace_array['[#review_rating_star#]']  = $rating_star;
				$replace_array['[#review_rating_title#]'] = isset( $rating_titles[ $rating_star ] ) ? html_entity_decode( $rating_titles[ $rating_star ], ENT_COMPAT, 'UTF-8' ) : '';
				$replace_array['[#review_city#]']         = ! empty( $rating->city ) ? html_entity_decode( $rating->city, ENT_COMPAT, 'UTF-8' ) : '';
				$replace_array['[#review_region#]']       = ! empty( $rating->region ) ? html_entity_decode( $rating->region, ENT_COMPAT, 'UTF-8' ) : '';
				$replace_array['[#review_country#]']      = ! empty( $rating->country ) ? html_entity_decode( $rating->country, ENT_COMPAT, 'UTF-8' ) : '';
				$replace_array['[#review_latitude#]']     = ! empty( $rating->latitude ) ? $rating->latitude : '';
				$replace_array['[#review_longitude#]']    = ! empty( $rating->longitude ) ? $rating->longitude : '';

			} else {
				$replace_array['[#review_rating_star#]']  = '';
				$replace_array['[#review_rating_title#]'] = '';
				$replace_array['[#review_city#]']         = '';
				$replace_array['[#review_region#]']       = '';
				$replace_array['[#review_country#]']      = '';
				$replace_array['[#review_latitude#]']     = '';
				$replace_array['[#review_longitude#]']    = '';
			}
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
     * @since 2.0.0
	 *
	 * @return string Site name.
	 */
	public static function get_mail_from_name() {
		$mail_from_name = geodir_get_option( 'email_name' );

		if ( ! $mail_from_name ) {
			$mail_from_name = GeoDir_Defaults::email_name();
		}

		return apply_filters( 'geodir_get_mail_from_name', stripslashes( $mail_from_name ) );
	}

	/**
	 * Get the from address for outgoing emails.
     *
     * @since 2.0.0
	 *
	 * @return string|mixed|void The email ID.
	 */
	public static function get_mail_from() {
		$mail_from = geodir_get_option( 'email_address' );

		if ( ! $mail_from ) {
			$mail_from = GeoDir_Defaults::email_address();
		}

		return apply_filters( 'geodir_get_mail_from', $mail_from );
	}

	/**
	 * Get the site admin email.
     *
     * @since 2.0.0
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
     * @since 2.0.0
	 *
	 * @param string $email_name Optional. Email name. Default null.
	 * @param array $email_vars Optional. Email vars. Default array.
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

		// Get the default text is empty
		if(!$content && method_exists('GeoDir_Defaults','email_' . $email_name . '_body')){
			$method = 'email_' . $email_name . '_body';
			$content = GeoDir_Defaults::$method();
		}

		if ( $content ) {
			$content = self::replace_variables( __( $content, 'geodirectory' ), $email_name, $email_vars );
		}

		return apply_filters( 'geodir_email_content', $content, $email_name, $email_vars );
	}

	/**
	 * Get the email headers for sending.
     *
     * @since 2.0.0
	 *
	 * @param string $email_name Email name.
	 * @param array $email_vars Optional. Email vars. Default array.
	 * @param string $from_email Optional. From email. Default null.
	 * @param string $from_name Optional. From name. Default null.
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
     * @since 2.0.0
	 *
	 * @param string $content_type Optional. Content type. Default text/html.
	 * @param string $email_type Optional. Email type. Default null.
	 *
	 * @return string $content_type
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
     * @since 2.0.0
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
     * @since 2.0.0
	 *
	 * @param string $email_name Optional. Email name. Default null.
	 * @param array $email_vars Optional. Email vars. Default array.
	 *
	 * @return mixed|void
	 */
	public static function get_attachments( $email_name = '', $email_vars = array() ) {
		$attachments = array();

		return apply_filters( 'geodir_email_attachments', $attachments, $email_name, $email_vars );
	}

	/**
     * Send email.
     *
     * @since 2.0.0
     *
	 * @param string $to To email address.
	 * @param string $subject Email subject.
	 * @param string $message Email message.
	 * @param string $headers Email Headers.
	 * @param array $attachments Optional. Email attachments. Default array.
	 * @param string $email_name Optional. Email name. Default null.
	 * @param array $email_vars Optional. Email vars. Default null.
	 *
	 * @return bool
	 */
	public static function send( $to, $subject, $message, $headers, $attachments = array(), $email_name = '', $email_vars = array() ) {
		add_filter( 'wp_mail_from', array( __CLASS__, 'get_mail_from' ) );
		add_filter( 'wp_mail_from_name', array( __CLASS__, 'get_mail_from_name' ) );
		add_filter( 'wp_mail_content_type', array( __CLASS__, 'get_content_type' ) );

		$message = self::style_body( $message, $email_name, $email_vars );
		$message = apply_filters( 'geodir_mail_content', $message, $email_name, $email_vars, $to, $subject );

		$sent = $message ? wp_mail( $to, $subject, $message, $headers, $attachments ) : false;

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
     * @since 2.0.0
	 *
	 * @param string $content Email content.
	 * @param string $email_name Optional. Email name. Default null.
	 * @param array $email_vars Optional. Email vars. Default array.
	 *
	 * @return string $content.
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
     * @since 2.0.0
     *
	 * @param string $email_name Optional. Email name. Default null.
	 *
	 * @return mixed|void
	 */
	public static function is_admin_bcc_active( $email_name = '' ) {
		switch ( $email_name ) {
			// TODO add some cases
			default:
				$active = geodir_get_option( 'email_bcc_' . $email_name );
				$active = $active === 'yes' || $active == '1' ? true : false;
				break;
		}

		return apply_filters( 'geodir_mail_admin_bcc_active', $active, $email_name );
	}

	/**
	 * Send the user an email when their post is published.
     *
     * @since 2.0.0
	 *
	 * @param object $post Post data object.
	 * @param array $data Optional. Data array. Default array.
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

		/**
		 * Skip email send.
		 *
		 * @since 2.3.58
		 */
		$skip = apply_filters( 'geodir_skip_email_send', false, $email_name, $email_vars );

		if ( $skip === true ) {
			return;
		}

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
			'email_heading'	=> '',
			'sent_to_admin' => false,
			'plain_text'    => $plain_text,
			'message_body'  => $message_body,
		) );

		$sent = self::send( $recipient, $subject, $content, $headers, $attachments, $email_name, $email_vars );

		if ( self::is_admin_bcc_active( $email_name ) ) {
			$recipient = self::get_admin_email();
			$subject .= ' - ADMIN BCC COPY';
			self::send( $recipient, $subject, $content, $headers, $attachments, $email_name, $email_vars );
		}

		do_action( 'geodir_post_user_publish_post_email', $email_name, $email_vars );

		return $sent;
	}

	/**
	 * Send some email on post save.
     *
     * @since 2.0.0
	 *
     * @global object $gd_notified_edited Geo Directory notified edited object.
     *
	 * @param array $post_data {
     *      An array for post data.
     *      @type int @ID Post ID.
     * }
     *
	 * @param bool $update Optional. Update value. Default false.
	 */
	public static function send_email_on_post_saved( $post_data, $update = false ) {
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

		if ( $update ) {
			$user_id = (int) get_current_user_id();

			if ( $user_id > 0 && ! empty( $gd_post->post_author ) && $user_id == $gd_post->post_author && ! current_user_can( 'manage_options' ) && empty( $gd_notified_edited[ $post_ID ] ) && self::is_email_enabled( 'admin_post_edit' ) ) {
				if ( empty( $gd_notified_edited ) ) {
					$gd_notified_edited = array();
				}
				$gd_notified_edited[ $post_ID ] = true;

				self::send_admin_post_edit_email( $gd_post );
			}
		}

		// is post status not set then get the real status
		if ( ! isset( $post_data['post_status'] ) ) {
			$post_data['post_status'] = get_post_status( $post_ID );
		}

		if ( isset( $post_data['post_status'] ) && $post_data['post_status'] == 'pending' ) {
			// Send email to admin
			self::send_admin_pending_post_email( $gd_post );

			// Send email to user
			self::send_user_pending_post_email( $gd_post );
		}
	}

	/**
	 * Send the admin an email when a post is edited.
     *
     * @since 2.0.0
	 *
	 * @param object $post Post data object.
	 * @param array $data Optional. Data array. Default array.
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

		/**
		 * Skip email send.
		 *
		 * @since 2.3.58
		 */
		$skip = apply_filters( 'geodir_skip_email_send', false, $email_name, $email_vars );

		if ( $skip === true ) {
			return;
		}

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
			'email_heading'	=> '',
			'email_heading'	=> '',
			'sent_to_admin' => true,
			'plain_text'    => $plain_text,
			'message_body'  => $message_body,
		) );

		$sent = self::send( $recipient, $subject, $content, $headers, $attachments, $email_name, $email_vars );

		do_action( 'geodir_post_admin_post_edit_email', $email_name, $email_vars );

		return $sent;
	}

	/**
	 * Send the admin the post pending review email.
     *
     * @since 2.0.0
	 *
	 * @param object $post Post data object.
	 * @param array $data Optional. Data array. Default array.
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

		/**
		 * Skip email send.
		 *
		 * @since 2.3.58
		 */
		$skip = apply_filters( 'geodir_skip_email_send', false, $email_name, $email_vars );

		if ( $skip === true ) {
			return;
		}

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
			'email_heading'	=> '',
			'sent_to_admin' => true,
			'plain_text'    => $plain_text,
			'message_body'  => $message_body,
		) );

		$sent = self::send( $recipient, $subject, $content, $headers, $attachments, $email_name, $email_vars );

		do_action( 'geodir_post_admin_pending_post_email', $email_name, $email_vars );

		return $sent;
	}

	/**
	 * Send the user an email about their post pending review.
     *
     * @since 2.0.0
	 *
	 * @param object $post Post data object.
	 * @param array $data Optional. Data array. Default array.
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

		/**
		 * Skip email send.
		 *
		 * @since 2.3.58
		 */
		$skip = apply_filters( 'geodir_skip_email_send', false, $email_name, $email_vars );

		if ( $skip === true ) {
			return;
		}

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
			'email_heading'	=> '',
			'sent_to_admin' => false,
			'plain_text'    => $plain_text,
			'message_body'  => $message_body,
		) );

		$sent = self::send( $recipient, $subject, $content, $headers, $attachments, $email_name, $email_vars );

		if ( self::is_admin_bcc_active( $email_name ) ) {
			$recipient = self::get_admin_email();
			$subject .= ' - ADMIN BCC COPY';
			self::send( $recipient, $subject, $content, $headers, $attachments, $email_name, $email_vars );
		}

		do_action( 'geodir_post_user_pending_post_email', $email_name, $email_vars );

		return $sent;
	}

	/**
	 * Send the listing owner a notification about a new comment.
     *
     * @since 2.0.0
	 *
	 * @param object $comment Comment object.
	 * @param array $data Optional. Data array. Default array.
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
			return;
		}
		$comment_ID = $comment->comment_ID;

		$email_vars              = $data;
		$email_vars['post']      = $gd_post;
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

		/**
		 * Skip email send.
		 *
		 * @since 2.3.58
		 */
		$skip = apply_filters( 'geodir_skip_email_send', false, $email_name, $email_vars );

		if ( $skip === true ) {
			return;
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
			'email_heading'	=> '',
			'sent_to_admin' => false,
			'plain_text'    => $plain_text,
			'message_body'  => $message_body,
		) );

		$sent = self::send( $recipient, $subject, $content, $headers, $attachments, $email_name, $email_vars );

		if ( self::is_admin_bcc_active( $email_name ) ) {
			$recipient = self::get_admin_email();
			$subject .= ' - ADMIN BCC COPY';
			self::send( $recipient, $subject, $content, $headers, $attachments, $email_name, $email_vars );
		}

		do_action( 'geodir_post_owner_comment_submit_email', $email_name, $email_vars );

		return $sent;
	}

	/**
	 * Send the listing owner notification when comment approved.
     *
     * @since 2.0.0
	 *
	 * @param object $comment Comment object.
	 * @param array $data Optional. Data array. Default array.
	 *
	 * @return bool
	 */
	public static function send_owner_comment_approved_email( $comment, $data = array() ) {
		$email_name = 'owner_comment_approved';

		if ( ! self::is_email_enabled( $email_name ) ) {
			return false;
		}

		$gd_post = geodir_get_post_info( $comment->comment_post_ID );
		if ( empty( $gd_post ) || empty( $comment ) ) {
			return;
		}

		$author_data = get_userdata( $gd_post->post_author );
		if ( empty( $author_data ) ) {
			return false;
		}

		$recipient = ! empty( $author_data->user_email ) ? $author_data->user_email : '';

		if ( empty( $gd_post ) || ! is_email( $recipient ) ) {
			return;
		}

		$email_vars             = $data;
		$email_vars['post']     = $gd_post;
		$email_vars['comment']  = $comment;
		$email_vars['to_name']  = geodir_get_client_name( $gd_post->post_author );
		$email_vars['to_email'] = $recipient;

		/**
		 * Skip email send.
		 *
		 * @since 2.3.58
		 */
		$skip = apply_filters( 'geodir_skip_email_send', false, $email_name, $email_vars );

		if ( $skip === true ) {
			return;
		}

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
			'email_heading'	=> '',
			'sent_to_admin' => false,
			'plain_text'    => $plain_text,
			'message_body'  => $message_body,
		) );

		$sent = self::send( $recipient, $subject, $content, $headers, $attachments, $email_name, $email_vars );

		if ( self::is_admin_bcc_active( $email_name ) ) {
			$recipient = self::get_admin_email();
			$subject .= ' - ADMIN BCC COPY';
			self::send( $recipient, $subject, $content, $headers, $attachments, $email_name, $email_vars );
		}

		do_action( 'geodir_post_owner_comment_approved_email', $email_name, $email_vars );

		return $sent;
	}

	/**
	 * Send the comment author an email when their comment is approved.
     *
     * @since 2.0.0
	 *
	 * @param object $comment Comment object.
	 * @param array $data Optional. Data array. Default array.
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

		$gd_post = geodir_get_post_info( $comment->comment_post_ID );
		if ( empty( $gd_post ) ) {
			return;
		}

		$email_vars             = $data;
		$email_vars['post']     = $gd_post;
		$email_vars['comment']  = $comment;
		$email_vars['to_name']  = $to_name;
		$email_vars['to_email'] = $recipient;

		/**
		 * Skip email send.
		 *
		 * @since 2.3.58
		 */
		$skip = apply_filters( 'geodir_skip_email_send', false, $email_name, $email_vars );

		if ( $skip === true ) {
			return;
		}

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
			'email_heading'	=> '',
			'sent_to_admin' => false,
			'plain_text'    => $plain_text,
			'message_body'  => $message_body,
		) );

		$sent = self::send( $recipient, $subject, $content, $headers, $attachments, $email_name, $email_vars );

		if ( self::is_admin_bcc_active( $email_name ) ) {
			$recipient = self::get_admin_email();
			$subject .= ' - ADMIN BCC COPY';
			self::send( $recipient, $subject, $content, $headers, $attachments, $email_name, $email_vars );
		}

		do_action( 'geodir_post_author_comment_approved_email', $email_name, $email_vars );

		return $sent;
	}

}
