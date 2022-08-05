<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GeoDirectory Search widget.
 *
 * @since 1.0.0
 */
class GeoDir_Widget_Post_Distance extends WP_Super_Duper {

	/**
	 * Register the advanced search widget with WordPress.
	 *
	 */
	public function __construct() {

		$options = array(
			'textdomain'    => GEODIRECTORY_TEXTDOMAIN,
			'block-icon'    => 'admin-site',
			'block-category'=> 'geodirectory',
			'block-keywords'=> "['post','distance','geodir']",
			'class_name'    => __CLASS__,
			'base_id'       => 'gd_post_distance', // this us used as the widget id and the shortcode id.
			'name'          => __('GD > Distance To Post','geodirectory'), // the name of the widget.
			'widget_ops'    => array(
				'classname'   => 'geodir-post-distance ' . geodir_bsui_class(), // widget class
				'description' => esc_html__('Shows the distance do the current post.','geodirectory'), // widget description
				'geodirectory' => true,
			),
		);

		$design_style = geodir_design_style();

		if ( $design_style ) {
			$arguments['type'] = array(
				'title' => __('Type', 'geodirectory'),
				'desc' => __('Select the badge type.', 'geodirectory'),
				'type' => 'select',
				'options'   =>  array(
					"" => __('Badge', 'geodirectory'),
					"pill" => __('Pill', 'geodirectory'),
				),
				'default'  => '',
				'desc_tip' => true,
				'advanced' => false,
				'group'     => __("Design","geodirectory")
			);

			$arguments['shadow'] = array(
				'title' => __('Shadow', 'geodirectory'),
				'desc' => __('Select the shadow badge type.', 'geodirectory'),
				'type' => 'select',
				'options'   =>  array(
					"" => __('None', 'geodirectory'),
					"small" => __('small', 'geodirectory'),
					"medium" => __('medium', 'geodirectory'),
					"large" => __('large', 'geodirectory'),
				),
				'default'  => '',
				'desc_tip' => true,
				'advanced' => false,
				'group'     => __("Design","geodirectory")
			);

			$arguments['color'] = array(
				'title' => __('Badge Color', 'geodirectory'),
				'desc' => __('Select the the badge color.', 'geodirectory'),
				'type' => 'select',
				'options'   =>  array(
					                "" => __('Custom colors', 'geodirectory'),
				                )+geodir_aui_colors(true),
				'default'  => '',
				'desc_tip' => true,
				'advanced' => false,
				'group'     => __("Design","geodirectory")
			);
			$arguments['bg_color']  = array(
				'type' => 'color',
				'title' => __('Badge background color:', 'geodirectory'),
				'desc' => __('Color for the badge background.', 'geodirectory'),
				'placeholder' => '',
				'default' => '#0073aa',
				'desc_tip' => true,
				'group'     => __("Design","geodirectory"),
				'element_require' => $design_style ?  '[%color%]==""' : '',
			);
			$arguments['txt_color']  = array(
				'type' => 'color',
				'title' => __('Badge text color:', 'geodirectory'),
				'desc' => __('Color for the badge text.', 'geodirectory'),
				'placeholder' => '',
				'desc_tip' => true,
				'default'  => '#ffffff',
				'group'     => __("Design","geodirectory"),
				'element_require' => $design_style ?  '[%color%]==""' : '',
			);
			$arguments['size']  = array(
				'type' => 'select',
				'title' => __('Badge size:', 'geodirectory'),
				'desc' => __('Size of the badge.', 'geodirectory'),
				'options' =>  array(
					"" => __('h6', 'geodirectory'),
					"h5" => __('h5', 'geodirectory'),
					"h4" => __('h4', 'geodirectory'),
					"h3" => __('h3', 'geodirectory'),
					"h2" => __('h2', 'geodirectory'),
					"h1" => __('h1', 'geodirectory'),

				),
				'default' => '',
				'desc_tip' => true,
				'group'     => __("Design","geodirectory")
			);
			$arguments['alignment']  = array(
				'type' => 'select',
				'title' => __('Alignment:', 'geodirectory'),
				'desc' => __('How the item should be positioned on the page.', 'geodirectory'),
				'options'   =>  array(
					"" => __('None', 'geodirectory'),
					"left" => __('Left', 'geodirectory'),
					"center" => __('Center', 'geodirectory'),
					"right" => __('Right', 'geodirectory'),
				),
				'desc_tip' => true,
				'group'     => __("Design","geodirectory")
			);
			$arguments['list_hide']  = array(
				'title' => __('Hide item on view:', 'geodirectory'),
				'desc' => __('You can set at what view the item will become hidden.', 'geodirectory'),
				'type' => 'select',
				'options'   =>  array(
					"" => __('None', 'geodirectory'),
					"2" => __('Grid view 2', 'geodirectory'),
					"3" => __('Grid view 3', 'geodirectory'),
					"4" => __('Grid view 4', 'geodirectory'),
					"5" => __('Grid view 5', 'geodirectory'),
				),
				'desc_tip' => true,
				'group'     => __("Design","geodirectory")
			);
			$arguments['list_hide_secondary']  = array(
				'title' => __('Hide secondary info on view', 'geodirectory'),
				'desc' => __('You can set at what view the secondary info such as label will become hidden.', 'geodirectory'),
				'type' => 'select',
				'options'   =>  array(
					"" => __('None', 'geodirectory'),
					"2" => __('Grid view 2', 'geodirectory'),
					"3" => __('Grid view 3', 'geodirectory'),
					"4" => __('Grid view 4', 'geodirectory'),
					"5" => __('Grid view 5', 'geodirectory'),
				),
				'desc_tip' => true,
				'group'     => __("Design","geodirectory")
			);
			$arguments['css_class']  = array(
				'type' => 'text',
				'title' => __('Extra class:', 'geodirectory'),
				'desc' => __('Give the wrapper an extra class so you can style things as you want.', 'geodirectory'),
				'placeholder' => '',
				'default' => '',
				'desc_tip' => true,
				'group'     => __("Design","geodirectory")
			);

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
		global $post, $gd_post;

		$post_id = isset($gd_post->ID) ? $gd_post->ID : 0;
		$block_preview = $this->is_block_content_call();

		if ( ! $block_preview ) {
			if ( empty( $gd_post ) ) {
				return '';
			}
		}

		$design_style = geodir_design_style();

		if ( ! empty( $post ) && ! empty( $gd_post->ID ) && $post->ID == $gd_post->ID && isset( $post->distance ) ) {
			$gd_post->distance = $post->distance;
		}

		if ( ! isset( $gd_post->distance ) && ( ! $design_style || ! geodir_is_page( 'single' ) ) && ! $block_preview ) {
			return '';
		}

		$distance = isset( $gd_post->distance ) && geodir_sanitize_float( $gd_post->distance ) > 0 ? geodir_sanitize_float( $gd_post->distance ) : 0;
		$is_single = ( geodir_is_page( 'single' ) || ( ! empty( $_REQUEST['set_post'] ) && wp_doing_ajax() ) ) ? true : false;

		// Default options
		$defaults = array(
			'icon_class' => 'fas fa-road',
			'color' => '',
		);

		/**
		 * Parse incoming $args into an array and merge it with $defaults
		 */
		$args = wp_parse_args( $args, $defaults );

		// set defaults
		if ( empty( $args['icon_class'] ) ) {
			$args['icon_class'] = $defaults['icon_class'];
		}

		ob_start();

		if ( isset( $gd_post->latitude ) || ( $block_preview && $design_style ) ) {
			if ( $design_style ) {
				if ( $is_single ) {
					if ( ! $block_preview ) {
						if ( ! empty( $gd_post->gps_latitude ) && ! empty( $gd_post->gps_longitude ) && isset( $gd_post->distance ) ) {
							$distance = geodir_sanitize_float( $gd_post->distance );
						} else {
							$distance_unit = geodir_get_option( 'search_distance_long' );
							$main_post = ( ! empty( $_REQUEST['set_post'] ) && wp_doing_ajax() ) ? absint( $_REQUEST['set_post'] ) : (int) get_queried_object_id();

							$point1 = array(
								'latitude'  => $gd_post->latitude,
								'longitude'  => $gd_post->longitude,
							);

							if ( $main_post && GeoDir_Post_types::supports( get_post_type( $main_post ), 'location' ) ) {
								$point2 = array(
									'latitude'  => geodir_get_post_meta( $main_post,'latitude', true ),
									'longitude'  => geodir_get_post_meta( $main_post,'longitude', true ),
								);
							} else {
								$point2 = array();
							}

							if ( empty( $point2['latitude'] ) ) {
								ob_get_clean();
								return '';
							}

							$distance = geodir_sanitize_float( geodir_calculateDistanceFromLatLong( $point1, $point2, $distance_unit ) );
						}

						if ( ! $distance > 0 ) {
							$distance = 0;
						}
					} else {
						$distance = 1.23;
					}

					$args['onclick'] = $block_preview ? '' : "gd_set_get_directions('" . esc_attr( $gd_post->latitude ) . "','" . esc_attr( $gd_post->longitude ) . "');";
					$args['link'] = '#post_map';
					$args['badge'] = $distance;
					$args['icon_class'] = 'fas fa-arrows-alt-h';
					$args['tooltip_text'] = __( "Distance from the current listing, click for directions.", "geodirectory" );
				} else {
					$args['link'] = $block_preview ? '#link_to_directions' : 'https://maps.google.com/?daddr=' . esc_attr( $gd_post->latitude ) . ',' . esc_attr( $gd_post->longitude );
					$args['tooltip_text'] = __( "View Directions on Google Map", "geodirectory" );
					$args['new_window'] = true;

					if ( $block_preview ) {
						$distance = 1.23;
					}
				}

				$args['badge'] = geodir_show_distance( $distance );

				// set list_hide class
				if($args['list_hide']=='2'){$args['css_class'] .= $design_style ? " gv-hide-2 " : " gd-lv-2 ";}
				if($args['list_hide']=='3'){$args['css_class'] .= $design_style ? " gv-hide-3 " : " gd-lv-3 ";}
				if($args['list_hide']=='4'){$args['css_class'] .= $design_style ? " gv-hide-4 " : " gd-lv-4 ";}
				if($args['list_hide']=='5'){$args['css_class'] .= $design_style ? " gv-hide-5 " : " gd-lv-5 ";}

				// set list_hide_secondary class
				if($args['list_hide_secondary']=='2'){$args['css_class'] .= $design_style ? " gv-hide-s-2 " : " gd-lv-s-2 ";}
				if($args['list_hide_secondary']=='3'){$args['css_class'] .= $design_style ? " gv-hide-s-3 " : " gd-lv-s-3 ";}
				if($args['list_hide_secondary']=='4'){$args['css_class'] .= $design_style ? " gv-hide-s-4 " : " gd-lv-s-4 ";}
				if($args['list_hide_secondary']=='5'){$args['css_class'] .= $design_style ? " gv-hide-s-5 " : " gd-lv-s-5 ";}

				if ( ! empty( $args['size'] ) ) {
					switch ($args['size']) {
						case 'small':
							$args['size'] = $design_style ? '' : 'small';
							break;
						case 'medium':
							$args['size'] = $design_style ? 'h4' : 'medium';
							break;
						case 'large':
							$args['size'] = $design_style ? 'h2' : 'large';
							break;
						case 'extra-large':
							$args['size'] = $design_style ? 'h1' : 'extra-large';
							break;
						case 'h6': $args['size'] = 'h6';break;
						case 'h5': $args['size'] = 'h5';break;
						case 'h4': $args['size'] = 'h4';break;
						case 'h3': $args['size'] = 'h3';break;
						case 'h2': $args['size'] = 'h2';break;
						case 'h1': $args['size'] = 'h1';break;
						default:
							$args['size'] = '';
					}
				}

				echo geodir_get_post_badge( $gd_post->ID, $args );
			} else {
				if ( $is_single ) {
					?>
					<a href="#post_map" onclick="gd_set_get_directions('<?php echo esc_attr( $gd_post->latitude ); ?>','<?php echo esc_attr( $gd_post->longitude ); ?>');">
					<?php
				}
				?>
				<span class="geodir_post_meta_icon geodir-i-distance" style=""><i class="fas fa-road" aria-hidden="true"></i> <?php echo geodir_show_distance( $distance ); ?></span>
				<?php if ( $is_single ) { ?>
				</a>
				<?php
				}
			}
		}

		$content = ob_get_clean();

		if ( ! empty( $content ) && ! empty( $gd_post ) ) {
			$content = geodir_post_address( $content, 'gd_post_distance', $gd_post );
		}

		return apply_filters( 'geodir_post_distance_content', $content, $gd_post );
	}

}