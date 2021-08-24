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

    /**
     * Init.
     *
     * @since 2.0.0
     */
	public static function init(){
		//add_filter( 'wp_super_duper_options', array(__CLASS__, 'add_show_hide_widget_options') );
		add_filter( 'wp_super_duper_arguments', array(__CLASS__, 'add_show_hide_widget_options'), 10, 2 );
	}

    /**
     * Add Widget hide show options.
     *
     * @since 2.0.0
     *
     * @param array $arguments {
     *      An array widget arguments.
     *
     *      @type array $gd_wgt_showhide {
     *          An array widget hide show options.
     *
     *          @type string $title Widget hide show title.
     *          @type string $desc Widget description.
     *          @type string $type Widget type.
     *          @type array $options {
     *              An array options.
     *
     *              @type string $show Show everywhere.
     *              @type string $gd Show Only GD pages.
     *              @type string $show_on Show on selected pages.
     *              @type string $hide_on Hide on selected pages.
     *          }
     *          @type string $default Default widget value.
     *          @type bool $desc_tip Description tooltip.
     *          @type bool $advanced Widget advanced option.
     *      }
     *      @type array $gd_wgt_restrict {
     *          An array Widget restrict options.
     *
     *          @type string $title Title.
     *          @type string $desc Description.
     *          @type string $type Type.
     *          @type bool $multiple Multiple option.
     *          @type array $options {
     *              An array widget restrict options.
     *
     *              @type string $gd-add-listing Add listing title.
     *              @type string $gd-author Author page title.
     *              @type string $gd-detail Listing detail page title.
     *              @type string $gd-location Location page title.
     *              @type string $gd-pt Post type title.
     *              @type string $gd-search Search title.
     *              @type string $gd-listing listing title.
     *          }
     *          @type string $default Default option value.
     *          @type bool $desc_tip Description tooltip.
     *          @type bool $advanced Advanced value.
     *          @type string $element_require Require element value.
     *     }
     *
     * }
     *
     * @param array $options {
     *      An array widget options.
     *
     *      @type string $gd_wgt_showhide Widget hide show value.
     *      @type string $gd_wgt_restrict Widget restrict value.
     * }
     * @return array $arguments.
     */
	public static function add_show_hide_widget_options( $arguments, $options ) {
		if ( ( isset( $options['textdomain'] ) && $options['textdomain'] == GEODIRECTORY_TEXTDOMAIN ) || ( isset( $options['widget_ops']['gd_wgt_restrict'] ) ) ) {
			$gd_wgt_showhide = isset($options['widget_ops']['gd_wgt_showhide']) && $options['widget_ops']['gd_wgt_showhide'] ? $options['widget_ops']['gd_wgt_showhide'] : '';
			$gd_wgt_restrict = isset($options['widget_ops']['gd_wgt_restrict']) && $options['widget_ops']['gd_wgt_restrict'] ? $options['widget_ops']['gd_wgt_restrict'] : '';
			$arguments['gd_wgt_showhide'] = array(
				'title' => __('Show / Hide Options:', 'geodirectory'),
				'desc' => __('Where the widget should be shown.', 'geodirectory'),
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

			$arguments['gd_wgt_restrict'] = array(
				'title' => __('GD Pages:', 'geodirectory'),
				'desc' => __('The pages that should be included/excluded.', 'geodirectory'),
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
				'element_require' => '( [%gd_wgt_showhide%]=="show_on" || [%gd_wgt_showhide%]=="hide_on" )',
			);
		}
		return $arguments;
	}

}
