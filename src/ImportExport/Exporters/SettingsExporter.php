<?php
/**
 * This file contains the class for exporting plugin Settings.
 *
 * @author   AyeCode
 * @category Exporters
 * @package  GeoDirectory\ImportExport\Exporters
 * @version  1.0.0
 */

namespace AyeCode\GeoDirectory\ImportExport\Exporters;

/**
 * SettingsExporter Class
 *
 * Handles the logic for gathering all relevant GeoDirectory settings
 * into an array, ready to be encoded as a JSON file for export.
 */
class SettingsExporter
{
	/**
	 * Gathers all exportable settings into an array.
	 *
	 * It retrieves all GeoDirectory options and then unsets specific,
	 * environment-dependent keys that should not be transferred
	 * between sites, such as registered post types and taxonomies.
	 *
	 * @return array The filtered settings array.
	 */
	public function getSettingsData(): array
	{
		$settings = geodir_get_settings();

		// Unset taxonomies and post_types, as these are dependent on which
		// addons are active and should be regenerated on the target site.
		unset($settings['taxonomies']);
		unset($settings['post_types']);

		return $settings;
	}
}
