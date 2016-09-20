<?php
/**
 * Returns current city latitude.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @todo fix location variable
 * @return string
 */
function geodir_get_current_city_lat()
{
    $location = geodir_get_default_location();
    $lat = isset($location_result->city_latitude) ? $location_result->city_latitude : '39.952484';

    return $lat;
}

/**
 * Returns current city longitude.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @todo fix location variable
 * @return string
 */
function geodir_get_current_city_lng()
{
    $location = geodir_get_default_location();
    $lng = isset($location_result->city_longitude) ? $location_result->city_longitude : '-75.163786';
    return $lng;
}


/**
 * Returns the default location.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @return object
 */
function geodir_get_default_location()
{
    /**
     * Filter the default location.
     *
     * @since 1.0.0
     * @package GeoDirectory
     *
     * @param string $location_result The default location object.
     */
    return $location_result = apply_filters('geodir_get_default_location', get_option('geodir_default_location'));
}

/**
 * Checks whether the default location is set or not.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @return bool
 */
function geodir_is_default_location_set()
{
    $default_location = geodir_get_default_location();
    if (!empty($default_location))
        return true;
    else
        return false;
}

/**
 * Returns location slug using location string.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @param string $location_string The location string.
 * @return string The location slug.
 */
function create_location_slug($location_string)
{

    /**
     * Filter the location slug.
     *
     * @since 1.0.0
     * @package GeoDirectory
     *
     * @param string $location_string Sanitized location string.
     */
    return urldecode(apply_filters('geodir_location_slug_check', sanitize_title($location_string)));

}

/**
 * Returns location object using location id.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @param string $id The location ID.
 * @return object The location object.
 */
function geodir_get_location($id = '')
{
    /**
     * Filter the location information.
     *
     * @since 1.0.0
     * @package GeoDirectory
     *
     * @param string $id The location ID.
     */
    return $location_result = apply_filters('geodir_get_location_by_id', get_option('geodir_default_location'), $id);
}

/**
 * Returns country selection dropdown box.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @param string $post_country The dropdown default selected country.
 * @param string $prefix Not yet implemented.
 */
function geodir_get_country_dl($post_country = '', $prefix = '')
{
    global $wpdb;

    $rows = $wpdb->get_results("SELECT Country,ISO2 FROM " . GEODIR_COUNTRIES_TABLE . " ORDER BY Country ASC");
    
    $ISO2 = array();
    $countries = array();
    
    foreach ($rows as $row) {
        $ISO2[$row->Country] = $row->ISO2;
        $countries[$row->Country] = __($row->Country, 'geodirectory');
    }
    
    asort($countries);
    
    $out_put = '<option ' . selected('', $post_country, false) . ' value="">' . __('Select Country', 'geodirectory') . '</option>';
    foreach ($countries as $country => $name) {
        $ccode = $ISO2[$country];

        $out_put .= '<option ' . selected($post_country, $country, false) . ' value="' . esc_attr($country) . '" data-country_code="' . $ccode . '">' . $name . '</option>';
    }

    echo $out_put;
}


/**
 * Handles location form submitted data.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 */
function geodir_location_form_submit()
{

    global $wpdb, $plugin_prefix;
    if (isset($_REQUEST['add_location'])) {

        $location_info = array(
            'city' => $_REQUEST['city'],
            'region' => $_REQUEST['region'],
            'country' => $_REQUEST['country'],
            'geo_lat' => $_REQUEST['latitude'],
            'geo_lng' => $_REQUEST['longitude'],
            'is_default' => $_REQUEST['is_default'],
            'update_city' => $_REQUEST['update_city']
        );

        $old_location = geodir_get_default_location();

        $locationid = geodir_add_new_location($location_info);

        $default_location = geodir_get_location($locationid);

        //UPDATE AND DELETE LISTING
        $posttype = geodir_get_posttypes();
        if (isset($_REQUEST['listing_action']) && $_REQUEST['listing_action'] == 'delete') {

            foreach ($posttype as $posttypeobj) {
                $post_locations = '[' . $default_location->city_slug . '],[' . $default_location->region_slug . '],[' . $default_location->country_slug . ']'; // set all overall post location

                $sql = $wpdb->prepare(
                    "UPDATE " . $plugin_prefix . $posttypeobj . "_detail SET post_city=%s, post_region=%s, post_country=%s, post_locations=%s WHERE post_location_id=%d AND ( post_city!=%s OR post_region!=%s OR post_country!=%s OR post_locations!=%s OR post_locations IS NULL)",
                    array($_REQUEST['city'], $_REQUEST['region'], $_REQUEST['country'], $post_locations, $locationid, $_REQUEST['city'], $_REQUEST['region'], $_REQUEST['country'], $post_locations)
                );
                $wpdb->query($sql);
            }
        }
    }
}

