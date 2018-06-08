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
?>

<li class="dd-item <?php echo $tab_class;?>" data-id="1" id="setName_<?php echo $field->id;?>">
	<div class="dd-form">
		<i class="fa fa-caret-down" aria-hidden="true" onclick="gd_tabs_item_settings(this);"></i>
		<div class="dd-handle">
			<?php
			echo $field_icon;
			echo isset($field->frontend_title) ? geodir_ucwords( ' ' . $field->frontend_title ) : ''; ?>
			<span class="dd-key" title="<?php _e('Open/Close','geodirectory');?>"><?php echo ' ('.esc_attr($field->htmlvar_name).')';?></span>
		</div>
		<div class="dd-setting <?php echo 'dd-type-'.esc_attr($field->field_type);?>">

			<input type="hidden" name="_wpnonce" value="<?php echo $nonce; ?>"/>
			<input type="hidden" name="post_type" id="post_type" value="<?php echo $field->post_type; ?>"/>
			<input type="hidden" name="field_type" id="field_type" value="<?php echo $field->field_type; ?>"/>
			<input type="hidden" name="field_id" id="field_id" value="<?php echo $field->id; ?>"/>
			<input type="hidden" name="data_type" id="data_type" value="<?php if ( isset( $field->data_type ) ) {echo $field->data_type;} ?>"/>
			<input type="hidden" name="htmlvar_name" id="htmlvar_name" value="<?php echo $field->htmlvar_name; ?>"/>

			<?php if ( $field->field_type != 'random' ) { ?>

				<input type="hidden" name="frontend_title" id="frontend_title"
				       value="<?php echo esc_attr( $frontend_title ); ?>"/>

				<p class="dd-setting-name" data-gdat-display-switch-set="gdat-asc-sort">
					<label for="gd-asc-<?php echo $field->id;?>">
						<?php
						echo geodir_help_tip( __( 'Select if you want to show this option in the sort options. (A-Z,0-100 or OFF)', 'geodirectory' ));
						_e('Show Ascending Sort (low to high)','geodirectory') ?>
						<?php $value = ( isset( $field->sort_asc ) && $field->sort_asc ) ? $field->sort_asc : 0; ?>
						<input type="hidden" name="asc" value="0" />
						<input type="checkbox" name="asc" value="1" <?php checked( $value, 1, true );?> onclick="gd_show_hide_radio(this,'show','cfs-asc-title');" />
					</label>
				</p>

				<p class="dd-setting-name cfs-asc-title gdat-asc-sort">
					<label for="gd-asc_title-<?php echo $field->id;?>">
						<?php
						echo geodir_help_tip( __( 'This is the text used for the sort option.', 'geodirectory' ));
						_e('Ascending title','geodirectory') ?>
						<?php $value = ( isset( $field->asc_title ) && $field->asc_title ) ? esc_attr( $field->asc_title ) : ''; ?>
						<input type="text" name="asc_title" id="asc_title" value="<?php echo $value; ?>"/>
					</label>
				</p>

				<p class="dd-setting-name cfs-asc-title gdat-asc-sort">
					<label for="gd-is_default-<?php echo $field->id;?>">
						<?php
						echo geodir_help_tip( __( 'This sets the option as the overall default sort value, there can be only one.', 'geodirectory' ));
						_e('Default sort?','geodirectory') ?>
						<input type="radio" name="is_default"
						       value="<?php echo $field->htmlvar_name; ?>_asc" <?php if ( isset( $field->default_order ) && $field->default_order == $field->htmlvar_name . '_asc' ) {
							echo 'checked="checked"';
						} ?>/>
					</label>
				</p>

				<p class="dd-setting-name" data-gdat-display-switch-set="gdat-desc-sort">
					<label for="gd-desc-<?php echo $field->id;?>">
						<?php
						echo geodir_help_tip( __( 'Select if you want to show this option in the sort options. (Z-A,100-0 or ON)', 'geodirectory' ));
						_e('Show Descending Sort (high to low)','geodirectory') ?>
						<?php $value = ( isset( $field->sort_desc ) && $field->sort_desc ) ? $field->sort_desc : 0; ?>
						<input type="hidden" name="desc" value="0" />
						<input type="checkbox" name="desc" value="1" <?php checked( $value, 1, true );?> onclick="gd_show_hide_radio(this,'show','cfs-desc-title');" />
					</label>
				</p>

				<p class="dd-setting-name cfs-desc-title gdat-desc-sort">
					<label for="gd-desc_title-<?php echo $field->id;?>">
						<?php
						echo geodir_help_tip( __( 'This is the text used for the sort option.', 'geodirectory' ));
						_e('Descending title','geodirectory') ?>
						<?php $value = ( isset( $field->desc_title ) && $field->desc_title ) ? esc_attr( $field->desc_title ) : ''; ?>
						<input type="text" name="desc_title" id="desc_title" value="<?php echo $value; ?>"/>
					</label>
				</p>

				<p class="dd-setting-name cfs-desc-title gdat-desc-sort">
					<label for="gd-is_default-<?php echo $field->id;?>">
						<?php
						echo geodir_help_tip( __( 'This sets the option as the overall default sort value, there can be only one.', 'geodirectory' ));
						_e('Default sort?','geodirectory') ?>
						<input type="radio" name="is_default"
						       value="<?php echo $field->htmlvar_name; ?>_desc" <?php if ( isset( $field->default_order ) && $field->default_order == $field->htmlvar_name . '_desc' ) {
							echo 'checked="checked"';
						} ?>/>
					</label>
				</p>

			<?php } else {?>

				<p class="dd-setting-name gd-advanced-setting">
					<label for="gd-admin-title-<?php echo $field->id;?>">
						<?php
						echo geodir_help_tip( __( 'This is the text used for the sort option.', 'geodirectory' ));
						_e('Frontend title','geodirectory') ?>
						<?php $value = esc_attr( $frontend_title ) ?>
						<input type="text" name="frontend_title" id="frontend_title" value="<?php echo $value; ?>"/>
					</label>
				</p>

				<p class="dd-setting-name cfs-desc-title gdat-desc-sort">
					<label for="gd-is_default-<?php echo $field->id;?>">
						<?php
						echo geodir_help_tip( __( 'This sets the option as the overall default sort value, there can be only one.', 'geodirectory' ));
						_e('Default sort?','geodirectory') ?>
						<input type="radio" name="is_default"
						       value="<?php echo $field->htmlvar_name; ?>_desc" <?php if ( isset( $field->default_order ) && $field->default_order == $field->htmlvar_name . '_desc' ) {
							echo 'checked="checked"';
						} ?>/>
					</label>
				</p>


			<?php } ?>

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