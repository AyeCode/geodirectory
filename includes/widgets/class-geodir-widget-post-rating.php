<?php
/**
 * GeoDirectory Detail Rating Stars Widget
 *
 * @since 2.0.0
 *
 * @package GeoDirectory
 */

/**
 * GeoDir_Widget_Post_Rating class.
 *
 * @since 2.0.0
 * @since 2.0.0.49 Added list_hide and list_hide_secondary options for more flexible designs.
 */
class GeoDir_Widget_Post_Rating extends WP_Super_Duper {

	public $arguments;

	public $post_rating = '';
	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {

		$options = array(
			'textdomain'       => GEODIRECTORY_TEXTDOMAIN,
			'block-icon'       => 'fas fa-star',
			'block-category'   => 'geodirectory',
			'block-keywords'   => "['rating','geo','geodir']",
			'block-supports'   => array(
				'customClassName' => false,
			),
			'class_name'       => __CLASS__,
			'base_id'          => 'gd_post_rating', // this us used as the widget id and the shortcode id.
			'name'             => __( 'GD > Post Rating', 'geodirectory' ), // the name of the widget.
			'widget_ops'       => array(
				'classname'                   => 'geodir-post-rating ' . geodir_bsui_class(), // widget class
				'description'                 => esc_html__( 'This shows a GD post rating stars.', 'geodirectory' ), // widget description
				'customize_selective_refresh' => true,
				'geodirectory'                => true,
				'gd_wgt_showhide'             => 'show_on',
				'gd_wgt_restrict'             => array( 'gd-detail' ),
			),
			'block_group_tabs' => array(
				'content'  => array(
					'groups' => array(
						__( 'Output', 'geodirectory' ),
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
						__( 'Grid Visibility', 'geodirectory' ),
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

		$arguments    = array();
		$design_style = geodir_design_style();

		$arguments['show'] = array(
			'name'     => 'show',
			'title'    => __( 'Style', 'geodirectory' ),
			'desc'     => __( 'What output style to show', 'geodirectory' ),
			'type'     => 'select',
			'options'  => array(
				''            => __( 'All', 'geodirectory' ),
				'stars'       => __( 'Stars only', 'geodirectory' ),
				'text'        => __( 'Text only', 'geodirectory' ),
				'short'       => __( 'Short with no count', 'geodirectory' ),
				'short-count' => __( 'Short with count', 'geodirectory' ),
			),
			'desc_tip' => true,
			'advanced' => false,
			'group'    => __( 'Output', 'geodirectory' ),
		);

		if ( $design_style ) {

			// font size
			$arguments['size'] = sd_get_font_size_input(
				'font_size',
				array(
					'title'    => __( 'Badge size:', 'geodirectory' ),
					'desc'     => __( 'Size of the badge.', 'geodirectory' ),
					'default'  => '',
					'desc_tip' => true,
					'group'    => __( 'Design', 'geodirectory' ),
				)
			);
		}

		$arguments['list_hide'] = array(
			'title'    => __( 'Hide item on view:', 'geodirectory' ),
			'desc'     => __( 'You can set at what view the item will become hidden.', 'geodirectory' ),
			'type'     => 'select',
			'options'  => array(
				''  => __( 'None', 'geodirectory' ),
				'2' => __( 'Grid view 2', 'geodirectory' ),
				'3' => __( 'Grid view 3', 'geodirectory' ),
				'4' => __( 'Grid view 4', 'geodirectory' ),
				'5' => __( 'Grid view 5', 'geodirectory' ),
			),
			'desc_tip' => true,
			'advanced' => false,
			'group'    => __( 'Grid Visibility', 'geodirectory' ),
		);

		$arguments['list_hide_secondary'] = array(
			'title'    => __( 'Hide secondary info on view', 'geodirectory' ),
			'desc'     => __( 'You can set at what view the secondary info such as label will become hidden.', 'geodirectory' ),
			'type'     => 'select',
			'options'  => array(
				''  => __( 'None', 'geodirectory' ),
				'2' => __( 'Grid view 2', 'geodirectory' ),
				'3' => __( 'Grid view 3', 'geodirectory' ),
				'4' => __( 'Grid view 4', 'geodirectory' ),
				'5' => __( 'Grid view 5', 'geodirectory' ),
			),
			'desc_tip' => true,
			'advanced' => false,
			'group'    => __( 'Grid Visibility', 'geodirectory' ),
		);

		//      $arguments = $arguments + sd_get_background_inputs( 'bg', array(), array(), array(), false );

		$arguments['alignment'] = array(
			'name'     => 'alignment',
			'title'    => __( 'Alignment:', 'geodirectory' ),
			'desc'     => __( 'How the item should be positioned on the page.', 'geodirectory' ),
			'type'     => 'select',
			'options'  => array(
				''       => __( 'None', 'geodirectory' ),
				'left'   => __( 'Left', 'geodirectory' ),
				'center' => __( 'Center', 'geodirectory' ),
				'right'  => __( 'Right', 'geodirectory' ),
			),
			'desc_tip' => true,
			'advanced' => false,
			'group'    => __( 'Wrapper Styles', 'geodirectory' ),
		);

		// margins mobile
		$arguments['mt'] = sd_get_margin_input( 'mt', array( 'device_type' => 'Mobile' ) );
		$arguments['mr'] = sd_get_margin_input( 'mr', array( 'device_type' => 'Mobile' ) );
		$arguments['mb'] = sd_get_margin_input( 'mb', array( 'device_type' => 'Mobile' ) );
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
		global $aui_bs5, $post, $gd_post;

		// Check comments are disabled.
		if ( ! geodir_is_block_demo() && ! ( ! empty( $gd_post ) && ! empty( $gd_post->post_type ) && GeoDir_Post_types::supports( $gd_post->post_type, 'comments' ) ) ) {
			return;
		}

		$defaults = array(
			'show'                => '', // stars, text
			'alignment'           => '', // left, center, right
			'list_hide'           => '',
			'list_hide_secondary' => '',
			'size'                => '',
		);

		/**
		 * Parse incoming $args into an array and merge it with $defaults
		 */
		$args = wp_parse_args( $args, $defaults );

		$design_style = geodir_design_style();

		$class = '';
		$main  = '';

		// set alignment class
		if ( '' !== $args['alignment'] ) {
			if ( $design_style ) {
				if ( 'block' === $args['alignment'] ) {
					$class .= ' d-block ';
				} elseif ( 'left' === $args['alignment'] ) {
					$class .= ' float-left mr-2 ';
				} elseif ( 'right' === $args['alignment'] ) {
					$class .= ' float-right ml-2 ';
				} elseif ( 'center' === $args['alignment'] ) {
					$class .= ' mw-100 d-block mx-auto text-center ';
				}
			} else {
				$class .= ' geodir-align' . sanitize_html_class( $args['alignment'] );
			}
		} elseif ( $design_style ) {
			$class .= ' clear-both ';
		}

		// size class
		if ( ! empty( $args['size'] ) ) {
			$class .= ' ' . sanitize_html_class( $args['size'] );
		}

		$design_style = geodir_design_style();

		// set list_hide class
		if ( '2' == $args['list_hide'] ) {
			$class .= $design_style ? ' gv-hide-2 ' : ' gd-lv-2 ';}
		if ( '3' == $args['list_hide'] ) {
			$class .= $design_style ? ' gv-hide-3 ' : ' gd-lv-3 ';}
		if ( '4' == $args['list_hide'] ) {
			$class .= $design_style ? ' gv-hide-4 ' : ' gd-lv-4 ';}
		if ( '5' == $args['list_hide'] ) {
			$class .= $design_style ? ' gv-hide-5 ' : ' gd-lv-5 ';}

		// set list_hide_secondary class
		if ( '2' == $args['list_hide_secondary'] ) {
			$class .= $design_style ? ' gv-hide-s-2 ' : ' gd-lv-s-2 ';}
		if ( '3' == $args['list_hide_secondary'] ) {
			$class .= $design_style ? ' gv-hide-s-3 ' : ' gd-lv-s-3 ';}
		if ( '4' == $args['list_hide_secondary'] ) {
			$class .= $design_style ? ' gv-hide-s-4 ' : ' gd-lv-s-4 ';}
		if ( '5' == $args['list_hide_secondary'] ) {
			$class .= $design_style ? ' gv-hide-s-5 ' : ' gd-lv-s-5 ';}

		do_action( 'geodir_post_rating_widget_content_before' );

		if ( 'stars' === $args['show'] ) {
			$main .= $this->get_rating_stars();
		} elseif ( 'text' === $args['show'] ) {
			$main .= $this->get_rating_text();
		} elseif ( 'short' === $args['show'] ) {
			$main .= $this->get_short_style( false );
		} elseif ( 'short-count' === $args['show'] ) {
			$main .= $this->get_short_style();
		} else {
			$main .= $this->get_rating_stars();
			$main .= $this->get_rating_text();
		}

		$post_rating = geodir_sanitize_float( $this->post_rating );
		if ( 0 == $post_rating ) {
			$class .= ' geodir-post-rating-value-0';
		} elseif ( $post_rating ) {
			$class .= ' geodir-post-rating-value-' . absint( $post_rating );
		}

		$wrap_class = sd_build_aui_class( $args );

		$before = '<div class="geodir_post_meta gd-rating-info-wrap ' . $class . ' ' . $wrap_class . '" data-rating="' . round( $post_rating, 1 ) . '">';
		$after  = '</div>';

		$content = $before . $main . $after;

		do_action( 'geodir_post_rating_widget_content_after' );

		return $content;
	}

	/**
	 * Get rating stars html.
	 *
	 * @since 2.0.0
	 *
	 * @return string Rating stars html.
	 */
	public function get_short_style( $show_count = true ) {
		global $post, $gd_post;

		if ( ! empty( $gd_post ) && ! empty( $gd_post->ID ) ) {
			$post_id = (int) $gd_post->ID;
			$post_type = $gd_post->post_type;
		} else if ( ! empty( $post ) && ! empty( $post->ID ) ) {
			$post_id = (int) $post->ID;
			$post_type = $post->post_type;
		} else {
			$post_id = 0;
			$post_type = '';
		}

		ob_start();
		?>
		<div class="gd-list-rating-stars d-inline-block">
			<?php
			// icon
			if ( ! empty( $post_type ) && geodir_cpt_has_rating_disabled( $post_type ) ) {
				$number = get_comments_number( $post_id );
				echo '<i class="fas fa-comments" aria-hidden="true"></i> ';
				echo '<span class="text-muted">(' . absint( $number ) . ')</span>';
			} else {
				if ( $this->is_preview() ) {
					$post_rating = '5';
					$number      = 123;
				} else {
					$post_rating = geodir_get_post_rating( $post_id );
					$number      = ! empty( $gd_post->rating_count ) ? $gd_post->rating_count : 0;
				}

				$this->post_rating = $post_rating;
				$rating_title = '';
				$icon_class   = esc_attr( geodir_get_option( 'rating_icon', 'fas fa-star' ) );
				$color        = $post_rating > 0 ? esc_attr( geodir_get_option( 'rating_color' ) ) : esc_attr( geodir_get_option( 'rating_color_off' ) );
				echo '<i class="' . esc_attr( $icon_class ) . ' me-1 mr-1 fa-lg" aria-hidden="true" ' . esc_attr( $rating_title ) . ' style="vertical-align: unset;color: ' . esc_attr( $color ) . '"></i>';
				echo '<b class="">' . esc_attr( number_format( $post_rating, 1 ) ) . '</b>';
				echo $show_count ? '<span class="text-muted ml-1 ms-1">(' . absint( $number ) . ')</span>' : '';
			}
			?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get rating stars html.
	 *
	 * @since 2.0.0
	 *
	 * @return string Rating stars html.
	 */
	public function get_rating_stars() {
		global $post, $gd_post;

		if ( ! empty( $gd_post ) && ! empty( $gd_post->ID ) ) {
			$post_id = (int) $gd_post->ID;
			$post_type = $gd_post->post_type;
		} else if ( ! empty( $post ) && ! empty( $post->ID ) ) {
			$post_id = (int) $post->ID;
			$post_type = $post->post_type;
		} else {
			$post_id = 0;
			$post_type = '';
		}

		ob_start();
		?>
		<div class="gd-list-rating-stars d-inline-block">
			<?php
			if ( ! empty( $post_type ) && geodir_cpt_has_rating_disabled( $post_type ) ) {
				echo '<i class="fas fa-comments" aria-hidden="true"></i>';
			} else {
				if ( geodir_is_block_demo() ) {
					$post_rating = '5';
				} elseif ( isset( $post->ID ) && ( geodir_details_page_id() == $post->ID || geodir_details_page_id( $post->post_type ) == $post->ID ) ) {
					$post_rating = '5';
				} else {
					$post_rating = geodir_get_post_rating( $post_id );
				}
				$this->post_rating = $post_rating;
				echo geodir_get_rating_stars( $post_rating, $post_id );
			}
			?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get rating text html.
	 *
	 * @since 2.0.0
	 *
	 * @return string rating text html.
	 */
	public function get_rating_text() {
		global $gd_post;
		ob_start();
		?>
		<span class="gd-list-rating-text d-inline-bloc gv-secondary">
			<a href="<?php comments_link(); ?>" class="gd-list-rating-link">
				<?php geodir_comments_number( $gd_post ); ?>
			</a>
		</span>
		<?php
		return ob_get_clean();
	}

}
