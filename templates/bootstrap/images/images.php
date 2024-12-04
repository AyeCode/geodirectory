<?php
/**
 * Display post images single image/slider/gallery
 *
 * This template can be overridden by copying it to yourtheme/geodirectory/bootstrap/images/images.php.
 *
 * HOWEVER, on occasion GeoDirectory will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see        https://docs.wpgeodirectory.com/article/346-customizing-templates/
 * @package    GeoDirectory/Templates
 * @version    2.8.91
 */

defined( 'ABSPATH' ) || exit;

/**
 * Variables.
 *
 * @var string $main_wrapper_class
 * @var string $type
 * @var string $slider_id
 * @var string $second_wrapper_class
 * @var string $controlnav
 * @var string $animation
 * @var bool $slideshow
 * @var string $limit
 * @var string $limit_show
 * @var string $ul_class
 * @var object $post_images
 * @var string $link_to
 * @var string $link_screenshot_to
 * @var string $ajax_load
 * @var string $link
 * @var string $show_title
 * @var string $link_tag_open
 * @var string $link_tag_close
 * @var string $show_caption
 * @var string $image_size
 * @var string $cover
 * @var string $aspect
 * @var string $responsive_image_class
 */

global $ayecode_ui_settings, $gd_post, $aui_bs5, $geodir_carousel_open;

$the_post = $gd_post;

