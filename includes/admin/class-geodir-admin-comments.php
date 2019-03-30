<?php
/**
 * Display notices in admin.
 *
 * @author      AyeCode Ltd
 * @category    Admin
 * @package     GeoDirectory/Admin
 * @version     2.0.0
 * @info        Uses GeoDir_Admin_Comments class as a base.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GeoDir_Admin_Comments Class.
 */
class GeoDir_Admin_Comments {

	/**
	 * Initiate the admin comments class.
	 */
	public static function init() {
		if ( get_option( 'geodirectory_version' ) ) {
			add_filter( 'comment_row_actions', array(__CLASS__,'add_meta_row'), 11, 1 );

			add_action( 'add_meta_boxes_comment', array(__CLASS__,'add_meta_box') );
		}
	}

	/**
	 * Adds comment rating meta box.
	 *
	 * Adds meta box to Comments -> Edit page using hook {@see 'add_meta_boxes_comment'}.
	 *
	 * @param object $comment The comment object.
	 */
	public static function add_meta_box() {
		add_meta_box( 'gd-comment-rating', __( 'Comment Rating', 'geodirectory' ), array('GeoDir_Comments','rating_input'), 'comment', 'normal', 'high' );
	}
	
	/**
	 * Add the comment meta fields to the comments admin page.
	 *
	 * Adds rating stars below each comment of the WP Admin Dashboard -> Comments page.
	 *
	 * @param $a
	 *
	 * @return mixed
	 */
	public static function add_meta_row( $a ) {
		global $comment;

		$rating = geodir_get_comment_rating( $comment->comment_ID );
		if ( $rating != 0 ) {
			echo geodir_get_rating_stars( $rating, $comment->comment_ID );
		}

		return $a;
	}


}

GeoDir_Admin_Comments::init();
