<?php
/**
 * Template functions that affect the output of most GeoDirectory pages
 *
 * @since 1.0.0
 * @package GeoDirectory
 */
###############################################
########### DYNAMIC CONTENT ###################
###############################################


###############################################
########### DETAILS PAGE ACTIONS ##############


add_action('wp_head', 'geodir_action_details_micordata', 10);

/**
 * Output the posts microdata in the source code.
 *
 * This micordata is used by things like Google as a standard way of declaring things like telephone numbers etc.
 *
 * @global bool $preview True of on a preview page. False if not.
 * @global object $post The current post object.
 * @since 1.0.0
 * @since 1.5.4 Changed to JSON-LD and added filters.
 * @since 1.5.7 Added $post param.
 * @param object $post Optional. The post object or blank.
 * @package GeoDirectory
 */
function geodir_action_details_micordata($post='')
{
return; //@todo we need to update this
    global $preview;
    if(empty($post)){global $post;}
    if ($preview || !geodir_is_page('detail')) {
        return;
    }

    // url
    $c_url = geodir_curPageURL();

    // post reviews
    $post_reviews = get_comments(array('post_id' => $post->ID, 'status' => 'approve'));
    if (empty($post_reviews)) {
        $reviews = '';
    } else {
        foreach ($post_reviews as $review) {

            if($rating_value = geodir_get_comment_rating($review->comment_ID)){
                $reviews[] = array(
                    "@type" => "Review",
                    "author" => $review->comment_author,
                    "datePublished" => $review->comment_date,
                    "description" => $review->comment_content,
                    "reviewRating" => array(
                        "@type" => "Rating",
                        "bestRating" => "5",// @todo this will need to be filtered for review manager if user changes the score.
                        "ratingValue" => $rating_value,
                        "worstRating" => "1"
                    )
                );
            }

        }

    }

    // post images
    //$post_images = geodir_get_images($post->ID, 'thumbnail', geodir_get_option('geodir_listing_no_img'));
	$post_images = geodir_get_images($post->ID);
    if (empty($post_images)) {
        $images = '';
    } else {
        $i_arr = array();
        foreach ($post_images as $img) {
            $i_arr[] = $img->src;
        }

        if (count($i_arr) == 1) {
            $images = $i_arr[0];
        } else {
            $images = $i_arr;
        }

    }
    //print_r($post);
    // external links
    $external_links =  array();
    $external_links[] = $post->geodir_website;
    $external_links[] = $post->geodir_twitter;
    $external_links[] = $post->geodir_facebook;
    $external_links = array_filter($external_links);

    if(!empty($external_links)){
        $external_links = array_values($external_links);
    }

    // reviews
    $comment_count = geodir_get_review_count_total($post->ID);
    $post_avgratings = geodir_get_post_rating($post->ID);

    // schema type
    $schema_type = 'LocalBusiness';
    if(isset($post->default_category) && $post->default_category){
        $cat_schema = get_term_meta( $post->default_category, 'ct_cat_schema', true );
        if($cat_schema){$schema_type = $cat_schema;}
        if(!$cat_schema && $schema_type=='LocalBusiness' && $post->post_type=='gd_event'){$schema_type = 'Event';}
    }

    $schema = array();
    $schema['@context'] = "https://schema.org";
    $schema['@type'] = $schema_type;
    $schema['name'] = $post->post_title;
    $schema['description'] = wp_strip_all_tags( $post->post_content, true );
    $schema['telephone'] = $post->geodir_contact;
    $schema['url'] = $c_url;
    $schema['sameAs'] = $external_links;
    $schema['image'] = $images;
    $schema['address'] = array(
        "@type" => "PostalAddress",
        "streetAddress" => $post->post_address,
        "addressLocality" => $post->post_city,
        "addressRegion" => $post->post_region,
        "addressCountry" => $post->post_country,
        "postalCode" => $post->post_zip
    );

    if($post->post_latitude && $post->post_longitude) {
        $schema['geo'] = array(
            "@type" => "GeoCoordinates",
            "latitude" => $post->post_latitude,
            "longitude" => $post->post_longitude
        );
    }

    if($post_avgratings) {
        $schema['aggregateRating'] = array(
            "@type" => "AggregateRating",
            "ratingValue" => $post_avgratings,
            "bestRating" => "5", // @todo this will need to be filtered for review manager if user changes the score.
            "worstRating" => "1",
            "ratingCount" => $comment_count
        );
    }
    $schema['review'] = $reviews;

    /**
     * Allow the schema JSON-LD info to be filtered.
     *
     * @since 1.5.4
     * @since 1.5.7 Added $post variable.
     * @param array $schema The array of schema data to be filtered.
     * @param object $post The post object.
     */
    $schema = apply_filters('geodir_details_schema', $schema,$post);


    echo '<script type="application/ld+json">' . json_encode($schema) . '</script>';


    $uploads = wp_upload_dir();
    $facebook_og = (isset($post->featured_image) && $post->featured_image) ? '<meta property="og:image" content="'.$uploads['baseurl'].$post->featured_image.'"/>' : '';

    /**
     * Show facebook open graph meta info
     *
     * @since 1.6.6
     * @param string $facebook_og The open graph html to be filtered.
     * @param object $post The post object.
     */
    echo apply_filters('geodir_details_facebook_og', $facebook_og,$post);



}






