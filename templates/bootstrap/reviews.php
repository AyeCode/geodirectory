<?php
/**
 * The template for displaying Comments.
 *
 * The area of the page that contains both current comments
 * and the comment form.
 *
 * @since 1.0.0
 * @since 1.5.4 Modified to fix review sorting.
 *
 * @package GeoDirectory
 */

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
global $preview;
if (post_password_required() || $preview)
    return;
?>

<div id="comments" class="comments-area geodir-comments-area bsui" style="padding:0;">
    <div class="commentlist-wrap">

    <?php // You can start editing here -- including this comment! ?>

    <?php
    /**
     * Called before displaying reviews.
     *
     * If you would like to wrap reviews inside a div this is the place to print your open div. @see geodir_before_review_form to print your closing div.
     *
     * @since 1.5.7
     */
    do_action('geodir_before_review_list'); ?>

    <?php if (have_comments()) :

        ?>
        <h3 class="comments-title h3">
            <?php
            printf(_n('1 Review <span class="r-title-on">on</span> <span class="r-title">&ldquo;%2$s&rdquo;</span>', '%1$s Reviews <span>on</span> <span class="r-title"> &ldquo;%2$s&rdquo;</span>', get_comments_number(), 'geodirectory'),
                number_format_i18n(get_comments_number()), get_the_title());
            ?>
        </h3>

        <?php

        /**
         * Filter the label for main rating.
         *
         * This is not shown everywhere but is used by reviews manager.
         */
        $overall_label = apply_filters('geodir_overall_rating_label_main','');
        $post_rating = geodir_get_post_rating( $post->ID );
        echo "<div class='gd-main-overall-rating d-flex align-items-center h4'>" . geodir_get_rating_stars( $post_rating, $post->ID, $overall_label ) . "<span class='ml-2 gd-overall-rating-text badge badge-secondary' >".__("Overall rating","geodirectory")."</span></div>";
        /**
         * Called after displaying review listing title.
         *
         * @since 1.5.7
         */
        do_action('geodir_after_review_list_title'); ?>

        <ul class="commentlist list-unstyled">
            <?php $reverse_top_level = null;// @todo we need to do this more efficient than "is_plugin_active" // is_plugin_active('geodir_review_rating_manager/geodir_review_rating_manager.php') ? false : null; ?>
			<?php wp_list_comments(array('callback' => array('GeoDir_Comments','list_comments_callback'), 'reverse_top_level' => $reverse_top_level, 'style' => 'ol'));
            ?>
        </ul><!-- .commentlist -->

        <?php if (get_comment_pages_count() > 1 && get_option('page_comments')) : // are there comments to navigate through ?>
            <nav id="comment-nav-below" class="navigation" role="navigation">
                <h1 class="assistive-text section-heading"><?php _e('Review navigation', 'geodirectory'); ?></h1>

                <div
                    class="nav-previous"><?php previous_comments_link(__('&larr; Older Reviews', 'geodirectory')); ?></div>
                <div
                    class="nav-next"><?php next_comments_link(__('Newer Reviews &rarr;', 'geodirectory')); ?></div>
            </nav>
        <?php endif; // check for comment navigation ?>

        <?php
        /* If there are no comments and comments are closed, let's leave a note.
         * But we only want the note on posts and pages that had comments in the first place.
         */
        if (!comments_open() && get_comments_number()) : ?>
            <p class="nocomments"><?php _e('Reviews are closed.', 'geodirectory'); ?></p>
        <?php endif; ?>

    <?php endif; // have_comments() ?>

    <?php
    /**
     * Called before displaying "Leave a review form".
     *
     * If you would like to wrap "review form" inside a div this is the best place to hook your open div. @see geodir_after_review_form to print your closing div.
     * Also If you would like to wrap "reviews" inside a div this is the best place to print your closing div. @see geodir_before_review_list to print your open div.
     *
     * @since 1.5.7
     */
    do_action('geodir_before_review_form'); ?>
    </div>
    <?php
    /**
     * Filters comment form args
     *
     * If you would like to modify your comment form args, use this filter. @see https://codex.wordpress.org/Function_Reference/comment_form for accepted args.
     *
     * @since 1.0.0
     */
    // remove args and do it by filter
    $args = apply_filters('geodir_review_form_args', array());
    comment_form($args);
    ?>

    <?php
    /**
     * Called after displaying "Leave a review form".
     *
     * If you would like to wrap "review form" inside a div this is the best place to print your closing div. @see geodir_before_review_form to print your open div.
     *
     * @since 1.5.7
     */
    do_action('geodir_after_review_form'); ?>

</div><!-- #comments .comments-area -->