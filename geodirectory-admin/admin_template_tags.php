<?php
/**
 * Admin template tag functions.
 *
 * @since 1.0.0
 * @package GeoDirectory
 */

if (!function_exists('geodir_admin_panel')) {
    /**
     * GeoDirectory Backend Admin Panel.
     *
     * Handles the display of the main GeoDirectory admin panel.
     *
     * @since 1.0.0
     * @package GeoDirectory
     * @global string $current_tab Current tab in geodirectory settings.
     * @global object $geodirectory GeoDirectory plugin object.
     */
    function geodir_admin_panel()
    {
        global $geodirectory;
        global $current_tab;


        ?>

        <div id="gd-wrapper-main" class="wrap geodirectory">
            <?php
            /**
             * Called just after the opening wrapper div for the GD settings page in the admin area.
             *
             * @since 1.0.0
             */
            do_action('geodir_before_admin_panel');
            ?>

            <div class="gd-wrapper gd-wrapper-vr clearfix">

                <div class="gd-left-nav">
                    <img src="<?php echo geodir_plugin_url(); ?>/geodirectory-assets/images/geo-logo.png" alt="geo-logo"
                         class="geo-logo"/>
                    <img src="<?php echo geodir_plugin_url(); ?>/geodirectory-assets/images/geo-logoalter.png"
                         alt="geo-logo" class="geo-logoalter"/>
                    <ul>
                        <?php
                        $tabs = array();
                        /**
                         * Filter the GD admin settings tabs on the left of the admin menu.
                         *
                         * @since 1.0.0
                         * @param array $tabs The array of tabs to display.
                         */
                        $tabs = apply_filters('geodir_settings_tabs_array', $tabs);
                        update_option('geodir_tabs', $tabs);// Important to show settings menu dropdown

                        foreach ($tabs as $name => $args) :
                            $label = $args['label'];


                            $query_string = '';
                            if (isset($args['subtabs']) && !empty($args['subtabs'])):

                                $subtabs = $args['subtabs'];

                                $query_string = '&subtab=' . $subtabs[0]['subtab'];

                            endif;


                            $tab_link = admin_url('admin.php?page=geodirectory&tab=' . $name . $query_string);

                            if (isset($args['url']) && $args['url'] != '') {
                                $tab_link = $args['url'];
                            }

                            if (!empty($args['request']))
                                $tab_link = geodir_getlink($tab_link, $args['request']);

                            if (isset($args['target']) && $args['target'] != '') {
                                $tab_target = " target='" . sanitize_text_field($args['target']) . "' ";
                            } else
                                $tab_target = '';

                            $tab_active = '';
                            if ($current_tab == $name)
                                $tab_active = ' class="tab-active" ';
                            /**
                             * Called before the individual settings tabs are output.
                             *
                             * @since 1.0.0
                             * @param string $name The name of the settings tab.
                             * @see 'geodir_after_settings_tabs'
                             */
                            do_action('geodir_before_settings_tabs', $name);
                            echo '<li ' . $tab_active . ' ><a href="' . esc_url($tab_link) . '"  ' . $tab_target . ' >' . $label . '</a></li>';
                            /**
                             * Called after the individual settings tabs are output.
                             *
                             * @since 1.0.0
                             * @param string $name The name of the settings tab.
                             * @see 'geodir_before_settings_tabs'
                             */
                            do_action('geodir_after_settings_tabs', $name);
                        endforeach;

                        /**
                         * Called after the GD settings tabs have been output.
                         *
                         * Called before the closing `ul` so can be used to add new settings tab links.
                         *
                         * @since 1.0.0
                         */
                        do_action('geodir_settings_tabs');
                        ?>
                    </ul>
                </div>
                <!--gd-left-nav ends here-->

                <div class="gd-content-wrapper">
                    <div class="gd-tabs-main">

                        <?php
                        unset($subtabs);
                        if (isset($tabs[$current_tab]['subtabs']))
                            $subtabs = $tabs[$current_tab]['subtabs'];
                        $form_action = '';

                        if (!empty($subtabs)):
                        ?>
                            <dl class="gd-tab-head">
                                <?php
                                foreach ($subtabs as $sub) {
                                    $subtab_active = '';
                                    if (isset($_REQUEST['subtab']) && $sub['subtab'] == $_REQUEST['subtab']) {
                                        $subtab_active = 'class="gd-tab-active"';
                                        $form_action = isset($sub['form_action']) ? $sub['form_action'] : '';
                                    }

                                    $sub_tabs_link = admin_url() . 'admin.php?page=geodirectory&tab=' . $current_tab . '&subtab=' . $sub['subtab'];
                                    if (isset($sub['request']) && is_array($sub['request']) && !empty($sub['request'])) {
                                        $sub_tabs_link = geodir_getlink($sub_tabs_link, $sub['request']);
                                    }
                                    echo '<dd ' . $subtab_active . ' id="claim_listing"><a href="' . esc_url($sub_tabs_link) . '" >' . sanitize_text_field($sub['label']) . '</a></dd>';
                                }
                                ?>
                            </dl>

                        <?php endif; ?>
                        <div class="gd-tab-content <?php if (empty($subtabs)) {
                            echo "inner_contet_tabs";
                        } ?>">
                            <form method="post" id="mainform"
                                  class="geodir_optionform <?php echo $current_tab . ' '; ?><?php if (isset($sub['subtab'])) {
                                      echo sanitize_text_field($sub['subtab']);
                                  } ?>" action="<?php echo $form_action; ?>" enctype="multipart/form-data">
                                <input type="hidden" class="active_tab" name="active_tab"
                                       value="<?php if (isset($_REQUEST['active_tab'])) {
                                           echo sanitize_text_field($_REQUEST['active_tab']);
                                       } ?>"/>
                                <?php wp_nonce_field('geodir-settings', '_wpnonce', true, true); ?>
                                <?php wp_nonce_field('geodir-settings-' . $current_tab, '_wpnonce-' . $current_tab, true, true); ?>
                                <?php
                                /**
                                 * Used to call the content of each GD settings tab page.
                                 *
                                 * @since 1.0.0
                                 */
                                do_action('geodir_admin_option_form', $current_tab); ?>
                            </form>
                        </div>

                    </div>
                </div>

            </div>
        </div>
        <script type="text/javascript">
            jQuery(window).load(function () {
                // Subsubsub tabs
                jQuery('ul.subsubsub li a:eq(0)').addClass('current');
                jQuery('.subsubsub_section .section:gt(0)').hide();

                jQuery('ul.subsubsub li a').click(function () {
                    /*jQuery('a', jQuery(this).closest('ul.subsubsub')).removeClass('current');
                     jQuery(this).addClass('current');
                     jQuery('.section', jQuery(this).closest('.subsubsub_section')).hide();
                     jQuery( jQuery(this).attr('href') ).show();
                     jQuery('#last_tab').val( jQuery(this).attr('href') );
                     return false;*/
                });
                <?php if (isset($_GET['subtab']) && $_GET['subtab']) echo 'jQuery(\'ul.subsubsub li a[href="#' . sanitize_text_field($_GET['subtab']) . '"]\').click();'; ?>
                // Countries
                jQuery('select#geodirectory_allowed_countries').change(function () {
                    if (jQuery(this).val() == "specific") {
                        jQuery(this).parent().parent().next('tr').show();
                    } else {
                        jQuery(this).parent().parent().next('tr').hide();
                    }
                }).change();

                // Color picker
                jQuery('.colorpick').each(function () {
                    jQuery('.colorpickdiv', jQuery(this).parent()).farbtastic(this);
                    jQuery(this).click(function () {
                        if (jQuery(this).val() == "") jQuery(this).val('#');
                        jQuery('.colorpickdiv', jQuery(this).parent()).show();
                    });
                });
                jQuery(document).mousedown(function () {
                    jQuery('.colorpickdiv').hide();
                });

                // Edit prompt
                jQuery(function () {
                    var changed = false;

                    jQuery('input, textarea, select, checkbox').change(function () {
                        changed = true;
                    });

                    jQuery('.geodirectory-nav-tab-wrapper a').click(function () {
                        if (changed) {
                            window.onbeforeunload = function () {
                                return '<?php echo __( 'The changes you made will be lost if you navigate away from this page.', 'geodirectory'); ?>';
                            }
                        } else {
                            window.onbeforeunload = '';
                        }
                    });

                    jQuery('.submit input').click(function () {
                        window.onbeforeunload = '';
                    });
                });

                // Sorting
                jQuery('table.wd_gateways tbody').sortable({
                    items: 'tr',
                    cursor: 'move',
                    axis: 'y',
                    handle: 'td',
                    scrollSensitivity: 40,
                    helper: function (e, ui) {
                        ui.children().each(function () {
                            jQuery(this).width(jQuery(this).width());
                        });
                        ui.css('left', '0');
                        return ui;
                    },
                    start: function (event, ui) {
                        ui.item.css('background-color', '#f6f6f6');
                    },
                    stop: function (event, ui) {
                        ui.item.removeAttr('style');
                    }
                });

                // Chosen selects
                jQuery("select.chosen_select").chosen();

                jQuery("select.chosen_select_nostd").chosen({
                    allow_single_deselect: 'true'
                });

            });
        </script>
    <?php

    }
}


