<?php
/**
 * GeoDirectory Installation related functions.
 *
 * Plugin install script which adds default pages, taxonomies, and database tables.
 *
 * @since 1.0.0
 * @package GeoDirectory
 */

/**
 * Include custom database table related functions.
 *
 * @since 1.0.0
 */
include_once('admin_db_install.php');
/**
 * Activate GeoDirectory.
 *
 * @since 1.0.0
 * @package GeoDirectory
 */

function geodir_activation()
{

    geodir_install();
    add_action('wp_loaded', 'geodir_flush_activation');
}

/**
 * Remove rewrite rules and then recreate rewrite rules.
 *
 * @since 1.0.0
 * @package GeoDirectory
 */
function geodir_flush_activation()
{
    // Remove rewrite rules and then recreate rewrite rules.
    // flush late so everything is loaded
    flush_rewrite_rules();
}

/**
 * Install GeoDirectory.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global array $geodir_settings GeoDirectory settings array.
 */
function geodir_install()
{
    global $geodir_settings;

    /**
     * Called before the GD installation scripts have run.
     *
     * @since 1.0.0
     * @see 'geodir_installation_end'
     */
    do_action('geodir_installation_start');

    // Do install
    if (!get_option('geodir_default_data_installed')) {
        geodir_create_tables(); // in admin db install.php
        geodir_register_defaults(); // geodir_functions/ taxonomy_functions.php
        geodir_create_default_fields();
        //geodir_default_taxonomies();
        geodir_set_default_options();
        geodir_create_pages();
        geodir_set_default_widgets();
        gd_install_theme_compat();

        update_option('geodir_default_data_installed', 1);

    }

    if (!get_option('geodir_default_data_installed_1.2.8')) {
        //geodir_create_tables(); // in admin db install.php
        update_option('geodir_default_data_installed_1.2.8', 1);
    }

    geodir_installation_end();
    /**
     * Called after the GD installation scripts have run.
     *
     * @since 1.0.0
     * @see 'geodir_installation_start'
     */
    do_action('geodir_installation_end');


}


/**
 * Create GD pages.
 *
 * Creates pages that the plugin relies on, storing page id's in variables.
 *
 * @since 1.0.0
 * @package GeoDirectory
 */
function geodir_create_pages()
{

    //geodir_create_page( esc_sql( _x('home-map', 'page_slug', 'geodirectory') ), 'geodir_home_map_page', __('Home Map', 'geodirectory'), '',0,'publish' );
    geodir_create_page(esc_sql(_x('gd-home', 'page_slug', 'geodirectory')), 'geodir_home_page', __('GD Home page', 'geodirectory'), '');
    geodir_create_page(esc_sql(_x('add-listing', 'page_slug', 'geodirectory')), 'geodir_add_listing_page', __('Add Listing', 'geodirectory'), '');
    geodir_create_page(esc_sql(_x('listing-preview', 'page_slug', 'geodirectory')), 'geodir_preview_page', __('Listing Preview', 'geodirectory'), '');
    geodir_create_page(esc_sql(_x('listing-success', 'page_slug', 'geodirectory')), 'geodir_success_page', __('Listing Success', 'geodirectory'), '');
    geodir_create_page(esc_sql(_x('location', 'page_slug', 'geodirectory')), 'geodir_location_page', __('Location', 'geodirectory'), '');

    //New since 1.5.3
    geodir_create_page(esc_sql(_x('gd-info', 'page_slug', 'geodirectory')), 'geodir_info_page', __('Info', 'geodirectory'), '');
    geodir_create_page(esc_sql(_x('gd-login', 'page_slug', 'geodirectory')), 'geodir_login_page', __('Login', 'geodirectory'), '');


}




/**
 * Post installation actions.
 *
 * @since 1.0.0
 * @package GeoDirectory
 */
function geodir_installation_end()
{
    //update_option( "geodir_db_version", GEODIRECTORY_VERSION );

    update_option("geodir_installed", 1);
    update_option("geodir_installation_redirect", 1);
    update_option('skip_install_geodir_pages', 0);
}

/**
 * Default options.
 *
 * Adds the default options. Modify at your own risk.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global array $geodir_settings Geodirectory settings array.
 */
