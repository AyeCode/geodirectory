<?php
/**
 * Display Map
 *
 * This template can be overridden by copying it to yourtheme/geodirectory/bootstrap/map/map.php.
 *
 * HOWEVER, on occasion GeoDirectory will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see        https://docs.wpgeodirectory.com/article/346-customizing-templates/
 * @package    GeoDirectory
 * @version    2.1.1.12
 *
 * @var array $map_options The map settings options.
 * @var string $map_type The map type.
 * @var string $map_canvas The map canvas string.
 * @var string $height The map height setting.
 * @var string $width The map width setting.
 * @var string $wrap_class The wrapper classes setting.
 */

defined( 'ABSPATH' ) || exit;
?>
<!--START geodir-map-wrap-->
<div class="geodir-map-wrap geodir-<?php echo $map_type; ?>-map-wrap <?php if(!empty($wrap_class)){ echo $wrap_class; }?>">
	<div id="catcher_<?php echo $map_canvas; ?>"></div>
	<!--START stick_trigger_container-->
	<div class="stick_trigger_container bsui">
		<div class="trigger_sticky triggeroff_sticky mt-n5 btn btn-secondary mr-n2 me-n2 c-pointer" style="display: none;">
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
					<div class="<?php if ( wp_doing_ajax() || ! empty( $hide_expand_map ) ) { echo "d-none "; } echo $map_canvas; ?>_TopLeft TopLeft position-absolute bg-white text-muted rounded-sm rounded-1 shadow-sm m-2 px-1 py-1 h5 c-pointer" style="z-index: 3;">
						<span class="triggermap" id="<?php echo $map_canvas; ?>_triggermap">
							<i class="fas fa-expand-arrows-alt fa-fw" aria-hidden="true"></i>
							<i class="fas fa-compress-arrows-alt fa-fw d-none" aria-hidden="true"></i>
						</span>
					</div>
					<div class="<?php echo $map_canvas; ?>_TopRight TopRight"></div>
					<div id="<?php echo $map_canvas; ?>_wrapper" class="main_map_wrapper" style="height:<?php echo $height; ?>;width:<?php echo $width; ?>;">
						<div class="iprelative position-relative">
							<div id="<?php echo $map_canvas; ?>" class="geodir-map-canvas" data-map-type="<?php echo $map_type; ?>" data-map-canvas="<?php echo $map_canvas; ?>" style="height:<?php echo $height; ?>;width:<?php echo $width; ?>;z-index: 0;" <?php echo ( isset( $extra_attribs ) ? $extra_attribs : '' ); ?>></div>
							<div id="<?php echo $map_canvas; ?>_loading_div" class="loading_div overlay overlay-black position-absolute row m-0" style="height:<?php echo $height; ?>;width:<?php echo $width; ?>;top:0;z-index: 2;">
								<div class="spinner-border mx-auto align-self-center text-white" role="status">
									<span class="sr-only visually-hidden"><?php _e( "Loading...", "geodirectory" ); ?></span>
								</div>
							</div>
							<div id="<?php echo $map_canvas; ?>_map_nofound"
							     class="advmap_nofound position-absolute row m-0 z-index-1"
							     style="display:none;transform: translate(-50%, -50%);top:50%;left:50%;opacity: .85;pointer-events: none;">
								<div class="alert alert-info text-center mx-auto align-self-center shadow-lg mb-0">
									<?php echo wp_sprintf( __( '%sNo Records Found%s Sorry, no records were found. Please adjust your search criteria and try again.%s', 'geodirectory' ), "<div class='h3 alert-heading'>", "</div><p>", "</p>" ); ?>
								</div>
							</div>
							<div id="<?php echo $map_canvas; ?>_map_notloaded"
							     class="advmap_notloaded position-absolute row m-0 z-index-1"
							     style="display:none;transform: translate(-50%, -50%);top:50%;left:50%;opacity: .85;pointer-events: none;">
								<div class="alert alert-danger text-center mx-auto align-self-center shadow-lg mb-0">
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
<style>body.stickymap_hide .stickymap{display:none!important}body.stickymap_hide .stick_trigger_container .fa-angle-right{transform:rotate(180deg)}body.body_fullscreen>.stick_trigger_container{width:100vw;height:100vh;position:fixed;z-index:50000}body.gd-google-maps .geodir-map-wrap .geodir-map-cat-filter-wrap{margin-bottom:14px!important}body.gd-osm-gmaps .geodir-map-wrap .geodir-map-cat-filter-wrap{margin-bottom:16.5px!important}</style>
