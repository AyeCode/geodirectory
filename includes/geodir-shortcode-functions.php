<?php
/**
 * Shortcode functions.
 *
 * @since 1.0.0
 * @package GeoDirectory
 */
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * The geodirectory add listing shortcode.
 *
 * This implements the functionality of the shortcode for displaying geodirectory add listing page form.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @param array $atts {
 *     Attributes of the shortcode.
 *
 *     @type string $pid            Post ID. If passed post will be edited. Default empty.
 *     @type string $listing_type   Post type of listing. Default gd_place.
 *     @type string $login_msg      Message to display when user not logged in.
 *     @type bool   $show_login     Do you want to display login widget when user not logged in?. Default: false.
 *
 * }
 * @return string Add listing page HTML.
 */
function geodir_sc_add_listing( $atts, $content = '' ) {
    $default_post_type = geodir_add_listing_default_post_type();

    $defaults = array(
        'pid'           => '',
        'listing_type'  => $default_post_type,
        'login_msg'     => __( 'You must login to post.', 'geodirectory' ),
        'show_login'    => false,
    );

    $params = shortcode_atts( $defaults, $atts, 'gd_add_listing' );
    
    if ( !empty( $_REQUEST['pid'] ) && $post_type = get_post_type( absint( $_REQUEST['pid'] ) ) ) {
        $params['pid'] = absint( $_REQUEST['pid'] );
        $params['listing_type'] = $post_type;
    } else if ( isset( $_REQUEST['listing_type'] ) ) {
        $params['listing_type'] = sanitize_text_field( $_REQUEST['listing_type'] );
    }

    if ( !geodir_add_listing_check_post_type( $params['listing_type'] ) ) {
        return __( 'Post type has not allowed to add listing.', 'geodirectory' );
    }

    foreach ( $params as $key => $value ) {
        $_REQUEST[ $key ] = $value;
    }

    $user_id = get_current_user_id();

    ob_start();

    if ( !$user_id ) {
        echo $params['login_msg'];

        if ( $params['show_login'] ) {
            echo "<br />";

            $defaults = array(
                'before_widget' => '',
                'after_widget'  => '',
                'before_title'  => '',
                'after_title'   => '',
            );

            geodir_loginwidget_output( $defaults, $defaults );
        }
    } else {
        geodir_render_add_listing_form();
    }

    return ob_get_clean();
}

/**
 * The geodirectory home page map shortcode.
 *
 * This implements the functionality of the shortcode for displaying map on home page.
 *
 * @since 1.0.0
 * @since 1.5.2 Added TERRAIN map type.
 * @package GeoDirectory
 * @param array $atts {
 *     Attributes of the shortcode.
 *
 *     @type string $width           Map width in pixels. Default 960.
 *     @type string $height          Map height in pixels. Default 425.
 *     @type string $maptype         Map type. Default ROADMAP. Can be ROADMAP | SATELLITE | HYBRID | TERRAIN.
 *     @type string $zoom            The zoom level of the map. Between 1-19. Default 13.
 *     @type string $autozoom        True if the map should autozoom, false if not.
 *     @type string $child_collapse  True if the map should collapse the categories, false if not.
 *     @type string $scrollwheel     True to allow scroll wheel to scroll map or false if not.
 *     @type bool   $marker_cluster  Enable marker cluster? Default: false.
 *
 * }
 * @return string Map HTML.
 */
function geodir_sc_home_map($atts)
{
    ob_start();
    $defaults = array(
        'width' => '960',
        'height' => '425',
        'maptype' => 'ROADMAP',
        'zoom' => '13',
        'autozoom' => '',
        'child_collapse' => '0',
        'scrollwheel' => '0',
		'marker_cluster' => false,
        'latitude' => '',
        'longitude' => ''
    );

    $params = shortcode_atts($defaults, $atts);

    $params = gdsc_validate_map_args($params);

    $map_args = array(
        'map_canvas_name' => 'gd_home_map',
        'latitude' => $params['latitude'],
        'longitude' => $params['longitude'],

        /**
         * Filter the widget width of the map on home/listings page.
         *
         * @since 1.0.0
         * @param mixed(string|int|float) $params['width'] The map width.
         */
        'width' => apply_filters('widget_width', $params['width']),
        /**
         * Filter the widget height of the map on home/listings page.
         *
         * @since 1.0.0
         * @param mixed(string|int|float) $params['height'] The map height.
         */
        'height' => apply_filters('widget_heigh', $params['height']),
        /**
         * Filter the widget maptype of the map on home/listings page.
         *
         * @since 1.0.0
		 * @since 1.5.2 Added TERRAIN map type.
         * @param string $params['maptype'] The map type. Can be ROADMAP | SATELLITE | HYBRID | TERRAIN.
         */
        'maptype' => apply_filters('widget_maptype', $params['maptype']),
        /**
         * Filter the widget scrollwheel value of the map on home/listings page.
         *
         * Should the scrollwheel zoom the map or not.
         *
         * @since 1.0.0
         * @param bool $params['scrollwheel'] True to allow scroll wheel to scroll map or false if not.
         */
        'scrollwheel' => apply_filters('widget_scrollwheel', $params['scrollwheel']),
        /**
         * Filter the widget zoom level of the map on home/listings page.
         *
         * @since 1.0.0
         * @param int $params['zoom'] The zoom level of the map. Between 1-19.
         */
        'zoom' => apply_filters('widget_zoom', $params['zoom']),
        /**
         * Filter the widget auto zoom value of the map on home/listings page.
         *
         * If the map should autozoom to fit the markers shown.
         *
         * @since 1.0.0
         * @param bool $params['autozoom'] True if the map should autozoom, false if not.
         */
        'autozoom' => apply_filters('widget_autozoom', $params['autozoom']),
        /**
         * Filter the widget child_collapse value of the map on home/listings page.
         *
         * If the map should auto collapse the child categories if the category bar is present.
         *
         * @since 1.0.0
         * @param bool $params['child_collapse'] True if the map should collapse the categories, false if not.
         */
        'child_collapse' => apply_filters('widget_child_collapse', $params['child_collapse']),
        'enable_cat_filters' => true,
        'enable_text_search' => true,
        'enable_post_type_filters' => true,
        /**
         * Filter the widget enable_location_filters value of the map on home/listings page.
         *
         * This is used when the location addon is used.
         *
         * @since 1.0.0
         * @param bool $val True if location filters should be used, false if not.
         */
        'enable_location_filters' => apply_filters('geodir_home_map_enable_location_filters', false),
        'enable_jason_on_load' => false,
        'enable_marker_cluster' => false,
        'enable_map_resize_button' => true,
        'map_class_name' => 'geodir-map-home-page',
        'is_geodir_home_map_widget' => true,
    );

	// Add marker cluster
	if (isset($params['marker_cluster']) && gdsc_to_bool_val($params['marker_cluster']) && defined('GDCLUSTER_VERSION')) {
        $map_args['enable_marker_cluster'] = true;
        if(geodir_get_option('geodir_marker_cluster_type')) {
            if ($map_args['autozoom']) {
                $map_args['enable_marker_cluster_no_reposition'] = false;
            } else {
                $map_args['enable_marker_cluster_no_reposition'] = true;
            }

            $map_args['enable_marker_cluster_server'] = true ;

        }
	} else {
		$map_args['enable_marker_cluster'] = false;
	}

    // if lat and long set in shortcode, hack it so the map is not repositioned
    if(!empty($params['latitude']) && !empty($params['longitude']) ){
        $map_args['enable_marker_cluster_no_reposition'] = true;
    }


    geodir_draw_map($map_args);

    add_action('wp_footer', 'geodir_home_map_add_script', 100);

    $output = ob_get_contents();

    ob_end_clean();

    return $output;
}

/**
 * The geodirectory listing map shortcode.
 *
 * This implements the functionality of the shortcode for displaying listing map.
 *
 * @since 1.0.0
 * @since 1.5.2 Added TERRAIN for $maptype attribute.
 * @since 1.6.16 CHANGED: New parameters post_type, category & event_type added.
 * @since 1.6.18 FIXED: For CPT other then "gd_place" not working.
 * @package GeoDirectory
 * @global object $post The current post object.
 * @param array $atts {
 *     Attributes of the shortcode.
 *
 *     @type string $width           Map width in pixels. Default 294.
 *     @type string $height          Map height in pixels. Default 370.
 *     @type string $maptype         Map type. Default ROADMAP. Can be ROADMAP | SATELLITE | HYBRID | TERRAIN.
 *     @type string $zoom            The zoom level of the map. Between 1-19. Default 13.
 *     @type string $autozoom        True if the map should autozoom, false if not.
 *     @type bool   $sticky          True if should be sticky, false if not
 *     @type string $showall         Show all listings on map? (not just page list). Default 0.
 *     @type string $child_collapse  True if the map should collapse the categories, false if not.
 *     @type string $scrollwheel     True to allow scroll wheel to scroll map or false if not.
 *     @type bool   $marker_cluster  Enable marker cluster? Default: false.
 *     @type string $post_type       Post type of listing. Default. gd_place.
 *     @type string $category        Category ids to filter listings. Ex: 1,3. Default. 0.
 *     @type string $event_type      The events filter.(for gd_event CPT only) Default: all.
 *
 * }
 * @return string Map HTML.
 */
