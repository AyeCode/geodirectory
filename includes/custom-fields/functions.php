<?php
/**
 * Custom fields functions
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @global string $table_prefix WordPress Database Table prefix.
 */
global $wpdb, $table_prefix;

/**
 * Check if we are getting demo data for the post meta.
 *
 * @since 2.0.0
 * @return bool
 */
function geodir_is_block_demo(){
    global $post;

    if(isset($post->ID)){$post_id = $post->ID;}else{$post_id = '';}

    // WP Core
    if(empty($_POST['attributes']['id'])
       && isset($_POST['action'])
       && $_POST['action'] == 'super_duper_output_shortcode'
       && wp_doing_ajax()
     //  && ( $post_id == geodir_details_page_id() || $post_id == geodir_archive_item_page_id() )
    ){
        return true;
    }elseif(
        isset($_POST['fl_builder_data']['fl_action'])
        && $_POST['fl_builder_data']['fl_action']=='save_settings'
        && isset($_POST['fl_builder_data']['post_id'])
       // && ( $_POST['fl_builder_data']['post_id'] == geodir_details_page_id() || $_POST['fl_builder_data']['post_id'] == geodir_archive_item_page_id() )
    ){
        return true;
    }elseif(
        is_page( geodir_details_page_id() ) ||  is_page( geodir_archive_item_page_id() )
    ) {
        return true;
    }else{
        return false;
    }
}


/**
 * Returns custom fields based on page type. (detail page, listing page).
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @param int|string $package_id The package ID.
 * @param string $default Optional. When set to "default" it will display only default fields.
 * @param string $post_type Optional. The wordpress post type.
 * @param string $fields_location Optional. Where exactly are you going to place this custom fields?.
 * @return array|mixed|void Returns custom fields.
 */
function geodir_post_custom_fields( $package_id = '', $default = 'all', $post_type = 'gd_place', $fields_location = 'none' ) {
    global $wpdb;

    $cache_stored = $post_type . '_' . $package_id . '_' . $default . '_' . $fields_location;
    $custom_fields = array();
    $geodir_post_custom_fields_cache = get_transient( 'geodir_post_custom_fields' );

    if ( ! is_array( $geodir_post_custom_fields_cache ) ) {
        $geodir_post_custom_fields_cache = array();
    }

    if ( !empty( $geodir_post_custom_fields_cache ) && array_key_exists( $cache_stored, $geodir_post_custom_fields_cache ) ) {
        $custom_fields = $geodir_post_custom_fields_cache[ $cache_stored ];
    }else{

        $where = array( $wpdb->prepare( "is_active = %d", 1 ) );
        if ( $post_type != 'all' ) {
            $where[] = $wpdb->prepare( "post_type = %s", $post_type );
        }
        if ( $fields_location != 'none' ) {
            $where[] = "show_in LIKE '%%[" . esc_sql( $fields_location ) . "]%%'";
        }
        if ( $default == 'default' ) {
            $where[] = $wpdb->prepare( "is_admin = %d", 1 );
        } else if ( $default == 'custom' ) {
            $where[] = $wpdb->prepare( "is_admin = %d", 0 );
        }

        $where = ! empty( $where ) ? "WHERE " . implode( " AND ", $where ) : '';

        $where = apply_filters( 'geodir_post_custom_fields_query_where', $where, $post_type, $package_id, $default, $fields_location );

        $fields = $wpdb->get_results( "SELECT * FROM " . GEODIR_CUSTOM_FIELDS_TABLE . " {$where} ORDER BY sort_order ASC, admin_title ASC" );


        if ( $fields ) {
            foreach ( $fields as $key => $field ) {
                $skip = apply_filters( 'geodir_post_custom_fields_skip_field', false, $field, $package_id, $default, $fields_location );
                $skip = apply_filters( 'geodir_post_custom_fields_skip_field_' . $field->htmlvar_name, $skip, $field, $package_id, $default, $fields_location );

                if ( $skip ) {
                    continue;
                }

                $custom_field = array(
                    "name" => $field->htmlvar_name,
                    "label" => $field->clabels,
                    "default" => $field->default_value,
                    "type" => $field->field_type,
                    "desc" => $field->frontend_desc,
                    "post_type" => $field->post_type,
                );

                if ( $field->field_type ) {
                    $options = explode( ',', $field->option_values );
                    $custom_field["options"] = $options;
                }

                foreach ( $field as $field_key => $val ) {
                    $custom_field[ $field_key ] = $val;
                }

                $custom_fields[ $field->sort_order . $field->post_type . $field->htmlvar_name ] = $custom_field;
            }
        }

        $geodir_post_custom_fields_cache[ $cache_stored ] = $custom_fields;
        set_transient('geodir_post_custom_fields',$geodir_post_custom_fields_cache, DAY_IN_SECONDS);

    }


    if ( has_filter('geodir_filter_geodir_post_custom_fields' ) ) {
        /**
         * Filter the post custom fields array.
         *
         * @since 1.0.0
         *
         * @param array $custom_fields Post custom fields array.
         * @param int|string $package_id The package ID.
         * @param string $post_type Optional. The wordpress post type.
         * @param string $fields_location Optional. Where exactly are you going to place this custom fields?.
         */
        $custom_fields = apply_filters( 'geodir_filter_geodir_post_custom_fields', $custom_fields, $package_id, $post_type, $fields_location );
    }


    return $custom_fields;
}


/**
 * Get the value of a custom field for a current post.
 *
 * @param $cf
 *
 * @return mixed|void
 */
