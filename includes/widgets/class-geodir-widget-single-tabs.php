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
            'block-category'=> 'widgets',
            'block-keywords'=> "['tabs','details','geodir']",
            'block-output'   => array( // the block visual output elements as an array
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
                'classname'   => 'geodir-single-tabs-container', // widget class
                'description' => esc_html__('Shows the current posts tabs information.','geodirectory'), // widget description
                'geodirectory' => true,
            ),
            'arguments'     => array(
                'show_as_list'  => array(
                    'title' => __('Show as list:', 'geodirectory'),
                    'desc' => __('This will show the tabs as a list and not as tabs.', 'geodirectory'),
                    'type' => 'checkbox',
                    'desc_tip' => true,
                    'value'  => '1',
                    'default'  => '',
                    'advanced' => true
                ),
                'output'  => array(
                    'title' => __('Output Type:', 'geodirectory'),
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

        if ( ! isset( $post->ID ) ) {
            return '';
        }

        // Default options
        $defaults = array(
            'show_as_list' => '0', // 0 =  all
            'output' => '',
        );

        /**
         * Parse incoming $args into an array and merge it with $defaults
         */
        $args = wp_parse_args( $args, $defaults );

        // Check if we have been here before
        $tabs_array = ! empty( $gd_single_tabs_array ) ? $gd_single_tabs_array : array();

        $post_type = $post->post_type;

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

        // Output start
        ob_start();

        if ( ! empty( $tabs_array ) ) {
            echo '<div class="geodir-tabs" id="gd-tabs">';

            // Tabs head
            if ( ! $args['show_as_list'] && $args['output'] != 'body' || ( $args['show_as_list'] && $args['output'] == 'head' ) ) {
                ?>
                <div id="geodir-tab-mobile-menu">
                    <span class="geodir-mobile-active-tab"></span>
                    <i class="fas fa-sort-down" aria-hidden="true"></i>
                </div>
                <?php
                echo '<dl class="geodir-tab-head">';

                $count = 0;
                foreach( $tabs_array as $tab ) {
					// Tab icon
					$icon = trim( $tab['tab_icon'] );
					if ( geodir_is_fa_icon( $icon ) ) {
						$tab_icon = '<i class="' . esc_attr( $icon ) . '" aria-hidden="true"></i>';
					} elseif ( strpos( $icon, 'fa-' ) === 0 ) {
						$tab_icon = '<i class="fas ' . esc_attr( $icon ) . '" aria-hidden="true"></i>';
					} else {
						$tab_icon = '';
					}

                    $tab_class = $count==0 ? 'geodir-tab-active' :'';
                    $data_status = '';//$count==0 ? 'data-status="enable"' : '';
                    echo '<dt></dt> <!-- added to comply with validation -->';
                    echo '<dd class="'.$tab_class .'">';
                    $href = $args['show_as_list'] ? ' href="#' . esc_attr( $tab['tab_key'] ) . '" ' : '';
                    echo '<a data-tab="#' . esc_attr( $tab['tab_key'] ) . '" data-status="enable" '. $href .'>';
                    echo $tab_icon;
                    echo stripslashes( esc_attr__( $tab['tab_name'], 'geodirectory' ) ).'</a>';
                    echo '</dd>';
                    $count++;
                }

                echo '</dl>';
            }

            if ( $args['output'] != 'head' ) {
                // Tabs content
                $list_class =  $args['show_as_list'] ? 'geodir-tabs-as-list' : '';
                echo '<ul class="geodir-tabs-content geodir-entry-content ' . $list_class . '">';
                foreach( $tabs_array as $tab ) {
                    $add_tab = $args['show_as_list'] ? 'List' : 'Tab';
                    echo '<li id="' . esc_attr( $tab['tab_key'] ) . $add_tab . '" >';
                    echo "<span id='" . esc_attr( $tab['tab_key'] ) . "' class='geodir-tabs-anchor'></span>";
                    if ( $args['show_as_list'] ) {
                        $tab_icon = '';

                        if ( $tab['tab_icon'] ) {
                            $tab_icon = '<i class=" ' . esc_attr( $tab['tab_icon'] ) . '" aria-hidden="true"></i>';
                        }
                        $tab_title = '<span class="gd-tab-list-title" ><a href="#' . esc_attr( $tab['tab_key'] ) . '">' . $tab_icon . esc_attr__( $tab['tab_name'], 'geodirectory' ) . '</a></span><hr />';

                        /**
                         * Filter the tab list title html.
                         *
                         * @since 1.6.1
                         *
                         * @param string $tab_title      The html for the tab title.
                         * @param array $tab             The array of values including title text.
                         */
                        echo apply_filters( 'geodir_tab_list_title', $tab_title, (object)$tab );
                    }
                    echo '<div id="geodir-tab-content-' . esc_attr( $tab['tab_key'] ) . '" class="hash-offset"></div>';

                    echo $tab['tab_content_rendered'];

                    echo '</li>';
                }
                echo '</ul>';
            }

            echo '</div>';

            if ( ! $args['show_as_list']) { ?>
                <script type="text/javascript">/* <![CDATA[ */
					if (window.location.hash && window.location.hash.indexOf('&') === -1 && jQuery(window.location.hash + 'Tab').length) {
						hashVal = window.location.hash;
					} else {
						hashVal = jQuery('dl.geodir-tab-head dd.geodir-tab-active').find('a').attr('data-tab');
					}
					jQuery('dl.geodir-tab-head dd').each(function() {
						//Get all tabs
						var tabs = jQuery(this).children('dd');
						var tab = '';
						tab = jQuery(this).find('a').attr('data-tab');
						if (hashVal != tab) {
							jQuery(tab + 'Tab').hide();
						}
					});
                /* ]]> */</script>
                <?php
            }
        }

        return ob_get_clean();
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
    
}