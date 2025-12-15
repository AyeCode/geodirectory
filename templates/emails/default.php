<?php
/**
 * Default Email Template
 *
 * This is the default template used for all GeoDirectory emails unless
 * a specific email type template is found in the theme or plugin.
 *
 * This template can be overridden by copying it to:
 * yourtheme/geodirectory/emails/default.php
 * or for specific email types:
 * yourtheme/geodirectory/emails/geodir-email-{email_name}.php
 *
 * @package GeoDirectory
 * @since 3.0.0
 *
 * @var string $email_heading Email heading text
 * @var string $email_name    Email type identifier
 * @var array  $email_vars    Email template variables
 * @var bool   $plain_text    Whether this is plain text email
 * @var bool   $sent_to_admin Whether sent to admin
 * @var string $message_body  The email message body content
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fires before email header output.
 *
 * @since 1.0.0
 *
 * @param string $email_heading Email heading text.
 * @param string $email_name    Email type identifier.
 * @param array  $email_vars    Email template variables.
 * @param bool   $plain_text    Whether this is plain text email.
 * @param bool   $sent_to_admin Whether sent to admin.
 */
do_action( 'geodir_email_header', $email_heading, $email_name, $email_vars, $plain_text, $sent_to_admin );

// Output the email body content
if ( ! empty( $message_body ) ) {
	echo wpautop( wptexturize( $message_body ) );
}

/**
 * Fires after email content, before footer.
 *
 * @since 1.0.0
 *
 * @param string $email_name    Email type identifier.
 * @param array  $email_vars    Email template variables.
 * @param bool   $plain_text    Whether this is plain text email.
 * @param bool   $sent_to_admin Whether sent to admin.
 */
do_action( 'geodir_email_footer', $email_name, $email_vars, $plain_text, $sent_to_admin );