/**
 * Displays setting form for the given tab.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global array $geodir_settings Geodirectory settings array.
 * @global object $wpdb WordPress Database object.
 * @param string $tab_name Tab name.
 */
function geodir_admin_option_form($tab_name)
{

    //echo $tab_name.'_array.php' ;
    global $geodir_settings, $is_default, $mapzoom;
    if (file_exists(dirname(__FILE__) . '/option-pages/' . $tab_name . '_array.php')) {
        /**
         * Contains settings array for given tab.
         *
         * @since 1.0.0
         * @package GeoDirectory
         */
        include_once('option-pages/' . $tab_name . '_array.php');
    }

    $listing_type = isset($_REQUEST['listing_type']) ? $_REQUEST['listing_type'] : '';

    switch ($tab_name) {

        case 'general_settings':

            geodir_admin_fields($geodir_settings['general_settings']);
            /**
             *
             * Update Taxonomy Options *
             *
             **/
            /*add_action('updated_option_place_prefix','update_listing_prefix');
            function update_listing_prefix(){
                geodir_register_defaults();
            }*/

            if (isset($_REQUEST['active_tab']) && ($_REQUEST['active_tab'] == 'dummy_data_settings' || $_REQUEST['active_tab'] == 'csv_upload_settings'))
                $hide_save_button = "style='display:none;'";
            else
                $hide_save_button = '';

            /**
             * Filter weather the default save button in the GD admin settings pages should be shown.
             *
             * @since 1.0.0
             * @param string $hide_save_button The style element, either blank or: style='display:none;'.
             */
            $hide_save_button = apply_filters('geodir_hide_save_button', $hide_save_button);
            ?>

            <p class="submit">
            <input <?php echo $hide_save_button;?> name="save" class="button-primary" type="submit" value="<?php _e('Save changes', 'geodirectory'); ?>" />
            <input type="hidden" name="subtab" id="last_tab" />
            </p>
            
            </div>
            
		<?php break;
        case 'design_settings' :
            geodir_admin_fields($geodir_settings['design_settings']);



            ?>
			<p class="submit">
			<input name="save" class="button-primary" type="submit" value="<?php _e('Save changes', 'geodirectory'); ?>" />
			<input type="hidden" name="subtab" id="last_tab" />
			</p>
			</div>
        <?php break;
        case 'permalink_settings' :
            geodir_admin_fields($geodir_settings['permalink_settings']); ?>
            <p class="submit">
            <input name="save" class="button-primary" type="submit" value="<?php _e('Save changes', 'geodirectory'); ?>" />
            <input type="hidden" name="subtab" id="last_tab" />
            </p>
            </div>	
		<?php break;
        case 'title_meta_settings' :
            geodir_admin_fields($geodir_settings['title_meta_settings']); ?>
            <p class="submit">
            <input name="save" class="button-primary" type="submit" value="<?php _e('Save changes', 'geodirectory'); ?>" />
            <input type="hidden" name="subtab" id="last_tab" />
            </p>
            </div>
		<?php break;
        case 'notifications_settings' :
            geodir_admin_fields($geodir_settings['notifications_settings']); ?>
			
			<p class="submit">
				
			<input name="save" class="button-primary" type="submit" value="<?php _e('Save changes', 'geodirectory'); ?>" />
			<input type="hidden" name="subtab" id="last_tab" />
			</p>
			</div>
			
		<?php break;
        case 'default_location_settings' :
            ?>
            <div class="inner_content_tab_main">
                <div class="gd-content-heading">
                    <?php global $wpdb;


                    $location_result = geodir_get_default_location();

                    $prefix = '';


                    $lat = isset($location_result->city_latitude) ? $location_result->city_latitude : '';
                    $lng = isset($location_result->city_longitude) ? $location_result->city_longitude : '';
                    $city = isset($location_result->city) ? $location_result->city : '';
                    $region = isset($location_result->region) ? $location_result->region : '';
                    $country = isset($location_result->country) ? $location_result->country : '';


                    $map_title = __("Set Address On Map", 'geodirectory');

                    ?>

                    <h3><?php _e('Set Default Location', 'geodirectory');?></h3>

                    <input type="hidden" name="add_location" value="location">

                    <input type="hidden" name="update_city" value="<?php if (isset($location_result->location_id)) {
                        echo $location_result->location_id;
                    } ?>">

                    <input type="hidden" name="address" id="<?php echo $prefix;?>address" value="">

                    <table class="form-table default_location_form">
                        <tbody>
                        <tr valign="top" class="single_select_page">
                            <th class="titledesc" scope="row"><?php _e('City', 'geodirectory');?></th>
                            <td class="forminp">
                                <div class="gtd-formfeild required">
                                    <input class="require" type="text" size="80" style="width:440px"
                                           id="<?php echo $prefix;?>city" name="city"
                                           value="<?php if (isset($location_result->city)) {
                                               echo $location_result->city;
                                           } ?>"/>

                                    <div
                                        class="gd-location_message_error"> <?php _e('This field is required.', 'geodirectory'); ?></div>
                                </div>
                                <span class="description"></span>
                            </td>
                        </tr>
                        <tr valign="top" class="single_select_page">
                            <th class="titledesc" scope="row"><?php _e('Region', 'geodirectory');?></th>
                            <td class="forminp">
                                <div class="gtd-formfeild required">
                                    <input class="require" type="text" size="80" style="width:440px"
                                           id="<?php echo $prefix;?>region" name="region"
                                           value="<?php if (isset($location_result->region)) {
                                               echo $location_result->region;
                                           } ?>"/>

                                    <div
                                        class="gd-location_message_error"> <?php _e('This field is required.', 'geodirectory'); ?></div>
                                </div>
                                <span class="description"></span>
                            </td>
                        </tr>
                        <tr valign="top" class="single_select_page">
                            <th class="titledesc" scope="row"><?php _e('Country', 'geodirectory');?></th>
                            <td class="forminp">
                                <div class="gtd-formfeild required" style="padding-top:10px;">
                                    <?php

                                    $country_result = isset($location_result->country) ? $location_result->country : '';
                                    ?>
                                    <select id="<?php echo $prefix ?>country" class="chosen_select"
                                            data-location_type="country" name="<?php echo $prefix ?>country"
                                            data-placeholder="<?php _e('Choose a country.', 'geodirectory');?>"
                                            data-addsearchtermonnorecord="1" data-ajaxchosen="0" data-autoredirect="0"
                                            data-showeverywhere="0">
                                        <?php geodir_get_country_dl($country, $prefix); ?>
                                    </select>

                                    <div
                                        class="gd-location_message_error"><?php _e('This field is required.', 'geodirectory'); ?></div>

                                </div>


                                <span class="description"></span>
                            </td>
                        </tr>
                        <tr valign="top" class="single_select_page gd-add-location-map">
                            <th class="titledesc"
                                scope="row"><?php _e('Set Location on Map', 'geodirectory');?></th>
                            <td class="forminp">
                                <?php
                                /**
                                 * Contains add listing page map functions.
                                 *
                                 * @since 1.0.0
                                 */
                                include(geodir_plugin_path() . "/geodirectory-functions/map-functions/map_on_add_listing_page.php");?>
                            </td>
                        </tr>
                        <tr valign="top" class="single_select_page">
                            <th class="titledesc" scope="row"><?php _e('City Latitude', 'geodirectory');?></th>
                            <td class="forminp">
                                <div class="gtd-formfeild required" style="padding-top:10px;">
                                    <input type="text" class="require" size="80" style="width:440px"
                                           id="<?php echo $prefix;?>latitude" name="latitude"
                                           value="<?php if (isset($location_result->city_latitude)) {
                                               echo $location_result->city_latitude;
                                           } ?>"/>

                                    <div
                                        class="gd-location_message_error"><?php _e('This field is required.', 'geodirectory'); ?></div>
                                </div>
                                <span class="description"></span>
                            </td>
                        </tr>
                        <tr valign="top" class="single_select_page">
                            <th class="titledesc"
                                scope="row"><?php _e('City Longitude', 'geodirectory');?></th>
                            <td class="forminp">
                                <div class="gtd-formfeild required" style="padding-top:10px;">
                                    <input type="text" class="require" size="80" style="width:440px"
                                           id="<?php echo $prefix;?>longitude" name="longitude"
                                           value="<?php if (isset($location_result->city_longitude)) {
                                               echo $location_result->city_longitude;
                                           } ?>"/>

                                    <div
                                        class="gd-location_message_error"><?php _e('This field is required.', 'geodirectory'); ?></div>
                                </div>
                                <span class="description"></span>
                            </td>
                        </tr>
                        <?php if (isset($location_result->location_id) && $location_result->location_id >= 0) { ?>
                            <tr valign="top" class="single_select_page">
                                <th class="titledesc"
                                    scope="row"><?php _e('Action For Listing', 'geodirectory'); ?></th>
                                <td class="forminp">
                                    <div class="gtd-formfeild" style="padding-top:10px;">
                                        <input style="display:none;" type="radio" name="listing_action"
                                               checked="checked" value="delete"/>
                                        <label><?php _e('Post will be updated if both city and map marker position has been changed.', 'geodirectory'); ?></label>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>

                    <p class="submit">
                        <input type="hidden" name="is_default" value="1"/>
                        <input id="location_save" type="submit" value="Save changes" class="button-primary" name="save">
                    </p>

                </div>
            </div>
            <?php break;
        case $listing_type . '_fields_settings' :

            geodir_custom_post_type_form();

            break;
        case 'tools_settings' :
            geodir_diagnostic_tools_setting_page();
            break;
        case 'compatibility_settings' :
            geodir_theme_compatibility_setting_page();
            break;		
		case 'import_export' :
            geodir_import_export_page();
            break;

    }// end of switch
}


