<?php
/**
 * Hook and filter actions used by the plugin
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global array $geodir_addon_list List of active GeoDirectory extensions.
 * @global string $plugin_file_name Plugin main file name. 'geodirectory/geodirectory.php'.
 */










////////////////////////
/* WP REVIEW COUNT ACTIONS */
////////////////////////

add_action('geodir_update_postrating', 'geodir_term_review_count_force_update_single_post', 100,1);
//add_action('geodir_update_postrating', 'geodir_term_review_count_force_update', 100);
add_action('transition_post_status', 'geodir_term_review_count_force_update', 100,3);
//add_action('created_term', 'geodir_term_review_count_force_update', 100);
add_action('edited_term', 'geodir_term_review_count_force_update', 100);
add_action('delete_term', 'geodir_term_review_count_force_update', 100);

////////////////////////
/* WP CAT META UPDATE ACTIONS */
////////////////////////
add_action('gd_tax_meta_updated', 'geodir_get_term_icon_rebuild', 5000);
////////////////////////
/* WP FOOTER ACTIONS */
////////////////////////




/* Sharelocation scripts */
//global $geodir_addon_list;
//if(!empty($geodir_addon_list) && array_key_exists('geodir_sharelocation_manager', $geodir_addon_list) && $geodir_addon_list['geodir_sharelocation_manager'] == 'yes') { 
add_action('wp_footer', 'geodir_add_sharelocation_scripts');
//}






/* Sidebar */


/* Pagination in loop-store */
add_action('geodir_pagination', 'geodir_pagination', 10);





/* ------------------------------START CODE FOR HIDE/DISPLAY TABS */















/*   --------- geodir remove url seperator ------- */



if (!is_admin()) {
    add_filter('posts_results', 'geodir_set_status_draft_to_publish_for_own_post');
}
/**
 * Set status from draft to publish.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wp WordPress object.
 * @param object $post Post object.
 * @return object Modified post object.
 */
function geodir_set_status_draft_to_publish_for_own_post($post)
{
	if (is_single()) {
		$current_post = ! empty( $post[0]->post_status ) ?  $post[0] :  $post;
		if ( geodir_post_is_closed($current_post) ) {
			add_filter( 'comments_open', '__return_false', 9999, 2 );
			add_filter( 'pings_open', '__return_false', 9999, 2 );
			return $post;
		}
	}
    $user_id = get_current_user_id();

    if(!$user_id){return $post;}

    $gd_post_types = geodir_get_posttypes();

    if (!empty($post) && $post[0]->post_author == $user_id && in_array($post[0]->post_type, $gd_post_types) && !isset($_REQUEST['fl_builder'])) {
        $post[0]->post_status = 'publish';
    }
    return $post;
}






/*
 * hook action for post updated
 */
add_action('post_updated', 'geodir_function_post_updated', 16, 3);


add_action('geodir_after_edit_post_link_on_listing', 'geodir_add_post_status_author_page', 11);

/**
 * Adds post status on author page when the author is current user.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @global object $post The current post object.
 */
function geodir_add_post_status_author_page()
{
    global $wpdb, $post;

    $html = '';
    if (get_current_user_id()) {

        $is_author_page = apply_filters('geodir_post_status_is_author_page', geodir_is_page('author'));
        if ($is_author_page && !empty($post) && isset($post->post_author) && $post->post_author == get_current_user_id()) {

            // we need to query real status direct as we dynamically change the status for author on author page so even non author status can view them.
            $real_status = $wpdb->get_var("SELECT post_status from $wpdb->posts WHERE ID=$post->ID");
            $status = "<strong>(";
            $status_icon = '<i class="fa fa-play"></i>';
            if ($real_status == 'publish') {
                $status .= __('Published', 'geodirectory');
            } else {
                $status .= __('Not published', 'geodirectory');
                $status_icon = '<i class="fa fa-pause"></i>';
            }
            $status .= ")</strong>";

            $html = '<span class="geodir-post-status">' . $status_icon . ' <font class="geodir-status-label">' . __('Status: ', 'geodirectory') . '</font>' . $status . '</span>';
        }
    }

    if ($html != '') {
        /**
         * Filter the post status text on the author page.
         *
         * @since 1.0.0
         * @param string $html The HTML of the status.
         */
        echo apply_filters('geodir_filter_status_text_on_author_page', $html);
    }


}

