<?php
/**
 * GeoDirectory GeoDirectory Popular Post View Widget
 *
 * @since 1.0.0
 *
 * @package GeoDirectory
 */

/**
 * GeoDirectory listings widget class.
 *
 * @since 1.0.0
 */
class GeoDir_Widget_Ninja_Forms extends WP_Super_Duper {


    /**
     * Register the popular posts widget.
     *
     * @since 1.0.0
     * @since 1.5.1 Changed from PHP4 style constructors to PHP5 __construct.
     */
    public function __construct() {


        $options = array(
            'textdomain'    => GEODIRECTORY_TEXTDOMAIN,
            'block-icon'    => 'admin-site',
            'block-category'=> 'geodirectory',
            'block-keywords'=> "['ninja','contact','geo']",

            'class_name'    => __CLASS__,
            'base_id'       => 'gd_ninja_forms', // this us used as the widget id and the shortcode id.
            'name'          => __('GD > Ninja Forms','geodirectory'), // the name of the widget.
            'widget_ops'    => array(
                'classname'   => 'geodir-ninja-forms '.geodir_bsui_class(), // widget class
                'description' => esc_html__('Lets you use a ninja form to send to listings.','geodirectory'), // widget description
                'customize_selective_refresh' => true,
                'geodirectory' => true,
                'gd_wgt_showhide' => 'show_on',
                'gd_wgt_restrict' => array( 'gd-detail' ),
            ),

        );

        


        parent::__construct( $options );

    }

    /**
     * Set the arguments later.
     *
     * @return array
     */
    public function set_arguments(){

        $design_style = geodir_design_style();

        $arguments = array();
        $arguments['form_id'] = array(
                'title' => __('Form:', 'geodirectory'),
                'desc' => __('Select the form to use. (You can create a GD contact form from the Ninja Forms settings', 'geodirectory'),
                'type' => 'select',
                'options'   =>  $this->get_ninja_form_options(),
                'default'  => '',
                'custom_attributes' => array(
                    //'required'    => 'true', //
                ),
                'desc_tip' => true,
                'advanced' => false
            );
            $arguments['text'] = array(
                'title' => __('Text:', 'geodirectory'),
                'desc' => __('The text shown than opens the lightbox.', 'geodirectory'),
                'type' => 'text',
                'default'  => __('Contact form','geodirectory'),
                'desc_tip' => true,
                'advanced' => false
            );
            $arguments['post_contact'] = array(
                'title' => __("Post Contact form", 'geodirectory'),
                'desc' => __('If the form is to contact a listing this will only show the form if the `email` field is filled.', 'geodirectory'),
                'type' => 'checkbox',
                'desc_tip' => true,
                'value'  => '1',
                'default'  => '1',
                'advanced' => true
            );
            $arguments['output'] = array(
                'title' => __('Output Type:', 'geodirectory'),
                'desc' => __('How the link to open the lightbox is displayed.', 'geodirectory'),
                'type' => 'select',
                'options'   =>  array(
                    'button'      => __('Button (lightbox)','geodirectory'),
                    'link'      => __("Link (lightbox)",'geodirectory'),
                    'form'      => __("Form",'geodirectory'),
                ),
                'default'  => 'button',
                'desc_tip' => true,
                'advanced' => true
            );


        if($design_style) {

            $arguments['alignment']           = array(
                'name'     => 'alignment',
                'title'    => __( 'Alignment:', 'geodirectory' ),
                'desc'     => __( 'How the item should be positioned on the page.', 'geodirectory' ),
                'type'     => 'select',
                'options'  => array(
                    ""       => __( 'None', 'geodirectory' ),
                    "left"   => __( 'Left', 'geodirectory' ),
                    "center" => __( 'Center', 'geodirectory' ),
                    "right"  => __( 'Right', 'geodirectory' ),
                ),
                'desc_tip' => true,
                'advanced' => false,
                'group'    => __( "Positioning", "geodirectory" )
            );

            $arguments['output'] = array(
                'title' => __('Output Type:', 'geodirectory'),
                'desc' => __('How the link to open the lightbox is displayed.', 'geodirectory'),
                'type' => 'select',
                'options'  => array(
                    "button" => __('Button', 'geodirectory'),
                    "badge" => __( 'Badge', 'geodirectory' ),
                    "pill"  => __( 'Pill', 'geodirectory' ),
                    "link"  => __( 'Link', 'geodirectory' ),
                    'form'      => __("Form",'geodirectory'),
                ),
                'default'  => '',
                'desc_tip' => true,
                'advanced' => false,
                'group'    => __( "Design", "geodirectory" )
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
                                )+geodir_aui_colors(true, true, true),
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
                'element_require' => $design_style ?  '[%color%]=="" && [%show%]!="icon"' : '',
            );
            $arguments['size']  = array(
                'type' => 'select',
                'title' => __('Badge size:', 'geodirectory'),
                'desc' => __('Size of the badge.', 'geodirectory'),
                'options' =>  array(
                    "" => __('Default', 'geodirectory'),
                    "h6" => __('XS (badge)', 'geodirectory'),
                    "h5" => __('S (badge)', 'geodirectory'),
                    "h4" => __('M (badge)', 'geodirectory'),
                    "h3" => __('L (badge)', 'geodirectory'),
                    "h2" => __('XL (badge)', 'geodirectory'),
                    "h1" => __('XXL (badge)', 'geodirectory'),
                    "btn-lg" => __('L (button)', 'geodirectory'),
                    "btn-sm" => __('S (button)', 'geodirectory'),
                ),
                'default' => 'h5',
                'desc_tip' => true,
                'group'     => __("Design","geodirectory")
            );

            $arguments['mt']  = geodir_get_sd_margin_input('mt');
            $arguments['mr']  = geodir_get_sd_margin_input('mr');
            $arguments['mb']  = geodir_get_sd_margin_input('mb');
            $arguments['ml']  = geodir_get_sd_margin_input('ml');

        }

