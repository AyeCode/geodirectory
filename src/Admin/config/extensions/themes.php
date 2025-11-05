<?php
/**
 * V3 GeoDirectory Addons
 *
 * @package     GeoDirectory
 * @since       3.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


return [
	'id'    => 'themes',
	'name'  => 'Themes',
	'icon'  => 'fa-solid fa-palette',
	'type'  => 'extension_list_page',
	'api_config' => [ 'category' => 'themes','item_type' => 'theme' ],
];
