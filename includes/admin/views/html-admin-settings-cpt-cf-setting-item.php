<?php
/**
 * Admin custom field form
 *
 * @since 1.0.0
 *
 * @package GeoDirectory
 */

global $aui_bs5;

/**
 * Displays the custom field form content.
 *
 * @since 1.0.0
 *
 * @global string $post_type Post type.
 */


$tab_class = isset( $field->id ) && strpos( $field->id, 'new' ) === 0 ? 'geodir-tab-new ' : '';
$tab_class .= ( isset( $field->field_type ) && $field->field_type == 'fieldset' ) && ! $tab_class ? '' : 'mjs-nestedSortable-no-nesting';
?>
<li class="dd-item mb-0 <?php echo $tab_class;?>" data-id="1" id="setName_<?php echo $field->id;?>" data-htmlvar_name="<?php echo ( isset( $field->htmlvar_name ) ? esc_attr( $field->htmlvar_name ) : '' );?>" id="setName_<?php echo $field->id;?>" data-field_type="<?php echo esc_attr( $field->field_type ); ?>" data-field_type_key="<?php echo esc_attr( $field->field_type_key ); ?>" data-field-nonce="<?php echo esc_attr( $nonce ); ?>">
	<div class="hover-shadow dd-form d-flex justify-content-between rounded c-pointer list-group-item border rounded-smx <?php echo $aui_bs5 ? 'text-start' : 'text-left';?> bg-light <?php if(empty($field->is_active)){echo 'border-warning';} ?>" onclick="gd_tabs_item_settings(this);">
		<div class="  flex-fill font-weight-bold fw-bold">
			<?php echo $field_icon; ?>
			<?php echo isset( $field->admin_title ) ? $field->admin_title : '';?>
			<span class="<?php echo $aui_bs5 ? 'float-end text-end' : 'float-right text-right';?> small" title="<?php _e('Open/Close','geodirectory');?>"><?php echo ' ('.esc_attr($field->field_type_name).')';?></span>
		</div>
		<div class="dd-handle">
			<?php do_action( 'geodir_cfa_tab_header_icon', $field, $cf ); ?>
			<?php
			if(empty($field->is_active)){
				?>
				<i class="fas fa-exclamation-triangle text-warning ml-2 ms-2" title="<?php _e("Inactive","geodirectory");?>" data-toggle="tooltip"></i>
			<?php } ?>
			<?php
			$core_fields = array('post_title','post_content','post_tags','post_category','address','post_images');
			if ( ! ( ! empty( $field->htmlvar_name ) && in_array( $field->htmlvar_name, $core_fields ) ) && apply_filters( 'geodir_cfa_can_delete_field', true, $field ) ) {
				?>
				<i class="far fa-trash-alt text-danger ml-2 ms-2" id="delete-16"  onclick="gd_delete_custom_field('<?php echo esc_attr( $field->id ); ?>','<?php echo $nonce; ?>');event.stopPropagation();return false;"></i>
			<?php }
			?>
			<i class="fas fa-grip-vertical text-muted ml-2 ms-2" style="cursor: move" aria-hidden="true" ></i>
		</div>
		<?php // store the form as a template. This saves a load of memory on page load. ?>
		<script type="text/template" class="dd-setting <?php echo 'dd-type-'.esc_attr($field->field_type_name);?>">
			<input type="hidden" name="security" value="<?php echo esc_attr( $nonce ); ?>"/>
			<input type="hidden" name="post_type" id="post_type"  value="<?php echo esc_attr( $field->post_type ); ?>"/>
			<input type="hidden" name="field_type" id="field_type"
			       value="<?php echo esc_attr( $field->field_type ); ?>"/>
			<input type="hidden" name="field_type_key" id="field_type_key"
			       value="<?php echo esc_attr( $field->field_type_key ); ?>"/>
			<input type="hidden" name="field_id" id="field_id"
			       value="<?php echo esc_attr( $field->id ); ?>"/>
			<input type="hidden" name="clabels" id="clabels" value="<?php if ( isset( $field->clabels ) ) {
				echo esc_attr( $field->clabels );
			} ?>"/>
			<input type="hidden" readonly="readonly" name="sort_order" id="sort_order"
			       value="<?php if ( isset( $field->sort_order ) ) {
				       echo absint( $field->sort_order );
			       } ?>"/>
			<input type="hidden" name="is_default" id="is_default"
			       value="<?php echo esc_attr( $field->is_default ); ?>"/>

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
				echo aui()->input(
					array(
						'id'                => 'frontend_title',
						'name'              => 'frontend_title',
						'label_type'        => 'top',
						'label'             => __('Label','geodirectory') . geodir_help_tip( __( 'This will be the label for the field input on the frontend.', 'geodirectory' )),
						'type'              =>   'text',
//						'wrap_class'        => geodir_advanced_toggle_class(),
						'value' => $value,
					)
				);

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

				echo aui()->input(
					array(
						'id'                => 'gd-admin-title-'.$field->id,
						'name'              => 'admin_title',
						'label_type'        => 'top',
						'label'             => __('Admin name','geodirectory') . geodir_help_tip( __( 'This is used as the field setting name here in the backend only.', 'geodirectory' )),
						'type'              =>   'text',
						'wrap_class'        => geodir_advanced_toggle_class(),
						'value' => $value,
					)
				);
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
				echo aui()->input(
					array(
						'id'                => 'frontend_desc',
						'name'              => 'frontend_desc',
						'label_type'        => 'top',
						'label'             => __('Description','geodirectory') . geodir_help_tip( __( 'This will be shown below the field on the add listing form.', 'geodirectory' )),
						'type'              =>   'text',
//						'wrap_class'        => geodir_advanced_toggle_class(),
						'value' => $value,
					)
				);

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

				$extra_attributes = array();
				$readonly = false;
				if(isset($field->id) && substr( $field->id, 0, 4 ) === "new-" && empty($field->single_use)){} // New non single use predefined fields should have ability to change html_var
				elseif ( ! empty( $value ) && $value != 'geodir_' ) { $extra_attributes['readonly'] = 'readonly'; }

				$extra_attributes['maxlength'] = 50;
				$extra_attributes['pattern'] = "[a-zA-Z0-9]+";

				echo aui()->input(
					array(
						'id'                => 'htmlvar_name',
						'name'              => 'htmlvar_name',
						'title'             => __( 'Must not contain spaces or special characters', 'geodirectory' ),
						'label_type'        => 'top',
						'label'             => __('Key','geodirectory') . geodir_help_tip( __( 'This is a unique identifier used in the database and HTML, it MUST NOT contain spaces or special characters.', 'geodirectory' )),
						'type'              =>   'text',
						'wrap_class'        => geodir_advanced_toggle_class(),
						'value' => $value ? preg_replace( '/geodir_/', '', $value, 1 ) : '',
						'extra_attributes' => $extra_attributes
					)
				);
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

				// Prevent is_active option for default fields.
				if ( ! empty( $field->htmlvar_name ) && in_array( $field->htmlvar_name, array( 'post_title', 'post_content', 'post_category', 'post_tags' ) ) ) {
					$value = '1';
					$wrap_class = 'd-none';
				} else {
					$wrap_class = '';
				}

				echo aui()->input(
					array(
						'id' => 'is_active',
						'name' => 'is_active',
						'type' => 'checkbox',
						'label_type' => 'horizontal',
						'label_col' => '4',
						'label' => __( 'Is active', 'geodirectory' ) ,
						'checked' => $value,
						'value' => '1',
						'switch' => 'md',
						'label_force_left' => true,
						'help_text' => geodir_help_tip( __( 'If no is selected then the field will not be displayed anywhere.', 'geodirectory' ) ),
						'wrap_class' => $wrap_class
					)
				);
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

				// Prevent for_admin_use option for default fields.
				if ( ! empty( $field->htmlvar_name ) && in_array( $field->htmlvar_name, array( 'post_title', 'post_content', 'post_category' ) ) ) {
					$value = '';
					$wrap_class = 'd-none';
				} else {
					$wrap_class = '';
				}

				echo aui()->input(
					array(
						'id' => 'for_admin_use',
						'name' => 'for_admin_use',
						'type' => 'checkbox',
						'label_type' => 'horizontal',
						'label_col' => '4',
						'label' => __( 'Admin edit only','geodirectory' ) ,
						'checked' => $value,
						'value' => '1',
						'switch' => 'md',
						'label_force_left' => true,
						'help_text' => geodir_help_tip( __( 'If yes is selected then only site admin can see and edit this field on the add listing page.', 'geodirectory' ) ),
						'wrap_class' => $wrap_class
					)
				);
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

				if ( $field->field_type == 'checkbox' ) {
					$help_text = geodir_help_tip( __( 'Should the checkbox be checked by default?', 'geodirectory' ));
				} else if ( $field->field_type == 'email' ) {
					$help_text = geodir_help_tip( __( 'A default value for the field, usually blank. Ex: info@mysite.com', 'geodirectory' ));
				} else {
					$help_text = geodir_help_tip( __( 'A default value for the field, usually blank. (for "link" this will be used as the link text)', 'geodirectory' ));
				}

				if ( $field->field_type == 'checkbox' ) {
					echo aui()->select(
						array(
							'id'                => 'default_value',
							'name'              =>  'default_value',
							'label_type'        => 'top',
							'multiple'   => false,
							'class'             => ' mw-100',
							'options'       => array(
								''   =>  __( 'Unchecked', 'geodirectory' ),
								'1'   =>  __( 'Checked', 'geodirectory' ),
							),
							'label'              => __('Default value','geodirectory') . $help_text,
							'value'         => $value ,
							'wrap_class'    => geodir_advanced_toggle_class(),
						)
					);
				}else{
					echo aui()->input(
						array(
							'id'                => 'default_value',
							'name'              => 'default_value',
							'label_type'        => 'top',
							'label'             => __('Default value','geodirectory') . $help_text,
							'type'              =>   'text',
							'wrap_class'        => geodir_advanced_toggle_class(),
							'value' => $value,
							'placeholder' =>  $field->field_type == 'email' ? __( 'info@mysite.com', 'geodirectory' ) : ''
						)
					);
				}

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

					if ( $field->field_type == 'checkbox' ) {
						$help_text = geodir_help_tip( __( 'Should the value be set by default in the database?', 'geodirectory' ) );
					} else if ( $field->field_type == 'email' ) {
						$help_text = geodir_help_tip( __( 'A default database value for the field, usually blank.', 'geodirectory' ) );
					} else {
						$help_text = geodir_help_tip( __( 'A default database value for the field, usually blank.', 'geodirectory' ) );
					}

					if ( $field->field_type == 'checkbox' ) {
						echo aui()->select(
							array(
								'id'                => 'db_default',
								'name'              =>  'db_default',
								'label_type'        => 'top',
								'multiple'   => false,
								'class'             => ' mw-100',
								'options'       => array(
									''   =>  __( 'Unchecked', 'geodirectory' ),
									'1'   =>  __( 'Checked', 'geodirectory' ),
								),
								'label'              => __('Database Default value','geodirectory') . $help_text,
								'value'         => $value ,
								'wrap_class'    => geodir_advanced_toggle_class(),
							)
						);
					}else{
						echo aui()->input(
							array(
								'id'                => 'db_default',
								'name'              => 'db_default',
								'label_type'        => 'top',
								'label'             => __('Database Default value','geodirectory') . $help_text,
								'type'              =>   'text',
								'wrap_class'        => geodir_advanced_toggle_class(),
								'value' => $value,
								'placeholder' =>  $field->field_type == 'email' ? __( 'info@mysite.com', 'geodirectory' ) : ''
							)
						);
					}

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

				echo aui()->input(
					array(
						'id'                => 'placeholder_value',
						'name'              => 'placeholder_value',
						'label_type'        => 'top',
						'label'              => __('Placeholder value','geodirectory') . geodir_help_tip( __( 'A placeholder value to use for text input fields.', 'geodirectory' )),
						'type'              =>   'text',
						'wrap_class'        => geodir_advanced_toggle_class(),
						'value' => $value,
					)
				);

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

				$show_in_locations = geodir_show_in_locations($field, $field->field_type );

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

				$show_in_values = explode( ',', $value );

				echo aui()->select(
					array(
						'id'                => 'show_in',
						'name'              =>  'show_in[]',
						'label_type'        => 'top',
						'multiple'   => true,
						'select2'    => true,
						'class'             => ' mw-100',
						'options'       => $show_in_locations,
						'label'              => __('Show in extra output location','geodirectory') . geodir_help_tip( __( 'Select in what locations you want to display this field.', 'geodirectory' )),
						'value'         => $show_in_values,
						'placeholder' => __( 'Select locations', 'geodirectory' ),
//						'wrap_class'    => geodir_advanced_toggle_class(),
					)
				);
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

				echo aui()->input(
					array(
						'id'                => 'is_required',
						'name'              => 'is_required',
						'label_type'        => 'horizontal',
						'label_col'        => '4',
						'label'              => __('Is required','geodirectory') ,
						'type'              =>   'checkbox',
						'checked' => $value,
						'value' => '1',
						'switch'    => 'md',
						'label_force_left'  => true,
						'help_text' => geodir_help_tip( __( 'Set field as required.', 'geodirectory' ))
					)
				);

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

				echo aui()->input(
					array(
						'id'                => 'required_msg',
						'name'              => 'required_msg',
						'label_type'        => 'top',
						'label'              => __('Required message','geodirectory') . geodir_help_tip( __( 'Enter text for the error message if the field is required and has not fulfilled the requirements.', 'geodirectory' )),
						'type'              =>   'text',
						///'wrap_class'    => geodir_advanced_toggle_class(),
						'value' => $value,
						'element_require' => '[%is_required%:checked]'
					)
				);

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
<!--				<h3 class="h6 border-bottom <?php echo geodir_advanced_toggle_class(); ?>">--><?php //echo __( 'Custom css', 'geodirectory' ); ?><!--</h3>-->
				<?php

				echo aui()->input(
					array(
						'id'                => 'field_icon',
						'name'              => 'field_icon',
						'label_type'        => 'top',
						'label'              => __('Icon','geodirectory') . geodir_help_tip( __( 'Upload icon using media and enter its url path, or enter font awesome class eg:"fas fa-home"', 'geodirectory' )),
						'type'              =>   'iconpicker',
						'wrap_class'        => geodir_advanced_toggle_class(),
						'value' => $value,
						'extra_attributes' => defined('FAS_PRO') && FAS_PRO ? array(
							'data-fa-icons'   => true,
							'data-bs-toggle'  => "tooltip",
							'data-bs-trigger' => "focus",
							'title'           => __('For pro icon variants (light, thin, duotone), paste the class here','geodirectory'),
						) : array(),
					)
				);

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

				$help_text = __( 'Enter custom css class for field custom style.', 'geodirectory' );
				if ( $field->field_type == 'multiselect' ) {
					$help_text.= ' '.__( '(Enter class `gd-comma-list` to show list as comma separated)', 'geodirectory' );
				}

				echo aui()->input(
					array(
						'id'                => 'css_class',
						'name'              => 'css_class',
						'label_type'        => 'top',
						'label'              => __('Css class','geodirectory') . geodir_help_tip($help_text),
						'type'              =>   'text',
						'wrap_class'        => geodir_advanced_toggle_class(),
						'value' => isset( $field->css_class ) ? esc_attr( $field->css_class ) : '',
					)
				);

			}


			// cat_sort
			do_action( "geodir_cfa_before_css_sort_{$field->field_type}", $cf, $field);

			if ( has_filter( "geodir_cfa_cat_sort_{$field->field_type}" ) ) {

				echo apply_filters( "geodir_cfa_cat_sort_{$field->field_type}", '', $field->id, $cf, $field );

			} else {

				// @todo we should just remove this and make all fields available as sort options.
				$value         = '';
				$hide_cat_sort = '';
				if ( isset( $field->cat_sort ) ) {
					$value = esc_attr( $field->cat_sort );
				} elseif ( isset( $cf['defaults']['cat_sort'] ) && $cf['defaults']['cat_sort'] ) {
					$value         = $cf['defaults']['cat_sort'];
					$hide_cat_sort = ( $value === false ) ? "style='display:none;'" : '';
				}

				$hide_cat_sort = ( isset( $cf['defaults']['cat_sort'] ) && $cf['defaults']['cat_sort'] === false ) ? "style='display:none;'" : '';
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
				$cat_sort_heading_label = apply_filters( 'geodir_advance_custom_fields_heading', __( 'Posts sort options', 'geodirectory' ), $field->field_type );
				$cat_sort_heading_label = apply_filters( 'geodir_advance_custom_fields_heading', __( 'Posts sort options', 'geodirectory' ), $field->field_type );
				?>
				<h3 class="border-bottom text-dark h4 pt-3 pb-2 mb-3 <?php echo geodir_advanced_toggle_class(); ?>" data-setting="cat_sort_heading"><?php echo $cat_sort_heading_label; ?></h3>
				<?php
				echo aui()->input(
					array(
						'id'                => 'cat_sort',
						'name'              => 'cat_sort',
						'label_type'        => 'horizontal',
						'label_col'        => '4',
						'label'              => __('Include this field in sorting options','geodirectory') ,
						'type'              =>   'checkbox',
						'checked' => $value,
						'value' => '1',
						'switch'    => 'md',
						'label_force_left'  => true,
						'wrap_class'    => geodir_advanced_toggle_class(),
						'help_text' => geodir_help_tip( __( 'Lets you use this field as a sorting option, set from sorting options above.', 'geodirectory' ))
					)
				);

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

			?>
			<div class="gd-tab-actions mb-0" data-setting="save_button">
				<span class="text-left text-start float-left float-start">
					<?php GeoDir_Settings_Page::toggle_advanced_button('btn btn-outline-primary text-left text-start gd-advanced-toggle-field',false);?>
				</span>

				<a class=" btn btn-link text-muted" href="javascript:void(0);" onclick="gd_tabs_close_settings(this); return false;"><?php _e("Close","geodirectory");?></a>
				<button type="button" class="btn btn-primary" name="save" id="save" data-save-text="<?php _e("Save","geodirectory");?>"  onclick="gd_save_custom_field('<?php echo esc_attr( $field->id ); ?>',event);jQuery(this).html('<span class=\'spinner-border spinner-border-sm\' role=\'status\'></span> <?php esc_attr_e( 'Saving', 'geodirectory' ); ?>').addClass('disabled');return false;">
					<?php _e("Save","geodirectory");?>
				</button>
			</div>
		</script>
	</div>
</li>
