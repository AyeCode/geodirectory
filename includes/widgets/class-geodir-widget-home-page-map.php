<?php
/**
 * GeoDirectory GMap - Home page Widget
 *
 * This will display Google map on home page with use of Google Map Api V3.
 *
 * @since 1.0.0
 *
 * @package GeoDirectory
 */


class GeoDir_Widget_Main_Map extends WP_Super_Duper {


    public $arguments;
    /**
     * Sets up the widgets name etc
     */
    public function __construct() {

        $options = array(
            'textdomain'    => GEODIRECTORY_TEXTDOMAIN,
            'block-icon'    => 'location-alt',
            'block-category'=> 'common',
            'block-keywords'=> "['geo','geodirectory','geodir']",
            'block-output'  => array(
                'element::img'   => array(
                    
                    'src' => geodir_plugin_url()."/assets/images/block-placeholder-map.png",
                    'alt' => __('Placeholder','geodirectory'),
                    'width' => '[%width%]',
                    'height' => '[%height%]',
                )
            ),
            'class_name'    => __CLASS__,
            'base_id'       => 'gd_main_map', // this us used as the widget id and the shortcode id.
            'name'          => __('GD > Main Map','geodirectory'), // the name of the widget.
            'widget_ops'    => array(
                'classname'   => 'geodir-main-map', // widget class
                'description' => esc_html__('This shows the main map with category and search filters.','geodirectory'), // widget description
            ),
            'arguments'     => array(
                'width'  => array(
                    'name' => __('width', 'geodirectory'),
                    'title' => __('Width:', 'geodirectory'),
                    'desc' => __('This is the width of the map, you can use % or px here.', 'geodirectory'),
                    'type' => 'text',
                    'placeholder' => '100%',
                    'desc_tip' => true,
                    'default'  => '100%',
                    'advanced' => false
                ),
                'height'  => array(
                    'name' => __('height', 'geodirectory'),
                    'title' => __('Height:', 'geodirectory'),
                    'desc' => __('This is the height of the map, you can use %, px or vh here.', 'geodirectory'),
                    'type' => 'text',
                    'placeholder' => '425px',
                    'desc_tip' => true,
                    'default'  => '425px',
                    'advanced' => false
                ),
                'maptype'  => array(
                    'name' => __('maptype', 'geodirectory'),
                    'title' => __('Map type:', 'geodirectory'),
                    'desc' => __('This is the type of map view that will be used by default.', 'geodirectory'),
                    'type' => 'select',
                    'options'   =>  array(
                        "ROADMAP" => __('Road Map', 'geodirectory'),
                        "SATELLITE" => __('Satellite Map', 'geodirectory'),
                        "HYBRID" => __('Hybrid Map', 'geodirectory'),
                        "TERRAIN" => __('Terrain Map', 'geodirectory'),
                    ),
                    'placeholder' => '425px',
                    'desc_tip' => true,
                    'default'  => 'ROADMAP',
                    'advanced' => false
                ),
                'zoom'  => array(
                    'name' => __('zoom', 'geodirectory'),
                    'title' => __('Zoom level:', 'geodirectory'),
                    'desc' => __('This is the zoom level of the map, `auto` is recommended.', 'geodirectory'),
                    'type' => 'select',
                    'options'   =>  array(
                        "0" => __('Auto', 'geodirectory'),
                        "1"  =>  "1",
                        "2"  =>  "2",
                        "3"  =>  "3",
                        "4"  =>  "4",
                        "5"  =>  "5",
                        "6"  =>  "6",
                        "7"  =>  "7",
                        "8"  =>  "8",
                        "9"  =>  "9",
                        "10"  =>  "10",
                        "11"  =>  "11",
                        "12"  =>  "12",
                        "13"  =>  "13",
                        "14"  =>  "14",
                        "15"  =>  "15",
                        "16"  =>  "16",
                        "17"  =>  "17",
                        "18"  =>  "18",
                        "19"  =>  "19",
                    ),
                    'placeholder' => '',
                    'desc_tip' => true,
                    'default'  => '0',
                    'advanced' => false
                ),
                'child_collapse'  => array(
                    'name' => __('child_collapse', 'geodirectory'),
                    'title' => __('Collapse sub categories:', 'geodirectory'),
                    'desc' => __('This will hide the sub-categories under the parent, requiring a click to show.', 'geodirectory'),
                    'type' => 'checkbox',
                    'placeholder' => '',
                    'desc_tip' => true,
                    'default'  => '0',
                    'advanced' => false
                )
            )
        );


        parent::__construct( $options );


    }


