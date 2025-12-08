<?php
/**
 * Template Resolver
 *
 * Resolves which template file should be used for GeoDirectory pages in classic themes.
 *
 * @package GeoDirectory\Frontend\Templates
 * @since   3.0.0
 * @author  AyeCode Ltd
 */

declare( strict_types=1 );

namespace AyeCode\GeoDirectory\Frontend\Templates;

use AyeCode\GeoDirectory\Core\Services\Templates;

/**
 * Handles template file resolution for classic themes.
 *
 * @since 3.0.0
 */
final class TemplateResolver {

	/**
	 * Templates service.
	 *
	 * @var Templates
	 */
	private Templates $templates;

	/**
	 * Constructor.
	 *
	 * @param Templates $templates Templates service.
	 */
	public function __construct( Templates $templates ) {
		$this->templates = $templates;
	}

	/**
	 * Get the default template file for the current GeoDirectory page.
	 *
	 * @global \WP_Query $wp_query WordPress query object.
	 *
	 * @param string $default_template The theme's default template.
	 * @return string Template file path or empty string.
	 */
	public function get_default_file( string $default_template = '' ): string {
		global $wp_query;

		// Check if this is a GeoDirectory page
		if ( ! function_exists( 'geodir_is_geodir_page' ) || ! geodir_is_geodir_page() ) {
			return '';
		}

		// Start with a fake return to trigger defaults
		$default_file = ' ';

		// Singular post templates
		if ( function_exists( 'geodir_is_singular' ) && geodir_is_singular() ) {
			$default_file = $this->get_singular_template();
		}
		// Location page template
		elseif ( function_exists( 'geodir_is_page' ) && geodir_is_page( 'location' ) ) {
			$default_file = $this->get_location_template();
		}
		// Add listing page template
		elseif ( function_exists( 'geodir_is_page' ) && geodir_is_page( 'add-listing' ) ) {
			$default_file = $this->get_add_listing_template();
		}
		// Archive templates (including search)
		else {
			$default_file = $this->get_archive_template( $default_template );
		}

		/**
		 * Filters the resolved template file.
		 *
		 * @since 3.0.0
		 *
		 * @param string $default_file Default template file.
		 * @param string $default_template Theme's default template.
		 */
		return apply_filters( 'geodir_resolved_template_file', $default_file, $default_template );
	}

	/**
	 * Get template file for singular GeoDirectory posts.
	 *
	 * @global \WP_Query $wp_query WordPress query object.
	 *
	 * @return string Template file path.
	 */
	private function get_singular_template(): string {
		global $wp_query;

		// Check for details page template setting
		$single_template = function_exists( 'geodir_get_option' ) ? geodir_get_option( 'details_page_template' ) : '';

		if ( $single_template && locate_template( $single_template ) ) {
			return $single_template;
		}

		// Check for page template from details page
		$post_type = function_exists( 'geodir_get_current_posttype' ) ? geodir_get_current_posttype() : '';
		$page_id = function_exists( 'geodir_details_page_id' ) ? geodir_details_page_id( $post_type ) : 0;

		if ( $page_id && ( $template = get_page_template_slug( $page_id ) ) ) {
			if ( locate_template( $template ) ) {
				$wp_query->is_page = 1;
				return $template;
			}
		}

		// Check for theme compatibility template
		if ( class_exists( 'GeoDir_Compatibility' ) && method_exists( 'GeoDir_Compatibility', 'theme_single_template' ) ) {
			$theme_template = \GeoDir_Compatibility::theme_single_template();
			if ( $theme_template ) {
				return $theme_template;
			}
		}

		return ' ';
	}

	/**
	 * Get template file for location pages.
	 *
	 * @return string Template file path.
	 */
	private function get_location_template(): string {
		$page_id = function_exists( 'geodir_location_page_id' ) ? geodir_location_page_id() : 0;

		if ( $page_id && ( $template = get_page_template_slug( $page_id ) ) ) {
			if ( locate_template( $template ) ) {
				return $template;
			}
		}

		return ' ';
	}

