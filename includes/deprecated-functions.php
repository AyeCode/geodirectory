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

/**
 * function for post type settings.
 *
 * @since 1.0.0
 * @package GeoDirectory
 */
function geodir_post_type_setting_fun() {
    $post_type_arr = array();

    $post_types = geodir_get_posttypes('object');

    foreach ($post_types as $key => $post_types_obj) {
        $post_type_arr[$key] = $post_types_obj->labels->singular_name;
    }
    return $post_type_arr;
}

// Should be removed as no longer in use within plugins/themes.
if ( !function_exists( 'is_allow_user_register' ) ) {
    /**
     * Checks whether the site allowing user registration or not.
     *
     * @since 1.0.0
     * @package GeoDirectory
     * @return bool|string
     */
    function is_allow_user_register() {
        return get_option('users_can_register');
    }
}

/**
 * Get site email ID or site admin email ID.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @return string|mixed|void The email ID.
 */
function geodir_get_site_email_id() {
    $site_email = geodir_get_option( 'site_email' );
    
    if ( !$site_email ) {
        $site_email = get_option( 'admin_email' );
    }
    
    return apply_filters( 'geodir_get_site_email_id', $site_email );
}

if ( !function_exists( 'get_site_emailName' ) ) {
    /**
     * Get site name for sending emails.
     *
     * @since 1.0.0
     * @package GeoDirectory
     * @return string Site name.
     */
    function get_site_emailName() {
        $site_email_name = geodir_get_option( 'site_email_name' );
    
        if ( !$site_email_name ) {
            $site_email_name = get_option( 'blogname' );
        }
        
        return apply_filters( 'get_site_emailName', stripslashes( $site_email_name ) );
    }
}