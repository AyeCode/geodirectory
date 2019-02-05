<?php
/**
 * Debug/Status page
 *
 * @author      GeoDirectory
 * @category    Admin
 * @package     GeoDirectory/Admin/System Status
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GeoDir_Admin_Status Class.
 */
class GeoDir_Admin_Status {

	/**
	 * Handles output of the reports page in admin.
	 */
	public static function output() {
		include_once( dirname( __FILE__ ) . '/views/html-admin-page-status.php' );
	}

	/**
	 * Handles output of report.
	 */
	public static function status_report() {
		include_once( dirname( __FILE__ ) . '/views/html-admin-page-status-report.php' );
	}

	/**
	 * Handles output of tools.
	 */
	public static function status_tools() {
		$tools = self::get_tools();

		if ( ! empty( $_GET['action'] ) && ! empty( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'debug_action' ) ) {
			$tools_controller = new GeoDir_Admin_Tools();
			$action           = geodir_clean( $_GET['action'] );

			if ( array_key_exists( $action, $tools ) ) {
				$response = $tools_controller->execute_tool( $action );
			} else {
				$response = array( 'success' => false, 'message' => __( 'Tool does not exist.', 'geodirectory' ) );
			}

			if ( $response['success'] ) {
				echo '<div class="notice notice-success inline"><p>' . esc_html( $response['message'] ) . '</p></div>';
			} else {
				echo '<div class="notice notice-error inline"><p>' . esc_html( $response['message'] ) . '</p></div>';
			}
		}

		// Display message if settings settings have been saved
		if ( isset( $_REQUEST['settings-updated'] ) ) {
			echo '<div class="notice notice-success inline"><p>' . __( 'Your changes have been saved.', 'geodirectory' ) . '</p></div>';
		}

		include_once( dirname( __FILE__ ) . '/views/html-admin-page-status-tools.php' );
	}

	/**
	 * Get tools.
     *
     * @since 2.0.0
     *
	 * @return array of tools
	 */
	public static function get_tools() {
		$tools_controller = new GeoDir_Admin_Tools();
		return $tools_controller->get_tools();
	}

	/**
	 * Retrieve metadata from a file. Based on WP Core's get_file_data function.
	 * @since  2.0.0
	 * @param  string $file Path to the file
	 * @return string
	 */
	public static function get_file_version( $file ) {

		// Avoid notices if file does not exist
		if ( ! file_exists( $file ) ) {
			return '';
		}

		// We don't need to write to the file, so just open for reading.
		$fp = fopen( $file, 'r' );

		// Pull only the first 8kiB of the file in.
		$file_data = fread( $fp, 8192 );

		// PHP will close file handle, but we are good citizens.
		fclose( $fp );

		// Make sure we catch CR-only line endings.
		$file_data = str_replace( "\r", "\n", $file_data );
		$version   = '';

		if ( preg_match( '/^[ \t\/*#@]*' . preg_quote( '@version', '/' ) . '(.*)$/mi', $file_data, $match ) && $match[1] ) {
			$version = _cleanup_header_comment( $match[1] );
		}

		return $version ;
	}

	/**
	 * Scan the template files.
     *
     * @since 2.0.0
     *
	 * @param  string $template_path
	 * @return array
	 */
	public static function scan_template_files( $template_path ) {

		$files  = @scandir( $template_path );
		$result = array();

		if ( ! empty( $files ) ) {

			foreach ( $files as $key => $value ) {

				if ( ! in_array( $value, array( ".", ".." ) ) ) {

					if ( is_dir( $template_path . DIRECTORY_SEPARATOR . $value ) ) {
						$sub_files = self::scan_template_files( $template_path . DIRECTORY_SEPARATOR . $value );
						foreach ( $sub_files as $sub_file ) {
							if($value != 'index.php') {
								$result[] = $value . DIRECTORY_SEPARATOR . $sub_file;
							}
						}
					} else {
						if($value != 'index.php'){
							$result[] = $value;
						}
					}
				}
			}
		}
		return $result;
	}

	/**
	 * Get latest version of a theme by slug.
     *
     * @since 2.0.0
     *
	 * @param  object $theme WP_Theme object.
	 * @return string Version number if found.
	 */
	public static function get_latest_theme_version( $theme ) {
		include_once( ABSPATH . 'wp-admin/includes/theme.php' );

		$api = themes_api( 'theme_information', array(
			'slug'     => $theme->get_stylesheet(),
			'fields'   => array(
				'sections' => false,
				'tags'     => false,
			),
		) );

		$update_theme_version = 0;

		// Check .org for updates.
		if ( is_object( $api ) && ! is_wp_error( $api ) ) {
			if ( isset( $api->version ) ) {
				$update_theme_version = $api->version;
			} else if ( isset( $api->stable_version ) ) {
				$update_theme_version = $api->stable_version;
			}

		// Check GeoDirectory Theme Version.
		} elseif ( strstr( $theme->{'Author URI'}, 'geodirectory' ) ) {
			$theme_dir = substr( strtolower( str_replace( ' ','', $theme->Name ) ), 0, 45 );

			if ( false === ( $theme_version_data = get_transient( $theme_dir . '_version_data' ) ) ) {
				$theme_changelog = wp_safe_remote_get( 'http://dzv365zjfbd8v.cloudfront.net/changelogs/' . $theme_dir . '/changelog.txt' );
				$cl_lines  = explode( "\n", wp_remote_retrieve_body( $theme_changelog ) );
				if ( ! empty( $cl_lines ) ) {
					foreach ( $cl_lines as $line_num => $cl_line ) {
						if ( preg_match( '/^[0-9]/', $cl_line ) ) {
							$theme_date         = str_replace( '.' , '-' , trim( substr( $cl_line , 0 , strpos( $cl_line , '-' ) ) ) );
							$theme_version      = preg_replace( '~[^0-9,.]~' , '' ,stristr( $cl_line , "version" ) );
							$theme_update       = trim( str_replace( "*" , "" , $cl_lines[ $line_num + 1 ] ) );
							$theme_version_data = array( 'date' => $theme_date , 'version' => $theme_version , 'update' => $theme_update , 'changelog' => $theme_changelog );
							set_transient( $theme_dir . '_version_data', $theme_version_data , DAY_IN_SECONDS );
							break;
						}
					}
				}
			}

			if ( ! empty( $theme_version_data['version'] ) ) {
				$update_theme_version = $theme_version_data['version'];
			}
		}

		return $update_theme_version;
	}
}
