<?php
/**
 * Post Listing functions.
 *
 * @since 1.0.0
 * @package GeoDirectory
 */


/**
 * Set post category structure based on given parameters.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @param int $post_id The post ID.
 * @param string $taxonomy Name of the taxonomy e.g place_category.
 * @param string|int $default_cat Optional. The default category ID.
 * @param array|string $category_str Optional. Blank string if no categories. Else array of categories to set.
 */
function geodir_set_postcat_structure($post_id, $taxonomy, $default_cat = '', $category_str = '')
{

    $post_cat_ids = geodir_get_post_meta($post_id, $taxonomy);
    if (!empty($post_cat_ids))
        $post_cat_array = explode(",", trim($post_cat_ids, ","));

    if (!isset($default_cat) || empty($default_cat)) {
        $default_cat = isset($post_cat_array[0]) ? $post_cat_array[0] : '';
    }else{
        if(!is_int($default_cat)){
            $category = get_term_by('name', $default_cat, $taxonomy);
            if(isset($category->term_id)){
                $default_cat =  $category->term_id;
            }
        }

    }


    geodir_save_post_meta($post_id, 'default_category', $default_cat);

    if (isset($category_str) && empty($category_str)) {

        $post_cat_str = '';
        $post_category = array();
        if (isset($post_cat_array) && is_array($post_cat_array) && !empty($post_cat_array)) {
            $post_cat_str = implode(",y:#", $post_cat_array);
            $post_cat_str .= ",y:";
            $post_cat_str = substr_replace($post_cat_str, ',y,d:', strpos($post_cat_str, ',y:'), strlen(',y:'));
        }
        $post_category[$taxonomy] = $post_cat_str;
        $category_str = $post_category;
    }

    $change_cat_str = $category_str[$taxonomy];

    $default_pos = strpos($change_cat_str, 'd:');

    if ($default_pos === false) {

        $change_cat_str = str_replace($default_cat . ',y:', $default_cat . ',y,d:', $change_cat_str);

    }

    $category_str[$taxonomy] = $change_cat_str;

    update_post_meta($post_id, 'post_category', $category_str);

}



/**
 * Get post custom fields.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @global object $post The current post object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 * @param int|string $post_id Optional. The post ID.
 * @return object|bool Returns full post details as an object. If no details returns false.
 * @todo this needs caching
 */
function geodir_get_post_info($post_id = '')
{
    
    global $wpdb, $plugin_prefix, $post, $post_info,$preview;


    if ($post_id == '' && !empty($post))
        $post_id = $post->ID;

    

    $post_type = get_post_type($post_id);

    if($post_type == 'revision'){
        $post_type = get_post_type(wp_get_post_parent_id($post_id));
    }

    // check if preview
    if($preview && $post->ID==$post_id){
        $post_id = GeoDir_Post_Data::get_post_preview_id($post_id);
    }

    $all_postypes = geodir_get_posttypes();

    if (!in_array($post_type, $all_postypes)){
        return new stdClass();
    }

    $table = $plugin_prefix . $post_type . '_detail';

    /**
     * Apply Filter to change Post info
     *
     * You can use this filter to change Post info.
     *
     * @since 1.0.0
     * @package GeoDirectory
     */
    $query = apply_filters('geodir_post_info_query', $wpdb->prepare("SELECT p.*,pd.* FROM " . $wpdb->posts . " p," . $table . " pd
			  WHERE p.ID = pd.post_id
			  AND pd.post_id = %d", $post_id));

    $post_detail = $wpdb->get_row($query);

    return (!empty($post_detail)) ? $post_info = $post_detail : $post_info = false;

}



/**
 * Save or update post custom fields.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 * @param int $post_id The post ID.
 * @param string $postmeta Detail table column name.
 * @param string $meta_value Detail table column value.
 * @return void|bool
 */
function geodir_save_post_meta($post_id, $postmeta = '', $meta_value = '')
{

    global $wpdb, $plugin_prefix;

    $post_type = get_post_type($post_id);

    $table = $plugin_prefix . $post_type . '_detail';

    if ($postmeta != '' && geodir_column_exist($table, $postmeta) && $post_id) {

        if (is_array($meta_value)) {
            $meta_value = implode(",", $meta_value);
        }

        if ($wpdb->get_var($wpdb->prepare("SELECT post_id from " . $table . " where post_id = %d", array($post_id)))) {

            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE " . $table . " SET " . $postmeta . " = '" . $meta_value . "' where post_id =%d",
                    array($post_id)
                )
            );

        } else {

            $wpdb->query(
                $wpdb->prepare(
                    "INSERT INTO " . $table . " SET post_id = %d, " . $postmeta . " = '" . $meta_value . "'",
                    array($post_id)
                )
            );
        }


    } else
        return false;
}


if (!function_exists('geodir_delete_post_meta')) {
    /**
     * Delete post custom fields.
     *
     * @since 1.0.0
     * @package GeoDirectory
     * @global object $wpdb WordPress Database object.
     * @global string $plugin_prefix Geodirectory plugin table prefix.
     * @param int $post_id The post ID.
     * @param string $postmeta Detail table column name.
     * @todo check if this is depreciated
     * @todo Fix unknown variable mval
     * @return bool
     */
    function geodir_delete_post_meta($post_id, $postmeta)
    {

        global $wpdb, $plugin_prefix;

        $post_type = get_post_type($post_id);

        $table = $plugin_prefix . $post_type . '_detail';

        if (is_array($postmeta) && !empty($postmeta) && $post_id) {
            $post_meta_set_query = '';

            foreach ($postmeta as $mkey) {
                if ($mval != '')
                    $post_meta_set_query .= $mkey . " = '', ";
            }

            $post_meta_set_query = trim($post_meta_set_query, ", ");
            
            if (empty($post_meta_set_query) || trim($post_meta_set_query) == '') {
                return false;
            }

            if ($wpdb->get_var("SHOW COLUMNS FROM " . $table . " WHERE field = '" . $postmeta . "'") != '') {

                $wpdb->query(
                    $wpdb->prepare(
                        "UPDATE " . $table . " SET " . $post_meta_set_query . " where post_id = %d",
                        array($post_id)
                    )
                );

                return true;
            }

        } elseif ($postmeta != '' && $post_id) {
            if ($wpdb->get_var("SHOW COLUMNS FROM " . $table . " WHERE field = '" . $postmeta . "'") != '') {

                $wpdb->query(
                    $wpdb->prepare(
                        "UPDATE " . $table . " SET " . $postmeta . "= '' where post_id = %d",
                        array($post_id)
                    )
                );

                return true;
            }

        } else
            return false;
    }
}


