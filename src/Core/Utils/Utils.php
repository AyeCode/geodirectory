<?php
/**
 * General utility functions.
 *
 * @package GeoDirectory\Core\Utils
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core\Utils;

class Utils {
	/**
	 * Creates a random hash.
	 *
	 * This is a wrapper for the global geodir_rand_hash() function to make it
	 * available via the core service container.
	 *
	 * @return string The random hash.
	 */
	public function rand_hash(): string {
		if ( function_exists( 'openssl_random_pseudo_bytes' ) ) {
			return bin2hex( openssl_random_pseudo_bytes( 20 ) );
		} else {
			return sha1( (string) \wp_rand() );
		}
	}

	/**
	 * GeoDir API - Hash.
	 *
	 * @param  string $data
	 *
	 * @return string
	 *
	 */
	public function api_hash( string $data ) {
		return hash_hmac( 'sha256', $data, 'wc-api' );
	}

}
