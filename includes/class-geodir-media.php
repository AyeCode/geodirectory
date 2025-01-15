<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Media Class
 *
 * @version 2.0.0
 */
class GeoDir_Media {


	/**
	 * Get the post type fields that are for file uploads and return the allowed file types.
     *
     * @since 2.0.0
	 *
	 * @param $post_type
	 *
	 * @return array
	 */
	public static function get_file_fields($post_type){
		global $wpdb;
		$fields = array();

		$result = $wpdb->get_results($wpdb->prepare("SELECT htmlvar_name,extra_fields FROM ".GEODIR_CUSTOM_FIELDS_TABLE." WHERE post_type=%s AND field_type='file' ",$post_type),ARRAY_A);
		if(!empty($result)){
			foreach($result as $field){
				$extra_fields = isset($field['extra_fields']) ? maybe_unserialize($field['extra_fields']) : array();
				$fields[$field['htmlvar_name']] = isset($extra_fields['gd_file_types']) ? maybe_unserialize($extra_fields['gd_file_types']) : array();
			}
		}

		return $fields;
	}

	/**
	 * Check if the file type is an image.
     *
     * @since 2.0.0
	 *
	 * @param string $mime_type Image mime type.
	 *
	 * @return bool $image
	 */
	public static function is_image($mime_type){

		switch ( $mime_type ) {
			case 'image/jpeg':
			case 'image/png':
			case 'image/gif':
			case 'image/webp':
			case 'image/avif':
				$image = true;
				break;
			default:
				$image = false;
				break;
		}

		return $image;
	}

	/**
	 * Handles post image upload.
	 *
	 * @since 1.0.0
	 * @package GeoDirectory
	 */
	public static function post_attachment_upload() {

		// the post id
		$field_id = isset($_POST["imgid"]) ? esc_attr($_POST["imgid"]) : '';
		$post_id = isset($_POST["post_id"]) ? absint($_POST["post_id"]) : '';

		// set GD temp upload dir
		add_filter( 'upload_dir', array( __CLASS__, 'temp_upload_dir' ) );

		// change file orientation if needed
		//$fixed_file = geodir_exif($_FILES[$imgid . 'async-upload']);

		$fixed_file = $_FILES[ $field_id . 'async-upload' ];

		// handle file upload
		$status = wp_handle_upload( $fixed_file, array(
			'test_form' => true,
			'action'    => 'geodir_post_attachment_upload'
		) );
		// unset GD temp upload dir
		remove_filter( 'upload_dir', array( __CLASS__, 'temp_upload_dir' ) );

		if ( ! isset( $status['url'] ) && isset( $status['error'] ) ) {
			print_r( $status );
		}
		//print_r( $status );exit;


		// send the uploaded file url in response
		if ( isset( $status['url'] ) && $post_id) {

			// insert to DB
			$file_info = self::insert_attachment($post_id,$field_id,$status['url'],'', '', -1,0);

			if ( is_wp_error( $file_info ) ) {
				//geodir_error_log( $file_info->get_error_message(), 'post_attachment_upload', __FILE__, __LINE__ );
			} else {
				$wp_upload_dir = wp_upload_dir();
				echo $wp_upload_dir['baseurl'] . $file_info['file'] ."|".$file_info['ID']."||";
			}

		} elseif( isset( $status['url'] )) {
			echo $status['url'];
		}
		else
		{
			echo 'x';
		}

		// if file exists it should have been moved if uploaded correctly so now we can remove it
		if(!empty($status['file']) && $post_id){
			wp_delete_file( $status['file'] );
		}


		exit;
	}

	/**
	 * Get the attachment id from the file path.
     *
     * @since 2.0.0
	 *
	 * @param string $path File path.
	 * @param string $post_id Optional. Post id. Default null.
	 *
	 * @return null|string $result.
	 */
	public static function get_id_from_file_path($path,$post_id = ''){
		global $wpdb;

		if($post_id){
			$result = $wpdb->get_var($wpdb->prepare("SELECT ID FROM ".GEODIR_ATTACHMENT_TABLE." WHERE post_id=%d AND file=%s",$post_id,$path));

		}else{
			$result = $wpdb->get_var($wpdb->prepare("SELECT ID FROM ".GEODIR_ATTACHMENT_TABLE." WHERE file=%s",$path));

		}

		return $result;

	}

