<?php
/**
* GeoDirectory Popular Post Category Widget
*
* @since 1.0.0
*
* @package GeoDirectory
*/

/**
 * GeoDirectory popular post category widget class.
 *
 * @since 1.0.0
 */
class GeoDir_Widget_Popular_Post_Category extends WP_Super_Duper {
    
    /**
     * Register the categories with WordPress.
     *
     */
    public function __construct() {

        $options = array(
            'textdomain'    	=> GEODIRECTORY_TEXTDOMAIN,
            'block-icon'    	=> 'admin-site',
            'block-category'	=> 'widgets',
            'block-keywords'	=> "['category','geo','popular']",
            'class_name'    	=> __CLASS__,
            'base_id'       	=> 'gd_popular_post_category', // this us used as the widget id and the shortcode id.
            'name'          	=> __('GD > Popular Post Category','geodirectory'), // the name of the widget.
            'widget_ops'    	=> array(
                'classname'   => 'gd-wgt-popular-post-category', // widget class
                'description' => esc_html__('Shows a list of popular GeoDirectory categories.','geodirectory'), // widget description
                'customize_selective_refresh' => true,
                'geodirectory' => true,
                'gd_show_pages' => array(),
            ),
            'arguments'     	=> array(
                'title'  => array(
                    'title' => __('Title:', 'geodirectory'),
                    'desc' => __('The widget title.', 'geodirectory'),
                    'type' => 'text',
                    'default'  => '',
                    'desc_tip' => true,
                    'advanced' => false
                ),
                'default_post_type'  => array(
                    'title' => __('Default post type:', 'geodirectory'),
                    'desc' => __('The default post type to use if current post type not set by the page.', 'geodirectory'),
                    'type' => 'select',
                    'options' => geodir_get_posttypes('options-plural'),
                    'default'  => '',
                    'desc_tip' => true,
                    'advanced' => true
                ),
				'category_limit'  => array(
                    'title' => __('Customize categories count to appear by default:', 'geodirectory'),
                    'desc' => __('After categories count reaches this limit option More Categories / Less Categoris will be displayed to show/hide categories. Default: 15', 'geodirectory'),
                    'type' => 'text',
                    'default'  => '15',
                    'desc_tip' => true,
                    'advanced' => false
                ),
			    'parent_only'  => array(
				    'title' => __("Show parent categories only", 'geodirectory'),
				    'type' => 'checkbox',
				    'desc_tip' => true,
				    'value'  => '1',
				    'default'  => 0,
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
	public function output($args = array(), $widget_args = array(),$content = '') {
		ob_start();
		
		//	defaults
		//	array(
		//		'title' => '',
		//		'default_post_type' => '',
		//		'category_limit' => '15',
		//		'parent_only' => '0'
		//	)

		geodir_popular_post_category_output($args, $args);

		return ob_get_clean();
	}
}
