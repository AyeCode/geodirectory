<?php
/**
 * Helper Utilities
 *
 * Common helper functions for strings, colors, URLs, and misc utilities.
 *
 * @package GeoDirectory\Core\Utils
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core\Utils;

/**
 * Helpers utility class with static methods.
 */
final class Helpers {

	/**
	 * Convert string to uppercase (multibyte safe).
	 *
	 * @since 3.0.0
	 *
	 * @param string $string String to convert.
	 * @param string $charset Character encoding.
	 * @return string Uppercase string.
	 */
	public static function strtoupper( string $string, string $charset = 'UTF-8' ): string {
		if ( function_exists( 'mb_strtoupper' ) ) {
			return mb_strtoupper( $string, $charset );
		}
		return strtoupper( $string );
	}

	/**
	 * Convert string to lowercase (multibyte safe).
	 *
	 * @since 3.0.0
	 *
	 * @param string $string String to convert.
	 * @param string $charset Character encoding.
	 * @return string Lowercase string.
	 */
	public static function strtolower( string $string, string $charset = 'UTF-8' ): string {
		if ( function_exists( 'mb_strtolower' ) ) {
			return mb_strtolower( $string, $charset );
		}
		return strtolower( $string );
	}

	/**
	 * Convert string to title case (multibyte safe).
	 *
	 * @since 3.0.0
	 *
	 * @param string $string String to convert.
	 * @param string $charset Character encoding.
	 * @return string Title case string.
	 */
	public static function ucwords( string $string, string $charset = 'UTF-8' ): string {
		if ( function_exists( 'mb_convert_case' ) ) {
			return mb_convert_case( $string, MB_CASE_TITLE, $charset );
		}
		return ucwords( $string );
	}

	/**
	 * Get UTF-8 string length.
	 *
	 * @since 3.0.0
	 *
	 * @param string $str String.
	 * @param string $encoding Character encoding.
	 * @return int String length.
	 */
	public static function utf8_strlen( string $str, string $encoding = 'UTF-8' ): int {
		if ( function_exists( 'mb_strlen' ) ) {
			return mb_strlen( $str, $encoding );
		}
		return strlen( $str );
	}

	/**
	 * Get UTF-8 substring.
	 *
	 * @since 3.0.0
	 *
	 * @param string $str String.
	 * @param int $start Start position.
	 * @param int|null $length Length.
	 * @param string $encoding Character encoding.
	 * @return string Substring.
	 */
	public static function utf8_substr( string $str, int $start, ?int $length = null, string $encoding = 'UTF-8' ): string {
		if ( function_exists( 'mb_substr' ) ) {
			return mb_substr( $str, $start, $length, $encoding );
		}
		return $length === null ? substr( $str, $start ) : substr( $str, $start, $length );
	}

	/**
	 * Find position of first occurrence in UTF-8 string.
	 *
	 * @since 3.0.0
	 *
	 * @param string $str Haystack.
	 * @param string $find Needle.
	 * @param int $offset Start offset.
	 * @param string $encoding Character encoding.
	 * @return int|false Position or false.
	 */
	public static function utf8_strpos( string $str, string $find, int $offset = 0, string $encoding = 'UTF-8' ) {
		if ( function_exists( 'mb_strpos' ) ) {
			return mb_strpos( $str, $find, $offset, $encoding );
		}
		return strpos( $str, $find, $offset );
	}

	/**
	 * Find position of last occurrence in UTF-8 string.
	 *
	 * @since 3.0.0
	 *
	 * @param string $str Haystack.
	 * @param string $find Needle.
	 * @param int $offset Start offset.
	 * @param string $encoding Character encoding.
	 * @return int|false Position or false.
	 */
	public static function utf8_strrpos( string $str, string $find, int $offset = 0, string $encoding = 'UTF-8' ) {
		if ( function_exists( 'mb_strrpos' ) ) {
			return mb_strrpos( $str, $find, $offset, $encoding );
		}
		return strrpos( $str, $find, $offset );
	}

