<?php
/**
 * Post Types Functions
 *
 * All functions related to post types.
 *
 * @package GeoDirectory
 * @since   2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get list of geodirectory Post Types.
 *
 * @since 1.0.0
 * @since 1.5.1 options case added to get post type options array.
 * @since 2.0.0 options-plural option added.
 * @package GeoDirectory
 * @param string $output The output Type.
 * @return array|object|string Post Types.
 */
function geodir_get_posttypes($output = 'names')
{
    $post_types = array();
    $post_types = geodir_get_option('post_types');
    $post_types = stripslashes_deep($post_types);
    if (!empty($post_types)) {
        switch ($output):
            case 'object':
            case 'Object':
                $post_types = json_decode(json_encode($post_types), FALSE);//(object)$post_types;
                break;
            case 'array':
            case 'Array':
                $post_types = (array)$post_types;
                break;
            case 'options':
                $post_types = (array)$post_types;

                $options = array();
                if (!empty($post_types)) {
                    foreach ($post_types as $key => $info) {
                        $options[$key] = __($info['labels']['singular_name'], 'geodirectory');
                    }
                }
                $post_types = $options;
                break;
            case 'options-plural':
                $post_types = (array)$post_types;

                $options = array();
                if (!empty($post_types)) {
                    foreach ($post_types as $key => $info) {
                        $options[$key] = __($info['labels']['name'], 'geodirectory');
                    }
                }
                $post_types = $options;
                break;
            default:
                $post_types = array_keys($post_types);
                break;
        endswitch;
    }

    if (!empty($post_types))
        return $post_types;
    else
        return array();
}

/**
 * Get the slug for a given CPT.
 *
 * @param $post_type string The CPT string.
 *
 * @return mixed The slug or false.
 */
function geodir_get_ctp_slug($post_type){
    $post_types = geodir_get_posttypes('array');
    if(isset($post_types[$post_type]) && isset($post_types[$post_type]['rewrite']['slug']) && $post_types[$post_type]['rewrite']['slug'] ){
        return $post_types[$post_type]['rewrite']['slug'];
    }

    return false;
}

/**
 * Get post type options array.
 *
 * @since 2.0.0
 *
 * @param bool $plural_name True to get plural post type name. Default false.
 * @param bool $translated True to get translated name. Default false.
 * @return array GD post types options array.
 */
function geodir_post_type_options( $plural_name = false, $translated = false ) {
    $post_types = geodir_get_posttypes( 'object' );

    $options = array();
    if ( !empty( $post_types ) ) {
        foreach ( $post_types as $key => $post_type_obj ) {
            $name = $plural_name ? $post_type_obj->labels->name : $post_type_obj->labels->singular_name;
            if ( $translated ) {
                $name = __( $name, 'geodirectory' );
            }
            $options[ $key ] = $name;
        }

        if ( !empty( $options ) ) {
            $options = array_unique( $options );
        }
    }

    return $options;
}

/**
 * Check given post type is GD post type or not.
 *
 * @since 2.0.0
 *
 * @param string $post_type The post type.
 * @return bool True if given post type is GD post type, otherwise False.
 */
function geodir_is_gd_post_type( $post_type ) {
    global $gd_is_post_type;

    if ( empty( $post_type ) || is_array($post_type) ) {
        return false;
    }

    if ( strpos( $post_type, 'gd_' ) !== 0 ) {
        return false;
    }

    if ( !empty( $gd_is_post_type ) && !empty( $gd_is_post_type[ $post_type ] ) ) {
        return true;
    }

    $gd_posttypes = geodir_get_posttypes();

    if ( !empty( $gd_posttypes ) && in_array( $post_type, $gd_posttypes ) ) {
        if ( !is_array( $gd_is_post_type ) ) {
            $gd_is_post_type = array();
        }

        $gd_is_post_type[ $post_type ] = true;

        return true;
    }

    return false;
}

