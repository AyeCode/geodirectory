<?php
/**
 * GeoDir_Widget_Post_Image class.
 *
 * @since 2.0.0
 */
class GeoDir_Widget_Post_Images extends WP_Super_Duper {

	public $arguments;
	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {

		$options = array(
			'textdomain' => GEODIRECTORY_TEXTDOMAIN,
			'block-icon' => 'format-image',
			'block-category' => 'geodirectory',
			'block-keywords' => "['images','geo','geodir']",
			'block-supports' => array(
				'customClassName' => false
			),
			'class_name' => __CLASS__,
			'base_id' => 'gd_post_images',
			'name' => __( 'GD > Post Images', 'geodirectory' ),
			'widget_ops' => array(
				'classname' => 'geodir-post-slider ' . geodir_bsui_class(),
				'description' => esc_html__( 'This shows a GD post image.', 'geodirectory' ),
				'geodirectory' => true,
			),
			'arguments' => array(
				'title'=> array(
					'type' => 'text',
					'title' => __( 'Title', 'geodirectory' ),
					'desc' => __( 'The widget title.', 'geodirectory' ),
					'default' => '',
					'desc_tip' => true,
					'advanced' => false,
					'group'     => __("General","geodirectory"),
				),
				'id'  => array(
					'title' => __( 'Post ID:', 'geodirectory' ),
					'desc' => __( 'Leave blank to use current post id.', 'geodirectory' ),
					'type' => 'number',
					'placeholder' => __( 'Leave blank to use current post id.', 'geodirectory' ),
					'desc_tip' => true,
					'default'  => '',
					'advanced' => false
				),
				'types'  => array(
					'title' => __('Image types:', 'geodirectory'),
					'desc' => __('Comma separated list of image types to show. Defaults to: post_images', 'geodirectory'),
					'type' => 'text',
					'desc_tip' => true,
					'value'  => '',
					'default'  => '',
					'placeholder'  => 'post_images,logo,comment_images,website_screenshot',
					'advanced' => false,
					'group'     => __("General","geodirectory"),
				),
				'fallback_types'  => array(
					'title' => __('Fallback types:', 'geodirectory'),
					'desc' => __('Comma separated list of fallback types to show (only one will be shown). Defaults to: logo,cat_default,cpt_default,listing_default', 'geodirectory'),
					'type' => 'text',
					'desc_tip' => true,
					'value'  => '',
					'default'  => '',
					'placeholder'  => 'logo,cat_default,cpt_default,listing_default',
					'advanced' => false,
					'group'     => __("General","geodirectory"),
				),

				'ajax_load'  => array(
					'title' => __('Load via Ajax', 'geodirectory'),
					'desc' => __('This will load all but the first slide via ajax for faster load times.', 'geodirectory'),
					'type' => 'checkbox',
					'desc_tip' => true,
					'value'  => '1',
					'default'  => 1,
					'advanced' => true,
					'group'     => __("General","geodirectory"),
				),
				'limit'  => array(
					'title' => __('Image limit:', 'geodirectory'),
					'desc' => __('Limit the number of images returned.', 'geodirectory'),
					'type' => 'number',
					'desc_tip' => true,
					'value'  => '',
					'default'  => '',
					'advanced' => true,
					'group'     => __("General","geodirectory"),
				),
				'limit_show'  => array(
					'title' => __('Show limit:', 'geodirectory'),
					'desc' => __('Limit the number of images shown. This can be used to output 1-2 images in a gallery and if linked to lightbox it can ajax load more images when in lightbox. This can also be sued to turn the slider into a carousel and will set the default visible images.', 'geodirectory'),
					'type' => 'number',
					'desc_tip' => true,
					'value'  => '',
					'default'  => '',
					'advanced' => true,
					'group'     => __("General","geodirectory"),
				),

				'css_class'  => array(
					'type' => 'text',
					'title' => __('Extra class:', 'geodirectory'),
					'desc' => __('Give the wrapper an extra class so you can style things as you want.', 'geodirectory'),
					'placeholder' => '',
					'default' => '',
					'desc_tip' => true,
					'advanced' => true,
					'group'     => __("General","geodirectory"),
				),
				'type'  => array(
					'title' => __('Output Type', 'geodirectory'),
					'desc' => __('How the images should be displayed.', 'geodirectory'),
					'type' => 'select',
					'options'   =>  array(
						"image" => __('Single image', 'geodirectory'),
						"slider" => __('Slider', 'geodirectory'),
						"gallery" => __('Gallery', 'geodirectory'),
						"masonry" => __('Masonry Gallery', 'geodirectory'),
					),
					'default'  => 'image',
					'desc_tip' => true,
					'advanced' => false,
					'group'     => __("Design","geodirectory"),
				),
				'slideshow'  => array(
					'title' => __('Auto start', 'geodirectory'),
					'desc' => __('Should the slider auto start.', 'geodirectory'),
					'type' => 'checkbox',
					'desc_tip' => true,
					'value'  => '1',
					'default'  => 1,
					'element_require' => '[%type%]=="slider"',
					'advanced' => true,
					'group'     => __("Design","geodirectory"),
				),
				'controlnav'  => array(
					'title' => __('Control Navigation', 'geodirectory'),
					'desc' => __('Image navigation controls below slider.', 'geodirectory'),
					'type' => 'select',
					'options'   =>  array(
						"1" => __('Default', 'geodirectory'),
						"0" => __('None', 'geodirectory'),
						"2" => __('Thumbnails (not ajax compatible)', 'geodirectory'),
					),
					'default'  => '1',
					'desc_tip' => true,
					'element_require' => '[%type%]=="slider"',
					'advanced' => true,
					'group'     => __("Design","geodirectory"),
				),
				'animation'  => array(
					'title' => __('Animation', 'geodirectory'),
					'desc' => __('Slide or fade transition.', 'geodirectory'),
					'type' => 'select',
					'options'   =>  array(
						"slide" => __('Slide', 'geodirectory'),
						"fade" => __('Fade', 'geodirectory'),
					),
					'default'  => 'slide',
					'desc_tip' => true,
					'element_require' => '[%type%]=="slider"',
					'advanced' => true,
					'group'     => __("Design","geodirectory"),
				),
				'show_title'  => array(
					'title' => __('Show title', 'geodirectory'),
					'desc' => __('Show the titles on the image.', 'geodirectory'),
					'type' => 'checkbox',
					'desc_tip' => true,
					'value'  => '1',
					'default'  => 1,
					'element_require' => '[%type%]!="gallery"',
					'advanced' => true,
					'group'     => __("Design","geodirectory"),
				),
				'show_caption'  => array(
					'title' 	=> __('Show caption', 'geodirectory'),
					'desc' 		=> __('Show the captions on the image. Requires you to enable titles.', 'geodirectory'),
					'type' 		=> 'checkbox',
					'desc_tip' 	=> true,
					'value'  	=> '0',
					'default'   => 0,
					'element_require' => '[%type%]!="gallery"',
					'advanced' 	=> true,
					'group'     => __("Design","geodirectory"),
				),
				'image_size'  => array(
					'title' => __('Image size:', 'geodirectory'),
					'desc' => __('The WP image size as a text string.', 'geodirectory'),
					'type' => 'select',
					'options' => self:: get_image_sizes(),
					'desc_tip' => true,
					'value'  => '',
					'default'  => '',
					'advanced' => true,
					'group'     => __("Design","geodirectory"),
				),
				'aspect'  => array(
					'title' => __('Aspect ratio', 'geodirectory')." ".__('(bootstrap only)', 'geodirectory'),
					'desc' => __('For a more consistent image view you can set the aspect ratio of the image view port.', 'geodirectory'),
					'type' => 'select',
					'options' => array(
						'' => __("Default (16by9)","geodirectory"),
						'21x9' => __("21by9","geodirectory"),
						'4x3' => __("4by3","geodirectory"),
						'1x1' => __("1by1 (square)","geodirectory"),
						'n' => __("No ratio (natural)","geodirectory"),
					),
					'desc_tip' => true,
					'value'  => '',
					'default'  => '',
					'advanced' => true,
					'group'     => __("Design","geodirectory"),
				),
				'cover'  => array(
					'title' => __('Image cover type:', 'geodirectory'),
					'desc' => __('This is how the image should cover the image viewport.', 'geodirectory'),
					'type' => 'select',
					'options' => array(
						'' => __("Default (cover both)","geodirectory"),
						'x' => __("Width cover","geodirectory"),
						'y' => __("height cover","geodirectory"),
						'n' => __("No cover (contain)","geodirectory"),
					),
					'desc_tip' => true,
					'value'  => '',
					'default'  => '',
					'advanced' => true,
					'group'     => __("Design","geodirectory"),
				),
				'link_to'  => array(
					'title' => __('Link to:', 'geodirectory'),
					'desc' => __('Link images to where.', 'geodirectory'),
					'type' => 'select',
					'options'   =>  array(
						"" => __('None', 'geodirectory'),
						"post" => __('Post', 'geodirectory'),
						"lightbox" => __('Lightbox image', 'geodirectory'),
					),
					'default'  => '',
					'desc_tip' => true,
					'advanced' => false,
					'group'     => __("Link","geodirectory"),
				),
				'link_screenshot_to'  => array(
					'title' => __('Link screenshots to:', 'geodirectory'),
					'desc' => __('Link screenshot images to where.', 'geodirectory'),
					'type' => 'select',
					'options'   =>  array(
						"" => __('None', 'geodirectory'),
						"post" => __('Post', 'geodirectory'),
						"lightbox" => __('Lightbox image', 'geodirectory'),
						"lightbox_url" => __('Lightbox iframe URL', 'geodirectory'),
						"url" => __('URL (new window)', 'geodirectory'),
						"url_same" => __('URL (same window)', 'geodirectory'),
					),
					'default'  => '',
					'desc_tip' => true,
					'advanced' => true,
					'group'     => __("Link","geodirectory"),
				),

			)
		);

		$design_style = geodir_design_style();

		if ( $design_style ) {
			// background
			$arguments['bg']  = geodir_get_sd_background_input('mt');

			// margins
			$arguments['mt']  = geodir_get_sd_margin_input('mt');
			$arguments['mr']  = geodir_get_sd_margin_input('mr');
			$arguments['mb']  = geodir_get_sd_margin_input('mb' );
			$arguments['ml']  = geodir_get_sd_margin_input('ml');

			// padding
			$arguments['pt']  = geodir_get_sd_padding_input('pt');
			$arguments['pr']  = geodir_get_sd_padding_input('pr');
			$arguments['pb']  = geodir_get_sd_padding_input('pb');
			$arguments['pl']  = geodir_get_sd_padding_input('pl');

			// border
			$arguments['border']  = geodir_get_sd_border_input('border');
			$arguments['rounded']  = geodir_get_sd_border_input('rounded');
			$arguments['rounded_size']  = geodir_get_sd_border_input('rounded_size');

			// shadow
			$arguments['shadow']  = geodir_get_sd_shadow_input('shadow');

			$options['arguments'] = $options['arguments'] + $arguments;
		}

		parent::__construct( $options );
	}

