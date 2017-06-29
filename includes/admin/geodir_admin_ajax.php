<?php
/**
 * Admin ajax.
 *
 * @since 1.0.0
 * @package GeoDirectory
 */

if (isset($_REQUEST['create_field'])) {
	/**
	 * Includes file to create custom fields.
	 *
	 * @since 1.0.0
	 */
	include_once(geodir_plugin_path() . '/geodirectory-admin/option-pages/create_field.php');
    gd_die();
}


if (isset($_REQUEST['ajax_action']) && $_REQUEST['ajax_action'] != '') {
    switch ($_REQUEST['ajax_action']):
        case 'get_cat_dl':
            geodir_get_categories_dl($_REQUEST['post_type'], $_REQUEST['selected'], false, true);
            break;
    endswitch;
}