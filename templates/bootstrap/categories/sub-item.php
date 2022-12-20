<?php
/**
 * @see        https://docs.wpgeodirectory.com/article/346-customizing-templates/
 * @package    GeoDirectory\Templates
 * @version    2.1.0.11
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Variables.
 *
 * @var string $li_class_button_label The class for the main wrapper (not used).
 * @var bool $hide_count If the count should be hidden.
 * @var int $cat_count The count number.
 * @var string $cat_color The hex color for the icon.
 * @var string $term_link The link to the category.
 * @var string $cat_name The category name.
 * @var string $cat_icon The category icon HTML.
 * @var bool $hide_icon If the icon should be hidden.
 * @var bool $use_image If the category default image should be used.
 * @var int $depth The count of the depth of the sub category.
 * @var array $args All the raw widget arguments.
 */

$indents = $depth > 2 ? implode("", array_fill( 0,$depth - 2, "- " ) ) : '';
$count = !$hide_count ? ' <span class="gd-cptcat-count badge badge-light ml-2">' . $cat_count . '</span>' : '';

$icon_color_class = '';
$icon_color =  !empty($args['icon_color']) ? sanitize_html_class($args['icon_color']) : '';
if($icon_color){$icon_color_class = " text-$icon_color"; $cat_color = '';}
$cat_color = $cat_color ? ' style="color:' . sanitize_hex_color( $cat_color ) . '"' : '';
$icon = ! $hide_icon ? "<span class='gd-cptcat-icon mr-1" . $icon_color_class . "'" . $cat_color . ">$cat_icon</span>" : '';

?>
<div class="list-group-item list-group-item-action" >
	<div class="gd-cptcat-cat-right text-uppercase text-truncate">
		<a href="<?php echo esc_url($term_link);?>" class="text-reset stretched-link h6">
			<?php echo $indents . $icon . esc_attr( $cat_name ) . $count;?>
		</a>
	</div>
</div>
