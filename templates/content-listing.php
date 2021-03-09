<?php
/**
 * The template for displaying listing content within loops
 *
 * This template can be overridden by copying it to yourtheme/geodirectory/content-listing.php.
 *
 * HOWEVER, on occasion GeoDirectory will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.wpgeodirectory.com/article/346-customizing-templates/
 * @author  AyeCode
 * @package GeoDirectory/Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $gd_post;

?>
<li <?php GeoDir_Post_Data::post_class(); ?> data-post-id="<?php echo esc_attr($gd_post->ID);?>">
	<?php

	// get content from GD Archive Item page template
	echo GeoDir_Template_Loader::archive_item_template_content($gd_post->post_type);

	?>
</li>