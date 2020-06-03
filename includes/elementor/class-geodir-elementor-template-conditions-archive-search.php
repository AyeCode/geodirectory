<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class GeoDir_Elementor_Template_Conditions_Archive_Search extends \ElementorPro\Modules\ThemeBuilder\Conditions\Condition_Base {
	public static function get_type() {
		return 'geodirectory-archive';
	}

	public function get_name() {
		return 'geodirectory_archive_search';
	}

	public static function get_priority() {
		return 45;
	}

	public function get_label() {
		return __( 'All GD Search Results', 'geodirectory' );
	}

	public function check( $args ) {
		return geodir_is_page('search');
	}

	public function register_sub_conditions() {

		// CPT Archives
		$post_types = geodir_get_posttypes('array');
		foreach($post_types as $key => $post_type){

			$condition = new GeoDir_Elementor_Template_Conditions_Archive_Search_CPT( [
				'post_type' => $key,
				'cpt'       => $post_type,
			] );

			$this->register_sub_condition( $condition );

		}

	}
}