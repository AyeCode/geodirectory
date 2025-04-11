<?php

/**
 * GeoDir_Widget_Detail_Meta class.
 *
 * @since 2.0.0
 * @since 2.0.0.49 Added list_hide and list_hide_secondary options for more flexible designs.
 */
class GeoDir_Widget_Post_Meta extends WP_Super_Duper {

	public $arguments;

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {

		$options = array(
			'textdomain'       => GEODIRECTORY_TEXTDOMAIN,
			'block-icon'       => 'location-alt',
			'block-category'   => 'geodirectory',
			'block-keywords'   => "['geo', 'geodirectory', 'geodir']",
			'block-supports'   => array(
				'customClassName' => false,
			),
			'class_name'       => __CLASS__,
			'base_id'          => 'gd_post_meta', // this us used as the widget id and the shortcode id.
			'name'             => __( 'GD > Post Meta', 'geodirectory' ), // the name of the widget.
			'widget_ops'       => array(
				'classname'                   => 'geodir-post-meta-container ' . geodir_bsui_class(),
				// widget class
				'description'                 => esc_html__( 'This shows a post single post meta.', 'geodirectory' ),
				// widget description
				'customize_selective_refresh' => true,
				'geodirectory'                => true,
				'gd_wgt_showhide'             => 'show_on',
				'gd_wgt_restrict'             => array( 'gd-detail' ),
				//@todo implement this on all other widgets
			),
			'block_group_tabs' => array(
				'content'  => array(
					'groups' => array(
						__( 'Content', 'geodirectory' ),
						__( 'List View Hide', 'geodirectory' ),
					),
					'tab'    => array(
						'title'     => __( 'Content', 'geodirectory' ),
						'key'       => 'bs_tab_content',
						'tabs_open' => true,
						'open'      => true,
						'class'     => 'text-center flex-fill d-flex justify-content-center',
					),
				),
				'styles'   => array(
					'groups' => array(
						//__( 'Link styles', 'geodirectory' ),
						__( 'Typography', 'geodirectory' )
					),
					'tab'    => array(
						'title'     => __( 'Styles', 'geodirectory' ),
						'key'       => 'bs_tab_styles',
						'tabs_open' => true,
						'open'      => true,
						'class'     => 'text-center flex-fill d-flex justify-content-center',
					),
				),
				'advanced' => array(
					'groups' => array(
						__( 'Wrapper Styles', 'geodirectory' ),
						__( 'Advanced', 'geodirectory' ),
					),
					'tab'    => array(
						'title'     => __( 'Advanced', 'geodirectory' ),
						'key'       => 'bs_tab_advanced',
						'tabs_open' => true,
						'open'      => true,
						'class'     => 'text-center flex-fill d-flex justify-content-center',
					),
				),
			),
		);

		parent::__construct( $options );
	}

