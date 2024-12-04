<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GeoDirectory A-Z Search widget.
 *
 * @since 2.3.73
 */
class GeoDir_Widget_AZ_Search extends WP_Super_Duper {

	public $arguments;

	/**
	 * Sets up a widget instance.
	 */
	public function __construct() {
		$options = array(
			'textdomain'     => GEODIRECTORY_TEXTDOMAIN,
			'block-icon'     => 'editor-spellcheck',
			'block-category' => 'geodirectory',
			'block-keywords' => "['az','geodir','search']",
			'block-supports' => array(
				'customClassName' => false,
			),
			'class_name'     => __CLASS__,
			'base_id'        => 'gd_az_search',
			'name'           => __( 'GD > A-Z Search', 'geodirectory' ),
			'widget_ops'     => array(
				'classname'   => 'geodir-az-search-container ' . geodir_bsui_class(),
				'description' => esc_html__( 'Shows the listings in A-Z alphabetical list.', 'geodirectory' ),
				'geodirectory' => true
			),
			'block_group_tabs' => array(
				'content'  => array(
					'tab'    => array(
						'title'     => __( 'Content', 'geodirectory' ),
						'key'       => 'bs_tab_content',
						'tabs_open' => true,
						'open'      => true,
						'class'     => 'text-center flex-fill d-flex justify-content-center'
					),
					'groups' => array(
						__( 'Title', 'geodirectory' ),
						__( 'Filters', 'geodirectory' )
					)
				),
				'styles'   => array(
					'tab'    => array(
						'title'     => __( 'Styles', 'geodirectory' ),
						'key'       => 'bs_tab_styles',
						'tabs_open' => true,
						'open'      => true,
						'class'     => 'text-center flex-fill d-flex justify-content-center'
					),
					'groups' => array(
						__( 'Paging', 'geodirectory' )
					)
				),
				'advanced' => array(
					'tab'    => array(
						'title'     => __( 'Advanced', 'geodirectory' ),
						'key'       => 'bs_tab_advanced',
						'tabs_open' => true,
						'open'      => true,
						'class'     => 'text-center flex-fill d-flex justify-content-center'
					),
					'groups' => array(
						__( 'Wrapper Styles', 'geodirectory' ),
						__( 'Advanced', 'geodirectory' )
					)
				)
			)
		);

		parent::__construct( $options );
	}