/**
 * Get posttype object by posttype.
 *
 * @since 2.0.0
 *
 * @param string $post_type Get post type.
 * @return object $post_type_obj.
 */
function geodir_post_type_object( $post_type ) {
    if ( geodir_is_gd_post_type( $post_type ) ) {
        $post_types = geodir_get_posttypes( 'object' );

        $post_type_obj = !empty( $post_types->{$post_type} ) ? $post_types->{$post_type} : NULL;
    } else {
        $post_type_obj = get_post_type_object( $post_type );
    }

    return $post_type_obj;
}

/**
 * Get posttype name by posttype.
 *
 * Check if $translated is true then post name get in translated
 * else post name without translated.
 *
 * @since 2.0.0
 *
 * @param string $post_type Get posttype.
 * @param bool $translated Optional. Default false.
 * @return string Posttype name.
 */
function geodir_post_type_name( $post_type, $translated = false ) {
    $post_type_obj = geodir_post_type_object( $post_type );

    if ( !( !empty( $post_type_obj ) && !empty( $post_type_obj->labels->name ) ) ) {
        return $post_type;
    }

    $name = $post_type_obj->labels->name;
    if ( $translated ) {
        $name = __( $name, 'geodirectory' );
    }

    return apply_filters( 'geodir_post_type_name', $name, $post_type, $translated );
}

/**
 * Get the posttype singular name by posttype.
 *
 * Check if $translated is true then display translated singular name
 * else without translated name.
 *
 * @since 2.0.0
 *
 * @param string $post_type Get posttype.
 * @param bool $translated Optional. Default false.
 * @return string posttype singular name.
 */
function geodir_post_type_singular_name( $post_type, $translated = false ) {
    $post_type_obj = geodir_post_type_object( $post_type );

    if ( !( !empty( $post_type_obj ) && !empty( $post_type_obj->labels->singular_name ) ) ) {
        return $post_type;
    }

    $singular_name = $post_type_obj->labels->singular_name;
    if ( $translated ) {
        $singular_name = __( $singular_name, 'geodirectory' );
    }

    return apply_filters( 'geodir_post_type_singular_name', $singular_name, $post_type, $translated );
}

/**
 * Get the listing owner label for the post type.
 *
 * @since 2.3.7
 *
 * @param string $post_type The post type.
 * @param bool $translated Optional. Default true.
 * @return string Listing owner label.
 */
function geodir_listing_owner_label( $post_type, $translated = true ) {
	$label = 'Listing Owner';

	if ( ! empty( $post_type ) ) {
		$post_type_obj = geodir_post_type_object( $post_type );

		if ( ! empty( $post_type_obj ) && ! empty( $post_type_obj->labels->listing_owner ) ) {
			$label = $post_type_obj->labels->listing_owner;
		}
	}

	if ( $translated ) {
		$label = __( $label, 'geodirectory' );
	}

	/**
	 * Filter the listing owner label for the post type.
	 *
	 * @since 2.3.7
	 *
	 * @param string $label Listing owner label.
	 * @param string $post_type The post type.
	 * @param bool $translated Optional. Default true.
	 */
	return apply_filters( 'geodir_listing_owner_label', $label, $post_type, $translated );
}

/**
 * Get and array of CPTs allowed to be added from the frontend.
 *
 * @return mixed|void
 */
function geodir_add_listing_allowed_post_types() {

    $allowed_post_types = array();
    $post_types = geodir_get_posttypes('array');
    if(!empty($post_types)){
        foreach($post_types as $cpt => $post_type){
            if(!isset($post_type['disable_frontend_add']) || $post_type['disable_frontend_add']=='0'){
                $allowed_post_types[] = $cpt;
            }
        }
    }

    return apply_filters( 'geodir_add_listing_allowed_post_types', $allowed_post_types  );
}

/**
 * Get default listing posttype.
 *
 * @since 2.0.0
 *
 * @return string $post_type default listing posttype.
 */
