<?php
/**
 * Address Field Output Rendering Trait
 *
 * Handles output rendering for address fields with complex template system.
 *
 * @package GeoDirectory\Fields\Types
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Fields\Types;

/**
 * Address field output methods.
 *
 * @since 3.0.0
 */
trait AddressFieldOutputTrait {

	/**
	 * Render the output HTML for address field type.
	 *
	 * Replaces: geodir_cf_address()
	 *
	 * @param object|array $gd_post GeoDirectory post object with custom fields already loaded.
	 * @param array        $args    Output arguments:
	 *                              - 'show' (string|array): What to display.
	 *                              - 'location' (string): Output location.
	 * @return string
	 */
	public function render_output( $gd_post, $args = [] ) {
		// Use the $gd_post directly (no DB call needed - already has all custom fields!)
		if ( ! is_object( $gd_post ) ) {
			$gd_post = (object) $gd_post;
		}

		if ( empty( $gd_post ) ) {
			return '';
		}

		// Parse args with defaults
		$args = wp_parse_args( $args, [
			'show'     => '',
			'location' => '',
		] );

		$location = $args['location'];
		$html_var = $this->field_data['htmlvar_name'];

		// Parse output arguments (convert string to array)
		$output = $this->parse_output_args( $args['show'] );

		// Block demo content
		if ( $this->is_block_demo() ) {
			$gd_post = $this->set_demo_content( $gd_post, $html_var );
		}

		$html = '';

		// Apply custom filters first
		$html = $this->apply_output_filters( $html, $location, $output );

		// If filters provided custom HTML, return it
		if ( ! empty( $html ) ) {
			return $html;
		}

		// Check if we have any address data
		if ( empty( $gd_post->street ) && empty( $gd_post->city ) && empty( $gd_post->region ) && empty( $gd_post->country ) ) {
			return '';
		}

		// Get extra field settings for address display
		$extra_fields = geodir_parse_cf_extra_fields( $this->field_data );

		// Determine what parts to show
		$show_parts = $this->get_address_show_parts( $extra_fields, $gd_post );

		// Build address fields array
		$address_fields = $this->build_address_fields( $gd_post, $show_parts );

		// Get address template
		$address_template = ! empty( $this->field_data['address_template'] )
			? $this->field_data['address_template']
			: '%%street_br%% %%street2_br%% %%neighbourhood_br%% %%city_br%% %%region_br%% %%zip_br%% %%country%%';

		$address_template = apply_filters( 'geodir_cf_address_template', $address_template, $this->field_data, $location );

		// Process address template
		$address_output = $this->process_address_template( $address_template, $address_fields, $gd_post );

		// Render private address (respects privacy settings)
		$address_output = \geodir_post_address( $address_output, 'address', $gd_post );

		// Check if address is empty after privacy filtering
		$plain_value = wp_strip_all_tags( $address_output, true );
		if ( $plain_value == '' ) {
			return '';
		}

		// Return raw database value
		if ( ! empty( $output['raw'] ) ) {
			$address_output = str_replace( '<br>', '', $address_output );
			return stripslashes( wp_strip_all_tags( $address_output, true ) );
		}

		// Return stripped value
		if ( ! empty( $output['strip'] ) ) {
			$address_output = str_replace( '<br>', ',', $address_output );
			return stripslashes( wp_strip_all_tags( $address_output, true ) );
		}

		// Build full HTML output
		$design_style = $this->get_design_style();
		$icon_data = $this->process_icon();
		$field_icon_style = $icon_data['style'];
		$field_icon_html = $icon_data['icon_html'];

		// Default address icon
		if ( ! $field_icon_html && ! $field_icon_style ) {
			$field_icon_html = $design_style ? '<i class="fas fa-home fa-fw" aria-hidden="true"></i> ' : '<i class="fas fa-home" aria-hidden="true"></i>';
		}

		$css_class = isset( $this->field_data['css_class'] ) ? $this->field_data['css_class'] : '';

		$html = '<div class="geodir_post_meta ' . esc_attr( $css_class ) . ' geodir-field-' . esc_attr( $html_var ) . '" itemscope itemtype="http://schema.org/PostalAddress">';

		$maybe_secondary_class = isset( $output['icon'] ) ? 'gv-secondary' : '';

		// Icon
		if ( $output == '' || isset( $output['icon'] ) ) {
			$html .= '<span class="geodir_post_meta_icon geodir-i-address" style="' . esc_attr( $field_icon_style ) . '">' . $field_icon_html;
		}

		// Label
		if ( $output == '' || isset( $output['label'] ) ) {
			$frontend_title = isset( $this->field_data['frontend_title'] ) ? trim( $this->field_data['frontend_title'] ) : '';
			if ( $frontend_title ) {
				$html .= '<span class="geodir_post_meta_title ' . esc_attr( $maybe_secondary_class ) . '">' . __( $frontend_title, 'geodirectory' ) . ': </span>';
			}
		}

		if ( $output == '' || isset( $output['icon'] ) ) {
			$html .= '</span>';
		}

		// Value
		if ( $output == '' || isset( $output['value'] ) ) {
			// Add map link if requested
			if ( ! empty( $output['link'] ) ) {
				$value = stripslashes( $address_output );
				$address = normalize_whitespace( wp_strip_all_tags( $value ) );
				$map_link = 'https://www.google.com/maps?q=' . urlencode( $address );

				$map_link = apply_filters( 'geodir_custom_field_output_address_map_link', $map_link, $address, $gd_post, $this->field_data );

				$html .= '<a href="' . esc_url( $map_link ) . '" target="_blank" title="' . esc_attr__( 'View on map', 'geodirectory' ) . '">';
				$html .= $value;
				$html .= '</a>';
			} else {
				$html .= stripslashes( $address_output );
			}
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Determine which address parts to show based on field settings.
	 *
	 * @param array  $extra_fields Extra field settings.
	 * @param object $gd_post      Post object.
	 * @return array Array of show settings.
	 */
	protected function get_address_show_parts( $extra_fields, $gd_post ) {
		$show_parts = [
			'street'        => apply_filters( 'geodir_show_street_in_address', true, $gd_post ),
			'street2'       => false,
			'city'          => false,
			'region'        => false,
			'country'       => false,
			'zip'           => false,
			'neighbourhood' => false,
		];

		if ( ! empty( $extra_fields ) && is_array( $extra_fields ) ) {
			if ( isset( $extra_fields['show_street2'] ) && $extra_fields['show_street2'] ) {
				$show_parts['street2'] = true;
			}
			$show_parts['street2'] = apply_filters( 'geodir_show_street2_in_address', $show_parts['street2'] );

			if ( isset( $extra_fields['show_city'] ) && $extra_fields['show_city'] ) {
				$show_parts['city'] = true;
			}
			$show_parts['city'] = apply_filters( 'geodir_show_city_in_address', $show_parts['city'] );

			if ( isset( $extra_fields['show_region'] ) && $extra_fields['show_region'] ) {
				$show_parts['region'] = true;
			}
			$show_parts['region'] = apply_filters( 'geodir_show_region_in_address', $show_parts['region'] );

			if ( isset( $extra_fields['show_country'] ) && $extra_fields['show_country'] ) {
				$show_parts['country'] = true;
			}
			$show_parts['country'] = apply_filters( 'geodir_show_country_in_address', $show_parts['country'] );

			if ( isset( $extra_fields['show_zip'] ) && $extra_fields['show_zip'] ) {
				$show_parts['zip'] = true;
			}
			$show_parts['zip'] = apply_filters( 'geodir_show_zip_in_address', $show_parts['zip'] );

			if ( isset( $extra_fields['show_neighbourhood'] ) && $extra_fields['show_neighbourhood'] ) {
				$show_parts['neighbourhood'] = true;
			}
		}

		return $show_parts;
	}

	/**
	 * Build address fields array with schema.org markup.
	 *
	 * @param object $gd_post    Post object.
	 * @param array  $show_parts Show settings.
	 * @return array Address fields.
	 */
	protected function build_address_fields( $gd_post, $show_parts ) {
		$address_fields = [];

		if ( isset( $gd_post->post_title ) ) {
			$address_fields['post_title'] = '<span itemprop="placeName">' . esc_html( $gd_post->post_title ) . '</span>';
		}

		if ( $show_parts['street'] && isset( $gd_post->street ) && $gd_post->street ) {
			$address_fields['street'] = '<span itemprop="streetAddress">' . esc_html( $gd_post->street ) . '</span>';
		}

		if ( $show_parts['street2'] && isset( $gd_post->street2 ) && $gd_post->street2 ) {
			$address_fields['street2'] = '<span itemprop="streetAddress2">' . esc_html( $gd_post->street2 ) . '</span>';
		}

		if ( $show_parts['neighbourhood'] && isset( $gd_post->neighbourhood ) && $gd_post->neighbourhood ) {
			$address_fields['neighbourhood'] = '<span itemprop="addressNeighbourhood">' . esc_html( $gd_post->neighbourhood ) . '</span>';
		}

		if ( $show_parts['city'] && isset( $gd_post->city ) && $gd_post->city ) {
			$address_fields['city'] = '<span itemprop="addressLocality">' . esc_html( $gd_post->city ) . '</span>';
		}

		if ( $show_parts['region'] && isset( $gd_post->region ) && $gd_post->region ) {
			$address_fields['region'] = '<span itemprop="addressRegion">' . esc_html( $gd_post->region ) . '</span>';
		}

		if ( $show_parts['zip'] && isset( $gd_post->zip ) && $gd_post->zip ) {
			$address_fields['zip'] = '<span itemprop="postalCode">' . esc_html( $gd_post->zip ) . '</span>';
		}

		if ( $show_parts['country'] && isset( $gd_post->country ) && $gd_post->country ) {
			$address_fields['country'] = '<span itemprop="addressCountry">' . esc_html( __( $gd_post->country, 'geodirectory' ) ) . '</span>';
		}

		if ( isset( $gd_post->latitude ) && $gd_post->latitude ) {
			$address_fields['latitude'] = '<span itemprop="addressLatitude">' . esc_html( $gd_post->latitude ) . '</span>';
		}

		if ( isset( $gd_post->longitude ) && $gd_post->longitude ) {
			$address_fields['longitude'] = '<span itemprop="addressLongitude">' . esc_html( $gd_post->longitude ) . '</span>';
		}

		return apply_filters( 'geodir_custom_field_output_address_fields', $address_fields, $gd_post, $this->field_data, $args['location'] ?? '' );
	}

	/**
	 * Process address template with field values.
	 *
	 * @param string $template       Address template.
	 * @param array  $address_fields Address field values.
	 * @param object $gd_post        Post object.
	 * @return string Processed address.
	 */
	protected function process_address_template( $template, $address_fields, $gd_post ) {
		$address_items = [ 'post_title', 'street', 'street2', 'neighbourhood', 'city', 'region', 'zip', 'country', 'latitude', 'longitude' ];

		$address_fields_extra = [
			'c'     => ', ',       // Value with comma
			'br'    => '<br>',     // Value with line break
			'brc'   => ',<br>',    // Value with comma & line break
			'space' => ' ',        // Value with space
			'dash'  => ' - '       // Value with dash
		];

		$address_fields_extra = apply_filters( 'geodir_custom_field_output_address_fields_extra', $address_fields_extra, $gd_post, $this->field_data, $args['location'] ?? '' );

		// Replace template variables
		foreach ( $address_items as $type ) {
			$value = isset( $address_fields[ $type ] ) ? $address_fields[ $type ] : '';
			$template = str_replace( '%%' . $type . '%%', $value, $template );

			// Handle variations with separators
			foreach ( $address_fields_extra as $_var => $_rep ) {
				$template = str_replace( '%%' . $_var . '_' . $type . '%%', ( $value != '' ? $_rep . $value : '' ), $template );
				$template = str_replace( '%%' . $type . '_' . $_var . '%%', ( $value != '' ? $value . $_rep : '' ), $template );
			}
		}

		// Replace separator variables
		foreach ( $address_fields_extra as $_var => $_rep ) {
			$template = str_replace( '%%' . $_var . '%%', $_rep, $template );
		}

		return $template;
	}

	/**
	 * Set demo content for block editor.
	 *
	 * @param object $gd_post  Post object.
	 * @param string $html_var Field htmlvar_name.
	 * @return object Modified post object.
	 */
	protected function set_demo_content( $gd_post, $html_var ) {
		$gd_post->{$html_var}    = '123 Demo Street';
		$gd_post->street         = '123 Demo Street';
		$gd_post->street2        = 'Street line 2';
		$gd_post->region         = 'Pennsylvania';
		$gd_post->city           = 'Philadelphia';
		$gd_post->zip            = '19107';
		$gd_post->neighbourhood  = 'Chinatown';

		return $gd_post;
	}

	/**
	 * Helper methods from AbstractFieldOutput.
	 */
	abstract protected function parse_output_args( $args );
	abstract protected function apply_output_filters( $html, $location, $output );
	abstract protected function process_icon();
	abstract protected function is_block_demo();
	abstract protected function get_design_style();
}
