<?php
/**
 * Display notices in admin.
 *
 * @author      AyeCode Ltd
 * @category    Admin
 * @package     GeoDirectory/Admin
 * @version     2.0.0
 * @info        Uses WC_Admin_Notices class as a base.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GeoDir_Admin_Notices Class.
 */
class GeoDir_Admin_Comments {

	/**
	 * Initiate the admin comments class.
	 */
	public static function init() {
		add_filter( 'comment_row_actions', array(__CLASS__,'add_meta_row'), 11, 1 );

		add_action( 'add_meta_boxes_comment', array(__CLASS__,'add_meta_box') );

		// comment actions
		add_action( 'comment_post', array(__CLASS__,'save_rating') );
		add_action( 'wp_set_comment_status', array(__CLASS__, 'status_change'), 10, 2 );
		add_action( 'edit_comment', array(__CLASS__, 'edit_comment') );
		add_action( 'delete_comment', array(__CLASS__, 'delete_comment') );








	}


	/**
	 * Update post overall rating and rating count.
	 *
	 * @global object $wpdb WordPress Database object.
	 * @global string $plugin_prefix Geodirectory plugin table prefix.
	 *
	 * @param int $post_id The post ID.
	 * @param string $post_type The post type.
	 * @param bool $delete Depreciated since ver 1.3.6.
	 */
	public static function update_post_rating( $post_id = 0, $post_type = '', $delete = false ) {
		global $wpdb, $plugin_prefix, $comment;
		if ( ! $post_type ) {
			$post_type = get_post_type( $post_id );
		}
		$detail_table         = $plugin_prefix . $post_type . '_detail';
		$post_newrating       = geodir_get_post_rating( $post_id, 1 );
		$post_newrating_count = geodir_get_review_count_total( $post_id );

		if ( $wpdb->get_var( "SHOW TABLES LIKE '" . $detail_table . "'" ) == $detail_table ) {

			$wpdb->query(
				$wpdb->prepare(
					"UPDATE " . $detail_table . " SET
						overall_rating = %f,
						rating_count = %f
						where post_id = %d",
					array( $post_newrating, $post_newrating_count, $post_id )
				)
			);

			update_post_meta( $post_id, 'overall_rating', $post_newrating );
			update_post_meta( $post_id, 'rating_count', $post_newrating_count );
		}
		/**
		 * Called after Updating post overall rating and rating count.
		 *
		 * @since 1.0.0
		 * @since 1.4.3 Added `$post_id` param.
		 * @package GeoDirectory
		 *
		 * @param int $post_id The post ID.
		 */
		do_action( 'geodir_update_post_rating', $post_id );

	}


