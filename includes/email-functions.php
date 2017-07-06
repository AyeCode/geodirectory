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