<?php
/**
 * Template for the list of places
 *
 * This is used to outputs the actual grid or list of widget listings.
 * See the link below for info on how to replace the template in your theme.
 *
 * @link https://wpgeodirectory.com/documentation/article/how-tos/customizing-templates/
 * @since 1.0.0
 * @package GeoDirectory
 * @version 2.1.0.10
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

$style_tag = '';
$styles = '';
if($column_gap){
	$styles .= 'grid-column-gap: '.absint($column_gap).'px;';
}
if($row_gap){
	$styles .= 'grid-row-gap: '.absint($row_gap).'px;';
}
if($styles){
	$style_tag = "style='$styles'";
}

global $gdecs_render_loop, $geodir_el_archive_item_tl;
?>
<div class="elementor-posts-container elementor-posts elementor-grid elementor-posts--skin-gd_custom" <?php echo $style_tag;?>>
	<?php if ( !empty( $widget_listings ) ) {

		/**
		 * Called inside the `ul` of the listings template, but before any `li` elements.
		 *
		 * When used by the widget view template then it will only show if there are listings to be shown.
		 *
		 * @since 1.0.0
		 * @see 'geodir_after_listing_post_listview'
		 */
		do_action('geodir_before_listing_post_listview');
		$classes = 'elementor-post elementor-grid-item';
		foreach ( $widget_listings as $widget_listing ) {
			geodir_setup_postdata( $widget_listing );

			$gdecs_render_loop = get_the_ID() . "," . $skin_id;
			$geodir_el_archive_item_tl = $skin_id;
			?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( [ $classes ] ); ?>>
			<?php
			$return = \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $skin_id );

			echo $return;
			?>
		</article>
			<?php
		}

		/**
		 * Called inside the `ul` of the listings template, but after all `li` elements.
		 *
		 * When used by the widget view template then it will only show if there are listings to be shown.
		 *
		 * @since 1.0.0
		 * @see 'geodir_before_listing_post_listview'
		 */
		do_action('geodir_after_listing_post_listview');

	} else {
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