	/**
	 * Set widget arguments.
	 */
	public function set_arguments() {
		$design_style = geodir_design_style();

		$arguments = array();
		$arguments['title'] = array(
			'type' => 'text',
			'title' => __( 'Title:', 'geodirectory' ),
			'desc' => __( 'The widget title.', 'geodirectory' ),
			'default' => '',
			'desc_tip' => true,
			'advanced' => false,
			'group' => __( 'Title', 'geodirectory' )
		);

		if ( $design_style ) {
			$arguments = $arguments + geodir_get_sd_title_inputs();
		}

		$arguments['post_type'] = array(
			'title'    => __( 'Post Type:', 'geodirectory' ),
			'desc'     => __( 'Post type to filter posts.', 'geodirectory' ),
			'type'     => 'select',
			'options'  => geodir_get_posttypes( 'options-plural' ),
			'default'  => '',
			'desc_tip' => true,
			'advanced' => false,
			'group'    => __( 'Filters', 'geodirectory' ),
		);

		$arguments['no_cpt_filter'] = array(
			'title'    => __( 'Do not filter current CPT:', 'geodirectory' ),
			'desc'     => __( 'Do not filter for current viewing post type.', 'geodirectory' ),
			'type'     => 'checkbox',
			'desc_tip' => true,
			'value'    => '1',
			'default'  => 0,
			'advanced' => false,
			'group'    => __( 'Filters', 'geodirectory' ),
		);

		if ( $design_style ) {
			// Styles > Paging
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
				'group'    => __( 'Paging', 'geodirectory' )
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
				'group'    => __( 'Paging', 'geodirectory' )
			);

			// margins mobile
			$_arguments = array();
			$_arguments['rounded_mt'] = sd_get_margin_input( 'mt', array( 'device_type' => 'Mobile' ) );
			$_arguments['rounded_mr'] = sd_get_margin_input( 'mr', array( 'device_type' => 'Mobile' ) );
			$_arguments['rounded_mb'] = sd_get_margin_input( 'mb', array( 'device_type' => 'Mobile' ) );
			$_arguments['rounded_ml'] = sd_get_margin_input( 'ml', array( 'device_type' => 'Mobile' ) );

			// margins tablet
			$_arguments['rounded_mt_md'] = sd_get_margin_input( 'mt', array( 'device_type' => 'Tablet' ) );
			$_arguments['rounded_mr_md'] = sd_get_margin_input( 'mr', array( 'device_type' => 'Tablet' ) );
			$_arguments['rounded_mb_md'] = sd_get_margin_input( 'mb', array( 'device_type' => 'Tablet' ) );
			$_arguments['rounded_ml_md'] = sd_get_margin_input( 'ml', array( 'device_type' => 'Tablet' ) );

			// margins desktop
			$_arguments['rounded_mt_lg'] = sd_get_margin_input( 'mt', array( 'device_type' => 'Desktop' ) );
			$_arguments['rounded_mr_lg'] = sd_get_margin_input( 'mr', array( 'device_type' => 'Desktop', 'default' => 1 ) );
			$_arguments['rounded_mb_lg'] = sd_get_margin_input( 'mb', array( 'device_type' => 'Desktop' ) );
			$_arguments['rounded_ml_lg'] = sd_get_margin_input( 'ml', array( 'device_type' => 'Desktop', 'default' => 1 ) );

			foreach ( $_arguments as $key => $field ) {
				$arguments[ $key ] = array_merge( $field, array( 'group' => __( 'Paging', 'geodirectory' ), 'element_require' => '[%paging_style%]=="rounded"' ) );
			}

			$arguments['paging_display']    = sd_get_display_input( 'd', array( 'device_type' => 'Mobile', 'group' => __( 'Paging', 'geodirectory' ) ) );
			$arguments['paging_display_md'] = sd_get_display_input( 'd', array( 'device_type' => 'Tablet', 'group' => __( 'Paging', 'geodirectory' ) ) );
			$arguments['paging_display_lg'] = sd_get_display_input( 'd', array( 'device_type' => 'Desktop', 'group' => __( 'Paging', 'geodirectory' ) ) );

			$arguments = $arguments + sd_get_flex_justify_content_input_group( 'alignment', array( 'group' => __( 'Paging', 'geodirectory' ), 'element_require' => '( [%paging_display%]=="d-flex" || [%paging_display_md%]=="d-md-flex" || [%paging_display_lg%]=="d-lg-flex" )' ) );

			// Advanced > Wrapper Styles
			// background
			$arguments['bg'] = sd_get_background_input();

			// margins mobile
			$arguments['mt'] = sd_get_margin_input( 'mt', array( 'device_type' => 'Mobile' ) );
			$arguments['mr'] = sd_get_margin_input( 'mr', array( 'device_type' => 'Mobile' ) );
			$arguments['mb'] = sd_get_margin_input( 'mb', array( 'device_type' => 'Mobile', 'default' => 3 ) );
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
		}

		// Advanced > Advanced
		$arguments['css_class'] = sd_get_class_input();

