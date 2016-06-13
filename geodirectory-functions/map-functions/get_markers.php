<?php
/**
 * Retrive markers data and marker info window to use in map
 *
 * @since 1.0.0
 * @package GeoDirectory
 */
 
if (isset($_REQUEST['ajax_action']) && $_REQUEST['ajax_action'] == 'homemap_catlist') {
    global $gd_session;
    $gd_post_type = sanitize_text_field($_REQUEST['post_type']);
    $gd_session->set('homemap_catlist_ptype', $gd_post_type);
    $post_taxonomy = geodir_get_taxonomies($gd_post_type);
    $map_canvas_name = sanitize_text_field($_REQUEST['map_canvas']);
    $child_collapse = (bool)$_REQUEST['child_collapse'];
    echo home_map_taxonomy_walker($post_taxonomy, 0, true, 0, $map_canvas_name, $child_collapse, true);
    die;
}

// Send the content-type header with correct encoding
header("Content-type: text/javascript; charset=utf-8");

if (isset($_REQUEST['ajax_action']) && $_REQUEST['ajax_action'] == 'cat') { // Retrives markers data for categories
    echo get_markers();
    exit;
} else if (isset($_REQUEST['ajax_action']) && $_REQUEST['ajax_action'] == 'info') { // Retrives marker info window html
    /**
     * @global object $wpdb WordPress Database object.
     * @global string $plugin_prefix Geodirectory plugin table prefix.
     * @global object $gd_session GeoDirectory Session object.
     */
    global $wpdb, $plugin_prefix, $gd_session;

    if ($_REQUEST['m_id'] != '') {
        $pid = (int)$_REQUEST['m_id'];
    } else {
        echo __('No marker data found', 'geodirectory');
        exit;
    }

    if (isset($_REQUEST['post_preview']) && $_REQUEST['post_preview'] != '' && $gd_ses_listing = $gd_session->get('listing')) {
        $post = (object)$gd_ses_listing;
        echo geodir_get_infowindow_html($post, $_REQUEST['post_preview']);
    } else {
        $geodir_post_type = get_post_type($pid);

        $table = $plugin_prefix . $geodir_post_type . '_detail';

        $sql = $wpdb->prepare("SELECT * FROM " . $table . " WHERE post_id = %d", array($pid));

        $postinfo = $wpdb->get_results($sql);

        $data_arr = array();

        if ($postinfo) {
            $srcharr = array("'", "/", "-", '"', '\\');
            $replarr = array("&prime;", "&frasl;", "&ndash;", "&ldquo;", '');

            foreach ($postinfo as $postinfo_obj) {
                echo geodir_get_infowindow_html($postinfo_obj);
            }
        }
    }
    exit;
}

/**
 * Retrive markers data to use in map
 *
 * @since 1.0.0
 * @since 1.5.7 Fixed non recurring events markers.
 * @since 1.6.1 Marker icons size added.
 *
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 * @global array  $geodir_cat_icons Array of the category icon urls.
 * @global array  $gd_marker_sizes Array of the marker icons sizes.
 * 
 * @return string
 */
