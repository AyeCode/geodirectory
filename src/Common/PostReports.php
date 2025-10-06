<?php
/**
 * A central utility class for post reporting features.
 *
 * @package GeoDirectory\Common
 */

declare( strict_types=1 );

namespace AyeCode\GeoDirectory\Common;

use AyeCode\GeoDirectory\Admin\Settings\Handlers\ModerationSettingsHandler;

final class PostReports {

	/**
	 * Gets the report reasons.
	 *
	 * This static method provides a consistent, filterable list of reasons
	 * for use across the plugin (admin, frontend, AJAX).
	 *
	 * @return array The report reasons, filterable via `geodir_report_post_reasons`.
	 */
	public static function get_reasons(): array {
		// @todo we are adding a key here, we will need a converter to convert these
		$reasons = array(
			'copyright_issue'      => __( 'Copyright Issue', 'geodirectory' ),
			'harassment'           => __( 'Harassment', 'geodirectory' ),
			'inappropriate'        => __( 'Inappropriate', 'geodirectory' ),
			'incorrect_details'    => __( 'Incorrect Details', 'geodirectory' ),
			'offensive_or_hateful' => __( 'Offensive or Hateful', 'geodirectory' ),
			'privacy_concern'      => __( 'Privacy Concern', 'geodirectory' ),
			'spam'                 => __( 'Spam', 'geodirectory' ),
			'violence'             => __( 'Violence', 'geodirectory' ),
			'other'                => __( 'Other', 'geodirectory' )
		);

		return apply_filters( 'geodir_report_post_reasons', $reasons );
	}

	/**
	 * @return array
	 */
	public static function get_statuses(): array {
		$statuses = [
			'pending' => __( 'Pending', 'geodirectory' ),
			'rejected' => __( 'Rejected', 'geodirectory' ),
			'resolved' => __( 'Resolved', 'geodirectory' ),
		];


		return apply_filters( 'geodir_report_post_statuses', $statuses );
	}

	/**
	 * @return array
	 */
	public static function get_bulk_actions() {

		return array(
			'pending'        => __( 'Unpublish Posts', 'geodirectory' ),
			'draft'          => __( 'Move Posts to Draft', 'geodirectory' ),
			'trash'          => __( 'Move Posts to Trash', 'geodirectory' ),
			'delete'         => __( 'Delete Posts Permanently', 'geodirectory' ),
			'resolved'       => __( 'Mark Reports as Resolved', 'geodirectory' ),
			'rejected'       => __( 'Mark Reports as Rejected', 'geodirectory' ),
			'delete-reports' => __( 'Delete Reports', 'geodirectory' ),
		);
	}
}