if (!function_exists('geodir_get_post_meta')) {
    /**
     * Get post custom meta.
     *
     * @since 1.0.0
     * @since 1.6.20 Hook added to filter value.
     * @package GeoDirectory
     * @global object $wpdb WordPress Database object.
     * @global string $plugin_prefix Geodirectory plugin table prefix.
     * @param int $post_id The post ID.
     * @param string $meta_key The meta key to retrieve.
     * @param bool $single Optional. Whether to return a single value. Default false.
     * @todo single variable not yet implemented.
     * @return bool|mixed|null|string Will be an array if $single is false. Will be value of meta data field if $single is true.
     */
    function geodir_get_post_meta($post_id, $meta_key, $single = false) {
        if (!$post_id) {
            return false;
        }
        global $wpdb, $plugin_prefix,$preview;

        $all_postypes = geodir_get_posttypes();

        $post_type = get_post_type($post_id);

        if($post_type == 'revision'){
            $post_type = get_post_type(wp_get_post_parent_id($post_id));
        }

        // check if preview
        if($preview){
            $post_id = GeoDir_Post_Data::get_post_preview_id($post_id);
        }

        if (!in_array($post_type, $all_postypes))
            return false;

        $table = $plugin_prefix . $post_type . '_detail';

        if ($wpdb->get_var("SHOW COLUMNS FROM " . $table . " WHERE field = '" . $meta_key . "'") != '') {
            $meta_value = $wpdb->get_var($wpdb->prepare("SELECT " . $meta_key . " from " . $table . " where post_id = %d", array($post_id)));
            
            if ($meta_value && $meta_value !== '') {
                $meta_value = maybe_serialize($meta_value);
            }
        } else {
            $meta_value = false;
        }
        
        /**
         * Filter the listing custom meta.
         *
         * @since 1.6.20
         * 
         * @param bool|mixed|null|string $meta_value Will be an array if $single is false. Will be value of meta data field if $single is true.
         * @param int $post_id The post ID.
         * @param string $meta_key The meta key to retrieve.
         * @param bool $single Optional. Whether to return a single value. Default false.
         */
        return apply_filters( 'geodir_get_post_meta', $meta_value, $post_id, $meta_key, $single );
    }
}


if (!function_exists('geodir_save_post_images')) {
    /**
     * Save post attachments.
     *
     * @since 1.0.0
     * @package GeoDirectory
     * @global object $wpdb WordPress Database object.
     * @global string $plugin_prefix Geodirectory plugin table prefix.
     * @global object $current_user Current user object.
     * @param int $post_id The post ID.
     * @param array $post_image Post image urls as an array.
     * @param bool $dummy Optional. Is this a dummy listing? Default false.
     */
    function geodir_save_post_images($post_id = 0, $post_image = array(), $dummy = false)
    {


        global $wpdb, $plugin_prefix, $current_user;

        $post_type = get_post_type($post_id);

        $table = $plugin_prefix . $post_type . '_detail';

        $post_images = geodir_get_images($post_id);

        $wpdb->query(
            $wpdb->prepare(
                "UPDATE " . $table . " SET featured_image = '' where post_id =%d",
                array($post_id)
            )
        );

        $invalid_files = $post_images;
        $valid_file_ids = array();
        $valid_files_condition = '';
        $geodir_uploaddir = '';

        $remove_files = array();

        if (!empty($post_image)) {

            $uploads = wp_upload_dir();
            $uploads_dir = $uploads['path'];

            $geodir_uploadpath = $uploads['path'];
            $geodir_uploadurl = $uploads['url'];
            $sub_dir = isset($uploads['subdir']) ? $uploads['subdir'] : '';

            $invalid_files = array();
            $postcurr_images = array();

            for ($m = 0; $m < count($post_image); $m++) {
                $menu_order = $m + 1;

                $file_path = '';
                /* --------- start ------- */

                $split_img_path = explode(str_replace(array('http://','https://'),'',$uploads['baseurl']), str_replace(array('http://','https://'),'',$post_image[$m]));

                $split_img_file_path = isset($split_img_path[1]) ? $split_img_path[1] : '';


                if (!$find_image = $wpdb->get_var($wpdb->prepare("SELECT ID FROM " . GEODIR_ATTACHMENT_TABLE . " WHERE file=%s AND post_id = %d", array($split_img_file_path, $post_id)))) {

                    /* --------- end ------- */
                    $curr_img_url = $post_image[$m];

                    $image_name_arr = explode('/', $curr_img_url);

                    $count_image_name_arr = count($image_name_arr) - 2;

                    $count_image_name_arr = ($count_image_name_arr >= 0) ? $count_image_name_arr : 0;

                    $curr_img_dir = $image_name_arr[$count_image_name_arr];

                    $filename = end($image_name_arr);
                    if (strpos($filename, '?') !== false) {
                        list($filename) = explode('?', $filename);
                    }

                    $curr_img_dir = str_replace($uploads['baseurl'], "", $curr_img_url);
                    $curr_img_dir = str_replace($filename, "", $curr_img_dir);

                    $img_name_arr = explode('.', $filename);

                    $file_title = isset($img_name_arr[0]) ? $img_name_arr[0] : $filename;
                    if (!empty($img_name_arr) && count($img_name_arr) > 2) {
                        $new_img_name_arr = $img_name_arr;
                        if (isset($new_img_name_arr[count($img_name_arr) - 1])) {
                            unset($new_img_name_arr[count($img_name_arr) - 1]);
                            $file_title = implode('.', $new_img_name_arr);
                        }
                    }
                    $file_title = sanitize_file_name($file_title);
                    $file_name = sanitize_file_name($filename);

                    $arr_file_type = wp_check_filetype($filename);

                    $uploaded_file_type = $arr_file_type['type'];

                    // Set an array containing a list of acceptable formats
                    $allowed_file_types = array('image/jpg', 'image/jpeg', 'image/gif', 'image/png');

                    // If the uploaded file is the right format
                    if (in_array($uploaded_file_type, $allowed_file_types)) {
                        if (!function_exists('wp_handle_upload')) {
                            require_once(ABSPATH . 'wp-admin/includes/file.php');
                        }

                        if (!is_dir($geodir_uploadpath)) {
                            mkdir($geodir_uploadpath);
                        }

                        $external_img = false;
                        if (strpos( str_replace( array('http://','https://'),'',$curr_img_url ), str_replace(array('http://','https://'),'',$uploads['baseurl'] ) ) !== false) {
                        } else {
                            $external_img = true;
                        }

                        if ($dummy || $external_img) {
                            $uploaded_file = array();
                            $uploaded = (array)fetch_remote_file($curr_img_url);

                            if (isset($uploaded['error']) && empty($uploaded['error'])) {
                                $new_name = basename($uploaded['file']);
                                $uploaded_file = $uploaded;
                            }else{
                                print_r($uploaded);exit;
                            }
                            $external_img = false;
                        } else {
                            $new_name = $post_id . '_' . $file_name;

                            if ($curr_img_dir == $sub_dir) {
                                $img_path = $geodir_uploadpath . '/' . $filename;
                                $img_url = $geodir_uploadurl . '/' . $filename;
                            } else {
                                $img_path = $uploads_dir . '/temp_' . $current_user->data->ID . '/' . $filename;
                                $img_url = $uploads['url'] . '/temp_' . $current_user->data->ID . '/' . $filename;
                            }

                            $uploaded_file = '';

                            if (file_exists($img_path)) {
                                $uploaded_file = copy($img_path, $geodir_uploadpath . '/' . $new_name);
                                $file_path = '';
                            } else if (file_exists($uploads['basedir'] . $curr_img_dir . $filename)) {
                                $uploaded_file = true;
                                $file_path = $curr_img_dir . '/' . $filename;
                            }

                            if ($curr_img_dir != $geodir_uploaddir && file_exists($img_path))
                                unlink($img_path);
                        }

                        if (!empty($uploaded_file)) {
                            if (!isset($file_path) || !$file_path) {
                                $file_path = $sub_dir . '/' . $new_name;
                            }

                            $postcurr_images[] = str_replace(array('http://','https://'),'',$uploads['baseurl'] . $file_path);

                            if ($menu_order == 1) {

                                $wpdb->query($wpdb->prepare("UPDATE " . $table . " SET featured_image = %s where post_id =%d", array($file_path, $post_id)));

                            }

                            // Set up options array to add this file as an attachment
                            $attachment = array();
                            $attachment['post_id'] = $post_id;
                            $attachment['title'] = $file_title;
                            $attachment['content'] = '';
                            $attachment['file'] = $file_path;
                            $attachment['mime_type'] = $uploaded_file_type;
                            $attachment['menu_order'] = $menu_order;
                            $attachment['featured'] = 0;

                            $attachment_set = '';

                            foreach ($attachment as $key => $val) {
                                if ($val != '')
                                    $attachment_set .= $key . " = '" . $val . "', ";
                            }

                            $attachment_set = trim($attachment_set, ", ");

                            $wpdb->query("INSERT INTO " . GEODIR_ATTACHMENT_TABLE . " SET " . $attachment_set);

                            $valid_file_ids[] = $wpdb->insert_id;
                        }

                    }


                } else {
                    $valid_file_ids[] = $find_image;

                    $postcurr_images[] = str_replace(array('http://','https://'),'',$post_image[$m]);

                    $wpdb->query(
                        $wpdb->prepare(
                            "UPDATE " . GEODIR_ATTACHMENT_TABLE . " SET menu_order = %d where file =%s AND post_id =%d",
                            array($menu_order, $split_img_path[1], $post_id)
                        )
                    );

                    if ($menu_order == 1)
                        $wpdb->query($wpdb->prepare("UPDATE " . $table . " SET featured_image = %s where post_id =%d", array($split_img_path[1], $post_id)));

                }


            }

            if (!empty($valid_file_ids)) {

                $remove_files = $valid_file_ids;

                $remove_files_length = count($remove_files);
                $remove_files_format = array_fill(0, $remove_files_length, '%d');
                $format = implode(',', $remove_files_format);
                $valid_files_condition = " ID NOT IN ($format) AND ";

            }

            //Get and remove all old images of post from database to set by new order

            if (!empty($post_images)) {

                foreach ($post_images as $img) {

                    if (!in_array(str_replace(array('http://','https://'),'',$img->src), $postcurr_images)) {

                        $invalid_files[] = (object)array('src' => $img->src);

                    }

                }

            }

            $invalid_files = (object)$invalid_files;
        }

        $remove_files[] = $post_id;

        $wpdb->query($wpdb->prepare("DELETE FROM " . GEODIR_ATTACHMENT_TABLE . " WHERE " . $valid_files_condition . " post_id = %d", $remove_files));

        if (!empty($invalid_files))
            geodir_remove_attachments($invalid_files);
    }

}

