<?php
/**
 * Compatibility functions for third party plugins.
 *
 * @since 2.0.0
 * @package GeoDirectory
 */

/*######################################################
Yoast (WP SEO)
######################################################*/

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



/*######################################################
Disqus (comments system)
######################################################*/

/*
 * If Disqus plugin is active, do some fixes to show on blogs but no on GD post types
 */
if ( function_exists( 'dsq_can_replace' ) ) {
	remove_filter( 'comments_template', 'dsq_comments_template' );
	add_filter( 'comments_template', 'dsq_comments_template', 100 );
	add_filter( 'pre_option_disqus_active', 'geodir_option_disqus_active', 10, 1 );
}
/**
 * Disable Disqus plugin on the fly when visiting GeoDirectory post types.
 *
 * @since 1.5.0
 * @package GeoDirectory
 *
 * @param string $disqus_active Hook called before DB call for option so this is empty.
 *
 * @return string `1` if active `0` if disabled.
 */
function geodir_option_disqus_active( $disqus_active ) {
	global $post;
	$all_postypes = geodir_get_posttypes();

	if ( isset( $post->post_type ) && is_array( $all_postypes ) && in_array( $post->post_type, $all_postypes ) ) {
		$disqus_active = '0';
	}

	return $disqus_active;
}


/*######################################################
JetPack
######################################################*/
/**
 * Disable JetPack comments on GD post types.
 *
 * @since 1.6.21
 */
function geodir_jetpack_disable_comments() {
	//only run if jetpack installed
	if ( defined( 'JETPACK__VERSION' ) ) {
		$post_types = geodir_get_posttypes();
		foreach ( $post_types as $post_type ) {
			add_filter( 'jetpack_comment_form_enabled_for_' . $post_type, '__return_false' );
		}
	}
}
add_action( 'plugins_loaded', 'geodir_jetpack_disable_comments' );