function geodir_sc_listing_map($atts) {

    // if some params are set then we need a new query, if not then we can use the main query
    if( isset($atts['post_type']) || isset($atts['category']) || isset($atts['event_type']) ) {

        global $add_post_in_marker_array, $gd_sc_map_params;
        $backup_globals                             = array();
        $backup_globals['add_post_in_marker_array'] = $add_post_in_marker_array;
        $backup_globals['gd_sc_map_params']         = $gd_sc_map_params;

        $defaults = array(
            'width'          => '294',
            'height'         => '370',
            'zoom'           => '13',
            'autozoom'       => '',
            'sticky'         => '',
            'showall'        => '0',
            'scrollwheel'    => '0',
            'maptype'        => 'ROADMAP',
            'child_collapse' => 0,
            'marker_cluster' => false,
            'post_type'      => 'gd_place',
            'category'       => '0',
            'event_type'     => 'all'
        );

        $params = shortcode_atts( $defaults, $atts );

        if ( ! ( gdsc_is_post_type_valid( $params['post_type'] ) ) ) {
            $params['post_type'] = 'gd_place';
        }

        // Validate the selected category/ies - Grab the current list based on post_type
        $category_taxonomy = geodir_get_taxonomies( $params['post_type'] );
        $categories        = get_terms( $category_taxonomy, array(
            'orderby' => 'count',
            'order'   => 'DESC',
            'fields'  => 'ids'
        ) );

        // Make sure we have an array
        if ( ! ( is_array( $params['category'] ) ) ) {
            $params['category'] = explode( ',', $params['category'] );
        }

        // Array_intersect returns only the items in $params['category'] that are also in our category list
        // Otherwise it becomes empty and later on that will mean "All"
        $params['category'] = array_intersect( $params['category'], $categories );

        if ( $params['post_type'] == 'gd_event' ) {
            $params['event_type'] = gdsc_validate_list_filter_choice( $params['event_type'] );
        }

        $params = gdsc_validate_map_args( $params );

        $gd_sc_map_params = $params;

        $query_args = array(
            'posts_per_page' => 1000000, //@todo kiran why was this added? 
            'is_geodir_loop' => true,
            'gd_location'    => false,
            'post_type'      => $params['post_type'],
        );

        if ( ! empty( $params['category'] ) && isset( $params['category'][0] ) && (int) $params['category'][0] != 0 ) {
            $category_taxonomy = geodir_get_taxonomies( $params['post_type'] );

            ######### WPML #########
            if ( geodir_wpml_is_taxonomy_translated( $category_taxonomy[0] ) ) {
                $category = gd_lang_object_ids( $params['category'], $category_taxonomy[0] );
            }
            ######### WPML #########

            $tax_query = array(
                'taxonomy' => $category_taxonomy[0],
                'field'    => 'id',
                'terms'    => $params['category']
            );

            $query_args['tax_query'] = array( $tax_query );
        }

        $add_post_in_marker_array = true;

        if ( $params['post_type'] == 'gd_event' && function_exists( 'geodir_event_get_widget_events' ) ) {
            global $geodir_event_widget_listview;
            $geodir_event_widget_listview = true;

            $query_args['geodir_event_type'] = $params['event_type'];

            $listings = geodir_event_get_widget_events( $query_args );

            $geodir_event_widget_listview = false;
        } else {
            $listings = geodir_get_widget_listings( $query_args );
        }

        if ( ! empty( $listings ) ) {
            foreach ( $listings as $listing ) {
                create_marker_jason_of_posts( $listing );
            }
        }

        ob_start();
        add_action( 'wp_head', 'init_listing_map_script' ); // Initialize the map object and marker array

        add_action( 'the_post', 'create_list_jsondata' ); // Add marker in json array

        add_action( 'wp_footer', 'show_listing_widget_map' ); // Show map for listings with markers

        $default_location = geodir_get_default_location();

        $map_args = array(
            'map_canvas_name'          => 'gd_listing_map',
            'width'                    => $params['width'],
            'height'                   => $params['height'],
            'zoom'                     => $params['zoom'],
            'autozoom'                 => $params['autozoom'],
            'sticky'                   => $params['sticky'],
            'showall'                  => $params['showall'],
            'scrollwheel'              => $params['scrollwheel'],
            'maptype'                  => $params['maptype'],
            'child_collapse'           => 0,
            'enable_cat_filters'       => false,
            'enable_text_search'       => false,
            'enable_post_type_filters' => false,
            'enable_location_filters'  => false,
            'enable_jason_on_load'     => true,
            'ajax_url'                 => geodir_get_ajax_url(),
            'latitude'                 => isset( $default_location->city_latitude ) ? $default_location->city_latitude : '',
            'longitude'                => isset( $default_location->city_longitude ) ? $default_location->city_longitude : '',
            'streetViewControl'        => true,
            'showPreview'              => '0',
            'maxZoom'                  => 21,
            'bubble_size'              => 'small',
        );

        if ( is_single() ) {
            global $post;
            if ( isset( $post->post_latitude ) ) {
                $map_args['latitude']  = $post->post_latitude;
                $map_args['longitude'] = $post->post_longitude;
            }

            $map_args['map_class_name'] = 'geodir-map-listing-page-single';
        } else {
            $map_args['map_class_name'] = 'geodir-map-listing-page';
        }

        // Add marker cluster
        if ( isset( $params['marker_cluster'] ) && gdsc_to_bool_val( $params['marker_cluster'] ) && defined( 'GDCLUSTER_VERSION' ) ) {
            $map_args['enable_marker_cluster'] = true;
        } else {
            $map_args['enable_marker_cluster'] = false;
        }

        geodir_draw_map( $map_args );

        $output = ob_get_contents();

        ob_end_clean();

        foreach ( $backup_globals as $global => $value ) {
            ${$global} = $value;
        }

        return $output;
    }else{
        ob_start();
        add_action('wp_head', 'init_listing_map_script'); // Initialize the map object and marker array
        add_action('the_post', 'create_list_jsondata'); // Add marker in json array
        add_action('wp_footer', 'show_listing_widget_map'); // Show map for listings with markers
        $defaults = array(
            'width' => '294',
            'height' => '370',
            'zoom' => '13',
            'autozoom' => '',
            'sticky' => '',
            'showall' => '0',
            'scrollwheel' => '0',
            'maptype' => 'ROADMAP',
            'child_collapse' => 0,
            'marker_cluster' => false
        );
        $params = shortcode_atts($defaults, $atts);
        $params = gdsc_validate_map_args($params);
        $map_args = array(
            'map_canvas_name' => 'gd_listing_map',
            'width' => $params['width'],
            'height' => $params['height'],
            'zoom' => $params['zoom'],
            'autozoom' => $params['autozoom'],
            'sticky' => $params['sticky'],
            'showall' => $params['showall'],
            'scrollwheel' => $params['scrollwheel'],
            'child_collapse' => 0,
            'enable_cat_filters' => false,
            'enable_text_search' => false,
            'enable_post_type_filters' => false,
            'enable_location_filters' => false,
            'enable_jason_on_load' => true,
        );
        if (is_single()) {
            global $post;
            $map_default_lat = $address_latitude = $post->post_latitude;
            $map_default_lng = $address_longitude = $post->post_longitude;
            $mapview = $post->post_mapview;
            $map_args['zoom'] = $post->post_mapzoom;
            $map_args['map_class_name'] = 'geodir-map-listing-page-single';
        } else {
            $default_location = geodir_get_default_location();
            $map_default_lat = isset($default_location->city_latitude) ? $default_location->city_latitude : '';
            $map_default_lng = isset($default_location->city_longitude) ? $default_location->city_longitude : '';
            $map_args['map_class_name'] = 'geodir-map-listing-page';
        }
        if (empty($mapview)) {
            $mapview = 'ROADMAP';
        }
        // Set default map options
        $map_args['ajax_url'] = geodir_get_ajax_url();
        $map_args['latitude'] = $map_default_lat;
        $map_args['longitude'] = $map_default_lng;
        $map_args['streetViewControl'] = true;
        $map_args['maptype'] = $mapview;
        $map_args['showPreview'] = '0';
        $map_args['maxZoom'] = 21;
        $map_args['bubble_size'] = 'small';

        // Add marker cluster
        if (isset($params['marker_cluster']) && gdsc_to_bool_val($params['marker_cluster']) && defined('GDCLUSTER_VERSION')) {
            $map_args['enable_marker_cluster'] = true;
        } else {
            $map_args['enable_marker_cluster'] = false;
        }
        geodir_draw_map($map_args);
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }
}

/**
 * The geodirectory listing slider shortcode.
 *
 * This implements the functionality of the shortcode for displaying listing slider.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @param array $atts {
 *     Attributes of the shortcode.
 *
 *     @type string $animation              Controls the animation type, "fade" or "slide". Default. slide.
 *     @type int    $animation_loop         Gives the slider a seamless infinite loop. Default. 0.
 *     @type int    $animation_speed        Set the speed of animations, in milliseconds. Default. 600.
 *     @type string $category               Filter by term. Can be any valid term. Default. 0.
 *     @type int    $direction_nav          Enable previous/next arrow navigation?. Can be 1 or 0. Default. 0.
 *     @type string $order_by               Order by filter. Default. latest.
 *     @type string $post_number            Number of listings to display. Default. 5.
 *     @type string $post_type              Post type of listing. Default. gd_place.
 *     @type string $show_featured_only     Do you want to display only featured listing? Can be 1 or 0. Default. Empty.
 *     @type string $show_title             Do you want to display title? Can be 1 or 0. Default. Empty.
 *     @type string $slideshow              Setup a slideshow for the slider to animate automatically. Default. 0.
 *     @type int    $slideshow_speed        Set the speed of the slideshow cycling, in milliseconds. Default. 5000.
 *     @type string $title                  Slider title. Default. Empty.
 *
 * }
 * @return string Slider HTML.
 */
function geodir_sc_listing_slider($atts)
{
    ob_start();
    $defaults = array(
        'post_type' => 'gd_place',
        'category' => '0',
        'post_number' => '5',
        'slideshow' => '0',
        'animation_loop' => 0,
        'direction_nav' => 0,
        'slideshow_speed' => 5000,
        'animation_speed' => 600,
        'animation' => 'slide',
        'order_by' => 'latest',
        'show_title' => '',
        'show_featured_only' => '',
        'title' => '',
    );

    $params = shortcode_atts($defaults, $atts);


    /*
     *
     * Now we begin the validation of the attributes.
     */
    // Check we have a valid post_type
    if (!(gdsc_is_post_type_valid($params['post_type']))) {
        $params['post_type'] = 'gd_place';
    }

    // Check we have a valid sort_order
    $params['order_by'] = gdsc_validate_sort_choice($params['order_by']);

    // Match the chosen animation to our options
    $animation_list = array('slide', 'fade');
    if (!(in_array($params['animation'], $animation_list))) {
        $params['animation'] = 'slide';
    }

    // Post_number needs to be a positive integer
    $params['post_number'] = absint($params['post_number']);
    if (0 == $params['post_number']) {
        $params['post_number'] = 1;
    }

    // Manage the entered categories
    if (0 != $params['category'] || '' != $params['category']) {
        $params['category'] = gdsc_manage_category_choice($params['post_type'], $params['category']);
    }
    // Convert show_title to a bool
    $params['show_title'] = intval(gdsc_to_bool_val($params['show_title']));

    // Convert show_featured_only to a bool
    $params['show_featured_only'] = intval(gdsc_to_bool_val($params['show_featured_only']));

    /*
     * Hopefully all attributes are now valid, and safe to pass forward
     */

    // redeclare vars after validation

    if (isset($params['direction_nav'])) {
        $params['directionNav'] = $params['direction_nav'];
    }
    if (isset($params['animation_loop'])) {
        $params['animationLoop'] = $params['animation_loop'];
    }
    if (isset($params['slideshow_speed'])) {
        $params['slideshowSpeed'] = $params['slideshow_speed'];
    }
    if (isset($params['animation_speed'])) {
        $params['animationSpeed'] = $params['animation_speed'];
    }
    if (isset($params['order_by'])) {
        $params['list_sort'] = $params['order_by'];
    }

    $query_args = array(
        'post_number' => $params['post_number'],
        'is_geodir_loop' => true,
        'post_type' => $params['post_type'],
        'order_by' => $params['order_by']
    );

    if (1 == $params['show_featured_only']) {
        $query_args['show_featured_only'] = 1;
    }

    if (0 != $params['category'] && '' != $params['category']) {
        $category_taxonomy = geodir_get_taxonomies($params['post_type']);
        $tax_query = array(
            'taxonomy' => $category_taxonomy[0],
            'field' => 'id',
            'terms' => $params['category'],
        );

        $query_args['tax_query'] = array($tax_query);
    }

    $defaults = array(
        'before_widget' => '',
        'after_widget' => '',
        'before_title' => '',
        'after_title' => '',
    );

    $query_args = array_merge($query_args, $params);

    geodir_listing_slider_widget_output($defaults, $query_args);

    $output = ob_get_contents();

    ob_end_clean();

    return $output;
}