/*
function gd_compat_read_write_code($code,$theme){

$url = wp_nonce_url('admin.php?page=geodirectory&tab=compatibility_settings','gd-compat-theme-options');
if (false === ($creds = request_filesystem_credentials($url, '', false, false, null) ) ) {
	return; // stop processing here
}

if ( ! WP_Filesystem($creds) ) {
	request_filesystem_credentials($url, '', true, false, null);
	return;
}

global $wp_filesystem;

$wp_filesystem->put_contents(
  plugin_dir_path( __FILE__ ).'/geodirectory/geodirectory-templates/compatibility/'.$theme.'php',
  'Example contents of a file',
  FS_CHMOD_FILE // predefined mode settings for WP files
);
 
 
 }
*/


/**
 * Updates theme compatibility settings.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 */
function geodir_update_options_compatibility_settings()
{

    global $wpdb;


    $theme_settings = array();

    $theme_settings['geodir_wrapper_open_id'] = $_POST['geodir_wrapper_open_id'];
    $theme_settings['geodir_wrapper_open_class'] = $_POST['geodir_wrapper_open_class'];
    $theme_settings['geodir_wrapper_open_replace'] = stripslashes($_POST['geodir_wrapper_open_replace']);

    $theme_settings['geodir_wrapper_close_replace'] = stripslashes($_POST['geodir_wrapper_close_replace']);

    $theme_settings['geodir_wrapper_content_open_id'] = $_POST['geodir_wrapper_content_open_id'];
    $theme_settings['geodir_wrapper_content_open_class'] = $_POST['geodir_wrapper_content_open_class'];
    $theme_settings['geodir_wrapper_content_open_replace'] = stripslashes($_POST['geodir_wrapper_content_open_replace']);

    $theme_settings['geodir_wrapper_content_close_replace'] = stripslashes($_POST['geodir_wrapper_content_close_replace']);

    $theme_settings['geodir_article_open_id'] = $_POST['geodir_article_open_id'];
    $theme_settings['geodir_article_open_class'] = $_POST['geodir_article_open_class'];
    $theme_settings['geodir_article_open_replace'] = stripslashes($_POST['geodir_article_open_replace']);

    $theme_settings['geodir_article_close_replace'] = stripslashes($_POST['geodir_article_close_replace']);

    $theme_settings['geodir_sidebar_right_open_id'] = $_POST['geodir_sidebar_right_open_id'];
    $theme_settings['geodir_sidebar_right_open_class'] = $_POST['geodir_sidebar_right_open_class'];
    $theme_settings['geodir_sidebar_right_open_replace'] = stripslashes($_POST['geodir_sidebar_right_open_replace']);

    $theme_settings['geodir_sidebar_right_close_replace'] = stripslashes($_POST['geodir_sidebar_right_close_replace']);

    $theme_settings['geodir_sidebar_left_open_id'] = $_POST['geodir_sidebar_left_open_id'];
    $theme_settings['geodir_sidebar_left_open_class'] = $_POST['geodir_sidebar_left_open_class'];
    $theme_settings['geodir_sidebar_left_open_replace'] = stripslashes($_POST['geodir_sidebar_left_open_replace']);

    $theme_settings['geodir_sidebar_left_close_replace'] = stripslashes($_POST['geodir_sidebar_left_close_replace']);

    $theme_settings['geodir_main_content_open_id'] = $_POST['geodir_main_content_open_id'];
    $theme_settings['geodir_main_content_open_class'] = $_POST['geodir_main_content_open_class'];
    $theme_settings['geodir_main_content_open_replace'] = stripslashes($_POST['geodir_main_content_open_replace']);

    $theme_settings['geodir_main_content_close_replace'] = stripslashes($_POST['geodir_main_content_close_replace']);

// Other Actions
    $theme_settings['geodir_top_content_add'] = stripslashes($_POST['geodir_top_content_add']);
    $theme_settings['geodir_before_main_content_add'] = stripslashes($_POST['geodir_before_main_content_add']);

// Filters
    $theme_settings['geodir_full_page_class_filter'] = stripslashes($_POST['geodir_full_page_class_filter']);
    $theme_settings['geodir_before_widget_filter'] = stripslashes($_POST['geodir_before_widget_filter']);
    $theme_settings['geodir_after_widget_filter'] = stripslashes($_POST['geodir_after_widget_filter']);
    $theme_settings['geodir_before_title_filter'] = stripslashes($_POST['geodir_before_title_filter']);
    $theme_settings['geodir_after_title_filter'] = stripslashes($_POST['geodir_after_title_filter']);
    $theme_settings['geodir_menu_li_class_filter'] = stripslashes($_POST['geodir_menu_li_class_filter']);
    $theme_settings['geodir_sub_menu_ul_class_filter'] = stripslashes($_POST['geodir_sub_menu_ul_class_filter']);
    $theme_settings['geodir_sub_menu_li_class_filter'] = stripslashes($_POST['geodir_sub_menu_li_class_filter']);
    $theme_settings['geodir_menu_a_class_filter'] = stripslashes($_POST['geodir_menu_a_class_filter']);
    $theme_settings['geodir_sub_menu_a_class_filter'] = stripslashes($_POST['geodir_sub_menu_a_class_filter']);
//location manager filters
    $theme_settings['geodir_location_switcher_menu_li_class_filter'] = stripslashes($_POST['geodir_location_switcher_menu_li_class_filter']);
    $theme_settings['geodir_location_switcher_menu_a_class_filter'] = stripslashes($_POST['geodir_location_switcher_menu_a_class_filter']);
    $theme_settings['geodir_location_switcher_menu_sub_ul_class_filter'] = stripslashes($_POST['geodir_location_switcher_menu_sub_ul_class_filter']);
    $theme_settings['geodir_location_switcher_menu_sub_li_class_filter'] = stripslashes($_POST['geodir_location_switcher_menu_sub_li_class_filter']);


// theme required css
    $theme_settings['geodir_theme_compat_css'] = stripslashes($_POST['geodir_theme_compat_css']);

// theme required js
    $theme_settings['geodir_theme_compat_js'] = stripslashes($_POST['geodir_theme_compat_js']);

// theme compat name
    $theme_settings['gd_theme_compat'] = $_POST['gd_theme_compat'];
    if ($theme_settings['gd_theme_compat'] == '') {
        update_option('gd_theme_compat', '');
        update_option('theme_compatibility_setting', '');
        return;
    }

// theme default options
    $theme_settings['geodir_theme_compat_default_options'] = '';


//supported theme code
    $theme_settings['geodir_theme_compat_code'] = false;

    $theme = wp_get_theme();

    if ($theme->parent()) {
        $theme_name = str_replace(" ", "_", $theme->parent()->get('Name'));
    } else {
        $theme_name = str_replace(" ", "_", $theme->get('Name'));
    }

    if (in_array($theme_name, array('Avada', 'Enfold', 'X', 'Divi', 'Genesis', 'Jupiter', 'Multi_News'))) {// list of themes that have php files
        $theme_settings['geodir_theme_compat_code'] = $theme_name;
    }


    $theme_name = $theme_name . "_custom";
    $theme_arr = get_option('gd_theme_compats');
    update_option('gd_theme_compat', $theme_name);
    /**
     * Called before the theme compatibility settings are saved to the DB.
     *
     * @since 1.4.0
     * @param array $theme_settings {
     *    Attributes of the theme compatibility settings array.
     *
     *    @type string $geodir_wrapper_open_id Geodir wrapper open html id.
     *    @type string $geodir_wrapper_open_class Geodir wrapper open html class.
     *    @type string $geodir_wrapper_open_replace Geodir wrapper open content replace.
     *    @type string $geodir_wrapper_close_replace Geodir wrapper close content replace.
     *    @type string $geodir_wrapper_content_open_id Geodir wrapper content open html id.
     *    @type string $geodir_wrapper_content_open_class Geodir wrapper content open html class.
     *    @type string $geodir_wrapper_content_open_replace Geodir wrapper content open content replace.
     *    @type string $geodir_wrapper_content_close_replace Geodir wrapper content close content replace.
     *    @type string $geodir_article_open_id Geodir article open html id.
     *    @type string $geodir_article_open_class Geodir article open html class.
     *    @type string $geodir_article_open_replace Geodir article open content replace.
     *    @type string $geodir_article_close_replace Geodir article close content replace.
     *    @type string $geodir_sidebar_right_open_id Geodir sidebar right open html id.
     *    @type string $geodir_sidebar_right_open_class Geodir sidebar right open html class.
     *    @type string $geodir_sidebar_right_open_replace Geodir sidebar right open content replace.
     *    @type string $geodir_sidebar_right_close_replace Geodir sidebar right close content replace.
     *    @type string $geodir_sidebar_left_open_id Geodir sidebar left open html id.
     *    @type string $geodir_sidebar_left_open_class Geodir sidebar left open html class.
     *    @type string $geodir_sidebar_left_open_replace Geodir sidebar left open content replace.
     *    @type string $geodir_sidebar_left_close_replace Geodir sidebar left close content replace.
     *    @type string $geodir_main_content_open_id Geodir main content open html id.
     *    @type string $geodir_main_content_open_class Geodir main content open html class.
     *    @type string $geodir_main_content_open_replace Geodir main content open content replace.
     *    @type string $geodir_main_content_close_replace Geodir main content close content replace.
     *    @type string $geodir_top_content_add Geodir top content add.
     *    @type string $geodir_before_main_content_add Geodir before main content add.
     *    @type string $geodir_full_page_class_filter Geodir full page class filter.
     *    @type string $geodir_before_widget_filter Geodir before widget filter.
     *    @type string $geodir_after_widget_filter Geodir after widget filter.
     *    @type string $geodir_before_title_filter Geodir before title filter.
     *    @type string $geodir_after_title_filter Geodir after title filter.
     *    @type string $geodir_menu_li_class_filter Geodir menu li class filter.
     *    @type string $geodir_sub_menu_ul_class_filter Geodir sub menu ul class filter.
     *    @type string $geodir_sub_menu_li_class_filter Geodir sub menu li class filter.
     *    @type string $geodir_menu_a_class_filter Geodir menu a class filter.
     *    @type string $geodir_sub_menu_a_class_filter Geodir sub menu a class filter.
     *    @type string $geodir_location_switcher_menu_li_class_filter Geodir location switcher menu li class filter.
     *    @type string $geodir_location_switcher_menu_a_class_filter Geodir location switcher menu a class filter.
     *    @type string $geodir_location_switcher_menu_sub_ul_class_filter Geodir location switcher menu sub ul class filter.
     *    @type string $geodir_location_switcher_menu_sub_li_class_filter Geodir location switcher menu sub li class filter.
     *    @type string $geodir_theme_compat_css Geodir theme compatibility css.
     *    @type string $geodir_theme_compat_js Geodir theme compatibility js.
     *    @type string $gd_theme_compat Gd theme compatibility.
     *    @type string $geodir_theme_compat_default_options Geodir theme compatibility default options.
     *    @type bool $geodir_theme_compat_code Geodir theme compatibility code Ex: 'Avada.
     *
     * }
     */
    do_action('gd_compat_save_settings', $theme_settings);

//if($_POST['gd_theme_compat'])==
    $theme_arr[$theme_name] = $theme_settings;
    update_option('gd_theme_compats', $theme_arr);


//print_r($theme_settings);exit;
    update_option('theme_compatibility_setting', $theme_settings);

}

