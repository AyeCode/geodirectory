<?php
/**
 * Content Wrappers.
 */
add_action( 'geodir_before_main_content', 'geodir_output_content_wrapper', 10 );
add_action( 'geodir_after_main_content', 'geodir_output_content_wrapper_end', 10 );
