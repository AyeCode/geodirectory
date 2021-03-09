<?php
/**
 * Template functions
 *
 * @since 1.0.0
 * @package GeoDirectory
 */

/**
 * get_templates_dir function.
 *
 * The function is return templates dir path.
 *
 * @since 2.0.0
 *
 * @return string Templates dir path.
 */
function geodir_get_templates_dir() {
	return GEODIRECTORY_PLUGIN_DIR . 'templates';
}

/**
 * get_templates_url function.
 *
 * The function is return templates dir url.
 *
 * @since 2.0.0
 *
 * @return string Templates dir url.
 */
function geodir_get_templates_url() {
	return GEODIRECTORY_PLUGIN_URL . '/templates';
}

/**
 * get_theme_template_dir_name function.
 *
 * The function is return theme template dir name.
 *
 * @since 2.0.0
 *
 * @return string Theme template dir name.
 */
function geodir_get_theme_template_dir_name() {
	return untrailingslashit( apply_filters( 'geodir_templates_dir', 'geodirectory' ) );
}

/**
 * get_template_part function.
 *
 * The function is use for load templates files.
 *
 * @since 2.0.0
 *
 * @param string $slug Template slug.
 * @param string $name Optional. Template name. Default null.
 *
 * @return string
 */
function geodir_get_template_part( $slug, $name = '' ) {
	$load_template = apply_filters( 'geodir_allow_template_part_' . $slug . '_' . $name, true );
	if ( false === $load_template ) {
		return '';
	}

	$template = '';

	if ( $name ) {
		// Look in yourtheme/slug-name.php and yourtheme/geodirectory/slug-name.php
		$template = locate_template( array(
			"{$slug}-{$name}.php",
			geodir_get_theme_template_dir_name() . "/{$slug}-{$name}.php"
		) );
	} else {
		// Look in yourtheme/slug-name.php and yourtheme/geodirectory/slug.php
		$template = locate_template( array( "{$slug}.php", geodir_get_theme_template_dir_name() . "/{$slug}.php" ) );
	}

	// Get default slug-name.php
	if ( ! $template ) {
		if ( $name && file_exists( geodir_get_templates_dir() . "/{$slug}-{$name}.php" ) ) {
			$template = geodir_get_templates_dir() . "/{$slug}-{$name}.php";
		} else if ( ! $name && file_exists( geodir_get_templates_dir() . "/{$slug}.php" ) ) {
			$template = geodir_get_templates_dir() . "/{$slug}.php";
		}
	}

	// If template file doesn't exist, look in yourtheme/slug.php and yourtheme/geodirectory/slug.php
	if ( ! $template ) {
		$template = locate_template( array( "{$slug}.php", geodir_get_theme_template_dir_name() . "/{$slug}.php" ) );

		// If template file doesn't exist, look in /geodirectory/templates/slug.php
		if ( ! $template && file_exists( geodir_get_templates_dir() . "/{$slug}.php" ) ) {
			$template = geodir_get_templates_dir() . "/{$slug}.php";
		}
	}

	// Allow 3rd party plugins to filter template file from their plugin.
	$template = apply_filters( 'geodir_get_template_part', $template, $slug, $name );

	if ( $template ) {

		do_action( 'geodir_before_get_template_part', $template, $slug, $name);

		load_template( $template, false );

		do_action( 'geodir_after_get_template_part', $template, $slug, $name);
	}

	return '';
}

/**
 * The function is for include templates files.
 *
 * @since 2.0.0
 *
 * @param string $template_name Template name.
 * @param array $args Optional. Template arguments. Default array().
 * @param string $template_path Optional. Template path. Default null.
 * @param string $default_path Optional. Default path. Default null.
 */
