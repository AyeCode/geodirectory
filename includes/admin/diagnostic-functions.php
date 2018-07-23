<?php
// @todo do we want to reuse these or build new ones?





/**
 * Displays 'GD Diagnostic Tools' page.
 *
 * @since 1.0.0
 * @package GeoDirectory
 */
function geodir_diagnostic_tools_setting_page()
{
    ?>
    <div class="inner_content_tab_main">
        <div class="gd-content-heading">


            <h3><?php _e('GD Diagnostic Tools', 'geodirectory');?></h3>
            <style>
                .gd-tools-table {
                    width: 100%;
                    border: 1px solid #666;
                }

                .gd-tool-results, .gd-tool-results td {
                    padding: 0px;
                }

                .gd-tool-results-remove {
                    float: right;
                    margin-top: 10px;
                }
            </style>
            <table class="form-table gd-tools-table">
                <tbody>
                <tr>
                    <td><strong><?php _e('Tool', 'geodirectory');?></strong></td>
                    <td><strong><?php _e('Description', 'geodirectory');?></strong></td>
                    <td><strong><?php _e('Action', 'geodirectory');?></strong></td>
                </tr>


                <tr>
                    <td><?php _e('GD pages check', 'geodirectory');?></td>
                    <td>
                        <small><?php _e('Checks if the GD pages are installed correctly or not.', 'geodirectory');?></small>
                    </td>
                    <td>
                        <input type="button" value="<?php _e('Run', 'geodirectory');?>"
                               class="button-primary geodir_diagnosis_button" data-diagnose="default_pages"/>
                    </td>
                </tr>


                <tr>
                    <td><?php _e('Multisite DB conversion check', 'geodirectory');?></td>
                    <td>
                        <small><?php _e('Checks if the GD database tables have been converted to use multisite correctly.', 'geodirectory');?></small>
                    </td>
                    <td><input type="button" value="<?php _e('Run', 'geodirectory');?>"
                               class="button-primary geodir_diagnosis_button" data-diagnose="multisite_conversion"/>
                    </td>
                </tr>

                <tr>
                    <td><?php _e('Ratings check', 'geodirectory');?></td>
                    <td>
                        <small><?php _e('Checks ratings for correct location and content settings', 'geodirectory');?></small>
                    </td>
                    <td><input type="button" value="<?php _e('Run', 'geodirectory');?>"
                               class="button-primary geodir_diagnosis_button" data-diagnose="ratings"/>
                    </td>
                </tr>

                <tr>
                    <?php
                    $l_count = geodir_total_listings_count();
                    $step_max_items = geodir_get_diagnose_step_max_items();
                    if ($l_count > $step_max_items) {
                        $multiple = 'data-step="1"';
                    } else {
                        $multiple = "";
                    }
                    ?>
                    <td><?php _e('Sync GD tags', 'geodirectory');?></td>
                    <td>
                        <small><?php _e('This tool can be used when tags are showing in the backend but missing from the front end.', 'geodirectory');?></small>
                        <?php
                        if ($l_count > $step_max_items) {
                            ?>
                            <table id="tags_sync_sub_table" class="widefat" style="display: none">
                                <?php
                                $all_postypes = geodir_get_posttypes('array');

                                if (!empty($all_postypes)) {
                                    foreach ($all_postypes as $key => $value) {
                                        ?>
                                        <tr id="tags_sync_<?php echo $key; ?>">
                                            <td>
                                                <?php echo $value['labels']['name']; ?>
                                            </td>
                                            <td>
                                                <input type="button" value="<?php _e('Run', 'geodirectory');?>"
                                                       class="button-primary geodir_diagnosis_button" data-ptype="<?php echo $key; ?>" data-diagnose="tags_sync"/>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                }
                                ?>
                            </table>
                            <?php
                        }
                        ?>
                    </td>
                    <td>
                        <input type="button" value="<?php _e('Run', 'geodirectory');?>"
                               class="button-primary geodir_diagnosis_button" <?php echo $multiple; ?> data-diagnose="tags_sync"/>

                    </td>
                </tr>

                <tr>
                    <td><?php _e('Sync GD Categories', 'geodirectory');?></td>
                    <td>
                        <small><?php _e('This tool can be used when categories are missing from the details table but showing in other places in the backend (only checks posts with missing category info in details table)', 'geodirectory');?></small>
                    </td>
                    <td><input type="button" value="<?php _e('Run', 'geodirectory');?>"
                               class="button-primary geodir_diagnosis_button" data-diagnose="cats_sync"/>
                    </td>
                </tr>


                <tr>
                    <td><?php _e('Clear all GD version numbers', 'geodirectory');?></td>
                    <td>
                        <small><?php _e('This tool will clear all GD version numbers so any upgrade functions will run again.', 'geodirectory');?></small>
                    </td>
                    <td><input type="button" value="<?php _e('Run', 'geodirectory');?>"
                               class="button-primary geodir_diagnosis_button" data-diagnose="version_clear"/>
                    </td>
                </tr>
                <tr>
                    <td><?php _e('Load custom fields translation', 'geodirectory');?></td>
                    <td>
                        <small><?php _e('This tool will load strings from the database into a file to translate via po editor.Ex: custom fields', 'geodirectory');?></small>
                    </td>
                    <td>
                        <input type="button" value="<?php _e('Run', 'geodirectory');?>" class="button-primary geodir_diagnosis_button" data-diagnose="load_db_language"/>
                    </td>
                </tr>
                <tr>
                    <td><?php _e('Reload Countries table', 'geodirectory');?></td>
                    <td>
                        <small><?php _e('This tool will drop and re-add the countries table, it is meant to refresh the list when countries are added/removed, if you have duplicate country problems you should merge those first or you could have orphaned posts.', 'geodirectory');?></small>
                    </td>
                    <td>
                        <input type="button" value="<?php _e('Run', 'geodirectory');?>" class="button-primary geodir_diagnosis_button" data-diagnose="reload_db_countries"/>
                    </td>
                </tr>
                <?php
                /**
                 * Allows you to add more setting to the GD>Tools settings page.
                 *
                 * Called after the last setting on the GD>Tools page.
                 * @since 1.0.0
                 */
                do_action('geodir_diagnostic_tool');?>

                </tbody>
            </table>

        </div>
    </div>
    <?php
}



/* Ajax Handler Start */
add_action('wp_ajax_geodir_admin_ajax', "geodir_admin_ajax_handler");

/**
 * Handles admin ajax.
 *
 * @since 1.0.0
 * @package GeoDirectory
 */
function geodir_admin_ajax_handler()
{
    if (isset($_REQUEST['geodir_admin_ajax_action']) && $_REQUEST['geodir_admin_ajax_action'] != '') {
        $geodir_admin_ajax_action = $_REQUEST['geodir_admin_ajax_action'];
        $diagnose_this = "";
        switch ($geodir_admin_ajax_action) {
            case 'diagnosis' :
                if (isset($_REQUEST['diagnose_this']) && $_REQUEST['diagnose_this'] != '') {
                    $diagnose_this = sanitize_text_field($_REQUEST['diagnose_this']);
                    call_user_func('geodir_diagnose_' . $diagnose_this);

                }
                exit();
                break;

            case 'diagnosis-fix' :
                if (isset($_REQUEST['diagnose_this']) && $_REQUEST['diagnose_this'] != '')
                    $diagnose_this = sanitize_text_field($_REQUEST['diagnose_this']);
                call_user_func('geodir_diagnose_' . $diagnose_this);
                exit();
                break;
        }
    }
    exit();
}



/**
 * Syncs when tags are showing in the backend but missing from the front end.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 */
function geodir_diagnose_tags_sync()
{
    global $wpdb, $plugin_prefix;
    $fix = isset($_POST['fix']) ? true : false;
    $step = isset($_POST['step']) ? strip_tags(esc_sql($_POST['step'])) : 0;
    $step_max_items = geodir_get_diagnose_step_max_items();
    $offset = (int) $step * $step_max_items;
    $ptype = isset($_POST['ptype']) ? strip_tags(esc_sql($_POST['ptype'])) : false;

    $total_listings = geodir_total_listings_count();
    $total_ptype_listings = 0;
    if ($ptype) {
        $total_ptype_listings = geodir_total_listings_count($ptype);
    }
    $max_step = ceil($total_ptype_listings / $step_max_items) - 1;

    //if($fix){echo 'true';}else{echo 'false';}
    $is_error_during_diagnose = false;
    $output_str = '';

    if ($ptype && !empty($ptype) && $total_listings > $step_max_items) {
        $stepped_process = true;
    } else {
        $stepped_process = false;
    }

    if ($stepped_process) {
        $sql = $wpdb->prepare( "SELECT * FROM " . $wpdb->prefix . "geodir_" . $ptype . "_detail LIMIT %d OFFSET %d", $step_max_items, $offset );
        $posts = $wpdb->get_results( $sql );

        if (!empty($posts)) {

            foreach ($posts as $p) {
                $p->post_type = $ptype;
                $raw_tags = wp_get_object_terms($p->post_id, $p->post_type . '_tags', array('fields' => 'names'));
                if (empty($raw_tags)) {
                    $post_tags = '';
                } else {
                    $post_tags = implode(",", $raw_tags);
                }
                $tablename = $plugin_prefix . $p->post_type . '_detail';
                $wpdb->query($wpdb->prepare("UPDATE " . $tablename . " SET post_tags=%s WHERE post_id =%d", $post_tags, $p->post_id));

            }
            if ($step >= $max_step) {
                $output_str = "done";
            } else {
                $output_str = $step + 1;
            }
        }

    } else {
        $all_postypes = geodir_get_posttypes();

        if (!empty($all_postypes)) {
            foreach ($all_postypes as $key) {
                // update each GD CPT
                $posts = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "geodir_" . $key . "_detail");

                if (!empty($posts)) {

                    foreach ($posts as $p) {
                        $p->post_type = $key;
                        $raw_tags = wp_get_object_terms($p->post_id, $p->post_type . '_tags', array('fields' => 'names'));
                        if (empty($raw_tags)) {
                            $post_tags = '';
                        } else {
                            $post_tags = implode(",", $raw_tags);
                        }
                        $tablename = $plugin_prefix . $p->post_type . '_detail';
                        $wpdb->query($wpdb->prepare("UPDATE " . $tablename . " SET post_tags=%s WHERE post_id =%d", $post_tags, $p->post_id));

                    }
                    $output_str .= "<li>" . $key . __(': Done', 'geodirectory') . "</li>";
                }

            }

        }
    }


    if ($is_error_during_diagnose) {
        $info_div_class = "geodir_problem_info";
        $fix_button_txt = "<input type='button' value='" . __('Fix', 'geodirectory') . "' class='button-primary geodir_fix_diagnostic_issue' data-diagnostic-issue='ratings' />";
    } else {
        $info_div_class = "geodir_noproblem_info";
        $fix_button_txt = '';
    }

    if ($stepped_process) {
        $percent = ($step/$max_step) * 100;
        if ($output_str == 'done') {
            echo $output_str;
        } else {
            $output = array(
                'step' => $output_str,
                'percent' => $percent
            );
            echo json_encode($output);
        }
    } else {
        echo "<ul class='$info_div_class'>";
        echo $output_str;
        echo $fix_button_txt;
        echo "</ul>";
    }
}

