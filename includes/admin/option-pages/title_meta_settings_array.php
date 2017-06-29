<?php
/**
 * Permalink tab settings.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global array $geodir_settings Geodirectory settings array.
 */
global $geodir_settings;

$gd_wpseo_use = '';
if ((class_exists('WPSEO_Frontend') || class_exists('All_in_One_SEO_Pack')) && !geodir_disable_yoast_seo_metas()) {
    $gd_wpseo_use = "<b style='color:red;'>".__('Please use the WPSEO settings instead.','geodirectory')."</b><br />";
}

/**
 * Filter GD Permalink Settings array.
 *
 * @since 1.0.0
 * @package GeoDirectory
 */
$geodir_settings['title_meta_settings'] = apply_filters('geodir_title_meta_settings', array(

    /* Listing Permalink Settings start */
    array('name' => __('Title / Meta', 'geodirectory'), 'type' => 'no_tabs', 'desc' => 'Settings to set page title and meta', 'id' => 'geodir_title_meta_settings '),

    array('name' => $gd_wpseo_use.__('Available Variables', 'geodirectory'),
        'desc' => '%%title%%, %%sitename%%, %%sitedesc%%, %%excerpt%%, %%pt_single%%, %%pt_plural%%, %%category%%, %%id%%, %%sep%%, %%location%%, %%in_location%%, %%in_location_single%%, %%location_single%%, %%location_country%%, %%in_location_country%%, %%location_region%%, %%in_location_region%%, %%location_city%%, %%in_location_city%%, %%location_sep%%, %%search_term%%, %%search_near%%, %%name%%, %%page%%, %%pagenumber%%, %%pagetotal%%',
        'type' => 'sectionstart',
        'id' => 'geodir_meta_vars'),

    array('type' => 'sectionend', 'id' => 'geodir_meta_vars'),
    
    array('name' => __('Title & Metas Settings', 'geodirectory'),
        'type' => 'sectionstart',
        'desc' => '',
        'id' => 'geodir_titles_and_metas'),

    array(  
        'name' => __( 'Disable overwrite by Yoast SEO titles & metas on GD pages', 'geodirectory' ),
        'desc' => __( 'This allows to disable overwriting by Yoast SEO titles & metas on GD pages. If ticked then GD pages will use titles & metas settings from GeoDirectory > Titles & Metas. Otherwise it will use titles & metas settings from SEO > Titles & Metas.', 'geodirectory' ),
        'id' => 'geodir_disable_yoast_meta',
        'type' => 'checkbox',
        'std' => '0'
    ),

    array('type' => 'sectionend', 'id' => 'geodir_titles_and_metas'),

    array('name' => __('Homepage Meta Settings', 'geodirectory'),
        'type' => 'sectionstart',
        'desc' => '',
        'id' => 'geodir_home_meta'),

    array(
        'name' => __('Homepage meta title', 'geodirectory'),
        'desc' => __('This will use the title of the page set as frontpage if left blank.', 'geodirectory'),
        'id' => 'geodir_meta_title_homepage',
        'type' => 'text',
        'css' => 'width:100%;',
        'std' => '',
        'placeholder' => ''
    ),

    array(
        'name' => __('Homepage meta description', 'geodirectory'),
        'desc' => __('Enter the meta description for the homepage.', 'geodirectory'),
        'id' => 'geodir_meta_desc_homepage',
        'type' => 'textarea',
        'css' => 'width:100%;',
        'std' => ''
    ),

    array('type' => 'sectionend', 'id' => 'geodir_home_meta'),

    // details page meta
    array('name' => __('Details Page Meta Settings', 'geodirectory'),
        'type' => 'sectionstart',
        'desc' => '',
        'id' => 'geodir_details_meta'),

    array(
        'name' => __('Details page meta title', 'geodirectory'),
        'desc' => __('Enter the meta title for the details page.', 'geodirectory'),
        'id' => 'geodir_meta_title_detail',
        'type' => 'text',
        'css' => 'width:100%;',
        'std' => __('%%title%% %%sep%% %%sitename%%', 'geodirectory'),
        'placeholder' => '%%title%% %%sep%% %%sitename%%'
    ),

    array(
        'name' => __('Details page meta description', 'geodirectory'),
        'desc' => __('Enter the meta description for the details page.', 'geodirectory'),
        'id' => 'geodir_meta_desc_detail',
        'type' => 'textarea',
        'css' => 'width:100%;',
        'std' => __('%%excerpt%%', 'geodirectory'),
        'placeholder' => '%%excerpt%%'
    ),

    array('type' => 'sectionend', 'id' => 'geodir_details_meta'),

    // CPT page meta
    array('name' => __('Post Type Page Meta Settings', 'geodirectory'),
        'type' => 'sectionstart',
        'desc' => '',
        'id' => 'geodir_pt_meta'),

    array(
        'name' => __('Post type page meta title', 'geodirectory'),
        'desc' => __('Enter the meta title for the post type pages.', 'geodirectory'),
        'id' => 'geodir_meta_title_pt',
        'type' => 'text',
        'css' => 'width:100%;',
        'std' => __('%%pt_plural%% %%in_location%% %%sep%% %%sitename%%', 'geodirectory'),
        'placeholder' => '%%pt_plural%% %%in_location%% %%sep%% %%sitename%%'
    ),

    array(
        'name' => __('Post type page meta description', 'geodirectory'),
        'desc' => __('Enter the meta description for the post type pages.', 'geodirectory'),
        'id' => 'geodir_meta_desc_pt',
        'type' => 'textarea',
        'css' => 'width:100%;',
        'std' => __('%%pt_plural%% %%in_location%%', 'geodirectory'),
        'placeholder' => '%%pt_plural%% %%in_location%%'
    ),

    array(
        'name' => __('Post type page title', 'geodirectory'),
        'desc' => __('Enter the title for the post type pages.', 'geodirectory'),
        'id' => 'geodir_page_title_pt',
        'type' => 'text',
        'css' => 'width:100%;',
        'std' => __('All %%pt_plural%% %%in_location_single%%', 'geodirectory'),
        'placeholder' => 'All %%pt_plural%% %%in_location_single%%'
    ),

    array('type' => 'sectionend', 'id' => 'geodir_pt_meta'),

    // Cat listing page meta
    array('name' => __('Listing Page Meta Settings', 'geodirectory'),
        'type' => 'sectionstart',
        'desc' => '',
        'id' => 'geodir_pt_meta'),

    array(
        'name' => __('Listing page meta title', 'geodirectory'),
        'desc' => __('Enter the meta title for the category listing pages.', 'geodirectory'),
        'id' => 'geodir_meta_title_listing',
        'type' => 'text',
        'css' => 'width:100%;',
        'std' => __('%%category%% %%in_location%% %%sep%% %%sitename%%', 'geodirectory'),
        'placeholder' => '%%category%% %%in_location%% %%sep%% %%sitename%%'
    ),

    array(
        'name' => __('Listing page meta description', 'geodirectory'),
        'desc' => __('Enter the meta description for the category listing pages.', 'geodirectory'),
        'id' => 'geodir_meta_desc_listing',
        'type' => 'textarea',
        'css' => 'width:100%;',
        'std' => __('Posts related to Category: %%category%% %%in_location%%', 'geodirectory'),
        'placeholder' => 'Posts related to Category: %%category%% %%in_location%%'
    ),

    array(
        'name' => __('Category listing page title', 'geodirectory'),
        'desc' => __('Enter the title for the category listing pages.', 'geodirectory'),
        'id' => 'geodir_page_title_cat-listing',
        'type' => 'text',
        'css' => 'width:100%;',
        'std' => __('All %%category%% %%in_location_single%%', 'geodirectory'),
        'placeholder' => 'All %%category%% %%in_location_single%%'
    ),

    array(
        'name' => __('Tag listing page title', 'geodirectory'),
        'desc' => __('Enter the title for the tag listing pages.', 'geodirectory'),
        'id' => 'geodir_page_title_tag-listing',
        'type' => 'text',
        'css' => 'width:100%;',
        'std' => __('Tag: %%tag%% %%in_location_single%%', 'geodirectory'),
        'placeholder' => 'Tag: %%tag%% %%in_location_single%%'
    ),

    array('type' => 'sectionend', 'id' => 'geodir_location_meta'),

    // location page meta
    array('name' => __('Location Page Meta Settings', 'geodirectory'),
        'type' => 'sectionstart',
        'desc' => '',
        'id' => 'geodir_pt_meta'),

    array(
        'name' => __('Location page meta title', 'geodirectory'),
        'desc' => __('Enter the meta title for the location pages.', 'geodirectory'),
        'id' => 'geodir_meta_title_location',
        'type' => 'text',
        'css' => 'width:100%;',
        'std' => __('%%title%% %%location%% %%sep%% %%sitename%%', 'geodirectory'),
        'placeholder' => '%%title%% %%location%% %%sep%% %%sitename%%'
    ),

    array(
        'name' => __('Location page meta description', 'geodirectory'),
        'desc' => __('Enter the meta description for the location pages.', 'geodirectory'),
        'id' => 'geodir_meta_desc_location',
        'type' => 'textarea',
        'css' => 'width:100%;',
        'std' => __('%%location%%', 'geodirectory'),
        'placeholder' => '%%location%%'
    ),

    array('type' => 'sectionend', 'id' => 'geodir_location_meta'),

    // Search page meta
    array('name' => __('Search Page Meta Settings', 'geodirectory'),
        'type' => 'sectionstart',
        'desc' => '',
        'id' => 'geodir_pt_meta'),

    array(
        'name' => __('Search page meta title', 'geodirectory'),
        'desc' => __('Enter the meta title for the search page.', 'geodirectory'),
        'id' => 'geodir_meta_title_search',
        'type' => 'text',
        'css' => 'width:100%;',
        'std' => __('%%pt_plural%% search results for %%search_term%%, Near %%search_near%% %%sep%% %%sitename%%', 'geodirectory'),
        'placeholder' => '%%pt_plural%% search results for %%search_term%%, Near %%search_near%% %%sep%% %%sitename%%'
    ),

    array(
        'name' => __('Search page meta description', 'geodirectory'),
        'desc' => __('Enter the meta description for the search page.', 'geodirectory'),
        'id' => 'geodir_meta_desc_search',
        'type' => 'textarea',
        'css' => 'width:100%;',
        'std' => __('%%pt_plural%% search results for %%search_term%%, Near %%search_near%%', 'geodirectory'),
        'placeholder' => '%%pt_plural%% search results for %%search_term%%, Near %%search_near%%'
    ),

    array('type' => 'sectionend', 'id' => 'geodir_location_meta'),

    //Add listing page meta
    array('name' => __('Add Listing Page Meta Settings', 'geodirectory'),
        'type' => 'sectionstart',
        'desc' => '',
        'id' => 'geodir_pt_meta'),

    array(
        'name' => __('Add listing page meta title', 'geodirectory'),
        'desc' => __('Enter the meta title for the add listing page.', 'geodirectory'),
        'id' => 'geodir_meta_title_add-listing',
        'type' => 'text',
        'css' => 'width:100%;',
        'std' => __('Add %%pt_single%% %%sep%% %%sitename%%', 'geodirectory'),
        'placeholder' => 'Add %%pt_single%% %%sep%% %%sitename%%'
    ),

    array(
        'name' => __('Add listing page meta description', 'geodirectory'),
        'desc' => __('Enter the meta description for the add listing page.', 'geodirectory'),
        'id' => 'geodir_meta_desc_add-listing',
        'type' => 'textarea',
        'css' => 'width:100%;',
        'std' => __('Add %%pt_single%%', 'geodirectory'),
        'placeholder' => 'Add %%pt_single%%'
    ),

    array(
        'name' => __('Add listing page title', 'geodirectory'),
        'desc' => __('Enter the title for the add listing page.', 'geodirectory'),
        'id' => 'geodir_page_title_add-listing',
        'type' => 'text',
        'css' => 'width:100%;',
        'std' => __('Add %%pt_single%%', 'geodirectory'),
        'placeholder' => 'Add %%pt_single%%'
    ),

    array(
        'name' => __('Edit listing page title', 'geodirectory'),
        'desc' => __('Enter the title for the edit listing page.', 'geodirectory'),
        'id' => 'geodir_page_title_edit-listing',
        'type' => 'text',
        'css' => 'width:100%;',
        'std' => __('Edit %%pt_single%%', 'geodirectory'),
        'placeholder' => 'Edit %%pt_single%%'
    ),

    array('type' => 'sectionend', 'id' => 'geodir_location_meta'),

    //Author page meta
    array('name' => __('Author Page Meta Settings', 'geodirectory'),
        'type' => 'sectionstart',
        'desc' => '',
        'id' => 'geodir_pt_meta'),

    array(
        'name' => __('Author page meta title', 'geodirectory'),
        'desc' => __('Enter the meta title for the author page.', 'geodirectory'),
        'id' => 'geodir_meta_title_author',
        'type' => 'text',
        'css' => 'width:100%;',
        'std' => __('Author: %%name%% %%sep%% %%sitename%%', 'geodirectory'),
        'placeholder' => 'Author: %%name%% %%sep%% %%sitename%%'
    ),

    array(
        'name' => __('Author page meta description', 'geodirectory'),
        'desc' => __('Enter the meta description for the author page.', 'geodirectory'),
        'id' => 'geodir_meta_desc_author',
        'type' => 'textarea',
        'css' => 'width:100%;',
        'std' => ''
    ),

    array(
        'name' => __('Author page title', 'geodirectory'),
        'desc' => __('Enter the title for the author page.', 'geodirectory'),
        'id' => 'geodir_page_title_author',
        'type' => 'text',
        'css' => 'width:100%;',
        'std' => __('%%pt_plural%% by: %%name%%', 'geodirectory'),
        'placeholder' => '%%pt_plural%% by: %%name%%'
    ),

    array(
        'name' => __('Author favorite page title', 'geodirectory'),
        'desc' => __('Enter the title for the author favorite page.', 'geodirectory'),
        'id' => 'geodir_page_title_favorite',
        'type' => 'text',
        'css' => 'width:100%;',
        'std' => __('%%name%%: Favorite %%pt_plural%%', 'geodirectory'),
        'placeholder' => '%%name%%: Favorite %%pt_plural%%'
    ),

    array('type' => 'sectionend', 'id' => 'geodir_location_meta'),

    //Login page meta
    array('name' => __('Login Page Meta Settings', 'geodirectory'),
        'type' => 'sectionstart',
        'desc' => '',
        'id' => 'geodir_login_meta'),

    array(
        'name' => __('Login page meta title', 'geodirectory'),
        'desc' => __('Enter the meta title for the login page.', 'geodirectory'),
        'id' => 'geodir_meta_title_login',
        'type' => 'text',
        'css' => 'width:100%;',
        'std' => __('%%title%% %%sep%% %%sitename%%', 'geodirectory'),
        'placeholder' => '%%title%% %%sep%% %%sitename%%'
    ),

    array(
        'name' => __('Login page meta description', 'geodirectory'),
        'desc' => __('Enter the meta description for the login page.', 'geodirectory'),
        'id' => 'geodir_meta_desc_login',
        'type' => 'textarea',
        'css' => 'width:100%;',
        'std' => ''
    ),

    array('type' => 'sectionend', 'id' => 'geodir_login_meta'),

    //Listing success page meta
    array('name' => __('Listing Success Page Meta Settings', 'geodirectory'),
        'type' => 'sectionstart',
        'desc' => '',
        'id' => 'geodir_login_meta'),

    array(
        'name' => __('Listing success page meta title', 'geodirectory'),
        'desc' => __('Enter the meta title for the listing success page.', 'geodirectory'),
        'id' => 'geodir_meta_title_listing-success',
        'type' => 'text',
        'css' => 'width:100%;',
        'std' => __('%%title%% %%sep%% %%sitename%%', 'geodirectory'),
        'placeholder' => '%%title%% %%sep%% %%sitename%%'
    ),

    array(
        'name' => __('Listing success page meta description', 'geodirectory'),
        'desc' => __('Enter the meta description for the listing success page.', 'geodirectory'),
        'id' => 'geodir_meta_desc_listing-success',
        'type' => 'textarea',
        'css' => 'width:100%;',
        'std' => ''
    ),

    array('type' => 'sectionend', 'id' => 'geodir_login_meta'),




)); // End Design settings


