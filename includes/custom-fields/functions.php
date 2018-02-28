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
       && $post_id == geodir_details_page_id()
    ){
        return true;
    }elseif(
        isset($_POST['fl_builder_data']['fl_action'])
        && $_POST['fl_builder_data']['fl_action']=='save_settings'
        && isset($_POST['fl_builder_data']['post_id'])
        && $_POST['fl_builder_data']['post_id'] == geodir_details_page_id()
    ){
        return true;
    }elseif(
        is_page(geodir_details_page_id())
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
function geodir_post_custom_fields($package_id = '', $default = 'all', $post_type = 'gd_place', $fields_location = 'none')
{
    global $wpdb, $geodir_post_custom_fields_cache;

    $cache_stored = $post_type . '_' . $package_id . '_' . $default . '_' . $fields_location;

    if (array_key_exists($cache_stored, $geodir_post_custom_fields_cache)) {
        return $geodir_post_custom_fields_cache[$cache_stored];
    }

    $default_query = '';

    if ($default == 'default')
        $default_query .= " and is_admin IN ('1') ";
    elseif ($default == 'custom')
        $default_query .= " and is_admin = '0' ";

    if ($fields_location == 'none') {
    } else{
        $fields_location = esc_sql( $fields_location );
        $default_query .= " and show_in LIKE '%%[$fields_location]%%' ";
    }
    
    $post_type_sql = $post_type != 'all' ? $wpdb->prepare(" and post_type = %s ",$post_type) : '';

    $post_meta_info = $wpdb->get_results(
        "select * from " . GEODIR_CUSTOM_FIELDS_TABLE . " where is_active = '1' {$post_type_sql}  {$default_query} order by sort_order asc,admin_title asc"
    );


    $return_arr = array();
    if ($post_meta_info) {

        foreach ($post_meta_info as $post_meta_info_obj) {

            $custom_fields = array(
                "name" => $post_meta_info_obj->htmlvar_name,
                "label" => $post_meta_info_obj->clabels,
                "default" => $post_meta_info_obj->default_value,
                "type" => $post_meta_info_obj->field_type,
                "desc" => $post_meta_info_obj->frontend_desc);

            if ($post_meta_info_obj->field_type) {
                $options = explode(',', $post_meta_info_obj->option_values);
                $custom_fields["options"] = $options;
            }

            foreach ($post_meta_info_obj as $key => $val) {
                $custom_fields[$key] = $val;
            }

            $pricearr = array();
            $pricearr = explode(',', $post_meta_info_obj->packages);

            if ($package_id != '' && in_array($package_id, $pricearr)) {
                $return_arr[$post_meta_info_obj->sort_order] = $custom_fields;
            } elseif ($package_id == '') {
                $return_arr[$post_meta_info_obj->sort_order] = $custom_fields;
            }
        }
    }
    $geodir_post_custom_fields_cache[$cache_stored] = $return_arr;

    if (has_filter('geodir_filter_geodir_post_custom_fields')) {
        /**
         * Filter the post custom fields array.
         *
         * @since 1.0.0
         *
         * @param array $return_arr Post custom fields array.
         * @param int|string $package_id The package ID.
         * @param string $post_type Optional. The wordpress post type.
         * @param string $fields_location Optional. Where exactly are you going to place this custom fields?.
         */
        $return_arr = apply_filters('geodir_filter_geodir_post_custom_fields', $return_arr, $package_id, $post_type, $fields_location);
    }

    return $return_arr;
}


/**
 * Get the value of a custom field for a current post.
 * 
 * @param $cf
 *
 * @return mixed|void
 */
function geodir_get_cf_value($cf) {
    global $post,$gd_session,$gd_post;
    $value = '';
    if (is_admin()) {
        global $post;

        if (isset($_REQUEST['post'])) {
            $_REQUEST['pid'] = (int)$_REQUEST['post'];
        }
    }elseif(!empty($gd_post)){
        $post = $gd_post;
    }


    // check if post content
    if($cf['name']=='post_content'){
        $value = get_post_field('post_content', $post->ID);
    }else{
        $value = geodir_get_post_meta($post->ID, $cf['name'], true);
    }

    // Set defaults
    if ($value == '' && $post->post_status=='auto-draft') {
        $value = $cf['default'];
    }

    // Blank title for auto drafts
    If($cf['name']=='post_title' && $value == __("Auto Draft")){ // no text domain used here on purpose as we are matching a core WP text.
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
    global $post,$gd_session,$gd_post;
    if (is_admin()) {
        global $post;

        if (isset($_REQUEST['post'])) {
            $_REQUEST['pid'] = (int)$_REQUEST['post'];
        }
    }elseif(!empty($gd_post)){
        $post = $gd_post;
    }


    $value = geodir_get_post_meta($post->ID, 'default_category', true);

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
 * @global object $gd_session GeoDirectory Session object.
 *
 * @param int|string $package_id The package ID.
 * @param string $default Optional. When set to "default" it will display only default fields.
 * @param string $post_type Optional. The wordpress post type.
 */
function geodir_get_custom_fields_html($package_id = '', $default = 'custom', $post_type = 'gd_place') {
    global $is_default, $mapzoom, $gd_session;

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
        $for_admin_use = isset($val['for_admin_use']) && (int)$val['for_admin_use'] == 1 ? true : false;
        if ($for_admin_use && !is_super_admin()) {
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


if (!function_exists('geodir_get_field_infoby')) {
    /**
     * Get custom field using key and value.
     *
     * @since 1.0.0
     * @package GeoDirectory
     * @global object $wpdb WordPress Database object.
     * @param string $key The key you want to look for.
     * @param string $value The value of the key you want to look for.
     * @param string $geodir_post_type The post type.
     * @return bool|mixed Returns field info when available. otherwise returns false.
     */
    function geodir_get_field_infoby($key = '', $value = '', $geodir_post_type = '')
    {

        global $wpdb;

        $filter = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM " . GEODIR_CUSTOM_FIELDS_TABLE . " WHERE post_type=%s AND " . $key . "='" . $value . "'",
                array($geodir_post_type)
            )
        );

        if ($filter) {
            return $filter;
        } else {
            return false;
        }

    }
}


function geodir_field_icon_proccess($cf){


    if (strpos($cf['field_icon'], 'http') !== false) {
        $field_icon = ' background: url(' . $cf['field_icon'] . ') no-repeat left center;background-size:18px 18px;padding-left: 21px;';
    } elseif (strpos($cf['field_icon'], 'fa fa-') !== false) {
        $field_icon = '<i class="' . $cf['field_icon'] . '"></i>';
    }else{
        $field_icon = $cf['field_icon'];
    }

    return $field_icon;
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
     * @global bool $send_to_friend True if send to friend link already rendered. Otherwise false.
     *
     * @param string $fields_location In which page you are going to place this custom fields?. Ex: listing, detail etc.
     * @return string Returns listing info html.
     */
    function geodir_show_listing_info($fields_location = '') {
        global $post, $preview, $wpdb, $send_to_friend;

        $package_info = array();

        $package_info = geodir_post_package_info($package_info, $post);
        $post_package_id = !empty($package_info->pid) ? $package_info->pid : '';
        $p_type = !empty($post->post_type) ? $post->post_type : geodir_get_current_posttype();
        $send_to_friend = false;

        ob_start();
        $fields_info = geodir_post_custom_fields($post_package_id, 'all', $p_type, $fields_location);

        if (!empty($fields_info)) {
            $post = stripslashes_deep($post); // strip slashes
            
            //echo '<div class="geodir-company_info field-group">';
            global $field_set_start;
            $field_set_start = 0;



            foreach ($fields_info as $type) {
                if(isset($type['extra_fields'])){$extra_fields= $type['extra_fields'];}
                $type = stripslashes_deep($type); // strip slashes
                if(isset($type['extra_fields'])){$type['extra_fields'] = $extra_fields;}
                $html = '';
                $field_icon = geodir_field_icon_proccess($type);
                $filed_type = $type['type'];
                $html_var = isset($type['htmlvar_name']) ? $type['htmlvar_name'] : '';
                if($html_var=='post'){$html_var='post_address';}

                /**
                 * Filter the output for custom fields.
                 *
                 * Here we can remove or add new functions depending on the field type.
                 *
                 * @param string $html The html to be filtered (blank).
                 * @param string $fields_location The location the field is to be show.
                 * @param array $type The array of field values.
                 */
                $html = apply_filters("geodir_custom_field_output_{$filed_type}",$html,$fields_location,$type);

                $variables_array = array();


                if ($type['type'] != 'fieldset'):
                    $variables_array['post_id'] = !empty($post->ID) ? $post->ID : (!empty($post->pid) ? $post->pid : NULL);
                    $variables_array['label'] = __($type['frontend_title'], 'geodirectory');
                    $variables_array['value'] = '';
                    if (isset($post->{$type['htmlvar_name']}))
                        $variables_array['value'] = $post->{$type['htmlvar_name']};
                endif;


                if ($html):

                    /**
                     * Called before a custom fields is output on the frontend.
                     *
                     * @since 1.0.0
                     * @param string $html_var The HTML variable name for the field.
                     */
                    do_action("geodir_before_show_{$html_var}");
                    /**
                     * Filter custom field output.
                     *
                     * @since 1.0.0
                     *
                     * @param string $html_var The HTML variable name for the field.
                     * @param string $html Custom field unfiltered HTML.
                     * @param array $variables_array Custom field variables array.
                     */
                    if ($html) echo apply_filters("geodir_show_{$html_var}", $html, $variables_array);

                    /**
                     * Called after a custom fields is output on the frontend.
                     *
                     * @since 1.0.0
                     * @param string $html_var The HTML variable name for the field.
                     */
                    do_action("geodir_after_show_{$html_var}");

                endif;

            }

            //echo '</div>';

        }


        $html = ob_get_clean();

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



if (!function_exists('geodir_save_post_file_fields')) {
    /**
     * Save post file fields
     *
     * @since 1.0.0
     * @since 1.4.7 Added `$extra_fields` parameter.
     * @package GeoDirectory
     * @global object $wpdb WordPress Database object.
     * @global string $plugin_prefix Geodirectory plugin table prefix.
     * @global object $current_user Current user object.
     * @param int $post_id
     * @param string $field_id
     * @param array $post_image
     * @param array $extra_fields Array of extra fields.
     */
    function geodir_save_post_file_fields( $post_id = 0, $field_id = '', $post_image = array(), $extra_fields = array() ) {
        global $wpdb, $plugin_prefix, $current_user;

        $post_type = get_post_type( $post_id );
        $table = $plugin_prefix . $post_type . '_detail';

        $postcurr_images = array();
        $postcurr_images = geodir_get_post_meta( $post_id, $field_id, true );
        $file_urls = '';

        if ( !empty( $post_image ) ) {
            $invalid_files = array();

            //Get and remove all old images of post from database to set by new order
            $geodir_uploaddir = '';
            $uploads = wp_upload_dir();
            $uploads_dir = $uploads['path'];

            $geodir_uploadpath = $uploads['path'];
            $geodir_uploadurl = $uploads['url'];
            $sub_dir = $uploads['subdir'];

            $allowed_file_types = !empty($extra_fields['gd_file_types']) && is_array($extra_fields['gd_file_types']) && !in_array("*", $extra_fields['gd_file_types'] ) ? $extra_fields['gd_file_types'] : '';

            for ( $m = 0; $m < count($post_image); $m++ ) {
                if ( !$find_image = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM " . $table . " WHERE $field_id = %s AND post_id = %d", array( $post_image[$m], $post_id ) ) ) ) {
                    $curr_img_url = $post_image[$m];
                    $image_name_arr = explode('/', $curr_img_url);
                    $curr_img_dir = $image_name_arr[count($image_name_arr) - 2];
                    $filename = end( $image_name_arr );
                    $img_name_arr = explode( '.', $filename );

                    $arr_file_type = wp_check_filetype( $filename );

                    if ( empty( $arr_file_type['ext'] ) || empty( $arr_file_type['type'] ) ) {
                        continue;
                    }

                    $uploaded_file_type = $arr_file_type['type'];
                    $uploaded_file_ext = $arr_file_type['ext'];

                    if ( !empty( $allowed_file_types ) && !in_array( $uploaded_file_ext, $allowed_file_types ) ) {
                        continue; // Invalid file type.
                    }

                    // Set an array containing a list of acceptable formats
                    //$allowed_file_types = array('image/jpg', 'image/jpeg', 'image/gif', 'image/png', 'application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/octet-stream', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv', 'text/plain');

                    if ( !function_exists( 'wp_handle_upload' ) ) {
                        require_once( ABSPATH . 'wp-admin/includes/file.php' );
                    }

                    if ( !is_dir( $geodir_uploadpath ) ) {
                        mkdir( $geodir_uploadpath );
                    }

                    $new_name = $post_id . '_' . $field_id . '_' . $img_name_arr[0] . '.' . $img_name_arr[1];
                    $explode_sub_dir = explode( "/", $sub_dir );
                    
                    if ( $curr_img_dir == end( $explode_sub_dir ) ) {
                        $img_path = $geodir_uploadpath . '/' . $filename;
                        $img_url = $geodir_uploadurl . '/' . $filename;
                    } else {
                        $img_path = $uploads_dir . '/temp_' . $current_user->data->ID . '/' . $filename;
                        $img_url = $uploads['url'] . '/temp_' . $current_user->data->ID . '/' . $filename;
                    }

                    $uploaded_file = false;
                    if ( file_exists( $img_path ) ) {
                        $uploaded_file = copy( $img_path, $geodir_uploadpath . '/' . $new_name );
                    }

                    if ( $curr_img_dir != $geodir_uploaddir ) {
                        if ( file_exists( $img_path ) ) {
                            unlink( $img_path );
                        }
                    }

                    if ( $uploaded_file ) {
                        $file_urls = trim( $uploads['subdir'] . '/' . $new_name, '/\\' );
                    }
                } else {
                    $file_urls = geodir_file_relative_url( $post_image[$m] );
                }
            }
        }

        // Remove all old attachments and temp images
        if ( !empty( $postcurr_images ) ) {
            if ( $file_urls != $postcurr_images ) {
                $invalid_files[] = (object)array( 'src' => $postcurr_images );
                $invalid_files = (object)$invalid_files;
            }
        }

        geodir_save_post_meta( $post_id, $field_id, $file_urls );

        if ( !empty( $invalid_files ) ) {
            geodir_remove_attachments( $invalid_files );
        }
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
function geodir_custom_upload_mimes($existing_mimes = array())
{
    $existing_mimes['wif'] = 'text/plain';
    $existing_mimes['jpg|jpeg'] = 'image/jpeg';
    $existing_mimes['gif'] = 'image/gif';
    $existing_mimes['png'] = 'image/png';
    $existing_mimes['pdf'] = 'application/pdf';
    $existing_mimes['txt'] = 'text/text';
    $existing_mimes['csv'] = 'application/octet-stream';
    $existing_mimes['doc'] = 'application/msword';
    $existing_mimes['xla|xls|xlt|xlw'] = 'application/vnd.ms-excel';
    $existing_mimes['docx'] = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    $existing_mimes['xlsx'] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    return $existing_mimes;
}

if (!function_exists('geodir_plupload_action')) {

    /**
     * Get upload directory path details
     *
     * @since 1.0.0
     * @package GeoDirectory
     * @global object $current_user Current user object.
     * @param array $upload Array of upload directory data with keys of 'path','url', 'subdir, 'basedir', and 'error'.
     * @return mixed Returns upload directory details as an array.
     */
    function geodir_upload_dir($upload)
    {
        global $current_user;
        $upload['subdir'] = $upload['subdir'] . '/temp_' . $current_user->data->ID;
        $upload['path'] = $upload['basedir'] . $upload['subdir'];
        $upload['url'] = $upload['baseurl'] . $upload['subdir'];
        return $upload;
    }

    /**
     * Handles place file and image upload.
     *
     * @since 1.0.0
     * @package GeoDirectory
     */
    function geodir_plupload_action()
    {
        // check ajax nonce
        $imgid = $_POST["imgid"];

        check_ajax_referer($imgid . 'pluploadan');

        // handle custom file uploaddir
        add_filter('upload_dir', 'geodir_upload_dir');

        // change file orientation if needed
        $fixed_file = geodir_exif($_FILES[$imgid . 'async-upload']);

        // handle file upload
        $status = wp_handle_upload($fixed_file, array('test_form' => true, 'action' => 'plupload_action'));
        // remove handle custom file uploaddir
        remove_filter('upload_dir', 'geodir_upload_dir');

        if(!isset($status['url']) && isset($status['error'])){
            print_r($status);
        }

        // send the uploaded file url in response
        if (isset($status['url'])) {
            echo $status['url'];
        } else {
            echo 'x';
        }
        exit;
    }
}

/**
 * Get video using post ID.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 * @param int $post_id The post ID.
 * @return mixed Returns video.
 */
function geodir_get_video($post_id)
{
    global $wpdb, $plugin_prefix;

    $post_type = get_post_type($post_id);

    $table = $plugin_prefix . $post_type . '_detail';

    $results = $wpdb->get_results($wpdb->prepare("SELECT video FROM " . $table . " WHERE post_id=%d", array($post_id)));

    if (!empty($results)) {
        return $results[0]->video;
    }

}

/**
 * Get special offers using post ID.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 * @param int $post_id The post ID.
 * @return mixed Returns special offers.
 */
function geodir_get_special_offers($post_id)
{
    global $wpdb, $plugin_prefix;

    $post_type = get_post_type($post_id);

    $table = $plugin_prefix . $post_type . '_detail';

    $results = $wpdb->get_results($wpdb->prepare("SELECT special_offers FROM " . $table . " WHERE post_id=%d", array($post_id)));

    if (!empty($results)) {
        return $results[0]->special_offers;
    }

}

if (!function_exists('geodir_max_upload_size')) {
    /**
     * Get max upload file size
     *
     * @since 1.0.0
     * @package GeoDirectory
     * @return mixed|void Returns max upload file size.
     */
    function geodir_max_upload_size()
    {
        $max_filesize = (float)geodir_get_option('geodir_upload_max_filesize', 2);

        if ($max_filesize > 0 && $max_filesize < 1) {
            $max_filesize = (int)($max_filesize * 1024) . 'kb';
        } else {
            $max_filesize = $max_filesize > 0 ? $max_filesize . 'mb' : '2mb';
        }
        /** Filter documented in includes/general_functions.php **/
        return apply_filters('geodir_default_image_upload_size_limit', $max_filesize);
    }
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
function geodir_add_custom_sort_options($fields, $post_type)
{
    global $wpdb;

    if ($post_type != '') {

        $all_postypes = geodir_get_posttypes();

        if (in_array($post_type, $all_postypes)) {

            $custom_fields = $wpdb->get_results(
                $wpdb->prepare(
                    "select post_type,data_type,field_type,frontend_title,htmlvar_name,field_icon from " . GEODIR_CUSTOM_FIELDS_TABLE . " where post_type = %s and is_active='1' and cat_sort='1' AND field_type != 'address' order by sort_order asc",
                    array($post_type)
                ), 'ARRAY_A'
            );

            if (!empty($custom_fields)) {

                foreach ($custom_fields as $val) {
                    $fields[] = $val;
                }
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

if (!function_exists('check_field_visibility')) {
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
    function check_field_visibility($package_id, $field_name, $post_type)
    {
        global $wpdb, $geodir_addon_list;
        if (!(isset($geodir_addon_list['geodir_payment_manager']) && $geodir_addon_list['geodir_payment_manager'] == 'yes')) {
            return true;
        }
        if (!$package_id || !$field_name || !$post_type) {
            return true;
        }
        $sql = $wpdb->prepare("SELECT id FROM " . GEODIR_CUSTOM_FIELDS_TABLE . " WHERE is_active='1' AND htmlvar_name=%s AND post_type=%s AND FIND_IN_SET(%s, packages)", array($field_name, $post_type, (int)$package_id));

        if ($wpdb->get_var($sql)) {
            return true;
        }
        return false;
    }
}

/**
 * Parse label & values from string.
 *
 * @since 1.0.0
 * @since 1.5.7 New parameter $translated added.
 * @since 1.6.11 Input option value should not be translated.
 * @package GeoDirectory
 * @param string $input The string input.
 * @param bool $translated True if label needs to be translated.
 * @return array Returns option array.
 */
function geodir_string_to_options($input = '', $translated = false)
{
    $return = array();
    if ($input != '') {
        $input = trim($input);
        $input = rtrim($input, ",");
        $input = ltrim($input, ",");
        $input = trim($input);
    }

    $input_arr = explode(',', $input);

    if (!empty($input_arr)) {
        foreach ($input_arr as $input_str) {
            $input_str = trim($input_str);

            if (strpos($input_str, "/") !== false) {
                $input_str = explode("/", $input_str, 2);
                $label = trim($input_str[0]);
                if ($translated && $label != '') {
                    $label = __($label, 'geodirectory');
                }
                $label = geodir_utf8_ucfirst($label);
                $value = trim($input_str[1]);
            } else {
                $value = $input_str;
                if ($translated && $input_str != '') {
                    $input_str = __($input_str, 'geodirectory');
                }
                $label = geodir_utf8_ucfirst($input_str);
            }

            if ($label != '') {
                $return[] = array('label' => $label, 'value' => $value, 'optgroup' => NULL);
            }
        }
    }

    return $return;
}

/**
 * Parse option values string to array.
 *
 * @since 1.0.0
 * @since 1.5.7 New parameter $translated added.
 * @package GeoDirectory
 * @param string $option_values The option values.
 * @param bool $translated True if label needs to be translated.
 * @return array Returns option array.
 */
function geodir_string_values_to_options($option_values = '', $translated = false)
{
    $options = array();
    if ($option_values == '') {
        return NULL;
    }

    if (strpos($option_values, "{/optgroup}") !== false) {
        $option_values_arr = explode("{/optgroup}", $option_values);

        foreach ($option_values_arr as $optgroup) {
            if (strpos($optgroup, "{optgroup}") !== false) {
                $optgroup_arr = explode("{optgroup}", $optgroup);

                $count = 0;
                foreach ($optgroup_arr as $optgroup_str) {
                    $count++;
                    $optgroup_str = trim($optgroup_str);

                    $optgroup_label = '';
                    if (strpos($optgroup_str, "|") !== false) {
                        $optgroup_str_arr = explode("|", $optgroup_str, 2);
                        $optgroup_label = trim($optgroup_str_arr[0]);
                        if ($translated && $optgroup_label != '') {
                            $optgroup_label = __($optgroup_label, 'geodirectory');
                        }
                        $optgroup_label = geodir_utf8_ucfirst($optgroup_label);
                        $optgroup_str = $optgroup_str_arr[1];
                    }

                    $optgroup3 = geodir_string_to_options($optgroup_str, $translated);

                    if ($count > 1 && $optgroup_label != '' && !empty($optgroup3)) {
                        $optgroup_start = array(array('label' => $optgroup_label, 'value' => NULL, 'optgroup' => 'start'));
                        $optgroup_end = array(array('label' => $optgroup_label, 'value' => NULL, 'optgroup' => 'end'));
                        $optgroup3 = array_merge($optgroup_start, $optgroup3, $optgroup_end);
                    }
                    $options = array_merge($options, $optgroup3);
                }
            } else {
                $optgroup1 = geodir_string_to_options($optgroup, $translated);
                $options = array_merge($options, $optgroup1);
            }
        }
    } else {
        $options = geodir_string_to_options($option_values, $translated);
    }

    return $options;
}







































function geodir_currency_format_number($number='',$cf=''){

    $cs = isset($cf['extra_fields']) ? maybe_unserialize($cf['extra_fields']) : '';

    $symbol = isset($cs['currency_symbol']) ? $cs['currency_symbol'] : '$';
    $decimals = isset($cf['decimal_point']) && $cf['decimal_point'] ? $cf['decimal_point'] : 2;
    $decimal_display = !empty($cf['decimal_display']) ? $cf['decimal_display'] : (!empty($cs['decimal_display']) ? $cs['decimal_display'] : 'if');
    $decimalpoint = '.';

    if(isset($cs['decimal_separator']) && $cs['decimal_separator']=='comma'){
        $decimalpoint = ',';
    }

    $separator = ',';

    if(isset($cs['thousand_separator'])){
        if($cs['thousand_separator']=='comma'){$separator = ',';}
        if($cs['thousand_separator']=='slash'){$separator = '\\';}
        if($cs['thousand_separator']=='period'){$separator = '.';}
        if($cs['thousand_separator']=='space'){$separator = ' ';}
        if($cs['thousand_separator']=='none'){$separator = '';}
    }

    $currency_symbol_placement = isset($cs['currency_symbol_placement']) ? $cs['currency_symbol_placement'] : 'left';

    if($decimals>0 && $decimal_display=='if'){
        if(is_int($number) || floor( $number ) == $number)
            $decimals = 0;
    }

    $number = number_format($number,$decimals,$decimalpoint,$separator);



    if($currency_symbol_placement=='left'){
        $number = $symbol . $number;
    }else{
        $number = $number . $symbol;
    }


   return $number;
}