function geodir_set_default_options()
{
    global $geodir_settings;
    /**
     * Contains settings array for general tab.
     *
     * @since 1.0.0
     * @package GeoDirectory
     */
    include_once("option-pages/general_settings_array.php");
    /**
     * Contains settings array for design tab.
     *
     * @since 1.0.0
     * @package GeoDirectory
     */
    include_once("option-pages/design_settings_array.php");
    /**
     * Contains settings array for notifications tab.
     *
     * @since 1.0.0
     * @package GeoDirectory
     */
    include_once("option-pages/notifications_settings_array.php");
    /**
     * Contains settings array for permalink tab.
     *
     * @since 1.0.0
     * @package GeoDirectory
     */
    include_once("option-pages/permalink_settings_array.php");
    /**
     * Contains settings array for title / meta tab.
     *
     * @since 1.5.4
     * @package GeoDirectory
     */
    include_once("option-pages/title_meta_settings_array.php");
    foreach ($geodir_settings as $value) {
        geodir_update_options($value, true);
    }

}


/**
 * Set sidebar widgets.
 *
 * @since 1.0.0
 * @package GeoDirectory
 **/
function geodir_set_default_widgets()
{

    $widget_option_list = array();
    $widgetinfo = array();
    $sidebarvalue_array = array();
    $sidebars_widgets = array();

    /*===========================*/
    /*  Widgets ON HOME PAGE     */
    /*===========================*/

    $widget_option_list['geodir_home_top'] =
        array('popular_post_category' => array("title" => __('Popular Categories', 'geodirectory')),
            'geodir_map_v3_home_map' => array("autozoom" => 1, "width" => '100%', "heigh" => '425'),
            'geodir_advance_search' => array());

    $widget_option_list['geodir_home_content'] =
        array('popular_post_view' => array("title" => __('Popular Places', 'geodirectory'), "layout" => 'list', "add_location_filter" => '1'));

    $widget_option_list['geodir_home_right'] =
        array('geodir_loginbox' => array("title" => __('My Dashboard', 'geodirectory')),
            'popular_post_view' => array("title" => __('Latest Places', 'geodirectory'), "add_location_filter" => '1'));

    /*===========================*/
    /*  Widgets ON LISTING PAGE     */
    /*===========================*/

    $widget_option_list['geodir_listing_top'] =
        array('popular_post_category' => array("title" => __('Popular Categories', 'geodirectory')),
            'geodir_advance_search' => array());

    $widget_option_list['geodir_listing_right_sidebar'] =
        array('geodir_loginbox' => array("title" => __('My Dashboard', 'geodirectory')),
            'geodir_map_v3_listing_map' => array("autozoom" => 1, "sticky" => 1),
            'popular_post_view' => array("title" => __('Latest Places', 'geodirectory'), "add_location_filter" => '1'));


    /*===========================*/
    /*  Widgets ON SEARCH PAGE     */
    /*===========================*/

    $widget_option_list['geodir_search_top'] =
        array('popular_post_category' => array("title" => __('Popular Categories', 'geodirectory')),
            'geodir_advance_search' => array());

    $widget_option_list['geodir_search_right_sidebar'] =
        array('geodir_loginbox' => array("title" => __('My Dashboard', 'geodirectory')),
            'geodir_map_v3_listing_map' => array("autozoom" => 1, "sticky" => 1),
            'popular_post_view' => array("title" => __('Latest Places', 'geodirectory'), "add_location_filter" => '1'));

    /*===========================*/
    /*  Widgets ON DETAIL/SINGLE PAGE     */
    /*===========================*/

    $widget_option_list['geodir_detail_sidebar'] =
        array('geodir_loginbox' => array("title" => __('My Dashboard', 'geodirectory')),
            'geodir_map_v3_listing_map' => array("autozoom" => 1, "sticky" => 1),
            'popular_post_view' => array("title" => __('Latest Places', 'geodirectory'), "add_location_filter" => '1'));


    /*===========================*/
    /*  Widgets ON AUTHOR PAGE     */
    /*===========================*/


    $widget_option_list['geodir_author_right_sidebar'] =
        array('geodir_loginbox' => array("title" => __('My Dashboard', 'geodirectory')));


    $sidebars_widgets = get_option('sidebars_widgets');

    foreach ($widget_option_list as $key => $widget_options) {

        foreach ($widget_options as $key2 => $widget_options_obj) {
            $widgetid = 'widget_' . $key2;

            $widgetinfo[$widgetid][] = $widget_options_obj;

            $sidebarvalue_array[$key][] = $key2 . "-" . (count($widgetinfo[$widgetid]));

            $widget_update[$widgetid][count($widgetinfo[$widgetid])] = $widget_options_obj;

        }

        if (!empty($sidebarvalue_array[$key])) {

            $sidebars_widgets = get_option('sidebars_widgets');
            $sidebars_widgets[$key] = $sidebarvalue_array[$key];
            update_option('sidebars_widgets', $sidebars_widgets);

            foreach ($widget_update as $key => $value) {

                update_option($key, $value);

            }

        }

    }


}




/// GEODIRECTORY INSTALLATION FUNCTIONS ENDS ////
