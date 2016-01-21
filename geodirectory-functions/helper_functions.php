<?php
/**
 * Helper functions, this file contains functions made to make a developer's life easier.
 *
 * @since 1.4.6
 * @package GeoDirectory
 */

/**
 * Get the page ID of the add listing page.
 *
 * @package Geodirectory
 * @since 1.4.6
 * @return int|null Return the page ID if present or null if not.
 */
function geodir_add_listing_page_id(){
    $gd_page_id = get_option('geodir_add_listing_page');

    if (function_exists('icl_object_id')) {
        $gd_page_id =  icl_object_id($gd_page_id, 'page', true);
    }

    return $gd_page_id;
}

/**
 * Get the page ID of the add listing preview page.
 *
 * @package Geodirectory
 * @since 1.4.6
 * @return int|null Return the page ID if present or null if not.
 */
function geodir_preview_page_id(){
    $gd_page_id = get_option('geodir_preview_page');

    if (function_exists('icl_object_id')) {
        $gd_page_id =  icl_object_id($gd_page_id, 'page', true);
    }

    return $gd_page_id;
}

/**
 * Get the page ID of the add listing success page.
 *
 * @package Geodirectory
 * @since 1.4.6
 * @return int|null Return the page ID if present or null if not.
 */
function geodir_success_page_id(){
    $gd_page_id = get_option('geodir_success_page');

    if (function_exists('icl_object_id')) {
        $gd_page_id =  icl_object_id($gd_page_id, 'page', true);
    }

    return $gd_page_id;
}

/**
 * Get the page ID of the add location page.
 *
 * @package Geodirectory
 * @since 1.4.6
 * @return int|null Return the page ID if present or null if not.
 */
function geodir_location_page_id(){
    $gd_page_id = get_option('geodir_location_page');

    if (function_exists('icl_object_id')) {
        $gd_page_id =  icl_object_id($gd_page_id, 'page', true);
    }

    return $gd_page_id;
}

/**
 * Get the page ID of the GD home page.
 *
 * @package Geodirectory
 * @since 1.5.4
 * @return int|null Return the page ID if present or null if not.
 */
function geodir_home_page_id(){
    $gd_page_id = get_option('geodir_home_page');

    if (function_exists('icl_object_id')) {
        $gd_page_id =  icl_object_id($gd_page_id, 'page', true);
    }

    return $gd_page_id;
}

/**
 * Get the page ID of the info page.
 *
 * @package Geodirectory
 * @since 1.5.3
 * @return int|null Return the page ID if present or null if not.
 */
function geodir_info_page_id(){
    $gd_page_id = get_option('geodir_info_page');

    if (function_exists('icl_object_id')) {
        $gd_page_id =  icl_object_id($gd_page_id, 'page', true);
    }

    return $gd_page_id;
}

/**
 * Get the page ID of the login page.
 *
 * @package Geodirectory
 * @since 1.5.3
 * @return int|null Return the page ID if present or null if not.
 */
function geodir_login_page_id(){
    $gd_page_id = get_option('geodir_login_page');

    if (function_exists('icl_object_id')) {
        $gd_page_id =  icl_object_id($gd_page_id, 'page', true);
    }

    return $gd_page_id;
}


/**
 * Get the page ID of the login page.
 *
 * @package Geodirectory
 * @since 1.5.3
 * @return int|null Return the page ID if present or null if not.
 */
