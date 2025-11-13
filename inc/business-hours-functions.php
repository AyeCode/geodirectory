<?php
/**
 * Business hours related functions.
 *
 * @since 2.0.0
 * @package GeoDirectory
 */

/**
 * Return the week days.
 *
 * @since 2.0.0
 * @return array $weekdays The days of the week
 * @param bool $untranslated If the returned day names should be translated or not.
 */
function geodir_get_weekdays( $untranslated = false ) {
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

	return apply_filters( 'geodir_get_weekdays', $weekdays, $untranslated );
}

/**
 * Return three letter abbreviation week day.
 *
 * @since 2.0.0.97
 * @return array $weekdays The days of the week
 * @param bool $untranslated If the returned day names should be translated or not.
 */
function geodir_get_short_weekdays( $untranslated = false ) {
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

	return apply_filters( 'geodir_get_short_weekdays', $weekdays, $untranslated );
}

/**
 * Return the day names in 2 digits.
 *
 * @since 2.0.0
 * @return array $weekdays The days of the week
 */
function geodir_day_short_names() {
   $days = array( 
       '1'	=> 'Mo', 
       '2'	=> 'Tu',  
       '3' 	=> 'We',  
       '4'  => 'Th',  
       '5'  => 'Fr',  
       '6'  => 'Sa',
       '7'  => 'Su'
   );

   return apply_filters( 'geodir_day_short_names', $days );
}

/**
 * Get UTC Offset without DST (Daylight Savings Time).
 *
 * @since 2.0.0
 *
 * @param bool $formatted Format the offset.
 * @param string $offeset Default offset.
 * @return string Formatted offset.
 */
function geodir_utc_offset( $offeset = '', $formatted = true ) {
	$offset = $offeset || $offeset == '0' ? $offeset : geodir_timezone_utc_offset( '', false );
	if ( $offset == '' ) {
		return geodir_wp_gmt_offset( $formatted );
	} else {
		$offset = preg_replace( '/\s+/', '', $offset );
	}

	if ( strpos( strtoupper( $offset ), 'UTC' ) === 0 || strpos( strtoupper( $offset ), 'GMT' ) === 0 ) {
		$offset = substr( $offset, 3, strlen( $offset ) -3 );
	}
	if ( strpos( $offset, '+' ) !== 0 && strpos( $offset, '-' ) !== 0 ) {
		$offset = $offset > 0 ? '+' . $offset : '-' . $offset;
	}

	$seconds = iso8601_timezone_to_offset( $offset );

	if ( ! $formatted ) {
		return $seconds;
	}

	$formatted_offset = geodir_seconds_to_hhmm( $seconds );

	return $formatted_offset;
}

/**
 * Get UTC Offset with DST (Daylight Savings Time).
 *
 * @since 2.0.0
 *
 * @param bool $formatted Format the offset.
 * @param string $offeset Default offset.
 * @return string Formatted offset.
 */
function geodir_gmt_offset( $offeset = '', $formatted = true ) {

	$offset = $offeset || $offeset == '0' ? $offeset : geodir_timezone_utc_offset( '', false );
	if ( $offset == '' ) {
		return geodir_wp_gmt_offset( $formatted );
	} else {
		$offset = preg_replace( '/\s+/', '', $offset );
	}

	if ( strpos( strtoupper( $offset ), 'UTC' ) === 0 || strpos( strtoupper( $offset ), 'GMT' ) === 0 ) {
		$offset = substr( $offset, 3, strlen( $offset ) -3 );
	}
	if ( strpos( $offset, '+' ) !== 0 && strpos( $offset, '-' ) !== 0 ) {
		$offset = $offset > 0 ? '+' . $offset : '-' . $offset;
	}

	$seconds = iso8601_timezone_to_offset( $offset );

	// DST (Daylight Savings Time) fixes
	$timezone_name = timezone_name_from_abbr("", $seconds, 0);

	// Workaround for bug #44780
	if($timezone_name === false){ $timezone_name = timezone_name_from_abbr('', $seconds, 1); }

	if($timezone_name){
		$timezone_name = $timezone_name . "\n"; // yeah, who knows why this is needed?...
		$dst_offset = geodir_utc_offset_dst( trim($timezone_name) );
		if($dst_offset){
			$dst_offset_in_seconds = $dst_offset * 3600;
			$seconds = $dst_offset_in_seconds;
		}

	}

//	echo '###'.$seconds;

	if ( ! $formatted ) {
		return $seconds;
	}

	$formatted_offset = geodir_seconds_to_hhmm( $seconds );

	return $formatted_offset;
}

/**
 * Converts the business seconds to hhmm.
 *
 * @since 2.0.0
 *
 * @param string $seconds A business hours value in schema form.
 * @param bool $abs True to remove ':00' minutes.
 * @return string $hhmm Formatted hhmm.
 */
function geodir_seconds_to_hhmm( $seconds, $abs = false ) {
	$sign = $seconds < 0 ? '-' : '+';
	$seconds = absint( $seconds );
	$hours = floor( $seconds / 3600 );
	$minutes = floor( ( $seconds - ( $hours * 3600 ) ) / 60 );
	$hhmm = $hours;
	if ( ! ( $abs && $minutes == 0 ) ) {
		$hhmm .= ":" . ( $minutes < 10 ? "0" . (string) $minutes : (string) $minutes );
	}
	$hhmm = $sign . '' .  $hhmm;
	return $hhmm;
}

/**
 * Prints a string showing current time zone offset to UTC, considering daylight savings time.
 * @link                     http://php.net/manual/en/timezones.php
 * @param  string $time_zone Time zone name
 * @param  bool   $formatted Format the offset. Converts +1.5 to +1:30.
 * @return string            Offset in hours, prepended by +/-
 */
function geodir_utc_offset_dst( $time_zone = 'Europe/Berlin', $formatted = false ) {
	$original_timezone = date_default_timezone_get();
	// Set UTC as default time zone.
	date_default_timezone_set( 'UTC' ); // @codingStandardsIgnoreEnd
	$utc = new DateTime();
	if ( empty( $time_zone ) ) {
		$time_zone = 'UTC';
	}
	// Calculate offset.
	$current   = timezone_open( $time_zone );
	$offset_s  = timezone_offset_get( $current, $utc ); // seconds

	if ( $formatted ) {
		$offset_h = geodir_seconds_to_hhmm( $offset_s ); // Converts +1.5 to +1:30.
	} else {
		$offset_h  = $offset_s / ( 60 * 60 ); // hours
		// Prepend “+” when positive
		$offset_h  = (string) $offset_h;
		if ( strpos( $offset_h, '-' ) === FALSE ) {
			$offset_h = '+' . $offset_h; // prepend +
		}
	}

	date_default_timezone_set( $original_timezone ); // @codingStandardsIgnoreEnd

	return $offset_h;
}

/**
 * Get WP UTC Offset.
 *
 * @since 2.0.0
 *
 * @param bool $formatted Format the offset.
 * @return string Formatted offset.
 */
function geodir_wp_gmt_offset( $formatted = true ) {
	$offset = get_option( 'gmt_offset' );
	if ( ! $formatted ) {
		return $offset * HOUR_IN_SECONDS;
	}

	if ( 0 <= $offset ) {
		$formatted_offset = '+' . (string) $offset;
	} else {
		$formatted_offset = (string) $offset;
	}
	$formatted_offset = str_replace(
		array( '.25', '.5', '.75' ),
		array( ':15', ':30', ':45' ),
		$formatted_offset
	);
	return $formatted_offset;
}

/**
 * Get WP default UTC Offset (without daylight savings considered).
 *
 * @since 2.0.0
 *
 * @param bool $formatted Format the offset.
 * @return string Formatted offset.
 */
function geodir_timezone_default_utc_offset( $timezone = '' ) {

	$timezone = get_option('timezone_string');
	$manual_offset = get_option( 'gmt_offset' );
	$manual = false;
	if ( ! $timezone && $manual_offset) {
		$manual = true;
	}elseif(! $timezone){
		$timezone = 'Europe/Berlin';
	}

	if( $manual ){
		$offset_h = $manual_offset;
	}else{
		$original_timezone = date_default_timezone_get();
		// Set UTC as default time zone.
		date_default_timezone_set( 'UTC' ); // @codingStandardsIgnoreEnd
		$utc = new DateTime();
		// Calculate offset.
		$gmt_offset_s = timezone_offset_get( new DateTimeZone("Europe/London"), $utc ); // seconds
		$current   = timezone_open( $timezone );
		$offset_s  = timezone_offset_get( $current, $utc ); // seconds
		$offset_s = $offset_s - $gmt_offset_s; // remove DST
		$offset_h  = $offset_s / ( 60 * 60 ); // hours
		date_default_timezone_set( $original_timezone ); // @codingStandardsIgnoreEnd
	}

	// Prepend “+” when positive
	$offset_h  = (string) $offset_h;
	if ( strpos( $offset_h, '-' ) === FALSE ) {
		$offset_h = '+' . $offset_h; // prepend +
	}

	return $offset_h;
}

/**
 * Get the default value for business hour.
 *
 * @since 2.0.0
 *
 * @return array $default Default business hour.
 */
function geodir_bh_default_values() {
	$weekdays = geodir_get_weekdays();
	
	$default = array();
	foreach( $weekdays as $day_short => $day_name ) {
		if ( in_array( $day_short, array( 'Mo', 'Tu', 'We', 'Th', 'Fr' ) ) ) {
			$default[$day_short] = array( array( 'opens' => '09:00', 'closes' => '17:00' ) );
		}
	}

	return apply_filters( 'geodir_bh_default_values', $default );
}

/**
 * Converts business hour array settings to schema output.
 *
 * @since 2.0.0
 *
 * @param array $schema_input {
 *      Arguments to retrieve business hours schema.
 *
 *      @type int       $hours      Get the business hour.
 *      @type string    $offset     Get the business hour offset.
 * }
 * @return string $schema Converted schema.
 */
function geodir_array_to_schema( $schema_input ) {
	if ( empty( $schema_input ) || ! is_array( $schema_input ) ) {
		return $schema_input;
	}
	
	$schema_hours = ! empty( $schema_input['hours'] ) ? $schema_input['hours'] : NULL;
	if ( empty( $schema_hours ) || ! is_array( $schema_hours ) ) {
		return $schema_input;
	}

	$days = geodir_day_short_names();
	if ( isset( $schema_input['offset'] ) && $schema_input['offset'] === '0' ) {
		$schema_input['offset'] = '+0';
	}
	$offset = ! empty( $schema_input['offset'] ) ? $schema_input['offset'] : geodir_gmt_offset();
	$timezone_string = ! empty( $schema_input['timezone_string'] ) ? $schema_input['timezone_string'] : geodir_timezone_string();
	$periods = array();

	foreach ( $schema_hours as $day_no => $slots ) {
		$hours = array();
		foreach ( $slots as $i => $slot ) {
			if ( ! empty( $slot['opens'] ) ) {
				$hour = $slot['opens'];
				$hour .= '-';
				$hour .= ! empty( $slot['closes'] ) ? $slot['closes'] : '00:00';
				$hours[] = $hour;
			}
		}

		if ( ! empty( $hours ) ) {
			$periods[] = $day_no . ' ' . implode( ',', $hours );
		}
	}

	$property = array();
	if ( !empty( $periods ) ) {
		$property[] = json_encode( $periods );
	}
	$property[] = '["UTC":"' . $offset . '","Timezone":"' . $timezone_string . '"]';

	$schema = implode( ",", $property );
	
	return $schema;
}

/**
 * Converts business hour schema to array output.
 *
 * @since 2.0.0
 *
 * @param string $schema Business hour schema.
 * @param string $country Country name to find timezone string.
 * @return array $return Offset and hour.
 */
