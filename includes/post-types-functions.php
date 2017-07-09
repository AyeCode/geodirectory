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

/**
 * Check given post type is GD post type or not.
 *
 * @since 2.0.0
 *
 * @param string $post_type The post type.
 * @return boll True if given post type is GD post type, otherwise False.
 */
function geodir_is_gd_post_type( $post_type ) {
    global $gd_is_post_type;
    
    if ( empty( $post_type ) ) {
        return false;
    }
    
    if ( !empty( $gd_is_post_type ) && !empty( $gd_is_post_type[ $post_type ] ) ) {
        return true;
    }
    
    $gd_posttypes = geodir_get_posttypes();
    
    if ( !empty( $gd_posttypes ) && in_array( $post_type, $gd_posttypes ) ) {
        if ( !is_array( $gd_is_post_type ) ) {
            $gd_is_post_type = array();
        }
        
        $gd_is_post_type[ $post_type ] = true;
        
        return true;
    }
    
    return false;
}

function geodir_post_type_object( $post_type ) {
    if ( geodir_is_gd_post_type( $post_type ) ) {
        $post_types = geodir_get_posttypes( 'object' );
        
        $post_type_obj = !empty( $post_types->{$post_type} ) ? $post_types->{$post_type} : NULL;
    } else {
        $post_type_obj = get_post_type_object( $post_type );
    }
    
    return $post_type_obj;
}

function geodir_post_type_name( $post_type, $translated = false ) {
    $post_type_obj = geodir_post_type_object( $post_type );
    
    if ( !( !empty( $post_type_obj ) && !empty( $post_type_obj->labels->name ) ) ) {
        return $post_type;
    }
    
    $name = $post_type_obj->labels->name;
    if ( $translated ) {
        $name = __( $name, 'geodirectory' );
    }
    
    return apply_filters( 'geodir_post_type_name', $name, $post_type, $translated );
}

function geodir_post_type_singular_name( $post_type, $translated = false ) {
    $post_type_obj = geodir_post_type_object( $post_type );
    
    if ( !( !empty( $post_type_obj ) && !empty( $post_type_obj->labels->singular_name ) ) ) {
        return $post_type;
    }
    
    $singular_name = $post_type_obj->labels->singular_name;
    if ( $translated ) {
        $singular_name = __( $singular_name, 'geodirectory' );
    }
    
    return apply_filters( 'geodir_post_type_singular_name', $singular_name, $post_type, $translated );
}