/**
 * Remove current user's temporary images.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $current_user Current user object.
 **/
function geodir_remove_temp_images()
{

    global $current_user;

    $uploads = wp_upload_dir();
    $uploads_dir = $uploads['path'];

    /*	if(is_dir($uploads_dir.'/temp_'.$current_user->data->ID)){

			$dirPath = $uploads_dir.'/temp_'.$current_user->data->ID;
			if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
				$dirPath .= '/';
			}
			$files = glob($dirPath . '*', GLOB_MARK);
			foreach ($files as $file) {
				if (is_dir($file)) {
					self::deleteDir($file);
				} else {
					unlink($file);
				}
			}
			rmdir($dirPath);
	}	*/

    $dirname = $uploads_dir . '/temp_' . $current_user->ID;
    geodir_delete_directory($dirname);
}


/**
 * Delete a directory.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @param string $dirname Directory name that needs to be deleted.
 * @return bool
 */
function geodir_delete_directory($dirname)
{
    $dir_handle = '';
    if (is_dir($dirname))
        $dir_handle = opendir($dirname);
    if (!$dir_handle)
        return false;
    while ($file = readdir($dir_handle)) {
        if ($file != "." && $file != "..") {
            if (!is_dir($dirname . "/" . $file))
                unlink($dirname . "/" . $file);
            else
                geodir_delete_directory($dirname . '/' . $file);
        }
    }
    closedir($dir_handle);
    rmdir($dirname);
    return true;

}


if (!function_exists('geodir_remove_attachments')) {
    /**
     * Remove post attachments.
     *
     * @since 1.0.0
     * @package GeoDirectory
     * @param array $postcurr_images Array of image objects.
     */
    function geodir_remove_attachments($postcurr_images = array())
    {
        // Unlink all past images of post
        if (!empty($postcurr_images)) {

            $uploads = wp_upload_dir();
            $uploads_dir = $uploads['path'];

            foreach ($postcurr_images as $postimg) {
                $image_name_arr = explode('/', $postimg->src);
                $filename = end($image_name_arr);
                if (file_exists($uploads_dir . '/' . $filename))
                    unlink($uploads_dir . '/' . $filename);
            }

        } // endif
        // Unlink all past images of post end
    }
}

if (!function_exists('geodir_get_featured_image')) {
    /**
     * Gets the post featured image.
     *
     * @since 1.0.0
     * @package GeoDirectory
     * @global object $wpdb WordPress Database object.
     * @global object $post The current post object.
     * @global string $plugin_prefix Geodirectory plugin table prefix.
     * @param int|string $post_id The post ID.
     * @param string $size Optional. Thumbnail size. Default: thumbnail.
     * @param bool $no_image Optional. Do you want to return the default image when no image is available? Default: false.
     * @param bool|string $file Optional. The file path from which you want to get the image details. Default: false.
     * @return bool|object Image details as an object.
     */
    function geodir_get_featured_image($post_id = '', $size = '', $no_image = false, $file = false)
    {

        /*$img_arr['src'] = get_the_post_thumbnail_url( $post_id,  'medium');//medium/thumbnail
        $img_arr['path'] = '';
        $img_arr['width'] = '';
        $img_arr['height'] = '';
        $img_arr['title'] = '';
        return (object)$img_arr;*/
        global $wpdb, $plugin_prefix, $post;

        if (isset($post->ID) && isset($post->post_type) && $post->ID == $post_id) {
            $post_type = $post->post_type;
        } else {
            $post_type = get_post_type($post_id);
        }

        if (!in_array($post_type, geodir_get_posttypes())) {
            return false;// if not a GD CPT return;
        }


        $list_img_size = geodir_get_option('geodir_listing_img_size','default');

        if( $size=='list-thumb' && $list_img_size != 'default' ){
            $fimg = get_the_post_thumbnail_url($post_id,$list_img_size);
            if($fimg){
                $uploads = wp_upload_dir(); 
                $uploads_baseurl = $uploads['baseurl'];
                $file = str_replace($uploads_baseurl,'',$fimg);
            }
        }

        $table = $plugin_prefix . $post_type . '_detail';

        if (!$file) {
            if (isset($post->featured_image)) {
                $file = $post->featured_image;
            } else {
                $file = $wpdb->get_var($wpdb->prepare("SELECT featured_image FROM " . $table . " WHERE post_id = %d", array($post_id)));
            }
        }

        if ($file != NULL && $file != '' && (($uploads = wp_upload_dir()) && false === $uploads['error'])) {
            $img_arr = array();

            $file_info = pathinfo($file);
            $sub_dir = '';
            if ($file_info['dirname'] != '.' && $file_info['dirname'] != '..') {
                $sub_dir = stripslashes_deep($file_info['dirname']);
            }

            $uploads = wp_upload_dir(trim($sub_dir, '/')); // Array of key => value pairs
            $uploads_baseurl = $uploads['baseurl'];
            $uploads_path = $uploads['path'];

            $file_name = $file_info['basename'];

            $uploads_url = $uploads_baseurl . $sub_dir;

            $img_src = $uploads_url . '/' . $file_name;
            // jetpack CDN check
            if (strpos($file, '.wp.com/') !== false) {
                $img_src = $file;
            }

            /*
             * Allows the filter of image src for such things as CDN change.
             *
             * @since 1.5.7
             * @param string $url The full image url.
             * @param string $file_name The image file name and directory path.
             * @param string $uploads_url The server upload directory url.
             * @param string $uploads_baseurl The uploads dir base url.
             */
            $img_arr['src'] = apply_filters('geodir_get_featured_image_src',$img_src,$file_name,$uploads_url,$uploads_baseurl);
            $img_arr['path'] = $uploads_path . '/' . $file_name;
            $width = 0;
            $height = 0;
            if (is_file($img_arr['path']) && file_exists($img_arr['path'])) {
                $imagesize = getimagesize($img_arr['path']);
                $width = !empty($imagesize) && isset($imagesize[0]) ? $imagesize[0] : '';
                $height = !empty($imagesize) && isset($imagesize[1]) ? $imagesize[1] : '';
            }
            $img_arr['width'] = $width;
            $img_arr['height'] = $height;
            $img_arr['title'] = $post->post_title;
        } elseif ($post_images = geodir_get_images($post_id, 1)) {
            foreach ($post_images as $image) {
                return $image;
            }
        } else if ($no_image) {
            $img_arr = array();

            $default_img = '';
            if (isset($post->default_category) && $post->default_category) {
                $default_cat = $post->default_category;
            } else {
                $default_cat = geodir_get_post_meta($post_id, 'default_category', true);
            }

            $default_img = geodir_get_cat_image( $default_cat, true );
            if ( !$default_img ) {
                $default_img = geodir_get_option( 'geodir_listing_no_img' );
            }

            if (!empty($default_img)) {
                $uploads = wp_upload_dir(); // Array of key => value pairs
                $uploads_baseurl = $uploads['baseurl'];
                $uploads_path = $uploads['path'];

                $img_arr = array();

                $file_info = pathinfo($default_img);

                $file_name = $file_info['basename'];

                $img_arr['src'] = $default_img;
                $img_arr['path'] = $uploads_path . '/' . $file_name;

                $width = 0;
                $height = 0;
                if (is_file($img_arr['path']) && file_exists($img_arr['path'])) {
                    $imagesize = getimagesize($img_arr['path']);
                    $width = !empty($imagesize) && isset($imagesize[0]) ? $imagesize[0] : '';
                    $height = !empty($imagesize) && isset($imagesize[1]) ? $imagesize[1] : '';
                }
                $img_arr['width'] = $width;
                $img_arr['height'] = $height;

                $img_arr['title'] = $post->post_title; // add the title to the array
            }
        }

        if (!empty($img_arr))
            return (object)$img_arr;//return (object)array( 'src' => $file_url, 'path' => $file_path );
        else
            return false;
    }
}