function geodir_schema_to_array( $schema, $country = '' ) {
	if ( empty( $schema ) ) {
		return array();
	}

	$return = array();
	$schema_array = explode( '],[', $schema );

	if ( ! empty( $schema_array[0] ) ) {
		$day_names = geodir_day_short_names();
		$schema_str = $schema_array[0];
		if ( count( $schema_array ) > 1 ) {
			$schema_str .= ']';
		}
		$schema_arr = json_decode( $schema_str );
		$properties = array();

		if ( ! empty( $schema_arr ) ) {
			foreach ( $schema_arr as $str ) {
				$str = trim( $str );
				if ( ! empty( $str ) ) {
					$property = geodir_parse_property( $str );
					if ( ! empty( $property ) ) {
						foreach ( $day_names as $day_no => $day_name ) {
							if ( ! empty( $property[ $day_name ] ) ) {
								if ( ! empty( $properties[ $day_name ] ) ) {
									$period = array_merge( $properties[ $day_name ], $property[ $day_name ] );
								} else {
									$period = $property[ $day_name ];
								}
								$period = array_map("unserialize", array_unique(array_map("serialize", $period)));
								
								$properties[ $day_name ] = $period;
							}
						}
					}
				}
			}
		}
		
		if ( ! empty( $properties ) ) {
			$return['hours'] = $properties;
		}
	}

	if ( ! empty( $schema_array[1] ) ) {
		$_offset_value = str_replace( array( '"', '[', ']', "'" ), '', trim( $schema_array[1] ) );
		$_offset_value = explode( ',', $_offset_value, 2 );

		$timezone_string = '';
		$utc_offset = '';

		if ( ! empty( $_offset_value ) ) {
			foreach ( $_offset_value as $_value ) {
				$_values = explode( ':', $_value, 2 );

				if ( strtolower( trim( $_values[0] ) ) == 'utc' || strtolower( trim ( $_values[0] ) ) == 'gmt' ) {
					$utc_offset = isset( $_values[1] ) ? trim( $_values[1] ) : '';
				} elseif ( strtolower( trim( $_values[0] ) ) == 'timezone' ) {
					$timezone_string = isset( $_values[1] ) ? trim( $_values[1] ) : '';
				}
			}
		}

		if ( empty( $timezone_string ) ) {
			if ( $utc_offset === '0' ) {
				$utc_offset = '+0';
			}

			if ( empty( $utc_offset ) ) {
				$timezone_string = geodir_timezone_string();
				$utc_offset = '';
			} else {
				$timezone_string = geodir_offset_to_timezone_string( $utc_offset, $country );
			}
		}

		if ( empty( $utc_offset ) ) {
			$timezone_data = geodir_timezone_data( $timezone_string );
			$utc_offset = $timezone_data['utc_offset'];
		}

		$return['timezone_string'] = $timezone_string;
		$return['utc_offset'] = $utc_offset;
	}

	if ( ! empty( $return['hours'] ) && empty( $return['timezone_string'] ) ) {
		$timezone_string = geodir_timezone_string();
		$timezone_data = geodir_timezone_data( $timezone_string );

		$return['timezone_string'] = $timezone_string;
		$return['utc_offset'] = $timezone_data['utc_offset'];
	}

	return $return;
}

/**
 * Converts the parse property string to array output.
 *
 * @since 2.0.0
 *
 * @param string $str A property values for business hour.
 * @return array $property Parse property.
 */
function geodir_parse_property( $str ) {
	$arr = explode( ' ', $str );
	$property = array();

	if ( ! empty( $arr[0] ) && ! empty( $arr[1] ) ) {
		$days = geodir_parse_days( $arr[0] );
		
		if ( ! empty( $days ) ) {
			$hours = geodir_parse_hours( $arr[1] );
			
			if ( ! empty( $hours ) ) {
				foreach ( $days as $day ) {
					$property[$day] = $hours;
				}
			}
		}
	}
	
	return $property;
}

/**
 * Converts the parse days string to array output.
 *
 * @since 2.0.0
 *
 * @param string $days_str A parse days values for business hour.
 * @return array $return Unique parse day.
 */
function geodir_parse_days( $days_str ) {
	$days_names = array_values( geodir_day_short_names() );
	$days_str = trim( $days_str );
	$days_arr = explode( ',', $days_str );
	
	$return = array();
	if ( ! empty( $days_arr ) ) {
		foreach ( $days_arr as $day ) {
			$day = trim( $day );
			if ( strpos( $day, '-' ) !== false ) {
				$day = geodir_parse_days_range( $day );
				if ( ! empty( $day ) ) {
					$return = array_merge( $return, $day );
				}
			} else {
				if ( in_array( $day, $days_names ) ) {
					$return[] = $day;
				}
			}
		}
		
		if ( ! empty( $return ) ) {
			$return = array_unique( $return );
		}
	}
	return $return;
}

/**
 * Converts days range string to array output.
 *
 * @since 2.0.0
 *
 * @param string $days_str A parse days range value.
 * @return array $return Parse days names.
 */
function geodir_parse_days_range( $days_str ) {
	$days_names = geodir_day_short_names();
	$day_nos = array_flip( $days_names );
	$days_arr = explode( '-', $days_str );
	
	$start = 0;
	$end = 0;
	if ( ! empty( $days_arr[0] ) && isset( $day_nos[trim( $days_arr[0] )] ) ) {
		$start = (int)$day_nos[trim( $days_arr[0] )];
	}
	if ( ! empty( $days_arr[1] ) && isset( $day_nos[trim( $days_arr[1] )] ) ) {
		$end = (int)$day_nos[trim( $days_arr[1] )];
	}
	
	$return = array();
	if ( ! empty( $start ) && ! empty( $end ) ) {
		$start_no = min($start, $end);
		$end_no = max($start, $end);
		
		for ( $i = $start_no; $i <= $end_no; $i++ ) {
			if ( isset( $days_names[ $i ] ) ) {
				$return[] = $days_names[ $i ];
			}
		}
	}

	return $return;
}

/**
 * Converts parse hours string to array output.
 *
 * @since 2.0.0
 *
 * @param string $hours_str A business hours string.
 * @return array $return Opens and closes hours.
 */
function geodir_parse_hours( $hours_str ) {
	$hours_str = trim( $hours_str );
	$hours_arr = explode( ',', $hours_str );
	
	$return = array();
	if ( ! empty( $hours_arr ) ) {
		foreach ( $hours_arr as $hour ) {
			$hour = trim( $hour );
			$range = geodir_parse_hours_range( $hour );
			
			if ( ! empty( $range[0] ) && ! empty( $range[1] ) ) {
				$return[] = array( 'opens' => $range[0], 'closes' => $range[1] );
			}
		}
	}
	return $return;
}

/**
 * Converts the business hour range string to array output.
 *
 * @since 2.0.0
 *
 * @param string $hours_str A business hours string.
 * @return array $return Opens and closes hours.
 */
function geodir_parse_hours_range( $hours_str ) {
	$hours_arr = explode( '-', $hours_str );
	
	$return = array();
	if ( ! empty( $hours_arr[0] ) ) {
		$opens = trim( $hours_arr[0] );
		$closes = ! empty( $hours_arr[1] ) ? trim( $hours_arr[1] ) : '00:00';
		//if ( strpos( $closes, '00:00' ) === 0 ) {
			//$closes = '23:59';
		//}
		$return[] = $opens;
		$return[] = $closes;
	}
	return $return;
}

/**
 * Get business hours.
 *
 * @since 2.0.0
 *
 * @param string $value Optional. A business hour values. Default null.
 * @return array $hours List of hour data.
 */
function geodir_get_business_hours( $value = '', $country = '' ) {
	if ( empty( $value ) ) {
		return NULL;
	}
	
	if ( ! is_array( $value ) ) {
		$data = geodir_schema_to_array( stripslashes_deep( $value ), $country );
	} else {
		$data = $value;
	}

	$hours = array();
	if ( ! empty( $data['hours'] ) || ! empty( $data['timezone_string'] ) || ! empty( $data['utc_offset'] ) ) {
		$days = geodir_get_weekdays(true);
		$day_nos = array_flip( geodir_day_short_names() );

		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );
		$timestamp = current_time( 'timestamp' );
		$time = date_i18n( 'H:i:s', $timestamp );
		$time_int = strtotime( $time );
		$closed_label = __( 'Closed', 'geodirectory');
		$open_now_label = __( 'Open now', 'geodirectory');
		$closed_now_label = __( 'Closed now', 'geodirectory');
		$open_24hours_label = __( 'Open 24 hours', 'geodirectory');

		$timezone_string = ! empty( $data['timezone_string'] ) ? $data['timezone_string'] : geodir_timezone_string();
		$timezone_data = geodir_timezone_data( $timezone_string );

		$has_open = 0;
		$has_closed = 0;
		$day_slots = array();
		$today_range = $closed_label;
		$max = ( 24 * 60 * 7 ); // Week in minutes
		foreach ( $days as $day => $day_name ) {
			$day_no = (int)$day_nos[ $day ];
			$is_today = date( 'N' ) == $day_no ? 1 : 0;
			$day_short = date_i18n( 'D', strtotime( $day_name ) );
			$is_open = 0; $closed = 0;
			$values = array(); $ranges = array();
			if ( ! empty( $data['hours'][$day] ) && is_array( $data['hours'][$day] ) ) {
				$slots = $data['hours'][$day];
				$day_range = array();
				foreach ( $slots as $slot ) {
					if ( empty( $slot['opens'] ) ) {
						continue;
					}
					$open_24h = false;
					$opens = $slot['opens'];
					$closes = ! empty( $slot['closes'] ) ? $slot['closes'] : '00:00';
					if ( $closes == '00:00' ) {
						if ( $opens == '00:00' ) {
							$open_24h = true;
						}
						$closes = '24:00';
					}
					$opens_time = strtotime( $opens );
					$closes_time = strtotime( date_i18n( 'H:i:59', strtotime( $closes ) ) );

					if ( $is_today && ( ( $opens_time <= $time_int && $time_int <= $closes_time ) || $open_24h || ( $opens != '00:00' && $opens == $closes && $opens_time <= $time_int ) ) ) {
						$is_open = 1;
						$has_open = 1;
					} else {
						$is_open = 0;
					}

					if ( $open_24h ) {
						$range = $open_24hours_label;
					} else {
						$range = date_i18n( $time_format, $opens_time ) . ' - ' . date_i18n( $time_format, $closes_time );
					}

					$day_range[] = $range;

					$minutes = array( geodir_hhmm_to_bh_minutes( $opens, $day_no ), geodir_hhmm_to_bh_minutes( $closes, $day_no ) );

					$_range = array( 
						'slot' => $opens . '-' . $closes,
						'range' => $range,
						'open' => $is_open,
						'time' => array( date_i18n( 'Hi', $opens_time ) , date_i18n( 'Hi', $closes_time ) ),
						'minutes' => $minutes,
					);

					if ( ! ( $timezone_string == 'UTC' || ( empty( $timezone_data['offset'] ) && empty( $timezone_data['offset_dst'] ) ) ) ) {
						// UTC
						if ( ! empty( $timezone_data['offset'] ) ) {
							$offset_utc = round( $timezone_data['offset'] / 60 ); // Minutes
							$open_utc = $_open_utc = $minutes[0] - $offset_utc;
							$close_utc = $_close_utc = $minutes[1] - $offset_utc;

							if ( $close_utc <= $open_utc ) {
								$close_utc = $close_utc + ( 24 * 60 ); // Close on next day.
							}

							$diff = $close_utc - $open_utc;

							if ( $_open_utc < 0 ) {
								$open_utc = $_open_utc + $max;
							} elseif ( $_open_utc >= $max ) {
								$open_utc = $_open_utc - $max;
							}
							
							$close_utc = $open_utc + $diff;
						} else {
							$open_utc = $minutes[0];
							$close_utc = $minutes[1];
						}
						
						$_range['utc_minutes'] = array( $open_utc, $close_utc );

						// UTC + DST
						if ( ! empty( $timezone_data['has_dst'] ) && ! empty( $timezone_data['offset_dst'] ) ) {
							$offset_dst = round( $timezone_data['offset_dst'] / 60 ); // Minutes
							$open_dst = $_open_dst = $minutes[0] - $offset_dst;
							$close_dst = $_close_dst = $minutes[1] - $offset_dst;

							if ( $close_dst <= $open_dst ) {
								$close_dst = $close_dst + ( 24 * 60 ); // Close on next day.
							}

							$diff = $close_dst - $open_dst;

							if ( $_open_dst < 0 ) {
								$open_dst = $_open_dst + $max;
							} elseif ( $_open_dst >= $max ) {
								$open_dst = $_open_dst - $max;
							}

							$close_dst = $open_dst + $diff;

							$_range['utc_minutes_dst'] = array( $open_dst, $close_dst );
						}
					}

					$ranges[] = $_range;
				}
				if ( $is_today && ! empty( $day_range ) ) {
					$today_range = implode( ', ', $day_range );
				}
			} else {
				if ( $is_today ) {
					$has_closed = 1;
				}
				$closed = 1;
				$range = $closed_label;
				$ranges[] = array( 
					'slot' => NULL,
					'range' => $closed_label,
					'open' => 0,
					'time' => array(),
					'minutes' => array()
				);
			}
			
			$values['today'] = $is_today;
			$values['closed'] = $closed;
			$values['open'] = $is_open;
			$values['day'] = $day_name;
			$values['day_short'] = $day_short;
			$values['day_no'] = $day_nos[$day];
			$values['slots'] = $ranges;
			
			$day_slots[$day] = $values;
		}
		
		$date = date_i18n( 'Y-m-d', $timestamp );
		$date_format = date_i18n( $date_format, $timestamp );
		$time_format = date_i18n( $time_format, $timestamp );

		$hours['days'] = $day_slots;
		$hours['extra'] = array(
			'has_open' => $has_open,
			'has_closed' => $has_closed,
			'today_range' => $today_range,
			'current_label' => $has_open ? $open_now_label : $closed_now_label,
			'open_now_label' => $open_now_label,
			'closed_now_label' => $closed_now_label,
			'date' => $date,
			'time' => $time,
			'full_date' => $date . ' ' . $time,
			'date_format' => $date_format,
			'time_format' => $time_format,
			'full_date_format' => $date_format . ' ' . $time_format,
			'timezone_string' => $timezone_string,
			'offset' => $timezone_data['offset'],
			'utc_offset' => $timezone_data['utc_offset'],
			'offset_dst' => $timezone_data['offset_dst'],
			'utc_offset_dst' => $timezone_data['utc_offset_dst'],
			'has_dst' => $timezone_data['has_dst'],
			'is_dst' => $timezone_data['is_dst'],
		);
	}

	return apply_filters( 'geodir_get_business_hours', $hours, $data );
}