/**
 * Displays theme compatibility settings.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 */
function geodir_theme_compatibility_setting_page()
{
    global $wpdb;
    $tc = get_option('theme_compatibility_setting');
    //print_r($tc);
    //print_r(wp_get_theme());

    ?>
    <div class="inner_content_tab_main">
        <div class="gd-content-heading">


            <h3><?php _e('Theme Compatibility Settings', 'geodirectory');?></h3>
            <style>
                .gd-theme-compat-table {
                    width: 100%;
                    border: 1px solid #666;
                }

                #gd-import-export-theme-comp, .gd-theme-compat-table textarea {
                    width: 100%;
                }

                .gd-theme-comp-out {
                    border-bottom: #000000 solid 1px;
                }

                .gd-comp-import-export {
                    display: none;
                }

                #gd-compat-warnings h3 {
                    background-color: #FFA07A;
                }

            </style>

            <?php if (str_replace("_custom", "", get_option('gd_theme_compat')) == 'Avada') { ?>
                <div id="gd-compat-warnings">
                    <h3><?php _e('Since Avada 3.8+ they have added hooks for compatibility for GeoDirectory so the header.php modification is no longer required. <a href="http://docs.wpgeodirectory.com/avada-compatibility-header-php/" target="_blank">See here</a>', 'geodirectory'); ?></h3>
                </div>
            <?php }?>

            <h4><?php _e('Select Theme Compatibility Pack', 'geodirectory');?></h4>

            <select name="gd_theme_compat" id="gd_theme_compat">
                <option value=""><?php _e('Select Theme', 'geodirectory');?></option>
                <option value="custom"><?php _e('Custom', 'geodirectory');?></option>
                <?php
                $theme_arr = get_option('gd_theme_compats');
                $theme_active = get_option('gd_theme_compat');
                if (is_array($theme_arr)) {
                    foreach ($theme_arr as $key => $theme) {
                        $sel = '';
                        if ($theme_active == $key) {
                            $sel = "selected";
                        }
                        echo "<option $sel>$key</option>";
                    }


                }

                ?>
            </select>
            <button onclick="gd_comp_export();" type="button"
                    class="button-primary"><?php _e('Export', 'geodirectory');?></button>
            <button onclick="gd_comp_import();" type="button"
                    class="button-primary"><?php _e('Import', 'geodirectory');?></button>

            <div class="gd-comp-import-export">
                <textarea id="gd-import-export-theme-comp"
                          placeholder="<?php _e('Paste the JSON code here and then click import again', 'geodirectory');?>"></textarea>
            </div>
            <script>

                function gd_comp_export() {
                    theme = jQuery('#gd_theme_compat').val();
                    if (theme == '' || theme == 'custom') {
                        alert("<?php _e('Please select a theme to export','geodirectory');?>");
                        return false;
                    }
                    jQuery('.gd-comp-import-export').show();
                    var data = {
                        'action': 'get_gd_theme_compat_callback',
                        'theme': theme,
                        'export': true
                    };
                    jQuery.post(ajaxurl, data, function (response) {
                        jQuery('#gd-import-export-theme-comp').val(response);
                    });
                    return false;
                }

                function gd_comp_import() {
                    if (jQuery('.gd-comp-import-export').css('display') == 'none') {
                        jQuery('#gd-import-export-theme-comp').val('');
                        jQuery('.gd-comp-import-export').show();
                        return false;
                    }

                    json = jQuery('#gd-import-export-theme-comp').val();
                    if (json == '') {
                        return false;
                    }

                    var data = {
                        'action': 'get_gd_theme_compat_import_callback',
                        'theme': json
                    };

                    jQuery.post(ajaxurl, data, function (response) {
                        if (response == '0') {
                            alert("<?php _e('Something went wrong','geodirectory');?>");
                        } else {
                            alert("<?php _e('Theme Compatibility Imported','geodirectory');?>");
                            jQuery('#gd-import-export-theme-comp').val('');
                            jQuery('.gd-comp-import-export').hide();
                            jQuery('#gd_theme_compat').append(new Option(response, response));
                        }
                    });
                    return false;
                }

                jQuery("#gd_theme_compat").change(function () {
                    var data = {
                        'action': 'get_gd_theme_compat_callback',
                        'theme': jQuery(this).val()
                    };

                    if (jQuery(this).val() == 'custom') {
                        return;
                    }
                    if (jQuery(this).val() != '') {
                        jQuery.post(ajaxurl, data, function (response) {
                            var obj = jQuery.parseJSON(response);
                            console.log(obj);
                            gd_fill_compat_fields(obj);
                        });
                    } else {
                        jQuery(this).closest('form').find("input[type=text], textarea").val("");

                    }

                });

                function gd_fill_compat_fields(obj) {

                    jQuery.each(obj, function (i, item) {
                        jQuery('[name="' + i + '"]').val(item);
                    });

                }

            </script>

            <h4><?php _e('Main Wrapper Actions', 'geodirectory');?></h4>

            <table class="form-table gd-theme-compat-table">
                <tbody>
                <tr>
                    <td><strong><?php _e('Hook', 'geodirectory');?></strong></td>
                    <td><strong><?php _e('ID', 'geodirectory');?></strong></td>
                    <td><strong><?php _e('Class', 'geodirectory');?></strong></td>
                </tr>


                <tr>
                    <td>
                        <small>geodir_wrapper_open</small>
                    </td>
                    <td><input value="<?php if (isset($tc['geodir_wrapper_open_id'])) {
                            echo $tc['geodir_wrapper_open_id'];
                        }?>" type="text" name="geodir_wrapper_open_id" placeholder="geodir-wrapper"/></td>
                    <td><input value="<?php if (isset($tc['geodir_wrapper_open_class'])) {
                            echo $tc['geodir_wrapper_open_class'];
                        }?>" type="text" name="geodir_wrapper_open_class" placeholder=""/></td>
                </tr>

                <tr class="gd-theme-comp-out">
                    <td colspan="3">
                        <span><?php _e('Output:', 'geodirectory');?></span>
                        <textarea name="geodir_wrapper_open_replace"
                                  placeholder='<div id="[id]" class="[class]">'><?php if (isset($tc['geodir_wrapper_open_replace'])) {
                                echo $tc['geodir_wrapper_open_replace'];
                            }?></textarea>
                    </td>
                </tr>


                <tr>
                    <td>
                        <small>geodir_wrapper_close</small>
                    </td>
                    <td><input disabled="disabled" type="text" name="geodir_wrapper_open_id"
                               placeholder="<?php _e('Not used', 'geodirectory');?>"/></td>
                    <td><input disabled="disabled" type="text" name="geodir_wrapper_open_class"
                               placeholder="<?php _e('Not used', 'geodirectory');?>"/></td>
                </tr>

                <tr class="gd-theme-comp-out">
                    <td colspan="3">
                        <span><?php _e('Output:', 'geodirectory');?></span>
                        <textarea name="geodir_wrapper_close_replace"
                                  placeholder='</div><!-- wrapper ends here-->'><?php if (isset($tc['geodir_wrapper_close_replace'])) {
                                echo $tc['geodir_wrapper_close_replace'];
                            }?></textarea>
                    </td>
                </tr>


                <tr>
                    <td>
                        <small>geodir_wrapper_content_open</small>
                    </td>
                    <td><input value="<?php if (isset($tc['geodir_wrapper_content_open_id'])) {
                            echo $tc['geodir_wrapper_content_open_id'];
                        }?>" type="text" name="geodir_wrapper_content_open_id" placeholder="geodir-wrapper-content"/>
                    </td>
                    <td><input value="<?php if (isset($tc['geodir_wrapper_content_open_class'])) {
                            echo $tc['geodir_wrapper_content_open_class'];
                        }?>" type="text" name="geodir_wrapper_content_open_class" placeholder=""/></td>
                </tr>

                <tr class="gd-theme-comp-out">
                    <td colspan="3">
                        <span><?php _e('Output:', 'geodirectory');?></span>
                        <textarea name="geodir_wrapper_content_open_replace"
                                  placeholder='<div id="[id]" class="[class]" role="main" [width_css]>'><?php if (isset($tc['geodir_wrapper_content_open_replace'])) {
                                echo $tc['geodir_wrapper_content_open_replace'];
                            }?></textarea>
                    </td>
                </tr>


                <tr>
                    <td>
                        <small>geodir_wrapper_content_close</small>
                    </td>
                    <td><input disabled="disabled" type="text" name="geodir_wrapper_content_close_id"
                               placeholder="<?php _e('Not used', 'geodirectory');?>"/></td>
                    <td><input disabled="disabled" type="text" name="geodir_wrapper_content_close_class"
                               placeholder="<?php _e('Not used', 'geodirectory');?>"/></td>
                </tr>

                <tr class="gd-theme-comp-out">
                    <td colspan="3">
                        <span><?php _e('Output:', 'geodirectory');?></span>
                        <textarea name="geodir_wrapper_content_close_replace"
                                  placeholder='</div><!-- content ends here-->'><?php if (isset($tc['geodir_wrapper_content_close_replace'])) {
                                echo $tc['geodir_wrapper_content_close_replace'];
                            }?></textarea>
                    </td>
                </tr>

                <tr>
                    <td>
                        <small>geodir_article_open</small>
                    </td>
                    <td><input value="<?php if (isset($tc['geodir_article_open_id'])) {
                            echo $tc['geodir_article_open_id'];
                        }?>" type="text" name="geodir_article_open_id" placeholder="geodir-wrapper-content"/></td>
                    <td><input value="<?php if (isset($tc['geodir_article_open_class'])) {
                            echo $tc['geodir_article_open_class'];
                        }?>" type="text" name="geodir_article_open_class" placeholder=""/></td>
                </tr>

                <tr class="gd-theme-comp-out">
                    <td colspan="3">
                        <span><?php _e('Output:', 'geodirectory');?></span>
                        <textarea name="geodir_article_open_replace"
                                  placeholder='<article  id="[id]" class="[class]" itemscope itemtype="[itemtype]">'><?php if (isset($tc['geodir_article_open_replace'])) {
                                echo $tc['geodir_article_open_replace'];
                            }?></textarea>
                    </td>
                </tr>

                <tr>
                    <td>
                        <small>geodir_article_close</small>
                    </td>
                    <td><input disabled="disabled" type="text" name="geodir_article_close_id"
                               placeholder="<?php _e('Not used', 'geodirectory');?>"/></td>
                    <td><input disabled="disabled" type="text" name="geodir_article_close_class"
                               placeholder="<?php _e('Not used', 'geodirectory');?>"/></td>
                </tr>

                <tr class="gd-theme-comp-out">
                    <td colspan="3">
                        <span><?php _e('Output:', 'geodirectory');?></span>
                        <textarea name="geodir_article_close_replace"
                                  placeholder='</article><!-- article ends here-->'><?php if (isset($tc['geodir_article_close_replace'])) {
                                echo $tc['geodir_article_close_replace'];
                            }?></textarea>
                    </td>
                </tr>

                <tr>
                    <td>
                        <small>geodir_sidebar_right_open</small>
                    </td>
                    <td><input value="<?php if (isset($tc['geodir_sidebar_right_open_id'])) {
                            echo $tc['geodir_sidebar_right_open_id'];
                        }?>" type="text" name="geodir_sidebar_right_open_id" placeholder="geodir-sidebar-right"/></td>
                    <td><input value="<?php if (isset($tc['geodir_sidebar_right_open_class'])) {
                            echo $tc['geodir_sidebar_right_open_class'];
                        }?>" type="text" name="geodir_sidebar_right_open_class"
                               placeholder="geodir-sidebar-right geodir-listings-sidebar-right"/></td>
                </tr>

                <tr class="gd-theme-comp-out">
                    <td colspan="3">
                        <span><?php _e('Output:', 'geodirectory');?></span>
                        <textarea name="geodir_sidebar_right_open_replace"
                                  placeholder='<aside  id="[id]" class="[class]" role="complementary" itemscope itemtype="[itemtype]" [width_css]>'><?php if (isset($tc['geodir_sidebar_right_open_replace'])) {
                                echo $tc['geodir_sidebar_right_open_replace'];
                            }?></textarea>
                    </td>
                </tr>

                <tr>
                    <td>
                        <small>geodir_sidebar_right_close</small>
                    </td>
                    <td><input disabled="disabled" type="text" name="geodir_sidebar_right_close_id"
                               placeholder="<?php _e('Not used', 'geodirectory');?>"/></td>
                    <td><input disabled="disabled" type="text" name="geodir_sidebar_right_close_class"
                               placeholder="<?php _e('Not used', 'geodirectory');?>"/></td>
                </tr>

                <tr class="gd-theme-comp-out">
                    <td colspan="3">
                        <span><?php _e('Output:', 'geodirectory');?></span>
                        <textarea name="geodir_sidebar_right_close_replace"
                                  placeholder='</aside><!-- sidebar ends here-->'><?php if (isset($tc['geodir_sidebar_right_close_replace'])) {
                                echo $tc['geodir_sidebar_right_close_replace'];
                            }?></textarea>
                    </td>
                </tr>


                <tr>
                    <td>
                        <small>geodir_sidebar_left_open</small>
                    </td>
                    <td><input value="<?php if (isset($tc['geodir_sidebar_left_open_id'])) {
                            echo $tc['geodir_sidebar_left_open_id'];
                        }?>" type="text" name="geodir_sidebar_left_open_id" placeholder="geodir-sidebar-left"/></td>
                    <td><input value="<?php if (isset($tc['geodir_sidebar_left_open_class'])) {
                            echo $tc['geodir_sidebar_left_open_class'];
                        }?>" type="text" name="geodir_sidebar_left_open_class"
                               placeholder="geodir-sidebar-left geodir-listings-sidebar-left"/></td>
                </tr>

                <tr class="gd-theme-comp-out">
                    <td colspan="3">
                        <span><?php _e('Output:', 'geodirectory');?></span>
                        <textarea name="geodir_sidebar_left_open_replace"
                                  placeholder='<aside  id="[id]" class="[class]" role="complementary" itemscope itemtype="[itemtype]" [width_css]>'><?php if (isset($tc['geodir_sidebar_left_open_replace'])) {
                                echo $tc['geodir_sidebar_left_open_replace'];
                            }?></textarea>
                    </td>
                </tr>

                <tr>
                    <td>
                        <small>geodir_sidebar_left_close</small>
                    </td>
                    <td><input disabled="disabled" type="text" name="geodir_sidebar_left_close_id"
                               placeholder="<?php _e('Not used', 'geodirectory');?>"/></td>
                    <td><input disabled="disabled" type="text" name="geodir_sidebar_left_close_class"
                               placeholder="<?php _e('Not used', 'geodirectory');?>"/></td>
                </tr>

                <tr class="gd-theme-comp-out">
                    <td colspan="3">
                        <span><?php _e('Output:', 'geodirectory');?></span>
                        <textarea name="geodir_sidebar_left_close_replace"
                                  placeholder='</aside><!-- sidebar ends here-->'><?php if (isset($tc['geodir_sidebar_left_close_replace'])) {
                                echo $tc['geodir_sidebar_left_close_replace'];
                            }?></textarea>
                    </td>
                </tr>

                <tr>
                    <td>
                        <small>geodir_main_content_open</small>
                    </td>
                    <td><input value="<?php if (isset($tc['geodir_main_content_open_id'])) {
                            echo $tc['geodir_main_content_open_id'];
                        }?>" type="text" name="geodir_main_content_open_id" placeholder="geodir-main-content"/></td>
                    <td><input value="<?php if (isset($tc['geodir_main_content_open_class'])) {
                            echo $tc['geodir_main_content_open_class'];
                        }?>" type="text" name="geodir_main_content_open_class" placeholder="CURRENT-PAGE-page"/></td>
                </tr>

                <tr class="gd-theme-comp-out">
                    <td colspan="3">
                        <span><?php _e('Output:', 'geodirectory');?></span>
                        <textarea name="geodir_main_content_open_replace"
                                  placeholder='<main  id="[id]" class="[class]"  role="main">'><?php if (isset($tc['geodir_main_content_open_replace'])) {
                                echo $tc['geodir_main_content_open_replace'];
                            }?></textarea>
                    </td>
                </tr>

                <tr>
                    <td>
                        <small>geodir_main_content_close</small>
                    </td>
                    <td><input disabled="disabled" type="text" name="geodir_main_content_close_id"
                               placeholder="<?php _e('Not used', 'geodirectory');?>"/></td>
                    <td><input disabled="disabled" type="text" name="geodir_main_content_close_class"
                               placeholder="<?php _e('Not used', 'geodirectory');?>"/></td>
                </tr>

                <tr class="gd-theme-comp-out">
                    <td colspan="3">
                        <span><?php _e('Output:', 'geodirectory');?></span>
                        <textarea name="geodir_main_content_close_replace"
                                  placeholder='</main><!-- main ends here-->'><?php if (isset($tc['geodir_main_content_close_replace'])) {
                                echo $tc['geodir_main_content_close_replace'];
                            }?></textarea>
                    </td>
                </tr>


                </tbody>
            </table>

            <h4><?php _e('Other Actions', 'geodirectory');?></h4>

            <table class="form-table gd-theme-compat-table">
                <tbody>
                <tr>
                    <td><strong><?php _e('Hook', 'geodirectory');?></strong></td>
                    <td><strong><?php _e('Content', 'geodirectory');?></strong></td>
                </tr>


                <tr>
                    <td>
                        <small>geodir_top_content</small>
                    </td>
                    <td><textarea name="geodir_top_content_add"
                                  placeholder=''><?php if (isset($tc['geodir_top_content_add'])) {
                                echo $tc['geodir_top_content_add'];
                            }?></textarea></td>
                </tr>

                <tr>
                    <td>
                        <small>geodir_before_main_content</small>
                    </td>
                    <td><textarea name="geodir_before_main_content_add"
                                  placeholder=''><?php if (isset($tc['geodir_before_main_content_add'])) {
                                echo $tc['geodir_before_main_content_add'];
                            }?></textarea></td>
                </tr>


                </tbody>
            </table>


            <h4><?php _e('Other Filters', 'geodirectory');?></h4>

            <table class="form-table gd-theme-compat-table">
                <tbody>
                <tr>
                    <td><strong><?php _e('Filter', 'geodirectory');?></strong></td>
                    <td><strong><?php _e('Content', 'geodirectory');?></strong></td>
                </tr>

                <tr>
                    <td>
                        <small>geodir_full_page_class</small>
                    </td>
                    <td><textarea name="geodir_full_page_class_filter"
                                  placeholder='geodir_full_page clearfix'><?php if (isset($tc['geodir_full_page_class_filter'])) {
                                echo $tc['geodir_full_page_class_filter'];
                            }?></textarea></td>
                </tr>

                <tr>
                    <td>
                        <small>geodir_before_widget</small>
                    </td>
                    <td><textarea name="geodir_before_widget_filter"
                                  placeholder='<section id="%1$s" class="widget geodir-widget %2$s">'><?php if (isset($tc['geodir_before_widget_filter'])) {
                                echo $tc['geodir_before_widget_filter'];
                            }?></textarea></td>
                </tr>

                <tr>
                    <td>
                        <small>geodir_after_widget</small>
                    </td>
                    <td><textarea name="geodir_after_widget_filter"
                                  placeholder='</section>'><?php if (isset($tc['geodir_after_widget_filter'])) {
                                echo $tc['geodir_after_widget_filter'];
                            }?></textarea></td>
                </tr>

                <tr>
                    <td>
                        <small>geodir_before_title</small>
                    </td>
                    <td><textarea name="geodir_before_title_filter"
                                  placeholder='<h3 class="widget-title">'><?php if (isset($tc['geodir_before_title_filter'])) {
                                echo $tc['geodir_before_title_filter'];
                            }?></textarea></td>
                </tr>

                <tr>
                    <td>
                        <small>geodir_after_title</small>
                    </td>
                    <td><textarea name="geodir_after_title_filter"
                                  placeholder='</h3>'><?php if (isset($tc['geodir_after_title_filter'])) {
                                echo $tc['geodir_after_title_filter'];
                            }?></textarea></td>
                </tr>

                <tr>
                    <td>
                        <small>geodir_menu_li_class</small>
                    </td>
                    <td><textarea name="geodir_menu_li_class_filter"
                                  placeholder='menu-item'><?php if (isset($tc['geodir_menu_li_class_filter'])) {
                                echo $tc['geodir_menu_li_class_filter'];
                            }?></textarea></td>
                </tr>

                <tr>
                    <td>
                        <small>geodir_sub_menu_ul_class</small>
                    </td>
                    <td><textarea name="geodir_sub_menu_ul_class_filter"
                                  placeholder='sub-menu'><?php if (isset($tc['geodir_sub_menu_ul_class_filter'])) {
                                echo $tc['geodir_sub_menu_ul_class_filter'];
                            }?></textarea></td>
                </tr>

                <tr>
                    <td>
                        <small>geodir_sub_menu_li_class</small>
                    </td>
                    <td><textarea name="geodir_sub_menu_li_class_filter"
                                  placeholder='menu-item'><?php if (isset($tc['geodir_sub_menu_li_class_filter'])) {
                                echo $tc['geodir_sub_menu_li_class_filter'];
                            }?></textarea></td>
                </tr>

                <tr>
                    <td>
                        <small>geodir_menu_a_class</small>
                    </td>
                    <td><textarea name="geodir_menu_a_class_filter"
                                  placeholder=''><?php if (isset($tc['geodir_menu_a_class_filter'])) {
                                echo $tc['geodir_menu_a_class_filter'];
                            }?></textarea></td>
                </tr>

                <tr>
                    <td>
                        <small>geodir_sub_menu_a_class</small>
                    </td>
                    <td><textarea name="geodir_sub_menu_a_class_filter"
                                  placeholder=''><?php if (isset($tc['geodir_sub_menu_a_class_filter'])) {
                                echo $tc['geodir_sub_menu_a_class_filter'];
                            }?></textarea></td>
                </tr>


                <tr>
                    <td>
                        <small>geodir_location_switcher_menu_li_class</small>
                    </td>
                    <td><textarea name="geodir_location_switcher_menu_li_class_filter"
                                  placeholder='menu-item menu-item-type-social menu-item-type-social gd-location-switcher'><?php if (isset($tc['geodir_location_switcher_menu_li_class_filter'])) {
                                echo $tc['geodir_location_switcher_menu_li_class_filter'];
                            }?></textarea></td>
                </tr>

                <tr>
                    <td>
                        <small>geodir_location_switcher_menu_a_class</small>
                    </td>
                    <td><textarea name="geodir_location_switcher_menu_a_class_filter"
                                  placeholder=''><?php if (isset($tc['geodir_location_switcher_menu_a_class_filter'])) {
                                echo $tc['geodir_location_switcher_menu_a_class_filter'];
                            }?></textarea></td>
                </tr>

                <tr>
                    <td>
                        <small>geodir_location_switcher_menu_sub_ul_class</small>
                    </td>
                    <td><textarea name="geodir_location_switcher_menu_sub_ul_class_filter"
                                  placeholder='sub-menu'><?php if (isset($tc['geodir_location_switcher_menu_sub_ul_class_filter'])) {
                                echo $tc['geodir_location_switcher_menu_sub_ul_class_filter'];
                            }?></textarea></td>
                </tr>

                <tr>
                    <td>
                        <small>geodir_location_switcher_menu_sub_li_class</small>
                    </td>
                    <td><textarea name="geodir_location_switcher_menu_sub_li_class_filter"
                                  placeholder='menu-item gd-location-switcher-menu-item'><?php if (isset($tc['geodir_location_switcher_menu_sub_li_class_filter'])) {
                                echo $tc['geodir_location_switcher_menu_sub_li_class_filter'];
                            }?></textarea></td>
                </tr>



                <?php
                /**
                 * Allows more filter setting to be added to theme compatibility settings page.
                 *
                 * Called after the last setting in "Other filters" section of theme compatibility settings.
                 *
                 * @since 1.4.0
                 */
                do_action('gd_compat_other_filters');?>

                </tbody>
            </table>


            <h4><?php _e('Required CSS', 'geodirectory');?></h4>

            <table class="form-table gd-theme-compat-table">
                <tbody>
                <tr>
                    <td><textarea name="geodir_theme_compat_css"
                                  placeholder=''><?php if (isset($tc['geodir_theme_compat_css'])) {
                                echo $tc['geodir_theme_compat_css'];
                            }?></textarea></td>
                </tr>


                </tbody>
            </table>

            <h4><?php _e('Required JS', 'geodirectory');?></h4>

            <table class="form-table gd-theme-compat-table">
                <tbody>
                <tr>
                    <td><textarea name="geodir_theme_compat_js"
                                  placeholder=''><?php if (isset($tc['geodir_theme_compat_js'])) {
                                echo $tc['geodir_theme_compat_js'];
                            }?></textarea></td>
                </tr>


                </tbody>
            </table>


            <p class="submit">
                <input name="save" class="button-primary" type="submit"
                       value="<?php _e('Save changes', 'geodirectory');?>">
            </p>

        </div>
    </div>
