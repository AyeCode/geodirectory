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
		add_action( 'geodir_update_200_settings_after', array( __CLASS__, 'update_200_set_permalink_structure' ), 10, 1 );

		// Payment Manager
		if ( self::needs_upgrade( 'payment_manager' ) ) {
			add_filter( 'geodir_update_200_get_options', array( __CLASS__, 'update_200_pm_get_options' ), 9, 1 );

			add_action( 'geodir_update_200_create_default_options', array( __CLASS__, 'update_200_pm_create_default_options' ), 9 );
			add_action( 'geodir_update_200_create_tables', array( __CLASS__, 'update_200_pm_create_tables' ), 9 );
			add_action( 'geodir_update_200_update_gd_version', array( __CLASS__, 'update_200_pm_update_version' ), 9 );
		}

		// Custom post types
		if ( self::needs_upgrade( 'custom_post_types' ) ) {
			add_filter( 'geodir_update_200_get_options', array( __CLASS__, 'update_200_cp_get_options' ), 10, 1 );

			add_action( 'geodir_update_200_create_default_options', array( __CLASS__, 'update_200_cp_create_default_options' ), 10 );
			add_action( 'geodir_update_200_custom_fields', array( __CLASS__, 'update_200_cp_custom_fields' ), 10 );
			add_action( 'geodir_update_200_post_fields', array( __CLASS__, 'update_200_cp_post_fields' ), 10, 1 );
			add_action( 'geodir_update_200_create_tables', array( __CLASS__, 'update_200_cp_create_tables' ), 10 );
			add_action( 'geodir_update_200_update_gd_version', array( __CLASS__, 'update_200_cp_update_version' ), 10 );
		}

		// Location manager
		if ( self::needs_upgrade( 'location_manager' ) ) {
			add_filter( 'geodir_update_200_get_options', array( __CLASS__, 'update_200_lm_get_options' ), 11, 1 );

			add_action( 'geodir_update_200_create_default_options', array( __CLASS__, 'update_200_lm_create_default_options' ), 11 );
			add_action( 'geodir_update_200_post_fields', array( __CLASS__, 'update_200_lm_post_fields' ), 11, 1 );
			add_action( 'geodir_update_200_term_metas', array( __CLASS__, 'update_200_lm_term_metas' ), 11 );
			add_action( 'geodir_update_200_create_tables', array( __CLASS__, 'update_200_lm_create_tables' ), 11 );
			add_action( 'geodir_update_200_update_gd_version', array( __CLASS__, 'update_200_lm_update_version' ), 11 );
		}

		// Advance search
		if ( self::needs_upgrade( 'advance_search' ) ) {
			add_filter( 'geodir_update_200_get_options', array( __CLASS__, 'update_200_search_get_options' ), 12, 1 );

			add_action( 'geodir_update_200_create_default_options', array( __CLASS__, 'update_200_search_create_default_options' ), 12 );
			add_action( 'geodir_update_200_custom_fields', array( __CLASS__, 'update_200_search_custom_fields' ), 12 );
			add_action( 'geodir_update_200_update_gd_version', array( __CLASS__, 'update_200_search_update_version' ), 12 );
		}

		// Event manager
		if ( self::needs_upgrade( 'event_manager' ) ) {
			add_filter( 'geodir_update_200_get_options', array( __CLASS__, 'update_200_event_get_options' ), 10, 1 );

			add_action( 'geodir_update_200_create_default_options', array( __CLASS__, 'update_200_event_create_default_options' ), 10 );
			add_action( 'geodir_update_200_create_tables', array( __CLASS__, 'update_200_event_create_tables' ), 10 );
			add_action( 'geodir_update_200_update_gd_version', array( __CLASS__, 'update_200_event_update_version' ), 10 );
		}

		// Review Ratings
		if ( self::needs_upgrade( 'review_rating' ) ) {
			add_filter( 'geodir_update_200_get_options', array( __CLASS__, 'update_200_rr_get_options' ), 13, 1 );

			add_action( 'geodir_update_200_create_default_options', array( __CLASS__, 'update_200_rr_create_default_options' ), 13 );
			add_action( 'geodir_update_200_create_tables', array( __CLASS__, 'update_200_rr_create_tables' ), 13 );
			add_action( 'geodir_update_200_update_gd_version', array( __CLASS__, 'update_200_rr_update_version' ), 13 );
		}

		// Claim Manager
		if ( self::needs_upgrade( 'claim_manager' ) ) {
			add_filter( 'geodir_update_200_get_options', array( __CLASS__, 'update_200_claim_get_options' ), 30, 1 );

			add_action( 'geodir_update_200_create_default_options', array( __CLASS__, 'update_200_claim_create_default_options' ), 30 );
			add_action( 'geodir_update_200_create_tables', array( __CLASS__, 'update_200_claim_create_tables' ), 30 );
			add_action( 'geodir_update_200_update_gd_version', array( __CLASS__, 'update_200_claim_update_version' ), 30 );
		}

		// Franchise Manager
		if ( self::needs_upgrade( 'franchise_manager' ) ) {
			add_filter( 'geodir_update_200_get_options', array( __CLASS__, 'update_200_franchise_get_options' ), 40, 1 );

			add_action( 'geodir_update_200_create_default_options', array( __CLASS__, 'update_200_franchise_create_default_options' ), 40 );
			add_action( 'geodir_update_200_custom_fields', array( __CLASS__, 'update_200_franchise_custom_fields' ), 40 );
			add_action( 'geodir_update_200_update_gd_version', array( __CLASS__, 'update_200_franchise_update_version' ), 40 );
		}

		add_action( 'geodirectory_v2_updated', array( __CLASS__, 'v2_updated' ), 10 );
	}

	public static function needs_upgrade( $plugin ) {
		$found = false;

		switch ( $plugin ) {
			case 'advance_search':
				$found = ! is_null( get_option( 'geodiradvancesearch_db_version', null ) ) && ( is_null( get_option( 'geodir_advance_search_db_version', null ) ) || ( get_option( 'geodir_advance_search_db_version' ) && version_compare( get_option( 'geodir_advance_search_db_version' ), '2.0.0.0', '<' ) ) );
			break;
			case 'claim_manager':
				$found = ! is_null( get_option( 'geodirclaim_db_version', null ) ) && ( is_null( get_option( 'geodir_claim_db_version', null ) ) || ( get_option( 'geodir_claim_db_version' ) && version_compare( get_option( 'geodir_claim_db_version' ), '2.0.0.0', '<' ) ) );
			break;
			case 'custom_post_types':
				$found = ! is_null( get_option( 'geodir_custom_posts_db_version', null ) ) && ( is_null( get_option( 'geodir_cp_db_version', null ) ) || ( get_option( 'geodir_cp_db_version' ) && version_compare( get_option( 'geodir_cp_db_version' ), '2.0.0.0', '<' ) ) );
			break;
			case 'event_manager':
				$found = ! is_null( get_option( 'geodirevents_db_version', null ) ) && ( is_null( get_option( 'geodir_event_db_version', null ) ) || ( get_option( 'geodir_event_db_version' ) && version_compare( get_option( 'geodir_event_db_version' ), '2.0.0.0', '<' ) ) );
			break;
			case 'franchise_manager':
				$found = ( is_null( get_option( 'geodir_franchise_db_version', null ) ) || ( get_option( 'geodir_franchise_db_version' ) && version_compare( get_option( 'geodir_franchise_db_version' ), '2.0.0.0', '<' ) ) ) && ! is_null( get_option( 'geodir_franchise_posttypes', null ) );
			break;
			case 'location_manager':
				$found = ! is_null( get_option( 'geodirlocation_db_version', null ) ) && ( is_null( get_option( 'geodir_location_db_version', null ) ) || ( get_option( 'geodir_location_db_version' ) && version_compare( get_option( 'geodir_location_db_version' ), '2.0.0.0', '<' ) ) );
			break;
			case 'payment_manager':
				$found = ! is_null( get_option( 'geodir_payments_db_version', null ) ) && ( is_null( get_option( 'geodir_pricing_db_version', null ) ) || ( get_option( 'geodir_pricing_db_version' ) && version_compare( get_option( 'geodir_pricing_db_version' ), '2.5.0.0', '<' ) ) );
			break;
			case 'review_rating':
				$found = ! is_null( get_option( 'geodir_reviewratings_db_version', null ) ) && ( is_null( get_option( 'geodir_reviewrating_db_version', null ) ) || ( get_option( 'geodir_reviewrating_db_version' ) && version_compare( get_option( 'geodir_reviewrating_db_version' ), '2.0.0.0', '<' ) ) );
			break;
		}

		return $found;
	}

	public static function update_200_settings() {
		global $geodir_options;

		if ( self::is_done( 'update_200_settings' ) ) {
			return;
		}

		do_action( 'geodir_update_200_settings_before' );

		self::create_default_options();

		$saved_options = get_option( 'geodir_settings' );
		if ( empty( $saved_options ) || ! is_array( $saved_options ) ) {
			$saved_options = array();
		}

		$update_options = self::update_200_get_options();
		foreach ( $update_options as $key => $value ) {
			$saved_options[ $key ] = $value;
		}

		update_option( 'geodir_settings', $saved_options );

		$geodir_options = geodir_get_settings();

		do_action( 'geodir_update_200_settings_after' );

		self::update_log( 'update_200_settings' );
	}

	public static function update_200_fields() {
		do_action( 'geodir_update_200_fields_before' );

		// Custom fields
		self::update_200_custom_fields();

		// Post fields
		self::update_200_post_fields();

		do_action( 'geodir_update_200_fields_after' );
	}

	public static function update_200_terms() {
		do_action( 'geodir_update_200_terms_before' );

		self::update_200_term_metas();

		do_action( 'geodir_update_200_terms_after' );
	}

	public static function update_200_posts() {
		do_action( 'geodir_update_200_posts_before' );

		self::update_200_reviews();
		self::update_200_attachments();

		do_action( 'geodir_update_200_posts_after' );
	}

	public static function update_200_merge_data() {
		self::create_tables();
		self::insert_default_fields();
		self::insert_default_tabs();
		self::create_pages();

		if ( ! self::is_done( 'register_post_status' ) ) {
			GeoDir_Post_types::register_post_status();

			self::update_log( 'register_post_status' );
		}

		if ( ! self::is_done( 'create_uncategorized_categories' ) ) {
			GeoDir_Admin_Install::create_uncategorized_categories();

			self::update_log( 'create_uncategorized_categories' );
		}
		
		self::create_cron_jobs();

		// Queue upgrades/setup wizard
		self::maybe_enable_setup_wizard();
	}

	public static function update_200_db_version() {
		// Update GD version
		self::update_gd_version();

		// Update DB version
		GeoDir_Admin_Install::update_db_version( GEODIRECTORY_VERSION );

		// Flush rules after install
		flush_rewrite_rules();
		do_action( 'geodir_flush_rewrite_rules' );
		wp_schedule_single_event( time(), 'geodir_flush_rewrite_rules' );

		// Trigger action
		do_action( 'geodirectory_v2_updated' );
	}

	/**
	 * Default options.
	 *
	 * Sets up the default options used on the settings page.
     *
     * @since 2.0.0
	 */
	public static function create_default_options() {
		// Include settings so that we can run through defaults
		include_once( dirname( __FILE__ ) . '/class-geodir-admin-settings.php' );
		
		$current_settings = geodir_get_settings();

		$settings = GeoDir_Admin_Settings::get_settings_pages();

		foreach ( $settings as $section ) {
			if ( ! method_exists( $section, 'get_settings' ) ) {
				continue;
			}
			$subsections = array_unique( array_merge( array( '' ), array_keys( $section->get_sections() ) ) );

			foreach ( $subsections as $subsection ) {
				foreach ( $section->get_settings( $subsection ) as $value ) {
					if ( !isset($current_settings[$value['id']]) && isset( $value['default'] ) && isset( $value['id'] ) ) {
						geodir_update_option($value['id'], $value['default']);
					}
				}
			}
		}

		do_action( 'geodir_update_200_create_default_options' );
	}

	public static function update_200_get_options() {
		// Post types
		$v1_post_types = self::v1_post_types();
		$v2_post_types = geodir_get_option( 'post_types' );
		if ( ! is_array( $v2_post_types ) || empty( $v2_post_types ) ) {
			$v2_post_types = array();
		}
		if ( ! empty( $v1_post_types ) ) {
			foreach( $v1_post_types as $post_type => $data ) {
				if ( ! empty( $data['labels'] ) ) {
					$remove_labels = array( 'label_post_profile', 'label_post_info', 'label_post_images', 'label_post_map', 'label_reviews', 'label_related_listing' );
					foreach ( $remove_labels as $label ) {
						if ( isset( $data['labels'][ $label ] ) ) {
							unset( $data['labels'][ $label ] );
						}
					}
				}

				if ( ! in_array( 'revisions', $data['supports'] ) ) {
					$data['supports'][] = 'revisions';
				}

				if ( $post_type == 'gd_place' ) {
					$data['menu_icon'] = 'dashicons-location-alt';
				} else if ( $post_type == 'gd_event' ) {
					$data['menu_icon'] = 'dashicons-calendar-alt';
				} else {
					$data['menu_icon'] = 'dashicons-admin-site';
				}

				$data['default_image'] = self::update_200_generate_attachment_id( get_option( 'geodir_cpt_img_' . $post_type ) );

				$data['disable_reviews'] = in_array( $post_type, (array) get_option( 'geodir_disable_rating_cpt' ) );
				$data['disable_favorites'] = 0;
				$data['disable_frontend_add'] = ! in_array( $post_type, (array) get_option( 'geodir_allow_posttype_frontend' ) );

				if ( self::needs_upgrade( 'custom_post_types' ) ) {
					$data['disable_location'] = in_array( $post_type, (array) get_option( 'geodir_cpt_disable_location' ) );
				}

				if ( self::needs_upgrade( 'franchise_manager' ) ) {
					$data['supports_franchise'] = in_array( $post_type, (array) get_option( 'geodir_franchise_posttypes' ) );
				}

				$data['seo'] = array(
					'title' => ( isset( $data['seo']['title'] ) ? $data['seo']['title'] : '' ),
                    'meta_title' => ( isset( $data['seo']['meta_title'] ) ? $data['seo']['meta_title'] : '' ),
                    'meta_description' => ( isset( $data['seo']['meta_description'] ) ? $data['seo']['meta_description'] : '' ),
				);

				$v2_post_types[ $post_type ] = $data;
			}
		}

		// Taxonomies
		$v1_taxonomies = self::v1_taxonomies();
		$v2_taxonomies = geodir_get_option( 'taxonomies' );
		if ( ! is_array( $v2_taxonomies ) || empty( $v2_taxonomies ) ) {
			$v2_taxonomies = array();
		}
		if ( ! empty( $v1_taxonomies ) ) {
			foreach( $v1_taxonomies as $taxonomy => $data ) {
				$v2_taxonomies[ $taxonomy ] = $data;
			}
		}

		$default_location = wp_parse_args( (array) get_option( 'geodir_default_location' ), array(
			'country' => '',
			'region' => '',
			'city' => '',
			'city_latitude' => '',
			'city_longitude' => '',
		) );

		$rating_color = get_option( 'geodir_reviewrating_fa_full_rating_color', '#ff9900' );
		if ( $rating_color == '#757575' ) {
			$rating_color = '#ff9900';
		}

		// Core options
		$options = array(
			'taxonomies' => $v2_taxonomies,
			'post_types' => $v2_post_types,
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
			'map_default_marker_icon' => self::update_200_generate_attachment_id( get_option( 'geodir_default_marker_icon' ) ),
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
			'gd_term_icons' => '', // force rebuild terms icon on upgrade
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
			'admin_blocked_roles' => array( 'subscriber' ),
			'listing_default_image' => self::update_200_generate_attachment_id( get_option( 'geodir_listing_no_img' ) ),
			'rating_color' => $rating_color,
			'rating_color_off' => '#afafaf',
			'rating_type' => 'font-awesome',
			'rating_icon' => 'fas fa-star',
			'rating_image' => self::update_200_generate_attachment_id( get_option( 'geodir_default_rating_star_icon' ) ),
			'default_location_city' => $default_location['city'],
			'default_location_region' => $default_location['region'],
			'default_location_country' => $default_location['country'],
			'default_location_latitude' => $default_location['city_latitude'],
			'default_location_longitude' => $default_location['city_longitude'],
			'default_location_timezone_string' => geodir_timezone_string(),
			'permalink_category_base' => 'category',
			'permalink_tag_base' => 'tags',

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

		if ( $options['rating_type'] == 'image' && empty( $options['rating_image'] ) ) {
			$options['rating_type'] = 'font-awesome';
		}

		return apply_filters( 'geodir_update_200_get_options', $options );
	}

	public static function update_200_generate_attachment_id( $image_url ) {
		if ( empty( $image_url ) ) {
			return '';
		}

		$image_url = str_replace( 'geodirectory-assets/', 'assets/', $image_url );
		$image_url = str_replace( 'geodirectory-functions/map-functions/icons', 'assets/images', $image_url );

		$upload = GeoDir_Media::upload_image_from_url( $image_url );
		if ( ! empty( $upload ) && ! is_wp_error( $upload ) && ! empty( $upload['file'] ) ) {
			$attachment_id = GeoDir_Media::set_uploaded_image_as_attachment( $upload );

			if ( ! empty( $attachment_id ) && ! is_wp_error( $attachment_id ) ) {
				return $attachment_id;
			}
		}

		return '';
	}

	public static function update_200_set_permalink_structure() {
		if ( get_option( 'geodir_add_location_url' ) ) {
			$show_location_url = get_option( 'geodir_show_location_url' );

			if ( $show_location_url == 'all' ) {
				$permalink_structure = '/%country%/%region%/%city%/%category%/%postname%/';
			} else if ( $show_location_url == 'country_city' ) {
				$permalink_structure = '/%country%/%city%/%category%/%postname%/';
			} else if ( $show_location_url == 'region_city' ) {
				$permalink_structure = '/%region%/%city%/%category%/%postname%/';
			} else {
				$permalink_structure = '/%city%/%category%/%postname%/';
			}
		} else {
			$permalink_structure = '/%category%/%postname%/';
		}

		if ( ! get_option( 'geodir_add_categories_url' ) ) {
			$permalink_structure = str_replace( '/%category%', '', $permalink_structure );
		}

		if ( ! empty( $permalink_structure ) ) {
			$permalink_structure = preg_replace( '#/+#', '/', '/' . str_replace( '#', '', $permalink_structure ) );
		}
		$permalink_structure = sanitize_option( 'permalink_structure', $permalink_structure );

		geodir_set_permalink_structure( $permalink_structure );
	}

	public static function update_200_term_metas() {
	    global $wpdb;

		if ( ! self::is_done( 'update_200_term_metas' ) ) {
			// Migrate tax meta.
			$term_meta_options = $wpdb->get_results( "SELECT option_name, option_value FROM " . $wpdb->options . " WHERE option_name LIKE 'tax_meta_%'" );

			foreach ( $term_meta_options as $option ) {
				$explode = explode( '_', $option->option_name );
				$index = count( $explode ) - 1;

				
				if ( !empty( $explode[ $index ] ) ) {
					$term_id = $explode[ $index ];
					$value = maybe_unserialize( $option->option_value );
					$value = !empty( $value[0] ) && is_array( $value[0] ) ? $value[0] : $value;
					
					if ( !empty( $value ) && is_array( $value ) ) {
						foreach ( $value as $meta_key => $meta_value ) {
							if ( $meta_key == 'ct_cat_icon' || $meta_key == 'ct_cat_default_img' ) {
								if ( empty( $meta_value['src'] ) || substr( $meta_value['src'], 0, 4 ) != "http" ) {
									continue;
								}
								$meta_value['src'] = geodir_file_relative_url( $meta_value['src'] );
							}

							update_term_meta( $term_id, $meta_key, $meta_value );
						}
					}
				}
			}

			self::update_log( 'update_200_term_metas' );
		}

		do_action( 'geodir_update_200_term_metas' );
	}

	public static function update_200_custom_fields() {
		global $wpdb, $plugin_prefix;

		$post_types = self::v2_post_types( true );

		// Custom fields
		$custom_fields_table = GEODIR_CUSTOM_FIELDS_TABLE;
		$packages_table = $plugin_prefix . 'price';

		if ( ! self::is_done( 'update_200_custom_fields' ) ) {
			$wpdb->query( "ALTER TABLE `{$custom_fields_table}` 
				CHANGE admin_desc frontend_desc text NULL DEFAULT NULL, 
				CHANGE site_title frontend_title varchar(255) NULL DEFAULT NULL, 
				ADD `placeholder_value` text NULL DEFAULT NULL AFTER `default_value`, 
				ADD `tab_level` int(11) NOT NULL AFTER `sort_order`, 
				ADD `tab_parent` varchar(100) NOT NULL AFTER `sort_order`;" 
			);

			$results = $wpdb->get_results( "SELECT * FROM `{$custom_fields_table}`" );

			$wpdb->query( "ALTER TABLE `{$custom_fields_table}` 
				CHANGE `is_active` `is_active` CHAR(1) NOT NULL DEFAULT '1', 
				CHANGE `is_default` `is_default` CHAR(1) NOT NULL DEFAULT '0', 
				CHANGE `is_required` `is_required` CHAR(1) NOT NULL DEFAULT '0', 
				CHANGE `for_admin_use` `for_admin_use` CHAR(1) NOT NULL DEFAULT '0';" 
			);
			$wpdb->query( "ALTER TABLE `{$custom_fields_table}` 
				CHANGE `is_active` `is_active` TINYINT(1) NOT NULL DEFAULT '1', 
				CHANGE `is_default` `is_default` TINYINT(1) NOT NULL DEFAULT '0', 
				CHANGE `is_required` `is_required` TINYINT(1) NOT NULL DEFAULT '0', 
				CHANGE `for_admin_use` `for_admin_use` TINYINT(1) NOT NULL DEFAULT '0';" 
			);

			foreach ( $results as $row ) {
				if ( in_array( $row->htmlvar_name, array( 'geodir_contact', 'is_featured' ) ) ) {
					if ( $row->htmlvar_name == 'geodir_contact' ) {
						$row->htmlvar_name = 'phone';
					}
					if ( $row->htmlvar_name == 'is_featured' ) {
						$row->htmlvar_name = 'featured';
					}
				}
				if ( strpos( $row->htmlvar_name, 'geodir_' ) === 0 ) {
					$row->htmlvar_name = strtolower( substr( $row->htmlvar_name, 7 ) );
				}

				if ( $row->field_type == 'taxonomy' ) {
					$row->field_type = 'categories';
					$row->field_type_key = 'categories';
					$row->htmlvar_name = 'post_category';
					$extra_fields = maybe_unserialize( $row->extra_fields );
					if ( ! empty( $extra_fields ) ) {
						if ( $extra_fields == 'ajax_chained' ) {
							$cat_display_type = 'multiselect';
						} else {
							$cat_display_type = $extra_fields;
						}
					} else {
						$cat_display_type = 'multiselect';
					}
					$row->extra_fields = maybe_serialize( array( 'cat_display_type' => $cat_display_type ) );
				}

				if ( $row->field_type == 'address' ) {
					$row->htmlvar_name = 'address';
					if ( empty( $row->field_icon ) ) {
						$row->field_icon = 'fas fa-map-marker-alt';
					}
				}

				if ( empty( $row->field_type_key ) ) {
					$row->field_type_key = $row->field_type;
				}

				if ( empty( $row->data_type ) ) {
					if ( $row->field_type == 'textarea' || $row->field_type == 'html' || $row->field_type == 'url' ) {
						$data_type = 'TEXT';
					} else if ( $row->field_type == 'checkbox' ) {
						$data_type = 'TINYINT';
					} else if ( $row->field_type == 'datepicker' ) {
						$data_type = 'DATE';
					} else if ( $row->field_type == 'time' ) {
						$data_type = 'TIME';
					} else {
						$data_type = 'VARCHAR';
					}
					$row->data_type = $data_type;
				}

				if ( ! empty( $row->field_icon ) && ( strpos( $row->field_icon, 'fa ' ) === 0 || strpos( $row->field_icon, 'fa-' ) === 0 ) ) {
					$field_icon = $row->field_icon;
					$field_icon = str_replace( 'fa ', 'fas ', $field_icon );
					$field_icon = str_replace( 'fa-usd', 'fa-dollar-sign', $field_icon );
					$field_icon = str_replace( 'fa-money', 'fa-money-bill-alt', $field_icon );
					$row->field_icon = $field_icon;
				}
				if ( empty( $row->htmlvar_name ) ) {
					$title = ( ! empty( $row->frontend_title ) ? $row->frontend_title : $row->admin_title );
					$row->htmlvar_name = str_replace( '-', '_', sanitize_key( str_replace( ' ', '_', $title ) ) . '_' . $row->id );
				}
				$row->is_active = (int) $row->is_active;
				$row->is_default = (int) $row->is_default;
				$row->is_required = (int) $row->is_required;
				$row->for_admin_use = (int) $row->for_admin_use;

				// Fix is_default issue
				if ( in_array( $row->htmlvar_name, array( 'timing', 'contact', 'phone', 'email', 'website', 'twitter', 'facebook', 'video', 'special_offers' ) ) ) {
					$row->is_default = 0;
				}

				$wpdb->update( $custom_fields_table, (array) $row, array( 'id' => $row->id ) );
			}

			self::update_log( 'update_200_custom_fields' );
		}

		// Create pre-defined custom fields
		if ( ! empty( $post_types ) ) {
			foreach ( $post_types as $key => $post_type ) {
				if ( empty( $post_type ) ) {
					continue;
				}

				if ( self::is_done( 'update_200_custom_fields_predefined_' . $post_type ) ) {
					continue;
				}
				
				$packages = '';
				if ( self::needs_upgrade( 'payment_manager' ) ) {
					$results = $wpdb->get_col( $wpdb->prepare( "SELECT pid FROM {$packages_table} WHERE post_type = %s", $post_type ) );
					if ( ! empty( $results ) ) {
						$packages = implode( ',', $results );
					}
				}

				// Featured
				$field_data = array(
					'post_type' => $post_type,
					'data_type' => 'TINYINT', 
					'field_type' => 'checkbox', 
					'field_type_key' => 'featured', 
					'admin_title' => 'Featured', 
					'frontend_desc' => 'Mark listing as a featured.', 
					'frontend_title' => 'Is Featured?', 
					'htmlvar_name' => 'featured', 
					'default_value' => '',
					'sort_order' => '20',
					'is_active' => '1', 
					'show_in' => '',
					'for_admin_use' => '1', 
					'packages' => $packages, 
					'cat_sort' => '1', 
					'extra_fields' => '', 
					'field_icon' => 'fas fa-certificate'
				);

				$wpdb->insert( $custom_fields_table, $field_data, array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%d', '%s', '%d', '%s', '%s' ) );

				// Claimed
				if ( self::needs_upgrade( 'claim_manager' ) ) {
					$field_data = array(
						'post_type' => $post_type,
						'data_type' => 'TINYINT', 
						'field_type' => 'checkbox', 
						'field_type_key' => 'claimed', 
						'admin_title' => 'Is Claimed?', 
						'frontend_desc' => 'Mark listing as a claimed.', 
						'frontend_title' => 'Business Owner/Associate?', 
						'htmlvar_name' => 'claimed', 
						'default_value' => '',
						'sort_order' => '21',
						'is_active' => '1', 
						'show_in' => '',
						'packages' => $packages, 
						'cat_sort' => '1', 
						'extra_fields' => '', 
						'field_icon' => 'fas fa-user-check'
					);

					$wpdb->insert( $custom_fields_table, $field_data, array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%d', '%s', '%s' ) );
				}

				self::update_log( 'update_200_custom_fields_predefined_' . $post_type );
			}
		}

		// Sorting fields
		$custom_sort_fields_table = GEODIR_CUSTOM_SORT_FIELDS_TABLE;

		if ( ! self::is_done( 'update_200_custom_fields_fields_table' ) ) {
			$results = $wpdb->get_results( "SELECT * FROM `{$custom_sort_fields_table}`" );

			$wpdb->query( "ALTER TABLE `{$custom_sort_fields_table}` 
				CHANGE site_title frontend_title varchar(255) NULL DEFAULT NULL, 
				ADD `tab_level` int(11) NOT NULL AFTER `sort_order`, 
				ADD `tab_parent` varchar(100) NOT NULL AFTER `sort_order`, 
				ADD sort varchar(5) DEFAULT 'asc';" 
			);

			if ( ! empty( $results ) ) {
				foreach ( $results as $row ) {
					$htmlvar_name = $row->htmlvar_name;

					if ( $htmlvar_name == 'geodir_contact' ) {
						$htmlvar_name = 'phone';
					}
					if ( $htmlvar_name == 'is_featured' ) {
						$htmlvar_name = 'featured';
					}
					if ( strpos( $htmlvar_name, 'geodir_' ) === 0 ) {
						$htmlvar_name = strtolower( substr( $htmlvar_name, 7 ) );
					}

					if ( ! empty( $row->data_type ) ) {
						$data_type = $row->data_type;
					} else {
						$data_type = $wpdb->get_var( $wpdb->prepare( "SELECT data_type FROM `{$custom_fields_table}` WHERE htmlvar_name = %s", $htmlvar_name ) );
					}
					if ( empty( $data_type ) ) {
						$data_type = 'VARCHAR';
					}

					$data = array();
					$data['post_type'] = $row->post_type;
					$data['data_type'] = $data_type;
					$data['field_type'] = $row->field_type;
					$data['htmlvar_name'] = $htmlvar_name;
					$data['sort_order'] = $row->sort_order;
					$data['is_active'] = $row->is_active;
					$data['tab_parent'] = 0;

					if ( $row->field_type == 'random' ) {
						$data['htmlvar_name'] = 'post_status';
						$data['frontend_title'] = $row->site_title;
						$data['is_default'] = ! empty( $row->is_default ) ? 1 : 0;
						$data['sort'] = 'asc';

						$wpdb->update( $custom_sort_fields_table, (array) $data, array( 'id' => $row->id ) );
					} else {
						$update = true;
						if ( ! empty( $row->sort_asc ) ) {
							$is_default = ! empty( $row->is_default ) && $row->htmlvar_name . '_asc' ==  $row->default_order ? 1 : 0;
							$asc_data = $data;
							$asc_data['frontend_title'] = ! empty( $row->asc_title ) ? $row->asc_title : $row->site_title;
							$asc_data['is_default'] = $is_default;
							$asc_data['sort'] = 'asc';

							$wpdb->update( $custom_sort_fields_table, (array) $asc_data, array( 'id' => $row->id ) );
							$update = false;
						}
						if ( ! empty( $row->sort_desc ) ) {
							$is_default = ! empty( $row->is_default ) && $row->htmlvar_name . '_desc' ==  $row->default_order ? 1 : 0;
							$desc_data = $data;
							$desc_data['frontend_title'] = ! empty( $row->desc_title ) ? $row->desc_title : $row->site_title;
							$desc_data['is_default'] = $is_default;
							$desc_data['sort'] = 'desc';

							if ( $update ) {
								$wpdb->update( $custom_sort_fields_table, (array) $desc_data, array( 'id' => $row->id ) );
							} else {
								$wpdb->insert( $custom_sort_fields_table, (array) $desc_data );
							}
						}
					}
				}

				// update sorting fields sort order
				self::update_200_sort_fields_sort_order();
			}

			self::update_log( 'update_200_custom_fields_fields_table' );
		}

		do_action( 'geodir_update_200_custom_fields' );
	}

	public static function update_200_post_fields() {
		global $wpdb;

		$post_types = self::v2_post_types( true );

		if ( ! empty( $post_types ) ) {
			foreach ( $post_types as $key => $post_type ) {
				if ( self::is_done( 'update_200_post_fields_' . $post_type ) ) {
					continue;
				}

				$table = $wpdb->prefix . 'geodir_' . $post_type . '_detail';
				
				$columns = @$wpdb->get_results("DESC {$table}");

				if ( empty( $columns ) ) {
					continue;
				}

				$fields = array();
				foreach ( $columns as $column_key => $column ) {
					$fields[ $column->Field ] = (array) $column;
				}
				$columns = array_keys( $fields );

				$wpdb->query( "ALTER TABLE `{$table}` CHANGE {$post_type}category post_category text NULL DEFAULT NULL;" );
				if ( in_array( 'is_featured', $columns ) ) {
					$wpdb->query( "ALTER TABLE {$table} DROP INDEX is_featured" );
					// Converting the ENUM to TINYINT directly might give unexpected results. So we should start by converting column to a CHAR(1) and then to TINYINT(1).
					$wpdb->query( "ALTER TABLE `{$table}` CHANGE is_featured featured char(1) NOT NULL DEFAULT '0';" );
					$wpdb->query( "ALTER TABLE `{$table}` CHANGE featured featured tinyint(1) NOT NULL DEFAULT '0';" );
				}
				$wpdb->query( "ALTER TABLE `{$table}` 
					CHANGE submit_ip `submit_ip` varchar(100) DEFAULT NULL, 
					CHANGE post_address `street` varchar(254) DEFAULT NULL, 
					CHANGE post_city `city` varchar(50) DEFAULT NULL, 
					CHANGE post_region `region` varchar(50) DEFAULT NULL, 
					CHANGE post_country `country` varchar(50) DEFAULT NULL, 
					CHANGE post_zip `zip` varchar(50) NULL, 
					CHANGE post_latitude `latitude` varchar(22)  DEFAULT NULL, 
					CHANGE post_longitude `longitude` varchar(22) DEFAULT NULL, 
					CHANGE post_mapview `mapview` varchar(15) DEFAULT NULL;" 
				);
				if ( in_array( 'post_mapzoom', $columns ) ) {
					$wpdb->query( "ALTER TABLE `{$table}` CHANGE post_mapzoom `mapzoom` varchar(3) DEFAULT NULL;" );
				}
				$wpdb->query( "ALTER TABLE `{$table}` 
					CHANGE geodir_contact `phone` varchar(254) DEFAULT NULL, 
					CHANGE geodir_email `email` varchar(254) DEFAULT NULL, 
					CHANGE geodir_website `website` text, 
					CHANGE geodir_twitter `twitter` text, 
					CHANGE geodir_facebook `facebook` text, 
					CHANGE geodir_video `video` text, 
					CHANGE geodir_special_offers `special_offers` text;" 
				);
				if ( $post_type == 'gd_event' ) {
					$wpdb->query( "ALTER TABLE `{$table}` 
						CHANGE is_recurring recurring TINYINT(1) DEFAULT '0', 
						CHANGE recurring_dates event_dates TEXT NOT NULL;" 
					);
				}
				if ( in_array( 'expire_date', $columns ) ) {
					$wpdb->query( "ALTER TABLE `{$table}` CHANGE `expire_date` `expire_date` DATE DEFAULT NULL;" );
				}
				if ( in_array( 'claimed', $columns ) ) {
					// Converting the ENUM to TINYINT directly might give unexpected results. So we should start by converting column to a CHAR(1) and then to INT(11).
					$wpdb->query( "ALTER TABLE `{$table}` CHANGE claimed claimed char(1) NOT NULL DEFAULT '0';" );
					$wpdb->query( "ALTER TABLE `{$table}` CHANGE claimed claimed int(11) DEFAULT '0';" );
				}
				if ( in_array( 'franchise', $columns ) ) {
					$wpdb->query( "ALTER TABLE `{$table}` CHANGE franchise franchise_of INT(11) UNSIGNED DEFAULT '0';" );
					$wpdb->query( "ALTER TABLE `{$table}` ADD franchise TINYINT(1) UNSIGNED DEFAULT '0';" );
					$wpdb->query( "ALTER TABLE `{$table}` ADD franchise_fields TEXT NULL;" );
				}

				$wpdb->query( "ALTER TABLE {$table} ADD INDEX country(country)" );
				$wpdb->query( "ALTER TABLE {$table} ADD INDEX region(region)" );
				$wpdb->query( "ALTER TABLE {$table} ADD INDEX city(city)" );

				$change_columns = array();
				foreach ( $columns as $key => $column ) {
					if ( strpos( $column, 'geodir_' ) === 0 && ! in_array( $column, array( 'geodir_contact', 'geodir_email', 'geodir_website', 'geodir_twitter', 'geodir_facebook', 'geodir_video', 'geodir_special_offers' ) ) ) {
						$new_column = strtolower( substr( $fields[ $column ]['Field'], 7 ) );
						$data_type = $fields[ $column ]['Type'];
						$null = strtolower( $fields[ $column ]['Null'] ) == 'no' ? ' NOT NULL' : '';
						$default = $fields[ $column ]['Default'] !== '' && $fields[ $column ]['Default'] !== NULL ? " DEFAULT '" . addslashes( $fields[ $column ]['Default'] ) . "'" : ( strtolower( $fields[ $column ]['Null'] ) == 'yes' ? ' DEFAULT NULL' : '' );

						$change_columns[] = "CHANGE {$column} `{$new_column}` {$data_type}{$null}{$default}";
					}
				}
				if ( ! empty( $change_columns ) ) {
					$wpdb->query( "ALTER TABLE `{$table}` " . implode( ", ", $change_columns ) );
				}

				// Drop columns
				$drop_columns = array( 'marker_json', 'paid_amount', 'alive_days', 'paymentmethod', 'expire_notification', 'exp2', 'exp3', 'post_location_id', 'post_locations' );

				$query_drop_columns = array();
				foreach( $drop_columns as $drop_column ) {
					if ( in_array( $drop_column, $columns ) ) {
						$query_drop_columns[] = "DROP `{$drop_column}`";
					}
				}
				if ( ! empty( $query_drop_columns ) ) {
					$wpdb->query( "ALTER TABLE `{$table}` " . implode( ", ", $query_drop_columns ) );
				}

				self::update_log( 'update_200_post_fields_' . $post_type );
			}
		}

		do_action( 'geodir_update_200_post_fields', $post_types );
	}

	public static function update_200_reviews() {
		global $wpdb;

		if ( self::is_done( 'update_200_reviews' ) ) {
			return;
		}

		$reviews_table = GEODIR_REVIEW_TABLE;
		
		$wpdb->query( "ALTER TABLE `{$reviews_table}` 
			DROP `id`,
			CHANGE post_id post_id bigint(20) DEFAULT '0',
			DROP `post_title`,
			CHANGE post_type post_type varchar(20) DEFAULT '',
			CHANGE user_id user_id bigint(20) DEFAULT '0', 
			DROP `rating_ip`,
			CHANGE overall_rating rating float DEFAULT '0',
			CHANGE comment_images attachments text DEFAULT '',
			DROP `status`,
			DROP `post_status`,
			DROP `post_date`,
			CHANGE post_city city varchar(50) DEFAULT '',
			CHANGE post_region region varchar(50) DEFAULT '',
			CHANGE post_country country varchar(50) DEFAULT '',
			CHANGE post_latitude latitude varchar(22) DEFAULT '',
			CHANGE post_longitude longitude varchar(22) DEFAULT '',
			DROP `comment_content`;" 
		);

		if ( self::needs_upgrade( 'review_rating' ) ) {
			$columns = @$wpdb->get_results("DESC {$reviews_table}");

			$fields = array();
			foreach ( $columns as $key => $column ) {
				$fields[ $column->Field ] = (array) $column;
			}
			$columns = array_keys( $fields );

			if ( ! in_array( 'wasthis_review', $columns ) ) {
				$wpdb->query( "ALTER TABLE {$reviews_table} ADD `wasthis_review` int(11) NOT NULL;" );
			}

			if ( ! in_array( 'read_unread', $columns ) ) {
				$wpdb->query( "ALTER TABLE {$reviews_table} ADD `read_unread` VARCHAR(50) NOT NULL;" );
			}

			if ( ! in_array( 'total_images', $columns ) ) {
				$wpdb->query( "ALTER TABLE {$reviews_table} ADD `total_images` int(11) NOT NULL;" );
			}
		}

		$wpdb->query( "ALTER TABLE `{$reviews_table}` CHANGE comment_id comment_id bigint(20) DEFAULT NULL, ADD UNIQUE (`comment_id`);" );

		self::update_log( 'update_200_reviews' );
	}

	public static function update_200_attachments() {
		global $wpdb;
		
		if ( self::is_done( 'update_200_attachments' ) ) {
			return;
		}
		
		$attachments_table = GEODIR_ATTACHMENT_TABLE;

		$wpdb->query( "ALTER TABLE `{$attachments_table}` 
			DROP `content`, 
			CHANGE `is_featured` `featured` CHAR(1) NULL DEFAULT '0',
			CHANGE `is_approved` `is_approved` CHAR(1) NULL DEFAULT '1',
			ADD `date_gmt` datetime NULL default NULL AFTER `post_id`,
			ADD `type` varchar(254) NULL DEFAULT 'post_images';" 
		);

		$wpdb->query( "ALTER TABLE `{$attachments_table}` 
			CHANGE `featured` `featured` TINYINT(1) NULL DEFAULT '0', 
			CHANGE `is_approved` `is_approved` TINYINT(1) NULL DEFAULT '1';" 
		);

		self::update_log( 'update_200_attachments' );
	}

	private static function create_tables() {
		global $wpdb;

		$wpdb->hide_errors();

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		if ( ! self::is_done( 'create_tables' ) ) {
			dbDelta( GeoDir_Admin_Install::get_schema() );

			self::update_log( 'create_tables' );
		}

		do_action( 'geodir_update_200_create_tables' );
	}

	private static function insert_default_fields() {
		$post_types = self::v2_post_types( true );

		if ( ! empty( $post_types ) ) {
			foreach ( $post_types as $key => $post_type ) {
				if ( empty( $post_type ) ) {
					continue;
				}

				if ( self::is_done( 'insert_default_fields_' . $post_type ) ) {
					continue;
				}

				add_filter( 'geodir_before_default_custom_fields_saved', array( __CLASS__, 'filter_custom_fields_saved' ), 100, 1 );

				GeoDir_Admin_Install::insert_default_fields( $post_type );

				remove_filter( 'geodir_before_default_custom_fields_saved', array( __CLASS__, 'filter_custom_fields_saved' ), 100, 1 );

				// update custom fields sort order
				self::update_200_fields_sort_order( $post_type );

				self::update_log( 'insert_default_fields_' . $post_type );
			}
		}
	}

	private static function insert_default_tabs() {
		global $wpdb;

		$custom_fields_table = GEODIR_CUSTOM_FIELDS_TABLE;
		$tabs_layout_table = GEODIR_TABS_LAYOUT_TABLE;

		$post_types = self::v2_post_types();

		if ( ! empty( $post_types ) ) {
			foreach ( $post_types as $post_type => $data ) {
				if ( empty( $post_type ) ) {
					continue;
				}

				if ( self::is_done( 'insert_default_tabs_' . $post_type ) ) {
					continue;
				}

				GeoDir_Admin_Install::insert_default_tabs( $post_type );

				self::update_log( 'insert_default_tabs_' . $post_type );
			}
		}

		// merge tabs from custom fields
		if ( ! self::is_done( 'insert_default_tabs_owntab_data' ) ) {
			$results = $wpdb->get_results( "SELECT post_type, htmlvar_name, frontend_title, field_icon, sort_order FROM `" . GEODIR_CUSTOM_FIELDS_TABLE . "` WHERE show_in LIKE '%[owntab]%' AND is_active = '1'" );
			if ( ! empty( $results ) ) {
				foreach ( $results as $key => $row ) {
					if ( $row->htmlvar_name && ! in_array( $row->htmlvar_name, array( 'post_content', 'post_images' ) ) ) {
						$field = array(
							'post_type'     => $row->post_type,
							'tab_layout'    => 'post',
							'tab_type'      => 'meta',
							'tab_name'      => __( $row->frontend_title, 'geodirectory' ),
							'tab_icon'      => ( geodir_is_fa_icon( $row->field_icon ) ? $row->field_icon : '' ),
							'tab_key'       => $row->htmlvar_name,
							'tab_content'   => '',
							'sort_order'    => $row->sort_order,
							'tab_level'     => '0',
							'tab_parent'    => '0'
						);

						GeoDir_Settings_Cpt_Tabs::save_tab_item( $field );
					}
				}
			}

			self::update_log( 'insert_default_tabs_owntab_data' );
		}

		if ( ! self::is_done( 'insert_default_tabs_plugins_data' ) ) {
			if ( self::needs_upgrade( 'custom_post_types' ) ) {
				$results = $wpdb->get_results( "SELECT post_type, htmlvar_name, frontend_title, field_icon FROM `{$custom_fields_table}` WHERE field_type = 'link_posts' AND is_active = '1' ORDER BY id ASC" );
				if ( ! empty( $results ) ) {
					$sort_order = (int) $wpdb->get_var( "SELECT MAX( sort_order ) FROM {$tabs_layout_table} LIMIT 1" );
					foreach ( $results as $key => $row ) {
						$sort_order++;
						$field = array(
							'post_type'     => $row->post_type,
							'tab_layout'    => 'post',
							'tab_type'      => 'meta',
							'tab_name'      => __( $row->frontend_title, 'geodirectory' ),
							'tab_icon'      => $row->field_icon,
							'tab_key'       => $row->htmlvar_name,
							'tab_content'   => '',
							'sort_order'    => $sort_order,
							'tab_level'     => '0',
							'tab_parent'    => '0'
						);

						GeoDir_Settings_Cpt_Tabs::save_tab_item( $field );

						$sort_by = geodir_get_posts_default_sort( $row->post_type );
						if ( empty( $sort_by ) ) {
							$sort_by = 'latest';
						}

						$sort_order++;
						$field = array(
							'post_type'     => $row->htmlvar_name,
							'tab_layout'    => 'post',
							'tab_type'      => 'shortcode',
							'tab_name'      => $post_types[ $row->post_type ]['labels']['name'],
							'tab_icon'      => $row->field_icon,
							'tab_key'       => $post_types[ $row->post_type ]['has_archive'],
							'tab_content'   => '[gd_listings post_type="' . $row->post_type . '" linked_posts="from" sort_by="' . esc_attr( $sort_by ) . '" post_limit=5 layout=2 mb=3]',
							'sort_order'    => $sort_order,
							'tab_level'     => '0',
							'tab_parent'    => '0'
						);

						GeoDir_Settings_Cpt_Tabs::save_tab_item( $field );
					}
				}
			}

			if ( self::needs_upgrade( 'franchise_manager' ) ) {
				$results = $wpdb->get_results( "SELECT post_type, field_icon FROM `{$custom_fields_table}` WHERE htmlvar_name = 'franchise' AND is_active = '1' ORDER BY id ASC" );
				if ( ! empty( $results ) ) {
					$sort_order = (int) $wpdb->get_var( "SELECT MAX( sort_order ) FROM {$tabs_layout_table} LIMIT 1" );
					foreach ( $results as $key => $row ) {
						$sort_order++;
						$field = array(
							'post_type'     => $row->post_type,
							'tab_layout'    => 'post',
							'tab_type'      => 'shortcode',
							'tab_name'      => 'Franchises',
							'tab_icon'      => $row->field_icon,
							'tab_key'       => 'franchises',
							'tab_content'   => '[gd_listings post_type="' . $row->post_type . '" sort_by="latest" title_tag="h3" layout="list" post_limit="5" franchise_of="auto"]',
							'sort_order'    => $sort_order,
							'tab_level'     => '0',
							'tab_parent'    => '0'
						);
						
						GeoDir_Settings_Cpt_Tabs::save_tab_item( $field );
					}
				}
			}

			self::update_log( 'insert_default_tabs_plugins_data' );
		}

		// update detail tabs sort order
		self::update_200_post_tabs_sort_order();
	}

	private static function create_pages() {
		global $wpdb;
		
		if ( self::is_done( 'create_pages' ) ) {
			return;
		}

		$page_location = geodir_get_option( 'page_location' );
		if ( $page_location && ( $row = $wpdb->get_row( $wpdb->prepare( "SELECT ID, post_content FROM {$wpdb->posts} WHERE ID = %d", array( (int)$page_location ) ) ) ) ) {
			$default_content = GeoDir_Defaults::page_location_content();
			if (strpos($row->post_content, $default_content) !== false) {}
			else{// only add the default content if it is not already there.
				$post_content = $row->post_content . ' ' . $default_content;
				$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->posts} SET post_content = %s WHERE ID = %d", array( trim( $post_content ), (int)$page_location ) ) );
			}

		}
		$page_add = geodir_get_option( 'page_add' );
		if ( $page_add && ( $row = $wpdb->get_row( $wpdb->prepare( "SELECT ID, post_content FROM {$wpdb->posts} WHERE ID = %d", array( (int)$page_add ) ) ) ) ) {
			$default_content = GeoDir_Defaults::page_add_content();
			if (strpos($row->post_content, $default_content) !== false) {}
			else{// only add the default content if it is not already there.
				$post_content = $row->post_content . ' ' . $default_content;
				$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->posts} SET post_content = %s WHERE ID = %d", array( trim( $post_content ), (int)$page_add ) ) );
			}

		}

		GeoDir_Admin_Install::create_pages();

		self::update_log( 'create_pages' );
	}

	/**
	 * Create cron jobs (clear them first).
     *
     * @since 2.0.0
	 */
	private static function create_cron_jobs() {
		//@todo add crons here
		if ( self::is_done( 'create_cron_jobs' ) ) {
			return;
		}

		wp_clear_scheduled_hook( 'geodirectory_tracker_send_event' );
		wp_schedule_event( time(), apply_filters( 'geodirectory_tracker_event_recurrence', 'daily' ), 'geodirectory_tracker_send_event' );

		self::update_log( 'create_cron_jobs' );
	}

	/**
	 * See if we need the wizard or not.
	 *
	 * @since 2.0.0
	 */
	private static function maybe_enable_setup_wizard() {
		GeoDir_Admin_Notices::add_notice( 'install' );
	}

	/**
	 * Update GeoDirectory version to current.
     *
     * @since 2.0.0
	 */
	private static function update_gd_version() {
		delete_option( 'geodirectory_version' );
		add_option( 'geodirectory_version', GEODIRECTORY_VERSION );

		do_action( 'geodir_update_200_update_gd_version' );
	}

	

	public static function filter_custom_fields_saved( $fields ) {
		global $wpdb;

		$filter_fields = array();
		foreach( $fields as $key => $field ) {
			if ( $wpdb->get_var( $wpdb->prepare( "SELECT id FROM " . GEODIR_CUSTOM_FIELDS_TABLE . " WHERE post_type = %s AND htmlvar_name = %s", array( $field['post_type'], $field['htmlvar_name'] ) ) ) ) {
				continue;
			}
			if ( empty( $field['sort_order'] ) ) {
				if ( $field['htmlvar_name'] == 'post_title' ) {
					$field['sort_order'] = 1;
				} else if ( $field['htmlvar_name'] == 'post_content' ) {
					$field['sort_order'] = 2;
				} else if ( $field['htmlvar_name'] == 'post_category' ) {
					$field['sort_order'] = 3;
				} else if ( $field['htmlvar_name'] == 'post_tags' ) {
					$field['sort_order'] = 4;
				} else if ( $field['htmlvar_name'] == 'address' ) {
					$field['sort_order'] = 5;
				}
			}
			$filter_fields[] = $field;
		}

		return $filter_fields;
	}

	public static function update_200_fields_sort_order( $post_type ) {
		global $wpdb;

		if ( ! empty( $post_type ) ) {
			$results = $wpdb->get_results( $wpdb->prepare( "SELECT id, htmlvar_name, sort_order FROM `" . GEODIR_CUSTOM_FIELDS_TABLE . "` WHERE post_type = %s ORDER BY sort_order ASC", array( $post_type ) ) );

			if ( ! empty( $results ) ) {
				$save_order = array();
				$sort_order = 5;
				foreach ( $results as $key => $row ) {
					if ( in_array( $row->htmlvar_name, array( 'post_title', 'post_content', 'post_category', 'post_tags', 'address' ) ) ) {
						if ( $row->htmlvar_name == 'post_title' ) {
							$row->sort_order = 1;
						} else if ( $row->htmlvar_name == 'post_content' ) {
							$row->sort_order = 2;
						} else if ( $row->htmlvar_name == 'post_category' ) {
							$row->sort_order = 3;
						} else if ( $row->htmlvar_name == 'post_tags' ) {
							$row->sort_order = 4;
						} else {
							$row->sort_order = 5;
						}
					} else {
						$sort_order++;
						$row->sort_order = $sort_order;
					}
					$save_order[] = $row;
				}

				foreach ( $save_order as $key => $save ) {
					$wpdb->query( $wpdb->prepare( "UPDATE `" . GEODIR_CUSTOM_FIELDS_TABLE . "` SET `sort_order` = %d WHERE id = %d", array( $save->sort_order, $save->id ) ) );
				}
			}
		}
	}

	public static function update_200_sort_fields_sort_order() {
		global $wpdb;

		$post_types = self::v2_post_types( true );

		if ( ! empty( $post_types ) ) {
			foreach ( $post_types as $k => $post_type ) {
				if ( empty( $post_type ) ) {
					continue;
				}

				$results = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM `" . GEODIR_CUSTOM_SORT_FIELDS_TABLE . "` WHERE post_type = %s ORDER BY sort_order ASC, id ASC", array( $post_type ) ) );

				if ( ! empty( $results ) ) {
					$sort_order = 0;
					foreach ( $results as $key => $row ) {
						$sort_order++;
						$wpdb->update( GEODIR_CUSTOM_SORT_FIELDS_TABLE, array( 'sort_order' => $sort_order ), array( 'id' => $row->id ) );
					}
				}
			}
		}
	}

	public static function update_200_post_tabs_sort_order() {
		global $wpdb;

		$post_types = self::v2_post_types( true );

		if ( ! empty( $post_types ) ) {
			foreach ( $post_types as $key => $post_type ) {
				if ( empty( $post_type ) ) {
					continue;
				}

				$results = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM `" . GEODIR_TABS_LAYOUT_TABLE . "` WHERE post_type = %s ORDER BY sort_order ASC, id ASC", array( $post_type ) ) );

				if ( ! empty( $results ) ) {
					$sort_order = 0;
					foreach ( $results as $key => $row ) {
						$sort_order++;
						$wpdb->update( GEODIR_TABS_LAYOUT_TABLE, array( 'sort_order' => $sort_order ), array( 'id' => $row->id ) );
					}
				}
			}
		}
	}

	// Payment Manager
	public static function update_200_pm_get_options( $options = array() ) {
		$merge_options = array(
			'pm_listing_expiry' => get_option( 'geodir_listing_expiry' ),
			'pm_listing_ex_status' => get_option( 'geodir_listing_ex_status' ),
			'pm_paid_listing_status' => get_option( 'geodir_paid_listing_status' ),
			'pm_free_package_renew' => get_option( 'geodir_payment_free_package_renew' ),
			'pm_cart' => defined( 'WPINV_VERSION' ) ? 'invoicing' : '',
			'email_user_renew_success' => '1',
			'email_user_renew_success_subject' => get_option( 'geodir_post_renew_success_email_subject' ),
			'email_user_renew_success_body' => get_option( 'geodir_post_renew_success_email_content' ),
			'email_user_upgrade_success' => '1',
			'email_user_upgrade_success_subject' => get_option( 'geodir_post_upgrade_success_email_subject' ),
			'email_user_upgrade_success_body' => get_option( 'geodir_post_upgrade_success_email_content' ),
			'email_user_pre_expiry_reminder' => get_option( 'geodir_listing_preexpiry_notice_disable' ),
			'email_user_pre_expiry_reminder_subject' => get_option( 'geodir_renew_email_subject' ),
			'email_user_pre_expiry_reminder_body' => get_option( 'geodir_renew_email_content' ),
			'email_admin_renew_success' => '1',
			'email_admin_renew_success_subject' => get_option( 'geodir_post_renew_success_email_subject_admin' ),
			'email_admin_renew_success_body' => get_option( 'geodir_post_renew_success_email_content_admin' ),
			'email_admin_upgrade_success' => '1',
			'email_admin_upgrade_success_subject' => get_option( 'geodir_post_upgrade_success_email_subject_admin' ),
			'email_admin_upgrade_success_body' => get_option( 'geodir_post_upgrade_success_email_content_admin' ),
			'email_user_post_expire' => '1',
			'email_user_post_downgrade' => '1',
		);
		$notice_days = array();
		if ( get_option( 'geodir_listing_preexpiry_notice_days' ) !== false ) {
			$notice_days[] = absint( get_option( 'geodir_listing_preexpiry_notice_days' ) );
		}
		if ( get_option( 'geodir_listing_preexpiry_notice_days2' ) !== false ) {
			$notice_days[] = absint( get_option( 'geodir_listing_preexpiry_notice_days2' ) );
		}
		if ( get_option( 'geodir_listing_preexpiry_notice_days3' ) !== false ) {
			$notice_days[] = absint( get_option( 'geodir_listing_preexpiry_notice_days3' ) );
		}
		$merge_options['email_user_pre_expiry_reminder_days'] = ! empty( $notice_days ) ? array_unique( $notice_days ) : '';

		return array_merge( $options, $merge_options );
	}

	public static function update_200_pm_create_default_options() {
		if ( ! ( defined( 'GEODIRPAYMENT_VERSION' ) && version_compare( GEODIRPAYMENT_VERSION, '2.5.0.0', '<=' ) ) ) {
			$default_options = array(
				'pm_listing_expiry' => '1',
				'pm_listing_ex_status' => 'gd-expired',
				'pm_paid_listing_status' => 'publish',
				'pm_free_package_renew' => '0',
				'email_user_renew_success' => '1',
				'email_user_upgrade_success' => '1',
				'email_admin_renew_success' => '1',
				'email_admin_upgrade_success' => '1',
				'email_user_post_expire' => '1',
				'email_user_post_downgrade' => '1',
			);

			foreach ( $default_options as $key => $value ) {
				geodir_update_option( $key, $value );
			}
		}
	}

	public static function update_200_pm_create_tables() {
		global $wpdb, $plugin_prefix;

		$collate = '';
		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		// Tables
		$packages_table = $plugin_prefix . 'price';
		$package_meta_table = $plugin_prefix . 'pricemeta';
		$post_packages_table = $plugin_prefix . 'post_packages';
		$invoice_table = $plugin_prefix . 'invoice';
		$custom_fields_table = GEODIR_CUSTOM_FIELDS_TABLE;

		if ( ! self::is_done( 'update_200_pm_create_tables' ) ) {
			// Package meta table
			$schema = "CREATE TABLE {$package_meta_table} (
			  `meta_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `package_id` int(11) unsigned NOT NULL DEFAULT '0',
			  `meta_key` varchar(255) DEFAULT NULL,
			  `meta_value` text,
			  PRIMARY KEY (`meta_id`),
			  KEY `package_id` (`package_id`),
			  KEY `meta_key` (`meta_key`(191))
			) $collate; ";

			// Post package relationship table
			$schema .= "CREATE TABLE {$post_packages_table} (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `post_id` int(11) unsigned NOT NULL DEFAULT '0',
			  `package_id` int(11) unsigned NOT NULL DEFAULT '0',
			  `cart` varchar(50) NOT NULL,
			  `invoice_id` int(11) unsigned NOT NULL DEFAULT '0',
			  `product_id` int(11) unsigned NOT NULL DEFAULT '0',
			  `task` varchar(50) NOT NULL,
			  `meta` text NOT NULL,
			  `date` datetime NOT NULL,
			  `status` varchar(20) NOT NULL,
			  PRIMARY KEY (`id`)
			) $collate; ";

			dbDelta( $schema );

			// Change
			$wpdb->query( "ALTER TABLE `{$packages_table}` CHANGE `pid` `id` int(11) unsigned NOT NULL AUTO_INCREMENT;" );
			$wpdb->query( "ALTER TABLE `{$packages_table}` 
				CHANGE `title` `name` varchar(255) NOT NULL, 
				CHANGE `amount` `amount` varchar(50) NOT NULL DEFAULT '0', 
				CHANGE `sub_units` `time_unit` varchar(1) NOT NULL DEFAULT 'M', 
				CHANGE `sub_active` `recurring` tinyint(1) NOT NULL DEFAULT '0', 
				CHANGE `sub_units_num_times` `recurring_limit` int(11) unsigned NOT NULL DEFAULT '0', 
				CHANGE `sub_num_trial_days` `trial_interval` int(11) unsigned NOT NULL DEFAULT '1', 
				CHANGE `sub_num_trial_units` `trial_unit` varchar(1) NOT NULL DEFAULT 'M', 
				CHANGE `downgrade_pkg` `downgrade_pkg` int(11) unsigned NOT NULL DEFAULT '0', 
				CHANGE `is_default` `is_default` tinyint(1) NOT NULL DEFAULT '0';" 
			);
			$wpdb->query( "ALTER TABLE `{$packages_table}` CHANGE `title_desc` `title` text NOT NULL;" );

			// Add
			$wpdb->query( "ALTER TABLE `{$packages_table}` 
				ADD `time_interval` int(11) unsigned NOT NULL DEFAULT '1' AFTER `days`, 
				ADD `description` text NOT NULL AFTER `title`, 
				ADD `post_status` varchar(20) NOT NULL AFTER `status`, 
				ADD `trial` tinyint(1) NOT NULL DEFAULT '0' AFTER `sub_units_num`;" 
			);
			$wpdb->query( "ALTER TABLE `{$packages_table}` 
				ADD `fa_icon` varchar(50) NOT NULL AFTER `description`, 
				ADD `trial_amount` varchar(50) NOT NULL DEFAULT '0' AFTER `trial_interval`;" 
			);

			// Update
			$wpdb->query( "UPDATE `{$packages_table}` SET `time_interval` = days, time_unit = 'D', post_status= '" . get_option( 'geodir_paid_listing_status' ) . "' WHERE recurring != '1'" );
			$wpdb->query( "UPDATE `{$packages_table}` SET `time_interval` = sub_units_num, post_status= '" . get_option( 'geodir_paid_listing_status' ) . "' WHERE recurring = '1'" );
			$wpdb->query( "UPDATE `{$packages_table}` SET `trial` = '1' WHERE trial_interval > 0" );

			self::update_log( 'update_200_pm_create_tables' );
		}

		// packages data
		if ( ! self::is_done( 'update_200_pm_create_tables_packages_data' ) ) {
			$results = $wpdb->get_results( "SELECT * FROM `{$packages_table}` ORDER BY id ASC" );
			if ( ! empty( $results ) ) {
				$metas = array( 'cat', 'is_featured', 'image_limit', 'cat_limit', 'recurring_pkg', 'google_analytics', 'use_desc_limit', 'desc_limit', 'tag_limit', 'has_upgrades', 'enable_franchise', 'franchise_cost', 'franchise_limit', 'disable_editor' );

				foreach ( $results as $row ) {
					$row = (array)$row;
					$package_id = $row['id'];

					$rows = array();
					// exclude_field
					$fields = $wpdb->get_col( "SELECT htmlvar_name FROM `{$custom_fields_table}` WHERE post_type = '" . $row['post_type'] . "' AND is_default != '1' AND htmlvar_name != '' AND htmlvar_name != 'post_images' AND htmlvar_name != 'franchise' AND NOT FIND_IN_SET( {$package_id}, packages )" );
					if ( isset( $row['enable_franchise'] ) && empty( $row['enable_franchise'] ) ) {
						$fields[] = 'franchise';
					}
					$meta_value = ! empty( $fields ) ? implode( ",", $fields ) : '';
					$rows[] = "( {$package_id}, 'exclude_field', '{$meta_value}' )";

					foreach ( $metas as $key ) {
						if ( isset( $row[ $key ] ) ) {
							$meta_value = $row[ $key ];

							if ( $key == 'cat' ) {
								$meta_key = 'exclude_category';
							} else if ( $key == 'cat_limit' ) {
								$meta_key = 'category_limit';
							} else if ( $key == 'tag_limit' ) {
								$meta_value = ! empty( $row[ 'use_tag_limit' ] ) ? $row[ 'use_tag_limit' ] : 0;
							} else if ( $key == 'recurring_pkg' ) {
								$meta_key = 'no_recurring';
							} else {
								$meta_key = $key;
							}

							$rows[] = "( {$package_id}, '{$meta_key}', '{$meta_value}' )";
						}
					}

					// invoicing_product_id
					$args = array(
						'post_type'      => 'wpi_item',
						'posts_per_page' => 1,
						'post_status'    => 'any',
						'orderby'        => 'ID',
						'order'          => 'ASC',
						'meta_query'     => array( 
							array(
								'key'   => '_wpinv_type',
								'value' => 'package',
							),
							array(
								'key'   => '_wpinv_custom_id',
								'value' => $package_id,
							)
						)
					);
					$posts = get_posts( $args );
					if ( ! empty( $posts[0] ) ) {
						$meta_value = $posts[0]->ID;
						$rows[] = "({$package_id}, 'invoicing_product_id', '{$meta_value}')";
					}

					$wpdb->query( "INSERT INTO `{$package_meta_table}` (`package_id`, `meta_key`, `meta_value`) VALUES " . implode( ", ", $rows ) . ";" );
				}
			}

			self::update_log( 'update_200_pm_create_tables_packages_data' );
		}

		// invoice to post_packages data
		if ( ! self::is_done( 'update_200_pm_create_tables_invoices_data' ) ) {
			$results = $wpdb->get_results( "SELECT * FROM `{$invoice_table}` ORDER BY id ASC" );
			if ( ! empty( $results ) ) {
				$geodir_package_product = array();
				$geodir_package_exists = array();

				foreach ( $results as $row ) {
					if ( ! isset( $geodir_package_exists[ $row->package_id ] ) ) {
						if ( $wpdb->get_var( "SELECT id FROM `{$packages_table}` WHERE id = '" . $row->package_id . "' LIMIT 1" ) ) {
							$geodir_package_exists[ $row->package_id ] = true;
						} else {
							$geodir_package_exists[ $row->package_id ] = false;
						}
					}
					if ( empty( $row->invoice_id ) || empty( $geodir_package_exists[ $row->package_id ] ) ) {
						continue;
					}

					$invoice_post = $wpdb->get_row( "SELECT post_date, post_status FROM `{$wpdb->posts}` WHERE ID = '" . $row->invoice_id . "' LIMIT 1" );
					if ( empty( $invoice_post ) ) {
						continue;
					}
					if ( ! empty( $geodir_package_product[ $row->package_id ] ) ) {
						$product_id = $geodir_package_product[ $row->package_id ];
					} else {
						$product_id = $wpdb->get_var( "SELECT meta_value FROM `{$package_meta_table}` WHERE package_id = '" . $row->package_id . "' AND meta_key = 'invoicing_product_id' ORDER BY meta_id ASC LIMIT 1" );
						$geodir_package_product[ $row->package_id ] = $product_id;
					}
					$task = str_replace( '_listing', '', $row->invoice_type );
					if ( $task == 'add' ) {
						$task = 'new';
					}
					$meta = (array) maybe_unserialize( $row->invoice_data );
					$meta['task'] = $task;
					$data = array(
						'id' => $row->id,
						'post_id' => $row->post_id,
						'package_id' => $row->package_id,
						'cart' => 'invoicing',
						'invoice_id' => $row->invoice_id,
						'product_id' => $product_id,
						'task' => $task,
						'meta' => maybe_serialize( $meta ),
						'date' => $invoice_post->post_date,
						'status' => $invoice_post->post_status,
					);
					$wpdb->insert( $post_packages_table, $data, array( '%d', '%d', '%d', '%s', '%d', '%d', '%s', '%s', '%s', '%s' ) );
				}
			}

			self::update_log( 'update_200_pm_create_tables_invoices_data' );
		}
	}

	public static function update_200_pm_update_version() {
		$version = defined( 'GEODIR_PRICING_VERSION' ) && version_compare( GEODIR_PRICING_VERSION, '2.5.0.0', '>=' ) ? GEODIR_PRICING_VERSION : '2.5.0.0';

		delete_option( 'geodir_pricing_version' );
		add_option( 'geodir_pricing_version', $version );

		delete_option( 'geodir_pricing_db_version' );
		add_option( 'geodir_pricing_db_version', $version );
	}

	// Custom post types
	public static function update_200_cp_get_options( $options = array() ) {
		$merge_options = array(
			'linked_post_types' => get_option( 'geodir_linked_post_types' ),
			'uninstall_geodir_custom_posts' => get_option( 'geodir_un_geodir_custom_posts' ),
		);

		return array_merge( $options, $merge_options );
	}

	public static function update_200_cp_create_default_options() {
		if ( ! ( defined( 'GEODIR_CP_VERSION' ) && version_compare( GEODIR_CP_VERSION, '2.0.0.0', '<=' ) ) ) {
			$default_options = array(
				'linked_post_types' => '',
			);

			foreach ( $default_options as $key => $value ) {
				geodir_update_option( $key, $value );
			}
		}
	}

	public static function update_200_cp_custom_fields() {
		global $wpdb, $plugin_prefix;

		if ( self::is_done( 'update_200_cp_custom_fields' ) ) {
			return;
		}

		// Tables
		$custom_fields_table = GEODIR_CUSTOM_FIELDS_TABLE;
		$packages_table = $plugin_prefix . 'price';

		$post_types = self::v1_post_types();

		if ( ! empty( $post_types ) ) {
			foreach ( $post_types as $post_type => $data ) {
				if ( empty( $post_type ) ) {
					continue;
				}

				if ( $post_type == 'gd_event' ) {
					$data['link_business'] = 1;
					$data['linkable_to'] = 'gd_place';
				}

				if ( ! empty( $data['link_business'] ) && ! empty( $data['linkable_to'] ) && ! empty( $post_types[ $data['linkable_to'] ] ) ) {
					$linked_to_name = $post_types[ $data['linkable_to'] ]['labels']['singular_name'];

					$packages = '';
					if ( self::needs_upgrade( 'payment_manager' ) ) {
						$results = $wpdb->get_col( $wpdb->prepare( "SELECT pid FROM {$packages_table} WHERE post_type = %s", $post_type ) );
						if ( ! empty( $results ) ) {
							$packages = implode( ',', $results );
						}
					}

					$link_data = array(
						'post_type' => $post_type,
						'data_type' => 'TEXT', 
						'field_type' => 'link_posts', 
						'field_type_key' => $data['linkable_to'], 
						'admin_title' => 'Link Posts: ' . $linked_to_name, 
						'frontend_desc' => wp_sprintf( 'Select your %s to link with this %s.', $linked_to_name, $data['labels']['singular_name'] ), 
						'frontend_title' => $linked_to_name, 
						'htmlvar_name' => $data['linkable_to'], 
						'sort_order' => '-1', 
						'is_active' => '1', 
						'show_in' => '[detail]',  
						'packages' => $packages, 
						'extra_fields' => maybe_serialize( array( 'max_posts' => 1, 'all_posts' => 0 ) ), 
						'field_icon' => 'fas fa-link'
					);

					$wpdb->insert( $custom_fields_table, $link_data, array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s', '%s' ) );
				}
			}
		}

		self::update_log( 'update_200_cp_custom_fields' );
	}

	public static function update_200_cp_post_fields( $post_types ) {
		global $wpdb;

	}

	public static function update_200_cp_create_tables() {
		global $wpdb, $plugin_prefix;

		$collate = '';
		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		// Tables
		$cp_link_posts_table = $plugin_prefix . 'cp_link_posts';

		if ( ! self::is_done( 'update_200_cp_create_tables' ) ) {
			// Link posts table
			$schema = "CREATE TABLE {$cp_link_posts_table} (
				post_type varchar(50) NOT NULL,
				post_id int(11) NOT NULL DEFAULT 0,
				linked_id int(11) NOT NULL DEFAULT 0,
				linked_post_type varchar(50) NOT NULL,
				PRIMARY KEY (post_id,linked_id),
				KEY post_id (post_id)
			) $collate;";
			$wpdb->query( $schema );

			self::update_log( 'update_200_cp_create_tables' );
		}

		$post_types = self::v2_post_types( true );

		if ( ! empty( $post_types ) ) {
			foreach ( $post_types as $key => $post_type ) {
				if ( empty( $post_type ) ) {
					continue;
				}

				if ( self::is_done( 'update_200_cp_link_posts_data_' . $post_type ) ) {
					continue;
				}

				$table = $wpdb->prefix . 'geodir_' . $post_type . '_detail';

				$results = @$wpdb->get_results("DESC {$table}");

				if ( empty( $results ) ) {
					continue;
				}

				$columns = array();
				foreach ( $results as $key => $row ) {
					$columns[] = $row->Field;
				}

				if ( ! in_array( 'link_business', $columns ) ) {
					continue;
				}

				$results = $wpdb->get_results( "SELECT post_id, link_business FROM {$table} WHERE link_business > 0" );
				if ( empty( $results ) ) {
					continue;
				}

				foreach ( $results as $row ) {
					$linked_post_type = get_post_type( (int) $row->link_business );

					if ( $linked_post_type ) {
						$link_data = array(
							'post_type' => $post_type,
							'post_id' => $row->post_id,
							'linked_id' => (int) $row->link_business,
							'linked_post_type' => $linked_post_type
						);
						$wpdb->insert( $cp_link_posts_table, $link_data, array( '%s', '%d', '%d', '%s' ) );
					}
				}

				self::update_log( 'update_200_cp_link_posts_data_' . $post_type );
			}
		}
	}

	public static function update_200_cp_update_version() {
		$version = defined( 'GEODIR_CP_VERSION' ) && version_compare( GEODIR_CP_VERSION, '2.0.0.0', '>=' ) ? GEODIR_CP_VERSION : '2.0.0.0';

		delete_option( 'geodir_cp_version' );
		add_option( 'geodir_cp_version', $version );

		delete_option( 'geodir_cp_db_version' );
		add_option( 'geodir_cp_db_version', $version );
	}

	// Location Manager
	public static function update_200_lm_create_default_options() {
		if ( ! ( defined( 'GEODIRLOCATION_VERSION' ) && version_compare( GEODIRLOCATION_VERSION, '2.0.0.0', '<=' ) ) ) {
			$default_options = array(
				'lm_home_go_to' => 'root',
				'lm_default_country' => 'multi',
				'lm_selected_countries' => '',
				'lm_hide_country_part' => '0',
				'lm_default_region' => 'multi',
				'lm_selected_regions' => '',
				'lm_hide_region_part' => '0',
				'lm_default_city' => 'multi',
				'lm_selected_cities' => '0',
				'lm_enable_neighbourhoods' => '0',
				'lm_location_address_fill' => '0',
				'lm_location_dropdown_all' => '0',
				'lm_set_address_disable' => '0',
				'lm_set_pin_disable' => '0',
				'lm_location_no_of_records' => '50',
				'lm_disable_term_auto_count' => '0',
				'lm_sitemap_exclude_location' => '0',
				'lm_sitemap_exclude_cats' => '0',
				'lm_sitemap_exclude_tags' => '1'
			);

			foreach ( $default_options as $key => $value ) {
				geodir_update_option( $key, $value );
			}
		}
	}

	public static function update_200_lm_get_options( $options = array() ) {
		$lm_options = array(
			'lm_home_go_to' => get_option( 'geodir_home_go_to' ),
			'lm_default_country' => get_option( 'geodir_enable_country' ),
			'lm_hide_country_part' => get_option( 'geodir_location_hide_country_part' ),
			'lm_selected_countries' => get_option( 'geodir_selected_countries' ),
			'lm_default_region' => get_option( 'geodir_enable_region' ),
			'lm_hide_region_part' => get_option( 'geodir_location_hide_region_part' ),
			'lm_selected_regions' => get_option( 'geodir_selected_regions' ),
			'lm_default_city' => get_option( 'geodir_enable_city' ),
			'lm_selected_cities' => get_option( 'geodir_selected_cities' ),
			'lm_enable_neighbourhoods' => get_option( 'location_neighbourhoods' ),
			'lm_location_address_fill' => get_option( 'location_address_fill' ),
			'lm_location_dropdown_all' => get_option( 'location_dropdown_all' ),
			'lm_set_address_disable' => get_option( 'location_set_address_disable' ),
			'lm_set_pin_disable' => get_option( 'location_set_pin_disable' ),
			'lm_location_no_of_records' => get_option( 'geodir_location_no_of_records' ),
			'lm_disable_term_auto_count' => get_option( 'geodir_location_disable_term_auto_count' ),
			'lm_sitemap_exclude_location' => get_option( 'gd_location_sitemap_exclude_location', '0' ),
			'lm_sitemap_exclude_cats' => get_option( 'gd_location_sitemap_exclude_cats', '0' ),
			'lm_sitemap_exclude_tags' => get_option( 'gd_location_sitemap_exclude_tags', '1' ),
			'uninstall_geodir_location_manager' => get_option( 'geodir_un_geodir_location_manager' ),
		);

		return array_merge( $options, $lm_options );
	}

	public static function update_200_lm_post_fields( $post_types ) {
		global $wpdb;

		if ( ! empty( $post_types ) && ! self::is_done( 'update_200_lm_post_fields' ) ) {
			foreach ( $post_types as $key => $post_type ) {
				$table = $wpdb->prefix . 'geodir_' . $post_type . '_detail';

				if ( geodir_column_exist( $table, 'neighbourhood' ) ) {
					continue;
				}

				if ( geodir_column_exist( $table, 'post_neighbourhood' ) ) {
					$wpdb->query( "ALTER TABLE `{$table}` CHANGE post_neighbourhood neighbourhood VARCHAR(50) NULL" );
				} else {
					$wpdb->query( "ALTER TABLE `{$table}` ADD `neighbourhood` VARCHAR(50) NULL AFTER longitude" );
				}
			}

			self::update_log( 'update_200_lm_post_fields' );
		}
	}

	public static function update_200_lm_term_metas() {
		global $wpdb;

		if ( self::is_done( 'update_200_lm_term_metas' ) ) {
			return;
		}

		// Migrate tax meta.
		$term_meta_options = $wpdb->get_results( "SELECT option_name, option_value FROM " . $wpdb->options . " WHERE option_name LIKE 'geodir_cat_loc_gd_%'" );

		if ( ! empty( $term_meta_options ) ) {
			$update_meta = array();
			foreach ( $term_meta_options as $key => $option ) {
				$name = str_replace( 'geodir_cat_loc_', '', $option->option_name );
				$value = strpos( $option->option_value, 'a:' ) === 0 ? maybe_unserialize( $option->option_value ) : $option->option_value;

				if ( is_array( $value ) ) { // city
					if ( empty( $value['gd_cat_loc_cat_id'] ) ) {
						continue;
					}
					$term_id = (int) $value['gd_cat_loc_cat_id'];

					if ( isset( $value['gd_cat_loc_default'] ) ) {
						$update_meta[ $term_id ]['gd_desc_custom'] = empty( $value['gd_cat_loc_default'] ) ? true : false;
					}

					if ( ! empty( $value['gd_cat_loc_loc_id'] ) && isset( $value['gd_cat_loc_desc'] ) ) {
						$update_meta[ $term_id ]['gd_desc_id_' . $value['gd_cat_loc_loc_id'] ] = $value['gd_cat_loc_desc'];
					}
				} else { // country, region
					$term_id = 0;
					$type = '';

					if ( strpos( $name, '_co_' ) > 0 ) {
						$type = 'co';
						$names = explode( '_co_', $name, 2 );
						if ( ! empty( $names ) ) {
							$names_id = explode( '_', $names[0] );
							if ( ! empty( $names_id ) && (int) $names_id[ count( $names_id ) - 1 ] > 0 ) {
								$term_id = (int) $names_id[ count( $names_id ) - 1 ];
							}
						}
					} else if ( strpos( $name, '_re_' ) > 0 ) {
						$type = 're';
						$names = explode( '_re_', $name, 2 );
						if ( ! empty( $names ) ) {
							$names_id = explode( '_', $names[0] );
							if ( ! empty( $names_id ) && (int) $names_id[ count( $names_id ) - 1 ] > 0 ) {
								$term_id = (int) $names_id[ count( $names_id ) - 1 ];
							}
						}
					}

					if ( $term_id > 0 ) {
						$update_meta[ $term_id ]['gd_desc_' . $type . '_' . $names[1] ] = $value;
					}
				}
			}

			if ( ! empty( $update_meta ) ) {
				foreach ( $update_meta as $term_id => $metas ) {
					if ( ! empty( $metas ) ) {
						foreach ( $metas as $meta_key => $meta_value ) {
							update_term_meta( $term_id, $meta_key, $meta_value );
						}
					}
				}
			}
		}

		self::update_log( 'update_200_lm_term_metas' );
	}

	public static function update_200_lm_create_tables() {
		global $wpdb, $plugin_prefix;
		
		if ( self::is_done( 'update_200_lm_create_tables' ) ) {
			return;
		}

		// Locations table
		$locations_table = $plugin_prefix . 'post_locations';

		$wpdb->query( "ALTER TABLE `{$locations_table}` 
			DROP `city_meta`, 
			DROP `city_desc`, 
			CHANGE city_latitude latitude varchar(22) NOT NULL, 
			CHANGE city_longitude longitude varchar(22) NOT NULL, 
			CHANGE is_default is_default CHAR(1) NOT NULL DEFAULT '0'" 
		);
		$wpdb->query( "ALTER TABLE `{$locations_table}` CHANGE is_default is_default TINYINT(1) NOT NULL DEFAULT '0'" );

		// Neighbourhoods table
		$neighbourhoods_table = $plugin_prefix . 'post_neighbourhood';

		$wpdb->query( "ALTER TABLE `{$neighbourhoods_table}` ADD image int(11) NOT NULL" );

		// Location seo table
		$seo_table = $plugin_prefix . 'location_seo';

		$wpdb->query( "ALTER TABLE `{$seo_table}` 
			DROP `date_created`, 
			DROP `date_updated`, 
			DROP `seo_title`, 
			CHANGE seo_meta_title meta_title varchar(254) NOT NULL, 
			CHANGE seo_meta_desc meta_desc text NOT NULL, 
			CHANGE seo_desc location_desc text NOT NULL, 
			CHANGE seo_image image varchar(254) NOT NULL, 
			CHANGE seo_image_tagline image_tagline varchar(140) NOT NULL;" 
		);

		self::update_log( 'update_200_lm_create_tables' );
	}

	public static function update_200_lm_update_version() {
		$version = defined( 'GEODIRLOCATION_VERSION' ) && version_compare( GEODIRLOCATION_VERSION, '2.0.0.0', '>=' ) ? GEODIRLOCATION_VERSION : '2.0.0.0';

		delete_option( 'geodir_location_version' );
		add_option( 'geodir_location_version', $version );

		delete_option( 'geodir_location_db_version' );
		add_option( 'geodir_location_db_version', $version );
	}

	// Advance search
	public static function update_200_search_get_options( $options = array() ) {
		$merge_options = array(
			'advs_enable_autocompleter' => get_option( 'geodir_enable_autocompleter' ),
			'advs_autocompleter_autosubmit' => get_option( 'geodir_autocompleter_autosubmit' ),
			'advs_autocompleter_min_chars' => get_option( 'geodir_autocompleter_min_chars' ),
			'advs_autocompleter_max_results' => get_option( 'geodir_autocompleter_max_results' ),
			'advs_autocompleter_filter_location' => get_option( 'geodir_autocompleter_filter_location' ),
			'advs_enable_autocompleter_near' => get_option( 'geodir_enable_autocompleter_near' ),
			'advs_autocompleter_autosubmit_near' => get_option( 'geodir_autocompleter_autosubmit_near' ),
			'advs_first_load_redirect' => get_option( 'geodir_first_load_redirect' ),
			'advs_autolocate_ask' => get_option( 'geodir_autolocate_ask' ),
			'advs_near_me_dist' => get_option( 'geodir_near_me_dist' ),
			'advs_search_display_searched_params' => get_option( 'geodir_search_display_searched_params' ),
			'uninstall_geodir_advance_search_filters' => get_option( 'geodir_un_geodir_advance_search_filters' ),
		);

		return array_merge( $options, $merge_options );
	}

	public static function update_200_search_create_default_options() {
		if ( ! ( defined( 'GEODIRADVANCESEARCH_VERSION' ) && version_compare( GEODIRADVANCESEARCH_VERSION, '2.0.0.0', '<=' ) ) ) {
			$default_options = array(
				'advs_enable_autocompleter' => '1',
				'advs_autocompleter_autosubmit' => '1',
				'advs_autocompleter_min_chars' => '3',
				'advs_autocompleter_max_results' => '10',
				'advs_autocompleter_filter_location' => '',
				'advs_enable_autocompleter_near' => '1',
				'advs_autocompleter_autosubmit_near' => '0',
				'advs_first_load_redirect' => 'no',
				'advs_autolocate_ask' => '0',
				'advs_near_me_dist' => '40',
				'advs_search_display_searched_params' => '0'
			);

			foreach ( $default_options as $key => $value ) {
				geodir_update_option( $key, $value );
			}
		}
	}

	public static function update_200_search_custom_fields() {
		global $wpdb, $plugin_prefix;

		if ( self::is_done( 'update_200_search_custom_fields' ) ) {
			return;
		}

		// Advance search fields table
		$table = $plugin_prefix . 'custom_advance_search_fields';

		$wpdb->query( "ALTER TABLE `{$table}` 
			CHANGE site_htmlvar_name htmlvar_name varchar(255) NOT NULL, 
			CHANGE field_site_name admin_title varchar(255) NULL DEFAULT NULL, 
			CHANGE front_search_title frontend_title varchar(255) NULL DEFAULT NULL, 
			CHANGE field_site_type field_type varchar(100) NOT NULL, 
			CHANGE first_search_value range_start int(11) NOT NULL, 
			CHANGE search_min_value range_min int(11) NOT NULL, 
			CHANGE search_max_value range_max int(11) NOT NULL, 
			CHANGE expand_custom_value range_expand int(11) NOT NULL, 
			CHANGE searching_range_mode range_mode tinyint(1) NOT NULL DEFAULT '0', 
			CHANGE search_diff_value range_step int(11) NOT NULL, 
			CHANGE field_input_type input_type varchar(100) NULL DEFAULT NULL, 
			CHANGE field_data_type data_type varchar(100) NULL DEFAULT NULL, 
			CHANGE first_search_text range_from_title varchar(255) NULL DEFAULT NULL, 
			CHANGE last_search_text range_to_title varchar(255) NULL DEFAULT NULL, 
			CHANGE field_desc description text NULL DEFAULT NULL;" 
		);

		// Update fields data
		$results = $wpdb->get_results( "SELECT id, htmlvar_name, field_type, post_type FROM `{$table}`" );

		if ( ! empty( $results ) ) {
			foreach ( $results as $row ) {
				$data = array();

				$htmlvar_name = $row->htmlvar_name;

				if ( $htmlvar_name == 'dist' ) {
					$htmlvar_name = 'distance';
				} else if ( $htmlvar_name == 'geodir_contact' ) {
					$htmlvar_name = 'phone';
				} else if ( $htmlvar_name == 'is_featured' ) {
					$htmlvar_name = 'featured';
				} else if ( $htmlvar_name == 'fieldset' && $row->field_type == 'fieldset' ) {
					$htmlvar_name = 'fieldset_' . $row->id; // Fix duplicate htmlvar name
				} else if ( $htmlvar_name == 'event' ) {
					$htmlvar_name = 'event_dates';
				}

				if ( $row->field_type == 'taxonomy' ) {
					$htmlvar_name = 'post_category';
					$data['field_type'] = 'categories';
				}

				if ( strpos( $htmlvar_name, 'geodir_' ) === 0 ) {
					$htmlvar_name = strtolower( substr( $htmlvar_name, 7 ) );
				}

				if ( $htmlvar_name === $row->htmlvar_name ) {
					continue;
				}

				$data['htmlvar_name'] = $htmlvar_name;

				$wpdb->update( $table, $data, array( 'id' => $row->id ) );
			}
		}

		self::update_log( 'update_200_search_custom_fields' );
	}

	public static function update_200_search_update_version() {
		$version = defined( 'GEODIR_ADV_SEARCH_VERSION' ) && version_compare( GEODIR_ADV_SEARCH_VERSION, '2.0.0.0', '>=' ) ? GEODIR_ADV_SEARCH_VERSION : '2.0.0.0';

		delete_option( 'geodir_advance_search_version' );
		add_option( 'geodir_advance_search_version', $version );

		delete_option( 'geodir_advance_search_db_version' );
		add_option( 'geodir_advance_search_db_version', $version );
	}

	// Event Manager
	public static function update_200_event_get_options( $options = array() ) {
		$merge_options = array(
			'event_default_filter' => get_option( 'geodir_event_defalt_filter' ),
			'event_disable_recurring' => get_option( 'geodir_event_disable_recurring' ),
			'event_hide_past_dates' => get_option( 'geodir_event_hide_past_dates' ),
			'event_map_popup_count' => get_option( 'geodir_event_infowindow_dates_count' ),
			'event_map_popup_dates' => get_option( 'geodir_event_infowindow_dates_filter' ),
			'event_field_date_format' => get_option( 'geodir_event_date_format_feild' ),
			'event_display_date_format' => get_option( 'geodir_event_date_format' ),
			'event_use_custom_format' => get_option( 'geodir_event_date_use_custom' ),
			'event_custom_date_format' => get_option( 'geodir_event_date_format_custom' ),
			'event_link_any_user' => get_option( 'geodir_event_link_any' )
		);

		return array_merge( $options, $merge_options );
	}

	public static function update_200_event_create_default_options() {
		if ( ! ( defined( 'GDEVENTS_VERSION' ) && version_compare( GDEVENTS_VERSION, '2.0.0.0', '<=' ) ) ) {
			$default_options = array(
				'event_default_filter' => 'upcoming',
				'event_disable_recurring' => '0',
				'event_hide_past_dates' => '0',
				'event_map_popup_count' => '1',
				'event_map_popup_dates' => 'upcoming',
				'event_field_date_format' => 'Y-m-d',
				'event_display_date_format' => get_option( 'date_format' ),
				'event_use_custom_format' => '0',
				'event_custom_date_format' => '',
				'event_link_any_user' => '0',
				'event_linked_count' => '5',
				'event_linked_event_type' => 'upcoming',
				'event_linked_single_event' => '0',
				'event_linked_sortby' => 'latest',
				'event_linked_listing_view' => 'gridview_onehalf',
			);

			foreach ( $default_options as $key => $value ) {
				geodir_update_option( $key, $value );
			}
		}
	}

	public static function update_200_event_create_tables() {
		global $wpdb, $plugin_prefix;
		
		if ( self::is_done( 'update_200_event_create_tables' ) ) {
			return;
		}
		
		// Event schedule table
		$table = $plugin_prefix . 'event_schedule';

		$wpdb->query( "ALTER TABLE `{$table}` 
			CHANGE event_date start_date date NOT NULL DEFAULT '0000-00-00', 
			CHANGE event_enddate end_date date NOT NULL DEFAULT '0000-00-00', 
			CHANGE event_starttime start_time time NOT NULL DEFAULT '00:00:00', 
			CHANGE event_endtime end_time time NOT NULL DEFAULT '00:00:00';" 
		);

		self::update_log( 'update_200_event_create_tables' );
	}

	public static function update_200_event_update_version() {
		$version = defined( 'GEODIR_EVENT_VERSION' ) && version_compare( GEODIR_EVENT_VERSION, '2.0.0.0', '>=' ) ? GEODIR_EVENT_VERSION : '2.0.0.0';

		delete_option( 'geodir_event_version' );
		add_option( 'geodir_event_version', $version );

		delete_option( 'geodir_event_db_version' );
		add_option( 'geodir_event_db_version', $version );
	}

	// Review Rating
	public static function update_200_rr_get_options( $options = array() ) {
		$merge_options = array(
			'rr_enable_rating' => get_option( 'geodir_reviewrating_enable_rating' ),
			'rr_enable_images' => get_option( 'geodir_reviewrating_enable_images' ),
			'rr_enable_rate_comment' => get_option( 'geodir_reviewrating_enable_review' ),
			'rr_enable_sorting' => get_option( 'geodir_reviewrating_enable_sorting' ),
			'uninstall_geodir_review_rating_manager' => get_option( 'geodir_un_geodir_review_rating_manager' ),
		);

		return array_merge( $options, $merge_options );
	}

	public static function update_200_rr_create_default_options() {
		if ( ! ( defined( 'GEODIRREVIEWRATING_VERSION' ) && version_compare( GEODIRREVIEWRATING_VERSION, '2.0.0.0', '<=' ) ) ) {
			$default_options = array(
				'rr_enable_rating' => '0',
				'rr_enable_images' => '0',
				'rr_enable_rate_comment' => '0',
				'rr_enable_sorting' => '0',
			);

			foreach ( $default_options as $key => $value ) {
				geodir_update_option( $key, $value );
			}
		}
	}

	public static function update_200_rr_create_tables() {
		global $wpdb, $plugin_prefix;
		
		if ( self::is_done( 'update_200_rr_create_tables' ) ) {
			return;
		}

		$collate = '';
		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		// Tables
		$reviews_table = GEODIR_REVIEW_TABLE;
		$rating_style_table = $plugin_prefix . 'rating_style';
		$attachments_table = GEODIR_ATTACHMENT_TABLE;

		if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/image.php' );
		}

		$results = $wpdb->get_results( "SELECT post_id, comment_id, attachments FROM {$reviews_table} WHERE attachments != '' ORDER BY comment_id ASC" );
		if ( ! empty( $results ) ) {
			$last_orders = array();

			foreach ( $results as $row ) {
				$images = explode( '::', $row->attachments );
				if ( ! empty( $images ) ) {
					if ( empty( $last_orders[ $row->post_id ] ) ) {
						$last_order = (int) $wpdb->get_var( $wpdb->prepare( "SELECT MAX( menu_order ) FROM {$attachments_table} WHERE post_id = %d", array( $row->post_id ) ) );
					} else {
						$last_order = $last_orders[ $row->post_id ];
					}
					$attachments = array();

					foreach ( $images as $image_src ) {
						$image_src = trim( $image_src );

						if ( ! empty( $image_src ) ) {
							$last_order++;
							$attachment = GeoDir_Media::insert_attachment( $row->post_id, 'comment_images', $image_src, '', '', $last_order, 1, false, $row->comment_id );
							if ( ! is_wp_error( $attachment ) && ! empty( $attachment['ID'] ) ) {
								$attachments[] = $attachment['ID'];
							}
							$last_orders[ $row->post_id ] = $last_order;
						}
					}
					$comment_images = ! empty( $attachments ) ? implode( ',', $attachments ) : '';

					$wpdb->query( $wpdb->prepare( "UPDATE `{$reviews_table}` SET `attachments` = %s, total_images = %d WHERE comment_id = %d", array( $comment_images, count( $attachments ), $row->comment_id ) ) );
				}
			}
		}

		// Rating style
		$wpdb->query( "ALTER TABLE `{$rating_style_table}` 
			ADD s_rating_icon varchar(100) NOT NULL AFTER `name`, 
			ADD s_rating_type varchar(25) NOT NULL AFTER `name`, 
			ADD star_color_off text NOT NULL AFTER `star_color`;" 
		);

		$results = $wpdb->get_results( "SELECT id, s_img_off FROM {$rating_style_table} WHERE s_img_off != '' ORDER BY id ASC" );
		if ( ! empty( $results ) ) {
			foreach ( $results as $i => $row ) {
				$icon = $row->s_img_off;
				if ( strpos( $icon, 'plugins/geodir_review_rating_manager/icons/stars.png' ) !== false ) {
					$icon = GEODIRECTORY_PLUGIN_URL . '/assets/images/stars.png';
				}
				$attachment_id = self::update_200_generate_attachment_id( $icon );

				if ( ! empty( $attachment_id ) && ! is_wp_error( $attachment_id ) ) {
					$wpdb->query( $wpdb->prepare( "UPDATE `{$rating_style_table}` SET s_rating_type = 'image', `s_img_off` = %s, `star_color_off` = '#afafaf' WHERE id = %d", array( $attachment_id, $row->id ) ) );
				}
			}
		}

		self::update_log( 'update_200_rr_create_tables' );
	}

	public static function update_200_rr_update_version() {
		$version = defined( 'GEODIR_REVIEWRATING_VERSION' ) && version_compare( GEODIR_REVIEWRATING_VERSION, '2.0.0.0', '>=' ) ? GEODIR_REVIEWRATING_VERSION : '2.0.0.0';

		delete_option( 'geodir_reviewrating_version' );
		add_option( 'geodir_reviewrating_version', $version );

		delete_option( 'geodir_reviewrating_db_version' );
		add_option( 'geodir_reviewrating_db_version', $version );
	}

	// Claim Manager
	public static function update_200_claim_get_options( $options = array() ) {
		$merge_options = array(
			'claim_auto_approve' => get_option( 'geodir_reviewrating_enable_rating' ),
			'claim_show_author_link' => get_option( 'geodir_reviewrating_enable_images' ),
			'claim_force_upgrade' => get_option( 'geodir_claim_force_upgrade' ),
			'uninstall_geodir_claim_listing' => get_option( 'geodir_un_geodir_claim_listing' ),
		);

		return array_merge( $options, $merge_options );
	}

	public static function update_200_claim_create_default_options() {
		if ( ! ( defined( 'GEODIRCLAIM_VERSION' ) && version_compare( GEODIRCLAIM_VERSION, '2.0.0.0', '<=' ) ) ) {
			$default_options = array(
				'claim_auto_approve' => '0',
				'claim_show_author_link' => '0',
				'claim_force_upgrade' => '0',
			);

			foreach ( $default_options as $key => $value ) {
				geodir_update_option( $key, $value );
			}
		}
	}

	public static function update_200_claim_create_tables() {
		global $wpdb, $plugin_prefix;
		
		if ( self::is_done( 'update_200_claim_create_tables' ) ) {
			return;
		}

		$collate = '';
		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		// Tables
		$claim_table = $plugin_prefix . 'claim';
		$custom_fields_table = GEODIR_CUSTOM_FIELDS_TABLE;

		// Change
		$wpdb->query( "ALTER TABLE `{$claim_table}` CHANGE `pid` `id` int(11) unsigned NOT NULL AUTO_INCREMENT;" );
		$wpdb->query( "ALTER TABLE `{$claim_table}`  
			CHANGE `list_id` `post_id` int(11) unsigned NOT NULL DEFAULT '0', 
			CHANGE `org_authorid` `author_id` int(11) unsigned NOT NULL DEFAULT '0', 
			CHANGE `claim_date` `claim_date` datetime NOT NULL,
			ADD `meta` text NOT NULL;" 
		);

		self::update_log( 'update_200_claim_create_tables' );
	}

	public static function update_200_claim_update_version() {
		$version = defined( 'GEODIR_CLAIM_VERSION' ) && version_compare( GEODIR_CLAIM_VERSION, '2.0.0.0', '>=' ) ? GEODIR_CLAIM_VERSION : '2.0.0.0';

		delete_option( 'geodir_claim_version' );
		add_option( 'geodir_claim_version', $version );

		delete_option( 'geodir_claim_db_version' );
		add_option( 'geodir_claim_db_version', $version );
	}

	// Franchise Manager
	public static function update_200_franchise_get_options( $options = array() ) {
		$merge_options = array(
			'franchise_show_main' => ! get_option( 'geodir_franchise_hide_main_all' ),
			'franchise_show_viewing' => ! get_option( 'geodir_franchise_hide_viewing' ),
			'email_user_franchise_approved' => 1,
			'email_user_franchise_approved_subject' => get_option( 'geodir_franchise_client_email_subject_payment_franchises' ),
			'email_user_franchise_approved_body' => get_option( 'geodir_franchise_client_email_message_payment_franchises' ),
			'email_bcc_user_franchise_approved' => get_option( 'geodir_franchise_bcc_admin_payment_franchises' ),
			'uninstall_geodir_franchise' => get_option( 'uninstall_geodir_franchise' ),
		);

		return array_merge( $options, $merge_options );
	}

	public static function update_200_franchise_create_default_options() {
		if ( ! ( defined( 'GEODIR_FRANCHISE_VERSION' ) && version_compare( GEODIR_FRANCHISE_VERSION, '2.0.0.0', '<=' ) ) ) {
			$default_options = array(
				'franchise_show_main' => 1,
				'franchise_show_viewing' => 1,
				'email_user_franchise_approved' => 1,
			);

			foreach ( $default_options as $key => $value ) {
				geodir_update_option( $key, $value );
			}
		}
	}

	public static function update_200_franchise_custom_fields() {
		global $wpdb, $plugin_prefix;

		if ( self::is_done( 'update_200_franchise_custom_fields' ) ) {
			return;
		}

		// Tables
		$custom_fields_table = GEODIR_CUSTOM_FIELDS_TABLE;
		$packages_table = $plugin_prefix . 'price';

		$post_types = (array) get_option( 'geodir_franchise_posttypes' );

		if ( ! empty( $post_types ) ) {
			foreach ( $post_types as $post_type ) {
				if ( empty( $post_type ) ) {
					continue;
				}

				$packages = '';
				if ( self::needs_upgrade( 'payment_manager' ) ) {
					$results = $wpdb->get_col( $wpdb->prepare( "SELECT pid FROM {$packages_table} WHERE post_type = %s", $post_type ) );
					if ( ! empty( $results ) ) {
						$packages = implode( ',', $results );
					}
				}

				$fields = array(
					array(
						'post_type' => $post_type,
						'data_type' => 'TINYINT', 
						'field_type' => 'radio', 
						'field_type_key' => 'franchise', 
						'admin_title' => 'Has Franchise?', 
						'frontend_desc' => 'Tick "Yes" if listing has franchises.', 
						'frontend_title' => 'Has Franchise?', 
						'htmlvar_name' => 'franchise', 
						'sort_order' => '0',
						'option_values' => 'Yes/1,No/0',
						'clabels' => 'Has Franchise?',
						'is_active' => '1',
						'show_in' => '',  
						'packages' => $packages, 
						'for_admin_use' => '0',
						'field_icon' => 'fas fa-sitemap'
					),
					array(
						'post_type' => $post_type,
						'data_type' => 'TEXT', 
						'field_type' => 'multiselect', 
						'field_type_key' => 'franchise_fields', 
						'admin_title' => 'Lock franchise fields', 
						'frontend_desc' => 'Select fields to lock from franchise edit.', 
						'frontend_title' => 'Lock franchise fields', 
						'htmlvar_name' => 'franchise_fields', 
						'sort_order' => '0',
						'option_values' => '',
						'clabels' => 'Lock franchise fields',
						'is_active' => '1', 
						'show_in' => '',  
						'packages' => $packages, 
						'for_admin_use' => '0',
						'field_icon' => 'fas fa-sitemap'
					),
					array(
						'post_type' => $post_type,
						'data_type' => 'INT', 
						'field_type' => 'text', 
						'field_type_key' => 'franchise_of', 
						'admin_title' => 'Main Listing', 
						'frontend_desc' => 'Enter main listing ID.', 
						'frontend_title' => 'Main Listing', 
						'htmlvar_name' => 'franchise_of', 
						'sort_order' => '0',
						'option_values' => '',
						'clabels' => 'Main Listing',
						'is_active' => '1', 
						'show_in' => '[detail]',  
						'packages' => $packages, 
						'for_admin_use' => '1',
						'field_icon' => 'fas fa-sitemap'
					)
				);

				foreach ( $fields as $field ) {
					$wpdb->insert( $custom_fields_table, $field, array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%d', '%s', '%s', '%d', '%s' ) );
				}
			}
		}

		self::update_log( 'update_200_franchise_custom_fields' );
	}

	public static function update_200_franchise_update_version() {
		$version = defined( 'GEODIR_FRANCHISE_VERSION' ) && version_compare( GEODIR_FRANCHISE_VERSION, '2.0.0.0', '>=' ) ? GEODIR_FRANCHISE_VERSION : '2.0.0.0';

		delete_option( 'geodir_franchise_version' );
		add_option( 'geodir_franchise_version', $version );

		delete_option( 'geodir_franchise_db_version' );
		add_option( 'geodir_franchise_db_version', $version );
	}

	public static function v2_updated() {
		global $wpdb, $plugin_prefix;

		if ( ! self::is_done( 'v2_updated_pricemeta_fix_post_images' ) && get_option( 'geodir_pricing_version' ) && get_option( 'geodir_payments_db_version' ) ) {
			$package_meta_table = $plugin_prefix . 'pricemeta';

			$wpdb->query( "UPDATE `" . GEODIR_CUSTOM_FIELDS_TABLE . "` SET `sort_order` = '-3' WHERE htmlvar_name = 'package_id'" );
			$wpdb->query( "UPDATE `" . GEODIR_CUSTOM_FIELDS_TABLE . "` SET `sort_order` = '-2' WHERE htmlvar_name = 'expire_date'" );

			$results = $wpdb->get_results( "SELECT * FROM {$package_meta_table} WHERE meta_key = 'exclude_field' AND meta_value LIKE '%post_images%'" );
			if ( ! empty( $results ) ) {
				foreach ( $results as $row ) {
					$meta_value = explode( "::", $row->meta_value );
					$index = array_search( 'post_images', $meta_value );
					if ( $index !== false ) {
						unset( $meta_value[ $index ] );
					}
					$meta_value = implode( "::", $meta_value );

					$wpdb->query( $wpdb->prepare( "UPDATE `{$package_meta_table}` SET `meta_value` = %s WHERE meta_id = %d", array( $meta_value, $row->meta_id ) ) );
				}
			}

			if ( get_option( 'geodir_cp_version' ) && get_option( 'geodir_cp_db_version' ) ) {
				$wpdb->query( "UPDATE `" . GEODIR_CUSTOM_FIELDS_TABLE . "` SET `sort_order` = '-1' WHERE field_type = 'link_posts'" );
			}

			self::update_log( 'v2_updated_pricemeta_fix_post_images' );
		}

		if ( get_option( 'geodir_franchise_version' ) && get_option( 'geodir_franchise_db_version' ) ) {
			$post_types = get_option( 'geodir_franchise_posttypes' );

			if ( ! empty( $post_types ) ) {
				foreach ( $post_types as $post_type ) {
					if ( ! empty( $post_type ) && ! self::is_done( 'v2_updated_franchise_data_' . $post_type ) ) {
						$table = $plugin_prefix . $post_type . '_detail';
						$results = $wpdb->get_results( "SELECT p.ID FROM {$wpdb->posts} AS p LEFT JOIN {$wpdb->postmeta} AS pm ON pm.post_id = p.ID WHERE p.post_type = '{$post_type}' AND pm.meta_key = 'gd_is_franchise' AND pm.meta_value = '1'" );

						if ( ! empty( $results ) ) {
							foreach ( $results as $row ) {
								$data = array();
								$data['franchise'] = 1;
								$locked_fields = get_post_meta( $row->ID, 'gd_franchise_lock', true );
								if ( ! empty( $locked_fields ) ) {
									$fields = array();
									foreach ( $locked_fields as $locked_field ) {
										if ( in_array( $locked_field, array( 'geodir_contact', 'is_featured' ) ) ) {
											if ( $locked_field == 'geodir_contact' ) {
												$locked_field = 'phone';
											}
											if ( $locked_field == 'is_featured' ) {
												$locked_field = 'featured';
											}
										}
										if ( strpos( $locked_field, 'geodir_' ) === 0 ) {
											$locked_field = strtolower( substr( $locked_field, 7 ) );
										}
										if ( $locked_field == 'post' ) {
											$locked_field = 'address';
										} else if ( $locked_field == $post_type . 'category' ) {
											$locked_field = 'post_category';
										} else if ( $locked_field == 'post_desc' ) {
											$locked_field = 'post_content';
										} else if ( $locked_field == 'title' ) {
											$locked_field = 'post_title';
										}
										$fields[] = $locked_field;
									}
									if ( ! empty( $fields ) ) {
										$data['franchise_fields'] = implode( ',', $fields );
									}
								}
								$wpdb->update( $table, $data, array( 'post_id' => $row->ID ) );
							}
						}

						self::update_log( 'v2_updated_franchise_data_' . $post_type );
					}
				}
			}

			delete_option( 'geodir_franchise_posttypes' );
		}

		// Convert file fields data.
		self::convert_file_fields();
	}

	public static function convert_file_fields() {
		global $wpdb;

		$post_types = self::v2_post_types( true );

		foreach ( $post_types as $key => $post_type ) {
			if ( self::is_done( 'v2_updated_convert_file_fields_' . $post_type ) ) {
				continue;
			}
	
			$file_fields = $wpdb->get_results( $wpdb->prepare( "SELECT htmlvar_name FROM `" . GEODIR_CUSTOM_FIELDS_TABLE . "` WHERE post_type = %s AND field_type = %s AND htmlvar_name != %s ORDER BY id ASC", array( $post_type, 'file', 'post_images' ) ) );
			if ( empty( $file_fields ) ) {
				continue;
			}

			$htmlvar_names = array();
			$fields = 'pd.post_id, p.post_author, p.post_date_gmt';
			$where = array();
			foreach ( $file_fields as $field ) {
				if ( ! empty( $field->htmlvar_name ) ) {
					$htmlvar_names[] = $field->htmlvar_name;
					$fields .= ', pd.' . $field->htmlvar_name;
					$where[] = "pd.{$field->htmlvar_name} != ''";
				}
			}

			if ( empty( $htmlvar_names ) ) {
				continue;
			}

			$table = geodir_db_cpt_table( $post_type );

			$results = $wpdb->get_results( "SELECT {$fields} FROM `{$table}` AS pd LEFT JOIN `{$wpdb->posts}` AS p ON p.ID = pd.post_id WHERE " . implode( " OR ", $where ) . " ORDER BY pd.post_id ASC" );

			if ( empty( $results ) ) {
				continue;
			}

			foreach ( $results as $row ) {
				$save_fields = array();

				foreach ( $htmlvar_names as $htmlvar_name ) {
					$save_field = array();

					if ( ! empty( $row->{$htmlvar_name} ) && strpos( $row->{$htmlvar_name}, "|" ) === false && ( $items = explode( "::", trim( $row->{$htmlvar_name} ) ) ) ) {
						$order = 0;

						foreach ( $items as $item_url ) {
							$item_url = trim( $item_url );

							if ( ! empty( $item_url ) ) {
								$order++;
								$relative_url = geodir_file_relative_url( $item_url );
								$full_relative_url = geodir_file_relative_url( $item_url, true );
								$filetype = wp_check_filetype( $full_relative_url );

								$insert_data = array(
									'post_id' => $row->post_id,
									'date_gmt' => $row->post_date_gmt,
									'user_id' => $row->post_author,
									'title' => '',
									'caption' => '',
									'file' => ( $relative_url != '' && strpos( "/", $relative_url ) !== 0 ? "/" . $relative_url : $relative_url ),
									'mime_type' => ( ! empty( $filetype ) && ! empty( $filetype['type'] ) ? $filetype['type'] : '' ),
									'menu_order' => ( $order - 1 ),
									'featured' => 0,
									'is_approved' => 1,
									'metadata' => '',
									'type' => $htmlvar_name,
									'other_id' => 0
								);

								$result = $wpdb->insert(
									GEODIR_ATTACHMENT_TABLE,
									$insert_data,
									array(
										'%d',
										'%s',
										'%d',
										'%s',
										'%s',
										'%s',
										'%s',
										'%d',
										'%d',
										'%d',
										'%s',
										'%s',
										'%d'
									)
								);
								$insert_id = $result && ! empty( $wpdb->insert_id ) ? $wpdb->insert_id : 0;

								if ( $insert_id ) {
									$save_field[] = $full_relative_url . '|' . $insert_id . '||';
								}
							}
						}
					}

					if ( ! empty( $save_field ) ) {
						$save_fields[ $htmlvar_name ] = implode( ",", $save_field );
					}
				}

				if ( ! empty( $save_fields ) ) {
					$wpdb->update( $table, $save_fields, array( 'post_id' => $row->post_id ) );
				}
			}

			self::update_log( 'v2_updated_convert_file_fields_' . $post_type );
		}
	}

	public static function v1_post_types() {
		$post_types = array();

		// Post types from v1 option
		$v1_post_types = get_option( 'geodir_post_types' );
		if ( ! empty( $v1_post_types ) ) {
			foreach ( $v1_post_types as $post_type => $data ) {
				if ( ! empty( $post_type ) ) {
					$post_types[ $post_type ] = $data;
				}
			}
		}

		// Post types from v2 option
		$v2_post_types = geodir_get_option( 'post_types' );
		if ( ! empty( $v2_post_types ) ) {
			foreach ( $v2_post_types as $post_type => $data ) {
				if ( ! empty( $post_type ) && empty( $post_types[ $post_type ] ) ) {
					$post_types[ $post_type ] = $data;
				}
			}
		}

		return $post_types;
	}

	public static function v1_taxonomies() {
		$taxonomies = array();

		// Taxonomies from v1 option
		$v1_taxonomies = get_option( 'geodir_taxonomies' );
		if ( ! empty( $v1_taxonomies ) ) {
			foreach ( $v1_taxonomies as $taxonomy => $data ) {
				if ( ! empty( $taxonomy ) ) {
					$taxonomies[ $taxonomy ] = $data;
				}
			}
		}

		// Taxonomies from v2 option
		$v2_taxonomies = geodir_get_option( 'taxonomies' );
		if ( ! empty( $v2_taxonomies ) ) {
			foreach ( $v2_taxonomies as $taxonomy => $data ) {
				if ( ! empty( $taxonomy ) && empty( $taxonomies[ $taxonomy ] ) ) {
					$taxonomies[ $taxonomy ] = $data;
				}
			}
		}

		return $taxonomies;
	}

	public static function v2_post_types( $names = false ) {
		$post_types = geodir_get_option( 'post_types' );

		if ( empty( $post_types ) ) {
			return array();
		}

		if ( $names ) {
			$post_types = array_keys( $post_types );
		}

		return $post_types;
	}

	public static function is_done( $task ) {
		if ( empty( $task ) ) {
			return false;
		}

		$log = get_option( 'geodir_v2_upgrade' );

		if ( is_array( $log ) && ! empty( $log[ $task ] ) ) {
			geodir_error_log( $task, 'Skip', __FILE__, __LINE__ );
			return true;
		}

		return false;
	}

	public static function update_log( $task ) {
		if ( empty( $task ) ) {
			return false;
		}

		$log = get_option( 'geodir_v2_upgrade' );

		if ( ! is_array( $log ) ) {
			$log = array();
		}

		if ( ! empty( $log[ $task ] ) ) {
			return true;
		}

		$log[ $task ] = 1;

		update_option( 'geodir_v2_upgrade', $log );

		return true;
	}
}
