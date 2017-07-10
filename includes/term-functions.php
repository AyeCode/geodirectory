<?php
/**
 * Terms Functions
 *
 * All functions related to terms(categories/tags).
 *
 * @package GeoDirectory
 * @since   2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

function geodir_get_cat_icon( $term_id, $full_path = false, $default = false ) {
    $term_meta = get_term_meta( $term_id, 'ct_cat_icon', true );
    
    $cat_icon = is_array( $term_meta ) && !empty( $term_meta['src'] ) ? $term_meta['src'] : '';
    
    if ( !$cat_icon && $default ) {
        $cat_icon = geodir_get_option( 'geodir_default_marker_icon' );
    }
    
    if ( $cat_icon && $full_path && strpos( $cat_icon, 'http://' ) !== 0 && strpos( $cat_icon, 'https://' ) !== 0 ) {
        $upload_dir = wp_upload_dir();
        $cat_icon = $upload_dir['baseurl'] . '/' . $cat_icon;
    }
    
    return apply_filters( 'geodir_get_cat_icon', $cat_icon, $full_path, $default );
}

function geodir_get_cat_image( $term_id, $full_path = false ) {
    $term_meta = get_term_meta( $term_id, 'ct_cat_default_img', true );
    
    $cat_image = is_array( $term_meta ) && !empty( $term_meta['src'] ) ? $term_meta['src'] : '';
        
    if ( $cat_image && $full_path && strpos( $cat_image, 'http://' ) !== 0 && strpos( $cat_image, 'https://' ) !== 0 ) {
        $upload_dir = wp_upload_dir();
        $cat_image = $upload_dir['baseurl'] . '/' . $cat_image;
    }
    
    return apply_filters( 'geodir_get_cat_image', $cat_image, $full_path );
}

function geodir_get_cat_top_description( $term_id, $full_path = false ) {
    $term_meta = get_term_meta( $term_id, 'ct_cat_default_img', true );
    
    $cat_image = '';
    if ( is_array( $term_meta ) && !empty( $term_meta['src'] ) ) {
        $cat_image = $term_meta['src'];
        
        if ( $full_path && strpos( $cat_image, 'http://' ) !== 0 && strpos( $cat_image, 'https://' ) !== 0 ) {
            $upload_dir = wp_upload_dir();
            $cat_image = $upload_dir['baseurl'] . '/' . $cat_image;
        }
    }
    
    return apply_filters( 'geodir_get_cat_top_description', $cat_image, $full_path );
}