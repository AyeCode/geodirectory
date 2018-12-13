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
	'status' => true,
	'version'   => GeoDir()->version,
	'min_version'   => GeoDir()->version,
);
if ( $db_version = get_option( 'geodiradvancesearch_db_version' ) ) {
	$plugins[] = array(
		'directory' => 'geodir_advance_search_filters',
		'name' => 'GeoDirectory Advance Search Filters',
		'db_version' => $db_version,
		'min_version'   => '2.0.0.3-beta',
		'version'   => $all_plugins[ 'geodir_advance_search_filters/geodir_advance_search_filters.php' ]['Version'],
		'status' => is_plugin_active( 'geodir_advance_search_filters/geodir_advance_search_filters.php' )
	);
}
if ( isset( $all_plugins[ 'geodir_buddypress/geodir_buddypress.php' ] ) && $db_version = $all_plugins[ 'geodir_buddypress/geodir_buddypress.php' ]['Version'] ) {
	$plugins[] = array(
		'directory' => 'geodir_buddypress',
		'name' => 'GeoDirectory BuddyPress Integration',
		'db_version' => $db_version,
		'min_version'   => '2.0.0.0-beta',
		'version'   => $all_plugins[ 'geodir_buddypress/geodir_buddypress.php' ]['Version'],
		'status' => is_plugin_active( 'geodir_buddypress/geodir_buddypress.php' )
	);
}
if ( isset( $all_plugins[ 'geodir_custom_google_maps/geodir_custom_google_maps.php' ] ) && $db_version = $all_plugins[ 'geodir_custom_google_maps/geodir_custom_google_maps.php' ]['Version'] ) {
	$plugins[] = array(
		'directory' => 'geodir_custom_google_maps',
		'name' => 'GeoDirectory Custom Google Maps',
		'db_version' => $db_version,
		'min_version'   => '2.0.0.0-beta',
		'version'   => $all_plugins[ 'geodir_custom_google_maps/geodir_custom_google_maps.php' ]['Version'],
		'status' => is_plugin_active( 'geodir_custom_google_maps/geodir_custom_google_maps.php' )
	);
}
if ( $db_version = get_option( 'geodir_custom_posts_db_version' ) ) {
	$plugins[] = array(
		'directory' => 'geodir_custom_posts',
		'name' => 'GeoDirectory Custom Post Types',
		'db_version' => $db_version,
		'min_version'   => '2.0.0.7',
		'version'   => $all_plugins[ 'geodir_custom_posts/geodir_custom_posts.php' ]['Version'],
		'status' => is_plugin_active( 'geodir_custom_posts/geodir_custom_posts.php' )
	);
}
if ( $db_version = get_option( 'geodirevents_db_version' ) ) {
	$plugins[] = array(
		'directory' => 'geodir_event_manager',
		'name' => 'GeoDirectory Events',
		'db_version' => $db_version,
		'min_version'   => '2.0.0.2-rc',
		'version'   => $all_plugins[ 'geodir_event_manager/geodir_event_manager.php' ]['Version'],
		'status' => is_plugin_active( 'geodir_event_manager/geodir_event_manager.php' )
	);
}
// @todo not out yet
if ( 1==2 && $db_version = get_option( 'geodirlists_db_version' ) ) {
	$plugins[] = array(
		'directory' => 'geodir_list_manager',
		'name' => 'GeoDirectory Lists',
		'db_version' => $db_version,
		'min_version'   => '2.0.0.0-beta',
		'version'   => $all_plugins[ 'geodir_list_manager/geodir_list_manager.php' ]['Version'],
		'status' => is_plugin_active( 'geodir_list_manager/geodir_list_manager.php' )
	);
}
if ( $db_version = get_option( 'geodirlocation_db_version' ) ) {
	$plugins[] = array(
		'directory' => 'geodir_location_manager',
		'name' => 'GeoDirectory Location Manager',
		'db_version' => $db_version,
		'min_version'   => '2.0.0.10',
		'version'   => $all_plugins[ 'geodir_location_manager/geodir_location_manager.php' ]['Version'],
		'status' => is_plugin_active( 'geodir_location_manager/geodir_location_manager.php' )
	);
}
if ( $db_version = get_option( 'geodir_payments_db_version' ) ) {
	$plugins[] = array(
		'directory' => 'geodir_payment_manager',
		'name' => 'GeoDirectory Payment Manager',
		'db_version' => $db_version,
		'min_version'   => '2.5.0.3-beta',
		'version'   => $all_plugins[ 'geodir_payment_manager/geodir_payment_manager.php' ]['Version'],
		'status' => is_plugin_active( 'geodir_payment_manager/geodir_payment_manager.php' )
	);
}
if ( $db_version = get_option( 'geodir_reviewratings_db_version' ) ) {
	$plugins[] = array(
		'directory' => 'geodir_review_rating_manager',
		'name' => 'GeoDirectory Review Rating Manager',
		'db_version' => $db_version,
		'min_version'   => '2.0.0.7',
		'version'   => $all_plugins[ 'geodir_review_rating_manager/geodir_review_rating_manager.php' ]['Version'],
		'status' => is_plugin_active( 'geodir_review_rating_manager/geodir_review_rating_manager.php' )
	);
}
if ( isset( $all_plugins[ 'geodir_social_importer/geodir_social_importer.php' ] ) && $db_version = $all_plugins[ 'geodir_social_importer/geodir_social_importer.php' ]['Version'] ) {
	$plugins[] = array(
		'directory' => 'geodir_social_importer',
		'name' => 'GeoDirectory Social Importer',
		'db_version' => $db_version,
		'min_version'   => '2.0.0.0-beta',
		'version'   => $all_plugins[ 'geodir_social_importer/geodir_social_importer.php' ]['Version'],
		'status' => is_plugin_active( 'geodir_social_importer/geodir_social_importer.php' )
	);
}
if ( isset( $all_plugins[ 'geodir_franchise/geodir_franchise.php' ] ) && $db_version = $all_plugins[ 'geodir_franchise/geodir_franchise.php' ]['Version'] ) {
	$plugins[] = array(
		'directory' => 'geodir_franchise',
		'name' => 'GeoDirectory Franchise Manager',
		'db_version' => $db_version,
		'min_version'   => '2.0.0.0-dev',
		'version'   => $all_plugins[ 'geodir_franchise/geodir_franchise.php' ]['Version'],
		'status' => is_plugin_active( 'geodir_franchise/geodir_franchise.php' )
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
				<td><b><?php _e( 'Version', 'geodirectory' ); ?></b></td>
				<td><b><?php _e( 'Required Version', 'geodirectory' ); ?></b></td>
				<td><b><?php _e( 'Plugin Name', 'geodirectory' ); ?></b></td>
				<td><b><?php _e( 'Plugin Directory', 'geodirectory' ); ?></b></td>
				<td><b><?php _e( 'Plugin Status', 'geodirectory' ); ?></b>
				<td><b><?php _e( 'Note', 'geodirectory' ); ?></b>
			</tr>
			<?php
			$has_issues = false;
			foreach ( $plugins as $plugin ) {

				// version check
				$ver_css = 'green';
				$ver_icon = '';
				if(isset($plugin['version']) && version_compare($plugin['version'],$plugin['min_version'],"<")){
					$has_issues = true;
					$ver_css = 'red';
					$ver_icon = ' <i class="fas fa-exclamation-triangle" title="'.__("Requires upgrade","geodirectory").'"></i>';
				}

				// status check
				$status_text = __( 'Active', 'geodirectory' );
				$status_css = 'green';
				if($ver_icon){
					$has_issues = true;
					$status_text = __( 'Update required', 'geodirectory' );
					$status_css = 'red';
				}elseif(! $plugin['status']){
					$has_issues = true;
					$status_text = __( 'Disabled', 'geodirectory' );
					$status_css = 'red';
				}

				?>
			<tr>
				<td style="color: <?php echo esc_attr($ver_css);?>;"><?php echo $plugin['version']; echo $ver_icon;?></td>
				<td><?php echo isset($plugin['min_version']) ? $plugin['min_version']."+" : ''; ?></td>
				<td><?php echo $plugin['name']; ?></td>
				<td><?php echo $plugin['directory']; ?></td>
				<td style="color: <?php echo esc_attr($status_css);?>;"><?php echo esc_attr($status_text);?></td>
				<td>
				<?php
				if ($ver_icon && ! $plugin['status'] ) {
					echo __( 'Update and activate to convert plugin data.', 'geodirectory' );
				}elseif ( ! $plugin['status'] ) {
					echo __( 'Active to convert plugin data.', 'geodirectory' );
				}
				?>
				</td>
			</tr>
			<?php } ?>
		</tbody>
	</table>

	<?php
		if($has_issues){
			$upgrade_message = __( 'It is strongly recommended that you follow the update instructions before proceeding. Are you sure you wish to run the GeoDirectory v1 to v2 data conversion now?', 'geodirectory' );
			echo "<p><strong>".__( 'Please update the required plugins before proceeding.', 'geodirectory' )."</strong></p>";
			echo "<p style='color: red'><input type='checkbox' id='gd-let-me-upgrade' /><strong>".__( 'Let me proceed without updating (highly not recommended)', 'geodirectory' )."</strong></p>";
			$hide_upgrade_button = true;
		}else{
			$upgrade_message = __( 'It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the GeoDirectory v1 to v2 data conversion now?', 'geodirectory' );
			$hide_upgrade_button = false;
		}
	?>
	<p <?php if($hide_upgrade_button){echo "style='display:none;'";}?> class="submit gd-let-me-upgrade-submit"><a href="<?php echo esc_url( add_query_arg( 'do_update_geodirectory', 'true', admin_url( 'admin.php?page=gd-settings' ) ) ); ?>" class="gd-update-now button-primary"><?php _e( 'Run GeoDirectory v1 to v2 data conversion', 'geodirectory' ); ?></a></p>
</div>
<script type="text/javascript">
	jQuery( '.gd-update-now' ).click( 'click', function() {
		return window.confirm( '<?php echo esc_js( $upgrade_message ); ?>' ); // jshint ignore:line
	});
	jQuery( '#gd-let-me-upgrade' ).click( 'click', function() {
		jQuery(".gd-let-me-upgrade-submit").toggle();
	});
</script>