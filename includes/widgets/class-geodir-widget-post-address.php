<?php

/**
 * GeoDir_Widget_Post_Address class.
 *
 * @since 2.0.0
 */
class GeoDir_Widget_Post_Address extends WP_Super_Duper {


	public $arguments;
	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {

		$options = array(
			'textdomain'    => GEODIRECTORY_TEXTDOMAIN,
			'block-icon'    => 'admin-home',
			'block-category'=> 'geodirectory',
			'block-keywords'=> "['geo','address','location']",
			'class_name'    => __CLASS__,
			'base_id'       => 'gd_post_address', // this us used as the widget id and the shortcode id.
			'name'          => __('GD > Post Address','geodirectory'), // the name of the widget.
			'widget_ops'    => array(
				'classname'   => 'geodir-post-address-container '.geodir_bsui_class(), // widget class
				'description' => esc_html__('This shows the post address formatted as required.','geodirectory'), // widget description
				'customize_selective_refresh' => true,
				'geodirectory' => true,
				'gd_wgt_showhide' => 'show_on',
				'gd_wgt_restrict' => array( 'gd-detail' ),
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
					'title' => __('Post ID:', 'geodirectory'),
					'desc' => __('Leave blank to use current post id.', 'geodirectory'),
					'type' => 'number',
					'placeholder' => 'Leave blank to use current post id.',
					'desc_tip' => true,
					'default'  => '',
					'advanced' => false
				),
				'show'  => array(
					'title' => __('Show:', 'geodirectory'),
					'desc' => __('What part of the post meta to show.', 'geodirectory'),
					'type' => 'select',
					'options'   =>  array(
						"icon-label-value" => __('icon + label + value', 'geodirectory'),
						"icon-value" => __('icon + value', 'geodirectory'),
						"label-value" => __('label + value', 'geodirectory'),
						"label" => __('label', 'geodirectory'),
						"value" => __('value', 'geodirectory'),
					),
					'default'  => '',
					'desc_tip' => true,
					'advanced' => false
				),
				'address_template'  => array(
					'title' => __('Address template:', 'geodirectory'),
					'desc' => sprintf( __('Enter the address tags as required, adding _br or _brc to the tag adds a line break or comma and line break after it. Available tags: %s', 'geodirectory'),'%%street%% %%neighbourhood%% %%city%% %%region%% %%country%% %%zip%% %%latitude%% %%longitude%% %%post_title%% %%br%'),
					'type' => 'text',
					'placeholder' => '%%street_br%% %%neighbourhood_br%% %%city_br%% %%region_br%% %%zip_br%% %%country%%',
					'default'  => '',
					'desc_tip' => true,
					'advanced' => false
				),
				'map_link' => array(
					'type' => 'checkbox',
					'title' => __( 'Link to show address on Google Map:', 'geodirectory' ),
					'value' => '1',
					'default' => '',
					'element_require' => '[%show%]!="label"',
					'desc_tip' => false,
					'advanced' => true
				),
				'alignment'  => array(
					'title' => __('Alignment:', 'geodirectory'),
					'desc' => __('How the item should be positioned on the page.', 'geodirectory'),
					'type' => 'select',
					'options'   =>  array(
						"" => __('None', 'geodirectory'),
						"block" => __('Block', 'geodirectory'),
						"left" => __('Left', 'geodirectory'),
						"center" => __('Center', 'geodirectory'),
						"right" => __('Right', 'geodirectory'),
					),
					'desc_tip' => true,
					'advanced' => false
				),
				'text_alignment'  => array(
					'title' => __('Text Align:', 'geodirectory'),
					'desc' => __('How the text should be aligned.', 'geodirectory'),
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

		global $post,$gd_post;

		$original_id = isset($args['id']) ? $args['id'] : '';
		$args['location'] = !empty($args['location']) ? $args['location'] : 'none';
		$output = '';
		$args = shortcode_atts( array(
			'id'    => isset($gd_post->ID) ? $gd_post->ID : 0,
			'key'   => 'address',
			'show'    => 'icon-label-value', // title,value (default blank, all)
			'alignment'    => '', // left,right,center
			'text_alignment'    => '', // left,right,center
			'list_hide'    => '',
			'list_hide_secondary'    => '',
			'address_template' => '%%street_br%% %%neighbourhood_br%% %%city_br%% %%region_br%% %%zip_br%% %%country%%',
			'map_link' => '',
			'location'  => 'none',
		), $args, 'gd_post_meta' );
		if(empty($args['id'])){
			$args['id'] =  isset($gd_post->ID) ? $gd_post->ID : 0;
		}

		$design_style = geodir_design_style();
		$block_preview = $this->is_block_content_call();

		$post_type = !$original_id && isset($post->post_type) ? $post->post_type : get_post_type($args['id']);
		if($block_preview){$post_type = 'gd_place';}

		// error checks
		$errors = array();
		if(!$block_preview){
			if(empty($args['key'])){$errors[] = __('key is missing','geodirectory');}
			if(empty($args['id'])){$errors[] = __('id is missing','geodirectory');}
			if(empty($post_type)){$errors[] = __('invalid post type','geodirectory');}
		}


		if ( ! empty( $errors ) ) {
			$output .= implode( ", ", $errors );
		}

		if ( class_exists( 'FLBuilder' ) && isset( $_REQUEST['fl_builder'] ) ) {
			$output = ''; // Show placehoder on beaver builder preview.
		}

		// check if its demo content
		if($post_type == 'page' && !empty($args['id']) && geodir_is_block_demo()){
			$post_type = 'gd_place';
		}

		if(geodir_is_gd_post_type($post_type)){

			$package_id = geodir_get_post_package_id( $args['id'], $post_type );
			$fields = geodir_post_custom_fields($package_id ,  'all', $post_type);

			if(!empty($fields)){
				$field = array();
				foreach($fields as $field_info){
					if($args['key']==$field_info['htmlvar_name']){
						$field = $field_info;
					}
				}
				if(!empty($field)){ // the field is allowed to be shown
					$field = stripslashes_deep( $field );

					// set text alignment class
					if ( $args['text_alignment'] != '' ) {
						$field['css_class'] .= $design_style ? " text-".sanitize_html_class( $args['text_alignment'] ) : " geodir-text-align" . sanitize_html_class( $args['text_alignment'] );
					}

					// set alignment class
					if ( $args['alignment'] != '' ) {
						if($design_style){
							if($args['alignment']=='block'){$field['css_class'] .= " d-block ";}
							elseif($args['alignment']=='left'){$field['css_class'] .= " float-left mr-2 ";}
							elseif($args['alignment']=='right'){$field['css_class'] .= " float-right ml-2 ";}
							elseif($args['alignment']=='center'){$field['css_class'] .= " mw-100 d-block mx-auto ";}
						}else{
							$field['css_class'] .= $args['alignment']=='block' ? " gd-d-block gd-clear-both " : " geodir-align" . sanitize_html_class( $args['alignment'] );
						}
					}

					// set list_hide class
					if($args['list_hide']=='2'){$field['css_class'] .= $design_style ? " gv-hide-2 " : " gd-lv-2 ";}
					if($args['list_hide']=='3'){$field['css_class'] .= $design_style ? " gv-hide-3 " : " gd-lv-3 ";}
					if($args['list_hide']=='4'){$field['css_class'] .= $design_style ? " gv-hide-4 " : " gd-lv-4 ";}
					if($args['list_hide']=='5'){$field['css_class'] .= $design_style ? " gv-hide-5 " : " gd-lv-5 ";}

					// set list_hide_secondary class
					if($args['list_hide_secondary']=='2'){$field['css_class'] .= $design_style ? " gv-hide-s-2 " : " gd-lv-s-2 ";}
					if($args['list_hide_secondary']=='3'){$field['css_class'] .= $design_style ? " gv-hide-s-3 " : " gd-lv-s-3 ";}
					if($args['list_hide_secondary']=='4'){$field['css_class'] .= $design_style ? " gv-hide-s-4 " : " gd-lv-s-4 ";}
					if($args['list_hide_secondary']=='5'){$field['css_class'] .= $design_style ? " gv-hide-s-5 " : " gd-lv-s-5 ";}


					// set to value if empty
					if(empty($args['show'])){
						$args['show'] = 'icon-label-value';
					}

					if ( ! empty( $args['map_link'] ) ) {
						$args['show'] = str_replace( 'value', 'link', $args['show'] );
					}

					$field['address_template'] = $args['address_template'];

					// unset the extra fields
					unset($field['extra_fields']);

					$output = apply_filters("geodir_custom_field_output_{$field['type']}",'',$args['location'],$field,$args['id'],$args['show']);

				}
			}

			if ( ! empty( $output ) && absint( $args['id'] ) ) {
				$output = geodir_post_address( $output, 'gd_post_address', absint( $args['id'] ) );
			}
		}

		return $output;

	}

	/**
	 * Gets an array of custom field keys for textareas.
	 *
	 * @return array
	 */
	public function get_custom_field_keys(){
		$fields = geodir_post_custom_fields('', 'all', 'all','none');
		$keys = array();
		$keys[] = __('Select Key','geodirectory');
		if(!empty($fields)){
			foreach($fields as $field){
				if(isset($field['field_type']) && $field['field_type']=='textarea'){
					$keys[$field['htmlvar_name']] = $field['htmlvar_name'];
				}
			}
		}

		return $keys;

	}

}

