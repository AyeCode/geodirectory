<?php
/**
 * Core functions.
 *
 * @since 2.0.0
 * @package GeoDirectory
 */

/**
 * A function to log GD errors no matter the type given.
 *
 * This function will log GD errors if the WP_DEBUG constant is true, it can be filtered.
 *
 * @since 1.5.7
 *
 * @param mixed $log The thing that should be logged.
 *
 * @package GeoDirectory
 */
function geodir_error_log( $log, $title = '', $file = '', $line = '', $exit = false ) {
	/**
	 * A filter to override the WP_DEBUG setting for function geodir_error_log().
	 *
	 * @since 1.5.7
	 */
	$should_log = apply_filters( 'geodir_log_errors', WP_DEBUG );

	if ( $should_log ) {
		$label = '';
		if ( $file && $file !== '' ) {
			$label .= basename( $file ) . ( $line ? '(' . $line . ')' : '' );
		}

		if ( $title && $title !== '' ) {
			$label = $label !== '' ? $label . ' ' : '';
			$label .= $title . ' ';
		}

		$label = $label !== '' ? trim( $label ) . ' : ' : '';

		if ( is_array( $log ) || is_object( $log ) ) {
			error_log( $label . print_r( $log, true ) );
		} else {
			error_log( $label . $log );
		}

		if ( $exit ) {
			exit;
		}
	}
}

/**
 * doing_it_wrong function.
 *
 * A function is called when mark something as being incorrectly called.
 *
 * @since 2.0.0
 *
 * @param $function The function that was called.
 * @param $message A message explaining what has been done incorrectly.
 * @param $version The version of WordPress where the message was added.
 */
function geodir_doing_it_wrong( $function, $message, $version ) {
	$message .= ' Backtrace: ' . wp_debug_backtrace_summary();

	if ( defined( 'DOING_AJAX' ) ) {
		do_action( 'doing_it_wrong_run', $function, $message, $version );
		geodir_error_log( $function . ' was called incorrectly. ' . $message . '. This message was added in version ' . $version . '.' );
	} else {
		_doing_it_wrong( $function, $message, $version );
	}
}

/**
 * is_singular function.
 *
 * The function for existing single post of any post type.
 *
 * @since 2.0.0
 *
 * @param array $post_types Optional. Array of post types. Default array().
 *
 * @return bool Whether the query is for an existing single post of any of the given post types.
 */
function geodir_is_singular( $post_types = array() ) {
	if ( empty( $post_types ) ) {
		$post_types = geodir_get_posttypes();
	}

	return is_singular( $post_types ) || geodir_is_page( 'preview' );
}

/**
 * is_taxonomy function.
 *
 * The function for an existing custom taxonomy archive page?
 *
 * If the $taxonomies parameter is specified, this function will additionally
 * check if the query is for that specific $taxonomies.
 *
 * @since 2.0.0
 *
 * @param array $taxonomies Optional. Taxonomies slugs. Default array().
 *
 * @return bool True for custom taxonomy archive pages, false for built-in taxonomies.
 */
function geodir_is_taxonomy( $taxonomies = array() ) {
	if ( empty( $taxonomies ) ) {
		$taxonomies = geodir_get_taxonomies( '', true );
	}

	return is_tax( $taxonomies );
}

/**
 * In this function existing post type archive page?
 *
 * @since 2.0.0
 *
 * @param array $post_types Optional. Array of post types. Default array().
 *
 * @return bool
 */
function geodir_is_post_type_archive( $post_types = array() ) {
	if ( empty( $post_types ) ) {
		$post_types = geodir_get_posttypes();
	}

	return is_post_type_archive( $post_types );
}


/**
 * Display a GeoDirectory help tip.
 *
 * @since  2.0.0
 *
 * @param  string $tip Help tip text
 * @param  bool $allow_html Allow sanitized HTML if true or escape
 *
 * @return string
 */
function geodir_help_tip( $tip, $allow_html = false ) {
	if ( $allow_html ) {
		$tip = geodir_sanitize_tooltip( $tip );
	} else {
		$tip = esc_attr( $tip );
	}

	return '<span class="gd-help-tip dashicons dashicons-editor-help" title="' . $tip . '"></span>';
}

/**
 * Get permalink settings for GeoDirectory
 *
 * @since  2.0.0
 * @return string
 */
function geodir_get_permalink_structure() {
	return geodir_get_option( 'permalink_structure', '' );
}

/**
 * Set permalink structure for GeoDirectory
 *
 * @since  2.0.0
 * @return string
 */
function geodir_set_permalink_structure( $permalink_structure = '' ) {
	$old_permalink_structure = geodir_get_option( 'permalink_structure' );

	if ( $permalink_structure != $old_permalink_structure ) {
		geodir_update_option( 'permalink_structure', $permalink_structure );

		/**
		 * Fires after the GeoDirectory permalink structure is updated.
		 *
		 * @since 2.0.0
		 *
		 * @param string $old_permalink_structure The previous permalink structure.
		 * @param string $permalink_structure The new permalink structure.
		 */
		do_action( 'geodir_permalink_structure_changed', $old_permalink_structure, $permalink_structure );
	}
}

/**
 * Get the search form default text.
 *
 * @since 2.0.0
 * @return string|void
 */
function geodir_get_search_default_text() {
	return __( 'Search for', 'geodirectory' );
}

/**
 * Get the search near form default text.
 *
 * @since 2.0.0
 * @return string|void
 */
function geodir_get_search_default_near_text() {
	return __( 'Near', 'geodirectory' );
}

/**
 * Get the search form default text.
 *
 * @since 2.0.0
 * @return string|void
 */
function geodir_get_search_default_button_text() {
	return __( 'fas fa-search', 'geodirectory' );
}


/**
 * Outputs translated JS text strings.
 *
 * This function outputs text strings used in JS files as a json array of strings so they can be translated and still be used in JS files.
 *
 * @since 1.0.0
 * @package GeoDirectory
 */
