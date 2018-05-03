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
        'Mo'    	=> __( 'Monday' ),
        'Tu'  	=> __( 'Tuesday' ),
        'We' 	=> __( 'Wednesday' ),
        'Th'  	=> __( 'Thursday' ),
        'Fr'    	=> __( 'Friday' ),
        'Sa'  	=> __( 'Saturday' ),
        'Su'    	=> __( 'Sunday' )
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
 * Get UTC Offset.
 *
 * @since 2.0.0
 *
 * @param bool $formatted Format the offset.
 * @return string Formatted offset.
 */
function geodir_gmt_offset( $formatted = true ) {
    $offset = geodir_get_option( 'default_location_timezone' );
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
 * Convert the seconds to hmmm.
 *
 * @since 2.0.0
 *
 * @param string $seconds.
 * @return string Formatted hmmm.
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
 * Return the default weekdays.
 *
 * @since 2.0.0
 *
 * @return array default weekdays.
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
 * Convert array to schema.
 *
 * @since 2.0.0
 *
 * @param array $schema_input.
 * @return string $schema.
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
 * Convert schema to array.
 *
 * @since 2.0.0
 *
 * @param string $schema.
 * @return array $return.
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
 * Convert the parse property string to array.
 *
 * @since 2.0.0
 *
 * @param string $str.
 * @return array $property.
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
 * Convert the parse days string to array.
 *
 * @since 2.0.0
 *
 * @param string $days_str.
 * @return array $return.
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
 * Convert days range string to array.
 *
 * @since 2.0.0
 *
 * @param string $days_str.
 * @return array $return.
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
 * Convert parse hours string to array.
 *
 * @since 2.0.0
 *
 * @param string $hours_str.
 * @return array $return.
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
 * Convert hour range string to array.
 *
 * @since 2.0.0
 *
 * @param string $hours_str.
 * @return array $return.
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
 * Get Business hours.
 *
 * @since 2.0.0
 *
 * @param string $value.
 * @return array $hours.
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

    if ( ! empty( $data['hours'] ) || ! empty( $data['offset'] ) ) {
        $days = geodir_get_weekdays();
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

        $hours = array();
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
            'offset' => ! empty( $data['hours']['offset'] ) ? $data['hours']['offset'] : geodir_gmt_offset()
        );
    }

    return apply_filters( 'geodir_get_business_hours', $hours, $data );
}

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

function geodir_delete_business_hours( $post_ID ) {
    global $wpdb;

    if ( empty( $post_ID ) ) {
        return false;
    }

    return $wpdb->query( $wpdb->prepare( "DELETE FROM `" . GEODIR_BUSINESS_HOURS_TABLE . "` WHERE `post_id` = %d", array( $post_ID ) ) );
}

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