function geodir_login_url($args=array()){
    $gd_page_id = get_option('geodir_login_page');

    if (function_exists('icl_object_id')) {
        $gd_page_id =  icl_object_id($gd_page_id, 'page', true);
    }

    if (function_exists('geodir_location_geo_home_link')) {
        remove_filter('home_url', 'geodir_location_geo_home_link', 100000);
    }
    $home_url = get_home_url();
    if (function_exists('geodir_location_geo_home_link')) {
        add_filter('home_url', 'geodir_location_geo_home_link', 100000, 2);
    }

    if($gd_page_id){
        $post = get_post($gd_page_id);
        $slug = $post->post_name;
        //$login_url = get_permalink($gd_page_id );// get_permalink can only be user after theme-Setup hook, any earlier and it errors
        $login_url = trailingslashit($home_url)."$slug/";
    }else{
        $login_url = trailingslashit($home_url)."?geodir_signup=true";
    }

    if($args){
        $login_url = add_query_arg($args,$login_url );
    }

    /**
     * Filter the GeoDirectory login page url.
     *
     * This filter can be used to change the GeoDirectory page url.
     *
     * @since 1.5.3
     * @package GeoDirectory
     * @param string $login_url The url of the login page.
     * @param array $args The array of query args used.
     * @param int $gd_page_id The page id of the GD login page.
     */
    return apply_filters('geodir_login_url',$login_url,$args,$gd_page_id);
}

/**
 * Returns info page url
 *
 * @package Geodirectory
 * @since 1.5.4
 * @return string Info page url.
 */
function geodir_info_url($args=array()){
    $gd_page_id = get_option('geodir_info_page');

    if (function_exists('icl_object_id')) {
        $gd_page_id =  icl_object_id($gd_page_id, 'page', true);
    }

    if (function_exists('geodir_location_geo_home_link')) {
        remove_filter('home_url', 'geodir_location_geo_home_link', 100000);
    }
    $home_url = get_home_url();
    if (function_exists('geodir_location_geo_home_link')) {
        add_filter('home_url', 'geodir_location_geo_home_link', 100000, 2);
    }

    if($gd_page_id){
        $post = get_post($gd_page_id);
        $slug = $post->post_name;
        //$login_url = get_permalink($gd_page_id );// get_permalink can only be user after theme-Setup hook, any earlier and it errors
        $info_url = trailingslashit($home_url)."$slug/";
    }else{
        $info_url = trailingslashit($home_url);
    }

    if($args){
        $info_url = add_query_arg($args,$info_url );
    }

    return $info_url;
}

/**
 * Converts string to title case.
 *
 * This function converts string to title case. Ex: hello world -> Hello World.
 * When mbstring php extension available this function supports all unicode characters.
 *
 * @package Geodirectory
 * @since 1.5.4
 * @param string $string String to convert.
 * @param string $charset Character set to use for conversion.
 * @return string Returns converted string.
 */
function geodir_ucwords($string, $charset='UTF-8') {
    if (function_exists('mb_convert_case')) {
        return mb_convert_case($string, MB_CASE_TITLE, $charset);
    } else {
        return ucwords($string);
    }
}

/**
 * Converts string to lower case.
 *
 * This function converts string to lower case. Ex: HelLO WorLd -> hello world.
 * When mbstring php extension available this function supports all unicode characters.
 *
 * @package Geodirectory
 * @since 1.5.4
 * @param string $string String to convert.
 * @param string $charset Character set to use for conversion.
 * @return string Returns converted string.
 */
function geodir_strtolower($string, $charset='UTF-8') {
    if (function_exists('mb_convert_case')) {
        return mb_convert_case($string, MB_CASE_LOWER, $charset);
    } else {
        return strtolower($string);
    }
}

/**
 * Converts string to upper case.
 *
 * This function converts string to upper case. Ex: HelLO WorLd -> HELLO WORLD.
 * When mbstring php extension available this function supports all unicode characters.
 *
 * @package Geodirectory
 * @since 1.5.4
 * @param string $string String to convert.
 * @param string $charset Character set to use for conversion.
 * @return string Returns converted string.
 */
function geodir_strtoupper($string, $charset='UTF-8') {
    if (function_exists('mb_convert_case')) {
        return mb_convert_case($string, MB_CASE_UPPER, $charset);
    } else {
        return strtoupper($string);
    }
}

/**
 * Returns parsed url and title.
 *
 * This function converts string to url and title if there is "|" separator used in url.
 * Ex: "http://wpgeodirectory.com|GeoDirectory" will return array( url => http://wpgeodirectory.com, label => GeoDirectory ).
 *
 * @package Geodirectory
 * @since 1.5.7
 * @param string $url The website url.
 * @param bool $formatted True if returns formatted url. False if not. Default true.
 * @return array Parsed url and title.
 */
