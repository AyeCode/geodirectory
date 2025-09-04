<?php
/**
 * V3 SEO Settings for GeoDirectory
 *
 * @package     GeoDirectory
 * @since       3.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Import our new factory class to build the field settings.
use AyeCode\GeoDirectory\Admin\Utils\TabFieldFactory;

return [
	'id'    => 'tabs_form_builder', // This will be the key where the form structure is saved
	'name'  => __( 'Tabs', 'geodirectory' ),
	'icon'  => 'fa-solid fa-ellipsis',
	'type'  => 'form_builder',
	'nestable' => true,
	//'templates' => TabFieldFactory::get,
//
//	'templates' => [
//		[
//			'group_title' =>  __( 'Standard Fields', 'geodirectory' ),
//			'options' => [
//				[
//					'title'   => 'Text',
//					'id'      => 'text',
//					'icon'    => 'fa-solid fa-font',
//					'fields'  => TabFieldFactory::build([
//						'_uid',
//						'label',
//						'type',
////						'tab_icon',
////						'tab_type',
////						'tab_content',
////						'tab_layout',
////						'tab_',
////						'tab_',
//
//					]),
//				],
//				[
//					'title'   => 'Textarea',
//					'id'      => 'textarea',
//					'icon'    => 'fa-solid fa-paragraph',
//					'fields' => [
//						[ 'id' => 'type', 'type' => 'hidden', 'default' => 'textarea' ],
//						[ 'id' => 'label', 'type' => 'text', 'label' => 'Label', 'default' => 'New Textarea' ],
//						[ 'id' => 'icon', 'type' => 'icon', 'label' => 'Icon', 'default' => 'fa-solid fa-paragraph' ],
////						[ 'id' => 'description', 'type' => 'textarea', 'label' => 'Description', 'rows' => 2 ],
//						[ 'id' => 'is_required', 'type' => 'toggle', 'label' => 'Is Required' ],
//					]
//				],
//				[
//					'title'   => 'Select',
//					'id'      => 'select',
//					'icon'    => 'fa-solid fa-list-ul',
//					'fields' => [
//						[ 'id' => 'type', 'type' => 'hidden', 'default' => 'select' ],
//						[ 'id' => 'label', 'type' => 'text', 'label' => 'Label', 'default' => 'New Select' ],
////						[ 'id' => 'icon', 'type' => 'icon', 'label' => 'Icon', 'default' => 'fa-solid fa-list-ul' ],
////						[ 'id' => 'description', 'type' => 'textarea', 'label' => 'Description', 'rows' => 2 ],
////						[ 'id' => 'options', 'type' => 'textarea', 'label' => 'Options', 'description' => 'Enter one option per line in `key : value` format.', 'default' => 'opt1 : Option 1' ],
////						[ 'id' => 'is_required', 'type' => 'toggle', 'label' => 'Is Required' ],
//					]
//				],
//			]
//		],
//	]
];
