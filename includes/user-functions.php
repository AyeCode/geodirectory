<?php
/**
 * GeoDirectory User Functions
 *
 * Functions for users.
 *
 * @author 		AyeCode
 * @category 	Core
 * @package 	GeoDirectory/Functions
 * @version 	2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Get user's favorite listing count.
 *
 * @since 1.0.0
 * @package GeoDirectory
 *
 * @param bool $user_id Optional. The user id to get, defaults to current user.
 *
 * @return array User listing count for each post type.
 */
function geodir_user_favourite_listing_count( $user_id = false ) {
	return GeoDir_User::get_post_type_fav_counts($user_id);
}

/**
 * Get the array of user favourites.
 *
 * @param string $user_id
 *
 * @since 1.6.24
 * @return mixed
 */
function geodir_get_user_favourites( $user_id = '' ) {
	return GeoDir_User::get_user_favs( $user_id );
}

/**
 * Get user's post listing count.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @global object $current_user Current user object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 * @return array User listing count for each post type.
 */
function geodir_user_post_listing_count($user_id = null)
{
	global $wpdb, $plugin_prefix, $current_user;
	if(!$user_id){
		$user_id = $current_user->ID;
	}

	if(!$user_id){
		return array();
	}
	
	$all_postypes = geodir_get_posttypes();

	$user_listing = array();
	foreach ($all_postypes as $ptype) {
		$total_posts = $wpdb->get_var("SELECT count( ID ) FROM " . $wpdb->prefix . "posts WHERE post_author=" . $user_id . " AND post_type='" . $ptype . "' AND ( post_status = 'publish' OR post_status = 'draft' OR post_status = 'private' )");

		if ($total_posts > 0) {
			$user_listing[$ptype] = $total_posts;
		}
	}
	
	return $user_listing;
}

/**
 * Generate a unique username.
 *
 * @param $username
 *
 * @return mixed|string
 */
function geodir_generate_unique_username( $username ) {
	static $i;
	if ( null === $i ) {
		$i = 1;
	} else {
		$i++;
	}
	if ( ! username_exists( $username ) ) {
		return $username;
	}
	$new_username = sprintf( '%s-%s', $username, $i );
	if ( ! username_exists( $new_username ) ) {
		return $new_username;
	} else {
		return call_user_func( __FUNCTION__, $username );
	}
}