if (!function_exists('geodir_show_featured_image')) {
    /**
     * Gets the post featured image.
     *
     * @since 1.0.0
     * @package GeoDirectory
     * @param int|string $post_id The post ID.
     * @param string $size Optional. Thumbnail size. Default: thumbnail.
     * @param bool $no_image Optional. Do you want to return the default image when no image is available? Default: false.
     * @param bool $echo Optional. Do you want to print it instead of returning it? Default: true.
     * @param bool|string $fimage Optional. The file path from which you want to get the image details. Default: false.
     * @return bool|string Returns image html.
     */
    function geodir_show_featured_image($post_id = '', $size = 'thumbnail', $no_image = false, $echo = true, $fimage = false)
    {
        $image = geodir_get_featured_image($post_id, $size, $no_image, $fimage);

        $html = geodir_show_image($image, $size, $no_image, false);

        if (!empty($html) && $echo) {
            echo $html;
        } elseif (!empty($html)) {
            return $html;
        } else
            return false;
    }
}

if (!function_exists('geodir_get_images_old')) {
    /**
     * Gets the post images.
     *
     * @since 1.0.0
     * @package GeoDirectory
     * @global object $wpdb WordPress Database object.
     * @param int $post_id The post ID.
     * @param string $img_size Optional. Thumbnail size.
     * @param bool $no_images Optional. Do you want to return the default image when no image is available? Default: false.
     * @param bool $add_featured Optional. Do you want to include featured images too? Default: true.
     * @param int|string $limit Optional. Number of images.
     * @return array|bool Returns images as an array. Each item is an object.
     */
    function geodir_get_images_old($post_id = 0, $img_size = '', $no_images = false, $add_featured = true, $limit = '')
    {
        global $wpdb;
        if ($limit) {
            $limit_q = " LIMIT $limit ";
        } else {
            $limit_q = '';
        }
        $not_featured = '';
        $sub_dir = '';
        if (!$add_featured)
            $not_featured = " AND featured = 0 ";

        $arrImages = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM " . GEODIR_ATTACHMENT_TABLE . " WHERE mime_type like %s AND post_id = %d" . $not_featured . " ORDER BY menu_order ASC, ID DESC $limit_q ",
                array('%image%', $post_id)
            )
        );

        $counter = 0;
        $return_arr = array();

        if (!empty($arrImages)) {
            foreach ($arrImages as $attechment) {

                $img_arr = array();
                $img_arr['id'] = $attechment->ID;
                $img_arr['user_id'] = isset($attechment->user_id) ? $attechment->user_id : 0;

                $file_info = pathinfo($attechment->file);

                if ($file_info['dirname'] != '.' && $file_info['dirname'] != '..')
                    $sub_dir = stripslashes_deep($file_info['dirname']);

                $uploads = wp_upload_dir(trim($sub_dir, '/')); // Array of key => value pairs
                $uploads_baseurl = $uploads['baseurl'];
                $uploads_path = $uploads['path'];

                $file_name = $file_info['basename'];

                $uploads_url = $uploads_baseurl . $sub_dir;
                /*
                * Allows the filter of image src for such things as CDN change.
                *
                * @since 1.5.7
                * @param string $url The full image url.
                * @param string $file_name The image file name and directory path.
                * @param string $uploads_url The server upload directory url.
                * @param string $uploads_baseurl The uploads dir base url.
                */
                $img_arr['src'] = apply_filters('geodir_get_images_src',$uploads_url . '/' . $file_name,$file_name,$uploads_url,$uploads_baseurl);
                $img_arr['path'] = $uploads_path . '/' . $file_name;
                $width = 0;
                $height = 0;
                if (is_file($img_arr['path']) && file_exists($img_arr['path'])) {
                    $imagesize = getimagesize($img_arr['path']);
                    $width = !empty($imagesize) && isset($imagesize[0]) ? $imagesize[0] : '';
                    $height = !empty($imagesize) && isset($imagesize[1]) ? $imagesize[1] : '';
                }
                $img_arr['width'] = $width;
                $img_arr['height'] = $height;

                $img_arr['file'] = $file_name; // add the title to the array
                $img_arr['title'] = $attechment->title; // add the title to the array
                $img_arr['caption'] = isset($attechment->caption) ? $attechment->caption : ''; // add the caption to the array
               // $img_arr['content'] = $attechment->content; // add the description to the array
                $img_arr['is_approved'] = isset($attechment->is_approved) ? $attechment->is_approved : ''; // used for user image moderation. For backward compatibility Default value is 1.

                $return_arr[] = (object)$img_arr;

                $counter++;
            }
            //return (object)$return_arr;
            /**
             * Filter the images array so things can be changed.
             *
             * @since 1.6.20
             * @param array $return_arr The array of image objects.
             */
            return apply_filters('geodir_get_images_arr',$return_arr);
        } else if ($no_images) {
            $default_cat = geodir_get_post_meta( $post_id, 'default_category', true );
            $default_img = geodir_get_cat_image( $default_cat, true );
            
            if ( !$default_img ) {
                $default_img = geodir_get_option( 'geodir_listing_no_img' );
            }

            if (!empty($default_img)) {
                $uploads = wp_upload_dir(); // Array of key => value pairs
                
                $image_path = $default_img;
                if (!path_is_absolute($image_path)) {
                    $image_path = str_replace($uploads['baseurl'], $uploads['basedir'], $image_path);
                }

                $file_info = pathinfo($default_img);
                $file_name = $file_info['basename'];

                $width = '';
                $height = '';
                if (is_file($image_path) && file_exists($image_path)) {
                    $imagesize = getimagesize($image_path);
                    $width = !empty($imagesize) && isset($imagesize[0]) ? $imagesize[0] : '';
                    $height = !empty($imagesize) && isset($imagesize[1]) ? $imagesize[1] : '';
                }
                
                $img_arr = array();
                $img_arr['src'] = $default_img;
                $img_arr['path'] = $image_path;
                $img_arr['width'] = $width;
                $img_arr['height'] = $height;
                $img_arr['file'] = $file_name; // add the title to the array
                $img_arr['title'] = $file_info['filename']; // add the title to the array
                //$img_arr['content'] = $file_info['filename']; // add the description to the array

                $return_arr[] = (object)$img_arr;

                /**
                 * Filter the images array so things can be changed.
                 * 
                 * @since 1.6.20
                 * @param array $return_arr The array of image objects.
                 */
                return apply_filters('geodir_get_images_arr',$return_arr);
            } else
                return false;
        }
    }
}




