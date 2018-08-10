<?php


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
    global $wpdb,$wp_country_database;

    $rows = $wp_country_database->get_countries();
    
    $ISO2 = array();
    $countries = array();
    
    foreach ($rows as $row) {
//        print_r($row);
        $ISO2[$row->name] = $row->alpha2Code;
        $countries[$row->name] = __($row->name, 'geodirectory');

        $gps = explode(",",$row->latlng);
        $latlng[$row->name]['lat'] = isset($gps[0]) ? $gps[0] : '';
        $latlng[$row->name]['lon'] = isset($gps[1]) ? $gps[1] : '';

    }
    
    asort($countries);
    
    $out_put = '<option ' . selected('', $post_country, false) . ' value="">' . __('Select Country', 'geodirectory') . '</option>';
    foreach ($countries as $country => $name) {
        $ccode = $ISO2[$country];
        $gps = $latlng[$country];

        $out_put .= '<option ' . selected($post_country, $country, false) . ' value="' . esc_attr($country) . '" data-country_code="' . $ccode . '" data-country_lat="' . $gps['lat'] . '" data-country_lon="' . $gps['lon'] . '" >' . $name . '</option>';
    }

    echo $out_put;
}

/**
 * Returns an array of all countries by key val where the key is the country name untranslated and the val is the country name translated.
 *
 * @since 2.0.0
 *
 * @package GeoDirectory
 *
 * @global object $wpdb WordPress Database object.
 *
 * @return array $countries
 */
