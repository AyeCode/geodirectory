<?php
/**
 * This file contains environment-related utility functions.
 *
 * @author   AyeCode
 * @category Utils
 * @package  GeoDirectory\ImportExport\Utils
 * @version  1.0.0
 */

namespace AyeCode\GeoDirectory\ImportExport\Utils;

/**
 * Environment Class
 *
 * Provides static helper methods for interacting with the server environment,
 * such as adjusting PHP execution limits for long-running tasks.
 */
class Environment
{
	/**
	 * Attempts to increase PHP execution limits for import/export processes.
	 *
	 * This helps prevent timeouts on servers with restrictive configurations
	 * when processing large files. We suppress errors as ini_set() can be
	 * disabled by the hosting provider.
	 *
	 * @return void
	 */
	public static function setExecutionLimits()
	{
		if ( ! ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ) {
			error_reporting( 0 );
		}
		/** @scrutinizer ignore-unhandled */ @ini_set( 'display_errors', 0 );

		// Try to set higher limits for import.
		$max_input_time     = ini_get( 'max_input_time' );
		$max_execution_time = ini_get( 'max_execution_time' );
		$memory_limit       = ini_get( 'memory_limit' );

		if ( $max_input_time !== 0 && $max_input_time != -1 && ( ! $max_input_time || $max_input_time < 3000 ) ) {
			@ini_set( 'max_input_time', '3000' );
		}

		if ( $max_execution_time !== 0 && ( ! $max_execution_time || $max_execution_time < 3000 ) ) {
			@ini_set( 'max_execution_time', '3000' );
		}

		if ( $memory_limit && str_replace( 'M', '', $memory_limit ) ) {
			if ( str_replace( 'M', '', $memory_limit ) < 256 ) {
				@ini_set( 'memory_limit', '256M' );
			}
		}

		/*
		 * The `auto_detect_line_endings` setting is deprecated in PHP 8.1 but
		 * is kept here for compatibility with older systems and files that may
		 * still use non-standard line endings.
		 */
		if (version_compare(PHP_VERSION, '8.1.0', '<')) {
			@ini_set( 'auto_detect_line_endings', '1' );
		}
	}
}