<?php
}


/**
 * Displays settings form for the custom post type.
 *
 * @since 1.0.0
 * @package GeoDirectory
 */
function geodir_custom_post_type_form()
{
    $listing_type = ($_REQUEST['listing_type'] != '') ? $_REQUEST['listing_type'] : 'gd_place';

    $sub_tab = ($_REQUEST['subtab'] != '') ? $_REQUEST['subtab'] : '';


    ?>

    <div class="gd-content-heading">
        <?php
        /**
         * Filter custom fields panel heading.
         *
         * @since 1.0.0
         * @param string $sub_tab Sub tab name.
         * @param string $listing_type Post type.
         */
        ?>
        <h3><?php echo apply_filters('geodir_custom_fields_panel_head', '', $sub_tab, $listing_type);?></h3>
    </div>
    <div id="container_general" class="clearfix">
        <div class="general-form-builder-frame">

            <div class="side-sortables" id="geodir-available-fields">
                <?php
                /**
                 * Filter custom field available fields heading.
                 *
                 * @since 1.0.0
                 * @param string $sub_tab Sub tab name.
                 * @param string $listing_type Post type.
                 */
                ?>
                <h3 class="hndle"><span><?php echo apply_filters('geodir_cf_panel_available_fields_head', '', $sub_tab, $listing_type);?>
							</span></h3>
                <?php
                /**
                 * Filter custom field available fields note text.
                 *
                 * @since 1.0.0
                 * @param string $sub_tab Sub tab name.
                 * @param string $listing_type Post type.
                 */
                ?>
                <p><?php echo apply_filters('geodir_cf_panel_available_fields_note', '', $sub_tab, $listing_type);?></p>

                <h3><?php _e('Setup New Field','geodirectory');?></h3>
                <div class="inside">

                    <div id="gt-form-builder-tab" class="gt-tabs-panel">

                        <?php
                        /**
                         * Adds the available fields to the custom fields settings page per post type.
                         *
                         * @since 1.0.0
                         * @param string $sub_tab The current settings tab name.
                         */
                        do_action('geodir_manage_available_fields', $sub_tab); ?>

                        <div style="clear:both"></div>
                    </div>

                </div>

                <?php if($sub_tab=='custom_fields'){ ?>

                <h3><?php _e('Predefined Fields','geodirectory');?></h3>
                <div class="inside">

                    <div id="gt-form-builder-tab" class="gt-tabs-panel">

                        <?php
                        /**
                         * Adds the available fields to the custom fields predefined settings page per post type.
                         *
                         * @since 1.6.9
                         * @param string $sub_tab The current settings tab name.
                         */
                        do_action('geodir_manage_available_fields_predefined', $sub_tab); ?>

                        <div style="clear:both"></div>
                    </div>

                </div>

                <h3><?php _e('Custom Fields','geodirectory');?></h3>
                <div class="inside">

                    <div id="gt-form-builder-tab" class="gt-tabs-panel">

                        <?php
                        /**
                         * Adds the available fields to the custom fields custom added settings page per post type.
                         *
                         * @since 1.6.9
                         * @param string $sub_tab The current settings tab name.
                         */
                        do_action('geodir_manage_available_fields_custom', $sub_tab); ?>

                        <div style="clear:both"></div>
                    </div>

                </div>

                <?php }?>


        </div>
            <!--side-sortables -->


            <div class="side-sortables" id="geodir-selected-fields">
                <h3 class="hndle">
                    <?php
                    /**
                     * Filter custom field selected fields heading.
                     *
                     * @since 1.0.0
                     * @param string $sub_tab Sub tab name.
                     * @param string $listing_type Post type.
                     */
                    ?>
                    <span><?php echo apply_filters('geodir_cf_panel_selected_fields_head', '', $sub_tab, $listing_type);?></span>
                </h3>
                <?php
                /**
                 * Filter custom field selected fields note text.
                 *
                 * @since 1.0.0
                 * @param string $sub_tab Sub tab name.
                 * @param string $listing_type Post type.
                 */
                ?>
                <p><?php echo apply_filters('geodir_cf_panel_selected_fields_note', '', $sub_tab, $listing_type);?></p>

                <div class="inside">

                    <div id="gt-form-builder-tab" class="gt-tabs-panel">
                        <div class="field_row_main">
                            <?php
                            /**
                             * Adds the selected fields and setting to the custom fields settings page per post type.
                             *
                             * @since 1.0.0
                             * @param string $sub_tab The current settings tab name.
                             */
                            do_action('geodir_manage_selected_fields', $sub_tab); ?>
                        </div>
                        <div style="clear:both"></div>
                    </div>

                </div>
            </div>

        </div>
        <!--general-form-builder-frame -->
    </div> <!--container_general -->

<?php
}

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
                    <td><?php _e('Sync GD tags', 'geodirectory');?></td>
                    <td>
                        <small><?php _e('This tool can be used when tags are showing in the backend but missing from the front end.', 'geodirectory');?></small>
                    </td>
                    <td><input type="button" value="<?php _e('Run', 'geodirectory');?>"
                               class="button-primary geodir_diagnosis_button" data-diagnose="tags_sync"/>
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
