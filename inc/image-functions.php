<?php
/**
 * Post image functions.
 *
 * These functions act as thin wrappers around the Images class.
 * For direct access, use: geodirectory()->images
 *
 * @since 2.0.0
 * @package GeoDirectory
 */

/**
 * Function for replace images tag src and class values.
 *
 * Check if $lazy_load is true then replace image tag class value
 * else replace only image src value.
 *
 * @since 2.0.0
 *
 * @param string $img_tag Image tag html.
 * @param bool $lazy_load Optional. Default true.
 * @return string $img_tag.
 */
function geodir_image_tag_ajaxify( $img_tag, $lazy_load = true ) {
	return geodirectory()->images->image_tag_ajaxify( $img_tag, $lazy_load );
}

/**
 * Function for get image src path by image object.
 *
 * @since 2.0.0
 *
 * @param object $image image object.
 * @param string $size Optional. Set image size. Default medium.
 * @return string $img_src Image path.
 */
function geodir_get_image_src( $image, $size = 'medium' ) {
	return geodirectory()->images->get_image_src( $image, $size );
}

/**
 * Get the image tag.
 *
 * @since 2.0.0
 *
 * @param object $image Image data.
 * @param string $size Optional. Get the image size. Default medium.
 * @param string $align Optional. get the image alignment value. Default null.
 * @return string $html Image tag.
 */
function geodir_get_image_tag( $image, $size = 'medium', $align = '', $classes = '' ) {
	return geodirectory()->images->get_image_tag( $image, $size, $align, $classes );
}

/**
 * Retrieve mshots screenshot url for requested url.
 *
 * @since 2.0.0.100
 *
 * @param string $url The url to generate screenshot.
 * @param array  $params Screenshot parameters.
 * @return string Screenshot url.
 */
function geodir_get_screenshot( $url, $params = array() ) {
	return geodirectory()->images->get_screenshot( $url, $params );
}

function geodir_get_field_screenshot( $field, $sizes = array(), $the_post = array() ) {
	return geodirectory()->images->get_field_screenshot( $field, $sizes, $the_post );
}

/**
 * Get a screenshot URL from a video URL.
 *
 * @param $field_raw
 *
 * @return mixed|void
 */
function geodir_get_video_screenshot( $field_raw ) {
	return geodirectory()->images->get_video_screenshot( $field_raw );
}

/**
 * Gets the post images.
 *
 * @since 1.0.0
 * @package GeoDirectory
 *
 * @global object $gd_post The GeoDirectory post object.
 *
 * @param int $post_id The post ID.
 * @param int|string $limit Optional. Number of images.
 * @param bool $logo Optional. Show logo image? Default: false.
 * @param int $revision_id The revision post ID.
 * @param array $types Images types to retrieve.
 * @param array $fallback_types Fallback images types to retrieve for empty results.
 * @param int|string $status Optional. Retrieve images with status passed.
 * @return array|bool Returns images as an array. Each item is an object.
 */
function geodir_get_images( $post_id = 0, $limit = '', $logo = false, $revision_id = '', $types = array() , $fallback_types = array( 'logo', 'cat_default', 'cpt_default', 'listing_default', 'website_screenshot' ), $status = '' ) {
	return geodirectory()->images->get_images( $post_id, $limit, $logo, $revision_id, $types, $fallback_types, $status );
}

/**
 * Check font awesome icon or not.
 *
 * @param string $icon Font awesome icon.
 * @return bool True if font awesome icon.
 */
function geodir_is_fa_icon( $icon ) {
	return geodirectory()->images->is_fa_icon( $icon );
}

/**
 * Check icon url.
 *
 * @param string $icon Icon url.
 * @return bool True if icon url.
 */
function geodir_is_icon_url( $icon ) {
	return geodirectory()->images->is_icon_url( $icon );
}

/**
 * Validate post image types.
 *
 * $since 2.0.0.65
 *
 * @param array $types Array of image types. Ex: logo, comment_images, post_images.
 * @param int $post_id Post ID.
 * @param int $revision_id Post revision ID. Default 0.
 * @return array Array of valid image types.
 */
function geodir_post_has_image_types( $types = array(), $post_id = 0, $revision_id = 0 ) {
	if ( ! empty( $types ) ) {
		if ( is_scalar( $types ) ) {
			$image_types = array_map( 'trim', explode( ",", $types ) );
		} else {
			$image_types = $types;
		}
		$image_types = array_filter( array_unique( $image_types ) );

		if ( ! empty( $image_types ) ) {
			$post_type = get_post_type( $post_id );
			$package_id = geodir_get_post_package_id( $post_id, $post_type );

			$valid_types = array();
			foreach ( $image_types as $type ) {
				if ( in_array( $type, array( 'post_images', 'comment_images' ) ) ) {
					$valid_types[] = $type;
				} elseif(stripos(strrev($type), "tohsneercs_") === 0){
					$valid_types[] = $type;
				}elseif ( geodir_check_field_visibility( $package_id, $type, $post_type ) ) {
					$valid_types[] = $type;
				}
			}
			$image_types = $valid_types;
		}
	} else {
		$image_types = array();
	}

	return apply_filters( 'geodir_post_has_image_types', $image_types, $types, $post_id, $revision_id  );
}

/**
 * Retrieve image dimension.
 *
 * @since 2.0.0.100
 *
 * @global array $geodir_image_dimension Array of cached image dimension.
 *
 * @param string $image_url The image url.
 * @param array  $default Default dimension. Default empty.
 * @return array Image dimension array.
 */
function geodir_get_image_dimension( $image_url, $default = array() ) {
	return geodirectory()->images->get_image_dimension( $image_url, $default );
}

/**
 * Get image size.
 *
 * @since 2.3.99
 *
 * @param string $image_path The image path.
 * @return array Image size array(width, height).
 */
function geodir_getimagesize( $image_path ) {
	return geodirectory()->images->getimagesize( $image_path );
}

/**
 * Check if a image meta file source is external and if so change the srcset to match.
 *
 * @param $sources
 * @param $size_array
 * @param $image_src
 * @param $image_meta
 * @param $attachment_id
 *
 * @return mixed
 */
function geodir_set_external_srcset( $sources, $size_array, $image_src, $image_meta, $attachment_id ) {
	return geodirectory()->images->set_external_srcset( $sources, $size_array, $image_src, $image_meta, $attachment_id );
}
add_filter('wp_calculate_image_srcset','geodir_set_external_srcset',10,5);
