<?php
register_widget("Geodir_Features_Widget");
class Geodir_Features_Widget extends WP_Widget {

    /**
     * Class constructor.
     */
    function __construct() {
        $widget_ops = array(
            'description' => __( 'Displays "GD Features" widget', 'geodirectory' ),
            'classname' => 'widget_gd_features',
        );
        parent::__construct( false, $name = _x( 'GD > Features', 'widget name', 'geodirectory' ), $widget_ops );

    }

    /**
     * Display the widget.
     *
     * @param array $args Widget arguments.
     * @param array $instance The widget settings, as saved by the user.
     */
    function widget( $args, $instance ) {
        extract( $args );

        $title = empty($instance['title']) ? '' : apply_filters('gd_features_widget_title', __($instance['title'], 'geodirectory'));
        $icon_color = $instance['icon_color'];

        $title1 = $instance['title1'];
        $title2 = $instance['title2'];
        $title3 = $instance['title3'];
        $title4 = $instance['title4'];
        $title5 = $instance['title5'];
        $title6 = $instance['title6'];
        $title7 = $instance['title7'];
        $title8 = $instance['title8'];
        $title9 = $instance['title9'];
        $title10 = $instance['title10'];
        $title11 = $instance['title11'];
        $title12 = $instance['title12'];

        $image1 = $instance['image1'];
        $image2 = $instance['image2'];
        $image3 = $instance['image3'];
        $image4 = $instance['image4'];
        $image5 = $instance['image5'];
        $image6 = $instance['image6'];
        $image7 = $instance['image7'];
        $image8 = $instance['image8'];
        $image9 = $instance['image9'];
        $image10 = $instance['image10'];
        $image11 = $instance['image11'];
        $image12 = $instance['image12'];

        $desc1 = $instance['desc1'];
        $desc2 = $instance['desc2'];
        $desc3 = $instance['desc3'];
        $desc4 = $instance['desc4'];
        $desc5 = $instance['desc5'];
        $desc6 = $instance['desc6'];
        $desc7 = $instance['desc7'];
        $desc8 = $instance['desc8'];
        $desc9 = $instance['desc9'];
        $desc10 = $instance['desc10'];
        $desc11 = $instance['desc11'];
        $desc12 = $instance['desc12'];

        echo $before_widget;
        ?>
        <?php if ($title) {
            echo '<div class="geodir_list_heading clearfix">';
            echo $before_title . $title . $after_title;
            echo '</div>';
        } ?>
        <?php
        echo "<ul class='gd-features'>";
        if ($title1 OR $image1 OR $desc1) {
            echo "<li>";
            if ($title1) {
                echo "<h3 class='gd-fe-title'>" . $title1 . "</h3>";
            }
            if ($image1) {
                echo "<div class='gd-fe-image'>" . gd_features_parse_image($image1, $icon_color) . "</div>";
            }
            if ($desc1) {
                echo "<div class='gd-fe-desc'>" . gd_features_parse_desc($desc1) . "</div>";
            }
            echo "</li>";
        }

        if ($title2 OR $image2 OR $desc2) {
            echo "<li>";
            if ($title2) {
                echo "<h3 class='gd-fe-title'>" . $title2 . "</h3>";
            }
            if ($image2) {
                echo "<div class='gd-fe-image'>" . gd_features_parse_image($image2, $icon_color) . "</div>";
            }
            if ($desc2) {
                echo "<div class='gd-fe-desc'>" . gd_features_parse_desc($desc2) . "</div>";
            }
            echo "</li>";
        }

        if ($title3 OR $image3 OR $desc3) {
            echo "<li>";
            if ($title3) {
                echo "<h3 class='gd-fe-title'>" . $title3 . "</h3>";
            }
            if ($image3) {
                echo "<div class='gd-fe-image'>" . gd_features_parse_image($image3, $icon_color) . "</div>";
            }
            if ($desc3) {
                echo "<div class='gd-fe-desc'>" . gd_features_parse_desc($desc3) . "</div>";
            }
            echo "</li>";
        }

        if ($title4 OR $image4 OR $desc4) {
            echo "<li>";
            if ($title4) {
                echo "<h3 class='gd-fe-title'>" . $title4 . "</h3>";
            }
            if ($image4) {
                echo "<div class='gd-fe-image'>" . gd_features_parse_image($image4, $icon_color) . "</div>";
            }
            if ($desc4) {
                echo "<div class='gd-fe-desc'>" . gd_features_parse_desc($desc4) . "</div>";
            }
            echo "</li>";
        }

        if ($title5 OR $image5 OR $desc5) {
            echo "<li>";
            if ($title5) {
                echo "<h3 class='gd-fe-title'>" . $title5 . "</h3>";
            }
            if ($image5) {
                echo "<div class='gd-fe-image'>" . gd_features_parse_image($image5, $icon_color) . "</div>";
            }
            if ($desc5) {
                echo "<div class='gd-fe-desc'>" . gd_features_parse_desc($desc5) . "</div>";
            }
            echo "</li>";
        }

        if ($title6 OR $image6 OR $desc6) {
            echo "<li>";
            if ($title6) {
                echo "<h3 class='gd-fe-title'>" . $title6 . "</h3>";
            }
            if ($image6) {
                echo "<div class='gd-fe-image'>" . gd_features_parse_image($image6, $icon_color) . "</div>";
            }
            if ($desc6) {
                echo "<div class='gd-fe-desc'>" . gd_features_parse_desc($desc6) . "</div>";
            }
            echo "</li>";
        }

        if ($title7 OR $image7 OR $desc7) {
            echo "<li>";
            if ($title7) {
                echo "<h3 class='gd-fe-title'>" . $title7 . "</h3>";
            }
            if ($image7) {
                echo "<div class='gd-fe-image'>" . gd_features_parse_image($image7, $icon_color) . "</div>";
            }
            if ($desc7) {
                echo "<div class='gd-fe-desc'>" . gd_features_parse_desc($desc7) . "</div>";
            }
            echo "</li>";
        }

        if ($title8 OR $image8 OR $desc8) {
            echo "<li>";
            if ($title8) {
                echo "<h3 class='gd-fe-title'>" . $title8 . "</h3>";
            }
            if ($image8) {
                echo "<div class='gd-fe-image'>" . gd_features_parse_image($image8, $icon_color) . "</div>";
            }
            if ($desc8) {
                echo "<div class='gd-fe-desc'>" . gd_features_parse_desc($desc8) . "</div>";
            }
            echo "</li>";
        }

        if ($title9 OR $image9 OR $desc9) {
            echo "<li>";
            if ($title9) {
                echo "<h3 class='gd-fe-title'>" . $title9 . "</h3>";
            }
            if ($image9) {
                echo "<div class='gd-fe-image'>" . gd_features_parse_image($image9, $icon_color) . "</div>";
            }
            if ($desc9) {
                echo "<div class='gd-fe-desc'>" . gd_features_parse_desc($desc9) . "</div>";
            }
            echo "</li>";
        }

        if ($title10 OR $image10 OR $desc10) {
            echo "<li>";
            if ($title10) {
                echo "<h3 class='gd-fe-title'>" . $title10 . "</h3>";
            }
            if ($image10) {
                echo "<div class='gd-fe-image'>" . gd_features_parse_image($image10, $icon_color) . "</div>";
            }
            if ($desc10) {
                echo "<div class='gd-fe-desc'>" . gd_features_parse_desc($desc10) . "</div>";
            }
            echo "</li>";
        }

        if ($title11 OR $image11 OR $desc11) {
            echo "<li>";
            if ($title11) {
                echo "<h3 class='gd-fe-title'>" . $title11 . "</h3>";
            }
            if ($image11) {
                echo "<div class='gd-fe-image'>" . gd_features_parse_image($image11, $icon_color) . "</div>";
            }
            if ($desc11) {
                echo "<div class='gd-fe-desc'>" . gd_features_parse_desc($desc11) . "</div>";
            }
            echo "</li>";
        }

        if ($title12 OR $image12 OR $desc12) {
            echo "<li>";
            if ($title12) {
                echo "<h3 class='gd-fe-title'>" . $title12 . "</h3>";
            }
            if ($image12) {
                echo "<div class='gd-fe-image'>" . gd_features_parse_image($image12, $icon_color) . "</div>";
            }
            if ($desc12) {
                echo "<div class='gd-fe-desc'>" . gd_features_parse_desc($desc12) . "</div>";
            }
            echo "</li>";
        }
        echo "</ul>";
        ?>
        <?php echo $after_widget; ?>
        <?php
    }