	/**
	 * Set the arguments later.
	 *
	 * @return array
	 */
	public function set_arguments() {

		$arguments    = array();
		$design_style = geodir_design_style();

		$arguments['title'] = array(
			'title'       => __( 'Title:', 'geodirectory' ),
			'desc'        => __( 'Extra main title if needed.', 'geodirectory' ),
			'type'        => 'text',
			'placeholder' => __( 'Extra main title if needed.', 'geodirectory' ),
			'default'     => '',
			'desc_tip'    => true,
			'advanced'    => true,
			'group'       => __( 'Content', 'geodirectory' ),
		);

		$arguments['id'] = array(
			'title'       => __( 'Post ID:', 'geodirectory' ),
			'desc'        => __( 'Leave blank to use current post id.', 'geodirectory' ),
			'type'        => 'number',
			'placeholder' => 'Leave blank to use current post id.',
			'desc_tip'    => true,
			'default'     => '',
			'advanced'    => false,
			'group'       => __( 'Content', 'geodirectory' ),
		);

		$arguments['key'] = array(
			'title'       => __( 'Key:', 'geodirectory' ),
			'desc'        => __( 'This is the custom field key.', 'geodirectory' ),
			'type'        => 'select',
			'placeholder' => 'website',
			'options'     => $this->get_custom_field_keys(),
			'desc_tip'    => true,
			'default'     => '',
			'advanced'    => false,
			'group'       => __( 'Content', 'geodirectory' ),
		);

		$arguments['show'] = array(
			'title'    => __( 'Show:', 'geodirectory' ),
			'desc'     => __( 'What part of the post meta to show.', 'geodirectory' ),
			'type'     => 'select',
			'options'  => array(
				''            => __( 'icon + label + value', 'geodirectory' ),
				'icon-value'  => __( 'icon + value', 'geodirectory' ),
				'label-value' => __( 'label + value', 'geodirectory' ),
				'icon'        => __( 'icon', 'geodirectory' ),
				'label'       => __( 'label', 'geodirectory' ),
				'value'       => __( 'value', 'geodirectory' ),
				'value-strip' => __( 'value (strip_tags)', 'geodirectory' ),
				'value-raw'   => __( 'value (saved in database)', 'geodirectory' ),
			),
			'desc_tip' => true,
			'advanced' => false,
			'group'    => __( 'Content', 'geodirectory' ),
		);

		$arguments['no_wrap'] = array(
			'title'           => __( 'No Wrap:', 'geodirectory' ),
			'desc'            => __( 'Remove wrapping div.', 'geodirectory' ),
			'type'            => 'checkbox',
			'default'         => '0',
			'element_require' => '( [%show%]=="value-strip" || [%show%]=="value-raw" )',
			'group'           => __( 'Content', 'geodirectory' ),
		);

		$arguments['location'] = array(
			'title'    => __( 'Output Location:', 'geodirectory' ),
			'desc'     => __( 'The location type to output.', 'geodirectory' ),
			'type'     => 'select',
			'options'  => $this->show_in_locations(),
			'desc_tip' => true,
			'advanced' => false,
			'group'    => __( 'Content', 'geodirectory' ),
		);

		$arguments['list_hide'] = array(
			'title'    => __( 'Hide item on view:', 'geodirectory' ),
			'desc'     => __( 'You can set at what view the item will become hidden.', 'geodirectory' ),
			'type'     => 'select',
			'options'  => array(
				''  => __( 'None', 'geodirectory' ),
				'2' => __( 'Grid view 2', 'geodirectory' ),
				'3' => __( 'Grid view 3', 'geodirectory' ),
				'4' => __( 'Grid view 4', 'geodirectory' ),
				'5' => __( 'Grid view 5', 'geodirectory' ),
			),
			'desc_tip' => true,
			'advanced' => false,
			'group'    => __( 'List View Hide', 'geodirectory' ),
		);

		$arguments['list_hide_secondary'] = array(
			'title'    => __( 'Hide secondary info on view', 'geodirectory' ),
			'desc'     => __( 'You can set at what view the secondary info such as label will become hidden.', 'geodirectory' ),
			'type'     => 'select',
			'options'  => array(
				''  => __( 'None', 'geodirectory' ),
				'2' => __( 'Grid view 2', 'geodirectory' ),
				'3' => __( 'Grid view 3', 'geodirectory' ),
				'4' => __( 'Grid view 4', 'geodirectory' ),
				'5' => __( 'Grid view 5', 'geodirectory' ),
			),
			'desc_tip' => true,
			'advanced' => false,
			'group'    => __( 'List View Hide', 'geodirectory' ),
		);


		$arguments['alignment'] = array(
			'title'    => __( 'Alignment:', 'geodirectory' ),
			'desc'     => __( 'How the item should be positioned on the page.', 'geodirectory' ),
			'type'     => 'select',
			'options'  => array(
				''       => __( 'None', 'geodirectory' ),
				'block'  => __( 'Block', 'geodirectory' ),
				'left'   => __( 'Left', 'geodirectory' ),
				'center' => __( 'Center', 'geodirectory' ),
				'right'  => __( 'Right', 'geodirectory' ),
			),
			'desc_tip' => true,
			'advanced' => false,
			'group'    => __( 'Typography', 'geodirectory' ),
		);

		$arguments['text_alignment'] = array(
			'title'    => __( 'Text Align:', 'geodirectory' ),
			'desc'     => __( 'How the text should be aligned.', 'geodirectory' ),
			'type'     => 'select',
			'options'  => array(
				''       => __( 'None', 'geodirectory' ),
				'left'   => __( 'Left', 'geodirectory' ),
				'center' => __( 'Center', 'geodirectory' ),
				'right'  => __( 'Right', 'geodirectory' ),
			),
			'desc_tip' => true,
			'advanced' => false,
			'group'    => __( 'Typography', 'geodirectory' ),
		);

		// text color
		$arguments['text_color'] = sd_get_text_color_input();

		// font size
		$arguments['font_size'] = sd_get_font_size_input();

		if ( $design_style ) {
			// margins
			$arguments['mt'] = geodir_get_sd_margin_input( 'mt' );
			$arguments['mr'] = geodir_get_sd_margin_input( 'mr' );
			$arguments['mb'] = geodir_get_sd_margin_input( 'mb' );
			$arguments['ml'] = geodir_get_sd_margin_input( 'ml' );

			// padding
			$arguments['pt'] = geodir_get_sd_padding_input( 'pt' );
			$arguments['pr'] = geodir_get_sd_padding_input( 'pr' );
			$arguments['pb'] = geodir_get_sd_padding_input( 'pb' );
			$arguments['pl'] = geodir_get_sd_padding_input( 'pl' );
		}

		$arguments['css_class'] = sd_get_class_input();

		return $arguments;
	}

