<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * GeoDirectory Search widget.
 *
 * @since 1.0.0
 */
class GeoDir_Widget_Recently_Viewed extends WP_Super_Duper {

    public function __construct() {

        $options = array(
            'textdomain'    => GEODIRECTORY_TEXTDOMAIN,
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['Recently Viewed','geodir']",
            'class_name'    => __CLASS__,
            'base_id'       => 'gd_recently_viewed',
            'name'          => __('GD > Recently Viewed','geodirectory'), // the name of the widget.
            'widget_ops'    => array(
                'classname'   => 'geodir-recently-viewed', // widget class
                'description' => esc_html__('Shows the GeoDirectory Most Recently Viewed Listings.','geodirectory'),
                'geodirectory' => true,
            ),
        );

        parent::__construct( $options );

        add_action('wp_footer', array( $this, 'geodir_recently_viewed_posts' ));
        add_action('wp_ajax_gd_recently_viewed_action', array( $this, 'gd_recently_viewed_action_fn' ));
        add_action('wp_ajax_nopriv_gd_recently_viewed_action', array( $this, 'gd_recently_viewed_action_fn' ));

    }

    /**
     * Set the arguments later.
     *
     * @since 2.0.0
     *
     * @return array
     */
    public function set_arguments(){

        $widget_args = array();

        $widget_args['title'] = array(
            'title' => __('Title:', 'geodirectory'),
            'desc' => __('The Recently Viewed widget title.', 'geodirectory'),
            'type' => 'text',
            'placeholder' => __( 'Recently Viewed', 'geodirectory' ),
            'default'  => '',
            'desc_tip' => true,
            'advanced' => false,
        );

        $widget_args['post_limit'] = array(
            'title' => __('Posts to show:', 'geodirectory'),
            'desc' => __('The number of posts to show by default.', 'geodirectory'),
            'type' => 'number',
            'default'  => '5',
            'desc_tip' => true,
            'advanced' => true
        );

        $widget_args['layout'] = array(
            'title' => __('Layout:', 'geodirectory'),
            'desc' => __('How the listings should laid out by default.', 'geodirectory'),
            'type' => 'select',
            'options'   =>  array(
                "gridview_onehalf"        =>  __('Grid View (Two Columns)', 'geodirectory'),
                "gridview_onethird"        =>  __('Grid View (Three Columns)', 'geodirectory'),
                "gridview_onefourth"        =>  __('Grid View (Four Columns)', 'geodirectory'),
                "gridview_onefifth"        =>  __('Grid View (Five Columns)', 'geodirectory'),
                "list"        =>  __('List view', 'geodirectory'),
            ),
            'default'  => 'h3',
            'desc_tip' => true,
            'advanced' => true
        );

        return $widget_args;
    }

    /**
     * Outputs the map widget on the front-end.
     *
     * @since 2.0.0
     *
     * @param array $args
     * @param array $widget_args
     * @param string $content
     *
     * @return mixed|string
     */
    public function output($args = array(), $widget_args = array(),$content = ''){

        $create_rv_nonce = wp_create_nonce('recently_viewed');
        $post_page_limit = !empty( $args['post_limit'] ) ? $args['post_limit'] : '5';
        $layout = !empty( $args['layout'] ) ? $args['layout'] : 'list';
        ob_start();
        ?>
        <div class="geodir-recently-reviewed">
            <div class="recently-reviewed-content"></div>
            <div class="recently-reviewed-loader" style="display: none;text-align: center;"><i class="fas fa-sync fa-spin fa-2x"></i></div>
        </div>

        <script type="text/javascript">
            jQuery( document ).ready(function() {

                jQuery('.recently-reviewed-loader').show();

                var recently_viewed = localStorage.getItem("gd_recently_viewed");
                var data = {
                    'action': 'gd_recently_viewed_action',
                    'viewed_post_id' : recently_viewed,
                    'list_per_page' :'<?php echo $post_page_limit; ?>' ,
                    'layout' : '<?php echo $layout; ?>',
                    '_wpnonce' : '<?php echo $create_rv_nonce; ?>'
                };

                jQuery.post(geodir_params.ajax_url, data, function(response) {
                    jQuery('.geodir-recently-reviewed .recently-reviewed-content').html(response);
                    jQuery('.recently-reviewed-loader').hide();
                    init_read_more();

                });
            });
        </script>

        <?php
        return ob_get_clean();
    }

