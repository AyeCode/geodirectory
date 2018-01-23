<?php
/**
 * Geodirectory Custom Post Types/Taxonomies
 *
 * Inits custom post types and taxonomies
 *
 * @package     GeoDirectory
 * @category    Core
 * @author      WPGeoDirectory
 */

/**
 * Contains custom post types/taxonomies related functions.
 *
 * @since 1.0.0
 * @package GeoDirectory
 */
include_once('custom_taxonomy_hooks_actions.php');


if (!function_exists('geodir_get_taxonomies')) {
    /**
     * Get all custom taxonomies.
     *
     * @since 1.0.0
     * @package GeoDirectory
     * @param string $post_type The post type.
     * @param bool $tages_taxonomies Is this a tag taxonomy?. Default: false.
     * @return array|bool Taxonomies on success. false on failure.
     */
    function geodir_get_taxonomies($post_type = '', $tages_taxonomies = false)
    {

        $taxonomies = array();
        $gd_taxonomies = array();

        if ($taxonomies = geodir_get_option('taxonomies')) {


            $gd_taxonomies = array_keys($taxonomies);


            if ($post_type != '')
                $gd_taxonomies = array();

            $i = 0;
            foreach ($taxonomies as $taxonomy => $args) {

                if ($post_type != '' && $args['object_type'] == $post_type)
                    $gd_taxonomies[] = $taxonomy;

                if ($tages_taxonomies === false && strpos($taxonomy, '_tag') !== false) {
                    if (array_search($taxonomy, $gd_taxonomies) !== false)
                        unset($gd_taxonomies[array_search($taxonomy, $gd_taxonomies)]);
                }

            }

            $gd_taxonomies = array_values($gd_taxonomies);
        }

        /**
         * Filter the taxonomies.
         *
         * @since 1.0.0
         * @param array $gd_taxonomies The taxonomy array.
         */
        $taxonomies = apply_filters('geodir_taxonomy', $gd_taxonomies);

        if (!empty($taxonomies)) {
            return $taxonomies;
        } else {
            return false;
        }
    }
}

if (!function_exists(' geodir_get_categories_dl')) {
    /**
     * Get categories dropdown HTML.
     *
     * @since 1.0.0
     * @package GeoDirectory
     * @param string $post_type The post type.
     * @param string $selected The selected value.
     * @param bool $tages_taxonomies Is this a tag taxonomy?. Default: false.
     * @param bool $echo Prints the HTML when set to true. Default: true.
     * @return void|string Dropdown HTML.
     */
    function  geodir_get_categories_dl($post_type = '', $selected = '', $is_tags = false, $echo = true)
    {

        $tax = new GeoDir_Admin_Taxonomies();
        $html = $tax->get_category_select($post_type, $selected, $is_tags , $echo);

        if (!$echo)
            return $html;
    }
}


/**
 * Get post type listing slug.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @param string $object_type The post type.
 * @return bool|string Slug on success. false on failure.
 */
function geodir_get_listing_slug($object_type = '')
{

    $listing_slug = '';

    $post_types = geodir_get_posttypes('array');
    $taxonomies = geodir_get_option('taxonomies');


    if ($object_type != '') {
        if (!empty($post_types) && array_key_exists($object_type, $post_types)) {

            $object_info = $post_types[$object_type];
            $listing_slug = $object_info['listing_slug'];
        } elseif (!empty($taxonomies) && array_key_exists($object_type, $taxonomies)) {
            $object_info = $taxonomies[$object_type];
            $listing_slug = $object_info['listing_slug'];
        }

    }

    if (!empty($listing_slug))
        return $listing_slug;
    else
        return false;
}


/**
 * Get a taxonomy post type.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wp_query WordPress Query object.
 * @param string $taxonomy The WordPress taxonomy string.
 * @return bool|string Post type on success. false on failure.
 */
function geodir_get_taxonomy_posttype($taxonomy = '')
{
    global $wp_query;

    $post_type = array();
    $taxonomies = array();

    if (!empty($taxonomy)) {
        $taxonomies[] = $taxonomy;
    } elseif (isset($wp_query->tax_query->queries)) {
        $tax_arr = $wp_query->tax_query->queries;
        //if tax query has 'relation' set then it will break wp_list_pluck so we remove it
        if(isset( $tax_arr['relation'])){unset( $tax_arr['relation']);}
        $taxonomies = wp_list_pluck($tax_arr, 'taxonomy');
    }

    if (!empty($taxonomies)) {
        foreach (geodir_get_posttypes() as $pt) {
            $object_taxonomies = $pt === 'attachment' ? get_taxonomies_for_attachments() : get_object_taxonomies($pt);
            if (array_intersect($taxonomies, $object_taxonomies))
                $post_type[] = $pt;
        }
    }

    if (!empty($post_type))
        return $post_type[0];
    else
        return false;
}

