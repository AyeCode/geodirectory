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
	 * Handles post image upload.
	 *
	 * @since 1.0.0
	 * @package GeoDirectory
	 */
	public static function post_attachment_upload() {

		// the post id
		$imgid = isset($_POST["imgid"]) ? esc_attr($_POST["imgid"]) : '';
		$post_id = isset($_POST["post_id"]) ? absint($_POST["post_id"]) : '';

		// set GD temp upload dir
		add_filter( 'upload_dir', array( __CLASS__, 'temp_upload_dir' ) );

		// change file orientation if needed
		//$fixed_file = geodir_exif($_FILES[$imgid . 'async-upload']);

		$fixed_file = $_FILES[ $imgid . 'async-upload' ];

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
			//echo $status['url'];

			// get the image id and path
			$image_url = self::insert_image_attachment($post_id,$status['url'],'', '', -1,0);

			if($image_url){
				$image_id = self::get_id_from_file_path($image_url,$post_id);
			}

			$wp_upload_dir = wp_upload_dir();
			echo $wp_upload_dir['baseurl'] . $image_url ."|$image_id||";

			//echo '###'.$image_url.'###';


		} elseif( isset( $status['url'] )) {
			echo $status['url'];
		}
		else
		{
			echo 'x';
		}

		exit;
	}

	/**
	 * Get the attachment id from the file path.
	 *
	 * @param $path
	 * @param string $post_id
	 *
	 * @return null|string
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
	 * @param $file
	 */
	public static function create_image_sizes( $file ){
		$metadata = array();
		$imagesize = getimagesize( $file );
		$metadata['width'] = $imagesize[0];
		$metadata['height'] = $imagesize[1];

		// Make the file path relative to the upload dir.
		$metadata['file'] = _wp_relative_upload_path($file);

		// Make thumbnails and other intermediate sizes.
		$_wp_additional_image_sizes = wp_get_additional_image_sizes();

		$sizes = array();
		foreach ( get_intermediate_image_sizes() as $s ) {
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
		 *
		 * @param array $sizes    An associative array of image sizes.
		 * @param array $metadata An associative array of image metadata: width, height, file.
		 */
		$sizes = apply_filters( 'intermediate_image_sizes_advanced', $sizes, $metadata );

		if ( $sizes ) { 
			$editor = wp_get_image_editor( $file );

			if ( ! is_wp_error( $editor ) )
				$metadata['sizes'] = $editor->multi_resize( $sizes );
		} else {
			$metadata['sizes'] = array();
		}

		// Fetch additional metadata from EXIF/IPTC.
		$image_meta = wp_read_image_metadata( $file );
		if ( $image_meta )
			$metadata['image_meta'] = $image_meta;

		return $metadata;
	}

	/**
	 * Insert the image info to the DB and return the attachment ID.
	 * 
	 * @param $post_id
	 * @param $image_url
	 * @param string $image_title
	 * @param string $image_caption
	 * @param string $order
	 * @param int $is_approved
	 *
	 * @return array|WP_Error
	 */
	public static function insert_image_attachment($post_id,$image_url,$image_title = '', $image_caption = '', $order = '', $is_approved = 1){
		global $wpdb;

//		echo '###'.$post_id. " \n";
//		echo '###'.$image_url. " \n";
//		echo '###'.$image_title. " \n";
//		echo '###'.$image_caption. " \n";
//		echo '###'.$order. " \n";
//		echo '###'.$is_approved. " \n";

		// check we have what we need
		if(!$post_id || !$image_url){
			return new WP_Error( 'image_insert', __( "No post_id or image url, image insert failed.", "geodirectory" ) );
		}

		// if menu order is 0 then its featured and we need to set the post thumbnail
		if($order === 0 ){
			$attachment_id = media_sideload_image($image_url, $post_id, $image_title, 'id');
			// return error object if its an error
			if (!$attachment_id || is_wp_error( $attachment_id ) ) {
				return $attachment_id;
			}

			$metadata = wp_get_attachment_metadata( $attachment_id );
			$file = wp_check_filetype(basename($image_url));

			// only set the featured image if its approved
			if($is_approved ){
				set_post_thumbnail($post_id, $attachment_id);
			}


		}else{

			// move the temp image to the uploads directory
			$file = self::get_external_media( $image_url, $image_title );

			// return error object if its an error
			if ( is_wp_error($file  ) ) {
				return $file;
			}

			// create the different image sizes and get the image meta data
			$metadata = self::create_image_sizes( $file['file'] );

			// if image meta fail then return error object
			if ( is_wp_error($metadata ) ) {
				return $metadata;
			}
		}


		// pre slash the file path
		if(!empty($metadata['file'])){
			$metadata['file'] = "/".$metadata['file'];
		}

		// insert into the DB
		$result = $wpdb->insert(
			GEODIR_ATTACHMENT_TABLE,
			array(
				'post_id' => $post_id,
				'user_id'   => get_current_user_id(),
				'title' => $image_title,
				'caption' => $image_caption,
				'file' => $metadata['file'],
				'mime_type' => $file['type'],
				'menu_order' => $order,
				'featured' => $order === 0 ? 1 : 0,
				'is_approved' => $is_approved,
				'metadata' => maybe_serialize($metadata)
			),
			array(
				'%d',
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%d',
				'%d',
				'%s'
			)
		);

		// if DB save failed then return error object
		if ( !$result ) {
			return new WP_Error( 'image_insert', __( "Failed to insert image info to DB.", "geodirectory" ) );
		}

		// return the file path
		return $metadata['file'];

	}

	/**
	 * Insert the placeholder image info to the DB and return the attachment ID.
	 *
	 * This is used for CSV import so the image can be added to the listing and uploaded after.
	 *
	 * @param $post_id
	 * @param $image_url
	 * @param string $image_title
	 * @param string $image_caption
	 * @param string $order
	 * @param int $is_approved
	 *
	 * @return array|WP_Error
	 */
	public static function insert_placeholder_image_attachment($post_id,$image_url,$image_title = '', $image_caption = '', $order = '', $is_approved = 1){
		global $wpdb;

		// check we have what we need
		if(!$post_id || !$image_url){
			return new WP_Error( 'image_insert', __( "No post_id or image url, image insert failed.", "geodirectory" ) );
		}

		$upload_dir = wp_upload_dir();

		$file = $upload_dir['subdir'].'/'.basename($image_url);
		$file_type = wp_check_filetype( basename($image_url));
		$metadata = '';


		// insert into the DB
		$result = $wpdb->insert(
			GEODIR_ATTACHMENT_TABLE,
			array(
				'post_id' => $post_id,
				'user_id'   => get_current_user_id(),
				'title' => $image_title,
				'caption' => $image_caption,
				'file' => $file,
				'mime_type' => $file_type,
				'menu_order' => $order,
				'featured' => $order === 0 ? 1 : 0,
				'is_approved' => $is_approved,
				'metadata' => maybe_serialize($metadata)
			),
			array(
				'%d',
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%d',
				'%d',
				'%s'
			)
		);

		// if DB save failed then return error object
		if ( !$result ) {
			return new WP_Error( 'image_insert', __( "Failed to insert image info to DB.", "geodirectory" ) );
		}

		// return the file path
		return $metadata['file'];

	}

	/**
	 * Insert the image info to the DB and return the attachment ID.
	 *
	 * @param $post_id
	 * @param $image_url
	 * @param string $image_title
	 * @param string $image_caption
	 * @param string $order
	 * @param int $is_approved
	 *
	 * @return array|WP_Error
	 */
	public static function update_image_texts($image_id, $post_id,$image_url,$image_title = '', $image_caption = '', $order = ''){
		global $wpdb;

		// check we have what we need
		if(!$image_id || !$post_id || !$image_url){
			return new WP_Error( 'image_insert', __( "No image_id, post_id or image url, image update failed.", "geodirectory" ) );
		}

		// if menu order is 0 then its featured and we need to set the post thumbnail
		if($order === 0){
			// Get the path to the upload directory.
			$wp_upload_dir = wp_upload_dir();
			$filename = $wp_upload_dir['basedir'] . $wpdb->get_var($wpdb->prepare("SELECT file FROM ".GEODIR_ATTACHMENT_TABLE." WHERE ID = %d",$image_id));
			$featured_img_url = get_the_post_thumbnail_url($post_id,'full');
			//echo $featured_img_url.'###'.$image_url;exit;
			if($featured_img_url != $image_url){
				$file = wp_check_filetype(basename($image_url));
				$attachment = array(
					'guid'           => $image_url,
					'post_mime_type' => $file['type'],
					'post_title'     => $image_title,
					'post_content'   => $image_caption,
					'post_status'    => 'inherit'
				);
				$attachment_id = wp_insert_attachment( $attachment, $filename, $post_id );

				// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
				require_once( ABSPATH . 'wp-admin/includes/image.php' );

				// Generate the metadata for the attachment, and update the database record.
				$attach_data = wp_generate_attachment_metadata( $attachment_id , $filename );
				wp_update_attachment_metadata( $attachment_id , $attach_data );
				set_post_thumbnail($post_id, $attachment_id);
			}

		}

		// check if file is local

		// insert into the DB
		$result = $wpdb->update(
			GEODIR_ATTACHMENT_TABLE,
			array(
				'title' => $image_title,
				'caption' => $image_caption,
				'menu_order' => $order,
				'featured' => $order === 0 ? 1 : 0,
			),
			array('ID' => $image_id),
			array(
				'%s',
				'%s',
				'%d',
				'%d'
			)
		);


		// if DB save failed then return error object
		if ( $result === false ) {
			return new WP_Error( 'image_insert', __( "Failed to update image info to DB.", "geodirectory" ) );
		}


		// return the file path
		return $wpdb->get_var($wpdb->prepare("SELECT file FROM ".GEODIR_ATTACHMENT_TABLE." WHERE ID = %d",$image_id));

	}

	public static function get_external_media( $url, $file_name = '', $allowed_file_types = array('image/jpg', 'image/jpeg', 'image/gif', 'image/png') ) {
		// Gives us access to the download_url() and wp_handle_sideload() functions
		require_once( ABSPATH . 'wp-admin/includes/file.php' );

		// URL to the external image.
		$timeout_seconds = 5;

		// Download file to temp dir
		$temp_file = download_url( $url, $timeout_seconds );

		if ( ! is_wp_error( $temp_file ) ) {

			// make sure its an image
			$file_type = wp_check_filetype(basename($url));

			// Set an array containing a list of acceptable formats
			if(!empty($file_type['ext']) && !empty($file_type['type']) && in_array($file_type['type'],$allowed_file_types)){}else{return false;}


			// Set the fiel name tot he title if it exists
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
			@unlink($temp_file);

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


	public static function delete_attachment($id, $post_id){
		global $wpdb;
		$attachment = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . GEODIR_ATTACHMENT_TABLE . " WHERE id = %d AND post_id = %d", $id, $post_id));

		// check we have an attachment
		if(!$attachment){return false;}

		// unlink the image
		if(isset($attachment->file) && $attachment->file){
			$wp_upload_dir = wp_upload_dir();
			$file_path = $wp_upload_dir['basedir'] . $attachment->file;
			@wp_delete_file( $file_path );
		}

		// remove from DB
		$result = $wpdb->query($wpdb->prepare("DELETE FROM " . GEODIR_ATTACHMENT_TABLE . " WHERE id = %d AND post_id = %d", $id, $post_id));
		return $result;
	}

	public static function get_attachments_by_type($post_id,$type = 'post_image',$limit = '',$revision_id =''){
		global $wpdb;
		$limit_sql = '';
		$sql_args = array();
		$sql_args[] = $type;
		$sql_args[] = $post_id;




		if($limit){
			$limit_sql = ' LIMIT %d ';
			$limit = absint($limit);
		}
		if($revision_id ){
			$sql_args[] = $revision_id;
			if($limit){$sql_args[] = $limit;}
			return $wpdb->get_results($wpdb->prepare("SELECT * FROM " . GEODIR_ATTACHMENT_TABLE . " WHERE type = %s AND post_id IN (%d,%d)  ORDER BY menu_order $limit_sql",$sql_args));
		}else{
			if($limit){$sql_args[] = $limit;}
//			echo '###'.$wpdb->prepare("SELECT * FROM " . GEODIR_ATTACHMENT_TABLE . " WHERE  1 = %d  ORDER BY menu_order",1);
//			print_r($wpdb->get_results($wpdb->prepare("SELECT * FROM " . GEODIR_ATTACHMENT_TABLE . " WHERE  1 = %d  ORDER BY menu_order",1)));
//			exit;
			return $wpdb->get_results($wpdb->prepare("SELECT * FROM " . GEODIR_ATTACHMENT_TABLE . " WHERE type = %s AND post_id = %d ORDER BY menu_order $limit_sql",$sql_args));
		}
	}

	public static function get_post_images($post_id,$limit = '',$revision_id = ''){
		return self::get_attachments_by_type($post_id,'post_image',$limit,$revision_id );
	}

	public static function get_post_images_edit_string($post_id,$revision_id = ''){
		$post_images = self::get_post_images($post_id,'',$revision_id);

		if(!empty($post_images)){
			$wp_upload_dir = wp_upload_dir();
			$images_arr = array();
			foreach( $post_images as $image ){

				$is_approved = isset($image->is_approved) && $image->is_approved ? '' : '|0';

				//print_r($image);
				$images_arr[] = $wp_upload_dir['baseurl'].$image->file."|".$image->ID."|".$image->title."|".$image->caption . $is_approved;
			}
			return implode(",",$images_arr);

		}else{
			return '';
		}

	}

	/**
	 * @param $post_id
	 *
	 * @return false|int
	 * @todo we need to remove the images from the folders.
	 */
	public static function delete_post_images($post_id){
		global $wpdb;
		$result = $wpdb->query($wpdb->prepare("DELETE FROM " . GEODIR_ATTACHMENT_TABLE . " WHERE post_id = %d AND type = 'post_image'", $post_id));
		delete_post_thumbnail( $post_id);
		return $result;
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
}