    function update($new_instance, $old_instance)
    {
        //save the widget
        $instance = $old_instance;

        $instance['title'] = strip_tags($new_instance['title']);
        $instance['icon_color'] = strip_tags($new_instance['icon_color']);

        $instance['title1'] = $new_instance['title1'];
        $instance['title2'] = $new_instance['title2'];
        $instance['title3'] = $new_instance['title3'];
        $instance['title4'] = $new_instance['title4'];
        $instance['title5'] = $new_instance['title5'];
        $instance['title6'] = $new_instance['title6'];
        $instance['title7'] = $new_instance['title7'];
        $instance['title8'] = $new_instance['title8'];
        $instance['title9'] = $new_instance['title9'];
        $instance['title10'] = $new_instance['title10'];
        $instance['title11'] = $new_instance['title11'];
        $instance['title12'] = $new_instance['title12'];
        //image
        $instance['image1'] = $new_instance['image1'];
        $instance['image2'] = $new_instance['image2'];
        $instance['image3'] = $new_instance['image3'];
        $instance['image4'] = $new_instance['image4'];
        $instance['image5'] = $new_instance['image5'];
        $instance['image6'] = $new_instance['image6'];
        $instance['image7'] = $new_instance['image7'];
        $instance['image8'] = $new_instance['image8'];
        $instance['image9'] = $new_instance['image9'];
        $instance['image10'] = $new_instance['image10'];
        $instance['image11'] = $new_instance['image11'];
        $instance['image12'] = $new_instance['image12'];
        //Description
        $instance['desc1'] = $new_instance['desc1'];
        $instance['desc2'] = $new_instance['desc2'];
        $instance['desc3'] = $new_instance['desc3'];
        $instance['desc4'] = $new_instance['desc4'];
        $instance['desc5'] = $new_instance['desc5'];
        $instance['desc6'] = $new_instance['desc6'];
        $instance['desc7'] = $new_instance['desc7'];
        $instance['desc8'] = $new_instance['desc8'];
        $instance['desc9'] = $new_instance['desc9'];
        $instance['desc10'] = $new_instance['desc10'];
        $instance['desc11'] = $new_instance['desc11'];
        $instance['desc12'] = $new_instance['desc12'];
        return $instance;
    }

