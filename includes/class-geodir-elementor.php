<?php
/**
 * GeoDirectory Elementor
 *
 * Adds compatibility for Elementor page builder.
 *
 * @author   AyeCode
 * @category Compatibility
 * @package  GeoDirectory
 * @since    2.0.0.41
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GeoDir_Elementor {

	/**
	 * Version.
	 *
	 * @var int
	 */
	const VERSION = 1;

	/**
	 * Setup class.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		// Add query vars
		//add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );

		// enqueue flexslider JS
		

		// add any extra scripts
		add_action('wp_enqueue_scripts', array( $this, 'enqueue_scripts' ),11);

	}

	public function is_elementor_preview(){
		return isset($_REQUEST['elementor-preview']) ? true : false;
	}
	
	public function enqueue_scripts(){
		if($this->is_elementor_preview()){
			GeoDir_Frontend_Scripts::enqueue_script( 'jquery-flexslider' );
		}
	}

}