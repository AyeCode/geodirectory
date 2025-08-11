<?php
/**
 * Class CheckReviewsAction
 * Handles the process of reviewing and ensuring the accuracy of review-related data within the GeoDirectory plugin.
 *
 * @since 3.0.0
 */

// Define the namespace for the class. This helps prevent conflicts.
namespace GeoDirectory\Ajax\Actions;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CheckReviewsAction
 * Responsible for reviewing and correcting incomplete or missing review data within the GeoDirectory plugin.
 */
class RecountTermsAction {

	/**
	 * Handles the dispatch process.
	 *
	 * @return void
	 */
	public static function dispatch() {

		$post_types = geodir_get_posttypes();
		foreach ( $post_types as $post_type ) {
			$cats = get_terms( $post_type . 'category', array( 'hide_empty' => false, 'fields' => 'id=>parent' ) );
			geodir_term_recount( $cats, get_taxonomy( $post_type . 'category' ), $post_type, true, false );
			$tags = get_terms( $post_type . '_tags', array( 'hide_empty' => false, 'fields' => 'id=>parent' ) );
			geodir_term_recount( $tags, get_taxonomy( $post_type . '_tags' ), $post_type, true, false );
		}
		$message = __( 'Terms successfully recounted', 'geodirectory' );

		wp_send_json_success(array(
			'message'  => $message,
			'progress' => 100
		));
	}


}
