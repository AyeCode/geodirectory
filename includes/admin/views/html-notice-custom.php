<?php
/**
 * Admin View: Custom Notices
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="message" class="notice-warning notice notice-alt geodir-message">
	<?php echo wp_kses_post( wpautop( $notice_html ) ); ?>
</div>
