<?php
/**
 * Add To Favorite
 *
 * This template can be overridden by copying it to yourtheme/geodirectory/bootstrap/fav/fav.php.
 *
 * HOWEVER, on occasion GeoDirectory will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see        https://wpgeodirectory.com/documentation/article/how-tos/customizing-templates/
 * @package    GeoDirectory
 * @version    2.2.24
 *
 * Variables.
 *
 * @var int $post_id The post_id.
 * @var string $link_class The class for the link.
 * @var string $onclick The onclick script actions.
 * @var string $title The title for the link.
 * @var string $icon_class The icon class.
 * @var string $icon_style The styles for the icon.
 * @var string $link_style The styles for the link.
 * @var string $text_color The color code for the text.
 * @var string $text_style The styles for the text.
 * @var string $text The text to display.
 * @var string $text_class The class for the text.
 * @var string $wrap_class The class for the wrapper.
 * @var string $icon_color_on The color code for the icon set.
 * @var string $icon_color_off The color code for the icon unset.
 * @var string $show The string indicating what should be shown.
 * @var array $args The raw arguments for the output.
 */

defined( 'ABSPATH' ) || exit;

$text_class .= $show == 'icon' ? 'sr-only visually-hidden' : '';
$text        = '<span class="geodir-fav-text gv-secondary ' . $text_class . '" style="' . $text_style . '">' . $text . '</span>';

echo '<span class="geodir-addtofav favorite_property_' . absint( $post_id ) . ' ' . esc_attr( $wrap_class ) . '">';
	echo aui()->badge(
		array(
			'type'                  => 'badge',
			'class'                 => esc_attr( $link_class ),
			'title'                 => $title,
			'content'               => $text,
			'icon'                  => $show == 'text' ? $icon_class . ' sr-only visually-hidden' : $icon_class,
			'href'                  => '',
			'onclick'               => $onclick,
			'style'                 => $link_style,
			'extra_attributes'      => array(
				'data-color-on'  => $icon_color_on,
				'data-icon'      => $show == 'text' ? $icon_class . ' sr-only visually-hidden' : $icon_class,
				'data-color-off' => $icon_color_off,
				'data-text-color'=> $text_color,
				'data-toggle'    => 'tooltip',
			),
			'icon_extra_attributes' => array(
				'style' => $icon_style,
			),
		)
	);
echo '</span>';
