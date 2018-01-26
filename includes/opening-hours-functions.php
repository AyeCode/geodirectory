<?php
/**
 * Opening hours related functions.
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
       '1'    	=> __( 'Monday', 'geodirectory' ), 
       '2'  	=> __( 'Tuesday', 'geodirectory' ),  
       '3' 		=> __( 'Wednesday', 'geodirectory' ),  
       '4'  	=> __( 'Thursday', 'geodirectory' ),  
       '5'    	=> __( 'Friday', 'geodirectory' ),  
       '6'  	=> __( 'Saturday', 'geodirectory' ),
       '7'    	=> __( 'Sunday' , 'geodirectory' )
   );

   return apply_filters( 'geodir_get_weekdays', $weekdays );
}