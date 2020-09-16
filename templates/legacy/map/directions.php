<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Variables.
 *
 * @var array $map_options The map settings options.
 * @var string $map_canvas The map canvas string.
 * @var string $distance_unit The distance units setting.
 */
?>
<div class="geodir-map-directions-wrap">
	<div class="gd-input-group gd-get-directions">
		<div class="gd-input-group-addon gd-directions-left">
			<div class="gd-input-group">
				<input type="text" id="<?php echo $map_canvas; ?>_fromAddress" name="from"
				       class="gd-form-control textfield" value=""
				       placeholder="<?php esc_attr_e( 'Enter your location', 'geodirectory' ); ?>"
				       aria-label="<?php esc_attr_e( 'Enter your location', 'geodirectory' ); ?>"/>
				<div id="<?php echo $map_canvas; ?>_mylocation"
				     class="gd-input-group-addon gd-map-mylocation"
				     onclick="gdMyGeoDirection('<?php echo $map_canvas; ?>');"
				     title="<?php echo esc_attr__( 'My location', 'geodirectory' ); ?>">
					<i class="fas fa-crosshairs" aria-hidden="true"></i></div>
			</div>
		</div>
		<div class="gd-input-group-addon gd-directions-right gd-mylocation-go">
			<input type="button"
			       value="<?php esc_attr_e( 'Get Directions', 'geodirectory' ); ?>"
			       aria-label="<?php esc_attr_e( 'Get Directions', 'geodirectory' ); ?>"
			       class="gd-map-get-directions <?php echo $map_canvas; ?>_getdirection"
			       id="directions"
			       onclick="geodirFindRoute('<?php echo $map_canvas; ?>')"/>
		</div>
	</div>
	<div id='directions-options' class="gd-hidden">
		<select id="travel-mode" onchange="geodirFindRoute('<?php echo $map_canvas; ?>')"
		        aria-label="<?php esc_attr_e( 'Travel mode', 'geodirectory' ); ?>">
			<option value="driving"><?php _e( 'Driving', 'geodirectory' ); ?></option>
			<option value="walking"><?php _e( 'Walking', 'geodirectory' ); ?></option>
			<option value="bicycling"><?php _e( 'Bicycling', 'geodirectory' ); ?></option>
			<option value="transit"><?php _e( 'Public Transport', 'geodirectory' ); ?></option>
		</select>
		<select id="travel-units" onchange="geodirFindRoute('<?php echo $map_canvas; ?>')"
		        aria-label="<?php esc_attr_e( 'Distance unit', 'geodirectory' ); ?>">
			<option
				value="miles" <?php selected( 'miles' == $distance_unit, true ); ?>><?php _e( 'Miles', 'geodirectory' ); ?></option>
			<option
				value="kilometers" <?php selected( 'km' == $distance_unit, true ); ?>><?php _e( 'Kilometers', 'geodirectory' ); ?></option>
		</select>
	</div>
	<div id="<?php echo $map_canvas; ?>_directionsPanel" style="width:auto;"></div>
</div>