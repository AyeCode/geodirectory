<?php
/**
 * BuddyBoss theme compatibility functions.
 *
 * This file lets the GeoDirectory Plugin use the BuddyBoss theme HTML wrappers to fit and work perfectly.
 *
 * @since 1.0.0
 * @package GeoDirectory
 */

add_action('after_setup_theme', 'gd_buddyboss_action_calls', 11);

remove_action('geodir_page_title', 'geodir_action_page_title', 10);
function gd_buddyboss_action_calls(){
    // listings page remove
    remove_action('geodir_listings_page_title', 'geodir_action_listings_title', 10);
    remove_action('geodir_listings_before_main_content', 'geodir_action_geodir_sidebar_listings_top', 10);
    remove_action('geodir_listings_before_main_content', 'geodir_breadcrumb', 20);
    remove_action('geodir_listings_page_description', 'geodir_action_listings_description', 10);
    remove_action('geodir_listings_page_description', 'geodir_location_action_listings_description', 100); // for location manager
    remove_action('geodir_listings_page_description', 'geodir_cpt_pt_desc', 10); //CPT manager

    //listing page add
    add_action('geodir_listings_content', 'geodir_breadcrumb', 3);
    add_action('geodir_listings_content', 'geodir_action_geodir_sidebar_listings_top', 4);
    add_action('geodir_listings_content', 'geodir_cpt_pt_desc', 5); //CPT manager
    if(defined("GEODIRLOCATION_VERSION")){
        add_action('geodir_listings_content', 'geodir_location_action_listings_description', 5);
    }else{
        add_action('geodir_listings_content', 'geodir_action_listings_description', 5);
    }
    add_action('geodir_listings_content', 'geodir_action_listings_title', 9);


    // details page remove
    remove_action('geodir_detail_before_main_content', 'geodir_action_geodir_set_preview_post', 8);
    remove_action('geodir_detail_before_main_content', 'geodir_action_geodir_preview_code', 9);
    remove_action('geodir_detail_before_main_content', 'geodir_action_geodir_sidebar_detail_top', 10);
    remove_action('geodir_detail_before_main_content', 'geodir_breadcrumb', 20);


    //details page add
    add_action('geodir_article_open', 'geodir_action_geodir_set_preview_post', 1);
    add_action('geodir_article_open','geodir_action_geodir_preview_code', 2);
    add_action('geodir_article_open', 'geodir_breadcrumb', 3);
    add_action('geodir_article_open', 'geodir_action_geodir_sidebar_detail_top', 5);



    //location/home remove
    remove_action('geodir_location_before_main_content', 'geodir_action_geodir_sidebar_home_top', 10);
    remove_action('geodir_location_before_main_content', 'geodir_breadcrumb', 20);

    remove_action('geodir_home_before_main_content', 'geodir_action_geodir_sidebar_home_top', 10);
    remove_action('geodir_home_before_main_content', 'geodir_breadcrumb', 20);

    //location/home add
    add_action('geodir_home_content', 'geodir_breadcrumb', 3);
    add_action('geodir_location_content', 'geodir_breadcrumb', 3);

    add_action('geodir_home_content', 'geodir_action_geodir_sidebar_home_top', 5);
    add_action('geodir_location_content', 'geodir_action_geodir_sidebar_home_top', 5);

    // search remove
    remove_action('geodir_search_before_main_content', 'geodir_action_geodir_sidebar_search_top', 10);
    remove_action('geodir_search_before_main_content', 'geodir_breadcrumb', 20);
    remove_action('geodir_search_page_title', 'geodir_action_search_page_title', 10);

    //search add
    add_action('geodir_search_content', 'geodir_action_geodir_sidebar_search_top', 5);
    add_action('geodir_search_content', 'geodir_breadcrumb', 3);
    add_action('geodir_search_content', 'geodir_action_search_page_title', 6);



}

function gd_bb__dequeue_script() {
    if(geodir_is_geodir_page()){
        wp_dequeue_script( 'selectboxes');
    }
}
add_action( 'wp_print_scripts', 'gd_bb__dequeue_script', 100 );

add_action('geodir_listings_content','_bb_do_geodir_listings_page_description',11);
function _bb_do_geodir_listings_page_description(){
    do_action('geodir_listings_page_description');
}
