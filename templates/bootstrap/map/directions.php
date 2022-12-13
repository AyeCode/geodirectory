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

global $aui_bs5;
?>
<div class="geodir-map-directions-wrap mt-3">

	<div class="row">
		<div class="col">
			<div class="gd-input-group">

				<?php

				echo aui()->input(
					array(
						'id'                => "{$map_canvas}_fromAddress",
						'name'              => "from",
						'placeholder'       => esc_html__( 'Enter your location', 'geodirectory' ),
						'class'             => '',
						'label'             => esc_html__( 'Enter your location', 'geodirectory' ),
						'label_class'       => 'sr-only visually-hidden',
						'extra_attributes'  => array(
							'autocomplete' => 'off',
							'onfocus'       => "jQuery('.gd-directions-from-user').tooltip('show');",
							'onblur'       => "jQuery('.gd-directions-from-user').tooltip('hide');",
						),
						'input_group_right' => '<div class="input-group-text c-pointer gd-directions-from-user" onclick="gdMyGeoDirection(\'' . $map_canvas . '\');" data-toggle="tooltip" title="' . esc_attr__( 'use my location', 'geodirectory' ) . '"><i class="fas fa-location-arrow"></i></div><button class="btn btn-primary" type="button" onclick="geodirFindRoute(\'' . $map_canvas . '\')">' . esc_attr__( 'Get Directions', 'geodirectory' ) . '</button>',
					)
				);
				?>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col">
			<div id='directions-options' class="<?php echo $aui_bs5 ? 'row gap-2' : 'form-row'; ?>" style="display: none;">
				<div class="col">
					<?php
					echo aui()->select( array(
						'id'               => "travel-mode",
						'placeholder'      => esc_attr__( 'Travel mode', 'geodirectory' ),
						'value'            => 'driving',
						'options'          => array(
							"driving"   => esc_attr__( 'Driving', 'geodirectory' ),
							"walking"   => esc_attr__( 'Walking', 'geodirectory' ),
							"bicycling" => esc_attr__( 'Bicycling', 'geodirectory' ),
							"transit"   => esc_attr__( 'Public Transport', 'geodirectory' ),
						),
						'extra_attributes' => array(
							'onchange' => "geodirFindRoute('$map_canvas')"
						)
					) );
					?>
				</div>
				<div class="col">
					<?php
					echo aui()->select( array(
						'id'               => "travel-units",
						'placeholder'      => esc_attr__( 'Distance units', 'geodirectory' ),
						'value'            => $distance_unit == 'km' ? 'kilometers' : 'miles',
						'options'          => array(
							"miles"      => esc_attr__( 'Miles', 'geodirectory' ),
							"kilometers" => esc_attr__( 'Kilometers', 'geodirectory' ),
						),
						'extra_attributes' => array(
							'onchange' => "geodirFindRoute('$map_canvas')"
						)
					) );
					?>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col">
			<div id="<?php echo $map_canvas; ?>_directionsPanel"
			     class="w-auto table table-striped table-borderless"></div>
		</div>
	</div>

</div>
<style>
	.adp-summary {
		text-align: center;
		font-weight: bold;
		font-size: 1.5rem;
	}
</style>