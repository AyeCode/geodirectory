<?php

namespace AyeCode\GeoDirectory\Admin\Settings;

use AyeCode\GeoDirectory\Admin\Settings\Handlers\FieldSettingsHandler;
use AyeCode\GeoDirectory\Admin\Settings\Handlers\GeneralSettingsHandler;
use AyeCode\GeoDirectory\Admin\Settings\Handlers\SortingSettingsHandler;
use AyeCode\GeoDirectory\Admin\Settings\Handlers\TabSettingsHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages and delegates settings persistence to specialized handlers.
 *
 * Uses a lazy-loading pattern to only instantiate a handler class when needed.
 *
 * @package AyeCode\GeoDirectory\Admin\Settings
 * @since 3.0.0
 */
final class SettingsPersistenceManager {

	/**
	 * @var array<string, string> Maps a setting group key to its handler's class name.
	 */
	private array $handler_classes = [];

	/**
	 * @var array<string, PersistenceHandlerInterface> Caches handler instances.
	 */
	private array $instances = [];

	/**
	 * Defines the mapping of settings groups to their handler classes.
	 */
	public function __construct() {
		$this->handler_classes = [
			'general' => GeneralSettingsHandler::class,
//			'fields'  => FieldSettingsHandler::class,
			'tabs'    => TabSettingsHandler::class,
			'sorting' => SortingSettingsHandler::class,
		];

		/**
		 * Allows addons to register their own CPT settings handlers.
		 * 'search' => My\Search\Handler::class
		 */
		$this->handler_classes = apply_filters( 'geodir_cpt_settings_handlers', $this->handler_classes );
	}

	/**
	 * Retrieves ALL settings for a post type and formats them into a single array.
	 *
	 * This method iterates through every registered handler, fetches its data,
	 * and merges it into the specific structure required by the Settings Framework:
	 * - 'general' settings are placed at the top level.
	 * - All other settings are nested in an array with their group key.
	 *
	 * @param string $post_type The CPT slug.
	 * @return array The complete, structured settings array.
	 */
	public function get_all( string $post_type ): array {
		$all_settings = [];

		foreach ( array_keys( $this->handler_classes ) as $group ) {
			$handler = $this->get_handler( $group );
			if ( ! $handler ) {
				continue;
			}

			$settings = $handler->get( $post_type );

			if ( $group === 'general' ) {
				// General settings are merged into the top level of the array.
				$all_settings = array_merge( $all_settings, $settings );
			} else {
				// All other settings are added as a nested array.
				$all_settings[ $group .'_form_builder' ] = $settings;
			}
		}

//		print_r($all_settings);exit;

		return $all_settings;
	}

	/**
	 * Saves all settings from a single, combined array.
	 *
	 * It iterates through its known handlers, checks for the existence of a
	 * matching key in the input array, and saves the data. General settings
	 * are what remains after all other groups are stripped out.
	 *
	 * @param string $post_type The CPT slug.
	 * @param array  $all_settings The complete array of new settings from the framework.
	 * @return bool Always returns true, assuming the framework handles success/error state.
	 */
	public function save_all( string $post_type, array $all_settings ): bool {

		$group_name       = '';
		$group_key        = '';
		$is_partial_save  = ! empty( $_POST['is_partial_save'] );
		$general_settings = $all_settings; // Start with everything for the general handler.
		$save_results     = [];

		if ( $is_partial_save && ! empty( $_POST['settings'] ) ) {
			$raw_settings = json_decode( wp_unslash( $_POST['settings'] ?? '' ), true ) ?: [];
			$group_name   = ! empty( $raw_settings ) ? array_key_first( $raw_settings ) : '';
			$group_key    = str_replace( '_form_builder', '', $group_name );;
		}


		if ( $group_key && ! empty( $this->handler_classes[ $group_key ] ) ) {
			$handler = $this->get_handler( $group_key );
			if ( $handler ) {
//				print_r( $all_settings[ $group_name ] );
//				echo $group_key.'#####'.$post_type;exit;
				$save_results[ $group_key ] = $handler->save( $post_type, $all_settings[ $group_name ] );
			}

		} else {
			$other_handlers = array_diff( array_keys( $this->handler_classes ), [ 'general' ] );

			foreach ( $other_handlers as $other_handler ) {
				unset( $general_settings[ $other_handler . '_form_builder' ] );
			}

			// Now, save the remaining settings using the 'general' handler.
			$general_handler = $this->get_handler( 'general' );
			if ( $general_handler ) {
				$save_results['general'] = $general_handler->save( $post_type, $general_settings );
			}

		}


		// The framework likely doesn't need a specific return, but we'll be thorough.
		// Returns true if any of the save operations were successful.
		return in_array( true, $save_results, true );
	}

	/**
	 * Lazily instantiates and returns the correct handler for a given group.
	 *
	 * @param string $group The settings group key.
	 * @return PersistenceHandlerInterface|null
	 */
	private function get_handler( string $group ): ?PersistenceHandlerInterface {
		if ( isset( $this->instances[ $group ] ) ) {
			return $this->instances[ $group ];
		}

		if ( ! isset( $this->handler_classes[ $group ] ) || ! class_exists( $this->handler_classes[ $group ] ) ) {
			return null;
		}

		$class_name = $this->handler_classes[ $group ];
		$instance = new $class_name();
		$this->instances[ $group ] = $instance;

		return $instance;
	}
}
