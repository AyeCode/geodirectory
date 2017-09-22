<?php
/**
 * Content Wrappers.
 */
add_action( 'geodir_before_main_content', 'geodir_output_content_wrapper_start', 10 );
add_action( 'geodir_after_main_content', 'geodir_output_content_wrapper_end', 10 );

/**
 * Breadcrumbs
 */
add_action( 'geodir_before_main_content', 'geodir_output_breadcrumb', 9.5, 0 );

add_filter( 'geodir_listing_classes', 'geodir_listing_old_classes', 10, 2 );
add_filter( 'geodir_listing_attrs', 'geodir_listing_old_attrs', 10, 2 );
add_filter( 'geodir_listing_inner_classes', 'geodir_listing_inner_old_classes', 10, 2 );

add_action( 'template_redirect', 'geodir_template_redirect' );
add_action( 'geodir_add_listing_form_start', 'geodir_action_add_listing_page_mandatory', -10, 3 );
add_action( 'geodir_before_add_listing_form', 'geodir_add_listing_form_wrap_start', 100.5, 3 );
add_action( 'geodir_after_add_listing_form', 'geodir_add_listing_form_wrap_end', 100.5, 3 );
//add_filter( 'the_content', 'geodir_replace_the_content' );