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
				$settings[] = include( 'settings/class-geodir-settings-cpt-cf.php' );
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
		global $current_tab, $geodir_settings_error;

		if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'geodirectory-settings' ) ) {
			die( __( 'Action failed. Please refresh the page and retry.', 'geodirectory' ) );
		}

		// Trigger actions
		do_action( 'geodir_settings_save_' . $current_tab );

		// Show error message.
		if ( ! empty( $geodir_settings_error ) ) {
			if ( is_array( $geodir_settings_error ) ) {
				foreach ( $geodir_settings_error as $message ) {
					self::add_error( $message );
				}
			} else {
				self::add_error( $geodir_settings_error );
			}

			return;
		}

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
		global $aui_bs5;

		$disable_advanced = geodir_get_option( 'admin_disable_advanced', false );

		foreach ( $options as $value ) {
			if ( ! isset( $value['type'] ) ) {
				continue;
			}

			if ( $disable_advanced ) {
				$value['advanced'] = false;
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
				$custom_attributes = $value['custom_attributes'];
//				foreach ( $value['custom_attributes'] as $attribute => $attribute_value ) {
//					$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
//				}
			}

			// Description handling
			$field_description = self::get_field_description( $value );
			extract( $field_description );


			$boxed_settings = true;

			$label_type =  isset($_REQUEST['page']) && $_REQUEST['page']=='gd-setup' ? 'top' : 'horizontal';


			$bs_prefix = $aui_bs5 ? 'bs-' : '';

			// Switch based on type
			switch ( $value['type'] ) {

				// Section Titles
				case 'page-title':

					if ( ! empty( $value['title'] ) ) {

						echo '<div class="gd-setting-page-title"><h2 class="gd-settings-title h4 mb-0 ">';
						echo esc_html( $value['title'] );
						if(!empty($value['title_html'])){echo $value['title_html'];}
						if(isset($value['desc_tip']) && $value['desc_tip']){
							echo $tooltip_html;
						}
						echo '</h2></div>';
					}
					break;
				case 'title':
				case 'sectionstart':



					if ( $boxed_settings ) {
						$advanced = (isset($value['advanced']) && $value['advanced']) ? geodir_advanced_toggle_class() :'';
						echo '<div class="accordion '.$advanced.'" ><div class="card p-0 mw-100 border-0 shadow-sm" style="overflow: initial;">' . "\n\n";
						if ( ! empty( $value['title'] ) ) {

						echo '<div class="card-header bg-white rounded-top"><h2 class="gd-settings-title h5 mb-0 ">';
							echo esc_html( $value['title'] );
							if(!empty($value['title_html'])){echo $value['title_html'];}
							if(isset($value['desc_tip']) && $value['desc_tip']){
								echo $tooltip_html;
							}
						echo '</h2></div>';
						}

						echo '<div class="card-body">' . "\n\n";

						//print_r($value);
						if ( ! empty( $value['desc'] ) && (!isset($value['desc_tip']) || !$value['desc_tip']) ) {
							echo wpautop( wptexturize( wp_kses_post( $value['desc'] ) ) );
						}

						if(isset($value['seo_helper_tags']) && $value['seo_helper_tags']){
							echo GeoDir_SEO::helper_tags($value['seo_helper_tags']);
						}



						if ( ! empty( $value['id'] ) ) {
							do_action( 'geodir_settings_' . sanitize_title( $value['id'] ) );
						}
					}else{

						if ( ! empty( $value['title'] ) ) {
							$advanced = (isset($value['advanced']) && $value['advanced']) ? geodir_advanced_toggle_class() :'';
							echo '<h2 class="gd-settings-title h4 clearfix '.$advanced.'">';
							echo esc_html( $value['title'] );
							if(!empty($value['title_html'])){echo $value['title_html'];}
							if(isset($value['desc_tip']) && $value['desc_tip']){
								echo $tooltip_html;
							}
							echo '</h2>';
						}

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
					}


					break;

				// Section start
//				case 'sectionstart':
//
//					echo  $boxed_settings ? '<div><div><div>' : '<table>';
////
//					break;

				// Section Ends
				case 'sectionend':
					if ( ! empty( $value['id'] ) ) {
						do_action( 'geodir_settings_' . sanitize_title( $value['id'] ) . '_end' );
					}

					echo  $boxed_settings ? '</div></div></div>' : '</table>';
//					echo '</table>';
//					echo '</div></div></div>';
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

					echo aui()->input(
						array(
							'id'                => $value['id'],
							'name'              => $value['id'],
							'type'              => $value['type'] ?  $value['type']  : 'text',
							'label'             => $value['title'] . $tooltip_html,
							'label_type'        => $label_type,
							'label_col'         => '3',
							'label_class'       => 'font-weight-bold fw-bold',
							'placeholder'       => $value['placeholder'],
							'help_text'         => isset( $description ) ? $description : '',
							'required'          => ! empty( $value['required'] ) ? true : false,
							'value'             => $option_value,
							'class'             => ! empty( $value['class'] ) ? $value['class'] : '',
							'wrap_class'        => isset( $value['advanced'] ) && $value['advanced'] ? geodir_advanced_toggle_class() : '',
							'no_wrap'           => ! empty( $value['no_wrap'] ) ? true : false,
							'form_group_class'  => ! empty( $value['form_group_class'] ) ? $value['form_group_class'] : '',
							'input_group_left'  => ! empty( $value['input_group_left'] ) ? $value['input_group_left'] : '',
							'input_group_right' => ! empty( $value['input_group_right'] ) ? $value['input_group_right'] : '',
							'input_group_left_inside'  => ! empty( $value['input_group_left_inside'] ) ? $value['input_group_left_inside'] : '',
							'input_group_right_inside' => ! empty( $value['input_group_right_inside'] ) ? $value['input_group_right_inside'] : '',
							'extra_attributes'  => ! empty( $custom_attributes ) ? $custom_attributes : array(),
							'element_require'   => ! empty( $value['element_require'] ) ? $value['element_require'] : '',
						)
					);

					break;

				// Color picker.
				case 'color' :
					if ( isset( $value['value'] ) ) {
						$option_value = $value['value'];
					} else {
						$option_value = self::get_option( $value['id'], $value['default'] );
					}

					$custom_attributes['data-default-color'] = esc_attr( $value['default'] );

					echo aui()->input(
						array(
							'id'                => $value['id'],
							'name'              => $value['id'],
							'label_type'        => $label_type,
							'label_col'         => '3',
							'label_class'       => 'font-weight-bold fw-bold',
							'class'             => $value['class'].' gd-color-picker',
							'wrap_class'        => isset( $value['advanced'] ) && $value['advanced'] ? geodir_advanced_toggle_class() . " gd-row-color-picker" : ' gd-row-color-picker ',
							'label'             => $value['title'] . $tooltip_html,
							//'type'              => $value['type'] ?  $value['type']  : 'text',
							'type'              => 'text', // Use 'text' to allow HEX value input.
							'value'             => $option_value,
							'placeholder'       => $value['placeholder'],
							'help_text'         => isset($description) ? $description : '',
							'extra_attributes'  => ! empty( $custom_attributes ) ? $custom_attributes : array(),
							'element_require'   => ! empty( $value['element_require'] ) ? $value['element_require'] : '',
						)
					);

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
						} else if ( strpos( $option_value, 'plugins/geodirectory' ) === 0 ) {
							$show_img = '<img src="' . esc_url( geodir_file_relative_url( $option_value, true ) ) . '" />';
						} else {
							$show_img = wp_get_attachment_image($option_value, $image_size);
						}
					}else{
						$remove_class = 'gd-hidden';
						$show_img = '<img src="' . esc_url( geodir_plugin_url() . '/assets/images/media-button-image.gif' ) . '" />';
					}


					// @todo this can be improved a lot.
					echo aui()->input(
						array(
							'id'                => $value['id'],
							'name'              => $value['id'],
							'label_type'        => $label_type,
							'label_col'        => '3',
							'label_class'=> 'font-weight-bold fw-bold',
							'class' => !empty($value['class']) ? $value['class'] : '',
							//'required'          => true,
							'wrap_class'        => isset($value['advanced']) && $value['advanced'] ? geodir_advanced_toggle_class() : '',
							'label'              => $value['title'] . $tooltip_html,
							'type'              =>  'hidden',//$value['type'] ?  $value['type']  : 'text',
							'placeholder'       => $value['placeholder'],
							'value' => $option_value,
							'help_text'  => isset($description) ? $description : '',
							'extra_attributes'  => !empty($custom_attributes) ? $custom_attributes : array(),
							'input_group_left'  => '<div class="gd-upload-img" data-field="' . esc_attr( $value['id'] ) . '"><button type="button" class="gd_upload_image_button btn btn-outline-primary mr-2 me-2">' . __( 'Upload Image', 'geodirectory' ) . '</button><button type="button" class="gd_remove_image_button btn btn-outline-primary ' . $remove_class . '">' . __( 'Remove Image', 'geodirectory' ) . '</button> <div class="gd-upload-display gd-img-size-' . $image_size . ' thumbnail mr-3 me-3"><div class="centered">' . $show_img . '</div></div></div>',
							'element_require' => !empty($value['element_require']) ? $value['element_require'] : '',
						)
					);

					break;

				// Textarea
				case 'textarea':

					if ( isset( $value['value'] ) ) {
						$option_value = $value['value'];
					} else {
						$option_value = self::get_option( $value['id'], $value['default'] );
					}

					$rows = !empty( $value['size'] ) ? absint($value['size']) : 4;
					// Editor
					$wysiwyg = false;
					if ( ! empty( $value['wysiwyg'] ) ) {
						if ( is_array( $value['wysiwyg'] ) ) {
							$wysiwyg = $value['wysiwyg'];
						} else {
							$wysiwyg = array( 'quicktags' => true );
						}
						$value['allow_tags'] = true;
					}

					// Select placeholder on empty textarea focus.
					$class = ! empty( $value['class'] ) ? $value['class'] : 'active-placeholder';
					if ( strpos( $class, 'active-placeholder' ) === false ) {
						$class .= ' active-placeholder';
					}

					echo aui()->textarea(
						array(
							'id'                => $value['id'],
							'name'              => $value['id'],
							'label_type'        => $label_type,
							'label_col'         => '3',
							'class'             => $class,
							'label_class'       => 'font-weight-bold fw-bold',
							'wrap_class'        => isset( $value['advanced'] ) && $value['advanced'] ? geodir_advanced_toggle_class() : '',
							'label'             => $value['title'] . $tooltip_html,
							'placeholder'       => $value['placeholder'],
							'value'             => $option_value,
							'help_text'         => isset( $description ) ? $description : '',
							'rows'              => $rows,
							'wysiwyg'           => $wysiwyg,
							'allow_tags'        => isset( $value['allow_tags'] ) ? $value['allow_tags'] : true, // Allow HTML Tags. Default True.
							'no_wrap'           => ! empty( $value['no_wrap'] ) ? true : false,
							'form_group_class'  => ! empty( $value['form_group_class'] ) ? $value['form_group_class'] : '',
							'input_group_left'  => ! empty( $value['input_group_left'] ) ? $value['input_group_left'] : '',
							'input_group_right' => ! empty( $value['input_group_right'] ) ? $value['input_group_right'] : ( ! empty( $value['custom_desc'] ) ? "<div class='d-flex flex-wrap pt-3 text-muted'>" . $value['custom_desc'] . "</div>" : '' ),
							'input_group_left_inside'  => ! empty( $value['input_group_left_inside'] ) ? $value['input_group_left_inside'] : '',
							'input_group_right_inside' => ! empty( $value['input_group_right_inside'] ) ? $value['input_group_right_inside'] : '',
							'element_require'   => ! empty( $value['element_require'] ) ? $value['element_require'] : '',
							'extra_attributes'  => ! empty( $custom_attributes ) ? $custom_attributes : array()
						)
					);

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

					// @todo is this used in any settings?

					?><tr valign="top" class="<?php echo (!empty($value['advanced']) ? 'gd-advanced-setting' : ''); ?>">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php echo $tooltip_html; ?>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
							<?php echo $description; ?>
							<?php
							if ( $wp_version >= 3.3 && function_exists( 'wp_editor' ) ) {
								wp_editor( stripslashes( $option_value ), $value['id'], array( 'textarea_name' => esc_attr( $value['id'] ), 'wpautop' => false, 'textarea_rows' => $rows, 'media_buttons' => false, 'editor_class' => 'gd-wp-editor', 'editor_height' => 16 * $rows ) );
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


					$select2 = strpos($value['class'], 'geodir-select') !== false ? true : false;
//				$value['class'] = str_replace('geodir-select')
				$class = $aui_bs5 ? str_replace('geodir-select','',$value['class']) : $value['class'];

				echo aui()->select(
					array(
						'id'                => $value['id'],
						'name'              => $value['id'],
						'label_type'        => $label_type,
						'label_col'        => '3',
						'label_class'=> 'font-weight-bold fw-bold',
						'multiple'   => 'multiselect' == $value['type'] ? true : false,
						'class' => $class." mw-100",
						//'required'          => true,
						'select2'   => $select2,
						'options'       => $value['options'],
						'wrap_class'        => isset($value['advanced']) && $value['advanced'] ? geodir_advanced_toggle_class() : '',
						'label'              => $value['title'] . $tooltip_html,
						'placeholder'       => $value['placeholder'],
						'value' => $option_value,
						'help_text'  => isset($description) ? $description : '',
						'extra_attributes'  => !empty($custom_attributes) ? $custom_attributes : array(),
						'element_require' => !empty($value['element_require']) ? $value['element_require'] : '',
					)
				);

					break;

				// Radio inputs
				case 'radio' :

					if ( isset( $value['value'] ) ) {
						$option_value = $value['value'];
					} else {
						$option_value = self::get_option( $value['id'], $value['default'] );
					}


					$html = aui()->radio(
						array(
							'id'                => $value['id'],
							'name'              => $value['id'],
							'label_type'        => $label_type,
							'label_col'        => '3',
							'label_class'=> 'font-weight-bold fw-bold',
							'type'              => "radio",
//							'title'             => esc_attr__($cf['frontend_title'], 'geodirectory'),
							'label'             => $value['title'] . $tooltip_html,
//							'help_text'         => $help_text,
							'wrap_class'        => isset($value['advanced']) && $value['advanced'] ? geodir_advanced_toggle_class() : '',
							'class'             => '',
							'value'             => $option_value,
							'inline'            => false,
							'options'           => $value['options'],
//							'wrap_attributes'   => $conditional_attrs,
							'extra_attributes'  => !empty($custom_attributes) ? $custom_attributes : array(),
							'element_require' => !empty($value['element_require']) ? $value['element_require'] : '',
						)
					);

					echo $html;

					break;

				// Checkbox input
				case 'checkbox' :

					if ( isset( $value['value'] ) ) {
						$option_value = $value['value'];
					} else {
						$option_value = self::get_option( $value['id'], $value['default'] );
					}

					$description .= $tooltip_html;

//					echo '###'. $option_value;

					echo aui()->input(
					array(
						'id'                => $value['id'],
						'name'              => $value['id'],
						'value'              => !empty($value['value']) ? $value['value'] : '1',
						'label_type'        => $label_type,
						'label_col'        => '3',
						'label_class'=> 'font-weight-bold fw-bold',
						'label_force_left'  => true,
						'class' => !empty($value['class']) ? $value['class'] : '',
						//'required'          => true,
						'wrap_class'        => isset($value['advanced']) && $value['advanced'] ? geodir_advanced_toggle_class() : '',
						'label'              => $value['title'],
						'type'              =>  $value['type'] ?  $value['type']  : 'checkbox',
						'placeholder'       => $value['placeholder'],
						'checked' => $option_value,
						'help_text'  => isset($description) ? $description : ' ',
						'extra_attributes'  => !empty($custom_attributes) ? $custom_attributes : array(),
						'switch'    => 'md',
						'element_require' => !empty($value['element_require']) ? $value['element_require'] : '',
					)
				);

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
					add_thickbox();

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

					$defaults = array(
						'depth'                 => 0,
						'child_of'              => 0,
						'selected'              => 0,
						'echo'                  => 1,
						'name'                  => 'page_id',
						'id'                    => '',
						'class'                 => '',
						'show_option_none'      => '',
						'show_option_no_change' => '',
						'option_none_value'     => '',
						'value_field'           => 'ID',
					);

					$parsed_args = wp_parse_args( $args, $defaults );

					$page_options = array();

					if ( ! empty( $pages ) ) {
						foreach ( $pages as $page ) {
							$id = ! empty( $page->ID ) ? absint( $page->ID ) : '';
							$title = ! empty( $page->post_title ) ? esc_attr( $page->post_title ) : '';
							$page_options[ $id ] = $title;
						}
					}
					$page_options  = geodir_template_page_options();

					if ( isset( $parsed_args['show_option_none'] ) ) {
						$parsed_args['show_option_none'] = trim( $parsed_args['show_option_none'] );
					}

					$buttons = '';
					$buttons_links = array();

					ob_start();

					if ( $args['selected'] ) {
						$buttons_links[get_edit_post_link( $args['selected'] )] = __( 'Edit Page', 'geodirectory' );

						if ( empty( $value['is_template_page'] ) ) {
							$page_url = get_permalink( $args['selected'] );

							if ( ! empty( $value['view_page_args'] ) && is_array( $value['view_page_args'] ) ) {
								if ( $value['id'] == 'page_add' && get_post_type( (int) $args['selected'] ) && get_option( 'permalink_structure' ) && ! empty( $value['view_page_args']['listing_type'] ) ) {
									$page_url = trailingslashit( $page_url ) . geodir_cpt_permalink_rewrite_slug( $value['view_page_args']['listing_type'] ) . '/';

									unset( $value['view_page_args']['listing_type'] );
								}

								foreach ( $value['view_page_args'] as $_key => $_value ) {
									if ( ! empty( $_key ) && $_value != '' ) {
										$page_url = add_query_arg( $_key, $_value, $page_url );
									}
								}
							}
							$buttons_links[ $page_url ] = __( 'View Page', 'geodirectory' );
						}
					}

					if ( ! empty( $value['default_content'] ) ) {
						$raw_default_content = '';
						$default_method = $value['id'].'_content';
						$gutenberg = geodir_is_gutenberg();

						// check if the default content has been filtered
						if ( method_exists( 'GeoDir_Defaults', $default_method ) && GeoDir_Defaults::$default_method( true ) != $value['default_content'] ) {
							$raw_default_content = GeoDir_Defaults::$default_method( true, $gutenberg );
						}
						$buttons_links["#TB_inline?&width=650&height=350&inlineId=gd_default_content_".esc_attr($value['id'])] = __('View Default Content','geodirectory');
						?>
						<div id="gd_default_content_<?php echo esc_attr($value['id'])?>" style="background:#fff;display:none;" class="lity-hidex gd-notification ">
							<?php
							$height = "50";
							if($raw_default_content && $raw_default_content !== $value['default_content']){
								echo geodir_notification( array('gd-warn'=>__('Default content has been modified by a plugin or theme.','geodirectory')) );
								$height = "25";
							}
							?>
							<textarea style="min-width: calc(50vw - 32px);min-height: <?php echo $height;?>vh; display:block;"><?php echo $value['default_content'];?></textarea>
							<?php
							if($raw_default_content && $raw_default_content !== $value['default_content']){
								echo geodir_notification( array('gd-info'=>__('Original content below.','geodirectory')) );
								?>
								<textarea style="min-width: 50vw;min-height: <?php echo $height;?>vh;display:block;"><?php echo $raw_default_content;?></textarea>

							<?php }
							?>
						</div>
						<?php
					}

					if ( ! empty( $buttons_links ) ) { ?>
						<button class="btn btn-outline-primary dropdown-toggle" type="button" data-<?php echo $bs_prefix;?>toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php _e( "Actions", "geodirectory" ); ?></button>
						<div class="dropdown-menu">
							<?php
							foreach($buttons_links as $link => $title){
								if ( substr( $link, 0, 1 ) === "#" ) {
									echo '<a class="dropdown-item thickbox" href="'.esc_attr($link).'">'.$title.'</a>';
								}else{
									echo '<a class="dropdown-item" href="'.esc_attr($link).'">'.$title.'</a>';
								}

							}
							?>
						</div>
						<?php
					}
					$buttons = ob_get_clean();

					// Placeholder
					if ( ! empty( $parsed_args['show_option_none'] ) ) {
						$placeholder = $parsed_args['show_option_none'];
					} elseif ( ! empty( $parsed_args['placeholder'] ) ) {
						$placeholder = $parsed_args['placeholder'];
					} else {
						$placeholder = __( 'Select a page&hellip;', 'geodirectory' );
					}

					$class = $aui_bs5 ? str_replace('geodir-select','',$value['class']) : $value['class'];

					$output =  aui()->select(
						array(
							'id'                => $value['id'],
							'name'              => $value['id'],
							'label_type'        => $label_type,
							'label_col'         => '3',
							'label_class'       => 'font-weight-bold fw-bold',
							'multiple'          => 'multiselect' == $value['type'] ? true : false,
							'class'             => $buttons ? $class. ' mw-100 w-auto' : $class.' mw-100 w-100',
							'select2'           => strpos( $value['class'], 'geodir-select' ) !== false ? true : false,
							'options'           => array( '' => esc_html( $placeholder ) ) + $page_options,
							'wrap_class'        => ! empty( $value['advanced'] ) ? geodir_advanced_toggle_class() : '',
							'label'              => $value['title'] . $tooltip_html,
							'placeholder'       => esc_html( $placeholder ),
							'value'             => $option_value,
							'help_text'         => isset( $description ) ? $description : '',
							'extra_attributes'  => ! empty( $custom_attributes ) ? $custom_attributes : array(),
							'input_group_right' => $buttons,
							'element_require'   => ! empty( $value['element_require'] ) ? $value['element_require'] : '',
						)
					);

					echo $output;

					break;
				// Single template selects
				case 'single_wp_template' :
					add_thickbox();
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

					$post = $option_value ? get_post( absint( $option_value ) ) : array();

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

					$defaults = array(
						'depth'                 => 0,
						'child_of'              => 0,
						'selected'              => 0,
						'echo'                  => 1,
						'name'                  => 'page_id',
						'id'                    => '',
						'class'                 => '',
						'show_option_none'      => '',
						'show_option_no_change' => '',
						'option_none_value'     => '',
						'value_field'           => 'ID',
					);

					$parsed_args = wp_parse_args( $args, $defaults );

					$pages = self::get_wp_templates();
					$page_options = array();

					if ( ! empty( $pages ) ) {
						foreach ( $pages as $page ) {
							if ( $page->post_name && $page->post_name == 'front-page' ) {
								continue;
							}
							$id = ! empty( $page->ID ) ? absint( $page->ID ) : '';
							$title = ! empty( $page->post_title ) ? esc_attr( $page->post_title ) : '';
							$page_options[ $id ] = $title;
						}
					}

					if ( isset( $parsed_args['show_option_none'] ) ) {
						$parsed_args['show_option_none'] = trim( $parsed_args['show_option_none'] );
					}

					$buttons = '';
					$buttons_links = array();

					if ( $option_value && ! empty( $post ) ) {
						$customize_url = self::get_site_editor_url( $post->post_type, $post->post_name );
						$buttons_links[ $customize_url ] = __( 'Edit Template','geodirectory' );
					}

					if ( empty( $value['is_template_page'] ) ) {
						if ( ! empty( $value['page_option'] ) ) {
							$view_page_id = absint( geodir_get_option( $value['page_option'] ) );
						} else if ( ! empty( $option_value ) ) {
							$view_page_id = absint( $option_value );
						} else {
							$view_page_id = 0;
						}

						if ( $view_page_id && get_post_type( $view_page_id ) ) {
							$page_url = get_permalink( $view_page_id );

							if ( ! empty( $value['view_page_args'] ) && is_array( $value['view_page_args'] ) ) {
								if ( $value['id'] == 'template_add' && get_option( 'permalink_structure' ) && ! empty( $value['view_page_args']['listing_type'] ) ) {
									$page_url = trailingslashit( $page_url ) . geodir_cpt_permalink_rewrite_slug( $value['view_page_args']['listing_type'] ) . '/';

									unset( $value['view_page_args']['listing_type'] );
								}

								foreach ( $value['view_page_args'] as $_key => $_value ) {
									if ( ! empty( $_key ) ) {
										$page_url = add_query_arg( $_key, $_value, $page_url );
									}
								}
							}

							$buttons_links[ $page_url ] = __( 'View Page','geodirectory' );
						}
					}

					ob_start();

					if ( ! empty( $value['default_content'] ) ) {
						$raw_default_content = '';
						$default_method = $value['id'].'_content';
						$gutenberg = geodir_is_gutenberg();
						// Check if the default content has been filtered.
						if ( method_exists( 'GeoDir_Defaults', $default_method ) && GeoDir_Defaults::$default_method( true ) != $value['default_content'] ) {
							$raw_default_content = GeoDir_Defaults::$default_method( true, $gutenberg );
						}
						$buttons_links[ "#TB_inline?&width=650&height=350&inlineId=gd_default_content_".esc_attr( $value['id'] ) ] = __( 'View Default Content', 'geodirectory' );
						?>
						<div id="gd_default_content_<?php echo esc_attr($value['id'])?>" style="background:#fff;display:none;" class="lity-hidex gd-notification ">
							<?php
							$height = "50";
							if($raw_default_content && $raw_default_content !== $value['default_content']){
								echo geodir_notification( array('gd-warn'=>__('Default content has been modified by a plugin or theme.','geodirectory')) );
								$height = "25";
							}
							?>
							<textarea style="min-width: calc(50vw - 32px);min-height: <?php echo $height;?>vh; display:block;"><?php echo $value['default_content'];?></textarea>
							<?php
							if ( $raw_default_content && $raw_default_content !== $value['default_content']) {
								echo geodir_notification( array('gd-info'=>__('Original content below.','geodirectory')) );
								?>
								<textarea style="min-width: 50vw;min-height: <?php echo $height;?>vh;display:block;"><?php echo $raw_default_content;?></textarea>
							<?php }
							?>
						</div>
						<?php
					}

					if ( ! empty( $value['create_template'] ) ) {
						$buttons_links['#new-template'] = __( 'Create New Template','geodirectory' );
					}

					if ( ! empty( $buttons_links ) ) {
						?>
						<button class="btn btn-outline-primary dropdown-toggle" type="button" data-<?php echo $bs_prefix;?>toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php _e( "Actions", "geodirectory" ); ?></button>
						<div class="dropdown-menu">
							<?php
							foreach( $buttons_links as $link => $title ) {
								if ( $link == '#new-template' ) {
									$attrs = 'data-page="' . esc_attr( $value['id'] ) . '"';
									$reload = true;
									if ( ! empty( $value['page_cpt'] ) ) {
										$attrs .= ' data-post-type="' . esc_attr( $value['page_cpt'] ) . '"';

										if ( ! geodir_is_gd_post_type( $value['page_cpt'] ) ) {
											$reload = false;
										}
									}
									$attrs .= ' data-reload="' . (int) $reload . '"';
									echo '<a class="dropdown-item gd-wp-tmpl-new" href="javascript:void(0);" ' . $attrs . '>' . $title . '</a>';
								} else if ( substr( $link, 0, 1 ) === "#" ) {
									echo '<a class="dropdown-item thickbox" href="' . esc_attr( $link ) . '">' . $title . '</a>';
								} else {
									echo '<a class="dropdown-item" href="' . esc_attr( $link ) . '">' . $title . '</a>';
								}
							}
							?>
						</div>
						<?php
					}
					$buttons = ob_get_clean();

					// Placeholder
					if ( ! empty( $parsed_args['show_option_none'] ) ) {
						$placeholder = $parsed_args['show_option_none'];
					} elseif ( ! empty( $parsed_args['placeholder'] ) ) {
						$placeholder = $parsed_args['placeholder'];
					} else {
						$placeholder = __( 'Select a template&hellip;', 'geodirectory' );
					}

					$class = $aui_bs5 ? str_replace('geodir-select','',$value['class']) : $value['class'];

					$output =  aui()->select(
						array(
							'id'                => $value['id'],
							'name'              => $value['id'],
							'label_type'        => $label_type,
							'label_col'         => '3',
							'label_class'       => 'font-weight-bold fw-bold',
							'multiple'          => 'multiselect' == $value['type'] ? true : false,
							'class'             => $buttons ? $class. ' mw-100 w-auto' : $class.' mw-100 w-100',
							'select2'           => strpos( $value['class'], 'geodir-select' ) !== false ? true : false,
							'options'           => array( '' => esc_html( $placeholder ) ) + $page_options,
							'wrap_class'        => ! empty( $value['advanced'] ) ? geodir_advanced_toggle_class() : '',
							'label'             => $value['title'] . $tooltip_html,
							'placeholder'       => esc_html( $placeholder ),
							'value'             => $option_value,
							'help_text'         => isset( $description ) ? $description : '',
							'extra_attributes'  => ! empty( $custom_attributes ) ? $custom_attributes : array(),
							'input_group_right' => $buttons,
							'element_require'   => ! empty( $value['element_require'] ) ? $value['element_require'] : '',
						)
					);

					echo $output;

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

					$countries = geodir_get_country_dl($country,'',true);

					$class = $aui_bs5 ? str_replace('geodir-select','',$value['class']) : $value['class'];

					echo aui()->select(
						array(
							'id'                => $value['id'],
							'name'              => $value['id'],
							'label_type'        => $label_type,
							'label_col'        => '3',
							'label_class'=> 'font-weight-bold fw-bold',
							'multiple'   => 'multiselect' == $value['type'] ? true : false,
							'class'             => $class .' mw-100',
							//'required'          => true,
							'select2'   => strpos($value['class'], 'geodir-select') !== false ? true : false,
							'options'       => $countries,
							'wrap_class'        => isset($value['advanced']) && $value['advanced'] ? geodir_advanced_toggle_class() : '',
							'label'              => $value['title'] . $tooltip_html,
							'placeholder'       => $value['placeholder'],
							'value'         => $country,
							'help_text'  => isset($description) ? $description : '',
							'extra_attributes'  => !empty($custom_attributes) ? $custom_attributes : array(),
							'element_require' => !empty($value['element_require']) ? $value['element_require'] : '',
						)
					);

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

					<div class="<?php echo ( $aui_bs5 ? 'mb-3' : 'form-group' ); ?>">
					<?php /**
                     * Contains add listing page map functions.
                     *
                     * @since 1.0.0
                     */
                    include( GEODIRECTORY_PLUGIN_DIR . 'templates/map.php' );
					?>
					</div>

                    <?php

					break;

				case 'dummy_installer':



					GeoDir_Admin_Dummy_Data::dummy_data_ui();
					//geodir_autoinstall_admin_header($post_type);
					break;

				case 'map_key' :
					if ( isset( $value['value'] ) ) {
						$option_value = $value['value'];
					} else {
						$option_value = self::get_option( $value['id'], $value['default'] );
					}

					$gm_api_url = 'https://console.cloud.google.com/apis/enableflow?apiid=maps-backend.googleapis.com,static-maps-backend.googleapis.com,street-view-image-backend.googleapis.com,maps-embed-backend.googleapis.com,places-backend.googleapis.com,geocoding-backend.googleapis.com,directions-backend.googleapis.com,distance-matrix-backend.googleapis.com,geolocation.googleapis.com,elevation-backend.googleapis.com,timezone-backend.googleapis.com&keyType=CLIENT_SIDE&reusekey=true&pli=1';


					$custom_attributes['data-key-original'] = esc_attr($option_value);

					echo aui()->input(
						array(
							'id'                => $value['id'],
							'name'              => $value['id'],
							'label_type'        => $label_type,
							'label_col'        => '3',
							'class' => !empty($value['class']) ? $value['class'] : '',
							'label_class'=> 'font-weight-bold fw-bold',
							'wrap_class'        => isset($value['advanced']) && $value['advanced'] ? geodir_advanced_toggle_class() : '',
							'label'              => $value['title'] . $tooltip_html,
							'type'              =>  $value['type'] ?  $value['type']  : 'text',
							'placeholder'       => $value['placeholder'],
							'value' => $option_value,
							'help_text'  => $description,
							'extra_attributes'  => !empty($custom_attributes) ? $custom_attributes : array(),
							'input_group_right' => '<button class="btn btn-success text-white" type="button"  onclick="geodir_validate_google_api_key(jQuery(\'#google_maps_api_key\').val());">'.esc_attr__( 'Verify', 'geodirectory' ).'</button><div class="input-group-text c-pointer" data-toggle="tooltip" title="' . esc_attr__( 'API Key Guide', 'geodirectory' ) . '"><a href="https://wpgeodirectory.com/documentation/article/installation/get-a-google-api-key/" target="_blank" class="text-dark"><i class="fas fa-info-circle"></i></a></div><button class="btn btn-primary" type="button"  onclick=\'window.open("'.wp_slash($gm_api_url).'", "newwindow", "width=600, height=400"); return false;\' >' . esc_attr__( 'Generate Key', 'geodirectory' ) . '</button>',
							'element_require' => !empty($value['element_require']) ? $value['element_require'] : '',
						)
					);

					break;

				case 'geocode_key' :
					if ( isset( $value['value'] ) ) {
						$option_value = $value['value'];
					} else {
						$option_value = self::get_option( $value['id'], $value['default'] );
					}

					$gm_api_url = 'https://console.cloud.google.com/apis/enableflow?apiid=geocoding-backend.googleapis.com,timezone-backend.googleapis.com&keyType=CLIENT_SIDE&reusekey=true&pli=1';
					echo aui()->input(
						array(
							'id'                => $value['id'],
							'name'              => $value['id'],
							'label_type'        => $label_type,
							'label_col'        => '3',
							'class' => !empty($value['class']) ? $value['class'] : '',
							'label_class'=> 'font-weight-bold fw-bold',
							'wrap_class'        => isset($value['advanced']) && $value['advanced'] ? geodir_advanced_toggle_class() : '',
							'label'              => $value['title'] . $tooltip_html,
							'type'              =>  $value['type'] ?  $value['type']  : 'text',
							'placeholder'       => $value['placeholder'],
							'value' => $option_value,
							'help_text'  => $description,
							'extra_attributes'  => !empty($custom_attributes) ? $custom_attributes : array(),
							'input_group_right' => '<div class="input-group-text c-pointer" data-toggle="tooltip" title="' . esc_attr__( 'API Key Guide', 'geodirectory' ) . '"><a href="https://wpgeodirectory.com/documentation/article/installation/get-a-google-api-key/" target="_blank" class="text-dark"><i class="fas fa-info-circle"></i></a></div><button class="btn btn-primary" type="button"  onclick=\'window.open("'.wp_slash($gm_api_url).'", "newwindow", "width=600, height=400"); return false;\' >' . esc_attr__( 'Generate Key', 'geodirectory' ) . '</button>',
							'element_require' => !empty($value['element_require']) ? $value['element_require'] : '',
						)
					);

					break;

				// Select boxes
				case 'font-awesome' :
					// include the font-awesome data
					if ( ! function_exists( 'geodir_font_awesome_array' ) ) {
						include_once( dirname( __FILE__ ) . '/settings/data_fontawesome.php' );
					}
					$value['options'] = geodir_font_awesome_array();
					$rating_color = geodir_get_option('rating_color','#ff9900');

					if ( isset( $value['value'] ) ) {
						$option_value = $value['value'];
					} else {
						$option_value = self::get_option( $value['id'], $value['default'] );
					}

					// if Font Awesome pro is enabled then add a notice other variants can be used.
					if ( defined( 'FAS_PRO' ) && FAS_PRO ) {
						$custom_attributes = array(
							'data-fa-icons'   => true,
							'data-bs-toggle'  => "tooltip",
							'data-bs-trigger' => "focus",
							'title'           => __( 'For pro icon variants (light, thin, duotone), paste the class here', 'geodirectory' ),
						);

						if ( ! empty( $value['custom_attributes'] ) && is_array( $value['custom_attributes'] ) ) {
							$value['custom_attributes'] = $value['custom_attributes'] + $custom_attributes;
						} else {
							$value['custom_attributes'] = $custom_attributes;
						}
					}

					echo aui()->input(
						array(
							'type'              =>  'iconpicker',
							'id'                => $value['id'],
							'name'              => $value['id'],
							'label_type'        => $label_type,
							'label_col'        => '3',
							'label_class'=> 'font-weight-bold fw-bold',
							'class'             => $value['class'],
							'wrap_class'        => isset($value['advanced']) && $value['advanced'] ? geodir_advanced_toggle_class() : '',
							'label'              => $value['title'] . $tooltip_html,
							'placeholder'       => $value['placeholder'],
							'value'         => $option_value,
							'help_text'  => isset($description) ? $description : '',
							'extra_attributes'  => !empty($value['custom_attributes'] ) ? $value['custom_attributes'] : array(),
							'element_require' => !empty($value['element_require']) ? $value['element_require'] : '',
						)
					);

					break;
				case 'dashicon' :
					$value['options'] = geodir_dashicon_options();

					if ( isset( $value['value'] ) ) {
						$option_value = $value['value'];
					} else {
						$option_value = self::get_option( $value['id'], $value['default'] );
					}
					$option_value = GeoDir_Post_types::sanitize_menu_icon( $option_value );

					$options = array();

//					print_r( $value['options'] );exit;

					foreach ( $value['options'] as $key => $val ) {
						$options[] = array(
							'label' => str_replace( 'dashicons-', '', $key ),
							'value' => esc_attr($key) ,
							'extra_attributes' => array(
								'data-dashicon' => esc_attr( $key )
							)
						);
					}

					$value['class'] = str_replace( "geodir-select", "", $value['class'] );

					echo aui()->input(
						array(
							'type'              =>  'iconpicker',
							'id'                => $value['id'],
							'name'              => $value['id'],
							'label_type'        => $label_type,
							'label_col'        => '3',
							'label_class'=> 'font-weight-bold fw-bold',
							'multiple'   => 'multiselect' == $value['type'] ? true : false,
							'class'             => $value['class']. " gd-dashicons-picker",
							'wrap_class'        => isset($value['advanced']) && $value['advanced'] ? geodir_advanced_toggle_class() : '',
							'label'              => $value['title'] . $tooltip_html,
							'placeholder'       => $value['placeholder'],
							'value'         => $option_value,
							'help_text'  => isset($description) ? $description : '',
							'extra_attributes'  => !empty($value['custom_attributes'] ) ? $value['custom_attributes'] : array(),
							'element_require' => !empty($value['element_require']) ? $value['element_require'] : '',
						)
					);

					?>
					<script>
						var $dashicons = [
							<?php
							if ( ! empty( $value['options'] ) ) {
								foreach($value['options'] as $key => $val){
									?>
										{
											title: "<?php echo esc_attr($key);?>",
											searchTerms: []
										},
									<?php
								}
							}
							?>
						];

						jQuery(function() {
							jQuery.iconpicker.batch(".gd-dashicons-picker", 'destroy');
							jQuery(".gd-dashicons-picker").iconpicker({
								icons: $dashicons,
								fullClassFormatter: function(val) {
									return 'dashicons ' + val;
								},
							});
						});

					</script>
					<?php

					break;
					case 'hidden' :
						if ( isset( $value['value'] ) ) {
							$option_value = $value['value'];
						} else {
							$option_value = self::get_option( $value['id'], $value['default'] );
						}

						echo aui()->input(
							array(
								'id'                => $value['id'],
								'name'              => $value['id'],
								'label_type'        => $label_type,
								'label_col'        => '3',
								'label_class'=> 'font-weight-bold fw-bold',
								'class' => !empty($value['class']) ? $value['class'] : '',
								//'required'          => true,
								'wrap_class'        => isset($value['advanced']) && $value['advanced'] ? geodir_advanced_toggle_class() : 'd-none',
								'label'              => $value['title'] . $tooltip_html,
								'type'              =>  $value['type'] ?  $value['type']  : 'hidden',
								'placeholder'       => $value['placeholder'],
								'value' => $option_value,
								'help_text'  => isset($description) ? $description : '',
								'extra_attributes'  => !empty($custom_attributes) ? $custom_attributes : array(),
								'element_require' => !empty($value['element_require']) ? $value['element_require'] : '',
							)
						);

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

					$class = $aui_bs5 ? str_replace('geodir-select','',$value['class']) : $value['class'];

					$tz = geodir_timezone_choice( $timezone_string, $locale, true );
					echo aui()->select(
						array(
							'id'                => $value['id'],
							'name'              => $value['id'],
							'label_type'        => $label_type,
							'label_col'        => '3',
							'label_class'=> 'font-weight-bold fw-bold',
							'multiple'   => 'multiselect' == $value['type'] ? true : false,
							'class'             => $class . ' mw-100',
							//'required'          => true,
							'select2'   => strpos($value['class'], 'geodir-select') !== false ? true : false,
							'options'       => $tz,
							'wrap_class'        => isset($value['advanced']) && $value['advanced'] ? geodir_advanced_toggle_class() : '',
							'label'              => $value['title'] . $tooltip_html,
							'placeholder'       => $value['placeholder'],
							'value'         => $locale ,
							'help_text'  => isset($description) ? $description : '',
							'extra_attributes'  => !empty($custom_attributes) ? $custom_attributes : array(),
							'element_require' => !empty($value['element_require']) ? $value['element_require'] : '',
						)
					);

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
			$tooltip_html =  $tooltip_html;
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
		$geodir_temp_dir = $upload_dir['basedir'] . '/geodir_temp/';
		$index_file_path = $geodir_temp_dir . 'index.php';

		if ( file_exists( $index_file_path ) ) {
			// index file exists.
			return true;
		}

		if ( wp_mkdir_p( $geodir_temp_dir ) ) {
			$file = @fopen( $index_file_path, 'w' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_read_fopen

			if ( false === $file ) {
				return new WP_Error( 'geodir_temp_dir_error', __( 'Unable to protect geodir_temp folder from browsing.', 'geodirectory' ) );
			}

			fwrite( $file, "<?php\n// Silence is golden.\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fwrite
			fclose( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose

			return true;
		} else {
			return new WP_Error( 'geodir_temp_dir_error', __( 'Unable to create geodir_temp folder!', 'geodirectory' ) );
		}
	}

	/**
	 * Get the list of wp_template posts.
	 *
	 * @since 2.2.4
	 *
	 * @param array $args Array of arguments.
	 * @return array Lists of posts.
	 */
	public static function get_wp_templates( $args = array() ) {
		$defaults = array(
			'post_status'    => array( 'publish' ),
			'post_type'      => 'wp_template',
			'posts_per_page' => -1,
			'no_found_rows'  => true,
			'tax_query'      => array(
				array(
					'taxonomy' => 'wp_theme',
					'field'    => 'name',
					'terms'    => wp_get_theme()->get_stylesheet(),
				),
			),
		);

		$parsed_args = wp_parse_args( $args, $defaults );

		$template_query = new WP_Query( $parsed_args );

		return $template_query->posts;
	}

	/**
	 * Get the site editor for requested post.
	 *
	 * @since 2.2.4
	 *
	 * @param string $post_type The post type.
	 * @param string $post_name Post name.
	 * @param string $theme_slug Theme slug.
	 * @return string Full site editor page url.
	 */
	public static function get_site_editor_url( $post_type, $post_name, $theme_slug = '' ) {
		if ( empty( $theme_slug ) ) {
			$theme_slug = wp_get_theme()->get_stylesheet();
		}

		$post_id = $theme_slug . '//' . $post_name;

		return esc_url(
			add_query_arg(
				array(
					'postType' => $post_type,
					'postId' => $post_id,
				),
				admin_url( 'site-editor.php' )
			)
		);
	}
}
