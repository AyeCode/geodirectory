<?php
/**
 * GeoDirectory Widget Functions
 *
 * Widget related functions and widget registration.
 *
 * @package 	GeoDirectory
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


// the autoloader will load these now just by registering the widget
//// Include widget classes.
//include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-advance-search.php' );
//include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-advertise.php' );
//include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-best-of.php' );
//include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-cpt-categories.php' );
//include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-features.php' );
//include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-flickr.php' );
//include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-home-page-map.php' );
//include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-listing-page-map.php' );
//include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-listing-slider.php' );
//include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-login.php' );
//include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-subscribe.php' );
//include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-popular-post-category.php' );
//include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-popular-post-view.php' );
//include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-recent-reviews.php' );
//include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-related-listing.php' );
//include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-social-like.php' );
//include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-twitter.php' );
//// Detail page widgets
////include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-detail-sidebar.php' );
//include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-detail-social-sharing.php' );
//include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-detail-google-analytics.php' );
//include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-detail-user-actions.php' );
//include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-detail-rating-stars.php' );
//include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-detail-sidebar-info.php' );
//include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-detail-meta.php' );
//include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-detail-slider.php' );


/**
 * Register Widgets.
 *
 * @since 2.0.0
 */
function goedir_register_widgets() {
	register_widget( 'GeoDir_Widget_Advance_Search' );
    register_widget( 'GeoDir_Widget_Advertise' );
    register_widget( 'GeoDir_Widget_Best_Of' );
    register_widget( 'GeoDir_Widget_CPT_Categories' );
    register_widget( 'GeoDir_Widget_Features' );
    register_widget( 'GeoDir_Widget_Flickr' );
    register_widget( 'GeoDir_Widget_Home_Page_Map' );
    register_widget( 'GeoDir_Widget_Listing_Page_Map' );
    register_widget( 'GeoDir_Widget_Listing_Slider' );
    register_widget( 'GeoDir_Widget_Login' );
    register_widget( 'GeoDir_Widget_Subscribe' );
    register_widget( 'GeoDir_Widget_Popular_Post_Category' );
    register_widget( 'GeoDir_Widget_Popular_Post_View' );
    register_widget( 'GeoDir_Widget_Recent_Reviews' );
    register_widget( 'GeoDir_Widget_Related_Listing' );
    register_widget( 'GeoDir_Widget_Social_Like' );
    register_widget( 'GeoDir_Widget_Twitter' );
    
    // Register detail page widgets
    //register_widget( 'GeoDir_Widget_Detail_Sidebar' );
    register_widget( 'GeoDir_Widget_Detail_Social_Sharing' );
    register_widget( 'GeoDir_Widget_Detail_User_Actions' );
    register_widget( 'GeoDir_Widget_Detail_Sidebar_Info' );
    register_widget( 'GeoDir_Widget_Detail_Meta' );

    // post widgets
    register_widget( 'GeoDir_Widget_Post_Images' );
    register_widget( 'GeoDir_Widget_Post_Title' );
    register_widget( 'GeoDir_Widget_Post_Rating' );
    register_widget( 'GeoDir_Widget_Post_Fav' );


}
add_action( 'widgets_init', 'goedir_register_widgets' );


/**
 * Display the best of widget listings using the given query args.
 *
 * @since 1.3.9
 *
 * @global object $post The current post object.
 * @global array $map_jason Map data in json format.
 * @global array $map_canvas_arr Map canvas array.
 * @global string $gridview_columns_widget The girdview style of the listings for widget.
 * @global object $gd_session GeoDirectory Session object.
 *
 * @param array $query_args The query array.
 */
