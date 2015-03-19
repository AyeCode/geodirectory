<?php
// call
add_action('after_setup_theme', 'enfold_action_calls', 11);
function enfold_action_calls()
{

    /* ACTIONS
    ****************************************************************************************/

    // Add body class for styling purposes
    add_filter('body_class', 'wpgeo_enfold_body_class');

    // Pages using the page-builder shouldn't redirect on successful payment
    if (isset($_REQUEST['pay_action'])) {
        add_action('init', 'geodir_allow_payment_urls_enfold', 15);
    }

    // LOCATION MANAGER MENU ACTIONS - set the location menu item before the Enfold search
    if (function_exists('geodir_location_menu_items')) {
        remove_filter('wp_nav_menu_items', 'geodir_location_menu_items', 110);
        add_filter('wp_nav_menu_items', 'geodir_location_menu_items', 8, 2);
    }
    // GEODIR MENU ACTIONS - set the GeoDir menu items before the Enfold search
    remove_filter('wp_nav_menu_items', 'geodir_menu_items', 100);
    add_filter('wp_nav_menu_items', 'geodir_menu_items', 7, 2);

    // HOME TOP SIDEBAR
    remove_action('geodir_home_before_main_content', 'geodir_action_geodir_sidebar_home_top', 10);
    remove_action('geodir_location_before_main_content', 'geodir_action_geodir_sidebar_home_top', 10);
    //add_action( 'ava_after_main_container', 'enfold_home_sidebar' );


    // WRAPPER OPEN ACTIONS
    remove_action('geodir_wrapper_open', 'geodir_action_wrapper_open', 10);
    add_action('geodir_wrapper_open', 'enfold_action_wrapper_open', 9);
    add_action('geodir_wrapper_open', 'enfold_detail_title', 8, 2); // ADD GEODIR TITLE


    // WRAPPER CONTENT OPEN ACTIONS
    remove_action('geodir_wrapper_content_open', 'geodir_action_wrapper_content_open', 10);
    add_action('geodir_wrapper_content_open', 'enfold_action_wrapper_content_open', 9, 3);


    // SIDEBAR RIGHT OPEN ACTIONS
    remove_action('geodir_sidebar_right_open', 'geodir_action_sidebar_right_open', 10);
    add_action('geodir_sidebar_right_open', 'enfold_action_sidebar_right_open', 10, 4);

    // SIDEBAR LEFT OPEN ACTIONS
    remove_action('geodir_sidebar_left_open', 'geodir_action_sidebar_left_open', 10);
    add_action('geodir_sidebar_left_open', 'enfold_action_sidebar_left_open', 10, 4);


    // HOME PAGE BREADCRUMBS
    remove_action('geodir_home_before_main_content', 'geodir_breadcrumb', 20);
    remove_action('geodir_location_before_main_content', 'geodir_breadcrumb', 20);

    // LISTINGS PAGE BREADCRUMBS & TITLES
    remove_action('geodir_listings_before_main_content', 'geodir_breadcrumb', 20);
    remove_action('geodir_listings_page_title', 'geodir_action_listings_title', 10);

    // DETAILS PAGE BREADCRUMBS & TITLES
    remove_action('geodir_detail_before_main_content', 'geodir_breadcrumb', 20);
    remove_action('geodir_details_main_content', 'geodir_action_page_title', 20);

    // SEARCH PAGE BREADCRUMBS & TITLES
    remove_action('geodir_search_before_main_content', 'geodir_breadcrumb', 20);
    remove_action('geodir_search_page_title', 'geodir_action_search_page_title', 10);

    // AUTHOR PAGE BREADCRUMBS & TITLES
    remove_action('geodir_author_before_main_content', 'geodir_breadcrumb', 20);
    remove_action('geodir_author_page_title', 'geodir_action_author_page_title', 10);

    // DISABLE ENFOLD MAPS CALL
    add_filter('avf_load_google_map_api', 'gd_enfold_remove_maps_api', 10, 1);

    // make top section wide
    remove_action('geodir_home_before_main_content', 'geodir_action_geodir_sidebar_home_top', 10);
    remove_action('geodir_location_before_main_content', 'geodir_action_geodir_sidebar_home_top', 10);
    remove_action('geodir_author_before_main_content', 'geodir_action_geodir_sidebar_author_top', 10);
    remove_action('geodir_search_before_main_content', 'geodir_action_geodir_sidebar_search_top', 10);
    remove_action('geodir_detail_before_main_content', 'geodir_action_geodir_sidebar_detail_top', 10);
    remove_action('geodir_listings_before_main_content', 'geodir_action_geodir_sidebar_listings_top', 10);

    add_action('geodir_wrapper_open', 'gd_enfold_compat_add_top_section_back', 5);

} // Close enfold_action_calls


function gd_enfold_compat_add_top_section_back()
{

    if (is_page_geodir_home() || geodir_is_page('location')) {
        add_action('geodir_wrapper_open', 'geodir_action_geodir_sidebar_home_top', 8);
    } elseif (geodir_is_page('listing')) {
        add_action('geodir_wrapper_open', 'geodir_action_geodir_sidebar_listings_top', 8);
    } elseif (geodir_is_page('detail')) {
        add_action('geodir_wrapper_open', 'geodir_action_geodir_sidebar_detail_top', 8);
    } elseif (geodir_is_page('search')) {
        add_action('geodir_wrapper_open', 'geodir_action_geodir_sidebar_search_top', 8);
    } elseif (geodir_is_page('author')) {
        add_action('geodir_wrapper_open', 'geodir_action_geodir_sidebar_author_top', 8);
    }


}

