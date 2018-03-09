<?php
/**
 * Admin View: Notice - Install
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="message" class="updated notice-alt geodir-message">
	<p><?php _e( '<strong>Welcome to GeoDirectory</strong> &#8211; You&lsquo;re almost ready to start your directory! :)', 'geodirectory' ); ?></p>
	<p class="submit"><a href="<?php echo esc_url( admin_url( 'admin.php?page=gd-setup' ) ); ?>" class="button-primary"><?php _e( 'Run the Setup Wizard', 'geodirectory' ); ?></a> <a class="button-secondary skip" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'gd-hide-notice', 'install' ), 'geodir_hide_notices_nonce', '_gd_notice_nonce' ) ); ?>"><?php _e( 'Skip setup', 'geodirectory' ); ?></a></p>
</div>