if (!function_exists('geodir_custom_taxonomy_walker')) {
    /**
     * Custom taxonomy walker function.
     *
     * @since 1.0.0
     * @package GeoDirectory
     * @param string $cat_taxonomy The taxonomy name.
     * @param int $cat_parent The parent term ID.
     * @param bool $hide_empty Hide empty taxonomies? Default: false.
     * @param int $pading CSS padding in pixels.
     * @return string|void taxonomy HTML.
     */
    function geodir_custom_taxonomy_walker($cat_taxonomy, $cat_parent = 0, $hide_empty = false, $pading = 0)
    {
        global $cat_display, $post_cat, $exclude_cats;

        $search_terms = trim($post_cat, ",");

        $search_terms = explode(",", $search_terms);

        $cat_terms = get_terms($cat_taxonomy, array('parent' => $cat_parent, 'hide_empty' => $hide_empty, 'exclude' => $exclude_cats));

        $display = '';
        $onchange = '';
        $term_check = '';
        $main_list_class = '';
        $out = '';
        //If there are terms, start displaying
        if (count($cat_terms) > 0) {
            //Displaying as a list
            $p = $pading * 20;
            $pading++;


            if ((!geodir_is_page('listing')) || (is_search() && $_REQUEST['search_taxonomy'] == '')) {
                if ($cat_parent == 0) {
                    $list_class = 'main_list gd-parent-cats-list gd-cats-display-' . $cat_display;
                    $main_list_class = 'class="main_list_selecter"';
                } else {
                    //$display = 'display:none';
                    $list_class = 'sub_list gd-sub-cats-list';
                }
            }

            if ($cat_display == 'checkbox' || $cat_display == 'radio') {
                $p = 0;
                $out = '<div class="' . $list_class . ' gd-cat-row-' . $cat_parent . '" style="margin-left:' . $p . 'px;' . $display . ';">';
            }

            foreach ($cat_terms as $cat_term) {

                $checked = '';

                if (in_array($cat_term->term_id, $search_terms)) {
                    if ($cat_display == 'select' || $cat_display == 'multiselect')
                        $checked = 'selected="selected"';
                    else
                        $checked = 'checked="checked"';
                }

                if ($cat_display == 'radio')
                    $out .= '<span style="display:block" ><input type="radio" field_type="radio" name="post_category[' . $cat_term->taxonomy . '][]" ' . $main_list_class . ' alt="' . $cat_term->taxonomy . '" title="' . geodir_utf8_ucfirst($cat_term->name) . '" value="' . $cat_term->term_id . '" ' . $checked . $onchange . ' id="gd-cat-' . $cat_term->term_id . '" >' . $term_check . geodir_utf8_ucfirst($cat_term->name) . '</span>';
                elseif ($cat_display == 'select' || $cat_display == 'multiselect')
                    $out .= '<option ' . $main_list_class . ' style="margin-left:' . $p . 'px;" alt="' . $cat_term->taxonomy . '" title="' . geodir_utf8_ucfirst($cat_term->name) . '" value="' . $cat_term->term_id . '" ' . $checked . $onchange . ' >' . $term_check . geodir_utf8_ucfirst($cat_term->name) . '</option>';

                else {
                    $out .= '<span style="display:block"><input style="display:inline-block" type="checkbox" field_type="checkbox" name="post_category[' . $cat_term->taxonomy . '][]" ' . $main_list_class . ' alt="' . $cat_term->taxonomy . '" title="' . geodir_utf8_ucfirst($cat_term->name) . '" value="' . $cat_term->term_id . '" ' . $checked . $onchange . ' id="gd-cat-' . $cat_term->term_id . '" >' . $term_check . geodir_utf8_ucfirst($cat_term->name) . '</span>';
                }

                // Call recurson to print sub cats
                $out .= geodir_custom_taxonomy_walker($cat_taxonomy, $cat_term->term_id, $hide_empty, $pading);

            }

            if ($cat_display == 'checkbox' || $cat_display == 'radio')
                $out .= '</div>';

            return $out;
        }
        return;
    }
}