function geodir_bestof_places_by_term($query_args) {
    global $gd_session;

    /**
     * This action called before querying widget listings.
     *
     * @since 1.0.0
     */
    do_action('geodir_bestof_get_widget_listings_before');

    $widget_listings = geodir_get_widget_listings($query_args);

    /**
     * This action called after querying widget listings.
     *
     * @since 1.0.0
     */
    do_action('geodir_bestof_get_widget_listings_after');

    $character_count = isset($query_args['excerpt_length']) ? $query_args['excerpt_length'] : '';

    if (!isset($character_count)) {
        /** This filter is documented in geodirectory-widgets/geodirectory_bestof_widget.php */
        $character_count = $character_count == '' ? 50 : apply_filters('bestof_widget_character_count', $character_count);
    }

    global $post, $map_jason, $map_canvas_arr, $gridview_columns_widget, $geodir_is_widget_listing;
    $current_post = $post;
    $current_map_jason = $map_jason;
    $current_map_canvas_arr = $map_canvas_arr;
    $current_grid_view = $gridview_columns_widget;
    $gridview_columns_widget = null;

    $gd_listing_view_set = $gd_session->get('gd_listing_view') ? true : false;
    $gd_listing_view_old = $gd_listing_view_set ? $gd_session->get('gd_listing_view') : '';

    $gd_session->set('gd_listing_view', '1');
    $geodir_is_widget_listing = true;

    geodir_get_template( 'widget-listing-listview.php', array( 'widget_listings' => $widget_listings, 'character_count' => $character_count, 'gridview_columns_widget' => $gridview_columns_widget, 'before_widget' => $before_widget ) );

    $geodir_is_widget_listing = false;

    $GLOBALS['post'] = $current_post;
    if (!empty($current_post)) {
        setup_postdata($current_post);
    }
    if ($gd_listing_view_set) { // Set back previous value
        $gd_session->set('gd_listing_view', $gd_listing_view_old);
    } else {
        $gd_session->un_set('gd_listing_view');
    }
    $map_jason = $current_map_jason;
    $map_canvas_arr = $current_map_canvas_arr;
    $gridview_columns_widget = $current_grid_view;
}

// Ajax functions
add_action('wp_ajax_geodir_bestof', 'geodir_bestof_callback');
add_action('wp_ajax_nopriv_geodir_bestof', 'geodir_bestof_callback');

/**
 * Get the best of widget content using ajax.
 *
 * @since 1.3.9
 * @since 1.5.1 Added filter to view all link.
 *
 * @return string Html content.
 */
function geodir_bestof_callback() {
    check_ajax_referer('geodir-bestof-nonce', 'geodir_bestof_nonce');
    //set variables
    $post_type = strip_tags(esc_sql($_POST['post_type']));
    $post_limit = strip_tags(esc_sql($_POST['post_limit']));
    $character_count = strip_tags(esc_sql($_POST['char_count']));
    $taxonomy = strip_tags(esc_sql($_POST['taxonomy']));
    $add_location_filter = strip_tags(esc_sql($_POST['add_location_filter']));
    $term_id = strip_tags(esc_sql($_POST['term_id']));
    $excerpt_type = strip_tags(esc_sql($_POST['excerpt_type']));

    $query_args = array(
        'posts_per_page' => $post_limit,
        'is_geodir_loop' => true,
        'post_type' => $post_type,
        'gd_location' => $add_location_filter ? true : false,
        'order_by' => 'high_review'
    );

    if ($character_count >= 0) {
        $query_args['excerpt_length'] = $character_count;
    }

    $tax_query = array(
        'taxonomy' => $taxonomy,
        'field' => 'id',
        'terms' => $term_id
    );

    $query_args['tax_query'] = array($tax_query);
    if ($term_id && $taxonomy) {
        $term = get_term_by('id', $term_id, $taxonomy);
        $view_all_link = add_query_arg(array('sort_by' => 'rating_count_desc'), get_term_link($term));
        /** This filter is documented in geodirectory-widgets/geodirectory_bestof_widget.php */
        $view_all_link = apply_filters('geodir_bestof_widget_view_all_link', $view_all_link, $post_type, $term);

        echo '<h3 class="bestof-cat-title">' . wp_sprintf(__('Best of %s', 'geodirectory'), $term->name) . '<a href="' . esc_url($view_all_link) . '">' . __("View all", 'geodirectory') . '</a></h3>';
    }
    if ($excerpt_type == 'show-reviews') {
        add_filter('get_the_excerpt', 'best_of_show_review_in_excerpt');
    }
    geodir_bestof_places_by_term($query_args);
    if ($excerpt_type == 'show-reviews') {
        remove_filter('get_the_excerpt', 'best_of_show_review_in_excerpt');
    }
    geodir_die();
}

// Javascript
add_action('wp_footer', 'geodir_bestof_js');

/**
 * Adds the javascript in the footer for best of widget.
 *
 * @since 1.3.9
 */
