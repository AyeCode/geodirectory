<?php
/**
 * Post image functions.
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
function geodir_image_tag_ajaxify($img_tag,$lazy_load = true){

    
    $strip = array("src=","srcset=");
    $replace = array("src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAAABCAQAAACC0sM2AAAADklEQVR42mP8X88wLAAAK5IBgMYCdqgAAAAASUVORK5CYII=' data-src=","data-srcset=");
    if($lazy_load){
        $strip[] = 'class="';
        $replace[] = 'class="geodir-lazy-load ';
    }
    $img_tag = str_replace($strip,$replace,$img_tag);

    return $img_tag;
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
	$img_src = '';

	$meta = isset( $image->metadata ) ? maybe_unserialize( $image->metadata ) : '';
	$upload_dir = wp_upload_dir();

	// Base url
	$base_url = $upload_dir['baseurl'];

	if ( isset( $meta['sizes'] ) && $size ) {
		if ( is_array( $size ) ) {
			$size = 'medium';
		}

		$img_url_basename = wp_basename( $base_url . $image->file );

		if ( $img_url_basename && isset( $meta['sizes'][ $size ] ) && isset( $meta['sizes'][ $size ]['file'] ) && $meta['sizes'][ $size ]['file'] ) {
			if ( substr( $image->file, 0, 4 ) === "http" ) {
				$img_url = esc_url_raw( $image->file );
			} else {
				$img_url = $base_url . $image->file;
			}

			$img_src = str_replace( $img_url_basename, wp_basename( $meta['sizes'][ $size ]['file'] ), $img_url );
		}
	}

	// No sizes just return full size.
	if ( ! $img_src ) {
		if ( isset( $image->file ) ) {
			if ( substr( $image->file, 0, 4 ) === "http" ) {
				$img_src = esc_url_raw( $image->file );
			} else {
				$img_src = $base_url . $image->file;
			}
		}
	}

	return $img_src;
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
function geodir_get_image_tag( $image, $size = 'medium',$align = '', $classes = '' ) {
	$meta = isset($image->metadata) ? maybe_unserialize($image->metadata) : '';
	$img_src = geodir_get_image_src($image, $size);
	$width = '';
	$height = '';
	if($size){
		$width = isset($meta['sizes'][$size]['width']) ? $meta['sizes'][$size]['width'] : '';
		$height = isset($meta['sizes'][$size]['height']) ? $meta['sizes'][$size]['height'] : '';
	}
	if(!$width){$width = isset($meta['width']) ? $meta['width'] : '';}
	if(!$height){$height = isset($meta['height']) ? $meta['height'] : '';}
	$hwstring = image_hwstring($width, $height);

	$id = isset($image->ID) ? esc_attr( $image->ID ) : 0;
	$_title = isset( $image->title ) && $image->title ? wp_strip_all_tags( stripslashes_deep( $image->title ) ) : '';
	$title = $_title ? 'title="' . esc_attr( $_title ) . '" ' : '';
	$_caption = ! empty( $image->caption ) ? wp_strip_all_tags( stripslashes_deep( $image->caption ) ) : '';
	$caption = $_caption ? ' data-caption="' . esc_attr( $_caption ) . '" ' : '';

	if ( $_title ) {
		$alt = $_title;
	} else if ( $img_src ) {
		$alt = preg_replace( '/\.[^.]+$/', '', basename( $img_src ) );
		$alt = str_replace( array( '-', '_' ), ' ', $alt );
	} else {
		$alt = '';
	}

	if ( $alt ) {
		$alt = esc_attr( trim( $alt ) );
	}

	$class = 'align' . esc_attr($align) .' size-' . esc_attr($size) . ' geodir-image-' . $id .' ' . $classes;
	if ( geodir_design_style() ) {
		$class .= ' w-100 p-0 m-0 mw-100 border-0';
	}

	/**
	 * Filters the value of the attachment's image tag class attribute.
	 *
	 * @since 2.0.0
	 *
	 * @param string       $class CSS class name or space-separated list of classes.
	 * @param int          $id    Attachment ID.
	 * @param string       $align Part of the class name for aligning the image.
	 * @param string|array $size  Size of image. Image size or array of width and height values (in that order).
	 *                            Default 'medium'.
	 */
	$class = apply_filters( 'geodir_get_image_tag_class', $class, $id, $align, $size );

	$html = '<img src="' . esc_attr($img_src) . '" alt="' . $alt . '" ' . $title . $caption . $hwstring . 'class="' . $class . '" />';

	/**
	 * Filters the HTML content for the image tag.
	 *
	 * @since 2.0.0
	 *
	 * @param string       $html  HTML content for the image.
	 * @param int          $id    Attachment ID.
	 * @param string       $alt   Alternate text.
	 * @param string       $title Attachment title.
	 * @param string       $align Part of the class name for aligning the image.
	 * @param string|array $size  Size of image. Image size or array of width and height values (in that order).
	 *                            Default 'medium'.
	 */
	return apply_filters( 'geodir_get_image_tag', $html, $id, $alt, $title, $align, $size );
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
	$screenshot_base_url = 'https://s.wordpress.com/mshots/v1/';

	$params = wp_parse_args( $params, array(
		'image' => false,     // Check image & assign auto vpw & vph when empty.
		'w' => '',            // Screenshot width
		'h' => '',            // Screenshot height
		'vpw' => '',          // Viewport width
		'vph' => '',          // Viewport height
		'requeue' => false,   // 'true' to remove cached & regenerate screenshot.
	) );

	$params = apply_filters( 'geodir_get_screenshot_params', $params, $url, $params );

	$args = array();
	if ( ! empty( $params['w'] ) ) {
		$args['w'] = $params['w'];
	}
	if ( ! empty( $params['h'] ) ) {
		$args['h'] = $params['h'];
	}
	if ( ! empty( $params['image'] ) && ( empty( $params['vpw'] ) || empty( $params['vph'] ) ) ) {
		// Find image dimension.
		$dimension = geodir_get_image_dimension( $url );

		if ( ! empty( $dimension ) && ! empty( $dimension['width'] ) && ! empty( $dimension['height'] ) ) {
			$args['vpw'] = $dimension['width'];
			$args['vph'] = $dimension['height'];
		}
	}
	if ( ! empty( $params['vpw'] ) && ! empty( $params['vph'] ) ) {
		$args['vpw'] = $params['vpw'];
		$args['vph'] = $params['vph'];
	}

	if ( ! empty( $params['requeue'] ) ) {
		$args['requeue'] = 'true';
	}

	$screenshot = $screenshot_base_url . urlencode( $url );
	if ( ! empty( $args ) ) {
		$screenshot = add_query_arg( $args, $screenshot );
	}

	return apply_filters( 'geodir_get_screenshot', $screenshot, $url, $params, $args );
}

