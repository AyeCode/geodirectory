<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * GeoDirectory Search widget.
 *
 * @since 1.0.0
 */
class GeoDir_Widget_Add_Listing extends WP_Super_Duper {

    /**
     * Register the advanced search widget with WordPress.
     *
     */
    public function __construct() {

        $options = array(
            'textdomain'    => GEODIRECTORY_TEXTDOMAIN,
            'block-icon'    => 'fas fa-plus',
            'block-category'=> 'geodirectory',
            'block-keywords'=> "['add','listing','geodir']",
            'block-outputx'   => array( // the block visual output elements as an array
                array(
                    'element' => 'div',
                    'class'   => '[%className%]',
                    'style'   => '{background: "#eee",width: "100%", height: "450px", position:"relative"}',
                    array(
                        'element' => 'i',
                        'if_class'   => '[%animation%]=="fade" ? "fas fa-file-alt gd-fadein-animation" : "far fa-image gd-right-left-animation"',
                        'style'   => '{"text-align": "center", "vertical-align": "middle", "line-height": "450px", width: "100%","font-size":"40px",color:"#aaa"}',
                        'content' => ' '.__( 'Add listing form placeholder', 'geodirectory' ),
                    ),
                ),
            ),
            'class_name'    => __CLASS__,
            'base_id'       => 'gd_add_listing', // this us used as the widget id and the shortcode id.
            'name'          => __('GD > Add Listing','geodirectory'), // the name of the widget.
            'widget_ops'    => array(
                'classname'   => 'geodir-add-listing '.geodir_bsui_class(),
                'description' => esc_html__('Shows the GeoDirectory add listing form.','geodirectory'), // widget description
                'geodirectory' => true,
            ),
        );

        parent::__construct( $options );

        add_action( 'wp_enqueue_scripts',array( $this, 'enqueue_scripts' ) );
    }

    /**
     * Set the arguments later.
     *
     * @return array
     */
    public function set_arguments(){
        $args = array();
        $design_style = geodir_design_style();

        $args['post_type']  = array(
                'title' => __('Default Post Type:', 'geodirectory'),
                'desc' => __('The custom post types to show by default. Only used when there are multiple CPTs.', 'geodirectory'),
                'type' => 'select',
                'options'   =>  self::post_type_options(),
                'default'  => '',
                'desc_tip' => true,
                'advanced' => false,
                'group'     => __("General","geodirectory")
        );
        $args['show_login']  = array(
            'title' => __("Show the login and register links if required.", 'geodirectory'),
            'type' => 'checkbox',
            'desc_tip' => true,
            'value'  => '1',
            'default'  => '1',
            'advanced' => false,
            'group'     => __("Login","geodirectory")
        );
        $args['login_msg']  = array(
            'title' => __('Login Message', 'geodirectory'),
            'desc' => __('The message to show if login is required.', 'geodirectory'),
            'type' => 'text',
            'placeholder'  => __( 'You must login to post.', 'geodirectory' ),
            'desc_tip' => true,
            'advanced' => false,
            'group'     => __("Login","geodirectory")
        );
        $args['container']  = array(
            'title' => __('Replace container', 'geodirectory'),
            'desc' => __('When submitted the response message will replace the add listing page content, you can set a different container to replace here, eg: .page-content', 'geodirectory'),
            'type' => 'text',
            'placeholder'  => __( '.page-content', 'geodirectory' ),
            'desc_tip' => true,
            'advanced' => true,
            'group'     => __("General","geodirectory")
        );
        $args['mapzoom'] = array(
            'type'        => 'select',
            'title'       => __( 'Map Zoom level:', 'geodirectory' ),
            'desc'        => __( 'This is the zoom level of the map, `auto` is recommended.', 'geodirectory' ),
            'options'     => array_merge( array( '0' => __( 'Auto', 'geodirectory' ) ), range( 1, 19 ) ),
            'placeholder' => '',
            'desc_tip'    => true,
            'default'     => '0',
            'advanced'    => true,
            'group'     => __("General","geodirectory")
        );

        if($design_style ){
            $args['label_type']  = array(
                'title' => __('Label type', 'geodirectory'),
                'desc' => __('Select the label type for inputs.', 'geodirectory'),
                'type' => 'select',
                'options'   =>  array(
                    'horizontal'    => __("Horizontal","geodirectory"),
                    'top'    => __("Top","geodirectory"),
                    'floating'    => __("Floating","geodirectory"),
                    'hidden'    => __("Hidden","geodirectory"),
                ),
                'default'  => '',
                'desc_tip' => true,
                'advanced' => false,
                'group'     => __("General","geodirectory")
            );

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


            $args = $args + $arguments;
        }


        return $args;
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
		global $geodir_label_type;

		// Some theme renders add listing shortcode in search page results.
		/**
		 * @since 2.0.0.68
		 */
		$output = apply_filters( 'geodir_pre_add_listing_shortcode_output', NULL, $args, $widget_args, $content );
		if ( $output !== NULL ) {
			return $output;
		}

		$design_style = geodir_design_style();

		$default_post_type = geodir_add_listing_default_post_type();

		$defaults = array(
			'pid'           => '',
			'listing_type'  => $default_post_type,
			'login_msg'     => __( 'You must login to post.', 'geodirectory' ),
			'show_login'    => true,
			'container'     => '',
			'mapzoom'       => '0',
			'label_type'    => 'horizontal',
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
		);

		$params = wp_parse_args( $args,$defaults);

		if ( empty( $params['label_type'] ) ) {
			$params['label_type'] = $defaults['label_type'];
		}

		// Set the label type.
		$geodir_label_type = esc_attr( $params['label_type'] );

		if ( isset( $args['post_type'] ) && ! empty( $args['post_type'] ) ) {
			$params['listing_type'] = $args['post_type'];
		}

		if ( ! isset( $args['login_msg'] ) || $args['login_msg'] == '' ) {
			$params['login_msg'] = $defaults['login_msg'];
		}

		if ( ! empty( $_REQUEST['pid'] ) && $post_type = get_post_type( absint( $_REQUEST['pid'] ) ) ) {
			$params['pid'] = absint( $_REQUEST['pid'] );
			$params['listing_type'] = $post_type;
		} else if ( isset( $_REQUEST['listing_type'] ) ) {
			$params['listing_type'] = sanitize_text_field( $_REQUEST['listing_type'] );
		}

		// Check if CPT is disabled add listing.
		if ( ! geodir_add_listing_check_post_type( $params['listing_type'] ) ) {
			$message = __( 'Adding listings is disabled for this post type.', 'geodirectory' );
			/**
			 * Filter the message for post type add listing disabled.
			 *
			 * @since 2.0.0.56
			 *
			 * @param string $message Message for add listing disabled.
			 * @param string $listing_type The post type.
			 */
			$message = apply_filters( 'geodir_add_listing_disabled_message', $message, $params['listing_type'] );

			return $design_style ? aui()->alert( array( 'type'=> 'warning', 'content'=> $message ) ) : $message;
		}

		if ( $this->is_preview() ) {
			return $design_style ? $this->get_dummy_preview( $params ) : '';
		}

		foreach ( $params as $key => $value ) {
			$_REQUEST[ $key ] = $value;
		}

		$user_id = get_current_user_id();

		ob_start();
		if ( ! $user_id && !geodir_get_option( 'post_logged_out' ) ) {
			echo geodir_notification( array( 'info' => $params['login_msg'] ) );
			if ( $params['show_login'] ) {
				echo "<br />";
				echo GeoDir_User::login_link();
			}
		} else if ( ! $user_id && ! get_option( 'users_can_register' ) ) {
			echo geodir_notification( array( 'error' => __( 'User registration is disabled, please login to continue.', 'geodirectory' ) ) );
		} else {
			// Enqueue widget scripts on call.
			geodir_widget_enqueue_scripts( $params, $this );

			GeoDir_Post_Data::add_listing_form($params);
		}

		// Reset the label type.
		$geodir_label_type = '';

		return ob_get_clean();
	}