function geodir_bestof_js() {
    $ajax_nonce = wp_create_nonce("geodir-bestof-nonce");
?>
<script type="text/javascript">
jQuery(document).ready(function () {
    jQuery('.geodir-bestof-cat-list a, #geodir_bestof_tab_dd').on("click change", function (e) {
        var widgetBox = jQuery(this).closest('.geodir_bestof_widget');
        var loading = jQuery(widgetBox).find("#geodir-bestof-loading");
        var container = jQuery(widgetBox).find('#geodir-bestof-places');

        jQuery(document).ajaxStart(function () {
            //container.hide(); // Not working when more then one widget on page
            //loading.show();
        }).ajaxStop(function () {
            loading.hide();
            container.fadeIn('slow');
        });

        e.preventDefault();

        var activeTab = jQuery(this).closest('dl').find('dd.geodir-tab-active');
        activeTab.removeClass('geodir-tab-active');
        jQuery(this).parent().addClass('geodir-tab-active');

        var term_id = 0;
        if (e.type === "change") {
            term_id = jQuery(this).val();
        } else if (e.type === "click") {
            term_id = jQuery(this).attr('data-termid');
        }

        var post_type = jQuery(widgetBox).find('#bestof_widget_post_type').val();
        var excerpt_type = jQuery(widgetBox).find('#bestof_widget_excerpt_type').val();
        var post_limit = jQuery(widgetBox).find('#bestof_widget_post_limit').val();
        var taxonomy = jQuery(widgetBox).find('#bestof_widget_taxonomy').val();
        var char_count = jQuery(widgetBox).find('#bestof_widget_char_count').val();
        var add_location_filter = jQuery(widgetBox).find('#bestof_widget_location_filter').val();

        var data = {
            'action': 'geodir_bestof',
            'geodir_bestof_nonce': '<?php echo $ajax_nonce; ?>',
            'post_type': post_type,
            'excerpt_type': excerpt_type,
            'post_limit': post_limit,
            'taxonomy': taxonomy,
            'geodir_ajax': true,
            'term_id': term_id,
            'char_count': char_count,
            'add_location_filter': add_location_filter
        };

        container.hide();
        loading.show();

        jQuery.post(geodir_var.geodir_ajax_url, data, function (response) {
            container.html(response);
            jQuery(widgetBox).find('.geodir_category_list_view li .geodir-post-img .geodir_thumbnail img').css('display', 'block');

            // start lazy load if it's turned on
            if(geodirectory_params.lazy_load==1){
                geodir_init_lazy_load();
            }

        });
    })
});
jQuery(document).ready(function () {
    if (jQuery(window).width() < 660) {
        if (jQuery('.bestof-widget-tab-layout').hasClass('bestof-tabs-on-left')) {
            jQuery('.bestof-widget-tab-layout').removeClass('bestof-tabs-on-left').addClass('bestof-tabs-as-dropdown');
        } else if (jQuery('.bestof-widget-tab-layout').hasClass('bestof-tabs-on-top')) {
            jQuery('.bestof-widget-tab-layout').removeClass('bestof-tabs-on-top').addClass('bestof-tabs-as-dropdown');
        }
    }
});
</script>
<?php
}

function best_of_show_review_in_excerpt($excerpt) {
    global $wpdb, $post;

    $query = $wpdb->prepare( "SELECT cmt.comment_content FROM " . GEODIR_REVIEW_TABLE . " AS r INNER JOIN {$wpdb->comments} AS cmt ON cmt.comment_ID = r.comment_id WHERE cmt.comment_post_ID = %d ORDER BY cmt.comment_date DESC, cmt.comment_id DESC", array( $post->ID ) );
    $review = $wpdb->get_row( $query );

    if ( ! empty( $review ) ) {
        $excerpt = strip_tags( $review->comment_content );
    }

    return $excerpt;
}

/**
 * Get the cpt categories content.
 *
 * @since 1.5.4
 * @since 1.6.6 New parameters $no_cpt_filter &no_cat_filter added.
 *
 * @global object $post The post object.
 * @global bool $gd_use_query_vars If true then use query vars to get current location terms.
 *
 * @param array $params An array of cpt categories parameters.
 * @return string CPT categories content.
 */