/**
 * Set the business hours as closed if the temp_closed field is used and set.
 *
 * @since 2.0.0.83
 */
function geodir_filter_business_hours_if_temp_closed($hours){
	global $gd_post;

	if(!empty($gd_post->temp_closed) && !empty($hours['days'])){
		foreach($hours['days'] as $key=>$val){
			$hours['days'][$key]['closed'] = 1;
			$hours['days'][$key]['open'] = 0;
			$hours['days'][$key]['slots'] = array(); // blank timings

			$hours['days'][$key]['slots'][] = array(
				'slot' => NULL,
				'range' => __( 'Temporarily Closed', 'geodirectory'),
				'open' => 0,
				'time' => array(),
				'minutes' => array()
			);
		}
	}

	return $hours;
}
add_filter('geodir_get_business_hours','geodir_filter_business_hours_if_temp_closed');

/**
 * Set the business hours as closed if the temp_closed field is used and set.
 *
 * @since 2.0.0.83
 * @param $schema
 * @param $gd_post
 *
 * @return mixed
 */
function geodir_filter_schema_business_hours_if_temp_closed($schema){
	global $gd_post;

	if(!empty($gd_post->temp_closed) && !empty($gd_post->business_hours)){
		$schema['openingHours'] = array();
	}

	return $schema;
}
add_filter('geodir_details_schema','geodir_filter_schema_business_hours_if_temp_closed');

/**
 * Converts hhmm to business hour minutes.
 *
 * @since 2.0.0
 *
 * @param string $hm Hour minutes string.
 * @param int $day_no Optional. Day number integer. Default 0.
 * @return int Hours minutes.
 */
function geodir_hhmm_to_bh_minutes( $hm, $day_no = 0 ) {
	$hours = $hm;
	$minutes = 0;

	if ( strpos( $hm, ':' ) !== false ) {
		list( $hours, $minutes ) = explode( ':', $hm );
	}

	$hours = geodir_sanitize_float( $hours );

	$diff = 0;
	if ( $day_no > 0 ) {
		$diff = ( $day_no - 1 ) * 60 * 24;
	}

	return ( ( $hours * 60 ) + $minutes ) + $diff;
}

/**
 * Sanitize business hours value.
 *
 * @since 2.0.0
 *
 * @param string $value Business hours value.
 * @param object $gd_post GD Post.
 * @param string $custom_field Custom field.
 * @param int $post_id Post id.
 * @param object $post Post.
 * @param string $update Update.
 * @return string $value Sanitize business hours.
 */
function geodir_sanitize_business_hours_value( $value, $gd_post, $custom_field, $post_id, $post, $update ) {
	if ( ! empty( $value ) && ! is_array( $value ) ) {
		$value = stripslashes_deep( $value );

		if ( strpos( $value, '"UTC"' ) === false || strpos( $value, '"Timezone"' ) === false ) {
			$schema = explode( '],[', $value, 2 );

			if ( ! empty( $gd_post['country'] ) ) {
				$country = $gd_post['country'];
			} elseif ( GeoDir_Post_types::supports( $post->post_type, 'location' ) ) {
				$country = geodir_get_post_meta( $post_id, 'country', true );
			} else {
				$country = geodir_get_option( 'default_location_country' );
			}

			$_value = geodir_schema_to_array( $value, $country );

			if ( ! empty( $_value['hours'] ) || ! empty( $_value['timezone_string'] ) ) {
				if ( ! empty( $_value['hours'] ) ) {
					$value = $schema[0];
					if ( count( $schema ) > 1 ) {
						$value .= ']';
					}
					$value .= ',';
				}

				$value .= '["UTC":"' . $_value['utc_offset'] . '","Timezone":"' . $_value['timezone_string'] . '"]';
			}
		}
	}
	return $value;
}
add_filter( 'geodir_custom_field_value_business_hours', 'geodir_sanitize_business_hours_value', 10, 6 );

/**
 * Sanitize business hours schema and add timezone.
 *
 * @since 2.0.0.96
 *
 * @param string $value Business hours schema.
 * @param string $country Country.
 * @return string Business hours schema.
 */
function geodir_sanitize_business_hours( $value, $country = '' ) {
	$value = stripslashes_deep( $value );

	if ( ! empty( $value ) && is_scalar( $value ) && ( strpos( $value, '"UTC"' ) === false || strpos( $value, '"Timezone"' ) === false ) ) {
		$schema = explode( '],[', $value, 2 );

		$_value = geodir_schema_to_array( $value, $country );

		if ( ! empty( $_value['hours'] ) || ! empty( $_value['timezone_string'] ) ) {
			if ( ! empty( $_value['hours'] ) ) {
				$value = trim( $schema[0] );
				if ( count( $schema ) > 1 ) {
					$value .= ']';
				}
				$value .= ',';
			}

			$value .= '["UTC":"' . $_value['utc_offset'] . '","Timezone":"' . $_value['timezone_string'] . '"]';
		}
	}

	return $value;
}

/**
 * Business hours time format for input field.
 *
 * @since 2.0.0
 *
 * @param bool $jqueryui If true returns in jQuery UI format. Default False.
 * @return string Time format.
 */
function geodir_bh_input_time_format( $jqueryui = false ) {
	$time_format = geodir_time_format();

	$time_format = apply_filters( 'geodir_business_hours_input_time_format', $time_format );

	if ( $jqueryui ) {
		if ( geodir_design_style() ) {
			$time_format = geodir_date_format_php_to_aui( $time_format ); // AUI Flatpickr
		} else {
			$time_format = geodir_date_format_php_to_jqueryui( $time_format );
		}
	}

	return $time_format;
}

/**
 * Converts UTC offset in minutes.
 *
 * @since 2.0.0.95
 *
 * @param string $offset UTC offset.
 * @return int Offset in minutes.
 */
function geodir_offset_to_minutes( $offset ) {
	if ( empty( $offset ) ) {
		return 0;
	}

	$offset = strtoupper( $offset );
	$offset = str_replace( array( 'UTC', 'GMT', ' ', '.' ), array( '', '', '', ':' ), $offset );
	$sign = strpos( $offset, '-' ) === 0 ? -1 : 1;
	$offset = str_replace( array( '+', '-' ), array( '', '' ), $offset );

	$minutes = geodir_hhmm_to_bh_minutes( $offset ); // HH:mm to minutes

	if ( $minutes > 0 ) {
		$minutes = $minutes * $sign; // Assign +- sign
	}

	return $minutes;
}

/**
 * Gives a nicely-formatted list of timezone strings.
 *
 * @since 2.0.0.96
 *
 * @staticvar bool $mo_loaded
 * @staticvar string $locale_loaded
 *
 * @param string $selected_zone Selected timezone.
 * @param string $locale        Optional. Locale to load the timezones in. Default current site locale.
 * @param string $manual_offsets Whether to include manual offsets.
 * @return string
 */
function geodir_timezone_choice( $selected_zone, $locale = null, $manual_offsets = false, $return_array = false ) {
	static $mo_loaded = false, $locale_loaded = null;

	$continents = array( 'Africa', 'America', 'Antarctica', 'Arctic', 'Asia', 'Atlantic', 'Australia', 'Europe', 'Indian', 'Pacific' );

	// Load translations for continents and cities.
	if ( ! $mo_loaded || $locale !== $locale_loaded ) {
		$locale_loaded = $locale ? $locale : get_locale();
		$mofile        = WP_LANG_DIR . '/continents-cities-' . $locale_loaded . '.mo';
		unload_textdomain( 'continents-cities' );
		load_textdomain( 'continents-cities', $mofile );
		$mo_loaded = true;
	}

	$zonen = array();
	foreach ( timezone_identifiers_list() as $zone ) {
		$zone = explode( '/', $zone );
		if ( ! in_array( $zone[0], $continents ) ) {
			continue;
		}

		// This determines what gets set and translated - we don't translate Etc/* strings here, they are done later.
		$exists    = array(
			0 => ( isset( $zone[0] ) && $zone[0] ),
			1 => ( isset( $zone[1] ) && $zone[1] ),
			2 => ( isset( $zone[2] ) && $zone[2] ),
		);
		$exists[3] = ( $exists[0] && 'Etc' !== $zone[0] );
		$exists[4] = ( $exists[1] && $exists[3] );
		$exists[5] = ( $exists[2] && $exists[3] );

		// phpcs:disable WordPress.WP.I18n.LowLevelTranslationFunction,WordPress.WP.I18n.NonSingularStringLiteralText
		$zonen[] = array(
			'continent'   => ( $exists[0] ? $zone[0] : '' ),
			'city'        => ( $exists[1] ? $zone[1] : '' ),
			'subcity'     => ( $exists[2] ? $zone[2] : '' ),
			't_continent' => ( $exists[3] ? translate( str_replace( '_', ' ', $zone[0] ), 'continents-cities' ) : '' ),
			't_city'      => ( $exists[4] ? translate( str_replace( '_', ' ', $zone[1] ), 'continents-cities' ) : '' ),
			't_subcity'   => ( $exists[5] ? translate( str_replace( '_', ' ', $zone[2] ), 'continents-cities' ) : '' ),
		);
		// phpcs:enable
	}
	usort( $zonen, '_wp_timezone_choice_usort_callback' );

	$structure = array();


	// Do UTC.
	if ( $return_array ) {
		$structure[] = array('optgroup' => 'start','label' => esc_attr__( 'UTC', 'geodirectory' ));
		$structure[] = array(
			'label' => __( 'UTC', 'geodirectory' ),
			'value' => esc_attr( 'UTC' ),
			'extra_attributes'  => array('data-offset' => "+0")
		);
		$structure[] = array( 'optgroup' => 'end' );
	}else{
		$structure[] = '<optgroup label="' . esc_attr__( 'UTC', 'geodirectory' ) . '">';
		$selected    = '';
		if ( 'UTC' === $selected_zone || empty( $selected_zone ) ) {
			$selected = 'selected="selected" ';
		}
		$structure[] = '<option ' . $selected . 'value="' . esc_attr( 'UTC' ) . '" data-offset="+0">' . __( 'UTC', 'geodirectory' ) . '</option>';
		$structure[] = '</optgroup>';
	}


	foreach ( $zonen as $key => $zone ) {
		// Build value in an array to join later.
		$value = array( $zone['continent'] );

		if ( empty( $zone['city'] ) ) {
			// It's at the continent level (generally won't happen).
			$display = $zone['t_continent'];
		} else {
			// It's inside a continent group.

			// Continent optgroup.
			if ( ! isset( $zonen[ $key - 1 ] ) || $zonen[ $key - 1 ]['continent'] !== $zone['continent'] ) {
				$label       = $zone['t_continent'];
				$structure[] = $return_array ? array('optgroup' => 'start','label' => esc_attr( $label ) ) : '<optgroup label="' . esc_attr( $label ) . '">';
			}

			// Add the city to the value.
			$value[] = $zone['city'];

			$display = $zone['t_city'];
			if ( ! empty( $zone['subcity'] ) ) {
				// Add the subcity to the value.
				$value[]  = $zone['subcity'];
				$display .= ' - ' . $zone['t_subcity'];
			}
		}

		// Build the value.
		$value    = join( '/', $value );
		$selected = '';
		if ( $value === $selected_zone ) {
			$selected = 'selected="selected" ';
		}

		$timezone_data = geodir_timezone_data( $value );

		// Offset
		$offset = $timezone_data['utc_offset'];
		$offset_display = ! empty( $timezone_data['has_dst'] ) && ! empty( $timezone_data['is_dst'] ) ? $timezone_data['utc_offset_dst'] : $timezone_data['utc_offset'];
		$structure[] = $return_array ? array(
			'label' => esc_html( $display ) . ' - UTC' . $offset_display,
			'value' => esc_attr( $value ),
			'extra_attributes'  => array('data-offset' => esc_attr( $offset ) )
		) : '<option ' . $selected . 'value="' . esc_attr( $value ) . '" data-offset="' . esc_attr( $offset ) . '">' . esc_html( $display ) . ' - UTC' . $offset_display . '</option>';

		// Close continent optgroup.
		if ( ! empty( $zone['city'] ) && ( ! isset( $zonen[ $key + 1 ] ) || ( isset( $zonen[ $key + 1 ] ) && $zonen[ $key + 1 ]['continent'] !== $zone['continent'] ) ) ) {
			$structure[] = $return_array ? array( 'optgroup' => 'end' ) : '</optgroup>';
		}
	}

	// Do manual UTC offsets.
	if ( $manual_offsets ) {
		$structure[]  = $return_array ? array('optgroup' => 'start','label' => esc_attr__( 'Manual Offsets', 'geodirectory' ) ) : '<optgroup label="' . esc_attr__( 'Manual Offsets', 'geodirectory' ) . '">';
		$offset_range = array(
			-12,
			-11.5,
			-11,
			-10.5,
			-10,
			-9.5,
			-9,
			-8.5,
			-8,
			-7.5,
			-7,
			-6.5,
			-6,
			-5.5,
			-5,
			-4.5,
			-4,
			-3.5,
			-3,
			-2.5,
			-2,
			-1.5,
			-1,
			-0.5,
			0,
			0.5,
			1,
			1.5,
			2,
			2.5,
			3,
			3.5,
			4,
			4.5,
			5,
			5.5,
			5.75,
			6,
			6.5,
			7,
			7.5,
			8,
			8.5,
			8.75,
			9,
			9.5,
			10,
			10.5,
			11,
			11.5,
			12,
			12.75,
			13,
			13.75,
			14,
		);
		foreach ( $offset_range as $offset ) {
			if ( 0 <= $offset ) {
				$offset_name = '+' . $offset;
			} else {
				$offset_name = (string) $offset;
			}

			$offset_value = $offset_name;
			$offset_name = str_replace( array( '.25', '.5', '.75' ), array( ':15', ':30', ':45' ), $offset_name );
			$_offset_name = $offset_name;
			$offset_name = 'UTC' . $offset_name;
			$offset_value = 'UTC' . $offset_value;
			$selected = '';
			if ( $offset_value === $selected_zone ) {
				$selected = 'selected="selected" ';
			}
			$structure[] = $return_array ? array(
				'label' => esc_html( $offset_name ),
				'value' => esc_attr( $offset_value ),
				'extra_attributes'  => array('data-offset' => esc_attr( $_offset_name )  )
			) : '<option ' . $selected . 'value="' . esc_attr( $offset_value ) . '" data-offset="' . esc_attr( $_offset_name ) . '">' . esc_html( $offset_name ) . '</option>';

		}
		$structure[] = $return_array ? array( 'optgroup' => 'end' ) : '</optgroup>';
	}

	$structure = apply_filters( 'geodir_timezone_choice_options', $structure, $selected_zone, $locale, $manual_offsets, $return_array );

	if( !$return_array ){
		$structure = is_array( $structure ) && ! empty( $structure ) ? join( "\n", $structure ) : '';
	}


	return $structure;
}

