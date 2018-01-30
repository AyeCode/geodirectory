<?php
/**
 * Compatibility functions for third party plugins.
 *
 * @since 2.0.0
 * @package GeoDirectory
 */


/**
 * Add category meta image in Yoast SEO taxonomy data.
 *
 * @since 1.6.9
 *
 * @global object $wp_query WordPress Query object.
 *
 * @param array $value Taxonomy meta value.
 * @param string $option Option name.
 * @return mixed The taxonomy option value.
 */
function geodir_wpseo_taxonomy_meta( $value, $option = '' ) {
	global $wp_query;

	if ( !empty( $value ) && ( is_category() || is_tax() ) ) {
		$term = $wp_query->get_queried_object();

		if ( !empty( $term->term_id ) && !empty( $term->taxonomy ) && isset( $value[$term->taxonomy][$term->term_id] ) && geodir_is_gd_taxonomy( $term->taxonomy ) ) {
			$image  = geodir_get_cat_image( $term->term_id, true );

			if ( !empty( $image ) ) {
				$value[$term->taxonomy][$term->term_id]['wpseo_twitter-image'] = $image;
				$value[$term->taxonomy][$term->term_id]['wpseo_opengraph-image'] = $image;
			}
		}
	}
	return $value;
}
add_filter( 'option_wpseo_taxonomy_meta', 'geodir_wpseo_taxonomy_meta', 10, 2 );
