<?php
namespace Bricks;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Geodir_Element_Image_Gallery extends Element {
	public $category = 'geodirectory';
	public $name     = 'geodir-image-gallery';
	public $icon     = 'ti-gallery';
	public $scripts  = [ 'bricksIsotope' ];

	public function get_label() {
		return esc_html__( 'GD Image Gallery', 'geodirectory' );
	}

	public function enqueue_scripts() {
		$layout = ! empty( $this->settings['layout'] ) ? $this->settings['layout'] : 'grid';

		if ( $layout === 'masonry' ) {
			wp_enqueue_script( 'bricks-isotope' );
			wp_enqueue_style( 'bricks-isotope' );
		}

		$link_to = ! empty( $this->settings['link'] ) ? $this->settings['link'] : false;

		if ( $link_to === 'lightbox' ) {
			wp_enqueue_script( 'bricks-photoswipe' );
			wp_enqueue_script( 'bricks-photoswipe-lightbox' );
			wp_enqueue_style( 'bricks-photoswipe' );
		}
	}

	public function set_controls() {
		$this->controls['_border']['css'][0]['selector']    = '.image';
		$this->controls['_boxShadow']['css'][0]['selector'] = '.image';

		$this->controls['gdImageField'] = [
			'tab'         => 'content',
			'label'       => esc_html__( 'Image Field', 'geodirectory' ),
			'type'        => 'select',
			'options'     => $this->get_file_fields_options(),
			'placeholder' => esc_html__( 'Post Images', 'geodirectory' ),
			'inline'      => true,
			'rerender'    => true,
		];

		$this->controls['gdImageSize'] = [
			'tab'         => 'content',
			'label'       => esc_html__( 'Image Size', 'geodirectory' ),
			'type'        => 'select',
			'options'     => Setup::get_image_sizes_options(),
			'placeholder' => '',
		];

		$this->controls['gdImageLimit'] = [
			'tab'         => 'content',
			'label'       => esc_html__( 'Image Limit', 'geodirectory' ),
			'type'        => 'number',
			'min'         => 1,
			'css'         => [
				[
					'selector' => '',
				],
			],
			'rerender'    => true,
			'placeholder' => '',
		];

		$this->controls['settingsSeparator'] = [
			'tab'   => 'content',
			'label' => esc_html__( 'Settings', 'geodirectory' ),
			'type'  => 'separator',
		];

		$this->controls['layout'] = [
			'tab'         => 'content',
			'label'       => esc_html__( 'Layout', 'geodirectory' ),
			'type'        => 'select',
			'options'     => [
				'grid'    => esc_html__( 'Grid', 'geodirectory' ),
				'masonry' => esc_html__( 'Masonry', 'geodirectory' ),
				'metro'   => esc_html__( 'Metro', 'geodirectory' ),
			],
			'placeholder' => esc_html__( 'Grid', 'geodirectory' ),
			'inline'      => true,
			'rerender'    => true,
		];

		$this->controls['imageRatio'] = [
			'tab'         => 'content',
			'label'       => esc_html__( 'Image ratio', 'geodirectory' ),
			'type'        => 'select',
			'options'     => $this->control_options['imageRatio'],
			'inline'      => true,
			'placeholder' => esc_html__( 'Square', 'geodirectory' ),
			'required'    => [ 'layout', '!=', [ 'masonry', 'metro' ] ],
		];

		$this->controls['imageHeight'] = [
			'tab'         => 'content',
			'label'       => esc_html__( 'Image height', 'geodirectory' ),
			'type'        => 'number',
			'units'       => true,
			'css'         => [
				[
					'property'  => 'padding-top',
					'selector'  => '.image',
					'important' => true,
				],
			],
			'placeholder' => '',
			'required'    => [ 'layout', '!=', [ 'masonry', 'metro' ] ],
		];

		$this->controls['columns'] = [
			'tab'         => 'content',
			'label'       => esc_html__( 'Columns', 'geodirectory' ),
			'type'        => 'number',
			'min'         => 2,
			'css'         => [
				[
					'property' => '--columns',
					'selector' => '',
				],
			],
			'rerender'    => true,
			'placeholder' => 3,
			'required'    => [ 'layout', '!=', [ 'metro' ] ],
		];

		$this->controls['gutter'] = [
			'tab'         => 'content',
			'label'       => esc_html__( 'Spacing', 'geodirectory' ),
			'type'        => 'number',
			'units'       => true,
			'css'         => [
				[
					'property' => '--gutter',
					'selector' => '',
				],
			],
			'placeholder' => 0,
		];

		$this->controls['link'] = [
			'tab'         => 'content',
			'label'       => esc_html__( 'Link to', 'geodirectory' ),
			'type'        => 'select',
			'options'     => [
				'lightbox'   => esc_html__( 'Lightbox', 'geodirectory' ),
				'post' => esc_html__( 'Post', 'geodirectory' ),
				'media'      => esc_html__( 'Media File', 'geodirectory' ),
				'custom'     => esc_html__( 'Custom URL', 'geodirectory' ),
			],
			'inline'      => true,
			'placeholder' => esc_html__( 'None', 'geodirectory' ),
		];

		$this->controls['lightboxImageSize'] = [
			'tab'         => 'content',
			'label'       => esc_html__( 'Lightbox image size', 'geodirectory' ),
			'type'        => 'select',
			'options'     => $this->control_options['imageSizes'],
			'placeholder' => esc_html__( 'Full', 'geodirectory' ),
			'required'    => [ 'link', '=', 'lightbox' ],
		];

		$this->controls['lightboxAnimationType'] = [
			'tab'         => 'content',
			'label'       => esc_html__( 'Lightbox animation type', 'geodirectory' ),
			'type'        => 'select',
			'options'     => $this->control_options['lightboxAnimationTypes'],
			'placeholder' => esc_html__( 'Zoom', 'geodirectory' ),
			'required'    => [ 'link', '=', 'lightbox' ],
		];

		$this->controls['linkCustom'] = [
			'tab'         => 'content',
			'label'       => esc_html__( 'Custom links', 'geodirectory' ),
			'type'        => 'repeater',
			'fields'      => [
				'link' => [
					'label'   => esc_html__( 'Link', 'geodirectory' ),
					'type'    => 'link',
					'exclude' => [
						'lightboxImage',
						'lightboxVideo',
					],
				],
			],
			'placeholder' => esc_html__( 'Custom link', 'geodirectory' ),
			'required'    => [ 'link', '=', 'custom' ],
		];

		$this->controls['caption'] = [
			'tab'   => 'content',
			'label' => esc_html__( 'Caption', 'geodirectory' ),
			'type'  => 'checkbox',
		];
	}

