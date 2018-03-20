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
					'title'   => __( 'Placeholder1', 'geodirectory' ),
					'class'   => '[%className%]',
					'style'   => '{background: "#eee",width: "100%", height: "450px", position:"relative"}',
					array(
						'element' => 'i',
						'if_class'   => '[%animation%]=="fade" ? "fa fa-picture-o gd-fadein-animation" : "fa fa-picture-o gd-right-left-animation"',
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
						'class'   => 'fa fa-circle',
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
						'class'   => 'fa fa-picture-o',
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
		);

		/**
		 * Parse incoming $args into an array and merge it with $defaults
		 */
		$options = wp_parse_args( $options, $defaults );

		//print_r($options);echo '###';

		$post_images = geodir_get_images($post->ID, $options['limit']);
		

		if (!empty($post_images)) {
			$main_wrapper_class = "geodir-image-container";
			$second_wrapper_class = "geodir-image-wrapper";
			if($options['type']=='slider'){
				$main_wrapper_class .= " geodir_flex-container ";
				$second_wrapper_class .= " geodir_flexslider geodir-slider ";
			}else{
				//$main_wrapper_class = "geodir-image-container";
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

			?>
			<div class="<?php echo $main_wrapper_class;?>" >
				<?php if($options['type']=='slider'){ echo '<div class="geodir_flex-loader"><i class="fa fa-refresh fa-spin"></i></div>';}?>
				<div id="<?php echo $slider_id; ?>" class="<?php echo $second_wrapper_class;?>" <?php
				if($options['controlnav']==1){echo "data-controlnav='1'";}
				if($options['animation']=='fade'){echo "data-animation='fade'";}
				if($options['slideshow']){echo "data-slideshow='1'";}
				?>>
					<ul class="geodir-slides clearfix"><?php
						$image_count = 0;
						foreach($post_images as $image){
							echo "<li>";
							//print_r($image);
							$img_tag = geodir_get_image_tag($image,'medium');
							$meta = isset($image->metadata) ? maybe_unserialize($image->metadata) : '';
							$img_tag =  wp_image_add_srcset_and_sizes( $img_tag, $meta , 0 );

							// ajaxify images
							if($options['ajax_load'] && $image_count){
								$img_tag = geodir_image_tag_ajaxify($img_tag);
							}
							echo $img_tag;

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

}