	/**
	 * UTF-8 first character uppercase.
	 *
	 * @since 3.0.0
	 *
	 * @param string $str String.
	 * @param bool $lower_str_end Lowercase rest of string.
	 * @param string $encoding Character encoding.
	 * @return string Modified string.
	 */
	public static function utf8_ucfirst( string $str, bool $lower_str_end = false, string $encoding = 'UTF-8' ): string {
		if ( empty( $str ) ) {
			return $str;
		}

		$first_char = self::utf8_substr( $str, 0, 1, $encoding );
		$rest = self::utf8_substr( $str, 1, null, $encoding );

		if ( $lower_str_end ) {
			$rest = self::strtolower( $rest, $encoding );
		}

		return self::strtoupper( $first_char, $encoding ) . $rest;
	}

	/**
	 * Get IP address.
	 *
	 * @since 3.0.0
	 *
	 * @return string IP address.
	 */
	public static function get_ip(): string {
		$ip = '';

		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		// Clean up the IP
		$ip = sanitize_text_field( $ip );

		// Handle comma-separated IPs (from proxies)
		if ( strpos( $ip, ',' ) !== false ) {
			$ip_array = explode( ',', $ip );
			$ip = trim( $ip_array[0] );
		}

		/**
		 * Filters the detected IP address.
		 *
		 * @since 2.0.0
		 *
		 * @param string $ip IP address.
		 */
		return apply_filters( 'geodir_get_ip', $ip );
	}

	/**
	 * Convert hex color to RGB.
	 *
	 * @since 3.0.0
	 *
	 * @param string $color Hex color.
	 * @return array RGB values.
	 */
	public static function rgb_from_hex( string $color ): array {
		$color = str_replace( '#', '', $color );

		if ( strlen( $color ) === 3 ) {
			$r = hexdec( substr( $color, 0, 1 ) . substr( $color, 0, 1 ) );
			$g = hexdec( substr( $color, 1, 1 ) . substr( $color, 1, 1 ) );
			$b = hexdec( substr( $color, 2, 1 ) . substr( $color, 2, 1 ) );
		} else {
			$r = hexdec( substr( $color, 0, 2 ) );
			$g = hexdec( substr( $color, 2, 2 ) );
			$b = hexdec( substr( $color, 4, 2 ) );
		}

		return array( 'r' => $r, 'g' => $g, 'b' => $b );
	}

	/**
	 * Make hex color darker.
	 *
	 * @since 3.0.0
	 *
	 * @param string $color Hex color.
	 * @param int $factor Darkening factor (0-100).
	 * @return string Darker hex color.
	 */
	public static function hex_darker( string $color, int $factor = 30 ): string {
		$color = self::format_hex( $color );
		$rgb = self::rgb_from_hex( $color );

		$r = max( 0, $rgb['r'] - ( $rgb['r'] * $factor / 100 ) );
		$g = max( 0, $rgb['g'] - ( $rgb['g'] * $factor / 100 ) );
		$b = max( 0, $rgb['b'] - ( $rgb['b'] * $factor / 100 ) );

		return sprintf( '#%02x%02x%02x', $r, $g, $b );
	}

	/**
	 * Make hex color lighter.
	 *
	 * @since 3.0.0
	 *
	 * @param string $color Hex color.
	 * @param int $factor Lightening factor (0-100).
	 * @return string Lighter hex color.
	 */
	public static function hex_lighter( string $color, int $factor = 30 ): string {
		$color = self::format_hex( $color );
		$rgb = self::rgb_from_hex( $color );

		$r = min( 255, $rgb['r'] + ( ( 255 - $rgb['r'] ) * $factor / 100 ) );
		$g = min( 255, $rgb['g'] + ( ( 255 - $rgb['g'] ) * $factor / 100 ) );
		$b = min( 255, $rgb['b'] + ( ( 255 - $rgb['b'] ) * $factor / 100 ) );

		return sprintf( '#%02x%02x%02x', $r, $g, $b );
	}

	/**
	 * Determine if color is light or dark.
	 *
	 * @since 3.0.0
	 *
	 * @param string $color Hex color.
	 * @param string $dark Color to return if dark.
	 * @param string $light Color to return if light.
	 * @return string Dark or light color.
	 */
	public static function light_or_dark( string $color, string $dark = '#000000', string $light = '#FFFFFF' ): string {
		$color = self::format_hex( $color );
		$rgb = self::rgb_from_hex( $color );

		// Calculate brightness
		$brightness = ( ( $rgb['r'] * 299 ) + ( $rgb['g'] * 587 ) + ( $rgb['b'] * 114 ) ) / 1000;

		return $brightness > 155 ? $dark : $light;
	}

