<?php
/**
 * GeoDirectory Formatting
 *
 * Functions for formatting data.
 * These functions act as thin wrappers around the Formatter class.
 * For direct access, use: geodirectory()->formatter
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
 * Stripslashes custom field data.
 *
 * @since 2.3.110
 *
 * @param array|object $data Field data.
 * @return array|object Field after stripslashes.
 */
function geodir_stripslashes_field( $data ) {
	if ( empty( $data ) ) {
		return $data;
	}

	$_data = $data;

	$data = stripslashes_deep( $data );

	// Don't apply stripslashes to extra_fields.
	if ( is_array( $_data ) ) {
		if ( ! empty( $_data['extra_fields'] ) && is_serialized( $_data['extra_fields'] ) ) {
			$data['extra_fields'] = $_data['extra_fields'];
		}
	} else if ( is_object( $_data ) ) {
		if ( ! empty( $_data->extra_fields ) && is_serialized( $_data->extra_fields ) ) {
			$data->extra_fields = $_data->extra_fields;
		}
	}

	return $data;
}

/**
 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
 * Non-scalar values are ignored.
 *
 * @param string|array $var
 * @return string|array
 */
function geodir_clean( $var ) {
	return geodirectory()->formatter->clean( $var );
}

/**
 * Emulate the WP native sanitize_text_field function in a %%variable%% safe way.
 *
 * @since 2.0.0
 *
 * @param string $value String value to sanitize.
 * @return string
 */
function geodir_sanitize_text_field( $value ) {
	return geodirectory()->formatter->sanitize_text_field( $value );
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
 * @return string Cleaned variable.
 */
function geodir_clean_slug( $string ) {
	return geodirectory()->formatter->clean_slug( $string );
}

/**
 * Sanitize a string destined to be a tooltip.
 *
 * @since 2.0.0 Tooltips are encoded with htmlspecialchars to prevent XSS. Should not be used in conjunction with esc_attr()
 * @param string $var
 * @return string
 */
function geodir_sanitize_tooltip( $var ) {
	return geodirectory()->formatter->sanitize_tooltip( $var );
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
 * @return bool|int|string the formatted date.
 */
function geodir_get_formated_date( $date ) {
	return geodirectory()->formatter->get_formatted_date( $date );
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
 * @return bool|int|string the formatted time.
 */
function geodir_get_formated_time( $time ) {
	return geodirectory()->formatter->get_formatted_time( $time );
}

/**
 * GeoDirectory Date Format.
 *
 * @since 2.0.0
 *
 * @return string
 */
function geodir_date_format() {
	return geodirectory()->formatter->date_format();
}

/**
 * GeoDirectory Time Format.
 *
 * @since 2.0.0
 *
 * @return string
 */
function geodir_time_format() {
	return geodirectory()->formatter->time_format();
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
	return geodirectory()->formatter->date_time_format( $sep );
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
	return geodirectory()->formatter->let_to_num( $size );
}

/**
 * Return the thousand separator for prices.
 * @since  2.0.0
 * @return string
 */
function geodir_get_price_thousand_separator() {
	return geodirectory()->formatter->get_price_thousand_separator();
}

/**
 * Return the decimal separator for prices.
 * @since  2.0.0
 * @return string
 */
function geodir_get_price_decimal_separator() {
	return geodirectory()->formatter->get_price_decimal_separator();
}

/**
 * Return the number of decimals after the decimal point.
 * @since  2.0.0
 * @return int
 */
function geodir_get_price_decimals() {
	return geodirectory()->formatter->get_price_decimals();
}

/**
 * Get rounding precision for internal GD calculations.
 * Will increase the precision of geodir_get_price_decimals by 2 decimals, unless GEODIR_ROUNDING_PRECISION is set to a higher number.
 *
 * @since 2.0.0
 * @return int
 */
function geodir_get_rounding_precision() {
	return geodirectory()->formatter->get_rounding_precision();
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
	return geodirectory()->formatter->format_decimal( $number, $dp, $trim_zeros );
}

/**
 * Retrieve the timezone string for a site until.
 *
 * @since 2.0.0
 * @return string PHP timezone string for the site
 */
function geodir_timezone_string() {
	return geodirectory()->formatter->timezone_string();
}

/**
 * Retrieve the timezone offset.
 *
 * @since 2.0.0.96
 * @return string PHP timezone string for the site
 */
function geodir_timezone_utc_offset( $timezone_string = '', $dst = true ) {
	return geodirectory()->formatter->timezone_utc_offset( $timezone_string, $dst );
}

/**
 * Get timezone offset in seconds.
 *
 * @since  2.0.0
 * @return float
 */
function geodir_timezone_offset() {
	return geodirectory()->formatter->timezone_offset();
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
	return geodirectory()->formatter->sanitize_html_field( $str, $allowed_html );
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
	return geodirectory()->formatter->sanitize_textarea_field( $str );
}

/**
 * Strip shortcodes/blocks from content.
 *
 * @since 2.8.120
 *
 * @param string $content Content to sanitize.
 * @return string Sanitized Content.
 */
function geodir_strip_shortcodes( $content ) {
	return geodirectory()->formatter->strip_shortcodes( $content );
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
	return geodirectory()->formatter->sanitize_keyword( $keyword, $extra );
}

/**
 * Characters replacements for keyword sanitization.
 *
 * @since 2.0.0.82
 *
 * @return array Characters replacements.
 */
function geodir_keyword_replacements() {
	return geodirectory()->formatter->keyword_replacements();
}

/**
 * Strip block content to shortcodes only.
 *
 * @param $content
 * @since 2.1
 *
 * @return mixed
 */
function geodir_blocks_to_shortcodes( $content ) {
	return geodirectory()->formatter->blocks_to_shortcodes( $content );
}

/**
 * Replaces some entities formatted back to normal.
 *
 * This reverses some entities formatted by wptexturize().
 * Ex: replace "&#038;" to "&".
 *
 * @param string $text  The text to be formatted.
 * @return string The string replaced with HTML entities.
 */
function geodir_untexturize( $text ) {
	return geodirectory()->formatter->untexturize( $text );
}

/**
 * Replaces formatted entities with back to common plain text characters .
 *
 * As an example,
 *
 *    &#8217;cause today&#8217;s effort makes it worth tomorrow&#8217;s &#8220;holiday&#8221; &#8230;
 *
 * Becomes:
 *
 *    'cause today's effort makes it worth tomorrow's "holiday" ...
 *
 * @since 2.1.1.9
 *
 * @param string $text  The text to be formatted.
 * @return string The string replaced with HTML entities.
 */
function geodir_unwptexturize( $text ) {
	return geodirectory()->formatter->unwptexturize( $text );
}

/**
 * Sanitize float value.
 *
 * @since 2.2.6
 *
 * @param float Number value.
 * @return float Sanitized number.
 */
function geodir_sanitize_float( $number ) {
	return geodirectory()->formatter->sanitize_float( $number );
}

/**
 * Emulate the WP native sanitize_html_class to sanitize css class.
 *
 * @since 2.2.22
 *
 * @param string $value String value to sanitize.
 * @return string
 */
function geodir_sanitize_html_class( $string ) {
	return geodirectory()->formatter->sanitize_html_class( $string );
}

/**
 * JavaScript Minifier.
 *
 * @since 2.3.71
 *
 * @param string $script Input JavaScript.
 * @return string Minified JavaScript.
 */
function geodir_minify_js( $script ) {
	return geodirectory()->formatter->minify_js( $script );
}
