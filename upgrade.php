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

/**
 * Layout grid view 1 added which replaces layout=1 so we change current layout=1 to layout=0 (back to list view)
 */
function geodir_upgrade_20060() {

	// Archive page
	$archive_page_id = geodir_archive_page_id();
	if($archive_page_id){
		$archive_page_content = get_post_field('post_content', $archive_page_id);
		if (strpos($archive_page_content, '[gd_loop layout=1]') !== false) {
			$my_post = array(
				'ID'           => $archive_page_id,
				'post_content' => str_replace('[gd_loop layout=1]','[gd_loop layout=0]',$archive_page_content),
			);
			wp_update_post( $my_post );
		}
	}

	// Search page
	$search_page_id = geodir_search_page_id();
	if($search_page_id){
		$search_page_content = get_post_field('post_content', $search_page_id);
		if (strpos($search_page_content, '[gd_loop layout=1]') !== false) {
			$my_post = array(
				'ID'           => $search_page_id,
				'post_content' => str_replace('[gd_loop layout=1]','[gd_loop layout=0]',$search_page_content),
			);
			wp_update_post( $my_post );
		}
	}
}