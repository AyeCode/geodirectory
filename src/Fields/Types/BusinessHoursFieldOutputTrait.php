<?php
/**
 * Business Hours Field Output Rendering Trait
 *
 * Handles output rendering for business hours fields with expandable/dropdown display.
 *
 * @package GeoDirectory\Fields\Types
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Fields\Types;

/**
 * Business hours field output methods.
 *
 * @since 3.0.0
 */
trait BusinessHoursFieldOutputTrait {

	/**
	 * Render the output HTML for business hours field type.
	 *
	 * Replaces: geodir_cf_business_hours()
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

		// Check if we have business hours data
		if ( empty( $gd_post->{$html_var} ) ) {
			return '';
		}

		$value = stripslashes_deep( $gd_post->{$html_var} );
		$business_hours = geodir_get_business_hours( $value, ( ! empty( $gd_post->country ) ? $gd_post->country : '' ) );

		if ( empty( $business_hours['days'] ) ) {
			return '';
		}

		// Return raw database value
		if ( ! empty( $output['raw'] ) ) {
			return $value;
		}

		$show_value = $this->is_block_demo() ? __( 'Open now', 'geodirectory' ) : $business_hours['extra']['today_range'];

		if ( empty( $show_value ) ) {
			return '';
		}

		$preview_class = $this->is_block_demo() ? 'text-success' : '';
		$offset = isset( $business_hours['extra']['offset'] ) ? (int) $business_hours['extra']['offset'] : '';
		$utc_offset = isset( $business_hours['extra']['utc_offset'] ) ? $business_hours['extra']['utc_offset'] : '';

		if ( ! empty( $business_hours['extra']['is_dst'] ) ) {
			$offset = isset( $business_hours['extra']['offset_dst'] ) ? (int) $business_hours['extra']['offset_dst'] : $offset;
			$utc_offset = isset( $business_hours['extra']['utc_offset_dst'] ) ? $business_hours['extra']['utc_offset_dst'] : $utc_offset;
		}

		$bh_expanded = $location == 'owntab' || strpos( $this->field_data['css_class'], 'gd-bh-expanded' ) !== false;
		$design_style = $this->get_design_style();
		$aui_bs5 = isset( $GLOBALS['aui_bs5'] ) && $GLOBALS['aui_bs5'];

		// Build CSS classes for dropdown/expanded display
		$dropdown_class = $design_style ? ' dropdown ' : '';
		$dropdown_toggle_class = $design_style ? ' dropdown-toggle nav-link ' : '';
		$dropdown_item_class = $design_style ? ' dropdown-item py-1 ' : '';
		$dropdown_item_inline_class = $design_style ? ' d-inline-block ' : '';

		if ( $aui_bs5 ) {
			$dropdown_item_mr_class = $design_style ? ' me-3 ' : '';
			$dropdown_item_float_class = $design_style ? ' float-end' : '';
		} else {
			$dropdown_item_mr_class = $design_style ? ' mr-3 ' : '';
			$dropdown_item_float_class = $design_style ? ' float-right' : '';
		}

		$dropdown_menu_class = $design_style ? ' dropdown-menu dropdown-caret-0 my-3 ' : '';

		// Adjust classes for expanded view
		if ( $design_style && $bh_expanded ) {
			$dropdown_class = '';
			$dropdown_menu_class = '';
			$dropdown_toggle_class = '';
		}

		$icon_data = $this->process_icon();
		$field_icon_style = $icon_data['style'];
		$field_icon_html = $icon_data['icon_html'];

		$extra_class = $location == 'owntab' || strpos( $this->field_data['css_class'], 'gd-bh-expanded' ) !== false ? ' gd-bh-expanded' : ' gd-bh-toggled';
		if ( ! empty( $business_hours['extra']['has_closed'] ) ) {
			$extra_class .= ' gd-bh-closed';
		}

		$css_class = isset( $this->field_data['css_class'] ) ? $this->field_data['css_class'] : '';

		$html = '<div class="geodir_post_meta gd-bh-show-field ' . esc_attr( $css_class ) . ' geodir-field-' . esc_attr( $html_var ) . $extra_class . $dropdown_class . '" style="">';

		// Dropdown toggle (if design style)
		if ( $design_style ) {
			$html .= '<a class="text-reset ' . $dropdown_toggle_class . ' text-truncate" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
		}

		$html .= '<span class="geodir-i-business_hours pe-1 geodir-i-biz-hours ' . $preview_class . '" style="' . esc_attr( $field_icon_style ) . '">' . $field_icon_html . '<font></font>' . ': </span>';
		$html .= '<span class="gd-bh-expand-range ' . $preview_class . '" data-offset="' . esc_attr( $utc_offset ) . '" data-offsetsec="' . esc_attr( $offset ) . '" title="' . esc_attr__( 'Expand opening hours', 'geodirectory' ) . '"><span class="gd-bh-today-range gv-secondary">' . $show_value . '</span>';

		if ( ! $design_style ) {
			$html .= '<span class="gd-bh-expand"><i class="fas fa-caret-up" aria-hidden="true"></i><i class="fas fa-caret-down" aria-hidden="true"></i></span>';
		}

		$html .= '</span>';

		if ( $design_style ) {
			$html .= '</a>';
		}

		// Business hours details (dropdown menu or expanded)
		$html .= '<div class="gd-bh-open-hours ' . $dropdown_menu_class . '" style="min-width:250px;">';

		foreach ( $business_hours['days'] as $day => $slots ) {
			/**
			 * Filter business hours slot display day name.
			 *
			 * @since 2.3.29
			 *
			 * @param string $day_short Day short name.
			 * @param string $day       Day full name.
			 * @param array  $slots     Day slots data.
			 * @param string $location  Output location.
			 * @param array  $cf        Custom field data.
			 */
			$day_name = apply_filters( 'geodir_output_business_hours_slot_day_name', $slots['day_short'], $day, $slots, $location, $this->field_data );