	public function get_normalized_image_settings( $settings ) {
		global $gd_post;

		$image_field = ! empty( $settings['gdImageField'] ) ? $settings['gdImageField'] : 'post_images';
		$size  = ! empty( $settings['gdImageSize'] ) ? $settings['gdImageSize'] : BRICKS_DEFAULT_IMAGE_SIZE;
		$lightbox_image_size = ! empty( $settings['lightboxImageSize'] ) ? $settings['lightboxImageSize'] : 'full';
		$items = ! empty( $settings['items'] ) ? $settings['items'] : [];
		$image_limit = ! empty( $settings['gdImageLimit'] ) ? absint( $settings['gdImageLimit'] ) : 0;

		$post_id = ! empty( $gd_post ) && ! empty( $gd_post->ID ) ? (int) $gd_post->ID : 0;
		$revision_id = is_preview() && ! empty( $post_id ) ? $post_id : '';
		if ( $post_id && wp_is_post_revision( $post_id ) ) {
			$post_id = wp_get_post_parent_id( $post_id );
		}

		if ( self::is_bricks_preview() ) {
			$post_id = -1;

			$post_images = self::get_dummy_images( $image_limit );

			$count = 0;

			foreach ( $post_images as $image ) {
				$items['images'][] = [
					'id'   => 0,
					'full' => $image->file,
					'url'  => $image->file,
					'lightbox' => $image->file,
					'sizes' => array(),
					'item' => $image
				];

				$count++;

				if ( $image_limit > 0 && $count >= $image_limit ) {
					break;
				}
			}
		} else {
			if ( is_preview() && geodir_listing_belong_to_current_user( $post_id ) ) {
				$status = '';
			} else {
				$status = '1';
			}

			$post_images = geodir_get_images( $post_id, '', false, $revision_id, array( $image_field ), '', $status );

			if ( ! empty( $post_images ) ) {
				$count = 0;

				foreach ( $post_images as $image ) {
					$metadata = isset( $image->metadata ) ? maybe_unserialize( $image->metadata ) : array();

					$full = geodir_get_image_src( $image, 'full' );
					$url = $size == 'full' ? $full : geodir_get_image_src( $image, $size );
					$lightbox = $lightbox_image_size == 'full' ? $full : geodir_get_image_src( $image, $lightbox_image_size );

					$sizes = array();
					if ( ! empty( $metadata['width'] ) && ! empty( $metadata['height'] ) ) {
						$sizes['full'] = array(
							$metadata['width'],
							$metadata['height']
						);
					} else {
						$sizes['full'] = array();
					}

					if ( ! empty( $metadata['sizes'][ $size ]['width'] ) && ! empty( $metadata['sizes'][ $size ]['height'] ) ) {
						$sizes['url'] = array(
							$metadata['sizes'][ $size ]['width'],
							$metadata['sizes'][ $size ]['height']
						);
					} else {
						$sizes['url'] = $sizes['full'];
					}

					if ( ! empty( $metadata['sizes'][ $lightbox_image_size ]['width'] ) && ! empty( $metadata['sizes'][ $lightbox_image_size ]['height'] ) ) {
						$sizes['lightbox'] = array(
							$metadata['sizes'][ $lightbox_image_size ]['width'],
							$metadata['sizes'][ $lightbox_image_size ]['height']
						);
					} else {
						$sizes['lightbox'] = $sizes['full'];
					}

					$items['images'][] = [
						'id'   => $image->ID,
						'full' => $full,
						'url'  => $url,
						'lightbox' => $lightbox,
						'sizes' => $sizes,
						'item' => $image
					];

					$count++;

					if ( $image_limit > 0 && $count >= $image_limit ) {
						break;
					}
				}
			}
		}

		// Old data structure (images were saved as one array directly on $items)
		if ( ! isset( $items['images'] ) ) {
			$images = ! empty( $items ) ? $items : [];

			unset( $items );

			$items['images'] = $images;
		}

		$settings['items']         = $items;
		$settings['items']['size'] = $size;

		return $settings;
	}