if (!function_exists('geodir_show_image')) {
    /**
     * Show image using image details.
     *
     * @since 1.0.0
     * @package GeoDirectory
     * @param array|object $request Image info either as an array or object.
     * @param string $size Optional. Thumbnail size. Default: thumbnail.
     * @param bool $no_image Optional. Do you want to return the default image when no image is available? Default: false.
     * @param bool $echo Optional. Do you want to print it instead of returning it? Default: true.
     * @return bool|string Returns image html.
     */
    function geodir_show_image($request = array(), $size = 'thumbnail', $no_image = false, $echo = true)
    {
        $image = new stdClass();

        $html = '';
        if (!empty($request)) {
            if (!is_object($request)){
                $request = (object)$request;
            }

            if (isset($request->src) && !isset($request->path)) {
                $request->path = $request->src;
            }

            /*
             * getimagesize() works faster from path than url so we try and get path if we can.
             */
            $upload_dir = wp_upload_dir();
            $img_no_http = str_replace(array("http://", "https://"), "", $request->path);
            $upload_no_http = str_replace(array("http://", "https://"), "", $upload_dir['baseurl']);
            if (strpos($img_no_http, $upload_no_http) !== false) {
                $request->path = str_replace( $img_no_http,$upload_dir['basedir'], $request->path);
            }
            
            $width = 0;
            $height = 0;
            if (is_file($request->path) && file_exists($request->path)) {
                $imagesize = getimagesize($request->path);
                $width = !empty($imagesize) && isset($imagesize[0]) ? $imagesize[0] : '';
                $height = !empty($imagesize) && isset($imagesize[1]) ? $imagesize[1] : '';
            }


            $image->src = $request->src;
            $image->width = $width;
            $image->height = $height;
            $image->title = isset($request->title) ? $request->title : '';

            $max_size = (object)geodir_get_imagesize($size);

            if (!is_wp_error($max_size)) {
                if ($image->width) {
                    if ($image->height >= $image->width) {
                        $width_per = round(((($image->width * ($max_size->h / $image->height)) / $max_size->w) * 100), 2);
                    } else if ($image->width < ($max_size->h)) {
                        $width_per = round((($image->width / $max_size->w) * 100), 2);
                    } else
                        $width_per = 100;
                }

                if (is_admin() && !isset($_REQUEST['geodir_ajax'])){
                    $html = '<div class="geodir_thumbnail"><img style="max-height:' . $max_size->h . 'px;" alt="place image" src="' . $image->src . '"  /></div>';
                } else {
                    if($size=='widget-thumb' || !geodir_get_option('geodir_lazy_load',1)){
                        $html = '<div class="geodir_thumbnail" style="background-image:url(\'' . $image->src . '\');" title="'.$image->title.'" aria-label="'.$image->title.'" ></div>';
                    }else{
                        $html = '<div data-src="'.str_replace(' ','%20',$image->src).'" class="geodir_thumbnail geodir_lazy_load_thumbnail" title="'.$image->title.'" aria-label="'.$image->title.'"></div>';
                    }

                }
            }
        }

        if (!empty($html) && $echo) {
            echo $html;
        } elseif (!empty($html)) {
            return $html;
        } else
            return false;
    }
}



