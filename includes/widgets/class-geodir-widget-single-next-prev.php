<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GeoDirectory Search widget.
 *
 * @since 1.0.0
 */
class GeoDir_Widget_Single_Next_Prev extends WP_Super_Duper {

	/**
	 * Register the advanced search widget with WordPress.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {


		$options = array(
			'textdomain'     => GEODIRECTORY_TEXTDOMAIN,
			'block-icon'     => 'admin-site',
			'block-category' => 'geodirectory',
			'block-keywords' => "['next','prev','geodir']",
			'class_name'     => __CLASS__,
			'base_id'        => 'gd_single_next_prev', // this us used as the widget id and the shortcode id.
			'name'           => __( 'GD > Single Next Prev', 'geodirectory' ), // the name of the widget.
			'widget_ops'     => array(
				'classname'    => 'geodir-single-taxonomies-container ' . geodir_bsui_class(),
				// widget class
				'description'  => esc_html__( 'Shows the current post`s next and previous post links on the details page.', 'geodirectory' ),
				// widget description
				'geodirectory' => true,
			),
		);

		$design_style = geodir_design_style();

		if ( $design_style ) {

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

			$options['arguments'] = $arguments;

		}


		parent::__construct( $options );
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
		$template     = $design_style ? $design_style . "/single/next-prev.php" : "legacy/single/next-prev.php";

		$is_preview = $this->is_preview();

		// wrap class.
		$wrap_class    = geodir_build_aui_class( $args );
		$template_args = array(
			'args'               => $args,
			'wrap_class'         => $wrap_class,
			'previous_post_link' => $is_preview ? '<a href="#">' . __( 'Previous', 'geodirectory' ) . '</a>' : get_previous_post_link( '%link', '' . __( 'Previous', 'geodirectory' ), false ),
			'next_post_link'     => $is_preview ? '<a href="#">' . __( 'Next', 'geodirectory' ) . '</a>' : get_next_post_link( '%link', __( 'Next', 'geodirectory' ) . '', false ),
		);

		return geodir_get_template_html( $template, $template_args );

	}

}
