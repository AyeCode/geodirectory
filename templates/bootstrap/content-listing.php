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
 *
 * @var int $row_gap_class The row gap class setting.
 * @var int $column_gap_class The column gap class setting.
 * @var int $card_border_class The card border class setting.
 * @var int $card_shadow_class The card shadow class setting.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $gd_post,$geodir_item_tmpl;
//print_r( $geodir_item_tmpl ); echo '###';
$card_wrap = !empty($geodir_item_tmpl['type']) && 'template_part' === $geodir_item_tmpl['type'] ? false : true;
?>

<div <?php GeoDir_Post_Data::post_class("col ".$row_gap_class." ".$column_gap_class); ?> data-post-id="<?php echo esc_attr( $gd_post->ID ); ?>">
	<?php if($card_wrap){ ?>
	<div class="card h-100 p-0 m-0 mw-100 <?php echo sanitize_html_class($card_border_class); echo " ".sanitize_html_class($card_shadow_class);?>">
	<?php
	}

		// get content from GD Archive Item page template
		echo GeoDir_Template_Loader::archive_item_template_content( $gd_post->post_type );

	if($card_wrap){
	?>
	</div>
	<?php } ?>
</div>
