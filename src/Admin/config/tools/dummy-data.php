<?php
/**
 * V3 Tools Settings for GeoDirectory
 *
 * @package     GeoDirectory
 * @since       3.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


$cpts   = geodir_get_posttypes( 'options-plural' );
$fields = [];
if ( ! empty( $cpts ) ) {
	foreach ( $cpts as $cpt => $cpt_name ) {
//		$fields[] = [
//			'id'           => 'dummy_data_cpt_' . $cpt,
//			'type'         => 'action_button',
//			'label'        => $cpt_name,
//			//__( 'Search & Replace Custom Field Value', 'geodirectory' ),
//			//'description'  => __( 'Search & replace custom field values in post type details database table for SELECT, MULTISELECT, RADIO, CHECKBOX field types.', 'geodirectory' ),
//			'button_text'  => __( 'Insert posts', 'geodirectory' ),
//			'button_class' => 'btn-primary',
//			'ajax_action'  => 'search_replace_cf',
//			'custom_desc'  => $output,
//		];

		$fields[] = [
			'id'     => 'dummy_data_cpt_' . esc_attr( $cpt ),
			'type'   => 'group',
//			'class'  => 'row',
			'label'  => esc_attr( $cpt_name ),
//			'description' => __( 'Quickly populate your site with sample content for testing.', 'geodirectory' ),
			'fields' => [
				[
					'id'      => 'post_type_' . esc_attr( $cpt ),
					'type'    => 'hidden',
					'label'   => __( 'Post type', 'geodirectory' ),
					'default' => esc_attr($cpt),
					'show_if'  => 'false', // hide the wrapper
					'searchable' => false,
				],
				[
					'id'      => 'data_type_' . esc_attr( $cpt ),
					'type'    => 'select',
					'options' => self::dummy_data_types_for_import( $cpt ),
					'label'   => __( 'Data type', 'geodirectory' ),
					'default' => geodir_get_option( $cpt . '_dummy_data_type' ),
					'searchable' => false,
				],
				[
					'id'      => 'number_' . esc_attr( $cpt ),
					'type'    => 'select',
					'label'   => __( 'Post count', 'geodirectory' ),
					'options' => array_combine( range( 1, 30 ), range( 1, 30 ) ),
					'default' => 30,
					'show_if' => '[%dummy_data_btn_'. esc_attr( $cpt ).'%] == false',
					'searchable' => false,
//					'extra_attributes' => [
//						'disabled' => 'disabled'
//					]
				],
				[
					'id'      => 'update_templates_' . esc_attr( $cpt ),
					'type'    => 'select',
					'label'   => __( 'Post templates', 'geodirectory' ),
					'options' => [
						'1' =>  __("Update page templates","geodirectory"),
						'0' =>  __("Do not update page templates","geodirectory"),
					],
					'show_if' => '[%dummy_data_btn_'. esc_attr( $cpt ).'%] == false',
					'default' => 1,
					'searchable' => false,
//					'extra_attributes' => [
//						'disabled' => 'disabled'
//					]
				],
				[
					'id'             => 'dummy_data_btn_'. esc_attr( $cpt ),
					'type'           => 'action_button',
					'label'          => ' ',
					'has_dummy_data' => self::has_dummy_data( $cpt ),
					'toggle_config'  => [
						'insert' => [
							'button_text' => __( 'Insert posts', 'geodirectory' ),
							'ajax_action' => 'dummy_data_install',
						],
						'remove' => [
							'button_text'  => 'Remove Places',
							'button_class' => 'btn-danger',
							'ajax_action'  => 'dummy_data_uninstall',
						],
					]
				]
			]
		];
	}
}
//print_r($fields);exit;

return array(
	'id'     => 'dummy-data',
	'type'   => 'tool_page',
	'name'   => __( 'Dummy Data', 'geodirectory' ),
	'description' => __( 'Quickly populate your site with sample content for testing.', 'geodirectory' ),
	'icon'   => 'fa-solid fa-list-ul',
	'fields' => $fields
);


