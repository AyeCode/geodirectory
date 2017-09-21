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
class GeoDir_Widget_Popular_Post_Category extends WP_Widget {
    
    /**
     * Register the popular post category widget.
     *
     * @since 1.0.0
     * @since 1.5.1 Changed from PHP4 style constructors to PHP5 __construct.
     */
    public function __construct() {
        $widget_ops = array(
            'classname' => 'geodir_popular_post_category',
            'description' => __( 'GD > Popular Post Category', 'geodirectory' ),
            'customize_selective_refresh' => true,
            'geodirectory' => true,
            'gd_show_pages' => array(),
        );
        parent::__construct( 'popular_post_category', __( 'GD > Popular Post Category', 'geodirectory' ), $widget_ops );
    }

    /**
     * Front-end display content for popular post category widget.
     *
     * @since 1.0.0
     * @since 1.5.1 Declare function public.
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget($args, $instance) {
        geodir_popular_post_category_output($args, $instance);
    }

    /**
     * Sanitize popular post category widget form values as they are saved.
     *
     * @since 1.0.0
     * @since 1.5.1 Declare function public.
     * @since 1.5.1 Added default_post_type parameter.
     * @since 1.6.9 Added parent_only parameter.
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */ 
    public function update($new_instance, $old_instance) {
        //save the widget
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $category_limit = (int)$new_instance['category_limit'];
        $instance['category_limit'] = $category_limit > 0 ? $category_limit : 15;
        $instance['default_post_type'] = isset($new_instance['default_post_type']) ? $new_instance['default_post_type'] : '';
        $instance['parent_only'] = !empty($new_instance['parent_only']) ? true : false;
        return $instance;
    }

    /**
     * Back-end popular post category widget settings form.
     *
     * @since 1.0.0
     * @since 1.5.1 Declare function public.
     * @since 1.5.1 Added option to set default post type.
     * @since 1.6.9 Added option to show parent categories only.
     *
     * @param array $instance Previously saved values from database.
     */
    public function form($instance) {
        //widgetform in backend
        $instance = wp_parse_args((array)$instance, array('title' => '', 'category_limit' => 15, 'default_post_type' => '', 'parent_only' => false));

        $title = strip_tags($instance['title']);
        $category_limit = (int)$instance['category_limit'];
        $category_limit = $category_limit > 0 ? $category_limit : 15;
        $default_post_type = isset($instance['default_post_type']) ? $instance['default_post_type'] : '';
        $parent_only = !empty($instance['parent_only']) ? true: false;
        
        $post_type_options = geodir_get_posttypes('options');
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'geodirectory'); ?>
                <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>"/>
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('post_type'); ?>"><?php _e('Default post type to use (if not set by page)', 'geodirectory');?>
                <select class="widefat" id="<?php echo $this->get_field_id('default_post_type'); ?>" name="<?php echo $this->get_field_name('default_post_type'); ?>">
                <?php foreach ($post_type_options as $name => $title) { ?>
                    <option value="<?php echo $name;?>" <?php selected($name, $default_post_type);?>><?php echo $title; ?></option>
                <?php } ?>
                </select>
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('category_limit'); ?>"><?php _e('Customize categories count to appear by default:', 'geodirectory'); ?>
                <input class="widefat" id="<?php echo $this->get_field_id('category_limit'); ?>" name="<?php echo $this->get_field_name('category_limit'); ?>" type="text" value="<?php echo (int)esc_attr($category_limit); ?>"/>
                <p class="description" style="padding:0"><?php _e('After categories count reaches this limit option More Categories / Less Categoris will be displayed to show/hide categories. Default: 15', 'geodirectory'); ?></p>
            </label>
        </p>
        <p>
            <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('parent_only'); ?>" name="<?php echo $this->get_field_name('parent_only'); ?>"<?php checked( $parent_only ); ?> value="1" />
            <label for="<?php echo $this->get_field_id('parent_only'); ?>"><?php _e( 'Show parent categories only', 'geodirectory' ); ?></label>
        </p>
    <?php
    }
}
