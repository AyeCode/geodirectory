<?php
/**
 * Admin custom field sorting form
 *
 * @since 2.0.0
 *
 * @package GeoDirectory
 */


?>

<li class="text" id="licontainer_<?php echo $field->id; ?>">
	<form><!-- we need to wrap in a form so we can use radio buttons with same name -->
		<div class="title title<?php echo $field->id; ?> gd-fieldset"
		     title="<?php _e( 'Double Click to toggle and drag-drop to sort', 'geodirectory' ); ?>"
		     ondblclick="show_hide('field_frm<?php echo $field->id; ?>')">
			<?php

			?>

			<div title="<?php _e( 'Click to remove field', 'geodirectory' ); ?>"
			     onclick="gd_delete_sort_field('<?php echo $field->id; ?>', '<?php echo $nonce; ?>', this)"
			     class="handlediv close"><i class="fa fa-times" aria-hidden="true"></i></div>


			<?php echo $field_icon; ?>
			<b style="cursor:pointer;"
			   onclick="show_hide('field_frm<?php echo $field->id; ?>')"><?php echo geodir_ucwords( __( 'Field:', 'geodirectory' ) . ' (' . $frontend_title . ')' ); ?></b>

		</div>

		<div id="field_frm<?php echo $field->id; ?>" class="field_frm" style="display:none;">
			<input type="hidden" name="_wpnonce" value="<?php echo $nonce; ?>"/>
			<input type="hidden" name="post_type" id="post_type" value="<?php echo self::$post_type; ?>"/>
			<input type="hidden" name="field_type" id="field_type" value="<?php echo $field->field_type; ?>"/>
			<input type="hidden" name="field_id" id="field_id" value="<?php echo $field->id; ?>"/>
			<input type="hidden" name="data_type" id="data_type" value="<?php if ( isset( $field->data_type ) ) {
				echo $field->data_type;
			} ?>"/>
			<input type="hidden" name="htmlvar_name" id="htmlvar_name" value="<?php echo $field->htmlvar_name; ?>"/>


			<ul class="widefat post fixed" border="0" style="width:100%;">

				<?php if ( $field->field_type != 'random' ) { ?>

					<input type="hidden" name="frontend_title" id="frontend_title"
					       value="<?php echo esc_attr( $frontend_title ); ?>"/>

					<li data-gdat-display-switch-set="gdat-asc-sort">
						<?php $value = ( isset( $field->sort_asc ) && $field->sort_asc ) ? $field->sort_asc : 0; ?>

						<label for="asc" class="gd-cf-tooltip-wrap">
							<span
								class="gd-help-tip gd-help-tip-float-none gd-help-tip-no-margin dashicons dashicons-editor-help"
								title="<?php _e( 'Select if you want to show this option in the sort options. (A-Z,0-100 or OFF)', 'geodirectory' ); ?>">
							</span>
							<?php _e( 'Show Ascending Sort (low to high)', 'geodirectory' ); ?>
						</label>
						<div class="gd-cf-input-wrap gd-switch">

							<input type="radio" id="asc_yes<?php echo $radio_id; ?>" name="asc" class="gdri-enabled"
							       value="1"
								<?php if ( $value == '1' ) {
									echo 'checked';
								} ?>/>
							<label onclick="show_hide_radio(this,'show','cfs-asc-title');"
							       for="asc_yes<?php echo $radio_id; ?>"
							       class="gdcb-enable"><span><?php _e( 'Yes', 'geodirectory' ); ?></span></label>

							<input type="radio" id="asc_no<?php echo $radio_id; ?>" name="asc" class="gdri-disabled"
							       value="0"
								<?php if ( $value == '0' || ! $value ) {
									echo 'checked';
								} ?>/>
							<label onclick="show_hide_radio(this,'hide','cfs-asc-title');"
							       for="asc_no<?php echo $radio_id; ?>"
							       class="gdcb-disable"><span><?php _e( 'No', 'geodirectory' ); ?></span></label>

						</div>

					</li>

					<li class="cfs-asc-title gdat-asc-sort" >
						<?php $value = ( isset( $field->asc_title ) && $field->asc_title ) ? esc_attr( $field->asc_title ) : ''; ?>

						<label for="asc_title" class="gd-cf-tooltip-wrap">
							<span
								class="gd-help-tip gd-help-tip-float-none gd-help-tip-no-margin dashicons dashicons-editor-help"
								title="<?php _e( 'This is the text used for the sort option.', 'geodirectory' ); ?>">
							</span>
							<?php _e( 'Ascending title', 'geodirectory' ); ?>
						</label>
						<div class="gd-cf-input-wrap">

							<input type="text" name="asc_title" id="asc_title" value="<?php echo $value; ?>"/>
						</div>


					</li>


					<li class="cfs-asc-title gdat-asc-sort" >

						<label for="is_default" class="gd-cf-tooltip-wrap">
							<span
								class="gd-help-tip gd-help-tip-float-none gd-help-tip-no-margin dashicons dashicons-editor-help"
								title="<?php _e( 'This sets the option as the overall default sort value, there can be only one.', 'geodirectory' ); ?>">
							</span>
							<?php _e( 'Default sort?', 'geodirectory' ); ?>
						</label>
						<div class="gd-cf-input-wrap">

							<input type="radio" name="is_default"
							       value="<?php echo $field->htmlvar_name; ?>_asc" <?php if ( isset( $field->default_order ) && $field->default_order == $field->htmlvar_name . '_asc' ) {
								echo 'checked="checked"';
							} ?>/>
						</div>

					</li>


					<li data-gdat-display-switch-set="gdat-desc-sort">
						<?php $value = ( isset( $field->sort_desc ) && $field->sort_desc ) ? $field->sort_desc : 0; ?>

						<label for="desc" class="gd-cf-tooltip-wrap">
							<span
								class="gd-help-tip gd-help-tip-float-none gd-help-tip-no-margin dashicons dashicons-editor-help"
								title="<?php _e( 'Select if you want to show this option in the sort options. (Z-A,100-0 or ON)', 'geodirectory' ); ?>">
							</span>
							<?php _e( 'Show Descending Sort (high to low)', 'geodirectory' ); ?>
						</label>
						<div class="gd-cf-input-wrap gd-switch">

							<input type="radio" id="desc_yes<?php echo $radio_id; ?>" name="desc" class="gdri-enabled"
							       value="1"
								<?php if ( $value == '1' ) {
									echo 'checked';
								} ?>/>
							<label onclick="show_hide_radio(this,'show','cfs-desc-title');"
							       for="desc_yes<?php echo $radio_id; ?>"
							       class="gdcb-enable"><span><?php _e( 'Yes', 'geodirectory' ); ?></span></label>

							<input type="radio" id="desc_no<?php echo $radio_id; ?>" name="desc" class="gdri-disabled"
							       value="0"
								<?php if ( $value == '0' || ! $value ) {
									echo 'checked';
								} ?>/>
							<label onclick="show_hide_radio(this,'hide','cfs-desc-title');"
							       for="desc_no<?php echo $radio_id; ?>"
							       class="gdcb-disable"><span><?php _e( 'No', 'geodirectory' ); ?></span></label>

						</div>

					</li>

					<li class="cfs-desc-title gdat-desc-sort">
						<?php $value = ( isset( $field->desc_title ) && $field->desc_title ) ? esc_attr( $field->desc_title ) : ''; ?>

						<label for="desc_title" class="gd-cf-tooltip-wrap">
							<span
								class="gd-help-tip gd-help-tip-float-none gd-help-tip-no-margin dashicons dashicons-editor-help"
								title="<?php _e( 'This is the text used for the sort option.', 'geodirectory' ); ?>">
							</span>
							<?php _e( 'Descending title', 'geodirectory' ); ?>
						</label>
						<div class="gd-cf-input-wrap">

							<input type="text" name="desc_title" id="desc_title" value="<?php echo $value; ?>"/>
						</div>


					</li>

					<li class="cfs-desc-title gdat-desc-sort" >

						<label for="is_default" class="gd-cf-tooltip-wrap">
							<span
								class="gd-help-tip gd-help-tip-float-none gd-help-tip-no-margin dashicons dashicons-editor-help"
								title="<?php _e( 'This sets the option as the overall default sort value, there can be only one.', 'geodirectory' ); ?>">
							</span>
							<?php _e( 'Default sort?', 'geodirectory' ); ?>
						</label>
						<div class="gd-cf-input-wrap">

							<input type="radio" name="is_default"
							       value="<?php echo $field->htmlvar_name; ?>_desc" <?php if ( isset( $field->default_order ) && $field->default_order == $field->htmlvar_name . '_desc' ) {
								echo 'checked="checked"';
							} ?>/>
						</div>

					</li>


				<?php } else { ?>


					<li>
						<?php $value = esc_attr( $frontend_title ) ?>

						<label for="frontend_title" class="gd-cf-tooltip-wrap">
							<span
								class="gd-help-tip gd-help-tip-float-none gd-help-tip-no-margin dashicons dashicons-editor-help"
								title="<?php _e( 'This is the text used for the sort option.', 'geodirectory' ); ?>">
							</span>
							<?php _e( 'Frontend title', 'geodirectory' ); ?>
						</label>
						<div class="gd-cf-input-wrap">

							<input type="text" name="frontend_title" id="frontend_title" value="<?php echo $value; ?>"/>
						</div>


					</li>

					<li>
						<?php $value = ( isset( $field->is_default ) && $field->is_default ) ? esc_attr( $field->is_default ) : ''; ?>

						<label for="is_default" class="gd-cf-tooltip-wrap">
							<span
								class="gd-help-tip gd-help-tip-float-none gd-help-tip-no-margin dashicons dashicons-editor-help"
								title="<?php _e( 'This sets the option as the overall default sort value, there can be only one.', 'geodirectory' ); ?>">
							</span>
							<?php _e( 'Default sort?', 'geodirectory' ); ?>
						</label>
						<div class="gd-cf-input-wrap">

							<input type="checkbox" name="is_default"
							       value="<?php echo $field->field_type; ?>" <?php if ( isset( $value ) && $value == '1' ) {
								echo 'checked="checked"';
							} ?>/>
						</div>


					</li>


				<?php } ?>


				<li>
					<?php $value = ( isset( $field->is_active ) && $field->is_active ) ? $field->is_active : 0; ?>

					<label for="is_active" class="gd-cf-tooltip-wrap">
						<span
							class="gd-help-tip gd-help-tip-float-none gd-help-tip-no-margin dashicons dashicons-editor-help"
							title="<?php _e( 'Set if this sort option is active or not, if not it will not be shown to users.', 'geodirectory' ); ?>">
							</span>
						<?php _e( 'Is active', 'geodirectory' ); ?>
					</label>
					<div class="gd-cf-input-wrap gd-switch">

						<input type="radio" id="is_active_yes<?php echo $radio_id; ?>" name="is_active"
						       class="gdri-enabled" value="1"
							<?php if ( $value == '1' ) {
								echo 'checked';
							} ?>/>
						<label for="is_active_yes<?php echo $radio_id; ?>"
						       class="gdcb-enable"><span><?php _e( 'Yes', 'geodirectory' ); ?></span></label>

						<input type="radio" id="is_active_no<?php echo $radio_id; ?>" name="is_active"
						       class="gdri-disabled" value="0"
							<?php if ( $value == '0' || ! $value ) {
								echo 'checked';
							} ?>/>
						<label for="is_active_no<?php echo $radio_id; ?>"
						       class="gdcb-disable"><span><?php _e( 'No', 'geodirectory' ); ?></span></label>

					</div>

				</li>


				<input type="hidden" readonly="readonly" name="sort_order" id="sort_order"
				       value="<?php if ( isset( $field->sort_order ) ) {
					       echo esc_attr( $field->sort_order );
				       } ?>" size="50"/>


				<li>

					<label for="save" class="gd-cf-tooltip-wrap">
						<h3></h3>
					</label>
					<div class="gd-cf-input-wrap">
						<input type="button" class="button button-primary" name="save" id="save"
						       value="<?php echo esc_attr( __( 'Save', 'geodirectory' ) ); ?>"
						       onclick="gd_save_sort_field('<?php echo esc_attr( $field->id ); ?>')"/>
						<a href="javascript:void(0)"><input type="button" name="delete"
						                                    value="<?php echo esc_attr( __( 'Delete', 'geodirectory' ) ); ?>"
						                                    onclick="gd_delete_sort_field('<?php echo $field->id; ?>', '<?php echo $nonce; ?>', this)"
						                                    class="button"/></a>
					</div>
				</li>
			</ul>

		</div>
	</form>
</li> 
