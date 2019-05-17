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
function geodir_get_weekdays($untranslated = false) {

	if($untranslated){
		$weekdays = array(
			'Mo'    => 'Monday',
			'Tu'  	=> 'Tuesday',
			'We' 	=> 'Wednesday',
			'Th'  	=> 'Thursday',
			'Fr'    => 'Friday',
			'Sa'  	=> 'Saturday',
			'Su'    => 'Sunday'
		);
	}else{
		$weekdays = array(
			'Mo'    	=> __( 'Monday' ),
			'Tu'  	=> __( 'Tuesday' ),
			'We' 	=> __( 'Wednesday' ),
			'Th'  	=> __( 'Thursday' ),
			'Fr'    	=> __( 'Friday' ),
			'Sa'  	=> __( 'Saturday' ),
			'Su'    	=> __( 'Sunday' )
		);
	}

   return apply_filters( 'geodir_get_weekdays', $weekdays,$untranslated );
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
	$offset = $offeset || $offeset == '0' ? $offeset : geodir_get_option( 'default_location_timezone' );
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

	$offset = $offeset || $offeset == '0' ? $offeset : geodir_get_option( 'default_location_timezone' );
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
 * @return string $hhmm Formatted hhmm.
 */
function geodir_seconds_to_hhmm( $seconds ) {
	$sign = $seconds < 0 ? '-' : '+';
	$seconds = absint( $seconds );
	$hours = floor( $seconds / 3600 );
	$minutes = floor( ( $seconds - ( $hours * 3600 ) ) / 60 );
	$hhmm = $hours;
	$hhmm .= ":" . ( $minutes < 10 ? "0" . (string) $minutes : (string) $minutes );
	$hhmm = $sign . '' .  $hhmm;
	return $hhmm;
}

/**
 * Prints a string showing current time zone offset to UTC, considering daylight savings time.
 * @link                     http://php.net/manual/en/timezones.php
 * @param  string $time_zone Time zone name
 * @return string            Offset in hours, prepended by +/-
 */
function geodir_utc_offset_dst( $time_zone = 'Europe/Berlin' ) {
	// Set UTC as default time zone.
	date_default_timezone_set( 'UTC' );
	$utc = new DateTime();
	// Calculate offset.
	$current   = timezone_open( $time_zone );
	$offset_s  = timezone_offset_get( $current, $utc ); // seconds
	$offset_h  = $offset_s / ( 60 * 60 ); // hours
	// Prepend “+” when positive
	$offset_h  = (string) $offset_h;
	if ( strpos( $offset_h, '-' ) === FALSE ) {
		$offset_h = '+' . $offset_h; // prepend +
	}
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
		// Set UTC as default time zone.
		date_default_timezone_set( 'UTC' );
		$utc = new DateTime();
		// Calculate offset.
		$gmt_offset_s = timezone_offset_get( new DateTimeZone("Europe/London"), $utc ); // seconds
		$current   = timezone_open( $timezone );
		$offset_s  = timezone_offset_get( $current, $utc ); // seconds
		$offset_s = $offset_s - $gmt_offset_s; // remove DST
		$offset_h  = $offset_s / ( 60 * 60 ); // hours
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
	$property[] = '["UTC":"' . $offset . '"]';

	$schema = implode( ",", $property );
	
	return $schema;
}

/**
 * Converts business hour schema to array output.
 *
 * @since 2.0.0
 *
 * @param string $schema Business hour schema.
 * @return array $return Offset and hour.
 */
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
		$gmt_offset = str_replace(array('"UTC":"', '"', '[', ']'), '', $schema_array[1]);
		$return['offset'] = ( ! empty( $gmt_offset ) ? $gmt_offset : geodir_gmt_offset() );
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
		$closes = ! empty( $hours_arr[1] ) ? trim( $hours_arr[1] ) : '23:59';
		if ( strpos( $closes, '00:00' ) === 0 ) {
			$closes = '23:59';
		}
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
function geodir_get_business_hours( $value = '' ) {
	if ( empty( $value ) ) {
		return NULL;
	}
	
	if ( ! is_array( $value ) ) {
		$data = geodir_schema_to_array( stripslashes_deep( $value ) );
	} else {
		$data = $value;
	}

	$hours = array();
	if ( ! empty( $data['hours'] ) || ! empty( $data['offset'] ) ) {
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

		$has_open = 0;
		$has_closed = 0;
		$day_slots = array();
		$today_range = $closed_label;
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
					$opens = $slot['opens'];
					$closes = ! empty( $slot['closes'] ) ? $slot['closes'] : '23:59';
					$opens_time = strtotime( $opens );
					$closes_time = strtotime( date_i18n( 'H:i:59', strtotime( $closes ) ) );
					
					if ( $is_today && $opens_time <= $time_int && $time_int <= $closes_time ) {
						$is_open = 1;
						$has_open = 1;
					} else {
						$is_open = 0;
					}
					$range = date_i18n( $time_format, $opens_time ) . ' - ' . date_i18n( $time_format, $closes_time );
					$day_range[] = $range;

					$ranges[] = array( 
						'slot' => $opens . '-' . $closes,
						'range' => $range,
						'open' => $is_open,
						'time' => array( date_i18n( 'Hi', $opens_time ) , date_i18n( 'Hi', $closes_time ) ),
						'minutes' => array( geodir_hhmm_to_bh_minutes( $opens, $day_no ), geodir_hhmm_to_bh_minutes( $closes, $day_no ) )
					);
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
			'offset' => isset( $data['offset'] ) && $data['offset'] !== '' ? $data['offset'] : geodir_gmt_offset()
		);
	}

	return apply_filters( 'geodir_get_business_hours', $hours, $data );
}

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

	$diff = 0;
	if ( $day_no > 0 ) {
		$diff = ( $day_no - 1 ) * 60 * 24;
	}

    return ( ( $hours * 60 ) + $minutes ) + $diff;
}

