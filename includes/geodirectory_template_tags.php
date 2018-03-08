<?php
/**
 * Template tag functions.
 *
 * @since 1.0.0
 * @package GeoDirectory
 */

/**
 * Loads custom JS in footer.
 *
 * WP Admin -> Geodirectory -> Design -> Scripts -> Footer script code.
 *
 * @since 1.0.0
 * @package GeoDirectory
 */
function geodir_footer_scripts()
{

    echo stripslashes(geodir_get_option('geodir_footer_scripts'));

    /*
     * Apple suck and can't/won't fix bugs: https://bugs.webkit.org/show_bug.cgi?id=136041
     *
     * Flexbox wont wrap on ios for search form items
     */
    if ( !empty( $_SERVER['HTTP_USER_AGENT'] ) && preg_match( '/iPad|iPod|iPhone|Safari/', $_SERVER['HTTP_USER_AGENT'] ) ) {
        echo "<style>body .geodir-listing-search.gd-search-bar-style .geodir-loc-bar .clearfix.geodir-loc-bar-in .geodir-search .gd-search-input-wrapper{flex:50 1 auto !important;min-width: initial !important;width:auto !important;}.geodir-filter-container .geodir-filter-cat{width:auto !important;}</style>";
    }
}


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
 * Prints location related javascript.
 *
 * @since 1.0.0
 * @since 1.6.16 Fix: Single quote in default city name causes problem in add new city.
 * @package GeoDirectory
 */
