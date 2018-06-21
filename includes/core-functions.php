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
 * @param mixed $log The thing that should be logged.
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
 * @return bool True for custom taxonomy archive pages, false for built-in taxonomies.
 */
function geodir_is_taxonomy( $taxonomies = array() ) {
    if ( empty( $taxonomis ) ) {
        $taxonomis = geodir_get_taxonomies( '', true );
    }

    return is_tax( $taxonomis );
}

/**
 * In this function existing post type archive page?
 *
 * @since 2.0.0
 *
 * @param array $post_types Optional. Array of post types. Default array().
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
 * @param  string $tip        Help tip text
 * @param  bool   $allow_html Allow sanitized HTML if true or escape
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
		 * @param string $permalink_structure     The new permalink structure.
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
function geodir_get_search_default_text(){
    return __('Search for','geodirectory');
}

/**
 * Get the search near form default text.
 *
 * @since 2.0.0
 * @return string|void
 */
function geodir_get_search_default_near_text(){
    return __('Near','geodirectory');
}

/**
 * Get the search form default text.
 *
 * @since 2.0.0
 * @return string|void
 */
function geodir_get_search_default_button_text(){
    return __('fa-search','geodirectory');
}



/**
 * Outputs translated JS text strings.
 *
 * This function outputs text strings used in JS files as a json array of strings so they can be translated and still be used in JS files.
 *
 * @since 1.0.0
 * @package GeoDirectory
 */