/**
 * Save the business hours.
 *
 * @since 2.0.0
 *
 * @param int $post_ID Places post id.
 * @param string $data Optional. Business hour metadata. Default NULL.
 * @return int|false $saved The number of rows inserted, or false on error.
 */
function geodir_save_business_hours( $post_ID, $data = NULL ) {
	global $wpdb;

	if ( empty( $post_ID ) ) {
		return false;
	}

	if ( $data === NULL ) {
		$data = get_post_meta( $post_ID, 'business_hours', true );
	}

	// clear old rows
	$saved = geodir_delete_business_hours( $post_ID );

	$business_hours = geodir_get_business_hours( $data );
	if ( ! empty( $business_hours['days'] ) ) {
		foreach ( $business_hours['days'] as $day => $hours ) {
			if ( empty( $hours['closed'] ) && ! empty( $hours['slots'] ) && ! empty( $hours['day_no'] ) ) {
				foreach ( $hours['slots'] as $slot ) {
					if ( ! empty( $slot['minutes'] ) && is_array( $slot['minutes'] ) && count( $slot['minutes'] ) == 2 ) {
						$saved = $wpdb->insert( GEODIR_BUSINESS_HOURS_TABLE, array( 'post_id' => $post_ID, 'open' => $slot['minutes'][0], 'close' => $slot['minutes'][1] ) );
					}
				}
			}
		}
	}

	return $saved;
}

/**
 * Delete the business hours.
 *
 * @since 2.0.0
 *
 * @param int $post_ID Places post id.
 * @return int|false Number of rows affected/selected or false on error.
 */
function geodir_delete_business_hours( $post_ID ) {
	global $wpdb;
	
	if ( empty( $post_ID ) ) {
		return false;
	}

	return $wpdb->query( $wpdb->prepare( "DELETE FROM `" . GEODIR_BUSINESS_HOURS_TABLE . "` WHERE `post_id` = %d", array( $post_ID ) ) );
}

/**
 * Update the business hours.
 *
 * @since 2.0.0
 *
 * @param array $post_data Business hours data.
 * @param bool $update Optional. True or false. Default false.
 */
function geodir_update_business_hours( $post_data, $update = false ) {
	global $gd_business_hours_updated;

	if ( empty( $post_data['ID'] ) ) {
		return;
	}

	$post_ID = $post_data['ID'];
	$post    = geodir_get_post_info( $post_ID );
	if ( empty( $post ) ) {
		return;
	}
	
	if ( empty( $gd_business_hours_updated ) ) {
		$gd_business_hours_updated = array();
	}
	
	if ( ! in_array( $post_ID, $gd_business_hours_updated ) ) {
		$gd_business_hours_updated[] = $post_ID;
	}
	
	$business_hours = isset( $post->business_hours ) ? $post->business_hours : '';
	
	geodir_save_business_hours( $post_ID, $business_hours );
}
//add_action( 'geodir_ajax_post_saved', 'geodir_update_business_hours', 0, 2 ); @remove this once we implement search by open_now

/**
 * Sanitize business hours value.
 *
 * @since 2.0.0
 *
 * @param string $value Business hours value.
 * @param string $custom_field Custom field.
 * @param int $post_id Post id.
 * @param object $post Post.
 * @param string $update Update.
 * @return string $value Sanitize business hours.
 */
function geodir_sanitize_business_hours_value( $value, $custom_field, $post_id, $post, $update ) {
	if ( ! empty( $value ) && ! is_array( $value ) ) {
		$value = stripslashes( $value );
		if ( strpos( $value, '"UTC"' ) === false ) {
			$value .= ',["UTC":"' . geodir_gmt_offset() . '"]';
		}
	}
	return $value;
}
add_filter( 'geodir_custom_field_value_business_hours', 'geodir_sanitize_business_hours_value', 10, 5 );

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
		$time_format = geodir_date_format_php_to_jqueryui( $time_format );
	}

	return $time_format;
}