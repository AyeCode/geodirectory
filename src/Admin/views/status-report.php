<?php
/**
 * Admin View: Page - Status Report (Modernized with Bootstrap 5)
 *
 * This template has been updated to use Bootstrap 5 for a more modern,
 * responsive, and user-friendly interface. The core functionality remains identical
 * to the original version.
 *
 * @package     GeoDirectory
 * @since       2.2.0
 * @todo make the js work
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

// Ensure the required REST API controller is available.
if ( ! class_exists( 'GeoDir_REST_System_Status_Controller', false ) ) {
	wp_die( 'Cannot load the REST API to access GeoDir_REST_System_Status_Controller.' );
}

// Instantiate the controller and fetch all necessary system data.
$system_status    = new GeoDir_REST_System_Status_Controller;
$environment      = $system_status->get_environment_info();
$database         = $system_status->get_database_info();
$post_type_counts = $system_status->get_post_type_counts();
$active_plugins   = $system_status->get_active_plugins();
$theme            = $system_status->get_theme_info();
$security         = $system_status->get_security_info();
$settings         = $system_status->get_settings();
$pages            = $system_status->get_pages();

/**
 * Helper function to generate a Bootstrap 5 tooltip icon.
 * This replaces the old help tip functionality for a consistent BS5 look and feel.
 *
 * @param string $tip The content of the tooltip.
 * @return string HTML for the tooltip icon.
 */
function geodir_bs5_help_tip( $tip ) {
	if ( ! $tip ) {
		return '';
	}
	return '<i class="fas fa-question-circle text-muted ms-2" data-bs-toggle="tooltip" title="' . esc_attr( $tip ) . '"></i>';
}
?>

