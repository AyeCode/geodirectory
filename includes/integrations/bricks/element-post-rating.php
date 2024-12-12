<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Geodir_Bricks_Post_Rating extends Super_Duper_Bricks_Element {

	public $widget;

	public function __construct( $element = null ) {

		$this->widget = new \GeoDir_Widget_Post_Rating();

		parent::__construct($element);
	}

	/**
	 * A way to remove some settings by keys.
	 *
	 * @return array
	 */
	public function sd_remove_arguments()
	{

		$args = [
			'bg',
			'mt',
			'mr',
			'mb',
			'ml',
			'pt',
			'pr',
			'pb',
			'pl',
			'border',
			'rounded',
			'rounded_size',
			'shadow',
			'display',
			'list_hide',
			'list_hide_secondary',
			'css_class',
			'border_type',
			'border_width',
			'border_opacity',
			'position',
			'alignment',
		];

		// add sizes
		foreach ( $args as $arg ) {
			$args[] = $arg . '_md';
			$args[] = $arg . '_lg';
		}

		return $args;

	}

}