function geodir_cpt_categories_output($params) {
    global $post, $gd_use_query_vars;
    
    $old_gd_use_query_vars = $gd_use_query_vars;
    
    $gd_use_query_vars = geodir_is_page('detail') ? true : false;
    
    $args = wp_parse_args((array)$params,
        array(
            'title' => '',
            'post_type' => array(), // NULL for all
            'hide_empty' => '',
            'show_count' => '',
            'hide_icon' => '',
            'cpt_left' => '',
            'sort_by' => 'count',
            'max_count' => 'all',
            'max_level' => '1',
            'no_cpt_filter' => '',
            'no_cat_filter' => '',
        )
    );

    $sort_by = isset($args['sort_by']) && in_array($args['sort_by'], array('az', 'count')) ? $args['sort_by'] : 'count';
    $cpt_filter = empty($args['no_cpt_filter']) ? true : false;
    $cat_filter = empty($args['no_cat_filter']) ? true : false;

    $gd_post_types = geodir_get_posttypes('array');

    $post_type_arr = !is_array($args['post_type']) ? explode(',', $args['post_type']) : $args['post_type'];
    $current_posttype = geodir_get_current_posttype();

    $is_listing = false;
    $is_detail = false;
    $is_category = false;
    $post_ID = 0;
    $is_listing_page = geodir_is_page('listing');
    $is_detail_page = geodir_is_page('detail');
    if ($is_listing_page || $is_detail_page) {
        $current_posttype = geodir_get_current_posttype();

        if ($current_posttype != '' && isset($gd_post_types[$current_posttype])) {
            if ($is_detail_page) {
                $is_detail = true;
                $post_ID = is_object($post) && !empty($post->ID) ? (int)$post->ID : 0;
            } else {
                $is_listing = true;
                if (is_tax()) { // category page
                    $current_term_id = get_queried_object_id();
                    $current_taxonomy = get_query_var('taxonomy');
                    $current_posttype = geodir_get_current_posttype();

                    if ($current_term_id && $current_posttype && get_query_var('taxonomy') == $current_posttype . 'category') {
                        $is_category = true;
                    }
                }
            }
        }
    }

    $parent_category = 0;
    if (($is_listing || $is_detail) && $cpt_filter) {
        $post_type_arr = array($current_posttype);
    }

    $post_types = array();
    if (!empty($post_type_arr)) {
        if (in_array('0', $post_type_arr)) {
            $post_types = $gd_post_types;
        } else {
            foreach ($post_type_arr as $cpt) {
                if (isset($gd_post_types[$cpt])) {
                    $post_types[$cpt] = $gd_post_types[$cpt];
                }
            }
        }
    }

    if (empty($post_type_arr)) {
        $post_types = $gd_post_types;
    }

    $hide_empty = !empty($args['hide_empty']) ? true : false;
    $max_count = strip_tags($args['max_count']);
    $all_childs = $max_count == 'all' ? true : false;
    $max_count = $max_count > 0 ? (int)$max_count : 0;
    $max_level = strip_tags($args['max_level']);
    $show_count = !empty($args['show_count']) ? true : false;
    $hide_icon = !empty($args['hide_icon']) ? true : false;
    $cpt_left = !empty($args['cpt_left']) ? true : false;

    if(!$cpt_left){
        $cpt_left = "gd-cpt-flat";
    }else{
        $cpt_left = '';
    }

    $orderby = 'count';
    $order = 'DESC';
    if ($sort_by == 'az') {
        $orderby = 'name';
        $order = 'ASC';
    }

    $output = '';
    if (!empty($post_types)) {
        foreach ($post_types as $cpt => $cpt_info) {
            $parent_category = ($is_category && $cat_filter && $cpt == $current_posttype) ? $current_term_id : 0;
            $cat_taxonomy = $cpt . 'category';
            $skip_childs = false;
            if ($cat_filter && $cpt == $current_posttype && $is_detail && $post_ID) {
                $skip_childs = true;
                $categories = get_terms($cat_taxonomy, array('orderby' => $orderby, 'order' => $order, 'hide_empty' => $hide_empty, 'object_ids' => $post_ID));
            } else {
                $categories = get_terms($cat_taxonomy, array('orderby' => $orderby, 'order' => $order, 'hide_empty' => $hide_empty, 'parent' => $parent_category));
            }

            if ($hide_empty) {
                $categories = geodir_filter_empty_terms($categories);
            }
            if ($sort_by == 'count') {
                $categories = geodir_sort_terms($categories, 'count');
            }

            if (!empty($categories)) {
                $term_icons = !$hide_icon ? geodir_get_term_icon() : array();
                $row_class = '';

                if ($is_listing) {
                    $row_class = $is_category ? ' gd-cptcat-categ' : ' gd-cptcat-listing';
                }
                $cpt_row = '<div class="gd-cptcat-row gd-cptcat-' . $cpt . $row_class . ' '.$cpt_left.'">';

                if ($is_category && $cat_filter && $cpt == $current_posttype) {
                    $term_info = get_term($current_term_id, $cat_taxonomy);

                    $term_icon_url = !empty($term_icons) && isset($term_icons[$term_info->term_id]) ? $term_icons[$term_info->term_id] : '';
                    $term_icon_url = $term_icon_url != '' ? '<img alt="' . esc_attr($term_info->name) . ' icon" src="' . $term_icon_url . '" /> ' : '';

                    $count = $show_count ? ' <span class="gd-cptcat-count">(' . $term_info->count . ')</span>' : '';
                    $cpt_row .= '<h2 class="gd-cptcat-title">' . $term_icon_url . $term_info->name . $count . '</h2>';
                } else {
                    $cpt_row .= '<h2 class="gd-cptcat-title">' . __($cpt_info['labels']['name'], 'geodirectory') . '</h2>';
                }
                foreach ($categories as $category) {
                    $term_icon_url = !empty($term_icons) && isset($term_icons[$category->term_id]) ? $term_icons[$category->term_id] : '';
                    $term_icon_url = $term_icon_url != '' ? '<img alt="' . esc_attr($category->name) . ' icon" src="' . $term_icon_url . '" /> ' : '';

                    $term_link = get_term_link( $category, $category->taxonomy );
                    /** Filter documented in includes/general_functions.php **/
                    $term_link = apply_filters( 'geodir_category_term_link', $term_link, $category->term_id, $cpt );

                    $cpt_row .= '<ul class="gd-cptcat-ul gd-cptcat-parent  '.$cpt_left.'">';
                    $cpt_row .= '<li class="gd-cptcat-li gd-cptcat-li-main">';
                    $count = $show_count ? ' <span class="gd-cptcat-count">(' . $category->count . ')</span>' : '';
                    $cpt_row .= '<h3 class="gd-cptcat-cat"><a href="' . esc_url($term_link) . '" title="' . esc_attr($category->name) . '">'  .$term_icon_url . $category->name . $count . '</a></h3>';
                    if (!$skip_childs && ($all_childs || $max_count > 0) && ($max_level == 'all' || (int)$max_level > 0)) {
                        $cpt_row .= geodir_cpt_categories_child_cats($category->term_id, $cpt, $hide_empty, $show_count, $sort_by, $max_count, $max_level, $term_icons);
                    }
                    $cpt_row .= '</li>';
                    $cpt_row .= '</ul>';
                }
                $cpt_row .= '</div>';

                $output .= $cpt_row;
            }
        }
    }
        
    $gd_use_query_vars = $old_gd_use_query_vars;
    
    return $output;
}

