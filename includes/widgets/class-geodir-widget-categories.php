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
				'cpt_ajax'  => array(
                    'title' => __('Add CPT ajax select:', 'geodirectory'),
                    'desc' => __('Add CPT list as a dropdown.', 'geodirectory'),
                    'type' => 'checkbox',
                    'desc_tip' => true,
                    'value'  => '1',
                    'default'  => 0,
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
                    'desc' => __('This will show list in single column.', 'geodirectory'),
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

		add_action('wp_footer', array($this, 'add_js'));
		
        ob_start();
        // options
        $defaults = array(
            'post_type' => '0', // 0 =  all
            'hide_empty' => '0',
			'cpt_ajax' => '0'
        );

        /**
         * Parse incoming $args into an array and merge it with $defaults
         */
        $options = wp_parse_args( $args, $defaults );

        $output = geodir_cpt_categories_output($options );

		$ajax_class = ! empty( $options['cpt_ajax'] ) ? ' gd-wgt-cpt-ajax' : '';

        echo '<div class="gd-cptcats-widget' . $ajax_class . '">';
        echo $output;
        echo '</div>';

        return ob_get_clean();
    }


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
	
	public static function get_categories( $params ) {
		$params['via_ajax'] = true;
		$output = geodir_cpt_categories_output( $params );
		if ( ! empty( $output ) ) {
			echo $output;
		} else {
			echo '<div class="gd-cptcats-empty">' . __( 'No categories found','geodirectory' ) . '</div>';
		}
	}
	
	/**
	 * Adds the javascript in the footer for best of widget.
	 *
	 * @since 2.0.0
	 */
	public function add_js() {
		?>
        <script type="text/javascript">
            if (!window.gdCategoriesJs) {
                jQuery(document).ready(function() {
                    jQuery('.geodir-cat-list-tax').on("change", function(e) {
                        e.preventDefault();
                        var $widgetBox = jQuery(this).closest('.geodir-categories-container');
                        var $container = jQuery('.gd-cptcat-rows', $widgetBox);
                        $container.addClass('gd-loading');
                        $container.html('<i class="fa fa-cog fa-spin"></i>');
                        var data = {
                            'action': 'geodir_cpt_categories',
                            'security': geodirectory_params.basic_nonce,
                            'ajax_cpt': jQuery(this).val()
                        };
                        jQuery('.gd-wgt-params', $widgetBox).find('input').each(function() {
                            if (jQuery(this).attr('name')) {
                                data[jQuery(this).attr('name')] = jQuery(this).val();
                            }
                        });
                        jQuery.post(geodirectory_params.ajax_url, data, function(response) {
                            $container.html(response);
                            $container.removeClass('gd-loading');
                        });
                    })
                });
                window.gdCategoriesJs = true;
			}
        </script>
		<?php
	}
}