###############################################
########### LISTINGS PAGE ACTIONS #############
###############################################
add_action('geodir_listings_page_title', 'geodir_action_listings_title', 10);
/**
 * Outputs the listings template title.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wp The WordPress object.
 * @global string $term Current term slug.
 */
function geodir_action_listings_title()
{
    global $wp, $term;

    $gd_post_type = geodir_get_current_posttype();
    $post_type_info = get_post_type_object($gd_post_type);

    $add_string_in_title = __('All', 'geodirectory') . ' ';
    if (isset($_REQUEST['list']) && $_REQUEST['list'] == 'favourite') {
        $add_string_in_title = __('My Favorite', 'geodirectory') . ' ';
    }

    $list_title = $add_string_in_title . __($post_type_info->labels->name, 'geodirectory');
    $single_name = $post_type_info->labels->singular_name;

    $taxonomy = geodir_get_taxonomies($gd_post_type, true);

    $gd_country = get_query_var('gd_country');
    $gd_region = get_query_var('gd_region');
    $gd_city = get_query_var('gd_city');

    if (!empty($term)) {
        $location_name = '';
        if ($gd_country != '' || $gd_region != '' || $gd_city != '') {
            if ($gd_country != '') {
                $location_name = geodir_sanitize_location_name('gd_country', $gd_country);
            }

            if ($gd_region != '') {
                $location_name = geodir_sanitize_location_name('gd_region', $gd_region);
            }

            if ($gd_city != '') {
                $location_name = geodir_sanitize_location_name('gd_city', $gd_city);
            }
        }

        $current_term = get_term_by('slug', $term, $taxonomy[0]);
        if (!empty($current_term)) {
            $current_term_name = __(geodir_utf8_ucfirst($current_term->name), 'geodirectory');
            if ($current_term_name != '' && $location_name != '' && isset($current_term->taxonomy) && $current_term->taxonomy == $gd_post_type . 'category') {
                $location_last_char = substr($location_name, -1);
                $location_name_attach = geodir_strtolower($location_last_char) == 's' ? __("'", 'geodirectory') : __("'s", 'geodirectory');
                $list_title .= __(' in', 'geodirectory') . ' ' . $location_name . $location_name_attach . ' ' . $current_term_name;
            } else {
                $list_title .= __(' in', 'geodirectory') . " '" . $current_term_name . "'";
            }
        } else {
            if (count($taxonomy) > 1) {
                $current_term = get_term_by('slug', $term, $taxonomy[1]);

                if (!empty($current_term)) {
                    $current_term_name = __(geodir_utf8_ucfirst($current_term->name), 'geodirectory');
                    if ($current_term_name != '' && $location_name != '' && isset($current_term->taxonomy) && $current_term->taxonomy == $gd_post_type . 'category') {
                        $location_last_char = substr($location_name, -1);
                        $location_name_attach = geodir_strtolower($location_last_char) == 's' ? __("'", 'geodirectory') : __("'s", 'geodirectory');
                        $list_title .= __(' in', 'geodirectory') . ' ' . $location_name . $location_name_attach . ' ' . $current_term_name;
                    } else {
                        $list_title .= __(' in', 'geodirectory') . " '" . $current_term_name . "'";
                    }
                }
            }
        }

    } else {
        $gd_country = (isset($wp->query_vars['gd_country']) && $wp->query_vars['gd_country'] != '') ? $wp->query_vars['gd_country'] : '';
        $gd_region = (isset($wp->query_vars['gd_region']) && $wp->query_vars['gd_region'] != '') ? $wp->query_vars['gd_region'] : '';
        $gd_city = (isset($wp->query_vars['gd_city']) && $wp->query_vars['gd_city'] != '') ? $wp->query_vars['gd_city'] : '';

        $gd_country_actual = $gd_region_actual = $gd_city_actual = '';

        if (function_exists('get_actual_location_name')) {
            $gd_country_actual = $gd_country != '' ? get_actual_location_name('country', $gd_country, true) : $gd_country;
            $gd_region_actual = $gd_region != '' ? get_actual_location_name('region', $gd_region) : $gd_region;
            $gd_city_actual = $gd_city != '' ? get_actual_location_name('city', $gd_city) : $gd_city;
        }

        if ($gd_city != '') {
            if ($gd_city_actual != '') {
                $gd_city = $gd_city_actual;
            } else {
                $gd_city = preg_replace('/-(\d+)$/', '', $gd_city);
                $gd_city = preg_replace('/[_-]/', ' ', $gd_city);
                $gd_city = __(geodir_ucwords($gd_city), 'geodirectory');
            }

            $list_title .= __(' in', 'geodirectory') . " '" . $gd_city . "'";
        } else if ($gd_region != '') {
            if ($gd_region_actual != '') {
                $gd_region = $gd_region_actual;
            } else {
                $gd_region = preg_replace('/-(\d+)$/', '', $gd_region);
                $gd_region = preg_replace('/[_-]/', ' ', $gd_region);
                $gd_region = __(geodir_ucwords($gd_region), 'geodirectory');
            }

            $list_title .= __(' in', 'geodirectory') . " '" . $gd_region . "'";
        } else if ($gd_country != '') {
            if ($gd_country_actual != '') {
                $gd_country = $gd_country_actual;
            } else {
                $gd_country = preg_replace('/-(\d+)$/', '', $gd_country);
                $gd_country = preg_replace('/[_-]/', ' ', $gd_country);
                $gd_country = __(geodir_ucwords($gd_country), 'geodirectory');
            }

            $list_title .= __(' in', 'geodirectory') . " '" . $gd_country . "'";
        }
    }

    if (is_search()) {
        $list_title = __('Search', 'geodirectory') . ' ' . __(geodir_utf8_ucfirst($post_type_info->labels->name), 'geodirectory') . __(' For :', 'geodirectory') . " '" . get_search_query() . "'";
    }
    /** This action is documented in geodirectory_template_actions.php */
    $class = apply_filters('geodir_page_title_class', 'entry-title fn');
    /** This action is documented in geodirectory_template_actions.php */
    $class_header = apply_filters('geodir_page_title_header_class', 'entry-header');


    $title = $list_title;
    $gd_page = '';
    if(geodir_is_page('pt')){
        $gd_page = 'pt';
        $title  = (geodir_get_option('geodir_page_title_pt')) ? geodir_get_option('geodir_page_title_pt') : $title;
    }
    elseif(geodir_is_page('listing')){
        $gd_page = 'listing';
        global $wp_query;
        $current_term = $wp_query->get_queried_object();
        if (strpos($current_term->taxonomy,'_tags') !== false) {
            $title = (geodir_get_option('geodir_page_title_tag-listing')) ? geodir_get_option('geodir_page_title_tag-listing') : $title;
        }else{
            $title = (geodir_get_option('geodir_page_title_cat-listing')) ? geodir_get_option('geodir_page_title_cat-listing') : $title;
        }

    }
    elseif(geodir_is_page('author')){
        $gd_page = 'author';
        if(isset($_REQUEST['list']) && $_REQUEST['list']=='favourite'){
            $title = (geodir_get_option('geodir_page_title_favorite')) ? geodir_get_option('geodir_page_title_favorite') : $title;
        }else{
            $title = (geodir_get_option('geodir_page_title_author')) ? geodir_get_option('geodir_page_title_author') : $title;
        }

    }


    /**
     * Filter page title to replace variables.
     *
     * @since 1.5.4
     * @param string $title The page title including variables.
     * @param string $gd_page The GeoDirectory page type if any.
     */
    $title =  apply_filters('geodir_seo_page_title', __($title, 'geodirectory'), $gd_page);

    echo '<header class="' . $class_header . '"><h1 class="' . $class . '">' .
        /**
         * Filter the listing page title.
         *
         * @since 1.0.0
         * @param string $list_title The title for the category page.
         */
        apply_filters('geodir_listing_page_title', $title) . '</h1></header>';
}