/**
 * The geodirectory login box shortcode.
 *
 * This implements the functionality of the shortcode for displaying login box.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @param array $atts {
 *     Attributes of the shortcode.
 *
 *     @type string $before_widget HTML content to prepend to each widget's HTML output. Default. Empty.
 *     @type string $after_widget  HTML content to append to each widget's HTML output. Default. Empty.
 *     @type string $before_title  HTML content to prepend to the title when displayed. Default. Empty.
 *     @type string $after_title   HTML content to append to the title when displayed. Default. Empty.
 *
 * }
 * @return string Login box HTML.
 */
function geodir_sc_login_box($atts)
{
    ob_start();

    $defaults = array(
        'before_widget' => '',
        'after_widget' => '',
        'before_title' => '',
        'after_title' => '',
    );

    geodir_loginwidget_output($defaults, $defaults);

    $output = ob_get_contents();

    ob_end_clean();

    return $output;
}

/**
 * The geodirectory popular post category shortcode.
 *
 * This implements the functionality of the shortcode for displaying popular post category.
 *
 * @since 1.0.0
 * @since 1.5.1 Added default_post_type parameter.
 * @since 1.6.9 Added parent_only parameter.
 * @package GeoDirectory
 * @global string $geodir_post_category_str The geodirectory post category.
 * @param array $atts {
 *     Attributes of the shortcode.
 *
 *     @type string $before_widget      HTML content to prepend to each widget's HTML output. Default. Empty.
 *     @type string $after_widget       HTML content to append to each widget's HTML output. Default. Empty.
 *     @type string $before_title       HTML content to prepend to the title when displayed. Default. Empty.
 *     @type string $after_title        HTML content to append to the title when displayed. Default. Empty.
 *     @type int $category_limit        Number of categories to display. Default. 15.
 *     @type string $title              Widget title. Default. Empty.
 *     @type string $default_post_type  Default post type. Default. Empty.
 *     @type bool   $parent_only        True to show parent categories only. Default False.
 *
 * }
 * @return string Popular post category HTML.
 */
function geodir_sc_popular_post_category($atts)
{
    ob_start();
    global $geodir_post_category_str;
    $defaults = array(
        'category_limit' => 15,
        'category_restrict' => false,
        'before_widget' => '',
        'after_widget' => '',
        'before_title' => '',
        'after_title' => '',
        'title' => '',
        'default_post_type' => '',
        'parent_only' => false,
    );

    $params = shortcode_atts($defaults, $atts, 'popular_post_category');
    $params['category_limit'] = absint($params['category_limit']);
    $params['default_post_type'] = gdsc_is_post_type_valid($params['default_post_type']) ? $params['default_post_type'] : '';
    $params['parent_only'] = gdsc_to_bool_val($params['parent_only']);
    geodir_popular_post_category_output($params, $params);

    $output = ob_get_contents();

    ob_end_clean();

    return $output;
}

/**
 * The geodirectory popular post view shortcode.
 *
 * This implements the functionality of the shortcode for displaying popular post view.
 *
 * @since 1.0.0
 * @since 1.6.18 [gd_popular_post_view] shortcode character_count=0 not working - FIXED
 * @since 1.6.22 $hide_if_empty parameter added.
 * @package GeoDirectory
 * @param array $atts {
 *     Attributes of the shortcode.
 *
 *     @type string $add_location_filter    Filter listings using current location. Default 0.
 *     @type string $before_widget          HTML content to prepend to each widget's HTML output. Default. Empty.
 *     @type string $after_widget           HTML content to append to each widget's HTML output. Default. Empty.
 *     @type string $before_title           HTML content to prepend to the title when displayed. Default. <h3 class="widget-title">.
 *     @type string $after_title            HTML content to append to the title when displayed. Default. </h3>.
 *     @type string $category               Category ids to filter listings. Ex: 1,3. Default. 0.
 *     @type string $category_title         Category title. Default. Empty.
 *     @type string $character_count        The excerpt length. Default. 20.
 *     @type string $layout                 Layout to display listing. Should be gridview_onehalf, gridview_onethird,
 *                                          gridview_onefourth, gridview_onefifth, list. Default 'gridview_onehalf'. Default. gridview_onehalf.
 *     @type string $list_sort              Sort by. Default. latest.
 *     @type string $listing_width          Width of the listing in %. Default. Empty.
 *     @type string $post_number            No. of post to display. Default. 5.
 *     @type string $post_type              Post type of listing. Default. gd_place.
 *     @type string $show_featured_only     Display only featured listings. Default. 0.
 *     @type string $show_special_only      Display only special offers listings. Default. 0.
 *     @type string $title                  Widget title. Default. Empty.
 *     @type string $use_viewing_post_type  Filter using viewing post type. Default. 1.
 *     @type string $with_pics_only         Only display listings which has image available. Default empty. Default. 0.
 *     @type string $with_videos_only       Only display listings which has video available. Default. 0.
 *     @type string $hide_if_empty          Hide widget if no listings found. Default. 0.
 *
 * }
 * @return string Popular post view HTML.
 */
function geodir_sc_popular_post_view($atts)
{
    ob_start();
    $defaults = array(
        'post_type' => 'gd_place',
        'category' => '0',
        'post_number' => '5',
        'layout' => 'gridview_onehalf',
        'add_location_filter' => '0',
        'list_sort' => 'latest',
        'use_viewing_post_type' => '1',
        'character_count' => '20',
        'listing_width' => '',
        'show_featured_only' => '0',
        'show_special_only' => '0',
        'with_pics_only' => '0',
        'with_videos_only' => '0',
        'hide_if_empty' => '0',
        'before_widget' => '',
        'after_widget' => '',
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>',
        'title' => '',
        'category_title' => '',
    );

    $params = shortcode_atts($defaults, $atts);

    /**
     * Validate our incoming params
     */

    // Validate the selected post type, default to gd_place on fail
    if (!(gdsc_is_post_type_valid($params['post_type']))) {
        $params['post_type'] = 'gd_place';
    }

    // Validate the selected category/ies - Grab the current list based on post_type
    $category_taxonomy = geodir_get_taxonomies($params['post_type']);
    $categories = get_terms($category_taxonomy, array('orderby' => 'count', 'order' => 'DESC', 'fields' => 'ids'));

    // Make sure we have an array
    if (!(is_array($params['category']))) {
        $params['category'] = explode(',', $params['category']);
    }

    // Array_intersect returns only the items in $params['category'] that are also in our category list
    // Otherwise it becomes empty and later on that will mean "All"
    $params['category'] = array_intersect($params['category'], $categories);

    // Post_number needs to be a positive integer
    $params['post_number'] = absint($params['post_number']);
    if (0 == $params['post_number']) {
        $params['post_number'] = 1;
    }

    // Validate our layout choice
    // Outside of the norm, I added some more simple terms to match the existing
    // So now I just run the switch to set it properly.
    $params['layout'] = gdsc_validate_layout_choice($params['layout']);

    // Validate our sorting choice
    $params['list_sort'] = gdsc_validate_sort_choice($params['list_sort'], $params['post_type']);

    // Validate character_count
    if ($params['character_count'] !== '') {
        $params['character_count'] = absint($params['character_count']);
    }

    // Validate Listing width, used in the template widget-listing-listview.php
    // The context is in width=$listing_width% - So we need a positive number between 0 & 100
    $params['listing_width'] = gdsc_validate_listing_width($params['listing_width']);

    // Validate the checkboxes used on the widget
    $params['add_location_filter'] = gdsc_to_bool_val($params['add_location_filter']);
    $params['show_featured_only'] = gdsc_to_bool_val($params['show_featured_only']);
    $params['show_special_only'] = gdsc_to_bool_val($params['show_special_only']);
    $params['with_pics_only'] = gdsc_to_bool_val($params['with_pics_only']);
    $params['with_videos_only'] = gdsc_to_bool_val($params['with_videos_only']);
    $params['use_viewing_post_type'] = gdsc_to_bool_val($params['use_viewing_post_type']);
    $params['hide_if_empty'] = gdsc_to_bool_val($params['hide_if_empty']);

    /**
     * End of validation
     */

    geodir_popular_postview_output($params, $params);


    $output = ob_get_contents();

    ob_end_clean();

    return $output;
}

/**
 * The geodirectory recent reviews shortcode.
 *
 * This implements the functionality of the shortcode for displaying recent reviews.
 *
 * @since 1.0.0
 * @since 1.5.0 New title attribute added.
 * @package GeoDirectory
 * @param array $atts {
 *     Attributes of the shortcode.
 *
 *     @type string $title The title displayed above recent reviews. Default empty.
 *     @type int $count Number of reviews you want to display. Default. 5.
 *
 * }
 * @return string Recent reviews HTML.
 */
