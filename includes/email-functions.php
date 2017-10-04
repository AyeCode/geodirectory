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
        $mail_from_name = get_option( 'blogname' );
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

function geodir_email_get_headers( $from_email = '', $from_name = '' ) {
    $from_email = !empty( $from_email ) ? $from_email : geodir_get_mail_from();
    $from_name = !empty( $from_name ) ? $from_name : geodir_get_mail_from_name();
    
    $headers    = "From: " . stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) . " <$from_email>\r\n";
    $headers    .= "Reply-To: ". $from_email . "\r\n";
    $headers    .= "Content-Type: " . geodir_mail_get_content_type() . "\r\n";
    
    return apply_filters( 'geodir_email_get_headers', $headers, $from_email, $from_name );
}

function geodir_email_get_subject( $email_type = '', $args = array() ) {
    $subject    = geodir_get_option( $email_type . '_subject' );
    
    $subject    = geodir_email_format_text( $subject );
    
    return apply_filters( 'geodir_email_get_subject', $subject, $email_type, $args );
}

function geodir_email_get_content( $email_type = '', $args = array() ) {
    $content    = geodir_get_option( $email_type . '_content' );
    
    $content    = geodir_email_format_text( $content );
    
    return apply_filters( 'wpinv_email_get_content', $content, $email_type, $args );
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
    $blogname           = get_option( 'blogname' );
    $email_from_anme    = geodir_get_mail_from_name();
    
    $search                 = array();
    $replace                = array();
    
    $search['blogname']      = '[#blogname#]';
    $search['site_name']     = '[#site_name#]';
    
    $replace['blogname']     = $blogname;
    $replace['site_name']    = $email_from_anme;
    
    return apply_filters( 'geodir_email_global_vars', array( $search, $replace ) );
}