function geodir_get_cf_value( $cf ) {
	global $post, $gd_post;

	$value = '';
	if ( is_admin() ) {
		global $post;

		if ( isset( $_REQUEST['post'] ) ) {
			$_REQUEST['pid'] = (int)$_REQUEST['post'];
		}

		// Clear cache to prevent mis-match when loaded from cache.
		if ( ! empty( $gd_post ) && ! empty( $post ) && (int) $gd_post->ID != (int) $post->ID ) {
			$gd_post = array();
		}
	} elseif ( ! empty( $gd_post ) ) {

	}

	if ( empty( $gd_post ) && ! empty( $post ) ) {
		$gd_post = geodir_get_post_info( $post->ID );
	}

	// check if post content
	if ( $cf['name'] == 'post_content' ) {
		$value = get_post_field( 'post_content', $gd_post->ID );
	} elseif ( $cf['name'] == 'post_date' ) {
		$value = get_post_field( 'post_date', $gd_post->ID );
	} elseif ( $cf['name'] == 'address' ) {
		$value = geodir_get_post_meta( $gd_post->ID, 'street', true );
	} else {
		$value = geodir_get_post_meta( $gd_post->ID, $cf['name'], true );
	}

	// Set defaults
	if ( ( $value == '' || $cf['type'] == 'checkbox' ) && ! empty( $gd_post ) && $gd_post->post_status == 'auto-draft' ) {
		$value = $cf['default'];
	}

	// Blank title for auto drafts
	if ( $cf['name'] == 'post_title' && $value == __( "Auto Draft" ) ) { // no text domain used here on purpose as we are matching a core WP text.
		$value = "";
	}

	/**
	 * Filter the custom field value.
	 *
	 * @since 1.6.20
	 *
	 * @param mixed $value Custom field value.
	 * @param array $cf Custom field info.
	 */
	return apply_filters( 'geodir_get_cf_value', $value, $cf );
}

/**
 * Get the value of a default category field for a current post.
 *
 * @return mixed|void
 */
function geodir_get_cf_default_category_value() {
	global $post, $gd_post;

	$value = '';
	if ( is_admin() ) {
		global $post;

		if ( isset( $_REQUEST['post'] ) ) {
			$_REQUEST['pid'] = (int)$_REQUEST['post'];
		}

		// Clear cache to prevent mis-match when loaded from cache.
		if ( ! empty( $gd_post ) && ! empty( $post ) && (int) $gd_post->ID != (int) $post->ID ) {
			$gd_post = array();
		}
	} elseif ( ! empty( $gd_post ) ) {

	}

	if ( empty( $gd_post ) && ! empty( $post ) ) {
		$gd_post = geodir_get_post_info( $post->ID );
	}

	$value = geodir_get_post_meta( (int) $gd_post->ID, 'default_category', true );

	/**
	 * Filter the default category field value.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed $value Custom field value.
	 */
	return apply_filters( 'geodir_get_cf_default_category_value', $value );
}

/**
 * Displays custom fields html.
 *
 * @since 1.0.0
 * @since 1.5.2 Added TERRAIN map type.
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @global object $post The current post object.
 * @global array $geodir_addon_list List of active GeoDirectory extensions.
 *
 * @param int|string $package_id The package ID.
 * @param string $default Optional. When set to "default" it will display only default fields.
 * @param string $post_type Optional. The wordpress post type.
 */
function geodir_get_custom_fields_html($package_id = '', $default = 'custom', $post_type = 'gd_place') {
    global $is_default, $mapzoom;

    $listing_type = $post_type;

    $custom_fields = geodir_post_custom_fields($package_id, $default, $post_type);

    foreach ($custom_fields as $key => $val) {
        if(isset($val['extra_fields'])){$extra_fields = $val['extra_fields'];}
        $val = stripslashes_deep($val); // strip slashes from labels
        if(isset($val['extra_fields'])){$val['extra_fields'] = $extra_fields;}

        $name = $val['name'];
        $type = $val['type'];
        $is_default = $val['is_default'];

        /* field available to site admin only for edit */
        $for_admin_use = isset( $val['for_admin_use'] ) && (int) $val['for_admin_use'] == 1 ? true : false;
        $is_hidden = ( $for_admin_use && ! current_user_can( 'manage_options' ) ) ? true : false;

        /**
         * Add listing form filter to hide post custom field.
         *
         * @since 2.1.0.11
         *
         * @param bool $is_hidden True to hide field.
         * @param array $val Custom field array.
         * @param int|string $package_id The package ID.
         * @param string $default Optional. When set to "default" it will display only default fields.
         */
        $is_hidden = apply_filters( 'geodir_add_listing_custom_field_is_hidden', $is_hidden, $val, $package_id, $default );

        if ( $is_hidden ) {
            continue;
        }

        if (is_admin()) {
            global $post;

            if (isset($_REQUEST['post']))
                $_REQUEST['pid'] = $_REQUEST['post'];
        }

        /**
         * Called before the custom fields info is output for submitting a post.
         *
         * Used dynamic hook type geodir_before_custom_form_field_$name.
         *
         * @since 1.0.0
         * @param string $listing_type The post post type.
         * @param int $package_id The price package ID for the post.
         * @param array $val The settings array for the field. {@see geodir_custom_field_save()}.
         * @see 'geodir_after_custom_form_field_$name'
         */
        do_action('geodir_before_custom_form_field_' . $name, $listing_type, $package_id, $val);


        $custom_field = $val;
        $html ='';
        /**
         * Filter the output for custom fields.
         *
         * Here we can remove or add new functions depending on the field type.
         *
         * @param string $html The html to be filtered (blank).
         * @param array $custom_field The custom field array values.
         */
        echo apply_filters("geodir_custom_field_input_{$type}",$html,$custom_field);



        /**
         * Called after the custom fields info is output for submitting a post.
         *
         * Used dynamic hook type geodir_after_custom_form_field_$name.
         *
         * @since 1.0.0
         * @param string $listing_type The post post type.
         * @param int $package_id The price package ID for the post.
         * @param array $val The settings array for the field. {@see geodir_custom_field_save()}.
         * @see 'geodir_before_custom_form_field_$name'
         */
        do_action('geodir_after_custom_form_field_' . $name, $listing_type, $package_id, $val);

    }

}

