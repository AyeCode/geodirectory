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
		add_action( 'geodir_v1_to_v2_convert_options_after', array( __CLASS__, 'set_permalink_structure' ), 10, 1 );

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

		$saved_options = get_option( 'geodir_settings' );
		if ( empty( $saved_options ) ) {
			$saved_options = array();
		}

		$update_options = self::get_options();
		foreach ( $update_options as $key => $value ) {
			$saved_options[ $key ] = $value;
		}

		update_option( 'geodir_settings', $saved_options );

		do_action( 'geodir_v1_to_v2_convert_options_after' );
	}

	public static function convert_fields() {
		do_action( 'geodir_v1_to_v2_convert_fields_before' );

		self::convert_fields();

		do_action( 'geodir_v1_to_v2_convert_fields_after' );
	}

	public static function convert_terms() {
		do_action( 'geodir_v1_to_v2_convert_terms_before' );

		self::convert_term_meta();

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

				$data['default_image'] = self::generate_attachment_id( get_option( 'geodir_cpt_img_' . $post_type ) );

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
			'map_default_marker_icon' => geodir_file_relative_url( get_option( 'geodir_default_marker_icon' ) ),
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
			'listing_default_image' => self::generate_attachment_id( get_option( 'geodir_listing_no_img' ) ),
			'rating_color' => get_option( 'geodir_reviewrating_fa_full_rating_color', '#ff9900' ),
			'rating_color_off' => '#afafaf',
			'rating_type' => get_option( 'geodir_reviewrating_enable_font_awesome' ) ? 'font-awesome' : 'image',
			'rating_icon' => 'fas fa-star';
			'rating_image' => self::generate_attachment_id( get_option( 'geodir_default_rating_star_icon' ) ),
			'default_location_city' => $default_location['city'],
			'default_location_region' => $default_location['region'],
			'default_location_country' => $default_location['country'],
			'default_location_latitude' => $default_location['city_v'],
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

		return apply_filters( 'geodir_v1_to_v2_get_options', $options );
	}

	public static function generate_attachment_id( $image_url ) {
		if ( empty( $image_url ) ) {
			return '';
		}

		$upload = self::upload_image_from_url( $image_url ); // @todo move function to GeoDir_Media class & replace self to GeoDir_Media
		if ( ! empty( $upload ) && ! is_wp_error( $upload ) && ! empty( $upload['file'] ) ) {
			$attachment_id = self::set_uploaded_image_as_attachment( $upload ); // @todo move function to GeoDir_Media class & replace self to GeoDir_Media

			if ( ! empty( $attachment_id ) && ! is_wp_error( $attachment_id ) ) {
				return $attachment_id;
			}
		}

		return '';
	}

	/**
	 * Upload image from URL.
	 *
	 * @since 2.0.0
	 * @param string $image_url
	 * @return array|WP_Error Attachment data or error message.
	 */
	public static function upload_image_from_url( $image_url ) {
		$file_name  = basename( current( explode( '?', $image_url ) ) );
		$parsed_url = @parse_url( $image_url );

		// Check parsed URL.
		if ( ! $parsed_url || ! is_array( $parsed_url ) ) {
			return new WP_Error( 'geodir_invalid_image_url', sprintf( __( 'Invalid URL %s.', 'geodirectory' ), $image_url ), array( 'status' => 400 ) );
		}

		// Ensure url is valid.
		$image_url = esc_url_raw( $image_url );

		// Get the file.
		$response = wp_safe_remote_get( $image_url, array(
			'timeout' => 10,
		) );

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'geodir_invalid_remote_image_url', sprintf( __( 'Error getting remote image %s.', 'geodirectory' ), $image_url ) . ' ' . sprintf( __( 'Error: %s.', 'geodirectory' ), $response->get_error_message() ), array( 'status' => 400 ) );
		} elseif ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return new WP_Error( 'geodir_invalid_remote_image_url', sprintf( __( 'Error getting remote image %s.', 'geodirectory' ), $image_url ), array( 'status' => 400 ) );
		}

		// Ensure we have a file name and type.
		$wp_filetype = wp_check_filetype( $file_name );

		if ( ! $wp_filetype['type'] ) {
			$headers = wp_remote_retrieve_headers( $response );
			if ( isset( $headers['content-disposition'] ) && strstr( $headers['content-disposition'], 'filename=' ) ) {
				$disposition = end( explode( 'filename=', $headers['content-disposition'] ) );
				$disposition = sanitize_file_name( $disposition );
				$file_name   = $disposition;
			} elseif ( isset( $headers['content-type'] ) && strstr( $headers['content-type'], 'image/' ) ) {
				$file_name = 'image.' . str_replace( 'image/', '', $headers['content-type'] );
			}
			unset( $headers );

			// Recheck filetype
			$wp_filetype = wp_check_filetype( $file_name );

			if ( ! $wp_filetype['type'] ) {
				return new WP_Error( 'geodir_invalid_image_type', __( 'Invalid image type.', 'geodirectory' ), array( 'status' => 400 ) );
			}
		}

		// Upload the file.
		$upload = wp_upload_bits( $file_name, '', wp_remote_retrieve_body( $response ) );

		if ( $upload['error'] ) {
			return new WP_Error( 'geodir_image_upload_error', $upload['error'], array( 'status' => 400 ) );
		}

		// Get filesize.
		$filesize = filesize( $upload['file'] );

		if ( 0 == $filesize ) {
			@unlink( $upload['file'] );
			unset( $upload );

			return new WP_Error( 'geodir_image_upload_file_error', __( 'Zero size file downloaded.', 'geodirectory' ), array( 'status' => 400 ) );
		}

		do_action( 'geodir_uploaded_image_from_url', $upload, $image_url );

		return $upload;
	}

	/**
	 * Set uploaded image as attachment.
	 *
	 * @since 2.0.0
	 * @param array $upload Upload information from wp_upload_bits.
	 * @param int $id Post ID. Default to 0.
	 * @return int Attachment ID
	 */
	public static function set_uploaded_image_as_attachment( $upload, $id = 0 ) {
		if ( empty( $upload['file'] ) ) {
			return false;
		}

		$info    = wp_check_filetype( $upload['file'] );
		if ( empty( $info['type'] ) ) {
			return false;
		}

		$title   = '';
		$content = '';

		if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/image.php' );
		}

		if ( $image_meta = wp_read_image_metadata( $upload['file'] ) ) {
			if ( trim( $image_meta['title'] ) && ! is_numeric( sanitize_title( $image_meta['title'] ) ) ) {
				$title = geodir_clean( $image_meta['title'] );
			}
			if ( trim( $image_meta['caption'] ) ) {
				$content = geodir_clean( $image_meta['caption'] );
			}
		}

		$attachment = array(
			'post_mime_type' => $info['type'],
			'guid'           => $upload['url'],
			'post_parent'    => $id,
			'post_title'     => $title ? $title : basename( $upload['file'] ),
			'post_content'   => $content,
		);

		$attachment_id = wp_insert_attachment( $attachment, $upload['file'], $id );
		if ( ! is_wp_error( $attachment_id ) ) {
			wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $upload['file'] ) );
		}

		return $attachment_id;
	}

	public static function set_permalink_structure() {
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

	public static function convert_term_meta() {
	   // Migrate tax meta.
		$term_meta_options = self::get_old_term_meta_options();

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

	public static function get_old_term_meta_options() {
		global $wpdb;

		$results = $wpdb->get_results( "SELECT option_name, option_value FROM " . $wpdb->options . " WHERE option_name LIKE 'tax_meta_%'" );

		return $results;
	}

	public static function convert_fields() {
		global $wpdb;

		// Custom fields
		$custom_fields_table = GEODIR_CUSTOM_FIELDS_TABLE;

		$wpdb->query( "ALTER TABLE `{$custom_fields_table}` CHANGE admin_desc frontend_desc text NULL DEFAULT NULL;" );
		$wpdb->query( "ALTER TABLE `{$custom_fields_table}` CHANGE site_title frontend_title varchar(255) NULL DEFAULT NULL;" );
		$wpdb->query( "ALTER TABLE `{$custom_fields_table}` CHANGE `is_active` `is_active` TINYINT(1) NOT NULL DEFAULT '1';" );
		$wpdb->query( "ALTER TABLE `{$custom_fields_table}` CHANGE `is_default` `is_default` TINYINT(1) NOT NULL DEFAULT '0';" );
		$wpdb->query( "ALTER TABLE `{$custom_fields_table}` CHANGE `is_required` `is_required` TINYINT(1) NOT NULL DEFAULT '0';" );
		$wpdb->query( "ALTER TABLE `{$custom_fields_table}` CHANGE `for_admin_use` `for_admin_use` TINYINT(1) NOT NULL DEFAULT '0';" );
		$wpdb->query( "ALTER TABLE `{$custom_fields_table}` ADD `placeholder_value` text NULL DEFAULT NULL AFTER `default_value`;" );
		$wpdb->query( "ALTER TABLE `{$custom_fields_table}` ADD `tab_level` int(11) NOT NULL AFTER `sort_order`;" );
		$wpdb->query( "ALTER TABLE `{$custom_fields_table}` ADD `tab_parent` varchar(100) NOT NULL AFTER `sort_order`;" );

		$results = $wpdb->get_results( "SELECT * FROM `{$custom_fields_table}`" );
		foreach ( $results as $row ) {
			if ( ! empty( $row->field_icon ) && ( strpos( $row->field_icon, 'fa ' ) === 0 || strpos( $row->field_icon, 'fa-' ) === 0 ) ) {
				$field_icon = $row->field_icon;
				$field_icon = str_replace( 'fa ', 'fas ', $field_icon );
				$field_icon = str_replace( 'fa-usd', 'fa-dollar-sign', $field_icon );
				$field_icon = str_replace( 'fa-money', 'fa-money-bill-alt', $field_icon );
				$row->field_icon = $field_icon;
			}
			$wpdb->update( $custom_fields_table, (array) $row, array( 'id' => $row->id ) );
		}

		// Sorting fields
		$custom_sort_fields_table = GEODIR_CUSTOM_SORT_FIELDS_TABLE;
		$results = $wpdb->get_results( "SELECT * FROM `{$custom_sort_fields_table}`" );

		$wpdb->query( "ALTER TABLE `{$custom_sort_fields_table}` CHANGE site_title frontend_title varchar(255) NULL DEFAULT NULL;" );
		$wpdb->query( "ALTER TABLE `{$custom_sort_fields_table}` ADD `tab_level` int(11) NOT NULL AFTER `sort_order`;" );
		$wpdb->query( "ALTER TABLE `{$custom_sort_fields_table}` ADD `tab_parent` varchar(100) NOT NULL AFTER `sort_order`;" );
		$wpdb->query( "ALTER TABLE `{$custom_sort_fields_table}` ADD sort varchar(5) DEFAULT 'asc' AFTER `sort_order`;" );
	}
}
