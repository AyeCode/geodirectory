<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class GeoDir_Elementor_Template_Conditions_Archive_Item extends \ElementorPro\Modules\ThemeBuilder\Conditions\Condition_Base {

	public function __construct( array $data = [] ) {
		parent::__construct( $data );
	}

	public static function get_type() {
		return 'geodirectory-archive-item';
	}

	public function get_name() {
		return 'geodirectory_archive_item';
	}

	public static function get_priority() {
		return 40;
	}

	public function get_label() {
		return 'Geodirectory Archive item';
	}

	public function get_all_label() {
		return __( 'No conditions needed. Template used by Elementor Post and Archive widgets skin conditions.', 'geodirectory' );
	}

	public function register_sub_conditions() {

		// we need to set a condition for the main title to show.
		$this->register_sub_condition( new GeoDir_Elementor_Template_Conditions_Archive_Item_None() );

	}

	public function check( $args ) {
		return false;
	}
}