<?php
/**
 * GeoDirectory REST Functions
 *
 * Functions for REST specific things.
 *
 * @author   GeoDirectory
 * @category Core
 * @package  GeoDirectory/Functions
 * @version  2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Parses and formats a date for ISO8601/RFC3339.
 *
 * @since  2.0.0
 * @param  string|null|GeoDir_DateTime $date
 * @param  bool Send false to get local/offset time.
 * @return string|null ISO8601/RFC3339 formatted datetime.
 */
function geodir_rest_prepare_date_response( $date, $utc = true ) {
	if ( is_numeric( $date ) ) {
		$date = new GeoDir_DateTime( "@$date", new DateTimeZone( 'UTC' ) );
		$date->setTimezone( new DateTimeZone( geodir_timezone_string() ) );
	} elseif ( is_string( $date ) ) {
		$date = new GeoDir_DateTime( $date, new DateTimeZone( 'UTC' ) );
		$date->setTimezone( new DateTimeZone( geodir_timezone_string() ) );
	}

	if ( ! is_a( $date, 'GeoDir_DateTime' ) ) {
		return null;
	}

	// Get timestamp before changing timezone to UTC.
	return gmdate( 'Y-m-d\TH:i:s', $utc ? $date->getTimestamp() : $date->getOffsetTimestamp() );
}

/**
 * Returns image mime types users are allowed to upload via the API.
 * @since  2.0.0
 * @return array
 */
function geodir_rest_allowed_image_mime_types() {
	return apply_filters( 'geodir_rest_allowed_image_mime_types', array(
		'jpg|jpeg|jpe' => 'image/jpeg',
		'gif'          => 'image/gif',
		'png'          => 'image/png',
		'bmp'          => 'image/bmp',
		'tiff|tif'     => 'image/tiff',
		'ico'          => 'image/x-icon',
	) );
}

/**
 * Upload image from URL.
 *
 * @since 2.0.0
 * @param string $image_url
 * @return array|WP_Error Attachment data or error message.
 */
function geodir_rest_upload_image_from_url( $image_url ) {
	$file_name  = basename( current( explode( '?', $image_url ) ) );
	$parsed_url = @parse_url( $image_url );

	// Check parsed URL.
	if ( ! $parsed_url || ! is_array( $parsed_url ) ) {
		return new WP_Error( 'geodir_rest_invalid_image_url', sprintf( __( 'Invalid URL %s.', 'geodirectory' ), $image_url ), array( 'status' => 400 ) );
	}

	// Ensure url is valid.
	$image_url = esc_url_raw( $image_url );

	// Get the file.
	$response = wp_safe_remote_get( $image_url, array(
		'timeout' => 10,
	) );

	if ( is_wp_error( $response ) ) {
		return new WP_Error( 'geodir__rest_invalid_remote_image_url', sprintf( __( 'Error getting remote image %s.', 'geodirectory' ), $image_url ) . ' ' . sprintf( __( 'Error: %s.', 'geodirectory' ), $response->get_error_message() ), array( 'status' => 400 ) );
	} elseif ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
		return new WP_Error( 'geodir__rest_invalid_remote_image_url', sprintf( __( 'Error getting remote image %s.', 'geodirectory' ), $image_url ), array( 'status' => 400 ) );
	}

	// Ensure we have a file name and type.
	$wp_filetype = wp_check_filetype( $file_name, geodir_rest_allowed_image_mime_types() );

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
		$wp_filetype = wp_check_filetype( $file_name, geodir_rest_allowed_image_mime_types() );

		if ( ! $wp_filetype['type'] ) {
			return new WP_Error( 'geodir__rest_invalid_image_type', __( 'Invalid image type.', 'geodirectory' ), array( 'status' => 400 ) );
		}
	}

	// Upload the file.
	$upload = wp_upload_bits( $file_name, '', wp_remote_retrieve_body( $response ) );

	if ( $upload['error'] ) {
		return new WP_Error( 'geodir__rest_image_upload_error', $upload['error'], array( 'status' => 400 ) );
	}

	// Get filesize.
	$filesize = filesize( $upload['file'] );

	if ( 0 == $filesize ) {
		@unlink( $upload['file'] );
		unset( $upload );

		return new WP_Error( 'geodir__rest_image_upload_file_error', __( 'Zero size file downloaded.', 'geodirectory' ), array( 'status' => 400 ) );
	}

	do_action( 'geodir_rest_api_uploaded_image_from_url', $upload, $image_url );

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
function geodir_rest_set_uploaded_image_as_attachment( $upload, $id = 0 ) {
	$info    = wp_check_filetype( $upload['file'] );
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
		'post_title'     => $title ? $title : basename( $upload['file'] ),
		'post_content'   => $content,
	);

	$attachment_id = wp_insert_attachment( $attachment, $upload['file'], $id );
	if ( ! is_wp_error( $attachment_id ) ) {
		wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $upload['file'] ) );
	}

	return $attachment_id;
}

/**
 * Validate reports request arguments.
 *
 * @since 2.0.0
 * @param mixed $value
 * @param WP_REST_Request $request
 * @param string $param
 * @return WP_Error|boolean
 */
function geodir_rest_validate_reports_request_arg( $value, $request, $param ) {

	$attributes = $request->get_attributes();
	if ( ! isset( $attributes['args'][ $param ] ) || ! is_array( $attributes['args'][ $param ] ) ) {
		return true;
	}
	$args = $attributes['args'][ $param ];

	if ( 'string' === $args['type'] && ! is_string( $value ) ) {
		return new WP_Error( 'geodir__rest_invalid_param', sprintf( __( '%1$s is not of type %2$s', 'geodirectory' ), $param, 'string' ) );
	}

	if ( 'date' === $args['format'] ) {
		$regex = '#^\d{4}-\d{2}-\d{2}$#';

		if ( ! preg_match( $regex, $value, $matches ) ) {
			return new WP_Error( 'geodir__rest_invalid_date', __( 'The date you provided is invalid.', 'geodirectory' ) );
		}
	}

	return true;
}

