<?php
/**
 * Hook and filter actions used by the plugin
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global array $geodir_addon_list List of active GeoDirectory extensions.
 * @global string $plugin_file_name Plugin main file name. 'geodirectory/geodirectory.php'.
 */

/**
 * Return the GeoDirectory ajax specific url.
 *
 * This is used to run GeoDirectory specific functions via ajax.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @return string The GeoDirectory ajax URL.
 */
//function geodir_get_ajax_url()
//{
//    return admin_url('admin-ajax.php?action=geodir_ajax_action');
//}
//return;
/////////////////////
/* ON INIT ACTIONS */
/////////////////////

//add_action('init', 'geodir_on_init', 1);

add_action('init', 'geodir_add_post_filters');

//add_action('init', 'geodir_init_defaults');


//add_action('init', 'geodir_register_taxonomies', 1);

//add_action('init', 'geodir_register_post_types', 2);

//add_filter('geodir_post_type_args', 'geodir_post_type_args_modify', 0, 2);

//add_action( 'init', 'geodir_flush_rewrite_rules', 99 );

//add_action('widgets_init', 'geodir_register_sidebar'); // Takes care of widgets

global $geodir_addon_list;
/**
 * Build an array of installed addons.
 *
 * This filter builds an array of installed addons which can be used to check what exactly is installed.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @param array $geodir_addon_list The array of installed plugins $geodir_addon_list['geodir_location_manager'].
 */
apply_filters('geodir_build_addon_list', $geodir_addon_list);


/////////////////////////////
/* ON WP HEAD ACTIONS */
/////////////////////////////

add_action('wp_head', 'geodir_init_map_jason'); // Related to MAP

add_action('wp_head', 'geodir_init_map_canvas_array'); // Related to MAP

add_action('wp_head', 'geodir_restrict_widget'); // Related to widgets


/////////////////////////
/* CATEGORY / TAXONOMY / CUSTOM POST ACTIONS */
/////////////////////////

add_filter('term_link', 'geodir_get_term_link', 10, 3);

add_filter('post_type_archive_link', 'geodir_get_posttype_link', 10, 2);

////////////////////////
/* POST AND LOOP ACTIONS */
////////////////////////
if (!is_admin()) {
    /** Exclude Virtual Pages From Pages List **/
    //add_action('pre_get_posts', 'set_listing_request', 0);
    //add_action('pre_get_posts', 'geodir_listing_loop_filter', 1);
    add_filter('excerpt_more', 'geodir_excerpt_more', 1000);
    add_filter('excerpt_length', 'geodir_excerpt_length', 1000);
    add_action('the_post', 'create_marker_jason_of_posts'); // Add marker in json array, Map related filter
}


//add_action('set_object_terms', 'geodir_set_post_terms', 10, 4);




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

add_action('wp_footer', 'geodir_footer_scripts'); /* Footer Scripts loader */

add_action('wp_footer', 'send_marker_jason_to_js'); // Show map for listings with markers



/* Sharelocation scripts */
//global $geodir_addon_list;
//if(!empty($geodir_addon_list) && array_key_exists('geodir_sharelocation_manager', $geodir_addon_list) && $geodir_addon_list['geodir_sharelocation_manager'] == 'yes') { 
add_action('wp_footer', 'geodir_add_sharelocation_scripts');
//}



/**
 * Includes the file that adds filters/functions to change the database queries.
 *
 * @since 1.0.0
 * @package GeoDirectory
 */
function geodir_add_post_filters() {
    /**
     * Contains all function for filtering listing.
     *
     * @since 1.0.0
     * @package GeoDirectory
     */
    include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/listing_filters.php' );
    
    // Theme My Login compatibility fix
    if ( isset( $_REQUEST['geodir_search'] ) && class_exists( 'Theme_My_Login' ) ) {
        remove_action( 'pre_get_posts', array( Theme_My_Login::get_object(), 'pre_get_posts' ) );
    }
}



/* Sidebar */


/* Pagination in loop-store */
add_action('geodir_pagination', 'geodir_pagination', 10);


/** Add Custom Menu Items **/


/** Replaces "Post" in the update messages for custom post types on the "Edit" post screen. **/
add_filter('post_updated_messages', 'geodir_custom_update_messages');


// CALLED ON 'sidebars_widgets' FILTER

if (!function_exists('geodir_restrict_widget')) {
    /**
     * Sets global values to be able to tell if the current page is a GeoDirectory listing page or a GeoDirectory details page.
     *
     * @global bool $is_listing Sets the global value to true if on a GD category page. False if not.
     * @global bool $is_single_place Sets the global value to true if on a GD details (post) page. False if not.
     * @since 1.0.0
     * @package GeoDirectory
     */
    function geodir_restrict_widget()
    {
        global $is_listing, $is_single_place;

        // set is listing	
        (geodir_is_page('listing')) ? $is_listing = true : $is_listing = false;

        // set is single place
        (geodir_is_page('place')) ? $is_single_place = true : $is_single_place = false;


    }
}


/////// GEO DIRECOTORY CUSTOM HOOKS ///

add_action('geodir_before_tab_content', 'geodir_before_tab_content');// this function is in custom_functions.php and it is used to wrap detail page tab content 
add_action('geodir_after_tab_content', 'geodir_after_tab_content');// this function is in custom_functions.php and it is used to wrap detail page tab content

// Detail page sidebar content 
//add_action('geodir_detail_page_sidebar', 'geodir_detail_page_sidebar_content_sorting', 1);
/**
 * Builds an array of elements for the details (post) page sidebar.
 *
 * Builds an array of functions to be called in the details page (post) sidebar, this array can be changed via hook or filter.
 *
 * @since 1.0.0
 * @package GeoDirectory
 */
function geodir_detail_page_sidebar_content_sorting()
{
    $arr_detail_page_sidebar_content =
        /**
         * An array of functions to be called to be displayed on the details (post) page sidebar.
         *
         * This filter can be used to remove sections of the details page sidebar,
         * add new sections or rearrange the order of the sections.
         *
         * @param array array('geodir_social_sharing_buttons','geodir_share_this_button','geodir_edit_post_link','geodir_detail_page_review_rating','geodir_detail_page_more_info') The array of functions that will be called.
         * @since 1.0.0
         */
        apply_filters('geodir_detail_page_sidebar_content',
            array('geodir_social_sharing_buttons',
                'geodir_edit_post_link',
                'geodir_detail_page_review_rating',
                'geodir_detail_page_more_info'
            ) // end of array 
        ); // end of apply filter
    if (!empty($arr_detail_page_sidebar_content)) {
        foreach ($arr_detail_page_sidebar_content as $content_function) {
            if (function_exists($content_function)) {
                add_action('geodir_detail_page_sidebar', $content_function);
            }
        }
    }
}

