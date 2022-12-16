<?php
/**
 * Admin custom field sorting form
 *
 * @since 2.0.0
 *
 * @package GeoDirectory
 */

//print_r($field);
$tab_class = isset($field->field_type) && $field->field_type=='random' ? 'mjs-nestedSortable-no-nesting' : '';
$tab_field_name = isset( $field->field_type ) && $field->field_type == 'random' ? 'random' : $field->htmlvar_name;
?>
<li class="dd-item mb-0 <?php echo $tab_class;?>" data-id="1" id="setName_<?php echo esc_attr( $field->id );?>" data-field-nonce="<?php echo esc_attr( $nonce ); ?>">
	<div class="dd-form hover-shadow d-flex justify-content-between rounded c-pointer list-group-item border rounded-smx text-left text-start bg-light <?php if(empty($field->is_active)){echo 'border-warning';} ?>" onclick="gd_tabs_item_settings(this);">
		<div class="  flex-fill font-weight-bold fw-bold">
			<?php echo $field_icon; ?>
			<?php echo isset($field->frontend_title) ? geodir_ucwords( ' ' . esc_html( $field->frontend_title ) ) : ''; ?>
			<span class="float-right text-right float-end text-end small" title="<?php _e('Open/Close','geodirectory');?>"><?php echo ' ('.esc_attr( $tab_field_name ).')';?></span>
		</div>
		<div class="dd-handle">
			<?php
			if(empty($field->is_active)){
			?>
			<i class="fas fa-exclamation-triangle text-warning ml-2 ms-2" title="<?php _e("Inactive","geodirectory");?>" data-toggle="tooltip"></i>
			<?php } ?>
			<i class="fas fa-check-circle ml-2 ms-2 text-primary gd-is-default <?php if(empty($field->is_default)){echo 'd-none';}?>" title="<?php _e("Default sort option","geodirectory");?>" data-toggle="tooltip"></i>
			<i class="far fa-trash-alt text-danger ml-2 ms-2" id="delete-16"  onclick="gd_delete_sort_field('<?php echo esc_attr( $field->id ); ?>', '<?php echo esc_attr( $nonce ); ?>', this);event.stopPropagation();return false;"></i>
			<i class="fas fa-grip-vertical text-muted ml-2 ms-2" style="cursor: move" aria-hidden="true" ></i>
		</div>
		<script type="text/template" class="dd-setting <?php echo 'dd-type-'.esc_attr($field->field_type);?> d-none">

			<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( $nonce ); ?>"/>
			<input type="hidden" name="post_type" id="post_type" value="<?php echo esc_attr( $field->post_type ); ?>"/>
			<input type="hidden" name="field_type" id="field_type" value="<?php echo esc_attr( $field->field_type ); ?>"/>
			<input type="hidden" name="field_id" id="field_id" value="<?php echo esc_attr( $field->id ); ?>"/>
			<input type="hidden" name="data_type" id="data_type" value="<?php if ( isset( $field->data_type ) ) {echo esc_attr( $field->data_type );} ?>"/>
			<input type="hidden" name="htmlvar_name" id="htmlvar_name" value="<?php echo esc_attr( $field->htmlvar_name ); ?>"/>

			<?php
			echo aui()->input(
				array(
					'id'                => 'frontend_title',
					'name'              => 'frontend_title',
					'label_type'        => 'top',
					'label'              => __('Frontend title','geodirectory') . geodir_help_tip( __( 'This is the text used for the sort option.', 'geodirectory' )),
					'type'              =>   'text',
					'value' => esc_attr( $frontend_title),
				)
			);

			if ( $field->field_type != 'random' ) {
				$value = isset( $field->sort ) && $field->sort=='desc'  ? 'desc' : 'asc';

				echo aui()->select(
					array(
						'id'         => 'gd-sort-' . esc_attr( $field->id ),
						'name'       => 'sort',
						'label_type' => 'top',
						'multiple'   => false,
						'class'      => ' mw-100',
						'options'    => array(
							'asc'  => __( 'Ascending', 'geodirectory' ),
							'desc' => __( 'Descending', 'geodirectory' ),
						),
						'label'      => __( 'Ascending or Descending', 'geodirectory' ) . geodir_help_tip( __( 'Select the sort direction: (A-Z or Z-A)', 'geodirectory' ) ),
						'value'      => $value
					)
				);
			}

			echo aui()->input(
				array(
					'id'                => 'is_default',
					'name'              => 'is_default',
					'label_type'        => 'horizontal',
					'label_col'        => '3',
					'label'              => __('Default sort','geodirectory') ,
					'type'              =>   'checkbox',
					'checked' => isset( $field->is_default ) && $field->is_default == 1 ? 1 : 0,
					'value' => '1',
					'switch'    => 'md',
					'label_force_left'  => true,
					'help_text' => geodir_help_tip( __( 'This sets the option as the overall default sort value, there can be only one.', 'geodirectory' ))
				)
			);

//			$value = isset( $field->is_active ) && $field->is_active  ? $field->is_active : 0;
			echo aui()->input(
				array(
					'id'                => 'is_active',
					'name'              => 'is_active',
					'label_type'        => 'horizontal',
					'label_col'        => '3',
					'label'              => __('Is active','geodirectory') ,
					'type'              =>   'checkbox',
					'checked' => isset( $field->is_active ) && $field->is_active ? 1 : 0,
					'value' => '1',
					'switch'    => 'md',
					'label_force_left'  => true,
					'help_text' => geodir_help_tip( __( 'Set if this sort option is active or not, if not it will not be shown to users.', 'geodirectory' ))
				)
			);

			?>


				<input type="hidden" readonly="readonly" name="sort_order" id="sort_order"
			       value="<?php if ( isset( $field->sort_order ) ) {
				       echo esc_attr( $field->sort_order );
			       } ?>" size="50"/>



			<div class="gd-tab-actions text-right mb-0">
				<a class=" btn btn-link text-muted" href="javascript:void(0);" onclick="gd_tabs_close_settings(this); return false;"><?php _e("Close","geodirectory");?></a>
				<button type="button" class="btn btn-primary" name="save" id="save" data-save-text="<?php _e("Save","geodirectory");?>"  onclick="gd_save_sort_field('<?php echo esc_attr( $field->id ); ?>',event);jQuery(this).html('<span class=\'spinner-border spinner-border-sm\' role=\'status\'></span> <?php esc_attr_e( 'Saving', 'geodirectory' ); ?>').addClass('disabled');return false;">
					<?php _e("Save","geodirectory");?>
				</button>
			</div>



		</script>
	</div>
</li>