if ( ! function_exists( 'geodir_get_field_infoby' ) ) {
	/**
	 * Get custom field using key and value.
	 *
	 * @since 1.0.0
	 * @since 2.0.0 Returns array instead of object.
	 * @package GeoDirectory
	 * @global object $wpdb WordPress Database object.
	 * @param string $key The key you want to look for.
	 * @param string $value The value of the key you want to look for.
	 * @param string $post_type The post type.
	 * @param bool $stripslashes Return with stripslashes. Default True.
	 * @return bool|mixed Returns field info when available. otherwise returns false.
	 */
	function geodir_get_field_infoby( $key = '', $value = '', $post_type = '', $stripslashes = true ) {
		global $wpdb, $geodir_field_infoby;

		if ( empty( $geodir_field_infoby ) ) {
			$geodir_field_infoby = array();
		}

		$cache_key = $key . '::' . $value . '::' . $post_type . '::' . (int) $stripslashes;

		// Return cached field data.
		if ( isset( $geodir_field_infoby[ $cache_key ] ) ) {
			return $geodir_field_infoby[ $cache_key ];
		}

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . GEODIR_CUSTOM_FIELDS_TABLE . " WHERE post_type=%s AND " . $key . "='" . $value . "'", array( $post_type ) ), ARRAY_A );

		if ( ! empty( $row ) ) {
			$row = $stripslashes ? stripslashes_deep( $row ) : $row;
		} else {
			$row = false;
		}

		// Cache the field data.
		$geodir_field_infoby[ $cache_key ] = $row;

		return $row;
	}
}

/**
 * Process the field icon to html.
 *
 * @param $cf
 *
 * @return string
 */
function geodir_field_icon_proccess($cf){


    if (strpos($cf['field_icon'], 'http') !== false) {
        $field_icon = ' background: url(' . $cf['field_icon'] . ') no-repeat left center;background-size:18px 18px;padding-left: 21px;';
    } elseif (strpos($cf['field_icon'], 'fas fa-') !== false || strpos($cf['field_icon'], 'fa-') !== false) {
        $design_style = geodir_design_style();
        $field_icon = $design_style ? '<i class="' . $cf['field_icon'] . ' fa-fw" aria-hidden="true"></i> ' : '<i class="' . $cf['field_icon'] . '" aria-hidden="true"></i>';
    }else{
        $field_icon = $cf['field_icon'];
    }

    return $field_icon;
}

/**
 * Process the field output string to a reversed array.
 *
 * @param $output
 *
 * @return array
 */
function geodir_field_output_process($output){

    if(!empty($output) && !is_array($output)){
        $output = array_flip(explode("-",$output)); // for speed

        // check for values
        if(!empty($output)){
            foreach($output as $key=>$val){
                $parts = explode("::",$key);
                if(!empty($parts[1])){
                    unset($output[$key]);
                    $output[$parts[0]] = $parts[1];
                }else{
                    $output[$key] = '';
                }
            }
        }
    }
    return $output;
}

if (!function_exists('geodir_show_listing_info')) {
    /**
     * Show listing info depending on field location.
     *
     * @since 1.0.0
     * @since 1.5.7 Custom fields option values added to db translation.
     *              Changes to display url fields title.
     * @package GeoDirectory
     * @global object $wpdb WordPress Database object.
     * @global object $post The current post object.
     *
     * @param string $fields_location In which page you are going to place this custom fields?. Ex: listing, detail etc.
     * @return string Returns listing info html.
     */
    function geodir_show_listing_info($fields_location = '') {
        global $post, $preview, $wpdb;

        $output_arr = array();

        $p_type = !empty($post->post_type) ? $post->post_type : geodir_get_current_posttype();
		$package_id = geodir_get_post_package_id( $post, $p_type );

        $fields_info = geodir_post_custom_fields($package_id, 'all', $p_type, $fields_location);

        if (!empty($fields_info)) {
            $post = stripslashes_deep($post); // strip slashes

            global $field_set_start;
            $field_set_start = 0;


            foreach ($fields_info as $type) {
                if ( isset( $type['extra_fields'] ) ) {
                    $extra_fields = $type['extra_fields'];
                }
                $type = stripslashes_deep( $type ); // strip slashes
                if ( isset( $type['extra_fields'] ) ) {
                    $type['extra_fields'] = $extra_fields;
                }
                $id = ! empty( $type['id'] ) ? absint( $type['id'] ) : '';
                $html = '';
                $field_icon  = geodir_field_icon_proccess( $type );
                $field_type  = $type['type'];
                $is_fieldset = $field_type == 'fieldset' ? true : false;
                $html_var    = isset( $type['htmlvar_name'] ) ? $type['htmlvar_name'] : '';
                if ( $html_var == 'post' ) {
                    $html_var = 'post_address';
                }

                /**
                 * Filter the output for custom fields.
                 *
                 * Here we can remove or add new functions depending on the field type.
                 *
                 * @param string $html The html to be filtered (blank).
                 * @param string $fields_location The location the field is to be show.
                 * @param array $type The array of field values.
                 */
                $html = apply_filters( "geodir_custom_field_output_{$field_type}", $html, $fields_location, $type );

                $variables_array = array();


                if ( $type['type'] != 'fieldset' ):
                    $variables_array['post_id'] = ! empty( $post->ID ) ? $post->ID : ( ! empty( $post->pid ) ? $post->pid : null );
                    $variables_array['label']   = __( $type['frontend_title'], 'geodirectory' );
                    $variables_array['value']   = '';
                    if ( isset( $post->{$type['htmlvar_name']} ) ) {
                        $variables_array['value'] = $post->{$type['htmlvar_name']};
                    }
                endif;


                ob_start();

                if ( $html ) {


                    /**
                     * Called before a custom fields is output on the frontend.
                     *
                     * @since 1.0.0
                     *
                     * @param string $html_var The HTML variable name for the field.
                     */
                    do_action( "geodir_before_show_{$html_var}" );
                    /**
                     * Filter custom field output.
                     *
                     * @since 1.0.0
                     *
                     * @param string $html_var The HTML variable name for the field.
                     * @param string $html Custom field unfiltered HTML.
                     * @param array $variables_array Custom field variables array.
                     */
                    if ( $html ) {
                        echo apply_filters( "geodir_show_{$html_var}", $html, $variables_array );
                    }

                    /**
                     * Called after a custom fields is output on the frontend.
                     *
                     * @since 1.0.0
                     *
                     * @param string $html_var The HTML variable name for the field.
                     */
                    do_action( "geodir_after_show_{$html_var}" );


                }

                $arr_key = empty( $type['tab_level'] ) ? "parent-$id" : "child-" . absint( $type['tab_parent'] );
                if ( $is_fieldset ) {
                    $arr_key = "fieldset-$id";
                }

                if(isset($output_arr[ $arr_key ])){
                    $output_arr[ $arr_key ] .= ob_get_clean();
                }else{
                    $output_arr[ $arr_key ] = ob_get_clean();
                }

            }


        }

        $html = ''; // we need to rest the html var

        // loop the output_arr
        if(!empty($output_arr)){
            foreach($output_arr as $key => $output){

                // only output a fieldset if it has child elements
               if(substr( $key, 0, 1 ) === "f"){
                   $parts = explode("-",$key);
                   $id = !empty($parts[1]) ? absint($parts[1]) : '';
                   if(!isset($output_arr["child-$id"]) || !empty($output_arr["child-$id"])){
                       $html .= $output;
                   }
               }else{
                   $html .= $output;
               }
            }
        }

        /**
         * Filter the custom fields over all output.
         *
         * @param string $html The html of the custom fields.
         * @param string $fields_location The location the fields are being output.
         * @since 1.6.9
         */
        return apply_filters('geodir_show_listing_info',$html,$fields_location);

    }
}

