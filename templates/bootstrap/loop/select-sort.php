<?php
/**
 * Select Sort
 *
 * @ver 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $aui_bs5;

// get the items first so we can label the button with current sort option
$button_label = esc_attr__( 'Sort By', 'geodirectory' );
$sort_options_html = '';
if(!empty($sort_options )) {
	foreach ( $sort_options as $sort ) {
		$value = '';
		if ( $sort->field_type == 'random' ) {
			$value = "random";
		} elseif ( $sort->sort == 'asc' ) {
			$value = esc_attr( $sort->htmlvar_name . "_asc" );
		} elseif ( $sort->sort == 'desc' ) {
			$value = esc_attr( $sort->htmlvar_name . "_desc" );
		}
		$active = ( $value && ! empty( $_REQUEST['sort_by'] ) && esc_attr( $_REQUEST['sort_by'] ) == $value ) || ( $sort->is_default == '1' && ! isset( $_REQUEST['sort_by'] ) ) ? 'active' : '';

		if($active){
			$button_label = esc_attr($sort->frontend_title );
		}
		$sort_options_html .= '<a href="'.esc_url( add_query_arg( 'sort_by', $value ) ).'" class="dropdown-item '.$active.'">'.esc_attr($sort->frontend_title ).'</a>';
	}
}
?>

<div class="btn-group btn-group-sm geodir-sort-by" role="group" aria-label="<?php esc_attr_e("Sort by","geodirectory");?>">
	<div class="btn-group btn-group-sm" role="group">
		<button id="geodir-sort-by" type="button" class="btn btn-outline-primary <?php echo $aui_bs5 ? 'rounded-end dropdown-toggle dropdown-toggle-0' : 'rounded-right'; ?>" data-<?php echo ( $aui_bs5 ? 'bs-' : '' ); ?>toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			<?php echo $button_label;?> <i class="fas fa-sort"></i>
		</button>
		<div class="dropdown-menu dropdown-caret-0 my-3 p-0" aria-labelledby="gd-list-view-select-grid">
			<h6 class="dropdown-header"><?php esc_attr_e("Sort Options","geodirectory");?></h6>
			<?php echo $sort_options_html;?>
			<div class="dropdown-divider"></div>
			<a class="dropdown-item" href="<?php echo esc_url( remove_query_arg( 'sort_by' ) );?>"><?php esc_attr_e("Default","geodirectory");?></a>
		</div>
	</div>
</div>