function get_markers() {
    global $wpdb, $plugin_prefix, $geodir_cat_icons, $gd_marker_sizes;

    $search = '';
    $main_query_array;

    $srcharr = array("'", "/", "-", '"', '\\');
    $replarr = array("&prime;", "&frasl;", "&ndash;", "&ldquo;", '');

    $post_type = isset($_REQUEST['gd_posttype']) ? $_REQUEST['gd_posttype'] : 'gd_place';

    $map_cat_ids_array = array('0');
    $cat_find_array = array(" FIND_IN_SET(%d, pd." . $post_type . "category)");


    $field_default_cat = '';
    if (isset($_REQUEST['cat_id']) && $_REQUEST['cat_id'] != '') {
        $map_cat_arr = trim($_REQUEST['cat_id'], ',');

        if (!empty($map_cat_arr)) {
            $field_default_cat .= "WHEN (default_category IN (" . $map_cat_arr . ")) THEN default_category ";

            $map_cat_ids_array = explode(',', $map_cat_arr);
            $cat_find_array = array();
            foreach ($map_cat_ids_array as $cat_id) {
                $field_default_cat .= "WHEN (FIND_IN_SET($cat_id, `" . $post_type . "category`) > 0) THEN $cat_id ";
                $cat_find_array[] = " FIND_IN_SET(%d, pd." . $post_type . "category)";
                $main_query_array[] = $cat_id;
            }

        }
    }

    if (!empty($field_default_cat))
        $field_default_cat = '';

    if (!empty($cat_find_array))
        $search .= "AND (" . implode(' OR ', $cat_find_array) . ")";

    $main_query_array = $map_cat_ids_array;
  
    if (isset($_REQUEST['search']) && !empty($_REQUEST['search']) && $_REQUEST['search'] != __('Title', 'geodirectory')) {
        $search .= " AND p.post_title LIKE %s";
        $main_query_array[] = "%" . $_REQUEST['search'] . "%";
    }

    /**
     * Filter the marker query search SQL, values are replaces with %s or %d.
     *
     * @since 1.5.3
     *
     * @param string $search The SQL query for search/where.
     */
    $search = apply_filters('geodir_marker_search', $search);
    /**
     * Filter the marker query search SQL values %s and %d, this is an array of values.
     *
     * @since 1.5.3
     *
     * @param array $main_query_array The SQL query values for search/where.
     */
    $main_query_array = apply_filters('geodir_marker_main_query_array', $main_query_array);

    $gd_posttype = '';
    if (isset($_REQUEST['gd_posttype']) && $_REQUEST['gd_posttype'] != '') {
        $table = $plugin_prefix . $_REQUEST['gd_posttype'] . '_detail';
        $gd_posttype = " AND p.post_type = %s";
        $main_query_array[] = $_REQUEST['gd_posttype'];

    } else
        $table = $plugin_prefix . 'gd_place_detail';

    $join = ", " . $table . " AS pd ";

    /**
	 * Filter the SQL JOIN clause for the markers data
	 *
	 * @since 1.0.0
	 *
	 * @param string $join Row of SQL JOIN clause to join table.
	 */
	$join = apply_filters('geodir_home_map_listing_join', $join);
    
	/**
	 * Filter the searched fields for the markers data
	 *
	 * @since 1.0.0
	 *
	 * @param string $search Row of searched fields to use in WHERE clause.
	 */
	$search = apply_filters('geodir_home_map_listing_where', $search);
    $search = str_replace(array("'%", "%'"), array("'%%", "%%'"), $search);
    $cat_type = $post_type . 'category';
    if ($post_type == 'gd_event') {
        $event_select = ", pd.recurring_dates, pd.is_recurring";
    } else {
        $event_select = "";
    }

    $sql_select = 'SELECT pd.default_category, pd.' . $cat_type . ', pd.post_title, pd.post_id, pd.post_latitude, pd.post_longitude' . $event_select;
    /**
	 * Filter the SQL SELECT clause to retrive fields data
	 *
	 * @since 1.0.0
	 *
	 * @param string $sql_select Row of SQL SELECT clause.
	 */
	$select = apply_filters('geodir_home_map_listing_select', $sql_select);
	
	$groupby = " GROUP BY pd.post_id";
	/**
	 * Filter the SQL GROUP BY clause to retrive map markers data.
	 *
	 * @since 1.5.7
	 *
	 * @param string $groupby Row of SQL GROUP BY clause.
	 */
	$groupby = apply_filters('geodir_home_map_listing_groupby', $groupby);

    $catsql = $wpdb->prepare("$select $field_default_cat FROM " . $wpdb->posts . " as p" . $join . " WHERE p.ID = pd.post_id AND p.post_status = 'publish' " . $search . $gd_posttype . $groupby , $main_query_array);
    
	/**
	 * Filter the SQL query to retrive markers data
	 *
	 * @since 1.0.0
	 *
	 * @param string $catsql Row of SQL query.
	 * @param string $search Row of searched fields to use in WHERE clause.
	 */
	$catsql = apply_filters('geodir_home_map_listing_query', $catsql, $search);

    $catinfo = $wpdb->get_results($catsql);
	
    $cat_content_info = array();
    $content_data = array();
    $post_ids = array();

    /**
     * Called before marker data is processed into JSON.
     *
     * Called before marker data is processed into JSON, this action can be used to change the format or add/remove markers.
     *
     * @since 1.5.3
     * @param object $catinfo The posts object containing all marker data.
     * @see 'geodir_after_marker_post_process'
     */
    $catinfo = apply_filters('geodir_before_marker_post_process', $catinfo);

    /**
     * Called before marker data is processed into JSON.
     *
     * Called before marker data is processed into JSON, this action can be used to change the format or add/remove markers.
     *
     * @since 1.4.9
     * @param object $catinfo The posts object containing all marker data.
     * @see 'geodir_after_marker_post_process'
     */
    do_action('geodir_before_marker_post_process_action', $catinfo);

    // Sort any posts into a ajax array
    if (!empty($catinfo)) {
        $geodir_cat_icons = geodir_get_term_icon();
        global $geodir_date_format;

        $today = strtotime(date_i18n('Y-m-d'));
        
        foreach ($catinfo as $catinfo_obj) {
            $post_title = $catinfo_obj->post_title;
            
            if ($post_type == 'gd_event' && !empty($catinfo_obj->recurring_dates)) {
                $event_dates = '';
                $recurring_data = isset($catinfo_obj->recurring_dates) ? maybe_unserialize($catinfo_obj->recurring_dates) : array();
                
                $post_info = geodir_get_post_info($catinfo_obj->post_id);
                if (!empty($catinfo_obj->is_recurring) && !empty($recurring_data) && !empty($recurring_data['is_recurring']) && geodir_event_recurring_pkg($post_info)) {
                    $recurring_dates = explode(',', $recurring_data['event_recurring_dates']);
                    
                    if (!empty($recurring_dates)) {					
                        $e = 0;
                        foreach ($recurring_dates as $date) {
                            if (strtotime($date) >= $today) {
                                $event_dates .= ' :: ' . date_i18n($geodir_date_format, strtotime($date));
                                
                                $e++;
                                if ($e == 3) { // only show 3 event dates
                                    break;
                                }
                            }
                        }
                    }
                } else {
                    $start_date = !empty($recurring_data['event_start']) && $recurring_data['event_start'] != '0000-00-00 00:00:00' ? $recurring_data['event_start'] : '';
                    $end_date = !empty($recurring_data['event_end']) && $recurring_data['event_end'] != '0000-00-00 00:00:00' ? $recurring_data['event_end'] : $start_date;
                
                    if ($end_date != '' && strtotime($end_date) >= $today) {
                        $event_dates .= ' :: ' . date_i18n($geodir_date_format, strtotime($start_date)) .' -> ' . date_i18n($geodir_date_format, strtotime($end_date));
                    }
                }

                if (empty($event_dates)) {
                    continue;
                }
                
                $post_title .= $event_dates;
            }

            $icon = !empty($geodir_cat_icons) && isset($geodir_cat_icons[$catinfo_obj->default_category]) ? $geodir_cat_icons[$catinfo_obj->default_category] : '';
            $mark_extra = (isset($catinfo_obj->marker_extra)) ? $catinfo_obj->marker_extra : '';
            $title = str_replace($srcharr, $replarr, $post_title);
            
            if ($icon != '') {
                $gd_marker_sizes = empty($gd_marker_sizes) ? array() : $gd_marker_sizes;
                
                if (isset($gd_marker_sizes[$icon])) {
                    $icon_size = $gd_marker_sizes[$icon];
                } else {
                    $icon_size = geodir_get_marker_size($icon);
                    $gd_marker_sizes[$icon] = $icon_size;
                }               
            } else {
                $icon_size = array('w' => 36, 'h' => 45);
            }

            $content_data[] = '{"id":"' . $catinfo_obj->post_id . '","t": "' . $title . '","lt": "' . $catinfo_obj->post_latitude . '","ln": "' . $catinfo_obj->post_longitude . '","mk_id":"' . $catinfo_obj->post_id . '_' . $catinfo_obj->default_category . '","i":"' . $icon . '","w":"' . $icon_size['w'] . '","h":"' . $icon_size['h'] . '"'.$mark_extra.'}';
            $post_ids[] = $catinfo_obj->post_id;
        }
    }

    /**
     * Called after marker data is processed into JSON.
     *
     * Called after marker data is processed into JSON, this action can be used to change the format or add/remove markers.
     *
     * @since 1.4.9
     * @param array $content_data The array containing all markers in JSON format.
     * @param object $catinfo The posts object containing all marker data.
     * @see 'geodir_before_marker_post_process'
     */
    do_action('geodir_after_marker_post_process', $content_data, $catinfo);

    if (!empty($content_data)) {
        $cat_content_info[] = implode(',', $content_data);
    }

    $totalcount = count(array_unique($post_ids));

    if (!empty($cat_content_info)) {
        return '[{"totalcount":"' . $totalcount . '",' . substr(implode(',', $cat_content_info), 1) . ']';
    }
    else {
        return '[{"totalcount":"0"}]';
    }
}