add_filter('upload_mimes', 'geodir_custom_upload_mimes');
/**
 * Returns list of supported mime types for upload handling.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @param array $existing_mimes List of existing mime types.
 * @return array Returns list of supported mime types.
 */
function geodir_custom_upload_mimes( $existing_mimes = array() ) {
	$existing_mimes['wif'] = 'text/plain';
	$existing_mimes['jpg|jpeg'] = 'image/jpeg';
	$existing_mimes['gif'] = 'image/gif';
	$existing_mimes['png'] = 'image/png';
	$existing_mimes['pdf'] = 'application/pdf';
	$existing_mimes['txt'] = 'text/text';
	$existing_mimes['csv'] = 'text/csv';
	$existing_mimes['doc'] = 'application/msword';
	$existing_mimes['xla|xls|xlt|xlw'] = 'application/vnd.ms-excel';
	$existing_mimes['docx'] = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
	$existing_mimes['xlsx'] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
	$existing_mimes['webp'] = 'image/webp';
	$existing_mimes['avif'] = 'image/avif';
	return $existing_mimes;
}
add_filter('geodir_add_custom_sort_options', 'geodir_add_custom_sort_options', 0, 2);

/**
 * Add custom sort options to the existing fields.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @param array $fields The sorting fields array
 * @param string $post_type The post type.
 * @return array Returns the fields.
 */
function geodir_add_custom_sort_options( $fields, $post_type ) {
    global $wpdb;

    if ($post_type != '') {
        $all_postypes = geodir_get_posttypes();

        if ( in_array( $post_type, $all_postypes ) ) {
            $custom_fields = $wpdb->get_results(
                $wpdb->prepare(
                    "select post_type,data_type,field_type,frontend_title,htmlvar_name,field_icon from " . GEODIR_CUSTOM_FIELDS_TABLE . " where post_type = %s and is_active='1' and cat_sort='1' AND field_type != 'address' order by sort_order asc",
                    array($post_type)
                ), 'ARRAY_A'
            );

            if ( ! empty( $custom_fields ) ) {
                foreach ( $custom_fields as $val ) {
                    $fields[ esc_attr( $val['htmlvar_name'] ) ] = $val;
                }
            }

            if ( GeoDir_Post_types::supports( $post_type, 'location' ) ) {
                $fields['street'] = array(
                    'post_type'      => $post_type,
                    'data_type'      => '',
                    'field_type'     => 'text',
                    'frontend_title' => __( 'Address', 'geodirectory' ),
                    'htmlvar_name'   => 'street',
                    'field_icon'     => 'fas fa-map-marker-alt',
                    'description'    => __( 'Sort by address.', 'geodirectory' )
                );

                $fields['zip'] = array(
                    'post_type'      => $post_type,
                    'data_type'      => '',
                    'field_type'     => 'text',
                    'frontend_title' => __( 'Zip/Post Code', 'geodirectory' ),
                    'htmlvar_name'   => 'zip',
                    'field_icon'     => 'fas fa-map-marker-alt',
                    'description'    => __( 'Sort by zip/post code.', 'geodirectory' )
                );
            }
        }
    }

    return $fields;
}

/**
 * Set custom sort field order.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @param array $field_ids List of field ids.
 * @return array|bool Returns field ids. If no field id, returns false.
 */
function godir_set_sort_field_order($field_ids = array())
{

    global $wpdb;

    $count = 0;
    if (!empty($field_ids)):
        foreach ($field_ids as $id) {

            $cf = trim($id, '_');

            $post_meta_info = $wpdb->query(
                $wpdb->prepare(
                    "update " . GEODIR_CUSTOM_SORT_FIELDS_TABLE . " set
															sort_order=%d
															where id= %d",
                    array($count, $cf)
                )
            );
            $count++;
        }

        return $field_ids;
    else:
        return false;
    endif;
}





if (!function_exists('geodir_custom_sort_field_adminhtml')) {
    /**
     * Custom sort field admin html.
     *
     * @since 1.0.0
     * @package GeoDirectory
     * @global object $wpdb WordPress Database object.
     * @param string $field_type The form field type.
     * @param object|int $result_str The custom field results object or row id.
     * @param string $field_ins_upd When set to "submit" displays form.
     * @param bool $default when set to true field will be for admin use only.
     */
    function geodir_custom_sort_field_adminhtml($field_type, $result_str, $field_ins_upd = '', $field_type_key='')
    {


    }
}

if (!function_exists('geodir_check_field_visibility')) {
    /**
     * Check field visibility as per price package.
     *
     * @since 1.0.0
     * @package GeoDirectory
     * @global object $wpdb WordPress Database object.
     * @global array $geodir_addon_list List of active GeoDirectory extensions.
     * @param int|string $package_id The package ID.
     * @param string $field_name The field name.
     * @param string $post_type Optional. The wordpress post type.
     * @return bool Returns true when field visible, otherwise false.
     */
    function geodir_check_field_visibility( $package_id, $field_name, $post_type ) {
        $show = true;

		return apply_filters( 'geodir_check_field_visibility', $show, $field_name, $package_id, $post_type );
    }
}

/**
 * Parse label & values from string.
 *
 * @since 1.0.0
 * @since 1.5.7 New parameter $translated added.
 *
 * @param string $string The string input.
 * @param bool   $translated True if label needs to be translated.
 * @return array Returns option array.
 */
