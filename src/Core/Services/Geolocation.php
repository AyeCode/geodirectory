<?php
/**
 * Geolocation Service
 *
 * Handles GPS coordinates, geocoding, reverse geocoding, IP geolocation, and timezone detection.
 *
 * @package GeoDirectory\Core
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core\Services;

use AyeCode\GeoDirectory\Core\Services\Maps;
use WP_Error;

/**
 * Geolocation class for GPS and location-based operations.
 */
final class Geolocation {

	/**
	 * Get location info from IP address.
	 *
	 * @since 3.0.0
	 *
	 * @param string $ip IP address. If empty, uses current user's IP.
	 * @return array Location data with latitude and longitude.
	 */
	public function geo_by_ip( string $ip = '' ): array {
		$geo = array();

		$data = $this->ip_api_data( $ip );
		if ( ! empty( $data ) && ! empty( $data['lat'] ) && ! empty( $data['lon'] ) ) {
			$geo['latitude'] = $data['lat'];
			$geo['longitude'] = $data['lon'];
		}

		return apply_filters( 'geodir_geo_by_ip', $geo, $ip );
	}

	/**
	 * Get location data from IP via ip-api.com.
	 *
	 * @since 3.0.0
	 *
	 * @param string $ip IP address.
	 * @return array|null Location data or null.
	 */
	public function ip_api_data( string $ip = '' ): ?array {
		global $wp_version;

		if ( empty( $ip ) ) {
			$ip = geodir_get_ip();
		}

		if ( empty( $ip ) ) {
			return null;
		}

		$data = array();

		// Check transient cache
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
			$response = wp_remote_get( $url );

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

		$data = apply_filters( 'geodir_ip_api_data', $data, $ip );

		set_transient( 'geodir_ip_location_' . $ip, $data, 24 * HOUR_IN_SECONDS ); // Cache IP location for 24 hours

		return $data;
	}

	/**
	 * Get GPS coordinates from an address.
	 *
	 * @since 3.0.0
	 *
	 * @param array|string $address Address array or string.
	 * @param bool $wp_error Whether to return WP_Error on failure.
	 * @return array|WP_Error|null GPS coordinates or error.
	 */
	public function get_gps_from_address( $address = array(), bool $wp_error = false ) {
		$api = Maps::active_map();

		$api = apply_filters( 'geodir_post_gps_from_address_api', $api );

		if ( $api == 'google' || $api == 'auto' ) {
			$_gps = $this->google_get_gps_from_address( $address, $wp_error );
		} elseif ( $api == 'osm' ) {
			$_gps = $this->osm_get_gps_from_address( $address, $wp_error );
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
				return null;
			}
		}

		return apply_filters( 'geodir_get_gps_from_address', $gps, $address, $api );
	}

	/**
	 * Get GPS info for the address using Google Geocode API.
	 *
	 * @since 3.0.0
	 *
	 * @param array|string $address Array of address element or full address.
	 * @param bool $wp_error Optional. Whether to return a WP_Error on failure. Default false.
	 * @return array|bool|WP_Error GPS data or WP_Error on failure.
	 */
	public function google_get_gps_from_address( $address, bool $wp_error = false ) {
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
		$google_api_key = Maps::google_geocode_api_key();
		if ( $google_api_key ) {
			$request_url .= '&key=' . $google_api_key;
		}

		// Maybe add language.
		$lang = Maps::map_language();
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
	 * @since 3.0.0
	 *
	 * @param array|string $address Array of address element or full address.
	 * @param bool $wp_error Optional. Whether to return a WP_Error on failure. Default false.
	 * @return array|bool|WP_Error GPS data or WP_Error on failure.
	 */
	public function osm_get_gps_from_address( $address, bool $wp_error = false ) {
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

				return $this->osm_get_gps_from_address( $address, $wp_error );
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
	 * Get address using latitude and longitude (Google reverse geocoding).
	 *
	 * @since 3.0.0
	 *
	 * @param string $lat Latitude.
	 * @param string $lng Longitude.
	 * @return array|bool Returns address components on success.
	 */
	public function get_address_by_lat_lng( string $lat, string $lng ) {
		$url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' . trim( $lat ) . ',' . trim( $lng ) . Maps::google_geocode_api_key( true );

		// Maybe add language.
		$lang = Maps::map_language();
		if ( $lang && 'en' !== $lang ) {
			$url .= '&language=' . esc_attr( $lang );
		}

		$response = wp_remote_get( $url );
		if ( is_wp_error( $response ) ) {
			return false;
		}

		$result = json_decode( wp_remote_retrieve_body( $response ) );

		if ( isset( $result->results[0]->address_components ) ) {
			return $result->results[0]->address_components;
		} else {
			return false;
		}
	}

	/**
	 * Get OpenStreetMap address using latitude and longitude.
	 *
	 * @since 3.0.0
	 *
	 * @param string $lat Latitude.
	 * @param string $lng Longitude.
	 * @return object|bool Returns address object on success.
	 */
	public function get_osm_address_by_lat_lng( string $lat, string $lng ) {
		$url = 'https://nominatim.openstreetmap.org/reverse?format=json&lat=' . trim( $lat ) . '&lon=' . trim( $lng ) . '&zoom=16&addressdetails=1&email=' . get_option( 'admin_email' );

		$response = wp_remote_get( $url );
		if ( is_wp_error( $response ) ) {
			return false;
		}

		$result = json_decode( wp_remote_retrieve_body( $response ) );

		if ( ! empty( $result->address ) ) {
			$address_fields = array( 'public_building', 'house', 'house_number', 'bakery', 'footway', 'street', 'road', 'village', 'attraction', 'pedestrian', 'neighbourhood', 'suburb' );
			$formatted_address = (array) $result->address;

			foreach ( $result->address as $key => $value ) {
				if ( ! in_array( $key, $address_fields ) ) {
					unset( $formatted_address[ $key ] );
				}
			}
			$result->formatted_address = ! empty( $formatted_address ) ? implode( ', ', $formatted_address ) : '';

			return $result;
		} else {
			return false;
		}
	}

	/**
	 * Get timezone data from latitude and longitude using Google Timezone API.
	 *
	 * @since 3.0.0
	 *
	 * @param string $latitude Latitude.
	 * @param string $longitude Longitude.
	 * @param int $timestamp Timestamp.
	 * @return array|WP_Error Timezone data or WP_Error on failure.
	 */
	public function get_timezone_by_lat_lng( string $latitude, string $longitude, int $timestamp = 0 ) {
		global $wp_version;

		$data = array();
		$error = '';

		if ( ! empty( $latitude ) && ! empty( $longitude ) ) {
			$api_url = 'https://maps.googleapis.com/maps/api/timezone/json';
			$api_url .= '?key=' . Maps::google_geocode_api_key();
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
}
