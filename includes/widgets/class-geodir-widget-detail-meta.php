<?php

/**
 * GeoDirectory Detail Rating Stars Widget
 *
 * @since 2.0.0
 *
 * @package GeoDirectory
 */



class GeoDir_Hello_world extends WP_Super_Duper {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {


		$options = array(
			'class_name'    => __CLASS__,
			'base_id'       => 'gd_hello_world', // rewuired, this us used as the widget id and the shortcode id.
			'name'          => __('GD > Hello World','geodirectory'), // the name of the widget.
			'widget_ops'    => array(
				'classname'   => 'geodir-hello-world', // widget class
				'description' => esc_html__('My hello world widget','geodirectory'), // widget description
				),
			'arguments'     => array(
				'title'  => array(
					'name' => __('title', 'geodirectory'),
					'title' => __('title:', 'geodirectory'),
					'desc' => __('The widget title:', 'geodirectory'),
					'type' => 'text',
					'placeholder' => __('The widget placeholder', 'geodirectory'),
					'desc_tip' => true,
					'default'  => '',
					'advanced' => false
				),
			)


		);

		
		parent::__construct( $options );


	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {

		print_r( $instance );
		echo '###';
		print_r( $args );

		// outputs the content of the widget
		echo "hello world";
	}

	

}

//new GeoDir_SBW_Hello_world();
//add_action( 'widgets_init', function () {
//	//new GeoDir_SBW_Hello_world();
//	register_widget( 'GeoDir_Hello_world' );
//} );

//function theme_prefix_enqueue_script() {
//    wp_add_inline_script( 'wp-blocks', 'alert("hello world");' );
//}
//add_action( 'admin_enqueue_scripts', 'theme_prefix_enqueue_script' );


/**
 * GeoDir_Widget_Detail_Meta class.
 *
 * @since 2.0.0
 */
class GeoDir_Widget_Detail_Meta_Old extends WP_Widget {

	/**
	 * Sets up a new Detail Rating Stars widget instance.
	 *
	 * @since 2.0.0
	 * @access public
	 */
	public function __construct() {
		$widget_ops = array(
			'classname'                   => 'geodir-widget gd-widget-detail-meta',
			'description'                 => __( 'xDisplay rating stars on the listing detail page.', 'geodirectory' ),
			'customize_selective_refresh' => true,
			'geodirectory'                => true,
			'gd_show_pages'               => array( 'detail' ),
		);
		parent::__construct( 'detail_meta', __( 'GD > Detail Meta', 'geodirectory' ), $widget_ops );
	}

	/**
	 * Outputs the content for the current Detail Rating Stars widget instance.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param array $args Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance Settings for the current Detail Rating widget instance.
	 */
	public function widget( $args, $instance ) {
		if ( ! geodir_is_page( 'detail' ) ) {
			return;
		}

		/**
		 * Filters the widget title.
		 *
		 * @since 2.0.0
		 *
		 * @param string $title The widget title. Default 'Pages'.
		 * @param array $instance An array of the widget's settings.
		 * @param mixed $id_base The widget ID.
		 */
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

		ob_start();

		do_action( 'geodir_widget_before_detail_meta' );

		//print_r($instance);

		echo geodir_sc_single_meta( $instance, $content = '' );

		do_action( 'geodir_widget_after_detail_meta' );

		$content = ob_get_clean();

		$content = trim( $content );
		if ( empty( $content ) ) {
			return;
		}

		echo $args['before_widget'];

		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		echo $content;

		echo $args['after_widget'];
	}

	/**
	 * Handles updating settings for the current Detail Rating Stars widget instance.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param array $new_instance New settings for this instance as input by the user via
	 *                            WP_Widget::form().
	 * @param array $old_instance Old settings for this instance.
	 *
	 * @return array Updated settings to save.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['key']   = sanitize_text_field( $new_instance['key'] );

		return $instance;
	}

	/**
	 * Outputs the settings form for the Detail Rating Stars widget.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param array $instance Current settings.
	 */
	public function form( $instance ) {
		// Defaults
		$instance = wp_parse_args( (array) $instance,
			array(
				'title' => '',
				'key'   => '',
			)
		);

		$title = sanitize_text_field( $instance['title'] );
		?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'geodirectory' ); ?></label>
			<input class="widefat"
			       id="<?php echo $this->get_field_id( 'title' ); ?>"
			       name="<?php echo $this->get_field_name( 'title' ); ?>"
			       type="text"
			       value="<?php echo esc_attr( $title ); ?>"/></p>