function geodir_string_to_options( $string = '', $translated = false ) {
	$options = array();

	if ( $string == '' ) {
		return NULL;
	}

	if ( is_array( $string ) ) {
		return $string;
	} else if ( ! is_scalar( $string ) ) {
		return $options;
	}

	// Check for old option values.
	if ( geodir_is_old_option_values( $string ) ) {
		return geodir_old_string_values_to_options( $string, $translated );
	}

	$lines = explode( "\n", $string );
	$optgroup_open = false;

	foreach ( $lines as $line ) {
		$line = trim( $line );

		if ( $line === "" ) {
			continue;
		}

		$line = stripslashes( $line );

		if ( strpos( $line, " : " ) !== false ) {
			$line_exp = explode( " : ", $line, 2 );

			$value = trim( $line_exp[0] );
			$label = trim( $line_exp[1] );
		} else if ( strpos( $line, ": " ) === 0 ) {
			$line_exp = explode( ": ", $line, 2 );

			$value = trim( $line_exp[0] );
			$label = trim( $line_exp[1] );
		} else {
			$value = $line;
			$label = $line;
		}

		$option = array();

		if ( $value === "optgroup" ) {
			if ( $optgroup_open ) {
				$options[] = array( 'label' => '', 'value' => '', 'optgroup' => 'end' );
				$optgroup_open = false; // Close optgroup
			}

			$option = array( 'label' => $label, 'value' => '', 'optgroup' => 'start' );
			$optgroup_open = true;
		} else if ( $value === "optgroup-close" ) {
			if ( ! $optgroup_open ) {
				continue;
			}

			$option = array( 'label' => '', 'value' => '', 'optgroup' => 'end' );
			$optgroup_open = false; // Close optgroup
		} else {
			if ( $label === "" && $value !== "" ) {
				$label = $value;
			}

			if ( $label !== "" ) {
				$option = array( 'label' => $label, 'value' => $value, 'optgroup' => NULL );
			}
		}

		if ( ! empty( $option ) ) {
			if ( $translated && isset( $option['label'] ) && $option['label'] !== "" ) {
				$option['label']  = __( $option['label'], 'geodirectory' );
			}

			$options[] = $option;
		}
	}

	// Close if optgroup is still open.
	if ( ! empty( $options ) && $optgroup_open ) {
		$options[] = array( 'label' => '', 'value' => '', 'optgroup' => 'end' );
	}

	return $options;
}

/**
 * Parse option values string to array.
 *
 * @since 1.0.0
 *
 * @param string $option_values The option values.
 * @param bool   $translated True if label needs to be translated.
 * @return array Options array.
 */
function geodir_string_values_to_options( $option_values = '', $translated = false ) {
	return geodir_string_to_options( $option_values, $translated );
}

/**
 * Check option values are old format.
 *
 * @since 2.3.19.
 *
 * @param string $string The option values.
 * @return bool True if options are in old format.
 */
function geodir_is_old_option_values( $string ) {
	if ( empty( $string ) ) {
		return false;
	}

	if ( is_scalar( $string ) ) {
		if ( strpos( $string, "\n" ) !== false || strpos( $string, PHP_EOL ) !== false ) {
			return false;
		}

		if ( strpos( $string, " : " ) !== false && strpos( $string, "," ) === false ) {
			return false;
		}
	}

	return true;
}

/**
 * Converts old string to options array.
 *
 * @since 2.3.19
 *
 * @param string $input The string input.
 * @param bool $translated True if label needs to be translated.
 * @return array Returns option array.
 */
function geodir_old_string_to_options( $input = '', $translated = false ) {
	$return = array();

	if ( $input != '' ) {
		$input = trim( $input );
		$input = rtrim( $input, "," );
		$input = ltrim( $input, "," );
		$input = trim( $input );
	}

	$input_arr = explode( ',', $input );

	if ( ! empty( $input_arr ) ) {
		foreach ( $input_arr as $input_str ) {
			$input_str = trim( stripslashes( $input_str ) );

			if ( strpos( $input_str, "/" ) !== false ) {
				$input_str = explode( "/", $input_str, 2 );
				$label = trim( $input_str[0] );
				if ( $translated && $label != '' ) {
					$label = __( $label, 'geodirectory' );
				}
				$value = trim( $input_str[1] );
			} else {
				$value = $input_str;
				if ( $translated && $input_str != '' ) {
					$input_str = __( $input_str, 'geodirectory' );
				}
				$label = $input_str;
			}

			if ( $label != '' ) {
				$return[] = array( 'label' => $label, 'value' => $value, 'optgroup' => NULL );
			}
		}
	}

	return $return;
}

/**
 * Converts old option values string to options array.
 *
 * @since 2.3.19
 *
 * @param string $option_values The option values.
 * @param bool   $translated True if label needs to be translated.
 * @return array Returns option array.
 */
function geodir_old_string_values_to_options( $option_values = '', $translated = false ) {
	$options = array();
	if ( $option_values == '' ) {
		return NULL;
	}

	if ( strpos( $option_values, "{/optgroup}" ) !== false ) {
		$option_values_arr = explode( "{/optgroup}", $option_values );

		foreach ( $option_values_arr as $optgroup ) {
			if ( strpos( $optgroup, "{optgroup}" ) !== false ) {
				$optgroup_arr = explode( "{optgroup}", $optgroup );

				$count = 0;
				foreach ( $optgroup_arr as $optgroup_str ) {
					$count++;
					$optgroup_str = trim( $optgroup_str );

					$optgroup_label = '';
					if ( strpos( $optgroup_str, "|" ) !== false ) {
						$optgroup_str_arr = explode( "|", $optgroup_str, 2 );
						$optgroup_label = trim( $optgroup_str_arr[0] );
						if ( $translated && $optgroup_label != '' ) {
							$optgroup_label = __( $optgroup_label, 'geodirectory' );
						}
						$optgroup_str = $optgroup_str_arr[1];
					}

					$optgroup3 = geodir_old_string_to_options( $optgroup_str, $translated );

					if ( $count > 1 && $optgroup_label != '' && !empty( $optgroup3 ) ) {
						$optgroup_start = array( array( 'label' => $optgroup_label, 'value' => NULL, 'optgroup' => 'start' ) );
						$optgroup_end = array( array( 'label' => $optgroup_label, 'value' => NULL, 'optgroup' => 'end' ) );
						$optgroup3 = array_merge( $optgroup_start, $optgroup3, $optgroup_end );
					}
					$options = array_merge( $options, $optgroup3 );
				}
			} else {
				$optgroup1 = geodir_old_string_to_options( $optgroup, $translated );
				$options = array_merge( $options, $optgroup1 );
			}
		}
	} else {
		$options = geodir_old_string_to_options( $option_values, $translated );
	}

	return $options;
}

