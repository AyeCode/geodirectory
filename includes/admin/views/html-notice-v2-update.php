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
		'path' => 'geodir_advance_search_filters/geodir_advance_search_filters.php',
		'directory' => 'geodir_advance_search_filters',
		'name' => 'GeoDirectory Advance Search Filters',
		'db_version' => $db_version,
		'min_version'   => '2.0.0.3',
		'version'   => $all_plugins[ 'geodir_advance_search_filters/geodir_advance_search_filters.php' ]['Version'],
		'status' => is_plugin_active( 'geodir_advance_search_filters/geodir_advance_search_filters.php' )
	);
}
if ( isset( $all_plugins[ 'geodir_buddypress/geodir_buddypress.php' ] ) && $db_version = $all_plugins[ 'geodir_buddypress/geodir_buddypress.php' ]['Version'] ) {
	$plugins[] = array(
		'path' => 'geodir_buddypress/geodir_buddypress.php',
		'directory' => 'geodir_buddypress',
		'name' => 'GeoDirectory BuddyPress Integration',
		'db_version' => $db_version,
		'min_version'   => '2.0.0.0',
		'version'   => $all_plugins[ 'geodir_buddypress/geodir_buddypress.php' ]['Version'],
		'status' => is_plugin_active( 'geodir_buddypress/geodir_buddypress.php' )
	);
}
if ( isset( $all_plugins[ 'geodir_custom_google_maps/geodir_custom_google_maps.php' ] ) && $db_version = $all_plugins[ 'geodir_custom_google_maps/geodir_custom_google_maps.php' ]['Version'] ) {
	$plugins[] = array(
		'path' => 'geodir_custom_google_maps/geodir_custom_google_maps.php',
		'directory' => 'geodir_custom_google_maps',
		'name' => 'GeoDirectory Custom Google Maps',
		'db_version' => $db_version,
		'min_version'   => '2.0.0.0',
		'version'   => $all_plugins[ 'geodir_custom_google_maps/geodir_custom_google_maps.php' ]['Version'],
		'status' => is_plugin_active( 'geodir_custom_google_maps/geodir_custom_google_maps.php' )
	);
}
if ( $db_version = get_option( 'geodir_custom_posts_db_version' ) ) {
	$plugins[] = array(
		'path' => 'geodir_custom_posts/geodir_custom_posts.php',
		'directory' => 'geodir_custom_posts',
		'name' => 'GeoDirectory Custom Post Types',
		'db_version' => $db_version,
		'min_version'   => '2.0.0.7',
		'version'   => $all_plugins[ 'geodir_custom_posts/geodir_custom_posts.php' ]['Version'],
		'status' => is_plugin_active( 'geodir_custom_posts/geodir_custom_posts.php' )
	);
}
if ( $db_version = get_option( 'geodirevents_db_version' ) ) {
	if ( ! empty( $all_plugins[ 'events-for-geodirectory/events-for-geodirectory.php' ] ) ) {
		$plugins[] = array(
			'path' => 'events-for-geodirectory/events-for-geodirectory.php',
			'directory' => 'events-for-geodirectory',
			'name' => 'Events for GeoDirectory',
			'db_version' => $db_version,
			'min_version' => '2.1.0.0',
			'version' => $all_plugins[ 'events-for-geodirectory/events-for-geodirectory.php' ]['Version'],
			'status' => is_plugin_active( 'events-for-geodirectory/events-for-geodirectory.php' )
		);
	} else {
		$plugins[] = array(
			'path' => 'geodir_event_manager/geodir_event_manager.php',
			'directory' => 'geodir_event_manager',
			'name' => 'GeoDirectory Events',
			'db_version' => $db_version,
			'min_version'   => '2.0.0.2',
			'version'   => $all_plugins[ 'geodir_event_manager/geodir_event_manager.php' ]['Version'],
			'status' => is_plugin_active( 'geodir_event_manager/geodir_event_manager.php' )
		);
	}
}

