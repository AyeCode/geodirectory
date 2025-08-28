<?php
/**
 * GeoDirectory SEO Variable Replacer
 *
 * @package GeoDirectory\Core\Seo
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core\Seo;

use AyeCode\GeoDirectory\Core\Data\LocationData;
use AyeCode\GeoDirectory\Core\Interfaces\LocationsInterface;

/**
 * Handles the replacement of GeoDirectory's SEO variables (e.g., %%category%%).
 */
final class VariableReplacer {
	public string $gd_page = '';
	private ?object $post = null;
	private ?object $gd_post = null;
	private LocationData $current_location;
	private LocationsInterface $locations;

	/**
	 * Constructor.
	 *
	 * @param LocationsInterface $locations The locations service.
	 */
	public function __construct( LocationsInterface $locations ) {
		$this->locations = $locations;
		// Determine the current page context once.
		$this->gd_page          = geodir_get_page_type();
		$this->post             = get_post();
		$this->gd_post          = geodir_get_post_info( $this->post );
		$this->current_location = $this->locations->get_current();
	}

	/**
	 * Replaces all GD variables in a given string.
	 *
	 * @param string $string The string containing variables.
	 * @return string The string with variables replaced.
	 */
	public function replace( string $string ): string {
		if ( ! is_scalar( $string ) || strpos( $string, '%%' ) === false ) {
			return $string;
		}

		$post_type = get_post_type( $this->post );

		// Generic variables
		$string = str_replace( '%%sitename%%', get_bloginfo( 'name' ), $string );
		$string = str_replace( '%%sitedesc%%', get_bloginfo( 'description' ), $string );
		if ( $this->post ) {
			$string = str_replace( '%%title%%', $this->post->post_title, $string );
			$string = str_replace( '%%id%%', (string) $this->post->ID, $string );

			$excerpt = ! empty( $this->post->post_excerpt ) ? $this->post->post_excerpt : wp_trim_words( $this->post->post_content, 55, '' );
			$string = str_replace( '%%excerpt%%', strip_tags( $excerpt ), $string );
		}

		// Category & Tag variables
		$cat_name = '';
		if ( $this->gd_page === 'single' && ! empty( $this->gd_post->default_category ) ) {
			$cat      = get_term( $this->gd_post->default_category );
			$cat_name = $cat->name ?? '';
		} elseif ( $this->gd_page === 'archive' ) {
			$cat_name = get_queried_object()->name ?? '';
		} elseif ( $this->gd_page === 'search' && ! empty( $_REQUEST['spost_category'] ) ) {
			// @todo This part could be improved by a dedicated search service.
			$term = get_term( (int) $_REQUEST['spost_category'] );
			$cat_name = $term->name ?? '';
		}
		$string = str_replace( '%%category%%', $cat_name, $string );
		$string = str_replace( '%%in_category%%', $cat_name ? sprintf( _x( 'in %s', 'in category', 'geodirectory' ), $cat_name ) : '', $string );
		$string = str_replace( '%%tag%%', $cat_name, $string ); // Old class used category for tag variable.

		// Post Type variables
		if ( $post_type ) {
			$string = str_replace( '%%pt_single%%', geodir_get_post_type_singular_label( $post_type ), $string );
			$string = str_replace( '%%pt_plural%%', geodir_get_post_type_plural_label( $post_type ), $string );
		}

		// Location variables (using the injected Locations service)
		$full_location = implode( ', ', array_filter( [ $this->current_location->country, $this->current_location->region, $this->current_location->city ] ) );
		$string = str_replace( '%%location%%', $full_location, $string );
		$string = str_replace( '%%in_location%%', $full_location ? sprintf( __( 'in %s', 'geodirectory' ), $full_location ) : '', $string );
		$string = str_replace( '%%location_country%%', $this->current_location->country, $string );
		$string = str_replace( '%%location_region%%', $this->current_location->region, $string );
		$string = str_replace( '%%location_city%%', $this->current_location->city, $string );

		// Search variables
		$search_term = isset( $_REQUEST['s'] ) ? esc_attr( stripslashes( $_REQUEST['s'] ) ) : '';
		$string = str_replace( '%%search_term%%', $search_term, $string );
		$string = str_replace( '%%for_search_term%%', $search_term ? sprintf( __( 'for %s', 'geodirectory' ), $search_term ) : '', $string );
		$near_term = isset( $_REQUEST['snear'] ) ? esc_attr( stripslashes( $_REQUEST['snear'] ) ) : '';
		$string = str_replace( '%%search_near_term%%', $near_term, $string );
		$string = str_replace( '%%search_near%%', $near_term ? sprintf( __( 'near %s', 'geodirectory' ), $near_term ) : '', $string );

		// Custom Field variables (%%_field-key%%)
		if ( $this->gd_post && strpos( $string, '%%_' ) !== false ) {
			if ( preg_match_all( '/%%_([^%]+)%%/', $string, $matches ) ) {
				foreach ( $matches[1] as $index => $field_key ) {
					$field_value = $this->gd_post->{$field_key} ?? '';
					$string = str_replace( $matches[0][$index], (string) $field_value, $string );
				}
			}
		}

		return apply_filters( 'geodir_replace_seo_vars', $string, $this->gd_page );
	}

