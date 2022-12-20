<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Variables.
 *
 * @var int $post_id The post_id.
 * @var string $link_class The class for the link.
 * @var string $onclick The onclick script actions.
 * @var string $title The title for the link.
 * @var string $icon_class The icon class.
 * @var string $icon_style The styles for the icon.
 * @var string $link_style The styles for the link.
 * @var string $text_style The styles for the text.
 * @var string $text The text to display.
 * @var string $text_class The class for the text.
 * @var string $wrap_class The class for the wrapper.
 * @var string $icon_color_on The color code for the icon set.
 * @var string $icon_color_off The color code for the icon unset.
 * @var string $show The string indicating what should be shown.
 * @var array  $args The raw arguments for the output.
 */

$text_class .= $show == 'icon' ? 'sr-only' : '';
$text = '<span class="geodir-fav-text gv-secondary '.$text_class.'" style="'.$text_style.'">'.$text.'</span>';
?>
<span class="geodir-addtofav favorite_property_<?php echo absint($post_id);  echo ' '.$wrap_class;?>">
	<?php
	echo aui()->badge(
		array(
			'type'       => 'a',
			'class'      =>  esc_attr($link_class),
			'title'      =>  $title,
			'content'    => $text ,
			'icon'      => $show == 'text' ? $icon_class . ' sr-only' : $icon_class,
			'href'       => 'javascript:void(0);',
			'onclick'   => $onclick,
			'style'     => $link_style,
			'extra_attributes'  => array(
				'data-color-on' => $icon_color_on,
				'data-icon' => $show == 'text' ? $icon_class . ' sr-only' : $icon_class,
				'data-color-off' => $icon_color_off,
				'data-toggle'   => 'tooltip',
			),
			'icon_extra_attributes'  => array(
				'style' => $icon_style,
			)
		)
	);
	?>
</span>
