<?php
/**
 * Admin View: Notice - Try AUI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="message" class="notice notice-info gd-notice-try-bootstrap">
	<p><?php _e( '<strong>GeoDirectory - </strong> Try our exciting new bootstrap styling for a more modern and clean look (switch back at any time).', 'geodirectory' ); ?></p>
	<p class=""><a href="<?php echo esc_url( admin_url("admin.php?page=gd-settings&tab=general&section=developer&try-bootstrap=true") ); ?>" class="button-primary"><?php _e( 'Try Now', 'geodirectory' ); ?></a>  <a class="button-secondary skip" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'gd-hide-notice', 'try_aui' ), 'geodir_hide_notices_nonce', '_gd_notice_nonce' ) ); ?>"><?php _e( 'Maybe Later', 'geodirectory' ); ?></a></p>
</div>