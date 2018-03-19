<?php
/**
 * The template for displaying listing content within loops
 *
 * This template can be overridden by copying it to yourtheme/geodirectory/map-popup.php.
 *
 * HOWEVER, on occasion GeoDirectory will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://wpgeodirectory.com/docs/customizing-geodirectory-templates/
 * @author  AyeCode
 * @package GeoDirectory/Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $gd_post;

?>
<div class="gd-bubble" style="">
    <div class="gd-bubble-inside">
        <div class="geodir-bubble_desc">
			[gd_post_title tag='h4']
			<div class="geodir-bubble_image">
				<?php if ( $featured_image = geodir_show_featured_image( $gd_post->ID, 'widget-thumb', true, false, $gd_post->featured_image ) ) { ?>
				<a href="<?php the_permalink(); ?>"><?php echo $featured_image; ?></a>
				<?php } ?>
			</div>
			<div class="geodir-bubble-meta-side">
				[gd_output_location location="mapbubble"]
			</div>
			<div class="geodir-bubble-meta-fade"></div>
			<div class="geodir-bubble-meta-bottom">
				[gd_post_rating alignment='left' ]
				[gd_post_fav show='' alignment='right' ]
			</div>
		</div>
	</div>
</div>