function geodir_add_listing_default_post_type() {
    $post_types = geodir_add_listing_allowed_post_types();

    $post_type = !empty( $post_types ) && is_array( $post_types ) ? $post_types[0] : '';

    return apply_filters( 'geodir_add_listing_default_post_type', $post_type );
}

/**
 * Get default search post type.
 *
 * @since 2.1.0.17
 *
 * @return string $post_type Default post type.
 */
function geodir_search_default_post_type() {
	$post_types = geodir_get_posttypes();

	$post_type = ! empty( $post_types ) && is_array( $post_types ) ? $post_types[0] : 'gd_place';

	return apply_filters( 'geodir_search_default_post_type', $post_type );
}

/**
 * Function for check listing posttype.
 *
 * @since 2.0.0
 *
 * @param $post_type Get posttype.
 * @return bool $return.
 */
function geodir_add_listing_check_post_type( $post_type ) {
    if ( !geodir_is_gd_post_type( $post_type ) ) {
        return false;
    }

    $allowed_post_types = geodir_add_listing_allowed_post_types();

    if ( !empty( $allowed_post_types ) && is_array( $allowed_post_types ) && in_array( $post_type, $allowed_post_types ) ) {
        $return = true;
    } else {
        $return = false;
    }

    return apply_filters( 'geodir_add_listing_check_post_type', $return, $post_type );
}

/**
 * Get the post types that have favourites enabled.
 *
 * @since 2.0.0
 * @return array
 */
function geodir_fav_allowed_post_types(){
    $postypes = geodir_get_posttypes( 'array' );

    $allowed_cpts = array();
    if(!empty($postypes)){
        foreach($postypes as $cpt => $postype){
            if(!isset($postype['disable_favorites']) || !$postype['disable_favorites']){
                $allowed_cpts[] = $cpt;
            }
        }
    }

    return $allowed_cpts;
}


/**
 * Returns the post type link with parameters.
 *
 * @since 1.0.0
 * @since 1.5.5 Fixed post type archive link for selected location.
 * @package GeoDirectory
 *
 * @global bool $geodir_add_location_url If true it will add location name in url.
 * @global object $post WordPress Post object.
 *
 * @param string $link The post link.
 * @param string $post_type The post type.
 * @return string The modified link.
 */
function geodir_posttype_link($link, $post_type) {
    global $geodir_add_location_url, $post;

    $location_terms = array();

    if (in_array($post_type, geodir_get_posttypes())) {
        if (geodir_get_option('geodir_add_location_url')) {
            if(geodir_is_page('detail') && !empty($post) && isset($post->country_slug)) {
                $location_terms = array(
                    'gd_country' => $post->country_slug,
                    'gd_region' => $post->region_slug,
                    'gd_city' => $post->city_slug
                );
            } else {
                $location_terms = geodir_get_current_location_terms('query_vars');
            }

            $location_terms = geodir_remove_location_terms($location_terms);

            if (!empty($location_terms)) {
                if (get_option('permalink_structure') != '') {
                    $location_terms = implode("/", $location_terms);
                    $location_terms = rtrim($location_terms, '/');

                    $link .= urldecode($location_terms) . '/';
                } else {
                    $link = geodir_getlink($link, $location_terms);
                }
            }
        }
    }

    return $link;
}

/**
 * Print or Get post type singular label.
 *
 * @since 2.1.0.5
 *
 * @package GeoDirectory
 * @param string $post_type The post type.
 * @param bool $echo Prints the label when set to true.
 * @param bool $translate Returns translated label if True. Default false.
 * @return void|string Label.
 */
function geodir_get_post_type_singular_label( $post_type, $echo = false, $translate = false ) {
    $obj_post_type = get_post_type_object($post_type);
    if (!is_object($obj_post_type)) {
        return;
    }

    $label = $translate ? __($obj_post_type->labels->singular_name, 'geodirectory') : $obj_post_type->labels->singular_name;

    if ($echo)
        echo $label;
    else
        return $label;
}

