<?php
/**
 * GeoDirectory Dynamic CPT Settings Page Handler
 */

namespace AyeCode\GeoDirectory\Admin\Pages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AyeCode\SettingsFramework\Settings_Framework;

final class Dynamic_CPT_Settings extends Settings_Framework {

	private $cpt;

	public function __construct( $cpt_slug, $cpt_object ) {
		$this->cpt = $cpt_object;
//		print_r( $cpt_object );exit;
		$this->option_name = 'geodir_' . $cpt_slug . '_settings';
		$this->parent_slug = 'edit.php?post_type=' . $cpt_slug;
		$this->page_slug   = $cpt_slug . '-settings';
		$this->page_title  = sprintf( __( '%s Settings', 'geodirectory' ), $this->cpt->labels->singular_name );
		$this->menu_title  = __( 'Settings', 'geodirectory' );
		$this->plugin_name = '<span class="fa-stack fa-1x me-1"><i class="fas fa-circle fa-stack-2x text-light"></i><i style="color: #ff8333 !important;" class="fas fa-globe-americas fa-stack-2x text-primary "></i></span> <span class="fw-normal fs-4"><span style="color: #ff8333 !important;">Geo</span>Directory</span>';

		parent::__construct();
	}

	protected function get_config() {
		// Define the list of settings files to be included.
		$settings_files = [
			'general' => 'config/cpt-settings/general.php',
			'fields'  => 'config/cpt-settings/fields.php',
			'sorting' => 'config/cpt-settings/sorting.php',
			'tabs'    => 'config/cpt-settings/tabs.php',
		];

		$sections = [];

		// Define the base path for the settings files.
		$base_path = dirname( __FILE__ ) . '/../';

		// Loop through the files, include them, and collect their returned section arrays.
		foreach ( $settings_files as $file_path ) {
			$full_path = $base_path . $file_path;
			if ( file_exists( $full_path ) ) {
				$sections[] = include( $full_path );
			}
		}

		// The final configuration array required by the framework.
		return [ 'sections' => $sections ];
	}
}