/**
 * Syncs when categories are missing from the details table but showing in other places in the backend.
 *
 * Only checks posts with missing category info in details table.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 */
function geodir_diagnose_cats_sync()
{
    global $wpdb, $plugin_prefix;
    $fix = isset($_POST['fix']) ? true : false;

    //if($fix){echo 'true';}else{echo 'false';}
    $is_error_during_diagnose = false;
    $output_str = '';


    $all_postypes = geodir_get_posttypes();

    if (!empty($all_postypes)) {
        foreach ($all_postypes as $key) {
            // update each GD CTP
            $posts = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "geodir_" . $key . "_detail d WHERE d." . $key . "category='' ");

            if (!empty($posts)) {

                foreach ($posts as $p) {
                    $p->post_type = $key;
                    $raw_cats = wp_get_object_terms($p->post_id, $p->post_type . 'category', array('fields' => 'ids'));

                    if (empty($raw_cats)) {
                        $post_category = get_post_meta($p->post_id, 'post_category', true);

                        if (!empty($post_category) && !empty($post_category[$p->post_type . 'category'])) {
                            $post_category[$p->post_type . 'category'] = str_replace("d:", "", $post_category[$p->post_type . 'category']);
                            foreach (explode(",", $post_category[$p->post_type . 'category']) as $cat_part) {
                                if (is_numeric($cat_part)) {
                                    $raw_cats[] = (int)$cat_part;
                                }
                            }

                        }

                        if (!empty($raw_cats)) {
                            $term_taxonomy_ids = wp_set_object_terms($p->post_id, $raw_cats, $p->post_type . 'category');

                        }

                    }


                    if (empty($raw_cats)) {
                        $post_cats = '';
                    } else {
                        $post_cats = ',' . implode(",", $raw_cats) . ',';
                    }
                    $tablename = $plugin_prefix . $p->post_type . '_detail';
                    $wpdb->query($wpdb->prepare("UPDATE " . $tablename . " SET " . $p->post_type . "category=%s WHERE post_id =%d", $post_cats, $p->post_id));
                }

            }
            $output_str .= "<li>" . $key . __(': Done', 'geodirectory') . "</li>";

        }

    }

    if ($is_error_during_diagnose) {
        $info_div_class = "geodir_problem_info";
        $fix_button_txt = "<input type='button' value='" . __('Fix', 'geodirectory') . "' class='button-primary geodir_fix_diagnostic_issue' data-diagnostic-issue='ratings' />";
    } else {
        $info_div_class = "geodir_noproblem_info";
        $fix_button_txt = '';
    }
    echo "<ul class='$info_div_class'>";
    echo $output_str;
    echo $fix_button_txt;
    echo "</ul>";

}