function geodir_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	if ( ! empty( $args ) && is_array( $args ) ) {
		extract( $args );
	}

	$located = geodir_locate_template( $template_name, $template_path, $default_path );

	if ( ! file_exists( $located ) ) {
		geodir_doing_it_wrong( __FUNCTION__, sprintf( __( '%s does not exist.', 'geodirectory' ), '<code>' . $located . '</code>' ), '2.1' );

		return;
	}

	// Allow 3rd party plugin filter template file from their plugin.
	$located = apply_filters( 'geodir_get_template', $located, $template_name, $args, $template_path, $default_path );

	do_action( 'geodir_before_template_part', $template_name, $template_path, $located, $args );

	include( $located );

	do_action( 'geodir_after_template_part', $template_name, $template_path, $located, $args );
}

/**
 * The function is use to get template html.
 *
 * @since 2.0.0
 *
 * @param string $template_name Template name.
 * @param array $args Optional. Template arguments. Default array().
 * @param string $template_path Optional. Template dir path. Default null.
 * @param string $default_path Optional. Template default path. Default null.
 *
 * @return string Template html.
 *
 */
function geodir_get_template_html( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	ob_start();
	geodir_get_template( $template_name, $args, $template_path, $default_path );

	return ob_get_clean();
}

/**
 * The function is use for Retrieve the name of the highest
 * priority template file that exists.
 *
 * @since  2.0.0
 *
 * @param $template_name Template files to search for, in order.
 * @param string $template_path Optional. Template path. Default null.
 * @param string $default_path Optional. Default path. Default null.
 *
 * @return string Template path.
 */
function geodir_locate_template( $template_name, $template_path = '', $default_path = '' ) {
	if ( ! $template_path ) {
		$template_path = geodir_get_theme_template_dir_name();
	}

	if ( ! $default_path ) {
		$default_path = geodir_get_templates_dir();
	}

	// Look within passed path within the theme - this is priority.
	$template = locate_template(
		array(
			untrailingslashit( $template_path ) . '/' . $template_name,
			$template_name,
		)
	);

	// Get default template
	if ( ! $template ) {
		$template = untrailingslashit( $default_path ) . '/' . $template_name;
	}

	// Return what we found.
	return apply_filters( 'geodir_locate_template', $template, $template_name, $template_path );
}

/**
 * Content to display when no listings are found.
 *
 */
function geodir_no_listings_found() {
	$design_style = geodir_design_style();
	if($design_style){
		geodir_get_template( $design_style.'/loop/no-listings-found.php' );
	}else{
		geodir_get_template( 'loop/no-listings-found.php' );	
	}
}

/**
 * Content to display when no listings are found.
 *
 */
