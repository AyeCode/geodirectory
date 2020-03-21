<?php
/**
 * GeoDirectory Elementor
 *
 * Adds compatibility for Elementor page builder.
 *
 * @author   AyeCode
 * @category Compatibility
 * @package  GeoDirectory
 * @since    2.0.0.41
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GeoDir_Elementor {

	/**
	 * Setup class.
	 *
	 * @since 2.0.0
	 */
	public static function init() {
		// add any extra scripts
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ), 11 );
		add_filter( 'geodir_bypass_archive_item_template_content', array(
			__CLASS__,
			'archive_item_template_content'
		), 10, 3 );
		add_filter( 'elementor/utils/get_the_archive_title', array( __CLASS__, 'get_page_title' ), 10, 1 );

		// ajax
		add_action( 'wp_ajax_elementor_ajax', array( __CLASS__, 'maybe_hijack_ajax' ), 8 );

		// add templates
		//add_action( 'option_elementor_remote_info_library', array( __CLASS__, 'add_gd_templates' ), 10, 2 ); //@todo removed until ready

		// Dynamic content
		add_action( 'elementor/dynamic_tags/register_tags', array( __CLASS__, 'register_dynamic_content_tags' ) );

		// fix image sizes
		add_filter( 'elementor/image_size/get_attachment_image_html', array(
			__CLASS__,
			'maybe_fix_image_sizes'
		), 10, 4 );

		add_filter( 'elementor/widget/render_content', array(
			__CLASS__,
			'maybe_add_image_caption'
		), 10, 2 );

	}


	/**
	 * Add the image caption if present.
	 *
	 * @param $html
	 * @param $widget
	 *
	 * @return mixed
	 */
	public static function maybe_add_image_caption( $html, $widget ) {

		$type = $widget->get_name();
		if ( 'image' === $type ) {
			$settings = $widget->get_settings();

			if ( ! empty( $settings['__dynamic__']['image'] ) && strpos( $settings['__dynamic__']['image'], 'name="gd-image"' ) !== false && ! empty( $settings['caption_source'] ) && $settings['caption_source'] == 'attachment' ) {
				preg_match( '~alt[ ]*=[ ]*["\'](.*?)["\']~is', $html, $match );
				if ( ! empty( $match[1] ) ) {
					$html = str_replace( '></figcaption>', '>' . esc_attr( $match[1] ) . '</figcaption>', $html );
				}
			}
		} elseif ( 'image-gallery' === $type ) {
			$settings = $widget->get_settings();

			if ( ! empty( $settings['__dynamic__']['wp_gallery'] ) && strpos( $settings['__dynamic__']['wp_gallery'], 'name="gd-gallery"' ) !== false ) {
				preg_match( '~settings[ ]*=[ ]*["\'](.*?)["\']~is', $settings['__dynamic__']['wp_gallery'], $match );
				if ( ! empty( $match[1] ) ) {
					$gallery_settings = json_decode( urldecode( $match[1] ) );
					if ( ! empty( $gallery_settings->key ) ) {
						global $gd_post;
						$key = esc_attr( $gallery_settings->key );

						$post_images = GeoDir_Media::get_attachments_by_type( $gd_post->ID, $key );

						if ( ! empty( $post_images ) ) {
							// render gallery
							$attr = array(
								'columns'       => $settings['gallery_columns'],
								'link'          => $settings['gallery_link'],
								'size'          => $settings['thumbnail_size'],
								'open_lightbox' => $settings['open_lightbox'],
							);

							$open_lightbox = isset( $settings['open_lightbox'] ) ? $settings['open_lightbox'] : false;

							$image_html = self::render_gallery( $attr, $post_images, $widget, $open_lightbox );
							if ( ! empty( $image_html ) ) {
								$html = '<div class="elementor-image-gallery">';
								$html .= $image_html;
								$html .= '</div>';

							}

						}
					}
				}
			}

		}

		return $html;
	}

	/**
	 * Render the WP Gallery with our own images.
	 *
	 * @param $attr
	 * @param $attachments
	 * @param $widget
	 * @param bool $open_lightbox
	 *
	 * @return mixed|string|void
	 */
	public static function render_gallery( $attr, $attachments, $widget, $open_lightbox = false ) {
		$post = get_post();

		static $instance = 0;
		$instance ++;


		$html5 = current_theme_supports( 'html5', 'gallery' );
		$atts  = shortcode_atts(
			array(
				'order'      => 'ASC',
				'orderby'    => 'menu_order ID',
				'id'         => $post ? $post->ID : 0,
				'itemtag'    => $html5 ? 'figure' : 'dl',
				'icontag'    => $html5 ? 'div' : 'dt',
				'captiontag' => $html5 ? 'figcaption' : 'dd',
				'columns'    => 3,
				'size'       => 'thumbnail',
				'include'    => '',
				'exclude'    => '',
				'link'       => '',
			),
			$attr,
			'gallery'
		);

		$id = intval( $atts['id'] );

		if ( empty( $attachments ) ) {
			return '';
		}


		$itemtag    = tag_escape( $atts['itemtag'] );
		$captiontag = tag_escape( $atts['captiontag'] );
		$icontag    = tag_escape( $atts['icontag'] );
		$valid_tags = wp_kses_allowed_html( 'post' );
		if ( ! isset( $valid_tags[ $itemtag ] ) ) {
			$itemtag = 'dl';
		}
		if ( ! isset( $valid_tags[ $captiontag ] ) ) {
			$captiontag = 'dd';
		}
		if ( ! isset( $valid_tags[ $icontag ] ) ) {
			$icontag = 'dt';
		}

		$columns   = intval( $atts['columns'] );
		$itemwidth = $columns > 0 ? floor( 100 / $columns ) : 100;
		$float     = is_rtl() ? 'right' : 'left';

		$selector = "gallery-{$instance}";

		$gallery_style = '';

		/**
		 * Filters whether to print default gallery styles.
		 *
		 * @since 3.1.0
		 *
		 * @param bool $print Whether to print default gallery styles.
		 *                    Defaults to false if the theme supports HTML5 galleries.
		 *                    Otherwise, defaults to true.
		 */
		if ( apply_filters( 'use_default_gallery_style', ! $html5 ) ) {
			$type_attr = current_theme_supports( 'html5', 'style' ) ? '' : ' type="text/css"';

			$gallery_style = "
		<style{$type_attr}>
			#{$selector} {
				margin: auto;
			}
			#{$selector} .gallery-item {
				float: {$float};
				margin-top: 10px;
				text-align: center;
				width: {$itemwidth}%;
			}
			#{$selector} img {
				border: 2px solid #cfcfcf;
			}
			#{$selector} .gallery-caption {
				margin-left: 0;
			}
			/* see gallery_shortcode() in wp-includes/media.php */
		</style>\n\t\t";
		}

		$size_class  = sanitize_html_class( $atts['size'] );
		$gallery_div = "<div id='$selector' class='gallery galleryid-{$id} gallery-columns-{$columns} gallery-size-{$size_class}'>";

		/**
		 * Filters the default gallery shortcode CSS styles.
		 *
		 * @since 2.5.0
		 *
		 * @param string $gallery_style Default CSS styles and opening HTML div container
		 *                              for the gallery shortcode output.
		 */
		$output = apply_filters( 'gallery_style', $gallery_style . $gallery_div );

		$i = 0;


		foreach ( $attachments as $id => $attachment ) {

			$attr = ( trim( $attachment->caption ) ) ? array( 'aria-describedby' => "$selector-$id" ) : '';

			$img_tag = geodir_get_image_tag( $attachment, $atts['size'] );

			// elementor attributes

			$image_output = $img_tag;

			if ( ! empty( $atts['link'] ) && 'file' === $atts['link'] ) {
				$img_src      = geodir_get_image_src( $attachment, 'full' );
				$image_output = "<a href='$img_src'>" . $image_output . "</a>";
			}

			// maybe add lightbox
			$image_output = $widget->add_lightbox_data_to_image_link( $image_output, null );

			if ( $open_lightbox ) {
				$img_title = isset( $attachment->title ) ? esc_attr( $attachment->title ) : '';
				$widget->add_render_attribute( 'link', 'data-elementor-lightbox-title', $img_title );


				$img_desc = isset( $attachment->caption ) ? esc_attr( $attachment->caption ) : '';
				$widget->add_render_attribute( 'link', 'data-elementor-lightbox-description', $img_desc );

				$image_output = $widget->add_lightbox_data_to_image_link( $image_output, null );
			}

			$image_meta = isset( $attachment->metadata ) ? maybe_unserialize( $attachment->metadata ) : '';

			$orientation = '';

			if ( isset( $image_meta['height'], $image_meta['width'] ) ) {
				$orientation = ( $image_meta['height'] > $image_meta['width'] ) ? 'portrait' : 'landscape';
			}

			$output .= "<{$itemtag} class='gallery-item'>";
			$output .= "
			<{$icontag} class='gallery-icon {$orientation}'>
				$image_output
			</{$icontag}>";

			if ( $captiontag && trim( $attachment->caption ) ) {
				$output .= "
				<{$captiontag} class='wp-caption-text gallery-caption' id='$selector-$id'>
				" . wptexturize( $attachment->caption ) . "
				</{$captiontag}>";
			}

			$output .= "</{$itemtag}>";

			if ( ! $html5 && $columns > 0 && ++ $i % $columns === 0 ) {
				$output .= '<br style="clear: both" />';
			}
		}

		if ( ! $html5 && $columns > 0 && $i % $columns !== 0 ) {
			$output .= "
			<br style='clear: both' />";
		}

		$output .= "
		</div>\n";

		return $output;
	}

	/**
	 * Add title and alt to GD images.
	 *
	 * @param $html
	 * @param $settings
	 * @param string $image_size_key
	 * @param string $image_key
	 *
	 * @return mixed
	 */
	public static function maybe_fix_image_sizes( $html, $settings, $image_size_key = '', $image_key = '' ) {

		if ( ! empty( $settings['image_size'] ) && $settings['image_size'] != 'full' && ! empty( $settings['image']['gd-id'] ) ) {
			global $gd_post;
			$image_src   = $settings['image']['url'];
			$image_key   = $settings['image']['gd-key'];
			$post_images = GeoDir_Media::get_attachments_by_type( $gd_post->ID, $image_key, 1 );

			if ( ! empty( $post_images ) ) {
				$image   = $post_images[0];
				$size    = $settings['image_size'];
				$img_src = geodir_get_image_src( $image, $size );

				// replace image
				if ( $img_src ) {
					$html = str_replace( $image_src, $img_src, $html );
				}

				// title
				if ( ! empty( $image->title ) ) {
					$html = str_replace( 'title=""', 'title="' . esc_attr( $image->title ) . '"', $html );
				}

				// Alt text
				if ( ! empty( $settings['caption_source'] ) && $settings['caption_source'] == 'attachment' && ! empty( $image->caption ) ) {
					$html = str_replace( 'alt=""', 'alt="' . esc_attr( $image->caption ) . '"', $html );
				}
			}

		} elseif ( ! empty( $settings['image']['gd-url'] ) ) {
			$image_size = ! empty( $settings['image_size'] ) ? esc_attr( $settings['image_size'] ) : '';
			if ( $image_size ) {
				$image_sizes   = \Elementor\Group_Control_Image_Size::get_all_image_sizes();
				$image_src     = $settings['image']['url'];
				$image_url_src = esc_url_raw( $settings['image']['gd-url'] );
				if ( $image_size == 'custom' && ! empty( $settings['image_custom_dimension']['width'] ) ) {
					$width         = ! empty( $settings['image_custom_dimension']['width'] ) ? absint( $settings['image_custom_dimension']['width'] ) : 1024;
					$height        = ! empty( $settings['image_custom_dimension']['height'] ) ? absint( $settings['image_custom_dimension']['height'] ) : $width;
					$new_image_url = esc_url_raw( "https://wordpress.com/mshots/v1/$image_url_src?w=$width&amp;h=$height" );
					$html          = str_replace( $image_src, $new_image_url, $html );
				} elseif ( isset( $image_sizes[ $image_size ] ) ) {
					echo '###3';
					$width         = ! empty( $image_sizes[ $image_size ]['width'] ) ? absint( $image_sizes[ $image_size ]['width'] ) : 1024;
					$height        = ! empty( $image_sizes[ $image_size ]['height'] ) ? absint( $image_sizes[ $image_size ]['height'] ) : $width;
					$new_image_url = "https://wordpress.com/mshots/v1/$image_url_src?w=$width&amp;h=$height";
					$html          = str_replace( $image_src, $new_image_url, $html );
				}
			}
		}

		return $html;
	}

	/**
	 * Register dynamic content tags to use in elementor fields.
	 *
	 * @param $dynamic_tags
	 */
	public static function register_dynamic_content_tags( $dynamic_tags ) {
		// In our Dynamic Tag we use a group named request-variables so we need
		// To register that group as well before the tag
		\Elementor\Plugin::$instance->dynamic_tags->register_group( 'geodirectory', [
			'title' => 'GeoDirectory'
		] );

		$tag_classes = array(
			'GeoDir_Elementor_Tag_Text',
			'GeoDir_Elementor_Tag_URL',
			'GeoDir_Elementor_Tag_Image',
			'GeoDir_Elementor_Tag_Gallery',
		);

		// Finally register the tag
		foreach ( $tag_classes as $tag_class ) {
			$dynamic_tags->register_tag( $tag_class );
		}

	}


	/**
	 * Add GD template options to Elementor template directory.
	 *
	 * @param $value
	 * @param $option
	 *
	 * @return mixed
	 */
	public static function add_gd_templates( $value, $option ) {

		// add our own template
		if ( ! empty( $value['templates'] ) ) {
			$default_templates = $value['templates'];
			$templates         = array();

			// Realestate
			$templates[] = array(
				'id'                => 'ayecode-123',
				'title'             => "<i class=\"fas fa-globe-americas\" style='color:#ff8333 !important'></i> Homepage &#8211; Real-estate",
				'thumbnail'         => 'https://wpgeodirectory.com/dummy/elementor/realestate/preview.png',
				'tmpl_created'      => '1477388340',
				'1477388340'        => 'AyeCode',
				'url'               => 'https://ppldb.com/realestate-elemntor/home-version-3/',
				'type'              => 'page',
				'subtype'           => 'page',
				'tags'              => '["Directory","GeoDirectory","AyeCode"]',
				'menu_order'        => '3',
				'popularity_index'  => '4',
				'trend_index'       => '4',
				'is_pro'            => '0',
				'has_page_settings' => '0',
			);

			$value['templates'] = $templates + $default_templates;
		}

		return $value;
	}

	/**
	 * In some cases we need to hijack the Elementor AJAX to return our own values.
	 */
	public static function maybe_hijack_ajax() {
		if ( ! empty( $_POST['actions'] ) ) {
			$actions = json_decode( stripslashes( $_POST['actions'] ), true );
			if ( ! empty( $actions ) ) {
				foreach ( $actions as $key => $action ) {
					if ( ! empty( $action['action'] ) && $action['action'] == 'get_template_data' && substr( $key, 0, 8 ) === "ayecode-" ) {
						self::handle_template_ajax_request( $key );
					}
				}
			}
		}
	}

	/**
	 * Get our own template JSON when requested via AJAX.
	 *
	 * @param $key
	 */
	public static function handle_template_ajax_request( $key ) {
		// security
		check_ajax_referer( 'elementor_ajax', '_nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( - 1 );
		}

		$page    = self::json1();
		$content = self::template_response( $page, $key );
		wp_send_json_success( $content );
		exit;

	}

	/**
	 * A temp template.
	 * @todo we should move this to a API or maybe a CDN
	 *
	 * @return mixed
	 */
	public static function json1() {
		return json_decode( '{"version":"0.4","title":"home-minimal","type":"page","content":[{"id":"6d6d7e2a","settings":{"content_width":{"unit":"px","size":1000,"sizes":[]},"background_background":"classic","background_video_link":"https:\/\/wpinstapress.com\/geodirectorydemo\/wp-content\/uploads\/2020\/01\/video.mp4","background_overlay_color":"#1A2142","background_overlay_opacity":{"unit":"px","size":0.65000000000000002220446049250313080847263336181640625,"sizes":[]},"padding":{"unit":"%","top":"14","right":"0","bottom":"9","left":"0","isLinked":false},"background_image":{"id":214,"url":"https:\/\/ppldb.com\/realestate-elemntor\/wp-content\/uploads\/sites\/61\/2020\/01\/architecture-sky-sun-skyline-photography-town-496377-pxhere.com_-1.jpg"},"background_position":"center center","background_repeat":"no-repeat","background_image_tablet":{"url":"","id":""},"background_image_mobile":{"url":"","id":""},"background_video_fallback":{"url":"","id":""},"background_slideshow_gallery":[],"background_hover_image":{"url":"","id":""},"background_hover_image_tablet":{"url":"","id":""},"background_hover_image_mobile":{"url":"","id":""},"background_hover_video_fallback":{"url":"","id":""},"background_hover_slideshow_gallery":[],"background_overlay_image":{"url":"","id":""},"background_overlay_image_tablet":{"url":"","id":""},"background_overlay_image_mobile":{"url":"","id":""},"background_overlay_video_fallback":{"url":"","id":""},"background_overlay_slideshow_gallery":[],"background_overlay_hover_image":{"url":"","id":""},"background_overlay_hover_image_tablet":{"url":"","id":""},"background_overlay_hover_image_mobile":{"url":"","id":""},"background_overlay_hover_video_fallback":{"url":"","id":""},"background_overlay_hover_slideshow_gallery":[]},"elements":[{"id":"7771780a","settings":{"_column_size":100,"_inline_size":null,"animation":"fadeInUp","animation_duration":"fast","background_image":{"url":"","id":""},"background_image_tablet":{"url":"","id":""},"background_image_mobile":{"url":"","id":""},"background_video_fallback":{"url":"","id":""},"background_slideshow_gallery":[],"background_hover_image":{"url":"","id":""},"background_hover_image_tablet":{"url":"","id":""},"background_hover_image_mobile":{"url":"","id":""},"background_hover_video_fallback":{"url":"","id":""},"background_hover_slideshow_gallery":[],"background_overlay_image":{"url":"","id":""},"background_overlay_image_tablet":{"url":"","id":""},"background_overlay_image_mobile":{"url":"","id":""},"background_overlay_video_fallback":{"url":"","id":""},"background_overlay_slideshow_gallery":[],"background_overlay_hover_image":{"url":"","id":""},"background_overlay_hover_image_tablet":{"url":"","id":""},"background_overlay_hover_image_mobile":{"url":"","id":""},"background_overlay_hover_video_fallback":{"url":"","id":""},"background_overlay_hover_slideshow_gallery":[]},"elements":[{"id":"4e2b3849","settings":{"title":"Find Your Perfect Home","align":"center","title_color":"#000000","typography_typography":"custom","typography_font_family":"Poppins","typography_font_size":{"unit":"px","size":42,"sizes":[]},"typography_font_size_tablet":{"unit":"px","size":30,"sizes":[]},"typography_font_size_mobile":{"unit":"px","size":20,"sizes":[]},"typography_font_weight":"700","_background_image":{"url":"","id":""},"_background_image_tablet":{"url":"","id":""},"_background_image_mobile":{"url":"","id":""},"_background_video_fallback":{"url":"","id":""},"_background_slideshow_gallery":[],"_background_hover_image":{"url":"","id":""},"_background_hover_image_tablet":{"url":"","id":""},"_background_hover_image_mobile":{"url":"","id":""},"_background_hover_video_fallback":{"url":"","id":""},"_background_hover_slideshow_gallery":[]},"elements":[],"isInner":false,"widgetType":"heading","elType":"widget"},{"id":"3f2a8bce","settings":{"shortcode":"[gd_search]","custom_css":".geodir-loc-bar .geodir-loc-bar-in {\n    border-radius: 3px;\n    border: none;\n    background: rgba(0, 0, 0, 0.8);\n}\n.geodir-loc-bar {\n    border: none;\n}","_background_image":{"url":"","id":""},"_background_image_tablet":{"url":"","id":""},"_background_image_mobile":{"url":"","id":""},"_background_video_fallback":{"url":"","id":""},"_background_slideshow_gallery":[],"_background_hover_image":{"url":"","id":""},"_background_hover_image_tablet":{"url":"","id":""},"_background_hover_image_mobile":{"url":"","id":""},"_background_hover_video_fallback":{"url":"","id":""},"_background_hover_slideshow_gallery":[]},"elements":[],"isInner":false,"widgetType":"shortcode","elType":"widget"}],"isInner":false,"elType":"column"}],"isInner":false,"elType":"section"},{"id":"43cfdd68","settings":{"background_color":"#FFFFFF","padding":{"unit":"%","top":"0","right":"0","bottom":"0","left":"0","isLinked":false},"content_width":{"unit":"px","size":1000,"sizes":[]},"structure":"20","background_image":{"id":"201","url":"https:\/\/ppldb.com\/realestate-elemntor\/wp-content\/uploads\/sites\/61\/2020\/01\/buildings-near-body-of-water-at-night-1519088-1-1.jpg"},"background_position":"center center","background_attachment":"fixed","background_motion_fx_motion_fx_scrolling":"yes","background_motion_fx_translateY_speed":{"unit":"px","size":8.5,"sizes":[]},"margin":{"unit":"px","top":"-81","right":0,"bottom":"0","left":0,"isLinked":false},"background_image_tablet":{"url":"","id":""},"background_image_mobile":{"url":"","id":""},"background_video_fallback":{"url":"","id":""},"background_slideshow_gallery":[],"background_hover_image":{"url":"","id":""},"background_hover_image_tablet":{"url":"","id":""},"background_hover_image_mobile":{"url":"","id":""},"background_hover_video_fallback":{"url":"","id":""},"background_hover_slideshow_gallery":[],"background_overlay_image":{"url":"","id":""},"background_overlay_image_tablet":{"url":"","id":""},"background_overlay_image_mobile":{"url":"","id":""},"background_overlay_video_fallback":{"url":"","id":""},"background_overlay_slideshow_gallery":[],"background_overlay_hover_image":{"url":"","id":""},"background_overlay_hover_image_tablet":{"url":"","id":""},"background_overlay_hover_image_mobile":{"url":"","id":""},"background_overlay_hover_video_fallback":{"url":"","id":""},"background_overlay_hover_slideshow_gallery":[]},"elements":[{"id":"51bee1de","settings":{"_column_size":50,"_inline_size":null,"content_position":"center","align":"flex-start","background_background":"classic","background_color":"#55970C","border_radius":{"unit":"px","top":"5","right":"5","bottom":"5","left":"5","isLinked":true},"margin":{"unit":"px","top":"10","right":"10","bottom":"10","left":"10","isLinked":true},"padding":{"unit":"px","top":"30","right":"30","bottom":"30","left":"30","isLinked":true},"background_image":{"url":"","id":""},"background_image_tablet":{"url":"","id":""},"background_image_mobile":{"url":"","id":""},"background_video_fallback":{"url":"","id":""},"background_slideshow_gallery":[],"background_hover_image":{"url":"","id":""},"background_hover_image_tablet":{"url":"","id":""},"background_hover_image_mobile":{"url":"","id":""},"background_hover_video_fallback":{"url":"","id":""},"background_hover_slideshow_gallery":[],"background_overlay_image":{"url":"","id":""},"background_overlay_image_tablet":{"url":"","id":""},"background_overlay_image_mobile":{"url":"","id":""},"background_overlay_video_fallback":{"url":"","id":""},"background_overlay_slideshow_gallery":[],"background_overlay_hover_image":{"url":"","id":""},"background_overlay_hover_image_tablet":{"url":"","id":""},"background_overlay_hover_image_mobile":{"url":"","id":""},"background_overlay_hover_video_fallback":{"url":"","id":""},"background_overlay_hover_slideshow_gallery":[]},"elements":[{"id":"64d99f04","settings":{"image":{"id":"199","url":"https:\/\/ppldb.com\/realestate-elemntor\/wp-content\/uploads\/sites\/61\/2020\/01\/001-searching-1.png"},"title_text":" Looking for the new home?","description_text":"\n10 new offers every day. 350 offers on site, trusted by a community of thousands of users.","position":"left","image_size":{"unit":"%","size":19,"sizes":[]},"title_bottom_space":{"unit":"px","size":0,"sizes":[]},"title_color":"#FFFFFF","description_color":"#FFFFFF","description_typography_typography":"custom","description_typography_line_height":{"unit":"em","size":1.600000000000000088817841970012523233890533447265625,"sizes":[]},"_background_image":{"url":"","id":""},"_background_image_tablet":{"url":"","id":""},"_background_image_mobile":{"url":"","id":""},"_background_video_fallback":{"url":"","id":""},"_background_slideshow_gallery":[],"_background_hover_image":{"url":"","id":""},"_background_hover_image_tablet":{"url":"","id":""},"_background_hover_image_mobile":{"url":"","id":""},"_background_hover_video_fallback":{"url":"","id":""},"_background_hover_slideshow_gallery":[]},"elements":[],"isInner":false,"widgetType":"image-box","elType":"widget"}],"isInner":false,"elType":"column"},{"id":"1d7c9e8d","settings":{"_column_size":50,"_inline_size":null,"content_position":"center","align":"flex-start","background_background":"classic","background_color":"#55970C","border_radius":{"unit":"px","top":"5","right":"5","bottom":"5","left":"5","isLinked":true},"margin":{"unit":"px","top":"10","right":"10","bottom":"10","left":"10","isLinked":true},"padding":{"unit":"px","top":"30","right":"30","bottom":"30","left":"30","isLinked":true},"background_image":{"url":"","id":""},"background_image_tablet":{"url":"","id":""},"background_image_mobile":{"url":"","id":""},"background_video_fallback":{"url":"","id":""},"background_slideshow_gallery":[],"background_hover_image":{"url":"","id":""},"background_hover_image_tablet":{"url":"","id":""},"background_hover_image_mobile":{"url":"","id":""},"background_hover_video_fallback":{"url":"","id":""},"background_hover_slideshow_gallery":[],"background_overlay_image":{"url":"","id":""},"background_overlay_image_tablet":{"url":"","id":""},"background_overlay_image_mobile":{"url":"","id":""},"background_overlay_video_fallback":{"url":"","id":""},"background_overlay_slideshow_gallery":[],"background_overlay_hover_image":{"url":"","id":""},"background_overlay_hover_image_tablet":{"url":"","id":""},"background_overlay_hover_image_mobile":{"url":"","id":""},"background_overlay_hover_video_fallback":{"url":"","id":""},"background_overlay_hover_slideshow_gallery":[]},"elements":[{"id":"5d03db16","settings":{"image":{"id":"200","url":"https:\/\/ppldb.com\/realestate-elemntor\/wp-content\/uploads\/sites\/61\/2020\/01\/002-house-1.png"},"title_text":" Want to sell your home?","description_text":"\n10 new offers every day. 350 offers on site, trusted by a community of thousands of users.","position":"left","image_size":{"unit":"%","size":19,"sizes":[]},"title_bottom_space":{"unit":"px","size":0,"sizes":[]},"title_color":"#FFFFFF","description_color":"#FFFFFF","description_typography_typography":"custom","description_typography_line_height":{"unit":"em","size":1.600000000000000088817841970012523233890533447265625,"sizes":[]},"_background_image":{"url":"","id":""},"_background_image_tablet":{"url":"","id":""},"_background_image_mobile":{"url":"","id":""},"_background_video_fallback":{"url":"","id":""},"_background_slideshow_gallery":[],"_background_hover_image":{"url":"","id":""},"_background_hover_image_tablet":{"url":"","id":""},"_background_hover_image_mobile":{"url":"","id":""},"_background_hover_video_fallback":{"url":"","id":""},"_background_hover_slideshow_gallery":[]},"elements":[],"isInner":false,"widgetType":"image-box","elType":"widget"}],"isInner":false,"elType":"column"}],"isInner":false,"elType":"section"},{"id":"74c85c87","settings":{"content_width":{"unit":"px","size":1280,"sizes":[]},"background_color":"#060A20","padding":{"unit":"%","top":"5","right":"0","bottom":"5","left":"0","isLinked":false},"background_image":{"url":"","id":""},"background_image_tablet":{"url":"","id":""},"background_image_mobile":{"url":"","id":""},"background_video_fallback":{"url":"","id":""},"background_slideshow_gallery":[],"background_hover_image":{"url":"","id":""},"background_hover_image_tablet":{"url":"","id":""},"background_hover_image_mobile":{"url":"","id":""},"background_hover_video_fallback":{"url":"","id":""},"background_hover_slideshow_gallery":[],"background_overlay_image":{"url":"","id":""},"background_overlay_image_tablet":{"url":"","id":""},"background_overlay_image_mobile":{"url":"","id":""},"background_overlay_video_fallback":{"url":"","id":""},"background_overlay_slideshow_gallery":[],"background_overlay_hover_image":{"url":"","id":""},"background_overlay_hover_image_tablet":{"url":"","id":""},"background_overlay_hover_image_mobile":{"url":"","id":""},"background_overlay_hover_video_fallback":{"url":"","id":""},"background_overlay_hover_slideshow_gallery":[]},"elements":[{"id":"700a3db3","settings":{"_column_size":100,"_inline_size":null,"background_image":{"url":"","id":""},"background_image_tablet":{"url":"","id":""},"background_image_mobile":{"url":"","id":""},"background_video_fallback":{"url":"","id":""},"background_slideshow_gallery":[],"background_hover_image":{"url":"","id":""},"background_hover_image_tablet":{"url":"","id":""},"background_hover_image_mobile":{"url":"","id":""},"background_hover_video_fallback":{"url":"","id":""},"background_hover_slideshow_gallery":[],"background_overlay_image":{"url":"","id":""},"background_overlay_image_tablet":{"url":"","id":""},"background_overlay_image_mobile":{"url":"","id":""},"background_overlay_video_fallback":{"url":"","id":""},"background_overlay_slideshow_gallery":[],"background_overlay_hover_image":{"url":"","id":""},"background_overlay_hover_image_tablet":{"url":"","id":""},"background_overlay_hover_image_mobile":{"url":"","id":""},"background_overlay_hover_video_fallback":{"url":"","id":""},"background_overlay_hover_slideshow_gallery":[]},"elements":[{"id":"24a4ea09","settings":{"layout":"full_width","background_image":{"url":"","id":""},"background_image_tablet":{"url":"","id":""},"background_image_mobile":{"url":"","id":""},"background_video_fallback":{"url":"","id":""},"background_slideshow_gallery":[],"background_hover_image":{"url":"","id":""},"background_hover_image_tablet":{"url":"","id":""},"background_hover_image_mobile":{"url":"","id":""},"background_hover_video_fallback":{"url":"","id":""},"background_hover_slideshow_gallery":[],"background_overlay_image":{"url":"","id":""},"background_overlay_image_tablet":{"url":"","id":""},"background_overlay_image_mobile":{"url":"","id":""},"background_overlay_video_fallback":{"url":"","id":""},"background_overlay_slideshow_gallery":[],"background_overlay_hover_image":{"url":"","id":""},"background_overlay_hover_image_tablet":{"url":"","id":""},"background_overlay_hover_image_mobile":{"url":"","id":""},"background_overlay_hover_video_fallback":{"url":"","id":""},"background_overlay_hover_slideshow_gallery":[]},"elements":[{"id":"674035eb","settings":{"_column_size":100,"_inline_size":null,"space_between_widgets":10,"background_image":{"url":"","id":""},"background_image_tablet":{"url":"","id":""},"background_image_mobile":{"url":"","id":""},"background_video_fallback":{"url":"","id":""},"background_slideshow_gallery":[],"background_hover_image":{"url":"","id":""},"background_hover_image_tablet":{"url":"","id":""},"background_hover_image_mobile":{"url":"","id":""},"background_hover_video_fallback":{"url":"","id":""},"background_hover_slideshow_gallery":[],"background_overlay_image":{"url":"","id":""},"background_overlay_image_tablet":{"url":"","id":""},"background_overlay_image_mobile":{"url":"","id":""},"background_overlay_video_fallback":{"url":"","id":""},"background_overlay_slideshow_gallery":[],"background_overlay_hover_image":{"url":"","id":""},"background_overlay_hover_image_tablet":{"url":"","id":""},"background_overlay_hover_image_mobile":{"url":"","id":""},"background_overlay_hover_video_fallback":{"url":"","id":""},"background_overlay_hover_slideshow_gallery":[]},"elements":[{"id":"592edb20","settings":{"title":"Why Choose Us","align":"center","title_color":"#000000","typography_typography":"custom","typography_font_family":"Poppins","typography_font_size":{"unit":"px","size":31,"sizes":[]},"typography_font_size_tablet":{"unit":"px","size":30,"sizes":[]},"typography_font_size_mobile":{"unit":"px","size":20,"sizes":[]},"typography_font_weight":"700","align_mobile":"center","typography_text_transform":"uppercase","_background_image":{"url":"","id":""},"_background_image_tablet":{"url":"","id":""},"_background_image_mobile":{"url":"","id":""},"_background_video_fallback":{"url":"","id":""},"_background_slideshow_gallery":[],"_background_hover_image":{"url":"","id":""},"_background_hover_image_tablet":{"url":"","id":""},"_background_hover_image_mobile":{"url":"","id":""},"_background_hover_video_fallback":{"url":"","id":""},"_background_hover_slideshow_gallery":[]},"elements":[],"isInner":false,"widgetType":"heading","elType":"widget"},{"id":"215218db","settings":{"title":"FIND A PROPERTY","align":"center","title_color":"#7A7A7A","typography_typography":"custom","typography_font_family":"Poppins","typography_font_size":{"unit":"px","size":19,"sizes":[]},"typography_font_size_tablet":{"unit":"px","size":30,"sizes":[]},"typography_font_size_mobile":{"unit":"px","size":20,"sizes":[]},"typography_font_weight":"700","align_mobile":"center","_background_image":{"url":"","id":""},"_background_image_tablet":{"url":"","id":""},"_background_image_mobile":{"url":"","id":""},"_background_video_fallback":{"url":"","id":""},"_background_slideshow_gallery":[],"_background_hover_image":{"url":"","id":""},"_background_hover_image_tablet":{"url":"","id":""},"_background_hover_image_mobile":{"url":"","id":""},"_background_hover_video_fallback":{"url":"","id":""},"_background_hover_slideshow_gallery":[]},"elements":[],"isInner":false,"widgetType":"heading","elType":"widget"},{"id":"5f114d9b","settings":{"width":{"unit":"%","size":20,"sizes":[]},"align":"center","text":"Divider","color":"#7A7A7A","weight":{"unit":"px","size":2,"sizes":[]},"align_mobile":"center","icon":{"value":"fas fa-star","library":"fa-solid"},"_background_image":{"url":"","id":""},"_background_image_tablet":{"url":"","id":""},"_background_image_mobile":{"url":"","id":""},"_background_video_fallback":{"url":"","id":""},"_background_slideshow_gallery":[],"_background_hover_image":{"url":"","id":""},"_background_hover_image_tablet":{"url":"","id":""},"_background_hover_image_mobile":{"url":"","id":""},"_background_hover_video_fallback":{"url":"","id":""},"_background_hover_slideshow_gallery":[]},"elements":[],"isInner":false,"widgetType":"divider","elType":"widget"}],"isInner":true,"elType":"column"}],"isInner":true,"elType":"section"},{"id":"e0b221","settings":{"structure":"30","margin":{"unit":"px","top":"30","right":0,"bottom":"0","left":0,"isLinked":false},"background_image":{"url":"","id":""},"background_image_tablet":{"url":"","id":""},"background_image_mobile":{"url":"","id":""},"background_video_fallback":{"url":"","id":""},"background_slideshow_gallery":[],"background_hover_image":{"url":"","id":""},"background_hover_image_tablet":{"url":"","id":""},"background_hover_image_mobile":{"url":"","id":""},"background_hover_video_fallback":{"url":"","id":""},"background_hover_slideshow_gallery":[],"background_overlay_image":{"url":"","id":""},"background_overlay_image_tablet":{"url":"","id":""},"background_overlay_image_mobile":{"url":"","id":""},"background_overlay_video_fallback":{"url":"","id":""},"background_overlay_slideshow_gallery":[],"background_overlay_hover_image":{"url":"","id":""},"background_overlay_hover_image_tablet":{"url":"","id":""},"background_overlay_hover_image_mobile":{"url":"","id":""},"background_overlay_hover_video_fallback":{"url":"","id":""},"background_overlay_hover_slideshow_gallery":[]},"elements":[{"id":"56ca5e77","settings":{"_column_size":33,"_inline_size":null,"space_between_widgets":10,"background_hover_color":"#55970C","border_radius":{"unit":"px","top":"5","right":"5","bottom":"5","left":"5","isLinked":true},"padding":{"unit":"px","top":"25","right":"25","bottom":"25","left":"25","isLinked":true},"background_image":{"url":"","id":""},"background_image_tablet":{"url":"","id":""},"background_image_mobile":{"url":"","id":""},"background_video_fallback":{"url":"","id":""},"background_slideshow_gallery":[],"background_hover_image":{"url":"","id":""},"background_hover_image_tablet":{"url":"","id":""},"background_hover_image_mobile":{"url":"","id":""},"background_hover_video_fallback":{"url":"","id":""},"background_hover_slideshow_gallery":[],"background_overlay_image":{"url":"","id":""},"background_overlay_image_tablet":{"url":"","id":""},"background_overlay_image_mobile":{"url":"","id":""},"background_overlay_video_fallback":{"url":"","id":""},"background_overlay_slideshow_gallery":[],"background_overlay_hover_image":{"url":"","id":""},"background_overlay_hover_image_tablet":{"url":"","id":""},"background_overlay_hover_image_mobile":{"url":"","id":""},"background_overlay_hover_video_fallback":{"url":"","id":""},"background_overlay_hover_slideshow_gallery":[]},"elements":[{"id":"3335d3f7","settings":{"image":{"id":215,"url":"https:\/\/ppldb.com\/realestate-elemntor\/wp-content\/uploads\/sites\/61\/2020\/01\/001-contract-1-1.png"},"title_text":"TRUSTED BY THOUSANDS","description_text":"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.","image_size":{"unit":"%","size":20,"sizes":[]},"title_bottom_space":{"unit":"px","size":0,"sizes":[]},"title_color":"#000000","title_typography_typography":"custom","title_typography_font_family":"Poppins","title_typography_font_size":{"unit":"px","size":21,"sizes":[]},"description_color":"#000000","description_typography_typography":"custom","_background_image":{"url":"","id":""},"_background_image_tablet":{"url":"","id":""},"_background_image_mobile":{"url":"","id":""},"_background_video_fallback":{"url":"","id":""},"_background_slideshow_gallery":[],"_background_hover_image":{"url":"","id":""},"_background_hover_image_tablet":{"url":"","id":""},"_background_hover_image_mobile":{"url":"","id":""},"_background_hover_video_fallback":{"url":"","id":""},"_background_hover_slideshow_gallery":[]},"elements":[],"isInner":false,"widgetType":"image-box","elType":"widget"}],"isInner":true,"elType":"column"},{"id":"1dafe160","settings":{"_column_size":33,"_inline_size":null,"space_between_widgets":10,"background_color":"#55970C","background_hover_color":"#55970C","border_radius":{"unit":"px","top":"5","right":"5","bottom":"5","left":"5","isLinked":true},"padding":{"unit":"px","top":"25","right":"25","bottom":"25","left":"25","isLinked":true},"background_image":{"url":"","id":""},"background_image_tablet":{"url":"","id":""},"background_image_mobile":{"url":"","id":""},"background_video_fallback":{"url":"","id":""},"background_slideshow_gallery":[],"background_hover_image":{"url":"","id":""},"background_hover_image_tablet":{"url":"","id":""},"background_hover_image_mobile":{"url":"","id":""},"background_hover_video_fallback":{"url":"","id":""},"background_hover_slideshow_gallery":[],"background_overlay_image":{"url":"","id":""},"background_overlay_image_tablet":{"url":"","id":""},"background_overlay_image_mobile":{"url":"","id":""},"background_overlay_video_fallback":{"url":"","id":""},"background_overlay_slideshow_gallery":[],"background_overlay_hover_image":{"url":"","id":""},"background_overlay_hover_image_tablet":{"url":"","id":""},"background_overlay_hover_image_mobile":{"url":"","id":""},"background_overlay_hover_video_fallback":{"url":"","id":""},"background_overlay_hover_slideshow_gallery":[]},"elements":[{"id":"6008bb8a","settings":{"image":{"id":216,"url":"https:\/\/ppldb.com\/realestate-elemntor\/wp-content\/uploads\/sites\/61\/2020\/01\/003-property-1-1.png"},"title_text":"WIDE RANGE OF PROPERTIES","description_text":"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.","image_size":{"unit":"%","size":20,"sizes":[]},"title_bottom_space":{"unit":"px","size":0,"sizes":[]},"title_color":"#000000","title_typography_typography":"custom","title_typography_font_family":"Poppins","title_typography_font_size":{"unit":"px","size":21,"sizes":[]},"description_color":"#000000","_background_image":{"url":"","id":""},"_background_image_tablet":{"url":"","id":""},"_background_image_mobile":{"url":"","id":""},"_background_video_fallback":{"url":"","id":""},"_background_slideshow_gallery":[],"_background_hover_image":{"url":"","id":""},"_background_hover_image_tablet":{"url":"","id":""},"_background_hover_image_mobile":{"url":"","id":""},"_background_hover_video_fallback":{"url":"","id":""},"_background_hover_slideshow_gallery":[]},"elements":[],"isInner":false,"widgetType":"image-box","elType":"widget"}],"isInner":true,"elType":"column"},{"id":"1067e6bc","settings":{"_column_size":33,"_inline_size":null,"space_between_widgets":10,"background_hover_color":"#55970C","border_radius":{"unit":"px","top":"5","right":"5","bottom":"5","left":"5","isLinked":true},"padding":{"unit":"px","top":"25","right":"25","bottom":"25","left":"25","isLinked":true},"background_image":{"url":"","id":""},"background_image_tablet":{"url":"","id":""},"background_image_mobile":{"url":"","id":""},"background_video_fallback":{"url":"","id":""},"background_slideshow_gallery":[],"background_hover_image":{"url":"","id":""},"background_hover_image_tablet":{"url":"","id":""},"background_hover_image_mobile":{"url":"","id":""},"background_hover_video_fallback":{"url":"","id":""},"background_hover_slideshow_gallery":[],"background_overlay_image":{"url":"","id":""},"background_overlay_image_tablet":{"url":"","id":""},"background_overlay_image_mobile":{"url":"","id":""},"background_overlay_video_fallback":{"url":"","id":""},"background_overlay_slideshow_gallery":[],"background_overlay_hover_image":{"url":"","id":""},"background_overlay_hover_image_tablet":{"url":"","id":""},"background_overlay_hover_image_mobile":{"url":"","id":""},"background_overlay_hover_video_fallback":{"url":"","id":""},"background_overlay_hover_slideshow_gallery":[]},"elements":[{"id":"5b107432","settings":{"image":{"id":217,"url":"https:\/\/ppldb.com\/realestate-elemntor\/wp-content\/uploads\/sites\/61\/2020\/01\/002-estate-agent-1-1.png"},"title_text":"FINANCING MADE EASY","description_text":"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.","image_size":{"unit":"%","size":20,"sizes":[]},"title_bottom_space":{"unit":"px","size":0,"sizes":[]},"title_color":"#000000","title_typography_typography":"custom","title_typography_font_family":"Poppins","title_typography_font_size":{"unit":"px","size":21,"sizes":[]},"description_color":"#000000","_background_image":{"url":"","id":""},"_background_image_tablet":{"url":"","id":""},"_background_image_mobile":{"url":"","id":""},"_background_video_fallback":{"url":"","id":""},"_background_slideshow_gallery":[],"_background_hover_image":{"url":"","id":""},"_background_hover_image_tablet":{"url":"","id":""},"_background_hover_image_mobile":{"url":"","id":""},"_background_hover_video_fallback":{"url":"","id":""},"_background_hover_slideshow_gallery":[]},"elements":[],"isInner":false,"widgetType":"image-box","elType":"widget"}],"isInner":true,"elType":"column"}],"isInner":true,"elType":"section"}],"isInner":false,"elType":"column"}],"isInner":false,"elType":"section"},{"id":"3efd1a2a","settings":{"background_background":"classic","background_color":"rgba(236, 236, 236, 0.92)","padding":{"unit":"%","top":"05","right":"0","bottom":"5","left":"0","isLinked":false},"content_width":{"unit":"px","size":1280,"sizes":[]},"background_image":{"url":"","id":""},"background_image_tablet":{"url":"","id":""},"background_image_mobile":{"url":"","id":""},"background_video_fallback":{"url":"","id":""},"background_slideshow_gallery":[],"background_hover_image":{"url":"","id":""},"background_hover_image_tablet":{"url":"","id":""},"background_hover_image_mobile":{"url":"","id":""},"background_hover_video_fallback":{"url":"","id":""},"background_hover_slideshow_gallery":[],"background_overlay_image":{"url":"","id":""},"background_overlay_image_tablet":{"url":"","id":""},"background_overlay_image_mobile":{"url":"","id":""},"background_overlay_video_fallback":{"url":"","id":""},"background_overlay_slideshow_gallery":[],"background_overlay_hover_image":{"url":"","id":""},"background_overlay_hover_image_tablet":{"url":"","id":""},"background_overlay_hover_image_mobile":{"url":"","id":""},"background_overlay_hover_video_fallback":{"url":"","id":""},"background_overlay_hover_slideshow_gallery":[]},"elements":[{"id":"c2f03e1","settings":{"_column_size":100,"_inline_size":null,"background_image":{"url":"","id":""},"background_image_tablet":{"url":"","id":""},"background_image_mobile":{"url":"","id":""},"background_video_fallback":{"url":"","id":""},"background_slideshow_gallery":[],"background_hover_image":{"url":"","id":""},"background_hover_image_tablet":{"url":"","id":""},"background_hover_image_mobile":{"url":"","id":""},"background_hover_video_fallback":{"url":"","id":""},"background_hover_slideshow_gallery":[],"background_overlay_image":{"url":"","id":""},"background_overlay_image_tablet":{"url":"","id":""},"background_overlay_image_mobile":{"url":"","id":""},"background_overlay_video_fallback":{"url":"","id":""},"background_overlay_slideshow_gallery":[],"background_overlay_hover_image":{"url":"","id":""},"background_overlay_hover_image_tablet":{"url":"","id":""},"background_overlay_hover_image_mobile":{"url":"","id":""},"background_overlay_hover_video_fallback":{"url":"","id":""},"background_overlay_hover_slideshow_gallery":[]},"elements":[{"id":"e26bbf5","settings":{"layout":"full_width","background_image":{"url":"","id":""},"background_image_tablet":{"url":"","id":""},"background_image_mobile":{"url":"","id":""},"background_video_fallback":{"url":"","id":""},"background_slideshow_gallery":[],"background_hover_image":{"url":"","id":""},"background_hover_image_tablet":{"url":"","id":""},"background_hover_image_mobile":{"url":"","id":""},"background_hover_video_fallback":{"url":"","id":""},"background_hover_slideshow_gallery":[],"background_overlay_image":{"url":"","id":""},"background_overlay_image_tablet":{"url":"","id":""},"background_overlay_image_mobile":{"url":"","id":""},"background_overlay_video_fallback":{"url":"","id":""},"background_overlay_slideshow_gallery":[],"background_overlay_hover_image":{"url":"","id":""},"background_overlay_hover_image_tablet":{"url":"","id":""},"background_overlay_hover_image_mobile":{"url":"","id":""},"background_overlay_hover_video_fallback":{"url":"","id":""},"background_overlay_hover_slideshow_gallery":[]},"elements":[{"id":"5a445ca9","settings":{"_column_size":100,"_inline_size":null,"space_between_widgets":10,"background_image":{"url":"","id":""},"background_image_tablet":{"url":"","id":""},"background_image_mobile":{"url":"","id":""},"background_video_fallback":{"url":"","id":""},"background_slideshow_gallery":[],"background_hover_image":{"url":"","id":""},"background_hover_image_tablet":{"url":"","id":""},"background_hover_image_mobile":{"url":"","id":""},"background_hover_video_fallback":{"url":"","id":""},"background_hover_slideshow_gallery":[],"background_overlay_image":{"url":"","id":""},"background_overlay_image_tablet":{"url":"","id":""},"background_overlay_image_mobile":{"url":"","id":""},"background_overlay_video_fallback":{"url":"","id":""},"background_overlay_slideshow_gallery":[],"background_overlay_hover_image":{"url":"","id":""},"background_overlay_hover_image_tablet":{"url":"","id":""},"background_overlay_hover_image_mobile":{"url":"","id":""},"background_overlay_hover_video_fallback":{"url":"","id":""},"background_overlay_hover_slideshow_gallery":[]},"elements":[{"id":"4f4ab858","settings":{"title":"FEATURE PROPERTIES","align":"center","title_color":"#55970C","typography_typography":"custom","typography_font_family":"Poppins","typography_font_size":{"unit":"px","size":31,"sizes":[]},"typography_font_size_tablet":{"unit":"px","size":30,"sizes":[]},"typography_font_size_mobile":{"unit":"px","size":20,"sizes":[]},"typography_font_weight":"700","align_mobile":"center","_background_image":{"url":"","id":""},"_background_image_tablet":{"url":"","id":""},"_background_image_mobile":{"url":"","id":""},"_background_video_fallback":{"url":"","id":""},"_background_slideshow_gallery":[],"_background_hover_image":{"url":"","id":""},"_background_hover_image_tablet":{"url":"","id":""},"_background_hover_image_mobile":{"url":"","id":""},"_background_hover_video_fallback":{"url":"","id":""},"_background_hover_slideshow_gallery":[]},"elements":[],"isInner":false,"widgetType":"heading","elType":"widget"},{"id":"268a9453","settings":{"title":"FIND A PROPERTY","align":"center","title_color":"#626262","typography_typography":"custom","typography_font_family":"Poppins","typography_font_size":{"unit":"px","size":19,"sizes":[]},"typography_font_size_tablet":{"unit":"px","size":30,"sizes":[]},"typography_font_size_mobile":{"unit":"px","size":20,"sizes":[]},"typography_font_weight":"700","align_mobile":"center","_background_image":{"url":"","id":""},"_background_image_tablet":{"url":"","id":""},"_background_image_mobile":{"url":"","id":""},"_background_video_fallback":{"url":"","id":""},"_background_slideshow_gallery":[],"_background_hover_image":{"url":"","id":""},"_background_hover_image_tablet":{"url":"","id":""},"_background_hover_image_mobile":{"url":"","id":""},"_background_hover_video_fallback":{"url":"","id":""},"_background_hover_slideshow_gallery":[]},"elements":[],"isInner":false,"widgetType":"heading","elType":"widget"},{"id":"7f6e2b4a","settings":{"width":{"unit":"%","size":20,"sizes":[]},"align":"center","text":"Divider","color":"#7A7A7A","weight":{"unit":"px","size":2,"sizes":[]},"align_mobile":"center","icon":{"value":"fas fa-star","library":"fa-solid"},"_background_image":{"url":"","id":""},"_background_image_tablet":{"url":"","id":""},"_background_image_mobile":{"url":"","id":""},"_background_video_fallback":{"url":"","id":""},"_background_slideshow_gallery":[],"_background_hover_image":{"url":"","id":""},"_background_hover_image_tablet":{"url":"","id":""},"_background_hover_image_mobile":{"url":"","id":""},"_background_hover_video_fallback":{"url":"","id":""},"_background_hover_slideshow_gallery":[]},"elements":[],"isInner":false,"widgetType":"divider","elType":"widget"}],"isInner":true,"elType":"column"}],"isInner":true,"elType":"section"},{"id":"2c82ffba","settings":{"animation":"fadeIn","layout":"full_width","padding":{"unit":"px","top":"15","right":"0","bottom":"0","left":"0","isLinked":false},"background_image":{"url":"","id":""},"background_image_tablet":{"url":"","id":""},"background_image_mobile":{"url":"","id":""},"background_video_fallback":{"url":"","id":""},"background_slideshow_gallery":[],"background_hover_image":{"url":"","id":""},"background_hover_image_tablet":{"url":"","id":""},"background_hover_image_mobile":{"url":"","id":""},"background_hover_video_fallback":{"url":"","id":""},"background_hover_slideshow_gallery":[],"background_overlay_image":{"url":"","id":""},"background_overlay_image_tablet":{"url":"","id":""},"background_overlay_image_mobile":{"url":"","id":""},"background_overlay_video_fallback":{"url":"","id":""},"background_overlay_slideshow_gallery":[],"background_overlay_hover_image":{"url":"","id":""},"background_overlay_hover_image_tablet":{"url":"","id":""},"background_overlay_hover_image_mobile":{"url":"","id":""},"background_overlay_hover_video_fallback":{"url":"","id":""},"background_overlay_hover_slideshow_gallery":[]},"elements":[{"id":"5d0887e3","settings":{"_column_size":100,"_inline_size":null,"background_image":{"url":"","id":""},"background_image_tablet":{"url":"","id":""},"background_image_mobile":{"url":"","id":""},"background_video_fallback":{"url":"","id":""},"background_slideshow_gallery":[],"background_hover_image":{"url":"","id":""},"background_hover_image_tablet":{"url":"","id":""},"background_hover_image_mobile":{"url":"","id":""},"background_hover_video_fallback":{"url":"","id":""},"background_hover_slideshow_gallery":[],"background_overlay_image":{"url":"","id":""},"background_overlay_image_tablet":{"url":"","id":""},"background_overlay_image_mobile":{"url":"","id":""},"background_overlay_video_fallback":{"url":"","id":""},"background_overlay_slideshow_gallery":[],"background_overlay_hover_image":{"url":"","id":""},"background_overlay_hover_image_tablet":{"url":"","id":""},"background_overlay_hover_image_mobile":{"url":"","id":""},"background_overlay_hover_video_fallback":{"url":"","id":""},"background_overlay_hover_slideshow_gallery":[]},"elements":[{"id":"283f7fce","settings":{"shortcode":"[gd_listings post_type=\"gd_place\" related_to=\"default_category\" sort_by=\"distance_asc\" layout=\"4\" post_limit=\"4\" category=\"0\"]\n","_element_id":"feature-listing","_css_classes":"feature-listing","custom_css":"#feature-listing .geodir-post-content-container {\n    display: none;\n}\n#feature-listing ul.geodir-category-list-view.geodir-gridview>li {\n    background: #fff;\n    padding: 0px !important;\n    border: 1px solid #ccc;\n    border-radius: 5px;\n}\n#feature-listing ul.geodir-category-list-view.geodir-gridview>li:hover {\n   \n    box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.3);\n    \n}\n#feature-listing .gd-badge-meta.gd-badge-alignleft {\ndisplay: none;\n}\n#feature-listing .gd-list-item-right {\n    padding: 20px;\n}\n#feature-listing .geodir-entry-title {\n    font-size: 18px;\n}\n#feature-listing .geodir-post-fav {\n    position: absolute;\n    top: -18px;\n    font-size: 30px !important;\n    right: 30px;\n}\n\n#feature-listing .geodir-output-location {\n    font-size: 14px;\n}\n\n","_background_image":{"url":"","id":""},"_background_image_tablet":{"url":"","id":""},"_background_image_mobile":{"url":"","id":""},"_background_video_fallback":{"url":"","id":""},"_background_slideshow_gallery":[],"_background_hover_image":{"url":"","id":""},"_background_hover_image_tablet":{"url":"","id":""},"_background_hover_image_mobile":{"url":"","id":""},"_background_hover_video_fallback":{"url":"","id":""},"_background_hover_slideshow_gallery":[]},"elements":[],"isInner":false,"widgetType":"shortcode","elType":"widget"},{"id":"116dabb4","settings":{"text":"View More","align":"center","align_mobile":"center","typography_typography":"custom","typography_font_family":"Poppins","button_text_color":"#55970C","background_color":"rgba(255, 255, 255, 0)","border_border":"solid","border_width":{"unit":"px","top":"2","right":"2","bottom":"2","left":"2","isLinked":true},"border_radius":{"unit":"px","top":"3","right":"3","bottom":"3","left":"3","isLinked":true},"selected_icon":{"value":"","library":""},"_background_image":{"url":"","id":""},"_background_image_tablet":{"url":"","id":""},"_background_image_mobile":{"url":"","id":""},"_background_video_fallback":{"url":"","id":""},"_background_slideshow_gallery":[],"_background_hover_image":{"url":"","id":""},"_background_hover_image_tablet":{"url":"","id":""},"_background_hover_image_mobile":{"url":"","id":""},"_background_hover_video_fallback":{"url":"","id":""},"_background_hover_slideshow_gallery":[]},"elements":[],"isInner":false,"widgetType":"button","elType":"widget"}],"isInner":true,"elType":"column"}],"isInner":true,"elType":"section"}],"isInner":false,"elType":"column"}],"isInner":false,"elType":"section"},{"id":"33e9228c","settings":{"content_width":{"unit":"px","size":1280,"sizes":[]},"background_background":"classic","background_color":"#000000","padding":{"unit":"%","top":"5","right":"0","bottom":"5","left":"0","isLinked":false},"background_image":{"url":"","id":""},"background_image_tablet":{"url":"","id":""},"background_image_mobile":{"url":"","id":""},"background_video_fallback":{"url":"","id":""},"background_slideshow_gallery":[],"background_hover_image":{"url":"","id":""},"background_hover_image_tablet":{"url":"","id":""},"background_hover_image_mobile":{"url":"","id":""},"background_hover_video_fallback":{"url":"","id":""},"background_hover_slideshow_gallery":[],"background_overlay_image":{"url":"","id":""},"background_overlay_image_tablet":{"url":"","id":""},"background_overlay_image_mobile":{"url":"","id":""},"background_overlay_video_fallback":{"url":"","id":""},"background_overlay_slideshow_gallery":[],"background_overlay_hover_image":{"url":"","id":""},"background_overlay_hover_image_tablet":{"url":"","id":""},"background_overlay_hover_image_mobile":{"url":"","id":""},"background_overlay_hover_video_fallback":{"url":"","id":""},"background_overlay_hover_slideshow_gallery":[]},"elements":[{"id":"4d5f3ea8","settings":{"_column_size":100,"_inline_size":null,"background_image":{"url":"","id":""},"background_image_tablet":{"url":"","id":""},"background_image_mobile":{"url":"","id":""},"background_video_fallback":{"url":"","id":""},"background_slideshow_gallery":[],"background_hover_image":{"url":"","id":""},"background_hover_image_tablet":{"url":"","id":""},"background_hover_image_mobile":{"url":"","id":""},"background_hover_video_fallback":{"url":"","id":""},"background_hover_slideshow_gallery":[],"background_overlay_image":{"url":"","id":""},"background_overlay_image_tablet":{"url":"","id":""},"background_overlay_image_mobile":{"url":"","id":""},"background_overlay_video_fallback":{"url":"","id":""},"background_overlay_slideshow_gallery":[],"background_overlay_hover_image":{"url":"","id":""},"background_overlay_hover_image_tablet":{"url":"","id":""},"background_overlay_hover_image_mobile":{"url":"","id":""},"background_overlay_hover_video_fallback":{"url":"","id":""},"background_overlay_hover_slideshow_gallery":[]},"elements":[{"id":"5c28337a","settings":{"title":"Become a Real Estate Agent","align":"center","title_color":"#FFFFFF","typography_typography":"custom","typography_font_family":"Poppins","typography_font_size":{"unit":"px","size":31,"sizes":[]},"typography_font_size_tablet":{"unit":"px","size":30,"sizes":[]},"typography_font_size_mobile":{"unit":"px","size":20,"sizes":[]},"typography_font_weight":"700","align_mobile":"center","typography_text_transform":"uppercase","_background_image":{"url":"","id":""},"_background_image_tablet":{"url":"","id":""},"_background_image_mobile":{"url":"","id":""},"_background_video_fallback":{"url":"","id":""},"_background_slideshow_gallery":[],"_background_hover_image":{"url":"","id":""},"_background_hover_image_tablet":{"url":"","id":""},"_background_hover_image_mobile":{"url":"","id":""},"_background_hover_video_fallback":{"url":"","id":""},"_background_hover_slideshow_gallery":[]},"elements":[],"isInner":false,"widgetType":"heading","elType":"widget"},{"id":"745ed044","settings":{"text":"Be an Agent","align":"center","align_mobile":"center","size":"xl","typography_typography":"custom","typography_font_family":"Poppins","button_text_color":"#FFFFFF","background_color":"rgba(255, 255, 255, 0)","border_border":"solid","border_width":{"unit":"px","top":"2","right":"2","bottom":"2","left":"2","isLinked":true},"border_color":"#FFFFFF","border_radius":{"unit":"px","top":"3","right":"3","bottom":"3","left":"3","isLinked":true},"selected_icon":{"value":"","library":""},"_background_image":{"url":"","id":""},"_background_image_tablet":{"url":"","id":""},"_background_image_mobile":{"url":"","id":""},"_background_video_fallback":{"url":"","id":""},"_background_slideshow_gallery":[],"_background_hover_image":{"url":"","id":""},"_background_hover_image_tablet":{"url":"","id":""},"_background_hover_image_mobile":{"url":"","id":""},"_background_hover_video_fallback":{"url":"","id":""},"_background_hover_slideshow_gallery":[]},"elements":[],"isInner":false,"widgetType":"button","elType":"widget"}],"isInner":false,"elType":"column"}],"isInner":false,"elType":"section"},{"id":"5bf0b5ee","settings":{"content_width":{"unit":"px","size":1280,"sizes":[]},"background_background":"classic","background_color":"#FFFFFF","padding":{"unit":"%","top":"8","right":"0","bottom":"8","left":"0","isLinked":false},"background_image":{"url":"","id":""},"background_image_tablet":{"url":"","id":""},"background_image_mobile":{"url":"","id":""},"background_video_fallback":{"url":"","id":""},"background_slideshow_gallery":[],"background_hover_image":{"url":"","id":""},"background_hover_image_tablet":{"url":"","id":""},"background_hover_image_mobile":{"url":"","id":""},"background_hover_video_fallback":{"url":"","id":""},"background_hover_slideshow_gallery":[],"background_overlay_image":{"url":"","id":""},"background_overlay_image_tablet":{"url":"","id":""},"background_overlay_image_mobile":{"url":"","id":""},"background_overlay_video_fallback":{"url":"","id":""},"background_overlay_slideshow_gallery":[],"background_overlay_hover_image":{"url":"","id":""},"background_overlay_hover_image_tablet":{"url":"","id":""},"background_overlay_hover_image_mobile":{"url":"","id":""},"background_overlay_hover_video_fallback":{"url":"","id":""},"background_overlay_hover_slideshow_gallery":[]},"elements":[{"id":"f821b77","settings":{"_column_size":100,"_inline_size":null,"animation":"fadeInUp","animation_duration":"fast","background_image":{"url":"","id":""},"background_image_tablet":{"url":"","id":""},"background_image_mobile":{"url":"","id":""},"background_video_fallback":{"url":"","id":""},"background_slideshow_gallery":[],"background_hover_image":{"url":"","id":""},"background_hover_image_tablet":{"url":"","id":""},"background_hover_image_mobile":{"url":"","id":""},"background_hover_video_fallback":{"url":"","id":""},"background_hover_slideshow_gallery":[],"background_overlay_image":{"url":"","id":""},"background_overlay_image_tablet":{"url":"","id":""},"background_overlay_image_mobile":{"url":"","id":""},"background_overlay_video_fallback":{"url":"","id":""},"background_overlay_slideshow_gallery":[],"background_overlay_hover_image":{"url":"","id":""},"background_overlay_hover_image_tablet":{"url":"","id":""},"background_overlay_hover_image_mobile":{"url":"","id":""},"background_overlay_hover_video_fallback":{"url":"","id":""},"background_overlay_hover_slideshow_gallery":[]},"elements":[{"id":"20073faf","settings":{"layout":"full_width","background_image":{"url":"","id":""},"background_image_tablet":{"url":"","id":""},"background_image_mobile":{"url":"","id":""},"background_video_fallback":{"url":"","id":""},"background_slideshow_gallery":[],"background_hover_image":{"url":"","id":""},"background_hover_image_tablet":{"url":"","id":""},"background_hover_image_mobile":{"url":"","id":""},"background_hover_video_fallback":{"url":"","id":""},"background_hover_slideshow_gallery":[],"background_overlay_image":{"url":"","id":""},"background_overlay_image_tablet":{"url":"","id":""},"background_overlay_image_mobile":{"url":"","id":""},"background_overlay_video_fallback":{"url":"","id":""},"background_overlay_slideshow_gallery":[],"background_overlay_hover_image":{"url":"","id":""},"background_overlay_hover_image_tablet":{"url":"","id":""},"background_overlay_hover_image_mobile":{"url":"","id":""},"background_overlay_hover_video_fallback":{"url":"","id":""},"background_overlay_hover_slideshow_gallery":[]},"elements":[{"id":"5efa92ae","settings":{"_column_size":100,"_inline_size":null,"space_between_widgets":10,"background_image":{"url":"","id":""},"background_image_tablet":{"url":"","id":""},"background_image_mobile":{"url":"","id":""},"background_video_fallback":{"url":"","id":""},"background_slideshow_gallery":[],"background_hover_image":{"url":"","id":""},"background_hover_image_tablet":{"url":"","id":""},"background_hover_image_mobile":{"url":"","id":""},"background_hover_video_fallback":{"url":"","id":""},"background_hover_slideshow_gallery":[],"background_overlay_image":{"url":"","id":""},"background_overlay_image_tablet":{"url":"","id":""},"background_overlay_image_mobile":{"url":"","id":""},"background_overlay_video_fallback":{"url":"","id":""},"background_overlay_slideshow_gallery":[],"background_overlay_hover_image":{"url":"","id":""},"background_overlay_hover_image_tablet":{"url":"","id":""},"background_overlay_hover_image_mobile":{"url":"","id":""},"background_overlay_hover_video_fallback":{"url":"","id":""},"background_overlay_hover_slideshow_gallery":[]},"elements":[{"id":"47c23eae","settings":{"title":"Feature Events","align":"center","title_color":"#55970C","typography_typography":"custom","typography_font_family":"Poppins","typography_font_size":{"unit":"px","size":31,"sizes":[]},"typography_font_size_tablet":{"unit":"px","size":30,"sizes":[]},"typography_font_size_mobile":{"unit":"px","size":20,"sizes":[]},"typography_font_weight":"700","align_mobile":"center","typography_text_transform":"uppercase","_background_image":{"url":"","id":""},"_background_image_tablet":{"url":"","id":""},"_background_image_mobile":{"url":"","id":""},"_background_video_fallback":{"url":"","id":""},"_background_slideshow_gallery":[],"_background_hover_image":{"url":"","id":""},"_background_hover_image_tablet":{"url":"","id":""},"_background_hover_image_mobile":{"url":"","id":""},"_background_hover_video_fallback":{"url":"","id":""},"_background_hover_slideshow_gallery":[]},"elements":[],"isInner":false,"widgetType":"heading","elType":"widget"},{"id":"471e4ac2","settings":{"title":"FIND A PROPERTY","align":"center","title_color":"#626262","typography_typography":"custom","typography_font_family":"Poppins","typography_font_size":{"unit":"px","size":19,"sizes":[]},"typography_font_size_tablet":{"unit":"px","size":30,"sizes":[]},"typography_font_size_mobile":{"unit":"px","size":20,"sizes":[]},"typography_font_weight":"700","align_mobile":"center","_background_image":{"url":"","id":""},"_background_image_tablet":{"url":"","id":""},"_background_image_mobile":{"url":"","id":""},"_background_video_fallback":{"url":"","id":""},"_background_slideshow_gallery":[],"_background_hover_image":{"url":"","id":""},"_background_hover_image_tablet":{"url":"","id":""},"_background_hover_image_mobile":{"url":"","id":""},"_background_hover_video_fallback":{"url":"","id":""},"_background_hover_slideshow_gallery":[]},"elements":[],"isInner":false,"widgetType":"heading","elType":"widget"},{"id":"2cf7f9e3","settings":{"width":{"unit":"%","size":20,"sizes":[]},"align":"center","text":"Divider","color":"#7A7A7A","weight":{"unit":"px","size":2,"sizes":[]},"align_mobile":"center","icon":{"value":"fas fa-star","library":"fa-solid"},"_background_image":{"url":"","id":""},"_background_image_tablet":{"url":"","id":""},"_background_image_mobile":{"url":"","id":""},"_background_video_fallback":{"url":"","id":""},"_background_slideshow_gallery":[],"_background_hover_image":{"url":"","id":""},"_background_hover_image_tablet":{"url":"","id":""},"_background_hover_image_mobile":{"url":"","id":""},"_background_hover_video_fallback":{"url":"","id":""},"_background_hover_slideshow_gallery":[]},"elements":[],"isInner":false,"widgetType":"divider","elType":"widget"}],"isInner":true,"elType":"column"}],"isInner":true,"elType":"section"},{"id":"16c1b2a8","settings":{"layout":"full_width","padding":{"unit":"px","top":"50","right":"0","bottom":"0","left":"0","isLinked":false},"animation":"fadeInUp","animation_duration":"fast","background_image":{"url":"","id":""},"background_image_tablet":{"url":"","id":""},"background_image_mobile":{"url":"","id":""},"background_video_fallback":{"url":"","id":""},"background_slideshow_gallery":[],"background_hover_image":{"url":"","id":""},"background_hover_image_tablet":{"url":"","id":""},"background_hover_image_mobile":{"url":"","id":""},"background_hover_video_fallback":{"url":"","id":""},"background_hover_slideshow_gallery":[],"background_overlay_image":{"url":"","id":""},"background_overlay_image_tablet":{"url":"","id":""},"background_overlay_image_mobile":{"url":"","id":""},"background_overlay_video_fallback":{"url":"","id":""},"background_overlay_slideshow_gallery":[],"background_overlay_hover_image":{"url":"","id":""},"background_overlay_hover_image_tablet":{"url":"","id":""},"background_overlay_hover_image_mobile":{"url":"","id":""},"background_overlay_hover_video_fallback":{"url":"","id":""},"background_overlay_hover_slideshow_gallery":[]},"elements":[{"id":"7d91ca1f","settings":{"_column_size":100,"_inline_size":null,"space_between_widgets":10,"background_image":{"url":"","id":""},"background_image_tablet":{"url":"","id":""},"background_image_mobile":{"url":"","id":""},"background_video_fallback":{"url":"","id":""},"background_slideshow_gallery":[],"background_hover_image":{"url":"","id":""},"background_hover_image_tablet":{"url":"","id":""},"background_hover_image_mobile":{"url":"","id":""},"background_hover_video_fallback":{"url":"","id":""},"background_hover_slideshow_gallery":[],"background_overlay_image":{"url":"","id":""},"background_overlay_image_tablet":{"url":"","id":""},"background_overlay_image_mobile":{"url":"","id":""},"background_overlay_video_fallback":{"url":"","id":""},"background_overlay_slideshow_gallery":[],"background_overlay_hover_image":{"url":"","id":""},"background_overlay_hover_image_tablet":{"url":"","id":""},"background_overlay_hover_image_mobile":{"url":"","id":""},"background_overlay_hover_video_fallback":{"url":"","id":""},"background_overlay_hover_slideshow_gallery":[]},"elements":[{"id":"154b525f","settings":{"_skin":"cards","classic_posts_per_page":3,"classic_meta_separator":"\/\/\/","classic_read_more_text":"Read More \u00bb","cards_posts_per_page":3,"cards_meta_separator":"\u2022","cards_read_more_text":"Read More \u00bb","full_content_meta_separator":"\/\/\/","posts_post_type":"post","pagination_page_limit":"5","pagination_prev_label":"&laquo; Previous","pagination_next_label":"Next &raquo;","_background_image":{"url":"","id":""},"_background_image_tablet":{"url":"","id":""},"_background_image_mobile":{"url":"","id":""},"_background_video_fallback":{"url":"","id":""},"_background_slideshow_gallery":[],"_background_hover_image":{"url":"","id":""},"_background_hover_image_tablet":{"url":"","id":""},"_background_hover_image_mobile":{"url":"","id":""},"_background_hover_video_fallback":{"url":"","id":""},"_background_hover_slideshow_gallery":[]},"elements":[],"isInner":false,"widgetType":"posts","elType":"widget"}],"isInner":true,"elType":"column"}],"isInner":true,"elType":"section"},{"id":"2136af55","settings":{"text":"View More","align":"center","align_mobile":"center","typography_typography":"custom","typography_font_family":"Poppins","button_text_color":"#55970C","background_color":"rgba(255, 255, 255, 0)","border_border":"solid","border_width":{"unit":"px","top":"2","right":"2","bottom":"2","left":"2","isLinked":true},"border_radius":{"unit":"px","top":"3","right":"3","bottom":"3","left":"3","isLinked":true},"_margin":{"unit":"px","top":"33","right":"0","bottom":"0","left":"0","isLinked":false},"selected_icon":{"value":"","library":""},"_background_image":{"url":"","id":""},"_background_image_tablet":{"url":"","id":""},"_background_image_mobile":{"url":"","id":""},"_background_video_fallback":{"url":"","id":""},"_background_slideshow_gallery":[],"_background_hover_image":{"url":"","id":""},"_background_hover_image_tablet":{"url":"","id":""},"_background_hover_image_mobile":{"url":"","id":""},"_background_hover_video_fallback":{"url":"","id":""},"_background_hover_slideshow_gallery":[]},"elements":[],"isInner":false,"widgetType":"button","elType":"widget"}],"isInner":false,"elType":"column"}],"isInner":false,"elType":"section"}]}', true );
	}

	/**
	 * The AJAX template response needs to be wrapped correctly.
	 *
	 * @param $data
	 * @param $key
	 *
	 * @return array
	 */
	public static function template_response( $data, $key ) {

		$response = array();

		if ( ! empty( $data['content'] ) ) {
			$response['responses'][ $key ] = array(
				'success' => true,
				'code'    => 200,
				'data'    => array( 'content' => $data['content'] )
			);
		}

		return $response;
	}

	/**
	 * Allow to filter the archive itme template content if being edited by elementor.
	 *
	 * @param $content
	 * @param $original_content
	 * @param $page_id
	 *
	 * @return mixed
	 */
	public static function archive_item_template_content( $content, $original_content, $page_id ) {

		if ( ! $original_content && $page_id && self::is_elementor( $page_id ) ) {
			$original_content = $content = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $page_id );
		} else {
			$original_content = $content;
		}

		return $original_content;
	}

	/**
	 * Check if a page is being edited by elementor.
	 *
	 * @return bool
	 */
	public static function is_elementor( $post_id ) {
		return \Elementor\Plugin::$instance->db->is_built_with_elementor( $post_id );
	}

	/**
	 * Add the flex slider JS if in preview.
	 */
	public static function enqueue_scripts() {
		if ( self::is_elementor_preview() ) {
			GeoDir_Frontend_Scripts::enqueue_script( 'jquery-flexslider' );
		}
	}

	/**
	 * Check if we are currently in an Elementor preview.
	 *
	 * @return bool
	 */
	public static function is_elementor_preview() {
		return isset( $_REQUEST['elementor-preview'] ) ? true : false;
	}

	/**
	 * Check if a GD archive override is in place.
	 *
	 * @param string $template
	 *
	 * @return bool
	 */
	public static function is_template_override( $template = '' ) {
		$result    = false;
		$type      = '';
		$page_type = '';

		// set post_type
		if ( geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) ) {
			$post_type = geodir_get_current_posttype();

			if ( geodir_is_page( 'post_type' ) ) {
				$type = $post_type . "_archive";
			} elseif ( $tax = get_query_var( 'taxonomy' ) ) {
				$type = $tax;
			}
			$page_type = 'archive';
		} elseif ( geodir_is_page( 'single' ) ) {
			$type      = geodir_get_current_posttype();
			$page_type = 'single';
		} elseif ( geodir_is_page( 'search' ) ) {
			$type      = 'search';
			$page_type = 'archive';
		}

		if ( $type && $conditions = get_option( 'elementor_pro_theme_builder_conditions' ) ) {
			if ( $page_type == 'archive' && ! empty( $conditions['archive'] ) ) {
				foreach ( $conditions['archive'] as $archive_conditions ) {
					foreach ( $archive_conditions as $archive_condition ) {
						if ( stripos( strrev( $archive_condition ), strrev( $type ) ) === 0 ) {
							$result = true;
							break 2;
						}
					}
				}
			} elseif ( $page_type == 'single' && ! empty( $conditions['single'] ) ) {
				foreach ( $conditions['single'] as $archive_conditions ) {
					foreach ( $archive_conditions as $archive_condition ) {
						if ( stripos( strrev( $archive_condition ), strrev( $type ) ) === 0 ) {
							$result = true;
							break 2;
						}
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Check the current output is inside a elementor preview.
	 *
	 * @since 2.0.58
	 * @return bool
	 */
	public static function is_elementor_view() {
		$result = false;
		if ( isset( $_REQUEST['elementor-preview'] ) || ( is_admin() && isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'elementor' ) || ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'elementor_ajax' ) ) {
			$result = true;
		}

		return $result;
	}

	/**
	 * Filter the archive title element.
	 *
	 * @since 2.0.0.71
	 *
	 * @param string $page_title The page title.
	 *
	 * @return string Filtered page title.
	 */
	public static function get_page_title( $page_title ) {
		$title = GeoDir_SEO::set_meta();

		if ( $title ) {
			$page_title = $title;
		}

		return $page_title;
	}
}