        return $arguments;
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
        global $aui_bs5, $post, $gd_post;

        $defaults = array(
            'form_id' => '',
            'text' => __('Contact form','geodirectory'),
            'post_contact' => '1',
            'output' => 'button',
            'shadow'           => '',
            'color'           => 'primary',
            'bg_color'           => '',
            'txt_color'           => '#ffffff',
            'size'           => 'h5',
            'position'           => '',
            'mt'    => '',
            'mb'    => '',
            'mr'    => '',
            'ml'    => '',
        );

        /**
         * Parse incoming $args into an array and merge it with $defaults
         */
        $args = wp_parse_args( $args, $defaults );

        $design_style = geodir_design_style();

        if($design_style){
            $args['type'] = $args['output'];
            $args['badge'] = $args['text'];
        }

        $is_preview = $this->is_preview();

        $output = '';

        $show = false;
        $post_id = isset($post->ID) ? $post->ID : '';
        if($args['post_contact']=='1'){
            $package_id = ! empty( $gd_post->package_id ) ? $gd_post->package_id : '';
            if(isset($post->post_type) && in_array($post->post_type, geodir_get_posttypes()) && isset($gd_post->email) && $gd_post->email && geodir_check_field_visibility( $package_id, 'email', $post->post_type ) ) {
                $show = true;
            }
        }else{
            $show = true;
        }

        if ( $is_preview ) {
            $show = true;
            $post_id = 0;
        }
        

		/**
		 * Filters whether show or not widget output.
		 *
		 * @since 2.0.46
		 *
		 * @param bool $show Whether to show or not widget output.
		 * @param object $post The post object.
		 * @param array $args Widget arguments.
		 * @param object $this Widget object.
		 */
		$show = apply_filters( 'geodir_show_ninja_form_widget', $show, $post, $args, $this );

		if ( $show ) {
			$action_text = apply_filters( 'geodir_ninja_form_widget_action_text', $args['text'], $post->ID, $args );
			$action_text = __( $action_text, 'geodirectory' );

            if ( $design_style ) {
                if ( empty( $args['css_class'] ) ) {
                    $args['css_class'] = '';
                }

                // margins
                if ( ! empty( $args['mt'] ) ) { $args['css_class'] .= " mt-".sanitize_html_class($args['mt'])." "; }
                if ( ! empty( $args['mr'] ) ) { $args['css_class'] .= ( $aui_bs5 ? ' me-' : ' mr-' ) . sanitize_html_class( $args['mr'] ) . " "; }
                if ( ! empty( $args['mb'] ) ) { $args['css_class'] .= " mb-".sanitize_html_class($args['mb'])." "; }
                if ( ! empty( $args['ml'] ) ) { $args['css_class'] .= ( $aui_bs5 ? ' ms-' : ' ml-' ) . sanitize_html_class( $args['ml'] ) . " "; }

                // set alignment class
                if ( $args['alignment'] != '' ) {
                    if($design_style){
                        if($args['alignment']=='block'){$args['css_class'] .= " d-block ";}
                        elseif($args['alignment']=='left'){$args['css_class'] .= ( $aui_bs5 ? ' float-start ms-2 ' : ' float-left mr-2 ' );}
                        elseif($args['alignment']=='right'){$args['css_class'] .= ( $aui_bs5 ? ' float-end me-2 ' : ' float-right ml-2 ' );}
                        elseif($args['alignment']=='center'){$args['css_class'] .= " text-center ";}
                    }else{
                        $args['css_class'] .= $args['alignment']=='block' ? " gd-d-block gd-clear-both " : " geodir-align-" . sanitize_html_class( $args['alignment'] );
                    }
                }

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
                        case 'btn-lg': $args['size'] = ''; $args['css_class'] = 'btn-lg';break;
                        case 'btn-sm':$args['size'] = '';  $args['css_class'] = 'btn-sm';break;
                        default:
                            $args['size'] = '';

                    }

                }