	/**
	 * Get template file for add listing pages.
	 *
	 * @return string Template file path.
	 */
	private function get_add_listing_template(): string {
		// The add listing page should never be cached
		if ( function_exists( 'geodir_nocache_headers' ) ) {
			geodir_nocache_headers();
		}

		$post_type = function_exists( 'geodir_get_current_posttype' ) ? geodir_get_current_posttype() : '';
		$page_id = function_exists( 'geodir_add_listing_page_id' ) ? geodir_add_listing_page_id( $post_type ) : 0;

		if ( $page_id && ( $template = get_page_template_slug( $page_id ) ) ) {
			if ( locate_template( $template ) ) {
				return $template;
			}
		}

		return ' ';
	}

	/**
	 * Get template file for archive pages.
	 *
	 * @global \WP_Query $wp_query WordPress query object.
	 *
	 * @param string $default_template Theme's default template.
	 * @return string Template file path.
	 */
	private function get_archive_template( string $default_template ): string {
		global $wp_query;

		// Check for archive page template setting
		$archive_template = function_exists( 'geodir_get_option' ) ? geodir_get_option( 'archive_page_template' ) : '';

		if ( $archive_template && locate_template( $archive_template ) ) {
			return $archive_template;
		}

		$post_type = function_exists( 'geodir_get_current_posttype' ) ? geodir_get_current_posttype() : '';

		// Check for search page template
		if ( function_exists( 'geodir_is_page' ) && geodir_is_page( 'search' ) ) {
			$page_id = function_exists( 'geodir_search_page_id' ) ? geodir_search_page_id() : 0;
		} else {
			$page_id = function_exists( 'geodir_archive_page_id' ) ? geodir_archive_page_id( $post_type ) : 0;
		}

		if ( $page_id && ( $template = get_page_template_slug( $page_id ) ) ) {
			if ( locate_template( $template ) ) {
				return $template;
			}
		}

		// Handle search page special cases
		if ( function_exists( 'geodir_is_page' ) && geodir_is_page( 'search' ) ) {
			// Prevent 404 on search with no results
			$wp_query->is_404 = '';
			$wp_query->is_page = 1;
			$wp_query->is_archive = 1;
			$wp_query->is_search = 1;
		}

		return ' ';
	}

	/**
	 * Get an array of template files to search for.
	 *
	 * Builds the template hierarchy for locate_template().
	 *
	 * @param string $default_file Default template file.
	 * @return array Array of template files to search for.
	 */
	public function get_template_files( string $default_file ): array {
		$search_files = array();

		/**
		 * Filters template files before GeoDirectory adds its own.
		 *
		 * @since 3.0.0
		 *
		 * @param array  $search_files Template files.
		 * @param string $default_file Default file.
		 */
		$search_files = apply_filters( 'geodir_template_loader_files', $search_files, $default_file );

		// Add taxonomy templates
		if ( function_exists( 'geodir_is_taxonomy' ) && geodir_is_taxonomy() ) {
			$term = get_queried_object();

			if ( $term ) {
				$search_files[] = "taxonomy-{$term->taxonomy}-{$term->slug}.php";
				$search_files[] = $this->templates->get_theme_template_dir_name() . "/taxonomy-{$term->taxonomy}-{$term->slug}.php";
				$search_files[] = "taxonomy-{$term->taxonomy}.php";
				$search_files[] = $this->templates->get_theme_template_dir_name() . "/taxonomy-{$term->taxonomy}.php";
			}
		}

		// Add the default file if provided
		if ( ! empty( $default_file ) && $default_file !== ' ' ) {
			$search_files[] = $default_file;
			$search_files[] = $this->templates->get_theme_template_dir_name() . '/' . $default_file;
		}

		// Add GeoDirectory-specific templates
		if ( function_exists( 'geodir_is_page' ) ) {
			if ( geodir_is_page( 'archive' ) || geodir_is_page( 'search' ) ) {
				$search_files[] = 'geodirectory-archive.php';
			}

			if ( geodir_is_page( 'single' ) ) {
				$search_files[] = 'geodirectory-single.php';
			}
		}

		// Add fallback templates
		$search_files[] = 'geodirectory.php';
		$search_files[] = 'page.php';

		// Handle themes without page.php
		if ( ( empty( $default_file ) || $default_file === ' ' )
			&& isset( $_REQUEST['geodir_search'] )
			&& is_search()
			&& ! get_query_template( 'page' )
			&& get_index_template()
		) {
			$search_files[] = 'index.php';
		}

		return array_unique( $search_files );
	}
}
