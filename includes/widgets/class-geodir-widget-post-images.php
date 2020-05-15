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
			'textdomain'    => GEODIRECTORY_TEXTDOMAIN,
			'block-icon'    => 'format-image',
			'block-category'=> 'common',
			'block-keywords'=> "['images','geo','geodir']",
			'block-output'   => array( // the block visual output elements as an array
				array(
					'element' => 'div',
					'title'   => __( 'Placeholder image', 'geodirectory' ),
					'class'   => '[%className%]',
					'element_require' => '[%type%]!="gallery"',
					'style'   => '{background: "#eee",width: "100%", height: "450px", position:"relative"}',
					array(
						'element' => 'i',
						'if_class'   => '[%animation%]=="fade" ? "far fa-image gd-fadein-animation" : "far fa-image gd-right-left-animation"',
						'style'   => '{"text-align": "center", "vertical-align": "middle", "line-height": "450px", "height": "100%", width: "100%","font-size":"140px",color:"#aaa"}',
					),
					array(
						'element' => 'p',
						'element_require' => '[%show_title%] && [%type%]=="slider"',
						'content' => __('This is a default picture title','geodirectory'),
						'style'   => '{background: "#aaa","text-align": "left", "vertical-align": "middle","font-size":"14px",color:"#fff",position:"absolute","margin-top":"-30px",height:"30px","line-height":"30px",width:"100%","padding-left":"10px"}',
					),
				),
				// default nav
				array(
					'element' => 'div',
					'element_require' => '[%controlnav%]=="1" && [%type%]=="slider"',
					'style'   => '{width: "100%","margin-top":"5px","text-align": "center"}',
					array(
						'element' => 'i',
						'element_repeat' => '10',
						'class'   => 'fas fa-circle',
						'style'   => '{background: "#eee","text-align": "center", "vertical-align": "middle","font-size":"10px",color:"#aaa",margin:"0.2%"}',

					),
				),
				// thumbnail nav
				array(
					'element' => 'div',
					'element_require' => '[%controlnav%]=="2" || [%type%]=="gallery"',
					'style'   => '{width: "100%","margin-top":"5px"}',
					array(
						'element' => 'i',
						'element_repeat' => '5',
						'class'   => 'far fa-image',
						'style'   => '{background: "#eee","text-align": "center", "vertical-align": "middle", "line-height": "65px", width: "19.6%",height:"65px","font-size":"35px",color:"#aaa",margin:"0.2%"}',

					),
				),
			),
			'class_name'    => __CLASS__,
			'base_id'       => 'gd_post_images', // this us used as the widget id and the shortcode id.
			'name'          => __('GD > Post Images','geodirectory'), // the name of the widget.
			//'disable_widget'=> true,
			'widget_ops'    => array(
				'classname'   => 'geodir-post-slider', // widget class
				'description' => esc_html__('This shows a GD post image.','geodirectory'), // widget description
				'geodirectory' => true,
			),
			'arguments' => array(
				'title'=> array(
					'type' => 'text',
					'title' => __( 'Title:', 'geodirectory' ),
					'desc' => __( 'The widget title.', 'geodirectory' ),
					'default' => '',
					'desc_tip' => true,
					'advanced' => false
				),
				'type'  => array(
					'title' => __('Output Type:', 'geodirectory'),
					'desc' => __('How the images should be displayed.', 'geodirectory'),
					'type' => 'select',
					'options'   =>  array(
						"image" => __('Single image', 'geodirectory'),
						"slider" => __('Slider', 'geodirectory'),
						"gallery" => __('Gallery', 'geodirectory'),
					),
					'default'  => 'image',
					'desc_tip' => true,
					'advanced' => false
				),
				'ajax_load'  => array(
					'title' => __('Load via Ajax:', 'geodirectory'),
					'desc' => __('This will load all but the first slide via ajax for faster load times.', 'geodirectory'),
					'type' => 'checkbox',
					'desc_tip' => true,
					'value'  => '1',
					'default'  => 1,
					'advanced' => true
				),
				'slideshow'  => array(
					'title' => __('Auto start:', 'geodirectory'),
					'desc' => __('Should the slider auto start.', 'geodirectory'),
					'type' => 'checkbox',
					'desc_tip' => true,
					'value'  => '1',
					'default'  => 1,
					'element_require' => '[%type%]=="slider"',
					'advanced' => true
				),
				'show_title'  => array(
					'title' => __('Show title:', 'geodirectory'),
					'desc' => __('Show the titles on the image.', 'geodirectory'),
					'type' => 'checkbox',
					'desc_tip' => true,
					'value'  => '1',
					'default'  => 1,
					'element_require' => '[%type%]=="slider"',
					'advanced' => true
				),
				'show_caption'  => array(
					'title' 	=> __('Show caption:', 'geodirectory'),
					'desc' 		=> __('Show the captions on the image. Requires you to enable titles.', 'geodirectory'),
					'type' 		=> 'checkbox',
					'desc_tip' 	=> true,
					'value'  	=> '0',
					'default'   => 0,
					'element_require' => '[%show_title%] && [%type%]=="slider"',
					'advanced' 	=> true
				),
				'animation'  => array(
					'title' => __('Animation:', 'geodirectory'),
					'desc' => __('Slide or fade transition.', 'geodirectory'),
					'type' => 'select',
					'options'   =>  array(
						"slide" => __('Slide', 'geodirectory'),
						"fade" => __('Fade', 'geodirectory'),
					),
					'default'  => 'slide',
					'desc_tip' => true,
					'element_require' => '[%type%]=="slider"',
					'advanced' => true
				),
				'controlnav'  => array(
					'title' => __('Control Navigation:', 'geodirectory'),
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
					'advanced' => true
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
					'advanced' => true
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
					'advanced' => true
				),
				'types'  => array(
					'title' => __('Image types:', 'geodirectory'),
					'desc' => __('Comma separated list of image types to show. Defaults to: post_images', 'geodirectory'),
					'type' => 'text',
					'desc_tip' => true,
					'value'  => '',
					'default'  => '',
					'placeholder'  => 'post_images,logo,comment_images,website_screenshot',
					'advanced' => true
				),
				'fallback_types'  => array(
					'title' => __('Fallback types:', 'geodirectory'),
					'desc' => __('Comma separated list of fallback types to show (only one will be shown). Defaults to: logo,cat_default,cpt_default,listing_default', 'geodirectory'),
					'type' => 'text',
					'desc_tip' => true,
					'value'  => '',
					'default'  => '',
					'placeholder'  => 'logo,cat_default,cpt_default,listing_default',
					'advanced' => true
				),
				'image_size'  => array(
					'title' => __('Image size:', 'geodirectory'),
					'desc' => __('The WP image size as a text string.', 'geodirectory'),
					'type' => 'select',
					'options' => self:: get_image_sizes(),
					'desc_tip' => true,
					'value'  => '',
					'default'  => '',
					'advanced' => true
				),
				'limit'  => array(
					'title' => __('Image limit:', 'geodirectory'),
					'desc' => __('Limit the number of images returned.', 'geodirectory'),
					'type' => 'number',
					'desc_tip' => true,
					'value'  => '',
					'default'  => '',
					'advanced' => true
				),
				'limit_show'  => array(
					'title' => __('Show limit:', 'geodirectory'),
					'desc' => __('Limit the number of images shown. This can be used to output 1-2 images in a gallery and if linked to lightbox it can ajax load more images when in lightbox. This can also be sued to turn the slider into a carousel and will set the default visible images.', 'geodirectory'),
					'type' => 'number',
					'desc_tip' => true,
					'value'  => '',
					'default'  => '',
					'advanced' => true
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
					'advanced' => true
				),
				'css_class'  => array(
					'type' => 'text',
					'title' => __('Extra class:', 'geodirectory'),
					'desc' => __('Give the wrapper an extra class so you can style things as you want.', 'geodirectory'),
					'placeholder' => '',
					'default' => '',
					'desc_tip' => true,
					'advanced' => true,
				)
			)
		);

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
	public function output($args = array(), $widget_args = array(),$content = ''){

		/**
		 * @var bool $ajax_load Ajax load or not.
		 * @var string $animation Fade or slide.
		 * @var bool $slideshow Auto start or not.
		 * @var int $controlnav 0 = none, 1 =  standard, 2 = thumbnails
		 * @var bool $show_title If the title should be shown or not.
		 * @var int/empty $limit If the number of images should be limited.
		 */
		extract($args, EXTR_SKIP);

		return $this->output_images($args);

	}

	/**
	 * Output the imae slider.
	 *
	 * @param $options
	 */
	public function output_images($options){
		global $post, $gd_post;
		if(!isset($post->ID)){return '';}
		ob_start();

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
			'limit_show'     => '',
			'link_to'     => '',
			'link_screenshot_to'     => '',
			'image_size'     => 'medium',
			'show_logo'     => '0',
			'cover'   => '', // image cover type
			'types'   => '', // types to show, post_images,comment_images,logo
			'fallback_types'   => 'logo,cat_default,cpt_default,listing_default,website_screenshot', //logo,cat_default,cpt_default,listing_default
			'css_class' => '',
		);

		/**
		 * Parse incoming $args into an array and merge it with $defaults
		 */
		$options = wp_parse_args( $options, $defaults );

		if($this->is_preview()){
			$options['ajax_load'] = false;
		}

		if($options['type']=='image'){
			$options['limit'] = 1;
		}

		$revision_id = is_preview() && !empty($gd_post->ID) ? absint($gd_post->ID) : '';

		// types
		if(!empty($options['types'])){
			$options['types'] = explode(",",$options['types']);
		}
		
		// fallback types
		if(!empty($options['fallback_types'])){
			$options['fallback_types'] = explode(",",$options['fallback_types']);
		}elseif($options['fallback_types']=='0'){
			$options['fallback_types'] = array();
		}else{
			$options['fallback_types'] = array('logo','cat_default','cpt_default','listing_default');
		}

		$post_images = geodir_get_images($post->ID, $options['limit'], $options['show_logo'],$revision_id,$options['types'],$options['fallback_types']);

