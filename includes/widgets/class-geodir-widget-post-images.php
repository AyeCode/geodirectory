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
					'style'   => '{background: "#eee",width: "100%", height: "450px", position:"relative"}',
					array(
						'element' => 'i',
						'if_class'   => '[%animation%]=="fade" ? "far fa-image gd-fadein-animation" : "far fa-image gd-right-left-animation"',
						'style'   => '{"text-align": "center", "vertical-align": "middle", "line-height": "450px", width: "100%","font-size":"140px",color:"#aaa"}',
					),
					array(
						'element' => 'p',
						'element_require' => '[%show_title%]',
						'content' => __('This is a default picture title','geodirectory'),
						'style'   => '{background: "#aaa","text-align": "left", "vertical-align": "middle","font-size":"14px",color:"#fff",position:"absolute","margin-top":"-30px",height:"30px","line-height":"30px",width:"100%","padding-left":"10px"}',
					),
				),
				// default nav
				array(
					'element' => 'div',
					'element_require' => '[%controlnav%]=="1"',
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
					'element_require' => '[%controlnav%]=="2"',
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
			'arguments'     => array(
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
					//'element_require' => '[%type%]=="slider"',
					'advanced' => true
				),
				'show_logo'  => array(
					'title' => __('Show logo:', 'geodirectory'),
					'desc' => __('Show the listing logo first if uploaded.', 'geodirectory'),
					'type' => 'checkbox',
					'desc_tip' => true,
					'value'  => '1',
					'default'  => 0,
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
				)
			)
		);

		// maybe show logo options
//		global $wpdb;
//		if($wpdb->get_var("SELECT id FROM ".GEODIR_CUSTOM_FIELDS_TABLE." WHERE htmlvar_name='logo' LIMIT 1")){
//
//		}


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
		global $post,$gd_slider_outputs;
		ob_start();


		// options
		$defaults = array(
			'type'      => 'image', // image, slider, gallery
			'ajax_load' => '1',
			'animation' => 'fade', // fade or slide
			'slideshow' => 'true', // auto start
			'controlnav'=> '2', // 0 = none, 1 =  standard, 2 = thumbnails
			'show_title'=> '1',
			'limit'     => '',
			'link_to'     => '',
			'image_size'     => 'medium',
			'show_logo'     => 'false',
		);

		/**
		 * Parse incoming $args into an array and merge it with $defaults
		 */
		$options = wp_parse_args( $options, $defaults );

		if($options['type']=='image'){
			$options['limit'] = 1;
		}


		$post_images = geodir_get_images($post->ID, $options['limit'], $options['show_logo']);


		// make it just a image if only one
		if($options['type']=='slider' && count($post_images) == 1){
			$options['type']='image';
		}


		if (!empty($post_images)) {
			$main_wrapper_class = "geodir-image-container";
			$second_wrapper_class = "geodir-image-wrapper";
			$ul_class = "geodir-post-image";
			$image_size = isset($options['image_size']) && $options['image_size'] ? $options['image_size'] : 'medium_large';



			if($options['type']=='slider'){
				// enqueue flexslider JS
				GeoDir_Frontend_Scripts::enqueue_script( 'jquery-flexslider' );

				$main_wrapper_class .= " geodir_flex-container ";
				$second_wrapper_class .= " geodir_flexslider geodir-slider geodir-slider-loading ";
				$ul_class = "geodir-slides";
			}elseif($options['type']=='gallery'){
				if(!$image_size){$image_size = 'medium_large';}
				$ul_class = "geodir-gallery";
			}

			// Set the slider ID
			$slider_id = "geodir_images_".$post->ID;
			if(!is_array($gd_slider_outputs)){
				$gd_slider_outputs = array();
			}
			if(isset($gd_slider_outputs[$post->ID])){
				$gd_slider_outputs[$post->ID]++;
				$slider_id .= "_".$gd_slider_outputs[$post->ID];
			}else{
				$gd_slider_outputs[$post->ID] = 1;
			}

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

			?>
			<div class="<?php echo $main_wrapper_class;?>" >
				<?php if($options['type']=='slider'){ echo '<div class="geodir_flex-loader"><i class="fas fa-sync fa-spin" aria-hidden="true"></i></div>';}?>
				<div id="<?php echo $slider_id; ?>" class="<?php echo $second_wrapper_class;?>" <?php
				if($options['controlnav']==1){echo " data-controlnav='1' ";}
				if($options['animation']=='fade'){echo " data-animation='fade' ";}
				if($options['slideshow']){echo " data-slideshow='1' ";}
				?>>
					<ul class="<?php echo esc_attr($ul_class );?> geodir-images clearfix"><?php
						$image_count = 0;
						foreach($post_images as $image){
							echo "<li>";
							//print_r($image);
							$img_tag = geodir_get_image_tag($image,$image_size );
							$meta = isset($image->metadata) ? maybe_unserialize($image->metadata) : '';
							$img_tag =  wp_image_add_srcset_and_sizes( $img_tag, $meta , 0 );


							// image link
							if($options['link_to']=='lightbox'){
								$link = geodir_get_image_src($image, 'large');
							}

							// ajaxify images
							if($options['type']=='slider' && $options['ajax_load'] && $image_count){
								$img_tag = geodir_image_tag_ajaxify($img_tag,$options['type']!='slider');
							}elseif($options['ajax_load']){
								$img_tag = geodir_image_tag_ajaxify($img_tag);
							}
							// output image
							echo $link_tag_open ? sprintf($link_tag_open,esc_url($link)) : '';
							echo $img_tag;
							echo $link_tag_close;


							if($options['type']=='slider' && $options['show_title'] && !empty($image->title)){
								echo '<p class="flex-caption">'.$image->title.'</p>';
							}
							echo "</li>";
							$image_count++;
						}
						?></ul>
				</div>
				<?php if ($options['type']=='slider' && $image_count > 1 && $options['controlnav'] == 2 ) { ?>
					<div id="<?php echo $slider_id; ?>_carousel" class="geodir_flexslider">
						<ul class="geodir-slides clearfix"><?php
							foreach($post_images as $image){
								echo "<li>";
								$img_tag = geodir_get_image_tag($image,'thumbnail');
								$meta = isset($image->metadata) ? maybe_unserialize($image->metadata) : '';
								$img_tag =  wp_image_add_srcset_and_sizes( $img_tag, $meta , 0 );
								echo $img_tag;
								echo "</li>";
							}
							?></ul>
					</div>
				<?php } ?>
			</div>
			<?php
		}

		return ob_get_clean();
	}


	/**
	 * Get the available image sizes.
	 * 
	 * @return array
	 */
	public static function get_image_sizes(){
		$image_sizes = array(''=>'default');
		$available = get_intermediate_image_sizes();

		if(!empty($available)){
			foreach($available as $size){
				$image_sizes[$size] = $size;
			}
		}

		$image_sizes['full'] = 'full';


		return $image_sizes;
	}

}

