<?php

/**
 * GeoDir_Widget_Detail_Meta class.
 *
 * @since 2.0.0
 */
class GeoDir_Widget_Post_Meta extends WP_Super_Duper {


	public $arguments;
	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {

		$options = array(
			'textdomain'    => GEODIRECTORY_TEXTDOMAIN,
			'block-icon'    => 'location-alt',
			'block-category'=> 'common',
			'block-keywords'=> "['geo','geodirectory','geodir']",
			'class_name'    => __CLASS__,
			'base_id'       => 'gd_post_meta', // this us used as the widget id and the shortcode id.
			'name'          => __('GD > Post Meta','geodirectory'), // the name of the widget.
			'widget_ops'    => array(
				'classname'   => 'geodir-post-meta', // widget class
				'description' => esc_html__('This shows a post single post meta.','geodirectory'), // widget description
				'customize_selective_refresh' => true,
				'geodirectory' => true,
				'gd_show_pages' => array( 'detail' ),
			),
			'arguments'     => array(
				'id'  => array(
					'name' => 'id',
					'title' => __('Post ID:', 'geodirectory'),
					'desc' => __('Leave blank to use current post id.', 'geodirectory'),
					'type' => 'number',
					'placeholder' => 'Leave blank to use current post id.',
					'desc_tip' => true,
					'default'  => '',
					'advanced' => false
				),
				'key'  => array(
					'name' => 'key',
					'title' => __('Key:', 'geodirectory'),
					'desc' => __('This is the custom field key.', 'geodirectory'),
					'type' => 'select',
					'placeholder' => 'website',
					'options'   => $this->get_custom_field_keys(),
					'desc_tip' => true,
					'default'  => '',
					'advanced' => false
				),
				'show'  => array(
					'name' => 'show',
					'title' => __('Show:', 'geodirectory'),
					'desc' => __('What part of the post meta to show.', 'geodirectory'),
					'type' => 'select',
					'options'   =>  array(
						"all" => __('All', 'geodirectory'),
						"title" => __('Title', 'geodirectory'),
						"value" => __('Value', 'geodirectory'),
					),
					'desc_tip' => true,
					'advanced' => false
				),
				'alignment'  => array(
					'name' => 'alignment',
					'title' => __('Alignment:', 'geodirectory'),
					'desc' => __('How the item should be positioned on the page.', 'geodirectory'),
					'type' => 'select',
					'options'   =>  array(
						"" => __('None', 'geodirectory'),
						"left" => __('Left', 'geodirectory'),
						"center" => __('Center', 'geodirectory'),
						"right" => __('Right', 'geodirectory'),
					),
					'desc_tip' => true,
					'advanced' => false
				),
				'location'  => array(
					'name' => 'location',
					'title' => __('Location key:', 'geodirectory'),
					'desc' => __('Meta values can be filtered to show differently in some location types.', 'geodirectory'),
					'type' => 'text',
					'placeholder' => 'mapbubble',
					'desc_tip' => true,
					'default'  => '',
					'advanced' => false
				)
			)
		);


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
	public function output($args = array(), $widget_args = array(),$content = ''){

		/**
		 * @var int    $ID Optional. The current post ID if empty.
		 * @var string $key The meta key : email
		 * @var string $show Optional. What to show, 'title','value' or 'all'. Default 'all'.
		 * @var string $align left,right,center or blank.. Default ''
		 * @var string $location The show in what location key. Default 'none'
		 */
		extract($args, EXTR_SKIP);

		return geodir_sc_single_meta( $args, $content = '' );

	}

	/**
	 * Gets an array of custom field keys.
	 *
	 * @return array
	 */
	public function get_custom_field_keys(){
		$fields = geodir_post_custom_fields('', 'all', 'all','none');
		$keys = array();
		$keys[] = __('Select Key','geodirectory');
		if(!empty($fields)){
			foreach($fields as $field){
				$keys[$field['htmlvar_name']] = $field['htmlvar_name'];
			}
		}

		return $keys;

	}
	
}