/**
 * Print or Get post type plural label.
 *
 * @since 2.1.0.5
 *
 * @package GeoDirectory
 * @param string $post_type The post type.
 * @param bool $echo Prints the label when set to true.
 * @param bool $translate Returns translated label if True. Default false.
 * @return void|string Label.
 */
function geodir_get_post_type_plural_label( $post_type, $echo = false, $translate = false ) {
    $all_postypes = geodir_get_posttypes();

    if (!in_array($post_type, $all_postypes))
        return false;

    $obj_post_type = get_post_type_object($post_type);

    $label = $translate ? __($obj_post_type->labels->name, 'geodirectory') : $obj_post_type->labels->name;

    if ($echo)
        echo $label;
    else
        return $label;
}

/**
 * Custom post type messages for admin actions.
 *
 * Replaces "Post" in the update messages for custom post types on the "Edit" post screen.
 * For example "Post updated. View Post." becomes "Place updated. View Place".
 *
 * @since 1.0.0
 *
 * @package GeoDirectory
 *
 * @global object $post WordPress Post object.
 * @global object $post_ID WordPress Post ID.
 *
 * @param array $messages Messages.
 * @return array $messages.
 */
function geodir_custom_update_messages($messages)
{
    global $post, $post_ID;

    $post_types = get_post_types(array('show_ui' => true, '_builtin' => false), 'objects');

    foreach ($post_types as $post_type => $post_object) {

        $messages[$post_type] = array(
            0 => '', // Unused. Messages start at index 1.
            1 => sprintf(__('%s updated. <a href="%s">View %s</a>', 'geodirectory'), $post_object->labels->singular_name, esc_url(get_permalink($post_ID)), $post_object->labels->singular_name),
            2 => __('Custom field updated.', 'geodirectory'),
            3 => __('Custom field deleted.', 'geodirectory'),
            4 => sprintf(__('%s updated.', 'geodirectory'), $post_object->labels->singular_name),
            5 => isset($_GET['revision']) ? sprintf(__('%s restored to revision from %s', 'geodirectory'), $post_object->labels->singular_name, wp_post_revision_title((int)$_GET['revision'], false)) : false,
            6 => sprintf(__('%s published. <a href="%s">View %s</a>', 'geodirectory'), $post_object->labels->singular_name, esc_url(get_permalink($post_ID)), $post_object->labels->singular_name),
            7 => sprintf(__('%s saved.', 'geodirectory'), $post_object->labels->singular_name),
            8 => sprintf(__('%s submitted. <a target="_blank" href="%s">Preview %s</a>', 'geodirectory'), $post_object->labels->singular_name, esc_url(add_query_arg('preview', 'true', get_permalink($post_ID))), $post_object->labels->singular_name),
            9 => sprintf(__('%s scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview %s</a>', 'geodirectory'), $post_object->labels->singular_name, date_i18n(__('M j, Y @ G:i', 'geodirectory'), strtotime($post->post_date)), esc_url(get_permalink($post_ID)), $post_object->labels->singular_name),
            10 => sprintf(__('%s draft updated. <a target="_blank" href="%s">Preview %s</a>', 'geodirectory'), $post_object->labels->singular_name, esc_url(add_query_arg('preview', 'true', get_permalink($post_ID))), $post_object->labels->singular_name),
        );
    }

    return $messages;
}
add_filter('post_updated_messages', 'geodir_custom_update_messages');

/**
 * Get Custom Post Type info.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @param string $post_type The post type.
 * @return bool|array Post type details.
 */
function geodir_get_posttype_info( $post_type = '' ) {
    $post_types = geodir_get_posttypes('array');
    $post_types = stripslashes_deep( $post_types );

    if ( ! empty( $post_types ) && $post_type != '' && isset( $post_types[ $post_type ] ) ) {
        return $post_types[ $post_type ];
    } else
        return false;
}

