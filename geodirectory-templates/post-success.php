<?php
/**
 * Template for the success page after submitting a listing
 *
 * You can make most changes via hooks or see the link below for info on how to replace the template in your theme.
 *
 * @link http://docs.wpgeodirectory.com/customizing-geodirectory-templates/
 * @since 1.0.0
 * @package GeoDirectory
 * @deprecated 1.4.2 listing-success.php
 */
get_header();

do_action('geodir_before_main_content');

###### WRAPPER OPEN ######
/** This action is documented in geodirectory-templates/add-listing.php */
do_action('geodir_wrapper_open', 'success-page', 'geodir-wrapper', '');

###### TOP CONTENT ######
/** This action is documented in geodirectory-templates/add-listing.php */
do_action('geodir_top_content', 'success-page');
// action called before the main content
do_action('geodir_before_main_content', 'success-page');

###### MAIN CONTENT WRAPPERS OPEN ######
/** This action is documented in geodirectory-templates/add-listing.php */
do_action('geodir_wrapper_content_open', 'success-page', 'geodir-wrapper-content', '');


geodir_get_template_part('preview', 'success');


###### MAIN CONTENT WRAPPERS CLOSE ######
/** This action is documented in geodirectory-templates/add-listing.php */
do_action('geodir_article_close', 'success-page');
/** This action is documented in geodirectory-templates/add-listing.php */
do_action('geodir_after_main_content');

###### SIDEBAR ######
do_action('geodir_detail_sidebar');


# WRAPPER CLOSE ######	
/** This action is documented in geodirectory-templates/add-listing.php */
do_action('geodir_wrapper_close', 'success-page');
get_footer();   