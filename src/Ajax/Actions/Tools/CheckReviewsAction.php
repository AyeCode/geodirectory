<?php
/**
 * Class CheckReviewsAction
 * Handles the process of reviewing and ensuring the accuracy of review-related data within the GeoDirectory plugin.
 *
 * @since 3.0.0
 */

// Define the namespace for the class. This helps prevent conflicts.
namespace AyeCode\GeoDirectory\Ajax\Actions\Tools;

// Exit if accessed directly
use const AyeCode\GeoDirectory\Ajax\Actions\GEODIR_REVIEW_TABLE;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CheckReviewsAction
 * Responsible for reviewing and correcting incomplete or missing review data within the GeoDirectory plugin.
 */
class CheckReviewsAction {

	/**
	 * Handles the dispatch process.
	 *
	 * @return void
	 */
	public static function dispatch() {

		if (self::check_reviews()) {
			$message = __( 'Reviews fixed.', 'geodirectory' );
		} else {
			$message = __( 'No reviews need fixed', 'geodirectory' );
		}

		wp_send_json_success(array(
			'message'  => $message,
			'progress' => 100
		));
	}

	/**
	 * Check reviews.
	 *
	 * @since 2.0.0
	 *
	 * @global object $wpdb WordPress Database object.
	 *
	 * @return bool $checked
	 */
	public static function check_reviews() {
		global $wpdb;

		$checked = false;

		if ($wpdb->get_results("SELECT * FROM " . GEODIR_REVIEW_TABLE . " WHERE latitude IS NULL OR latitude = '' OR longitude IS NULL OR longitude = '' OR city IS NULL OR city = ''")) {
			if (self::check_reviews_location()) {
				$checked = true;
			}
		}

		return $checked;
	}

	/**
	 * Check reviews location.
	 *
	 * @since 2.0.0
	 *
	 * @global object $wpdb WordPress Database object.
	 *
	 * @return bool
	 */
	public static function check_reviews_location() {
		global $wpdb;

		$post_types = geodir_get_posttypes();

		if ( !empty( $post_types ) ) {
			foreach ( $post_types as $post_type ) {
				$wpdb->query( "UPDATE " . GEODIR_REVIEW_TABLE . " AS gdr JOIN " . $wpdb->prefix . "geodir_" . $post_type . "_detail d ON gdr.post_id=d.post_id SET gdr.latitude=d.latitude, gdr.longitude=d.longitude, gdr.city=d.city, gdr.region=d.region, gdr.country=d.country WHERE gdr.latitude IS NULL OR gdr.latitude = '' OR gdr.longitude IS NULL OR gdr.longitude = '' OR gdr.city IS NULL OR gdr.city = ''" );

			}
			return true;
		}

		return false;
	}
}
