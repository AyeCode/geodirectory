<?php
/**
 * Template Loader Hooks
 *
 * Centralized hooks for classic theme template loading.
 *
 * @package GeoDirectory\Frontend\Templates
 * @since   3.0.0
 * @author  AyeCode Ltd
 */

declare( strict_types=1 );

namespace AyeCode\GeoDirectory\Frontend\Templates;

use AyeCode\GeoDirectory\Support\Hookable;
use AyeCode\GeoDirectory\Core\Services\Templates;

/**
 * Manages WordPress hooks for classic theme template loading.
 *
 * @since 3.0.0
 */
final class TemplateLoaderHooks {
	use Hookable;

	/**
	 * Template resolver service.
	 *
	 * @var TemplateResolver
	 */
	private TemplateResolver $resolver;

	/**
	 * Content injector service.
	 *
	 * @var ContentInjector
	 */
	private ContentInjector $injector;

	/**
	 * Template restrictions service.
	 *
	 * @var TemplateRestrictions
	 */
	private TemplateRestrictions $restrictions;

	/**
	 * Templates service.
	 *
	 * @var Templates
	 */
	private Templates $templates;

	/**
	 * Constructor.
	 *
	 * @param TemplateResolver     $resolver Template resolver.
	 * @param ContentInjector      $injector Content injector.
	 * @param TemplateRestrictions $restrictions Template restrictions.
	 * @param Templates            $templates Templates service.
	 */
	public function __construct(
		TemplateResolver $resolver,
		ContentInjector $injector,
		TemplateRestrictions $restrictions,
		Templates $templates
	) {
		$this->resolver = $resolver;
		$this->injector = $injector;
		$this->restrictions = $restrictions;
		$this->templates = $templates;
	}

	/**
	 * Registers all WordPress hooks for template loading.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		// Main template routing
		$this->filter( 'template_include', [ $this, 'template_loader' ] );

		// Disable theme featured image output on detail pages
		$this->on( 'wp', [ $this, 'disable_theme_featured_output' ] );

		// Disable template pages from frontend viewing
		$this->on( 'wp', [ $this, 'disable_page_templates_frontend' ] );

		// Clear list view storage when archive page is updated
		$this->on( 'post_updated', [ $this, 'set_clear_list_view_storage' ], 10, 3 );
	}

	/**
	 * Load the appropriate template for GeoDirectory pages.
	 *
	 * Main template routing function that determines which template file
	 * to use for the current GeoDirectory page.
	 *
	 * @param string $template The default template path.
	 * @return string The modified template path.
	 */
	public function template_loader( string $template ): string {
		// Skip for non-GeoDirectory pages
		if ( is_attachment() || is_embed() || ( is_404() && ! isset( $_REQUEST['geodir_search'] ) ) ) {
			return $template;
		}

		// Get the default file to use
		$default_file = $this->resolver->get_default_file( $template );

		if ( ! $default_file ) {
			return $template;
		}

		/**
		 * Filters which files to find before GeoDirectory template logic.
		 *
		 * @since 3.0.0
		 *
		 * @param array  $search_files Template files to search.
		 * @param string $default_file Default file.
		 */
		$search_files = $this->resolver->get_template_files( $default_file );
		$gd_template = locate_template( $search_files );

		// Fall back to plugin template if theme doesn't have it
		if ( ! $gd_template && $default_file && $default_file !== ' ' ) {
			$gd_template = $this->templates->get_templates_dir() . '/' . $default_file;
		}

		if ( $gd_template ) {
			$template = $gd_template;
		}

		// Setup archive loop as page for applicable pages
		$this->maybe_setup_archive_loop( $default_file, $template );

		// Setup singular page content injection
		$this->maybe_setup_singular_page();

		/**
		 * Filters the final template path.
		 *
		 * @since 3.0.0
		 *
		 * @param string $template Template path.
		 * @param string $default_file Default file.
		 */
		return apply_filters( 'geodir_template_loader', $template, $default_file );
	}

	/**
	 * Setup archive loop to display as a page.
	 *
	 * Called for taxonomy, post type archive, favorites, and search pages.
	 *
	 * @global \WP_Query $wp_query WordPress query object.
	 *
	 * @param string $default_file Default template file.
	 * @param string $default_template Default template.
	 * @return void
	 */
	private function maybe_setup_archive_loop( string $default_file = '', string $default_template = '' ): void {
		global $wp_query;

		// Check if we should handle archive pages
		if ( ! function_exists( 'geodir_is_taxonomy' ) || ! function_exists( 'geodir_is_post_type_archive' ) || ! function_exists( 'geodir_is_page' ) ) {
			return;
		}

		$is_archive = geodir_is_taxonomy()
			|| geodir_is_post_type_archive()
			|| ( geodir_is_page( 'author' ) && ! empty( $wp_query->query['gd_favs'] ) )
			|| geodir_is_page( 'search' );

		if ( $is_archive ) {
			$this->injector->setup_archive_loop_as_page( $default_file, $default_template );
		}
	}

	/**
	 * Setup singular page content injection.
	 *
	 * @return void
	 */
	private function maybe_setup_singular_page(): void {
		if ( ! function_exists( 'geodir_is_singular' ) || ! geodir_is_singular() ) {
			return;
		}

		add_filter( 'the_content', [ $this->injector, 'setup_singular_page' ] );
	}

	/**
	 * Attempt to remove theme featured image output.
	 *
	 * @return void
	 */
	public function disable_theme_featured_output(): void {
		$this->restrictions->maybe_disable_theme_featured_output();
	}

	/**
	 * Disable page templates from frontend viewing.
	 *
	 * @return void
	 */
	public function disable_page_templates_frontend(): void {
		$this->restrictions->disable_page_templates_frontend();
	}

	/**
	 * Set clear list view storage flag.
	 *
	 * @param int      $post_ID Post ID.
	 * @param \WP_Post $post_after Post after update.
	 * @param \WP_Post $post_before Post before update.
	 * @return void
	 */
	public function set_clear_list_view_storage( int $post_ID, \WP_Post $post_after, \WP_Post $post_before ): void {
		$this->restrictions->set_clear_list_view_storage( $post_ID, $post_after, $post_before );
	}
}
