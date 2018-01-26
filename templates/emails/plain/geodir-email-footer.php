<?php
// don't load directly
if ( !defined('ABSPATH') )
    die('-1');

if ( !isset( $email_vars ) ) {
    global $email_vars;
}
$email_footer = apply_filters( 'geodir_email_footer_text', geodir_get_option( 'email_footer_text' ) );
$email_footer = $email_footer ? wp_strip_all_tags( $email_footer ) : '';

if ( $email_footer ) {
echo "\n\n=====================================================================\n\n";
	
echo $email_footer;
}