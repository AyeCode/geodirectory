<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Geodir_Bricks_Search extends Super_Duper_Bricks_Element {

	public $widget;

	public function __construct( $element = null ) {

		$this->widget = new \GeoDir_Widget_Search();

		parent::__construct($element);
	}

	/**
	 * A way to remove some settings by keys.
	 *
	 * @return array
	 */
	public function sd_remove_arguments()
	{
		// remove wrapper options as they are included in bricks
		return [
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
			'rounded_size',
			'rounded_size_md',
			'rounded_size_lg',
			'css_class',
		];
	}

}