if ( $db_version = get_option( 'geodirlists_db_version' ) ) {
	$plugins[] = array(
		'path' => 'geodir_list_manager/geodir_list_manager.php',
		'directory' => 'geodir_list_manager',
		'name' => 'GeoDirectory Lists',
		'db_version' => $db_version,
		'min_version'   => '2.0.0.0',
		'version'   => $all_plugins[ 'geodir_list_manager/geodir_list_manager.php' ]['Version'],
		'status' => is_plugin_active( 'geodir_list_manager/geodir_list_manager.php' )
	);
}
if ( $db_version = get_option( 'geodirlocation_db_version' ) ) {
	$plugins[] = array(
		'path' => 'geodir_location_manager/geodir_location_manager.php',
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
		'path' => 'geodir_payment_manager/geodir_payment_manager.php',
		'directory' => 'geodir_payment_manager',
		'name' => 'GeoDirectory Payment Manager',
		'db_version' => $db_version,
		'min_version'   => '2.5.0.3',
		'version'   => $all_plugins[ 'geodir_payment_manager/geodir_payment_manager.php' ]['Version'],
		'status' => is_plugin_active( 'geodir_payment_manager/geodir_payment_manager.php' )
	);
}
if ( $db_version = get_option( 'geodir_reviewratings_db_version' ) ) {
	$plugins[] = array(
		'path' => 'geodir_review_rating_manager/geodir_review_rating_manager.php',
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
		'path' => 'geodir_social_importer/geodir_social_importer.php',
		'directory' => 'geodir_social_importer',
		'name' => 'GeoDirectory Social Importer',
		'db_version' => $db_version,
		'min_version'   => '2.0.0.0',
		'version'   => $all_plugins[ 'geodir_social_importer/geodir_social_importer.php' ]['Version'],
		'status' => is_plugin_active( 'geodir_social_importer/geodir_social_importer.php' )
	);
}
if ( isset( $all_plugins[ 'geodir_franchise/geodir_franchise.php' ] ) && $db_version = $all_plugins[ 'geodir_franchise/geodir_franchise.php' ]['Version'] ) {
	$plugins[] = array(
		'path' => 'geodir_franchise/geodir_franchise.php',
		'directory' => 'geodir_franchise',
		'name' => 'GeoDirectory Franchise Manager',
		'db_version' => $db_version,
		'min_version'   => '2.0.0.0',
		'version'   => $all_plugins[ 'geodir_franchise/geodir_franchise.php' ]['Version'],
		'status' => is_plugin_active( 'geodir_franchise/geodir_franchise.php' )
	);
}


$action = 'install-plugin';
$slug = 'geodirectory';
$install_url = wp_nonce_url(
	add_query_arg(
		array(
			'action' => $action,
			'plugin' => $slug,
			'geodir_downgrade' => 1
		),
		admin_url( 'update.php' )
	),
	$action.'_'.$slug
);
?>
<div class="notice notice-error" style="text-align: center">
	<h1 style="font-size: 40px;font-weight: bold;text-align: center;">
		<?php
		_e("GeoDirectory v2 Upgrade","geodirectory");
		?>
	</h1>
	<h2 style="font-size: 22px;font-weight: bold;text-align: center;color: red;">
		<?php
		_e("Immediate attention required","geodirectory");
		?>
	</h2>
	<p><strong><?php echo sprintf(__("This is a major update and may require some manual work such as adding widgets to sidebars to recreate your current layout. %sLearn more.%s","geodirectory"),"<a href='https://docs.wpgeodirectory.com/article/260-upgrading-from-gdv1-to-gdv2' target='_blank'>","</a>");?></strong></p>
	<p><?php _e("Not ready? no problem","geodirectory");?><br><strong><a onclick="return confirm('<?php _e("This will downgrade GeoDirectory to the latest version 1","geodirectory");?>');" class="button button-primary" href="<?php echo $install_url;?>" target="_parent"><i class="fas fa-undo-alt"></i> <?php _e("Downgrade to latest v1","geodirectory");?></a></strong></p>
	<p><strong><?php _e("OR","geodirectory");?></strong></p>
	<p>
		<strong><?php _e("Continue upgrade below","geodirectory");?></strong><br />
		<strong style="font-size: 30px;"><i class="fas fa-arrow-down"></i></strong>
	</p>
