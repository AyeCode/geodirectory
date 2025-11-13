<?php
/**
 * Images Service
 *
 * Handles image operations, screenshots, thumbnails, and image metadata.
 *
 * @package GeoDirectory\Core
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core;

/**
 * Images class for image handling and operations.
 */
final class Images {

	/**
	 * Replace images tag src and class values for lazy loading.
	 *
	 * @since 3.0.0
	 *
	 * @param string $img_tag Image tag html.
	 * @param bool $lazy_load Optional. Default true.
	 * @return string Modified image tag.
	 */
	public function image_tag_ajaxify( string $img_tag, bool $lazy_load = true ): string {
		$strip = array( "src=", "srcset=" );
		$replace = array( "src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAAABCAQAAACC0sM2AAAADklEQVR42mP8X88wLAAAK5IBgMYCdqgAAAAASUVORK5CYII=' data-src=", "data-srcset=" );

		if ( $lazy_load ) {
			$strip[] = 'class="';
			$replace[] = 'class="geodir-lazy-load ';
		}
		$img_tag = str_replace( $strip, $replace, $img_tag );

		return $img_tag;
	}

	/**
	 * Get image src path by image object.
	 *
	 * @since 3.0.0
	 *
	 * @param object $image Image object.
	 * @param string $size Optional. Set image size. Default medium.
	 * @return string Image path.
	 */
	public function get_image_src( $image, string $size = 'medium' ): string {
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
	 * @since 3.0.0
	 *
	 * @param object $image Image data.
	 * @param string $size Optional. Get the image size. Default medium.
	 * @param string $align Optional. Get the image alignment value. Default null.
	 * @param string $classes Optional. Additional CSS classes.
	 * @return string Image tag HTML.
	 */
	public function get_image_tag( $image, string $size = 'medium', string $align = '', string $classes = '' ): string {
		$meta = isset( $image->metadata ) ? maybe_unserialize( $image->metadata ) : '';
		$img_src = $this->get_image_src( $image, $size );
		$width = '';
		$height = '';

		if ( $size ) {
			$width = isset( $meta['sizes'][ $size ]['width'] ) ? $meta['sizes'][ $size ]['width'] : '';
			$height = isset( $meta['sizes'][ $size ]['height'] ) ? $meta['sizes'][ $size ]['height'] : '';
		}
		if ( ! $width ) {
			$width = isset( $meta['width'] ) ? $meta['width'] : '';
		}
		if ( ! $height ) {
			$height = isset( $meta['height'] ) ? $meta['height'] : '';
		}
		$hwstring = image_hwstring( $width, $height );

		$id = isset( $image->ID ) ? esc_attr( $image->ID ) : 0;
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

		$class = 'align' . esc_attr( $align ) .' size-' . esc_attr( $size ) . ' geodir-image-' . $id .' ' . $classes;
		if ( geodir_design_style() ) {
			$class .= ' w-100 p-0 m-0 mw-100 border-0';
		}

		$class = apply_filters( 'geodir_get_image_tag_class', $class, $id, $align, $size );

		$html = '<img src="' . esc_attr( $img_src ) . '" alt="' . $alt . '" ' . $title . $caption . $hwstring . 'class="' . $class . '" />';

		return apply_filters( 'geodir_get_image_tag', $html, $id, $alt, $title, $align, $size );
	}

	/**
	 * Retrieve mshots screenshot url for requested url.
	 *
	 * @since 3.0.0
	 *
	 * @param string $url The url to generate screenshot.
	 * @param array $params Screenshot parameters.
	 * @return string Screenshot url.
	 */
	public function get_screenshot( string $url, array $params = array() ): string {
		$screenshot_base_url = 'https://s.wordpress.com/mshots/v1/';

		$params = wp_parse_args( $params, array(
			'image' => false,
			'w' => '',
			'h' => '',
			'vpw' => '',
			'vph' => '',
			'requeue' => false,
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
			$dimension = $this->get_image_dimension( $url );

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

	/**
	 * Get field screenshot.
	 *
	 * @since 3.0.0
	 *
	 * @param string $field Field name.
	 * @param array $sizes Screenshot sizes.
	 * @param array|object $the_post Post object.
	 * @return string Screenshot URL.
	 */
	public function get_field_screenshot( string $field, array $sizes = array(), $the_post = array() ): string {
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
			if ( $video = $this->get_video_screenshot( $the_post->{$field} ) ) {
				$url = $video;
			} else {
				$url = $this->get_screenshot( $the_post->{$field}, $sizes );
			}
		}

		return $url;
	}

	/**
	 * Get a screenshot URL from a video URL.
	 *
	 * @since 3.0.0
	 *
	 * @param string $field_raw Video URL.
	 * @return string Screenshot URL.
	 */
	public function get_video_screenshot( string $field_raw ): string {
		$screenshot = '';
		$screenshot_base_url = 'https://img.youtube.com/vi/%s/hqdefault.jpg';

		// check if its a video URL
		if ( ! empty( $field_raw ) && preg_match( "/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/", $field_raw, $matches ) ) {
			if ( ! empty( $matches[1] ) ) {
				$screenshot = esc_url( sprintf( $screenshot_base_url, esc_attr( $matches[1] ) ) );
			}
		}

		return apply_filters( 'geodir_get_video_screenshot', $screenshot, $field_raw );
	}

	/**
	 * Check font awesome icon or not.
	 *
	 * @since 3.0.0
	 *
	 * @param string $icon Font awesome icon.
	 * @return bool True if font awesome icon.
	 */
	public function is_fa_icon( string $icon ): bool {
		$return = false;
		if ( $icon != '' ) {
			$fa_icon = trim( $icon );
			if ( strpos( $fa_icon, 'fa fa-' ) === 0 || strpos( $fa_icon, 'fas fa-' ) === 0 || strpos( $fa_icon, 'far fa-' ) === 0 || strpos( $fa_icon, 'fab fa-' ) === 0 || strpos( $fa_icon, 'fa-' ) === 0 ) {
				$return = true;
			}
		}
		return apply_filters( 'geodir_is_fa_icon', $return, $icon );
	}

	/**
	 * Check icon url.
	 *
	 * @since 3.0.0
	 *
	 * @param string $icon Icon url.
	 * @return bool True if icon url.
	 */
	public function is_icon_url( string $icon ): bool {
		$return = false;
		if ( $icon != '' ) {
			$icon = trim( $icon );
			if ( strpos( $icon, 'http://' ) === 0 || strpos( $icon, 'https://' ) === 0 ) {
				$return = true;
			}
		}
		return apply_filters( 'geodir_is_icon_url', $return, $icon );
	}

	/**
	 * Retrieve image dimension.
	 *
	 * @since 3.0.0
	 *
	 * @param string $image_url The image url.
	 * @param array $default Default dimension. Default empty.
	 * @return array Image dimension array.
	 */
	public function get_image_dimension( string $image_url, array $default = array() ): array {
		// @todo: Consider using object caching instead of global variable
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
			$uploads = wp_upload_dir();

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
			$size = $this->getimagesize( trim( $image_url ) );

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
	 * @since 3.0.0
	 *
	 * @param string $image_path The image path.
	 * @return array Image size array(width, height).
	 */
	public function getimagesize( string $image_path ): array {
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
	 * @since 3.0.0
	 *
	 * @param array $sources Image srcset sources.
	 * @param array $size_array Image size array.
	 * @param string $image_src Image source URL.
	 * @param array $image_meta Image metadata.
	 * @param int $attachment_id Attachment ID.
	 * @return array Modified sources.
	 */
	public function set_external_srcset( array $sources, array $size_array, string $image_src, array $image_meta, int $attachment_id ): array {
		if ( $attachment_id === 0 && ! empty( $sources ) && isset( $image_meta['file'] ) && substr( $image_meta['file'], 0, 4 ) === "http" ) {
			$img_url_basename = wp_basename( $image_src );
			foreach ( $sources as $key => $source ) {
				$sources[ $key ]['url'] = str_replace( $img_url_basename, wp_basename( $source['url'] ), $image_src );
			}
		}

		return $sources;
	}
}
