<?php
/**
 * GeoDirectory Admin Settings Class
 *
 * @author   AyeCode
 * @category Admin
 * @package  GeoDirectory/Admin
 * @version  2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * GeoDir_Admin_Settings Class.
 */
class GeoDir_Admin_Settings {

	/**
	 * Setting pages.
	 *
	 * @var array
	 */
	private static $settings = array();

	/**
	 * Error messages.
	 *
	 * @var array
	 */
	private static $errors   = array();

	/**
	 * Update messages.
	 *
	 * @var array
	 */
	private static $messages = array();

	/**
	 * Include the settings page classes.
	 */
	public static function get_settings_pages() {
		if ( empty( self::$settings ) ) {
			$settings = array();

			include_once( dirname( __FILE__ ) . '/settings/class-geodir-settings-page.php' );

			$post_type = isset($_REQUEST['post_type']) ? sanitize_title($_REQUEST['post_type']) : '';
			// CPT Settings
			if(isset($_REQUEST['page']) && $_REQUEST['page']==$post_type.'-settings'){
				//$settings[] = include( 'settings/class-geodir-settings-cpt-cf.php' );
				$settings[] = include( 'settings/class-geodir-settings-cpt-sorting.php' );
				$settings[] = include( 'settings/class-geodir-settings-cpt-tabs.php' );
				$settings[] = include( 'settings/class-geodir-settings-cpt.php' );

			} else {
				$settings[] = include( 'settings/class-geodir-settings-general.php' );
				$settings[] = include( 'settings/class-geodir-settings-emails.php' );
				$settings[] = include( 'settings/class-geodir-settings-design.php' );
				$settings[] = include( 'settings/class-geodir-settings-import-export.php' );
				$settings[] = include( 'settings/class-geodir-settings-api.php' );
			}

			self::$settings = apply_filters( 'geodir_get_settings_pages', $settings );
		}

		return self::$settings;
	}

