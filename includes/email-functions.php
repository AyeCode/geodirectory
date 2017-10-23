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
        $mail_from = get_option( 'admin_email' );
    }
    
    return apply_filters( 'geodir_get_mail_from', $mail_from );
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

function geodir_mail( $to, $subject, $message, $headers, $attachments = array() ) {
    add_filter( 'wp_mail_from', 'geodir_get_mail_from' );
    add_filter( 'wp_mail_from_name', 'geodir_get_mail_from_name' );
    add_filter( 'wp_mail_content_type', 'geodir_mail_get_content_type' );

    $message = geodir_email_style_body( $message );
    $message = apply_filters( 'geodir_mail_content', $message );
    
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

function geodir_email_style_body( $content, $gd_mail_vars = array() ) {
    // make sure we only inline CSS for html emails
    if ( in_array( geodir_mail_get_content_type(), array( 'text/html', 'multipart/alternative' ) ) && class_exists( 'DOMDocument' ) ) {
        // include css inliner
        if ( ! class_exists( 'Emogrifier' ) ) {
            include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/libraries/class-emogrifier.php' );
        }
        
        ob_start();
        geodir_get_template( 'emails/geodir-email-styles.php', array( 'gd_mail_vars' => $gd_mail_vars ) );
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

function geodir_email_header( $gd_mail_vars = array() ) {
    geodir_get_template( 'emails/geodir-email-header.php', array( 'gd_mail_vars' => $gd_mail_vars ) );
}
add_action( 'geodir_email_header', 'geodir_email_header' );

function geodir_email_footer( $gd_mail_vars = array() ) {
    geodir_get_template( 'emails/geodir-email-footer.php', array( 'gd_mail_vars' => $gd_mail_vars ) );
}
add_action( 'geodir_email_footer', 'geodir_email_footer' );

function geodir_email_wrap_message( $message, $gd_mail_vars = array() ) {
    // Buffer
    ob_start();

    do_action( 'geodir_email_header', $gd_mail_vars );

    echo wpautop( wptexturize( $message ) );

    do_action( 'geodir_email_footer', $gd_mail_vars );

    // Get contents
    $message = ob_get_clean();

    return $message;
}

function geodir_email_get_headers( $from_email = '', $from_name = '' ) {
    $from_email = !empty( $from_email ) ? $from_email : geodir_get_mail_from();
    $from_name = !empty( $from_name ) ? $from_name : geodir_get_mail_from_name();
    
    $headers    = "From: " . stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) . " <$from_email>\r\n";
    $headers    .= "Reply-To: ". $from_email . "\r\n";
    $headers    .= "Content-Type: " . geodir_mail_get_content_type() . "\r\n";
    
    return apply_filters( 'geodir_email_get_headers', $headers, $from_email, $from_name );
}

function geodir_email_get_subject( $email_type = '', $gd_mail_vars = array() ) {
    $subject    = geodir_get_option( $email_type . '_subject' );
    
    $subject    = geodir_email_format_text( $subject );
    
    return apply_filters( 'geodir_email_get_subject', $subject, $email_type, $gd_mail_vars );
}

function geodir_email_get_content( $email_type = '', $gd_mail_vars = array() ) {
    $content    = geodir_get_option( $email_type . '_content' );
    
    $content    = geodir_email_format_text( $content );
    
    return apply_filters( 'geodir_email_get_content', $content, $email_type, $gd_mail_vars );
}

function geodir_email_format_text( $content ) {
    global $geodir_email_search, $geodir_email_replace;
    
    if ( empty( $geodir_email_search ) ) {
        $geodir_email_search = array();
    }
    
    if ( empty( $geodir_email_replace ) ) {
        $geodir_email_replace = array();
    }
    
    $geodir_email_search     = (array)apply_filters( 'geodir_email_format_text_search', $geodir_email_search );
    $geodir_email_replace    = (array)apply_filters( 'geodir_email_format_text_replace', $geodir_email_replace );
    
    $global_vars    = geodir_email_global_vars();
    
    $search         = array_merge( $global_vars[0], $geodir_email_search );
    $replace        = array_merge( $global_vars[1], $geodir_email_replace );
    
    if ( empty( $search ) || empty( $replace ) || !is_array( $search ) || !is_array( $replace ) ) {
        return  $content;
    }
        
    return str_replace( $search, $replace, $content );
}

function geodir_email_global_vars() {
    $blogname           = geodir_get_blogname();
    $email_from_anme    = geodir_get_mail_from_name();
    
    $search                 = array();
    $replace                = array();
    
    $search['blogname']      = '[#blogname#]';
    $search['site_name']     = '[#site_name#]';
    
    $replace['blogname']     = $blogname;
    $replace['site_name']    = $email_from_anme;
    
    return apply_filters( 'geodir_email_global_vars', array( $search, $replace ) );
}

function geodir_email_footer_text( $footer_text = '' ) {
    $footer_text = wp_sprintf( __( '<a href="%s">%s</a> - Powered by GeoDirectory', 'geodirectory' ), geodir_get_blogurl(), geodir_get_blogname() );
    return $footer_text;
}
add_filter( 'geodir_email_footer_text', 'geodir_email_footer_text', 10, 1 );