    /**
     * Get recently reviewed listing html.
     *
     * @since 2.0.0
     */
    public function gd_recently_viewed_action_fn(){

        global $gd_post, $post,$gd_layout_class, $geodir_is_widget_listing;
        ob_start();

        if( !empty( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'],'recently_viewed')) {

            $list_per_page = !empty($_REQUEST['list_per_page']) ? $_REQUEST['list_per_page'] : '';
            $layout = !empty( $_REQUEST['layout'] ) ? $_REQUEST['layout'] :'';
            $post_ids = !empty($_REQUEST['viewed_post_id']) ? json_decode(stripslashes($_REQUEST['viewed_post_id'])) : '';
            $post_ids = !empty($post_ids) ? array_reverse($post_ids) : '';
            $post_ids = !empty($post_ids) ? array_slice($post_ids, 0, $list_per_page) : array();
            $widget_listings = !empty( $post_ids ) ? array_map('intval', $post_ids) : '';

            //geodir_get_template('content-widget-listing.php', array('widget_listings' => $post_ids));

            $layout_class = 'geodir-listview';

            if( !empty( $layout ) && 'list' !== $layout ) {
                $layout_class = ' geodir-gridview '.$layout;
            }

            ?><ul class="geodir-category-list-view clearfix geodir-widget-posts <?php echo $layout_class; ?>"><?php
                if( !empty( $widget_listings ) ) {

                    do_action('geodir_before_listing_post_listview');

                    foreach ( $widget_listings as $widget_listing ) {

                        geodir_setup_postdata( $widget_listing )
                        ?>
                        <li <?php GeoDir_Post_Data::post_class(); ?> data-post-id="<?php echo esc_attr($widget_listing);?>">
                            <?php
                            $content = "[gd_archive_item_section type='open' position='left'][gd_post_images type='image' ajax_load='false' link_to='post' show_logo='true' ][gd_archive_item_section type='close' position='left'][gd_archive_item_section type='open' position='right'][gd_post_title tag='h2'][gd_author_actions author_page_only='1'][gd_post_distance][gd_post_rating alignment='left' ][gd_post_fav show='' alignment='right' ][gd_post_meta key='business_hours' location='listing'][gd_output_location location='listing'][gd_post_meta key='post_content' show='value-strip'][gd_archive_item_section type='close' position='right']";
                            echo do_shortcode($content);
                            ?>
                        </li>
                        <?php

                    }
                    do_action('geodir_after_listing_post_listview');

                } else {

                    geodir_no_listings_found();

                }
            ?></ul><?php

        }

        echo ob_get_clean();

        wp_die();
    }

    /**
     * Added reviewed posts on local storage.
     *
     * Check if is_single page then added reviewed on local storage.
     *
     * @since 2.0.0
     */
    public function geodir_recently_viewed_posts() {

        if( is_single() ){

            $get_post_id = get_the_ID();
            $get_post_type = get_post_type($get_post_id);
            $gd_post_types = geodir_get_posttypes();

            if( !empty( $get_post_type ) && in_array( $get_post_type,$gd_post_types )) {
                ?>
                <script type="text/javascript">
                    jQuery( document ).ready(function() {
                        var get_post_id = '<?php echo $get_post_id; ?>',
                            items_arr = [],
                            recently_viewed = localStorage.getItem("gd_recently_viewed");
                        if( null != recently_viewed ) {
                            items_arr = JSON.parse(localStorage.getItem('gd_recently_viewed'));
                        }
                        if( items_arr.length > 0 ) {
                            if(jQuery.inArray(get_post_id, items_arr) === -1){
                                items_arr.push(get_post_id);
                            }
                        } else {
                            items_arr.push(get_post_id);
                        }
                        localStorage.setItem('gd_recently_viewed', JSON.stringify(items_arr));
                    });
                </script>
                <?php
            }

        }

    }
}