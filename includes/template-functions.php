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

    // Look in yourtheme/slug-name.php and yourtheme/woocommerce/slug-name.php
    if ( $name ) {
        $template = locate_template( array( "{$slug}-{$name}.php", geodir_get_theme_template_dir_name() . "/{$slug}-{$name}.php" ) );
    }

    // Get default slug-name.php
    if ( !$template && $name && file_exists( geodir_get_templates_dir() . "/{$slug}-{$name}.php" ) ) {
        $template = geodir_get_templates_dir() . "/{$slug}-{$name}.php";
    }

    // If template file doesn't exist, look in yourtheme/slug.php and yourtheme/woocommerce/slug.php
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

function geodir_add_body_classes( $class ) {
    $classes = (array) $class;

    return array_unique( $classes );
}
add_filter( 'body_class', 'geodir_add_body_classes' );

// TODO remove
/**
 * Locates template based on the template type.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global string $post_type The post type.
 * @global object $wp WordPress object.
 * @global object $post WordPress post object.
 * @param string $template The template type.
 * @return bool|string The template path.
 */
function geodir_locate_template_old($template = '')
{
    global $post_type, $wp, $post;
    $fields = array();

    switch ($template):
        case 'signup':
            return $template = locate_template(array("geodirectory/geodir-signup.php"));
            break;
        case 'add-listing':
            $gd_post_types = geodir_get_posttypes();
            
            if (!(!empty($post_type) && in_array($post_type, $gd_post_types))) {
                $post_type = '';
            }
            
            $sc_post_type = '';
            if (is_page() && isset($post->post_content) && has_shortcode($post->post_content, 'gd_add_listing')) {
                $listing_page_id = $post->ID;
                
                $regex_pattern = get_shortcode_regex();
                preg_match('/'.$regex_pattern.'/s', $post->post_content, $regex_matches);
                
                if (!empty($regex_matches) && isset($regex_matches[2]) == 'gd_add_listing' && isset($regex_matches[3])) {
                    $shortcode_atts = shortcode_parse_atts($regex_matches[3]);
                    $sc_post_type = !empty($shortcode_atts) && isset($shortcode_atts['listing_type']) && !empty($shortcode_atts['listing_type']) ? $shortcode_atts['listing_type'] : '';
                }
            } else {
                $listing_page_id = geodir_add_listing_page_id();
            }
            
            $is_wpml = function_exists('icl_object_id') ? true : false;

            if ($listing_page_id != '' && (is_page($listing_page_id) || ($is_wpml && !empty($wp->query_vars['page_id']))) && isset($_REQUEST['listing_type'])
                && in_array($_REQUEST['listing_type'], $gd_post_types)) {
                $post_type = sanitize_text_field($_REQUEST['listing_type']);
            }
            
            if (empty($post_type) && !isset($_REQUEST['pid'])) {
                $pagename = $wp->query_vars['pagename'];
                
                if (!empty($gd_post_types)) {
                    $post_type = $gd_post_types[0];
                }
                
                if ($sc_post_type != '') {
                    $post_type = $sc_post_type;
                }
                
                if (empty($post_type) && !empty($gd_post_types)) {
                    $post_type = $gd_post_types[0];
                }
                
                if ($is_wpml && !empty($wp->query_vars['page_id'])) {
                    wp_redirect(geodir_getlink(get_permalink($wp->query_vars['page_id']), array('listing_type' => $post_type)));
                } else {
                    wp_redirect(trailingslashit(get_site_url()) . $pagename . '/?listing_type=' . $post_type);
                }
                gd_die();
            }
            return $template = locate_template(array("geodirectory/add-{$post_type}.php", "geodirectory/add-listing.php"));
            break;
        case 'success':
            $success_page_id = geodir_success_page_id();
            if ($success_page_id != '' && is_page($success_page_id) && isset($_REQUEST['listing_type'])
                && in_array($_REQUEST['listing_type'], geodir_get_posttypes())
            )
                $post_type = sanitize_text_field($_REQUEST['listing_type']);
            return $template = locate_template(array("geodirectory/{$post_type}-success.php", "geodirectory/listing-success.php"));
            break;
        case 'detail':
        case 'preview':
            if (in_array(get_post_type(), geodir_get_posttypes()))
                $post_type = get_post_type();
            return $template = locate_template(array("geodirectory/single-{$post_type}.php", "geodirectory/single-listing.php"));
            break;
        case 'listing':
            $templates = array();
            if (is_post_type_archive() && in_array(get_post_type(), geodir_get_posttypes())) {
                $post_type = get_post_type();
                $templates[] = "geodirectory/archive-$post_type.php";
            }


            if (is_tax() && geodir_get_taxonomy_posttype()) {
                $query_obj = get_queried_object();
                $curr_taxonomy = isset($query_obj->taxonomy) ? $query_obj->taxonomy : '';
                $curr_term = isset($query_obj->slug) ? $query_obj->slug : '';
                $templates[] = "geodirectory/taxonomy-$curr_taxonomy-$curr_term.php";
                $templates[] = "geodirectory/taxonomy-$curr_taxonomy.php";
            }

            $templates[] = "geodirectory/geodir-listing.php";

            return $template = locate_template($templates);
            break;
        case 'information':
            return $template = locate_template(array("geodirectory/geodir-information.php"));
            break;
        case 'author':
            return $template = locate_template(array("geodirectory/geodir-author.php"));
            break;
        case 'search':
            return $template = locate_template(array("geodirectory/geodir-search.php"));
            break;
        case 'location':
            return $template = locate_template(array("geodirectory/geodir-location.php"));
            break;
        case 'geodir-home':
            return $template = locate_template(array("geodirectory/geodir-home.php"));
            break;
        case 'listing-listview':
            $template = locate_template(array("geodirectory/listing-listview.php"));
            if (!$template) {
                $template = geodir_plugin_path() . '/includes/templates/listing-listview.php';
            }
            return $template;
            break;
        case 'widget-listing-listview':
            $template = locate_template(array("geodirectory/widget-listing-listview.php"));
            if (!$template) {
                $template = geodir_plugin_path() . '/includes/templates/widget-listing-listview.php';
            }
            return $template;
            break;
    endswitch;

    return false;

}

