<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * GeoDirectory Notifications widget.
 *
 * @since 2.0.0.38
 */
class GeoDir_Widget_Notifications extends WP_Super_Duper {

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
            'block-keywords'=> "['notifications','post','geodir']",
            'class_name'    => __CLASS__,
            'base_id'       => 'gd_notifications', // this us used as the widget id and the shortcode id.
            'name'          => __('GD > Notifications','geodirectory'), // the name of the widget.
//			'no_wrap'       => true,
			'block-wrap'       => '',
            'widget_ops'    => array(
                'classname'   => 'geodir-notifications '.geodir_bsui_class(), // widget class
                'description' => esc_html__('Shows hints tips and notifications to users, these can be added by GeoDirectory or any of its addons.','geodirectory'), // widget description
                'geodirectory' => true,
            ),
        );


        parent::__construct( $options );
    }

    /**
     * The Super block output function.
     *     * @param array

     * @param string $content
     *$args
     * @param array $widget_args
     * @return mixed|string|void
     */
    public function output($args = array(), $widget_args = array(),$content = ''){
        global $gd_notifications_shortcode_used;

        $notifications = array();

        // check if we are testing the notifications
        if( $this->is_preview() || geodir_is_block_demo() || ( isset($_REQUEST['gd_notifications_test']) && current_user_can('administrator') )) {
            $notifications = self::test_notifications();
        }

        $notifications = apply_filters('geodir_notifications',$notifications);

        $notifications_html = '';



        if(!empty($notifications)){
            $notifications_html = geodir_notification($notifications);
        }


        return apply_filters('geodir_notifications_html',$notifications_html,$notifications);
    }


    /**
     * Returns some dummy test notifications.
     *
     * @return array|mixed|void
     */
    public static function test_notifications(){
        $notifications = array();

        // error
        $notifications['gd-test-error'] = array(
            'type'  =>  'error',
            'note'  =>  wp_sprintf( __( 'This is how %s notifications will look on your site.', 'geodirectory' ), 'error' )
        );

        // warning
        $notifications['gd-test-warning'] = array(
            'type'  =>  'warning',
            'note'  =>  wp_sprintf( __( 'This is how %s notifications will look on your site.', 'geodirectory' ), 'warning' )
        );

        // success
        $notifications['gd-test-success'] = array(
            'type'  =>  'success',
            'note'  =>  wp_sprintf( __( 'This is how %s notifications will look on your site.', 'geodirectory' ), 'success' ),
            'dismissible'  =>  true
        );

        // info
        $notifications['gd-test-info'] = array(
            'type'  =>  'info',
            'note'  =>  wp_sprintf( __( 'This is how %s notifications will look on your site.', 'geodirectory' ), 'info' ),
            'dismissible'  =>  true
        );

        $notifications = apply_filters('geodir_notifications_test',$notifications);

        return $notifications;
    }

}
