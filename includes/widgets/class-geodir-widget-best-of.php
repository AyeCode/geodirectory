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

	private $w_settings = array();
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
		    'block-category'=> 'geodirectory',
		    'block-keywords'=> "['best','top','geo']",

		    'class_name'    => __CLASS__,
		    'base_id'       => 'gd_best_of', // this us used as the widget id and the shortcode id.
		    'name'          => __('GD > Best of listings','geodirectory'), // the name of the widget.
		    'widget_ops'    => array(
			    'classname'   => 'geodir-best-of '.geodir_bsui_class(), // widget class
			    'description' => esc_html__('Shows the best of listings from categories.','geodirectory'), // widget description
			    'customize_selective_refresh' => true,
			    'geodirectory' => true,
		    )
	    );

	    parent::__construct( $options );
    }
	/**
	 * Set widget arguments.
	 *
	 */
	public function set_arguments() {

		$design_style = geodir_design_style();
		$arguments = array();
		$arguments ['title'] = array(
				'title' => __('Title:', 'geodirectory'),
				'desc' => __('The widget title.', 'geodirectory'),
				'type' => 'text',
				'default'  => '',
				'desc_tip' => true,
				'advanced' => false
			);
			$arguments['post_type'] = array(
				'title' => __('Default Post Type:', 'geodirectory'),
				'desc' => __('The custom post types to show by default. Only used when there are multiple CPTs.', 'geodirectory'),
				'type' => 'select',
				'options'   =>  geodir_get_posttypes('options-plural'),
				'default'  => 'gd_place',
				'desc_tip' => true,
				'advanced' => true
			);
			$arguments['tab_layout'] = array(
				'title' => __('Tabs layout:', 'geodirectory'),
				'desc' => __('The custom post types to show by default. Only used when there are multiple CPTs.', 'geodirectory'),
				'type' => 'select',
				'options'   =>  array(
					'top' => __('Tabs on top','geodirectory'),
					'left' => __('Tabs on left','geodirectory'),
					'dropdown' => __('Tabs as dropdown','geodirectory'),
				),
				'default'  => 'bestof-tabs-on-top',
				'desc_tip' => true,
				'advanced' => false,
				'group'     => __("Design","geodirectory")
			);

		if ( $design_style ) {
			$arguments['tab_head_align'] = array(
				'title' => __('Tabs align', 'geodirectory'),
				'desc' => __('How he tabs should be aligned.', 'geodirectory'),
				'type' => 'select',
				'options'   =>  array(
					'' => __('Left (default)','geodirectory'),
					'center' => __('Center','geodirectory'),
					'right' => __('Right','geodirectory'),
				),
				'default'  => 'bestof-tabs-on-top',
				'desc_tip' => true,
				'advanced' => false,
				'element_require' => '[%tab_layout%]=="top"',
				'group'     => __("Design","geodirectory")
			);
		}
			$arguments['layout'] = array(
				'title' => __('Layout', 'geodirectory'),
				'desc' => __('How the listings should laid out by default.', 'geodirectory'),
				'type' => 'select',
				'options'   =>  geodir_get_layout_options(),
				'default'  => '0',
				'desc_tip' => true,
				'advanced' => false,
				'group'     => __("Design","geodirectory")
			);
			$arguments['post_limit'] = array(
				'title' => __('Posts to show:', 'geodirectory'),
				'desc' => __('The number of posts to show by default.', 'geodirectory'),
				'type' => 'number',
				'default'  => '5',
				'desc_tip' => true,
				'advanced' => false,
				'group'     => __("Design","geodirectory")

			);
			$arguments['cat_limit'] = array(
				'title' => __('Categories to show:', 'geodirectory'),
				'desc' => __('The number of categories to show by default.', 'geodirectory'),
				'type' => 'number',
				'default'  => '3',
				'desc_tip' => true,
				'advanced' => false,
				'group'     => __("Design","geodirectory")
			);
			$arguments['add_location_filter'] = array(
				'title' => __("Enable location filter?", 'geodirectory'),
				'type' => 'checkbox',
				'desc_tip' => true,
				'value'  => '1',
				'default'  => '1',
				'advanced' => true
			);
			$arguments['use_viewing_post_type'] = array(
				'title' => __("Use current viewing post type?", 'geodirectory'),
				'type' => 'checkbox',
				'desc_tip' => true,
				'value'  => '1',
				'default'  => '1',
				'advanced' => true
			);




	    if($design_style) {

		    // background
		    $arguments['bg']  = geodir_get_sd_background_input('mt');

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


		    // card design
		    $arguments['row_gap'] = array(
			    'title' => __( "Card row gap", 'geodirectory' ),
			    'desc' => __('This adjusts the spacing between the cards horizontally.','geodirectory'),
			    'type' => 'select',
			    'options' =>  array(
				    ''  =>  __("Default","geodirectory"),
				    '1'  =>  '1',
				    '2'  =>  '2',
				    '3'  =>  '3',
				    '4'  =>  '4',
				    '5'  =>  '5',
			    ),
			    'default'  => '',
			    'desc_tip' => false,
			    'advanced' => false,
			    'group'     => __("Card Design","geodirectory")
		    );

		    $arguments['column_gap'] = array(
			    'title' => __( "Card column gap", 'geodirectory' ),
			    'desc' => __('This adjusts the spacing between the cards vertically.','geodirectory'),
			    'type' => 'select',
			    'options' =>  array(
				    ''  =>  __("Default","geodirectory"),
				    '1'  =>  '1',
				    '2'  =>  '2',
				    '3'  =>  '3',
				    '4'  =>  '4',
				    '5'  =>  '5',
			    ),
			    'default'  => '',
			    'desc_tip' => false,
			    'advanced' => false,
			    'group'     => __("Card Design","geodirectory")
		    );

		    $arguments['card_border'] = array(
			    'title' => __( "Card border", 'geodirectory' ),
			    'desc' => __('Set the border style for the card.','geodirectory'),
			    'type' => 'select',
			    'options' =>  array(
				                  ''  =>  __("Default","geodirectory"),
				                  'none'  =>  __("None","geodirectory"),
			                  ) + geodir_aui_colors(),
			    'default'  => '',
			    'desc_tip' => false,
			    'advanced' => false,
			    'group'     => __("Card Design","geodirectory")
		    );

		    $arguments['card_shadow'] = array(
			    'title' => __( "Card shadow", 'geodirectory' ),
			    'desc' => __('Set the card shadow style.','geodirectory'),
			    'type' => 'select',
			    'options' =>  array(
				    ''  =>  __("None","geodirectory"),
				    'small'  =>  __("Small","geodirectory"),
				    'medium'  =>  __("Medium","geodirectory"),
				    'large'  =>  __("Large","geodirectory"),
			    ),
			    'default'  => '',
			    'desc_tip' => false,
			    'advanced' => false,
			    'group'     => __("Card Design","geodirectory")
		    );
	    }
		return $arguments;
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
		$this->w_settings = $args;
		ob_start();
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

	    // wrap class
	    $wrap_class = geodir_build_aui_class($instance);

        if(!defined( 'DOING_AJAX' ) || isset($_REQUEST['shortcode']) )  echo '<div class="geodir_bestof_widget bestof-widget-tab-layout ' . $tab_layout . ' '.$wrap_class .'">';

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
         * Filter the event type.
         *
         * @since 1.5.4
         *
         * @param string $instance ['event_type'] The event type.
         */
        $event_type = empty($instance['event_type']) ? 'show-desc' : apply_filters('bestof_widget_event_type', $instance['event_type']);


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

	    $design_style = geodir_design_style();

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

	    $a_terms = $terms;


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
	    $tabs_left = false;
	    if(!empty($instance['tab_layout']) && $instance['tab_layout']=='left'){
		    $nav_html .= '<div class="row"><div class="col-3"> ';
		    $tabs_left = true;
	    }

        //term navigation - start
	    if(!defined( 'DOING_AJAX' ) || isset($_REQUEST['shortcode']) ) $nav_html .= '<div class="geodir-tabs gd-bestof-tabs" id="gd-bestof-tabs" style="position:relative;">';



        foreach ($layout as $tab_layout) {

            $is_dropdown = ($tab_layout == 'bestof-tabs-as-dropdown') ? true : false;

            if ($is_dropdown) {
	            if($design_style){
		            $nav_html .= '<select id="geodir_bestof_tab_dd" class="geodir-select form-control mb-3 mw-100" name="geodir_bestof_tab_dd" data-placeholder="' . esc_attr(__('Select Category', 'geodirectory')) . '">';
	            }else{
		            $nav_html .= '<select id="geodir_bestof_tab_dd" class="geodir-select" name="geodir_bestof_tab_dd" data-placeholder="' . esc_attr(__('Select Category', 'geodirectory')) . '">';
	            }
            } else {
	            $tabs_class = $tabs_left ? 'flex-column nav-pills' : 'nav-tabs';
	            if ( ! empty( $instance['tab_head_align'] ) && ! empty( $instance['tab_layout'] ) && $instance['tab_layout']=='top' && $instance['tab_head_align']=='center' ) {
		            $tabs_class .= ' justify-content-center';
	            }elseif ( ! empty( $instance['tab_head_align'] ) && ! empty( $instance['tab_layout'] ) && $instance['tab_layout']=='top' && $instance['tab_head_align']=='right' ) {
		            $tabs_class .= ' justify-content-end';
	            }
                $nav_html .= $design_style ? '<ul class="geodir-tab-head geodir-bestof-cat-list m-0 mb-3 nav '.$tabs_class.'">' :  '<ul class="geodir-tab-head geodir-bestof-cat-list">';
            }


            $term_icons = geodir_get_term_icon();
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
                            $nav_html .= $design_style ? '<li class="nav-item">' : '<li class="geodir-tab-active">';
                        } else {
                            $nav_html .= $design_style ? '<li class="nav-item">' : '<li class="">';
                        }


	                    $term_link = get_term_link($cat, $cat->taxonomy);
	                    $term_icon_url = !empty($term_icons) && isset($term_icons[$cat->term_id]) ? $term_icons[$cat->term_id] : '';
	                    $term_icon_url = $term_icon_url != '' ? '<img alt="' . esc_attr($cat->name) . ' icon" src="' . $term_icon_url . '" /> ' : '';
	                    $cat_font_icon = get_term_meta( $cat->term_id, 'ct_cat_font_icon', true );
	                    $cat_color = get_term_meta( $cat->term_id, 'ct_cat_color', true );
	                    $cat_color = $cat_color ? $cat_color : '#ababab';

	                    $term_icon = $cat_font_icon ? '<i class="'.$cat_font_icon.' fa-fw" aria-hidden="true" aria-hidden="true"></i>' : $term_icon_url;

	                    if($design_style){
		                    $active = $cat_count == 1 ? 'active' : '';
		                    $nav_html .= '<a class="nav-link '.$active.'" data-termid="' . $cat->term_id . '" href="' . esc_url($term_link) . '" data-toggle="pill">';
		                    $nav_html .= "<span class='gd-cptcat-icon' style='color: $cat_color' >$term_icon</span> ";
		                    $nav_html .=  esc_attr($cat->name);
		                    $nav_html .= '</a>';

	                    }else{
		                    $nav_html .= '<span class="gd-cptcat-cat-left" style="background: '.$cat_color.';"><a data-termid="' . $cat->term_id . '" href="' . esc_url($term_link) . '" title="' . esc_attr($cat->name) . '">';
		                    $nav_html .= "<span class='gd-cptcat-icon' >$term_icon</span>";
		                    $nav_html .= '</a></span>';
		                    $nav_html .= '<span class="gd-cptcat-cat-right"><a data-termid="' . $cat->term_id . '" href="' . esc_url($term_link) . '" title="' . esc_attr($cat->name) . '">';
		                    $nav_html .= $cat->name;
		                    $nav_html .= '<small>';
		                    $nav_html .= '</small>';
		                    $nav_html .= '</a></span>';

	                    }

	                    $nav_html .= '</li>';

                    }
                }
            }

            if ($is_dropdown) {
                $nav_html .= '</select>';
            } else {
                $nav_html .= '</ul>';
            }
            //$final_html .= $nav_html;
        }

	    if(!empty($instance['tab_layout']) && $instance['tab_layout']=='left'){
		    $nav_html .= '</div>';
	    }

	    if(!defined( 'DOING_AJAX' ) || isset($_REQUEST['shortcode']) ) $nav_html .= '</div>';
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

	    if(!defined( 'DOING_AJAX' ) || isset($_REQUEST['shortcode']) ) {
		    ?>
            <input type="hidden" id="bestof_widget_post_type" name="bestof_widget_post_type"
                   value="<?php echo $post_type; ?>">
            <input type="hidden" id="bestof_widget_excerpt_type" name="bestof_widget_excerpt_type"
                   value="<?php echo $excerpt_type; ?>">	   
			<input type="hidden" id="bestof_widget_event_type" name="bestof_widget_event_type"
                   value="<?php echo $event_type; ?>">
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
			    if(!defined( 'DOING_AJAX' ) || isset($_REQUEST['shortcode']) ) echo $nav_html;
		    }
		    ?>

		    <?php



		    if(!empty($instance['tab_layout']) && $instance['tab_layout']=='left'){
			    echo '<div class="col-9"> ';
		    }

		    echo '<div id="geodir-bestof-places">';
	    }


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

	            if($design_style){
		            echo '<h4 class="bestof-cat-title h4  pb-3 p-0 m-0 w-100">' . wp_sprintf(__('Best of %s', 'geodirectory'), $first_term->name) . '</h4>';
	            }else{
		            echo '<h4 class="bestof-cat-title">' . wp_sprintf(__('Best of %s', 'geodirectory'), $first_term->name) . '<a href="' . esc_url($view_all_link) . '">' . __("View all", 'geodirectory') . '</a></h4>';
	            }
            }
            if ($excerpt_type == 'show-reviews') {
                add_filter('get_the_excerpt', array(__CLASS__,'best_of_show_review_in_excerpt'));
            }
			/*
			 * Filter widget listings query args.
			 */
			$query_args = apply_filters( 'geodir_widget_listings_query_args', $query_args, $instance );
            self::bestof_places_by_term($query_args,$instance);
            if ($excerpt_type == 'show-reviews') {
                remove_filter('get_the_excerpt', array(__CLASS__,'best_of_show_review_in_excerpt'));
            }


	    if ($design_style && $terms) {
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

		    echo '<div class="text-center"><a class="btn btn-outline-primary" href="' . esc_url($view_all_link) . '">' . __("View all", 'geodirectory') . '</a></div>';

	    }



	    if(!defined( 'DOING_AJAX' ) || isset($_REQUEST['shortcode']) ) {
		    echo "</div>";

		    // loading class
		    if ( $design_style ) {
			    echo '<div class="text-center"><p id="geodir-bestof-loading" class="geodir-bestof-loading spinner-border" style="display: none;" role="status"><span class="sr-only">'.__("Loading...","geodirectory").'</span></p></div>';
		    }else{
			    echo '<p id="geodir-bestof-loading" class="geodir-bestof-loading"><i class="fas fa-cog fa-spin" aria-hidden="true"></i></p>';
		    }
		    ?>


		    </div>
		    <?php //first term listings by default - end
		    ?>
		    <?php
		    echo "</div>";
	    }

	    if(!empty($instance['tab_layout']) && $instance['tab_layout']=='left'){
		    echo '</div> ';
	    }
    }

	/**
	 * Function for show best review in excerpt.
	 *
	 * @since 2.0.0
	 *
	 * @param string $excerpt Best review excerpt value.
	 * @return string $excerpt.
	 */
	public static function best_of_show_review_in_excerpt($excerpt) {
		global $wpdb, $post;

		$query = $wpdb->prepare( "SELECT cmt.comment_content FROM " . GEODIR_REVIEW_TABLE . " AS r INNER JOIN {$wpdb->comments} AS cmt ON cmt.comment_ID = r.comment_id WHERE cmt.comment_post_ID = %d ORDER BY cmt.comment_date DESC, cmt.comment_id DESC", array( $post->ID ) );
		$review = $wpdb->get_row( $query );

		if ( ! empty( $review ) ) {
			$excerpt = strip_tags( $review->comment_content );
		}

		return $excerpt;
	}

	/**
	 * Display the best of widget listings using the given query args.
	 *
	 * @since 1.3.9
	 *
	 * @global object $post The current post object.
	 *
	 * @param array $query_args The query array.
	 */
	public static function bestof_places_by_term($query_args,$instance = array()) {
		/**
		 * This action called before querying widget listings.
		 *
		 * @since 1.0.0
		 */
		do_action('geodir_bestof_get_widget_listings_before');

		$widget_listings = geodir_get_widget_listings($query_args);
		/**
		 * This action called after querying widget listings.
		 *
		 * @since 1.0.0
		 */
		do_action('geodir_bestof_get_widget_listings_after');

		$character_count = isset($query_args['excerpt_length']) ? $query_args['excerpt_length'] : '';

		if (!isset($character_count)) {
			/** This filter is documented in geodirectory-widgets/geodirectory_bestof_widget.php */
			$character_count = $character_count == '' ? 50 : apply_filters('bestof_widget_character_count', $character_count);
		}

		// card border class
		$card_border_class = '';
		if(!empty($instance['card_border'])){
			if($instance['card_border']=='none'){
				$card_border_class = 'border-0';
			}else{
				$card_border_class = 'border-'.sanitize_html_class($instance['card_border']);
			}
		}

		// card shadow
		$card_shadow_class = '';
		if(!empty($instance['card_shadow'])){
			if($instance['card_shadow']=='small'){
				$card_shadow_class = 'shadow-sm';
			}elseif($instance['card_shadow']=='medium'){
				$card_shadow_class = 'shadow';
			}elseif($instance['card_shadow']=='large'){
				$card_shadow_class = 'shadow-lg';
			}
		}


		global $post, $geodir_is_widget_listing, $gd_layout_class;
		$current_post = $post;

		$geodir_is_widget_listing = true;
		$layout = isset($instance['layout']) ? $instance['layout'] : '';
		$gd_layout_class = geodir_convert_listing_view_class( $layout );

		$design_style = geodir_design_style();
		$template = $design_style ? $design_style."/content-widget-listing.php" : "content-widget-listing.php";

//		echo geodir_get_template_html( $template, array(
//			'widget_listings' => $widget_listings
//		) );

		echo geodir_get_template_html( $template, array(
			'widget_listings' => $widget_listings,
			'column_gap_class'   => $instance['column_gap'] ? 'mb-'.absint($instance['column_gap']) : 'mb-4',
			'row_gap_class'   => $instance['row_gap'] ? 'px-'.absint($instance['row_gap']) : '',
			'card_border_class'   => $card_border_class,
			'card_shadow_class'  =>  $card_shadow_class,
		) );

		//geodir_get_template( 'content-widget-listing.php', array( 'widget_listings' => $widget_listings ) );

		$geodir_is_widget_listing = false;

		$GLOBALS['post'] = $current_post;
		if (!empty($current_post)) {
			setup_postdata($current_post);
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
	        document.addEventListener("DOMContentLoaded", function(event) {
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

                    var activeTab = jQuery(this).closest('ul').find('li.geodir-tab-active');
                    activeTab.removeClass('geodir-tab-active');
                    jQuery(this).parent().parent().addClass('geodir-tab-active');

                    var term_id = 0;
                    if (e.type === "change") {
                        term_id = jQuery(this).val();
                    } else if (e.type === "click" && jQuery(this).attr('data-termid')!='undefined') {
                        term_id = jQuery(this).attr('data-termid');
                    }

	                if(!term_id ){
		                return;
	                }
	                
                    var post_type = jQuery(widgetBox).find('#bestof_widget_post_type').val();
                    var excerpt_type = jQuery(widgetBox).find('#bestof_widget_excerpt_type').val();
                    var post_limit = jQuery(widgetBox).find('#bestof_widget_post_limit').val();
                    var taxonomy = jQuery(widgetBox).find('#bestof_widget_taxonomy').val();
                    var char_count = jQuery(widgetBox).find('#bestof_widget_char_count').val();
                    var add_location_filter = jQuery(widgetBox).find('#bestof_widget_location_filter').val();

	                var data = <?php echo json_encode( $this->w_settings ); ?>;
	                data['action'] = 'geodir_bestof';
	                data['security'] = geodir_params.basic_nonce;
	                data['post_type'] = post_type;
	                data['excerpt_type'] = excerpt_type;
	                data['taxonomy'] = taxonomy;
	                data['term_id'] = term_id;
//	                data['action'] = 'geodir_bestof';
//	                data['action'] = 'geodir_bestof';
//	                data['action'] = 'geodir_bestof';
//	                data['action'] = 'geodir_bestof';
//                    var data = {
//                        'action': 'geodir_bestof',
//                        'security': geodir_params.basic_nonce,
//                        'post_type': post_type,
//                        'excerpt_type': excerpt_type,
//                        'post_limit': post_limit,
//                        'taxonomy': taxonomy,
//                        'geodir_ajax': true,
//                        'term_id': term_id,
//                        'char_count': char_count,
//                        'add_location_filter': add_location_filter
//                    };

                    container.hide();
                    loading.show();

                    console.log(data );
                    jQuery.post(geodir_params.ajax_url, data, function (response) {
                        container.html(response);
                        jQuery(widgetBox).find('.geodir_category_list_view li .geodir-post-img .geodir_thumbnail img').css('display', 'block');
	                    geodir_init_lazy_load();
                    });
                })
            });
	        document.addEventListener("DOMContentLoaded", function(event) {
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