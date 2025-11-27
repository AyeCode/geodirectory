<?php

namespace AyeCode\GeoDirectory\Fields\Types;

use AyeCode\GeoDirectory\Fields\Abstracts\AbstractFieldType;

/**
 * Class AddressField
 * * Replaces geodir_cfi_address in input-functions-aui.php
 */
class AddressField extends AbstractFieldType {

	public function render_input() {
		global $geodir_label_type;

		// Extract commonly used data
		$cf           = $this->field_data;
		$extra_fields = $this->get_extra_fields();
		$prefix       = $cf['htmlvar_name'] . '_';

		// Get all post data in a single call instead of multiple geodir_get_post_meta calls
		$gd_post = $this->post_id ? geodirectory()->posts->get_info( $this->post_id ) : null;

		// Prepare values from the single post object
		$street  = ! empty( $gd_post->street ) ? $gd_post->street : '';
		$street2 = ! empty( $gd_post->street2 ) ? $gd_post->street2 : '';
		$zip     = ! empty( $gd_post->zip ) ? $gd_post->zip : '';
		$lat     = ! empty( $gd_post->latitude ) ? $gd_post->latitude : '';
		$lng     = ! empty( $gd_post->longitude ) ? $gd_post->longitude : '';
		$country = ! empty( $gd_post->country ) ? $gd_post->country : '';
		$region  = ! empty( $gd_post->region ) ? $gd_post->region : '';
		$city    = ! empty( $gd_post->city ) ? $gd_post->city : '';

		// 1. Render Street (Main Input)
		$street_args              = $this->get_aui_args(); // Gets standard label, required, wrappers
		$street_args['id']        = $prefix . 'street'; // Override ID
		$street_args['name']      = 'street';         // Override Name to match DB column
		$street_args['class']     .= ' gd-add-listing-address-input';
		$street_args['value']     = $street;
		$street_args['help_text'] = __( 'Please enter the listing street address. eg. : 230 Vine Street', 'geodirectory' );

		// "Locate Me" Button logic
		$locate_me = ! empty( $extra_fields['show_map'] ) && geodirectory()->maps->active_map() != 'none' ? true : false;
		if ( $locate_me ) {
			$street_args['input_group_right'] = '<div class="gd-locate-me-btn input-group-text c-pointer" data-toggle="tooltip" title="' . esc_attr__( 'use my location', 'geodirectory' ) . '"><i class="fas fa-location-arrow"></i></div>';
		}

		$html = aui()->input( $street_args );

		// 2. Render Street 2 (Conditional)
		if ( ! empty( $extra_fields['show_street2'] ) ) {
			$html .= aui()->input( [
				'id'         => $prefix . 'street2',
				'name'       => 'street2',
				// the typo in DB 'lable' is historic
				'label'      => isset( $extra_fields['street2_lable'] ) ? $extra_fields['street2_lable'] : __( 'Street 2', 'geodirectory' ),
				'value'      => $street2,
				'type'       => 'text',
				'label_type' => $street_args['label_type'],
				'help_text'  => __( 'Please enter listing Address line 2 (optional)', 'geodirectory' ),

			] );
		}

		// 3. Render Country, Region, City (Conditional - only if multi_city is enabled)
		if ( geodir_core_multi_city() ) {
			$html .= $this->render_multi_city_fields( $country, $region, $city, $cf, $street_args['label_type'] );
		}

		// 4. Render Zip (Conditional)
		if ( ! empty( $extra_fields['show_zip'] ) ) {
			$html .= aui()->input( [
				'id'         => $prefix . 'zip',
				'name'       => 'zip',
				'label'      => isset( $extra_fields['zip_lable'] ) ? $extra_fields['zip_lable'] : __( 'Zip/Post Code', 'geodirectory' ),
				'value'      => $zip,
				'required'   => ! empty( $extra_fields['zip_required'] ),
				'type'       => 'text',
				'label_type' => $street_args['label_type'],
				'help_text'  => __( 'Please enter listing Zip/Post Code', 'geodirectory' ),

			] );
		}


		// 5. Render Map & Lat/Lng (This should probably be a view partial or a helper method)
		if ( ! empty( $extra_fields['show_map'] ) ) {
			$html .= $this->render_map_interface( $lat, $lng, $extra_fields );
		}

		return $html;
	}

