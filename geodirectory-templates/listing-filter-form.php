<?php
/**
 * Template for search bar used in the GD Search widget
 *
 * You can make most changes via hooks or see the link below for info on how to replace the template in your theme.
 *
 * @link http://docs.wpgeodirectory.com/customizing-geodirectory-templates/
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wp_query WordPress Query object.
 * @global object $wpdb WordPress Database object.
 */
global $wp_query, $current_term, $query;

$curr_post_type = geodir_get_current_posttype();

?>


<form class="<?php
/**
 * Filters the GD search form class.
 *
 * @since 1.0.0
 * @param string $class The class for the search form, default: 'geodir-listing-search'.
 */
echo apply_filters('geodir_search_form_class', 'geodir-listing-search'); ?>"
      name="geodir-listing-search" action="<?php echo home_url(); ?>" method="get">
    <input type="hidden" name="geodir_search" value="1"/>

    <div class="geodir-loc-bar">

        <?php
        /**
         * Called inside the search form but before any of the fields.
         *
         * @since 1.0.0
         */
        do_action('geodir_before_search_form') ?>

        <div class="clearfix geodir-loc-bar-in">

            <div class="geodir-search">

                <?php

                $default_search_for_text = SEARCH_FOR_TEXT;
                if (get_option('geodir_search_field_default_text'))
                    $default_search_for_text = __(get_option('geodir_search_field_default_text'), GEODIRECTORY_TEXTDOMAIN);

                $default_near_text = NEAR_TEXT;
                if (get_option('geodir_near_field_default_text'))
                    $default_near_text = __(get_option('geodir_near_field_default_text'), GEODIRECTORY_TEXTDOMAIN);

                $default_search_button_label = __('Search', GEODIRECTORY_TEXTDOMAIN);
                if (get_option('geodir_search_button_label'))
                    $default_search_button_label = __(get_option('geodir_search_button_label'), GEODIRECTORY_TEXTDOMAIN);

                $post_types = geodir_get_posttypes('object');

                if (!empty($post_types) && count((array)$post_types) > 1):
                    ?>
                    <select name="stype" class="search_by_post">
                        <?php foreach ($post_types as $post_type => $info):
                            global $wpdb;
                            $has_posts = '';
                            $has_posts = $wpdb->get_row($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_type = %s LIMIT 1", $post_type));
                            if (!$has_posts) {
                                continue;
                            }
                            ?>

                            <option opt_label="<?php echo get_post_type_archive_link($post_type);?>"
                                    value="<?php echo $post_type;?>" <?php if (isset($_REQUEST['stype'])) {
                                if ($post_type == $_REQUEST['stype']) {
                                    echo 'selected="selected"';
                                }
                            } elseif ($curr_post_type == $post_type) {
                                echo 'selected="selected"';
                            }?>><?php _e(ucfirst($info->labels->name), GEODIRECTORY_TEXTDOMAIN);?></option>

                        <?php endforeach; ?>
                    </select>
                <?php elseif (!empty($post_types)):
                    echo '<input type="hidden" name="stype" value="' . key($post_types) . '"  />';
                endif; ?>

                <input class="search_text" name="s"
                       value="<?php if (isset($_REQUEST['s']) && trim($_REQUEST['s']) != '') {
                           echo $_REQUEST['s'];
                       } else {
                           echo $default_search_for_text;
                       } ?>" type="text"
                       onblur="if (this.value == '') {this.value = '<?php echo $default_search_for_text; ?>';}"
                       onfocus="if (this.value == '<?php echo $default_search_for_text; ?>') {this.value = '';}"
                       onkeydown="javascript: if(event.keyCode == 13) geodir_click_search(this);">


                <?php
                if (isset($_REQUEST['snear']) && $_REQUEST['snear'] != '') {
                    $near = stripslashes($_REQUEST['snear']);
                } else {
                    $near = $default_near_text;
                }
                /**
                 * Filter the "Near" text value for the search form.
                 *
                 * This is the input "value" attribute and can change depending on what the user searches and is not always the default value.
                 *
                 * @since 1.0.0
                 * @param string $near The current near value.
                 * @param string $default_near_text The default near value.
                 */
                $near = apply_filters('geodir_search_near_text', $near, $default_near_text);
                /**
                 * Filter the default "Near" text value for the search form.
                 *
                 * This is the default value if nothing has been searched.
                 *
                 * @since 1.0.0
                 * @param string $near The current near value.
                 * @param string $default_near_text The default near value.
                 */
                $default_near_text = apply_filters('geodir_search_default_near_text', $default_near_text, $near);
                /**
                 * Filter the class for the near search input.
                 *
                 * @since 1.0.0
                 * @param string $class The class for the HTML near input, default is blank.
                 */
                $near_class = apply_filters('geodir_search_near_class', '');
                ?>

                <input name="snear" class="snear <?php echo $near_class; ?>" type="text" value="<?php echo $near; ?>"
                       onblur="if (this.value == '') {this.value = ('<?php echo $near; ?>' != '' ? '<?php echo $near; ?>' : '<?php echo $default_near_text; ?>');}"
                       onfocus="if (this.value == '<?php echo $default_near_text; ?>' || this.value =='<?php echo $near; ?>') {this.value = '';}"
                       onkeydown="javascript: if(event.keyCode == 13) geodir_click_search(this);"/>

                <?php
                /**
                 * Called on the GD search form just before the search button.
                 *
                 * @since 1.0.0
                 */
                do_action('geodir_before_search_button'); ?>
                <input type="button" value="<?php echo $default_search_button_label; ?>" class="geodir_submit_search">
                <?php
                /**
                 * Called on the GD search form just after the search button.
                 *
                 * @since 1.0.0
                 */
                do_action('geodir_after_search_button'); ?>
            </div>


        </div>

        <?php
        /**
         * Called inside the search form but after all the input fields.
         *
         * @since 1.0.0
         */
        do_action('geodir_after_search_form') ?>


    </div>
    <input name="sgeo_lat" class="sgeo_lat" type="hidden" value=""/>
    <input name="sgeo_lon" class="sgeo_lon" type="hidden" value=""/>
</form>
