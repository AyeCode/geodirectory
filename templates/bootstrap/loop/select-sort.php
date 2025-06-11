<?php
/**
 * Select Sort Options
 *
 * This template can be overridden by copying it to yourtheme/geodirectory/bootstrap/loop/select-sort.php.
 *
 * HOWEVER, on occasion GeoDirectory will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see        https://wpgeodirectory.com/documentation/article/how-tos/customizing-templates/
 * @package    GeoDirectory
 * @version    2.8.119
 *
 * @param array  $sort_options Sort options.
 */

defined( 'ABSPATH' ) || exit;

global $aui_bs5;

// Get the items first so we can label the button with current sort option
$button_label = __( 'Sort By', 'geodirectory' );
$sort_options_html = '';

if ( ! empty( $sort_options ) ) {
	foreach ( $sort_options as $sort ) {
		$value = '';

		if ( $sort->field_type == 'random' ) {
			$value = "random";
		} else if ( $sort->sort == 'asc' ) {
			$value = $sort->htmlvar_name . "_asc";
		} else if ( $sort->sort == 'desc' ) {
			$value = $sort->htmlvar_name . "_desc";
		}

		$active = ( $value && ! empty( $_REQUEST['sort_by'] ) && $_REQUEST['sort_by'] == $value ) || ( $sort->is_default == '1' && ! isset( $_REQUEST['sort_by'] ) ) ? ' active' : '';

		if ( $active ) {
			$button_label = $sort->frontend_title;
		}

		$sort_options_html .= '<a href="' . esc_url( add_query_arg( 'sort_by', $value ) ) . '" class="dropdown-item' . esc_attr( $active ) . '" rel="nofollow">' . esc_html( $sort->frontend_title ) . '</a>';
	}
}
?>
<div class="btn-group btn-group-sm geodir-sort-by" role="group" aria-label="<?php esc_attr_e( "Sort by", "geodirectory" ); ?>">
	<button id="geodir-sort-by" type="button" class="btn btn-outline-primary <?php echo $aui_bs5 ? 'rounded-end dropdown-toggle dropdown-toggle-0' : 'rounded-right'; ?>" data-<?php echo ( $aui_bs5 ? 'bs-' : '' ); ?>toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo esc_html( $button_label ); ?> <i class="fas fa-sort"></i>
	</button>
	<div class="dropdown-menu dropdown-caret-0 my-3 p-0" aria-labelledby="gd-list-view-select-grid">
		<h6 class="dropdown-header"><?php echo esc_html__( "Sort Options", "geodirectory" ); ?></h6>
		<?php echo $sort_options_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<div class="dropdown-divider"></div>
		<a class="dropdown-item" href="<?php echo esc_url( remove_query_arg( 'sort_by' ) );?>" rel="nofollow"><?php echo esc_html__( "Default", "geodirectory" ); ?></a>
	</div>
</div>
