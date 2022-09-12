<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * GeoDirectory Search widget.
 *
 * @since 1.0.0
 */
class GeoDir_Widget_Single_Tabs extends WP_Super_Duper {

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
            'block-keywords'=> "['tabs','details','geodir']",
            'block-supports'=> array(
                'customClassName'   => false
            ),
            'block-outputx'   => array( // the block visual output elements as an array
                array(
                    'element' => 'div',
                    'title'   => __( 'Placeholder tabs', 'geodirectory' ),
                    'class'   => '[%className%]',
                    'style'   => '{background: "#eee",width: "100%", height: "250px", position:"relative"}',
                    array(
                        'element' => 'i',
                        'if_class'   => '[%show_as_list%]=="1" ? "fas fa-align-justify gd-fadein-animation" : "fas fa-columns gd-fadein-animation"',
                        'style'   => '{"text-align": "center", "vertical-align": "middle", "line-height": "250px", "height": "100%", width: "100%","font-size":"140px",color:"#aaa"}',
                    ),
                ),
            ),
            'class_name'    => __CLASS__,
            'base_id'       => 'gd_single_tabs', // this us used as the widget id and the shortcode id.
            'name'          => __('GD > Single Tabs','geodirectory'), // the name of the widget.
            'widget_ops'    => array(
                'classname'   => 'geodir-single-tabs-container '.geodir_bsui_class(), // widget class
                'description' => esc_html__('Shows the current posts tabs information.','geodirectory'), // widget description
                'geodirectory' => true,
            ),
            'arguments'     => array(
                'show_as_list'  => array(
                    'title' => __('Show as list', 'geodirectory'),
                    'desc' => __('This will show the tabs as a list and not as tabs.', 'geodirectory'),
                    'type' => 'checkbox',
                    'desc_tip' => true,
                    'value'  => '1',
                    'default'  => '',
                    'advanced' => false
                ),
                'output'  => array(
                    'title' => __('Output Type', 'geodirectory'),
                    'desc' => __('What parts should be output.', 'geodirectory'),
                    'type' => 'select',
                    'options'   =>  array(
                        "" => __('Default', 'geodirectory'),
                        "head" => __('Head only', 'geodirectory'),
                        "body" => __('Body only', 'geodirectory'),
                        "json" => __('JSON Array (developer option)', 'geodirectory'),
                    ),
                    'default'  => '',
                    'desc_tip' => true,
                    'advanced' => true
                )
            )
        );

        $design_style = geodir_design_style();

        if($design_style){
            $options['arguments']['tab_style'] = array(
                'title' => __('Tab style', 'geodirectory'),
                'desc' => __('Output tab head as standard tabs or as pills.', 'geodirectory'),
                'type' => 'select',
                'options'   =>  array(
                    "" => __('Tabs', 'geodirectory'),
                    "pills" => __('Pills', 'geodirectory'),
                ),
                'default'  => '',
                'desc_tip' => true,
                'advanced' => false,
//                'group'     => __("Design","geodirectory")
            );

            $options['arguments']['disable_greedy'] = array(
                'title' => __('Disable Greedy Menu', 'geodirectory'),
                'desc' => __('Greedy menu prevents a large menu falling onto another line by adding a dropdown select.', 'geodirectory'),
                'type' => 'checkbox',
                'desc_tip' => true,
                'value'  => '1',
                'default'  => '',
                'advanced' => false,
//                'group'     => __("Design","geodirectory")
            );
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
    public function output( $args = array(), $widget_args = array(), $content = '' ) {
        global $preview, $post, $gd_post, $gd_single_tabs_array;

	    $is_preview = $this->is_preview();

        if ( ! isset( $post->ID ) && !$is_preview ) {
            return '';
        }

        // Default options
        $defaults = array(
            'show_as_list' => '0', // 0 =  all
            'output' => '',
            'tab_style' => '',
            'disable_greedy' => '',
        );

        /**
         * Parse incoming $args into an array and merge it with $defaults
         */
        $args = wp_parse_args( $args, $defaults );

        // Check if we have been here before
        $tabs_array = ! empty( $gd_single_tabs_array ) ? $gd_single_tabs_array : array();

        $post_id = ! empty( $gd_post->ID ) ? absint( $gd_post->ID ) : 0;
        if ( $post_id && wp_is_post_revision( $post_id ) ) {
            $post_id = wp_get_post_parent_id( $post_id );
        }
        $post_type = $post_id ? get_post_type( $post_id ) : 'gd_place';

        if ( empty( $tabs_array ) ) {
            // Get the tabs head
            $tabs = self::get_tab_settings( $post_type );

            // Get the tab contents first so we can decide to output the tab head
            $tabs_content = array();
            foreach( $tabs as $tab ) {
                $tabs_content[ $tab->id . "tab" ] = self::tab_content( $tab );
            }

            // Setup the array
            if ( ! empty( $tabs ) ) {
                foreach( $tabs as $tab ) {
                    if ( $tab->tab_level > 0 ) {
                        continue;
                    }

                    if ( empty( $tabs_content[ $tab->id . "tab" ] ) ) {
                        continue;
                    }

                    $tab->tab_content_rendered = $tabs_content[ $tab->id . "tab" ];
                    $tabs_array[] = (array) $tab;
                }
            }

            /**
             * Filter the listing tabs results array.
             *
             * @since 2.0.0.77
             *
             * @param array $tabs_array Tabs array.
             * @param array $gd_post The post.
             */
            $tabs_array = apply_filters( 'geodir_single_post_tabs_array', $tabs_array, $gd_post );

            $gd_single_tabs_array = $tabs_array;
        }

        // Output JSON
        if ( $args['output'] == 'json' ) {
            return json_encode( $tabs_array );
        }

        $design_style = geodir_design_style();
        $template = $design_style ? $design_style."/single/tabs.php" : "legacy/single/tabs.php";

        $args = array(
            'args'  => $args,
            'tabs_array' => $tabs_array,
        );
        $output = geodir_get_template_html( $template, $args );

        return $output;
    }

    /**
     * Get tab content.
     *
     * @since 2.0.0
     *
     * @param object $tab Tab object.
     * @param bool $child Optional. Tab child. Default false.
     * @return string
     */
    public function tab_content( $tab, $child = false ) {
        ob_start();

        do_action( 'geodir_single_tab_content_before', $tab, array() );

        // Main content
        if ( ! empty( $tab->tab_content ) ) { // override content
            $content = geodir_replace_variables( stripslashes( $tab->tab_content ));
            echo do_shortcode( $content );
        } elseif ( $tab->tab_type == 'meta' ) { // meta info
            echo do_shortcode('[gd_post_meta key="' . $tab->tab_key . '" show="value"]');
        } elseif ( $tab->tab_type == 'standard' ) { // meta info
            if ( $tab->tab_key == 'reviews' ) {
                comments_template();
            } else {
                do_action( 'geodir_standard_tab_content', $tab );
            }
        }

        do_action( 'geodir_single_tab_content_after', $tab, array() );

        echo self::tab_content_child( $tab );

        $content = ob_get_clean();

        /**
         * Filter the listing tab content.
         *
         * @since 2.0.0.77
         *
         * @param string $content Tab content.
         * @param object $tab Tab object.
         * @param bool $child True if child tab else False.
         */
        return apply_filters( 'geodir_single_post_tab_content', $content, $tab, $child );
    }

    /**
     * Get tab content child.
     *
     * @since 2.0.0
     *
     * @param object $tab Tab object.
     * @return string
     */
    public function tab_content_child( $tab ) {
        global $post;

        $post_type = $post->post_type;
        $tabs = self::get_tab_settings( $post_type );
        $parent_id = $tab->id;

        ob_start();

        foreach ( $tabs as $child_tab ) {
            if ( $child_tab->tab_parent == $parent_id ) {
                ob_start();

                do_action( 'geodir_single_tab_content_before', $child_tab, $tab );

                if ( ! empty( $child_tab->tab_content ) ) { // override content
                    $_content = geodir_replace_variables( stripslashes( $child_tab->tab_content ));
                    echo do_shortcode( $_content );
                } elseif ( $child_tab->tab_type == 'meta' ) { // meta info
                    echo do_shortcode( '[gd_post_meta key="' . $child_tab->tab_key . '"]' );
                } elseif ( $child_tab->tab_type == 'fieldset' ) { // meta info
                    self::output_fieldset( $child_tab );
                } elseif ( $child_tab->tab_type == 'standard' ) { // meta info
                    if ( $child_tab->tab_key == 'reviews' ) {
                        comments_template();
                    }
                }

                do_action( 'geodir_single_tab_content_after', $child_tab, $tab );

                $child_content = ob_get_clean();

                /**
                 * Filter the listing child tab content.
                 *
                 * @since 2.0.0.77
                 *
                 * @param string $child_content Child tab content.
                 * @param object $child_tab Child tab object.
                 * @param object $tab Parent tab object.
                 */
                 echo apply_filters( 'geodir_single_post_child_tab_content', $child_content, $child_tab, $tab );
            }
        }
        $content = ob_get_clean();

       /**
         * Filter the listing tab content.
         *
         * @since 2.0.0.77
         *
         * @param string $content Tab content.
         * @param object $tab Parent tab object.
         */
        return apply_filters( 'geodir_single_post_child_tabs_content', $content, $tab );
    }

    /**
     * Fieldset html output.
     *
     * @since 2.0.0
     *
     * @param object $tab Tab object.
     * @return string
     */
    public function output_fieldset($tab){
        ob_start();
        echo '<div class="geodir_post_meta  gd-fieldset">';
        echo "<h4>";
        if($tab->tab_icon){
            echo '<i class="fas '.esc_attr($tab->tab_icon).'" aria-hidden="true"></i>';
        }
        if($tab->tab_name){
            esc_attr_e($tab->tab_name,'geodirectory');
        }
        echo "</h4>";
        echo "</div>";

        return ob_get_clean();
    }

    /**
     * Get tab settings.
     *
     * @since 2.0.0
     *
     * @param string $post_type Post type.
     *
     * @global object $wpdb WordPress Database object.
     * @global object $geodir_tab_layout_settings Geo directory tab layout settings object.
     *
     * @return array|object $tabs.
     */
    public function get_tab_settings($post_type){

        // check if block preview
        $block_preview = $this->is_block_content_call();

        if($block_preview){
            return $this->block_preview_dummy_tabs();
        }

        global $wpdb,$geodir_tab_layout_settings;

        if($geodir_tab_layout_settings){
            $tabs = $geodir_tab_layout_settings;
        }else{
            $geodir_tab_layout_settings = $tabs = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".GEODIR_TABS_LAYOUT_TABLE." WHERE post_type=%s ORDER BY sort_order ASC",$post_type));
        }

        /**
         * Get the tabs output settings.
         *
         * @param array $tabs The array of stdClass settings for the tabs output.
         * @param string $post_type The post type.
         */
        return apply_filters('geodir_tab_settings',$tabs,$post_type);
    }

    public function block_preview_dummy_tabs(){
        $tabs = array();
        $text = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. ";
        $dummy_tabs = array(
            'fas fa-home',
            'fas fa-image',
            'fas fa-globe-americas',
            'fas fa-comments',
            'fas fa-bed',
            'fas fa-calendar-alt',
            'fas fa-bullhorn',
            'fas fa-cloud',
        );

        $count = 1;
        foreach($dummy_tabs as $dummy_tab){
            // tab
            $tab = new stdClass();
            $tab->id = $count;
            $tab->post_type = 'gd_place';
            $tab->sort_order = $count;
            $tab->tab_layout = 'post';
            $tab->tab_parent = '';
            $tab->tab_type = 'standard';
            $tab->tab_level = 0;
            $tab->tab_name = sprintf( __( 'Demo tab %d', 'geodirectory' ),$count);
            $tab->tab_icon = $dummy_tab;
            $tab->tab_key = 'dummy_'.$count;
            $tab->tab_content = sprintf( __( 'Demo tab content %d.', 'geodirectory' ),$count)." ".str_repeat($text,5);

            $tabs[] = $tab;
            $count++;
        }

        return $tabs;
    }

}
