<?php
/**
 * Pagination - Show numbered pagination for catalog pages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $wp_query;

if ( $wp_query->max_num_pages <= 1 ) {
	return;
}
?>
<nav class="geodir-pagination">
	<?php

	//$gd_advanced_pagination = geodir_get_option('search_advanced_pagination');
	global $gd_advanced_pagination;
	$pagination_info = '';
	if ($gd_advanced_pagination != '') {
		global $posts_per_page, $wpdb, $paged;

		$post_type = geodir_get_current_posttype();
		$listing_type_name = get_post_type_plural_label($post_type);
		if (geodir_is_page('archive') || geodir_is_page('search')) {
			$term = array();

			if (is_tax()) {
				$term_id = get_queried_object_id();
				$taxonomy = get_query_var('taxonomy');

				if ($term_id && $post_type && get_query_var('taxonomy') == $post_type . 'category' ) {
					$term = get_term($term_id, $post_type . 'category');
				}
			}

			if (geodir_is_page('search') && !empty($_REQUEST['s' . $post_type . 'category'])) {
				$taxonomy_search = $_REQUEST['s' . $post_type . 'category'];

				if (!is_array($taxonomy_search)) {
					$term = get_term((int)$taxonomy_search, $post_type . 'category');
				} else if(is_array($taxonomy_search) && count($taxonomy_search) == 1) { // single category search
					$term = get_term((int)$taxonomy_search[0], $post_type . 'category');
				}
			}

			if (!empty($term) && !is_wp_error($term)) {
				$listing_type_name = $term->name;
			}
		}

		$numposts = $wp_query->found_posts;
		$max_page = $posts_per_page ? ceil($numposts / $posts_per_page) : 1;
		if (empty($paged)) {
			$paged = 1;
		}
		$start_no = ( $paged - 1 ) * $posts_per_page + 1;
		$end_no = min($paged * $posts_per_page, $numposts);
		if ( $listing_type_name ) {
			$listing_type_name = __( $listing_type_name, 'geodirectory' );
			$pegination_desc   = wp_sprintf( __( 'Showing %s %d-%d of %d', 'geodirectory' ), $listing_type_name, $start_no, $end_no, $numposts );
		} else {
			$pegination_desc = wp_sprintf( __( 'Showing listings %d-%d of %d', 'geodirectory' ), $start_no, $end_no, $numposts );
		}
		$pagination_info = '<div class="gd-pagination-details">' . $pegination_desc . '</div>';

		/**
		 * Adds an extra pagination info above/under pagination.
		 *
		 * @since 1.5.9
		 *
		 * @param string $pagination_info Extra pagination info content.
		 * @param string $listing_type_name Listing results type.
		 * @param string $start_no First result number.
		 * @param string $end_no Last result number.
		 * @param string $numposts Total number of listings.
		 * @param string $post_type The post type.
		 */
		$pagination_info = apply_filters('geodir_pagination_advance_info', $pagination_info, $listing_type_name, $start_no, $end_no, $numposts, $post_type);

	}

	if (function_exists('geodir_location_geo_home_link')) {
		remove_filter('home_url', 'geodir_location_geo_home_link', 100000);
	}


	if($gd_advanced_pagination=='before' && $pagination_info){
		echo $pagination_info;
	}

	echo paginate_links( apply_filters( 'geodir_pagination_args', array(
		'base'         => esc_url_raw( str_replace( 999999999, '%#%', remove_query_arg( 'add-to-cart', get_pagenum_link( 999999999, false ) ) ) ),
		'format'       => '',
		'add_args'     => false,
		'current'      => max( 1, get_query_var( 'paged' ) ),
		'total'        => $wp_query->max_num_pages,
		'prev_text'    => '&larr;',
		'next_text'    => '&rarr;',
		'type'         => 'list',
		'end_size'     => 3,
		'mid_size'     => 3,
	) ) );

	if($gd_advanced_pagination=='after' && $pagination_info){
		echo $pagination_info;
	}

	if (function_exists('geodir_location_geo_home_link')) {
		add_filter('home_url', 'geodir_location_geo_home_link', 100000, 2);
	}

	?>
</nav>
