<?php
/**
 * Admin View: Notice - Updated
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="message" class="updated geodirectory-message gd-connect geodirectory-message--success">
	<a class="geodirectory-message-close notice-dismiss" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'gd-hide-notice', 'update', remove_query_arg( 'do_update_geodirectory' ) ), 'geodir_hide_notices_nonce', '_gd_notice_nonce' ) ); ?>"><?php _e( 'Dismiss', 'geodirectory' ); ?></a>

	<p><?php _e( 'GeoDirectory data update complete. Thank you for updating to the latest version!', 'geodirectory' ); ?></p>
</div>
