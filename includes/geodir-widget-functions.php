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

// Include widget classes.
include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-advance-search.php' );
include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-advertise.php' );
include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-best-of.php' );
include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-cpt-categories.php' );
include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-features.php' );
include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-flickr.php' );
include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-home-page-map.php' );
include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-listing-page-map.php' );
include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-listing-slider.php' );
include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-login.php' );
include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-subscribe.php' );
include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-popular-post-category.php' );
include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-popular-post-view.php' );
include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-recent-reviews.php' );
include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-related-listing.php' );
include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-social-like.php' );
include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-twitter.php' );
// Detail page widgets
//include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-detail-sidebar.php' );
include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-detail-social-sharing.php' );
include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-detail-google-analytics.php' );
include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-detail-user-actions.php' );
include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-detail-rating-stars.php' );
include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-detail-sidebar-info.php' );

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
    register_widget( 'GeoDir_Widget_Detail_Google_Analytics' );
    register_widget( 'GeoDir_Widget_Detail_User_Actions' );
    register_widget( 'GeoDir_Widget_Detail_Rating_Stars' );
    register_widget( 'GeoDir_Widget_Detail_Sidebar_Info' );
}
add_action( 'widgets_init', 'goedir_register_widgets' );

/**
 * Registers GeoDirectory sidebar.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global array $geodir_sidebars List of geodirectory sidebars.
 */
