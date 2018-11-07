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
				'title' => __('Feild Key:', 'geodirectory'),
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
			'badge'  => array(
				'type' => 'text',
				'title' => __('Badge:', 'geodirectory'),
				'desc' => __('Badge text. Ex: FOR SALE. Leave blank to show field title as a badge, or use %%input%% to use the input value of the field.', 'geodirectory'),
				'placeholder' => '',
				'default' => '',
				'desc_tip' => true,
				'advanced' => false
			),
			'link'  => array(
				'type' => 'text',
				'title' => __('Link url:', 'geodirectory'),
				'desc' => __('Badge link url. You can use this to make the button link to something, %%input%% can be used here if a link.', 'geodirectory'),
				'placeholder' => '',
				'default' => '',
				'desc_tip' => true,
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
			)
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
		global $post;

		$post_id 	= ! empty( $args['id'] ) ? $args['id'] : ( ! empty( $post->ID ) ? $post->ID : 0 );
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

		$output = geodir_get_post_badge( $post_id, $args );

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

