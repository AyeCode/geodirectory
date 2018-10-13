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
				'classname'   => 'geodir-post-meta-container', // widget class
				'description' => esc_html__('This shows a post single post meta.','geodirectory'), // widget description
				'customize_selective_refresh' => true,
				'geodirectory' => true,
				'gd_wgt_showhide' => 'show_on',
				'gd_wgt_restrict' => array( 'gd-detail' ), //@todo implement this on all other widgets
			),
			'arguments'     => array(
				'title'  => array(
					'title' => __('Title:', 'geodirectory'),
					'desc' => __('Extra main title if needed.', 'geodirectory'),
					'type' => 'text',
					'placeholder' => __( 'Extra main title if needed.', 'geodirectory' ),
					'default'  => '',
					'desc_tip' => true,
					'advanced' => true
				),
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
						"" => __('icon + label + value', 'geodirectory'),
						"icon-value" => __('icon + value', 'geodirectory'),
						"label-value" => __('label + value', 'geodirectory'),
						"label" => __('label', 'geodirectory'),
						"value" => __('value', 'geodirectory'),
						"value-strip" => __('value (strip_tags)', 'geodirectory'),
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
//				'location'  => array(
//					'name' => 'location',
//					'title' => __('Location key:', 'geodirectory'),
//					'desc' => __('Meta values can be filtered to show differently in some location types.', 'geodirectory'),
//					'type' => 'text',
//					'placeholder' => 'mapbubble',
//					'desc_tip' => true,
//					'default'  => '',
//					'advanced' => false
//				),
			)
		);


		parent::__construct( $options );


	}

	//gd_wgt_showhide

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

		global $post;

		$original_id = isset($args['id']) ? $args['id'] : '';
		$args['location'] = !empty($args['location']) ? $args['location'] : 'none';
		$output = '';
		$args = shortcode_atts( array(
			'id'    => $post->ID,
			'key'    => '', // the meta key : email
			'show'    => '', // title,value (default blank, all)
			'alignment'    => '', // left,right,center
			'location'  => 'none',
		), $args, 'gd_post_meta' );
		$args['id'] = !empty($args['id']) ? $args['id'] : $post->ID;

		$post_type = !$original_id && isset($post->post_type) ? $post->post_type : get_post_type($args['id']);


		// print_r($args);
		// error checks
		$errors = array();
		if(empty($args['key'])){$errors[] = __('key is missing','geodirectory');}
		if(empty($args['id'])){$errors[] = __('id is missing','geodirectory');}
		if(empty($post_type)){$errors[] = __('invalid post type','geodirectory');}

		if(!empty($errors)){
			$output .= implode(", ",$errors);
		}

		// check if its demo content
		if($post_type == 'page' && !empty($args['id']) && geodir_is_block_demo()){
			$post_type = 'gd_place';
		}

		if(geodir_is_gd_post_type($post_type)){ //echo '###2';
			$fields = geodir_post_custom_fields('',  'all', $post_type , 'none');

			if(!empty($fields)){
				$field = array();
				foreach($fields as $field_info){
					if($args['key']==$field_info['htmlvar_name']){
						$field = $field_info;
					}
				}
				if(!empty($field)){
					$field = stripslashes_deep( $field );
					//print_r($field );
					if($args['alignment']=='left'){$field['css_class'] .= " geodir-alignleft ";}
					if($args['alignment']=='center'){$field['css_class'] .= " geodir-aligncenter ";}
					if($args['alignment']=='right'){$field['css_class'] .= " geodir-alignright ";}
					$output = apply_filters("geodir_custom_field_output_{$field['type']}",'',$args['location'],$field,$args['id'],$args['show']);

					if($field['name']=='post_content'){
						//$output = wp_strip_all_tags($output);
					}

				}else{
					//$output = __('Key does not exist','geodirectory');
				}
			}
		}

		return $output;

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

