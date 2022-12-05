<?php
/**
 * Report Post widget.
 *
 * @package GeoDirectory
 * @since 2.1.1.12
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GeoDir_Widget_Report_Post class.
 */
class GeoDir_Widget_Report_Post extends WP_Super_Duper {

	public $arguments;

	/**
	 * Sets up a Report Post widget instance.
	 */
	public function __construct() {

		$options = array(
			'textdomain'     => GEODIRECTORY_TEXTDOMAIN,
			'block-icon'     => 'flag',
			'block-category' => 'geodirectory',
			'block-keywords' => "['geo','geodir','report']",
			'class_name'     => __CLASS__,
			'base_id'        => 'gd_report_post',
			'name'           => __( 'GD > Report Post', 'geodirectory' ),
			'widget_ops'     => array(
				'classname'     => 'geodir-report-post-container ' . geodir_bsui_class(),
				'description'   => esc_html__( 'Displays action to report post with inappropriate contents.', 'geodirectory' ),
				'geodirectory'  => true,
			)
		);

		parent::__construct( $options );
	}

	/**
	 * Set widget arguments.
	 */
	public function set_arguments() {
		$arguments = array(
			'text' => array(
				'type' => 'text',
				'title' => __( 'Text:', 'geodirectory' ),
				'desc' => __( 'The text shown to open the report form.', 'geodirectory' ),
				'placeholder' => __( 'Report Post', 'geodirectory' ),
				'desc_tip' => true,
				'advanced' => false
			),
			'output' => array(
				'type' => 'select',
				'title' => __( 'Output Type:', 'geodirectory' ),
				'desc' => __( 'Select how to show action to report post.', 'geodirectory' ),
				'options' => array(
					'button' => __( 'Button (lightbox)','geodirectory' ),
					'link' => __( 'Link (lightbox)', 'geodirectory' ),
					'form' => __( 'Form', 'geodirectory' ),
				),
				'default' => 'button',
				'desc_tip' => true,
				'advanced' => false
			)
		);

		$arguments['alignment'] = array(
			'type' => 'select',
			'title' => __( 'Alignment:', 'geodirectory' ),
			'desc' => __( 'How the item should be positioned on the page.', 'geodirectory' ),
			'options' => array(
				'' => __( 'None', 'geodirectory' ),
				'left' => __( 'Left', 'geodirectory' ),
				'center' => __( 'Center', 'geodirectory' ),
				'right' => __( 'Right', 'geodirectory' ),
			),
			'desc_tip' => true,
			'advanced' => false,
			'group' => __( 'Positioning', 'geodirectory' )
		);

		$arguments['output'] = array(
			'type' => 'select',
			'title' => __( 'Output Type:', 'geodirectory' ),
			'desc' => __( 'Select how to show action to report post.', 'geodirectory' ),
			'options' => array(
				'button' => __( 'Button', 'geodirectory' ),
				'badge' => __( 'Badge', 'geodirectory' ),
				'pill' => __( 'Pill', 'geodirectory' ),
				'link' => __( 'Link', 'geodirectory' ),
				'form' => __( 'Form', 'geodirectory' ),
			),
			'default' => 'button',
			'desc_tip' => true,
			'advanced' => false,
			'group' => __( 'Design', 'geodirectory' )
		);

		$arguments['shadow'] = array(
			 'type' => 'select',
			'title' => __( 'Shadow', 'geodirectory' ),
			'desc' => __( 'Select the shadow badge type.', 'geodirectory' ),
			'options' => array(
				'' => __( 'None', 'geodirectory' ),
				'small' => __( 'small', 'geodirectory' ),
				'medium' => __( 'medium', 'geodirectory' ),
				'large' => __( 'large', 'geodirectory' ),
			),
			'default' => '',
			'desc_tip' => true,
			'advanced' => false,
			'group' => __( 'Design', 'geodirectory' )
		);

		$arguments['color'] = array(
			'title' => __( 'Badge Color', 'geodirectory' ),
			'desc' => __( 'Select the the badge color.', 'geodirectory' ),
			'type' => 'select',
			'options' => array(
				'' => __( 'Custom colors', 'geodirectory' ),
			) + geodir_aui_colors( true, true, true ),
			'default' => '',
			'desc_tip' => true,
			'advanced' => false,
			'group' => __( 'Design', 'geodirectory' )
		);

		$arguments['bg_color'] = array(
			'type' => 'color',
			'title' => __( 'Badge Background Color:', 'geodirectory' ),
			'desc' => __( 'Color for the badge background.', 'geodirectory' ),
			'placeholder' => '',
			'default' => '#0073aa',
			'desc_tip' => true,
			'group' => __( 'Design', 'geodirectory' ),
			'element_require' => '[%color%]==""',
		);
		$arguments['txt_color'] = array(
			'type' => 'color',
			'title' => __( 'Badge Text Color:', 'geodirectory' ),
			'desc' => __( 'Color for the badge text.', 'geodirectory' ),
			'placeholder' => '',
			'desc_tip' => true,
			'default' => '#ffffff',
			'group' => __( 'Design', 'geodirectory' ),
			'element_require' => '[%color%]==""',
		);
		$arguments['size'] = array(
			'type' => 'select',
			'title' => __( 'Badge Size:', 'geodirectory' ),
			'desc' => __( 'Size of the badge.', 'geodirectory' ),
			'options' => array(
				'' => __( 'Default', 'geodirectory' ),
				'h6' => __( 'XS (badge)', 'geodirectory' ),
				'h5' => __( 'S (badge)', 'geodirectory' ),
				'h4' => __( 'M (badge)', 'geodirectory' ),
				'h3' => __( 'L (badge)', 'geodirectory' ),
				'h2' => __( 'XL (badge)', 'geodirectory' ),
				'h1' => __( 'XXL (badge)', 'geodirectory' ),
				'btn-lg' => __( 'Large (button)', 'geodirectory' ),
				'btn-sm' => __( 'Small (button)', 'geodirectory' ),
			),
			'default' => '',
			'desc_tip' => true,
			'group' => __( 'Design', 'geodirectory' )
		);

		$arguments['icon_class']  = array(
			'type' => 'text',
			'title' => __( 'Icon Class:', 'geodirectory' ),
			'desc' => __( 'You can show a font-awesome icon here by entering the icon class.', 'geodirectory' ),
			'placeholder' => 'Ex: fas fa-flag',
			'default' => '',
			'desc_tip' => true,
			'group' => __( 'Design', 'geodirectory' )
		);

		$arguments['mt'] = geodir_get_sd_margin_input( 'mt' );
		$arguments['mr'] = geodir_get_sd_margin_input( 'mr' );
		$arguments['mb'] = geodir_get_sd_margin_input( 'mb' );
		$arguments['ml'] = geodir_get_sd_margin_input( 'ml' );

		$arguments['css_class'] = array(
			'type' => 'text',
			'title' => __( 'Extra CSS class: ', 'geodirectory' ),
			'desc' => __( 'Give the wrapper an extra class so you can style things as you want.', 'geodirectory' ),
			'placeholder' => '',
			'default' => '',
			'desc_tip' => true,
			'advanced' => false,
			'group' => __( 'Design', 'geodirectory' )
		);

		return $arguments;
	}

