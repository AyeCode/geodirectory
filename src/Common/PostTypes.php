<?php
/**
 * Post Type Registrar
 *
 * @package GeoDirectory\Common
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Common;

final class PostTypes {
	private CptConfig $config;

	public function __construct( CptConfig $config ) {
		$this->config = $config;
	}

	/**
	 * Registers the custom post types with WordPress.
	 */
	public function register(): void {
		if ( post_type_exists( 'gd_place' ) ) {
			return;
		}

		$post_types = $this->config->get_post_types();

		do_action( 'geodirectory_register_post_type' );

		foreach ( $post_types as $post_type => $args ) {
			$args = stripslashes_deep( $args );

			// Ensure labels are translated.
			if ( ! empty( $args['labels'] ) ) {
				foreach ( $args['labels'] as $key => $val) {
					$args['labels'][ $key ] = __( $val, 'geodirectory' );
				}
			}

			// Force required settings.
			$args['show_ui'] = true;
			$args['show_in_menu'] = true;

			$args = apply_filters( 'geodir_post_type_args', $args, $post_type );

			register_post_type( $post_type, $args );
		}

		do_action( 'geodirectory_after_register_post_type' );
	}
}