/**
 * Clears all GD version numbers so any upgrade functions will run again.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 */
function geodir_diagnose_version_clear()
{
    global $wpdb, $plugin_prefix;
    $fix = isset($_POST['fix']) ? true : false;

    //if($fix){echo 'true';}else{echo 'false';}
    $is_error_during_diagnose = false;
    $output_str = '';


    $gd_arr = array('GeoDirectory' => 'geodirectory_db_version',
        'Payment Manager' => 'geodir_payments_db_version',
        'GeoDirectory Framework' => 'gdf_db_version',
        'Advanced Search' => 'geodiradvancesearch_db_version',
        'Review Rating Manager' => 'geodir_reviewratings_db_version',
        'Claim Manager' => 'geodirclaim_db_version',
        'CPT Manager' => 'geodir_custom_posts_db_version',
        'Location Manager' => 'geodirlocation_db_version',
        'Payment Manager' => 'geodir_payments_db_version',
        'Events Manager' => 'geodirevents_db_version',
    );

    /**
     * Filter the array of plugins to clear the version numbers for in the GD >Tools : clear all version numbers.
     *
     * @since 1.0.0
     * @param array $gd_arr The array or addons to clear, array('GeoDirectory' => 'geodirectory_db_version',...
     */
    $ver_arr = apply_filters('geodir_db_version_name', $gd_arr);

    if (!empty($ver_arr)) {
        foreach ($ver_arr as $key => $val) {
            if (delete_option($val)) {
                $output_str .= "<li>" . $key . __(' Version: Deleted', 'geodirectory') . "</li>";
            } else {
                $output_str .= "<li>" . $key . __(' Version: Not Found', 'geodirectory') . "</li>";
            }

        }

        if ($output_str) {
            $output_str .= "<li><strong>" . __(' Upgrade/install scripts will run on next page reload.', 'geodirectory') . "</strong></li>";
        }

    }

    if ($is_error_during_diagnose) {
        $info_div_class = "geodir_problem_info";
        $fix_button_txt = "";
    } else {
        $info_div_class = "geodir_noproblem_info";
        $fix_button_txt = '';
    }
    echo "<ul class='$info_div_class'>";
    echo $output_str;
    echo $fix_button_txt;
    echo "</ul>";

}


