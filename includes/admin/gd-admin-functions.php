<?php
/**
 * GeoDirectory Admin Functions
 *
 * @author   AyeCode Ltd
 * @category Core
 * @package  GeoDirectory/Admin/Functions
 * @version  2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}



/**
 * Get all GeoDirectory screen ids.
 *
 * @return array
 */
function gd_get_screen_ids() {

	$screen_ids   = array(
		'geodirectory_page_gd-settings',
		'geodirectory_page_gd-status',
		'geodirectory_page_gd-addons',
	);


	// Add the CPT screens
	$post_types = geodir_get_posttypes( 'names' );
	if(!empty($post_types)){
		foreach ($post_types as $post_type){
			$screen_ids[] = $post_type; // CPT add new
			$screen_ids[] = 'edit-'.$post_type; // CPT view screen
			$screen_ids[] = 'edit-'.$post_type.'_tags'; // CPT tags screen
			$screen_ids[] = 'edit-'.$post_type.'category'; // CPT category screen
			$screen_ids[] = $post_type.'_page_gd-cpt-settings'; // CPT settings page
		}
	}

	return apply_filters( 'geodirectory_screen_ids', $screen_ids );
}