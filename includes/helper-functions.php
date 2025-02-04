<?php
/**
 * Helper functions, this file contains functions made to make a developer's life easier.
 *
 * @since 1.4.6
 * @package GeoDirectory
 */

/**
 * Get the Database table string from Custom Post Type.
 *
 * @param $post_type
 *
 * @return bool|string
 */
function geodir_db_cpt_table($post_type){
	if(!empty($post_type)){
		global $plugin_prefix;
		return $plugin_prefix . $post_type . '_detail';
	}

	return false;
}

/**
 * Get the page ID of the add listing page.
 *
 * @package Geodirectory
 * @since 1.4.6
 * @since 2.0.0.97 Added $post_type parameter.
 *
 * @param string $post_type The post type.
 * @return int|null Return the page ID if present or null if not.
 */
function geodir_add_listing_page_id( $post_type = '' ) {
    $gd_page_id = geodir_get_page_id( 'add', $post_type );

	return apply_filters( 'geodir_add_listing_page_id', $gd_page_id, $post_type );
}

/**
 * Get the page ID of the add listing preview page.
 *
 * @package Geodirectory
 * @since 1.4.6
 * @return int|null Return the page ID if present or null if not.
 * @todo we need to remove this function and all references to it.
 */
function geodir_preview_page_id(){
    $gd_page_id = geodir_get_page_id( 'preview' );

	return apply_filters( 'geodir_preview_page_id', $gd_page_id );
}

/**
 * Get the page ID of the add listing success page.
 *
 * @package Geodirectory
 * @since 1.4.6
 * @return int|null Return the page ID if present or null if not.
 * @todo we need to remove this function and all references to it.
 */
function geodir_success_page_id(){
    $gd_page_id = geodir_get_page_id( 'success' );

	return apply_filters( 'geodir_success_page_id', $gd_page_id );
}

/**
 * Get the page ID of the add location page.
 *
 * @package Geodirectory
 * @since 1.4.6
 * @return int|null Return the page ID if present or null if not.
 */
function geodir_location_page_id(){
    $gd_page_id = geodir_get_page_id( 'location' );

	return apply_filters( 'geodir_location_page_id', $gd_page_id );
}

/**
 * Get the page ID of the info page.
 *
 * @package Geodirectory
 * @since 1.5.3
 * @return int|null Return the page ID if present or null if not.
 * @deprecated No longer needed since version 2.0.0
 */
function geodir_info_page_id(){
    $gd_page_id = geodir_get_page_id( 'info' );

	return apply_filters( 'geodir_info_page_id', $gd_page_id );
}

/**
 * Get the page ID of the login page.
 *
 * @package Geodirectory
 * @since 1.5.3
 * @return int|null Return the page ID if present or null if not.
 * @deprecated No longer needed since version 2.0.0
 */
function geodir_login_page_id(){
    $gd_page_id = geodir_get_page_id( 'login' );

	return apply_filters( 'geodir_login_page_id', $gd_page_id );
}

/**
 * Get the page ID of the GD archive page.
 *
 * @package Geodirectory
 * @since 2.0.0
 * @param string $post_type Post type.
 * @return int|null Return the page ID if present or null if not.
 */
function geodir_archive_page_id($post_type = ''){
	$gd_page_id = geodir_get_page_id( 'archive', $post_type );

	return apply_filters( 'geodir_archive_page_id', $gd_page_id, $post_type );
}

/**
 * Get the page ID of the GD archive item page.
 *
 * @package Geodirectory
 * @since 2.0.0
 * @param string $post_type Post type.
 * @return int|null Return the page ID if present or null if not.
 */
function geodir_archive_item_page_id($post_type = ''){
	$gd_page_id = geodir_get_page_id( 'archive_item', $post_type );

	return apply_filters( 'geodir_archive_item_page_id', $gd_page_id, $post_type );
}

/**
 * Get the page ID of the GD search page.
 *
 * @package Geodirectory
 * @since 2.0.0
 * @return int|null Return the page ID if present or null if not.
 */
function geodir_search_page_id(){
	$gd_page_id = geodir_get_page_id( 'search' );

	return apply_filters( 'geodir_search_page_id', $gd_page_id );
}

/**
 * Get the page ID of the GD details page.
 *
 * @package Geodirectory
 * @since 2.0.0
 * @param string $post_type Post type.
 * @return int|null Return the page ID if present or null if not.
 */
function geodir_details_page_id($post_type = ''){
	$gd_page_id = geodir_get_page_id( 'details', $post_type );

	return apply_filters( 'geodir_details_page_id', $gd_page_id, $post_type );
}

/**
 * Get the page ID of the GD search page.
 *
 * @package Geodirectory
 * @since 2.0.0
 * @return int|null Return the page ID if present or null if not.
 */
function geodir_terms_and_conditions_page_id(){
	$gd_page_id = geodir_get_page_id( 'terms_conditions' );

	return apply_filters( 'geodir_terms_and_conditions_page_id', $gd_page_id );
}


/**
 * Get the page ID of the login page.
 *
 * @package Geodirectory
 * @since 1.5.3
 * @return int|null Return the page ID if present or null if not.
 *                  'false' to prevent adding redirect_to to login url.
 */
function geodir_login_url( $redirect = '' ) {
	if ( empty( $redirect ) && $redirect !== false ) {
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			$redirect = wp_get_referer();
		} else {
			$redirect = geodir_curPageURL();
		}
	}
	return wp_login_url( $redirect );
}

/**
 * Returns info page url
 *
 * @package Geodirectory
 * @since 1.5.4
 * @since 1.5.16 Added WPML lang code to url.
 * @return string Info page url.
 */
function geodir_info_url($args=array()){
    $gd_page_id = geodir_info_page_id( 'info' );

    if (function_exists('geodir_location_geo_home_link')) {
        remove_filter('home_url', 'geodir_location_geo_home_link', 100000);
    }

	$home_url = apply_filters( 'geodir_info_page_home_url', home_url() );

    if (function_exists('geodir_location_geo_home_link')) {
        add_filter('home_url', 'geodir_location_geo_home_link', 100000, 2);
    }

    if($gd_page_id){
        $post = get_post($gd_page_id);
        $slug = $post->post_name;
        //$login_url = get_permalink($gd_page_id );// get_permalink can only be user after theme-Setup hook, any earlier and it errors
        $info_url = trailingslashit($home_url)."$slug/";
    }else{
        $info_url = trailingslashit($home_url);
    }

    if($args){
        $info_url = add_query_arg($args,$info_url );
    }

    return $info_url;
}

/**
 * Converts string to title case.
 *
 * This function converts string to title case. Ex: hello world -> Hello World.
 * When mbstring php extension available this function supports all unicode characters.
 *
 * @package Geodirectory
 * @since 1.5.4
 * @param string $string String to convert.
 * @param string $charset Character set to use for conversion.
 * @return string Returns converted string.
 */
function geodir_ucwords($string, $charset='UTF-8') {
    if (function_exists('mb_convert_case')) {
        return mb_convert_case($string, MB_CASE_TITLE, $charset);
    } else {
        return ucwords($string);
    }
}

/**
 * Converts string to lower case.
 *
 * This function converts string to lower case. Ex: HelLO WorLd -> hello world.
 * When mbstring php extension available this function supports all unicode characters.
 *
 * @package Geodirectory
 * @since 1.5.4
 * @param string $string String to convert.
 * @param string $charset Character set to use for conversion.
 * @return string Returns converted string.
 */
function geodir_strtolower($string, $charset='UTF-8') {
    if (function_exists('mb_convert_case')) {
        return mb_convert_case($string, MB_CASE_LOWER, $charset);
    } else {
        return strtolower($string);
    }
}

/**
 * Converts string to upper case.
 *
 * This function converts string to upper case. Ex: HelLO WorLd -> HELLO WORLD.
 * When mbstring php extension available this function supports all unicode characters.
 *
 * @package Geodirectory
 * @since 1.5.4
 * @param string $string String to convert.
 * @param string $charset Character set to use for conversion.
 * @return string Returns converted string.
 */
function geodir_strtoupper($string, $charset='UTF-8') {
    if (function_exists('mb_convert_case')) {
        return mb_convert_case($string, MB_CASE_UPPER, $charset);
    } else {
        return strtoupper($string);
    }
}

