<?php
/**
 * Template tag functions.
 *
 * @since 1.0.0
 * @package GeoDirectory
 */




/**
 * Adds async tag to javascript for faster page loading.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @param string $url The javascript file url.
 * @return string The modified javascript string.
 */
function geodir_add_async_forscript($url)
{
    if (strpos($url, '#asyncload')===false)
        return $url;
    else if (is_admin())
        return str_replace('#asyncload', '', $url);
    else
        return str_replace('#asyncload', '', $url)."' async='async";
}
add_filter('clean_url', 'geodir_add_async_forscript', 11, 1);



/**
 * Returns paginated HTML string based on the given parameters.
 *
 * @since 1.0.0
 * @since 1.5.5 Fixed pagination links when location selected.
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @global object $wp_query WordPress Query object.
 * @param string $before The HTML to prepend.
 * @param string $after The HTML to append.
 * @param string $prelabel The previous link label.
 * @param string $nxtlabel The next link label.
 * @param int $pages_to_show Number of pages to display on the pagination. Default: 5.
 * @param bool $always_show Do you want to show the pagination always? Default: false.
 */
function geodir_pagination($before = '', $after = '', $prelabel = '', $nxtlabel = '', $pages_to_show = 5, $always_show = false) {
    global $wp_query, $posts_per_page, $wpdb, $paged, $blog_id;

    if (empty($prelabel)) {
        $prelabel = '<strong>&lt;</strong>';
    }

    if (empty($nxtlabel)) {
        $nxtlabel = '<strong>&gt;</strong>';
    }

    $half_pages_to_show = round($pages_to_show / 2);

    if (geodir_is_page('home')) // dont apply default  pagination for geodirectory home page.
        return;

    if (!is_single()) {
        if (function_exists('geodir_location_geo_home_link')) {
            remove_filter('home_url', 'geodir_location_geo_home_link', 100000);
        }
        $numposts = $wp_query->found_posts;

        $max_page = ceil($numposts / $posts_per_page);

        if (empty($paged)) {
            $paged = 1;
        }
        
        $post_type = geodir_get_current_posttype();
        $listing_type_name = get_post_type_plural_label($post_type);
        if (geodir_is_page('listing') || geodir_is_page('search')) {            
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

        if ($max_page > 1 || $always_show) {            
            // Extra pagination info
            $geodir_pagination_more_info = geodir_get_option('search_advanced_pagination');
            $start_no = ( $paged - 1 ) * $posts_per_page + 1;
            $end_no = min($paged * $posts_per_page, $numposts);

            if ($geodir_pagination_more_info != '') {
                if ($listing_type_name) {
                    $listing_type_name = __($listing_type_name, 'geodirectory');
                    $pegination_desc = wp_sprintf(__('Showing %s %d-%d of %d', 'geodirectory'), $listing_type_name, $start_no, $end_no, $numposts);
                } else {
                    $pegination_desc = wp_sprintf(__('Showing listings %d-%d of %d', 'geodirectory'), $start_no, $end_no, $numposts);
                }
                $pagination_info = '<div class="gd-pagination-details">' . $pegination_desc . '</div>';
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
                
                if ($geodir_pagination_more_info == 'before') {
                    $before = $before . $pagination_info;
                } else if ($geodir_pagination_more_info == 'after') {
                    $after = $pagination_info . $after;
                }
            }
            
            echo "$before <div class='Navi gd-navi'>";
            if ($paged >= ($pages_to_show - 1)) {
                echo '<a href="' . str_replace('&paged', '&amp;paged', get_pagenum_link()) . '">&laquo;</a>';
            }
            previous_posts_link($prelabel);
            for ($i = $paged - $half_pages_to_show; $i <= $paged + $half_pages_to_show; $i++) {
                if ($i >= 1 && $i <= $max_page) {
                    if ($i == $paged) {
                        echo "<strong class='on'>$i</strong>";
                    } else {
                        echo ' <a href="' . str_replace('&paged', '&amp;paged', get_pagenum_link($i)) . '">' . $i . '</a> ';
                    }
                }
            }
            next_posts_link($nxtlabel, $max_page);
            if (($paged + $half_pages_to_show) < ($max_page)) {
                echo '<a href="' . str_replace('&paged', '&amp;paged', get_pagenum_link($max_page)) . '">&raquo;</a>';
            }
            echo "</div> $after";
        }
        
        if (function_exists('geodir_location_geo_home_link')) {
            add_filter('home_url', 'geodir_location_geo_home_link', 100000, 2);
        }
    }
}

/**
 * Prints listing search related javascript.
 *
 * @since 1.0.0
 * @package GeoDirectory
 */
function geodir_listingsearch_scripts()
{
    if (geodir_get_option('gd_search_dist') != '') {
        $dist = geodir_get_option('gd_search_dist');
    } else {
        $dist = 500;
    }
    $dist_dif = 1000;

    if ($dist <= 5000) $dist_dif = 500;
    if ($dist <= 1000) $dist_dif = 100;
    if ($dist <= 500) $dist_dif = 50;
    if ($dist <= 100) $dist_dif = 10;
    if ($dist <= 50) $dist_dif = 5;

    ?>
    <script type="text/javascript">

        jQuery(function ($) {
            $("#distance_slider").slider({
                range: true,
                values: [0, <?php echo ($_REQUEST['sdist']!='') ? sanitize_text_field($_REQUEST['sdist']) : "0"; ?>],
                min: 0,
                max: <?php echo $dist; ?>,
                step: <?php echo $dist_dif; ?>,
                slide: function (event, ui) {
                    $("#sdist").val(ui.values[1]);
                    $("#sdist_span").html(ui.values[1]);
                }
            });

            $("#sdist").val($("#distance_slider").slider("values", 1));
            $("#sdist_span").html($("#distance_slider").slider("values", 1));

        });

        /*jQuery(".showFilters").click(function () {
         jQuery(".gdFilterOptions").slideToggle("slow");
         });*/

        jQuery("#cat_all").click(function () {
            jQuery('.cat_check').attr('checked', this.checked);
        });

        jQuery(".cat_check").click(function () {
            if (jQuery(".cat_check").length == jQuery(".cat_check:checked").length) {
                jQuery("#cat_all").attr("checked", "checked");
            } else {
                jQuery("#cat_all").removeAttr("checked");
            }
        });


        jQuery(window).load(function () {
            if (jQuery(".cat_check").length == jQuery(".cat_check:checked").length) {
                jQuery("#cat_all").attr("checked", "checked");
            } else {
                jQuery("#cat_all").removeAttr("checked");
            }
        });

    </script>
<?php
}



/**
 * Dequeue scripts to fix JS conflicts.
 *
 * @since 1.6.22
 */
function geodir_fix_script_conflict() {
    if ( wp_script_is( 'flexslider', 'enqueued' ) && wp_script_is( 'geodirectory-jquery-flexslider-js', 'enqueued' ) ) {
        wp_dequeue_script( 'flexslider' );
    }
}
add_action( 'wp_enqueue_scripts', 'geodir_fix_script_conflict', 100 );