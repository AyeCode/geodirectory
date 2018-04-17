<?php
/**
 * Add widget settings.
 *
 * @author      GeoDirectory
 * @category    Admin
 * @package     GeoDirectory/Admin/Widgets
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GeoDir_Admin_Status Class.
 */
class GeoDir_Admin_Widgets {

	public static function init(){
		add_filter( 'wp_super_duper_options', array(__CLASS__, 'add_show_hide_widget_options') );
	}

	public static function add_show_hide_widget_options($options){

		if(isset($options['textdomain']) && $options['textdomain'] == GEODIRECTORY_TEXTDOMAIN){
			$gd_wgt_showhide = isset($options['widget_ops']['gd_wgt_showhide']) && $options['widget_ops']['gd_wgt_showhide'] ? $options['widget_ops']['gd_wgt_showhide'] : '';
			$gd_wgt_restrict = isset($options['widget_ops']['gd_wgt_restrict']) && $options['widget_ops']['gd_wgt_restrict'] ? $options['widget_ops']['gd_wgt_restrict'] : '';
			$options['arguments']['gd_wgt_showhide'] = array(
				'title' => __('Show / Hide Options:', 'geodirectory'),
				'desc' => __('How the item should be positioned on the page.', 'geodirectory'),
				'type' => 'select',
				'options'   =>  array(
					"show" => __('Show everywhere', 'geodirectory'),
					"gd" => __('Show only on GD pages', 'geodirectory'),
					"show_on" => __('Show on selected pages', 'geodirectory'),
					"hide_on" => __('Hide on selected pages', 'geodirectory'),
				),
				'default' => $gd_wgt_showhide,
				'desc_tip' => true,
				'advanced' => false
			);

			$options['arguments']['gd_wgt_restrict'] = array(
				'title' => __('GD Pages:', 'geodirectory'),
				'desc' => __('How the item should be positioned on the page.', 'geodirectory'),
				'type' => 'select',
				'multiple' => true,
				'options'   =>  array(
					"gd-add-listing" => __('Add Listing Page', 'geodirectory'),
					"gd-author" => __('Author Page', 'geodirectory'),
					"gd-detail" => __('Listing Detail Page', 'geodirectory'),
					"gd-location" => __('Location Page', 'geodirectory'),
					"gd-pt" => __('Post Type Archive', 'geodirectory'),
					"gd-search" => __('Search Page', 'geodirectory'),
					"gd-listing" => __('Taxonomies Page', 'geodirectory'),
				),
				'default' => $gd_wgt_restrict,
				'desc_tip' => true,
				'advanced' => false,
				'element_require' => '[%gd_wgt_showhide%]=="show_on" || [%gd_wgt_showhide%]=="hide_on"',
			);
		}
		return $options;
	}

}