if ( empty( $the_post ) && ! empty( $current_post ) ) {
	$the_post = $current_post;
}
?>
<div class="<?php echo esc_attr( $main_wrapper_class ); ?>">
	<?php
	// Slider stuff
	if ( $type == 'slider' ) {
		if ( $limit_show ) {
			$second_wrapper_class .= ' carousel-multiple-items';
		}

		if ( $animation == 'fade' ) {
			$second_wrapper_class .= ' carousel-fade';
		}
	?>
	<div id="<?php echo esc_attr( $slider_id ); ?>" class="carousel slide <?php echo esc_attr( trim( $second_wrapper_class ) ); ?>"<?php echo ( ( $controlnav == 1 ? " data-controlnav='1'" : "" ) . ( $animation == 'fade' ? " data-animation='fade'" : "" ) . ( $slideshow ? " data-ride='carousel'" : "" ) . ( $limit_show ? " data-limit_show='" . absint( $limit_show ) . "'" : "" ) ); ?>>
	<?php
	}

	$image_total = count( (array) $post_images );

	if ( $limit_show > 0 && $limit_show > $image_total ) {
		$limit_show = $image_total;
	}

	$image_count = 0;
	$max_width_percent = $limit_show ? 100 / absint( $limit_show ) : '';
	$inner_wrapper_class = '';

	if ( $type == 'slider' || $type == 'image' ) {
		$inner_wrapper_class = ( ! $geodir_carousel_open ? 'carousel-inner ' : '' );

		if ( $max_width_percent > 0 && ! ( ! empty( $ayecode_ui_settings ) && is_callable( array( $ayecode_ui_settings, 'is_block_editor' ) ) && ( $ayecode_ui_settings->is_block_editor() || $ayecode_ui_settings->is_block_content_call() ) ) ) {
			$max_width_percent = '';
		}
	} else if ( $type == 'gallery' || $type == 'masonry' ) {
		$inner_wrapper_class = 'row row-cols-1 row-cols-md-3 g-2';
	}

	// Image cover class
	if ( $type == 'masonry' ) {
		$image_class = 'h-auto';
	} else {
		$image_class = 'embed-responsive-item';

		if ( $cover =='x' ) {
			$image_class .= ' embed-item-cover-x';
		} else if ( $cover == 'y' ) {
			$image_class .= ' embed-item-cover-y';
		} else if ( $cover == 'n' ) {
			$image_class .= ' embed-item-contain';
		} else {
			$image_class .= ' embed-item-cover-xy';
		}
	}
	?>
		<div class="geodir-images aui-gallery geodir-images-n-<?php echo (int) $image_total; ?> geodir-images-<?php echo esc_attr( $type . " " . $inner_wrapper_class ); ?>"<?php echo ( $type == 'masonry' ? 'data-masonry=\'{"itemSelector":".geodir-masonry-item","percentPosition":"true"}\'' : '' ); ?>>
		<?php
		foreach ( $post_images as $i => $image ) {
			// Reset temp tags
			$link_tag_open_ss = '';
			$link_tag_close_ss = '';

			if ( $type == 'slider' || $type == 'image' ) {
				if ( $geodir_carousel_open ) {
					echo '<div ' . ( $image_count == 0 && $limit_show && $max_width_percent ? 'style="width:' . (float) $max_width_percent . '%"' : '' ) . '>';
				} else {
					echo '<div class="carousel-item' . ( $image_count == 0 ? ' active' : '' ) . '"' . ( $image_count == 0 && $limit_show && $max_width_percent ? ' style="width:' . (float) $max_width_percent . '%"' : '' ) . '>';
				}
			} else if( $type == 'gallery' ) {
				echo '<div class="col' . ( $aui_bs5 ? '' : ' mb-4' ) . '"' . ( $limit_show && $image_count >= $limit_show ? ' style="display:none"' : '' ) . '><div class="card m-0 p-0 overflow-hidden">';
			} else if ( $type == 'masonry' ) {
				echo '<div class="col' . ( $aui_bs5 ? '' : ' mb-4' ) . ' geodir-masonry-item"><div class="card m-0 p-0 overflow-hidden">';
			}

			$img_tag = geodir_get_image_tag( $image, $image_size, '', $image_class );
			$meta = isset( $image->metadata ) ? maybe_unserialize( $image->metadata ) : '';

			// Only set different sizes if not thumbnail
			if ( $image_size != 'thumbnail' ) {
				$img_tag =  wp_image_add_srcset_and_sizes( $img_tag, $meta , 0 );
			}

			// Image link
			if ( $link_to == 'lightbox' ) {
				$link = geodir_get_image_src( $image, $lightbox_image_size );
			}

			// Check if screenshot link is different
			if ( $link_screenshot_to != '' && $link_screenshot_to != $link_to && ! $image->ID && stripos( strrev( $image->type ), "tohsneercs_" ) === 0 ) {
				$lightbox_attrs = apply_filters( 'geodir_link_to_lightbox_attrs', '' );

				if ( $link_screenshot_to == 'post' ) {
					$link = get_the_permalink( $post->ID );
					$link_tag_open_ss = '<a href="%s" class="' . esc_attr( $responsive_image_class ) . '">';
					$link_tag_close_ss = '</a>';
				} else if ( $link_screenshot_to == 'lightbox' ) {
					$link = geodir_get_image_src( $image, $lightbox_image_size );
					$link_tag_open_ss = '<a href="%s" class="aui-lightbox-image ' . esc_attr( $responsive_image_class ) . '" ' . $lightbox_attrs . '>';
					$link_tag_close_ss = "<i class=\"fas fa-search-plus  w-auto h-auto\" aria-hidden=\"true\"></i></a>";
				} else if ( $link_screenshot_to == 'lightbox_url' ) {
					$field_key = str_replace( "_screenshot", "", $image->type );
					$link = isset( $the_post->{$field_key} ) ? $the_post->{$field_key} : '';
					$fa_icon = 'fas fa-link';

					if ( ! empty( $meta ) && is_array( $meta ) ) {
						if ( ! empty( $meta['media_url'] ) ) {
							$link = $meta['media_url'];
						}

						if ( ! empty( $meta['mime_type'] ) && strpos( $meta['mime_type'], 'video' ) === 0 ) {
							$fa_icon = 'fas fa-video';
						}
					}

					// Check if youtube
					$screenshot_base_url = 'https://www.youtube.com/embed/%s';

					// Check if its a video URL
					if ( ! empty( $link ) && preg_match("/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/", $link , $matches ) ) {
						if ( ! empty( $matches[1] ) ) {
							$link = sprintf( $screenshot_base_url, esc_attr( $matches[1] ) );
							$fa_icon = 'fas fa-video';
						}
					}

					$link_tag_open_ss = '<a href="%s" class="geodir-lightbox-iframe ' . esc_attr( $responsive_image_class ) . '">';
					$link_tag_close_ss = '<i class="' . esc_attr( $fa_icon ) . ' w-auto h-auto" aria-hidden="true"></i></a>';
				} else if ( $link_screenshot_to == 'url' || $link_screenshot_to == 'url_same' ) {
					$field_key = str_replace( "_screenshot", "", $image->type );
					$link_icon = $link_screenshot_to == 'url' ? "fas fa-external-link-alt" : 'fas fa-link';
					$link = isset( $the_post->{$field_key} ) ? $the_post->{$field_key} : '';
					if ( ! empty( $meta ) && is_array( $meta ) && ! empty( $meta['media_url'] ) ) {
						$link = $meta['media_url'];
					}
					$link_tag_open_ss = '<a href="%s" class="' . esc_attr( $responsive_image_class ) . '" rel="nofollow noopener noreferrer"' . ( $link_screenshot_to == 'url' ? " target='_blank'" : '' ) . '>';
					$link_tag_close_ss = '<i class="' . esc_attr( $link_icon ) . ' w-auto h-auto" aria-hidden="true"></i></a>';
				}
			}

			// Ajaxify images
			if ( $type == 'slider' && $ajax_load ) {
				if ( ! $image_count && geodir_is_page( 'single' ) ) {
					// Don't ajax the first image for a details page slider to improve the FCP pagespeed score.
				} else {
					$img_tag = geodir_image_tag_ajaxify( $img_tag );
				}
			} else if ( $ajax_load ) {
				$img_tag = geodir_image_tag_ajaxify( $img_tag );
			}

			$img_tag_output = '';
			if ( $link_tag_open_ss ) {
				$img_tag_output .= sprintf( $link_tag_open_ss, esc_url( $link ) );
			} else if ( $link_tag_open ) {
				$img_tag_output .= sprintf( $link_tag_open, esc_url( $link ) );
			}

			$img_tag_output .= $img_tag;

			if ( $link_tag_close_ss ) {
				$img_tag_output .= $link_tag_close_ss;
			} else {
				$img_tag_output .= $link_tag_close;
			}

			// Output image
			echo $img_tag_output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			if ( $show_title && ! empty( $image->title ) ) {
				$title = stripslashes( $image->title );
			} else {
				$title = '';
			}

			/**
			 * Filters whether or not the caption should be displayed.
			 *
			 * @since   2.0.0.63
			 * @package GeoDirectory
			 */
			$show_caption = apply_filters( 'geodir_post_images_show_caption', $show_caption );

			if ( $show_caption && ! empty( $image->caption ) ) {
				$caption = stripslashes( $image->caption );
			} else {
				$caption = '';
			}

			if ( $title || $caption ) {
				?>
				<div class="carousel-caption d-none d-md-block p-0 m-0 py-1 w-100 rounded-bottom<?php echo ( $type == 'gallery' ? ' sr-only visually-hidden' : '' ); ?>" style="bottom:0;left:0;background:#00000060">
					<h5 class="m-0 p-0 h6 font-weight-bold fw-bold text-white"><?php echo esc_html( $title );?></h5>
					<p class="m-0 p-0 h6 text-white"><?php echo esc_textarea( $caption );?></p>
				</div>
				<?php
			}

			if ( $type == 'gallery' || $type == 'masonry' ) {
				echo "</div></div>";
			} else {
				echo "</div>";
			}

			$image_count++;
		}
		?>
		</div>
		<?php
		if ( $type == 'slider' ) {
			?>
			<a class="carousel-control-prev" href="#<?php echo esc_attr( $slider_id ); ?>" role="button" data-slide="prev">
				<span class="carousel-control-prev-icon" aria-hidden="true"></span>
				<span class="sr-only visually-hidden"><?php esc_html_e( 'Previous', 'geodirectory' ); ?></span>
			</a>
			<a class="carousel-control-next" href="#<?php echo esc_attr( $slider_id ); ?>" role="button" data-slide="next">
				<span class="carousel-control-next-icon" aria-hidden="true"></span>
				<span class="sr-only visually-hidden"><?php esc_html_e( 'Next', 'geodirectory' ); ?></span>
			</a>
			<?php
		}

		if ( $type == 'slider' && $controlnav == 1 && $image_count > 1 ) {
			$image_count = 0;
			?>
			<ol class="carousel-indicators w-100 position-relative mx-0 my-1">
				<?php
				foreach ( $post_images as $image ) {
					echo '<li data-target="#' . esc_attr( $slider_id ) . '" data-slide-to="' . esc_attr( $image_count ) . '" class="my-1 mx-1 bg-dark' . ( $image_count == 0 ? ' active' : '' ) . '"></li>';
					$image_count++;
				}
				?>
			</ol>
			<?php
		} else if ( $type == 'slider' && $controlnav == 2 && $image_count > 1 ) {
			$image_count = 0;
			?>
			<ul class="carousel-indicators w-100 position-relative mx-0 my-1 list-unstyled overflow-auto scrollbars-ios">
				<?php
				foreach ( $post_images as $image ) {
					$img_tag = geodir_get_image_tag( $image, 'thumbnail', '', 'embed-item-cover-xy' );

					echo '<li data-target="#' . esc_attr( $slider_id ) . '" data-slide-to="' . esc_attr( $image_count ) . '" class="my-1 mx-1 bg-dark list-unstyled border-0' . ( $image_count == 0 ? ' active' : '' ) . '" style="text-indent:0;height:60px;width:60px;min-width:60px;">';
					echo $img_tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo '</li>';

					$image_count++;
				}
				?>
			</ul>
			<?php
		}

		// Slider close
		if ( $type == 'slider' ) { ?>
	</div>
	<?php } ?>
</div>