			$class = '';
			if ( ! empty( $slots['closed'] ) ) {
				$class .= 'gd-bh-days-closed ';
			}

			$html .= '<div data-day="' . esc_attr( $slots['day_no'] ) . '" data-closed="' . esc_attr( $slots['closed'] ) . '" class="' . $dropdown_item_class . ' gd-bh-days-list d-flex justify-content-between ' . trim( $class ) . '">';
			$html .= '<div class="gd-bh-days-d ' . $dropdown_item_inline_class . $dropdown_item_mr_class . '">' . esc_html( $day_name ) . '</div>';
			$html .= '<div class="gd-bh-slots ' . $dropdown_item_inline_class . $dropdown_item_float_class . '">';

			foreach ( $slots['slots'] as $i => $slot ) {
				$attrs = '';
				$slot_class = '';

				if ( ! empty( $slot['time'] ) ) {
					$attrs .= 'data-open="' . esc_attr( $slot['time'][0] ) . '" data-close="' . esc_attr( $slot['time'][1] ) . '"';

					// Next day close (when close time is before open time or equals open for 24h)
					if ( (int) $slot['time'][0] == (int) $slot['time'][1] || (int) $slot['time'][1] < (int) $slot['time'][0] ) {
						$slot_class .= ' gd-bh-next-day';
					}
				}

				$html .= '<div ' . $attrs . ' class="gd-bh-slot' . $slot_class . '"><div class="gd-bh-slot-r">' . esc_html( $slot['range'] ) . '</div>';
				$html .= '</div>';
			}

			$html .= '</div></div>';
		}

		$html .= '</div></div>';

		return $html;
	}

	/**
	 * Set demo content for block editor.
	 *
	 * @param object $gd_post  Post object.
	 * @param string $html_var Field htmlvar_name.
	 * @return object Modified post object.
	 */
	protected function set_demo_content( $gd_post, $html_var ) {
		// Demo business hours JSON with typical hours
		$demo_hours = [
			'days' => [
				'Mo' => [ [ 'opens' => '09:00', 'closes' => '17:00' ] ],
				'Tu' => [ [ 'opens' => '09:00', 'closes' => '17:00' ] ],
				'We' => [ [ 'opens' => '09:00', 'closes' => '17:00' ] ],
				'Th' => [ [ 'opens' => '09:00', 'closes' => '17:00' ] ],
				'Fr' => [ [ 'opens' => '09:00', 'closes' => '17:00' ] ],
				'Sa' => [ [ 'opens' => '10:00', 'closes' => '15:00' ] ],
				'Su' => [],
			],
		];

		$gd_post->{$html_var} = wp_json_encode( $demo_hours );

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
