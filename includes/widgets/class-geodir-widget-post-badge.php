<?php

/**
 * GeoDir_Widget_Post_Badge class.
 *
 * @since 2.0.0
 */
class GeoDir_Widget_Post_Badge extends WP_Super_Duper {

	public $arguments;

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {

		$options = array(
			'textdomain'     => GEODIRECTORY_TEXTDOMAIN,
			'block-icon'     => 'fas fa-certificate',
			'block-wrap'    => '', // the element to wrap the block output in. , ie: div, span or empty for no wrap
			'block-supports'=> array(
				'customClassName'   => false
			),
			'block-category' => 'geodirectory',
			'block-keywords' => "['badge','geodir','geodirectory']",
			'class_name'     => __CLASS__,
			'base_id'        => 'gd_post_badge', // this us used as the widget id and the shortcode id.
			'name'           => __( 'GD > Post Badge', 'geodirectory' ), // the name of the widget.
			'no_wrap'       => true,
			'widget_ops'     => array(
				'classname'     => 'geodir-post-badge '.geodir_bsui_class(), // widget class
				'description'   => esc_html__( 'Displays the post badge.', 'geodirectory' ), // widget description
				'geodirectory'  => true,
			)
		);

		parent::__construct( $options );
	}

	/**
	 * Set widget arguments.
	 *
	 */
	public function set_arguments() {
		$design_style = geodir_design_style();

		$arguments = array();
		$arguments['id']  	= array(
				'type' => 'number',
				'title' => __('Post ID:', 'geodirectory'),
				'desc' => __('Leave blank to use current post id.', 'geodirectory'),
				'placeholder' => 'Leave blank to use current post id.',
				'default' => '',
				'desc_tip' => true,
				'advanced' => false
			);
		$arguments['key']  = array(
				'type' => 'select',
				'title' => __('Field Key:', 'geodirectory'),
				'desc' => __('This is the custom field key.', 'geodirectory'),
				'placeholder' => '',
				'options' => $this->get_custom_field_keys(),
				'default'  => '',
				'desc_tip' => true,
				'advanced' => false
		);
		$arguments['condition']  = array(
			'type' => 'select',
			'title' => __('Field condition:', 'geodirectory'),
			'desc' => __('Select the custom field condition.', 'geodirectory'),
			'placeholder' => '',
			'options' => $this->get_badge_conditions(),
			'default' => 'is_equal',
			'desc_tip' => true,
			'advanced' => false
		);
		$arguments['search']  = array(
			'type' => 'text',
			'title' => __('Value to match:', 'geodirectory'),
			'desc' => __('Match this text with field value to display post badge. For post date enter value like +7 or -7.', 'geodirectory'),
			'placeholder' => '',
			'default' => '',
			'desc_tip' => true,
			'advanced' => false,
			'element_require' => '[%condition%]!="is_empty" && [%condition%]!="is_not_empty"'
		);
		$arguments['icon_class']  = array(
			'type' => 'text',
			'title' => __('Icon class:', 'geodirectory'),
			'desc' => __('You can show a font-awesome icon here by entering the icon class.', 'geodirectory'),
			'placeholder' => 'fas fa-award',
			'default' => '',
			'desc_tip' => true,
			'group'     => __("Design","geodirectory")
		);
		$arguments['badge']  = array(
			'type' => 'text',
			'title' => __('Badge:', 'geodirectory'),
			'desc' => __('Badge text. Ex: FOR SALE. Leave blank to show field title as a badge, or use %%input%% to use the input value of the field or %%post_url%% for the post url, or the field key for any other info %%email%%.', 'geodirectory'),
			'placeholder' => '',
			'default' => '',
			'desc_tip' => true,
			'advanced' => false,
		);
		$arguments['link']  = array(
			'type' => 'text',
			'title' => __('Link url:', 'geodirectory'),
			'desc' => __('Badge link url. You can use this to make the button link to something, %%input%% can be used here if a link or %%post_url%% for the post url.', 'geodirectory'),
			'placeholder' => '',
			'default' => '',
			'desc_tip' => true,
			'group'     => __("Click Action","geodirectory")
		);
		$arguments['new_window']  = array(
			'title' => __('Open link in new window:', 'geodirectory'),
			'desc' => __('This will open the link in a new window.', 'geodirectory'),
			'type' => 'checkbox',
			'desc_tip' => true,
			'value'  => '1',
			'default'  => 0,
			'group'     => __("Click Action","geodirectory")
		);
		$arguments['popover_title']  = array(
			'type' => 'text',
			'title' => __('Popover title:', 'geodirectory'),
			'desc' => __('Reveals some title text onclick. Enter some text or use %%input%% to use the input value of the field or the field key for any other info %%email%%.', 'geodirectory'),
			'placeholder' => '',
			'default' => '',
			'desc_tip' => true,
			'group'     => __("Click Action","geodirectory")
		);
		$arguments['popover_text']  = array(
			'type' => 'text',
			'title' => __('Popover text:', 'geodirectory'),
			'desc' => __('Reveals some text onclick. Enter some text or use %%input%% to use the input value of the field or the field key for any other info %%email%%.', 'geodirectory'),
			'placeholder' => '',
			'default' => '',
			'desc_tip' => true,
			'group'     => __("Click Action","geodirectory")
		);
		$arguments['cta']  = array(
			'type' => 'text',
			'title' => __('Click through action:', 'geodirectory'),
			'desc' => __('This will attempt to send a Google Analytics custom event when clicked. By default this will use the field key, you can add your own such as `phone sidebar` or enter zero `0` to disable tracking.', 'geodirectory'),
			'placeholder' => 'phone',
			'default' => '',
			'desc_tip' => true,
			'group'     => __("Click Action","geodirectory")
		);
		$arguments['tooltip_text']  = array(
			'type' => 'text',
			'title' => __('Tooltip text:', 'geodirectory'),
			'desc' => __('Reveals some text on hover. Enter some text or use %%input%% to use the input value of the field or the field key for any other info %%email%%. (this can NOT be used with popover text)', 'geodirectory'),
			'placeholder' => '',
			'default' => '',
			'desc_tip' => true,
			'group'     => __("Hover Action","geodirectory")
		);
		$arguments['hover_content']  = array(
			'type' => 'text',
			'title' => __('Hover content:', 'geodirectory'),
			'desc' => __('Change the button text on hover. Enter some text or use %%input%% to use the input value of the field or the field key for any other info %%email%%.', 'geodirectory'),
			'placeholder' => '',
			'default' => '',
			'desc_tip' => true,
			'group'     => __("Hover Action","geodirectory")
		);
		$arguments['hover_icon']  = array(
			'type' => 'text',
			'title' => __('Hover icon:', 'geodirectory'),
			'desc' => __('Change the button icon on hover. You can show a font-awesome icon here by entering the icon class.', 'geodirectory'),
			'placeholder' => 'fas fa-bacon',
			'default' => '',
			'desc_tip' => true,
			'group'     => __("Hover Action","geodirectory")
		);

		if($design_style) {
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
		}

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
//			'disable_alpha'=> true,
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
				"block" => __('Block', 'geodirectory'),
			),
			'desc_tip' => true,
			'group'     => __("Positioning","geodirectory")
		);

		if($design_style) {
			$arguments['position'] = array(
				'type'     => 'select',
				'title'    => __( 'Absolute Positioning', 'geodirectory' ),
				'desc'     => __( 'Set an absolute position for floating badges over other content.', 'geodirectory' ),
				'options'  => array(
					""                      => __( 'None', 'geodirectory' ),
					"ab-left"               => __( 'Left', 'geodirectory' ),
					"ab-left-angle"         => __( 'Left angle', 'geodirectory' ),
					"ab-top-left"           => __( 'Top left', 'geodirectory' ),
					"ab-bottom-left"        => __( 'Bottom left', 'geodirectory' ),
					"ab-top-left-angle"     => __( 'Top left angle', 'geodirectory' ),
					"ab-bottom-left-angle"  => __( 'Bottom left angle', 'geodirectory' ),
					"ab-right"              => __( 'Right', 'geodirectory' ),
					"ab-right-angle"        => __( 'Right angle', 'geodirectory' ),
					"ab-top-right"          => __( 'Top right', 'geodirectory' ),
					"ab-bottom-right"       => __( 'Bottom right', 'geodirectory' ),
					"ab-top-right-angle"    => __( 'Top right angle', 'geodirectory' ),
					"ab-bottom-right-angle" => __( 'Bottom right angle', 'geodirectory' ),
				),
				'desc_tip' => true,
				'group'    => __( "Positioning", "geodirectory" )
			);

			$arguments['mt']  = geodir_get_sd_margin_input('mt');
			$arguments['mr']  = geodir_get_sd_margin_input('mr');
			$arguments['mb']  = geodir_get_sd_margin_input('mb');
			$arguments['ml']  = geodir_get_sd_margin_input('ml');
		}

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

		return $arguments;
	}

	/**
	 * Outputs the post badge on the front-end.
	 *
	 * @param array $args
	 * @param array $widget_args
	 * @param string $content
	 *
	 * @return mixed|string|void
	 */
	public function output( $args = array(), $widget_args = array(), $content = '' ) {
		global $aui_bs5, $post, $gd_post;

		// Default options
		$defaults = array(
			'type' => '',
			'shadow' => '',
			'color' => '',
			'position'  => '',
			'mt'    => '',
			'mb'    => '',
			'mr'    => '',
			'ml'    => '',
		);

		/**
		 * Parse incoming $args into an array and merge it with $defaults
		 */
		$args = wp_parse_args( $args, $defaults );

		if ( ! empty( $args['id'] ) ) {
			$post_id = absint( $args['id'] );
		} elseif ( ! empty( $post->ID ) ) {
			$post_id = $post->ID;
		} elseif ( ! empty( $gd_post->ID ) ) {
			$post_id = $gd_post->ID;
		} else {
			$post_id = 0;
		}

		$is_preview = $this->is_preview();
		if($is_preview){
			$args['key'] = ''; // if preview blank the key so it will always show.
			$args['preview'] = true; 
			if(empty($args['badge'])){$args['badge'] = 'Badge';}
		}

		$design_style = geodir_design_style();
		$block_preview = $this->is_block_content_call() || $is_preview;

		if(empty($gd_post->ID) && $block_preview && !empty($args['key'])){
			$post_id = geodir_get_post_id_with_content($args['key']);
		}

		$post_type 	= $post_id ? get_post_type( $post_id ) : '';

		$args['id'] = $post_id;

		// Errors.
		$errors = array();
		if(!$block_preview){
			if ( empty( $args['id'] ) ) {
				$errors[] = __('post id is missing','geodirectory');
			}
			if ( empty( $post_type ) ) {
				$errors[] = __('invalid post type','geodirectory');
			}
			if ( empty( $args['key'] ) ) {
				$errors[] = __('field key is missing', 'geodirectory');
			}
		}

		$output = '';
		if ( ! empty( $errors ) ){
			$output .= implode( ", ", $errors );
		}

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

		// Set positioning class
		if ( ! empty( $args['position'] ) ) {
			$args['css_class'] .= " " . sanitize_html_class( $args['position'] );
		}

		// margins
		if ( ! empty( $args['mt'] ) ) { $args['css_class'] .= " mt-" . sanitize_html_class( $args['mt'] ) . " "; }
		if ( ! empty( $args['mr'] ) ) { $args['css_class'] .= ( $aui_bs5 ? ' me-' : ' mr-' ) . sanitize_html_class( $args['mr'] ) . " "; }
		if ( ! empty( $args['mb'] ) ) { $args['css_class'] .= " mb-" . sanitize_html_class( $args['mb'] ) . " "; }
		if ( ! empty( $args['ml'] ) ) { $args['css_class'] .= ( $aui_bs5 ? ' ms-' : ' ml-' ) . sanitize_html_class( $args['ml'] ) . " "; }

		if(!empty($args['size'])){
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

		$output .= geodir_get_post_badge( $post_id, $args );

		return $output;
	}

	/**
	 * Gets an array of custom field keys for post badge.
	 *
	 * @return array
	 */
	public function get_custom_field_keys(){
		$fields = geodir_post_custom_fields( '', 'all', 'all', 'none' );
		$keys = array();
		if ( !empty( $fields ) ) {
			foreach( $fields as $field ) {
				if ( apply_filters( 'geodir_badge_field_skip_key', false, $field ) ) {
					continue;
				}
				$keys[ $field['htmlvar_name'] ] = $field['htmlvar_name'] . ' ( ' . __( $field['admin_title'], 'geodirectory' ) . ' )';

				// Extra address fields
				if ( $field['htmlvar_name'] == 'address' && ( $address_fields = geodir_post_meta_address_fields( '' ) ) ) {
					foreach ( $address_fields as $_field => $args ) {
						if ( $_field != 'map_directions' && $_field != 'street' ) {
							$keys[ $_field ] = $_field . ' ( ' . $args['frontend_title'] . ' )';
						}
					}
				}
			}
		}
		$keys['post_date'] = 'post_date ( ' . __( 'post date', 'geodirectory' ) . ' )';
		$keys['post_modified'] = 'post_modified ( ' . __( 'post modified', 'geodirectory' ) . ' )';
		$keys['default_category'] = 'default_category ( ' . __( 'Default Category', 'geodirectory' ) . ' )';
		$keys['post_id'] = 'post_id ( ' . __( 'post id', 'geodirectory' ) . ' )';
		$keys['post_status'] = 'post_status ( ' . __( 'Post Status', 'geodirectory' ) . ' )';

		return apply_filters( 'geodir_badge_field_keys', $keys );
	}

	/**
	 * Gets an array of badge field conditions.
	 *
	 * @return array
	 */
	public function get_badge_conditions(){
		$conditions = array(
			'is_equal' => __( 'is equal', 'geodirectory' ),
			'is_not_equal' => __( 'is not equal', 'geodirectory' ),
			'is_greater_than' => __( 'is greater than', 'geodirectory' ),
			'is_less_than' => __( 'is less than', 'geodirectory' ),
			'is_empty' => __( 'is empty', 'geodirectory' ),
			'is_not_empty' => __( 'is not empty', 'geodirectory' ),
			'is_contains' => __( 'is contains', 'geodirectory' ),
			'is_not_contains' => __( 'is not contains', 'geodirectory' ),
		);

		return apply_filters( 'geodir_badge_conditions', $conditions );
	}
}
