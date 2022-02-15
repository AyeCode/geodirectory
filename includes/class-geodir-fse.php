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

//		add_filter( str_replace( '-', '', $template_type ) . '_template', 'gutenberg_override_query_template', 20, 3 );
//		add_filter('archive_gd_place_template', 'gutenberg_override_query_template', 20, 3 );

//		add_action('wp',array( $this, 'x' ));
	}

	function gutenberg_override_query_template( $template, $type, $templates ) {
		global $_wp_current_template_content;

		echo '###'.$template;
		echo '###'.$type;

		print_r( $templates );exit;
	}

	public function x(){
		print_r( gutenberg_get_template_type_slugs() );exit;
	}
	
	public function default_template_types( $templates ){



		$templates['single-gd_place']  = array(
			'title'       => _x( 'Single Place', 'Template name', 'geodirectory' ),
			'description' => __( 'Template used to display a single GeoDirectory Place post.', 'geodirectory' ),
		);

		$templates['archive-gd_place']  = array(
			'title'       => _x( 'Archive Place', 'Template name', 'geodirectory' ),
			'description' => __( 'Template used to display a Archive GeoDirectory Place post.', 'geodirectory' ),
		);
//		print_r( $templates );exit;
		return $templates;
	}



}
new GeoDir_FSE();