	/**
	 * The Super block output function.
	 *
	 * @param array $args
	 * @param array $widget_args
	 * @param string $content
	 *
	 * @return mixed|string|void
	 */
	public function output( $args = array(), $widget_args = array(), $content = '' ) {
		/**
		 * @var bool $ajax_load Ajax load or not.
		 * @var string $animation Fade or slide.
		 * @var bool $slideshow Auto start or not.
		 * @var int $controlnav 0 = none, 1 =  standard, 2 = thumbnails
		 * @var bool $show_title If the title should be shown or not.
		 * @var int/empty $limit If the number of images should be limited.
		 */
		extract( $args, EXTR_SKIP );

		return $this->output_images( $args );
	}

	/**
	 * Output the image slider.
	 *
	* @global bool $geodir_carousel_open  Check whether carousel already open.
	 *
	 * @param $options
	 */
	public function output_images( $options ) {
		global $post, $gd_post, $geodir_carousel_open, $aui_bs5;

		// options
		$defaults = array(
			'title'     => '', // widget title
			'type'      => 'image', // image, slider, gallery
			'ajax_load' => '1',
			'animation' => 'fade', // fade or slide
			'slideshow' => '', // auto start
			'controlnav'=> '2', // 0 = none, 1 =  standard, 2 = thumbnails
			'show_title'=> '1',
			'show_caption' => '0',
			'limit'     => '',
			'limit_show'=> '',
			'link_to'   => '',
			'link_screenshot_to' => '',
			'image_size'=> 'medium',
			'show_logo' => '0',
			'cover' => '', // image cover type
			'aspect'=> '', // image aspect ratio
			'id'    => '',
			'types' => '', // types to show, post_images,comment_images,logo
			'fallback_types' => 'logo,cat_default,cpt_default,listing_default,website_screenshot', //logo,cat_default,cpt_default,listing_default
			'css_class' => '',
			'bg'    => '',
			'mt'    => '',
			'mb'    => '3',
			'mr'    => '',
			'ml'    => '',
			'pt'    => '',
			'pb'    => '',
			'pr'    => '',
			'pl'    => '',
			'border' => '',
			'rounded' => '',
			'rounded_size' => '',
			'shadow' => '',
		);

		/**
		 * Parse incoming $args into an array and merge it with $defaults
		 */
		$options = wp_parse_args( $options, $defaults );

		// Nested carousels/sliders are not supported.
		if ( $geodir_carousel_open && $options['type'] == 'slider' ) {
			$options['type'] = 'image';
			$options['slideshow'] = false;
			$options['limit_show'] = 1;
			$options['limit'] = 1;
		}

		$design_style = ! empty( $args['design_style'] ) ? esc_attr( $args['design_style'] ) : geodir_design_style();

		if ( $this->is_preview() || $options['type'] == 'masonry' ) {
			$options['ajax_load'] = false;
		}

		if ( $options['type'] == 'image' ) {
			$options['limit'] = 1;
		}

		$revision_id = is_preview() && ! empty( $gd_post->ID ) ? absint( $gd_post->ID ) : '';

		// Types
		if ( ! empty( $options['types'] ) ) {
			$options['types'] = explode( ",", $options['types'] );
		}

		// Fallback types
		if ( ! empty( $options['fallback_types'] ) ) {
			$options['fallback_types'] = explode( ",", $options['fallback_types'] );
		} else if ( $options['fallback_types'] == '0' ) {
			$options['fallback_types'] = array();
		} else {
			$options['fallback_types'] = array( 'logo', 'cat_default', 'cpt_default', 'listing_default' );
		}

		// check if block preview
		$block_preview = $this->is_block_content_call();
		$post_id = ! empty( $gd_post->ID ) ? absint( $gd_post->ID ) : 0;
		if ( $post_id && wp_is_post_revision( $post_id ) ) {
			$post_id = wp_get_post_parent_id( $post_id );
		}

		// Set static post ID.
		if ( ! empty( $options['id'] ) ) {
			$post_id = absint( $options['id'] );
		}

		$current_post = ! empty( $gd_post ) && $gd_post->ID == $post_id ? $gd_post : array();

		if ( empty( $current_post->post_id ) && ! empty( $post_id ) ) {
			$current_post = geodir_get_post_info( $post_id );
		}

		if ( $block_preview ) {
			$options['ajax_load'] = false; // disable ajax loading
			$post_id = -1;
		}

		if ( ! $post_id ) {
			return '';
		}

		if ( $block_preview ) {
			$post_images = $this->get_dummy_images( $options['limit'] );
		} else {
			// Show images with all statuses to admin & post author.
			if ( is_preview() && geodir_listing_belong_to_current_user( $post_id ) ) {
				$status = '';
			} else {
				$status = '1';
			}

			$post_images = geodir_get_images( $post_id, $options['limit'], $options['show_logo'], $revision_id, $options['types'], $options['fallback_types'], $status );
		}

		// Make it just a image if only one
		if ( $options['type'] == 'slider' && count( $post_images ) == 1 && $options['limit_show'] ) {
			$options['type'] = 'image';
		}

		ob_start();
		if ( ! empty( $post_images ) ) {
			if ( $options['type'] == 'masonry' ) {
				// Enqueue masonry.
				wp_enqueue_script( 'masonry' );
			}

			$main_wrapper_class = "geodir-image-container";
			$second_wrapper_class = "geodir-image-wrapper";
			$ul_class = "geodir-post-image";
			$image_size = isset( $options['image_size'] ) && $options['image_size'] ? $options['image_size'] : 'medium_large';
			$lightbox_image_size = ! empty( $options['lightbox_image_size'] ) ? $options['lightbox_image_size'] : 'large';
			$lightbox_image_size = apply_filters( 'geodir_post_images_lightbox_image_size', $lightbox_image_size );
			$main_wrapper_class .= " geodir-image-sizes-" . $image_size;

			if ( ! empty( $options ) ) {
				// rounded & rounded_size don't work together.
				if ( ! empty( $options['rounded_size'] ) && in_array( $options['rounded_size'], array( 'sm', 'lg' ) ) ) {
					if ( $aui_bs5 ) {
						if ( $options['rounded_size'] == 'sm' ) {
							$options['rounded_size'] = '1';
						} elseif ( $options['rounded_size'] == 'lg' ) {
							$options['rounded_size'] = '3';
						}
					}

					if ( ! empty( $options['rounded'] ) && in_array( $options['rounded'], array( 'rounded', 'rounded-top', 'rounded-right', 'rounded-bottom', 'rounded-left', 'rounded-circle', 'rounded-pill', 'rounded-0' ) ) ) {
						$options['rounded_size'] = '';
					}
				}

				// Wrap class
				$main_wrapper_class .= " " . sd_build_aui_class( $options );
			}

			if ( $options['type'] == 'slider' ) {
				if ( ! $design_style ) {
					// enqueue flexslider JS
					GeoDir_Frontend_Scripts::enqueue_script( 'jquery-flexslider' );
				}

				$main_wrapper_class .= " geodir_flex-container ";
				$second_wrapper_class .= " geodir_flexslider geodir-slider geodir-slider-loading ";
				$ul_class = "geodir-slides";

				if ( $options['limit_show'] ) {
					$second_wrapper_class .= " geodir-carousel ";
				}
			} else if ( $options['type'] == 'gallery' || $options['type'] == 'masonry' ) {
				if ( ! $image_size ) {
					$image_size = 'medium_large';
				}
				$ul_class = "geodir-gallery";
			}

			// Set the slider ID
			$slider_id = wp_doing_ajax() ? "geodir_images_ajax" : "geodir_images";
			$slider_id .= '_' . uniqid() . '_' . $post_id; // Generate unique slider id. //@todo this is not cache friendly

			// Responsive image class
			$aspect = $options['type'] == 'masonry' ? 'none' : $options['aspect'];
			$responsive_image_class = '';

			if ( $design_style ) {
				$embed_action_class = $options['link_to'] ? 'embed-has-action ' : '';

				if ( ! $aspect || $aspect == '16x9' ) {
					$responsive_image_class = $embed_action_class . 'embed-responsive embed-responsive-16by9 ratio ratio-16x9';
				} else if ( $aspect == '21x9' ) {
					$responsive_image_class = $embed_action_class . 'embed-responsive embed-responsive-21by9 ratio ratio-21x9';
				} else if ( $aspect == '4x3' ) {
					$responsive_image_class = $embed_action_class . 'embed-responsive embed-responsive-4by3 ratio ratio-4x3';
				} else if ( $aspect == '1x1' ) {
					$responsive_image_class = $embed_action_class . 'embed-responsive embed-responsive-1by1 ratio ratio-1x1';
				} else {
					$responsive_image_class = $embed_action_class;
				}
			}

			// Image link
			$link = '';
			$link_tag_open = "";
			$link_tag_close = "";

			if ( $options['link_to'] == 'post' ) {
				$link = get_the_permalink( $post_id );
				$link_tag_open = "<a href='%s' class='geodir-link-image $responsive_image_class d-block'>";
				$link_tag_close = "<i class=\"fas fa-link w-auto h-auto\" aria-hidden=\"true\"></i></a>";
			} else if ( $options['link_to'] == 'lightbox' ) {
				$link = '';
				$lightbox_attrs = apply_filters( 'geodir_link_to_lightbox_attrs', '' );
				$link_tag_open = "<a href='%s' class='geodir-lightbox-image $responsive_image_class d-block' data-lity {$lightbox_attrs}>";
				$link_tag_close = "<i class=\"fas fa-search-plus w-auto h-auto\" aria-hidden=\"true\"></i></a>";
			} else if ( $responsive_image_class ) {
				$link_tag_open = '<span class="' . esc_attr( $responsive_image_class ) . '">';
				$link_tag_close = '</span>';
			}

			// Image_cover
			if ( ! empty( $options['cover'] ) ) {
				if ( $options['cover'] == 'x' ) {
					$main_wrapper_class .= " gd-image-cover-x ";
				} else if ( $options['cover'] == 'y' ) {
					$main_wrapper_class .= " gd-image-cover-y ";
				} else if ( $options['cover'] == 'n' ) {
					$main_wrapper_class .= " gd-image-cover-n ";
				}
			}

			$main_wrapper_class_x = 'card-img-top embed-responsive-item';

			$args = array(
				'current_post' => $current_post,
				'main_wrapper_class' => " " . $main_wrapper_class . " " . geodir_sanitize_html_class( $options['css_class'] ),
				'type' => $options['type'],
				'slider_id' => $slider_id,
				'second_wrapper_class' => $second_wrapper_class,
				'controlnav' => $options['controlnav'],
				'animation' => $options['animation'],
				'slideshow' => $options['slideshow'],
				'limit' => $options['limit'],
				'limit_show' => $options['limit_show'],
				'ajax_load' => $options['ajax_load'],
				'show_title' => $options['show_title'],
				'show_caption' => $options['show_caption'],
				'ul_class' => $ul_class,
				'post_images' => $post_images,
				'link_to' => $options['link_to'],
				'link_screenshot_to' => $options['link_screenshot_to'],
				'link' => $link,
				'link_tag_open' => $link_tag_open,
				'link_tag_close' => $link_tag_close,
				'image_size' => $image_size,
				'lightbox_image_size' => $lightbox_image_size,
				'cover' => $options['cover'],
				'aspect' => $options['aspect'],
				'responsive_image_class' => $responsive_image_class
			);

			$template = $design_style ? $design_style."/images/images.php" : "legacy/images/images.php";

			$output = geodir_get_template_html( $template, $args );

			echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		// Make it work with builder preview
		if ( $this->is_elementor_preview() ) {
			?><script type="text/javascript">(function(){if(typeof init_read_more == 'function'){init_read_more();geodir_init_lazy_load();geodir_refresh_business_hours();geodir_init_flexslider();}}());</script><?php
		}

		return ob_get_clean();
	}

	/**
	 * Get the available image sizes.
	 *
	 * @return array
	 */
	public static function get_image_sizes() {
		$image_sizes = array( '' => 'default' );

		$available = get_intermediate_image_sizes();

		if ( ! empty( $available ) ) {
			foreach( $available as $size ) {
				$image_sizes[ $size ] = $size;
			}
		}

		$image_sizes['full'] = 'full';

		return $image_sizes;
	}

	/**
	 * Get dummy images for block demo content.
	 *
	 * @return array
	 */
	public static function get_dummy_images( $limit = 0 ) {
		$images = array();
		$dummy_image_url = 'https://ayecode.b-cdn.net/dummy/plugin/';
		$dummy_images = array(
			'burger.jpg',
			'food-salad.jpg',
			'las-vegas.jpg',
			'plate-restaurant.jpg',
			'san-francisco.jpg',
			'sea-scape.jpg',
		);

		$count = 1;

		foreach ( $dummy_images as $dummy_image ) {
			$image = new stdClass();
			$image->ID = 0;
			$image->post_id = 0;
			$image->user_id = 0;
			$image->title = wp_sprintf( __( 'Demo image title %d', 'geodirectory' ), $count );
			$image->caption = wp_sprintf( __( 'Demo image caption %d', 'geodirectory' ), $count );
			$image->file = $dummy_image_url . $dummy_image;
			$image->mime_type = '';
			$image->menu_order = 0;
			$image->featured= 0;
			$image->is_approved = 1;
			$image->metadata = '';
			$image->type = '_dummy';
			$images[] = $image;

			if ( $limit > 0 && $limit == $count ) {
				return $images;
			}

			$count++;
		}

		return $images;
	}
}
