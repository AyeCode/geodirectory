<?php
/**
 * Select Sort Options
 *
 * This template can be overridden by copying it to yourtheme/geodirectory/loop/select-sort.php.
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

		$selected = ( $value && ! empty( $_REQUEST['sort_by'] ) && $_REQUEST['sort_by'] == $value ) || ( $sort->is_default == '1' && ! isset( $_REQUEST['sort_by'] ) ) ? true : false;

		$sort_options_html .= '<option value="' . esc_url( add_query_arg( 'sort_by', $value ) ) . '" ' . selected( $selected, true, false ) . '>' . esc_html( $sort->frontend_title ) . '</option>';
	}
}
?>
<div class="geodir-tax-sort">
	<select name="sort_by" class="geodir-select geodir-sort-by" aria-label="<?php esc_attr_e( 'Sort By' ,'geodirectory' ); ?>">>
		<option value="<?php echo esc_url( add_query_arg( 'sort_by', '' ) ); ?>"><?php esc_html_e( 'Sort By', 'geodirectory' ); ?></option>
		<?php echo $sort_options_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</select>
</div>