    public function output($args = array(), $widget_args = array(),$content = ''){

        ob_start();

        /**
         * @var string $width
         * @var string $height
         * @var string $maptype
         * @var string $zoom
         * @var bool $child_collapse
         */
        extract($args, EXTR_SKIP);

        //print_r($args);echo '####';exit;

        $widget_id = isset($widget_args['widget_id']) ? $widget_args['widget_id'] : 'shortcode';

        $map_args = array();
        $map_args['map_canvas_name'] = str_replace('-', '_', $widget_id); //'home_map_canvas'.$str ;
        $map_args['width'] = $width;
        $map_args['height'] = $height;
        $map_args['maptype'] = $maptype;
        $map_args['fullscreenControl'] = false;
        $map_args['zoom'] = is_int($zoom) ? $zoom : 7;
        $map_args['autozoom'] = is_int($zoom) ? 0 : 1;
        $map_args['child_collapse'] = $child_collapse;
        $map_args['enable_cat_filters'] = true;
        $map_args['enable_text_search'] = true;
        $map_args['post_type_filter'] = true;
        /** This action is documented in geodirectory_shortcodes.php */
        $map_args['location_filter'] = apply_filters('geodir_home_map_enable_location_filters', false);
        $map_args['jason_on_load'] = false;
        $map_args['marker_cluster'] = false;
        $map_args['map_resize_button'] = true;
        $map_args['map_class_name'] = 'geodir-map-home-page';

        $is_geodir_home_map_widget = true;
        $map_args['is_geodir_home_map_widget'] = $is_geodir_home_map_widget;

        geodir_draw_map($map_args);

        /* home map post type slider */
        if ($is_geodir_home_map_widget) {
            add_action('wp_footer', array(__CLASS__, 'map_script'), 100);
        }

        return ob_get_clean();
        
    }


