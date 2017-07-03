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
    array('name' => __('Permalink', 'geodirectory'), 'type' => 'no_tabs', 'desc' => 'Settings to set permalink', 'id' => 'geodir_permalink_settings '),


    array('name' => __('Listing Detail Permalink Settings', 'geodirectory'),
        'type' => 'sectionstart',
        'desc' => '',
        'id' => 'geodir_permalink'),

    array(
        'name' => __('Add location in urls', 'geodirectory'),
        'desc' => __('Add location slug in listing urls', 'geodirectory'),
        'id' => 'geodir_add_location_url',
        'type' => 'checkbox',
        'std' => '1',
        'checkboxgroup' => 'start'
    ),

    array(
        'name' => __('Add full location in listing urls', 'geodirectory'),
        'desc' => __('Add full location info with country, region and city slug in listing urls', 'geodirectory'),
        'id' => 'geodir_show_location_url',
        'type' => 'radio',
        'value' => 'all',
        'std' => 'all',
        'radiogroup' => ''
    ),

	array(
        'name' => __('Add country and city slug in listing urls', 'geodirectory'),
        'desc' => __('Add country and city slug in listing urls (/country/city/)', 'geodirectory'),
        'id' => 'geodir_show_location_url',
        'type' => 'radio',
        'std' => 'all',
        'value' => 'country_city',
        'radiogroup' => ''
    ),
	array(
        'name' => __('Add region and city slug in listing urls', 'geodirectory'),
        'desc' => __('Add region and city slug in listing urls (/region/city/)', 'geodirectory'),
        'id' => 'geodir_show_location_url',
        'type' => 'radio',
        'std' => 'all',
        'value' => 'region_city',
        'radiogroup' => ''
    ),
    array(
        'name' => __('Add only city in listing urls', 'geodirectory'),
        'desc' => __('Add city slug in listing urls', 'geodirectory'),
        'id' => 'geodir_show_location_url',
        'type' => 'radio',
        'std' => 'all',
        'value' => 'city',
        'radiogroup' => 'end'
    ),



    array(
        'name' => __('Add category in listing urls', 'geodirectory'),
        'desc' => __('Add requested category slugs in listing urls', 'geodirectory'),
        'id' => 'geodir_add_categories_url',
        'type' => 'checkbox',
        'std' => '1',
    ),

    array(
        'name' => __('Listing url prefix', 'geodirectory'),
        'desc' => __('Listing prefix to show in url', 'geodirectory'),
        'id' => 'geodir_listing_prefix',
        'type' => 'text',
        'css' => 'min-width:300px;',
        'std' => 'places'
    ),

    array(
        'name' => __('Location url prefix', 'geodirectory'),
        'desc' => __('Depreciated, now uses the location page slug', 'geodirectory'),
        'id' => 'geodir_location_prefix',
        'type' => 'text',
        'css' => 'min-width:300px;',
        'std' => 'location' // Default value to show home top section
    ),

    array(
        'name' => __('Location and category url separator', 'geodirectory'),
        'desc' => __('Separator to show between location and category url slugs in listing urls', 'geodirectory'),
        'id' => 'geodir_listingurl_separator',
        'type' => 'text',
        'css' => 'min-width:300px;',
        'std' => 'C' // Default value to show home top section
    ),

    array(
        'name' => __('Listing detail url separator', 'geodirectory'),
        'desc' => __('Separator to show before listing slug in listing detail urls', 'geodirectory'),
        'id' => 'geodir_detailurl_separator',
        'type' => 'text',
        'css' => 'min-width:300px;',
        'std' => 'info' // Default value to show home top section
    ),


    array('type' => 'sectionend', 'id' => 'geodir_permalink'),

    array('name' => __('GeoDirectory Pages', 'geodirectory'),
        'type' => 'sectionstart',
        'desc' => '',
        'id' => 'geodir_pages'),

    array(
        'name' => __('GD Home page', 'geodirectory'),
        'desc' => __('Select the page to use for the GD homepage (you must also set this page in Settings>Reading>Front page for it to work)', 'geodirectory'),
        'id' => 'geodir_home_page',
        'type' => 'single_select_page',
        'class' => 'chosen_select'
    ),

    array(
        'name' => __('Add listing page', 'geodirectory'),
        'desc' => __('Select the page to use for adding listings', 'geodirectory'),
        'id' => 'geodir_add_listing_page',
        'type' => 'single_select_page',
        'class' => 'chosen_select'
    ),

    array(
        'name' => __('Listing preview page', 'geodirectory'),
        'desc' => __('Select the page to use for listing preview', 'geodirectory'),
        'id' => 'geodir_preview_page',
        'type' => 'single_select_page',
        'class' => 'chosen_select'
    ),

    array(
        'name' => __('Listing success page', 'geodirectory'),
        'desc' => __('Select the page to use for listing success', 'geodirectory'),
        'id' => 'geodir_success_page',
        'type' => 'single_select_page',
        'class' => 'chosen_select'
    ),

    array(
        'name' => __('Location page', 'geodirectory'),
        'desc' => __('Select the page to use for locations', 'geodirectory'),
        'id' => 'geodir_location_page',
        'type' => 'single_select_page',
        'class' => 'chosen_select'
    ),

    array(
        'name' => __('Terms and Conditions page', 'geodirectory'),
        'desc' => __('Select the page to use for Terms and Conditions (if enabled)', 'geodirectory'),
        'id' => 'geodir_term_condition_page',
        'type' => 'single_select_page',
        'class' => 'chosen_select'
    ),

    array(
        'name' => __('Info page', 'geodirectory'),
        'desc' => __('Select the page to use for Gd general Info', 'geodirectory'),
        'id' => 'geodir_info_page',
        'type' => 'single_select_page',
        'class' => 'chosen_select'
    ),

    array(
        'name' => __('Login page', 'geodirectory'),
        'desc' => __('Select the page to use for Login / Register', 'geodirectory'),
        'id' => 'geodir_login_page',
        'type' => 'single_select_page',
        'class' => 'chosen_select'
    ),


    array('type' => 'sectionend', 'id' => 'geodir_pages'),

    /* Listing Detail Permalink Settings End */


)); // End Design settings
