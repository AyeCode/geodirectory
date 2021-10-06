<?php
/**
 * GeoDirectory Full Site Editing
 *
 * @author   AyeCode
 * @category Full Site Editing
 * @package  GeoDirectory
 * @since    2.0.x
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GeoDir_FSE {


	/**
	 * Setup class.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		// Add query vars
		add_filter( 'default_template_types', array( $this, 'default_template_types' ), 10 );

	}

	public function default_template_types( $templates ){



		$templates['single-gd_place']  = array(
			'title'       => _x( 'Single Place', 'Template name', 'geodirectory' ),
			'description' => __( 'Template used to display a single GeoDirectory Place post.', 'geodirectory' ),
		);
//		print_r( $templates );exit;
		return $templates;
	}



}
new GeoDir_FSE();
