<?php
/**
 * GeoDirectory cpt categories widget.
 *
 * @package GeoDirectory
 * @since 1.5.3
 */

/**
 * GeoDirectory CPT categories widget class.
 *
 * @since 1.5.3
 */
class geodir_cpt_categories_widget extends WP_Widget {
    
	/**
	 * Register the cpt categories with WordPress.
	 *
     * @since 1.5.3
	 */
    public function __construct() {
		$widget_ops = array('classname' => 'geodir_cpt_categories_widget', 'description' => __('A list of GeoDirectory CPT categories.', 'geodirectory'));
		parent::__construct('geodir_cpt_categories_widget', __('GD > CPT Categories', 'geodirectory'), $widget_ops);
	}

	/**
	 * Front-end display content for cpt categories widget.
	 *
	 * @since 1.5.3
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget($args, $instance) {
        $params = array();
		/**
		 * Filter the widget title.
		 *
		 * @since 1.5.3
		 *
		 * @param string $title The widget title. Default empty.
		 * @param array  $instance An array of the widget's settings.
		 * @param mixed  $id_base The widget ID.
		 */
		$params['title'] = apply_filters('geodir_cpt_categories_widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $this->id_base);
		
		/**
		 * Filter the widget setting post type.
		 *
		 * @since 1.5.3
		 *
		 * @param array $post_type The post types to display categories.
		 * @param array $instance An array of the widget's settings.
		 * @param mixed $id_base The widget ID.
		 */
		$params['post_type'] = apply_filters('geodir_cpt_categories_widget_post_type', empty($instance['post_type']) ? array() : $instance['post_type'], $instance, $this->id_base);
		
		/**
		 * Filter the widget setting to hide empty categories.
		 *
		 * @since 1.5.3
		 *
		 * @param bool  $hide_empty If true then empty category will be not displayed.
		 * @param array $instance An array of the widget's settings.
		 * @param mixed $id_base The widget ID.
		 */
		$params['hide_empty'] = apply_filters('geodir_cpt_categories_widget_hide_empty', empty($instance['hide_empty']) ? 0 : 1, $instance, $this->id_base);
		
		/**
		 * Filter the widget setting to show/hide category count.
		 *
		 * @since 1.5.3
		 *
		 * @param bool  $show_count If true then category count will be displayed.
		 * @param array $instance An array of the widget's settings.
		 * @param mixed $id_base The widget ID.
		 */
		$params['show_count'] = apply_filters('geodir_cpt_categories_widget_show_count', empty($instance['show_count']) ? 0 : 1, $instance, $this->id_base);
		
		/**
		 * Filter the widget setting to show/hide category icon.
		 *
		 * @since 1.5.3
		 *
		 * @param bool  $hide_icon If true then category icon will be not displayed.
		 * @param array $instance An array of the widget's settings.
		 * @param mixed $id_base The widget ID.
		 */
		$params['hide_icon'] = apply_filters('geodir_cpt_categories_widget_hide_icon', empty($instance['hide_icon']) ? 0 : 1, $instance, $this->id_base);
		
		/**
		 * Filter the widget max no of sub-categories count.
		 *
		 * @since 1.5.3
		 *
		 * @param bool|string $max_count Widget max no of sub-categories count.
		 * @param array       $instance An array of the widget's settings.
		 * @param mixed       $id_base The widget ID.
		 */
		$params['max_count'] = apply_filters('geodir_cpt_categories_widget_max_count', !isset($instance['max_count']) ? 'all' : strip_tags($instance['max_count']), $instance, $this->id_base);
		
		/**
		 * Filter the widget parameters.
		 *
		 * @since 1.5.3
		 *
		 * @param array $params The widget parameters.
		 * @param array $instance An array of the widget's settings.
		 * @param mixed $id_base The widget ID.
		 */
		$params = apply_filters('geodir_cpt_categories_widget_params', $params, $instance, $this->id_base);
		
		$output = geodir_cpt_categories_output($params);
		
		echo $args['before_widget'];
		if ( $params['title'] ) {
            echo '<div class="geodir_list_heading clearfix">';
			echo $args['before_title'] . $params['title'] . $args['after_title'];
            echo '</div>';
		}
		echo '<div class="gd-cptcats-widget">';		
		echo $output;
		echo '</div>';
		echo $args['after_widget'];
    }

