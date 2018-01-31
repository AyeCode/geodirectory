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