if (!function_exists('geodir_custom_taxonomy_walker2')) {
    /**
     * Custom taxonomy walker function.
     *
     * @since 1.0.0
     * @package GeoDirectory
     * @global object $post WordPress Post object.
     * @global object $gd_session GeoDirectory Session object.
     * @param string $cat_taxonomy The taxonomy name.
     * @param string $cat_limit Number of categories to display.
     */
    function geodir_custom_taxonomy_walker2($cat_taxonomy, $cat_limit = '')
    {
        $post_category = '';
        $post_category_str = '';
        global $exclude_cats, $gd_session;

        $cat_exclude = '';
        if (is_array($exclude_cats) && !empty($exclude_cats))
            $cat_exclude = serialize($exclude_cats);

        if (isset($_REQUEST['backandedit'])) {
            $post = (object)$gd_session->get('listing');

            if (!is_array($post->post_category[$cat_taxonomy]))
                $post_category = $post->post_category[$cat_taxonomy];

            $post_categories = $post->post_category_str;
            if (!empty($post_categories) && array_key_exists($cat_taxonomy, $post_categories))
                $post_category_str = $post_categories[$cat_taxonomy];

        } elseif ((geodir_is_page('add-listing') && isset($_REQUEST['pid']) && $_REQUEST['pid'] != '') || (is_admin())) {
            global $post;

            $post_category = geodir_get_post_meta($post->ID, $cat_taxonomy, true);
            if (empty($post_category) && isset($post->{$cat_taxonomy})) {
                $post_category = $post->{$cat_taxonomy};
            }

            $post_categories = get_post_meta($post->ID, 'post_categories', true);

            if (empty($post_category) && !empty($post_categories) && !empty($post_categories[$cat_taxonomy])) {
                foreach (explode(",", $post_categories[$cat_taxonomy]) as $cat_part) {
                    if (is_numeric($cat_part)) {
                        $cat_part_arr[] = $cat_part;
                    }
                }
                if (is_array($cat_part_arr)) {
                    $post_category = implode(',', $cat_part_arr);
                }
            }

            if (!empty($post_category)) {
                $cat1 = array_filter(explode(',', $post_category));
                $post_category = ',' . implode(',', $cat1) . ',';

            }

            if ($post_category != '' && is_array($exclude_cats) && !empty($exclude_cats)) {

                $post_category_upd = explode(',', $post_category);
                $post_category_change = '';
                foreach ($post_category_upd as $cat) {

                    if (!in_array($cat, $exclude_cats) && $cat != '') {
                        $post_category_change .= ',' . $cat;
                    }
                }
                $post_category = $post_category_change;
            }


            if (!empty($post_categories) && array_key_exists($cat_taxonomy, $post_categories)) {
                $post_category_str = $post_categories[$cat_taxonomy];
            }
        }

        echo '<input type="hidden" id="cat_limit" value="' . $cat_limit . '" name="cat_limit[' . $cat_taxonomy . ']"  />';

        echo '<input type="hidden" id="post_category" value="' . $post_category . '" name="post_category[' . $cat_taxonomy . ']"  />';

        echo '<input type="hidden" id="post_category_str" value="' . $post_category_str . '" name="post_category_str[' . $cat_taxonomy . ']"  />';


        ?>
        <div class="cat_sublist">
            <?php

            $post_id = isset($post->ID) ? $post->ID : '';

            if ((geodir_is_page('add-listing') || is_admin()) && !empty($post_categories[$cat_taxonomy])) {

                geodir_editpost_categories_html($cat_taxonomy, $post_id, $post_categories);
            }
            ?>
        </div>
        <script type="text/javascript">

            function show_subcatlist(main_cat, catObj) {
                if (main_cat != '') {
					var url = '<?php echo geodir_get_ajax_url();?>';
                    var cat_taxonomy = '<?php echo $cat_taxonomy;?>';
                    var cat_exclude = '<?php echo base64_encode($cat_exclude);?>';
                    var cat_limit = jQuery('#' + cat_taxonomy).find('#cat_limit').val();
					<?php if ((int)$cat_limit > 0) { ?>
					var selected = parseInt(jQuery('#' + cat_taxonomy).find('.cat_sublist > div.post_catlist_item').length);
					if (cat_limit != '' && selected > 0 && selected >= cat_limit && cat_limit != 0) {
						alert("<?php echo esc_attr(wp_sprintf(__('You have reached category limit of %d categories.', 'geodirectory'), (int)$cat_limit));?>");
						return false;
					}
					<?php } ?>
                    jQuery.post(url, {
                        geodir_ajax: 'category_ajax',
                        cat_tax: cat_taxonomy,
                        main_catid: main_cat,
                        exclude: cat_exclude
                    }, function (data) {
                        if (data != '') {
                            jQuery('#' + cat_taxonomy).find('.cat_sublist').append(data);

                            setTimeout(function () {
                                jQuery('#' + cat_taxonomy).find('.cat_sublist').find('.geodir-select').trigger('geodir-select-init');
                            }, 200);


                        }
                        maincat_obj = jQuery('#' + cat_taxonomy).find('.main_cat_list');

                        if (cat_limit != '' && jQuery('#' + cat_taxonomy).find('.cat_sublist .geodir-select').length >= cat_limit) {
                            if (maincat_obj.find('.geodir-select').data('select2')) {
                                maincat_obj.find('.geodir-select').removeClass('enhanced').select2('destroy');
                            }
                            maincat_obj.hide();
                        } else {
                            maincat_obj.show();
                            if (maincat_obj.find('.geodir-select').data('select2')) {
                                maincat_obj.find('.geodir-select').removeClass('enhanced').select2('destroy');
                            }
                            maincat_obj.find('.geodir-select').prop('selectedIndex', 0);
                            maincat_obj.find('.geodir-select').trigger('geodir-select-init');
                        }

                        update_listing_cat();

                    });
                }
                update_listing_cat();
            }

            function update_listing_cat(el) {
                var cat_taxonomy = '<?php echo $cat_taxonomy;?>';
                var cat_ids = '';
                var main_cat = '';
                var sub_cat = '';
                var post_cat_str = '';
                var cat_limit = jQuery('#' + cat_taxonomy).find('#cat_limit').val();
				
				var delEl = jQuery(el).closest('.post_catlist_item').find('input.listing_main_cat');
				if (typeof el != 'undefined' && jQuery(delEl).val()) {
					jQuery('.geodir_taxonomy_field').find('select > option[_hc="f"][value="'+jQuery(delEl).val()+'"]').attr('disabled', false);
				}
				jQuery('.geodir_taxonomy_field').find('input.listing_main_cat:checked').each(function() {
					var cV = jQuery(this).val();
					if (parseInt(cV) > 0) {
						jQuery('.geodir_taxonomy_field').find('select > option[_hc="f"][value="'+cV+'"]').attr('disabled', true);
					}
				});

                jQuery('#' + cat_taxonomy).find('.cat_sublist > div').each(function () {
                    main_cat = jQuery(this).find('.listing_main_cat').val();

                    if (jQuery(this).find('.geodir-select').length > 0)
                        sub_cat = jQuery(this).find('.geodir-select').val()

                    if (post_cat_str != '')
                        post_cat_str = post_cat_str + '#';

                    post_cat_str = post_cat_str + main_cat;

                    if (jQuery(this).find('.listing_main_cat').is(':checked')) {
                        cat_ids = cat_ids + ',' + main_cat;
                        post_cat_str = post_cat_str + ',y';

                        if (jQuery(this).find('.post_default_category input').is(':checked'))
                            post_cat_str = post_cat_str + ',d';

                    } else {
                        post_cat_str = post_cat_str + ',n';
                    }

                    if (sub_cat != '' && sub_cat) {
                        cat_ids = cat_ids + ',' + sub_cat;
                        post_cat_str = post_cat_str + ':' + sub_cat;
                    } else {
                        post_cat_str = post_cat_str + ':';
                    }

                });

                maincat_obj = jQuery('#' + cat_taxonomy).find('.main_cat_list');
                if (cat_limit != '' && jQuery('#' + cat_taxonomy).find('.cat_sublist > div.post_catlist_item').length >= cat_limit && cat_limit != 0) {
                    if (maincat_obj.find('.geodir-select').data('select2')) {
                        maincat_obj.find('.geodir-select').removeClass('enhanced').select2('destroy');
                    }
                    maincat_obj.hide();
                } else {
                    maincat_obj.show();
                    if (maincat_obj.find('.geodir-select').data('select2')) {
                        maincat_obj.find('.geodir-select').removeClass('enhanced').select2('destroy');
                    }
                    maincat_obj.find('.geodir-select').prop('selectedIndex', 0);
                    maincat_obj.find('.geodir-select').trigger('geodir-select-init');
                }

                jQuery('#' + cat_taxonomy).find('#post_category').val(cat_ids);
                jQuery('#' + cat_taxonomy).find('#post_category_str').val(post_cat_str);
            }
            jQuery(function () {
                update_listing_cat();
            })


        </script>
        <?php
        if (!empty($post_categories) && array_key_exists($cat_taxonomy, $post_categories)) {
            $post_cat_str = $post_categories[$cat_taxonomy];
            $post_cat_array = explode("#", $post_cat_str);
            if (count($post_cat_array) >= $cat_limit && $cat_limit != 0)
                $style = "display:none;";
        }
        ?>
        <div class="main_cat_list" style=" <?php if (isset($style)) {
            echo $style;
        }?> ">
            <?php geodir_get_catlist($cat_taxonomy, 0);  // print main categories list
            ?>
        </div>
    <?php

    }
}

