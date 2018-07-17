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
	}

	public static function update_200_settings() {
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

		do_action( 'geodir_update_200_settings_after' );
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

		GeoDir_Post_types::register_post_status();

		GeoDir_Admin_Install::create_uncategorized_categories();
		
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
		do_action( 'geodir_flush_rewrite_rules' );

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
	}

	public static function update_200_get_options() {
		$v1_post_types = get_option( 'geodir_post_types' );
		$v2_post_types = array();
		if ( ! empty( $old_post_types ) ) {
			foreach( $old_post_types as $post_type => $data ) {
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

				$data['disable_reviews'] = ! in_array( $post_type, (array) get_option( 'geodir_disable_rating_cpt' ) );
				$data['disable_favorites'] = 0;
				$data['disable_frontend_add'] = ! in_array( $post_type, (array) get_option( 'geodir_allow_posttype_frontend' ) );

				$data['seo'] = array(
					'title' => ( isset( $data['seo']['title'] ) ? $data['seo']['title'] : $data['labels']['name'] ),
                    'meta_title' => ( isset( $data['seo']['meta_title'] ) ? $data['seo']['meta_title'] : '' ),
                    'meta_description' => ( isset( $data['seo']['meta_description'] ) ? $data['seo']['meta_description'] : '' ),
				);

				$v2_post_types[ $post_type ] = $data;
			}
		}

		$default_location = wp_parse_args( (array) get_option( 'geodir_default_location' ), array(
			'country' => '',
			'region' => '',
			'city' => '',
			'city_latitude' => '',
			'city_longitude' => '',
		) );

		$default_marker_icon = get_option( 'geodir_default_marker_icon' );
		$default_marker_icon = str_replace( 'geodirectory-functions/map-functions/icons', 'assets/images', $default_marker_icon );
		
		// Core options
		$options = array(
			'taxonomies' => get_option( 'geodir_taxonomies' ),
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
			'map_default_marker_icon' => self::update_200_generate_attachment_id( $default_marker_icon ),
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
			'admin_blocked_roles' => array( 'subscriber' ),
			'listing_default_image' => self::update_200_generate_attachment_id( get_option( 'geodir_listing_no_img' ) ),
			'rating_color' => get_option( 'geodir_reviewrating_fa_full_rating_color', '#ff9900' ),
			'rating_color_off' => '#afafaf',
			'rating_type' => get_option( 'geodir_reviewrating_enable_font_awesome' ) ? 'font-awesome' : 'image',
			'rating_icon' => 'fas fa-star',
			'rating_image' => self::update_200_generate_attachment_id( get_option( 'geodir_default_rating_star_icon' ) ),
			'default_location_city' => $default_location['city'],
			'default_location_region' => $default_location['region'],
			'default_location_country' => $default_location['country'],
			'default_location_latitude' => $default_location['city_latitude'],
			'default_location_longitude' => $default_location['city_longitude'],
			'default_location_timezone' => '',

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

		return apply_filters( 'geodir_update_200_get_options', $options );
	}

	public static function update_200_generate_attachment_id( $image_url ) {
		if ( empty( $image_url ) ) {
			return '';
		}

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

		geodir_set_permalink_structure( $permalink_structure );
	}

	public static function update_200_term_metas() {
	    global $wpdb;

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
							if ( empty( $meta_value['src'] ) ) {
								continue;
							}
							
							$meta_value['src'] = geodir_file_relative_url( $meta_value['src'] );
						}

						update_term_meta( $term_id, $meta_key, $meta_value );
					}
				}
			}
		}
	}

	public static function update_200_custom_fields() {
		global $wpdb;

		// Custom fields
		$custom_fields_table = GEODIR_CUSTOM_FIELDS_TABLE;

		$wpdb->query( "ALTER TABLE `{$custom_fields_table}` CHANGE admin_desc frontend_desc text NULL DEFAULT NULL;" );
		$wpdb->query( "ALTER TABLE `{$custom_fields_table}` CHANGE site_title frontend_title varchar(255) NULL DEFAULT NULL;" );
		$wpdb->query( "ALTER TABLE `{$custom_fields_table}` ADD `placeholder_value` text NULL DEFAULT NULL AFTER `default_value`;" );
		$wpdb->query( "ALTER TABLE `{$custom_fields_table}` ADD `tab_level` int(11) NOT NULL AFTER `sort_order`;" );
		$wpdb->query( "ALTER TABLE `{$custom_fields_table}` ADD `tab_parent` varchar(100) NOT NULL AFTER `sort_order`;" );

		$results = $wpdb->get_results( "SELECT * FROM `{$custom_fields_table}`" );

		$wpdb->query( "ALTER TABLE `{$custom_fields_table}` CHANGE `is_active` `is_active` TINYINT(1) NOT NULL DEFAULT '1';" );
		$wpdb->query( "ALTER TABLE `{$custom_fields_table}` CHANGE `is_default` `is_default` TINYINT(1) NOT NULL DEFAULT '0';" );
		$wpdb->query( "ALTER TABLE `{$custom_fields_table}` CHANGE `is_required` `is_required` TINYINT(1) NOT NULL DEFAULT '0';" );
		$wpdb->query( "ALTER TABLE `{$custom_fields_table}` CHANGE `for_admin_use` `for_admin_use` TINYINT(1) NOT NULL DEFAULT '0';" );

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
					$cat_display_type = 'select';
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

			$wpdb->update( $custom_fields_table, (array) $row, array( 'id' => $row->id ) );
		}

		// Sorting fields
		$custom_sort_fields_table = GEODIR_CUSTOM_SORT_FIELDS_TABLE;

		$results = $wpdb->get_results( "SELECT * FROM `{$custom_sort_fields_table}`" );

		$wpdb->query( "ALTER TABLE `{$custom_sort_fields_table}` CHANGE site_title frontend_title varchar(255) NULL DEFAULT NULL;" );
		$wpdb->query( "ALTER TABLE `{$custom_sort_fields_table}` ADD `tab_level` int(11) NOT NULL AFTER `sort_order`;" );
		$wpdb->query( "ALTER TABLE `{$custom_sort_fields_table}` ADD `tab_parent` varchar(100) NOT NULL AFTER `sort_order`;" );
		$wpdb->query( "ALTER TABLE `{$custom_sort_fields_table}` ADD sort varchar(5) DEFAULT 'asc';" );

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
	}

	public static function update_200_post_fields() {
		global $wpdb;

		$post_types = geodir_get_posttypes(); 

		if ( ! empty( $post_types ) ) {
			foreach ( $post_types as $key => $post_type ) {
				$table = $wpdb->prefix . 'geodir_' . $post_type . '_detail';
				
				$columns = @$wpdb->get_results("DESC {$table}");

				if ( empty( $columns ) ) {
					continue;
				}

				$fields = array();
				foreach ( $columns as $key => $column ) {
					$fields[ $column->Field ] = (array) $column;
				}
				$columns = array_keys( $fields );

				$wpdb->query( "ALTER TABLE `{$table}` CHANGE {$post_type}category post_category varchar(254) DEFAULT NULL;" );
				if ( in_array( 'post_location_id', $columns ) ) {
					$wpdb->query( "ALTER TABLE `{$table}` CHANGE post_location_id location_id int(11) NOT NULL;" );
				}
				if ( in_array( 'is_featured', $columns ) ) {
					$wpdb->query( "ALTER TABLE `{$table}` CHANGE is_featured featured tinyint(1) NOT NULL DEFAULT '0';" );
					// changing data type ENUM to TINYINT sets '0' to '2'
					$wpdb->query( "UPDATE `{$table}` SET `featured` = '0' WHERE featured = '2';" );
				}
				$wpdb->query( "ALTER TABLE `{$table}` CHANGE submit_ip `submit_ip` varchar(100) DEFAULT NULL;" );
				$wpdb->query( "ALTER TABLE `{$table}` CHANGE post_locations `locations` varchar(254) DEFAULT NULL;" );
				$wpdb->query( "ALTER TABLE `{$table}` CHANGE post_address `street` varchar(254) DEFAULT NULL;" );
				$wpdb->query( "ALTER TABLE `{$table}` CHANGE post_city `city` varchar(50) DEFAULT NULL;" );
				$wpdb->query( "ALTER TABLE `{$table}` CHANGE post_region `region` varchar(50) DEFAULT NULL;" );
				$wpdb->query( "ALTER TABLE `{$table}` CHANGE post_country `country` varchar(50) DEFAULT NULL;" );
				$wpdb->query( "ALTER TABLE `{$table}` CHANGE post_zip `zip` varchar(20) NULL;" );
				$wpdb->query( "ALTER TABLE `{$table}` CHANGE post_latitude `latitude` varchar(22)  DEFAULT NULL;" );
				$wpdb->query( "ALTER TABLE `{$table}` CHANGE post_longitude `longitude` varchar(22) DEFAULT NULL;" );
				$wpdb->query( "ALTER TABLE `{$table}` CHANGE post_mapview `mapview` varchar(15) DEFAULT NULL;" );
				$wpdb->query( "ALTER TABLE `{$table}` CHANGE post_mapzoom `mapzoom` varchar(3) DEFAULT NULL;" );
				$wpdb->query( "ALTER TABLE `{$table}` CHANGE geodir_contact `phone` varchar(254) DEFAULT NULL;" );
				$wpdb->query( "ALTER TABLE `{$table}` CHANGE geodir_email `email` varchar(254) DEFAULT NULL;" );
				$wpdb->query( "ALTER TABLE `{$table}` CHANGE geodir_website `website` text;" );
				$wpdb->query( "ALTER TABLE `{$table}` CHANGE geodir_twitter `twitter` text;" );
				$wpdb->query( "ALTER TABLE `{$table}` CHANGE geodir_facebook `facebook` text;" );
				$wpdb->query( "ALTER TABLE `{$table}` CHANGE geodir_video `video` text;" );
				$wpdb->query( "ALTER TABLE `{$table}` CHANGE geodir_special_offers `special_offers` text;" );

				$wpdb->query( "ALTER TABLE {$table} DROP INDEX post_locations, ADD INDEX locations(locations(191))" );
				$wpdb->query( "ALTER TABLE {$table} ADD INDEX country(country)" );
				$wpdb->query( "ALTER TABLE {$table} ADD INDEX region(region)" );
				$wpdb->query( "ALTER TABLE {$table} ADD INDEX city(city)" );
				$wpdb->query( "ALTER TABLE {$table} DROP INDEX is_featured" );

				foreach ( $columns as $key => $column ) {
					if ( strpos( $column, 'geodir_' ) === 0 && ! in_array( $column, array( 'geodir_contact', 'geodir_email', 'geodir_website', 'geodir_twitter', 'geodir_facebook', 'geodir_video', 'geodir_special_offers' ) ) ) {
						$new_column = strtolower( substr( $fields[ $column ]['Field'], 7 ) );
						$data_type = $fields[ $column ]['Type'];
						$null = strtolower( $fields[ $column ]['Null'] ) == 'no' ? ' NOT NULL' : '';
						$default = $fields[ $column ]['Default'] !== '' && $fields[ $column ]['Default'] !== NULL ? " DEFAULT " . $fields[ $column ]['Default'] : ( strtolower( $fields[ $column ]['Null'] ) == 'yes' ? ' DEFAULT NULL' : '' );

						$wpdb->query( "ALTER TABLE `{$table}` CHANGE {$column} `{$new_column}` {$data_type}{$null}{$default};" );
					}
				}
			}
		}
	}

	public static function update_200_reviews() {
		global $wpdb;
		
		$reviews_table = GEODIR_REVIEW_TABLE;
		
		$wpdb->query( "ALTER TABLE `{$reviews_table}` DROP `id`;" );
		$wpdb->query( "ALTER TABLE `{$reviews_table}` CHANGE post_id post_id bigint(20) DEFAULT '0';" );
		$wpdb->query( "ALTER TABLE `{$reviews_table}` DROP `post_title`;" );
		$wpdb->query( "ALTER TABLE `{$reviews_table}` CHANGE post_type post_type varchar(20) DEFAULT '';" );
		$wpdb->query( "ALTER TABLE `{$reviews_table}` CHANGE user_id user_id bigint(20) DEFAULT '0';" );
		$wpdb->query( "ALTER TABLE `{$reviews_table}` CHANGE comment_id comment_id bigint(20) DEFAULT NULL, ADD UNIQUE (`comment_id`);" );
		$wpdb->query( "ALTER TABLE `{$reviews_table}` DROP `rating_ip`;" );
		$wpdb->query( "ALTER TABLE `{$reviews_table}` CHANGE overall_rating rating float DEFAULT '0';" );
		$wpdb->query( "ALTER TABLE `{$reviews_table}` CHANGE comment_images attachments text DEFAULT '';" );
		$wpdb->query( "ALTER TABLE `{$reviews_table}` DROP `wasthis_review`;" );
		$wpdb->query( "ALTER TABLE `{$reviews_table}` DROP `status`;" );
		$wpdb->query( "ALTER TABLE `{$reviews_table}` DROP `post_status`;" );
		$wpdb->query( "ALTER TABLE `{$reviews_table}` DROP `post_date`;" );
		$wpdb->query( "ALTER TABLE `{$reviews_table}` CHANGE post_city city varchar(50) DEFAULT '';" );
		$wpdb->query( "ALTER TABLE `{$reviews_table}` CHANGE post_region region varchar(50) DEFAULT '';" );
		$wpdb->query( "ALTER TABLE `{$reviews_table}` CHANGE post_country country varchar(50) DEFAULT '';" );
		$wpdb->query( "ALTER TABLE `{$reviews_table}` CHANGE post_latitude latitude varchar(22) DEFAULT '';" );
		$wpdb->query( "ALTER TABLE `{$reviews_table}` CHANGE post_longitude longitude varchar(22) DEFAULT '';" );
		$wpdb->query( "ALTER TABLE `{$reviews_table}` DROP `comment_content`;" );
	}

	public static function update_200_attachments() {
		global $wpdb;
		
		$attachments_table = GEODIR_ATTACHMENT_TABLE;

		$wpdb->query( "ALTER TABLE `{$attachments_table}` DROP `content`;" );
		$wpdb->query( "ALTER TABLE `{$attachments_table}` CHANGE `is_featured` `featured` TINYINT(1) NULL DEFAULT '0';" );
		$wpdb->query( "ALTER TABLE `{$attachments_table}` CHANGE `is_approved` `is_approved` TINYINT(1) NULL DEFAULT '1';" );
		$wpdb->query( "ALTER TABLE `{$attachments_table}` ADD `date_gmt` datetime NULL default NULL AFTER `post_id`;" );
		$wpdb->query( "ALTER TABLE `{$attachments_table}` ADD `type` varchar(254) NULL DEFAULT 'post_images';" );

		// changing data type ENUM to TINYINT sets '0' to '2'
		$wpdb->query( "UPDATE `{$attachments_table}` SET `featured` = '0' WHERE featured = '2';" );
		$wpdb->query( "UPDATE `{$attachments_table}` SET `is_approved` = '0' WHERE is_approved = '2';" );
	}

	private static function create_tables() {
		global $wpdb;

		$wpdb->hide_errors();

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		dbDelta( GeoDir_Admin_Install::get_schema() );

	}

	private static function insert_default_fields() {
		add_filter( 'geodir_before_default_custom_fields_saved', array( __CLASS__, 'filter_custom_fields_saved' ), 100, 1 );

		GeoDir_Admin_Install::insert_default_fields();

		remove_filter( 'geodir_before_default_custom_fields_saved', array( __CLASS__, 'filter_custom_fields_saved' ), 100, 1 );

		// update custom fields sort order
		self::update_200_fields_sort_order();
	}

	private static function insert_default_tabs() {
		global $wpdb;

		GeoDir_Admin_Install::insert_default_tabs();

		// merge tabs from custom fields
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

		// update detail tabs sort order
		self::update_200_post_tabs_sort_order();
	}

	private static function create_pages() {
		global $wpdb;

		$page_location = geodir_get_option( 'page_location' );
		if ( $page_location && ( $row = $wpdb->get_row( $wpdb->prepare( "SELECT ID, post_content FROM {$wpdb->posts} WHERE ID = %d", array( (int)$page_location ) ) ) ) ) {
			$post_content = $row->post_content . ' ' . GeoDir_Defaults::page_location_content();
			$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->posts} SET post_content = %s WHERE ID = %d", array( trim( $post_content ), (int)$page_location ) ) );
		}
		$page_add = geodir_get_option( 'page_add' );
		if ( $page_add && ( $row = $wpdb->get_row( $wpdb->prepare( "SELECT ID, post_content FROM {$wpdb->posts} WHERE ID = %d", array( (int)$page_add ) ) ) ) ) {
			$post_content = $row->post_content . ' ' . GeoDir_Defaults::page_add_content();
			$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->posts} SET post_content = %s WHERE ID = %d", array( trim( $post_content ), (int)$page_add ) ) );
		}

		GeoDir_Admin_Install::create_pages();
	}

	/**
	 * Create cron jobs (clear them first).
     *
     * @since 2.0.0
	 */
	private static function create_cron_jobs() {
		//@todo add crons here
		wp_clear_scheduled_hook( 'geodirectory_tracker_send_event' );
		wp_schedule_event( time(), apply_filters( 'geodirectory_tracker_event_recurrence', 'daily' ), 'geodirectory_tracker_send_event' );
	}

	/**
	 * See if we need the wizard or not.
	 *
	 * @since 2.0.0
	 */
	private static function maybe_enable_setup_wizard() {
		GeoDir_Admin_Notices::add_notice( 'install' );
		set_transient( '_gd_activation_redirect', 1, 30 );
	}

	/**
	 * Update GeoDirectory version to current.
     *
     * @since 2.0.0
	 */
	private static function update_gd_version() {
		delete_option( 'geodirectory_version' );
		add_option( 'geodirectory_version', GEODIRECTORY_VERSION );
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

	public static function update_200_fields_sort_order() {
		global $wpdb;

		$post_types = (array) geodir_get_option( 'post_types' );

		if ( ! empty( $post_types ) ) {
			foreach ( $post_types as $post_type => $data ) {
				if ( empty( $post_type ) ) {
					continue;
				}

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
	}

	public static function update_200_sort_fields_sort_order() {
		global $wpdb;

		$post_types = (array) geodir_get_option( 'post_types' );

		if ( ! empty( $post_types ) ) {
			foreach ( $post_types as $post_type => $data ) {
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

		$post_types = (array) geodir_get_option( 'post_types' );

		if ( ! empty( $post_types ) ) {
			foreach ( $post_types as $post_type => $data ) {
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
}
