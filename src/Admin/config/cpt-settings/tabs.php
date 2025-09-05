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
	'id'       => 'tabs_form_builder', // This will be the key where the form structure is saved
	'name'     => __( 'Tabs', 'geodirectory' ),
	'icon'     => 'fa-solid fa-ellipsis',
	'type'     => 'form_builder',
	'nestable' => true,
];
