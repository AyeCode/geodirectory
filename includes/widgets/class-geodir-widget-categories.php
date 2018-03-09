<?php
/**
 * GeoDirectory cpt categories widget.
 *
 * @package GeoDirectory
 * @since 1.5.4
 */

/**
 * GeoDirectory categories widget class.
 *
 * @since 1.5.4
 */
class GeoDir_Widget_Categories extends WP_Super_Duper {

    /**
     * Register the categories with WordPress.
     *
     */
    public function __construct() {

        $options = array(
            'textdomain'    => GEODIRECTORY_TEXTDOMAIN,
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['categories','geo','taxonomy']",

            'class_name'    => __CLASS__,
            'base_id'       => 'gd_categories', // this us used as the widget id and the shortcode id.
            'name'          => __('GD > Categories','geodirectory'), // the name of the widget.
            //'disable_widget'=> true,
            'widget_ops'    => array(
                'classname'   => 'geodir-categories-container', // widget class
                'description' => esc_html__('Shows a list of GeoDirectory categories.','geodirectory'), // widget description
                'customize_selective_refresh' => true,
                'geodirectory' => true,
                'gd_show_pages' => array(),
            ),
            'arguments'     => array(
                'title'  => array(
                    'title' => __('Title:', 'geodirectory'),
                    'desc' => __('The widget title.', 'geodirectory'),
                    'type' => 'text',
//                    'placeholder' => 'Leave blank to use current post id.',
                    'default'  => '',
                    'desc_tip' => true,
                    'advanced' => false
                ),
                'post_type'  => array(
                    'title' => __('Post Type:', 'geodirectory'),
                    'desc' => __('The custom post types to show by default. Only used when there are multiple CPTs.', 'geodirectory'),
                    'type' => 'select',
                    'options'   =>  $this->post_type_options(),
                    'default'  => '',
                    'desc_tip' => true,
                    'advanced' => true
                ),
                'hide_empty'  => array(
                    'title' => __('Hide empty:', 'geodirectory'),
                    'desc' => __('This will hide categories that do not have any listings.', 'geodirectory'),
                    'type' => 'checkbox',
                    'desc_tip' => true,
                    'value'  => '1',
                    'default'  => 0,
                    'advanced' => true
                ),
                'show_count'  => array(
                    'title' => __('Show count:', 'geodirectory'),
                    'desc' => __('This will show the number of listings in the categories.', 'geodirectory'),
                    'type' => 'checkbox',
                    'desc_tip' => true,
                    'value'  => '1',
                    'default'  => 0,
                    'advanced' => true
                ),
                'hide_icon'  => array(
                    'title' => __('Hide icon:', 'geodirectory'),
                    'desc' => __('This will hide the category icons from the list.', 'geodirectory'),
                    'type' => 'checkbox',
                    'desc_tip' => true,
                    'value'  => '1',
                    'default'  => 0,
                    'advanced' => true
                ),
                'cpt_left'  => array(
                    'title' => __('Show single column:', 'geodirectory'),
                    'desc' => __('This will hide the category icons from the list.', 'geodirectory'),
                    'type' => 'checkbox',
                    'desc_tip' => true,
                    'value'  => '1',
                    'default'  => 0,
                    'advanced' => true
                ),
                'sort_by'  => array(
                    'title' => __('Sort by:', 'geodirectory'),
                    'desc' => __('Sort categories by.', 'geodirectory'),
                    'type' => 'select',
                    'options'   =>  array(
                        "count" => __('Count', 'geodirectory'),
                        "az" => __('A-Z', 'geodirectory'),
                    ),
                    'default'  => 'count',
                    'desc_tip' => true,
                    'advanced' => true
                ),
                'max_level'  => array(
                    'title' => __('Max sub-cat depth:', 'geodirectory'),
                    'desc' => __('The maximum number of sub category levels to show.', 'geodirectory'),
                    'type' => 'select',
                    'options'   =>  array_merge(array('all' => __('All', 'geodirectory')), range(0, 10)),
                    'default'  => '1',
                    'desc_tip' => true,
                    'advanced' => true
                ),
                'max_count'  => array(
                    'title' => __('Max sub-cat to show:', 'geodirectory'),
                    'desc' => __('The maximum number of sub categories to show.', 'geodirectory'),
                    'type' => 'select',
                    'options'   =>  array_merge(array('all' => __('All', 'geodirectory')), array_reverse( range(0, 10) )),
                    'default'  => 'all',
                    'desc_tip' => true,
                    'advanced' => true
                ),
                'no_cpt_filter'  => array(
                    'title' => __("Don't filter for current viewing post type", 'geodirectory'),
                    'type' => 'checkbox',
                    'desc_tip' => true,
                    'value'  => '1',
                    'default'  => 0,
                    'advanced' => true
                ),
                'no_cat_filter'  => array(
                    'title' => __("Don't filter for current viewing category", 'geodirectory'),
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

        ob_start();
        // options
        $defaults = array(
            'post_type'      => '0', // 0 =  all
            'hide_empty' => '0',
           
        );

        /**
         * Parse incoming $args into an array and merge it with $defaults
         */
        $options = wp_parse_args( $args, $defaults );

        $output = geodir_cpt_categories_output($options );

        echo '<div class="gd-cptcats-widget">';
        echo $output;
        echo '</div>';

        return ob_get_clean();
    }


    /**
     * Front-end display content for cpt categories widget.
     *
     * @since 1.5.4
     * @since 1.6.6 New parameters $no_cpt_filter &no_cat_filter added.
     *
     * @param array $args Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widgetxxx($args, $instance) {
        $params = array();
        /**
         * Filter the widget title.
         *
         * @since 1.5.4
         *
         * @param string $title The widget title. Default empty.
         * @param array  $instance An array of the widget's settings.
         * @param mixed  $id_base The widget ID.
         */
        $params['title'] = apply_filters('geodir_cpt_categories_widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $this->id_base);

        /**
         * Filter the widget setting post type.
         *
         * @since 1.5.4
         *
         * @param array $post_type The post types to display categories.
         * @param array $instance An array of the widget's settings.
         * @param mixed $id_base The widget ID.
         */
        $params['post_type'] = apply_filters('geodir_cpt_categories_widget_post_type', empty($instance['post_type']) ? array() : $instance['post_type'], $instance, $this->id_base);

        /**
         * Filter the widget setting to hide empty categories.
         *
         * @since 1.5.4
         *
         * @param bool  $hide_empty If true then empty category will be not displayed.
         * @param array $instance An array of the widget's settings.
         * @param mixed $id_base The widget ID.
         */
        $params['hide_empty'] = apply_filters('geodir_cpt_categories_widget_hide_empty', empty($instance['hide_empty']) ? 0 : 1, $instance, $this->id_base);

        /**
         * Filter the widget setting to show/hide category count.
         *
         * @since 1.5.4
         *
         * @param bool  $show_count If true then category count will be displayed.
         * @param array $instance An array of the widget's settings.
         * @param mixed $id_base The widget ID.
         */
        $params['show_count'] = apply_filters('geodir_cpt_categories_widget_show_count', empty($instance['show_count']) ? 0 : 1, $instance, $this->id_base);

        /**
         * Filter the widget setting to show/hide category icon.
         *
         * @since 1.5.4
         *
         * @param bool  $hide_icon If true then category icon will be not displayed.
         * @param array $instance An array of the widget's settings.
         * @param mixed $id_base The widget ID.
         */
        $params['hide_icon'] = apply_filters('geodir_cpt_categories_widget_hide_icon', empty($instance['hide_icon']) ? 0 : 1, $instance, $this->id_base);

        /**
         * Filter the widget setting to show CPT inline or not.
         *
         * @since 1.5.4
         *
         * @param bool  $cpt_left If true then CPT will be displayed inline.
         * @param array $instance An array of the widget's settings.
         * @param mixed $id_base The widget ID.
         */
        $params['cpt_left'] = apply_filters('geodir_cpt_categories_widget_cpt_left', empty($instance['cpt_left']) ? 0 : 1, $instance, $this->id_base);

        /**
         * Filter the widget categories sorting order settings.
         *
         * @since 1.5.4
         *
         * @param string $max_count Widget max no of sub-categories count. Default 'count'.
         * @param array       $instance An array of the widget's settings.
         * @param mixed       $id_base The widget ID.
         */
        $params['sort_by'] = apply_filters('geodir_cpt_categories_widget_sort_by', isset($instance['sort_by']) && in_array($instance['sort_by'], array('az', 'count')) ? $instance['sort_by'] : 'count', $instance, $this->id_base);

        /**
         * Filter the widget max no of sub-categories count.
         *
         * @since 1.5.4
         *
         * @param bool|string $max_count Widget max no of sub-categories count.
         * @param array       $instance An array of the widget's settings.
         * @param mixed       $id_base The widget ID.
         */
        $params['max_count'] = apply_filters('geodir_cpt_categories_widget_max_count', !isset($instance['max_count']) ? 'all' : strip_tags($instance['max_count']), $instance, $this->id_base);

        /**
         * Filter the widget max sub-categories depth.
         *
         * @since 1.5.4
         *
         * @param bool|string $max_level Widget max sub-categories depth.
         * @param array       $instance An array of the widget's settings.
         * @param mixed       $id_base The widget ID.
         */
        $params['max_level'] = apply_filters('geodir_cpt_categories_widget_max_level', !isset($instance['max_level']) ? 'all' : strip_tags($instance['max_level']), $instance, $this->id_base);
        
        /**
         * Filter the widget setting to disable filter current viewing post type.
         *
         * @since 1.6.6
         *
         * @param bool  $no_cpt_filter If true then it doesn't filter current viewing post type.
         * @param array $instance An array of the widget's settings.
         * @param mixed $id_base The widget ID.
         */
        $params['no_cpt_filter'] = apply_filters('geodir_cpt_categories_widget_no_cpt_filter', empty($instance['no_cpt_filter']) ? 0 : 1, $instance, $this->id_base);
        
        /**
         * Filter the widget setting to disable current viewing category.
         *
         * @since 1.6.6
         *
         * @param bool  $no_cat_filter If true then it doesn't filter current viewing category.
         * @param array $instance An array of the widget's settings.
         * @param mixed $id_base The widget ID.
         */
        $params['no_cat_filter'] = apply_filters('geodir_cpt_categories_widget_no_cat_filter', empty($instance['no_cat_filter']) ? 0 : 1, $instance, $this->id_base);

        /**
         * Filter the widget parameters.
         *
         * @since 1.5.4
         *
         * @param array $params The widget parameters.
         * @param array $instance An array of the widget's settings.
         * @param mixed $id_base The widget ID.
         */
        $params = apply_filters('geodir_cpt_categories_widget_params', $params, $instance, $this->id_base);

        $output = geodir_cpt_categories_output($params);

        echo $args['before_widget'];
        if ( $params['title'] ) {
            echo '<div class="geodir_list_heading clearfix">';
            echo $args['before_title'] . $params['title'] . $args['after_title'];
            echo '</div>';
        }
        echo '<div class="gd-cptcats-widget">';
        echo $output;
        echo '</div>';
        echo $args['after_widget'];
    }

//    /**
//     * Sanitize cpt categories widget values as they are saved.
//     *
//     * @since 1.5.4
//     * @since 1.6.6 New parameters $no_cpt_filter &no_cat_filter added.
//     *
//     * @param array $new_instance Values just sent to be saved.
//     * @param array $old_instance Previously saved values from database.
//     *
//     * @return array Updated safe values to be saved.
//     */
//    public function update($new_instance, $old_instance) {
//        $new_instance['post_type'] = is_array($new_instance['post_type']) && in_array('0', $new_instance['post_type']) ? array('0') : $new_instance['post_type'];
//        $instance = $old_instance;
//        $instance['title'] = strip_tags($new_instance['title']);
//        $instance['post_type'] = isset($new_instance['post_type']) ? $new_instance['post_type'] : array('0');
//        $instance['hide_empty'] = !empty($new_instance['hide_empty']) ? 1 : 0;
//        $instance['show_count'] = !empty($new_instance['show_count']) ? 1 : 0;
//        $instance['hide_icon'] = !empty($new_instance['hide_icon']) ? 1 : 0;
//        $instance['cpt_left'] = !empty($new_instance['cpt_left']) ? 1 : 0;
//        $instance['sort_by'] = isset($new_instance['sort_by']) && in_array($new_instance['sort_by'], array('az', 'count')) ? $new_instance['sort_by'] : 'count';
//        $instance['max_count'] = strip_tags($new_instance['max_count']);
//        $instance['max_level'] = strip_tags($new_instance['max_level']);
//        $instance['no_cpt_filter'] = !empty($new_instance['no_cpt_filter']) ? 1 : 0;
//        $instance['no_cat_filter'] = !empty($new_instance['no_cat_filter']) ? 1 : 0;
//
//        return $instance;
//    }




    /**
     * Get the post type options for search.
     *
     * @return array
     */
    public function post_type_options(){
        $options = array('0'=>__('Auto','geodirectory'));

        $post_types = geodir_get_posttypes('options-plural');
        if(!empty($post_types)){
            $options = array_merge($options,$post_types);
        }

        //print_r($options);

        return $options;
    }
}