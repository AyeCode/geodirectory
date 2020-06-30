<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class GeoDir_Elementor_Template_Conditions_Archive_Search_CPT extends \ElementorPro\Modules\ThemeBuilder\Conditions\Condition_Base {

	private $post_type;
	private $cpt;

	public function __construct( $data ) {
		parent::__construct();

		$this->post_type = $data['post_type'];
		$this->cpt = $data['cpt'];
	}
	
	public static function get_type() {
		return 'geodirectory-archive_search';
	}

	public function get_name() {
		return "geodirectory_archive_search_".$this->post_type;
	}

	public static function get_priority() {
		return 45;
	}

	public function get_label() {
		$name = !empty($this->cpt['labels']['name']) ? esc_attr($this->cpt['labels']['name']) : esc_attr($this->post_type);
		return sprintf( __( '%s Search Results', 'geodirectory' ),$name);
	}

	public function check( $args ) {
		$searched_cpt = geodir_get_current_posttype();

		if($searched_cpt && $searched_cpt == $this->post_type){
			return true;
		}
		return false;
	}

}