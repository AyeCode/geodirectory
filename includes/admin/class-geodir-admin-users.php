<?php
/**
 * Load admin users
 *
 * @author      AyeCode Ltd
 * @category    Admin
 * @package     GeoDirectory/Admin
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'GeoDir_Admin_Users', false ) ) :

/**
 * GeoDir_Admin_Users Class.
 */
class GeoDir_Admin_Users {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		add_filter( 'manage_users_columns', array( $this, 'add_extra_user_column') );
		add_filter( 'manage_users_custom_column', array( $this, 'user_column_content') , 10, 3 );
	}

	/**
	 * Adds a new backend user column.
	 * 
	 * @param $column
	 *
	 * @return mixed
	 */
	public function add_extra_user_column( $column ) {
		$column['gd_listings'] = __('GD Listings','geodirectory');
		return $column;
	}

	/**
	 * Add the backend user column content.
	 *
	 * @param $val
	 * @param $column_name
	 * @param $user_id
	 *
	 * @return string
	 */
	function user_column_content( $val, $column_name, $user_id ) {
		switch ($column_name) {
			case 'gd_listings' :
				return $this->get_user_listings( $user_id );
				break;
			default:
		}
		return $val;
	}

	/**
	 * Get the backend user listing links.
	 *
	 * @param $user_id
	 *
	 * @return string
	 */
	public function get_user_listings($user_id){
		$output = '';
		$user_listing = geodir_user_post_listing_count( $user_id, true );
		if(empty($user_listing)){
			$output .= __('No listings','geodirectory');
		}else{
			$post_types = geodir_get_posttypes( 'array' );
			foreach($user_listing as $post_type => $count){
				if(isset($post_types[$post_type])){
					$link_url = admin_url( "edit.php?post_type=".sanitize_title_with_dashes($post_type)."&author=".absint($user_id) );
					$link_text = sprintf( __('%s ( %d )', 'geodirectory'), $post_types[$post_type]['labels']['name'], absint( $count ) );
					$output .= "<a href='$link_url' >$link_text</a></br>";
				}

			}
		}
		return $output;
	}


}

endif;
