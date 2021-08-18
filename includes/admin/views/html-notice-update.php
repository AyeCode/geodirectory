<?php
/**
 * Admin View: Notice - Update
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="message" class="notice-warning notice notice-alt geodir-message">
	<p><strong><?php _e( 'GeoDirectory data update', 'geodirectory' ); ?></strong> &#8211; <?php _e( 'We need to update your directory database to the latest version.', 'geodirectory' ); ?></p>
	<p class="submit"><a href="<?php echo esc_url( add_query_arg( 'do_update_geodirectory', 'true', admin_url( 'admin.php?page=gd-settings' ) ) ); ?>" class="gd-update-now button-primary"><?php _e( 'Run the updater', 'geodirectory' ); ?></a></p>
</div>
<script type="text/javascript">
	jQuery( '.gd-update-now' ).on("click", 'click', function() {
		return window.confirm( '<?php echo esc_js( __( 'It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the updater now?', 'geodirectory' ) ); ?>' ); // jshint ignore:line
	});
</script>
