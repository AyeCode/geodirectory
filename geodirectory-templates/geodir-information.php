<?php
/**
 * Template used to output warning/info messages
 *
 * @link http://docs.wpgeodirectory.com/customizing-geodirectory-templates/
 * @since 1.0.0
 * @package GeoDirectory
 */
get_header();
?>
    <div id="geodir_wrapper">
        <div class="clearfix">
            <div id="geodir_content">
                <?php
                /** This action is documented in geodirectory-templates/geodir-home.php */
                do_action('geodir_add_page_content', 'before', 'info-page');
                global $information;
                echo '<h5 class="geodir_information">';
                echo $information;
                echo '</h5>';
                /** This action is documented in geodirectory-templates/geodir-home.php */
                do_action('geodir_add_page_content', 'after', 'info-page');
                ?>
            </div>
            <!-- geodir_content ends here-->
            <div id="gd-sidebar-wrapper">
                <div class="geodir-sidebar-main">
                    <div class="geodir-gd-sidebar">
                        <?php
                        /**
                         * Calls the author sidebar.
                         *
                         * @since 1.6.11
                         */
                        dynamic_sidebar('geodir_author_right_sidebar'); ?>
                    </div>
                </div>
            </div>
            <!-- gd-sidebar-wrapper ends here-->
        </div>
        <!-- clearfix ends here-->
    </div><!-- geodir_wrapper ends here-->
<?php get_footer(); ?>