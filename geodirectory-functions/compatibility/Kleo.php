<?php
/**
 * Kleo theme compatibility functions.
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

/**
 * Fix search returns all the posts for Kleo theme.
 *
 * Kleo sets the search page to use whether post or page, we need it to be 'any'.
 *
 * @since 1.0.0
 *
 * @param object $query Current query object.
 * @return object Modified query object.
 */
function geodir_kleo_search_filter( $query ) {
    if ( !empty( $query->is_search ) && geodir_is_page('search') && is_search() ) {
        $query->set( 'post_type', 'any' );
    }
    return $query;
}
if ( !is_admin() ) {
    add_filter( 'pre_get_posts', 'geodir_kleo_search_filter', 11 );
}