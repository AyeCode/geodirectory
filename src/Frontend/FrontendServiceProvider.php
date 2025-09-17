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
		//$container = geodir();

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
}