/**
 * Category Selection Interface in add/edit listing form.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @param string $request_taxonomy The taxonomy name.
 * @param int $parrent The parent term ID.
 * @param bool|string $selected The selected value.
 * @param bool $main_selected Not yet implemented.
 * @param bool $default Is this the default category? Default: false.
 * @param string $exclude Excluded terms list. Serialized base64 encoded string.
 */
function geodir_addpost_categories_html($request_taxonomy, $parrent, $selected = false, $main_selected = true, $default = false, $exclude = '')
{
    global $exclude_cats;

    if ($exclude != '') {
        $exclude_cats = maybe_unserialize(base64_decode($exclude));

        if(is_array( $exclude_cats)){
            $exclude_cats = array_map( 'intval', $exclude_cats );
        }else{
            $exclude_cats = intval($exclude_cats);
        }

    }

    if ((is_array($exclude_cats) && !empty($exclude_cats) && !in_array($parrent, $exclude_cats)) ||
        (!is_array($exclude_cats) || empty($exclude_cats))
    ) {
        ?>

        <?php $main_cat = get_term($parrent, $request_taxonomy); ?>

        <div class="post_catlist_item">
            <span class="gd-catlist-remove" onclick="jQuery(this).closest('div').remove();update_listing_cat(this);"><i class="fa fa-times"></i></span>
            <div class="gd-catlist-chkbox gd-catlist-row">
                <input type="checkbox" value="<?php echo $main_cat->term_id;?>" class="listing_main_cat" onchange="if(jQuery(this).is(':checked')){jQuery(this).closest('div').find('.post_default_category').prop('checked',false).show();}else{jQuery(this).closest('div').find('.post_default_category').prop('checked',false).hide();};update_listing_cat()" checked="checked" disabled="disabled"/> 
                <span> <?php printf(__('Add listing in %s category', 'geodirectory'), geodir_ucwords($main_cat->name));?></span>
            </div>
            <div class="post_default_category gd-catlist-row">
                <input id="post_default_category" type="radio" name="post_default_category" value="<?php echo $main_cat->term_id;?>" onchange="update_listing_cat()" <?php if ($default) echo ' checked="checked" ';?> /> 
                <span><?php printf(__('Set %s as default category', 'geodirectory'), geodir_ucwords($main_cat->name));?> </span>
            </div>
            <?php
            $cat_terms = get_terms($request_taxonomy, array('parent' => $main_cat->term_id, 'hide_empty' => false, 'exclude' => $exclude_cats));
            if (!empty($cat_terms)) { ?>
                <div class="gd-catlist-subcatlist gd-catlist-row">
                    <span> <?php printf(__('Add listing in category', 'geodirectory')); ?></span>
                    <?php geodir_get_catlist($request_taxonomy, $parrent, $selected) ?>
                </div>
            <?php } ?>
        </div>

    <?php }
}