	/**
	 * Gets a list of available SEO variables and their descriptions.
	 *
	 * This is a static helper for use in config files or other places
	 * where an object instance is not available.
	 *
	 * @param string $page_type The GeoDirectory page type (e.g., 'pt', 'single', 'search').
	 * @return array An array of variables => descriptions.
	 */
	public static function get_variables( string $page_type = '' ): array {
		$vars = [];

		// Generic variables
		$vars['%%title%%']       = __( 'The current post title.', 'geodirectory' );
		$vars['%%sitename%%']    = __( 'The site name from general settings: site title. ', 'geodirectory' );
		$vars['%%sitedesc%%']    = __( 'The site description from general settings: tagline.', 'geodirectory' );
		$vars['%%sep%%']         = __( 'The separator mostly used in meta titles.', 'geodirectory' );
		$vars['%%id%%']          = __( 'The current post id.', 'geodirectory' );
		$vars['%%excerpt%%']     = __( 'The current post excerpt.', 'geodirectory' );

		// CPT/Archive/Single specific variables
		if ( in_array( $page_type, [ 'pt', 'archive', 'single', 'search' ] ) ) {
			$vars['%%pt_single%%']   = __( 'Post type singular name.', 'geodirectory' );
			$vars['%%pt_plural%%']   = __( 'Post type plural name.', 'geodirectory' );
			$vars['%%category%%']    = __( 'The current category name.', 'geodirectory' );
			$vars['%%in_category%%'] = __( 'The current category name prefixed with `in` eg: in Attractions', 'geodirectory' );
		}

		// Location variables
		$vars['%%location%%']           = __( 'The full current location eg: United States, Pennsylvania, Philadelphia', 'geodirectory' );
		$vars['%%in_location%%']        = __( 'The full current location prefixed with `in` eg: in United States, Pennsylvania, Philadelphia', 'geodirectory' );
		$vars['%%location_country%%']   = __( 'The current viewing country eg: United States', 'geodirectory' );
		$vars['%%location_region%%']    = __( 'The current viewing region eg: Pennsylvania', 'geodirectory' );
		$vars['%%location_city%%']      = __( 'The current viewing city eg: Philadelphia', 'geodirectory' );

		// Search page only variables
		if ( $page_type === 'search' ) {
			$vars['%%search_term%%']      = __( 'The currently used search for term.', 'geodirectory' );
			$vars['%%for_search_term%%']  = __( 'The currently used search for term with `for`. Ex: for dinner.', 'geodirectory' );
			$vars['%%search_near%%']      = __( 'The currently used search near term with `near`. Ex: near Philadelphia.', 'geodirectory' );
			$vars['%%search_near_term%%'] = __( 'The currently used search near term.', 'geodirectory' );
		}

		// Single page only variables
		if ( $page_type === 'single' ) {
			$vars['%%_FIELD-KEY%%'] = __( 'Show any custom field by using its field key prefixed with an _underscore. Ex: _phone.', 'geodirectory' );
		}

		return apply_filters( 'geodir_seo_variables', $vars, $page_type );
	}
}