	/**
	 * Get review details using comment ID.
	 *
	 * Returns review details using comment ID. If no reviews returns false.
	 *
	 * @param int $comment_id The comment ID.
	 *
	 * @global object $wpdb WordPress Database object.
	 * @return bool|mixed
	 */
	public static function get_review( $comment_id = 0 ) {
		global $wpdb;

		$reatings = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM " . GEODIR_REVIEW_TABLE . " WHERE comment_id = %d",
				array( $comment_id )
			)
		);

		if ( ! empty( $reatings ) ) {
			return $reatings;
		} else {
			return false;
		}
	}

	/**
	 * Delete review details when deleting comment.
	 *
	 * @param int $comment_id The comment ID.
	 *
	 * @global object $wpdb WordPress Database object.
	 */
	function delete_comment( $comment_id ) {
		global $wpdb;

		$review_info = self::get_review( $comment_id );
		if ( $review_info ) {
			self::update_post_rating( $review_info->post_id );
		}

		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM " . GEODIR_REVIEW_TABLE . " WHERE comment_id=%d",
				array( $comment_id )
			)
		);
	}

	/**
	 * Update comment rating.
	 *
	 * @param int $comment_id The comment ID.
	 *
	 * @global object $wpdb WordPress Database object.
	 * @global string $plugin_prefix Geodirectory plugin table prefix.
	 * @global int $user_ID The current user ID.
	 */
	function edit_comment( $comment_id = 0 ) {

		global $wpdb;

		$comment_info = get_comment( $comment_id );

		$post_id    = $comment_info->comment_post_ID;
		$status     = $comment_info->comment_approved;
		$old_rating = geodir_get_comment_rating( $comment_info->comment_ID );

		$post_type = get_post_type( $post_id );


		if ( isset( $_REQUEST['geodir_overallrating'] ) ) {

			$overall_rating = absint($_REQUEST['geodir_overallrating']);

			if ( isset( $comment_info->comment_parent ) && (int) $comment_info->comment_parent == 0 ) {
				$overall_rating = $overall_rating > 0 ? $overall_rating : '0';

				if ( isset( $old_rating ) ) {

					$sqlqry = $wpdb->prepare( "UPDATE " . GEODIR_REVIEW_TABLE . " SET
						overall_rating = %f,
						status		= %s,
						comment_content	= %s 
						WHERE comment_id = %d ", array(
						$overall_rating,
						$status,
						$comment_info->comment_content,
						$comment_id
					) );

					$wpdb->query( $sqlqry );

					//update rating
					self::update_post_rating( $post_id, $post_type );

				}
			}
		}
	}

	/**
	 * Update comment status when changing the rating.
	 *
	 * @param int $comment_id The comment ID.
	 * @param int|string $status The comment status.
	 *
	 * @global object $wpdb WordPress Database object.
	 * @global string $plugin_prefix Geodirectory plugin table prefix.
	 * @global int $user_ID The current user ID.
	 */
	function status_change( $comment_id, $status ) {
		if ( $status == 'delete' ) {
			return;
		}
		global $wpdb;

		$comment_info = get_comment( $comment_id );

		$post_id = isset( $comment_info->comment_post_ID ) ? $comment_info->comment_post_ID : '';

		if ( ! empty( $comment_info ) ) {
			$status = $comment_info->comment_approved;
		}

		if ( $status == 'approve' || $status == 1 ) {
			$status = 1;
		} else {
			$status = 0;
		}

		$comment_info_ID = isset( $comment_info->comment_ID ) ? $comment_info->comment_ID : '';
		$old_rating      = geodir_get_comment_rating( $comment_info_ID );
		$post_type = get_post_type( $post_id );

		if ( $comment_id ) {

			$overall_rating = $old_rating;

			if ( isset( $old_rating ) ) {

				$sqlqry = $wpdb->prepare( "UPDATE " . GEODIR_REVIEW_TABLE . " SET
						overall_rating = %f,
						status		= %s,
						comment_content = %s 
						WHERE comment_id = %d ", array(
					$overall_rating,
					$status,
					$comment_info->comment_content,
					$comment_id
				) );

				$wpdb->query( $sqlqry );

				//update rating
				self::update_post_rating( $post_id, $post_type );
			}
		}
	}

	/**
	 * Save rating details for a comment.
	 *
	 * @param int $comment The comment ID.
	 *
	 * @global object $wpdb WordPress Database object.
	 * @global int $user_ID The current user ID.
	 */
	public function save_rating( $comment = 0 ) {
		global $wpdb, $user_ID;

		$comment_info = get_comment( $comment );

		$post_id   = $comment_info->comment_post_ID;
		$status    = $comment_info->comment_approved;
		$rating_ip = getenv( "REMOTE_ADDR" );

		$post = geodir_get_post_info( $post_id );
		if ( empty( $post ) ) {
			return;
		}

		if ( $post->post_status == 'publish' ) {
			$post_status = '1';
		} else {
			$post_status = '0';
		}

		if ( isset( $_REQUEST['geodir_overallrating'] ) ) {
			$overall_rating = absint($_REQUEST['geodir_overallrating']);

			if ( isset( $comment_info->comment_parent ) && (int) $comment_info->comment_parent == 0 ) {
				$overall_rating = $overall_rating > 0 ? $overall_rating : '0';

				$sqlqry = $wpdb->prepare( "INSERT INTO " . GEODIR_REVIEW_TABLE . " SET
					post_id		= %d,
					post_type = %s,
					post_title	= %s,
					user_id		= %d,
					comment_id	= %d,
					rating_ip	= %s,
					overall_rating = %f,
					status		= %s,
					post_status		= %s, 
					post_date		= %s, 
					post_city		= %s, 
					post_region		= %s, 
					post_country	= %s,
					post_longitude	= %s,
					post_latitude	= %s,
					comment_content	= %s 
					",
					array(
						$post_id,
						$post->post_type,
						$post->post_title,
						$user_ID,
						$comment,
						$rating_ip,
						$overall_rating,
						$status,
						$post_status,
						date_i18n( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
						$post->city,
						$post->region,
						$post->country,
						$post->latitude,
						$post->longitude,
						$comment_info->comment_content
					)
				);

				$wpdb->query( $sqlqry );

				/**
				 * Called after saving the comment.
				 *
				 * @since 1.0.0
				 * @package GeoDirectory
				 *
				 * @param array $_REQUEST {
				 *    Attributes of the $_REQUEST variable.
				 *
				 * @type string $geodir_overallrating Overall rating.
				 * @type string $comment Comment text.
				 * @type string $submit Submit button text.
				 * @type string $comment_post_ID Comment post ID.
				 * @type string $comment_parent Comment Parent ID.
				 * @type string $_wp_unfiltered_html_comment Unfiltered html comment string.
				 *
				 * }
				 */
				do_action( 'geodir_after_save_comment', $_REQUEST, 'Comment Your Post' );

				if ( $status ) {
					self::update_post_rating( $post_id );
				}
			}
		}
	}

	/**
	 * Adds comment rating meta box.
	 *
	 * Adds meta box to Comments -> Edit page using hook {@see 'add_meta_boxes_comment'}.
	 *
	 * @param object $comment The comment object.
	 */
	function add_meta_box() {
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
	function add_meta_row( $a ) {
		global $comment;

		$rating = geodir_get_comment_rating( $comment->comment_ID );
		if ( $rating != 0 ) {
			echo geodir_get_rating_stars( $rating, $comment->comment_ID );
		}

		return $a;
	}


}

GeoDir_Admin_Comments::init();