/**
 * Adds new location using location info.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @param array $location_info {
 *     Array of location info arguments.
 *
 *     @type string         $city               The city string.
 *     @type string         $region             The region string.
 *     @type string         $country            The country string.
 *     @type string         $geo_lat            The latitude string.
 *     @type string         $geo_lng            The longitude string.
 *     @type string|bool    $is_default         Is this the default location?.
 * }
 * @return string|bool Location ID on success. False when Fail.
 */
function geodir_add_new_location($location_info = array())
{
    global $wpdb;

    if (!empty($location_info)) {

        $location_city = ($location_info['city'] != '') ? $location_info['city'] : 'all';
        $location_region = ($location_info['region'] != '') ? $location_info['region'] : 'all';
        $location_country = ($location_info['country'] != '') ? $location_info['country'] : 'all';
        $location_lat = ($location_info['geo_lat'] != '') ? $location_info['geo_lat'] : '';
        $location_lng = ($location_info['geo_lng'] != '') ? $location_info['geo_lng'] : '';
        $is_default = isset($location_info['is_default']) ? $location_info['is_default'] : '';
        $country_slug = create_location_slug(__($location_country, 'geodirectory'));
        $region_slug = create_location_slug($location_region);
        $city_slug = create_location_slug($location_city);

        /**
         * Filter add new location data.
         *
         * @since 1.0.0
         */
        $geodir_location = (object)apply_filters('geodir_add_new_location', array('location_id' => 0,
            'country' => $location_country,
            'region' => $location_region,
            'city' => $location_city,
            'country_slug' => $country_slug,
            'region_slug' => $region_slug,
            'city_slug' => $city_slug,
            'city_latitude' => $location_lat,
            'city_longitude' => $location_lng,
            'is_default' => $is_default
        ));


        if ($geodir_location->country) {

            $get_country = $wpdb->get_var($wpdb->prepare("SELECT Country FROM " . GEODIR_COUNTRIES_TABLE . " WHERE Country=%s", array($geodir_location->country)));

            if (empty($get_country)) {

                $wpdb->query($wpdb->prepare("INSERT INTO " . GEODIR_COUNTRIES_TABLE . " (Country, Title) VALUES (%s,%s)", array($geodir_location->country, $geodir_location->country)));

            }

        }

        if ($geodir_location->is_default)
            update_option('geodir_default_location', $geodir_location);

        return $geodir_location->location_id;

    } else {
        return false;
    }
}

/**
 * Returns random float number.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @param int $min The minimum number. Default: 0.
 * @param int $max The maximum number. Default: 1.
 * @return float
 */
function geodir_random_float($min = 0, $max = 1)
{
    return $min + mt_rand() / mt_getrandmax() * ($max - $min);
}

/**
 * Returns address using latitude and longitude.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @param string $lat Latitude string.
 * @param string $lng Longitude string.
 * @return string|bool Returns address on success.
 */
function geodir_get_address_by_lat_lan($lat, $lng)
{
    $url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' . trim($lat) . ',' . trim($lng) ;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $response = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($response);
    $status = $data->status;
    if ($status == "OK") {
        return $data->results[0]->address_components;
    } else
        return false;
}

/**
 * Returns current location terms.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wp WordPress object.
 * @global object $gd_session GeoDirectory Session object.
 *
 * @param string $location_array_from Place to look for location array. Default: 'session'.
 * @param string $gd_post_type The post type.
 * @return array The location term array.
 */
