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
class GeoDir_Widget_Simple_Archive_Item extends WP_Super_Duper {

	public $post_title_tag;

	/**
	 * Register the popular posts widget.
	 *
	 * @since 1.0.0
	 * @since 1.5.1 Changed from PHP4 style constructors to PHP5 __construct.
	 */
	public function __construct() {

		$options = array(
			'textdomain'     => GEODIRECTORY_TEXTDOMAIN,
			'block-icon'     => 'fas fa-th-list',
			'block-category' => 'geodirectory',
			'block-supports' => array(//  'customClassName'   => false
			),
			'block-wrap'     => '', // the element to wrap the block output in. , ie: div, span or empty for no wrap
			'no_wrap'        => true,
			'block-keywords' => "['archive','item','geo']",
			'class_name'     => __CLASS__,
			'base_id'        => 'gd_simple_archive_item', // this us used as the widget id and the shortcode id.
			'name'           => __( 'GD > Simple Archive Item ', 'geodirectory' ), // the name of the widget.
			'widget_ops'     => array(
				'classname'                   => 'geodir-simple-archive-item ' . geodir_bsui_class(),
				// widget class
				'description'                 => esc_html__( 'Easily build an archive item design.', 'geodirectory' ),
				// widget description
				'customize_selective_refresh' => true,
				'geodirectory'                => true,
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

		//      $post_types = geodir_get_posttypes( 'options-plural' );

		$show_options = array(
			''            => __( 'icon + label + value', 'geodirectory' ),
			'icon-value'  => __( 'icon + value', 'geodirectory' ),
			'label-value' => __( 'label + value', 'geodirectory' ),
			'icon'        => __( 'icon', 'geodirectory' ),
			'label'       => __( 'label', 'geodirectory' ),
			'value'       => __( 'value', 'geodirectory' ),
			'value-strip' => __( 'value (strip_tags)', 'geodirectory' ),
			'value-raw'   => __( 'value (saved in database)', 'geodirectory' ),
		//          "badge" => __('Badge (may not work with all)', 'geodirectory'),
		);

		$badge_options = $this->get_badge_options();

		$arguments = array(

			'preview_type' => array(
				'title'    => __( 'Preview Type', 'geodirectory' ),
				'desc'     => __( 'Select the preview layout', 'geodirectory' ),
				'type'     => 'select',
				'options'  => array(
					'grid' => __( 'Grid', 'geodirectory' ),
					'list' => __( 'List', 'geodirectory' ),
				),
				'default'  => 'grid',
				'desc_tip' => true,
				'advanced' => false,
				'group'    => __( 'Preview', 'geodirectory' ),
			),

			'card_wrap'    => array(
				'title'    => __( 'Add Card Wrap', 'geodirectory' ),
				'desc'     => __( 'If you are using this inside a block theme template part you will need to set this to yes.', 'geodirectory' ),
				'type'     => 'select',
				'options'  => array(
					''  => __( 'No', 'geodirectory' ),
					'1' => __( 'Yes', 'geodirectory' ),
				),
				'default'  => 'grid',
				'desc_tip' => true,
				'advanced' => false,
				'group'    => __( 'Card Wrap', 'geodirectory' ),
			),

			'card_border'  => array(
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
				'group'    => __( 'Card Wrap', 'geodirectory' ),
				'element_require' => '[%card_wrap%]!=""',
			),

			'card_shadow'  => array(
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
				'group'    => __( 'Card Wrap', 'geodirectory' ),
				'element_require' => '[%card_wrap%]!=""',
			),

			'image_type'   => array(
				'title'    => __( 'Image Type', 'geodirectory' ),
				'desc'     => __( 'Set the image type', 'geodirectory' ),
				'type'     => 'select',
				'options'  => array(
					'image'  => __( 'Single', 'geodirectory' ),
					'slider' => __( 'Slider', 'geodirectory' ),
					'none'   => __( 'No image', 'geodirectory' ),
				),
				'default'  => 'image',
				'desc_tip' => true,
				'advanced' => false,
				'group'    => __( 'Image', 'geodirectory' ),
			),
			'image_link'   => array(
				'title'           => __( 'Image Link', 'geodirectory' ),
				'desc'            => __( 'Image link action', 'geodirectory' ),
				'type'            => 'select',
				'options'         => array(
					'post'     => __( 'Post', 'geodirectory' ),
					'lightbox' => __( 'Open Image Lightbox', 'geodirectory' ),
					'none'     => __( 'No link', 'geodirectory' ),
				),
				'default'         => 'post',
				'desc_tip'        => true,
				'advanced'        => false,
				'group'           => __( 'Image', 'geodirectory' ),
				'element_require' => '[%image_type%]!="none"',
			),
		);

		$arguments = $arguments + $this->image_badge( 'top_left' );
		$arguments = $arguments + $this->image_badge( 'top_right' );
		$arguments = $arguments + $this->image_badge( 'bottom_left' );
		$arguments = $arguments + $this->image_badge( 'bottom_right' );
			// Image Badges
		//          'top_left_badge'     => array(
		//              'title'    => __( 'Top Left Badge', 'geodirectory' ),
		//              'desc'     => __( 'Select the badge to show', 'geodirectory' ),
		//              'type'     => 'select',
		//              'options'  => $this->get_badge_options(true),
		//              'default'  => 'featured',
		//              'desc_tip' => true,
		//              'advanced' => false,
		//              'group'    => __( "Image Badge (top left)", "geodirectory" ),
		//              'element_require' => '[%image_type%]!="none"',
		//          ),

		$arguments = $arguments + array(
			//          'top_right_badge'    => array(
			//              'title'    => __( 'Top Right Badge', 'geodirectory' ),
			//              'desc'     => __( 'Select the badge to show', 'geodirectory' ),
			//              'type'     => 'select',
			//              'options'  => $this->get_badge_options(true),
			//              'default'  => 'new',
			//              'desc_tip' => true,
			//              'advanced' => false,
			//              'group'    => __( "Image", "geodirectory" ),
			//              'element_require' => '[%image_type%]!="none"',
			//          ),
			//          'bottom_left_badge'  => array(
			//              'title'    => __( 'Bottom Left Badge', 'geodirectory' ),
			//              'desc'     => __( 'Select the badge to show', 'geodirectory' ),
			//              'type'     => 'select',
			//              'options'  => $this->get_badge_options(true),
			//              'default'  => 'category',
			//              'desc_tip' => true,
			//              'advanced' => false,
			//              'group'    => __( "Image", "geodirectory" ),
			//              'element_require' => '[%image_type%]!="none"',
			//          ),
			//          'bottom_right_badge' => array(
			//              'title'    => __( 'Bottom Right Badge', 'geodirectory' ),
			//              'desc'     => __( 'Select the badge to show', 'geodirectory' ),
			//              'type'     => 'select',
			//              'options'  => $this->get_badge_options(true),
			//              'default'  => 'favorite',
			//              'desc_tip' => true,
			//              'advanced' => false,
			//              'group'    => __( "Image", "geodirectory" ),
			//              'element_require' => '[%image_type%]!="none"',
			//          ),

				// Body Design
				'body_bg_color'  => array(
					'title'    => __( 'Background color', 'geodirectory' ),
					'desc'     => __( 'Select the the background color.', 'geodirectory' ),
					'type'     => 'select',
					'options'  => array(
						'' => __( 'Default', 'geodirectory' ),
					) + geodir_aui_colors( false ),
					'default'  => '',
					'desc_tip' => true,
					'advanced' => false,
					'group'    => __( 'Body Design', 'geodirectory' ),
				),
			'body_pt'            => geodir_get_sd_padding_input( 'pt', array( 'group' => __( 'Body Design', 'geodirectory' ) ) ),
			'body_pr'            => geodir_get_sd_padding_input( 'pr', array( 'group' => __( 'Body Design', 'geodirectory' ) ) ),
			'body_pb'            => geodir_get_sd_padding_input( 'pb', array( 'group' => __( 'Body Design', 'geodirectory' ) ) ),
			'body_pl'            => geodir_get_sd_padding_input( 'pl', array( 'group' => __( 'Body Design', 'geodirectory' ) ) ),

			// Circle image
			'circle_image_type'  => array(
				'title'    => __( 'Circle image type', 'geodirectory' ),
				'desc'     => __( 'Select the type of image', 'geodirectory' ),
				'type'     => 'select',
				'options'  => array(
					'author'         => __( 'Author', 'geodirectory' ),
					'author_claimed' => __( 'Author (claimed only)', 'geodirectory' ),
					'logo'           => __( 'Logo', 'geodirectory' ),
					'hide'           => __( 'Hide', 'geodirectory' ),
				),
				'default'  => 'author',
				'desc_tip' => true,
				'advanced' => false,
				'group'    => __( 'Circle Image', 'geodirectory' ),
			),
			'circle_image_align' => array(
				'title'    => __( 'Align', 'geodirectory' ),
				'desc'     => __( 'How to align the image.', 'geodirectory' ),
				'type'     => 'select',
				'options'  => array(
					'center' => __( 'Center', 'geodirectory' ),
					'left'   => __( 'Left', 'geodirectory' ),
					'right'  => __( 'Right', 'geodirectory' ),
				),
				'default'  => 'center',
				'desc_tip' => true,
				'advanced' => false,
				'group'    => __( 'Circle Image', 'geodirectory' ),
			),
			// Title
			'title_font_size'    => geodir_get_sd_font_size_input( array( 'group' => __( 'Title', 'geodirectory' ) ) ),
			'title_text_align'   => geodir_get_sd_text_align_input( array( 'group' => __( 'Title', 'geodirectory' ) ) ),
			'title_text_color'   => array(
				'title'    => __( 'Font Color', 'geodirectory' ),
				'desc'     => __( 'Set the font color', 'geodirectory' ),
				'type'     => 'select',
				'options'  => array(
					'' => __( 'Default (inherit)', 'geodirectory' ),
				) + geodir_aui_colors(),
				'default'  => '',
				'desc_tip' => true,
				'advanced' => false,
				'group'    => __( 'Title', 'geodirectory' ),
			),
			'title_pt'           => geodir_get_sd_padding_input(
				'pt',
				array(
					'group' => __( 'Title', 'geodirectory' ),
					'row'   => array(
						'title' => __( 'Padding', 'geodirectory' ),
						'key'   => 'title-padding',
						'open'  => true,
						'class' => 'text-center',
					),
				)
			),
			'title_pb'           => geodir_get_sd_padding_input(
				'pb',
				array(
					'group' => __( 'Title', 'geodirectory' ),
					'row'   => array(
						'key'   => 'title-padding',
						'close' => true,

					),
				)
			),

			// Description
			'limit'              => array(
				'title'       => __( 'Word limit:', 'geodirectory' ),
				'desc'        => __( 'How many words to limit the text to. (will auto strip tags)', 'geodirectory' ),
				'type'        => 'number',
				'placeholder' => '20',
				'default'     => '20',
				'desc_tip'    => true,
				'advanced'    => false,
				'group'       => __( 'Description', 'geodirectory' ),
			),
			'read_more'          => array(
				'title'       => __( 'Read more link:', 'geodirectory' ),
				'desc'        => __( 'Show the read more link at the end of the text. enter `0` to not show link.', 'geodirectory' ),
				'type'        => 'text',
				'desc_tip'    => true,
				'default'     => '0',
				'placeholder' => __( 'Read more...', 'geodirectory' ),
				'advanced'    => false,
				'group'       => __( 'Description', 'geodirectory' ),
			),
			'desc_text_color'    => geodir_get_sd_text_color_input( array( 'group' => __( 'Description', 'geodirectory' ) ) ),
			'desc_text_align'    => array(
				'title'    => __( 'Text Align', 'geodirectory' ),
				'type'     => 'select',
				'options'  => array(
					''        => __( 'None', 'geodirectory' ),
					'left'    => __( 'Left', 'geodirectory' ),
					'center'  => __( 'Center', 'geodirectory' ),
					'right'   => __( 'Right', 'geodirectory' ),
					'justify' => __( 'Justify', 'geodirectory' ),
				),
				'default'  => 'justify',
				'desc_tip' => true,
				'advanced' => false,
				'group'    => __( 'Description', 'geodirectory' ),
			),
			'desc_pt'            => geodir_get_sd_padding_input(
				'pt',
				array(
					'group' => __( 'Description', 'geodirectory' ),
					'row'   => array(
						'title' => __( 'Padding', 'geodirectory' ),
						'key'   => 'desc-padding',
						'open'  => true,
						'class' => 'text-center',
					),
				)
			),
			'desc_pb'            => geodir_get_sd_padding_input(
				'pb',
				array(
					'group'   => __( 'Description', 'geodirectory' ),
					'default' => '1',
					'row'     => array(
						'key'   => 'desc-padding',
						'close' => true,

					),
				)
			),

			// Output location
			'list_style'         => array(
				'title'    => __( 'List style', 'geodirectory' ),
				'desc'     => __( 'Select the list style', 'geodirectory' ),
				'type'     => 'select',
				'options'  => array(
					'wrap' => __( 'Wrap with lines', 'geodirectory' ),
					'line' => __( 'Line separators', 'geodirectory' ),
					'none' => __( 'None', 'geodirectory' ),
				),
				'default'  => 'none',
				'desc_tip' => true,
				'advanced' => false,
				'group'    => __( 'Output Location', 'geodirectory' ),
			),
			'item_py'            => array(
				'title'    => __( 'Item vertical padding', 'geodirectory' ),
				'desc'     => __( 'The padding between items', 'geodirectory' ),
				'type'     => 'select',
				'options'  => array(
					''  => __( 'Default', 'geodirectory' ),
					'0' => '0',
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
				),
				'default'  => '1',
				'desc_tip' => true,
				'advanced' => false,
				'group'    => __( 'Output Location', 'geodirectory' ),
			),
			'list_text_align'    => geodir_get_sd_text_align_input( array( 'group' => __( 'Output Location', 'geodirectory' ) ) ),
			'list_pt'            => geodir_get_sd_padding_input(
				'pt',
				array(
					'group' => __( 'Output Location', 'geodirectory' ),
					'row'   => array(
						'title' => __( 'Padding', 'geodirectory' ),
						'key'   => 'list-padding',
						'open'  => true,
						'class' => 'text-center',
					),
				)
			),
			'list_pb'            => geodir_get_sd_padding_input(
				'pb',
				array(
					'group' => __( 'Output Location', 'geodirectory' ),
					'row'   => array(
						'key'   => 'list-padding',
						'close' => true,

					),
				)
			),
			// footer
			'footer_items'       => array(
				'title'    => __( 'Footer Items', 'geodirectory' ),
				'desc'     => __( 'Select how many footer items to show', 'geodirectory' ),
				'type'     => 'select',
				'options'  => array(
					'0' => __( 'None', 'geodirectory' ),
					'1' => __( 'One', 'geodirectory' ),
					'2' => __( 'Two', 'geodirectory' ),
					'3' => __( 'Three', 'geodirectory' ),
					'4' => __( 'Four', 'geodirectory' ),
					'5' => __( 'Five', 'geodirectory' ),
				),
				'default'  => '2',
				'desc_tip' => true,
				'advanced' => false,
				'group'    => __( 'Footer', 'geodirectory' ),
			),
			'footer_item_1'      => array(
				'title'           => __( 'Type', 'geodirectory' ),
				'type'            => 'select',
				'options'         => $badge_options,
				'default'         => 'rating',
				'desc_tip'        => true,
				'advanced'        => false,
				'group'           => __( 'Footer', 'geodirectory' ),
				'element_require' => '[%footer_items%] > 0',
				'row'             => array(
					'title' => __( 'Item 1', 'geodirectory' ),
					'key'   => 'footer-item-1',
					'open'  => true,
					'class' => 'text-center',
				),
			),
			'footer_item_1_show' => array(
				'title'           => __( 'Output', 'geodirectory' ),
				'type'            => 'select',
				'options'         => $show_options,
				'default'         => '',
				'desc_tip'        => true,
				'advanced'        => false,
				'group'           => __( 'Footer', 'geodirectory' ),
				'element_require' => '[%footer_items%] > 0',
				'row'             => array(
					'key'   => 'footer-item-1',
					'close' => true,

				),
			),
			'footer_item_2'      => array(
				'title'           => __( 'Type', 'geodirectory' ),
				'type'            => 'select',
				'options'         => $badge_options,
				'default'         => 'business_hours',
				'desc_tip'        => true,
				'advanced'        => false,
				'group'           => __( 'Footer', 'geodirectory' ),
				'element_require' => '[%footer_items%] > 1',
				'row'             => array(
					'title' => __( 'Item 2', 'geodirectory' ),
					'key'   => 'footer-item-2',
					'open'  => true,
					'class' => 'text-center',
				),
			),
			'footer_item_2_show' => array(
				'title'           => __( 'Output', 'geodirectory' ),
				'type'            => 'select',
				'options'         => $show_options,
				'default'         => '',
				'desc_tip'        => true,
				'advanced'        => false,
				'group'           => __( 'Footer', 'geodirectory' ),
				'element_require' => '[%footer_items%] > 1',
				'row'             => array(
					'key'   => 'footer-item-2',
					'close' => true,

				),
			),
			'footer_item_3'      => array(
				'title'           => __( 'Type', 'geodirectory' ),
				'type'            => 'select',
				'options'         => $badge_options,
				'default'         => 'business_hours',
				'desc_tip'        => true,
				'advanced'        => false,
				'group'           => __( 'Footer', 'geodirectory' ),
				'element_require' => '[%footer_items%] > 2',
				'row'             => array(
					'title' => __( 'Item 3', 'geodirectory' ),
					'key'   => 'footer-item-3',
					'open'  => true,
					'class' => 'text-center',
				),
			),
			'footer_item_3_show' => array(
				'title'           => __( 'Output', 'geodirectory' ),
				'type'            => 'select',
				'options'         => $show_options,
				'default'         => '',
				'desc_tip'        => true,
				'advanced'        => false,
				'group'           => __( 'Footer', 'geodirectory' ),
				'element_require' => '[%footer_items%] > 2',
				'row'             => array(
					'key'   => 'footer-item-3',
					'close' => true,

				),
			),
			'footer_item_4'      => array(
				'title'           => __( 'Type', 'geodirectory' ),
				'type'            => 'select',
				'options'         => $badge_options,
				'default'         => 'business_hours',
				'desc_tip'        => true,
				'advanced'        => false,
				'group'           => __( 'Footer', 'geodirectory' ),
				'element_require' => '[%footer_items%] > 3',
				'row'             => array(
					'title' => __( 'Item 4', 'geodirectory' ),
					'key'   => 'footer-item-4',
					'open'  => true,
					'class' => 'text-center',
				),
			),
			'footer_item_4_show' => array(
				'title'           => __( 'Output', 'geodirectory' ),
				'type'            => 'select',
				'options'         => $show_options,
				'default'         => '',
				'desc_tip'        => true,
				'advanced'        => false,
				'group'           => __( 'Footer', 'geodirectory' ),
				'element_require' => '[%footer_items%] > 3',
				'row'             => array(
					'key'   => 'footer-item-4',
					'close' => true,

				),
			),
			'footer_item_5'      => array(
				'title'           => __( 'Type', 'geodirectory' ),
				'type'            => 'select',
				'options'         => $badge_options,
				'default'         => 'business_hours',
				'desc_tip'        => true,
				'advanced'        => false,
				'group'           => __( 'Footer', 'geodirectory' ),
				'element_require' => '[%footer_items%] > 4',
				'row'             => array(
					'title' => __( 'Item 5', 'geodirectory' ),
					'key'   => 'footer-item-5',
					'open'  => true,
					'class' => 'text-center',
				),
			),
			'footer_item_5_show' => array(
				'title'           => __( 'Output', 'geodirectory' ),
				'type'            => 'select',
				'options'         => $show_options,
				'default'         => '',
				'desc_tip'        => true,
				'advanced'        => false,
				'group'           => __( 'Footer', 'geodirectory' ),
				'element_require' => '[%footer_items%] > 4',
				'row'             => array(
					'key'   => 'footer-item-5',
					'close' => true,

				),
			),

		);

		$arguments['footer_bg_color'] = array(
			'title'           => __( 'Background color', 'geodirectory' ),
			'desc'            => __( 'Select the the background color.', 'geodirectory' ),
			'type'            => 'select',
			'options'         => array(
				'' => __( 'Default', 'geodirectory' ),
			) + geodir_aui_colors( false ),
			'default'         => '',
			'desc_tip'        => true,
			'advanced'        => false,
			'group'           => __( 'Footer', 'geodirectory' ),
			'element_require' => '[%footer_items%] > 0',
		);

		$arguments['footer_border'] = array(
			'title'    => __( 'Border top', 'geodirectory' ),
			'desc'     => __( 'Select the border color.', 'geodirectory' ),
			'type'     => 'select',
			'options'  => array(
				''     => __( 'Default', 'geodirectory' ),
				'none' => __( 'None', 'geodirectory' ),
			) + geodir_aui_colors( false ),
			'default'  => '',
			'desc_tip' => true,
			'advanced' => false,
			'group'    => __( 'Footer', 'geodirectory' ),
		);

		$arguments['footer_font_size'] = array(
			'title'    => __( 'Font size', 'geodirectory' ),
			'desc'     => __( 'Select the font size', 'geodirectory' ),
			'type'     => 'select',
			'options'  => array(
				''      => __( 'Default', 'geodirectory' ),
				'small' => __( 'Small', 'geodirectory' ),
			),
			'default'  => '',
			'desc_tip' => true,
			'advanced' => false,
			'group'    => __( 'Footer', 'geodirectory' ),
		);

		// footer padding
		$overwrite              = array(
			'group'           => __( 'Footer', 'geodirectory' ),
			'element_require' => '[%footer_items%] > 0',
		);
		$arguments['footer_pt'] = geodir_get_sd_padding_input( 'pt', $overwrite );
		$arguments['footer_pr'] = geodir_get_sd_padding_input( 'pr', $overwrite );
		$arguments['footer_pb'] = geodir_get_sd_padding_input( 'pb', $overwrite );
		$arguments['footer_pl'] = geodir_get_sd_padding_input( 'pl', $overwrite );

		return $arguments;
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
		global $aui_bs5, $gd_post, $post;

		$instance = wp_parse_args(
			(array) $instance,
			array(
				'title' => '',
			)
		);

		$is_preview = $this->is_preview();

		$content = '';

		if ( $is_preview ) {
			$is_archive_template = false;
			// Is archive item admin view
			if ( $post->ID == geodir_archive_item_page_id() || ! isset( $post->ID ) ) {
				$is_archive_template = true;
			} else {
				$post_types = geodir_get_posttypes( 'array' );
				foreach ( $post_types as $post_type => $post_type_arr ) {
					if ( ! empty( $post_type_arr['page_archive_item'] ) && $post->ID == $post_type_arr['page_archive_item'] ) {
						$is_archive_template = true;
					}
				}
			}

			if ( ! $is_archive_template ) {
				$is_preview = false;
			}
		}


		// card border class
		$card_class = '';
		if ( ! empty( $instance['card_border'] ) ) {
			if ( $instance['card_border'] == 'none' ) {
				$card_class .= ' border-0';
			} else {
				$card_class .= ' border-' . sanitize_html_class( $instance['card_border'] );
			}
		}

		// card shadow
		if ( ! empty( $instance['card_shadow'] ) ) {
			if ( $instance['card_shadow'] == 'small' ) {
				$card_class .= ' shadow-sm';
			} elseif ( $instance['card_shadow'] == 'medium' ) {
				$card_class .= ' shadow';
			} elseif ( $instance['card_shadow'] == 'large' ) {
				$card_class .= ' shadow-lg';
			}
		}


		$preview_type       = isset( $instance['preview_type'] ) ? esc_attr( $instance['preview_type'] ) : 'grid';
		$preview_type_class = $preview_type == 'list' ? 'row-cols-md-0' : 'row-cols-md-1';
		$preview_type_style = $preview_type == 'grid' ? 'style="max-width: 350px;"' : '';

		// preview wrapper open
		$content .= $is_preview ? '<div class="bsui"><div class="row row-cols-1 row-cols-sm-2 ' . $preview_type_class . ' " ><div class="col mx-auto " ' . $preview_type_style . '><div class="card p-0 mw-100 ' . esc_attr($card_class ) . '">' : '';

		// maybe card open
		if ( ! $is_preview ) {
			$content .= ! empty( $instance['card_wrap'] ) ? '<div class="card p-0 mw-100 h-100 ' . esc_attr($card_class ) . '">' : '';
		}

		// image
		if ( $instance['image_type'] != 'none' ) {

			// Open header
			$content .= $is_preview ? '<div class="card-img-top overflow-hidden position-relative " >' : "[gd_archive_item_section type='open' position='left']";

			// top left badge
			$content .= $instance['top_left_badge_preset'] == 'custom' ? $this->get_custom_badge( 'top_left', $instance ) : self::get_badge_type( $instance['top_left_badge_preset'], array( 'position' => 'top-left' ) );

			// top right badge
			$content .= $instance['top_right_badge_preset'] == 'custom' ? $this->get_custom_badge( 'top_right', $instance ) : self::get_badge_type( $instance['top_right_badge_preset'], array( 'position' => 'top-right' ) );

			// bottom left badge
			$content .= $instance['bottom_left_badge_preset'] == 'custom' ? $this->get_custom_badge( 'bottom_left', $instance ) : self::get_badge_type( $instance['bottom_left_badge_preset'], array( 'position' => 'bottom-left' ) );

			// bottom right badge
			$content .= $instance['bottom_right_badge_preset'] == 'custom' ? $this->get_custom_badge( 'bottom_right', $instance ) : self::get_badge_type( $instance['bottom_right_badge_preset'], array( 'position' => 'bottom-right' ) );

			$image_type = esc_attr( $instance['image_type'] );
			$image_link = $instance['image_link'] == 'none' ? '' : esc_attr( $instance['image_link'] );
			$content   .= "[gd_post_images type='$image_type' ajax_load='true' link_to='$image_link' types='logo,post_images' controlnav='0']";

			// Close header
			$content .= $is_preview ? '</div>' : "[gd_archive_item_section type='close' position='left']";
		}

		// Open Body
		// wrapper class
		$args       = array();
		$args['bg'] = esc_attr( $instance['body_bg_color'] );
		$args['pt'] = esc_attr( $instance['body_pt'] );
		$args['pr'] = esc_attr( $instance['body_pr'] );
		$args['pb'] = esc_attr( $instance['body_pb'] );
		$args['pl'] = esc_attr( $instance['body_pl'] );
		$wrap_class = geodir_build_aui_class( $args );
		$wrap_class = $wrap_class ? $wrap_class : 'p-2';
		$content   .= $is_preview ? '<div class="card-body ' . $wrap_class . '" >' : "[gd_archive_item_section type='open' position='right' bg='" . esc_attr( $instance['body_bg_color'] ) . "' pt='" . $args['pt'] . "' pr='" . $args['pr'] . "' pb='" . $args['pb'] . "' pl='" . $args['pl'] . "']";

		$circle_image_type = esc_attr( $instance['circle_image_type'] );
		if ( $circle_image_type != 'hide' ) {
			$circle_image_align = esc_attr( $instance['circle_image_align'] );
			$image_url          = '';
			$image_link         = '';
			$link_title         = '';
			$logo               = '';
			if ( $circle_image_type == 'author' || ( $circle_image_type == 'author_claimed' && ( $is_preview || ! empty( $gd_post->claimed ) ) ) ) {
				$author_id  = ! empty( $gd_post->post_author ) ? absint( $gd_post->post_author ) : 0;
				$image_link = $is_preview ? '#' : get_author_posts_url( $author_id );
				$image_url  = get_avatar_url( $author_id );
				$link_title = $is_preview ? 'User Name' : get_the_author_meta( 'nicename', $author_id );
			} elseif ( $circle_image_type == 'logo' ) {
				if ( $is_preview ) {
					$image_url = geodir_plugin_url() . '/assets/images/ayecode-logo.svg';
				} elseif ( ! empty( $gd_post->logo ) ) {
					$logo_image = geodir_get_images( $gd_post->ID, 1, 0, 0, array( 'logo' ) );
					if ( ! empty( $logo_image ) ) {
						//                      $logo = geodir_get_image_tag( $logo_image[0],'thumbnail','','rounded-circle shadow border border-white border-width-4 mt-n5 bg-white' );
						//                      $logo = str_replace( "<img ", '<img style="max-height:75px;" ', $logo );
						//                      $image_url = geodir_get_image_src($logo_image[0],'thumbnail');
						$image_url = geodir_get_image_src( $logo_image[0] );
					}

					//                  print_r( $logo_image );

					//                  $image_url = geodir_plugin_url() ."/assets/images/ayecode-logo.svg";
					$image_link = get_permalink();
					//                  $logo = "[gd_post_images title=''  id=''  types=''  fallback_types=''  ajax_load='true'  limit='1'  limit_show=''  css_class=''  type='image'  slideshow='false'  controlnav='1'  animation='slide'  show_title='false'  show_caption='false'  image_size='thumbnail'  aspect='1x1'  cover=''  link_to='post'  link_screenshot_to=''  bg=''  mt='n5'  mr=''  mb=''  ml=''  pt=''  pr=''  pb=''  pl=''  border=''  rounded=''  rounded_size=''  shadow='' ]";
				}
			}

			// align
			if ( $circle_image_align == 'left' ) {
				$author_image_class = 'start';} elseif ( $circle_image_align == 'right' ) {
				$author_image_class = 'end';} else {
					$author_image_class = 'center';}

				if ( $image_url || $logo ) {
					$content .= '<div class="row justify-content-' . $author_image_class . ' gv-hide-3 gv-hide-0">';
					$content .= '<div class="col col-4 text-center tofront ">';
					if ( $logo ) {
						$content .= $logo;
					} else {
						$content .= '<a href="' . esc_url( $image_link ) . '" title="' . esc_attr( $link_title ) . '"><img style="max-height:75px;" class="rounded-circle shadow border border-white border-width-4 p-0 mw-100 mt-n5 bg-white" src="' . esc_url( $image_url ) . '" alt="' . __( 'Author Image', 'geodirectory' ) . '"></a>';
					}
					$content .= '</div>';
					$content .= '</div>';
				}
		}

		// title
		$args                    = array();
		$args['pt']              = esc_attr( $instance['title_pt'] );
		$args['pb']              = esc_attr( $instance['title_pb'] );
		$args['font_size_class'] = esc_attr( $instance['title_font_size'] );
		$args['text_align']      = esc_attr( $instance['title_text_align'] );
		$args['text_color']      = esc_attr( $instance['title_text_color'] );
		$args_out                = '';
		foreach ( $args as $key => $val ) {
			$args_out .= " $key='$val'";
		}
		$content .= "[gd_post_title tag='h2' $args_out]";

		// content
		$args               = array();
		$args['pt']         = esc_attr( $instance['desc_pt'] );
		$args['pb']         = esc_attr( $instance['desc_pb'] );
		$args['text_color'] = esc_attr( $instance['desc_text_color'] );
		$args_out           = '';
		foreach ( $args as $key => $val ) {
			$args_out .= " $key='$val'";
		}
		$alignment = esc_attr( $instance['desc_text_align'] );
		//      $alignment = $alignment ? "text-$alignment" : '';
		$limit     = absint( $instance['limit'] );
		$read_more = esc_attr( $instance['read_more'] );
		$content  .= $limit !== 0 ? "[gd_post_content key='post_content' limit='$limit' read_more='$read_more' alignment='$alignment' $args_out]" : '';

		// Output location
		$args               = array();
		$args['pt']         = esc_attr( $instance['list_pt'] );
		$args['pb']         = esc_attr( $instance['list_pb'] );
		$args['text_align'] = esc_attr( $instance['list_text_align'] );
		$args_out           = '';
		foreach ( $args as $key => $val ) {
			$args_out .= " $key='$val'";
		}
		$list_style = esc_attr( $instance['list_style'] );
		$list_py    = esc_attr( $instance['item_py'] );
		$content   .= "[gd_output_location location='listing' list_style='$list_style' item_py='$list_py'  $args_out]";

		// Author Actions
		$content .= "[gd_author_actions author_page_only='1']";

		// Close Body
		$content .= $is_preview ? '</div>' : "[gd_archive_item_section type='close' position='right']";

		// Open Footer
		$footer_items = absint( $instance['footer_items'] );

		$footer_item_1 = $footer_items > 0 ? self::get_badge_type( $instance['footer_item_1'], array( 'show' => $instance['footer_item_1_show'] ) ) : '';
		$footer_item_2 = $footer_items > 1 ? self::get_badge_type( $instance['footer_item_2'], array( 'show' => $instance['footer_item_2_show'] ) ) : '';
		$footer_item_3 = $footer_items > 2 ? self::get_badge_type( $instance['footer_item_3'], array( 'show' => $instance['footer_item_3_show'] ) ) : '';
		$footer_item_4 = $footer_items > 3 ? self::get_badge_type( $instance['footer_item_4'], array( 'show' => $instance['footer_item_4_show'] ) ) : '';
		$footer_item_5 = $footer_items > 4 ? self::get_badge_type( $instance['footer_item_5'], array( 'show' => $instance['footer_item_5_show'] ) ) : '';

		if ( $footer_items ) {

			// wrapper class
			$args           = array();
			$args['bg']     = esc_attr( $instance['footer_bg_color'] );
			$args['pt']     = esc_attr( $instance['footer_pt'] );
			$args['pr']     = esc_attr( $instance['footer_pr'] );
			$args['pb']     = esc_attr( $instance['footer_pb'] );
			$args['pl']     = esc_attr( $instance['footer_pl'] );
			$args['border'] = esc_attr( $instance['footer_border'] );
			$wrap_class     = geodir_build_aui_class( $args );

			$wrap_class .= ' p-2';

			// border
			if ( ! empty( $instance['footer_border'] ) && $instance['footer_border'] != 'none' ) {
				$wrap_class .= ( $aui_bs5 ? 'border-start-0 border-end-0' : 'border-right-0 border-left-0' ) . ' border-bottom-0';
			}

			// font size
			$font_size = '';
			if ( ! empty( $instance['footer_font_size'] ) && $instance['footer_font_size'] == 'small' ) {
				$font_size   = 'font_size="small"';
				$wrap_class .= ' small';
			}

			$content .= $is_preview ? '<div class="card-footer ' . $wrap_class . '" >' : "[gd_archive_item_section type='open' position='footer' $font_size  border='" . esc_attr( $instance['footer_border'] ) . "' bg='" . esc_attr( $instance['footer_bg_color'] ) . "' pt='" . $args['pt'] . "' pr='" . $args['pr'] . "' pb='" . $args['pb'] . "' pl='" . $args['pl'] . "']";

			$content .= "<div class='d-flex justify-content-between align-items-center flex-wrap'>";
			$content .= $footer_item_1 ? '<div>' . $footer_item_1 . '</div>' : '';
			$content .= $footer_item_2 ? '<div>' . $footer_item_2 . '</div>' : '';
			$content .= $footer_item_3 ? '<div>' . $footer_item_3 . '</div>' : '';
			$content .= $footer_item_4 ? '<div>' . $footer_item_4 . '</div>' : '';
			$content .= $footer_item_5 ? '<div>' . $footer_item_5 . '</div>' : '';
			$content .= '</div>';

			// Close Footer
			$content .= $is_preview ? '</div>' : "[gd_archive_item_section type='close' position='footer']";
		}

		// close card wrap if set
		$content .= ! $is_preview && ! empty( $instance['card_wrap'] ) ? '</div>' : '';

		// preview wrapper close
		$content .= $is_preview ? '</div></div></div></div>' : '';

		/**
		 * Filter simple archive item hook before render.
		 *
		 * @since 2.3.15
		 *
		 * @param string $content Shortcode content.
		 * @param array  $args Widget parameters.
		 * @param array  $instance Widget instance.
		 * @param array  $widget_args Widget arguements.
		 * @param bool   $is_preview Current page is preview or not.
		 */
		$content = apply_filters( 'geodir_widget_simple_archive_item_shortcode', $content, $args, $instance, $widget_args, $is_preview );

		$content = do_shortcode( $content );

		return $content;
	}

	public function get_badge_options( $over_image = false ) {
		$badge_options = array(
			'none'     => __( 'None', 'geodirectory' ),
			'new'      => __( 'New', 'geodirectory' ),
			'featured' => __( 'Featured', 'geodirectory' ),
			'favorite' => __( 'Favorite', 'geodirectory' ),
			'category' => __( 'Category', 'geodirectory' ),
		//          'custom' => __( 'Custom', 'geodirectory' ),
		);

		if ( ! $over_image ) {
			$badge_options['rating']         = __( 'Rating', 'geodirectory' );
			$badge_options['business_hours'] = __( 'Business Hours', 'geodirectory' );

			$custom_fields = $this->get_custom_field_keys();

			if ( ! empty( $custom_fields ) ) {
				foreach ( $custom_fields as $key ) {
					$badge_options[ $key ] = $key;
				}
			}
		} else {
			$badge_options['custom'] = __( 'Custom', 'geodirectory' );
		}

		return $badge_options;
	}

	public function get_custom_badge( $position, $instance ) {

		$keys = array(
			'key',
			'condition',
			'search',
			'icon_class',
			'badge',
			'link',
			'type',
			'color',
			'bg_color',
			'txt_color',
		//          '',
		);
		$args = array();

		foreach ( $keys as $key ) {
			$args[ $key ] = isset( $instance[ $position . '_badge_' . $key ] ) ? esc_attr( $instance[ $position . '_badge_' . $key ] ) : '';
		}

		$a = array( 'position' => str_replace( '_', '-', $position ) );

		return $this->get_badge_type( 'custom_badge', $a, $args );

	}

	public function get_badge_type( $type, $args = array(), $badge_args = array() ) {
		global $aui_bs5;

		$type = esc_attr( $type );

		$output = '';

		// possition
		$position_args = '';
		$position      = ! empty( $args['position'] ) ? esc_attr( $args['position'] ) : '';
		$mb            = $type == 'favorite' ? 'n1' : '0';
		$mt            = $type == 'favorite' ? '0' : '1';
		if ( $position == 'top-left' ) {
			$position_args = " position='ab-top-left'  mt='$mt'  ml='1' ";
		} elseif ( $position == 'top-right' ) {
			$position_args = " position='ab-top-right'  mt='$mt'  mr='1' ";
		} elseif ( $position == 'bottom-left' ) {
			$position_args = " position='ab-bottom-left'  mt=''  mr=''  mb='$mb'  ml='1' ";
		} elseif ( $position == 'bottom-right' ) {
			$position_args = " position='ab-bottom-right'  mt=''  mr='1'  mb='$mb'  ml='' ";
		}

		// alignment
		$alignment = ! empty( $args['alignment'] ) ? esc_attr( $args['alignment'] ) : '';
		if ( $alignment ) {
			$alignment = '';//"alignment='".esc_attr($alignment)."'";
		}

		if ( $type == 'featured' ) {
			$output = "[gd_post_badge key='featured' condition='is_not_empty' badge='" . esc_attr( _x( 'FEATURED', 'featured badge', 'geodirectory' ) ) . "' bg_color='#fd4700' txt_color='#ffffff' css_class='' $alignment $position_args]";
		} elseif ( $type == 'new' ) {
			$output = "[gd_post_badge id=''  key='post_date'  condition='is_less_than'  search='+30'  icon_class=''  badge='" . esc_attr( _x( 'New', 'new badge', 'geodirectory' ) ) . "'  link=''  new_window='false'  popover_title=''  popover_text=''  cta=''  tooltip_text=''  hover_content=''  hover_icon=''  type=''  shadow=''  color=''  bg_color='#ff0000'  txt_color='#ffffff'  size=''  $alignment  mb=''  ml='' $position_args  list_hide=''  list_hide_secondary=''  css_class='' ]";
		} elseif ( $type == 'favorite' ) {
			$output = "[gd_post_fav show='icon'  icon=''  icon_color_off='rgba(223,223,223,0.8)'  icon_color_on='#ff0000'  type='link'  shadow=''  color=''  bg_color=''  txt_color=''  size='h5'  $alignment $position_args  list_hide=''  list_hide_secondary='' ]";
		} elseif ( $type == 'price' ) {
			$output = "[gd_post_badge id=''  key='price'  condition='is_not_empty'  search=''  icon_class=''  badge='%%input%% '  link='%%post_url%%'  new_window='false'  popover_title=''  popover_text=''  cta=''  tooltip_text=''  hover_content=''  hover_icon=''  type=''  shadow=''  color='danger'  bg_color='#0073aa'  txt_color='#ffffff'  size=''  $alignment $position_args  list_hide=''  list_hide_secondary=''  css_class='' ]";
		} elseif ( $type == 'category' ) {
			$output = "[gd_post_badge id=''  key='default_category'  condition='is_not_empty'  search=''  icon_class=''  badge='%%input%%'  link='%%input%%'  new_window='false'  popover_title=''  popover_text=''  cta='0'  tooltip_text=''  hover_content=''  hover_icon=''  type=''  shadow=''  color=''  bg_color='rgba(0,0,0,0.5)'  txt_color='#ffffff'  size=''  $alignment $position_args  list_hide=''  list_hide_secondary=''  css_class='' ]";
		} elseif ( $type == 'rating' ) {
			$output = "[gd_post_rating show='stars'  size=''  $alignment  list_hide=''  list_hide_secondary='' ]";
		} elseif ( $type == 'business_hours' ) {
			$lhs    = $this->is_preview() ? '1' : '2';
			$output = "[gd_post_meta title=''  id=''  key='business_hours'  show=''  no_wrap='false'  $alignment text_alignment=''  list_hide=''  list_hide_secondary='$lhs'  location=''  css_class='' ]";
		} elseif ( $type == 'custom_badge' ) {
			$args_out = '';
			if ( $badge_args ) {
				foreach ( $badge_args as $key => $val ) {
					$args_out .= " $key='$val'";
				}
			}

			$output = "[gd_post_badge $args_out $alignment $position_args]";
		} else {
			$lhs       = $this->is_preview() ? '1' : '2';
			$show      = ! empty( $args['show'] ) ? esc_attr( $args['show'] ) : '';
			$css_class = '';
			if ( $show == 'badge' ) {
				$show      = 'value';
				$css_class = 'badge ' . ( $aui_bs5 ? 'bg-primary' : 'badge-primary' );
			}
			$output = "[gd_post_meta title=''  id=''  key='$type'  show='$show'  no_wrap='false'  $alignment text_alignment=''  list_hide=''  list_hide_secondary='$lhs'  location=''  css_class='$css_class' ]";
		}

		return $output;
	}

	/**
	 * Gets an array of custom field keys.
	 *
	 * @return array
	 */
	public function get_custom_field_keys() {
		$fields = geodir_post_custom_fields( '', 'all', 'all', 'none' );
		$keys   = array();
		if ( ! empty( $fields ) ) {
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

	public function image_badge( $type = 'top_left' ) {

		if ( $type == 'top_left' ) {
			$title   = __( 'Top Left Badge', 'geodirectory' );
			$group   = __( 'Image Badge (top left)', 'geodirectory' );
			$default = 'featured';
		} elseif ( $type == 'top_right' ) {
			$title   = __( 'Top Right Badge', 'geodirectory' );
			$group   = __( 'Image Badge (top right)', 'geodirectory' );
			$default = 'new';
		} elseif ( $type == 'bottom_left' ) {
			$title   = __( 'Bottom Left Badge', 'geodirectory' );
			$group   = __( 'Image Badge (bottom left)', 'geodirectory' );
			$default = 'category';
		} elseif ( $type == 'bottom_right' ) {
			$title   = __( 'Bottom Right Badge', 'geodirectory' );
			$group   = __( 'Image Badge (bottom right)', 'geodirectory' );
			$default = 'favorite';
		} else {
			return array();
		}

		$badge                            = array();
		$badge[ $type . '_badge_preset' ] = array(
			'title'           => $title,
			'desc'            => __( 'Select the badge to show', 'geodirectory' ),
			'type'            => 'select',
			'options'         => $this->get_badge_options( true ),
			'default'         => $default,
			'desc_tip'        => true,
			'advanced'        => false,
			'group'           => $group,
			'element_require' => '[%image_type%]!="none"',
		);

		$badge[ $type . '_badge_key' ] = array(
			'type'            => 'select',
			'title'           => __( 'Field Key:', 'geodirectory' ),
			'desc'            => __( 'This is the custom field key.', 'geodirectory' ),
			'placeholder'     => '',
			'options'         => $this->get_custom_field_keys(),
			'default'         => '',
			'desc_tip'        => true,
			'advanced'        => false,
			'group'           => $group,
			'element_require' => '[%' . $type . '_badge_preset%]=="custom"',
		);

		$badge[ $type . '_badge_condition' ]  = array(
			'type'            => 'select',
			'title'           => __( 'Field condition:', 'geodirectory' ),
			'desc'            => __( 'Select the custom field condition.', 'geodirectory' ),
			'placeholder'     => '',
			'options'         => $this->get_badge_conditions(),
			'default'         => 'is_equal',
			'desc_tip'        => true,
			'advanced'        => false,
			'group'           => $group,
			'element_require' => '[%' . $type . '_badge_preset%]=="custom"',
		);
		$badge[ $type . '_badge_search' ]     = array(
			'type'            => 'text',
			'title'           => __( 'Value to match:', 'geodirectory' ),
			'desc'            => __( 'Match this text with field value to display post badge. For post date enter value like +7 or -7.', 'geodirectory' ),
			'placeholder'     => '',
			'default'         => '',
			'desc_tip'        => true,
			'advanced'        => false,
			'group'           => $group,
			'element_require' => '[%' . $type . '_badge_preset%]=="custom" && [%' . $type . '_badge_condition%]!="is_empty" && [%' . $type . '_badge_condition%]!="is_not_empty"',
		);
		$badge[ $type . '_badge_icon_class' ] = array(
			'type'            => 'text',
			'title'           => __( 'Icon class:', 'geodirectory' ),
			'desc'            => __( 'You can show a font-awesome icon here by entering the icon class.', 'geodirectory' ),
			'placeholder'     => 'fas fa-award',
			'default'         => '',
			'desc_tip'        => true,
			'group'           => $group,
			'element_require' => '[%' . $type . '_badge_preset%]=="custom"',
		);
		$badge[ $type . '_badge_badge' ]      = array(
			'type'            => 'text',
			'title'           => __( 'Badge:', 'geodirectory' ),
			'desc'            => __( 'Badge text. Ex: FOR SALE. Leave blank to show field title as a badge, or use %%input%% to use the input value of the field or %%post_url%% for the post url, or the field key for any other info %%email%%.', 'geodirectory' ),
			'placeholder'     => '',
			'default'         => '',
			'desc_tip'        => true,
			'advanced'        => false,
			'group'           => $group,
			'element_require' => '[%' . $type . '_badge_preset%]=="custom"',
		);
		$badge[ $type . '_badge_link' ]       = array(
			'type'            => 'text',
			'title'           => __( 'Link url:', 'geodirectory' ),
			'desc'            => __( 'Badge link url. You can use this to make the button link to something, %%input%% can be used here if a link or %%post_url%% for the post url.', 'geodirectory' ),
			'placeholder'     => '',
			'default'         => '',
			'desc_tip'        => true,
			'group'           => $group,
			'element_require' => '[%' . $type . '_badge_preset%]=="custom"',
		);

		$badge[ $type . '_badge_type' ]      = array(
			'title'           => __( 'Type', 'geodirectory' ),
			'desc'            => __( 'Select the badge type.', 'geodirectory' ),
			'type'            => 'select',
			'options'         => array(
				''     => __( 'Badge', 'geodirectory' ),
				'pill' => __( 'Pill', 'geodirectory' ),
			),
			'default'         => '',
			'desc_tip'        => true,
			'advanced'        => false,
			'group'           => $group,
			'element_require' => '[%' . $type . '_badge_preset%]=="custom"',
		);
		$badge[ $type . '_badge_color' ]     = array(
			'title'           => __( 'Badge Color', 'geodirectory' ),
			'desc'            => __( 'Select the the badge color.', 'geodirectory' ),
			'type'            => 'select',
			'options'         => array(
				'' => __( 'Custom colors', 'geodirectory' ),
			) + geodir_aui_colors( true ),
			'default'         => '',
			'desc_tip'        => true,
			'advanced'        => false,
			'group'           => $group,
			'element_require' => '[%' . $type . '_badge_preset%]=="custom"',
		);
		$badge[ $type . '_badge_bg_color' ]  = array(
			'type'            => 'color',
			'title'           => __( 'Badge background color:', 'geodirectory' ),
			'desc'            => __( 'Color for the badge background.', 'geodirectory' ),
			'placeholder'     => '',
			'default'         => '#0073aa',
			'desc_tip'        => true,
			'group'           => $group,
			'element_require' => '[%' . $type . '_badge_preset%]=="custom" && [%' . $type . '_badge_color%]==""',
		);
		$badge[ $type . '_badge_txt_color' ] = array(
			'type'            => 'color',
			'title'           => __( 'Badge text color:', 'geodirectory' ),
			'desc'            => __( 'Color for the badge text.', 'geodirectory' ),
			'placeholder'     => '',
			'desc_tip'        => true,
			'default'         => '#ffffff',
			'group'           => $group,
			'element_require' => '[%' . $type . '_badge_preset%]=="custom" && [%' . $type . '_badge_color%]==""',
		);

		return $badge;
	}

	/**
	 * Gets an array of badge field conditions.
	 *
	 * @return array
	 */
	public function get_badge_conditions() {
		$conditions = array(
			'is_equal'        => __( 'is equal', 'geodirectory' ),
			'is_not_equal'    => __( 'is not equal', 'geodirectory' ),
			'is_greater_than' => __( 'is greater than', 'geodirectory' ),
			'is_less_than'    => __( 'is less than', 'geodirectory' ),
			'is_empty'        => __( 'is empty', 'geodirectory' ),
			'is_not_empty'    => __( 'is not empty', 'geodirectory' ),
			'is_contains'     => __( 'is contains', 'geodirectory' ),
			'is_not_contains' => __( 'is not contains', 'geodirectory' ),
		);

		return apply_filters( 'geodir_badge_conditions', $conditions );
	}

}