/**
 * Returns parsed url and title.
 *
 * This function converts string to url and title if there is "|" separator used in url.
 * Ex: "http://wpgeodirectory.com|GeoDirectory" will return array( url => http://wpgeodirectory.com, label => GeoDirectory ).
 *
 * @package Geodirectory
 * @since 1.5.7
 * @param string $url The website url.
 * @param bool $formatted True if returns formatted url. False if not. Default true.
 * @return array Parsed url and title.
 */
function geodir_parse_custom_field_url( $url, $formatted = true ) {
	if ( $url == '' || ! is_string( $url ) ) {
		return NULL;
	}

	$original_url = $url;

	$url = stripcslashes( $url );
	$parts = explode( '|', $url, 2 );

	$url = trim( $parts[0] );
	if ( $formatted && $url != '' ) {
		$url = str_replace( ' ', '%20', $url );
		$url = preg_replace( '|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\[\]\\x80-\\xff]|i', '', $url );

		if ( 0 !== stripos( $url, 'mailto:' ) ) {
			$strip = array( '%0d', '%0a', '%0D', '%0A' );
			$url = _deep_replace( $strip, $url );
		}

		$url = str_replace( ';//', '://', $url );

		if ( strpos( $url, ':' ) === false && ! in_array( $url[0], array('/', '#', '?' ) ) && ! preg_match( '/^[a-z0-9-]+?\.php/i', $url ) ) {
			$url = 'http://' . $url;
		}

		$url = wp_kses_normalize_entities( $url );
		$url = str_replace( '&amp;', '&#038;', $url );
		$url = str_replace( "'", '&#039;', $url );
	}

	$return = array();
	$return['url'] = $url;

	if ( ! empty( $parts[1] ) && trim( $parts[1] ) != '') {
		$return['label'] = trim( urldecode( $parts[1] ) );
	}

	return $return;
}


/**
 * Add the parent terms in current terms.
 *
 * @since 1.5.7
 * @package GeoDirectory
 *
 * @param int|array $term_ids Term id or Terms array.
 * @param string $taxonomy Category taxonomy of post type.
 * @return array Modified term ids.
 */
function geodir_add_parent_terms($term_ids, $taxonomy) {
	if (is_int($term_ids)) {
		$term_ids = array($term_ids);
	}

	$parent_terms = array();

	foreach ($term_ids as $term_id) {
		$parent_terms[] = $term_id;
		$term_parents = geodir_get_category_parents($term_id, $taxonomy, $parent_terms);

		if (!empty($term_parents)) {
			$parent_terms = array_merge($parent_terms, $term_parents);
		}
	}

	return $parent_terms;
}

/**
 * Get the parent categories of current id.
 *
 * @since 1.5.7
 * @package GeoDirectory
 *
 * @param int $id Category id.
 * @param string $taxonomy Category taxonomy of post type.
 * @param array $visited Array of category ids already included.
 * @param array $parents Array of category ids.
 * @return array Category ids.
 */
function geodir_get_category_parents($id, $taxonomy, $visited = array(), $parents = array()) {
	$parent = get_term($id, $taxonomy);
	if (is_wp_error($parent)) {
		return $parents;
	}

	if (isset($parent->parent) && $parent->parent && ($parent->parent != $parent->term_id) && !in_array($parent->parent, $visited)) {
		$visited[] = $parent->parent;
		$parents[] = $parent->parent;
		$parents = geodir_get_category_parents($parent->parent, $taxonomy, $visited, $parents);
	}

	return $parents;
}

if (!function_exists('geodir_get_ip')) {
/**
 * Get the visitor's IP address.
 *
 * @since 1.5.7
 * @package GeoDirectory
 *
 * @return string The IP address.
 */
function geodir_get_ip() {
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		//check ip from share internet
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		//to check ip is pass from proxy
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}

	/**
	 * Filter the the visitor's IP address.
	 *
	 * @since 1.5.7
	 * @package GeoDirectory
	 *
	 * @param string $ip The IP address.
	 */
	return apply_filters('geodir_get_ip', $ip);
}
}

/**
 * Register die handler for geodir_die()
 *
 * @since 1.5.9
 * @package GeoDirectory
 */
function geodir_die_handler() {
    if ( defined( 'GD_TESTING_MODE' ) ) {
        return 'geodir_die_handler';
    } else {
        die();
    }
}

/**
 * Wrapper function for wp_die(). This function adds filters for wp_die() which
 * kills execution of the script using wp_die(). This allows us to then to work
 * with functions using geodir_die() in the unit tests.
 *
 * @since 1.5.9
 * @package GeoDirectory
 * @param string $message Optional. Error message.
 * @param string $title   Optional. Error title.
 * @param int $status     Optional. Status code.
 */
function geodir_die( $message = '', $title = '', $status = 400 ) {
    add_filter( 'wp_die_ajax_handler', 'geodir_die_handler', 10, 3 );
    add_filter( 'wp_die_handler', 'geodir_die_handler', 10, 3 );
    wp_die( $message, $title, array( 'response' => $status ));
}

/*
 * Matches each symbol of PHP date format standard with jQuery equivalent codeword
 *
 * @since 1.6.5
 * @param string $php_format The PHP date format.
 * @return string The jQuery format date string.
 */
function geodir_date_format_php_to_jqueryui( $php_format ) {
	$symbols = array(
		// Day
		'd' => 'dd',
		'D' => 'D',
		'j' => 'd',
		'l' => 'DD',
		'N' => '',
		'S' => '',
		'w' => '',
		'z' => 'o',
		// Week
		'W' => '',
		// Month
		'F' => 'MM',
		'm' => 'mm',
		'M' => 'M',
		'n' => 'm',
		't' => '',
		// Year
		'L' => '',
		'o' => '',
		'Y' => 'yy',
		'y' => 'y',
		// Time
		'a' => 'tt',
		'A' => 'TT',
		'B' => '',
		'g' => 'h',
		'G' => 'H',
		'h' => 'hh',
		'H' => 'HH',
		'i' => 'mm',
		's' => '',
		'u' => ''
	);

	$jqueryui_format = "";
	$escaping = false;

	for ( $i = 0; $i < strlen( $php_format ); $i++ ) {
		$char = $php_format[$i];

		// PHP date format escaping character
		if ( $char === '\\' ) {
			$i++;

			if ( $escaping ) {
				$jqueryui_format .= $php_format[$i];
			} else {
				$jqueryui_format .= '\'' . $php_format[$i];
			}

			$escaping = true;
		} else {
			if ( $escaping ) {
				$jqueryui_format .= "'";
				$escaping = false;
			}

			if ( isset( $symbols[$char] ) ) {
				$jqueryui_format .= $symbols[$char];
			} else {
				$jqueryui_format .= $char;
			}
		}
	}

	if ( $escaping ) {
		$jqueryui_format .= "'";
	}

	return $jqueryui_format;
}

/*
 * Matches each symbol of PHP date format standard with Flatpickr equivalent codeword
 *
 * @since 2.1.0.0
 * @param string $php_format The PHP date format.
 * @return string The Flatpickr format date string.
 */
function geodir_date_format_php_to_aui( $php_format ) {
	$symbols = array(
		// Day
		'd' => 'd', // Day of the month, 2 digits with leading zeros. Example: 01 to 31
		'D' => 'D', // A textual representation of a day. Example: Mon through Sun
		'j' => 'j', // Day of the month without leading zeros. Example: 1 to 31
		'l' => 'l', // A full textual representation of the day of the week. Example: Sunday through Saturday
		'N' => '',
		'S' => 'J', // Day of the month without leading zeros and ordinal suffix. Example: 1st, 2nd, to 31st
		'w' => 'w', // Numeric representation of the day of the week. Example: 0 (for Sunday) through 6 (for Saturday)
		'z' => '',
		'c' => 'Z', // ISO Date format. Example: 2017-03-04T01:23:43.000Z
		'U' => 'U', // The number of seconds since the Unix Epoch. Example: 1413704993
		// Week
		'W' => 'W', // Numeric representation of the week. Example: 0 (first week of the year) through 52 (last week of the year)
		// Month
		'F' => 'F', // A full textual representation of a month. Example: January through December
		'm' => 'm', // Numeric representation of a month, with leading zero. Example: 01 through 12
		'M' => 'M', // A short textual representation of a month. Example: Jan through Dec
		'n' => 'n', // Numeric representation of a month, without leading zeros. Example: 1 through 12
		't' => '',
		// Year
		'L' => '',
		'o' => '',
		'Y' => 'Y', // A full numeric representation of a year, 4 digits. Example: 1999 or 2003
		'y' => 'y', // A two digit representation of a year. Example: 99 or 03
		// Time
		'a' => 'K', // AM/PM
		'A' => 'K', // AM/PM
		'B' => '',
		'g' => 'h', // Hours. Example: 1 to 12
		'G' => 'H', // Hours (24 hours). Example: 00 to 23
		'h' => 'G', // Hours, 2 digits with leading zeros. Example: 1 to 12
		'H' => 'H', // Hours (24 hours). Example: 00 to 23
		'i' => 'i', // Minutes. Example: 00 to 59
		's' => 'S', // Seconds, 2 digits. Example: 00 to 59
		'u' => ''
	);

	$aui_format = "";
	$escaping = false;

	for ( $i = 0; $i < strlen( $php_format ); $i++ ) {
		$char = $php_format[$i];

		// PHP date format escaping character.
		if ( $char === '\\' ) {
			$aui_format .= $char;

			if ( $escaping ) {
				$escaping = false;
			} else {
				$escaping = true;
			}
		} else {
			if ( ! $escaping && isset( $symbols[$char] ) ) {
				$aui_format .= $symbols[$char];
			} else {
				$aui_format .= $char;
			}

			$escaping = false;
		}
	}

	return $aui_format;
}