	public function render() {
		global $gd_post;

		$post_id = ! empty( $gd_post ) && ! empty( $gd_post->ID ) ? (int) $gd_post->ID : 0;
		$revision_id = is_preview() && ! empty( $post_id ) ? $post_id : '';
		if ( $post_id && wp_is_post_revision( $post_id ) ) {
			$post_id = wp_get_post_parent_id( $post_id );
		}

		$settings = $this->get_normalized_image_settings( $this->settings );
		$images   = ! empty( $settings['items']['images'] ) ? $settings['items']['images'] : false;
		$size     = ! empty( $settings['items']['size'] ) ? $settings['items']['size'] : BRICKS_DEFAULT_IMAGE_SIZE;
		$layout   = ! empty( $settings['layout'] ) ? $settings['layout'] : 'grid';
		$link_to  = ! empty( $settings['link'] ) ? $settings['link'] : false;
		$columns  = ! empty( $settings['columns'] ) ? $settings['columns'] : 3;

		// Return placeholder
		if ( ! $images ) {
			return $this->render_element_placeholder(
				[
					'title' => esc_html__( 'No image found.', 'geodirectory' ),
				]
			);
		}

		$root_classes = [ 'bricks-layout-wrapper' ];

		// Set isotopeJS CSS class
		if ( $layout === 'masonry' ) {
			$root_classes[] = 'isotope';
		}
		$root_classes[] = 'brxe-image-gallery';

		$this->set_attribute( '_root', 'class', $root_classes );
		$this->set_attribute( '_root', 'data-layout', $layout );

		foreach ( $images as $index => $item ) {
			$item_classes  = [ 'bricks-layout-item' ];
			$image_classes = [ 'image' ];
			$image_styles  = [];

			$this->set_attribute( "item-$index", 'class', $item_classes );

			// Get image url, width and height (Fallback: Placeholder image)
			if ( isset( $item['id'] ) ) {
				if ( ! empty( $item['sizes']['url'] ) ) {
					$image_src = [ $item['url'], $item['sizes']['url'][0], $item['sizes']['url'][1] ];
				} else {
					$image_src = [ $item['url'] ];
				}

				// Add 'data-id' attribute to image <li> (helps to perform custom JS logic based on attachment ID)
				$this->set_attribute( "item-$index", 'data-id', $item['id'] );
			} else if ( isset( $item['url'] ) ) {
				$image_src = [ $item['url'], 800, 600 ];
			}

			$image_src = ! empty( $image_src ) && is_array( $image_src ) ? $image_src : [ \Bricks\Builder::get_template_placeholder_image(), 800, 600 ];

			$image_url    = ! empty( $image_src[0] ) ? $image_src[0] : ( isset( $item['url'] ) ? $item['url'] : '' );
			$image_width  = ! empty( $image_src[1] ) ? $image_src[1] : 200;
			$image_height = ! empty( $image_src[2] ) ? $image_src[2] : 200;

			if ( $image_width ) {
				$this->set_attribute( "img-$index", 'width', $image_width );
			}

			if ( $image_height ) {
				$this->set_attribute( "img-$index", 'height', $image_height );
			}

			// Image lazy load
			if ( $this->lazy_load() ) {
				$image_classes[] = 'bricks-lazy-hidden';
				$image_classes[] = 'bricks-lazy-load-isotope';
			}

			// Layout-specific attributes
			if ( $layout !== 'masonry' ) {
				$image_classes[] = 'bricks-layout-inner';

				if ( $layout === 'grid' ) {
					// Precedes imageRatio setting
					if ( isset( $settings['imageRatio'] ) && ! empty( $settings['imageRatio'] ) ) {
						$image_classes[] = 'bricks-' . $settings['imageRatio'];
					} elseif ( isset( $settings['imageHeight'] ) ) {
						$image_styles[] = 'width: 100%';
						$image_styles[] = "height: {$settings['imageHeight']}";
					} else {
						// Default: Ratio square
						$image_classes[] = 'bricks-ratio-square';
					}
				}

				$image_styles[] = "background-image: url({$image_url})";

				$image_styles = join( '; ', $image_styles );

				$this->set_attribute( "img-$index", $this->lazy_load() ? 'data-style' : 'style', $image_styles );

				if ( isset( $item['id'] ) ) {
					$this->set_attribute( "img-$index", 'role', 'img' );
					$this->set_attribute( "img-$index", 'aria-label', ! empty( $item['item']->title ) ? stripslashes( $item['item']->title ) : '' );
				}
			}

			// CSS filters
			$image_classes[] = 'css-filter';
		}

		// Item sizer (Isotope requirement)
		$item_sizer_classes = [ 'bricks-isotope-sizer' ];

		$this->set_attribute( 'item-sizer', 'class', $item_sizer_classes );

		// STEP: Render
		$layout = isset( $settings['layout'] ) ? $settings['layout'] : 'grid';
		$gutter = isset( $settings['gutter'] ) ? $settings['gutter'] : '0px';

		if ( $link_to === 'lightbox' ) {
			$this->set_attribute( '_root', 'class', 'bricks-lightbox' );

			if ( ! empty( $settings['lightboxAnimationType'] ) ) {
				$this->set_attribute( '_root', 'data-animation-type', esc_attr( $settings['lightboxAnimationType'] ) );
			}
		}

		echo "<ul " . $this->render_attributes( '_root' ) . ">";

		foreach ( $images as $index => $item ) {
			$close_a_tag = false;
			$caption     = isset( $settings['caption'] ) && ! empty( $item['item']->caption ) ? stripslashes( $item['item']->caption ) : false;

			echo "<li " . $this->render_attributes( "item-{$index}" ) . ">";

			if ( $link_to === 'post' && ! empty( $post_id ) ) {
				$close_a_tag = true;

				echo '<a href="' . get_permalink( $post_id ) . '" target="_blank">';
			} else if ( $link_to === 'media' ) {
				$close_a_tag = true;

				echo '<a href="' . esc_url( $item['url'] ) . '" target="_blank">';
			} else if ( $link_to === 'custom' && isset( $settings['linkCustom'][ $index ]['link'] ) ) {
				$close_a_tag = true;

				$this->set_link_attributes( "a-$index", $settings['linkCustom'][ $index ]['link'] );

				echo "<a " . $this->render_attributes( "a-$index" ) . ">";
			} else if ( $link_to === 'lightbox' ) {
				$lightbox_image = ! empty( $item['lightbox'] ) ? $item['lightbox'] : false;

				if ( ! empty( $item['sizes']['lightbox'] ) ) {
					$lightbox_image = [ $lightbox_image, $item['sizes']['lightbox'][0], $item['sizes']['lightbox'][1] ];
				} else {
					$lightbox_image = [ $lightbox_image, 800, 600 ];
				}

				$this->set_attribute( "a-$index", 'href', $lightbox_image[0] );
				$this->set_attribute( "a-$index", 'data-pswp-src', $lightbox_image[0] );
				$this->set_attribute( "a-$index", 'data-pswp-width', $lightbox_image[1] );
				$this->set_attribute( "a-$index", 'data-pswp-height', $lightbox_image[2] );

				$close_a_tag = true;

				echo "<a " . $this->render_attributes( "a-$index" ) . ">";
			}

			if ( $layout === 'masonry' ) {
				$metadata = isset( $item['item']->metadata ) ? maybe_unserialize( $item['item']->metadata ) : '';
				$image_class = implode( ' ', $image_classes );
				$img_tag = geodir_get_image_tag( $item['item'], $size, '', $image_class );

				if ( $size != 'thumbnail' ) {
					$img_tag =  wp_image_add_srcset_and_sizes( $img_tag, $metadata , 0 );
				}

				echo $img_tag;
			} else {
				$this->set_attribute( "img-$index", 'class', $image_classes );

				echo "<div " . $this->render_attributes( "img-$index" ) . "></div>";
			}

			if ( $caption ) {
				echo "<div class=\"bricks-image-caption\">$caption</div>";
			}

			if ( $close_a_tag ) {
				echo '</a>';
			}

			echo '</li>';
		}

		if ( $layout === 'masonry' ) {
			echo "<li " . $this->render_attributes( 'item-sizer' ) . "></li>";
			echo '<li class="bricks-gutter-sizer"></li>';
		}

		echo '</ul>';
	}

