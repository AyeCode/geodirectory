<?php
/**
 * GeoDirectory GeoDirectory Popular Post View Widget
 *
 * @since 1.0.0
 *
 * @package GeoDirectory
 */

/**
 * GeoDirectory listings widget class.
 *
 * @since 1.0.0
 */
class GeoDir_Widget_Listings extends WP_Super_Duper {

	public $post_title_tag;

	/**
	 * Register the popular posts widget.
	 *
	 * @since 1.0.0
	 * @since 1.5.1 Changed from PHP4 style constructors to PHP5 __construct.
	 */
	public function __construct() {

		$options = array(
			'textdomain'       => GEODIRECTORY_TEXTDOMAIN,
			'block-icon'       => 'fas fa-th-list',
			'block-category'   => 'geodirectory',
			'block-supports'   => array(
				'customClassName' => false,
			),
			'block-keywords'   => "['listings','posts','geo']",
			'class_name'       => __CLASS__,
			'base_id'          => 'gd_listings', // this us used as the widget id and the shortcode id.
			'name'             => __( 'GD > Listings', 'geodirectory' ), // the name of the widget.
			'widget_ops'       => array(
				'classname'                   => 'geodir-listings ' . geodir_bsui_class(),
				// widget class
				'description'                 => esc_html__( 'Shows the GD listings filtered by your choices.', 'geodirectory' ),
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
						__( 'SEO', 'geodirectory' ),
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
						__( 'Design', 'geodirectory' ),
						__( 'Card Design', 'geodirectory' ),
						__( 'Carousel', 'geodirectory' ),
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

	public static function filter_pagination_args( $pagination_args ) {
		$pagination_args['base']   = '%_%';
		$pagination_args['format'] = '#%#%#';

		return $pagination_args;
	}

	/**
	 * Set the arguments later.
	 *
	 * @return array
	 */
	public function set_arguments() {

		$design_style = geodir_design_style();

		$post_types = geodir_get_posttypes( 'options-plural' );

		$arguments['title']                 = array(
			'title'    => __( 'Title:', 'geodirectory' ),
			'desc'     => __( 'The widget title.', 'geodirectory' ),
			'type'     => 'text',
			'default'  => '',
			'desc_tip' => true,
			'advanced' => false,
			'group'    => __( 'Title', 'geodirectory' ),
		);

		if ( $design_style ) {
			// title styles
			$arguments = $arguments + geodir_get_sd_title_inputs();
		}

		$arguments['hide_if_empty']         = array(
			'title'    => __( 'Hide widget if no posts?', 'geodirectory' ),
			'type'     => 'checkbox',
			'desc_tip' => true,
			'value'    => '1',
			'default'  => '0',
			'advanced' => false,
			'group'    => __( 'Filters', 'geodirectory' ),
		);
		$arguments['post_type']             = array(
			'title'         => __( 'Default Post Type:', 'geodirectory' ),
			'desc'          => __( 'The custom post types to show by default. Only used when there are multiple CPTs.', 'geodirectory' ),
			'type'          => 'select',
			'options'       => $post_types,
			'default'       => 'gd_place',
			'desc_tip'      => true,
			'advanced'      => false,
			'onchange_rest' => array(
				'path'   => '/geodir/v2/"+$value+"/categories/?per_page=100',
				'values' => $this->get_rest_slugs_array(),
			),
			'group'         => __( 'Filters', 'geodirectory' ),
		);
		$arguments['category']              = array(
			'title'            => __( 'Categories:', 'geodirectory' ),
			'desc'             => __( 'The categories to show.', 'geodirectory' ),
			'type'             => 'select',
			'multiple'         => true,
			'options'          => $this->get_categories(),
			'default'          => '',
			'desc_tip'         => true,
			'advanced'         => false,
			'post_type_linked' => count( $post_types ) > 1 ? true : false,
			'group'            => __( 'Filters', 'geodirectory' ),
		);
		$arguments['related_to']            = array(
			'title'    => __( 'Filter listings related to:', 'geodirectory' ),
			'desc'     => __( 'Select to filter the listings related to current listing categories/tags on detail page.', 'geodirectory' ),
			'type'     => 'select',
			'options'  => array(
				''                 => __( 'No filter', 'geodirectory' ),
				'default_category' => __( 'Default Category only', 'geodirectory' ),
				'category'         => __( 'Categories', 'geodirectory' ),
				'tags'             => __( 'Tags', 'geodirectory' ),
			),
			'default'  => '',
			'desc_tip' => true,
			'advanced' => true,
			'group'    => __( 'Filters', 'geodirectory' ),
		);
		$arguments['tags']                  = array(
			'title'       => __( 'Filter by tags:', 'geodirectory' ),
			'desc'        => __( 'Insert separate tags with commas to filter listings by tags.', 'geodirectory' ),
			'type'        => 'text',
			'default'     => '',
			'placeholder' => __( 'garden,dinner,pizza', 'geodirectory' ),
			'desc_tip'    => true,
			'advanced'    => true,
			'group'       => __( 'Filters', 'geodirectory' ),
		);
		$arguments['post_author']           = array(
			'title'    => __( 'Filter by author:', 'geodirectory' ),
			'desc'     => __( 'Filter by current_user, current_author or ID (default = unfiltered). current_user: Filters the listings by author id of the logged in user. current_author: Filters the listings by author id of current viewing post/listing. 11: Filters the listings by author id = 11. Leave blank to show posts from all authors.', 'geodirectory' ),
			'type'     => 'text',
			'default'  => '',
			'desc_tip' => true,
			'advanced' => true,
			'group'    => __( 'Filters', 'geodirectory' ),
		);
		$arguments['post_limit']            = array(
			'title'    => __( 'Posts to show:', 'geodirectory' ),
			'desc'     => __( 'The number of posts to show by default.', 'geodirectory' ),
			'type'     => 'number',
			'default'  => '5',
			'desc_tip' => true,
			'advanced' => false,
			'group'    => __( 'Filters', 'geodirectory' ),
		);
		$arguments['post_ids']              = array(
			'title'       => __( 'Posts IDs:', 'geodirectory' ),
			'desc'        => __( 'Enter a comma separated list of post ids (1,2,3) to limit the listing to these posts only, or a negative list (-1,-2,-3) to exclude those post IDs (negative and positive IDs can not be mixed) ', 'geodirectory' ),
			'type'        => 'text',
			'default'     => '',
			'placeholder' => '1,2,3',
			'desc_tip'    => true,
			'advanced'    => true,
			'group'       => __( 'Filters', 'geodirectory' ),
		);
		$arguments['add_location_filter']   = array(
			'title'    => __( 'Enable location filter?', 'geodirectory' ),
			'type'     => 'checkbox',
			'desc_tip' => true,
			'value'    => '1',
			'default'  => '1',
			'advanced' => true,
			'group'    => __( 'Filters', 'geodirectory' ),
		);
		$arguments['nearby_gps']            = array(
			'title'    => __( 'Filter Nearby User/Visitor GPS', 'geodirectory' ),
			'type'     => 'checkbox',
			'desc_tip' => true,
			'value'    => '1',
			'default'  => '0',
			'advanced' => true,
			'group'    => __( 'Filters', 'geodirectory' ),
		);
		$arguments['show_featured_only']    = array(
			'title'    => __( 'Show featured only?', 'geodirectory' ),
			'type'     => 'checkbox',
			'desc_tip' => true,
			'value'    => '1',
			'default'  => '0',
			'advanced' => true,
			'group'    => __( 'Filters', 'geodirectory' ),
		);
		$arguments['show_special_only']     = array(
			'title'    => __( 'Only show listings with special offers?', 'geodirectory' ),
			'type'     => 'checkbox',
			'desc_tip' => true,
			'value'    => '1',
			'default'  => '0',
			'advanced' => true,
			'group'    => __( 'Filters', 'geodirectory' ),
		);
		$arguments['with_pics_only']        = array(
			'title'    => __( 'Only show listings with images(post_images)?', 'geodirectory' ),
			'type'     => 'checkbox',
			'desc_tip' => true,
			'value'    => '1',
			'default'  => '0',
			'advanced' => true,
			'group'    => __( 'Filters', 'geodirectory' ),
		);
		$arguments['with_videos_only']      = array(
			'title'    => __( 'Only show listings with videos?', 'geodirectory' ),
			'type'     => 'checkbox',
			'desc_tip' => true,
			'value'    => '1',
			'default'  => '0',
			'advanced' => true,
			'group'    => __( 'Filters', 'geodirectory' ),
		);
		$arguments['show_favorites_only']   = array(
			'title'    => __( 'Show favorited by user?', 'geodirectory' ),
			'type'     => 'checkbox',
			'desc_tip' => true,
			'value'    => '1',
			'default'  => '0',
			'advanced' => true,
			'group'    => __( 'Filters', 'geodirectory' ),
		);
		$arguments['favorites_by_user']     = array(
			'title'           => __( 'Favorited by user:', 'geodirectory' ),
			'desc'            => __( 'Display listings favorited by current_user, current_author or ID (default = unfiltered). current_user: Display listings favorited by author id of the logged in user. current_author: Display listings favorited by author id of current viewing post/listing. 11: Display listings favorited author id = 11. Leave blank to show listings favorited by logged user.', 'geodirectory' ),
			'type'            => 'text',
			'default'         => '',
			'desc_tip'        => true,
			'advanced'        => true,
			'element_require' => '[%show_favorites_only%]=="1"',
			'group'           => __( 'Filters', 'geodirectory' ),
		);
		$arguments['use_viewing_post_type'] = array(
			'title'    => __( 'Use current viewing post type?', 'geodirectory' ),
			'type'     => 'checkbox',
			'desc_tip' => true,
			'value'    => '1',
			'default'  => '0',
			'advanced' => true,
			'group'    => __( 'Filters', 'geodirectory' ),
		);
		$arguments['use_viewing_term']      = array(
			'title'    => __( 'Filter by current viewing category/tag?', 'geodirectory' ),
			'type'     => 'checkbox',
			'desc_tip' => true,
			'value'    => '1',
			'default'  => '0',
			'advanced' => true,
			'group'    => __( 'Filters', 'geodirectory' ),
		);
		$arguments['sort_by']               = array(
			'title'            => __( 'Sort by:', 'geodirectory' ),
			'desc'             => __( 'How the listings should be sorted.', 'geodirectory' ),
			'type'             => 'select',
			'options'          => $this->get_sort_options(),
			'default'          => '',
			'desc_tip'         => true,
			'advanced'         => false,
			'post_type_linked' => count( $post_types ) > 1 ? true : false,
			'group'            => __( 'Sorting', 'geodirectory' ),
		);
		$arguments['title_tag']             = array(
			'title'    => __( 'Title tag:', 'geodirectory' ),
			'desc'     => __( 'The title tag used for the listings.', 'geodirectory' ),
			'type'     => 'select',
			'options'  => array(
				'h3' => __( 'h3 (default)', 'geodirectory' ),
				'h2' => __( 'h2 (if main content of page)', 'geodirectory' ),
			),
			'default'  => 'h3',
			'desc_tip' => true,
			'advanced' => false,
			'group'    => __( 'SEO', 'geodirectory' ),
		);
		$arguments['layout']                = array(
			'title'    => __( 'Layout:', 'geodirectory' ),
			'desc'     => __( 'How the listings should laid out by default.', 'geodirectory' ),
			'type'     => 'select',
			'options'  => geodir_get_layout_options(),
			'default'  => '2',
			'desc_tip' => true,
			'advanced' => false,
			'group'    => __( 'Design', 'geodirectory' ),
		);
		$arguments['view_all_link']         = array(
			'title'    => __( 'Show view all link?', 'geodirectory' ),
			'type'     => 'checkbox',
			'desc_tip' => true,
			'value'    => '1',
			'default'  => '1',
			'advanced' => false,
			'group'    => __( 'Design', 'geodirectory' ),
		);
		$arguments['with_pagination']       = array(
			'title'    => __( 'Show pagination?', 'geodirectory' ),
			'type'     => 'checkbox',
			'desc_tip' => true,
			'value'    => '1',
			'default'  => '0',
			'advanced' => true,
			'group'    => __( 'Design', 'geodirectory' ),
		);
		$arguments['top_pagination']        = array(
			'title'           => __( 'Show pagination on top of the listings?', 'geodirectory' ),
			'type'            => 'checkbox',
			'desc_tip'        => true,
			'value'           => '1',
			'default'         => '0',
			'advanced'        => true,
			'element_require' => '[%with_pagination%]=="1"',
			'group'           => __( 'Design', 'geodirectory' ),
		);
		$arguments['bottom_pagination']     = array(
			'title'           => __( 'Show pagination at bottom of the listings?', 'geodirectory' ),
			'type'            => 'checkbox',
			'desc_tip'        => true,
			'value'           => '1',
			'default'         => '1',
			'advanced'        => true,
			'element_require' => '[%with_pagination%]=="1"',
			'group'           => __( 'Design', 'geodirectory' ),
		);
		$arguments['pagination_info']       = array(
			'title'           => __( 'Show advance pagination info?', 'geodirectory' ),
			'desc'            => '',
			'type'            => 'select',
			'options'         => array(
				''       => __( 'Never display', 'geodirectory' ),
				'before' => __( 'Before pagination', 'geodirectory' ),
				'after'  => __( 'After pagination', 'geodirectory' ),
			),
			'default'         => '',
			'desc_tip'        => false,
			'advanced'        => true,
			'element_require' => '[%with_pagination%]=="1"',
			'group'           => __( 'Design', 'geodirectory' ),
		);

		$arguments['template_type'] = array(
			'title'    => __( 'Archive Item Template Type:', 'geodirectory' ),
			'desc'     => 'Select archive item template type to assign template to archive loop.',
			'type'     => 'select',
			'options'  => geodir_template_type_options(),
			'default'  => '',
			'desc_tip' => true,
			'advanced' => false,
			'group'    => __( 'Card Design', 'geodirectory' ),
		);

		$arguments['tmpl_page'] = array(
			'title'           => __( 'Archive Item Template Page:', 'geodirectory' ),
			'desc'            => 'Select archive item template page.',
			'type'            => 'select',
			'options'         => geodir_template_page_options(),
			'default'         => '',
			'desc_tip'        => true,
			'advanced'        => false,
			'element_require' => '[%template_type%]=="page"',
			'group'           => __( 'Card Design', 'geodirectory' ),
		);

		if ( geodir_is_block_theme() ) {
			$arguments['tmpl_part'] = array(
				'title'           => __( 'Archive Item Template Part:', 'geodirectory' ),
				'desc'            => 'Select archive item template part.',
				'type'            => 'select',
				'options'         => geodir_template_part_options(),
				'default'         => '',
				'desc_tip'        => true,
				'advanced'        => false,
				'element_require' => '[%template_type%]=="template_part"',
				'group'           => __( 'Card Design', 'geodirectory' ),
			);
		}

		/*
		* Elementor Pro features below here
		*/
		if ( defined( 'ELEMENTOR_PRO_VERSION' ) ) {
			$arguments['skin_id'] = array(
				'title'           => __( 'Archive Item Elementor Skin:', 'geodirectory' ),
				'desc'            => '',
				'type'            => 'select',
				'options'         => GeoDir_Elementor::get_elementor_pro_skins(),
				'default'         => '',
				'desc_tip'        => false,
				'advanced'        => false,
				'element_require' => '([%template_type%]=="" || [%template_type%]=="elementor_skin")',
				'group'           => __( 'Card Design', 'geodirectory' ),
			);

			$arguments['skin_column_gap'] = array(
				'title'           => __( 'Skin column gap', 'geodirectory' ),
				'desc'            => __( 'The px value for the column gap.', 'geodirectory' ),
				'type'            => 'number',
				'default'         => '30',
				'desc_tip'        => true,
				'advanced'        => false,
				'element_require' => '([%template_type%]=="" || [%template_type%]=="elementor_skin")',
				'group'           => __( 'Card Design', 'geodirectory' ),
			);

			$arguments['skin_row_gap'] = array(
				'title'           => __( 'Skin row gap', 'geodirectory' ),
				'desc'            => __( 'The px value for the row gap.', 'geodirectory' ),
				'type'            => 'number',
				'default'         => '35',
				'desc_tip'        => true,
				'advanced'        => false,
				'element_require' => '([%template_type%]=="" || [%template_type%]=="elementor_skin")',
				'group'           => __( 'Card Design', 'geodirectory' ),
			);
		}

		if ( $design_style ) {
			$arguments['row_gap'] = array(
				'title'    => __( 'Card row gap', 'geodirectory' ),
				'desc'     => __( 'This adjusts the spacing between the cards horizontally.', 'geodirectory' ),
				'type'     => 'select',
				'options'  => array(
					''  => __( 'Default', 'geodirectory' ),
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
				),
				'default'  => '',
				'desc_tip' => false,
				'advanced' => false,
				'group'    => __( 'Card Design', 'geodirectory' ),
			);

			$arguments['column_gap'] = array(
				'title'    => __( 'Card column gap', 'geodirectory' ),
				'desc'     => __( 'This adjusts the spacing between the cards vertically.', 'geodirectory' ),
				'type'     => 'select',
				'options'  => array(
					''  => __( 'Default', 'geodirectory' ),
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
				),
				'default'  => '',
				'desc_tip' => false,
				'advanced' => false,
				'group'    => __( 'Card Design', 'geodirectory' ),
			);

			$arguments['card_border'] = array(
				'title'    => __( 'Card border', 'geodirectory' ),
				'desc'     => __( 'Set the border style for the card.', 'geodirectory' ),
				'type'     => 'select',
				'options'  => array(
					''     => __( 'Default', 'geodirectory' ),
					'none' => __( 'None', 'geodirectory' ),
				) + geodir_aui_colors(),
				'default'  => '',
				'desc_tip' => false,
				'advanced' => false,
				'group'    => __( 'Card Design', 'geodirectory' ),
			//              'element_require' => '[%template_type%]==""',
			);

			$arguments['card_shadow'] = array(
				'title'    => __( 'Card shadow', 'geodirectory' ),
				'desc'     => __( 'Set the card shadow style.', 'geodirectory' ),
				'type'     => 'select',
				'options'  => array(
					''       => __( 'None', 'geodirectory' ),
					'small'  => __( 'Small', 'geodirectory' ),
					'medium' => __( 'Medium', 'geodirectory' ),
					'large'  => __( 'Large', 'geodirectory' ),
				),
				'default'  => '',
				'desc_tip' => false,
				'advanced' => false,
				'group'    => __( 'Card Design', 'geodirectory' ),
			//              'element_require' => '[%template_type%]==""',
			);

			// Carousel
			$arguments['with_carousel'] = array(
				'type'     => 'select',
				'title'    => __( 'With Carousel:', 'geodirectory' ),
				'desc'     => __( 'Enable carousel to show listings slideshow.', 'geodirectory' ),
				'options'  => array(
					''  => __( 'No', 'geodirectory' ),
					'1' => __( 'Yes', 'geodirectory' ),
				),
				'default'  => '',
				'desc_tip' => false,
				'advanced' => false,
				'group'    => __( 'Carousel', 'geodirectory' ),
			);

			$arguments['with_indicators'] = array(
				'type'            => 'select',
				'title'           => __( 'With Indicators:', 'geodirectory' ),
				'desc'            => __( 'Show the previous/next navigation indicators to the carousel.', 'geodirectory' ),
				'options'         => array(
					''  => __( 'No', 'geodirectory' ),
					'1' => __( 'Yes', 'geodirectory' ),
				),
				'default'         => '',
				'desc_tip'        => false,
				'advanced'        => false,
				'element_require' => '[%with_carousel%]=="1"',
				'group'           => __( 'Carousel', 'geodirectory' ),
			);

			$arguments['indicators_mb'] = sd_get_margin_input(
				'mb',
				array(
					//'device_type' => 'Mobile',
					'icon'            => '',
					'title'           => __( 'Indicator Margin Bottom', 'geodirectory' ),
					'group'           => __( 'Carousel', 'geodirectory' ),
					'element_require' => '[%with_indicators%]=="1"',
				)
			);

			$arguments['with_controls'] = array(
				'type'            => 'select',
				'title'           => __( 'With Controls:', 'geodirectory' ),
				'desc'            => __( 'Show the paging control of each slide to the carousel.', 'geodirectory' ),
				'options'         => array(
					''  => __( 'No', 'geodirectory' ),
					'1' => __( 'Yes', 'geodirectory' ),
				),
				'default'         => '',
				'desc_tip'        => false,
				'advanced'        => false,
				'element_require' => '[%with_carousel%]=="1"',
				'group'           => __( 'Carousel', 'geodirectory' ),
			);

			$arguments['slide_interval'] = array(
				'type'            => 'number',
				'title'           => __( 'Interval:', 'geodirectory' ),
				'desc'            => __( 'The amount of time in seconds to delay between automatically cycling an listing item. If 0, carousel will not automatically cycle.', 'geodirectory' ),
				'default'         => '5',
				'desc_tip'        => false,
				'advanced'        => false,
				'element_require' => '[%with_carousel%]=="1"',
				'group'           => __( 'Carousel', 'geodirectory' ),
			);

			$arguments['slide_ride'] = array(
				'type'            => 'select',
				'title'           => __( 'Autoplay:', 'geodirectory' ),
				'desc'            => __( 'Autoplays the carousel after the user manually cycles the first slide. If "carousel", autoplays the carousel on page load automatically.', 'geodirectory' ),
				'options'         => array(
					''      => __( 'Default (Auto)', 'geodirectory' ),
					'click' => __( 'On Click', 'geodirectory' ),
					'auto'  => __( 'Auto', 'geodirectory' ),
				),
				'default'         => '',
				'desc_tip'        => false,
				'advanced'        => false,
				'element_require' => '[%with_carousel%]=="1"',
				'group'           => __( 'Carousel', 'geodirectory' ),
			);

		$arguments['center_slide'] = array(
			'type' => 'select',
			'title' => __( 'Center Slides:', 'geodirectory' ),
			'desc' => __( 'Show center slide and left/right slide with half preview.', 'geodirectory' ),
			'options' => array(
				'' => __( 'No', 'geodirectory' ),
				'1' => __( 'Yes', 'geodirectory' )
			),
			'default' => '',
			'desc_tip' => false,
			'advanced' => false,
			'element_require' => '[%with_carousel%]=="1"',
			'group' => __( 'Carousel', 'geodirectory' )
		);

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
			$arguments['border']       = geodir_get_sd_border_input( 'border' );
			$arguments['rounded']      = geodir_get_sd_border_input( 'rounded' );
			$arguments['rounded_size'] = geodir_get_sd_border_input( 'rounded_size' );

			// shadow
			$arguments['shadow'] = geodir_get_sd_shadow_input( 'shadow' );
		}

		// CSS Class
		$arguments['css_class'] = sd_get_class_input();

		return $arguments;
	}

	/**
	 * Get the rest API slug values.
	 *
	 * @return array
	 */
	public function get_rest_slugs_array() {
		$post_types = geodir_get_posttypes( 'array' );

		$options = array();
		if ( ! empty( $post_types ) ) {
			foreach ( $post_types as $key => $info ) {
				$options[ $key ] = $info['rewrite']['slug'];
			}
		}

		return $options;
	}

	/**
	 * Get categories.
	 *
	 * @param string $post_type Optional. Post type. Default gd_place0
	 *
	 * @return array $options.s
	 * @since 2.0.0
	 *
	 */
	public function get_categories( $post_type = 'gd_place' ) {
		return geodir_category_options( $post_type );
	}

	/**
	 * Get sort options.
	 *
	 * @param string $post_type Optional. Post type. Default gd_place.
	 *
	 * @return array $options.
	 * @since 2.0.0
	 *
	 */
	public function get_sort_options( $post_type = 'gd_place' ) {
		return geodir_sort_by_options( $post_type );
	}

	/**
	 * Post title tag filter.
	 *
	 * @param string $tag Optional. Title tag.
	 * @param array $instance Widget settings.
	 * @param array $rags Optional. Widget arguments.
	 * @param object $widget Widget object.
	 *
	 * @return string $title
	 * @since 2.0.0
	 *
	 */
	public function filter_post_title_tag( $tag, $instance = array(), $rags = array(), $widget = array() ) {
		if ( ! empty( $this->post_title_tag ) ) {
			$tag = $this->post_title_tag;
		}

		return $tag;
	}

	public function ajax_listings( $data = array() ) {
		global $wp, $geodirectory, $post, $gd_post, $geodir_ajax_gd_listings;

		$backup_wp               = $wp;
		$geodir_ajax_gd_listings = true;

		$data = apply_filters( 'geodir_widget_listings_ajax_listings', $data );

		if ( ! empty( $data['set_post'] ) ) {
			$post    = get_post( absint( $data['set_post'] ) );
			$gd_post = geodir_get_post_info( absint( $data['set_post'] ) );
		}

		if ( ! empty( $data['set_query_vars'] ) && is_array( $data['set_query_vars'] ) ) {
			$set_query_vars = array();
			foreach ( $data['set_query_vars'] as $_key => $_value ) {
				if ( ! empty( $_key ) && ( is_scalar( $_value ) || ( ! is_object( $_value ) && ! is_array( $_value ) ) ) ) {
					$set_query_vars[ sanitize_text_field( $_key ) ] = sanitize_text_field( stripslashes( $_value ) );
				}
			}
			$wp->query_vars = $set_query_vars;

			add_filter( 'geodir_location_set_current_check_404', array( $this, 'set_current_check_404' ), 999, 1 );

			$geodirectory->location->set_current();
		}

		ob_start();

		do_action( 'geodir_widget_ajax_listings_before', $data );

		if ( isset( $data['widget_args'] ) ) {
			$widget_args = (array) $data['widget_args'];
			unset( $data['widget_args'] );
		} else {
			$widget_args = array();
		}

		echo $this->output( $data, $widget_args );

		do_action( 'geodir_widget_ajax_listings_after', $data );

		$output = ob_get_clean();

		$wp                      = $backup_wp;
		$geodir_ajax_gd_listings = false;

		wp_send_json_success( array( 'content' => trim( $output ) ) );
	}

	/**
	 * The Super block output function.
	 *
	 * @param array $instance Settings for the current widget instance.
	 * @param array $widget_args
	 * @param string $content
	 *
	 * @return mixed|string|void
	 */
	public function output( $instance = array(), $widget_args = array(), $content = '' ) {
		$instance = wp_parse_args(
			(array) $instance,
			array(
				'title'                 => '',
				'post_type'             => '',
				'category'              => array(),
				'related_to'            => '',
				'tags'                  => '',
				'post_author'           => '',
				'category_title'        => '',
				'sort_by'               => 'az',
				'title_tag'             => 'h3',
				'list_order'            => '',
				'post_limit'            => '5',
				'post_ids'              => '',
				'layout'                => '2',
				'listing_width'         => '',
				'add_location_filter'   => '1',
				'nearby_gps' > '',
				'character_count'       => '20',
				'show_featured_only'    => '',
				'show_special_only'     => '',
				'with_pics_only'        => '',
				'with_videos_only'      => '',
				'show_favorites_only'   => '',
				'favorites_by_user'     => '',
				'use_viewing_post_type' => '',
				'use_viewing_term'      => '',
				'hide_if_empty'         => '',
				'view_all_link'         => '1',
				'with_pagination'       => '0',
				'top_pagination'        => '0',
				'bottom_pagination'     => '1',
				'pagination_info'       => '',
				// Template Settings
				'template_type'         => '',
				'tmpl_page'             => '',
				'tmpl_part'             => '',
				// elementor settings
				'skin_id'               => '',
				'skin_column_gap'       => '',
				'skin_row_gap'          => '',
				// AUI settings
				'column_gap' => '',
				'row_gap' => '',
				'card_border' => '',
				'card_shadow' => '',
				'bg' => '',
				'mt' => '',
				'mb' => '3',
				'mr' => '',
				'ml' => '',
				'pt' => '',
				'pb' => '',
				'pr' => '',
				'pl' => '',
				'border' => '',
				'rounded' => '',
				'rounded_size' => '',
				'shadow' => '',
				'with_carousel' => '',
				'with_controls' => '',
				'with_indicators' => '',
				'slide_interval' => '5',
				'slide_ride' => '',
				'center_slide' => ''
			)
		);


		// sanitize title_tag
		$instance['title_tag'] = in_array( $instance['title_tag'], array( 'h2', 'h3' ), true ) ? esc_attr( $instance['title_tag'] ) : 'h3';

		ob_start();

		$this->output_html( $widget_args, $instance );

		return ob_get_clean();
	}

	/**
	 * Generates popular postview HTML.
	 *
	 * @param array|string $args Display arguments including before_title, after_title, before_widget, and
	 *                                         after_widget.
	 * @param array|string $instance The settings for the particular instance of the widget.
	 *
	 * @since   1.0.0
	 * @since   1.5.1 View all link fixed for location filter disabled.
	 * @since   1.6.24 View all link should go to search page with near me selected.
	 * @package GeoDirectory
	 * @global object $post The current post object.
	 * @global string $gd_layout_class The girdview style of the listings for widget.
	 * @global bool $geodir_is_widget_listing Is this a widget listing?. Default: false.
	 * @global bool $geodir_carousel_open Check whether widget has carousel or not.
	 *
	 */
	public function output_html( $args = '', $instance = '' ) {
		global $wp, $aui_bs5, $geodirectory, $gd_post, $post, $gd_advanced_pagination, $posts_per_page, $paged, $geodir_ajax_gd_listings, $geodir_carousel_open, $geodir_item_tmpl;

		$is_single = ( geodir_is_page( 'single' ) || ! empty( $instance['set_post'] ) ) && ! empty( $gd_post ) ? true : false;

		// Prints the widget
		extract( $args, EXTR_SKIP );

		/** This filter is documented in includes/widget/class-geodir-widget-advance-search.php.php */
		$title = empty( $instance['title'] ) ? geodir_ucwords( $instance['category_title'] ) : apply_filters( 'widget_title', __( $instance['title'], 'geodirectory' ) );
		/**
		 * Filter the widget post type.
		 *
		 * @param string $instance ['post_type'] Post type of listing.
		 *
		 * @since 1.0.0
		 *
		 */
		$post_type = empty( $instance['post_type'] ) ? 'gd_place' : apply_filters( 'widget_post_type', $instance['post_type'] );
		/**
		 * Filter the widget's term.
		 *
		 * @param string $instance ['category'] Filter by term. Can be any valid term.
		 *
		 * @since 1.0.0
		 *
		 */
		$category = empty( $instance['category'] ) ? '0' : apply_filters( 'widget_category', $instance['category'] );
		/**
		 * Filter the widget related_to param.
		 *
		 * @param string $instance ['related_to'] Filter by related to categories/tags.
		 *
		 * @since 2.0.0
		 *
		 */
		$related_to = empty( $instance['related_to'] ) ? '' : apply_filters( 'widget_related_to', ( $is_single ? $instance['related_to'] : '' ), $instance, $this->id_base );
		/**
		 * Filter the widget tags param.
		 *
		 * @param string $instance ['tags'] Filter by tags.
		 *
		 * @since 2.0.0
		 *
		 */
		$tags = empty( $instance['tags'] ) ? '' : apply_filters( 'widget_tags', $instance['tags'], $instance, $this->id_base );
		/**
		 * Filter the widget post_author param.
		 *
		 * @param string $instance ['post_author'] Filter by author.
		 *
		 * @since 2.0.0
		 *
		 */
		$post_author = empty( $instance['post_author'] ) ? '' : apply_filters( 'widget_post_author', $instance['post_author'], $instance, $this->id_base );
		/**
		 * Filter the widget listings limit.
		 *
		 * @param string $instance ['post_number'] Number of listings to display.
		 *
		 * @since 1.0.0
		 *
		 */
		$post_number = empty( $instance['post_limit'] ) ? '5' : apply_filters( 'widget_post_number', $instance['post_limit'] );
		/**
		 * Filter the widget listings post ids.
		 *
		 * @param string $instance ['post_ids'] Post ids to include or exclude.
		 *
		 * @since 1.0.0
		 *
		 */
		$post_ids = empty( $instance['post_ids'] ) ? '' : apply_filters( 'widget_post_ids', $instance['post_ids'] );
		/**
		 * Filter widget's "layout" type.
		 *
		 * @param string $instance ['layout'] Widget layout type.
		 *
		 * @since 1.0.0
		 *
		 */
		$layout = ! isset( $instance['layout'] ) ? geodir_grid_view_class( 0 ) : apply_filters( 'widget_layout', $instance['layout'] );
		/**
		 * Filter widget's "add_location_filter" value.
		 *
		 * @param string|bool $instance ['add_location_filter'] Do you want to add location filter? Can be 1 or 0.
		 *
		 * @since 1.0.0
		 *
		 */
		$add_location_filter = empty( $instance['add_location_filter'] ) ? '0' : apply_filters( 'widget_add_location_filter', $instance['add_location_filter'] );
		/**
		 * Filter widget's listing width.
		 *
		 * @param string $instance ['listing_width'] Listing width.
		 *
		 * @since 1.0.0
		 *
		 */
		$listing_width = empty( $instance['listing_width'] ) ? '' : apply_filters( 'widget_listing_width', $instance['listing_width'] );
		/**
		 * Filter widget's "list_sort" type.
		 *
		 * @param string $instance ['list_sort'] Listing sort by type.
		 *
		 * @since 1.0.0
		 *
		 */
		$list_sort = empty( $instance['sort_by'] ) ? 'latest' : apply_filters( 'widget_list_sort', $instance['sort_by'] );
		/**
		 * Filter widget's "title_tag" type.
		 *
		 * @param string $instance ['title_tag'] Listing title tag.
		 *
		 * @since 1.6.26
		 *
		 */
		$title_tag = empty( $instance['title_tag'] ) ? 'h3' : apply_filters( 'widget_title_tag', $instance['title_tag'] );
		/**
		 * Filter widget's "show_favorites_only" type.
		 *
		 * @param string $instance ['show_favorites_only'] Listing show favorites only.
		 *
		 * @since 1.6.26
		 *
		 */
		$show_favorites_only = empty( $instance['show_favorites_only'] ) ? '' : apply_filters( 'widget_show_favorites_only', absint( $instance['show_favorites_only'] ), $instance, $this->id_base );
		/**
		 * Filter the widget favorites_by_user param.
		 *
		 * @param string $instance ['favorites_by_user'] Filter favorites by user.
		 *
		 * @since 2.0.0
		 *
		 */
		$favorites_by_user = empty( $instance['favorites_by_user'] ) || empty( $show_favorites_only ) ? '' : apply_filters( 'widget_favorites_by_user', $instance['favorites_by_user'], $instance, $this->id_base );

		$design_style = ! empty( $args['design_style'] ) ? esc_attr( $args['design_style'] ) : geodir_design_style();

		if ( ! empty( $instance['with_carousel'] ) ) {
			$instance['with_pagination'] = false;
			$instance['view_all_link']   = false;

			if ( $this->is_preview() ) {
				$instance['post_limit'] = $post_number = ! empty( $instance['layout'] ) ? absint( $instance['layout'] ) : 3;
			}
		}

		$view_all_link         = ! empty( $instance['view_all_link'] ) ? true : false;
		$use_viewing_post_type = ! empty( $instance['use_viewing_post_type'] ) ? true : false;
		$use_viewing_term      = ! empty( $instance['use_viewing_term'] ) ? true : false;
		$shortcode_atts        = ! empty( $instance['shortcode_atts'] ) ? $instance['shortcode_atts'] : array();
		$top_pagination        = ! empty( $instance['with_pagination'] ) && ! empty( $instance['top_pagination'] ) ? true : false;
		$bottom_pagination     = ! empty( $instance['with_pagination'] ) && ! empty( $instance['bottom_pagination'] ) ? true : false;
		$pagination_info       = ! empty( $instance['with_pagination'] ) && ! empty( $instance['pagination_info'] ) ? $instance['pagination_info'] : '';
		$pageno                = ! empty( $instance['pageno'] ) ? absint( $instance['pageno'] ) : 1;
		if ( $pageno < 1 ) {
			$pageno = 1;
		}

		// set post type to current viewing post type
		if ( $use_viewing_post_type ) {
			$current_post_type = geodir_get_current_posttype();
			if ( $current_post_type != '' && $current_post_type != $post_type ) {
				$post_type = $current_post_type;
				$category  = array(); // old post type category will not work for current changed post type
			}
		}
		if ( ( $related_to == 'default_category' || $related_to == 'category' || $related_to == 'tags' ) && ! empty( $gd_post->ID ) ) {
			if ( $post_type != $gd_post->post_type ) {
				$post_type = $gd_post->post_type;
				$category  = array();
			}
		}

		// check its a GD post type, if not then bail
		if ( ! geodir_is_gd_post_type( $post_type ) ) {
			return '';
		}

		/**
		 * Filter the widget template_type param.
		 *
		 * @param string $template_type Filter template_type.
		 *
		 * @since 2.2.20
		 *
		 */
		$template_type = apply_filters( 'geodir_widget_gd_listings_template_type', ( ! empty( $instance['template_type'] ) ? $instance['template_type'] : '' ), $instance, $this->id_base );

		$template_page = 0;
		/**
		 * Filter the widget tmpl_page param.
		 *
		 * @param int $template_page Filter tmpl_page.
		 *
		 * @since 2.2.20
		 *
		 */
		if ( $template_type == 'page' ) {
			$template_page = apply_filters( 'geodir_widget_gd_listings_tmpl_page', ( ! empty( $instance['tmpl_page'] ) ? (int) $instance['tmpl_page'] : 0 ), $instance, $this->id_base );
		}

		$template_part = '';
		/**
		 * Filter the widget tmpl_part param.
		 *
		 * @param string $template_part Filter tmpl_part.
		 *
		 * @since 2.2.20
		 *
		 */
		if ( $template_type == 'template_part' && geodir_is_block_theme() ) {
			$template_part = apply_filters( 'geodir_widget_gd_listings_tmpl_part', ( ! empty( $instance['tmpl_part'] ) ? $instance['tmpl_part'] : '' ), $instance, $this->id_base );
		}

		$skin_id = 0;
		/**
		 * Filter the widget skin_id param.
		 *
		 * @param int $skin_id Filter skin_id.
		 *
		 * @since 2.2.20
		 *
		 */
		if ( empty( $template_type ) || $template_type == 'elementor_skin' ) {
			$skin_id = apply_filters( 'widget_skin_id', ( ! empty( $instance['skin_id'] ) ? (int) $instance['skin_id'] : 0 ), $instance, $this->id_base );
		}

		$geodir_item_tmpl = array();
		if ( ! empty( $template_page ) && get_post_type( $template_page ) == 'page' && get_post_status( $template_page ) == 'publish' ) {
			$geodir_item_tmpl = array(
				'id'   => $template_page,
				'type' => 'page',
			);
		} elseif ( ! empty( $template_part ) && ( $_template_part = geodir_get_template_part_by_slug( $template_part ) ) ) {
			$geodir_item_tmpl = array(
				'id'      => $_template_part->slug,
				'content' => $_template_part->content,
				'type'    => 'template_part',
			);
		}

		// Filter posts by current terms on category/tag/search archive pages.
		if ( $use_viewing_term ) {
			if ( is_tax() && ( $queried_object = get_queried_object() ) ) {
				if ( ! empty( $queried_object->taxonomy ) ) {
					if ( $queried_object->taxonomy == $post_type . 'category' ) {
						$category             = $queried_object->term_id;
						$instance['category'] = $category;
					} elseif ( $queried_object->taxonomy == $post_type . '_tags' ) {
						$tags             = $queried_object->name;
						$instance['tags'] = $tags;
					}
				}
			}

			if ( geodir_is_page( 'search' ) && ! empty( $_REQUEST['stype'] ) && $_REQUEST['stype'] == $post_type && isset( $_REQUEST['spost_category'] ) && ( ( is_array( $_REQUEST['spost_category'] ) && ! empty( $_REQUEST['spost_category'][0] ) ) || ( ! is_array( $_REQUEST['spost_category'] ) && ! empty( $_REQUEST['spost_category'] ) ) ) ) {
				if ( is_array( $_REQUEST['spost_category'] ) ) {
					$_post_category = array_map( 'absint', $_REQUEST['spost_category'] );
				} else {
					$_post_category = array( absint( $_REQUEST['spost_category'] ) );
				}
				$category             = implode( ',', $_post_category );
				$instance['category'] = $category;
			}
		}

		// replace widget title dynamically
		$posttype_plural_label   = __( geodir_get_post_type_plural_label( $post_type ), 'geodirectory' );
		$posttype_singular_label = __( geodir_get_post_type_singular_label( $post_type ), 'geodirectory' );

		$title = str_replace( '%posttype_plural_label%', $posttype_plural_label, $title );
		$title = str_replace( '%posttype_singular_label%', $posttype_singular_label, $title );

		$category_taxonomy = $post_type . 'category';
		$category          = is_array( $category ) ? $category : explode( ',', $category ); // convert to array
		$category          = apply_filters( 'geodir_filter_query_var_categories', $category, $post_type );
		$categories        = $category;

		if ( isset( $instance['character_count'] ) ) {
			/**
			 * Filter the widget's excerpt character count.
			 *
			 * @param int $instance ['character_count'] Excerpt character count.
			 *
			 * @since 1.0.0
			 *
			 */
			$character_count = apply_filters( 'widget_list_character_count', $instance['character_count'] );
		} else {
			$character_count = '';
		}

		if ( empty( $title ) || $title == 'All' ) {
			$title .= ' ' . __( geodir_get_post_type_plural_label( $post_type ), 'geodirectory' );
		}

		$location_allowed = GeoDir_Post_types::supports( $post_type, 'location' );
		$nearby_gps       = false;

		if ( $location_allowed && $add_location_filter && ( $user_lat = get_query_var( 'user_lat' ) ) && ( $user_lon = get_query_var( 'user_lon' ) ) && geodir_is_page( 'location' ) ) {
			$viewall_url = add_query_arg(
				array(
					'geodir_search' => 1,
					'stype'         => $post_type,
					's'             => '',
					'snear'         => __( 'Near:', 'geodirectory' ) . ' ' . __( 'Me', 'geodirectory' ),
					'sgeo_lat'      => $user_lat,
					'sgeo_lon'      => $user_lon,
				),
				geodir_search_page_base_url()
			);

			if ( ! empty( $category ) && ! in_array( '0', $category ) ) {
				$viewall_url = add_query_arg( array( 's' . $post_type . 'category' => $category ), $viewall_url );
			}
		} elseif ( $location_allowed && ! empty( $instance['nearby_gps'] ) && ( $ip = geodir_get_ip() ) && ( $geo = geodir_geo_by_ip( $ip ) ) ) {
			if ( $this->is_preview() && ! empty( $geodirectory->location ) && ( $default_location = $geodirectory->location->get_default_location() ) ) {
				$nearby_gps = array(
					'latitude'  => $default_location->latitude,
					'longitude' => $default_location->longitude,
				);
			} else {
				$nearby_gps = $geo;
			}

			$viewall_url = add_query_arg(
				array(
					'geodir_search' => 1,
					'stype'         => $post_type,
					's'             => '',
					'snear'         => '',
					'near'          => 'me',
					'sgeo_lat'      => $geo['latitude'],
					'sgeo_lon'      => $geo['longitude'],
				),
				geodir_search_page_base_url()
			);

			if ( ! empty( $category ) && ! in_array( '0', $category ) ) {
				$viewall_url = add_query_arg( array( 's' . $post_type . 'category' => $category ), $viewall_url );
			}
		} else {
			$viewall_url = get_post_type_archive_link( $post_type );

			if ( ! empty( $category ) && $category[0] != '0' ) {
				global $geodir_add_location_url;

				$geodir_add_location_url = '0';

				if ( $add_location_filter != '0' ) {
					$geodir_add_location_url = '1';
				}

				$viewall_url = get_term_link( (int) $category[0], $post_type . 'category' );

				$geodir_add_location_url = null;
			}
		}

		if ( is_wp_error( $viewall_url ) ) {
			$viewall_url = '';
		}

		$distance_to_post = $list_sort == 'distance_asc' && ! empty( $gd_post->latitude ) && ! empty( $gd_post->longitude ) && $is_single && empty( $nearby_gps ) ? true : false;

		if ( $list_sort == 'distance_asc' && ! $distance_to_post && empty( $nearby_gps ) ) {
			$list_sort = geodir_get_posts_default_sort( $post_type );
		}

		$query_args = array(
			'posts_per_page'   => $post_number,
			'is_geodir_loop'   => true,
			'gd_location'      => $add_location_filter ? true : false,
			'post_type'        => $post_type,
			'order_by'         => $list_sort,
			'distance_to_post' => $distance_to_post,
			'nearby_gps'       => $nearby_gps,
			'pageno'           => $pageno,
			'is_gd_author'     => ! empty( $instance['is_gd_author'] ) || geodir_is_page( 'author' ),
		);

		// Post_number needs to be a positive integer
		if ( ! empty( $post_author ) ) {
			// 'current' left for backwards compatibility
			if ( $post_author == 'current' || $post_author == 'current_author' ) {
				if ( ! empty( $post ) && $post->post_type != 'page' && isset( $post->post_author ) ) {
					$query_args['post_author'] = $post->post_author;
				} else {
					$query_args['post_author'] = - 1; // Don't show any listings.
				}
			} elseif ( $post_author == 'current_user' ) {
				if ( is_user_logged_in() && ( $current_user_id = get_current_user_id() ) ) {
					$query_args['post_author'] = $current_user_id;
				} else {
					$query_args['post_author'] = - 1; // If not logged in then don't show any listings.
				}
			} elseif ( absint( $post_author ) > 0 ) {
				$query_args['post_author'] = absint( $post_author );
			} else {
				$query_args['post_author'] = - 1; // Don't show any listings.
			}
		}

		// Posts favorited by user.
		if ( ! empty( $show_favorites_only ) ) {
			if ( empty( $favorites_by_user ) ) {
				$favorites_by_user = 'current_user';
			}

			// 'current' left for backwards compatibility
			if ( $favorites_by_user == 'current' || $favorites_by_user == 'current_author' ) {
				if ( ! empty( $post ) && $post->post_type != 'page' && isset( $post->post_author ) ) {
					$query_args['favorites_by_user'] = $post->post_author;
				} else {
					$query_args['favorites_by_user'] = - 1; // Don't show any listings.
				}
			} elseif ( $favorites_by_user == 'current_user' ) {
				if ( is_user_logged_in() && ( $current_user_id = get_current_user_id() ) ) {
					$query_args['favorites_by_user'] = $current_user_id;
				} else {
					$query_args['favorites_by_user'] = - 1; // If not logged in then don't show any listings.
				}
			} elseif ( absint( $favorites_by_user ) > 0 ) {
				$query_args['favorites_by_user'] = absint( $favorites_by_user );
			} else {
				$query_args['favorites_by_user'] = - 1; // Don't show any listings.
			}
		}

		if ( $character_count ) {
			$query_args['excerpt_length'] = $character_count;
		}

		if ( ! empty( $instance['show_featured_only'] ) ) {
			$query_args['show_featured_only'] = 1;
		}

		if ( ! empty( $instance['show_special_only'] ) ) {
			$query_args['show_special_only'] = 1;
		}

		if ( ! empty( $instance['with_pics_only'] ) || ! empty( $instance['featured_image_only'] ) ) {
			$query_args['with_pics_only']      = 0;
			$query_args['featured_image_only'] = 1;
		}

		if ( ! empty( $instance['with_videos_only'] ) ) {
			$query_args['with_videos_only'] = 1;
		}
		$hide_if_empty = ! empty( $instance['hide_if_empty'] ) ? true : false;

		if ( ! empty( $categories ) && $categories[0] != '0' ) {
			$tax_query = array(
				'taxonomy' => $category_taxonomy,
				'field'    => 'id',
				'terms'    => $category,
			);

			$query_args['tax_query'] = array( $tax_query );
		}

		if ( ( $related_to == 'default_category' || $related_to == 'category' || $related_to == 'tags' ) && ! empty( $gd_post->ID ) ) {
			$terms         = array();
			$term_field    = 'id';
			$term_taxonomy = $post_type . 'category';
			if ( $related_to == 'category' && ! empty( $gd_post->post_category ) ) {
				$terms = explode( ',', trim( $gd_post->post_category, ',' ) );
			} elseif ( $related_to == 'tags' && ! empty( $gd_post->post_tags ) ) {
				$term_taxonomy = $post_type . '_tags';
				$term_field    = 'name';
				$terms         = explode( ',', trim( $gd_post->post_tags, ',' ) );
			} elseif ( $related_to == 'default_category' && ! empty( $gd_post->default_category ) ) {
				$terms = absint( $gd_post->default_category );
			}
			$query_args['post__not_in'] = $gd_post->ID;

			$query_args['tax_query'] = array(
				array(
					'taxonomy' => $term_taxonomy,
					'field'    => $term_field,
					'terms'    => $terms,
				),
			);
		} elseif ( $is_single && empty( $instance['franchise_of'] ) ) {
			$query_args['post__not_in'] = $gd_post->ID;
		}

		// Clean tags
		if ( ! empty( $tags ) ) {
			if ( ! is_array( $tags ) ) {
				$comma = _x( ',', 'tag delimiter' );

				if ( ',' !== $comma ) {
					$tags = str_replace( $comma, ',', $tags );
				}
				$tags = explode( ',', trim( $tags, " \n\t\r\0\x0B," ) );
				$tags = array_map( 'trim', $tags );
			}

			if ( ! empty( $tags ) ) {
				$tag_query = array(
					'taxonomy' => $post_type . '_tags',
					'field'    => 'name',
					'terms'    => $tags,
				);

				if ( ! empty( $query_args['tax_query'] ) ) {
					$query_args['tax_query'][] = $tag_query;
				} else {
					$query_args['tax_query'] = array( $tag_query );
				}
			}
		}

		// $post_ids, include or exclude post ids
		if ( ! empty( $post_ids ) ) {
			$post__not_in = array();
			$post__in     = array();
			$post_ids     = array_filter( array_map( 'trim', explode( ',', $post_ids ) ) );

			foreach ( $post_ids as $pid ) {
				$tmp_id = $pid;
				if ( abs( $tmp_id ) != $tmp_id ) {
					$post__not_in[] = absint( $tmp_id );
				} else {
					$post__in[] = absint( $tmp_id );
				}
			}

			if ( ! empty( $post__in ) ) {
				$query_args['post__in'] = implode( ',', $post__in );
			} elseif ( ! empty( $post__not_in ) ) {
				if ( ! empty( $query_args['post__not_in'] ) ) {
					$post__not_in[] = $query_args['post__not_in'];
				}
				$query_args['post__not_in'] = implode( ',', $post__not_in );
			}
		}

		global $geodir_widget_cpt, $gd_layout_class, $geodir_is_widget_listing;

		/*
		 * Filter widget listings query args.
		 */
		$query_args = apply_filters( 'geodir_widget_listings_query_args', $query_args, $instance );

		$query_args['country']    = isset( $instance['country'] ) ? $instance['country'] : '';
		$query_args['region']     = isset( $instance['region'] ) ? $instance['region'] : '';
		$query_args['city']       = isset( $instance['city'] ) ? $instance['city'] : '';
		$query_args['count_only'] = true;

		$post_count = geodir_get_widget_listings( $query_args, true );

		$query_args['count_only'] = false;

		if ( $hide_if_empty && empty( $post_count ) ) {
			return;
		}

		$widget_listings = geodir_get_widget_listings( $query_args );

		// Filter post title tag.
		$this->post_title_tag = $title_tag;
		add_filter( 'geodir_widget_gd_post_title_tag', array( $this, 'filter_post_title_tag' ), 10, 4 );

		$gd_layout_class = geodir_convert_listing_view_class( $layout );

		$class = $top_pagination || $bottom_pagination ? ' geodir-wgt-pagination' : '';
		if ( $top_pagination ) {
			$class .= ' geodir-wgt-pagination-top';
		}
		if ( $bottom_pagination ) {
			$class .= ' geodir-wgt-pagination-bottom';
		}

		// card border class
		$card_border_class = '';
		if ( ! empty( $instance['card_border'] ) ) {
			if ( $instance['card_border'] == 'none' ) {
				$card_border_class = 'border-0';
			} else {
				$card_border_class = 'border-' . sanitize_html_class( $instance['card_border'] );
			}
		}

		// card shadow
		$card_shadow_class = '';
		if ( ! empty( $instance['card_shadow'] ) ) {
			if ( $instance['card_shadow'] == 'small' ) {
				$card_shadow_class = 'shadow-sm';
			} elseif ( $instance['card_shadow'] == 'medium' ) {
				$card_shadow_class = 'shadow';
			} elseif ( $instance['card_shadow'] == 'large' ) {
				$card_shadow_class = 'shadow-lg';
			}
		}

		$backup_posts_per_page         = $posts_per_page;
		$backup_paged                  = $paged;
		$backup_gd_advanced_pagination = $gd_advanced_pagination;

		$geodir_widget_cpt      = $post_type;
		$posts_per_page         = $post_number;
		$paged                  = $pageno;
		$gd_advanced_pagination = $pagination_info;
		$unique_id              = 'geodir_' . uniqid();
		$wrapper_attrs          = '';

		// Carousel
		$carousel_items       = absint( $layout );
		$carousel_row         = '#' . $unique_id . ' .row';
		$geodir_carousel_open = ! empty( $instance['with_carousel'] ) ? true : false;
		if ( ! empty( $instance['with_carousel'] ) && ! empty( $widget_listings ) ) {
			if ( ! geodir_design_style() ) {
				// Enqueue flexslider script.
				GeoDir_Frontend_Scripts::enqueue_script( 'jquery-flexslider' );
			}

			$class .= ' geodir-posts-carousel';

			if ( ! empty( $instance['with_controls'] ) ) {
				$wrapper_attrs .= ' data-with-controls="1"';
			}

			if ( ! empty( $instance['with_indicators'] ) ) {
				$wrapper_attrs .= ' data-with-indicators="1"';
			}

			if ( $aui_bs5 && ! empty( $instance['center_slide'] ) ) {
				$wrapper_attrs .= ' data-center-slide="1"';
			}

			// Interval
			if ( $instance['slide_interval'] === '0' ) {
				$slide_interval = 'false';

				// Disable auto slide.
				if ( $aui_bs5 ) {
					$instance['slide_ride'] = 'click';
				}
			} else {
				$slide_interval = ! empty( $instance['slide_interval'] ) ? geodir_sanitize_float( $instance['slide_interval'] ) * 1000 : 5000;
			}

			$bs = $aui_bs5 ? 'bs-' : '';

			$wrapper_attrs .= ' data-' . $bs . 'interval="' . $slide_interval . '"';
			$wrapper_attrs .= ' data-' . $bs . 'ride="' . ( $instance['slide_ride'] == 'click' ? 'false' : 'carousel' ) . '"';
			$wrapper_attrs .= ' data-' . $bs . 'pause="hover"';


			$indicators_class = sd_build_aui_class(array(
				'mb'	=> !empty($instance['indicators_mb']) ? $instance['indicators_mb'] : '',
			));
			$wrapper_attrs .= ' data-' . $bs . 'indicators-class="' . esc_attr($indicators_class) . '"';

			if ( $carousel_items < 1 ) {
				$carousel_items = 1;
			}

			$wrapper_attrs .= ' data-with-items="' . $carousel_items . '"';
		}

		// Elementor
		$skin_active             = false;
		$elementor_wrapper_class = '';
		if ( defined( 'ELEMENTOR_PRO_VERSION' ) && $skin_id ) {
			if ( get_post_status( $skin_id ) == 'publish' ) {
				$skin_active = true;

				$geodir_item_tmpl = array(
					'id'   => $skin_id,
					'type' => 'elementor_skin',
				);
			}

			if ( $skin_active ) {
				$columns = isset( $layout ) ? absint( $layout ) : 1;
				if ( $columns == '0' ) {
					$columns = 6; // we have no 6 row option to lets use list view
				}
				$elementor_wrapper_class = ' elementor-element elementor-element-9ff57fdx elementor-posts--thumbnail-top elementor-grid-' . $columns . ' elementor-grid-tablet-2 elementor-grid-mobile-1 elementor-widget elementor-widget-posts ';
			}
			$carousel_row = '#' . $unique_id . ' .elementor-posts';
		}

		// wrap class
		$class .= ' ' . sd_build_aui_class( $instance );

		// preview message
		$is_preview = $this->is_preview();
		if ( $is_preview && $design_style ) {
			echo aui()->alert(
				array(
					'type'    => 'info',
					'content' => __( 'This preview shows all content items to give an idea of layout. Dummy data is used in places.', 'geodirectory' ),
				)
			);
		}

		?>
		<div id="<?php echo $unique_id; ?>" class="geodir_locations geodir_location_listing
							<?php
							echo $class;
							echo $elementor_wrapper_class;
							?>
		 position-relative"<?php echo $wrapper_attrs; ?>>
			<?php
			if ( ! isset( $character_count ) ) {
				/**
				 * Filter the widget's excerpt character count.
				 *
				 * @param int $instance ['character_count'] Excerpt character count.
				 *
				 * @since 1.0.0
				 *
				 */
				$character_count = $character_count == '' ? 50 : apply_filters( 'widget_character_count', $character_count );
			}

			if ( isset( $post ) ) {
				$reset_post = $post;
			}
			if ( isset( $gd_post ) ) {
				$reset_gd_post = $gd_post;
			}
			$geodir_is_widget_listing = true;

			if ( ! empty( $widget_listings ) && $top_pagination ) {
				self::get_pagination( 'top', $post_count, $post_number, $pageno, $pagination_info );
			}

			if ( $skin_active ) {
				$column_gap = ! empty( $instance['skin_column_gap'] ) ? absint( $instance['skin_column_gap'] ) : '';
				$row_gap    = ! empty( $instance['skin_row_gap'] ) ? absint( $instance['skin_row_gap'] ) : '';
				geodir_get_template(
					'elementor/content-widget-listing.php',
					array(
						'widget_listings' => $widget_listings,
						'skin_id'         => $skin_id,
						'columns'         => $columns,
						'column_gap'      => $column_gap,
						'row_gap'         => $row_gap,
					)
				);
			} else {

				$template = $design_style ? $design_style . '/content-widget-listing.php' : 'content-widget-listing.php';

				echo geodir_get_template_html(
					$template,
					array(
						'widget_listings'   => $widget_listings,
						'column_gap_class'  => $instance['column_gap'] ? 'mb-' . absint( $instance['column_gap'] ) : 'mb-4',
						'row_gap_class'     => $instance['row_gap'] ? 'px-' . absint( $instance['row_gap'] ) : '',
						'card_border_class' => $card_border_class,
						'card_shadow_class' => $card_shadow_class,
					)
				);
			}

			if ( ! empty( $widget_listings ) && ( $bottom_pagination || $top_pagination ) ) {
				if ( $design_style ) {
					echo '<div class="geodir-ajax-listings-loader loading_div overlay overlay-black position-absolute row m-0 z-index-1 w-100 h-100 rounded overflow-hidden" style="display: none;z-index: 3;top:0;">
								<div class="spinner-border mx-auto align-self-center text-white" role="status">
									<span class="sr-only visually-hidden">' . __( 'Loading...', 'geodirectory' ) . '</span>
								</div>
							</div>';
				} else {
					echo '<div class="geodir-ajax-listings-loader" style="display:none"><i class="fas fa-sync fa-spin" aria-hidden="true"></i></div>';
				}

				if ( $bottom_pagination ) {
					self::get_pagination( 'bottom', $post_count, $post_number, $pageno, $pagination_info );
				}
			}

			if ( ! empty( $widget_listings ) && $view_all_link && $viewall_url ) {
				/**
				 * Filter view all url.
				 *
				 * @param string $viewall_url View all url.
				 * @param array $query_args WP_Query args.
				 * @param array $instance Widget settings.
				 * @param array $args Widget arguments.
				 * @param object $this The GeoDir_Widget_Listings object.
				 *
				 * @since 2.0.0
				 *
				 */
				$viewall_url = apply_filters( 'geodir_widget_gd_listings_view_all_url', $viewall_url, $query_args, $instance, $args, $this );

				if ( $viewall_url ) {
					$view_all_link = '<a href="' . esc_url( $viewall_url ) . '" class="geodir-all-link">' . __( 'View all', 'geodirectory' ) . '</a>';

					/**
					 * Filter view all link content.
					 *
					 * @param string $view_all_link View all listings link content.
					 * @param string $viewall_url View all url.
					 * @param array $query_args WP_Query args.
					 * @param array $instance Widget settings.
					 * @param array $args Widget arguments.
					 * @param object $this The GeoDir_Widget_Listings object.
					 *
					 * @since 2.0.0
					 *
					 */
					$view_all_link = apply_filters( 'geodir_widget_gd_listings_view_all_link', $view_all_link, $viewall_url, $query_args, $instance, $args, $this );

					if ( $design_style ) {
						$view_all_link = str_replace( 'geodir-all-link', 'geodir-all-link btn btn-outline-primary', $view_all_link );
						echo '<div class="geodir-widget-bottom text-center">' . $view_all_link . '</div>';
					} else {
						echo '<div class="geodir-widget-bottom">' . $view_all_link . '</div>';
					}
				}
			}

			if ( $this->is_preview() && ! empty( $instance['with_indicators'] ) ) {
				?>
				<a class="carousel-control-prev text-dark mr-2 ml-n2 me-2 ms-n4 w-auto <?php echo !empty($indicators_class) ? esc_attr($indicators_class) : ''; ?>" href="#geodir_63f5e57bafaec_0" role="button" data-bs-slide="prev"><i class="fas fa-chevron-left fa-lg" aria-hidden="true"></i><span class="sr-only visually-hidden">Previous</span></a>
				<a class="carousel-control-next text-dark ml-2 w-auto mr-n2 me-n4 ms-2 <?php echo !empty($indicators_class) ? esc_attr($indicators_class) : ''; ?>" href="#geodir_63f5e57bafaec_0" role="button" data-bs-slide="next"><i class="fas fa-chevron-right fa-lg" aria-hidden="true"></i><span class="sr-only visually-hidden">Next</span></a>
				<?php
			}

			if ( $this->is_preview() && ! empty( $instance['with_controls'] ) ) {
				?>
				<ol class="carousel-indicators position-relative"><li data-bs-target="#geodir_63f5e57bafaec_0" data-bs-slide-to="0" class="bg-dark active" aria-current="true"></li><li data-bs-target="#geodir_63f5e57bafaec_0" data-bs-slide-to="1" class="bg-dark"></li><li data-bs-target="#geodir_63f5e57bafaec_0" data-bs-slide-to="2" class="bg-dark"></li></ol>
				<?php
			}

			$geodir_is_widget_listing = false;

			if ( isset( $reset_post ) ) {
				if ( ! empty( $reset_post ) ) {
					setup_postdata( $reset_post );
				}
				$post = $reset_post;
			}
			if ( isset( $reset_gd_post ) ) {
				$gd_post = $reset_gd_post;
			}
			if ( ! empty( $widget_listings ) && ( $top_pagination || $bottom_pagination ) ) {
				$params = array_merge( $instance, $query_args );

				$set_query_vars = (array) $wp->query_vars;
				if ( isset( $query_args['tax_query'] ) ) {
					$set_query_vars['tax_query'] = $query_args['tax_query'];
				}

				if ( isset( $query_args['post__in'] ) ) {
					$set_query_vars['post__in'] = $query_args['post__in'];
				}

				if ( isset( $query_args['post__not_in'] ) ) {
					$set_query_vars['post__not_in'] = $query_args['post__not_in'];
				}

				$params['set_query_vars'] = $set_query_vars;

				if ( $is_single ) {
					$params['set_post'] = $gd_post->ID;
				}

				if ( ! empty( $_REQUEST['sgeo_lat'] ) && ! empty( $_REQUEST['sgeo_lon'] ) ) {
					$params['sgeo_lat'] = isset( $_REQUEST['sgeo_lat'] ) ? filter_var( $_REQUEST['sgeo_lat'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION ) : '';
					$params['sgeo_lon'] = isset( $_REQUEST['sgeo_lon'] ) ? filter_var( $_REQUEST['sgeo_lon'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION ) : '';
				}

				foreach ( $params as $key => $value ) {
					if ( is_scalar( $value ) && ( $value === true || $value === false ) ) {
						$value = (int) $value;
					}
					$params[ $key ] = $value;
				}

				$params = apply_filters( 'geodir_widget_listings_pagination_set_params', $params, $instance, $this->id_base );
				?>
				<script type="text/javascript">
					/* <![CDATA[ */
					jQuery(function () {
						try {
							var params = <?php echo json_encode( $params ); ?>;
							params['action'] = 'geodir_widget_listings';
							params['widget_args'] = <?php echo json_encode( $args ); ?>;
							params['security'] = geodir_params.basic_nonce;
							geodir_widget_listings_pagination('<?php echo $unique_id; ?>', params);
						} catch (err) {
							console.log(err.message);
						}
					});
					/* ]]> */
				</script>
				<?php
			}
			?>
		</div>
		<?php
		if ( $design_style && ! empty( $instance['with_carousel'] ) && ! empty( $widget_listings ) ) {
			?>
			<style><?php echo $carousel_row; ?>.carousel-item.active, <?php echo $carousel_row; ?>.carousel-item-<?php echo ( $aui_bs5 ? 'start' : 'left' ); ?>, <?php echo $carousel_row; ?>.carousel-item-<?php echo ( $aui_bs5 ? 'end' : 'right' ); ?> {
					display: flex;
				}

				<?php echo $carousel_row; ?>.carousel-item {
					margin-left: auto;
				}</style>
			<?php
		}

		$geodir_widget_cpt      = false;
		$posts_per_page         = $backup_posts_per_page;
		$paged                  = $backup_paged;
		$gd_advanced_pagination = $backup_gd_advanced_pagination;
		$geodir_carousel_open   = false;
		$geodir_item_tmpl       = array();

		remove_filter( 'geodir_widget_gd_post_title_tag', array( $this, 'filter_post_title_tag' ), 10, 2 );
	}

	public static function get_pagination( $position, $post_count, $post_number, $pageno = 1, $show_advanced = '' ) {
		global $wp_query;

		$backup_wp_query = $wp_query;
		if ( isset( $wp_query->paged ) ) {
			$backup_paged = $wp_query->paged;
		}
		if ( isset( $wp_query->max_num_pages ) ) {
			$backup_max_num_pages = $wp_query->max_num_pages;
		}
		if ( isset( $wp_query->found_posts ) ) {
			$backup_found_posts = $wp_query->found_posts;
		}
		if ( isset( $wp_query->is_paged ) ) {
			$backup_is_paged = $wp_query->is_paged;
		}

		$max_num_pages = ceil( $post_count / $post_number );
		set_query_var( 'paged', $pageno );
		$wp_query->max_num_pages = $max_num_pages;
		$wp_query->found_posts   = $post_count;
		$wp_query->is_paged      = true;

		add_filter( 'geodir_pagination_args', array( __CLASS__, 'filter_pagination_args' ), 999999, 1 );

		$shortcode = '[gd_loop_paging mid_size=0 show_advanced="' . $show_advanced . '"]';

		$shortcode = apply_filters( 'geodir_widget_listings_pagination_shortcode', $shortcode, $wp_query, $position, $post_number, $show_advanced );

		ob_start();

		echo do_shortcode( $shortcode );

		$pagination = ob_get_clean();

		echo $pagination;

		remove_filter( 'geodir_pagination_args', array( __CLASS__, 'filter_pagination_args' ), 999999, 1 );

		$wp_query = $backup_wp_query;
		if ( isset( $backup_paged ) ) {
			set_query_var( 'paged', $backup_paged );
		}
		if ( isset( $backup_max_num_pages ) ) {
			$wp_query->max_num_pages = $backup_max_num_pages;
		}
		if ( isset( $backup_found_posts ) ) {
			$wp_query->found_posts = $backup_found_posts;
		}
		if ( isset( $backup_is_paged ) ) {
			$wp_query->is_paged = $backup_is_paged;
		}
	}

	public function set_current_check_404( $check_404 ) {
		return false;
	}
}
