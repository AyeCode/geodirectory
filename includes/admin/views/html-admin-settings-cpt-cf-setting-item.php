<?php
/**
 * Admin custom field form
 *
 * @since 1.0.0
 *
 * @package GeoDirectory
 */

/**
 * Displays the custom field form content.
 *
 * @since 1.0.0
 *
 * @global string $post_type Post type.
 */


?>
<li class="text" id="licontainer_<?php echo $field->id; ?>">
	<div class="title title<?php echo $field->id; ?> gd-fieldset"
	     title="<?php _e( 'Double Click to toggle and drag-drop to sort', 'geodirectory' ); ?>"
	     ondblclick="show_hide('field_frm<?php echo $field->id; ?>')">
		<?php
		if ( $field->field_type == 'fieldset' ) {
			?>
			<i class="fa fa-long-arrow-left " aria-hidden="true"></i>
			<i class="fa fa-long-arrow-right " aria-hidden="true"></i>
			<b style="cursor:pointer;"
			   onclick="show_hide('field_frm<?php echo $field->id; ?>')"><?php echo geodir_ucwords( __( 'Fieldset:', 'geodirectory' ) . ' ' . $field->admin_title ); ?></b>
			<?php
		} else {
			echo $field_icon;
			?>
			<b style="cursor:pointer;"
			   onclick="show_hide('field_frm<?php echo $field->id; ?>')"><?php echo geodir_ucwords( ' ' . $field->admin_title . ' (' . $field->field_type_name . ')' ); ?></b>
			<?php
		}
		?>



		<?php if ( $field->is_default ): ?>
