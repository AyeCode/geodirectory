<?php
/**
 * Template for the list of places
 *
 * This is used mostly on the listing (category) pages and outputs the actual grid or list of listings.
 * See the link below for info on how to replace the template in your theme.
 *
 * @link https://wpgeodirectory.com/documentation/article/how-tos/customizing-templates/
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
 *
 * @var int $row_gap_class The row gap class setting.
 * @var int $column_gap_class The column gap class setting.
 * @var int $card_border_class The card border class setting.
 * @var int $card_shadow_class The card shadow class setting.
 */
do_action('geodir_before_listing_listview');

global $related_nearest, $related_parent_lat, $related_parent_lon, $gd_layout_class;

?>
<div
	class="row row-cols-1 row-cols-sm-2 geodir-category-list-view <?php echo apply_filters('geodir_listing_listview_ul_extra_class', $gd_layout_class, 'listing'); ?>">

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

				echo geodir_get_template_html( "bootstrap/content-listing.php", array(
					'column_gap_class'   => $column_gap_class,
					'row_gap_class'   => $row_gap_class,
					'card_border_class'   => $card_border_class,
					'card_shadow_class'   => $card_shadow_class,
				) );

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
	</div>
<?php
/**
 * Called after the listings list view template, after all the wrapper at the very end.
 *
 * @since 1.0.0
 */
do_action('geodir_after_listing_listview');
