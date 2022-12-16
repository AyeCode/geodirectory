<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Variables.
 *
 * @var array $args The widget args.
 * @var array taxonomies The array of taxonomies.
 * @var string $cat_taxonomy The category taxonomy slug.
 * @var string $tag_taxonomy The tag taxonomy slug.
 * @var string $wrap_class The wrapper class.
 */

global $aui_bs5;
?>
<div class="geodir_post_taxomomies clearfix <?php echo $wrap_class;?>">
	<?php 
	if ( isset( $taxonomies[ $cat_taxonomy ] ) ) { 
		echo '<span class="geodir-category ' . ( $aui_bs5 ? 'float-start' : 'float-left' ) . '">' . $taxonomies[ $cat_taxonomy ] . '</span>';
	}

	if ( isset( $taxonomies[ $tag_taxonomy ] ) ) {
		if ( $aui_bs5 ) {
			$align = ! isset( $taxonomies[ $cat_taxonomy ] ) ? 'float-start' : 'float-end';
		} else {
			$align = ! isset( $taxonomies[ $cat_taxonomy ] ) ? 'float-left' : 'float-right';
		}
		echo '<span class="geodir-tags '.$align.'">' . $taxonomies[ $tag_taxonomy ] . '</span>';
	}
	?>
</div>
