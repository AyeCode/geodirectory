<?php
/**
 * Select Sort (default)
 *
 * @ver 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="geodir-tax-sort">

	<select name="sort_by" class="geodir-select geodir-sort-by" aria-label="<?php esc_attr_e( 'Sort By' ,'geodirectory' ); ?>">>
		<option
			value="<?php echo esc_url( add_query_arg( 'sort_by', '' ) ); ?>" <?php if ( empty($_REQUEST['sort_by']) ) {
			echo 'selected="selected"';
		} ?>><?php _e( 'Sort By', 'geodirectory' ); ?></option>
		<?php
		foreach($sort_options as $sort){
			$value = '';
			if($sort->field_type == 'random'){$value = "random";}
			elseif($sort->sort == 'asc'){$value = esc_attr($sort->htmlvar_name."_asc");}
			elseif($sort->sort == 'desc'){$value = esc_attr($sort->htmlvar_name."_desc");}
			$selected = ( $value && !empty($_REQUEST['sort_by']) && esc_attr($_REQUEST['sort_by']) == $value ) || ( $sort->is_default == '1' && ! isset( $_REQUEST['sort_by'] ) )  ?  'selected="selected"' : '';
			echo '<option ' . $selected . ' value="' . esc_url( add_query_arg( 'sort_by', $value ) ) . '">' . esc_attr($sort->frontend_title) . '</option>';
		}
		?>
	</select>

</div>