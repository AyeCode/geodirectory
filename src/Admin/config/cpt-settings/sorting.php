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
	'id'          => 'sorting_form_builder', // This will be the key where the form structure is saved
	'name'        => __( 'Sorting', 'geodirectory' ),
	'icon'        => 'fa-solid fa-sort',
	'type'        => 'form_builder',
	'nestable'    => true,
	'default_top' => true,
];
