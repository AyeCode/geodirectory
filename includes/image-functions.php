<?php
/**
 * Post image functions.
 *
 * @since 2.0.0
 * @package GeoDirectory
 */

function geodir_image_tag_ajaxify($img_tag,$lazy_load = true){

    
    $strip = array("src=","srcset=");
    $replace = array("src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAAABCAQAAACC0sM2AAAADklEQVR42mP8X88wLAAAK5IBgMYCdqgAAAAASUVORK5CYII=' data-src=","data-srcset=");
    if($lazy_load){
        $strip[] = 'class="';
        $replace[] = 'class="geodir-lazy-load ';
    }
    $img_tag = str_replace($strip,$replace,$img_tag);

    return $img_tag;
}

function geodir_get_image_src($image, $size = 'medium'){
    $img_src = '';

    $meta = isset($image->metadata) ? maybe_unserialize($image->metadata) : '';
    $upload_dir = wp_upload_dir();
    if(isset($meta['sizes']) && $size){
        $img_url_basename = wp_basename($upload_dir['baseurl'].$image->file);
        if($img_url_basename && isset($meta['sizes'][$size]) && isset($meta['sizes'][$size]['file']) && $meta['sizes'][$size]['file']){
            $img_src = str_replace($img_url_basename, wp_basename($meta['sizes'][$size]['file']), $upload_dir['baseurl'].$image->file);
        }
    }

    // no sizes just return full size
    if(!$img_src){
        if(isset($image->file)){
            $img_src = $upload_dir['baseurl'].$image->file;
        }
    }

    return $img_src;
}

function geodir_get_image_tag( $image, $size = 'medium',$align = '' ) {
    //function geodir_get_image_tag( $id, $alt, $title, $align, $size = 'medium' ) {

    //list( $img_src, $width, $height ) = image_downsize($id, $size);

    $meta = isset($image->metadata) ? maybe_unserialize($image->metadata) : '';

    $img_src = geodir_get_image_src($image, $size);

    //print_r($meta);exit;
    $width = isset($meta['width']) ? $meta['width'] : '';
    $height = isset($meta['height']) ? $meta['height'] : '';
    $hwstring = image_hwstring($width, $height);

    $id = isset($image->ID) ? esc_attr( $image->ID ) : 0;
    $title = isset( $image->title ) && $image->title ? 'title="' . esc_attr( $image->title ) . '" ' : '';
    $alt = isset( $image->caption ) && $image->caption ? $image->caption : 'image-'.$id;
    $class = 'align' . esc_attr($align) .' size-' . esc_attr($size) . ' geodir-image-' . $id;

    /**
     * Filters the value of the attachment's image tag class attribute.
     *
     * @since 2.0.0
     *
     * @param string       $class CSS class name or space-separated list of classes.
     * @param int          $id    Attachment ID.
     * @param string       $align Part of the class name for aligning the image.
     * @param string|array $size  Size of image. Image size or array of width and height values (in that order).
     *                            Default 'medium'.
     */
    $class = apply_filters( 'geodir_get_image_tag_class', $class, $id, $align, $size );

    $html = '<img src="' . esc_attr($img_src) . '" alt="' . esc_attr($alt) . '" ' . $title . $hwstring . 'class="' . $class . '" />';

    /**
     * Filters the HTML content for the image tag.
     *
     * @since 2.0.0
     *
     * @param string       $html  HTML content for the image.
     * @param int          $id    Attachment ID.
     * @param string       $alt   Alternate text.
     * @param string       $title Attachment title.
     * @param string       $align Part of the class name for aligning the image.
     * @param string|array $size  Size of image. Image size or array of width and height values (in that order).
     *                            Default 'medium'.
     */
    return apply_filters( 'geodir_get_image_tag', $html, $id, $alt, $title, $align, $size );
}





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
function geodir_get_images($post_id = 0, $limit = '')
{

    $post_images = GeoDir_Media::get_post_images($post_id,$limit);

//    print_r( $post_images );
    if(!empty($post_images)){

        // wp_image_add_srcset_and_sizes( $image, $image_meta, $attachment_id );

    }else{


        $default_img_id = '';

        // no image code

        // cat image
        if(geodir_is_page('archive')){
            if($term_id = get_queried_object_id()){
                $term_img = get_term_meta( $term_id, 'ct_cat_default_img', true);
            }
        }

        if(empty($term_img)){
            $default_term_id = geodir_get_post_meta($post_id,'default_category');
            if($default_term_id){
                $term_img = get_term_meta( $default_term_id, 'ct_cat_default_img', true);
            }
        }

        if(!empty($term_img)){
            $default_img_id = $term_img['id'];
        }else{
            $listing_default_image_id = geodir_get_option('listing_default_image');
            if( $listing_default_image_id ){
                $default_img_id = $listing_default_image_id;
            }
        }

        // default image
        if($default_img_id){
            $default_image_post = get_post($default_img_id);

            if($default_image_post){

                $wp_upload_dir = wp_upload_dir();

                $post_images = array();
                $image = new stdClass();
                $image->ID = 0;
                $image->post_id = $default_image_post->ID;
                $image->user_id = 0;
                $image->title = __('Placeholder image','geodirectory');
                $image->caption = '';
                $image->file = str_replace($wp_upload_dir['basedir'],'', get_attached_file( $default_img_id));
                $image->mime_type = $default_image_post->post_mime_type;
                $image->menu_order = 0;
                $image->featured= 0;
                $image->is_approved= 1;
                $image->metadata= wp_get_attachment_metadata( $default_img_id );
                $image->type = 'post_images';
                $post_images[] =  $image;
            }

        }


    }

// print_r($post_images);


//    (
//    [ID] => 90
//            [post_id] => 51
//            [user_id] => 1
//            [title] =>
//             =>
//            [file] => /2018/03/psf8-1.jpg
//[mime_type] => image/jpeg
//[menu_order] => 0
//            [featured] => 1
//            [is_approved] => 1
//            [metadata] => a:4:{s:5:"width";i:1280;s:6:"height";i:856;s:4:"file";s:19:"/2018/03/psf8-1.jpg";s:10:"image_meta";a:12:{s:8:"aperture";s:1:"0";s:6:"credit";s:0:"";s:6:"camera";s:0:"";s:7:"caption";s:0:"";s:17:"created_timestamp";s:1:"0";s:9:"copyright";s:0:"";s:12:"focal_length";s:1:"0";s:3:"iso";s:1:"0";s:13:"shutter_speed";s:1:"0";s:5:"title";s:0:"";s:11:"orientation";s:1:"0";s:8:"keywords";a:0:{}}}
//            [type] => post_image
//        )




    return $post_images;
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