function geodir_loop_paging($args = array()) {

global $wp_query;

if ( $wp_query->max_num_pages <= 1 && empty($args['preview']) ) {
	return;
}

	$defaults = array(
		'prev_text' => sprintf(
			'%s <span class="nav-prev-text sr-only">%s</span>',
			'<i class="fas fa-chevron-left"></i>',
			__( 'Newer posts', 'ayetheme' )
		),
		'next_text' => sprintf(
			'<span class="nav-next-text sr-only">%s</span> %s',
			__( 'Older posts', 'ayetheme' ),
			'<i class="fas fa-chevron-right"></i>'
		),
	);

	$args = wp_parse_args( $args, $defaults );

	$gd_advanced_pagination = !empty($args['show_advanced']) ? esc_attr($args['show_advanced']) : ''; 
	$pagination_info = '';
	$design_style = geodir_design_style();
	if ($gd_advanced_pagination != '') {
		global $posts_per_page, $wpdb, $paged;

		$post_type = !empty($args['preview']) ? 'gd_place' : geodir_get_current_posttype();
		$listing_type_name =  geodir_get_post_type_plural_label($post_type);
		if (geodir_is_page('archive') || geodir_is_page('search')) {
			$term = array();

			if (is_tax()) {
				$term_id = get_queried_object_id();
				$taxonomy = get_query_var('taxonomy');

				if ($term_id && $post_type && get_query_var('taxonomy') == $post_type . 'category' ) {
					$term = get_term($term_id, $post_type . 'category');
				}
			}

			if (geodir_is_page('search') && !empty($_REQUEST['s' . $post_type . 'category'])) {
				$taxonomy_search = $_REQUEST['s' . $post_type . 'category'];

				if (!is_array($taxonomy_search)) {
					$term = get_term((int)$taxonomy_search, $post_type . 'category');
				} else if(is_array($taxonomy_search) && count($taxonomy_search) == 1) { // single category search
					$term = get_term((int)$taxonomy_search[0], $post_type . 'category');
				}
			}

			if (!empty($term) && !is_wp_error($term)) {
				$listing_type_name = $term->name;
			}
		}

		$numposts = !empty($args['preview']) ? 30 : $wp_query->found_posts;
		$max_page = ceil($numposts / $posts_per_page);
		if (empty($paged)) {
			$paged = 1;
		}
		$start_no = ( $paged - 1 ) * $posts_per_page + 1;
		$end_no = min($paged * $posts_per_page, $numposts);
		if ( $listing_type_name ) {
			$listing_type_name = __( $listing_type_name, 'geodirectory' );
			$pegination_desc   = wp_sprintf( __( 'Showing %s %d-%d of %d', 'geodirectory' ), $listing_type_name, $start_no, $end_no, $numposts );
		} else {
			$pegination_desc = wp_sprintf( __( 'Showing listings %d-%d of %d', 'geodirectory' ), $start_no, $end_no, $numposts );
		}
		$bs_class = $design_style ? 'text-muted pb-2' : '';
		$pagination_info = '<div class="gd-pagination-details '.$bs_class.'">' . $pegination_desc . '</div>';

		/**
		 * Adds an extra pagination info above/under pagination.
		 *
		 * @since 1.5.9
		 *
		 * @param string $pagination_info Extra pagination info content.
		 * @param string $listing_type_name Listing results type.
		 * @param string $start_no First result number.
		 * @param string $end_no Last result number.
		 * @param string $numposts Total number of listings.
		 * @param string $post_type The post type.
		 */
		$pagination_info = apply_filters('geodir_pagination_advance_info', $pagination_info, $listing_type_name, $start_no, $end_no, $numposts, $post_type);

	}

	if (function_exists('geodir_location_geo_home_link')) {
		remove_filter('home_url', 'geodir_location_geo_home_link', 100000);
	}


	if($gd_advanced_pagination=='before' && $pagination_info){
		$args['before_paging'] = $pagination_info;
	}elseif($gd_advanced_pagination=='after' && $pagination_info){
		$args['after_paging'] = $pagination_info;
	}

	// wrap class
	$wrap_class = geodir_build_aui_class($args);

	$template = $design_style ? $design_style."/loop/pagination.php" : "loop/pagination.php";
	geodir_get_template( $template, array(
		'args' => $args,
		'wrap_class'   => $wrap_class
	) );

	if (function_exists('geodir_location_geo_home_link')) {
		add_filter('home_url', 'geodir_location_geo_home_link', 100000, 2);
	}

}

/**
 * Display loop actions such as sort order and listing view type.
 */
function geodir_loop_actions($args = array()) {
	do_action( 'geodir_before_loop_actions' );
	$design_style = geodir_design_style();
	$template = $design_style ? $design_style."/loop/actions.php" : "loop/actions.php";

	// wrap class
	$wrap_class = geodir_build_aui_class($args);

	echo geodir_get_template_html( $template, array(
		'wrap_class' => $wrap_class,
	) );
	do_action( 'geodir_after_loop_actions' );
}

/**
 * Display message when no listing result found.
 *
 * @since 1.5.5
 * @package GeoDirectory
 *
 * @param string $template_listview Optional. Listing listview template. Ex: listing-listview, widget-listing-listview,
 * gdevents_widget_listview, link-business-listview. Default: 'listing-listview'.
 * @param bool $favorite Listing Optional. Are favorite listings results? Default: false.
 */
