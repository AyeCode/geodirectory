<?php
/**
 * Template for the list of places
 *
 * This is used mostly on the listing (category) pages and outputs the actual grid or list of listings.
 * See the link below for info on how to replace the template in your theme.
 *
 * @link https://docs.wpgeodirectory.com/article/346-customizing-templates/
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @global object $post The current post object.
 * @global object $wp_query WordPress Query object.
 */

/**
 * Called before the listing template used to list listing of places.
 *
 * This is used anywhere you see a list of listings.
 *
 * @since 1.0.0
 */
do_action('geodir_before_listing_listview');

global $related_nearest, $related_parent_lat, $related_parent_lon, $gd_layout_class;

?>
	<ul class="geodir-category-list-view clearfix <?php echo apply_filters('geodir_listing_listview_ul_extra_class', $gd_layout_class, 'listing'); ?>">
		<?php if (have_posts()) {

			/**
			 * Called inside the `ul` of the listings template, but before any `li` elements.
			 *
			 * When used by the widget view template then it will only show if there are listings to be shown.
			 *
			 * @since 1.0.0
			 * @see 'geodir_after_listing_post_listview'
			 */
			do_action( 'geodir_before_listing_post_listview' );

			while ( have_posts() ) : the_post();

				geodir_get_template_part( 'content', 'listing' );

			endwhile;

			/**
			 * Called inside the `ul` of the listings template, but after all `li` elements.
			 *
			 * When used by the widget view template then it will only show if there are listings to be shown.
			 *
			 * @since 1.0.0
			 * @see 'geodir_before_listing_post_listview'
			 */
			do_action( 'geodir_after_listing_post_listview' );

		}else {
			geodir_no_listings_found();
		}

		?>
	</ul>  <!-- geodir_category_list_view ends here-->

	<div class="clear"></div>
<?php
/**
 * Called after the listings list view template, after all the wrapper at the very end.
 *
 * @since 1.0.0
 */
do_action('geodir_after_listing_listview');