function geodir_params() {// check_ajax_referer function is used to make sure no files are uploaded remotely but it will fail if used between https and non https so we do the check below of the urls
	if ( str_replace( "https", "http", admin_url( 'admin-ajax.php' ) ) && ! empty( $_SERVER['HTTPS'] ) ) {
		$ajax_url = admin_url( 'admin-ajax.php' );
	} elseif ( ! str_replace( "https", "http", admin_url( 'admin-ajax.php' ) ) && empty( $_SERVER['HTTPS'] ) ) {
		$ajax_url = admin_url( 'admin-ajax.php' );
	} elseif ( str_replace( "https", "http", admin_url( 'admin-ajax.php' ) ) && empty( $_SERVER['HTTPS'] ) ) {
		$ajax_url = str_replace( "https", "http", admin_url( 'admin-ajax.php' ) );
	} elseif ( ! str_replace( "https", "http", admin_url( 'admin-ajax.php' ) ) && ! empty( $_SERVER['HTTPS'] ) ) {
		$ajax_url = str_replace( "http", "https", admin_url( 'admin-ajax.php' ) );
	}

	/**
	 * Filter the allowed image type extensions for post images.
	 *
	 * @since 1.4.7.1
	 *
	 * @param string $allowed_img_types The image type extensions array.
	 */
	$allowed_img_types = apply_filters( 'geodir_allowed_post_image_exts', array( 'jpg', 'jpeg', 'jpe', 'gif', 'png', 'webp' ) );

	$default_marker_icon   = GeoDir_Maps::default_marker_icon( true );
	$default_marker_size   = GeoDir_Maps::get_marker_size( $default_marker_icon, array( 'w' => 20, 'h' => 34 ) );
	$default_marker_width  = $default_marker_size['w'];
	$default_marker_height = $default_marker_size['h'];

	$arr_alert_msg = array(
		'plugin_url'                                   => geodir_plugin_url(),
		'ajax_url'                                     => $ajax_url,
		'api_url'                                      => geodir_rest_url(),
		'location_base_url'                            => trailingslashit( geodir_get_location_link('base') ),
		'location_url'                                 => geodir_get_location_link('current'),
		'search_base_url'                              => get_permalink(geodir_get_page_id('search')),
		'custom_field_not_blank_var'                   => __( 'Field label must not be blank', 'geodirectory' ),
		'custom_field_not_special_char'                => __( 'Please do not use special character and spaces in field key Variable Name.', 'geodirectory' ),
		'custom_field_unique_name'                     => __( 'Field key must be unique.', 'geodirectory' ),
		'custom_field_delete'                          => __( 'Are you sure you wish to delete this field?', 'geodirectory' ),
		'custom_field_delete_children'                 => __( 'You must move or remove child elements first.', 'geodirectory' ),
		//start not show alert msg
		'tax_meta_class_succ_del_msg'                  => __( 'File has been successfully deleted.', 'geodirectory' ),
		'tax_meta_class_not_permission_to_del_msg'     => __( 'You do NOT have permission to delete this file.', 'geodirectory' ),
		'tax_meta_class_order_save_msg'                => __( 'Order saved!', 'geodirectory' ),
		'tax_meta_class_not_permission_record_img_msg' => __( 'You do not have permission to reorder images.', 'geodirectory' ),
		'address_not_found_on_map_msg'                 => __( 'Address not found for:', 'geodirectory' ),
		// end not show alert msg
		'my_place_listing_del'                         => __( 'Are you sure you wish to delete this listing?', 'geodirectory' ),
		'my_main_listing_del'                          => __( 'Deleting the main listing of a franchise will turn all franchises in regular listings. Are you sure wish to delete this main listing?', 'geodirectory' ),
		//start not show alert msg
		'rating_error_msg'                             => __( 'Error : please retry', 'geodirectory' ),
		'listing_url_prefix_msg'                       => __( 'Please enter listing url prefix', 'geodirectory' ),
		'invalid_listing_prefix_msg'                   => __( 'Invalid character in listing url prefix', 'geodirectory' ),
		'location_url_prefix_msg'                      => __( 'Please enter location url prefix', 'geodirectory' ),
		'invalid_location_prefix_msg'                  => __( 'Invalid character in location url prefix', 'geodirectory' ),
		'location_and_cat_url_separator_msg'           => __( 'Please enter location and category url separator', 'geodirectory' ),
		'invalid_char_and_cat_url_separator_msg'       => __( 'Invalid character in location and category url separator', 'geodirectory' ),
		'listing_det_url_separator_msg'                => __( 'Please enter listing detail url separator', 'geodirectory' ),
		'invalid_char_listing_det_url_separator_msg'   => __( 'Invalid character in listing detail url separator', 'geodirectory' ),
		'loading_listing_error_favorite'               => __( 'Error loading listing.', 'geodirectory' ),
		'field_id_required'                            => __( 'This field is required.', 'geodirectory' ),
		'valid_email_address_msg'                      => __( 'Please enter valid email address.', 'geodirectory' ),
		'default_marker_icon'                          => $default_marker_icon,
		'default_marker_w'                             => $default_marker_width,
		'default_marker_h'                             => $default_marker_height,
		'latitude_error_msg'                           => __( 'A numeric value is required. Please make sure you have either dragged the marker or clicked the button: Set Address On Map', 'geodirectory' ),
		'longgitude_error_msg'                         => __( 'A numeric value is required. Please make sure you have either dragged the marker or clicked the button: Set Address On Map', 'geodirectory' ),
		'gd_cmt_btn_post_reply'                        => __( 'Post Reply', 'geodirectory' ),
		'gd_cmt_btn_reply_text'                        => __( 'Reply text', 'geodirectory' ),
		'gd_cmt_btn_post_review'                       => __( 'Post Review', 'geodirectory' ),
		'gd_cmt_btn_review_text'                       => __( 'Review text', 'geodirectory' ),
		'gd_cmt_err_no_rating'                         => __( "Please select star rating, you can't leave a review without stars.", 'geodirectory' ),
		'err_max_file_size'                            => __( 'File size error : You tried to upload a file over %s', 'geodirectory' ),
		'err_file_upload_limit'                        => __( 'You have reached your upload limit of %s files.', 'geodirectory' ),
		'err_pkg_upload_limit'                         => __( 'You may only upload %s files with this package, please try again.', 'geodirectory' ),
		'action_remove'                                => __( 'Remove', 'geodirectory' ),
		'txt_all_files'                                => __( 'Allowed files', 'geodirectory' ),
		'err_file_type'                                => __( 'File type error. Allowed file types: %s', 'geodirectory' ),
		'gd_allowed_img_types'                         => ! empty( $allowed_img_types ) ? implode( ',', $allowed_img_types ) : '',
		'txt_form_wait'                                => __( 'Wait...', 'geodirectory' ),
		'txt_form_searching'                           => __( 'Searching...', 'geodirectory' ),
		'txt_form_my_location'                           => __( 'My Location', 'geodirectory' ),
		'rating_type'                                  => geodir_get_option( 'rating_type' ) ? geodir_get_option( 'rating_type' ) : 'font-awesome',
		'reviewrating'                                 => defined( 'GEODIR_REVIEWRATING_VERSION' ) ? 1 : '',
		'multirating'                                  => defined( 'GEODIR_REVIEWRATING_VERSION' ) && geodir_get_option( 'rr_enable_rating' ) ? true : false,
		'map_name'                                     => GeoDir_Maps::active_map(),
		'osmStart'                                     => __( 'Start', 'geodirectory' ),
		'osmVia'                                       => __( 'Via {viaNumber}', 'geodirectory' ),
		'osmEnd'                                       => __( 'Enter Your Location', 'geodirectory' ),
		'osmPressEnter'                                => __( 'Press Enter key to search', 'geodirectory' ),
		'geoMyLocation'                                => __( 'My Location', 'geodirectory' ),
		'geoErrUNKNOWN_ERROR'                          => addslashes( __( 'Unable to find your location', 'geodirectory' ) ),
		'geoErrPERMISSION_DENINED'                     => addslashes( __( 'Permission denied in finding your location', 'geodirectory' ) ),
		'geoErrPOSITION_UNAVAILABLE'                   => addslashes( __( 'Your location is currently unknown', 'geodirectory' ) ),
		'geoErrBREAK'                                  => addslashes( __( 'Attempt to find location took too long', 'geodirectory' ) ),
		'geoErrDEFAULT'                                => addslashes( __( 'Location detection not supported in browser', 'geodirectory' ) ),
		'i18n_set_as_default'                          => _x( 'Set as default', 'geodir select', 'geodirectory' ),
		'i18n_no_matches'                              => _x( 'No matches found', 'geodir select', 'geodirectory' ),
		'i18n_ajax_error'                              => _x( 'Loading failed', 'geodir select', 'geodirectory' ),
		'i18n_input_too_short_1'                       => _x( 'Please enter 1 or more characters', 'geodir select', 'geodirectory' ),
		'i18n_input_too_short_n'                       => _x( 'Please enter %item% or more characters', 'geodir select', 'geodirectory' ),
		'i18n_input_too_long_1'                        => _x( 'Please delete 1 character', 'geodir select', 'geodirectory' ),
		'i18n_input_too_long_n'                        => _x( 'Please delete %item% characters', 'geodir select', 'geodirectory' ),
		'i18n_selection_too_long_1'                    => _x( 'You can only select 1 item', 'geodir select', 'geodirectory' ),
		'i18n_selection_too_long_n'                    => _x( 'You can only select %item% items', 'geodir select', 'geodirectory' ),
		'i18n_load_more'                               => _x( 'Loading more results&hellip;', 'geodir select', 'geodirectory' ),
		'i18n_searching'                               => _x( 'Searching&hellip;', 'geodir select', 'geodirectory' ),
		'txt_choose_image'                             => __( 'Choose an image', 'geodirectory' ),
		'txt_use_image'                                => __( 'Use image', 'geodirectory' ),
		'img_spacer'                                   => admin_url( 'images/media-button-image.gif' ),
		'txt_post_review'                              => __( 'Post Review', 'geodirectory' ),
		'txt_post_reply'                               => __( 'Post reply', 'geodirectory' ),
		'txt_leave_a_review'                           => __( 'Leave a Review', 'geodirectory' ),
		'txt_leave_a_reply'                            => __( 'Leave a reply', 'geodirectory' ),
		'txt_reply_text'                               => __( 'Reply text', 'geodirectory' ),
		'txt_review_text'                              => __( 'Review text', 'geodirectory' ),
		'txt_read_more'                                => __( 'Read more', 'geodirectory' ),
		'txt_about_listing'                            => __( 'about this listing', 'geodirectory' ),
		'txt_open_now'                                 => __( 'Open now', 'geodirectory' ),
		'txt_closed_now'                               => __( 'Closed now', 'geodirectory' ),
		'txt_closed_today'                             => __( 'Closed today', 'geodirectory' ),
		'txt_closed'                                   => __( 'Closed', 'geodirectory' ),
		'txt_single_use'                               => __( "This field is single use only and is already being used.", 'geodirectory' ),
		'txt_page_settings'                            => __( "Page selections should not be the same, please correct the issue to continue.", 'geodirectory' ),
		'txt_save_other_setting'                       => __( 'Please save the current setting before adding a new one.', 'geodirectory' ),
		'txt_previous'                            	   => __( 'Previous', 'geodirectory' ),
		'txt_next'                            		   => __( 'Next', 'geodirectory' ),
		'txt_lose_changes'                             => __( 'You may lose changes if you navigate away now!', 'geodirectory' ),
		'txt_are_you_sure'                             => __( 'Are you sure?', 'geodirectory' ),
		'gmt_offset'                                   => geodir_gmt_offset(),
		'timezone_string'                              => geodir_timezone_string(),
		'autosave'                                     => apply_filters('geodir_autosave',10000),// 10000 = 10 seconds, set to 0 to disable
		'search_users_nonce'                           => wp_create_nonce( 'search-users' ),
		'google_api_key'                               => GeoDir_Maps::google_api_key(),
		'mapLanguage'                                  => GeoDir_Maps::map_language(),
		'osmRouteLanguage'                             => GeoDir_Maps::osm_routing_language(),
		'markerAnimation'                              => apply_filters( 'geodir_map_marker_animation', 'bounce' ), // bounce, drop or none
		'confirm_set_location'                         => addslashes( __( 'Would you like to manually set your location?', 'geodirectory' ) ),
		'confirm_lbl_error'                            => addslashes( __( 'ERROR:', 'geodirectory' ) ),
		'label_title'                                  => __( 'Title', 'geodirectory' ),
		'label_caption'                                => __( 'Caption', 'geodirectory' ),
		'button_set'                                   => __( 'Set', 'geodirectory' ),
		'BH_altTimeFormat'                             => geodir_bh_input_time_format( true ),
		'basic_nonce'                                  => wp_create_nonce( 'geodir_basic_nonce' ),
		'time_ago'                                     => array(
			'prefix_ago' => '',
			'suffix_ago' => ' ' . _x( 'ago', 'time ago', 'geodirectory' ),
			'prefix_after' => _x( 'after', 'time ago', 'geodirectory' ) . ' ',
			'suffix_after' => '',
			'seconds' => _x( 'less than a minute', 'time ago', 'geodirectory' ),
			'minute' => _x( 'about a minute', 'time ago', 'geodirectory' ),
			'minutes' => _x( '%d minutes', 'time ago', 'geodirectory' ),
			'hour' => _x( 'about an hour', 'time ago', 'geodirectory' ),
			'hours' => _x( 'about %d hours', 'time ago', 'geodirectory' ),
			'day' => _x( 'a day', 'time ago', 'geodirectory' ),
			'days' => _x( '%d days', 'time ago', 'geodirectory' ),
			'month' => _x( 'about a month', 'time ago', 'geodirectory' ),
			'months' => _x( '%d months', 'time ago', 'geodirectory' ),
			'year' => _x( 'about a year', 'time ago', 'geodirectory' ),
			'years' => _x( '%d years', 'time ago', 'geodirectory' ),
		),
		'resize_marker' => apply_filters( 'geodir_map_marker_resize_marker', false ), /* Resize map marker icon */
		'marker_max_width' => apply_filters( 'geodir_map_resize_marker_max_width', 50 ), /* Max width to apply resize marker icon */
		'marker_max_height' => apply_filters( 'geodir_map_resize_marker_max_height', 50 ) /* Max height to apply resize marker icon. */
	);

	/**
	 * Filters the translated JS strings from function geodir_params().
	 *
	 * With this filter you can add, remove or change translated JS strings.
	 * You should add your own translations to this if you are building an addon rather than adding another script block.
	 *
	 * @since 1.0.0
	 */
	return apply_filters( 'geodir_params', $arr_alert_msg );
}

