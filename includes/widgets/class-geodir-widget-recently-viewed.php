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

        $widget_args['post_display'] = array(
            'title' => __('Post Display: ', 'geodirectory'),
            'desc' => __('The option are use for post display option.', 'geodirectory'),
            'type' => 'select',
            'options'   =>  array(
                'only_title' => __('Display only title','geodirectory'),
                'title_with_image' => __('Display title with image','geodirectory'),
            ),
            'default'  => 'only_title',
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
        $post_display = !empty( $args['post_display'] ) ? $args['post_display'] : '5';
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
                    'post_display' : '<?php echo $post_display; ?>',
                    '_wpnonce' : '<?php echo $create_rv_nonce; ?>'
                };

                jQuery.post(geodir_params.ajax_url, data, function(response) {
                    jQuery('.geodir-recently-reviewed .recently-reviewed-content').html(response);
                    jQuery('.recently-reviewed-loader').hide();

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

        ob_start();

        ?><table class="gd-recently-viewed list-view"><tbody><?php

        if( !empty( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'],'recently_viewed')) {

            $list_per_page = !empty( $_REQUEST['list_per_page'] ) ? $_REQUEST['list_per_page'] :'';

            $post_display = !empty( $_REQUEST['post_display'] ) ? $_REQUEST['post_display'] :'only_title';

            $post_ids = !empty( $_REQUEST['viewed_post_id'] ) ? json_decode(stripslashes( $_REQUEST['viewed_post_id'] ) ): '';
            $post_ids = !empty( $post_ids ) ? array_reverse( $post_ids ) :'';
            $post_ids = !empty( $post_ids ) ? array_slice($post_ids,0,$list_per_page) : array();

            if( !empty( $post_ids ) && is_array( $post_ids ) ) {

                foreach ( $post_ids as $postid ) { ?>
                    <tr>
                        <td>
                            <h5 class="post-title"><a href="<?php echo get_the_permalink($postid); ?>"><?php echo get_the_title($postid); ?></a></h5>
                            <?php if( !empty( $post_display ) && 'title_with_image' === $post_display ) {
                                $post_thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id($postid),'thumbnail');
                                if( !empty( $post_thumbnail[0] ) && '' != $post_thumbnail[0] ) {
                                ?>
                                    <img src="<?php echo $post_thumbnail [0]; ?>" alt="<?php echo get_the_title($postid); ?>">
                                 <?php
                                }
                             } ?>
                        </td>
                    </tr>
                <?php } ?>

            <?php } else { ?>
                <tr>
                    <td><?php _e('No recent view lists...','geodirectory' ); ?></td>
                </tr>
            <?php }
        }

        ?></tbody></table><?php

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