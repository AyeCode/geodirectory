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


		/*
		 * Elementor Pro features below here
		 */
		if(defined( 'ELEMENTOR_PRO_VERSION' )){
			// doc template types
			add_action( 'elementor/documents/register', array( __CLASS__, 'register_template_types' ) );

			// register template conditions
			add_action( 'elementor/theme/register_conditions', array( __CLASS__, 'register_template_conditions' ) );

			// register skins
			add_action('elementor/widgets/widgets_registered',array( __CLASS__,'add_archive_item_skins'));
		}


	}

	public static function add_archive_item_skins(){

		require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/elementor/class-geodir-elementor-skin-custom.php' );

	}

	/**
	 * Register our own template conditions.
	 *
	 * @param Conditions_Manager $conditions_manager
	 */
	public static function register_template_conditions( $conditions_manager ) {
		// Single conditions
		$conditions_single = new GeoDir_Elementor_Template_Conditions_Single();
		$conditions_manager->get_condition( 'general' )->register_sub_condition( $conditions_single );

		// Archive Conditions
		$conditions_archive = new GeoDir_Elementor_Template_Conditions_Archive();
		$conditions_manager->get_condition( 'general' )->register_sub_condition( $conditions_archive );

		// Archive item, show message that no Conditions are needed.
		$conditions_archive_item = new GeoDir_Elementor_Template_Conditions_Archive_item();
		$conditions_manager->get_condition( 'general' )->register_sub_condition( $conditions_archive_item );
	}

	/**
	 * Register our single and archive template types.
	 *
	 * @param $documents_manager
	 */
	public static function register_template_types($documents_manager){
		$docs_types = [
			'geodirectory' => 'GeoDir_Elementor_Template_Single',
			'geodirectory-archive' => 'GeoDir_Elementor_Template_Archive',
			'geodirectory-archive-item' => 'GeoDir_Elementor_Template_Archive_Item',
		];

		foreach ( $docs_types as $type => $class_name ) {
			$documents_manager->register_document_type( $type, $class_name );
		}
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

		if(geodir_is_page('single')){
			$type = $widget->get_name();
			if ($type  === 'image'  ) {
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

			}elseif($type  === 'gallery'){
				$settings = $widget->get_settings();
				$gallery_type = isset($settings['gallery_type']) ? $settings['gallery_type'] : '';
				$is_gd_gallery = false;
				if ( $gallery_type == 'single' && ! empty( $settings['__dynamic__']['gallery'] ) && strpos( $settings['__dynamic__']['gallery'], 'name="gd-gallery"' ) !== false ) {
					$is_gd_gallery = true;

				}elseif( $gallery_type == 'multiple' && !empty($settings['galleries'])){

					foreach($settings['galleries'] as $gallery){

						if(! empty( $gallery['__dynamic__']['multiple_gallery'] ) && strpos( $gallery['__dynamic__']['multiple_gallery'], 'name="gd-gallery"' ) !== false ){
							$is_gd_gallery = true;
						}
					}
				}

				if($is_gd_gallery){
					$html = self::render_pro_gallery( $widget,$settings );
				}

			}elseif($type  === 'image-carousel'){
				$settings = $widget->get_settings();
				$is_gd_gallery = false;
				if ( ! empty( $settings['__dynamic__']['carousel'] ) && strpos( $settings['__dynamic__']['carousel'], 'name="gd-gallery"' ) !== false ) {
					$is_gd_gallery = true;
				}
				if($is_gd_gallery){
					$html = self::render_pro_carousel( $widget,$settings );
				}

			}
		}


		return $html;
	}

	/**
	 * Render Elementor Pro carousel.
	 *
	 * @param $widget
	 * @param $settings
	 *
	 * @return string
	 */
	public static function render_pro_carousel($widget,$settings){
		global $gd_post;
		ob_start();

		$key = self::get_gallery_key($settings['__dynamic__']['carousel']);
		$settings['slides'] = GeoDir_Media::get_attachments_by_type( $gd_post->ID, $key );
		$slides_count = count( $settings['slides'] );
		$link_to = !empty($settings['link_to']) && $settings['link_to']!='none' ? esc_attr($settings['link_to']) : '';
		$caption_type = !empty($settings['caption_type']) ? esc_attr($settings['caption_type']) : '';
		if(!empty($settings['slides'])){
			?>
			<div class="elementor-image-carousel-wrapper swiper-container" dir="ltr">
				<div class="elementor-image-carousel swiper-wrapper">
						<?php
						$widget->slide_prints_count = 0;
							foreach($settings['slides'] as $slide){
								$widget->slide_prints_count++;
								$image_src = geodir_get_image_src( $slide, $settings['thumbnail_size'] );



								?>
								<div class="swiper-slide">
									<?php
									if($link_to){
										if($link_to=='file'){
											$image_src_full = geodir_get_image_src( $slide, 'full' );
											echo '<a data-elementor-open-lightbox="yes" data-elementor-lightbox-slideshow="'.$widget->get_id().'" data-elementor-lightbox-title="'.esc_attr($slide->title).'" data-elementor-lightbox-description="'.esc_attr($slide->caption).'" href="'.$image_src_full .'">';
										}else{
											$href = isset($settings['link']['url']) ? esc_url_raw($settings['link']['url']) :'';
											$link = '<a href="'.$href .'" ';
											if(isset($settings['link']['nofollow']) && $settings['link']['nofollow']=='on'){
												$link .= ' target="_blank" ';
											}
											if(isset($settings['link']['is_external']) && $settings['link']['is_external']=='on'){
												$link .= ' rel="nofollow" ';
											}
											$link .= ' >';
											echo $link;
										}
									}
									?>
									<figure class="swiper-slide-inner">
										<img class="swiper-slide-image"
										     src="<?php echo esc_url_raw($image_src);?>"
										     alt=""/>
										<?php
										if($caption_type == 'title' || $caption_type=='caption'){
											$caption = $slide->{$caption_type};
											if($caption){
												echo '<figcaption class="elementor-image-carousel-caption">'.esc_attr($caption).'</figcaption>';
											}
										}

										?>
									</figure>
									<?php
									if($link_to){
										echo "</a>";
									}
									?>
								</div>
								<?php

							}
						?>
					</div>
					<?php if ( 1 < $slides_count ) : ?>
						<?php if ( !empty($settings['navigation']) && $settings['navigation']!='none') : ?>
							<div class="swiper-pagination"></div>
						<?php endif; ?>
						<?php if ( !empty($settings['navigation']) && ( $settings['navigation']=='both' || $settings['navigation']=='arrows')) : ?>
							<div class="elementor-swiper-button elementor-swiper-button-prev">
								<i class="eicon-chevron-left" aria-hidden="true"></i>
								<span class="elementor-screen-only"><?php _e( 'Previous', 'elementor-pro' ); ?></span>
							</div>
							<div class="elementor-swiper-button elementor-swiper-button-next">
								<i class="eicon-chevron-right" aria-hidden="true"></i>
								<span class="elementor-screen-only"><?php _e( 'Next', 'elementor-pro' ); ?></span>
							</div>
						<?php endif; ?>
					<?php endif; ?>
			</div>
			<?php
		}


		return ob_get_clean();

	}

	/**
	 * Get the image field key from gallery info.
	 *
	 * @param $gallery
	 *
	 * @return string|void
	 */
	public static function get_gallery_key($gallery){
		$key = '';

		preg_match( '~settings[ ]*=[ ]*["\'](.*?)["\']~is', $gallery, $match );
		if ( ! empty( $match[1] ) ) {
			$gallery_settings = json_decode( urldecode( $match[1] ) );
			if ( ! empty( $gallery_settings->key ) ) {
				$key = esc_attr( $gallery_settings->key );
			}
		}

		return $key;

	}

	/**
	 * Elementor pro gallery element re-render.
	 *
	 * @param $widget
	 * @param $settings
	 *
	 * @return string
	 */
	public static function render_pro_gallery($widget,$settings){
		global $gd_post;
		$is_multiple = 'multiple' === $settings['gallery_type'] && ! empty( $settings['galleries'] );

		$is_single = 'single' === $settings['gallery_type'] && ! empty( $settings['gallery'] );

		$has_description = ! empty( $settings['overlay_description'] );

		$has_title = ! empty( $settings['overlay_title'] );

		$has_animation = ! empty( $settings['image_hover_animation'] ) || ! empty( $settings['content_hover_animation'] ) || ! empty( $settings['background_overlay_hover_animation'] );

		$gallery_item_tag = ! empty( $settings['link_to'] ) ? 'a' : 'div';

		$galleries = [];

		ob_start();

		if ( $is_multiple ) {
			$widget->add_render_attribute( 'titles-container', 'class', 'elementor-gallery__titles-container' );

			if ( $settings['pointer'] ) {
				$widget->add_render_attribute( 'titles-container', 'class', 'e--pointer-' . $settings['pointer'] );

				foreach ( $settings as $key => $value ) {
					if ( 0 === strpos( $key, 'animation' ) && $value ) {
						$widget->add_render_attribute( 'titles-container', 'class', 'e--animation-' . $value );
						break;
					}
				}
			} ?>
			<div <?php echo $widget->get_render_attribute_string( 'titles-container' ); ?>>
				<?php if ( $settings['show_all_galleries'] ) { ?>
					<a data-gallery-index="all" class="elementor-item elementor-gallery-title"><?php echo $settings['show_all_galleries_label']; ?></a>
				<?php } ?>

				<?php foreach ( $settings['galleries'] as $index => $gallery ) :
					if ( ! $gallery['__dynamic__'] ) {
						continue;
					}
					$key = self::get_gallery_key($gallery['__dynamic__']['multiple_gallery']);
					$galleries[] = GeoDir_Media::get_attachments_by_type( $gd_post->ID, $key );
					?>
					<a data-gallery-index="<?php echo $index; ?>" class="elementor-item elementor-gallery-title"><?php echo $gallery['gallery_title']; ?></a>
					<?php
				endforeach; ?>
			</div>
			<?php
		} elseif ( $is_single ) {
			$key = self::get_gallery_key($settings['__dynamic__']['gallery']);
			$galleries[0] = GeoDir_Media::get_attachments_by_type( $gd_post->ID, $key );
		} elseif ( \ElementorPro\Plugin::elementor()->editor->is_edit_mode() ) { ?>
			<i class="elementor-widget-empty-icon eicon-gallery-justified"></i>
		<?php }

		$widget->add_render_attribute( 'gallery_container', 'class', 'elementor-gallery__container' );

		if ( $has_title || $has_description ) {
			$widget->add_render_attribute( 'gallery_item_content', 'class', 'elementor-gallery-item__content' );

			if ( $has_title ) {
				$widget->add_render_attribute( 'gallery_item_title', 'class', 'elementor-gallery-item__title' );
			}

			if ( $has_description ) {
				$widget->add_render_attribute( 'gallery_item_description', 'class', 'elementor-gallery-item__description' );
			}
		}

		$widget->add_render_attribute( 'gallery_item_background_overlay', [ 'class' => 'elementor-gallery-item__overlay' ] );

		$gallery_items = [];
		$all_items = [];
		$thumbnail_size = $settings['thumbnail_image_size'];
		foreach ( $galleries as $gallery_index => $gallery ) {
			foreach ( $gallery as $index => $item ) {
//				if ( in_array( $item['id'], array_keys( $gallery_items ), true ) ) {
//					$gallery_items[ $item['id'] ][] = $gallery_index;
//				} else {
//					$gallery_items[ $item['id'] ] = [ $gallery_index ];
//				}

//				$gallery_items[] = $item;
				$gallery_items[$item->ID] = $gallery_index;
				$all_items[$item->ID] = $item;

			}
		}

//		echo '###';print_r($gallery_items);exit;

		if ( 'random' === $settings['order_by'] ) {
			$shuffled_items = [];
			$keys = array_keys( $gallery_items );
			shuffle( $keys );
			foreach ( $keys as $key ) {
				$shuffled_items[ $key ] = $gallery_items[ $key ];
			}
			$gallery_items = $shuffled_items;
		}

		if ( ! empty( $galleries ) ) { ?>
		<div <?php echo $widget->get_render_attribute_string( 'gallery_container' ); ?>>
			<?php
			foreach ( $gallery_items as $id => $tags ) :
				$tags = array($tags);
				$item = $all_items[$id];
				$unique_index = $id; //$gallery_index . '_' . $index;
				$img_src      = geodir_get_image_src( $item, $thumbnail_size );
//				$img_src      = geodir_get_image_src( $item, 'full' );
				if ( ! $img_src ) {
					continue;
				}

				$image_meta = isset( $item->metadata ) ? maybe_unserialize( $item->metadata ) : '';

//				print_r($image_meta);

				$attributes = array(
					'data-elementor-lightbox-description' => $item->caption,
//					'title' => $item->title,
//					'alt' => $item->caption,
					'data-elementor-lightbox-title' => $item->title,
				);

//				$image_data = [
//					'alt' => get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ),
//					'caption' => $attachment->post_excerpt,
//					'description' => $attachment->post_content,
//					'title' => $attachment->post_title,
//				];

//				$attachment = get_post( $id );
				$image_data = [
					'alt' => $item->caption,
					'media' => geodir_get_image_src( $item, 'full' ),
					'src' => $img_src,
					'width' => !empty($image_meta['sizes'][$thumbnail_size]['width']) ? $image_meta['sizes'][$thumbnail_size]['width'] : $image_meta['width'],
					'height' => !empty($image_meta['sizes'][$thumbnail_size]['height']) ? $image_meta['sizes'][$thumbnail_size]['height'] : $image_meta['height'],
					'caption' => $item->caption,
					'description' => $item->caption,
					'title' => $item->title,
				];

				$widget->add_render_attribute( 'gallery_item_' . $unique_index, [
					'class' => [
						'e-gallery-item',
						'elementor-gallery-item',
					],
				] );

				if ( $has_animation ) {
					$widget->add_render_attribute( 'gallery_item_' . $unique_index, [ 'class' => 'elementor-animated-content' ] );
				}

				if ( $is_multiple ) {
					$widget->add_render_attribute( 'gallery_item_' . $unique_index, [ 'data-e-gallery-tags' => implode( ',', $tags ) ] );
				}

				if ( 'a' === $gallery_item_tag ) {
					if ( 'file' === $settings['link_to'] ) {
						$href = $image_data['media'];

						$widget->add_render_attribute( 'gallery_item_' . $unique_index, [
							'href' => $href,
						] );

//						$widget->add_lightbox_data_attributes( 'gallery_item_' . $unique_index, null, 'yes', 'all-' . $widget->get_id() );
						$widget->add_render_attribute( 'gallery_item_' . $unique_index, $attributes, null );
					} elseif ( 'custom' === $settings['link_to'] ) {
						$widget->add_link_attributes( 'gallery_item_' . $unique_index, $settings['url'] );
					}
				}

				$widget->add_render_attribute( 'gallery_item_image_' . $unique_index,
					[
						'class' => [
							'e-gallery-image',
							'elementor-gallery-item__image',
						],
						'data-thumbnail' => $image_data['src'],
						'data-width' => $image_data['width'],
						'data-height' => $image_data['height'],
						'alt' => $image_data['alt'],
					]
				);?>
				<<?php echo $gallery_item_tag; ?> <?php echo $widget->get_render_attribute_string( 'gallery_item_' . $unique_index ); ?>>
				<div <?php echo $widget->get_render_attribute_string( 'gallery_item_image_' . $unique_index ); ?> ></div>
				<?php if ( ! empty( $settings['overlay_background'] ) ) : ?>
				<div <?php echo $widget->get_render_attribute_string( 'gallery_item_background_overlay' ); ?>></div>
			<?php endif; ?>
				<?php if ( $has_title || $has_description ) : ?>
				<div <?php echo $widget->get_render_attribute_string( 'gallery_item_content' ); ?>>
					<?php if ( $has_title ) :
						$title = $image_data[ $settings['overlay_title'] ];
						if ( ! empty( $title ) ) : ?>
							<div <?php echo $widget->get_render_attribute_string( 'gallery_item_title' ); ?>><?php echo $title; ?></div>
						<?php endif;
					endif;
					if ( $has_description ) :
						$description = $image_data[ $settings['overlay_description'] ];
						if ( ! empty( $description ) ) :?>
							<div <?php echo $widget->get_render_attribute_string( 'gallery_item_description' ); ?>><?php echo $description; ?></div>
						<?php endif;
					endif; ?>
				</div>
			<?php endif; ?>
				</<?php echo $gallery_item_tag; ?>>
			<?php endforeach;
			//endforeach; ?>
			</div>
		<?php }

		return ob_get_clean();
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
			'GeoDir_Elementor_Tag_Number',
			'GeoDir_Elementor_Tag_Color',
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

//		print_r($value);exit;
		// add our own templates
		if ( ! empty( $value['templates'] ) ) {

			$default_templates = $value['templates'];
			$templates         = array();

			// Add block categories
			if(!empty($value['types_data']['block']['categories'])){
				$value['types_data']['block']['categories'][] = "directory archive";
				$value['types_data']['block']['categories'][] = "directory single";
			}

//			// Add categories
//			if(!empty($value['categories'])){
//				$value['categories'] = str_replace('"product archive"','"product archive","directory archive"',$value['categories']);
//			}

			// Real-estate
			$templates[] = array(
				'id'                => 'ayecode-realestate-homepage-001',
				'title'             => "<i class=\"fas fa-globe-americas\" style='color:#ff8333 !important'></i> Homepage &#8211; Real-estate",
				'thumbnail'         => 'https://wpgeodirectory.com/dummy/elementor/realestate/preview.png',
				'tmpl_created'      => '1477388340',
				'author'            => 'AyeCode',
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

			// Archive blocks
			$templates[] = array(
				'id'                => 'ayecode-block-archive-001',
				'title'             => "<i class=\"fas fa-globe-americas\" style='color:#ff8333 !important'></i> Directory Archive",
				'thumbnail'         => 'https://wpgeodirectory.com/dummy/elementor/realestate/preview.png',
				'tmpl_created'      => '1477388340',
				'author'            => 'AyeCode',
				'url'               => 'https://ppldb.com/realestate-elemntor/home-version-3/',
				'type'              => 'block',
				'subtype'           => 'directory archive',
				'tags'              => '["Directory","GeoDirectory","AyeCode"]',
				'menu_order'        => '3',
				'popularity_index'  => '4',
				'trend_index'       => '4',
				'is_pro'            => '0',
				'has_page_settings' => '0',
			);

			// Single blocks
			$templates[] = array(
				'id'                => 'ayecode-block-single-001',
				'title'             => "<i class=\"fas fa-globe-americas\" style='color:#ff8333 !important'></i> Directory Single",
				'thumbnail'         => 'https://wpgeodirectory.com/dummy/elementor/realestate/preview.png',
				'tmpl_created'      => '1477388340',
				'author'            => 'AyeCode',
				'url'               => 'https://ppldb.com/realestate-elemntor/home-version-3/',
				'type'              => 'block',
				'subtype'           => 'directory single',
				'tags'              => '["Directory","GeoDirectory","AyeCode"]',
				'menu_order'        => '3',
				'popularity_index'  => '4',
				'trend_index'       => '4',
				'is_pro'            => '0',
				'has_page_settings' => '0',
			);

			$value['templates'] = $templates + $default_templates;
		}
//		print_r($value);exit;
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
		$data = array();
		$base_url = "https://wpgeodirectory.com/dummy/elementor/";
		$key_parts = explode("-",$key);
		$route = $key_parts[1]."/";
		unset($key_parts[0]);
		unset($key_parts[1]);
		$route .= implode("-",$key_parts).".json";

		if($route){
			$url = $base_url.$route;
			$response = wp_remote_get($url);

			if ( is_array( $response ) && wp_remote_retrieve_response_code( $response ) == '200' ) {
				$data = json_decode(wp_remote_retrieve_body( $response ),true);
			}
		}


		if(!empty($data)){
			$content = self::template_response( $data, $key );
			wp_send_json_success( $content );
		}else{
			wp_send_json_error(__('Could not retrieve template.','geodirectory'));
		}
		exit;

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
	 * Allow to filter the archive item template content if being edited by elementor.
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

			if( geodir_is_page( 'author' ) ){
				$type = 'author';
			}elseif ( geodir_is_page( 'post_type' ) ) {
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
						if ( $archive_condition == 'include/geodirectory_archive' || stripos( strrev( $archive_condition ), strrev( $type ) ) === 0 ) {
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