<div class="container-fluid py-4">

	<!-- System Report for Support -->
	<div class="card shadow-sm mb-4 mw-100">
		<div class="card-body">
			<div class="d-flex align-items-center">
				<i class="fas fa-info-circle fa-2x text-primary me-3"></i>
				<div>
					<h5 class="card-title mb-1"><?php _e( 'Need Support?', 'geodirectory' ); ?></h5>
					<p class="card-text text-muted mb-2"><?php _e( 'Please copy and paste this information in your ticket when contacting support.', 'geodirectory' ); ?></p>
					<button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseReport" aria-expanded="false" aria-controls="collapseReport" >
						<i class="fas fa-clipboard me-2"></i><?php _e( 'Get System Report', 'geodirectory' ); ?>
					</button>
				</div>
			</div>
			<div class="collapse mt-3" id="collapseReport">
				<div class="card card-body bg-light mw-100">
					<textarea id="debug-report-textarea" class="form-control font-monospace" rows="15" readonly="readonly"></textarea>
					<div class="d-flex justify-content-between align-items-center mt-2">
						<button id="copy-for-support" class="btn btn-sm btn-dark">
							<i class="fas fa-copy me-2"></i><?php _e( 'Copy to Clipboard', 'geodirectory' ); ?>
						</button>
						<small id="copy-error" class="text-danger d-none"><?php _e( 'Copying failed. Please use Ctrl/Cmd+C.', 'geodirectory' ); ?></small>
						<small id="copy-success" class="text-success d-none"><?php _e( 'Copied!', 'geodirectory' ); ?></small>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Main Accordion for System Status Sections -->
	<div class="accordion" id="statusReportAccordion">

		<?php
		// An array to define the structure of our accordion items.
		// This makes the layout clean and easy to manage.
		$status_sections = array(
			'wordpress' => array(
				'title' => __( 'WordPress Environment', 'geodirectory' ),
				'icon' => 'fab fa-wordpress',
				'data' => $environment,
			),
			'server' => array(
				'title' => __( 'Server Environment', 'geodirectory' ),
				'icon' => 'fas fa-server',
				'data' => $environment,
			),
			'user_platform' => array(
				'title' => __( 'User Platform', 'geodirectory' ),
				'icon'  => 'fas fa-desktop',
				'data'  => $environment,
			),
			'settings' => array(
				'title' => __( 'Settings', 'geodirectory' ),
				'icon' => 'fas fa-cogs',
				'data' => $settings,
			),
			'security' => array(
				'title' => __( 'Security', 'geodirectory' ),
				'icon' => 'fas fa-shield-alt',
				'data' => $security,
			),
			'theme' => array(
				'title' => __( 'Theme', 'geodirectory' ),
				'icon' => 'fas fa-paint-brush',
				'data' => $theme,
			),
			'templates' => array(
				'title' => __( 'Templates', 'geodirectory' ),
				'icon' => 'fas fa-file-code',
				'data' => $theme,
			),
			'pages' => array(
				'title' => __( 'GD Pages', 'geodirectory' ),
				'icon' => 'fas fa-file-alt',
				'data' => $pages,
			),
			'active_plugins' => array(
				'title' => __( 'Active Plugins', 'geodirectory' ) . ' (' . count( $active_plugins ) . ')',
				'icon' => 'fas fa-plug',
				'data' => $active_plugins,
			),
			'database' => array(
				'title' => __( 'Database', 'geodirectory' ),
				'icon' => 'fas fa-database',
				'data' => $database,
			),
			'post_types' => array(
				'title' => __( 'Post Type Counts', 'geodirectory' ),
				'icon' => 'fas fa-th-list',
				'data' => $post_type_counts,
			),
		);

		// Loop through each section and render it as an accordion item.
		foreach ( $status_sections as $key => $section ) :
			?>
			<div class="accordion-item">
				<h2 class="accordion-header" id="heading-<?php echo esc_attr( $key ); ?>">
					<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo esc_attr( $key ); ?>" aria-expanded="false" aria-controls="collapse-<?php echo esc_attr( $key ); ?>">
						<i class="<?php echo esc_attr( $section['icon'] ); ?> fa-fw me-3 text-primary"></i>
						<span class="fw-bold"><?php echo esc_html( $section['title'] ); ?></span>
					</button>
				</h2>
				<div id="collapse-<?php echo esc_attr( $key ); ?>" class="accordion-collapse collapse" aria-labelledby="heading-<?php echo esc_attr( $key ); ?>" data-bs-parent="#statusReportAccordion">
					<div class="accordion-body">
						<div class="list-group list-group-flush">
							<?php
							// A switch statement to render the content for each specific section.
							// This keeps the rendering logic organized.
							switch ( $key ) {
								case 'wordpress':
									?>
									<div class="list-group-item d-flex justify-content-between align-items-center">
										<div><?php _e( 'Home URL', 'geodirectory' ); ?><?php echo geodir_bs5_help_tip( __( 'The homepage URL of your site.', 'geodirectory' ) ); ?></div>
										<span class="font-monospace"><?php echo esc_html( $environment['home_url'] ); ?></span>
									</div>
									<div class="list-group-item d-flex justify-content-between align-items-center">
										<div><?php _e( 'Site URL', 'geodirectory' ); ?><?php echo geodir_bs5_help_tip( __( 'The root URL of your site.', 'geodirectory' ) ); ?></div>
										<span class="font-monospace"><?php echo esc_html( $environment['site_url'] ); ?></span>
									</div>
									<div class="list-group-item d-flex justify-content-between align-items-center">
										<div><?php _e( 'GD Version', 'geodirectory' ); ?><?php echo geodir_bs5_help_tip( __( 'The version of GeoDirectory installed on your site.', 'geodirectory' ) ); ?></div>
										<span class="badge bg-secondary"><?php echo esc_html( $environment['version'] ); ?></span>
									</div>
									<div class="list-group-item d-flex justify-content-between align-items-center">
										<div><?php _e( 'WP Version', 'geodirectory' ); ?><?php echo geodir_bs5_help_tip( __( 'The version of WordPress installed on your site.', 'geodirectory' ) ); ?></div>
										<span class="badge bg-secondary"><?php echo esc_html( $environment['wp_version'] ); ?></span>
									</div>
									<div class="list-group-item d-flex justify-content-between align-items-center">
										<div><?php _e( 'WP Multisite', 'geodirectory' ); ?><?php echo geodir_bs5_help_tip( __( 'Whether or not you have WordPress Multisite enabled.', 'geodirectory' ) ); ?></div>
										<span><?php echo $environment['wp_multisite'] ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-minus-circle text-muted"></i>'; ?></span>
									</div>
									<div class="list-group-item d-flex justify-content-between align-items-center">
										<div><?php _e( 'WP Memory Limit', 'geodirectory' ); ?><?php echo geodir_bs5_help_tip( __( 'The maximum amount of memory (RAM) that your site can use at one time.', 'geodirectory' ) ); ?></div>
										<span>
										<?php
										if ( $environment['wp_memory_limit'] < 67108864 ) {
											echo '<span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle me-1"></i> ' . sprintf( __( '%1$s - We recommend at least 64MB. See: %2$s', 'geodirectory' ), size_format( $environment['wp_memory_limit'] ), '<a href="https://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP" target="_blank" class="link-dark">' . __( 'More info', 'geodirectory' ) . '</a>' ) . '</span>';
										} else {
											echo '<span class="badge bg-success">' . size_format( $environment['wp_memory_limit'] ) . '</span>';
										}
										?>
									</span>
									</div>
									<div class="list-group-item d-flex justify-content-between align-items-center">
										<div><?php _e( 'WP Debug Mode', 'geodirectory' ); ?><?php echo geodir_bs5_help_tip( __( 'Displays whether or not WordPress is in Debug Mode.', 'geodirectory' ) ); ?></div>
										<span><?php echo $environment['wp_debug_mode'] ? '<i class="fas fa-check-circle text-success" title="Enabled"></i>' : '<i class="fas fa-minus-circle text-muted" title="Disabled"></i>'; ?></span>
									</div>
									<div class="list-group-item d-flex justify-content-between align-items-center">
										<div><?php _e( 'WP Cron', 'geodirectory' ); ?><?php echo geodir_bs5_help_tip( __( 'Displays whether or not WP Cron Jobs are enabled.', 'geodirectory' ) ); ?></div>
										<span><?php echo $environment['wp_cron'] ? '<i class="fas fa-check-circle text-success" title="Enabled"></i>' : '<i class="fas fa-minus-circle text-muted" title="Disabled"></i>'; ?></span>
									</div>
									<div class="list-group-item d-flex justify-content-between align-items-center">
										<div><?php _e( 'Language', 'geodirectory' ); ?><?php echo geodir_bs5_help_tip( __( 'The current language used by WordPress.', 'geodirectory' ) ); ?></div>
										<span class="badge bg-light text-dark"><?php echo esc_html( $environment['language'] ); ?></span>
									</div>
									<?php
									break;

								case 'server':
									?>
									<div class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
										<div><?php _e( 'Server Info', 'geodirectory' ); ?><?php echo geodir_bs5_help_tip( __( 'Information about the web server that is currently hosting your site.', 'geodirectory' ) ); ?></div>
										<span class="badge bg-light text-dark text-break text-start"><?php echo esc_html( $environment['server_info'] ); ?></span>
									</div>
									<div class="list-group-item d-flex justify-content-between align-items-center">
										<div><?php _e( 'PHP Version', 'geodirectory' ); ?><?php echo geodir_bs5_help_tip( __( 'The version of PHP installed on your hosting server.', 'geodirectory' ) ); ?></div>
										<span>
										<?php
										if ( version_compare( $environment['php_version'], '7.4', '<' ) ) {
											echo '<span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle me-1"></i> ' . sprintf( __( '%s - We recommend a minimum PHP version of 7.4.', 'geodirectory' ), esc_html( $environment['php_version'] ) ) . '</span>';
										} else {
											echo '<span class="badge bg-success">' . esc_html( $environment['php_version'] ) . '</span>';
										}
										?>
									</span>
									</div>
									<?php if ( function_exists( 'ini_get' ) ) : ?>
									<div class="list-group-item d-flex justify-content-between align-items-center">
										<div><?php _e( 'PHP Post Max Size', 'geodirectory' ); ?><?php echo geodir_bs5_help_tip( __( 'The largest filesize that can be contained in one post.', 'geodirectory' ) ); ?></div>
										<span class="badge bg-secondary"><?php echo esc_html( size_format( $environment['php_post_max_size'] ) ); ?></span>
									</div>
									<div class="list-group-item d-flex justify-content-between align-items-center">
										<div><?php _e( 'PHP Time Limit', 'geodirectory' ); ?><?php echo geodir_bs5_help_tip( __( 'The amount of time (in seconds) that your site will spend on a single operation before timing out.', 'geodirectory' ) ); ?></div>
										<span class="badge bg-secondary"><?php echo esc_html( $environment['php_max_execution_time'] ); ?></span>
									</div>
									<div class="list-group-item d-flex justify-content-between align-items-center">
										<div><?php _e( 'PHP Max Input Vars', 'geodirectory' ); ?><?php echo geodir_bs5_help_tip( __( 'The maximum number of variables your server can use for a single function.', 'geodirectory' ) ); ?></div>
										<span class="badge bg-secondary"><?php echo esc_html( $environment['php_max_input_vars'] ); ?></span>
									</div>
									<div class="list-group-item d-flex justify-content-between align-items-center">
										<div><?php _e( 'cURL Version', 'geodirectory' ); ?><?php echo geodir_bs5_help_tip( __( 'The version of cURL installed on your server.', 'geodirectory' ) ); ?></div>
										<span class="badge bg-light text-dark"><?php echo esc_html( $environment['curl_version'] ); ?></span>
									</div>
									<div class="list-group-item d-flex justify-content-between align-items-center">
										<div><?php _e( 'SUHOSIN Installed', 'geodirectory' ); ?><?php echo geodir_bs5_help_tip( __( 'Suhosin is an advanced protection system for PHP installations.', 'geodirectory' ) ); ?></div>
										<span><?php echo $environment['suhosin_installed'] ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-minus-circle text-muted"></i>'; ?></span>
									</div>
								<?php endif; ?>
									<?php
									if ( $wpdb->use_mysqli ) {
										$ver = mysqli_get_server_info( $wpdb->dbh );
									} elseif ( function_exists( 'mysql_get_server_info' ) ) {
										$ver = mysql_get_server_info();
									} else {
										$ver = '';
									}
									if ( ! empty( $wpdb->is_mysql ) && ! stristr( $ver, 'MariaDB' ) ) :
										?>
										<div class="list-group-item d-flex justify-content-between align-items-center">
											<div><?php _e( 'MySQL Version', 'geodirectory' ); ?><?php echo geodir_bs5_help_tip( __( 'The version of MySQL installed on your hosting server.', 'geodirectory' ) ); ?></div>
											<span>
										<?php
										if ( version_compare( $environment['mysql_version'], '5.6', '<' ) ) {
											echo '<span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle me-1"></i> ' . sprintf( __( '%1$s - We recommend 5.6+. See: %2$s', 'geodirectory' ), esc_html( $environment['mysql_version'] ), '<a href="https://wordpress.org/about/requirements/" target="_blank" class="link-dark">' . __( 'WP requirements', 'geodirectory' ) . '</a>' ) . '</span>';
										} else {
											echo '<span class="badge bg-success">' . esc_html( $environment['mysql_version'] ) . '</span>';
										}
										?>
									</span>
										</div>
									<?php endif; ?>
									<div class="list-group-item d-flex justify-content-between align-items-center">
										<div><?php _e( 'Max Upload Size', 'geodirectory' ); ?><?php echo geodir_bs5_help_tip( __( 'The largest filesize that can be uploaded to your WordPress installation.', 'geodirectory' ) ); ?></div>
										<span class="badge bg-secondary"><?php echo size_format( $environment['max_upload_size'] ); ?></span>
									</div>
									<div class="list-group-item d-flex justify-content-between align-items-center">
										<div><?php _e( 'Default Timezone is UTC', 'geodirectory' ); ?><?php echo geodir_bs5_help_tip( __( 'The default timezone for your server.', 'geodirectory' ) ); ?></div>
										<span>
										<?php
										if ( 'UTC' !== $environment['default_timezone'] ) {
											echo '<span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle me-1"></i> ' . sprintf( __( 'Default timezone is %s - it should be UTC', 'geodirectory' ), $environment['default_timezone'] ) . '</span>';
										} else {
											echo '<i class="fas fa-check-circle text-success"></i>';
										}
										?>
									</span>
									</div>
									<div class="list-group-item d-flex justify-content-between align-items-center">
										<div><?php _e( 'fsockopen/cURL', 'geodirectory' ); ?><?php echo geodir_bs5_help_tip( __( 'Used for communication with remote servers.', 'geodirectory' ) ); ?></div>
										<span><?php echo $environment['fsockopen_or_curl_enabled'] ? '<i class="fas fa-check-circle text-success"></i>' : '<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> Disabled</span>'; ?></span>
									</div>
									<div class="list-group-item d-flex justify-content-between align-items-center">
										<div><?php _e( 'SoapClient', 'geodirectory' ); ?><?php echo geodir_bs5_help_tip( __( 'Some webservices use SOAP for communication.', 'geodirectory' ) ); ?></div>
										<span><?php echo $environment['soapclient_enabled'] ? '<i class="fas fa-check-circle text-success"></i>' : '<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> Disabled</span>'; ?></span>
									</div>
									<div class="list-group-item d-flex justify-content-between align-items-center">
										<div><?php _e( 'DOMDocument', 'geodirectory' ); ?><?php echo geodir_bs5_help_tip( __( 'Used for generating HTML/Multipart emails.', 'geodirectory' ) ); ?></div>
										<span><?php echo $environment['domdocument_enabled'] ? '<i class="fas fa-check-circle text-success"></i>' : '<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> Disabled</span>'; ?></span>
									</div>
									<div class="list-group-item d-flex justify-content-between align-items-center">
										<div><?php _e( 'GZip', 'geodirectory' ); ?><?php echo geodir_bs5_help_tip( __( 'Used to open the GEOIP database from MaxMind.', 'geodirectory' ) ); ?></div>
										<span><?php echo $environment['gzip_enabled'] ? '<i class="fas fa-check-circle text-success"></i>' : '<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> Disabled</span>'; ?></span>
									</div>
									<div class="list-group-item d-flex justify-content-between align-items-center">
										<div><?php _e( 'Multibyte String', 'geodirectory' ); ?><?php echo geodir_bs5_help_tip( __( 'Used to convert character encoding.', 'geodirectory' ) ); ?></div>
										<span><?php echo $environment['mbstring_enabled'] ? '<i class="fas fa-check-circle text-success"></i>' : '<span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle me-1"></i> Disabled</span>'; ?></span>
									</div>
									<div class="list-group-item d-flex justify-content-between align-items-center">
										<div><?php _e( 'Remote Post', 'geodirectory' ); ?><?php echo geodir_bs5_help_tip( __( 'PayPal uses this to send transaction information.', 'geodirectory' ) ); ?></div>
										<span>
										<?php
										if ( $environment['remote_post_successful'] ) {
											echo '<i class="fas fa-check-circle text-success"></i>';
										} else {
											echo '<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> ' . sprintf( __( '%s failed.', 'geodirectory' ), 'wp_remote_post()' ) . '</span>';
										}
										?>
									</span>
									</div>
									<div class="list-group-item d-flex justify-content-between align-items-center">
										<div><?php _e( 'Remote Get', 'geodirectory' ); ?><?php echo geodir_bs5_help_tip( __( 'Used to check for plugin updates.', 'geodirectory' ) ); ?></div>
										<span>
										<?php
										if ( $environment['remote_get_successful'] ) {
											echo '<i class="fas fa-check-circle text-success"></i>';
										} else {
											echo '<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> ' . sprintf( __( '%s failed.', 'geodirectory' ), 'wp_remote_get()' ) . '</span>';
										}
										?>
									</span>
									</div>
									<?php
									break;

								case 'user_platform':
									?>
									<div class="list-group-item d-flex justify-content-between align-items-center">
										<div><?php _e( 'Platform', 'geodirectory' ); ?></div>
										<span class="badge bg-light text-dark"><?php echo esc_html( $environment['platform'] ); ?></span>
									</div>
									<div class="list-group-item d-flex justify-content-between align-items-center">
										<div><?php _e( 'Browser Name', 'geodirectory' ); ?></div>
										<span class="badge bg-light text-dark"><?php echo esc_html( $environment['browser_name'] ); ?></span>
									</div>
									<div class="list-group-item d-flex justify-content-between align-items-center">
										<div><?php _e( 'Browser Version', 'geodirectory' ); ?></div>
										<span class="badge bg-light text-dark"><?php echo esc_html( $environment['browser_version'] ); ?></span>
									</div>
									<div class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
										<div><?php _e( 'User Agent', 'geodirectory' ); ?></div>
										<span class="font-monospace text-muted text-break text-start"><?php echo esc_html( $environment['user_agent'] ); ?></span>
									</div>
									<?php
									break;

								case 'settings':
									?>
									<div class="list-group-item d-flex justify-content-between align-items-center">
										<div><?php _e( 'Rest API Enabled', 'geodirectory' ); ?><?php echo geodir_bs5_help_tip( __( 'Does site have REST API enabled?', 'geodirectory' ) ); ?></div>
										<span><?php echo $settings['api_enabled'] ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-minus-circle text-muted"></i>'; ?></span>
									</div>
									<div class="list-group-item d-flex justify-content-between align-items-center">
										<div><?php _e( 'Max Upload File Size (MB)', 'geodirectory' ); ?><?php echo geodir_bs5_help_tip( __( 'Max upload file size in MB.', 'geodirectory' ) ); ?></div>
										<span class="badge bg-secondary"><?php echo esc_html( $settings['upload_max_filesize'] ); ?></span>
									</div>
									<div class="list-group-item d-flex justify-content-between align-items-center">
										<div><?php _e( 'New Listing Default Status', 'geodirectory' ); ?><?php echo geodir_bs5_help_tip( __( 'The post status a new listing will get when submitted from the frontend.', 'geodirectory' ) ); ?></div>
										<span class="badge bg-info"><?php echo esc_html( $settings['default_status'] ); ?></span>
									</div>
									<div class="list-group-item d-flex justify-content-between align-items-center">
										<div><?php _e( 'Google Maps API KEY', 'geodirectory' ); ?><?php echo geodir_bs5_help_tip( __( 'This is a requirement to use Google Maps.', 'geodirectory' ) ); ?></div>
										<span><?php echo $settings['maps_api_key'] ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-minus-circle text-muted"></i>'; ?></span>
									</div>
									<div class="list-group-item d-flex justify-content-between align-items-center">
										<div><?php _e( 'Set Default Location', 'geodirectory' ); ?></div>
										<span><?php echo $settings['default_location'] ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-minus-circle text-muted"></i>'; ?></span>
									</div>
									<?php
									break;

								case 'security':
									?>
									<div class="list-group-item d-flex justify-content-between align-items-center">
										<div><?php _e( 'Secure Connection (HTTPS)', 'geodirectory' ); ?><?php echo geodir_bs5_help_tip( __( 'Is the connection to your site secure?', 'geodirectory' ) ); ?></div>
										<span>
										<?php if ( $security['secure_connection'] ) : ?>
											<i class="fas fa-check-circle text-success"></i>
										<?php else : ?>
											<span class="badge bg-danger"><i class="fas fa-exclamation-triangle me-1"></i> <?php _e( 'Your site is not using HTTPS.', 'geodirectory' ); ?></span>
										<?php endif; ?>
									</span>
									</div>
									<div class="list-group-item d-flex justify-content-between align-items-center">
										<div><?php _e( 'Hide Errors from Visitors', 'geodirectory' ); ?><?php echo geodir_bs5_help_tip( __( 'Error messages can contain sensitive information and should be hidden from untrusted visitors.', 'geodirectory' ) ); ?></div>
										<span>
										<?php if ( $security['hide_errors'] ) : ?>
											<i class="fas fa-check-circle text-success"></i>
										<?php else : ?>
											<span class="badge bg-danger"><i class="fas fa-exclamation-triangle me-1"></i> <?php _e( 'Error messages should not be shown to visitors.', 'geodirectory' ); ?></span>
										<?php endif; ?>
									</span>
									</div>
									<?php
									break;

								case 'theme':
									?>
									<div class="list-group-item d-flex justify-content-between align-items-center">
										<div><?php _e( 'Name', 'geodirectory' ); ?><?php echo geodir_bs5_help_tip( __( 'The name of the current active theme.', 'geodirectory' ) ); ?></div>
										<span><?php echo esc_html( $theme['name'] ); ?></span>
									</div>
									<div class="list-group-item d-flex justify-content-between align-items-center">
										<div><?php _e( 'Version', 'geodirectory' ); ?><?php echo geodir_bs5_help_tip( __( 'The installed version of the current active theme.', 'geodirectory' ) ); ?></div>
										<span>
										<?php
										echo esc_html( $theme['version'] );
										if ( version_compare( $theme['version'], $theme['latest_verison'], '<' ) ) {
											echo ' <span class="badge bg-danger">' . sprintf( __( '%s available', 'geodirectory' ), esc_html( $theme['latest_verison'] ) ) . '</span>';
										}
										?>
									</span>
									</div>
									<div class="list-group-item d-flex justify-content-between align-items-center">
										<div><?php _e( 'Author URL', 'geodirectory' ); ?><?php echo geodir_bs5_help_tip( __( 'The theme developers URL.', 'geodirectory' ) ); ?></div>
										<a href="<?php echo esc_url( $theme['author_url'] ); ?>" target="_blank"><?php echo esc_html( $theme['author_url'] ); ?></a>
									</div>
									<div class="list-group-item d-flex justify-content-between align-items-center">
										<div><?php _e( 'Child Theme', 'geodirectory' ); ?><?php echo geodir_bs5_help_tip( __( 'Displays whether or not the current theme is a child theme.', 'geodirectory' ) ); ?></div>
										<span>
										<?php echo $theme['is_child_theme'] ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-times-circle text-danger"></i>'; ?>
									</span>
									</div>
									<?php if ( $theme['is_child_theme'] ) : ?>
									<div class="list-group-item d-flex justify-content-between align-items-center bg-light">
										<div><?php _e( 'Parent Theme Name', 'geodirectory' ); ?></div>
										<span><?php echo esc_html( $theme['parent_name'] ); ?></span>
									</div>
									<div class="list-group-item d-flex justify-content-between align-items-center bg-light">
										<div><?php _e( 'Parent Theme Version', 'geodirectory' ); ?></div>
										<span>
										<?php
										echo esc_html( $theme['parent_version'] );
										if ( version_compare( $theme['parent_version'], $theme['parent_latest_verison'], '<' ) ) {
											echo ' <span class="badge bg-danger">' . sprintf( __( '%s available', 'geodirectory' ), esc_html( $theme['parent_latest_verison'] ) ) . '</span>';
										}
										?>
									</span>
									</div>
									<div class="list-group-item d-flex justify-content-between align-items-center bg-light">
										<div><?php _e( 'Parent Theme Author URL', 'geodirectory' ); ?></div>
										<a href="<?php echo esc_url( $theme['parent_author_url'] ); ?>" target="_blank"><?php echo esc_html( $theme['parent_author_url'] ); ?></a>
									</div>
								<?php endif; ?>
									<div class="list-group-item d-flex justify-content-between align-items-center">
										<div><?php _e( 'GeoDirectory Support', 'geodirectory' ); ?><?php echo geodir_bs5_help_tip( __( 'Displays whether or not the current active theme declares GeoDirectory support.', 'geodirectory' ) ); ?></div>
										<span>
										<?php if ( ! $theme['has_geodirectory_support'] ) {
											echo '<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> ' . __( 'Not declared', 'geodirectory' ) . '</span>';
										} else {
											echo '<i class="fas fa-check-circle text-success"></i>';
										} ?>
									</span>
									</div>
									<?php
									break;

								case 'templates':
									?>
									<div class="list-group-item">
										<h6 class="mb-2"><?php _e( 'Template Overrides', 'geodirectory' ); ?><?php echo geodir_bs5_help_tip( __( 'This section shows any files that are overriding the default GeoDirectory template pages.', 'geodirectory' ) ); ?></h6>
										<?php if ( ! empty( $theme['overrides'] ) ) : ?>
											<ul class="list-unstyled">
												<?php foreach ( $theme['overrides'] as $override ) : ?>
													<li>
														<code class="font-monospace"><?php echo esc_html( $override['file'] ); ?></code>
														<?php
														if ( $override['core_version'] && ( empty( $override['version'] ) || version_compare( $override['version'], $override['core_version'], '<' ) ) ) {
															$current_version = $override['version'] ? $override['version'] : '-';
															echo '<br><small class="ms-3 text-danger"><i class="fas fa-exclamation-triangle"></i> ' . sprintf( __( 'Version %1$s is out of date. The core version is %2$s', 'geodirectory' ), '<strong>' . $current_version . '</strong>', '<strong>' . $override['core_version'] . '</strong>' ) . '</small>';
														}
														?>
													</li>
												<?php endforeach; ?>
											</ul>
										<?php else: ?>
											<p class="text-muted mb-0"><?php _e( 'No template overrides found.', 'geodirectory' ); ?></p>
										<?php endif; ?>
									</div>
									<?php if ( true === $theme['has_outdated_templates'] ) : ?>
									<div class="list-group-item list-group-item-warning d-flex justify-content-between align-items-center">
										<div class="fw-bold"><?php _e( 'Outdated Templates Detected', 'geodirectory' ); ?></div>
										<i class="fas fa-exclamation-triangle fa-lg text-warning"></i>
									</div>
								<?php endif; ?>
									<?php
									break;

								case 'pages':
									foreach ( $pages as $page ) {
										$error_msg = '';
										if ( ! $page['page_set'] ) {
											$error_msg = __( 'Page not set', 'geodirectory' );
										} elseif ( ! $page['page_exists'] ) {
											$error_msg = __( 'Page ID is set, but the page does not exist', 'geodirectory' );
										} elseif ( ! $page['page_visible'] ) {
											$error_msg = sprintf( __( 'Page visibility should be <a href="%s" target="_blank">public</a>', 'geodirectory' ), 'https://codex.wordpress.org/Content_Visibility' );
										} elseif ( $page['shortcode_required'] && ! $page['shortcode_present'] ) {
											$error_msg = __( 'Page does not contain the required shortcode.', 'geodirectory' );
										}

										$page_name = $page['page_id'] ? '<a href="' . get_edit_post_link( $page['page_id'] ) . '">' . esc_html( $page['page_name'] ) . '</a>' : esc_html( $page['page_name'] );
										?>
										<div class="list-group-item d-flex justify-content-between align-items-center">
											<div><?php echo $page_name; ?><?php echo geodir_bs5_help_tip( sprintf( __( 'The URL of your %s page.', 'geodirectory' ), esc_html( $page['page_name'] ) ) ); ?></div>
											<span>
											<?php if ( $error_msg ) : ?>
												<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> <?php echo $error_msg; ?></span>
											<?php else : ?>
												<span class="badge bg-success font-monospace"><?php echo '#' . absint( $page['page_id'] ) . ' &rarr; ' . str_replace( home_url(), '', get_permalink( $page['page_id'] ) ); ?></span>
											<?php endif; ?>
										</span>
										</div>
										<?php
									}
									break;

								case 'active_plugins':
									foreach ( $active_plugins as $plugin ) {
										if ( empty( $plugin['name'] ) ) continue;

										$plugin_name = ! empty( $plugin['url'] ) ? '<a href="' . esc_url( $plugin['url'] ) . '" target="_blank">' . esc_html( $plugin['name'] ) . '</a>' : esc_html( $plugin['name'] );
										$version_string = esc_html( $plugin['version'] );
										if ( ! empty( $plugin['latest_verison'] ) && version_compare( $plugin['latest_verison'], $plugin['version'], '>' ) ) {
											$version_string .= ' <span class="badge bg-danger">' . sprintf( esc_html__( '%s available', 'geodirectory' ), $plugin['latest_verison'] ) . '</span>';
										}
										$network_string = $plugin['network_activated'] ? ' <span class="badge bg-info">' . __( 'Network', 'geodirectory' ) . '</span>' : '';
										?>
										<div class="list-group-item d-flex justify-content-between align-items-center">
											<div>
												<div class="fw-bold"><?php echo $plugin_name; ?></div>
												<small class="text-muted"><?php printf( __( 'by %s', 'geodirectory' ), $plugin['author_name'] ); ?></small>
											</div>
											<div><?php echo $version_string . $network_string; ?></div>
										</div>
										<?php
									}
									break;

								case 'database':
									?>
									<div class="list-group-item d-flex justify-content-between align-items-center">
										<div><?php _e( 'GD Database Version', 'geodirectory' ); ?><?php echo geodir_bs5_help_tip( __( 'The DB version, should match the GeoDirectory version.', 'geodirectory' ) ); ?></div>
										<span class="badge bg-secondary"><?php echo esc_html( $database['geodirectory_db_version'] ); ?></span>
									</div>
									<div class="list-group-item d-flex justify-content-between align-items-center">
										<div><?php _e( 'Database Prefix', 'geodirectory' ); ?></div>
										<span>
										<?php
										if ( strlen( $database['database_prefix'] ) > 20 ) {
											echo '<span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle me-1"></i> ' . sprintf( __( '%s - Prefix is long.', 'geodirectory' ), esc_html( $database['database_prefix'] ) ) . '</span>';
										} else {
											echo '<span class="badge bg-success font-monospace">' . esc_html( $database['database_prefix'] ) . '</span>';
										}
										?>
									</span>
									</div>
									<div class="list-group-item d-flex justify-content-between align-items-center">
										<div><?php _e( 'Total Database Size', 'geodirectory' ); ?></div>
										<span class="badge bg-info"><?php printf( '%.2fMB', $database['database_size']['data'] + $database['database_size']['index'] ); ?></span>
									</div>
									<div class="list-group-item d-flex justify-content-between align-items-center">
										<div><?php _e( 'Database Data Size', 'geodirectory' ); ?></div>
										<span class="badge bg-light text-dark"><?php printf( '%.2fMB', $database['database_size']['data'] ); ?></span>
									</div>
									<div class="list-group-item d-flex justify-content-between align-items-center">
										<div><?php _e( 'Database Index Size', 'geodirectory' ); ?></div>
										<span class="badge bg-light text-dark"><?php printf( '%.2fMB', $database['database_size']['index'] ); ?></span>
									</div>
									<div class="list-group-item">
										<h6 class="mb-2"><?php _e( 'GeoDirectory Tables', 'geodirectory' ); ?></h6>
										<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-2">
											<?php foreach ( $database['database_tables']['geodirectory'] as $table => $table_data ) : ?>
												<div class="col">
													<div class="p-2 border rounded <?php echo $table_data ? 'bg-light' : 'bg-danger-soft'; ?>">
														<div class="d-flex justify-content-between align-items-center">
															<code class="font-monospace text-break"><?php echo esc_html( $table ); ?></code>
															<?php if( ! $table_data ) : ?>
																<span class="badge bg-danger"><i class="fas fa-times-circle"></i></span>
															<?php endif; ?>
														</div>
														<?php if( $table_data ) : ?>
															<small class="text-muted d-block"><?php printf( __( 'Data: %.2fMB | Index: %.2fMB', 'geodirectory' ), geodir_format_decimal( $table_data['data'], 2 ), geodir_format_decimal( $table_data['index'], 2 ) ); ?></small>
														<?php endif; ?>
													</div>
												</div>
											<?php endforeach; ?>
										</div>
									</div>
									<?php
									break;

								case 'post_types':
									foreach ( $post_type_counts as $post_type ) {
										?>
										<div class="list-group-item d-flex justify-content-between align-items-center">
											<span class="text-capitalize"><?php echo esc_html( str_replace( '_', ' ', $post_type->type ) ); ?></span>
											<span class="badge bg-primary rounded-pill"><?php echo absint( $post_type->count ); ?></span>
										</div>
										<?php
									}
									break;
							}
							?>
						</div>
					</div>
				</div>
			</div>
		<?php endforeach; ?>

	</div>

	<?php do_action( 'geodir_system_status_report' ); ?>

