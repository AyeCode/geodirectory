<?php
/**
 * Email functions.
 *
 * @since 2.0.0
 * @package GeoDirectory
 */

/**
 * Get the from address for outgoing emails.
 *
 * @since 2.0.0
 * @package GeoDirectory
 * @return string|mixed|void The email ID.
 */
function geodir_get_mail_from() {
    $mail_from = geodir_get_option( 'site_email' );
    
    if ( !$mail_from ) {
        $mail_from = geodir_get_admin_email();
    }
    
    return apply_filters( 'geodir_get_mail_from', $mail_from );
}

function geodir_get_admin_email() {
    $admin_email = get_option( 'admin_email' );
    return apply_filters( 'geodir_admin_email', $admin_email );
}

/**
 * Get the from name for outgoing emails.
 *
 * @since 2.0.0
 * @package GeoDirectory
 * @return string Site name.
 */
function geodir_get_mail_from_name() {
    $mail_from_name = geodir_get_option( 'site_email_name' );
    
    if ( !$mail_from_name ) {
        $mail_from_name = geodir_get_blogname();
    }
    
    return apply_filters( 'geodir_get_mail_from_name', stripslashes( $mail_from_name ) );
}

function geodir_mail_get_content_type(  $content_type = 'text/html', $email_type = 'html' ) {
    $email_type = apply_filters( 'geodir_mail_get_content_type', $email_type );
    
    switch ( $email_type ) {
        case 'html' :
            $content_type = 'text/html';
            break;
        case 'multipart' :
            $content_type = 'multipart/alternative';
            break;
        default :
            $content_type = 'text/plain';
            break;
    }
    
    return $content_type;
}

function geodir_mail_admin_bcc_active( $email_type = '' ) {
    switch ( $email_type ) {
		// TODO add some cases
		default:
			$active = geodir_get_option( 'email_bcc_' . $email_type );
		break;
	}//geodir_error_log( $active, 'geodir_mail_admin_bcc_active( email_bcc_' . $email_type . ' )', __FILE__, __LINE__ );

    return apply_filters( 'geodir_mail_admin_bcc_active', $active, $email_type );
}

function geodir_email_is_enabled( $email_type ) {
    switch ( $email_type ) {
		// TODO add some cases
		default:
			$active = geodir_get_option( 'email_' . $email_type );
		break;
	}

    return apply_filters( 'geodir_email_is_enabled', $active, $email_type );
}

function geodir_mail( $to, $subject, $message, $headers, $attachments = array(), $email_type = '', $email_vars = array() ) {
    add_filter( 'wp_mail_from', 'geodir_get_mail_from' );
    add_filter( 'wp_mail_from_name', 'geodir_get_mail_from_name' );
    add_filter( 'wp_mail_content_type', 'geodir_mail_get_content_type' );

    $message = geodir_email_style_body( $message, $email_type, $email_vars );
    $message = apply_filters( 'geodir_mail_content', $message, $email_type, $email_vars );
    
    $sent = wp_mail( $to, $subject, $message, $headers, $attachments );
    
    if ( !$sent ) {
        $log_message = wp_sprintf( __( "\nTime: %s\nTo: %s\nSubject: %s\n", 'geodirectory' ), date_i18n( 'F j Y H:i:s', current_time( 'timestamp' ) ), ( is_array( $to ) ? implode( ', ', $to ) : $to ), $subject );
        geodir_error_log( $log_message, __( "Email from GeoDirectory plugin failed to send", 'geodirectory' ), __FILE__, __LINE__ );
    }

    remove_filter( 'wp_mail_from', 'geodir_get_mail_from' );
    remove_filter( 'wp_mail_from_name', 'geodir_get_mail_from_name' );
    remove_filter( 'wp_mail_content_type', 'geodir_mail_get_content_type' );

    return $sent;
}

