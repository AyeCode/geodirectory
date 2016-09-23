<?php
/**
 * GeoDirectory Listing Slider Widget
 *
 * @since 1.0.0
 *
 * @package GeoDirectory
 */

/**
 * GeoDirectory listing slider widget class.
 *
 * @since 1.0.0
 */
class geodir_listing_slider_widget extends WP_Widget
{

    /**
	 * Register the listing slider widget.
	 *
	 * @since 1.0.0
     * @since 1.5.1 Changed from PHP4 style constructors to PHP5 __construct.
	 */
    public function __construct() {
        $widget_ops = array('classname' => 'geodir_listing_slider_view', 'description' => __('GD > Listing Slider', 'geodirectory'));
        parent::__construct(
            'listing_slider_view', // Base ID
            __('GD > Listing Slider', 'geodirectory'), // Name
            $widget_ops// Args
        );
    }
	
	/**
	 * Front-end display content for listing slider widget.
	 *
	 * @since 1.0.0
     * @since 1.5.1 Declare function public.
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
    public function widget($args, $instance)
    {
        geodir_listing_slider_widget_output($args, $instance);
    }

	/**
	 * Sanitize listing slider widget form values as they are saved.
	 *
	 * @since 1.0.0
     * @since 1.5.1 Declare function public.
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update($new_instance, $old_instance)
    {
        //save the widget
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['post_type'] = strip_tags($new_instance['post_type']);
        $instance['category'] = strip_tags($new_instance['category']);
        $instance['post_number'] = strip_tags($new_instance['post_number']);
        $instance['max_show'] = strip_tags($new_instance['max_show']);
        $instance['slide_width'] = strip_tags($new_instance['slide_width']);
        $instance['show_title'] = isset($new_instance['show_title']) ? $new_instance['show_title'] : '';
        $instance['slideshow'] = isset($new_instance['slideshow']) ? $new_instance['slideshow'] : '';
        $instance['animationLoop'] = isset($new_instance['animationLoop']) ? $new_instance['animationLoop'] : '';
        $instance['directionNav'] = isset($new_instance['directionNav']) ? $new_instance['directionNav'] : '';
        $instance['slideshowSpeed'] = $new_instance['slideshowSpeed'];
        $instance['animationSpeed'] = $new_instance['animationSpeed'];
        $instance['animation'] = $new_instance['animation'];
        $instance['list_sort'] = isset($new_instance['list_sort']) ? $new_instance['list_sort'] : '';
        $instance['show_featured_only'] = isset($new_instance['show_featured_only']) && $new_instance['show_featured_only'] ? 1 : 0;
        if (isset($new_instance['add_location_filter']) && $new_instance['add_location_filter'] != '')
            $instance['add_location_filter'] = strip_tags($new_instance['add_location_filter']);
        else
            $instance['add_location_filter'] = '0';

        return $instance;
    }

	/**
	 * Back-end listing slider widget settings form.
	 *
	 * @since 1.0.0
     * @since 1.5.1 Declare function public.
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form($instance)
    {

        //widgetform in backend
        $instance = wp_parse_args((array)$instance,
            array('title' => '',
                'post_type' => '',
                'category' => '',
                'post_number' => '5',
                'max_show' => '1',
                'slide_width' => '',
                'show_title' => '',
                'slideshow' => '',
                'animationLoop' => '',
                'directionNav' => '',
                'slideshowSpeed' => 5000,
                'animationSpeed' => 600,
                'animation' => '',
                'list_sort' => 'latest',
                'show_featured_only' => '',
                'add_location_filter' => '0',
            )
        );

        $title = strip_tags($instance['title']);

        $post_type = strip_tags($instance['post_type']);

        $category = strip_tags($instance['category']);

        $post_number = strip_tags($instance['post_number']);

        $max_show = strip_tags($instance['max_show']);

        $slide_width = strip_tags($instance['slide_width']);

        $show_title = $instance['show_title'];

        $slideshow = $instance['slideshow'];

        $animationLoop = $instance['animationLoop'];

        $directionNav = $instance['directionNav'];

        $slideshowSpeed = $instance['slideshowSpeed'];

        $animationSpeed = $instance['animationSpeed'];

        $add_location_filter = strip_tags($instance['add_location_filter']);

        $animation = $instance['animation'];
        $list_sort = $instance['list_sort'];
        $show_featured_only = isset($instance['show_featured_only']) && $instance['show_featured_only'] ? true : false;

        $sort_fields = array();
        $sort_fields[] = array('field' => 'latest', 'label' => __('Latest', 'geodirectory'));
        $sort_fields[] = array('field' => 'featured', 'label' => __('Featured', 'geodirectory'));
        $sort_fields[] = array('field' => 'high_review', 'label' => __('Review', 'geodirectory'));
        $sort_fields[] = array('field' => 'high_rating', 'label' => __('Rating', 'geodirectory'));
        $sort_fields[] = array('field' => 'random', 'label' => __('Random', 'geodirectory'));
        ?>

        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'geodirectory');?>

                <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
                       name="<?php echo $this->get_field_name('title'); ?>" type="text"
                       value="<?php echo esc_attr($title); ?>"/>
            </label>
        </p>

        <p>
            <label
                for="<?php echo $this->get_field_id('post_type'); ?>"><?php _e('Post Type:', 'geodirectory');?>

                <?php $postypes = geodir_get_posttypes(); ?>

                <select class="widefat" id="<?php echo $this->get_field_id('post_type'); ?>"
                        name="<?php echo $this->get_field_name('post_type'); ?>"
                        onchange="geodir_change_category_list(this.value)">

                    <?php foreach ($postypes as $postypes_obj) { ?>

                        <option <?php if ($post_type == $postypes_obj) {
                            echo 'selected="selected"';
                        } ?> value="<?php echo $postypes_obj; ?>"><?php $extvalue = explode('_', $postypes_obj);
                            echo ucfirst($extvalue[1]); ?></option>

                    <?php } ?>

                </select>
            </label>
        </p>


        <p>
            <label
                for="<?php echo $this->get_field_id('category'); ?>"><?php _e('Post Category:', 'geodirectory');?>

                <?php
                $category_taxonomy = geodir_get_taxonomies('gd_place');
                $categories = get_terms($category_taxonomy, array('orderby' => 'count', 'order' => 'DESC'));
                ?>

                <select class="widefat" id="<?php echo $this->get_field_id('category'); ?>"
                        name="<?php echo $this->get_field_name('category'); ?>">
                    <option <?php if ($category == '0') {
                        echo 'selected="selected"';
                    } ?> value="0"><?php _e('All', 'geodirectory'); ?></option>
                    <?php foreach ($categories as $category_obj) { ?>

                        <option <?php if ($category == $category_obj->term_id) {
                            echo 'selected="selected"';
                        } ?>
                            value="<?php echo $category_obj->term_id; ?>"><?php echo ucfirst($category_obj->name); ?></option>

                    <?php } ?>

                </select>
            </label>
        </p>
        <p>
            <label
                for="<?php echo $this->get_field_id('list_sort'); ?>"><?php _e('Sort by:', 'geodirectory'); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id('list_sort'); ?>"
                    name="<?php echo $this->get_field_name('list_sort'); ?>">
                <?php foreach ($sort_fields as $sort_field) { ?>
                    <option
                        value="<?php echo $sort_field['field']; ?>" <?php echo($list_sort == $sort_field['field'] ? 'selected="selected"' : ''); ?>><?php echo $sort_field['label']; ?></option>
                <?php } ?>
            </select>
        </p>
        <p>
            <label
                for="<?php echo $this->get_field_id('post_number'); ?>"><?php _e('Number of posts(total):', 'geodirectory');?>
                <input class="widefat" id="<?php echo $this->get_field_id('post_number'); ?>"
                       name="<?php echo $this->get_field_name('post_number'); ?>" type="text"
                       value="<?php echo esc_attr($post_number); ?>"/>
            </label>
        </p>

        <p>
            <label
                for="<?php echo $this->get_field_id('max_show'); ?>"><?php _e('Number of posts(shown at one time, requires a slide width to be set):', 'geodirectory');?>
                <input class="widefat" id="<?php echo $this->get_field_id('max_show'); ?>"
                       name="<?php echo $this->get_field_name('max_show'); ?>" type="text"
                       value="<?php echo esc_attr($max_show); ?>"/>
            </label>
        </p>

        <p>
            <label
                for="<?php echo $this->get_field_id('slide_width'); ?>"><?php _e('Slide width(leave blank unless showing more than one slide at a time, ex: 210):', 'geodirectory');?>
                <input class="widefat" id="<?php echo $this->get_field_id('slide_width'); ?>"
                       name="<?php echo $this->get_field_name('slide_width'); ?>" type="text"
                       value="<?php echo esc_attr($slide_width); ?>"/>
            </label>
        </p>

        <p>
            <label
                for="<?php echo $this->get_field_id('animation'); ?>"><?php _e('Animation:', 'geodirectory');?>

                <select class="widefat" id="<?php echo $this->get_field_id('animation'); ?>"
                        name="<?php echo $this->get_field_name('animation'); ?>">
                    <option <?php if ($animation == 'slide') {
                        echo 'selected="selected"';
                    } ?> value="slide">Slide
                    </option>
                    <option <?php if ($animation == 'fade') {
                        echo 'selected="selected"';
                    } ?> value="fade">Fade
                    </option>
                </select>
            </label>
        </p>

        <p>
            <label
                for="<?php echo $this->get_field_id('slideshowSpeed'); ?>"><?php _e('Slide Show Speed: (milliseconds)', 'geodirectory');?>

                <input class="widefat" id="<?php echo $this->get_field_id('slideshowSpeed'); ?>"
                       name="<?php echo $this->get_field_name('slideshowSpeed'); ?>" type="text"
                       value="<?php echo esc_attr($slideshowSpeed); ?>"/>
            </label>
        </p>

        <p>
            <label
                for="<?php echo $this->get_field_id('animationSpeed'); ?>"><?php _e('Animation Speed: (milliseconds)', 'geodirectory');?>

                <input class="widefat" id="<?php echo $this->get_field_id('animationSpeed'); ?>"
                       name="<?php echo $this->get_field_name('animationSpeed'); ?>" type="text"
                       value="<?php echo esc_attr($animationSpeed); ?>"/>
            </label>
        </p>

        <p>
            <label
                for="<?php echo $this->get_field_id('slideshow'); ?>"><?php _e('SlideShow:', 'geodirectory');?>

                <input type="checkbox" <?php if ($slideshow) {
                    echo 'checked="checked"';
                } ?> id="<?php echo $this->get_field_id('slideshow'); ?>" value="1"
                       name="<?php echo $this->get_field_name('slideshow'); ?>"/>

            </label>
        </p>

        <p>
            <label
                for="<?php echo $this->get_field_id('animationLoop'); ?>"><?php _e('AnimationLoop:', 'geodirectory');?>

                <input type="checkbox" <?php if ($animationLoop) {
                    echo 'checked="checked"';
                } ?> id="<?php echo $this->get_field_id('animationLoop'); ?>" value="1"
                       name="<?php echo $this->get_field_name('animationLoop'); ?>"/>

            </label>
        </p>

        <p>
            <label
                for="<?php echo $this->get_field_id('directionNav'); ?>"><?php _e('DirectionNav:', 'geodirectory');?>

                <input type="checkbox" <?php if ($directionNav) {
                    echo 'checked="checked"';
                } ?> id="<?php echo $this->get_field_id('directionNav'); ?>" value="1"
                       name="<?php echo $this->get_field_name('directionNav'); ?>"/>

            </label>
        </p>


        <p>
            <label
                for="<?php echo $this->get_field_id('show_title'); ?>"><?php _e('Show Title:', 'geodirectory');?>

                <input type="checkbox" <?php if ($show_title) {
                    echo 'checked="checked"';
                } ?> id="<?php echo $this->get_field_id('show_title'); ?>" value="1"
                       name="<?php echo $this->get_field_name('show_title'); ?>"/>

            </label>
        </p>
        <p>
            <label
                for="<?php echo $this->get_field_id('show_featured_only'); ?>"><?php _e('Show only featured listings:', 'geodirectory'); ?>
                <input type="checkbox" id="<?php echo $this->get_field_id('show_featured_only'); ?>"
                       name="<?php echo $this->get_field_name('show_featured_only'); ?>" <?php if ($show_featured_only) echo 'checked="checked"'; ?>
                       value="1"/>
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('add_location_filter'); ?>">
                <?php _e('Enable Location Filter:', 'geodirectory');?>
                <input type="checkbox" id="<?php echo $this->get_field_id('add_location_filter'); ?>"
                       name="<?php echo $this->get_field_name('add_location_filter'); ?>" <?php if ($add_location_filter) echo 'checked="checked"';?>
                       value="1"/>
            </label>
        </p>
        <script type="text/javascript">
            function geodir_change_category_list(post_type, selected) {

                var ajax_url = '<?php echo geodir_get_ajax_url(); ?>'

                var myurl = ajax_url + "&geodir_ajax=admin_ajax&ajax_action=get_cat_dl&post_type=" + post_type + "&selected=" + selected;

                jQuery.ajax({
                    type: "GET",
                    url: myurl,
                    success: function (data) {
                        jQuery('#<?php echo $this->get_field_id('category'); ?>').html(data);
                    }
                });

            }

            <?php if(is_active_widget( false, false, $this->id_base, true )){ ?>
            var post_type = jQuery('#<?php echo $this->get_field_id('post_type'); ?>').val();

            geodir_change_category_list(post_type, '<?php echo $category;?>');
            <?php } ?>

        </script>


    <?php
    }
} // class geodir_listing_slider_widget

register_widget('geodir_listing_slider_widget');