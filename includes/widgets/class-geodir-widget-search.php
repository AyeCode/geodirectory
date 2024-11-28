<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GeoDirectory Search widget.
 *
 * @since 1.0.0
 */
class GeoDir_Widget_Search extends WP_Super_Duper {

	/**
	 * Register the advanced search widget with WordPress.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		$options = array(
			'textdomain'       => GEODIRECTORY_TEXTDOMAIN,
			'block-icon'       => 'admin-site',
			'block-category'   => 'geodirectory',
			'block-keywords'   => "['search','geo','geodir']",
			'block-supports'   => array(
				'customClassName' => false,
			),
			'class_name'       => __CLASS__,
			'base_id'          => 'gd_search', // this us used as the widget id and the shortcode id.
			'name'             => __( 'GD > Search', 'geodirectory' ), // the name of the widget.
			//'disable_widget'=> true,
			'widget_ops'       => array(
				'classname'    => 'geodir-search-container ' . geodir_bsui_class(), // widget class
				'description'  => esc_html__( 'Shows the GeoDirectory search bar.', 'geodirectory' ), // widget description
				'geodirectory' => true,
			),
			'block_group_tabs' => array(
				'content'  => array(
					'groups' => array( __( 'Content', 'geodirectory' ) ),
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
						__( 'Search bar', 'geodirectory' ),
						__( 'Main Inputs', 'geodirectory' ),
						__( 'Buttons', 'geodirectory' ),
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
	 * Set widget arguments.
	 */
	public function set_arguments() {
		global $aui_bs5;

		$arguments  = array();
		$post_types = $this->post_type_options();

		if ( count( $post_types ) > 2 ) {
			$arguments['post_type'] = array(
				'title'    => __( 'Default Post Type:', 'geodirectory' ),
				'desc'     => __( 'The custom post types to show by default. Only used when there are multiple CPTs.', 'geodirectory' ),
				'type'     => 'select',
				'options'  => $this->post_type_options(),
				'default'  => '',
				'desc_tip' => true,
				//'advanced' => true,
				'group'    => __( 'Content', 'geodirectory' ),
			);

			$arguments['post_type_hide'] = array(
				'title'    => __( 'Hide Post Type Selector:', 'geodirectory' ),
				'desc'     => __( 'Hide the CPT selector (if not on search page) this can be used to setup a specific CPT search and not give the option to change the CPT.', 'geodirectory' ),
				'type'     => 'checkbox',
				'desc_tip' => true,
				'value'    => '1',
				'default'  => '',
				//'advanced' => true,
				'group'    => __( 'Content', 'geodirectory' ),
			);
		}

		$arguments['hide_search_input'] = array(
			'type'     => 'checkbox',
			'title'    => __( 'Hide Main Search Input:', 'geodirectory' ),
			'desc'     => __( 'Hide the main search input.', 'geodirectory' ),
			'value'    => '1',
			'default'  => '',
			'desc_tip' => true,
			//'advanced' => true,
			'group'    => __( 'Content', 'geodirectory' ),
		);

		$arguments['hide_near_input'] = array(
			'type'     => 'checkbox',
			'title'    => __( 'Hide Near Search Input:', 'geodirectory' ),
			'desc'     => __( 'Hide the near location search input.', 'geodirectory' ),
			'value'    => '1',
			'default'  => '',
			'desc_tip' => true,
			//'advanced' => true,
			'group'    => __( 'Content', 'geodirectory' ),
		);

		$design_style = geodir_design_style();

		if ( $design_style ) {

			// Search bar
			$arguments['input_size'] = array(
				'title'    => __( 'Input size', 'geodirectory' ),
				'type'     => 'select',
				'options'  => array(
					''   => __( 'Default', 'geodirectory' ),
					'sm' => __( 'Small', 'geodirectory' ),
					'lg' => __( 'Large', 'geodirectory' ),
				),
				'default'  => '',
				'desc_tip' => true,
				'group'    => __( 'Search bar', 'geodirectory' ),
			);

			if ( function_exists( 'sd_get_flex_wrap_group' ) ) {
				$arguments = $arguments + sd_get_flex_wrap_group(
					'bar_flex_wrap',
					array(
						'group' => __( 'Search bar', 'geodirectory' ),
					)
				);
			}

			// Main Inputs
			$arguments['input_border'] = sd_get_border_input(
				'border',
				array(
					'group' => __( 'Main Inputs', 'geodirectory' ),
				)
			);
			// BS5 only
			$arguments['input_border_opacity'] = sd_get_border_input(
				'opacity',
				array(
					'group'           => __( 'Main Inputs', 'geodirectory' ),
					'element_require' => '[%input_border%]',
				)
			); // BS5 only
			$arguments['input_rounded_size']   = sd_get_border_input(
				'rounded_size',
				array(
					'group'           => __( 'Main Inputs', 'geodirectory' ),
					'element_require' => '[%input_border%]',
				)
			);

			// buttons
			$arguments = $arguments + sd_get_background_inputs(
				'btn_bg',
				array(
					'title' => __( 'Button color', 'geodirectory' ),
					'group' => __( 'Buttons', 'geodirectory' ),
				),
				false,
				false,
				false,
				true
			);

			$arguments['btn_rounded_size'] = sd_get_border_input(
				'rounded_size',
				array(
					'group'           => __( 'Buttons', 'geodirectory' ),
					'device_type'     => 'Mobile',
					'element_require' => '',
				)
			);

			$arguments['btn_rounded_size_md'] = sd_get_border_input(
				'rounded_size',
				array(
					'group'           => __( 'Buttons', 'geodirectory' ),
					'device_type'     => 'Tablet',
					'element_require' => '',
				)
			);

			$arguments['btn_rounded_size_lg'] = sd_get_border_input(
				'rounded_size',
				array(
					'group'           => __( 'Buttons', 'geodirectory' ),
					'device_type'     => 'Desktop',
					'element_require' => '',
				)
			);

			//          $arguments['btn_circle'] = array(
			//              'title'    => __( 'Round button', 'geodirectory' ),
			//              'desc'     => __( 'Designed to work when showing only an icon and not text.', 'geodirectory' ),
			//              'type'     => 'checkbox',
			//              'desc_tip' => true,
			//              'value'    => '1',
			//              'default'  => '',
			//              'group'    => __( 'Buttons', 'geodirectory' ),
			//          );

			// background
			$arguments['bg'] = geodir_get_sd_background_input( 'mt' );
			// margins
			$arguments['mt'] = geodir_get_sd_margin_input( 'mt' );
			$arguments['mr'] = geodir_get_sd_margin_input( 'mr' );
			$arguments['mb'] = geodir_get_sd_margin_input( 'mb', array( 'default' => 3 ) );
			$arguments['ml'] = geodir_get_sd_margin_input( 'ml' );

			// padding
			$arguments['pt'] = geodir_get_sd_padding_input( 'pt' );
			$arguments['pr'] = geodir_get_sd_padding_input( 'pr' );
			$arguments['pb'] = geodir_get_sd_padding_input( 'pb' );
			$arguments['pl'] = geodir_get_sd_padding_input( 'pl' );

			// border
			$arguments['border']  = sd_get_border_input( 'border' );
			$arguments['rounded'] = sd_get_border_input( 'rounded' );

			// rounded size
			$arguments['rounded_size']    = sd_get_border_input( 'rounded_size', array( 'device_type' => 'Mobile' ) );
			$arguments['rounded_size_md'] = sd_get_border_input( 'rounded_size', array( 'device_type' => 'Tablet' ) );
			$arguments['rounded_size_lg'] = sd_get_border_input( 'rounded_size', array( 'device_type' => 'Desktop' ) );

			// shadow
			$arguments['shadow'] = geodir_get_sd_shadow_input( 'shadow' );

			// custom css class
			$arguments['css_class'] = sd_get_class_input();

		}

		// hidden
//		$arguments['show'] = array(
//			'type'     => 'hidden',
//			'name'     => 'show',
//			'title'    => __( 'Show:', 'geodirectory' ),
//			'desc'     => '',
//			'default'  => '',
//			'desc_tip' => false,
//			'advanced' => false,
//			'group'    => __( 'Advanced', 'geodirectory' ),
//		);

		return $arguments;
	}