function geodir_display_message_not_found_on_listing( $template_listview = 'listing-listview', $favorite = false ) {
	if ( $favorite ) {
		$message = __( 'No favorite listings found which match your selection.', 'geodirectory' );
	} else {
		$message = __( 'No listings found which match your selection.', 'geodirectory' );
	}

	/**
	 * Filter the no listing found message.
	 *
	 * @since 1.5.5
	 *
	 * @param string $template_listview Listing listview template.
	 * @param bool $favorite Are favorite listings results?
	 */
	$message = apply_filters( 'geodir_message_listing_not_found', $message, $template_listview, $favorite );

	echo '<li class="no-listing">' . $message . '</li>';
}


/**
 * Get listing listview class for current column length.
 *
 * @since 1.5.7
 *
 * @param int $layout Listing view(1,2,3,4,5,gridview_onehalf,gridview_onethird,gridview_onefourth,gridview_onefifth). Default empty.
 *
 * @return string Listing listview class.
 */
function geodir_convert_listing_view_class( $columns = '' ) {
	$class = '';
	$style = geodir_design_style();
	switch ( $columns ) {
		case '1':
		case 'gridview_one':
		case 'row-cols-md-1':
			$class = geodir_grid_view_class(1);
			break;
		case '2':
		case 'gridview_onehalf':
		case 'row-cols-md-2':
		$class = geodir_grid_view_class(2);
			break;
		case '3':
		case 'gridview_onethird':
		case 'row-cols-md-3':
			$class = geodir_grid_view_class(3);
			break;
		case '4':
		case 'gridview_onefourth':
		case 'row-cols-md-4':
			$class = geodir_grid_view_class(4);
			break;
		case '5':
		case 'gridview_onefifth':
		case 'row-cols-md-5':
			$class = geodir_grid_view_class(5);
			break;
		case '0':
		case 'list':
		default:
			$class = geodir_grid_view_class(0);
			break;
	}

	return $class;
}


/**
 * The function is use for page title.
 *
 * Check if $echo true then echo page title else return page title.
 *
 * @since 2.0.0
 *
 * @param bool $echo Optional. Default true.
 *
 * @return string Page title.
 */
function geodir_page_title( $echo = true ) {
	if ( is_search() ) {
		$page_title = sprintf( __( 'Search results: &ldquo;%s&rdquo;', 'geodirectory' ), get_search_query() );

		if ( get_query_var( 'paged' ) ) {
			$page_title .= sprintf( __( '&nbsp;&ndash; Page %s', 'geodirectory' ), get_query_var( 'paged' ) );
		}

	} elseif ( is_tax() ) {
		$page_title = single_term_title( "", false );
	} else {
		$page_title = get_the_title();
	}

	$page_title = apply_filters( 'geodir_page_title', $page_title );

	if ( $echo ) {
		echo $page_title;
	} else {
		return $page_title;
	}
}

/**
 * The function is use for add listing classes in particular post.
 *
 * @since 2.0.0
 *
 * @param object $post Optional. Default null.
 * @param array $classes Optional. Default array.
 */
function geodir_listing_class( $post = null, $classes = array() ) {
	if ( ! is_array( $classes ) ) {
		$classes = array();
	}

	$classes[] = 'clearfix';

	if ( ! ( is_object( $post ) && ! empty( $post->ID ) ) ) {
		$post = get_post( $post );
	}

	if ( ! empty( $post ) ) {
		if ( ! empty( $post->post_type ) ) {
			$classes[] = 'gd-post-' . $post->post_type;
		}

		if ( ! empty( $post->featured ) ) {
			$classes[] = 'gd-post-featured';
		}
	}

	$classes = apply_filters( 'geodir_listing_classes', $classes, $post );

	if ( empty( $classes ) ) {
		return;
	}

	if ( is_scalar( $classes ) ) {
		echo ' class="' . esc_attr( $classes ) . '"';
	} elseif ( is_array( $classes ) ) {
		$classes = array_unique( $classes );

		echo ' class="' . esc_attr( implode( ' ', $classes ) ) . '"';
	}
}

