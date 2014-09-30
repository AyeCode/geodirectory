<?php

function sec2hms($sec, $padHours = false) {
	// holds formatted string
	$hms = "";
	// there are 3600 seconds in an hour, so if we
	// divide total seconds by 3600 and throw away
	// the remainder, we've got the number of hours
	$hours = intval(intval($sec) / 3600); 

	// add to $hms, with a leading 0 if asked for
	$hms .= ($padHours) ? str_pad($hours, 2, "0", STR_PAD_LEFT). ':' : $hours. ':';

	// dividing the total seconds by 60 will give us
	// the number of minutes, but we're interested in 
	// minutes past the hour: to get that, we need to 
	// divide by 60 again and keep the remainder
	$minutes = intval(($sec / 60) % 60); 

	// then add to $hms (with a leading 0 if needed)
	$hms .= str_pad($minutes, 2, "0", STR_PAD_LEFT). ':';

	// seconds are simple - just divide the total
	// seconds by 60 and keep the remainder
	$seconds = intval($sec % 60); 

	// add to $hms, again with a leading 0 if needed
	$hms .= str_pad($seconds, 2, "0", STR_PAD_LEFT);

	// done!
	return $hms;
}

function geodir_getGoogleAnalytics( $page, $ga_start, $ga_end) {
	
	include_once ('analytics_api.php');
	
	// enter your login, password and id into the variables below to try it out
	$login = get_option( 'geodir_ga_user' );
	$password = get_option( 'geodir_ga_pass' );

	// NOTE: the id is in the form ga:12345 and not just 12345
	// if you do e.g. 12345 then no data will be returned
	// read http://www.electrictoolbox.com/get-id-for-google-analytics-api/ for info about how to get this id from the GA web interface
	// or load the accounts (see below) and get it from there
	// if you don't specify an id here, then you'll get the "Badly formatted request to the Google Analytics API..." error message
	$id = trim( get_option( 'geodir_ga_id' ) );
	
	$api = new analytics_api();
	
	if( $api->login( $login, $password ) ) {
	
		$testr = $page;
		$metrics['ga:pageviews'] ='0';
		$filters = new analytics_filters("ga:pagePath", "==", $testr);
		if($api->data($id, 'ga:pagePath', 'ga:pageviews,ga:uniquePageviews,ga:bounces,ga:entrances,ga:exits,ga:newVisits,ga:timeOnPage', '', $ga_start, $ga_end, 10, 1, $filters, false)){
			
			$data = $api->data($id, 'ga:pagePath', 'ga:pageviews,ga:uniquePageviews,ga:bounces,ga:entrances,ga:exits,ga:newVisits,ga:timeOnPage', '', $ga_start, $ga_end, 10, 1, $filters, false);
			
			foreach( $data as $dimension => $metrics ) {
				$time = sec2hms($metrics['ga:timeOnPage'] / ($metrics['ga:pageviews'] - $metrics['ga:exits']));
				
				echo "<b>" . __( "Google Analytics (Last 30 Days)", GEODIRECTORY_TEXTDOMAIN ) . "</b><br>" . __( "Total pageviews:", GEODIRECTORY_TEXTDOMAIN ) . " {$metrics['ga:pageviews']} <br>" . __( "Unique visitors:", GEODIRECTORY_TEXTDOMAIN ) . " {$metrics['ga:uniquePageviews']}<br>" . __( "Average time on page:", GEODIRECTORY_TEXTDOMAIN ) . " {$time}<br>\n";
			}
		
		} else {
			echo '<b>' . __( 'Google Analytics (Last 30 Days)', GEODIRECTORY_TEXTDOMAIN ) . '</b><br>' . __( 'No Stats Yet', GEODIRECTORY_TEXTDOMAIN );
		}		
	
	} else {
		echo __( 'Login failed', GEODIRECTORY_TEXTDOMAIN ) . "\n";
	}
}// end GA function


