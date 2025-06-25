<?php

/**
 * GeoDir_Widget_Detail_Meta class.
 *
 * @since 2.0.0
 * @since 2.0.0.49 Added list_hide and list_hide_secondary options for more flexible designs.
 */
class GeoDir_Widget_Post_Features extends WP_Super_Duper {

	public $arguments;

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {

		$options = array(
			'textdomain'       => GEODIRECTORY_TEXTDOMAIN,
			'block-icon'       => 'fas fa-th',
			'block-category'   => 'geodirectory',
			'block-keywords'   => "['geodirectory', 'features', 'list']",
			'block-supports'   => array(
				'customClassName' => false,
			),
			'class_name'       => __CLASS__,
			'base_id'          => 'gd_post_features', // this us used as the widget id and the shortcode id.
			'name'             => __( 'GD > Post Features', 'geodirectory' ), // the name of the widget.
			'widget_ops'       => array(
				'classname'                   => 'geodir-post-features-container ' . geodir_bsui_class(),
				// widget class
				'description'                 => esc_html__( 'This shows a list of post features.', 'geodirectory' ),
				// widget description
				'customize_selective_refresh' => true,
				'geodirectory'                => true,
		//              'gd_wgt_showhide'             => 'show_on',
		//              'gd_wgt_restrict'             => array( 'gd-detail' ),
		//              //@todo implement this on all other widgets
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
						__( 'List Style', 'geodirectory' ),
						__( 'List Item', 'geodirectory' ),
						__( 'Background', 'geodirectory' ),
						__( 'Icons', 'geodirectory' ),
						__( 'Labels', 'geodirectory' ),
						__( 'Values', 'geodirectory' ),
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
			'advanced'    => true,
			'group'       => __( 'Content', 'geodirectory' ),
		);

		$arguments['source'] = array(
			'title'    => __( 'Source', 'geodirectory' ),
			'type'     => 'select',
			'options'  => array(
				'meta_auto'   => __( 'Custom fields (auto)', 'geodirectory' ),
				'meta_manual' => __( 'Custom fields (manual)', 'geodirectory' ),
				'tags'        => __( 'Tags', 'geodirectory' ),
			),
			'desc_tip' => true,
			'default'  => 'meta_auto',
			'advanced' => false,
			'group'    => __( 'Content', 'geodirectory' ),
		);

		$arguments['tag_link'] = array(
			'title'    => __( 'Link to tags', 'geodirectory' ),
			'type'     => 'select',
			'options'  => array(
				''   => __( 'No', 'geodirectory' ),
				'yes' => __( 'Yes', 'geodirectory' ),
			),
			'desc_tip' => true,
			'default'  => '',
			'advanced' => false,
			'group'    => __( 'Content', 'geodirectory' ),
			'element_require' => '[%source%]=="tags"',
		);

		$arguments['tag_include'] = array(
			'title'       => __( 'Specific tags', 'geodirectory' ),
			'desc'        => __( 'Enter a comma separated list of tag slugs to only show them specifically (otherwise any tag will show)', 'geodirectory' ),
			'type'        => 'text',
			'placeholder' => 'parking,cafe,swimming-pool',
			'desc_tip'    => true,
			'default'     => '',
//			'advanced'    => true,
			'group'       => __( 'Content', 'geodirectory' ),
			'element_require' => '[%source%]=="tags"',
		);

		$arguments['tag_count'] = array(
			'title'       => __( 'Tag count limit', 'geodirectory' ),
			'desc'        => __( 'Limit the number of tags shown', 'geodirectory' ),
			'type'        => 'number',
			'placeholder' => '16',
			'desc_tip'    => true,
			'default'     => '',
//			'advanced'    => true,
			'group'       => __( 'Content', 'geodirectory' ),
			'element_require' => '[%source%]=="tags"',
		);

		$arguments['source_auto_notice'] = array(
			'type'            => 'notice',
			'desc'            => __( 'This uses the custom fields output locations.', 'geodirectory' ),
			'status'          => 'info',
			'group'           => __( 'Content', 'geodirectory' ),
			'element_require' => '[%source%]=="meta_auto"',
		);

		$custom_fields = $this->get_custom_field_keys();
		$i             = 1;
		while ( $i <= 16 ) {

			$prev                     = $i - 1;
			$arguments[ 'key_' . $i ] = array(
				'title'                    => __( 'Field', 'geodirectory' ) . ' ' . $i,
				//              'desc'        => __( 'This is the custom field key.', 'geodirectory' ),
									'type' => 'select',
				'placeholder'              => 'website',
				'options'                  => $custom_fields,
				'desc_tip'                 => true,
				'default'                  => '',
				'advanced'                 => false,
				'group'                    => __( 'Content', 'geodirectory' ),
				'element_require'          => $i === 1 ? '[%source%]=="meta_manual"' : '( [%source%]=="meta_manual" && [%key_' . $prev . '%]!=0 )',
			);

			$i++;
		}

		//      $arguments['location'] = array(
		//          'title'           => __( 'Output Location:', 'geodirectory' ),
		//          'desc'            => __( 'The location type to output.', 'geodirectory' ),
		//          'type'            => 'select',
		//          'options'         => $this->show_in_locations(),
		//          'desc_tip'        => true,
		//          'advanced'        => false,
		//          'group'           => __( 'Content', 'geodirectory' ),
		//          'element_require' => '[%source%]=="meta_auto"',
		//
		//      );

		// row-cols
		$arguments['row_cols']    = sd_get_row_cols_input(
			'row_cols',
			array(
				'device_type'     => 'Mobile',
				'group'           => __( 'List Style', 'geodirectory' ),
				'element_require' => '',
			)
		);
		$arguments['row_cols_md'] = sd_get_row_cols_input(
			'row_cols',
			array(
				'device_type'     => 'Tablet',
				'group'           => __( 'List Style', 'geodirectory' ),
				'element_require' => '',
			)
		);
		$arguments['row_cols_lg'] = sd_get_row_cols_input(
			'row_cols',
			array(
				'device_type'     => 'Desktop',
				'group'           => __( 'List Style', 'geodirectory' ),
				'element_require' => '',
				'default'  => 1,
			)
		);

		$arguments['gy'] = array(
			'title'    => __( 'Row gap', 'geodirectory' ),
			'type'     => 'select',
			'options'  => array(
				'0'  => __( 'Default', 'geodirectory' ),
				'1'  => 1,
				'2'  => 2,
				'3'  => 3,
				'4'  => 4,
				'5'  => 5,
				'6'  => 6,
				'7'  => 7,
				'8'  => 8,
				'9'  => 9,
				'10' => 10,
			),
			'default'  => 2,
			'desc_tip' => true,
			'advanced' => false,
			'group'    => __( 'List Style', 'geodirectory' ),
		);

		// Text justify
		$arguments['text_justify'] = sd_get_text_justify_input(
			'text_justify',
			array(
				'group' => __( 'List Style', 'geodirectory' ),
			)
		);

		// text align
		$arguments['text_align']    = sd_get_text_align_input(
			'text_align',
			array(
				'device_type'     => 'Mobile',
				'element_require' => '[%text_justify%]==""',//'![%text_justify%:checked]', // @todo update after SD v1.1.30 release with UWP
				'group'           => __( 'List Style', 'geodirectory' ),
			)
		);
		$arguments['text_align_md'] = sd_get_text_align_input(
			'text_align',
			array(
				'device_type'     => 'Tablet',
				'element_require' => '[%text_justify%]==""',//'![%text_justify%:checked]',
				'group'           => __( 'List Style', 'geodirectory' ),
			)
		);
		$arguments['text_align_lg'] = sd_get_text_align_input(
			'text_align',
			array(
				'device_type'     => 'Desktop',
				'element_require' => '[%text_justify%]==""',//'![%text_justify%:checked]',
				'group'           => __( 'List Style1', 'geodirectory' ),
			)
		);

		// list item
		// border
		$arguments['item_border']         = sd_get_border_input( 'item_border', array(
			'title'           => __( 'Border Bottom Color', 'geodirectory' ),
			'group'           => __( 'List Item', 'geodirectory' ),
		) );

		// padding
		$arguments['item_pt'] = sd_get_padding_input( 'pt', array(
			'icon'  => '',
			'row'  => '',
			'element_require' => '[%item_border%]',
			'group'           => __( 'List Item', 'geodirectory' ),
		) );

		$arguments['item_pb'] = sd_get_padding_input( 'pb', array(
			'icon'  => '',
			'row'  => '',
			'element_require' => '[%item_border%]',
			'group'           => __( 'List Item', 'geodirectory' ),
		) );

		$arguments['item_border_width']   = sd_get_border_input( 'width', array(
			'element_require' => '[%item_border%]',
			'group'           => __( 'List Item', 'geodirectory' ),
		) ); // BS5 only
		$arguments['item_border_opacity'] = sd_get_border_input( 'opacity', array(
			'element_require' => '[%item_border%]',
			'group'           => __( 'List Item', 'geodirectory' ),
		) ); // BS5 only

		$arguments['list_display_flex']    = array(
			'title'                    => __( 'Display Flex', 'geodirectory' ),
			'type'                     => 'select',
			'options'                  => array(
				''           => __( 'No', 'geodirectory' ),
				'd-flex'  => __( 'yes', 'geodirectory' ),
			),
			'desc_tip' => true,
			'advanced'                 => false,
			'group'                    => __( 'List Item', 'geodirectory' ),
		);

		// flex align items
		$arguments = $arguments + sd_get_flex_align_items_input_group( 'list_flex_align_items',array(
				'element_require' => '[%list_display_flex%]',
				'group'           => __( 'List Item', 'geodirectory' ),
			)  );
		$arguments = $arguments + sd_get_flex_justify_content_input_group( 'list_flex_justify_content',array(
				'element_require' => '[%list_display_flex%]',
				'group'           => __( 'List Item', 'geodirectory' ),
			) );



		$arguments = $arguments + sd_get_background_inputs( 'bg' );

		$arguments['tag_icon'] = array(
			'title'       => __( 'Icon Class', 'geodirectory' ),
			'desc'        => __( 'Enter a Font Awesome icon class', 'geodirectory' ),
			'type'        => 'text',
			'placeholder' => 'far fa-check-circle',
			'desc_tip'    => true,
			'default'     => '',
//			'advanced'    => true,
			'group'       => __( 'Icons', 'geodirectory' ),
			'element_require' => '[%source%]=="tags"',
		);

		$arguments['icon_box'] = array(
			'title'                    => __( 'Show as iconbox', 'geodirectory' ),
			'type'                     => 'select',
			'options'                  => array(
				''           => __( 'No', 'geodirectory' ),
				'iconsmall'  => __( 'Small', 'geodirectory' ),
				'iconmedium' => __( 'Medium', 'geodirectory' ),
				'iconlarge'  => __( 'Large', 'geodirectory' ),
			),
			//          'default'   => 1,
							'desc_tip' => true,
			'advanced'                 => false,
			'group'                    => __( 'Icons', 'geodirectory' ),
		);
		$arguments['icon_box_color'] = array(
			'title'                    => __( 'Iconbox color', 'geodirectory' ),
			'type'                     => 'select',
			'options'                  => sd_aui_colors( false, false, false, true ),
			//          'default'   => 1,
							'desc_tip' => true,
			'advanced'                 => false,
			'group'                    => __( 'Icons', 'geodirectory' ),
			'element_require' => '[%icon_box%]!=""',
		);
		// text color
		$arguments['icon_text_color'] = sd_get_text_color_input( 'text_color', array( 'group' => __( 'Icons', 'geodirectory' ), 'default'  => 'muted', ) );
		// font size
		$arguments['icon_font_size'] = sd_get_font_size_input( 'font_size', array( 'group' => __( 'Icons', 'geodirectory' ) ) );
		// font weight
		$arguments['icon_font_weight'] = sd_get_font_weight_input( 'font_weight', array( 'group' => __( 'Icons', 'geodirectory' ) ) );
		// display
		$arguments['icon_display']    = sd_get_display_input(
			'd',
			array(
				'device_type' => 'Mobile',
				'group'       => __(
					'Icons',
					'geodirectory'
				),
			)
		);
		$arguments['icon_display_md'] = sd_get_display_input(
			'd',
			array(
				'device_type' => 'Tablet',
				'group'       => __(
					'Icons',
					'geodirectory'
				),
			)
		);
		$arguments['icon_display_lg'] = sd_get_display_input(
			'd',
			array(
				'device_type' => 'Desktop',
				'group'       => __(
					'Icons',
					'geodirectory'
				),
			)
		);
		// padding
		$arguments['icon_mt']    = sd_get_margin_input( 'mt', array( 'group' => __( 'Icons', 'geodirectory' ) ) );
		$arguments['icon_mr']    = sd_get_margin_input( 'mr', array( 'group' => __( 'Icons', 'geodirectory' ),'default'   => 1 ) );
		$arguments['icon_mb']    = sd_get_margin_input( 'mb', array( 'group' => __( 'Icons', 'geodirectory' ) ) );
		$arguments['icon_ml']    = sd_get_margin_input( 'ml', array( 'group' => __( 'Icons', 'geodirectory' ) ) );
		$arguments['icon_order'] = array(
			'title'    => __( 'Display order', 'geodirectory' ),
			'type'     => 'select',
			'options'  => array(
				'1' => 1,
				'2' => 2,
				'3' => 3,
			),
			'default'  => 1,
			'desc_tip' => true,
			'advanced' => false,
			'group'    => __( 'Icons', 'geodirectory' ),
		);

		// text color
		$arguments['label_text_color'] = sd_get_text_color_input( 'text_color', array( 'group' => __( 'Labels', 'geodirectory' ) ) );
		// font size
		$arguments['label_font_size'] = sd_get_font_size_input( 'font_size', array( 'group' => __( 'Labels', 'geodirectory' ) ) );
		// font weight
		$arguments['label_font_weight'] = sd_get_font_weight_input( 'font_weight', array( 'group' => __( 'Labels', 'geodirectory' ) ) );
		// display
		$arguments['label_display']    = sd_get_display_input(
			'd',
			array(
				'device_type' => 'Mobile',
				'group'       => __(
					'Labels',
					'geodirectory'
				),
			)
		);
		$arguments['label_display_md'] = sd_get_display_input(
			'd',
			array(
				'device_type' => 'Tablet',
				'group'       => __(
					'Labels',
					'geodirectory'
				),
			)
		);
		$arguments['label_display_lg'] = sd_get_display_input(
			'd',
			array(
				'device_type' => 'Desktop',
				'group'       => __(
					'Labels',
					'geodirectory'
				),
				'default'   => 'd-lg-inline'
			)
		);
		// padding
		$arguments['label_mt']    = sd_get_margin_input( 'mt', array( 'group' => __( 'Labels', 'geodirectory' ) ) );
		$arguments['label_mr']    = sd_get_margin_input( 'mr', array( 'group' => __( 'Labels', 'geodirectory' ),'default'   => 2 ) );
		$arguments['label_mb']    = sd_get_margin_input( 'mb', array( 'group' => __( 'Labels', 'geodirectory' ) ) );
		$arguments['label_ml']    = sd_get_margin_input( 'ml', array( 'group' => __( 'Labels', 'geodirectory' ) ) );
		$arguments['label_order'] = array(
			'title'    => __( 'Display order', 'geodirectory' ),
			'type'     => 'select',
			'options'  => array(
				'1' => 1,
				'2' => 2,
				'3' => 3,
			),
			'default'  => 2,
			'desc_tip' => true,
			'advanced' => false,
			'group'    => __( 'Labels', 'geodirectory' ),
		);
		// remove colon
		$arguments['label_colon_remove'] = array(
			'title'    => __( 'Remove label colon', 'geodirectory' ),
			'type'     => 'select',
			'options'  => array(
				'0' => __( 'No', 'geodirectory' ),
				'1' => __( 'Yes', 'geodirectory' ),
			),
			'default'  => 1,
			'desc_tip' => true,
			'advanced' => false,
			'group'    => __( 'Labels', 'geodirectory' ),
		);

		// text color
		$arguments['value_text_color'] = sd_get_text_color_input( 'text_color', array( 'group' => __( 'Values', 'geodirectory' ) ) );
		// font size
		$arguments['value_font_size'] = sd_get_font_size_input( 'font_size', array( 'group' => __( 'Values', 'geodirectory' ) ) );
		// font weight
		$arguments['value_font_weight'] = sd_get_font_weight_input( 'font_weight', array( 'group' => __( 'Values', 'geodirectory' ),'default'  => 'font-weight-bold', ) );
		// display
		$arguments['value_display']    = sd_get_display_input(
			'd',
			array(
				'device_type' => 'Mobile',
				'group'       => __(
					'Values',
					'geodirectory'
				),
			)
		);
		$arguments['value_display_md'] = sd_get_display_input(
			'd',
			array(
				'device_type' => 'Tablet',
				'group'       => __(
					'Values',
					'geodirectory'
				),
			)
		);
		$arguments['value_display_lg'] = sd_get_display_input(
			'd',
			array(
				'device_type' => 'Desktop',
				'group'       => __(
					'Values',
					'geodirectory'
				),
				'default'   => 'd-lg-inline'
			)
		);
		// padding
		$arguments['value_mt']    = sd_get_margin_input( 'mt', array( 'group' => __( 'Values', 'geodirectory' ) ) );
		$arguments['value_mr']    = sd_get_margin_input( 'mr', array( 'group' => __( 'Values', 'geodirectory' ) ) );
		$arguments['value_mb']    = sd_get_margin_input( 'mb', array( 'group' => __( 'Values', 'geodirectory' ) ) );
		$arguments['value_ml']    = sd_get_margin_input( 'ml', array( 'group' => __( 'Values', 'geodirectory' ) ) );
		$arguments['value_order'] = array(
			'title'    => __( 'Display order', 'geodirectory' ),
			'type'     => 'select',
			'options'  => array(
				'1' => 1,
				'2' => 2,
				'3' => 3,
			),
			'default'  => 3,
			'desc_tip' => true,
			'advanced' => false,
			'group'    => __( 'Values', 'geodirectory' ),
		);

		// margins mobile
		$arguments['mt'] = sd_get_margin_input( 'mt', array( 'device_type' => 'Mobile' ) );
		$arguments['mr'] = sd_get_margin_input( 'mr', array( 'device_type' => 'Mobile' ) );
		$arguments['mb'] = sd_get_margin_input(
			'mb',
			array(
				'device_type' => 'Mobile',
				//              'default'     => 3,
			)
		);
		$arguments['ml'] = sd_get_margin_input( 'ml', array( 'device_type' => 'Mobile' ) );

		// margins tablet
		$arguments['mt_md'] = sd_get_margin_input( 'mt', array( 'device_type' => 'Tablet' ) );
		$arguments['mr_md'] = sd_get_margin_input( 'mr', array( 'device_type' => 'Tablet' ) );
		$arguments['mb_md'] = sd_get_margin_input( 'mb', array( 'device_type' => 'Tablet' ) );
		$arguments['ml_md'] = sd_get_margin_input( 'ml', array( 'device_type' => 'Tablet' ) );

		// margins desktop
		$arguments['mt_lg'] = sd_get_margin_input( 'mt', array( 'device_type' => 'Desktop' ) );
		$arguments['mr_lg'] = sd_get_margin_input( 'mr', array( 'device_type' => 'Desktop' ) );
		$arguments['mb_lg'] = sd_get_margin_input(
			'mb',
			array(
				'device_type' => 'Desktop',
				'default'     => 3,
			)
		);
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

		// position
		$arguments['position'] = sd_get_position_class_input( 'position' );

		$arguments['sticky_offset_top']    = sd_get_sticky_offset_input( 'top' );
		$arguments['sticky_offset_bottom'] = sd_get_sticky_offset_input( 'bottom' );

		$arguments['display']    = sd_get_display_input( 'd', array( 'device_type' => 'Mobile' ) );
		$arguments['display_md'] = sd_get_display_input( 'd', array( 'device_type' => 'Tablet' ) );
		$arguments['display_lg'] = sd_get_display_input( 'd', array( 'device_type' => 'Desktop' ) );

		// flex align items
		$arguments = $arguments + sd_get_flex_align_items_input_group( 'flex_align_items' );
		$arguments = $arguments + sd_get_flex_justify_content_input_group( 'flex_justify_content' );
		$arguments = $arguments + sd_get_flex_align_self_input_group( 'flex_align_self' );
		$arguments = $arguments + sd_get_flex_order_input_group( 'flex_order' );

		// overflow
		$arguments['overflow'] = sd_get_overflow_input();

		// Max height
		$arguments['max_height'] = sd_get_max_height_input();

		// scrollbars
		$arguments['scrollbars'] = sd_get_scrollbars_input();

		// Hover animations
		$arguments['hover_animations'] = sd_get_hover_animations_input();

		// block visibility conditions
		$arguments['visibility_conditions'] = sd_get_visibility_conditions_input();

		// advanced
		$arguments['anchor'] = sd_get_anchor_input();

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

		$args['location'] = 'none';

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

		if ( geodir_is_page( 'single' ) ) {
			$args['location'] = 'detail';
		} elseif ( geodir_is_page( 'archive' ) ) {
			$args['location'] = 'listing';
		}

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

		if ( empty( $gd_post->ID ) && $block_preview && ! empty( $args['key_1'] ) ) {
			$args['id'] = geodir_get_post_id_with_content( $args['key_1'] );
		}

		$post_type = ! empty( $args['id'] ) ? get_post_type( $args['id'] ) : ( ! empty( $post->post_type ) ? $post->post_type : '' );

		// check if its demo content
		if ( $block_preview ) {
			$args['location'] = 'detail';
			$post_type        = 'gd_place';
		}

		// error checks
		$errors = array();
		if ( empty( $post_type ) ) {
			$errors[] = __( 'invalid post type', 'geodirectory' );
		}

		$output = '';
		if ( ! empty( $errors ) ) {
			$output .= implode( ', ', $errors );
		}


		if ( geodir_is_gd_post_type( $post_type ) ) {

			$args['text_align'] = 'text-' . $args['text_alignment']; // set AUI preview

			$args['id'] = apply_filters( 'geodir_widget_post_meta_set_id', $args['id'], $args );

			$package_id = $this->is_preview() ? 0 : geodir_get_post_package_id( $args['id'], $post_type );
			$fields =  array();
			$tags = array();
			$features = array();
			if ( 'meta_manual' === $args['source'] ) {
				$field_keys = array();
				$i          = 1;
				while ( $i <= 16 ) {
					if ( ! empty( $args[ 'key_' . $i ] ) ) {
						$field_keys[] = esc_attr( $args[ 'key_' . $i ] );
					}
					$i++;
				}

				$all_fields = geodir_post_custom_fields( $package_id, 'all', $post_type, $args['location'] );

				$fields_keys = array();
				foreach($all_fields  as $field){
					$fields_keys[$field['htmlvar_name']] = $field;
				}

				$all_fields = $fields_keys;

				// Advance fields
				$advance_fields = geodir_post_meta_advance_fields();


				if ( ! empty( $field_keys ) ) {
					foreach ( $field_keys as $field_key ) {
						if(!empty($all_fields[$field_key])){
							$fields[] = $all_fields[$field_key];
						}elseif(isset($advance_fields[$field_key])){

							$field = $advance_fields[$field_key];
							$field['advanced_field'] = true;
							$field['key'] = esc_attr($advance_fields[$field_key]['htmlvar_name']);
							$field['type'] = esc_attr($advance_fields[$field_key]['type']);
							$fields[] = $field;
						}
					}
				}


				if (  $this->is_preview() ) {

					$count = 0;
					$i          = 16;
					while ( $i > 0 ) {
						if ( ! empty( $args[ 'key_' . $i ] ) ) {
							$count = $i;break;
						}
						$i--;
					}

					$features = array_fill(0, $count, array(
						'icon'  => 'fas fa-tools',
						'label' => 'Label',
						'value' => 'Value',
					));
				}

			} elseif ( 'tags' === $args['source'] ) {
				//$fields = geodir_post_custom_fields( $package_id, 'all', $post_type, $args['location'] );
				$tags = !empty($gd_post->post_tags) ? explode(",",$gd_post->post_tags) : array();
				if ( $this->is_preview() ) {
					$tags = array(
						'Swimming Pool',
						'Parking',
						'Garden',
						'Gym',
						'Disabled Access',
						'Wifi',
						'Pet Friendly',
						'3 Bedrooms',
						'2 Bathrooms',
						'Fireplace',
					);
				}

				$tag_include = ! empty( $args['tag_include'] ) ? array_map( 'trim', explode( ',', $args['tag_include'] ) ) : array();

				// If we can cut the size before links then better.
				if ( ! empty( $tag_include  ) && ! empty( $args['tag_count'] ) && count( $tag_include ) > absint( $args['tag_count'] ) ) {
					$tag_include = array_slice( $tag_include, 0, absint( $args['tag_count'] ) );
				}

				if ( ! empty( $tags ) ) {
					$tag_icon = ! empty( $args['tag_icon'] ) ? esc_attr( $args['tag_icon'] ) : 'far fa-check-circle';

					foreach ( $tags as $tag ) {
						$slug = sanitize_title_with_dashes( $tag );

						if ( ! empty( $tag_include ) && ! in_array( $slug, $tag_include ) ) {
							continue;
						}

						if ( $args['tag_link'] ) {
							$tag_link = $this->is_preview() ? '#' : get_term_link( $slug, $post_type . '_tags' );

							if ( is_wp_error( $tag_link ) ) {
								continue;
							}

							$tag_value = '<a href="' . esc_url( $tag_link ) . '">' . esc_attr( $tag ) . '</a>';
						} else {
							$tag_value = esc_attr( $tag );
						}

						$features[] = array(
							'icon' => $tag_icon,
							'value' => $tag_value
						);
					}
				}

				if ( ! empty( $features ) && ! empty( $args['tag_count'] ) && count( $features ) > $args['tag_count'] ) {
					$features = array_slice( $features, 0, absint( $args['tag_count'] ) );
				}
			} else {
				$fields = geodir_post_custom_fields( $package_id, 'all', $post_type, $args['location'] );
			}

			if ( ! empty( $fields ) ) {
				$features = array();

				foreach ( $fields as $field ) {
					$field = geodir_stripslashes_field( $field );

					$type = $field['type'];
					if ( isset( $args['key'] ) && $args['key'] == 'post_images' ) {
						$type = 'file';
					}

					$output = apply_filters( "geodir_custom_field_output_{$type}", '', $args['location'], $field, $args['id'], $args['show'] );

					if ( $output ) {
						$features[] = $this->get_parsed_values( $output, $field );
					}
				}
			} else if ( ! empty( $tags ) ) {
			}

			if ( ! empty( $features ) ) {
				$wrap_class = sd_build_aui_class( $args );

				$styles = sd_build_aui_styles( $args );
				$style  = $styles ? ' style="' . $styles . '"' : '';

				if ( ! empty( $args['gy'] ) ) {
					$wrap_class .= ' gy-' . absint( $args['gy'] );
				}

				$output  = '<div class="row geodir-features ' . esc_attr( $wrap_class ) . '" ' . $style . '>';
				$output .= $this->get_rendered_features( $features, $args );
				$output .= '</div>';
			}

			if ( ! $output && $this->is_preview() ) {
				$output = $this->get_field_preview( $args, $field );
			}

			$args['id'] = apply_filters( 'geodir_widget_post_meta_reset_id', $args['id'], $args );
		}

		return $output;
	}

	public function get_rendered_features( $features, $args ) {
		$output = '';

		if ( ! empty( $features ) ) {

			$wrap_class = sd_build_aui_class( array(
				'pb' => $args['item_pb'],
				'pt' => $args['item_pt'],
				'border' => $args['item_border'],
//				'border_type' => $args['item_border_type'],
				'border_width' => $args['item_border_width'],
				'border_opacity' => $args['item_border_opacity'],
				'display' => $args['list_display_flex'],
				'flex_align_items' => $args['list_flex_align_items'],
				'flex_justify_content' => $args['list_flex_justify_content'],
//				'pb' => $args['item_pb'],
			) );

			$wrap_class = str_replace('border ', 'border-bottom ',$wrap_class);

			foreach ( $features as $feature ) {
				$output .= '<div class="col geodir-feature-item ">';
				$output .= '<div class="geodir-feature-wrapper ' . esc_attr( $wrap_class ) . '">';

				$x = 1;

				while ( $x <= 3 ) {
					if ( $x === absint( $args['icon_order'] ) && ! empty( $feature['icon'] ) ) {
						$output .= $this->get_rendered_feature( 'icon', $feature['icon'], $args );
					}
					if ( $x === absint( $args['label_order'] ) && ! empty( $feature['label'] ) ) {
						$output .= $this->get_rendered_feature( 'label', $feature['label'], $args );
					}
					if ( $x === absint( $args['value_order'] ) && ! empty( $feature['value'] ) ) {
						$output .= $this->get_rendered_feature( 'value', $feature['value'], $args );
					}
					$x++;
				}

				$output .= '</div>';
				$output .= '</div>';
			}
		}

		return $output;
	}

	public function get_rendered_feature( $type, $content, $args ) {
		$output = '';

		$wrap_class = sd_build_aui_class(
			array(
				'text_color'  => $args[ $type . '_text_color' ],
				'font_size'   => $args[ $type . '_font_size' ],
				'display'     => $args[ $type . '_display' ],
				'display_md'  => $args[ $type . '_display_md' ],
				'display_lg'  => $args[ $type . '_display_lg' ],
				'font_weight' => $args[ $type . '_font_weight' ],
				'mt'          => $args[ $type . '_mt' ],
				'mr'          => $args[ $type . '_mr' ],
				'mb'          => $args[ $type . '_mb' ],
				'ml'          => $args[ $type . '_ml' ],
			)
		);

		if ( 'icon' === $type ) {
			if ( ! empty( $content ) ) {

				if ( ! empty( $args['icon_box'] ) ) {
					$output .= '<i class="geodir-feature-icon ' . esc_attr( $content ) . '" ></i>';

					$color_class = strpos( $args['icon_box_color'], 'translucent-' ) !== false ? 'btn-' . esc_attr( $args['icon_box_color'] ) : 'bg-' . esc_attr( $args['icon_box_color'] );
					$output      = '<span class="iconbox border-0 ' . esc_attr( $args['icon_box'] ) . ' fill rounded-circle  d-inline-block align-middle ' . esc_attr( $color_class ) . ' transition-all ' . esc_attr( $wrap_class ) . '">' . $output . '</span>';
				} else {
					$output .= '<i class="geodir-feature-icon ' . esc_attr( $content ) . ' ' . esc_attr( $wrap_class ) . '" ></i>';
				}
			}
		} elseif ( 'label' === $type ) {
			if ( $args['label_colon_remove'] ) {
				$output .= ! empty( $content ) ? '<div class="geodir-feature-label ' . $wrap_class . '">' . rtrim( esc_attr( $content ), ': ' ) . '</div>' : '';
			} else {
				$output .= ! empty( $content ) ? '<div class="geodir-feature-label ' . $wrap_class . '">' . esc_attr( $content ) . '</div>' : '';
			}
		} elseif ( 'value' === $type ) {
			$output .= ! empty( $content ) ? '<div class="geodir-feature-value ' . esc_attr( $wrap_class ) . '">' . $content . '</div>' : '';
		}

		return $output;
	}

	public function get_parsed_values( $html, $field ) {
		// Create a new DOMDocument instance
		$dom = new DOMDocument();

		// Add charset to prevent encode issue for characters like äüö.
		$_html = sprintf( '<!DOCTYPE html><html><head><meta charset="%s"></head><body>%s</body></html>', esc_attr( get_bloginfo( 'charset' ) ), $html );

		// Suppress warnings generated by loadHTML.
		$errors = libxml_use_internal_errors( true );

		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		/** @scrutinizer ignore-unhandled */ @$dom->loadHTML( $_html );

		libxml_use_internal_errors( $errors );

		// Create a new DOMXPath instance
		$xpath = new DOMXPath( $dom );

		// Query the DOM to find the element with the icon
		$iconElement = $xpath->query( '//i' )->item( 0 );
		$iconClass   = ! empty( $iconElement ) ? $iconElement->getAttribute( 'class' ) : '';

		// Query the DOM to find the element with the label
		$labelElement = $xpath->query( '//span[@class[starts-with(., "geodir_post_meta_title")]]' )->item( 0 );
		$label        = !empty($labelElement) ? $labelElement->nodeValue : '';

		if ( 'business_hours' === $field['type'] ) {
			$value     = $html;
			$iconClass = '';
		} else {
			// Split the HTML string at the first occurrence of a double ending span or a single ending span
			$parts = preg_split( '/<\/span><\/span>|<\/span>/', $html, 2 );

			// The first part of the HTML string
			$firstPart = $parts[0];

			// The second part of the HTML string (all HTML after the first double ending span or single ending span)
			$value = $parts[1];

			// Check if the HTML string ends with a closing div
			if ( substr( $value, -6 ) === '</div>' ) {
				// If it does, remove the closing div from the end of the HTML string
				$value = substr( $value, 0, -6 );
			}
		}

		// Create an array with the extracted information
		return array(
			'icon'  => $iconClass,
			'label' => $label,
			'value' => trim( $value ),
			'html'  => $html,
		);
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
			$value  = __( 'Placeholder', 'geodirectory' );
			$icon   = '<i class="fas fa-tools"></i> ';
			$view   = ! empty( $args['show'] ) ? esc_attr( $args['show'] ) : '';
			$output = geodir_field_output_process( $view );

			$wrap_class = ! empty( $field['css_class'] ) ? esc_attr( $field['css_class'] ) : sd_build_aui_class( $args );
			$html       = '<div class="geodir_post_meta  geodir-field-preview ' . $wrap_class . '">';
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