/**
 * Converts old option values to new option values.
 *
 * @since 2.3.19.
 *
 * @param string $option_values The option values.
 * @return array Returns option array.
 */
function geodir_convert_old_option_values( $option_values = '' ) {
	if ( ! geodir_is_old_option_values ( $option_values ) ) {
		return $option_values;
	}

	$options = array();

	if ( strpos( $option_values, "{/optgroup}") !== false ) {
		$option_values_arr = explode( "{/optgroup}", $option_values );

		foreach ( $option_values_arr as $optgroup ) {
			if ( strpos( $optgroup, "{optgroup}") !== false ) {
				$optgroup_arr = explode( "{optgroup}", $optgroup );

				$count = 0;
				foreach ( $optgroup_arr as $optgroup_str ) {
					$count++;
					$optgroup_str = trim( $optgroup_str );

					$optgroup_label = '';
					if ( strpos( $optgroup_str, "|" ) !== false ) {
						$optgroup_str_arr = explode( "|", $optgroup_str, 2 );
						$optgroup_label = trim( $optgroup_str_arr[0] );
						$optgroup_str = $optgroup_str_arr[1];
					}

					$optgroup3 = array( geodir_convert_old_string_options( $optgroup_str ) );

					if ( $count > 1 && $optgroup_label != '' && ! empty( $optgroup3 ) ) {
						$optgroup_start = array( 'optgroup : ' . $optgroup_label );
						$optgroup_end = array( 'optgroup-close' );
						$optgroup3 = array_merge( $optgroup_start, $optgroup3, $optgroup_end );
					}
					$options = array_merge( $options, $optgroup3 );
				}
			} else {
				$optgroup1 = array( geodir_convert_old_string_options( $optgroup ) );
				$options = array_merge( $options, $optgroup1 );
			}
		}
	} else {
		$options = array( geodir_convert_old_string_options( $option_values ) );
	}

	if ( ! empty( $options ) ) {
		$options = implode( "\n", array_filter( $options ) );
	} else {
		$options = "";
	}

	return $options;
}

/**
 * Converts old option to new option.
 *
 * @since 2.3.19
 *
 * @param string $input The string input.
 * @return string Returns option string.
 */
function geodir_convert_old_string_options( $input = '' ) {
	if ( $input != '' ) {
		$input = trim( $input );
		$input = rtrim( $input, "," );
		$input = ltrim( $input, "," );
		$input = trim( $input );
	}

	$input_arr = explode( ',', $input );

	if ( ! empty( $input_arr ) ) {
		foreach ( $input_arr as $input_str ) {
			$input_str = trim( stripslashes( $input_str ) );

			if ( strpos( $input_str, "/" ) !== false ) {
				$input_str = explode( "/", $input_str, 2 );

				$label = trim( $input_str[0] );
				$value = trim( $input_str[1] );
			} else {
				$value = $input_str;
				$label = '';
			}

			if ( $value !== "" || $label !== "" ) {
				$return[] = $value . ( $label !== "" ? " : " . $label : "" );
			}
		}
	}

	$options = ! empty( $return ) ? implode( PHP_EOL, $return ) : "";

	return $options;
}

/**
 * Get currency number format.
 *
 * @since 2.0.0
 *
 * @param string $number Optional. Currency number. Default null.
 * @param string $cf Optional. Custom fields. Default null.
 * @return string $number.
 */
function geodir_currency_format_number( $number = '', $cf = '' ) {
	$extra_fields = isset( $cf['extra_fields'] ) ? maybe_unserialize( $cf['extra_fields'] ) : '';

	$symbol = isset( $extra_fields['currency_symbol'] ) ? $extra_fields['currency_symbol'] : '$';
	$decimals = isset( $cf['decimal_point'] ) && $cf['decimal_point'] ? $cf['decimal_point'] : 2;
	$decimal_display = ! empty( $cf['decimal_display'] ) ? $cf['decimal_display'] : ( ! empty( $extra_fields['decimal_display'] ) ? $extra_fields['decimal_display'] : 'if' );
	$decimalpoint = '.';

	if ( isset( $extra_fields['decimal_separator'] ) && $extra_fields['decimal_separator'] == 'comma' ) {
		$decimalpoint = ',';
	}

	$separator = ',';

	if ( isset( $extra_fields['thousand_separator'] ) ) {
		if ( $extra_fields['thousand_separator'] == 'comma' ) {
			$separator = ',';
		}
		if ( $extra_fields['thousand_separator'] == 'slash' ) {
			$separator = '\\';
		}
		if ( $extra_fields['thousand_separator'] == 'period' ) {
			$separator = '.';
		}
		if ( $extra_fields['thousand_separator'] == 'space' ) {
			$separator = ' ';
		}
		if ( $extra_fields['thousand_separator'] == 'none' ) {
			$separator = '';
		}
	}

	$currency_symbol_placement = isset( $extra_fields['currency_symbol_placement'] ) ? $extra_fields['currency_symbol_placement'] : 'left';

	if ( $decimals > 0 && $decimal_display == 'if' ) {
		if ( is_int( $number ) || floor( $number ) == $number ) {
			$decimals = 0;
		}
	}

	$number = number_format( $number, $decimals, $decimalpoint, $separator );

	if ( $currency_symbol_placement == 'left' ) {
		$number = $symbol . $number;
	} else {
		$number = $number . $symbol;
	}

	return $number;
}

/**
 * Get custom field number format.
 *
 * @since 2.1.0.11
 *
 * @param string $number Optional. Number. Default null.
 * @param string $cf Optional. Custom fields. Default null.
 * @return string $number.
 */
