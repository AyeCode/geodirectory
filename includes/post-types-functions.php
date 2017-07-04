<?php
/**
 * Post Types Functions
 *
 * All functions related to post types.
 *
 * @package GeoDirectory
 * @since   2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get post type options array.
 *
 * @since 2.0.0
 *
 * @param bool $plural_name True to get plural post type name. Default false.
 * @param bool $translated True to get translated name. Default false.
 * @return array GD post types options array.
 */
function geodir_post_type_options( $plural_name = false, $translated = false ) {
    $post_types = geodir_get_posttypes( 'object' );

    $options = array();
    if ( !empty( $post_types ) ) {
        foreach ( $post_types as $key => $post_type_obj ) {
            $name = $plural_name ? $post_type_obj->labels->name : $post_type_obj->labels->singular_name;
            if ( $translated ) {
                $name = __( $name, 'geodirectory' );
            }
            $options[ $key ] = $name;
        }
        
        if ( !empty( $options ) ) {
            $options = array_unique( $options );
        }
    }
    
    return $options;
}