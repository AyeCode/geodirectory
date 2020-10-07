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
 * @var string $wrap_class The wrapper classes setting.
 */
?>
<!--START geodir-map-wrap-->
<div class="geodir-map-wrap geodir-<?php echo $map_type; ?>-map-wrap <?php if(!empty($wrap_class)){ echo $wrap_class; }?>">
	<div id="catcher_<?php echo $map_canvas; ?>"></div>
	<!--START stick_trigger_container-->
	<div class="stick_trigger_container bsui">
		<div class="trigger_sticky triggeroff_sticky mt-n5 btn btn-secondary mr-n2 c-pointer" style="display: none;">
			<i class="fas fa-map-marked-alt"></i>
			<i class="fas fa-angle-right"></i>
		</div>
		<!--end of stick trigger container-->
		<div class="geodir_map_container <?php echo $map_canvas; ?> bsui position-relative mw-100"
		     id="sticky_map_<?php echo $map_canvas; ?>"
		     style="width:<?php echo $width; ?>;min-height:<?php echo $height; ?>;">
			<!--END map_background-->
			<div class="map_background">
				<div class="top_banner_section_in clearfix">
					<div class="<?php if(wp_doing_ajax()){echo "d-none ";} echo $map_canvas; ?>_TopLeft TopLeft position-absolute bg-white text-muted rounded-sm shadow-sm m-2 px-1 py-1 h5 c-pointer" style="z-index: 3;">
						<span class="triggermap" id="<?php echo $map_canvas; ?>_triggermap">
							<i class="fas fa-expand-arrows-alt fa-fw" aria-hidden="true"></i>
							<i class="fas fa-compress-arrows-alt fa-fw d-none" aria-hidden="true"></i>
						</span>
					</div>
					<div class="<?php echo $map_canvas; ?>_TopRight TopRight"></div>
					<div id="<?php echo $map_canvas; ?>_wrapper" class="main_map_wrapper"
					     style="height:<?php echo $height; ?>;width:<?php echo $width; ?>;">
						<div class="iprelative position-relative">
							<div id="<?php echo $map_canvas; ?>" class="geodir-map-canvas"
							     data-map-type="<?php echo $map_type; ?>"
							     data-map-canvas="<?php echo $map_canvas; ?>"
							     style="height:<?php echo $height; ?>;width:<?php echo $width; ?>;z-index: 0;"></div>
							<div id="<?php echo $map_canvas; ?>_loading_div"
							     class="loading_div overlay overlay-black position-absolute row m-0"
							     style="height:<?php echo $height; ?>;width:<?php echo $width; ?>;top:0;z-index: 2;">
								<div class="spinner-border mx-auto align-self-center text-white" role="status">
									<span class="sr-only"><?php _e( "Loading...", "geodirectory" ); ?></span>
								</div>
							</div>
							<div id="<?php echo $map_canvas; ?>_map_nofound"
							     class="advmap_nofound position-absolute row m-0 z-index-1"
							     style="height:<?php echo $height; ?>;width:<?php echo $width; ?>;display:none;top:0;">
								<div class="alert alert-info text-center mx-auto align-self-center shadow-lg">
									<?php echo wp_sprintf( __( '%sNo Records Found%s Sorry, no records were found. Please adjust your search criteria and try again.%s', 'geodirectory' ), "<div class='h3 alert-heading'>", "</div><p>", "</p>" ); ?>
								</div>
							</div>
							<div id="<?php echo $map_canvas; ?>_map_notloaded"
							     class="advmap_notloaded position-absolute row m-0 z-index-1"
							     style="height:<?php echo $height; ?>;width:<?php echo $width; ?>;display:none;top:0;">
								<div class="alert alert-danger text-center mx-auto align-self-center shadow-lg">
									<?php echo wp_sprintf( __( '%sMaps failed to load%s Sorry, unable to load the Maps API.%s', 'geodirectory' ), "<div class='h3 alert-heading'>", "</div><p>", "</p>" ); ?>
								</div>
							</div>
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
<style>
	body.stickymap_hide .stickymap{ display: none !important;}
	body.stickymap_hide .stick_trigger_container .fa-angle-right{ transform: rotate(180deg);}

	/* fullscreen map */
	body.body_fullscreen > .stick_trigger_container{width: 100vw;
		height: 100vh;
		position: fixed;
		z-index: 50000;}

</style>