	/**
	 * Sanitize cpt categories widget values as they are saved.
	 *
	 * @since 1.5.3
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update($new_instance, $old_instance) {
        $new_instance['post_type'] = is_array($new_instance['post_type']) && in_array('0', $new_instance['post_type']) ? array('0') : $new_instance['post_type'];
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['post_type'] = isset($new_instance['post_type']) ? $new_instance['post_type'] : array('0');
		$instance['hide_empty'] = !empty($new_instance['hide_empty']) ? 1 : 0;
		$instance['show_count'] = !empty($new_instance['show_count']) ? 1 : 0;
		$instance['hide_icon'] = !empty($new_instance['hide_icon']) ? 1 : 0;
		$instance['max_count'] = strip_tags($new_instance['max_count']);

		return $instance;
    }

	/**
	 * Back-end cpt categories settings form.
	 *
	 * @since 1.5.3
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form($instance) {
        $instance = wp_parse_args( (array)$instance, 
									array( 
										'title' => '',
										'post_type' => array(), // NULL for all
										'hide_empty' => '',
										'show_count' => '',
										'hide_icon' => '',
										'max_count' => 'all'
									) 
								);

		$title = strip_tags($instance['title']);
		$post_type = $instance['post_type'];
		$hide_empty = !empty($instance['hide_empty']) ? true : false;
		$show_count = !empty($instance['show_count']) ? true : false;
		$hide_icon = !empty($instance['hide_icon']) ? true : false;
		$max_count = strip_tags($instance['max_count']);
		
		$post_type_options = geodir_get_posttypes('options');
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'geodirectory'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
		<p>
			<label for="<?php echo $this->get_field_id('post_type'); ?>"><?php _e('Select CPT:', 'geodirectory'); ?></label> 
			<select name="<?php echo $this->get_field_name('post_type'); ?>[]" id="<?php echo $this->get_field_id('post_type'); ?>" class="widefat" multiple="multiple">
				<option value="0" <?php selected( (empty($post_type) || (is_array($post_type) && in_array('0', $post_type))), true ); ?>><?php _e('All', 'geodirectory'); ?></option>
				<?php foreach ($post_type_options as $name => $title) { ?>
				<option value="<?php echo $name;?>" <?php selected( is_array($post_type) && in_array($name, $post_type), true ); ?>><?php echo $title; ?></option>
				<?php } ?>
			</select>
		</p>
		<p><input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('hide_empty'); ?>" name="<?php echo $this->get_field_name('hide_empty'); ?>"<?php checked( $hide_empty ); ?> value="1" />
		<label for="<?php echo $this->get_field_id('hide_empty'); ?>"><?php _e( 'Hide empty categories', 'geodirectory' ); ?></label><br />
		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_count'); ?>" name="<?php echo $this->get_field_name('show_count'); ?>"<?php checked( $show_count ); ?> value="1" />
		<label for="<?php echo $this->get_field_id('show_count'); ?>"><?php _e( 'Show category count' ); ?></label> <small><?php _e( '( Enabling will slow down page loading for big directories. )', 'geodirectory' ); ?></small><br />
		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('hide_icon'); ?>" name="<?php echo $this->get_field_name('hide_icon'); ?>"<?php checked( $hide_icon ); ?> value="1" />
		<label for="<?php echo $this->get_field_id('hide_icon'); ?>"><?php _e( 'Hide category icon', 'geodirectory' ); ?></label></p>
		<p>
			<label for="<?php echo $this->get_field_id('max_count'); ?>"><?php _e('Max no of sub-categories:', 'geodirectory'); ?></label> 
			<select name="<?php echo $this->get_field_name('max_count'); ?>" id="<?php echo $this->get_field_id('max_count'); ?>" class="widefat">
				<option value="all" <?php selected( $max_count, 'all' ); ?>><?php _e('All', 'geodirectory'); ?></option>
				<?php for ($n = 10; $n >= 0; $n--) { ?>
				<option value="<?php echo $n;?>" <?php selected( $max_count, $n ); ?>><?php echo $n; ?></option>
				<?php } ?>
			</select>
		</p>
		<?php
    }
} // class geodir_cpt_categories_widget

register_widget('geodir_cpt_categories_widget');

/**
 * Get the cpt categories content.
 *
 * @since 1.5.3
 *
 * @param array $params An array of cpt categories parameters.
 * @return string CPT categories content.
 */
function geodir_cpt_categories_output($params) {
	$args = wp_parse_args((array)$params, 
							array( 
								'title' => '',
								'post_type' => array(), // NULL for all
								'hide_empty' => '',
								'show_count' => '',
								'hide_icon' => '',
								'max_count' => 'all'
							) 
						);
						
	$gd_post_types = geodir_get_posttypes('array');
	
	$post_type_arr = !is_array($args['post_type']) ? explode(',', $args['post_type']) : $args['post_type'];
	$current_posttype = geodir_get_current_posttype();
	
	$is_listing = false;
	$is_category = false;
	if (geodir_is_page('listing')) {
		$current_posttype = geodir_get_current_posttype();
		
		if ($current_posttype != '' && isset($gd_post_types[$current_posttype])) {
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
	
	$parent_category = 0;
	if ($is_listing) {
		$post_type_arr = array($current_posttype);
		
		if ($is_category) {
			$parent_category = $current_term_id;
		}
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
	$all_childs = $max_count == 'all' ? true : false;
	$max_count = $max_count > 0 ? (int)$max_count : 0;
	$show_count = !empty($args['show_count']) ? true : false;
	$hide_icon = !empty($args['hide_icon']) ? true : false;
	
	$output = '';
	if (!empty($post_types)) {
		foreach ($post_types as $cpt => $cpt_info) {
			$cat_taxonomy = $cpt . 'category';
			$categories = get_terms($cat_taxonomy, array('orderby' => 'count', 'order' => 'DESC', 'hide_empty' => $hide_empty, 'parent' => $parent_category));
			if ($hide_empty) {
				$categories = geodir_filter_empty_terms($categories);
			}
			$categories = geodir_sort_terms($categories, 'count');
			
			if (!empty($categories)) {			
				$cpt_image_url = get_option('geodir_cpt_img_' . $cpt);
				$cpt_image = $cpt_image_url != '' ? ' style="background-image:url(\'' . $cpt_image_url . '\')"' : '';
				$term_icons = !$hide_icon ? geodir_get_term_icon() : array();
				
				$cpt_row = '<div class="gd-cptcat-row gd-cptcat-' . $cpt . '">';
				if ($is_category) {
					$term_info = get_term($current_term_id, $cat_taxonomy);
					
					$term_icon_url = !empty($term_icons) && isset($term_icons[$term_info->term_id]) ? $term_icons[$term_info->term_id] : '';
					$term_icon_url = $term_icon_url != '' ? '<img alt="' . esc_attr($term_info->name) . ' icon" src="' . $term_icon_url . '" /> ' : '';
					
					$count = $show_count ? ' <span class="gd-cptcat-count">(' . $term_info->count . ')</span>' : '';
					$cpt_row .= '<h2 class="gd-cptcat-title">' . $term_icon_url . $term_info->name . $count . '</h2>';
				} else {
					$cpt_row .= '<h2 class="gd-cptcat-title"' . $cpt_image .'>' . __($cpt_info['labels']['name'], 'geodirectory') . '</h2>';
				}
				$cpt_row .= '<ul class="gd-cptcat-ul gd-cptcat-cat-1">';
				foreach ($categories as $category) {
					$term_icon_url = !empty($term_icons) && isset($term_icons[$category->term_id]) ? $term_icons[$category->term_id] : '';
					$term_icon_url = $term_icon_url != '' ? '<img alt="' . esc_attr($category->name) . ' icon" src="' . $term_icon_url . '" /> ' : '';
					
					$term_link = get_term_link( $category, $category->taxonomy );
					/** Filter documented in geodirectory-functions/general_functions.php **/
					$term_link = apply_filters( 'geodir_category_term_link', $term_link, $category->term_id, $cpt );
		
					$cpt_row .= '<li class="gd-cptcat-li">';
					$count = $show_count ? ' <span class="gd-cptcat-count">(' . $category->count . ')</span>' : '';
					$cpt_row .= '<h3 class="gd-cptcat-cat"><a href="' . esc_url($term_link) . '" title="' . esc_attr($category->name) . '">'  .$term_icon_url . $category->name . $count . '</a></h3>';
					if ($all_childs || $max_count > 0) {
						$cpt_row .= geodir_cpt_categories_child_cats($category->term_id, $cpt, $hide_empty, $show_count, $max_count, $term_icons);
					}
					$cpt_row .= '</li>';
				}
				$cpt_row .= '</ul></div>';
				
				$output .= $cpt_row;
			}
		}
	}
	return $output;
}

/**
 * Get the child categories content.
 *
 * @since 1.5.3
 *
 * @param int $parent_id Parent category id.
 * @param string $cpt The post type.
 * @param bool $hide_empty If true then filter the empty categories.
 * @param bool $show_count If true then category count will be displayed.
 * @param bool|string $max_count Max no of sub-categories count to display.
 * @param array $term_icons Array of terms icons url.
 * @param int $depth Category depth level. Default 1.
 * @return string Html content.
 */
function geodir_cpt_categories_child_cats($parent_id, $cpt, $hide_empty, $show_count, $max_count, $term_icons, $depth = 1) {
	$cat_taxonomy = $cpt . 'category';
	
	$child_cats = get_terms($cat_taxonomy, array('orderby' => 'count', 'order' => 'DESC', 'hide_empty' => $hide_empty, 'parent' => $parent_id, 'number' => $max_count));
	if ($hide_empty) {
        $child_cats = geodir_filter_empty_terms($child_cats);
	}
	$child_cats = geodir_sort_terms($child_cats, 'count');
	if (empty($child_cats)) {
		return '';
	}
	
	$depth++;
	$content = '<li class="gd-cptcat-li gd-cptcat-li-sub"><ul class="gd-cptcat-ul gd-cptcat-sub gd-cptcat-cat-' . $depth . '">';
	foreach ($child_cats as $category) {
		$term_icon_url = !empty($term_icons) && isset($term_icons[$category->term_id]) ? $term_icons[$category->term_id] : '';
		$term_icon_url = $term_icon_url != '' ? '<img alt="' . esc_attr($category->name) . ' icon" src="' . $term_icon_url . '" /> ' : '';
		$term_link = get_term_link( $category, $category->taxonomy );
		/** Filter documented in geodirectory-functions/general_functions.php **/
		$term_link = apply_filters( 'geodir_category_term_link', $term_link, $category->term_id, $cpt );
		$count = $show_count ? ' <span class="gd-cptcat-count">(' . $category->count . ')</span>' : '';
					
		$content .= '<li class="gd-cptcat-li gd-cptcat-li-sub">';
		$content .= '<a href="' . esc_url($term_link) . '" title="' . esc_attr($category->name) . '">' . $term_icon_url . $category->name . $count . '</a></li>';
		$content .= geodir_cpt_categories_child_cats($category->term_id, $cpt, $hide_empty, $show_count, $max_count, $term_icons, $depth);
	}
	$content .= '</li></ul>';
			
	return $content;
}

/**
 * Filter the terms with count empty.
 *
 * @since 1.5.3
 *
 * @param array $terms Terms array.
 * @return array Terms.
 */
function geodir_filter_empty_terms($terms) {
	if (empty($terms)) {
		return $terms;
	}
	
	$return = array();
	foreach ($terms as $term) {
		if (isset($term->count) && $term->count > 0) {
			$return[] = $term;
		}
	}	
	return $return;
}
?>