function geodir_register_sidebar()
{
    global $geodir_sidebars;

    if (function_exists('register_sidebar')) {
        /*===========================*/
        /* Home page sidebars start*/
        /*===========================*/

        /**
         * Filter the `$before_widget` widget opening HTML tag.
         *
         * @since 1.0.0
         * @param string $var The HTML string to filter. Default = '<section id="%1$s" class="widget geodir-widget %2$s">'.
         * @see 'geodir_after_widget'
         */
        $before_widget = apply_filters('geodir_before_widget', '<section id="%1$s" class="widget geodir-widget %2$s">');
        /**
         * Filter the `$after_widget` widget closing HTML tag.
         *
         * @since 1.0.0
         * @param string $var The HTML string to filter. Default = '</section>'.
         * @see 'geodir_before_widget'
         */
        $after_widget = apply_filters('geodir_after_widget', '</section>');
        /**
         * Filter the `$before_title` widget title opening HTML tag.
         *
         * @since 1.0.0
         * @param string $var The HTML string to filter. Default = '<h3 class="widget-title">'.
         * @see 'geodir_after_title'
         */
        $before_title = apply_filters('geodir_before_title', '<h3 class="widget-title">');
        /**
         * Filter the `$after_title` widget title closing HTML tag.
         *
         * @since 1.0.0
         * @param string $var The HTML string to filter. Default = '</h3>'.
         * @see 'geodir_before_title'
         */
        $after_title = apply_filters('geodir_after_title', '</h3>');

        if (geodir_get_option('geodir_show_home_top_section')) {
            register_sidebars(1, array('id' => 'geodir_home_top', 'name' => __('GD Home Top Section', 'geodirectory'), 'before_widget' => $before_widget, 'after_widget' => $after_widget, 'before_title' => $before_title, 'after_title' => $after_title));

            $geodir_sidebars[] = 'geodir_home_top';
        }

        if (geodir_get_option('geodir_show_home_contant_section')) {
            register_sidebars(1, array('id' => 'geodir_home_content', 'name' => __('GD Home Content Section', 'geodirectory'), 'before_widget' => $before_widget, 'after_widget' => $after_widget, 'before_title' => $before_title, 'after_title' => $after_title));

            $geodir_sidebars[] = 'geodir_home_content';
        }

        if (geodir_get_option('geodir_show_home_right_section')) {
            register_sidebars(1, array('id' => 'geodir_home_right', 'name' => __('GD Home Right Section', 'geodirectory'), 'before_widget' => $before_widget, 'after_widget' => $after_widget, 'before_title' => $before_title, 'after_title' => $after_title));

            $geodir_sidebars[] = 'geodir_home_right';
        }

        if (geodir_get_option('geodir_show_home_left_section')) {
            register_sidebars(1, array('id' => 'geodir_home_left', 'name' => __('GD Home Left Section', 'geodirectory'), 'before_widget' => $before_widget, 'after_widget' => $after_widget, 'before_title' => $before_title, 'after_title' => $after_title));

            $geodir_sidebars[] = 'geodir_home_left';
        }

        if (geodir_get_option('geodir_show_home_bottom_section')) {
            register_sidebars(1, array('id' => 'geodir_home_bottom', 'name' => __('GD Home Bottom Section', 'geodirectory'), 'before_widget' => $before_widget, 'after_widget' => $after_widget, 'before_title' => $before_title, 'after_title' => $after_title));

            $geodir_sidebars[] = 'geodir_home_bottom';
        }

        /*===========================*/
        /* Home page sidebars end*/
        /*===========================*/

        /*===========================*/
        /* Listing page sidebars start*/
        /*===========================*/

        if (geodir_get_option('geodir_show_listing_top_section')) {
            register_sidebars(1, array('id' => 'geodir_listing_top', 'name' => __('GD Listing Top Section', 'geodirectory'), 'before_widget' => $before_widget, 'after_widget' => $after_widget, 'before_title' => $before_title, 'after_title' => $after_title));

            $geodir_sidebars[] = 'geodir_listing_top';
        }

        if (geodir_get_option('geodir_show_listing_left_section')) {
            register_sidebars(1, array('id' => 'geodir_listing_left_sidebar', 'name' => __('GD Listing Left Sidebar', 'geodirectory'), 'before_widget' => $before_widget, 'after_widget' => $after_widget, 'before_title' => $before_title, 'after_title' => $after_title));

            $geodir_sidebars[] = 'geodir_listing_left_sidebar';
        }

        if (geodir_get_option('geodir_show_listing_right_section')) {
            register_sidebars(1, array('id' => 'geodir_listing_right_sidebar', 'name' => __('GD Listing Right Sidebar', 'geodirectory'), 'before_widget' => $before_widget, 'after_widget' => $after_widget, 'before_title' => $before_title, 'after_title' => $after_title));

            $geodir_sidebars[] = 'geodir_listing_right_sidebar';
        }

        if (geodir_get_option('geodir_show_listing_bottom_section')) {
            register_sidebars(1, array('id' => 'geodir_listing_bottom', 'name' => __('GD Listing Bottom Section', 'geodirectory'), 'before_widget' => $before_widget, 'after_widget' => $after_widget, 'before_title' => $before_title, 'after_title' => $after_title));

            $geodir_sidebars[] = 'geodir_listing_bottom';
        }

        /*===========================*/
        /* Listing page sidebars start*/
        /*===========================*/

        /*===========================*/
        /* Search page sidebars start*/
        /*===========================*/

        if (geodir_get_option('geodir_show_search_top_section')) {
            register_sidebars(1, array('id' => 'geodir_search_top', 'name' => __('GD Search Top Section', 'geodirectory'), 'before_widget' => $before_widget, 'after_widget' => $after_widget, 'before_title' => $before_title, 'after_title' => $after_title));

            $geodir_sidebars[] = 'geodir_search_top';
        }

        if (geodir_get_option('geodir_show_search_left_section')) {
            register_sidebars(1, array('id' => 'geodir_search_left_sidebar', 'name' => __('GD Search Left Sidebar', 'geodirectory'), 'before_widget' => $before_widget, 'after_widget' => $after_widget, 'before_title' => $before_title, 'after_title' => $after_title));

            $geodir_sidebars[] = 'geodir_search_left_sidebar';
        }

        if (geodir_get_option('geodir_show_search_right_section')) {
            register_sidebars(1, array('id' => 'geodir_search_right_sidebar', 'name' => __('GD Search Right Sidebar', 'geodirectory'), 'before_widget' => $before_widget, 'after_widget' => $after_widget, 'before_title' => $before_title, 'after_title' => $after_title));

            $geodir_sidebars[] = 'geodir_search_right_sidebar';
        }

        if (geodir_get_option('geodir_show_search_bottom_section')) {
            register_sidebars(1, array('id' => 'geodir_search_bottom', 'name' => __('GD Search Bottom Section', 'geodirectory'), 'before_widget' => $before_widget, 'after_widget' => $after_widget, 'before_title' => $before_title, 'after_title' => $after_title));

            $geodir_sidebars[] = 'geodir_search_bottom';
        }

        /*===========================*/
        /* Search page sidebars end*/
        /*===========================*/

        /*==================================*/
        /* Detail/Single page sidebars start*/
        /*==================================*/
        if (geodir_get_option('geodir_show_detail_top_section')) {
            register_sidebars(1, array('id' => 'geodir_detail_top', 'name' => __('GD Detail Top Section', 'geodirectory'), 'before_widget' => $before_widget, 'after_widget' => $after_widget, 'before_title' => $before_title, 'after_title' => $after_title));

            $geodir_sidebars[] = 'geodir_detail_top';
        }

        register_sidebars(1, array('id' => 'geodir_detail_sidebar', 'name' => __('GD Detail Sidebar', 'geodirectory'), 'before_widget' => $before_widget, 'after_widget' => $after_widget, 'before_title' => $before_title, 'after_title' => $after_title));

        $geodir_sidebars[] = 'geodir_detail_sidebar';

        if (geodir_get_option('geodir_show_detail_bottom_section')) {
            register_sidebars(1, array('id' => 'geodir_detail_bottom', 'name' => __('GD Detail Bottom Section', 'geodirectory'), 'before_widget' => $before_widget, 'after_widget' => $after_widget, 'before_title' => $before_title, 'after_title' => $after_title));

            $geodir_sidebars[] = 'geodir_detail_bottom';
        }

        /*==================================*/
        /* Detail/Single page sidebars end*/
        /*==================================*/

        /*==================================*/
        /* Author page sidebars start       */
        /*==================================*/

        if (geodir_get_option('geodir_show_author_top_section')) {
            register_sidebars(1, array('id' => 'geodir_author_top', 'name' => __('GD Author Top Section', 'geodirectory'), 'before_widget' => $before_widget, 'after_widget' => $after_widget, 'before_title' => $before_title, 'after_title' => $after_title));

            $geodir_sidebars[] = 'geodir_author_top';
        }

        if (geodir_get_option('geodir_show_author_left_section')) {
            register_sidebars(1, array('id' => 'geodir_author_left_sidebar', 'name' => __('GD Author Left Sidebar', 'geodirectory'), 'before_widget' => $before_widget, 'after_widget' => $after_widget, 'before_title' => $before_title, 'after_title' => $after_title));

            $geodir_sidebars[] = 'geodir_author_left_sidebar';
        }

        if (geodir_get_option('geodir_show_author_right_section')) {
            register_sidebars(1, array('id' => 'geodir_author_right_sidebar', 'name' => __('GD Author Right Sidebar', 'geodirectory'), 'before_widget' => $before_widget, 'after_widget' => $after_widget, 'before_title' => $before_title, 'after_title' => $after_title));

            $geodir_sidebars[] = 'geodir_author_right_sidebar';
        }

        if (geodir_get_option('geodir_show_author_bottom_section')) {
            register_sidebars(1, array('id' => 'geodir_author_bottom', 'name' => __('GD Author Bottom Section', 'geodirectory'), 'before_widget' => $before_widget, 'after_widget' => $after_widget, 'before_title' => $before_title, 'after_title' => $after_title));

            $geodir_sidebars[] = 'geodir_author_bottom';
        }

        /*==================================*/
        /* Author page sidebars end         */
        /*==================================*/

        /*==================================*/
        /* Add listing page sidebars start       */
        /*==================================*/

        register_sidebars(1, array('id' => 'geodir_add_listing_sidebar', 'name' => __('GD Add Listing Right Sidebar', 'geodirectory'), 'before_widget' => $before_widget, 'after_widget' => $after_widget, 'before_title' => $before_title, 'after_title' => $after_title));

        $geodir_sidebars[] = 'geodir_add_listing_sidebar';

        /*==================================*/
        /* Add listing page sidebars end         */
        /*==================================*/
    }
}

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
    gd_die();
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
            if(geodir_var.geodir_lazy_load==1){
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
    $review_table = GEODIR_REVIEW_TABLE;
    $request = "SELECT comment_ID FROM $review_table WHERE post_id = $post->ID ORDER BY post_date DESC, id DESC LIMIT 1";
    $comments = $wpdb->get_results($request);

    if ($comments) {
        foreach ($comments as $comment) {
            // Set the extra comment info needed.
            $comment_extra = $wpdb->get_row("SELECT * FROM $wpdb->comments WHERE comment_ID =$comment->comment_ID");
            $comment_content = $comment_extra->comment_content;
            $excerpt = strip_tags($comment_content);
        }
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

function gd_features_parse_image($image, $icon_color) {
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

function gd_features_parse_desc($desc) {
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