function geodir_sc_recent_reviews($atts) {
    ob_start();
    $defaults = array(
		'title' => '',
		'count' => 5,
    );

    $params = shortcode_atts($defaults, $atts);

    $count = absint($params['count']);
    if (0 == $count) {
        $count = 1;
    }
	
	$title = !empty($params['title']) ? __($params['title'], 'geodirectory') : '';

    $comments_li = geodir_get_recent_reviews(30, $count, 100, false);

    if ($comments_li) {
        if ($title != '') { ?>
		<h3 class="geodir-sc-recent-reviews-title widget-title"><?php echo $title; ?></h3>
		<?php } ?>
        <div class="geodir_sc_recent_reviews_section">
            <ul class="geodir_sc_recent_reviews"><?php echo $comments_li; ?></ul>
        </div>
    <?php
    }
    $output = ob_get_contents();

    ob_end_clean();

    return $output;
}

/**
 * The geodirectory related listing widget shortcode.
 *
 * This implements the functionality of the shortcode for displaying related listing widget.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @param array $atts {
 *     Attributes of the shortcode.
 *
 *     @type int $add_location_filter   Filter listings using current location. Default 0.
 *     @type string $before_title       HTML content to prepend to the title when displayed. Default. <style type="text/css">.geodir_category_list_view li{margin:0px!important}</style>.
 *     @type int $character_count       The excerpt length Default. 20.
 *     @type int $is_widget             Is this a widget? Default. 1.
 *     @type string $layout             Layout to display listing. Should be gridview_onehalf, gridview_onethird,
 *                                      gridview_onefourth, gridview_onefifth, list. Default 'gridview_onehalf'. Default. gridview_onehalf.
 *     @type string $list_sort          Sort by. Default. latest.
 *     @type string $listing_width      Width of the listing in %. Default. Empty.
 *     @type int $post_number           No. of post to display. Default. 5.
 *     @type string $relate_to          Type to use for making relation. Can be tags or category. Default. category.
 *
 * }
 * @return string Related widget HTML.
 */
function geodir_sc_related_listings($atts)
{
    ob_start();
    $defaults = array(
        'post_number' => 5,
        'relate_to' => 'category',
        'layout' => 'gridview_onehalf',
        'add_location_filter' => 0,
        'listing_width' => '',
        'list_sort' => 'latest',
        'character_count' => 20,
        'is_widget' => 1,
        'before_title' => '<style type="text/css">.geodir_category_list_view li{margin:0px!important}</style>',
    );
    // The "before_title" code is an ugly & terrible hack. But it works for now. I should enqueue a new stylesheet.

    $params = shortcode_atts($defaults, $atts);

    /**
     * Begin validating parameters
     */

    // Validate that post_number is a number and is 1 or higher
    $params['post_number'] = absint($params['post_number']);
    if (0 === $params['post_number']) {
        $params['post_number'] = 1;
    }

    // Validate relate_to - only category or tags
    $params['relate_to'] = geodir_strtolower($params['relate_to']);
    if ('category' != $params['relate_to'] && 'tags' != $params['relate_to']) {
        $params['relate_to'] = 'category';
    }

    // Validate layout selection
    $params['layout'] = gdsc_validate_layout_choice($params['layout']);

    // Validate sorting option
    $params['list_sort'] = gdsc_validate_sort_choice($params['list_sort']);

    // Validate add_location_filter
    $params['add_location_filter'] = gdsc_to_bool_val($params['add_location_filter']);

    // Validate listing_width
    $params['listing_width'] = gdsc_validate_listing_width($params['listing_width']);

    // Validate character_count
    if ($params['character_count'] !== '') {
        $params['character_count'] = absint($params['character_count']);
    }

    if ($related_display = geodir_related_posts_display($params)) {
        echo $related_display;
    }
    $output = ob_get_contents();

    ob_end_clean();

    return $output;
}

/**
 * The geodirectory advanced search widget shortcode.
 *
 * This implements the functionality of the shortcode for displaying advanced search widget.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @param array $atts {
 *     Attributes of the shortcode.
 *
 *     @type string $before_widget   HTML content to prepend to each widget's HTML output. Default. <section id="geodir_advanced_search-1" class="widget geodir-widget geodir_advanced_search_widget">.
 *     @type string $after_widget    HTML content to append to each widget's HTML output. Default. </section>.
 *     @type string $before_title    HTML content to prepend to the title when displayed. Default. <h3 class="widget-title">.
 *     @type string $after_title     HTML content to append to the title when displayed. Default. </h3>.
 *     @type string $show_adv_search Show advanced search? Default. default.
 *     @type string $title           Widget title. Default. Empty.
 *
 * }
 * @return string Advanced search widget HTML.
 */
function geodir_sc_advanced_search($atts) {
    $defaults = array(
		'title' => '',
		'before_widget' => '<section id="geodir_advanced_search-1" class="widget geodir-widget geodir_advance_search_widget">',
        'after_widget' => '</section>',
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>',
        'show_adv_search' => 'default',
		'post_type' => ''
	);
	
	$params = shortcode_atts($defaults, $atts);
	
	$show_adv_search = isset($params['show_adv_search']) && in_array($params['show_adv_search'], array('default', 'always', 'searched')) ? $params['show_adv_search'] : '';
	
	if ($show_adv_search != '' ) {
		$show_adv_class = 'geodir-advance-search-' . $show_adv_search . ' ';
		if ($show_adv_search == 'searched' && geodir_is_page('search')) {
			$show_adv_search = 'search';
		}
		$show_adv_attrs = 'data-show-adv="' . $show_adv_search . '"';
		
		$params['before_widget'] = str_replace('class="', $show_adv_attrs . ' class="' . $show_adv_class, $params['before_widget']);
	}
	
	ob_start();
	
	//geodir_get_template_part('listing', 'filter-form');
	the_widget('geodir_advance_search_widget', $params, $params );
	
	$output = ob_get_contents();
    ob_end_clean();

    return $output;
}

/**
 * The best of widget shortcode.
 *
 * This implements the functionality of the best of widget shortcode for displaying
 * top rated listing.
 *
 * @since 1.4.2
 *
 * @param array $atts {
 *     Attributes of the shortcode.
 *
 *     @type string $title         The title of the widget displayed.
 *     @type string $post_type     Post type of listing. Default gd_place.
 *     @type int    $post_limit    No. of post to display. Default 5.
 *     @type int    $categ_limit   No. of categories to display. Default 3.
 *     @type int    $character_count       The excerpt length
 *     @type int    $use_viewing_post_type Filter viewing post type. Default 1.
 *     @type int    $add_location_filter   Filter current location. Default 1.
 *     @type string $tab_layout    Tab layout to display listing. Default 'bestof-tabs-on-top'.
 *     @type string $before_widget HTML content to prepend to each widget's HTML output.
 *                                 Default is an opening list item element.
 *     @type string $after_widget  HTML content to append to each widget's HTML output.
 *                                 Default is a closing list item element.
 *     @type string $before_title  HTML content to prepend to the widget title when displayed.
 *                                 Default is an opening h3 element.
 *     @type string $after_title   HTML content to append to the widget title when displayed.
 *                                 Default is a closing h3 element.
 * }
 * @param string $content The enclosed content. Optional.
 * @return string HTML content to display best of listings.
 */
function geodir_sc_bestof_widget($atts, $content = '') {
	$defaults = array(
		'title' => '',
		'post_type' => 'gd_place',
		'post_limit' => 5,
		'categ_limit' => 3,
		'character_count' => 20,
		'use_viewing_post_type' => '1',
		'add_location_filter' => '1',
		'tab_layout' => 'bestof-tabs-on-top',
		'before_widget' => '<section id="bestof_widget-1" class="widget geodir-widget geodir_bestof_widget geodir_sc_bestof_widget">',
        'after_widget' => '</section>',
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>',
	);
	$params = shortcode_atts($defaults, $atts);

    /**
     * Validate our incoming params
     */

    // Validate the selected post type, default to gd_place on fail
    if (!(gdsc_is_post_type_valid($params['post_type']))) {
        $params['post_type'] = 'gd_place';
    }
	
	// Post limit needs to be a positive integer
    $params['post_limit'] = absint($params['post_limit']);
    if (0 == $params['post_limit']) {
        $params['post_limit'] = 5;
    }
	
	// Category limit needs to be a positive integer
    $params['categ_limit'] = absint($params['categ_limit']);
    if (0 == $params['categ_limit']) {
        $params['categ_limit'] = 3;
    }
	
	// Tab layout validation
    $params['tab_layout'] = $params['tab_layout'];
    if (!in_array($params['tab_layout'], array('bestof-tabs-on-top', 'bestof-tabs-on-left', 'bestof-tabs-as-dropdown'))) {
        $params['tab_layout'] = 'bestof-tabs-on-top';
    }
	
	// Validate character_count
    $params['character_count'] = $params['character_count'];

	ob_start();
	the_widget('geodir_bestof_widget', $params, $params);
    $output = ob_get_contents();
    ob_end_clean();

    return $output;
}

/**
 * The geodirectory listings shortcode.
 *
 * This implements the functionality of the shortcode for displaying geodirectory listings.
 *
 * @since 1.4.2
 * @since 1.5.9 New parameter "post_author" added.
 * @since 1.6.5 tags parameter added.
 * @since 1.6.18 New attributes added in gd_listings shortcode to filter user favorite listings.
 *               In [gd_listings] shortcode if category has no posts then it shows all the results - FIXED
 *
 * @global object $post The current post object.
 *
 * @param array $atts {
 *     Attributes of the shortcode.
 *
 *     @type string $title         The title to be displayed above listings.
 *     @type string $post_type     Post type of listing. Default gd_place.
 *     @type string $category      Category ids to filter listings. Ex: 1,3. Default empty.
 *     @type string $list_sort     Sorting for listings. Should be from az, latest, featured, 
                                   high_review, high_rating.Default 'latest'.
 *     @type string $event_type    Event type filter. Should today, upcoming, past, all. Default empty.
                                   For post type gd_event only. 
 *     @type int    $post_number   No. of post to display. Default 10.
 *     @type int|string $post_author       Filter the posts by author. Either author ID or 'current'(it uses 
                                           the author ID of the current post. Default empty.
 *     @type string $layout        Layout to display listing. Should be gridview_onehalf, gridview_onethird
                                   gridview_onefourth, gridview_onefifth, list. Default 'gridview_onehalf'.
 *     @type string $listing_width Listing width. Optional
 *     @type int      $character_count     The excerpt length of content
 *     @type int|bool $add_location_filter Filter listings using current location. Default 1.
 *     @type int|bool $show_featured_only  Display only featured listings. Default empty.
 *     @type int|bool $show_special_only   Display only special offers listings. Default empty.
 *     @type int|bool $with_pics_only      Display listings which has image available. Default empty.
 *     @type int|bool $with_videos_only    Display listings which has video available. Default empty.
 *     @type int|bool $with_pagination     Display pagination for listings. Default 1.
 *     @type int|bool $top_pagination      Display pagination on top of listings. Default 0.
                                           Required $with_pagination true.
 *     @type int|bool $bottom_pagination   Display pagination on bottom of listings. Default 1.
                                           Required $with_pagination true.
       @type string $tags                  Post tags. Ex: "Tag1,TagB" Optional.
 *     @type int|bool $show_favorites_only    Display listings which are favorited by user. Default empty.
 *     @type int|string $favorites_by_user    Filter the posts favorites by user. Should be user ID or 'current' or empty. Default empty.
                                   ('current' uses the author Id of current viewing post, If empty then uses the current logged user ID).
 * }
 * @param string $content The enclosed content. Optional.
 * @return string HTML content to display geodirectory listings.
 */
