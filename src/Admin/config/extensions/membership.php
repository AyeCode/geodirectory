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
	'id' => 'membership',
	'name' => 'Membership',
	'icon' => 'fa-solid fa-id-card',
	'type' => 'action_page',
	'button_text' => 'Save & Activate Key',
	'ajax_action' => 'save_membership_key',
	'fields' => [
		[
			'id'      => 'membership_key',
			'type'    => 'text',
			'label'   => __( 'Membership Key', 'ayecode-settings-framework' ),
			'description'    => __( 'Enter your membership key to enable one-click installations on local sites.', 'ayecode-settings-framework' ),
		]
	]
];
