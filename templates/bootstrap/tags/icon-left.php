<?php
/**
 * Tag Icon Left
 *
 * This template can be overridden by copying it to yourtheme/geodirectory/bootstrap/tags/icon-left.php.
 *
 * HOWEVER, on occasion GeoDirectory will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see        https://wpgeodirectory.com/documentation/article/how-tos/customizing-templates/
 * @package    GeoDirectory\Templates
 * @version    2.8.103
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $aui_bs5;

// Tag Text
$tag_class = 'stretched-link';

if ( empty( $args['tag_text_color'] ) ) {
	$tag_class .= ' text-reset';
}
if ( empty( $args['tag_font_size'] ) ) {
	$tag_class .= ' h6';
}
if ( empty( $args['tag_font_weight'] ) ) {
	$tag_class .= ' ' . ( $aui_bs5 ? 'fw-bold' : 'font-weight-bold' );
}

$tag_class .= ' ' . sd_build_aui_class(
	array(
		'text_color'  => $args['tag_text_color'],
		'font_size'   => $args['tag_font_size'],
		'font_weight' => $args['tag_font_weight'],
		'font_case'   => $args['tag_font_case'],
	)
);

$tag_class = normalize_whitespace( $tag_class );

// Count Text
$count_class = '';
if ( $aui_bs5 && ! empty( $args['badge_position'] ) && 'top-right' === $args['badge_position'] ) {
	$count_class .= ' position-absolute top-0 end-0';
} else if ( ! empty( $args['badge_position'] ) && 'block' === $args['badge_position'] ) {
	$tag_class .= ' d-block';
	$count_class .= ( $hide_icon ? 'mt-2' : 'mt-3' );
} else {
	$count_class .= ( $aui_bs5 ? 'ms-2' : 'ml-2' );
}

if ( ! $hide_count ) {
	if ( ! empty( $args['badge_color'] ) && 'none' === $args['badge_color'] ) {
		// No class
	} else if ( ! empty( $args['badge_color'] ) && strpos( $args['badge_color'], 'translucent' ) !== false ) {
		$count_class .= ' badge btn-' . esc_attr( $args['badge_color'] );
	} else if ( ! empty( $args['badge_color'] ) ) {
		$count_class .= ' badge badge-' . esc_attr( $args['badge_color'] );
	} else {
		$count_class .= ' badge badge-light';
	}

	$count_class .= ' ' . sd_build_aui_class(
		array(
			'text_color' => $args['badge_text_color'],
			'font_size' => $args['badge_font_size'],
			'font_weight' => $args['badge_font_weight'],
			'font_case' => $args['badge_font_case'],
		)
	);

	$count_text = ' <span class="gd-cpttag-count ' . esc_attr( $count_class ) . '">' . $term_count_text . '</span>';
} else {
	$count_text = '';
}

$card_color = ! empty( $args['card_color'] ) ? sanitize_html_class( $args['card_color'] ) : 'outline-primary';
$card_padding_inside = ! empty( $args['card_padding_inside'] ) ? absint( $args['card_padding_inside'] ) : '4';
$icon = '';

if ( ! $hide_icon ) {
	$icon_size_class = isset( $args['icon_size'] ) ? sanitize_html_class( $args['icon_size'] ) : 'h1';

	if ( $icon_size_class == 'box-large' ) {
		$icon_size_class = 'iconbox fill rounded-circle bg-white iconlarge';
	} else if ( $icon_size_class == 'box-medium' ) {
		$icon_size_class = 'iconbox fill rounded-circle bg-white iconmedium';
	} else if ( $icon_size_class == 'box-small' ) {
		$icon_size_class = 'iconbox fill rounded-circle bg-white iconsmall';
	}

	$icon_size_class .= ' d-inline-block mr-1 me-1 align-middle';
	$icon_color_class = '';
	$icon_color = ! empty( $args['icon_color'] ) ? sanitize_html_class( $args['icon_color'] ) : '';

	if ( $icon_color ) {
		if ( strpos( $icon_color, 'translucent' ) !== false ) {
			$icon_size_class .= " btn-" . $icon_color . " transition-all";
		} else {
			$icon_size_class .= ' bg-white';
			$icon_color_class = " text-" . $icon_color;
		}

		$term_color = '';
	} else {
		$term_color = $term_color ? ' style="color:' . sanitize_hex_color( $term_color ) . '"' : '';
	}

	$icon = '<div class="gd-cpttag-tag-left border-0 m-0 overflow-hidden ' . esc_attr( trim( $icon_size_class ) ) . '"><span class="gd-cpttag-icon' . esc_attr( $icon_color_class ) . '"' . $term_color . '>' . $term_icon . '</span></div> ';
}
?>
<div class="card h-100 p-0 m-0 border-0 bg-transparent <?php echo esc_attr( $card_class ); ?>">
	<div class="card-body position-relative text-center btn btn-<?php echo esc_attr( $card_color );?> p-1 py-<?php echo esc_attr( $card_padding_inside );?>">
		<div class="gd-cpttag-tag-right text-truntage">
			<a href="<?php echo esc_url( $term_link );?>" class="<?php echo esc_attr( $tag_class ); ?>"><?php echo $icon . esc_attr( $term_name );?></a><?php echo $count_text;?>
		</div>
<?php
// NOTE: The two closing divs are added in the main loop so that child tags can be added inside.
//	</div>
//</div>
?>
