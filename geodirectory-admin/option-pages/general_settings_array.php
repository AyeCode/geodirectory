<?php
/**
 * General tab settings.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global array $geodir_settings Geodirectory settings array.
 */
global $geodir_settings;

/**
 * Filter GD general settings array.
 *
 * @since 1.0.0
 * @package GeoDirectory
 */
$general_options = apply_filters('geodir_general_options', array(

    array('name' => __('General', 'geodirectory'), 'type' => 'title', 'desc' => '', 'id' => 'general_options'),

    array('name' => __('General Options', 'geodirectory'), 'type' => 'sectionstart', 'id' => 'general_options'),

    array(
        'name' => __('Sender name', 'geodirectory'),
        'desc' => __('(Name that will be shown as email sender when users receive emails from this site)', 'geodirectory'),
        'id' => 'site_email_name',
        'type' => 'text',
        'css' => 'min-width:300px;',
        'std' => get_bloginfo('name') // Default value for the page title - changed in settings
    ),

    array(
        'name' => __('Email address', 'geodirectory'),
        'desc' => __('(Emails to users will be sent via this mail ID)', 'geodirectory'),
        'id' => 'site_email',
        'type' => 'text',
        'css' => 'min-width:300px;',
        'std' => get_bloginfo('admin_email') // Default value for the page title - changed in settings
    ),
    array(
        'name' => __('Allow user to see wp-admin area', 'geodirectory'),
        'desc' => __('Yes', 'geodirectory'),
        'id' => 'geodir_allow_wpadmin',
        'std' => '1',
        'type' => 'radio',
        'value' => '1',
        'radiogroup' => 'start'
    ),
    array(
        'name' => __('Allow user to see wp-admin area', 'geodirectory'),
        'desc' => __('No', 'geodirectory'),
        'id' => 'geodir_allow_wpadmin',
        'std' => '0',
        'type' => 'radio',
        'value' => '0',
        'radiogroup' => 'end'
    ),

    array(
        'name' => __('Allow user to choose own password', 'geodirectory'),
        'desc' => __('Yes', 'geodirectory'),
        'id' => 'geodir_allow_cpass',
        'std' => '1',
        'type' => 'radio',
        'value' => '1',
        'radiogroup' => 'start'
    ),
    array(
        'name' => __('Allow user to choose own password', 'geodirectory'),
        'desc' => __('No', 'geodirectory'),
        'id' => 'geodir_allow_cpass',
        'std' => '0',
        'type' => 'radio',
        'value' => '0',
        'radiogroup' => 'end'
    ),
    array(
        'name' => __('Disable rating on comments', 'geodirectory'),
        'desc' => __('Disable rating without disabling comments on listings', 'geodirectory'),
        'id' => 'geodir_disable_rating',
        'type' => 'checkbox',
        'std' => '0'
    ),
    array(
        'name' => __('User deleted posts go to trash', 'geodirectory'),
        'desc' => __('If checked a user deleted post will go to trash, otherwise it will be permanently deleted', 'geodirectory'),
        'id' => 'geodir_disable_perm_delete',
        'type' => 'checkbox',
        'std' => '1'
    ),
    array(
        'name' => __('Max upload file size(in mb)', 'geodirectory'),
        'desc' => __('(Maximum upload file size in MB, 1 MB = 1024 KB. Must be greater then 0(ZERO), for ex: 2. This setting will overwrite the max upload file size limit in image/file upload & import listings for entire GeoDirectory core + GeoDirectory plugins.)', 'geodirectory'),
        'id' => 'geodir_upload_max_filesize',
        'type' => 'text',
        'css' => 'min-width:300px;',
        'std' => '2'
    ),
    array('type' => 'sectionend', 'id' => 'general_options'),

));/* General Options End*/

/**
 * Filter GD Google Analytic Settings array.
 *
 * @since 1.0.0
 * @package GeoDirectory
 */
