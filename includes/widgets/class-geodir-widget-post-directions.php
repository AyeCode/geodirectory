<?php

/**
 * GeoDir_Widget_Post_Title class.
 *
 * @since 2.0.0
 */
class GeoDir_Widget_Post_Directions extends WP_Super_Duper {


	public $arguments;
	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {

		$options = array(
			'textdomain'    => GEODIRECTORY_TEXTDOMAIN,
			'block-icon'    => 'minus',
			'block-category'=> 'common',
			'block-keywords'=> "['directions','geo','geodir']",
			'block-output'   => array( // the block visual output elements as an array
				array(
					'element' => 'h1',
					'class'   => '[%className%]',
					'element_require' => '[%tag%]=="h1"',
					'content'   => __("Demo title h1","geodirectory"),
				),
				array(
					'element' => 'h2',
					'class'   => '[%className%]',
					'element_require' => '[%tag%]=="h2"',
					'content'   => __("Demo title h2","geodirectory"),
				),
				array(
					'element' => 'h3',
					'class'   => '[%className%]',
					'element_require' => '[%tag%]=="h3"',
					'content'   => __("Demo title h3","geodirectory"),
				),

			),
			'class_name'    => __CLASS__,
			'base_id'       => 'gd_post_directions', // this us used as the widget id and the shortcode id.
			'name'          => __('GD > Directions','geodirectory'), // the name of the widget.
			'widget_ops'    => array(
				'classname'   => 'geodir-post-directions', // widget class
				'description' => esc_html__('This shows a link to map directions to the current post.','geodirectory'), // widget description
				'geodirectory' => true,
			),
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
		global $gd_post;
		ob_start();

		if(isset($gd_post->latitude) && $gd_post->latitude) {
			?>
			<div class="geodir_post_meta  geodir_get_directions" style="clear:both;">
				<span class="geodir_post_meta_icon geodir-i-address" style=""><i class="fa fa-location-arrow"></i></span>
				<span class="geodir_post_meta_title">
					<a href="https://maps.google.com/?daddr=<?php echo esc_attr($gd_post->latitude);?>,<?php echo esc_attr($gd_post->longitude);?>"
					target="_blank"><?php esc_attr_e( 'Get Directions', 'geodirectory' ); ?></a>
				</span>
			</div>

			<?php
		}

		return ob_get_clean();

	}

}

