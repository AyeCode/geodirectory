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

namespace AyeCode\GeoDirectory\Core;

use AyeCode\GeoDirectory\Database\AttachmentRepository;

final class Media {
	private AttachmentRepository $repository;

	/**
	 * Constructor.
	 *
	 * @param AttachmentRepository $repository The repository for database access.
	 */
	public function __construct( AttachmentRepository $repository ) {
		$this->repository = $repository;
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
		$file_path    = get_attached_file( $attachment_id );
		$file_rel_path = _wp_relative_upload_path( $file_path );
		$attachment   = get_post( $attachment_id );

		$data = [
			'post_id'     => $post_id,
			'user_id'     => get_current_user_id(),
			'type'        => $type,
			'file'        => $file_rel_path,
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