	/**
	 * Gets an array of custom field keys.
	 *
	 * @return array
	 */
	public function get_custom_field_keys() {
		$fields = geodir_post_custom_fields( '', 'all', 'all', 'none' );
		$keys   = array();
		$keys[] = __( 'Select Key', 'geodirectory' );
		if ( ! empty( $fields ) ) {
			$address = array();
			foreach ( $fields as $field ) {
				$keys[ $field['htmlvar_name'] ] = $field['htmlvar_name'];
			}
		}

		// Advance fields
		$advance_fields = geodir_post_meta_advance_fields();
		if ( ! empty( $advance_fields ) ) {
			foreach ( $advance_fields as $field => $args ) {
				$keys[ $field ] = $field;
			}
		}

		return $keys;
	}

	/**
	 * Get the field output locations.
	 *
	 * @return array Output locations.
	 * @since 2.0.0.66
	 *
	 */
	public function show_in_locations() {
		$locations         = geodir_show_in_locations();
		$show_in_locations = array( '' => __( 'Auto', 'geodirectory' ) );

		foreach ( $locations as $value => $label ) {
			$value                       = str_replace( array( '[', ']' ), '', $value );
			$show_in_locations[ $value ] = $label;
		}

		return $show_in_locations;
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
	public function output( $args = array(), $widget_args = array(), $content = '' ) {
		global $aui_bs5, $post, $gd_post;

		/**
		 * @var int $ID Optional. The current post ID if empty.
		 * @var string $key The meta key : email
		 * @var string $show Optional. What to show, 'title', 'value' or 'all'. Default 'all'.
		 * @var string $align left,right,center or blank.. Default ''
		 * @var string $location The show in what location key. Default 'none'
		 */
		extract( $args, EXTR_SKIP );

		$args['location'] = ! empty( $args['location'] ) ? $args['location'] : 'none';

		$args = wp_parse_args(
			$args,
			array(
				'id'                  => ! empty( $gd_post->ID ) ? absint( $gd_post->ID ) : 0,
				'key'                 => '', // the meta key : email
				'show'                => '', // title,value (default blank, all)
				'list_hide'           => '',
				'list_hide_secondary' => '',
				'css_class'           => '',
				'alignment'           => '', // left,right,center
				'text_alignment'      => '', // left,right,center
				'location'            => 'none',
				'no_wrap'             => '',
				'mt'                  => '',
				'mb'                  => '',
				'mr'                  => '',
				'ml'                  => '',
				'pt'                  => '',
				'pb'                  => '',
				'pr'                  => '',
				'pl'                  => '',
			)
		);

		if ( empty( $args['id'] ) ) {
			$args['id'] = ! empty( $gd_post->ID ) ? absint( $gd_post->ID ) : 0;

			if ( ! empty( $args['id'] ) && (int) wp_is_post_revision( $args['id'] ) ) {
				$args['id'] = (int) wp_get_post_parent_id( $args['id'] );
			}
		}

		// maybe no wrap
		if ( $args['show'] == 'value-strip' ) {
			$args['no_wrap'] = true;
		}

		$design_style  = geodir_design_style();
		$block_preview = $this->is_block_content_call();

		if ( empty( $gd_post->ID ) && $block_preview && ! empty( $args['key'] ) ) {
			$args['id'] = geodir_get_post_id_with_content( $args['key'] );
		}

		$post_type = ! empty( $args['id'] ) ? get_post_type( $args['id'] ) : ( ! empty( $post->post_type ) ? $post->post_type : '' );

		// error checks
		$errors = array();
		if ( empty( $args['key'] ) ) {
			$errors[] = __( 'key is missing', 'geodirectory' );
		}
		if ( empty( $post_type ) ) {
			$errors[] = __( 'invalid post type', 'geodirectory' );
		}

		$output = '';
		if ( ! empty( $errors ) ) {
			$output .= implode( ', ', $errors );
		}

		// check if its demo content
		if ( $post_type == 'page' && ! empty( $args['id'] ) && geodir_is_block_demo() ) {
			$post_type = 'gd_place';
		}

		if ( geodir_is_gd_post_type( $post_type ) ) {

			$args['text_align'] = 'text-' . $args['text_alignment']; // set AUI preview

			$args['id'] = apply_filters( 'geodir_widget_post_meta_set_id', $args['id'], $args );

			$package_id = $this->is_preview() ? 0 : geodir_get_post_package_id( $args['id'], $post_type );
			$fields     = geodir_post_custom_fields( $package_id, 'all', $post_type, 'none' );

			$fields = $fields + geodir_post_meta_advance_fields( $post_type );

			if ( ! empty( $fields ) ) {
				$field = array();
				foreach ( $fields as $field_info ) {
					if ( $args['key'] == $field_info['htmlvar_name'] ) {
						$field = $field_info;
					}
				}

				if ( ! empty( $field ) ) {
					$field = geodir_stripslashes_field( $field );

					// apply standard css
					if ( ! empty( $args['css_class'] ) ) {
						$field['css_class'] .= ' ' . geodir_sanitize_html_class( $args['css_class'] ) . ' ';
					}

					// set text alignment class
					if ( $args['text_alignment'] != '' ) {
						$field['css_class'] .= $design_style ? ' text-' . sanitize_html_class( $args['text_alignment'] ) : ' geodir-text-align' . sanitize_html_class( $args['text_alignment'] );
					}

					// set alignment class
					if ( $args['alignment'] != '' ) {
						if ( $design_style ) {
							if ( $args['alignment'] == 'block' ) {
								$field['css_class'] .= ' d-block ';
							} elseif ( $args['alignment'] == 'left' ) {
								$field['css_class'] .= ( $aui_bs5 ? ' float-start ms-2 ' : ' float-left mr-2 ' );
							} elseif ( $args['alignment'] == 'right' ) {
								$field['css_class'] .= ( $aui_bs5 ? ' float-end me-2 ' : ' float-right ml-2 ' );
							} elseif ( $args['alignment'] == 'center' ) {
								$field['css_class'] .= ' mw-100 d-block mx-auto ';
							} else {
								$field['css_class'] .= ' clear-both ';
							}
						} else {
							$field['css_class'] .= $args['alignment'] == 'block' ? ' gd-d-block gd-clear-both ' : ' geodir-align' . sanitize_html_class( $args['alignment'] );
						}
					} elseif ( $design_style ) {
						$field['css_class'] .= ' clear-both ';
					}

					// set list_hide class
					if ( $args['list_hide'] == '2' ) {
						$field['css_class'] .= $design_style ? ' gv-hide-2 ' : ' gd-lv-2 ';
					}
					if ( $args['list_hide'] == '3' ) {
						$field['css_class'] .= $design_style ? ' gv-hide-3 ' : ' gd-lv-3 ';
					}
					if ( $args['list_hide'] == '4' ) {
						$field['css_class'] .= $design_style ? ' gv-hide-4 ' : ' gd-lv-4 ';
					}
					if ( $args['list_hide'] == '5' ) {
						$field['css_class'] .= $design_style ? ' gv-hide-5 ' : ' gd-lv-5 ';
					}

					// set list_hide_secondary class
					if ( $args['list_hide_secondary'] == '2' ) {
						$field['css_class'] .= $design_style ? ' gv-hide-s-2 ' : ' gd-lv-s-2 ';
					}
					if ( $args['list_hide_secondary'] == '3' ) {
						$field['css_class'] .= $design_style ? ' gv-hide-s-3 ' : ' gd-lv-s-3 ';
					}
					if ( $args['list_hide_secondary'] == '4' ) {
						$field['css_class'] .= $design_style ? ' gv-hide-s-4 ' : ' gd-lv-s-4 ';
					}
					if ( $args['list_hide_secondary'] == '5' ) {
						$field['css_class'] .= $design_style ? ' gv-hide-s-5 ' : ' gd-lv-s-5 ';
					}

					// wrapper class
					$wrap_class = geodir_build_aui_class( $args );
					$wrap_class .= ' ' .sd_build_aui_class( $args );

					$field['css_class'] .= ' ' . $wrap_class;

					$type = $field['type'];
					if ( isset( $args['key'] ) && $args['key'] == 'post_images' ) {
						$type = 'file';
					}

					$the_post = ! empty( $args['id'] ) && ! empty( $gd_post ) && ! empty( $gd_post->post_id ) && (int) $gd_post->post_id == (int) $args['id'] ? $gd_post : array();

					$output = apply_filters( "geodir_custom_field_output_{$type}", '', $args['location'], $field, $args['id'], $args['show'], $the_post );

					if ( ! $output && $this->is_preview() ) {
						$output = $this->get_field_preview( $args, $field );
					}

					// Return clean striped value.
					if ( $args['show'] == 'value-strip' && $output != '' ) {
						$output = wp_strip_all_tags( $output );
					}
				} else {
					//$output = __( 'Key does not exist', 'geodirectory' );
				}
			} else {
			}

			if ( ! $output && $this->is_preview() ) {
				$output = $this->get_field_preview( $args, $field );
			}

			$args['id'] = apply_filters( 'geodir_widget_post_meta_reset_id', $args['id'], $args );
		}

		return $output;
	}

	/**
	 * Get a field preview if empty.
	 *
	 * @param $field
	 * @param $key
	 *
	 * @return string
	 */
	public function get_field_preview( $args, $field = array() ) {
		$html = '';

		if ( ! empty( $args ) ) {
			$title  = esc_attr( ucwords( str_replace( '_', ' ', $args['key'] ) ) );

			if($args['key']== 'video'){
				$value = 'https://www.youtube.com/watch?v=eEzD-Y97ges';
			}elseif($args['key'] == 'virtual_tour' ){
				$value = '<div class="ratio ratio-16x9"><iframe border="0"  loading="lazy" src="https://my.matterport.com/show/?m=Zh14WDtkjdC&amp;play=1"></iframe></div>';
			}else{
				$value  = __( 'Placeholder', 'geodirectory' );
			}

			$icon   = '<i class="fas fa-tools"></i> ';
			$view   = ! empty( $args['show'] ) ? esc_attr( $args['show'] ) : '';
			$output = geodir_field_output_process( $view );


			$wrap_class  = !empty( $field['css_class'] ) ? esc_attr( $field['css_class'] ) : sd_build_aui_class( $args );
			$html = '<div class="geodir_post_meta  geodir-field-preview '.$wrap_class.'">';
			if ( $output == '' || isset( $output['icon'] ) ) {
				$html .= '<span class="geodir_post_meta_placeholder" >' . $icon;
			}
			if ( $output == '' || isset( $output['label'] ) ) {
				$html .= '<span class="geodir_post_meta_title " >' . $title . ': ' . '</span>';
			}
			if ( $output == '' || isset( $output['icon'] ) ) {
				$html .= '</span>';
			}
			if ( $output == '' || isset( $output['value'] ) ) {
				$html .= $value;
			}
			$html .= '</div>';
		}

		return $html;
	}
}
