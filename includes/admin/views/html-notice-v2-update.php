<?php
/**
 * Admin View: Notice - Update
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="message" class="notice-warning notice notice-alt geodir-message geodir-v1-to-v2-notice">
	<p><h2><?php _e( 'GeoDirectory v1 to v2 data conversion:', 'geodirectory' ); ?></h2></p>
	<p><h3><?php _e( 'You need to run the v1 to v2 data conversion now to update your directory database to the latest version to restore your directory functionality.', 'geodirectory' ); ?></h3></p>
	<p><b><?php _e( 'Note:', 'geodirectory' ); ?></b> <?php _e( 'This is a major update, so it is strongly recommended that you backup your database before start running v1 to v2 data conversion.', 'geodirectory' ); ?></p>
	<p class="submit"><a href="<?php echo esc_url( add_query_arg( 'do_update_geodirectory', 'true', admin_url( 'admin.php?page=gd-settings' ) ) ); ?>" class="gd-update-now button-primary"><?php _e( 'Run GeoDirectory v1 to v2 data conversion', 'geodirectory' ); ?></a></p>
</div>
<script type="text/javascript">
	jQuery( '.gd-update-now' ).click( 'click', function() {
		return window.confirm( '<?php echo esc_js( __( 'It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the GeoDirectory v1 to v2 data conversion now?', 'geodirectory' ) ); ?>' ); // jshint ignore:line
	});
</script>