if (!function_exists('geodir_get_infowindow_html')) {
    /**
     * Set post Map Marker info html.
     *
     * @since 1.0.0
     * @since 1.5.4 Modified to add new action "geodir_infowindow_meta_before".
     * @since 1.6.16 Changes for disable review stars for certain post type.
     * @since 1.6.18 Fix: Map marker not showing custom fields in bubble info.
     * @package GeoDirectory
     * @global array $geodir_addon_list List of active GeoDirectory extensions.
     * @global object $gd_session GeoDirectory Session object.
     * @param object $postinfo_obj The post details object.
     * @param string $post_preview Is this a post preview?.
     * @global object $post WordPress Post object.
     * @return mixed|string|void
     */
    function geodir_get_infowindow_html($postinfo_obj, $post_preview = '') {
        global $preview, $gd_post, $gd_session;
        $srcharr = array("'", "/", "-", '"', '\\');
        $replarr = array("&prime;", "&frasl;", "&ndash;", "&ldquo;", '');

        if ($gd_session->get('listing') && isset($post_preview) && $post_preview != '') {
            $ID = '';
            $plink = '';

            if (isset($postinfo_obj->pid)) {
                $ID = $postinfo_obj->pid;
                $plink = get_permalink($ID);
            }

            $title = str_replace($srcharr, $replarr, ($postinfo_obj->post_title));
            $lat = $postinfo_obj->post_latitude;
            $lng = $postinfo_obj->post_longitude;
        } else {
            $ID = $postinfo_obj->post_id;
            $title = str_replace($srcharr, $replarr, htmlentities($postinfo_obj->post_title, ENT_COMPAT, 'UTF-8')); // fix by Stiofan
            $title = wp_specialchars_decode($title); // Fixed #post-320722 on 2016-12-08
            $plink = get_permalink($ID);
            $lat = htmlentities(geodir_get_post_meta($ID, 'post_latitude', true));
            $lng = htmlentities(geodir_get_post_meta($ID, 'post_longitude', true));
        }
        
        // Some theme overwrites global gd listing $post
        if (!empty($ID) && (!empty($gd_post->ID) && $gd_post->ID != $ID) || empty($gd_post)) {
            $gd_post = geodir_get_post_info($ID);
        }
        
        $post_type = $ID ? get_post_type($ID) : '';

        // filter field as per price package
        global $geodir_addon_list;
        if ($post_type && defined('GEODIRPAYMENT_VERSION')) {
            $package_id = isset($postinfo_obj->package_id) && $postinfo_obj->package_id ? $postinfo_obj->package_id : NULL;
            $field_name = 'geodir_contact';
            if (!check_field_visibility($package_id, $field_name, $post_type)) {
                $contact = '';
            }

            $field_name = 'geodir_timing';
            if (!check_field_visibility($package_id, $field_name, $post_type)) {
                $timing = '';
            }
        }

        if ($lat && $lng) {
            ob_start(); ?>
            <div class="gd-bubble" style="">
                <div class="gd-bubble-inside">
                    <?php
                    $comment_count = '';
                    $rating_star = '';
                    if ($ID != '' && $post_type != '' && !geodir_cpt_has_rating_disabled($post_type)) {
                        $rating_star = '';
                        $comment_count = geodir_get_review_count_total($ID);

                        if (!$preview) {
                            $post_avgratings = geodir_get_post_rating($ID);

                            $rating_star = geodir_get_rating_stars($post_avgratings, $ID, false);

                            /**
                             * Filter to change rating stars
                             *
                             * You can use this filter to change Rating stars.
                             *
                             * @since 1.0.0
                             * @package GeoDirectory
                             * @param string $rating_star Rating stars.
                             * @param float $post_avgratings Average ratings of the post.
                             * @param int $ID The post ID.
                             */
                            $rating_star = apply_filters('geodir_review_rating_stars_on_infowindow', $rating_star, $post_avgratings, $ID);
                        }
                    }
                    ?>
                    <div class="geodir-bubble_desc">
                        <h4>
                            <a href="<?php if ($plink != '') {
                                echo $plink;
                            } else {
                                echo 'javascript:void(0);';
                            } ?>"><?php echo $title; ?></a>
                        </h4>
                        <?php
                        if ($gd_session->get('listing') && isset($post_preview) && $post_preview != '') {
                            $post_images = array();
                            if (!empty($postinfo_obj->post_images)) {
                                $post_images = explode(",", $postinfo_obj->post_images);
                            }

                            if (!empty($post_images)) {
                                ?>
                                <div class="geodir-bubble_image"><a href="<?php if ($plink != '') {
                                        echo $plink;
                                    } else {
                                        echo 'javascript:void(0);';
                                    } ?>"><img alt="bubble image" style="max-height:50px;"
                                               src="<?php echo $post_images[0]; ?>"/></a></div>
                            <?php
                            }else{
                                echo '<div class="geodir-bubble_image"></div>';
                            }
                        } else {
                            if ($image = geodir_show_featured_image($ID, 'widget-thumb', true, false, $postinfo_obj->featured_image)) {
                                ?>
                                <div class="geodir-bubble_image"><a href="<?php echo $plink; ?>"><?php echo $image; ?></a></div>
                            <?php
                            }else{
                                echo '<div class="geodir-bubble_image"></div>';
                            }
                        }
                        ?>
                        <div class="geodir-bubble-meta-side">
                            <?php
                            /**
                             * Fires before the meta info in the map info window.
                             *
                             * This can be used to add more info to the map info window before the normal meta info.
                             *
                             * @since 1.5.4
                             * @param int $ID The post id.
                             * @param object $postinfo_obj The posts info as an object.
                             * @param bool|string $post_preview True if currently in post preview page. Empty string if not.                           *
                             */
                            do_action('geodir_infowindow_meta_before', $ID, $postinfo_obj, $post_preview);

                            echo geodir_show_listing_info('mapbubble');

                            /**
                             * Fires after the meta info in the map info window.
                             *
                             * This can be used to add more info to the map info window after the normal meta info.
                             *
                             * @since 1.4.2
                             * @param object $postinfo_obj The posts info as an object.
                             * @param bool|string $post_preview True if currently in post preview page. Empty string if not.                           *
                             */
                            do_action('geodir_infowindow_meta_after',$postinfo_obj,$post_preview );
                            ?>
                        </div>
                        <?php
                        if ($ID) {
                            $post_author = isset($postinfo_obj->post_author) ? $postinfo_obj->post_author : get_post_field('post_author', $ID);
                            ?>
                            <div class="geodir-bubble-meta-fade"></div>
                            <div class="geodir-bubble-meta-bottom">
                                <?php if ($rating_star != '') { ?>
                                <span class="geodir-bubble-rating"><?php echo $rating_star;?></span>
                                <?php } ?>
                                <span class="geodir-bubble-fav"><?php echo geodir_favourite_html($post_author, $ID);?></span>
                                <span class="geodir-bubble-reviews">
                                    <a href="<?php echo get_comments_link($ID); ?>" class="geodir-pcomments"><i class="fa fa-comments"></i> <?php echo get_comments_number($ID); ?></a>
                                </span>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <?php
            $html = ob_get_clean();
            /**
             * Filter to change infowindow html
             *
             * You can use this filter to change infowindow html.
             *
             * @since 1.0.0
             * @package GeoDirectory
             * @param string $html Infowindow html.
             * @param object $postinfo_obj The Post object.
             * @param bool|string $post_preview Is this a post preview?
             */
            $html = apply_filters('geodir_custom_infowindow_html', $html, $postinfo_obj, $post_preview);
            return $html;
        }
    }
}


/**
 * Default post status for new posts.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @return string Returns the default post status for new posts. Ex: draft, publish etc.
 */
function geodir_new_post_default_status()
{
    return GeoDir_Post_Data::get_post_default_status();

}











/**
 * This function would display the html content for add to favorite or remove from favorite.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $current_user Current user object.
 * @global object $post The current post object.
 * @param int $user_id The user ID.
 * @param int $post_id The post ID.
 */
function geodir_favourite_html($user_id, $post_id)
{

    global $current_user, $post;

    if(isset($post->post_type) && $post->post_type){
        $post_type = $post->post_type;
    }else{
        $post_type = get_post_type($post_id);
    }

    if(geodir_cpt_has_favourite_disabled($post_type)){
        return '';
    }

    /**
     * Filter to modify "Add to Favorites" text
     *
     * You can use this filter to rename "Add to Favorites" text to something else.
     *
     * @since 1.0.0
     * @package GeoDirectory
     */
    $add_favourite_text = apply_filters('geodir_add_favourite_text', ADD_FAVOURITE_TEXT);

    /**
     * Filter to modify "Favourite" text
     *
     * You can use this filter to rename "Favourite" text to something else.
     *
     * @since 1.0.0
     * @package GeoDirectory
     */
    $favourite_text = apply_filters('geodir_favourite_text', FAVOURITE_TEXT);

    /**
     * Filter to modify "Unfavorite" text
     *
     * You can use this filter to rename "Unfavorite" text to something else.
     *
     * @since 1.0.0
     * @package GeoDirectory
     */
    $remove_favourite_text = apply_filters('geodir_remove_favourite_text', REMOVE_FAVOURITE_TEXT);

    /**
     * Filter to modify "Remove from Favorites" text
     *
     * You can use this filter to rename "Remove from Favorites" text to something else.
     *
     * @since 1.0.0
     * @package GeoDirectory
     */
    $unfavourite_text = apply_filters('geodir_unfavourite_text', UNFAVOURITE_TEXT);

    /**
     * Filter to modify "fa fa-heart" icon
     *
     * You can use this filter to change "fa fa-heart" icon to something else.
     *
     * @since 1.0.0
     * @package GeoDirectory
     */
    $favourite_icon = apply_filters('geodir_favourite_icon', 'fa fa-heart');

    /**
     * Filter to modify "fa fa-heart" icon for "remove from favorites" link
     *
     * You can use this filter to change "fa fa-heart" icon to something else.
     *
     * @since 1.0.0
     * @package GeoDirectory
     */
    $unfavourite_icon = apply_filters('geodir_unfavourite_icon', 'fa fa-heart');

    $user_meta_data = '';
    if (isset($current_user->data->ID))
        $user_meta_data = geodir_get_user_favourites($current_user->data->ID);

    if (!empty($user_meta_data) && in_array($post_id, $user_meta_data)) {
        ?><span class="geodir-addtofav favorite_property_<?php echo $post_id;?>"  ><a
            class="geodir-removetofav-icon" href="javascript:void(0);"
            onclick="javascript:gd_fav_save(<?php echo $post_id;?>);"
            title="<?php echo $remove_favourite_text;?>"><i class="<?php echo $unfavourite_icon; ?>"></i> <?php echo $unfavourite_text;?>
        </a>   </span><?php

    } else {

        if (!isset($current_user->data->ID) || $current_user->data->ID == '') {
            $script_text = 'javascript:window.location.href=\'' . geodir_login_url() . '\'';
        } else
            $script_text = 'javascript:gd_fav_save(' . $post_id . ')';

        ?><span class="geodir-addtofav favorite_property_<?php echo $post_id;?>"><a class="geodir-addtofav-icon"
                                                                                    href="javascript:void(0);"
                                                                                    onclick="<?php echo $script_text;?>"
                                                                                    title="<?php echo $add_favourite_text;?>"><i
                class="<?php echo $favourite_icon; ?>"></i> <?php echo $favourite_text;?></a></span>
    <?php }
}



