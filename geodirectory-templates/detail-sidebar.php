<?php
/**
 * Template for the details page sidebar for the add listing preview page ONLY.
 *
 * You can make most changes via hooks or see the link below for info on how to replace the template in your theme.
 *
 * @link http://docs.wpgeodirectory.com/customizing-geodirectory-templates/
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $post The current post object.
 * @global object $post_images Image objects of current post if available.
 */

global $post, $preview, $post_images;
$package_info = array();

$package_info = geodir_post_package_info($package_info, $post);
if (isset($package_info->google_analytics))
    $package_info->google_analytics = false;
$html = '';
?>
<div id="gd-sidebar-wrapper">
    <div class="geodir-sidebar-main">
        <?php ob_start(); ?>
        <div class="geodir-gd-sidebar">
            <?php ob_start();

            /** This action is documented in geodirectory_template_actions.php */
            do_action('geodir_detail_page_sidebar');
            $html = ob_get_clean();
            /**
             * Filter the details page sidebar HTML (add listing preview page only).
             *
             * @since 1.0.0
             * @param string $html The sidebar HTML.
             * @see 'geodir_detail_page_sidebar_html'
             */
            echo apply_filters('geodir_post_sidebar_html', $html);
            ?>
        </div>
        <!-- geodir-gd-sidebar ends here-->
        <?php
        $html = ob_get_clean();
        /**
         * Filter the details page sidebar HTML including the wrapper div (add listing preview page only).
         *
         * @since 1.0.0
         * @param string $html The sidebar HTML.
         * @see 'geodir_post_sidebar_html'
         * @see 'geodir_detail_page_sidebar'
         */
        echo apply_filters('geodir_detail_page_sidebar_html', $html);
        ?>
    </div>
    <!-- geodir-sidebar-main ends here-->

    <div class="geodir-sidebar-main">
        <div class="geodir-gd-sidebar">
            <?php dynamic_sidebar('geodir_detail_sidebar');
            /**
             * Calls the standard sidebar.
             *
             * @since 1.0.0
             */
            do_action('geodir_sidebar');
            ?>
        </div>
        <!-- geodir-gd-sidebar ends here-->
    </div>
    <!-- geodir-sidebar-main ends here-->
</div>  <!-- gd-sidebar-wrapper ends here-->
