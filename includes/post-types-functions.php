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
 * @return boll True if given post type is GD post type, otherwise False.
 */
function geodir_is_gd_post_type( $post_type ) {
    global $gd_is_post_type;
    
    if ( empty( $post_type ) ) {
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

function geodir_post_type_object( $post_type ) {
    if ( geodir_is_gd_post_type( $post_type ) ) {
        $post_types = geodir_get_posttypes( 'object' );
        
        $post_type_obj = !empty( $post_types->{$post_type} ) ? $post_types->{$post_type} : NULL;
    } else {
        $post_type_obj = get_post_type_object( $post_type );
    }
    
    return $post_type_obj;
}

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
 * Get and array of CPTs with opening hours feature enabled.
 * 
 * @return mixed|void
 */
function geodir_opening_hours_allowed_post_types() {
    $allowed_post_types = array();
    $post_types = geodir_get_posttypes( 'array' );
    if ( !empty( $post_types ) ) {
        foreach ( $post_types as $cpt => $post_type ) {
            if ( ! empty( $post_type['opening_hours'] ) ) {
                $allowed_post_types[] = $cpt;
            }
        }
    }

    return apply_filters( 'geodir_opening_hours_allowed_post_types', $allowed_post_types  );
}

function geodir_add_listing_default_post_type() {
    $post_types = geodir_add_listing_allowed_post_types();

    $post_type = !empty( $post_types ) && is_array( $post_types ) ? $post_types[0] : '';

    return apply_filters( 'geodir_add_listing_default_post_type', $post_type );
}

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
 * @global object $gd_session GeoDirectory Session object.
 *
 * @param string $link The post link.
 * @param string $post_type The post type.
 * @return string The modified link.
 */
function geodir_posttype_link($link, $post_type) {
    global $geodir_add_location_url, $post, $gd_session;

    $location_terms = array();

    if (in_array($post_type, geodir_get_posttypes())) {
        if (geodir_get_option('geodir_add_location_url') && $gd_session->get('gd_multi_location') == 1) {
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
 * @since 1.0.0
 * @since 1.6.16 New $translate parameter added.
 * @package GeoDirectory
 * @param string $post_type The post type.
 * @param bool $echo Prints the label when set to true.
 * @param bool $translate Returns translated label if True. DefauT false.
 * @return void|string Label.
 */
function get_post_type_singular_label($post_type, $echo = false, $translate = false) {
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
 * @since 1.0.0
 * @since 1.6.16 New $translate parameter added.
 * @package GeoDirectory
 * @param string $post_type The post type.
 * @param bool $echo Prints the label when set to true.
 * @param bool $translate Returns translated label if True. DefauT false.
 * @return void|string Label.
 */
function get_post_type_plural_label($post_type, $echo = false, $translate = false) {
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
 * @package GeoDirectory
 * @global object $post WordPress Post object.
 * @global int $post_ID WordPress Post ID.
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

/**
 * Get Custom Post Type info.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @param string $post_type The post type.
 * @return bool|array Post type details.
 */
function geodir_get_posttype_info($post_type = '')
{
    $post_types = array();
    $post_types = geodir_get_posttypes('array');
    $post_types = stripslashes_deep($post_types);
    if (!empty($post_types) && $post_type != '') {
        return $post_types[$post_type];
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
function geodir_get_default_posttype()
{
    $post_types = apply_filters( 'geodir_get_default_posttype', geodir_get_posttypes( 'object' ) );

    $stype = false;
    foreach ( $post_types as $post_type => $info ) {
        global $wpdb;
        $has_posts = $wpdb->get_row( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = %s AND post_status='publish' LIMIT 1", $post_type ) );
        if ( $has_posts ) {
            $stype = $post_type; break;
        }
    }

    if(!$stype){
        $stype = 'gd_place';
    }

    return $stype;
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

    // Retrive post type for map marker html ajax request on preview page.
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

    if ( $post_type != '' ) {

        $all_postypes = geodir_get_posttypes();

        if ( ! in_array( $post_type, $all_postypes ) ) {
            return false;
        }

        $sort_field_info = $wpdb->get_var( $wpdb->prepare( "select default_order from " . GEODIR_CUSTOM_SORT_FIELDS_TABLE . " where	post_type= %s and is_active=%d and is_default=%d", array(
            $post_type,
            1,
            1
        ) ) );

        if ( ! empty( $sort_field_info ) ) {
            return $sort_field_info;
        }

    }

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

    if ( $post_type != '' ) {
        $all_postypes = geodir_get_posttypes();

        if ( ! in_array( $post_type, $all_postypes ) ) {
            return false;
        }

        $sort_field_info = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . GEODIR_CUSTOM_SORT_FIELDS_TABLE . " WHERE post_type=%s AND is_active=%d AND (sort_asc=1 || sort_desc=1 || field_type='random') AND field_type != 'address' ORDER BY sort_order ASC", array(
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
        return apply_filters( 'geodir_get_sort_options', $sort_field_info, $post_type );
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
function geodir_display_sort_options() {
    global $wp_query;

    /**
     * On search pages there should be no sort options, sorting is done by search criteria.
     *
     * @since 1.4.4
     */
    if ( is_search() ) {
        return;
    }

    $sort_by = '';

    if ( isset( $_REQUEST['sort_by'] ) ) {
        $sort_by = $_REQUEST['sort_by'];
    }

    $gd_post_type = geodir_get_current_posttype();

    $sort_options = geodir_get_sort_options( $gd_post_type );


    $sort_field_options = '';

    if ( ! empty( $sort_options ) ) {
        foreach ( $sort_options as $sort ) {
            $sort = stripslashes_deep( $sort ); // strip slashes

            $label = __( $sort->frontend_title, 'geodirectory' );

            if ( $sort->field_type == 'random' ) {
                $key = $sort->field_type;
                ( $sort_by == $key || ( $sort->is_default == '1' && ! isset( $_REQUEST['sort_by'] ) ) ) ? $selected = 'selected="selected"' : $selected = '';
                $sort_field_options .= '<option ' . $selected . ' value="' . esc_url( add_query_arg( 'sort_by', $key ) ) . '">' . __( $label, 'geodirectory' ) . '</option>';
            }

            if ( $sort->htmlvar_name == 'comment_count' ) {
                $sort->htmlvar_name = 'rating_count';
            }

            if ( $sort->sort_asc ) {
                $key   = $sort->htmlvar_name . '_asc';
                $label = $sort->frontend_title;
                if ( $sort->asc_title ) {
                    $label = $sort->asc_title;
                }
                ( $sort_by == $key || ( $sort->is_default == '1' && $sort->default_order == $key && ! isset( $_REQUEST['sort_by'] ) ) ) ? $selected = 'selected="selected"' : $selected = '';
                $sort_field_options .= '<option ' . $selected . ' value="' . esc_url( add_query_arg( 'sort_by', $key ) ) . '">' . __( $label, 'geodirectory' ) . '</option>';
            }

            if ( $sort->sort_desc ) {
                $key   = $sort->htmlvar_name . '_desc';
                $label = $sort->frontend_title;
                if ( $sort->desc_title ) {
                    $label = $sort->desc_title;
                }
                ( $sort_by == $key || ( $sort->is_default == '1' && $sort->default_order == $key && ! isset( $_REQUEST['sort_by'] ) ) ) ? $selected = 'selected="selected"' : $selected = '';
                $sort_field_options .= '<option ' . $selected . ' value="' . esc_url( add_query_arg( 'sort_by', $key ) ) . '">' . __( $label, 'geodirectory' ) . '</option>';
            }

        }
    }

    if ( $sort_field_options != '' ) {

        ?>

        <div class="geodir-tax-sort">

            <select name="sort_by" id="sort_by" onchange="javascript:window.location=this.value;">

                <option
                    value="<?php echo esc_url( add_query_arg( 'sort_by', '' ) ); ?>" <?php if ( $sort_by == '' ) {
                    echo 'selected="selected"';
                } ?>><?php _e( 'Sort By', 'geodirectory' ); ?></option><?php

                echo $sort_field_options; ?>

            </select>

        </div>
        <?php

    }

}
