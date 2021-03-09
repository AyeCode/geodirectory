<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class GeoDir_Elementor_Template_Conditions_Archive extends \ElementorPro\Modules\ThemeBuilder\Conditions\Condition_Base {

//	private $post_type = 'product';
//	private $post_taxonomies;

	public function __construct( array $data = [] ) {
//		$taxonomies = get_object_taxonomies( $this->post_type, 'objects' );
//		$this->post_taxonomies = wp_filter_object_list( $taxonomies, [
//			'public' => true,
//			'show_in_nav_menus' => true,
//		] );

		parent::__construct( $data );
	}

	public static function get_type() {
		return 'geodirectory-archive';
	}

	public function get_name() {
		return 'geodirectory_archive';
	}

	public static function get_priority() {
		return 40;
	}

	public function get_label() {
		return __( 'GeoDirectory Archive', 'geodirectory' );
	}

	public function get_all_label() {
		return __( 'All GD Archives', 'geodirectory' );
	}

	public function register_sub_conditions() {

		// Author Archives
		$this->register_sub_condition( new GeoDir_Elementor_Template_Conditions_Archive_Author() );

		// Search Archives
		$this->register_sub_condition( new GeoDir_Elementor_Template_Conditions_Archive_Search() );


		// CPT Archives
		$post_types = geodir_get_posttypes('array');
		foreach($post_types as $key => $post_type){

			$condition = new \ElementorPro\Modules\ThemeBuilder\Conditions\Post_Type_Archive( [
				'post_type' => $key,
			] );

			$this->register_sub_condition( $condition );

		}

	}

	public function check( $args ) {
//		return is_woocommerce() || Module::is_product_search();
//		return geodir_is_geodir_page();
		return geodir_is_page('archive') || geodir_is_page('post_type') || geodir_is_page('search');
	}
}