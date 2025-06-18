<?php
/**
 * GeoDirectory cpt categories widget.
 *
 * @package GeoDirectory
 * @since 1.5.4
 */

/**
 * GeoDirectory categories widget class.
 *
 * @since 1.5.4
 */
class GeoDir_Widget_Categories extends WP_Super_Duper {

	/**
	 * Register the categories with WordPress.
	 *
	 * @since 2.0.0
	 *
	 */
	public function __construct() {

		$options = array(
			'textdomain'       => GEODIRECTORY_TEXTDOMAIN,
			'block-icon'       => 'admin-site',
			'block-category'   => 'geodirectory',
			'block-keywords'   => "['categories','geo','taxonomy']",
			'block-supports'   => array(
				'customClassName' => false,
			),
			'class_name'       => __CLASS__,
			'base_id'          => 'gd_categories', // this us used as the widget id and the shortcode id.
			'name'             => __( 'GD > Categories', 'geodirectory' ), // the name of the widget.
			'widget_ops'       => array(
				'classname'                   => 'geodir-categories-container ' . geodir_bsui_class(),
				// widget class
				'description'                 => esc_html__( 'Shows a list of GeoDirectory categories.', 'geodirectory' ),
				// widget description
				'customize_selective_refresh' => true,
				'geodirectory'                => true,
			),
			'block_group_tabs' => array(
				'content'  => array(
					'groups' => array(
						__( 'Title', 'geodirectory' ),
						__( 'Filters', 'geodirectory' ),
						__( 'Sorting', 'geodirectory' ),
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
						__( 'Card Design', 'geodirectory' ),
						__( 'Icon / Image', 'geodirectory' ),
						__( 'Category Text', 'geodirectory' ),
						__( 'Count Text', 'geodirectory' ),
						__( 'CPT Title', 'geodirectory' ),
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

		$design_style = geodir_design_style();
		$arguments    = array();

		$arguments['title'] = array(
			'title'    => __( 'Title:', 'geodirectory' ),
			'desc'     => __( 'The widget title.', 'geodirectory' ),
			'type'     => 'text',
			'default'  => '',
			'desc_tip' => true,
			'group'    => __( 'Title', 'geodirectory' ),
			'advanced' => false,
		);

		if ( $design_style ) {
			// title styles
			$arguments = $arguments + geodir_get_sd_title_inputs();
		}

		$arguments['post_type'] = array(
			'title'    => __( 'Post Type:', 'geodirectory' ),
			'desc'     => __( 'The custom post types to show by default. Only used when there are multiple CPTs.', 'geodirectory' ),
			'type'     => 'select',
			'options'  => $this->post_type_options(),
			'default'  => '0',
			'desc_tip' => true,
			'advanced' => false,
			'group'    => __( 'Filters', 'geodirectory' ),
		);

		$arguments['cpt_ajax']        = array(
			'title'    => __( 'Add CPT ajax select:', 'geodirectory' ),
			'desc'     => __( 'Add CPT list as a dropdown.', 'geodirectory' ),
			'type'     => 'checkbox',
			'desc_tip' => true,
			'value'    => '1',
			'default'  => 0,
			'advanced' => false,
			'group'    => __( 'Filters', 'geodirectory' ),
		);
		$arguments['filter_ids']      = array(
			'type'        => 'text',
			'title'       => __( 'Include/exclude categories:', 'geodirectory' ),
			'desc'        => __( 'Enter a comma separated list of category ids (21,8,43) to show the these categories, or a negative list (-21,-8,-43) to exclude these categories.', 'geodirectory' ),
			'default'     => '',
			'desc_tip'    => true,
			'advanced'    => false,
			'placeholder' => '21,8,43 (default: empty)',
			'group'       => __( 'Filters', 'geodirectory' ),
		);
		$arguments['hide_empty']      = array(
			'title'    => __( 'Hide empty:', 'geodirectory' ),
			'desc'     => __( 'This will hide categories that do not have any listings.', 'geodirectory' ),
			'type'     => 'checkbox',
			'desc_tip' => true,
			'value'    => '1',
			'default'  => 0,
			'advanced' => false,
			'group'    => __( 'Filters', 'geodirectory' ),
		);
		$arguments['max_level']       = array(
			'title'    => __( 'Max sub-cat depth:', 'geodirectory' ),
			'desc'     => __( 'The maximum number of sub category levels to show.', 'geodirectory' ),
			'type'     => 'select',
			'options'  => array_merge( array( 'all' => __( 'All', 'geodirectory' ) ), range( 0, 10 ) ),
			'default'  => '1',
			'desc_tip' => true,
			'advanced' => false,
			'group'    => __( 'Filters', 'geodirectory' ),
		);
		$arguments['max_count']       = array(
			'title'    => __( 'Max cats to show per CPT:', 'geodirectory' ),
			'desc'     => __( 'The maximum number of categories to show per CPT.', 'geodirectory' ),
			'type'     => 'select',
			'options'  => array_merge( array( 'all' => __( 'All', 'geodirectory' ) ), range( 0, 10 ) ),
			'default'  => 'all',
			'desc_tip' => true,
			'advanced' => false,
			'group'    => __( 'Filters', 'geodirectory' ),
		);
		$arguments['max_count_child'] = array(
			'title'    => __( 'Max sub-cat to show:', 'geodirectory' ),
			'desc'     => __( 'The maximum number of sub categories to show.', 'geodirectory' ),
			'type'     => 'select',
			'options'  => array_merge( array( 'all' => __( 'All', 'geodirectory' ) ), range( 0, 10 ) ),
			'default'  => 'all',
			'desc_tip' => true,
			'advanced' => false,
			'group'    => __( 'Filters', 'geodirectory' ),
		);
		$arguments['no_cpt_filter']   = array(
			'title'    => __( 'Do not filter for current viewing post type', 'geodirectory' ),
			'type'     => 'checkbox',
			'desc_tip' => true,
			'value'    => '1',
			'default'  => 0,
			'advanced' => false,
			'group'    => __( 'Filters', 'geodirectory' ),
		);
		$arguments['no_cat_filter']   = array(
			'title'    => __( 'Tick to show all the categories. Leave unticked to show only child categories of current viewing category.', 'geodirectory' ),
			'type'     => 'checkbox',
			'desc_tip' => true,
			'value'    => '1',
			'default'  => 0,
			'advanced' => false,
			'group'    => __( 'Filters', 'geodirectory' ),
		);
		$arguments['sort_by']         = array(
			'title'    => __( 'Sort by:', 'geodirectory' ),
			'desc'     => __( 'Sort categories by.', 'geodirectory' ),
			'type'     => 'select',
			'options'  => array(
				'count' => __( 'Count', 'geodirectory' ),
				'az'    => __( 'A-Z', 'geodirectory' ),
			),
			'default'  => 'count',
			'desc_tip' => true,
			'advanced' => false,
			'group'    => __( 'Sorting', 'geodirectory' ),
		);

		if ( $design_style ) {
			$arguments['design_type'] = array(
				'title'    => __( 'Design Type', 'geodirectory' ),
				'desc'     => __( 'Set the design type', 'geodirectory' ),
				'type'     => 'select',
				'options'  => array(
					'icon-left' => __( 'Icon Left', 'geodirectory' ),
					'icon-top'  => __( 'Icon Top', 'geodirectory' ),
					'image'     => __( 'Image Background', 'geodirectory' ),
				),
				'default'  => '',
				'desc_tip' => true,
				'advanced' => false,
				'group'    => __( 'Card Design', 'geodirectory' ),
			);

		}

		if ( ! $design_style ) {
			$arguments['cpt_left'] = array(
				'title'    => __( 'Show single column:', 'geodirectory' ),
				'desc'     => __( 'This will show list in single column.', 'geodirectory' ),
				'type'     => 'checkbox',
				'desc_tip' => true,
				'value'    => '1',
				'default'  => 0,
				'advanced' => false,
				'group'    => __( 'Card Design', 'geodirectory' ),
			);
		}

		if ( $design_style ) {

			$arguments['row_items']           = array(
				'title'    => __( 'Row Items', 'geodirectory' ),
				'desc'     => __( 'The number of items in a row on desktop view.', 'geodirectory' ),
				'type'     => 'select',
				'options'  => array(
					''  => __( 'Default (3)', 'geodirectory' ),
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
					'6' => '6',
				),
				'default'  => '',
				'desc_tip' => true,
				'advanced' => false,
				'group'    => __( 'Card Design', 'geodirectory' ),
			);
			$arguments['row_positioning']     = array(
				'title'    => __( 'Row Positioning', 'geodirectory' ),
				'desc'     => __( 'Positions items that do not fill a whole row.', 'geodirectory' ),
				'type'     => 'select',
				'options'  => array(
					''       => __( 'Default (left)', 'geodirectory' ),
					'center' => __( 'Center', 'geodirectory' ),
					'right'  => __( 'Right', 'geodirectory' ),
				),
				'default'  => '',
				'desc_tip' => true,
				'advanced' => false,
				'group'    => __( 'Card Design', 'geodirectory' ),
			);
			$arguments['card_padding_inside'] = array(
				'title'           => __( 'Card Padding Inside', 'geodirectory' ),
				'desc'            => __( 'Set the inside padding for the card', 'geodirectory' ),
				'type'            => 'select',
				'options'         => array(
					''  => '3 (default)',
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
				),
				'default'         => '',
				'desc_tip'        => true,
				'advanced'        => false,
				'element_require' => '[%design_type%]!="image"',
				'group'           => __( 'Card Design', 'geodirectory' ),
			);
			$arguments['card_color']          = array(
				'title'           => __( 'Card Color', 'geodirectory' ),
				'desc'            => __( 'Set the card color', 'geodirectory' ),
				'type'            => 'select',
				'options'         => array(
					''            => __( 'Select color', 'geodirectory' ),
					'transparent' => __( 'Transparent', 'geodirectory' ),
				) + geodir_aui_colors( false, true ),
				'default'         => '',
				'desc_tip'        => true,
				'advanced'        => false,
				'element_require' => '[%design_type%]!="image"',
				'group'           => __( 'Card Design', 'geodirectory' ),
			);

			$arguments['card_shadow'] = array(
				'title'    => __( 'Card shadow', 'geodirectory' ),
				'desc'     => __( 'Set the card shadow style.', 'geodirectory' ),
				'type'     => 'select',
				'options'  => array(
					'small'  => __( 'Default(small)', 'geodirectory' ),
					'medium' => __( 'Medium', 'geodirectory' ),
					'large'  => __( 'Large', 'geodirectory' ),
					'none'   => __( 'None', 'geodirectory' ),
				),
				'default'  => '',
				'desc_tip' => true,
				'advanced' => false,
				'group'    => __( 'Card Design', 'geodirectory' ),
			);
		}

			$arguments['hide_icon'] = array(
				'title'    => __( 'Hide icon:', 'geodirectory' ),
				'desc'     => __( 'This will hide the category icons from the list.', 'geodirectory' ),
				'type'     => 'checkbox',
				'desc_tip' => true,
				'value'    => '1',
				'default'  => 0,
				'advanced' => false,
				'group'    => __( 'Icon / Image', 'geodirectory' ),
			);

			$arguments['use_image'] = array(
				'title'    => __( 'Use category image:', 'geodirectory' ),
				'desc'     => __( 'This will use the category default image instead of the icons.', 'geodirectory' ),
				'type'     => 'checkbox',
				'desc_tip' => true,
				'value'    => '1',
				'default'  => 0,
				'advanced' => false,
				'group'    => __( 'Icon / Image', 'geodirectory' ),
			);

			$arguments['image_size'] = array(
				'type'            => 'select',
				'title'           => __( 'Image size:', 'geodirectory' ),
				'desc'            => __( 'Image size to show category image.', 'geodirectory' ),
				'options'         => self::get_image_sizes(),
				'value'           => '',
				'default'         => 'medium',
				'desc_tip'        => true,
				'advanced'        => false,
				'element_require' => '([%use_image%]=="1" || [%design_type%]=="image")',
				'group'           => __( 'Icon / Image', 'geodirectory' ),
			);

			if ( $design_style ) {

				$arguments['icon_color'] = array(
					'title'           => __( 'Icon Color', 'geodirectory' ),
					'desc'            => __( 'Set the icon color', 'geodirectory' ),
					'type'            => 'select',
					'options'         => array(
						'' => __( 'Use Category Color (default)', 'geodirectory' ),
					) + sd_aui_colors( false, false, false, true ),
					'default'         => '',
					'desc_tip'        => true,
					'advanced'        => false,
					'element_require' => '[%design_type%]!="image"',
					'group'           => __( 'Icon / Image', 'geodirectory' ),
				);

				$arguments['icon_size'] = array(
					'title'           => __( 'Icon Size', 'geodirectory' ),
					'desc'            => __( 'Set the icon size', 'geodirectory' ),
					'type'            => 'select',
					'options'         => array(
						''           => __( 'Boxed Small', 'geodirectory' ),
						'box-small-medium' => __( 'Boxed Small-Medium', 'geodirectory' ),
						'box-medium' => __( 'Boxed Medium', 'geodirectory' ),
						'box-large'  => __( 'Boxed Large', 'geodirectory' ),
						'h1'         => 'XXL',
						'h2'         => 'XL',
						'h3'         => 'L',
						'h4'         => 'M',
						'h5'         => 'S',
						'h6'         => 'XS',
					),
					'default'         => '',
					'desc_tip'        => true,
					'advanced'        => false,
					'element_require' => '[%design_type%]!="image"',
					'group'           => __( 'Icon / Image', 'geodirectory' ),
				);

				// category text

				// text color
				$arguments = $arguments + sd_get_text_color_input_group( 'cat_text_color', array( 'group' => __( 'Category Text', 'geodirectory' ) ), array( 'group' => __( 'Category Text', 'geodirectory' ) ) );

				// font size
				$arguments = $arguments + sd_get_font_size_input_group( 'cat_font_size', array( 'group' => __( 'Category Text', 'geodirectory' ) ), array( 'group' => __( 'Category Text', 'geodirectory' ) ) );

				// font weight
				$arguments['cat_font_weight'] = sd_get_font_weight_input( 'cat_font_weight', array( 'group' => __( 'Category Text', 'geodirectory' ) ) );

				// font case
				$arguments['cat_font_case'] = sd_get_font_case_input( 'cat_font_case', array( 'group' => __( 'Category Text', 'geodirectory' ) ) );

			}

			$arguments['hide_count'] = array(
				'title'    => __( 'Hide count:', 'geodirectory' ),
				'desc'     => __( 'This will show the number of listings in the categories.', 'geodirectory' ),
				'type'     => 'checkbox',
				'desc_tip' => true,
				'value'    => '1',
				'default'  => 0,
				'advanced' => false,
				'group'    => __( 'Count Text', 'geodirectory' ),
			);

			if ( $design_style ) {
				// count text
				$arguments['badge_position'] = array(
					'title'           => __( 'Count Position', 'geodirectory' ),
					'type'            => 'select',
					'options'         => array(
						''          => __( 'Inline', 'geodirectory' ),
						'block'     => __( 'Below', 'geodirectory' ),
						'top-right' => __( 'Top right (BS5)', 'geodirectory' ),
					),
					'default'         => '',
					'desc_tip'        => true,
					'advanced'        => false,
					'element_require' => '[%hide_count%]==""',
					'group'           => __( 'Count Text', 'geodirectory' ),
				);

				$arguments['badge_color'] = array(
					'title'           => __( 'Badge Color', 'geodirectory' ),
					'type'            => 'select',
					'options'         => array(
						''     => __( 'Default (light)', 'geodirectory' ),
						'none' => __( 'None', 'geodirectory' ),
					) + sd_aui_colors( false, false, false, true ),
					'default'         => 'light',
					'desc_tip'        => true,
					'advanced'        => false,
					'element_require' => '[%hide_count%]==""',
					'group'           => __( 'Count Text', 'geodirectory' ),
				);

				$arguments['badge_text_append'] = array(
					'title'           => __( 'Text Append', 'geodirectory' ),
					'type'            => 'select',
					'options'         => array(
						''         => __( 'None', 'geodirectory' ),
						'cpt'      => __( 'Post type name', 'geodirectory' ),
						'items'    => __( 'Items', 'geodirectory' ),
						'listings' => __( 'Listings', 'geodirectory' ),
						'options'  => __( 'Options', 'geodirectory' ),
					),
					'default'         => 'light',
					'desc_tip'        => true,
					'advanced'        => false,
					'element_require' => '[%hide_count%]==""',
					'group'           => __( 'Count Text', 'geodirectory' ),
				);

				// text color
				$arguments = $arguments + sd_get_text_color_input_group( 'badge_text_color', array( 'group' => __( 'Count Text', 'geodirectory' ) ), array( 'group' => __( 'Category Text', 'geodirectory' ) ) );

				// font size
				$arguments = $arguments + sd_get_font_size_input_group( 'badge_font_size', array( 'group' => __( 'Count Text', 'geodirectory' ) ), array( 'group' => __( 'Category Text', 'geodirectory' ) ) );

				// font weight
				$arguments['badge_font_weight'] = sd_get_font_weight_input( 'badge_font_weight', array( 'group' => __( 'Count Text', 'geodirectory' ) ) );

				// font case
				$arguments['badge_font_case'] = sd_get_font_case_input( 'badge_font_case', array( 'group' => __( 'Count Text', 'geodirectory' ) ) );

			}

			$arguments['cpt_title'] = array(
				'title'    => __( 'Show CPT title:', 'geodirectory' ),
				'desc'     => __( 'Tick to show CPT title. Ex: Place Categories', 'geodirectory' ),
				'type'     => 'checkbox',
				'desc_tip' => true,
				'value'    => '1',
				'default'  => 0,
				'advanced' => false,
				'group'    => __( 'CPT Title', 'geodirectory' ),
			);
			$arguments['title_tag'] = array(
				'title'    => __( 'Title tag:', 'geodirectory' ),
				'desc'     => __( 'The tag used to display the auto generated CPT title.', 'geodirectory' ),
				'type'     => 'select',
				'options'  => array(
					'h6'   => 'h6',
					'h5'   => 'h5',
					'h4'   => 'h4',
					'h3'   => 'h3',
					'h2'   => 'h2',
					'span' => 'span',
				),
				'default'  => 'h4',
				'desc_tip' => true,
				'advanced' => false,
				'group'    => __( 'CPT Title', 'geodirectory' ),
			);

			if ( $design_style ) {

				// background
				$arguments['bg'] = geodir_get_sd_background_input();

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
				$arguments['border']       = geodir_get_sd_border_input( 'border' );
				$arguments['rounded']      = geodir_get_sd_border_input( 'rounded' );
				$arguments['rounded_size'] = geodir_get_sd_border_input( 'rounded_size' );

				// shadow
				$arguments['shadow'] = geodir_get_sd_shadow_input( 'shadow' );

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

		add_action( 'wp_footer', array( $this, 'add_js' ) );

		ob_start();
		// options
		$defaults = array(
			'post_type'           => '0', // 0 =  all
			'hide_empty'          => '0',
			'hide_count'          => '0',
			'use_image'           => '0',
			'image_size'          => 'medium',
			'cpt_ajax'            => '0',
			'filter_ids'          => array(), // comma separated ids or array
			'title_tag'           => 'h4',
			'cpt_title'           => '',
			'card_color'          => 'outline-primary',
			'card_shadow'         => 'small',
			'icon_color'          => '',
			'icon_size'           => 'box-small',
			'design_type'         => 'icon-left',
			'row_items'           => '3',
			'row_positioning'     => '',
			'card_padding_inside' => '3',
			'bg'                  => '',
			'mt'                  => '',
			'mb'                  => '3',
			'mr'                  => '',
			'ml'                  => '',
			'pt'                  => '',
			'pb'                  => '',
			'pr'                  => '',
			'pl'                  => '',
			'border'              => '',
			'rounded'             => '',
			'rounded_size'        => '',
			'shadow'              => '',
		);

		/**
		 * Parse incoming $args into an array and merge it with $defaults
		 */
		$options = wp_parse_args( $args, $defaults );

		// sanitize tag
		$options['title_tag'] = in_array( $options['title_tag'], array( 'h2', 'h3', 'h4', 'h5', 'h6', 'span' ), true ) ? esc_attr( $options['title_tag'] ) : 'h4';

		if ( empty( $options['card_color'] ) ) {
			$options['card_color'] = $defaults['card_color'];}
		if ( empty( $options['icon_size'] ) ) {
			$options['icon_size'] = $defaults['icon_size'];}
		if ( empty( $options['design_type'] ) ) {
			$options['design_type'] = $defaults['design_type'];}
		if ( empty( $options['card_padding_inside'] ) ) {
			$options['card_padding_inside'] = $defaults['card_padding_inside'];}

		$output = self::categories_output( $options );

		$ajax_class = ! empty( $options['cpt_ajax'] ) ? ' gd-wgt-cpt-ajax' : '';

		// wrapper class
		$wrap_class = geodir_build_aui_class( $options );

		if ( $output ) {
			echo '<div class="gd-categories-widget ' . $ajax_class . ' ' . $wrap_class . '">';
			echo $output;
			echo '</div>';
		}

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
		$options = array( '0' => __( 'Auto', 'geodirectory' ) );

		$post_types = geodir_get_posttypes( 'options-plural' );
		if ( ! empty( $post_types ) ) {
			$options = array_merge( $options, $post_types );
		}

		//print_r($options);

		return $options;
	}

	/**
	 * Get categories.
	 *
	 * @since 2.0.0
	 *
	 * @param array $params Category parameter.
	 */
	public static function get_categories( $params ) {
		$params['via_ajax'] = true;
		$output = self::categories_output( $params );

		if ( empty( $output ) ) {
			$output = '<div class="gd-cptcats-empty alert alert-info">' . __( 'No categories found', 'geodirectory' ) . '</div>';
		} else {
			if ( function_exists( 'aui_bs_convert_sd_output' ) ) {
				$output = aui_bs_convert_sd_output( $output );
			}
		}

		echo $output;
	}

	/**
	 * Adds the javascript in the footer for best of widget.
	 *
	 * @since 2.0.0
	 */
	public function add_js() {
		global $geodir_cats_script;

		if ( ! empty( $geodir_cats_script ) ) {
			return;
		}

		$geodir_cats_script = true;
		?>
<script type="text/javascript">if(!window.gdCategoriesJs){document.addEventListener("DOMContentLoaded",function(event){jQuery(".geodir-cat-list-tax").on("change",function(e){e.preventDefault();var $widgetBox=jQuery(this).closest(".geodir-categories-container");var $container=$widgetBox.find(".gd-cptcat-rows:first");$container.addClass("gd-loading p-3 text-center").html('<i class="fas fa-circle-notch fa-spin fa-2x" aria-hidden="true"></i>');var data={action:"geodir_cpt_categories",security:geodir_params.basic_nonce,ajax_cpt:jQuery(this).val()};$widgetBox.find(".gd-wgt-params:first").find("input").each(function(){if(jQuery(this).attr("name")){data[jQuery(this).attr("name")]=jQuery(this).val()}});jQuery.post(geodir_params.ajax_url,data,function(response){$container.removeClass("gd-loading p-3 text-center").html(response)})})});window.gdCategoriesJs=true}</script>
		<?php
	}


	/**
	 * Get the cpt categories content.
	 *
	 * @since 1.5.4
	 * @since 1.6.6 New parameters $no_cpt_filter &no_cat_filter added.
	 *
	 * @global object $post The post object.
	 * @global bool $gd_use_query_vars If true then use query vars to get current location terms.
	 *
	 * @param array $params An array of cpt categories parameters.
	 * @return string CPT categories content.
	 */
	public static function categories_output( $params ) {
		global $aui_bs5, $post, $gd_use_query_vars;

		$old_gd_use_query_vars = $gd_use_query_vars;

		$gd_use_query_vars = geodir_is_page( 'detail' ) ? true : false;

		$instance = $params;

		$args = wp_parse_args(
			(array) $params,
			array(
				'title'               => '',
				'title_tag'           => 'span',
				'post_type'           => array(), // NULL for all
				'hide_empty'          => '',
				'hide_count'          => '',
				'hide_icon'           => '',
				'use_image'           => '',
				'cpt_left'            => '',
				'sort_by'             => 'count',
				'max_count'           => 'all',
				'max_count_child'     => 'all',
				'max_level'           => '1',
				'no_cpt_filter'       => '',
				'no_cat_filter'       => '',
				'cpt_ajax'            => '',
				'filter_ids'          => array(), // comma separated ids or array
				'cpt_title'           => '',
				'card_color'          => 'outline-primary',
				'card_shadow'         => 'small',
				'icon_color'          => '',
				'icon_size'           => 'box-small',
				'design_type'         => 'icon-left',
				'row_items'           => '3',
				'row_positioning'     => '',
				'card_padding_inside' => '3',
				'cat_text_color'      => '',
				'cat_font_size'       => '',
				'cat_font_weight'     => '',
				'cat_font_case'       => '',
				'badge_text_color'    => '',
				'badge_font_size'     => '',
				'badge_font_weight'   => '',
				'badge_font_case'     => '',

				'bg'                  => '',
				'mt'                  => '',
				'mb'                  => '3',
				'mr'                  => '',
				'ml'                  => '',
				'pt'                  => '',
				'pb'                  => '',
				'pr'                  => '',
				'pl'                  => '',
				'border'              => '',
				'rounded'             => '',
				'rounded_size'        => '',
				'shadow'              => '',
			)
		);

		// sanitize tag
		$args['title_tag'] = in_array( $args['title_tag'], array( 'h2', 'h3', 'h4', 'h5', 'h6', 'span' ), true ) ? esc_attr( $args['title_tag'] ) : 'h4';


		$sort_by    = isset( $args['sort_by'] ) && in_array( $args['sort_by'], array( 'az', 'count' ) ) ? sanitize_text_field( $args['sort_by'] ) : 'count';
		$cpt_filter = empty( $args['no_cpt_filter'] ) ? true : false;
		$cat_filter = empty( $args['no_cat_filter'] ) ? true : false;
		$cpt_ajax   = ! empty( $args['cpt_ajax'] ) ? true : false;

		$gd_post_types = geodir_get_posttypes( 'array' );

		$post_type_arr    = ! is_array( $args['post_type'] ) ? explode( ',', $args['post_type'] ) : $args['post_type'];
		$current_posttype = geodir_get_current_posttype();

		$is_listing      = false;
		$is_detail       = false;
		$is_category     = false;
		$current_term_id = 0;
		$post_ID         = 0;
		$is_listing_page = geodir_is_page( 'listing' );
		$is_detail_page  = geodir_is_page( 'detail' );
		if ( $is_listing_page || $is_detail_page || geodir_is_page( 'search' ) ) {
			$current_posttype = geodir_get_current_posttype();

			if ( $current_posttype != '' && isset( $gd_post_types[ $current_posttype ] ) ) {
				if ( $is_detail_page ) {
					$is_detail = true;
					$post_ID   = is_object( $post ) && ! empty( $post->ID ) ? (int) $post->ID : 0;
				} else {
					$is_listing = true;
					if ( is_tax() ) { // category page
						$current_term_id = get_queried_object_id();

						if ( $current_term_id && $current_posttype && get_query_var( 'taxonomy' ) == $current_posttype . 'category' ) {
							$is_category = true;
						}
					} elseif ( geodir_is_page( 'search' ) && isset( $_REQUEST['spost_category'] ) && ( ( is_array( $_REQUEST['spost_category'] ) && ! empty( $_REQUEST['spost_category'][0] ) ) || ( ! is_array( $_REQUEST['spost_category'] ) && ! empty( $_REQUEST['spost_category'] ) ) ) ) {
						$is_category = true;

						if ( is_array( $_REQUEST['spost_category'] ) && 1 == count( $_REQUEST['spost_category'] ) ) {
							$current_term_id = absint( $_REQUEST['spost_category'][0] );
						} else {
							$current_term_id = absint( $_REQUEST['spost_category'] );
						}
					}
				}
			}
		}

		$parent_category = 0;
		if ( ( $is_listing || $is_detail ) && $cpt_filter ) {
			$post_type_arr = array( $current_posttype );
		}

		$post_types = array();
		if ( ! empty( $post_type_arr ) ) {
			if ( in_array( '0', $post_type_arr ) ) {
				$post_types = $gd_post_types;
			} else {
				foreach ( $post_type_arr as $cpt ) {
					if ( isset( $gd_post_types[ $cpt ] ) ) {
						$post_types[ $cpt ] = $gd_post_types[ $cpt ];
					}
				}
			}
		}

		if ( empty( $post_type_arr ) ) {
			$post_types = $gd_post_types;
		}

		$hide_empty      = ! empty( $args['hide_empty'] ) ? true : false;
		$max_count       = strip_tags( $args['max_count'] );
		$max_count_child = strip_tags( $args['max_count_child'] );
		$all_childs      = $max_count_child == 'all' ? true : false;
		$max_count       = $max_count > 0 ? (int) $max_count : 0;
		$max_count_child = $max_count_child > 0 ? (int) $max_count_child : 0;
		$max_level       = $args['max_level'] == 'all' ? 0 : strval( absint( $args['max_level'] ) );
		$hide_count      = ! empty( $args['hide_count'] ) ? true : false;
		$hide_icon       = ! empty( $args['hide_icon'] ) ? true : false;
		$use_image       = ! empty( $args['use_image'] ) ? true : false;
		$cpt_left        = ! empty( $args['cpt_left'] ) ? true : false;
		$image_size      = ! empty( $args['image_size'] ) ? $args['image_size'] : 'medium';

		// Include/exclude terms
		if ( ! empty( $args['filter_ids'] ) ) {
			$filter_ids = is_array( $args['filter_ids'] ) ? implode( ',', $args['filter_ids'] ) : $args['filter_ids'];
		} else {
			$filter_ids = '';
		}

		$filter_terms = array(
			'include' => array(),
			'exclude' => array(),
		);

		if ( ! empty( $filter_ids ) ) {
			$_filter_ids = explode( ',', $filter_ids );

			foreach ( $_filter_ids as $filter_id ) {
				$filter_id = (int) trim( $filter_id );
				$absint_filter_id = absint( $filter_id );

				if ( $absint_filter_id > 0 ) {
					if ( $absint_filter_id != $filter_id ) {
						$filter_terms['exclude'][] = $absint_filter_id;
					} else {
						$filter_terms['include'][] = $absint_filter_id;
					}
				}
			}
		}

		if ( $cpt_left ) {
			$cpt_left_class = 'gd-cpt-flat';
		} else {
			$cpt_left_class = '';
		}

		$orderby = 'count';
		$order   = 'DESC';
		if ( $sort_by == 'az' ) {
			$orderby = 'name';
			$order   = 'ASC';
		}

		$via_ajax     = ! empty( $params['via_ajax'] ) && wp_doing_ajax() ? true : false;
		$ajax_cpt     = ! empty( $params['ajax_cpt'] ) && $via_ajax ? sanitize_text_field( $params['ajax_cpt'] ) : '';
		$set_location = array();
		if ( $via_ajax ) {
			if ( ! empty( $params['ajax_is_listing'] ) ) {
				$is_listing = true;
			}
			if ( ! empty( $params['ajax_is_detail'] ) ) {
				$is_detail = true;
			}
			if ( ! empty( $params['ajax_is_category'] ) ) {
				$is_category = true;
			}
			if ( ! empty( $params['ajax_post_ID'] ) ) {
				$post_ID = absint( $params['ajax_post_ID'] );
			}
			if ( ! empty( $params['ajax_current_term_id'] ) ) {
				$current_term_id = absint( $params['ajax_current_term_id'] );
			}
			if ( GeoDir_Post_types::supports( $ajax_cpt, 'location' ) ) {
				foreach ( $params as $_key => $_value ) {
					if ( strpos( $_key, '_gd_set_loc_' ) === 0 && ( $_key = substr( sanitize_text_field( $_key ), 12 ) ) && ( is_scalar( $_value ) || ( ! is_object( $_value ) && ! is_array( $_value ) ) ) ) {
						$set_location[ $_key ] = sanitize_text_field( stripslashes( $_value ) );
					}
				}
			}
		}

		$output = '';
		if ( ! empty( $post_types ) ) {
			global $geodirectory;
			// Backup
			$backup_geodirectory = $geodirectory;

			$design_style = geodir_design_style();

			$cpt_options = array();
			$cpt_list    = '';
			$cpt_count   = 0;
			$cpt_opened  = false;
			$cpt_closed  = false;
			foreach ( $post_types as $cpt => $cpt_info ) {

				$cpt_count++;
				if ( $ajax_cpt && $ajax_cpt !== $cpt ) {
					continue;
				}

				$args['cpt_name'] = geodir_post_type_name( $cpt, true );
				$args['cpt_singular_name'] = geodir_post_type_singular_name( $cpt, true );

				$args['cpt_name_lcase'] = geodir_strtolower( $args['cpt_name'] );
				$args['cpt_singular_name_lcase'] = geodir_strtolower( $args['cpt_singular_name'] );

				$cpt_options[] = '<option value="' . $cpt . '" ' . selected( $cpt, $current_posttype, false ) . '>' . wp_sprintf( __( '%s Categories', 'geodirectory' ), $args['cpt_singular_name'] ) . '</option>';

				// if ajaxed then only show the first one
				if ( $cpt_ajax && $cpt_list != '' ) {
					continue;}

				if ( $via_ajax && $set_location ) {
					foreach ( $set_location as $_key => $_value ) {
						$geodirectory->location->{$_key} = $_value;
					}
				}

				$parent_category = ( $is_category && $cat_filter && $cpt == $current_posttype ) ? $current_term_id : 0;
				$cat_taxonomy    = $cpt . 'category';
				$skip_childs     = false;

				$category_args = array(
					'orderby'    => $orderby,
					'order'      => $order,
					'hide_empty' => $hide_empty,
					'number'     => $max_count,
				);

				// Include terms
				if ( ! empty( $filter_terms['include'] ) ) {
					$category_args['include'] = $filter_terms['include'];
				}

				// Exclude terms
				if ( ! empty( $filter_terms['exclude'] ) ) {
					$category_args['exclude'] = $filter_terms['exclude'];
				}

				/**
				 * Filters the category arguments passed to get_terms when fetching categories for GD Categories widget
				 */
				$category_args = apply_filters( 'geodir_gd_category_widget_category_args', $category_args, $cat_taxonomy );

				if ( $cat_filter && $cpt == $current_posttype && $is_detail && $post_ID ) {
					$skip_childs                 = true;
					$category_args['object_ids'] = $post_ID;
					$categories                  = get_terms( $cat_taxonomy, $category_args );
				} else {
					if ( empty( $category_args['include'] ) || $parent_category > 0 ) {
						$category_args['parent'] = $parent_category;
					}
					$categories = get_terms( $cat_taxonomy, $category_args );
				}

				if ( $hide_empty ) {
					$categories = geodir_filter_empty_terms( $categories );
				}
				if ( $sort_by == 'count' ) {
					$categories = geodir_sort_terms( $categories, 'count' );
				}

				$close_wrap = false;

				if ( ! empty( $categories ) ) {
					$term_icons = ! $hide_icon ? geodir_get_term_icon() : array();

					$row_class = '';

					if ( $is_listing ) {
						$row_class = $is_category ? ' gd-cptcat-categ' : ' gd-cptcat-listing';
					}

					$cpt_row   = '';
					$open_wrap = true;

					if ( $design_style ) {
						if ( empty( $args['cpt_title'] ) && $cpt_opened ) {
							$open_wrap = false;
						}
					}

					if ( $open_wrap ) {
						$cpt_row .= '<div class="gd-cptcat-row gd-cptcat-' . $cpt . $row_class . ' ' . $cpt_left_class . '">';

						if ( ! $cpt_ajax ) {
							$cpt_opened = true;
						}
					}

					if ( ! empty( $args['cpt_title'] ) && ! $cpt_ajax ) {
						$cpt_row .= '<' . esc_attr( $args['title_tag'] ) . ' class="gd-cptcat-title">' . wp_sprintf( __( '%s Categories', 'geodirectory' ), $args['cpt_singular_name'] ) . '</' . esc_attr( $args['title_tag'] ) . '>';
					}

					if ( $design_style && $open_wrap ) {

						$desktop_class = absint( $args['row_items'] ) ? 'row-cols-md-' . absint( $args['row_items'] ) : 'row-cols-md-3';
						$col_class     = $cpt_left ? 'row-cols-1' : 'row-cols-1 row-cols-sm-2 ' . $desktop_class;

						// row_positioning
						if ( ! empty( $args['row_positioning'] ) && $args['row_positioning'] == 'center' ) {
							$col_class .= ' justify-content-center';
						} elseif ( ! empty( $args['row_positioning'] ) && $args['row_positioning'] == 'right' ) {
							$col_class .= ' justify-content-end';
						}

						$cpt_row .= '<div class="row ' . $col_class . '">';
					}

					foreach ( $categories as $category ) {
						$args['parent_term'] = $category;

						$term_icon = '';
						$cat_color = '';

						if ( ! $hide_icon ) {
							$term_icon_class = '';
							if ( $design_style ) {
								$term_icon_class = 'mw-100 mh-100';
								if ( ! empty( $args['design_type'] ) && $args['design_type'] == 'image' ) {
									$term_icon_class .= ' embed-item-contain align-top card-img';
								}
							}
							$term_icon_url   = ! empty( $term_icons ) && isset( $term_icons[ $category->term_id ] ) ? $term_icons[ $category->term_id ] : '';
							$term_icon_url   = $term_icon_url != '' ? '<img alt="' . esc_attr( $category->name ) . ' icon" src="' . $term_icon_url . '" class="' . $term_icon_class . '"/> ' : '';
							$cat_font_icon   = get_term_meta( $category->term_id, 'ct_cat_font_icon', true );
							$cat_color       = get_term_meta( $category->term_id, 'ct_cat_color', true );
							$cat_color       = $cat_color ? $cat_color : '#ababab';

							// use_image
							if ( $use_image ) {
								$term_image = get_term_meta( $category->term_id, 'ct_cat_default_img', true );
								if ( ! empty( $term_image['id'] ) ) {
									$cat_font_icon        = false;
									$img_background_class = ! empty( $args['design_type'] ) && $args['design_type'] == 'image' ? ' card-img' : 'mw-100 mh-100';
									$img_args             = $design_style ? array( 'class' => 'embed-item-cover-xy align-top ' . $img_background_class ) : array();
									$term_icon_url        = wp_get_attachment_image( $term_image['id'], $image_size, false, $img_args );
								}
							}

							$term_icon = $cat_font_icon ? '<i class="' . $cat_font_icon . '" aria-hidden="true"></i>' : $term_icon_url;
						}

						$term_link = get_term_link( $category, $category->taxonomy );
						/** Filter documented in includes/general_functions.php **/
						$term_link = apply_filters( 'geodir_category_term_link', $term_link, $category->term_id, $cpt );
						$count     = $category->count;

						/**
						 * Whether include child categories posts count in parent category or not.
						 *
						 * @since 2.0.0.96
						 *
						 * @param bool $child_term_count True to include child categories posts count. Default true.
						 * @param int $category->term_id Term ID.
						 * @param string $category->taxonomy Term taxonomy.
						 */
						$child_term_count = apply_filters( 'geodir_categories_include_child_terms_posts_count', true, $category->term_id, $category->taxonomy );

						if ( $child_term_count ) {
							$tax_terms = get_terms( $category->taxonomy, array( 'child_of' => $category->term_id ) );
							if ( ! empty( $tax_terms ) ) {
								foreach ( $tax_terms as $tax_term ) {
									$count += $tax_term->count;
								}
							}
						}

						$count = ! $hide_count ? ' <span class="gd-cptcat-count">' . $count . '</span>' : '';

						$cpt_row .= $design_style ? '<div class="gd-cptcat-ul gd-cptcat-parent col mb-4">' : '<ul class="gd-cptcat-ul gd-cptcat-parent  ' . $cpt_left_class . '">';

						$cpt_row .= self::categories_loop_output( 'gd-cptcat-li-main', $hide_count, $count, $cat_color, $term_link, $category->name, $term_icon, $hide_icon, $use_image, 0, $args );

						$child_cats = '';
						if ( ! $skip_childs && ( $all_childs || $max_count_child > 0 ) && ( $max_level == 'all' || (int) $max_level > 0 ) ) {
							$child_cats .= self::child_cats( $category->term_id, $cpt, $hide_empty, $hide_count, $sort_by, $max_count_child, $max_level, $term_icons, $hide_icon, $use_image, 1, $filter_terms, $args );
						}
						$cpt_row .= $child_cats;

						$cpt_row .= $design_style ? '</div>' : '</li>';

						$cpt_row .= $design_style ? '</div>' : '';
						$cpt_row .= $design_style ? '</div>' : '</ul>';
					}

					$close_wrap = true;
					if ( $design_style ) {
						if ( $cpt_opened && empty( $args['cpt_title'] ) && $cpt_count < count( $post_types ) ) {
							$close_wrap = false;
						}
					}

					if ( $design_style && $close_wrap ) {
						$cpt_row .= '</div>';
					}

					if ( $close_wrap ) {
						$cpt_row .= '</div>';
					}

					$cpt_list .= $cpt_row;

					// check if closed or not
					if ( $design_style && $cpt_count == count( $post_types ) && ! $close_wrap ) {
						$cpt_list .= '</div></div>';
					}
				} else {
					// Check if closed or not
					if ( $design_style && $cpt_count == count( $post_types ) && ! $close_wrap && $cpt_list != '' ) {
						$cpt_list .= '</div></div>';
					}
				}
			}

			if ( ! $via_ajax && $cpt_ajax && ! empty( $cpt_options ) ) {
				global $geodirectory;
				$post_type = is_array( $args['post_type'] ) ? implode( ',', $args['post_type'] ) : ( ! empty( $args['post_type'] ) ? $args['post_type'] : '0' );
				$output .= '<div class="gd-cptcats-select"><div class="gd-wgt-params">';
				$output .= '<input type="hidden" name="post_type" value="' . esc_attr( $post_type ) . '">';
				$output .= '<input type="hidden" name="cpt_ajax" value="' . esc_attr( $cpt_ajax ) . '">';
				$output .= '<input type="hidden" name="filter_ids" value="' . esc_attr( $filter_ids ) . '">';
				$output .= '<input type="hidden" name="cpt_title" value="' . absint( $args['cpt_title'] ) . '">';
				$output .= '<input type="hidden" name="title_tag" value="' . esc_attr( $args['title_tag'] ) . '">';
				$output .= '<input type="hidden" name="hide_empty" value="' . esc_attr( $hide_empty ) . '">';
				$output .= '<input type="hidden" name="hide_count" value="' . esc_attr( $hide_count ) . '">';
				$output .= '<input type="hidden" name="hide_icon" value="' . esc_attr( $hide_icon ) . '">';
				$output .= '<input type="hidden" name="cpt_left" value="' . esc_attr( $cpt_left ) . '">';
				$output .= '<input type="hidden" name="sort_by" value="' . esc_attr( $sort_by ) . '">';
				$output .= '<input type="hidden" name="max_level" value="' . esc_attr( $max_level ) . '">';
				$output .= '<input type="hidden" name="max_count" value="' . esc_attr( $max_count ) . '">';
				$output .= '<input type="hidden" name="no_cpt_filter" value="' . absint( $args['no_cpt_filter'] ) . '">';
				$output .= '<input type="hidden" name="no_cat_filter" value="' . absint( $args['no_cat_filter'] ) . '">';
				$output .= '<input type="hidden" name="ajax_is_listing" value="' . (int) $is_listing . '">';
				$output .= '<input type="hidden" name="ajax_is_detail" value="' . (int) $is_detail . '">';
				$output .= '<input type="hidden" name="ajax_is_category" value="' . (int) $is_category . '">';
				$output .= '<input type="hidden" name="ajax_post_ID" value="' . absint( $post_ID ) . '">';
				$output .= '<input type="hidden" name="ajax_current_term_id" value="' . absint( $current_term_id ) . '">';

				$input_keys = array( 'card_color', 'icon_color', 'icon_size', 'design_type', 'row_items', 'row_positioning', 'card_padding_inside', 'bg', 'mt', 'mr', 'mb', 'ml', 'pt', 'pr', 'pb', 'pl', 'border', 'rounded', 'rounded_size', 'shadow', 'card_shadow', 'fa_icon', 'cat_text_color', 'cat_font_size', 'cat_font_weight', 'cat_font_case', 'badge_text_color', 'badge_font_size', 'badge_font_weight', 'badge_font_case', 'cat_text_color_custom', 'cat_font_size_custom', 'badge_position', 'badge_color', 'badge_text_append', 'badge_text_color_custom', 'badge_font_size_custom', 'css_class' );
				foreach ( $input_keys as $input_key ) {
					if ( isset( $instance[ $input_key ] ) ) {
						$input_value = is_array( $args[ $input_key ] ) ? implode( ",", $args[ $input_key ] ) : $args[ $input_key ];

						$output .= '<input type="hidden" name="' . esc_attr( $input_key ) . '" value="' . ( $input_value !== "" ? esc_attr( $input_value ) : "" ) . '">';
					}
				}

				if ( ! empty( $geodirectory->location ) ) {
					foreach ( $geodirectory->location as $key => $value ) {
						if ( is_scalar( $value ) || ( ! is_object( $value ) && ! is_array( $value ) ) ) {
							$output .= '<input type="hidden" data-set-param="1" name="_gd_set_loc_' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '">';
						}
					}
				}
				$select_class = $design_style ? ( $aui_bs5 ? 'form-select' : 'custom-select' ) . ' form-control mb-3' : 'geodir-select';
				$output .= '</div><select class="geodir-cat-list-tax '. $select_class . '" aria-label="' . esc_attr__( 'CPT Categories', 'geodirectory' ) . '">' . implode( '', $cpt_options ) . '</select>';
				$output .= '</div><div class="gd-cptcat-rows">';
			}
			$output .= $cpt_list;
			if ( ! $via_ajax && $cpt_ajax && ! empty( $cpt_options ) ) {
				$output .= '</div>';
			}

			// Set back
			$geodirectory = $backup_geodirectory;
		}

		$gd_use_query_vars = $old_gd_use_query_vars;

		return $output;
	}

	public static function categories_loop_output( $li_class = 'gd-cptcat-li-main', $hide_count = false, $cat_count = '', $cat_color = '', $term_link = '', $cat_name = '', $cat_icon = false, $hide_icon = false, $use_image = false, $depth = 0, $args = array() ) {
		$cpt_row = '';

		$design_style = geodir_design_style();

		if ( $design_style ) {
			$style = ! empty( $args['design_type'] ) ? esc_attr( $args['design_type'] ) : 'icon-left';
			if ( $style == 'icon-left' ) {
				$style = 'icon-left';} elseif ( $style == 'icon-top' ) {
				$style = 'icon-top';} elseif ( $style == 'image' ) {
					$style = 'image';} else {
					$style = 'icon-left';}
					$style = $depth ? 'sub-item' : $style;

					$card_shadow = ! empty( $args['card_shadow'] ) ? $args['card_shadow'] : 'small';

					// card class
					if ( $card_shadow == 'none' ) {
						$card_class = 'shadow-none';
					} elseif ( $card_shadow == 'medium' ) {
						$card_class = 'shadow';
					} elseif ( $card_shadow == 'large' ) {
						$card_class = 'shadow-lg';
					} else {
						$card_class = 'shadow-sm';
					}

					$term_count = absint( wp_strip_all_tags( $cat_count ) );

					$badge_text_append = ! empty( $args['badge_text_append'] ) ? $args['badge_text_append'] : '';

					if ( 'options' === $badge_text_append ) {
						/* translators: %s: items count */
						$term_count_text = wp_sprintf( _n( '%s option', '%s options', $term_count, 'geodirectory' ), number_format_i18n( $term_count ) );
					} else if ( 'listings' === $badge_text_append ) {
						/* translators: %s: items count */
						$term_count_text = wp_sprintf( _n( '%s listing', '%s listings', $term_count, 'geodirectory' ), number_format_i18n( $term_count ) );
					} else if ( 'items' === $badge_text_append ) {
						/* translators: %s: items count */
						$term_count_text = wp_sprintf( _n( '%s item', '%s items', $term_count, 'geodirectory' ), number_format_i18n( $term_count ) );
					} else if ( 'cpt' === $badge_text_append ) {
						$term_count_text = $term_count > 1 ? number_format_i18n( $term_count ) . ' ' . $args['cpt_name_lcase'] : number_format_i18n( $term_count ) . ' ' . $args['cpt_singular_name_lcase'];
					} else {
						$term_count_text = $term_count;
					}

					$template = $design_style . "/categories/$style.php";

					$cpt_row .= geodir_get_template_html(
						$template,
						array(
							'li_class'   => $li_class,
							'hide_count' => $hide_count,
							'cat_count'  => $cat_count,
							'cat_color'  => $cat_color,
							'term_link'  => $term_link,
							'cat_name'   => $cat_name,
							'cat_icon'   => $cat_icon,
							'hide_icon'  => $hide_icon,
							'use_image'  => $use_image,
							'depth'      => $depth,
							'card_class' => $card_class,
							'args'       => $args,
							'term_count_text' => $term_count_text
						)
					);

		} else {
			$cpt_row .= '<li class="gd-cptcat-li ' . $li_class . '">';
			$count    = ! $hide_count ? ' <span class="gd-cptcat-count">' . $cat_count . '</span>' : '';

			if ( ! $hide_icon ) {
				$cpt_row .= '<span class="gd-cptcat-cat-left" style="background: ' . $cat_color . ';"><a href="' . esc_url( $term_link ) . '" title="' . esc_attr( $cat_name ) . '">';
				$cpt_row .= "<span class='gd-cptcat-icon' >$cat_icon</span>";
				$cpt_row .= '</a></span>';
			}

			$cpt_row .= '<span class="gd-cptcat-cat-right"><a href="' . esc_url( $term_link ) . '" title="' . esc_attr( $cat_name ) . '">';
			$cpt_row .= $cat_name . $count . '</a></span>';
		}

		return $cpt_row;
	}

	/**
	 * Get the child categories content.
	 *
	 * @since 1.5.4
	 * @since 2.0.0.86 New parameter $filter_terms added.
	 *
	 * @param int $parent_id Parent category id.
	 * @param string $cpt The post type.
	 * @param bool $hide_empty If true then filter the empty categories.
	 * @param bool $show_count If true then category count will be displayed.
	 * @param string $sort_by Sorting order for categories.
	 * @param bool|string $max_count Max no of sub-categories count to display.
	 * @param bool|string $max_level Max depth level sub-categories to display.
	 * @param array $term_icons Array of terms icons url.
	 * @param int $depth Category depth level. Default 1.
	 * @param array $filter_terms Array of terms to include/exclude.
	 * @return string Html content.
	 */
	public static function child_cats( $parent_id, $cpt, $hide_empty, $hide_count, $sort_by, $max_count, $max_level, $term_icons, $hide_icon, $use_image, $depth = 1, $filter_terms = array(), $args = array() ) {
		$cat_taxonomy = $cpt . 'category';
		$image_size   = ! empty( $args['image_size'] ) ? $args['image_size'] : 'medium';

		$orderby = 'count';
		$order   = 'DESC';
		if ( $sort_by == 'az' ) {
			$orderby = 'name';
			$order   = 'ASC';
		}

		if ( $max_level != 'all' && $depth > (int) $max_level ) {
			return '';
		}

		$term_args = array(
			'orderby'    => $orderby,
			'order'      => $order,
			'hide_empty' => $hide_empty,
			'parent'     => $parent_id,
			'number'     => $max_count,
		);
		// Include terms
		if ( ! empty( $filter_terms['include'] ) && empty( $parent_id ) ) {
			$term_args['include'] = $filter_terms['include'];
		}

		// Exclude terms
		if ( ! empty( $filter_terms['exclude'] ) ) {
			$term_args['exclude'] = $filter_terms['exclude'];
		}

		$child_cats = get_terms( $cat_taxonomy, $term_args );
		if ( $hide_empty ) {
			$child_cats = geodir_filter_empty_terms( $child_cats );
		}

		if ( empty( $child_cats ) ) {
			return '';
		}

		if ( $sort_by == 'count' ) {
			$child_cats = geodir_sort_terms( $child_cats, 'count' );
		}

		$design_style = geodir_design_style();

		if($design_style ){
			$link_height = !empty($args['card_padding_inside']) && $args['card_padding_inside'] < 3 ? "15px" : "22px";
			$content = $depth == 1 ? '<div class="gd-cptcat-li gd-cptcat-li-sub-container dropdown w-100 position-absolute" style="bottom: 0;left: 0;height:'.$link_height.';">' : '';
			$content .= $depth == 1 ? '<a class="btn btn-link z-index-1 p-0 text-reset w-100 align-top position-relative" href="#" id="cat-submenu-'.$parent_id.'" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label><span class="sr-only visually-hidden">' . __( "Expand sub-categories", "geodirectory" ) . '</span><i class="fas fa-chevron-down align-top"></i></a>' : '';
			$content .= $depth == 1 ? '<ul class="p-0 mt-1 gd-cptcat-ul gd-cptcat-sub gd-cptcat-sub-' . $depth . '  dropdown-menu dropdown-caret-0 w-100" aria-labelledby="cat-submenu-'.$parent_id.'">' : '';
		}else{
			$content = '<li class="gd-cptcat-li gd-cptcat-li-sub-container"><ul class="gd-cptcat-ul gd-cptcat-sub gd-cptcat-sub-' . $depth . '">';
		}

		$depth++;
		foreach ( $child_cats as $category ) {
			$args['child_term'] = $category;

			$term_icon_url = ! empty( $term_icons ) && isset( $term_icons[ $category->term_id ] ) ? $term_icons[ $category->term_id ] : '';
			$term_icon_url = $term_icon_url != '' ? '<img alt="' . esc_attr( $category->name ) . ' icon" src="' . $term_icon_url . '" class=""/> ' : '';
			$cat_font_icon = get_term_meta( $category->term_id, 'ct_cat_font_icon', true );
			$cat_color     = get_term_meta( $category->term_id, 'ct_cat_color', true );
			$cat_color     = $cat_color ? $cat_color : '#ababab';

			// use_image
			if ( $use_image ) {
				$term_image = get_term_meta( $category->term_id, 'ct_cat_default_img', true );
				if ( ! empty( $term_image['id'] ) ) {
					$cat_font_icon = false;
					$term_icon_url = wp_get_attachment_image( $term_image['id'], $image_size );
				}
			}

			$term_icon = $cat_font_icon ? '<i class="fas ' . $cat_font_icon . '" aria-hidden="true"></i>' : $term_icon_url;
			$term_link = get_term_link( $category, $category->taxonomy );
			/** Filter documented in includes/general_functions.php **/
			$term_link = apply_filters( 'geodir_category_term_link', $term_link, $category->term_id, $cpt );
			$count     = ! $hide_count ? ' <span class="gd-cptcat-count">' . $category->count . '</span>' : '';

			$content .= self::categories_loop_output( 'gd-cptcat-li-sub', $hide_count, $count, $cat_color, $term_link, $category->name, $term_icon, $hide_icon, $use_image, $depth, $args );

			$content .= self::child_cats( $category->term_id, $cpt, $hide_empty, $hide_count, $sort_by, $max_count, $max_level, $term_icons, $hide_icon, $use_image, $depth, $filter_terms, $args );
		}

		if ( $design_style ) {
			$content .= $depth == 2 ? '</ul>' : '';
			$content .= $depth == 2 ? '</div>' : '';
		} else {
			$content .= '</li>';
			$content .= '</ul>';
		}

		return $content;
	}

	/**
	 * Get images sizes.
	 *
	 * @since 2.1.0.12
	 *
	 * @return array Image sizes.
	 */
	public static function get_image_sizes() {
		$image_sizes = array(
			'' => 'default (medium)',
		);

		$sizes = get_intermediate_image_sizes();

		if ( ! empty( $sizes ) ) {
			foreach ( $sizes as $size ) {
				$image_sizes[ $size ] = $size;
			}
		}

		$image_sizes['full'] = 'full';

		return $image_sizes;
	}
}
