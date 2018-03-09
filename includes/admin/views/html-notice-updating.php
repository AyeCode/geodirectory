<?php
/**
 * Admin View: Notice - Updating
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="message" class="updated notice notice-alt geodir-message">
	<p><strong><?php _e( 'GeoDirectory data update', 'geodirectory' ); ?></strong> &#8211; <?php _e( 'Your database is being updated in the background.', 'geodirectory' ); ?> <a href="<?php echo esc_url( add_query_arg( 'force_update_geodirectory', 'true', admin_url( 'admin.php?page=gd-settings' ) ) ); ?>"><?php _e( 'Taking a while? Click here to run it now.', 'geodirectory' ); ?></a></p>
</div>