$google_analytic_settings = apply_filters('geodir_google_analytic_settings', array(

    array('name' => __('Google Analytics', 'geodirectory'), 'type' => 'title', 'desc' => '', 'id' => 'google_analytic_settings'),

    array('name' => __('Google Analytic Settings', 'geodirectory'), 'type' => 'sectionstart', 'id' => 'google_analytic_settings'),



    array(
        'name' => __('Show business owner google analytics stats?', 'geodirectory'),
        'desc' => __('Yes', 'geodirectory'),
        'id' => 'geodir_ga_stats',
        'std' => '0',
        'type' => 'radio',
        'value' => '1',
        'radiogroup' => 'start'
    ),
    array(
        'name' => __('Show business owner Google Analytics stats?', 'geodirectory'),
        'desc' => __('No', 'geodirectory'),
        'id' => 'geodir_ga_stats',
        'std' => '1',
        'type' => 'radio',
        'value' => '0',
        'radiogroup' => 'end'
    ),

    array(
        'name' => __('Google analytics "Profile ID(ie: ga:12345678)?', 'geodirectory') . ' ' .
            '<a target="_blank" href="https://docs.wpgeodirectory.com/gd-core-plugin-google-analytics/">' . __('help', 'geodirectory') . '</a>',
        'desc' => '',
        'id' => 'geodir_ga_id',
        'type' => 'text',
        'css' => 'min-width:300px;',
        'std' => '' // Default value for the page title - changed in settings
    ),

    array(
        'name' => __('Client ID', 'geodirectory') . ' ' .
            '<a target="_blank" href="https://docs.wpgeodirectory.com/gd-core-plugin-google-analytics/">' . __('help', 'geodirectory') . '</a>',
        'desc' => '',
        'id' => 'geodir_ga_client_id',
        'type' => 'text',
        'css' => 'min-width:300px;',
        'std' => '' // Default value for the page title - changed in settings
    ),

    array(
        'name' => __('Client secret', 'geodirectory') . ' ' .
            '<a target="_blank" href="https://docs.wpgeodirectory.com/gd-core-plugin-google-analytics/">' . __('help', 'geodirectory') . '</a>',
        'desc' => '',
        'id' => 'geodir_ga_client_secret',
        'type' => 'password',
        'css' => 'min-width:300px;',
        'std' => '' // Default value for the page title - changed in settings
    ),

    array(
        'name' => __('Google analytics access', 'geodirectory'),
        'desc' => '',
        'id' => 'geodir_ga_token',
        'type' => 'google_analytics',
        'css' => 'min-width:300px;',
        'std' => '' // Default value for the page title - changed in settings
    ),


    array(
        'name' => __('Google analytics tracking code', 'geodirectory'),
        'desc' => '',
        'id' => 'geodir_ga_tracking_code',
        'type' => 'textarea',
        'css' => 'min-width:300px;',
        'std' => '' // Default value for the page title - changed in settings
    ),
    array(
        'name' => __('Auto refresh active users?', 'geodirectory'),
        'desc' => __('If ticked it uses the auto refresh time below, if not it never refreshes unless the refresh button is clicked.', 'geodirectory'),
        'id' => 'geodir_ga_auto_refresh',
        'type' => 'checkbox',
        'std' => '0'
    ),
    array(
        'name' => __('Time interval for auto refresh active users', 'geodirectory'),
        'desc' => __('Time interval in seconds to auto refresh active users. The active users will be auto refreshed after this time interval. Leave blank or use 0(zero) to disable auto refresh. Default: 5', 'geodirectory'),
        'id' => 'geodir_ga_refresh_time',
        'type' => 'text',
        'std' => '5'
    ),

    array('type' => 'sectionend', 'id' => 'google_analytic_settings'),

)); // google_analytic_settings End

/**
 * Filter GD search Settings array.
 *
 * @since 1.0.0
 * @package GeoDirectory
 */
