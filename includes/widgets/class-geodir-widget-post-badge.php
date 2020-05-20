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
			'block-icon'     => 'location-alt',
			'block-category' => 'common',
			'block-keywords' => "['badge','geodir','geodirectory']",
			'class_name'     => __CLASS__,
			'base_id'        => 'gd_post_badge',												// this us used as the widget id and the shortcode id.
			'name'           => __( 'GD > Post Badge', 'geodirectory' ),						// the name of the widget.
			'no_wrap'       => true,
			'widget_ops'     => array(
				'classname'     => 'geodir-post-badge',                                     	// widget class
				'description'   => esc_html__( 'Displays the post badge.', 'geodirectory' ),	// widget description
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
		$arguments = array(
			'id'  	=> array(
				'type' => 'number',
				'title' => __('Post ID:', 'geodirectory'),
				'desc' => __('Leave blank to use current post id.', 'geodirectory'),
				'placeholder' => 'Leave blank to use current post id.',
				'default' => '',
				'desc_tip' => true,
				'advanced' => false
			),
			'key'  => array(
				'type' => 'select',
				'title' => __('Field Key:', 'geodirectory'),
				'desc' => __('This is the custom field key.', 'geodirectory'),
				'placeholder' => '',
				'options' => $this->get_custom_field_keys(),
				'default'  => '',
				'desc_tip' => true,
				'advanced' => false
			),
			'condition'  => array(
				'type' => 'select',
				'title' => __('Field condition:', 'geodirectory'),
				'desc' => __('Select the custom field condition.', 'geodirectory'),
				'placeholder' => '',
				'options' => $this->get_badge_conditions(),
				'default' => 'is_equal',
				'desc_tip' => true,
				'advanced' => false
			),
			'search'  => array(
				'type' => 'text',
				'title' => __('Value to match:', 'geodirectory'),
				'desc' => __('Match this text with field value to display post badge. For post date enter value like +7 or -7.', 'geodirectory'),
				'placeholder' => '',
				'default' => '',
				'desc_tip' => true,
				'advanced' => false,
				'element_require' => '[%condition%]!="is_empty" && [%condition%]!="is_not_empty"'
			),
			'icon_class'  => array(
				'type' => 'text',
				'title' => __('Icon class:', 'geodirectory'),
				'desc' => __('You can show a font-awesome icon here by entering the icon class.', 'geodirectory'),
				'placeholder' => 'fas fa-award',
				'default' => '',
				'desc_tip' => true,
				'advanced' => true
			),
			'badge'  => array(
				'type' => 'text',
				'title' => __('Badge:', 'geodirectory'),
				'desc' => __('Badge text. Ex: FOR SALE. Leave blank to show field title as a badge, or use %%input%% to use the input value of the field or %%post_url%% for the post url, or the field key for any other info %%email%%.', 'geodirectory'),
				'placeholder' => '',
				'default' => '',
				'desc_tip' => true,
				'advanced' => false
			),
			'link'  => array(
				'type' => 'text',
				'title' => __('Link url:', 'geodirectory'),
				'desc' => __('Badge link url. You can use this to make the button link to something, %%input%% can be used here if a link or %%post_url%% for the post url.', 'geodirectory'),
				'placeholder' => '',
				'default' => '',
				'desc_tip' => true,
				'advanced' => true
			),
			'new_window'  => array(
				'title' => __('Open link in new window:', 'geodirectory'),
				'desc' => __('This will open the link in a new window.', 'geodirectory'),
				'type' => 'checkbox',
				'desc_tip' => true,
				'value'  => '1',
				'default'  => 0,
				'advanced' => true
			),
			'bg_color'  => array(
				'type' => 'color',
				'title' => __('Badge background color:', 'geodirectory'),
				'desc' => __('Color for the badge background.', 'geodirectory'),
				'placeholder' => '',
				'default' => '#0073aa',
				'desc_tip' => true,
				'advanced' => true
			),
			'txt_color'  => array(
				'type' => 'color',
				'title' => __('Badge text color:', 'geodirectory'),
				'desc' => __('Color for the badge text.', 'geodirectory'),
				'placeholder' => '',
				'desc_tip' => true,
				'default'  => '#ffffff',
				'advanced' => true
			),
			'size'  => array(
				'type' => 'select',
				'title' => __('Badge size:', 'geodirectory'),
				'desc' => __('Size of the badge.', 'geodirectory'),
				'options' =>  array(
					"small" => __('Small', 'geodirectory'),
					 "" => __('Normal', 'geodirectory'),
					"medium" => __('Medium', 'geodirectory'),
					"large" => __('Large', 'geodirectory'),
					"extra-large" => __('Extra Large', 'geodirectory'),
				),
				'default' => '',
				'desc_tip' => true,
				'advanced' => true
			),
			'alignment'  => array(
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
				'advanced' => true
			),
			'list_hide'  => array(
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
				'advanced' => true
			),
			'list_hide_secondary'  => array(
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
				'advanced' => true
			),
			'css_class'  => array(
				'type' => 'text',
				'title' => __('Extra class:', 'geodirectory'),
				'desc' => __('Give the wrapper an extra class so you can style things as you want.', 'geodirectory'),
				'placeholder' => '',
				'default' => '',
				'desc_tip' => true,
				'advanced' => true,
			),
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
		global $post, $gd_post;

		if ( ! empty( $args['id'] ) ) {
			$post_id = absint( $args['id'] );
		} elseif ( ! empty( $post->ID ) ) {
			$post_id = $post->ID;
		} elseif ( ! empty( $gd_post->ID ) ) {
			$post_id = $gd_post->ID;
		} else {
			$post_id = 0;
		}
		$post_type 	= $post_id ? get_post_type( $post_id ) : '';

		$args['id'] = $post_id;

		// Errors.
		$errors = array();
		if ( empty( $args['id'] ) ) {
			$errors[] = __('post id is missing','geodirectory');
		}
		if ( empty( $post_type ) ) {
			$errors[] = __('invalid post type','geodirectory');
		}
		if ( empty( $args['key'] ) ) {
			$errors[] = __('field key is missing', 'geodirectory');
		}

		$output = '';
		if ( ! empty( $errors ) ){
			$output .= implode( ", ", $errors );
		}

		// set list_hide class
		if($args['list_hide']=='2'){$args['css_class'] .= " gd-lv-2 ";}
		if($args['list_hide']=='3'){$args['css_class'] .= " gd-lv-3 ";}
		if($args['list_hide']=='4'){$args['css_class'] .= " gd-lv-4 ";}
		if($args['list_hide']=='5'){$args['css_class'] .= " gd-lv-5 ";}

		// set list_hide_secondary class
		if($args['list_hide_secondary']=='2'){$args['css_class'] .= " gd-lv-s-2 ";}
		if($args['list_hide_secondary']=='3'){$args['css_class'] .= " gd-lv-s-3 ";}
		if($args['list_hide_secondary']=='4'){$args['css_class'] .= " gd-lv-s-4 ";}
		if($args['list_hide_secondary']=='5'){$args['css_class'] .= " gd-lv-s-5 ";}

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
			}
		}
		$keys['post_date'] = 'post_date ( ' . __( 'post date', 'geodirectory' ) . ' )';
		$keys['post_modified'] = 'post_modified ( ' . __( 'post modified', 'geodirectory' ) . ' )';

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

