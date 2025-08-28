<?php
/**
 * GeoDirectory Plugin Lifecycle
 *
 * @package GeoDirectory\Core
 * @since   3.0.0
 * @author  AyeCode Ltd
 */

// Use strict types for better code quality.
declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core;

/**
 * Handles activation and deactivation hooks for the plugin.
 *
 * @since 3.0.0
 */
final class Lifecycle {
	/**
	 * Fired when the plugin is activated.
	 *
	 * This is the ideal place to create database tables, set default options,
	 * and flush rewrite rules.
	 *
	 * @return void
	 */
	public static function activate(): void {
		// @todo Add logic to create custom database tables.
		// Example: Database\TableManager::create_tables();

		// @todo Add logic to set default plugin options.
		// Example: Options::set_defaults();

		// Flush rewrite rules to ensure CPTs and permalinks work immediately.
		flush_rewrite_rules();
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * This is the place for any cleanup actions. Usually, we just flush
	 * rewrite rules again to remove our plugin's rules.
	 *
	 * @return void
	 */
	public static function deactivate(): void {
		flush_rewrite_rules();
	}
}
