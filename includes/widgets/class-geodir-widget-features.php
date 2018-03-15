<?php
/**
 * GeoDirectory features widget.
 *
 * @package GeoDirectory
 * @since 1.5.4
 */

/**
 * GeoDirectory categories widget class.
 *
 * @since 1.5.4
 */
class GeoDir_Widget_Features extends WP_Super_Duper {

    /**
     * Register the categories with WordPress.
     *
     */
    public function __construct() {

        $options = array(
            'textdomain'    => GEODIRECTORY_TEXTDOMAIN,
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['features','geo']",

            'class_name'    => __CLASS__,
            'base_id'       => 'gd_features', // this us used as the widget id and the shortcode id.
            'name'          => __('GD > Features','geodirectory'), // the name of the widget.
            //'disable_widget'=> true,
            'widget_ops'    => array(
                'classname'   => 'geodir-wgt-features-container', // widget class
                'description' => esc_html__('Display your product/listing features on the site.','geodirectory'), // widget description
                'customize_selective_refresh' => true,
                'geodirectory' => true,
                'gd_show_pages' => array(),
            ),
            'arguments'     => array(
                'title'  => array(
                    'title' => __('Title:', 'geodirectory'),
                    'desc' => __('The widget title.', 'geodirectory'),
                    'type' => 'text',
                    'default'  => '',
                    'desc_tip' => true,
                    'advanced' => false
                ),
                'icon_color'  => array(
                    'title' => __('Icon Color:', 'geodirectory'),
                    'desc' => __('Font awesome icon color.', 'geodirectory'),
                    'type' => 'text',
                    'default'  => '#757575',
                    'desc_tip' => true,
                    'advanced' => true
                ),
				'title1'  => array(
                    'title' => __('Title:', 'geodirectory'),
                    'desc' => __('The feature title.', 'geodirectory'),
                    'type' => 'text',
                    'default'  => '',
                    'desc_tip' => true,
                    'advanced' => false
                ),
				'title1'  => array(
                    'title' => __('Feature 1 Title:', 'geodirectory'),
                    'desc' => __('Title for feature 1.', 'geodirectory'),
                    'type' => 'text',
                    'default'  => '',
                    'desc_tip' => true,
                    'advanced' => false
                ),
				'image1'  => array(
                    'title' => __('Feature 1 Image:', 'geodirectory'),
                    'desc' => __('Image for feature 1.', 'geodirectory'),
                    'type' => 'text',
                    'default'  => '',
                    'desc_tip' => true,
                    'advanced' => false
                ),
				'desc1'  => array(
                    'title' => __('Feature 1 Description:', 'geodirectory'),
                    'desc' => __('Description for feature 1.', 'geodirectory'),
                    'type' => 'text',
                    'default'  => '',
                    'desc_tip' => true,
                    'advanced' => false
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
    public function output( $args = array(), $widget_args = array(), $content = '' ) {
        $defaults = array(
            'title' => '',
            'icon_color' => '#757575',
        );
        $options = wp_parse_args( $args, $defaults );
		
		extract($options);

        $title = empty($options['title']) ? '' : apply_filters('gd_features_widget_title', __($options['title'], 'geodirectory'));
        $icon_color = empty($options['icon_color']) ? '#757575' : apply_filters('gd_features_widget_icon_color', __($options['icon_color'], 'geodirectory'));

		ob_start();

		echo $before_widget;
        
		if ($title) {
			echo '<div class="geodir_list_heading clearfix">';
			echo $before_title . $title . $after_title;
			echo '</div>';
		}

        echo "<ul class='gd-features'>";

        $i = 1;
        while ($i < 100) {

            if (isset($options['title' . $i]) || isset($options['image' . $i]) || isset($options['desc' . $i])) {
                echo "<li>";
                if ($options['title' . $i]) {
                    echo "<h3 class='gd-fe-title'>" . $options['title' . $i] . "</h3>";
                }
                if ($options['image' . $i]) {
                    echo "<div class='gd-fe-image'>" . geodir_features_parse_image($options['image' . $i], $icon_color) . "</div>";
                }
                if ($options['desc' . $i]) {
                    echo "<div class='gd-fe-desc'>" . geodir_features_parse_desc($options['desc' . $i]) . "</div>";
                }
                echo "</li>";
            } else {
                break;
            }

            $i++;
        }

        echo "</ul>";
        
		echo $after_widget;

		return ob_get_clean();
    }
}
