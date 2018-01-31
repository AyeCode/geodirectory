<?php
/**
 * Upgrade related functions.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 */



// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

global $wpdb;

if (get_option('geodirectory_db_version') != GEODIRECTORY_VERSION) {
    /**
     * Include custom database table related functions.
     *
     * @since 1.0.0
     * @package GeoDirectory
     */
    add_action('plugins_loaded', 'geodirectory_upgrade_all', 10);

    // Upgrade old options to new options before loading the rest GD options.
    if (GEODIRECTORY_VERSION <= '2.0.0') {
        add_action('init', 'geodir_upgrade_200');
    }

    add_action('init', 'gd_fix_cpt_rewrite_slug', 11);// this needs to be kept for a few versions

    update_option('geodirectory_db_version', GEODIRECTORY_VERSION);

}
