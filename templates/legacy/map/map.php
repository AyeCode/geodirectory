<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Variables.
 *
 * @var array $map_options The map settings options.
 * @var string $map_type The map type.
 * @var string $map_canvas The map canvas string.
 * @var string $height The map height setting.
 * @var string $width The map width setting.
 */
?>
<!--START geodir-map-wrap-->
<div class="geodir-map-wrap geodir-<?php echo $map_type; ?>-map-wrap">
	<div id="catcher_<?php echo $map_canvas; ?>"></div>
	<!--START stick_trigger_container-->
	<div class="stick_trigger_container">
		<div class="trigger_sticky triggeroff_sticky">
			<i class="fas fa-map-marked-alt"></i>
			<i class="fas fa-angle-right"></i>
		</div>
		<!--end of stick trigger container-->
		<div class="top_banner_section geodir_map_container <?php echo $map_canvas; ?>"
		     id="sticky_map_<?php echo $map_canvas; ?>"
		     style="width:<?php echo $width; ?>;min-height:<?php echo $height; ?>;">
			<!--END map_background-->
			<div class="map_background">
				<div class="top_banner_section_in clearfix">
					<div class="<?php echo $map_canvas; ?>_TopLeft TopLeft"><span class="triggermap"
					                                                              id="<?php echo $map_canvas; ?>_triggermap"><i
								class="fas fa-expand-arrows-alt" aria-hidden="true"></i></span></div>
					<div class="<?php echo $map_canvas; ?>_TopRight TopRight"></div>
					<div id="<?php echo $map_canvas; ?>_wrapper" class="main_map_wrapper"
					     style="height:<?php echo $height; ?>;width:<?php echo $width; ?>;">
						<div class="iprelative">
							<div id="<?php echo $map_canvas; ?>" class="geodir-map-canvas"
							     data-map-type="<?php echo $map_type; ?>"
							     data-map-canvas="<?php echo $map_canvas; ?>"
							     style="height:<?php echo $height; ?>;width:<?php echo $width; ?>;" <?php echo ( isset( $extra_attribs ) ? $extra_attribs : '' ); ?>></div>
							<div id="<?php echo $map_canvas; ?>_loading_div" class="loading_div"
							     style="height:<?php echo $height; ?>;width:<?php echo $width; ?>;"></div>
							<div id="<?php echo $map_canvas; ?>_map_nofound"
							     class="advmap_nofound"><?php _e( '<h3>No Records Found</h3><p>Sorry, no records were found. Please adjust your search criteria and try again.</p>', 'geodirectory' ); ?></div>
							<div id="<?php echo $map_canvas; ?>_map_notloaded"
							     class="advmap_notloaded"><?php _e( '<h3>Google Map Not Loaded</h3><p>Sorry, unable to load Google Maps API.', 'geodirectory' ); ?></div>
						</div>
					</div>
					<div class="<?php echo $map_canvas; ?>_BottomLeft BottomLeft"></div>
				</div>
			</div><!--END map_background-->
			<?php do_action( 'geodir_map_custom_content', $map_options ); ?>
		</div>
		<?php do_action( 'geodir_map_custom_script', $map_options ); ?>
	</div><!--END stick_trigger_container-->
</div><!--END geodir-map-wrap-->