	public function get_file_fields_options(){
		$file_types = $this->get_file_types();
		$fields = geodir_post_custom_fields( '', 'all', 'all' ,'none' );

		$options = array();

		foreach ( $fields as $key => $field ) {
			if ( ! empty( $field['type'] ) && in_array( $field['type'], $file_types ) ) {
				$field = stripslashes_deep( $field );
				$options[ $field['name'] ] = ! empty( $field['admin_title'] ) ? __( $field['admin_title'], 'geodirectory' ) : __( $field['frontend_title'], 'geodirectory' );
			}
		}

		return $options;
	}

	public function get_file_types() {
		return array(
			'image',
			'images',
			'file'
		);
	}

	public static function is_bricks_preview() {
		$result = false;

		if ( function_exists( 'bricks_is_builder' ) && ( bricks_is_builder() || bricks_is_builder_call() ) ) {
			$result = true;
		}

		return $result;
	}

	public static function get_dummy_images(){
		$dummy_image_url = 'https://ayecode.b-cdn.net/dummy/plugin/';
		$dummy_images = array(
			'burger.jpg',
			'food-salad.jpg',
			'las-vegas.jpg',
			'plate-restaurant.jpg',
			'san-francisco.jpg',
			'sea-scape.jpg',
		);

		$images = array();
		$count = 1;
		foreach( $dummy_images as $dummy_image ) {
			$image = array();
			$image['ID'] = 0;
			$image['post_id'] = 0;
			$image['user_id'] = 0;
			$image['title'] = wp_sprintf( __( 'Demo image title %d', 'geodirectory' ), $count );
			$image['caption'] = wp_sprintf( __( 'Demo image caption %d', 'geodirectory' ), $count );
			$image['file'] = $dummy_image_url . $dummy_image;
			$image['mime_type'] = '';
			$image['menu_order'] = 0;
			$image['featured'] = 0;
			$image['is_approved'] = 1;
			$image['metadata'] = '';
			$image['type'] = '_dummy';

			$images[] = (object) $image;
			$count++;
		}

		return $images;
	}
}
