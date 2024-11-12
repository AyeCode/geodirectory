<?php
/**
 * Admin View: Page - Status Report.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

if ( ! class_exists( 'GeoDir_REST_System_Status_Controller', false ) ) {
	wp_die( 'Cannot load the REST API to access GeoDir_REST_System_Status_Controller.' );
}

$system_status    = new GeoDir_REST_System_Status_Controller;
$environment      = $system_status->get_environment_info();
$database         = $system_status->get_database_info();
$post_type_counts = $system_status->get_post_type_counts();
$active_plugins   = $system_status->get_active_plugins();
$theme            = $system_status->get_theme_info();
$security         = $system_status->get_security_info();
$settings         = $system_status->get_settings();
$pages            = $system_status->get_pages();
?>
<div class="notice alert alert-primary">
	<p><?php _e( 'Please copy and paste this information in your ticket when contacting support:', 'geodirectory' ); ?> </p>
	<p class="submit"><a href="#" class="button-primary debug-report btn btn-primary btn-sm text-white text-decoration-none"><?php _e( 'Get system report', 'geodirectory' ); ?></a></p>
	<div id="debug-report">
		<textarea readonly="readonly"></textarea>
		<p class="submit"><button id="copy-for-support" class="btn btn-primary btn-sm" href="#" data-tip="<?php esc_attr_e( 'Copied!', 'geodirectory' ); ?>"><?php _e( 'Select all & copy for support', 'geodirectory' ); ?></button></p>
		<p class="copy-error gd-hidden"><?php _e( 'Copying to clipboard failed. Please press Ctrl/Cmd+C to copy.', 'geodirectory' ); ?></p>
	</div>
</div>
<table class="gd_status_table widefat" cellspacing="0" id="status">
	<thead>
		<tr>
			<th colspan="3" data-export-label="WordPress Environment"><h2><?php _e( 'WordPress environment', 'geodirectory' ); ?></h2></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td data-export-label="Home URL"><?php _e( 'Home URL', 'geodirectory' ); ?>:</td>
			<td class="help"><?php echo geodir_help_tip( __( 'The homepage URL of your site.', 'geodirectory' ) ); ?></td>
			<td><?php echo esc_html( $environment['home_url'] ) ?></td>
		</tr>
		<tr>
			<td data-export-label="Site URL"><?php _e( 'Site URL', 'geodirectory' ); ?>:</td>
			<td class="help"><?php echo geodir_help_tip( __( 'The root URL of your site.', 'geodirectory' ) ); ?></td>
			<td><?php echo esc_html( $environment['site_url'] ) ?></td>
		</tr>
		<tr>
			<td data-export-label="GD Version"><?php _e( 'GD version', 'geodirectory' ); ?>:</td>
			<td class="help"><?php echo geodir_help_tip( __( 'The version of GeoDirectory installed on your site.', 'geodirectory' ) ); ?></td>
			<td><?php echo esc_html( $environment['version'] ) ?></td>
		</tr>
		<tr>
			<td data-export-label="WP Version"><?php _e( 'WP version', 'geodirectory' ); ?>:</td>
			<td class="help"><?php echo geodir_help_tip( __( 'The version of WordPress installed on your site.', 'geodirectory' ) ); ?></td>
			<td><?php echo esc_html( $environment['wp_version'] ) ?></td>
		</tr>
		<tr>
			<td data-export-label="WP Multisite"><?php _e( 'WP multisite', 'geodirectory' ); ?>:</td>
			<td class="help"><?php echo geodir_help_tip( __( 'Whether or not you have WordPress Multisite enabled.', 'geodirectory' ) ); ?></td>
			<td><?php echo ( $environment['wp_multisite'] ) ? '<span class="dashicons dashicons-yes"></span>' : '&ndash;'; ?></td>
		</tr>
		<tr>
			<td data-export-label="WP Memory Limit"><?php _e( 'WP memory limit', 'geodirectory' ); ?>:</td>
			<td class="help"><?php echo geodir_help_tip( __( 'The maximum amount of memory (RAM) that your site can use at one time.', 'geodirectory' ) ); ?></td>
			<td><?php
				if ( $environment['wp_memory_limit'] < 67108864 ) {
					echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( '%1$s - We recommend setting memory to at least 64MB. See: %2$s', 'geodirectory' ), size_format( $environment['wp_memory_limit'] ), '<a href="https://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP" target="_blank">' . __( 'Increasing memory allocated to PHP', 'geodirectory' ) . '</a>' ) . '</mark>';
				} else {
					echo '<mark class="yes">' . size_format( $environment['wp_memory_limit'] ) . '</mark>';
				}
			?></td>
		</tr>
		<tr>
			<td data-export-label="WP Debug Mode"><?php _e( 'WP debug mode', 'geodirectory' ); ?>:</td>
			<td class="help"><?php echo geodir_help_tip( __( 'Displays whether or not WordPress is in Debug Mode.', 'geodirectory' ) ); ?></td>
			<td>
				<?php if ( $environment['wp_debug_mode'] ) : ?>
					<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>
				<?php else : ?>
					<mark class="no">&ndash;</mark>
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<td data-export-label="WP Cron"><?php _e( 'WP cron', 'geodirectory' ); ?>:</td>
			<td class="help"><?php echo geodir_help_tip( __( 'Displays whether or not WP Cron Jobs are enabled.', 'geodirectory' ) ); ?></td>
			<td>
				<?php if ( $environment['wp_cron'] ) : ?>
					<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>
				<?php else : ?>
					<mark class="no">&ndash;</mark>
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<td data-export-label="Language"><?php _e( 'Language', 'geodirectory' ); ?>:</td>
			<td class="help"><?php echo geodir_help_tip( __( 'The current language used by WordPress. Default = English', 'geodirectory' ) ); ?></td>
			<td><?php echo esc_html( $environment['language'] ) ?></td>
		</tr>
	</tbody>
</table>
<table class="gd_status_table widefat" cellspacing="0">
	<thead>
		<tr>
			<th colspan="3" data-export-label="Server Environment"><h2><?php _e( 'Server environment', 'geodirectory' ); ?></h2></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td data-export-label="Server Info"><?php _e( 'Server info', 'geodirectory' ); ?>:</td>
			<td class="help"><?php echo geodir_help_tip( __( 'Information about the web server that is currently hosting your site.', 'geodirectory' ) ); ?></td>
			<td><?php echo esc_html( $environment['server_info'] ); ?></td>
		</tr>
		<tr>
			<td data-export-label="PHP Version"><?php _e( 'PHP version', 'geodirectory' ); ?>:</td>
			<td class="help"><?php echo geodir_help_tip( __( 'The version of PHP installed on your hosting server.', 'geodirectory' ) ); ?></td>
			<td><?php
				if ( version_compare( $environment['php_version'], '5.6', '<' ) ) {
					echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( '%1$s - We recommend a minimum PHP version of 5.6.', 'geodirectory' ), esc_html( $environment['php_version'] ) ) . '</mark>';
				} else {
					echo '<mark class="yes">' . esc_html( $environment['php_version'] ) . '</mark>';
				}
				?></td>
		</tr>
		<?php if ( function_exists( 'ini_get' ) ) : ?>
			<tr>
				<td data-export-label="PHP Post Max Size"><?php _e( 'PHP post max size', 'geodirectory' ); ?>:</td>
				<td class="help"><?php echo geodir_help_tip( __( 'The largest filesize that can be contained in one post.', 'geodirectory' ) ); ?></td>
				<td><?php echo esc_html( size_format( $environment['php_post_max_size'] ) ) ?></td>
			</tr>
			<tr>
				<td data-export-label="PHP Time Limit"><?php _e( 'PHP time limit', 'geodirectory' ); ?>:</td>
				<td class="help"><?php echo geodir_help_tip( __( 'The amount of time (in seconds) that your site will spend on a single operation before timing out (to avoid server lockups)', 'geodirectory' ) ); ?></td>
				<td><?php echo esc_html( $environment['php_max_execution_time'] ) ?></td>
			</tr>
			<tr>
				<td data-export-label="PHP Max Input Vars"><?php _e( 'PHP max input vars', 'geodirectory' ); ?>:</td>
				<td class="help"><?php echo geodir_help_tip( __( 'The maximum number of variables your server can use for a single function to avoid overloads.', 'geodirectory' ) ); ?></td>
				<td><?php echo esc_html( $environment['php_max_input_vars'] ) ?></td>
			</tr>
			<tr>
				<td data-export-label="cURL Version"><?php _e( 'cURL version', 'geodirectory' ); ?>:</td>
				<td class="help"><?php echo geodir_help_tip( __( 'The version of cURL installed on your server.', 'geodirectory' ) ); ?></td>
				<td><?php echo esc_html( $environment['curl_version'] ) ?></td>
			</tr>
			<tr>
				<td data-export-label="SUHOSIN Installed"><?php _e( 'SUHOSIN installed', 'geodirectory' ); ?>:</td>
				<td class="help"><?php echo geodir_help_tip( __( 'Suhosin is an advanced protection system for PHP installations. It was designed to protect your servers on the one hand against a number of well known problems in PHP applications and on the other hand against potential unknown vulnerabilities within these applications or the PHP core itself. If enabled on your server, Suhosin may need to be configured to increase its data submission limits.', 'geodirectory' ) ); ?></td>
				<td><?php echo $environment['suhosin_installed'] ? '<span class="dashicons dashicons-yes"></span>' : '&ndash;'; ?></td>
			</tr>
		<?php endif;
		if ( $wpdb->use_mysqli ) {
			$ver = mysqli_get_server_info( $wpdb->dbh );
		} elseif(function_exists('mysql_get_server_info')) {
			$ver = mysql_get_server_info();
		}else{
			$ver = '';
		}
		if ( ! empty( $wpdb->is_mysql ) && ! stristr( $ver, 'MariaDB' ) ) : ?>
			<tr>
				<td data-export-label="MySQL Version"><?php _e( 'MySQL version', 'geodirectory' ); ?>:</td>
				<td class="help"><?php echo geodir_help_tip( __( 'The version of MySQL installed on your hosting server.', 'geodirectory' ) ); ?></td>
				<td>
					<?php
					if ( version_compare( $environment['mysql_version'], '5.6', '<' ) ) {
						echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( '%1$s - We recommend a minimum MySQL version of 5.6. See: %2$s', 'geodirectory' ), esc_html( $environment['mysql_version'] ), '<a href="https://wordpress.org/about/requirements/" target="_blank">' . __( 'WordPress requirements', 'geodirectory' ) . '</a>' ) . '</mark>';
					} else {
						echo '<mark class="yes">' . esc_html( $environment['mysql_version'] ) . '</mark>';
					}
					?>
				</td>
			</tr>
		<?php endif; ?>
		<tr>
			<td data-export-label="Max Upload Size"><?php _e( 'Max upload size', 'geodirectory' ); ?>:</td>
			<td class="help"><?php echo geodir_help_tip( __( 'The largest filesize that can be uploaded to your WordPress installation.', 'geodirectory' ) ); ?></td>
			<td><?php echo size_format( $environment['max_upload_size'] ) ?></td>
		</tr>
		<tr>
			<td data-export-label="Default Timezone is UTC"><?php _e( 'Default timezone is UTC', 'geodirectory' ); ?>:</td>
			<td class="help"><?php echo geodir_help_tip( __( 'The default timezone for your server.', 'geodirectory' ) ); ?></td>
			<td><?php
				if ( 'UTC' !== $environment['default_timezone'] ) {
					echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( 'Default timezone is %s - it should be UTC', 'geodirectory' ), $environment['default_timezone'] ) . '</mark>';
				} else {
					echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
				} ?>
			</td>
		</tr>
		<tr>
			<td data-export-label="fsockopen/cURL"><?php _e( 'fsockopen/cURL', 'geodirectory' ); ?>:</td>
			<td class="help"><?php echo geodir_help_tip( __( 'Payment gateways can use cURL to communicate with remote servers to authorize payments, other plugins may also use it when communicating with remote services.', 'geodirectory' ) ); ?></td>
			<td><?php
				if ( $environment['fsockopen_or_curl_enabled'] ) {
					echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
				} else {
					echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . __( 'Your server does not have fsockopen or cURL enabled - PayPal IPN and other scripts which communicate with other servers will not work. Contact your hosting provider.', 'geodirectory' ) . '</mark>';
				} ?>
			</td>
		</tr>
		<tr>
			<td data-export-label="SoapClient"><?php _e( 'SoapClient', 'geodirectory' ); ?>:</td>
			<td class="help"><?php echo geodir_help_tip( __( 'Some webservices like shipping use SOAP to get information from remote servers, for example, live shipping quotes from FedEx require SOAP to be installed.', 'geodirectory' ) ); ?></td>
			<td><?php
				if ( $environment['soapclient_enabled'] ) {
					echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
				} else {
					echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( 'Your server does not have the %s class enabled - some gateway plugins which use SOAP may not work as expected.', 'geodirectory' ), '<a href="https://php.net/manual/en/class.soapclient.php">SoapClient</a>' ) . '</mark>';
				} ?>
			</td>
		</tr>
		<tr>
			<td data-export-label="DOMDocument"><?php _e( 'DOMDocument', 'geodirectory' ); ?>:</td>
			<td class="help"><?php echo geodir_help_tip( __( 'HTML/Multipart emails use DOMDocument to generate inline CSS in templates.', 'geodirectory' ) ); ?></td>
			<td><?php
				if ( $environment['domdocument_enabled'] ) {
					echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
				} else {
					echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( 'Your server does not have the %s class enabled - HTML/Multipart emails, and also some extensions, will not work without DOMDocument.', 'geodirectory' ), '<a href="https://php.net/manual/en/class.domdocument.php">DOMDocument</a>' ) . '</mark>';
				} ?>
			</td>
		</tr>
		<tr>
			<td data-export-label="GZip"><?php _e( 'GZip', 'geodirectory' ); ?>:</td>
			<td class="help"><?php echo geodir_help_tip( __( 'GZip (gzopen) is used to open the GEOIP database from MaxMind.', 'geodirectory' ) ); ?></td>
			<td><?php
				if ( $environment['gzip_enabled'] ) {
					echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
				} else {
					echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( 'Your server does not support the %s function - this is required to use the GeoIP database from MaxMind.', 'geodirectory' ), '<a href="https://php.net/manual/en/zlib.installation.php">gzopen</a>' ) . '</mark>';
				} ?>
			</td>
		</tr>
		<tr>
			<td data-export-label="Multibyte String"><?php _e( 'Multibyte string', 'geodirectory' ); ?>:</td>
			<td class="help"><?php echo geodir_help_tip( __( 'Multibyte String (mbstring) is used to convert character encoding, like for emails or converting characters to lowercase.', 'geodirectory' ) ); ?></td>
			<td><?php
				if ( $environment['mbstring_enabled'] ) {
					echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
				} else {
					echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( 'Your server does not support the %s functions - this is required for better character encoding. Some fallbacks will be used instead for it.', 'geodirectory' ), '<a href="https://php.net/manual/en/mbstring.installation.php">mbstring</a>' ) . '</mark>';
				} ?>
			</td>
		</tr>
		<tr>
			<td data-export-label="Remote Post"><?php _e( 'Remote post', 'geodirectory' ); ?>:</td>
			<td class="help"><?php echo geodir_help_tip( __( 'PayPal uses this method of communicating when sending back transaction information.', 'geodirectory' ) ); ?></td>
			<td><?php
				if ( $environment['remote_post_successful'] ) {
					echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
				} else {
					echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( '%s failed. Contact your hosting provider.', 'geodirectory' ), 'wp_remote_post()' ) . ' ' . esc_html( $environment['remote_post_response'] ) . '</mark>';
				} ?>
			</td>
		</tr>
		<tr>
			<td data-export-label="Remote Get"><?php _e( 'Remote get', 'geodirectory' ); ?>:</td>
			<td class="help"><?php echo geodir_help_tip( __( 'GeoDirectory plugins may use this method of communication when checking for plugin updates.', 'geodirectory' ) ); ?></td>
			<td><?php
				if ( $environment['remote_get_successful'] ) {
					echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
				} else {
					echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( '%s failed. Contact your hosting provider.', 'geodirectory' ), 'wp_remote_get()' ) . ' ' . esc_html( $environment['remote_get_response'] ) . '</mark>';
				} ?>
			</td>
		</tr>
		<?php
		$rows = apply_filters( 'geodir_system_status_environment_rows', array() );
		foreach ( $rows as $row ) {
			if ( ! empty( $row['success'] ) ) {
				$css_class = 'yes';
				$icon = '<span class="dashicons dashicons-yes"></span>';
			} else {
				$css_class = 'error';
				$icon = '<span class="dashicons dashicons-no-alt"></span>';
			}
			?>
			<tr>
				<td data-export-label="<?php echo esc_attr( $row['name'] ); ?>"><?php echo esc_html( $row['name'] ); ?>:</td>
				<td class="help"><?php echo isset( $row['help'] ) ? $row['help'] : ''; ?></td>
				<td>
					<mark class="<?php echo esc_attr( $css_class ); ?>">
						<?php echo $icon; ?>  <?php echo ! empty( $row['note'] ) ? wp_kses_data( $row['note'] ) : ''; ?>
					</mark>
				</td>
			</tr><?php
		} ?>
	</tbody>
</table>
<table class="gd_status_table widefat" cellspacing="0">
	<thead>
		<tr>
			<th colspan="3" data-export-label="User platform"><h2><?php _e( 'User platform', 'geodirectory' ); ?></h2></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td data-export-label="Platform"><?php _e( 'Platform', 'geodirectory' ); ?>:</td>
			<td class="help"></td>
			<td><?php echo esc_html( $environment['platform'] ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Browser name"><?php _e( 'Browser name', 'geodirectory' ); ?>:</td>
			<td class="help"></td>
			<td><?php echo esc_html( $environment['browser_name'] ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Browser version"><?php _e( 'Browser version', 'geodirectory' ); ?>:</td>
			<td class="help"></td>
			<td><?php echo esc_html( $environment['browser_version'] ); ?></td>
		</tr>
		<tr>
			<td data-export-label="User agent"><?php _e( 'User agent', 'geodirectory' ); ?>:</td>
			<td class="help"></td>
			<td><?php echo esc_html( $environment['user_agent'] ); ?></td>
		</tr>
	</tbody>
</table>
<table class="gd_status_table widefat" cellspacing="0">
	<thead>
		<tr>
			<th colspan="3" data-export-label="Settings"><h2><?php _e( 'Settings', 'geodirectory' ); ?></h2></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td data-export-label="API Enabled"><?php _e( 'Rest API enabled', 'geodirectory' ); ?>:</td>
			<td class="help"><?php echo geodir_help_tip( __( 'Does site have REST API enabled?', 'geodirectory' ) ); ?></td>
			<td><?php echo $settings['api_enabled'] ? '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>' : '<mark class="no">&ndash;</mark>'; ?></td>
		</tr>
		<tr>
			<td data-export-label="Max upload file size(in mb)"><?php _e( 'Max upload file size(in mb)', 'geodirectory' ); ?>:</td>
			<td class="help"><?php echo geodir_help_tip( __( 'Max upload file size in MB. This setting will overwrite the max upload file size limit in image/file upload & import listings for entire GeoDirectory core + GeoDirectory plugins.', 'geodirectory' ) ); ?></td>
			<td><?php echo esc_html( $settings['upload_max_filesize'] ) ?></td>
		</tr>
		<tr>
			<td data-export-label="New listing default status"><?php _e( 'New listing default status', 'geodirectory' ); ?>:</td>
			<td class="help"><?php echo geodir_help_tip( __( 'This is the post status a new listing will get when submitted from the frontend.', 'geodirectory' ) ); ?></td>
			<td><?php echo esc_html( $settings['default_status'] ) ?></td>
		</tr>
		<tr>
			<td data-export-label="Google Maps API KEY"><?php _e( 'Google Maps API KEY', 'geodirectory' ); ?>:</td>
			<td class="help"><?php echo geodir_help_tip( __( 'This is a requirement to use Google Maps.', 'geodirectory' ) ); ?></td>
			<td><?php echo $settings['maps_api_key'] ? '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>' : '<mark class="no">&ndash;</mark>'; ?></td>
		</tr>
		<tr>
			<td data-export-label="Set default location"><?php _e( 'Set default location', 'geodirectory' ); ?>:</td>
			<td class="help"></td>
			<td><?php echo $settings['default_location'] ? '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>' : '<mark class="no">&ndash;</mark>'; ?></td>
		</tr>
	</tbody>
</table>
<table class="gd_status_table widefat" cellspacing="0">
    <thead>
    <tr>
        <th colspan="3" data-export-label="Database"><h2><?php _e( 'Database', 'geodirectory' ); ?></h2></th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td data-export-label="GD Database Version"><?php _e( 'GD database version', 'geodirectory' ); ?>:</td>
        <td class="help"><?php echo geodir_help_tip( __( 'The version of GeoDirectory that the database is formatted for. This should be the same as your GeoDirectory version.', 'geodirectory' ) ); ?></td>
        <td><?php echo esc_html( $database['geodirectory_db_version'] ); ?></td>
    </tr>
    <tr>
        <td data-export-label="GD Database Prefix"><?php _e( 'Database prefix', 'geodirectory' ); ?></td>
        <td class="help">&nbsp;</td>
        <td><?php
			if ( strlen( $database['database_prefix'] ) > 20 ) {
				echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( '%1$s - We recommend using a prefix with less than 20 characters.', 'geodirectory' ), esc_html( $database['database_prefix'] ) ) . '</mark>';
			} else {
				echo '<mark class="yes">' . esc_html( $database['database_prefix'] ) . '</mark>';
			}
			?>
        </td>
    </tr>
    <tr>
        <td><?php _e( 'Total Database Size', 'geodirectory' ); ?></td>
        <td class="help">&nbsp;</td>
        <td><?php printf( '%.2fMB', $database['database_size']['data'] + $database['database_size']['index'] ); ?></td>
    </tr>

    <tr>
        <td><?php _e( 'Database Data Size', 'geodirectory' ); ?></td>
        <td class="help">&nbsp;</td>
        <td><?php printf( '%.2fMB', $database['database_size']['data'] ); ?></td>
    </tr>

    <tr>
        <td><?php _e( 'Database Index Size', 'geodirectory' ); ?></td>
        <td class="help">&nbsp;</td>
        <td><?php printf( '%.2fMB', $database['database_size']['index'] ); ?></td>
    </tr>

	<?php foreach ( $database['database_tables']['geodirectory'] as $table => $table_data ) { ?>
    <tr>
        <td><?php echo esc_html( $table ); ?></td>
        <td class="help">&nbsp;</td>
        <td>
			<?php if( ! $table_data ) {
				echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . __( 'Table does not exist', 'geodirectory' ) . '</mark>';
			} else {
				printf( __( 'Data: %.2fMB + Index: %.2fMB', 'geodirectory' ), geodir_format_decimal( $table_data['data'], 2 ), geodir_format_decimal( $table_data['index'], 2 ) );
			} ?>
        </td>
        </tr>
    <?php } ?>

    <?php foreach ( $database['database_tables']['other'] as $table => $table_data ) { ?>
        <tr>
            <td><?php echo esc_html( $table ); ?></td>
            <td class="help">&nbsp;</td>
            <td>
			    <?php printf( __( 'Data: %.2fMB + Index: %.2fMB', 'geodirectory' ), geodir_format_decimal( $table_data['data'], 2 ), geodir_format_decimal( $table_data['index'], 2 ) ); ?>
            </td>
        </tr>
    <?php } ?>
    </tbody>
</table>
<table class="gd_status_table widefat" cellspacing="0">
    <thead>
    <tr>
        <th colspan="3" data-export-label="Post Type Counts"><h2><?php _e( 'Post Type Counts', 'geodirectory' ); ?></h2></th>
    </tr>
    </thead>
    <tbody>
	<?php
	foreach ( $post_type_counts as $post_type ) {
		?>
        <tr>
            <td><?php echo esc_html( $post_type->type ); ?></td>
            <td class="help">&nbsp;</td>
            <td><?php echo absint( $post_type->count ); ?></td>
        </tr>
		<?php
	}
	?>
    </tbody>
</table>
<table class="gd_status_table widefat" cellspacing="0">
	<thead>
		<tr>
			<th colspan="3" data-export-label="Security"><h2><?php _e( 'Security', 'geodirectory' ); ?></h2></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td data-export-label="Secure connection (HTTPS)"><?php _e( 'Secure connection (HTTPS)', 'geodirectory' ); ?>:</td>
			<td class="help"><?php echo geodir_help_tip( __( 'Is the connection to your site secure?', 'geodirectory' ) ); ?></td>
			<td>
				<?php if ( $security['secure_connection'] ) : ?>
					<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>
				<?php else : ?>
					<mark class="error"><span class="dashicons dashicons-warning"></span><?php echo __( 'Your site is not using HTTPS.', 'geodirectory' ); ?></mark>
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<td data-export-label="Hide errors from visitors"><?php _e( 'Hide errors from visitors', 'geodirectory' ); ?></td>
			<td class="help"><?php echo geodir_help_tip( __( 'Error messages can contain sensitive information about your site environment. These should be hidden from untrusted visitors.', 'geodirectory' ) ); ?></td>
			<td>
				<?php if ( $security['hide_errors'] ) : ?>
					<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>
				<?php else : ?>
					<mark class="error"><span class="dashicons dashicons-warning"></span><?php _e( 'Error messages should not be shown to visitors.', 'geodirectory' ); ?></mark>
				<?php endif; ?>
			</td>
		</tr>
	</tbody>
</table>
<table class="gd_status_table widefat" cellspacing="0">
	<thead>
		<tr>
			<th colspan="3" data-export-label="Active Plugins (<?php echo count( $active_plugins ) ?>)"><h2><?php _e( 'Active plugins', 'geodirectory' ); ?> (<?php echo count( $active_plugins ) ?>)</h2></th>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach ( $active_plugins as $plugin ) {
			if ( ! empty( $plugin['name'] ) ) {
				$dirname = dirname( $plugin['plugin'] );

				// Link the plugin name to the plugin url if available.
				$plugin_name = esc_html( $plugin['name'] );
				if ( ! empty( $plugin['url'] ) ) {
					$plugin_name = '<a href="' . esc_url( $plugin['url'] ) . '" aria-label="' . esc_attr__( 'Visit plugin homepage' , 'geodirectory' ) . '" target="_blank">' . $plugin_name . '</a>';
				}

				$version_string = '';
				$network_string = '';
				if ( ! empty( $plugin['latest_verison'] ) && version_compare( $plugin['latest_verison'], $plugin['version'], '>' ) ) {
					/* translators: %s: plugin latest version */
					$version_string = ' &ndash; <strong style="color:red;">' . sprintf( esc_html__( '%s is available', 'geodirectory' ), $plugin['latest_verison'] ) . '</strong>';
				}

				if ( false != $plugin['network_activated'] ) {
					$network_string = ' &ndash; <strong style="color:black;">' . __( 'Network enabled', 'geodirectory' ) . '</strong>';
				}
				?>
				<tr>
					<td><?php echo $plugin_name; ?></td>
					<td class="help">&nbsp;</td>
					<td><?php
						/* translators: %s: plugin author */
						printf( __( 'by %s', 'geodirectory' ), $plugin['author_name'] );
						echo ' &ndash; ' . esc_html( $plugin['version'] ) . $version_string . $network_string;
					?></td>
				</tr>
				<?php
			}
		}
		?>
	</tbody>
