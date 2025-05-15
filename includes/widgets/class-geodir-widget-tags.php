<?php
/**
 * Tags widget.
 *
 * @author    AyeCode Ltd
 * @package   GeoDirectory
 * @version   2.8.103
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GeoDir_Widget_Tags class.
 */
class GeoDir_Widget_Tags extends WP_Super_Duper {

	/**
	 * Sets up a widget instance.
	 *
	 * @since 2.8.103
	 */
	public function __construct() {

		$options = array(
			'base_id'          => 'gd_tags',
			'name'             => __( 'GD > Tags', 'geodirectory' ),
			'class_name'       => __CLASS__,
			'textdomain'       => GEODIRECTORY_TEXTDOMAIN,
			'block-icon'       => 'tag',
			'block-category'   => 'geodirectory',
			'block-supports'   => array(
				'customClassName' => false
			),
			'block-keywords'   => "['geo','tags','taxonomy']",
			'widget_ops'       => array(
				'classname'                   => 'geodir-tags-container ' . geodir_bsui_class(),
				'description'                 => esc_html__( 'Shows a list of GeoDirectory post type tags.', 'geodirectory' ),
				'customize_selective_refresh' => true,
				'geodirectory'                => true
			),
			'block_group_tabs' => array(
				'content'  => array(
					'groups' => array(
						__( 'Title', 'geodirectory' ),
						__( 'CPT Title', 'geodirectory' ),
						__( 'Filters', 'geodirectory' ),
						__( 'Sorting', 'geodirectory' )
					),
					'tab'    => array(
						'title'     => __( 'Content', 'geodirectory' ),
						'key'       => 'bs_tab_content',
						'tabs_open' => true,
						'open'      => true,
						'class'     => 'text-center flex-fill d-flex justify-content-center'
					)
				),
				'styles'   => array(
					'groups' => array(
						__( 'Card Design', 'geodirectory' ),
						__( 'Tag Icon', 'geodirectory' ),
						__( 'Tag Text', 'geodirectory' ),
						__( 'Count Text', 'geodirectory' )
					),
					'tab'    => array(
						'title'     => __( 'Styles', 'geodirectory' ),
						'key'       => 'bs_tab_styles',
						'tabs_open' => true,
						'open'      => true,
						'class'     => 'text-center flex-fill d-flex justify-content-center'
					)
				),
				'advanced' => array(
					'groups' => array(
						__( 'Wrapper Styles', 'geodirectory' ),
						__( 'Advanced', 'geodirectory' )
					),
					'tab'    => array(
						'title'     => __( 'Advanced', 'geodirectory' ),
						'key'       => 'bs_tab_advanced',
						'tabs_open' => true,
						'open'      => true,
						'class'     => 'text-center flex-fill d-flex justify-content-center'
					)
				)
			)
		);

		parent::__construct( $options );
	}