/**
 * Get default Post Type.
 *
 * @since 1.6.9
 * @package GeoDirectory
 * @global object $wp_query WordPress Query object.
 * @global string $geodir_post_type The post type.
 * @return string The post type.
 */
function geodir_get_default_posttype() {
	global $wpdb;

	$post_types = apply_filters( 'geodir_get_default_posttype', geodir_get_posttypes( 'object' ) );

	$stype = false;

	foreach ( $post_types as $post_type => $info ) {
		if ( geodir_cpt_has_post( $post_type ) ) {
			$stype = $post_type;

			break;
		}
	}

	if ( ! $stype ) {
		$stype = 'gd_place';
	}

	return $stype;
}

/*
 * Check post type has published post.
 *
 * @since 2.3.85
 *
 * @param string $post_type The post type.
 * @return string True if has posts or False.
 */
function geodir_cpt_has_post( $post_type ) {
	global $wpdb, $geodir_cpt_has_post;

	$has_post = false;

	// Check global cached.
	if ( ! ( is_array( $geodir_cpt_has_post ) && isset( $geodir_cpt_has_post[ $post_type ] ) ) ) {
		geodir_cpt_set_has_post();
	}

	if ( is_array( $geodir_cpt_has_post ) && isset( $geodir_cpt_has_post[ $post_type ] ) ) {
		$has_post = $geodir_cpt_has_post[ $post_type ];
	}

	return $has_post;
}

/*
 * Set post type has post found.
 *
 * @since 2.3.85
 */
function geodir_cpt_set_has_post() {
	global $wpdb, $geodir_cpt_has_post;

	if ( empty( $geodir_cpt_has_post ) ) {
		$geodir_cpt_has_post = array();
	}

	$post_types = geodir_get_posttypes();

	if ( empty( $post_types ) ) {
		return;
	}

	$fields = array();
	$values = array();

	foreach ( $post_types as $post_type ) {
		$fields[] = 'post_type = %s';
		$values[] = $post_type;
	}

	$where = count( $fields ) > 1 ? "( " . implode( " OR ", $fields ) . " )" : $fields[0];

	$col = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT post_type FROM `{$wpdb->posts}` WHERE post_status = 'publish' AND {$where}", $values ) );

	foreach ( $post_types as $post_type ) {
		$geodir_cpt_has_post[ $post_type ] = ! empty( $col ) && in_array( $post_type, $col ) ? true : false;
	}
}


/**
 * Get Current Post Type.
 *
 * @since 1.0.0
 * @since 1.6.18 Get the post type on map marker info request with preview mode.
 * @package GeoDirectory
 * @global object $wp_query WordPress Query object.
 * @global object $post WordPress Post object.
 * @global string $geodir_post_type The post type.
 * @return string The post type.
 */
function geodir_get_current_posttype() {
    global $wp_query, $post, $geodir_post_type;

    $geodir_post_type = get_query_var('post_type');

    if (geodir_is_page('add-listing') || geodir_is_page('preview')) {
        if (isset($_REQUEST['pid']) && $_REQUEST['pid'] != '')
            $geodir_post_type = get_post_type((int)$_REQUEST['pid']);
        elseif (isset($_REQUEST['listing_type']))
            $geodir_post_type = sanitize_text_field($_REQUEST['listing_type']);
    }

    if ((geodir_is_page('search') || geodir_is_page('author')) && isset($_REQUEST['stype']))
        $geodir_post_type = sanitize_text_field($_REQUEST['stype']);

    if (is_tax())
        $geodir_post_type = geodir_get_taxonomy_posttype();

    // Retrieve post type for map marker html ajax request on preview page.
    if (empty($geodir_post_type) && defined('DOING_AJAX') && !empty($post)) {
        if (!empty($post->post_type)) {
            $geodir_post_type = $post->post_type;
        } else if (!empty($post->listing_type)) {
            $geodir_post_type = $post->listing_type;
        }
    }

    $all_postypes = geodir_get_posttypes();
    $all_postypes = stripslashes_deep($all_postypes);

    if (is_array($all_postypes) && !in_array($geodir_post_type, $all_postypes))
        $geodir_post_type = '';

    if( defined( 'DOING_AJAX' ) && isset($_REQUEST['stype'])){
        $geodir_post_type = sanitize_text_field($_REQUEST['stype']);
    }

    // Set default past type on search page when stype is not set.
    if ( empty( $geodir_post_type ) && geodir_is_page( 'search' ) ) {
        $geodir_post_type = geodir_get_default_posttype();
    }

    /**
     * Filter the default CPT return.
     *
     * @since 1.6.9
     */
    return apply_filters('geodir_get_current_posttype',$geodir_post_type);
}