/**
 * Loads template based on the current page.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wp_query WordPress Query object.
 * @todo $wp_query declared twice - fix it.
 * @global object $post The current post object.
 * @global object $current_user Current user object.
 * @param string $template The template path.
 * @return bool|string The template path.
 */
function geodir_template_loader($template)
{

    global $wp_query;

    /**
     * Filter the custom page list.
     *
     * @since 1.0.0
     */
    $geodir_custom_page_list = apply_filters('geodir_set_custom_pages', array(
        'geodir_signup_page' =>
            apply_filters('geodir_set_custom_signup_page', false),
        'geodir_add_listing_page' =>
            apply_filters('geodir_set_custom_add_listing_page', false),
        'geodir_preview_page' =>
            apply_filters('geodir_set_custom_preview_page', false),
        'geodir_listing_success_page' =>
            apply_filters('geodir_set_custom_listing_success_page', false),
        'geodir_listing_detail_page' =>
            apply_filters('geodir_set_custom_listing_detail_page', false),
        'geodir_listing_page' =>
            apply_filters('geodir_set_custom_listing_page', false),
        'geodir_search_page' =>
            apply_filters('geodir_set_custom_search_page', false),
        'geodir_author_page' =>
            apply_filters('geodir_set_custom_author_page', false),
        'geodir_home_map_page' =>
            apply_filters('geodir_set_custom_home_map_page', false)
    ));


    if (geodir_is_page('login') || $geodir_custom_page_list['geodir_signup_page']) {

        $template = geodir_locate_template('signup');

        if (!$template) $template = geodir_plugin_path() . '/includes/templates/geodir-signup.php';

        /**
         * Filter the signup template path.
         *
         * @since 1.0.0
         * @param string $template The template path.
         */
        return $template = apply_filters('geodir_template_signup', $template);
    }

    if (geodir_is_page('add-listing') || $geodir_custom_page_list['geodir_add_listing_page']) {
        if (!geodir_is_default_location_set()) {
            global $information;
            $information = sprintf(__('Please %sclick here%s to set a default location, this will make the plugin work properly.', 'geodirectory'), '<a href=\'' . admin_url('admin.php?page=geodirectory&tab=default_location_settings') . '\'>', '</a>');

            $template = geodir_locate_template('information');

            if (!$template) $template = geodir_plugin_path() . '/includes/templates/geodir-information.php';
            /**
             * Filter the information template path.
             *
             * @since 1.0.0
             * @param string $template The template path.
             */
            return $template = apply_filters('geodir_template_information', $template);
        }
        // check if pid exists in the record if yes then check if this post belongs to the user who is logged in.
        if (isset($_REQUEST['pid']) && $_REQUEST['pid'] != '') {
            /// WPML
            if (geodir_wpml_is_post_type_translated(get_post_type((int)$_GET['pid'])) && $duplicate_of = wpml_get_master_post_from_duplicate((int)$_GET['pid'])) {
                global $sitepress;
                
                $lang_of_duplicate = geodir_get_language_for_element($duplicate_of, 'post_' . get_post_type($duplicate_of));
                $sitepress->switch_lang($lang_of_duplicate, true);
        
                $redirect_to = get_permalink(geodir_add_listing_page_id());
                $_GET['pid'] = $duplicate_of;
                if (!empty($_GET)) {
                    $redirect_to = add_query_arg($_GET, $redirect_to);
                }
                wp_redirect($redirect_to);
                exit;
            }
            /// WPML
            
            global $information;
            $information = __('This listing does not belong to your account, please check the listing id carefully.', 'geodirectory');
            $is_current_user_owner = geodir_listing_belong_to_current_user();
            if (!$is_current_user_owner) {
                $template = geodir_locate_template('information');

                if (!$template) $template = geodir_plugin_path() . '/includes/templates/geodir-information.php';
                /**
                 * Filter the information template path.
                 *
                 * @since 1.0.0
                 * @param string $template The template path.
                 */
                return $template = apply_filters('geodir_template_information', $template);
            }


        }

        //geodir_is_login(true);
        global $current_user;
        if (!$current_user->ID) {
            wp_redirect(geodir_login_url(array('redirect_add_listing'=>urlencode(geodir_curPageURL()))), 302);
            exit;
        }

        $template = geodir_locate_template('add-listing');

        if (!$template) $template = geodir_plugin_path() . '/includes/templates/add-listing.php';
        /**
         * Filter the add listing template path.
         *
         * @since 1.0.0
         * @param string $template The template path.
         */
        return $template = apply_filters('geodir_template_add_listing', $template);
    }


    if (geodir_is_page('preview') || $geodir_custom_page_list['geodir_preview_page']) {
        global $preview;
        $preview = true;

        $template = geodir_locate_template('preview');

        if (!$template) $template = geodir_plugin_path() . '/includes/templates/single-listing.php';
        /**
         * Filter the preview template path.
         *
         * @since 1.0.0
         * @param string $template The template path.
         */
        return $template = apply_filters('geodir_template_preview', $template);

    }


    if (geodir_is_page('listing-success') || $geodir_custom_page_list['geodir_listing_success_page']) {

        $template = geodir_locate_template('success');

        if (!$template) $template = geodir_plugin_path() . '/includes/templates/listing-success.php';
        /**
         * Filter the success template path.
         *
         * @since 1.0.0
         * @param string $template The template path.
         */
        return $template = apply_filters('geodir_template_success', $template);

    }

    if (geodir_is_page('detail') || $geodir_custom_page_list['geodir_listing_detail_page']) {

        $template = geodir_locate_template('detail');

        if (!$template) $template = geodir_plugin_path() . '/includes/templates/single-listing.php';
        /**
         * Filter the detail template path.
         *
         * @since 1.0.0
         * @param string $template The template path.
         */
        return $template = apply_filters('geodir_template_detail', $template);

    }

    if (geodir_is_page('listing') || $geodir_custom_page_list['geodir_listing_page']) {

        $template = geodir_locate_template('listing');

        if (!$template) $template = geodir_plugin_path() . '/includes/templates/geodir-listing.php';
        /**
         * Filter the listing template path.
         *
         * @since 1.0.0
         * @param string $template The template path.
         */
        return $template = apply_filters('geodir_template_listing', $template);

    }

    if (geodir_is_page('search') || $geodir_custom_page_list['geodir_search_page']) {

        $template = geodir_locate_template('search');

        if (!$template) $template = geodir_plugin_path() . '/includes/templates/geodir-search.php';
        /**
         * Filter the search template path.
         *
         * @since 1.0.0
         * @param string $template The template path.
         */
        return $template = apply_filters('geodir_template_search', $template);

    }

    if (geodir_is_page('author') || $geodir_custom_page_list['geodir_author_page']) {

        $template = geodir_locate_template('author');

        if (!$template) $template = geodir_plugin_path() . '/includes/templates/geodir-author.php';
        /**
         * Filter the author template path.
         *
         * @since 1.0.0
         * @param string $template The template path.
         */
        return $template = apply_filters('geodir_template_author', $template);

    }

    if ( geodir_is_page('home') || geodir_is_page('location')) {

        global $post, $wp_query;

        if (geodir_is_page('home') || ('page' == get_option('show_on_front') && isset($post->ID) && $post->ID == get_option('page_on_front'))
            || (is_home() && !$wp_query->is_posts_page)
        ) {

            $template = geodir_locate_template('geodir-home');

            if (!$template) $template = geodir_plugin_path() . '/includes/templates/geodir-home.php';
            /**
             * Filter the home page template path.
             *
             * @since 1.0.0
             * @param string $template The template path.
             */
            return $template = apply_filters('geodir_template_homepage', $template);

        } elseif (geodir_is_page('location')) {

            $template = geodir_locate_template('location');

            if (!$template) $template = geodir_plugin_path() . '/includes/templates/geodir-location.php';
            /**
             * Filter the location template path.
             *
             * @since 1.0.0
             * @param string $template The template path.
             */
            return $template = apply_filters('geodir_template_location', $template);

        } else
            return $template;

    }

    return $template;
}

