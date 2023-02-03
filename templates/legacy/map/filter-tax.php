<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Variables.
 *
 * @var array $map_options The map settings options.
 * @var string $map_canvas The map canvas string.
 * @var string $cat_filter_class The category filter class.
 */
?>
<!-- START cat_filter/search_filter -->
<div class="map-category-listing-main geodir-map-cat-filter-wrap"<?php echo ( geodir_lazy_load_map() == 'click' ? ' style="display:none;"' : '' ); ?>>
	<div class="map-category-listing<?php echo $cat_filter_class; ?>">
		<div class="gd-trigger gd-triggeroff"><i class="fas fa-compress" aria-hidden="true"></i><i
				class="fas fa-expand" aria-hidden="true"></i>
		</div>
		<div id="<?php echo $map_canvas; ?>_cat"
		     class="<?php echo $map_canvas; ?>_map_category map_category" <?php checked( ! empty( $map_options['child_collapse'] ), true ); ?>
		     style="max-height:<?php echo $map_options['height']; ?>;">
			<input
				onkeydown="if(event.keyCode == 13){build_map_ajax_search_param('<?php echo $map_canvas; ?>', false);}"
				type="text"
				class="inputbox <?php echo( $map_options['search_filter'] ? '' : 'geodir-hide' ); ?>"
				id="<?php echo $map_canvas; ?>_search_string"
				name="search"
				placeholder="<?php esc_attr_e( 'Title', 'geodirectory' ); ?>"
				aria-label="<?php esc_attr_e( 'Title', 'geodirectory' ); ?>"/>
			<?php if ( ! empty( $map_options['cat_filter'] ) ) { ?>
				<input type="hidden" id="<?php echo $map_canvas; ?>_child_collapse" value="<?php echo absint( $map_options['child_collapse'] ); ?>"/>
				<input type="hidden" id="<?php echo $map_canvas; ?>_cat_enabled" value="1"/>
				<div class="geodir_toggle">
				<?php if ( geodir_lazy_load_map() ) { ?>
					<div class="py-2 px-1"><i class="fas fa-circle-notch fa-spin mr-1 me-1" aria-hidden="true"></i> <small><?php _e( "Loading categories...", "geodirectory" ); ?></small></div>
					<?php } else { ?>
					<?php echo GeoDir_Maps::get_categories_filter( $map_options['post_type'], 0, true, 0, $map_canvas, absint( $map_options['child_collapse'] ), $map_options['terms'], true, $map_options['tick_terms'] ); ?>
					<script type="text/javascript">jQuery(window).on("load",function(){geodir_show_sub_cat_collapse_button('<?php echo $map_canvas; ?>');});</script>
					<?php } ?>
				</div>
			<?php } else { ?>
				<input type="hidden" id="<?php echo $map_canvas; ?>_cat_enabled" value="0"/>
				<input type="hidden" id="<?php echo $map_canvas; ?>_child_collapse" value="0"/>
			<?php } ?>
			<div class="BottomRight"></div>
		</div>
	</div>
</div><!-- END cat_filter/search_filter -->