function geodir_email_style_body( $content, $email_type = '', $email_vars = array() ) {
    // make sure we only inline CSS for html emails
    if ( in_array( geodir_mail_get_content_type(), array( 'text/html', 'multipart/alternative' ) ) && class_exists( 'DOMDocument' ) ) {
        // include css inliner
        if ( ! class_exists( 'Emogrifier' ) ) {
            include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/libraries/class-emogrifier.php' );
        }
        
        ob_start();
        geodir_get_template( 'emails/geodir-email-styles.php', array( 'email_type' => $email_type, 'email_vars' => $email_vars ) );
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

function geodir_email_header( $email_heading = '', $email_type = '', $email_vars = array(), $sent_to_admin = false ) {
    geodir_get_template( 'emails/geodir-email-header.php', array( 'email_heading' => $email_heading, 'email_type' => $email_type, 'email_vars' => $email_vars, 'sent_to_admin' => $sent_to_admin ) );
}
add_action( 'geodir_email_header', 'geodir_email_header' );

function geodir_email_footer( $email_type = '', $email_vars = array(), $sent_to_admin = false ) {
    geodir_get_template( 'emails/geodir-email-footer.php', array( 'email_type' => $email_type, 'email_vars' => $email_vars, 'sent_to_admin' => $sent_to_admin ) );
}
add_action( 'geodir_email_footer', 'geodir_email_footer' );

function geodir_email_wrap_message( $message, $email_type = '', $email_vars = array(), $email_heading = '', $sent_to_admin = false ) {
	// Buffer
    ob_start();

    do_action( 'geodir_email_header', $email_heading, $email_type, $email_vars, $sent_to_admin );

    echo wpautop( wptexturize( $message ) );

    do_action( 'geodir_email_footer', $email_type, $email_vars, $sent_to_admin );

    // Get contents
    $message = ob_get_clean();

    return $message;
}

function geodir_email_get_headers( $email_type, $email_vars = array(), $from_email = '', $from_name = '' ) {
    $from_email = !empty( $from_email ) ? $from_email : geodir_get_mail_from();
    $from_name = !empty( $from_name ) ? $from_name : geodir_get_mail_from_name();
    
    $headers    = "From: " . stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) . " <$from_email>\r\n";
    $headers    .= "Reply-To: ". $from_email . "\r\n";
    $headers    .= "Content-Type: " . geodir_mail_get_content_type() . "\r\n";
    
    return apply_filters( 'geodir_email_headers', $headers, $email_type, $email_type, $email_vars, $from_email, $from_name );
}

function geodir_email_get_subject( $email_type = '', $email_vars = array() ) {//geodir_error_log( $email_type, 'geodir_email_get_subject()', __FILE__, __LINE__ );
    switch ( $email_type ) {
		// TODO some custom options
		default:
			$subject	= geodir_get_option( 'email_' . $email_type . '_subject' );
		break;
	}

    $subject = geodir_email_format_text( __( $subject, 'geodirectory' ), $email_type, $email_vars );

    return apply_filters( 'geodir_email_subject', $subject, $email_type, $email_vars );
}

function geodir_email_get_heading( $email_type = '', $email_vars = array() ) {
	switch ( $email_type ) {
		// TODO some custom options
		default:
			$email_heading	= geodir_get_option( 'email_' . $email_type . '_heading' );
		break;
	}

    $email_heading = 'Website Enquiry';geodir_email_format_text( __( $email_heading, 'geodirectory' ), $email_type, $email_vars );

    return apply_filters( 'geodir_email_heading', $email_heading, $email_type, $email_vars );
}

function geodir_email_get_content( $email_type = '', $email_vars = array() ) {
    switch ( $email_type ) {
		// TODO some custom options
		default:
			$content	= geodir_get_option( 'email_' . $email_type . '_body' );
		break;
	}

    $content = geodir_email_format_text( __( $content, 'geodirectory' ), $email_type, $email_vars );

    return apply_filters( 'geodir_email_content', $content, $email_type, $email_vars );
}

function geodir_email_get_attachments( $email_type = '', $email_vars = array() ) {
    $attachments = array();
    
    return apply_filters( 'geodir_email_attachments', $attachments, $email_type, $email_vars );
}

function geodir_email_format_text( $content, $email_type = '', $email_vars = array() ) {
    $site_url       	= home_url();
	$blogname           = geodir_get_blogname();
    $email_from_anme    = geodir_get_mail_from_name();
	$login_url			= geodir_login_url();
	$timestamp			= current_time( 'timestamp' );
	$date				= date_i18n( get_option( 'date_format' ), $timestamp );
	$time				= date_i18n( get_option( 'time_format' ), $timestamp );
	$date_time			= $date . ' ' . $time;
	
	$replace_array = array(
        '[#blogname#]'      => $blogname,
		'[#site_url#]' 		=> $site_url,
		'[#site_name_url#]' => '<a href="' . esc_url( $site_url ) . '">' . $site_url . '</a>',
		'[#site_link#]' 	=> '<a href="' . esc_url( $site_url ) . '">' . $blogname . '</a>',
		'[#site_name#]'     => $email_from_anme,
		'[#login_url#]'  	=> $login_url,
		'[#login_link#]'  	=> '<a href="' . esc_url( $login_url ) . '">' . __( 'Login', 'geodirectory' ) . '</a>',
        '[#current_date#]'  => date_i18n( 'Y-m-d H:i:s', $timestamp ),
		'[#date#]'  		=> $date,
		'[#time#]'  		=> $time,
		'[#date_time#]'  	=> $date_time,
    );
	
	foreach ( $email_vars as $key => $value ) {
		if ( is_scalar( $value ) ) {
			$replace_array['[#' . $key . '#]'] = $value;
		}
	}
	
	$post = ! empty( $email_vars['post'] ) ? $email_vars['post'] : NULL;
	if ( empty( $post ) && ! empty( $email_vars['post_id'] ) ) {
		$post = geodir_get_post_info( $email_vars['post_id'] );
	}

	if ( ! empty( $post ) ) {
		$post_id = $email_vars['post_id'];

		$replace_array['[#post_id#]'] = $post_id;
		$replace_array['[#listing_title#]'] = get_the_title( $post_id );
		$replace_array['[#listing_url#]'] = get_permalink( $post_id );
		$replace_array['[#listing_link#]'] = '<a href="' . esc_url( $replace_array['[#listing_url#]'] ) . '">' . $replace_array['[#listing_title#]'] . '</a>';
	}

    $replace_array = apply_filters( 'geodir_email_wild_cards', $replace_array, $content, $email_type, $email_vars );

    foreach ( $replace_array as $key => $value ) {
        $content = str_replace( $key, $value, $content );
    }

    return apply_filters( 'geodir_email_content_replace_vars', $content, $email_type, $email_vars );
}

function geodir_send_to_friend_email( $post, $data ) {//geodir_error_log( $data, 'geodir_send_to_friend_email()', __FILE__, __LINE__ );
    $email_type = 'send_friend';

    if ( ! geodir_email_is_enabled( $email_type ) ) {
        return false;
    }
	
	$defaults = array(
		'from_name' => '',
		'from_email' => '',
		'to_name' => '',
		'to_email' => '',
		'subject' => '',
		'comments' => ''
	);
	$email_vars = wp_parse_args( $data, $defaults );
	$email_vars['post'] = $post;

	if ( empty( $post ) || ! is_email( $email_vars['to_email'] ) ) {
        return;
    }

	$recipient = $email_vars['to_email'];
	
	do_action( 'geodir_pre_send_to_friend_email', $email_type, $email_vars );//geodir_error_log( $email_vars, 'email_vars', __FILE__, __LINE__ );

    $subject        = geodir_email_get_subject( $email_type, $email_vars );//geodir_error_log( $subject, 'subject', __FILE__, __LINE__ );
    $email_heading  = geodir_email_get_heading( $email_type, $email_vars );//geodir_error_log( $email_heading, 'email_heading', __FILE__, __LINE__ );
    $message_body   = geodir_email_get_content( $email_type, $email_vars );//geodir_error_log( $message_body, 'message_body', __FILE__, __LINE__ );
	$headers        = geodir_email_get_headers( $email_type, $email_vars, $email_vars['from_email'], $email_vars['from_name'] );//geodir_error_log( $headers, 'headers', __FILE__, __LINE__ );
    $attachments    = geodir_email_get_attachments( $email_type, $email_vars );

    $content        = geodir_get_template_html( 'emails/geodir-email-' . $email_type . '.php', array(
			'email_type'    => $email_type,
			'email_vars'  	=> $email_vars,
            'email_heading' => $email_heading,
            'sent_to_admin' => true,
            'plain_text'    => false,
            'message_body'  => $message_body,
        ) );

    $sent = geodir_mail( $recipient, $subject, $content, $headers, $attachments );//geodir_error_log( $sent, 'sent', __FILE__, __LINE__ );

	if ( geodir_mail_admin_bcc_active( $email_type ) ) {
        $recipient  = geodir_get_admin_email();
        $subject    .= ' - ADMIN BCC COPY';
        geodir_mail( $recipient, $subject, $content, $headers, $attachments );
    }

    do_action( 'geodir_post_send_to_friend_email', $email_type, $email_vars );

    return $sent;
}
add_action( 'geodir_send_to_friend_email', 'geodir_send_to_friend_email', 10, 2 );

function geodir_send_enquiry_email( $post, $data ) {//geodir_error_log( $data, 'geodir_send_enquiry_email()', __FILE__, __LINE__ );
    $email_type = 'send_enquiry';

    if ( ! geodir_email_is_enabled( $email_type ) ) {
        return false;
    }
	
	$defaults = array(
		'from_name' => '',
		'from_email' => '',
		'phone' => '',
		'comments' => ''
	);
	$email_vars = wp_parse_args( $data, $defaults );
	$email_vars['post'] = $post;
	
	$email_vars['to_name'] = geodir_get_client_name( $post->post_author );
	$email_vars['to_email'] = geodir_get_post_meta( $post->ID, 'geodir_email', true );
	if ( empty( $email_vars['to_email'] ) ) {
		$author_data = get_userdata( $post->post_author );
		$email_vars['to_email'] = !empty( $author_data->user_email ) ? $author_data->user_email : '';
	}

	if ( empty( $post ) || ! is_email( $email_vars['to_email'] ) ) {
        return;
    }

	$recipient = $email_vars['to_email'];
	
	do_action( 'geodir_pre_send_enquiry_email', $email_type, $email_vars );//geodir_error_log( $email_vars, 'email_vars', __FILE__, __LINE__ );

    $subject        = geodir_email_get_subject( $email_type, $email_vars );//geodir_error_log( $subject, 'subject', __FILE__, __LINE__ );
    $email_heading  = geodir_email_get_heading( $email_type, $email_vars );//geodir_error_log( $email_heading, 'email_heading', __FILE__, __LINE__ );
    $message_body   = geodir_email_get_content( $email_type, $email_vars );//geodir_error_log( $message_body, 'message_body', __FILE__, __LINE__ );
	$headers        = geodir_email_get_headers( $email_type, $email_vars, $email_vars['from_email'], $email_vars['from_name'] );//geodir_error_log( $headers, 'headers', __FILE__, __LINE__ );
    $attachments    = geodir_email_get_attachments( $email_type, $email_vars );

    $content        = geodir_get_template_html( 'emails/geodir-email-' . $email_type . '.php', array(
			'email_type'    => $email_type,
			'email_vars'  	=> $email_vars,
            'email_heading' => $email_heading,
            'sent_to_admin' => false,
            'plain_text'    => false,
            'message_body'  => $message_body,
        ) );

    $sent = geodir_mail( $recipient, $subject, $content, $headers, $attachments );//geodir_error_log( $sent, 'sent', __FILE__, __LINE__ );

	if ( geodir_mail_admin_bcc_active( $email_type ) ) {
        $recipient  = geodir_get_admin_email();
        $subject    .= ' - ADMIN BCC COPY';
        geodir_mail( $recipient, $subject, $content, $headers, $attachments );//geodir_error_log( $subject, 'bcc', __FILE__, __LINE__ );
    }

    do_action( 'geodir_post_send_enquiry_email', $email_type, $email_vars );

    return $sent;
}
add_action( 'geodir_send_enquiry_email', 'geodir_send_enquiry_email', 10, 2 );

function geodir_send_email_on_listing_submit( $valid, $post_id, $request_info, $user_ID ) {geodir_error_log( $post_id, 'geodir_send_email_on_listing_submit()', __FILE__, __LINE__ );
	geodir_error_log( $post_id, 'post_id', __FILE__, __LINE__ );
	geodir_error_log( $request_info, 'request_info', __FILE__, __LINE__ );
	geodir_error_log( $user_ID, 'user_ID', __FILE__, __LINE__ );
	if ( ! $valid ) {
		return;
	}
	
	$post = geodir_get_post_info( $post_id );
	if ( empty( $post ) ) {
		return;
	}
	
	if ( $post->post_status == 'pending' ) {
		// Send email to admin
		geodir_send_admin_pending_listing_email( $post );
		
		// Send email to usre
		geodir_send_user_pending_listing_email( $post );
		
	} else if ( $post->post_status == 'publish' ) {
		// Send email to usre
		geodir_send_user_publish_listing_email( $post );
	}
}
add_action( 'geodir_send_email_on_listing_submit', 'geodir_send_email_on_listing_submit', 10, 4 );

function geodir_send_admin_pending_listing_email( $post, $data = array() ) {geodir_error_log( $data, 'geodir_send_admin_pending_listing_email()', __FILE__, __LINE__ );
    $email_type = 'admin_pending_listing';

    if ( ! geodir_email_is_enabled( $email_type ) ) {
        return false;
    }
	
	$author_data = get_userdata( $post->post_author );
	if ( empty( $author_data ) ) {
        return false;
    }
	
	$recipient = !empty( $author_data->user_email ) ? $author_data->user_email : '';

	if ( empty( $post ) || ! is_email( $recipient ) ) {
        return;
    }
	
	$email_vars = $data;
	$email_vars['post'] = $post;
	$email_vars['to_name'] = geodir_get_client_name( $post->post_author );
	$email_vars['to_email'] = $recipient;
	
	do_action( 'geodir_pre_admin_pending_listing_email', $email_type, $email_vars );//geodir_error_log( $email_vars, 'email_vars', __FILE__, __LINE__ );

    $subject        = geodir_email_get_subject( $email_type, $email_vars );//geodir_error_log( $subject, 'subject', __FILE__, __LINE__ );
    $email_heading  = geodir_email_get_heading( $email_type, $email_vars );//geodir_error_log( $email_heading, 'email_heading', __FILE__, __LINE__ );
    $message_body   = geodir_email_get_content( $email_type, $email_vars );//geodir_error_log( $message_body, 'message_body', __FILE__, __LINE__ );
	$headers        = geodir_email_get_headers( $email_type, $email_vars );//geodir_error_log( $headers, 'headers', __FILE__, __LINE__ );
    $attachments    = geodir_email_get_attachments( $email_type, $email_vars );

    $content        = geodir_get_template_html( 'emails/geodir-email-' . $email_type . '.php', array(
			'email_type'    => $email_type,
			'email_vars'  	=> $email_vars,
            'email_heading' => $email_heading,
            'sent_to_admin' => true,
            'plain_text'    => false,
            'message_body'  => $message_body,
        ) );

    $sent = geodir_mail( $recipient, $subject, $content, $headers, $attachments );//geodir_error_log( $sent, 'sent', __FILE__, __LINE__ );

    do_action( 'geodir_post_admin_pending_listing_email', $email_type, $email_vars );

    return $sent;
}

function geodir_send_user_pending_listing_email( $post, $data = array() ) {geodir_error_log( $data, 'geodir_send_user_pending_listing_email()', __FILE__, __LINE__ );
    $email_type = 'user_pending_listing';

    if ( ! geodir_email_is_enabled( $email_type ) ) {
        return false;
    }
	
	$recipient = geodir_get_admin_email();

	if ( empty( $post ) || ! is_email( $recipient ) ) {
        return;
    }
	
	$email_vars = $data;
	$email_vars['post'] = $post;
	$email_vars['to_email'] = geodir_get_admin_email();
	
	do_action( 'geodir_pre_user_pending_listing_email', $email_type, $email_vars );//geodir_error_log( $email_vars, 'email_vars', __FILE__, __LINE__ );

    $subject        = geodir_email_get_subject( $email_type, $email_vars );//geodir_error_log( $subject, 'subject', __FILE__, __LINE__ );
    $email_heading  = geodir_email_get_heading( $email_type, $email_vars );//geodir_error_log( $email_heading, 'email_heading', __FILE__, __LINE__ );
    $message_body   = geodir_email_get_content( $email_type, $email_vars );//geodir_error_log( $message_body, 'message_body', __FILE__, __LINE__ );
	$headers        = geodir_email_get_headers( $email_type, $email_vars );//geodir_error_log( $headers, 'headers', __FILE__, __LINE__ );
    $attachments    = geodir_email_get_attachments( $email_type, $email_vars );

    $content        = geodir_get_template_html( 'emails/geodir-email-' . $email_type . '.php', array(
			'email_type'    => $email_type,
			'email_vars'  	=> $email_vars,
            'email_heading' => $email_heading,
            'sent_to_admin' => false,
            'plain_text'    => false,
            'message_body'  => $message_body,
        ) );

    $sent = geodir_mail( $recipient, $subject, $content, $headers, $attachments );//geodir_error_log( $sent, 'sent', __FILE__, __LINE__ );
	
	if ( geodir_mail_admin_bcc_active( $email_type ) ) {
        $recipient  = geodir_get_admin_email();
        $subject    .= ' - ADMIN BCC COPY';
        geodir_mail( $recipient, $subject, $content, $headers, $attachments );//geodir_error_log( $subject, 'bcc', __FILE__, __LINE__ );
    }

    do_action( 'geodir_post_user_pending_listing_email', $email_type, $email_vars );

    return $sent;
}

function geodir_send_user_publish_listing_email( $post, $data = array() ) {geodir_error_log( $data, 'geodir_send_user_publish_listing_email()', __FILE__, __LINE__ );
    $email_type = 'user_publish_listing';

    if ( ! geodir_email_is_enabled( $email_type ) ) {
        return false;
    }
	
	$recipient = geodir_get_admin_email();

	if ( empty( $post ) || ! is_email( $recipient ) ) {
        return;
    }
	
	$email_vars = $data;
	$email_vars['post'] = $post;
	$email_vars['to_email'] = geodir_get_admin_email();
	
	do_action( 'geodir_pre_user_publish_listing_email', $email_type, $email_vars );//geodir_error_log( $email_vars, 'email_vars', __FILE__, __LINE__ );

    $subject        = geodir_email_get_subject( $email_type, $email_vars );//geodir_error_log( $subject, 'subject', __FILE__, __LINE__ );
    $email_heading  = geodir_email_get_heading( $email_type, $email_vars );//geodir_error_log( $email_heading, 'email_heading', __FILE__, __LINE__ );
    $message_body   = geodir_email_get_content( $email_type, $email_vars );//geodir_error_log( $message_body, 'message_body', __FILE__, __LINE__ );
	$headers        = geodir_email_get_headers( $email_type, $email_vars );//geodir_error_log( $headers, 'headers', __FILE__, __LINE__ );
    $attachments    = geodir_email_get_attachments( $email_type, $email_vars );

    $content        = geodir_get_template_html( 'emails/geodir-email-' . $email_type . '.php', array(
			'email_type'    => $email_type,
			'email_vars'  	=> $email_vars,
            'email_heading' => $email_heading,
            'sent_to_admin' => false,
            'plain_text'    => false,
            'message_body'  => $message_body,
        ) );

    $sent = geodir_mail( $recipient, $subject, $content, $headers, $attachments );//geodir_error_log( $sent, 'sent', __FILE__, __LINE__ );
	
	if ( geodir_mail_admin_bcc_active( $email_type ) ) {
        $recipient  = geodir_get_admin_email();
        $subject    .= ' - ADMIN BCC COPY';
        geodir_mail( $recipient, $subject, $content, $headers, $attachments );//geodir_error_log( $subject, 'bcc', __FILE__, __LINE__ );
    }

    do_action( 'geodir_post_user_publish_listing_email', $email_type, $email_vars );

    return $sent;
}