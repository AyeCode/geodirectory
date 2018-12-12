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
		global $gd_post,$geodirectory;
		ob_start();

		$lat = !empty($gd_post->latitude) ? esc_attr($gd_post->latitude) : '';
		$lon = !empty($gd_post->longitude) ? esc_attr($gd_post->longitude) : '';

		if(geodir_is_block_demo() && !$lat && !$lon){
			$default_location = $geodirectory->location->get_default_location();
			$lat = $default_location->latitude;
			$lon = $default_location->longitude;
		}

		if($lat && $lon) {
			?>
			<div class="geodir_post_meta  geodir_get_directions" style="clear:both;">
				<span class="geodir_post_meta_icon geodir-i-address" style=""><i class="fas fa-location-arrow" aria-hidden="true"></i></span>
				<span class="geodir_post_meta_title">
					<a href="https://maps.google.com/?daddr=<?php echo esc_attr($lat);?>,<?php echo esc_attr($lon);?>"
					target="_blank"><?php esc_attr_e( 'Get Directions', 'geodirectory' ); ?></a>
				</span>
			</div>

			<?php
		}

		return ob_get_clean();

	}

}

