<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class GeoDir_Elementor_Template_Conditions_Single extends \ElementorPro\Modules\ThemeBuilder\Conditions\Condition_Base {

	public static function get_type() {
		return 'geodirectory';
	}

	public function get_name() {
		return 'geodirectory';
	}

	public function get_label() {
		return __( 'GeoDirectory Single', 'geodirectory' );
	}

	public function get_all_label() {
		return __( 'All GD Single', 'geodirectory' );
	}

	public function register_sub_conditions() {


		$post_types = geodir_get_posttypes('array');
		foreach($post_types as $key => $post_type){
//			// Set root CPT
//			$archive_options [ 'post_type_archive/' . $key ] = sprintf(__('%s Archive','geodirectory'),$post_type['labels']['name']);
//
//			// Set taxonomies
//			$archive_options [ 'taxonomy/' . $key. 'category' ] = sprintf(__('%s Categories Archive','geodirectory'),$post_type['labels']['singular_name']);
//			$archive_options [ 'taxonomy/' . $key. '_tags' ] = sprintf(__('%s Tags Archive','geodirectory'),$post_type['labels']['singular_name']);
			
			
			// Set archives
//			$gd_archive = new GeoDir_Elementor_Template_Archive_Conditions([
//				'post_type' => $key,
//			]);
//			$this->register_sub_condition( $gd_archive );
			
			// Set single
			$gd_single = new \ElementorPro\Modules\ThemeBuilder\Conditions\Post( [
				'post_type' => $key,
			] );

			$this->register_sub_condition( $gd_single );

		}
		
		
//		$gd_archive = new GeoDir_Elementor_Template_Archive_Conditions();
//
//		$gd_single = new \ElementorPro\Modules\ThemeBuilder\Conditions\Post( [
//			'post_type' => 'gd_place',
//		] );
//
//		$this->register_sub_condition( $gd_archive );
//		$this->register_sub_condition( $gd_single );
	}

	public function check( $args ) {
//		return is_woocommerce() || Module::is_product_search();
		return geodir_is_page('single');
	}
}