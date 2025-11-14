<?php
/**
 * Taxonomy Registrar
 *
 * @package GeoDirectory\Common
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Common;

final class TaxonomiesRegistrar {
	private CptConfig $config;

	public function __construct( CptConfig $config ) {
		$this->config = $config;
	}

	/**
	 * Registers the custom taxonomies with WordPress.
	 */
	public function register(): void {
		if ( taxonomy_exists( 'gd_placecategory' ) ) {
			return;
		}

		do_action( 'geodirectory_register_taxonomy' );

		$taxonomies = $this->config->get_taxonomies();

		foreach ( $taxonomies as $taxonomy => $args ) {
			$args = apply_filters( 'geodir_taxonomy_args', $args, $taxonomy, $args['object_type'] );
			register_taxonomy( $taxonomy, $args['object_type'], $args['args'] );
		}

		do_action( 'geodirectory_after_register_taxonomy' );
	}
}