/**
 * Define a constant if it is not already defined.
 *
 * @since 2.0.0
 *
 * @param string $name Constant name.
 * @param string $value Value.
 */
function geodir_maybe_define( $name, $value ) {
	if ( ! defined( $name ) ) {
		define( $name, $value );
	}
}

/**
 * Wrapper for nocache_headers which also disables page caching.
 *
 * @since 2.0.0
 */
function geodir_nocache_headers() {
	geodir_maybe_define( 'DONOTCACHEPAGE', true );
	geodir_maybe_define( 'DONOTCACHEOBJECT', true );
	geodir_maybe_define( 'DONOTCACHEDB', true );
	nocache_headers();
}

/**
 * Output a notification
 *
 * @param $user_notes
 *
 * @return string
 * @since 2.0.0.38 Added the ability for notifications t
 */
function geodir_notification( $user_notes ) {
	$notes = '';
	$design_style = geodir_design_style();
	foreach ( $user_notes as $key => $user_note ) {

		if(is_array($user_note)){

			$type = !empty($user_note['type']) ? esc_attr($user_note['type']) : '';
			$extra_class = !empty($user_note['extra_class']) ? esc_attr($user_note['extra_class']) : '';
			$icon = !empty($user_note['icon']) ? "<i class='".esc_attr($user_note['icon'])."'></i>" : '';
			$note = !empty($user_note['note']) ? $user_note['note']  : '';
			$dismissible = !empty($user_note['dismissible']) && $user_note['dismissible'] ? 'gd-is-dismissible'  : '';
			if(!$icon && $type){
				if($type=='error'){$icon = '<i class="fas fa-exclamation-circle"></i>';}
				elseif($type=='warning'){$icon = '<i class="fas fa-exclamation-triangle"></i>';}
				elseif($type=='success'){$icon = '<i class="fas fa-check-circle"></i>';}
				elseif($type=='info'){$icon = '<i class="fas fa-info-circle"></i>';}
			}
			
			if($design_style){
				$notes .= aui()->alert(array(
						'type'=> $type ? $type : 'info',
						'content'=> $note,
						'dismissible'=> !empty($user_note['dismissible']) && $user_note['dismissible']!==false ? true : false,
						'class' => !empty($user_note['icon']) ? $user_note['icon'].$extra_class : $extra_class // escaped in AUI
					)
				);
			}else{
				$notes .= "<div class='gd-notification gd-$type $extra_class $dismissible'>";
				if($icon) {$notes .= $icon. " ";}
				$notes .= $note;
				if($dismissible){$notes .= '<i class="fas fa-times gd-notification-dismiss" onclick="jQuery(this).parent().fadeOut();" title="'.__('Dismiss','geodirectory').'"></i>';}
				$notes .= "</div>";
			}
		}else{
			if($design_style){
				$notes .= aui()->alert(array(
						'type'=> $key,
						'content'=> $user_note,
					)
				);
			}else{
				$notes .= "<div class='gd-notification $key'>";
				$notes .= $user_note;
				$notes .= "</div>";
			}

		}


	}

	return $notes;
}