    /**
     * Get the post type options for search.
     *
     * @return array
     */
    public function post_type_options(){
        $options = array(''=>__('Auto','geodirectory'));

        $post_types = geodir_get_posttypes('options-plural');
        if(!empty($post_types)){
            $options = array_merge($options,$post_types);
        }

        //print_r($options);

        return $options;
    }

    public function get_dummy_preview($params){
        global $geodir_label_type;

        // wrap class
        $wrap_class = geodir_build_aui_class($params);


        $output = '<div class="'.$wrap_class.'">';

        $output .= aui()->alert(array(
                'type'=> 'info',
                'content'=> __("This is a simple preview for the add listing form.","geodirectory")
            )
        );

        $output .= aui()->input(
            array(
                'name'              => 'demo-title',
                'required'          => !empty($cf['is_required']) ? true : false,
                'label'              => __("Title", 'geodirectory'),
                'label_type'       => !empty($geodir_label_type) ? $geodir_label_type : 'horizontal',
                'type'              => 'text',
                'placeholder'       => esc_html__( "Enter the listing title", 'geodirectory'),
                'class'             => '',
                'value'             => 'Example title',
                'help_text'         => __("Enter the title for the listing.", 'geodirectory'),
            )
        );

        $output .= aui()->textarea(array(
            'name'       => 'demo-description',
            'class'      => '',
            'placeholder'=> esc_html__( "Description text", 'geodirectory'),
            'label_type'       => !empty($geodir_label_type) ? $geodir_label_type : 'horizontal',
            'label'      => __("Description", 'geodirectory'),
            'no_wrap'    => false,
            'rows'      => 8,
            'wysiwyg'   => false,
            'help_text'        => esc_html__( "Enter a description for your listing.", 'geodirectory'),
        ));


        $output .= "</div>";

        return $output;
    }

    /**
     * Load conditional fields JS.
     *
     * @since 2.1.1.0
     *
     * @return mixed
     */
    public function enqueue_scripts() {
        global $aui_conditional_js;

        // Don't load JS again.
        if ( empty( $aui_conditional_js ) && geodir_design_style() && class_exists( 'AyeCode_UI_Settings' ) ) {
            $aui_settings = AyeCode_UI_Settings::instance();

            if ( is_callable( array( $aui_settings, 'conditional_fields_js' ) ) ) {
                $conditional_fields_js = $aui_settings->conditional_fields_js();

                if ( ! empty( $conditional_fields_js ) ) {
                    $aui_conditional_js = wp_add_inline_script( 'geodir-add-listing', $conditional_fields_js );
                }
            }
        }
    }
}
