<?php
/**
 * GD Page Title widget
 *
 * @package GeoDirectory
 * @since 2.0.0.93
 */

/**
 * GeoDir_Widget_Page_Title class.
 */
class GeoDir_Widget_Page_Title extends WP_Super_Duper {

	/**
	 * Sets up a widget instance.
	 */
	public function __construct() {
		$options = array(
			'textdomain'    => 'geodirectory',
			'block-icon'    => 'location-alt',
			'block-category'=> 'widgets',
			'block-keywords'=> "['title','header','geodir']",
			'block-output'  => array(
				array(
					'element' => 'h1',
					'class'   => '[%className%]',
					'element_require' => '[%tag%]=="h1"',
					'content' => __( "Demo title h1", "geodirectory "),
				),
				array(
					'element' => 'h2',
					'class'   => '[%className%]',
					'element_require' => '[%tag%]=="h2"',
					'content' => __( "Demo title h2", "geodirectory" ),
				),
				array(
					'element' => 'h3',
					'class'   => '[%className%]',
					'element_require' => '[%tag%]=="h3"',
					'content' => __( "Demo title h3", "geodirectory" ),
				),
				array(
					'element' => 'h4',
					'class'   => '[%className%]',
					'element_require' => '[%tag%]=="h4"',
					'content' => __( "Demo title h4", "geodirectory" ),
				),
				array(
					'element' => 'h5',
					'class'   => '[%className%]',
					'element_require' => '[%tag%]=="h5"',
					'content' => __( "Demo title h5", "geodirectory" ),
				),
				array(
					'element' => 'div',
					'class'   => '[%className%]',
					'element_require' => '[%tag%]=="div"',
					'content' => __( "Demo title div", "geodirectory" ),
				),
			),
			'class_name'    => __CLASS__,
			'base_id'       => 'gd_page_title',
			'name'          => __( 'GD > Page Title', 'geodirectory' ),
			'widget_ops'    => array(
				'classname'   => 'geodir-page-title-container',
				'description' => esc_html__( 'Displays the page title on GD pages.', 'geodirectory' ),
				'geodirectory' => true,
				'gd_wgt_showhide' => 'gd',
				'gd_wgt_restrict' => array()
			),
			'arguments' => array(
				'tag'  => array(
					'type' => 'select',
					'title' => __( 'Output Type:', 'geodirectory' ),
					'desc' => __( 'Set the HTML tag for the title.', 'geodirectory' ),
					'options' => array(
						"h1" => "h1",
						"h2" => "h2",
						"h3" => "h3",
						"div" => "div"
					),
					'default' => 'h1',
					'desc_tip' => true,
					'advanced' => false
				),
				'no_wrap' => array(
					'type' => 'checkbox',
					'title' => __( 'No Wrap:', 'geodirectory' ),
					'desc' => __( 'Remove widget main wrapping div.', 'geodirectory' ),
					'default' => '0',
					'advanced' => true
				),
				'title_container' => array(
					'type' => 'select',
					'title' => __( 'Title Container:', 'geodirectory' ),
					'desc' => __( 'Remove title container div.', 'geodirectory' ),
					'options' => array(
						"1" => __( 'Show', 'geodirectory' ),
						"0" => __( 'Hide', 'geodirectory' )
					),
					'default' => '1',
					'desc_tip' => true,
					'advanced' => true
				),
				'strip_html' => array(
					'type' => 'select',
					'title' => __( 'Strip HTML:', 'geodirectory' ),
					'desc' => __( 'Remove html tags & display clean title.', 'geodirectory' ),
					'options' => array(
						"0" => __( 'No', 'geodirectory' ),
						"1" => __( 'Yes', 'geodirectory' ),
					),
					'default' => '0',
					'desc_tip' => true,
					'advanced' => true
				),
				'alignment' => array(
					'type' => 'select',
					'title' => __( 'Alignment:', 'geodirectory' ),
					'desc' => __( 'How the title should be positioned on the page.', 'geodirectory' ),
					'options' => array(
						"" => __( 'None', 'geodirectory' ),
						"block" => __( 'Block', 'geodirectory' ),
						"left" => __( 'Left', 'geodirectory' ),
						"center" => __( 'Center', 'geodirectory' ),
						"right" => __( 'Right', 'geodirectory' ),
					),
					'desc_tip' => true,
					'advanced' => true,
					'element_require' => '[%title_container%]!="0"',
				),
				'text_alignment' => array(
					'type' => 'select',
					'title' => __( 'Text Align:', 'geodirectory' ),
					'desc' => __( 'How the text should be aligned.', 'geodirectory' ),
					'options' => array(
						"" => __( 'None', 'geodirectory' ),
						"left" => __( 'Left', 'geodirectory' ),
						"center" => __( 'Center', 'geodirectory' ),
						"right" => __( 'Right', 'geodirectory' ),
					),
					'desc_tip' => true,
					'advanced' => true,
					'element_require' => '[%title_container%]!="0"',
				),
				'container_class' => array(
					'type' => 'text',
					'title' => __( 'Title container css class:', 'geodirectory' ),
					'desc' => __( 'Give the title container an extra class so you can style things as you want.', 'geodirectory' ),
					'placeholder' => '',
					'default' => '',
					'desc_tip' => true,
					'advanced' => true,
					'element_require' => '[%title_container%]!="0"',
				),
				'css_class' => array(
					'type' => 'text',
					'title' => __( 'Title element css class:', 'geodirectory' ),
					'desc' => __( 'Give the title element an extra class so you can style things as you want.', 'geodirectory' ),
					'placeholder' => 'entry-title',
					'default' => 'entry-title',
					'desc_tip' => true,
					'advanced' => true,
				),
			)
		);

		parent::__construct( $options );
	}


