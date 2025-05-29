<?php
/**
 * @see        https://wpgeodirectory.com/documentation/article/how-tos/customizing-templates/
 * @package    GeoDirectory\Templates
 * @version    2.8.103
 *
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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $aui_bs5;

$icon = '';
if ( ! $hide_icon ) {
	$img_class =  ' d-inline-block mr-1 me-1 align-middle h1';
	$cat_icon = str_replace('<i class="','<i style="opacity:0.8;" class="d-block ', $cat_icon);
	$icon = "<div class='gd-cptcat-cat-left border-0 m-0 overflow-hidden embed-responsive-item text-center $img_class' >$cat_icon</div>";
}

$cat_class = '';
$cat_style = '';
if ( $args['cat_text_color'] == 'custom' && ! empty( $args['cat_text_color_custom'] ) ) {
	$cat_style .= 'color:' . sanitize_hex_color( $args['cat_text_color_custom'] ) . ';';
}

$cat_style = $cat_style ? ' style="' . $cat_style . '"' : '';

// Category text
$cat_class .= ' stretched-link';
$cat_class .= $args['cat_text_color'] ? '' : ' text-white';
$cat_class .= empty( $args['cat_font_size'] ) ? ' h5' : '';
$cat_class .= empty( $args['cat_font_weight'] ) ? ( $aui_bs5 ? ' fw-bold' : ' font-weight-bold' ) : '';
$cat_class .= empty( $args['cat_font_case'] ) ? ' text-uppercase' : '';
$cat_class .= ' ' . sd_build_aui_class(
	array(
		'text_color'  => $args['cat_text_color'],
		'font_size'   => $args['cat_font_size'],
		'font_weight' => $args['cat_font_weight'],
		'font_case'   => $args['cat_font_case'],
	)
);

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
?>
<div class="card h-100 p-0 bg-dark overlayx overlay-blackx text-white border-0 rounded m-0 <?php echo $card_class; ?>">
	<a href="<?php echo esc_url($term_link);?>" class="embed-has-action embed-responsive embed-responsive-4by3 stretched-link">
	<?php echo $icon; ?>
	</a>
	<div class="card-img-overlay d-flex align-items-end text-center rounded p-0 pb-3 bg-shadow-bottom">
	<div class="card-body text-center btn btn-link p-1 overflow-hidden">
		<div class="gd-cptcat-cat-right text-truncate">
			<a href="<?php echo esc_url( $term_link ); ?>" class="<?php echo esc_attr( $cat_class ); ?>"<?php echo $cat_style; ?>>
				<?php echo esc_attr( $cat_name );?>
			</a>
			<?php echo $count;?>
		</div>
	</div>

<?php
// NOTE: The two closing divs are added in the main loop so that child cats can be added inside.
//	</div>
//</div>
?>