function geodir_params()
{// check_ajax_referer function is used to make sure no files are uploaded remotely but it will fail if used between https and non https so we do the check below of the urls
    if (str_replace("https", "http", admin_url('admin-ajax.php')) && !empty($_SERVER['HTTPS'])) {
        $ajax_url = admin_url('admin-ajax.php');
    } elseif (!str_replace("https", "http", admin_url('admin-ajax.php')) && empty($_SERVER['HTTPS'])) {
        $ajax_url = admin_url('admin-ajax.php');
    } elseif (str_replace("https", "http", admin_url('admin-ajax.php')) && empty($_SERVER['HTTPS'])) {
        $ajax_url = str_replace("https", "http", admin_url('admin-ajax.php'));
    } elseif (!str_replace("https", "http", admin_url('admin-ajax.php')) && !empty($_SERVER['HTTPS'])) {
        $ajax_url = str_replace("http", "https", admin_url('admin-ajax.php'));
    }

    /**
     * Filter the allowed image type extensions for post images.
     *
     * @since 1.4.7.1
     * @param string $allowed_img_types The image type extensions array.
     */
    $allowed_img_types = apply_filters('geodir_allowed_post_image_exts', array('jpg', 'jpeg', 'jpe', 'gif', 'png'));

    $default_marker_icon = geodir_default_marker_icon( true );
    $default_marker_size = geodir_get_marker_size($default_marker_icon, array('w' => 20, 'h' => 34));
    $default_marker_width = $default_marker_size['w'];
    $default_marker_height = $default_marker_size['h'];

    $arr_alert_msg = array(
        'plugin_url' => geodir_plugin_url(),
        'ajax_url' => $ajax_url,
        'custom_field_not_blank_var' => __('Field label must not be blank', 'geodirectory'),
        'custom_field_not_special_char' => __('Please do not use special character and spaces in field key Variable Name.', 'geodirectory'),
        'custom_field_unique_name' => __('Field key must be unique.', 'geodirectory'),
        'custom_field_delete' => __('Are you sure you wish to delete this field?', 'geodirectory'),
        'custom_field_delete_children' => __('You must move or remove child elements first.', 'geodirectory'),
        //start not show alert msg
        'tax_meta_class_succ_del_msg' => __('File has been successfully deleted.', 'geodirectory'),
        'tax_meta_class_not_permission_to_del_msg' => __('You do NOT have permission to delete this file.', 'geodirectory'),
        'tax_meta_class_order_save_msg' => __('Order saved!', 'geodirectory'),
        'tax_meta_class_not_permission_record_img_msg' => __('You do not have permission to reorder images.', 'geodirectory'),
        'address_not_found_on_map_msg' => __('Address not found for:', 'geodirectory'),
        // end not show alert msg
        'my_place_listing_del' => __('Are you sure you wish to delete this listing?', 'geodirectory'),
        'my_main_listing_del' => __('Deleting the main listing of a franchise will turn all franchises in regular listings. Are you sure wish to delete this main listing?', 'geodirectory'),
        //start not show alert msg
        'rating_error_msg' => __('Error : please retry', 'geodirectory'),
        'listing_url_prefix_msg' => __('Please enter listing url prefix', 'geodirectory'),
        'invalid_listing_prefix_msg' => __('Invalid character in listing url prefix', 'geodirectory'),
        'location_url_prefix_msg' => __('Please enter location url prefix', 'geodirectory'),
        'invalid_location_prefix_msg' => __('Invalid character in location url prefix', 'geodirectory'),
        'location_and_cat_url_separator_msg' => __('Please enter location and category url separator', 'geodirectory'),
        'invalid_char_and_cat_url_separator_msg' => __('Invalid character in location and category url separator', 'geodirectory'),
        'listing_det_url_separator_msg' => __('Please enter listing detail url separator', 'geodirectory'),
        'invalid_char_listing_det_url_separator_msg' => __('Invalid character in listing detail url separator', 'geodirectory'),
        'loading_listing_error_favorite' => __('Error loading listing.', 'geodirectory'),
        'field_id_required' => __('This field is required.', 'geodirectory'),
        'valid_email_address_msg' => __('Please enter valid email address.', 'geodirectory'),
        'default_marker_icon' => $default_marker_icon,
        'default_marker_w' => $default_marker_width,
        'default_marker_h' => $default_marker_height,
        'latitude_error_msg' => GEODIR_LATITUDE_ERROR_MSG,
        'longgitude_error_msg' => GEODIR_LOGNGITUDE_ERROR_MSG,
        'gd_cmt_btn_post_reply' => __('Post Reply', 'geodirectory'),
        'gd_cmt_btn_reply_text' => __('Reply text', 'geodirectory'),
        'gd_cmt_btn_post_review' => __('Post Review', 'geodirectory'),
        'gd_cmt_btn_review_text' => __('Review text', 'geodirectory'),
        'gd_cmt_err_no_rating' => __("Please select star rating, you can't leave a review without stars.", 'geodirectory'),
        'err_max_file_size' => __('File size error : You tried to upload a file over %s', 'geodirectory'),
        'err_file_upload_limit' => __('You have reached your upload limit of %s files.', 'geodirectory'),
        'err_pkg_upload_limit' => __('You may only upload %s files with this package, please try again.', 'geodirectory'),
        'action_remove' => __('Remove', 'geodirectory'),
        'txt_all_files' => __('Allowed files', 'geodirectory'),
        'err_file_type' => __('File type error. Allowed file types: %s', 'geodirectory'),
        'gd_allowed_img_types' => !empty($allowed_img_types) ? implode(',', $allowed_img_types) : '',
        'txt_form_wait' => __('Wait...', 'geodirectory'),
        'txt_form_searching' => __('Searching...', 'geodirectory'),
        'rating_type' => geodir_get_option('rating_type') ? geodir_get_option('rating_type') : 'font-awesome',
        'reviewrating' => defined('GEODIRREVIEWRATING_VERSION') ? 1 : '',
        'multirating' => defined('GEODIRREVIEWRATING_VERSION') && geodir_get_option('geodir_reviewrating_enable_rating') ? true : false,
        'map_name' => geodir_map_name(),
        'osmStart' => __('Start', 'geodirectory'),
        'osmVia' => __('Via {viaNumber}', 'geodirectory'),
        'osmEnd' => __('Enter Your Location', 'geodirectory'),
        'geoMyLocation' => __('My Location', 'geodirectory'),
        'geoErrUNKNOWN_ERROR' => addslashes(__('Unable to find your location', 'geodirectory')),
        'geoErrPERMISSION_DENINED' => addslashes(__('Permission denied in finding your location', 'geodirectory')),
        'geoErrPOSITION_UNAVAILABLE' => addslashes(__('Your location is currently unknown', 'geodirectory')),
        'geoErrBREAK' => addslashes(__('Attempt to find location took too long', 'geodirectory')),
        'geoErrDEFAULT' => addslashes(__('Location detection not supported in browser', 'geodirectory')),
        'i18n_set_as_default' => _x( 'Set as default', 'geodir select', 'geodirectory' ),
        'i18n_no_matches' => _x( 'No matches found', 'geodir select', 'geodirectory' ),
        'i18n_ajax_error' => _x( 'Loading failed', 'geodir select', 'geodirectory' ),
        'i18n_input_too_short_1' => _x( 'Please enter 1 or more characters', 'geodir select', 'geodirectory' ),
        'i18n_input_too_short_n' => _x( 'Please enter %item% or more characters', 'geodir select', 'geodirectory' ),
        'i18n_input_too_long_1' => _x( 'Please delete 1 character', 'geodir select', 'geodirectory' ),
        'i18n_input_too_long_n' => _x( 'Please delete %item% characters', 'geodir select', 'geodirectory' ),
        'i18n_selection_too_long_1' => _x( 'You can only select 1 item', 'geodir select', 'geodirectory' ),
        'i18n_selection_too_long_n' => _x( 'You can only select %item% items', 'geodir select', 'geodirectory' ),
        'i18n_load_more' => _x( 'Loading more results&hellip;', 'geodir select', 'geodirectory' ),
        'i18n_searching' => _x( 'Searching&hellip;', 'geodir select', 'geodirectory' )	,
        'txt_choose_image' => __( 'Choose an image', 'geodirectory' ),
        'txt_use_image' => __( 'Use image', 'geodirectory' ),
        'img_spacer' => admin_url( 'images/media-button-image.gif' ),
        'txt_post_review' => __('Post Review', 'geodirectory'),
        'txt_post_reply' => __('Post reply', 'geodirectory'),
        'txt_leave_a_review' => __('Leave a Review', 'geodirectory'),
        'txt_leave_a_reply' => __('Leave a reply', 'geodirectory'),
        'txt_reply_text' => __('Reply text', 'geodirectory'),
        'txt_review_text' => __('Review text', 'geodirectory'),
        'txt_read_more' => __('Read more', 'geodirectory'),
        'txt_open_now' => __('Open now', 'geodirectory'),
		'txt_closed_now' => __('Closed now', 'geodirectory'),
		'txt_closed_today' => __('Today closed', 'geodirectory'),
        'txt_closed' => __('Closed', 'geodirectory'),
        'txt_single_use' => __("This field is single use only and is already being used.", 'geodirectory'),
        'txt_page_settings' => __("Page selections should not be the same, please correct the issue to continue.", 'geodirectory'),
        'txt_save_other_setting' => __('Please save the current setting before adding a new one.', 'geodirectory'),
		'gmt_offset' => geodir_gmt_offset(),
		'search_users_nonce' => wp_create_nonce( 'search-users' ),
		'google_api_key' => geodir_get_map_api_key(),
		'mapLanguage' => geodir_get_map_default_language()
    );

    /**
     * Filters the translated JS strings from function geodir_params().
     *
     * With this filter you can add, remove or change translated JS strings.
     * You should add your own translations to this if you are building an addon rather than adding another script block.
     *
     * @since 1.0.0
     */
    return apply_filters('geodir_params', $arr_alert_msg);
}

