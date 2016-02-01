<?php
/**
 * Template for the message on the success page after submitting a listing.
 *
 * You can make most changes via hooks or see the link below for info on how to replace the template in your theme.
 *
 * @link http://docs.wpgeodirectory.com/customizing-geodirectory-templates/
 * @since 1.0.0
 * @since 1.5.7 Added db translations for post success message.
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 */
?>
<div class="geodir_preview_section">
    <?php


    global $wpdb;

    $post_id = $_REQUEST['pid'];
    $post_info = get_post($post_id);

    $posted_date = $post_info->post_date;
    $productlink = get_permalink($post_id);
    $siteName = get_bloginfo('name');
    $siteurl = home_url();
    $siteurl_link = '<a href="' . $siteurl . '">' . $siteurl . '</a>';

    $loginurl = geodir_login_url();
    $loginurl_link = '<a href="' . $loginurl . '">login</a>';

    $post_author = $post_info->post_author;

    $user_info = get_userdata($post_author);
    $username = $user_info->user_login;
    $user_email = $user_info->user_email;

    $message = wpautop(__(stripslashes_deep(get_option('geodir_post_added_success_msg_content')),'geodirectory'));

    /*
     * Filter the success page message before variable replacements.
     *
     * @since 1.5.7
     * @param string The message string.
     * @param object $post_info Post object.
     * @param object $user_info User object.
     */
    $message = apply_filters('geodir_success_page_msg_before_var_replace', $message,$post_info, $user_info);

    $search_array = array('[#submited_information_link#]', '[#listing_link#]', '[#site_name_url#]', '[#post_id#]', '[#site_name#]', '[#user_email#]', '[#username#]', '[#login_url#]', '[#posted_date#]');
    $replace_array = array($productlink, $productlink, $siteurl_link, $post_id, $siteName, $user_email, $username, $loginurl_link, $posted_date);
    $message = str_replace($search_array, $replace_array, $message);

    /*
     * Filter the success page message after variable replacements.
     *
     * @since 1.5.7
     * @param string The message string.
     * @param object $post_info Post object.
     * @param object $user_info User object.
     */
    $message = apply_filters('geodir_success_page_msg_after_var_replace', $message,$post_info, $user_info);



    ?>

    <?php

    /*
     * Action called before the success page message wrapper.
     *
     * @since 1.5.7
     * @param string The message string.
     * @param object $post_info Post object.
     * @param object $user_info User object.
     */
    do_action('geodir_before_success_page_msg_wrapper', $message,$post_info, $user_info);
    echo '<h5 class="geodir_information">';
    echo $message;
    echo '</h5>';
    /*
     * Action called after the success page message wrapper.
     *
     * @since 1.5.7
     * @param string The message string.
     * @param object $post_info Post object.
     * @param object $user_info User object.
     */
    do_action('geodir_after_success_page_msg_wrapper', $message,$post_info, $user_info);

    ?>

</div>