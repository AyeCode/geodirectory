<?php


add_filter( 'geodir_listing_classes', 'geodir_listing_old_classes', 10, 2 );
add_filter( 'geodir_listing_attrs', 'geodir_listing_old_attrs', 10, 2 );
add_filter( 'geodir_listing_inner_classes', 'geodir_listing_inner_old_classes', 10, 2 );

add_action( 'geodir_add_listing_form_start', 'geodir_action_add_listing_page_mandatory', -10, 3 );
add_action( 'geodir_before_add_listing_form', 'geodir_add_listing_form_wrap_start', 100.5, 3 );
add_action( 'geodir_after_add_listing_form', 'geodir_add_listing_form_wrap_end', 100.5, 3 );