function geodir_timezone_data( $tzstring = 'UTC', $time = null ) {
	$data = array(
		'offset' => 0,
		'utc_offset' => '',
		'offset_dst' => 0,
		'utc_offset_dst' => '',
		'has_dst' => 0,
		'is_dst' => 0
	);

	if ( in_array( $tzstring, timezone_identifiers_list() ) ) {
		if ( empty( $time ) ) {
			$time = time();
		}

		$transitions = timezone_transitions_get( timezone_open( $tzstring ), $time );

		if ( ! empty( $transitions[0]['isdst'] ) || ! empty( $transitions[1]['isdst'] ) ) {
			$data['offset'] = empty( $transitions[0]['isdst'] ) ? (int) $transitions[0]['offset'] : (int) $transitions[1]['offset'];
			$data['offset_dst'] = ! empty( $transitions[0]['isdst'] ) ? (int) $transitions[0]['offset'] : (int) $transitions[1]['offset'];

			$data['has_dst'] = 1;
			if ( ! empty( $transitions[0]['isdst'] ) ) {
				$data['is_dst'] = 1;
			}
		} else {
			$data['offset'] = (int) $transitions[0]['offset'];
			$data['offset_dst'] = (int) $transitions[0]['offset'];
		}

		$data['utc_offset'] = geodir_seconds_to_hhmm( $data['offset'], true );
		$data['utc_offset_dst'] = geodir_seconds_to_hhmm( $data['offset_dst'], true );
	}

	return $data;
}

function geodir_offset_to_timezone_string( $offset, $country = '' ) {
	$timezone_string = '';

	$offset = str_replace( array( 'UTC', 'GMT', ' ', '.' ), array( '', '', '', ':' ), $offset );
	if ( $offset == '' ) {
		$offset = geodir_gmt_offset();
	}

	$seconds = geodir_offset_to_minutes( $offset ) * 60;
	$sign = $seconds < 0 ? '-' : '+';
	$seconds = absint( $seconds );
	$hours = floor( $seconds / 3600 );
	$minutes = floor( ( $seconds - ( $hours * 3600 ) ) / 60 );
	$hhmm = ( $hours < 10 ? "0" . (string) $hours : (string) $hours );
	$hhmm .= ":" . ( $minutes < 10 ? "0" . (string) $minutes : (string) $minutes );
	$hhmm = $sign . '' .  $hhmm;
	$wp_timezone_string = get_option( 'timezone_string' );

	$timezone_countries = geodir_timezone_countries();

	if ( isset( $timezone_countries[ $hhmm ] ) ) {
		$zones = $timezone_countries[ $hhmm ];

		if ( count( $zones ) == 1 ) {
			return $zones[0]['z'];
		}

		if ( ! empty( $country ) ) {
			$country = stripslashes( $country );

			if ( $wp_timezone_string ) {
				foreach ( $zones as $zone ) {
					if ( $wp_timezone_string == $zone['z'] ) {
						return $zone['z'];
					}
				}
			}

			foreach ( $zones as $zone ) {
				if ( geodir_strtolower( $country ) == geodir_strtolower( $zone['c'] ) ) {
					return $zone['z'];
				}
			}
		}
	}

	if ( $hhmm != '+00:00' ) {
		$_timezone_string = timezone_name_from_abbr( '', $seconds, 0 );
		if ( $_timezone_string === false ) {
			$_timezone_string = timezone_name_from_abbr( '', $seconds, 1 ); // DST
		}

		if ( $_timezone_string ) {
			return $_timezone_string;
		}
	}

	$timezone_string = 'UTC';

	return $timezone_string;
}

