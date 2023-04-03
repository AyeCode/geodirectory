<?php
/**
 * Post Type Meta widget
 *
 * @package GeoDirectory
 * @since 2.0.0.85
 */

/**
 * GeoDir_Widget_CPT_Meta class.
 */
class GeoDir_Widget_CPT_Meta extends WP_Super_Duper {

	/**
	 * Sets up a widget instance.
	 */
	public function __construct() {
		$options = array(
			'textdomain'    => 'geodirectory',
			'block-icon'    => 'location-alt',
			'block-category'=> 'geodirectory',
			'block-keywords'=> "['cpt','meta','post type']",
			'class_name'    => __CLASS__,
			'base_id'       => 'gd_cpt_meta',
			'name'          => __( 'GD > CPT Meta', 'geodirectory' ),
			'widget_ops'    => array(
				'classname'   => 'geodir-cpt-meta-container '.geodir_bsui_class(),
				'description' => esc_html__( 'Displays the meta title, meta description, cpt description, image on post type archive page.', 'geodirectory' ),
				'geodirectory' => true,
				'gd_wgt_showhide' => 'show_on',
				'gd_wgt_restrict' => array( 'gd-pt' )
			),
			'arguments' => array(
				'title' => array(
					'type' => 'text',
					'title' => __( 'Title:', 'geodirectory' ),
					'desc' => __( 'Extra main title if needed.', 'geodirectory' ),
					'placeholder' => '',
					'default' => '',
					'desc_tip' => true,
					'advanced' => false
				),
				'post_type' => array(
					'title' => __('Post Type:', 'geodirectory'),
					'desc' => __('Select the CPT or leave as auto to have it auto detect the CPT.', 'geodirectory'),
					'type' => 'select',
					'options'   =>  array(''=>__("Auto Detect","geodirectory")) + geodir_get_posttypes('options-plural'),
					'default'  => '',
					'desc_tip' => true,
					'advanced' => false,
//					'group'     => __("Filters","geodirectory")
				),
				'key' => array(
					'type' => 'select',
					'title' => __( 'Key:', 'geodirectory' ),
					'desc' => __( 'This is the cpt meta field key.', 'geodirectory' ),
					'placeholder' => '',
					'options' => array(
						'plural_name' => __( 'Plural Name', 'geodirectory' ),
						'singular_name' => __( 'Singular Name', 'geodirectory' ),
						'slug' => __( 'Slug', 'geodirectory' ),
						'cpt_title' => __( 'CPT Title', 'geodirectory' ),
						'meta_title' => __( 'Meta Title', 'geodirectory' ),
						'meta_description' => __( 'Meta Description', 'geodirectory' ),
						'description' => __( 'CPT Description', 'geodirectory' ),
						'image' => __( 'CPT Image', 'geodirectory' ),
					),
					'desc_tip' => true,
					'default' => '',
					'advanced' => false
				),
				'image_size' => array(
					'type' => 'select',
					'title' => __( 'Image size:', 'geodirectory' ),
					'desc' => __( 'The WP image size as a text string.', 'geodirectory' ),
					'options' => self::get_image_sizes(),
					'desc_tip' => true,
					'value' => '',
					'default' => '',
					'advanced' => true,
					'element_require' => '[%key%]=="image"',
				),
				'no_wrap' => array(
					'type' => 'checkbox',
					'title' => __( 'No Wrap:', 'geodirectory' ),
					'desc' => __( 'Remove wrapping div.', 'geodirectory' ),
					'default' => '0',
					'advanced' => false,
					'group'     => __("Wrapper Styles","geodirectory")
				),
				'alignment' => array(
					'type' => 'select',
					'title' => __( 'Alignment:', 'geodirectory' ),
					'desc' => __( 'How the item should be positioned on the page.', 'geodirectory' ),
					'options' => array(
						"" => __( 'None', 'geodirectory' ),
						"block" => __( 'Block', 'geodirectory' ),
						"left" => __( 'Left', 'geodirectory' ),
						"center" => __( 'Center', 'geodirectory' ),
						"right" => __( 'Right', 'geodirectory' ),
					),
					'desc_tip' => true,
					'advanced' => true
				),
				'text_alignment' => array(
					'type' => 'select',
					'title' => __( 'Text Align:', 'geodirectory' ),
					'desc' => __( 'How the text should be aligned.', 'geodirectory' ),
					'options' => array(
						"" => __( 'None', 'geodirectory' ),
						"left" => __( 'Left', 'geodirectory' ),
						"center" => __( 'Center', 'geodirectory' ),
						"right" => __( 'Right', 'geodirectory' ),
					),
					'desc_tip' => true,
					'advanced' => true
				),
				'css_class' => array(
					'type' => 'text',
					'title' => __( 'Extra class:', 'geodirectory' ),
					'desc' => __( 'Give the wrapper an extra class so you can style things as you want.', 'geodirectory' ),
					'placeholder' => '',
					'default' => '',
					'desc_tip' => true,
					'advanced' => true,
				),
				'cpt_only'  => array(
					'type' => 'checkbox',
					'title' => __( 'Hide On Non Post Type Archive Pages', 'geodirectory' ),
					'value' => '1',
					'default' => '0',
					'desc_tip' => true,
					'advanced' => false
				)
			)
		);

		$design_style = geodir_design_style();

		if($design_style) {

			// background
			$arguments['bg']  = geodir_get_sd_background_input('mt');

			// margins
			$arguments['mt']  = geodir_get_sd_margin_input('mt');
			$arguments['mr']  = geodir_get_sd_margin_input('mr');
			$arguments['mb']  = geodir_get_sd_margin_input('mb',array('default'=>3));
			$arguments['ml']  = geodir_get_sd_margin_input('ml');

			// padding
			$arguments['pt']  = geodir_get_sd_padding_input('pt');
			$arguments['pr']  = geodir_get_sd_padding_input('pr');
			$arguments['pb']  = geodir_get_sd_padding_input('pb');
			$arguments['pl']  = geodir_get_sd_padding_input('pl');

			// border
			$arguments['border']  = geodir_get_sd_border_input('border');
			$arguments['rounded']  = geodir_get_sd_border_input('rounded');
			$arguments['rounded_size']  = geodir_get_sd_border_input('rounded_size');

			// shadow
			$arguments['shadow']  = geodir_get_sd_shadow_input('shadow');

			$options['arguments'] = $options['arguments'] + $arguments;

		}

		parent::__construct( $options );
	}


