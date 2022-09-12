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
			'textdomain'    => GEODIRECTORY_TEXTDOMAIN,
			'block-icon'    => 'admin-site',
			'block-category'=> 'geodirectory',
			'block-keywords'=> "['search','geo','geodir']",
			'class_name'    => __CLASS__,
			'base_id'       => 'gd_search', // this us used as the widget id and the shortcode id.
			'name'          => __('GD > Search','geodirectory'), // the name of the widget.
			//'disable_widget'=> true,
			'widget_ops'    => array(
				'classname'   => 'geodir-search-container '.geodir_bsui_class(), // widget class
				'description' => esc_html__('Shows the GeoDirectory search bar.','geodirectory'), // widget description
				'geodirectory' => true,
			),
			'arguments' => array() // keep this
		);

		$post_types =  $this->post_type_options();

		if ( count( $post_types ) > 2 ) {
			$options['arguments'] = array(
				'post_type' => array(
					'title' => __('Default Post Type:', 'geodirectory'),
					'desc' => __('The custom post types to show by default. Only used when there are multiple CPTs.', 'geodirectory'),
					'type' => 'select',
					'options' => $this->post_type_options(),
					'default' => '',
					'desc_tip' => true,
					'advanced' => true
				),
				'post_type_hide' => array(
					'title' => __('Hide Post Type Selector:', 'geodirectory'),
					'desc' => __('Hide the CPT selector (if not on search page) this can be used to setup a specific CPT search and not give the option to change the CPT.', 'geodirectory'),
					'type' => 'checkbox',
					'desc_tip' => true,
					'value' => '1',
					'default' => '',
					'advanced' => true
				)
			);
		}

		$options['arguments']['hide_search_input'] = array(
			'type' => 'checkbox',
			'title' => __( 'Hide Main Search Input:', 'geodirectory' ),
			'desc' => __( 'Hide the main search input.', 'geodirectory' ),
			'value' => '1',
			'default' => '',
			'desc_tip' => true,
			'advanced' => true
		);

		$options['arguments']['hide_near_input'] = array(
			'type' => 'checkbox',
			'title' => __( 'Hide Near Search Input:', 'geodirectory' ),
			'desc' => __( 'Hide the near location search input.', 'geodirectory' ),
			'value' => '1',
			'default' => '',
			'desc_tip' => true,
			'advanced' => true
		);

		$design_style = geodir_design_style();

		if ( $design_style ) {
			$arguments = array();
			// background
			$arguments['bg'] = geodir_get_sd_background_input('mt');
			// margins
			$arguments['mt'] = geodir_get_sd_margin_input('mt');
			$arguments['mr'] = geodir_get_sd_margin_input('mr');
			$arguments['mb'] = geodir_get_sd_margin_input('mb',array('default'=>3));
			$arguments['ml'] = geodir_get_sd_margin_input('ml');

			// padding
			$arguments['pt'] = geodir_get_sd_padding_input('pt');
			$arguments['pr'] = geodir_get_sd_padding_input('pr');
			$arguments['pb'] = geodir_get_sd_padding_input('pb');
			$arguments['pl'] = geodir_get_sd_padding_input('pl');

			// border
			$arguments['border'] = geodir_get_sd_border_input('border');
			$arguments['rounded'] = geodir_get_sd_border_input('rounded');
			$arguments['rounded_size'] = geodir_get_sd_border_input('rounded_size');

			// shadow
			$arguments['shadow'] = geodir_get_sd_shadow_input('shadow');

			$options['arguments'] = $options['arguments'] + $arguments;
		}

		// hidden
		$options['arguments']['show']  = array(
			'type' => 'hidden',
			'name' => 'show',
			'title' => __( 'Show:', 'geodirectory' ),
			'desc' => '',
			'default' => '',
			'desc_tip' => false,
			'advanced' => false
		);

		parent::__construct( $options );
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

		// Set the CPT to be used.
		if ( isset( $post_type ) && $post_type && geodir_is_gd_post_type( $post_type ) ) {
			geodir_get_search_post_type( $post_type ); // set the post type
		} else {
			geodir_get_search_post_type(); // set the post type
		}

		// Set if the cpt selector, search, near should be hidden
		global $geodir_search_post_type_hide, $geodir_hide_search_input, $geodir_hide_near_input;

		if ( isset( $post_type_hide ) && $post_type_hide ) {
			$geodir_search_post_type_hide = true;
		}

		if ( ! empty( $hide_search_input ) ) {
			$geodir_hide_search_input = true;
		}

		if ( ! empty( $hide_near_input ) ) {
			$geodir_hide_near_input = true;
		}

		$design_style = ! empty( $instance['design_style'] ) ? esc_attr( $instance['design_style'] ) : geodir_design_style();
		$template = $design_style ? $design_style . "/search-bar/form.php" : "listing-filter-form.php";

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
			$show = geodir_clean( $instance['show'] );
			$form_class .= ' geodir-search-show-' . sanitize_html_class( $show );
		} else {
			$form_class .= ' geodir-search-show-all';
		}

		/**
		 * Filters the GD search form class.
		 *
		 * @since 1.0.0
		 * @param string $form_class The class for the search form, default: 'geodir-listing-search'.
		 * @param string $wrap_class The wrapper class for styles.
		 */
		$form_class = apply_filters( 'geodir_search_form_class', $form_class, $instance );

		$tmpl_args = array(
			'wrap_class' => geodir_build_aui_class( $instance ),
			'form_class' => $form_class,
			'instance' => $instance,
			'keep_args' => $keep_args,
			'show' => $show
		);

		$template_params = array(
			'template' => $template,
			'template_args' => $tmpl_args,
			'template_path' => '',
			'default_path' => ''
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
		$geodir_search_post_type = '';
		$geodir_search_post_type_hide = false;
		$geodir_hide_search_input = false;
		$geodir_hide_near_input = false;

		return ob_get_clean();
	}

	/**
	 * Get the post type options for search.
	 *
	 * @since 2.0.0
	 *
	 * @return array $options
	 */
	public function post_type_options(){
		$options = array();
		$post_types = geodir_get_posttypes('options-plural');
		if(!empty($post_types)){
		$options = array(''=>__('Auto','geodirectory'));
			$options = array_merge($options,$post_types);
		}

		return $options;
	}
}
