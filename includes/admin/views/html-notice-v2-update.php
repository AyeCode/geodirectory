<?php
/**
 * Admin View: Notice - Update
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$all_plugins = get_plugins();

$plugins = array();
$plugins[] = array(
	'directory' => 'geodirectory',
	'name' => 'GeoDirectory',
	'db_version' => get_option( 'geodirectory_db_version' ),
	'status' => true
);
if ( $db_version = get_option( 'geodiradvancesearch_db_version' ) ) {
	$plugins[] = array(
		'directory' => 'geodir_advance_search_filters',
		'name' => 'GeoDirectory Advance Search Filters',
		'db_version' => $db_version,
		'status' => is_plugin_active( 'geodir_advance_search_filters/geodir_advance_search_filters.php' )
	);
}
if ( isset( $all_plugins[ 'geodir_buddypress/geodir_buddypress.php' ] ) && $db_version = $all_plugins[ 'geodir_buddypress/geodir_buddypress.php' ]['Version'] ) {
	$plugins[] = array(
		'directory' => 'geodir_buddypress',
		'name' => 'GeoDirectory BuddyPress Integration',
		'db_version' => $db_version,
		'status' => is_plugin_active( 'geodir_buddypress/geodir_buddypress.php' )
	);
}
if ( isset( $all_plugins[ 'geodir_custom_google_maps/geodir_custom_google_maps.php' ] ) && $db_version = $all_plugins[ 'geodir_custom_google_maps/geodir_custom_google_maps.php' ]['Version'] ) {
	$plugins[] = array(
		'directory' => 'geodir_custom_google_maps',
		'name' => 'GeoDirectory Custom Google Maps',
		'db_version' => $db_version,
		'status' => is_plugin_active( 'geodir_custom_google_maps/geodir_custom_google_maps.php' )
	);
}
if ( $db_version = get_option( 'geodir_custom_posts_db_version' ) ) {
	$plugins[] = array(
		'directory' => 'geodir_custom_posts',
		'name' => 'GeoDirectory Custom Post Types',
		'db_version' => $db_version,
		'status' => is_plugin_active( 'geodir_custom_posts/geodir_custom_posts.php' )
	);
}
if ( $db_version = get_option( 'geodirevents_db_version' ) ) {
	$plugins[] = array(
		'directory' => 'geodir_event_manager',
		'name' => 'GeoDirectory Events',
		'db_version' => $db_version,
		'status' => is_plugin_active( 'geodir_event_manager/geodir_event_manager.php' )
	);
}
if ( $db_version = get_option( 'geodirlists_db_version' ) ) {
	$plugins[] = array(
		'directory' => 'geodir_list_manager',
		'name' => 'GeoDirectory Lists',
		'db_version' => $db_version,
		'status' => is_plugin_active( 'geodir_list_manager/geodir_list_manager.php' )
	);
}
if ( $db_version = get_option( 'geodirlocation_db_version' ) ) {
	$plugins[] = array(
		'directory' => 'geodir_location_manager',
		'name' => 'GeoDirectory Location Manager',
		'db_version' => $db_version,
		'status' => is_plugin_active( 'geodir_location_manager/geodir_location_manager.php' )
	);
}
if ( $db_version = get_option( 'geodir_payments_db_version' ) ) {
	$plugins[] = array(
		'directory' => 'geodir_payment_manager',
		'name' => 'GeoDirectory Payment Manager',
		'db_version' => $db_version,
		'status' => is_plugin_active( 'geodir_payment_manager/geodir_payment_manager.php' )
	);
}
if ( $db_version = get_option( 'geodir_reviewratings_db_version' ) ) {
	$plugins[] = array(
		'directory' => 'geodir_review_rating_manager',
		'name' => 'GeoDirectory Review Rating Manager',
		'db_version' => $db_version,
		'status' => is_plugin_active( 'geodir_review_rating_manager/geodir_review_rating_manager.php' )
	);
}
if ( isset( $all_plugins[ 'geodir_social_importer/geodir_social_importer.php' ] ) && $db_version = $all_plugins[ 'geodir_social_importer/geodir_social_importer.php' ]['Version'] ) {
	$plugins[] = array(
		'directory' => 'geodir_social_importer',
		'name' => 'GeoDirectory Social Importer',
		'db_version' => $db_version,
		'status' => is_plugin_active( 'geodir_social_importer/geodir_social_importer.php' )
	);
}

?>
<div id="message" class="notice-warning notice notice-alt geodir-message geodir-v1-to-v2-notice">
	<h2><?php _e( 'GeoDirectory v1 to v2 data conversion:', 'geodirectory' ); ?></h2>
	<h3><?php _e( 'You need to run the v1 to v2 data conversion now to update your directory database to the latest version to restore your directory functionality.', 'geodirectory' ); ?></h3>
	<h3><?php _e( 'Please check following requirements before v1 to v2 data conversion:' ); ?></h3>
	<p><b><?php _e( 'Note:', 'geodirectory' ); ?></b> <?php _e( 'This is a major update, so it is strongly recommended that you backup your database before start running v1 to v2 data conversion.', 'geodirectory' ); ?></p>
	<p><b><?php _e( 'Plugins  data found:', 'geodirectory' ); ?></b></p>
	<table class="widefat striped">
		<tbody>
			<tr>
				<td><b><?php _e( 'DB Version', 'geodirectory' ); ?></b></td>
				<td><b><?php _e( 'Plugin Name', 'geodirectory' ); ?></b></td>
				<td><b><?php _e( 'Plugin Directory', 'geodirectory' ); ?></b></td>
				<td><b><?php _e( 'Plugin Status', 'geodirectory' ); ?></b>
				<td><b><?php _e( 'Note', 'geodirectory' ); ?></b>
			</tr>
			<?php foreach ( $plugins as $plugin ) { ?>
			<tr>
				<td><?php echo $plugin['db_version']; ?></td>
				<td><?php echo $plugin['name']; ?></td>
				<td><?php echo $plugin['directory']; ?></td>
				<td><?php echo $plugin['status'] ? __( 'Active', 'geodirectory' ) :  __( 'Disabled', 'geodirectory' ); ?></td>
				<td>
				<?php if ( ! $plugin['status'] ) {
					echo __( 'Plugin should be active to convert plugin data.', 'geodirectory' );
				} ?>
				</td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<p class="submit"><a href="<?php echo esc_url( add_query_arg( 'do_update_geodirectory', 'true', admin_url( 'admin.php?page=gd-settings' ) ) ); ?>" class="gd-update-now button-primary"><?php _e( 'Run GeoDirectory v1 to v2 data conversion', 'geodirectory' ); ?></a></p>
</div>
<script type="text/javascript">
	jQuery( '.gd-update-now' ).click( 'click', function() {
		return window.confirm( '<?php echo esc_js( __( 'It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the GeoDirectory v1 to v2 data conversion now?', 'geodirectory' ) ); ?>' ); // jshint ignore:line
	});
</script>