function geodir_cf_format_number( $number = '', $cf = '' ) {
	$extra_fields = isset( $cf['extra_fields'] ) ? maybe_unserialize( $cf['extra_fields'] ) : '';
	$thousand_separator = isset( $extra_fields['thousand_separator'] ) ? $extra_fields['thousand_separator'] : '';

	if ( $thousand_separator == 'comma' ) {
		$separator = ',';
	} else if ( $thousand_separator == 'slash' ) {
		$separator = '\\';
	} else if ( $thousand_separator == 'period' ) {
		$separator = '.';
	} else if ( $thousand_separator == 'space' ) {
		$separator = ' ';
	} else {
		$separator = '';
	}

	$number = number_format( geodir_sanitize_float( $number ), 0, '', $separator );

	return $number;
}

/**
 * Get custom field decimal format.
 *
 * @since 2.1.0.11
 *
 * @param string $number Optional. Number. Default null.
 * @param string $cf Optional. Custom fields. Default null.
 * @return string $number.
 */
function geodir_cf_format_decimal( $number = '', $cf = '' ) {
	$extra_fields = isset( $cf['extra_fields'] ) ? maybe_unserialize( $cf['extra_fields'] ) : '';

	$decimals = isset( $cf['decimal_point'] ) ? absint( $cf['decimal_point'] ) : 2;
	$decimal_display = ! empty( $cf['decimal_display'] ) ? $cf['decimal_display'] : ( ! empty( $extra_fields['decimal_display'] ) ? $extra_fields['decimal_display'] : 'if' );
	$decimalpoint = '.';

	if ( isset( $extra_fields['decimal_separator'] ) && $extra_fields['decimal_separator'] == 'comma' ) {
		$decimalpoint = ',';
	}

	$separator = ',';

	if ( isset( $extra_fields['thousand_separator'] ) ) {
		if ( $extra_fields['thousand_separator'] == 'comma' ) {
			$separator = ',';
		}
		if ( $extra_fields['thousand_separator'] == 'slash' ) {
			$separator = '\\';
		}
		if ( $extra_fields['thousand_separator'] == 'period' ) {
			$separator = '.';
		}
		if ( $extra_fields['thousand_separator'] == 'space' ) {
			$separator = ' ';
		}
		if ( $extra_fields['thousand_separator'] == 'none' ) {
			$separator = '';
		}
	}

	if ( $decimals > 0 && $decimal_display == 'if' ) {
		if ( is_int( $number ) || floor( $number ) == $number ) {
			$decimals = 0;
		}
	}

	$number = number_format( $number, $decimals, $decimalpoint, $separator );

	return $number;
}

/**
 * Get conditional field attributes.
 *
 * @since 2.1.1.0
 *
 * @param object $field Field object.
 * @param string $_key Field key. Default empty.
 * @param string $_type Field type. Default empty.
 * @return string Condition fields attributes.
 */
function geodir_conditional_field_attrs( $field, $_key = '', $_type = '' ) {
	$attrs = '';

	if ( ! geodir_design_style() ) {
		return $attrs;
	}

	if ( is_object( $field ) ) {
		$key = ! empty( $field->htmlvar_name ) ? $field->htmlvar_name : '';
		$type = ! empty( $field->field_type ) ? $field->field_type : '';
		$extra_fields = ! empty( $field->extra_fields ) ? $field->extra_fields : '';
	} else if ( is_array( $field ) ) {
		$key = ! empty( $field['htmlvar_name'] ) ? $field['htmlvar_name'] : '';
		$type = ! empty( $field['field_type'] ) ? $field['field_type'] : '';
		$extra_fields = ! empty( $field['extra_fields'] ) ? $field['extra_fields'] : '';
	} else {
		return $attrs;
	}

	if ( $_key ) {
		$key = $_key;
	}

	if ( $_type ) {
		$type = $_type;
	}

	$extra_fields = maybe_unserialize( $extra_fields );

	if ( is_array( $extra_fields ) && ! empty( $extra_fields['cat_display_type'] ) && $key != 'default_category' ) {
		$type = $extra_fields['cat_display_type'];
	}

	$conditions = geodir_parse_field_conditions( $extra_fields );

	return geodir_build_conditional_attrs( $conditions, $key, $type );
}

/**
 * Parse field conditions from extra fields.
 *
 * @since 2.1.1.0
 *
 * @param array|string $extra_fields Extra fields data.
 * @return array Field conditions.
 */
function geodir_parse_field_conditions( $extra_fields ) {
	if ( ! empty( $extra_fields ) ) {
		$extra_fields = maybe_unserialize( stripslashes_deep( $extra_fields ) );
	}

	$_conditions = ! empty( $extra_fields ) && is_array( $extra_fields ) && ! empty( $extra_fields['conditions'] ) && is_array( $extra_fields['conditions'] ) ? $extra_fields['conditions'] : array();

	$conditions = array();

	if ( ! empty( $_conditions ) ) {
		foreach ( $_conditions as $k => $condition ) {
			if ( ! empty( $condition['action'] ) && ! empty( $condition['field'] ) && ! empty( $condition['condition'] ) ) {
				$conditions[ $k ] = array(
					'action' => stripslashes( $condition['action'] ),
					'field' => stripslashes( $condition['field'] ),
					'condition' => stripslashes( $condition['condition'] ),
					'value' => ( isset( $condition['value'] ) ? stripslashes( $condition['value'] ) : '' )
				);
			}
		}
	}

	return $conditions;
}

/**
 * Prepare conditional fields attributes.
 *
 * @since 2.1.1.0
 *
 * @param array  $conditions Field conditions.
 * @param string $key Field key.
 * @param string $type Field type.
 * @return string Conditional fields attributes.
 */
function geodir_build_conditional_attrs( $conditions, $key = '', $type = '' ) {
	$attrs = 'data-rule-key="' . esc_attr( $key ) . '"';

	if ( $key != '' ) {
		$attrs .= ' data-rule-type="' . esc_attr( $type ) . '"';
	}

	if ( ! empty( $conditions ) ) {
		$attrs .= ' data-has-rule="' . count( $conditions ) . '"';

		foreach ( $conditions as $k => $condition ) {
			$attrs .= ' data-rule-act-' . esc_attr( $k ) . '="' . esc_attr( $condition['action'] ) . '"';
			$attrs .= ' data-rule-fie-' . esc_attr( $k ) . '="' . esc_attr( $condition['field'] ) . '"';
			$attrs .= ' data-rule-con-' . esc_attr( $k ) . '="' . esc_attr( $condition['condition'] ) . '"';
			$attrs .= ' data-rule-val-' . esc_attr( $k ) . '="' . esc_attr( $condition['value'] ) . '"';
		}
	}

	/**
	 * Filter conditional fields attributes.
	 *
	 * @since 2.1.1.0
	 *
	 * @param string $attrs Conditional fields attributes.
	 * @param array  $conditions Field conditions.
	 * @param string $key  Field key.
	 * @param string $type Field type.
	 */
	return apply_filters( 'geodir_build_conditional_attrs', $attrs, $conditions, $key, $type );
}

