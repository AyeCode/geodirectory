<?php
/**
 * Common Service Provider
 *
 * @package GeoDirectory\Common
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Common;

final class CommonServiceProvider {
	public function register_hooks(): void {
		$container = geodirectory()->container();

		// Get our registrar instances from the container.
		$post_types = $container->get( PostTypesRegistrar::class );
		$taxonomies = $container->get( TaxonomiesRegistrar::class );
		$statuses   = $container->get( PostStatusesRegistrar::class );

		// Hook them into the WordPress init action at the correct priorities.
		add_action( 'init', [ $taxonomies, 'register' ], 5 );
		add_action( 'init', [ $post_types, 'register' ], 5 );
		add_action( 'init', [ $statuses, 'register' ], 9 );

		// Register AJAX actions that are available on both front and back end.
		$file_upload_action = $container->get( \AyeCode\GeoDirectory\Frontend\Ajax\FileUploadAction::class );
		$file_upload_action->register();

		// Other common hooks from your old class.
		add_action( 'geodir_flush_rewrite_rules', [ $this, 'flush_rewrite_rules' ] );
		add_filter( 'use_block_editor_for_post_type', [ $this, 'disable_gutenberg' ], 10, 2 );
		add_filter( 'rest_api_allowed_post_types', [ $this, 'rest_api_allowed_post_types' ] );
	}

	public function flush_rewrite_rules(): void {
		flush_rewrite_rules();
	}

	public function disable_gutenberg( bool $is_enabled, string $post_type ): bool {
		// @todo Refactor geodir_get_posttypes() to a method in CptConfig.
		if ( in_array( $post_type, geodir_get_posttypes() ) ) {
			return apply_filters('geodir_force_block_editor', false, $post_type );
		}
		return $is_enabled;
	}

	public function rest_api_allowed_post_types( array $post_types ): array {
		// @todo Refactor geodir_get_posttypes() to a method in CptConfig.
		$gd_post_types = geodir_get_posttypes();
		return array_merge( $post_types, $gd_post_types );
	}
}