    /**
     * Adds the javascript in the footer for home page map widget.
     *
     * @since 1.0.0
     * @since 1.5.1 Declare function public.
     */
    public function map_script() {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function () {
                geoDirMapSlide();
                jQuery(window).resize(function () {
                    jQuery('.geodir_map_container.geodir-map-home-page').each(function () {
                        jQuery(this).find('.geodir-map-posttype-list').css({'width': 'auto'});
                        jQuery(this).find('.map-places-listing ul.place-list').css({'margin-left': '0px'});
                        geoDirMapPrepare(this);
                    });
                });
            });
            function geoDirMapPrepare($thisMap) {
                var $objMpList = jQuery($thisMap).find('.geodir-map-posttype-list');
                var $objPlList = jQuery($thisMap).find('.map-places-listing ul.place-list');
                var wArrL = parseFloat(jQuery($thisMap).find('.geodir-map-navigation .geodir-leftarrow').outerWidth(true));
                var wArrR = parseFloat(jQuery($thisMap).find('.geodir-map-navigation .geodir-rightarrow').outerWidth(true));
                var ptw1 = parseFloat($objMpList.outerWidth(true));
                $objMpList.css({'margin-left': wArrL + 'px'});
                $objMpList.attr('data-width', ptw1);
                ptw1 = ptw1 - (wArrL + wArrR);
                $objMpList.width(ptw1);
                var ptw = $objPlList.width();
                var ptw2 = 0;
                $objPlList.find('li').each(function () {
                    var ptw21 = jQuery(this).outerWidth(true);
                    ptw2 += parseFloat(ptw21);
                });
                var doMov = parseFloat(ptw * 0.75);
                ptw2 = ptw2 + ( ptw2 * 0.05 );
                var maxMargin = ptw2 - ptw;
                $objPlList.attr('data-domov', doMov);
                $objPlList.attr('data-maxMargin', maxMargin);
            }
            function geoDirMapSlide() {
                jQuery('.geodir_map_container.geodir-map-home-page').each(function () {
                    var $thisMap = this;
                    geoDirMapPrepare($thisMap);
                    var $objPlList = jQuery($thisMap).find('.map-places-listing ul.place-list');
                    jQuery($thisMap).find('.geodir-leftarrow a').click(function (e) {
                        e.preventDefault();
                        var cm = $objPlList.css('margin-left');
                        var doMov = parseFloat($objPlList.attr('data-domov'));
                        var maxMargin = parseFloat($objPlList.attr('data-maxMargin'));
                        cm = parseFloat(cm);
                        if (cm == 0 || maxMargin < 0) {
                            return;
                        }
                        domargin = cm + doMov;
                        if (domargin > 0) {
                            domargin = 0;
                        }
                        $objPlList.animate({'margin-left': domargin + 'px'}, 1000);
                    });
                    jQuery($thisMap).find('.geodir-rightarrow a').click(function (e) {
                        e.preventDefault();
                        var cm = $objPlList.css('margin-left');
                        var doMov = parseFloat($objPlList.attr('data-domov'));
                        var maxMargin = parseFloat($objPlList.attr('data-maxMargin'));
                        cm = parseFloat(cm);
                        domargin = cm - doMov;
                        if (cm == ( maxMargin * -1 ) || maxMargin < 0) {
                            return;
                        }
                        if (( domargin * -1 ) > maxMargin) {
                            domargin = maxMargin * -1;
                        }
                        $objPlList.animate({'margin-left': domargin + 'px'}, 1000);
                    });
                });
            }
        </script>
        <?php
    }
    



}

add_action( 'widgets_init', function () {
    //new GeoDir_Widget_Main_Map();
    register_widget( 'GeoDir_Widget_Main_Map' );
} );


//return;
























/**
 * GeoDirectory home page map widget class.
 *
 * @since 1.0.0
 */
class GeoDir_Widget_Home_Page_Map extends WP_Widget {
    
    /**
     * Register the home page map widget.
     *
     * @since 1.0.0
     * @since 1.5.1 Changed from PHP4 style constructors to PHP5 __construct.
     */
    public function __construct() {
        $widget_ops = array(
            'classname' => 'widget Google Map in Home page',
            'description' => __( 'Google Map in Home page. It will show you google map V3 for Home page with category checkbox selection.', 'geodirectory' ),
            'customize_selective_refresh' => true,
            'geodirectory' => true,
            'gd_show_pages' => array(),
        );
        parent::__construct( 'geodir_map_v3_home_map', __('GD > GMap - Home page', 'geodirectory'), $widget_ops );
    }

