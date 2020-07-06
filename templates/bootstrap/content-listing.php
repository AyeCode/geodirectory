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
 * @see     https://wpgeodirectory.com/docs/customizing-geodirectory-templates/
 * @author  AyeCode
 * @package GeoDirectory/Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $gd_post;

?>

<div <?php GeoDir_Post_Data::post_class("col mb-4"); ?> data-post-id="<?php echo esc_attr( $gd_post->ID ); ?>">
	<div class="card h-100">
	<?php

		// get content from GD Archive Item page template
		echo GeoDir_Template_Loader::archive_item_template_content( $gd_post->post_type );

	?>
	</div>
</div>