<?php
/**
 * Display Map Filters
 *
 * This template can be overridden by copying it to yourtheme/geodirectory/bootstrap/map/filter-tax.php.
 *
 * HOWEVER, on occasion GeoDirectory will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see        https://wpgeodirectory.com/documentation/article/how-tos/customizing-templates/
 * @package    GeoDirectory
 * @version    2.1.0.16
 *
 * @var array $map_options The map settings options.
 * @var string $map_canvas The map canvas string.
 * @var string $cat_filter_class The category filter class.
 * @var array $map_post_types The custom post types for the map.
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- START cat_filter/search_filter -->
<div class="geodir-map-cat-filter-wrap position-absolute row m-0 text-light px-2 overflow-hidden z-index-1" style="max-height: 100%;right:0;bottom:0;background:#000000a1;<?php echo ( geodir_lazy_load_map() == 'click' ? 'display:none;' : '' ); ?>">
	<div class="map-category-listing<?php echo $cat_filter_class; ?> scrollbars-ios overflow-auto px-0" style="max-height:<?php echo $map_options['height']; ?>;">
		<div class="gd-trigger gd-triggeroff text-right text-end c-pointer">
			<i class="fas fa-chevron-down"></i>
			<i class="fas fa-sliders-h d-none"></i>
		</div>
		<div id="<?php echo $map_canvas; ?>_cat"
		     class="<?php echo $map_canvas; ?>_map_category map_category" <?php checked( ! empty( $map_options['child_collapse'] ), true ); ?>
		     style="max-height:<?php echo $map_options['height']; ?>;">
			<?php

			if ( ! empty( $map_options['post_type_filter'] ) ) {
				if ( ! empty( $map_post_types ) && count( array_keys( $map_post_types ) ) > 1 ) {

					// template output
					$template = "bootstrap/map/filter-cpt.php";
					$args = array(
						'map_options'  => $map_options,
						'map_canvas'  => $map_canvas,
						'map_post_types'  => $map_post_types,
					);
					echo geodir_get_template_html( $template, $args );

				}
			}

			if ( ! empty( $map_options['search_filter'] ) ) {
				echo aui()->input(
					array(
						'id'                => "{$map_canvas}_search_string",
						'name'              => "search",
						'type'              => "search",
						'placeholder'       => esc_attr__( 'Search by name', 'geodirectory' ),
						'title'       => esc_attr__( 'hit enter to search', 'geodirectory' ),
						'class'             => 'form-control-sm',
						'no_wrap'           => true,
						'extra_attributes'  => array(
							'autocomplete' => 'off',
							'data-toggle' => 'tooltip',
							'data-placement' => 'left',
							'onkeydown'       => "if(event.keyCode == 13){build_map_ajax_search_param('$map_canvas', false);}",
							'style'         => "box-sizing: border-box;",
							'aria-label' => esc_attr__( 'Search by name', 'geodirectory' )
						),
					)
				);
			}
			?>

			<?php if ( ! empty( $map_options['cat_filter'] ) ) { ?>
				<input type="hidden" id="<?php echo $map_canvas; ?>_child_collapse" value="<?php echo absint( $map_options['child_collapse'] ); ?>"/>
				<input type="hidden" id="<?php echo $map_canvas; ?>_cat_enabled" value="1"/>
				<div class="geodir_toggle my-1">
					<?php if ( geodir_lazy_load_map() ) { ?>
					<div class="py-2 px-1 small"><div role="status" class="spinner-border text-white d-inline-block align-middle"></div><div class="ml-2 ms-2 d-inline-block align-middle"><?php _e( "Loading categories...", "geodirectory" ); ?></div></div>
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