		<p><label for="<?php echo $this->get_field_id( 'key' ); ?>"><?php _e( 'Key:', 'geodirectory' ); ?></label>
			<input class="widefat"
			       id="<?php echo $this->get_field_id( 'key' ); ?>"
			       name="<?php echo $this->get_field_name( 'key' ); ?>"
			       type="text"
			       value="<?php echo esc_attr( sanitize_text_field( $instance['key'] ) ); ?>"/></p>
		<?php
	}
}





class GeoDir_Widget_Detail_Meta extends WP_Super_Duper {


	public $arguments;
	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {

		$options = array(
			'textdomain'    => GEODIRECTORY_TEXTDOMAIN,
			'block-icon'    => 'location-alt',
			'block-category'=> 'common',
			'block-keywords'=> "['geo','geodirectory','geodir']",
//			'block-output'  => array(
//				'element::img'   => array(
//					'src' => geodir_plugin_url()."/assets/images/block-placeholder-map.png",
//					'alt' => __('Placeholder','geodirectory'),
//					'width' => '[%width%]',
//					'height' => '[%height%]',
//				)
//			),
			'class_name'    => __CLASS__,
			'base_id'       => 'gd_post_meta', // this us used as the widget id and the shortcode id.
			'name'          => __('GD > Post Meta','geodirectory'), // the name of the widget.
			'widget_ops'    => array(
				'classname'   => 'geodir-post-meta', // widget class
				'description' => esc_html__('This shows a post single post meta.','geodirectory'), // widget description
			),
			'arguments'     => array(
				'id'  => array(
					'name' => 'id',
					'title' => __('Post ID:', 'geodirectory'),
					'desc' => __('Leave blank to use current post id.', 'geodirectory'),
					'type' => 'number',
					'placeholder' => 'Leave blank to use current post id.',
					'desc_tip' => true,
					'default'  => '',
					'advanced' => false
				),
				'key'  => array(
					'name' => 'key',
					'title' => __('Key:', 'geodirectory'),
					'desc' => __('This is the custom field key.', 'geodirectory'),
					'type' => 'text',
					'placeholder' => 'website',
					'desc_tip' => true,
					'default'  => '',
					'advanced' => false
				),
				'show'  => array(
					'name' => 'show',
					'title' => __('Show:', 'geodirectory'),
					'desc' => __('What part of the post meta to show.', 'geodirectory'),
					'type' => 'select',
					'options'   =>  array(
						"all" => __('All', 'geodirectory'),
						"title" => __('Title', 'geodirectory'),
						"value" => __('Value', 'geodirectory'),
					),
					'desc_tip' => true,
					'advanced' => false
				),
				'alignment'  => array(
					'name' => 'alignment',
					'title' => __('Alignment:', 'geodirectory'),
					'desc' => __('How the item should be positioned on the page.', 'geodirectory'),
					'type' => 'select',
					'options'   =>  array(
						"" => __('None', 'geodirectory'),
						"left" => __('Left', 'geodirectory'),
						"center" => __('Center', 'geodirectory'),
						"right" => __('Right', 'geodirectory'),
					),
					'desc_tip' => true,
					'advanced' => false
				),
				'location'  => array(
					'name' => 'location',
					'title' => __('Location key:', 'geodirectory'),
					'desc' => __('Meta values can be filtered to show differently in some location types.', 'geodirectory'),
					'type' => 'text',
					'placeholder' => 'mapbubble',
					'desc_tip' => true,
					'default'  => '',
					'advanced' => false
				)
			)
		);


		parent::__construct( $options );


	}


	public function output($args = array(), $widget_args = array(),$content = ''){

		ob_start();
		
		/**
		 * @var int    $ID Optional. The current post ID if empty.
		 * @var string $key The meta key : email
		 * @var string $show Optional. What to show, 'title','value' or 'all'. Default 'all'.
		 * @var string $align left,right,center or blank.. Default ''
		 * @var string $location The show in what location key. Default 'none'
		 */
		extract($args, EXTR_SKIP);

		//print_r($args);echo '####';exit;

		//$widget_id = isset($widget_args['widget_id']) ? $widget_args['widget_id'] : 'shortcode';

		echo geodir_sc_single_meta( $args, $content = '' );


		return ob_get_clean();

	}


}

