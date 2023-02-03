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

        $design_style = geodir_design_style();

        $options = array(
            'textdomain'    => GEODIRECTORY_TEXTDOMAIN,
            'block-icon'    => 'admin-site',
            'block-wrap'    => '', // the element to wrap the block output in. , ie: div, span or empty for no wrap
            'block-category'=> 'geodirectory',
            'block-keywords'=> "['archive','section','geodir']",
            'class_name'    => __CLASS__,
            'base_id'       => 'gd_archive_item_section', // this us used as the widget id and the shortcode id.
            'name'          => __('GD > Archive Item Section','geodirectory'), // the name of the widget.
            'no_wrap'       => true,
            'widget_ops'    => array(
                'classname'   => 'geodir-archive-item-section-container '.geodir_bsui_class(), // widget class
                'description' => esc_html__('This provides opening and closing sections to be able to wrap output and split the archive item template into left and right.','geodirectory'), // widget description
                'geodirectory' => true,
            ),
            'arguments'     => array(
                'type'  => array(
                    'title' => __('Open / Close', 'geodirectory'),
                    'desc' => __('This is the opening or closing section type.', 'geodirectory'),
                    'type' => 'select',
                    'options'   =>  array(
                        "open" => __('Open', 'geodirectory'),
                        "close" => __('Close', 'geodirectory'),
                    ),
                    'default'  => 'open',
                    'desc_tip' => true,
                    'advanced' => false
                ),
                'position'  => array(
                    'title' => __('Section type', 'geodirectory'),
                    'desc' => __('This is the section type', 'geodirectory'),
                    'type' => 'select',
                    'options'   => $design_style ? array(
                        "left" => __('Media (floats left on list view)', 'geodirectory'),
                        "right" => __('Body (floats right on list view)', 'geodirectory'),
                        "header" => __('Header', 'geodirectory'),
                        "footer" => __('Footer', 'geodirectory'),
                    ) : array(
                        "left" => __('Left', 'geodirectory'),
                        "right" => __('Right', 'geodirectory'),
                    ),
                    'default'  => 'left',
                    'desc_tip' => true,
                    'advanced' => false
                ),
            )
        );

        if ( $design_style ) {
            $options['arguments']['bg']  = array(
                'title' => __('Background color', 'geodirectory'),
                'desc' => __('Select the the background color.', 'geodirectory'),
                'type' => 'select',
                'options'   =>  array(
                                    "" => __('Default', 'geodirectory'),
                                )+geodir_aui_colors(true),
                'default'  => '',
                'desc_tip' => true,
                'advanced' => false,
                'group'     => __("Design","geodirectory"),
                'element_require' => '[%type%]=="open"',
            );

            $options['arguments']['border']  = array(
                'title' => __('Border separator color', 'geodirectory'),
                'desc' => __('Select the border separator color.', 'geodirectory'),
                'type' => 'select',
                'options'   =>  array(
                                    "" => __('Default', 'geodirectory'),
                                    "none" => __('None', 'geodirectory'),
                                )+geodir_aui_colors(true),
                'default'  => '',
                'desc_tip' => true,
                'advanced' => false,
                'group'     => __("Design","geodirectory"),
                'element_require' => '[%type%]=="open" && ([%position%]=="header" || [%position%]=="footer")',
            );

            $options['arguments']['font_size']  = array(
                'title' => __('Font size', 'geodirectory'),
                'desc' => __('Select the font size', 'geodirectory'),
                'type' => 'select',
                'options'   =>  array(
                                    "" => __('Default', 'geodirectory'),
                                    "small" => __('Small', 'geodirectory'),
                                ),
                'default'  => '',
                'desc_tip' => true,
                'advanced' => false,
                'group'     => __("Design","geodirectory"),
                'element_require' => '[%type%]=="open"',
            );

            // footer padding
            $overwrite = array(
                'group'     => __("Design","geodirectory"),
                'element_require' => '[%type%]=="open"',
            );
            $options['arguments']['pt']  = geodir_get_sd_padding_input('pt', $overwrite );
            $options['arguments']['pr']  = geodir_get_sd_padding_input('pr', $overwrite );
            $options['arguments']['pb']  = geodir_get_sd_padding_input('pb', $overwrite );
            $options['arguments']['pl']  = geodir_get_sd_padding_input('pl', $overwrite );

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
        global $aui_bs5;

        $defaults = array(
            'type' => 'open',
            'position' => 'left',
        );

        $args = wp_parse_args( $args, $defaults );
        $output = '';

        $design_style = geodir_design_style();

        if ( isset( $args['type'] ) && $args['type'] == 'open' ) {
            if ( $design_style ) {
                // wrapper class
                $wrap_class = geodir_build_aui_class($args);

                // border separator
                if ( ! empty( $args['border'] ) && $args['border'] != 'none' ) {
                    $wrap_class .= ( $aui_bs5 ? 'border-start-0 border-end-0' : 'border-left-0 border-right-0' ) . " border-bottom-0";
                }

                // font size
                if ( ! empty( $args['font_size'] ) && $args['font_size'] == 'small' ) {
                    $wrap_class .= " small";
                }

                $class = ! empty( $args['class'] ) ? esc_attr( $args['class'] ) : '';
                $position = '';
                if ( empty( $args['position'] ) || $args['position'] == 'left' ) {
                    $position =  'card-img-top overflow-hidden position-relative';
                } else if ( $args['position'] == 'right' ) {
                    $position = 'card-body p-2';
                } else if ( $args['position'] == 'header' ) {
                    $position = 'card-header p-2';
                } else if ( $args['position'] == 'footer' ) {
                    $position = 'card-footer p-2';
                }
                $output = '<div class="' . $position . ' ' . $class . ' ' . $wrap_class . '">';
            } else {
                $class = ! empty( $args['class'] ) ? esc_attr( $args['class'] ) : '';
                $position = isset( $args['position'] ) && $args['position'] == 'left' ? 'left' : 'right';
                $output = '<div class="gd-list-item-' . $position . ' ' . $class . '">';
            }
        } else if ( isset( $args['type'] ) && $args['type'] == 'close' ) {
            $output = "</div>";
        }

        // if block demo return empty to show placeholder text
        if ( $this->is_block_content_call() && geodir_is_archive_item_template_page() ) {
            $type = ! empty( $args['type'] ) ? esc_attr( $args['type'] ) : '';
            if ( $design_style ) {
                $position = '';
                if ( empty( $args['position'] ) || $args['position'] == 'left' ) {
                    $position =  'media';
                } else if ( $args['position'] == 'right' ) {
                    $position = 'body';
                } else if ( $args['position'] == 'header' ) {
                    $position = 'header';
                } else if ( $args['position'] == 'footer' ) {
                    $position = 'footer';
                }
            } else {
                $position = isset( $args['position'] ) && $args['position'] == 'left' ? 'left' : 'right';
            }

            $section_type = $type == 'open' ? __( 'closing', 'geodirectory' ) : __( 'opening', 'geodirectory' );
            $output = '<div style="background:#0185ba33;padding:10px;">' . wp_sprintf( __( 'Archive Item Section: <b>%s : %s</b> <small>(requires %s section to work)</small>', 'geodirectory' ), strtoupper( $type ), strtoupper( $position), $section_type ) . '</div>';
        }

        return $output;
    }
}