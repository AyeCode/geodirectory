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


return array(
	'id'           => 'status_report',
	'name'         => __( 'System Status', 'wp-ayecode-settings-framework' ),
	'icon'         => 'fa-solid fa-server',
	'type'         => 'custom_page',
	'ajax_content' => 'status_report',
	'searchable'   => array( 'report', 'system', 'status', 'support', 'data' ),

);



