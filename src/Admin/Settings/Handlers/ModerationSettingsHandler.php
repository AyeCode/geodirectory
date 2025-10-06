<?php
/**
 * Handles persistence of Moderation settings.
 *
 * @package GeoDirectory\Admin\Settings\Handlers
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Admin\Settings\Handlers;

use AyeCode\GeoDirectory\Admin\Utils\DataMapper;
use AyeCode\GeoDirectory\Common\PostReports;
use AyeCode\GeoDirectory\Database\Repository\ReportedRepository;
use AyeCode\GeoDirectory\Database\Schema\ReportedSchema;

class ModerationSettingsHandler {

	/** @var ReportedRepository */
	private $repository;

	/** @var DataMapper */
	private $mapper;

	public function __construct() {
		$this->repository = new ReportedRepository();
		$this->mapper     = new DataMapper( $this->get_data_map() );
	}

	private function get_data_map(): array {
		$schema_definition = ( new ReportedSchema() )->get_schema();
		$base_map          = [];

		foreach ( $schema_definition as $db_column => $props ) {
			$ui_key      = $props['ui_key'] ?? $db_column;
			$ui_sanitize = $props['ui_sanitize'] ?? null;
			$db_sanitize = $props['db_sanitize'] ?? null;

			$base_map[ $db_column ] = [ $ui_key, $ui_sanitize, $db_sanitize, null, null ];
		}

		return $base_map;
	}

	public function get_reports( $status = 'all', $filters = [] ): array {
		$raw_data = $this->repository->get_all($status, $filters);
		$ui_data  = $this->mapper->transform( $raw_data, 'to_ui', true );

		$return_data = [];

		if ( ! empty( $ui_data ) ) {

			$reasons = PostReports::get_reasons();
			$statuses = PostReports::get_statuses();
			foreach ( $ui_data as $key => $value ) {
				// --- Reported Content ---
				$post_title = get_the_title( $value['post_id'] );
				$post_url   = get_permalink( $value['post_id'] );
				$post       = get_post( $value['post_id'] );
				$content    = '';

				if ( $post_url && $post_title ) {
					$content .= "<strong><a href='" . esc_url( $post_url ) . "' target='_blank'>" . esc_attr( $post_title ) . "</a></strong><br>";
				} else {
					$content .= '<em>' . __( 'Post missing', 'geodirectory' ) . ' (' . absint( $value['post_id'] ) . ')</em><br>';
				}

				if ( $post ) {
					$author    = get_user_by( 'id', $post->post_author );
					if ( $author ) {
						$author_url = get_edit_user_link( $author->ID );
						$content    .= "<small>" . __( 'Author:', 'geodirectory' ) . " <a href='" . esc_url( $author_url ) . "' target='_blank'>" . esc_attr( $author->display_name ) . "</a></small>";
					}
				}
				$ui_data[ $key ]['reported_content'] = $content;

				// --- Report Details (with modal for message) ---
				$reason = !empty( $reasons[ $value['reason'] ] ) ? esc_attr($reasons[ $value['reason'] ] ) : esc_attr( $value['reason'] );
				$details = '<strong>' . esc_attr( $reason ) . '</strong>';
				if ( ! empty( $value['message'] ) ) {
					$modal_id = 'report-message-' . $value['id'];
					$details .= "<br><a href='javascript:void(0);' onclick='aui_modal(\"" . esc_attr__('Report Message', 'geodirectory') . "\",\"".esc_html( $value['message'] )."\",\"\",true)'>" . __( 'Show message', 'geodirectory' ) . "</a>";
				}
				$ui_data[ $key ]['report_details'] = $details;


				// --- Reported By ---
				$reported_by = '';
				if ( ! empty( $value['user_id'] ) ) {
					$user = get_user_by( 'id', $value['user_id'] );
					if ( $user ) {
						$user_url      = get_edit_user_link( $user->ID );
						$reported_by   .= "<a href='" . esc_url( $user_url ) . "'>" . esc_attr( $user->display_name ) . "</a><br>";
						$reported_by   .= "<a href='mailto:" . esc_attr( $user->user_email ) . "'>" . esc_attr( $user->user_email ) . "</a><br>";
					}
				} else {
					$reported_by .= esc_attr( $value['user_name'] ) . "<br>";
					$reported_by .= "<a href='mailto:" . esc_attr( $value['user_email'] ) . "'>" . esc_attr( $value['user_email'] ) . "</a><br>";
				}
				$reported_by            .= "IP: " . esc_attr( $value['user_ip'] );
				$ui_data[ $key ]['reported_by'] = $reported_by;

				// --- Date ---
				$ui_data[ $key ]['report_date_formatted'] = date( get_option( 'date_format' ), strtotime( $value['report_date'] ) );

				// --- Status ---
				$ui_data[ $key ]['status_formatted'] = !empty($statuses[ $value['status'] ]) ? $statuses[ $value['status'] ] : $statuses['pending'];

			}

			$return_data['items'] = $ui_data;


		}

		$return_data['counts'] = $this->repository->get_status_counts();

		return $return_data;
	}

	public function create_report( array $ui_data ): array {
		$db_data = $this->mapper->transform( [$ui_data], 'to_db' )[0];

		$new_report_id = $this->repository->add_report( $db_data );

		$saved_data = $this->repository->get_by_id( $new_report_id );

		$response = $this->mapper->transform( [$saved_data], 'to_ui' )[0];
		$response['message'] = __( 'Report created successfully.', 'geodirectory' );

		return $response;
	}

	public function update_report( int $id, array $ui_data ): bool {
		$db_data = $this->mapper->transform( [$ui_data], 'to_db' )[0];
		return $this->repository->update_report( $id, $db_data );
	}

	public function delete_report( int $id ): bool {
		return $this->repository->delete_report( $id );
	}

	/**
	 * Deletes multiple reports in a single query based on an array of IDs.
	 *
	 * @param array $report_ids An array of report IDs to delete.
	 * @return int|false The number of rows deleted, or false on failure.
	 */
	public function bulk_delete( array $report_ids ) {
		return $this->repository->bulk_delete( $report_ids );
	}

	public function set_status( $status, $id ) {

		$item = $this->repository->get_by_id( (int) $id );

		if ( empty( $item ) ) {
			return false;
		}

		if ( $item['status'] === $status ) {
			return false;
		}

		$item['status'] = esc_html( $status );
		$item['updated_date'] = date_i18n( 'Y-m-d H:i:s' );

		$result = $this->repository->update_report( $id, $item);

		if ( ! $result ) {
			return false;
		}

		do_action( 'geodir_report_post_set_status', $status, $id, $item );

		return true;
	}

	public function set_post_status( $post_status, $id ) {
		$item = $this->repository->get_by_id( (int) $id );

		if ( empty( $item['post_id'] ) ) {
			return false;
		}

		$gd_post = geodir_get_post_info( $item['post_id'] );
		if ( empty( $gd_post ) ) {
			return false;
		}

		if ( $gd_post['post_status'] === $post_status ) {
			return false;
		}

		$post_data = array();
		$post_data['ID'] = $gd_post->ID;
		$post_data['post_status'] = $post_status;

		$post_data = apply_filters( 'geodir_repost_post_set_post_status_data', $post_data, $gd_post, $post_status, $item );

		$result = wp_update_post( $post_data );

		do_action( 'geodir_repost_post_after_set_post_status', $gd_post, $post_status, $item );

		if ( ! $result ) {
			return false;
		}

		// Mark as resolved.
		$this->set_status( 'resolved', $item['id'] );

		do_action( 'geodir_repost_post_set_post_status', $post_status, $item, $gd_post );

		return true;
	}

	public function delete_post( $id, $force = false ) {
		$item = $this->repository->get_by_id( (int) $id );

		if ( empty( $item['post_id'] ) ) {
			return false;
		}

		$gd_post = geodir_get_post_info( $item['post_id'] );
		if ( empty( $gd_post ) ) {
			return false;
		}

		do_action( 'geodir_repost_post_before_delete_post', $gd_post, $item, $force );

		if ( $force ) {
			$result = wp_delete_post( $gd_post->ID, true );
		} else {
			$result = wp_trash_post( $gd_post->ID );
		}

		if ( ! $result ) {
			return false;
		}

		if ( $force ) {
			// Delete
			//self::delete( $item['id'] ); //@todo should we still delete the report?
		} else {
			// Mark as resolved.
			$this->set_status( 'resolved', $item['id'] );
		}

//		self::delete_cache( $gd_post->ID );

		do_action( 'geodir_repost_post_after_delete_post', $gd_post, $item, $force );

		return true;
	}
}
