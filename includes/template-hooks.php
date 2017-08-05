<?php
/**
 * Content Wrappers.
 */
add_action( 'geodir_before_main_content', 'geodir_output_content_wrapper', 10 );
add_action( 'geodir_after_main_content', 'geodir_output_content_wrapper_end', 10 );

/**
 * Breadcrumbs
 */
add_action( 'geodir_before_main_content', 'geodir_output_breadcrumb', 20, 0 );

add_filter( 'geodir_listing_classes', 'geodir_listing_old_classes', 10, 2 );
add_filter( 'geodir_listing_attrs', 'geodir_listing_old_attrs', 10, 2 );
add_filter( 'geodir_listing_inner_classes', 'geodir_listing_inner_old_classes', 10, 2 );