/**
 * Display the attributes for the listing div.
 *
 * @since 2.0.0
 *
 * @param int|WP_Post $post_id Optional. Post ID or post object. Defaults to the global `$post`.
 */
function geodir_listing_attrs( $post = null, $attrs = array() ) {
	if ( ! is_array( $attrs ) ) {
		$attrs = array();
	}

	if ( ! ( is_object( $post ) && ! empty( $post->ID ) ) ) {
		$post = get_post( $post );
	}

	if ( ! empty( $post ) ) {
		$attrs['data-post-id'] = $post->ID;
	}

	$attrs = apply_filters( 'geodir_listing_attrs', $attrs, $post );

	if ( empty( $attrs ) ) {
		return;
	}

	if ( is_scalar( $attrs ) ) {
		echo esc_html( $attrs );
	} elseif ( is_array( $attrs ) ) {
		foreach ( $attrs as $key => $value ) {
			echo $key . '="' . esc_attr( $value ) . '" ';
		}
	}
}

/**
 * The function is use for add listing inner class.
 *
 * @since 2.0.0
 *
 * @param object $post Optional. Default null.
 * @param array $classes Optional. Default array.
 */
function geodir_listing_inner_class( $post = null, $classes = array() ) {
	if ( ! is_array( $classes ) ) {
		$classes = array();
	}

	$classes[] = 'geodir-category-listing';

	if ( ! ( is_object( $post ) && ! empty( $post->ID ) ) ) {
		$post = get_post( $post );
	}

	if ( ! empty( $post ) ) {
	}

	$classes = apply_filters( 'geodir_listing_inner_classes', $classes, $post );

	if ( empty( $classes ) ) {
		return;
	}

	if ( is_scalar( $classes ) ) {
		echo ' class="' . esc_attr( $classes ) . '"';
	} elseif ( is_array( $classes ) ) {
		$classes = array_unique( $classes );

		echo ' class="' . esc_attr( implode( ' ', $classes ) ) . '"';
	}
}

/**
 * listing_old_classes function.
 *
 * The function is add new class in listing old classes.
 *
 * @since 2.0.0
 *
 * @param array $classes Listing old classes.
 * @param object $post Post object.
 *
 * @return array $classes Listing old with new classes.
 */
function geodir_listing_old_classes( $classes, $post ) {
	global $grid_view_class;

	if ( $grid_view_class ) {
		$classes[] = 'geodir-gridview ' . $grid_view_class;
	} else {
		$classes[] = 'geodir-listview';
	}

	return $classes;
}

/**
 * listing_inner_old_classes function.
 *
 * The function is add new class in listing inner old classes.
 *
 * @since 2.0.0
 *
 * @param array $classes Listing inner old classes.
 * @param object $post post object.
 *
 * @return array $classes Listing inner add old with new classes.
 */
function geodir_listing_inner_old_classes( $classes, $post ) {
	/**
	 * Add a class to the `article` tag inside the `li` element on the listings list template.
	 *
	 * @since 1.0.0
	 *
	 * @param string $class The extra class for the `article` element, default empty.
	 */
	$post_view_article_class = apply_filters( 'geodir_post_view_article_extra_class', '' );

	if ( ! empty( $post_view_article_class ) ) {
		$classes[] = $post_view_article_class;
	}

	return $classes;
}

add_filter( 'geodir_listing_classes', 'geodir_listing_old_classes', 10, 2 );
add_filter( 'geodir_listing_inner_classes', 'geodir_listing_inner_old_classes', 10, 2 );

/**
 * Handle redirects before content is output - hooked into template_redirect so is_page works.
 *
 * @since 2.0.0
 *
 * @global object $wp_query WordPress Query object.
 * @global object $wp WordPress post object.
 * @global object $post post object.
 */