</table>
<table class="gd_status_table widefat" cellspacing="0">
	<thead>
		<tr>
			<th colspan="3" data-export-label="GD Pages"><h2><?php _e( 'GD pages', 'geodirectory' ); ?></h2></th>
		</tr>
	</thead>
	<tbody>
		<?php
			$alt = 1;
			foreach ( $pages as $page ) {
				$error   = false;

				if ( $page['page_id'] ) {
					$page_name = '<a href="' . get_edit_post_link( $page['page_id'] ) . '" aria-label="' . sprintf( __( 'Edit %s page', 'geodirectory' ), esc_html( $page['page_name'] ) ) . '">' . esc_html( $page['page_name'] ) . '</a>';
				} else {
					$page_name = esc_html( $page['page_name'] );
				}

				echo '<tr><td data-export-label="' . esc_attr( $page_name ) . '">' . $page_name . ':</td>';
				echo '<td class="help">' . geodir_help_tip( sprintf( __( 'The URL of your %s page (along with the Page ID).', 'geodirectory' ), $page_name ) ) . '</td><td>';

				// Page ID check.
				if ( ! $page['page_set'] ) {
					echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . __( 'Page not set', 'geodirectory' ) . '</mark>';
					$error = true;
				} elseif ( ! $page['page_exists'] ) {
					echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . __( 'Page ID is set, but the page does not exist', 'geodirectory' ) . '</mark>';
					$error = true;
				} elseif ( ! $page['page_visible'] ) {
					echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( 'Page visibility should be <a href="%s" target="_blank">public</a>', 'geodirectory' ), 'https://codex.wordpress.org/Content_Visibility' ) . '</mark>';
					$error = true;
				} else {
					// Shortcode check
					if ( $page['shortcode_required'] ) {
						if ( ! $page['shortcode_present'] ) {
							echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( 'Page does not contain the shortcode.', 'geodirectory' ), $page['shortcode'] ) . '</mark>';
							$error = true;
						}
					}
				}

				if ( ! $error ) {
					echo '<mark class="yes">#' . absint( $page['page_id'] ) . ' - ' . str_replace( home_url(), '', get_permalink( $page['page_id'] ) ) . '</mark>';
				}

				echo '</td></tr>';
			}
		?>
	</tbody>
