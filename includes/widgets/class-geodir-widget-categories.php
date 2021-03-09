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
     * @since 2.0.0
     *
     */
    public function __construct() {

        $options = array(
            'textdomain'    => GEODIRECTORY_TEXTDOMAIN,
            'block-icon'    => 'admin-site',
            'block-category'=> 'geodirectory',
            'block-keywords'=> "['categories','geo','taxonomy']",

            'class_name'    => __CLASS__,
            'base_id'       => 'gd_categories', // this us used as the widget id and the shortcode id.
            'name'          => __('GD > Categories','geodirectory'), // the name of the widget.
            //'disable_widget'=> true,
//	        'example'   => array(
//		        'hide_count'    => true,
//		        'hide_empty'    => true,
//		        'design_type'   => 'icon_top'
//	        ),
            'widget_ops'    => array(
                'classname'   => 'geodir-categories-container '.geodir_bsui_class(), // widget class
                'description' => esc_html__('Shows a list of GeoDirectory categories.','geodirectory'), // widget description
                'customize_selective_refresh' => true,
                'geodirectory' => true,
            ),
            'arguments'     => array(
                'title'  => array(
                    'title' => __('Title:', 'geodirectory'),
                    'desc' => __('The widget title.', 'geodirectory'),
                    'type' => 'text',
                    'default'  => '',
                    'desc_tip' => true,
                    'group'     => __("Title","geodirectory"),
                    'advanced' => false
                ),
                'post_type'  => array(
                    'title' => __('Post Type:', 'geodirectory'),
                    'desc' => __('The custom post types to show by default. Only used when there are multiple CPTs.', 'geodirectory'),
                    'type' => 'select',
                    'options'   =>  $this->post_type_options(),
                    'default'  => '0',
                    'desc_tip' => true,
                    'advanced' => false,
                    'group'     => __("Filters","geodirectory")
                ),
				'cpt_title'  => array(
                    'title' => __( 'Show CPT title:', 'geodirectory' ),
                    'desc' => __( 'Tick to show CPT title. Ex: Place Categories', 'geodirectory' ),
                    'type' => 'checkbox',
                    'desc_tip' => true,
                    'value'  => '1',
                    'default'  => 0,
                    'advanced' => false,
                    'group'     => __("Design","geodirectory")
                ),
                'title_tag'  => array(
	                'title' => __('Title tag:', 'geodirectory'),
	                'desc' => __('The tag used to display the auto generated CPT title.', 'geodirectory'),
	                'type' => 'select',
	                'options'   =>  array(
		                'h6'      => 'h6',
		                'h5'      => 'h5',
		                'h4'      => 'h4',
		                'h3'      => 'h3',
		                'h2'      => 'h2',
		                'span'    => 'span',
	                ),
	                'default'  => 'h4',
	                'desc_tip' => true,
	                'advanced' => false,
	                'group'     => __("Design","geodirectory")
                ),
				'cpt_ajax'  => array(
                    'title' => __('Add CPT ajax select:', 'geodirectory'),
                    'desc' => __('Add CPT list as a dropdown.', 'geodirectory'),
                    'type' => 'checkbox',
                    'desc_tip' => true,
                    'value'  => '1',
                    'default'  => 0,
                    'advanced' => false,
                    'group'     => __("Filters","geodirectory")
                ),
				'filter_ids' => array(
					'type' => 'text',
					'title' => __( 'Include/exclude categories:', 'geodirectory' ),
					'desc' => __( 'Enter a comma separated list of category ids (21,8,43) to show the these categories, or a negative list (-21,-8,-43) to exclude these categories.', 'geodirectory' ),
					'default' => '',
					'desc_tip' => true,
					'advanced' => false,
					'placeholder' => "21,8,43 (default: empty)",
					'group' => __( "Filters", "geodirectory" )
				),
                'hide_empty'  => array(
                    'title' => __('Hide empty:', 'geodirectory'),
                    'desc' => __('This will hide categories that do not have any listings.', 'geodirectory'),
                    'type' => 'checkbox',
                    'desc_tip' => true,
                    'value'  => '1',
                    'default'  => 0,
                    'advanced' => false,
                    'group'     => __("Filters","geodirectory")
                ),
                'hide_count'  => array(
                    'title' => __('Hide count:', 'geodirectory'),
                    'desc' => __('This will show the number of listings in the categories.', 'geodirectory'),
                    'type' => 'checkbox',
                    'desc_tip' => true,
                    'value'  => '1',
                    'default'  => 0,
                    'advanced' => false,
                    'group'     => __("Design","geodirectory")
                ),
                'hide_icon'  => array(
                    'title' => __('Hide icon:', 'geodirectory'),
                    'desc' => __('This will hide the category icons from the list.', 'geodirectory'),
                    'type' => 'checkbox',
                    'desc_tip' => true,
                    'value'  => '1',
                    'default'  => 0,
                    'advanced' => false,
                    'group'     => __("Design","geodirectory")
                ),
                'use_image'  => array(
	                'title' => __('Use category image:', 'geodirectory'),
	                'desc' => __('This will use the category default image instead of the icons.', 'geodirectory'),
	                'type' => 'checkbox',
	                'desc_tip' => true,
	                'value'  => '1',
	                'default'  => 0,
	                'advanced' => false,
	                'group'     => __("Design","geodirectory")
                ),
                'cpt_left'  => array(
                    'title' => __('Show single column:', 'geodirectory'),
                    'desc' => __('This will show list in single column.', 'geodirectory'),
                    'type' => 'checkbox',
                    'desc_tip' => true,
                    'value'  => '1',
                    'default'  => 0,
                    'advanced' => false,
                    'group'     => __("Design","geodirectory")
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
                    'advanced' => false,
                    'group'     => __("Sorting","geodirectory")
                ),
                'max_level'  => array(
                    'title' => __('Max sub-cat depth:', 'geodirectory'),
                    'desc' => __('The maximum number of sub category levels to show.', 'geodirectory'),
                    'type' => 'select',
                    'options'   =>  array_merge(array('all' => __('All', 'geodirectory')), range(0, 10)),
                    'default'  => '1',
                    'desc_tip' => true,
                    'advanced' => false,
                    'group'     => __("Filters","geodirectory")
                ),
                'max_count'  => array(
                    'title' => __('Max cats to show per CPT:', 'geodirectory'),
                    'desc' => __('The maximum number of categories to show per CPT.', 'geodirectory'),
                    'type' => 'select',
                    'options'   =>  array_merge(array('all' => __('All', 'geodirectory')), range(0, 10) ),
                    'default'  => 'all',
                    'desc_tip' => true,
                    'advanced' => false,
                    'group'     => __("Filters","geodirectory")
                ),
                'max_count_child'  => array(
	                'title' => __('Max sub-cat to show:', 'geodirectory'),
	                'desc' => __('The maximum number of sub categories to show.', 'geodirectory'),
	                'type' => 'select',
	                'options'   =>  array_merge(array('all' => __('All', 'geodirectory')), range(0, 10) ),
	                'default'  => 'all',
	                'desc_tip' => true,
	                'advanced' => false,
	                'group'     => __("Filters","geodirectory")
                ),
                'no_cpt_filter'  => array(
                    'title' => __("Do not filter for current viewing post type", 'geodirectory'),
                    'type' => 'checkbox',
                    'desc_tip' => true,
                    'value'  => '1',
                    'default'  => 0,
                    'advanced' => false,
                    'group'     => __("Filters","geodirectory")
                ),
                'no_cat_filter'  => array(
                    'title' => __("Tick to show all the categories. Leave unticked to show only child categories of current viewing category.", 'geodirectory'),
                    'type' => 'checkbox',
                    'desc_tip' => true,
                    'value'  => '1',
                    'default'  => 0,
                    'advanced' => false,
                    'group'     => __("Filters","geodirectory")
                ),

            )
        );


	    $design_style = geodir_design_style();

	    if($design_style){

		    // title styles
		    $title_args = geodir_get_sd_title_inputs();
		    $options['arguments'] = $options['arguments'] + $title_args;

		    $options['arguments']['design_type'] = array(
			    'title' => __('Design Type', 'geodirectory'),
			    'desc' => __('Set the design type', 'geodirectory'),
			    'type' => 'select',
			    'options'   =>  array(
				    "icon-left" => __('Icon Left', 'geodirectory'),
				    "icon-top" => __('Icon Top', 'geodirectory'),
				    "image" => __('Image Background', 'geodirectory'),
			    ),
			    'default'  => '',
			    'desc_tip' => true,
			    'advanced' => false,
			    'group'     => __("Design","geodirectory")
		    );
		    $options['arguments']['row_items'] = array(
			    'title' => __('Row Items', 'geodirectory'),
			    'desc' => __('The number of items in a row on desktop view.', 'geodirectory'),
			    'type' => 'select',
			    'options'   =>  array(
				    "" => __('Default (3)', 'geodirectory'),
				    "1" => "1",
				    "2" => "2",
				    "3" => "3",
				    "4" => "4",
				    "5" => "5",
				    "6" => "6",
			    ),
			    'default'  => '',
			    'desc_tip' => true,
			    'advanced' => false,
			    'group'     => __("Design","geodirectory")
		    );
		    $options['arguments']['row_positioning'] = array(
			    'title' => __('Row Positioning', 'geodirectory'),
			    'desc' => __('Positions items that do not fill a whole row.', 'geodirectory'),
			    'type' => 'select',
			    'options'   =>  array(
				    "" => __('Default (left)', 'geodirectory'),
				    "center" => __('Center', 'geodirectory'),
				    "right" => __('Right', 'geodirectory'),
			    ),
			    'default'  => '',
			    'desc_tip' => true,
			    'advanced' => false,
			    'group'     => __("Design","geodirectory")
		    );
		    $options['arguments']['card_padding_inside'] = array(
			    'title' => __('Card Padding Inside', 'geodirectory'),
			    'desc' => __('Set the inside padding for the card', 'geodirectory'),
			    'type' => 'select',
			    'options'   =>  array(
				    "" => "3 (default)",
				    "1" => "1",
				    "2" => "2",
				    "3" => "3",
				    "4" => "4",
				    "5" => "5",
			    ),
			    'default'  => '',
			    'desc_tip' => true,
			    'advanced' => false,
			    'element_require' => '[%design_type%]!="image"',
			    'group'     => __("Design","geodirectory")
		    );
		    $options['arguments']['card_color'] = array(
			    'title' => __('Card Color', 'geodirectory'),
			    'desc' => __('Set the card color', 'geodirectory'),
			    'type' => 'select',
			    'options'   =>  array(
				                    "" => __('Select color', 'geodirectory'),
			                    )+geodir_aui_colors(false,true),
			    'default'  => '',
			    'desc_tip' => true,
			    'advanced' => false,
			    'element_require' => '[%design_type%]!="image"',
			    'group'     => __("Design","geodirectory")
		    );

		    $options['arguments']['icon_color'] = array(
			    'title' => __('Icon Color', 'geodirectory'),
			    'desc' => __('Set the icon color', 'geodirectory'),
			    'type' => 'select',
			    'options'   =>  array(
				                    "" => __('Use Category Color (default)', 'geodirectory'),
			                    )+geodir_aui_colors(),
			    'default'  => '',
			    'desc_tip' => true,
			    'advanced' => false,
			    'element_require' => '[%design_type%]!="image"',
			    'group'     => __("Design","geodirectory")
		    );

		    $options['arguments']['icon_size'] = array(
			    'title' => __('Icon Size', 'geodirectory'),
			    'desc' => __('Set the icon size', 'geodirectory'),
			    'type' => 'select',
			    'options'   =>  array(
				    "" => __('Boxed Small', 'geodirectory'),
				    "box-medium" => __('Boxed Medium', 'geodirectory'),
				    "box-large" => __('Boxed Large', 'geodirectory'),
				    "h1" => 'XXL',
				    "h2" => 'XL',
				    "h3" => 'L',
				    "h4" => 'M',
				    "h5" => 'S',
				    "h6" => 'XS',
			    ),
			    'default'  => '',
			    'desc_tip' => true,
			    'advanced' => false,
			    'element_require' => '[%design_type%]!="image"',
			    'group'     => __("Design","geodirectory")
		    );


		    // background
		    $arguments['bg']  = geodir_get_sd_background_input();

		    // margins
		    $arguments['mt']  = geodir_get_sd_margin_input('mt');
		    $arguments['mr']  = geodir_get_sd_margin_input('mr');
		    $arguments['mb']  = geodir_get_sd_margin_input('mb',array('default'=>3));
		    $arguments['ml']  = geodir_get_sd_margin_input('ml');

		    // padding
		    $arguments['pt']  = geodir_get_sd_padding_input('pt');
		    $arguments['pr']  = geodir_get_sd_padding_input('pr');
		    $arguments['pb']  = geodir_get_sd_padding_input('pb');
		    $arguments['pl']  = geodir_get_sd_padding_input('pl');

		    // border
		    $arguments['border']  = geodir_get_sd_border_input('border');
		    $arguments['rounded']  = geodir_get_sd_border_input('rounded');
		    $arguments['rounded_size']  = geodir_get_sd_border_input('rounded_size');

		    // shadow
		    $arguments['shadow']  = geodir_get_sd_shadow_input('shadow');

		    $options['arguments'] = $options['arguments'] + $arguments;
	    }


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
            'hide_count' => '0',
	        'use_image' => '0',
			'cpt_ajax' => '0',
			'filter_ids' => array(), // comma separated ids or array
	        'title_tag' => 'h4',
            'cpt_title' => '',
            'card_color' => 'outline-primary',
            'icon_color' => '',
            'icon_size' => 'box-small',
            'design_type' => 'icon-left',
            'row_items' => '3',
	        'row_positioning'   => '',
	        'card_padding_inside'   => '3',
            'bg'    => '',
            'mt'    => '',
            'mb'    => '3',
            'mr'    => '',
            'ml'    => '',
            'pt'    => '',
            'pb'    => '',
            'pr'    => '',
            'pl'    => '',
            'border'    => '',
            'rounded'    => '',
            'rounded_size'    => '',
            'shadow'    => '',
        );

        /**
         * Parse incoming $args into an array and merge it with $defaults
         */
	    $options = wp_parse_args( $args, $defaults );