/**
 * Checks ratings for correct location and content settings.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 */
function geodir_diagnose_ratings()
{
    global $wpdb;
    $fix = isset($_POST['fix']) ? true : false;

    //if($fix){echo 'true';}else{echo 'false';}
    $is_error_during_diagnose = false;
    $output_str = '';

    // check review locations
    if ($wpdb->get_results("SELECT * FROM " . GEODIR_REVIEW_TABLE . " WHERE city='' OR city IS NULL OR latitude='' OR latitude IS NULL")) {
        $output_str .= "<li>" . __('Review locations missing or broken', 'geodirectory') . "</li>";
        $is_error_during_diagnose = true;

        if ($fix) {
            if (geodir_fix_review_location()) {
                $output_str .= "<li><strong>" . __('-->FIXED: Review locations fixed', 'geodirectory') . "</strong></li>";
            } else {
                $output_str .= "<li><strong>" . __('-->FAILED: Review locations fix failed', 'geodirectory') . "</strong></li>";
            }
        }

    } else {
        $output_str .= "<li>" . __('Review locations ok', 'geodirectory') . "</li>";
    }

    if ($is_error_during_diagnose) {
        $info_div_class = "geodir_problem_info";
        $fix_button_txt = "<input type='button' value='" . __('Fix', 'geodirectory') . "' class='button-primary geodir_fix_diagnostic_issue' data-diagnostic-issue='ratings' />";
    } else {
        $info_div_class = "geodir_noproblem_info";
        $fix_button_txt = '';
    }
    echo "<ul class='$info_div_class'>";
    echo $output_str;
    echo $fix_button_txt;
    echo "</ul>";

}


/**
 * Checks if the GD database tables have been converted to use multisite correctly.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 */