                if ( $args['output'] == 'form' ) {
                    $output = $is_preview ? '' :  do_shortcode( "[ninja_form id=" . absint( $args['form_id'] ) . "]" );
                }else{

                    $args['link']       ="#form";
                    $args['onclick'] = 'gd_ninja_lightbox(\'geodir_ninja_forms\',\'\',' . absint( $post_id ) . ',' . absint( $args['form_id'] ) . '); return false;';


                    $output = geodir_get_post_badge( $post_id, $args );
                }
            }else{
                if ( $args['output'] == 'button' ) {
                    $output = '<button class="btn btn-default geodir-ninja-forms-link" onclick="gd_ajax_lightbox(\'geodir_ninja_forms\',\'\',' . absint( $post_id ) . ',' . absint( $args['form_id'] ) . '); return false;">' . esc_attr( $action_text ) . '</button>';
                } elseif ( $args['output'] == 'form' ) {
                    $output = $is_preview ? '' : do_shortcode( "[ninja_form id=" . absint( $args['form_id'] ) . "]" );
                } else {
                    $output = '<a class="geodir-ninja-forms-link" href="#" onclick="gd_ajax_lightbox(\'geodir_ninja_forms\',\'\',' . absint( $post_id ) . ',' . absint( $args['form_id'] ) . '); return false;">' . esc_attr( $action_text ) . '</a>';

                }
            }


		}

		return $output;
	}

    /**
     * Get an array of Ninja Forms forms to choose from.
     *
     * @return array
     */
    public function get_ninja_form_options(){
        $forms = Ninja_Forms()->form()->get_forms();
        $form_options = array();
        foreach( $forms as $form ){
            $form_options[$form->get_id()] = $form->get_setting( 'title' ) . " (ID: ".$form->get_id().")";
        }
        asort($form_options);

        return array(''=>__('Select a form','geodirectory')) + $form_options;
    }


}

class GeoDir_Ninja_Forms_MergeTags extends NF_Abstracts_MergeTags {
	/*
	 * The $id property should match the array key where the class is registered.
	 */
	protected $id = 'geodirectory';

