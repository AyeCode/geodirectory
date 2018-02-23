<?php
/**
 * Template functions
 *
 * @since 1.0.0
 * @package GeoDirectory
 */

function geodir_get_templates_dir() {
    return GEODIRECTORY_PLUGIN_DIR . 'templates';
}

function geodir_get_templates_url() {
    return GEODIRECTORY_PLUGIN_URL . '/templates';
}

function geodir_get_theme_template_dir_name() {
    return untrailingslashit( apply_filters( 'geodir_templates_dir', 'geodirectory' ) );
}

function geodir_get_template_part( $slug, $name = '' ) {
    $load_template = apply_filters( 'geodir_allow_template_part_' . $slug . '_' . $name, true );
    if ( false === $load_template ) {
        return '';
    }
    
    $template = '';

    if ( $name ) {
        // Look in yourtheme/slug-name.php and yourtheme/geodirectory/slug-name.php
        $template = locate_template( array( "{$slug}-{$name}.php", geodir_get_theme_template_dir_name() . "/{$slug}-{$name}.php" ) );
    } else {
        // Look in yourtheme/slug-name.php and yourtheme/geodirectory/slug.php
        $template = locate_template( array( "{$slug}.php", geodir_get_theme_template_dir_name() . "/{$slug}.php" ) );
    }

    // Get default slug-name.php
    if ( !$template ) {
        if ( $name && file_exists( geodir_get_templates_dir() . "/{$slug}-{$name}.php" ) ) {
            $template = geodir_get_templates_dir() . "/{$slug}-{$name}.php";
        } else if ( !$name && file_exists( geodir_get_templates_dir() . "/{$slug}.php" ) ) {
            $template = geodir_get_templates_dir() . "/{$slug}.php";
        }
    }

    // If template file doesn't exist, look in yourtheme/slug.php and yourtheme/geodirectory/slug.php
    if ( !$template ) {
        $template = locate_template( array( "{$slug}.php", geodir_get_theme_template_dir_name() . "/{$slug}.php" ) );
    }

    // Allow 3rd party plugins to filter template file from their plugin.
    $template = apply_filters( 'geodir_get_template_part', $template, $slug, $name );

    if ( $template ) {
        load_template( $template, false );
    }
}

function geodir_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
    if ( !empty( $args ) && is_array( $args ) ) {
        extract( $args );
    }

    $located = geodir_locate_template( $template_name, $template_path, $default_path );

    if ( !file_exists( $located ) ) {
        geodir_doing_it_wrong( __FUNCTION__, sprintf( __( '%s does not exist.', 'geodirectory' ), '<code>' . $located . '</code>' ), '2.1' );
        return;
    }

    // Allow 3rd party plugin filter template file from their plugin.
    $located = apply_filters( 'geodir_get_template', $located, $template_name, $args, $template_path, $default_path );

    do_action( 'geodir_before_template_part', $template_name, $template_path, $located, $args );

    include( $located );

    do_action( 'geodir_after_template_part', $template_name, $template_path, $located, $args );
}

function geodir_get_template_html( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
    ob_start();
    geodir_get_template( $template_name, $args, $template_path, $default_path );
    return ob_get_clean();
}

