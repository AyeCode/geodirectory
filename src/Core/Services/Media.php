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

	/**
	 * Save post files during post save operation.
	 *
	 * This handles file uploads for a post, including:
	 * - Processing image and file fields
	 * - Managing featured images
	 * - Handling file field validation
	 * - Updating/inserting/deleting attachments
	 *
	 * Refactored from legacy GeoDir_Post_Data::save_files()
	 *
	 * @param int    $post_id   The post ID.
	 * @param array  $gd_post   The post data array from $_POST.
	 * @param string $post_type The post type slug.
	 * @param bool   $is_dummy  Whether this is a dummy/sample post.
	 * @return int|false The featured image attachment ID or false.
	 */
	public function save_post_files( int $post_id, array $gd_post, string $post_type, bool $is_dummy = false ) {
		$featured_image = false;

		// 1. Handle post_images field
		if ( isset( $gd_post['post_images'] ) && $gd_post['post_images'] !== '' ) {
			$featured_image = $this->process_field_files( $post_id, 'post_images', $gd_post['post_images'], $is_dummy );
		}

		// 2. Get all file-type fields for this post type
		$file_fields = $this->get_file_fields( $post_type );

		if ( ! empty( $file_fields ) ) {
			foreach ( $file_fields as $htmlvar_name => $allowed_types ) {
				// Skip post_images as it's already handled
				if ( $htmlvar_name === 'post_images' ) {
					continue;
				}

				// Process this file field if it exists in gd_post
				if ( isset( $gd_post[ $htmlvar_name ] ) ) {
					$this->process_field_files( $post_id, $htmlvar_name, $gd_post[ $htmlvar_name ], $is_dummy );
				}
			}
		}

		return $featured_image;
	}

	/**
	 * Process files for a specific field.
	 *
	 * Handles the file string format: "url|ID|title|caption|approved::url|ID|title|caption|approved"
	 *
	 * @param int    $post_id      The post ID.
	 * @param string $field        The field htmlvar_name.
	 * @param string $file_string  The file data string.
	 * @param bool   $is_dummy     Whether this is a dummy post.
	 * @return int|false The featured image attachment ID or false.
	 */
	private function process_field_files( int $post_id, string $field, string $file_string, bool $is_dummy = false ) {
		if ( empty( $file_string ) ) {
			return false;
		}

		// Get existing files for this post/field
		$existing_files = $this->get_attachments_by_type( $post_id, $field );
		$existing_ids   = [];
		foreach ( $existing_files as $file ) {
			$existing_ids[ $file->ID ] = $file;
		}

		// Parse the file string
		$files_array = explode( '::', $file_string );
		$order       = 0;
		$featured    = false;

		foreach ( $files_array as $file_data ) {
			if ( empty( $file_data ) ) {
				continue;
			}

			// Parse: url|ID|title|caption|is_approved
			$file_parts = explode( '|', $file_data );
			$file_url   = isset( $file_parts[0] ) ? $file_parts[0] : '';
			$file_id    = isset( $file_parts[1] ) ? absint( $file_parts[1] ) : 0;
			$title      = isset( $file_parts[2] ) ? sanitize_text_field( $file_parts[2] ) : '';
			$caption    = isset( $file_parts[3] ) ? sanitize_text_field( $file_parts[3] ) : '';
			$approved   = isset( $file_parts[4] ) ? absint( $file_parts[4] ) : 1;

			if ( empty( $file_url ) ) {
				continue;
			}

			// If file_id exists in our DB, update it
			if ( $file_id > 0 && isset( $existing_ids[ $file_id ] ) ) {
				$this->update_attachment( $file_id, $post_id, $field, $file_url, $title, $caption, $order, $approved );
				unset( $existing_ids[ $file_id ] ); // Mark as processed
			} else {
				// New file - insert it
				$file_id = $this->insert_attachment( $post_id, $field, $file_url, $title, $caption, $order, $approved, false, $is_dummy );
			}

			// First image is featured
			if ( $order === 0 && $field === 'post_images' && $file_id ) {
				$featured = $file_id;
			}

			$order++;
		}

		// Delete any files that weren't in the submitted list
		foreach ( $existing_ids as $delete_id => $file ) {
			$this->delete( $delete_id );
		}

		return $featured;
	}

	/**
	 * Insert a new attachment record.
	 *
	 * Simplified version of legacy GeoDir_Media::insert_attachment()
	 *
	 * @param int    $post_id       The post ID.
	 * @param string $type          The attachment type (e.g., 'post_images').
	 * @param string $url           The file URL.
	 * @param string $title         The attachment title.
	 * @param string $caption       The attachment caption.
	 * @param int    $order         The menu order.
	 * @param int    $is_approved   Whether the attachment is approved.
	 * @param bool   $is_placeholder Whether this is a placeholder URL.
	 * @param bool   $is_dummy      Whether this is a dummy post.
	 * @return int|false The new attachment ID or false on failure.
	 */
	private function insert_attachment( int $post_id, string $type, string $url, string $title = '', string $caption = '', int $order = 0, int $is_approved = 1, bool $is_placeholder = false, bool $is_dummy = false ) {
		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		if ( ! function_exists( 'media_sideload_image' ) ) {
			require_once ABSPATH . 'wp-admin/includes/media.php';
		}
		if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}

		$attachment_id = 0;
		$metadata      = '';
		$file          = null;
		$file_type     = null;

		// Handle placeholder images (not processed, just stored as reference)
		if ( $is_placeholder ) {
			$upload_dir = wp_upload_dir();

			if ( geodirectory()->helpers->is_full_url( $url ) ) {
				$file = esc_url_raw( $url );
			} elseif ( strpos( $url, '#' ) === 0 ) {
				$file = esc_url_raw( ltrim( $url, '#' ) );
			} else {
				$file = trailingslashit( $upload_dir['subdir'] ) . basename( $url );
			}

			$file_type_arr = wp_check_filetype( basename( $url ) );
			$file_type     = $file_type_arr['type'];
		} else {
			// For featured image (order 0), use WordPress native attachment
			if ( $order === 0 && $type === 'post_images' ) {
				$attachment_id = media_sideload_image( $url, $post_id, $title, 'id' );

				if ( is_wp_error( $attachment_id ) ) {
					return false;
				}

				$metadata      = wp_get_attachment_metadata( $attachment_id );
				$file_type_arr = wp_check_filetype( basename( $url ) );
				$file_type     = $file_type_arr['type'];
				$upload_dir    = wp_upload_dir();
				$file          = trailingslashit( $upload_dir['basedir'] ) . $metadata['file'];

				// Set as post thumbnail
				if ( $is_approved && ! wp_is_post_revision( $post_id ) ) {
					set_post_thumbnail( $post_id, $attachment_id );
				}
			} else {
				// For non-featured images, download and process
				if ( ! class_exists( 'GeoDir_Media' ) ) {
					require_once geodir_plugin_path() . '/includes/class-geodir-media.php';
				}

				$file_result = \GeoDir_Media::get_external_media( $url, $title, geodir_image_extensions() );

				if ( is_wp_error( $file_result ) ) {
					return false;
				}

				$file      = $file_result['file'];
				$file_type = $file_result['type'];

				// Create image sizes
				if ( isset( $file_type ) && \GeoDir_Media::is_image( $file_type ) ) {
					$metadata = \GeoDir_Media::create_image_sizes( $file, $attachment_id );
				}
			}

			// Make file path relative
			if ( ! empty( $file ) ) {
				$file = strrev( trailingslashit( strrev( _wp_relative_upload_path( $file ) ) ) );
			}
		}

		// Bail if file is null
		if ( is_null( $file ) ) {
			return false;
		}

		// Insert into custom attachments table
		$data = [
			'post_id'     => $post_id,
			'date_gmt'    => gmdate( 'Y-m-d H:i:s' ),
			'user_id'     => get_current_user_id(),
			'title'       => stripslashes_deep( $title ),
			'caption'     => stripslashes_deep( $caption ),
			'file'        => $file,
			'mime_type'   => $file_type,
			'menu_order'  => $order,
			'featured'    => ( $order === 0 ) ? 1 : 0,
			'is_approved' => $is_approved,
			'metadata'    => maybe_serialize( $metadata ),
			'type'        => $type,
		];

		$result = $this->repository->create( $data );

		if ( ! $result ) {
			return false;
		}

		/**
		 * Action fired after attachment is inserted.
		 *
		 * @param int   $result The new attachment ID.
		 * @param array $data   The attachment data.
		 * @param int   $order  The menu order.
		 */
		do_action( 'geodir_insert_attachment', $result, $data, $order );

		return $result;
	}

	/**
	 * Update an existing attachment record.
	 *
	 * Simplified version of legacy GeoDir_Media::update_attachment()
	 *
	 * @param int    $file_id      The attachment ID.
	 * @param int    $post_id      The post ID.
	 * @param string $field        The field htmlvar_name.
	 * @param string $file_url     The file URL.
	 * @param string $file_title   The file title.
	 * @param string $file_caption The file caption.
	 * @param int    $order        The menu order.
	 * @param int    $is_approved  Whether the file is approved.
	 * @return bool True on success, false on failure.
	 */
	private function update_attachment( int $file_id, int $post_id, string $field, string $file_url, string $file_title = '', string $file_caption = '', int $order = 0, int $is_approved = 1 ): bool {
		// Handle featured image for post_images
		if ( $order === 0 && $field === 'post_images' && ! wp_is_post_revision( $post_id ) ) {
			$featured_img_url = get_the_post_thumbnail_url( $post_id, 'full' );

			// If featured image changed, update the WordPress attachment
			if ( $featured_img_url != $file_url ) {
				global $wpdb;
				$file = $wpdb->get_var( $wpdb->prepare(
					"SELECT file FROM " . geodirectory()->tables->get( 'attachments' ) . " WHERE ID = %d",
					$file_id
				) );

				if ( ! geodirectory()->helpers->is_full_url( $file ) ) {
					// Delete existing featured attachment
					$wpdb->delete( $wpdb->posts, [
						'post_parent' => $post_id,
						'post_type'   => 'attachment',
						'post_status' => 'inherit'
					] );

					// Create new attachment
					$wp_upload_dir = wp_upload_dir();
					$filename      = $wp_upload_dir['basedir'] . $file;
					$file_info     = wp_check_filetype( basename( $file_url ) );

					$attachment = [
						'guid'           => $file_url,
						'post_mime_type' => $file_info['type'],
						'post_title'     => stripslashes_deep( $file_title ),
						'post_content'   => stripslashes_deep( $file_caption ),
						'post_status'    => 'inherit'
					];

					$attachment_id = wp_insert_attachment( $attachment, $filename, $post_id );

					if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
						require_once ABSPATH . 'wp-admin/includes/image.php';
					}

					$attach_data = wp_generate_attachment_metadata( $attachment_id, $filename );
					wp_update_attachment_metadata( $attachment_id, $attach_data );
					set_post_thumbnail( $post_id, $attachment_id );
				}
			} else {
				// Update existing thumbnail title/caption
				$post_thumbnail_id = get_post_thumbnail_id( $post_id );

				if ( $post_thumbnail_id ) {
					global $wpdb;
					$wpdb->update(
						$wpdb->posts,
						[
							'post_title'   => stripslashes( $file_title ),
							'post_excerpt' => stripslashes( $file_caption ),
							'post_content' => stripslashes( $file_caption )
						],
						[ 'ID' => $post_thumbnail_id ],
						[ '%s', '%s', '%s' ]
					);
				}
			}
		}

		// Update custom attachments table
		$data = [
			'title'       => stripslashes_deep( $file_title ),
			'caption'     => stripslashes_deep( $file_caption ),
			'menu_order'  => $order,
			'featured'    => ( $order === 0 && $field === 'post_images' ) ? 1 : 0,
			'is_approved' => $is_approved,
		];

		$result = $this->repository->update( $file_id, $data );

		if ( $result === false ) {
			return false;
		}

		// Get updated attachment for action
		$attachment = $this->repository->find( $file_id );

		/**
		 * Action fired after attachment is updated.
		 *
		 * @param int    $file_id    The attachment ID.
		 * @param object $attachment The attachment data.
		 * @param int    $order      The menu order.
		 */
		do_action( 'geodir_update_attachment', $file_id, $attachment, $order );

		if ( $result ) {
			do_action( 'geodir_updated_attachment', $file_id, $attachment, $order );
		}

		return true;
	}
}
