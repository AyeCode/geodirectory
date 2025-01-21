<?php


/**
 * Returns location slug using location string.
 *
 * @since 2.0.0
 * @package GeoDirectory
 * @param string $location_string The location string.
 * @return string The location slug.
 */
function geodir_create_location_slug( $location_string ) {
	global $geodirectory;

    return $geodirectory->location->create_location_slug( $location_string );
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
function geodir_get_country_dl($post_country = '', $prefix = '', $return_array = false ) {
	global $wpdb;

	$rows = geodir_wp_countries();

	$ISO2 = array();
	$countries = array();

	foreach ($rows as $row) {
		$ISO2[$row->name] = $row->alpha2Code;
		$countries[$row->name] = __($row->name, 'geodirectory');

		$gps = explode( ",", $row->latlng );
		$latlng[$row->name]['lat'] = isset($gps[0]) ? $gps[0] : '';
		$latlng[$row->name]['lon'] = isset($gps[1]) ? $gps[1] : '';
	}

	asort($countries);

	$array_out = array();

	$out_put = '<option ' . selected('', $post_country, false) . ' value="">' . __('Select Country', 'geodirectory') . '</option>';
	foreach ($countries as $country => $name) {
		$ccode = $ISO2[$country];
		$gps = $latlng[$country];
		$value = esc_attr($country);

		$out_put .= '<option ' . selected($post_country, $country, false) . ' value="' . $value . '" data-country_code="' . $ccode . '" data-country_lat="' . $gps['lat'] . '" data-country_lon="' . $gps['lon'] . '" >' . $name . '</option>';

		$array_out[$value] = array(
			'label' => $name,
			'value' => $value,
			'extra_attributes' => array(
				'data-country_code' => $ccode,
				'data-country_lat'  => $gps['lat'],
				'data-country_lon'  => $gps['lon']

			)
		);
	}

	return $return_array ? $array_out : $out_put;
}

/**
 * Returns an array of all countries from wp country database.
 *
 * @since 2.2.14
 *
 * @param array $args The parameters. DEfault empty.
 * @param bool  $split Split the country like UK when enabled. Default True.
 * @return array Countries array.
 */
function geodir_wp_countries( $args = array(), $split = true ) {
	$countries = wp_country_database()->get_countries( $args );

	// Split UK.
	if ( $split && geodir_split_uk() && ! empty( $countries ) ) {
		$_countries = array();
		$uk = array();
		$country_id = 0;

		foreach ( $countries as $key => $country ) {
			$country_id = max( $country_id, ( ! empty( $country->ID ) ? absint( $country->ID ) : ( $key + 1 ) ) );

			if ( ( ! empty( $country->name ) && $country->name == 'United Kingdom' ) || ( ! empty( $country->slug ) && $country->slug == 'united-kingdom' ) ) {
				$uk = $country;
				continue; // Skip UK.
			}

			$_countries[] = $country;
		}

		if ( ! empty( $uk ) ) {
			$uk_countries = array(
				array(
					'name' => 'England',
					'slug' => 'england',
					'capital' => 'London',
					'latlng'=> '52.3555177,-1.1743197',
					'population' => 56550000,
					'flag' => 'https://flagicons.lipis.dev/flags/4x3/gb-eng.svg'
				),
				array(
					'name' => 'Northern Ireland',
					'slug' => 'northern-ireland',
					'capital' => 'Belfast',
					'latlng'=> '54.7877149,-6.4923145',
					'population' => 1896000,
					'flag' => 'https://flagicons.lipis.dev/flags/4x3/gb-nir.svg'
				),
				array(
					'name' => 'Scotland',
					'slug' => 'scotland',
					'capital' => 'Edinburgh',
					'latlng'=> '56.4906712,-4.2026458',
					'population' => 5466000,
					'flag' => 'https://flagicons.lipis.dev/flags/4x3/gb-sct.svg'
				),
				array(
					'name' => 'Wales',
					'slug' => 'wales',
					'capital' => 'Cardiff',
					'latlng'=> '52.1306607,-3.7837117',
					'population' => 3170000,
					'flag' => 'https://flagicons.lipis.dev/flags/4x3/gb-wls.svg'
				)
			);

			foreach ( $uk_countries as $country ) {
				$country_id++;

				$uk_country = (array) $uk;
				$uk_country['ID'] = $country_id;
				$uk_country['name'] = $country['name'];
				$uk_country['slug'] = $country['slug'];
				$uk_country['capital'] = $country['capital'];
				$uk_country['latlng'] = $country['latlng'];
				$uk_country['population'] = $country['population'];
				$uk_country['flag'] = $country['flag'];

				$_countries[] = (object) $uk_country;
			}
		}

		$countries = $_countries;
	}

	/**
	 * Filters an array of all countries from wp country database.
	 *
	 * @since 2.2.14
	 *
	 * @param array $countries Countries array.
	 * @param bool $split Split the country like UK when enabled.
	 */
	return apply_filters( 'geodir_wp_countries', $countries, $split );
}

/**
 * Returns an array of all countries by key val where the key is the country name untranslated and the val is the country name translated.
 *
 * @since 2.0.0
 *
 * @package GeoDirectory
 *
 * @return array $countries Countries array.
 */
function geodir_get_countries() {
	$rows = geodir_wp_countries();

	$countries = array();
	foreach ( $rows as $row ) {
		$countries[ $row->name ] = __( $row->name, 'geodirectory' );
	}

	asort( $countries );

	/**
	 * Filters an array of all countries by key => value.
	 *
	 * @since 2.2.14
	 *
	 * @param array $countries Countries array with key => value pair.
	 * @param array $rows Countries array.
	 */
	return apply_filters( 'geodir_get_countries', $countries, $rows );
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
    $url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' . trim($lat) . ',' . trim($lng). GeoDir_Maps::google_geocode_api_key(true) ;

	// maybe add language.
	$lang = GeoDir_Maps::map_language();
	if ( $lang && 'en' !== $lang ) {
		$url .= '&language=' . esc_attr( $lang );
	}

    $response = wp_remote_get($url);
    if ( is_wp_error( $response ) ) {
        return false;
    }

    $result = json_decode( wp_remote_retrieve_body( $response ) );

    if(isset($result->results[0]->address_components)){
        return $result->results[0]->address_components;
    }else{
        return false;
    }
}

/**
 * Returns current location terms.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wp WordPress object.
 *
 * @param string $location_array_from Place to look for location array. Default: 'session'. @deprecated
 * @param string $gd_post_type The post type.
 * @return array The location term array.
 */
function geodir_get_current_location_terms( $location_array_from = null, $gd_post_type = '' ) {
	global $wp, $geodirectory;

	$location_array = array();

	if ( ( isset( $wp->query_vars['country'] ) && $wp->query_vars['country'] == 'me' ) || ( isset($wp->query_vars['region'] ) && $wp->query_vars['region'] == 'me' ) || ( isset( $wp->query_vars['city'] ) && $wp->query_vars['city'] == 'me' ) ) {
		return $location_array;
	}

	if ( ! ( ! empty( $geodirectory ) && ! empty( $geodirectory->location ) ) ) {
		return $location_array;
	}

	$location_terms = $geodirectory->location->allowed_query_variables();

	foreach ( $location_terms as $location_term ) {
		$location_array[ $location_term ] = isset( $geodirectory->location->{$location_term . "_slug"} ) ? $geodirectory->location->{$location_term . "_slug"} : '';
	}

	// Set empty locations terms when outside default location active.
	if ( geodir_core_multi_city() && ! geodir_is_page( 'single' ) && ! geodir_is_page( 'search' ) ) {
		$location_array = array();
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

        $location_terms = apply_filters('geodir_location_link_location_terms',$location_terms);

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


    $response = wp_remote_get($url);
    if ( is_wp_error( $response ) ) {
        return false;
    }

    $result = json_decode( wp_remote_retrieve_body( $response ) );

    if(!empty($result->address)){
        $address_fields = array('public_building', 'house', 'house_number', 'bakery', 'footway', 'street', 'road', 'village', 'attraction', 'pedestrian', 'neighbourhood', 'suburb');
        $formatted_address = (array)$result->address;

        foreach ( $result->address as $key => $value ) {
            if (!in_array($key, $address_fields)) {
                unset($formatted_address[$key]);
            }
        }
        $result->formatted_address = !empty($formatted_address) ? implode(', ', $formatted_address) : '';

        return $result;
    }else{
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
function geodir_location_replace_vars( $location_array = array(), $sep = NULL, $gd_page = '' ) {
    global $wp, $gd_post;

    // Private address
    $check_address = ( $gd_page == 'single' || geodir_is_page( 'single' ) ) && ! empty( $gd_post ) && GeoDir_Post_types::supports( $gd_post->post_type, 'private_address' ) ? true : false;

    if ( empty( $location_array ) ) {
        $location_array = geodir_get_current_location_terms( 'query_vars' );
    }

	if ( class_exists( 'GeoDir_Location_City' ) && geodir_get_option( 'lm_url_filter_archives_on_single', 'city' ) != 'city' && ! ( ! empty( $location_array ) && ! empty( $location_array['country'] ) && ! empty( $location_array['region'] ) && ! empty( $location_array['city'] ) ) && ! empty( $gd_post ) && ( $gd_page == 'single' || geodir_is_page( 'single' ) ) && GeoDir_Post_types::supports( $gd_post->post_type, 'location' ) && ! empty( $gd_post->country ) && ! empty( $gd_post->region ) && ! empty( $gd_post->city ) ) {
		if ( ! empty( $gd_post->neighbourhood ) && GeoDir_Location_Neighbourhood::is_active() ) {
			$location = GeoDir_Location_Neighbourhood::get_info_by_slug( $gd_post->neighbourhood );
			$neighbourhood = $gd_post->neighbourhood;

			if ( empty( $location ) ) {
				$location = GeoDir_Location_City::get_info_by_name( $gd_post->city, $gd_post->country, $gd_post->region );
			}
		} else {
			$location = GeoDir_Location_City::get_info_by_name( $gd_post->city, $gd_post->country, $gd_post->region );
			$neighbourhood = '';
		}

		if ( ! empty( $location ) ) {
			$location_array['country'] = isset( $location->country_slug ) ? $location->country_slug : '';
			$location_array['region'] = isset( $location->region_slug ) ? $location->region_slug : '';
			$location_array['city'] = isset( $location->city_slug ) ? $location->city_slug : '';
			$location_array['neighbourhood'] = $neighbourhood;
		}
	}

	$location_terms = array();
	$location_terms['gd_neighbourhood'] = ! empty( $wp->query_vars['neighbourhood'] ) && is_scalar( $wp->query_vars['neighbourhood'] ) ? $wp->query_vars['neighbourhood'] : '';
	$location_terms['gd_city'] = ! empty( $wp->query_vars['city'] ) && is_scalar( $wp->query_vars['city'] ) ? $wp->query_vars['city'] : '';
	$location_terms['gd_region'] = ! empty( $wp->query_vars['region'] ) && is_scalar( $wp->query_vars['region'] ) ? $wp->query_vars['region'] : '';
	$location_terms['gd_country'] = ! empty( $wp->query_vars['country'] ) && is_scalar( $wp->query_vars['country'] ) ? $wp->query_vars['country'] : '';

    // On single page set neighbourhood from post.
    if ( ! empty( $location_terms['gd_city'] ) && empty( $location_terms['gd_neighbourhood'] ) && ( $gd_page == 'single' || geodir_is_page( 'single' ) ) && ! empty( $gd_post->neighbourhood ) ) {
        $location_terms['gd_neighbourhood'] = $gd_post->neighbourhood;
    }

    $location_single = '';
    $location_names = array();
    foreach ( $location_terms as $type => $location ) {
        $location_type = strpos( $type, 'gd_' ) === 0 ? substr( $type, 3 ) : $type;
        if ( $location == '' && isset( $location_array[ $location_type ] ) ) {
            $location = $location_array[ $location_type ];
        };

        if ( ! empty( $location ) ) {
            if ( function_exists( 'get_actual_location_name' ) ) {
                $location = get_actual_location_name( $location_type, $location, true );
            } else {
                $location = preg_replace( '/-(\d+)$/', '', $location);
                $location = preg_replace( '/[_-]/', ' ', $location );
                $location = __( geodir_ucwords( $location ), 'geodirectory' );
            }
        }

		if ( $check_address && ! empty( $location ) ) {
			$location = geodir_post_address( $location, $location_type, $gd_post, '' );
		}

        if ( empty( $location_single ) ) {
            $location_single = $location;
        }

        $location_names[ $type ] = $location;
    }

    $full_location = array();
    if ( ! empty( $location_array ) ) {
        $location_array = array_reverse( $location_array );
        foreach ( $location_array as $type => $location ) {
            if ( ! empty( $location_names[ $type ] ) ) {
                $location_name = $location_names[ $type ];
            } else {
                $location_type = strpos($type, 'gd_') === 0 ? substr($type, 3) : $type;

                if ( function_exists( 'get_actual_location_name' ) ) {
                    $location_name = get_actual_location_name( $location_type, $location, true );
                } else {
                    $location_name = preg_replace( '/-(\d+)$/', '', $location );
                    $location_name = preg_replace( '/[_-]/', ' ', $location_name );
                    $location_name = __( geodir_ucwords( $location_name ), 'geodirectory' );
                }
            }

            $location_name = $location_name ? trim( $location_name ) : '';

			// Private address
			if ( $check_address && ! empty( $location_name ) ) {
				$location_name = geodir_post_address( $location_name, $location_type, $gd_post, '' );
			}

			if ( $location_name != '' ) {
				$full_location[] = $location_name;
			}
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
        $location_replace_vars['%%_' . $location_type . '%%'] = $name;
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


/**
 * Get location info via from IP.
 *
 * @since 2.0.0.60 Changed to new site for IP information.
 * @param string $ip
 *
 * @return mixed|void
 */
function geodir_geo_by_ip( $ip = '' ) {
	$geo = array();

    $data = geodir_ip_api_data( $ip );
    if ( ! empty( $data ) && ! empty( $data['lat'] ) && ! empty( $data['lon'] ) ) {
        $geo['latitude'] = $data['lat'];
        $geo['longitude'] = $data['lon'];
    }

	return apply_filters( 'geodir_geo_by_ip', $geo, $ip );
}


/**
 * Get location data from ip via geoplugin.net
 *
 * @deprecated 2.0.0.60
 * @param string $ip
 *
 * @return array|mixed|null|void
 */
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
        if(ini_get('allow_url_fopen')){
            $response = file_get_contents( $url );
            if ( ! empty( $response ) ) {
                $geoplugin_data = maybe_unserialize( $response );
            }
        }
    }else{
        $geoplugin_data = $cache;
    }

    $geoplugin_data  = apply_filters( 'geodir_geoplugin_data', $geoplugin_data, $ip );

    set_transient( 'geodir_ip_location_'.$ip, $geoplugin_data, 24 * HOUR_IN_SECONDS ); // cache ip location for 24 hours

	return $geoplugin_data;
}

/**
 * Get location data from ip via geoplugin.net.
 *
 * @param string $ip
 * @since 2.0.0.60
 * @return array|bool|mixed|null|object|void
 */
function geodir_ip_api_data( $ip = '' ) {
    global $wp_version;

    if ( empty( $ip ) ) {
        $ip = geodir_get_ip();
    }

    if ( empty( $ip ) ) {
        return NULL;
    }

    $data = array();

    // check transient cache
    $cache = get_transient( 'geodir_ip_location_' . $ip );

    /**
     * Filters the IP 2 location before the request takes place.
     *
     * @since 2.2.23
     *
     * @param null|array $pre_data Location data.
     * @param string     $ip IP.
     * @param null|array $cache Cached location data.
     */
    $pre_data = apply_filters( 'geodir_ip_api_pre_data', null, $ip, $cache );

    if ( $pre_data !== null && is_array( $pre_data ) ) {
        $cache = $pre_data;
    }

    if ( $cache === false ) {
        $url = 'http://ip-api.com/json/' . $ip;
        $response = wp_remote_get($url);

        if ( is_array( $response ) && wp_remote_retrieve_response_code( $response ) == '200' ) {
            $data = json_decode( wp_remote_retrieve_body( $response ), true );

            if ( ! empty( $data ) && ! empty( $data['lat'] ) && ! empty( $data['lon'] ) ) {
                $data['lat'] = geodir_sanitize_float( $data['lat'] );
                $data['lon'] = geodir_sanitize_float( $data['lon'] );
            }
        }
    } else {
        $data = $cache;
    }

    $data  = apply_filters( 'geodir_ip_api_data', $data, $ip );

    set_transient( 'geodir_ip_location_' . $ip, $data, 24 * HOUR_IN_SECONDS ); // cache ip location for 24 hours

    return $data;
}

/**
 * Get timezone data from via timezone api service.
 *
 * @since 2.0.0.66
 *
 * @param string $latitude Latitude
 * @param string $longitude Longitude
 * @param int $timestamp Timestamp
 * @return array|WP_Error
 */
function geodir_get_timezone_by_lat_lon( $latitude, $longitude, $timestamp = 0 ) {
	global $wp_version;

	$data = array();
	$error = '';

	if ( ! empty( $latitude ) && ! empty( $longitude ) ) {
		$api_url = 'https://maps.googleapis.com/maps/api/timezone/json';
		$api_url .= '?key=' . GeoDir_Maps::google_geocode_api_key();
		$api_url .= '&timestamp=' . ( absint( $timestamp ) > 0 ? absint( $timestamp ) : current_time( 'timestamp' ) );
		$api_url .= '&location=' . $latitude . ',' . $longitude;

		$args = array(
			'timeout'     => 5,
			'redirection' => 5,
			'httpversion' => '1.0',
			'user-agent'  => 'WordPress/' . $wp_version . '; ' . home_url(),
			'blocking'    => true,
			'decompress'  => true,
			'sslverify'   => false,
		);

		$response = wp_remote_get( $api_url , $args );

		if ( ! is_wp_error( $response ) ) {
			$body = (array) json_decode( wp_remote_retrieve_body( $response ) );

			if ( ! empty( $body ) && $body['status'] == 'OK' ) {
				if ( isset( $body['timeZoneId'] ) && $body['timeZoneId'] == 'Asia/Calcutta' ) {
					$body['timeZoneId'] = 'Asia/Kolkata';
				}
				$data = $body;
			} elseif ( ! empty( $body ) && ! empty( $body['errorMessage'] ) ) {
				$error = __( $body['errorMessage'], 'geodirectory' );
			}
		} else {
			if ( current_user_can( 'manage_options' ) ) {
				$error = __( $response->get_error_message(), 'geodirectory' );
			}
		}
	}

	if ( empty( $data ) ) {
		if ( empty( $error ) ) {
			$error = __( 'There is an error in timezone data request.', 'geodirectory' );
		}

		$data = new WP_Error( 'gd-timezone-api', wp_sprintf( __( 'Google Timezone API: %s' ), $error ) );
	}

	return apply_filters( 'geodir_get_timezone_by_lat_lon', $data, $latitude, $longitude, $timestamp );
}

/**
 * Get the GPS from a post address.
 *
 * @param $address
 *
 * @return WP_Error|array|bool
 */
function geodir_get_gps_from_address( $address = array(), $wp_error = false ) {
	$api = GeoDir_Maps::active_map();

	$api = apply_filters( 'geodir_post_gps_from_address_api', $api );

	if ( $api == 'google' || $api == 'auto' ) {
		$_gps = geodir_google_get_gps_from_address( $address, $wp_error );
	} elseif ( $api == 'osm' ) {
		$_gps = geodir_osm_get_gps_from_address( $address, $wp_error );
	} else {
		$_gps = apply_filters( 'geodir_gps_from_address_custom_api_gps', array(), $api );
	}

	$gps = array();

	if ( is_array( $_gps ) && ! empty( $_gps['latitude'] ) && ! empty( $_gps['longitude'] ) ) {
		$gps['latitude'] = $_gps['latitude'];
		$gps['longitude'] = $_gps['longitude'];
	} else {
		if ( $wp_error ) {
			if ( is_wp_error( $_gps ) ) {
				return $_gps;
			} else {
				return new WP_Error( 'geodir-gps-from-address', esc_attr__( 'Failed to retrieve GPS data from a address using API.', 'geodirectory' ) );
			}
		} else {
			return NULL;
		}
	}

	return apply_filters( 'geodir_get_gps_from_address', $gps, $address, $api );
}

/**
 * Get GPS info for the address using Google Geocode API.
 *
 * @since 2.0.0.66
 *
 * @param array|string $address Array of address element or full address.
 * @param bool $wp_error Optional. Whether to return a WP_Error on failure. Default false.
 * @return bool|WP_Error GPS data or WP_Error on failure.
 */
function geodir_google_get_gps_from_address( $address, $wp_error = false ) {
	global $wp_version;

	if ( empty( $address ) ) {
		if ( $wp_error ) {
			return new WP_Error( 'geodir-gps-from-address', __( 'Address must be non-empty.', 'geodirectory' ) );
		} else {
			return false;
		}
	}

	if ( is_array( $address ) ) {
		$address = wp_parse_args( $address, array(
			'street' => '',
			'city' => '',
			'region' => '',
			'country' => '',
			'zip' => '',
		) );

		$_address = array();
		if ( trim( $address['street'] ) != '' ) {
			$_address[] = trim( $address['street'] );
		}
		if ( trim( $address['city'] ) != '' ) {
			$_address[] = trim( $address['city'] );
		}
		if ( trim( $address['region'] ) != '' ) {
			$_address[] = trim( $address['region'] );
		}
		if ( trim( $address['country'] ) != '' ) {
			$_address[] = trim( $address['country'] );
		}
		if ( trim( $address['zip'] ) != '' ) {
			$_address[] = trim( $address['zip'] );
		}

		// We must have at least 4 address items.
		if ( count( $_address ) < 4 ) {
			if ( $wp_error ) {
				return new WP_Error( 'geodir-gps-from-address', __( 'Not enough location info for address.', 'geodirectory' ) );
			} else {
				return false;
			}
		}

		$search_address = implode( ', ', $_address );
	} else {
		if ( trim( $address ) == '' ) {
			if ( $wp_error ) {
				return new WP_Error( 'geodir-gps-from-address', __( 'Not enough location info for address.', 'geodirectory' ) );
			} else {
				return false;
			}
		}

		$search_address = trim( $address );
	}

	$request_url = 'https://maps.googleapis.com/maps/api/geocode/json';
	$request_url .= '?address=' . $search_address;

	// Api key if we have it, it helps with limits.
	$google_api_key = GeoDir_Maps::google_geocode_api_key();
	if ( $google_api_key ) {
		$request_url .= '&key=' . $google_api_key;
	}

	// maybe add language.
	$lang = GeoDir_Maps::map_language();
	if ( $lang && 'en' !== $lang ) {
		$request_url .= '&language=' . esc_attr( $lang );
	}

	$request_url = apply_filters( 'geodir_google_gps_from_address_request_url', $request_url, $address );

	$args = array(
		'timeout'     => 5,
		'redirection' => 5,
		'httpversion' => '1.0',
		'user-agent'  => 'WordPress/' . $wp_version . '; ' . home_url(),
		'blocking'    => true,
		'decompress'  => true,
		'sslverify'   => false,
	);
	$response = wp_remote_get( $request_url , $args );

	// Check for errors
	if ( is_wp_error( $response ) ) {
		if ( $wp_error ) {
			return new WP_Error( 'geodir-gps-from-address', __( 'Failed to reach Google geocode server.', 'geodirectory' ) );
		} else {
			return false;
		}
	}

	$body = wp_remote_retrieve_body( $response );
	$data = json_decode( $body, true );

	$gps = array();
	if ( isset( $data['status'] ) && $data['status'] == 'OK' ) {
		if ( isset( $data['results'][0]['geometry']['location']['lat'] ) && $data['results'][0]['geometry']['location']['lat'] ) {
			$gps['latitude'] = $data['results'][0]['geometry']['location']['lat'];
			$gps['longitude'] = $data['results'][0]['geometry']['location']['lng'];
		} else {
			if ( $wp_error ) {
				$gps = new WP_Error( 'geodir-gps-from-address', wp_sprintf( __( 'Could not retrieve GPS info from Google geocode server for the address %s', 'geodirectory' ), $search_address ) );
			} else {
				$gps = false;
			}
		}
	} else {
		if ( isset( $data['status'] ) ) {
			$error = wp_sprintf( __( 'Google geocode failed: %s', 'geodirectory' ),  $data['status'] );
		} else {
			$error = __( 'Failed to reach Google geocode server.', 'geodirectory' );
		}

		if ( $wp_error ) {
			$gps = new WP_Error( 'geodir-gps-from-address', $error );
		} else {
			$gps = false;
		}
	}

	return $gps;
}

/**
 * Get GPS info for the address using OpenStreetMap Nominatim API.
 *
 * @since 2.0.0.66
 *
 * @param array|string $address Array of address element or full address.
 * @param bool $wp_error Optional. Whether to return a WP_Error on failure. Default false.
 * @return bool|WP_Error GPS data or WP_Error on failure.
 */
function geodir_osm_get_gps_from_address( $address, $wp_error = false ) {
	global $wp_version;

	if ( empty( $address ) ) {
		if ( $wp_error ) {
			return new WP_Error( 'geodir-gps-from-address', __( 'Address must be non-empty.', 'geodirectory' ) );
		} else {
			return false;
		}
	}

	$extra_params = '';
	if ( is_array( $address ) ) {
		$address = wp_parse_args( $address, array(
			'street' => '',
			'city' => '',
			'region' => '',
			'country' => '',
			'zip' => '',
			'country_code' => '',
		) );

		$_address = array();
		if ( trim( $address['street'] ) != '' ) {
			$_address[] = trim( $address['street'] );
		}
		if ( trim( $address['city'] ) != '' ) {
			$_address[] = trim( $address['city'] );
		}
		if ( trim( $address['region'] ) != '' ) {
			$_address[] = trim( $address['region'] );
		}
		if ( trim( $address['zip'] ) != '' ) {
			$_address[] = trim( $address['zip'] );
		}
		if ( trim( $address['country'] ) != '' ) {
			$_address[] = trim( $address['country'] );
		}

		// We must have at least 2 address items.
		if ( count( $_address ) < 2 ) {
			if ( $wp_error ) {
				return new WP_Error( 'geodir-gps-from-address', __( 'Not enough location info for address.', 'geodirectory' ) );
			} else {
				return false;
			}
		}

		$search_address = implode( ', ', $_address );

		// Search within specific country code(s).
		if ( ! empty( $address['country_code'] ) ) {
			$extra_params .= '&countrycodes=' . ( is_array( $address['country_code'] ) ? implode( ',', $address['country_code'] ) : $address['country_code'] );
		}
	} else {
		if ( trim( $address ) == '' ) {
			if ( $wp_error ) {
				return new WP_Error( 'geodir-gps-from-address', __( 'Not enough location info for address.', 'geodirectory' ) );
			} else {
				return false;
			}
		}

		$search_address = trim( $address );
	}

	$request_url = 'https://nominatim.openstreetmap.org/search?format=json&addressdetails=1&limit=1';
	$request_url .= '&q=' . $search_address;
	$request_url .= $extra_params;

	// If making large numbers of request please include an appropriate email address to identify requests.
	$request_url .= '&email=' . get_option( 'admin_email' );

	$request_url = apply_filters( 'geodir_osm_gps_from_address_request_url', $request_url, $address );

	$args = array(
		'timeout'     => 5,
		'redirection' => 5,
		'httpversion' => '1.0',
		'user-agent'  => 'WordPress/' . $wp_version . '; ' . home_url(),
		'blocking'    => true,
		'decompress'  => true,
		'sslverify'   => false,
	);
	$response = wp_remote_get( $request_url , $args );

	// Check for errors
	if ( is_wp_error( $response ) ) {
		if ( $wp_error ) {
			return new WP_Error( 'geodir-gps-from-address', __( 'Failed to reach OpenStreetMap Nominatim server.', 'geodirectory' ) );
		} else {
			return false;
		}
	}

	$body = wp_remote_retrieve_body( $response );
	$data = json_decode( $body, true );

	$gps = array();
	if ( ! empty( $data ) && is_array( $data ) ) {
		if ( ! empty( $data[0]['lat'] ) && ! empty( $data[0]['lon'] ) ) {
			$details = $data[0];

			$gps['latitude'] = $details['lat'];
			$gps['longitude'] = $details['lon'];
		} else {
			if ( $wp_error ) {
				$gps = new WP_Error( 'geodir-gps-from-address', wp_sprintf( __( 'Could not retrieve GPS info from OpenStreetMap server for the address %s', 'geodirectory' ), $search_address ) );
			} else {
				$gps = false;
			}
		}
	} else {
		if ( is_array( $address ) && ! empty( $address['city'] ) && ! empty( $address['zip'] ) ) {
			unset( $address['city'] );

			return geodir_osm_get_gps_from_address( $address, $wp_error );
		}

		if ( $wp_error ) {
			$gps = new WP_Error( 'geodir-gps-from-address', wp_sprintf( __( 'Could not retrieve GPS info from OpenStreetMap server for the address %s', 'geodirectory' ), $search_address ) );
		} else {
			$gps = false;
		}
	}

	return $gps;
}

/**
 * Check multi city active or not.
 *
 * @since 2.1.0.7
 *
 * @param array|string $address Array of address element or full address.
 * @param bool $wp_error Optional. Whether to return a WP_Error on failure. Default false.
 * @return bool|WP_Error GPS data or WP_Error on failure.
 */
function geodir_core_multi_city() {
	return ( ! defined( 'GEODIRLOCATION_VERSION' ) && geodir_get_option( 'multi_city' ) );
}

/**
 * Render the post address field value.
 *
 * @since 2.1.1.9
 *
 * @param string $value Field value.
 * @param string $key Field key.
 * @param int|object $post The post.
 * @param mixed $default Whether to use default value.
 * @return string Filtered field value.
 */
function geodir_post_address( $value, $key, $post, $default = NULL ) {
	if ( geodir_is_block_demo() ) {
		return $value;
	}

	if ( ! empty( $post ) && ! geodir_user_can( 'see_private_address', array( 'post' => $post ) ) ) {
		switch( $key ) {
			case 'address':
			case 'street':
			case 'street2':
			case 'city':
			case 'region':
			case 'country':
			case 'neighbourhood':
			case 'zip':
			case 'latitude':
			case 'longitude':
			case 'post_badge_street':          /* GD > Post Badge (address) widget/block */
				if ( $default !== NULL ) {
					$output = $default;
				} else {
					$output = __( 'Private Address', 'geodirectory' );
				}
				break;
			case 'gd_post_address':            /* GD > Post Address widget/block */
				if ( $default !== NULL ) {
					$output = $default;
				} else {
					$output = $value;
				}
				break;
			case 'gd_post_directions':         /* GD > Directions widget/block */
			case 'gd_post_distance':           /* GD > Distance To Post widget/block */
			case 'map_directions':             /* GD > Post Meta (map_directions) */
			case 'gd_location_description':    /* GD > Location Description widget/block */
			case 'gd_location_meta':           /* GD > Location Meta widget/block */
				if ( $default !== NULL ) {
					$output = $default;
				} else {
					$output = '';
				}
				break;
			default:
				$output = $value;
				break;
		}
	} else {
		$output = $value;
	}

	/**
	 * Filters post address field value.
	 *
	 * @since 2.1.1.9
	 *
	 * @param string $output Field filtered value.
	 * @param string $value Field original value.
	 * @param string $key Field key.
	 * @param int|object $post The post.
	 * @param mixed $default Whether to use default value.
	 */
	return apply_filters( 'geodir_render_post_address', $output, $value, $key, $post, $default );
}

/**
 * Check split of UK into England, Northern Ireland, Scotland & Wales.
 *
 * @since 2.2.14
 *
 * @return bool True to split otherwise False.
 */
function geodir_split_uk() {
	$split_uk = geodir_get_option( 'split_uk' ) ? true : false;

	/**
	 * Filter the split of UK option.
	 *
	 * @since 2.2.14
	 *
	 * @param bool $split_uk True to split otherwise False.
	 */
	return apply_filters( 'geodir_split_uk', $split_uk );
}