function geodir_locate_template( $template_name, $template_path = '', $default_path = '' ) {
    if ( !$template_path ) {
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
    if ( !$template ) {
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
	geodir_get_template( 'loop/no-listings-found.php' );
}

/**
 * Content to display when no listings are found.
 *
 */
function geodir_loop_paging() {
	geodir_get_template( 'loop/pagination.php' );
}


/**
 * Display loop actions such as sort order and listing view type.
 *
 */
function geodir_loop_actions() {
	do_action('geodir_before_loop_actions');
	geodir_get_template( 'loop/actions.php' );
	do_action('geodir_after_loop_actions');
}

/**
 * Display message when no listing result found.
 *
 * @since 1.5.5
 * @package GeoDirectory
 *
 * @param string $template_listview Optional. Listing listview template. Ex: listing-listview, widget-listing-listview,
                 gdevents_widget_listview, link-business-listview. Default: 'listing-listview'.
 * @param bool $favorite Listing Optional. Are favorite listings results? Default: false.
 */
function geodir_display_message_not_found_on_listing($template_listview = 'listing-listview', $favorite = false) {
    if ($favorite) {
		$message = __('No favorite listings found which match your selection.', 'geodirectory');
	} else {
		$message = __('No listings found which match your selection.', 'geodirectory');
	}
	
	/**
	 * Filter the no listing found message.
	 *
	 * @since 1.5.5
	 * @param string $template_listview Listing listview template.
	 * @param bool $favorite Are favorite listings results?
	 */
	$message = apply_filters('geodir_message_listing_not_found', $message, $template_listview, $favorite);
	
	echo '<li class="no-listing">' . $message . '</li>';
}


/**
 * Get listing listview class for current column length.
 *
 * @since 1.5.7
 * @param int $columns Column length(ex: 1,2,3,4,5). Default empty.
 * @return string Listing listview class.
 */
function geodir_convert_listing_view_class($columns = '') {
	$class = '';
	
	switch ((int)$columns) {
		case 1:
			$class = '';
		break;
		case 2:
			$class = 'gridview_onehalf';
		break;
		case 3:
			$class = 'gridview_onethird';
		break;
		case 4:
			$class = 'gridview_onefourth';
		break;
		case 5:
			$class = 'gridview_onefifth';
		break;
		default:
			$class = '';
		break;
	}
	
	return $class;
}

/**
 * Filter to hide the listing excerpt.
 *
 * @since 1.5.7
 * @param bool $display Display the excerpt or not.
 * @param string $view The view type, Ex: 'listview'.
 * @param object $post The post object.
 * @return bool Modified value for display the excerpt.
 */
function geodir_show_listing_post_excerpt($display, $view, $post) {
	if ($view == 'listview') {
		if (geodir_is_page('author')) {
			$word_limit = geodir_get_option('geodir_author_desc_word_limit');
		} else {
			$word_limit = geodir_get_option('geodir_desc_word_limit');
		}
		
		if ($word_limit !== '' && ($word_limit == 0 || $word_limit == '0')) {
			$display = false;
		}
	}
	return $display;
}



/**
 * Display the font awesome rating icons in place of default rating images.
 *
 * @since 1.5.7
 * @package GeoDirectory
 *
 * @param string $html Rating icons html.
 * @param float $rating Current rating value.
 * @param int $star_count Total rating stars. Default 5.
 * @return string Rating icons html content.
 */
function geodir_font_awesome_rating_stars_html($html, $rating, $star_count = 5) {
	if ( geodir_get_option( 'geodir_reviewrating_enable_font_awesome' ) == '1' ) {
		$rating = min($rating, $star_count);
		$full_stars = floor( $rating );
		$half_stars = ceil( $rating - $full_stars );
		$empty_stars = $star_count - $full_stars - $half_stars;
		
		$html = '<div class="gd-star-rating gd-fa-star-rating">';
		$html .= str_repeat( '<i class="fa fa-star gd-full-star"></i>', $full_stars );
		$html .= str_repeat( '<i class="fa fa-star-o fa-star-half-full gd-half-star"></i>', $half_stars );
		$html .= str_repeat( '<i class="fa fa-star-o gd-empty-star"></i>', $empty_stars);
		$html .= '</div>';
	}

	return $html;
}

/**
 * Adds the style for the font awesome rating icons.
 *
 * @since 1.5.7
 * @package GeoDirectory
 */
function geodir_font_awesome_rating_css() {
	// Font awesome rating style
	if ( geodir_get_option( 'geodir_reviewrating_enable_font_awesome' ) == '1' ) {
		$full_color = geodir_get_option( 'geodir_reviewrating_fa_full_rating_color', '#757575' );
		if ( $full_color != '#757575' ) {
			echo '<style type="text/css">.br-theme-fontawesome-stars .br-widget a.br-active:after,.br-theme-fontawesome-stars .br-widget a.br-selected:after,
			.gd-star-rating i.fa {color:' . stripslashes( $full_color ) . '!important;}</style>';
		}
	}
}

function geodir_detail_page_sidebar_functions() {
    $detail_sidebar_content = array(
        'geodir_social_sharing_buttons',
        'geodir_edit_post_link',
        'geodir_detail_page_review_rating',
        'geodir_detail_page_more_info'
    );
    
    /**
     * An array of functions to be called to be displayed on the details (post) page sidebar.
     *
     * This filter can be used to remove sections of the details page sidebar,
     * add new sections or rearrange the order of the sections.
     *
     * @param array array('geodir_social_sharing_buttons','geodir_share_this_button','geodir_edit_post_link','geodir_detail_page_review_rating','geodir_detail_page_more_info') The array of functions that will be called.
     * @since 1.0.0
     */
    return apply_filters( 'geodir_detail_page_sidebar_content', $detail_sidebar_content );
}

function geodir_page_title( $echo = true ) {
    if ( is_search() ) {
        $page_title = sprintf( __( 'Search results: &ldquo;%s&rdquo;', 'geodirectory' ), get_search_query() );

        if ( get_query_var( 'paged' ) )
            $page_title .= sprintf( __( '&nbsp;&ndash; Page %s', 'geodirectory' ), get_query_var( 'paged' ) );

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

function geodir_listing_loop_header( $echo = true ) {
    ob_start();
        
    geodir_display_sort_options();
    
    $sorting = ob_get_clean();
    
    ob_start();
        
    geodir_list_view_select();
    
    $layout_selection = ob_get_clean();
    
    ob_start();
    
    geodir_get_template( 'listing/loop-header.php', array( 'sorting' => trim( $sorting ), 'layout_selection' => trim( $layout_selection ) ) );
    
    if ( $echo ) {
        echo ob_get_clean();
    } else {
        return ob_get_clean();
    }
}

function geodir_listing_loop_start( $echo = true ) {
    global $gridview_columns, $grid_view_class, $gd_session, $related_nearest, $related_parent_lat, $related_parent_lon;
    /**
     * Filter the default grid view class.
     *
     * This can be used to filter the default grid view class but can be overridden by a user $_SESSION.
     *
     * @since 1.0.0
     * @param string $gridview_columns The grid view class, can be '', 'gridview_onehalf', 'gridview_onethird', 'gridview_onefourth' or 'gridview_onefifth'.
     */
    $grid_view_class = apply_filters( 'geodir_grid_view_widget_columns', $gridview_columns );
    if ( $gd_session->get( 'gd_listing_view' ) && !isset( $before_widget ) && !isset( $related_posts ) ) {
        $grid_view_class = geodir_convert_listing_view_class( $gd_session->get( 'gd_listing_view' ) );
    }
    
    ob_start();
    
    $GLOBALS['geodir_loop']['loop'] = 0;
    
    $header_options = geodir_listing_loop_header( false );
    
    //geodir_get_template( 'listing/loop-start.php', array( 'header_options' => $header_options ) );
    
    if ( $echo ) {
        echo ob_get_clean();
    } else {
        return ob_get_clean();
    }
}

function geodir_listing_loop_end( $echo = true ) {
    ob_start();

    geodir_get_template( 'listing/loop-end.php' );

    if ( $echo ) {
        echo ob_get_clean();
    } else {
        return ob_get_clean();
    }
}






function geodir_listing_class( $post = null, $classes = array() ) {
    if ( !is_array( $classes ) ) {
        $classes = array();
    }
    
    $classes[] = 'clearfix';
    
    if ( !( is_object( $post ) && !empty( $post->ID ) ) ) {
        $post = get_post( $post );
    }
    
    if ( !empty( $post ) ) {
        if ( !empty( $post->post_type ) ) {
            $classes[] = 'gd-post-' . $post->post_type;
        }
        
        if ( !empty( $post->is_featured ) ) {
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
    if ( !is_array( $attrs ) ) {
        $attrs = array();
    }
    
    if ( !( is_object( $post ) && !empty( $post->ID ) ) ) {
        $post = get_post( $post );
    }
    
    if ( !empty( $post ) ) {
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

function geodir_listing_inner_class( $post = null, $classes = array() ) {
    if ( !is_array( $classes ) ) {
        $classes = array();
    }
    
    $classes[] = 'geodir-category-listing';
    
    if ( !( is_object( $post ) && !empty( $post->ID ) ) ) {
        $post = get_post( $post );
    }
    
    if ( !empty( $post ) ) {
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

function geodir_listing_old_classes( $classes, $post ) {
    global $grid_view_class;
    
    if ( $grid_view_class ) {
        $classes[] = 'geodir-gridview ' . $grid_view_class;
    } else {
        $classes[] = 'geodir-listview';
    }
    
    return $classes;
}

function geodir_listing_inner_old_classes( $classes, $post ) {
    /**
     * Add a class to the `article` tag inside the `li` element on the listings list template.
     *
     * @since 1.0.0
     * @param string $class The extra class for the `article` element, default empty.
     */
    $post_view_article_class = apply_filters( 'geodir_post_view_article_extra_class', '' );
    
    if ( !empty( $post_view_article_class ) ) {
        $classes[] = $post_view_article_class;
    }
    
    return $classes;
}
add_filter( 'geodir_listing_classes', 'geodir_listing_old_classes', 10, 2 );
add_filter( 'geodir_listing_inner_classes', 'geodir_listing_inner_old_classes', 10, 2 );

/**
 * Handle redirects before content is output - hooked into template_redirect so is_page works.
 */
function geodir_template_redirect() {
    global $wp_query, $wp, $post;

    if ( is_page() ) {

        if ( !isset( $_REQUEST['listing_type'] ) && geodir_is_page( 'add-listing' ) ) {
            if ( !empty( $_REQUEST['pid'] ) && $post_type = get_post_type( absint( $_REQUEST['pid'] ) ) ) {
            } else {
                $post_type = geodir_add_listing_default_post_type();

                if ( !empty( $post->post_content ) && has_shortcode( $post->post_content, 'gd_add_listing' ) ) {

                    $regex_pattern = get_shortcode_regex();
                    preg_match( '/' . $regex_pattern . '/s', $post->post_content, $regex_matches );

                    if ( !empty( $regex_matches ) && !empty( $regex_matches[2] ) == 'gd_add_listing' && !empty( $regex_matches[3] ) ) {
                        $shortcode_atts = shortcode_parse_atts( $regex_matches[3] );
                        $post_type = !empty( $shortcode_atts ) && !empty( $shortcode_atts['listing_type'] ) ? $shortcode_atts['listing_type'] : $post_type;
                    }
                }
            }

            if ( !empty( $post_type ) ) {
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
 * @global object $gd_session GeoDirectory Session object.
 */
function geodir_list_view_select() {
	global $gd_session;
	?>
	<script type="text/javascript">

		function geodir_list_view_select(list) {
			//alert(listval);
			val = list.value;
			if (!val) {
				return;
			}

			var listSel = jQuery(list).parent().parent().next('.geodir_category_list_view');
			if (val != 1) {
				jQuery(listSel).children('li').addClass('geodir-gridview');
				jQuery(listSel).children('li').removeClass('geodir-listview');
			} else {
				jQuery(listSel).children('li').addClass('geodir-listview');
			}

			if (val == 1) {
				jQuery(listSel).children('li').removeClass('geodir-gridview gridview_onehalf gridview_onethird gridview_onefourth gridview_onefifth');
			}
			else if (val == 2) {
				jQuery(listSel).children('li').GDswitchClass('gridview_onethird gridview_onefourth gridview_onefifth', 'gridview_onehalf', 600);
			}
			else if (val == 3) {
				jQuery(listSel).children('li').GDswitchClass('gridview_onehalf gridview_onefourth gridview_onefifth', 'gridview_onethird', 600);
			}
			else if (val == 4) {
				jQuery(listSel).children('li').GDswitchClass('gridview_onehalf gridview_onethird gridview_onefifth', 'gridview_onefourth', 600);
			}
			else if (val == 5) {
				jQuery(listSel).children('li').GDswitchClass('gridview_onehalf gridview_onethird gridview_onefourth', 'gridview_onefifth', 600);
			}

			jQuery.post("<?php echo geodir_get_ajax_url();?>&gd_listing_view=" + val, function (data) {
				//alert(data );
			});
		}
	</script>
	<div class="geodir-list-view-select">
		<select name="gd_list_view" id="gd_list_view" onchange="geodir_list_view_select(this);">
			<?php $listing_view = (int) $gd_session->get( 'gd_listing_view' ); ?>
			<option value=""><?php _e( 'View:', 'geodirectory' ); ?></option>
			<option
				value="1" <?php selected( 1, $listing_view ); ?>><?php _e( 'View: List', 'geodirectory' ); ?></option>
			<option
				value="2" <?php selected( 2, $listing_view ); ?>><?php _e( 'View: Grid 2', 'geodirectory' ); ?></option>
			<option
				value="3" <?php selected( 3, $listing_view ); ?>><?php _e( 'View: Grid 3', 'geodirectory' ); ?></option>
			<option
				value="4" <?php selected( 4, $listing_view ); ?>><?php _e( 'View: Grid 4', 'geodirectory' ); ?></option>
			<option
				value="5" <?php selected( 5, $listing_view ); ?>><?php _e( 'View: Grid 5', 'geodirectory' ); ?></option>
		</select>
	</div>
	<?php
}


/**
 * Output the listing archive image
 */
function geodir_listing_archive_image(){

}

/**
 * Retrieve page ids - used for myaccount, edit_address, shop, cart, checkout, pay, view_order, terms. returns -1 if no page is found.
 *
 * @param string $page
 * @return int
 */
function geodir_get_page_id( $page, $wpml = true ) {
	$page = geodir_get_option( 'page_' . $page );
	
	if ( $wpml && function_exists( 'icl_object_id' ) ) {
		$page =  icl_object_id( $page, 'page', true );
	}
	
	$page = apply_filters( 'geodir_get_' . $page . '_page_id', $page, $wpml );

	return $page ? absint( $page ) : -1;
}

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