function geodir_get_field_screenshot( $field, $sizes = array(), $the_post = array() ) {
	global $gd_post;

	if ( empty( $sizes ) || ! is_array( $sizes ) ) {
		$sizes = array( 'w' => 825, 'h' => 430, 'image' => 1 );
	}

	if ( empty( $the_post ) ) {
		$the_post = $gd_post;
	}

	$url = '';

	if ( isset( $the_post->{$field} ) && esc_url( $the_post->{$field} ) ) {
		// check if maybe a video URL
		if ($video = geodir_get_video_screenshot( $the_post->{$field} ) ) {
			$url = $video;
		}else{
			$url = geodir_get_screenshot( $the_post->{$field}, $sizes );
		}
	}

	return $url;
}

/**
 * Get a screenshot URL from a video URL.
 * 
 * @param $field_raw
 *
 * @return mixed|void
 */
function geodir_get_video_screenshot( $field_raw ) {
	$screenshot = '';
	$screenshot_base_url = 'https://img.youtube.com/vi/%s/hqdefault.jpg';

	// check if its a video URL
	if (!empty($field_raw) && preg_match("/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/", $field_raw, $matches) ) {
		if(!empty($matches[1])){
			$screenshot = esc_url( sprintf($screenshot_base_url, esc_attr($matches[1])) );
		}
	}

	return apply_filters( 'geodir_get_video_screenshot', $screenshot, $field_raw);
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
										$image = new stdClass();
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
									$image = new stdClass();
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
		$fallback_image = new stdClass();
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
						$image = new stdClass();
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
						$image = new stdClass();
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
				$image = new stdClass();
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
	$return = false;
	if ( $icon != '' ) {
		$fa_icon = trim( $icon );
		if ( strpos( $fa_icon, 'fa fa-' ) === 0 || strpos( $fa_icon, 'fas fa-' ) === 0 || strpos( $fa_icon, 'far fa-' ) === 0 || strpos( $fa_icon, 'fab fa-' ) === 0 || strpos( $fa_icon, 'fa-' ) === 0 ) {
			$return = true;
		}
	}
	return apply_filters( 'geodir_is_fa_icon', $return, $icon  );
}