/**
 * Categories HTML for edit post page.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @param string $request_taxonomy The taxonomy ID.
 * @param int $request_postid The post ID.
 * @param array $post_categories The post catagories.
 */
function geodir_editpost_categories_html($request_taxonomy, $request_postid, $post_categories)
{

    if (!empty($post_categories) && array_key_exists($request_taxonomy, $post_categories)) {
        $post_cat_str = $post_categories[$request_taxonomy];
        $post_cat_array = explode("#", $post_cat_str);
        if (is_array($post_cat_array)) {
            $post_cat_array = array_unique( $post_cat_array );

			foreach ($post_cat_array as $post_cat_html) {

                $post_cat_info = explode(":", $post_cat_html);
                $post_maincat_str = $post_cat_info[0];

                if (!empty($post_maincat_str)) {
                    $post_maincat_info = explode(",", $post_maincat_str);
                    $post_maincat_id = $post_maincat_info[0];
                    ($post_maincat_info[1] == 'y') ? $post_maincat_selected = true : $post_maincat_selected = false;
                    (end($post_maincat_info) == 'd') ? $post_maincat_default = true : $post_maincat_default = false;
                }
                $post_sub_catid = '';
                if (isset($post_cat_info[1]) && !empty($post_cat_info[1])) {
                    $post_sub_catid = (int)$post_cat_info[1];
                }

                geodir_addpost_categories_html($request_taxonomy, $post_maincat_id, $post_sub_catid, $post_maincat_selected, $post_maincat_default);

            }
        } else {

            $post_cat_info = explode(":", $post_cat_str);
            $post_maincat_str = $post_cat_info[0];

            $post_sub_catid = '';

            if (!empty($post_maincat_str)) {
                $post_maincat_info = explode(",", $post_maincat_str);
                $post_maincat_id = $post_maincat_info[0];
                ($post_maincat_info[1] == 'y') ? $post_maincat_selected = true : $post_maincat_selected = false;
                (end($post_maincat_info) == 'd') ? $post_maincat_default = true : $post_maincat_default = false;
            }

            if (isset($post_cat_info[1]) && !empty($post_cat_info[1])) {
                $post_sub_catid = (int)$post_cat_info[1];
            }

            geodir_addpost_categories_html($request_taxonomy, $post_maincat_id, $post_sub_catid, $post_maincat_selected, $post_maincat_default);

        }
    }
}

/**
 * Get terms of a taxonomy as dropdown.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @param string $cat_taxonomy The taxonomy name.
 * @param int $parrent The parent term ID. Default: 0.
 * @param bool|string $selected The selected value. Default: false.
 */
function geodir_get_catlist($cat_taxonomy, $parrent = 0, $selected = false)
{
    global $exclude_cats;

    $cat_terms = get_terms($cat_taxonomy, array('parent' => $parrent, 'hide_empty' => false, 'exclude' => $exclude_cats));

    if (!empty($cat_terms)) {
        $onchange = '';
        $onchange = ' onchange="show_subcatlist(this.value, this)" ';

        $option_selected = '';
        if (!$selected)
            $option_slected = ' selected="selected" ';

        echo '<select field_type="select" id="' . sanitize_text_field($cat_taxonomy) . '" class="geodir-select" ' . $onchange . ' option-ajaxChosen="false" >';

        echo '<option value="" ' . $option_selected . ' >' . __('Select Category', 'geodirectory') . '</option>';

        foreach ($cat_terms as $cat_term) {
            $option_selected = '';
            if ($selected == $cat_term->term_id)
                $option_selected = ' selected="selected" ';

            // Count child terms
            $child_terms = get_terms( $cat_taxonomy, array( 'parent' => $cat_term->term_id, 'hide_empty' => false, 'exclude' => $exclude_cats, 'number' => 1 ) );
            $has_child = !empty( $child_terms ) ? 't' : 'f';

            echo '<option  ' . $option_selected . ' alt="' . $cat_term->taxonomy . '" title="' . geodir_utf8_ucfirst($cat_term->name) . '" value="' . $cat_term->term_id . '" _hc="' . $has_child . '" >' . geodir_utf8_ucfirst($cat_term->name) . '</option>';
        }
        echo '</select>';
    }
}





