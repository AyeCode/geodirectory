<?php
/**
 * Template for add listings page
 *
 * You can make most changes via hooks or see the link below for info on how to replace the template in your theme.
 *
 * @link http://docs.wpgeodirectory.com/customizing-geodirectory-templates/
 * @since 1.0.0
 * @package GeoDirectory
 */
 
global $gd_session;

if (!isset($_REQUEST['backandedit'])) {
    $gd_session->un_set('listing');
}
// call header
get_header();

###### WRAPPER OPEN ######

/**
 * Outputs the opening HTML wrappers for most template pages.
 *
 * This adds the opening html tags to the primary div, this required the closing tag below :: ($type='',$id='',$class='').
 *
 * @since 1.1.0
 * @param string $type Page type.
 * @param string $id The id of the HTML element.
 * @param string $class The class of the HTML element.
 * @see 'geodir_wrapper_close'
 */
do_action('geodir_wrapper_open', 'add-listing-page', 'geodir-wrapper', '');

###### TOP CONTENT ######

/**
 * Called before the main content and the page specific content.
 *
 * @since 1.1.0
 * @param string $type Page type.
 */
do_action('geodir_top_content', 'add-listing-page');

/**
 * Calls the top section widget area and the breadcrumbs on the add listing page.
 *
 * @since 1.1.0
 */
do_action('geodir_add_listing_before_main_content');

/**
 * Called before the main content of a template page.
 *
 * @since 1.1.0
 * @see 'geodir_after_main_content'
 */
do_action('geodir_before_main_content', 'add-listing-page');

###### MAIN CONTENT WRAPPERS OPEN ######
/**
 * Outputs the opening HTML wrappers for the content.
 *
 * This adds the opening html tags to the content div, this required the closing tag below :: ($type='',$id='',$class='')
 *
 * @since 1.1.0
 * @param string $type Page type.
 * @param string $id The id of the HTML element.
 * @param string $class The class of the HTML element.
 * @see 'geodir_wrapper_content_close'
 */
do_action('geodir_wrapper_content_open', 'add-listing-page', 'geodir-wrapper-content', '');


###### MAIN CONTENT ######

/**
 * Adds the title to the add listing page.
 *
 * This action adds the title to the add listing page.
 *
 * @since 1.1.0
 */
do_action('geodir_add_listing_page_title');
/**
 * Called before the add listing page form. This adds the mandatory messages.
 *
 * @since 1.1.0
 */
do_action('geodir_add_listing_page_mandatory');

/** This action is documented in geodirectory-templates/geodir-home.php */
do_action('geodir_add_page_content', 'before', 'add-listing-page');

/**
 * Adds the add listing form.
 *
 * @since 1.1.0
 */
do_action('geodir_add_listing_form');

/** This action is documented in geodirectory-templates/geodir-home.php */
do_action('geodir_add_page_content', 'after', 'add-listing-page');

###### MAIN CONTENT WRAPPERS CLOSE ######

/**
 * Called after the main content of a template page.
 *
 * @see 'geodir_before_main_content'
 * @since 1.1.0
 */
do_action('geodir_after_main_content');

/**
 * Outputs the closing HTML wrappers for the content.
 *
 * This adds the closing html tags to the wrapper_content div :: ($type='')
 *
 * @since 1.1.0
 * @param string $type Page type.
 * @see 'geodir_wrapper_content_open'
 */
do_action('geodir_wrapper_content_close', 'add-listing-page');


###### SIDEBAR ######
/**
 * This action adds the sidebar to the add listing page template.
 *
 * @since 1.1.0
 */
do_action('geodir_add_listing_sidebar');

###### WRAPPER CLOSE ######	

/**
 * Outputs the closing HTML wrappers for most template pages.
 *
 * This adds the closing html tags to the wrapper div :: ($type='')
 *
 * @since 1.1.0
 * @param string $type Page type.
 * @see 'geodir_wrapper_open'
 */
do_action('geodir_wrapper_close', 'add-listing-page');

// call footer
get_footer();