<!--			<div title="--><?php //_e( 'Default field, should not be removed.', 'geodirectory' ); ?><!--"-->
<!--			     class="handlediv move gd-default-remove"><i class="fa fa-times" aria-hidden="true"></i></div>-->
		<?php else: ?>
			<div title="<?php _e( 'Click to remove field', 'geodirectory' ); ?>"
			     onclick="gd_delete_custom_field('<?php echo $field->id; ?>', '<?php echo $nonce; ?>')"
			     class="handlediv close">
				<i class="fa fa-trash" aria-hidden="true"></i>
			</div>
		<?php endif;
		?>


		<?php /* @todo test on mobile to see if we need these buttons

		<div title="<?php _e( 'Click to remove field', 'geodirectory' ); ?>"
		     onclick="gd_delete_custom_fieldx('<?php echo $field->id; ?>', '<?php echo $nonce; ?>')"
		     class="handlediv close">
			<i class="fa fa-arrows" aria-hidden="true"></i>
		</div>

		<div title="<?php _e( 'Click to remove field', 'geodirectory' ); ?>"
		     onclick="gd_delete_custom_fieldx('<?php echo $field->id; ?>', '<?php echo $nonce; ?>')"
		     class="handlediv close">
			<i class="fa fa-pencil-square" aria-hidden="true"></i>
		</div>
		 */ ?>

	</div>

	<form><!-- we need to wrap in a fom so we can use radio buttons with same name -->
		<div id="field_frm<?php echo $field->id; ?>" class="field_frm" style="display:none;">
			<input type="hidden" name="security" value="<?php echo sanitize_text_field( $nonce ); ?>"/>
			<input type="hidden" name="post_type" id="post_type"
			       value="<?php echo sanitize_text_field( self::$post_type ); ?>"/>
			<input type="hidden" name="field_type" id="field_type"
			       value="<?php echo sanitize_text_field( $field->field_type ); ?>"/>
			<input type="hidden" name="field_type_key" id="field_type_key"
			       value="<?php echo sanitize_text_field( $field->field_type_key ); ?>"/>
			<input type="hidden" name="field_id" id="field_id"
			       value="<?php echo sanitize_text_field( $field->id ); ?>"/>
			<input type="hidden" name="clabels" id="clabels" value="<?php if ( isset( $field->clabels ) ) {
				echo sanitize_text_field( $field->clabels );
			} ?>"/>
			<input type="hidden" readonly="readonly" name="sort_order" id="sort_order"
			       value="<?php if ( isset( $field->sort_order ) ) {
				       echo absint( $field->sort_order );
			       } ?>"/>
			<input type="hidden" name="is_default" id="is_default"
			       value="<?php echo sanitize_text_field( $field->is_default ); ?>"/>

			<ul class="widefat post fixed" border="0" style="width:100%;">

				<?php

				// data_type
				if ( has_filter( "geodir_cfa_data_type_{$field->field_type}" ) ) {

					echo apply_filters( "geodir_cfa_data_type_{$field->field_type}", '', $field->id, $cf, $field );

				} else {
					$value = '';
					if ( isset( $field->data_type ) ) {
						$value = esc_attr( $field->data_type );
					} elseif ( isset( $cf['defaults']['data_type'] ) && $cf['defaults']['data_type'] ) {
						$value = $cf['defaults']['data_type'];
					}
					?>
					<input type="hidden" name="data_type" id="data_type" value="<?php echo $value; ?>"/>
					<?php
				}

				/**
				 * Action before the custom field setting: geodir_cfa_before_FIELD_TYPE
				 *
				 * @param object $field The field object settings.
				 * @param array $cf The customs field default settings.
				 */
				do_action( "geodir_cfa_before_admin_title_{$field->field_type}", $cf, $field);

				// admin_title
				if ( has_filter( "geodir_cfa_admin_title_{$field->field_type}" ) ) {

					echo apply_filters( "geodir_cfa_admin_title_{$field->field_type}", '', $field->id, $cf, $field );

				} else {
					$value = '';
					if ( isset( $field->admin_title ) ) {
						$value = esc_attr( $field->admin_title );
					} elseif ( isset( $cf['defaults']['admin_title'] ) && $cf['defaults']['admin_title'] ) {
						$value = $cf['defaults']['admin_title'];
					}
					?>
					<li class="gd-advanced-setting">
						<label for="admin_title" class="gd-cf-tooltip-wrap">
							<span
								class="gd-help-tip gd-help-tip-float-none gd-help-tip-no-margin dashicons dashicons-editor-help"
								title="<?php _e( 'This is used as the field setting name here in the backend only.', 'geodirectory' ); ?>">
							</span>
							<?php _e( 'Admin name :', 'geodirectory' ); ?>
						</label>
						<div class="gd-cf-input-wrap">
							<input type="text" name="admin_title" id="admin_title"
							       value="<?php echo $value; ?>"/>
						</div>
					</li>
					<?php
				}

				do_action( "geodir_cfa_before_frontend_title_{$field->field_type}", $cf, $field);

				// frontend_title
				if ( has_filter( "geodir_cfa_frontend_title_{$field->field_type}" ) ) {

					echo apply_filters( "geodir_cfa_frontend_title_{$field->field_type}", '', $field->id, $cf, $field );

				} else {
					$value = '';
					if ( isset( $field->frontend_title ) ) {
						$value = esc_attr( $field->frontend_title );
					} elseif ( isset( $cf['defaults']['frontend_title'] ) && $cf['defaults']['frontend_title'] ) {
						$value = $cf['defaults']['frontend_title'];
					}
					?>
					<li>
						<label for="frontend_title" class="gd-cf-tooltip-wrap">
							<span
								class="gd-help-tip gd-help-tip-float-none gd-help-tip-no-margin dashicons dashicons-editor-help"
								title="<?php _e( 'This will be the label for the field input on the frontend.', 'geodirectory' ); ?>">
							</span>
							<?php _e( 'Field label :', 'geodirectory' ); ?>
						</label>
						<div class="gd-cf-input-wrap">
							<input type="text" name="frontend_title" id="frontend_title"
							       value="<?php echo $value; ?>"/>
						</div>
					</li>
					<?php
				}


				// frontend_desc
				do_action( "geodir_cfa_before_frontend_desc_{$field->field_type}", $cf, $field);

				if ( has_filter( "geodir_cfa_frontend_desc_{$field->field_type}" ) ) {

					echo apply_filters( "geodir_cfa_frontend_desc_{$field->field_type}", '', $field->id, $cf, $field );

				} else {
					$value = '';
					if ( isset( $field->frontend_desc ) ) {
						$value = esc_attr( $field->frontend_desc );
					} elseif ( isset( $cf['defaults']['frontend_desc'] ) && $cf['defaults']['frontend_desc'] ) {
						$value = $cf['defaults']['frontend_desc'];
					}
					?>
					<li>
						<label for="frontend_desc" class="gd-cf-tooltip-wrap">
							<span
								class="gd-help-tip gd-help-tip-float-none gd-help-tip-no-margin dashicons dashicons-editor-help"
								title="<?php _e( 'This will be shown below the field on the add listing form.', 'geodirectory' ); ?>">
							</span>
							<?php _e( 'Field description :', 'geodirectory' ); ?>
						</label>
						<div class="gd-cf-input-wrap">
							<input type="text" name="frontend_desc" id="frontend_desc" value="<?php echo $value; ?>"/>
						</div>
					</li>
					<?php
				}

				// htmlvar_name
				do_action( "geodir_cfa_before_htmlvar_name_{$field->field_type}", $cf, $field);


				if ( has_filter( "geodir_cfa_htmlvar_name_{$field->field_type}" ) ) {

					echo apply_filters( "geodir_cfa_htmlvar_name_{$field->field_type}", '', $field->id, $cf, $field );

				} else {
					$value = '';
					if ( isset( $field->htmlvar_name ) ) {
						$value = esc_attr( $field->htmlvar_name );
					} elseif ( isset( $cf['defaults']['htmlvar_name'] ) && $cf['defaults']['htmlvar_name'] ) {
						$value = $cf['defaults']['htmlvar_name'];
					}
					?>
					<li class="gd-advanced-setting">
						<label for="htmlvar_name" class="gd-cf-tooltip-wrap">
							<span
								class="gd-help-tip gd-help-tip-float-none gd-help-tip-no-margin dashicons dashicons-editor-help"
								title="<?php _e( 'This is a unique identifier used in the database and HTML, it MUST NOT contain spaces or special characters.', 'geodirectory' ); ?>">
							</span>
							<?php _e( 'Field key :', 'geodirectory' ); ?>
						</label>
						<div class="gd-cf-input-wrap">
							<input type="text" name="htmlvar_name" id="htmlvar_name" pattern="[a-zA-Z0-9]+"
							       title="<?php _e( 'Must not contain spaces or special characters', 'geodirectory' ); ?>"
							       value="<?php if ( $value ) {
								       echo preg_replace( '/geodir_/', '', $value, 1 );
							       } ?>" <?php if ( $field->is_default || $value == 'featured' || $value == 'new' ) {
								echo 'readonly="readonly"';
							} ?> />
						</div>
					</li>
					<?php
				}


				// is_active
				do_action( "geodir_cfa_before_is_active_{$field->field_type}", $cf, $field);

				if ( has_filter( "geodir_cfa_is_active_{$field->field_type}" ) ) {

					echo apply_filters( "geodir_cfa_is_active_{$field->field_type}", '', $field->id, $cf, $field );

				} else {
					$value = '';
					if ( isset( $field->is_active ) ) {
						$value = esc_attr( $field->is_active );
					} elseif ( isset( $cf['defaults']['is_active'] ) && $cf['defaults']['is_active'] ) {
						$value = $cf['defaults']['is_active'];
					}
					?>
					<li <?php echo $field_display; ?>>
						<label for="is_active" class="gd-cf-tooltip-wrap">
							<span
								class="gd-help-tip gd-help-tip-float-none gd-help-tip-no-margin dashicons dashicons-editor-help"
								title="<?php _e( 'If no is selected then the field will not be displayed anywhere.', 'geodirectory' ); ?>">
							</span>
							<?php _e( 'Is active :', 'geodirectory' ); ?>
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
					<?php
				}


				// for_admin_use only
				do_action( "geodir_cfa_before_for_admin_use_{$field->field_type}", $cf, $field);

				if ( has_filter( "geodir_cfa_for_admin_use_{$field->field_type}" ) ) {

					echo apply_filters( "geodir_cfa_for_admin_use_{$field->field_type}", '', $field->id, $cf, $field );

				} else {
					$value = '';
					if ( isset( $field->for_admin_use ) ) {
						$value = esc_attr( $field->for_admin_use );
					} elseif ( isset( $cf['defaults']['for_admin_use'] ) && $cf['defaults']['for_admin_use'] ) {
						$value = $cf['defaults']['for_admin_use'];
					}
					?>
					<li class="gd-advanced-setting">
						<label for="for_admin_use" class="gd-cf-tooltip-wrap">
							<span
								class="gd-help-tip gd-help-tip-float-none gd-help-tip-no-margin dashicons dashicons-editor-help"
								title="<?php _e( 'If yes is selected then only site admin can see and edit this field.', 'geodirectory' ); ?>">
							</span>
							<?php _e( 'For admin use only? :', 'geodirectory' ); ?>
						</label>
						<div class="gd-cf-input-wrap gd-switch">

							<input type="radio" id="for_admin_use_yes<?php echo $radio_id; ?>" name="for_admin_use"
							       class="gdri-enabled" value="1"
								<?php if ( $value == '1' ) {
									echo 'checked';
								} ?>/>
							<label for="for_admin_use_yes<?php echo $radio_id; ?>"
							       class="gdcb-enable"><span><?php _e( 'Yes', 'geodirectory' ); ?></span></label>

							<input type="radio" id="for_admin_use_no<?php echo $radio_id; ?>" name="for_admin_use"
							       class="gdri-disabled" value="0"
								<?php if ( $value == '0' || ! $value ) {
									echo 'checked';
								} ?>/>
							<label for="for_admin_use_no<?php echo $radio_id; ?>"
							       class="gdcb-disable"><span><?php _e( 'No', 'geodirectory' ); ?></span></label>

						</div>
					</li>
					<?php
				}


				// default_value
				do_action( "geodir_cfa_before_default_value_{$field->field_type}", $cf, $field);

				if ( has_filter( "geodir_cfa_default_value_{$field->field_type}" ) ) {

					echo apply_filters( "geodir_cfa_default_value_{$field->field_type}", '', $field->id, $cf, $field );

				} else {
					$value = '';
					if ( isset( $field->default_value ) ) {
						$value = esc_attr( $field->default_value );
					} elseif ( isset( $cf['defaults']['default_value'] ) && $cf['defaults']['default_value'] ) {
						$value = $cf['defaults']['default_value'];
					}
					?>
					<li class="gd-advanced-setting">
						<label for="default_value" class="gd-cf-tooltip-wrap">
							<span
								class="gd-help-tip gd-help-tip-float-none gd-help-tip-no-margin dashicons dashicons-editor-help"
								title="<?php
								if ( $field->field_type == 'checkbox' ) {
									_e( 'Should the checkbox be checked by default?', 'geodirectory' );
								} else if ( $field->field_type == 'email' ) {
									_e( 'A default value for the field, usually blank. Ex: info@mysite.com', 'geodirectory' );
								} else {
									_e( 'A default value for the field, usually blank. (for "link" this will be used as the link text)', 'geodirectory' );
								}
								?>">
							</span>
							<?php _e( 'Default value :', 'geodirectory' ); ?>
						</label>
						<div class="gd-cf-input-wrap">
							<?php if ( $field->field_type == 'checkbox' ) { ?>
								<select name="default_value" id="default_value">
									<option value=""><?php _e( 'Unchecked', 'geodirectory' ); ?></option>
									<option
										value="1" <?php selected( true, (int) $value === 1 ); ?>><?php _e( 'Checked', 'geodirectory' ); ?></option>
								</select>
							<?php } else if ( $field->field_type == 'email' ) { ?>
								<input type="email" name="default_value"
								       placeholder="<?php _e( 'info@mysite.com', 'geodirectory' ); ?>"
								       id="default_value" value="<?php echo esc_attr( $value ); ?>"/><br/>
							<?php } else { ?>
								<input type="text" name="default_value" id="default_value"
								       value="<?php echo esc_attr( $value ); ?>"/><br/>
							<?php } ?>
						</div>
					</li>
					<?php
				}


				// show_in
				do_action( "geodir_cfa_before_show_in_{$field->field_type}", $cf, $field);

				if ( has_filter( "geodir_cfa_show_in_{$field->field_type}" ) ) {

					echo apply_filters( "geodir_cfa_show_in_{$field->field_type}", '', $field->id, $cf, $field );

				} else {
					$value = '';
					if ( isset( $field->show_in ) ) {
						$value = esc_attr( $field->show_in );
					} elseif ( isset( $cf['defaults']['show_in'] ) && $cf['defaults']['show_in'] ) {
						$value = esc_attr( $cf['defaults']['show_in'] );
					}
					?>
					<li>
						<label for="show_in" class="gd-cf-tooltip-wrap">
							<span
								class="gd-help-tip gd-help-tip-float-none gd-help-tip-no-margin dashicons dashicons-editor-help"
								title="<?php _e( 'Select in what locations you want to display this field.', 'geodirectory' ); ?>">
							</span>
							<?php _e( 'Show in what locations?:', 'geodirectory' ); ?>
						</label>
						<div class="gd-cf-input-wrap">

							<?php
							
							$show_in_locations = GeoDir_Settings_Cpt_Cf::show_in_locations($field, $field->field_type );

							// remove some locations for some field types

							// don't show new tab option for some types
							if ( in_array( $field->field_type, array(
								'text',
								'datepicker',
								'textarea',
								'time',
								'phone',
								'email',
								'select',
								'multiselect',
								'url',
								'html',
								'fieldset',
								'radio',
								'checkbox',
								'file',
								'address',
								'taxonomy',
								'business_hours'
							) ) ) {
							} else {
								unset( $show_in_locations['[owntab]'] );
							}

							if ( ! $display_on_listing ) {
								unset( $show_in_locations['[listings]'] );
							}

							?>

							<select multiple="multiple" name="show_in[]"
							        id="show_in"
							        style="min-width:300px;"
							        class="geodir-select"
							        data-placeholder="<?php _e( 'Select locations', 'geodirectory' ); ?>"
							        option-ajaxchosen="false">
								<?php

								$show_in_values = explode( ',', $value );

								foreach ( $show_in_locations as $key => $val ) {
									$selected = '';

									if ( is_array( $show_in_values ) && in_array( $key, $show_in_values ) ) {
										$selected = 'selected';
									}

									?>
									<option
										value="<?php echo $key; ?>" <?php echo $selected; ?>><?php echo $val; ?></option>
									<?php
								}
								?>
							</select>
						</div>
					</li>
					<?php
				}


				// advanced_editor @todo this should be added via action
				if ( has_filter( "geodir_cfa_advanced_editor_{$field->field_type}" ) ) {

					echo apply_filters( "geodir_cfa_advanced_editor_{$field->field_type}", '', $field->id, $cf, $field );

				}


				?>



				<?php

				// @todo this should be added via action

				$pricearr = array();
				if ( isset( $field->packages ) && $field->packages != '' ) {
					$pricearr = explode( ',', trim( $field->packages, ',' ) );
				} else {
					$package_info = array();

					$package_info = geodir_post_package_info( $package_info, '', self::$post_type );
					$pricearr[]   = $package_info->pid;
				}

				ob_start()
				?>

				<select style="display:none" name="show_on_pkg[]" id="show_on_pkg" multiple="multiple">
					<?php
					if ( ! empty( $pricearr ) ) {
						foreach ( $pricearr as $val ) {
							?>
							<option selected="selected"
							        value="<?php echo esc_attr( $val ); ?>" ><?php echo $val; ?></option><?php
						}
					}
					?>
				</select>

				<?php
				$html = ob_get_clean();

				/**
				 * Filter the price packages list.
				 *
				 * Filter the price packages list in custom field form in admin
				 * custom fields settings.
				 *
				 * @since 1.0.0
				 *
				 * @param string $html The price packages content.
				 * @param object $field Current field object.
				 */
				echo $html = apply_filters( 'geodir_packages_list_on_custom_fields', $html, $field );

				?>



				<?php

				// is_required
				do_action( "geodir_cfa_before_is_required_{$field->field_type}", $cf, $field);

				if ( has_filter( "geodir_cfa_is_required_{$field->field_type}" ) ) {

					echo apply_filters( "geodir_cfa_is_required_{$field->field_type}", '', $field->id, $cf, $field );

				} else {
					$value = '';
					if ( isset( $field->is_required ) ) {
						$value = esc_attr( $field->is_required );
					} elseif ( isset( $cf['defaults']['is_required'] ) && $cf['defaults']['is_required'] ) {
						$value = $cf['defaults']['is_required'];
					}
					?>
					<li class="gd-advanced-setting">
						<label for="is_required" class="gd-cf-tooltip-wrap">
							<span
								class="gd-help-tip gd-help-tip-float-none gd-help-tip-no-margin dashicons dashicons-editor-help"
								title="<?php _e( 'Select yes to set field as required.', 'geodirectory' ); ?>">
							</span>
							<?php _e( 'Is required :', 'geodirectory' ); ?>
						</label>

						<div class="gd-cf-input-wrap gd-switch">

							<input type="radio" id="is_required_yes<?php echo $radio_id; ?>" name="is_required"
							       class="gdri-enabled" value="1"
								<?php if ( $value == '1' ) {
									echo 'checked';
								} ?>/>
							<label onclick="show_hide_radio(this,'show','cf-is-required-msg');"
							       for="is_required_yes<?php echo $radio_id; ?>"
							       class="gdcb-enable"><span><?php _e( 'Yes', 'geodirectory' ); ?></span></label>

							<input type="radio" id="is_required_no<?php echo $radio_id; ?>" name="is_required"
							       class="gdri-disabled" value="0"
								<?php if ( $value == '0' || ! $value ) {
									echo 'checked';
								} ?>/>
							<label onclick="show_hide_radio(this,'hide','cf-is-required-msg');"
							       for="is_required_no<?php echo $radio_id; ?>"
							       class="gdcb-disable"><span><?php _e( 'No', 'geodirectory' ); ?></span></label>

						</div>

					</li>

					<?php
				}

				// required_msg
				do_action( "geodir_cfa_before_required_msg_{$field->field_type}", $cf, $field);

				if ( has_filter( "geodir_cfa_required_msg_{$field->field_type}" ) ) {

					echo apply_filters( "geodir_cfa_required_msg_{$field->field_type}", '', $field->id, $cf, $field );

				} else {
					$value = '';
					if ( isset( $field->required_msg ) ) {
						$value = esc_attr( $field->required_msg );
					} elseif ( isset( $cf['defaults']['required_msg'] ) && $cf['defaults']['required_msg'] ) {
						$value = $cf['defaults']['required_msg'];
					}
					?>
					<li  class="cf-is-required-msg gd-advanced-setting" <?php if ( ( isset( $field->is_required ) && $field->is_required == '0' ) || ! isset( $field->is_required ) ) {
						echo "style='display:none;'";
					} ?>>
						<label for="required_msg" class="gd-cf-tooltip-wrap">
							<span
								class="gd-help-tip gd-help-tip-float-none gd-help-tip-no-margin dashicons dashicons-editor-help"
								title="<?php _e( 'Enter text for the error message if the field is required and has not fulfilled the requirements.', 'geodirectory' ); ?>">
							</span>
							<?php _e( 'Required message:', 'geodirectory' ); ?>
						</label>
						<div class="gd-cf-input-wrap">
							<input type="text" name="required_msg" id="required_msg"
							       value="<?php echo esc_attr( $value ); ?>"/>
						</div>
					</li>
					<?php
				}


				// required_msg
				if ( has_filter( "geodir_cfa_validation_pattern_{$field->field_type}" ) ) {

					echo apply_filters( "geodir_cfa_validation_pattern_{$field->field_type}", '', $field->id, $cf, $field );

				}



				// field_icon
				do_action( "geodir_cfa_before_field_icon{$field->field_type}", $cf, $field);

				if ( has_filter( "geodir_cfa_field_icon_{$field->field_type}" ) ) {

					echo apply_filters( "geodir_cfa_field_icon_{$field->field_type}", '', $field->id, $cf, $field );

				} else {
					$value = '';
					if ( isset( $field->field_icon ) ) {
						$value = esc_attr( $field->field_icon );
					} elseif ( isset( $cf['defaults']['field_icon'] ) && $cf['defaults']['field_icon'] ) {
						$value = $cf['defaults']['field_icon'];
					}
					?>
					<li class="gd-advanced-setting">
						<h3><?php echo __( 'Custom css', 'geodirectory' ); ?></h3>


						<label for="field_icon" class="gd-cf-tooltip-wrap">
							<span
								class="gd-help-tip gd-help-tip-float-none gd-help-tip-no-margin dashicons dashicons-editor-help"
								title='<?php _e( 'Upload icon using media and enter its url path, or enter font awesome class eg:"fa fa-home"', 'geodirectory' ); ?>'>
							</span>
							<?php _e( 'Upload icon :', 'geodirectory' ); ?>
						</label>
						<div class="gd-cf-input-wrap">
							<input type="text" name="field_icon" id="field_icon"
							       value="<?php echo $value; ?>"/>
						</div>

					</li>
					<?php
				}


				// css_class
				do_action( "geodir_cfa_before_css_class_{$field->field_type}", $cf, $field);

				if ( has_filter( "geodir_cfa_css_class_{$field->field_type}" ) ) {

					echo apply_filters( "geodir_cfa_css_class_{$field->field_type}", '', $field->id, $cf, $field );

				} else {
					$value = '';
					if ( isset( $field->css_class ) ) {
						$value = esc_attr( $field->css_class );
					} elseif ( isset( $cf['defaults']['css_class'] ) && $cf['defaults']['css_class'] ) {
						$value = $cf['defaults']['css_class'];
					}
					?>
					<li class="gd-advanced-setting">
						<label for="css_class" class="gd-cf-tooltip-wrap">
							<span
								class="gd-help-tip gd-help-tip-float-none gd-help-tip-no-margin dashicons dashicons-editor-help"
								title="<?php _e( 'Enter custom css class for field custom style.', 'geodirectory' ); ?>
								<?php if ( $field->field_type == 'multiselect' ) {
									_e( '(Enter class `gd-comma-list` to show list as comma separated)', 'geodirectory' );
								} ?>">
							</span>
							<?php _e( 'Css class :', 'geodirectory' ); ?>
						</label>
						<div class="gd-cf-input-wrap">
							<input type="text" name="css_class" id="css_class"
							       value="<?php if ( isset( $field->css_class ) ) {
								       echo esc_attr( $field->css_class );
							       } ?>"/>
						</div>
					</li>
					<?php
				}


				// cat_sort
				do_action( "geodir_cfa_before_css_sort_{$field->field_type}", $cf, $field);

				if ( has_filter( "geodir_cfa_cat_sort_{$field->field_type}" ) ) {

					echo apply_filters( "geodir_cfa_cat_sort_{$field->field_type}", '', $field->id, $cf, $field );

				} else {
					$value         = '';
					$hide_cat_sort = '';
					if ( isset( $field->cat_sort ) ) {
						$value = esc_attr( $field->cat_sort );
					} elseif ( isset( $cf['defaults']['cat_sort'] ) && $cf['defaults']['cat_sort'] ) {
						$value         = $cf['defaults']['cat_sort'];
						$hide_cat_sort = ( $value === false ) ? "style='display:none;'" : '';
					}

					$hide_cat_sort = ( isset( $cf['defaults']['cat_sort'] ) && $cf['defaults']['cat_sort'] === false ) ? "style='display:none;'" : '';
					?>
					<li class="gd-advanced-setting" <?php echo $hide_cat_sort; ?>>
						<h3><?php
							/**
							 * Filter the section title.
							 *
							 * Filter the section title in custom field form in admin
							 * custom fields settings.
							 *
							 * @since 1.0.0
							 *
							 * @param string $title Title of the section.
							 * @param string $field ->field_type Current field type.
							 */
							echo apply_filters( 'geodir_advance_custom_fields_heading', __( 'Posts sort options', 'geodirectory' ), $field->field_type );

							?></h3>
						<label for="cat_sort" class="gd-cf-tooltip-wrap">
							<span
								class="gd-help-tip gd-help-tip-float-none gd-help-tip-no-margin dashicons dashicons-editor-help"
								title="<?php _e( 'Lets you use this filed as a sorting option, set from sorting options above.', 'geodirectory' ); ?>">
							</span>
							<?php _e( 'Include this field in sorting options :', 'geodirectory' ); ?>
						</label>

						<div class="gd-cf-input-wrap gd-switch">

							<input type="radio" id="cat_sort_yes<?php echo $radio_id; ?>" name="cat_sort"
							       class="gdri-enabled" value="1"
								<?php if ( $value == '1' ) {
									echo 'checked';
								} ?>/>
							<label for="cat_sort_yes<?php echo $radio_id; ?>"
							       class="gdcb-enable"><span><?php _e( 'Yes', 'geodirectory' ); ?></span></label>

							<input type="radio" id="cat_sort_no<?php echo $radio_id; ?>" name="cat_sort"
							       class="gdri-disabled" value="0"
								<?php if ( ! $value ) {
									echo 'checked';
								} ?>/>
							<label for="cat_sort_no<?php echo $radio_id; ?>"
							       class="gdcb-disable"><span><?php _e( 'No', 'geodirectory' ); ?></span></label>

						</div>
					</li>
					<?php
				}

				// extra_fields
				if ( has_filter( "geodir_cfa_extra_fields_{$field->field_type}" ) ) {

					echo apply_filters( "geodir_cfa_extra_fields_{$field->field_type}", '', $field->id, $cf, $field );

				}


				switch ( $field->field_type ):
					case 'html':
					case 'file':
					case 'url':
					case 'fieldset':
						break;
					default:

						/**
						 * Called at the end of the advanced custom fields settings page loop.
						 *
						 * Can be used to add or deal with different settings types.
						 *
						 * @since 1.0.0
						 * @since 1.6.6 $cf param added.
						 *
						 * @param object $field The current fields info.
						 * @param array $cf The custom field settings
						 */
						do_action( 'geodir_advance_custom_fields', $field, $cf ); ?>


					<?php endswitch;


				// action before save button
				do_action( "geodir_cfa_before_save", self::$post_type, $field, $cf );
				do_action( "geodir_cfa_before_save_{$field->field_type}", $cf, $field);
				?>


				<li>

					<label for="save" class="gd-cf-tooltip-wrap">
						<h3></h3>
					</label>
					<div class="gd-cf-input-wrap">
						<input type="button" class="button button-primary" name="save" id="save"
						       value="<?php echo esc_attr( __( 'Save', 'geodirectory' ) ); ?>"
						       onclick="gd_save_custom_field('<?php echo esc_attr( $field->id ); ?>')"/>
						<?php if ( ! $field->is_default ): ?>
							<a href="javascript:void(0)"><input type="button" name="delete"
							                                    value="<?php echo esc_attr( __( 'Delete', 'geodirectory' ) ); ?>"
							                                    onclick="gd_delete_custom_field('<?php echo esc_attr( $field->id ); ?>', '<?php echo $nonce; ?>')"
							                                    class="button"/></a>
						<?php endif; ?>
					</div>
				</li>
			</ul>
		</div>
	</form>
</li>