function geodir_diagnose_multisite_conversion()
{
    global $wpdb;
    $fix = isset($_POST['fix']) ? true : false;
    //if($fix){echo 'true';}else{echo 'false';}
    $is_error_during_diagnose = false;
    $output_str = '';

    $filter_arr = array();
    $filter_arr['output_str'] = $output_str;
    $filter_arr['is_error_during_diagnose'] = $is_error_during_diagnose;
    $table_arr = array(
		'geodir_api_keys' => __('API Keys', 'geodirectory'),
		'geodir_business_hours' => __('Business Hours', 'geodirectory'),
        'geodir_custom_fields' => __('Custom fields', 'geodirectory'),
        'geodir_post_icon' => __('Post icon', 'geodirectory'),
        'geodir_attachments' => __('Attachments', 'geodirectory'),
        'geodir_post_review' => __('Reviews', 'geodirectory'),
        'geodir_custom_sort_fields' => __('Custom sort fields', 'geodirectory'),
        'geodir_gd_place_detail' => __('Place detail', 'geodirectory')
    );

    // allow other addons to hook in and add their checks

    /**
     * Filter the array of tables.
     *
     * Filter the array of tables to check during the GD>Tools multisite DB conversion tool check, this allows addons to add their DB tables to the checks.
     *
     * @since 1.0.0
     * @param array $table_arr The array of tables to check, array('geodir_post_review' => __('Reviews', 'geodirectory'),...
     */
    $table_arr = apply_filters('geodir_diagnose_multisite_conversion', $table_arr);

    foreach ($table_arr as $table => $table_name) {
        // Diagnose table
        $filter_arr = geodir_diagnose_multisite_table($filter_arr, $table, $table_name, $fix);
    }


    $output_str = $filter_arr['output_str'];
    $is_error_during_diagnose = $filter_arr['is_error_during_diagnose'];


    if ($is_error_during_diagnose) {
        $info_div_class = "geodir_problem_info";
        $fix_button_txt = "<input type='button' value='" . __('Fix', 'geodirectory') . "' class='button-primary geodir_fix_diagnostic_issue' data-diagnostic-issue='multisite_conversion' />";
    } else {
        $info_div_class = "geodir_noproblem_info";
        $fix_button_txt = '';
    }
    echo "<ul class='$info_div_class'>";
    echo $output_str;
    echo $fix_button_txt;
    echo "</ul>";
}

/**
 * Fixes if the GD pages are not installed correctly.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @global object $current_user Current user object.
 * @param string $slug The page slug.
 * @param string $page_title The page title.
 * @param int $old_id Old post ID.
 * @param string $option Option meta key.
 * @return bool Returns true when success. false when failure.
 */
function geodir_fix_virtual_page($slug, $page_title, $old_id, $option)
{
    global $wpdb, $current_user;

    if (!empty($old_id)) {
        wp_delete_post($old_id, true);
    }//delete post if already there
    else {
        $page_found = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT ID FROM " . $wpdb->posts . " WHERE post_name = %s LIMIT 1;",
                array($slug)
            )
        );
        wp_delete_post($page_found, true);

    }

    $page_data = array(
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_author' => $current_user->ID,
        'post_name' => $slug,
        'post_title' => $page_title,
        'post_content' => '',
        'post_parent' => 0,
        'comment_status' => 'closed'
    );
    $page_id = wp_insert_post($page_data);
    geodir_update_option($option, $page_id);
    if ($page_id) {
        return true;
    } else {
        return false;
    }
}

/**
 * Checks if the GD pages are installed correctly or not.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 */
