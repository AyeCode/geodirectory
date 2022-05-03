<?php
/**
 * Upgrade related functions.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

if ( get_option( 'geodirectory_db_version' ) !== GEODIRECTORY_VERSION ) {
	/**
	 * Include custom database table related functions.
	 *
	 * @since 1.0.0
	 * @package GeoDirectory
	 */
	add_action( 'plugins_loaded', 'geodir_upgrade_all', 10 );

	add_action( 'init', 'geodir_fix_cpt_rewrite_slug', 11 );// this needs to be kept for a few versions.

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
	// flush the rewrite rules.
	flush_rewrite_rules();
}

/**
 * Update DB Version.
 */
function geodir_upgrade_20013() {
	global $wpdb;
	$wpdb->query( 'UPDATE ' . GEODIR_ATTACHMENT_TABLE . " SET type='post_images' WHERE type='post_image'" );
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
	if ( $archive_page_id ) {
		$archive_page_content = get_post_field( 'post_content', $archive_page_id );
		if ( strpos( $archive_page_content, '[gd_loop layout=1]' ) !== false ) {
			$my_post = array(
				'ID'           => $archive_page_id,
				'post_content' => str_replace( '[gd_loop layout=1]', '[gd_loop layout=0]', $archive_page_content ),
			);
			wp_update_post( $my_post );
		}
	}

	// Search page
	$search_page_id = geodir_search_page_id();
	if ( $search_page_id ) {
		$search_page_content = get_post_field( 'post_content', $search_page_id );
		if ( strpos( $search_page_content, '[gd_loop layout=1]' ) !== false ) {
			$my_post = array(
				'ID'           => $search_page_id,
				'post_content' => str_replace( '[gd_loop layout=1]', '[gd_loop layout=0]', $search_page_content ),
			);
			wp_update_post( $my_post );
		}
	}
}

/**
 * Change country name Russian Federation to Russia.
 */
function geodir_upgrade_20064() {
	global $wpdb;

	$post_types = geodir_get_posttypes();

	$search_country       = 'Russian Federation';
	$replace_country      = 'Russia';
	$search_country_slug  = 'russian-federation';
	$replace_country_slug = 'russia';

	// Default Country
	$default_country = geodir_get_option( 'default_location_country' );
	if ( $default_country == $search_country ) {
		geodir_update_option( 'default_location_country', $replace_country );
	}

	// Details
	if ( ! empty( $post_types ) ) {
		foreach ( $post_types as $post_type ) {
			$table = geodir_db_cpt_table( $post_type );

			if ( ! GeoDir_Post_types::supports( $post_type, 'location' ) ) {
				continue;
			}

			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$table} SET country = %s WHERE country LIKE %s",
					array(
						$replace_country,
						$search_country,
					)
				)
			);
		}
	}

	// Reviews
	$wpdb->query(
		$wpdb->prepare(
			'UPDATE ' . GEODIR_REVIEW_TABLE . ' SET country = %s WHERE country LIKE %s',
			array(
				$replace_country,
				$search_country,
			)
		)
	);

	if ( class_exists( 'GeoDir_Location' ) ) {
		// Locations
		$wpdb->query(
			$wpdb->prepare(
				'UPDATE ' . GEODIR_LOCATIONS_TABLE . ' SET country = %s WHERE country LIKE %s',
				array(
					$replace_country,
					$search_country,
				)
			)
		);
		$wpdb->query(
			$wpdb->prepare(
				'UPDATE ' . GEODIR_LOCATIONS_TABLE . ' SET country_slug = %s WHERE country_slug LIKE %s',
				array(
					$replace_country_slug,
					$search_country_slug,
				)
			)
		);

		// Location SEO
		$wpdb->query(
			$wpdb->prepare(
				'UPDATE ' . GEODIR_LOCATION_SEO_TABLE . ' SET country_slug = %s WHERE country_slug LIKE %s',
				array(
					$replace_country_slug,
					$search_country_slug,
				)
			)
		);

		// Location Term Meta
		$wpdb->query(
			$wpdb->prepare(
				'UPDATE ' . GEODIR_LOCATION_TERM_META . ' SET country_slug = %s WHERE country_slug LIKE %s',
				array(
					$replace_country_slug,
					$search_country_slug,
				)
			)
		);
		$wpdb->query(
			$wpdb->prepare(
				'UPDATE ' . GEODIR_LOCATION_TERM_META . ' SET location_name = %s WHERE location_name LIKE %s',
				array(
					$replace_country_slug,
					$search_country_slug,
				)
			)
		);
	}
}

/**
 * Update for 2.0.0.82
 *
 * @since 2.0.0.82
 *
 * @return void
 */
function geodir_upgrade_20082() {
	// Generate title keywords.
	geodir_generate_title_keywords();
}

/**
 * Update for 2.0.0.96
 *
 * @since 2.0.0.96
 *
 * @return void
 */
function geodir_upgrade_20096() {
	global $wpdb;

	// Add columns in business hours table.
	$table = $wpdb->prefix . 'geodir_business_hours';

	if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" ) && ! geodir_column_exist( $table, 'open_utc' ) ) {
		$wpdb->query(
			"ALTER TABLE `{$table}` 
			ADD open_utc int(9) UNSIGNED NOT NULL, 
			ADD close_utc int(9) UNSIGNED NOT NULL, 
			ADD open_dst int(9) UNSIGNED NOT NULL, 
			ADD close_dst int(9) UNSIGNED NOT NULL, 
			ADD timezone_string varchar(100) NOT NULL, 
			ADD has_dst tinyint(1) NOT NULL DEFAULT '0', 
			ADD is_dst tinyint(1) NOT NULL DEFAULT '0'"
		);
	}

	// Update timezone to timezone string.
	if ( ! geodir_get_option( 'default_location_timezone_string' ) ) {
		$country         = geodir_get_option( 'default_location_country' );
		$timezone        = geodir_get_option( 'default_location_timezone' );
		$timezone_string = geodir_offset_to_timezone_string( $timezone, $country );
		geodir_update_option( 'default_location_timezone_string', $timezone_string );
	}
}

/**
 * Update for 2.1.0.16
 *
 * @since 2.1.0.16
 *
 * @return void
 */
function geodir_upgrade_21016() {
	// Disable beta addons setting.
	geodir_update_option( 'admin_enable_beta', '' );
}