	/**
	 * GeoDir_Ninja_Forms_MergeTags constructor.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		parent::__construct();

		/*
		 * Use the `init` and `admin_init` hooks for any necessary data setup that relies on WordPress.
		 * See: https://codex.wordpress.org/Plugin_API/Action_Reference
		 */
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
	}

	public function init() {
		/* Translatable display name for the group. */
		$this->title = __( '<span class="dashicons-before dashicons-admin-site"> GeoDirectory</span>', 'geodirectory' );

		/* Individual tag registration. */
		$this->merge_tags = array(
			'email' => array(
				'id' => 'email',
				'tag' => '{GD:listing_email}',
				'label' => esc_html__( 'Email', 'geodirectory' ),
				'callback' => 'email'
			)
		);
	}

	public function admin_init(){ /* This section intentionally left blank. */ }

	/**
	 * Get Post id.
	 *
	 * @since 2.0.0
	 *
	 * @global object $post WordPress post object.
	 *
	 * @return int $post_id.
	 */
	protected function post_id() {
		global $post;

		if ( is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			// If we are doing AJAX, use the referer to get the Post ID.
			$post_id = absint( url_to_postid( wp_get_referer() ) );

			// Check referer contains correct post
			if ( $post_id && ! geodir_is_gd_post_type( get_post_type( $post_id ) ) ) {
				$post_id = 0;
			}

			// Retrieve value from form data.
			if ( empty( $post_id ) && ( $_post_id = (int) $this->get_submitted_value( 'listing_id' ) ) ) {
				$post_id = $_post_id;
			}
		} elseif ( $post ) {
			$post_id = $post->ID;
		} else {
			return false; // No Post ID found.
		}

		return $post_id;
	}

	/**
	 * The callback method for the {my:foo} merge tag.
	 * @return string
	 */
	public function email() {
		$post_id = $this->post_id();

		if( ! $post_id ) {
			return '';
		}

		$listing_email = geodir_get_post_meta( $post_id, 'email', true );

		return $listing_email ? $listing_email : '';
	}

	/**
	 * Retrieve field value from form data.
	 * @return string|array
	 */
	public function get_submitted_value( $field_key ) {
		$field_value = '';

		if ( empty( $_POST['formData'] ) ) {
			return $field_value;
		}

		$_form_data = json_decode( $_POST['formData'], TRUE  );

		// php5.2 fallback
		if ( empty( $_form_data ) ) {
			$_form_data = json_decode( stripslashes( $_POST['formData'] ), TRUE  );
		}

		if ( empty( $_form_data['id'] ) ) {
			return $field_value;
		}

		$fields = Ninja_Forms()->form( $_form_data['id'] )->get_fields();

		if ( ! empty( $fields ) ) {
			foreach( $fields as $id => $field ) {
				if ( $field->get_setting( 'key' ) == $field_key && isset( $_form_data['fields'][ $id ]['value'] ) ) {
					$field_value = $_form_data['fields'][ $id ]['value'];
				}
			}
		}

		return $field_value;
	}
}
add_filter( 'ninja_forms_new_form_templates','geodir_add_ninja_forms_template' );

/**
* Add ninja forms template.
*
* @since 2.0.0
*
* @param array $templates Templates.
* @return array
*/
function geodir_add_ninja_forms_template( $templates ) {
	$new_templates['formtemplate-geodirectory-contactform'] = array(
		'id'             => 'formtemplate-geodirectory-contactform',
		'title'         => __( 'GeoDirectory Contact Form', 'geodirectory' ),
		'template-desc' => __( 'Allow your users to contact the listing owners. You can add and remove fields as needed.', 'geodirectory' ),
		'form' => geodir_ninja_forms_contact_template()
	);

	return $new_templates + $templates;
}

/**
 * Ninja forms contact Templates.
 *
 * @since 2.0.0
 *
 * @return string settings templates.
 */