	/**
	 * Create the image sizes and return the image metadata.
	 *
	 * @since 2.0.0
	 * @since GD 2.3.15 Added the `$attachment_id` argument.
	 *
	 * @param array $file File array.
	 * @param int   $attachment_id The attachment post ID for the image.
	 *
	 * @return array $metadata.
	 */
	public static function create_image_sizes( $file, $attachment_id = 0 ) {
		if ( ! function_exists( 'wp_read_image_metadata' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/image.php' );
		}

		$metadata = array();
		$imagesize = function_exists( 'wp_getimagesize' ) ? wp_getimagesize( $file ) : @getimagesize( $file );
		$metadata['width'] = $imagesize[0];
		$metadata['height'] = $imagesize[1];

		// Make the file path relative to the upload dir.
		$metadata['file'] = _wp_relative_upload_path( $file );

		// Make thumbnails and other intermediate sizes.
		$_wp_additional_image_sizes = wp_get_additional_image_sizes();
		$image_sizes = get_intermediate_image_sizes();

		$sizes = array();
		foreach ( $image_sizes as $s ) {
			$sizes[$s] = array( 'width' => '', 'height' => '', 'crop' => false );
			if ( isset( $_wp_additional_image_sizes[$s]['width'] ) ) {
				// For theme-added sizes
				$sizes[$s]['width'] = intval( $_wp_additional_image_sizes[$s]['width'] );
			} else {
				// For default sizes set in options
				$sizes[$s]['width'] = get_option( "{$s}_size_w" );
			}

			if ( isset( $_wp_additional_image_sizes[$s]['height'] ) ) {
				// For theme-added sizes
				$sizes[$s]['height'] = intval( $_wp_additional_image_sizes[$s]['height'] );
			} else {
				// For default sizes set in options
				$sizes[$s]['height'] = get_option( "{$s}_size_h" );
			}

			if ( isset( $_wp_additional_image_sizes[$s]['crop'] ) ) {
				// For theme-added sizes
				$sizes[$s]['crop'] = $_wp_additional_image_sizes[$s]['crop'];
			} else {
				// For default sizes set in options
				$sizes[$s]['crop'] = get_option( "{$s}_crop" );
			}
		}

		/**
		 * Filters the image sizes automatically generated when uploading an image.
		 *
		 * @since 2.9.0
		 * @since 4.4.0 Added the `$metadata` argument.
		 * @since GD 2.3.15, WP 5.3.0 Added the `$attachment_id` argument.
		 *
		 * @param array $sizes    An associative array of image sizes.
		 * @param array $metadata An associative array of image metadata: width, height, file.
		 * @param int   $attachment_id The attachment post ID for the image.
		 */
		$sizes = apply_filters( 'intermediate_image_sizes_advanced', $sizes, $metadata, $attachment_id );

		// Fetch additional metadata from EXIF/IPTC.
		$image_meta = wp_read_image_metadata( $file );

		if ( $sizes ) {
			$editor = wp_get_image_editor( $file );

			if ( ! is_wp_error( $editor ) ){
				// If stored EXIF data exists, rotate the source image before creating sub-sizes.
				if ( ! empty( $image_meta ) && method_exists( $editor, 'maybe_exif_rotate' ) ) {
					$rotated = $editor->maybe_exif_rotate();
				}

				// Create sizes
				$metadata['sizes'] = $editor->multi_resize( $sizes );
			}
		} else {
			$metadata['sizes'] = array();
		}

		if ( $image_meta ) {
			$metadata['image_meta'] = $image_meta;
		}

		return $metadata;
	}

	/**
	 * Insert the file info to the DB and return the attachment ID.
     *
     * @since 2.0.0
	 *
	 * @param int $post_id Post ID.
     * @param string $type Optional. Type. Default file.
	 * @param string $url URl.
	 * @param string $title Optional. Title. Default null.
	 * @param string $caption Optional. Caption. Default null.
	 * @param string $order Optional. Order. Default null.
	 * @param int $is_approved Optional. Is approved. Default 1.
	 * @param bool $is_placeholder Optional. If the images is a placeholder url and should not be auto imported. Default false.
	 *
	 * @return array|WP_Error
	 */
	public static function insert_attachment( $post_id, $type = 'file', $url = '', $title = '', $caption = '', $order = '', $is_approved = 1, $is_placeholder = false, $other_id = '',$raw_metadata = array() ) {
		global $wpdb;

		// Load media functions
		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		if ( ! function_exists( 'media_sideload_image' ) ) {
			require_once ABSPATH . 'wp-admin/includes/media.php';
		}
		if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}

		// Check we have what we need
		if ( ! $post_id || ! $url ) {
			return new WP_Error( 'file_insert', __( "No post_id or file url, file insert failed.", "geodirectory" ) );
		}

		$attachment_id = 0;
		$metadata = !empty($raw_metadata) ? $raw_metadata : '';

		if ( $is_placeholder ) { // If a placeholder image, such as a image name that will be uploaded manually to the upload dir
			$upload_dir = wp_upload_dir();

			if ( geodir_is_full_url( $url ) ) {
				$file = esc_url_raw( $url );
			} else if ( strpos( $url, '#' ) === 0 ) {
				$file = esc_url_raw( ltrim( $url, '#' ) );
			} else {
				$file = trailingslashit( $upload_dir['subdir'] ) . basename( $url );
			}

			$file_type_arr = wp_check_filetype( basename( $url ) );
			$file_type = $file_type_arr['type'];
		} else {
			$post_type = get_post_type( $post_id );

			// Check for revisions
			if ( $post_type == 'revision' ) {
				$post_type = get_post_type( wp_get_post_parent_id( $post_id ) );
			}

			$cf_file_types = self::get_file_fields( $post_type );

			if ( ! empty( $cf_file_types ) && ! empty( $cf_file_types[ $type ] ) ) {
				$allowed_file_types = $cf_file_types[ $type ];
			} else {
				$allowed_file_types = geodir_image_extensions();
			}

			if ( $order === 0 && $type == 'post_images' ) {
				$attachment_id = media_sideload_image( $url, $post_id, $title, 'id' ); // Uses the post date for the upload time /2009/12/image.jpg

				// Return error object if its an error
				if ( ! $attachment_id || is_wp_error( $attachment_id ) ) {
					return $attachment_id;
				}

				$metadata = wp_get_attachment_metadata( $attachment_id );
				$file_type = wp_check_filetype( basename( $url ) );
				$upload_dir = wp_upload_dir();
				$file = array(
					'file'  => trailingslashit( $upload_dir['basedir'] ) . $metadata['file'],
					'type'  => $file_type['type']
				);

				// Only set the featured image if its approved
				if ( $is_approved && ! wp_is_post_revision( absint( $post_id ) ) ) {
					set_post_thumbnail( $post_id, $attachment_id );
				}
			} else {
				// Move the temp image to the uploads directory
				$file = self::get_external_media( $url, $title, $allowed_file_types );
			}

			// Return error object if its an error
			if ( is_wp_error( $file ) ) {
				return $file;
			}

			if ( isset( $file['type'] ) && $file['type'] ) {
				if ( self::is_image( $file['type'] ) ) {
					$metadata = self::create_image_sizes( $file['file'], $attachment_id ); // Image
				} elseif ( in_array( $file['type'], wp_get_audio_extensions() ) ) {
					$metadata =  wp_read_audio_metadata( $file['file'] ); // Audio
				} elseif ( in_array( $file['type'], wp_get_video_extensions() ) ) {
					$metadata =  wp_read_video_metadata( $file['file'] ); // Video
				}
			}

			// If image meta fail then return error object
			if ( is_wp_error( $metadata ) ) {
				return $metadata;
			}

			/**
			 * Filter the raw file info before save to database.
			 *
			 * This allows us to pass the raw path to things like image optimize plugins.
			 *
			 * @since 2.0.0
			 */
			$file = apply_filters( 'geodir_insert_attachment_file', $file, $post_id );

			// Pre slash the file path
			if ( ! empty( $file['file'] ) ) {
				$file['file'] =  strrev( trailingslashit( strrev ( _wp_relative_upload_path( $file['file'] ) ) ) );
			}

			$file_type = $file['type'];
			$file = $file['file'];
		}

		// Throws exception if file is null.
		if ( is_null( $file ) ) {
			return new WP_Error( 'file_insert', __( "Failed to insert file info to DB.", "geodirectory" ) );
		}

		$file_info = array(
			'post_id' => $post_id,
			'date_gmt' => gmdate( 'Y-m-d H:i:s' ),
			'user_id' => get_current_user_id(),
			'title' => stripslashes_deep( $title ),
			'caption' => stripslashes_deep( $caption ),
			'file' => $file,
			'mime_type' => $file_type,
			'menu_order' => $order,
			'featured' => $order === 0 ? 1 : 0,
			'is_approved' => $is_approved,
			'metadata' => maybe_serialize( $metadata ),
			'type' => $type,
			'other_id' => $other_id
		);

		// Insert into the DB
		$result = $wpdb->insert(
			GEODIR_ATTACHMENT_TABLE,
			$file_info,
			array(
				'%d',
				'%s',
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%d',
				'%d',
				'%s',
				'%s',
				'%d'
			)
		);

		// If DB save failed then return error object
		if ( $result === false ) {
			return new WP_Error( 'file_insert', __( "Failed to insert file info to DB.", "geodirectory" ) );
		}

		// Clear the post attachment cache
		self::clear_attachment_cache( $post_id, $type, $other_id );

		$file_info['ID'] = $wpdb->insert_id;

		/**
		 * @since 2.0.0.75
		 */
		do_action( 'geodir_insert_attachment', $wpdb->insert_id, $file_info, $order );

		// Return the file info
		return $file_info;
	}

