<?php
/**
 * Helper functions, this file contains functions made to make a developer's life easier.
 *
 * @since 1.4.6
 * @package GeoDirectory
 */

/**
 * Get the page ID of the add listing page.
 *
 * @package Geodirectory
 * @since 1.4.6
 * @return int|null Return the page ID if present or null if not.
 */
function geodir_add_listing_page_id(){
    $gd_page_id = get_option('geodir_add_listing_page');

    if (function_exists('icl_object_id')) {
        $gd_page_id =  icl_object_id($gd_page_id, 'page', true);
    }

    return $gd_page_id;
}

/**
 * Get the page ID of the add listing preview page.
 *
 * @package Geodirectory
 * @since 1.4.6
 * @return int|null Return the page ID if present or null if not.
 */
function geodir_preview_page_id(){
    $gd_page_id = get_option('geodir_preview_page');

    if (function_exists('icl_object_id')) {
        $gd_page_id =  icl_object_id($gd_page_id, 'page', true);
    }

    return $gd_page_id;
}

/**
 * Get the page ID of the add listing success page.
 *
 * @package Geodirectory
 * @since 1.4.6
 * @return int|null Return the page ID if present or null if not.
 */
function geodir_success_page_id(){
    $gd_page_id = get_option('geodir_success_page');

    if (function_exists('icl_object_id')) {
        $gd_page_id =  icl_object_id($gd_page_id, 'page', true);
    }

    return $gd_page_id;
}

/**
 * Get the page ID of the add location page.
 *
 * @package Geodirectory
 * @since 1.4.6
 * @return int|null Return the page ID if present or null if not.
 */
function geodir_location_page_id(){
    $gd_page_id = get_option('geodir_location_page');

    if (function_exists('icl_object_id')) {
        $gd_page_id =  icl_object_id($gd_page_id, 'page', true);
    }

    return $gd_page_id;
}