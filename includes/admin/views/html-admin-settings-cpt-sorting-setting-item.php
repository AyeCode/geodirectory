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
<li class="dd-item <?php echo $tab_class;?>" data-id="1" id="setName_<?php echo $field->id;?>">
	<div class="dd-form">
		<i class="fas fa-caret-down" aria-hidden="true" onclick="gd_tabs_item_settings(this);"></i>
		<div class="dd-handle">
			<?php
			echo $field_icon;
			echo isset($field->frontend_title) ? geodir_ucwords( ' ' . $field->frontend_title ) : ''; ?>
			<span class="dd-key" title="<?php _e('Open/Close','geodirectory');?>"><?php echo ' (' . esc_attr( $tab_field_name ) . ')'; ?></span>
		</div>
		<div class="dd-setting <?php echo 'dd-type-'.esc_attr($field->field_type);?>">

			<input type="hidden" name="_wpnonce" value="<?php echo $nonce; ?>"/>
			<input type="hidden" name="post_type" id="post_type" value="<?php echo $field->post_type; ?>"/>
			<input type="hidden" name="field_type" id="field_type" value="<?php echo $field->field_type; ?>"/>
			<input type="hidden" name="field_id" id="field_id" value="<?php echo $field->id; ?>"/>
			<input type="hidden" name="data_type" id="data_type" value="<?php if ( isset( $field->data_type ) ) {echo $field->data_type;} ?>"/>
			<input type="hidden" name="htmlvar_name" id="htmlvar_name" value="<?php echo $field->htmlvar_name; ?>"/>

			<p class="dd-setting-name gd-advanced-setting">
				<label for="gd-admin-title-<?php echo $field->id;?>">
					<?php
					echo geodir_help_tip( __( 'This is the text used for the sort option.', 'geodirectory' ));
					_e('Frontend title','geodirectory') ?>
					<input type="text" name="frontend_title" id="frontend_title" value="<?php echo esc_attr( $frontend_title ) ?>"/>
				</label>
			</p>

			<?php if ( $field->field_type != 'random' ) { ?>

				<p class="dd-setting-name">
					<label for="gd-sort-<?php echo $field->id;?>">
						<?php
						echo geodir_help_tip( __( 'Select the sort direction: (A-Z or Z-A)', 'geodirectory' ));
						_e('Ascending or Descending','geodirectory') ?>
						<select name="sort" id="gd-sort-<?php echo $field->id;?>">
							<?php $value = isset( $field->sort ) && $field->sort=='desc'  ? 'desc' : 'asc'; ?>
							<option value="asc" <?php selected( 'asc', $value, true ); ?>><?php _e( 'Ascending', 'geodirectory' ); ?></option>
							<option	value="desc" <?php selected( 'desc', $value, true ); ?>><?php _e( 'Descending', 'geodirectory' ); ?></option>
						</select>
					</label>
				</p>
				
			<?php } ?>

				<p class="dd-setting-name dd-default-sort">
					<label for="gd-is_default-<?php echo $field->id;?>">
						<?php
						echo geodir_help_tip( __( 'This sets the option as the overall default sort value, there can be only one.', 'geodirectory' ));
						_e('Default sort?','geodirectory') ?>
						<input type="radio" name="is_default"
						       value="1" <?php if ( isset( $field->is_default ) && $field->is_default == 1 ) {
							echo 'checked="checked"';
						} ?>/>
					</label>
				</p>

				<p class="dd-setting-name">
					<label for="gd-is_active-<?php echo $field->id;?>">
						<?php
						echo geodir_help_tip( __( 'Set if this sort option is active or not, if not it will not be shown to users.', 'geodirectory' ));
						_e('Is active','geodirectory') ?>
						<?php $value = isset( $field->is_active ) && $field->is_active  ? $field->is_active : 0; ?>
						<input type="hidden" name="is_active" value="0" />
						<input type="checkbox" name="is_active" value="1" <?php checked( $value, 1, true );?> onclick="gd_show_hide_radio(this,'show','cfs-asc-title');" />
					</label>
				</p>

				<input type="hidden" readonly="readonly" name="sort_order" id="sort_order"
			       value="<?php if ( isset( $field->sort_order ) ) {
				       echo esc_attr( $field->sort_order );
			       } ?>" size="50"/>



				<p class="gd-tab-actions">
					<a class="item-delete submitdelete deletion" id="delete-16" href="javascript:void(0);" onclick="gd_delete_sort_field('<?php echo $field->id; ?>', '<?php echo $nonce; ?>', this);return false;"><?php _e("Remove","geodirectory");?></a>
					<input type="button" class="button button-primary" name="save" id="save" value="<?php _e("Save","geodirectory");?>" onclick="gd_save_sort_field('<?php echo esc_attr( $field->id ); ?>');return false;">
				</p>


		</div>
	</div>
</li>