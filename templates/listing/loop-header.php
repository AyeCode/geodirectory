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
 */
 ?>
 
<?php if ( !empty( $sorting ) || !empty( $layout_selection ) ) { ?>
<div class="clearfix">
    <?php if ( !empty( $sorting ) ) { ?>
        
        <?php echo $sorting; ?>
        
    <?php } ?>
    
    <?php if ( !empty( $layout_selection ) ) { ?>
        
        <?php echo $layout_selection; ?>
        
    <?php } ?>
</div>

<?php } ?>