    /**
     * Insert the image info to the DB and return the attachment ID.
     *
     * @since 2.0.0
     *
     * @param int $post_id Post ID.
     * @param string $type Type.
     * @param string $file_string Optional. File string. Default null.
     * @return int|string
     */
	public static function update_texts($post_id,$type,$file_string='') {
		global $wpdb;

		$return = '';
		if(!empty($file_string)){

			if ( strpos( $file_string, '|' ) !== false ) {
				$image_info = explode( "|", $file_string );
			}else {
				$image_info[0] = $file_string;
			}

			//print_r($image_info);//exit;
			/*
			 * $image_info[0] = image_url;
			 * $image_info[1] = image_id;
			 * $image_info[2] = image_title;
			 * $image_info[3] = image_caption;
			 */
			$image_id      = ! empty( $image_info[1] ) ? absint( $image_info[1] ) : '';
			$image_title   = ! empty( $image_info[2] ) ? sanitize_text_field( $image_info[2] ) : '';
			$image_caption = ! empty( $image_info[3] ) ? sanitize_text_field( $image_info[3] ) : '';
			// insert into the DB
			$result = $wpdb->update(
				GEODIR_ATTACHMENT_TABLE,
				array(
					'title' => stripslashes_deep( $image_title ),
					'caption' => stripslashes_deep( $image_caption ),
				),
				array('ID' => $image_id,'type'=>$type,'post_id'=>$post_id),
				array(
					'%s',
					'%s',
				)
			);

			$return = $image_id;
		}else{// delete
			//delete_attachment($id, $post_id){
		}

		return $return;
	}

