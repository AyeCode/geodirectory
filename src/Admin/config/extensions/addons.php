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
	'id'    => 'addons',
	'name'  => 'Addons',
	'icon'  => 'fa-solid fa-puzzle-piece',
	'type'  => 'extension_list_page',
	'api_config' => [ 'category' => 'addons','item_type' => 'plugin' ],
];