    /**
     * Front-end display content for home page map widget.
     *
     * @since 1.0.0
     * @since 1.5.1 Declare function public.
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget($args, $instance) {
        extract($args, EXTR_SKIP);
        /** This action is documented in geodirectory_shortcodes.php */
        $width = empty($instance['width']) ? '100%' : apply_filters('widget_width', $instance['width']);
        /** This action is documented in geodirectory_shortcodes.php */
        $height = empty($instance['heigh']) ? '425' : apply_filters('widget_heigh', $instance['heigh']);
        /** This action is documented in geodirectory_shortcodes.php */
        $maptype = empty($instance['maptype']) ? 'ROADMAP' : apply_filters('widget_maptype', $instance['maptype']);
        /** This action is documented in geodirectory_shortcodes.php */
        $zoom = empty($instance['zoom']) ? '13' : apply_filters('widget_zoom', $instance['zoom']);
        /** This action is documented in geodirectory_shortcodes.php */
        $autozoom = empty($instance['autozoom']) ? '' : apply_filters('widget_autozoom', $instance['autozoom']);
        /** This action is documented in geodirectory_shortcodes.php */
        $child_collapse = empty($instance['child_collapse']) ? '0' : apply_filters('widget_child_collapse', $instance['child_collapse']);
        /** This action is documented in geodirectory_shortcodes.php */
        $scrollwheel = empty($instance['scrollwheel']) ? '0' : apply_filters('widget_scrollwheel', $instance['scrollwheel']);

        $map_args = array();
        $map_args['map_canvas_name'] = str_replace('-', '_', $args['widget_id']); //'home_map_canvas'.$str ;
        $map_args['width'] = $width;
        $map_args['height'] = $height;
        $map_args['maptype'] = $maptype;
        $map_args['scrollwheel'] = $scrollwheel;
        $map_args['fullscreenControl'] = false;
        $map_args['zoom'] = $zoom;
        $map_args['autozoom'] = $autozoom;
        $map_args['child_collapse'] = $child_collapse;
        $map_args['enable_cat_filters'] = true;
        $map_args['enable_text_search'] = true;
        $map_args['post_type_filter'] = true;
        /** This action is documented in geodirectory_shortcodes.php */
        $map_args['location_filter'] = apply_filters('geodir_home_map_enable_location_filters', false);
        $map_args['jason_on_load'] = false;
        $map_args['marker_cluster'] = false;
        $map_args['map_resize_button'] = true;
        $map_args['map_class_name'] = 'geodir-map-home-page';

        $is_geodir_home_map_widget = true;
        $map_args['is_geodir_home_map_widget'] = $is_geodir_home_map_widget;

        geodir_draw_map($map_args);

