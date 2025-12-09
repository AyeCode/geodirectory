<?php
namespace AyeCode\GeoDirectory\Fields\Types;

use AyeCode\GeoDirectory\Fields\Abstracts\AbstractFieldType;

/**
 * Class DatepickerField
 *
 * Handles Datepicker inputs and outputs using AUI (Flatpickr).
 * Replaces geodir_cfi_datepicker() and geodir_cf_datepicker().
 */
class DatepickerField extends AbstractFieldType {

	use TextFieldOutputTrait;

	public function render_input() {
		$args         = $this->get_aui_args();
		$extra_fields = $this->get_extra_fields();
		$value        = $this->value;

		// Handle default "zero" date
		if ( $value === '0000-00-00' ) {
			$value = '';
			$args['value'] = '';
		}

		// 1. Date Format Handling
		$date_format = ! empty( $extra_fields['date_format'] ) ? $extra_fields['date_format'] : 'yy-mm-dd';

		// Convert format (Legacy Support + Flatpickr compatibility)
		// Logic ported from geodir_date_format_php_to_aui / legacy checks
		$date_format_len = strlen( str_replace( ' ', '', $date_format ) );
		if ( $date_format_len > 5 ) {
			// It's likely an old jQuery UI format (e.g. 'dd/mm/yy'), convert to PHP format
			$search  = [ 'dd', 'd', 'DD', 'mm', 'm', 'MM', 'yy' ];
			$replace = [ 'd', 'j', 'l', 'm', 'n', 'F', 'Y' ];
			$date_format = str_replace( $search, $replace, $date_format );
		} else {
			// Convert PHP to Flatpickr friendly format
			$date_format = $this->php_to_flatpickr_format( $date_format );
		}

		// 2. Setup Flatpickr Attributes
		$args['type'] = 'datepicker';

		$args['extra_attributes']['data-alt-input']   = 'true';
		$args['extra_attributes']['data-alt-format']  = $date_format; // The format user sees
		$args['extra_attributes']['data-date-format'] = 'Y-m-d';      // The format sent to DB

		// 3. Min/Max Date Logic (Date Range)
		if ( ! empty( $extra_fields['date_range'] ) ) {
			$year_range = $this->parse_year_range( $extra_fields['date_range'] );

			if ( ! empty( $year_range['min_year'] ) ) {
				$args['extra_attributes']['data-min-date'] = $year_range['min_year'] . '-01-01';
			}
			if ( ! empty( $year_range['max_year'] ) ) {
				$args['extra_attributes']['data-max-date'] = $year_range['max_year'] . '-12-31';
			}
		}

		// 4. Filters
		// geodir_cfi_datepicker_extra_attrs
		$args['extra_attributes'] = apply_filters( 'geodir_cfi_datepicker_extra_attrs', $args['extra_attributes'], $this->field_data );

		$html = '';
		$hook_name = "geodir_custom_field_input_datepicker_{$this->field_data['htmlvar_name']}";
		if ( has_filter( $hook_name ) ) {
			$html = apply_filters( $hook_name, $html, $this->field_data );
		}

		if ( empty( $html ) ) {
			$html = aui()->input( $args );
		}

		return $html;
	}

	public function sanitize( $value ) {
		if ( empty( $value ) ) {
			return '';
		}
		// Value comes in as Y-m-d from the flatpickr 'data-date-format' setting
		return sanitize_text_field( $value );
	}

	/**
	 * Helper: Convert PHP date format to AUI/Flatpickr friendly format.
	 * * @param string $format
	 * @return string
	 */
	protected function php_to_flatpickr_format( $format ) {
		$replacements = [
			// Day
			'd' => 'd', 'D' => 'D', 'j' => 'j', 'l' => 'l',
			// Month
			'F' => 'F', 'm' => 'm', 'M' => 'M', 'n' => 'n',
			// Year
			'Y' => 'Y', 'y' => 'y',
			// Special cases often found in GD settings
			'yy' => 'Y', 'mm' => 'm', 'dd' => 'd'
		];

		return strtr( $format, $replacements );
	}

	/**
	 * Helper: Parse date range string (e.g. "c-5:c+5").
	 * Logic ported from geodir_input_parse_year_range.
	 *
	 * @param string $range
	 * @return array
	 */
	protected function parse_year_range( $range ) {
		$current_year = (int) date( 'Y' );
		$year_range   = [ 'min_year' => 0, 'max_year' => 0 ];
		$parts        = explode( ":", trim( $range ) );

		if ( isset( $parts[0] ) ) $year_range['min_year'] = $this->parse_year( $parts[0], $current_year );
		if ( isset( $parts[1] ) ) $year_range['max_year'] = $this->parse_year( $parts[1], $current_year );

		// Swap if min > max
		if ( ! empty( $year_range['min_year'] ) && ! empty( $year_range['max_year'] ) ) {
			$years = array_values( $year_range );
			$year_range['min_year'] = min( $years );
			$year_range['max_year'] = max( $years );
		}

		return $year_range;
	}

	/**
	 * Helper: Parse individual year logic (e.g. "c+10").
	 * * @param string $str
	 * @param int $current_year
	 * @return int
	 */
	protected function parse_year( $str, $current_year ) {
		$str = str_replace( "c", (string) $current_year, $str );
		$year = 0;

		if ( strpos( $str, '-' ) !== false ) {
			$parts = explode( "-", $str );
			$base = ( strlen( trim( $parts[0] ) ) == 4 ) ? (int) $parts[0] : $current_year;
			$diff = (int) $parts[1];
			$year = $base - $diff;
		} elseif ( strpos( $str, '+' ) !== false ) {
			$parts = explode( "+", $str );
			$base = ( strlen( trim( $parts[0] ) ) == 4 ) ? (int) $parts[0] : $current_year;
			$diff = (int) $parts[1];
			$year = $base + $diff;
		} else {
			$val = (int) trim( $str );
			if ( strlen( (string) $val ) == 4 ) {
				$year = $val;
			} elseif( $val !== 0 ) {
				$year = $current_year + $val;
			}
		}

		return ( strlen( (string) $year ) == 4 ) ? $year : 0;
	}
}