add_action('init', 'geodir_init_no_rating', 100);
/**
 * remove rating stars fields if disabled.
 *
 * @since 1.0.0
 * @since 1.6.16 Changes for disable review stars for certain post type.
 * @package GeoDirectory
 */
function geodir_init_no_rating() {
    if (geodir_rating_disabled_post_types()) {
        add_filter('geodir_get_sort_options', 'geodir_no_rating_get_sort_options', 100, 2);
    }
}

/**
 * Skip overall rating sort option when rating disabled.
 *
 * @since 1.0.0
 * @since 1.6.16 Changes for disable review stars for certain post type.
 * @package GeoDirectory
 * @param array $options Sort options array.
 * @param string $post_type The post type.
 * @return array Modified sort options array.
 */
function geodir_no_rating_get_sort_options($options, $post_type = '') {
    if (!empty($post_type) && geodir_cpt_has_rating_disabled($post_type)) {
        $new_options = array();
        
        if (!empty($options)) {
            foreach ($options as $option) {
                if (is_object($option) && isset($option->htmlvar_name) && $option->htmlvar_name == 'overall_rating') {
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
 * Add body class for current active map.
 *
 * @since 1.6.16
 * @package GeoDirectory
 * @param array $classes The class array of the HTML element.
 * @return array Modified class array.
 */
function geodir_body_class_active_map($classes = array()) {
    $classes[] = 'gd-map-' . geodir_map_name();

    return $classes;
}
add_filter('body_class', 'geodir_body_class_active_map', 100);

/**
 * Add body class for current active map.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @param string $class The class of the HTML element.
 * @return string Modified class string.
 */
function geodir_admin_body_class_active_map($class = '') {    
    $class .= ' gd-map-' . geodir_map_name();

    return $class;
}
add_filter('admin_body_class', 'geodir_admin_body_class_active_map', 100);

add_filter('geodir_load_db_language', 'geodir_load_custom_field_translation');

add_filter('geodir_load_db_language', 'geodir_load_cpt_text_translation');

/**
 * Get the geodirectory notification subject & content texts for translation.
 *
 * @since 1.5.7
 * @package GeoDirectory
 *
 * @param  array $translation_texts Array of text strings.
 * @return array Translation texts.
 */
function geodir_load_gd_options_text_translation($translation_texts = array()) {
    $translation_texts = !empty( $translation_texts ) && is_array( $translation_texts ) ? $translation_texts : array();

    $gd_options = array('geodir_post_submited_success_email_subject_admin', 'geodir_post_submited_success_email_content_admin', 'geodir_post_submited_success_email_subject', 'geodir_post_submited_success_email_content', 'geodir_forgot_password_subject', 'geodir_forgot_password_content', 'geodir_registration_success_email_subject', 'geodir_registration_success_email_content', 'geodir_post_published_email_subject', 'geodir_post_published_email_content', 'geodir_email_friend_subject', 'geodir_email_friend_content', 'geodir_email_enquiry_subject', 'geodir_email_enquiry_content', 'geodir_post_added_success_msg_content', 'geodir_post_edited_email_subject_admin', 'geodir_post_edited_email_content_admin');

    /**
     * Filters the geodirectory option names that requires to add for translation.
     *
     * @since 1.5.7
     * @package GeoDirectory
     *
     * @param  array $gd_options Array of option names.
     */
    $gd_options = apply_filters('geodir_gd_options_for_translation', $gd_options);
    $gd_options = array_unique($gd_options);

    if (!empty($gd_options)) {
        foreach ($gd_options as $gd_option) {
            if ($gd_option != '' && $option_value = geodir_get_option($gd_option)) {
                $option_value = is_string($option_value) ? stripslashes_deep($option_value) : '';
                
                if ($option_value != '' && !in_array($option_value, $translation_texts)) {
                    $translation_texts[] = stripslashes_deep($option_value);
                }
            }
        }
    }

    $translation_texts = !empty($translation_texts) ? array_unique($translation_texts) : $translation_texts;

    return $translation_texts;
}

add_filter('geodir_load_db_language', 'geodir_load_gd_options_text_translation');