/**
 * Get post count for the given category / term.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 * @param object|array $term category / term object that need to be processed.
 * @return bool|int|null|string Post count.
 */
function geodir_get_cat_postcount($term = array())
{

    if (!empty($term)) {

        global $wpdb, $plugin_prefix;

        $where = '';
        $join = '';
        if (get_query_var('gd_country') != '' || get_query_var('gd_region') != '' || get_query_var('gd_city') != '') {
            $taxonomy_obj = get_taxonomy($term->taxonomy);

            $post_type = $taxonomy_obj->object_type[0];

            $table = $plugin_prefix . $post_type . '_detail';

            /**
             * Filter to modify the 'join' query
             *
             * @since 1.0.0
             * @package GeoDirectory
             * @param object|array $term category / term object that need to be processed.
             * @param string $join The join query.
             */
            $join = apply_filters('geodir_cat_post_count_join', $join, $term);

            /**
             * Filter to modify the 'where' query
             *
             * @since 1.0.0
             * @package GeoDirectory
             * @param object|array $term category / term object that need to be processed.
             * @param string $where The where query.
             */
            $where = apply_filters('geodir_cat_post_count_where', $where, $term);

            $count_query = "SELECT count(post_id) FROM
							" . $table . " as pd " . $join . "
							WHERE pd.post_status='publish' AND FIND_IN_SET('" . $term->term_id . "'," . $term->taxonomy . ") " . $where;

            $cat_post_count = $wpdb->get_var($count_query);
            if (empty($cat_post_count) || is_wp_error($cat_post_count))
                $cat_post_count = 0;

            return $cat_post_count;

        } else

            return $term->count;
    }
    return false;

}



/**
 * Changing excerpt length.
 *
 * @since 1.0.0
 * @since 1.5.7 Hide description when description word limit is 0.
 * @package GeoDirectory
 * @global object $wp_query WordPress Query object.
 * @global bool $geodir_is_widget_listing Is this a widget listing?
 * @param int $length Optional. Old length.
 * @return mixed|void Returns length.
 */
function geodir_excerpt_length($length)
{
    global $wp_query, $geodir_is_widget_listing;
	if ($geodir_is_widget_listing) {
		return $length;
	}
	
    if (isset($wp_query->query_vars['is_geodir_loop']) && $wp_query->query_vars['is_geodir_loop'] && geodir_get_option('geodir_desc_word_limit'))
        $length = geodir_get_option('geodir_desc_word_limit');
    elseif (get_query_var('excerpt_length'))
        $length = get_query_var('excerpt_length');

    if (geodir_is_page('author') && geodir_get_option('geodir_author_desc_word_limit'))
        $length = geodir_get_option('geodir_author_desc_word_limit');

    return $length;
}

/**
 * Changing excerpt more.
 *
 * @since 1.0.0
 * @since 1.5.4 Now only applied to GD post types.
 * @package GeoDirectory
 * @global object $post The current post object.
 * @param string $more Optional. Old string.
 * @return string Returns read more link.
 */
function geodir_excerpt_more($more)
{
    global $post;
    $all_postypes = geodir_get_posttypes();
    if (is_array($all_postypes) && in_array($post->post_type, $all_postypes)) {
        return ' <a href="' . get_permalink($post->ID) . '">' . READ_MORE_TXT . '</a>';
    }

    return $more;
}


/**
 * Get listing author id.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @param int|string $listing_id The post ID.
 * @return string|int The author ID.
 */
function geodir_get_listing_author($listing_id = '')
{
    if ($listing_id == '') {
        if (isset($_REQUEST['pid']) && $_REQUEST['pid'] != '') {
            $listing_id = $_REQUEST['pid'];
        }
    }
    $listing = get_post(strip_tags($listing_id));
    $listing_author_id = $listing->post_author;
    return $listing_author_id;
}


/**
 * Check whether a listing belongs to a user or not.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @param int|string $listing_id The post ID.
 * @param int $user_id The user ID.
 * @return bool
 */
function geodir_lisiting_belong_to_user($listing_id, $user_id)
{
    $listing_author_id = geodir_get_listing_author($listing_id);
    if ($listing_author_id == $user_id)
        return true;
    else
        return false;

}

/**
 * Check whether a listing belongs to current user or not.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $current_user Current user object.
 * @param int|string $listing_id The post ID.
 * @param bool $exclude_admin Optional. Do you want to exclude admin from the check?. Default true.
 * @return bool
 */
function geodir_listing_belong_to_current_user($listing_id = '', $exclude_admin = true)
{
    global $current_user;
    if ($exclude_admin) {
        foreach ($current_user->caps as $key => $caps) {
            if (geodir_strtolower($key) == 'administrator') {
                return true;
                break;
            }
        }
    }

    return geodir_lisiting_belong_to_user($listing_id, $current_user->ID);
}


/**
 * Delete only supported attachments. This function is hooked to wp_delete_file filter.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @param string $file. The File path.
 * @return string File path if valid. Else empty string.
 */
function geodir_only_supportable_attachments_remove($file)
{

    global $wpdb;

    $matches = array();

    $pattern = '/-\d+x\d+\./';
    preg_match($pattern, $file, $matches, PREG_OFFSET_CAPTURE);

    if (empty($matches))
        return '';
    else
        return $file;

}




/**
 * Function to copy custom meta info on WPML copy.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @global string $table_prefix WordPress Database Table prefix.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 */
function geodir_copy_original_translation()
{
    if (function_exists('icl_object_id')) {
        global $wpdb, $table_prefix, $plugin_prefix;
        $post_id = absint($_POST['post_id']);
        $upload_dir = wp_upload_dir();
        $post_type = get_post_type($_POST['post_id']);
        $table = $plugin_prefix . $post_type . '_detail';

        $post_arr = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $wpdb->posts p JOIN " . $table . " gd ON gd.post_id=p.ID WHERE p.ID=%d LIMIT 1",
            array($post_id)
        )
            , ARRAY_A);

        $arrImages = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM " . GEODIR_ATTACHMENT_TABLE . " WHERE mime_type like %s AND post_id = %d ORDER BY menu_order ASC, ID DESC ",
                array('%image%', $post_id)
            )
        );
        if ($arrImages) {
            $image_arr = array();
            foreach ($arrImages as $img) {
                $image_arr[] = $upload_dir['baseurl'] . $img->file;
            }
            $comma_separated = implode(",", $image_arr);
            $post_arr[0]['post_images'] = $comma_separated;
        }


        $cats = $post_arr[0][$post_arr[0]['post_type'] . 'category'];
        $cat_arr = array_filter(explode(",", $cats));
        $trans_cat = array();
        foreach ($cat_arr as $cat) {
            $trans_cat[] = icl_object_id($cat, $post_arr[0]['post_type'] . 'category', false);
        }


        $post_arr[0]['categories'] = array_filter($trans_cat);
