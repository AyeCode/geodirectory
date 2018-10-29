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

        $get_posts = geodir_get_posttypes('options-plural');
        $get_posts['all_post'] = 'All Posts';

        $widget_args['post_type'] = array(
            'title' => __('Default Post Type:', 'geodirectory'),
            'desc' => __('The custom post types to show by default. Only used when there are multiple CPTs.', 'geodirectory'),
            'type' => 'select',
            'options'   =>  $get_posts,
            'default'  => 'all_post',
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
        $post_type = !empty( $args['post_type'] ) ? $args['post_type'] : 'all_post';
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
                    'post_type':'<?php echo $post_type; ?>',
                    '_wpnonce' : '<?php echo $create_rv_nonce; ?>'
                };

                jQuery.post(geodir_params.ajax_url, data, function(response) {
                    jQuery('.geodir-recently-reviewed .recently-reviewed-content').html(response);
                    jQuery('.recently-reviewed-loader').hide();
                    init_read_more();
                    geodir_init_lazy_load();

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

            $post_type = !empty($_REQUEST['post_type']) ? $_REQUEST['post_type'] : '';

            $layout = empty( $_REQUEST['layout'] ) ? 'gridview_onehalf' : apply_filters( 'widget_layout', $_REQUEST['layout'] );

            $post_ids = !empty($_REQUEST['viewed_post_id']) ? json_decode(stripslashes($_REQUEST['viewed_post_id'])) : '';

            $listings_ids = array();

            if( !empty( $post_type ) && 'all_post' != $post_type ) {

                if( !empty( $post_ids ) && $post_ids !='' ) {

                    $listings_ids = $post_ids->$post_type;
                }
            } else{
                if( !empty( $post_ids ) && $post_ids !='' ) {
                    foreach ( $post_ids as $id_key => $id_val ) {
                        if( !empty( $id_val ) && $id_val !='' ) {
                            foreach ( $id_val as $key=> $value ) {
                                $listings_ids[] = $value;
                            }
                        }
                    }
                }
            }

            $listings_ids = !empty( $listings_ids ) ? array_reverse($listings_ids) : array();
            $listings_ids = !empty($listings_ids) ? array_slice($listings_ids, 0, $list_per_page) : array();
            $widget_listings = !empty( $listings_ids ) ? array_map('intval', $listings_ids) : '';

            $gd_layout_class = geodir_convert_listing_view_class( $layout );

            geodir_get_template('content-widget-listing.php', array('widget_listings' => $widget_listings));

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
                    jQuery( document ).ready(function($) {

                        function is_not_empty(obj) {
                            for(var key in obj) {
                                if(obj.hasOwnProperty(key))
                                    return true;
                            }
                            return false;
                        }

                        //localStorage.removeItem("gd_recently_viewed");

                        var post_id = '<?php echo $get_post_id; ?>',
                            post_type = '<?php echo $get_post_type; ?>',
                            reviewed_arr = {},
                            recently_reviewed = JSON.parse(localStorage.getItem('gd_recently_viewed'));

                        if( null != recently_reviewed ) {

                            if(is_not_empty(recently_reviewed)) {

                                if ( post_type in recently_reviewed ) {

                                    var temp_post_arr = [];

                                    if( recently_reviewed[post_type].length > 0 ) {
                                        temp_post_arr = recently_reviewed[post_type];
                                    }

                                    if(jQuery.inArray(post_id, temp_post_arr) === -1) {
                                        temp_post_arr.push(post_id);
                                    }

                                    recently_reviewed[post_type] = temp_post_arr;

                                } else{
                                    recently_reviewed[post_type] = [post_id];
                                }

                            } else{
                                recently_reviewed[post_type] = [post_id];
                            }

                            localStorage.setItem("gd_recently_viewed", JSON.stringify(recently_reviewed));

                        } else{
                            reviewed_arr[post_type] = [post_id];
                            localStorage.setItem("gd_recently_viewed", JSON.stringify(reviewed_arr));
                        }
                    });
                </script>
                <?php
            }

        }

    }
}