/**
 * Returns default sorting order of a post type.
 *
 * @since   1.0.0
 * @package GeoDirectory
 *
 * @param string $post_type The post type.
 *
 * @global object $wpdb     WordPress Database object.
 * @return bool|null|string Returns default sort results, when the post type is valid. Otherwise returns false.
 */
function geodir_get_posts_default_sort( $post_type ) {
	global $wpdb;

	// Check cache.
	$cache = wp_cache_get( "geodir_get_posts_default_sort_{$post_type}" );
	if ( $cache !== false ) {
		return $cache;
	}

	$default_sort = '';

	if ( $post_type != '' ) {
		$all_postypes = geodir_get_posttypes();

		if ( ! in_array( $post_type, $all_postypes ) ) {
			return false;
		}

		$field = $wpdb->get_row( $wpdb->prepare( "SELECT field_type, htmlvar_name, sort FROM " . GEODIR_CUSTOM_SORT_FIELDS_TABLE . " WHERE post_type = %s AND is_active = %d AND is_default = %d", array( $post_type, 1, 1 ) ) );

		if ( ! empty( $field ) ) {
			if ( $field->field_type == 'random' ) {
				$default_sort = 'random';
			} else {
				$default_sort = $field->htmlvar_name . '_' . $field->sort;
			}
		}

		/**
		 * Filter post default sort options.
		 *
		 * @since 2.2.4
		 *
		 * @param string $default_sort Default sort.
		 * @param string $post_type The post type.
		 * @param object $field Field object.
		 */
		$default_sort = apply_filters( 'geodir_get_posts_default_sort_by', $default_sort, $post_type, $field );
	}

	wp_cache_set("geodir_get_posts_default_sort_{$post_type}", $default_sort );

	return $default_sort;
}

/**
 * Returns sort options of a post type.
 *
 * @since   1.0.0
 * @package GeoDirectory
 *
 * @param string $post_type The post type.
 *
 * @global object $wpdb     WordPress Database object.
 * @return bool|mixed|void Returns sort results, when the post type is valid. Otherwise returns false.
 */
function geodir_get_sort_options( $post_type ) {
    global $wpdb;

    // check cache
    $cache = wp_cache_get("geodir_get_sort_options_{$post_type}");
    if($cache !== false){
        return $cache;
    }

    if ( $post_type != '' ) {
        $all_postypes = geodir_get_posttypes();

        if ( ! in_array( $post_type, $all_postypes ) ) {
            return false;
        }

        $sort_field_info = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . GEODIR_CUSTOM_SORT_FIELDS_TABLE . " WHERE post_type=%s AND is_active=%d AND field_type != 'address' AND tab_parent = '0' ORDER BY sort_order ASC", array(
            $post_type,
            1
        ) ) );

        /**
         * Filter post sort options.
         *
         * @since 1.0.0
         *
         * @param array $sort_field_info Unfiltered sort field array.
         * @param string $post_type      Post type.
         */
        $sort_field_info = apply_filters( 'geodir_get_sort_options', $sort_field_info, $post_type );

        wp_cache_set("geodir_get_sort_options_{$post_type}", $sort_field_info );

        return $sort_field_info;
    }
}

