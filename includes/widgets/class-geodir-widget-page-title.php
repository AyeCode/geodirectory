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
			'block-category'=> 'geodirectory',
			'block-keywords'=> "['title','header','geodir']",
			'class_name'    => __CLASS__,
			'base_id'       => 'gd_page_title',
			'name'          => __( 'GD > Page Title', 'geodirectory' ),
			'widget_ops'    => array(
				'classname'   => 'geodir-page-title-container '.geodir_bsui_class(),
				'description' => esc_html__( 'Displays the page title on GD pages.', 'geodirectory' ),
				'geodirectory' => true,
				'gd_wgt_showhide' => 'gd',
				'gd_wgt_restrict' => array()
			),
			'arguments' => array(
				'tag'  => array(
					'type' => 'select',
					'title' => __( 'Output Type:', 'geodirectory' ),
					'desc' => __( 'Set the HTML tag for the title. This is for SEO, the size is set in the design settings below.', 'geodirectory' ),
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

		// add more options if using AUI
		$design_style = geodir_design_style();
		if($design_style){
			$options['arguments']['font_size_class'] = array(
				'title' => __('Font size', 'geodirectory'),
				'desc' => __('Set the font-size class for the title. These are bootstrap font sizes not, HTML tags.', 'geodirectory'),
				'type' => 'select',
				'options'   =>  array(
					"" => __("Default (h1)","geodirectory"),
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
				'default'  => '',
				'desc_tip' => true,
				'advanced' => false,
				'group'     => __("Design","geodirectory")
			);

			// margins
			$arguments['mt']  = geodir_get_sd_margin_input('mt');
			$arguments['mr']  = geodir_get_sd_margin_input('mr');
			$arguments['mb']  = geodir_get_sd_margin_input('mb');
			$arguments['ml']  = geodir_get_sd_margin_input('ml');

			// padding
			$arguments['pt']  = geodir_get_sd_padding_input('pt');
			$arguments['pr']  = geodir_get_sd_padding_input('pr');
			$arguments['pb']  = geodir_get_sd_padding_input('pb');
			$arguments['pl']  = geodir_get_sd_padding_input('pl');

			// text alignment
			$arguments['text_align'] = geodir_get_sd_text_align_input();
		}

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
		global $aui_bs5, $geodirectory, $post, $gd_post;

		$instance = shortcode_atts(
			array(
				'tag' => 'h1',
				'no_wrap' => '',
				'title_container' => '1',
				'strip_html' => '',
				'alignment' => '',
				'text_alignment' => '',
				'container_class' => '',
				'css_class' => 'entry-title',
				'font_size_class'   => 'h1',
				'bg'    => '',
				'mt'    => '',
				'mb'    => '',
				'mr'    => '',
				'ml'    => '',
				'pt'    => '',
				'pb'    => '',
				'pr'    => '',
				'pl'    => '',
			),
			$instance,
			'gd_page_title'
		);
		if ( empty( $instance['tag'] ) ) {
			$instance['tag'] = 'h1';
		}

		if ( empty( $instance['font_size_class'] ) ) {
			$instance['font_size_class'] = 'h1';
		}

		// sanitize tag
		$instance['tag'] = in_array( $instance['tag'], array( 'h1', 'h2', 'h3', 'div' ), true ) ? esc_attr( $instance['tag'] ) : 'h1';

		$design_style = geodir_design_style();
		$block_preview = $this->is_block_content_call();
		$output = '';

		// Title container class
		$container_class = 'geodir-page-title-wrap geodir-page-title-' . sanitize_html_class( $instance['tag'] );

		// wrapper class
		$wrap_class = geodir_build_aui_class($instance);
		$container_class .= " ".$wrap_class;

		if ( $instance['container_class'] != '' ) {
			$container_class .= ' ' . geodir_sanitize_html_class( $instance['container_class'] );
		}

		if ( $instance['text_alignment'] != '' ) {
			$container_class .= $design_style ? " text-".sanitize_html_class( $instance['text_alignment'] ) : " geodir-text-align" . sanitize_html_class( $instance['text_alignment'] );
		}

		if ( $instance['alignment'] != '' ) {
			if($design_style){
				if($instance['alignment']=='block'){$container_class .= " d-block ";}
				elseif($instance['alignment']=='left'){$container_class .= ( $aui_bs5 ? ' float-start ms-2 ' : ' float-left mr-2 ' );}
				elseif($instance['alignment']=='right'){$container_class .= ( $aui_bs5 ? ' float-end me-2 ' : ' float-right ml-2 ' );}
				elseif($instance['alignment']=='center'){$container_class .= " mw-100 d-block mx-auto ";}
			}else{
				$container_class .= " geodir-align" . sanitize_html_class( $instance['alignment'] );
			}
		}

		// Title class
		$title_class = 'geodir-page-title';

		if ( $instance['css_class'] != '' ) {
			$title_class .= ' ' . esc_attr( $instance['css_class'] );
		}

		if ( $instance['font_size_class'] != '' ) {
			$title_class .= ' ' . esc_attr( $instance['font_size_class'] );
		}

		$title = GeoDir_SEO::set_meta();
		if(empty($title) && $block_preview ){$title = "Demo title preview";}
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
