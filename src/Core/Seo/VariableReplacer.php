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

		// Check if we need to handle private address for custom fields
		$check_address = ( $this->gd_page === 'single' || geodir_is_page( 'single' ) ) && ! empty( $this->gd_post ) && GeoDir_Post_types::supports( $post_type, 'private_address' );

		/**
		 * Filter pre meta title.
		 *
		 * @since 2.0.0.76
		 *
		 * @param string $string Meta string.
		 * @param string $gd_page GeoDirectory page.
		 */
		$string = apply_filters( 'geodir_seo_pre_replace_variable', $string, $this->gd_page );

		// Separator
		if ( strpos( $string, '%%sep%%' ) !== false ) {
			$string = str_replace( '%%sep%%', $this->get_separator(), $string );
		}

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
		} elseif ( $this->gd_page === 'search' ) {
			$cat_name = $this->get_searched_category_name( $post_type . 'category' );
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
		$location_replace_vars = geodir_location_replace_vars();
		foreach ( $location_replace_vars as $lkey => $lval ) {
			if ( strpos( $string, $lkey ) !== false ) {
				$string = str_replace( $lkey, $lval, $string );
			}
		}

		// Search variables
		$search_term = '';
		if ( isset( $_REQUEST['s'] ) ) {
			$search_term = esc_attr( $_REQUEST['s'] );
			$search_term = str_replace( [ '%E2%80%99', "'" ], [ '%27', "'" ], $search_term ); // Apple fix
			$search_term = trim( stripslashes( $search_term ) );
		}
		$string = str_replace( '%%search_term%%', $search_term, $string );
		$string = str_replace( '%%for_search_term%%', $search_term ? sprintf( __( 'for %s', 'geodirectory' ), $search_term ) : '', $string );

		$search_near_term = '';
		$search_near = '';
		if ( isset( $_REQUEST['snear'] ) || isset( $_REQUEST['near'] ) ) {
			$search_near_term = esc_attr( $_REQUEST['snear'] ?? '' );
			if ( empty( $search_near_term ) && ! empty( $_REQUEST['near'] ) && $_REQUEST['near'] === 'me' ) {
				$search_near_term = __( 'My Location', 'geodirectory' );
			}
			$search_near_term = str_replace( [ '%E2%80%99', "'" ], [ '%27', "'" ], $search_near_term );
			$search_near_term = trim( stripslashes( $search_near_term ) );

			if ( $search_near_term !== '' ) {
				$search_near = sprintf( __( 'near %s', 'geodirectory' ), $search_near_term );
			}
		}
		$string = str_replace( '%%search_near_term%%', $search_near_term, $string );
		$string = str_replace( '%%search_near%%', $search_near, $string );

		// Page number variables
		if ( strpos( $string, '%%page%%' ) !== false ) {
			$page = geodir_title_meta_page( $this->get_separator() );
			$page = $page ?? '';
			$string = str_replace( '%%page%%', $page, $string );
		}
		if ( strpos( $string, '%%pagenumber%%' ) !== false ) {
			$pagenumber = geodir_title_meta_pagenumber();
			$string = str_replace( '%%pagenumber%%', $pagenumber, $string );
		}
		if ( strpos( $string, '%%pagetotal%%' ) !== false ) {
			$pagetotal = geodir_title_meta_pagetotal();
			$string = str_replace( '%%pagetotal%%', $pagetotal, $string );
		}
		if ( strpos( $string, '%%postcount%%' ) !== false ) {
			$postcount = geodir_title_meta_postcount();
			$string = str_replace( '%%postcount%%', $postcount, $string );
		}

		// Featured image variables
		if ( ( strpos( $string, '%%_featured_image%%' ) !== false || strpos( $string, '%%_post_images%%' ) !== false ) && ! empty( $this->gd_post->ID ) ) {
			$post_image = geodir_get_images( (int) $this->gd_post->ID, 1, false, 0, [ 'post_images' ], [ 'post_images' ] );
			$post_image_src = ! empty( $post_image ) && ! empty( $post_image[0] ) ? geodir_get_image_src( $post_image[0], 'original' ) : '';

			$string = str_replace( '%%_featured_image%%', $post_image_src, $string );
			$string = str_replace( '%%_post_images%%', $post_image_src, $string );
		}

		// Custom Field variables (%%_field-key%%)
		if ( $this->gd_post && strpos( $string, '%%_' ) !== false ) {
			$address_fields = geodir_post_meta_address_fields( $post_type );
			$matches_count = preg_match_all( '/%%_[^%%]*%%/', $string, $matches );

			if ( $matches_count && ! empty( $matches[0] ) ) {
				foreach ( $matches[0] as $cf ) {
					$field_name = str_replace( [ '%%_', '%%' ], '', $cf );
					$cf_value = $this->gd_post->{$field_name} ?? '';

					// Round rating
					if ( $cf_value && $field_name === 'overall_rating' ) {
						$cf_value = round( (float) $cf_value, 1 );
					}

					// Private address
					if ( ! empty( $cf_value ) && $check_address && isset( $address_fields[ $field_name ] ) ) {
						$cf_value = geodir_post_address( $cf_value, $field_name, $this->gd_post, '' );
					}

					$string = str_replace( "%%_{$field_name}%%", (string) $cf_value, $string );
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

	/**
	 * Gets the document title separator.
	 *
	 * @return string The separator.
	 */
	private function get_separator(): string {
		$sep = '-';

		// Use RankMath setting separator if available
		if ( defined( 'RANK_MATH_VERSION' ) && class_exists( 'RankMath', false ) ) {
			$sep = \RankMath\Helper::get_settings( 'titles.title_separator' );
		}

		/**
		 * Filters the separator for the document title.
		 *
		 * @since 2.0.0.35
		 *
		 * @param string $sep Document title separator.
		 */
		return apply_filters( 'document_title_separator', $sep );
	}

	/**
	 * Gets searched category names.
	 *
	 * @param string $taxonomy Category taxonomy. Default empty.
	 * @return string Category names.
	 */
	private function get_searched_category_name( string $taxonomy = '' ): string {
		$category_names = '';

		if ( empty( $_REQUEST['spost_category'] ) ) {
			return $category_names;
		}

		$post_category = is_array( $_REQUEST['spost_category'] ) ? array_map( 'absint', $_REQUEST['spost_category'] ) : [ absint( $_REQUEST['spost_category'] ) ];
		$_category_names = [];

		if ( ! empty( $post_category ) ) {
			$taxonomy = $taxonomy ?: geodir_get_current_posttype() . 'category';

			foreach ( $post_category as $term_id ) {
				$term = get_term( $term_id, $taxonomy );

				if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
					$_category_names[] = $term->name;
				}
			}
		}

		if ( ! empty( $_category_names ) ) {
			$category_names = implode( ', ', $_category_names );
		}

		return apply_filters( 'geodir_get_searched_category_name', $category_names, $_category_names, $taxonomy );
	}

	/**
	 * Returns helper tags HTML for use in settings pages.
	 *
	 * @param string $page The page type.
	 * @return string Helper tags HTML.
	 */
	public static function helper_tags( string $page = '' ): string {
		$output = '';
		$variables = self::get_variables( $page );

		if ( ! empty( $variables ) ) {
			$output .= '<ul class="geodir-helper-tags d-block clearfix p-0">';
			foreach ( $variables as $variable => $desc ) {
				$output .= "<li><span class='geodir-helper-tag' title='" . esc_attr__( 'Click to copy', 'geodirectory' ) . "'>" . esc_attr( $variable ) . "</span>" . geodir_help_tip( $desc ) . "</li>";
			}
			$output .= '</ul>';
		}

		return $output;
	}
}