/**
 * Generate a rand hash.
 *
 * @since  2.0.0
 * @return string
 */
function geodir_rand_hash() {
	if ( function_exists( 'openssl_random_pseudo_bytes' ) ) {
		return bin2hex( openssl_random_pseudo_bytes( 20 ) );
	} else {
		return sha1( wp_rand() );
	}
}

/**
 * GeoDir API - Hash.
 *
 * @since  2.0.0
 *
 * @param  string $data
 *
 * @return string
 */
function geodir_api_hash( $data ) {
	return hash_hmac( 'sha256', $data, 'wc-api' );
}


/**
 * Check table column exist or not.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 *
 * @param string $db The table name.
 * @param string $column The column name.
 *
 * @return bool If column exists returns true. Otherwise false.
 */
function geodir_column_exist( $db, $column ) {
	global $wpdb;
	$exists  = false;
	$columns = $wpdb->get_col( "show columns from $db" );
	foreach ( $columns as $c ) {
		if ( $c == $column ) {
			$exists = true;
			break;
		}
	}

	return $exists;
}

/**
 * Register Widgets.
 *
 * @since 2.0.0
 */
function goedir_register_widgets() {

	if ( get_option( 'geodirectory_version' ) ) {
		register_widget( 'GeoDir_Widget_Search' );
		register_widget( 'GeoDir_Widget_Best_Of' );
		register_widget( 'GeoDir_Widget_Categories' );
		register_widget( 'GeoDir_Widget_Category_Description' );
		register_widget( 'GeoDir_Widget_Dashboard' );
		register_widget( 'GeoDir_Widget_Recent_Reviews' );
		register_widget( 'GeoDir_Widget_CPT_Meta' );

		// post widgets
		register_widget( 'GeoDir_Widget_Post_Badge' );
		register_widget( 'GeoDir_Widget_Post_Meta' );
		register_widget( 'GeoDir_Widget_Post_Images' );
		register_widget( 'GeoDir_Widget_Post_Title' );
		register_widget( 'GeoDir_Widget_Post_Rating' );
		register_widget( 'GeoDir_Widget_Post_Fav' );
		register_widget( 'GeoDir_Widget_Post_Directions' );
		register_widget( 'GeoDir_Widget_Post_Content' );
		register_widget( 'GeoDir_Widget_Post_Address' );


		// Widgets
		register_widget( 'GeoDir_Widget_Output_location' );
		register_widget( 'GeoDir_Widget_Author_Actions' );
		register_widget( 'GeoDir_Widget_Listings' );
		register_widget( 'GeoDir_Widget_Map' );
		register_widget( 'GeoDir_Widget_Recently_Viewed' );
		register_widget( 'GeoDir_Widget_Single_Tabs' );
		register_widget( 'GeoDir_Widget_Notifications' );
		register_widget( 'GeoDir_Widget_Add_Listing' );
		register_widget( 'GeoDir_Widget_Dynamic_Content' );


		// Template widgets
		register_widget( 'GeoDir_Widget_Loop' );
		register_widget( 'GeoDir_Widget_Loop_Paging' );
		register_widget( 'GeoDir_Widget_Loop_Actions' );
		register_widget( 'GeoDir_Widget_Archive_Item_Section' );
		register_widget( 'GeoDir_Widget_Single_Taxonomies' );
		register_widget( 'GeoDir_Widget_Single_Next_Prev' );
		register_widget( 'GeoDir_Widget_Single_Reviews' );
		register_widget( 'GeoDir_Widget_Post_Distance' );
		register_widget( 'GeoDir_Widget_Map_Pinpoint' );
		register_widget( 'GeoDir_Widget_Page_Title' );

		// Depreciated
		new GeoDir_Widget_Single_Closed_Text();

		// 3rd party widgets
		if ( class_exists( 'Ninja_Forms' ) && class_exists( 'NF_Abstracts_MergeTags' ) ) {
			register_widget( 'GeoDir_Widget_Ninja_Forms' );
		}
	}
}

