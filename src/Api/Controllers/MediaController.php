<?php
/**
 * REST API Media Controller
 *
 * Handles file uploads for GeoDirectory posts via REST API.
 *
 * @package GeoDirectory\Api\Controllers
 * @since   3.0.0
 * @author  AyeCode Ltd
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Api\Controllers;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * REST API Media controller.
 *
 * @since 3.0.0
 */
final class MediaController extends WP_REST_Controller {

	/**
	 * Namespace for API routes.
	 */
	protected $namespace = 'geodir/v3';

	/**
	 * REST base.
	 */
	protected $rest_base = 'media';

	/**
	 * Register routes.
	 */
	public function register_routes(): void {
		// Upload file.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/upload',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'upload_file' ),
					'permission_callback' => array( $this, 'upload_permissions_check' ),
					'args'                => array(
						'post_id' => array(
							'type'              => 'integer',
							'description'       => __( 'Post ID to attach file to (optional).', 'geodirectory' ),
							'sanitize_callback' => 'absint',
						),
					),
				),
			)
		);
	}

	/**
	 * Check if user can upload files.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return true|WP_Error
	 */
	public function upload_permissions_check( $request ) {
		// Must be logged in OR guest posting is enabled.
		$user_id = get_current_user_id();

		if ( ! $user_id ) {
			if ( geodir_get_option( 'post_logged_out' ) ) {
				return true;
			}

			return new WP_Error(
				'rest_forbidden',
				__( 'You must be logged in to upload files.', 'geodirectory' ),
				array( 'status' => 401 )
			);
		}

		// Check upload capability.
		if ( ! current_user_can( 'upload_files' ) ) {
			return new WP_Error(
				'rest_cannot_upload',
				__( 'You do not have permission to upload files.', 'geodirectory' ),
				array( 'status' => 403 )
			);
		}

		return true;
	}

	/**
	 * Upload a file.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function upload_file( $request ) {
		// Get files from request.
		$files = $request->get_file_params();

		if ( empty( $files['file'] ) ) {
			return new WP_Error(
				'no_file',
				__( 'No file provided.', 'geodirectory' ),
				array( 'status' => 400 )
			);
		}

		$file = $files['file'];

		// Validate file.
		$validation = $this->validate_file( $file );
		if ( is_wp_error( $validation ) ) {
			return $validation;
		}

		// Get post ID if provided.
		$post_id = isset( $request['post_id'] ) ? (int) $request['post_id'] : 0;

		// Handle upload.
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		// Upload file.
		$upload = wp_handle_upload(
			$file,
			array(
				'test_form' => false,
				'action' => 'geodir_upload_file',
			)
		);

		if ( isset( $upload['error'] ) ) {
			return new WP_Error(
				'upload_error',
				$upload['error'],
				array( 'status' => 500 )
			);
		}

		// Create attachment.
		$attachment_id = wp_insert_attachment(
			array(
				'post_mime_type' => $upload['type'],
				'post_title' => sanitize_file_name( basename( $upload['file'] ) ),
				'post_content' => '',
				'post_status' => 'inherit',
				'post_parent' => $post_id,
			),
			$upload['file'],
			$post_id
		);

		if ( is_wp_error( $attachment_id ) ) {
			return $attachment_id;
		}

		// Generate metadata.
		$attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
		wp_update_attachment_metadata( $attachment_id, $attachment_data );

		// Get attachment info.
		$attachment = get_post( $attachment_id );

		return rest_ensure_response( array(
			'success' => true,
			'message' => __( 'File uploaded successfully.', 'geodirectory' ),
			'attachment_id' => $attachment_id,
			'url' => wp_get_attachment_url( $attachment_id ),
			'type' => $attachment->post_mime_type,
			'filename' => basename( $upload['file'] ),
		) );
	}

	/**
	 * Validate uploaded file.
	 *
	 * @param array $file File data from $_FILES.
	 * @return true|WP_Error
	 */
	private function validate_file( array $file ) {
		// Check for upload errors.
		if ( isset( $file['error'] ) && $file['error'] !== UPLOAD_ERR_OK ) {
			$error_messages = array(
				UPLOAD_ERR_INI_SIZE   => __( 'File is too large (exceeds PHP upload_max_filesize).', 'geodirectory' ),
				UPLOAD_ERR_FORM_SIZE  => __( 'File is too large.', 'geodirectory' ),
				UPLOAD_ERR_PARTIAL    => __( 'File was only partially uploaded.', 'geodirectory' ),
				UPLOAD_ERR_NO_FILE    => __( 'No file was uploaded.', 'geodirectory' ),
				UPLOAD_ERR_NO_TMP_DIR => __( 'Missing temporary folder.', 'geodirectory' ),
				UPLOAD_ERR_CANT_WRITE => __( 'Failed to write file to disk.', 'geodirectory' ),
				UPLOAD_ERR_EXTENSION  => __( 'File upload blocked by PHP extension.', 'geodirectory' ),
			);

			$message = isset( $error_messages[ $file['error'] ] )
				? $error_messages[ $file['error'] ]
				: __( 'File upload failed.', 'geodirectory' );

			return new WP_Error(
				'upload_error',
				$message,
				array( 'status' => 400 )
			);
		}

		// Validate MIME type.
		$allowed_types = $this->get_allowed_mime_types();
		$file_type = wp_check_filetype( $file['name'], $allowed_types );

		if ( ! $file_type['type'] ) {
			return new WP_Error(
				'invalid_file_type',
				__( 'File type not allowed.', 'geodirectory' ),
				array( 'status' => 400 )
			);
		}

		// Validate file size (10MB default).
		$max_size = apply_filters( 'geodir_max_upload_size', 10 * MB_IN_BYTES );

		if ( $file['size'] > $max_size ) {
			return new WP_Error(
				'file_too_large',
				sprintf(
					/* translators: %s: Maximum file size in MB */
					__( 'File is too large. Maximum size is %s MB.', 'geodirectory' ),
					size_format( $max_size )
				),
				array( 'status' => 400 )
			);
		}

		return true;
	}

	/**
	 * Get allowed MIME types for uploads.
	 *
	 * @return array
	 */
	private function get_allowed_mime_types(): array {
		$default_types = array(
			// Images.
			'jpg|jpeg|jpe' => 'image/jpeg',
			'gif' => 'image/gif',
			'png' => 'image/png',
			'webp' => 'image/webp',
			'avif' => 'image/avif',

			// Documents.
			'pdf' => 'application/pdf',
			'doc' => 'application/msword',
			'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',

			// Videos.
			'mp4' => 'video/mp4',
			'mov' => 'video/quicktime',
			'avi' => 'video/x-msvideo',
		);

		/**
		 * Filter allowed MIME types for GeoDirectory uploads.
		 *
		 * @since 3.0.0
		 *
		 * @param array $default_types Default allowed MIME types.
		 */
		return apply_filters( 'geodir_upload_allowed_mime_types', $default_types );
	}
}