/**
 * Display list of sort options available in front end using dropdown.
 *
 * @since   1.0.0
 * @package GeoDirectory
 * @global object $wp_query WordPress Query object.
 * @todo this function can be made much simpler
 */
function geodir_display_sort_options( $post_type, $args = array() ) {
	global $wp_query;

	/**
	 * On search pages there should be no sort options, sorting is done by search criteria.
	 *
	 * @since 1.4.4
	 */
	if ( is_search() ) {
		return;
	}

	$sort_options_raw = geodir_get_sort_options( $post_type );

	$sort_options = array();

	if ( ! empty( $sort_options_raw ) && count( $sort_options_raw ) > 1 ) {
		foreach ( $sort_options_raw as $sort ) {
			$sort = stripslashes_deep( $sort );

			$sort->frontend_title = __( $sort->frontend_title, 'geodirectory' );

			if ( $sort->htmlvar_name == 'comment_count' ) {
				$sort->htmlvar_name = 'rating_count';
			}

			$sort_options[] = $sort;
		}
	}

	if ( ! empty( $sort_options ) ) {
		$design_style = geodir_design_style();

		$template = $design_style ? $design_style . "/loop/select-sort.php" : "loop/select-sort.php";

		echo geodir_get_template_html( $template, array(
			'sort_options' => $sort_options,
			'args' => $args
		) );
	}
}
add_action( 'geodir_extra_loop_actions', 'geodir_display_sort_options', 5, 2 );

function geodir_reorder_post_types() {
	$post_types = geodir_get_option( 'post_types', array() );

	if ( empty( $post_types ) ) {
		return;
	}

	$temp_post_types = array();
	$temp_keys = array();

	foreach ( $post_types as $post_type => $args ) {
		if ( ! empty( $temp_post_types ) ) {
			if ( empty( $args['listing_order'] ) || ( ! empty( $args['listing_order'] ) && array_key_exists( $args['listing_order'], $temp_post_types ) ) ) {
				$args['listing_order'] = max( array_keys( $temp_post_types ) ) + 1;
			}
		} else {
			if ( empty( $args['listing_order'] ) ) {
				$args['listing_order'] = 1;
			}
		}
		$temp_post_types[ $args['listing_order'] ] = $args;
		$temp_keys[ $args['listing_order'] ] = $post_type;
	}

	ksort( $temp_post_types );

	$save_post_types = array();
	foreach( $temp_post_types as $post_type => $args ) {
		$save_post_types[ $temp_keys[ $post_type ] ] = $args;
	}

	geodir_update_option( 'post_types', $save_post_types );
}

/**
 * Get the post type rewrite slug.
 *
 * @param string $post_type The post type being checked.
 * @param object $post_type_obj   The post type object.
 * @return string The post type slug.
 */
function geodir_cpt_permalink_rewrite_slug( $post_type, $post_type_obj = NULL ) {
	$slug = GeoDir_Post_types::get_rewrite_slug( $post_type, $post_type_obj );

	return apply_filters( 'geodir_cpt_permalink_rewrite_slug', $slug, $post_type, $post_type_obj );
}

/**
 * Add _search_title column to detail table.
 *
 * @param string $post_type The post type.
 * @return void.
 */
function geodir_check_column_search_title( $post_type ) {
	$table = geodir_db_cpt_table( $post_type );

	return geodir_add_column_if_not_exist( $table, '_search_title', "text NOT NULL AFTER `post_title`" );
}

/**
 * Generate keywords from post title.
 *
 * @param bool $force True to copy all search titles.
 *                    False to copy only empty search titles. Default False.
 * @return int No. of keywords generated.
 */
function geodir_generate_title_keywords( $force = false ) {
	$post_types = geodir_get_posttypes();

	$generated = 0;

	// Add _search_title column in details table.
	if ( ! empty( $post_types ) ) {
		foreach ( $post_types as $post_type ) {
			$generated += (int) geodir_cpt_generate_title_keywords( $post_type, $force );
		}
	}

	return $generated;
}

