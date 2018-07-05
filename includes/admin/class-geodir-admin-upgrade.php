<?php
/**
 * GeoDirectory v1 to v2 conversion class.
 *
 * @author   AyeCode
 * @category Admin
 * @package  GeoDirectory/Classes
 * @version  2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GeoDir_Admin_Upgrade Class.
 */
class GeoDir_Admin_Upgrade {

	public static function init() {
		// Start
		self::start_conversion();

		// Options
		self::convert_options();

		// Fields
		self::convert_fields();

		// Categories & Tags
		self::convert_terms();

		// Posts
		self::convert_posts();

		// End
		self::end_conversion();
	}

	public static function start_conversion() {
		do_action( 'geodir_v1_to_v2_start_conversion' );
	}

	public static function convert_options() {
		do_action( 'geodir_v1_to_v2_convert_options_before' );

		$options = self::get_options();

		do_action( 'geodir_v1_to_v2_convert_options_after' );
	}

	public static function convert_fields() {
		do_action( 'geodir_v1_to_v2_convert_fields_before' );

		do_action( 'geodir_v1_to_v2_convert_fields_after' );
	}

	public static function convert_terms() {
		do_action( 'geodir_v1_to_v2_convert_terms_before' );

		do_action( 'geodir_v1_to_v2_convert_terms_after' );
	}

	public static function convert_posts() {
		do_action( 'geodir_v1_to_v2_convert_posts_before' );

		do_action( 'geodir_v1_to_v2_convert_posts_after' );
	}

	public static function end_conversion() {
		do_action( 'geodir_v1_to_v2_end_conversion' );
	}

	public static function get_options() {
		// Core options
		$options = array(
			'taxonomies' => get_option( 'geodir_taxonomies' ),
			'post_types' => get_option( 'geodir_post_types' ),
			'page_add' => get_option( 'geodir_add_listing_page' ),
			'page_location' => get_option( 'geodir_location_page' ),
			'page_terms_conditions' => get_option( 'geodir_term_condition_page' ),
			'email_name' => get_option( 'site_email_name' ),
			'email_address' => get_option( 'site_email' ),
			'search_radius' => get_option( 'geodir_search_dist' ),
			'search_distance_long' => get_option( 'geodir_search_dist_1' ),
			'search_distance_short' => get_option( 'geodir_search_dist_2' ),
			'search_near_addition' => get_option( 'geodir_search_near_addition' ),
			'default_status' => get_option( 'geodir_new_post_default_status' ),
			'search_default_text' => get_option( 'geodir_search_field_default_text' ),
			'search_default_near_text' => get_option( 'geodir_near_field_default_text' ),
			'search_default_button_text' => get_option( 'geodir_search_button_label' ),
			'map_default_marker_icon' => get_option( 'geodir_default_marker_icon' ),
			'exclude_post_type_on_map' => get_option( 'geodir_exclude_post_type_on_map' ),
			'exclude_cat_on_map' => get_option( 'geodir_exclude_cat_on_map' ),
			'email_admin_pending_post' => get_option( 'geodir_notify_post_submit' ),
			'email_admin_pending_post_subject' => get_option( 'geodir_post_submited_success_email_subject_admin' ),
			'email_admin_pending_post_body' => get_option( 'geodir_post_submited_success_email_content_admin' ),
			'email_user_pending_post' => 1,
			'email_user_pending_post_subject' => get_option( 'geodir_post_submited_success_email_subject' ),
			'email_user_pending_post_body' => get_option( 'geodir_post_submited_success_email_content' ),
			'email_user_publish_post' => 1,
			'email_user_publish_post_subject' => get_option( 'geodir_post_published_email_subject' ),
			'email_user_publish_post_body' => get_option( 'geodir_post_published_email_content' ),
			'email_admin_post_edit' => get_option( 'geodir_notify_post_edited' ),
			'email_admin_post_edit_subject' => get_option( 'geodir_post_edited_email_subject_admin' ),
			'email_admin_post_edit_body' => get_option( 'geodir_post_edited_email_content_admin' ),
			'default_location' => get_option( 'geodir_default_location' ),
			'map_language' => get_option( 'geodir_default_map_language' ),
			'gd_term_icons' => get_option( 'gd_term_icons' ),
			'upload_max_filesize' => get_option( 'geodir_upload_max_filesize' ),
			'search_word_limit' => get_option( 'geodir_search_word_limit' ),
			'email_bcc_user_publish_post' => get_option( 'geodir_bcc_listing_published' ),
			'user_trash_posts' => get_option( 'geodir_disable_perm_delete' ),
			'seo_cpt_title' => get_option( 'geodir_page_title_pt' ),
			'seo_cpt_meta_title' => get_option( 'geodir_meta_title_pt' ),
			'seo_cpt_meta_description' => get_option( 'geodir_meta_desc_pt' ),
			'seo_cat_archive_title' => get_option( 'geodir_page_title_cat-listing' ),
			'seo_cat_archive_meta_title' => get_option( 'geodir_meta_title_listing' ),
			'seo_cat_archive_meta_description' => get_option( 'geodir_meta_desc_listing' ),
			'seo_tag_archive_title' => get_option( 'geodir_page_title_tag' ),
			'seo_single_meta_title' => get_option( 'geodir_meta_title_detail' ),
			'seo_single_meta_description' => get_option( 'geodir_meta_desc_detail' ),
			'seo_location_meta_title' => get_option( 'geodir_meta_title_location' ),
			'seo_location_meta_description' => get_option( 'geodir_meta_desc_location' ),
			'seo_search_meta_title' => get_option( 'geodir_meta_title_search' ),
			'seo_search_meta_description' => get_option( 'geodir_meta_desc_search' ),
			'seo_add_listing_title' => get_option( 'geodir_page_title_add-listing' ),
			'seo_add_listing_title_edit' => get_option( 'geodir_page_title_edit-listing' ),
			'seo_add_listing_meta_title' => get_option( 'geodir_meta_title_add-listing' ),
			'seo_add_listing_meta_description' => get_option( 'geodir_meta_desc_add-listing' ),
			'maps_api' => get_option( 'geodir_load_map' ),
			'lazy_load' => get_option( 'geodir_lazy_load' ),
			'google_maps_api_key' => get_option( 'geodir_google_api_key' ),
			'admin_uninstall' => get_option( 'geodir_un_geodirectory' ),
			'map_cache' => get_option( 'geodir_enable_map_cache' ),

			// Google Analytics options
			'ga_stats' => get_option( 'geodir_ga_stats' ),
			'ga_account_id' => get_option( 'geodir_ga_account_id' ),
			'ga_refresh_time' => get_option( 'geodir_ga_refresh_time' ),
			'ga_auto_refresh' => get_option( 'geodir_ga_auto_refresh' ),
			'ga_auth_code' => get_option( 'geodir_ga_auth_code' ),
			'ga_add_tracking_code' => get_option( 'geodir_ga_add_tracking_code' ),
			'ga_anonymize_ip' => get_option( 'geodir_ga_anonymize_ip' ),
			'ga_tracking_code' => get_option( 'geodir_ga_tracking_code' ),
			'gd_ga_access_token' => get_option( 'gd_ga_access_token' ),
			'gd_ga_refresh_token' => get_option( 'gd_ga_refresh_token' ),
			'ga_client_id' => get_option( 'geodir_ga_client_id' ),
			'ga_client_secret' => get_option( 'geodir_ga_client_secret' )
		);

		return apply_filters( 'geodir_v1_to_v2_get_options', $options );
	}

