<?php
/**
 * Elementor Archive Posts Loop
 *
 * This template can be overridden by copying it to yourtheme/geodirectory/elementor/content-archive-listing.php.
 *
 * HOWEVER, on occasion GeoDirectory will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see        https://docs.wpgeodirectory.com/article/346-customizing-templates/
 * @package    GeoDirectory\Templates
 * @version    2.2.7
 *
 * Variables.
 *
 * @var int $skin_id Elementor skin id.
 * @var int $columns Columns.
 * @var int $column_gap Columns padding.
 * @var int $row_gap Row padding.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $gdecs_render_loop, $geodir_el_archive_item_tl;

/**
 * Called before the listing template used to list listing of places.
 *
 * This is used anywhere you see a list of listings.
 *
 * @since 1.0.0
 */
do_action( 'geodir_before_listing_listview' );

$style_tag = '';
$styles = '';
if ( $column_gap ) {
	$styles .= 'grid-column-gap:' . absint( $column_gap ) . 'px;';
}
if ( $row_gap ) {
	$styles .= 'grid-row-gap:' . absint( $row_gap ) . 'px;';
}
if ( $styles ) {
	$style_tag = " style='$styles'";
}

$classes = 'elementor-post elementor-grid-item';
?>
<div class="elementor-posts-container elementor-posts elementor-grid elementor-posts--skin-gd_archive_custom"<?php echo $style_tag;?>>
	<?php 
	if ( have_posts() ) {
		/**
		 * Called inside the `ul` of the listings template, but before any `li` elements.
		 *
		 * When used by the widget view template then it will only show if there are listings to be shown.
		 *
		 * @since 1.0.0
		 * @see 'geodir_after_listing_post_listview'
		 */
		do_action( 'geodir_before_listing_post_listview' );

		while ( have_posts() ) {
			the_post();

			$gdecs_render_loop = get_the_ID() . "," . $skin_id;
			$geodir_el_archive_item_tl = $skin_id;

			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class( [ $classes ] ); ?> data-post-id="<?php echo absint( get_the_ID() ); ?>">
			<?php
				$output = \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $skin_id );

				echo $output;
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
		do_action( 'geodir_after_listing_post_listview' );
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
do_action( 'geodir_after_listing_listview' );