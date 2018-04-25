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

                //echo "content".esc_attr($tab->tab_key);

                echo self::tab_content($tab);



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

    public function tab_content($tab,$child=false) {

        // main content
        if(!empty($tab->tab_content)){ // override content
            echo stripslashes( $tab->tab_content );
        }elseif($tab->tab_type=='meta'){ // meta info
            echo do_shortcode('[gd_post_meta key="'.$tab->tab_key.'" show="value"]');
        }elseif($tab->tab_type=='standard'){ // meta info
            if($tab->tab_key=='reviews'){
                comments_template();
            }
        }



        self::tab_content_child($tab);
    }

    public function tab_content_child($tab) {
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
                }elseif($tab->tab_type=='standard'){ // meta info
                    if($tab->tab_key=='reviews'){
                        comments_template();
                    }
                }
            }
        }

    }

    public function output_fieldset($tab){
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

    }


    public function get_tab_settings($post_type){
        global $wpdb,$geodir_tab_layout_settings;

        if($geodir_tab_layout_settings){
            $tabs = $geodir_tab_layout_settings;
        }else{
            $geodir_tab_layout_settings = $tabs = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".GEODIR_TABS_LAYOUT_TABLE." WHERE post_type=%s ORDER BY sort_order ASC",$post_type));
        }

        return $tabs;
    }
    /**
     * The main function responsible for displaying tabs in frontend detail page.
     *
     * @since   1.0.0
     * @package GeoDirectory
     * @global object $post                      The current post object.
     * @global array $post_images                List of images attached to the post.
     * @global string $video                     The video embed content.
     * @global string $special_offers            Special offers content.
     * @global string $related_listing           Related listing html.
     * @global string $geodir_post_detail_fields Detail field html.
     * @todo this function needs redone
     */
    public function detail_page_tabs() {
        global $post,$gd_post, $post_images, $video, $special_offers, $related_listing, $geodir_post_detail_fields, $preview;

//	print_r($post);
//	print_r($gd_post);


        $post_id            = ! empty( $post ) && isset( $post->ID ) ? (int) $post->ID : 0;
        $request_post_id    = ! empty( $_REQUEST['p'] ) ? (int) $_REQUEST['p'] : 0;
        $map_args           = array();
        $is_backend_preview = ( is_single() && ! empty( $_REQUEST['post_type'] ) && ! empty( $_REQUEST['preview'] ) && ! empty( $_REQUEST['p'] ) ) && is_super_admin() ? true : false; // skip if preview from backend

        if ( $is_backend_preview && ! $post_id > 0 && $request_post_id > 0 ) {
            $post = geodir_get_post_info( $request_post_id );
            setup_postdata( $post );
        }

        $geodir_post_detail_fields = geodir_show_listing_info( 'moreinfo' );


        if ( geodir_is_page( 'detail' ) ) {
            $video                 = geodir_get_video( $post->ID );
            $special_offers        = geodir_get_special_offers( $post->ID );
            $related_listing_array = array();
            if ( geodir_get_option( 'geodir_add_related_listing_posttypes' ) ) {
                $related_listing_array = geodir_get_option( 'geodir_add_related_listing_posttypes' );
            }


            $excluded_tabs = geodir_get_option( 'geodir_detail_page_tabs_excluded' );
            if ( ! $excluded_tabs ) {
                $excluded_tabs = array();
            }

            $related_listing = '';
            if ( in_array( $post->post_type, $related_listing_array ) && ! in_array( 'related_listing', $excluded_tabs ) ) {
                $request = array(
                    'post_number'         => geodir_get_option( 'geodir_related_post_count' ),
                    'relate_to'           => geodir_get_option( 'geodir_related_post_relate_to' ),
                    'layout'              => geodir_get_option( 'geodir_related_post_listing_view' ),
                    'add_location_filter' => geodir_get_option( 'geodir_related_post_location_filter' ),
                    'list_sort'           => geodir_get_option( 'geodir_related_post_sortby' ),
                    'character_count'     => geodir_get_option( 'geodir_related_post_excerpt' )
                );

                if ( $post->post_type == 'gd_event' && defined( 'GDEVENTS_VERSION' ) ) {
                    $related_listing = geodir_get_detail_page_related_events( $request );
                } else {
                    $related_listing = geodir_related_posts_display( $request );
                }

            }

            $post_images = geodir_get_images( $post->ID );
            $thumb_image = '';
            if ( ! empty( $post_images ) ) {
                foreach ( $post_images as $image ) {
                    $caption = ( ! empty( $image->title ) ) ? $image->title : '';
                    $image_tag = geodir_get_image_tag( $image, 'thumbnail' );
                    $metadata = isset( $image->metadata ) ? maybe_unserialize( $image->metadata ) : '';

                    $thumb_image .= '<a href="' . geodir_get_image_src( $image, 'original' ) . '" title="' . esc_attr( $caption ) . '">';
                    $thumb_image .= wp_image_add_srcset_and_sizes( $image_tag, $metadata , 0 );
                    $thumb_image .= '</a>';
                }
            }

            $map_args['map_canvas'] = 'detail_page_map_canvas';
            $map_args['width']           = '600';
            $map_args['height']          = '300';
            if ( $gd_post->mapzoom ) {
                $map_args['zoom'] = '' . $gd_post->mapzoom . '';
            }
            $map_args['autozoom']                 = false;
            $map_args['scrollwheel']              = ( geodir_get_option( 'geodir_add_listing_mouse_scroll' ) ) ? 0 : 1;
            $map_args['child_collapse']           = '0';
            $map_args['enable_cat_filters']       = false;
            $map_args['enable_text_search']       = false;
            $map_args['post_type_filter'] = false;
            $map_args['location_filter']  = false;
            $map_args['jason_on_load']     = false;
            $map_args['enable_map_direction']     = true;
            $map_args['map_class_name']           = 'geodir-map-detail-page';
            $map_args['maptype']                  = ( ! empty( $post->post_mapview ) ) ? $post->post_mapview : 'ROADMAP';
            $map_args['posts']           		  = $post->ID;
        }

        $arr_detail_page_tabs = geodir_detail_page_tabs_list();// get this sooner so we can get the active tab for the user

        //print_r($arr_detail_page_tabs);

        $active_tab       = '';
        $active_tab_name  = '';
        $default_tab      = '';
        $default_tab_name = '';
        foreach ( $arr_detail_page_tabs as $tab_index => $tabs ) {
            if ( isset( $tabs['is_active_tab'] ) && $tabs['is_active_tab'] && ! empty( $tabs['is_display'] ) && isset( $tabs['heading_text'] ) && $tabs['heading_text'] ) {
                $active_tab      = $tab_index;
                $active_tab_name = __( $tabs['heading_text'], 'geodirectory' );
            }

            if ( $default_tab === '' && ! empty( $tabs['is_display'] ) && ! empty( $tabs['heading_text'] ) ) {
                $default_tab      = $tab_index;
                $default_tab_name = __( $tabs['heading_text'], 'geodirectory' );
            }
        }

        if ( $active_tab === '' && $default_tab !== '' ) { // Make first tab as a active tab if not any tab is active.
            if ( isset( $arr_detail_page_tabs[ $active_tab ] ) && isset( $arr_detail_page_tabs[ $active_tab ]['is_active_tab'] ) ) {
                $arr_detail_page_tabs[ $active_tab ]['is_active_tab'] = false;
            }

            $arr_detail_page_tabs[ $default_tab ]['is_active_tab'] = true;
            $active_tab                                            = $default_tab;
            $active_tab_name                                       = $default_tab_name;
        }
        $tab_list = ( geodir_get_option( 'geodir_disable_tabs', false ) ) ? true : false;
        ?>
        <div class="geodir-tabs" id="gd-tabs" style="position:relative;">
            <?php if ( ! $tab_list ){ ?>
            <div id="geodir-tab-mobile-menu">
                <i class="fa fa-bars"></i>
                <span class="geodir-mobile-active-tab"><?php echo $active_tab_name; ?></span>
                <i class="fa fa-sort-desc"></i>
            </div>
            <dl class="geodir-tab-head">
                <?php
                }
                /**
                 * Called before the details page tab list headings, inside the `dl` tag.
                 *
                 * @since 1.0.0
                 * @see   'geodir_after_tab_list'
                 */
                do_action( 'geodir_before_tab_list' ); ?>
                <?php

                foreach ( $arr_detail_page_tabs as $tab_index => $detail_page_tab ) {
                    if ( $detail_page_tab['is_display'] ) {

                        if ( ! $tab_list ) {
                            ?>
                            <dt></dt> <!-- added to comply with validation -->
                            <dd <?php if ( $detail_page_tab['is_active_tab'] ){ ?>class="geodir-tab-active"<?php } ?> ><a
                                    data-tab="#<?php echo $tab_index; ?>"
                                    data-status="enable"><?php _e( $detail_page_tab['heading_text'], 'geodirectory' ); ?></a>
                            </dd>
                            <?php
                        }
                        ob_start() // start tab content buffering
                        ?>
                        <li id="<?php echo $tab_index; ?>Tab">
                            <?php if ( $tab_list ) {
                                $tab_title = '<span class="gd-tab-list-title" ><a href="#' . $tab_index . '">' . __( $detail_page_tab['heading_text'], 'geodirectory' ) . '</a></span><hr />';
                                /**
                                 * Filter the tab list title html.
                                 *
                                 * @since 1.6.1
                                 *
                                 * @param string $tab_title      The html for the tab title.
                                 * @param string $tab_index      The tab index type.
                                 * @param array $detail_page_tab The array of values including title text.
                                 */
                                echo apply_filters( 'geodir_tab_list_title', $tab_title, $tab_index, $detail_page_tab );
                            } ?>
                            <div id="geodir-tab-content-<?php echo $tab_index; ?>" class="hash-offset"></div>
                            <?php
                            /**
                             * Called before the details tab content is output per tab.
                             *
                             * @since 1.0.0
                             *
                             * @param string $tab_index The tab name ID.
                             */
                            do_action( 'geodir_before_tab_content', $tab_index );

                            /**
                             * Called before the details tab content is output per tab.
                             *
                             * Uses dynamic hook name: geodir_before_$tab_index_tab_content
                             *
                             * @since 1.0.0
                             * @todo  do we need this if we have the hook above? 'geodir_before_tab_content'
                             */
                            do_action( 'geodir_before_' . $tab_index . '_tab_content' );
                            /// write a code to generate content of each tab
                            switch ( $tab_index ) {
                                case 'post_profile':
                                    /**
                                     * Called before the listing description content on the details page tab.
                                     *
                                     * @since 1.0.0
                                     */
                                    do_action( 'geodir_before_description_on_listing_detail' );
                                    if ( geodir_is_page( 'detail' ) ) {
                                        //the_content();
                                        echo wpautop(stripslashes( $post->post_content ));
                                        //print_r($post);
                                    } else {
                                        /** This action is documented in geodirectory_template_actions.php */
                                        //echo apply_filters( 'the_content', stripslashes( $post->post_desc ) );
                                        echo wpautop(stripslashes( $post->post_content ));
                                    }

                                    /**
                                     * Called after the listing description content on the details page tab.
                                     *
                                     * @since 1.0.0
                                     */
                                    do_action( 'geodir_after_description_on_listing_detail' );
                                    break;
                                case 'post_info':
                                    echo $geodir_post_detail_fields;
                                    break;
                                case 'post_images':
                                    //echo $thumb_image;
                                    echo do_shortcode('[gd_post_images type="gallery" ajax_load="1" show_title="1" link_to="lightbox"]');
                                    break;
                                case 'post_video':
                                    // some browsers hide $_POST data if used for embeds so we replace with a placeholder
                                    if ( $preview ) {
                                        if ( $video ) {
                                            echo "<span class='gd-video-embed-preview' ><p class='gd-video-preview-text'><i class=\"fa fa-video-camera\" aria-hidden=\"true\"></i><br />" . __( 'Video Preview Placeholder', 'geodirectory' ) . "</p></span>";
                                        }
                                    } else {

                                        // stop payment manager filtering content length
                                        $filter_priority = has_filter( 'the_content', 'geodir_payments_the_content' );
                                        if ( false !== $filter_priority ) {
                                            remove_filter( 'the_content', 'geodir_payments_the_content', $filter_priority );
                                        }

                                        /** This action is documented in geodirectory_template_actions.php */
                                        echo apply_filters( 'the_content', stripslashes( $video ) );// we apply the_content filter so oembed works also;

                                        if ( false !== $filter_priority ) {
                                            add_filter( 'the_content', 'geodir_payments_the_content', $filter_priority );
                                        }
                                    }
                                    break;
                                case 'special_offers':
                                    echo apply_filters( 'gd_special_offers_content', wpautop( stripslashes( $special_offers ) ) );

                                    break;
                                case 'post_map':
                                    $geodir_widget_map = new GeoDir_Widget_Map( array() );
                                    $geodir_widget_map->post_map( $gd_post );
                                    break;
                                case 'reviews':
                                    comments_template();
                                    break;
                                case 'related_listing':
                                    echo $related_listing;
                                    break;
                                default: {
                                    if ( ( isset( $gd_post->{$tab_index} ) || ( ! isset( $gd_post->{$tab_index} ) && ( strpos( $tab_index, 'gd_tab_' ) !== false || $tab_index == 'link_business' || $tab_index == 'address' ) ) ) && ! empty( $detail_page_tab['tab_content'] ) ) {
                                        echo $detail_page_tab['tab_content'];
                                    }
                                }
                                    break;
                            }

                            /**
                             * Called after the details tab content is output per tab.
                             *
                             * @since 1.0.0
                             */
                            do_action( 'geodir_after_tab_content', $tab_index );

                            /**
                             * Called after the details tab content is output per tab.
                             *
                             * Uses dynamic hook name: geodir_after_$tab_index_tab_content
                             *
                             * @since 1.0.0
                             * @todo  do we need this if we have the hook above? 'geodir_after_tab_content'
                             */
                            do_action( 'geodir_after_' . $tab_index . '_tab_content' );
                            ?> </li>
                        <?php
                        /**
                         * Filter the current tab content.
                         *
                         * @since 1.0.0
                         */
                        $arr_detail_page_tabs[ $tab_index ]['tab_content'] = apply_filters( "geodir_modify_" . $detail_page_tab['tab_content'] . "_tab_content", ob_get_clean() );
                    } // end of if for is_display
                }// end of foreach

                /**
                 * Called after the details page tab list headings, inside the `dl` tag.
                 *
                 * @since 1.0.0
                 * @see   'geodir_before_tab_list'
                 */
                do_action( 'geodir_after_tab_list' );
                ?>
                <?php if ( ! $tab_list ){ ?></dl><?php } ?>
            <ul class="geodir-tabs-content geodir-entry-content <?php if ( $tab_list ) { ?>geodir-tabs-list<?php } ?>"
                style="position:relative;">
                <?php
                foreach ( $arr_detail_page_tabs as $detail_page_tab ) {
                    if ( $detail_page_tab['is_display'] && ! empty( $detail_page_tab['tab_content'] ) ) {
                        echo $detail_page_tab['tab_content'];
                    }// end of if
                }// end of foreach

                /**
                 * Called after all the tab content is output in `li` tags, called before the closing `ul` tag.
                 *
                 * @since 1.0.0
                 */
                do_action( 'geodir_add_tab_content' ); ?>
            </ul>
            <!--gd-tabs-content ul end-->
        </div>
        <?php if ( ! $tab_list ) { ?>
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

}