<?php
namespace AyeCode\GeoDirectory\Fields\Types;

use AyeCode\GeoDirectory\Fields\Abstracts\AbstractFieldType;

/**
 * Class AddressField
 * * Replaces geodir_cfi_address in input-functions-aui.php
 */
class AddressField extends AbstractFieldType {

	public function render_input() {
		// Extract commonly used data
		$cf           = $this->field_data;
		$extra_fields = $this->get_extra_fields();
		$prefix       = $cf['htmlvar_name'] . '_';

		// Prepare values (Street, City, etc are usually stored in separate columns, not just one meta)
		// We might need a helper in Abstract to get the full $gd_post object if needed,
		// or fetching individual metas here.
		$street  = geodir_get_post_meta( $this->post_id, 'street', true );
		$street2 = geodir_get_post_meta( $this->post_id, 'street2', true );
		$zip     = geodir_get_post_meta( $this->post_id, 'zip', true );
		$lat     = geodir_get_post_meta( $this->post_id, 'latitude', true );
		$lng     = geodir_get_post_meta( $this->post_id, 'longitude', true );

		// 1. Render Street (Main Input)
		$street_args = $this->get_aui_args(); // Gets standard label, required, wrappers
		$street_args['id'] = $prefix . 'street'; // Override ID
		$street_args['name'] = 'street';         // Override Name to match DB column
		$street_args['class'] .= ' gd-add-listing-address-input';
		$street_args['value'] = $street;

		// "Locate Me" Button logic
		if ( ! empty( $extra_fields['show_map'] ) ) {
			$street_args['input_group_right'] = '<div class="gd-locate-me-btn input-group-text c-pointer" data-toggle="tooltip" title="' . esc_attr__( 'use my location', 'geodirectory' ) . '"><i class="fas fa-location-arrow"></i></div>';
		}

		$html = aui()->input( $street_args );

		// 2. Render Street 2 (Conditional)
		if ( ! empty( $extra_fields['show_street2'] ) ) {
			$html .= aui()->input([
				'id'    => $prefix . 'street2',
				'name'  => 'street2',
				'label' => isset($extra_fields['street2_lable']) ? $extra_fields['street2_lable'] : __( 'Street 2', 'geodirectory' ), // typo in DB 'lable' is historic
				'value' => $street2,
				'type'  => 'text',
				'label_type' => $street_args['label_type']
			]);
		}

		// 3. Render Zip (Conditional)
		if ( ! empty( $extra_fields['show_zip'] ) ) {
			$html .= aui()->input([
				'id'       => $prefix . 'zip',
				'name'     => 'zip',
				'label'    => isset($extra_fields['zip_lable']) ? $extra_fields['zip_lable'] : __( 'Zip/Post Code', 'geodirectory' ),
				'value'    => $zip,
				'required' => ! empty( $extra_fields['zip_required'] ),
				'type'     => 'text',
				'label_type' => $street_args['label_type']
			]);
		}

		// 4. Render Map & Lat/Lng (This should probably be a view partial or a helper method)
		if ( ! empty( $extra_fields['show_map'] ) ) {
			$html .= $this->render_map_interface( $lat, $lng, $extra_fields );
		}

		return $html;
	}

	/**
	 * Helper to render the map container and hidden lat/lng inputs.
	 */
	protected function render_map_interface( $lat, $lng, $extra_fields ) {
		ob_start();

		// Map Container
		?>
		<div id="geodir_map_row" class="geodir_form_row clearfix gd-fieldset-details">
			<?php
			// This logic from input-functions-aui.php ~line 1356 calls a template.
			// In v3, we should call the Maps Service.
			echo geodirectory()->maps->render_add_listing_map([
				'lat' => $lat,
				'lng' => $lng
			]);
			?>
		</div>

		<?php
		// Lat/Lng Inputs (often hidden via CSS class if show_latlng is false)
		$wrap_class = ( ! empty( $extra_fields['show_latlng'] ) || is_admin() ) ? '' : 'd-none gd-hidden-latlng';

		echo aui()->input([
			'type' => 'number',
			'name' => 'latitude',
			'value'=> $lat,
			'label'=> __('Address Latitude', 'geodirectory'),
			'wrap_class' => $wrap_class,
			'extra_attributes' => ['step' => 'any', 'min' => '-90', 'max' => '90']
		]);

		echo aui()->input([
			'type' => 'number',
			'name' => 'longitude',
			'value'=> $lng,
			'label'=> __('Address Longitude', 'geodirectory'),
			'wrap_class' => $wrap_class,
			'extra_attributes' => ['step' => 'any', 'min' => '-180', 'max' => '180']
		]);

		return ob_get_clean();
	}
}
