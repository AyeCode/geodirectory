<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * GeoDirectory Search widget.
 *
 * @since 1.0.0
 */
class GeoDir_Widget_Loop_Actions extends WP_Super_Duper {

    /**
     * Register the advanced search widget with WordPress.
     *
     */
    public function __construct() {


        $options = array(
            'textdomain'    => GEODIRECTORY_TEXTDOMAIN,
            'block-icon'    => 'admin-site',
            'block-category'=> 'geodirectory',
            'block-keywords'=> "['loop','actions','geodir']",
            'class_name'    => __CLASS__,
            'base_id'       => 'gd_loop_actions', // this us used as the widget id and the shortcode id.
            'name'          => __('GD > Loop Actions','geodirectory'), // the name of the widget.
            'widget_ops'    => array(
                'classname'   => 'geodir-loop-actions-container '.geodir_bsui_class(), // widget class
                'description' => esc_html__('Shows the archive loop actions such as sort by and grid view,  only used on Archive template page, usually above `gd_loop`.','geodirectory'), // widget description
                'geodirectory' => true,
            ),
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

            $options['arguments'] = $arguments;
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

        $defaults = array(
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

        $args = wp_parse_args( $args, $defaults );
        
        ob_start();
        if(geodir_is_post_type_archive() ||  geodir_is_taxonomy() ||  geodir_is_page('search') || $this->is_preview() ){
            geodir_loop_actions($args);
        }
        return ob_get_clean();
    }

}