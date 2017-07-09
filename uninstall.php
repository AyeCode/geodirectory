<?php
/**
 * Uninstall GeoDirectory
 *
 * Uninstalling GeoDirectory deletes data, tables and options.
 *
 * @package GeoDirectory_Advance_Search_Filters
 * @since 1.6.9
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

if ( !defined( 'GEODIRECTORY_VERSION' ) ) {
    // Load plugin file.
    include_once( 'geodirectory.php' );
}

if ( geodir_get_option( 'geodir_un_geodirectory' ) ) {
    $wpdb->hide_errors();

    // Delete default data.
    geodir_delete_option( 'geodir_default_data_installed' );
}