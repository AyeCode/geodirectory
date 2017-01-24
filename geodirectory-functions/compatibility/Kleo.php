<?php
/**
 * Kelo theme compatibility functions.
 *
 * This file lets the GeoDirectory Plugin use the X theme HTML wrappers to fit and work perfectly.
 *
 * @since 1.0.0
 * @package GeoDirectory
 */

// Page titles translatable CPT names
function geodir_kelo_title_translation( $args) {
    if(function_exists('geodir_is_geodir_page') && geodir_is_page('preview') ){
        $args['title'] = __(stripslashes_deep(esc_html($_POST['post_title'])),'geodirectory');
    }elseif(function_exists('geodir_is_geodir_page')){
        $args['title'] = __($args['title'],'geodirectory');
    }

    return $args;
}
add_filter( 'kleo_title_args', 'geodir_kelo_title_translation', 10, 1 );