	/**
	 * Helper to render country, region, and city inputs when multi_city is enabled.
	 *
	 * @param string $country The country value.
	 * @param string $region The region value.
	 * @param string $city The city value.
	 * @param array $field The field data array.
	 * @param string $label_type The label type for AUI inputs.
	 *
	 * @return string HTML for country, region, and city inputs.
	 */
	protected function render_multi_city_fields( $country, $region, $city, $field, $label_type ) {
		$html        = '';
		$name        = $field['htmlvar_name'];
		$prefix      = $name . '_';
		$is_required = ! empty( $field['is_required'] );
		$required    = $is_required ? ' <span class="text-danger">*</span>' : '';

		// Get default location for placeholders
		$location = geodirectory()->locations->get_default();

		// Fallback to default location if empty and field is required
		if ( empty( $country ) && ! empty( $location->country ) && $is_required ) {
			$country = $location->country;
		}
		if ( empty( $region ) && ! empty( $location->region ) && $is_required ) {
			$region = $location->region;
		}
		if ( empty( $city ) && ! empty( $location->city ) && $is_required ) {
			$city = $location->city;
		}

		// Country
		$html .= aui()->select( array(
			'id'               => $prefix . "country",
			'name'             => "country",
			'placeholder'      => esc_attr__( 'Choose a country&hellip;', 'geodirectory' ),
			'value'            => esc_attr( stripslashes( $country ) ),
			'required'         => $is_required,
			'label'            => esc_attr__( 'Country', 'geodirectory' ) . $required,
			'label_type'       => $label_type,
			'help_text'        => __( 'Click on above field and type to filter list.', 'geodirectory' ),
			'options'          => geodir_get_country_dl( $country, $prefix ),
			'select2'          => true,
			'extra_attributes' => array(
				'data-address-type' => 'country',
				'field_type'        => $field['field_type'],
				'data-select'       => '{"searchEnabled":true}',
			),
			'wrap_attributes'  => function_exists( 'geodir_conditional_field_attrs' ) ? geodir_conditional_field_attrs( $field, 'country', 'select' ) : array()
		) );

		// Region
		$html .= aui()->input( array(
			'type'             => 'text',
			'id'               => $prefix . "region",
			'name'             => "region",
			'value'            => esc_attr( stripslashes( $region ) ),
			'required'         => $is_required,
			'label_show'       => true,
			'label'            => esc_attr__( 'Region', 'geodirectory' ) . $required,
			'label_type'       => $label_type,
			'placeholder'      => ( ! empty( $location->region ) ? esc_attr( stripslashes( $location->region ) ) : '' ),
			'help_text'        => __( 'Enter listing region.', 'geodirectory' ),
			'extra_attributes' => array(
				'data-address-type' => 'region',
				'field_type'        => 'text',
				'data-tags'         => "false"
			),
			'wrap_attributes'  => function_exists( 'geodir_conditional_field_attrs' ) ? geodir_conditional_field_attrs( $field, 'region', 'select' ) : array()
		) );

		// City
		$html .= aui()->input( array(
			'type'             => 'text',
			'id'               => $prefix . "city",
			'name'             => "city",
			'value'            => esc_attr( stripslashes( $city ) ),
			'required'         => $is_required,
			'label_show'       => true,
			'label'            => esc_attr__( 'City', 'geodirectory' ) . $required,
			'label_type'       => $label_type,
			'placeholder'      => ( ! empty( $location->city ) ? esc_attr( stripslashes( $location->city ) ) : '' ),
			'help_text'        => __( 'Enter listing city.', 'geodirectory' ),
			'extra_attributes' => array(
				'data-address-type' => 'city',
				'field_type'        => 'text',
				'data-tags'         => "false"
			),
			'wrap_attributes'  => function_exists( 'geodir_conditional_field_attrs' ) ? geodir_conditional_field_attrs( $field, 'city', 'select' ) : array()
		) );

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
			echo geodirectory()->maps->render_add_listing_map( [
				'lat' => $lat,
				'lng' => $lng
			] );
			?>
		</div>

		<?php
		// Lat/Lng Inputs (often hidden via CSS class if show_latlng is false)
		$wrap_class = ( ! empty( $extra_fields['show_latlng'] ) || is_admin() ) ? '' : 'd-none gd-hidden-latlng';

		echo aui()->input( [
			'type'             => 'number',
			'name'             => 'latitude',
			'value'            => $lat,
			'label'            => __( 'Address Latitude', 'geodirectory' ),
			'label_type'       => 'horizontal',
			'wrap_class'       => $wrap_class,
			'extra_attributes' => [ 'step' => 'any', 'min' => '-90', 'max' => '90' ]
		] );

		echo aui()->input( [
			'type'             => 'number',
			'name'             => 'longitude',
			'value'            => $lng,
			'label'            => __( 'Address Longitude', 'geodirectory' ),
			'label_type'       => 'horizontal',
			'wrap_class'       => $wrap_class,
			'extra_attributes' => [ 'step' => 'any', 'min' => '-180', 'max' => '180' ]
		] );

		return ob_get_clean();
	}
}
