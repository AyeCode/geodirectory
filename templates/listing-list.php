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
 * @global object $gd_session GeoDirectory Session object.
 */

global $post;
?>
                        <div class="geodir-post-img <?php echo apply_filters('geodir_listing_listview_thumb_extra_class', '', 'listing'); ?>">
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
                                if (isset($post->is_featured) && $post->is_featured) {
                                    echo geodir_show_badges_on_image('featured', $post, get_permalink());
                                }

                                $geodir_days_new = (int)geodir_get_option('geodir_listing_new_days');

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

                        <div class="geodir-content <?php echo apply_filters('geodir_listing_listview_content_extra_class', '', 'listing'); ?>">
                            

                            <?php /// Print Distance
                            if ((isset($_REQUEST['sgeo_lat']) && $_REQUEST['sgeo_lat'] != '')) {

                                if ($related_nearest) {
                                    $startPoint = array('latitude' => $related_parent_lat, 'longitude' => $related_parent_lon);
                                } else {
                                    $startPoint = array('latitude' => $_REQUEST['sgeo_lat'], 'longitude' => $_REQUEST['sgeo_lon']);
                                }

                                $endLat = $post->post_latitude;
                                $endLon = $post->post_longitude;
                                $endPoint = array('latitude' => $endLat, 'longitude' => $endLon);
                                $uom = geodir_get_option('search_distance_long');
                                $distance = geodir_calculateDistanceFromLatLong($startPoint, $endPoint, $uom); ?>
                                <h3>
                                    <?php

                                    if (round($distance, 2) == 0) {
                                        $uom = geodir_get_option('geodir_search_dist_2');

                                        $distance = geodir_calculateDistanceFromLatLong($startPoint, $endPoint, $uom);
                                        if ($uom == 'feet') {
                                            $uom = __('feet', 'geodirectory');
                                        } else {
                                            $uom = __('meters', 'geodirectory');
                                        }
                                        echo round($distance) . ' ' . $uom . '<br />';
                                    } else {
                                        if ($uom == 'miles') {
                                            $uom = __('miles', 'geodirectory');
                                        } else {
                                            $uom = __('km', 'geodirectory');
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
                                /**
                                 * Filter to hide the listing excerpt
                                 *
                                 * @since 1.5.3
                                 * @param bool $display Display the excerpt or not. Default true.
                                 * @param string $view The view type, default 'listview'.
                                 * @param object $post The post object.
                                 */
                                $show_listing_excerpt = apply_filters('geodir_show_listing_post_excerpt', true, 'listview', $post);
                                if ($show_listing_excerpt) {
                                    if ( isset( $character_count ) && ( $character_count || $character_count == '0' ) ) {
                                        $content_out = geodir_max_excerpt( $character_count );
                                    } else {
                                        $content_out = get_the_excerpt();
                                    }
                                    if ( ! empty( $content_out ) ) {
                                        echo "<p>" . $content_out . "</p>";
                                    }
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
                        <?php
                        /**
                         * Called after printing listing content.
                         *
                         * @since 1.5.3
                         * @param object $post The post object.
                         * @param string $view The view type, default 'listing'.
                         */
                        do_action( 'geodir_after_listing_content', $post, 'listing' ); ?>
                        <footer class="geodir-entry-meta <?php echo apply_filters('geodir_listing_listview_meta_extra_class', '', 'listing'); ?>">
                            <div class="geodir-addinfo clearfix <?php echo apply_filters('geodir_listing_listview_addinfo_extra_class', '', 'listing'); ?>">

                                <?php
                                /**
                                 * Called before printing review stars html.
                                 *
                                 * @since 1.5.3
                                 * @param object $post The post object.
                                 * @param string $view The view type, default 'listing'.
                                 */
                                do_action( 'geodir_before_review_html', $post, 'listing' );

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
                                        <?php geodir_comments_number($post); ?></a>
                                <?php
                                }


                                /**
                                 * Called after printing favorite html.
                                 *
                                 * @since 1.0.0
                                 */
                                do_action( 'geodir_after_favorite_html', $post->ID, 'listing' );


                                /**
                                 * Called after printing map pin point.
                                 *
                                 * @since 1.0.0
                                 * @since 1.5.9 Added $post as second param.
                                 * @param int $post->ID The post id.
                                 * @param object $post The post object.
                                 */
                                do_action( 'geodir_listing_after_pinpoint', $post->ID ,$post);

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

                                                <a href="<?php echo esc_url($editlink); ?>" class="geodir-edit"
                                                   title="<?php _e('Edit Listing', 'geodirectory'); ?>">
                                                    <?php
                                                    $geodir_listing_edit_icon = apply_filters('geodir_listing_edit_icon', 'fa fa-edit');
                                                    echo '<i class="'. $geodir_listing_edit_icon .'"></i>';
                                                    ?>
                                                    <?php _e('Edit', 'geodirectory'); ?>
                                                </a>
                                                <a href="<?php echo esc_url($deletelink); ?>" class="geodir-delete"
                                                   title="<?php _e('Delete Listing', 'geodirectory'); ?>">
                                                    <?php
                                                    $geodir_listing_delete_icon = apply_filters('geodir_listing_delete_icon', 'fa fa-close');
                                                    echo '<i class="'. $geodir_listing_delete_icon .'"></i>';
                                                    ?>
                                                    <?php _e('Delete', 'geodirectory'); ?>
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