		return $arguments;
	}

	/**
	 * Outputs on the front-end.
	 *
	 * @param array $instance    Settings for the widget instance.
	 * @param array $widget_args Display arguments.
	 * @param string $content
	 *
	 * @return mixed|string|void
	 */
	public function output( $instance = array(), $widget_args = array(), $content = '' ) {
		if ( ! geodir_design_style() ) {
			return;
		}

		$args = wp_parse_args(
			$instance,
			array(
				'title' => '',
				'post_type' => '',
				'no_cpt_filter' => '',
				// Styles > Paging
				'paging_style' => '',
				'size' => '',
				'size_sm' => '',
				'rounded_mt' => '',
				'rounded_mr' => '',
				'rounded_mb' => '',
				'rounded_ml' => '',
				'rounded_mt_md' => '',
				'rounded_mr_md' => '',
				'rounded_mb_md' => '',
				'rounded_ml_md' => '',
				'rounded_mt_lg' => '',
				'rounded_mr_lg' => '1',
				'rounded_mb_lg' => '',
				'rounded_ml_lg' => '1',
				'paging_display' => '',
				'paging_display_md' => '',
				'paging_display_lg' => '',
				'alignment' => '',
				'alignment_md' => '',
				'alignment_lg' => '',
				// Advanced > Wrapper Styles
				'bg' => '',
				'mt' => '',
				'mr' => '',
				'mb' => '3',
				'ml' => '',
				'mt_md' => '',
				'mr_md' => '',
				'mb_md' => '',
				'ml_md' => '',
				'mt_lg' => '',
				'mr_lg' => '',
				'mb_lg' => '',
				'ml_lg' => '',
				'pt' => '',
				'pr' => '',
				'pb' => '',
				'pl' => '',
				'pt_md' => '',
				'pr_md' => '',
				'pb_md' => '',
				'pl_md' => '',
				'pt_lg' => '',
				'pr_lg' => '',
				'pb_lg' => '',
				'pl_lg' => '',
				'border' => '',
				'border_type' => '',
				'border_width' => '',
				'border_opacity' => '',
				'rounded' => '',
				'rounded_size' => '',
				'shadow' => '',
				'display' => '',
				'display_md' => '',
				'display_lg' => '',
				// Advanced > Advanced
				'css_class' => ''
			)
		);

		$post_types = geodir_get_posttypes();

		if ( empty( $args['post_type'] ) ) {
			$args['post_type'] = $post_types[0];
		}

		if ( ! empty( $args['post_type'] ) && ! geodir_is_gd_post_type( $args['post_type'] ) ) {
			return;
		}

		$is_preview = $this->is_preview();
		$current_post_type = geodir_get_current_posttype();

		if ( empty( $args['no_cpt_filter'] ) && $current_post_type ) {
			$args['post_type'] = $current_post_type;
		} else if ( $is_preview && ! empty( $args['post_type'] ) && empty( $current_post_type ) ) {
			$current_post_type = $args['post_type'];
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
		}

		$wrap_class = sd_build_aui_class( $args );
		if ( $wrap_class ) {
			$wrap_class = ' ' . $wrap_class;
		}

		$page_class = '';
		// paging style
		if ( ! empty( $args['paging_style'] ) && 'rounded' === $args['paging_style'] ) {
			$page_class .= ' rounded-pill ';

			$_args = array(
				'mt' => $args['rounded_mt'],
				'mr' => $args['rounded_mr'],
				'mb' => $args['rounded_mb'],
				'ml' => $args['rounded_ml'],
				'mt_md' => $args['rounded_mt_md'],
				'mr_md' => $args['rounded_mr_md'],
				'mb_md' => $args['rounded_mb_md'],
				'ml_md' => $args['rounded_ml_md'],
				'mt_lg' => $args['rounded_mt_lg'],
				'mr_lg' => $args['rounded_mr_lg'],
				'mb_lg' => $args['rounded_mb_lg'],
				'ml_lg' => $args['rounded_ml_lg']
			);

			$page_class .= sd_build_aui_class( $_args );
		}

		if ( $page_class ) {
			$page_class = ' ' . trim( $page_class );
		}

		$pagination_class = ' m-0 p-0';
		if ( ! empty( $args['size'] ) && 'small' === $args['size'] ) {
			$pagination_class .= ' ' . 'pagination-sm';
		} elseif ( ! empty( $args['size'] ) && 'large' === $args['size'] ) {
			$pagination_class .= ' ' . 'pagination-lg';
		}

		$pagination_class .= ' ' . sd_build_aui_class( array(
			'display' => $args['paging_display'],
			'display_md' => $args['paging_display_md'],
			'display_lg' => $args['paging_display_lg'],
			'flex_justify_content' => $args['alignment'],
			'flex_justify_content_md' => $args['alignment_md'],
			'flex_justify_content_lg' => $args['alignment_lg']
		) );

		$options = geodir_az_search_options( $args['post_type'] );
		$current = ! empty( $_REQUEST['saz'] ) && geodir_is_page( 'search' ) ? sanitize_text_field( $_REQUEST['saz'] ) : '';

		if ( $is_preview ) {
			$az_search_url = '';

			if ( empty( $current ) ) {
				$current = $options[0];
			}
		} else {
			if ( ! empty( $current_post_type ) && $current_post_type == $args['post_type'] && geodir_is_page( 'search' ) ) {
				$az_search_url = remove_query_arg( array( 'saz', 'paged' ), geodir_curPageURL() );
				$az_search_url = preg_replace( '#/page/\d+#', '', $az_search_url );
			} else {
				$az_search_url = add_query_arg(
					array(
						'geodir_search' => 1,
						'stype' => $args['post_type'],
						's'=> ''
					),
					geodir_search_page_base_url()
				);
			}
		}

		$output = '<div class="geodir-az-search-wrap' . esc_attr( $wrap_class ) . '">';
		$output .= '<nav aria-label="' .esc_attr__( 'A-Z Search', 'geodirectory' )  . '">';
		$output .= '<ul class="pagination flex-wrap' . esc_attr( $pagination_class ) . '">';

		foreach( $options as $char ) {
			$output .= '<li class="page-item mx-0" data-az-page="' . esc_attr( $char ) . '">';

			if ( $char == $current && $current_post_type == $args['post_type'] ) {
				$output .= '<span class="page-link' . esc_attr( $page_class ) .  ' current active" aria-current="page">' . esc_html( $char ) . '</span>';
			} else {
				if ( ! $is_preview ) {
					$az_search_url = add_query_arg( array( 'saz' => $char ), remove_query_arg( array( 'saz' ), $az_search_url ) );
				}
	
				$output .= '<a class="page-link' . esc_attr( $page_class ) .  '" href="' . ( $is_preview ? 'javascript:void(0)' : esc_url( $az_search_url ) ) . '">' . esc_html( $char ) . '</a>';
			}

			$output .= '</li>';
		}

		$output .= '</ul>';
		$output .= '</nav>';
		$output .= '</div>';

		return $output;
	}
}
