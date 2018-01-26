<?php
// don't load directly
if ( !defined('ABSPATH') )
    die('-1');

if ( !isset( $email_vars ) ) {
    global $email_vars;
}
if ( !isset( $email_heading ) ) {
    global $email_heading;
}
$email_heading = $email_heading ? wp_strip_all_tags( $email_heading ) : '';

if ( $email_heading ) {
echo "= " . $email_heading . " =";

echo "\n\n=====================================================================\n\n";
}