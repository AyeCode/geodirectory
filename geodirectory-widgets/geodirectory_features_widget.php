<?php

class Geodir_Features_Widget extends WP_Widget
{

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $widget_ops = array(
            'description' => __('Displays "GD Features" widget', 'geodirectory'),
            'classname' => 'widget_gd_features',
        );
        parent::__construct(false, $name = _x('GD > Features', 'widget name', 'geodirectory'), $widget_ops);

    }

    /**
     * Display the widget.
     *
     * @param array $args Widget arguments.
     * @param array $instance The widget settings, as saved by the user.
     */
    public function widget($args, $instance)
    {
        extract($args);

        $title = empty($instance['title']) ? '' : apply_filters('gd_features_widget_title', __($instance['title'], 'geodirectory'));
        $icon_color = empty($instance['icon_color']) ? '#757575' : apply_filters('gd_features_widget_icon_color', __($instance['icon_color'], 'geodirectory'));

        echo $before_widget;
        ?>
        <?php if ($title) {
        echo '<div class="geodir_list_heading clearfix">';
        echo $before_title . $title . $after_title;
        echo '</div>';
    } ?>
        <?php
        echo "<ul class='gd-features'>";

        $i = 1;
        while ($i < 100) {

            if (isset($instance['title' . $i]) || isset($instance['image' . $i]) || isset($instance['desc' . $i])) {
                echo "<li>";
                if ($instance['title' . $i]) {
                    echo "<h3 class='gd-fe-title'>" . $instance['title' . $i] . "</h3>";
                }
                if ($instance['image' . $i]) {
                    echo "<div class='gd-fe-image'>" . gd_features_parse_image($instance['image' . $i], $icon_color) . "</div>";
                }
                if ($instance['desc' . $i]) {
                    echo "<div class='gd-fe-desc'>" . gd_features_parse_desc($instance['desc' . $i]) . "</div>";
                }
                echo "</li>";
            } else {
                break;
            }

            $i++;
        }

        echo "</ul>";
        ?>
        <?php echo $after_widget; ?>
    <?php
    }

    public function update($new_instance, $old_instance)
    {
        //save the widget
        $instance = $old_instance;

        $instance['title'] = strip_tags($new_instance['title']);
        $instance['icon_color'] = strip_tags($new_instance['icon_color']);

        $i = 1;
        while ($i < 100) {

            if (isset($new_instance['title' . $i]) || isset($new_instance['image' . $i]) || isset($new_instance['desc' . $i])) {
                if ($new_instance['title' . $i]) {
                    $instance['title' . $i] = $new_instance['title' . $i];
                }
                if ($new_instance['image' . $i]) {
                    $instance['image' . $i] = $new_instance['image' . $i];
                }
                if ($new_instance['desc' . $i]) {
                    $instance['desc' . $i] = $new_instance['desc' . $i];
                }
            } else {
                break;
            }

            $i++;
        }

        return $instance;
    }

    public function form($instance)
    {
        //widgetform in backend
        $instance = wp_parse_args((array)$instance, array(
            'title' => '',
            'icon_color' => '#757575',
            'title1' => '',
            'image1' => '',
            'desc1' => '',
        ));


        $title = strip_tags($instance['title']);
        $icon_color = strip_tags($instance['icon_color']);

        ?>
        <p>
            <b>Heads Up!</b> If you don't have enough content, You can keep some boxes blank.
        </p>
        <p>
            For font awesome icons refer <a href="https://fortawesome.github.io/Font-Awesome/icons/" target="_blank">this
                page</a>. You must enter "icon class" if you are planning to use font awesome icons.
            For example if you planning to use "recycle" icon as your image, then you have to enter "fa-recycle" as
            class name which you can find in <a href="http://fortawesome.github.io/Font-Awesome/icon/recycle/"
                                                target="_blank">this page</a>
        </p>

        <p>
            <label><?php echo __("Widget Title (Optional):", 'geodirectory'); ?></label>
            <input name="<?php echo $this->get_field_name('title'); ?>" type="text"
                   value="<?php echo esc_attr($title); ?>" class="widefat"/>
        </p>

        <p>
            <label><?php echo __("Font Awesome Icon Color:", 'geodirectory'); ?></label>
            <input name="<?php echo $this->get_field_name('icon_color'); ?>" type="text"
                   value="<?php echo esc_attr($icon_color); ?>" class="widefat"/>
        </p>


        <div class="gd-fet-rep-<?php echo $this->get_field_id('xxx');?>">
            <?php

            $i = 1;
            while ($i < 100) {

                if ( $i==1 || (isset($instance['title' . $i]) || isset($instance['image' . $i]) || isset($instance['desc' . $i])) && ($instance['title' . $i] || $instance['image' . $i] || $instance['desc' . $i])) {
                    ?>
                    <div class="gdrep<?php echo $i;?>">
                        <p class="features-title">
                            <label
                                data-gdrep-title-num="1"><?php printf(__('Title %d:', 'geodirectory'), $i); ?></label>
                            <input data-gdrep-title="1" name="<?php echo $this->get_field_name('title' . $i); ?>"
                                   type="text" value="<?php echo esc_attr(strip_tags($instance['title' . $i])); ?>"
                                   class="widefat"/>
                        </p>

                        <p class="features-image">
                            <label><?php echo __('Image URL:', 'geodirectory'); ?></label>
                            <input data-gdrep-image="1" type="text" class="widefat"
                                   name="<?php echo $this->get_field_name('image' . $i); ?>"
                                   value="<?php echo esc_attr(strip_tags($instance['image' . $i])); ?>"/>
                        </p>

                        <p class="features-desc">
                            <label><?php echo __('Description:', 'geodirectory'); ?></label>
                            <textarea data-gdrep-desc="1" name="<?php echo $this->get_field_name('desc' . $i); ?>"
                                      rows="3"
                                      class="widefat"><?php echo esc_attr(strip_tags($instance['desc' . $i])); ?></textarea>
                        </p>
                    </div>
                <?php
                } else {
                    break;
                }

                $i++;
            }

            ?>
            <input class="button button-primary left"
                   onclick="gd_featured_widget_repeat('gd-fet-rep-<?php echo $this->get_field_id('xxx');?>','<?php echo $this->get_field_name('xxx');?>')"
                   type="button" value="<?php _e('Add item', 'geodirectory');?>"/>
        </div>


    <?php
    }

}

register_widget("Geodir_Features_Widget");
function gd_features_parse_image($image, $icon_color)
{
    if (substr($image, 0, 4) === "http") {
        $image = '<img src="' . $image . '" />';
    } elseif (substr($image, 0, 3) === "fa-") {
        if (empty($icon_color)) {
            $icon_color = '#757575';
        }
        $image = '<i style="color:' . $icon_color . '" class="fa ' . $image . '"></i>';
    }
    return $image;
}

function gd_features_parse_desc($desc)
{
    return $desc;
}
