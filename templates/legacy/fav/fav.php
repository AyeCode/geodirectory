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
 * @var string $text The text to display.
 * @var string $icon_color_on The color code for the icon set.
 * @var string $icon_color_off The color code for the icon unset.
 */

?>
<span class="geodir-addtofav favorite_property_<?php echo absint($post_id); ?>">
	<a class="<?php echo esc_attr($link_class);?>"
	   href="javascript:void(0);"
	   onclick="<?php echo $onclick; ?>"
	   title="<?php echo $title; ?>"
	   data-icon="<?php echo $icon_class; ?>"
	   data-color-on="<?php echo $icon_color_on; ?>"
	   data-color-off="<?php echo $icon_color_off; ?>"><i
			style="<?php echo $icon_style; ?>"
			class="<?php echo $icon_class; ?>"></i> <span
			class="geodir-fav-text"><?php echo esc_attr( $text ); ?></span>
	</a>
</span>
