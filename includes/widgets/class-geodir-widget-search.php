<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * GeoDirectory Search widget.
 *
 * @since 1.0.0
 */
class GeoDir_Widget_Search extends WP_Super_Duper {

    /**
     * Register the advanced search widget with WordPress.
     *
     * @since 2.0.0
     */
    public function __construct() {



        $options = array(
            'textdomain'    => GEODIRECTORY_TEXTDOMAIN,
            'block-icon'    => 'admin-site',
            'block-category'=> 'geodirectory',
            'block-keywords'=> "['search','geo','geodir']",

            'class_name'    => __CLASS__,
            'base_id'       => 'gd_search', // this us used as the widget id and the shortcode id.
            'name'          => __('GD > Search','geodirectory'), // the name of the widget.
            //'disable_widget'=> true,
            'widget_ops'    => array(
                'classname'   => 'geodir-search-container '.geodir_bsui_class(), // widget class
                'description' => esc_html__('Shows the GeoDirectory search bar.','geodirectory'), // widget description
                'geodirectory' => true,
            ),
            'arguments' => array() // keep this
        );

        $post_types =  $this->post_type_options();

        if(count($post_types) > 2){
            $options['arguments'] = array(
                'post_type'  => array(
                    'title' => __('Default Post Type:', 'geodirectory'),
                    'desc' => __('The custom post types to show by default. Only used when there are multiple CPTs.', 'geodirectory'),
                    'type' => 'select',
                    'options'   =>  $this->post_type_options(),
                    'default'  => '',
                    'desc_tip' => true,
                    'advanced' => true
                ),
                'post_type_hide'  => array(
                    'title' => __('Hide Post Type Selector:', 'geodirectory'),
                    'desc' => __('Hide the CPT selector (if not on search page) this can be used to setup a specific CPT search and not give the option to change the CPT.', 'geodirectory'),
                    'type' => 'checkbox',
                    'desc_tip' => true,
                    'value'  => '1',
                    'default'  => '',
                    'advanced' => true
                )
            );
        }

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
     * The Super block output function.
     *
     * @param array $args
     * @param array $widget_args
     * @param string $content
     *
     * @return mixed|string|void
     */
    public function output($args = array(), $widget_args = array(),$content = ''){

        ob_start();
        /**
         * @var bool $ajax_load Ajax load or not.
         * @var string $animation Fade or slide.
         * @var bool $slideshow Auto start or not.
         * @var int $controlnav 0 = none, 1 =  standard, 2 = thumbnails
         * @var bool $show_title If the title should be shown or not.
         * @var int/empty $limit If the number of images should be limited.
         */
        extract($args, EXTR_SKIP);


        // set the CPT to be used.
        if(isset($post_type) && $post_type && geodir_is_gd_post_type($post_type)){
            geodir_get_search_post_type($post_type);// set the post type
        }else{
            geodir_get_search_post_type();// set the post type
        }

        // set if the cpt selector should be hidden
        global $geodir_search_post_type_hide;
        if(isset($post_type_hide) && $post_type_hide){
            $geodir_search_post_type_hide = true;
        }


        $design_style = !empty($args['design_style']) ? esc_attr($args['design_style']) : geodir_design_style();
        $template = $design_style ? $design_style."/search-bar/form.php" : "listing-filter-form.php";

        $args = array(
            'wrap_class'    => geodir_build_aui_class($args)
        );

        echo geodir_get_template_html( $template , $args);

        // after outputing the search reset the CPT
        global $geodir_search_post_type;
        $geodir_search_post_type = '';
        $geodir_search_post_type_hide = false;

        return ob_get_clean();
    }


    /**
     * Get the post type options for search.
     *
     * @since 2.0.0
     *
     * @return array $options
     */
    public function post_type_options(){
        $options = array();
        $post_types = geodir_get_posttypes('options-plural');
        if(!empty($post_types)){
        $options = array(''=>__('Auto','geodirectory'));
            $options = array_merge($options,$post_types);
        }

        return $options;
    }

}