add_action('geodir_after_edit_post_link', 'geodir_add_to_favourite_link', 1);

/**
 * Outputs the add to favourite line for the current post if not add listing preview page.
 *
 * @global object $post The current post object.
 * @global bool $preview True if the current page is add listing preview page. False if not.
 * since 1.0.0
 * @package GeoDirectory
 */
function geodir_add_to_favourite_link()
{
    global $post, $preview;
    if (!$preview && geodir_is_page('detail')) {
        ?>
        <p class="edit_link">
            <?php geodir_favourite_html($post->post_author, $post->ID); ?>
        </p>
    <?php
    }
}


/**
 * Output the current post overall review and a small image compatible with google hreviews.
 *
 * @global WP_Post|null $post The current post, if available.
 * @global bool $preview True if the current page is add listing preview page. False if not.
 * @global object $post_images Image objects of current post if available.
 * @since 1.0.0
 * @since 1.6.16 Changes for disable review stars for certain post type.
 * @deprecated 1.6.3 Use geodir_action_details_micordata()
 * @see geodir_action_details_micordata()
 * @package GeoDirectory
 */
function geodir_detail_page_review_rating()
{
    global $post, $preview, $post_images;
    
    if (!empty($post->ID) && geodir_cpt_has_rating_disabled((int)$post->ID)) {
        return;
    }
    ob_start(); // Start  buffering;
    /**
     * This is called before the rating html in the function geodir_detail_page_review_rating().
     *
     * This is called outside the check for an actual rating and the check for preview page.
     *
     * @since 1.0.0
     */
    do_action('geodir_before_detail_page_review_rating');

    $comment_count = geodir_get_review_count_total($post->ID);
    $post_avgratings = geodir_get_post_rating($post->ID);

    if ($post_avgratings != 0 && !$preview) {
        /**
         * This is called before the rating html in the function geodir_detail_page_review_rating().
         *
         * This is called inside the check for an actual rating and the check for preview page.
         *
         * @since 1.0.0
         * @param float $post_avgratings Average rating for the current post.
         * @param int $post->ID Current post ID.
         */
        do_action('geodir_before_review_rating_stars_on_detail', $post_avgratings, $post->ID);

        $html = '<p style=" float:left;">';
        $html .= geodir_get_rating_stars($post_avgratings, $post->ID);
        $html .= '<div class="average-review" itemscope itemtype="http://data-vocabulary.org/Review-aggregate">';
        $post_avgratings = (is_float($post_avgratings) || (strpos($post_avgratings, ".", 1) == 1 && strlen($post_avgratings) > 3)) ? number_format($post_avgratings, 1, '.', '') : $post_avgratings;
       
	   $reviews_text = $comment_count > 1 ? __("reviews", 'geodirectory') : __("review", 'geodirectory');
	   
	   $html .= '<span itemprop="rating" itemscope itemtype="http://data-vocabulary.org/Rating"><span class="rating" itemprop="average" content="' . $post_avgratings . '">' . $post_avgratings . '</span> / <span itemprop="best" content="5">5</span> ' . __("based on", 'geodirectory') . ' </span><span class="count" itemprop="count" content="' . $comment_count . '">' . $comment_count . ' ' . $reviews_text . '</span><br />';

        $html .= '<span class="item">';
        $html .= '<span class="fn" itemprop="itemreviewed">' . $post->post_title . '</span>';

        if ($post_images) {
            foreach ($post_images as $img) {
                $post_img = $img->src;
                break;
            }
        }

        if (isset($post_img) && $post_img) {
            $html .= '<br /><img src="' . $post_img . '" class="photo" alt="' . esc_attr($post->post_title) . '" itemprop="photo" content="' . $post_img . '" class="photo" />';
        }

        $html .= '</span>';

        echo $html .= '</div>';
        /**
         * This is called after the rating html in the function geodir_detail_page_review_rating().
         *
         * This is called inside the check for an actual rating and the check for preview page.
         *
         * @since 1.0.0
         * @param float $post_avgratings Average rating for the current post.
         * @param int $post->ID Current post ID.
         */
        do_action('geodir_after_review_rating_stars_on_detail', $post_avgratings, $post->ID);
    }
    /**
     * This is called before the rating html in the function geodir_detail_page_review_rating().
     *
     * This is called outside the check for an actual rating and the check for preview page.
     *
     * @since 1.0.0
     */
    do_action('geodir_after_detail_page_review_rating');
    $content_html = ob_get_clean();
    if (trim($content_html) != '') {
        $content_html = '<div class="geodir-company_info geodir-details-sidebar-rating">' . $content_html . '</div>';
    }
    if ((int)geodir_get_option('geodir_disable_rating_info_section') != 1) {
        /**
         * Filter the geodir_detail_page_review_rating() function content.
         *
         * @since 1.0.0
         * @param string $content_html The output html of the geodir_detail_page_review_rating() function.
         */
        echo $content_html = apply_filters('geodir_detail_page_review_rating_html', $content_html);
    }
}

/**
 * This outputs the info section of the details page.
 *
 * This outputs the info section of the details page which includes all the post custom fields.
 * @since 1.0.0
 * @package GeoDirectory
 */
function geodir_detail_page_more_info()
{
    ob_start(); // Start  buffering;
    /**
     * This is called before the info section html.
     *
     * @since 1.0.0
     */
    do_action('geodir_before_detail_page_more_info');
    if ($geodir_post_detail_fields = geodir_show_listing_info('detail')) {
        echo $geodir_post_detail_fields;
    }
    /**
     * This is called after the info section html.
     *
     * @since 1.0.0
     */
    do_action('geodir_after_detail_page_more_info');

    $content_html = ob_get_clean();
    if (trim($content_html) != '')
        $content_html = '<div class="geodir-company_info geodir-details-sidebar-listing-info">' . $content_html . '</div>';
    if ((int)geodir_get_option('geodir_disable_listing_info_section') != 1) {
        /**
         * Filter the output html for function geodir_detail_page_more_info().
         *
         * @since 1.0.0
         * @param string $content_html The output html of the geodir_detail_page_more_info() function.
         */
        echo $content_html = apply_filters('geodir_detail_page_more_info_html', $content_html);
    }
}