	/**
	 * Outputs the report post on the front-end.
	 *
	 * @param array $instance Settings for the widget instance.
	 * @param array $args     Display arguments.
	 * @param string $content
	 *
	 * @return mixed|string|void
	 */
	public function output( $instance = array(), $args = array(), $content = '' ) {
		$html = $this->output_html( $instance, $args );

		return $html;
	}

	/**
	 * Output HTML.
	 *
	 * @param array $instance Settings for the widget instance.
	 * @param array $args     Display arguments.
	 * @return bool|string
	 */
	public function output_html( $instance = array(), $args = array() ) {
		global $aui_bs5, $gd_post;

		$is_preview = $this->is_preview();
		$block_preview = $this->is_block_content_call();

		if ( empty( $gd_post ) || empty( $gd_post->ID ) ) {
			return false;
		}

		if ( ! apply_filters( 'geodir_allow_report_post', true, $gd_post ) ) {
			return false;
		}

		$defaults = array(
			'text' => '',
			'output' => 'button',
			// AUI
			'alignment' => '',
			'shadow' => '',
			'color' => 'primary',
			'bg_color' => '',
			'txt_color' => '#ffffff',
			'size' => 'h5',
			'position' => '',
			'mt' => '',
			'mb' => '',
			'mr' => '',
			'ml' => '',
			'css_class' => '',
		);

		$instance = wp_parse_args( $instance, $defaults );

		if ( empty( $instance['text'] ) && empty( $instance['icon_class'] ) ) {
			$instance['text'] = __( 'Report Post', 'geodirectory' );
		}

		$instance['type'] = $instance['output'];
		$instance['badge'] = $instance['text'];

		$instance['text'] = apply_filters( 'geodir_report_post_widget_button_text', $instance['text'], $gd_post->ID, $instance );
		$instance['text'] = __( $instance['text'], 'geodirectory' );

		$wrap_class = '';
		$action_class = ' geodir-report-post-action';

		// Alignment
		if ( $instance['alignment'] != '' ) {
			if ( $instance['alignment'] == 'block' ) {
				$instance['css_class'] .= " d-block ";
			} elseif( $instance['alignment'] == 'left' ) {
				$instance['css_class'] .= ( $aui_bs5 ? ' float-start ms-2 ' : ' float-left mr-2 ' );
			} elseif ( $instance['alignment'] == 'right' ) {
				$instance['css_class'] .= ( $aui_bs5 ? ' float-end me-2 ' : ' float-right ml-2 ' );
			} elseif ( $instance['alignment'] == 'center' ) {
				$instance['css_class'] .= " text-center ";
			}
		}

		// Margins
		if ( ! empty( $instance['mt'] ) ) {
			$instance['css_class'] .= ' mt-' . sanitize_html_class( $instance['mt'] ) . ' ';
		}
		if ( ! empty( $instance['mr'] ) ) {
			$instance['css_class'] .= ( $aui_bs5 ? ' me-' : ' mr-' ) . sanitize_html_class( $instance['mr'] ) . ' ';
		}
		if ( ! empty( $instance['mb'] ) ) {
			$instance['css_class'] .= ' mb-' . sanitize_html_class( $instance['mb'] ) . ' ';
		}
		if ( ! empty( $instance['ml'] ) ) {
			$instance['css_class'] .= ( $aui_bs5 ? ' ms-' : ' ml-' ) . sanitize_html_class( $instance['ml'] ) . ' ';
		}

		// Size
		if ( ! empty( $instance['size'] ) ) {
			switch ( $instance['size'] ) {
				case 'small':
					$instance['size'] = '';
					break;
				case 'medium':
				case 'h4':
					$instance['size'] = 'h4';
					break;
				case 'large':
				case 'h2':
					$instance['size'] = 'h2';
					break;
				case 'extra-large':
				case 'h1':
					$instance['size'] = 'h1';
					break;
				case 'h6':
					$instance['size'] = 'h6';
					break;
				case 'h5':
					$instance['size'] = 'h5';
					break;
				case 'h3':
					$instance['size'] = 'h3';
					break;
				case 'btn-lg':
					$instance['size'] = '';
					$instance['css_class'] .= ' btn-lg ';
					break;
				case 'btn-sm':
					$instance['size'] = '';
					$instance['css_class'] .= ' btn-sm ';
					break;
				default:
					$instance['size'] = '';
			}
		}

		if ( $instance['output'] == 'form' ) {
			$output = GeoDir_Report_Post::get_form( $gd_post, true );
		} else {
			$instance['link'] = "javascript:void(0);";
			$instance['onclick'] = 'geodir_aui_ajax_modal(\'geodir_report_post_form\',\'\',' . absint( $gd_post->ID ) . '); return false;';

			$output = geodir_get_post_badge( $gd_post->ID, $instance );
		}

		return $output;
	}
}
