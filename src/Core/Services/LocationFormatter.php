<?php
/**
 * Location Formatter Service
 *
 * Handles location display formatting, URL generation, variable replacement, and address rendering.
 *
 * @package GeoDirectory\Core
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core\Services;

use AyeCode\GeoDirectory\Core\Interfaces\LocationsInterface;
use AyeCode\GeoDirectory\Core\Services\PostTypes;

/**
 * LocationFormatter class for formatting location data for display.
 */
final class LocationFormatter {

	/**
	 * Locations instance.
	 *
	 * @var LocationsInterface
	 */
	private LocationsInterface $locations;

	/**
	 * Constructor.
	 *
	 * @param LocationsInterface $locations Locations service.
	 */
	public function __construct( LocationsInterface $locations ) {
		$this->locations = $locations;
	}

	/**
	 * Replace location variables in content.
	 *
	 * @since 3.0.0
	 *
	 * @param string $content Content with variables.
	 * @param array $location_array Array of location variables.
	 * @param string|null $sep Separator.
	 * @param string $gd_page Page being filtered.
	 * @return string Filtered content.
	 */
	public function replace_location_variables( string $content, array $location_array = array(), ?string $sep = null, string $gd_page = '' ): string {
		if ( empty( $content ) ) {
			return $content;
		}

		$location_replace_vars = $this->location_replace_vars( $location_array, $sep, $gd_page );

		if ( ! empty( $location_replace_vars ) ) {
			foreach ( $location_replace_vars as $search => $replace ) {
				if ( ! empty( $search ) && strpos( $content, $search ) !== false ) {
					$content = str_replace( $search, $replace, $content );
				}
			}
		}

		return $content;
	}

	/**
	 * Build location replacement variables array.
	 *
	 * @since 3.0.0
	 *
	 * @param array $location_array Optional. Array of location variables.
	 * @param string|null $sep Optional. Separator.
	 * @param string $gd_page Optional. Page being filtered.
	 * @return array Location replacement variables.
	 */
	public function location_replace_vars( array $location_array = array(), ?string $sep = null, string $gd_page = '' ): array {
		global $wp, $gd_post;

		// Private address
		$check_address = ( $gd_page == 'single' || geodir_is_page( 'single' ) ) && ! empty( $gd_post ) && PostTypes::supports( $gd_post->post_type, 'private_address' ) ? true : false;

		if ( empty( $location_array ) ) {
			$location_array = $this->locations->get_current_location_terms( 'query_vars' );
		}

		if ( class_exists( 'GeoDir_Location_City' ) && geodir_get_option( 'lm_url_filter_archives_on_single', 'city' ) != 'city' && ! ( ! empty( $location_array ) && ! empty( $location_array['country'] ) && ! empty( $location_array['region'] ) && ! empty( $location_array['city'] ) ) && ! empty( $gd_post ) && ( $gd_page == 'single' || geodir_is_page( 'single' ) ) && PostTypes::supports( $gd_post->post_type, 'location' ) && ! empty( $gd_post->country ) && ! empty( $gd_post->region ) && ! empty( $gd_post->city ) ) {
			if ( ! empty( $gd_post->neighbourhood ) && class_exists( 'GeoDir_Location_Neighbourhood' ) && \GeoDir_Location_Neighbourhood::is_active() ) {
				$location = \GeoDir_Location_Neighbourhood::get_info_by_slug( $gd_post->neighbourhood );
				$neighbourhood = $gd_post->neighbourhood;

				if ( empty( $location ) ) {
					$location = \GeoDir_Location_City::get_info_by_name( $gd_post->city, $gd_post->country, $gd_post->region );
				}
			} else {
				$location = \GeoDir_Location_City::get_info_by_name( $gd_post->city, $gd_post->country, $gd_post->region );
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
				$location = $this->post_address( $location, $location_type, $gd_post, '' );
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
					$location_name = $this->post_address( $location_name, $location_type, $gd_post, '' );
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
		$location_replace_vars['%%location_sep%%'] = $sep !== null ? $sep : '|';
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
	 * Get location link based on location type.
	 *
	 * @since 3.0.0
	 *
	 * @param string $which_location Location link type. Default: 'current'.
	 * @return string Location link URL.
	 */
	public function get_location_link( string $which_location = 'current' ): string {
		$location_link = trailingslashit( get_permalink( geodir_location_page_id() ) );

		if ( $which_location == 'base' ) {
			return $location_link;
		} else {
			$location_terms = $this->locations->get_current_location_terms();

			$location_terms = apply_filters( 'geodir_location_link_location_terms', $location_terms );

			if ( ! empty( $location_terms ) ) {
				if ( get_option( 'permalink_structure' ) != '' ) {
					$location_terms = implode( "/", $location_terms );
					$location_terms = rtrim( $location_terms, '/' );
					$location_link .= $location_terms;
				} else {
					$location_link = geodir_getlink( $location_link, $location_terms );
				}
			}
		}
		return $location_link;
	}

	/**
	 * Check location slug for uniqueness.
	 *
	 * @since 3.0.0
	 *
	 * @param string $slug Term slug.
	 * @return string Modified term slug.
	 */
	public function location_slug_check( string $slug ): string {
		global $wpdb, $table_prefix;

		$slug_exists = $wpdb->get_var( $wpdb->prepare( "SELECT slug FROM " . $table_prefix . "terms WHERE slug=%s", array( $slug ) ) );

		if ( $slug_exists ) {
			$suffix = 1;
			do {
				$alt_location_name = _truncate_post_slug( $slug, 200 - ( strlen( (string) $suffix ) + 1 ) ) . "-$suffix";
				$location_slug_check = $wpdb->get_var( $wpdb->prepare( "SELECT slug FROM " . $table_prefix . "terms WHERE slug=%s", array( $alt_location_name ) ) );
				$suffix++;
			} while ( $location_slug_check && $suffix < 100 );

			$slug = $alt_location_name;
		}

		return $slug;
	}

	/**
	 * Render the post address field value for private addresses.
	 *
	 * @since 3.0.0
	 *
	 * @param string $value Field value.
	 * @param string $key Field key.
	 * @param object $post The post.
	 * @param mixed $default Whether to use default value.
	 * @return string Filtered field value.
	 */
	public function post_address( string $value, string $key, $post, $default = null ): string {
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
					if ( $default !== null ) {
						$output = $default;
					} else {
						$output = __( 'Private Address', 'geodirectory' );
					}
					break;
				case 'gd_post_address':            /* GD > Post Address widget/block */
					if ( $default !== null ) {
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
					if ( $default !== null ) {
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
}
