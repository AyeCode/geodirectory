<?php
/**
 * Deprecated Functions
 *
 * All functions that have been deprecated.
 *
 * @package GeoDirectory
 * @since   2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * CHECK FOR OLD COMPATIBILITY PACKS AND DISABLE IF THEY ARE ACTIVE
 */
if (is_admin()) {
    if ( ! function_exists( 'is_plugin_active' ) ) {
        /**
         * Include WordPress core file so we can use core functions to check for active plugins.
         */
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }

    if ( is_plugin_active( 'geodirectory-genesis-compatibility-pack/geodir_genesis_compatibility.php' ) ) {
        deactivate_plugins( 'geodirectory-genesis-compatibility-pack/geodir_genesis_compatibility.php' );
    }

    if ( is_plugin_active( 'geodirectory-x-theme-compatibility-pack/geodir_x_compatibility.php' ) ) {
        deactivate_plugins( 'geodirectory-x-theme-compatibility-pack/geodir_x_compatibility.php' );
    }

    if ( is_plugin_active( 'geodirectory-enfold-theme-compatibility-pack/geodir_enfold_compatibility.php' ) ) {
        deactivate_plugins( 'geodirectory-enfold-theme-compatibility-pack/geodir_enfold_compatibility.php' );
    }

    if ( is_plugin_active( 'geodir_avada_compatibility/geodir_avada_compatibility.php' ) ) {
        deactivate_plugins( 'geodir_avada_compatibility/geodir_avada_compatibility.php' );
    }

    if ( is_plugin_active( 'geodir_compat_pack_divi/geodir_divi_compatibility.php' ) ) {
        deactivate_plugins( 'geodir_compat_pack_divi/geodir_divi_compatibility.php' );
    }
}

/**
 * Load geodirectory plugin textdomain.
 *
 * @since   1.4.2
 * @package GeoDirectory
 */
function geodir_load_textdomain() {
	/**
	 * Filter the plugin locale.
	 *
	 * @since   1.4.2
	 * @package GeoDirectory
	 */
	$locale = apply_filters( 'plugin_locale', get_locale(), 'geodirectory' );

	load_textdomain( 'geodirectory', WP_LANG_DIR . '/' . 'geodirectory' . '/' . 'geodirectory' . '-' . $locale . '.mo' );
	load_plugin_textdomain( 'geodirectory', false, plugin_basename( dirname( dirname( __FILE__ ) ) ) . '/geodirectory-languages' );

	/**
	 * Define language constants.
	 *
	 * @since 1.0.0
	 */
	require_once( geodir_plugin_path() . '/language.php' );

	$language_file = geodir_plugin_path() . '/db-language.php';

	// Load language string file if not created yet
	if ( ! file_exists( $language_file ) ) {
		geodirectory_load_db_language();
	}

	if ( file_exists( $language_file ) ) {
		/**
		 * Language strings from database.
		 *
		 * @since 1.4.2
		 */
		try {
			require_once( $language_file );
		} catch ( Exception $e ) {
			error_log( 'Language Error: ' . $e->getMessage() );
		}
	}
}