$gd_wpml_get_languages = "";
function gd_wpml_get_lang_from_url($url) {
    global $sitepress, $gd_wpml_get_languages;
    
    if (geodir_is_wpml()) {
        return $sitepress->get_language_from_url($url);
    }
    
    if (isset($_REQUEST['lang']) && $_REQUEST['lang']) {
        return $_REQUEST['lang'];
    }

    $url = str_replace(array("http://","https://"),"",$url);

    // site_url() seems to work better than get_bloginfo('url') here, WPML can change get_bloginfo('url') to add the lang.
    $site_url = str_replace(array("http://","https://"),"",site_url());

    $url = str_replace($site_url,"",$url);

    $segments = explode('/', trim($url, '/'));

    if ($gd_wpml_get_languages) {
        $langs = $gd_wpml_get_languages;
    } else {
        $gd_wpml_get_languages = $sitepress->get_active_languages();
    }

    if (isset($segments[0]) && $segments[0] && array_key_exists($segments[0], $gd_wpml_get_languages)) {
        return $segments[0];
    }

    return false;
}

function gd_wpml_slug_translation_turned_on($post_type) {

    global $sitepress;
    $settings = $sitepress->get_settings();
    return isset($settings['posts_slug_translation']['types'][$post_type])
    && $settings['posts_slug_translation']['types'][$post_type]
    && isset($settings['posts_slug_translation']['on'])
    && $settings['posts_slug_translation']['on'];
}




/**
 * Returns the term link with parameters.
 *
 * @since 1.0.0
 * @since 1.5.7 Changes for the neighbourhood system improvement.
 * @since 1.6.11 Details page add locations to the term links.
 * @package GeoDirectory
 * @param string $termlink The term link
 * @param object $term Not yet implemented.
 * @param string $taxonomy The taxonomy name.
 * @return string The term link.
 */
function geodir_term_link($termlink, $term, $taxonomy) {
    $geodir_taxonomies = geodir_get_taxonomies('', true);

    if (isset($taxonomy) && !empty($geodir_taxonomies) && in_array($taxonomy, $geodir_taxonomies)) {
        global $geodir_add_location_url, $gd_session;
        $include_location = false;
        $request_term = array();
        $add_location_url = geodir_get_option('geodir_add_location_url');
        $location_manager = defined('POST_LOCATION_TABLE') ? true : false;

        $listing_slug = geodir_get_listing_slug($taxonomy);

        if ($geodir_add_location_url != NULL && $geodir_add_location_url != '') {
            if ($geodir_add_location_url && $add_location_url) {
                $include_location = true;
            }
        } elseif ($add_location_url && $gd_session->get('gd_multi_location') == 1) {
            $include_location = true;
        } elseif ($add_location_url && $location_manager && geodir_is_page('detail')) {
            $include_location = true;
        }

        if ($include_location) {
            global $post;
            
            $neighbourhood_active = $location_manager && geodir_get_option('location_neighbourhoods') ? true : false;
            
            if (geodir_is_page('detail') && isset($post->country_slug)) {
                $location_terms = array(
                    'gd_country' => $post->country_slug,
                    'gd_region' => $post->region_slug,
                    'gd_city' => $post->city_slug
                );
                
                if ($neighbourhood_active && !empty($location_terms['gd_city']) && $gd_ses_neighbourhood = $gd_session->get('gd_neighbourhood')) {
                    $location_terms['gd_neighbourhood'] = $gd_ses_neighbourhood;
                }
            } else {
                $location_terms = geodir_get_current_location_terms('query_vars');
            }

            $geodir_show_location_url = geodir_get_option('geodir_show_location_url');
            $location_terms = geodir_remove_location_terms($location_terms);

            if (!empty($location_terms)) {
                $url_separator = '';

                if (get_option('permalink_structure') != '') {
                    $old_listing_slug = '/' . $listing_slug . '/';
                    $request_term = implode("/", $location_terms);
                    $new_listing_slug = '/' . $listing_slug . '/' . $request_term . '/';

                    $termlink = substr_replace($termlink, $new_listing_slug, strpos($termlink, $old_listing_slug), strlen($old_listing_slug));
                } else {
                    $termlink = geodir_getlink($termlink, $request_term);
                }
            }
        }

        // Alter the CPT slug is WPML is set to do so
        /* we can replace this with the below function
        if(function_exists('icl_object_id')){
            global $sitepress;
            $post_type = str_replace("category","",$taxonomy);
            $termlink = $sitepress->post_type_archive_link_filter( $termlink, $post_type);
        }*/

        // Alter the CPT slug if WPML is set to do so
        if (function_exists('icl_object_id')) {
            $post_types = geodir_get_posttypes('array');
            $post_type = str_replace("category","",$taxonomy);
            $post_type = str_replace("_tags","",$post_type);
            $slug = $post_types[$post_type]['rewrite']['slug'];
            if (geodir_wpml_is_post_type_translated($post_type) && gd_wpml_slug_translation_turned_on($post_type)) {
                global $sitepress;
                $default_lang = $sitepress->get_default_language();
                $language_code = gd_wpml_get_lang_from_url($termlink);
                if (!$language_code ) {
                    $language_code  = $default_lang;
                }

                $org_slug = $slug;
                $slug = apply_filters('wpml_translate_single_string', $slug, 'WordPress', 'URL slug: ' . $slug, $language_code);

                if (!$slug) {
                    $slug = $org_slug;
                }

                $termlink = trailingslashit(preg_replace("/" . preg_quote($org_slug, "/") . "/", $slug  ,$termlink, 1));
            }
        }
    }
    
    return $termlink;
}