function geodir_sc_gd_listings($atts, $content = '') {
    global $post;
    $defaults = array(
        'title'                 => '',
        'post_type'             => 'gd_place',
        'category'              => 0,
        'list_sort'             => 'latest',
        'event_type'            => '',
        'post_number'           => 10,
        'post_author'           => '',
        'layout'                => 'gridview_onehalf',
        'listing_width'         => '',
        'character_count'       => 20,
        'add_location_filter'   => 1,
        'show_featured_only'    => '',
        'show_special_only'     => '',
        'with_pics_only'        => '',
        'with_videos_only'      => '',
        'with_pagination'       => '1',
        'top_pagination'        => '0',
        'bottom_pagination'     => '1',
        'without_no_results'    => 0,
        'tags'                  => '',
        'show_favorites_only'   => '',
        'favorites_by_user'     => '',
    );
    $params = shortcode_atts($defaults, $atts);

    $params['title']        = wp_strip_all_tags($params['title']);
    $params['post_type']    = gdsc_is_post_type_valid($params['post_type']) ? $params['post_type'] : 'gd_place';

    // Validate the selected category/ies - Grab the current list based on post_type
    $category_taxonomy      = geodir_get_taxonomies($params['post_type']);
    $categories             = get_terms($category_taxonomy, array('orderby' => 'count', 'order' => 'DESC', 'fields' => 'ids', 'hide_empty' => 0));

    // Make sure we have an array
    if (!(is_array($params['category']))) {
        $params['category'] = explode(',', $params['category']);
    }

    // Array_intersect returns only the items in $params['category'] that are also in our category list
    // Otherwise it becomes empty and later on that will mean "All"
    $params['category']     = array_intersect($params['category'], $categories);

    // Post_number needs to be a positive integer
    $params['post_number']  = absint($params['post_number']);
    $params['post_number']  = $params['post_number'] > 0 ? $params['post_number'] : 10;
    
    // Post_number needs to be a positive integer
    if (!empty($atts['post_author'])) {

        // 'current' left for backwards compatibility
        if ( ($atts['post_author'] == 'current' || $atts['post_author'] == 'current_author') && !empty($post) && isset($post->post_author) && $post->post_type != 'page') {
            $params['post_author'] = $post->post_author;
        } else if ($atts['post_author'] == 'current_user' ) {
            if($uid = get_current_user_id()){
                $params['post_author'] = absint($uid);
            }else{
                $params['post_author'] = -1;// if not logged in then don't show any listings.
            }

        } else if ($atts['post_author'] != 'current' && absint($atts['post_author']) > 0) {
            $params['post_author'] = absint($atts['post_author']);
        } else {
            unset($params['post_author']);
        }
    } else {
        unset($params['post_author']);
    }

    // Validate character_count
    //todo: is this necessary?
    $params['character_count']  = $params['character_count'];

    // Validate our layout choice
    // Outside of the norm, I added some more simple terms to match the existing
    // So now I just run the switch to set it properly.
    $params['layout']           = gdsc_validate_layout_choice($params['layout']);

    // Validate our sorting choice
    $params['list_sort']        = gdsc_validate_sort_choice($params['list_sort'], $params['post_type']);

    // Validate Listing width, used in the template widget-listing-listview.php
    // The context is in width=$listing_width% - So we need a positive number between 0 & 100
    $params['listing_width']    = gdsc_validate_listing_width($params['listing_width']);

    // Validate the checkboxes used on the widget
    $params['add_location_filter']  = gdsc_to_bool_val($params['add_location_filter']);
    $params['show_featured_only']   = gdsc_to_bool_val($params['show_featured_only']);
    $params['show_special_only']    = gdsc_to_bool_val($params['show_special_only']);
    $params['with_pics_only']       = gdsc_to_bool_val($params['with_pics_only']);
    $params['with_videos_only']     = gdsc_to_bool_val($params['with_videos_only']);
    $params['with_pagination']      = gdsc_to_bool_val($params['with_pagination']);
    $params['top_pagination']       = gdsc_to_bool_val($params['top_pagination']);
    $params['bottom_pagination']    = gdsc_to_bool_val($params['bottom_pagination']);
    
    // User favorites
    $params['show_favorites_only']  = gdsc_to_bool_val($params['show_favorites_only']);
    if (!empty($params['show_favorites_only'])) {
        if ($params['favorites_by_user'] == 'current' && !empty($post) && isset($post->post_author) && $post->post_type != 'page') {
            $params['favorites_by_user'] = $post->post_author;
        } else if ($params['favorites_by_user'] != 'current' && absint($params['favorites_by_user']) > 0) {
            $params['favorites_by_user'] = absint($atts['favorites_by_user']);
        } else if ($params['favorites_by_user'] != 'current' && $current_user_id = get_current_user_id()) {
            $params['favorites_by_user'] = $current_user_id;
        } else {
            $params['favorites_by_user'] = 0;
        }
    }

    // Clean tags
    if (!empty($params['tags'])) {
        if (!is_array($params['tags'])) {
            $comma = _x(',', 'tag delimiter');
            if ( ',' !== $comma ) {
                $params['tags'] = str_replace($comma, ',', $params['tags']);
            }
            $params['tags'] = explode(',', trim($params['tags'], " \n\t\r\0\x0B,"));
            $params['tags'] = array_map('trim', $params['tags']);
        }
    } else {
        $params['tags'] = array();
    }

    /**
     * End of validation
     */
    if (isset($atts['geodir_ajax'])) {
        $params['geodir_ajax'] = $atts['geodir_ajax'];
        unset($atts['geodir_ajax']);
    }
    if (isset($atts['pageno'])) {
        $params['pageno'] = $atts['pageno'];
        unset($atts['pageno']);
    }

    if ( !empty($atts['shortcode_content']) ) {
        $content = $atts['shortcode_content'];
    }
    $params['shortcode_content'] = trim($content);
    $atts['shortcode_content'] = trim($content);
    
    $params['shortcode_atts']       = $atts;

    $output = geodir_sc_gd_listings_output($params);

    return $output;
}

/**
 * The CPT categories widget shortcode.
 *
 * This implements the functionality of the CPT categories widget shortcode for displaying
 * all geodirectory categories.
 *
 * @since 1.5.5
 * @since 1.6.6 New parameters $no_cpt_filter &no_cat_filter added.
 *
 * @param array $atts {
 *     Attributes of the shortcode.
 *
 *     @type string $title         The title of the widget displayed.
 *     @type string $post_type     Post type of listing. Default empty.
 *     @type bool   $hide_empty    Hide empty categories? Default empty.
 *     @type bool   $show_count    Show category count? Default empty.
 *     @type bool   $hide_icon     Hide category icon? Default empty.
 *     @type bool   $cpt_left      Show CPT on same line? Default empty.
 *     @type string $sort_by       Categories sort by. 'az' or 'count'. Default 'count'.
 *     @type string|int $max_count Max no of sub-categories count. Default 'all'.
 *     @type string|int $max_level Max level of sub-categories depth. Default 1.
 *     @type bool   $no_cpt_filter Disable filter current viewing post type. Default empty.
 *     @type bool   $no_cat_filter Disable filter current viewing category. Default empty.
 *     @type string $before_widget HTML content to prepend to each widget's HTML output.
 *                                 Default is an opening list item element.
 *     @type string $after_widget  HTML content to append to each widget's HTML output.
 *                                 Default is a closing list item element.
 *     @type string $before_title  HTML content to prepend to the widget title when displayed.
 *                                 Default is an opening h3 element.
 *     @type string $after_title   HTML content to append to the widget title when displayed.
 *                                 Default is a closing h3 element.
 * }
 * @param string $content The enclosed content. Optional.
 * @return string HTML content to display CPT categories.
 */
function geodir_sc_cpt_categories_widget($atts, $content = '') {
    $defaults = array(
        'title' => '',
        'post_type' => '', // NULL for all
        'hide_empty' => '',
        'show_count' => '',
        'hide_icon' => '',
        'cpt_left' => '',
        'sort_by' => 'count',
        'max_count' => 'all',
        'max_level' => '1',
        'no_cpt_filter' => '',
        'no_cat_filter' => '',
        'before_widget' => '<section id="geodir_cpt_categories_widget-1" class="widget geodir-widget geodir_cpt_categories_widget geodir_sc_cpt_categories_widget">',
        'after_widget' => '</section>',
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>',
    );
    $params = shortcode_atts($defaults, $atts);

    /**
     * Validate our incoming params
     */
    // Make sure we have an array
    $params['post_type'] = !is_array($params['post_type']) && trim($params['post_type']) != '' ? explode(',', trim($params['post_type'])) : array();
     
    // Validate the checkboxes used on the widget
    $params['hide_empty'] 	= gdsc_to_bool_val($params['hide_empty']);
    $params['show_count'] 	= gdsc_to_bool_val($params['show_count']);
    $params['hide_icon'] 	= gdsc_to_bool_val($params['hide_icon']);
    $params['cpt_left'] 	= gdsc_to_bool_val($params['cpt_left']);

    if ($params['max_count'] != 'all') {
        $params['max_count'] = absint($params['max_count']);
    }

    if ($params['max_level'] != 'all') {
        $params['max_level'] = absint($params['max_level']);
    }

    $params['no_cpt_filter'] = gdsc_to_bool_val($params['no_cpt_filter']);
    $params['no_cat_filter'] = gdsc_to_bool_val($params['no_cat_filter']);

    $params['sort_by'] = $params['sort_by'] == 'az' ? 'az' : 'count';

    ob_start();
    the_widget('geodir_cpt_categories_widget', $params, $params);
    $output = ob_get_contents();
    ob_end_clean();

    return $output;
}

