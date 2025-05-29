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
			'block-category'=> 'geodirectory',
			'block-keywords'=> "['title','geo','geodir']",
			'class_name'    => __CLASS__,
			'base_id'       => 'gd_post_title', // this us used as the widget id and the shortcode id.
			'name'          => __('GD > Post Title','geodirectory'), // the name of the widget.
			'widget_ops'    => array(
				'classname'   => 'geodir-post-title '.geodir_bsui_class(), // widget class
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
					'advanced' => false
				)
			)
		);


		// add more options if using AUI
		$design_style = geodir_design_style();
		if($design_style){
			$options['arguments']['font_size_class'] = array(
				'title' => __('Font size', 'geodirectory'),
				'desc' => __('Set the font-size class for the title. These are bootstrap font sizes not, HTML tags.', 'geodirectory'),
				'type' => 'select',
				'options'   =>  array(
					"" => __("Default (h4)","geodirectory"),
					"h1" => "h1",
					"h2" => "h2",
					"h3" => "h3",
					"h4" => "h4",
					"h5" => "h5",
					"h6" => "h6",
					"display-1" => "display-1",
					"display-2" => "display-2",
					"display-3" => "display-3",
					"display-4" => "display-4",
				),
				'default'  => 'h5',
				'desc_tip' => true,
				'advanced' => false,
				'group'     => __("Design","geodirectory")
			);

			$options['arguments']['overflow'] = array(
				'title' => __('Text Overflow', 'geodirectory'),
				'desc' => __('Set what happens when text overflows its container.', 'geodirectory'),
				'type' => 'select',
				'options'   =>  array(
					"" => __("Line Break (default)","geodirectory"),
					"ellipsis" => __("Truncate with ellipsis...","geodirectory"),
				),
				'default'  => '',
				'desc_tip' => true,
				'advanced' => false,
				'group'     => __("Design","geodirectory")
			);


			// font color
			$options['arguments']['text_color'] = array(
				'title' => __('Font Color', 'geodirectory'),
				'desc' => __('Set the font color', 'geodirectory'),
				'type' => 'select',
				'options'   =>  array(
					                ''  =>  __("Default (inherit)","geodirectory"),
				                ) + geodir_aui_colors(),
				'default'  => '',
				'desc_tip' => true,
				'advanced' => false,
				'group'     => __("Design","geodirectory")
			);

			// margins
			$options['arguments']['mt']  = geodir_get_sd_margin_input('mt');
			$options['arguments']['mr']  = geodir_get_sd_margin_input('mr');
			$options['arguments']['mb']  = geodir_get_sd_margin_input('mb');
			$options['arguments']['ml']  = geodir_get_sd_margin_input('ml');

			// padding
			$options['arguments']['pt']  = geodir_get_sd_padding_input('pt');
			$options['arguments']['pr']  = geodir_get_sd_padding_input('pr');
			$options['arguments']['pb']  = geodir_get_sd_padding_input('pb');
			$options['arguments']['pl']  = geodir_get_sd_padding_input('pl');

			// text alignment
			$options['arguments']['text_align'] = geodir_get_sd_text_align_input();
		}

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
			'font_size_class' => 'h5', // h1, h2, h3
			'overflow' => '',
			'text_color' => '',
			'mt'    => '',
			'mb'    => '2',
			'mr'    => '',
			'ml'    => '',
			'pt'    => '',
			'pb'    => '',
			'pr'    => '',
			'pl'    => '',
		);

		$block_preview = $this->is_block_content_call();

		$backup_post = $post;

		if ( empty( $post ) && ! empty( $gd_post ) ) {
			$post = get_post( $gd_post->ID );
		}

		/**
		 * Parse incoming $instance into an array and merge it with $defaults
		 */
		$instance = wp_parse_args( $instance, $defaults );


		$allowed_tags = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' );
		$tag          = in_array( $instance['tag'], $allowed_tags ) ? esc_attr( $instance['tag'] ) : 'h2';
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
		$title_tag = apply_filters( 'geodir_widget_gd_post_title_tag', $tag, $instance, $args, $this );

		$design_style = geodir_design_style();
		$classes = '';
		$link_class = '';
		if($design_style){
			$classes = " " . sanitize_html_class($instance['font_size_class']);

			// text overflow
			if(!empty($instance['overflow']) && $instance['overflow'] == 'ellipsis'){
				$classes .= ' text-truncate';
			}

			// wrapper class
			$wrap_class = sd_build_aui_class($instance);
			$classes .= " ".$wrap_class;

			if ( !empty( $instance['text_color'] ) ) { $link_class .= "text-".sanitize_html_class($instance['text_color']); }
		}

		$title = get_the_title();
		if ( empty( $title ) && $block_preview ) {
			$title = "Demo title preview";
		}

		$title = (string) apply_filters( 'geodir_widget_post_title', $title, $instance, $args, $content );

		if ( $title ) {
			$title = strip_tags( stripslashes( $title ) );
		}

		$title_attribute = (string) the_title_attribute( array( 'echo' => false ) );
		if ( $title_attribute ) {
			$title_attribute = stripslashes( $title_attribute );
		}

		ob_start();
		?>
		<<?php echo esc_attr( $title_tag ); ?> class="geodir-entry-title <?php echo esc_attr( $classes ); ?>"><a href="<?php the_permalink(); ?>" class="<?php echo esc_attr( $link_class );?>" title="<?php echo esc_attr( wp_sprintf( _x( 'View: %s', 'listing title hover', 'geodirectory' ), $title_attribute ) ); ?>"><?php echo trim( esc_html( $title ) ); ?></a></<?php echo esc_attr( $title_tag ); ?>>
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
		$output = apply_filters( 'geodir_widget_post_title_output', trim( $output ), $title_tag, $instance, $args, $content, $this );

		$post = $backup_post;

		return $output;
	}

}
