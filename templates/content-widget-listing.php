<?php
/**
 * Template for the list of places
 *
 * This is used to outputs the actual grid or list of widget listings.
 * See the link below for info on how to replace the template in your theme.
 *
 * @link http://docs.wpgeodirectory.com/customizing-geodirectory-templates/
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @global object $post The current post object.
 * @global object $wp_query WordPress Query object.
 * @global string $gridview_columns The girdview style of the listings.
 * @global object $gd_session GeoDirectory Session object.
 */

/**
 * Called before the listing template used to list listing of places.
 *
 * This is used anywhere you see a list of listings.
 *
 * @since 1.0.0
 */
do_action('geodir_before_listing_listview');

?>
<ul class="geodir-category-list-view clearfix <?php echo apply_filters('geodir_listing_listview_ul_extra_class', '', 'widget'); ?>">

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

		foreach ( $widget_listings as $widget_listing ) {
			global $gd_post, $post;
			$gd_post = $widget_listing;
			$post = $gd_post;

			setup_postdata( $post );

			geodir_get_template_part('content', 'listing');

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
		$favorite = isset($_REQUEST['list']) && $_REQUEST['list'] == 'favourite' ? true : false;

		/**
		 * Called inside the `ul` of the listings template, when no listing found.
		 *
		 * @since 1.5.5
		 * @param string 'widget-listview' Widget listview template.
		 * @param bool $favorite Are favorite listings results?
		 */
		do_action('geodir_message_not_found_on_listing', 'widget-listview', $favorite);

		geodir_no_listings_found(); //@todo implement this and no the old above message
		
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