add_action( 'widgets_init', 'goedir_register_widgets' );

/**
 * Function for widget pages options.
 *
 * @since 2.0.0
 *
 * @return array $gd_widget_pages.
 */
function geodir_widget_pages_options() {
	global $gd_widget_pages;

	if ( ! empty( $gd_widget_pages ) && is_array( $gd_widget_pages ) ) {
		return $gd_widget_pages;
	}

	$gd_widget_pages       = array();
	$gd_widget_pages['gd'] = array(
		'label' => __( 'GD Pages', 'geodirectory' ),
		'pages' => array(
			'add-listing'     => __( 'Add Listing Page', 'geodirectory' ),
			'author'          => __( 'Author Page', 'geodirectory' ),
			'detail'          => __( 'Listing Detail Page', 'geodirectory' ),
			'preview'         => __( 'Listing Preview Page', 'geodirectory' ),
			'listing-success' => __( 'Listing Success Page', 'geodirectory' ),
			'location'        => __( 'Location Page', 'geodirectory' ),
			'login'           => __( 'Login Page', 'geodirectory' ),
			'pt'              => __( 'Post Type Archive', 'geodirectory' ),
			'search'          => __( 'Search Page', 'geodirectory' ),
			'listing'         => __( 'Taxonomies Page', 'geodirectory' ),
		),
	);

	return apply_filters( 'geodir_widget_pages_options', $gd_widget_pages );
}

/**
 * Function for widget page id bases detail.
 *
 * @since 2.0.0
 *
 * @return array $id_bases.
 */
function geodir_detail_page_widget_id_bases() {
	$id_bases = array(
		'detail_user_actions',
		'detail_social_sharing',
		'detail_sidebar',
		'detail_sidebar_info',
		'detail_rating_stars',
	);

	return apply_filters( 'geodir_detail_page_widget_id_bases', $id_bases );
}

/**
 * Function for check is detail page widget.
 *
 * @since 2.0.0
 *
 * @param string $id_base widget page id base.
 *
 * @return bool $return.
 */
function geodir_is_detail_page_widget( $id_base ) {
	$widgets = geodir_detail_page_widget_id_bases();

	$return = ! empty( $id_base ) && ! empty( $widgets ) && in_array( $id_base, $widgets ) ? true : false;

	return apply_filters( 'geodir_is_detail_page_widget', $return, $id_base, $widgets );
}

/**
 * Function for display widget c
 *
 *
 * @since 2.0.0
 *
 * @param array $instance {
 *      An array display widget arguments.
 *
 * @type string $gd_wgt_showhide Widget display type.
 * @type string $gd_wgt_restrict Widget restrict pages.
 * }
 *
 * @param object $widget Display widget options.
 * @param array $args Widget arguments.
 *
 * @return bool|array $instance
 */
function geodir_widget_display_callback( $instance, $widget, $args ) {
	if ( ! empty( $widget->widget_options['geodirectory'] ) && ! empty( $instance['gd_wgt_showhide'] ) ) {
		$display_type = ! empty( $instance['gd_wgt_showhide'] ) ? $instance['gd_wgt_showhide'] : '';
		$pages        = ! empty( $instance['gd_wgt_restrict'] ) && is_array( $instance['gd_wgt_restrict'] ) ? $instance['gd_wgt_restrict'] : array();

		$show = $instance;

		if ( $display_type == 'show' ) {
			$show = $instance; // Show on all pages.
		} else if ( $display_type == 'hide' ) {
			$show = false; // Hide on all pages.
		} else if ( $display_type == 'gd' ) {
			if ( ! geodir_is_geodir_page() ) {
				$show = false; // Show only on GD pages.
			}
		} else {
			if ( geodir_is_detail_page_widget( $widget->id_base ) ) {
				if ( geodir_is_page( 'detail' ) ) {
					if ( ! in_array( 'gd-detail', $pages ) ) {
						$show = false;
					}
				} else if ( geodir_is_page( 'preview' ) ) {
					if ( ! in_array( 'gd-preview', $pages ) ) {
						$show = false;
					}
				} else {
					$show = false;
				}
			} else {
				$gd_widget_pages = geodir_widget_pages_options();
				$gd_page         = '';

				if ( ! empty( $gd_widget_pages['gd']['pages'] ) ) {
					$gd_pages = $gd_widget_pages['gd']['pages'];

					foreach ( $gd_pages as $page => $page_title ) {
						if ( geodir_is_page( $page ) ) {
							$gd_page = $page;
							break;
						}
					}
				}

				if ( $display_type == 'show_on' ) {
					if ( $gd_page && in_array( 'gd-' . $gd_page, $pages ) ) {
						$show = $instance;
					} else {
						$show = false;
					}
				} else if ( $display_type == 'hide_on' ) {
					if ( $gd_page && in_array( 'gd-' . $gd_page, $pages ) ) {
						$show = false;
					} else {
						$show = $instance;
					}
				} else {
					$show = false;
				}
			}
		}

		$instance = $show;
	}

	return $instance;
}

