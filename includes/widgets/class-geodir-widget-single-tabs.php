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
            'class_name'    => __CLASS__,
            'base_id'       => 'gd_single_tabs', // this us used as the widget id and the shortcode id.
            'name'          => __('GD > Single Tabs','geodirectory'), // the name of the widget.
            'widget_ops'    => array(
                'classname'   => 'geodir-single-tabs-container', // widget class
                'description' => esc_html__('Shows the current post`s tabs information.','geodirectory'), // widget description
                'geodirectory' => true,
            ),
            'arguments'     => array(
                'show_as_list'  => array(
                    'title' => __('Show as list:', 'geodirectory'),
                    'desc' => __('This will show the tabs as a list and not as tabs.', 'geodirectory'),
                    'type' => 'checkbox',
                    'desc_tip' => true,
                    'value'  => '1',
                    'default'  => 0,
                    'advanced' => true
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
        global $preview, $post,$gd_post;

        // options
        $defaults = array(
            'show_as_list' => '0', // 0 =  all
            //'title_tag' => 'h4',
        );

        /**
         * Parse incoming $args into an array and merge it with $defaults
         */
        $args = wp_parse_args( $args, $defaults );



        ob_start();
        $post_type = $post->post_type;
        $tabs = self::get_tab_settings($post_type);

//       print_r( $tabs);

        // get the tab contents first so we can decide to output the tab head
        $tabs_content = array();
        foreach($tabs as $tab){
            $tabs_content[$tab->id."tab"] = self::tab_content($tab);
        }

//        print_r( $tabs_content);

        if(!empty($tabs)){
            echo '<div class="geodir-tabs" id="gd-tabs">';

            // tabs head
            if(!$args['show_as_list']){

                ?>
                <div id="geodir-tab-mobile-menu">
<!--                    <i class="fa fa-bars"></i>-->
                    <span class="geodir-mobile-active-tab"></span>
                    <i class="fa fa-sort-desc"></i>
                </div>
                <?php
                echo '<dl class="geodir-tab-head">';

                $count = 0;
                foreach($tabs as $tab){
                    if($tab->tab_level>0){continue;}
                    if(empty($tabs_content[$tab->id."tab"])){continue;}

                    $tab_class = $count==0 ? 'geodir-tab-active' :'';
                    $data_status = '';//$count==0 ? 'data-status="enable"' : '';
                    echo '<dt></dt> <!-- added to comply with validation -->';
                    echo '<dd class="'.$tab_class .'">';
                    echo '<a data-tab="#'.esc_attr($tab->tab_key).'" data-status="enable">';
                    if($tab->tab_icon){
                        echo '<i class="fa '.esc_attr($tab->tab_icon).'" aria-hidden="true"></i>';
                    }
                    echo esc_attr__($tab->tab_name,'geodirectory').'</a>';
                    echo '</dd>';
                    $count++;
                }


                echo '</dl>';
            }

            // tabs content
            $list_class =  $args['show_as_list'] ? 'geodir-tabs-as-list' : '';
            echo '<ul class="geodir-tabs-content geodir-entry-content '.$list_class.'">';
            foreach($tabs as $tab){
                if($tab->tab_level>0){continue;}
                if(empty($tabs_content[$tab->id."tab"])){continue;}

                $add_tab = $args['show_as_list'] ? '' : 'Tab';
                echo '<li id="'.esc_attr($tab->tab_key).$add_tab.'" >';
                if ( $args['show_as_list'] ) {
                    $tab_icon = '';
                    if($tab->tab_icon){
                        $tab_icon = '<i class="fa '.esc_attr($tab->tab_icon).'" aria-hidden="true"></i>';
                    }
                    $tab_title = '<span class="gd-tab-list-title" ><a href="#' . esc_attr($tab->tab_key) . '">' . $tab_icon . esc_attr__( $tab->tab_name, 'geodirectory' ) . '</a></span><hr />';

                    /**
                     * Filter the tab list title html.
                     *
                     * @since 1.6.1
                     *
                     * @param string $tab_title      The html for the tab title.
                     * @param array $tab             The array of values including title text.
                     */
                    echo apply_filters( 'geodir_tab_list_title', $tab_title, $tab );
                }
                echo '<div id="geodir-tab-content-'.esc_attr($tab->tab_key).'" class="hash-offset"></div>';

                echo $tabs_content[$tab->id."tab"];

                echo '</li>';
            }
            echo '</ul>';

            echo '</div>';

            if ( ! $args['show_as_list']) { ?>
                <script>
                    if (window.location.hash && window.location.hash.indexOf('&') === -1 && jQuery(window.location.hash + 'Tab').length) {
                        hashVal = window.location.hash;
                    } else {
                        hashVal = jQuery('dl.geodir-tab-head dd.geodir-tab-active').find('a').attr('data-tab');
                    }
                    jQuery('dl.geodir-tab-head dd').each(function () {
                        //Get all tabs
                        var tabs = jQuery(this).children('dd');
                        var tab = '';
                        tab = jQuery(this).find('a').attr('data-tab');
                        if (hashVal != tab) {
                            jQuery(tab + 'Tab').hide();
                        }

                    });
                </script>
                <?php
            }
        }

        // echo "<hr style='clear:both;'>";


        // $this->detail_page_tabs();

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
    public function tab_content($tab,$child=false) {

        ob_start();
        // main content
        if(!empty($tab->tab_content)){ // override content
            echo do_shortcode(stripslashes( $tab->tab_content ));
        }elseif($tab->tab_type=='meta'){ // meta info
            echo do_shortcode('[gd_post_meta key="'.$tab->tab_key.'" show="value"]');
        }elseif($tab->tab_type=='standard'){ // meta info
            if($tab->tab_key=='reviews'){
                comments_template();
            } else {
				do_action( 'geodir_standard_tab_content', $tab );
			}
        }

        echo self::tab_content_child($tab);

        return ob_get_clean();
    }

    /**
     * Get tab content child.
     *
     * @since 2.0.0
     *
     * @param object $tab Tab object.
     * @return string
     */
    public function tab_content_child($tab) {
        ob_start();
        global $post;
        $post_type = $post->post_type;
        $tabs = self::get_tab_settings($post_type);
        $parent_id = $tab->id;

        foreach($tabs as $child_tab){
            if($child_tab->tab_parent==$parent_id){
                if(!empty($child_tab->tab_content)){ // override content
                    echo stripslashes( $child_tab->tab_content );
                }elseif($child_tab->tab_type=='meta'){ // meta info
                    echo do_shortcode('[gd_post_meta key="'.$child_tab->tab_key.'"]');
                }elseif($child_tab->tab_type=='fieldset'){ // meta info
                    self:: output_fieldset($child_tab);
                }elseif($child_tab->tab_type=='standard'){ // meta info
                    if($child_tab->tab_key=='reviews'){
                        comments_template();
                    }
                }
            }
        }
        return ob_get_clean();

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
            echo '<i class="fa '.esc_attr($tab->tab_icon).'" aria-hidden="true"></i>';
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

        return $tabs;
    }
    
}