/**
 * Responsive videos shortcode.
 *
 * Responsive videos requires a wrapper. This shortcode adds a wrapper for the iframe code
 *
 * @since 1.6.6
 * @param array $atts Not being used.
 * @param string $content The iframe video code. Required.
 * @return string HTML code.
 */
function geodir_sc_responsive_videos($atts, $content) {
    return '<div class="geodir-video-wrapper">'.$content.'</div>';
}

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Validate and parse the measurement value.
 *
 * @since 1.0.0
 *
 * @param string $value Input value to validate measurement.
 * @return string The measurement value in valid format.
 */
function gdsc_validate_measurements($value)
{
    if ((strlen($value) - 1) == strpos(trim($value), '%')) {
        // $value is entered as a percent, so it can't be less than 0 or more than 100
        $value = preg_replace('/\D/', '', $value);
        if (100 < $value) {
            $value = 100;
        }
        // Re-add the percent symbol
        $value = $value . '%';
    } elseif ((strlen($value) - 2) == strpos(trim($value), 'px')) {
        // Get the absint & re-add the 'px'
        $value = preg_replace('/\D/', '', $value) . 'px';
    } else {
        $value = preg_replace('/\D/', '', $value);
    }

    return $value;
}

/**
 * Validate and parse the google map parameters.
 *
 * @since 1.0.0
 * @since 1.5.2 Added TERRAIN map type.
 *
 * @param string $value Input value to validate measurement.
 * @return string The measurement value in valid format.
 */
function gdsc_validate_map_args($params)
{

    $params['width'] = gdsc_validate_measurements($params['width']);
    $params['height'] = gdsc_validate_measurements($params['height']);

    // Only accept our 4 maptypes. Otherwise, revert to the default.
    if (!(in_array(geodir_strtoupper($params['maptype']), array('HYBRID', 'SATELLITE', 'ROADMAP', 'TERRAIN')))) {
        $params['maptype'] = 'ROADMAP';
    } else {
        $params['maptype'] = geodir_strtoupper($params['maptype']);
    }

    // Zoom accepts a value between 1 and 19
    $params['zoom'] = absint($params['zoom']);
    if (19 < $params['zoom']) {
        $params['zoom'] = '19';
    }
    if (0 == $params['zoom']) {
        $params['zoom'] = '1';
    }

    // Child_collapse must be boolean
    $params['child_collapse'] = gdsc_to_bool_val($params['child_collapse']);

    // Scrollwheel must be boolean
    $params['scrollwheel'] = gdsc_to_bool_val($params['scrollwheel']);

    // Scrollwheel must be boolean
    $params['autozoom'] = gdsc_to_bool_val($params['autozoom']);

    return $params;
}

/**
 * Check the boolean true or false.
 *
 * Checks a variable to see if it should be considered a boolean true or false.
 *     Also takes into account some text-based representations of true of false,
 *     such as 'false','N','yes','on','off', etc.
 *
 * @since 1.0.0
 *
 * @param mixed $in The variable to check
 * @param bool $strict If set to false, consider everything that is not false to be true.
 *
 * @return bool The boolean equivalent or null
 */
function gdsc_to_bool_val($in, $strict = false)
{
    $out = null;

    // if not strict, we only have to check if something is false
    if (in_array($in, array(
        'false',
        'False',
        'FALSE',
        'no',
        'No',
        'n',
        'N',
        '0',
        'off',
        'Off',
        'OFF',
        false,
        0,
        null
    ), true)) {
        $out = false;
    } else if ($strict) {
        // if strict, check the equivalent true values
        if (in_array($in, array(
            'true',
            'True',
            'TRUE',
            'yes',
            'Yes',
            'y',
            'Y',
            '1',
            'on',
            'On',
            'ON',
            true,
            1
        ), true)) {
            $out = true;
        }
    } else {
        // not strict? let the regular php bool check figure it out (will
        //     largely default to true)
        $out = ($in ? true : false);
    }

    return $out;
}

/**
 * Check the post type valid or not.
 *
 * @since 1.0.0
 *
 * @param string $incoming_post_type Post type.
 * @return bool The boolean equivalent or null.
 */
function gdsc_is_post_type_valid($incoming_post_type)
{
    $post_types = geodir_get_posttypes();
    $post_types = array_map('geodir_strtolower', $post_types);
    $post_type_found = false;
    foreach ($post_types as $type) {
        if (geodir_strtolower($incoming_post_type) == geodir_strtolower($type)) {
            $post_type_found = true;
        }
    }

    return $post_type_found;
}

/**
 * Adds the filters and gets the query.
 *
 * @since 1.0.0
 *
 * @global object $wp_query WordPress Query object.
 * @todo $wp_query declared twice - fix it.
 * @global string $geodir_post_type Post type.
 * @global string $table Listing table name.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 * @global string $term Term object.
 *
 * @param string $query Database query.
 * @return string Query.
 */
function gdsc_listing_loop_filter($query)
{
    global $wp_query, $geodir_post_type, $table, $plugin_prefix, $term;

    $geodir_post_type = geodir_get_current_posttype();

    if (isset($wp_query->tax_query->queries) && $wp_query->tax_query->queries) {
        $taxonomies = wp_list_pluck($wp_query->tax_query->queries, 'taxonomy');

        if (isset($wp_query->query[$taxonomies[0]])) {
            $request_term = explode("/", $wp_query->query[$taxonomies[0]]);
            $request_term = end($request_term);
            if (!term_exists($request_term)) {
                $args = array('number' => '1',);
                $terms_arr = get_terms($taxonomies[0], $args);
                foreach ($terms_arr as $location_term) {
                    $term_arr = $location_term;
                    $term_arr->name = geodir_ucwords(str_replace('-', ' ', $request_term));
                }
                $wp_query->queried_object_id = 1;
                $wp_query->queried_object = $term_arr;
            }
        }

    }
    if (isset($query->query_vars['is_geodir_loop']) && $query->query_vars['is_geodir_loop']) {

        $table = $plugin_prefix . $geodir_post_type . '_detail';

        add_filter('posts_fields', 'geodir_posts_fields', 1);
        add_filter('posts_join', 'geodir_posts_join', 1);
        geodir_post_where();
        if (!is_admin()) {
            add_filter('posts_orderby', 'geodir_posts_orderby', 1);
        }

        // advanced filter for popular post view widget
        global $wp_query;
        if (!is_admin()) {
            if (!empty($wp_query->query['with_pics_only'])) {
                add_filter('posts_join', 'geodir_filter_widget_join', 1000);
            }
            add_filter('posts_where', 'geodir_filter_widget_where', 1000);
        }

    }

    return $query;
}

/**
 * Get the category id from category name/slug.
 *
 * @since 1.0.0
 *
 * @param string $post_type Post type.
 * @param string $category Post category.
 * @return int Term id.
 */
function gdsc_manage_category_choice($post_type, $category)
{
    if (0 == $category || '' == $category) {
        return '';
    }

    if (!(gdsc_is_post_type_valid($post_type))) {
        return '';
    }

    $taxonomies = geodir_get_taxonomies($post_type);

    $categories = get_terms(array('taxonomy' => $taxonomies[0]));

    $cat_id = 0;

    foreach ($categories as $cat) {
        if (is_numeric($category)) {
            if (absint($category) == $cat->term_id) {
                $cat_id = $cat->term_id;
                break;
            }
        } else {
            if ($category == $cat->slug) {
                $cat_id = $cat->term_id;
                break;
            }

            if ($category == $cat->name) {
                $cat_id = $cat->term_id;
                break;
            }
        }
    }

    return $cat_id;
}

// @todo: Extract this
// This is wrong, it should be in JS and CSS files.
if (!(function_exists('geodir_home_map_add_script'))) {
	/**
	 * Adds the script in the page footer for the home page google map.
	 *
	 * @since 1.0.0
     * @return string Print the script in page footer.
	 */
	function geodir_home_map_add_script()
    {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function () {
                geoDirMapSlide();
                jQuery(window).resize(function () {
                    jQuery('.geodir_map_container.geodir-map-home-page').each(function () {
                        jQuery(this).find('.geodir-map-posttype-list').css({'width': 'auto'});
                        jQuery(this).find('.map-places-listing ul.place-list').css({'margin-left': '0px'});
                        geoDirMapPrepare(this);
                    });
                });
            });
            function geoDirMapPrepare($thisMap) {
                var $objMpList = jQuery($thisMap).find('.geodir-map-posttype-list');
                var $objPlList = jQuery($thisMap).find('.map-places-listing ul.place-list');
                var wArrL = parseFloat(jQuery($thisMap).find('.geodir-map-navigation .geodir-leftarrow').outerWidth(true));
                var wArrR = parseFloat(jQuery($thisMap).find('.geodir-map-navigation .geodir-rightarrow').outerWidth(true));
                var ptw1 = parseFloat($objMpList.outerWidth(true));
                $objMpList.css({'margin-left': wArrL + 'px'});
                $objMpList.attr('data-width', ptw1);
                ptw1 = ptw1 - (wArrL + wArrR);
                $objMpList.width(ptw1);
                var ptw = $objPlList.width();
                var ptw2 = 0;
                $objPlList.find('li').each(function () {
                    var ptw21 = jQuery(this).outerWidth(true);
                    ptw2 += parseFloat(ptw21);
                });
                var doMov = parseFloat(ptw * 0.75);
                ptw2 = ptw2 + ( ptw2 * 0.05 );
                var maxMargin = ptw2 - ptw;
                $objPlList.attr('data-domov', doMov);
                $objPlList.attr('data-maxMargin', maxMargin);
            }
            function geoDirMapSlide() {
                jQuery('.geodir_map_container.geodir-map-home-page').each(function () {
                    var $thisMap = this;
                    geoDirMapPrepare($thisMap);
                    var $objPlList = jQuery($thisMap).find('.map-places-listing ul.place-list');
                    jQuery($thisMap).find('.geodir-leftarrow a').click(function (e) {
                        e.preventDefault();
                        var cm = $objPlList.css('margin-left');
                        var doMov = parseFloat($objPlList.attr('data-domov'));
                        var maxMargin = parseFloat($objPlList.attr('data-maxMargin'));
                        cm = parseFloat(cm);
                        if (cm == 0 || maxMargin < 0) {
                            return;
                        }
                        domargin = cm + doMov;
                        if (domargin > 0) {
                            domargin = 0;
                        }
                        $objPlList.animate({'margin-left': domargin + 'px'}, 1000);
                    });
                    jQuery($thisMap).find('.geodir-rightarrow a').click(function (e) {
                        e.preventDefault();
                        var cm = $objPlList.css('margin-left');
                        var doMov = parseFloat($objPlList.attr('data-domov'));
                        var maxMargin = parseFloat($objPlList.attr('data-maxMargin'));
                        cm = parseFloat(cm);
                        domargin = cm - doMov;
                        if (cm == ( maxMargin * -1 ) || maxMargin < 0) {
                            return;
                        }
                        if (( domargin * -1 ) > maxMargin) {
                            domargin = maxMargin * -1;
                        }
                        $objPlList.animate({'margin-left': domargin + 'px'}, 1000);
                    });
                });
            }
        </script>
    <?php
    }
}

