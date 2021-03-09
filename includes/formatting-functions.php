<?php
/**
 * GeoDirectory Formatting
 *
 * Functions for formatting data.
 *
 * @author 		AyeCode
 * @category 	Core
 * @package 	GeoDirectory/Functions
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


/**
 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
 * Non-scalar values are ignored.
 * @param string|array $var
 * @return string|array
 */
function geodir_clean( $var ) {
	if ( is_array( $var ) ) {
		return array_map( 'geodir_clean', $var );
	} else {
		return is_scalar( $var ) ? geodir_sanitize_text_field( $var ) : $var;
	}
}

/**
 * Emulate the WP native sanitize_text_field function in a %%variable%% safe way.
 *
 * @see   https://core.trac.wordpress.org/browser/trunk/src/wp-includes/formatting.php for the original
 *
 * Sanitize a string from user input or from the db.
 *
 * - Check for invalid UTF-8,
 * - Convert single < characters to entity,
 * - Strip all tags,
 * - Remove line breaks, tabs and extra white space,
 * - Strip octets - BUT DO NOT REMOVE (part of) VARIABLES WHICH WILL BE REPLACED.
 *
 * @static
 *
 * @since 2.0.0
 *
 * @param string $value String value to sanitize.
 *
 * @return string
 */
function geodir_sanitize_text_field( $value ) {
	$filtered = wp_check_invalid_utf8( $value );

	if ( strpos( $filtered, '<' ) !== false ) {
		$filtered = wp_pre_kses_less_than( $filtered );
		// This will strip extra whitespace for us.
		$filtered = wp_strip_all_tags( $filtered, true );
	}
	else {
		$filtered = trim( preg_replace( '`[\r\n\t ]+`', ' ', $filtered ) );
	}

	$found = false;
	while ( preg_match( '`[^%](%[a-f0-9]{2})`i', $filtered, $match ) ) {
		$filtered = str_replace( $match[1], '', $filtered );
		$found    = true;
	}
	unset( $match );

	if ( $found ) {
		// Strip out the whitespace that may now exist after removing the octets.
		$filtered = trim( preg_replace( '` +`', ' ', $filtered ) );
	}

	/**
	 * Filter a sanitized text field string.
	 *
	 * @since WP 2.9.0
	 *
	 * @param string $filtered The sanitized string.
	 * @param string $str      The string prior to being sanitized.
	 */

	return apply_filters( 'sanitize_text_field', $filtered, $value );
}

/**
 * Clean variables.
 *
 * This function is used to create posttype, posts, taxonomy and terms slug.
 *
 * @since   1.0.0
 * @package GeoDirectory
 *
 * @param string $string The variable to clean.
 *
 * @return string Cleaned variable.
 */
function geodir_clean_slug( $string ) {

	$string = trim( strip_tags( stripslashes( $string ) ) );
	$string = str_replace( " ", "-", $string ); // Replaces all spaces with hyphens.
	$string = preg_replace( '/[^A-Za-z0-9\-\_]/', '', $string ); // Removes special chars.
	$string = preg_replace( '/-+/', '-', $string ); // Replaces multiple hyphens with single one.

	return $string;
}

/**
 * Sanitize a string destined to be a tooltip.
 *
 * @since 2.0.0 Tooltips are encoded with htmlspecialchars to prevent XSS. Should not be used in conjunction with esc_attr()
 * @param string $var
 * @return string
 */
function geodir_sanitize_tooltip( $var ) {
	return htmlspecialchars( wp_kses( html_entity_decode( $var ), array(
		'br'     => array(),
		'em'     => array(),
		'strong' => array(),
		'small'  => array(),
		'span'   => array(),
		'ul'     => array(),
		'li'     => array(),
		'ol'     => array(),
		'p'      => array(),
	) ) );
}

/**
 * Return the formatted date.
 *
 * Return a formatted date from a date/time string according to WordPress date format. $date must be in format : 'Y-m-d
 * H:i:s'.
 *
 * @since   1.0.0
 * @package GeoDirectory
 *
 * @param string $date must be in format: 'Y-m-d H:i:s'.
 *
 * @return bool|int|string the formatted date.
 */
function geodir_get_formated_date( $date ) {
	return mysql2date( get_option( 'date_format' ), $date );
}

/**
 * Return the formatted time.
 *
 * Return a formatted time from a date/time string according to WordPress time format. $time must be in format : 'Y-m-d
 * H:i:s'.
 *
 * @since   1.0.0
 * @package GeoDirectory
 *
 * @param string $time must be in format: 'Y-m-d H:i:s'.
 *
 * @return bool|int|string the formatted time.
 */
