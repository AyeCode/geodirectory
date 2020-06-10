<?php

/**
 * GeoDir_Widget_Post_Title class.
 *
 * @since 2.0.0
 */
class GeoDir_Widget_Post_Title extends WP_Super_Duper {

	public $arguments;
	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {

		$options = array(
			'textdomain'    => GEODIRECTORY_TEXTDOMAIN,
			'block-icon'    => 'minus',
			'block-category'=> 'common',
			'block-keywords'=> "['title','geo','geodir']",
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
			'base_id'       => 'gd_post_title', // this us used as the widget id and the shortcode id.
			'name'          => __('GD > Post Title','geodirectory'), // the name of the widget.
			'widget_ops'    => array(
				'classname'   => 'geodir-post-title', // widget class
				'description' => esc_html__('This shows a GD post title with link.','geodirectory'), // widget description
				'geodirectory' => true,
			),
			'arguments'     => array(
				'tag'  => array(
					'title' => __('Output Type:', 'geodirectory'),
					'desc' => __('Set the HTML tag for the title.', 'geodirectory'),
					'type' => 'select',
					'options'   =>  array(
						"h2" => "h2",
						"h3" => "h3",
						"h1" => "h1",
					),
					'default'  => 'h2',
					'desc_tip' => true,
					'advanced' => true
				)
			)
		);

		parent::__construct( $options );
	}

	/**
	 * The Super block output function.
	 *
	 * @param array $instance
	 * @param array $args
	 * @param string $content
	 *
	 * @return mixed|string|void
	 */
	public function output( $instance = array(), $args = array(), $content = '' ) {
		global $post, $gd_post;

		// options
		$defaults = array(
			'tag' => 'h2', // h1, h2, h3
		);

		$backup_post = $post;

		if ( empty( $post ) && ! empty( $gd_post ) ) {
			$post = get_post( $gd_post->ID );
		}

		/**
		 * Parse incoming $instance into an array and merge it with $defaults
		 */
		$instance = wp_parse_args( $instance, $defaults );

		/**
		 * Filter listing title tag.
		 *
		 * @since 2.0.0
		 *
		 * @param string $instance['tag'] Title tag.
		 * @param array $instance Widget settings.
		 * @param array $args Widget arguments.
		 * @param object $this The GeoDir_Widget_Post_Title object.
		 */
		$title_tag = empty( $instance['tag'] ) ? 'h2' : apply_filters( 'geodir_widget_gd_post_title_tag', $instance['tag'], $instance, $args, $this );

		ob_start();
		?>
		<<?php echo esc_attr($title_tag);?> class="geodir-entry-title">
			<a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( wp_sprintf( _x( 'View: %s', 'listing title hover', 'geodirectory' ), stripslashes( the_title_attribute( array( 'echo' => false ) ) ) ) ); ?>"><?php echo stripslashes( get_the_title() ); ?></a>
		</<?php echo esc_attr($title_tag);?>>
		<?php
		$output = ob_get_clean();

		/**
		 * Filter post title output.
		 *
		 * @since 2.0.0.94
		 *
		 * @param string $output Title output.
		 * @param string $title_tag Title tag.
		 * @param array $instance Widget settings.
		 * @param array $args Widget arguments.
		 * @param string $content Shortcode content.
		 * @param object $this The GeoDir_Widget_Post_Title object.
		 */
		$output = apply_filters( 'geodir_widget_post_title_output', $output, $title_tag, $instance, $args, $content, $this );

		$post = $backup_post;

		return $output;
	}

}
