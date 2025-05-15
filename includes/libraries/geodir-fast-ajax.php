<?php
/**
 * Plugin Name: GeoDirectory Fast AJAX
 * Plugin URI: https://wpgeodirectory.com/
 * Description: Speed up AJAX requests to improve AJAX performance within GeoDirectory plugins.
 * Version: 1.0.0
 * Author: AyeCode Ltd
 * Author URI: https://ayecode.io/
 * License: GPL-2.0+
 * License: GNU General Public License v3 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Requires at least: 5.0
 * Tested up to: 6.7
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Define version constant.
 *
 * @since 1.0.0
 */
if ( ! defined( 'GEODIR_FAST_AJAX_VERSION' ) ) {
	define( 'GEODIR_FAST_AJAX_VERSION', '1.0.0' );
}

if ( file_exists ( WP_PLUGIN_DIR . '/geodirectory/includes/class-geodir-fast-ajax.php' ) ) {
	/**
	 * Define constant so we can check if the plugin is active.
	 *
	 * @since 1.0.0
	 */
	if ( ! defined( 'GEODIR_FAST_AJAX' ) ) {
		define( 'GEODIR_FAST_AJAX', true );
	}

	/** Loads GeoDirectory Fast AJAX class file */
	require_once WP_PLUGIN_DIR . '/geodirectory/includes/class-geodir-fast-ajax.php';
}