//		print_r($post_images );

		// make it just a image if only one
		if($options['type']=='slider' && count($post_images) == 1 && $options['limit_show']){
			$options['type']='image';
		}


		if (!empty($post_images)) {
			$main_wrapper_class = "geodir-image-container";
			$second_wrapper_class = "geodir-image-wrapper";
			$ul_class = "geodir-post-image";
			$image_size = isset($options['image_size']) && $options['image_size'] ? $options['image_size'] : 'medium_large';
			$main_wrapper_class .= " geodir-image-sizes-".$image_size;



			if($options['type']=='slider'){
				// enqueue flexslider JS
				GeoDir_Frontend_Scripts::enqueue_script( 'jquery-flexslider' );

				$main_wrapper_class .= " geodir_flex-container ";
				$second_wrapper_class .= " geodir_flexslider geodir-slider geodir-slider-loading ";
				$ul_class = "geodir-slides";
				if($options['limit_show']){
					$second_wrapper_class .= " geodir-carousel ";
				}
			}elseif($options['type']=='gallery'){
				if(!$image_size){$image_size = 'medium_large';}
				$ul_class = "geodir-gallery";
			}

			// Set the slider ID
			$slider_id = wp_doing_ajax() ? "geodir_images_ajax" : "geodir_images";
			$slider_id .= '_' . uniqid() . '_' . $post->ID; // Generate unique slider id.

			// image link
			$link = '';
			$link_tag_open = "";
			$link_tag_close = "";
			if($options['link_to']=='post'){
				$link = get_the_permalink($post->ID);
				$link_tag_open = "<a href='%s'>";
				$link_tag_close = "</a>";
			}elseif($options['link_to']=='lightbox'){
				$link = '';
				$link_tag_open = "<a href='%s' class='geodir-lightbox-image' data-lity>";
				$link_tag_close = "<i class=\"fas fa-search-plus\" aria-hidden=\"true\"></i></a>";
			}

			// image_cover
			if(!empty($options['cover'])){
				if($options['cover']=='x'){$main_wrapper_class .= " gd-image-cover-x ";}
				if($options['cover']=='y'){$main_wrapper_class .= " gd-image-cover-y ";}
				if($options['cover']=='n'){$main_wrapper_class .= " gd-image-cover-n ";}
			}

			?>
			<div class="<?php echo $main_wrapper_class; echo " ".esc_attr($options['css_class']);?>" >
				<?php if($options['type']=='slider'){ echo '<div class="geodir_flex-loader"><i class="fas fa-sync fa-spin" aria-hidden="true"></i></div>';}?>
				<div id="<?php echo $slider_id; ?>" class="<?php echo $second_wrapper_class;?>" <?php
				if($options['controlnav']==1){echo " data-controlnav='1' ";}
				if($options['animation']=='fade'){echo " data-animation='fade' ";}
				if($options['slideshow']){echo " data-slideshow='1' ";}
				if($options['limit_show']){echo " data-limit_show='".absint($options['limit_show'])."' ";}
				?>>
					<ul class="<?php echo esc_attr($ul_class );?> geodir-images clearfix"><?php
						$image_count = 0;
						foreach($post_images as $image){

							// reset temp tags
							$link_tag_open_ss = '';
							$link_tag_close_ss = '';


							$limit_show = !empty($options['limit_show']) && $image_count >= $options['limit_show'] ? "style='display:none;'" : '';
							echo "<li $limit_show >";

							$img_tag = geodir_get_image_tag($image,$image_size );
							$meta = isset($image->metadata) ? maybe_unserialize($image->metadata) : '';

							// only set different sizes if not thumbnail
							if($image_size!='thumbnail'){
								$img_tag =  wp_image_add_srcset_and_sizes( $img_tag, $meta , 0 );
							}

							// image link
							if($options['link_to']=='lightbox'){
								$link = geodir_get_image_src($image, 'large');
							}

							// check if screenshot link is different
							if($options['link_screenshot_to']!='' && $options['link_screenshot_to']!=$options['link_to'] && !$image->ID && stripos(strrev($image->type), "tohsneercs_") === 0){
								if($options['link_screenshot_to']=='post'){
									$link = get_the_permalink($post->ID);
									$link_tag_open_ss = "<a href='%s'>";
									$link_tag_close_ss = "</a>";
								}elseif($options['link_screenshot_to']=='lightbox'){
									$link = geodir_get_image_src($image, 'large');
									$link_tag_open_ss = "<a href='%s' class='geodir-lightbox-image' data-lity>";
									$link_tag_close_ss = "<i class=\"fas fa-search-plus\" aria-hidden=\"true\"></i></a>";
								}elseif($options['link_screenshot_to']=='lightbox_url'){
									$field_key = str_replace("_screenshot","",$image->type);
									$link = isset($gd_post->{$field_key}) ? $gd_post->{$field_key} : '';
									$link_tag_open_ss = "<a href='%s' class='geodir-lightbox-image' data-lity>";
									$link_tag_close_ss = "<i class=\"fas fa-search-plus\" aria-hidden=\"true\"></i></a>";
								}elseif($options['link_screenshot_to']=='url' || $options['link_screenshot_to']=='url_same'){
									$field_key = str_replace("_screenshot","",$image->type);
									$target = $options['link_screenshot_to']=='url' ? "target='_blank'" : '';
									$link_icon = $options['link_screenshot_to']=='url' ? "fas fa-external-link-alt" : 'fas fa-link';
									$link = isset($gd_post->{$field_key}) ? $gd_post->{$field_key} : '';
									$link_tag_open_ss = "<a href='%s' $target class='geodir-lightbox-image' rel='nofollow noopener noreferrer'>";
									$link_tag_close_ss = "<i class=\"$link_icon\" aria-hidden=\"true\"></i></a>";
								}

							}

							// ajaxify images
							if($options['type']=='slider' && $options['ajax_load'] && $image_count){
								$img_tag = geodir_image_tag_ajaxify($img_tag,$options['type']!='slider');
							}elseif($options['ajax_load']){
								$img_tag = geodir_image_tag_ajaxify($img_tag);
							}
							// output image
							if($link_tag_open_ss){
								echo $link_tag_open_ss ? sprintf($link_tag_open_ss,esc_url($link)) : '';
							}else{
								echo $link_tag_open ? sprintf($link_tag_open,esc_url($link)) : '';
							}
							echo $img_tag;
							if($link_tag_close_ss){
								echo $link_tag_close_ss;
							}else{
								echo $link_tag_close;
							}

							$flex_caption = '';
							if($options['type']=='slider' && $options['show_title'] && !empty($image->title)){

								$flex_caption = esc_attr( stripslashes_deep( $image->title ) );
							}
							//Maybe add a caption to the title
							
							/**
							 * Filters whether or not the caption should be displayed.
							 *
							 * @since   2.0.0.63
							 * @package GeoDirectory
							 */
							$show_caption = apply_filters( 'geodir_post_images_show_caption', $options['show_caption'] );

							if( $show_caption && !empty( $image->caption ) ) {
								$flex_caption .= "<small>".esc_attr( stripslashes_deep( $image->caption ) )."</small>";
							}
							if( !empty( $flex_caption ) ){
								echo '<p class="flex-caption gd-flex-caption">'.$flex_caption.'</p>';
							}
							echo "</li>";
							$image_count++;
						}
						?></ul>
				</div>
				<?php if ($options['type']=='slider' && $image_count > 1 && $options['controlnav'] == 2 ) { ?>
					<div id="<?php echo $slider_id; ?>_carousel" class="geodir_flexslider geodir_flexslider_carousel">
						<ul class="geodir-slides clearfix"><?php
							foreach($post_images as $image){
								echo "<li>";
								$img_tag = geodir_get_image_tag($image,'thumbnail');
								$meta = isset($image->metadata) ? maybe_unserialize($image->metadata) : '';
								//$img_tag =  wp_image_add_srcset_and_sizes( $img_tag, $meta , 0 );
								echo $img_tag;
								echo "</li>";
							}
							?></ul>
					</div>
				<?php } ?>
			</div>
			<?php
		}


		// make it work with builder preview
		if($this->is_elementor_preview()){
			?>
			<script type="text/javascript">
				(function(){
					if (typeof init_read_more == 'function') {
						init_read_more();
						geodir_init_lazy_load();
						geodir_refresh_business_hours();
						// init any sliders
						geodir_init_flexslider();
					}
				}());
			</script>
			<?php
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

}
