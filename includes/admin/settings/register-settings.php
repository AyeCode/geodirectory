<?php
/**
 * Register Settings
 *
 * @package     GeoDirectory
 * @since       2.0.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Get theme location settings.
 *
 * @since 1.0.0
 * @package GeoDirectory
 */
function geodir_get_theme_menu_locations() {
    $nav_menus = get_registered_nav_menus();
    $menu_locations = get_nav_menu_locations();
    
    $gd_menu_locations = array();
    if ( ! empty( $menu_locations ) && is_array( $menu_locations ) ) {
        foreach ( $menu_locations as $key => $theme_location ) {
            if ( ! empty( $nav_menus ) && is_array( $nav_menus ) && array_key_exists( $key, $nav_menus ) )
                $gd_menu_locations[ $key ] = $nav_menus[ $key ];
        }
    }

    return ( ! empty( $gd_menu_locations ) ? array_unique( $gd_menu_locations ) : array() );
}

/**
 * Get an option.
 *
 * @since 2.0.0
 *
 * @global array $geodir_options Array of all the GD options.
 *
 * @param string $option  Name of option to retrieve.
 * @param mixed  $default Optional. Default value to return if the option does not exist.
 * @return mixed
 */
function geodir_get_option( $key = '', $default = false ) {
    global $geodir_options;
    //if ( isset( $geodir_options[ $key ] ) ) {
        $value = ! empty( $geodir_options[ $key ] ) ? $geodir_options[ $key ] : $default;
    /*} else { // TODO remove once all settings moved to one option
        $value = get_option( $key, $default );
        geodir_error_log( $key, '', __FILE__, __LINE__ );
    }*/
    $value = apply_filters( 'geodir_get_option', $value, $key, $default );
    return apply_filters( 'geodir_get_option_' . $key, $value, $key, $default );
}

/**
 * Update an option.
 *
 * Updates an gd setting value in both the db and the global variable.
 * Warning: Passing in an empty, false or null string value will remove
 *          the key from the geodir_options array.
 *
 * @since 2.0.0
 *
 * @global array $geodir_options Array of all the GD options.
 *
 * @param string $key The Key to update.
 * @param string|bool|int $value The value to set the key to.
 * @return boolean True if updated, false if not.
 */
function geodir_update_option( $key = '', $value = false ) {
    if ( empty( $key ) ){
        return false;
    }

    //update_option( $key, $value ); // TODO remove once all settings moved to one option

    $options = get_option( 'geodir_settings' );
    if ( empty( $options ) ) {
        $options = array();
    }

    $value = apply_filters( 'geodir_update_option', $value, $key );

    $options[ $key ] = $value;
    $updated = update_option( 'geodir_settings', $options );

    if ( $updated ){
        global $geodir_options;
        $geodir_options[ $key ] = $value;
    }

    return $updated;
}

/**
 * Remove an option.
 *
 * Removes an GD setting value in both the db and the global variable.
 *
 * @since 2.0.0
 *
 * @global array $geodir_options Array of all the GD options.
 *
 * @param string $key The Key to delete.
 * @return boolean True if removed, false if not.
 */
function geodir_delete_option( $key = '' ) {
    if ( empty( $key ) ){
        return false;
    }
    
    //delete_option( $key ); // TODO remove once all settings moved to one option

    $options = get_option( 'geodir_settings' );
    if ( empty( $options ) ) {
        $options = array();
    }

    if ( isset( $options[ $key ] ) ) {
        unset( $options[ $key ] );
    }

    $updated = update_option( 'geodir_settings', $options );

    if ( $updated ){
        global $geodir_options;
        $geodir_options = $options;
    }

    return $updated;
}

/**
 * Get GD Settings.
 *
 * Retrieves all plugin settings.
 *
 * @since 2.0.0
 *
 * @return array GD settings
 */
function geodir_get_settings() {
    $settings = get_option( 'geodir_settings' );
    
    if ( empty( $settings ) ) {
        // Update old settings with new single option.
        $settings = array();
        
        update_option( 'geodir_settings', $settings );
    }
    
    return apply_filters( 'geodir_get_settings', $settings );
}

/**
 * Retrieve the array of plugin settings
 *
 * @since 1.8
 * @return array
*/
function geodir_get_registered_settings() {
    global $geodir_settings;
    
    /**
     * Contains settings array for general tab.
     *
     * @since 1.0.0
     * @package GeoDirectory
     */
    include_once( 'general_settings_array.php' );
    /**
     * Contains settings array for design tab.
     *
     * @since 1.0.0
     * @package GeoDirectory
     */
    include_once( 'design_settings_array.php' );
    /**
     * Contains settings array for notifications tab.
     *
     * @since 1.0.0
     * @package GeoDirectory
     */
    include_once( 'notifications_settings_array.php' );
    /**
     * Contains settings array for permalink tab.
     *
     * @since 1.0.0
     * @package GeoDirectory
     */
    include_once( 'permalink_settings_array.php' );
    /**
     * Contains settings array for title / meta tab.
     *
     * @since 1.5.4
     * @package GeoDirectory
     */
    include_once( 'title_meta_settings_array.php' );

    return apply_filters( 'geodir_registered_settings', $geodir_settings );
}