function geodir_diagnose_default_pages()
{
    global $wpdb;
    $is_error_during_diagnose = false;
    $output_str = '';
    $fix = isset($_POST['fix']) ? true : false;
    

    //////////////////////////////////
    /* Diagnose Add Listing Page Starts */
    //////////////////////////////////
    $option_value = geodir_get_option('geodir_add_listing_page');
    $page = get_post($option_value);
    if(!empty($page)){$page_found = $page->ID;}else{$page_found = '';}

    if(!empty($option_value) && !empty($page_found) && $option_value == $page_found && $page->post_status=='publish')
        $output_str .= "<li>" . __('Add Listing page exists with proper setting.', 'geodirectory') . "</li>";
    else {
        $is_error_during_diagnose = true;
        $output_str .= "<li><strong>" . __('Add Listing page is missing.', 'geodirectory') . "</strong></li>";
        if ($fix) {
            if (geodir_fix_virtual_page('add-listing', __('Add Listing', 'geodirectory'), $page_found, 'geodir_add_listing_page')) {
                $output_str .= "<li><strong>" . __('-->FIXED: Add Listing page fixed', 'geodirectory') . "</strong></li>";
            } else {
                $output_str .= "<li><strong>" . __('-->FAILED: Add Listing page fix failed', 'geodirectory') . "</strong></li>";
            }
        }
    }

    ////////////////////////////////
    /* Diagnose Add Listing Page Ends */
    ////////////////////////////////


    //////////////////////////////////
    /* Diagnose Listing Preview Page Starts */
    //////////////////////////////////
    $option_value = geodir_get_option('geodir_preview_page');
    $page = get_post($option_value);
    if(!empty($page)){$page_found = $page->ID;}else{$page_found = '';}

    if(!empty($option_value) && !empty($page_found) && $option_value == $page_found && $page->post_status=='publish')
        $output_str .= "<li>" . __('Listing Preview page exists with proper setting.', 'geodirectory') . "</li>";
    else {
        $is_error_during_diagnose = true;
        $output_str .= "<li><strong>" . __('Listing Preview page is missing.', 'geodirectory') . "</strong></li>";
        if ($fix) {
            if (geodir_fix_virtual_page('listing-preview', __('Listing Preview', 'geodirectory'), $page_found, 'geodir_preview_page')) {
                $output_str .= "<li><strong>" . __('-->FIXED: Listing Preview page fixed', 'geodirectory') . "</strong></li>";
            } else {
                $output_str .= "<li><strong>" . __('-->FAILED: Listing Preview page fix failed', 'geodirectory') . "</strong></li>";
            }
        }
    }

    ////////////////////////////////
    /* Diagnose Listing Preview Page Ends */
    ////////////////////////////////

    //////////////////////////////////
    /* Diagnose Listing Success Page Starts */
    //////////////////////////////////
    $option_value = geodir_get_option('geodir_success_page');
    $page = get_post($option_value);
    if(!empty($page)){$page_found = $page->ID;}else{$page_found = '';}

    if(!empty($option_value) && !empty($page_found) && $option_value == $page_found && $page->post_status=='publish')
        $output_str .= "<li>" . __('Listing Success page exists with proper setting.', 'geodirectory') . "</li>";
    else {
        $is_error_during_diagnose = true;
        $output_str .= "<li><strong>" . __('Listing Success page is missing.', 'geodirectory') . "</strong></li>";
        if ($fix) {
            if (geodir_fix_virtual_page('listing-success', __('Listing Success', 'geodirectory'), $page_found, 'geodir_success_page')) {
                $output_str .= "<li><strong>" . __('-->FIXED: Listing Success page fixed', 'geodirectory') . "</strong></li>";
            } else {
                $output_str .= "<li><strong>" . __('-->FAILED: Listing Success page fix failed', 'geodirectory') . "</strong></li>";
            }
        }
    }

    ////////////////////////////////
    /* Diagnose Listing Sucess Page Ends */
    ////////////////////////////////

    //////////////////////////////////
    /* Diagnose Info Page Starts */
    //////////////////////////////////
    $option_value = geodir_get_option('geodir_info_page');
    $page = get_post($option_value);
    if(!empty($page)){$page_found = $page->ID;}else{$page_found = '';}

    if(!empty($option_value) && !empty($page_found) && $option_value == $page_found && $page->post_status=='publish')
        $output_str .= "<li>" . __('Info page exists with proper setting.', 'geodirectory') . "</li>";
    else {
        $is_error_during_diagnose = true;
        $output_str .= "<li><strong>" . __('Info page is missing.', 'geodirectory') . "</strong></li>";
        if ($fix) {
            if (geodir_fix_virtual_page('gd-info', __('Info', 'geodirectory'), $page_found, 'geodir_info_page')) {
                $output_str .= "<li><strong>" . __('-->FIXED: Info page fixed', 'geodirectory') . "</strong></li>";
            } else {
                $output_str .= "<li><strong>" . __('-->FAILED: Info page fix failed', 'geodirectory') . "</strong></li>";
            }
        }
    }

    ////////////////////////////////
    /* Diagnose Info Page Ends */
    ////////////////////////////////

    //////////////////////////////////
    /* Diagnose Login Page Starts */
    //////////////////////////////////
    $option_value = geodir_get_option('geodir_login_page');
    $page = get_post($option_value);
    if(!empty($page)){$page_found = $page->ID;}else{$page_found = '';}

    if(!empty($option_value) && !empty($page_found) && $option_value == $page_found && $page->post_status=='publish')
        $output_str .= "<li>" . __('Login page exists with proper setting.', 'geodirectory') . "</li>";
    else {
        $is_error_during_diagnose = true;
        $output_str .= "<li><strong>" . __('Login page is missing.', 'geodirectory') . "</strong></li>";
        if ($fix) {
            if (geodir_fix_virtual_page('gd-login', __('Login', 'geodirectory'), $page_found, 'geodir_login_page')) {
                $output_str .= "<li><strong>" . __('-->FIXED: Login page fixed', 'geodirectory') . "</strong></li>";
            } else {
                $output_str .= "<li><strong>" . __('-->FAILED: Login page fix failed', 'geodirectory') . "</strong></li>";
            }
        }
    }

    ////////////////////////////////
    /* Diagnose Info Page Ends */
    ////////////////////////////////

    //////////////////////////////////
    /* Diagnose Location Page Starts */
    //////////////////////////////////
    $option_value = geodir_get_option('geodir_location_page');
    $page = get_post($option_value);
    if(!empty($page)){$page_found = $page->ID;}else{$page_found = '';}

    if(!empty($option_value) && !empty($page_found) && $option_value == $page_found && $page->post_status=='publish')
        $output_str .= "<li>" . __('Location page exists with proper setting.', 'geodirectory') . "</li>";
    else {
        $is_error_during_diagnose = true;
        $output_str .= "<li><strong>" . __('Location page is missing.', 'geodirectory') . "</strong></li>";
        if ($fix) {
            if (geodir_fix_virtual_page('location', __('Location', 'geodirectory'), $page_found, 'geodir_location_page')) {
                $output_str .= "<li><strong>" . __('-->FIXED: Location page fixed', 'geodirectory') . "</strong></li>";
            } else {
                $output_str .= "<li><strong>" . __('-->FAILED: Location page fix failed', 'geodirectory') . "</strong></li>";
            }
        }
    }

    ////////////////////////////////
    /* Diagnose Location Page Ends */
    ////////////////////////////////

    //////////////////////////////////
    /* Diagnose Archive Page Starts */
    //////////////////////////////////
    $option_value = geodir_get_option('geodir_archive_page');
    $page = get_post($option_value);
    if(!empty($page)){$page_found = $page->ID;}else{$page_found = '';}

    if(!empty($option_value) && !empty($page_found) && $option_value == $page_found && $page->post_status=='publish')
        $output_str .= "<li>" . __('Archive page exists with proper setting.', 'geodirectory') . "</li>";
    else {
        $is_error_during_diagnose = true;
        $output_str .= "<li><strong>" . __('Archive page is missing.', 'geodirectory') . "</strong></li>";
        if ($fix) {
            if (geodir_fix_virtual_page('gd-archive', __('GD Archive', 'geodirectory'), $page_found, 'geodir_archive_page')) {
                $output_str .= "<li><strong>" . __('-->FIXED: Archive page fixed', 'geodirectory') . "</strong></li>";
            } else {
                $output_str .= "<li><strong>" . __('-->FAILED: Archive page fix failed', 'geodirectory') . "</strong></li>";
            }
        }
    }

    ////////////////////////////////
    /* Diagnose Archive Page Ends */
    ////////////////////////////////

    //////////////////////////////////
    /* Diagnose Search Page Starts */
    //////////////////////////////////
    $option_value = geodir_get_option('geodir_search_page');
    $page = get_post($option_value);
    if(!empty($page)){$page_found = $page->ID;}else{$page_found = '';}

    if(!empty($option_value) && !empty($page_found) && $option_value == $page_found && $page->post_status=='publish')
        $output_str .= "<li>" . __('Search page exists with proper setting.', 'geodirectory') . "</li>";
    else {
        $is_error_during_diagnose = true;
        $output_str .= "<li><strong>" . __('Search page is missing.', 'geodirectory') . "</strong></li>";
        if ($fix) {
            if (geodir_fix_virtual_page('gd-search', __('GD Search', 'geodirectory'), $page_found, 'geodir_search_page')) {
                $output_str .= "<li><strong>" . __('-->FIXED: Search page fixed', 'geodirectory') . "</strong></li>";
            } else {
                $output_str .= "<li><strong>" . __('-->FAILED: Search page fix failed', 'geodirectory') . "</strong></li>";
            }
        }
    }

    ////////////////////////////////
    /* Diagnose Search Page Ends */
    ////////////////////////////////

    //////////////////////////////////
    /* Diagnose Details Page Starts */
    //////////////////////////////////
    $option_value = geodir_get_option('geodir_details_page');
    $page = get_post($option_value);
    if(!empty($page)){$page_found = $page->ID;}else{$page_found = '';}

    if(!empty($option_value) && !empty($page_found) && $option_value == $page_found && $page->post_status=='publish')
        $output_str .= "<li>" . __('Details page exists with proper setting.', 'geodirectory') . "</li>";
    else {
        $is_error_during_diagnose = true;
        $output_str .= "<li><strong>" . __('Details page is missing.', 'geodirectory') . "</strong></li>";
        if ($fix) {
            if (geodir_fix_virtual_page('gd-details', __('GD Details', 'geodirectory'), $page_found, 'geodir_details_page')) {
                $output_str .= "<li><strong>" . __('-->FIXED: Details page fixed', 'geodirectory') . "</strong></li>";
            } else {
                $output_str .= "<li><strong>" . __('-->FAILED: Details page fix failed', 'geodirectory') . "</strong></li>";
            }
        }
    }

    ////////////////////////////////
    /* Diagnose Details Page Ends */
    ////////////////////////////////

    $page_chk_arr = array('output_str'=>$output_str,'is_error_during_diagnose'=>$is_error_during_diagnose );
    /**
     * This action is called at the end of the GD Tools page check function.
     *
     * @since 1.5.2
     */
    $page_chk_arr = apply_filters('geodir_diagnose_default_pages',$page_chk_arr);

    $output_str = $page_chk_arr['output_str'];
    $is_error_during_diagnose = $page_chk_arr['is_error_during_diagnose'];

    if ($is_error_during_diagnose) {
        if ($fix) {
            flush_rewrite_rules();
        }
        $info_div_class = "geodir_problem_info";
        $fix_button_txt = "<input type='button' value='" . __('Fix', 'geodirectory') . "' class='button-primary geodir_fix_diagnostic_issue' data-diagnostic-issue='default_pages' />";
    } else {
        $info_div_class = "geodir_noproblem_info";
        $fix_button_txt = '';
    }
    echo "<ul class='$info_div_class'>";
    echo $output_str;
    echo $fix_button_txt;
    echo "</ul>";

}