function geodir_add_sharelocation_scripts() {
    $default_search_for_text = SEARCH_FOR_TEXT;
    if (geodir_get_option('geodir_search_field_default_text'))
        $default_search_for_text = __(geodir_get_option('geodir_search_field_default_text'), 'geodirectory');

    $default_near_text = NEAR_TEXT;
    if (geodir_get_option('geodir_near_field_default_text'))
        $default_near_text = __(geodir_get_option('geodir_near_field_default_text'), 'geodirectory');
    
    $search_location = geodir_get_default_location();
    
    $default_search_for_text = addslashes(stripslashes($default_search_for_text));
    $default_near_text = addslashes(stripslashes($default_near_text));
    $city = !empty($search_location) ? addslashes(stripslashes($search_location->city)) : '';
    ?>
    <script type="text/javascript">
        var default_location = '<?php echo $city ;?>';
        var latlng;
        var address;
        var dist = 0;
        var Sgeocoder = (typeof google!=='undefined' && typeof google.maps!=='undefined') ? new google.maps.Geocoder() : {};

		function geodir_setup_submit_search() {
            jQuery('.geodir_submit_search').unbind('click');// unbind any other click events
			jQuery('.geodir_submit_search').click(function(e) {

                e.preventDefault();

				var s = ' ';
				var $form = jQuery(this).closest('form');

				if (jQuery("#sdist input[type='radio']:checked").length != 0) dist = jQuery("#sdist input[type='radio']:checked").val();
				if (jQuery('.search_text', $form).val() == '' || jQuery('.search_text', $form).val() == '<?php echo $default_search_for_text;?>') jQuery('.search_text', $form).val(s);
				
				// Disable location based search for disabled location post type.
				if (jQuery('.search_by_post', $form).val() != '' && typeof gd_cpt_no_location == 'function') {
					if (gd_cpt_no_location(jQuery('.search_by_post', $form).val())) {
						jQuery('.snear', $form).remove();
						jQuery('.sgeo_lat', $form).remove();
						jQuery('.sgeo_lon', $form).remove();
						jQuery('select[name="sort_by"]', $form).remove();
						jQuery($form).submit();
						return;
					}
				}
				
				if (dist > 0 || (jQuery('select[name="sort_by"]').val() == 'nearest' || jQuery('select[name="sort_by"]', $form).val() == 'farthest') || (jQuery(".snear", $form).val() != '' && jQuery(".snear", $form).val() != '<?php echo $default_near_text;?>')) {
					geodir_setsearch($form);
				} else {
					jQuery(".snear", $form).val('');
					jQuery($form).submit();
				}
			});
		}

        jQuery(document).ready(function() {
            geodir_setup_submit_search();
            //setup advanced search form on form ajax load
            jQuery("body").on("geodir_setup_search_form", function(){
                geodir_setup_submit_search();
            });
        });
        
		function geodir_setsearch($form) {
			if ((dist > 0 || (jQuery('select[name="sort_by"]', $form).val() == 'nearest' || jQuery('select[name="sort_by"]', $form).val() == 'farthest')) && (jQuery(".snear", $form).val() == '' || jQuery(".snear", $form).val() == '<?php echo $default_near_text;?>')) jQuery(".snear", $form).val(default_location);
			geocodeAddress($form);
		}

        function updateSearchPosition(latLng, $form) {
            if (window.gdMaps === 'google') {
                jQuery('.sgeo_lat').val(latLng.lat());
                jQuery('.sgeo_lon').val(latLng.lng());
            } else if (window.gdMaps === 'osm') {
                jQuery('.sgeo_lat').val(latLng.lat);
                jQuery('.sgeo_lon').val(latLng.lon);
            }
            jQuery($form).submit(); // submit form after insering the lat long positions
        }

        function geocodeAddress($form) {
            // Call the geocode function
            Sgeocoder = window.gdMaps == 'google' ? new google.maps.Geocoder() : null;

            if (jQuery('.snear', $form).val() == '' || ( jQuery('.sgeo_lat').val() != '' && jQuery('.sgeo_lon').val() != ''  ) || jQuery('.snear', $form).val().match("^<?php _e('In:','geodirectory');?>")) {
                if (jQuery('.snear', $form).val().match("^<?php _e('In:','geodirectory');?>")) {
                    jQuery(".snear", $form).val('');
                }
                jQuery($form).submit();
            } else {
                var address = jQuery(".snear", $form).val();

                if (jQuery('.snear', $form).val() == '<?php echo $default_near_text;?>') {
                    initialise2();
                } else {
                    <?php
                    $near_add = geodir_get_option('geodir_search_near_addition');
                    /**
                     * Adds any extra info to the near search box query when trying to geolocate it via google api.
                     *
                     * @since 1.0.0
                     */
                    $near_add2 = apply_filters('geodir_search_near_addition', '');
                    ?>
                    if (window.gdMaps === 'google') {
                        Sgeocoder.geocode({'address': address<?php echo ($near_add ? '+", ' . $near_add . '"' : '') . $near_add2;?>},
                            function (results, status) {
                                if (status == google.maps.GeocoderStatus.OK) {
                                    updateSearchPosition(results[0].geometry.location, $form);
                                } else {
                                    alert("<?php esc_attr_e('Search was not successful for the following reason :', 'geodirectory');?>" + status);
                                }
                            });
                    } else if (window.gdMaps === 'osm') {
                        geocodePositionOSM(false, address, false, false, 
                            function(geo) {
                                if (typeof geo !== 'undefined' && geo.lat && geo.lon) {
                                    updateSearchPosition(geo, $form);
                                } else {
                                    alert("<?php esc_attr_e('Search was not successful for the requested address.', 'geodirectory');?>");
                                }
                            });
                    } else {
                        jQuery($form).submit();
                    }
                }
            }
        }

        function initialise2() {
            if (!window.gdMaps) {
                return;
            }
            
            if (window.gdMaps === 'google') {
                var latlng = new google.maps.LatLng(56.494343, -4.205446);
                var myOptions = {
                    zoom: 4,
                    mapTypeId: google.maps.MapTypeId.TERRAIN,
                    disableDefaultUI: true
                }
            } else if (window.gdMaps === 'osm') {
                var latlng = new L.LatLng(56.494343, -4.205446);
                var myOptions = {
                    zoom: 4,
                    mapTypeId: 'TERRAIN',
                    disableDefaultUI: true
                }
            }
            try { prepareGeolocation(); } catch (e) {}
            doGeolocation();
        }

        function doGeolocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(positionSuccess, positionError);
            } else {
                positionError(-1);
            }
        }

        function positionError(err) {
            var msg;
            switch (err.code) {
                case err.UNKNOWN_ERROR:
                    msg = "<?php _e('Unable to find your location','geodirectory');?>";
                    break;
                case err.PERMISSION_DENINED:
                    msg = "<?php _e('Permission denied in finding your location','geodirectory');?>";
                    break;
                case err.POSITION_UNAVAILABLE:
                    msg = "<?php _e('Your location is currently unknown','geodirectory');?>";
                    break;
                case err.BREAK:
                    msg = "<?php _e('Attempt to find location took too long','geodirectory');?>";
                    break;
                default:
                    msg = "<?php _e('Location detection not supported in browser','geodirectory');?>";
            }
            jQuery('#info').html(msg);
        }

        function positionSuccess(position) {
            var coords = position.coords || position.coordinate || position;
            jQuery('.sgeo_lat').val(coords.latitude);
            jQuery('.sgeo_lon').val(coords.longitude);

            jQuery('.geodir-listing-search').submit();
        }

        /**
         * On unload page do some cleaning so back button cache does not store these values.
         */
        window.onunload = function(){
            if(jQuery('.sgeo_lat').length ){
                jQuery('.sgeo_lat').val('');
                jQuery('.sgeo_lon').val('');
            }
        };

    </script>
<?php
}


/**
 * Displays badges on the listings pages over the thumbnail.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @param string $which The badge type.
 * @param object $post The post object.
 * @param string $link The link to the post.
 * @return string|void The link HTML.
 */
function geodir_show_badges_on_image($which, $post, $link)
{
    $return = '';
    switch ($which) {
        case 'featured':
            /**
             * Filter the featured image badge html that appears in the listings pages over the thumbnail.
             *
             * @since 1.0.0
             * @param object $post The post object.
             * @param string $link The link to the post.
             */
            $return = apply_filters('geodir_featured_badge_on_image', '<a href="' . $link . '"><span class="geodir_featured_img">&nbsp;</span></a>',$post,$link);
            break;
        case 'new' :
            /**
             * Filter the new image badge html that appears in the listings pages over the thumbnail.
             *
             * @since 1.0.0
             * @param object $post The post object.
             * @param string $link The link to the post.
             */
            $return = apply_filters('geodir_new_badge_on_image', '<a href="' . $link . '"><span class="geodir_new_listing">&nbsp;</span></a>',$post,$link);
            break;

    }
    
    return $return;
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