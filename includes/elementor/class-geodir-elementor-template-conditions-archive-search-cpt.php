<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class GeoDir_Elementor_Template_Conditions_Archive_Search_CPT extends \ElementorPro\Modules\ThemeBuilder\Conditions\Condition_Base {

	private $xpost_type;
//	private $cpt;

	public function __construct( $data ) {
		parent::__construct();

		$this->xpost_type = $data['post_type'];
//		$this->cpt = $data['cpt'];
	}
	
	public static function get_type() {
		return 'geodirectory-archive_search';
	}

	public function get_name() {
		return "geodirectory_archive_search/".$this->xpost_type;
	}

	public static function get_priority() {
		return 45;
	}

	public function get_label() {
//		$name = !empty($this->cpt['labels']['name']) ? esc_attr($this->cpt['labels']['name']) : esc_attr($this->post_type);
		return sprintf( __( '%s Search Results', 'geodirectory' ),$this->xpost_type);
	}

	public function check( $args ) {
//		print_r($args);exit;
		return true;
//		$searched_cpt = !empty($_REQUEST['stype']) ? esc_attr($_REQUEST['stype']) : '';
//
//		if($searched_cpt && $searched_cpt == $this->xpost_type){
//			return true;
//		}
//		return false;
	}


}