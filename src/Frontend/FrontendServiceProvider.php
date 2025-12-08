<?php
/**
 * GeoDirectory Frontend Service Provider
 *
 * @package GeoDirectory\Frontend
 * @since   3.0.0
 * @author  AyeCode Ltd
 */

declare( strict_types=1 );

namespace AyeCode\GeoDirectory\Frontend;

/**
 * The main service provider for the frontend.
 *
 * This class is responsible for registering all frontend-specific
 * services, hooks, shortcodes, and scripts with the container.
 *
 * @since 3.0.0
 */
final class FrontendServiceProvider {
	/**
	 * Registers the WordPress hooks for frontend functionality.
	 *
	 * This is the primary entry point for the service provider. It gets
	 * the container, registers all necessary classes, and then runs the
	 * main hook classes.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		// Get the main container instance using our global helper.
		$container = geodirectory()->container();

		// --- Post System ---
		// Get the PostHooks class from the container and register its hooks.
		$post_hooks = $container->get( PostHooks::class );
		$post_hooks->register_hooks();

		// --- Query System ---
		// Get the Query class from the container and register its hooks.
		$query = $container->get( Query::class );
		$query->register_hooks();

		// --- Template System ---
		// Register template hooks based on theme type (FSE/Block vs Classic).
		$this->register_template_hooks( $container );

//		// --- Review System ---
//		// Register all the classes for our new review system.
//		// The container is smart enough to figure out the dependencies.
//		$container->bind( ReviewRepository::class );
//		$container->bind( Reviews::class );
//		$container->bind( ReviewForm::class );
//		$container->bind( ReviewHooks::class );
//
//		// Get the main ReviewHooks class from the container.
//		$review_hooks = $container->get( ReviewHooks::class );
//
//		// Tell it to register all the actions and filters for the review system.
//		$review_hooks->register();
//
//
//		// --- SEO System ---
//		$container->bind( \AyeCode\GeoDirectory\Integrations\Seo\DefaultSeo::class );
//		$container->bind( \AyeCode\GeoDirectory\Integrations\Seo\Yoast::class );
//		$container->bind( \AyeCode\GeoDirectory\Integrations\Seo\RankMath::class );
//		$container->bind( \AyeCode\GeoDirectory\Core\Seo\VariableReplacer::class );
//		$container->bind( \AyeCode\GeoDirectory\Core\Seo::class );
//
//		// Get the main SEO service. The container will automatically build it and all its dependencies.
//		$seo_service = $container->get( \AyeCode\GeoDirectory\Core\Seo::class );
//
//		// Find out which integration is active (Yoast, Rank Math, or our default).
//		$active_integration = $seo_service->get_active_integration();
//
//		// Get the variable replacer utility.
//		$variable_replacer = $container->get( \AyeCode\GeoDirectory\Core\Seo\VariableReplacer::class );
//
//		// Tell the active integration to register its hooks.
//		$active_integration->register_hooks( $variable_replacer );
	}

	/**
	 * Register template system hooks based on theme type.
	 *
	 * Uses different template systems for block themes (FSE) vs classic themes.
	 *
	 * @param \AyeCode\GeoDirectory\Core\Container $container DI container.
	 * @return void
	 */
	private function register_template_hooks( $container ): void {
		// Check if we're using a block theme
		$templates_service = $container->get( \AyeCode\GeoDirectory\Core\Services\Templates::class );
		$is_block_theme = $templates_service->is_block_theme();

		if ( $is_block_theme ) {
			// --- Block Theme / FSE System ---
			// Register hooks for Full Site Editing support.
			$block_theme_hooks = $container->get( \AyeCode\GeoDirectory\Frontend\BlockTheme\BlockThemeHooks::class );
			$block_theme_hooks->register_hooks();
		} else {
			// --- Classic Theme System ---
			// Register hooks for traditional theme template loading.
			$template_loader_hooks = $container->get( \AyeCode\GeoDirectory\Frontend\Templates\TemplateLoaderHooks::class );
			$template_loader_hooks->register_hooks();
		}
	}
}