/**
 * Define a constant if it is not already defined.
 *
 * @since 2.0.0
 * @param string $name  Constant name.
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
 * @param $user_notes
 *
 * @return string
 */
function geodir_notification( $user_notes ) {
	$notes = '';
	foreach ( $user_notes as $key => $user_note ) {
		$notes .= "<div class='gd-notification $key'>";
		$notes .= $user_note;
		$notes .= "</div>";
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
 * @param  string $data
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
 * @param string $db The table name.
 * @param string $column The column name.
 * @return bool If column exists returns true. Otherwise false.
 */
function geodir_column_exist($db, $column)
{
	global $wpdb;
	$exists = false;
	$columns = $wpdb->get_col("show columns from $db");
	foreach ($columns as $c) {
		if ($c == $column) {
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
	if ( get_option( 'geodirectory_version' )) {
		register_widget( 'GeoDir_Widget_Search' );
		register_widget( 'GeoDir_Widget_Best_Of' );
		register_widget( 'GeoDir_Widget_Categories' );
		register_widget( 'GeoDir_Widget_Category_Description' );
		register_widget( 'GeoDir_Widget_Dashboard' );
		register_widget( 'GeoDir_Widget_Recent_Reviews' );

		// post widgets
		register_widget( 'GeoDir_Widget_Post_Badge' );
		register_widget( 'GeoDir_Widget_Post_Meta' );
		register_widget( 'GeoDir_Widget_Post_Images' );
		register_widget( 'GeoDir_Widget_Post_Title' );
		register_widget( 'GeoDir_Widget_Post_Rating' );
		register_widget( 'GeoDir_Widget_Post_Fav' );
		register_widget( 'GeoDir_Widget_Post_Directions' );

		// Widgets
		register_widget( 'GeoDir_Widget_Output_location' );
		register_widget( 'GeoDir_Widget_Author_Actions' );
		register_widget( 'GeoDir_Widget_Listings' );
		register_widget( 'GeoDir_Widget_Map' );

		// Non Widgets
		new GeoDir_Widget_Add_Listing();
		new GeoDir_Widget_Single_Taxonomies();
		new GeoDir_Widget_Single_Tabs();
		new GeoDir_Widget_Single_Next_Prev();
		new GeoDir_Widget_Single_Closed_Text();
		new GeoDir_Widget_Loop();
		new GeoDir_Widget_Loop_Paging();
		new GeoDir_Widget_Loop_Actions();
		new GeoDir_Widget_Archive_Item_Section();
		new GeoDir_Widget_Post_Distance();

		// 3rd party widgets
		if(class_exists('Ninja_Forms')){
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

	if ( !empty( $gd_widget_pages ) && is_array( $gd_widget_pages ) ) {
		return $gd_widget_pages;
	}

	$gd_widget_pages = array();
	$gd_widget_pages['gd'] = array(
		'label'     => __( 'GD Pages', 'geodirectory' ),
		'pages'     => array(
			'add-listing'       => __( 'Add Listing Page', 'geodirectory' ),
			'author'            => __( 'Author Page', 'geodirectory' ),
			'detail'            => __( 'Listing Detail Page', 'geodirectory' ),
			'preview'           => __( 'Listing Preview Page', 'geodirectory' ),
			'listing-success'   => __( 'Listing Success Page', 'geodirectory' ),
			'location'          => __( 'Location Page', 'geodirectory' ),
			'login'             => __( 'Login Page', 'geodirectory' ),
			'pt'                => __( 'Post Type Archive', 'geodirectory' ),
			'search'            => __( 'Search Page', 'geodirectory' ),
			'listing'           => __( 'Taxonomies Page', 'geodirectory' ),
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
 *      @type string $gd_wgt_showhide Widget display type.
 *      @type string $gd_wgt_restrict Widget restrict pages.
 * }
 * @param object $widget Display widget options.
 * @param array $args Widget arguments.
 * @return bool|array $instance
 */
function geodir_widget_display_callback( $instance, $widget, $args ) {
	if ( !empty( $widget->widget_options['geodirectory'] ) && !empty( $instance['gd_wgt_showhide'] ) ) {
		$display_type = !empty( $instance['gd_wgt_showhide'] ) ? $instance['gd_wgt_showhide'] : '';
		$pages = !empty( $instance['gd_wgt_restrict'] ) && is_array( $instance['gd_wgt_restrict'] ) ? $instance['gd_wgt_restrict'] : array();

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
				$gd_page = '';

				if ( !empty( $gd_widget_pages['gd']['pages'] ) ) {
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