add_action('geodir_listings_page_description', 'geodir_action_listings_description', 10);
/**
 * Outputs the listings page description HTML.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wp_query WordPress Query object.
 */
function geodir_action_listings_description()
{
    global $wp_query;
    $current_term = $wp_query->get_queried_object();

    $gd_post_type = geodir_get_current_posttype();
    if (isset($current_term->term_id) && $current_term->term_id != '') {

        $term_desc = term_description($current_term->term_id, $gd_post_type . '_tags');
        $saved_data = stripslashes( get_term_meta( $current_term->term_id, 'ct_cat_top_desc', true ) );
        if ($term_desc && !$saved_data) {
            $saved_data = $term_desc;
        }

        // stop payment manager filtering content length
        $filter_priority = has_filter( 'the_content', 'geodir_payments_the_content' );
        if ( false !== $filter_priority ) {
            remove_filter( 'the_content', 'geodir_payments_the_content', $filter_priority );
        }

        /**
         * Apply the core filter `the_content` filter to the variable string.
         *
         * This is a WordPress core filter that does many things.
         *
         * @since 1.0.0
         * @param string $var The string to apply the filter to.
         */
        $cat_description = apply_filters('the_content', $saved_data);


        if ( false !== $filter_priority ) {
            add_filter( 'the_content', 'geodir_payments_the_content', $filter_priority );
        }

        if ($cat_description) {
            ?>

            <div class="term_description"><?php echo $cat_description;?></div> <?php
        }

    }
}