/**
 * Check icon url.
 *
 * @param string $icon Icon url.
 * @return bool True if icon url.
 */
function geodir_is_icon_url( $icon ) {
	$return = false;
	if ( $icon != '' ) {
		$icon = trim( $icon );
		if ( strpos( $icon, 'http://' ) === 0 || strpos( $icon, 'https://' ) === 0 ) {
			$return = true;
		}
	}
	return apply_filters( 'geodir_is_icon_url', $return, $icon  );
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
	global $geodir_image_dimension;

	if ( empty( $geodir_image_dimension ) ) {
		$geodir_image_dimension = array();
	}

	if ( ! empty( $geodir_image_dimension[ $image_url ] ) ) {
		return $geodir_image_dimension[ $image_url ];
	}

	if ( empty( $image_url ) ) {
		$geodir_image_dimension[ $image_url ] = $default;

		return $default;
	}

	$_image_url = $image_url;

	if ( ! path_is_absolute( $image_url ) ) {
		$uploads = wp_upload_dir(); // Array of key => value pairs

		$image_url = str_replace( $uploads['baseurl'], $uploads['basedir'], $image_url );
	}

	if ( ! path_is_absolute( $image_url ) && strpos( $image_url, WP_CONTENT_URL ) !== false ) {
		$image_url = str_replace( WP_CONTENT_URL, WP_CONTENT_DIR, $image_url );
	}

	if ( strpos( $image_url, 'http://' ) !== false || strpos( $image_url, 'https://' ) !== false ) {
		$geodir_image_dimension[ $_image_url ] = $default;

		return $default;
	}

	$dimension = array();
	if ( is_file( $image_url ) && file_exists( $image_url ) ) {
		$size = geodir_getimagesize( trim( $image_url ) );

		if ( ! empty( $size ) && ! empty( $size[0] ) && ! empty( $size[1] ) ) {
			$dimension = array( 'width' => $size[0], 'height' => $size[1] );
		}
	}

	$dimension = ! empty( $dimension ) ? $dimension : $default;

	$geodir_image_dimension[ $_image_url ] = $dimension;

	return $dimension;
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
	$size = function_exists( 'wp_getimagesize' ) ? wp_getimagesize( $image_path ) : @getimagesize( $image_path );

	if ( empty( $size ) ) {
		$size = array();

		// Check for .svg icon.
		if ( preg_match( '/\.svg$/i', $image_path ) && ( $xml = simplexml_load_file( $image_path ) ) !== false ) {
			$attributes = $xml->attributes();

			if ( ! empty( $attributes ) && ( isset( $attributes->viewBox ) || isset( $attributes->viewbox ) ) ) {
				// Mapmarker.io icon contains viewbox. 
				$viewbox = $attributes->viewBox ? explode( ' ', $attributes->viewBox ) : explode( ' ', $attributes->viewbox );

				$size[0] = isset( $attributes->width ) && preg_match( '/\d+/', $attributes->width, $value ) ? (int) $value[0] : ( count( $viewbox ) == 4 ? (int) trim( $viewbox[2] ) : 0 );
				$size[1] = isset( $attributes->height ) && preg_match( '/\d+/', $attributes->height, $value ) ? (int) $value[0] : ( count( $viewbox ) == 4 ? (int) trim( $viewbox[3] ) : 0 );
			}
		}
	}

	return $size;
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

	if($attachment_id === 0 && !empty($sources) && isset($image_meta['file']) && substr( $image_meta['file'], 0, 4 ) === "http"){
		$img_url_basename = wp_basename( $image_src );
		foreach ( $sources as $key => $source ) {
			$sources[$key]['url'] = str_replace($img_url_basename, wp_basename($source['url']), $image_src);
		}
	}

	return $sources;
}
add_filter('wp_calculate_image_srcset','geodir_set_external_srcset',10,5);
