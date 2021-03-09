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

echo aui()->select( array(
	'id'               => "{$map_canvas}_posttype",
	'class'            => 'custom-select-sm mb-1',
	'value'            => !empty($map_options['post_type']) ? esc_attr($map_options['post_type']) : '',
	'options'          => $map_post_types,
	'no_wrap'          => true,
	'extra_attributes' => array(
		'onchange' => "build_map_ajax_search_param('$map_canvas', true);",
		'aria-label' => __( 'Post Type', 'geodirectory' )
	)
) );