function geodir_timezone_countries() {
	return apply_filters( 'geodir_timezone_countries', 
	array(
		'-12:00' => array(
			array( 'c' => 'United States', 'z' => 'Pacific/Midway' )
		),
		'-11:00' => array(
			array( 'c' => 'American Samoa', 'z' => 'Pacific/Pago_Pago' ),
			array( 'c' => 'Niue', 'z' => 'Pacific/Niue' ),
			array( 'c' => 'United States', 'z' => 'Pacific/Midway' ),
			array( 'c' => 'United States Minor Outlying Islands', 'z' => 'Pacific/Midway' )
		),
		'-10:00' => array(
			array( 'c' => 'Cook Islands', 'z' => 'Pacific/Rarotonga' ),
			array( 'c' => 'French Polynesia', 'z' => 'Pacific/Tahiti' ),
			array( 'c' => 'United States', 'z' => 'America/Adak' ),
			array( 'c' => 'United States', 'z' => 'Pacific/Honolulu' )
		),
		'-09:30' => array(
			array( 'c' => 'French Polynesia', 'z' => 'Pacific/Marquesas' )
		),
		'-09:00' => array(
			array( 'c' => 'French Polynesia', 'z' => 'Pacific/Gambier' ),
			array( 'c' => 'United States', 'z' => 'America/Adak' ),
			array( 'c' => 'United States', 'z' => 'America/Anchorage' ),
			array( 'c' => 'United States', 'z' => 'America/Juneau' ),
			array( 'c' => 'United States', 'z' => 'America/Metlakatla' ),
			array( 'c' => 'United States', 'z' => 'America/Nome' ),
			array( 'c' => 'United States', 'z' => 'America/Sitka' ),
			array( 'c' => 'United States', 'z' => 'America/Yakutat' )
		),
		'-08:00' => array(
			array( 'c' => 'Canada', 'z' => 'America/Dawson' ),
			array( 'c' => 'Canada', 'z' => 'America/Vancouver' ),
			array( 'c' => 'Canada', 'z' => 'America/Whitehorse' ),
			array( 'c' => 'Mexico', 'z' => 'America/Tijuana' ),
			array( 'c' => 'Pitcairn', 'z' => 'Pacific/Pitcairn' ),
			array( 'c' => 'United States', 'z' => 'America/Los_Angeles' ),
			array( 'c' => 'United States', 'z' => 'America/Anchorage' ),
			array( 'c' => 'United States', 'z' => 'America/Juneau' ),
			array( 'c' => 'United States', 'z' => 'America/Metlakatla' ),
			array( 'c' => 'United States', 'z' => 'America/Nome' ),
			array( 'c' => 'United States', 'z' => 'America/Sitka' ),
			array( 'c' => 'United States', 'z' => 'America/Yakutat' )
		),
		'-07:00' => array(
			array( 'c' => 'Canada', 'z' => 'America/Cambridge_Bay' ),
			array( 'c' => 'Canada', 'z' => 'America/Inuvik' ),
			array( 'c' => 'Canada', 'z' => 'America/Creston' ),
			array( 'c' => 'Canada', 'z' => 'America/Whitehorse' ),
			array( 'c' => 'Canada', 'z' => 'America/Vancouver' ),
			array( 'c' => 'Canada', 'z' => 'America/Yellowknife' ),
			array( 'c' => 'Canada', 'z' => 'America/Fort_Nelson' ),
			array( 'c' => 'Canada', 'z' => 'America/Edmonton' ),
			array( 'c' => 'Canada', 'z' => 'America/Dawson_Creek' ),
			array( 'c' => 'Canada', 'z' => 'America/Dawson' ),
			array( 'c' => 'Mexico', 'z' => 'America/Chihuahua' ),
			array( 'c' => 'Mexico', 'z' => 'America/Hermosillo' ),
			array( 'c' => 'Mexico', 'z' => 'America/Mazatlan' ),
			array( 'c' => 'Mexico', 'z' => 'America/Ojinaga' ),
			array( 'c' => 'Mexico', 'z' => 'America/Tijuana' ),
			array( 'c' => 'United States', 'z' => 'America/Los_Angeles' ),
			array( 'c' => 'United States', 'z' => 'America/Boise' ),
			array( 'c' => 'United States', 'z' => 'America/Denver' ),
			array( 'c' => 'United States', 'z' => 'America/Phoenix' )
		),
		'-06:00' => array(
			array( 'c' => 'Belize', 'z' => 'America/Belize' ),
			array( 'c' => 'Canada', 'z' => 'America/Resolute' ),
			array( 'c' => 'Canada', 'z' => 'America/Cambridge_Bay' ),
			array( 'c' => 'Canada', 'z' => 'America/Winnipeg' ),
			array( 'c' => 'Canada', 'z' => 'America/Swift_Current' ),
			array( 'c' => 'Canada', 'z' => 'America/Yellowknife' ),
			array( 'c' => 'Canada', 'z' => 'America/Regina' ),
			array( 'c' => 'Canada', 'z' => 'America/Rankin_Inlet' ),
			array( 'c' => 'Canada', 'z' => 'America/Rainy_River' ),
			array( 'c' => 'Canada', 'z' => 'America/Inuvik' ),
			array( 'c' => 'Canada', 'z' => 'America/Edmonton' ),
			array( 'c' => 'Chile', 'z' => 'Pacific/Easter' ),
			array( 'c' => 'Costa Rica', 'z' => 'America/Costa_Rica' ),
			array( 'c' => 'Ecuador', 'z' => 'Pacific/Galapagos' ),
			array( 'c' => 'El Salvador', 'z' => 'America/El_Salvador' ),
			array( 'c' => 'Guatemala', 'z' => 'America/Guatemala' ),
			array( 'c' => 'Honduras', 'z' => 'America/Tegucigalpa' ),
			array( 'c' => 'Mexico', 'z' => 'America/Mexico_City' ),
			array( 'c' => 'Mexico', 'z' => 'America/Ojinaga' ),
			array( 'c' => 'Mexico', 'z' => 'America/Monterrey' ),
			array( 'c' => 'Mexico', 'z' => 'America/Chihuahua' ),
			array( 'c' => 'Mexico', 'z' => 'America/Merida' ),
			array( 'c' => 'Mexico', 'z' => 'America/Mazatlan' ),
			array( 'c' => 'Mexico', 'z' => 'America/Matamoros' ),
			array( 'c' => 'Mexico', 'z' => 'America/Bahia_Banderas' ),
			array( 'c' => 'Nicaragua', 'z' => 'America/Managua' ),
			array( 'c' => 'United States', 'z' => 'America/Chicago' ),
			array( 'c' => 'United States', 'z' => 'America/Boise' ),
			array( 'c' => 'United States', 'z' => 'America/Denver' ),
			array( 'c' => 'United States', 'z' => 'America/Indiana/Knox' ),
			array( 'c' => 'United States', 'z' => 'America/Indiana/Tell_City' ),
			array( 'c' => 'United States', 'z' => 'America/Menominee' ),
			array( 'c' => 'United States', 'z' => 'America/North_Dakota/Beulah' ),
			array( 'c' => 'United States', 'z' => 'America/North_Dakota/Center' ),
			array( 'c' => 'United States', 'z' => 'America/North_Dakota/New_Salem' )
		),
		'-05:00' => array(
			array( 'c' => 'Bahamas', 'z' => 'America/Nassau' ),
			array( 'c' => 'Brazil', 'z' => 'America/Eirunepe' ),
			array( 'c' => 'Brazil', 'z' => 'America/Rio_Branco' ),
			array( 'c' => 'Canada', 'z' => 'America/Toronto' ),
			array( 'c' => 'Canada', 'z' => 'America/Rankin_Inlet' ),
			array( 'c' => 'Canada', 'z' => 'America/Winnipeg' ),
			array( 'c' => 'Canada', 'z' => 'America/Thunder_Bay' ),
			array( 'c' => 'Canada', 'z' => 'America/Resolute' ),
			array( 'c' => 'Canada', 'z' => 'America/Rainy_River' ),
			array( 'c' => 'Canada', 'z' => 'America/Pangnirtung' ),
			array( 'c' => 'Canada', 'z' => 'America/Nipigon' ),
			array( 'c' => 'Canada', 'z' => 'America/Iqaluit' ),
			array( 'c' => 'Canada', 'z' => 'America/Atikokan' ),
			array( 'c' => 'Cayman Islands', 'z' => 'America/Cayman' ),
			array( 'c' => 'Chile', 'z' => 'Pacific/Easter' ),
			array( 'c' => 'Colombia', 'z' => 'America/Bogota' ),
			array( 'c' => 'Cuba', 'z' => 'America/Havana' ),
			array( 'c' => 'Ecuador', 'z' => 'America/Guayaquil' ),
			array( 'c' => 'Haiti', 'z' => 'America/Port-au-Prince' ),
			array( 'c' => 'Jamaica', 'z' => 'America/Jamaica' ),
			array( 'c' => 'Mexico', 'z' => 'America/Mexico_City' ),
			array( 'c' => 'Mexico', 'z' => 'America/Monterrey' ),
			array( 'c' => 'Mexico', 'z' => 'America/Merida' ),
			array( 'c' => 'Mexico', 'z' => 'America/Matamoros' ),
			array( 'c' => 'Mexico', 'z' => 'America/Bahia_Banderas' ),
			array( 'c' => 'Mexico', 'z' => 'America/Cancun' ),
			array( 'c' => 'Panama', 'z' => 'America/Panama' ),
			array( 'c' => 'Peru', 'z' => 'America/Lima' ),
			array( 'c' => 'Turks and Caicos Islands', 'z' => 'America/Grand_Turk' ),
			array( 'c' => 'United States', 'z' => 'America/New_York' ),
			array( 'c' => 'United States', 'z' => 'America/Indiana/Winamac' ),
			array( 'c' => 'United States', 'z' => 'America/North_Dakota/Center' ),
			array( 'c' => 'United States', 'z' => 'America/North_Dakota/Beulah' ),
			array( 'c' => 'United States', 'z' => 'America/Menominee' ),
			array( 'c' => 'United States', 'z' => 'America/Kentucky/Monticello' ),
			array( 'c' => 'United States', 'z' => 'America/Kentucky/Louisville' ),
			array( 'c' => 'United States', 'z' => 'America/Indiana/Petersburg' ),
			array( 'c' => 'United States', 'z' => 'America/Indiana/Vincennes' ),
			array( 'c' => 'United States', 'z' => 'America/Indiana/Vevay' ),
			array( 'c' => 'United States', 'z' => 'America/Indiana/Tell_City' ),
			array( 'c' => 'United States', 'z' => 'America/Indiana/Marengo' ),
			array( 'c' => 'United States', 'z' => 'America/Indiana/Knox' ),
			array( 'c' => 'United States', 'z' => 'America/Indiana/Indianapolis' ),
			array( 'c' => 'United States', 'z' => 'America/Detroit' ),
			array( 'c' => 'United States', 'z' => 'America/Chicago' ),
			array( 'c' => 'United States', 'z' => 'America/North_Dakota/New_Salem' )
		),
		'-04:00' => array(
			array( 'c' => 'Anguilla', 'z' => 'America/Anguilla' ),
			array( 'c' => 'Antigua and Barbuda', 'z' => 'America/Antigua' ),
			array( 'c' => 'Aruba', 'z' => 'America/Aruba' ),
			array( 'c' => 'Bahamas', 'z' => 'America/Nassau' ),
			array( 'c' => 'Barbados', 'z' => 'America/Barbados' ),
			array( 'c' => 'Bermuda', 'z' => 'Atlantic/Bermuda' ),
			array( 'c' => 'Bolivia', 'z' => 'America/La_Paz' ),
			array( 'c' => 'Bonaire, Sint Eustatius and Saba', 'z' => 'America/Kralendijk' ),
			array( 'c' => 'Brazil', 'z' => 'America/Boa_Vista' ),
			array( 'c' => 'Brazil', 'z' => 'America/Campo_Grande' ),
			array( 'c' => 'Brazil', 'z' => 'America/Cuiaba' ),
			array( 'c' => 'Brazil', 'z' => 'America/Manaus' ),
			array( 'c' => 'Brazil', 'z' => 'America/Porto_Velho' ),
			array( 'c' => 'British Virgin Islands', 'z' => 'America/Tortola' ),
			array( 'c' => 'Canada', 'z' => 'America/Toronto' ),
			array( 'c' => 'Canada', 'z' => 'America/Nipigon' ),
			array( 'c' => 'Canada', 'z' => 'America/Thunder_Bay' ),
			array( 'c' => 'Canada', 'z' => 'America/Pangnirtung' ),
			array( 'c' => 'Canada', 'z' => 'America/Goose_Bay' ),
			array( 'c' => 'Canada', 'z' => 'America/Moncton' ),
			array( 'c' => 'Canada', 'z' => 'America/Iqaluit' ),
			array( 'c' => 'Canada', 'z' => 'America/Halifax' ),
			array( 'c' => 'Canada', 'z' => 'America/Blanc-Sablon' ),
			array( 'c' => 'Canada', 'z' => 'America/Glace_Bay' ),
			array( 'c' => 'Chile', 'z' => 'America/Santiago' ),
			array( 'c' => 'Cuba', 'z' => 'America/Havana' ),
			array( 'c' => 'Curaçao', 'z' => 'America/Curacao' ),
			array( 'c' => 'Dominica', 'z' => 'America/Dominica' ),
			array( 'c' => 'Dominican Republic', 'z' => 'America/Santo_Domingo' ),
			array( 'c' => 'Greenland', 'z' => 'America/Thule' ),
			array( 'c' => 'Grenada', 'z' => 'America/Grenada' ),
			array( 'c' => 'Guadeloupe', 'z' => 'America/Guadeloupe' ),
			array( 'c' => 'Guyana', 'z' => 'America/Guyana' ),
			array( 'c' => 'Haiti', 'z' => 'America/Port-au-Prince' ),
			array( 'c' => 'Martinique', 'z' => 'America/Martinique' ),
			array( 'c' => 'Montserrat', 'z' => 'America/Montserrat' ),
			array( 'c' => 'Paraguay', 'z' => 'America/Asuncion' ),
			array( 'c' => 'Puerto Rico', 'z' => 'America/Puerto_Rico' ),
			array( 'c' => 'Saint Barthélemy', 'z' => 'America/St_Barthelemy' ),
			array( 'c' => 'Saint Kitts and Nevis', 'z' => 'America/St_Kitts' ),
			array( 'c' => 'Saint Lucia', 'z' => 'America/St_Lucia' ),
			array( 'c' => 'Saint Martin', 'z' => 'America/Marigot' ),
			array( 'c' => 'Saint Vincent and the Grenadines', 'z' => 'America/St_Vincent' ),
			array( 'c' => 'Sint Maarten', 'z' => 'America/Lower_Princes' ),
			array( 'c' => 'Trinidad and Tobago', 'z' => 'America/Port_of_Spain' ),
			array( 'c' => 'Turks and Caicos Islands', 'z' => 'America/Grand_Turk' ),
			array( 'c' => 'US Virgin Islands', 'z' => 'America/St_Thomas' ),
			array( 'c' => 'United States', 'z' => 'America/New_York' ),
			array( 'c' => 'United States', 'z' => 'America/Indiana/Petersburg' ),
			array( 'c' => 'United States', 'z' => 'America/Indiana/Vevay' ),
			array( 'c' => 'United States', 'z' => 'America/Indiana/Vincennes' ),
			array( 'c' => 'United States', 'z' => 'America/Indiana/Winamac' ),
			array( 'c' => 'United States', 'z' => 'America/Kentucky/Louisville' ),
			array( 'c' => 'United States', 'z' => 'America/Kentucky/Monticello' ),
			array( 'c' => 'United States', 'z' => 'America/Indiana/Marengo' ),
			array( 'c' => 'United States', 'z' => 'America/Indiana/Indianapolis' ),
			array( 'c' => 'United States', 'z' => 'America/Detroit' ),
			array( 'c' => 'Venezuela', 'z' => 'America/Caracas' )
		),
		'-03:30' => array(
			array( 'c' => 'Canada', 'z' => 'America/St_Johns' )
		),
		'-03:00' => array(
			array( 'c' => 'Antarctica', 'z' => 'Antarctica/Rothera' ),
			array( 'c' => 'Antarctica', 'z' => 'Antarctica/Palmer' ),
			array( 'c' => 'Argentina', 'z' => 'America/Argentina/San_Luis' ),
			array( 'c' => 'Argentina', 'z' => 'America/Argentina/Rio_Gallegos' ),
			array( 'c' => 'Argentina', 'z' => 'America/Argentina/Ushuaia' ),
			array( 'c' => 'Argentina', 'z' => 'America/Argentina/Tucuman' ),
			array( 'c' => 'Argentina', 'z' => 'America/Argentina/San_Juan' ),
			array( 'c' => 'Argentina', 'z' => 'America/Argentina/Salta' ),
			array( 'c' => 'Argentina', 'z' => 'America/Argentina/Mendoza' ),
			array( 'c' => 'Argentina', 'z' => 'America/Argentina/La_Rioja' ),
			array( 'c' => 'Argentina', 'z' => 'America/Argentina/Jujuy' ),
			array( 'c' => 'Argentina', 'z' => 'America/Argentina/Cordoba' ),
			array( 'c' => 'Argentina', 'z' => 'America/Argentina/Catamarca' ),
			array( 'c' => 'Argentina', 'z' => 'America/Argentina/Buenos_Aires' ),
			array( 'c' => 'Bermuda', 'z' => 'Atlantic/Bermuda' ),
			array( 'c' => 'Brazil', 'z' => 'America/Sao_Paulo' ),
			array( 'c' => 'Brazil', 'z' => 'America/Santarem' ),
			array( 'c' => 'Brazil', 'z' => 'America/Recife' ),
			array( 'c' => 'Brazil', 'z' => 'America/Maceio' ),
			array( 'c' => 'Brazil', 'z' => 'America/Cuiaba' ),
			array( 'c' => 'Brazil', 'z' => 'America/Fortaleza' ),
			array( 'c' => 'Brazil', 'z' => 'America/Campo_Grande' ),
			array( 'c' => 'Brazil', 'z' => 'America/Belem' ),
			array( 'c' => 'Brazil', 'z' => 'America/Bahia' ),
			array( 'c' => 'Brazil', 'z' => 'America/Araguaina' ),
			array( 'c' => 'Canada', 'z' => 'America/Glace_Bay' ),
			array( 'c' => 'Canada', 'z' => 'America/Goose_Bay' ),
			array( 'c' => 'Canada', 'z' => 'America/Halifax' ),
			array( 'c' => 'Canada', 'z' => 'America/Moncton' ),
			array( 'c' => 'Chile', 'z' => 'America/Punta_Arenas' ),
			array( 'c' => 'Chile', 'z' => 'America/Santiago' ),
			array( 'c' => 'Falkland Islands', 'z' => 'Atlantic/Stanley' ),
			array( 'c' => 'French Guiana', 'z' => 'America/Cayenne' ),
			array( 'c' => 'Greenland', 'z' => 'America/Godthab' ),
			array( 'c' => 'Greenland', 'z' => 'America/Thule' ),
			array( 'c' => 'Paraguay', 'z' => 'America/Asuncion' ),
			array( 'c' => 'Saint Pierre and Miquelon', 'z' => 'America/Miquelon' ),
			array( 'c' => 'Suriname', 'z' => 'America/Paramaribo' ),
			array( 'c' => 'Uruguay', 'z' => 'America/Montevideo' )
		),
		'-02:30' => array(
			array( 'c' => 'Canada', 'z' => 'America/St_Johns' )
		),
		'-02:00' => array(
			array( 'c' => 'Brazil', 'z' => 'America/Noronha' ),
			array( 'c' => 'Greenland', 'z' => 'America/Godthab' ),
			array( 'c' => 'Saint Pierre and Miquelon', 'z' => 'America/Miquelon' ),
			array( 'c' => 'South Georgia and the South Sandwich Islands', 'z' => 'Atlantic/South_Georgia' )
		),
		'-01:00' => array(
			array( 'c' => 'Cabo Verde', 'z' => 'Atlantic/Cape_Verde' ),
			array( 'c' => 'Greenland', 'z' => 'America/Scoresbysund' ),
			array( 'c' => 'Portugal', 'z' => 'Atlantic/Azores' )
		),
		'+00:00' => array(
			array( 'c' => 'Antarctica', 'z' => 'Antarctica/Troll' ),
			array( 'c' => 'Burkina Faso', 'z' => 'Africa/Ouagadougou' ),
			array( 'c' => "Côte d'Ivoire", 'z' => 'Africa/Abidjan' ),
			array( 'c' => 'Faroe Islands', 'z' => 'Atlantic/Faroe' ),
			array( 'c' => 'Gambia', 'z' => 'Africa/Banjul' ),
			array( 'c' => 'Ghana', 'z' => 'Africa/Accra' ),
			array( 'c' => 'Greenland', 'z' => 'America/Danmarkshavn' ),
			array( 'c' => 'Greenland', 'z' => 'America/Scoresbysund' ),
			array( 'c' => 'Guernsey', 'z' => 'Europe/Guernsey' ),
			array( 'c' => 'Guinea', 'z' => 'Africa/Conakry' ),
			array( 'c' => 'Guinea-Bissau', 'z' => 'Africa/Bissau' ),
			array( 'c' => 'Iceland', 'z' => 'Atlantic/Reykjavik' ),
			array( 'c' => 'Ireland', 'z' => 'Europe/Dublin' ),
			array( 'c' => 'Isle of Man', 'z' => 'Europe/Isle_of_Man' ),
			array( 'c' => 'Jersey', 'z' => 'Europe/Jersey' ),
			array( 'c' => 'Liberia', 'z' => 'Africa/Monrovia' ),
			array( 'c' => 'Mali', 'z' => 'Africa/Bamako' ),
			array( 'c' => 'Mauritania', 'z' => 'Africa/Nouakchott' ),
			array( 'c' => 'Portugal', 'z' => 'Atlantic/Azores' ),
			array( 'c' => 'Portugal', 'z' => 'Atlantic/Madeira' ),
			array( 'c' => 'Portugal', 'z' => 'Europe/Lisbon' ),
			array( 'c' => 'Saint Helena, Ascension and Tristan da Cunha', 'z' => 'Atlantic/St_Helena' ),
			array( 'c' => 'Sao Tome and Principe', 'z' => 'Africa/Sao_Tome' ),
			array( 'c' => 'Senegal', 'z' => 'Africa/Dakar' ),
			array( 'c' => 'Sierra Leone', 'z' => 'Africa/Freetown' ),
			array( 'c' => 'Spain', 'z' => 'Atlantic/Canary' ),
			array( 'c' => 'Togo', 'z' => 'Africa/Lome' ),
			array( 'c' => 'United Kingdom', 'z' => 'Europe/London' ),
			array( 'c' => 'Western Sahara', 'z' => 'Africa/El_Aaiun' )
		),
		'+01:00' => array(
			array( 'c' => 'Albania', 'z' => 'Europe/Tirane' ),
			array( 'c' => 'Algeria', 'z' => 'Africa/Algiers' ),
			array( 'c' => 'Andorra', 'z' => 'Europe/Andorra' ),
			array( 'c' => 'Angola', 'z' => 'Africa/Luanda' ),
			array( 'c' => 'Austria', 'z' => 'Europe/Vienna' ),
			array( 'c' => 'Belgium', 'z' => 'Europe/Brussels' ),
			array( 'c' => 'Benin', 'z' => 'Africa/Porto-Novo' ),
			array( 'c' => 'Bosnia and Herzegovina', 'z' => 'Europe/Sarajevo' ),
			array( 'c' => 'Cameroon', 'z' => 'Africa/Douala' ),
			array( 'c' => 'Central African Republic', 'z' => 'Africa/Bangui' ),
			array( 'c' => 'Chad', 'z' => 'Africa/Ndjamena' ),
			array( 'c' => 'Congo', 'z' => 'Africa/Brazzaville' ),
			array( 'c' => 'Croatia', 'z' => 'Europe/Zagreb' ),
			array( 'c' => 'Czechia', 'z' => 'Europe/Prague' ),
			array( 'c' => 'Democratic Republic of the Congo', 'z' => 'Africa/Kinshasa' ),
			array( 'c' => 'Denmark', 'z' => 'Europe/Copenhagen' ),
			array( 'c' => 'Equatorial Guinea', 'z' => 'Africa/Malabo' ),
			array( 'c' => 'Faroe Islands', 'z' => 'Atlantic/Faroe' ),
			array( 'c' => 'France', 'z' => 'Europe/Paris' ),
			array( 'c' => 'Gabon', 'z' => 'Africa/Libreville' ),
			array( 'c' => 'Germany', 'z' => 'Europe/Berlin' ),
			array( 'c' => 'Germany', 'z' => 'Europe/Busingen' ),
			array( 'c' => 'Gibraltar', 'z' => 'Europe/Gibraltar' ),
			array( 'c' => 'Guernsey', 'z' => 'Europe/Guernsey' ),
			array( 'c' => 'Holy See', 'z' => 'Europe/Vatican' ),
			array( 'c' => 'Hungary', 'z' => 'Europe/Budapest' ),
			array( 'c' => 'Ireland', 'z' => 'Europe/Dublin' ),
			array( 'c' => 'Isle of Man', 'z' => 'Europe/Isle_of_Man' ),
			array( 'c' => 'Italy', 'z' => 'Europe/Rome' ),
			array( 'c' => 'Jersey', 'z' => 'Europe/Jersey' ),
			array( 'c' => 'Liechtenstein', 'z' => 'Europe/Vaduz' ),
			array( 'c' => 'Luxembourg', 'z' => 'Europe/Luxembourg' ),
			array( 'c' => 'Macedonia (the former Yugoslav Republic of)', 'z' => 'Europe/Skopje' ),
			array( 'c' => 'Malta', 'z' => 'Europe/Malta' ),
			array( 'c' => 'Monaco', 'z' => 'Europe/Monaco' ),
			array( 'c' => 'Montenegro', 'z' => 'Europe/Podgorica' ),
			array( 'c' => 'Morocco', 'z' => 'Africa/Casablanca' ),
			array( 'c' => 'Netherlands', 'z' => 'Europe/Amsterdam' ),
			array( 'c' => 'Niger', 'z' => 'Africa/Niamey' ),
			array( 'c' => 'Nigeria', 'z' => 'Africa/Lagos' ),
			array( 'c' => 'Norway', 'z' => 'Europe/Oslo' ),
			array( 'c' => 'Poland', 'z' => 'Europe/Warsaw' ),
			array( 'c' => 'Portugal', 'z' => 'Atlantic/Madeira' ),
			array( 'c' => 'Portugal', 'z' => 'Europe/Lisbon' ),
			array( 'c' => 'San Marino', 'z' => 'Europe/San_Marino' ),
			array( 'c' => 'Serbia', 'z' => 'Europe/Belgrade' ),
			array( 'c' => 'Slovakia', 'z' => 'Europe/Bratislava' ),
			array( 'c' => 'Slovenia', 'z' => 'Europe/Ljubljana' ),
			array( 'c' => 'Spain', 'z' => 'Africa/Ceuta' ),
			array( 'c' => 'Spain', 'z' => 'Atlantic/Canary' ),
			array( 'c' => 'Spain', 'z' => 'Europe/Madrid' ),
			array( 'c' => 'Svalbard and Jan Mayen', 'z' => 'Arctic/Longyearbyen' ),
			array( 'c' => 'Sweden', 'z' => 'Europe/Stockholm' ),
			array( 'c' => 'Switzerland', 'z' => 'Europe/Zurich' ),
			array( 'c' => 'Tunisia', 'z' => 'Africa/Tunis' ),
			array( 'c' => 'United Kingdom', 'z' => 'Europe/London' ),
			array( 'c' => 'Western Sahara', 'z' => 'Africa/El_Aaiun' )
		),
		'+02:00' => array(
			array( 'c' => 'Albania', 'z' => 'Europe/Tirane' ),
			array( 'c' => 'Andorra', 'z' => 'Europe/Andorra' ),
			array( 'c' => 'Antarctica', 'z' => 'Antarctica/Troll' ),
			array( 'c' => 'Austria', 'z' => 'Europe/Vienna' ),
			array( 'c' => 'Belgium', 'z' => 'Europe/Brussels' ),
			array( 'c' => 'Bosnia and Herzegovina', 'z' => 'Europe/Sarajevo' ),
			array( 'c' => 'Botswana', 'z' => 'Africa/Gaborone' ),
			array( 'c' => 'Bulgaria', 'z' => 'Europe/Sofia' ),
			array( 'c' => 'Burundi', 'z' => 'Africa/Bujumbura' ),
			array( 'c' => 'Croatia', 'z' => 'Europe/Zagreb' ),
			array( 'c' => 'Cyprus', 'z' => 'Asia/Famagusta' ),
			array( 'c' => 'Cyprus', 'z' => 'Asia/Nicosia' ),
			array( 'c' => 'Czechia', 'z' => 'Europe/Prague' ),
			array( 'c' => 'Democratic Republic of the Congo', 'z' => 'Africa/Lubumbashi' ),
			array( 'c' => 'Denmark', 'z' => 'Europe/Copenhagen' ),
			array( 'c' => 'Egypt', 'z' => 'Africa/Cairo' ),
			array( 'c' => 'Estonia', 'z' => 'Europe/Tallinn' ),
			array( 'c' => 'Finland', 'z' => 'Europe/Helsinki' ),
			array( 'c' => 'France', 'z' => 'Europe/Paris' ),
			array( 'c' => 'Germany', 'z' => 'Europe/Berlin' ),
			array( 'c' => 'Germany', 'z' => 'Europe/Busingen' ),
			array( 'c' => 'Gibraltar', 'z' => 'Europe/Gibraltar' ),
			array( 'c' => 'Greece', 'z' => 'Europe/Athens' ),
			array( 'c' => 'Holy See', 'z' => 'Europe/Vatican' ),
			array( 'c' => 'Hungary', 'z' => 'Europe/Budapest' ),
			array( 'c' => 'Israel', 'z' => 'Asia/Jerusalem' ),
			array( 'c' => 'Italy', 'z' => 'Europe/Rome' ),
			array( 'c' => 'Jordan', 'z' => 'Asia/Amman' ),
			array( 'c' => 'Latvia', 'z' => 'Europe/Riga' ),
			array( 'c' => 'Lebanon', 'z' => 'Asia/Beirut' ),
			array( 'c' => 'Lesotho', 'z' => 'Africa/Maseru' ),
			array( 'c' => 'Libya', 'z' => 'Africa/Tripoli' ),
			array( 'c' => 'Liechtenstein', 'z' => 'Europe/Vaduz' ),
			array( 'c' => 'Lithuania', 'z' => 'Europe/Vilnius' ),
			array( 'c' => 'Luxembourg', 'z' => 'Europe/Luxembourg' ),
			array( 'c' => 'Macedonia (the former Yugoslav Republic of)', 'z' => 'Europe/Skopje' ),
			array( 'c' => 'Malawi', 'z' => 'Africa/Blantyre' ),
			array( 'c' => 'Malta', 'z' => 'Europe/Malta' ),
			array( 'c' => 'Moldova', 'z' => 'Europe/Chisinau' ),
			array( 'c' => 'Monaco', 'z' => 'Europe/Monaco' ),
			array( 'c' => 'Montenegro', 'z' => 'Europe/Podgorica' ),
			array( 'c' => 'Mozambique', 'z' => 'Africa/Maputo' ),
			array( 'c' => 'Namibia', 'z' => 'Africa/Windhoek' ),
			array( 'c' => 'Netherlands', 'z' => 'Europe/Amsterdam' ),
			array( 'c' => 'Norway', 'z' => 'Europe/Oslo' ),
			array( 'c' => 'Palestine', 'z' => 'Asia/Hebron' ),
			array( 'c' => 'Palestine', 'z' => 'Asia/Gaza' ),
			array( 'c' => 'Poland', 'z' => 'Europe/Warsaw' ),
			array( 'c' => 'Romania', 'z' => 'Europe/Bucharest' ),
			array( 'c' => 'Russia', 'z' => 'Europe/Kaliningrad' ),
			array( 'c' => 'Rwanda', 'z' => 'Africa/Kigali' ),
			array( 'c' => 'San Marino', 'z' => 'Europe/San_Marino' ),
			array( 'c' => 'Serbia', 'z' => 'Europe/Belgrade' ),
			array( 'c' => 'Slovakia', 'z' => 'Europe/Bratislava' ),
			array( 'c' => 'Slovenia', 'z' => 'Europe/Ljubljana' ),
			array( 'c' => 'South Africa', 'z' => 'Africa/Johannesburg' ),
			array( 'c' => 'Spain', 'z' => 'Europe/Madrid' ),
			array( 'c' => 'Sudan', 'z' => 'Africa/Khartoum' ),
			array( 'c' => 'Svalbard and Jan Mayen', 'z' => 'Arctic/Longyearbyen' ),
			array( 'c' => 'Swaziland', 'z' => 'Africa/Mbabane' ),
			array( 'c' => 'Sweden', 'z' => 'Europe/Stockholm' ),
			array( 'c' => 'Switzerland', 'z' => 'Europe/Zurich' ),
			array( 'c' => 'Syrian Arab Republic', 'z' => 'Asia/Damascus' ),
			array( 'c' => 'Ukraine', 'z' => 'Europe/Kiev' ),
			array( 'c' => 'Ukraine', 'z' => 'Europe/Uzhgorod' ),
			array( 'c' => 'Ukraine', 'z' => 'Europe/Zaporozhye' ),
			array( 'c' => 'Zambia', 'z' => 'Africa/Lusaka' ),
			array( 'c' => 'Zimbabwe', 'z' => 'Africa/Harare' ),
			array( 'c' => 'Åland Islands', 'z' => 'Europe/Mariehamn' )
		),
		'+03:00' => array(
			array( 'c' => 'Antarctica', 'z' => 'Antarctica/Syowa' ),
			array( 'c' => 'Bahrain', 'z' => 'Asia/Bahrain' ),
			array( 'c' => 'Belarus', 'z' => 'Europe/Minsk' ),
			array( 'c' => 'Bulgaria', 'z' => 'Europe/Sofia' ),
			array( 'c' => 'Comoros', 'z' => 'Indian/Comoro' ),
			array( 'c' => 'Cyprus', 'z' => 'Asia/Nicosia' ),
			array( 'c' => 'Djibouti', 'z' => 'Africa/Djibouti' ),
			array( 'c' => 'Eritrea', 'z' => 'Africa/Asmara' ),
			array( 'c' => 'Estonia', 'z' => 'Europe/Tallinn' ),
			array( 'c' => 'Ethiopia', 'z' => 'Africa/Addis_Ababa' ),
			array( 'c' => 'Finland', 'z' => 'Europe/Helsinki' ),
			array( 'c' => 'Greece', 'z' => 'Europe/Athens' ),
			array( 'c' => 'Iraq', 'z' => 'Asia/Baghdad' ),
			array( 'c' => 'Israel', 'z' => 'Asia/Jerusalem' ),
			array( 'c' => 'Jordan', 'z' => 'Asia/Amman' ),
			array( 'c' => 'Kenya', 'z' => 'Africa/Nairobi' ),
			array( 'c' => 'Kuwait', 'z' => 'Asia/Kuwait' ),
			array( 'c' => 'Latvia', 'z' => 'Europe/Riga' ),
			array( 'c' => 'Lebanon', 'z' => 'Asia/Beirut' ),
			array( 'c' => 'Lithuania', 'z' => 'Europe/Vilnius' ),
			array( 'c' => 'Madagascar', 'z' => 'Indian/Antananarivo' ),
			array( 'c' => 'Mayotte', 'z' => 'Indian/Mayotte' ),
			array( 'c' => 'Moldova', 'z' => 'Europe/Chisinau' ),
			array( 'c' => 'Palestine', 'z' => 'Asia/Gaza' ),
			array( 'c' => 'Palestine', 'z' => 'Asia/Hebron' ),
			array( 'c' => 'Qatar', 'z' => 'Asia/Qatar' ),
			array( 'c' => 'Romania', 'z' => 'Europe/Bucharest' ),
			array( 'c' => 'Russia', 'z' => 'Europe/Kirov' ),
			array( 'c' => 'Russia', 'z' => 'Europe/Moscow' ),
			array( 'c' => 'Saudi Arabia', 'z' => 'Asia/Riyadh' ),
			array( 'c' => 'Somalia', 'z' => 'Africa/Mogadishu' ),
			array( 'c' => 'South Sudan', 'z' => 'Africa/Juba' ),
			array( 'c' => 'Syrian Arab Republic', 'z' => 'Asia/Damascus' ),
			array( 'c' => 'Tanzania', 'z' => 'Africa/Dar_es_Salaam' ),
			array( 'c' => 'Turkey', 'z' => 'Europe/Istanbul' ),
			array( 'c' => 'Uganda', 'z' => 'Africa/Kampala' ),
			array( 'c' => 'Ukraine', 'z' => 'Europe/Kiev' ),
			array( 'c' => 'Ukraine', 'z' => 'Europe/Simferopol' ),
			array( 'c' => 'Ukraine', 'z' => 'Europe/Uzhgorod' ),
			array( 'c' => 'Ukraine', 'z' => 'Europe/Zaporozhye' ),
			array( 'c' => 'Yemen', 'z' => 'Asia/Aden' ),
			array( 'c' => 'Åland Islands', 'z' => 'Europe/Mariehamn' )
		),
		'+03:30' => array(
			array( 'c' => 'Iran', 'z' => 'Asia/Tehran' )
		),
		'+04:00' => array(
			array( 'c' => 'Armenia', 'z' => 'Asia/Yerevan' ),
			array( 'c' => 'Azerbaijan', 'z' => 'Asia/Baku' ),
			array( 'c' => 'Georgia', 'z' => 'Asia/Tbilisi' ),
			array( 'c' => 'Mauritius', 'z' => 'Indian/Mauritius' ),
			array( 'c' => 'Oman', 'z' => 'Asia/Muscat' ),
			array( 'c' => 'Russia', 'z' => 'Europe/Astrakhan' ),
			array( 'c' => 'Russia', 'z' => 'Europe/Samara' ),
			array( 'c' => 'Russia', 'z' => 'Europe/Saratov' ),
			array( 'c' => 'Russia', 'z' => 'Europe/Ulyanovsk' ),
			array( 'c' => 'Russia', 'z' => 'Europe/Volgograd' ),
			array( 'c' => 'Réunion', 'z' => 'Indian/Reunion' ),
			array( 'c' => 'Seychelles', 'z' => 'Indian/Mahe' ),
			array( 'c' => 'United Arab Emirates', 'z' => 'Asia/Dubai' )
		),
		'+04:30' => array(
			array( 'c' => 'Afghanistan', 'z' => 'Asia/Kabul' ),
			array( 'c' => 'Iran', 'z' => 'Asia/Tehran' )
		),
		'+05:00' => array(
			array( 'c' => 'Antarctica', 'z' => 'Antarctica/Mawson' ),
			array( 'c' => 'French Southern Territories', 'z' => 'Indian/Kerguelen' ),
			array( 'c' => 'Kazakhstan', 'z' => 'Asia/Aqtau' ),
			array( 'c' => 'Kazakhstan', 'z' => 'Asia/Aqtobe' ),
			array( 'c' => 'Kazakhstan', 'z' => 'Asia/Atyrau' ),
			array( 'c' => 'Kazakhstan', 'z' => 'Asia/Oral' ),
			array( 'c' => 'Kazakhstan', 'z' => 'Asia/Qyzylorda' ),
			array( 'c' => 'Maldives', 'z' => 'Indian/Maldives' ),
			array( 'c' => 'Pakistan', 'z' => 'Asia/Karachi' ),
			array( 'c' => 'Russia', 'z' => 'Asia/Yekaterinburg' ),
			array( 'c' => 'Tajikistan', 'z' => 'Asia/Dushanbe' ),
			array( 'c' => 'Turkmenistan', 'z' => 'Asia/Ashgabat' ),
			array( 'c' => 'Uzbekistan', 'z' => 'Asia/Samarkand' ),
			array( 'c' => 'Uzbekistan', 'z' => 'Asia/Tashkent' )
		),
		'+05:30' => array(
			array( 'c' => 'India', 'z' => 'Asia/Kolkata' ),
			array( 'c' => 'Sri Lanka', 'z' => 'Asia/Colombo' )
		),
		'+05:45' => array(
			array( 'c' => 'Nepal', 'z' => 'Asia/Kathmandu' )
		),
		'+06:00' => array(
			array( 'c' => 'Antarctica', 'z' => 'Antarctica/Vostok' ),
			array( 'c' => 'Bangladesh', 'z' => 'Asia/Dhaka' ),
			array( 'c' => 'Bhutan', 'z' => 'Asia/Thimphu' ),
			array( 'c' => 'British Indian Ocean Territory', 'z' => 'Indian/Chagos' ),
			array( 'c' => 'China', 'z' => 'Asia/Urumqi' ),
			array( 'c' => 'Kazakhstan', 'z' => 'Asia/Almaty' ),
			array( 'c' => 'Kyrgyzstan', 'z' => 'Asia/Bishkek' ),
			array( 'c' => 'Russia', 'z' => 'Asia/Omsk' )
		),
		'+06:30' => array(
			array( 'c' => 'Cocos (Keeling) Islands', 'z' => 'Indian/Cocos' ),
			array( 'c' => 'Myanmar', 'z' => 'Asia/Yangon' )
		),
		'+07:00' => array(
			array( 'c' => 'Antarctica', 'z' => 'Antarctica/Davis' ),
			array( 'c' => 'Cambodia', 'z' => 'Asia/Phnom_Penh' ),
			array( 'c' => 'Christmas Island', 'z' => 'Indian/Christmas' ),
			array( 'c' => 'Indonesia', 'z' => 'Asia/Jakarta' ),
			array( 'c' => 'Indonesia', 'z' => 'Asia/Pontianak' ),
			array( 'c' => "Lao People's Democratic Republic", 'z' => 'Asia/Vientiane' ),
			array( 'c' => 'Mongolia', 'z' => 'Asia/Hovd' ),
			array( 'c' => 'Russia', 'z' => 'Asia/Barnaul' ),
			array( 'c' => 'Russia', 'z' => 'Asia/Krasnoyarsk' ),
			array( 'c' => 'Russia', 'z' => 'Asia/Novokuznetsk' ),
			array( 'c' => 'Russia', 'z' => 'Asia/Novosibirsk' ),
			array( 'c' => 'Russia', 'z' => 'Asia/Tomsk' ),
			array( 'c' => 'Thailand', 'z' => 'Asia/Bangkok' ),
			array( 'c' => 'Vietnam', 'z' => 'Asia/Ho_Chi_Minh' )
		),
		'+08:00' => array(
			array( 'c' => 'Australia', 'z' => 'Australia/Perth' ),
			array( 'c' => 'Brunei Darussalam', 'z' => 'Asia/Brunei' ),
			array( 'c' => 'China', 'z' => 'Asia/Shanghai' ),
			array( 'c' => 'Hong Kong', 'z' => 'Asia/Hong_Kong' ),
			array( 'c' => 'Indonesia', 'z' => 'Asia/Makassar' ),
			array( 'c' => 'Macao', 'z' => 'Asia/Macau' ),
			array( 'c' => 'Malaysia', 'z' => 'Asia/Kuala_Lumpur' ),
			array( 'c' => 'Malaysia', 'z' => 'Asia/Kuching' ),
			array( 'c' => 'Mongolia', 'z' => 'Asia/Choibalsan' ),
			array( 'c' => 'Mongolia', 'z' => 'Asia/Ulaanbaatar' ),
			array( 'c' => 'Philippines', 'z' => 'Asia/Manila' ),
			array( 'c' => 'Russia', 'z' => 'Asia/Irkutsk' ),
			array( 'c' => 'Singapore', 'z' => 'Asia/Singapore' ),
			array( 'c' => 'Taiwan', 'z' => 'Asia/Taipei' )
		),
		'+08:45' => array(
			array( 'c' => 'Australia', 'z' => 'Australia/Eucla' )
		),
		'+09:00' => array(
			array( 'c' => 'Indonesia', 'z' => 'Asia/Jayapura' ),
			array( 'c' => 'Japan', 'z' => 'Asia/Tokyo' ),
			array( 'c' => 'North Korea', 'z' => 'Asia/Pyongyang' ),
			array( 'c' => 'Palau', 'z' => 'Pacific/Palau' ),
			array( 'c' => 'Russia', 'z' => 'Asia/Chita' ),
			array( 'c' => 'Russia', 'z' => 'Asia/Khandyga' ),
			array( 'c' => 'Russia', 'z' => 'Asia/Yakutsk' ),
			array( 'c' => 'South Korea', 'z' => 'Asia/Seoul' ),
			array( 'c' => 'Timor-Leste', 'z' => 'Asia/Dili' )
		),
		'+09:30' => array(
			array( 'c' => 'Australia', 'z' => 'Australia/Adelaide' ),
			array( 'c' => 'Australia', 'z' => 'Australia/Broken_Hill' ),
			array( 'c' => 'Australia', 'z' => 'Australia/Darwin' )
		),
		'+10:00' => array(
			array( 'c' => 'Antarctica', 'z' => 'Antarctica/DumontDUrville' ),
			array( 'c' => 'Australia', 'z' => 'Australia/Sydney' ),
			array( 'c' => 'Australia', 'z' => 'Australia/Brisbane' ),
			array( 'c' => 'Australia', 'z' => 'Australia/Currie' ),
			array( 'c' => 'Australia', 'z' => 'Australia/Hobart' ),
			array( 'c' => 'Australia', 'z' => 'Australia/Lindeman' ),
			array( 'c' => 'Australia', 'z' => 'Australia/Melbourne' ),
			array( 'c' => 'Guam', 'z' => 'Pacific/Guam' ),
			array( 'c' => 'Micronesia', 'z' => 'Pacific/Chuuk' ),
			array( 'c' => 'Northern Mariana Islands', 'z' => 'Pacific/Saipan' ),
			array( 'c' => 'Papua New Guinea', 'z' => 'Pacific/Port_Moresby' ),
			array( 'c' => 'Russia', 'z' => 'Asia/Ust-Nera' ),
			array( 'c' => 'Russia', 'z' => 'Asia/Vladivostok' )
		),
		'+10:30' => array(
			array( 'c' => 'Australia', 'z' => 'Australia/Adelaide' ),
			array( 'c' => 'Australia', 'z' => 'Australia/Broken_Hill' ),
			array( 'c' => 'Australia', 'z' => 'Australia/Lord_Howe' )
		),
		'+11:00' => array(
			array( 'c' => 'Antarctica', 'z' => 'Antarctica/Casey' ),
			array( 'c' => 'Australia', 'z' => 'Australia/Sydney' ),
			array( 'c' => 'Australia', 'z' => 'Antarctica/Macquarie' ),
			array( 'c' => 'Australia', 'z' => 'Australia/Currie' ),
			array( 'c' => 'Australia', 'z' => 'Australia/Hobart' ),
			array( 'c' => 'Australia', 'z' => 'Australia/Lord_Howe' ),
			array( 'c' => 'Australia', 'z' => 'Australia/Melbourne' ),
			array( 'c' => 'Micronesia', 'z' => 'Pacific/Pohnpei' ),
			array( 'c' => 'Micronesia', 'z' => 'Pacific/Kosrae' ),
			array( 'c' => 'New Caledonia', 'z' => 'Pacific/Noumea' ),
			array( 'c' => 'Norfolk Island', 'z' => 'Pacific/Norfolk' ),
			array( 'c' => 'Papua New Guinea', 'z' => 'Pacific/Bougainville' ),
			array( 'c' => 'Russia', 'z' => 'Asia/Magadan' ),
			array( 'c' => 'Russia', 'z' => 'Asia/Sakhalin' ),
			array( 'c' => 'Russia', 'z' => 'Asia/Srednekolymsk' ),
			array( 'c' => 'Solomon Islands', 'z' => 'Pacific/Guadalcanal' ),
			array( 'c' => 'Vanuatu', 'z' => 'Pacific/Efate' )
		),
		'+12:00' => array(
			array( 'c' => 'Antarctica', 'z' => 'Antarctica/McMurdo' ),
			array( 'c' => 'Fiji', 'z' => 'Pacific/Fiji' ),
			array( 'c' => 'Kiribati', 'z' => 'Pacific/Tarawa' ),
			array( 'c' => 'Marshall Islands', 'z' => 'Pacific/Kwajalein' ),
			array( 'c' => 'Marshall Islands', 'z' => 'Pacific/Majuro' ),
			array( 'c' => 'Nauru', 'z' => 'Pacific/Nauru' ),
			array( 'c' => 'New Zealand', 'z' => 'Pacific/Auckland' ),
			array( 'c' => 'Russia', 'z' => 'Asia/Anadyr' ),
			array( 'c' => 'Russia', 'z' => 'Asia/Kamchatka' ),
			array( 'c' => 'Tuvalu', 'z' => 'Pacific/Funafuti' ),
			array( 'c' => 'United States Minor Outlying Islands', 'z' => 'Pacific/Wake' ),
			array( 'c' => 'Wallis and Futuna', 'z' => 'Pacific/Wallis' )
		),
		'+12:45' => array(
			array( 'c' => 'New Zealand', 'z' => 'Pacific/Chatham' )
		),
		'+13:00' => array(
			array( 'c' => 'Antarctica', 'z' => 'Antarctica/McMurdo' ),
			array( 'c' => 'Fiji', 'z' => 'Pacific/Fiji' ),
			array( 'c' => 'Kiribati', 'z' => 'Pacific/Enderbury' ),
			array( 'c' => 'New Zealand', 'z' => 'Pacific/Auckland' ),
			array( 'c' => 'Samoa', 'z' => 'Pacific/Apia' ),
			array( 'c' => 'Tokelau', 'z' => 'Pacific/Fakaofo' ),
			array( 'c' => 'Tonga', 'z' => 'Pacific/Tongatapu' )
		),
		'+13:45' => array(
			array( 'c' => 'New Zealand', 'z' => 'Pacific/Chatham' )
		),
		'+14:00' => array(
			array( 'c' => 'Kiribati', 'z' => 'Pacific/Kiritimati' ),
			array( 'c' => 'Samoa', 'z' => 'Pacific/Apia' ),
			array( 'c' => 'Tonga', 'z' => 'Pacific/Tongatapu' )
		)
	) );
}