// TODO remove
/**
 * Locates template part based on the template slug.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $post The current post object.
 * @global object $geodirectory Not yet implemented.
 * @param string $slug The template slug.
 * @param null $name The template name.
 */
function geodir_get_template_part_old($slug = '', $name = NULL)
{
    global $geodirectory, $post;
    /**
     * Called at the start for the geodir_get_template_part() function.
     *
     * Used dynamic hook name: geodir_get_template_part_{$slug}
     *
     * @since 1.0.0
     * @package GeoDirectory
     * @param string $slug The template slug.
     * @param string $name The template name.
     */
    do_action("geodir_get_template_part_{$slug}", $slug, $name);
    $templates = array();
    $name = (string)$name;
    if ('' !== $name) {
        $template_name = "{$slug}-{$name}.php";

    } else {
        $template_name = "{$slug}.php";
    }

    if (!locate_template(array("geodirectory/" . $template_name))) :
        /**
         * Filter the template part with slug and name.
         *
         * @since 1.0.0
         * @param string $template_name The template name.
         */
        $template = apply_filters("geodir_template_part-{$slug}-{$name}", geodir_plugin_path() . '/includes/templates/' . $template_name);
        /**
         * Includes the template part with slug and name.
         *
         * @since 1.0.0
         */
        include($template);
    else:
        locate_template(array("geodirectory/" . $template_name), true, false);
    endif;

}

