<?php
/**
 * Formatter Service
 *
 * Handles data formatting, sanitization, cleaning, and transformation.
 *
 * @package GeoDirectory\Core
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core;

/**
 * Formatter class for data sanitization and formatting.
 */
final class Formatter {

	/**
	 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
	 * Non-scalar values are ignored.
	 *
	 * @since 3.0.0
	 *
	 * @param string|array $var Variable to clean.
	 * @return string|array Cleaned variable.
	 */
	public function clean( $var ) {
		if ( is_array( $var ) ) {
			return array_map( array( $this, 'clean' ), $var );
		} else {
			return is_scalar( $var ) ? $this->sanitize_text_field( $var ) : $var;
		}
	}

	/**
	 * Emulate the WP native sanitize_text_field function in a %%variable%% safe way.
	 *
	 * @since 3.0.0
	 *
	 * @param string $value String value to sanitize.
	 * @return string Sanitized string.
	 */
	public function sanitize_text_field( string $value ): string {
		$filtered = wp_check_invalid_utf8( $value );

		if ( strpos( $filtered, '<' ) !== false ) {
			$filtered = wp_pre_kses_less_than( $filtered );
			// This will strip extra whitespace for us.
			$filtered = wp_strip_all_tags( $filtered, true );
		} else {
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

		return apply_filters( 'sanitize_text_field', $filtered, $value );
	}

	/**
	 * Clean variables for slug usage.
	 *
	 * This function is used to create posttype, posts, taxonomy and terms slug.
	 *
	 * @since 3.0.0
	 *
	 * @param string $string The variable to clean.
	 * @return string Cleaned variable.
	 */
	public function clean_slug( string $string ): string {
		$string = trim( strip_tags( stripslashes( $string ) ) );
		$string = str_replace( " ", "-", $string ); // Replaces all spaces with hyphens.
		$string = preg_replace( '/[^A-Za-z0-9\-\_]/', '', $string ); // Removes special chars.
		$string = preg_replace( '/-+/', '-', $string ); // Replaces multiple hyphens with single one.

		return $string;
	}

	/**
	 * Sanitize a string destined to be a tooltip.
	 *
	 * @since 3.0.0
	 *
	 * @param string $var Tooltip content.
	 * @return string Sanitized tooltip.
	 */
	public function sanitize_tooltip( string $var ): string {
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
	 * @since 3.0.0
	 *
	 * @param string $date Date in format: 'Y-m-d H:i:s'.
	 * @return string|int|false The formatted date.
	 */
	public function get_formatted_date( string $date ) {
		return mysql2date( get_option( 'date_format' ), $date );
	}

	/**
	 * Return the formatted time.
	 *
	 * @since 3.0.0
	 *
	 * @param string $time Time in format: 'Y-m-d H:i:s'.
	 * @return string|int|false The formatted time.
	 */
	public function get_formatted_time( string $time ) {
		return mysql2date( get_option( 'time_format' ), $time, true );
	}

	/**
	 * GeoDirectory Date Format.
	 *
	 * @since 3.0.0
	 *
	 * @return string Date format.
	 */
	public function date_format(): string {
		$date_format = get_option( 'date_format' );
		if ( empty( $date_format ) ) {
			$date_format = 'F j, Y';
		}
		return apply_filters( 'geodir_date_format', $date_format );
	}

	/**
	 * GeoDirectory Time Format.
	 *
	 * @since 3.0.0
	 *
	 * @return string Time format.
	 */
	public function time_format(): string {
		$time_format = get_option( 'time_format' );
		if ( empty( $time_format ) ) {
			$time_format = 'g:i a';
		}
		return apply_filters( 'geodir_time_format', $time_format );
	}

	/**
	 * GeoDirectory Date Time Format.
	 *
	 * @since 3.0.0
	 *
	 * @param string|bool|null $sep Separator. Default null.
	 * @return string Date time format.
	 */
	public function date_time_format( $sep = null ): string {
		if ( $sep === null || $sep === false ) {
			$sep = ' ';
		}

		$date_time_format = $this->date_format() . $sep . $this->time_format();

		return apply_filters( 'geodir_date_time_format', $date_time_format, $sep );
	}

	/**
	 * let_to_num function.
	 *
	 * This function transforms the php.ini notation for numbers (like '2M') to an integer.
	 *
	 * @since 3.0.0
	 *
	 * @param string $size Size string.
	 * @return int Size in bytes.
	 */
	public function let_to_num( string $size ): int {
		$l   = substr( $size, -1 );
		$ret = (int) substr( $size, 0, -1 );
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
	 *
	 * @since 3.0.0
	 *
	 * @return string Thousand separator.
	 */
	public function get_price_thousand_separator(): string {
		$separator = apply_filters( 'geodir_get_price_thousand_separator', ',' );
		return stripslashes( $separator );
	}

	/**
	 * Return the decimal separator for prices.
	 *
	 * @since 3.0.0
	 *
	 * @return string Decimal separator.
	 */
	public function get_price_decimal_separator(): string {
		$separator = apply_filters( 'geodir_get_price_decimal_separator', '.' );
		return $separator ? stripslashes( $separator ) : '.';
	}

	/**
	 * Return the number of decimals after the decimal point.
	 *
	 * @since 3.0.0
	 *
	 * @return int Number of decimals.
	 */
	public function get_price_decimals(): int {
		$decimals = apply_filters( 'geodir_get_price_decimals', 2 );
		return absint( $decimals );
	}

	/**
	 * Get rounding precision for internal GD calculations.
	 *
	 * @since 3.0.0
	 *
	 * @return int Rounding precision.
	 */
	public function get_rounding_precision(): int {
		$precision = $this->get_price_decimals() + 2;
		if ( absint( GEODIR_ROUNDING_PRECISION ) > $precision ) {
			$precision = absint( GEODIR_ROUNDING_PRECISION );
		}
		return $precision;
	}

	/**
	 * Format decimal numbers ready for DB storage.
	 *
	 * @since 3.0.0
	 *
	 * @param float|string $number Number to format.
	 * @param mixed $dp Number of decimal points, blank to use get_price_decimals, or false to avoid all rounding.
	 * @param bool $trim_zeros Trim zeros from end of string.
	 * @return string Formatted number.
	 */
	public function format_decimal( $number, $dp = false, bool $trim_zeros = false ): string {
		$locale   = localeconv();
		$decimals = array( $this->get_price_decimal_separator(), $locale['decimal_point'], $locale['mon_decimal_point'] );

		// Remove locale from string.
		if ( ! is_float( $number ) ) {
			$number = str_replace( $decimals, '.', $number );
			$number = preg_replace( '/[^0-9\.,-]/', '', $this->clean( $number ) );
		}

		if ( false !== $dp ) {
			$dp     = intval( '' == $dp ? $this->get_price_decimals() : $dp );
			$number = number_format( floatval( $number ), $dp, '.', '' );

		// DP is false - don't use number format, just return a string in our format
		} elseif ( is_float( $number ) ) {
			// DP is false - don't use number format, just return a string using whatever is given. Remove scientific notation using sprintf.
			$number     = str_replace( $decimals, '.', sprintf( '%.' . $this->get_rounding_precision() . 'f', $number ) );
			// We already had a float, so trailing zeros are not needed.
			$trim_zeros = true;
		}

		if ( $trim_zeros && strstr( $number, '.' ) ) {
			$number = rtrim( rtrim( $number, '0' ), '.' );
		}

		return $number;
	}

	/**
	 * Retrieve the timezone string for a site.
	 *
	 * @since 3.0.0
	 *
	 * @return string PHP timezone string for the site.
	 */
	public function timezone_string(): string {
		$timezone_string = '';

		$timezones = timezone_identifiers_list();

		$_timezone_string = geodir_get_option( 'default_location_timezone_string' );
		if ( $_timezone_string && in_array( $_timezone_string, $timezones ) ) {
			$timezone_string = $_timezone_string;
		}

		if ( ! $timezone_string && ( $_timezone_string = trim( get_option( 'timezone_string' ) ) ) ) {
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
	 * @since 3.0.0
	 *
	 * @param string $timezone_string Timezone string.
	 * @param bool $dst Include DST offset.
	 * @return string Timezone offset.
	 */
	public function timezone_utc_offset( string $timezone_string = '', bool $dst = true ): string {
		$offset = '';

		if ( empty( $timezone_string ) ) {
			$timezone_string = $this->timezone_string();
		}

		$data = geodir_timezone_data( $timezone_string );

		$offset = $dst && ! empty( $data['is_dst'] ) ? $data['utc_offset_dst'] : $data['utc_offset'];

		return apply_filters( 'geodir_timezone_utc_offset', $offset, $timezone_string, $dst );
	}

	/**
	 * Get timezone offset in seconds.
	 *
	 * @since 3.0.0
	 *
	 * @return float Timezone offset in seconds.
	 */
	public function timezone_offset(): float {
		if ( $timezone = get_option( 'timezone_string' ) ) {
			$timezone_object = new \DateTimeZone( $timezone );
			return (float) $timezone_object->getOffset( new \DateTime( 'now' ) );
		} else {
			return geodir_sanitize_float( get_option( 'gmt_offset', 0 ) ) * HOUR_IN_SECONDS;
		}
	}

	/**
	 * Filters content and keeps only allowable HTML elements.
	 *
	 * @since 3.0.0
	 *
	 * @param string $str Content to filter through kses.
	 * @param array|null $allowed_html List of allowed HTML elements. Default NULL.
	 * @return string Filtered content with only allowed HTML elements.
	 */
	public function sanitize_html_field( string $str, ?array $allowed_html = null ): string {
		if ( $str === '' ) {
			return $str;
		}

		if ( ! is_array( $allowed_html ) ) {
			$allowed_html = wp_kses_allowed_html( 'post' );
		}

		$filtered = trim( wp_unslash( $str ) );
		$filtered = wp_kses( $filtered, $allowed_html );
		$filtered = balanceTags( $filtered ); // Balances tags

		return apply_filters( 'geodir_sanitize_html_field', $filtered, $str, $allowed_html );
	}

	/**
	 * Sanitizes a multiline string from user input or from the database.
	 *
	 * @since 3.0.0
	 *
	 * @param string $str String to sanitize.
	 * @return string Sanitized string.
	 */
	public function sanitize_textarea_field( string $str ): string {
		if ( $str === '' ) {
			return $str;
		}

		$filtered = trim( wp_unslash( $str ) );
		if ( function_exists( 'sanitize_textarea_field' ) ) {
			$filtered = sanitize_textarea_field( $filtered );
		} else {
			$filtered = sanitize_text_field( $filtered );
		}

		return apply_filters( 'geodir_sanitize_textarea_field', $filtered, $str );
	}

	/**
	 * Strip shortcodes/blocks from content.
	 *
	 * @since 3.0.0
	 *
	 * @param string $content Content to sanitize.
	 * @return string Sanitized Content.
	 */
	public function strip_shortcodes( string $content ): string {
		if ( empty( $content ) ) {
			return $content;
		}

		if ( ! ( str_contains( $content, '[' ) && str_contains( $content, ']' ) ) ) {
			return $content;
		}

		$strip = true;

		if ( is_user_logged_in() ) {
			if ( current_user_can( 'manage_options' ) ) {
				$strip = false;
			} else {
				$roles = wp_get_current_user()->roles;

				if ( ! empty( $roles ) && is_array( $roles ) ) {
					$allowed_roles = geodir_get_option( 'shortcodes_allowed_roles', array( 'administrator' ) );

					if ( empty( $allowed_roles ) || ! is_array( $allowed_roles ) ) {
						$allowed_roles = array();
					}

					$allowed_roles[] = 'administrator'; // Admin always allowed to use shortcodes in description.

					foreach ( $roles as $role ) {
						if ( in_array( $role, $allowed_roles ) ) {
							$strip = false;
							break;
						}
					}
				}
			}
		}

		if ( $strip ) {
			// Strip shortcodes.
			$filtered = trim( strip_shortcodes( $content ) );
		} else {
			$filtered = $content;
		}

		return apply_filters( 'geodir_strip_shortcodes', $filtered, $strip, $content );
	}

	/**
	 * Sanitizes a keyword.
	 *
	 * @since 3.0.0
	 *
	 * @param string $keyword Keyword to sanitize.
	 * @param string $extra Extra parameter. Default empty.
	 * @return string Sanitized keyword.
	 */
	public function sanitize_keyword( string $keyword, string $extra = '' ): string {
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

		$replacements = $this->keyword_replacements();
		if ( ! empty( $replacements ) ) {
			$keyword = str_replace( array_keys( $replacements ), array_values( $replacements ), $keyword );
		}

		// Converts string to lower case.
		$keyword = geodir_strtolower( $keyword );

		// Normalize EOL characters and strip duplicate whitespace.
		$keyword = normalize_whitespace( $keyword );

		return apply_filters( 'geodir_sanitize_keyword', $keyword, $raw_keyword, $extra );
	}

	/**
	 * Characters replacements for keyword sanitization.
	 *
	 * @since 3.0.0
	 *
	 * @return array Characters replacements.
	 */
	public function keyword_replacements(): array {
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
		);

		return apply_filters( 'geodir_keyword_replacements', $replacements );
	}

	/**
	 * Strip block content to shortcodes only.
	 *
	 * @since 3.0.0
	 *
	 * @param string $content Content to strip.
	 * @return string Stripped content.
	 */
	public function blocks_to_shortcodes( string $content ): string {
		return preg_replace( '/\n(\s*\n)+/', "\n", wp_strip_all_tags( $content ) );
	}

	/**
	 * Replaces some entities formatted back to normal.
	 *
	 * @since 3.0.0
	 *
	 * @param string $text The text to be formatted.
	 * @return string The string replaced with HTML entities.
	 */
	public function untexturize( string $text ): string {
		$orig_text = $text;

		$replacements = array(
			'&#038;'  => '&',
		);

		$replacements = apply_filters( 'geodir_untexturize_replacements', $replacements, $text );

		$text = str_replace( array_keys( $replacements ), array_values( $replacements ), $text );

		return apply_filters( 'geodir_untexturize', $text, $orig_text );
	}

	/**
	 * Replaces formatted entities with back to common plain text characters.
	 *
	 * @since 3.0.0
	 *
	 * @param string $text The text to be formatted.
	 * @return string The string replaced with HTML entities.
	 */
	public function unwptexturize( string $text ): string {
		if ( $text == '' ) {
			return $text;
		}

		$replacements = array(
			'“'       => '"', // left double curly quotation mark
			'”'       => '"', // right double curly quotation mark
			'‘'       => "'", // left double curly quotation mark
			'’'       => "'", // right double curly quotation mark
			'&#8216;' => "'", // left single quotation mark
			'&#8217;' => "'", // right single quotation mark
			'&#8218;' => "'", // single low 9 quotation mark
			'&#8220;' => '"', // left double quotation mark
			'&#8221;' => '"', // right double quotation mark
			'&#8222;' => '"', // double low 9 quotation mark
			'&#8242;' => "'", // prime mark
			'&#8243;' => '"', // double prime mark
		);

		$replacements = apply_filters( 'geodir_unwptexturize_replacements', $replacements, $text );

		$text = str_replace( array_keys( $replacements ), array_values( $replacements ), $text );

		return $text;
	}

	/**
	 * Sanitize float value.
	 *
	 * @since 3.0.0
	 *
	 * @param float|string $number Number value.
	 * @return float Sanitized number.
	 */
	public function sanitize_float( $number ): float {
		$locale = localeconv();

		// Keep as string initially for string replacement
		$number = (string) $number;

		// Replace comma to decimal for some locale with decimal_point as a comma.
		if ( ! empty( $locale['decimal_point'] ) ) {
			$number = str_replace( $locale['decimal_point'], ".", $number );
		}

		return floatval( $number );
	}

	/**
	 * Emulate the WP native sanitize_html_class to sanitize css class.
	 *
	 * @since 3.0.0
	 *
	 * @param string|array $string String or array of strings to sanitize.
	 * @return string Sanitized string.
	 */
	public function sanitize_html_class( $string ): string {
		if ( empty( $string ) ) {
			return $string;
		}

		if ( ! is_array( $string ) ) {
			$string = explode( ' ', $string );
		}

		$string = array_filter( array_map( 'sanitize_html_class', $string ) );
		$string = trim( implode( ' ', $string ) );

		return $string;
	}

	/**
	 * JavaScript Minifier.
	 *
	 * @since 3.0.0
	 *
	 * @param string $script Input JavaScript.
	 * @return string Minified JavaScript.
	 */
	public function minify_js( string $script ): string {
		if ( trim( $script ) === "" ) {
			return $script;
		}

		$script = preg_replace(
			array(
				// Remove comment(s)
				'#\s*("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')\s*|\s*\/\*(?!\!|@cc_on)(?>[\s\S]*?\*\/)\s*|\s*(?<![\:\=])\/\/.*(?=[\n\r]|$)|^\s*|\s*$#',
				// Remove white-space(s) outside the string and regex
				'#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/)|\/(?!\/)[^\n\r]*?\/(?=[\s.,;]|[gimuy]|$))|\s*([!%&*\(\)\-=+\[\]\{\}|;:,.<>?\/])\s*#s',
				// Remove the last semicolon
				'#;+\}#',
				// Minify object attribute(s) except JSON attribute(s). From `{'foo':'bar'}` to `{foo:'bar'}`
				'#([\{,])([\'])(\d+|[a-z_][a-z0-9_]*)\2(?=\:)#i',
				// --ibid. From `foo['bar']` to `foo.bar`
				'#([a-z0-9_\)\]])\[([\'"])([a-z_][a-z0-9_]*)\2\]#i'
			),
			array(
				'$1',
				'$1$2',
				'}',
				'$1$3',
				'$1.$3'
			),
			$script
		);

		return $script;
	}
}
