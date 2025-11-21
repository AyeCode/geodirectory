<?php
/**
 * Media Service
 *
 * Handles the business logic for media attachments, including file operations.
 *
 * @package GeoDirectory\Core
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core\Services;

use AyeCode\GeoDirectory\Database\Repository\AttachmentRepository;
use AyeCode\GeoDirectory\Database\Repository\CustomFieldRepository;

final class Media {

	/**
	 * @var AttachmentRepository
	 */
	private AttachmentRepository $repository;

	/**
	 * @var CustomFieldRepository
	 */
	private CustomFieldRepository $custom_field_repo;

	/**
	 * Constructor.
	 *
	 * @param AttachmentRepository  $repository        The repository for attachment database access.
	 * @param CustomFieldRepository $custom_field_repo The repository for custom field definitions.
	 */
	public function __construct( AttachmentRepository $repository, CustomFieldRepository $custom_field_repo ) {
		$this->repository        = $repository;
		$this->custom_field_repo = $custom_field_repo;
	}

	/**
	 * Get the post type fields that are for file uploads and return the allowed file types.
	 *
	 * Refactored from legacy get_file_fields().
	 *
	 * @since 2.0.0
	 * @since 3.0.0 Moved to Media service and refactored to use Repository.
	 *
	 * @param string $post_type The post type slug.
	 * @return array Array of [ 'htmlvar_name' => [ 'allowed', 'types' ] ].
	 */
	public function get_file_fields( string $post_type ): array {
		$fields = [];

		// Fetch all fields for this post type via Repository
		$results = $this->custom_field_repo->get_by_post_type( $post_type );

		if ( ! empty( $results ) ) {
			foreach ( $results as $field ) {
				// We only want fields of type 'file'
				if ( isset( $field['field_type'] ) && 'file' === $field['field_type'] ) {

					$extra_fields = isset( $field['extra_fields'] ) ? maybe_unserialize( $field['extra_fields'] ) : [];

					// Extract allowed file types
					$allowed_types = isset( $extra_fields['gd_file_types'] ) ? maybe_unserialize( $extra_fields['gd_file_types'] ) : [];

					$fields[ $field['htmlvar_name'] ] = $allowed_types;
				}
			}
		}

		return $fields;
	}

	/**
	 * Inserts an attachment from a URL, processes it, and saves it to the database.
	 *
	 * @param int    $post_id The post ID to attach to.
	 * @param string $type    The type of attachment (e.g., 'post_images').
	 * @param string $url     The URL of the file to sideload.
	 * @return array|\WP_Error The new attachment data array, or a WP_Error on failure.
	 */
	public function insert_from_url( int $post_id, string $type, string $url ) {
		if ( ! function_exists( 'media_sideload_image' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/media.php' );
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
		}

		// Sideload the image into the WordPress media library.
		$attachment_id = media_sideload_image( $url, $post_id, null, 'id' );

		if ( is_wp_error( $attachment_id ) ) {
			return $attachment_id;
		}

		// Now, create our own record in our custom attachments table.
		$file_path     = get_attached_file( $attachment_id );
		$file_rel_path = _wp_relative_upload_path( $file_path );
		$attachment    = get_post( $attachment_id );

		$data = [
			'post_id'     => $post_id,
			'user_id'     => get_current_user_id(),
			'type'        => $type,
			'file'        =>  '/' . $file_rel_path,
			'mime_type'   => $attachment->post_mime_type,
			'featured'    => 0, // Logic for featured image can be a separate method.
			'is_approved' => 1,
			'metadata'    => maybe_serialize( wp_get_attachment_metadata( $attachment_id ) ),
			'date_gmt'    => gmdate( 'Y-m-d H:i:s' ),
		];

		$new_id = $this->repository->create( $data );
		if ( ! $new_id ) {
			wp_delete_attachment( $attachment_id, true ); // Clean up if our DB insert fails.
			return new \WP_Error( 'db_insert_failed', 'Could not save attachment to GeoDirectory table.' );
		}

		$data['ID'] = $new_id;
		return $data;
	}

	/**
	 * Get the edit string for files per field (Formatted for the JS uploader).
	 * * Refactored from GeoDir_Media::get_field_edit_string().
	 *
	 * @param int    $post_id
	 * @param string $field
	 * @param string $revision_id
	 * @param string $other_id
	 * @param bool   $is_export
	 *
	 * @return string
	 */
	public function get_field_edit_string( $post_id, $field, $revision_id = '', $other_id = '', $is_export = false ) {
		$files = $this->get_attachments_by_type( $post_id, $field, 0, $revision_id, $other_id );

		if ( empty( $files ) ) {
			return '';
		}

		$wp_upload_dir = wp_upload_dir();
		$files_arr     = [];

		foreach ( $files as $file ) {
			$is_approved = ( isset( $file->is_approved ) && $file->is_approved ) ? '' : '|0';

			if ( isset( $file->menu_order ) && $file->menu_order == "-1" ) {
				$is_approved = "|-1";
			}

			// Use V3 helper for URL check
			if ( geodirectory()->helpers->is_full_url( $file->file ) ) {
				$img_src = esc_url_raw( $file->file );

				// Add '#' at start of the url when exporting the images.
				if ( $is_export ) {
					$img_src = '#' . $img_src;
				}
			} else {
				$img_src = $wp_upload_dir['baseurl'] . $file->file;
			}

			// Format: url|ID|title|caption|approved
			$files_arr[] = $img_src . "|" . $file->ID . "|" . $file->title . "|" . $file->caption . $is_approved;
		}

		return implode( "::", $files_arr );
	}

	/**
	 * Get attachments for a specific post and field.
	 * * @param int    $post_id     Post ID.
	 * @param string $mime_type   Attachment type (e.g. 'post_images', 'post_video').
	 * @param int    $limit       Limit number of results.
	 * @param string $revision_id Optional revision ID for previews/autosaves.
	 * @param string $other_id    Optional temp ID.
	 * @param string $status      Status filter (1=approved).
	 *
	 * @return array Array of objects.
	 */
	public function get_attachments_by_type( $post_id, $mime_type, $limit = 0, $revision_id = '', $other_id = '', $status = '' ): array {
		// Delegate the heavy SQL lifting to the Repository
		return $this->repository->get_by_type( [
			'post_id'     => $post_id,
			'mime_type'   => $mime_type,
			'limit'       => $limit,
			'revision_id' => $revision_id,
			'other_id'    => $other_id,
			'status'      => $status
		] );
	}

	/**
	 * Deletes an attachment, including its file and database record.
	 *
	 * @param int $attachment_id The ID of the attachment in our custom table.
	 * @return bool True on success, false on failure.
	 */
	public function delete( int $attachment_id ): bool {
		$attachment = $this->repository->find( $attachment_id );
		if ( ! $attachment ) {
			return false;
		}

		// Delete the physical file.
		$wp_upload_dir = wp_upload_dir();
		$file_path     = $wp_upload_dir['basedir'] . $attachment->file;
		if ( file_exists( $file_path ) ) {
			wp_delete_file( $file_path );
		}

		// @todo Add logic to delete resized image versions.

		// Delete the record from our custom table.
		$this->repository->delete( $attachment_id );

		return true;
	}

	/**
	 * Counts all image attachments.
	 *
	 * @return int
	 */
	public function count_image_attachments(): int {
		return $this->repository->count_all_images();
	}
}
