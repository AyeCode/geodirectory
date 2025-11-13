<?php
/**
 * Business Hours Manager
 *
 * Handles business hours operations, timezones, and time formatting.
 *
 * @package GeoDirectory\Core
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core;

use AyeCode\GeoDirectory\Core\Utils\Settings;

/**
 * Business Hours service class.
 */
final class BusinessHours {
	private Settings $settings;

	/**
	 * Constructor.
	 *
	 * @param Settings $settings The settings utility class.
	 */
	public function __construct( Settings $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Return the week days.
	 *
	 * @since 3.0.0
	 *
	 * @param bool $untranslated If the returned day names should be translated or not.
	 * @return array The days of the week.
	 */
	public function get_weekdays( bool $untranslated = false ): array {
		if ( $untranslated ) {
			$weekdays = array(
				'Mo'    => 'Monday',
				'Tu'  	=> 'Tuesday',
				'We' 	=> 'Wednesday',
				'Th'  	=> 'Thursday',
				'Fr'    => 'Friday',
				'Sa'  	=> 'Saturday',
				'Su'    => 'Sunday'
			);
		} else {
			$weekdays = array(
				'Mo'    => __( 'Monday' ),
				'Tu'  	=> __( 'Tuesday' ),
				'We' 	=> __( 'Wednesday' ),
				'Th'  	=> __( 'Thursday' ),
				'Fr'    => __( 'Friday' ),
				'Sa'  	=> __( 'Saturday' ),
				'Su'    => __( 'Sunday' )
			);
		}

		/**
		 * Filters the weekdays array.
		 *
		 * @since 2.0.0
		 *
		 * @param array $weekdays The days of the week.
		 * @param bool $untranslated If the returned day names should be translated or not.
		 */
		return apply_filters( 'geodir_get_weekdays', $weekdays, $untranslated );
	}

	/**
	 * Return three letter abbreviation week day.
	 *
	 * @since 3.0.0
	 *
	 * @param bool $untranslated If the returned day names should be translated or not.
	 * @return array The days of the week.
	 */
	public function get_short_weekdays( bool $untranslated = false ): array {
		if ( $untranslated ) {
			$weekdays = array(
				'Mo'    => 'Mon',
				'Tu'  	=> 'Tue',
				'We' 	=> 'Wed',
				'Th'  	=> 'Thu',
				'Fr'    => 'Fri',
				'Sa'  	=> 'Sat',
				'Su'    => 'Sun'
			);
		} else {
			$weekdays = array(
				'Mo'    => __( 'Mon' ),
				'Tu'  	=> __( 'Tue' ),
				'We' 	=> __( 'Wed' ),
				'Th'  	=> __( 'Thu' ),
				'Fr'    => __( 'Fri' ),
				'Sa'  	=> __( 'Sat' ),
				'Su'    => __( 'Sun' )
			);
		}

		/**
		 * Filters the short weekdays array.
		 *
		 * @since 2.0.0.97
		 *
		 * @param array $weekdays The days of the week.
		 * @param bool $untranslated If the returned day names should be translated or not.
		 */
		return apply_filters( 'geodir_get_short_weekdays', $weekdays, $untranslated );
	}

	/**
	 * Return the day names in 2 digits.
	 *
	 * @since 3.0.0
	 *
	 * @return array The days of the week.
	 */
	public function day_short_names(): array {
		$days = array(
			'1'	=> 'Mo',
			'2'	=> 'Tu',
			'3' 	=> 'We',
			'4'  => 'Th',
			'5'  => 'Fr',
			'6'  => 'Sa',
			'7'  => 'Su'
		);

		/**
		 * Filters the day short names array.
		 *
		 * @since 2.0.0
		 *
		 * @param array $days The days of the week.
		 */
		return apply_filters( 'geodir_day_short_names', $days );
	}

	/**
	 * Get UTC Offset without DST (Daylight Savings Time).
	 *
	 * @since 3.0.0
	 *
	 * @param string $offset The offset string.
	 * @param bool $formatted Format the offset.
	 * @return string|int The UTC offset.
	 */
	public function utc_offset( string $offset = '', bool $formatted = true ) {
		if ( empty( $offset ) ) {
			$offset = (string) get_option( 'gmt_offset' );
		}

		if ( ! is_numeric( $offset ) ) {
			$timezone_string = get_option( 'timezone_string' );
			if ( $timezone_string ) {
				$offset = geodir_timezone_default_utc_offset( $timezone_string );
			} else {
				$offset = (string) get_option( 'gmt_offset' );
			}
		}

		$offset = (float) $offset;
		$hours   = (int) $offset;
		$minutes = ( $offset - $hours );
		$sign    = ( $offset < 0 ) ? '-' : '+';
		$abs_hour  = abs( $hours );
		$abs_mins  = abs( $minutes * 60 );

		if ( ! $formatted ) {
			return $offset;
		} else {
			return sprintf( '%s%02d:%02d', $sign, $abs_hour, $abs_mins );
		}
	}

	/**
	 * Get GMT Offset.
	 *
	 * @since 3.0.0
	 *
	 * @param string $offset The offset string.
	 * @param bool $formatted Format the offset.
	 * @return string|float The GMT offset.
	 */
	public function gmt_offset( string $offset = '', bool $formatted = true ) {
		if ( empty( $offset ) ) {
			$offset = (string) get_option( 'gmt_offset' );
		}

		if ( ! is_numeric( $offset ) ) {
			$timezone_string = get_option( 'timezone_string' );
			if ( $timezone_string ) {
				$date_time_zone = new \DateTimeZone( $timezone_string );
				$date_time = new \DateTime( "now", $date_time_zone );
				$offset = $date_time_zone->getOffset( $date_time ) / 3600;
			} else {
				$offset = (string) get_option( 'gmt_offset' );
			}
		}

		$offset = (float) $offset;

		if ( ! $formatted ) {
			return $offset;
		}

		$hours   = (int) $offset;
		$minutes = ( $offset - $hours );
		$sign    = ( $offset < 0 ) ? '-' : '+';
		$abs_hour  = abs( $hours );
		$abs_mins  = abs( $minutes * 60 );

		return sprintf( '%s%02d:%02d', $sign, $abs_hour, $abs_mins );
	}

	/**
	 * Convert seconds to HH:MM format.
	 *
	 * @since 3.0.0
	 *
	 * @param int $seconds Seconds.
	 * @param bool $abs Use absolute value.
	 * @return string Time in HH:MM format.
	 */
	public function seconds_to_hhmm( int $seconds, bool $abs = false ): string {
		if ( $abs ) {
			$seconds = abs( $seconds );
		}

		$hours = floor( $seconds / 3600 );
		$minutes = floor( ( $seconds / 60 ) % 60 );

		return sprintf( "%02d:%02d", $hours, $minutes );
	}

	/**
	 * Get UTC offset with DST.
	 *
	 * @since 3.0.0
	 *
	 * @param string $time_zone Timezone string.
	 * @param bool $formatted Format the offset.
	 * @return string|int The UTC offset with DST.
	 */
	public function utc_offset_dst( string $time_zone = 'Europe/Berlin', bool $formatted = false ) {
		if ( empty( $time_zone ) ) {
			$time_zone = get_option( 'timezone_string' );
		}

		if ( empty( $time_zone ) ) {
			return $this->gmt_offset( '', $formatted );
		}

		$date_time_zone = new \DateTimeZone( $time_zone );
		$date_time = new \DateTime( "now", $date_time_zone );
		$time_offset = $date_time_zone->getOffset( $date_time );

		if ( ! $formatted ) {
			return $time_offset;
		}

		$hours = floor( $time_offset / 3600 );
		$mins = floor( $time_offset / 60 ) % 60;
		$offset = sprintf( '%+03d:%02d', $hours, $mins );

		return $offset;
	}

	/**
	 * Get WordPress GMT offset.
	 *
	 * @since 3.0.0
	 *
	 * @param bool $formatted Format the offset.
	 * @return string|float The GMT offset.
	 */
	public function wp_gmt_offset( bool $formatted = true ) {
		$offset = get_option( 'gmt_offset', 0 );

		if ( ! $formatted ) {
			return (float) $offset;
		}

		$hours   = (int) $offset;
		$minutes = abs( ( $offset - (int) $offset ) * 60 );
		$offset = sprintf( '%+03d:%02d', $hours, $minutes );

		return $offset;
	}

	/**
	 * Get default UTC offset for a timezone.
	 *
	 * @since 3.0.0
	 *
	 * @param string $timezone Timezone string.
	 * @return float The UTC offset.
	 */
	public function timezone_default_utc_offset( string $timezone = '' ): float {
		if ( empty( $timezone ) ) {
			$timezone = get_option( 'timezone_string' );
		}

		if ( empty( $timezone ) ) {
			return (float) get_option( 'gmt_offset', 0 );
		}

		try {
			$date_time_zone = new \DateTimeZone( $timezone );
			$transitions = $date_time_zone->getTransitions();

			if ( ! empty( $transitions ) && isset( $transitions[0]['offset'] ) ) {
				return $transitions[0]['offset'] / 3600;
			}
		} catch ( \Exception $e ) {
			return (float) get_option( 'gmt_offset', 0 );
		}

		return (float) get_option( 'gmt_offset', 0 );
	}

	/**
	 * Get default business hours values.
	 *
	 * @since 3.0.0
	 *
	 * @return array Default business hours array.
	 */
	public function default_values(): array {
		$values = array(
			'Mo' => array( 'opens' => '09:00', 'closes' => '17:00' ),
			'Tu' => array( 'opens' => '09:00', 'closes' => '17:00' ),
			'We' => array( 'opens' => '09:00', 'closes' => '17:00' ),
			'Th' => array( 'opens' => '09:00', 'closes' => '17:00' ),
			'Fr' => array( 'opens' => '09:00', 'closes' => '17:00' ),
			'Sa' => array( 'opens' => '', 'closes' => '' ),
			'Su' => array( 'opens' => '', 'closes' => '' ),
		);

		/**
		 * Filters the default business hours values.
		 *
		 * @since 2.0.0
		 *
		 * @param array $values Default business hours array.
		 */
		return apply_filters( 'geodir_bh_default_values', $values );
	}

	/**
	 * Convert array to schema format.
	 *
	 * @since 3.0.0
	 *
	 * @param array $schema_input Business hours array.
	 * @return string Schema formatted string.
	 */
	public function array_to_schema( array $schema_input ): string {
		global $geodirectory;

		$schema = '';
		if ( ! empty( $schema_input ) ) {
			$all_days = $this->get_weekdays( true );
			foreach ( $schema_input as $day => $hours ) {
				if ( isset( $all_days[ $day ] ) && ! empty( $hours ) && is_array( $hours ) && ! empty( $hours['opens'] ) && ! empty( $hours['closes'] ) ) {
					if ( $schema != '' ) {
						$schema .= ' ';
					}
					$schema .= $day . ' ' . $hours['opens'] . '-' . $hours['closes'];
				}
			}
		}

		return $schema;
	}

	/**
	 * Convert schema format to array.
	 *
	 * @since 3.0.0
	 *
	 * @param string $schema Schema formatted string.
	 * @param string $country Country code.
	 * @return array Business hours array.
	 */
	public function schema_to_array( string $schema, string $country = '' ): array {
		$schema_array = array();

		if ( ! empty( $schema ) ) {
			$properties = explode( ' ', trim( $schema ) );

			foreach ( $properties as $property ) {
				$days = $this->parse_property( $property );
				if ( ! empty( $days ) ) {
					foreach ( $days as $day => $hours ) {
						if ( isset( $schema_array[ $day ] ) ) {
							$schema_array[ $day ][] = $hours;
						} else {
							$schema_array[ $day ] = array( $hours );
						}
					}
				}
			}
		}

		/**
		 * Filters the schema to array conversion.
		 *
		 * @since 2.0.0
		 *
		 * @param array $schema_array Business hours array.
		 * @param string $schema Schema string.
		 * @param string $country Country code.
		 */
		return apply_filters( 'geodir_schema_to_array', $schema_array, $schema, $country );
	}

	/**
	 * Parse property string.
	 *
	 * @since 3.0.0
	 *
	 * @param string $str Property string.
	 * @return array Parsed property.
	 */
	public function parse_property( string $str ): array {
		$parts = explode( ' ', $str );
		if ( empty( $parts ) || count( $parts ) < 2 ) {
			return array();
		}

		$days_str = $parts[0];
		$hours_str = $parts[1];

		$days = $this->parse_days( $days_str );
		if ( empty( $days ) ) {
			return array();
		}

		$hours = $this->parse_hours( $hours_str );
		if ( empty( $hours ) ) {
			return array();
		}

		$property = array();
		foreach ( $days as $day ) {
			$property[ $day ] = $hours;
		}

		return $property;
	}

	/**
	 * Parse days string.
	 *
	 * @since 3.0.0
	 *
	 * @param string $days_str Days string.
	 * @return array Parsed days.
	 */
	public function parse_days( string $days_str ): array {
		if ( strpos( $days_str, ',' ) !== false ) {
			$days = array();
			$parts = explode( ',', $days_str );
			foreach ( $parts as $part ) {
				$_days = $this->parse_days_range( $part );
				if ( ! empty( $_days ) ) {
					$days = array_merge( $days, $_days );
				}
			}
			return array_unique( $days );
		}

		return $this->parse_days_range( $days_str );
	}

	/**
	 * Parse days range string.
	 *
	 * @since 3.0.0
	 *
	 * @param string $days_str Days range string.
	 * @return array Parsed days.
	 */
	public function parse_days_range( string $days_str ): array {
		$all_days = array_keys( $this->get_weekdays( true ) );

		if ( strpos( $days_str, '-' ) !== false ) {
			$parts = explode( '-', $days_str );
			if ( count( $parts ) == 2 ) {
				$start = array_search( $parts[0], $all_days );
				$end = array_search( $parts[1], $all_days );

				if ( $start !== false && $end !== false ) {
					if ( $start <= $end ) {
						return array_slice( $all_days, $start, $end - $start + 1 );
					}
				}
			}
		} elseif ( in_array( $days_str, $all_days ) ) {
			return array( $days_str );
		}

		return array();
	}

	/**
	 * Parse hours string.
	 *
	 * @since 3.0.0
	 *
	 * @param string $hours_str Hours string.
	 * @return array Parsed hours.
	 */
	public function parse_hours( string $hours_str ): array {
		if ( strpos( $hours_str, ',' ) !== false ) {
			$hours = array();
			$parts = explode( ',', $hours_str );
			foreach ( $parts as $part ) {
				$_hours = $this->parse_hours_range( $part );
				if ( ! empty( $_hours ) ) {
					$hours[] = $_hours;
				}
			}
			return $hours;
		}

		$hours = $this->parse_hours_range( $hours_str );
		return ! empty( $hours ) ? array( $hours ) : array();
	}

	/**
	 * Parse hours range string.
	 *
	 * @since 3.0.0
	 *
	 * @param string $hours_str Hours range string.
	 * @return array Parsed hours range.
	 */
	public function parse_hours_range( string $hours_str ): array {
		if ( strpos( $hours_str, '-' ) !== false ) {
			$parts = explode( '-', $hours_str );
			if ( count( $parts ) == 2 ) {
				return array( 'opens' => $parts[0], 'closes' => $parts[1] );
			}
		}
		return array();
	}

	/**
	 * Get business hours for display.
	 *
	 * @since 3.0.0
	 *
	 * @param string $value Business hours value.
	 * @param string $country Country code.
	 * @return array Business hours array.
	 */
	public function get_business_hours( string $value = '', string $country = '' ): array {
		global $geodirectory;

		$business_hours = array();

		if ( empty( $value ) ) {
			return $business_hours;
		}

		$schema_array = $this->schema_to_array( $value, $country );

		if ( empty( $schema_array ) ) {
			return $business_hours;
		}

		// @todo: Implement full business hours rendering logic
		// This is a simplified version
		return $schema_array;
	}

	/**
	 * Convert HH:MM to business hours minutes.
	 *
	 * @since 3.0.0
	 *
	 * @param string $hm Time in HH:MM format.
	 * @param int $day_no Day number (0-6).
	 * @return int Minutes since start of week.
	 */
	public function hhmm_to_bh_minutes( string $hm, int $day_no = 0 ): int {
		$parts = explode( ':', $hm );
		if ( count( $parts ) != 2 ) {
			return 0;
		}

		$hours = (int) $parts[0];
		$minutes = (int) $parts[1];

		return ( $day_no * 24 * 60 ) + ( $hours * 60 ) + $minutes;
	}

	/**
	 * Sanitize business hours value.
	 *
	 * @since 3.0.0
	 *
	 * @param string $value Business hours value.
	 * @param string $country Country code.
	 * @return string Sanitized business hours value.
	 */
	public function sanitize_business_hours( string $value, string $country = '' ): string {
		$value = trim( $value );

		if ( empty( $value ) ) {
			return '';
		}

		// Parse and rebuild to ensure format
		$array = $this->schema_to_array( $value, $country );
		if ( empty( $array ) ) {
			return '';
		}

		return $this->array_to_schema( $array );
	}

	/**
	 * Get input time format.
	 *
	 * @since 3.0.0
	 *
	 * @param bool $jqueryui jQuery UI format.
	 * @return string Time format.
	 */
	public function input_time_format( bool $jqueryui = false ): string {
		$time_format = $this->settings->get( 'bh_input_time_format', 'g:i a' );

		if ( $jqueryui ) {
			$time_format = str_replace( array( 'g', 'h', 'G', 'H', 'i', 'a', 'A' ), array( 'h', 'hh', 'H', 'HH', 'mm', 'tt', 'TT' ), $time_format );
		}

		/**
		 * Filters the input time format.
		 *
		 * @since 2.0.0
		 *
		 * @param string $time_format Time format.
		 * @param bool $jqueryui jQuery UI format.
		 */
		return apply_filters( 'geodir_bh_input_time_format', $time_format, $jqueryui );
	}

	/**
	 * Convert offset to minutes.
	 *
	 * @since 3.0.0
	 *
	 * @param string $offset Offset string.
	 * @return int Minutes.
	 */
	public function offset_to_minutes( string $offset ): int {
		$sign = substr( $offset, 0, 1 );
		$time = substr( $offset, 1 );
		$parts = explode( ':', $time );

		if ( count( $parts ) != 2 ) {
			return 0;
		}

		$hours = (int) $parts[0];
		$minutes = (int) $parts[1];
		$total = ( $hours * 60 ) + $minutes;

		return $sign == '-' ? -$total : $total;
	}

	/**
	 * Get timezone data.
	 *
	 * @since 3.0.0
	 *
	 * @param string $tzstring Timezone string.
	 * @param int|null $time Timestamp.
	 * @return array Timezone data.
	 */
	public function timezone_data( string $tzstring = 'UTC', ?int $time = null ): array {
		if ( $time === null ) {
			$time = time();
		}

		$tz = new \DateTimeZone( $tzstring );
		$dt = new \DateTime( 'now', $tz );
		$offset = $tz->getOffset( $dt );

		return array(
			'timezone' => $tzstring,
			'offset' => $offset,
			'offset_hours' => $offset / 3600,
			'abbr' => $dt->format( 'T' )
		);
	}

	/**
	 * Convert offset to timezone string.
	 *
	 * @since 3.0.0
	 *
	 * @param float $offset UTC offset in hours.
	 * @param string $country Country code.
	 * @return string Timezone string.
	 */
	public function offset_to_timezone_string( float $offset, string $country = '' ): string {
		$offset_seconds = $offset * 3600;

		$timezone_list = \DateTimeZone::listIdentifiers();

		foreach ( $timezone_list as $timezone ) {
			$tz = new \DateTimeZone( $timezone );
			$dt = new \DateTime( 'now', $tz );

			if ( $tz->getOffset( $dt ) == $offset_seconds ) {
				if ( ! empty( $country ) ) {
					if ( strpos( $timezone, $country ) !== false ) {
						return $timezone;
					}
				} else {
					return $timezone;
				}
			}
		}

		return 'UTC';
	}

	/**
	 * Get timezone countries mapping.
	 *
	 * @since 3.0.0
	 *
	 * @return array Timezone countries array.
	 */
	public function timezone_countries(): array {
		static $timezone_countries = null;

		if ( $timezone_countries !== null ) {
			return $timezone_countries;
		}

		$timezone_countries = array();

		// Build timezone to country mapping
		// @todo: Implement full mapping logic if needed

		/**
		 * Filters the timezone countries mapping.
		 *
		 * @since 2.0.0
		 *
		 * @param array $timezone_countries Timezone countries array.
		 */
		return apply_filters( 'geodir_timezone_countries', $timezone_countries );
	}
}
