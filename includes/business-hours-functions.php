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
 */
function geodir_get_weekdays() {
   $weekdays = array( 
       'Mo'    	=> __( 'Monday', 'geodirectory' ), 
       'Tu'  	=> __( 'Tuesday', 'geodirectory' ),  
       'We' 	=> __( 'Wednesday', 'geodirectory' ),  
       'Th'  	=> __( 'Thursday', 'geodirectory' ),  
       'Fr'    	=> __( 'Friday', 'geodirectory' ),  
       'Sa'  	=> __( 'Saturday', 'geodirectory' ),
       'Su'    	=> __( 'Sunday' , 'geodirectory' )
   );

   return apply_filters( 'geodir_get_weekdays', $weekdays );
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
 * Get GMT Offset.
 *
 * @since 2.0.0
 *
 * @param bool $formatted Format the offset.
 * @return string Formatted offset.
 */
function geodir_gmt_offset( $formatted = true ) {
	$offset = get_option( 'gmt_offset' );
	if ( ! $formatted ) {
		return $offset;
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

function geodir_array_to_schema( $schema_input ) {
	if ( empty( $schema_input ) || ! is_array( $schema_input ) ) {
		return $schema_input;
	}
	
	$schema_hours = ! empty( $schema_input['hours'] ) ? $schema_input['hours'] : NULL;
	if ( empty( $schema_hours ) || ! is_array( $schema_hours ) ) {
		return $schema_input;
	}

	$days = geodir_day_short_names();
	$offset = ! empty( $schema_input['offset'] ) ? $schema_input['offset'] : geodir_gmt_offset();
	$periods = array();

	foreach ( $schema_hours as $day_no => $slots ) {
		$hours = array();
		foreach ( $slots as $i => $slot ) {
			if ( ! empty( $slot['opens'] ) ) {
				$hour = $slot['opens'];
				$hour .= '-';
				$hour .= ! empty( $slot['closes'] ) ? $slot['closes'] : '23:59';
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
	$property[] = '["GMT":"' . $offset . '"]';

	$schema = implode( ",", $property );
	
	return $schema;
}

function geodir_schema_to_array( $schema ) {
	if ( empty( $schema ) ) {
		return array();
	}

	$return = array();
	$schema_array = explode('],[', $schema);
	if ( ! empty( $schema_array[0] ) ) {
		$day_names = geodir_day_short_names();
		$schema_str = $schema_array[0] . ']';
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
		$gmt_offset = str_replace(' ', '', $schema_array[1]);
		$gmt_offset = str_replace(array('"GMT":"', '"', '[', ']'), '', $schema_array[1]);
		$return['offset'] = ( ! empty( $gmt_offset ) ? $gmt_offset : geodir_gmt_offset() );
	}
	
	return $return;
}

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

function geodir_parse_hours_range( $hours_str ) {
	$hours_arr = explode( '-', $hours_str );
	
	$return = array();
	if ( ! empty( $hours_arr[0] ) ) {
		$return[] = trim( $hours_arr[0] );
		$return[] = ! empty( $hours_arr[1] ) ? trim( $hours_arr[1] ) : '23:59';
	}
	return $return;
}