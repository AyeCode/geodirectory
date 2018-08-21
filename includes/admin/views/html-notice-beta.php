<?php
/**
 * Admin View: Notice - Beta notice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="message" class="notice-warning notice notice-alt geodir-message">
	<p><?php _e("Not all GeoDirectory addons are supported yet.") ?></p>
	<p class="submit">
		<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'gd-hide-notice', 'beta' ), 'geodir_hide_notices_nonce', '_gd_notice_nonce' ) ); ?>" class="button-secondary"><?php _e( 'Dismiss', 'geodirectory' ); ?></a>
	</p>
</div>