/**
 * Get the child categories content.
 *
 * @since 1.5.4
 *
 * @param int $parent_id Parent category id.
 * @param string $cpt The post type.
 * @param bool $hide_empty If true then filter the empty categories.
 * @param bool $show_count If true then category count will be displayed.
 * @param string $sort_by Sorting order for categories.
 * @param bool|string $max_count Max no of sub-categories count to display.
 * @param bool|string $max_level Max depth level sub-categories to display.
 * @param array $term_icons Array of terms icons url.
 * @param int $depth Category depth level. Default 1.
 * @return string Html content.
 */
function geodir_cpt_categories_child_cats($parent_id, $cpt, $hide_empty, $show_count, $sort_by, $max_count, $max_level, $term_icons, $depth = 1) {
    $cat_taxonomy = $cpt . 'category';

    $orderby = 'count';
    $order = 'DESC';
    if ($sort_by == 'az') {
        $orderby = 'name';
        $order = 'ASC';
    }

    if ($max_level != 'all' && $depth > (int)$max_level ) {
        return '';
    }

    $child_cats = get_terms($cat_taxonomy, array('orderby' => $orderby, 'order' => $order, 'hide_empty' => $hide_empty, 'parent' => $parent_id, 'number' => $max_count));
    if ($hide_empty) {
        $child_cats = geodir_filter_empty_terms($child_cats);
    }

    if (empty($child_cats)) {
        return '';
    }

    if ($sort_by == 'count') {
        $child_cats = geodir_sort_terms($child_cats, 'count');
    }

    $content = '<li class="gd-cptcat-li gd-cptcat-li-sub"><ul class="gd-cptcat-ul gd-cptcat-sub gd-cptcat-sub-' . $depth . '">';
    $depth++;
    foreach ($child_cats as $category) {
        $term_icon_url = !empty($term_icons) && isset($term_icons[$category->term_id]) ? $term_icons[$category->term_id] : '';
        $term_icon_url = $term_icon_url != '' ? '<img alt="' . esc_attr($category->name) . ' icon" src="' . $term_icon_url . '" /> ' : '';
        $term_link = get_term_link( $category, $category->taxonomy );
        /** Filter documented in includes/general_functions.php **/
        $term_link = apply_filters( 'geodir_category_term_link', $term_link, $category->term_id, $cpt );
        $count = $show_count ? ' <span class="gd-cptcat-count">(' . $category->count . ')</span>' : '';

        $content .= '<li class="gd-cptcat-li gd-cptcat-li-sub">';
        $content .= '<a href="' . esc_url($term_link) . '" title="' . esc_attr($category->name) . '">' . $term_icon_url . $category->name . $count . '</a></li>';
        $content .= geodir_cpt_categories_child_cats($category->term_id, $cpt, $hide_empty, $show_count, $sort_by, $max_count, $max_level, $term_icons, $depth);
    }
    $content .= '</li></ul>';

    return $content;
}

