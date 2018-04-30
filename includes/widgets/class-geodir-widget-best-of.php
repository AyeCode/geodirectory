<?php
/**
 * GeoDirectory Best of widget
 *
 * @since 1.3.9
 *
 * @package GeoDirectory
 */

/**
 * GeoDirectory Best of widget widget class.
 *
 * @since 1.3.9
 * @todo needs some styling and a bit of code tidying
 */
class GeoDir_Widget_Best_Of extends WP_Super_Duper {
    
    /**
     * Register the best of widget with WordPress.
     *
     * @since 1.3.9
     * @since 1.5.1 Changed from PHP4 style constructors to PHP5 __construct.
     */
    public function __construct() {

	    $options = array(
		    'textdomain'    => GEODIRECTORY_TEXTDOMAIN,
		    'block-icon'    => 'admin-site',
		    'block-category'=> 'widgets',
		    'block-keywords'=> "['best','top','geo']",

		    'class_name'    => __CLASS__,
		    'base_id'       => 'gd_best_of', // this us used as the widget id and the shortcode id.
		    'name'          => __('GD > Best of listings','geodirectory'), // the name of the widget.
		    'widget_ops'    => array(
			    'classname'   => 'geodir-best-of', // widget class
			    'description' => esc_html__('Shows the best of listings from categories.','geodirectory'), // widget description
			    'customize_selective_refresh' => true,
			    'geodirectory' => true,
		    ),
		    'arguments'     => array(
			    'title'  => array(
				    'title' => __('Title:', 'geodirectory'),
				    'desc' => __('The widget title.', 'geodirectory'),
				    'type' => 'text',
				    //'placeholder' => __( 'My Dashboard', 'geodirectory' ),
				    'default'  => '',
				    'desc_tip' => true,
				    'advanced' => false
			    ),
			    'post_type'  => array(
                    'title' => __('Default Post Type:', 'geodirectory'),
                    'desc' => __('The custom post types to show by default. Only used when there are multiple CPTs.', 'geodirectory'),
                    'type' => 'select',
                    'options'   =>  geodir_get_posttypes('options-plural'),
                    'default'  => 'gd_place',
                    'desc_tip' => true,
                    'advanced' => true
                ),
			    'tab_layout'  => array(
				    'title' => __('Default Post Type:', 'geodirectory'),
				    'desc' => __('The custom post types to show by default. Only used when there are multiple CPTs.', 'geodirectory'),
				    'type' => 'select',
				    'options'   =>  array(
					    'top' => __('Tabs on top','geodirectory'),
					    'left' => __('Tabs on left','geodirectory'),
					    'dropdown' => __('Tabs as dropdown','geodirectory'),
                    ),
				    'default'  => 'bestof-tabs-on-top',
				    'desc_tip' => true,
				    'advanced' => true
			    ),
			    'post_limit'  => array(
				    'title' => __('Posts to show:', 'geodirectory'),
				    'desc' => __('The number of posts to show by default.', 'geodirectory'),
				    'type' => 'number',
				    'default'  => '5',
				    'desc_tip' => true,
				    'advanced' => true
			    ),
			    'cat_limit'  => array(
				    'title' => __('Categories to show:', 'geodirectory'),
				    'desc' => __('The number of categories to show by default.', 'geodirectory'),
				    'type' => 'number',
				    'default'  => '3',
				    'desc_tip' => true,
				    'advanced' => true
			    ),
			    'add_location_filter'  => array(
				    'title' => __("Enable location filter?", 'geodirectory'),
				    'type' => 'checkbox',
				    'desc_tip' => true,
				    'value'  => '1',
				    'default'  => '1',
				    'advanced' => true
			    ),
			    'use_viewing_post_type'  => array(
				    'title' => __("Use current viewing post type?", 'geodirectory'),
				    'type' => 'checkbox',
				    'desc_tip' => true,
				    'value'  => '1',
				    'default'  => '1',
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

		add_action('wp_footer', array($this, 'best_of_js'));

		ob_start();

		// defaults

//			    array(
//				    'title' => '',
//				    'post_type' => '',
//				    'post_limit' => '5',
//				    'cat_limit' => '3',
//				    'character_count' => '20',
//				    'add_location_filter' => '1',
//				    'tab_layout' => 'bestof-tabs-on-top',
//				    'excerpt_type' => 'show-desc',
//				    'use_viewing_post_type' => ''
//			    )


		$this::best_of($widget_args, $args );

		return ob_get_clean();
	}

    /**
     * Front-end display content for best of widget.
     *
     * @since 1.3.9
     * @since 1.5.1 Added filter to view all link.
     * @since 1.5.1 Declare function public.
     *
     * @param array $args Widget arguments.
     * @param array $instance Saved values from database.
     */
    public static function best_of($args, $instance) {
        extract($args);
        /**
         * Filter the best of widget tab layout.
         *
         * @since 1.3.9
         *
         * @param string $instance ['tab_layout'] Best of widget tab layout name.
         */
        $tab_layout = empty($instance['tab_layout']) ? 'top' : apply_filters('bestof_widget_tab_layout', $instance['tab_layout']);
	    if($tab_layout=='top' || $tab_layout=='left'){
		    $tab_layout = "bestof-tabs-on-".$tab_layout;
	    }elseif($tab_layout =='dropdown'){
		    $tab_layout = "bestof-tabs-as-".$tab_layout;
	    }
        if(!defined( 'DOING_AJAX' ))  echo '<div class="geodir_bestof_widget bestof-widget-tab-layout ' . $tab_layout . '">';

        $loc_terms = geodir_get_current_location_terms();
        if (!empty($loc_terms)) {
            $cur_location = ' : ' . geodir_ucwords(str_replace('-', ' ', end($loc_terms)));
        } else {
            $cur_location = '';
        }

        /**
         * Filter the current location name.
         *
         * @since 1.3.9
         *
         * @param string $cur_location Current location name.
         */
        $cur_location = apply_filters('bestof_widget_cur_location', $cur_location);

        /**
         * Filter the widget title.
         *
         * @since 1.3.9
         *
         * @param string $instance ['title'] The widget title.
         */
        $title = empty($instance['title']) ? wp_sprintf(__('Best of %s', 'geodirectory'), get_bloginfo('name') . $cur_location) : apply_filters('bestof_widget_title', __($instance['title'], 'geodirectory'));

        /**
         * Filter the post type.
         *
         * @since 1.3.9
         *
         * @param string $instance ['post_type'] The post type.
         */
        $post_type = empty($instance['post_type']) ? 'gd_place' : apply_filters('bestof_widget_post_type', $instance['post_type']);

        /**
         * Filter the excerpt type.
         *
         * @since 1.5.4
         *
         * @param string $instance ['excerpt_type'] The excerpt type.
         */
        $excerpt_type = empty($instance['excerpt_type']) ? 'show-desc' : apply_filters('bestof_widget_excerpt_type', $instance['excerpt_type']);


        /**
         * Filter the listing limit.
         *
         * @since 1.3.9
         *
         * @param int $instance ['post_limit'] No. of posts to display.
         */
        $post_limit = empty($instance['post_limit']) ? '5' : apply_filters('bestof_widget_post_limit', $instance['post_limit']);

        /**
         * Filter the category limit.
         *
         * @since 1.3.9
         *
         * @param int $instance ['categ_limit'] No. of categories to display.
         */
        $categ_limit = empty($instance['cat_limit']) ? '3' : apply_filters('bestof_widget_cat_limit', $instance['cat_limit']);
        $use_viewing_post_type = !empty($instance['use_viewing_post_type']) ? true : false;

        /**
         * Filter the use of location filter.
         *
         * @since 1.3.9
         *
         * @param int|bool $instance ['add_location_filter'] Filter listings using current location.
         */
        $add_location_filter = empty($instance['add_location_filter']) ? '1' : apply_filters('bestof_widget_location_filter', $instance['add_location_filter']);

        // set post type to current viewing post type
        if ($use_viewing_post_type) {
            $current_post_type = geodir_get_current_posttype();
            if ($current_post_type != '' && $current_post_type != $post_type) {
                $post_type = $current_post_type;
            }
        }

        if (isset($instance['character_count'])) {
            /**
             * Filter the widget's excerpt character count.
             *
             * @since 1.3.9
             *
             * @param int $instance ['character_count'] Excerpt character count.
             */
            $character_count = apply_filters('bestof_widget_list_character_count', $instance['character_count']);
        } else {
            $character_count = '';
        }

        $category_taxonomy = geodir_get_taxonomies($post_type);

        $term_args = array(
            'hide_empty' => true,
            'parent' => 0
        );

        $term_args = apply_filters('bestof_widget_term_args', $term_args);

        if (is_tax()) {
            $taxonomy = get_query_var('taxonomy');
            $cur_term = get_query_var('term');
            $term_data = get_term_by('slug', $cur_term, $taxonomy);
			if ( ! empty( $term_data ) && ! is_wp_error( $term_data ) ) {
				$term_args['parent'] = $term_data->term_id;
			}
        }

        $terms = get_terms($category_taxonomy[0], $term_args);

        $term_reviews = geodir_count_reviews_by_terms();
        $a_terms = array();
        foreach ($terms as $term) {


            if ($term->count > 0) {
                if (isset($term_reviews[$term->term_id])) {
                    $term->review_count = $term_reviews[$term->term_id];
                } else {
                    $term->review_count = '0';
                }

                $a_terms[] = $term;
            }

        }


        $terms = apply_filters('bestof_widget_sort_terms', geodir_sort_terms($a_terms, 'review_count'), $a_terms);

        $query_args = array(
            'posts_per_page' => $post_limit,
            'is_geodir_loop' => true,
            'post_type' => $post_type,
            'gd_location' => $add_location_filter ? true : false,
            'order_by' => 'high_review'
        );
        if ($character_count >= 0) {
            $query_args['excerpt_length'] = $character_count;
        }

        $layout = array();
        if ($tab_layout == 'bestof-tabs-as-dropdown') {
            $layout[] = $tab_layout;
        } else {
            //$layout[] = 'bestof-tabs-as-dropdown';
            $layout[] = $tab_layout;
        }

	    $final_html = '';
	    $nav_html = '';

        //term navigation - start
	    if(!defined( 'DOING_AJAX' )) $nav_html = '<div class="geodir-tabs gd-bestof-tabs" id="gd-bestof-tabs" style="position:relative;">';


        foreach ($layout as $tab_layout) {

            $is_dropdown = ($tab_layout == 'bestof-tabs-as-dropdown') ? true : false;

            if ($is_dropdown) {
                $nav_html .= '<select id="geodir_bestof_tab_dd" class="geodir-select" name="geodir_bestof_tab_dd" data-placeholder="' . esc_attr(__('Select Category', 'geodirectory')) . '">';
            } else {
                $nav_html .= '<dl class="geodir-tab-head geodir-bestof-cat-list">';
                $nav_html .= '<dt></dt>';
            }


            $term_icon = geodir_get_term_icon();
            $cat_count = 0;
            if (!empty($terms)) {
                foreach ($terms as $cat) {
                    $cat_count++;
                    if ($cat_count > $categ_limit) {
                        break;
                    }
                    if ($is_dropdown) {
                        $selected = ($cat_count == 1) ? 'selected="selected"' : '';
                        $nav_html .= '<option ' . $selected . ' value="' . $cat->term_id . '">' . geodir_ucwords($cat->name) . '</option>';
                    } else {
                        if ($cat_count == 1) {
                            $nav_html .= '<dd class="geodir-tab-active">';
                        } else {
                            $nav_html .= '<dd class="">';
                        }
                        $term_icon_url = !empty($term_icon) && isset($term_icon[$cat->term_id]) ? $term_icon[$cat->term_id] : '';
                        $nav_html .= '<a data-termid="' . $cat->term_id . '" href="' . get_term_link($cat, $cat->taxonomy) . '">';
                        $nav_html .= '<img alt="' . $cat->name . ' icon" class="bestof-cat-icon" src="' . $term_icon_url . '"/>';
                        $nav_html .= '<span>';
                        $nav_html .= geodir_ucwords($cat->name);
                        $nav_html .= '<small>';
                        if (isset($cat->review_count)) {
                            $num_reviews = $cat->review_count;
                            if ($num_reviews == 0) {
                                $reviews = __('No Reviews', 'geodirectory');
                            } elseif ($num_reviews > 1) {
                                $reviews = $num_reviews . __(' Reviews', 'geodirectory');
                            } else {
                                $reviews = __('1 Review', 'geodirectory');
                            }
                            $nav_html .= $reviews;
                        }
                        $nav_html .= '</small>';
                        $nav_html .= '</span>';
                        $nav_html .= '</a>';
                        $nav_html .= '</dd>';
                    }
                }
            }

            if ($is_dropdown) {
                $nav_html .= '</select>';
            } else {
                $nav_html .= '</dl>';
            }
            //$final_html .= $nav_html;
        }

	    if(!defined( 'DOING_AJAX' )) $nav_html .= '</div>';
        //term navigation - end

        //first term listings by default - start
        $first_term = '';
        if ($terms) {
            $first_term = defined( 'DOING_AJAX' ) && isset($instance['term_id']) ? get_term( absint($instance['term_id']), $category_taxonomy[0] ) : $terms[0];
            $tax_query = array(
                'taxonomy' => $category_taxonomy[0],
                'field' => 'id',
                'terms' => $first_term->term_id
            );
            $query_args['tax_query'] = array($tax_query);
        }

	    if(!defined( 'DOING_AJAX' )) {
		    ?>
            <input type="hidden" id="bestof_widget_post_type" name="bestof_widget_post_type"
                   value="<?php echo $post_type; ?>">
            <input type="hidden" id="bestof_widget_excerpt_type" name="bestof_widget_excerpt_type"
                   value="<?php echo $excerpt_type; ?>">
            <input type="hidden" id="bestof_widget_post_limit" name="bestof_widget_post_limit"
                   value="<?php echo $post_limit; ?>">
            <input type="hidden" id="bestof_widget_taxonomy" name="bestof_widget_taxonomy"
                   value="<?php echo $category_taxonomy[0]; ?>">
            <input type="hidden" id="bestof_widget_location_filter" name="bestof_widget_location_filter"
                   value="<?php if ( $add_location_filter ) {
			           echo 1;
		           } else {
			           echo 0;
		           } ?>">
            <input type="hidden" id="bestof_widget_char_count" name="bestof_widget_char_count"
                   value="<?php echo $character_count; ?>">
            <div class="geo-bestof-contentwrap geodir-tabs-content" style="position: relative; z-index: 0;">

		    <?php
		    if ($terms) {
			    if(!defined( 'DOING_AJAX' ))echo $nav_html;
		    }
		    ?>

		    <?php




		    echo '<div id="geodir-bestof-places">';
	    }



	    //print_r($instance);
	    //print_r($query_args);
            if ($terms) {
                $view_all_link = add_query_arg(array('sort_by' => 'rating_count_desc'), get_term_link($first_term, $first_term->taxonomy));
                /**
                 * Filter the page link to view all listings.
                 *
                 * @since 1.5.1
                 *
                 * @param array $view_all_link View all listings page link.
                 * @param array $post_type The Post type.
                 * @param array $first_term The category term object.
                 */
                $view_all_link = apply_filters('geodir_bestof_widget_view_all_link', $view_all_link, $post_type, $first_term);

                echo '<h4 class="bestof-cat-title">' . wp_sprintf(__('Best of %s', 'geodirectory'), $first_term->name) . '<a href="' . esc_url($view_all_link) . '">' . __("View all", 'geodirectory') . '</a></h4>';
            }
            if ($excerpt_type == 'show-reviews') {
                add_filter('get_the_excerpt', 'best_of_show_review_in_excerpt');
            }
			/*
			 * Filter widget listings query args.
			 */
			$query_args = apply_filters( 'geodir_widget_listings_query_args', $query_args, $instance );
            geodir_bestof_places_by_term($query_args);
            if ($excerpt_type == 'show-reviews') {
                remove_filter('get_the_excerpt', 'best_of_show_review_in_excerpt');
            }


	    if(!defined( 'DOING_AJAX' )) {
		    echo "</div>";
		    ?>
		    <p id="geodir-bestof-loading" class="geodir-bestof-loading"><i class="fa fa-cog fa-spin"></i></p>

		    </div>
		    <?php //first term listings by default - end
		    ?>
		    <?php
		    echo "</div>";
	    }
    }

	// Javascript


	/**
	 * Adds the javascript in the footer for best of widget.
	 *
	 * @since 1.3.9
	 */
	public function best_of_js() {
		$ajax_nonce = wp_create_nonce("geodir-bestof-nonce");
		?>
        <script type="text/javascript">
            jQuery(document).ready(function () {
                jQuery('.geodir-bestof-cat-list a, #geodir_bestof_tab_dd').on("click change", function (e) {
                    var widgetBox = jQuery(this).closest('.geodir_bestof_widget');
                    var loading = jQuery(widgetBox).find("#geodir-bestof-loading");
                    var container = jQuery(widgetBox).find('#geodir-bestof-places');

                    jQuery(document).ajaxStart(function () {
                        //container.hide(); // Not working when more then one widget on page
                        //loading.show();
                    }).ajaxStop(function () {
                        loading.hide();
                        container.fadeIn('slow');
                    });

                    e.preventDefault();

                    var activeTab = jQuery(this).closest('dl').find('dd.geodir-tab-active');
                    activeTab.removeClass('geodir-tab-active');
                    jQuery(this).parent().addClass('geodir-tab-active');

                    var term_id = 0;
                    if (e.type === "change") {
                        term_id = jQuery(this).val();
                    } else if (e.type === "click") {
                        term_id = jQuery(this).attr('data-termid');
                    }

                    var post_type = jQuery(widgetBox).find('#bestof_widget_post_type').val();
                    var excerpt_type = jQuery(widgetBox).find('#bestof_widget_excerpt_type').val();
                    var post_limit = jQuery(widgetBox).find('#bestof_widget_post_limit').val();
                    var taxonomy = jQuery(widgetBox).find('#bestof_widget_taxonomy').val();
                    var char_count = jQuery(widgetBox).find('#bestof_widget_char_count').val();
                    var add_location_filter = jQuery(widgetBox).find('#bestof_widget_location_filter').val();

                    var data = {
                        'action': 'geodir_bestof',
                        'security': geodir_params.basic_nonce,
                        'post_type': post_type,
                        'excerpt_type': excerpt_type,
                        'post_limit': post_limit,
                        'taxonomy': taxonomy,
                        'geodir_ajax': true,
                        'term_id': term_id,
                        'char_count': char_count,
                        'add_location_filter': add_location_filter
                    };

                    container.hide();
                    loading.show();

                    console.log(data );
                    jQuery.post(geodir_params.ajax_url, data, function (response) {
                        container.html(response);
                        jQuery(widgetBox).find('.geodir_category_list_view li .geodir-post-img .geodir_thumbnail img').css('display', 'block');

                        // start lazy load if it's turned on
                        if(geodir_params.lazy_load==1){
                            geodir_init_lazy_load();
                        }

                    });
                })
            });
            jQuery(document).ready(function () {
                if (jQuery(window).width() < 660) {
                    if (jQuery('.bestof-widget-tab-layout').hasClass('bestof-tabs-on-left')) {
                        jQuery('.bestof-widget-tab-layout').removeClass('bestof-tabs-on-left').addClass('bestof-tabs-as-dropdown');
                    } else if (jQuery('.bestof-widget-tab-layout').hasClass('bestof-tabs-on-top')) {
                        jQuery('.bestof-widget-tab-layout').removeClass('bestof-tabs-on-top').addClass('bestof-tabs-as-dropdown');
                    }
                }
            });
        </script>
		<?php
	}


}