/**
 * Maybe untranslate date string for saving to the database.
 *
 * @param string $date The date string.
 *
 * @return string The untranslated date string.
 * @since 1.6.5
 */
function geodir_maybe_untranslate_date( $date ) {
	$date_formated = ' '. $date;

	$names = array(
		// The Weekdays
		array( 'Sunday' => __( 'Sunday' ) ),
		array( 'Monday' => __( 'Monday' ) ),
		array( 'Tuesday' => __( 'Tuesday' ) ),
		array( 'Wednesday' => __( 'Wednesday' ) ),
		array( 'Thursday' => __( 'Thursday' ) ),
		array( 'Friday' => __( 'Friday' ) ),
		array( 'Saturday' => __( 'Saturday' ) ),

		// The Months
		array( 'January' => __( 'January' ) ),
		array( 'February' => __( 'February' ) ),
		array( 'March' => __( 'March' ) ),
		array( 'April' => __( 'April' ) ),
		array( 'May' => __( 'May' ) ),
		array( 'June' => __( 'June' ) ),
		array( 'July' => __( 'July' ) ),
		array( 'August' => __( 'August' ) ),
		array( 'September' => __( 'September' ) ),
		array( 'October' => __( 'October' ) ),
		array( 'November' => __( 'November' ) ),
		array( 'December' => __( 'December' ) ),

		// Abbreviations for each month.
		array( 'Jan' => _x( 'Jan', 'January abbreviation' ) ),
		array( 'Feb' => _x( 'Feb', 'February abbreviation' ) ),
		array( 'Mar' => _x( 'Mar', 'March abbreviation' ) ),
		array( 'Apr' => _x( 'Apr', 'April abbreviation' ) ),
		array( 'May' => _x( 'May', 'May abbreviation' ) ),
		array( 'Jun' => _x( 'Jun', 'June abbreviation' ) ),
		array( 'Jul' => _x( 'Jul', 'July abbreviation' ) ),
		array( 'Aug' => _x( 'Aug', 'August abbreviation' ) ),
		array( 'Sep' => _x( 'Sep', 'September abbreviation' ) ),
		array( 'Oct' => _x( 'Oct', 'October abbreviation' ) ),
		array( 'Nov' => _x( 'Nov', 'November abbreviation' ) ),
		array( 'Dec' => _x( 'Dec', 'December abbreviation' ) ),

		// Abbreviations for each day.
		array( 'Sun' => __( 'Sun' ) ),
		array( 'Mon' => __( 'Mon' ) ),
		array( 'Tue' => __( 'Tue' ) ),
		array( 'Wed' => __( 'Wed' ) ),
		array( 'Thu' => __( 'Thu' ) ),
		array( 'Fri' => __( 'Fri' ) ),
		array( 'Sat' => __( 'Sat' ) ),

		// The first letter of each day.
		array( 'S' => _x( 'S', 'Sunday initial' ) ),
		array( 'M' => _x( 'M', 'Monday initial' ) ),
		array( 'T' => _x( 'T', 'Tuesday initial' ) ),
		array( 'W' => _x( 'W', 'Wednesday initial' ) ),
		array( 'T' => _x( 'T', 'Thursday initial' ) ),
		array( 'F' => _x( 'F', 'Friday initial' ) ),
		array( 'S' => _x( 'S', 'Saturday initial' ) ),

		// The Meridiems
		array( 'am' => __( 'am' ) ),
		array( 'pm' => __( 'pm' ) ),
		array( 'AM' => __( 'AM' ) ),
		array( 'PM' => __( 'PM' ) )
	);

	foreach ( $names as $key => $translations ) {
		foreach ( $translations as $name => $translation ) {
			if ( $translation && trim( $translation ) != '' ) {
				$date_formated = preg_replace( "/([^\\\])" . $name . "/", "\\1" . backslashit( $name ), $date_formated );
				$date_formated = preg_replace( "/([^\\\])" . $translation . "/", "\\1" . backslashit( $name ), $date_formated );
			}
		}
	}

	$date_formated = substr( $date_formated, 1, strlen( $date_formated ) -1 );
	$date = stripslashes( $date_formated );

	return $date;
}

/**
 * Convert date to given format.
 *
 * @since 1.6.7
 *
 * @param string $date_input The date string.
 * @param string $date_to The destination date format.
 * @param string $date_from The source date format.
 * @return string The formatted date.
 */
function geodir_date($date_input, $date_to, $date_from = '') {
    if (empty($date_input) || empty($date_to)) {
        return NULL;
    }

    $date = '';
    if (!empty($date_from)) {
        $datetime = date_create_from_format($date_from, $date_input);

        if (!empty($datetime)) {
            $date = $datetime->format($date_to);
        }
    }

    if (empty($date)) {
        $date = strpos($date_input, '/') !== false ? str_replace('/', '-', $date_input) : $date_input;
        $date = date_i18n($date_to, strtotime($date));
    }

    $date = geodir_maybe_untranslate_date($date);
    /**
     * Filter the the date format conversion.
     *
     * @since 1.6.7
     * @package GeoDirectory
     *
     * @param string $date The date string.
     * @param string $date_input The date input.
     * @param string $date_to The destination date format.
     * @param string $date_from The source date format.
     */
    return apply_filters('geodir_date', $date, $date_input, $date_to, $date_from);
}

/**
 * Truncates the text.
 *
 * Cuts a string to the length of $length and replaces the last characters
 * with the ellipsis if the text is longer than length.
 *
 * @since 1.6.16
 * @package GeoDirectory
 *
 * @param string $text String to truncate.
 * @param int    $length Length of returned string, including ellipsis.
 * @param array  $options {
 *     An array of HTML attributes and options.
 *
 *     @type string $ellipsis Will be used as ending and appended to the trimmed string. Ex: "...".
 *     @type bool   $exact If false, $text will not be cut mid-word.
 *     @type bool   $html If true, HTML tags would be handled correctly.
 *     @type bool   $trimWidth If true, $text will be truncated with the width.
 * }
 * @return string Trimmed string.
 */