	/**
	 * The Super block output function.
	 *
	 * @param array $instance Settings for the widget instance.
	 * @param array $widget_args Display arguments including 'before_title', 'after_title',
	 *                           'before_widget', and 'after_widget'.
	 * @param string $content
	 *
	 * @return mixed|string|void
	 */
	public function output( $instance = array(), $widget_args = array(), $content = '' ) {
		ob_start();
		/**
		 * @var bool $ajax_load Ajax load or not.
		 * @var string $animation Fade or slide.
		 * @var bool $slideshow Auto start or not.
		 * @var int $controlnav 0 = none, 1 =  standard, 2 = thumbnails
		 * @var bool $show_title If the title should be shown or not.
		 * @var int/empty $limit If the number of images should be limited.
		 */
		extract( $instance, EXTR_SKIP );

		$design_style = ! empty( $instance['design_style'] ) ? esc_attr( $instance['design_style'] ) : geodir_design_style();

		// Enqueue widget scripts on call.
		geodir_widget_enqueue_scripts( $instance, $this );

		// Set the CPT to be used.
		if ( isset( $post_type ) && $post_type && geodir_is_gd_post_type( $post_type ) ) {
			geodir_get_search_post_type( $post_type ); // set the post type
		} else {
			geodir_get_search_post_type(); // set the post type
		}

		// maybe remove advanced search main fields
		if( !empty($instance['show']) && 'main-no-advanced' === $instance['show']){
			remove_action( 'geodir_before_search_form', 'geodir_search_add_to_main', 0 );
		}

		// Set if the cpt selector, search, near should be hidden
		global $geodir_search_widget_params;

		// filters position
		$geodir_search_widget_params['filters_pos'] = ! empty( $instance['filters_pos'] ) ? esc_attr($instance['filters_pos']) : '';

		// Inputs classes
		$geodir_search_widget_params['main_search_inputs_class'] = sd_build_aui_class(
			array(
				'border'         => isset( $instance['input_border'] ) ? $instance['input_border'] : '',
				'border_opacity' => isset( $instance['input_border_opacity'] ) ? $instance['input_border_opacity'] : '',
				'rounded_size'   => isset( $instance['input_rounded_size'] ) ? $instance['input_rounded_size'] : '',
			)
		);

		// Buttons classes
		$geodir_search_widget_params['btn_icon_class'] = '';
		if ( $design_style ) {
			$geodir_search_widget_params['buttons_class'] = sd_build_aui_class(
				array(
					'rounded_size'    => isset( $instance['btn_rounded_size'] ) ? $instance['btn_rounded_size'] : '',
					'rounded_size_md' => isset( $instance['btn_rounded_size_md'] ) ? $instance['btn_rounded_size_md'] : '',
					'rounded_size_lg' => isset( $instance['btn_rounded_size_lg'] ) ? $instance['btn_rounded_size_lg'] : '',
				)
			);

			$geodir_search_widget_params['buttons_class'] .= ! empty( $instance['btn_bg'] ) ? ' btn-' . esc_attr( $instance['btn_bg'] ) : ' btn-primary';
			$geodir_search_widget_params['buttons_class'] .= ! empty( $instance['input_size'] ) ? ' btn-' . esc_attr( $instance['input_size'] ) : '';

			if ( ( isset( $instance['btn_rounded_size'] ) && 'circle' === $instance['btn_rounded_size'] ) || ( isset( $instance['btn_rounded_size_md'] ) && 'circle' === $instance['btn_rounded_size_md'] ) || ( isset( $instance['btn_rounded_size_lg'] ) && 'circle' === $instance['btn_rounded_size_lg'] ) ) {
				$geodir_search_widget_params['buttons_class'] .= ' px-3';

				if ( ! ( isset( $instance['input_size'] ) && $instance['input_size'] == 'lg' ) ) {
					$geodir_search_widget_params['btn_icon_class'] = ' mx-n1';
				}
			}
		} else {
			$geodir_search_widget_params['buttons_class'] = '';
		}

		// input size
		$geodir_search_widget_params['input_size'] = ! empty( $instance['input_size'] ) ? esc_attr( $instance['input_size'] ) : '';

		if ( isset( $instance['input_border'] ) && '0' === $instance['input_border'] ) {
			$geodir_search_widget_params['main_search_inputs_class'] .= ' shadow-none';
		}

		if ( isset( $post_type_hide ) && $post_type_hide ) {
			$geodir_search_widget_params['post_type_hide'] = true;
		}

		if ( ! empty( $hide_search_input ) ) {
			$geodir_search_widget_params['hide_search_input'] = true;
		}

		if ( ! empty( $hide_near_input ) ) {
			$geodir_search_widget_params['hide_near_input'] = true;
		}

		$template     = $design_style ? $design_style . '/search-bar/form.php' : 'listing-filter-form.php';

		if ( $design_style && ! empty( $instance ) ) {
			$keep_args = $instance;
			if ( isset( $keep_args['post_type'] ) ) {
				unset( $keep_args['post_type'] );
			}
			if ( isset( $keep_args['post_type_hide'] ) ) {
				unset( $keep_args['post_type_hide'] );
			}
			if ( isset( $keep_args['customize_filters'] ) ) {
				unset( $keep_args['customize_filters'] );
			}
		} else {
			$keep_args = array();
		}

		$form_class = 'geodir-listing-search gd-search-bar-style';

		$show = '';
		if ( $design_style && ! empty( $instance['show'] ) ) {
			$show        = geodir_clean( $instance['show'] );
			$form_class .= ' geodir-search-show-' . sanitize_html_class( $show );
		} else {
			$form_class .= ' geodir-search-show-all';
		}

		if ( 'absolute' === $geodir_search_widget_params['filters_pos'] ) {
			$form_class .= ' position-relative zindex-1';
		}

		/**
		 * Filters the GD search form class.
		 *
		 * @since 1.0.0
		 * @param string $form_class The class for the search form, default: 'geodir-listing-search'.
		 * @param string $wrap_class The wrapper class for styles.
		 */
		$form_class = apply_filters( 'geodir_search_form_class', $form_class, $instance );
		//            print_r( $instance );

		$bar_class = sd_build_aui_class(
			array(
				'flex_wrap'    => isset( $instance['bar_flex_wrap'] ) ? $instance['bar_flex_wrap'] : '',
				'flex_wrap_md' => isset( $instance['bar_flex_wrap_md'] ) ? $instance['bar_flex_wrap_md'] : '',
				'flex_wrap_lg' => isset( $instance['bar_flex_wrap_lg'] ) ? $instance['bar_flex_wrap_lg'] : '',
			)
		);

		$tmpl_args = array(
			'wrap_class' => sd_build_aui_class( $instance ),
			'form_class' => $form_class,
			'bar_class'  => $bar_class,
			'instance'   => $instance,
			'keep_args'  => $keep_args,
			'show'       => $show,
		);

		//      print_r( $tmpl_args );

		$template_params = array(
			'template'      => $template,
			'template_args' => $tmpl_args,
			'template_path' => '',
			'default_path'  => '',
		);

		/**
		 * Filter the template parameters.
		 *
		 * @since 2.2.6
		 *
		 * @param array $template_params Template parameters.
		 * @param array $instance Settings for the widget instance.
		 * @param array $widget_args Widget display arguments.
		 */
		$template_params = apply_filters( 'geodir_search_form_template_params', $template_params, $instance, $widget_args );

		echo geodir_get_template_html( $template_params['template'], $template_params['template_args'], $template_params['template_path'], $template_params['default_path'] );

		// After outputing the search reset the CPT
		global $geodir_search_post_type;
		$geodir_search_post_type     = '';
		$geodir_search_widget_params = array();

		//      $geodir_search_post_type_hide    = false;
		//      $geodir_hide_search_input        = false;
		//      $geodir_hide_near_input          = false;
		//      $geodir_main_search_inputs_class = '';

		return ob_get_clean();
	}

	/**
	 * Get the post type options for search.
	 *
	 * @since 2.0.0
	 *
	 * @return array $options
	 */
	public function post_type_options() {
		$options    = array();
		$post_types = geodir_get_posttypes( 'options-plural' );
		if ( ! empty( $post_types ) ) {
			$options = array( '' => __( 'Auto', 'geodirectory' ) );
			$options = array_merge( $options, $post_types );
		}

		return $options;
	}
}
