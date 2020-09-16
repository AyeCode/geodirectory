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
 */
?>
<div class="geodir_post_taxomomies mx-0 mb-3 clearfix">
	<?php 
	
	if ( isset( $taxonomies[ $cat_taxonomy ] ) ) { 
		echo '<span class="geodir-category float-left">' . $taxonomies[ $cat_taxonomy ] . '</span>';
	}

	if ( isset( $taxonomies[ $tag_taxonomy ] ) ) {
		$align = !isset( $taxonomies[ $cat_taxonomy ] ) ? 'float-left' : 'float-right';
		echo '<span class="geodir-tags '.$align.'">' . $taxonomies[ $tag_taxonomy ] . '</span>';
	}

	?>
</div>
