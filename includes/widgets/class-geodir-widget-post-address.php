<?php

/**
 * GeoDir_Widget_Post_Address class.
 *
 * @since 2.0.0
 */
class GeoDir_Widget_Post_Address extends WP_Super_Duper {


	public $arguments;
	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {

		$options = array(
			'textdomain'       => GEODIRECTORY_TEXTDOMAIN,
			'block-icon'       => 'admin-home',
			'block-category'   => 'geodirectory',
			'block-keywords'   => "['geo','address','location']",
			'block-supports'   => array(
				'customClassName' => false,
			),
			'class_name'       => __CLASS__,
			'base_id'          => 'gd_post_address', // this us used as the widget id and the shortcode id.
			'name'             => __( 'GD > Post Address', 'geodirectory' ), // the name of the widget.
			'widget_ops'       => array(
				'classname'                   => 'geodir-post-address-container ' . geodir_bsui_class(), // widget class
				'description'                 => esc_html__( 'This shows the post address formatted as required.', 'geodirectory' ), // widget description
				'customize_selective_refresh' => true,
				'geodirectory'                => true,
				'gd_wgt_showhide'             => 'show_on',
				'gd_wgt_restrict'             => array( 'gd-detail' ),
			),
			'block_group_tabs' => array(
				'content'  => array(
					'groups' => array(
						__( 'Output', 'geodirectory' ),
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
						__( 'Typography', 'geodirectory' ),
						__( 'Grid Visibility', 'geodirectory' ),
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

		$arguments['title']            = array(
			'title'       => __( 'Title:', 'geodirectory' ),
			'desc'        => __( 'Extra main title if needed.', 'geodirectory' ),
			'type'        => 'text',
			'placeholder' => __( 'Extra main title if needed.', 'geodirectory' ),
			'default'     => '',
			'desc_tip'    => true,
			'advanced'    => true,
			'group'       => __( 'Output', 'geodirectory' ),
		);
		$arguments['id']               = array(
			'title'       => __( 'Post ID:', 'geodirectory' ),
			'desc'        => __( 'Leave blank to use current post id.', 'geodirectory' ),
			'type'        => 'number',
			'placeholder' => 'Leave blank to use current post id.',
			'desc_tip'    => true,
			'default'     => '',
			'advanced'    => false,
			'group'       => __( 'Output', 'geodirectory' ),
		);
		$arguments['show']             = array(
			'title'    => __( 'Show:', 'geodirectory' ),
			'desc'     => __( 'What part of the post meta to show.', 'geodirectory' ),
			'type'     => 'select',
			'options'  => array(
				'icon-label-value' => __( 'icon + label + value', 'geodirectory' ),
				'icon-value'       => __( 'icon + value', 'geodirectory' ),
				'label-value'      => __( 'label + value', 'geodirectory' ),
				'label'            => __( 'label', 'geodirectory' ),
				'value'            => __( 'value', 'geodirectory' ),
			),
			'default'  => '',
			'desc_tip' => true,
			'advanced' => false,
			'group'    => __( 'Output', 'geodirectory' ),
		);
		$arguments['address_template'] = array(
			'title'       => __( 'Address template:', 'geodirectory' ),
			/* translators: %s: Available address tags. */
			'desc'        => sprintf( __( 'Enter the address tags as required, adding _br or _brc to the tag adds a line break or comma and line break after it. Available tags: %s', 'geodirectory' ), '%%street%% %%neighbourhood%% %%city%% %%region%% %%country%% %%zip%% %%latitude%% %%longitude%% %%post_title%% %%br%' ),
			'type'        => 'text',
			'placeholder' => '%%street_br%% %%neighbourhood_br%% %%city_br%% %%region_br%% %%zip_br%% %%country%%',
			'default'     => '',
			'desc_tip'    => true,
			'advanced'    => false,
			'group'       => __( 'Output', 'geodirectory' ),
		);
		$arguments['map_link']         = array(
			'type'            => 'checkbox',
			'title'           => __( 'Link to show address on Google Map:', 'geodirectory' ),
			'value'           => '1',
			'default'         => '',
			'element_require' => '[%show%]!="label"',
			'desc_tip'        => false,
			'advanced'        => true,
			'group'           => __( 'Output', 'geodirectory' ),
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
		$arguments = $arguments + sd_get_text_color_input_group();

		// font size
		$arguments = $arguments + sd_get_font_size_input_group();

		// line height
		$arguments['font_line_height'] = sd_get_font_line_height_input();

		// font weight
		$arguments['font_weight'] = sd_get_font_weight_input();

		$arguments['list_hide']           = array(
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
			'advanced' => true,
			'group'    => __( 'Grid Visibility', 'geodirectory' ),
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
			'advanced' => true,
			'group'    => __( 'Grid Visibility', 'geodirectory' ),
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
			'group'    => __( 'Wrapper Styles', 'geodirectory' ),
		);

		// margins mobile
		$arguments['mt'] = sd_get_margin_input( 'mt', array( 'device_type' => 'Mobile' ) );
		$arguments['mr'] = sd_get_margin_input( 'mr', array( 'device_type' => 'Mobile' ) );
		$arguments['mb'] = sd_get_margin_input( 'mb', array( 'device_type' => 'Mobile' ) );
		$arguments['ml'] = sd_get_margin_input( 'ml', array( 'device_type' => 'Mobile' ) );

		// margins tablet
		$arguments['mt_md'] = sd_get_margin_input( 'mt', array( 'device_type' => 'Tablet' ) );
		$arguments['mr_md'] = sd_get_margin_input( 'mr', array( 'device_type' => 'Tablet' ) );
		$arguments['mb_md'] = sd_get_margin_input( 'mb', array( 'device_type' => 'Tablet' ) );
		$arguments['ml_md'] = sd_get_margin_input( 'ml', array( 'device_type' => 'Tablet' ) );

		// margins desktop
		$arguments['mt_lg'] = sd_get_margin_input( 'mt', array( 'device_type' => 'Desktop' ) );
		$arguments['mr_lg'] = sd_get_margin_input( 'mr', array( 'device_type' => 'Desktop' ) );
		$arguments['mb_lg'] = sd_get_margin_input( 'mb', array( 'device_type' => 'Desktop' ) );
		$arguments['ml_lg'] = sd_get_margin_input( 'ml', array( 'device_type' => 'Desktop' ) );

		// padding
		$arguments['pt'] = sd_get_padding_input( 'pt', array( 'device_type' => 'Mobile' ) );
		$arguments['pr'] = sd_get_padding_input( 'pr', array( 'device_type' => 'Mobile' ) );
		$arguments['pb'] = sd_get_padding_input( 'pb', array( 'device_type' => 'Mobile' ) );
		$arguments['pl'] = sd_get_padding_input( 'pl', array( 'device_type' => 'Mobile' ) );

		// padding tablet
		$arguments['pt_md'] = sd_get_padding_input( 'pt', array( 'device_type' => 'Tablet' ) );
		$arguments['pr_md'] = sd_get_padding_input( 'pr', array( 'device_type' => 'Tablet' ) );
		$arguments['pb_md'] = sd_get_padding_input( 'pb', array( 'device_type' => 'Tablet' ) );
		$arguments['pl_md'] = sd_get_padding_input( 'pl', array( 'device_type' => 'Tablet' ) );

		// padding desktop
		$arguments['pt_lg'] = sd_get_padding_input( 'pt', array( 'device_type' => 'Desktop' ) );
		$arguments['pr_lg'] = sd_get_padding_input( 'pr', array( 'device_type' => 'Desktop' ) );
		$arguments['pb_lg'] = sd_get_padding_input( 'pb', array( 'device_type' => 'Desktop' ) );
		$arguments['pl_lg'] = sd_get_padding_input( 'pl', array( 'device_type' => 'Desktop' ) );

		// border
		$arguments['border']         = sd_get_border_input( 'border' );
		$arguments['border_type']    = sd_get_border_input( 'type' );
		$arguments['border_width']   = sd_get_border_input( 'width' ); // BS5 only
		$arguments['border_opacity'] = sd_get_border_input( 'opacity' ); // BS5 only
		$arguments['rounded']        = sd_get_border_input( 'rounded' );
		$arguments['rounded_size']   = sd_get_border_input( 'rounded_size' );

		// shadow
		$arguments['shadow'] = sd_get_shadow_input( 'shadow' );

		$arguments['display']    = sd_get_display_input( 'd', array( 'device_type' => 'Mobile' ) );
		$arguments['display_md'] = sd_get_display_input( 'd', array( 'device_type' => 'Tablet' ) );
		$arguments['display_lg'] = sd_get_display_input( 'd', array( 'device_type' => 'Desktop' ) );

		$arguments['css_class'] = sd_get_class_input();

		return $arguments;
	}

	//gd_wgt_showhide

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
		global $aui_bs5;

		/**
		 * @var int    $ID Optional. The current post ID if empty.
		 * @var string $key The meta key : email
		 * @var string $show Optional. What to show, 'title','value' or 'all'. Default 'all'.
		 * @var string $align left,right,center or blank.. Default ''
		 * @var string $location The show in what location key. Default 'none'
		 */
		//extract( $args, EXTR_SKIP );

		global $post, $gd_post;

		$original_id      = isset( $args['id'] ) ? $args['id'] : '';
		$args['location'] = ! empty( $args['location'] ) ? $args['location'] : 'none';
		$output           = '';
		$args             = wp_parse_args(
			$args,
			array(
				'id'                  => isset( $gd_post->ID ) ? absint( $gd_post->ID ) : 0,
				'key'                 => 'address',
				'show'                => 'icon-label-value', // title,value (default blank, all)
				'alignment'           => '', // left,right,center
				'text_alignment'      => '', // left,right,center
				'list_hide'           => '',
				'list_hide_secondary' => '',
				'address_template'    => '%%street_br%% %%neighbourhood_br%% %%city_br%% %%region_br%% %%zip_br%% %%country%%',
				'map_link'            => '',
				'location'            => 'none',
			)
		);

		if ( ! empty( $args['border_type'] ) && empty( $args['border'] ) ) {
			$args['border_type'] = '';
		}

		if ( empty( $args['id'] ) ) {
			$args['id'] = ! empty( $gd_post->ID ) ? absint( $gd_post->ID ) : 0;

			if ( ! empty( $args['id'] ) && (int) wp_is_post_revision( $args['id'] ) ) {
				$args['id'] = (int) wp_get_post_parent_id( $args['id'] );
			}
		}

		$design_style  = geodir_design_style();
		$block_preview = $this->is_block_content_call();

		if ( empty( $gd_post->ID ) && $block_preview && ! empty( $args['key'] ) ) {
			$args['id'] = geodir_get_post_id_with_content( $args['key'] );
		}

		$post_type = ! empty( $args['id'] ) ? get_post_type( $args['id'] ) : ( ! empty( $post->post_type ) ? $post->post_type : '' );

		// Error checks
		$errors = array();

		if ( empty( $args['key'] ) ) {
			$errors[] = __( 'key is missing', 'geodirectory' );
		}

		if ( empty( $post_type ) ) {
			$errors[] = __( 'invalid post type', 'geodirectory' );
		}

		if ( ! empty( $errors ) ) {
			$output .= implode( ', ', $errors );
		}

		// Check if its demo content
		if ( $post_type == 'page' && ! empty( $args['id'] ) && geodir_is_block_demo() ) {
			$post_type = 'gd_place';
		}

		if ( class_exists( 'FLBuilder' ) && isset( $_REQUEST['fl_builder'] ) ) {
			$output = ''; // Show placehoder on beaver builder preview.
		}

		if ( geodir_is_gd_post_type( $post_type ) ) {
			$args['id'] = apply_filters( 'geodir_widget_post_meta_set_id', $args['id'], $args );

			$package_id = $this->is_preview() ? 0 : geodir_get_post_package_id( $args['id'], $post_type );
			$fields = geodir_post_custom_fields( $package_id,  'all', $post_type , 'none' );

			if ( ! empty( $fields ) ) {
				$field = array();
				foreach ( $fields as $field_info ) {
					if ( $args['key'] === $field_info['htmlvar_name'] ) {
						$field = $field_info;
					}
				}

				if ( ! empty( $field ) ) {
					$field = stripslashes_deep( $field );

					// Apply CSS css
					if ( ! empty( $args['css_class'] ) ) {
						$field['css_class'] .= " " . geodir_sanitize_html_class( $args['css_class'] ) . " ";
					}

					// Set text alignment class
					if ( '' !== $args['text_alignment'] ) {
						$field['css_class'] .= $design_style ? ' text-' . sanitize_html_class( $args['text_alignment'] ) : ' geodir-text-align' . sanitize_html_class( $args['text_alignment'] );
					}

					// set alignment class
					if ( '' !== $args['alignment'] ) {
						if ( $design_style ) {
							if ( 'block' === $args['alignment'] ) {
								$field['css_class'] .= ' d-block ';
							} elseif ( 'left' === $args['alignment'] ) {
								$field['css_class'] .= ' float-left mr-2 ';
							} elseif ( 'right' === $args['alignment'] ) {
								$field['css_class'] .= ' float-right ml-2 ';
							} elseif ( 'center' === $args['alignment'] ) {
								$field['css_class'] .= ' mw-100 d-block mx-auto ';
							}
						} else {
							$field['css_class'] .= 'block' === $args['alignment'] ? ' gd-d-block gd-clear-both ' : ' geodir-align' . sanitize_html_class( $args['alignment'] );
						}
					}

					// Set list_hide class
					if ( '2' == $args['list_hide'] ) {
						$field['css_class'] .= $design_style ? ' gv-hide-2 ' : ' gd-lv-2 ';}
					if ( '3' == $args['list_hide'] ) {
						$field['css_class'] .= $design_style ? ' gv-hide-3 ' : ' gd-lv-3 ';}
					if ( '4' == $args['list_hide'] ) {
						$field['css_class'] .= $design_style ? ' gv-hide-4 ' : ' gd-lv-4 ';}
					if ( '5' == $args['list_hide'] ) {
						$field['css_class'] .= $design_style ? ' gv-hide-5 ' : ' gd-lv-5 '; }

					// set list_hide_secondary class
					if ( '2' === $args['list_hide_secondary'] ) {
						$field['css_class'] .= $design_style ? ' gv-hide-s-2 ' : ' gd-lv-s-2 ';}
					if ( '3' === $args['list_hide_secondary'] ) {
						$field['css_class'] .= $design_style ? ' gv-hide-s-3 ' : ' gd-lv-s-3 ';}
					if ( '4' === $args['list_hide_secondary'] ) {
						$field['css_class'] .= $design_style ? ' gv-hide-s-4 ' : ' gd-lv-s-4 ';}
					if ( '5' === $args['list_hide_secondary'] ) {
						$field['css_class'] .= $design_style ? ' gv-hide-s-5 ' : ' gd-lv-s-5 ';}

					if ( $design_style ) {
						$field['css_class'] .= ' ' . sd_build_aui_class( $args );
					}

					// set to value if empty
					if ( empty( $args['show'] ) ) {
						$args['show'] = 'icon-label-value';
					}

					if ( ! empty( $args['map_link'] ) ) {
						$args['show'] = str_replace( 'value', 'link', $args['show'] );
					}

					// Wrapper class
					$wrap_class = geodir_build_aui_class( $args );
					$field['css_class'] .= " ".$wrap_class;
					$field['address_template'] = $args['address_template'];

					// Unset the extra fields
					unset( $field['extra_fields'] );

					$output = apply_filters( "geodir_custom_field_output_{$field['type']}", '', $args['location'], $field, $args['id'], $args['show'] );
				}
			}

			if ( ! empty( $output ) && absint( $args['id'] ) ) {
				$output = geodir_post_address( $output, 'gd_post_address', absint( $args['id'] ) );
			}

			$args['id'] = apply_filters( 'geodir_widget_post_meta_reset_id', $args['id'], $args );
		}

		return $output;
	}
}