function geodir_get_current_location_terms($location_array_from = 'session', $gd_post_type = '')
{
    global $wp, $gd_session;
    $location_array = array();
    if ($location_array_from == 'session') {
        if ($gd_session->get('gd_country') == 'me' || $gd_session->get('gd_region') == 'me' || $gd_session->get('gd_city') == 'me') {
            return $location_array;
        }

        $country = $gd_session->get('gd_country');
        if ($country != '' && $country)
            $location_array['gd_country'] = urldecode($country);

        $region = $gd_session->get('gd_region');
        if ($region != '' && $region)
            $location_array['gd_region'] = urldecode($region);

        $city = $gd_session->get('gd_city');
        if ($city != '' && $city)
            $location_array['gd_city'] = urldecode($city);
    } else {
        if ((isset($wp->query_vars['gd_country']) && $wp->query_vars['gd_country'] == 'me') || (isset($wp->query_vars['gd_region']) && $wp->query_vars['gd_region'] == 'me') || (isset($wp->query_vars['gd_city']) && $wp->query_vars['gd_city'] == 'me')) {
            return $location_array;
        }

        $country = (isset($wp->query_vars['gd_country']) && $wp->query_vars['gd_country'] != '') ? $wp->query_vars['gd_country'] : '';

        $region = (isset($wp->query_vars['gd_region']) && $wp->query_vars['gd_region'] != '') ? $wp->query_vars['gd_region'] : '';

        $city = (isset($wp->query_vars['gd_city']) && $wp->query_vars['gd_city'] != '') ? $wp->query_vars['gd_city'] : '';

        if ($country != '')
            $location_array['gd_country'] = urldecode($country);

        if ($region != '')
            $location_array['gd_region'] = urldecode($region);

        if ($city != '')
            $location_array['gd_city'] = urldecode($city);
			
		// Fix category link in ajax popular category widget on change post type
		if (empty($location_array) && defined('DOING_AJAX') && DOING_AJAX) {
			$location_array = geodir_get_current_location_terms('session');
		}
    }

	/**
	 * Filter the location terms.
	 *
	 * @since 1.4.6
     * @package GeoDirectory
	 *
     * @param array $location_array {
     *    Attributes of the location_array.
     *
     *    @type string $gd_country The country slug.
     *    @type string $gd_region The region slug.
     *    @type string $gd_city The city slug.
     *
     * }
	 * @param string $location_array_from Source type of location terms. Default session.
	 * @param string $gd_post_type WP post type.
	 */
	$location_array = apply_filters( 'geodir_current_location_terms', $location_array, $location_array_from, $gd_post_type );

    return $location_array;

}

/**
 * Returns location link based on location type.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @param string $which_location Location link type. Default: 'current'.
 * @return bool|string
 */
function geodir_get_location_link($which_location = 'current') {
    $location_link = get_permalink(geodir_location_page_id());

    if ($which_location == 'base') {
        return $location_link;
    } else {
        $location_terms = geodir_get_current_location_terms();

        if (!empty($location_terms)) {
            if (get_option('permalink_structure') != '') {
                $location_terms = implode("/", $location_terms);
                $location_terms = rtrim($location_terms, '/');
                $location_link .= $location_terms;
            } else {
                $location_link = geodir_getlink($location_link, $location_terms);
            }
        }
    }
    return $location_link;
}

/**
 * Returns openstreetmap address using latitude and longitude.
 *
 * @since 1.6.5
 * @package GeoDirectory
 * @param string $lat Latitude string.
 * @param string $lng Longitude string.
 * @return array|bool Returns address on success.
 */
function geodir_get_osm_address_by_lat_lan($lat, $lng) {
    $url = is_ssl() ? 'https:' : 'http:';
    $url .= '//nominatim.openstreetmap.org/reverse?format=json&lat=' . trim($lat) . '&lon=' . trim($lng) . '&zoom=16&addressdetails=1&email=' . get_option('admin_email');

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $response = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($response);
    
    if (!empty($data) && !empty($data->address)) {
        $address_fields = array('public_building', 'house', 'house_number', 'bakery', 'footway', 'street', 'road', 'village', 'attraction', 'pedestrian', 'neighbourhood', 'suburb');
        $formatted_address = (array)$data->address;
        
        foreach ( $data->address as $key => $value ) {
            if (!in_array($key, $address_fields)) {
                unset($formatted_address[$key]);
            }
        }
        $data->formatted_address = !empty($formatted_address) ? implode(', ', $formatted_address) : '';
        
        return $data;
    } else {
        return false;
    }
}