/**
 * Checks whether a term exists or not.
 *
 * Returns term data on success, bool when failure.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @param int|string $term The term ID or slug.
 * @param string $taxonomy The taxonomy name.
 * @param int $parent Parent term ID.
 * @return bool|object Term data.
 */
function geodir_term_exists($term, $taxonomy = '', $parent = 0)
{
    global $wpdb;

    $select = "SELECT term_id FROM $wpdb->terms as t WHERE ";
    $tax_select = "SELECT tt.term_id, tt.term_taxonomy_id FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy as tt ON tt.term_id = t.term_id WHERE ";

    if (is_int($term)) {
        if (0 == $term)
            return 0;
        $where = 't.term_id = %d';
        if (!empty($taxonomy))
            return $wpdb->get_row($wpdb->prepare($tax_select . $where . " AND tt.taxonomy = %s", $term, $taxonomy), ARRAY_A);
        else
            return $wpdb->get_var($wpdb->prepare($select . $where, $term));
    }

    $term = trim(wp_unslash($term));

    if ('' === $slug = sanitize_title($term))
        return 0;

    $where = 't.slug = %s';

    $where_fields = array($slug);
    if (!empty($taxonomy)) {
        $parent = (int)$parent;
        if ($parent > 0) {
            $where_fields[] = $parent;
            $else_where_fields[] = $parent;
            $where .= ' AND tt.parent = %d';

        }

        $where_fields[] = $taxonomy;


        if ($result = $wpdb->get_row($wpdb->prepare("SELECT tt.term_id, tt.term_taxonomy_id FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy as tt ON tt.term_id = t.term_id WHERE $where AND tt.taxonomy = %s", $where_fields), ARRAY_A))
            return $result;

        return false;
    }

    if ($result = $wpdb->get_var($wpdb->prepare("SELECT term_id FROM $wpdb->terms as t WHERE $where", $where_fields)))
        return $result;

    return false;
}

/**
 * Reset term icon values.
 *
 * @since 1.0.0
 * @package GeoDirectory
 */
function geodir_get_term_icon_rebuild() {
    geodir_update_option( 'gd_term_icons', '' );
}

/**
 * Gets term icon using term ID.
 *
 * If term ID not passed returns all icons.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @param int|bool $term_id The term ID.
 * @param bool $rebuild Force rebuild the icons when set to true.
 * @return mixed|string|void Term icon(s).
 */
function geodir_get_term_icon( $term_id = false, $rebuild = false ) {
    global $wpdb;
    
    if ( !$rebuild ) {
        $terms_icons = geodir_get_option( 'gd_term_icons' );
    } else {
        $terms_icons = array();
    }
    
    if ( empty( $terms_icons ) ) {
        $post_types = geodir_get_posttypes();
        $terms_icons = array();
        $tax_arr = array();
        
        foreach ( $post_types as $post_type ) {
            $tax_arr[ $post_type . 'category' ] = $post_type;
        }
        
        $terms = $wpdb->get_results( "SELECT term_id, taxonomy FROM $wpdb->term_taxonomy WHERE taxonomy IN ('" . implode( "','", array_keys( $tax_arr ) ) . "')" );
        if ( !empty( $terms ) ) {
            $a_terms = array();
            foreach ( $terms as $term ) {
                $a_terms[ $tax_arr[ $term->taxonomy ] ][] = $term;
            }
            
            foreach ( $a_terms as $pt => $t2 ) {
                foreach ( $t2 as $term ) {
                    $terms_icons[ $term->term_id ] = geodir_get_cat_icon( $term->term_id, true, true );
                }
            }
        }
        
        geodir_update_option( 'gd_term_icons', $terms_icons );
    }
    
    if ( !empty( $term_id ) ) {
        if ( isset( $terms_icons[ $term_id ] ) ) {
            return $terms_icons[ $term_id ];
        } else {
            return geodir_default_marker_icon( true );
        }
    }
    
    if ( is_ssl() ) {
        $terms_icons = str_replace( "http:", "https:", $terms_icons );
    }
    
    return apply_filters( 'geodir_get_term_icons', $terms_icons, $term_id );
}

/**
 * Check given taxonomy belongs to GD.
 *
 * @since 2.0.0
 *
 * @param string $taxonomy The taxonomy.
 * @return bool True if given taxonomy belongs to GD., otherwise False.
 */
function geodir_is_gd_taxonomy( $taxonomy ) {
    global $gd_is_taxonomy;
    
    if ( empty( $taxonomy ) ) {
        return false;
    }
    
    if ( strpos( $taxonomy, 'gd_' ) !== 0 ) {
        return false;
    }
    
    if ( !empty( $gd_is_taxonomy ) && !empty( $gd_is_taxonomy[ $taxonomy ] ) ) {
        return true;
    }
    
    $gd_taxonomies = geodir_get_taxonomies( '', true );
    
    if ( !empty( $gd_taxonomies ) && in_array( $taxonomy, $gd_taxonomies ) ) {
        if ( !is_array( $gd_is_taxonomy ) ) {
            $gd_is_taxonomy = array();
        }
        
        $gd_is_taxonomy[ $taxonomy ] = true;
        
        return true;
    }
    
    return false;
}