/**
 * Get conditional field icon.
 *
 * @since 2.1.1.0
 *
 * @param string $attrs Conditional fields attributes.
 * @param array  $field Field array.
 * @return string Icon HTML.
 */
function geodir_conditional_field_icon( $attrs, $field = array() ) {
	if ( ! empty( $attrs ) && strpos( $attrs, 'data-has-rule=' ) !== false ) {
		$icon = '<i class="far fa-eye text-warning c-pointer" data-toggle="tooltip"  title="' . __( 'Has hide conditions', 'geodirectory' ) . '"></i>';
	} else {
		$icon = '';
	}

	/**
	 * Filter conditional field icon.
	 *
	 * @since 2.1.1.0
	 *
	 * @param string $icon Conditional field icon.
	 * @param string $attrs Conditional fields attributes.
	 * @param array  $field Field array.
	 * @return string Icon HTML.
	 */
	return apply_filters( 'geodir_conditional_field_icon', $icon, $attrs, $field );
}

/**
 * Get GD field options.
 *
 * @since 2.3.15
 *
 * @param array $args Arguments.
 * @return array Field options.
 * @todo move to core
 */
function geodir_get_field_key_options( $args = array() ) {
	$defaults = array(
		'context' => '',
		'post_type' => 'all',
		'default' => 'all',
		'fields_location' => 'none',
		'package_id' => '',
		'frontend' => false,
		'display_label' => ''
	);

	$args = wp_parse_args( $args, $defaults );

	$fields = geodir_post_custom_fields( $args['package_id'], $args['default'], $args['post_type'], $args['fields_location'] );

	$options = array();

	if ( ! empty( $fields ) ) {
		foreach( $fields as $field ) {
			if ( apply_filters( 'geodir_field_key_options_skip_field', false, $field, $args ) ) {
				continue;
			}

			if ( ! empty( $args['frontend'] ) ) {
				$title = ! empty( $field['frontend_title'] ) ? __( $field['frontend_title'], 'geodirectory' ) : __( $field['admin_title'], 'geodirectory' );
			} else {
				$title = ! empty( $field['admin_title'] ) ? __( $field['admin_title'], 'geodirectory' ) : __( $field['frontend_title'], 'geodirectory' );
			}

			if ( $args['display_label'] == 'key' ) {
				$display_label = $field['htmlvar_name'];
			} else if ( $args['display_label'] == 'title' ) {
				$display_label = $title;
			} else {
				$display_label = $field['htmlvar_name'] . ' ( ' . $title . ' )';
			}

			$options[ $field['htmlvar_name'] ] = $display_label;

			if ( $field['htmlvar_name'] == 'post_images' ) {
				if ( $args['display_label'] == 'key' ) {
					$options['featured_image'] = 'featured_image';
				} else if ( $args['display_label'] == 'title' ) {
					$options['featured_image'] = __( 'Featured Image', 'geodirectory' );
				} else {
					$options['featured_image'] = 'featured_image ( ' . __( 'Featured Image', 'geodirectory' ) . ' )';
				}
			}
		}

		$post_type = $args['post_type'] == 'all' ? '' : $args['post_type'];

		$advance_fields = geodir_post_meta_advance_fields( $post_type );

		foreach( $advance_fields as $field ) {
			$continue = false;

			if ( strpos( $field['htmlvar_name'] , 'business_hours_' ) === 0 && ! isset( $options[ 'business_hours' ] ) ) {
				$continue = true;
			} else if ( ( strpos( $field['htmlvar_name'] , 'event_start_' ) === 0 || strpos( $field['htmlvar_name'] , 'event_end_' ) === 0 ) && ! isset( $options[ 'event_dates' ] ) ) {
				$continue = true;
			}

			if ( apply_filters( 'geodir_field_key_options_skip_field', $continue, $field, $args ) ) {
				continue;
			}

			if ( ! empty( $args['frontend'] ) ) {
				$title = ! empty( $field['frontend_title'] ) ? __( $field['frontend_title'], 'geodirectory' ) : __( $field['admin_title'], 'geodirectory' );
			} else {
				$title = ! empty( $field['admin_title'] ) ? __( $field['admin_title'], 'geodirectory' ) : __( $field['frontend_title'], 'geodirectory' );
			}

			if ( $args['display_label'] == 'key' ) {
				$display_label = $field['htmlvar_name'];
			} else if ( $args['display_label'] == 'title' ) {
				$display_label = $title;
			} else {
				$display_label = $field['htmlvar_name'] . ' ( ' . $title . ' )';
			}

			$options[ $field['htmlvar_name'] ] = $display_label;
		}
	}

	return apply_filters( 'geodir_get_field_key_options', $options, $args );
}

/**
 * Get visibility field conditions options.
 *
 * @since 2.3.15
 *
 * @param array $args Arguments.
 * @return array Field condition options.
 * @todo move to core
 */
function geodir_get_field_condition_options( $args = array() ) {
	$options = array(
		'is_empty' => __( 'is empty', 'geodirectory' ),
		'is_not_empty' => __( 'is not empty', 'geodirectory' ),
		'is_equal' => __( 'is equal', 'geodirectory' ),
		'is_not_equal' => __( 'is not equal', 'geodirectory' ),
		'is_greater_than' => __( 'is greater than', 'geodirectory' ),
		'is_less_than' => __( 'is less than', 'geodirectory' ),
		'is_contains' => __( 'is contains', 'geodirectory' ),
		'is_not_contains' => __( 'is not contains', 'geodirectory' ),
	);

	return apply_filters( 'geodir_get_field_condition_options', $options, $args );
}