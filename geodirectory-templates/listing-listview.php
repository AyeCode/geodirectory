<?php
/**
 * Template for the list of places
 *
 * This is used mostly on the listing (category) pages and outputs the actual grid or list of listings.
 * See the link below for info on how to replace the template in your theme.
 *
 * @link http://docs.wpgeodirectory.com/customizing-geodirectory-templates/
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @global object $post The current post object.
 * @global object $wp_query WordPress Query object.
 * @global string $gridview_columns The girdview style of the listings.
 */

/**
 * Called before the listing template used to list listing of places.
 *
 * This is used anywhere you see a list of listings.
 *
 * @since 1.0.0
 */
do_action('geodir_before_listing_listview');

global $gridview_columns;

/**
 * Filter the default grid view class.
 *
 * This can be used to filter the default grid view class but can be overridden by a user $_SESSION.
 *
 * @since 1.0.0
 * @param string $gridview_columns The grid view class, can be '', 'gridview_onehalf', 'gridview_onethird', 'gridview_onefourth' or 'gridview_onefifth'.
 */
$grid_view_class = apply_filters('geodir_grid_view_widget_columns', $gridview_columns);
if (isset($_SESSION['gd_listing_view']) && $_SESSION['gd_listing_view'] != '' && !isset($before_widget) && !isset($related_posts)) {
    if ($_SESSION['gd_listing_view'] == '1') {
        $grid_view_class = '';
    }
    if ($_SESSION['gd_listing_view'] == '2') {
        $grid_view_class = 'gridview_onehalf';
    }
    if ($_SESSION['gd_listing_view'] == '3') {
        $grid_view_class = 'gridview_onethird';
    }
    if ($_SESSION['gd_listing_view'] == '4') {
        $grid_view_class = 'gridview_onefourth';
    }
    if ($_SESSION['gd_listing_view'] == '5') {
        $grid_view_class = 'gridview_onefifth';
    }
}
?>

    <ul class="geodir_category_list_view clearfix">

        <?php if (have_posts()) :

            /**
             * Called inside the `ul` of the listings template, but before any `li` elements.
             *
             * When used by the widget view template then it will only show if there are listings to be shown.
             *
             * @since 1.0.0
             * @see 'geodir_after_listing_post_listview'
             */
            do_action('geodir_before_listing_post_listview');

            while (have_posts()) : the_post();
                global $post, $wpdb, $listing_width, $preview;

                /**
                 * Add a class to the `li` element of the listings list template.
                 *
                 * @since 1.0.0
                 * @param string $class The extra class for the `li` element, default empty.
                 */
                $post_view_class = apply_filters('geodir_post_view_extra_class', '');

                /**
                 * Add a class to the `article` tag inside the `li` element on the listings list template.
                 *
                 * @since 1.0.0
                 * @param string $class The extra class for the `article` element, default empty.
                 */
                $post_view_article_class = apply_filters('geodir_post_view_article_extra_class', '');
                ?>

                <li class="clearfix <?php if ($grid_view_class) {
                    echo 'geodir-gridview ' . $grid_view_class;
                } else {
                    echo ' geodir-listview ';
                } ?> <?php if ($post_view_class) {
                    echo $post_view_class;
                } ?>" <?php if ($listing_width) echo "style='width:{$listing_width}%;'"; // Width for widget listing ?> >
                    <article class="geodir-category-listing <?php if ($post_view_article_class) {
                        echo $post_view_article_class;
                    } ?>">
                        <div class="geodir-post-img">
                            <?php if ($fimage = geodir_show_featured_image($post->ID, 'list-thumb', true, false, $post->featured_image)) { ?>

                                <a href="<?php the_permalink(); ?>">
                                    <?php echo $fimage; ?>
                                </a>
                                <?php
                                /**
                                 * Called before badges are output.
                                 *
                                 * Called on the listings template after the image has been output and before the badges like `new` or `featured` are output.
                                 *
                                 * @since 1.0.0
                                 * @param object $post The post object.
                                 * @see 'geodir_after_badge_on_image'
                                 */
                                do_action('geodir_before_badge_on_image', $post);
                                if ($post->is_featured) {
                                    echo geodir_show_badges_on_image('featured', $post, get_permalink());
                                }

                                $geodir_days_new = (int)get_option('geodir_listing_new_days');

                                if (round(abs(strtotime($post->post_date) - strtotime(date('Y-m-d'))) / 86400) < $geodir_days_new) {
                                    echo geodir_show_badges_on_image('new', $post, get_permalink());
                                }

                                /**
                                 * Called after badges are output.
                                 *
                                 * Called on the listings template after the image and badges like `new` or `featured` have been output.
                                 *
                                 * @since 1.0.0
                                 * @param object $post The post object.
                                 * @see 'geodir_before_badge_on_image'
                                 */
                                do_action('geodir_after_badge_on_image', $post);
                                ?>


                            <?php } ?>

                        </div>

                        <div class="geodir-content">

                            <?php
                            /**
                             * Called before the post title on the listings view template.
                             *
                             * @since 1.0.0
                             * @param string $type The template type, default 'listview'.
                             * @param object $post The post object.
                             * @see 'geodir_after_listing_post_title'
                             */
                            do_action('geodir_before_listing_post_title', 'listview', $post); ?>

                            <header class="geodir-entry-header"><h3 class="geodir-entry-title">
                                    <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">

                                        <?php the_title(); ?>

                                    </a>
                                </h3></header>
                            <!-- .entry-header -->

                            <?php
                            /**
                             * Called after the post title on the listings view template.
                             *
                             * @since 1.0.0
                             * @param string $type The template type, default 'listview'.
                             * @param object $post The post object.
                             * @see 'geodir_before_listing_post_title'
                             */
                            do_action('geodir_after_listing_post_title', 'listview', $post); ?>

                            <?php /// Print Distance
                            if (isset($_REQUEST['sgeo_lat']) && $_REQUEST['sgeo_lat'] != '') {

                                $startPoint = array('latitude' => $_REQUEST['sgeo_lat'], 'longitude' => $_REQUEST['sgeo_lon']);

                                $endLat = $post->post_latitude;
                                $endLon = $post->post_longitude;
                                $endPoint = array('latitude' => $endLat, 'longitude' => $endLon);
                                $uom = get_option('geodir_search_dist_1');
                                $distance = geodir_calculateDistanceFromLatLong($startPoint, $endPoint, $uom); ?>
                                <h3>
                                    <?php

                                    if (round($distance, 2) == 0) {
                                        $uom = get_option('geodir_search_dist_2');

                                        $distance = geodir_calculateDistanceFromLatLong($startPoint, $endPoint, $uom);
                                        if ($uom == 'feet') {
                                            $uom = __('feet', GEODIRECTORY_TEXTDOMAIN);
                                        } else {
                                            $uom = __('meters', GEODIRECTORY_TEXTDOMAIN);
                                        }
                                        echo round($distance) . ' ' . $uom . '<br />';
                                    } else {
                                        if ($uom == 'miles') {
                                            $uom = __('miles', GEODIRECTORY_TEXTDOMAIN);
                                        } else {
                                            $uom = __('km', GEODIRECTORY_TEXTDOMAIN);
                                        }
                                        echo round($distance, 2) . ' ' . $uom . '<br />';
                                    }
                                    ?>
                                </h3>
                            <?php } ?>


                            <?php
                            /**
                             * Called before the post excerpt on the listings view template.
                             *
                             * @since 1.0.0
                             * @param object $post The post object.
                             * @see 'geodir_after_listing_post_excerpt'
                             */
                            do_action('geodir_before_listing_post_excerpt', $post); ?>
                            <?php echo geodir_show_listing_info('listing'); ?>
                            <div class="geodir-entry-content">

                                <?php
                                if (isset($character_count) && ($character_count || $character_count == '0')) {
                                    $content_out = geodir_max_excerpt($character_count);
                                } else {
                                    $content_out = get_the_excerpt();
                                }
                                if (!empty($content_out)) {
                                    echo "<p>" . $content_out . "</p>";
                                }
                                ?></div>

                            <?php
                            /**
                             * Called after the post excerpt on the listings view template.
                             *
                             * @since 1.0.0
                             * @param object $post The post object.
                             * @see 'geodir_before_listing_post_excerpt'
                             */
                            do_action('geodir_after_listing_post_excerpt', $post); ?>
                        </div>
                        <!-- gd-content ends here-->
                        <footer class="geodir-entry-meta">
                            <div class="geodir-addinfo clearfix">

                                <?php

                                $review_show = geodir_is_reviews_show('listview');

                                if ($review_show) {

                                    if (!$preview) {
                                        $post_avgratings = geodir_get_post_rating($post->ID);
                                        /**
                                         * Called before the rating stars are output on the listings view template.
                                         *
                                         * @since 1.0.0
                                         * @param float $post_avgratings The average rating for the post.
                                         * @param int $post->ID The post ID.
                                         * @see 'geodir_after_review_rating_stars_on_listview'
                                         */
                                        do_action('geodir_before_review_rating_stars_on_listview', $post_avgratings, $post->ID);

                                        echo geodir_get_rating_stars($post_avgratings, $post->ID);

                                        /**
                                         * Called after the rating stars are output on the listings view template.
                                         *
                                         * @since 1.0.0
                                         * @param float $post_avgratings The average rating for the post.
                                         * @param int $post->ID The post ID.
                                         * @see 'geodir_before_review_rating_stars_on_listview'
                                         */
                                        do_action('geodir_after_review_rating_stars_on_listview', $post_avgratings, $post->ID);
                                    }
                                    ?>
                                    <a href="<?php comments_link(); ?>" class="geodir-pcomments"><i
                                            class="fa fa-comments"></i>
                                        <?php geodir_comments_number($post->rating_count); ?></a>
                                <?php
                                }
                                geodir_favourite_html($post->post_author, $post->ID);

                                /**
                                 * Called after printing favorite html.
                                 *
                                 * @since 1.0.0
                                 */
                                do_action( 'geodir_after_favorite_html', $post->ID, 'listing' );

                                global $wp_query;

                                $show_pin_point = $wp_query->is_main_query();
                                if (!empty($show_pin_point) && is_active_widget(false, "", "geodir_map_v3_listing_map")) {

                                    /*if($json_info = json_decode($post->marker_json))
                                        $marker_icon = $json_info->icon;*/

                                    $term_icon_url = get_tax_meta($post->default_category, 'ct_cat_icon', false, $post->post_type);
                                    $marker_icon = isset($term_icon_url['src']) ? $term_icon_url['src'] : get_option('geodir_default_marker_icon');
                                    ?>
                                    <span class="geodir-pinpoint"
                                          style=" background:url('<?php if (isset($marker_icon)) {
                                              echo $marker_icon;
                                          } ?>') no-repeat scroll left top transparent; background-size:auto 100%; -webkit-background-size:auto 100%; -moz-background-size:auto 100%; height:9px; width:14px; "></span>
                                    <a class="geodir-pinpoint-link" href="javascript:void(0)"
                                       onclick="openMarker('listing_map_canvas' ,'<?php echo $post->ID; ?>')"
                                       onmouseover="animate_marker('listing_map_canvas' ,'<?php echo $post->ID; ?>')"
                                       onmouseout="stop_marker_animation('listing_map_canvas' ,'<?php echo $post->ID; ?>')"><?php _e('Pinpoint', GEODIRECTORY_TEXTDOMAIN); ?></a>
                                <?php }

                                /**
                                 * Called after printing map pin point.
                                 *
                                 * @since 1.0.0
                                 */
                                do_action( 'geodir_listing_after_pinpoint', $post->ID );

                                if ($post->post_author == get_current_user_id()) { ?>
                                    <?php
                                    $addplacelink = get_permalink(geodir_add_listing_page_id());
                                    $editlink = geodir_getlink($addplacelink, array('pid' => $post->ID), false);
                                    $upgradelink = geodir_getlink($editlink, array('upgrade' => '1'), false);

                                    $ajaxlink = geodir_get_ajax_url();
                                    $deletelink = geodir_getlink($ajaxlink, array('geodir_ajax' => 'add_listing', 'ajax_action' => 'delete', 'pid' => $post->ID), false);

                                    ?>

                                    <span class="geodir-authorlink clearfix">
											
											<?php if (isset($_REQUEST['geodir_dashbord']) && $_REQUEST['geodir_dashbord']) {
                                                /**
                                                 * Called before the edit post link on the listings view template used on the author page.
                                                 *
                                                 * @since 1.0.0
                                                 * @see 'geodir_after_edit_post_link_on_listing'
                                                 */
                                                do_action('geodir_before_edit_post_link_on_listing');
                                                ?>

                                                <a href="<?php echo $editlink; ?>" class="geodir-edit"
                                                   title="<?php _e('Edit Listing', GEODIRECTORY_TEXTDOMAIN); ?>">
                                                    <?php
                                                    $geodir_listing_edit_icon = apply_filters('geodir_listing_edit_icon', 'fa fa-edit');
                                                    echo '<i class="'. $geodir_listing_edit_icon .'"></i>';
                                                    ?>
                                                    <?php _e('Edit', GEODIRECTORY_TEXTDOMAIN); ?>
                                                </a>
                                                <a href="<?php echo $deletelink; ?>" class="geodir-delete"
                                                   title="<?php _e('Delete Listing', GEODIRECTORY_TEXTDOMAIN); ?>">
                                                    <?php
                                                    $geodir_listing_delete_icon = apply_filters('geodir_listing_delete_icon', 'fa fa-close');
                                                    echo '<i class="'. $geodir_listing_delete_icon .'"></i>';
                                                    ?>
                                                    <?php _e('Delete', GEODIRECTORY_TEXTDOMAIN); ?>
                                                </a>
                                                <?php

                                                /**
                                                 * Called after the edit post link on the listings view template used on the author page.
                                                 *
                                                 * @since 1.0.0
                                                 * @see 'geodir_before_edit_post_link_on_listing'
                                                 */
                                                do_action('geodir_after_edit_post_link_on_listing');
                                            } ?>
											</span>

                                <?php } ?>

                            </div>
                            <!-- geodir-addinfo ends here-->
                        </footer>
                        <!-- .entry-meta -->
                    </article>
                </li>

            <?php
            endwhile;

            /**
             * Called inside the `ul` of the listings template, but after all `li` elements.
             *
             * When used by the widget view template then it will only show if there are listings to be shown.
             *
             * @since 1.0.0
             * @see 'geodir_before_listing_post_listview'
             */
            do_action('geodir_after_listing_post_listview');

        else:

            if (isset($_REQUEST['list']) && $_REQUEST['list'] == 'favourite')
                echo '<li class="no-listing">' . __('No favorite listings found which match your selection.', GEODIRECTORY_TEXTDOMAIN) . '</li>';
            else
                echo '<li class="no-listing">' . __('No listings found which match your selection.', GEODIRECTORY_TEXTDOMAIN) . '</li>';

        endif;

        ?>
    </ul>  <!-- geodir_category_list_view ends here-->

    <div class="clear"></div>
<?php
/**
 * Called after the listings list view template, after all the wrapper at the very end.
 *
 * @since 1.0.0
 */
do_action('geodir_after_listing_listview');