</table>
<table class="gd_status_table widefat" cellspacing="0">
	<thead>
		<tr>
			<th colspan="3" data-export-label="Theme"><h2><?php _e( 'Theme', 'geodirectory' ); ?></h2></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td data-export-label="Name"><?php _e( 'Name', 'geodirectory' ); ?>:</td>
			<td class="help"><?php echo geodir_help_tip( __( 'The name of the current active theme.', 'geodirectory' ) ); ?></td>
			<td><?php echo esc_html( $theme['name'] ) ?></td>
		</tr>
		<tr>
			<td data-export-label="Version"><?php _e( 'Version', 'geodirectory' ); ?>:</td>
			<td class="help"><?php echo geodir_help_tip( __( 'The installed version of the current active theme.', 'geodirectory' ) ); ?></td>
			<td><?php
				echo esc_html( $theme['version'] );
				if ( version_compare( $theme['version'], $theme['latest_verison'], '<' ) ) {
					/* translators: %s: theme latest version */
					echo ' &ndash; <strong style="color:red;">' . sprintf( __( '%s is available', 'geodirectory' ), esc_html( $theme['latest_verison'] ) ) . '</strong>';
				}
			?></td>
		</tr>
		<tr>
			<td data-export-label="Author URL"><?php _e( 'Author URL', 'geodirectory' ); ?>:</td>
			<td class="help"><?php echo geodir_help_tip( __( 'The theme developers URL.', 'geodirectory' ) ); ?></td>
			<td><?php echo esc_html( $theme['author_url'] ) ?></td>
		</tr>
		<tr>
			<td data-export-label="Child Theme"><?php _e( 'Child theme', 'geodirectory' ); ?>:</td>
			<td class="help"><?php echo geodir_help_tip( __( 'Displays whether or not the current theme is a child theme.', 'geodirectory' ) ); ?></td>
			<td><?php
				echo $theme['is_child_theme'] ? '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>' : '<span class="dashicons dashicons-no-alt"></span> &ndash; ' . sprintf( __( 'If you are modifying GeoDirectory on a parent theme that you did not build personally we recommend using a child theme. See: <a href="%s" target="_blank">How to create a child theme</a>', 'geodirectory' ), 'https://codex.wordpress.org/Child_Themes' );
			?></td>
		</tr>
		<?php
		if ( $theme['is_child_theme'] ) :
		?>
		<tr>
			<td data-export-label="Parent Theme Name"><?php _e( 'Parent theme name', 'geodirectory' ); ?>:</td>
			<td class="help"><?php echo geodir_help_tip( __( 'The name of the parent theme.', 'geodirectory' ) ); ?></td>
			<td><?php echo esc_html( $theme['parent_name'] ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Parent Theme Version"><?php _e( 'Parent theme version', 'geodirectory' ); ?>:</td>
			<td class="help"><?php echo geodir_help_tip( __( 'The installed version of the parent theme.', 'geodirectory' ) ); ?></td>
			<td><?php
				echo esc_html( $theme['parent_version'] );
				if ( version_compare( $theme['parent_version'], $theme['parent_latest_verison'], '<' ) ) {
					/* translators: %s: parant theme latest version */
					echo ' &ndash; <strong style="color:red;">' . sprintf( __( '%s is available', 'geodirectory' ), esc_html( $theme['parent_latest_verison'] ) ) . '</strong>';
				}
			?></td>
		</tr>
		<tr>
			<td data-export-label="Parent Theme Author URL"><?php _e( 'Parent theme author URL', 'geodirectory' ); ?>:</td>
			<td class="help"><?php echo geodir_help_tip( __( 'The parent theme developers URL.', 'geodirectory' ) ); ?></td>
			<td><?php echo esc_html( $theme['parent_author_url'] ) ?></td>
		</tr>
		<?php endif ?>
		<tr>
			<td data-export-label="GeoDirectory Support"><?php _e( 'GeoDirectory support', 'geodirectory' ); ?>:</td>
			<td class="help"><?php echo geodir_help_tip( __( 'Displays whether or not the current active theme declares GeoDirectory support.', 'geodirectory' ) ); ?></td>
			<td>
				<?php if ( ! $theme['has_geodirectory_support'] ) {
					echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . __( 'Not declared', 'geodirectory' ) . '</mark>';
				} else {
					echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
				} ?>
			</td>
		</tr>
	</tbody>
</table>
<table class="gd_status_table widefat" cellspacing="0">
	<thead>
		<tr>
			<th colspan="3" data-export-label="Templates"><h2><?php _e( 'Templates', 'geodirectory' ); ?><?php echo geodir_help_tip( __( 'This section shows any files that are overriding the default GeoDirectory template pages.', 'geodirectory' ) ); ?></h2></th>
		</tr>
	</thead>
	<tbody>
		<?php
			if ( ! empty( $theme['overrides'] ) ) { ?>
					<tr>
						<td data-export-label="Overrides"><?php _e( 'Overrides', 'geodirectory' ); ?></td>
						<td class="help">&nbsp;</td>
						<td>
							<?php
							$total_overrides = count( $theme['overrides'] );
							for ( $i = 0; $i < $total_overrides; $i++ ) {
								$override = $theme['overrides'][ $i ];
								if ( $override['core_version'] && ( empty( $override['version'] ) || version_compare( $override['version'], $override['core_version'], '<' ) ) ) {
									$current_version = $override['version'] ? $override['version'] : '-';
									printf(
										__( '%1$s version %2$s is out of date. The core version is %3$s', 'geodirectory' ),
										'<code>' . $override['file'] . '</code>',
										'<strong style="color:red">' . $current_version . '</strong>',
										$override['core_version']
									);
								} else {
									echo esc_html( $override['file'] );
								}
								if ( ( count( $theme['overrides'] ) - 1 ) !== $i ) {
									echo ', ';
								}
								echo '<br />';
							}
							?>
						</td>
					</tr>
					<?php
			} else {
				?>
				<tr>
					<td data-export-label="Overrides"><?php _e( 'Overrides', 'geodirectory' ); ?>:</td>
					<td class="help">&nbsp;</td>
					<td>&ndash;</td>
				</tr>
				<?php
			}

			if ( true === $theme['has_outdated_templates'] ) {
				?>
				<tr>
					<td data-export-label="Outdated Templates"><?php _e( 'Outdated templates', 'geodirectory' ); ?>:</td>
					<td class="help">&nbsp;</td>
					<td><mark class="error"><span class="dashicons dashicons-warning"></span></mark></td>
				</tr>
				<?php
			}
		?>
	</tbody>
</table>

<?php do_action( 'geodir_system_status_report' ); ?>
