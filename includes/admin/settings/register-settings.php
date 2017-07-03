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
    $value = ! empty( $geodir_options[ $key ] ) ? $geodir_options[ $key ] : $default;
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

    if ( empty( $value ) ) {
        $remove_option = geodir_delete_option( $key );
        return $remove_option;
    }

    $options = get_option( 'geodir_settings' );

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

    $options = get_option( 'geodir_settings' );

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