function geodir_template_redirect() {
	global $wp_query, $wp, $post;

	if ( is_page() ) {
		if ( ! isset( $_REQUEST['listing_type'] ) && geodir_is_page( 'add-listing' ) && ! isset( $_REQUEST['action'] ) ) {
			if ( ! empty( $_REQUEST['pid'] ) && ( $post_type = get_post_type( absint( $_REQUEST['pid'] ) ) ) ) {
			} else {
				$page_id = ! empty( $post->ID ) && $post->post_type == 'page' ? $post->ID : 0;

				if ( $page_id && ( $_post_type = geodir_cpt_template_post_type( $page_id, 'add' ) ) ) {
					$post_type = $_post_type; // Detect post from CPT add listing page id.
				} else {
					$post_type = geodir_add_listing_default_post_type();
				}

				if ( ! empty( $post->post_content ) && has_shortcode( $post->post_content, 'gd_add_listing' ) ) {
					$regex_pattern = get_shortcode_regex( array( 'gd_add_listing' ) );
					preg_match( '/' . $regex_pattern . '/s', $post->post_content, $regex_matches );

					if ( ! empty( $regex_matches ) && ! empty( $regex_matches[2] ) == 'gd_add_listing' && ! empty( $regex_matches[3] ) ) {
						$shortcode_atts = shortcode_parse_atts( $regex_matches[3] );
						$post_type = ! empty( $shortcode_atts ) && ! empty( $shortcode_atts['post_type'] ) && geodir_is_gd_post_type( $shortcode_atts['post_type'] ) ? $shortcode_atts['post_type'] : $post_type;
					}
				}
			}

			if ( ! empty( $post_type ) ) {
				wp_redirect( add_query_arg( array( 'listing_type' => $post_type ) ) );
				exit;
			}
		}
	}
}

add_action( 'template_redirect', 'geodir_template_redirect' );

/**
 * Front end listing view template selection.
 *
 * This function adds a drop down in front end listing page for selecting view template. Ex: list view, 2 column grid
 * view, etc.
 *
 * @since   1.0.0
 * @package GeoDirectory
 *
 */
function geodir_list_view_select() {

	$design_style = geodir_design_style();
	$template = $design_style ? $design_style."/loop/select-layout.php" : "loop/select-layout.php";
	echo geodir_get_template_html( $template );

}

add_action( 'geodir_extra_loop_actions', 'geodir_list_view_select', 8 );


/**
 * Output the listing archive image
 *
 * @since 2.0.0
 */
function geodir_listing_archive_image() {

}

/**
 * Retrieve page ids - used for myaccount, edit_address, shop, cart, checkout, pay, view_order, terms. returns -1 if no page is found.
 *
 * @since 2.0.0
 *
 * @param string $page
 * @param string $post_type Post type.
 * @param  bool $translated Optional. Translated page ID .Default true.
 *
 * @return int
 */
function geodir_get_page_id( $page, $post_type = '', $translated = true ) {
	$page_id = 0;
	if ( ! empty( $post_type ) ) {
		$page_id = geodir_get_cpt_page_id( $page, $post_type );
	}

	if ( ! $page_id ) {
		$page_id = geodir_get_option( 'page_' . $page );
	}

	$page_id = apply_filters( 'geodir_get_page_id', $page_id, $page, $post_type, $translated );

	return $page_id ? absint( $page_id ) : - 1;
}

/**
 * post_closed_text function.
 *
 * The function is use for display post closed text content.
 *
 * Check if $echo is true then echo post closed text html content
 * else return post closed text html content.
 *
 * @since 2.0.0
 *
 * @param object $post Post object.
 * @param bool $echo Optional. Default true.
 *
 * @return string Post closed text.
 */
function geodir_post_closed_text( $post, $echo = true ) {
	if ( ! empty( $post ) && ! empty( $post->post_type ) ) {
		$cpt_name = geodir_strtolower( geodir_post_type_singular_name( $post->post_type ) );
	} else {
		$cpt_name = __( 'business', 'geodirectory' );
	}

	ob_start();

	geodir_get_template( 'view/post-closed-text.php', array(
		'cpt_name' => $cpt_name
	) );

	if ( $echo ) {
		echo ob_get_clean();
	} else {
		return ob_get_clean();
	}
}

