<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * GeoDirectory Search widget.
 *
 * @since 1.0.0
 */
class GeoDir_Widget_Archive_Item_Section extends WP_Super_Duper {

    /**
     * Register the advanced search widget with WordPress.
     *
     */
    public function __construct() {


        $options = array(
            'textdomain'    => GEODIRECTORY_TEXTDOMAIN,
            'block-icon'    => 'admin-site',
            'block-wrap'    => '', // the element to wrap the block output in. , ie: div, span or empty for no wrap
            'block-category'=> 'layout',
            'block-keywords'=> "['archive','section','geodir']",
            'class_name'    => __CLASS__,
            'base_id'       => 'gd_archive_item_section', // this us used as the widget id and the shortcode id.
            'name'          => __('GD > Archive Item Section','geodirectory'), // the name of the widget.
            'no_wrap'       => true,
            'widget_ops'    => array(
                'classname'   => 'geodir-archive-item-section-container', // widget class
                'description' => esc_html__('This provides opening and closing sections to be able to wrap output and split the archive item template into left and right.','geodirectory'), // widget description
                'geodirectory' => true,
            ),
            'arguments'     => array(
                'type'  => array(
                    'title' => __('Type:', 'geodirectory'),
                    'desc' => __('This is the opening or closing section type.', 'geodirectory'),
                    'type' => 'select',
                    'options'   =>  array(
                        "open" => __('Open', 'geodirectory'),
                        "close" => __('close', 'geodirectory'),
                    ),
                    'default'  => 'open',
                    'desc_tip' => true,
                    'advanced' => false
                ),
                'position'  => array(
                    'title' => __('Position:', 'geodirectory'),
                    'desc' => __('This is position of the section type.', 'geodirectory'),
                    'type' => 'select',
                    'options'   =>  array(
                        "left" => __('Left', 'geodirectory'),
                        "right" => __('Right', 'geodirectory'),
                    ),
                    'default'  => 'left',
                    'desc_tip' => true,
                    'advanced' => false
                ),
            )
        );


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
            'type' => 'open',
            'position' => 'left',
        );
//        print_r($args);
        $args = wp_parse_args( $args, $defaults );
        $output = '';

//        print_r($args);
        if(isset($args['type']) && $args['type']=='open'){
            $class = !empty($args['class']) ? esc_attr($args['class']) : '';
            $position = isset($args['position']) && $args['position']=='left' ? 'left' : 'right';
            $output = '<div class="gd-list-item-'.$position.' '.$class.'">';
        }elseif(isset($args['type']) && $args['type']=='close'){
            $output = "</div>";
        }

        // if block demo return empty to show placeholder text
        if($this->is_block_content_call()){
            $output = '';
            $type = !empty($args['type']) ? esc_attr($args['type']) : '';
            $position = isset($args['position']) && $args['position']=='left' ? 'left' : 'right';
            $section_type = $type=='open' ? __('closing','geodirectory') : __('opening','geodirectory');
            $output = '<div style="background:#0185ba33;padding: 10px;">'.sprintf( __('Archive Item Section: <b>%s : %s</b> <small>(requires %s section to work)</small>', 'geodirectory'),strtoupper($type),strtoupper($position),$section_type).'</div>';
        }

        return $output;
    }

}