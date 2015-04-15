<?php

// STRIP THE GD BREADCRUMB WRAPPERS
add_filter('geodir_breadcrumb', 'gd_strip_breadcrumb_wrappers');
function gd_strip_breadcrumb_wrappers($breadcrumb)
{
    $breadcrumb = str_replace(array("<li>","</li>"), "", $breadcrumb);
    $breadcrumb = str_replace('<div class="geodir-breadcrumb clearfix"><ul id="breadcrumbs">', '<ul class="fusion-breadcrumbs"><li>', $breadcrumb);
    $breadcrumb = str_replace('</li></ul></div>', '</ul>', $breadcrumb);
    return $breadcrumb;
}

// CHANGE THE GD BREADCRUMB SEPARATOR
add_filter('geodir_breadcrumb_separator', 'gd_change_breadcrumb_separator');
function gd_change_breadcrumb_separator($separator)
{
    $separator = ' / ';
    return $separator;
}

// NEW TITLE BAR FUNCTIONS FOR GD PAGES

add_action('avada_override_current_page_title_bar','gd_avada_current_page_title_bar_change');
function gd_avada_current_page_title_bar_change($c_pageID)
{
    if (geodir_is_geodir_page()) {
        gd_avada_current_page_title_bar();
    }else{
        avada_current_page_title_bar( $c_pageID );
    }

}

function gd_avada_current_page_title_bar()
{
    ob_start();
    geodir_breadcrumb();
    $secondary_content = ob_get_contents();
    ob_get_clean();

    $title = '';
    $subtitle = '';

    if (geodir_is_page('listing')) {
        ob_start(); // Start buffering;
        geodir_action_listings_title();
        $title = ob_get_clean();
        avada_page_title_bar($title, $subtitle, $secondary_content);
    }

    if (geodir_is_page('add-listing')) {
        ob_start(); // Start buffering;
        geodir_action_add_listing_page_title();
        $title = ob_get_clean();
        avada_page_title_bar($title, $subtitle, $secondary_content);
    }

    if (geodir_is_page('author')) {
        ob_start(); // Start buffering;
        geodir_action_author_page_title();
        $title = ob_get_clean();
        avada_page_title_bar($title, $subtitle, $secondary_content);
    }

    if (geodir_is_page('detail') || geodir_is_page('preview')) {
        $title = get_the_title();
        avada_page_title_bar($title, $subtitle, $secondary_content);
    }

    if (geodir_is_page('search')) {
        ob_start(); // Start buffering;
        geodir_action_search_page_title();
        $title = ob_get_clean();
        avada_page_title_bar($title, $subtitle, $secondary_content);
    }
}

function gd_compat_php_avada()
{
    // change widget wrappers
    //add_filter( 'geodir_before_widget', 'geodir_before_widget_compat',10,1 );
    //add_filter( 'geodir_after_widget', 'geodir_after_widget_compat',10,1 );

    // REMOVE BREADCRUMB
    remove_action('geodir_detail_before_main_content', 'geodir_breadcrumb', 20);
    remove_action('geodir_listings_before_main_content', 'geodir_breadcrumb', 20);
    remove_action('geodir_author_before_main_content', 'geodir_breadcrumb', 20);
    remove_action('geodir_search_before_main_content', 'geodir_breadcrumb', 20);
    remove_action('geodir_home_before_main_content', 'geodir_breadcrumb', 20);
    remove_action('geodir_location_before_main_content', 'geodir_breadcrumb', 20);

    // REMOVE PAGE TITLES
    remove_action('geodir_listings_page_title', 'geodir_action_listings_title', 10);
    remove_action('geodir_add_listing_page_title', 'geodir_action_add_listing_page_title', 10);
    remove_action('geodir_details_main_content', 'geodir_action_page_title', 20);
    remove_action('geodir_search_page_title', 'geodir_action_search_page_title', 10);
    remove_action('geodir_author_page_title', 'geodir_action_author_page_title', 10);

    // make top section wide
    remove_action('geodir_home_before_main_content', 'geodir_action_geodir_sidebar_home_top', 10);
    remove_action('geodir_location_before_main_content', 'geodir_action_geodir_sidebar_home_top', 10);
    remove_action('geodir_author_before_main_content', 'geodir_action_geodir_sidebar_author_top', 10);
    remove_action('geodir_search_before_main_content', 'geodir_action_geodir_sidebar_search_top', 10);
    remove_action('geodir_detail_before_main_content', 'geodir_action_geodir_sidebar_detail_top', 10);
    remove_action('geodir_listings_before_main_content', 'geodir_action_geodir_sidebar_listings_top', 10);

    //gd_compat_add_top_section_back();

}

add_action('avada_before_main', 'gd_compat_add_top_section_back', 10);
function gd_compat_add_top_section_back()
{

    if (is_page_geodir_home() || geodir_is_page('location')) {
        geodir_action_geodir_sidebar_home_top();
    } elseif (geodir_is_page('listing')) {
        geodir_action_geodir_sidebar_listings_top();
    } elseif (geodir_is_page('detail')) {
        geodir_action_geodir_sidebar_detail_top();
    } elseif (geodir_is_page('search')) {
        geodir_action_geodir_sidebar_search_top();
    } elseif (geodir_is_page('author')) {
        geodir_action_geodir_sidebar_author_top();
    }


}


// Add body class for styling purposes
add_filter('body_class', 'gd_compat_body_class');
function gd_compat_body_class($classes)
{
    if (geodir_is_geodir_page()) {
        $classes[] = 'wpgeo-avada';
    } else {
        $classes[] = '';
    }
    return $classes;
}


function geodir_before_widget_compat($var)
{
    return '<div id="%1$s" class="geodir-widget %2$s">';
}

function geodir_after_widget_compat($var)
{
    return '</div>';
}

add_filter('geodir_search_form_class', 'geodir_search_form_class_avada');
function geodir_search_form_class_avada($class)
{
    $class .= ' search';
    return $class;
}


// run Avada compat
gd_compat_php_avada();