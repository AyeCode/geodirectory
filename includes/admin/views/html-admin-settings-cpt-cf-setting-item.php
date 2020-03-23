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

//print_r($field);echo '###';
//mjs-nestedSortable-no-nesting
$tab_class = isset($field->field_type) && $field->field_type=='fieldset' ? '' : 'mjs-nestedSortable-no-nesting';
?>
<li class="dd-item <?php echo $tab_class;?>" data-id="1" id="setName_<?php echo $field->id;?>" data-field_type="<?php echo esc_attr( $field->field_type ); ?>" data-field_type_key="<?php echo esc_attr( $field->field_type_key ); ?>">
	<div class="dd-form">
		<i class="fas fa-caret-down" aria-hidden="true" onclick="gd_tabs_item_settings(this);"></i>
		<div class="dd-handle">
			<?php
			echo $field_icon;
			echo isset($field->admin_title) ? geodir_ucwords( ' ' . $field->admin_title  ) : ''; ?>
			<span class="dd-key" title="<?php _e('Open/Close','geodirectory');?>"><?php echo ' ('.esc_attr($field->field_type_name).')';?></span>
		</div>
		<div class="dd-setting <?php echo 'dd-type-'.esc_attr($field->field_type_name);?>">


			<input type="hidden" name="security" value="<?php echo sanitize_text_field( $nonce ); ?>"/>
			<input type="hidden" name="post_type" id="post_type"  value="<?php echo sanitize_text_field( $field->post_type ); ?>"/>
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
				// Some servers fail if a POST value is VARCHAR so we change it.
				if ( $value == 'VARCHAR' ) {
					$value = 'XVARCHAR';
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
				<p class="dd-setting-name gd-advanced-setting" data-setting="admin_title">
					<label for="gd-admin-title-<?php echo $field->id;?>">
						<?php
						echo geodir_help_tip( __( 'This is used as the field setting name here in the backend only.', 'geodirectory' ));
						_e('Admin name','geodirectory') ?><br>
						<input type="text" name="admin_title" id="gd-admin-title-<?php echo $field->id;?>" value="<?php echo $value; ?>"/>
					</label>
				</p>
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
				<p data-setting="frontend_title">
					<label for="frontend_title" class="dd-setting-name">
						<?php
						echo geodir_help_tip( __( 'This will be the label for the field input on the frontend.', 'geodirectory' ));
						_e( 'Field label :', 'geodirectory' ); ?>
						<input type="text" name="frontend_title" id="frontend_title" value="<?php echo $value; ?>"/>
					</label>
				</p>
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
				<p data-setting="frontend_desc">
					<label for="frontend_desc" class="dd-setting-name">
						<?php
						echo geodir_help_tip( __( 'This will be shown below the field on the add listing form.', 'geodirectory' ));
						_e( 'Field description :', 'geodirectory' ); ?>
						<input type="text" name="frontend_desc" id="frontend_desc" value="<?php echo $value; ?>"/>
					</label>
				</p>
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
				<p class="gd-advanced-setting" data-setting="htmlvar_name">
					<label for="htmlvar_name" class="dd-setting-name">
						<?php
						echo geodir_help_tip( __( 'This is a unique identifier used in the database and HTML, it MUST NOT contain spaces or special characters.', 'geodirectory' ));
						_e( 'Field key :', 'geodirectory' ); ?>
						<input type="text" name="htmlvar_name" id="htmlvar_name" pattern="[a-zA-Z0-9]+" maxlength="50"
						       title="<?php _e( 'Must not contain spaces or special characters', 'geodirectory' ); ?>"
						       value="<?php if ( $value ) {
							       echo preg_replace( '/geodir_/', '', $value, 1 );
						       } ?>" <?php
						if(isset($field->id) && substr( $field->id, 0, 4 ) === "new-" && empty($field->single_use)){} // New non single use predefined fields should have ability to change html_var
						elseif ( ! empty( $value ) && $value != 'geodir_' ) { echo 'readonly="readonly"'; }

						?> />
					</label>
				</p>
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
				<p <?php echo $field_display; ?> data-setting="is_active">
					<label for="is_active" class="dd-setting-name">
						<?php
						echo geodir_help_tip( __( 'If no is selected then the field will not be displayed anywhere.', 'geodirectory' ));
						_e( 'Is active', 'geodirectory' ); ?>
						<input type="hidden" name="is_active" value="0" />
						<input type="checkbox" name="is_active" value="1" <?php checked( $value, 1, true );?> />
					</label>
				</p>
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
				<p class="gd-advanced-setting" data-setting="for_admin_use">
					<label for="for_admin_use" class="dd-setting-name">
						<?php
						echo geodir_help_tip( __( 'If yes is selected then only site admin can see and edit this field on the add listing page.', 'geodirectory' ));
						_e( 'Admin only edit', 'geodirectory' ); ?>
						<input type="hidden" name="for_admin_use" value="0" />
						<input type="checkbox" name="for_admin_use" value="1" <?php checked( $value, 1, true );?> />
					</label>
				</p>
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
				<p class="gd-advanced-setting" data-setting="default_value">
					<label for="default_value" class="dd-setting-name">
						<?php
							if ( $field->field_type == 'checkbox' ) {
								echo geodir_help_tip( __( 'Should the checkbox be checked by default?', 'geodirectory' ));
							} else if ( $field->field_type == 'email' ) {
								echo geodir_help_tip( __( 'A default value for the field, usually blank. Ex: info@mysite.com', 'geodirectory' ));
							} else {
								echo geodir_help_tip( __( 'A default value for the field, usually blank. (for "link" this will be used as the link text)', 'geodirectory' ));
							}
							_e( 'Default value', 'geodirectory' ); ?>
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
					</label>
				</p>
				<?php
			}

			// db_default, this only shows on first add
			if(isset($field->id) && !is_numeric($field->id)) {
				do_action( "geodir_cfa_before_db_default_{$field->field_type}", $cf, $field );

				if ( has_filter( "geodir_cfa_db_default_{$field->field_type}" ) ) {

					echo apply_filters( "geodir_cfa_db_default_{$field->field_type}", '', $field->id, $cf, $field );

				} else {
					$value = '';
					if ( isset( $field->db_default ) ) {
						$value = esc_attr( $field->db_default );
					} elseif ( isset( $cf['defaults']['db_default'] ) && $cf['defaults']['db_default'] ) {
						$value = $cf['defaults']['db_default'];
					}
					?>
					<p class="gd-advanced-setting" data-setting="db_default">
						<label for="db_default" class="dd-setting-name">
							<?php
							if ( $field->field_type == 'checkbox' ) {
								echo geodir_help_tip( __( 'Should the value be set by default in the database?', 'geodirectory' ) );
							} else if ( $field->field_type == 'email' ) {
								echo geodir_help_tip( __( 'A default database value for the field, usually blank.', 'geodirectory' ) );
							} else {
								echo geodir_help_tip( __( 'A default database value for the field, usually blank.', 'geodirectory' ) );
							}
							_e( 'Database Default value', 'geodirectory' ); ?>
							<?php if ( $field->field_type == 'checkbox' ) { ?>
								<select name="db_default" id="db_default">
									<option value=""><?php _e( 'Unchecked', 'geodirectory' ); ?></option>
									<option
										value="1" <?php selected( true, (int) $value === 1 ); ?>><?php _e( 'Checked', 'geodirectory' ); ?></option>
								</select>
							<?php } else if ( $field->field_type == 'email' ) { ?>
								<input type="email" name="db_default"
								       placeholder="<?php _e( 'info@mysite.com', 'geodirectory' ); ?>"
								       id="db_default" value="<?php echo esc_attr( $value ); ?>"/><br/>
							<?php } else { ?>
								<input type="text" name="db_default" id="db_default"
								       value="<?php echo esc_attr( $value ); ?>"/><br/>
							<?php } ?>
						</label>
					</p>
					<?php
				}
			}

			// placeholder_value
			do_action( "geodir_cfa_before_placeholder_value_{$field->field_type}", $cf, $field);

			if ( has_filter( "geodir_cfa_placeholder_value_{$field->field_type}" ) ) {

				echo apply_filters( "geodir_cfa_placeholder_value_{$field->field_type}", '', $field->id, $cf, $field );

			} else {
				$value = '';
				if ( isset( $field->placeholder_value ) ) {
					$value = esc_attr( $field->placeholder_value );
				} elseif ( isset( $cf['defaults']['placeholder_value'] ) && $cf['defaults']['placeholder_value'] ) {
					$value = $cf['defaults']['placeholder_value'];
				}
				?>
				<p class="gd-advanced-setting" data-setting="placeholder_value">
					<label for="placeholder_value" class="dd-setting-name">
						<?php
						echo geodir_help_tip( __( 'A placeholder value to use for text input fields.', 'geodirectory' ));
						_e( 'Placeholder value', 'geodirectory' ); ?>
						<input type="text" name="placeholder_value" id="placeholder_value"
						       value="<?php echo esc_attr( $value ); ?>"/>
					</label>
				</p>
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
				<p data-setting="show_in">
					<label for="show_in" class="dd-setting-name">
						<?php
						echo geodir_help_tip( __( 'Select in what locations you want to display this field.', 'geodirectory' ));
						_e( 'Show in extra output location', 'geodirectory' );

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
							) ) || apply_filters( 'geodir_enable_field_type_in_owntab', false, $field->field_type, $field ) ) {
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
					</label>
				</p>
				<?php
			}

			do_action( "geodir_cfa_after_show_in_field", $cf, $field );

			// advanced_editor @todo this should be added via action
			if ( has_filter( "geodir_cfa_advanced_editor_{$field->field_type}" ) ) {
				echo apply_filters( "geodir_cfa_advanced_editor_{$field->field_type}", '', $field->id, $cf, $field );
			}

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
				<p class="" data-setting="is_required">
					<label for="is_required" class="dd-setting-name">
						<?php
						echo geodir_help_tip( __( 'Select yes to set field as required.', 'geodirectory' ));
						_e( 'Is required :', 'geodirectory' );
						?>
						<input type="hidden" name="is_required" value="0" />
						<input type="checkbox" name="is_required" value="1" <?php checked( $value, 1, true );?> onclick="gd_show_hide_radio(this,'show','cf-is-required-msg');" />
					</label>
				</p>

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
				<p  class="cf-is-required-msg gd-advanced-setting" <?php if ( ( isset( $field->is_required ) && $field->is_required == '0' ) || ! isset( $field->is_required ) ) {
					echo "style='display:none;'";
				} ?> data-setting="required_msg">
					<label for="required_msg" class="dd-setting-name">
						<?php
						echo geodir_help_tip( __( 'Enter text for the error message if the field is required and has not fulfilled the requirements.', 'geodirectory' ));
						_e( 'Required message:', 'geodirectory' ); ?>
						<input type="text" name="required_msg" id="required_msg" value="<?php echo esc_attr( $value ); ?>"/>
					</label>
				</p>
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
				<h3 class="gd-advanced-setting"><?php echo __( 'Custom css', 'geodirectory' ); ?></h3>
				<p class="gd-advanced-setting" data-setting="field_icon">
					<label for="field_icon" class="dd-setting-name">
						<?php
						echo geodir_help_tip( __( 'Upload icon using media and enter its url path, or enter font awesome class eg:"fas fa-home"', 'geodirectory' ));
						_e( 'Icon :', 'geodirectory' ); ?>  <a href="#gd-font-awesome-select" data-lity><?php _e('Select Icon','geodirectory');?></a>
						<input type="text" name="field_icon" id="field_icon"  value="<?php echo $value; ?>"/>
					</label>
				</p>
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
				<p class="gd-advanced-setting" data-setting="css_class">
					<label for="css_class" class="dd-setting-name">
						<?php
						$css_class_tool_tip = __( 'Enter custom css class for field custom style.', 'geodirectory' );
						if ( $field->field_type == 'multiselect' ) {
							$css_class_tool_tip .= ' '.__( '(Enter class `gd-comma-list` to show list as comma separated)', 'geodirectory' );
						}
						echo geodir_help_tip( $css_class_tool_tip);
						_e( 'Css class', 'geodirectory' ); ?>
						<input type="text" name="css_class" id="css_class"
						       value="<?php if ( isset( $field->css_class ) ) {
							       echo esc_attr( $field->css_class );
						       } ?>"/>
					</label>
				</p>
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
				<h3 class="gd-advanced-setting" data-setting="cat_sort_heading"><?php
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
				<p class="gd-advanced-setting" <?php echo $hide_cat_sort; ?> data-setting="cat_sort">
					<label for="cat_sort" class="dd-setting-name">
						<?php
						$tool_tip = __( 'Lets you use this field as a sorting option, set from sorting options above.', 'geodirectory' );
						echo geodir_help_tip( $tool_tip );
						_e( 'Include this field in sorting options', 'geodirectory' ); ?>
						<input type="hidden" name="cat_sort" value="0" />
						<input type="checkbox" name="cat_sort" value="1" <?php checked( $value, 1, true );?> />
					</label>
				</p>
				<?php
			}

			do_action( 'geodir_cfa_before_extra_fields', $cf, $field );

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

			/*
			?>


			<p>

				<label for="save" class="dd-setting-name">
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
					<?php endif;



					?>
				</div>
			</p>

			<?php */?>


			<p class="gd-tab-actions" data-setting="save_button">
				<?php
				$core_fields = array('post_title','post_content','post_tags','post_category','address','post_images');
				if ( ! ( ! empty( $field->htmlvar_name ) && in_array( $field->htmlvar_name, $core_fields ) ) && apply_filters( 'geodir_cfa_can_delete_field', true, $field ) ) {
				?>
				<a class="item-delete submitdelete deletion" id="delete-16" href="javascript:void(0);" onclick="gd_delete_custom_field('<?php echo esc_attr( $field->id ); ?>','<?php echo $nonce; ?>');return false;"><?php _e("Remove","geodirectory");?></a>
				<?php }else{echo "<a>&nbsp;</a>";}?>
				<input type="button" class="button button-primary" name="save" id="save" value="<?php _e("Save","geodirectory");?>" onclick="gd_save_custom_field('<?php echo esc_attr( $field->id ); ?>');return false;">
				<?php GeoDir_Settings_Page::toggle_advanced_button();?>
			</p>
		</div>
	</div>
</li>