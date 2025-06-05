<?php
/**
 * @see        https://wpgeodirectory.com/documentation/article/how-tos/customizing-templates/
 * @package    GeoDirectory\Templates
 * @version    2.8.118
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

global $aui_bs5;

// Category Text
$cat_class = 'stretched-link';
if ( empty( $args['cat_text_color'] ) ) {
	$cat_class .= ' text-reset';
}
if ( empty( $args['cat_font_size'] ) ) {
	$cat_class .= ' h6';
}
if ( empty( $args['cat_font_weight'] ) ) {
	$cat_class .= ' ' . ( $aui_bs5 ? 'fw-bold' : 'font-weight-bold' );
}

$cat_class .= ' ' . sd_build_aui_class(
	array(
		'text_color'  => $args['cat_text_color'],
		'font_size'   => $args['cat_font_size'],
		'font_weight' => $args['cat_font_weight'],
		'font_case'   => $args['cat_font_case'],
	)
);

$cat_class = normalize_whitespace( $cat_class );

// Count Text
$count_class = '';
if ( $aui_bs5 && ! empty( $args['badge_position'] ) && 'top-right' === $args['badge_position'] ) {
	$count_class .= ' position-absolute top-0 end-0';
} else if ( ! empty( $args['badge_position'] ) && 'block' === $args['badge_position'] ) {
	$cat_class .= ' d-block';
	$count_class .= ( $hide_icon ? 'mt-2' : 'mt-3' );
} else {
	$count_class .= ( $aui_bs5 ? 'ms-2' : 'ml-2' );
}

if ( ! $hide_count ) {
	// count text
	if ( ! empty( $args['badge_color'] ) && 'none' === $args['badge_color'] ) {
		// no class
	} else if ( ! empty( $args['badge_color'] ) && strpos( $args['badge_color'], 'translucent' ) !== false ) {
		$count_class .= ' badge btn-' . esc_attr( $args['badge_color'] );
	} else if ( ! empty( $args['badge_color'] ) ) {
		$count_class .= ' badge badge-' . esc_attr( $args['badge_color'] );
	} else {
		$count_class .= ' badge badge-light';// . ( $aui_bs5 ? 'text-bg-light' : 'badge-light' );
	}

	$count_class .= ' ' . sd_build_aui_class(
		array(
			'text_color' => $args['badge_text_color'],
			'font_size' => $args['badge_font_size'],
			'font_weight' => $args['badge_font_weight'],
			'font_case' => $args['badge_font_case'],
		)
	);

	$count = ' <span class="gd-cptcat-count ' . esc_attr( $count_class ) . '">' . $term_count_text . '</span>';
} else {
	$count = '';
}

$card_color = !empty($args['card_color']) ? sanitize_html_class($args['card_color']) : 'outline-primary';
$card_padding_inside = !empty($args['card_padding_inside']) ? absint($args['card_padding_inside']) : '4';
$icon = '';
if(!$hide_icon){
	$icon_size_class = isset($args['icon_size']) ? sanitize_html_class($args['icon_size']) : 'h1';
	if($icon_size_class=='box-large'){$icon_size_class = 'iconbox fill rounded-circle bg-white iconlarge';}
	if($icon_size_class=='box-medium'){$icon_size_class = 'iconbox fill rounded-circle bg-white iconmedium';}
	if($icon_size_class=='box-small'){$icon_size_class = 'iconbox fill rounded-circle bg-white iconsmall';}
	$icon_size_class .= ' d-inline-block mr-2 me-2 align-middle';
	$img_class = $icon_size_class;
	$icon_color_class = '';
	$icon_color =  !empty($args['icon_color']) ? sanitize_html_class($args['icon_color']) : '';
	if($icon_color){$icon_color_class = " text-$icon_color"; $cat_color = '';}
	$cat_color = $cat_color ? ' style="color:' . sanitize_hex_color( $cat_color ) . '"' : '';

	$icon = "<div class='gd-cptcat-cat-left border-0 m-0 overflow-hidden $img_class'><span class='gd-cptcat-icon" . $icon_color_class . "'" . $cat_color . ">$cat_icon</span></div>";
}
?>
<div class="card h-100 p-0 m-0 border-0 bg-transparent <?php echo $card_class; ?>">
	<div class="card-body position-relative text-center btn btn-<?php echo $card_color;?> p-1 py-<?php echo $card_padding_inside;?>">
		<div class="gd-cptcat-cat-right text-uppercase text-truncate">
			<a href="<?php echo esc_url($term_link);?>" class="<?php echo esc_attr( $cat_class ); ?>"><?php echo $icon . esc_attr( $cat_name );?></a><?php echo $count;?>
		</div>
<?php
// NOTE: The two closing divs are added in the main loop so that child cats can be added inside.
//	</div>
//</div>
?>