/**
 * Filter post meta advance fields keys to show business hours for the day.
 *
 * @since 2.1.0.7
 *
 * @param array $fields The custom fields keys.
 * @param string $post_type The post type.
 * @return array The custom fields keys.
 */
function geodir_post_meta_business_hours_days( $fields, $post_type ) {
	$days = array(
		'today' => __( 'Today', 'geodirectory' ),
		'mon' => __( 'Mon', 'geodirectory' ),
		'tue' => __( 'Tue', 'geodirectory' ),
		'wed' => __( 'Wed', 'geodirectory' ),
		'thu' => __( 'Thu', 'geodirectory' ),
		'fri' => __( 'Fri', 'geodirectory' ),
		'sat' => __( 'Sat', 'geodirectory' ),
		'sun' => __( 'Sun', 'geodirectory' ),
	);

	foreach ( $days as $key => $title ) {
		$fields['business_hours_' . $key ] = array(
			'type' => 'custom',
			'name' => 'business_hours_' . $key,
			'htmlvar_name' => 'business_hours_' . $key,
			'frontend_title' => $title,
			'field_icon' => 'fas fa-clock',
			'field_type_key' => '',
			'css_class' => '',
			'extra_fields' => array(
				'day' => $key
			)
		);
	}

	return $fields;
}
add_filter( 'geodir_post_meta_advance_fields', 'geodir_post_meta_business_hours_days', 50, 2 );

