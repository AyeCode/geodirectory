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

return [
	'id'    => 'listing_form_builder', // This will be the key where the form structure is saved
	'name'  => __( 'Fields', 'geodirectory' ),
	'icon'  => 'fa-solid fa-bars',
	'type'  => 'form_builder',
	'templates' => [
		[
			'group_title' => 'Standard Fields',
			'options' => [
				[
					'title'   => 'Text',
					'icon'    => 'fa-solid fa-font',
					'fields' => [
						[ 'id' => 'type', 'type' => 'hidden', 'default' => 'text' ],
						[ 'id' => 'label', 'type' => 'text', 'label' => 'Label', 'default' => 'New Text Field' ],
						[ 'id' => 'icon', 'type' => 'icon', 'label' => 'Icon', 'default' => 'fa-solid fa-font' ],
						[ 'id' => 'description', 'type' => 'textarea', 'label' => 'Description', 'rows' => 2 ],
						[ 'id' => 'placeholder', 'type' => 'text', 'label' => 'Placeholder' ],
						[ 'id' => 'is_required', 'type' => 'toggle', 'label' => 'Is Required' ],
					]
				],
				[
					'title'   => 'Textarea',
					'icon'    => 'fa-solid fa-paragraph',
					'fields' => [
						[ 'id' => 'type', 'type' => 'hidden', 'default' => 'textarea' ],
						[ 'id' => 'label', 'type' => 'text', 'label' => 'Label', 'default' => 'New Textarea' ],
						[ 'id' => 'icon', 'type' => 'icon', 'label' => 'Icon', 'default' => 'fa-solid fa-paragraph' ],
						[ 'id' => 'description', 'type' => 'textarea', 'label' => 'Description', 'rows' => 2 ],
						[ 'id' => 'is_required', 'type' => 'toggle', 'label' => 'Is Required' ],
					]
				],
				[
					'title'   => 'Select',
					'icon'    => 'fa-solid fa-list-ul',
					'fields' => [
						[ 'id' => 'type', 'type' => 'hidden', 'default' => 'select' ],
						[ 'id' => 'label', 'type' => 'text', 'label' => 'Label', 'default' => 'New Select' ],
						[ 'id' => 'icon', 'type' => 'icon', 'label' => 'Icon', 'default' => 'fa-solid fa-list-ul' ],
						[ 'id' => 'description', 'type' => 'textarea', 'label' => 'Description', 'rows' => 2 ],
						[ 'id' => 'options', 'type' => 'textarea', 'label' => 'Options', 'description' => 'Enter one option per line in `key : value` format.', 'default' => 'opt1 : Option 1' ],
						[ 'id' => 'is_required', 'type' => 'toggle', 'label' => 'Is Required' ],
					]
				],
			]
		],
	]
];