	/**
	 * Format hex color.
	 *
	 * @since 3.0.0
	 *
	 * @param string $hex Hex color.
	 * @return string Formatted hex color.
	 */
	public static function format_hex( string $hex ): string {
		$hex = str_replace( '#', '', $hex );

		if ( strlen( $hex ) === 3 ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}

		return '#' . $hex;
	}

	/**
	 * Check if URL is a full URL.
	 *
	 * @since 3.0.0
	 *
	 * @param string $url URL to check.
	 * @return bool True if full URL.
	 */
	public static function is_full_url( string $url ): bool {
		if ( empty( $url ) ) {
			return false;
		}

		return preg_match( '/^https?:\/\//', $url ) === 1;
	}

	/**
	 * Check if request URI contains a string.
	 *
	 * @since 3.0.0
	 *
	 * @param string $match String to match.
	 * @return bool True if matches.
	 */
	public static function has_request_uri( string $match ): bool {
		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return false;
		}

		return strpos( $_SERVER['REQUEST_URI'], $match ) !== false;
	}

	/**
	 * Generate random float.
	 *
	 * @since 3.0.0
	 *
	 * @param float $min Minimum value.
	 * @param float $max Maximum value.
	 * @return float Random float.
	 */
	public static function random_float( float $min = 0, float $max = 1 ): float {
		return $min + mt_rand() / mt_getrandmax() * ( $max - $min );
	}

	/**
	 * Get PHP arg separator for output.
	 *
	 * @since 3.0.0
	 *
	 * @return string Arg separator.
	 */
	public static function get_php_arg_separator_output(): string {
		return ini_get( 'arg_separator.output' );
	}

	/**
	 * Check if file is an image.
	 *
	 * @since 3.0.0
	 *
	 * @param string $url File URL.
	 * @return bool True if image.
	 */
	public static function is_image_file( string $url ): bool {
		if ( empty( $url ) ) {
			return false;
		}

		$image_extensions = array( 'jpg', 'jpeg', 'jpe', 'gif', 'png', 'bmp', 'tif', 'tiff', 'ico', 'webp', 'svg' );
		$ext = strtolower( pathinfo( $url, PATHINFO_EXTENSION ) );

		return in_array( $ext, $image_extensions );
	}

	/**
	 * Escape CSV data.
	 *
	 * @since 3.0.0
	 *
	 * @param string $data CSV data.
	 * @return string Escaped data.
	 */
	public static function escape_csv_data( string $data ): string {
		// Escape formula injection
		$triggers = array( '=', '+', '-', '@', "\t", "\r" );

		if ( in_array( substr( $data, 0, 1 ), $triggers, true ) ) {
			$data = "'" . $data;
		}

		return $data;
	}

	/**
	 * Format CSV data.
	 *
	 * @since 3.0.0
	 *
	 * @param string $data CSV data.
	 * @return string Formatted data.
	 */
	public static function format_csv_data( string $data ): string {
		$data = self::escape_csv_data( $data );

		// Remove HTML tags
		$data = wp_strip_all_tags( $data );

		// Remove line breaks
		$data = str_replace( array( "\r\n", "\r", "\n" ), ' ', $data );

		return $data;
	}

	/**
	 * Remove last word from text.
	 *
	 * @since 3.0.0
	 *
	 * @param string $text Text.
	 * @return string Text without last word.
	 */
	public static function remove_last_word( string $text ): string {
		$text = trim( $text );
		$words = explode( ' ', $text );

		if ( count( $words ) > 1 ) {
			array_pop( $words );
			return implode( ' ', $words );
		}

		return $text;
	}

	/**
	 * Set cookie.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name Cookie name.
	 * @param string $value Cookie value.
	 * @param int $expire Expiration timestamp.
	 * @param bool $secure HTTPS only.
	 * @param bool $httponly HTTP only.
	 * @return bool True on success.
	 */
	public static function setcookie( string $name, string $value, int $expire = 0, bool $secure = false, bool $httponly = false ): bool {
		if ( headers_sent() ) {
			return false;
		}

		return setcookie( $name, $value, $expire, COOKIEPATH, COOKIE_DOMAIN, $secure, $httponly );
	}

	/**
	 * Get cookie value.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name Cookie name.
	 * @return string Cookie value or empty string.
	 */
	public static function getcookie( string $name ): string {
		return isset( $_COOKIE[ $name ] ) ? sanitize_text_field( $_COOKIE[ $name ] ) : '';
	}
}