//* FUNCTIONS
/****************************************************************************************/


function wpgeo_enfold_body_class($classes)
{
    $classes[] = 'wpgeo-enfold';
    return $classes;
}

function geodir_allow_payment_urls_enfold()
{
    global $builder;
    remove_action('template_redirect', array($builder, 'template_redirect'), 1000);
}


// WRAPPER OPEN FUNCTIONS
function enfold_action_wrapper_open()
{
    echo "<div class='container_wrap container_wrap_first main_color " . avia_layout_class('main', false) . "'>";
    echo "<div class='container template-blog '>";
}

// PAGE TITLE & BREADCRUMB FUNCTIONS
function enfold_detail_title($page, $class)
{
    //echo '###'.$page;
    global $wp;
    if (isset($wp->query_vars['page_id']) && $wp->query_vars['page_id'] == get_option('geodir_location_page') && !isset($_GET['geodir_signup'])) {
        add_action('avia_breadcrumbs_trail', 'enfold_detail_breadcrum', 8, 2);
        echo avia_title();
    } elseif ($page == 'details-page') {
        add_action('avia_breadcrumbs_trail', 'enfold_detail_breadcrum', 8, 2);
        echo avia_title();
    } elseif ($page == 'listings-page' || $page == 'search-page') {
        add_action('avia_breadcrumbs_trail', 'enfold_detail_breadcrum', 8, 2);
        ob_start(); // Start buffering;
        geodir_action_listings_title();
        $gd_title = ob_get_clean();
        $title_p = explode('">', $gd_title);
        $title = str_replace('</h1></header>', "", $title_p[2]);
        //print_r($title_p);
        echo avia_title(array('title' => $title));
    } elseif ($page == 'author-page') {
        add_action('avia_breadcrumbs_trail', 'enfold_detail_breadcrum', 8, 2);
        ob_start(); // Start buffering;
        geodir_action_author_page_title();
        $gd_title = ob_get_clean();
        $gd_title = str_replace('<h1>', "", $gd_title);
        $gd_title = str_replace('</h1>', "", $gd_title);
        echo avia_title(array('title' => $gd_title));
    } elseif ($page == 'add-listing-page') {
        add_action('avia_breadcrumbs_trail', 'enfold_detail_breadcrum', 8, 2);
        echo avia_title();
    } elseif ($page == 'add-listing-page') {
        add_action('avia_breadcrumbs_trail', 'enfold_detail_breadcrum', 8, 2);
        echo avia_title();
    }

}

function enfold_detail_breadcrum($trail, $args)
{
    ob_start(); // Start buffering;
    geodir_breadcrumb();
    $gd_crums = ob_get_clean();
    if ($gd_crums) {
        $gd_crums = str_replace('<div class="geodir-breadcrumb clearfix"><ul id="breadcrumbs"><li>', "", $gd_crums);
        $gd_crums = str_replace('</li></ul></div>', "", $gd_crums);
        $gd_crums = str_replace('&nbsp;>&nbsp;', " > ", $gd_crums);
        $gd_crums = str_replace('</li><li>', "", $gd_crums);
        $gd_crums = explode(" > ", $gd_crums);
        $trail_end = array_pop($gd_crums);
        $gd_crums['trail_end'] = $trail_end;
        //print_r($gd_crums);
        //print_r($trail);
        $trail = $gd_crums;
    }
    return $trail;
}


// WRAPPER CONTENT OPEN FUNCTIONS
function enfold_action_wrapper_content_open($type = '', $id = '', $class = '')
{
    if (is_home() && isset($_GET['geodir_signup'])) {
        echo "<main class='template-page content twelve alpha units " . $class . "' " . avia_markup_helper(array('context' => 'content', 'post_type' => 'page', 'echo' => false)) . ">";
    } else {
        echo "<main class='template-page content " . avia_layout_class('content', false) . " units " . $class . "' " . avia_markup_helper(array('context' => 'content', 'post_type' => 'page', 'echo' => false)) . ">";
    }
    echo '<div class="entry-content-wrapper">';
}


// SIDEBAR RIGHT OPEN FUNCTIONS
function enfold_action_sidebar_right_open($type = '', $id = '', $class = '', $itemtype = '')
{
    $sidebar_smartphone = avia_get_option('smartphones_sidebar') == 'smartphones_sidebar' ? 'smartphones_sidebar_active' : "";
    echo "<aside class='sidebar sidebar_right " . $sidebar_smartphone . " " . avia_layout_class('sidebar', false) . " units' " . avia_markup_helper(array('context' => 'sidebar', 'echo' => false)) . ">";
    echo "<div class='inner_sidebar extralight-border'>";
}

// SIDEBAR LEFT OPEN FUNCTIONS
function enfold_action_sidebar_left_open($type = '', $id = '', $class = '', $itemtype = '')
{
    $sidebar_smartphone = avia_get_option('smartphones_sidebar') == 'smartphones_sidebar' ? 'smartphones_sidebar_active' : "";
    echo "<aside class='sidebar sidebar_left " . $sidebar_smartphone . " " . avia_layout_class('sidebar', false) . " units' " . avia_markup_helper(array('context' => 'sidebar', 'echo' => false)) . ">";
    echo "<div class='inner_sidebar extralight-border'>";
}


// DISABLE MAPS API FUNCTION
function gd_enfold_remove_maps_api($call)
{
    return false;
}


//enfold_action_calls();