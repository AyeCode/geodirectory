<?php
/**
 * Permalink tab settings.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global array $geodir_settings Geodirectory settings array.
 */
global $geodir_settings;

/**
 * Filter GD Permalink Settings array.
 *
 * @since 1.0.0
 * @package GeoDirectory
 */
$geodir_settings['title_meta_settings'] = apply_filters('geodir_title_meta_settings', array(

    /* Listing Permalink Settings start */
    array('name' => __('Title / Meta', 'geodirectory'), 'type' => 'no_tabs', 'desc' => 'Settings to set page title and meta', 'id' => 'geodir_title_meta_settings '),


    array('name' => __('Available Variables', 'geodirectory'),
        'desc' => __('%%title%%, %%sitename%%, %%sitedesc%%, %%excerpt%%, %%pt_single%%, %%pt_plural%%, %%category%%, %%id%%, %%sep%%, %%location%%, %%in_location%%', 'geodirectory'),
        'type' => 'sectionstart',
        'id' => 'geodir_meta_vars'),

    array('type' => 'sectionend', 'id' => 'geodir_meta_vars'),

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
        'std' => ''
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
        'std' => '%%title%% %%sep%% %%sitename%%'
    ),

    array(
        'name' => __('Details page meta description', 'geodirectory'),
        'desc' => __('Enter the meta description for the details page.', 'geodirectory'),
        'id' => 'geodir_meta_desc_detail',
        'type' => 'textarea',
        'css' => 'width:100%;',
        'std' => '%%excerpt%%'
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
        'std' => '%%pt_plural%% %%in_location%% %%sep%% %%sitename%%'
    ),

    array(
        'name' => __('Post type page meta description', 'geodirectory'),
        'desc' => __('Enter the meta description for the post type pages.', 'geodirectory'),
        'id' => 'geodir_meta_desc_pt',
        'type' => 'textarea',
        'css' => 'width:100%;',
        'std' => '%%pt_plural%% %%in_location%%'
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
        'std' => '%%category%% %%in_location%% %%sep%% %%sitename%%'
    ),

    array(
        'name' => __('Listing page meta description', 'geodirectory'),
        'desc' => __('Enter the meta description for the category listing pages.', 'geodirectory'),
        'id' => 'geodir_meta_desc_listing',
        'type' => 'textarea',
        'css' => 'width:100%;',
        'std' => 'Posts related to Category: %%category%% %%in_location%%'
    ),

    array('type' => 'sectionend', 'id' => 'geodir_location_meta'),

    // Cat listing page meta
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
        'std' => '%%title%% %%location%% %%sep%% %%sitename%%'
    ),

    array(
        'name' => __('Location page meta description', 'geodirectory'),
        'desc' => __('Enter the meta description for the location pages.', 'geodirectory'),
        'id' => 'geodir_meta_desc_location',
        'type' => 'textarea',
        'css' => 'width:100%;',
        'std' => '%%location%%'
    ),

    array('type' => 'sectionend', 'id' => 'geodir_location_meta'),




    /* Listing Detail Permalink Settings End */


)); // End Design settings


//$_SESSION['geodir_settings']['permalink_settings'] = $geodir_settings['permalink_settings'] ;
