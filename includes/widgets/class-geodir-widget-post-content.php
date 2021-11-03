<?php

/**
 * GeoDir_Widget_Post_Content class.
 *
 * @since 2.0.0
 */
class GeoDir_Widget_Post_Content extends WP_Super_Duper {


	public $arguments;
	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {

		$options = array(
			'textdomain'    => GEODIRECTORY_TEXTDOMAIN,
			'block-icon'    => 'menu',
			'block-category'=> 'geodirectory',
			'block-keywords'=> "['geo','description','content']",
			'class_name'    => __CLASS__,
			'base_id'       => 'gd_post_content', // this us used as the widget id and the shortcode id.
			'name'          => __('GD > Post Content','geodirectory'), // the name of the widget.
			'widget_ops'    => array(
				'classname'   => 'geodir-post-content-container '.geodir_bsui_class(), // widget class
				'description' => esc_html__('This shows a post content text. You can show text from any textarea field.','geodirectory'), // widget description
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
				'key'  => array(
					'name' => 'key',
					'title' => __('Key:', 'geodirectory'),
					'desc' => __('This is the custom field key.', 'geodirectory'),
					'type' => 'select',
					'placeholder' => 'post_content',
					'options'   => $this->get_custom_field_keys(),
					'desc_tip' => true,
					'default'  => 'post_content',
					'advanced' => false
				),
				'show'  => array(
					'title' => __('Show:', 'geodirectory'),
					'desc' => __('What part of the post meta to show.', 'geodirectory'),
					'type' => 'select',
					'options'   =>  array(
						"" => __('value (strip_tags)', 'geodirectory'),
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
				'limit'  => array(
					'title' => __('Word limit:', 'geodirectory'),
					'desc' => __('How many words to limit the text to. (will auto strip tags)', 'geodirectory'),
					'type' => 'number',
					'placeholder'  => '20',
					'desc_tip' => true,
					'advanced' => false
				),
				'max_height'  => array(
					'title' => __('Max height:', 'geodirectory'),
					'desc' => __('Height in (px) This can be used to set a consistent height of the text with the read more button then linking to the full text.', 'geodirectory'),
					'type' => 'number',
					'default'  => '',
					'placeholder' => '120',
					'desc_tip' => true,
					'advanced' => true
				),
				'read_more'  => array(
					'title' => __("Read more link:", 'geodirectory'),
					'desc' => __('Show the read more link at the end of the text. enter `0` to not show link.', 'geodirectory'),
					'type' => 'text',
					'desc_tip' => true,
					'value'  => '',
					'placeholder' => __("Read more...", 'geodirectory'),
					'advanced' => true
				),
				'alignment'  => array(
					'title' => __('Text Align:', 'geodirectory'),
					'desc' => __('How the text should be aligned.', 'geodirectory'),
					'type' => 'select',
					'options'   =>  array(
						"" => __('None', 'geodirectory'),
						"left" => __('Left', 'geodirectory'),
						"center" => __('Center', 'geodirectory'),
						"right" => __('Right', 'geodirectory'),
						"justify" => __("Justify","geodirectory"),
					),
					'desc_tip' => true,
					'advanced' => false
				),
				'strip_tags'  => array(
					'title' => __("Strip tags:", 'geodirectory'),
					'desc' => __('Strip tags from content.', 'geodirectory'),
					'type' => 'checkbox',
					'desc_tip' => true,
					'value'  => '1',
					'default'  => '0',
					'advanced' => true
				),
			)
		);

		// text color
		$options['arguments']['text_color'] = geodir_get_sd_text_color_input(array('group'     => __("General","geodirectory")));

		// background
		$options['arguments']['bg']  = geodir_get_sd_background_input();

		// margins
		$options['arguments']['mt']  = geodir_get_sd_margin_input('mt');
		$options['arguments']['mr']  = geodir_get_sd_margin_input('mr');
		$options['arguments']['mb']  = geodir_get_sd_margin_input('mb');
		$options['arguments']['ml']  = geodir_get_sd_margin_input('ml');

		// padding
		$options['arguments']['pt']  = geodir_get_sd_padding_input('pt');
		$options['arguments']['pr']  = geodir_get_sd_padding_input('pr');
		$options['arguments']['pb']  = geodir_get_sd_padding_input('pb');
		$options['arguments']['pl']  = geodir_get_sd_padding_input('pl');




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
			'key'   => 'post_content',
			'show'    => '', // title,value (default blank, all)
			'strip_tags' => '',
			'max_height' => '',
			'read_more' => '',
			'limit'    => '', // the word limit number (default: 20)
			'alignment'    => '', // left,right,center
			'location'  => 'none',
			'text_color'    => '',
			'bg'    => '',
			'mt'    => '',
			'mr'    => '',
			'mb'    => '',
			'ml'    => '',
			'pt'    => '',
			'pr'    => '',
			'pb'    => '',
			'pl'    => '',
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
		if(empty($args['key'])){$errors[] = __('key is missing','geodirectory');}
		if(empty($args['id'])){$errors[] = __('id is missing','geodirectory');}
		if(empty($post_type)){$errors[] = __('invalid post type','geodirectory');}

		if ( ! empty( $errors ) ) {
			$output .= implode( ", ", $errors );
		}

		if ( $this->is_preview()) {
			$output = ''; // Show placeholder on builder preview.
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
					if ( $args['alignment'] != '' ) {
						$field['css_class'] .= $design_style ? " text-".sanitize_html_class( $args['alignment'] ) : " geodir-text-align" . sanitize_html_class( $args['alignment'] );
					}elseif($design_style){
						$field['css_class'] .= " clear-both ";
					}

					if ( $design_style ) {
						$field['css_class'] .= " ".geodir_build_aui_class($args);
					}

					// set to value if empty
					if(empty($args['show'])){
						$args['show'] = 'value';
					}

					// set max_height
					if(!empty($args['max_height'])){
						$args['show'] .= '-fade::'.absint($args['max_height']);
					}

					// maybe force strip tags
					if(!empty($args['show']) && !empty($args['strip_tags'])){
						$args['show'] .= "-strip";
					}

					if(!empty($args['max_height']) || !empty($args['limit'])){
						// maybe show read_more
						if(!empty($args['read_more'])){
							$args['show'] .= "-more::".$args['read_more'];
						}elseif(empty($args['read_more']) && $args['read_more']!=='0'){
							$args['show'] .= "-more";
						}
					}


					// set the limit
					if(!empty($args['limit'])){
						$args['show'] .= "-limit::".absint($args['limit']);
					}

//					$output = $args['show'];
//					print_r($args);

					$output = apply_filters("geodir_custom_field_output_{$field['type']}",'',$args['location'],$field,$args['id'],$args['show']);


				}
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

