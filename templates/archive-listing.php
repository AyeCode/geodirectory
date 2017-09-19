<?php
/**
 * Template for the listings (category) page
 *
 * You can make most changes via hooks or see the link below for info on how to replace the template in your theme.
 *
 * @link http://docs.wpgeodirectory.com/customizing-geodirectory-templates/
 * @since 1.0.0
 * @package GeoDirectory
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
?>

<?php get_header( 'geodirectory' ); ?>

    <?php
        /**
         *
         * @hook geodir_output_content_wrapper_start - 10 (outputs opening divs for the content)
         * @hook geodir_breadcrumb - 20
         */
        do_action( 'geodir_before_main_content' );
    ?>
    
    <?php if ( apply_filters( 'geodir_show_page_title', true ) ) { ?>
        <?php geodir_get_template_part( 'title', 'archive' ); ?>
    <?php } ?>

        <?php
            /**
             *
             * @hook geodir_taxonomy_archive_description - 10
             * @hook geodir_product_archive_description - 10
             */
            do_action( 'geodir_archive_description' );
        ?>
        <?php if ( have_posts() ) { ?>

            <?php
                /**
                 *
                 * @hook geodir_print_notices - 10
                 * @hook geodir_result_count - 20
                 * @hook geodir_catalog_ordering - 30
                 */
                do_action( 'geodir_before_directory_loop' );
            ?>

            <?php geodir_listing_loop_start(); ?>

                <?php while ( have_posts() ) { ?>
                    <?php the_post(); ?>

                    <?php do_action( 'geodir_directory_loop' ); ?>

                    <?php geodir_get_template_part( 'content', 'listing' ); ?>

                <?php } // end of the loop. ?>

            <?php geodir_listing_loop_end(); ?>

            <?php
                /**
                 *
                 * @hook geodir_pagination - 10
                 */
                do_action( 'geodir_after_directory_loop' );
            ?>

        <?php } else { ?>

            <?php
                /**
                 *
                 * @hook geodir_no_products_found - 10
                 */
                do_action( 'geodir_no_listings_found' );
            ?>

        <?php } ?>

    <?php
        /**
         *
         * @hook geodir_output_content_wrapper_end - 10 (outputs closing divs for the content)
         */
        do_action( 'geodir_after_main_content' );
    ?>
    
    <?php
        /**
         *
         * @hook geodir_get_sidebar - 10
         */
        do_action( 'geodir_sidebar' );
    ?>
    
<?php get_footer( 'geodirectory' ); ?>