/**
 * Calls the listing template part.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global string $gridview_columns The girdview style of the listings.
 */
function geodir_action_listings_content_inside()
{
    global $gridview_columns;
    $listing_view = geodir_get_option('geodir_listing_view');
    if (strstr($listing_view, 'gridview')) {
        $gridview_columns = $listing_view;
        $listing_view_exp = explode('_', $listing_view);
        $listing_view = $listing_view_exp[0];
    }
    geodir_get_template_part('listing', 'listview');
}

add_action('geodir_listings_content_inside', 'geodir_action_listings_content_inside', 10);
add_action('geodir_listings_content_inside', 'geodir_pagination', 20);


add_action('geodir_listings_content', 'geodir_action_listings_content', 10);
/**
 * Builds and outputs the listings content via actions.
 *
 * @since 1.0.0
 * @package GeoDirectory
 */
function geodir_action_listings_content()
{
    $extra_class = apply_filters('geodir_before_listing_wrapper_extra_class', '', 'listings-page');
    echo '<div class="clearfix '.$extra_class.'">';
    /**
     * Called before the listings page content, inside the outer wrapper. Used on listings pages and search and author pages.
     *
     * @since 1.0.0
     */
    do_action('geodir_before_listing');
    echo '</div>';

    /**
     * This actions calls the listings list content. Used on listings pages and search and author pages.
     *
     * @since 1.0.0
     */
    do_action('geodir_listings_content_inside');

    /**
     * Called after the listings content, inside the outer wrapper HTML. Used on listings pages and search and author pages.
     *
     * @since 1.0.0
     */
    do_action('geodir_after_listing');

}



