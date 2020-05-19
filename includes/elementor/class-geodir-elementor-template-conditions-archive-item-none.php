<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class GeoDir_Elementor_Template_Conditions_Archive_Item_None extends \ElementorPro\Modules\ThemeBuilder\Conditions\Condition_Base {
	public static function get_type() {
		return 'geodirectory-archive-itemx'; // deliberate mistake as this is just a placeholder message
	}

	public function get_name() {
		return 'geodirectory_archive_itemx'; // deliberate mistake as this is just a placeholder message
	}

	public static function get_priority() {
		return 50;
	}

	public function get_label() {
		return __( 'No conditions needed. Template used by Elementor Post and Archive widgets skin conditions.', 'geodirectory' );
	}

	public function check( $args ) {
		return false;
	}
}