</div>

<div id="message" class="notice-warning notice notice-alt geodir-message geodir-v1-to-v2-notice">
	<h2><?php _e( 'GeoDirectory v1 to v2 data conversion:', 'geodirectory' ); ?>
	</h2>
	<p><a class="button button-primary" href="https://docs.wpgeodirectory.com/article/260-upgrading-from-gdv1-to-gdv2" target="_blank"><i class="fas fa-book"></i> <?php _e("Documentation","geodirectory");?></a></p>
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

					//https://ppldb.com/v2/wp-admin/update.php?action=upgrade-plugin&plugin=wp-smushit%2Fwp-smush.php&_wpnonce=101e5fc505
					$action = 'upgrade-plugin';
					$slug = !empty($plugin['path']) ? $plugin['path'] : '';
					$update_url = wp_nonce_url(
						add_query_arg(
							array(
								'action' => $action,
								'plugin' => $slug,
							),
							admin_url( 'update.php' )
						),
						$action.'_'.$slug
					);

					$lity = '';
					if ( !defined( 'WP_EASY_UPDATES_ACTIVE' ) ) {
						// if installed show activation link
						if(isset($installed_plugins['wp-easy-updates/external-updates.php'])){
							$update_url = '#gd-wpeu-required-activation';
						}else{
							$update_url = '#gd-wpeu-required-for-external';
						}
						$lity = 'data-lity=""';
					}

					echo sprintf(__( '%sUpdate%s and activate to convert plugin data.', 'geodirectory' ),"<a $lity class='button button-primary' href='$update_url'>","</a>");
				}elseif ( ! $plugin['status'] ) {
					$plugin_path = !empty($plugin['path']) ? $plugin['path'] : '';
					if($plugin_path ){
						$activation_url = wp_nonce_url(admin_url('plugins.php?action=activate&plugin=').$plugin_path, 'activate-plugin_'.$plugin_path);
					}else{
						$activation_url = '#';
					}
					echo sprintf(__( '%sActivate%s to convert plugin data.', 'geodirectory' ),"<a class='button button-primary' href='$activation_url'>","</a>");
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
	jQuery( '.gd-update-now' ).on("click", 'click', function() {
		return window.confirm( '<?php echo esc_js( $upgrade_message ); ?>' ); // jshint ignore:line
	});
	jQuery( '#gd-let-me-upgrade' ).on("click", 'click', function() {
		jQuery(".gd-let-me-upgrade-submit").toggle();
	});
</script>
<style>.lity-hide{display: none;}</style>
<div id="gd-wpeu-required-activation" class="lity-hide ">
	<span class="gd-notification "><?php printf( __("The plugin <a href='https://wpeasyupdates.com/' target='_blank'>WP Easy Updates</a> is required to check for and update some installed plugins/themes, please <a href='%s'>activate</a> it now.","geodirectory"),wp_nonce_url(admin_url('plugins.php?action=activate&plugin=wp-easy-updates/external-updates.php'), 'activate-plugin_wp-easy-updates/external-updates.php'));?><br /><?php _e("Or you can manually update the plugins with the zip files using this plugin:","geodirectory"); echo " <a href='https://wordpress.org/plugins/easy-theme-and-plugin-upgrades/' target='_blank'>Easy Theme and Plugin Upgrades</a>"; ?></span>
</div>
<div id="gd-wpeu-required-for-external" class="lity-hide "><span class="gd-notification "><?php printf(  __("The plugin <a href='https://wpeasyupdates.com/' target='_blank'>WP Easy Updates</a> is required to check for and update some installed plugins/themes, please <a href='%s' onclick='window.open(\"https://wpeasyupdates.com/wp-easy-updates.zip\", \"_blank\");' >download</a> and install it now.","geodirectory"),admin_url("plugin-install.php?tab=upload&wpeu-install=true"));?><br /><?php _e("Or you can manually update the plugins with the zip files using this plugin:","geodirectory"); echo " <a href='https://wordpress.org/plugins/easy-theme-and-plugin-upgrades/' target='_blank'>Easy Theme and Plugin Upgrades</a>";?></span></div>
