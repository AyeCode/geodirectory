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
<p class="geodir_post_taxomomies clearfix">
	<?php

	if ( isset( $taxonomies[ $cat_taxonomy ] ) ) {
		echo '<span class="geodir-category">' . $taxonomies[ $cat_taxonomy ] . '</span>';
	}

	if ( isset( $taxonomies[ $tag_taxonomy ] ) ) {
		$align_left = !isset( $taxonomies[ $cat_taxonomy ] ) ? 'style="float:left"' : '';
		echo '<span class="geodir-tags" '.$align_left .'>' . $taxonomies[ $tag_taxonomy ] . '</span>';
	}

	?>
</p>