function geodir_business_hours_post_meta( $request ) {
	$design_style = geodir_design_style();
	$post_ids = ! empty( $request['post_id'] ) ? geodir_clean( $request['post_id'] ) : 0;
	$date = ! empty( $request['date'] ) ? geodir_clean( $request['date'] ) : 0;

	$response = array();

	if ( ! empty( $post_ids ) && is_array( $post_ids ) ) {
		$day_nos = geodir_day_short_names();
		$day = $day_nos[ date( 'N', strtotime( $date ) ) ];

		foreach ( $post_ids as $post_id ) {
			$gd_post = geodir_get_post_info( (int) $post_id );

			if ( ! ( ! empty( $gd_post ) && ! empty( $gd_post->business_hours ) && geodir_check_field_visibility( $gd_post->package_id, 'business_hours', $gd_post->post_type ) ) ) {
				continue;
			}

			$business_hours = geodir_get_business_hours( stripslashes_deep( $gd_post->business_hours ), ( ! empty( $gd_post->country ) ? $gd_post->country : '' ) );

			if ( ! ( ! empty( $business_hours['days'] ) && ! empty( $business_hours['days'][ $day ] ) ) ) {
				continue;
			}
			$hours = $business_hours['days'][ $day ];

			$has_open = false;
			if ( ! empty( $hours['open'] ) ) {
				$has_open = true;
			}

			$slots_class = ' gd-bh-stoday gd-bh-done';
			if ( $design_style ) {
				$slots_class .= ' d-inline-block';
			}
			$slots_attr = 'data-bhs-day="' . (int) date( 'd', strtotime( $date ) ) . '" data-bhs-id="' . (int) $gd_post->ID . '"';

			$slots = '<div class="gd-bh-slots' . $slots_class . '" ' . $slots_attr . '>';
			foreach ( $hours['slots'] as $i => $slot ) {
				$slot_class = '';
				if ( ! empty( $slot['open'] ) ) {
					$slot_class .= ' gd-bh-open-now';

					if ( ! $has_open ) {
						$has_open = true;
					}
				}
				$slots .= '<div class="gd-bh-slot' . $slot_class . '"><div class="gd-bh-slot-r">' . $slot['range'] . '</div></div>';
			}
			$slots .= '</div>';

			$css_class = '';
			if ( ! empty( $has_open ) ) {
				$css_class .= ' gd-bh-open-today';
			}

			if ( ! empty( $hours['closed'] ) ) {
				$css_class .= ' gd-bh-days-closed';
			}

			$response['slots'][ (int) $post_id ] = array( 'slot' => $slots, 'css_class' => trim( $css_class ) );
		}
	}

	return $response;
}