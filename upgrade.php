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
    add_action('plugins_loaded', 'geodir_upgrade_all', 10);

    // Upgrade old options to new options before loading the rest GD options.
    if (GEODIRECTORY_VERSION <= '2.0.0') {
        add_action('init', 'geodir_upgrade_200');
    }

    add_action('init', 'geodir_fix_cpt_rewrite_slug', 11);// this needs to be kept for a few versions

    //update_option('geodirectory_db_version', GEODIRECTORY_VERSION); // @todo is this required?

}

/**
 * Handles upgrade for all geodirectory versions.
 *
 * @since 1.0.0
 * @package GeoDirectory
 */
function geodir_upgrade_all() {
}

/**
 * Handles upgrade for all geodirectory versions.
 *
 * @since 2.0.0
 * @package GeoDirectory
 */
function geodir_upgrade_200() {
}

/**
 * Converts all GD CPT's to the new rewrite slug by removing /%gd_taxonomy% from the slug
 *
 * @since 1.5.0
 * @package GeoDirectory
 */
function geodir_fix_cpt_rewrite_slug() {
    // flush the rewrite rules
    flush_rewrite_rules();
}

/**
 * Update DB Version.
 */
function geodir_upgrade_20013() {
    global $wpdb;
	$wpdb->query("UPDATE ".GEODIR_ATTACHMENT_TABLE." SET type='post_images' WHERE type='post_image'");
}

function geodir_update_200_settings() {
	GeoDir_Admin_Upgrade::update_200_settings();
}

function geodir_update_200_fields() {
	GeoDir_Admin_Upgrade::update_200_fields();
}

function geodir_update_200_terms() {
	GeoDir_Admin_Upgrade::update_200_terms();
}

function geodir_update_200_posts() {
	GeoDir_Admin_Upgrade::update_200_posts();
}

function geodir_update_200_merge_data() {
	GeoDir_Admin_Upgrade::update_200_merge_data();
}

function geodir_update_200_db_version() {
	GeoDir_Admin_Upgrade::update_200_db_version();
}