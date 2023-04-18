<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GeoDirectory Search widget.
 *
 * @since 1.0.0
 */
class GeoDir_Widget_Loop_Actions extends WP_Super_Duper {

	/**
	 * Register the advanced search widget with WordPress.
	 *
	 */
	public function __construct() {

		$options = array(
			'textdomain'       => GEODIRECTORY_TEXTDOMAIN,
			'block-icon'       => 'admin-site',
			'block-category'   => 'geodirectory',
			'block-keywords'   => "['loop','actions','geodir']",
			'block-supports'   => array(
				'customClassName' => false,
			),
			'class_name'       => __CLASS__,
			'base_id'          => 'gd_loop_actions', // this us used as the widget id and the shortcode id.
			'name'             => __( 'GD > Loop Actions', 'geodirectory' ), // the name of the widget.
			'widget_ops'       => array(
				'classname'    => 'geodir-loop-actions-container ' . geodir_bsui_class(), // widget class
				'description'  => esc_html__( 'Shows the archive loop actions such as sort by and grid view,  only used on Archive template page, usually above `gd_loop`.', 'geodirectory' ), // widget description
				'geodirectory' => true,
			),
			'block_group_tabs' => array(
				'content'  => array(
					'groups' => array( __( 'Output', 'geodirectory' ) ),
					'tab'    => array(
						'title'     => __( 'Content', 'geodirectory' ),
						'key'       => 'bs_tab_content',
						'tabs_open' => true,
						'open'      => true,
						'class'     => 'text-center flex-fill d-flex justify-content-center',
					),
				),
				'styles'   => array(
					'groups' => array( __( 'Buttons', 'geodirectory' ) ),
					'tab'    => array(
						'title'     => __( 'Styles', 'geodirectory' ),
						'key'       => 'bs_tab_styles',
						'tabs_open' => true,
						'open'      => true,
						'class'     => 'text-center flex-fill d-flex justify-content-center',
					),
				),
				'advanced' => array(
					'groups' => array( __( 'Wrapper Styles', 'geodirectory' ), __( 'Advanced', 'geodirectory' ) ),
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
	 * Set the widget arguments.
	 *
	 * @return array Widget arguments.
	 */
	public function set_arguments() {
		$design_style = geodir_design_style();

		$arguments = array(
			'hide_layouts' => array(
				'title'    => __( 'Hide Layouts:', 'geodirectory' ),
				'desc'     => __( 'Select layouts to hide from frontend list view.', 'geodirectory' ),
				'type'     => 'select',
				'options'  => geodir_get_layout_options(),
				'default'  => '',
				'desc_tip' => true,
				'advanced' => false,
				'multiple' => true,
				'group'    => __( 'Output', 'geodirectory' ),
			),
		);

		if ( $design_style ) {

			// button size
			$arguments['btn_size'] = array(
				'title'    => __( 'Button size', 'geodirectory' ),
				'type'     => 'select',
				'options'  => array(
					''       => __( 'Default (small)', 'geodirectory' ),
					'normal' => __( 'Normal', 'geodirectory' ),
					'large'  => __( 'Large', 'geodirectory' ),
				),
				'default'  => '',
				'desc_tip' => true,
				'advanced' => false,
				'group'    => __( 'Buttons', 'geodirectory' ),
			);

			// button style
			$arguments['btn_style'] = array(
				'title'    => __( 'Button style', 'geodirectory' ),
				'type'     => 'select',
				'options'  => array(
					''       => __( 'Default (primary outline)', 'geodirectory' ),
					'custom' => __( 'Custom', 'geodirectory' ),
				) + sd_aui_colors( false, true ),
				'default'  => '',
				'desc_tip' => true,
				'advanced' => false,
				'group'    => __( 'Buttons', 'geodirectory' ),
			);

			// background
			$arguments['btn_bg'] = sd_get_background_input(
				'bg',
				array(
					'group'           => __( 'Buttons', 'geodirectory' ),
					'element_require' => '[%btn_style%]=="custom"',
				)
			);

			$arguments['btn_border'] = sd_get_border_input(
				'border',
				array(
					'group'           => __( 'Buttons', 'geodirectory' ),
					'element_require' => '[%btn_style%]=="custom"',
				)
			);

			// text color
			$arguments['text_color'] = sd_get_text_color_input(
				'text_color',
				array(
					'group'           => __( 'Buttons', 'geodirectory' ),
					'element_require' => '[%btn_style%]=="custom"',
				)
			);

			// text align
			$arguments['text_align']    = sd_get_text_align_input(
				'text_align',
				array(
					'device_type'     => 'Mobile',
					'group'           => __( 'Buttons', 'geodirectory' ),
					'element_require' => '[%btn_style%]=="custom"',
				)
			);
			$arguments['text_align_md'] = sd_get_text_align_input(
				'text_align',
				array(
					'device_type'     => 'Tablet',
					'group'           => __( 'Buttons', 'geodirectory' ),
					'element_require' => '[%btn_style%]=="custom"',
				)
			);
			$arguments['text_align_lg'] = sd_get_text_align_input(
				'text_align',
				array(
					'device_type'     => 'Desktop',
					'group'           => __( 'Buttons', 'geodirectory' ),
					'element_require' => '[%btn_style%]=="custom"',
				)
			);

			// background
			$arguments['bg'] = sd_get_background_input();

			// margins mobile
			$arguments['mt'] = sd_get_margin_input( 'mt', array( 'device_type' => 'Mobile' ) );
			$arguments['mr'] = sd_get_margin_input( 'mr', array( 'device_type' => 'Mobile' ) );
			$arguments['mb'] = sd_get_margin_input(
				'mb',
				array(
					'device_type' => 'Mobile',
					'default'     => 3,
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
		}

		return $arguments;
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
		$design_style = geodir_design_style();

		$defaults = array(
			'hide_layouts' => '',
			'bg'           => '',
			'mt'           => '',
			'mb'           => '3',
			'mr'           => '',
			'ml'           => '',
			'pt'           => '',
			'pb'           => '',
			'pr'           => '',
			'pl'           => '',
			'border'       => '',
			'rounded'      => '',
			'rounded_size' => '',
			'shadow'       => '',
		);

		$args = wp_parse_args( $args, $defaults );

		$output = '';

		if ( geodir_is_post_type_archive() || geodir_is_taxonomy() || geodir_is_page( 'search' ) || $this->is_preview() ) {
			if ( ! empty( $args['hide_layouts'] ) && is_scalar( $args['hide_layouts'] ) ) {
				$args['hide_layouts'] = array_map( 'trim', explode( ',', $args['hide_layouts'] ) );
			}

			ob_start();

			geodir_loop_actions( $args );

			$output = ob_get_clean();
		}

		if ( $design_style && ! empty( $output ) ) {
			if ( ! empty( $args['btn_style'] ) && 'custom' !== $args['btn_style'] ) {
				$btn_class = 'btn-' . esc_attr( $args['btn_style'] );
				$output = str_replace( 'btn-outline-primary', $btn_class, $output );
			} else if ( ! empty( $args['btn_style'] ) && 'custom' === $args['btn_style'] ) {
				$btn_class = sd_build_aui_class(
					array(
						'bg' => isset( $args['btn_bg'] ) ? $args['btn_bg'] : '',
						'border' => isset( $args['btn_border'] ) ? $args['btn_border'] : '',
						'text_color' => isset( $args['text_color'] ) ? $args['text_color'] : ''
					)
				);

				$output = str_replace( 'btn-outline-primary', $btn_class, $output );
			}

			if ( ! empty( $args['btn_size'] ) ) {
				$btn_class = 'normal' === $args['btn_size'] ? 'btn-group' : 'btn-group-lg';

				$output = str_replace( 'btn-group-sm', $btn_class, $output );
			}
		}

		return $output;
	}
}