	/*
	public static function core_option_names() {
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

		$custom_options = array( 'geodir_un_geodirectory', 'geodir_default_data_installed', 'geodir_default_data_installed_1.2.8', 'geodir_theme_location_nav', 'geodir_exclude_post_type_on_map', 'geodir_exclude_cat_on_map', 'geodir_exclude_cat_on_map_upgrade', 'geodir_default_map_language', 'geodir_default_map_search_pt', 'avada_nag', 'gd_convert_custom_field_display', 'gd_facebook_button', 'gd_ga_access_token', 'gd_ga_refresh_token', 'gd_google_button', 'gd_search_dist', 'gd_term_icons', 'gd_theme_compats', 'gd_tweet_button', 'geodir_changes_in_custom_fields_table', 'geodir_default_location', 'geodir_disable_yoast_meta', 'geodir_ga_client_id', 'geodir_ga_client_secret', 'geodir_ga_tracking_code', 'geodir_gd_uids', 'geodir_global_review_count', 'geodir_listing_page', 'post_types', 'geodir_remove_unnecessary_fields', 'geodir_remove_url_seperator', 'geodir_set_post_attachments', 'geodir_sidebars', 'taxonomies', 'geodir_use_php_sessions', 'geodir_wpml_disable_duplicate', 'geodirectory_list_thumbnail_size', 'ptthemes_auto_login', 'ptthemes_listing_preexpiry_notice_days', 'ptthemes_logoin_page_content', 'ptthemes_reg_page_content', 'theme_compatibility_setting' );

		if ( version_compare( GEODIRECTORY_VERSION, '2.0.0', '<' ) ) {
			$results = $wpdb->get_results( "SELECT option_name FROM " . $wpdb->options . " WHERE option_name LIKE 'geodir_un_%' OR option_name LIKE 'geodir_theme_location_nav_%'" );
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

	public static function old_tax_meta_options() {
		global $wpdb;

		$results = $wpdb->get_results( "SELECT option_name, option_value FROM " . $wpdb->options . " WHERE option_name LIKE 'tax_meta_%'" );

		return $results;
	}
	*/
}