function geodir_excerpt($text, $length = 100, $options = array()) {
    if (!(int)$length > 0) {
        return $text;
    }
    $default = array(
        'ellipsis' => '', 'exact' => true, 'html' => true, 'trimWidth' => false,
	);
    if (!empty($options['html']) && function_exists('mb_internal_encoding') && strtolower(mb_internal_encoding()) === 'utf-8') {
        $default['ellipsis'] = "";
    }
    $options += $default;

    $prefix = '';
    $suffix = $options['ellipsis'];

    if ($options['html']) {
        $ellipsisLength = geodir_strlen(strip_tags($options['ellipsis']), $options);

        $truncateLength = 0;
        $totalLength = 0;
        $openTags = array();
        $truncate = '';

        preg_match_all('/(<\/?([\w+]+)[^>]*>)?([^<>]*)/', $text, $tags, PREG_SET_ORDER);
        foreach ($tags as $tag) {
            $contentLength = geodir_strlen($tag[3], $options);

            if ($truncate === '') {
                if (!preg_match('/img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param/i', $tag[2])) {
                    if (preg_match('/<[\w]+[^>]*>/', $tag[0])) {
                        array_unshift($openTags, $tag[2]);
                    } elseif (preg_match('/<\/([\w]+)[^>]*>/', $tag[0], $closeTag)) {
                        $pos = array_search($closeTag[1], $openTags);
                        if ($pos !== false) {
                            array_splice($openTags, $pos, 1);
                        }
                    }
                }

                $prefix .= $tag[1];

                if ($totalLength + $contentLength + $ellipsisLength > $length) {
                    $truncate = $tag[3];
                    $truncateLength = $length - $totalLength;
                } else {
                    $prefix .= $tag[3];
                }
            }

            $totalLength += $contentLength;
            if ($totalLength > $length) {
                break;
            }
        }

        if ($totalLength <= $length) {
            return $text;
        }

        $text = $truncate;
        $length = $truncateLength;

        foreach ($openTags as $tag) {
            $suffix .= '</' . $tag . '>';
        }
    } else {
        if (geodir_strlen($text, $options) <= $length) {
            return $text;
        }
        $ellipsisLength = geodir_strlen($options['ellipsis'], $options);
    }

    $result = geodir_substr($text, 0, $length - $ellipsisLength, $options);

    if (!$options['exact']) {
        if (geodir_substr($text, $length - $ellipsisLength, 1, $options) !== ' ') {
            $result = geodir_remove_last_word($result);
        }

        // Do not need to count ellipsis in the cut, if result is empty.
        if (!strlen($result)) {
            $result = geodir_substr($text, 0, $length, $options);
        }
    }

    return $prefix . $result . $suffix;
}

/**
 * Get string length.
 *
 * ### Options:
 *
 * - `html` If true, HTML entities will be handled as decoded characters.
 * - `trimWidth` If true, the width will return.
 *
 * @since 1.6.16
 * @package GeoDirectory
 *
 * @param string $text The string being checked for length
 * @param array  $options {
 *     An array of options.
 *
 *     @type bool $html If true, HTML entities will be handled as decoded characters.
 *     @type bool $trimWidth If true, the width will return.
 * }
 * @return int
 */
function geodir_strlen($text, array $options) {
    if (empty($options['trimWidth'])) {
        $strlen = 'geodir_utf8_strlen';
    } else {
        $strlen = 'geodir_utf8_strwidth';
    }

    if (empty($options['html'])) {
        return $strlen($text);
    }

    $pattern = '/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i';
    $replace = preg_replace_callback(
        $pattern,
        function ($match) use ($strlen) {
            $utf8 = html_entity_decode($match[0], ENT_HTML5 | ENT_QUOTES, 'UTF-8');

            return str_repeat(' ', $strlen($utf8, 'UTF-8'));
        },
        $text
    );

    return $strlen($replace);
}

/**
 * Return part of a string.
 *
 * @since 1.6.16
 * @package GeoDirectory
 *
 * @param string $text The input string.
 * @param int $start The position to begin extracting.
 * @param int $length The desired length.
 * @param array  $options {
 *     An array of options.
 *
 *     @type bool $html If true, HTML entities will be handled as decoded characters.
 *     @type bool $trimWidth If true, will be truncated with specified width.
 * }
 * @return string
 */
