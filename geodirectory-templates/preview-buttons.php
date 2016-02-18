<?php
/**
 * Template for the buttons and messages on the preview page
 *
 * You can make most changes via hooks or see the link below for info on how to replace the template in your theme.
 *
 * @link http://docs.wpgeodirectory.com/customizing-geodirectory-templates/
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @global object $post The current post object.
 */
global $wpdb, $post;

$post_type = $post->listing_type;

if (isset($_REQUEST['preview']) && $_REQUEST['preview'] && isset($_REQUEST['pid']) && $_REQUEST['pid'] != '') {
    $form_action_url = geodir_get_ajax_url() . '&geodir_ajax=add_listing&ajax_action=update&listing_type=' . $post_type;
} elseif (isset($_REQUEST['preview']) && $_REQUEST['preview']) {
    $form_action_url = geodir_get_ajax_url() . '&geodir_ajax=add_listing&ajax_action=publish&listing_type=' . $post_type;
}

/**
 * Filter the URL for the publish listing form on the preview page.
 *
 * @since 1.0.0
 * @param string $form_action_url The URL for the form.
 */
$form_action_url = apply_filters('geodir_publish_listing_form_action', $form_action_url);


?>
<?php
/**
 * Called on the add listing preview page before the publish listings form.
 *
 * @since 1.0.0
 * @see 'geodir_after_publish_listing_form'
 */
do_action('geodir_before_publish_listing_form');
ob_start()// start publish listing form buffering 
?>
    <div class="geodir_preview_section">

        <form action="<?php echo $form_action_url; ?>" name="publish_listing" id="publish_listing" method="post">
            <div class="clearfix">
                <input type="hidden" name="pid" value="<?php if (isset($post->pid)) {
                    echo $post->pid;
                } ?>">
                <?php
                /**
                 * Called on the add listing preview page inside the publish listings form, before the publish message.
                 *
                 * @since 1.0.0
                 * @see 'geodir_publish_listing_form_after_msg'
                 */
                do_action('geodir_publish_listing_form_before_msg'); ?>
                <?php
                $alive_days = UNLIMITED;
                $type_title = '';
                ob_start();
                echo '<h5 class="geodir_information">';

                if (!isset($_REQUEST['pid']))
                    printf(GOING_TO_FREE_MSG, $type_title, $alive_days);
                else
                    printf(GOING_TO_UPDATE_MSG, $type_title, $alive_days);

                echo '</h5>';
                $publish_listing_form_message = ob_get_clean();
                /**
                 * Filter the publish listing message on the preview page.
                 *
                 * @since 1.0.0
                 * @param string $publish_listing_form_message The message to be filtered.
                 */
                $publish_listing_form_message = apply_filters('geodir_publish_listing_form_message', $publish_listing_form_message);
                echo $publish_listing_form_message;

                /**
                 * Called on the add listing preview page inside the publish listings form, after the publish message.
                 *
                 * @since 1.0.0
                 * @see 'geodir_publish_listing_form_before_msg'
                 */
                do_action('geodir_publish_listing_form_after_msg');

                ob_start(); // start action button buffering
                ?>
                <?php if (isset($_REQUEST['pid']) && $_REQUEST['pid'] != '') { ?>

                    <input type="submit" name="Submit and Pay" value="<?php echo PRO_UPDATE_BUTTON; ?>"
                           class="geodir_button geodir_publish_button"/>
                <?php } else { ?>
                    <input type="submit" name="Submit and Pay" value="<?php echo PRO_SUBMIT_BUTTON; ?>"
                           class=" geodir_button geodir_publish_button"/>
                <?php
                }
                $publish_listing_form_button = ob_get_clean();
                /**
                 * Filter the HTML button for publishing the listing on the preview page.
                 *
                 * @since 1.0.0
                 * @param string $publish_listing_form_button The HTML for the submit button.
                 */
                $publish_listing_form_button = apply_filters('geodir_publish_listing_form_button', $publish_listing_form_button);
                echo $publish_listing_form_button;

                $post_id = '';
                if (isset($post->pid)) {
                    $post_id = $post->pid;
                } else if (isset($_REQUEST['pid'])) {
                    $post_id = (int)$_REQUEST['pid'];
                }

                $postlink = get_permalink(geodir_add_listing_page_id());
                $postlink = geodir_getlink($postlink, array('pid' => $post_id, 'backandedit' => '1', 'listing_type' => $post_type), false);

                ob_start(); // start go back and edit / cancel buffering
                ?>
                <a href="<?php echo esc_url($postlink); ?>" class="geodir_goback"><?php echo PRO_BACK_AND_EDIT_TEXT; ?></a>
                <input type="button" name="Cancel" value="<?php echo(PRO_CANCEL_BUTTON); ?>"
                       class="geodir_button geodir_cancle_button"
                       onclick="window.location.href='<?php echo geodir_get_ajax_url() . '&geodir_ajax=add_listing&ajax_action=cancel&pid=' . $post_id . '&listing_type=' . $post_type; ?>'"/>
                <?php

                $publish_listing_form_go_back = ob_get_clean();
                /**
                 * Filter the cancel and go back and edit HTML on the preview page.
                 *
                 * @since 1.0.0
                 * @param string $publish_listing_form_go_back The HTML for the cancel and go back and edit button/link.
                 */
                $publish_listing_form_go_back = apply_filters('geodir_publish_listing_form_go_back', $publish_listing_form_go_back);
                echo $publish_listing_form_go_back;

                ?>
            </div>
        </form>
    </div>
<?php
$publish_listing_form = ob_get_clean();
/**
 * Filter the HTML for the entire publish listing form preview page message and buttons etc.
 *
 * @since 1.0.0
 * @param string $publish_listing_form The publish listing HTML form.
 */
$publish_listing_form = apply_filters('geodir_publish_listing_form', $publish_listing_form);
echo $publish_listing_form;

/**
 * Called on the add listing preview page after the publish listings form.
 *
 * @since 1.0.0
 * @see 'geodir_before_publish_listing_form'
 */
do_action('geodir_after_publish_listing_form');
?>