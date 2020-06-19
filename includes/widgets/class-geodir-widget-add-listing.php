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
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['add','listing','geodir']",
            'block-output'   => array( // the block visual output elements as an array
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
                'classname'   => 'geodir-add-listing-container', // widget class
                'description' => esc_html__('Shows the GeoDirectory add listing form.','geodirectory'), // widget description
                'geodirectory' => true,
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

        return array(
            'post_type'  => array(
                'title' => __('Default Post Type:', 'geodirectory'),
                'desc' => __('The custom post types to show by default. Only used when there are multiple CPTs.', 'geodirectory'),
                'type' => 'select',
                'options'   =>  self::post_type_options(),
                'default'  => '',
                'desc_tip' => true,
                'advanced' => true
            ),
            'show_login'  => array(
                'title' => __("Show the login links if required.", 'geodirectory'),
                'type' => 'checkbox',
                'desc_tip' => true,
                'value'  => '1',
                'default'  => '1',
                'advanced' => true
            ),
            'login_msg'  => array(
                'title' => __('Login Message', 'geodirectory'),
                'desc' => __('The message to show if login is required.', 'geodirectory'),
                'type' => 'text',
                'placeholder'  => __( 'You must login to post.', 'geodirectory' ),
                'desc_tip' => true,
                'advanced' => true
            ),
            'container'  => array(
                'title' => __('Replace container', 'geodirectory'),
                'desc' => __('When submitted the response message will replace the add listing page content, you can set a different container to replace here, eg: .page-content', 'geodirectory'),
                'type' => 'text',
                'placeholder'  => __( '.page-content', 'geodirectory' ),
                'desc_tip' => true,
                'advanced' => true
            ),
            'mapzoom' => array(
                'type'        => 'select',
                'title'       => __( 'Map Zoom level:', 'geodirectory' ),
                'desc'        => __( 'This is the zoom level of the map, `auto` is recommended.', 'geodirectory' ),
                'options'     => array_merge( array( '0' => __( 'Auto', 'geodirectory' ) ), range( 1, 19 ) ),
                'placeholder' => '',
                'desc_tip'    => true,
                'default'     => '0',
                'advanced'    => true
            ),
        );
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
        // Some theme renders add listing shortcode in search page results.
        /**
         * @since 2.0.0.68
         */
        $output = apply_filters( 'geodir_pre_add_listing_shortcode_output', NULL, $args, $widget_args, $content );
        if ( $output !== NULL ) {
            return $output;
        }

        if(self::is_preview()){
            return '';
        }

        $default_post_type = geodir_add_listing_default_post_type();

        $defaults = array(
            'pid'           => '',
            'listing_type'  => $default_post_type,
            'login_msg'     => __( 'You must login to post.', 'geodirectory' ),
            'show_login'    => true,
            'container'     => '',
            'mapzoom'       => '0',
        );

        $params = wp_parse_args( $args,$defaults);

        if(isset($args['post_type']) && !empty($args['post_type'])){
            $params['listing_type'] = $args['post_type'];
        }

        if(!isset($args['login_msg']) || $args['login_msg']==''){
            $params['login_msg'] = $defaults['login_msg'];
        }

        if ( !empty( $_REQUEST['pid'] ) && $post_type = get_post_type( absint( $_REQUEST['pid'] ) ) ) {
            $params['pid'] = absint( $_REQUEST['pid'] );
            $params['listing_type'] = $post_type;
        } else if ( isset( $_REQUEST['listing_type'] ) ) {
            $params['listing_type'] = sanitize_text_field( $_REQUEST['listing_type'] );
        }

        // check if CPT is disabled add listing
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
			return apply_filters( 'geodir_add_listing_disabled_message', $message, $params['listing_type'] );
        }

        foreach ( $params as $key => $value ) {
            $_REQUEST[ $key ] = $value;
        }

        $user_id = get_current_user_id();

        ob_start();

        //
        if ( !$user_id && !geodir_get_option('post_logged_out')) {
            echo geodir_notification( array('login_msg'=>$params['login_msg']) );
            if ( $params['show_login'] ) {
                echo "<br />";
                echo GeoDir_User::login_link();
            }
        } elseif(!$user_id && !get_option( 'users_can_register' )){
            echo geodir_notification( array('add_listing_error'=>__('User registration is disabled, please login to continue.','geodirectory')) );
        }else {
            GeoDir_Post_Data::add_listing_form($params);
        }

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

}