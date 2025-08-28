<?php
/**
 * SEO Integration Interface
 *
 * @package GeoDirectory\Core\Interfaces
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core\Interfaces;

use AyeCode\GeoDirectory\Core\Seo\VariableReplacer;

/**
 * Defines the contract for any class that integrates with an SEO plugin.
 */
interface SeoIntegrationInterface {
	/**
	 * Checks if the corresponding SEO plugin is active and enabled.
	 *
	 * @return bool True if active, false otherwise.
	 */
	public function is_active(): bool;

	/**
	 * Registers all the necessary WordPress hooks for this specific integration.
	 *
	 * @param VariableReplacer $variable_replacer The variable replacer utility.
	 * @return void
	 */
	public function register_hooks( VariableReplacer $variable_replacer ): void;
}
