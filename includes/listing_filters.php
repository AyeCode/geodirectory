<?php
/**
 * Contains all function for filtering listing.
 *
 * @since 1.0.0
 * @package GeoDirectory
 */

function geodir_related_posts_fields($fields) {
    global $wp_query, $wpdb, $table, $post;

    $fields .= ", " . $table . ".* ";

    $DistanceRadius = geodir_getDistanceRadius(geodir_get_option('search_distance_long'));

    $mylat = $post->post_latitude;
    $mylon = $post->post_longitude;

    $fields .= " , (" . $DistanceRadius . " * 2 * ASIN(SQRT( POWER(SIN((ABS($mylat) - ABS(" . $table . ".post_latitude)) * pi()/180 / 2), 2) +COS(ABS($mylat) * pi()/180) * COS( ABS(" . $table . ".post_latitude) * pi()/180) *POWER(SIN(($mylon - " . $table . ".post_longitude) * pi()/180 / 2), 2) )))as distance ";
    return $fields;
}
function geodir_related_posts_fields_filter($query) {
    if ( isset($query->query_vars['is_geodir_loop']) && $query->query_vars['is_geodir_loop']
        && isset($query->query_vars['order_by']) && $query->query_vars['order_by'] == 'nearest'
        && isset($query->query_vars['related_listings']) && $query->query_vars['related_listings']
    ) {
        add_filter('posts_fields', 'geodir_related_posts_fields', 1);
    }
}
add_action('pre_get_posts', 'geodir_related_posts_fields_filter', 1);