        /* home map post type slider */
        if ($is_geodir_home_map_widget) {
            add_action('wp_footer', array($this, 'geodir_home_map_add_script'), 100);
        }

    }

    /**
     * Sanitize home page map widget form values as they are saved.
     *
     * @since 1.0.0
     * @since 1.5.1 Declare function public.
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update($new_instance, $old_instance) {
        //save the widget
        $instance = $old_instance;
        $instance['width'] = strip_tags($new_instance['width']);
        $instance['heigh'] = ($new_instance['heigh']);
        $instance['maptype'] = ($new_instance['maptype']);
        $instance['zoom'] = ($new_instance['zoom']);
        $instance['autozoom'] = isset($new_instance['autozoom']) ? $new_instance['autozoom'] : '';
        $instance['child_collapse'] = isset($new_instance['child_collapse']) ? ($new_instance['child_collapse']) : '';
        $instance['scrollwheel'] = isset($new_instance['scrollwheel']) ? ($new_instance['scrollwheel']) : '';

        return $instance;
    }

    /**
     * Back-end home page map widget settings form.
     *
     * @since 1.0.0
     * @since 1.5.1 Declare function public.
     *
     * @param array $instance Previously saved values from database.
     */
    public function form($instance) {
        //widgetform in backend

        $instance = wp_parse_args((array)$instance, array('width' => '', 'heigh' => '', 'maptype' => '', 'zoom' => '', 'autozoom' => '', 'child_collapse' => '0', 'scrollwheel' => '0'));
        $width = strip_tags($instance['width']);
        $heigh = strip_tags($instance['heigh']);
        $maptype = strip_tags($instance['maptype']);
        $zoom = strip_tags($instance['zoom']);
        $autozoom = strip_tags($instance['autozoom']);
        $child_collapse = strip_tags($instance['child_collapse']);
        $scrollwheel = strip_tags($instance['scrollwheel']);
        $map_zoom_level = geodir_map_zoom_level();
        ?>
        <p>
            <label
                for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Map Width <small>(Default is : 100%) you can use px or % here</small>', 'geodirectory'); ?>
                :
                <input class="widefat" id="<?php echo $this->get_field_id('width'); ?>"
                       name="<?php echo $this->get_field_name('width'); ?>" type="text"
                       value="<?php echo esc_attr($width); ?>"/>
            </label>
        </p>
        <p>
            <label
                for="<?php echo $this->get_field_id('heigh'); ?>"><?php _e('Map Height <small>(Default is : 425px) you can use px or vh here</small>', 'geodirectory'); ?>
                :
                <input class="widefat" id="<?php echo $this->get_field_id('heigh'); ?>"
                       name="<?php echo $this->get_field_name('heigh'); ?>" type="text"
                       value="<?php echo esc_attr($heigh); ?>"/>
            </label>
        </p>
        <p>
            <label
                for="<?php echo $this->get_field_id('maptype'); ?>"><?php _e(' Select Map View', 'geodirectory'); ?>
                :
                <select class="widefat" id="<?php echo $this->get_field_id('maptype'); ?>"
                        name="<?php echo $this->get_field_name('maptype'); ?>">

                    <option <?php if (isset($maptype) && $maptype == 'ROADMAP') {
                        echo 'selected="selected"';
                    } ?> value="ROADMAP"><?php _e('Road Map', 'geodirectory'); ?></option>
                    <option <?php if (isset($maptype) && $maptype == 'SATELLITE') {
                        echo 'selected="selected"';
                    } ?> value="SATELLITE"><?php _e('Satellite Map', 'geodirectory'); ?></option>
                    <option <?php if (isset($maptype) && $maptype == 'HYBRID') {
                        echo 'selected="selected"';
                    } ?> value="HYBRID"><?php _e('Hybrid Map', 'geodirectory'); ?></option>
                    <option <?php selected($maptype, 'TERRAIN');?> 
                            value="TERRAIN"><?php _e('Terrain Map', 'geodirectory'); ?></option>
                </select>
            </label>
        </p>
        <p>
            <label
                for="<?php echo $this->get_field_id('zoom'); ?>"><?php _e('Map Zoom level', 'geodirectory'); ?>
                :
                <select class="widefat" id="<?php echo $this->get_field_id('zoom'); ?>"
                        name="<?php echo $this->get_field_name('zoom'); ?>"> <?php

                    foreach ($map_zoom_level as $level) {
                        $selected = '';
                        if ($level == $zoom)
                            $selected = 'selected="selected"';

                        echo '<option ' . $selected . ' value="' . $level . '">' . $level . '</option>';

                    } ?>

                </select>
            </label>
        </p>
        <p>
            <label
                for="<?php echo $this->get_field_id('autozoom'); ?>"><?php _e('Map Auto Zoom ?', 'geodirectory'); ?>
                :
                <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('autozoom'); ?>"
                       name="<?php echo $this->get_field_name('autozoom'); ?>"<?php if ($autozoom) {
                    echo 'checked="checked"';
                } ?> /></label>
        </p>

        <p>
            <label
                for="<?php echo $this->get_field_id('child_collapse'); ?>"><?php _e('Collapse child/sub categories ?', 'geodirectory'); ?>
                :
                <input id="<?php echo $this->get_field_id('child_collapse'); ?>"
                       name="<?php echo $this->get_field_name('child_collapse'); ?>" type="checkbox" value="1"
                       <?php if ($child_collapse){ ?>checked="checked" <?php } ?> />
            </label>
        </p>

        <p>
            <label
                for="<?php echo $this->get_field_id('scrollwheel'); ?>"><?php _e('Enable mouse scroll zoom ?', 'geodirectory'); ?>
                :
                <input id="<?php echo $this->get_field_id('scrollwheel'); ?>"
                       name="<?php echo $this->get_field_name('scrollwheel'); ?>" type="checkbox" value="1"
                       <?php if ($scrollwheel){ ?>checked="checked" <?php } ?> />
            </label>
        </p>

    <?php
    }

    /**
     * Adds the javascript in the footer for home page map widget.
     *
     * @since 1.0.0
     * @since 1.5.1 Declare function public.
     */
    public function geodir_home_map_add_script() {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function () {
                geoDirMapSlide();
                jQuery(window).resize(function () {
                    jQuery('.geodir_map_container.geodir-map-home-page').each(function () {
                        jQuery(this).find('.geodir-map-posttype-list').css({'width': 'auto'});
                        jQuery(this).find('.map-places-listing ul.place-list').css({'margin-left': '0px'});
                        geoDirMapPrepare(this);
                    });
                });
            });
            function geoDirMapPrepare($thisMap) {
                var $objMpList = jQuery($thisMap).find('.geodir-map-posttype-list');
                var $objPlList = jQuery($thisMap).find('.map-places-listing ul.place-list');
                var wArrL = parseFloat(jQuery($thisMap).find('.geodir-map-navigation .geodir-leftarrow').outerWidth(true));
                var wArrR = parseFloat(jQuery($thisMap).find('.geodir-map-navigation .geodir-rightarrow').outerWidth(true));
                var ptw1 = parseFloat($objMpList.outerWidth(true));
                $objMpList.css({'margin-left': wArrL + 'px'});
                $objMpList.attr('data-width', ptw1);
                ptw1 = ptw1 - (wArrL + wArrR);
                $objMpList.width(ptw1);
                var ptw = $objPlList.width();
                var ptw2 = 0;
                $objPlList.find('li').each(function () {
                    var ptw21 = jQuery(this).outerWidth(true);
                    ptw2 += parseFloat(ptw21);
                });
                var doMov = parseFloat(ptw * 0.75);
                ptw2 = ptw2 + ( ptw2 * 0.05 );
                var maxMargin = ptw2 - ptw;
                $objPlList.attr('data-domov', doMov);
                $objPlList.attr('data-maxMargin', maxMargin);
            }
            function geoDirMapSlide() {
                jQuery('.geodir_map_container.geodir-map-home-page').each(function () {
                    var $thisMap = this;
                    geoDirMapPrepare($thisMap);
                    var $objPlList = jQuery($thisMap).find('.map-places-listing ul.place-list');
                    jQuery($thisMap).find('.geodir-leftarrow a').click(function (e) {
                        e.preventDefault();
                        var cm = $objPlList.css('margin-left');
                        var doMov = parseFloat($objPlList.attr('data-domov'));
                        var maxMargin = parseFloat($objPlList.attr('data-maxMargin'));
                        cm = parseFloat(cm);
                        if (cm == 0 || maxMargin < 0) {
                            return;
                        }
                        domargin = cm + doMov;
                        if (domargin > 0) {
                            domargin = 0;
                        }
                        $objPlList.animate({'margin-left': domargin + 'px'}, 1000);
                    });
                    jQuery($thisMap).find('.geodir-rightarrow a').click(function (e) {
                        e.preventDefault();
                        var cm = $objPlList.css('margin-left');
                        var doMov = parseFloat($objPlList.attr('data-domov'));
                        var maxMargin = parseFloat($objPlList.attr('data-maxMargin'));
                        cm = parseFloat(cm);
                        domargin = cm - doMov;
                        if (cm == ( maxMargin * -1 ) || maxMargin < 0) {
                            return;
                        }
                        if (( domargin * -1 ) > maxMargin) {
                            domargin = maxMargin * -1;
                        }
                        $objPlList.animate({'margin-left': domargin + 'px'}, 1000);
                    });
                });
            }
        </script>
    <?php
    }
}