    /**
     * Insert the image info to the DB and return the attachment ID.
     *
     * @since 2.0.0
     *
     * @param int $file_id File id.
     * @param int $post_id Post id.
     * @param string $field Field value.
     * @param string $file_url File url.
     * @param string $file_title Optional. File title. Default null.
     * @param string $file_caption Optional. File caption. Default null.
     * @param string $order Optional. Order. Default null.
     * @param string $is_approved Optional. Is approved. Default 1.
     *
     * @return array|null|object|void|WP_Error
     */
	public static function update_attachment( $file_id, $post_id, $field, $file_url, $file_title = '', $file_caption = '', $order = '', $is_approved = '1', $other_id = '' ) {
		global $wpdb;

		// Check we have what we need
		if ( ! $file_id || ! $post_id || ! $file_url ) {
			return new WP_Error( 'image_insert', __( "No image_id, post_id or image url, image update failed.", "geodirectory" ) );
		}

		// Check the post has not already been deleted
		if ( get_post_status ( $post_id ) === false ) {
			return '';
		}

		// If menu order is 0 then its featured and we need to set the post thumbnail
		if ( $order === 0 && $field == 'post_images' && ! wp_is_post_revision( absint( $post_id ) ) ) {
			// Get the path to the upload directory.
			$wp_upload_dir = wp_upload_dir();
			$file = $wpdb->get_var( $wpdb->prepare( "SELECT file FROM " . GEODIR_ATTACHMENT_TABLE . " WHERE ID = %d", $file_id ) );
			$filename = $wp_upload_dir['basedir'] . $file;
			$featured_img_url = get_the_post_thumbnail_url( $post_id, 'full' );

			if ( $featured_img_url != $file_url && ! geodir_is_full_url( $file ) ) {
				// Delete existing featured attachment post on re-order.
				$wpdb->delete( $wpdb->posts, array( 'post_parent' => (int) $post_id, 'post_type' => 'attachment', 'post_status' => 'inherit' ) );

				$file = wp_check_filetype( basename( $file_url ) );
				$attachment = array(
					'guid'           => $file_url,
					'post_mime_type' => $file['type'],
					'post_title'     => stripslashes_deep( $file_title ),
					'post_content'   => stripslashes_deep( $file_caption ),
					'post_status'    => 'inherit'
				);
				$attachment_id = wp_insert_attachment( $attachment, $filename, $post_id );

				// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
				require_once( ABSPATH . 'wp-admin/includes/image.php' );

				// Generate the metadata for the attachment, and update the database record.
				$attach_data = wp_generate_attachment_metadata( $attachment_id, $filename );
				wp_update_attachment_metadata( $attachment_id , $attach_data );
				set_post_thumbnail( $post_id, $attachment_id );
			} else {
				// Update post thumbnail title for existing attachment.
				$post_thumbnail_id = get_post_thumbnail_id( $post_id );

				if ( $post_thumbnail_id ) {
					$wpdb->update(
						$wpdb->posts,
						array( 'post_title' => stripslashes( $file_title ), 'post_excerpt' => stripslashes( $file_caption ), 'post_content' => stripslashes( $file_caption ) ),
						array( 'ID' => $post_thumbnail_id ),
						array( '%s', '%s', '%s' )
					);
				}
			}
		}

		$data = array(
			'title' => stripslashes_deep( $file_title ),
			'caption' => stripslashes_deep( $file_caption ),
			'menu_order' => $order,
			'featured' => $order === 0 && $field == 'post_images' ? 1 : 0,
			'is_approved' => $is_approved,
		);

		$format = array(
			'%s',
			'%s',
			'%d',
			'%d',
			'%d'
		);

		if ( $other_id ) {
			$data['other_id'] = $other_id;
			$format[] = '%d';
		}

		// insert into the DB
		$result = $wpdb->update(
			GEODIR_ATTACHMENT_TABLE,
			$data,
			array( 'ID' => $file_id ),
			$format
		);

		// If DB save failed then return error object
		if ( $result === false ) {
			return new WP_Error( 'image_insert', __( "Failed to update image info to DB.", "geodirectory" ) );
		}

		// Clear the post attachment cache
		self::clear_attachment_cache( $post_id, $field, $other_id );

		// Attachment info.
		$attachment = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . GEODIR_ATTACHMENT_TABLE . " WHERE ID = %d", $file_id ), ARRAY_A );

		/**
		 * @since 2.0.0.75
		 */
		do_action( 'geodir_update_attachment', $file_id, $attachment, $order );

		if ( $result ) {
			/**
			 * @since 2.0.0.75
			 */
			do_action( 'geodir_updated_attachment', $file_id, $attachment, $order );
		}

		return $attachment;
	}

	/**
	 * Get files via url.
	 *
	 * @param $url
	 * @param string $file_name
	 * @param array $allowed_file_types
	 *
	 * @return array|bool|mixed
	 */
	public static function get_external_media( $url, $file_name = '', $allowed_file_types = array('image/jpg', 'image/jpeg', 'image/gif', 'image/png', 'image/webp', 'image/avif'),$dangerously_set_filetype = array() ) {
		// Gives us access to the download_url() and wp_handle_sideload() functions
		require_once( ABSPATH . 'wp-admin/includes/file.php' );

		$upload_dir = wp_upload_dir();

		// Prevent SSL certificate related issues.
		$temp_file = '';
		if ( ! empty( $upload_dir ) && strpos( $url, $upload_dir['baseurl'] . '/geodir_temp/' ) === 0 ) {
			$temp_url = str_replace( $upload_dir['baseurl'] . '/geodir_temp/', $upload_dir['basedir'] . '/geodir_temp/', $url );

			if ( file_exists( $temp_url ) ) {
				$temp_file = $temp_url;
			}
		}

		// Download file to temp dir
		if ( ! $temp_file ) {
			// URL to the external image.
			$timeout_seconds = 5;

			$temp_file = self::download_url( $url, $timeout_seconds );
		}

		if ( ! is_wp_error( $temp_file ) ) {

			// make sure its an image
			$file_type = !empty($dangerously_set_filetype) ? $dangerously_set_filetype : wp_check_filetype(basename( parse_url( $url, PHP_URL_PATH ) ));

			// Set an array containing a list of acceptable formats
			if ( ! empty( $file_type['ext'] ) && ! empty( $file_type['type'] ) && ( in_array( '*', $allowed_file_types ) || in_array( $file_type['type'], $allowed_file_types ) || in_array( strtolower ( $file_type['ext'] ), $allowed_file_types ) ) ) {
			} else {
				return false;
			}

			// Set the file name to the title if it exists
			$_file_name = !empty($file_name) ? $file_name.".".$file_type['ext'] : basename( $url );

			// Array based on $_FILE as seen in PHP file uploads
			$file = array(
				'name'     => $_file_name, // ex: wp-header-logo.png
				'type'     => $file_type,
				'tmp_name' => $temp_file,
				'error'    => 0,
				'size'     => filesize( $temp_file ),
			);

			//print_r($file);

			$overrides = array(
				// Tells WordPress to not look for the POST form
				// fields that would normally be present as
				// we downloaded the file from a remote server, so there
				// will be no form fields
				// Default is true
				'test_form' => false,

				// Setting this to false lets WordPress allow empty files, not recommended
				// Default is true
				'test_size' => true,
			);

			// Move the temporary file into the uploads directory
			$results = wp_handle_sideload( $file, $overrides );

			// unlink the temp file
			/** @scrutinizer ignore-unhandled */ @unlink($temp_file);

			if ( ! empty( $results['error'] ) ) {
				// Insert any error handling here
				return $results;
			} else {

//				$filename  = $results['file']; // Full path to the file
//				$local_url = $results['url'];  // URL to the file in the uploads dir
//				$type      = $results['type']; // MIME type of the file

				// Perform any actions here based in the above results

				return $results;
			}

		}else{
			return $temp_file; // WP-error
		}
	}

	/**
	 * Duplicate of the WP function but we allow urls with query args.
	 *
	 * @param $url
	 * @param int $timeout
	 *
	 * @return array|bool|string|WP_Error
	 */
	public static function download_url($url, $timeout = 300){
		//WARNING: The file is not automatically deleted, The script must unlink() the file.
		if ( ! $url )
			return new WP_Error('http_no_url', __('Invalid URL Provided.'));

		$url_filename = basename( parse_url( $url, PHP_URL_PATH ) );

		$tmpfname = wp_tempnam( $url_filename );
		if ( ! $tmpfname )
			return new WP_Error('http_no_file', __('Could not create Temporary file.'));

		// WE CHANGE THIS TO LET US DOWNLOAD URLS WITH QUERY ARGS
		$response = wp_remote_get( $url, array( 'timeout' => $timeout, 'stream' => true, 'filename' => $tmpfname ) );

		if ( is_wp_error( $response ) ) {
			if( $tmpfname && file_exists( $tmpfname ) ){
				unlink( $tmpfname );
			}

			return $response;
		}

		if ( 200 != wp_remote_retrieve_response_code( $response ) ){
			unlink( $tmpfname );
			return new WP_Error( 'http_404', trim( wp_remote_retrieve_response_message( $response ) ) );
		}

		$content_md5 = wp_remote_retrieve_header( $response, 'content-md5' );
		if ( $content_md5 ) {
			$md5_check = verify_file_md5( $tmpfname, $content_md5 );
			if ( is_wp_error( $md5_check ) ) {
				unlink( $tmpfname );
				return $md5_check;
			}
		}

		return $tmpfname;
	}


	/**
	 * Delete an attachment by id.
	 *
	 * @param $id
	 * @param $post_id
	 *
	 * @return bool|false|int
	 */
	public static function delete_attachment( $id, $post_id = '', $attachment = '' ) {
		global $wpdb;

		if ( empty( $attachment ) ) {
			$attachment = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . GEODIR_ATTACHMENT_TABLE . " WHERE id = %d AND post_id = %d", $id, $post_id ) );
		}

		// check we have an attachment
		if ( empty( $attachment ) ) {
			return false;
		}

		// unlink the image
		if ( isset( $attachment->file ) && ! empty( $attachment->file ) ) {
			/**
			 * Filters whether a post attachment file deletion should take place.
			 *
			 * @since 2.0.0.71
			 *
			 * @param bool $delete Whether to go forward with deletion.
			 * @param int $id The attachment id.
			 * @param int $post_id Post ID.
			 * @param object $attachment Post attachment.
			 */
			$check = apply_filters( 'geodir_pre_delete_attachment_file', null, $id, $post_id, $attachment );

			if ( null === $check ) {
				$wp_upload_dir = wp_upload_dir();
				$file_path = $wp_upload_dir['basedir'] . $attachment->file;
				wp_delete_file( $file_path ); // delete main image

				if ( ! empty( $attachment->metadata ) ) {
					$metadata = maybe_unserialize( $attachment->metadata );
					// delete other sizes
					if ( ! empty( $metadata['sizes'] ) ) {
						$img_url_basename = wp_basename( $file_path );

						foreach ( $metadata['sizes'] as $size ) {
							if ( ! empty( $size['file'] ) ) {
								$file_path = str_replace( $img_url_basename, wp_basename( $size['file'] ), $wp_upload_dir['basedir'] . $attachment->file );
								wp_delete_file( $file_path ); // delete image size
							}
						}
					}
				}

				// Clear post attachment cache.
				self::clear_attachment_cache( $post_id, $attachment->type );
			}
		}

		$result = false;

		// Remove from DB
		/**
		 * Filters whether a post attachment deletion from DB should take place.
		 *
		 * @since 2.0.0.71
		 *
		 * @param bool $delete Whether to go forward with deletion.
		 * @param int $id The attachment id.
		 * @param int $post_id Post ID.
		 * @param object $attachment Post attachment.
		 */
		$check = apply_filters( 'geodir_pre_delete_attachment_record', null, $id, $post_id, $attachment );
		if ( null === $check ) {
			/**
			 * @since 2.0.0.75
			 */
			do_action( 'geodir_delete_attachment', $id, $attachment );

			$result = $wpdb->query( $wpdb->prepare( "DELETE FROM " . GEODIR_ATTACHMENT_TABLE . " WHERE id = %d AND post_id = %d", $id, $post_id ) );

			if ( $result ) {
				/**
				 * @since 2.0.0.75
				 */
				do_action( 'geodir_deleted_attachment', $id, $attachment );
			}
		}
		return $result;
	}

	/**
	 * Get attachments by type.
	 *
	 * @param $post_id
	 * @param mixed $type
	 * @param string $limit
	 * @param int $revision_id
	 * @param int $other_id
	 * @param int $status
	 *
	 * @return array|null|object
	 */
	public static function get_attachments_by_type( $post_id, $type = 'post_images', $limit = '', $revision_id ='', $other_id = '', $status = '' ) {
		global $wpdb;

		if ( ! empty( $_REQUEST['preview_id'] ) && $revision_id && is_preview() && geodir_listing_belong_to_current_user( $post_id ) && ( $temp_media = get_post_meta( $post_id, "__" . $revision_id, true ) ) ) {
			$attachments = self::get_preview_attachments_by_type( $temp_media, $post_id, $type, $limit, $revision_id, $other_id, $status );

			return $attachments;
		}

		$cache_type_key = $type;
		if ( is_array( $type ) ) {
			$cache_type_key = implode( ":", $type );
			if ( count( $type ) == 1 ) {
				$type = reset( $type );
			}
		}

		// Check for cache
		$cache_key = 'gd_attachments_by_type:' . $post_id . ':' . $cache_type_key. ':' . $limit . ':' . $revision_id . ':' . $other_id . ':' . $status . ':' . (int) is_preview();
		$cache = wp_cache_get( $cache_key, 'gd_attachments_by_type' );
		if ( $cache !== false ) {
			return $cache;
		}

		$limit_sql = '';
		$sql_args = array();
		$default_orderby = " `menu_order` ASC, `ID` DESC ";

		// types
		if ( is_array( $type ) ) {
			$prepare_types = implode( ",", array_fill( 0, count( $type ), '%s' ) );
			foreach ( $type as $key ) {
				$sql_args[] = $key;
				if ( $key == 'comment_images' ) {
					$default_orderby = " `ID` DESC, `menu_order` ASC ";
				}
			}
		} else {
			if ( $type == 'comment_images' ) {
				$default_orderby = " `ID` DESC, `menu_order` ASC ";
			}
			$prepare_types = "%s";
			$sql_args[] = $type;
		}

		// ids
		$prepare_ids = "%d";
		$sql_args[] = $post_id;

		// revision id
		if($revision_id ){
			$prepare_ids .= ",%d";
			$sql_args[] = $revision_id;
		}

		// other ids (things like comments)
		$where = '';
		if ( $other_id ) {
			$where .= " AND other_id = %d";
			$other_id  = absint( $other_id );
			$sql_args[] = $other_id;
		}

		// Status
		if ( $status !== '' ) {
			$where .= absint( $status ) == 0 ? " AND is_approved = 0" : " AND is_approved != 0";
		}

		// order by fields
		$field_orderby = '';
		if(is_array($type)){
			$field_orderby = $wpdb->prepare(" FIELD(type,$prepare_types) ,",$type);
		}

		// limit
		if($limit){
			$limit_sql = ' LIMIT %d ';
			$limit = absint($limit);
			$sql_args[] = $limit;
		}


		// get the results
		$sql = $wpdb->prepare( "SELECT * FROM " . GEODIR_ATTACHMENT_TABLE . " WHERE type IN ( $prepare_types ) AND post_id IN( $prepare_ids ) {$where} ORDER BY $field_orderby $default_orderby $limit_sql", $sql_args );

		$results = $wpdb->get_results($sql);

		// maybe set external meta
		$results = self::set_external_src_meta( $results );

		// set cache
		wp_cache_set( $cache_key, $results, 'gd_attachments_by_type' );
		return $results;

	}

	/**
	 * Get preview attachments by type.
	 *
	 * @param $post_id
	 * @param mixed $type
	 * @param string $limit
	 * @param int $revision_id
	 * @param int $other_id
	 * @param int $status
	 *
	 * @return array|null|object
	 */
	public static function get_preview_attachments_by_type( $temp_media, $post_id, $type = 'post_images', $limit = '', $revision_id ='', $other_id = '', $status = '' ) {
		global $wpdb;

		$types = array();
		$attachments = array();

		if ( empty( $type ) ) {
			$types = array( 'post_images' );
		} else if ( is_scalar( $type ) ) {
			$types = array( $type );
		} else {
			$types = $type;
		}

		if ( in_array( 'post_images', $types ) ) {
			$types = array_unique( array_merge( array( 'post_images' ), $types ) );
		}

		foreach ( $types as $_type ) {
			if ( ! empty( $temp_media[ $_type ] ) ) {
				$_temp_media = explode( "::", $temp_media[ $_type ] );

				foreach ( $_temp_media as $temp_file ) {
					$file_info = explode( "|", $temp_file );

					if ( ! empty( $file_info[1] ) && (int) $file_info[1] > 0 ) {
						$attachment = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `" . GEODIR_ATTACHMENT_TABLE . "` WHERE id = %d", (int) $file_info[1] ) );

						if ( ! empty( $attachment ) ) {
							$attachment->title = ! empty( $file_info[2] ) ? sanitize_text_field( $file_info[2] ) : '';
							$attachment->caption = ! empty( $file_info[3] ) ? sanitize_text_field( $file_info[3] ) : '';

							$attachments[] = $attachment;
						}

						if ( ! empty( $limit ) && (int) $limit == count( $attachments ) ) {
							return self::set_external_src_meta( $attachments );
						}
					}
				}
			}
		}

		return self::set_external_src_meta( $attachments );
	}

	/**
	 * If the file src is eternal then set the meta src as external.
	 *
	 * @param $images
	 *
	 * @return mixed
	 */
	public static function set_external_src_meta( $images ) {
		if ( ! empty( $images ) ) {
			foreach ( $images as $key => $image ) {
				if ( isset( $image->file ) && ! empty( $image->metadata ) && geodir_is_full_url( $image->file ) ) {
					$image_meta = maybe_unserialize( $image->metadata );

					if ( ! empty( $image_meta['file'] ) ) {
						$image_meta['file'] = $image->file;
						$images[$key]->metadata = maybe_serialize( $image_meta );
					}
				}
			}
		}

		return $images;
	}

	/**
	 * Get the post_images of the post.
	 *
	 * @param $post_id
	 * @param string $limit
	 * @param string $revision_id
	 * @param int|string $status Optional. Retrieve images with status passed.
	 *
	 * @return array|null|object
	 */
	public static function get_post_images( $post_id, $limit = '', $revision_id = '', $status = '' ) {
		return self::get_attachments_by_type( $post_id, 'post_images', $limit, $revision_id, '', $status );
	}


	/**
	 * Get the edit string for files per field.
	 *
	 * @param $post_id
	 * @param $field
	 * @param string $revision_id
	 *
	 * @return string
	 */
	public static function get_field_edit_string( $post_id, $field, $revision_id = '', $other_id = '', $is_export = false ) {
		$files = self::get_attachments_by_type( $post_id, $field, '', $revision_id, $other_id );

		if ( ! empty( $files ) ) {
			$wp_upload_dir = wp_upload_dir();
			$files_arr = array();

			foreach( $files as $file ) {
				$is_approved = isset( $file->is_approved ) && $file->is_approved ? '' : '|0';

				if ( $file->menu_order == "-1" ) {
					$is_approved = "|-1";
				}

				if ( geodir_is_full_url( $file->file ) ) {
					$img_src = esc_url_raw( $file->file );

					// Add '#' at start of the url when exporting the images.
					if ( $is_export ) {
						$img_src = '#' . $img_src;
					}
				} else {
					$img_src = $wp_upload_dir['baseurl'] . $file->file;
				}

				$files_arr[] = $img_src . "|" . $file->ID . "|" . $file->title . "|" . $file->caption . $is_approved;
			}
			return implode( "::", $files_arr );
		} else {
			return '';
		}
	}

	/**
	 * @param $post_id
	 *
	 * @return false|int
	 */
	public static function delete_files($post_id,$field=''){
		global $wpdb;
		$result = '';
		if($field=='all'){
			$files = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . GEODIR_ATTACHMENT_TABLE . " WHERE post_id = %d", $post_id));
			if(!empty($files)){
				self::delete_file($files);
			}
			$result = $wpdb->query($wpdb->prepare("DELETE FROM " . GEODIR_ATTACHMENT_TABLE . " WHERE post_id = %d", $post_id));
		}elseif($field){
			$files = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . GEODIR_ATTACHMENT_TABLE . " WHERE post_id = %d AND type = %s", $post_id,$field));
			if(!empty($files)){
				self::delete_file($files);
			}
			$result = $wpdb->query($wpdb->prepare("DELETE FROM " . GEODIR_ATTACHMENT_TABLE . " WHERE post_id = %d AND type = %s", $post_id,$field));
		}

		return $result;
	}

	/**
	 * Delete files from the attachment table.
	 *
	 * @param $files
	 */
	public static function delete_file($files){

		if(!empty($files)){
			foreach($files as $file){
				if(isset($file->file) && $file->file){
					// check if its a post thumbnail also
					if(isset($file->featured) && $file->featured){
						$post_thumbnail_id = get_post_thumbnail_id( $file->post_id );
						wp_delete_attachment( $post_thumbnail_id, true);

						if ( ! empty( $file->type ) ) {
							// Clear post attachment cache.
							self::clear_attachment_cache( $file->post_id, $file->type );
						}
					}else{
						self::delete_attachment($file->ID,$file->post_id, $file);
					}
				}
			}
		}

	}

	/**
	 * Set a temp upload directory for post attachments before they are saved.
	 *
	 * @since 2.0.0
	 * @param array $upload Array of upload directory data with keys of 'path','url', 'subdir, 'basedir', and 'error'.
	 *
	 * @return array Returns upload directory details as an array.
	 */
	public static function temp_upload_dir( $upload ) {
		$upload['subdir'] = "/geodir_temp";
		$upload['path']   = $upload['basedir'] . $upload['subdir'];
		$upload['url']    = $upload['baseurl'] . $upload['subdir'];

		return $upload;
	}

	/**
	 * Update the revision images IDs to the parent post on save.
	 *
	 * @param $post_id
	 * @param $revision_id
	 */
	public static function revision_to_parent($post_id,$revision_id){
		if(!empty($post_id) && !empty($revision_id)){
			global $wpdb;
			$result = $wpdb->update(
				GEODIR_ATTACHMENT_TABLE,
				array(
					'post_id' => $post_id
				),
				array('post_id' => $revision_id),
				array(
					'%d'
				)
			);
		}
	}

	/**
	 * Upload image from URL.
	 *
	 * @since 2.0.0
	 * @param string $image_url
	 * @return array|WP_Error Attachment data or error message.
	 */
	public static function upload_image_from_url( $image_url ) {
		$file_name  = basename( current( explode( '?', $image_url ) ) );
		$parsed_url = @parse_url( $image_url );

		// Check parsed URL.
		if ( ! $parsed_url || ! is_array( $parsed_url ) ) {
			return new WP_Error( 'geodir_invalid_image_url', sprintf( __( 'Invalid URL %s.', 'geodirectory' ), $image_url ), array( 'status' => 400 ) );
		}

		// Ensure url is valid.
		$image_url = esc_url_raw( $image_url );

		// Get the file.
		$response = wp_safe_remote_get( $image_url, array(
			'timeout' => 10,
		) );

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'geodir_invalid_remote_image_url', sprintf( __( 'Error getting remote image %s.', 'geodirectory' ), $image_url ) . ' ' . sprintf( __( 'Error: %s.', 'geodirectory' ), $response->get_error_message() ), array( 'status' => 400 ) );
		} elseif ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return new WP_Error( 'geodir_invalid_remote_image_url', sprintf( __( 'Error getting remote image %s.', 'geodirectory' ), $image_url ), array( 'status' => 400 ) );
		}

		// Ensure we have a file name and type.
		$wp_filetype = wp_check_filetype( $file_name );

		if ( ! $wp_filetype['type'] ) {
			$headers = wp_remote_retrieve_headers( $response );
			if ( isset( $headers['content-disposition'] ) && strstr( $headers['content-disposition'], 'filename=' ) ) {
				$disposition = end( explode( 'filename=', $headers['content-disposition'] ) );
				$disposition = sanitize_file_name( $disposition );
				$file_name   = $disposition;
			} elseif ( isset( $headers['content-type'] ) && strstr( $headers['content-type'], 'image/' ) ) {
				$file_name = 'image.' . str_replace( 'image/', '', $headers['content-type'] );
			}
			unset( $headers );

			// Recheck filetype
			$wp_filetype = wp_check_filetype( $file_name );

			if ( ! $wp_filetype['type'] ) {
				return new WP_Error( 'geodir_invalid_image_type', __( 'Invalid image type.', 'geodirectory' ), array( 'status' => 400 ) );
			}
		}

		// Upload the file.
		$upload = wp_upload_bits( $file_name, '', wp_remote_retrieve_body( $response ) );

		if ( $upload['error'] ) {
			return new WP_Error( 'geodir_image_upload_error', $upload['error'], array( 'status' => 400 ) );
		}

		// Get filesize.
		$filesize = filesize( $upload['file'] );

		if ( 0 == $filesize ) {
			/** @scrutinizer ignore-unhandled */ @unlink( $upload['file'] );
			unset( $upload );

			return new WP_Error( 'geodir_image_upload_file_error', __( 'Zero size file downloaded.', 'geodirectory' ), array( 'status' => 400 ) );
		}

		do_action( 'geodir_uploaded_image_from_url', $upload, $image_url );

		return $upload;
	}

	/**
	 * Set uploaded image as attachment.
	 *
	 * @since 2.0.0
	 * @param array $upload Upload information from wp_upload_bits.
	 * @param int $id Post ID. Default to 0.
	 * @return int Attachment ID
	 */
	public static function set_uploaded_image_as_attachment( $upload, $id = 0 ) {
		if ( empty( $upload['file'] ) ) {
			return false;
		}

		$info    = wp_check_filetype( $upload['file'] );
		if ( empty( $info['type'] ) ) {
			return false;
		}

		$title   = '';
		$content = '';

		if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/image.php' );
		}

		if ( $image_meta = wp_read_image_metadata( $upload['file'] ) ) {
			if ( trim( $image_meta['title'] ) && ! is_numeric( sanitize_title( $image_meta['title'] ) ) ) {
				$title = geodir_clean( $image_meta['title'] );
			}
			if ( trim( $image_meta['caption'] ) ) {
				$content = geodir_clean( $image_meta['caption'] );
			}
		}

		$attachment = array(
			'post_mime_type' => $info['type'],
			'guid'           => $upload['url'],
			'post_parent'    => $id,
			'post_title'     => $title ? stripslashes_deep( $title ) : stripslashes_deep( basename( $upload['file'] ) ),
			'post_content'   => stripslashes_deep( $content ),
		);

		$attachment_id = wp_insert_attachment( $attachment, $upload['file'], $id );
		if ( ! is_wp_error( $attachment_id ) ) {
			wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $upload['file'] ) );
		}

		return $attachment_id;
	}

	/**
	 * Count total image attachments.
	 *
	 * @since 2.1.0.10
	 *
	 * @global object $wpdb WordPress Database object.
	 *
	 * @return int Total image attachments.
	 */
	public static function count_image_attachments() {
		global $wpdb;

		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `" . GEODIR_ATTACHMENT_TABLE . "` WHERE `mime_type` LIKE 'image/%' OR `type` = %s", array( 'post_images' ) ) );
	}

	/**
	 * Regenerate thumbnails for bulk attachments.
	 *
	 * @since 2.1.0.10
	 *
	 * @global object $wpdb WordPress Database object.
	 *
	 * @param  int $page_no Page number.
	 * @param  int $per_page Per page.
	 * @return mixed
	 */
	public static function generate_bulk_attachment_metadata( $page_no, $per_page ) {
		global $wpdb;

		if ( $page_no < 1 ) {
			$page_no = 1;
		}

		if ( $per_page < 1 ) {
			$per_page = 10;
		}

		$data = array(
			'success' => array(),
			'error' => array(),
			'processed' => 0
		);

		$offset = ( $page_no - 1 ) * $per_page;

		if ( $offset > 0 ) {
			$limit = "LIMIT " . $offset . "," . $per_page;
		} else {
			$limit = "LIMIT " . $per_page;
		}

		$attachments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . GEODIR_ATTACHMENT_TABLE . "` WHERE `mime_type` LIKE 'image/%' OR `type` = %s ORDER BY ID ASC {$limit}", array( 'post_images' ) ) );

		if ( ! empty( $attachments ) ) {
			foreach ( $attachments as $attachment ) {
				$result = self::generate_attachment_metadata( $attachment );

				$data['processed'] = $data['processed'] + 1;
				if ( is_wp_error( $result ) ) {
					geodir_error_log( $result->get_error_message(), __( 'Generate attachment metadata', 'geodirectory' ) );

					$data['error'][ $attachment->ID ] = $result;
				} else {
					$data['success'][ $attachment->ID ] = $result;
				}
			}
		} else {
		}

		return $data;
	}

	/**
	 * Regenerate post thumbnails.
	 *
	 * @since 2.1.0.10
	 *
	 * @global object $wpdb WordPress Database object.
	 *
	 * @param  int $post_id The post ID.
	 * @return mixed
	 */
	public static function generate_post_attachment_metadata( $post_id ) {
		global $wpdb;

		if ( empty( $post_id ) ) {
			return new WP_Error( 'gd-invalid-post-id', __( 'Invalid post id!', 'geodirectory' ) );
		}

		$data = array(
			'success' => array(),
			'error' => array()
		);

		$attachments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . GEODIR_ATTACHMENT_TABLE . "` WHERE `post_id` = %d AND ( `mime_type` LIKE 'image/%' OR `type` = %s ) ORDER BY ID ASC", array( $post_id, 'post_images' ) ) );

		if ( ! empty( $attachments ) ) {
			foreach ( $attachments as $attachment ) {
				$result = self::generate_attachment_metadata( $attachment );

				if ( is_wp_error( $result ) ) {
					geodir_error_log( $result->get_error_message(), __( 'Generate attachment metadata', 'geodirectory' ) );

					$data['error'][ $attachment->ID ] = $result;
				} else {
					$data['success'][ $attachment->ID ] = $result;
				}
			}
		}

		return $data;
	}

	/**
	 * Regenerate attachment metadata.
	 *
	 * @since 2.1.0.10
	 *
	 * @global object $wpdb WordPress Database object.
	 *
	 * @param object $attachment Post attachment object.
	 * @return mixed
	 */
	public static function generate_attachment_metadata( $attachment = array() ) {
		global $wpdb;

		if ( ! ( ! empty( $attachment ) && ! empty( $attachment->ID ) && ! empty( $attachment->file ) ) ) {
			return new WP_Error( 'gd-invalid-attachment', __( 'Invalid attachment!', 'geodirectory' ) );
		}

		// Full or external image url
		if ( geodir_is_full_url( $attachment->file ) || strpos( $attachment->file, '#' ) === 0 ) {
			return new WP_Error( 'gd-external-image', __( 'Attachment metadata not generated for external image!', 'geodirectory' ) );
		}

		$wp_upload_dir = wp_upload_dir();

		$file_path = $wp_upload_dir['basedir'] . $attachment->file;

		if ( is_file( $file_path ) && file_exists( $file_path ) ) {
			$metadata = self::create_image_sizes( $file_path );

			if ( is_wp_error( $metadata ) ) {
				return $metadata;
			}

			if ( ! empty( $metadata ) && is_array( $metadata ) ) {
				$wpdb->update( GEODIR_ATTACHMENT_TABLE, array( 'metadata' => maybe_serialize( $metadata ) ), array( 'ID' => $attachment->ID ) );
			}
		} else {
			$metadata = new WP_Error( 'gd-image-notfound', wp_sprintf( __( '%s not exists!', 'geodirectory' ), $wp_upload_dir['baseurl'] . $attachment->file ) );
		}

		return $metadata;
	}

	/**
	 * Clear attachment cache.
	 *
	 * @since 2.3.2
	 *
	 * @param int      $post_id The post ID.
	 * @param string   $type Attachment type.
	 * @param int|null $other_id The other ID.
	 */
	public static function clear_attachment_cache( $post_id, $type = '', $other_id = '' ) {
		// 'gd_attachments_by_type:' . $post_id . ':' . $type. ':' . $limit . ':' . $revision_id . ':' . $other_id . ':' . $status . ':' . (int) is_preview()
		if ( $other_id !== '' ) {
			wp_cache_delete( 'gd_attachments_by_type:' . $post_id . ':' . $type . ':::' . $other_id . '::0', 'gd_attachments_by_type' );
			wp_cache_delete( 'gd_attachments_by_type:' . $post_id . ':' . $type . ':::' . $other_id . ':1:0', 'gd_attachments_by_type' );

			if ( is_preview() ) {
				wp_cache_delete( 'gd_attachments_by_type:' . $post_id . ':' . $type . ':::' . $other_id . '::1', 'gd_attachments_by_type' );
				wp_cache_delete( 'gd_attachments_by_type:' . $post_id . ':' . $type . ':::' . $other_id . ':1:1', 'gd_attachments_by_type' );
			}
		}

		wp_cache_delete( 'gd_attachments_by_type:' . $post_id . ':' . $type . ':::::0', 'gd_attachments_by_type' );
		wp_cache_delete( 'gd_attachments_by_type:' . $post_id . ':' . $type . ':1::::0', 'gd_attachments_by_type' );
		wp_cache_delete( 'gd_attachments_by_type:' . $post_id . ':' . $type . '::::1:0', 'gd_attachments_by_type' );
		wp_cache_delete( 'gd_attachments_by_type:' . $post_id . ':' . $type . ':1:::1:0', 'gd_attachments_by_type' );

		if ( is_preview() ) {
			wp_cache_delete( 'gd_attachments_by_type:' . $post_id . ':' . $type . ':::::1', 'gd_attachments_by_type' );
			wp_cache_delete( 'gd_attachments_by_type:' . $post_id . ':' . $type . ':1::::1', 'gd_attachments_by_type' );
			wp_cache_delete( 'gd_attachments_by_type:' . $post_id . ':' . $type . '::::1:1', 'gd_attachments_by_type' );
			wp_cache_delete( 'gd_attachments_by_type:' . $post_id . ':' . $type . ':1:::1:1', 'gd_attachments_by_type' );
		}
	}


	/**
	 * Retrieves an attachment by its ID.
	 *
	 * This function fetches an attachment from the database using its ID and optional status.
	 * It uses caching to optimize performance for repeated requests.
	 *
	 * @param int $attachment_id The ID of the attachment to retrieve.
	 * @param string $status Optional. The status of the attachment. Default is an empty string.
	 *
	 * @return object|null The attachment row from the database or null if not found.
	 */
	public static function get_attachment_by_id( $attachment_id, $status = '' ) {
		global $wpdb;


		// Check for cache
		$cache_key = 'gd_attachment_by_id:' . $attachment_id . ':' . $status . ':' . (int) is_preview();
		$cache = wp_cache_get( $cache_key, 'gd_attachment_by_id' );
		if ( $cache !== false ) {
			return $cache;
		}


		// get the results
		$sql = $wpdb->prepare( "SELECT * FROM " . GEODIR_ATTACHMENT_TABLE . " WHERE ID = %d LIMIT 1", $attachment_id );

		$results = $wpdb->get_row($sql);

		// maybe set external meta
		$results = self::set_external_src_meta( $results );

		// set cache
		wp_cache_set( $cache_key, $results, 'gd_attachment_by_id' );
		return $results;

	}
}