	/**
	 * Set the widget arguments.
	 *
	 * @return array Widget arguments.
	 */
	public function set_arguments() {
		// Don't show for non BootStrap.
		if ( ! geodir_design_style() ) {
			return array();
		}

		$arguments    = array();

		// Content > Title
		$arguments['title'] = array(
			'title'    => __( 'Title:', 'geodirectory' ),
			'desc'     => __( 'The widget title.', 'geodirectory' ),
			'type'     => 'text',
			'default'  => '',
			'desc_tip' => true,
			'advanced' => false,
			'group'    => __( 'Title', 'geodirectory' )
		);

		$arguments = $arguments + geodir_get_sd_title_inputs();

		// Content > CPT Title
		$arguments['cpt_title'] = array(
			'title'    => __( 'Show CPT title:', 'geodirectory' ),
			'desc'     => __( 'Tick to show CPT title. Ex: Place Tags', 'geodirectory' ),
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

		// Content > Filters
		$arguments['post_type'] = array(
			'title'    => __( 'Post Type:', 'geodirectory' ),
			'desc'     => __( 'The custom post types to show by default. Only used when there are multiple CPTs.', 'geodirectory' ),
			'type'     => 'select',
			'options'  => array_merge( array( '0' => __( 'Auto', 'geodirectory' ) ), geodir_get_posttypes( 'options-plural' ) ),
			'default'  => '0',
			'desc_tip' => true,
			'advanced' => false,
			'group'    => __( 'Filters', 'geodirectory' ),
		);

		$arguments['cpt_ajax'] = array(
			'title'    => __( 'Add CPT ajax select:', 'geodirectory' ),
			'desc'     => __( 'Add CPT list as a dropdown.', 'geodirectory' ),
			'type'     => 'checkbox',
			'desc_tip' => true,
			'value'    => '1',
			'default'  => 0,
			'advanced' => false,
			'group'    => __( 'Filters', 'geodirectory' ),
		);

		$arguments['filter_ids'] = array(
			'type'        => 'text',
			'title'       => __( 'Include/exclude tags:', 'geodirectory' ),
			'desc'        => __( 'Enter a comma separated list of tag ids (21,8,43) to show the these tags, or a negative list (-21,-8,-43) to exclude these tags.', 'geodirectory' ),
			'default'     => '',
			'desc_tip'    => true,
			'advanced'    => false,
			'placeholder' => '21,8,43 (default: empty)',
			'group'       => __( 'Filters', 'geodirectory' ),
		);

		$arguments['hide_empty'] = array(
			'title'    => __( 'Hide empty:', 'geodirectory' ),
			'desc'     => __( 'This will hide tags that do not have any listings.', 'geodirectory' ),
			'type'     => 'checkbox',
			'desc_tip' => true,
			'value'    => '1',
			'default'  => 0,
			'advanced' => false,
			'group'    => __( 'Filters', 'geodirectory' ),
		);

		$arguments['max_count'] = array(
			'title'    => __( 'Max tags to show per CPT:', 'geodirectory' ),
			'desc'     => __( 'The maximum number of tags to show per CPT.', 'geodirectory' ),
			'type'     => 'select',
			'options'  => array_merge( array( '0' => __( 'All', 'geodirectory' ) ), range( 1, 50 ) ),
			'default'  => '10',
			'desc_tip' => true,
			'advanced' => false,
			'group'    => __( 'Filters', 'geodirectory' ),
		);

		$arguments['no_cpt_filter'] = array(
			'title'    => __( 'Do not filter for current viewing post type', 'geodirectory' ),
			'type'     => 'checkbox',
			'desc_tip' => true,
			'value'    => '1',
			'default'  => 0,
			'advanced' => false,
			'group'    => __( 'Filters', 'geodirectory' ),
		);

		$arguments['no_tag_filter'] = array(
			'title'    => __( 'Do not filter tags of current viewing listing on the single page.', 'geodirectory' ),
			'type'     => 'checkbox',
			'desc_tip' => true,
			'value'    => '1',
			'default'  => 0,
			'advanced' => false,
			'group'    => __( 'Filters', 'geodirectory' ),
		);

		// Content > Sorting
		$arguments['sort_by'] = array(
			'title'    => __( 'Sort by:', 'geodirectory' ),
			'desc'     => __( 'Sort tags by.', 'geodirectory' ),
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

		// Styles > Card Design
		$arguments['design_type'] = array(
			'title'    => __( 'Design Type', 'geodirectory' ),
			'desc'     => __( 'Set the design type', 'geodirectory' ),
			'type'     => 'select',
			'options'  => array(
				''          => __( 'Default(icon left)', 'geodirectory' ),
				'icon-left' => __( 'Icon Left', 'geodirectory' ),
				'icon-top'  => __( 'Icon Top', 'geodirectory' ),
			),
			'default'  => '',
			'desc_tip' => true,
			'advanced' => false,
			'group'    => __( 'Card Design', 'geodirectory' ),
		);

		$arguments['row_items'] = array(
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

		$arguments['row_positioning'] = array(
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
			'group'           => __( 'Card Design', 'geodirectory' ),
		);

		$arguments['card_color'] = array(
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

		// Styles > Tag Icon
		$arguments['hide_icon'] = array(
			'title'    => __( 'Hide icon:', 'geodirectory' ),
			'desc'     => __( 'This will hide the tag icons from the list.', 'geodirectory' ),
			'type'     => 'checkbox',
			'desc_tip' => true,
			'value'    => '1',
			'default'  => 0,
			'advanced' => false,
			'group'    => __( 'Tag Icon', 'geodirectory' ),
		);

		$arguments['fa_icon'] = array(
			'type'        => 'text',
			'title'       => __( 'Term Icon class (font-awesome)', 'geodirectory' ),
			'desc'        => __( 'FontAwesome icon class to use. Ex: fas fa-tag.', 'geodirectory' ),
			'placeholder' => 'fas fa-tag',
			'default'     => '',
			'desc_tip'    => true,
			'advanced'    => false,
			'element_require' => '![%hide_icon%:checked]',
			'group'           => __( 'Tag Icon', 'geodirectory' )
		);

		$arguments['icon_color'] = array(
			'title'           => __( 'Icon Color', 'geodirectory' ),
			'desc'            => __( 'Set the icon color', 'geodirectory' ),
			'type'            => 'select',
			'options'         => array(
				'' => __( 'Default color', 'geodirectory' ),
			) + sd_aui_colors( false, false, false, true ),
			'default'         => '',
			'desc_tip'        => true,
			'advanced'        => false,
			'element_require' => '![%hide_icon%:checked]',
			'group'           => __( 'Tag Icon', 'geodirectory' ),
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
			'element_require' => '![%hide_icon%:checked]',
			'group'           => __( 'Tag Icon', 'geodirectory' ),
		);

		// Styles > Tag Text
		// Text color
		$arguments = $arguments + sd_get_text_color_input_group( 'tag_text_color', array( 'group' => __( 'Tag Text', 'geodirectory' ) ), array( 'group' => __( 'Tag Text', 'geodirectory' ) ) );

		// Font size
		$arguments = $arguments + sd_get_font_size_input_group( 'tag_font_size', array( 'group' => __( 'Tag Text', 'geodirectory' ) ), array( 'group' => __( 'Tag Text', 'geodirectory' ) ) );

		// Font weight
		$arguments['tag_font_weight'] = sd_get_font_weight_input( 'tag_font_weight', array( 'group' => __( 'Tag Text', 'geodirectory' ) ) );

		// Font case
		$arguments['tag_font_case'] = sd_get_font_case_input( 'tag_font_case', array( 'group' => __( 'Tag Text', 'geodirectory' ) ) );

		// Styles > Count Text
		$arguments['hide_count'] = array(
			'title'    => __( 'Hide count:', 'geodirectory' ),
			'desc'     => __( 'This will show the number of listings in the tags.', 'geodirectory' ),
			'type'     => 'checkbox',
			'desc_tip' => true,
			'value'    => '1',
			'default'  => 0,
			'advanced' => false,
			'group'    => __( 'Count Text', 'geodirectory' ),
		);

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

		// Text color
		$arguments = $arguments + sd_get_text_color_input_group( 'badge_text_color', array( 'group' => __( 'Count Text', 'geodirectory' ) ), array( 'group' => __( 'Tag Text', 'geodirectory' ) ) );

		// Font size
		$arguments = $arguments + sd_get_font_size_input_group( 'badge_font_size', array( 'group' => __( 'Count Text', 'geodirectory' ) ), array( 'group' => __( 'Tag Text', 'geodirectory' ) ) );

		// Font weight
		$arguments['badge_font_weight'] = sd_get_font_weight_input( 'badge_font_weight', array( 'group' => __( 'Count Text', 'geodirectory' ) ) );

		// Font case
		$arguments['badge_font_case'] = sd_get_font_case_input( 'badge_font_case', array( 'group' => __( 'Count Text', 'geodirectory' ) ) );


		// Background
		$arguments['bg'] = geodir_get_sd_background_input();

		// Margins
		$arguments['mt'] = geodir_get_sd_margin_input( 'mt' );
		$arguments['mr'] = geodir_get_sd_margin_input( 'mr' );
		$arguments['mb'] = geodir_get_sd_margin_input( 'mb', array( 'default' => 3 ) );
		$arguments['ml'] = geodir_get_sd_margin_input( 'ml' );

		// Padding
		$arguments['pt'] = geodir_get_sd_padding_input( 'pt' );
		$arguments['pr'] = geodir_get_sd_padding_input( 'pr' );
		$arguments['pb'] = geodir_get_sd_padding_input( 'pb' );
		$arguments['pl'] = geodir_get_sd_padding_input( 'pl' );

		// Border
		$arguments['border']       = geodir_get_sd_border_input( 'border' );
		$arguments['rounded']      = geodir_get_sd_border_input( 'rounded' );
		$arguments['rounded_size'] = geodir_get_sd_border_input( 'rounded_size' );

		// Shadow
		$arguments['shadow'] = geodir_get_sd_shadow_input( 'shadow' );

		$arguments['css_class'] = sd_get_class_input();

		return $arguments;
	}

	/**
	 * Outputs the widget content.
	 *
	 * @param array $instance Settings for the widget instance.
	 * @param array $args     Display arguments.
	 * @param string $content
	 *
	 * @return mixed|string|void
	 */
	public function output( $instance = array(), $args = array(), $content = '' ) {
		// Don't show for non BootStrap.
		if ( ! geodir_design_style() ) {
			return;
		}

		$tags_content = self::get_tags_content( $instance, $args );

		if ( ! $tags_content ) {
			return;
		}

		$wrap_class = sd_build_aui_class( $instance );

		if ( ! empty( $instance['cpt_ajax'] ) ) {
			$wrap_class .= ' geodir-wgt-cpt-ajax';

			// Add footer script.
			add_action( 'wp_footer', array( $this, 'add_footer_script' ), 20 );
		}

		$output = '<div class="geodir-tags-widget ' . esc_attr( trim( $wrap_class ) ) . '">' . $tags_content . '</div>';

		return $output;
	}

	/**
	 * Get the tags content.
	 *
	 * @since 2.8.103.
	 *
	 * @global object $post The post object.
	 * @global bool $gd_use_query_vars If true then use query vars to get current location terms.
	 *
	 * @param array $params An array of cpt tags parameters.
	 * @return string CPT tags content.
	 */
	public static function get_tags_content( $params ) {
		global $post, $geodirectory, $aui_bs5, $gd_use_query_vars;

		// Don't show for non BootStrap.
		if ( ! geodir_design_style() ) {
			return array();
		}

		$instance = $params;

		$defaults = array(
			'title'               => '',
			'title_tag'           => 'h4',
			'post_type'           => array(),
			'hide_empty'          => '',
			'hide_count'          => '',
			'hide_icon'           => '',
			'sort_by'             => 'count',
			'max_count'           => '0',
			'no_cpt_filter'       => '',
			'no_tag_filter'       => '',
			'cpt_ajax'            => '',
			'filter_ids'          => array(),
			'cpt_title'           => '',
			'card_color'          => 'outline-primary',
			'card_shadow'         => 'small',
			'fa_icon'             => 'fas fa-tag',
			'icon_color'          => 'secondary',
			'icon_size'           => 'box-small',
			'design_type'         => '',
			'row_items'           => '3',
			'row_positioning'     => '',
			'card_padding_inside' => '3',
			'tag_text_color'      => '',
			'tag_font_size'       => '',
			'tag_font_weight'     => '',
			'tag_font_case'       => '',
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
		);

		$args = wp_parse_args( $params, $defaults );

		$args['title_tag'] = in_array( $args['title_tag'], array( 'h2', 'h3', 'h4', 'h5', 'h6', 'span' ), true ) ? $args['title_tag'] : 'h4';

		if ( empty( $args['card_color'] ) ) {
			$args['card_color'] = $defaults['card_color'];
		}

		if ( empty( $args['icon_size'] ) ) {
			$args['icon_size'] = $defaults['icon_size'];
		}

		if ( empty( $args['icon_color'] ) ) {
			$args['icon_color'] = $defaults['icon_color'];
		}

		if ( empty( $args['fa_icon'] ) ) {
			$args['fa_icon'] = $defaults['fa_icon'];
		}

		if ( empty( $args['card_padding_inside'] ) ) {
			$args['card_padding_inside'] = $defaults['card_padding_inside'];
		}

		$gd_post_types     = geodir_get_posttypes( 'array' );
		$current_post_type = geodir_get_current_posttype();
		$is_archive_page   = geodir_is_page( 'archive' );
		$is_single_page    = geodir_is_page( 'single' );

		$old_gd_use_query_vars = $gd_use_query_vars;
		$gd_use_query_vars     = $is_single_page;

		$sort_by         = isset( $args['sort_by'] ) && in_array( $args['sort_by'], array( 'az', 'count' ) ) ? sanitize_text_field( $args['sort_by'] ) : 'count';
		$cpt_filter      = empty( $args['no_cpt_filter'] ) ? true : false;
		$tag_filter      = empty( $args['no_tag_filter'] ) ? true : false;
		$cpt_ajax        = ! empty( $args['cpt_ajax'] ) ? true : false;
		$post_type_arr   = ! is_array( $args['post_type'] ) ? explode( ',', $args['post_type'] ) : $args['post_type'];

		$is_archive      = false;
		$is_single       = false;
		$is_tag          = false;
		$current_term_id = 0;
		$post_ID         = 0;

		if ( $is_archive_page || $is_single_page || geodir_is_page( 'search' ) ) {
			if ( $current_post_type != '' && isset( $gd_post_types[ $current_post_type ] ) ) {
				if ( $is_single_page ) {
					$is_single = true;
					$post_ID   = is_object( $post ) && ! empty( $post->ID ) ? (int) $post->ID : 0;
				} else {
					$is_archive = true;

					if ( is_tax() ) {
						$current_term_id = get_queried_object_id();

						if ( $current_term_id && $current_post_type && get_query_var( 'taxonomy' ) == $current_post_type . '_tags' ) {
							$is_tag = true;
						}
					}
				}
			}
		}

		if ( ( $is_archive || $is_single ) && $cpt_filter ) {
			$post_type_arr = array( $current_post_type );
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

		$hide_empty     = ! empty( $args['hide_empty'] ) ? true : false;
		$max_count      = absint( $args['max_count'] );
		if ( $max_count < 0 ) {
			$max_count = 10;
		}
		$hide_count     = ! empty( $args['hide_count'] ) ? true : false;
		$hide_icon      = ! empty( $args['hide_icon'] ) ? true : false;

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
			if ( ! empty( $params['ajax_is_archive'] ) ) {
				$is_archive = true;
			}

			if ( ! empty( $params['ajax_is_single'] ) ) {
				$is_single = true;
			}

			if ( ! empty( $params['ajax_is_tag'] ) ) {
				$is_tag = true;
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
			// Backup
			$backup_geodirectory = $geodirectory;

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

				$cpt_options[] = '<option value="' . esc_attr( $cpt ) . '" ' . selected( $cpt, $current_post_type, false ) . '>' . wp_sprintf( __( '%s Tags', 'geodirectory' ), $args['cpt_singular_name'] ) . '</option>';

				// If ajaxed then only show the first one.
				if ( $cpt_ajax && $cpt_list != '' ) {
					continue;
				}

				if ( $via_ajax && $set_location ) {
					foreach ( $set_location as $_key => $_value ) {
						$geodirectory->location->{$_key} = $_value;
					}
				}

				$term_taxonomy = $cpt . '_tags';

				$term_args = array(
					'_geodir_context' => 'geodir_tags_widget',
					'orderby'         => $orderby,
					'order'           => $order,
					'hide_empty'      => $hide_empty,
					'number'          => $max_count,
				);

				// Include terms
				if ( ! empty( $filter_terms['include'] ) ) {
					$term_args['include'] = $filter_terms['include'];
				}

				// Exclude terms
				if ( ! empty( $filter_terms['exclude'] ) ) {
					$term_args['exclude'] = $filter_terms['exclude'];
				}

				/**
				 * Filters the tag arguments.
				 *
				 * @since 2.8.103
				 */
				$term_args = apply_filters( 'geodir_tags_widget_term_args', $term_args, $term_taxonomy );

				if ( $tag_filter && $cpt == $current_post_type && $is_single && $post_ID ) {
					$term_args['object_ids'] = $post_ID;
				}

				$terms = get_terms( $term_taxonomy, $term_args );

				if ( $hide_empty ) {
					$terms = geodir_filter_empty_terms( $terms );
				}

				$close_wrap = false;

				if ( ! empty( $terms ) ) {
					$row_class = '';

					if ( $is_archive ) {
						$row_class = $is_tag ? ' geodir-cpt-tag-page' : ' geodir-cpt-tag-listing';
					}

					$cpt_row   = '';
					$open_wrap = true;

					if ( empty( $args['cpt_title'] ) && $cpt_opened ) {
						$open_wrap = false;
					}

					if ( $open_wrap ) {
						$cpt_row   .= '<div class="geodir-cpt-tag-row geodir-cpt-tag-' . $cpt . $row_class . '">';
						$cpt_opened = true;
					}

					if ( ! empty( $args['cpt_title'] ) && ! $cpt_ajax ) {
						$cpt_row .= '<' . sanitize_key( $args['title_tag'] ) . ' class="geodir-cpt-tag-title">' . esc_html( wp_sprintf( __( '%s Tags', 'geodirectory' ), $args['cpt_singular_name'] ) ) . '</' . sanitize_key( $args['title_tag'] ) . '>';
					}

					if ( $open_wrap ) {
						$desktop_class = absint( $args['row_items'] ) ? 'row-cols-md-' . absint( $args['row_items'] ) : 'row-cols-md-3';
						$col_class     = 'row-cols-1 row-cols-sm-2 ' . $desktop_class;

						// row_positioning
						if ( ! empty( $args['row_positioning'] ) && $args['row_positioning'] == 'center' ) {
							$col_class .= ' justify-content-center';
						} elseif ( ! empty( $args['row_positioning'] ) && $args['row_positioning'] == 'right' ) {
							$col_class .= ' justify-content-end';
						}

						$cpt_row .= '<div class="row ' . esc_attr( $col_class ) . '">';
					}

					$term_icon = $args['fa_icon'];
					$term_color = '';

					foreach ( $terms as $term ) {
						$cpt_row .= '<div class="geodir-cpt-tag-parent col mb-4">';
						$cpt_row .= self::get_tags_loop_content( $term, $args, $hide_count, $hide_icon, $term_icon, $term_color );
						$cpt_row .= '</div>';
						$cpt_row .= '</div>';
						$cpt_row .= '</div>';
					}

					$close_wrap = true;
					if ( $cpt_opened && empty( $args['cpt_title'] ) && $cpt_count < count( $post_types ) ) {
						$close_wrap = false;
					}

					if ( $close_wrap ) {
						$cpt_row .= '</div>';
					}

					if ( $close_wrap ) {
						$cpt_row .= '</div>';
					}

					$cpt_list .= $cpt_row;

					// Check if closed or not
					if ( $cpt_count == count( $post_types ) && ! $close_wrap ) {
						$cpt_list .= '</div></div>';
					}
				} else {
					// Check if closed or not
					if ( $cpt_count == count( $post_types ) && ! $close_wrap && $cpt_list != '' ) {
						$cpt_list .= '</div></div>';
					}
				}
			}

			if ( ! $via_ajax && $cpt_ajax && ! empty( $cpt_options ) ) {
				$post_type = is_array( $args['post_type'] ) ? implode( ',', $args['post_type'] ) : ( ! empty( $args['post_type'] ) ? $args['post_type'] : '0' );
				$output .= '<div class="geodir-cpt-tags-select"><div class="gd-wgt-params">';
				$output .= '<input type="hidden" name="post_type" value="' . esc_attr( $post_type ) . '">';
				$output .= '<input type="hidden" name="cpt_ajax" value="' . esc_attr( $cpt_ajax ) . '">';
				$output .= '<input type="hidden" name="filter_ids" value="' . esc_attr( $filter_ids ) . '">';
				$output .= '<input type="hidden" name="cpt_title" value="' . absint( $args['cpt_title'] ) . '">';
				$output .= '<input type="hidden" name="title_tag" value="' . esc_attr( $args['title_tag'] ) . '">';
				$output .= '<input type="hidden" name="hide_empty" value="' . esc_attr( $hide_empty ) . '">';
				$output .= '<input type="hidden" name="hide_count" value="' . esc_attr( $hide_count ) . '">';
				$output .= '<input type="hidden" name="hide_icon" value="' . esc_attr( $hide_icon ) . '">';
				$output .= '<input type="hidden" name="sort_by" value="' . esc_attr( $sort_by ) . '">';
				$output .= '<input type="hidden" name="max_count" value="' . esc_attr( $max_count ) . '">';
				$output .= '<input type="hidden" name="no_cpt_filter" value="' . absint( $args['no_cpt_filter'] ) . '">';
				$output .= '<input type="hidden" name="no_tag_filter" value="' . absint( $args['no_tag_filter'] ) . '">';
				$output .= '<input type="hidden" name="ajax_is_archive" value="' . (int) $is_archive . '">';
				$output .= '<input type="hidden" name="ajax_is_single" value="' . (int) $is_single . '">';
				$output .= '<input type="hidden" name="ajax_is_tag" value="' . (int) $is_tag . '">';
				$output .= '<input type="hidden" name="ajax_post_ID" value="' . absint( $post_ID ) . '">';
				$output .= '<input type="hidden" name="ajax_current_term_id" value="' . absint( $current_term_id ) . '">';

				$input_keys = array( 'card_color', 'icon_color', 'icon_size', 'design_type', 'row_items', 'row_positioning', 'card_padding_inside', 'bg', 'mt', 'mr', 'mb', 'ml', 'pt', 'pr', 'pb', 'pl', 'border', 'rounded', 'rounded_size', 'shadow', 'card_shadow', 'fa_icon', 'tag_text_color', 'tag_font_size', 'tag_font_weight', 'tag_font_case', 'badge_text_color', 'badge_font_size', 'badge_font_weight', 'badge_font_case', 'tag_text_color_custom', 'tag_font_size_custom', 'badge_position', 'badge_color', 'badge_text_append', 'badge_text_color_custom', 'badge_font_size_custom', 'css_class' );
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

				$select_class = ( $aui_bs5 ? 'form-select' : 'custom-select' ) . ' form-control mb-3';
				$output .= '</div><select class="geodir-tag-list-tax '. $select_class . '" aria-label="' . esc_attr__( 'CPT Tags', 'geodirectory' ) . '">' . implode( '', $cpt_options ) . '</select>';
				$output .= '</div><div class="geodir-cpt-tag-rows">';
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

	public static function get_tags_loop_content( $term, $args = array(), $hide_count = false, $hide_icon = false, $term_fa_icon = '', $term_color = '' ) {
		if ( class_exists( 'GeoDir_Location_SEO', false ) ) {
			/**
			 * Filter whether to use location link for tags widget term URL.
			 *
			 * @param bool $use_location_link Whether to use location link. Default false.
			 * @param WP_Term|mixed $term The term object.
			 *
			 * @since 2.8.115
			 *
			 */
			$use_location_link = apply_filters( 'geodir_tags_widget_use_location_link', false, $term );

			$term_link = GeoDir_Location_SEO::get_term_link( $term, $use_location_link );
		} else {
			$term_link = get_term_link( $term, $term->taxonomy );
		}

		$term_link = apply_filters( 'geodir_tags_widget_term_link', $term_link, $term->term_id, $term );

		/**
		 * Filter tag FA icon.
		 *
		 * @since 2.8.103
		 */
		$term_fa_icon = apply_filters( 'geodir_tags_term_fa_icon', $term_fa_icon, $term );

		/**
		 * Filter tag color.
		 *
		 * @since 2.8.103
		 */
		$term_color = apply_filters( 'geodir_tags_term_color', $term_color, $term );

		$term_icon = '<i class="' . esc_attr( $term_fa_icon ) . '" aria-hidden="true"></i>';
		$term_count = (int) $term->count;

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

		$design_type = ! empty( $args['design_type'] ) ? $args['design_type'] : '';

		if ( empty( $design_type ) || $design_type == 'icon-left' ) {
			$style = 'icon-left';
		} else if ( $design_type == 'icon-top' ) {
			$style = 'icon-top';
		} else {
			$style = $design_type;
		}

		$card_shadow = ! empty( $args['card_shadow'] ) ? $args['card_shadow'] : 'small';

		// Card class
		if ( $card_shadow == 'none' ) {
			$card_class = 'shadow-none';
		} elseif ( $card_shadow == 'medium' ) {
			$card_class = 'shadow';
		} elseif ( $card_shadow == 'large' ) {
			$card_class = 'shadow-lg';
		} else {
			$card_class = 'shadow-sm';
		}

		$template = geodir_design_style() . "/tags/" . esc_attr( $style ) . ".php";

		$cpt_row = geodir_get_template_html(
			$template,
			array(
				'term_id'    => $term->term_id,
				'term_name'  => $term->name,
				'term_link'  => $term_link,
				'hide_count' => $hide_count,
				'term_count' => $term_count,
				'term_count_text' => $term_count_text,
				'hide_icon'  => $hide_icon,
				'term_icon'  => $term_icon,
				'term_color' => $term_color,
				'card_class' => $card_class,
				'args'       => $args
			)
		);

		return $cpt_row;
	}

	/**
	 * Adds the JavaScript in the footer.
	 *
	 * @since 2.8.103
	 */
	public function add_footer_script() {
		global $geodir_tags_script;

		if ( ! empty( $geodir_tags_script ) ) {
			return;
		}

		$geodir_tags_script = true;
?><script type="text/javascript">document.addEventListener("DOMContentLoaded",function(event){jQuery(".geodir-tag-list-tax").on("change",function(e){e.preventDefault();var $widgetBox=jQuery(this).closest(".geodir-tags-container");var $container=$widgetBox.find(".geodir-cpt-tag-rows:first");$container.addClass("gd-loading p-3 text-center").html('<i class="fas fa-circle-notch fa-spin fa-2x" aria-hidden="true"></i>');var data={action:"geodir_cpt_tags",security:geodir_params.basic_nonce,ajax_cpt:jQuery(this).val()};$widgetBox.find(".gd-wgt-params:first").find("input").each(function(){if(jQuery(this).attr("name")){data[jQuery(this).attr("name")]=jQuery(this).val()}});jQuery.post(geodir_params.ajax_url,data,function(response){$container.removeClass("gd-loading p-3 text-center").html(response)})})});</script>
<?php
	}

	/**
	 * Get tags ajax content.
	 *
	 * @since 2.8.103
	 *
	 * @param array $params Tags parameters.
	 */
	public static function get_tags_ajax_content( $params ) {
		$params['via_ajax'] = true;
		$output = self::get_tags_content( $params );

		if ( empty( $output ) ) {
			$output = '<div class="geodir-cpt-tags-empty alert alert-info">' . __( 'No tags found', 'geodirectory' ) . '</div>';
		} else {
			if ( function_exists( 'aui_bs_convert_sd_output' ) ) {
				$output = aui_bs_convert_sd_output( $output );
			}
		}

		echo $output;
	}
}