function geodir_get_formated_time( $time ) {
	return mysql2date( get_option( 'time_format' ), $time, $translate = true );
}

/**
 * GeoDirectory Date Format.
 *
 * @since 2.0.0
 *
 * @return string
 */
function geodir_date_format() {
	$date_format = get_option( 'date_format' );
	if ( empty( $date_format ) ) {
		$date_format = 'F j, Y';
	} 
	return apply_filters( 'geodir_date_format', $date_format );
}

/**
 * GeoDirectory Time Format.
 *
 * @since 2.0.0
 *
 * @return string
 */
function geodir_time_format() {
	$time_format = get_option( 'time_format' );
	if ( empty( $time_format ) ) {
		$time_format = 'g:i a';
	}
	return apply_filters( 'geodir_time_format', $time_format );
}

/**
 * GeoDirectory Date Time Format.
 *
 * @since 2.0.0
 *
 * @param string|bool $sep Separator. Default null.
 * @return string
 */
function geodir_date_time_format( $sep = null ) {
	if ( $sep === null || $sep === false ) {
		$sep = ' ';
	}

	$date_time_format = geodir_date_format() . $sep . geodir_time_format();

	return apply_filters( 'geodir_date_time_format', $date_time_format, $sep );
}

/**
 * let_to_num function.
 *
 * This function transforms the php.ini notation for numbers (like '2M') to an integer.
 *
 * @since 2.0.0
 * @param $size
 * @return int
 */
function geodir_let_to_num( $size ) {
	$l   = substr( $size, -1 );
	$ret = substr( $size, 0, -1 );
	switch ( strtoupper( $l ) ) {
		case 'P':
			$ret *= 1024;
		case 'T':
			$ret *= 1024;
		case 'G':
			$ret *= 1024;
		case 'M':
			$ret *= 1024;
		case 'K':
			$ret *= 1024;
	}
	return $ret;
}

/**
 * Return the thousand separator for prices.
 * @since  2.0.0
 * @return string
 */
function geodir_get_price_thousand_separator() {
	$separator = apply_filters( 'geodir_get_price_thousand_separator', ',' );
	return stripslashes( $separator );
}

/**
 * Return the decimal separator for prices.
 * @since  2.0.0
 * @return string
 */
function geodir_get_price_decimal_separator() {
	$separator = apply_filters( 'geodir_get_price_decimal_separator', '.' );
	return $separator ? stripslashes( $separator ) : '.';
}

/**
 * Return the number of decimals after the decimal point.
 * @since  2.0.0
 * @return int
 */
function geodir_get_price_decimals() {
	$decimals = apply_filters( 'geodir_get_price_decimals', 2 );
	return absint( $decimals );
}

/**
 * Get rounding precision for internal GD calculations.
 * Will increase the precision of geodir_get_price_decimals by 2 decimals, unless GEODIR_ROUNDING_PRECISION is set to a higher number.
 *
 * @since 2.0.0
 * @return int
 */
function geodir_get_rounding_precision() {
	$precision = geodir_get_price_decimals() + 2;
	if ( absint( GEODIR_ROUNDING_PRECISION ) > $precision ) {
		$precision = absint( GEODIR_ROUNDING_PRECISION );
	}
	return $precision;
}

/**
 * Format decimal numbers ready for DB storage.
 *
 * Sanitize, remove decimals, and optionally round + trim off zeros.
 *
 * This function does not remove thousands - this should be done before passing a value to the function.
 *
 * @since 2.0.0
 *
 * @param  float|string $number Expects either a float or a string with a decimal separator only (no thousands)
 * @param  mixed $dp number of decimal points to use, blank to use geodir_get_price_decimals, or false to avoid all rounding.
 * @param  bool $trim_zeros from end of string
 * @return string
 */
