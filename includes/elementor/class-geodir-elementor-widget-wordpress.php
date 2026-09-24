<?php
/**
 * GeoDirectory Elementor WordPress widget wrapper.
 *
 * Since Elementor v4.3.0 native WordPress widgets are hidden from the widget panel and
 * the Element Manager. This wrapper keeps the GeoDirectory widgets visible in their own
 * category while using the same widget name, so existing templates are not affected.
 *
 * @author   AyeCode
 * @category Compatibility
 * @package  GeoDirectory
 * @since    2.8.185
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GeoDir_Elementor_Widget_WordPress extends \Elementor\Widget_WordPress {

	/**
	 * Show the widget in the panel.
	 *
	 * @return bool
	 */
	public function show_in_panel() {
		return true;
	}

	/**
	 * Allow the widget to be found via panel search.
	 *
	 * @return bool
	 */
	public function hide_on_search() {
		return false;
	}

	/**
	 * Get widget categories.
	 *
	 * @return array
	 */
	public function get_categories() {
		return array( 'geodirectory' );
	}

	/**
	 * Get widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-globe';
	}

	/**
	 * Get widget keywords.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return array( 'geodir', 'geodirectory', 'gd', 'widget' );
	}
}