function geodir_substr($text, $start, $length, array $options) {
    if (empty($options['trimWidth'])) {
        $substr = 'geodir_utf8_substr';
    } else {
        $substr = 'geodir_utf8_strimwidth';
    }

    $maxPosition = geodir_strlen($text, array('trimWidth' => false) + $options);
    if ($start < 0) {
        $start += $maxPosition;
        if ($start < 0) {
            $start = 0;
        }
    }
    if ($start >= $maxPosition) {
        return '';
    }

    if ($length === null) {
        $length = geodir_strlen($text, $options);
    }

    if ($length < 0) {
        $text = geodir_substr($text, $start, null, $options);
        $start = 0;
        $length += geodir_strlen($text, $options);
    }

    if ($length <= 0) {
        return '';
    }

    if (empty($options['html'])) {
        return (string)$substr($text, $start, $length);
    }

    $totalOffset = 0;
    $totalLength = 0;
    $result = '';

    $pattern = '/(&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};)/i';
    $parts = preg_split($pattern, $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    foreach ($parts as $part) {
        $offset = 0;

        if ($totalOffset < $start) {
            $len = geodir_strlen($part, array('trimWidth' => false) + $options);
            if ($totalOffset + $len <= $start) {
                $totalOffset += $len;
                continue;
            }

            $offset = $start - $totalOffset;
            $totalOffset = $start;
        }

        $len = geodir_strlen($part, $options);
        if ($offset !== 0 || $totalLength + $len > $length) {
            if (strpos($part, '&') === 0 && preg_match($pattern, $part) && $part !== html_entity_decode($part, ENT_HTML5 | ENT_QUOTES, 'UTF-8') ) {
                // Entities cannot be passed substr.
                continue;
            }

            $part = $substr($part, $offset, $length - $totalLength);
            $len = geodir_strlen($part, $options);
        }

        $result .= $part;
        $totalLength += $len;
        if ($totalLength >= $length) {
            break;
        }
    }

    return $result;
}

/**
 * Removes the last word from the text.
 *
 * @since 1.6.16
 * @package GeoDirectory
 *
 * @param string $text The input text.
 * @return string
 */
function geodir_remove_last_word($text) {
    $spacepos = geodir_utf8_strrpos($text, ' ');

    if ($spacepos !== false) {
        $lastWord = geodir_utf8_strrpos($text, $spacepos);

        // Some languages are written without word separation.
        // We recognize a string as a word if it does not contain any full-width characters.
        if (geodir_utf8_strwidth($lastWord) === geodir_utf8_strlen($lastWord)) {
            $text = geodir_utf8_substr($text, 0, $spacepos);
        }

        return $text;
    }

    return '';
}

/**
 * Restore tool from custom post type taxonomies.
 *
 * @since 2.0.0
 */
function geodir_tool_restore_cpt_from_taxonomies() {
    $cpts = geodir_get_posttypes();
    if ( !empty( $cpts ) ) {
        return;
    }

    $taxonomies = geodir_get_option( 'taxonomies' );
    if ( !empty( $taxonomies ) ) {
        return;
    }

    $cpts = array();
    foreach( $taxonomies as $key => $val ) {
        if ( strpos( $val['listing_slug'], '/' ) === false ) {
            $cpts[ $val['object_type'] ] = array(
                'cpt' => $val['object_type'],
                'slug' => $val['listing_slug']
            );
        }
    }

    if ( !empty( $cpts ) ) {
        return;
    }

    $cpts_restore = $cpts;

    foreach ( $cpts as $cpt ) {
        $is_custom = $cpt['cpt'] == 'gd_place' ? 0 : 1;

        $cpts_restore[ $cpt['cpt'] ] = array(
            'labels' => array (
                'name' => $cpt['slug'],
                'singular_name' => $cpt['slug'],
                'add_new' => 'Add New',
                'add_new_item' => 'Add New ' . $cpt['slug'],
                'edit_item' => 'Edit ' . $cpt['slug'],
                'new_item' => 'New ' . $cpt['slug'],
                'view_item' => 'View ' . $cpt['slug'],
                'search_items' => 'Search ' . $cpt['slug'],
                'not_found' => 'No ' . $cpt['slug'] . ' Found',
                'not_found_in_trash' => 'No ' . $cpt['slug'] . ' Found In Trash',
                'listing_owner' => '',
                'label_post_profile' => '',
                'label_post_info' => '',
                'label_post_images' => '',
                'label_post_map' => '',
                'label_reviews' => '',
                'label_related_listing' => '',
            ),
            'can_export' => true,
            'capability_type' => 'post',
            'description' => '',
            'has_archive' => $cpt['slug'],
            'hierarchical' => false,
            'map_meta_cap' => true,
            'menu_icon' => '',
            'public' => true,
            'query_var' => true,
            'rewrite' => array (
                'slug' => $cpt['slug'],
                'with_front' => false,
                'hierarchical' => true,
                'feeds' => true,
            ),
            'supports' => array (
                0 => 'title',
                1 => 'editor',
                2 => 'author',
                3 => 'thumbnail',
                4 => 'excerpt',
                5 => 'custom-fields',
                6 => 'comments',
            ),
            'taxonomies' => array (
                0 => $cpt['cpt'] . 'category',
                1 => $cpt['cpt'] . '_tags',
            ),
            'is_custom' => $is_custom,
            'listing_order' => '1',
            'seo' => array (
	            'title' => '',
	            'meta_title' => '',
                'meta_description' => '',
            ),
            'show_in_nav_menus' => 1,
            'linkable_to' => '',
            'linkable_from' => '',
        );
    }

    geodir_update_option( 'geodir_post_types',$cpts_restore );
}

/**
 * Get truncated string with specified width.
 *
 * @since 1.6.18
 * @package Geodirectory
 *
 * @param string $str The string being decoded.
 * @param int $start The start position offset. Number of characters from the beginning of string.
 *                      For negative value, number of characters from the end of the string.
 * @param int $width The width of the desired trim. Negative widths count from the end of the string.
 * @param string $trimmaker A string that is added to the end of string when string is truncated. Ex: "...".
 * @param string $encoding The encoding parameter is the character encoding. Default "UTF-8".
 * @return string
 */
function geodir_utf8_strimwidth( $str, $start, $width, $trimmaker = '', $encoding = 'UTF-8' ) {
    if ( function_exists( 'mb_strimwidth' ) ) {
        return mb_strimwidth( $str, $start, $width, $trimmaker, $encoding );
    }

    return geodir_utf8_substr( $str, $start, $width, $encoding ) . $trimmaker;
}

/**
 * Get the string length.
 *
 * @since 1.6.18
 * @package Geodirectory
 *
 * @param string $str The string being checked for length.
 * @param string $encoding The encoding parameter is the character encoding. Default "UTF-8".
 * @return int Returns the number of characters in string.
 */
function geodir_utf8_strlen( $str, $encoding = 'UTF-8' ) {
    if ( function_exists( 'mb_strlen' ) ) {
        return mb_strlen( $str, $encoding );
    }

    return strlen( $str );
}

/**
 * Find position of first occurrence of string in a string
 *
 * @since 1.6.18
 * @package Geodirectory
 *
 * @param string $str The string being checked.
 * @param string $find The string to find in input string.
 * @param int $offset The search offset. Default "0". A negative offset counts from the end of the string.
 * @param string $encoding The encoding parameter is the character encoding. Default "UTF-8".
 * @return int Returns the position of the first occurrence of search in the string.
 */
function geodir_utf8_strpos( $str, $find, $offset = 0, $encoding = 'UTF-8' ) {
    if ( function_exists( 'mb_strpos' ) ) {
        return mb_strpos( $str, $find, $offset, $encoding );
    }

    return strpos( $str, $find, $offset );
}

/**
 * Find position of last occurrence of a string in a string.
 *
 * @since 1.6.18
 * @package Geodirectory
 *
 * @param string $str The string being checked, for the last occurrence of search.
 * @param string $find The string to find in input string.
 * @param int $offset Specifies begin searching an arbitrary number of characters into the string.
 * @param string $encoding The encoding parameter is the character encoding. Default "UTF-8".
 * @return int Returns the position of the last occurrence of search.
 */
function geodir_utf8_strrpos( $str, $find, $offset = 0, $encoding = 'UTF-8' ) {
    if ( function_exists( 'mb_strrpos' ) ) {
        return mb_strrpos( $str, $find, $offset, $encoding );
    }

    return strrpos( $str, $find, $offset );
}

/**
 * Get the part of string.
 *
 * @since 1.6.18
 * @package Geodirectory
 *
 * @param string $str The string to extract the substring from.
 * @param int $start If start is non-negative, the returned string will start at the entered position in string, counting from zero.
 *                      If start is negative, the returned string will start at the entered position from the end of string.
 * @param int|null $length Maximum number of characters to use from string.
 * @param string $encoding The encoding parameter is the character encoding. Default "UTF-8".
 * @return string
 */
function geodir_utf8_substr( $str, $start, $length = null, $encoding = 'UTF-8' ) {
    if ( function_exists( 'mb_substr' ) ) {
        if ( $length === null ) {
            return mb_substr( $str, $start, geodir_utf8_strlen( $str, $encoding ), $encoding );
        } else {
            return mb_substr( $str, $start, $length, $encoding );
        }
    }

    return substr( $str, $start, $length );
}

/**
 * Get the width of string.
 *
 * @since 1.6.18
 * @package Geodirectory
 *
 * @param string $str The string being decoded.
 * @param string $encoding The encoding parameter is the character encoding. Default "UTF-8".
 * @return string The width of string.
 */
function geodir_utf8_strwidth( $str, $encoding = 'UTF-8' ) {
	if ( function_exists( 'mb_strwidth' ) ) {
		return mb_strwidth( $str, $encoding );
	}

	return geodir_utf8_strlen( $str, $encoding );
}

/**
 * Get a string with the first character of string capitalized.
 *
 * @since 1.6.18
 * @package Geodirectory
 *
 * @param string $str The input string.
 * @param bool $lower_str_end If true it returns string lowercased except first character.
 * @param string $encoding The encoding parameter is the character encoding. Default "UTF-8".
 * @return string The resulting string.
 */
function geodir_utf8_ucfirst( $str, $lower_str_end = false, $encoding = 'UTF-8' ) {
    if ( function_exists( 'mb_strlen' ) ) {
        $first_letter = geodir_strtoupper( geodir_utf8_substr( $str, 0, 1, $encoding ), $encoding );
        $str_end = "";

        if ( $lower_str_end ) {
            $str_end = geodir_strtolower( geodir_utf8_substr( $str, 1, geodir_utf8_strlen( $str, $encoding ), $encoding ), $encoding );
        } else {
            $str_end = geodir_utf8_substr( $str, 1, geodir_utf8_strlen( $str, $encoding ), $encoding );
        }

        return $first_letter . $str_end;
    }

    return ucfirst( $str );
}

/**
 * Total listings count.
 *
 * @param bool $post_type Optional Post type. Default false.
 *
 * @global object $wpdb WordPress Database object.
 *
 * @return int $count.
 */
function geodir_total_listings_count( $post_type = false ) {
	global $wpdb;

	$count = 0;

	if ( $post_type ) {
		$count = $count + $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(post_id) FROM %i", geodir_db_cpt_table( $post_type ) ) );
	} else {
		$post_types = geodir_get_posttypes();

		if ( ! empty( $post_types ) ) {
			foreach ( $post_types as $key ) {
				$count = $count + $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(post_id) FROM %i", geodir_db_cpt_table( $key ) ) );
			}
		}
	}

	return $count;
}

/**
 * Get diagnose step max items.
 *
 * @return int
 */
function geodir_get_diagnose_step_max_items() {
	return 5;
}

/**
 * File relative url.
 *
 * @since 2.0.0
 *
 * @param string $url URL.
 * @param bool $full_path Optional. Full Path. Default false.
 * @return string
 */
function geodir_file_relative_url( $url, $full_path = false ) {
    $url = trim( $url );

    if ( !$url ) {
        return $url;
    }

    $relative_url = $url;
    $url = trim( $url, '/\\' ); // clean slashes

    $upload_dir = wp_upload_dir();
    $upload_basedir = $upload_dir['basedir'];
    $upload_baseurl = $upload_dir['baseurl'];
    $content_dir = untrailingslashit( WP_CONTENT_DIR );
    $content_url = untrailingslashit( WP_CONTENT_URL );

    if ( strpos( $upload_baseurl, 'https://' ) === 0 ) {
        $https = 'https://';
        $match_upload_baseurl = str_replace( 'https://', '', $upload_baseurl );
        $content_url = str_replace( 'http://', 'https://', $content_url );
    } else {
        $https = 'http://';
        $match_upload_baseurl = str_replace( 'http://', '', $upload_baseurl );
        $content_url = str_replace( 'https://', 'http://', $content_url );
    }

    $match_content_url = strpos( $content_url, 'https://' ) === 0 ? str_replace( 'https://', '', $content_url ) : str_replace( 'http://', '', $content_url );
    $match_url = strpos( $url, 'https://' ) === 0 ? str_replace( 'https://', '', $url ) : str_replace( 'http://', '', $url );

	// www.
	$www = '';
	if ( strpos( $match_upload_baseurl, 'www.' ) === 0 ) {
		$www = 'www.';
		$match_upload_baseurl = str_replace( 'www.', '', $match_upload_baseurl );
	}
	if ( strpos( $match_content_url, 'www.' ) === 0 ) {
		$match_content_url = str_replace( 'www.', '', $match_content_url );
	}
	if ( strpos( $match_url, 'www.' ) === 0 ) {
		$match_url = str_replace( 'www.', '', $match_url );
	}

    if ( $full_path ) {
        if ( strpos( $relative_url, 'http://' ) === 0 || strpos( $relative_url, 'https://' ) === 0 ) {
            if ( strpos( $match_url, $match_upload_baseurl ) === 0 || strpos( $match_url, $match_content_url ) === 0 ) {
                $relative_url = $https . $www . $match_url;
            }
        } else {
            if ( is_file( $content_dir . '/' . $match_url ) && file_exists( $content_dir . '/' . $match_url ) ) { // url contains content url
                $relative_url = $content_url . '/' . $match_url;
            } elseif ( is_file( $upload_basedir . '/' . $match_url ) && file_exists( $upload_basedir . '/' . $match_url ) ) { // url contains content url
                $relative_url = $upload_baseurl . '/' . $match_url;
            }
        }
    } else {
        if ( strpos( $match_url, $match_upload_baseurl ) === 0 ) { // url contains uploads baseurl
            $relative_url = str_replace( $match_upload_baseurl, '', $match_url );
        } elseif ( strpos( $match_url, $match_content_url ) === 0 ) { // url contains content url
            $relative_url = str_replace( $match_content_url, '', $match_url );
        }

        $relative_url = trim( $relative_url, '/\\' );
    }

    return apply_filters( 'geodir_file_relative_url', $relative_url, $url, $full_path );
}

/**
 * Check is image file.
 *
 * @since 2.0.0
 *
 * @param string $url URL.
 * @return bool
 */
function geodir_is_image_file( $url ) {
	if ( ! empty( $url ) ) {
		$filetype = wp_check_filetype( $url );

		if ( !empty( $filetype['ext'] ) && in_array( $filetype['ext'], array( 'jpg', 'jpeg', 'jpe', 'gif', 'png', 'bmp', 'tif', 'tiff', 'ico', 'webp', 'svg', 'avif' ) ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Get php arguments separator output.
 *
 * @since 2.0.0
 *
 * @return string
 */
function geodir_get_php_arg_separator_output() {
    return ini_get( 'arg_separator.output' );
}

/**
 * RGB from hex.
 *
 * @since 2.0.0
 *
 * @param string $color Color.
 * @return array $rgb.
 */
function geodir_rgb_from_hex( $color ) {
    $color = str_replace( '#', '', $color );

    // Convert shorthand colors to full format, e.g. "FFF" -> "FFFFFF"
    $color = preg_replace( '~^(.)(.)(.)$~', '$1$1$2$2$3$3', $color );
    if ( empty( $color ) ) {
        return NULL;
    }

    $color = str_split( $color );

    $rgb      = array();
    $rgb['R'] = hexdec( $color[0] . $color[1] );
    $rgb['G'] = hexdec( $color[2] . $color[3] );
    $rgb['B'] = hexdec( $color[4] . $color[5] );

    return $rgb;
}

/**
 * HEX darker.
 *
 * @since 2.0.0
 *
 * @param string $color Color.
 * @param int $factor Optional. Factor. Default 30.
 * @return string $color.
 */
function geodir_hex_darker( $color, $factor = 30 ) {
    $base  = geodir_rgb_from_hex( $color );
    if ( empty( $base ) ) {
		return $color;
	}

	$color = '#';
    foreach ( $base as $k => $v ) {
        $amount      = $v / 100;
        $amount      = round( $amount * $factor );
        $new_decimal = $v - $amount;

        $new_hex_component = dechex( $new_decimal );
        if ( strlen( $new_hex_component ) < 2 ) {
            $new_hex_component = "0" . $new_hex_component;
        }
        $color .= $new_hex_component;
    }

    return $color;
}

/**
 * Hex lighter.
 *
 * @since 2.0.0
 *
 * @param string $color Color.
 * @param int $factor Optional. factor. Default 30.
 * @return string $color.
 */
function geodir_hex_lighter( $color, $factor = 30 ) {
    $base  = geodir_rgb_from_hex( $color );
    if ( empty( $base ) ) {
		return $color;
	}

    $color = '#';

    foreach ( $base as $k => $v ) {
        $amount      = 255 - $v;
        $amount      = $amount / 100;
        $amount      = round( $amount * $factor );
        $new_decimal = $v + $amount;

        $new_hex_component = dechex( $new_decimal );
        if ( strlen( $new_hex_component ) < 2 ) {
            $new_hex_component = "0" . $new_hex_component;
        }
        $color .= $new_hex_component;
    }

    return $color;
}

/**
 * Get Light or dark.
 *
 * @since 2.0.0
 *
 * @param string $color color.
 * @param string $dark Optional. Dark. Default #000000.
 * @param string $light Optional. Light. Default #FFFFFF.
 * @return string
 */
function geodir_light_or_dark( $color, $dark = '#000000', $light = '#FFFFFF' ) {
    $hex = str_replace( '#', '', $color );
    if ( empty( $hex ) ) {
		return $color;
	}

    $c_r = hexdec( substr( $hex, 0, 2 ) );
    $c_g = hexdec( substr( $hex, 2, 2 ) );
    $c_b = hexdec( substr( $hex, 4, 2 ) );

    $brightness = ( ( $c_r * 299 ) + ( $c_g * 587 ) + ( $c_b * 114 ) ) / 1000;

    return $brightness > 155 ? $dark : $light;
}

/**
 * Format hex.
 *
 * @since 2.0.0
 *
 * @param string $hex hex.
 * @return string
 */
function geodir_format_hex( $hex ) {
    $hex = trim( str_replace( '#', '', $hex ) );
	if ( empty( $hex ) ) {
		return NULL;
	}

    if ( strlen( $hex ) == 3 ) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }

    return $hex ? '#' . $hex : null;
}

/**
 * Get a array of user roles.
 *
 * @param array $exclude An array of roles to exclude from the return array.
 * @since 2.0.0
 * @return array An array of roles.
 */
function geodir_user_roles($exclude = array()){
	$user_roles = array();
	if ( !function_exists('get_editable_roles') ) {
		require_once( ABSPATH . '/wp-admin/includes/user.php' );
	}
	$roles = get_editable_roles();
	foreach ( $roles as $role => $details ) {
		if(in_array($role,$exclude)){}else{
			$user_roles[esc_attr( $role )] = translate_user_role($details['name'] );
		}

	}
	return $user_roles;
}

/**
 * Get endpoint URL.
 *
 * Gets the URL for an endpoint, which varies depending on permalink settings.
 *
 * @since 2.0.0
 *
 * @param  string $endpoint  Endpoint slug.
 * @param  string $value     Query param value.
 * @param  string $permalink Permalink.
 *
 * @return string
 */
function geodir_get_endpoint_url( $endpoint, $value = '', $permalink = '' ) {
	if ( ! $permalink ) {
		$permalink = get_permalink();
	}

	// Map endpoint to options.
	$query_vars = array();//GeoDir_Query()->query->get_query_vars();
	$endpoint   = ! empty( $query_vars[ $endpoint ] ) ? $query_vars[ $endpoint ] : $endpoint;

	if ( get_option( 'permalink_structure' ) ) {
		if ( strstr( $permalink, '?' ) ) {
			$query_string = '?' . wp_parse_url( $permalink, PHP_URL_QUERY );
			$permalink    = current( explode( '?', $permalink ) );
		} else {
			$query_string = '';
		}
		$url = trailingslashit( $permalink ) . trailingslashit( $endpoint );

		if ( $value ) {
			$url .= trailingslashit( $value );
		}

		$url .= $query_string;
	} else {
		$url = add_query_arg( $endpoint, $value, $permalink );
	}

	return apply_filters( 'geodir_get_endpoint_url', $url, $endpoint, $value, $permalink );
}

/**
 * Array splice assoc.
 *
 * @since 2.0.0
 *
 * @param string $input Input value.
 * @param string $offset Offset.
 * @param string $length Length.
 * @param string $replacement Replacement.
 * @return array $input.
 */
function geodir_array_splice_assoc( $input, $offset, $length, $replacement ) {
	$replacement = (array) $replacement;

	$key_indices = array_flip( array_keys( $input ) );

	if ( isset( $input[ $offset ] ) && is_string( $offset ) ) {
		$offset = $key_indices[ $offset ];
	}
	if ( isset( $input[ $length ] ) && is_string( $length ) ) {
		$length = $key_indices[ $length ] - $offset;
	}

	$input = array_slice( $input, 0, $offset, true ) + $replacement + array_slice( $input, $offset + $length, null, true );

	return $input;
}

/**
 * Get the post type categories as an array of options.
 *
 * @param string $post_type
 *
 * @return array
 */
function geodir_category_options( $post_type = 'gd_place', $hide_empty = true ) {
	$cache_key = 'gd_category_options_' . $post_type . ':' . $hide_empty;
	$options = wp_cache_get( $cache_key, 'gd_category_options' );

	if ( ! empty( $options ) ) {
		return $options;
	}

	$post_types = geodir_get_posttypes();

	if ( ! in_array( $post_type, $post_types ) ) {
		$post_type = 'gd_place';
	}

	$terms = get_terms( array( 'taxonomy' => $post_type . 'category', 'orderby' => 'count', 'order' => 'DESC', 'hide_empty' => $hide_empty ) );

	$options = array(
		'0' => __( 'All', 'geodirectory' )
	);

	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			$options[ $term->term_id ] = $term->name;
		}

		wp_cache_set( $cache_key, $options, 'gd_category_options' );
	}

	return $options;
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
 * Get the post type sort by options array.
 *
 * @param string $post_type
 *
 * @return array
 */
function geodir_sort_by_options( $post_type = 'gd_place' ) {
	// check for cache
	$cache = wp_cache_get( "gd_sort_by_options_" . $post_type, 'gd_sort_by_options' );
	if ( $cache ) {
		return $cache;
	}

	$options = array(
		"az" => __('A-Z', 'geodirectory'),
		"latest" => __('Latest', 'geodirectory'),
		"high_review" => __('Most reviews', 'geodirectory'),
		"high_rating" => __('Highest rating', 'geodirectory'),
		"random" => __('Random', 'geodirectory'),
	);

	if ( GeoDir_Post_types::supports( $post_type, 'location' ) ) {
		$options['distance_asc'] = __('Distance to current post (details page only)', 'geodirectory');
	}

	if  ( $sort_options = geodir_get_sort_options( $post_type ) ) {
		foreach( $sort_options as $sort_option ) {
			$sort_option = stripslashes_deep( $sort_option );

            $label = __( $sort_option->frontend_title, 'geodirectory' );

            if ( $sort_option->htmlvar_name == 'comment_count' ) {
                $sort_option->htmlvar_name = 'rating_count';
            }

            if ( $sort_option->field_type == 'random' ) {
                $options[ 'random' ] = $label;
            } else {
                if ( $sort_option->sort == 'asc' ) {
                    $options[ $sort_option->htmlvar_name . '_asc' ] = $label;
                } else if ( $sort_option->sort == 'desc' ) {
                    $options[ $sort_option->htmlvar_name . '_desc' ] = $label;
                }
            }
		}
	}

	$options = apply_filters( 'geodir_sort_by_options', $options, $post_type );

	// set cache
	wp_cache_set( "gd_sort_by_options_" . $post_type, $options, 'gd_sort_by_options' );

	return $options;
}

/**
 * Get the post type categories in tree level as an array of options.
 *
 * @param string $post_type
 *
 * @return array
 */
function geodir_category_tree_options( $post_type = 'gd_place', $parent = 0, $hide_empty = false, $all = false, $level = 0 ) {
	// check for cache
	$cache = wp_cache_get( "gd_category_options_".$post_type.":".$parent.":".$hide_empty.":".$all.":".$level, 'gd_category_tree_options' );
	if($cache){
		return $cache;
	}

	if ( $level == 0 && $all ) {
		$options = array(
			'0' => __( 'All', 'geodirectory' )
		);
	} else {
		$options = array();
	}

	$terms = get_terms( array( 'taxonomy' => $post_type . 'category', 'parent' => $parent, 'hide_empty' => $hide_empty ) );

	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			$prefix = $level > 0 ? str_repeat( '-', $level ) . ' ' : '';
			$options[ $term->term_id ] = $prefix . geodir_utf8_ucfirst( $term->name );

			$child_options = geodir_category_tree_options( $post_type, $term->term_id, $hide_empty, $all, ( $level + 1 ) );

			if ( ! empty( $child_options ) ) {
				foreach ( $child_options as $child_id => $child_name ) {
					$options[ $child_id ] = $child_name;
				}
			}
		}
	}

	// set cache
	wp_cache_set( "gd_category_options_".$post_type.":".$parent.":".$hide_empty.":".$all.":".$level, $options, 'gd_category_tree_options' );

	return $options;
}


/**
 * Get raw meta value, so not to pass through post meta filter.
 *
 * @param $object_id
 * @param $meta_key
 *
 * @return null|string
 */
function geodir_get_post_meta_raw($object_id, $meta_key){
	global $wpdb;

	$value = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM $wpdb->postmeta WHERE post_id = %d AND  meta_key = %s ",$object_id,$meta_key)  );

	return $value;
}

/**
 * Set a cookie - wrapper for setcookie using WP constants.
 *
 * @since 2.0.0.68
 *
 * @param string $name   Name of the cookie being set.
 * @param string $value  Value of the cookie.
 * @param integer $expire Expiry of the cookie.
 * @param bool $secure Whether the cookie should be served only over https.
 * @param bool $httponly Whether the cookie is only accessible over HTTP, not scripting languages like JavaScript.
 */
function geodir_setcookie( $name, $value, $expire = 0, $secure = false, $httponly = false ) {
	if ( ! headers_sent() ) {
		setcookie( $name, $value, $expire, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, $secure, apply_filters( 'geodir_cookie_httponly', $httponly, $name, $value, $expire, $secure ) );
		$_COOKIE[ $name ] = $value;
	} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		geodir_error_log( "{$name} cookie cannot be set - headers already sent!" ); // @codingStandardsIgnoreLine
	}
}