add_filter( 'widget_display_callback', 'geodir_widget_display_callback', 10, 3 );


global $geodir_addon_list;
/**
 * Build an array of installed addons.
 *
 * This filter builds an array of installed addons which can be used to check what exactly is installed.
 *
 * @since 1.0.0
 * @package GeoDirectory
 *
 * @param array $geodir_addon_list The array of installed plugins $geodir_addon_list['geodir_location_manager'].
 */
apply_filters( 'geodir_build_addon_list', $geodir_addon_list );

/**
 * Add GeoDirectory link to the WordPress admin bar.
 *
 * This function adds a link to the GeoDirectory backend to the WP admin bar via a hook.
 *    add_action('admin_bar_menu', 'geodir_admin_bar_site_menu', 31);
 *
 * @since 1.0.0
 * @package GeoDirectory
 *
 * @param object $wp_admin_bar The admin bar object.
 */
function geodir_admin_bar_site_menu( $wp_admin_bar ) {
	if ( current_user_can( 'manage_options' ) ) {
		$wp_admin_bar->add_menu( array(
			'parent' => 'appearance',
			'id'     => 'geodirectory',
			'title'  => __( 'GeoDirectory', 'geodirectory' ),
			'href'   => admin_url( 'admin.php?page=geodirectory' )
		) );
	}
}

add_action( 'admin_bar_menu', 'geodir_admin_bar_site_menu', 31 );

/**
 * Fix query params sometimes not working as & becomes &#038;
 *
 * @since 1.0.0
 * @package GeoDirectory
 *
 * @param string $url Url.
 * @param string $original_url Original url.
 * @param string $_context Context.
 *
 * @return string Modified url.
 */
function so_handle_038( $url, $original_url, $_context ) {
	if ( strstr( $url, "maps.google.com/maps/api/js" ) !== false ) {
		$url = str_replace( "&#038;", "&amp;", $url ); // or $url = $original_url
	}

	return $url;
}

/**
 * Add body class for current active map.
 *
 * @since 1.6.16
 * @package GeoDirectory
 *
 * @param array $classes The class array of the HTML element.
 *
 * @return array Modified class array.
 */
function geodir_body_class_active_map( $classes = array() ) {
	$classes[] = 'gd-map-' . GeoDir_Maps::active_map();

	return $classes;
}

add_filter( 'body_class', 'geodir_body_class_active_map', 100 );

/**
 * remove rating stars fields if disabled.
 *
 * @since 1.0.0
 * @since 1.6.16 Changes for disable review stars for certain post type.
 * @package GeoDirectory
 */
function geodir_init_no_rating() {
	if ( geodir_rating_disabled_post_types() ) {
		add_filter( 'geodir_get_sort_options', 'geodir_no_rating_get_sort_options', 100, 2 );
	}
}

add_action( 'init', 'geodir_init_no_rating', 100 );


/**
 * Skip overall rating sort option when rating disabled.
 *
 * @since 1.0.0
 * @since 1.6.16 Changes for disable review stars for certain post type.
 * @package GeoDirectory
 *
 * @param array $options Sort options array.
 * @param string $post_type The post type.
 *
 * @return array Modified sort options array.
 */
function geodir_no_rating_get_sort_options( $options, $post_type = '' ) {
	if ( ! empty( $post_type ) && geodir_cpt_has_rating_disabled( $post_type ) ) {
		$new_options = array();

		if ( ! empty( $options ) ) {
			foreach ( $options as $option ) {
				if ( is_object( $option ) && isset( $option->htmlvar_name ) && $option->htmlvar_name == 'overall_rating' ) {
					continue;
				}
				$new_options[] = $option;
			}

			$options = $new_options;
		}
	}

	return $options;
}

/**
 * Search form submit button.
 *
 * @since 2.0.0
 */
function geodir_search_form_submit_button() {
	$default_search_button_label = geodir_get_option('search_default_button_text');
	if(!$default_search_button_label){$default_search_button_label = geodir_get_search_default_button_text();}


	/**
	 * Filter the default search button text value for the search form.
	 *
	 * This text can be changed via an option in settings, this is a last resort.
	 *
	 * @since 1.5.5
	 *
	 * @param string $default_search_button_label The current search button text.
	 */
	$default_search_button_label = apply_filters( 'geodir_search_default_search_button_text', $default_search_button_label );
	$fa_class = false;
	if ( geodir_is_fa_icon( $default_search_button_label ) ) {
		$fa_class = true;
	}

	$args = array(
		'fa_class'  => $fa_class,
		'default_search_button_label'  => $default_search_button_label,
	);
	$design_style = geodir_design_style();
	$template = $design_style ? $design_style."/search-bar/button-search.php" : "legacy/search/button-search.php";
	echo geodir_get_template_html( $template, $args );
}

add_action( 'geodir_before_search_button', 'geodir_search_form_submit_button', 5000 );

/**
 * Search form post type input.
 *
 * @since 2.0.0
 */