/**
 * Adds the script in the page footer for the popular category widget.
 *
 * @since 1.0.0
 * @return string Print the script in page footer.
 */
function geodir_popular_category_add_scripts()
{
    ?>
    <script type="text/javascript">
        jQuery(function ($) {
            $('.geodir-showcat').click(function () {
                var objCat = $(this).closest('.geodir-category-list-in');
                $(objCat).find('li.geodir-pcat-hide').removeClass('geodir-hide');
                $(objCat).find('a.geodir-showcat').addClass('geodir-hide');
                $(objCat).find('a.geodir-hidecat').removeClass('geodir-hide');
            });
            $('.geodir-hidecat').click(function () {
                var objCat = $(this).closest('.geodir-category-list-in');
                $(objCat).find('li.geodir-pcat-hide').addClass('geodir-hide');
                $(objCat).find('a.geodir-hidecat').addClass('geodir-hide');
                $(objCat).find('a.geodir-showcat').removeClass('geodir-hide');
            });
        });
    </script>
<?php
}

/**
 * Get the listing layout name from layout parameter.
 *
 * @since 1.0.0
 *
 * @param string $layout_choice Listing layout.
 * @return string Layout name.
 */
function gdsc_validate_layout_choice($layout_choice)
{
    switch (geodir_strtolower($layout_choice)) {
        case 'list';
        case 'one';
        case 'one_column';
        case 'onecolumn';
        case '1';
            $layout_choice = 'list';
            break;
        case 'gridview_onehalf';
        case 'two';
        case 'two_column';
        case 'two_columns';
        case 'twocolumn';
        case 'twocolumns';
        case '2';
            $layout_choice = 'gridview_onehalf';
            break;
        case 'gridview_onethird';
        case 'three';
        case 'three_column';
        case 'three_columns';
        case 'threecolumn';
        case 'threecolumns';
        case '3';
            $layout_choice = 'gridview_onethird';
            break;
        case 'gridview_onefourth';
        case 'four';
        case 'four_column';
        case 'four_columns';
        case 'fourcolumn';
        case 'fourcolumns';
        case '4';
            $layout_choice = 'gridview_onefourth';
            break;
        case 'gridview_onefifth';
        case 'five';
        case 'five_column';
        case 'five_columns';
        case 'fivecolumn';
        case 'fivecolumns';
        case '5';
            $layout_choice = 'gridview_onefifth';
            break;
        default:
            $layout_choice = 'gridview_onehalf';
            break;
    }

    return $layout_choice;
}

/**
 * Validate & get the correct sorting option.
 *
 * @since 1.0.0
 * @since 1.6.18 Allow order by custom field in widget listings results sorting.
 *
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 *
 * @param string $sort_choice Listing sort option.
 * @param string $post_type Post type to validate custom field sort.
 * @return string Listing sort.
 */
function gdsc_validate_sort_choice($sort_choice, $post_type = '')
{
    global $plugin_prefix;

    $sorts = array(
        'az',
        'latest',
        'featured',
        'high_review',
        'high_rating',
        'random',
    );

    if (in_array($sort_choice, $sorts)) {
        return $sort_choice;
    }

    if (!empty($post_type)) {
        $table = $plugin_prefix . $post_type . '_detail';
        
        if (!geodir_prepare_custom_sorting($sort_choice, $table)) {
            $sort_choice = '';
        }
    }

    if (empty($post_type) || empty($sort_choice)) {
        $sort_choice = 'latest';
    }

    return $sort_choice;
}

/**
 * Validate & get the listing layout width.
 *
 * @since 1.0.0
 *
 * @param string $width_choice Listing width.
 * @return int|null Listing width or empty value.
 */
function gdsc_validate_listing_width($width_choice)
{
    if (!(empty($width_choice))) {
        $width_choice = absint($width_choice);
    } else {
        return '';
    }

    if (100 < $width_choice) {
        $width_choice = 100;
    }

    // If listing_width is too narrow, it won't work, arbitrarily set to 10% here
    if (10 > $width_choice) {
        $width_choice = 10;
    }

    return $width_choice;
}

/**
 * Validate & get the event list filter.
 *
 * @since 1.0.0
 *
 * @param string $filter_choice Event filter option.
 * @return string Event filter option.
 */
function gdsc_validate_list_filter_choice($filter_choice)
{
    $filters = array(
        'all',
        'today',
        'upcoming',
        'past',
    );

    if (!(in_array($filter_choice, $filters))) {
        $filter_choice = 'all';
    }

    return $filter_choice;
}

/**
 * Get the geodirectory listings.
 *
 * @since 1.4.2
 * @since 1.6.5 $tags parameter added.
 * @since 1.6.18 New attributes added in gd_listings shortcode to filter user favorite listings.
 *
 * @global string $gridview_columns_widget The girdview style of the listings for widget.
 * @global bool $geodir_is_widget_listing Is this a widget listing?. Default: false.
 * @global bool   $geodir_event_widget_listview Check that current listview is event.
 * @global object $post The current post object.
 * @global array $map_jason Map data in json format.
 * @global array $map_canvas_arr Map canvas array.
 * @global object $gd_session GeoDirectory Session object.
 *
 * @param array $args Array of arguments to filter listings.
 * @return string Listings HTML content.
 */