/**
 * Appends extra HTML classes to the post class.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $post The current post object.
 * @param string $class The old class string.
 * @param string|array $all_postypes The GD post types.
 * @return string The modified post class.
 */
function geodir_core_post_view_extra_class($class, $all_postypes = '')
{
    global $post;

    if (!$all_postypes) {
        $all_postypes = geodir_get_posttypes();
    }

    $gdp_post_id = !empty($post) && isset($post->ID) ? $post->ID : NULL;
    $gdp_post_type = $gdp_post_id > 0 && isset($post->post_type) ? $post->post_type : NULL;
    $gdp_post_type = $gdp_post_type != '' && !empty($all_postypes) && in_array($gdp_post_type, $all_postypes) ? $gdp_post_type : NULL;

    if ($gdp_post_id && $gdp_post_type) {
        $append_class = 'gd-post-' . $gdp_post_type;
        $append_class .= isset($post->is_featured) && $post->is_featured > 0 ? ' gd-post-featured' : '';
        $class = $class != '' ? $class . ' ' . $append_class : $append_class;
    }

    return $class;
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
 * Strips </li><li> tags from Breadcrumb HTML to wrap breadcrumb html.
 *
 * Using </li><li> breaks the links to a new line when window size is small(ex: in mobile device).
 *
 * @since 1.5.5
 * @param string $breadcrumb Breadcrumb HTML.
 * @param string $separator Breadcrumb separator.
 * @return string Breadcrumb HTML.
 */
function geodir_strip_breadcrumb_li_wrappers($breadcrumb, $separator) {
	$breadcrumb = str_replace(array('</li><li>', '</li> <li>'), '', $breadcrumb);
	
	return $breadcrumb;
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
 * Replace the font awesome rating icons in comment form.
 *
 * @since 1.5.7
 * @package GeoDirectory
 *
 * @param string $html Rating icons html.
 * @param array $star_texts Rating icons labels.
 * @param int|null $default Default rating value to get selected.
 * @return string Rating icons html content.
 */
function geodir_font_awesome_rating_form_html($html, $star_texts = array(), $default = '') {
	if ( geodir_get_option( 'geodir_reviewrating_enable_font_awesome' ) == '1' ) {
		$html = '<select class="gd-fa-rating">';
		$html .= '<option value=""></option>';
		if (!empty($star_texts) && is_array($star_texts)) {
			foreach ($star_texts as $i => $text) {
				$html .= '<option ' . selected((int)($i + 1), (int)$default, false) . ' value="' . (int)($i + 1) . '">' . $text . '</option>';
			}
		} else {
			$html .= '<option value="1">1</option>';
			$html .= '<option value="2">2</option>';
			$html .= '<option value="3">3</option>';
			$html .= '<option value="4">4</option>';
			$html .= '<option value="5">5</option>';
		}
		$html .= '</select>';
	}

	return $html;
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
        'geodir_detail_page_google_analytics',
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
     * @param array array('geodir_social_sharing_buttons','geodir_share_this_button','geodir_detail_page_google_analytics','geodir_edit_post_link','geodir_detail_page_review_rating','geodir_detail_page_more_info') The array of functions that will be called.
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
    
    geodir_get_template( 'view/loop-header.php', array( 'sorting' => trim( $sorting ), 'layout_selection' => trim( $layout_selection ) ) );
    
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
    
    geodir_get_template( 'view/loop-start.php', array( 'header_options' => $header_options ) );
    
    if ( $echo ) {
        echo ob_get_clean();
    } else {
        return ob_get_clean();
    }
}

function geodir_listing_loop_end( $echo = true ) {
    ob_start();

    geodir_get_template( 'view/loop-end.php' );

    if ( $echo ) {
        echo ob_get_clean();
    } else {
        return ob_get_clean();
    }
}

function geodir_output_content_wrapper_start() {
    geodir_get_template( 'view/wrapper-start.php' );
}

function geodir_output_content_wrapper_end() {
    geodir_get_template( 'view/wrapper-end.php' );
}

function geodir_get_sidebar() {
    geodir_get_template( 'view/sidebar.php' );
}

// TODO remove
function geodir_breadcrumb() {
}

/**
 * Main function that generates breadcrumb for all pages.
 *
 * @since   1.0.0
 * @since   1.5.7 Changes for the neighbourhood system improvement.
 * @since   1.6.16 Fix: Breadcrumb formatting issue with the neighbourhood name.
 * @package GeoDirectory
 * @global object $wp_query   WordPress Query object.
 * @global object $post       The current post object.
 * @global object $gd_session GeoDirectory Session object.
 */
function geodir_output_breadcrumb( $args = array() ) {
	global $wp_query, $geodir_add_location_url;

	/**
	 * Filter breadcrumb separator.
	 *
	 * @since 1.0.0
	 */
	$separator = apply_filters( 'geodir_breadcrumb_separator', ' > ' );

	if ( ! geodir_is_page( 'home' ) ) {
		$breadcrumb    = '';
		$url_categoris = '';
		/**
		 * Filter breadcrumb's first link.
		 *
		 * @since 1.0.0
		 */
		$breadcrumb .= '<li>' . apply_filters( 'geodir_breadcrumb_first_link', '<a href="' . home_url() . '">' . __( 'Home', 'geodirectory' ) . '</a>' ) . '</li>';

		$gd_post_type   = geodir_get_current_posttype();
		$post_type_info = get_post_type_object( $gd_post_type );

		remove_filter( 'post_type_archive_link', 'geodir_get_posttype_link' );

		$listing_link = get_post_type_archive_link( $gd_post_type );

		add_filter( 'post_type_archive_link', 'geodir_get_posttype_link', 10, 2 );
		$listing_link = rtrim( $listing_link, '/' );
		$listing_link .= '/';

		$post_type_for_location_link = $listing_link;
		$location_terms              = geodir_get_current_location_terms( 'query_vars', $gd_post_type );

		global $wp, $gd_session;
		$location_link = $post_type_for_location_link;

		if ( geodir_is_page( 'detail' ) || geodir_is_page( 'listing' ) ) {
			global $post;
			$location_manager     = defined( 'POST_LOCATION_TABLE' ) ? true : false;
			$neighbourhood_active = $location_manager && geodir_get_option( 'location_neighbourhoods' ) ? true : false;

			if ( geodir_is_page( 'detail' ) && isset( $post->country_slug ) ) {
				$location_terms = array(
					'gd_country' => $post->country_slug,
					'gd_region'  => $post->region_slug,
					'gd_city'    => $post->city_slug
				);

				if ( $neighbourhood_active && ! empty( $location_terms['gd_city'] ) && $gd_ses_neighbourhood = $gd_session->get( 'gd_neighbourhood' ) ) {
					$location_terms['gd_neighbourhood'] = $gd_ses_neighbourhood;
				}
			}

			$geodir_show_location_url = geodir_get_option( 'geodir_show_location_url' );

			$hide_url_part = array();
			if ( $location_manager ) {
				$hide_country_part = geodir_get_option( 'geodir_location_hide_country_part' );
				$hide_region_part  = geodir_get_option( 'geodir_location_hide_region_part' );

				if ( $hide_region_part && $hide_country_part ) {
					$hide_url_part = array( 'gd_country', 'gd_region' );
				} else if ( $hide_region_part && ! $hide_country_part ) {
					$hide_url_part = array( 'gd_region' );
				} else if ( ! $hide_region_part && $hide_country_part ) {
					$hide_url_part = array( 'gd_country' );
				}
			}

			$hide_text_part = array();
			if ( $geodir_show_location_url == 'country_city' ) {
				$hide_text_part = array( 'gd_region' );

				if ( isset( $location_terms['gd_region'] ) && ! $location_manager ) {
					unset( $location_terms['gd_region'] );
				}
			} else if ( $geodir_show_location_url == 'region_city' ) {
				$hide_text_part = array( 'gd_country' );

				if ( isset( $location_terms['gd_country'] ) && ! $location_manager ) {
					unset( $location_terms['gd_country'] );
				}
			} else if ( $geodir_show_location_url == 'city' ) {
				$hide_text_part = array( 'gd_country', 'gd_region' );

				if ( isset( $location_terms['gd_country'] ) && ! $location_manager ) {
					unset( $location_terms['gd_country'] );
				}
				if ( isset( $location_terms['gd_region'] ) && ! $location_manager ) {
					unset( $location_terms['gd_region'] );
				}
			}

			$is_location_last = '';
			$is_taxonomy_last = '';
			$breadcrumb .= '<li>';
			if ( get_query_var( $gd_post_type . 'category' ) ) {
				$gd_taxonomy = $gd_post_type . 'category';
			} elseif ( get_query_var( $gd_post_type . '_tags' ) ) {
				$gd_taxonomy = $gd_post_type . '_tags';
			}

			$breadcrumb .= $separator . '<a href="' . $listing_link . '">' . __( geodir_utf8_ucfirst( $post_type_info->label ), 'geodirectory' ) . '</a>';
			if ( ! empty( $gd_taxonomy ) || geodir_is_page( 'detail' ) ) {
				$is_location_last = false;
			} else {
				$is_location_last = true;
			}

			if ( ! empty( $gd_taxonomy ) && geodir_is_page( 'listing' ) ) {
				$is_taxonomy_last = true;
			} else {
				$is_taxonomy_last = false;
			}

			if ( ! empty( $location_terms ) ) {
				$geodir_get_locations = function_exists( 'get_actual_location_name' ) ? true : false;

				foreach ( $location_terms as $key => $location_term ) {
					if ( $location_term != '' ) {
						if ( ! empty( $hide_url_part ) && in_array( $key, $hide_url_part ) ) { // Hide location part from url & breadcrumb.
							continue;
						}

						$gd_location_link_text = preg_replace( '/-(\d+)$/', '', $location_term );
						$gd_location_link_text = preg_replace( '/[_-]/', ' ', $gd_location_link_text );
						$gd_location_link_text = geodir_utf8_ucfirst( $gd_location_link_text );

						$location_term_actual_country = '';
						$location_term_actual_region  = '';
						$location_term_actual_city    = '';
						$location_term_actual_neighbourhood = '';
						if ( $geodir_get_locations ) {
							if ( $key == 'gd_country' ) {
								$location_term_actual_country = get_actual_location_name( 'country', $location_term, true );
							} else if ( $key == 'gd_region' ) {
								$location_term_actual_region = get_actual_location_name( 'region', $location_term, true );
							} else if ( $key == 'gd_city' ) {
								$location_term_actual_city = get_actual_location_name( 'city', $location_term, true );
							} else if ( $key == 'gd_neighbourhood' ) {
								$location_term_actual_neighbourhood = get_actual_location_name( 'neighbourhood', $location_term, true );
							}
						} else {
							$location_info = geodir_get_location();

							if ( ! empty( $location_info ) && isset( $location_info->location_id ) ) {
								if ( $key == 'gd_country' ) {
									$location_term_actual_country = __( $location_info->country, 'geodirectory' );
								} else if ( $key == 'gd_region' ) {
									$location_term_actual_region = __( $location_info->region, 'geodirectory' );
								} else if ( $key == 'gd_city' ) {
									$location_term_actual_city = __( $location_info->city, 'geodirectory' );
								}
							}
						}

						if ( $is_location_last && $key == 'gd_country' && ! ( isset( $location_terms['gd_region'] ) && $location_terms['gd_region'] != '' ) && ! ( isset( $location_terms['gd_city'] ) && $location_terms['gd_city'] != '' ) ) {
							$breadcrumb .= $location_term_actual_country != '' ? $separator . $location_term_actual_country : $separator . $gd_location_link_text;
						} else if ( $is_location_last && $key == 'gd_region' && ! ( isset( $location_terms['gd_city'] ) && $location_terms['gd_city'] != '' ) ) {
							$breadcrumb .= $location_term_actual_region != '' ? $separator . $location_term_actual_region : $separator . $gd_location_link_text;
						} else if ( $is_location_last && $key == 'gd_city' && empty( $location_terms['gd_neighbourhood'] ) ) {
							$breadcrumb .= $location_term_actual_city != '' ? $separator . $location_term_actual_city : $separator . $gd_location_link_text;
						} else if ( $is_location_last && $key == 'gd_neighbourhood' ) {
							$breadcrumb .= $location_term_actual_neighbourhood != '' ? $separator . $location_term_actual_neighbourhood : $separator . $gd_location_link_text;
						} else {
							if ( get_option( 'permalink_structure' ) != '' ) {
								$location_link .= $location_term . '/';
							} else {
								$location_link .= "&$key=" . $location_term;
							}

							if ( $key == 'gd_country' && $location_term_actual_country != '' ) {
								$gd_location_link_text = $location_term_actual_country;
							} else if ( $key == 'gd_region' && $location_term_actual_region != '' ) {
								$gd_location_link_text = $location_term_actual_region;
							} else if ( $key == 'gd_city' && $location_term_actual_city != '' ) {
								$gd_location_link_text = $location_term_actual_city;
							} else if ( $key == 'gd_neighbourhood' && $location_term_actual_neighbourhood != '' ) {
								$gd_location_link_text = $location_term_actual_neighbourhood;
							}

							/*
                            if (geodir_is_page('detail') && !empty($hide_text_part) && in_array($key, $hide_text_part)) {
                                continue;
                            }
                            */

							$breadcrumb .= $separator . '<a href="' . $location_link . '">' . $gd_location_link_text . '</a>';
						}
					}
				}
			}

			if ( ! empty( $gd_taxonomy ) ) {
				$term_index = 1;

				//if(geodir_get_option('geodir_add_categories_url'))
				{
					if ( get_query_var( $gd_post_type . '_tags' ) ) {
						$cat_link = $listing_link . 'tags/';
					} else {
						$cat_link = $listing_link;
					}

					foreach ( $location_terms as $key => $location_term ) {
						if ( $location_manager && in_array( $key, $hide_url_part ) ) {
							continue;
						}

						if ( $location_term != '' ) {
							if ( get_option( 'permalink_structure' ) != '' ) {
								$cat_link .= $location_term . '/';
							}
						}
					}

					$term_array = explode( "/", trim( $wp_query->query[ $gd_taxonomy ], "/" ) );
					foreach ( $term_array as $term ) {
						$term_link_text = preg_replace( '/-(\d+)$/', '', $term );
						$term_link_text = preg_replace( '/[_-]/', ' ', $term_link_text );

						// get term actual name
						$term_info = get_term_by( 'slug', $term, $gd_taxonomy, 'ARRAY_A' );
						if ( ! empty( $term_info ) && isset( $term_info['name'] ) && $term_info['name'] != '' ) {
							$term_link_text = urldecode( $term_info['name'] );
						} else {
							continue;
							//$term_link_text = wp_strip_all_tags(geodir_ucwords(urldecode($term_link_text)));
						}

						if ( $term_index == count( $term_array ) && $is_taxonomy_last ) {
							$breadcrumb .= $separator . $term_link_text;
						} else {
							$cat_link .= $term . '/';
							$breadcrumb .= $separator . '<a href="' . $cat_link . '">' . $term_link_text . '</a>';
						}
						$term_index ++;
					}
				}


			}

			if ( geodir_is_page( 'detail' ) ) {
				$breadcrumb .= $separator . get_the_title();
			}

			$breadcrumb .= '</li>';


		} elseif ( geodir_is_page( 'author' ) ) {
			$dashboard_post_type = isset($_REQUEST['stype']) ? sanitize_text_field($_REQUEST['stype']) : $gd_post_type;
			$user_id             = get_current_user_id();
			$author_link         = get_author_posts_url( $user_id );
			$default_author_link = geodir_getlink( $author_link, array(
				'geodir_dashbord' => 'true',
				'stype'           => $dashboard_post_type
			), false );

			/**
			 * Filter author page link.
			 *
			 * @since 1.0.0
			 *
			 * @param string $default_author_link Default author link.
			 * @param int $user_id                Author ID.
			 */
			$default_author_link = apply_filters( 'geodir_dashboard_author_link', $default_author_link, $user_id );

			$breadcrumb .= '<li>';
			$breadcrumb .= $separator . '<a href="' . $default_author_link . '">' . __( 'My Dashboard', 'geodirectory' ) . '</a>';

			if ( isset( $_REQUEST['list'] ) ) {
				$author_link = geodir_getlink( $author_link, array(
					'geodir_dashbord' => 'true',
					'stype'           => $_REQUEST['stype']
				), false );

				/**
				 * Filter author page link.
				 *
				 * @since 1.0.0
				 *
				 * @param string $author_link Author page link.
				 * @param int $user_id        Author ID.
				 * @param string $_REQUEST    ['stype'] Post type.
				 */
				$author_link = apply_filters( 'geodir_dashboard_author_link', $author_link, $user_id, $_REQUEST['stype'] );

				$breadcrumb .= $separator . '<a href="' . $author_link . '">' . __( geodir_utf8_ucfirst( $post_type_info->label ), 'geodirectory' ) . '</a>';
				$breadcrumb .= $separator . geodir_utf8_ucfirst( __( 'My', 'geodirectory' ) . ' ' . $_REQUEST['list'] );
			} else {
				$breadcrumb .= $separator . __( geodir_utf8_ucfirst( $post_type_info->label ), 'geodirectory' );
			}

			$breadcrumb .= '</li>';
		} elseif ( is_category() || is_single() ) {
			$category = get_the_category();
			if ( is_category() ) {
				$breadcrumb .= '<li>' . $separator . $category[0]->cat_name . '</li>';
			}
			if ( is_single() ) {
				$breadcrumb .= '<li>' . $separator . '<a href="' . get_category_link( $category[0]->term_id ) . '">' . $category[0]->cat_name . '</a></li>';
				$breadcrumb .= '<li>' . $separator . get_the_title() . '</li>';
			}
			/* End of my version ##################################################### */
		} else if ( is_page() ) {
			$page_title = get_the_title();

			if ( geodir_is_page( 'location' ) ) {
				$location_page_id = geodir_location_page_id();
				$loc_post         = get_post( $location_page_id );
				$post_name        = $loc_post->post_name;
				$slug             = ucwords( str_replace( '-', ' ', $post_name ) );
				$page_title       = ! empty( $slug ) ? $slug : __( 'Location', 'geodirectory' );
			}

			$breadcrumb .= '<li>' . $separator;
			$breadcrumb .= stripslashes_deep( $page_title );
			$breadcrumb .= '</li>';
		} else if ( is_tag() ) {
			$breadcrumb .= "<li> " . $separator . single_tag_title( '', false ) . '</li>';
		} else if ( is_day() ) {
			$breadcrumb .= "<li> " . $separator . __( " Archive for", 'geodirectory' ) . " ";
			the_time( 'F jS, Y' );
			$breadcrumb .= '</li>';
		} else if ( is_month() ) {
			$breadcrumb .= "<li> " . $separator . __( " Archive for", 'geodirectory' ) . " ";
			the_time( 'F, Y' );
			$breadcrumb .= '</li>';
		} else if ( is_year() ) {
			$breadcrumb .= "<li> " . $separator . __( " Archive for", 'geodirectory' ) . " ";
			the_time( 'Y' );
			$breadcrumb .= '</li>';
		} else if ( is_author() ) {
			$breadcrumb .= "<li> " . $separator . __( " Author Archive", 'geodirectory' );
			$breadcrumb .= '</li>';
		} else if ( isset( $_GET['paged'] ) && ! empty( $_GET['paged'] ) ) {
			$breadcrumb .= "<li>" . $separator . __( "Blog Archives", 'geodirectory' );
			$breadcrumb .= '</li>';
		} else if ( is_search() ) {
			$breadcrumb .= "<li> " . $separator . __( " Search Results", 'geodirectory' );
			$breadcrumb .= '</li>';
		}
        
        /**
         * Filter breadcrumb separator.
         *
         * @since 1.0.0
         */
        $separator = apply_filters( 'geodir_breadcrumb_separator', '&nbsp;>&nbsp;' );
        
        $defaults = array(
            'home' > _x( 'Home', 'breadcrumb', 'GeoDirectory' ),
            'separator' => $separator,
            'wrap_start' => '<div class="geodir-breadcrumb clearfix"><ul id="breadcrumbs">',
            'wrap_end' => '</ul></div>',
            'before' => '',
            'after' => '',
            'content' => $breadcrumb
        );
        
        $args = wp_parse_args( $args, apply_filters( 'geodir_breadcrumb_defaults', $defaults ) );
        
        ob_start();
        
        do_action( 'geodir_output_breadcrumb', $breadcrumb, $args );

        geodir_get_template( 'view/breadcrumb.php', $args );
        
        $output = ob_get_clean();

		/**
		 * Filter breadcrumb html output.
		 *
		 * @since 1.0.0
		 *
		 * @param string $breadcrumb Breadcrumb HTML.
		 * @param string $separator  Breadcrumb separator.
         * @param array $args Breadcrumb args.
		 */
		echo $breadcrumb = apply_filters( 'geodir_breadcrumb', $output, $separator, $args );
	}
}

function geodir_listing_loop_class() {
    $class = apply_filters( 'geodir_listing_loop_class', '' );
    
    echo ' class="geodir_category_list_view clearfix ' . esc_attr( $class ) . '"';
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

function geodir_listing_old_attrs( $attrs, $post ) {
    global $listing_width;
    
    if ( !empty( $listing_width ) && (float)$listing_width > 0 ) {
        $attrs['style'] = 'width:' . (float)$listing_width . '%';
    }
    
    return $attrs;
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