	/**
	 * The widget output.
	 *
	 * @param array $instance
	 * @param array $args
	 * @param string $content
	 *
	 * @return mixed|string|void
	 */
	public function output( $instance = array(), $args = array(), $content = '' ) {
		global $aui_bs5, $geodirectory, $post, $gd_post;

		$instance = shortcode_atts(
			array(
				'title' => '',
				'post_type' => '',
				'key' => 'name',
				'image_size' => '',
				'no_wrap' => '',
				'alignment' => '',
				'text_alignment' => '',
				'css_class' => '',
				'cpt_only' => '',
				'bg'    => '',
				'mt'    => '',
				'mb'    => '3',
				'mr'    => '',
				'ml'    => '',
				'pt'    => '',
				'pb'    => '',
				'pr'    => '',
				'pl'    => '',
				'border'    => '',
				'rounded'    => '',
				'rounded_size'    => '',
				'shadow'    => '',
			),
			$instance,
			'gd_cpt_meta'
		);

		if ( empty( $instance['image_size'] ) ) {
			$instance['image_size'] = 'thumbnail';
		}

		$post_type = ! empty( $instance['post_type'] ) ? esc_attr( $instance['post_type'] ) : geodir_get_current_posttype();

		$output = '';
		if ( $this->is_preview() && ! $post_type ) {
			$post_type = 'gd_place';
		}

		if ( ! geodir_is_gd_post_type( $post_type ) ) {
			return $output;
		}

		if ( ! empty( $instance['cpt_only'] ) && ! $this->is_preview() ) {
			if ( ! geodir_is_page( 'post_type' ) ) {
				return;
			}
		}

		$post_type_obj = $post_type ? get_post_type_object( $post_type ) : array();
		if ( empty( $post_type_obj ) ) {
			return;
		}

		$design_style = geodir_design_style();

		$key = $instance['key'];
		$css_class = 'geodir-cpt-meta geodir-meta-' . $key;

		if ( $instance['css_class'] != '' ) {
			$css_class .= " " . geodir_sanitize_html_class( $instance['css_class'] );
		}

		if ( $instance['text_alignment'] != '' ) {
			$css_class .= $design_style ? " text-".sanitize_html_class( $instance['text_alignment'] ) : " geodir-text-align" . sanitize_html_class( $instance['text_alignment'] );
		}

		if ( $instance['alignment'] != '' ) {
			if($design_style){
				if($instance['alignment']=='block'){$css_class .= " d-block ";}
				elseif($instance['alignment']=='left'){$css_class .= ( $aui_bs5 ? ' float-start ms-2 ' : ' float-left mr-2 ' );}
				elseif($instance['alignment']=='right'){$css_class .= ( $aui_bs5 ? ' float-end me-2 ' : ' float-right ml-2 ' );}
				elseif($instance['alignment']=='center'){$css_class .= " mw-100 d-block mx-auto ";}
			}else{
				$css_class .= " geodir-align" . sanitize_html_class( $instance['alignment'] );
			}
		}

		$value = '';
		switch ( $key ) {
			case 'plural_name':
				$value = geodir_post_type_name( $post_type, true );
				break;
			case 'singular_name':
				$value = geodir_post_type_singular_name( $post_type, true );
				break;
			case 'slug':
				$value = urlencode( geodir_cpt_permalink_rewrite_slug( $post_type ) );
				break;
			case 'cpt_title':
				if ( ! empty( $post_type_obj->seo['title'] ) ) {
					$value = __( stripslashes( $post_type_obj->seo['title'] ), 'geodirectory' );
				}
				break;
			case 'meta_title':
				if ( ! empty( $post_type_obj->seo['meta_title'] ) ) {
					$value = __( stripslashes( $post_type_obj->seo['meta_title'] ), 'geodirectory' );
				}
				break;
			case 'meta_description':
				if ( ! empty( $post_type_obj->seo['meta_description'] ) ) {
					$value = __( stripslashes( $post_type_obj->seo['meta_description'] ), 'geodirectory' );
				}
				break;
			case 'description':
				if ( ! empty( $post_type_obj->description ) ) {
					$value = __( stripslashes( $post_type_obj->description ), 'geodirectory' );
				}
				break;
			case 'image':
				if ( ! empty( $post_type_obj->default_image ) ) {
					$value = wp_get_attachment_image( $post_type_obj->default_image, $instance['image_size'], "", array( "class" => "img-responsive" ) );
				}
				break;
		}

		$value = apply_filters( 'geodir_cpt_meta_value', $value, $key, $post_type, $post_type_obj, $instance );
		if ( strpos( $value, '%%' ) !== false ) {
			$value = geodir_replace_location_variables( $value );
		}

		if ( empty( $value ) ) {
			return;
		}

		if ( empty( $instance['no_wrap'] ) ) {

			// wrap class
			$css_class .= " ".geodir_build_aui_class($instance);

			$output = '<div class="' . $css_class . '">' . $value . '</div>';
		} else {
			$output = $value;
		}

		return apply_filters( 'geodir_cpt_meta_output', $output, $value, $key, $post_type, $post_type_obj, $instance );
	}

	public static function get_image_sizes() {
		$image_sizes = array(
			'' => 'default (thumbnail)'
		);

		$available = get_intermediate_image_sizes();
		if ( ! empty( $available ) ) {
			foreach( $available as $size ) {
				$image_sizes[ $size ] = $size;
			}
		}

		$image_sizes['full'] = 'full';

		return $image_sizes;
	}
}
