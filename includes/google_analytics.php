<?php
/**
 * Google analystics related functions.
 *
 * @since 1.0.0
 * @package GeoDirectory
 */

/**
 * Formats seconds into to h:m:s.
 *
 * @since 1.0.0
 *
 * @param int  $sec The number of seconds.
 * @param bool $padHours Whether add leading zero for less than 10 hours. Default false.
 * @return string h:m:s format.
 */
function geodir_sec2hms($sec, $padHours = false)
{
    // holds formatted string
    $hms = "";
    // there are 3600 seconds in an hour, so if we
    // divide total seconds by 3600 and throw away
    // the remainder, we've got the number of hours
    $hours = intval(intval($sec) / 3600);

    // add to $hms, with a leading 0 if asked for
    $hms .= ($padHours) ? str_pad($hours, 2, "0", STR_PAD_LEFT) . ':' : $hours . ':';

    // dividing the total seconds by 60 will give us
    // the number of minutes, but we're interested in
    // minutes past the hour: to get that, we need to
    // divide by 60 again and keep the remainder
    $minutes = intval(($sec / 60) % 60);

    // then add to $hms (with a leading 0 if needed)
    $hms .= str_pad($minutes, 2, "0", STR_PAD_LEFT) . ':';

    // seconds are simple - just divide the total
    // seconds by 60 and keep the remainder
    $seconds = intval($sec % 60);

    // add to $hms, again with a leading 0 if needed
    $hms .= str_pad($seconds, 2, "0", STR_PAD_LEFT);

    // done!
    return $hms;
}

/**
 * Get the google analytics via api.
 *
 * @since 1.0.0
 *
 * @param string $page Page url to use in analytics filters.
 * @param bool   $ga_start The start date of the data to include in YYYY-MM-DD format.
 * @param bool   $ga_end The end date of the data to include in YYYY-MM-DD format.
 * @return string Html text content.
 */
function geodir_getGoogleAnalytics($page, $ga_start, $ga_end)
{

    // NEW ANALYTICS

    $start_date = '';
    $end_date = '';
    $dimensions = '';
    $sort = '';
    $filters = "ga:pagePath==".$page;
    $metrics = "ga:pageviews";
    $realtime = false;
    $limit = false;
    if(isset($_REQUEST['ga_type']) && $_REQUEST['ga_type']=='thisweek'){
        $start_date = date('Y-m-d', strtotime("-6 day"));
        $end_date = date('Y-m-d');
        $dimensions = "ga:date,ga:nthDay";
    }elseif(isset($_REQUEST['ga_type']) && $_REQUEST['ga_type']=='lastweek'){
        $start_date = date('Y-m-d', strtotime("-13 day"));
        $end_date = date('Y-m-d', strtotime("-7 day"));
        $dimensions = "ga:date,ga:nthDay";
    }
    elseif(isset($_REQUEST['ga_type']) && $_REQUEST['ga_type']=='thisyear'){
        $start_date = date('Y')."-01-01";
        $end_date = date('Y-m-d');
        $dimensions = "ga:month,ga:nthMonth";
    }
    elseif(isset($_REQUEST['ga_type']) && $_REQUEST['ga_type']=='lastyear'){
        $start_date = date('Y', strtotime("-1 year"))."-01-01";
        $end_date = date('Y', strtotime("-1 year"))."-12-31";
        $dimensions = "ga:month,ga:nthMonth";
    }
    elseif(isset($_REQUEST['ga_type']) && $_REQUEST['ga_type']=='country'){
        $start_date = "14daysAgo";
        $end_date = "yesterday";
        $dimensions = "ga:country";
        $sort = "ga:pageviews";
        $limit  = 5;
    }elseif(isset($_REQUEST['ga_type']) && $_REQUEST['ga_type']=='realtime'){
        $metrics = "rt:activeUsers";
        $realtime = true;
    }

    # Create a new Gdata call
    $gaApi = new GDGoogleAnalyticsStats();

    # Check if Google sucessfully logged in
    if (!$gaApi->checkLogin()){
        echo json_encode(array('error'=>__('Please check Google Analytics Settings','geodirectory')));
        return false;
    }

    $account = $gaApi->getSingleProfile();

    if(!isset($account[0]['id'])){
        echo json_encode(array('error'=>__('Please check Google Analytics Settings','geodirectory')));
        return false;
    }

    $account = $account[0]['id'];

    # Set the account to the one requested
    $gaApi->setAccount($account);



    # Get the metrics needed to build the visits graph;
    try {
        $stats = $gaApi->getMetrics($metrics, $start_date, $end_date, $dimensions, $sort, $filters, $limit , $realtime);
    }
    catch (Exception $e) {
        print 'GA Summary Widget - there was a service error ' . $e->getCode() . ':' . $e->getMessage();
    }


    //print_r($stats);
    echo json_encode($stats);
    exit;


}// end GA function


function geodir_ga_get_token(){
    $at = get_option('gd_ga_access_token');
    $use_url = "https://www.googleapis.com/oauth2/v1/tokeninfo?access_token=".$at;
    $response =  wp_remote_get($use_url,array('timeout' => 15));

    if(!empty($response['response']['code']) && $response['response']['code']==200) {//access token is valid

    return $at;
    }else{//else get new access token

        $refresh_at = get_option('gd_ga_refresh_token');
        if(!$refresh_at){
            echo json_encode(array('error'=>__('Not authorized, please click authorized in GD > Google analytic settings.', 'geodirectory')));exit;
        }

        $rat_url = "https://www.googleapis.com/oauth2/v3/token?";
        $client_id = "client_id=".get_option('geodir_ga_client_id');
        $client_secret = "&client_secret=".get_option('geodir_ga_client_secret');
        $refresh_token = "&refresh_token=".$refresh_at;
        $grant_type = "&grant_type=refresh_token";

        $rat_url_use = $rat_url.$client_id.$client_secret.$refresh_token.$grant_type;

        $rat_response =  wp_remote_post($rat_url_use,array('timeout' => 15));
        if(!empty($rat_response['response']['code']) && $rat_response['response']['code']==200) {
            $parts = json_decode($rat_response['body']);


            update_option('gd_ga_access_token', $parts->access_token);
            return $parts->access_token;

        }else{
            echo json_encode(array('error'=>__('Login failed', 'geodirectory')));exit;
        }


    }

}