function geodir_format_decimal( $number, $dp = false, $trim_zeros = false ) {
	$locale   = localeconv();
	$decimals = array( geodir_get_price_decimal_separator(), $locale['decimal_point'], $locale['mon_decimal_point'] );

	// Remove locale from string.
	if ( ! is_float( $number ) ) {
		$number = str_replace( $decimals, '.', $number );
		$number = preg_replace( '/[^0-9\.,-]/', '', geodir_clean( $number ) );
	}

	if ( false !== $dp ) {
		$dp     = intval( '' == $dp ? geodir_get_price_decimals() : $dp );
		$number = number_format( floatval( $number ), $dp, '.', '' );

	// DP is false - don't use number format, just return a string in our format
	} elseif ( is_float( $number ) ) {
		// DP is false - don't use number format, just return a string using whatever is given. Remove scientific notation using sprintf.
		$number     = str_replace( $decimals, '.', sprintf( '%.' . geodir_get_rounding_precision() . 'f', $number ) );
		// We already had a float, so trailing zeros are not needed.
		$trim_zeros = true;
	}

	if ( $trim_zeros && strstr( $number, '.' ) ) {
		$number = rtrim( rtrim( $number, '0' ), '.' );
	}

	return $number;
}


/**
 * Retrieve the timezone string for a site until.
 *
 * @since 2.0.0
 * @return string PHP timezone string for the site
 */
function geodir_timezone_string() {
	$timezone_string = '';

	$timezones = timezone_identifiers_list();

	$_timezone_string = geodir_get_option( 'default_location_timezone_string' );
	if ( $_timezone_string && in_array( $_timezone_string, $timezones ) ) {
		$timezone_string = $_timezone_string;
	}

	if ( ! $timezone_string && ( $_timezone_string = trim( get_option('timezone_string') ) ) ) {
		if ( in_array( $_timezone_string, $timezones ) ) {
			$timezone_string = $_timezone_string;
		}
	}

	if ( ! $timezone_string ) {
		$timezone_string = 'UTC';
	}

	return apply_filters( 'geodir_timezone_string', $timezone_string );
}

/**
 * Retrieve the timezone offset.
 *
 * @since 2.0.0.96
 * @return string PHP timezone string for the site
 */
function geodir_timezone_utc_offset( $timezone_string = '', $dst = true ) {
	$offset = '';

	if ( empty( $timezone_string ) ) {
		$timezone_string = geodir_timezone_string();
	}

	$data = geodir_timezone_data( $timezone_string );

	$offset = $dst && ! empty( $data['is_dst'] ) ? $data['utc_offset_dst'] : $data['utc_offset'];

	return apply_filters( 'geodir_timezone_utc_offset', $offset, $timezone_string, $dst );
}

/**
 * Get timezone offset in seconds.
 *
 * @since  2.0.0
 * @return float
 */
function geodir_timezone_offset() {
	if ( $timezone = get_option( 'timezone_string' ) ) {
		$timezone_object = new DateTimeZone( $timezone );
		return $timezone_object->getOffset( new DateTime( 'now' ) );
	} else {
		return floatval( get_option( 'gmt_offset', 0 ) ) * HOUR_IN_SECONDS;
	}
}

/**
 * Filters content and keeps only allowable HTML elements.
 *
 * This function makes sure that only the allowed HTML element names, attribute
 * names and attribute values plus only sane HTML entities will occur in
 * $string. You have to remove any slashes from PHP's magic quotes before you
 * call this function.
 *
 * The default allowed protocols are 'http', 'https', 'ftp', 'mailto', 'news',
 * 'irc', 'gopher', 'nntp', 'feed', 'telnet, 'mms', 'rtsp' and 'svn'. This
 * covers all common link protocols, except for 'javascript' which should not
 * be allowed for untrusted users.
 *
 * @since 2.0.0
 *
 * @param string $str               Content to filter through kses
 * @param array  $allowed_html      List of allowed HTML elements. Default NULL.
 * @return string Filtered content with only allowed HTML elements
 */
function geodir_sanitize_html_field( $str, $allowed_html = NULL ) {
	if ( $str === '' ) {
		return $str;
	}

	if ( ! is_array( $allowed_html ) ) {
		$allowed_html = wp_kses_allowed_html( 'post' );
	}

	$filtered = trim( wp_unslash( $str ) );
	$filtered = wp_kses( $filtered, $allowed_html );
	$filtered = balanceTags( $filtered ); // Balances tags

	/**
	 * Filter a sanitized html field string.
	 *
	 * @since 2.0.0
	 *
	 * @param string $filtered The sanitized string.
	 * @param string $str   The string prior to being sanitized.
	 * @param string $allowed_html List of allowed HTML elements.
	 */
	return apply_filters( 'geodir_sanitize_html_field', $filtered, $str, $allowed_html );
}

/**
 * Sanitizes a multiline string from user input or from the database.
 *
 * The function is like sanitize_text_field(), but preserves
 * new lines (\n) and other whitespace, which are legitimate
 * input in textarea elements.
 *
 * @see sanitize_text_field()
 *
 * @since 2.0.0
 *
 * @param string $str String to sanitize.
 * @return string Sanitized string.
 */