function geodir_get_countries()
{
    $rows = wp_country_database()->get_countries();
    $countries = array();
    foreach ($rows as $row) {
        $countries[$row->name] = __($row->name, 'geodirectory');
    }
    asort($countries);
    return $countries;
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
    $url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' . trim($lat) . ',' . trim($lng). GeoDir_Maps::google_api_key(true) ;

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
 *
 * @param string $location_array_from Place to look for location array. Default: 'session'. @depreciated
 * @param string $gd_post_type The post type.
 * @return array The location term array.
 */
function geodir_get_current_location_terms($location_array_from = null, $gd_post_type = '')
{

//    print_r(GeoDir()->location);
    global $wp,$geodirectory;
    $location_array = array();

    $location_terms = $geodirectory->location->allowed_query_variables();

    if ((isset($wp->query_vars['country']) && $wp->query_vars['country'] == 'me') || (isset($wp->query_vars['region']) && $wp->query_vars['region'] == 'me') || (isset($wp->query_vars['city']) && $wp->query_vars['city'] == 'me')) {
        return $location_array;
    }

    foreach($location_terms as $location_term){
        $location_array[$location_term] = isset($geodirectory->location->{$location_term."_slug"}) ? $geodirectory->location->{$location_term."_slug"} : '';
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

function geodir_location_name_from_slug($slug,$type){

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
    $location_link = trailingslashit( get_permalink(geodir_location_page_id()) );

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

    // we need the protocol to be "//" as a http site call to their https server fails. EDIT, it seems to require HTTPS now :/
    $url = 'https://nominatim.openstreetmap.org/reverse?format=json&lat=' . trim($lat) . '&lon=' . trim($lng) . '&zoom=16&addressdetails=1&email=' . get_option('admin_email');

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

/**
 * Replace the location variables.
 *
 * @since   1.6.16
 * @package GeoDirectory
 *
 * @global object $wp WordPress object.
 *
 * @param string $content       The content with variables.
 * @param array $location_array The array of location variables.
 * @param string $sep           The separator, Optional.
 * @param string $gd_page       The page being filtered. Optional.
 * @return string Filtered content.
 */
function geodir_replace_location_variables($content, $location_array = array(), $sep = NULL, $gd_page = '') {

    if (empty($content)) {
        return $content;
    }


    $location_replace_vars = geodir_location_replace_vars($location_array, $sep, $gd_page);

    if (!empty($location_replace_vars)) {
        foreach ($location_replace_vars as $search => $replace) {
            if (!empty($search) && strpos($content, $search) !== false) {
                $content = str_replace($search, $replace, $content);
            }
        }
    }

    return $content;
}
add_filter('geodir_replace_location_variables', 'geodir_replace_location_variables');

/**
 * Function for replace location values.
 *
 * @since 2.0.0
 *
 * @param array $location_array Optional. The array of location variables. Default array().
 * @param string $sep Optional. The separator. Default null.
 * @param string $gd_page Optional.The page being filtered. Default null.
 * @return array $location_replace_vars.
 */
function geodir_location_replace_vars($location_array = array(), $sep = NULL, $gd_page = ''){

    global $wp;
    
    $location_manager = defined('GEODIRLOCATION_VERSION') ? true : false;

    if (empty($location_array)) {
        $location_array = geodir_get_current_location_terms('query_vars');
    }
    
    $location_terms = array();
    $location_terms['gd_neighbourhood'] = !empty($wp->query_vars['neighbourhood']) ? $wp->query_vars['neighbourhood'] : '';
    $location_terms['gd_city'] = !empty($wp->query_vars['city']) ? $wp->query_vars['city'] : '';
    $location_terms['gd_region'] = !empty($wp->query_vars['region']) ? $wp->query_vars['region'] : '';
    $location_terms['gd_country'] = !empty($wp->query_vars['country']) ? $wp->query_vars['country'] : '';

    $location_names = array();
    foreach ($location_terms as $type => $location) {
        $location_name = $location;

        if (!empty($location_name)) {
            if ($location_manager) {
                $location_type = strpos($type, 'gd_') === 0 ? substr($type, 3) : $type;
                $location_name = get_actual_location_name($location_type, $location, true);
            } else {
                $location_name = preg_replace( '/-(\d+)$/', '', $location_name);
                $location_name = preg_replace( '/[_-]/', ' ', $location_name );
                $location_name = __(geodir_ucwords($location_name), 'geodirectory');
            }
        }

        $location_names[$type] = $location_name;
    }

    $location_single = '';
    foreach ($location_terms as $type => $location) {
        if (!empty($location)) {
            if (!empty($location_names[$type])) {
                $location_single = $location_names[$type];
            } else {
                if ($location_manager) {
                    $location_type = strpos($type, 'gd_') === 0 ? substr($type, 3) : $type;
                    $location_single = get_actual_location_name($location_type, $location, true);
                } else {
                    $location_name = preg_replace( '/-(\d+)$/', '', $location);
                    $location_name = preg_replace( '/[_-]/', ' ', $location_name );
                    $location_single = __(geodir_ucwords($location_name), 'geodirectory');
                }
            }
            break;
        }
    }

    $full_location = array();
    if (!empty($location_array)) {
        $location_array = array_reverse($location_array);

        foreach ($location_array as $type => $location) {
            if (!empty($location_names[$type])) {
                $location_name = $location_names[$type];
            } else {
                if ($location_manager) {
                    $location_type = strpos($type, 'gd_') === 0 ? substr($type, 3) : $type;
                    $location_name = get_actual_location_name($location_type, $location, true);
                } else {
                    $location_name = preg_replace( '/-(\d+)$/', '', $location);
                    $location_name = preg_replace( '/[_-]/', ' ', $location_name );
                    $location_name = __(geodir_ucwords($location_name), 'geodirectory');
                }
            }

            $full_location[] = $location_name;
        }

        if (!empty($full_location)) {
            $full_location = array_unique($full_location);
        }
    }
    $full_location = !empty($full_location) ? implode(', ', $full_location): '';
    
    if ( empty( $full_location ) ) {
        /**
         * Filter the text in meta description in full location is empty.
         *
         * @since 1.6.22
         * 
         * @param string $full_location Default: Empty.
         * @param array  $location_array The array of location variables.
         * @param string $gd_page       The page being filtered.
         * @param string $sep           The separator.
         */
         $full_location = apply_filters( 'geodir_meta_description_location_empty_text', '', $location_array, $gd_page, $sep );
    }
    
    if ( empty( $location_single ) ) {
        /**
         * Filter the text in meta description in single location is empty.
         *
         * @since 1.6.22
         * 
         * @param string $location_single Default: Empty.
         * @param array $location_array The array of location variables.
         * @param string $gd_page       The page being filtered.
         * @param string $sep           The separator.
         */
         $location_single = apply_filters( 'geodir_meta_description_single_location_empty_text', '', $location_array, $gd_page, $sep );
    }

    $location_replace_vars = array();
    $location_replace_vars['%%location_sep%%'] = $sep !== NULL ? $sep : '|';
    $location_replace_vars['%%location%%'] = $full_location;
    $location_replace_vars['%%in_location%%'] = $full_location != '' ? sprintf( _x('in %s','in location', 'geodirectory'), $full_location ) : '';
    $location_replace_vars['%%location_single%%'] = $location_single;
    $location_replace_vars['%%in_location_single%%'] = $location_single != '' ? sprintf( _x('in %s','in location', 'geodirectory'), $location_single ) : '';

    foreach ($location_names as $type => $name) {
        $location_type = strpos($type, 'gd_') === 0 ? substr($type, 3) : $type;

        $location_replace_vars['%%location_' . $location_type . '%%'] = $name;
        $location_replace_vars['%%in_location_' . $location_type . '%%'] = !empty($name) ? sprintf( _x('in %s','in location', 'geodirectory'), $name ) : '';
    }

    /**
     * Filter the location terms variables to search & replace.
     *
     * @since   1.6.16
     * @package GeoDirectory
     *
     * @param array $location_replace_vars The array of search & replace variables.
     * @param array $location_array The array of location variables.
     * @param string $gd_page       The page being filtered.
     * @param string $sep           The separator.
     */
    return apply_filters( 'geodir_filter_location_replace_variables', $location_replace_vars, $location_array, $gd_page, $sep );
}

/**
 * Check location slug.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @global string $table_prefix WordPress Database Table prefix.
 * @param string $slug Term slug.
 * @return string Modified term slug.
 */
function geodir_location_slug_check($slug)
{

    global $wpdb, $table_prefix;

    $slug_exists = $wpdb->get_var($wpdb->prepare("SELECT slug FROM " . $table_prefix . "terms WHERE slug=%s", array($slug)));

    if ($slug_exists) {

        $suffix = 1;
        do {
            $alt_location_name = _truncate_post_slug($slug, 200 - (strlen($suffix) + 1)) . "-$suffix";
            $location_slug_check = $wpdb->get_var($wpdb->prepare("SELECT slug FROM " . $table_prefix . "terms WHERE slug=%s", array($alt_location_name)));
            $suffix++;
        } while ($location_slug_check && $suffix < 100);

        $slug = $alt_location_name;

    }

    return $slug;

}
add_filter('geodir_location_slug_check', 'geodir_location_slug_check');

function geodir_geo_by_ip( $ip = '' ) {
	$geo = array();

	$geoplugin_data = geodir_geoplugin_data( $ip );
	if ( ! empty( $geoplugin_data ) && ! empty( $geoplugin_data['geoplugin_latitude'] ) && ! empty( $geoplugin_data['geoplugin_longitude'] ) ) {
		$geo['latitude'] = $geoplugin_data['geoplugin_latitude'];
		$geo['longitude'] = $geoplugin_data['geoplugin_longitude'];
	}

	return apply_filters( 'geodir_geo_by_ip', $geo, $ip );
}

function geodir_geoplugin_data( $ip = '' ) {
	global $wp_version;

	if ( empty( $ip ) ) {
		$ip = geodir_get_ip();
	}

	if ( empty( $ip ) ) {
		return NULL;
	}

    $geoplugin_data = array();

    // check transient cache
    $cache = get_transient( 'geodir_ip_location_'.$ip );
    if($cache === false){

        $url = 'http://www.geoplugin.net/php.gp?ip=' . $ip;
        $response = file_get_contents( $url );
        if ( ! empty( $response ) ) {
            $geoplugin_data = maybe_unserialize( $response );
        }
    }else{
        $geoplugin_data = $cache;
    }

    $geoplugin_data  = apply_filters( 'geodir_geoplugin_data', $geoplugin_data, $ip );

    set_transient( 'geodir_ip_location_'.$ip, $geoplugin_data, 24 * HOUR_IN_SECONDS ); // cache ip location for 24 hours

	return $geoplugin_data;
}