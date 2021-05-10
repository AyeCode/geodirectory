<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * GeoDirectory Search widget.
 *
 * @since 1.0.0
 */
class GeoDir_Widget_Loop_Paging extends WP_Super_Duper {

    /**
     * Register the advanced search widget with WordPress.
     *
     */
    public function __construct() {


        $options = array(
            'textdomain'    => GEODIRECTORY_TEXTDOMAIN,
            'block-icon'    => 'admin-site',
            'block-category'=> 'geodirectory',
            'block-keywords'=> "['loop','paging','geodir']",
            'class_name'    => __CLASS__,
            'base_id'       => 'gd_loop_paging', // this us used as the widget id and the shortcode id.
            'name'          => __('GD > Loop Paging','geodirectory'), // the name of the widget.
            'widget_ops'    => array(
                'classname'   => 'geodir-loop-paging-container '.geodir_bsui_class(), // widget class
                'description' => esc_html__('Shows the pagination links if the current query has multiple pages of results.','geodirectory'), // widget description
                'geodirectory' => true,
            ),
            'arguments'     => array(
                'show_advanced'  => array(
                    'title' => __('Show Advanced pagination:', 'geodirectory'),
                    'desc' => __('This will add extra pagination info like `Showing listings x-y of z` before/after pagination.', 'geodirectory'),
                    'type' => 'select',
                    'options'   =>  array(
                        "" => __('Never', 'geodirectory'),
                        "before" => __('Before', 'geodirectory'),
                        "after" => __('After', 'geodirectory'),
                    ),
                    'desc_tip' => true,
                    'advanced' => false
                )
            )
        );

        $design_style = geodir_design_style();

        if ( $design_style ) {
            $arguments = array();

            // mid_size
            $arguments['mid_size'] = array(
                'type' => 'select',
                'title' => __( 'Middle Pages Numbers:', 'geodirectory' ),
                'desc' => __( 'How many numbers to either side of the current pages. Default 2.', 'geodirectory' ),
                'options' => array(
                    "" => __( 'Default (2)', 'geodirectory' ), "0" => "0", "1" => "1", "2" => "2", "3" => "3", "4" => "4", "5" => "5", "6" => "6", "7" => "7", "8" => "8", "9" => "9", "10" => "10"
                ),
                'default' => '',
                'desc_tip' => true,
                'advanced' => false
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
        global $geodir_is_widget_listing;

        $defaults = array(
            'show_advanced' => '',
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
            'mid_size' => '',
        );
        $args = wp_parse_args( $args, $defaults );
        if(!empty($args['show_advanced'])){
            global $gd_advanced_pagination;
            $gd_advanced_pagination = $args['show_advanced'];
        }
        
        // preview
        $is_preview = $this->is_preview();
        if(  $is_preview ){
            $args['preview'] = true;
            $args['total'] = 3;
        }

        if ( $args['mid_size'] === '' ) {
            $args['mid_size'] = 2;
        }

        // Mobile devices
        if ( wp_is_mobile() ) {
            $args['mid_size'] = 0; // On mobile devices.
        }

        ob_start();
        if(geodir_is_post_type_archive() ||  geodir_is_taxonomy() ||  geodir_is_page('search') || $geodir_is_widget_listing ||  $is_preview ){
            geodir_loop_paging($args);
        }
        return ob_get_clean();
    }

}