$search_settings = apply_filters('geodir_search_settings', array(

    array('name' => __('Search', 'geodirectory'), 'type' => 'title', 'desc' => '', 'id' => 'search_settings'),

    array('name' => __('Search Settings', 'geodirectory'), 'type' => 'sectionstart', 'id' => 'search_settings'),

    array(
        'name' => __('Limit squared distance area to X miles (helps improve search speed)', 'geodirectory'),
        'desc' => __('Enter whole number only ex. 40 (Tokyo is largest city in the world @40 sq miles) LEAVE BLANK FOR NO DISTANCE LIMIT', 'geodirectory'),
        'id' => 'geodir_search_dist',
        'type' => 'text',
        'css' => 'min-width:300px;',
        'std' => '40' // Default value for the page title - changed in settings
    ),

    array(
        'name' => __('Show search distances in miles or km', 'geodirectory'),
        'desc' => __('Miles', 'geodirectory'),
        'id' => 'geodir_search_dist_1',
        'std' => 'miles',
        'type' => 'radio',
        'value' => 'miles',
        'radiogroup' => 'start'
    ),
    array(
        'name' => __('Show search distances in miles or km', 'geodirectory'),
        'desc' => __('Kilometers', 'geodirectory'),
        'id' => 'geodir_search_dist_1',
        'std' => 'miles',
        'type' => 'radio',
        'value' => 'km',
        'radiogroup' => 'end'
    ),

    array(
        'name' => __('If distance is less than 0.01 show distance in meters or feet', 'geodirectory'),
        'desc' => __('Meters', 'geodirectory'),
        'id' => 'geodir_search_dist_2',
        'std' => 'meters',
        'type' => 'radio',
        'value' => 'meters',
        'radiogroup' => 'start'
    ),

    array(
        'name' => __('If distance is less than 0.01 show distance in meters or feet', 'geodirectory'),
        'desc' => __('Feet', 'geodirectory'),
        'id' => 'geodir_search_dist_2',
        'std' => 'meters',
        'type' => 'radio',
        'value' => 'feet',
        'radiogroup' => 'end'
    ),

    array(
        'name' => __('Add location specific text to (Near) search for Google', 'geodirectory'),
        'desc' => __('This is usefull if your directory is limted to one location such as: New York or Australia (this setting should be blank if using default country, regions etc with multilocation addon as it will automatically add them)', 'geodirectory'),
        'id' => 'geodir_search_near_addition',
        'type' => 'text',
        'css' => 'min-width:300px;',
        'std' => ''
    ),
    array(
        'name' => __('Individual word search limit', 'geodirectory'),
        'desc' => __('With this option you can limit individual words being searched for, for example searching for `Jo Brown` would return results with words like `Jones`, you can exclude these types of small character words if you wish.', 'geodirectory'),
        'id' => 'geodir_search_word_limit',
        'css' => 'min-width:300px;',
        'std' => 'gridview_onehalf',
        'type' => 'select',
        'class' => 'chosen_select',
        'options' => array_unique(array(
            '0' => __('Disabled', 'geodirectory'),
            '1' => __('1 Character words excluded', 'geodirectory'),
            '2' => __('2 Character words and less excluded', 'geodirectory'),
            '3' => __('3 Character words and less excluded', 'geodirectory'),
        ))
    ),


    array('type' => 'sectionend', 'id' => 'search_settings'),

)); //search_settings End

/**
 * Filter GD Dummy data Settings array.
 *
 * @since 1.0.0
 * @package GeoDirectory
 */
$dummy_data_settings = apply_filters('geodir_dummy_data_settings', array(

    array('name' => __('Dummy Data', 'geodirectory'), 'type' => 'title', 'desc' => '', 'id' => 'dummy_data_settings'),

    array(
        'name' => '',
        'desc' => '',
        'id' => 'geodir_dummy_data_installer',
        'type' => 'dummy_installer',
        'css' => 'min-width:300px;',
        'std' => '40' // Default value for the page title - changed in settings
    ),
    array('type' => 'sectionend', 'id' => 'geodir_dummy_data_settings'),

)); //dummy_data_settings End

$general_settings = array_merge($general_options, $google_analytic_settings, $search_settings, $dummy_data_settings);

/**
 * Filter GD General Settings array.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @param array $general_settings General settings array.
 */
$geodir_settings['general_settings'] = apply_filters('geodir_general_settings', $general_settings);
