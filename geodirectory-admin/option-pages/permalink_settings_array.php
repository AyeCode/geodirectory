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
$geodir_settings['permalink_settings'] = apply_filters('geodir_permalink_settings', array(

    /* Listing Permalink Settings start */
    array('name' => __('Permalink', GEODIRECTORY_TEXTDOMAIN), 'type' => 'no_tabs', 'desc' => 'Settings to set permalink', 'id' => 'geodir_permalink_settings '),


    array('name' => __('Listing Detail Permalink Settings', GEODIRECTORY_TEXTDOMAIN),
        'type' => 'sectionstart',
        'desc' => '',
        'id' => 'geodir_permalink'),

    array(
        'name' => __('Add location in urls', GEODIRECTORY_TEXTDOMAIN),
        'desc' => __('Add location slug in listing urls', GEODIRECTORY_TEXTDOMAIN),
        'id' => 'geodir_add_location_url',
        'type' => 'checkbox',
        'std' => '1',
        'checkboxgroup' => 'start'
    ),

    array(
        'name' => __('Add full location in listing urls', GEODIRECTORY_TEXTDOMAIN),
        'desc' => __('Add full location info with country, region and city slug in listing urls', GEODIRECTORY_TEXTDOMAIN),
        'id' => 'geodir_show_location_url',
        'type' => 'radio',
        'value' => 'all',
        'std' => 'all',
        'radiogroup' => ''
    ),

    array(
        'name' => __('Add only city in listing urls', GEODIRECTORY_TEXTDOMAIN),
        'desc' => __('Add city slug in listing urls', GEODIRECTORY_TEXTDOMAIN),
        'id' => 'geodir_show_location_url',
        'type' => 'radio',
        'std' => 'all',
        'value' => 'city',
        'radiogroup' => 'end'
    ),


    array(
        'name' => __('Add category in listing urls', GEODIRECTORY_TEXTDOMAIN),
        'desc' => __('Add requested category slugs in listing urls', GEODIRECTORY_TEXTDOMAIN),
        'id' => 'geodir_add_categories_url',
        'type' => 'checkbox',
        'std' => '1',
    ),

    array(
        'name' => __('Listing url prefix', GEODIRECTORY_TEXTDOMAIN),
        'desc' => __('Listing prefix to show in url', GEODIRECTORY_TEXTDOMAIN),
        'id' => 'geodir_listing_prefix',
        'type' => 'text',
        'css' => 'min-width:300px;',
        'std' => 'places'
    ),

    array(
        'name' => __('Location url prefix', GEODIRECTORY_TEXTDOMAIN),
        'desc' => __('Depreciated, now uses the location page slug', GEODIRECTORY_TEXTDOMAIN),
        'id' => 'geodir_location_prefix',
        'type' => 'text',
        'css' => 'min-width:300px;',
        'std' => 'location' // Default value to show home top section
    ),

    array(
        'name' => __('Location and category url separator', GEODIRECTORY_TEXTDOMAIN),
        'desc' => __('Separator to show between location and category url slugs in listing urls', GEODIRECTORY_TEXTDOMAIN),
        'id' => 'geodir_listingurl_separator',
        'type' => 'text',
        'css' => 'min-width:300px;',
        'std' => 'C' // Default value to show home top section
    ),

    array(
        'name' => __('Listing detail url separator', GEODIRECTORY_TEXTDOMAIN),
        'desc' => __('Separator to show before listing slug in listing detail urls', GEODIRECTORY_TEXTDOMAIN),
        'id' => 'geodir_detailurl_separator',
        'type' => 'text',
        'css' => 'min-width:300px;',
        'std' => 'info' // Default value to show home top section
    ),


    array('type' => 'sectionend', 'id' => 'geodir_permalink'),

    array('name' => __('GeoDirectory Pages', GEODIRECTORY_TEXTDOMAIN),
        'type' => 'sectionstart',
        'desc' => '',
        'id' => 'geodir_pages'),

    array(
        'name' => __('Add listing page', GEODIRECTORY_TEXTDOMAIN),
        'desc' => __('Select the page to use for adding listings', GEODIRECTORY_TEXTDOMAIN),
        'id' => 'geodir_add_listing_page',
        'type' => 'single_select_page',
        'class' => 'chosen_select'
    ),

    array(
        'name' => __('Listing preview page', GEODIRECTORY_TEXTDOMAIN),
        'desc' => __('Select the page to use for listing preview', GEODIRECTORY_TEXTDOMAIN),
        'id' => 'geodir_preview_page',
        'type' => 'single_select_page',
        'class' => 'chosen_select'
    ),

    array(
        'name' => __('Listing success page', GEODIRECTORY_TEXTDOMAIN),
        'desc' => __('Select the page to use for listing success', GEODIRECTORY_TEXTDOMAIN),
        'id' => 'geodir_success_page',
        'type' => 'single_select_page',
        'class' => 'chosen_select'
    ),

    array(
        'name' => __('Location page', GEODIRECTORY_TEXTDOMAIN),
        'desc' => __('Select the page to use for locations', GEODIRECTORY_TEXTDOMAIN),
        'id' => 'geodir_location_page',
        'type' => 'single_select_page',
        'class' => 'chosen_select'
    ),


    array('type' => 'sectionend', 'id' => 'geodir_pages'),

    /* Listing Detail Permalink Settings End */


)); // End Design settings


//$_SESSION['geodir_settings']['permalink_settings'] = $geodir_settings['permalink_settings'] ;