/**
 * Get a cookie.
 *
 * @since 2.0.0.68
 *
 * @param  string $name Name of the cookie being set.
 * @return string Value of the cookie.
 */
function geodir_getcookie( $name ) {
	return ! empty( $_COOKIE ) && isset( $_COOKIE[ $name ] ) ? $_COOKIE[ $name ] : '';
}

function geodir_aui_colors($include_branding = false, $include_outlines = false, $outline_button_only_text = false){
	$theme_colors = array();

	$theme_colors["primary"] = __('Primary', 'geodirectory');
	$theme_colors["secondary"] = __('Secondary', 'geodirectory');
	$theme_colors["success"] = __('Success', 'geodirectory');
	$theme_colors["danger"] = __('Danger', 'geodirectory');
	$theme_colors["warning"] = __('Warning', 'geodirectory');
	$theme_colors["info"] = __('Info', 'geodirectory');
	$theme_colors["light"] = __('Light', 'geodirectory');
	$theme_colors["dark"] = __('Dark', 'geodirectory');
	$theme_colors["white"] = __('White', 'geodirectory');
	$theme_colors["purple"] = __('Purple', 'geodirectory');
	$theme_colors["salmon"] = __('Salmon', 'geodirectory');
	$theme_colors["cyan"] = __('Cyan', 'geodirectory');
	$theme_colors["gray"] = __('Gray', 'geodirectory');
	$theme_colors["indigo"] = __('Indigo', 'geodirectory');
	$theme_colors["orange"] = __('Orange', 'geodirectory');

	if($include_outlines){
		$button_only =  $outline_button_only_text ? " ".__("(button only)","geodirectory") : '';
		$theme_colors["outline-primary"] = __('Primary outline', 'geodirectory') . $button_only;
		$theme_colors["outline-secondary"] = __('Secondary outline', 'geodirectory') . $button_only;
		$theme_colors["outline-success"] = __('Success outline', 'geodirectory') . $button_only;
		$theme_colors["outline-danger"] = __('Danger outline', 'geodirectory') . $button_only;
		$theme_colors["outline-warning"] = __('Warning outline', 'geodirectory') . $button_only;
		$theme_colors["outline-info"] = __('Info outline', 'geodirectory') . $button_only;
		$theme_colors["outline-light"] = __('Light outline', 'geodirectory') . $button_only;
		$theme_colors["outline-dark"] = __('Dark outline', 'geodirectory') . $button_only;
		$theme_colors["outline-white"] = __('White outline', 'geodirectory') . $button_only;
		$theme_colors["outline-purple"] = __('Purple outline', 'geodirectory') . $button_only;
		$theme_colors["outline-salmon"] = __('Salmon outline', 'geodirectory') . $button_only;
		$theme_colors["outline-cyan"] = __('Cyan outline', 'geodirectory') . $button_only;
		$theme_colors["outline-gray"] = __('Gray outline', 'geodirectory') . $button_only;
		$theme_colors["outline-indigo"] = __('Indigo outline', 'geodirectory') . $button_only;
		$theme_colors["outline-orange"] = __('Orange outline', 'geodirectory') . $button_only;
	}


	if($include_branding){
		$theme_colors = $theme_colors  + geodir_aui_branding_colors();
	}

	return $theme_colors;
}