/**
 * Check the type of GD taxonomy.
 * 
 * @param $taxonomy
 *
 * @return null|string
 */
function geodir_taxonomy_type( $taxonomy ) {
    global $gd_taxonomy_type;
    
    if ( empty( $taxonomy ) ) {
        return NULL;
    }
    
    if ( strpos( $taxonomy, 'gd_' ) !== 0 ) {
        return NULL;
    }
    
    if ( substr( $taxonomy , -8 ) == 'category' ) {
        return 'category';
    } else if ( substr( $taxonomy , -5 ) == '_tags' ) {
        return 'tag';
    }
    
    return NULL;
}

/**
 * Get the category icon url.
 *
 * @param $term_id
 * @param bool $full_path
 * @param bool $default
 *
 * @return mixed|void
 */
function geodir_get_cat_icon( $term_id, $full_path = false, $default = false ) {
    return GeoDir_Admin_Taxonomies::get_cat_icon($term_id,$full_path ,$default);
}

/**
 * Get the category default image.
 *
 * @param $term_id
 * @param bool $full_path
 *
 * @return mixed|void
 */
function geodir_get_cat_image( $term_id, $full_path = false ) {
    return GeoDir_Admin_Taxonomies::get_cat_image($term_id,$full_path );
}

/**
 * Get the category top description html.
 *
 * @param $term_id
 *
 * @return mixed|void
 */
function geodir_get_cat_top_description( $term_id ) {
    return GeoDir_Admin_Taxonomies::get_cat_top_description($term_id);
}

/**
 * Get the taxonomy schemas.
 *
 * @return mixed|void
 */
function geodir_get_cat_schemas() {
    return GeoDir_Admin_Taxonomies::get_schemas();
}

/**
 * Function for recounting product terms, ignoring hidden products.
 *
 * @param array $terms
 * @param string $taxonomy
 * @param bool $callback
 * @param bool $terms_are_term_taxonomy_ids
 */
function geodir_term_recount( $terms, $taxonomy, $post_type, $callback = true, $terms_are_term_taxonomy_ids = true ) {
	global $wpdb;

	// Standard callback.
	if ( $callback ) {
		_update_post_term_count( $terms, $taxonomy );
	}

	$exclude_term_ids = array();

	$query = array(
		'fields' => "SELECT COUNT( DISTINCT ID ) FROM {$wpdb->posts} p",
		'join'   => '',
		'where'  => "
			WHERE 1=1
			AND p.post_status = 'publish'
			AND p.post_type = '{$post_type}'
		",
	);

	if ( count( $exclude_term_ids ) ) {
		$query['join']  .= " LEFT JOIN ( SELECT object_id FROM {$wpdb->term_relationships} WHERE term_taxonomy_id IN ( " . implode( ',', array_map( 'absint', $exclude_term_ids ) ) . " ) ) AS exclude_join ON exclude_join.object_id = p.ID";
		$query['where'] .= " AND exclude_join.object_id IS NULL";
	}

	// Pre-process term taxonomy ids.
	if ( ! $terms_are_term_taxonomy_ids ) {
		// We passed in an array of TERMS in format id=>parent.
		$terms = array_filter( (array) array_keys( $terms ) );
	} else {
		// If we have term taxonomy IDs we need to get the term ID.
		$term_taxonomy_ids = $terms;
		$terms             = array();
		foreach ( $term_taxonomy_ids as $term_taxonomy_id ) {
			$term    = get_term_by( 'term_taxonomy_id', $term_taxonomy_id, $taxonomy->name );
			$terms[] = $term->term_id;
		}
	}

	// Exit if we have no terms to count.
	if ( empty( $terms ) ) {
		return;
	}

	// Ancestors need counting.
	if ( is_taxonomy_hierarchical( $taxonomy->name ) ) {
		foreach ( $terms as $term_id ) {
			$terms = array_merge( $terms, get_ancestors( $term_id, $taxonomy->name ) );
		}
	}

	// Unique terms only.
	$terms = array_unique( $terms );

	// Count the terms.
	foreach ( $terms as $term_id ) {
		$terms_to_count = array( absint( $term_id ) );

		if ( is_taxonomy_hierarchical( $taxonomy->name ) ) {
			// We need to get the $term's hierarchy so we can count its children too
			if ( ( $children = get_term_children( $term_id, $taxonomy->name ) ) && ! is_wp_error( $children ) ) {
				$terms_to_count = array_unique( array_map( 'absint', array_merge( $terms_to_count, $children ) ) );
			}
		}

		// Generate term query
		$term_query          = $query;
		$term_query['join'] .= " INNER JOIN ( SELECT object_id FROM {$wpdb->term_relationships} INNER JOIN {$wpdb->term_taxonomy} using( term_taxonomy_id ) WHERE term_id IN ( " . implode( ',', array_map( 'absint', $terms_to_count ) ) . " ) ) AS include_join ON include_join.object_id = p.ID";

		// Get the count
		$count = $wpdb->get_var( implode( ' ', $term_query ) );

		// Update the count
		update_term_meta( $term_id, '_gd_post_count_' . $taxonomy->name, absint( $count ) );
	}

	delete_transient( 'geodir_term_counts' );
}