add_action('admin_bar_menu', 'geodir_admin_bar_site_menu', 31);
/**
 * Add GeoDirectory link to the WordPress admin bar.
 *
 * This function adds a link to the GeoDirectory backend to the WP admin bar via a hook.
 *    add_action('admin_bar_menu', 'geodir_admin_bar_site_menu', 31);
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @param object $wp_admin_bar The admin bar object.
 */
function geodir_admin_bar_site_menu($wp_admin_bar) {
    if ( get_option( 'geodir_installed' ) ) {
        if ( current_user_can( 'manage_options' ) ) {
            $wp_admin_bar->add_menu( array( 'parent' => 'appearance', 'id' => 'geodirectory', 'title' => __( 'GeoDirectory', 'geodirectory' ), 'href' => admin_url( '?page=geodirectory' ) ) );
        }
    }
}

add_action('geodir_before_listing', 'geodir_display_sort_options'); /*function in custom_functions.php*/


add_filter('geodir_advance_custom_fields_heading', 'geodir_advance_customfields_heading', 0, 2);




add_action('geodir_after_listing_post_gridview', 'geodir_after_listing_post_gridview');
/**
 * Set gridview columns value.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global string $gridview_columns The girdview style of the listings.
 */
function geodir_after_listing_post_gridview()
{
    global $gridview_columns;

    $gridview_columns = '';

}

/*
add_filter('script_loader_src' , 'geodir_script_loader_src');

function geodir_script_loader_src($url)
{
	if (strstr($url, "maps") !== false) {
       echo  $url = str_replace("&amp;", "&", $url); // or $url = $original_url
    }
	return $url ;
}*/

add_filter('clean_url', 'so_handle_038', 99, 3);
/**
 * Clean url.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @param string $url Url.
 * @param string $original_url Original url.
 * @param string $_context Context.
 * @return string Modified url.
 */
function so_handle_038($url, $original_url, $_context)
{
    if (strstr($url, "maps.google.com/maps/api/js") !== false) {
        $url = str_replace("&#038;", "&amp;", $url); // or $url = $original_url
    }

    return $url;
}



/* ------------------------------START CODE FOR HIDE/DISPLAY TABS */

add_filter('geodir_detail_page_tab_is_display', 'geodir_detail_page_tab_is_display', 0, 2);

/**
 * Check whether custom field should be displayed or not, on the details page tab.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $post The current post object.
 * @global object $post_images Image objects of current post if available.
 * @global string $video The video embed content.
 * @global string $special_offers Special offers content.
 * @global string $related_listing Related listing html.
 * @global string $geodir_post_detail_fields Detail field html.
 * @param bool $is_display Old display value.
 * @param string $tab Tab type.
 * @return bool New display value. If display returns true.
 */
function geodir_detail_page_tab_is_display($is_display, $tab)
{
    global $post, $post_images, $video, $special_offers, $related_listing, $geodir_post_detail_fields;

    if ($tab == 'post_profile') {
        /** This action is documented in geodirectory_template_actions.php */
        $desc_limit = apply_filters('geodir_description_field_desc_limit', '');
        
        if (!($desc_limit === '' || (int)$desc_limit > 0)) {
            $is_display = false;
        }
    }
    
    if ($tab == 'post_info')
        $is_display = (!empty($geodir_post_detail_fields)) ? true : false;
    
    if ($tab == 'post_images')
        $is_display = (!empty($post_images)) ? true : false;

    if ($tab == 'post_video')
        $is_display = (!empty($video)) ? true : false;

    if ($tab == 'special_offers')
        $is_display = (!empty($special_offers)) ? true : false;

    if ($tab == 'reviews')
        $is_display = (geodir_is_page('detail')) ? true : false;

    if ($tab == 'related_listing') {
       $message = __('No listings found which match your selection.', 'geodirectory');
       
       /** This action is documented in includes/template_functions.php */
       $message = apply_filters('geodir_message_listing_not_found', $message, 'listing-listview', false);
       
       $is_display = ((strpos($related_listing, $message) !== false || $related_listing == '' || !geodir_is_page('detail'))) ? false : true;
    }

    return $is_display;
}


//add_action('wp', 'geodir_changes_in_custom_fields_table');
//add_action('wp_admin', 'geodir_changes_in_custom_fields_table');

/**
 * Geodirectory updated custom field table(add field and change show in sidebar value in db).
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 */
function geodir_changes_in_custom_fields_table() {
    global $wpdb, $plugin_prefix;
	
	// Remove unused virtual page
	$listings_page_id = (int)geodir_get_option('geodir_listing_page');
	if ($listings_page_id) {
		$wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->posts . " WHERE ID=%d AND post_name = %s AND post_type=%s", array($listings_page_id, 'listings', 'page')));
        geodir_delete_option('geodir_listing_page');
	}

    if (!geodir_get_option('geodir_changes_in_custom_fields_table')) {
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE " . GEODIR_CUSTOM_FIELDS_TABLE . " SET is_default=%s, is_admin=%s WHERE is_default=%s",
                array('1', '1', 'admin')
            )
        );


        /* --- terms meta value set --- */
        $options_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "options WHERE option_name LIKE %s", array('%tax_meta_%')));

        if (!empty($options_data)) {

            foreach ($options_data as $optobj) {

                $option_val = str_replace('tax_meta_', '', $optobj->option_name);

                $taxonomies_data = $wpdb->get_results($wpdb->prepare("SELECT taxonomy FROM " . $wpdb->prefix . "term_taxonomy WHERE taxonomy LIKE %s AND term_id=%d", array('%category%', $option_val)));

                if (!empty($taxonomies_data)) {

                    foreach ($taxonomies_data as $taxobj) {

                        $taxObject = get_taxonomy($taxobj->taxonomy);
                        $post_type = $taxObject->object_type[0];

                        $opt_value = 'tax_meta_' . $post_type . '_' . $option_val;

                        $duplicate_data = $wpdb->get_var($wpdb->prepare("SELECT option_id FROM " . $wpdb->prefix . "options WHERE option_name=%s", array('tax_meta_' . $option_val)));

                        if ($duplicate_data) {

                            $wpdb->query($wpdb->prepare("UPDATE " . $wpdb->prefix . "options SET	option_name=%s WHERE option_id=%d", array($opt_value, $optobj->option_id)));

                        } else {

                            $wpdb->query($wpdb->prepare("INSERT INTO " . $wpdb->prefix . "options (option_name,option_value,autoload) VALUES (%s, %s, %s)", array($opt_value, $optobj->option_value, $optobj->autoload)));

                        }

                    }

                }

            }
        }

        geodir_update_option('geodir_changes_in_custom_fields_table', '1');

    }

}