function geodir_aui_branding_colors(){
	return array(
		"facebook" => __('Facebook', 'geodirectory'),
		"twitter" => __('Twitter', 'geodirectory'),
		"instagram" => __('Instagram', 'geodirectory'),
		"linkedin" => __('Linkedin', 'geodirectory'),
		"flickr" => __('Flickr', 'geodirectory'),
		"github" => __('GitHub', 'geodirectory'),
		"youtube" => __('YouTube', 'geodirectory'),
		"wordpress" => __('WordPress', 'geodirectory'),
		"google" => __('Google', 'geodirectory'),
		"yahoo" => __('Yahoo', 'geodirectory'),
		"vkontakte" => __('Vkontakte', 'geodirectory'),
	);
}

/**
 * Get the post id of the first post that has content for a field key.
 *
 * This is used to help with block previews.
 *
 * @param string $field_key
 * @param string $post_type
 *
 * @return int|null|string
 */
function geodir_get_post_id_with_content($field_key = '',$post_type = 'gd_place'){
	global $wpdb;

	$post_id = 0;
	$table = geodir_db_cpt_table( $post_type );
	$result = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $table WHERE `post_status` = 'publish' AND %s != '' AND %s IS NOT NULL",$field_key,$field_key));
	if(!empty($result)){
		$post_id = $result;
	}

	return $post_id;

}