function geodir_features_parse_image($image, $icon_color) {
    if (substr($image, 0, 4) === "http") {
        $image = '<img src="' . $image . '" />';
    } elseif (substr($image, 0, 3) === "fa-") {
        if (empty($icon_color)) {
            $icon_color = '#757575';
        }
        $image = '<i style="color:' . $icon_color . '" class="fa ' . $image . '"></i>';
    }
    return $image;
}

function geodir_features_parse_desc($desc) {
    return $desc;
}

/**
 * Enque listing map script.
 *
 * @since 1.0.0
 *
 * @global array $list_map_json Empty array.
 */
function init_listing_map_script() {
    global $list_map_json;

    $list_map_json = array();

}

/**
 * Create listing json for map script.
 *
 * @since 1.0.0
 *
 * @global object $wpdb WordPress Database object.
 * @global array $list_map_json Listing map data in json format.
 * @global bool $add_post_in_marker_array Displays posts in marker array when the value is true.
 */
function create_list_jsondata($post) {
    global $wpdb, $list_map_json, $add_post_in_marker_array;

    if ((is_main_query() || $add_post_in_marker_array) && isset($post->marker_json) && $post->marker_json != '') {
        /**
         * Filter the json data for search listing map.
         *
         * @since 1.5.7
         * @param string $post->marker_json JSON representation of the post marker info.
         * @param object $post The post object.
         */
        $list_map_json[] = apply_filters('geodir_create_list_jsondata',$post->marker_json,$post);
    }
}

/**
 * Send json data to script and show listing map.
 *
 * @since 1.0.0
 *
 * @global array $list_map_json Listing map data in json format.
 */
function show_listing_widget_map() {
    global $list_map_json;

    if (!empty($list_map_json)) {
        $list_map_json = array_unique($list_map_json);
        $cat_content_info[] = implode(',', $list_map_json);
    }

    $totalcount = count(array_unique($list_map_json));


    if (!empty($cat_content_info)) {
        $json_content = substr(implode(',', $cat_content_info), 1);
        $json_content = htmlentities($json_content, ENT_QUOTES, get_option('blog_charset')); // Quotes in csv title import break maps - FIXED by kiran on 2nd March, 2016
        $json_content = wp_specialchars_decode($json_content); // Fixed #post-320722 on 2016-12-08
        $list_json = '[{"totalcount":"' . $totalcount . '",' . $json_content . ']';
    } else {
        $list_json = '[{"totalcount":"0"}]';
    }

    $listing_map_args = array('list_json' => $list_json);

    // Pass the json data in listing map script
    wp_localize_script('geodir-listing-map-widget', 'listing_map_args', $listing_map_args);
}

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

function geodir_is_detail_page_widget( $id_base ) {
    $widgets = geodir_detail_page_widget_id_bases();
    
    $return = ! empty( $id_base ) && ! empty( $widgets ) && in_array( $id_base, $widgets ) ? true : false;
    
    return apply_filters( 'geodir_is_detail_page_widget', $return, $id_base, $widgets );
}

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