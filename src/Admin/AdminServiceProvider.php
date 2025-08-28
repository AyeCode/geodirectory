<?php
/**
 * GeoDirectory Admin Service Provider
 *
 * @package GeoDirectory\Admin
 * @since   3.0.0
 * @author  AyeCode Ltd
 */

// Use strict types for better code quality.
declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Admin;

// We'll create the Setup class next.
use AyeCode\GeoDirectory\Admin\Setup;

/**
 * The main service provider for the admin area.
 *
 * This class is the primary entry point for all backend functionality.
 * It's responsible for initializing the core admin setup class.
 *
 * @since 3.0.0
 */
final class AdminServiceProvider {
	/**
	 * Registers the WordPress hooks for the admin area.
	 *
	 * This method instantiates and initializes the main admin setup class.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		// @todo Create the Setup.php class.
		$setup = new Setup();
		$setup->register_hooks();
	}
}
