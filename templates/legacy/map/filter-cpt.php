<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Variables.
 *
 * @var array $map_options The map settings options.
 * @var string $map_canvas The map canvas string.
 * @var array $map_post_types The custom post types for the map.
 */
?>
<!-- START post_type_filter -->
<div
	class="map-places-listing  geodir-post-type-filter-wrap"
	id="<?php echo $map_canvas; ?>_posttype_menu" style="max-width:100%!important;">
	<div class="geodir-map-posttype-list">
		<ul class="clearfix place-list">
			<?php
			foreach ( $map_post_types as $cpt => $cpt_name ) {
				$class = $map_options['post_type'] == $cpt ? ' class="gd-map-search-pt"' : '';
				?>
				<li id="<?php echo $cpt; ?>"<?php echo $class; ?>><a href="
							    javascript:void(0);"
				                                                     onclick="jQuery('#<?php echo $map_canvas; ?>_posttype').val('<?php echo $cpt; ?>');build_map_ajax_search_param('<?php echo $map_canvas; ?>', true)"><?php echo $cpt_name; ?></a>
				</li>
			<?php } ?>
		</ul>
	</div>
	<div class="geodir-map-navigation">
		<ul>
			<li class="geodir-leftarrow"><a href="#"><i class="fas fa-chevron-left"
			                                            aria-hidden="true"></i></a></li>
			<li class="geodir-rightarrow"><a href="#"><i class="fas fa-chevron-right"
			                                             aria-hidden="true"></i></a>
			</li>
		</ul>
	</div>
	<input type="hidden" id="<?php echo $map_canvas; ?>_posttype"
	       value="<?php echo $map_options['post_type']; ?>"/>
</div><!-- END post_type_filter -->