add_filter('geodir_location_slug_check', 'geodir_location_slug_check');
/**
 * Check location slug.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @global string $table_prefix WordPress Database Table prefix.
 * @param string $slug Term slug.
 * @return string Modified term slug.
 */
function geodir_location_slug_check($slug)
{

    global $wpdb, $table_prefix;

    $slug_exists = $wpdb->get_var($wpdb->prepare("SELECT slug FROM " . $table_prefix . "terms WHERE slug=%s", array($slug)));

    if ($slug_exists) {

        $suffix = 1;
        do {
            $alt_location_name = _truncate_post_slug($slug, 200 - (strlen($suffix) + 1)) . "-$suffix";
            $location_slug_check = $wpdb->get_var($wpdb->prepare("SELECT slug FROM " . $table_prefix . "terms WHERE slug=%s", array($alt_location_name)));
            $suffix++;
        } while ($location_slug_check && $suffix < 100);

        $slug = $alt_location_name;

    }

    return $slug;

}


add_action('edited_term', 'geodir_update_term_slug', '1', 3);
add_action('create_term', 'geodir_update_term_slug', '1', 3);


/**
 * Update term slug.
 *
 * @since 1.0.0
 * @since 1.5.3 Modified to update tag in detail table when tag updated.
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 * @global string $table_prefix WordPress Database Table prefix.
 * @param int|string $term_id The term ID.
 * @param int $tt_id term Taxonomy ID.
 * @param string $taxonomy Taxonomy slug.
 */
function geodir_update_term_slug($term_id, $tt_id, $taxonomy)
{

    global $wpdb, $plugin_prefix, $table_prefix;

    $tern_data = get_term_by('id', $term_id, $taxonomy);

    $slug = $tern_data->slug;

    /**
     * Filter if a term slug exists.
     *
     * @since 1.0.0
     * @package GeoDirectory
     * @param bool $bool Default: false.
     * @param string $slug The term slug.
     * @param int $term_id The term ID.
     */
    $slug_exists = apply_filters('geodir_term_slug_is_exists', false, $slug, $term_id);

    if ($slug_exists) {

        $suffix = 1;
        do {
            $new_slug = _truncate_post_slug($slug, 200 - (strlen($suffix) + 1)) . "-$suffix";

            /** This action is documented in geodirectory_hooks_actions.php */
            $term_slug_check = apply_filters('geodir_term_slug_is_exists', false, $new_slug, $term_id);

            $suffix++;
        } while ($term_slug_check && $suffix < 100);

        $slug = $new_slug;

        //wp_update_term( $term_id, $taxonomy, array('slug' => $slug) );

        $wpdb->query($wpdb->prepare("UPDATE " . $table_prefix . "terms SET slug=%s WHERE term_id=%d", array($slug, $term_id)));

    }
	
	// Update tag in detail table.
	$taxonomy_obj = get_taxonomy($taxonomy);
	$post_type = !empty($taxonomy_obj) ? $taxonomy_obj->object_type[0] : NULL;
	
	$post_types = geodir_get_posttypes();
	if ($post_type && in_array($post_type, $post_types) && $post_type . '_tags' == $taxonomy) {		
		$posts_obj = $wpdb->get_results($wpdb->prepare("SELECT object_id FROM " . $wpdb->term_relationships . " WHERE term_taxonomy_id = %d", array($tt_id)));
		
		if (!empty($posts_obj)) {
			foreach ($posts_obj as $post_obj) {
				$post_id = $post_obj->object_id;
				
				$raw_tags = wp_get_object_terms($post_id, $post_type . '_tags', array('fields' => 'names'));
				$post_tags = !empty($raw_tags) ? implode(',', $raw_tags) : '';
				
				$listing_table = $plugin_prefix . $post_type . '_detail';
				$wpdb->query($wpdb->prepare("UPDATE " . $listing_table . " SET post_tags=%s WHERE post_id =%d", array($post_tags, $post_id)));
			}
		}
	}
}


add_filter('geodir_term_slug_is_exists', 'geodir_term_slug_is_exists', 0, 3); //in core plugin
/**
 * Check whether a term slug exists or not.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @global string $table_prefix WordPress Database Table prefix.
 * @param bool $slug_exists Default: false.
 * @param string $slug Term slug.
 * @param int $term_id The term ID.
 * @return bool true when exists. false when not exists.
 */
function geodir_term_slug_is_exists($slug_exists, $slug, $term_id)
{

    global $wpdb, $table_prefix;

    $default_location = geodir_get_default_location();

    $country_slug = $default_location->country_slug;
    $region_slug = $default_location->region_slug;
    $city_slug = $default_location->city_slug;

    if ($country_slug == $slug || $region_slug == $slug || $city_slug == $slug)
        return $slug_exists = true;

    if ($wpdb->get_var($wpdb->prepare("SELECT slug FROM " . $table_prefix . "terms WHERE slug=%s AND term_id != %d", array($slug, $term_id))))
        return $slug_exists = true;

    return $slug_exists;
}






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