function geodir_sanitize_textarea_field( $str ) {
	if ( $str === '' ) {
		return $str;
	}

	$filtered = trim( wp_unslash( $str ) );
	if ( function_exists( 'sanitize_textarea_field' ) ) {
		$filtered = sanitize_textarea_field( $filtered );
	} else {
		$filtered = sanitize_text_field( $filtered );
	}

	/**
	 * Filter a sanitized multiline string.
	 *
	 * @since 2.0.0
	 *
	 * @param string $filtered The sanitized string.
	 * @param string $string   The string prior to being sanitized.
	 */
	return apply_filters( 'geodir_sanitize_textarea_field', $filtered, $str );
}

/**
 * Sanitizes a keyword.
 *
 * @since 2.0.0.82
 *
 * @param string $keyword Keyword to sanitize.
 * @param string $extra Extra parameter. Default empty.
 * @return string Sanitized keyword.
 */
function geodir_sanitize_keyword( $keyword, $extra = '' ) {
	$raw_keyword = $keyword;

	$keyword = trim( $keyword );
	if ( ! $keyword ) {
		return $keyword;
	}

	// Converts a number of HTML entities into their special characters.
	$keyword = stripslashes( wp_specialchars_decode( $keyword, ENT_QUOTES ) );

	// Converts all accent characters to ASCII characters.
	$keyword = remove_accents( $keyword );

	// Properly strip all HTML tags including script and style.
	$keyword = wp_strip_all_tags( $keyword );

	$replacements = geodir_keyword_replacements();
	if ( ! empty( $replacements ) ) {
		$keyword = str_replace( array_keys( $replacements ), array_values( $replacements ), $keyword );
	}

	// Converts string to lower case.
	$keyword = geodir_strtolower( $keyword );

	// Normalize EOL characters and strip duplicate whitespace.
	$keyword = normalize_whitespace( $keyword );

	/**
	 * Filter sanitized keyword.
	 *
	 * @since 2.0.0.82
	 *
	 * @param string $keyword Keyword to sanitize.
	 * @param string $raw_keyword Original keyword to sanitize.
	 * @param string $extra Extra parameter.
	 */
	return apply_filters( 'geodir_sanitize_keyword', $keyword, $raw_keyword, $extra );
}

/**
 * Characters replacements for keyword sanitization.
 *
 * @since 2.0.0.82
 *
 * @return array Characters replacements.
 */
function geodir_keyword_replacements() {
	//^*=;:
	$replacements = array(
		'‘'       => '',
		'’'       => '',
		"'"       => '',
		'"'       => '',
		'”'       => '',
		'“'       => '',
		'„'       => '',
		'´'       => '',
		'`'       => '',
		'!'       => '',
		'?'       => '',
		'|'       => '',
		'&#038;'  => '',
		'&#8217;' => '',
		'&amp;'   => ' ',
		'&shy;'   => ' ',
		'&nbsp;'  => ' ',
		'@'       => ' ',
		'€'       => ' ',
		'®'       => ' ',
		'©'       => ' ',
		'™'       => ' ',
		'×'       => ' ',
		'~'       => ' ',
		'…'       => ' ',
		'-'       => ' ',
		'–'       => ' ',
		'—'       => ' ',
		'('       => ' ',
		')'       => ' ',
		'{'       => ' ',
		'}'       => ' ',
		'['       => ' ',
		']'       => ' ',
		'+'       => ' ',
		","       => ' ',
		"^"       => ' ',
		"="       => ' ',
		//'&'       => ' ',
		//'#'       => ' ',
		//'$'       => ' ',
		//'%'       => ' ',
		//'%'       => '*',
		//';'       => '',
		//':'       => '',
		//'/'       => '',
		//'\\'      => '',
		//'<'       => '',
		//'>'       => '',
	);

	/**
	 * Filter characters replacements for keyword sanitization.
	 *
	 * @since 2.0.0.82
	 *
	 * @param array $replacements Characters replacements.
	 */
	return apply_filters( 'geodir_keyword_replacements', $replacements );
}

/**
 * Strip block content to shortcodes only.
 *
 * @param $content
 * @since 2.1
 *
 * @return mixed
 */
function geodir_blocks_to_shortcodes($content){
	return preg_replace('/\n(\s*\n)+/', "\n",wp_strip_all_tags( $content ) );
}