/**
 * Loads custom fields in to file for translation.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 */
function geodir_diagnose_load_db_language() {
    global $wpdb;
	
    $tools_controller = new GeoDir_Admin_Tools();
    $is_error_during_diagnose = $tools_controller->load_db_language();

    $output_str = '';
    $fix_button_txt = '';

    if ($is_error_during_diagnose) {
        $output_str .= "<li>" . __('Fail to load custom fields in to file for translation, please check file permission:', 'geodirectory') . ' ' . geodir_plugin_path() . '/db-language.php' . "</li>";
		$info_div_class = "geodir_problem_info";
    } else {
        $output_str .= "<li>" . __('Load custom fields in to file for translation: ok', 'geodirectory') . "</li>";
		$info_div_class = "geodir_noproblem_info";
        $fix_button_txt = '';
    }
    
	echo "<ul class='$info_div_class'>";
    echo $output_str;
    echo $fix_button_txt;
    echo "</ul>";

}

/* Ajax Handler Ends*/

add_filter('posts_clauses_request', 'geodir_posts_clauses_request');
/**
 * Adds sorting type - sort by expire.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @global object $wp_query WordPress Query object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 * @param array $clauses {
 *    Attributes of the clause array.
 *
 *    @type string $where Where clause.
 *    @type string $groupby Groupby clause.
 *    @type string $join Join clause.
 *    @type string $orderby Orderby clause.
 *    @type string $distinct Distinct clause.
 *    @type string $fields Fields clause.
 *    @type string $limits Limits clause.
 *
 * }
 * @return array Altered clause array.
 */