	/**
	 * The widget output.
	 *
	 * @param array $instance
	 * @param array $args
	 * @param string $content
	 *
	 * @return mixed|string|void
	 */
	public function output( $instance = array(), $args = array(), $content = '' ) {
		global $geodirectory, $post, $gd_post;

		$instance = shortcode_atts( 
			array(
				'tag' => 'h1',
				'no_wrap' => '',
				'title_container' => '1',
				'strip_html' => '',
				'alignment' => '',
				'text_alignment' => '',
				'container_class' => '',
				'css_class' => 'entry-title'
			), 
			$instance, 
			'gd_page_title' 
		);
		if ( empty( $instance['tag'] ) ) {
			$instance['tag'] = 'h1';
		}

		$output = '';
		if ( $this->is_preview() ) {
			return $output;
		}

		// No GD page
		if ( ! geodir_is_geodir_page() ) {
			return;
		}

		// Title container class
		$container_class = 'geodir-page-title-wrap geodir-page-title-' . sanitize_html_class( $instance['tag'] );

		if ( $instance['container_class'] != '' ) {
			$container_class .= ' ' . esc_attr( $instance['container_class'] );
		}

		if ( $instance['text_alignment'] != '' ) {
			$container_class .= ' geodir-text-align' . $instance['text_alignment'];
		}

		if ( $instance['alignment'] != '' ) {
			$container_class .= ' geodir-align' . $instance['alignment'];
		}

		// Title class
		$title_class = 'geodir-page-title';

		if ( $instance['css_class'] != '' ) {
			$title_class .= ' ' . esc_attr( $instance['css_class'] );
		}

		$title = GeoDir_SEO::set_meta();
		$title = apply_filters( 'geodir_widget_page_title', $title, $instance, $args, $content );

		// Tag
		if ( ! empty( $title ) ) {
			$title_tag = '<' . esc_attr( $instance['tag'] ) . ' class="' . trim( $title_class ) . '">' . $title . '</' . esc_attr( $instance['tag'] ) . '>';
		} else {
			$title_tag = '';
		}

		// Strip tags.
		if ( ! empty( $instance['strip_html'] ) && ! empty( $title_tag ) ) {
			$title_tag = wp_strip_all_tags( $title_tag );
		}

		if ( ! empty( $instance['title_container'] ) && ! empty( $title_tag ) ) {
			$output = '<div class="' . trim( $container_class ) . '">' . $title_tag . '</div>';
		} else {
			$output = $title_tag;
		}

		return apply_filters( 'geodir_widget_page_title_output', $output, $title, $title_tag, $instance, $args, $content );
	}
}