/**
 * Function for add extra listing listview class in ul.
 *
 * @since 2.0.0
 *
 * @global string $gd_layout_class Listing view class.
 *
 * @param string $class Listing listview ul class.
 * @param string $template listing template.
 *
 * @return string $class
 */
function geodir_listing_listview_ul_extra_class( $class, $template ) {
	global $gd_layout_class;

	$class .= ' geodir-' . $template . '-posts';
	if ( ! empty( $gd_layout_class ) ) {
		$class .= ' geodir-gridview ' . $gd_layout_class;
	} else {
		$class .= ' '.geodir_grid_view_class(0);
	}
	$class = trim( $class );

	return $class;
}

add_filter( 'geodir_listing_listview_ul_extra_class', 'geodir_listing_listview_ul_extra_class', 10, 2 );

/**
 * Front end listing view extra actions.
 *
 * This function adds extra actions in listing page top actions.
 *
 * @since  2.0.0
 * @package GeoDirectory
 *
 */
function geodir_extra_loop_actions() {
	$post_type = geodir_get_current_posttype();

	do_action( 'geodir_extra_loop_actions', $post_type );
}

/**
 * Retrieve CPT page id.
 *
 * @since 2.0.0.28
 *
 * @param string $page
 * @param string $post_type Post type.
 *
 * @return int
 */
function geodir_get_cpt_page_id( $page, $post_type = '' ) {
	$page_id = 0;
	$pages   = array( 'page_add', 'page_details', 'page_archive', 'page_archive_item' );

	if ( empty( $page ) || ! in_array( 'page_' . $page, $pages ) ) {
		return $page_id;;
	}

	$page_id = wp_cache_get( "geodir_cpt_template_page_id:{$post_type}:{$page}", 'geodir_cpt_template_page' );

	if ( $page_id !== false ) {
		return $page_id;
	}

	$post_types = geodir_get_posttypes( 'array' );
	if ( ! empty( $post_types ) && ! empty( $post_types[ $post_type ][ 'page_' . $page ] ) ) {
		$page_id = (int) $post_types[ $post_type ][ 'page_' . $page ];
	}

	wp_cache_set( "geodir_cpt_template_page_id:{$post_type}:{$page}", $page_id, 'geodir_cpt_template_page' );

	return $page_id;
}

/**
 * Adds a responsive embed wrapper around oEmbed content
 *
 * @param string $html The oEmbed markup.
 * @param string $url  The URL being embedded. Default empty.
 * @param array  $attr An array of attributes. Default empty.
 * @param int    $post_ID Post ID. Default 0.
 * @return string       Updated embed markup
 */
function geodir_responsive_embeds( $html, $url = '', $attr = array(), $post_ID = 0 ) {
	if ( empty( $url ) ) {
		return $html;
	}

	if ( false !== strpos( $url, "://www.youtube.com" ) || false !== strpos( $url, "://youtube.com" ) || false !== strpos( $url, "://youtu.be" ) ) {
		$html = '<div class="geodir-embed-container">' . $html . '</div>';
	}

	return $html;
}
add_filter('embed_oembed_html', 'geodir_responsive_embeds', 10, 3);

/**
 * Get the grid view class depending on the style selected.
 *
 * @param int $view
 *
 * @since 2.1.0
 * @return string
 */
function geodir_grid_view_class($view = 0){
	$style = geodir_design_style();
	if($view == 5){
		return $style == '' ? 'gridview_onefifth' : 'row-cols-md-5';
	}elseif($view == 4){
		return $style == '' ? 'gridview_onefourth' : 'row-cols-md-4';
	}elseif($view == 3){
		return $style == '' ? 'gridview_onethird' : 'row-cols-md-3';
	}elseif($view == 2){
		return $style == '' ? 'gridview_onehalf' : 'row-cols-md-2';
	}elseif($view == 1){
		return $style == '' ? 'gridview_one' : 'row-cols-md-1';
	}else{
		return $style == '' ? '' : 'row-cols-md-0';
	}
}