function geodir_posts_clauses_request($clauses)
{
    global $wpdb, $wp_query, $plugin_prefix;

    if (is_admin() && !empty($wp_query->query_vars) && !empty($wp_query->query_vars['is_geodir_loop']) && !empty($wp_query->query_vars['orderby']) && $wp_query->query_vars['orderby'] == 'expire' && !empty($wp_query->query_vars['post_type']) && in_array($wp_query->query_vars['post_type'], geodir_get_posttypes()) && !empty($wp_query->query_vars['orderby']) && isset($clauses['join']) && isset($clauses['orderby']) && isset($clauses['fields'])) {
        $table = $plugin_prefix . $wp_query->query_vars['post_type'] . '_detail';

        $join = $clauses['join'] . ' INNER JOIN ' . $table . ' AS gd_posts ON (gd_posts.post_id = ' . $wpdb->posts . '.ID)';
        $clauses['join'] = $join;

        $fields = $clauses['fields'] != '' ? $clauses['fields'] . ', ' : '';
        $fields .= 'IF(UNIX_TIMESTAMP(DATE_FORMAT(gd_posts.expire_date, "%Y-%m-%d")), UNIX_TIMESTAMP(DATE_FORMAT(gd_posts.expire_date, "%Y-%m-%d")), 253402300799) AS gd_expire';
        $clauses['fields'] = $fields;

        $order = !empty($wp_query->query_vars['order']) ? $wp_query->query_vars['order'] : 'ASC';
        $orderby = 'gd_expire ' . $order;
        $clauses['orderby'] = $orderby;
    }
    return $clauses;
}