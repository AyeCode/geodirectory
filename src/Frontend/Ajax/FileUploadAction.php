<?php
/**
 * AJAX File Upload Action
 *
 * @package GeoDirectory\Frontend\Ajax
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Frontend\Ajax;

use AyeCode\GeoDirectory\Core\Media;

final class FileUploadAction {
	private Media $media;

	/**
	 * Constructor.
	 *
	 * @param Media $media The media service.
	 */
	public function __construct( Media $media ) {
		$this->media = $media;
	}

	/**
	 * Registers the AJAX action hooks.
	 */
	public function register(): void {
		// This matches the old action name.
		add_action( 'wp_ajax_geodir_post_attachment_upload', [ $this, 'handle' ] );
	}

	/**
	 * Handles the AJAX request.
	 */
	public function handle(): void {
		// @todo Add a proper nonce check here for security.
		// if ( ! check_ajax_referer( 'my_nonce_name', 'nonce', false ) ) {
		// 	wp_send_json_error( 'Security check failed.', 403 );
		// }

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( 'Permission denied.', 403 );
		}

		$post_id  = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		$field_id = isset( $_POST['imgid'] ) ? sanitize_key( $_POST['imgid'] ) : '';

		if ( ! $post_id || ! $field_id || empty( $_FILES[ $field_id . 'async-upload' ] ) ) {
			wp_send_json_error( 'Missing required data.' );
		}

		// WordPress's `wp_handle_upload` moves the file to a temporary location.
		// We'll use this temporary file to create a proper media library attachment.
		$file     = $_FILES[ $field_id . 'async-upload' ];
		$upload   = wp_handle_upload( $file, [ 'test_form' => false ] );

		if ( isset( $upload['error'] ) ) {
			wp_send_json_error( $upload['error'] );
		}

		$result = $this->media->insert_from_url( $post_id, $field_id, $upload['url'] );

		// Clean up the temporary file created by wp_handle_upload.
		wp_delete_file( $upload['file'] );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		// The old class returned a custom pipe-delimited string. We'll return structured JSON.
		wp_send_json_success( [
			'url' => \wp_upload_dir()['baseurl'] . $result['file'],
			'id'  => $result['ID'],
		] );
	}
}