function geodir_ninja_forms_contact_template(){
    return '{
    "settings": {
        "title": "GeoDirectory Contact Form",
        "key": "geodirectory_contact",
        "created_at": "' . esc_attr( date( 'Y-m-d H:i:s' ) ) . '",
        "default_label_pos": "above",
        "conditions": [],
        "objectType": "Form Setting",
        "editActive": "",
        "show_title": "0",
        "clear_complete": "1",
        "hide_complete": "1",
        "wrapper_class": "",
        "element_class": "",
        "add_submit": "1",
        "logged_in": "",
        "not_logged_in_msg": "",
        "sub_limit_number": "",
        "sub_limit_msg": "",
        "calculations": [],
        "formContentData": [{
            "order": "0",
            "cells": [{
                "order": "0",
                "fields": ["name"],
                "width": "100"
            }]
        }, {
            "order": "1",
            "cells": [{
                "order": "0",
                "fields": ["email"],
                "width": "100"
            }]
        }, {
            "order": "2",
            "cells": [{
                "order": "0",
                "fields": ["message"],
                "width": "100"
            }]
        }, {
            "order": "3",
            "cells": [{
                "order": "0",
                "fields": ["submit"],
                "width": "100"
            }]
        }],
        "container_styles_background-color": "",
        "container_styles_border": "",
        "container_styles_border-style": "",
        "container_styles_border-color": "",
        "container_styles_color": "",
        "container_styles_height": "",
        "container_styles_width": "",
        "container_styles_font-size": "",
        "container_styles_margin": "",
        "container_styles_padding": "",
        "container_styles_display": "",
        "container_styles_float": "",
        "container_styles_show_advanced_css": "0",
        "container_styles_advanced": "",
        "title_styles_background-color": "",
        "title_styles_border": "",
        "title_styles_border-style": "",
        "title_styles_border-color": "",
        "title_styles_color": "",
        "title_styles_height": "",
        "title_styles_width": "",
        "title_styles_font-size": "",
        "title_styles_margin": "",
        "title_styles_padding": "",
        "title_styles_display": "",
        "title_styles_float": "",
        "title_styles_show_advanced_css": "0",
        "title_styles_advanced": "",
        "row_styles_background-color": "",
        "row_styles_border": "",
        "row_styles_border-style": "",
        "row_styles_border-color": "",
        "row_styles_color": "",
        "row_styles_height": "",
        "row_styles_width": "",
        "row_styles_font-size": "",
        "row_styles_margin": "",
        "row_styles_padding": "",
        "row_styles_display": "",
        "row_styles_show_advanced_css": "0",
        "row_styles_advanced": "",
        "row-odd_styles_background-color": "",
        "row-odd_styles_border": "",
        "row-odd_styles_border-style": "",
        "row-odd_styles_border-color": "",
        "row-odd_styles_color": "",
        "row-odd_styles_height": "",
        "row-odd_styles_width": "",
        "row-odd_styles_font-size": "",
        "row-odd_styles_margin": "",
        "row-odd_styles_padding": "",
        "row-odd_styles_display": "",
        "row-odd_styles_show_advanced_css": "0",
        "row-odd_styles_advanced": "",
        "success-msg_styles_background-color": "",
        "success-msg_styles_border": "",
        "success-msg_styles_border-style": "",
        "success-msg_styles_border-color": "",
        "success-msg_styles_color": "",
        "success-msg_styles_height": "",
        "success-msg_styles_width": "",
        "success-msg_styles_font-size": "",
        "success-msg_styles_margin": "",
        "success-msg_styles_padding": "",
        "success-msg_styles_display": "",
        "success-msg_styles_show_advanced_css": "0",
        "success-msg_styles_advanced": "",
        "error_msg_styles_background-color": "",
        "error_msg_styles_border": "",
        "error_msg_styles_border-style": "",
        "error_msg_styles_border-color": "",
        "error_msg_styles_color": "",
        "error_msg_styles_height": "",
        "error_msg_styles_width": "",
        "error_msg_styles_font-size": "",
        "error_msg_styles_margin": "",
        "error_msg_styles_padding": "",
        "error_msg_styles_display": "",
        "error_msg_styles_show_advanced_css": "0",
        "error_msg_styles_advanced": ""
    },
    "fields": [{
        "objectType": "Field",
        "objectDomain": "fields",
        "editActive": false,
        "order": 1,
        "label": "Listing ID",
        "type": "hidden",
        "key": "listing_id",
        "default": "{wp:post_id}",
        "admin_label": "",
        "drawerDisabled": false
    },{
        "objectType": "Field",
        "objectDomain": "fields",
        "editActive": false,
        "order": 2,
        "label": "Contact",
        "type": "textbox",
        "key": "contact",
        "label_pos": "default",
        "required": false,
        "default": "{wp:post_title}",
        "placeholder": "",
        "container_class": "",
        "element_class": "",
        "input_limit": "",
        "input_limit_type": "characters",
        "input_limit_msg": "Character(s) left",
        "manual_key": false,
        "disable_input": 1,
        "admin_label": "",
        "help_text": "",
        "disable_browser_autocomplete": 1,
        "mask": "",
        "custom_mask": "",
        "custom_name_attribute": "",
        "drawerDisabled": false,
        "desc_text": "<p>This is for your reference only and can\'t be changed.</p>"
    },{
        "objectType": "Field",
        "objectDomain": "fields",
        "editActive": false,
        "order": 3,
        "label": "Divider",
        "type": "hr",
        "container_class": "",
        "element_class": "",
        "key": "hr",
        "drawerDisabled": false
    },{
        "label": "Name",
        "key": "name",
        "parent_id": "1",
        "type": "textbox",
        "created_at": "' . esc_attr( date( 'Y-m-d H:i:s' ) ) . '",
        "label_pos": "above",
        "required": "1",
        "order": "4",
        "placeholder": "",
        "default": "",
        "wrapper_class": "",
        "element_class": "",
        "objectType": "Field",
        "objectDomain": "fields",
        "editActive": "",
        "container_class": "",
        "input_limit": "",
        "input_limit_type": "characters",
        "input_limit_msg": "Character(s) left",
        "manual_key": "",
        "disable_input": "",
        "admin_label": "",
        "help_text": "",
        "desc_text": "",
        "disable_browser_autocomplete": "",
        "mask": "",
        "custom_mask": "",
        "wrap_styles_background-color": "",
        "wrap_styles_border": "",
        "wrap_styles_border-style": "",
        "wrap_styles_border-color": "",
        "wrap_styles_color": "",
        "wrap_styles_height": "",
        "wrap_styles_width": "",
        "wrap_styles_font-size": "",
        "wrap_styles_margin": "",
        "wrap_styles_padding": "",
        "wrap_styles_display": "",
        "wrap_styles_float": "",
        "wrap_styles_show_advanced_css": "0",
        "wrap_styles_advanced": "",
        "label_styles_background-color": "",
        "label_styles_border": "",
        "label_styles_border-style": "",
        "label_styles_border-color": "",
        "label_styles_color": "",
        "label_styles_height": "",
        "label_styles_width": "",
        "label_styles_font-size": "",
        "label_styles_margin": "",
        "label_styles_padding": "",
        "label_styles_display": "",
        "label_styles_float": "",
        "label_styles_show_advanced_css": "0",
        "label_styles_advanced": "",
        "element_styles_background-color": "",
        "element_styles_border": "",
        "element_styles_border-style": "",
        "element_styles_border-color": "",
        "element_styles_color": "",
        "element_styles_height": "",
        "element_styles_width": "",
        "element_styles_font-size": "",
        "element_styles_margin": "",
        "element_styles_padding": "",
        "element_styles_display": "",
        "element_styles_float": "",
        "element_styles_show_advanced_css": "0",
        "element_styles_advanced": "",
        "cellcid": "c3277"
    }, {
        "label": "Email",
        "key": "email",
        "parent_id": "1",
        "type": "email",
        "created_at": "' . esc_attr( date( 'Y-m-d H:i:s' ) ) . '",
        "label_pos": "above",
        "required": "1",
        "order": "5",
        "placeholder": "",
        "default": "",
        "wrapper_class": "",
        "element_class": "",
        "objectType": "Field",
        "objectDomain": "fields",
        "editActive": "",
        "container_class": "",
        "admin_label": "",
        "help_text": "",
        "desc_text": "",
        "wrap_styles_background-color": "",
        "wrap_styles_border": "",
        "wrap_styles_border-style": "",
        "wrap_styles_border-color": "",
        "wrap_styles_color": "",
        "wrap_styles_height": "",
        "wrap_styles_width": "",
        "wrap_styles_font-size": "",
        "wrap_styles_margin": "",
        "wrap_styles_padding": "",
        "wrap_styles_display": "",
        "wrap_styles_float": "",
        "wrap_styles_show_advanced_css": "0",
        "wrap_styles_advanced": "",
        "label_styles_background-color": "",
        "label_styles_border": "",
        "label_styles_border-style": "",
        "label_styles_border-color": "",
        "label_styles_color": "",
        "label_styles_height": "",
        "label_styles_width": "",
        "label_styles_font-size": "",
        "label_styles_margin": "",
        "label_styles_padding": "",
        "label_styles_display": "",
        "label_styles_float": "",
        "label_styles_show_advanced_css": "0",
        "label_styles_advanced": "",
        "element_styles_background-color": "",
        "element_styles_border": "",
        "element_styles_border-style": "",
        "element_styles_border-color": "",
        "element_styles_color": "",
        "element_styles_height": "",
        "element_styles_width": "",
        "element_styles_font-size": "",
        "element_styles_margin": "",
        "element_styles_padding": "",
        "element_styles_display": "",
        "element_styles_float": "",
        "element_styles_show_advanced_css": "0",
        "element_styles_advanced": "",
        "cellcid": "c3281"
    },{
        "objectType": "Field",
        "objectDomain": "fields",
        "editActive": false,
        "order": 6,
        "label": "Phone",
        "type": "phone",
        "key": "phone",
        "label_pos": "default",
        "required": false,
        "default": "",
        "placeholder": "",
        "container_class": "",
        "element_class": "",
        "input_limit": "",
        "input_limit_type": "characters",
        "input_limit_msg": "Character(s) left",
        "manual_key": false,
        "admin_label": "",
        "help_text": "",
        "mask": "",
        "custom_mask": "",
        "custom_name_attribute": "phone",
        "drawerDisabled": false
    }, {
        "label": "Message",
        "key": "message",
        "parent_id": "1",
        "type": "textarea",
        "created_at": "' . esc_attr( date( 'Y-m-d H:i:s' ) ) . '",
        "label_pos": "above",
        "required": "1",
        "order": "7",
        "placeholder": "",
        "default": "",
        "wrapper_class": "",
        "element_class": "",
        "objectType": "Field",
        "objectDomain": "fields",
        "editActive": "",
        "container_class": "",
        "input_limit": "",
        "input_limit_type": "characters",
        "input_limit_msg": "Character(s) left",
        "manual_key": "",
        "disable_input": "",
        "admin_label": "",
        "help_text": "",
        "desc_text": "",
        "disable_browser_autocomplete": "",
        "textarea_rte": "",
        "disable_rte_mobile": "",
        "textarea_media": "",
        "wrap_styles_background-color": "",
        "wrap_styles_border": "",
        "wrap_styles_border-style": "",
        "wrap_styles_border-color": "",
        "wrap_styles_color": "",
        "wrap_styles_height": "",
        "wrap_styles_width": "",
        "wrap_styles_font-size": "",
        "wrap_styles_margin": "",
        "wrap_styles_padding": "",
        "wrap_styles_display": "",
        "wrap_styles_float": "",
        "wrap_styles_show_advanced_css": "0",
        "wrap_styles_advanced": "",
        "label_styles_background-color": "",
        "label_styles_border": "",
        "label_styles_border-style": "",
        "label_styles_border-color": "",
        "label_styles_color": "",
        "label_styles_height": "",
        "label_styles_width": "",
        "label_styles_font-size": "",
        "label_styles_margin": "",
        "label_styles_padding": "",
        "label_styles_display": "",
        "label_styles_float": "",
        "label_styles_show_advanced_css": "0",
        "label_styles_advanced": "",
        "element_styles_background-color": "",
        "element_styles_border": "",
        "element_styles_border-style": "",
        "element_styles_border-color": "",
        "element_styles_color": "",
        "element_styles_height": "",
        "element_styles_width": "",
        "element_styles_font-size": "",
        "element_styles_margin": "",
        "element_styles_padding": "",
        "element_styles_display": "",
        "element_styles_float": "",
        "element_styles_show_advanced_css": "0",
        "element_styles_advanced": "",
        "cellcid": "c3284"
    }, {
        "label": "Submit",
        "key": "submit",
        "parent_id": "1",
        "type": "submit",
        "created_at": "' . esc_attr( date( 'Y-m-d H:i:s' ) ) . '",
        "processing_label": "Processing",
        "order": "8",
        "objectType": "Field",
        "objectDomain": "fields",
        "editActive": "",
        "container_class": "",
        "element_class": "",
        "wrap_styles_background-color": "",
        "wrap_styles_border": "",
        "wrap_styles_border-style": "",
        "wrap_styles_border-color": "",
        "wrap_styles_color": "",
        "wrap_styles_height": "",
        "wrap_styles_width": "",
        "wrap_styles_font-size": "",
        "wrap_styles_margin": "",
        "wrap_styles_padding": "",
        "wrap_styles_display": "",
        "wrap_styles_float": "",
        "wrap_styles_show_advanced_css": "0",
        "wrap_styles_advanced": "",
        "label_styles_background-color": "",
        "label_styles_border": "",
        "label_styles_border-style": "",
        "label_styles_border-color": "",
        "label_styles_color": "",
        "label_styles_height": "",
        "label_styles_width": "",
        "label_styles_font-size": "",
        "label_styles_margin": "",
        "label_styles_padding": "",
        "label_styles_display": "",
        "label_styles_float": "",
        "label_styles_show_advanced_css": "0",
        "label_styles_advanced": "",
        "element_styles_background-color": "",
        "element_styles_border": "",
        "element_styles_border-style": "",
        "element_styles_border-color": "",
        "element_styles_color": "",
        "element_styles_height": "",
        "element_styles_width": "",
        "element_styles_font-size": "",
        "element_styles_margin": "",
        "element_styles_padding": "",
        "element_styles_display": "",
        "element_styles_float": "",
        "element_styles_show_advanced_css": "0",
        "element_styles_advanced": "",
        "submit_element_hover_styles_background-color": "",
        "submit_element_hover_styles_border": "",
        "submit_element_hover_styles_border-style": "",
        "submit_element_hover_styles_border-color": "",
        "submit_element_hover_styles_color": "",
        "submit_element_hover_styles_height": "",
        "submit_element_hover_styles_width": "",
        "submit_element_hover_styles_font-size": "",
        "submit_element_hover_styles_margin": "",
        "submit_element_hover_styles_padding": "",
        "submit_element_hover_styles_display": "",
        "submit_element_hover_styles_float": "",
        "submit_element_hover_styles_show_advanced_css": "0",
        "submit_element_hover_styles_advanced": "",
        "cellcid": "c3287"
    }],
    "actions": [{
        "title": "",
        "key": "",
        "type": "save",
        "active": "1",
        "created_at": "' . esc_attr( date( 'Y-m-d H:i:s' ) ) . '",
        "label": "Store Submission",
        "objectType": "Action",
        "objectDomain": "actions",
        "editActive": "",
        "conditions": {
            "collapsed": "",
            "process": "1",
            "connector": "all",
            "when": [{
                "connector": "AND",
                "key": "",
                "comparator": "",
                "value": "",
                "type": "field",
                "modelType": "when"
            }],
            "then": [{
                "key": "",
                "trigger": "",
                "value": "",
                "type": "field",
                "modelType": "then"
            }],
            "else": []
        },
        "payment_gateways": "",
        "payment_total": "",
        "tag": "",
        "to": "",
        "email_subject": "",
        "email_message": "",
        "from_name": "",
        "from_address": "",
        "reply_to": "",
        "email_format": "html",
        "cc": "",
        "bcc": "",
        "attach_csv": "",
        "redirect_url": "",
        "email_message_plain": ""
    }, {
        "title": "",
        "key": "",
        "type": "email",
        "active": "1",
        "created_at": "' . esc_attr( date( 'Y-m-d H:i:s' ) ) . '",
        "label": "Email Confirmation",
        "to": "{field:email}",
        "subject": "This is an email action.",
        "message": "Hello, Ninja Forms!",
        "objectType": "Action",
        "objectDomain": "actions",
        "editActive": "",
        "conditions": {
            "collapsed": "",
            "process": "1",
            "connector": "all",
            "when": [],
            "then": [{
                "key": "",
                "trigger": "",
                "value": "",
                "type": "field",
                "modelType": "then"
            }],
            "else": []
        },
        "payment_gateways": "",
        "payment_total": "",
        "tag": "",
        "email_subject": "Submission Confirmation ",
        "email_message": "<p>{all_fields_table}<br><\/p>",
        "from_name": "",
        "from_address": "",
        "reply_to": "",
        "email_format": "html",
        "cc": "",
        "bcc": "",
        "attach_csv": "",
        "email_message_plain": ""
    }, {
        "title": "",
        "key": "",
        "type": "email",
        "active": "1",
        "created_at": "' . esc_attr( date( 'Y-m-d H:i:s' ) ) . '",
        "objectType": "Action",
        "objectDomain": "actions",
        "editActive": "",
        "label": "Email Notification",
        "conditions": {
            "collapsed": "",
            "process": "1",
            "connector": "all",
            "when": [{
                "connector": "AND",
                "key": "",
                "comparator": "",
                "value": "",
                "type": "field",
                "modelType": "when"
            }],
            "then": [{
                "key": "",
                "trigger": "",
                "value": "",
                "type": "field",
                "modelType": "then"
            }],
            "else": []
        },
        "payment_gateways": "",
        "payment_total": "",
        "tag": "",
        "to": "{GD:listing_email}",
        "email_subject": "New contact form: {wp:site_title}",
        "email_message": "<p>{all_fields_table}<\/p><p>-{field:name} ( {field:email} )<\/p>",
        "from_name": "",
        "from_address": "",
        "reply_to": "{field:email}",
        "email_format": "html",
        "cc": "",
        "bcc": "{system:admin_email}",
        "attach_csv": "0",
        "email_message_plain": ""
    }, {
        "title": "",
        "key": "",
        "type": "successmessage",
        "active": "1",
        "created_at": "' . esc_attr( date( 'Y-m-d H:i:s' ) ) . '",
        "label": "Success Message",
        "message": "Thank you {field:name} your contact form has been sent to the user!",
        "objectType": "Action",
        "objectDomain": "actions",
        "editActive": "",
        "conditions": {
            "collapsed": "",
            "process": "1",
            "connector": "all",
            "when": [{
                "connector": "AND",
                "key": "",
                "comparator": "",
                "value": "",
                "type": "field",
                "modelType": "when"
            }],
            "then": [{
                "key": "",
                "trigger": "",
                "value": "",
                "type": "field",
                "modelType": "then"
            }],
            "else": []
        },
        "payment_gateways": "",
        "payment_total": "",
        "tag": "",
        "to": "",
        "email_subject": "",
        "email_message": "",
        "from_name": "",
        "from_address": "",
        "reply_to": "",
        "email_format": "html",
        "cc": "",
        "bcc": "",
        "attach_csv": "",
        "redirect_url": "",
        "success_msg": "<p>Form submitted successfully.<\/p><p>A confirmation email was sent to {field:email}.<\/p>",
        "email_message_plain": ""
    }]
}';
}