function geodir_search_form_post_type_input() {
	global $geodir_search_post_type,$geodir_search_post_type_hide;
	$post_types     = apply_filters( 'geodir_search_form_post_types', geodir_get_posttypes( 'object' ) );
	$curr_post_type = $geodir_search_post_type;

	if ( ! empty( $post_types ) && count( (array) $post_types ) > 1 ) {

		foreach ( $post_types as $post_type => $info ){
			global $wpdb;
			$has_posts = $wpdb->get_row( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = %s AND post_status='publish' LIMIT 1", $post_type ) );
			if ( ! $has_posts ) {
				unset($post_types->{$post_type});
			}
		}

		$show_select = true;
		if($geodir_search_post_type_hide == true && !isset( $_REQUEST['stype'] ) ){
			$show_select = false;
		}

		if ( ! empty( $post_types ) && count( (array) $post_types ) > 1 && $show_select) {

			$design_style = geodir_design_style();

			$new_style = geodir_get_option( 'geodir_show_search_old_search_from' ) ? false : true;
			if ( $new_style ) {
				$wrap_class = $design_style ? " col-auto flex-fill" : '';
				echo "<div class='gd-search-input-wrapper gd-search-field-cpt $wrap_class'>";
			}

			$select_class = $design_style ? " form-control custom-select" : '';

			echo $design_style ? '<div class="form-group">' : '';
			echo $design_style ? '<label class="sr-only sr-only ">'.__("Select search type","geodirectory").'</label>' : '';

			?>
			<select name="stype" class="search_by_post <?php echo $select_class;?>" aria-label="<?php esc_attr_e( 'Post Type', 'geodirectory' ); ?>">
				<?php foreach ( $post_types as $post_type => $info ):
					global $wpdb;
					$pt_slug = isset($info->rewrite->slug) ? esc_attr($info->rewrite->slug) : 'places';
					?>

					<option
						<?php echo ' data-slug="'.$pt_slug.'" ';?>
						data-label="<?php echo get_post_type_archive_link( $post_type ); ?>"
					        value="<?php echo $post_type; ?>" <?php
					if ( isset( $_REQUEST['stype'] ) ) {
						if ( $post_type == $_REQUEST['stype'] ) {
							echo 'selected="selected"';
						}
					} elseif ( $curr_post_type == $post_type ) {
						echo 'selected="selected"';
					} ?>><?php _e( geodir_utf8_ucfirst( $info->labels->name ), 'geodirectory' ); ?></option>

				<?php endforeach; ?>
			</select>
			<?php
			echo $design_style ? '</div>' : '';

			if ( $new_style ) {
				echo "</div>";
			}
		}else{
			if(! empty( $post_types )){
				$post_types = (array)$post_types;
				if($curr_post_type && isset($post_types[$curr_post_type])){
					$pt_arr = $post_types[$curr_post_type];
					$pt_value = $curr_post_type;
				}else{
					$pt_value = key( $post_types );
					$pt_arr = reset($post_types);
				}

				$pt_slug = isset($pt_arr->rewrite->slug) ? esc_attr($pt_arr->rewrite->slug) : 'places';
				echo '<input type="hidden" name="stype" value="' . esc_attr( $pt_value  ) . '" data-slug="'.$pt_slug.'" />';
			}else{
				echo '<input type="hidden" name="stype" value="gd_place"  data-slug="places"/>';
			}

		}

	}elseif ( ! empty( $post_types ) ) {
		$pt_arr = (array)$post_types;
		$key = key( $pt_arr);
		$pt_arr = $pt_arr[$key];
		$pt_slug = isset($pt_arr->rewrite->slug) ? esc_attr($pt_arr->rewrite->slug) : 'places';
		echo '<input type="hidden" name="stype" value="gd_place" data-slug="'.$pt_slug.'" />';
	}
}

/**
 * Search form search inputs.
 *
 * @since 2.0.0
 */
function geodir_search_form_search_input() {
	$default_search_for_text = geodir_get_option('search_default_text');
	if(!$default_search_for_text){$default_search_for_text = geodir_get_search_default_text();}

	$search_term = '';
	if ( isset( $_REQUEST['s'] ) && trim( $_REQUEST['s'] ) != '' ) {
		$search_term = esc_attr( stripslashes_deep( $_REQUEST['s'] ) );
		$search_term = str_replace(array("%E2%80%99","’"),array("%27","'"),$search_term);// apple suck
	}

	$args = array(
		'default_search_for_text' => $default_search_for_text,
		'search_term'  => $search_term,
	);

	$design_style = geodir_design_style();
	$template = $design_style ? $design_style."/search-bar/input-search.php" : "legacy/search/input-search.php";
	echo geodir_get_template_html( $template, $args  );
}

/**
 * Search form near inputs.
 *
 * @since 2.0.0
 */
function geodir_search_form_near_input() {
	$default_near_text = geodir_get_option('search_default_near_text');
	if(!$default_near_text){$default_near_text = geodir_get_search_default_near_text();}

	if ( isset( $_REQUEST['snear'] ) && $_REQUEST['snear'] != '' ) {
		$near = esc_attr( stripslashes_deep( $_REQUEST['snear'] ) );
	} else {
		$near = '';
	}

	global $geodir_search_post_type;
	$curr_post_type = $geodir_search_post_type;
	/**
	 * Used to hide the near field and other things.
	 *
	 * @since 1.6.9
	 * @param string $curr_post_type The current post type.
	 */
	$near_input_extra = apply_filters('geodir_near_input_extra','',$curr_post_type); // @todo we will need to fix this

	/**
	 * Filter the "Near" text value for the search form.
	 *
	 * This is the input "value" attribute and can change depending on what the user searches and is not always the default value.
	 *
	 * @since 1.0.0
	 *
	 * @param string $near              The current near value.
	 * @param string $default_near_text The default near value.
	 */
	$near = apply_filters( 'geodir_search_near_text', $near, $default_near_text );
	/**
	 * Filter the default "Near" text value for the search form.
	 *
	 * This is the default value if nothing has been searched.
	 *
	 * @since 1.0.0
	 *
	 * @param string $near              The current near value.
	 * @param string $default_near_text The default near value.
	 */
	$default_near_text = apply_filters( 'geodir_search_default_near_text', $default_near_text, $near );
	/**
	 * Filter the class for the near search input.
	 *
	 * @since 1.0.0
	 *
	 * @param string $class The class for the HTML near input, default is blank.
	 */
	$near_class = apply_filters( 'geodir_search_near_class', '' );

	add_action( 'wp_print_footer_scripts', array( 'GeoDir_Maps', 'check_map_script' ), 99999 );

	$args = array(
		'near_class' => $near_class,
		'default_near_text' => $default_near_text,
		'near' => $near,
		'near_input_extra' => $near_input_extra,
	);
	$design_style = geodir_design_style();
	$template = $design_style ? $design_style."/search-bar/input-near.php" : "legacy/search/input-near.php";
	echo geodir_get_template_html( $template, $args );
}

add_action( 'geodir_search_form_inputs', 'geodir_search_form_post_type_input', 10 );
add_action( 'geodir_search_form_inputs', 'geodir_search_form_search_input', 20 );
add_action( 'geodir_search_form_inputs', 'geodir_search_form_near_input', 30 );

/**
 * Adds a icon to the search near input.
 */
function geodir_search_near_label() {
	if(!geodir_design_style()){
		echo '<span class="gd-icon-hover-swap geodir-search-input-label" onclick="jQuery(\'.snear\').val(\'\').trigger(\'change\').trigger(\'keyup\');jQuery(\'.sgeo_lat,.sgeo_lon\').val(\'\');">';
		echo '<i class="fas fa-map-marker-alt gd-show"></i>';
		echo '<i class="fas fa-times geodir-search-input-label-clear gd-hide" title="'.__('Clear field','geodirectory').'"></i>';
		echo '</span>';
	}

}
add_action('geodir_before_search_near_input','geodir_search_near_label');

/**
 * Adds a icon to the search for input.
 */
function geodir_search_for_label() {
	if(!geodir_design_style()) {
		echo '<span class="gd-icon-hover-swap geodir-search-input-label" onclick="jQuery(\'.search_text\').val(\'\').trigger(\'change\').trigger(\'keyup\');">';
		echo '<i class="fas fa-search gd-show"></i>';
		echo '<i class="fas fa-times geodir-search-input-label-clear gd-hide" title="' . __( 'Clear field', 'geodirectory' ) . '"></i>';
		echo '</span>';
	}
}
add_action('geodir_before_search_for_input','geodir_search_for_label');

/**
 * Get search post type.
 *
 * @since 2.0.0
 *
 * @param string $pt Optional. Post type. Default null.
 * @return string $geodir_search_post_type.
 */
function geodir_get_search_post_type($pt=''){
	global $geodir_search_post_type;

	if($pt!=''){return $geodir_search_post_type = $pt;}
	if(!empty($geodir_search_post_type)){ return $geodir_search_post_type;}

	$geodir_search_post_type = geodir_get_current_posttype();

	if(!$geodir_search_post_type) {
		$geodir_search_post_type = geodir_get_default_posttype();
	}

	return $geodir_search_post_type;
}

/**
 * Search form.
 *
 * @since 2.0.0
 */
function geodir_search_form() {
	geodir_get_search_post_type();

	$design_style = geodir_design_style();
	$template = $design_style ? $design_style . "/search-bar/form.php" : "listing-filter-form.php";

	$args = array();
	if ( wp_doing_ajax() && ! empty( $_POST['keepArgs'] ) ) {
		$_args = json_decode( stripslashes( sanitize_text_field( $_POST['keepArgs'] ) ), true );

		if ( ! empty( $_args ) && is_array( $_args ) ) {
			$args = $_args;
		}
	}

	$args = array(
		'wrap_class' => geodir_build_aui_class( $args ),
		'keep_args' => $args
	);

	echo geodir_get_template_html( $template, $args );

	// Always die in functions echoing ajax content
	die();
}

add_action( 'wp_ajax_geodir_search_form', 'geodir_search_form' );
add_action( 'wp_ajax_nopriv_geodir_search_form', 'geodir_search_form' );

/**
 * Get the CPT that disabled review stars.
 *
 * @since 1.6.16
 *
 * @param string $post_type WP post type or WP texonomy. Ex: gd_place.
 * @param bool $taxonomy Whether $post_type is taxonomy or not.
 * @return bool True if review star disabled, otherwise false.
 */
function geodir_rating_disabled_post_types() {
	//$post_types = geodir_get_option( 'geodir_disable_rating_cpt' );

	$disabled = array();

	$post_types = geodir_get_posttypes('array');

	if(!empty($post_types )){
		foreach($post_types as $post_type => $val){
			if(isset($val['disable_reviews']) && $val['disable_reviews']){
				$disabled[] = $post_type;
			}
		}
	}


	/**
	 * Filter the post types array which have rating disabled.
	 *
	 * @since 1.6.16
	 *
	 * @param array $post_types Array of post types which have rating starts disabled.
	 */
	return apply_filters( 'geodir_rating_disabled_post_types', $disabled );
}

/**
 * Check review star disabled for certain CPT.
 *
 * @since 1.6.16
 *
 * @param string|int $post_type WP post type or Post ID or WP texonomy. Ex: gd_place.
 * @param bool $taxonomy Whether $post_type is taxonomy or not.
 * @return bool True if review star disabled, otherwise false.
 */
function geodir_cpt_has_rating_disabled( $post_type = '', $taxonomy = false ) {
	$post_types = $post_types = geodir_get_posttypes('array');
	if(isset($post_types[$post_type]['disable_reviews']) && $post_types[$post_type]['disable_reviews']){
		return true;
	}else{
		return false;
	}
}


/**
 * Check favourite disabled for certain CPT.
 *
 * @since 2.0.0
 *
 * @param string|int $post_type WP post type or Post ID or WP texonomy. Ex: gd_place.
 * @return bool True if review star disabled, otherwise false.
 */
function geodir_cpt_has_favourite_disabled( $post_type = '') {
	$post_types = $post_types = geodir_get_posttypes('array');
	if(isset($post_types[$post_type]['disable_favorites']) && $post_types[$post_type]['disable_favorites']){
		return true;
	}else{
		return false;
	}
}


/**
 * Get the search page base url.
 *
 * @since 1.6.24
 *
 * @return string Filtered url.
 */
function geodir_search_page_base_url() {
	if ( function_exists( 'geodir_location_geo_home_link' ) ) {
		remove_filter( 'home_url', 'geodir_location_geo_home_link', 100000 );
	}

	$url = get_permalink(geodir_search_page_id());

	$url = trailingslashit( $url );

	if ( function_exists( 'geodir_location_geo_home_link' ) ) {
		add_filter( 'home_url', 'geodir_location_geo_home_link', 100000, 2 );
	}

	return apply_filters( 'geodir_search_page_base_url', $url );
}

/**
 * Output the Auth header.
 */
function geodir_output_auth_header() {
	geodir_get_template( 'auth/header.php' );
}

add_action( 'geodir_auth_page_header', 'geodir_output_auth_header', 10 );

/**
 * Output the Auth footer.
 */
function geodir_output_auth_footer() {
	geodir_get_template( 'auth/footer.php' );
}

add_action( 'geodir_auth_page_footer', 'geodir_output_auth_footer', 10 );