function geodir_parse_custom_field_url($url, $formatted = true) {
	if ($url == '' || !is_string($url)) {
		return NULL;
	}
	$original_url = $url;
	
	$url = stripcslashes($url);
	$parts = explode('|', $url, 2);
	
	$url = trim($parts[0]);
	if ($formatted && $url != '') {
		$url = str_replace( ' ', '%20', $url );
		$url = preg_replace('|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\[\]\\x80-\\xff]|i', '', $url);
		
		if (0 !== stripos($url, 'mailto:')) {
			$strip = array('%0d', '%0a', '%0D', '%0A');
			$url = _deep_replace($strip, $url);
		}
		
		$url = str_replace(';//', '://', $url);
		
		if (strpos($url, ':') === false && ! in_array($url[0], array('/', '#', '?')) && !preg_match('/^[a-z0-9-]+?\.php/i', $url)) {
			$url = 'http://' . $url;
		}
		
		$url = wp_kses_normalize_entities($url);
		$url = str_replace('&amp;', '&#038;', $url);
		$url = str_replace("'", '&#039;', $url);
	}
	
	$return = array();
	$return['url'] = $url;
	if (!empty($parts[1]) && trim($parts[1]) != '') {
		$return['label'] = trim($parts[1]);
	}

	return $return;
}

/**
 * Set parent categories to fix categoires tree structure.
 *
 * @since 1.5.7
 * @package GeoDirectory
 *
 * @param array $request_info Listing request info.
 * @return array Modified listing request info.
 */
function geodir_attach_parent_categories($request_info) {
	if (!empty($request_info['post_category']) && is_array($request_info['post_category'])) {
		foreach ($request_info['post_category'] as $taxomony => $term_ids) {			
			$attach_term_ids = array();
			
			if (!empty($term_ids) && is_array($term_ids) && taxonomy_exists($taxomony) && strpos($taxomony, 'category') !== false) {
				$attach_term_ids = geodir_add_parent_terms($term_ids, $taxomony);
				
				if (!empty($attach_term_ids)) {
					if (!isset($request_info['post_default_category'])) {
						$request_info['post_default_category'] = $attach_term_ids[0];
					}
					$request_info['post_category'][$taxomony] = $attach_term_ids;
				}
			}
		}
	}
	
	return $request_info;
}

/**
 * Add the parent terms in current terms.
 *
 * @since 1.5.7
 * @package GeoDirectory
 *
 * @param int|array $term_ids Term id or Terms array.
 * @param string $taxomony Category taxonomy of post type.
 * @return array Modified term ids.
 */
function geodir_add_parent_terms($term_ids, $taxomony) {	
	if (is_int($term_ids)) {
		$term_ids = array($term_ids);
	}
	
	$parent_terms = array();
	
	foreach ($term_ids as $term_id) {
		$parent_terms[] = $term_id;
		$term_parents = geodir_get_category_parents($term_id, $taxomony, $parent_terms);
		
		if (!empty($term_parents)) {
			$parent_terms = array_merge($parent_terms, $term_parents);
		}
	}

	return $parent_terms;
}

/**
 * Get the parent categories of current id.
 *
 * @since 1.5.7
 * @package GeoDirectory
 *
 * @param int $id Category id.
 * @param string $taxomony Category taxonomy of post type.
 * @param array $visited Array of category ids already included.
 * @param array $parents Array of category ids.
 * @return array Category ids.
 */
function geodir_get_category_parents($id, $taxomony, $visited = array(), $parents = array()) {
	$parent = get_term($id, $taxomony);
	if (is_wp_error($parent)) {
		return $parents;
	}

	if ($parent->parent && ($parent->parent != $parent->term_id) && !in_array($parent->parent, $visited)) {
		$visited[] = $parent->parent;
		$parents[] = $parent->parent;
		$parents = geodir_get_category_parents($parent->parent, $taxomony, $visited, $parents);
	}

	return $parents;
}