/**
 * Retrieve settings tabs.
 *
 * @since 2.0.0
 *
 * @return array $tabs
 */
function geodir_get_settings_tabs() {
    $tabs = array();
    
    $tabs = apply_filters('geodir_settings_tabs_array', $tabs);

    return $tabs;
}

/**
 * Retrieve settings tabs.
 *
 * @since 2.0.0
 * @return array $section.
 */
function geodir_get_settings_tab_subtabs( $tab = false ) {
    $tabs     = false;
    $sections = geodir_get_registered_settings_sections();

    if( $tab && ! empty( $sections[ $tab ] ) ) {
        $tabs = $sections[ $tab ];
    } else if ( $tab ) {
        $tabs = false;
    }

    return $tabs;
}

/**
 * Get the settings sections for each tab.
 * Uses a static to avoid running the filters on every request to this function.
 *
 * @since  2.0.0
 * @return array Array of tabs and sections.
 */
function geodir_get_registered_settings_sections() {
    static $sections = false;

    if ( false !== $sections ) {
        return $sections;
    }

    $sections = array();

    $sections = apply_filters( 'geodir_settings_sections', $sections );

    return $sections;
}

function geodir_core_option_names() {
    global $wpdb;
    
    $settings = geodir_get_registered_settings();
    $option_names = array();
    
    foreach ( $settings as $section => $options ) {
        foreach ( $options as $option ) {
            if ( !empty( $option['id'] ) ) {
                $option_name = $option['id'];
                $type = !empty( $option['type'] ) ? $option['type'] : '';
                
                if ( $type == 'image_width' ) {
                    $option_names[] = $option_name . '_width';
                    $option_names[] = $option_name . '_height';
                    $option_names[] = $option_name . '_crop';
                } else {
                    $option_names[] = $option_name;
                }
            }
        }
    }
    
    $custom_options = array( 'geodir_un_geodirectory', 'geodir_default_data_installed', 'geodir_default_data_installed_1.2.8', 'geodir_theme_location_nav', 'geodir_exclude_post_type_on_map', 'geodir_exclude_cat_on_map', 'geodir_exclude_cat_on_map_upgrade', 'geodir_default_map_language', 'geodir_default_map_search_pt', 'avada_nag', 'gd_convert_custom_field_display', 'gd_facebook_button', 'gd_ga_access_token', 'gd_ga_refresh_token', 'gd_google_button', 'gd_search_dist', 'gd_term_icons', 'gd_theme_compats', 'gd_tweet_button', 'geodir_changes_in_custom_fields_table', 'geodir_default_location', 'geodir_disable_yoast_meta', 'geodir_ga_client_id', 'geodir_ga_client_secret', 'geodir_ga_tracking_code', 'geodir_gd_uids', 'geodir_global_review_count', 'geodir_listing_page', 'geodir_post_types', 'geodir_remove_unnecessary_fields', 'geodir_remove_url_seperator', 'geodir_set_post_attachments', 'geodir_sidebars', 'geodir_taxonomies', 'geodir_use_php_sessions', 'geodir_wpml_disable_duplicate', 'geodirectory_list_thumbnail_size', 'ptthemes_auto_login', 'ptthemes_listing_preexpiry_notice_days', 'ptthemes_logoin_page_content', 'ptthemes_reg_page_content', 'theme_compatibility_setting' );
    
    if ( version_compare( GEODIRECTORY_VERSION, '2.0.0', '<' ) ) {
        $results = $wpdb->get_results( "SELECT option_name FROM " . $wpdb->options . " WHERE option_name LIKE 'geodir_un_%' OR option_name LIKE '%tax_meta_%' OR option_name LIKE 'geodir_theme_location_nav_%'" );
        if ( !empty( $results ) ) {
            foreach ( $results as $row ) {
                $custom_options[] = $row->option_name;
            }
        }
    } else {
        $custom_options[] = 'geodir_theme_location_nav_' . geodir_wp_theme_name();
    }
    
    $option_names = array_merge( $option_names, $custom_options );
    
    $option_names = apply_filters( 'geodir_all_option_names', $option_names );
    $option_names = !empty( $option_names ) ? array_unique( $option_names ) : array();
    
    return $option_names;
}