###############################################
######## ADD LISTINGS PAGE ACTIONS ############
###############################################








###############################################
########### AUTHOR PAGE ACTIONS ###############
###############################################


/**
 * Calls and outputs the template for the author page content section.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global string $gridview_columns The girdview style of the listings.
 */
function geodir_action_author_content_inside()
{
    global $gridview_columns;
    $listing_view = geodir_get_option('geodir_author_view');
    if (strstr($listing_view, 'gridview')) {
        $gridview_columns = $listing_view;
        $listing_view_exp = explode('_', $listing_view);
        $listing_view = $listing_view_exp[0];
    }
    geodir_get_template_part('listing', 'listview');
}

add_action('geodir_author_content_inside', 'geodir_action_author_content_inside', 10);
add_action('geodir_author_content_inside', 'geodir_pagination', 20);

add_action('geodir_author_content', 'geodir_action_author_content', 10);
/**
 * Build the content via hooks for the author page content.
 *
 * @since 1.0.0
 * @package GeoDirectory
 */
function geodir_action_author_content()
{
    /** This action is documented in geodirectory_template_actions.php */
    do_action('geodir_main_content_open', 'author-page', 'geodir-main-content', 'author-page');
    echo '<div class="clearfix">';
    /** This action is documented in geodirectory_template_actions.php */
    do_action('geodir_before_listing');
    echo '</div>';
    /**
     * This is used to add the content to the author page main content.
     *
     * @since 1.0.0
     */
    do_action('geodir_author_content_inside');
    /** This action is documented in geodirectory_template_actions.php */
    do_action('geodir_after_listing');
    /** This action is documented in geodirectory_template_actions.php */
    do_action('geodir_main_content_close', 'author-page');
}


###############################################
########### SEARCH PAGE ACTIONS ###############
###############################################


/**
 * Calls and outputs the template for the search page content section.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global string $gridview_columns The girdview style of the listings.
 */
function geodir_action_search_content_inside()
{
    global $gridview_columns;
    $listing_view = geodir_get_option('geodir_search_view');
    if (strstr($listing_view, 'gridview')) {
        $gridview_columns = $listing_view;
        $listing_view_exp = explode('_', $listing_view);
        $listing_view = $listing_view_exp[0];
    }
    geodir_get_template_part('listing', 'listview');
}

add_action('geodir_search_content_inside', 'geodir_action_search_content_inside', 10);
add_action('geodir_search_content_inside', 'geodir_pagination', 20);

add_action('geodir_search_content', 'geodir_action_search_content', 10);

/**
 * Build the content via hooks for the search page content.
 *
 * @since 1.0.0
 * @package GeoDirectory
 */
function geodir_action_search_content()
{
    /** This action is documented in geodirectory_template_actions.php */
    do_action('geodir_main_content_open', 'search-page', 'geodir-main-content', 'search-page');
    echo '<div class="clearfix">';
    /** This action is documented in geodirectory_template_actions.php */
    do_action('geodir_before_listing');
    echo '</div>';
    /**
     * This is used to add the content to the search page main content.
     *
     * @since 1.0.0
     */
    do_action('geodir_search_content_inside');
    /** This action is documented in geodirectory_template_actions.php */
    do_action('geodir_after_listing');
    /** This action is documented in geodirectory_template_actions.php */
    do_action('geodir_main_content_close', 'search-page');
}

###############################################
############# HOME PAGE ACTIONS ###############
###############################################

/**
 *
 * @since 1.0.0
 * @package GeoDirectory
 */