	/**
	 * Save the settings.
	 */
	public static function save() {
		global $current_tab;

		if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'geodirectory-settings' ) ) {
			die( __( 'Action failed. Please refresh the page and retry.', 'geodirectory' ) );
		}

		// Trigger actions
		do_action( 'geodir_settings_save_' . $current_tab );
		do_action( 'geodir_update_options_' . $current_tab );
		do_action( 'geodir_update_options' );

		self::add_message( __( 'Your settings have been saved.', 'geodirectory' ) );
		self::check_download_folder_protection();

		// Clear any unwanted data and flush rules
		delete_transient( 'geodir_cache_excluded_uris' );
		wp_schedule_single_event( time(), 'geodir_flush_rewrite_rules' );

		do_action( 'geodir_settings_saved' );
	}

	/**
	 * Add a message.
	 * @param string $text
	 */
	public static function add_message( $text ) {
		self::$messages[] = $text;
	}

	/**
	 * Add an error.
	 * @param string $text
	 */
	public static function add_error( $text ) {
		self::$errors[] = $text;
	}

	/**
	 * Output messages + errors.
	 * @return string
	 */
	public static function show_messages() {
		if ( sizeof( self::$errors ) > 0 ) {
			foreach ( self::$errors as $error ) {
				echo '<div id="message" class="error inline"><p><strong>' . esc_html( $error ) . '</strong></p></div>';
			}
		} elseif ( sizeof( self::$messages ) > 0 ) {
			foreach ( self::$messages as $message ) {
				echo '<div id="message" class="updated inline"><p><strong>' . esc_html( $message ) . '</strong></p></div>';
			}
		}
	}

	/**
	 * Settings page.
	 *
	 * Handles the display of the main geodirectory settings page in admin.
	 */
	public static function output($tab = '') {
		global $current_section, $current_tab;

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		do_action( 'geodir_settings_start' );

		//wp_enqueue_script( 'geodir_settings', GeoDir()->plugin_url() . '/assets/js/admin/settings' . $suffix . '.js', array( 'jquery', 'jquery-ui-datepicker', 'jquery-ui-sortable', 'iris', 'select2' ), GeoDir()->version, true );

		wp_localize_script( 'geodir_settings', 'geodir_settings_params', array(
			'i18n_nav_warning' => __( 'The changes you made will be lost if you navigate away from this page.', 'geodirectory' ),
		) );

		// Include settings pages
		self::get_settings_pages();

		// Get current tab/section
		if($tab){
			$current_tab = sanitize_title( $tab);

		}else{
			$current_tab = empty( $_GET['tab'] ) ? 'general' : sanitize_title( $_GET['tab'] );

		}
		$current_section = empty( $_REQUEST['section'] ) ? '' : sanitize_title( $_REQUEST['section'] );

		// Save settings if data has been posted
		if ( ! empty( $_POST ) ) {
			self::save();
		}

		// Add any posted messages
		if ( ! empty( $_GET['gd_error'] ) ) {
			self::add_error( stripslashes( $_GET['gd_error'] ) );
		}

		if ( ! empty( $_GET['gd_message'] ) ) {
			self::add_message( stripslashes( $_GET['gd_message'] ) );
		}

		// Get tabs for the settings page
		$tabs = apply_filters( 'geodir_settings_tabs_array', array() );
		
//		print_r($tabs);exit;

		include( dirname( __FILE__ ) . '/views/html-admin-settings.php' );
	}

	/**
	 * Get a setting from the settings API.
	 *
	 * @param mixed $option_name
	 * @return string
	 */
	public static function get_option( $option_name, $default = '' ) {
		return geodir_get_option( $option_name, $default );
	}

	/**
	 * Output admin fields.
	 *
	 * Loops though the geodirectory options array and outputs each field.
	 *
	 * @param array $options Opens array to output
	 */
	public static function output_fields( $options ) {
	//echo 'xxx';print_r($options);
		foreach ( $options as $value ) {
			if ( ! isset( $value['type'] ) ) {
				continue;
			}
			if ( ! isset( $value['id'] ) ) {
				$value['id'] = '';
			}
			if ( ! isset( $value['title'] ) ) {
				$value['title'] = isset( $value['name'] ) ? $value['name'] : '';
			}
			if ( ! isset( $value['class'] ) ) {
				$value['class'] = '';
			}
			if ( ! isset( $value['css'] ) ) {
				$value['css'] = '';
			}
			if ( ! isset( $value['default'] ) ) {
				$value['default'] = '';
			}
			if ( ! isset( $value['desc'] ) ) {
				$value['desc'] = '';
			}
			if ( ! isset( $value['desc_tip'] ) ) {
				$value['desc_tip'] = false;
			}
			if ( ! isset( $value['placeholder'] ) ) {
				$value['placeholder'] = '';
			}

			// Custom attribute handling
			$custom_attributes = array();

			if ( ! empty( $value['custom_attributes'] ) && is_array( $value['custom_attributes'] ) ) {
				foreach ( $value['custom_attributes'] as $attribute => $attribute_value ) {
					$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
				}
			}

			// Description handling
			$field_description = self::get_field_description( $value );
			extract( $field_description );

			// Switch based on type
			switch ( $value['type'] ) {

				// Section Titles
				case 'title':
					if ( ! empty( $value['title'] ) ) {
						$advanced = (isset($value['advanced']) && $value['advanced']) ? "gd-advanced-setting" :'';
						echo '<h2 class="gd-settings-title '.$advanced.'">';
						echo esc_html( $value['title'] );
						if(!empty($value['title_html'])){echo $value['title_html'];}
						if(isset($value['desc_tip']) && $value['desc_tip']){
							echo $tooltip_html;
						}
						echo '</h2>';
					}
					//print_r($value);
					if ( ! empty( $value['desc'] ) && (!isset($value['desc_tip']) || !$value['desc_tip']) ) {
						echo wpautop( wptexturize( wp_kses_post( $value['desc'] ) ) );
					}

					if(isset($value['seo_helper_tags']) && $value['seo_helper_tags']){
						echo GeoDir_SEO::helper_tags($value['seo_helper_tags']);
					}

					echo '<table class="form-table">' . "\n\n";
					if ( ! empty( $value['id'] ) ) {
						do_action( 'geodir_settings_' . sanitize_title( $value['id'] ) );
					}
					break;

				// Section Ends
				case 'sectionend':
					if ( ! empty( $value['id'] ) ) {
						do_action( 'geodir_settings_' . sanitize_title( $value['id'] ) . '_end' );
					}
					echo '</table>';
					if ( ! empty( $value['id'] ) ) {
						do_action( 'geodir_settings_' . sanitize_title( $value['id'] ) . '_after' );
					}
					break;

				// Standard text inputs and subtypes like 'number'
				case 'text':
				case 'email':
				case 'number':
				case 'password' :

					if ( isset( $value['value'] ) ) {
						$option_value = $value['value'];
					} else {
						$option_value = self::get_option( $value['id'], $value['default'] );
					}
					//echo $value['id'].'zzz'.$option_value;
					?><tr valign="top" class="<?php if(isset($value['advanced']) && $value['advanced']){echo "gd-advanced-setting";}?>">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php echo $tooltip_html; ?>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
							<input
								name="<?php echo esc_attr( $value['id'] ); ?>"
								id="<?php echo esc_attr( $value['id'] ); ?>"
								type="<?php echo esc_attr( $value['type'] ); ?>"
								style="<?php echo esc_attr( $value['css'] ); ?>"
								value="<?php echo esc_attr( $option_value ); ?>"
								class="regular-text <?php echo esc_attr( $value['class'] ); ?>"
								placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
								<?php echo implode( ' ', $custom_attributes ); ?>
								<?php if($value['type']=='number'){echo "lang='EN'";} // HTML5 number input can change number format depending on browser language, we don't want that ?>
								/> <?php echo $description; ?>
						</td>
					</tr><?php
					break;

				// Color picker.
				case 'color' :
					if ( isset( $value['value'] ) ) {
						$option_value = $value['value'];
					} else {
						$option_value = self::get_option( $value['id'], $value['default'] );
					}

					?><tr valign="top" class="gd-row-color-picker <?php if(isset($value['advanced']) && $value['advanced']){echo "gd-advanced-setting";}?>">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php echo $tooltip_html; ?>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
							<input 
									name="<?php echo esc_attr( $value['id'] ); ?>" 
									id="<?php echo sanitize_key( $value['id'] ); ?>" 
									type="text" 
									dir="ltr"
									value="<?php echo esc_attr( $option_value ); ?>" 
									class="gd-color-picker" 
									placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>" 
									data-default-color="<?php echo esc_attr( $value['default'] ); ?> 
									<?php echo esc_attr( implode( ' ', $custom_attributes ) ); ?> "/>&lrm; <?php echo $description; ?>
						</td>
					</tr><?php
					break;

				// Color picker.
				case 'image' :
					// add required scripts
					add_thickbox();
					wp_enqueue_script('media-upload');
					wp_enqueue_media();


					if ( isset( $value['value'] ) ) {
						$option_value = $value['value'];
					} else {
						$option_value = self::get_option( $value['id'], $value['default'] );
					}
					$image_size = ! empty( $value['image_size'] ) ? $value['image_size'] : 'thumbnail';

					if($option_value){
						$remove_class = '';
						if ( strpos( $option_value, 'dashicons-' ) === 0 ) {
							$show_img = '<div class="dashicons-before ' . esc_attr( $option_value ) . '"></div>';
						} else {
							$show_img = wp_get_attachment_image($option_value, $image_size);
						}
					}else{
						$remove_class = 'gd-hidden';
						$show_img = '<img src="'.admin_url( 'images/media-button-image.gif' ).'" />';
					}

					?><tr valign="top" class="<?php if(isset($value['advanced']) && $value['advanced']){echo "gd-advanced-setting";}?>">
					<th scope="row" class="titledesc">
						<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
						<?php echo $tooltip_html; ?>
					</th>
					<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">

						<div class="gd-upload-img" data-field="<?php echo esc_attr( $value['id'] ); ?>">
							<div class="gd-upload-display gd-img-size-<?php echo $image_size; ?> thumbnail"><div class="centered"><?php echo $show_img; ?></div></div>
							<div class="gd-upload-fields">
								<input type="hidden" id="<?php echo esc_attr( $value['id'] ); ?>" name="<?php echo esc_attr( $value['id'] ); ?>" value="<?php echo esc_attr( $option_value ); ?>" />
								<button type="button" class="gd_upload_image_button button"><?php _e( 'Upload Image', 'geodirectory' ); ?></button>
								<button type="button" class="gd_remove_image_button button <?php echo $remove_class;?>"><?php _e( 'Remove Image', 'geodirectory' ); ?></button>
							</div>
						</div>
					</td>
					</tr><?php
					break;

				// Textarea
				case 'textarea':

					if ( isset( $value['value'] ) ) {
						$option_value = $value['value'];
					} else {
						$option_value = self::get_option( $value['id'], $value['default'] );
					}

					?><tr valign="top" class="<?php if(isset($value['advanced']) && $value['advanced']){echo "gd-advanced-setting";}?>">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php echo $tooltip_html; ?>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
							<?php echo $description; ?>

							<textarea
								name="<?php echo esc_attr( $value['id'] ); ?>"
								id="<?php echo esc_attr( $value['id'] ); ?>"
								style="<?php echo esc_attr( $value['css'] ); ?>"
								class="large-text <?php echo esc_attr( $value['class'] ); ?>"
								placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
								<?php echo implode( ' ', $custom_attributes ); ?>
								><?php echo esc_textarea( $option_value );  ?></textarea>
							<?php if ( ! empty( $value['custom_desc'] ) ) { ?>
							<span class="gd-custom-desc"><?php echo $value['custom_desc']; ?></span>
							<?php } ?>
						</td>
					</tr><?php
					break;
				// Editor
				case 'editor':
					global $wp_version;
					if ( isset( $value['value'] ) ) {
						$option_value = $value['value'];
					} else {
						$option_value = self::get_option( $value['id'] );
					}
					if ( empty( $option_value ) && empty( $value['allow_blank'] ) ) {
						$option_value = isset( $value['default'] ) ? $value['default'] : '';
					}

					$rows = !empty( $value['size'] ) ? absint($value['size']) : 20;
					?><tr valign="top" class="<?php echo (!empty($value['advanced']) ? 'gd-advanced-setting' : ''); ?>">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php echo $tooltip_html; ?>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
							<?php echo $description; ?>
							<?php
							if ( $wp_version >= 3.3 && function_exists( 'wp_editor' ) ) {
								wp_editor( stripslashes( $option_value ), $value['id'], array( 'textarea_name' => esc_attr( $value['id'] ), 'textarea_rows' => $rows, 'media_buttons' => false, 'editor_class' => 'gd-wp-editor', 'editor_height' => 16 * $rows ) );
							} else { ?>
								<textarea
									name="<?php echo esc_attr( $value['id'] ); ?>"
									id="<?php echo esc_attr( $value['id'] ); ?>"
									style="<?php echo esc_attr( $value['css'] ); ?>"
									class="large-text <?php echo esc_attr( $value['class'] ); ?>"
									placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>" 
									rows="<?php echo $rows; ?>"
									<?php echo implode( ' ', $custom_attributes ); ?>
									><?php echo esc_textarea( stripslashes( $option_value ) );  ?></textarea>
							<?php } ?>
							<?php if ( ! empty( $value['custom_desc'] ) ) { ?>
							<span class="gd-custom-desc"><?php echo $value['custom_desc']; ?></span>
							<?php } ?>
						</td>
					</tr><?php
					break;

				// Select boxes
				case 'select' :
				case 'multiselect' :

					if ( isset( $value['value'] ) ) {
						$option_value = $value['value'];
					} else {
						$option_value = self::get_option( $value['id'], $value['default'] );
					}

					?><tr valign="top" class="<?php if(isset($value['advanced']) && $value['advanced']){echo "gd-advanced-setting";}?>">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php echo $tooltip_html; ?>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
							<select
								name="<?php echo esc_attr( $value['id'] ); ?><?php echo ( 'multiselect' === $value['type'] ) ? '[]' : ''; ?>"
								id="<?php echo esc_attr( $value['id'] ); ?>"
								style="<?php echo esc_attr( $value['css'] ); ?>"
								class="regular-text <?php echo esc_attr( $value['class'] ); ?>"
								<?php echo implode( ' ', $custom_attributes ); ?>
								<?php echo ( 'multiselect' == $value['type'] ) ? 'multiple="multiple"' : ''; ?>
								<?php echo ! empty( $value['sortable'] ) ? ' data-sortable="true"' : ''; ?>
								<?php echo ! empty( $value['placeholder'] ) ? ' data-placeholder="' . esc_attr( $value['placeholder'] ) . '"' : ''; ?>
								>
								<?php
									foreach ( $value['options'] as $key => $val ) {
										if(stripos(strrev($key), strrev('optgroup-open')) === 0){
											echo '<optgroup label="'.esc_attr( $val ).'">';
										}elseif(stripos(strrev($key), strrev('optgroup-close')) === 0){
											echo '</optgroup>';
										}else{
											?>
											<option value="<?php echo esc_attr( $key ); ?>" <?php

											if ( is_array( $option_value ) ) {
												selected( in_array( $key, $option_value ), true );
											} else {
												selected( $option_value, $key );
											}

											?>><?php echo $val ?></option>
											<?php
										}
									}
								?>
							</select> <?php echo $description; ?>
						</td>
					</tr><?php
					break;

				// Radio inputs
				case 'radio' :

					if ( isset( $value['value'] ) ) {
						$option_value = $value['value'];
					} else {
						$option_value = self::get_option( $value['id'], $value['default'] );
					}

					?><tr valign="top" class="<?php if(isset($value['advanced']) && $value['advanced']){echo "gd-advanced-setting";}?>">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php echo $tooltip_html; ?>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
							<fieldset>
								<?php echo $description; ?>
								<ul>
								<?php
									foreach ( $value['options'] as $key => $val ) {
										?>
										<li>
											<label><input
												name="<?php echo esc_attr( $value['id'] ); ?>"
												value="<?php echo $key; ?>"
												type="radio"
												style="<?php echo esc_attr( $value['css'] ); ?>"
												class="<?php echo esc_attr( $value['class'] ); ?>"
												<?php echo implode( ' ', $custom_attributes ); ?>
												<?php checked( $key, $option_value ); ?>
												/> <?php echo $val ?></label>
										</li>
										<?php
									}
								?>
								</ul>
							</fieldset>
						</td>
					</tr><?php
					break;

				// Checkbox input
				case 'checkbox' :

					if ( isset( $value['value'] ) ) {
						$option_value = $value['value'];
					} else {
						$option_value = self::get_option( $value['id'], $value['default'] );
					}
					$visbility_class = array();

					if ( ! isset( $value['hide_if_checked'] ) ) {
						$value['hide_if_checked'] = false;
					}
					if ( ! isset( $value['show_if_checked'] ) ) {
						$value['show_if_checked'] = false;
					}
					if ( 'yes' == $value['hide_if_checked'] || 'yes' == $value['show_if_checked'] ) {
						$visbility_class[] = 'hidden_option';
					}
					if ( 'option' == $value['hide_if_checked'] ) {
						$visbility_class[] = 'hide_options_if_checked';
					}
					if ( 'option' == $value['show_if_checked'] ) {
						$visbility_class[] = 'show_options_if_checked';
					}

					if ( ! isset( $value['checkboxgroup'] ) || 'start' == $value['checkboxgroup'] ) {
						?>
							<tr valign="top" class="<?php echo esc_attr( implode( ' ', $visbility_class ) ); ?> <?php if(isset($value['advanced']) && $value['advanced']){echo "gd-advanced-setting";}?>" >
								<th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ) ?></th>
								<td class="forminp forminp-checkbox">
									<fieldset>
						<?php
					} else {
						?>
							<fieldset class="<?php echo esc_attr( implode( ' ', $visbility_class ) ); ?>">
						<?php
					}

					if ( ! empty( $value['title'] ) ) {
						?>
							<legend class="screen-reader-text"><span><?php echo esc_html( $value['title'] ) ?></span></legend>
							
						<?php
					}

					?>
						<label for="<?php echo $value['id'] ?>">
							<input
								name="<?php echo esc_attr( $value['id'] ); ?>"
								id="<?php echo esc_attr( $value['id'] ); ?>"
								type="checkbox"
								class="<?php echo esc_attr( isset( $value['class'] ) ? $value['class'] : '' ); ?>"
								value="1"
								<?php checked( $option_value, '1' ); ?>
								<?php checked( $option_value, 'yes' ); ?>
								<?php echo implode( ' ', $custom_attributes ); ?>
							/> <?php echo $description ?>
						</label> <?php echo $tooltip_html; ?>
					<?php

					if ( ! isset( $value['checkboxgroup'] ) || 'end' == $value['checkboxgroup'] ) {
									?>
									</fieldset>
								</td>
							</tr>
						<?php
					} else {
						?>
							</fieldset>
						<?php
					}
					break;
				
				// Checkbox input
				case 'multicheckbox' :

					if ( isset( $value['value'] ) ) {
						$option_value = $value['value'];
					} else {
						$option_value = self::get_option( $value['id'], $value['default'] );
					}

					?>
					<tr valign="top" class="<?php if(isset($value['advanced']) && $value['advanced']){echo "gd-advanced-setting";}?>" >
						<th scope="row" class="titledesc">
							<label><?php echo esc_html( $value['title'] ); ?></label>
							<?php echo $tooltip_html; ?>
						</th>
						<td class="forminp forminp-checkbox">
							<div class="geodir-mcheck-rows geodir-mcheck-<?php echo sanitize_key( $value['id'] ); ?>">
								<?php foreach( $value['options'] as $key => $title ) { 
								if ( ! empty( $option_value ) && is_array( $option_value ) && in_array( $key, $option_value ) ) { 
									$checked = true;
								} else { 
									$checked = false; 
								}
								?>
								<div class="geodir-mcheck-row">
									<input
										name="<?php echo esc_attr( $value['id'] ); ?>[<?php echo $key; ?>]"
										id="<?php echo esc_attr( $value['id'] . '-' . sanitize_key( $key ) ); ?>"
										type="checkbox"
										class="<?php echo esc_attr( isset( $value['class'] ) ? $value['class'] : '' ); ?>"
										value="<?php echo $key; ?>"
										<?php checked( $checked, true ); ?>
										<?php echo implode( ' ', $custom_attributes ); ?>
									/> <label for="<?php echo $value['id'] . '-' . sanitize_key( $key ) ?>"><?php echo $title ?></label></div>
								<?php } ?>
								<?php echo $description; ?>
							</div>
						</td>
					</tr>
						<?php
					break;

				// Image width settings
				case 'image_width' :

					$image_size       = str_replace( '_image_size', '', $value['id'] );
					if ( isset( $value['value'] ) ) {
						$size = $value['value'];
					} else {
						$size = geodir_get_image_size( $image_size );
					}
					$width            = isset( $size['width'] ) ? $size['width'] : $value['default']['width'];
					$height           = isset( $size['height'] ) ? $size['height'] : $value['default']['height'];
					$crop             = isset( $size['crop'] ) ? $size['crop'] : $value['default']['crop'];
					$disabled_attr    = '';
					$disabled_message = '';

					if ( has_filter( 'geodir_get_image_size_' . $image_size ) ) {
						$disabled_attr = 'disabled="disabled"';
						$disabled_message = "<p><small>" . __( 'The settings of this image size have been disabled because its values are being overwritten by a filter.', 'geodirectory' ) . "</small></p>";
					}

					?><tr valign="top" class="<?php if(isset($value['advanced']) && $value['advanced']){echo "gd-advanced-setting";}?>">
						<th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ) ?> <?php echo $tooltip_html . $disabled_message; ?></th>
						<td class="forminp image_width_settings">

							<input name="<?php echo esc_attr( $value['id'] ); ?>[width]" <?php echo $disabled_attr; ?> id="<?php echo esc_attr( $value['id'] ); ?>-width" type="text" size="3" value="<?php echo $width; ?>" /> &times; <input name="<?php echo esc_attr( $value['id'] ); ?>[height]" <?php echo $disabled_attr; ?> id="<?php echo esc_attr( $value['id'] ); ?>-height" type="text" size="3" value="<?php echo $height; ?>" />px

							<label><input name="<?php echo esc_attr( $value['id'] ); ?>[crop]" <?php echo $disabled_attr; ?> id="<?php echo esc_attr( $value['id'] ); ?>-crop" type="checkbox" value="1" <?php checked( 1, $crop ); ?> /> <?php _e( 'Hard crop?', 'geodirectory' ); ?></label>

							</td>
					</tr><?php
					break;

				// Single page selects
				case 'single_select_page' :
					if ( isset( $value['value'] ) ) {
						$option_value = $value['value'];
					} else {
						$option_value = self::get_option( $value['id'] );
					}

					$args = array(
						'name'             => $value['id'],
						'id'               => $value['id'],
						'sort_column'      => 'menu_order',
						'sort_order'       => 'ASC',
						'show_option_none' => ' ',
						'class'            => ' regular-text '.$value['class'],
						'echo'             => false,
						'selected'         => absint( $option_value ),
					);

					$exclude_pages = array();
					if ( $page_on_front = get_option( 'page_on_front' ) ) {
						$exclude_pages[] = $page_on_front; // Exclude frontpage.
					}
					if ( $page_for_posts = get_option( 'page_for_posts' ) ) {
						$exclude_pages[] = $page_for_posts; // Exclude Blog page.
					}
					if ( ! empty( $exclude_pages ) ) {
						$args['exclude'] = $exclude_pages;
					}

					if ( isset( $value['args'] ) ) {
						$args = wp_parse_args( $value['args'], $args );
					}

					?><tr valign="top" class="single_select_page <?php if(isset($value['advanced']) && $value['advanced']){echo "gd-advanced-setting";}?>">
						<th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ) ?> <?php echo $tooltip_html; ?></th>
						<td class="forminp">
							<?php echo str_replace( ' id=', " data-placeholder='" . esc_attr__( 'Select a page&hellip;', 'geodirectory' ) . "' style='" . $value['css'] . "' class='" . $value['class'] . "' id=", wp_dropdown_pages( $args ) ); ?> <?php echo $description; ?>

							<?php if($args['selected']){ ?>
							<a href="<?php echo get_edit_post_link( $args['selected'] ); ?>" class="button gd-page-setting-edit"><?php _e('Edit Page','geodirectory');?></a>

								<?php 
								if ( empty( $value['is_template_page'] ) ) {
									$page_url = get_permalink( $args['selected'] );
									if ( ! empty( $value['view_page_args'] ) && is_array( $value['view_page_args'] ) ) {
										foreach ( $value['view_page_args'] as $_key => $_value ) {
											if ( ! empty( $_key ) && $_value != '' ) {
												$page_url = add_query_arg( $_key, $_value, $page_url );
											}
										}
									}
								?>
								<a href="<?php echo $page_url; ?>" class="button gd-page-setting-view"><?php _e('View Page','geodirectory');?></a>
							<?php }
							}
							
							if(!empty($value['default_content'])){

								$raw_default_content = '';
								$default_method = $value['id'].'_content';

								// check if the default content has been filtered
								if(method_exists('GeoDir_Defaults', $default_method) && GeoDir_Defaults::$default_method(true) != $value['default_content']){
									$raw_default_content = GeoDir_Defaults::$default_method(true);
								}
								?>
								<a href="#gd_default_content_<?php echo esc_attr($value['id'])?>" data-lity class="button gd-page-setting-view"><?php _e('View Default Content','geodirectory');?></a>
								<div id="gd_default_content_<?php echo esc_attr($value['id'])?>" style="background:#fff;" class="lity-hide gd-notification ">
									<?php
									$height = "50";
									if($raw_default_content){
										echo geodir_notification( array('gd-warn'=>__('Default content has been modified by a plugin or theme.','geodirectory')) );
										$height = "25";
									}
									?>
									<textarea style="min-width: 50vw;min-height: <?php echo $height;?>vh; display:block;"><?php echo $value['default_content'];?></textarea>
									<?php
									if($raw_default_content){
										echo geodir_notification( array('gd-info'=>__('Original content below.','geodirectory')) );
										?>
										<textarea style="min-width: 50vw;min-height: <?php echo $height;?>vh;display:block;"><?php echo $raw_default_content;?></textarea>

									<?php }
									?>
								</div>
								<?php
							}
							?>

						</td>
					</tr><?php
					break;

				// Single country selects
				case 'single_select_country' :
					if ( isset( $value['value'] ) ) {
						$country_setting = (string) $value['value'];
					} else {
						$country_setting = (string) self::get_option( $value['id'] );
					}

					if ( strstr( $country_setting, ':' ) ) {
						$country_setting = explode( ':', $country_setting );
						$country         = current( $country_setting );
						$state           = end( $country_setting );
					} else {
						$country = $country_setting;
						$state   = '*';
					}
					?><tr valign="top" class="<?php if(isset($value['advanced']) && $value['advanced']){echo "gd-advanced-setting";}?>">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php echo $tooltip_html; ?>
						</th>
						<td class="forminp">
						<select id="<?php echo esc_attr( $value['id'] ); ?>" name="<?php echo esc_attr( $value['id'] ); ?>" style="<?php echo esc_attr( $value['css'] ); ?>" data-placeholder="<?php esc_attr_e( 'Choose a country&hellip;', 'geodirectory' ); ?>" aria-label="<?php esc_attr_e( 'Country', 'geodirectory' ) ?>" class="regular-text <?php echo esc_attr( $value['class'] ); ?>">
							<?php
							echo geodir_get_country_dl($country);
							?>
						</select> <?php echo $description; ?>
						</td>
					</tr><?php
					break;

				// Country multiselects
				case 'multi_select_countries' :

					if ( isset( $value['value'] ) ) {
						$selections = (array) $value['value'];
					} else {
						$selections = (array) self::get_option( $value['id'] );
					}

					if ( ! empty( $value['options'] ) ) {
						$countries = $value['options'];
					} else {
						$countries = geodir_get_countries();
					}

					asort( $countries );
					?><tr valign="top" class="<?php if(isset($value['advanced']) && $value['advanced']){echo "gd-advanced-setting";}?>">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php echo $tooltip_html; ?>
						</th>
						<td class="forminp">
							<select multiple="multiple" name="<?php echo esc_attr( $value['id'] ); ?>[]" style="width:350px" data-placeholder="<?php esc_attr_e( 'Choose countries&hellip;', 'geodirectory' ); ?>" aria-label="<?php esc_attr_e( 'Country', 'geodirectory' ) ?>" class="geodir-select">
								<?php
									if ( ! empty( $countries ) ) {
										foreach ( $countries as $key => $val ) {
											echo '<option value="' . esc_attr( $key ) . '" ' . selected( in_array( $key, $selections ), true, false ) . '>' . $val . '</option>';
										}
									}
								?>
							</select> <?php echo ( $description ) ? $description : ''; ?> <br /><a class="select_all button" href="#"><?php _e( 'Select all', 'geodirectory' ); ?></a> <a class="select_none button" href="#"><?php _e( 'Select none', 'geodirectory' ); ?></a>
						</td>
					</tr><?php
					break;

				// Import/Export Listings
				case 'import_export_listings' :
					?>

					<tr valign="top" class="<?php if(isset($value['advanced']) && $value['advanced']){echo "gd-advanced-setting";}?>">
						<td class="forminp" colspan="2">
							<?php /**
							 * Contains template for import/export listings settings.
							 *
							 * @since 2.0.0
							 */
							include_once( dirname( __FILE__ ) . '/views/html-admin-settings-import-export-listings.php' );
							?>
						</td>
					</tr>

					<?php

					break;

				// Import/Export Categories
				case 'import_export_categories' :
					?>

					<tr valign="top" class="<?php if(isset($value['advanced']) && $value['advanced']){echo "gd-advanced-setting";}?>">
						<td class="forminp" colspan="2">
							<?php /**
							 * Contains template for import/export listings settings.
							 *
							 * @since 2.0.0
							 */
							include_once( dirname( __FILE__ ) . '/views/html-admin-settings-import-export-categories.php' );
							?>
						</td>
					</tr>

					<?php

					break;
					
				// Import/Export Reviews
				case 'import_export_reviews' :
					?>

					<tr valign="top" class="<?php if(isset($value['advanced']) && $value['advanced']){echo "gd-advanced-setting";}?>">
						<td class="forminp" colspan="2">
							<?php /**
							 * Contains template for import/export reviews.
							 *
							 * @since 2.0.0
							 */
							include_once( dirname( __FILE__ ) . '/views/html-admin-settings-import-export-reviews.php' );
							?>
						</td>
					</tr>

					<?php

					break;

				// Import/Export Categories
				case 'import_export_settings' :
					?>

					<tr valign="top" class="<?php if(isset($value['advanced']) && $value['advanced']){echo "gd-advanced-setting";}?>">
						<td class="forminp" colspan="2">
							<?php /**
							 * Contains template for import/export geodirectory settings.
							 *
							 * @since 2.0.0
							 */
							include_once( dirname( __FILE__ ) . '/views/html-admin-settings-import-export-settings.php' );
							?>
						</td>
					</tr>

					<?php

					break;
					
					// map
				case 'default_location_map' :
					// used by included file do not remove.
					$prefix = 'default_location_';
					$map_title = __("Set Address On Map", 'geodirectory');

					add_filter('geodir_add_listing_map_restrict','__return_false');



                    ?>

					<tr valign="top" class="<?php if(isset($value['advanced']) && $value['advanced']){echo "gd-advanced-setting";}?>">
						<td class="forminp" colspan="2">
					<?php /**
                     * Contains add listing page map functions.
                     *
                     * @since 1.0.0
                     */
                    include( GEODIRECTORY_PLUGIN_DIR . 'templates/map.php' );
					?>
						</td>
					</tr>

					<script>
						//jQuery('.gd-advanced-toggle')

						jQuery( ".gd-advanced-toggle" ).click(function() {
							///jQuery( "#default_location_set_address_button" ).toggle();
						});
					</script>

                    <?php

					break;

				case 'dummy_installer':

					

					GeoDir_Admin_Dummy_Data::dummy_data_ui();
					//geodir_autoinstall_admin_header($post_type);
					break;

				case 'map_key' :
					add_thickbox();// add the thickbox js/css
					if ( isset( $value['value'] ) ) {
						$option_value = $value['value'];
					} else {
						$option_value = self::get_option( $value['id'], $value['default'] );
					}
					//echo $value['id'].'zzz'.$option_value;
					?><tr valign="top" class="<?php if(isset($value['advanced']) && $value['advanced']){echo "gd-advanced-setting";}?>">
					<th scope="row" class="titledesc">
						<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
						<?php echo $tooltip_html; ?>
					</th>
					<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
						<input
							name="<?php echo esc_attr( $value['id'] ); ?>"
							id="<?php echo esc_attr( $value['id'] ); ?>"
							type="<?php echo esc_attr( $value['type'] ); ?>"
							style="<?php echo esc_attr( $value['css'] ); ?>"
							value="<?php echo esc_attr( $option_value ); ?>"
							class="regular-text <?php echo esc_attr( $value['class'] ); ?> <?php if(isset($_REQUEST['page']) && $_REQUEST['page']=='gd-setup'){echo "form-control w-100 mb-2";}?>"
							placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
							<?php echo implode( ' ', $custom_attributes ); ?>
						/>
						<?php $gm_api_url = 'https://console.developers.google.com/henhouse/?pb=["hh-1","maps_backend",null,[],"https://developers.google.com",null,["static_maps_backend","street_view_image_backend","maps_embed_backend","places_backend","geocoding_backend","directions_backend","distance_matrix_backend","geolocation","elevation_backend","timezone_backend","maps_backend"],null]';?>
						<a id="gd-api-key" onclick='window.open("<?php echo wp_slash($gm_api_url);?>", "newwindow", "width=600, height=400"); return false;' href='<?php echo $gm_api_url;?>' class="<?php if(isset($_REQUEST['page']) && $_REQUEST['page']=='gd-setup'){echo 'btn btn-primary btn-sm';}else{echo 'button-primary';}?>" name="<?php _e('Generate API Key - ( MUST be logged in to your Google account )','geodirectory');?>" ><?php _e('Generate API Key','geodirectory');?></a>
						<a href="https://console.developers.google.com/flows/enableapi?apiid=static_maps_backend,street_view_image_backend,maps_embed_backend,places_backend,geocoding_backend,directions_backend,distance_matrix_backend,geolocation,elevation_backend,timezone_backend,maps_backend&amp;keyType=CLIENT_SIDE&amp;reusekey=true" target="_blank"><?php _e('or get one here','geodirectory');?></a> :: (<a href="https://docs.wpgeodirectory.com/article/186-google-api" target="_blank"><?php _e('How to add a Google API KEY?','geodirectory');?>)</a>
						<br />
						<?php echo $description; ?>
					</td>
					</tr><?php
					break;

				case 'geocode_key' :
					add_thickbox();// add the thickbox js/css
					if ( isset( $value['value'] ) ) {
						$option_value = $value['value'];
					} else {
						$option_value = self::get_option( $value['id'], $value['default'] );
					}
					?><tr valign="top" class="<?php if(isset($value['advanced']) && $value['advanced']){echo "gd-advanced-setting";}?>">
					<th scope="row" class="titledesc">
						<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
						<?php echo $tooltip_html; ?>
					</th>
					<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
						<input
							name="<?php echo esc_attr( $value['id'] ); ?>"
							id="<?php echo esc_attr( $value['id'] ); ?>"
							type="<?php echo esc_attr( $value['type'] ); ?>"
							style="<?php echo esc_attr( $value['css'] ); ?>"
							value="<?php echo esc_attr( $option_value ); ?>"
							class="regular-text <?php echo esc_attr( $value['class'] ); ?>"
							placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
							<?php echo implode( ' ', $custom_attributes ); ?>
						/>
						<?php $gm_api_url = 'https://console.developers.google.com/henhouse/?pb=["hh-1","geocoding_backend",null,[],"https://developers.google.com",null,["geocoding_backend","timezone_backend"],null]';?>
						<a id="gd-geocode-api-key" onclick='window.open("<?php echo wp_slash($gm_api_url);?>", "newwindow", "width=600, height=400"); return false;' href='<?php echo $gm_api_url;?>' class="button-primary" name="<?php _e('Generate Geocoding API Key - ( MUST be logged in to your Google account )','geodirectory');?>" ><?php _e('Generate Geocoding API Key','geodirectory');?></a>
						<a href="https://console.developers.google.com/flows/enableapi?apiid=geocoding_backend,timezone_backend&amp;keyType=CLIENT_SIDE&amp;reusekey=true" target="_blank"><?php _e('or get one here','geodirectory');?></a>
						<br />
						<?php echo $description; ?>
					</td>
					</tr><?php
					break;

				// Select boxes
				case 'font-awesome' :
					// include the font-awesome data
					include_once( dirname( __FILE__ ) . '/settings/data_fontawesome.php' );
					$value['options'] = geodir_font_awesome_array();
					$rating_color = geodir_get_option('rating_color','#ff9900');

					if ( isset( $value['value'] ) ) {
						$option_value = $value['value'];
					} else {
						$option_value = self::get_option( $value['id'], $value['default'] );
					}

					?><tr valign="top" class="<?php if(isset($value['advanced']) && $value['advanced']){echo "gd-advanced-setting";}?>">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php echo $tooltip_html; ?>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
							<select
								name="<?php echo esc_attr( $value['id'] ); ?><?php echo ( 'multiselect' === $value['type'] ) ? '[]' : ''; ?>"
								id="<?php echo esc_attr( $value['id'] ); ?>"
								style="<?php echo esc_attr( $value['css'] ); ?>"
								class="regular-text <?php echo esc_attr( $value['class'] ); ?>"
								<?php echo implode( ' ', $custom_attributes ); ?>
								<?php echo ( 'multiselect' == $value['type'] ) ? 'multiple="multiple"' : ''; ?>
								>
								<option value=""><?php _e("Select icon","geodirectory");?></option>
								<?php
								foreach ( $value['options'] as $key => $val ) {
									?>
									<option value="<?php echo esc_attr( $key ); ?>" data-fa-icon="<?php echo esc_attr( $key ); ?>" <?php

									if ( is_array( $option_value ) ) {
										selected( in_array( $key, $option_value ), true );
									} else {
										selected( $option_value, $key );
									}
									?>><?php echo $key ?></option>
									<?php
								}
								?>
							</select> <?php echo $description; ?>
						</td>
					</tr><?php
					break;
				case 'dashicon' :
					$value['options'] = geodir_dashicon_options();

					if ( isset( $value['value'] ) ) {
						$option_value = $value['value'];
					} else {
						$option_value = self::get_option( $value['id'], $value['default'] );
					}
					$option_value = GeoDir_Post_types::sanitize_menu_icon( $option_value );

					?><tr valign="top" class="<?php if(isset($value['advanced']) && $value['advanced']){echo "gd-advanced-setting";}?>">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php echo $tooltip_html; ?>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
							<select
								name="<?php echo esc_attr( $value['id'] ); ?><?php echo ( 'multiselect' === $value['type'] ) ? '[]' : ''; ?>"
								id="<?php echo esc_attr( $value['id'] ); ?>"
								style="<?php echo esc_attr( $value['css'] ); ?>"
								class="regular-text <?php echo esc_attr( $value['class'] ); ?>"
								<?php echo implode( ' ', $custom_attributes ); ?>
								<?php echo ( 'multiselect' == $value['type'] ) ? 'multiple="multiple"' : ''; ?>
								>
								<?php
								foreach ( $value['options'] as $key => $val ) {
									?>
									<option value="<?php echo esc_attr( $key ); ?>" data-dashicon="<?php echo esc_attr( $key ); ?>" <?php
									if ( is_array( $option_value ) ) {
										selected( in_array( $key, $option_value ), true );
									} else {
										selected( $option_value, $key );
									}
									?>><?php echo str_replace( 'dashicons-', '', $key ); ?></option>
									<?php
								}
								?>
							</select> <?php echo $description; ?>
						</td>
					</tr><?php
					break;
					case 'hidden' :
						if ( isset( $value['value'] ) ) {
							$option_value = $value['value'];
						} else {
							$option_value = self::get_option( $value['id'], $value['default'] );
						}
						?><tr valign="top" style="display:none!important">
							<th scope="row" class="titledesc">
								<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							</th>
							<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
								<input
									name="<?php echo esc_attr( $value['id'] ); ?>"
									id="<?php echo esc_attr( $value['id'] ); ?>"
									type="hidden"
									value="<?php echo esc_attr( $option_value ); ?>"
									<?php echo implode( ' ', $custom_attributes ); ?>
									/> <?php echo $description; ?>
							</td>
						</tr><?php
					break;
				// Single timezone select
				case 'single_select_timezone' :
					if ( isset( $value['value'] ) ) {
						$timezone_string = (string) $value['value'];
					} else {
						$timezone_string = (string) self::get_option( $value['id'], $value['default'] );
					}
					$placeholder = ! empty( $value['placeholder'] ) ? $value['placeholder'] : __( 'Choose a city/timezone&hellip;', 'geodirectory' );
					$locale = function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
					?><tr valign="top" class="<?php if(isset($value['advanced']) && $value['advanced']){echo "gd-advanced-setting";}?>">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php echo $tooltip_html; ?>
						</th>
						<td class="forminp">
						<select id="<?php echo esc_attr( $value['id'] ); ?>" name="<?php echo esc_attr( $value['id'] ); ?>" style="<?php echo esc_attr( $value['css'] ); ?>" data-placeholder="<?php echo esc_attr( $placeholder ); ?>" aria-label="<?php esc_attr_e( 'Timezone', 'geodirectory' ) ?>" class="regular-text <?php echo esc_attr( $value['class'] ); ?>">
							<?php echo geodir_timezone_choice( $timezone_string, $locale ) ;?>
						</select> <?php echo $description; ?>
						</td>
					</tr><?php
					break;

				// Default: run an action
				default:
					do_action( 'geodir_admin_field_' . $value['type'], $value );
					break;
			}
		}
	}

	/**
	 * Helper function to get the formatted description and tip HTML for a
	 * given form field. Plugins can call this when implementing their own custom
	 * settings types.
	 *
	 * @param  array $value The form field value array
	 * @return array The description and tip as a 2 element array
	 */
	public static function get_field_description( $value ) {
		$description  = '';
		$tooltip_html = '';

		if ( true === $value['desc_tip'] ) {
			$tooltip_html = $value['desc'];
		} elseif ( ! empty( $value['desc_tip'] ) ) {
			$description  = $value['desc'];
			$tooltip_html = $value['desc_tip'];
		} elseif ( ! empty( $value['desc'] ) ) {
			$description  = $value['desc'];
		}

		if(!empty($value['docs'])){

			$docs_link = "<a class='geodir-docs-link' href='".esc_url($value['docs'])."' target='_blank'>".__('Documentation','geodirectory')." <i class=\"fas fa-external-link-alt\" aria-hidden=\"true\" aria-hidden=\"true\"></i></a>";

			if(in_array( $value['type'], array( 'checkbox' ) )){
				$description .= $docs_link;
			}else{
				$tooltip_html .= $docs_link;
			}
		}

		if ( $description && in_array( $value['type'], array( 'textarea', 'radio' ) ) ) {
			$description = '<p style="margin-top:0">' . wp_kses_post( $description ) . '</p>';
		} elseif ( $description && in_array( $value['type'], array( 'checkbox' ) ) ) {
			$description = wp_kses_post( $description );
		} elseif ( $description ) {
			$description = '<span class="description">' . wp_kses_post( $description ) . '</span>';
		}



		if ( $tooltip_html && in_array( $value['type'], array( 'checkbox' ) ) ) {
			$tooltip_html = '<p class="description">' . $tooltip_html . '</p>';
		} elseif ( $tooltip_html ) {
			$tooltip_html = geodir_help_tip( $tooltip_html );
		}



		return array(
			'description'  => $description,
			'tooltip_html' => $tooltip_html,
		);
	}

	/**
	 * Save admin fields.
	 *
	 * Loops though the geodirectory options array and outputs each field.
	 *
	 * @param array $options Options array to output
	 * @param array $data Optional. Data to use for saving. Defaults to $_POST.
	 * @return bool
	 */
	public static function save_fields( $options, $data = null ) {
		if ( is_null( $data ) ) {
			$data = $_POST;
		}
		if ( empty( $data ) ) {
			return false;
		}

		// Options to update will be stored here and saved later.
		$update_options = array();

		// Loop options and get values to save.
		foreach ( $options as $option ) {
			if ( ! isset( $option['id'] ) || ! isset( $option['type'] ) ) {
				continue;
			}

			// Get posted value.
			if ( strstr( $option['id'], '[' ) ) {
				parse_str( $option['id'], $option_name_array );
				$option_name  = current( array_keys( $option_name_array ) );
				$setting_name = key( $option_name_array[ $option_name ] );
				$raw_value    = isset( $data[ $option_name ][ $setting_name ] ) ? wp_unslash( $data[ $option_name ][ $setting_name ] ) : null;
			} else {
				$option_name  = $option['id'];
				$setting_name = '';
				$raw_value    = isset( $data[ $option['id'] ] ) ? wp_unslash( $data[ $option['id'] ] ) : null;
			}

			// Format the value based on option type.
			switch ( $option['type'] ) {
				case 'checkbox' :
					//$value = '1' === $raw_value || 'yes' === $raw_value ? 'yes' : 'no';
					$value = '1' === $raw_value ? 1 : 0;
					break;
				case 'textarea' :
				case 'editor' :
					$value = wp_kses_post( trim( $raw_value ) );
					break;
				case 'multiselect' :
				case 'multi_select_countries' :
					$value = array_filter( array_map( 'geodir_clean', (array) $raw_value ) );
					break;
				case 'multicheckbox' :
					$value = array_map( 'geodir_clean', (array) $raw_value );
					break;
				case 'image_width' :
					$value = array();
					if ( isset( $raw_value['width'] ) ) {
						$value['width']  = geodir_clean( $raw_value['width'] );
						$value['height'] = geodir_clean( $raw_value['height'] );
						$value['crop']   = isset( $raw_value['crop'] ) ? 1 : 0;
					} else {
						$value['width']  = $option['default']['width'];
						$value['height'] = $option['default']['height'];
						$value['crop']   = $option['default']['crop'];
					}
					break;
				case 'select':
					$allowed_values = empty( $option['options'] ) ? array() : array_keys( $option['options'] );
					if ( empty( $option['default'] ) && empty( $allowed_values ) ) {
						$value = null;
						break;
					}
					$default = ( empty( $option['default'] ) ? $allowed_values[0] : $option['default'] );
					$value   = in_array( $raw_value, $allowed_values ) ? $raw_value : $default;
					break;
				default :
					$value = geodir_clean( $raw_value );
					break;
			}

			/**
			 * Fire an action when a certain 'type' of field is being saved.
			 * @deprecated 2.4.0 - doesn't allow manipulation of values!
			 */
			if ( has_action( 'geodir_update_option_' . sanitize_title( $option['type'] ) ) ) {
				wc_deprecated_function( 'The geodir_update_option_X action', '2.4.0', 'geodir_admin_settings_sanitize_option filter' );
				do_action( 'geodir_update_option_' . sanitize_title( $option['type'] ), $option );
				continue;
			}

			/**
			 * Sanitize the value of an option.
			 * @since 2.4.0
			 */
			$value = apply_filters( 'geodir_admin_settings_sanitize_option', $value, $option, $raw_value );

			/**
			 * Sanitize the value of an option by option name.
			 * @since 2.4.0
			 */
			$value = apply_filters( "geodir_admin_settings_sanitize_option_$option_name", $value, $option, $raw_value );

			if ( is_null( $value ) ) {
				continue;
			}

			// Check if option is an array and handle that differently to single values.
			if ( $option_name && $setting_name ) {
				if ( ! isset( $update_options[ $option_name ] ) ) {
					$update_options[ $option_name ] = self::get_option( $option_name, array() );
				}
				if ( ! is_array( $update_options[ $option_name ] ) ) {
					$update_options[ $option_name ] = array();
				}
				$update_options[ $option_name ][ $setting_name ] = $value;
			} else {
				$update_options[ $option_name ] = $value;
			}

		}

		// Save all options in our array.
		foreach ( $update_options as $name => $value ) {
			//update_option( $name, $value );
			geodir_update_option($name, $value);
		}

		return true;
	}

	/**
	 * Checks which method we're using to serve downloads.
	 *
	 * If using force or x-sendfile, this ensures the .htaccess is in place.
	 */
	public static function check_download_folder_protection() {
		$upload_dir      = wp_upload_dir();
		$downloads_url   = $upload_dir['basedir'] . '/geodir_uploads';
		$download_method = get_option( 'geodir_file_download_method' );

		if ( 'redirect' == $download_method ) {

			// Redirect method - don't protect
			if ( file_exists( $downloads_url . '/.htaccess' ) ) {
				unlink( $downloads_url . '/.htaccess' );
			}
		} else {

			// Force method - protect, add rules to the htaccess file
			if ( ! file_exists( $downloads_url . '/.htaccess' ) ) {
				if ( $file_handle = @fopen( $downloads_url . '/.htaccess', 'w' ) ) {
					fwrite( $file_handle, 'deny from all' );
					fclose( $file_handle );
				}
			}
		}
	}
}