function geodir_sc_gd_listings_output($args = array()) {
    $title				 = !empty($args['title']) ? __($args['title'], 'geodirectory') : '';
	$post_type 			 = !empty($args['post_type']) ? $args['post_type'] : 'gd_place';
	$category 			 = !empty($args['category']) ? $args['category'] : '0';
	$post_number		 = !empty($args['post_number']) ? $args['post_number'] : 10;
	$add_location_filter = !empty($args['add_location_filter']) ? true : false;
	$list_sort 			 = !empty($args['list_sort']) ? $args['list_sort'] : 'latest';
	$character_count	 = isset($args['character_count']) ? $args['character_count'] : '';
	$layout 			 = !empty($args['layout']) ? $args['layout'] : 'gridview_onehalf';
	$with_pagination 	 = !empty($args['with_pagination']) ? true : false;
	$event_type 	 	 = !empty($args['event_type']) ? $args['event_type'] : '';
    $shortcode_content   = !empty($args['shortcode_content']) ? trim($args['shortcode_content']) : '';
    $tags                = !empty($args['tags']) ? $args['tags'] : array();
    /**
     * Filter the content text displayed when no listings found.
     *
     * @since 1.6.0
     *
     * @param string $shortcode_content The shortcode content text.
     * @param array $args Array of arguments to filter listings.
     */
    $shortcode_content = apply_filters('geodir_sc_gd_listings_not_found_content', $shortcode_content, $args);
		
	$top_pagination 	 = $with_pagination && !empty($args['top_pagination']) ? true : false;
	$bottom_pagination 	 = $with_pagination && !empty($args['bottom_pagination']) ? true : false;
	
	$shortcode_atts		 = !empty($args['shortcode_atts']) ? $args['shortcode_atts'] : array();

	// ajax mode
	$geodir_ajax		 = !empty($args['geodir_ajax']) ? true : false;
	$pageno 	 		 = $geodir_ajax && !empty($args['pageno']) ? $args['pageno'] : 1;
	
	$query_args = array(
        'posts_per_page' => $post_number,
        'is_geodir_loop' => true,
        'gd_location' => $add_location_filter,
        'post_type' => $post_type,
        'order_by' => $list_sort,
		'pageno' => $pageno
    );

    if ($character_count >= 0) {
        $query_args['excerpt_length'] = $character_count;
    }
    
    if (!empty($args['post_author'])) {
        $query_args['post_author'] = $args['post_author'];
    }

    if (!empty($args['show_featured_only'])) {
        $query_args['show_featured_only'] = 1;
    }

    if (!empty($args['show_special_only'])) {
        $query_args['show_special_only'] = 1;
    }

    if (!empty($args['with_pics_only'])) {
        $query_args['with_pics_only'] = 0;
        $query_args['featured_image_only'] = 1;
    }

    if (!empty($args['with_videos_only'])) {
        $query_args['with_videos_only'] = 1;
    }
    
    if (!empty($args['show_favorites_only'])) {
        $query_args['show_favorites_only'] = 1;
        $query_args['favorites_by_user'] = !empty($args['favorites_by_user']) ? $args['favorites_by_user'] : 0;
    }
    $with_no_results = !empty($args['without_no_results']) ? false : true;

    if (!empty($category) && isset($category[0]) && $category[0] != '0') {
        $category_taxonomy = geodir_get_taxonomies($post_type);

        ######### WPML #########
        if (geodir_wpml_is_taxonomy_translated($category_taxonomy[0])) {
            $category = gd_lang_object_ids($category, $category_taxonomy[0]);
        }
        ######### WPML #########

        $tax_query = array(
            'taxonomy' => $category_taxonomy[0],
            'field' => 'id',
            'terms' => $category
        );

        $query_args['tax_query'] = array($tax_query);
    }
    
    if (!empty($tags)) {
        // Clean tags
        if (!is_array($tags)) {
            $comma = _x(',', 'tag delimiter');
            if ( ',' !== $comma ) {
                $tags = str_replace($comma, ',', $tags);
            }
            $tags = explode(',', trim($tags, " \n\t\r\0\x0B,"));
            $tags = array_map('trim', $tags);
        }
        
        if (!empty($tags)) {
            $tag_query = array(
                'taxonomy' => $post_type . '_tags',
                'field' => 'name',
                'terms' => $tags
            );

            if (!empty($query_args['tax_query'])) {
                $query_args['tax_query'][] = $tag_query;
            } else {
                $query_args['tax_query'] = array($tag_query);
            }
        }
    }

    global $gridview_columns_widget, $geodir_is_widget_listing;

    if ($post_type == 'gd_event' && function_exists('geodir_event_get_widget_events')) {
		global $geodir_event_widget_listview;
		$geodir_event_widget_listview = true;
		
		if ($event_type && in_array($event_type, array('past', 'today', 'upcoming'))) {
			$query_args['geodir_event_type'] = $event_type;
		}
				
		$total_posts = geodir_event_get_widget_events($query_args, true);
		$widget_listings = $total_posts > 0 ? geodir_event_get_widget_events($query_args) : array();
	} else {
		$total_posts = geodir_get_widget_listings($query_args, true);
		$widget_listings = $total_posts > 0 ? geodir_get_widget_listings($query_args) : array();
	}
	$current_gridview_columns_widget = $gridview_columns_widget;
    $identifier = ' gd-wgt-pagi-' . mt_rand();
    ob_start();
	if (!empty($widget_listings) || $with_no_results) {
		if (!$geodir_ajax) {
        /**
         * Called before the shortcode [gd_listings] content is output.
         *
         * @since 1.0.0
         */
        do_action('geodir_before_sc_gd_listings');
		?>
        <div class="geodir_locations geodir_location_listing geodir-sc-gd-listings <?php echo $identifier;?>">
            <?php if ($title != '') { ?>
            <div class="geodir_list_heading clearfix">
                <?php echo $title; ?>
            </div>
			<?php } ?>
            <div class="gd-sc-loader">
                <div class="gd-sc-content">
            <?php }
            if (!(empty($widget_listings) && !empty($shortcode_content))) {
                if (strstr($layout, 'gridview')) {
                    $listing_view_exp = explode('_', $layout);
                    $gridview_columns_widget = $layout;
                    $layout = $listing_view_exp[0];
                } else {
                    $gridview_columns_widget = '';
                }
                            
                global $post, $map_jason, $map_canvas_arr, $gd_session;

                $current_post = $post;
                $current_map_jason = $map_jason;
                $current_map_canvas_arr = $map_canvas_arr;
                $geodir_is_widget_listing = true;
                $gd_session->un_set('gd_listing_view');

                if ($with_pagination && $top_pagination) {				
                    echo geodir_sc_listings_pagination($total_posts, $post_number, $pageno);
                }

                geodir_get_template( 'widget-listing-listview.php', array( 'widget_listings' => $widget_listings, 'character_count' => $character_count, 'gridview_columns_widget' => $gridview_columns_widget, 'before_widget' => $before_widget ) );
                
                if ($with_pagination && $bottom_pagination) {				
                    echo geodir_sc_listings_pagination($total_posts, $post_number, $pageno);
                }

                $geodir_is_widget_listing = false;

                $GLOBALS['post'] = $current_post;
                if (!empty($current_post)) {
                    setup_postdata($current_post);
                }
                $map_jason = $current_map_jason;
                $map_canvas_arr = $current_map_canvas_arr;
                global $gridview_columns_widget;
                $gridview_columns_widget = $current_gridview_columns_widget;
            } else {
                echo $shortcode_content;
            }
			?>
			<?php
            if (!$geodir_ajax) { 
			?>
            </div><p class="geodir-sclisting-loading" style="display:none;"><i class="fa fa-cog fa-spin"></i></p></div>
<script type="text/javascript">
jQuery(document).on('click', '.<?php echo trim($identifier);?> .gd-wgt-page', function(e) {
    var container = jQuery( '.<?php echo trim($identifier);?>');
    var obj = this;
    var pid = parseInt(jQuery(this).data('page'));
    var items = jQuery(obj).closest('.gd-sc-content');
    var loading = jQuery('.geodir-sclisting-loading', container);
    
    if (!pid > 0 || !(items && typeof items != 'undefined')) {
        return false;
    }
    
    var scatts = "<?php echo addslashes(json_encode($shortcode_atts));?>";
    
    var data = {
      'action': 'geodir_sclistings',
      'geodir_sclistings_nonce': '<?php echo wp_create_nonce("geodir-sclistings-nonce"); ?>',
      'scatts': scatts,
      'geodir_ajax': true,
      'pageno': pid
    };
    
    jQuery(document).ajaxStop(function() {
        jQuery(items).css({'opacity': '1'});
        loading.hide();
    });

    jQuery(items).css({'opacity': '0.4'});
    loading.show();

    jQuery.post(geodir_var.geodir_ajax_url, data, function(response) {
        if (response && response != '0') {
            loading.hide();
            jQuery(items).html(response);
            <?php
              /**
               * if lazyload images enabled then refresh them once ajax page changed.
               */
              if (geodir_get_option('geodir_lazy_load', 1)) { ?>
              geodir_init_lazy_load();
              <?php } ?>
        }
    });
});
</script>
</div>
		<?php } ?>
    <?php
    }
	$output = ob_get_contents();
    ob_end_clean();

    return trim($output);
}

/**
 * Get pagination for gd_listings shortcode.
 *
 * @since 1.4.2
 *
 * @global string $gridview_columns_widget Layout type of listing.
 * @global bool   $geodir_is_widget_listing Check that current listview is widget listing.
 * @global bool   $geodir_event_widget_listview Check that current listview is event.
 * @global null|WP_Post $post Post object.
 * @global array $map_jason Map data in json format.
 * @global array $map_canvas_arr Map canvas array.
 *
 * @param int    $total_posts Total items count.
 * @param int    $posts_per_page Number items displayed on one page.
 * @param int    $pageno Current page number.
 * @param string $before Display html before pagination. Default empty.
 * @param string $after Display html after pagination. Default empty.
 * @param string $prelabel Previous page label. Default empty.
 * @param string $nxtlabel Next page label. Default empty.
 * @param int    $pages_to_show Number pages to visible in pagination. Default 5.
 * @param bool   $always_show Always display pagination when pagination not required.
 * @return string Listings pagination HTML content.
 */
function geodir_sc_listings_pagination($total_posts, $posts_per_page, $pageno, $before = '', $after = '', $prelabel = '', $nxtlabel = '', $pages_to_show = 5, $always_show = false) {
    if (empty($prelabel)) {
        $prelabel = '<strong>&laquo;</strong>';
    }

    if (empty($nxtlabel)) {
        $nxtlabel = '<strong>&raquo;</strong>';
    }

    $half_pages_to_show = round($pages_to_show / 2);

	$numposts = $total_posts;

	$max_page = ceil($numposts / $posts_per_page);

	if (empty($pageno)) {
		$pageno = 1;
	}

	ob_start();
	if ($max_page > 1 || $always_show) {
		// Extra pagination info
		$geodir_pagination_more_info = geodir_get_option('geodir_pagination_advance_info');
		$start_no = ( $pageno - 1 ) * $posts_per_page + 1;
		$end_no = min($pageno * $posts_per_page, $numposts);
		
		if ($geodir_pagination_more_info != '') {
			$pagination_info = '<div class="gd-pagination-details gd-pagination-details-' . $geodir_pagination_more_info . '">' . wp_sprintf(__('Showing listings %d-%d of %d', 'geodirectory'), $start_no, $end_no, $numposts) . '</div>';
			
			if ($geodir_pagination_more_info == 'before') {
				$before = $before . $pagination_info;
			} else if ($geodir_pagination_more_info == 'after') {
				$after = $pagination_info . $after;
			}
		}
			
		echo "<div class='gd-pagi-container'> $before <div class='Navi geodir-ajax-pagination'>";
		if ($pageno > 1) {
			echo '<a class="gd-page-sc-fst gd-wgt-page" data-page="1" href="javascript:void(0);">&laquo;</a>&nbsp;';
		}
		
		if (($pageno - 1) > 0) {
            echo '<a class="gd-page-sc-prev gd-wgt-page" data-page="' . (int)($pageno - 1) . '" href="javascript:void(0);">' . $prelabel . '</a>&nbsp;';
		}
		
		for ($i = $pageno - $half_pages_to_show; $i <= $pageno + $half_pages_to_show; $i++) {
			if ($i >= 1 && $i <= $max_page) {
				if ($i == $pageno) {
					echo "<strong class='on' class='gd-page-sc-act'>$i</strong>";
				} else {
					echo ' <a class="gd-page-sc-no gd-wgt-page" data-page="' . (int)$i . '" href="javascript:void(0);">' . $i . '</a> ';
				}
			}
		}
		
		if (($pageno + 1) <= $max_page) {
			echo '&nbsp;<a class="gd-page-sc-nxt gd-wgt-page" data-page="' . (int)($pageno + 1) . '" href="javascript:void(0);">' . $nxtlabel . '</a>';
		}
		
		if ($pageno < $max_page) {
			echo '&nbsp;<a class="gd-page-sc-lst gd-wgt-page" data-page="' . (int)$max_page . '" href="javascript:void(0);">&raquo;</a>';
		}
		echo "</div> $after </div>";
	}
	$output = ob_get_contents();
    ob_end_clean();

    return trim($output);
}

/**
 * Get the listings by using ajax request.
 *
 * @since 1.4.2
 *
 * @return string Listings HTML content.
 */
function geodir_sclistings_callback() {
    check_ajax_referer('geodir-sclistings-nonce', 'geodir_sclistings_nonce');
    //set variables
    $scatts = isset($_POST['scatts']) ? $_POST['scatts'] : NULL;
    $pageno = isset($_POST['pageno']) ? absint($_POST['pageno']) : 1;
	
	$shortcode_atts = !empty($scatts) ? (array)json_decode(stripslashes_deep($scatts)) : NULL;
	
	if (!empty($shortcode_atts) && is_array($shortcode_atts)) {
		$shortcode_atts['pageno'] = $pageno;
		$shortcode_atts['geodir_ajax'] = true;
		
		echo geodir_sc_gd_listings($shortcode_atts);
	} else {
		echo 0;
	}
    wp_die();
}
add_action('wp_ajax_geodir_sclistings', 'geodir_sclistings_callback');
add_action('wp_ajax_nopriv_geodir_sclistings', 'geodir_sclistings_callback');