function geodir_action_home_content()
{
    /** This action is documented in geodirectory_template_actions.php */
    do_action('geodir_main_content_open', 'home-page', 'geodir-main-content', 'home-page');
    /**
     * This called before the home page main content.
     *
     * @since 1.0.0
     */
    do_action('geodir_before_home_content');
    /**
     * This is used to add the content to the home page main content.
     *
     * @since 1.0.0
     */
    do_action('geodir_home_content_inside');
    /**
     * This is called after the homepage main content.
     *
     * @since 1.0.0
     */
    do_action('geodir_after_home_content');
    /** This action is documented in geodirectory_template_actions.php */
    do_action('geodir_main_content_close', 'home-page');
}


add_filter('geodir_filter_widget_listings_fields', 'geodir_function_widget_listings_fields');
add_filter('geodir_filter_widget_listings_join', 'geodir_function_widget_listings_join');
add_filter('geodir_filter_widget_listings_where', 'geodir_function_widget_listings_where');
add_filter('geodir_filter_widget_listings_orderby', 'geodir_function_widget_listings_orderby');
add_filter('geodir_filter_widget_listings_limit', 'geodir_function_widget_listings_limit');

/**
 * Filters the JOIN clause in the SQL for an adjacent post query.
 *
 * @since 1.6.16
 * @package GeoDirectory
 *
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 *
 * @param string  $join           The JOIN clause in the SQL.
 * @param bool    $in_same_term   Whether post should be in a same taxonomy term.
 * @param array   $excluded_terms Array of excluded term IDs.
 * @param string  $taxonomy       Taxonomy. Used to identify the term used when `$in_same_term` is true.
 * @param WP_Post $post           WP_Post object.
 * @return string Filtered SQL JOIN clause.
 */
function geodir_previous_next_post_join( $join, $in_same_term, $excluded_terms, $taxonomy, $post ) {
    global $plugin_prefix;

    if ( !empty($post->post_type) && in_array( $post->post_type, geodir_get_posttypes() ) ) {
        $join .= " INNER JOIN " . $plugin_prefix . $post->post_type . "_detail AS gd ON gd.post_id = p.ID";
    }
    
    return $join;
}
add_filter( 'get_previous_post_join', 'geodir_previous_next_post_join', 10, 5 );
add_filter( 'get_next_post_join', 'geodir_previous_next_post_join', 10, 5 );

/**
 * Filters the WHERE clause in the SQL for an adjacent post query.
 *
 * @since 1.6.16
 * @package GeoDirectory
 *
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 *
 * @param string $where          The `WHERE` clause in the SQL.
 * @param bool   $in_same_term   Whether post should be in a same taxonomy term.
 * @param array  $excluded_terms Array of excluded term IDs.
 * @param string $taxonomy       Taxonomy. Used to identify the term used when `$in_same_term` is true.
 * @param WP_Post $post          WP_Post object.
 * @return string Filtered SQL WHERE clause.
 */
function geodir_previous_next_post_where( $where, $in_same_term, $excluded_terms, $taxonomy, $post ) {
    global $wpdb, $plugin_prefix;

    if ( !empty($post->post_type) && ( !empty( $post->country_slug ) || !empty( $post->region_slug ) || !empty( $post->city_slug ) ) && in_array( $post->post_type, geodir_get_posttypes() ) ) {
        $post_locations = '';
        $post_locations_var = array();
        
        if ( !empty( $post->country_slug ) ) {
            $post_locations .= " AND post_locations LIKE %s";
            $post_locations_var[] = "%,[" . $post->country_slug . "]";
        }

        if ( !empty( $post->region_slug ) ) {
            $post_locations .= " AND post_locations LIKE %s";
            $post_locations_var[] = "%,[" . $post->region_slug . "],%";
        }

        if ( !empty( $post->city_slug ) ) {
            $post_locations .= " AND post_locations LIKE %s";
            $post_locations_var[] = "[" . $post->city_slug . "],%";
        }
        
        $where .= $wpdb->prepare( $post_locations, $post_locations_var );
    }
    
    return $where;
}
add_filter( 'get_previous_post_where', 'geodir_previous_next_post_where', 10, 5 );
add_filter( 'get_next_post_where', 'geodir_previous_next_post_where', 10, 5 );

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