add_filter('geodir_detail_page_tab_list_extend', 'geodir_detail_page_tab_headings_change');
/**
 * Detail Page Tab Headings Change.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @param array $tabs_arr {
 *    Attributes of the Tabs array.
 *
 *    @type array $post_profile {
 *        Attributes of post_profile.
 *
 *        @type string $heading_text    Tab Heading. Default "Profile".
 *        @type bool   $is_active_tab   Is this tab active? Default true.
 *        @type bool   $is_display      Display this tab? Default true.
 *        @type string $tab_content     Tab content. Default "".
 *
 *    }
 *    @type array $post_info {
 *        Attributes of post_info.
 *
 *        @type string $heading_text    Tab Heading. Default "More Info".
 *        @type bool   $is_active_tab   Is this tab active? Default false.
 *        @type bool   $is_display      Display this tab? Default false.
 *        @type string $tab_content     Tab content. Default "".
 *
 *    }
 *    @type array $post_images {
 *        Attributes of post_images.
 *
 *        @type string $heading_text    Tab Heading. Default "Photo".
 *        @type bool   $is_active_tab   Is this tab active? Default false.
 *        @type bool   $is_display      Display this tab? Default true.
 *        @type string $tab_content     Tab content. Default "".
 *
 *    }
 *    @type array $post_video {
 *        Attributes of post_video.
 *
 *        @type string $heading_text    Tab Heading. Default "Video".
 *        @type bool   $is_active_tab   Is this tab active? Default false.
 *        @type bool   $is_display      Display this tab? Default false.
 *        @type string $tab_content     Tab content. Default "".
 *
 *    }
 *    @type array $special_offers {
 *        Attributes of special_offers.
 *
 *        @type string $heading_text    Tab Heading. Default "Special Offers".
 *        @type bool   $is_active_tab   Is this tab active? Default false.
 *        @type bool   $is_display      Display this tab? Default false.
 *        @type string $tab_content     Tab content. Default "".
 *
 *    }
 *    @type array $post_map {
 *        Attributes of post_map.
 *
 *        @type string $heading_text    Tab Heading. Default "Map".
 *        @type bool   $is_active_tab   Is this tab active? Default false.
 *        @type bool   $is_display      Display this tab? Default true.
 *        @type string $tab_content     Tab content. Default "".
 *
 *    }
 *    @type array $reviews {
 *        Attributes of reviews.
 *
 *        @type string $heading_text    Tab Heading. Default "Reviews".
 *        @type bool   $is_active_tab   Is this tab active? Default false.
 *        @type bool   $is_display      Display this tab? Default true.
 *        @type string $tab_content     Tab content. Default "review display".
 *
 *    }
 *    @type array $related_listing {
 *        Attributes of related_listing.
 *
 *        @type string $heading_text    Tab Heading. Default "Related Listing".
 *        @type bool   $is_active_tab   Is this tab active? Default false.
 *        @type bool   $is_display      Display this tab? Default true.
 *        @type string $tab_content     Tab content. Default "".
 *
 *    }
 *
 * }
 * @return array Modified tabs array.
 */
function geodir_detail_page_tab_headings_change($tabs_arr)
{
    global $wpdb;

    $post_type = geodir_get_current_posttype();

    $all_postypes = geodir_get_posttypes();

    if (!empty($tabs_arr) && $post_type != '' && in_array($post_type, $all_postypes)) {

        if (array_key_exists('post_video', $tabs_arr)) {

            $field_title = $wpdb->get_var($wpdb->prepare("select frontend_title from " . GEODIR_CUSTOM_FIELDS_TABLE . " where htmlvar_name = %s and post_type = %s ", array('geodir_video', $post_type)));

            if (isset($tabs_arr['post_video']['heading_text']) && $field_title != '')
                $tabs_arr['post_video']['heading_text'] = $field_title;
        }

        if (array_key_exists('special_offers', $tabs_arr)) {

            $field_title = $wpdb->get_var($wpdb->prepare("select frontend_title from " . GEODIR_CUSTOM_FIELDS_TABLE . " where htmlvar_name = %s and post_type = %s ", array('geodir_special_offers', $post_type)));

            if (isset($tabs_arr['special_offers']['heading_text']) && $field_title != '')
                $tabs_arr['special_offers']['heading_text'] = $field_title;
        }

    }

    return $tabs_arr;

}

add_action('init', 'geodir_remove_template_redirect_actions', 100);
/**
 * Remove template redirect options.
 *
 * @since 1.0.0
 * @package GeoDirectory
 */
function geodir_remove_template_redirect_actions()
{
    if (geodir_is_page('login')){
        remove_all_actions('template_redirect');
        remove_action('init', 'avia_modify_front', 10);
    }
}



/* ---------- temp function to delete media post */


/**
 * temp function to delete media post.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 * @param int $attachment_id Attachment ID.
 * @return bool|void Returns false on failure.
 */