/**
 * Checks a version number against the core version and adds a admin notice if requirements are not met.
 *
 * @param $name
 * @param $version
 *
 * @return bool
 */
function geodir_min_version_check($name,$version){
	if (version_compare(GEODIRECTORY_VERSION, $version, '<')) {
		add_action( 'admin_notices', function () use (&$name){
			?>
			<div class="notice notice-error is-dismissible">
				<p><?php echo sprintf( __("%s requires a newer version of GeoDirectory and will not run until the GeoDirectory plugin is updated.","geodirectory"),$name); ?></p>
			</div>
			<?php
		});

		return false;
	}

	return true;
}

/**
 * Return the bsui class for AUI if AUI design is active.
 *
 * @return string
 */
function geodir_bsui_class(){
	return geodir_design_style() ? 'bsui' : '';
}

/**
 * Check full url or not.
 *
 * @since 2.1.0.7
 *
 * @param string $url The url.
 * @return bool True if full url or False.
 */
function geodir_is_full_url( $url ) {

	// Start with http: or https:.
	if ( is_string($url) && ( 0 === stripos( $url, 'http:' ) || 0 === stripos( $url, 'https:' ) ) ) {
		return true;
	}

	return false;
}

/**
 * Check current request contains requested uri.
 *
 * @since 2.2.8
 *
 * @param string $match Matched requested uri.
 * @return bool True on match else false.
 */
function geodir_has_request_uri( $match ) {
	if ( $match && ! empty( $_SERVER['REQUEST_URI'] ) && strpos( $_SERVER['REQUEST_URI'], $match ) !== false ) {
		return true;
	}

	return false;
}

/**
 * Parse the video data like id & provider from url.
 *
 * @since 2.2.13
 *
 * @param string $url Requested video url.
 * @param array  $args Extra arguments.
 * @return array Video data.
 */
function geodir_parse_video_data( $url, $args = array() ) {
	$video_id = '';
	$video_type = '';

	if ( ! empty( $url ) ) {
		if ( ( false !== strpos( $url, 'youtube' ) || false !== strpos( $url, 'youtu.be' ) ) && preg_match( '%(?:youtube(?:-nocookie)?.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu.be/)([^"&?/ ]{11})%i', $url, $matches ) ) {
			// Youtube
			$video_type = 'youtube';
			$video_id = $matches[1];
		}else if ( false !== strpos( $url, 'vimeo.com' ) && preg_match( '#(?:https?://)?(?:www.)?(?:player.)?vimeo.com/(?:[a-z]*/)*([0-9]{6,11})[?]?.*#', $url, $matches ) ) {
			// Vimeo
			$video_type = 'vimeo';
			$video_id = $matches[1];
		} else if ( preg_match( '!^.+dailymotion\.com/(video|hub)/([^_]+)[^#]*(#video=([^_&]+))?|(dai\.ly/([^_]+))!', $url, $matches ) ) {
			// Dailymotion
			if ( ! empty( $matches[6] ) ) {
				$video_id = $matches[6];
			} else if ( ! empty( $matches[4] ) ) {
				$video_id = $matches[4];
			} else if ( ! empty( $matches[2] ) ) {
				$video_id = $matches[2];
			}

			if ( $video_id ) {
				$video_type = 'dailymotion';
			}
		} else if ( preg_match( '#https?:.*?.(mp4|mov)#s', $url, $matches ) ) {
			// mp4/mov
			$video_type = 'mp4';
			$video_id = $matches[0];
		} else if ( ( false !== strpos( $url, 'facebook.com' ) || false !== strpos( $url, 'fb.com' ) ) && preg_match( '/videos\/(\d+)+|v=(\d+)|vb.\d+\/(\d+)/', $url, $matches ) ) {
			// Facebook
			if ( ! empty( $matches[2] ) ) {
				$video_id = $matches[2];
			} else if ( ! empty( $matches[1] ) ) {
				$video_id = $matches[1];
			}

			if ( $video_id ) {
				$video_type = 'facebook';
			}
		}
	}

	$data = array(
		'video_type' => $video_type,
		'video_id' => ( ! empty( $video_id ) ? urlencode( $video_id ) : '' )
	);

	/**
	 * Filter the video data.
	 *
	 * @since 2.2.13
	 *
	 * @param array  $data Video data.
	 * @param string $url Requested video url.
	 * @param array  $args Extra arguments.
	 */
	return apply_filters( 'geodir_parse_video_data', $data, $url, $args );
}

/**
 * Get the video embed url.
 *
 * @since 2.2.13
 *
 * @param string $url Requested video url.
 * @param array  $args Extra arguments.
 */
function geodir_parse_embed_url( $url, $args = array() ) {
	$data = geodir_parse_video_data( $url, $args );

	$embed_url = $url;

	if ( ! empty( $data['video_id'] ) ) {
		$video_id = $data['video_id'];
		$provider = ! empty( $data['video_type'] ) ? $data['video_type'] : '';

		switch ( $provider ) {
			case 'youtube':
				$embed_url = 'https://www.youtube.com/embed/' . $video_id . '?autoplay=1';
				break;
			case 'vimeo':
				$embed_url = 'https://player.vimeo.com/video/' . $video_id . '?autoplay=1';
				break;
			case 'dailymotion':
				$embed_url = 'https://www.dailymotion.com/embed/video/' . $video_id . '?autoplay=1';
				break;
			case 'mp4':
				$embed_url = $url;
				break;
			case 'facebook':
				$embed_url = 'https://www.facebook.com/plugins/video.php?href=' . urlencode( $url ) . '&autoplay=1';
				break;
		}
	}
	/**
	 * Filter the video embed url.
	 *
	 * @since 2.2.13
	 *
	 * @param string $embed_url Embed video url.
	 * @param string $url Requested video url.
	 * @param array  $data Video data.
	 * @param array  $args Extra arguments.
	 */
	return apply_filters( 'geodir_parse_embed_url', $embed_url, $url, $data, $args );
}

/**
 * Escape a string to be used in a CSV export.
 *
 * @see https://hackerone.com/reports/72785
 *
 * @since 2.2.20
 *
 * @param string $data Data to escape.
 * @return string
 */
function geodir_escape_csv_data( $data ) {
	$escape_chars = array( '=', '+', '-', '@' );

	if ( $data && in_array( substr( $data, 0, 1 ), $escape_chars, true ) ) {
		$data = " " . $data;
	}

	return $data;
}

/**
 * Format and escape data ready for the CSV export.
 *
 * @since 2.2.20
 *
 * @param  string $data Data to format.
 * @return string
 */
function geodir_format_csv_data( $data ) {
	if ( $data && function_exists( 'mb_convert_encoding' ) ) {
		$encoding = mb_detect_encoding( $data, 'UTF-8, ISO-8859-1', true );
		$data = 'UTF-8' === $encoding ? $data : utf8_encode( $data );
	}

	return geodir_escape_csv_data( $data );
}