    function form($instance)
    {
        //widgetform in backend
        $instance = wp_parse_args((array)$instance, array(
            'title' => '',
            'icon_color' => '#757575',
            'title1' => '',
            'title2' => '',
            'title3' => '',
            'title4' => '',
            'title5' => '',
            'title6' => '',
            'title7' => '',
            'title8' => '',
            'title9' => '',
            'title10' => '',
            'title11' => '',
            'title12' => '',
            'image1' => '',
            'image2' => '',
            'image3' => '',
            'image4' => '',
            'image5' => '',
            'image6' => '',
            'image7' => '',
            'image8' => '',
            'image9' => '',
            'image10' => '',
            'image11' => '',
            'image12' => '',
            'desc1' => '',
            'desc2' => '',
            'desc3' => '',
            'desc4' => '',
            'desc5' => '',
            'desc6' => '',
            'desc7' => '',
            'desc8' => '',
            'desc9' => '',
            'desc10' => '',
            'desc11' => '',
            'desc12' => '',
        ));
        $title = strip_tags($instance['title']);
        $icon_color = strip_tags($instance['icon_color']);

        $title1 = strip_tags($instance['title1']);
        $title2 = strip_tags($instance['title2']);
        $title3 = strip_tags($instance['title3']);
        $title4 = strip_tags($instance['title4']);
        $title5 = strip_tags($instance['title5']);
        $title6 = strip_tags($instance['title6']);
        $title7 = strip_tags($instance['title7']);
        $title8 = strip_tags($instance['title8']);
        $title9 = strip_tags($instance['title9']);
        $title10 = strip_tags($instance['title10']);
        $title11 = strip_tags($instance['title11']);
        $title12 = strip_tags($instance['title12']);

        $image1 = strip_tags($instance['image1']);
        $image2 = strip_tags($instance['image2']);
        $image3 = strip_tags($instance['image3']);
        $image4 = strip_tags($instance['image4']);
        $image5 = strip_tags($instance['image5']);
        $image6 = strip_tags($instance['image6']);
        $image7 = strip_tags($instance['image7']);
        $image8 = strip_tags($instance['image8']);
        $image9 = strip_tags($instance['image9']);
        $image10 = strip_tags($instance['image10']);
        $image11 = strip_tags($instance['image11']);
        $image12 = strip_tags($instance['image12']);

        $desc1 = strip_tags($instance['desc1']);
        $desc2 = strip_tags($instance['desc2']);
        $desc3 = strip_tags($instance['desc3']);
        $desc4 = strip_tags($instance['desc4']);
        $desc5 = strip_tags($instance['desc5']);
        $desc6 = strip_tags($instance['desc6']);
        $desc7 = strip_tags($instance['desc7']);
        $desc8 = strip_tags($instance['desc8']);
        $desc9 = strip_tags($instance['desc9']);
        $desc10 = strip_tags($instance['desc10']);
        $desc11 = strip_tags($instance['desc11']);
        $desc12 = strip_tags($instance['desc12']);
        ?>
        <p>
            <b>Heads Up!</b> If you don't have enough content, You can keep some boxes blank.
        </p>
        <p>
            For font awesome icons refer <a href="https://fortawesome.github.io/Font-Awesome/icons/" target="_blank">this page</a>. You must enter "icon class" if you are planning to use font awesome icons.
            For example if you planning to use "recycle" icon as your image, then you have to enter "fa-recycle" as class name which you can find in <a href="http://fortawesome.github.io/Font-Awesome/icon/recycle/" target="_blank">this page</a>
        </p>

        <p>
            <label><?php echo __("Widget Title (Optional):", 'geodirectory'); ?></label>
            <input name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" class="widefat"/>
        </p>

        <p>
            <label><?php echo __("Font Awesome Icon Color:", 'geodirectory'); ?></label>
            <input name="<?php echo $this->get_field_name('icon_color'); ?>" type="text" value="<?php echo esc_attr($icon_color); ?>" class="widefat"/>
        </p>

        <p class="features-title">
            <label><?php echo __( 'Title 1:', 'geodirectory' ); ?></label>
            <input name="<?php echo $this->get_field_name( 'title1' ); ?>" type="text" value="<?php echo esc_attr($title1); ?>" class="widefat" />
        </p>

        <p class="features-image">
            <label><?php echo __( 'Image URL:', 'geodirectory' ); ?></label>
            <input type="text" class="widefat" name="<?php echo $this->get_field_name( 'image1' ); ?>" value="<?php echo esc_attr($image1); ?>" />
        </p>

        <p class="features-desc">
            <label><?php echo __( 'Description:', 'geodirectory' ); ?></label>
            <textarea name="<?php echo $this->get_field_name( 'desc1' ); ?>" rows="3" class="widefat"><?php echo esc_attr($desc1); ?></textarea>
        </p>

        <p class="features-title">
            <label><?php echo __( 'Title 2:', 'geodirectory' ); ?></label>
            <input name="<?php echo $this->get_field_name( 'title2' ); ?>" type="text" value="<?php echo esc_attr($title2); ?>" class="widefat" />
        </p>

        <p class="features-image">
            <label><?php echo __( 'Image URL:', 'geodirectory' ); ?></label>
            <input type="text" class="widefat" name="<?php echo $this->get_field_name( 'image2' ); ?>" value="<?php echo esc_attr($image2); ?>" />
        </p>

        <p class="features-desc">
            <label><?php echo __( 'Description:', 'geodirectory' ); ?></label>
            <textarea name="<?php echo $this->get_field_name( 'desc2' ); ?>" rows="3" class="widefat"><?php echo esc_attr($desc2); ?></textarea>
        </p>

        <p class="features-title">
            <label><?php echo __( 'Title 3:', 'geodirectory' ); ?></label>
            <input name="<?php echo $this->get_field_name( 'title3' ); ?>" type="text" value="<?php echo esc_attr($title3); ?>" class="widefat" />
        </p>

        <p class="features-image">
            <label><?php echo __( 'Image URL:', 'geodirectory' ); ?></label>
            <input type="text" class="widefat" name="<?php echo $this->get_field_name( 'image3' ); ?>" value="<?php echo esc_attr($image3); ?>" />
        </p>

        <p class="features-desc">
            <label><?php echo __( 'Description:', 'geodirectory' ); ?></label>
            <textarea name="<?php echo $this->get_field_name( 'desc3' ); ?>" rows="3" class="widefat"><?php echo esc_attr($desc3); ?></textarea>
        </p>

        <p class="features-title">
            <label><?php echo __( 'Title 4:', 'geodirectory' ); ?></label>
            <input name="<?php echo $this->get_field_name( 'title4' ); ?>" type="text" value="<?php echo esc_attr($title4); ?>" class="widefat" />
        </p>

        <p class="features-image">
            <label><?php echo __( 'Image URL:', 'geodirectory' ); ?></label>
            <input type="text" class="widefat" name="<?php echo $this->get_field_name( 'image4' ); ?>" value="<?php echo esc_attr($image4); ?>" />
        </p>

        <p class="features-desc">
            <label><?php echo __( 'Description:', 'geodirectory' ); ?></label>
            <textarea name="<?php echo $this->get_field_name( 'desc4' ); ?>" rows="3" class="widefat"><?php echo esc_attr($desc4); ?></textarea>
        </p>

        <p class="features-title">
            <label><?php echo __( 'Title 5:', 'geodirectory' ); ?></label>
            <input name="<?php echo $this->get_field_name( 'title5' ); ?>" type="text" value="<?php echo esc_attr($title5); ?>" class="widefat" />
        </p>

        <p class="features-image">
            <label><?php echo __( 'Image URL:', 'geodirectory' ); ?></label>
            <input type="text" class="widefat" name="<?php echo $this->get_field_name( 'image5' ); ?>" value="<?php echo esc_attr($image5); ?>" />
        </p>

        <p class="features-desc">
            <label><?php echo __( 'Description:', 'geodirectory' ); ?></label>
            <textarea name="<?php echo $this->get_field_name( 'desc5' ); ?>" rows="3" class="widefat"><?php echo esc_attr($desc5); ?></textarea>
        </p>

        <p class="features-title">
            <label><?php echo __( 'Title 6:', 'geodirectory' ); ?></label>
            <input name="<?php echo $this->get_field_name( 'title6' ); ?>" type="text" value="<?php echo esc_attr($title6); ?>" class="widefat" />
        </p>

        <p class="features-image">
            <label><?php echo __( 'Image URL:', 'geodirectory' ); ?></label>
            <input type="text" class="widefat" name="<?php echo $this->get_field_name( 'image6' ); ?>" value="<?php echo esc_attr($image6); ?>" />
        </p>

        <p class="features-desc">
            <label><?php echo __( 'Description:', 'geodirectory' ); ?></label>
            <textarea name="<?php echo $this->get_field_name( 'desc6' ); ?>" rows="3" class="widefat"><?php echo esc_attr($desc6); ?></textarea>
        </p>

        <p class="features-title">
            <label><?php echo __( 'Title 7:', 'geodirectory' ); ?></label>
            <input name="<?php echo $this->get_field_name( 'title7' ); ?>" type="text" value="<?php echo esc_attr($title7); ?>" class="widefat" />
        </p>

        <p class="features-image">
            <label><?php echo __( 'Image URL:', 'geodirectory' ); ?></label>
            <input type="text" class="widefat" name="<?php echo $this->get_field_name( 'image7' ); ?>" value="<?php echo esc_attr($image7); ?>" />
        </p>

        <p class="features-desc">
            <label><?php echo __( 'Description:', 'geodirectory' ); ?></label>
            <textarea name="<?php echo $this->get_field_name( 'desc7' ); ?>" rows="3" class="widefat"><?php echo esc_attr($desc7); ?></textarea>
        </p>

        <p class="features-title">
            <label><?php echo __( 'Title 8:', 'geodirectory' ); ?></label>
            <input name="<?php echo $this->get_field_name( 'title8' ); ?>" type="text" value="<?php echo esc_attr($title8); ?>" class="widefat" />
        </p>

        <p class="features-image">
            <label><?php echo __( 'Image URL:', 'geodirectory' ); ?></label>
            <input type="text" class="widefat" name="<?php echo $this->get_field_name( 'image8' ); ?>" value="<?php echo esc_attr($image8); ?>" />
        </p>

        <p class="features-desc">
            <label><?php echo __( 'Description:', 'geodirectory' ); ?></label>
            <textarea name="<?php echo $this->get_field_name( 'desc8' ); ?>" rows="3" class="widefat"><?php echo esc_attr($desc8); ?></textarea>
        </p>

        <p class="features-title">
            <label><?php echo __( 'Title 9:', 'geodirectory' ); ?></label>
            <input name="<?php echo $this->get_field_name( 'title9' ); ?>" type="text" value="<?php echo esc_attr($title9); ?>" class="widefat" />
        </p>

        <p class="features-image">
            <label><?php echo __( 'Image URL:', 'geodirectory' ); ?></label>
            <input type="text" class="widefat" name="<?php echo $this->get_field_name( 'image9' ); ?>" value="<?php echo esc_attr($image9); ?>" />
        </p>

        <p class="features-desc">
            <label><?php echo __( 'Description:', 'geodirectory' ); ?></label>
            <textarea name="<?php echo $this->get_field_name( 'desc9' ); ?>" rows="3" class="widefat"><?php echo esc_attr($desc9); ?></textarea>
        </p>

        <p class="features-title">
            <label><?php echo __( 'Title 10:', 'geodirectory' ); ?></label>
            <input name="<?php echo $this->get_field_name( 'title10' ); ?>" type="text" value="<?php echo esc_attr($title10); ?>" class="widefat" />
        </p>

        <p class="features-image">
            <label><?php echo __( 'Image URL:', 'geodirectory' ); ?></label>
            <input type="text" class="widefat" name="<?php echo $this->get_field_name( 'image10' ); ?>" value="<?php echo esc_attr($image10); ?>" />
        </p>

        <p class="features-desc">
            <label><?php echo __( 'Description:', 'geodirectory' ); ?></label>
            <textarea name="<?php echo $this->get_field_name( 'desc10' ); ?>" rows="3" class="widefat"><?php echo esc_attr($desc10); ?></textarea>
        </p>

        <p class="features-title">
            <label><?php echo __( 'Title 11:', 'geodirectory' ); ?></label>
            <input name="<?php echo $this->get_field_name( 'title11' ); ?>" type="text" value="<?php echo esc_attr($title11); ?>" class="widefat" />
        </p>

        <p class="features-image">
            <label><?php echo __( 'Image URL:', 'geodirectory' ); ?></label>
            <input type="text" class="widefat" name="<?php echo $this->get_field_name( 'image11' ); ?>" value="<?php echo esc_attr($image11); ?>" />
        </p>

        <p class="features-desc">
            <label><?php echo __( 'Description:', 'geodirectory' ); ?></label>
            <textarea name="<?php echo $this->get_field_name( 'desc11' ); ?>" rows="3" class="widefat"><?php echo esc_attr($desc11); ?></textarea>
        </p>

        <p class="features-title">
            <label><?php echo __( 'Title 12:', 'geodirectory' ); ?></label>
            <input name="<?php echo $this->get_field_name( 'title12' ); ?>" type="text" value="<?php echo esc_attr($title12); ?>" class="widefat" />
        </p>

        <p class="features-image">
            <label><?php echo __( 'Image URL:', 'geodirectory' ); ?></label>
            <input type="text" class="widefat" name="<?php echo $this->get_field_name( 'image12' ); ?>" value="<?php echo esc_attr($image12); ?>" />
        </p>

        <p class="features-desc">
            <label><?php echo __( 'Description:', 'geodirectory' ); ?></label>
            <textarea name="<?php echo $this->get_field_name( 'desc12' ); ?>" rows="3" class="widefat"><?php echo esc_attr($desc12); ?></textarea>
        </p>
        <?php
    }

}

function gd_features_parse_image($image, $icon_color) {
    if (substr( $image, 0, 4 ) === "http") {
        $image = '<img src="'.$image.'" />';
    } elseif (substr( $image, 0, 3 ) === "fa-") {
        if (empty($icon_color)) {
            $icon_color = '#757575';
        }
        $image = '<i style="color:'.$icon_color.'" class="fa '.$image.'"></i>';
    }
    return $image;
}

function gd_features_parse_desc($desc) {
    return $desc;
}