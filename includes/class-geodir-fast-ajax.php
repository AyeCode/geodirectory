<?php
/**
 * GeoDir_Fast_AJAX class
 *
 * @package GeoDirectory
 * @since   2.2.7
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * GeoDir_Fast_AJAX class.
 */
class GeoDir_Fast_AJAX {

	/**
	 * Init.
	 */
	public static function init() {
		// Disable GD Fast AJAX
		if ( defined( 'GEODIR_DISABLE_AJAX' ) && GEODIR_DISABLE_AJAX ) {
			return;
		}

		if ( ! empty( $_REQUEST['gd-ajax'] ) ) {
			self::define_ajax();

			add_filter( 'option_active_plugins', array( __CLASS__, 'allowed_plugins' ), 11, 1 );

			if ( ! empty( $_REQUEST['gd-no-auth'] ) ) {
				// Don't needs authentication (faster).
				add_action( 'plugins_loaded', array( __CLASS__, 'do_gd_ajax' ), 999 );
			} else {
				// Needs authentication.
				add_action( 'init', array( __CLASS__, 'do_gd_ajax' ), 999 );
			}
		}
	}

	/**
	 * Set GD AJAX constant and headers.
	 */
	public static function define_ajax() {
		/**
		 * Define constant so we can check if the AJAX request.
		 *
		 * @since 2.2.7
		 */
		if ( ! defined( 'DOING_AJAX' ) ) {
			define( 'DOING_AJAX', true );
		}

		/**
		 * Define constant so we can check if the GD AJAX request.
		 *
		 * @since 2.2.7
		 */
		if ( ! defined( 'GEODIR_DOING_AJAX' ) ) {
			define( 'GEODIR_DOING_AJAX', true );
		}

		if ( ( defined( 'DOING_AJAX' ) && ! WP_DEBUG ) || ( WP_DEBUG && ! WP_DEBUG_DISPLAY ) ) {
			/** @scrutinizer ignore-unhandled */ @ini_set( 'display_errors', 0 ); // Turn off display_errors during AJAX events to prevent malformed JSON.
		}

		$GLOBALS['wpdb']->hide_errors();
	}

	/**
	 * Fire AJAX actions.
	 *
	 * @since 2.2.7
	 */
	public static function do_gd_ajax() {
		$action = ! empty( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : '';

		if ( $action ) {
			self::gd_ajax_headers();
			do_action( 'geodir_ajax_' . $action );
			wp_die();
		}

		if ( ! self::is_rest_request() ) {
			exit;
		}
	}

	/**
	 * Send headers for GD Ajax Requests.
	 *
	 * @since 2.2.7
	 */
	private static function gd_ajax_headers() {
		if ( ! headers_sent() ) {
			send_origin_headers();
			send_nosniff_header();
			nocache_headers();
			/** @scrutinizer ignore-unhandled */ header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
			/** @scrutinizer ignore-unhandled */ header( 'X-Robots-Tag: noindex' );
			status_header( 200 );
		}
	}

	/**
	 * Get allowed plugins that required on GD requests.
	 *
	 * @since 2.2.7
	 */
	public static function allowed_plugins( $plugins = array() ) {
		global $geodir_ajax_allowed_plugins;

		if ( ! empty( $geodir_ajax_allowed_plugins ) && is_array( $geodir_ajax_allowed_plugins ) ) {
			return $geodir_ajax_allowed_plugins;
		}

		$not_allowed_plugins = array(
			'geodir_ajax_duplicate_alert/geodir_ajax_duplicate_alert.php',
			'geodir_recaptcha/geodir_recaptcha.php',
			'geodir_social_importer/geodir_social_importer.php',
			'geodir-converter/geodir-converter.php',
			'geodir-google-analytics/geodir-google-analytics.php',
			'geodir-wp-all-import/geodir-wp-all-import.php'
		);

		$allowed_plugins = array();
		foreach ( $plugins as $plugin ) {
			// Skip not allowed plugins 
			if ( in_array( $plugin, $not_allowed_plugins ) ) {
				continue;
			}

			if ( strpos( $plugin, 'geodir' ) === 0 || strpos( $plugin, 'geodirectory' ) !== false || strpos( $plugin, 'elementor' ) !== false ) {
				$allowed_plugins[] = $plugin;
			}
		}

		$geodir_ajax_allowed_plugins = $allowed_plugins;

		// Logs
		//self::_log( $plugins, 'plugins', __FILE__, __LINE__ );
		//self::_log( $allowed_plugins, 'allowed_plugins', __FILE__, __LINE__ );

		return $allowed_plugins;
	}

	/**
	 * A function to log GD errors no matter the type given.
	 *
	 * @todo Remove once done.
	 */
	public static function _log( $log, $title = '', $file = '', $line = '', $exit = false ) {
		$should_log = true;

		if ( $should_log ) {
			$label = '';
			if ( $file && $file !== '' ) {
				$label .= basename( $file ) . ( $line ? '(' . $line . ')' : '' );
			}

			if ( $title && $title !== '' ) {
				$label = $label !== '' ? $label . ' ' : '';
				$label .= $title . ' ';
			}

			$label = $label !== '' ? trim( $label ) . ' : ' : '';

			if ( is_array( $log ) || is_object( $log ) ) {
				error_log( $label . print_r( $log, true ) );
			} else {
				error_log( $label . $log );
			}

			if ( $exit ) {
				exit;
			}
		}
	}

	public static function is_rest_request() {
		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return false;
		}

		$rest_prefix = trailingslashit( rest_get_url_prefix() );

		return ( false !== strpos( $_SERVER['REQUEST_URI'], $rest_prefix . 'geodir/' ) );
	}
}

GeoDir_Fast_AJAX::init();
