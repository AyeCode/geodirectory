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
			'block-category'=> 'widgets',
			'block-keywords'=> "['cpt','meta','post type']",
			'class_name'    => __CLASS__,
			'base_id'       => 'gd_cpt_meta',
			'name'          => __( 'GD > CPT Meta', 'geodirectory' ),
			'widget_ops'    => array(
				'classname'   => 'geodir-cpt-meta-container',
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
					'advanced' => true
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
			)
		);

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
		global $geodirectory, $post, $gd_post;

		$instance = shortcode_atts( 
			array(
				'title' => '',
				'key' => 'name',
				'image_size' => '',
				'no_wrap' => '',
				'alignment' => '',
				'text_alignment' => '',
				'css_class' => ''
			), 
			$instance, 
			'gd_cpt_meta' 
		);
		if ( empty( $instance['image_size'] ) ) {
			$instance['image_size'] = 'thumbnail';
		}

		$output = '';
		if ( $this->is_preview() ) {
			return $output;
		}

		if ( ! geodir_is_page( 'post_type' ) ) {
			return;
		}

		$post_type = geodir_get_current_posttype();
		$post_type_obj = $post_type ? get_post_type_object( $post_type ) : array();
		if ( empty( $post_type_obj ) ) {
			return;
		}

		$key = $instance['key'];
		$css_class = 'geodir-cpt-meta geodir-meta-' . $key;

		if ( $instance['css_class'] != '' ) {
			$css_class .= " " . esc_attr( $instance['css_class'] );
		}

		if ( $instance['text_alignment'] != '' ) {
			$css_class .= " geodir-text-align" . $instance['text_alignment'];
		}

		if ( $instance['alignment'] != '' ) {
			$css_class .= " geodir-align" . $instance['alignment'];
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