</div>

<!-- JavaScript to handle the copy-to-clipboard functionality and initialize tooltips -->
<script>
	/**
	 * Initializes all JavaScript functionality for the GeoDirectory Status Report page.
	 * This function should be called after the report's HTML has been loaded into the DOM,
	 * for example, in an AJAX success callback.
	 */
	function initializeGeoDirectoryStatusReport() {
		console.log( 'Initializing GeoDirectory Status Report' );
		// Find the main container for the report. If it's not here, exit.
		const reportContainer = document.getElementById('statusReportAccordion');
		if (!reportContainer) {
			console.error('GeoDirectory Status Report container not found. JS initialization failed.');
			return;
		}

		// --- System Report Generation and Copying ---
		const copyButton = document.getElementById('copy-for-support');
		const reportTextarea = document.getElementById('debug-report-textarea');
		const copySuccessMessage = document.getElementById('copy-success');
		const copyErrorMessage = document.getElementById('copy-error');
		const collapseReportEl = document.getElementById('collapseReport');

		// This function generates the plain text report from the data on the page.
		const generateDebugReport = function() {
			let report = '';

			reportContainer.querySelectorAll('.accordion-item').forEach(item => {
				const header = item.querySelector('.accordion-button');
				const body = item.querySelector('.accordion-body');

				if (header) {
					report += '### ' + header.innerText.trim() + ' ###\n\n';
				}

				if (body) {
					body.querySelectorAll('.list-group-item').forEach(listItem => {
						// This is a simplified text extraction. It works for most cases here.
						// It tries to separate the label from the value.
						const children = Array.from(listItem.children);
						const labelEl = children.find(el => el.tagName === 'DIV');
						const valueEl = children.find(el => el.tagName === 'SPAN' || el.tagName === 'A' || el.tagName === 'I');

						if (labelEl && valueEl && labelEl.innerText && valueEl.innerText) {
							let label = labelEl.innerText.trim();
							let value = valueEl.innerText.trim().replace(/\s\s+/g, ' '); // Clean up whitespace
							report += label + ': ' + value + '\n';
						} else {
							// Fallback for items that don't fit the key:value pattern like templates/plugins
							report += listItem.innerText.trim().replace(/\s\s+/g, ' ') + '\n';
						}
					});
				}
				report += '\n';
			});

			return report;
		};

		// When the collapse is shown, generate the report text.
		if (collapseReportEl) {
			// Remove any existing listeners to prevent duplicates on re-init
			const newCollapseEl = collapseReportEl.cloneNode(true);
			collapseReportEl.parentNode.replaceChild(newCollapseEl, collapseReportEl);

			newCollapseEl.addEventListener('show.bs.collapse', function () {
				reportTextarea.value = generateDebugReport();
			});
		}


		// Handle the copy button click
		if (copyButton) {
			// Use event delegation on a parent or re-clone to ensure the listener is fresh
			const newCopyButton = copyButton.cloneNode(true);
			copyButton.parentNode.replaceChild(newCopyButton, copyButton);

			newCopyButton.addEventListener('click', function(e) {
				e.preventDefault();

				// Hide messages before trying to copy
				copySuccessMessage.classList.add('d-none');
				copyErrorMessage.classList.add('d-none');

				reportTextarea.select();
				try {
					// Use the modern clipboard API if available
					if(navigator.clipboard) {
						navigator.clipboard.writeText(reportTextarea.value).then(function() {
							copySuccessMessage.classList.remove('d-none');
							setTimeout(() => copySuccessMessage.classList.add('d-none'), 2000);
						}, function() {
							// Fallback for API failure
							throw new Error('Clipboard API failed');
						});
					} else {
						// Legacy fallback
						if(!document.execCommand('copy')) {
							throw new Error('execCommand failed');
						}
						copySuccessMessage.classList.remove('d-none');
						setTimeout(() => copySuccessMessage.classList.add('d-none'), 2000);
					}
				} catch (err) {
					console.error('Copy to clipboard failed:', err);
					copyErrorMessage.classList.remove('d-none');
				}
			});
		}
	}

	// You can now call initializeGeoDirectoryStatusReport() after your AJAX call completes.
	// For example:
	//
	// fetch('your-ajax-url')
	//   .then(response => response.text())
	//   .then(html => {
	//     document.getElementById('target-container').innerHTML = html;
	//     initializeGeoDirectoryStatusReport(); // <-- Initialize the JS here
	//   });

	// If you are loading this script in a context where the DOM might already be ready,
	// you can self-invoke it for non-AJAX scenarios.
	if (document.readyState === 'complete' || document.readyState === 'interactive') {
		initializeGeoDirectoryStatusReport();
	} else {
		document.addEventListener('DOMContentLoaded', initializeGeoDirectoryStatusReport);
	}
</script>

<style>
	/* Custom styles to refine the Bootstrap layout */
	.accordion-button:not(.collapsed) {
		background-color: rgba(var(--bs-primary-rgb), 0.05);
		color: var(--bs-body-color);
	}
	.accordion-button:focus {
		box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb), 0.2);
	}
	.bg-danger-soft {
		background-color: rgba(var(--bs-danger-rgb), 0.1);
	}
	.font-monospace {
		font-size: 0.875em;
	}
</style>