//	    print_r($args);
//	    print_r($options);

	    if(empty($options['card_color'])){$options['card_color'] = $defaults['card_color'];}
	    if(empty($options['icon_size'])){$options['icon_size'] = $defaults['icon_size'];}
	    if(empty($options['design_type'])){$options['design_type'] = $defaults['design_type'];}
	    if(empty($options['card_padding_inside'])){$options['card_padding_inside'] = $defaults['card_padding_inside'];}

        $output = self::categories_output( $options );

		$ajax_class = ! empty( $options['cpt_ajax'] ) ? ' gd-wgt-cpt-ajax' : '';

	    // wrapper class
	    $wrap_class = geodir_build_aui_class($options);

	    if($output){
		    echo '<div class="gd-categories-widget ' . $ajax_class . ' ' . $wrap_class . '">';
		    echo $output;
		    echo '</div>';
	    }


        return ob_get_clean();
    }


    /**
     * Get the post type options for search.
     *
     * @since 2.0.0
     *
     * @return array $options
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

    /**
     * Get categories.
     *
     * @since 2.0.0
     *
     * @param array $params Category parameter.
     */
	public static function get_categories( $params ) {
		$params['via_ajax'] = true;
		$output = self::categories_output( $params );
		if ( ! empty( $output ) ) {
			echo $output;
		} else {
			$design_style = geodir_design_style();
			$alert_class = $design_style ? 'alert alert-info' : '';
			echo '<div class="gd-cptcats-empty '.$alert_class.'">' . __( 'No categories found','geodirectory' ) . '</div>';
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
	            document.addEventListener("DOMContentLoaded", function(event) {
                    jQuery('.geodir-cat-list-tax').on("change", function(e) {
                        e.preventDefault();
                        var $widgetBox = jQuery(this).closest('.geodir-categories-container');
                        var $container = jQuery('.gd-cptcat-rows', $widgetBox);
                        $container.addClass('gd-loading');
                        $container.html('<i class="fas fa-cog fa-spin" aria-hidden="true"></i>');
                        var data = {
                            'action': 'geodir_cpt_categories',
                            'security': geodir_params.basic_nonce,
                            'ajax_cpt': jQuery(this).val()
                        };
                        jQuery('.gd-wgt-params', $widgetBox).find('input').each(function() {
                            if (jQuery(this).attr('name')) {
                                data[jQuery(this).attr('name')] = jQuery(this).val();
                            }
                        });
                        jQuery.post(geodir_params.ajax_url, data, function(response) {
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


	/**
	 * Get the cpt categories content.
	 *
	 * @since 1.5.4
	 * @since 1.6.6 New parameters $no_cpt_filter &no_cat_filter added.
	 *
	 * @global object $post The post object.
	 * @global bool $gd_use_query_vars If true then use query vars to get current location terms.
	 *
	 * @param array $params An array of cpt categories parameters.
	 * @return string CPT categories content.
	 */
	public static function categories_output($params) {

		global $post, $gd_use_query_vars;

		$old_gd_use_query_vars = $gd_use_query_vars;

		$gd_use_query_vars = geodir_is_page('detail') ? true : false;

		$args = wp_parse_args((array)$params,
			array(
				'title' => '',
				'title_tag' => 'span',
				'post_type' => array(), // NULL for all
				'hide_empty' => '',
				'hide_count' => '',
				'hide_icon' => '',
				'use_image' => '',
				'cpt_left' => '',
				'sort_by' => 'count',
				'max_count' => 'all',
				'max_count_child' => 'all',
				'max_level' => '1',
				'no_cpt_filter' => '',
				'no_cat_filter' => '',
				'cpt_ajax' => '',
				'filter_ids' => array(), // comma separated ids or array
				'cpt_title' => '',
				'card_color' => 'outline-primary',
				'icon_color' => '',
				'icon_size' => 'box-small',
				'design_type' => 'icon-left',
				'row_items' => '3',
				'row_positioning'   => '',
				'card_padding_inside'   => '3',

				'bg'    => '',
		        'mt'    => '',
		        'mb'    => '3',
		        'mr'    => '',
		        'ml'    => '',
		        'pt'    => '',
		        'pb'    => '',
		        'pr'    => '',
		        'pl'    => '',
		        'border'    => '',
		        'rounded'    => '',
		        'rounded_size'    => '',
		        'shadow'    => '',
			)
		);

		$sort_by = isset($args['sort_by']) && in_array($args['sort_by'], array('az', 'count')) ? sanitize_text_field( $args['sort_by'] ) : 'count';
		$cpt_filter = empty($args['no_cpt_filter']) ? true : false;
		$cat_filter = empty($args['no_cat_filter']) ? true : false;
		$cpt_ajax = ! empty( $args['cpt_ajax'] ) ? true : false;

		$gd_post_types = geodir_get_posttypes('array');

		$post_type_arr = !is_array($args['post_type']) ? explode(',', $args['post_type']) : $args['post_type'];
		$current_posttype = geodir_get_current_posttype();

		$is_listing = false;
		$is_detail = false;
		$is_category = false;
		$current_term_id = 0;
		$post_ID = 0;
		$is_listing_page = geodir_is_page('listing');
		$is_detail_page = geodir_is_page('detail');
		if ( $is_listing_page || $is_detail_page || geodir_is_page( 'search' ) ) {
			$current_posttype = geodir_get_current_posttype();

			if ($current_posttype != '' && isset($gd_post_types[$current_posttype])) {
				if ($is_detail_page) {
					$is_detail = true;
					$post_ID = is_object($post) && !empty($post->ID) ? (int)$post->ID : 0;
				} else {
					$is_listing = true;
					if (is_tax()) { // category page
						$current_term_id = get_queried_object_id();

						if ($current_term_id && $current_posttype && get_query_var('taxonomy') == $current_posttype . 'category') {
							$is_category = true;
						}
					} elseif ( geodir_is_page( 'search' ) && isset( $_REQUEST['spost_category'] ) && ( ( is_array( $_REQUEST['spost_category'] ) && ! empty( $_REQUEST['spost_category'][0] ) ) || ( ! is_array( $_REQUEST['spost_category'] ) && ! empty( $_REQUEST['spost_category'] ) ) ) ) {
						$is_category = true;

						if ( is_array( $_REQUEST['spost_category'] ) && count( $_REQUEST['spost_category'] == 1 ) ) {
							$current_term_id = absint( $_REQUEST['spost_category'][0] );
						} else {
							$current_term_id = absint( $_REQUEST['spost_category'] );
						}
					}
				}
			}
		}

		$parent_category = 0;
		if (($is_listing || $is_detail) && $cpt_filter) {
			$post_type_arr = array($current_posttype);
		}

		$post_types = array();
		if (!empty($post_type_arr)) {
			if (in_array('0', $post_type_arr)) {
				$post_types = $gd_post_types;
			} else {
				foreach ($post_type_arr as $cpt) {
					if (isset($gd_post_types[$cpt])) {
						$post_types[$cpt] = $gd_post_types[$cpt];
					}
				}
			}
		}

		if (empty($post_type_arr)) {
			$post_types = $gd_post_types;
		}

		$hide_empty = !empty($args['hide_empty']) ? true : false;
		$max_count = strip_tags($args['max_count']);
		$max_count_child = strip_tags($args['max_count_child']);
		$all_childs = $max_count_child == 'all' ? true : false;
		$max_count = $max_count > 0 ? (int)$max_count : 0;
		$max_count_child = $max_count_child > 0 ? (int)$max_count_child : 0;
		$max_level = $args['max_level']=='all' ? 0 : strval(absint($args['max_level']));
		$hide_count = !empty($args['hide_count']) ? true : false;
		$hide_icon = !empty($args['hide_icon']) ? true : false;
		$use_image = !empty($args['use_image']) ? true : false;
		$cpt_left = !empty($args['cpt_left']) ? true : false;

		// Include/exclude terms
		if ( ! empty( $args['filter_ids'] ) ) {
			$filter_ids = is_array( $args['filter_ids'] ) ? implode( ',', $args['filter_ids'] ) : $args['filter_ids'];
		} else {
			$filter_ids = '';
		}

		$filter_terms = array(
			'include' => array(),
			'exclude' => array(),
		);

		if ( ! empty( $filter_ids ) ) {
			$_filter_ids = explode( ",", $filter_ids );

			foreach( $_filter_ids as $filter_id ) {
				$filter_id = trim( $filter_id );

				if ( absint( $filter_id ) > 0 ) {
					if ( abs( $filter_id ) != $filter_id ) {
						$filter_terms['exclude'][] = absint( $filter_id );
					} else {
						$filter_terms['include'][] = absint( $filter_id );
					}
				}
			}
		}

		if($cpt_left){
			$cpt_left_class = "gd-cpt-flat";
		}else{
			$cpt_left_class = '';
		}

		$orderby = 'count';
		$order = 'DESC';
		if ($sort_by == 'az') {
			$orderby = 'name';
			$order = 'ASC';
		}

		$via_ajax = ! empty($params['via_ajax']) && wp_doing_ajax() ? true : false;
		$ajax_cpt = ! empty($params['ajax_cpt']) && $via_ajax ? sanitize_text_field( $params['ajax_cpt'] ) : '';
		$set_location = false;
		if ( $via_ajax ) {
			if ( ! empty( $params['ajax_is_listing'] ) ) {
				$is_listing = true;
			}
			if ( ! empty( $params['ajax_is_detail'] ) ) {
				$is_detail = true;
			}
			if ( ! empty( $params['ajax_is_category'] ) ) {
				$is_category = true;
			}
			if ( ! empty( $params['ajax_post_ID'] ) ) {
				$post_ID = absint( $params['ajax_post_ID'] );
			}
			if ( ! empty( $params['ajax_current_term_id'] ) ) {
				$current_term_id = absint( $params['ajax_current_term_id'] );
			}
			if ( ! empty( $params['ajax_set_location'] ) ) {
				$set_location = maybe_unserialize( sanitize_text_field( stripslashes( $params['ajax_set_location'] ) ) );

				if ( ! ( is_object( $set_location ) && GeoDir_Post_types::supports( $ajax_cpt, 'location' ) ) ) {
					$set_location = false;
				}
			}
		}

		$output = '';
		if (!empty($post_types)) {
			global $geodirectory;
			// Backup
			$backup_geodirectory = $geodirectory;

			$design_style = geodir_design_style();

			$cpt_options = array();
			$cpt_list = '';
			$cpt_count = 0;
			$cpt_opened = false;
			$cpt_closed = false;
			foreach ($post_types as $cpt => $cpt_info) {
				$cpt_count++;
				if ($ajax_cpt && $ajax_cpt !== $cpt) {
					continue;
				}
				$cpt_options[] = '<option value="' . $cpt . '" ' . selected( $cpt, $current_posttype, false ) . '>' . wp_sprintf( __( '%s Categories', 'geodirectory' ), __( $cpt_info['labels']['singular_name'], 'geodirectory' ) ) . '</option>';

				// if ajaxed then only show the first one
				if($cpt_ajax && $cpt_list != ''){ continue;}

				if ( $via_ajax && $set_location ) {
					$geodirectory->location = $set_location;
				}

				$parent_category = ($is_category && $cat_filter && $cpt == $current_posttype) ? $current_term_id : 0;
				$cat_taxonomy = $cpt . 'category';
				$skip_childs = false;

				$category_args = array(
					'orderby'    => $orderby, 
					'order'      => $order, 
					'hide_empty' => $hide_empty, 
					'number'     => $max_count,
				);

				// Include terms
				if ( ! empty( $filter_terms['include'] ) ) {
					$category_args['include'] = $filter_terms['include'];
				}

				// Exclude terms
				if ( ! empty( $filter_terms['exclude'] ) ) {
					$category_args['exclude'] = $filter_terms['exclude'];
				}

				/**
				 * Filters the category arguments passed to get_terms when fetching categories for GD Categories widget
				 */
				$category_args = apply_filters( 'geodir_gd_category_widget_category_args', $category_args, $cat_taxonomy );

				if ($cat_filter && $cpt == $current_posttype && $is_detail && $post_ID) {
					$skip_childs   = true;
					$category_args['object_ids'] = $post_ID;
					$categories    = get_terms($cat_taxonomy, $category_args);
				} else {
					$category_args['parent'] = $parent_category;
					$categories = get_terms($cat_taxonomy, $category_args);
				}

				if ($hide_empty) {
					$categories = geodir_filter_empty_terms($categories);
				}
				if ($sort_by == 'count') {
					$categories = geodir_sort_terms($categories, 'count');
				}

				if (!empty($categories)) {
					$term_icons = !$hide_icon ? geodir_get_term_icon() : array();

//					print_r($term_icons);
					$row_class = '';

					if ($is_listing) {
						$row_class = $is_category ? ' gd-cptcat-categ' : ' gd-cptcat-listing';
					}
					$cpt_row = '';
					$open_wrap = true;
					if($design_style){
						if( empty( $args['cpt_title'] ) && $cpt_opened ){
							$open_wrap = false;
						}
					}

					if($open_wrap){
						$cpt_row .= '<div class="gd-cptcat-row gd-cptcat-' . $cpt . $row_class . ' '.$cpt_left_class.'">';
						$cpt_opened = true;
					}

					if ( ! empty( $args['cpt_title'] ) && ! $cpt_ajax ) {
						$cpt_row .= '<' . esc_attr( $args['title_tag'] ) . ' class="gd-cptcat-title">' . wp_sprintf( __( '%s Categories', 'geodirectory' ), __( $cpt_info['labels']['singular_name'], 'geodirectory' ) ) . '</' . esc_attr( $args['title_tag'] ) . '>';
					}

					if($design_style && $open_wrap){

						$desktop_class = absint($args['row_items']) ? "row-cols-md-".absint($args['row_items']) : "row-cols-md-3";
						$col_class = $cpt_left ? 'row-cols-1' : 'row-cols-1 row-cols-sm-2 '.$desktop_class;

						// row_positioning
						if(!empty($args['row_positioning']) && $args['row_positioning']=='center'){
							$col_class .= " justify-content-center";
						}elseif(!empty($args['row_positioning']) && $args['row_positioning']=='right'){
							$col_class .= " justify-content-end";
						}

						$cpt_row .= '<div class="row '.$col_class.'">';
					}

					foreach ($categories as $category) {
						$term_icon = '';
						$cat_color ='';

						if(!$hide_icon) {
							$term_icon_class = '';
							if ( $design_style ) {
								$term_icon_class = 'mw-100 mh-100';
								if ( ! empty($args['design_type']) && $args['design_type'] == 'image' ) {
									$term_icon_class .= ' embed-item-contain align-top card-img';
								}
							}
							$term_icon_class = $term_icon_class != '' ? ' class="' . $term_icon_class . '"' : '';
							$term_icon_url = ! empty( $term_icons ) && isset( $term_icons[ $category->term_id ] ) ? $term_icons[ $category->term_id ] : '';
							$term_icon_url = $term_icon_url != '' ? '<img alt="' . esc_attr( $category->name ) . ' icon" src="' . $term_icon_url . '" ' . $term_icon_class . '/> ' : '';
							$cat_font_icon = get_term_meta( $category->term_id, 'ct_cat_font_icon', true );
							$cat_color     = get_term_meta( $category->term_id, 'ct_cat_color', true );
							$cat_color     = $cat_color ? $cat_color : '#ababab';

							// use_image
							if($use_image){
								$term_image = get_term_meta( $category->term_id, 'ct_cat_default_img', true );
								if(!empty($term_image['id'])){
									$cat_font_icon = false;
									$img_background_class = !empty($args['design_type']) && $args['design_type']=='image' ? ' card-img' : 'mw-100 mh-100';
									$img_args = $design_style ? array('class'=>'embed-item-cover-xy align-top '.$img_background_class) : array();
									$term_icon_url = wp_get_attachment_image($term_image['id'],'medium',false,$img_args);
								}
							}

							$term_icon     = $cat_font_icon ? '<i class="' . $cat_font_icon . '" aria-hidden="true"></i>' : $term_icon_url;
						}

						$term_link = get_term_link( $category, $category->taxonomy );
						/** Filter documented in includes/general_functions.php **/
						$term_link = apply_filters( 'geodir_category_term_link', $term_link, $category->term_id, $cpt );
						$count = $category->count;

						/**
						 * Whether include child categories posts count in parent category or not.
						 *
						 * @since 2.0.0.96
						 *
						 * @param bool $child_term_count True to include child categories posts count. Default true.
						 * @param int $category->term_id Term ID.
						 * @param string $category->taxonomy Term taxonomy.
						 */
						$child_term_count = apply_filters( 'geodir_categories_include_child_terms_posts_count', true, $category->term_id, $category->taxonomy );

						if ( $child_term_count ) {
							$tax_terms = get_terms( $category->taxonomy, array( 'child_of' => $category->term_id ) );
							if ( ! empty( $tax_terms ) ) {
								foreach ( $tax_terms as $tax_term ) {
									$count += $tax_term->count;
								}
							}
						}

						$count = !$hide_count ? ' <span class="gd-cptcat-count">' . $count . '</span>' : '';

						$cpt_row .= $design_style ? '<div class="gd-cptcat-ul gd-cptcat-parent col mb-4">' : '<ul class="gd-cptcat-ul gd-cptcat-parent  '.$cpt_left_class.'">';

						$cpt_row .= self::categories_loop_output('gd-cptcat-li-main',$hide_count,$count,$cat_color,$term_link,$category->name,$term_icon,$hide_icon,$use_image, 0, $args);

						$child_cats = '';
						if (!$skip_childs && ($all_childs || $max_count_child > 0) && ($max_level == 'all' || (int)$max_level > 0)) {
							$child_cats .= self::child_cats( $category->term_id, $cpt, $hide_empty, $hide_count, $sort_by, $max_count_child, $max_level, $term_icons, $hide_icon,$use_image, 1, $filter_terms,$args );
						}
						$cpt_row .= $child_cats;

						$cpt_row .= $design_style ? '</div>' : '</li>';

						$cpt_row .= $design_style ? '</div>' : '';
						$cpt_row .= $design_style ? '</div>' : '</ul>';
					}


					$close_wrap = true;
					if($design_style){
						if( $cpt_opened && empty( $args['cpt_title'] ) && $cpt_count < count($post_types)  ){
							$close_wrap = false;
						}
					}

					if($design_style && $close_wrap){
						$cpt_row .= '</div>';
					}

					if($close_wrap){
						$cpt_row .= '</div>';
					}


					$cpt_list .= $cpt_row;
				}


			}
			if ( !$via_ajax && $cpt_ajax && ! empty( $cpt_options ) ) {
				global $geodirectory;
				$post_type = is_array( $args['post_type'] ) ? implode( ',', $args['post_type'] ) : (! empty($args['post_type']) ? $args['post_type'] : '0');
				$output .= '<div class="gd-cptcats-select"><div class="gd-wgt-params">';
				$output .= '<input type="hidden" name="post_type" value="' . esc_attr( $post_type ) . '">';
				$output .= '<input type="hidden" name="cpt_ajax" value="' . $cpt_ajax . '">';
				$output .= '<input type="hidden" name="filter_ids" value="' . $filter_ids . '">';
				$output .= '<input type="hidden" name="cpt_title" value="' . absint( $args['cpt_title'] ) . '">';
				$output .= '<input type="hidden" name="title_tag" value="' . $args['title_tag'] . '">';
				$output .= '<input type="hidden" name="hide_empty" value="' . $hide_empty . '">';
				$output .= '<input type="hidden" name="hide_count" value="' . $hide_count . '">';
				$output .= '<input type="hidden" name="hide_icon" value="' . $hide_icon . '">';
				$output .= '<input type="hidden" name="cpt_left" value="' . $cpt_left . '">';
				$output .= '<input type="hidden" name="sort_by" value="' . $sort_by . '">';
				$output .= '<input type="hidden" name="max_level" value="' . $max_level . '">';
				$output .= '<input type="hidden" name="max_count" value="' . $max_count . '">';
				$output .= '<input type="hidden" name="no_cpt_filter" value="' . absint( $args['no_cpt_filter'] ) . '">';
				$output .= '<input type="hidden" name="no_cat_filter" value="' . absint( $args['no_cat_filter'] ) . '">';
				$output .= '<input type="hidden" name="ajax_is_listing" value="' . $is_listing . '">';


				$output .= '<input type="hidden" name="card_color" value="' . esc_attr( $args['card_color'] ) . '">';
				$output .= '<input type="hidden" name="icon_color" value="' . esc_attr( $args['icon_color'] ) . '">';
				$output .= '<input type="hidden" name="icon_size" value="' . esc_attr( $args['icon_size'] ) . '">';
				$output .= '<input type="hidden" name="design_type" value="' . esc_attr( $args['design_type'] ) . '">';
				$output .= '<input type="hidden" name="row_items" value="' . absint( $args['row_items'] ) . '">';
				$output .= '<input type="hidden" name="row_positioning" value="' . esc_attr( $args['row_positioning'] ) . '">';
				$output .= '<input type="hidden" name="card_padding_inside" value="' . absint( $args['card_padding_inside'] ) . '">';
				$output .= '<input type="hidden" name="bg" value="' . esc_attr( $args['bg'] ) . '">';
				$output .= '<input type="hidden" name="mt" value="' . absint( $args['mt'] ) . '">';
				$output .= '<input type="hidden" name="mb" value="' . absint( $args['mb'] ) . '">';
				$output .= '<input type="hidden" name="mr" value="' . absint( $args['mr'] ) . '">';
				$output .= '<input type="hidden" name="ml" value="' . absint( $args['ml'] ) . '">';
				$output .= '<input type="hidden" name="pt" value="' . absint( $args['pt'] ) . '">';
				$output .= '<input type="hidden" name="pb" value="' . absint( $args['pb'] ) . '">';
				$output .= '<input type="hidden" name="pr" value="' . absint( $args['pr'] ) . '">';
				$output .= '<input type="hidden" name="pl" value="' . absint( $args['pl'] ) . '">';
				$output .= '<input type="hidden" name="border" value="' . esc_attr( $args['border'] ) . '">';
				$output .= '<input type="hidden" name="rounded" value="' . esc_attr( $args['rounded'] ) . '">';
				$output .= '<input type="hidden" name="rounded_size" value="' . esc_attr( $args['rounded_size'] ) . '">';
				$output .= '<input type="hidden" name="shadow" value="' . esc_attr( $args['shadow'] ) . '">';

				$output .= '<input type="hidden" name="ajax_is_detail" value="' . $is_detail . '">';
				$output .= '<input type="hidden" name="ajax_is_category" value="' . $is_category . '">';
				$output .= '<input type="hidden" name="ajax_post_ID" value="' . $post_ID . '">';
				$output .= '<input type="hidden" name="ajax_current_term_id" value="' . $current_term_id . '">';
				if ( ! empty( $geodirectory->location ) ) {
					$output .= '<input type="hidden" name="ajax_set_location" value="' . esc_attr( maybe_serialize( $geodirectory->location ) ) . '">';
				}
				$select_class = $design_style ? 'form-control mb-3' : '';
				$output .= '</div><select class="geodir-cat-list-tax geodir-select '.$select_class.'" aria-label="' . esc_attr__( 'CPT Categories', 'geodirectory' ) . '">' . implode( '', $cpt_options ) . '</select>';
				$output .= '</div><div class="gd-cptcat-rows">';
			}
			$output .= $cpt_list;
			if ( !$via_ajax && $cpt_ajax && ! empty( $cpt_options ) ) {
				$output .= '</div>';
			}

			// Set back
			$geodirectory = $backup_geodirectory;
		}

		$gd_use_query_vars = $old_gd_use_query_vars;

		return $output;
	}

	public static function categories_loop_output($li_class = 'gd-cptcat-li-main',$hide_count=false,$cat_count='',$cat_color,$term_link,$cat_name,$cat_icon,$hide_icon,$use_image,$depth = 0, $args = array()){
		$cpt_row = '';

		$design_style = geodir_design_style();

		if($design_style ){

			$style = !empty($args['design_type']) ? esc_attr($args['design_type']) : 'icon-left';
			if($style=='icon-left'){$style = 'icon-left';}
			elseif($style=='icon-top'){$style = 'icon-top';}
			elseif($style=='image'){$style = 'image';}
			else{$style = 'icon-left';}
			$style = $depth ? 'sub-item' : $style;
			$template =  $design_style."/categories/$style.php";

			$cpt_row .=  geodir_get_template_html( $template ,array(
				'li_class' =>  $li_class,
				'hide_count'   =>  $hide_count,
				'cat_count'    =>  $cat_count,
				'cat_color'    =>  $cat_color,
				'term_link'    =>  $term_link,
				'cat_name' =>  $cat_name,
				'cat_icon' =>  $cat_icon,
				'hide_icon'    =>  $hide_icon,
				'use_image'    =>  $use_image,
				'depth'    =>  $depth,
				'args'  =>  $args
			));
			
//			$depth
//			$cpt_row .= $depth ? '<div class="gd-cptcat-li '.$li_class.' list-group-item list-group-item-action" >' :  '<div class="gd-cptcat-li '.$li_class.' card h-100 shadow-sm p-0 " >';
//			$cpt_row .= $depth ? '' : '<div class="card-body text-center btn btn-outline-primary p-1 py-4">';
//			$count = !$hide_count ? ' <span class="gd-cptcat-count badge badge-light ml-2">' . $cat_count . '</span>' : '';
//
//			$icon = '';
//			if(!$hide_icon){
//				$icon_size_class = isset($args['icon_size']) ? sanitize_html_class($args['icon_size']) : 'h1';
//				if($icon_size_class=='box-large'){$icon_size_class = 'iconbox fill rounded-circle bg-white iconlarge';}
//				if($icon_size_class=='box-medium'){$icon_size_class = 'iconbox fill rounded-circle bg-white iconmedium';}
//				if($icon_size_class=='box-small'){$icon_size_class = 'iconbox fill rounded-circle bg-white iconsmall';}
//				$icon_size_class .= $args['icon_position'] == 'top' ? ' mb-3 ' : ' d-inline-block mr-1 align-middle';
//				$img_class = $depth ? ' d-inline-block iconsmall mr-1' : $icon_size_class;
//				$icon .= '<div class="gd-cptcat-cat-left  text-whitex border-0 m-0 '.$img_class.'" >';
//				$icon .= "<span class='gd-cptcat-icon' style='color: $cat_color;'>$cat_icon</span>";
//				$icon .= '</div>';
//			}
//
//			if(!$depth && $args['icon_position'] != 'left'){$cpt_row .= $icon;}
//
//			$indents = $depth > 2 ? implode("", array_fill( 0,$depth - 2, "- " ) ) : '';
//
//			$link_class = $depth ? 'h6' : 'font-weight-bold h5';
//			$cpt_row .= '<div class="gd-cptcat-cat-right   text-uppercase text-truncate">';
//			$cpt_row .= '<a href="' . esc_url($term_link) . '" title="' . esc_attr($cat_name) . '" class="text-lightx text-reset stretched-link   '.$link_class.'">';
//			$cpt_row .= $indents;
//			$cpt_row .= $args['icon_position'] == 'left' ? $icon : '';
//			$cpt_row .= $cat_name  . '</a>'. $count;
//			$cpt_row .= $depth  ? '</div></div>' : '</div>';
		}else{
			$cpt_row .= '<li class="gd-cptcat-li '.$li_class.'">';
			$count = !$hide_count ? ' <span class="gd-cptcat-count">' . $cat_count . '</span>' : '';

			if(!$hide_icon){
				$cpt_row .= '<span class="gd-cptcat-cat-left" style="background: '.$cat_color.';"><a href="' . esc_url($term_link) . '" title="' . esc_attr($cat_name) . '">';
				$cpt_row .= "<span class='gd-cptcat-icon' >$cat_icon</span>";
				$cpt_row .= '</a></span>';
			}


			$cpt_row .= '<span class="gd-cptcat-cat-right"><a href="' . esc_url($term_link) . '" title="' . esc_attr($cat_name) . '">';
			$cpt_row .= $cat_name . $count . '</a></span>';
		}



		return $cpt_row;
	}

	/**
	 * Get the child categories content.
	 *
	 * @since 1.5.4
	 * @since 2.0.0.86 New parameter $filter_terms added.
	 *
	 * @param int $parent_id Parent category id.
	 * @param string $cpt The post type.
	 * @param bool $hide_empty If true then filter the empty categories.
	 * @param bool $show_count If true then category count will be displayed.
	 * @param string $sort_by Sorting order for categories.
	 * @param bool|string $max_count Max no of sub-categories count to display.
	 * @param bool|string $max_level Max depth level sub-categories to display.
	 * @param array $term_icons Array of terms icons url.
	 * @param int $depth Category depth level. Default 1.
	 * @param array $filter_terms Array of terms to include/exclude.
	 * @return string Html content.
	 */
	public static function child_cats( $parent_id, $cpt, $hide_empty, $hide_count, $sort_by, $max_count, $max_level, $term_icons,$hide_icon, $use_image, $depth = 1, $filter_terms = array(), $args = array() ) {
		$cat_taxonomy = $cpt . 'category';

		$orderby = 'count';
		$order = 'DESC';
		if ($sort_by == 'az') {
			$orderby = 'name';
			$order = 'ASC';
		}

		if ($max_level != 'all' && $depth > (int)$max_level ) {
			return '';
		}

		$term_args = array( 'orderby' => $orderby, 'order' => $order, 'hide_empty' => $hide_empty, 'parent' => $parent_id, 'number' => $max_count );
		// Include terms
		if ( ! empty( $filter_terms['include'] ) ) {
			$term_args['include'] = $filter_terms['include'];
		}

		// Exclude terms
		if ( ! empty( $filter_terms['exclude'] ) ) {
			$term_args['exclude'] = $filter_terms['exclude'];
		}

		$child_cats = get_terms( $cat_taxonomy, $term_args );
		if ( $hide_empty ) {
			$child_cats = geodir_filter_empty_terms( $child_cats );
		}

		if (empty($child_cats)) {
			return '';
		}

		if ($sort_by == 'count') {
			$child_cats = geodir_sort_terms($child_cats, 'count');
		}

		$design_style = geodir_design_style();

		if($design_style ){
			$link_height = !empty($args['card_padding_inside']) && $args['card_padding_inside'] < 3 ? "15px" : "22px";
			$content = $depth == 1 ? '<div class="gd-cptcat-li gd-cptcat-li-sub-container dropdown w-100 position-absolute" style="bottom: 0;left: 0;height:'.$link_height.';">' : '';
			$content .= $depth == 1 ? '<a class="btn btn-link z-index-1 p-0 text-reset w-100 align-top" href="#" id="cat-submenu-'.$parent_id.'" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label><span class="sr-only">' . __( "Expand sub-categories", "geodirectory" ) . '</span><i class="fas fa-chevron-down align-top"></i></a>' : '';
			$content .= $depth == 1 ? '<ul class="p-0 mt-1 gd-cptcat-ul gd-cptcat-sub gd-cptcat-sub-' . $depth . '  dropdown-menu dropdown-caret-0" aria-labelledby="cat-submenu-'.$parent_id.'">' : '';
		}else{
			$content = '<li class="gd-cptcat-li gd-cptcat-li-sub-container"><ul class="gd-cptcat-ul gd-cptcat-sub gd-cptcat-sub-' . $depth . '">';
		}


		$depth++;
		foreach ($child_cats as $category) {
			$term_icon_url = !empty($term_icons) && isset($term_icons[$category->term_id]) ? $term_icons[$category->term_id] : '';
			$term_icon_url = $term_icon_url != '' ? '<img alt="' . esc_attr($category->name) . ' icon" src="' . $term_icon_url . '" /> ' : '';
			$cat_font_icon = get_term_meta( $category->term_id, 'ct_cat_font_icon', true );
			$cat_color = get_term_meta( $category->term_id, 'ct_cat_color', true );
			$cat_color = $cat_color ? $cat_color : '#ababab';

			// use_image
			if($use_image){
				$term_image = get_term_meta( $category->term_id, 'ct_cat_default_img', true );
				if(!empty($term_image['id'])){
					$cat_font_icon = false;
					$term_icon_url = wp_get_attachment_image($term_image['id'], 'medium');
				}
			}

			$term_icon = $cat_font_icon ? '<i class="fas '.$cat_font_icon.'" aria-hidden="true"></i>' : $term_icon_url;
			$term_link = get_term_link( $category, $category->taxonomy );
			/** Filter documented in includes/general_functions.php **/
			$term_link = apply_filters( 'geodir_category_term_link', $term_link, $category->term_id, $cpt );
			$count = !$hide_count ? ' <span class="gd-cptcat-count">' . $category->count . '</span>' : '';

			$content .= self::categories_loop_output('gd-cptcat-li-sub',$hide_count,$count,$cat_color,$term_link,$category->name,$term_icon,$hide_icon,$use_image,$depth, $args);

			$content .= self::child_cats( $category->term_id, $cpt, $hide_empty, $hide_count, $sort_by, $max_count, $max_level, $term_icons,$hide_icon,$use_image, $depth, $filter_terms,$args );
		}

		if($design_style ){
			$content .= $depth == 2 ? '</ul>' : '';
			$content .= $depth == 2 ? '</div>' : '';
		}else{
			$content .= '</li>';
			$content .= '</ul>';
		}


		return $content;
	}
}