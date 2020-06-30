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
                    'title' => __('Max cats to show:', 'geodirectory'),
                    'desc' => __('The maximum number of categories to show.', 'geodirectory'),
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
			'cpt_title' => ''
        );

        /**
         * Parse incoming $args into an array and merge it with $defaults
         */
        $options = wp_parse_args( $args, $defaults );

//	    print_r($args);
//	    print_r($options);

        $output = self::categories_output($options );

		$ajax_class = ! empty( $options['cpt_ajax'] ) ? ' gd-wgt-cpt-ajax' : '';

	    if($output){
		    echo '<div class="gd-categories-widget ' . $ajax_class . '">';
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
		if ($is_listing_page || $is_detail_page) {
			$current_posttype = geodir_get_current_posttype();

			if ($current_posttype != '' && isset($gd_post_types[$current_posttype])) {
				if ($is_detail_page) {
					$is_detail = true;
					$post_ID = is_object($post) && !empty($post->ID) ? (int)$post->ID : 0;
				} else {
					$is_listing = true;
					if (is_tax()) { // category page
						$current_term_id = get_queried_object_id();
						$current_taxonomy = get_query_var('taxonomy');
						$current_posttype = geodir_get_current_posttype();

						if ($current_term_id && $current_posttype && get_query_var('taxonomy') == $current_posttype . 'category') {
							$is_category = true;
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

			$cpt_options = array();
			$cpt_list = '';
			foreach ($post_types as $cpt => $cpt_info) {
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
					$cpt_row = '<div class="gd-cptcat-row gd-cptcat-' . $cpt . $row_class . ' '.$cpt_left_class.'">';
					
					if ( ! empty( $args['cpt_title'] ) && ! $cpt_ajax ) {
						$cpt_row .= '<' . esc_attr( $args['title_tag'] ) . ' class="gd-cptcat-title">' . wp_sprintf( __( '%s Categories', 'geodirectory' ), __( $cpt_info['labels']['singular_name'], 'geodirectory' ) ) . '</' . esc_attr( $args['title_tag'] ) . '>';
					}

					foreach ($categories as $category) {
						$term_icon = '';
						$cat_color ='';

						if(!$hide_icon) {
							$term_icon_url = ! empty( $term_icons ) && isset( $term_icons[ $category->term_id ] ) ? $term_icons[ $category->term_id ] : '';
							$term_icon_url = $term_icon_url != '' ? '<img alt="' . esc_attr( $category->name ) . ' icon" src="' . $term_icon_url . '" /> ' : '';
							$cat_font_icon = get_term_meta( $category->term_id, 'ct_cat_font_icon', true );
							$cat_color     = get_term_meta( $category->term_id, 'ct_cat_color', true );
							$cat_color     = $cat_color ? $cat_color : '#ababab';

							// use_image
							if($use_image){
								$term_image = get_term_meta( $category->term_id, 'ct_cat_default_img', true );
								if(!empty($term_image['id'])){
									$cat_font_icon = false;
									$term_icon_url = wp_get_attachment_image($term_image['id'],'medium');
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

						$cpt_row .= '<ul class="gd-cptcat-ul gd-cptcat-parent  '.$cpt_left_class.'">';

						$cpt_row .= self::categories_loop_output('gd-cptcat-li-main',$hide_count,$count,$cat_color,$term_link,$category->name,$term_icon,$hide_icon,$use_image);

						if (!$skip_childs && ($all_childs || $max_count_child > 0) && ($max_level == 'all' || (int)$max_level > 0)) {
							$cpt_row .= self::child_cats( $category->term_id, $cpt, $hide_empty, $hide_count, $sort_by, $max_count_child, $max_level, $term_icons, $hide_icon,$use_image, 1, $filter_terms );
						}
						$cpt_row .= '</li>';
						$cpt_row .= '</ul>';
					}
					$cpt_row .= '</div>';

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
				$output .= '<input type="hidden" name="ajax_is_detail" value="' . $is_detail . '">';
				$output .= '<input type="hidden" name="ajax_is_category" value="' . $is_category . '">';
				$output .= '<input type="hidden" name="ajax_post_ID" value="' . $post_ID . '">';
				$output .= '<input type="hidden" name="ajax_current_term_id" value="' . $current_term_id . '">';
				if ( ! empty( $geodirectory->location ) ) {
					$output .= '<input type="hidden" name="ajax_set_location" value="' . esc_attr( maybe_serialize( $geodirectory->location ) ) . '">';
				}
				$output .= '</div><select class="geodir-cat-list-tax geodir-select">' . implode( '', $cpt_options ) . '</select>';
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

	public static function categories_loop_output($li_class = 'gd-cptcat-li-main',$hide_count=false,$cat_count='',$cat_color,$term_link,$cat_name,$cat_icon,$hide_icon,$use_image){
		$cpt_row = '';
		$cpt_row .= '<li class="gd-cptcat-li '.$li_class.'">';
		$count = !$hide_count ? ' <span class="gd-cptcat-count">' . $cat_count . '</span>' : '';

		if(!$hide_icon){
			$cpt_row .= '<span class="gd-cptcat-cat-left" style="background: '.$cat_color.';"><a href="' . esc_url($term_link) . '" title="' . esc_attr($cat_name) . '">';
			$cpt_row .= "<span class='gd-cptcat-icon' >$cat_icon</span>";
			$cpt_row .= '</a></span>';
		}


		$cpt_row .= '<span class="gd-cptcat-cat-right"><a href="' . esc_url($term_link) . '" title="' . esc_attr($cat_name) . '">';
		$cpt_row .= $cat_name . $count . '</a></span>';

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
	public static function child_cats( $parent_id, $cpt, $hide_empty, $hide_count, $sort_by, $max_count, $max_level, $term_icons,$hide_icon, $use_image, $depth = 1, $filter_terms = array() ) {
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

		$content = '<li class="gd-cptcat-li gd-cptcat-li-sub-container"><ul class="gd-cptcat-ul gd-cptcat-sub gd-cptcat-sub-' . $depth . '">';
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

			$content .= self::categories_loop_output('gd-cptcat-li-sub',$hide_count,$count,$cat_color,$term_link,$category->name,$term_icon,$hide_icon,$use_image);

			$content .= self::child_cats( $category->term_id, $cpt, $hide_empty, $hide_count, $sort_by, $max_count, $max_level, $term_icons,$hide_icon,$use_image, $depth, $filter_terms );
		}
		$content .= '</li></ul>';

		return $content;
	}
}