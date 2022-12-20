<?php
/**
 * @see        https://docs.wpgeodirectory.com/article/346-customizing-templates/
 * @package    GeoDirectory\Templates
 * @version    2.1.1.4
 */

//$cpt_row = $depth ? '<div class="gd-cptcat-li '.$li_class.' list-group-item list-group-item-action" >' :  '<div class="gd-cptcat-li '.$li_class.' card h-100 shadow-sm p-0 " >';
//$cpt_row .= $depth ? '' : '<div class="card-body text-center btn btn-outline-primary p-1 py-4">';
//$count = !$hide_count ? ' <span class="gd-cptcat-count badge badge-light ml-2">' . $cat_count . '</span>' : '';
//
//$icon = '';
//if(!$hide_icon){
//	$icon_size_class = isset($args['icon_size']) ? sanitize_html_class($args['icon_size']) : 'h1';
//	if($icon_size_class=='box-large'){$icon_size_class = 'iconbox fill rounded-circle bg-white iconlarge';}
//	if($icon_size_class=='box-medium'){$icon_size_class = 'iconbox fill rounded-circle bg-white iconmedium';}
//	if($icon_size_class=='box-small'){$icon_size_class = 'iconbox fill rounded-circle bg-white iconsmall';}
//	$icon_size_class .= $args['icon_position'] == 'top' ? ' mb-3 ' : ' d-inline-block mr-1 align-middle';
//	$img_class = $depth ? ' d-inline-block iconsmall mr-1' : $icon_size_class;
//	$icon .= '<div class="gd-cptcat-cat-left  text-whitex border-0 m-0 '.$img_class.'" >';
//	$icon .= "<span class='gd-cptcat-icon' style='color: $cat_color;'>$cat_icon</span>";
//	$icon .= '</div>';
//}
//
//if(!$depth && $args['icon_position'] != 'left'){$cpt_row .= $icon;}
//
//$indents = $depth > 2 ? implode("", array_fill( 0,$depth - 2, "- " ) ) : '';
//
//$link_class = $depth ? 'h6' : 'font-weight-bold h5';
//$cpt_row .= '<div class="gd-cptcat-cat-right   text-uppercase text-truncate">';
//$cpt_row .= '<a href="' . esc_url($term_link) . '" title="' . esc_attr($cat_name) . '" class="text-lightx text-reset stretched-link   '.$link_class.'">';
//$cpt_row .= $indents;
//$cpt_row .= $args['icon_position'] == 'left' ? $icon : '';
//$cpt_row .= $cat_name  . '</a>'. $count;
//$cpt_row .= $depth  ? '</div></div>' : '</div>';


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

$count = !$hide_count ? ' <span class="gd-cptcat-count badge badge-light ml-2">' . $cat_count . '</span>' : '';
$card_color = !empty($args['card_color']) ? sanitize_html_class($args['card_color']) : 'outline-primary';
$card_padding_inside = !empty($args['card_padding_inside']) ? absint($args['card_padding_inside']) : '4';
$icon = '';
if(!$hide_icon){
	$icon_size_class = isset($args['icon_size']) ? sanitize_html_class($args['icon_size']) : 'h1';
	if($icon_size_class=='box-large'){$icon_size_class = 'iconbox fill rounded-circle bg-white iconlarge';}
	if($icon_size_class=='box-medium'){$icon_size_class = 'iconbox fill rounded-circle bg-white iconmedium';}
	if($icon_size_class=='box-small'){$icon_size_class = 'iconbox fill rounded-circle bg-white iconsmall';}
	$icon_size_class .= ' d-inline-block align-middle';
	$img_class =  $icon_size_class;
	$icon_color_class = '';
	$icon_color =  !empty($args['icon_color']) ? sanitize_html_class($args['icon_color']) : '';
	if($icon_color){$icon_color_class = " text-$icon_color"; $cat_color = '';}
	$cat_color = $cat_color ? ' style="color:' . sanitize_hex_color( $cat_color ) . '"' : '';

	$icon = "<div class='gd-cptcat-cat-left border-0 mb-3 overflow-hidden $img_class'><span class='gd-cptcat-icon" . $icon_color_class . "'" . $cat_color . ">$cat_icon</span></div>";
}

?>
<div class="card h-100 p-0 m-0 border-0 bg-transparent <?php echo $card_class; ?>">
	<div class="card-body text-center btn btn-<?php echo $card_color;?> p-1 py-<?php echo $card_padding_inside;?>">
		<?php echo $icon; ?>
		<div class="gd-cptcat-cat-right text-uppercase text-truncate">
			<a href="<?php echo esc_url($term_link);?>" class="text-reset stretched-link font-weight-bold h6">
				<?php echo esc_attr( $cat_name );?>
			</a>
			<?php echo $count;?>
		</div>

<?php
// NOTE: The two closing divs are added in the main loop so that child cats can be added inside.
//	</div>
//</div>
?>
