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
	global $gd_post;

	$post_images = array();

	$the_post = ! empty( $gd_post ) && $gd_post->ID == $post_id ? $gd_post : array();

	if ( empty( $the_post->post_id ) && ! empty( $post_id ) ) {
		$the_post = geodir_get_post_info( $post_id );
	}

	if ( ! empty( $types ) ) {
		$original_types = $types;

		// Check for URL screenshots
		$has_screenshots = false;
		if ( ! empty( $types ) ) {
			foreach ( $types as $key => $val ) {
				if ( stripos( strrev( $val ), "tohsneercs_" ) === 0 ) {
					$field = str_replace( "_screenshot", "", $val );

					if ( isset( $the_post->{$field} ) && esc_url( $the_post->{$field} ) ) {
						$has_screenshots = true;
					} else {
						unset( $types[$key] ); // Unset if an invalid screenshot type
					}
				}
			}
		}

		$types = geodir_post_has_image_types( $types, $post_id, $revision_id );

		if ( ! empty( $types ) ) {
			if ( $has_screenshots ) {
				foreach ( $types as $type ) {
					if ( $limit && count( $post_images ) >= $limit ) {
						break;
					}

					$new_images = array();
					if ( stripos( strrev( $type ), "tohsneercs_") === 0 ) {
						$field = str_replace( "_screenshot", "", $type );

						if ( isset( $the_post->{$field} ) && esc_url( $the_post->{$field} ) ) {
							$file_fields = GeoDir_Media::get_file_fields( $the_post->post_type );

							if ( ! empty( $file_fields ) && isset( $file_fields[ $field ] ) ) {
								$attachments = GeoDir_Media::get_attachments_by_type( $post_id, $field, $limit, $revision_id, '', $status );
							} else {
								$attachments = array();
							}

							if ( ! empty( $attachments ) ) {
								foreach ( $attachments as $ia => $attachment ) {
									$media_url = geodir_get_image_src( $attachment );
									$media_image = geodir_get_screenshot( $media_url, array( 'w' => 825, 'h' => 430, 'image' => 1 ) );

									if ( $media_image ) {
										$image = new \stdClass();
										$image->ID = 0;
										$image->post_id = $post_id;
										$image->user_id = 0;
										$image->title = ! empty( $attachment->title ) ? $attachment->title : wp_sprintf( __( '%s screenshot', 'geodirectory' ), esc_attr( $field ) );
										$image->caption = ! empty( $attachment->caption ) ? $attachment->caption : '';
										$image->file = ltrim( $media_image, '/\\' );
										$image->mime_type = $attachment->mime_type;
										$image->menu_order = 0;
										$image->featured = 0;
										$image->is_approved = 1;
										$image->metadata = array( 'media_url' => $media_url, 'mime_type' => $attachment->mime_type );
										$image->type = esc_attr( $field ) . '_screenshot';

										$new_images[] = $image;
									}
								}
							} else {
								$image_src = geodir_get_field_screenshot( $field, array(), $the_post );

								if ( $image_src ) {
									$image = new \stdClass();
									$image->ID = 0;
									$image->post_id = $post_id;
									$image->user_id = 0;
									$image->title = wp_sprintf( __( '%s screenshot', 'geodirectory' ), esc_attr( $field ) );
									$image->caption = '';
									$image->file = ltrim( $image_src, '/\\' );
									$image->mime_type = '';
									$image->menu_order = 0;
									$image->featured= 0;
									$image->is_approved = 1;
									$image->metadata = '';
									$image->type = esc_attr( $field ) . '_screenshot';

									$new_images[] = $image;
								}
							}
						}
					} else {
						$new_images = GeoDir_Media::get_attachments_by_type( $post_id, $type, $limit, $revision_id, '', $status );
					}

					if ( ! empty( $new_images ) ) {
						foreach ( $new_images as $new_image ) {
							if ( $limit && count( $post_images ) >= $limit ) {
								break;
							}
							array_push( $post_images, $new_image );
						}
					}
				}
			} else {
				$post_images = GeoDir_Media::get_attachments_by_type( $post_id, $types, $limit, $revision_id, '', $status );
			}
		}
	} else {
		$post_images = GeoDir_Media::get_post_images( $post_id, $limit, $revision_id, $status );
	}

	if ( ! empty( $post_images ) ) {
		// wp_image_add_srcset_and_sizes( $image, $image_meta, $attachment_id );
		if ( $logo && geodir_post_has_image_types( 'logo', $post_id, $revision_id ) ) {
			$logo_image = GeoDir_Media::get_attachments_by_type( $post_id, 'logo', 1, $revision_id, '', $status );
			if ( $logo_image ) {
				$post_images = $logo_image + $post_images;
			}
		}
	} else if ( ! empty( $fallback_types ) ) {
		$fallback_image = new \stdClass();
		$default_img_id = 0;

		// Fallback images
		foreach( $fallback_types as $fallback_type ) {
			// Logo
			if ( $fallback_type == 'logo' && isset( $the_post->logo ) && $the_post->logo && geodir_post_has_image_types( 'logo', $post_id ) ) {
				$logo_image = GeoDir_Media::get_attachments_by_type( $post_id, 'logo', 1, '', '', $status );
				if ( $logo_image ) {
					$post_images = $logo_image;
					break;
				}
			}

			if ( $fallback_type == 'cat_default' ) {
				$term_img = 0;
				// Cat image
				if ( geodir_is_page('archive' ) ) {
					if ( $term_id = get_queried_object_id() ) {
						$term_img = get_term_meta( $term_id, 'ct_cat_default_img', true );
					}
				}

				if ( empty( $term_img ) ) {
					$default_term_id = geodir_get_post_meta( $post_id, 'default_category' );

					if ( $default_term_id ) {
						$term_img = get_term_meta( $default_term_id, 'ct_cat_default_img', true );
					}
				}

				if ( ! empty( $term_img ) ) {
					$default_img_id = $term_img['id'];

					if ( !empty( $default_img_id ) && ! empty( $term_img['src'] ) ) {
						$imag_title = __( 'Placeholder image', 'geodirectory' );
						$imag_caption = '';
						$image_mime_type = '';
						$image_metadata = '';

						if ( absint( $default_img_id ) > 0 && ( $default_image_post = get_post( absint( $default_img_id ) ) ) ) {
							// Image title
							if ( ! empty( $default_image_post->post_title ) ) {
								$imag_title = strip_tags( $default_image_post->post_title );
							}

							// Image caption
							if ( ! empty( $default_image_post->post_excerpt ) ) {
								$imag_caption = strip_tags( $default_image_post->post_excerpt );
							}

							// Image mime type
							if ( ! empty( $default_image_post->post_mime_type ) ) {
								$image_mime_type = strip_tags( $default_image_post->post_mime_type );
							}

							// Image metadata
							$image_metadata = wp_get_attachment_metadata( absint( $default_img_id ) );
						}

						$image_src = geodir_file_relative_url( $term_img['src'], false );
						$post_images = array();
						$image = new \stdClass();
						$image->ID = 0;
						$image->post_id = $post_id;
						$image->user_id = 0;
						$image->title = $imag_title;
						$image->caption = $imag_caption;
						$image->file = '/' . ltrim( $image_src, '/\\' );
						$image->mime_type = $image_mime_type;
						$image->menu_order = 0;
						$image->featured= 0;
						$image->is_approved = 1;
						$image->metadata = $image_metadata;
						$image->type = 'post_images';
						$post_images[] = $image;
						break;
					} else if ( ! empty( $default_img_id ) && absint( $default_img_id ) > 0 ) {
						$default_img_id = absint( $default_img_id );
						break;
					}
				}
			}

			if ( $fallback_type == 'cpt_default' && ! empty( $the_post ) ) {
				// Check for CPT default image
				$cpt = $the_post->post_type;
				if ( $cpt ) {
					$cpts = geodir_get_posttypes('array');
					if ( ! empty( $cpts[$cpt]['default_image'] ) ) {
						$default_img_id = absint( $cpts[$cpt]['default_image'] );
						break;
					}
				}
			}

			if ( $fallback_type == 'listing_default' ) {
				$listing_default_image_id = geodir_get_option( 'listing_default_image' );
				if ( $listing_default_image_id ) {
					$default_img_id = absint( $listing_default_image_id );
					break;
				}
			}

			if ( is_int( $fallback_type ) ) {
				$default_img_id = absint( $fallback_type );
				break;
			}

			if ( stripos( strrev( $fallback_type ), "tohsneercs_" ) === 0 ) { // screenshots
				$field = str_replace( "_screenshot", "", $fallback_type );

				if ( isset( $the_post->{$field} ) && esc_url( $the_post->{$field} ) ) {
					$image_src = geodir_get_field_screenshot( $field, array(), $the_post );

					if ( $image_src ) {
						$image = new \stdClass();
						$image->ID = 0;
						$image->post_id = $post_id;
						$image->user_id = 0;
						$image->title = wp_sprintf( __( '%s screenshot', 'geodirectory' ), esc_attr( $field ) );
						$image->caption = '';
						$image->file = ltrim( $image_src, '/\\' );
						$image->mime_type = '';
						$image->menu_order = 0;
						$image->featured= 0;
						$image->is_approved = 1;
						$image->metadata = '';
						$image->type = esc_attr( $field ).'_screenshot';
						$post_images[] = $image;
						break;
					}
				}
			}
		}

		// Default image
		if ( empty( $post_images ) && $default_img_id ) {
			$default_image_post = get_post( $default_img_id );

			if ( $default_image_post ) {
				$wp_upload_dir = wp_upload_dir();

				$post_images = array();
				$image = new \stdClass();
				$image->ID = 0;
				$image->post_id = $default_image_post->ID;
				$image->user_id = 0;
				$image->title = !empty($default_image_post->post_title) ? stripslashes_deep( $default_image_post->post_title ) : __( 'Placeholder image', 'geodirectory' );
				$image->caption = !empty($default_image_post->post_excerpt) ? stripslashes_deep( $default_image_post->post_excerpt ) : '';
				$image->file = str_replace( $wp_upload_dir['basedir'], '', get_attached_file( $default_img_id ) );
				$image->mime_type = $default_image_post->post_mime_type;
				$image->menu_order = 0;
				$image->featured = 0;
				$image->is_approved = 1;
				$image->metadata= wp_get_attachment_metadata( $default_img_id );
				$image->type = 'post_images';
				$post_images[] = $image;
			}
		}
	}

	return $post_images;
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
