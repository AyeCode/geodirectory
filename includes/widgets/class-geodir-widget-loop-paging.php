<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GeoDirectory Search widget.
 *
 * @since 1.0.0
 */
class GeoDir_Widget_Loop_Paging extends WP_Super_Duper {

	/**
	 * Register the advanced search widget with WordPress.
	 *
	 */
	public function __construct() {

		$options = array(
			'textdomain'       => GEODIRECTORY_TEXTDOMAIN,
			'block-icon'       => 'admin-site',
			'block-category'   => 'geodirectory',
			'block-keywords'   => "['loop','paging','geodir']",
			'block-supports'   => array(
				'customClassName' => false,
			),
			'class_name'       => __CLASS__,
			'base_id'          => 'gd_loop_paging', // this us used as the widget id and the shortcode id.
			'name'             => __( 'GD > Loop Paging', 'geodirectory' ), // the name of the widget.
			'widget_ops'       => array(
				'classname'    => 'geodir-loop-paging-container ' . geodir_bsui_class(), // widget class
				'description'  => esc_html__( 'Shows the pagination links if the current query has multiple pages of results.', 'geodirectory' ), // widget description
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
					'groups' => array( __( 'Paging', 'geodirectory' ), __( 'Advanced Paging', 'geodirectory' ) ),
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

		$arguments = array();

		$arguments['show_advanced'] = array(
			'title'    => __( 'Show Advanced pagination:', 'geodirectory' ),
			'desc'     => __( 'This will add extra pagination info like `Showing listings x-y of z` before/after pagination.', 'geodirectory' ),
			'type'     => 'select',
			'options'  => array(
				''              => __( 'Never', 'geodirectory' ),
				'before'        => __( 'Before', 'geodirectory' ),
				'after'         => __( 'After', 'geodirectory' ),
				'inline_before' => __( 'Inline Before', 'geodirectory' ),
				'inline_after'  => __( 'Inline After', 'geodirectory' ),
				'only'          => __( 'Only (hide paging)', 'geodirectory' ),
			),
			'desc_tip' => true,
			'advanced' => false,
			'group'    => __( 'Output', 'geodirectory' ),
		);

		$design_style = geodir_design_style();

		if ( $design_style ) {
			// mid_size
			$arguments['mid_size'] = array(
				'type'     => 'select',
				'title'    => __( 'Middle Pages Numbers:', 'geodirectory' ),
				'desc'     => __( 'How many numbers to either side of the current pages. Default 2.', 'geodirectory' ),
				'options'  => array(
					''   => __( 'Default (2)', 'geodirectory' ),
					'0'  => '0',
					'1'  => '1',
					'2'  => '2',
					'3'  => '3',
					'4'  => '4',
					'5'  => '5',
					'6'  => '6',
					'7'  => '7',
					'8'  => '8',
					'9'  => '9',
					'10' => '10',
				),
				'default'  => '',
				'desc_tip' => true,
				'advanced' => false,
				'group'    => __( 'Output', 'geodirectory' ),
			);

			// mid_size_sm
			$arguments['mid_size_sm'] = array(
				'type'     => 'select',
				'title'    => __( 'Middle Pages Numbers (mobile):', 'geodirectory' ),
				'desc'     => __( 'How many numbers to either side of the current pages on small screen like on mobile. Default 0.', 'geodirectory' ),
				'options'  => array(
					''   => __( 'Default (0)', 'geodirectory' ),
					'0'  => '0',
					'1'  => '1',
					'2'  => '2',
					'3'  => '3',
					'4'  => '4',
					'5'  => '5',
					'6'  => '6',
					'7'  => '7',
					'8'  => '8',
					'9'  => '9',
					'10' => '10',
				),
				'default'  => '',
				'desc_tip' => true,
				'advanced' => false,
				'group'    => __( 'Output', 'geodirectory' ),
			);

			// paging style
			$arguments['paging_style'] = array(
				'title'    => __( 'Style', 'geodirectory' ),
				'type'     => 'select',
				'options'  => array(
					''      => __( 'Default', 'geodirectory' ),
					'rounded' => __( 'Rounded', 'geodirectory' ),
				),
				'default'  => '',
				'desc_tip' => true,
				'advanced' => false,
				'group'    => __( 'Paging', 'geodirectory' ),
			);

			// button size
			$arguments['size'] = array(
				'title'    => __( 'Size', 'geodirectory' ),
				'type'     => 'select',
				'options'  => array(
					''      => __( 'Default', 'geodirectory' ),
					'small' => __( 'Small', 'geodirectory' ),
					'large' => __( 'Large', 'geodirectory' ),
				),
				'default'  => '',
				'desc_tip' => true,
				'advanced' => false,
				'group'    => __( 'Paging', 'geodirectory' ),
				'element_require' => '[%paging_style%]==""',
			);

			$arguments['size_sm'] = array(
				'title'    => __( 'Size (mobile)', 'geodirectory' ),
				'desc'     => __( 'Pagination size to show on mobile.', 'geodirectory' ),
				'type'     => 'select',
				'options'  => array(
					''      => __( 'Default', 'geodirectory' ),
					'small' => __( 'Small', 'geodirectory' ),
					'large' => __( 'Large', 'geodirectory' ),
				),
				'default'  => '',
				'desc_tip' => true,
				'advanced' => false,
				'group'    => __( 'Paging', 'geodirectory' ),
				'element_require' => '[%paging_style%]==""',
			);

			// text color
			$arguments['ap_text_color'] = sd_get_text_color_input(
				'text_color',
				array(
					'group' => __( 'Advanced Paging', 'geodirectory' ),
				)
			);

			// font size
			$arguments['ap_font_size'] = sd_get_font_size_input(
				'font_size',
				array(
					'group' => __( 'Advanced Paging', 'geodirectory' ),
				)
			);

			// padding
			$arguments['ap_pt'] = sd_get_padding_input( 'pt', array( 'group' => __( 'Advanced Paging', 'geodirectory' ) ) );
			$arguments['ap_pr'] = sd_get_padding_input( 'pr', array( 'group' => __( 'Advanced Paging', 'geodirectory' ) ) );
			$arguments['ap_pb'] = sd_get_padding_input( 'pb', array( 'group' => __( 'Advanced Paging', 'geodirectory' ) ) );
			$arguments['ap_pl'] = sd_get_padding_input( 'pl', array( 'group' => __( 'Advanced Paging', 'geodirectory' ) ) );

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
		global $geodir_is_widget_listing;

		$design_style = geodir_design_style();

		$defaults = array(
			'show_advanced' => '',
			'bg'            => '',
			'mt'            => '',
			'mb'            => '3',
			'mr'            => '',
			'ml'            => '',
			'pt'            => '',
			'pb'            => '',
			'pr'            => '',
			'pl'            => '',
			'border'        => '',
			'rounded'       => '',
			'rounded_size'  => '',
			'shadow'        => '',
			'mid_size'      => '',
			'size_sm'       => '',
			'mid_size_sm'   => ''
		);
		$args     = wp_parse_args( $args, $defaults );
		if ( ! empty( $args['show_advanced'] ) ) {
			global $gd_advanced_pagination;
			$gd_advanced_pagination = $args['show_advanced'];
		}

		// preview
		$is_preview = $this->is_preview();
		if ( $is_preview ) {
			$args['preview'] = true;
			$args['total']   = 3;
		}

		if ( '' === $args['mid_size'] ) {
			$args['mid_size'] = 2;
		}

		// paging size mobile
		if ( ! empty( $args['size_sm'] ) && 'small' === $args['size_sm'] ) {
			$args['size_sm'] = 'small';
		} else if ( ! empty( $args['size_sm'] ) && 'large' === $args['size_sm'] ) {
			$args['size_sm'] = 'large';
		} else {
			$args['size_sm'] = '';
		}

		// Mobile devices
		if ( wp_is_mobile() ) {
			$args['size'] = $args['size_sm'];
			$args['mid_size'] = absint( $args['mid_size_sm'] ); // On mobile devices.
		}

		// paging size
		if ( ! empty( $args['size'] ) && 'small' === $args['size'] ) {
			$args['class'] = 'pagination-sm';
		} elseif ( ! empty( $args['size'] ) && 'large' === $args['size'] ) {
			$args['class'] = 'pagination-lg';
		}

		// paging style
		if ( ! empty( $args['paging_style'] ) && 'rounded' === $args['paging_style'] ) {
			$args['rounded_style'] = true;
		}

		// advanced paging class
		if ( $design_style ) {
			$advanced_pagination_class = sd_build_aui_class(
				array(
					'text_color' => ! empty( $args['ap_text_color'] ) ? $args['ap_text_color'] : 'muted',
					'font_size'  => isset( $args['ap_font_size'] ) ? $args['ap_font_size'] : '',
					'pt'         => isset( $args['ap_pt'] ) ? $args['ap_pt'] : '',
					'pr'         => isset( $args['ap_pr'] ) ? $args['ap_pr'] : '',
					'pb'         => isset( $args['ap_pb'] ) ? $args['ap_pb'] : '',
					'pl'         => isset( $args['ap_pl'] ) ? $args['ap_pl'] : ''
				)
			);
		} else {
			$advanced_pagination_class = '';
		}

		$args['advanced_pagination_class'] = $advanced_pagination_class;

		ob_start();
		if ( geodir_is_post_type_archive() || geodir_is_taxonomy() || geodir_is_page( 'search' ) || $geodir_is_widget_listing || $is_preview ) {
			geodir_loop_paging( $args );
		}
		return ob_get_clean();
	}

}