/**
 * Encodes a value according to RFC 3986.
 * Supports multidimensional arrays.
 *
 * @since 2.0.0
 * @param string|array $value The value to encode.
 * @return string|array       Encoded values.
 */
function geodir_rest_urlencode_rfc3986( $value ) {
	if ( is_array( $value ) ) {
		return array_map( 'geodir_rest_urlencode_rfc3986', $value );
	} else {
		return str_replace( array( '+', '%7E' ), array( ' ', '~' ), rawurlencode( $value ) );
	}
}

/**
 * Check permissions of posts on REST API.
 *
 * @since 2.0.0
 * @param string $post_type Post type.
 * @param string $context   Request context.
 * @param int    $object_id Post ID.
 * @return bool
 */
function geodir_rest_check_post_permissions( $post_type, $context = 'read', $object_id = 0 ) {
	$contexts = array(
		'read'   => 'read_private_posts',
		'create' => 'publish_posts',
		'edit'   => 'edit_post',
		'delete' => 'delete_post',
		'batch'  => 'edit_others_posts',
	);

	if ( 'revision' === $post_type ) {
		$permission = false;
	} else {
		$cap = $contexts[ $context ];
		$post_type_object = get_post_type_object( $post_type );
		$permission = current_user_can( $post_type_object->cap->$cap, $object_id );
	}

	return apply_filters( 'geodir_rest_check_permissions', $permission, $context, $object_id, $post_type );
}

/**
 * Check permissions of product terms on REST API.
 *
 * @since 2.0.0
 * @param string $taxonomy  Taxonomy.
 * @param string $context   Request context.
 * @param int    $object_id Post ID.
 * @return bool
 */
function geodir_rest_check_post_term_permissions( $taxonomy, $context = 'read', $object_id = 0 ) {
	$contexts = array(
		'read'   => 'manage_terms',
		'create' => 'edit_terms',
		'edit'   => 'edit_terms',
		'delete' => 'delete_terms',
		'batch'  => 'edit_terms',
	);

	$cap = $contexts[ $context ];
	$taxonomy_object = get_taxonomy( $taxonomy );
	$permission = current_user_can( $taxonomy_object->cap->$cap, $object_id );

	return apply_filters( 'geodir_rest_check_permissions', $permission, $context, $object_id, $taxonomy );
}

/**
 * Check manager permissions on REST API.
 *
 * @since 2.0.0
 * @param string $object  Object.
 * @param string $context Request context.
 * @return bool
 */
function geodir_rest_check_manager_permissions( $object, $context = 'read' ) {
	$objects = array(
		'reports'          => 'manage_options',
		'settings'         => 'manage_options',
		'system_status'    => 'manage_options',
	);

	$permission = current_user_can( $objects[ $object ] );
	$permission = true; // @todo remove this after testing done.

	return apply_filters( 'geodir_rest_check_permissions', $permission, $context, 0, $object );
}

function geodir_rest_get_countries( $params = array() ) {
    global $wpdb;
    
    $defaults = array(
        'fields'        => 'CountryId AS id, Country AS name, ISO2 as iso2, ISO3 as iso3, Country AS title',
        'search'        => '',
        'translated'    => true,
        'order'         => 'Country',
        'orderby'       => 'ASC',
		'limit'			=> -1 // All
    );
    
    $args = wp_parse_args( $params, $defaults );
    
    $where = '';
    if ( !empty( $args['search'] ) ) {
        $where .= "AND Country LIKE '" . wp_slash( $args['search'] ) . "%' ";
    }
    
    if ( !empty( $args['where'] ) ) {
        $where .= $args['where'];
    }
    
    $sql = "SELECT " . $args['fields'] . " FROM " . GEODIR_COUNTRIES_TABLE . " WHERE 1 " . $where . " ORDER BY " . $args['order'] . " " . $args['orderby'];
	if ( !empty( $args['limit'] ) && absint( $args['limit'] ) > 0 ) {
        $sql .= " LIMIT " . absint( $args['limit'] );
    }
	
    $items = $wpdb->get_results( $sql );
    
    if ( empty( $args['translated'] ) ) {
        return $items;
    }
    
    if ( !empty( $items ) ) {
        foreach ( $items as $key => $item ) {
            $items[ $key ]->title = __( $item->name, 'geodirectory' ); // translate
        }
    }
    
    return $items;
}

function geodir_rest_country_by_id( $value ) {
    $rows = geodir_rest_get_countries( array( 'where' => "AND CountryId = '" . (int)$value . "'", 'limit' => 1 ) );
    
    if ( !empty( $rows ) ) {
		return $rows[0];
    }
}

function geodir_rest_country_by_name( $value ) {
    $rows = geodir_rest_get_countries( array( 'where' => "AND Country LIKE '" . wp_slash( $value ) . "'", 'limit' => 1 ) );
    
    if ( !empty( $rows ) ) {
        return $rows[0];
    }
}

function geodir_rest_country_by_iso2( $value ) {
    $rows = geodir_rest_get_countries( array( 'where' => "AND ISO2 LIKE '" . wp_slash( $value ) . "'", 'limit' => 1 ) );

    if ( !empty( $rows ) ) {
        return $rows[0];
    }
    
    return NULL;
}