function geodirectory_before_featured_image_delete($attachment_id)
{

    global $wpdb, $plugin_prefix;

    $post_id = get_post_field('post_parent', $attachment_id);

    $attachment_url = wp_get_attachment_url($attachment_id);

    if ($post_id > 0 && (isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete')) {

        $post_type = get_post_type($post_id);

        $all_postypes = geodir_get_posttypes();

        if (!in_array($post_type, $all_postypes) || !is_admin())
            return false;

        $uploads = wp_upload_dir();

        $split_img_path = explode($uploads['baseurl'], $attachment_url);

        $split_img_file_path = isset($split_img_path[1]) ? $split_img_path[1] : '';

        $wpdb->query(
            $wpdb->prepare("DELETE FROM " . GEODIR_ATTACHMENT_TABLE . " WHERE post_id = %d AND file=%s ",
                array($post_id, $split_img_file_path)
            )
        );

        $attachment_data = $wpdb->get_row(
            $wpdb->prepare("SELECT ID, MIN(`menu_order`) FROM " . GEODIR_ATTACHMENT_TABLE . " WHERE post_id=%d",
                array($post_id)
            )
        );

        if (!empty($attachment_data)) {
            $wpdb->query("UPDATE " . GEODIR_ATTACHMENT_TABLE . " SET menu_order=1 WHERE ID=" . $attachment_data->ID);
        }


        $table_name = $plugin_prefix . $post_type . '_detail';

        $wpdb->query("UPDATE " . $table_name . " SET featured_image='' WHERE post_id =" . $post_id);

        geodir_set_wp_featured_image($post_id);

    }

}


//add_action('wp', 'geodir_temp_set_post_attachment'); //WTF 

/**
 * temp function to set post attachment.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 */
function geodir_temp_set_post_attachment()
{

    global $wpdb, $plugin_prefix;

    $all_postypes = geodir_get_posttypes();

    foreach ($all_postypes as $posttype) {

        $tablename = $plugin_prefix . $posttype . '_detail';

        $get_post_data = $wpdb->get_results("SELECT post_id FROM " . $tablename);

        if (!empty($get_post_data)) {

            foreach ($get_post_data as $data) {

                $post_id = $data->post_id;

                $attachment_data = $wpdb->get_results("SELECT * FROM " . GEODIR_ATTACHMENT_TABLE . " WHERE post_id =" . $post_id . " AND file!=''");

                if (!empty($attachment_data)) {

                    foreach ($attachment_data as $attach) {

                        $file_info = pathinfo($attach->file);

                        $sub_dir = '';
                        if ($file_info['dirname'] != '.' && $file_info['dirname'] != '..')
                            $sub_dir = stripslashes_deep($file_info['dirname']);

                        $uploads = wp_upload_dir(trim($sub_dir, '/')); // Array of key => value pairs
                        $uploads_path = $uploads['basedir'];

                        $file_name = $file_info['basename'];

                        $img_arr['path'] = $uploads_path . $sub_dir . '/' . $file_name;

                        if (!file_exists($img_arr['path'])) {

                            $wpdb->query("DELETE FROM " . GEODIR_ATTACHMENT_TABLE . " WHERE ID=" . $attach->ID);

                        }

                    }

                    $attachment_data = $wpdb->get_row("SELECT ID, MIN(`menu_order`) FROM " . GEODIR_ATTACHMENT_TABLE . " WHERE post_id=" . $post_id . " GROUP BY post_id");

                    if (!empty($attachment_data)) {

                        if ($attachment_data->ID)
                            $wpdb->query("UPDATE " . GEODIR_ATTACHMENT_TABLE . " SET menu_order=1 WHERE ID=" . $attachment_data->ID);

                    } else {

                        if (has_post_thumbnail($post_id)) {

                            $post_thumbnail_id = get_post_thumbnail_id($post_id);

                            wp_delete_attachment($post_thumbnail_id);

                        }

                    }

                    $wpdb->query("UPDATE " . $tablename . " SET featured_image='' WHERE post_id =" . $post_id);

                    geodir_set_wp_featured_image($post_id);

                }

            }

        }

    }

}

/* ------- GET CURRENT USER POST LISTING -------*/




add_filter('geodir_detail_page_tab_list_extend', 'geodir_detail_page_custom_field_tab');
/**
 * Details page tab custom fields.
 *
 * @since 1.0.0
 * @since 1.5.7 Custom fields option values added to db translation.
 *              Changes to display url fields title.
 * @package GeoDirectory
 * @global object $post The current post object.
 * @param array $tabs_arr Tabs array {@see geodir_detail_page_tab_headings_change()}.
 * @return array Modified tabs array.
 */
function geodir_detail_page_custom_field_tab($tabs_arr)
{
    global $post,$gd_post;

    $post_type = geodir_get_current_posttype();
    $all_postypes = geodir_get_posttypes();

    if (!empty($tabs_arr) && $post_type != '' && in_array($post_type, $all_postypes) && (geodir_is_page('detail') || geodir_is_page('preview'))) {
        $package_info = array();
        $package_info = geodir_post_package_info($package_info, $post);
        $post_package_id = !empty($package_info->pid) ? $package_info->pid : '';
        $fields_location = 'owntab';

        $custom_fields = geodir_post_custom_fields($post_package_id, 'all', $post_type, $fields_location);
        //remove video and special offers if it is already set to show
        if(isset($tabs_arr['post_video']['is_display']) && $tabs_arr['post_video']['is_display']){
            $unset_video = true;
        }

        if(isset($tabs_arr['special_offers']['is_display']) && $tabs_arr['special_offers']['is_display']){
            $unset_special_offers = true;
        }
        if(isset($unset_video) || isset($unset_special_offers) && !empty($custom_fields)){
            foreach($custom_fields as $key => $custom_field){
                if($custom_field['name']=='geodir_video' && isset($unset_video)){
                    unset($custom_fields[$key]);
                }
                if($custom_field['name']=='geodir_special_offers' && isset($unset_special_offers)){
                    unset($custom_fields[$key]);
                }
            }
        }

//        print_r($custom_fields);

        if (!empty($custom_fields)) {
            $parse_custom_fields = array();
            foreach ($custom_fields as $field) {
                $field = stripslashes_deep($field); // strip slashes
                $type = $field;
                $field_name = $field['htmlvar_name'];

                if (isset($field['show_in']) && strpos($field['show_in'], '[owntab]') !== false  && ((isset($gd_post->{$field_name}) && $gd_post->{$field_name} != '') || $field['type'] == 'fieldset' || $field['type'] == 'address') && in_array($field['type'], array('text', 'datepicker', 'textarea', 'time', 'phone', 'email', 'select', 'multiselect', 'url', 'html', 'fieldset', 'radio', 'checkbox', 'file','address','taxonomy', 'business_hours'))) {
                    if ($type['type'] == 'datepicker' && ($gd_post->{$type['htmlvar_name']} == '' || $gd_post->{$type['htmlvar_name']} == '0000-00-00')) {
                        continue;
                    }

                    $parse_custom_fields[] = $field;
                }
            }
            $custom_fields = $parse_custom_fields;
        }


        if (!empty($custom_fields)) {

            global $field_set_start;

            $gd_post = stripslashes_deep($gd_post); // strip slashes
            
            $field_set_start = 0;
            $fieldset_count = 0;
            $fieldset = '';
            $total_fields = count($custom_fields);
            $count_field = 0;
            $fieldset_arr = array();
            $i = 0;
            $geodir_post_info = isset($post->ID) && !empty($post->ID) ? geodir_get_post_info($post->ID) : NULL;

            foreach ($custom_fields as $field) {
                $count_field++;
                $field_name = $field['htmlvar_name'];



                if (isset($field['show_in']) && strpos($field['show_in'], '[owntab]') !== false
                    && ((isset($gd_post->{$field_name}) && $gd_post->{$field_name} != '') || $field['type'] == 'fieldset' || $field['type'] == 'address')
                    && in_array($field['type'], array('text', 'datepicker', 'textarea', 'time', 'phone', 'email', 'select', 'multiselect', 'url', 'html', 'fieldset', 'radio', 'checkbox', 'file','address','taxonomy', 'business_hours'))) {


                    $label = $field['frontend_title'] != '' ? $field['frontend_title'] : $field['admin_title'];
                    $frontend_title = trim($field['frontend_title']);
                    $type = $field;
                    $variables_array = array();

                    if ($type['type'] == 'datepicker' && ($gd_post->{$type['htmlvar_name']} == '' || $gd_post->{$type['htmlvar_name']} == '0000-00-00')) {
                        continue;
                    }

                    if ($type['type'] != 'fieldset') {
                        $i++;
                        $variables_array['post_id'] = $post->ID;
                        $variables_array['label'] = __($type['frontend_title'], 'geodirectory');
                        $variables_array['value'] = isset($gd_post->{$type['htmlvar_name']}) ? $gd_post->{$type['htmlvar_name']} : '';

                    }else{
                        $i = 0;
                        $fieldset_count++;
                        $field_set_start = 1;
                        $fieldset_arr[$fieldset_count]['htmlvar_name'] = 'gd_tab_' . $fieldset_count;
                        $fieldset_arr[$fieldset_count]['label'] = $label;
                    }


                    if(isset($type['extra_fields'])){$extra_fields= $type['extra_fields'];}
                    $type = stripslashes_deep($type); // strip slashes
                    if(isset($type['extra_fields'])){$type['extra_fields'] = $extra_fields;}
                    $html = '';
                    $html_var = isset($type['htmlvar_name']) ? $type['htmlvar_name'] : '';
                    if($html_var=='post'){$html_var='post_address';}
                    $field_icon = geodir_field_icon_proccess($type);
                    $filed_type = $type['type'];

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


                    /**
                     * Filter custom field output in tab.
                     *
                     * @since 1.5.6
                     *
                     * @param string $html_var The HTML variable name for the field.
                     * @param string $html Custom field unfiltered HTML.
                     * @param array $variables_array Custom field variables array.
                     */
                    $html = apply_filters("geodir_tab_show_{$html_var}", $html, $variables_array);

//                    echo $html.'#####'.$html_var;
//                    print_r($custom_fields);
//                    print_r( $gd_post );

                    $fieldset_html = '';
                    if ($field_set_start == 1) {
                        $add_html = false;
                        if ($type['type'] == 'fieldset' && $fieldset_count > 1) {
                            if ($fieldset != '') {
                                $add_html = true;
                                $label = $fieldset_arr[$fieldset_count - 1]['label'];
                                $htmlvar_name = $fieldset_arr[$fieldset_count - 1]['htmlvar_name'];
                            }
                            $fieldset_html = $fieldset;
                            $fieldset = '';
                        } else {
                            $fieldset .= $html;
                            if ($total_fields == $count_field && $fieldset != '') {
                                $add_html = true;
                                $label = $fieldset_arr[$fieldset_count]['label'];
                                $htmlvar_name = $fieldset_arr[$fieldset_count]['htmlvar_name'];
                                $fieldset_html = $fieldset;
                            }
                        }

                        if ($add_html) {
                            $tabs_arr[$htmlvar_name] = array(
                                'heading_text' => __($label, 'geodirectory'),
                                'is_active_tab' => false,
                                /**
                                 * Filter if a custom field should be displayed on the details page tab.
                                 *
                                 * @since 1.0.0
                                 * @param string $htmlvar_name The field HTML var name.
                                 */
                                'is_display' => apply_filters('geodir_detail_page_tab_is_display', true, $htmlvar_name),
                                'tab_content' => '<div class="geodir-company_info field-group">' . $fieldset_html . '</div>'
                            );
                        }
                    } else {
                        if ($html != '') {
                            $tabs_arr[$html_var] = array(
                                'heading_text' => __($label, 'geodirectory'),
                                'is_active_tab' => false,
                                /** This action is documented in geodirectory_hooks_actions.php */
                                'is_display' => apply_filters('geodir_detail_page_tab_is_display', true, $field['htmlvar_name']),
                                'tab_content' => $html
                            );
                        }
                    }
                }
            }
        }
    }
    return $tabs_arr;
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
add_filter('geodir_action_get_request_info', 'geodir_attach_parent_categories', 0, 1);
add_filter('geodir_show_listing_post_excerpt', 'geodir_show_listing_post_excerpt', 10, 3);
//add_filter('gd_rating_form_html', 'geodir_font_awesome_rating_form_html', 10, 2);
add_filter('geodir_get_rating_stars_html', 'geodir_font_awesome_rating_stars_html', 10, 3);
add_action('wp_head', 'geodir_font_awesome_rating_css');
add_action('admin_head', 'geodir_font_awesome_rating_css');

add_filter('get_comments_link', 'geodir_get_comments_link', 10, 2);
function geodir_get_comments_link($comments_link, $post_id) {
    $post_type = get_post_type($post_id);

    $all_postypes = geodir_get_posttypes();
    if (in_array($post_type, $all_postypes)) {
        $comments_link = str_replace('#comments', '#reviews', $comments_link);
        $comments_link = str_replace('#respond', '#reviews', $comments_link);
    }

    return $comments_link;
}


/**
 * Add a class to theme menus so we can adjust the z-index.
 *
 * We add a class and adjust the z-index so menus don't hide behind maps and menus on second lines
 * don't overlap submenus of first line menus.
 *
 * @package GeoDirectory
 * @since 1.6.3
 * @param array $args The array of menu arguments.
 * @return array The modified arguments.
 */
function geodir_add_nav_menu_class( $args )
{

        if(isset($args['menu_class'])){
            $args['menu_class'] = $args['menu_class']." gd-menu-z";
        }
    
    return $args;
}

add_filter( 'wp_nav_menu_args', 'geodir_add_nav_menu_class' );

/**
 * Remove Yoast SEO hook if disabled on GD pages.
 *
 * @since 1.6.18
 *
 */
function geodir_remove_yoast_seo_metas(){
    if ( class_exists( 'WPSEO_Frontend' ) && geodir_is_geodir_page() && geodir_disable_yoast_seo_metas() ) {
        $wpseo = WPSEO_Frontend::get_instance();
        
        remove_action( 'wp_head', array( $wpseo, 'metadesc' ), 6 );
        remove_action( 'wp_head', array( $wpseo, 'metakeywords' ), 11 );
        remove_filter( 'pre_get_document_title', array( $wpseo, 'title' ), 15 );
        remove_filter( 'wp_title', array( $wpseo, 'title' ), 15, 3 );
        remove_filter( 'thematic_doctitle', array( $wpseo, 'title' ), 15 );
        remove_filter( 'woo_title', array( $wpseo, 'fix_woo_title' ), 99 );
        
        remove_action( 'template_redirect', 'wpseo_frontend_head_init', 999 );
    }
}

/**
 * Change country slug czech-republic to czechia and redirect.
 *
 * @since 1.6.18
 *
 * @param object $wp The WordPress object.
 */
function geodir_check_redirect($wp) {
    if (is_404() || (!empty($wp->query_vars['error']) && $wp->query_vars['error'] == '404')) {
        $current_url = geodir_curPageURL();
        $search = 'czech-republic';
        $replace = 'czechia';        
        
        $has_slash = substr($current_url, -1);
        if ($has_slash != "/") {
            $current_url .= '/';
        }
        
        $redirect = false;
        if (strpos($current_url, '/' . $search . '/') !== false) {
            $redirect = true;
            $current_url = preg_replace('/\/' . $search . '\//', '/' . $replace . '/', $current_url, 1);
        }
        
        if ($has_slash != "/") {
            $current_url = trim($current_url, '/');
        }
        
        if (strpos($current_url, 'gd_country=' . $search) !== false) {
            $redirect = true;
            $current_url = str_replace('gd_country=' . $search, 'gd_country=' . $replace, $current_url);
        }

        if ($redirect) {
            wp_redirect($current_url);
            exit;
        }
    }
}
add_action('parse_request', 'geodir_check_redirect', 101, 1);

/**
 * Filters the unique post slug.
 *
 * @since 1.6.20
 *
 * @global object $wpdb WordPress Database object.
 *
 * @param string $slug          The post slug.
 * @param int    $post_ID       Post ID.
 * @param string $post_status   The post status.
 * @param string $post_type     Post type.
 * @param int    $post_parent   Post parent ID
 * @param string $original_slug The original post slug.
 */
function geodir_check_post_to_term_slug( $slug, $post_ID, $post_status, $post_type, $post_parent, $original_slug ) {
    global $wpdb;
    
    if ( $post_type && strpos( $post_type, 'gd_' ) === 0 ) {
        $posts_join = apply_filters( 'geodir_unique_post_slug_posts_join', "", $post_ID, $post_type );
        $posts_where = apply_filters( 'geodir_unique_post_slug_posts_where', "", $post_ID, $post_type );
        $terms_join = apply_filters( 'geodir_unique_post_slug_terms_join', "", $post_ID, $post_type );
        $terms_where = apply_filters( 'geodir_unique_post_slug_terms_where', "", $post_ID, $post_type );

        $term_slug_check = $wpdb->get_var( $wpdb->prepare( "SELECT t.slug FROM $wpdb->terms AS t LEFT JOIN $wpdb->term_taxonomy AS tt ON tt.term_id = t.term_id {$terms_join} WHERE t.slug = '%s' AND ( tt.taxonomy = '" . $post_type . "category' OR tt.taxonomy = '" . $post_type . "_tags' ) {$terms_where} LIMIT 1", $slug ) );

        if ( $term_slug_check ) {
            $suffix = 1;
            
            do {
                $alt_slug = _truncate_post_slug( $original_slug, 200 - ( strlen( $suffix ) + 1 ) ) . "-$suffix";
                
                $term_check = $wpdb->get_var( $wpdb->prepare( "SELECT t.slug FROM $wpdb->terms AS t LEFT JOIN $wpdb->term_taxonomy AS tt ON tt.term_id = t.term_id {$terms_join} WHERE t.slug = '%s' AND ( tt.taxonomy = '" . $post_type . "category' OR tt.taxonomy = '" . $post_type . "_tags' ) {$terms_where} LIMIT 1", $alt_slug ) );
                
                $post_check = !$term_check && $wpdb->get_var( $wpdb->prepare( "SELECT p.post_name FROM $wpdb->posts p {$posts_join} WHERE p.post_name = %s AND p.post_type = %s AND p.ID != %d {$posts_where} LIMIT 1", $alt_slug, $post_type, $post_ID ) );
                
                $term_slug_check = $term_check || $post_check;
                
                $suffix++;
            } while ( $term_slug_check );
            
            $slug = $alt_slug;
        }
    }
    
    return $slug;
}
add_filter( 'wp_unique_post_slug', 'geodir_check_post_to_term_slug', 101, 6 );

/**
 * Check whether a post name with slug exists or not.
 *
 * @since 1.6.20
 *
 * @global object $wpdb WordPress Database object.
 * @global array $gd_term_post_type Cached array for term post type.
 * @global array $gd_term_taxonomy Cached array for term taxonomy.
 *
 * @param bool $slug_exists Default: false.
 * @param string $slug Term slug.
 * @param int $term_id The term ID.
 * @return bool true when exists. false when not exists.
 */
function geodir_check_term_to_post_slug( $slug_exists, $slug, $term_id ) {
    global $wpdb, $gd_term_post_type, $gd_term_taxonomy;
    
    if ( $slug_exists ) {
        return $slug_exists;
    }
    
    if ( !empty( $gd_term_taxonomy ) && isset($gd_term_taxonomy[$term_id]) ) {
        $taxonomy = $gd_term_taxonomy[$term_id];
    } else {
        $taxonomy = $wpdb->get_var( $wpdb->prepare( "SELECT taxonomy FROM $wpdb->term_taxonomy WHERE term_id = %d LIMIT 1", $term_id ) );
        $gd_term_taxonomy[$term_id] = $taxonomy;
    }
    
    if ( empty($taxonomy) ) {
        return $slug_exists;
    }
    
    if ( !empty( $gd_term_post_type ) && $gd_term_post_type[$term_id] ) {
        $post_type = $gd_term_post_type[$term_id];
    } else {
        $taxonomy_obj = get_taxonomy( $taxonomy );
        $post_type = !empty( $taxonomy_obj->object_type ) ? $taxonomy_obj->object_type[0] : NULL;
    }

	$posts_join = apply_filters( 'geodir_unique_term_slug_posts_join', "", $term_id, $taxonomy, $post_type );
    $posts_where = apply_filters( 'geodir_unique_term_slug_posts_where', "", $term_id, $taxonomy, $post_type );

    if ( $post_type && $wpdb->get_var( $wpdb->prepare( "SELECT p.post_name FROM $wpdb->posts p {$posts_join} WHERE p.post_name = %s AND p.post_type = %s {$posts_where} LIMIT 1", $slug, $post_type ) ) ) {
        $slug_exists = true;
    }

    return $slug_exists;
}
add_filter( 'geodir_term_slug_is_exists', 'geodir_check_term_to_post_slug', 10, 3 );