/**
 * Generate keywords from post title for post type.
 *
 * @param string $post_type The post type.
 * @param bool $force True to copy all search titles.
 *                    False to copy only empty search titles. Default False.
 * @return int No. of keywords generated.
 */
function geodir_cpt_generate_title_keywords( $post_type, $force = false ) {
	global $wpdb;

	// Check & add column _search_title.
	geodir_check_column_search_title( $post_type );

	$table = geodir_db_cpt_table( $post_type );

	// Blank existing search titles.
	if ( $force ) {
		$wpdb->query( "UPDATE `{$table}` SET _search_title = ''" );
	}

	$generated = 0;
	$results = $wpdb->get_results( "SELECT post_id, post_title, _search_title FROM `{$table}` WHERE `post_title` != '' AND `_search_title` = '' ORDER BY `post_id` ASC" );

	if ( ! empty( $results ) ) {
		foreach ( $results as $k => $row ) {
			// Format the data query arguments.
			$data = array(
				'_search_title' => geodir_sanitize_keyword( $row->post_title, $post_type )
			);

			// Format the where query arguments.
			$where = array(
				'post_id' => $row->post_id
			);

			$result = $wpdb->update( $table, $data, $where, array( '%s' ), array( '%d' ) );

			if ( $result ) {
				$generated++;
			}
		}
	}

	return $generated;
}

/**
 * Get the show in locations.
 *
 * @param string $field
 * @param string $field_type
 *
 * @return mixed|void
 */
function geodir_show_in_locations($field = '', $field_type=''){

    /*
	 * We wrap the key values in [] so we can search the DB easier with a LIKE query.
	 */
    $show_in_locations = array(
        "[detail]"    => __( "Details page sidebar", 'geodirectory' ),
        //"[moreinfo]"  => __( "More info tab", 'geodirectory' ),
        "[listing]"   => __( "Listings page", 'geodirectory' ),
        //"[owntab]"    => __( "Details page own tab", 'geodirectory' ),
        "[mapbubble]" => __( "Map bubble", 'geodirectory' ),
    );

    /**
     * Filter the locations array for where to display custom fields.
     *
     * @since 1.6.6
     *
     * @param array $show_in_locations The array of locations and descriptions.
     * @param object $field The field being displayed info.
     * @param string $field The type of field.
     */
    return apply_filters( 'geodir_show_in_locations', $show_in_locations, $field, $field_type );
}

/**
 * Check if a post type requires an address.
 *
 * @param string $post_type The post type to check.
 *
 * @return bool Whether the post type requires an address or not.
 * @since 2.3.39
 */
function geodir_cpt_requires_address( $post_type ) {
	global $geodir_cpt_requires_address;

	// check if we have done this before so we don't hit the DB again.
	if ( isset( $geodir_cpt_requires_address[ $post_type ] ) ) {
		return $geodir_cpt_requires_address[$post_type];
	}

	// set it as default true
	$result = true;

	if ( ! empty( $post_type ) ) {
		$address_field = geodir_get_field_infoby( 'htmlvar_name', 'address', $post_type, false );
		$result = isset($address_field['is_required']) && $address_field['is_required'];
		$geodir_cpt_requires_address[$post_type] = $result;
	}


	return $result;
}

/**
 * Get post type with rewrite slug options.
 *
 * @since 2.3.70
 *
 * @return array Post type options with slugs.
 */
function geodir_cpt_rewrite_slug_options() {
	$post_types = geodir_get_posttypes( 'array' );
	$options = array();

	if ( ! empty( $post_types ) ) {
		foreach ( $post_types as $key => $info ) {
			$options[ $key ] = $info['rewrite']['slug'];
		}
	}

	return apply_filters( 'geodir_cpt_rewrite_slug_options', $options );
}