//print_r($image_arr);
        //print_r($arrImages);
        //echo $_REQUEST['lang'];
//print_r($post_arr);
//print_r($trans_cat);
        echo json_encode($post_arr[0]);

    }
    die();
}


add_action('wp_ajax_gd_copy_original_translation', 'geodir_copy_original_translation');
//add_action('wp_ajax_nopriv_dc_update_profile', 'dc_update_profile_callback');




/**
 * Called when post updated.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @param int $post_ID The post ID.
 * @param object $post_after The post object after update.
 * @param object $post_before The post object before update.
 */
function geodir_function_post_updated($post_ID, $post_after, $post_before)
{
    $post_type = get_post_type($post_ID);

    if ($post_type != '' && in_array($post_type, geodir_get_posttypes())) {
        // send notification to client when post moves from draft to publish
        if (!empty($post_after->post_status) && $post_after->post_status == 'publish' && !empty($post_before->post_status) && $post_before->post_status != 'publish' && $post_before->post_status != 'trash') {
            $gd_post = geodir_get_post_info( $post_ID );
			if ( empty( $gd_post ) ) {
				return;
			}
			// Send email to usre
			GeoDir_Email::send_user_publish_post_email( $gd_post );
        }
    }
}

add_action('wp_head', 'geodir_fb_like_thumbnail');


/**
 * Adds the featured image to the place details page header so facebook can use it when sharing the link.
 *
 * @since 1.4.9
 * @package GeoDirectory
 */
function geodir_fb_like_thumbnail(){

    // return if not a single post
    if(!is_single()){return;}

    global $gd_post;
    if(isset($gd_post->featured_image) && $gd_post->featured_image){
        $upload_dir = wp_upload_dir();
        $thumb = $upload_dir['baseurl'].$gd_post->featured_image;
        echo "\n\n<!-- GD Facebook Like Thumbnail -->\n<link rel=\"image_src\" href=\"$thumb\" />\n<!-- End GD Facebook Like Thumbnail -->\n\n";

    }
}



/**
 * Limit the listing excerpt.
 *
 * This function limits excerpt characters and display "read more" link.
 *
 * @since   1.0.0
 * @package GeoDirectory
 *
 * @param string|int $charlength The character length.
 *
 * @global object $post          The current post object.
 * @return string The modified excerpt.
 */
function geodir_max_excerpt( $charlength ) {
    global $post;
    if ( $charlength == '0' ) {
        return;
    }
    $out = '';

    $temp_post = $post;
    $excerpt   = get_the_excerpt();

    $charlength ++;
    $excerpt_more = function_exists( 'geodirf_excerpt_more' ) ? geodirf_excerpt_more( '' ) : geodir_excerpt_more( '' );
    if ( geodir_utf8_strlen( $excerpt ) > $charlength ) {
        if ( geodir_utf8_strlen( $excerpt_more ) > 0 && geodir_utf8_strpos( $excerpt, $excerpt_more ) !== false ) {
            $excut = - ( geodir_utf8_strlen( $excerpt_more ) );
            $subex = geodir_utf8_substr( $excerpt, 0, $excut );
            if ( $charlength > 0 && geodir_utf8_strlen( $subex ) > $charlength ) {
                $subex = geodir_utf8_substr( $subex, 0, $charlength );
            }
            $out .= $subex;
        } else {
            $subex   = geodir_utf8_substr( $excerpt, 0, $charlength - 5 );
            $exwords = explode( ' ', $subex );
            $excut   = - ( geodir_utf8_strlen( $exwords[ count( $exwords ) - 1 ] ) );
            if ( $excut < 0 ) {
                $out .= geodir_utf8_substr( $subex, 0, $excut );
            } else {
                $out .= $subex;
            }
        }
        $out .= ' <a class="excerpt-read-more" href="' . get_permalink() . '" title="' . get_the_title() . '">';
        /**
         * Filter excerpt read more text.
         *
         * @since 1.0.0
         */
        $out .= apply_filters( 'geodir_max_excerpt_end', __( 'Read more [...]', 'geodirectory' ) );
        $out .= '</a>';

    } else {
        if ( geodir_utf8_strlen( $excerpt_more ) > 0 && geodir_utf8_strpos( $excerpt, $excerpt_more ) !== false ) {
            $excut = - ( geodir_utf8_strlen( $excerpt_more ) );
            $out .= geodir_utf8_substr( $excerpt, 0, $excut );
            $out .= ' <a class="excerpt-read-more" href="' . get_permalink() . '" title="' . get_the_title() . '">';
            /**
             * Filter excerpt read more text.
             *
             * @since 1.0.0
             */
            $out .= apply_filters( 'geodir_max_excerpt_end', __( 'Read more [...]', 'geodirectory' ) );
            $out .= '</a>';
        } else {
            $out .= $excerpt;
        }
    }
    $post = $temp_post;

    return $out;
}

function geodir_get_custom_statuses() {
	$custom_statuses = array(
		'gd-closed' => _x( 'Closed down', 'Listing status', 'geodirectory' )
	);

    return apply_filters( 'geodir_listing_custom_statuses', $custom_statuses );
}

function geodir_get_post_statuses() {
    $default_statuses = get_post_statuses();
	$custom_statuses = geodir_get_custom_statuses();

	$statuses = array_merge( $default_statuses, $custom_statuses );

    return apply_filters( 'geodir_post_statuses', $statuses );
}

/**
 * Get the nice name for an listing status.
 *
 * @since  2.0.0
 * @param  string $status
 * @return string
 */
function geodir_get_post_status_name( $status ) {
	$statuses = geodir_get_post_statuses();
	if ( ! empty( $statuses ) && isset( $statuses[ $status ] ) ) {
		$status_name = $statuses[ $status ];
	} else {
		$status_object = get_post_status_object( $status );
		if ( ! empty( $status_object->label ) ) {
			$status_name = $status_object->label;
		} else {
			$status_name = $status;
		}
	}
	return $status_name;
}

function geodir_post_is_closed( $post ) {
	if ( empty( $post ) ) {
		return false;
	}
	
	$status = ! empty( $post->post_status ) ? $post->post_status : get_post_status( $post );
	$closed = $status == 'gd-closed' ? true : false;
	
	return apply_filters( 'geodir_post_is_closed', $closed, $post );
}



/**
 * Returns the edit post link.
 *
 * @since 2.0.0
 * @package GeoDirectory
 */
function geodir_edit_post_link($post_id = '')
{

    if(!$post_id){
        global $post;
        $post_id = $post->ID;
    }

    $postlink = get_permalink(geodir_add_listing_page_id());
    return  geodir_getlink($postlink, array('pid' => $post_id), false);

}

/*
 * Setup $gd_post variable.
 */
function geodir_setup_postdata( $the_post ) {
	global $post;

	if ( is_int( $the_post ) && $the_post > 0 ) {
		$the_post = geodir_get_post_info( $the_post );
	} else if ( is_object( $the_post ) ) {
		if ( ! isset( $the_post->post_category ) ) {
			$the_post = geodir_get_post_info( $post->ID );
		}
	}

	if ( empty( $the_post->ID ) ) {
		return;
	}

	$GLOBALS['gd_post'] = $the_post;

	if ( $post->ID != $the_post->ID ) {
		setup_postdata( $the_post->ID );
		if ( $post->ID != $the_post->ID ) {
			$GLOBALS['post'] = get_post( $the_post->ID );
		}
	}
}