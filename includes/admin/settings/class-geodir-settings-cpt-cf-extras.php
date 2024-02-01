<?php
/**
 * GeoDirectory CPT Settings
 *
 * @author      AyeCode
 * @category    Admin
 * @package     GeoDirectory/Admin
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'GeoDir_Settings_Cpt_Cf_Extras', false ) ) :


	/**
	 * GeoDir_Admin_Settings_General.
	 */
	class GeoDir_Settings_Cpt_Cf_Extras {

		/**
		 * The single instance of the class.
		 *
		 * @var GeoDirectory
		 * @since 2.0.0
		 */
		protected static $_instance = null;


		/**
		 * Main GeoDirectory Instance.
		 *
		 * Ensures only one instance of GeoDirectory is loaded or can be loaded.
		 *
		 * @since 2.0.0
		 * @static
		 * @see GeoDir()
		 * @return GeoDirectory - Main instance.
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Constructor.
		 */
		public function __construct() {

			// add data type to text field
			add_filter( 'geodir_cfa_data_type_text', array( $this, 'data_type' ), 10, 4 );

			// add wysiwyg option to textareas
			add_filter( 'geodir_cfa_extra_fields_textarea', array( $this, 'advanced_editor' ), 10, 4 );

			// add embed option to html & textareas
			add_filter( 'geodir_cfa_extra_fields_html', array( $this, 'embed_input' ), 10, 4 );
			add_filter( 'geodir_cfa_extra_fields_textarea', array( $this, 'embed_input' ), 10, 4 );

			// validation pattern
			add_filter( 'geodir_cfa_validation_pattern_text', array( $this, 'validation_pattern' ), 10, 4 );
			add_filter( 'geodir_cfa_validation_pattern_phone', array( $this, 'validation_pattern' ), 10, 4 );
			add_filter( 'geodir_cfa_validation_pattern_email', array( $this, 'validation_pattern' ), 10, 4 );
			add_filter( 'geodir_cfa_validation_pattern_url', array( $this, 'validation_pattern' ), 10, 4 );

			// category display type
			add_filter( 'geodir_cfa_extra_fields_categories', array( $this, 'category_input_type' ), 10, 4 );

			// extra address fields
			add_filter('geodir_cfa_extra_fields_address',array( $this, 'address_fields' ),10,4);

			// multiselect input
			add_filter('geodir_cfa_extra_fields_multiselect',array( $this,'multiselect_input'),10,4);

			// Multiple value option values
			add_filter( 'geodir_cfa_extra_fields_multiselect', array( $this, 'option_values' ), 10, 4 );
			add_filter( 'geodir_cfa_extra_fields_select', array( $this, 'option_values' ), 10, 4 );
			add_filter( 'geodir_cfa_extra_fields_radio', array( $this, 'option_values' ), 10, 4 );

			// Date picker date format
			add_filter('geodir_cfa_extra_fields_datepicker',array( $this,'date_format'),10,4);
			add_filter('geodir_cfa_extra_fields_datepicker',array( $this,'date_range'),10,4);

			// file type input
			add_filter('geodir_cfa_extra_fields_file',array( $this,'file_types'),10,4);
			add_filter('geodir_cfa_extra_fields_file',array( $this,'file_limit'),10,4);

			// post_images
			add_filter('geodir_cfa_extra_fields_images',array( $this,'file_limit'),10,4);

			// price fields
			add_filter('geodir_cfa_extra_fields_text',array( $this,'price_fields'),10,4);

			// REMOVE SOME FIELDS NO NEEDED FOR SOME CFs

			// htmlvar not needed for fieldset, taxonomy, business_hours
			add_filter( 'geodir_cfa_htmlvar_name_fieldset', '__return_empty_string', 10, 4 );
			add_filter( 'geodir_cfa_htmlvar_name_taxonomy', '__return_empty_string', 10, 4 );
			//add_filter( 'geodir_cfa_htmlvar_name_business_hours', '__return_empty_string', 10, 4 );

			// default_value not needed for textarea, html, file, fieldset, taxonomy, address, business_hours
			add_filter( 'geodir_cfa_default_value_file', '__return_empty_string', 10, 4 );
			add_filter( 'geodir_cfa_default_value_taxonomy', '__return_empty_string', 10, 4 );
			add_filter( 'geodir_cfa_default_value_address', '__return_empty_string', 10, 4 );
			add_filter( 'geodir_cfa_default_value_fieldset', '__return_empty_string', 10, 4 );
			add_filter( 'geodir_cfa_default_value_business_hours', '__return_empty_string', 10, 4 );

			// is_required not needed for fieldset
			add_filter( 'geodir_cfa_is_required_fieldset', '__return_empty_string', 10, 4 );
			add_filter( 'geodir_cfa_required_msg_fieldset', '__return_empty_string', 10, 4 );
			add_filter( 'geodir_cfa_is_required_business_hours', '__return_empty_string', 10, 4 );
			add_filter( 'geodir_cfa_required_msg_business_hours', '__return_empty_string', 10, 4 );

			// field_icon not needed for fieldset
			add_filter( 'geodir_cfa_field_icon_fieldset', '__return_empty_string', 10, 4 );
			add_filter( 'geodir_cfa_css_class_fieldset', '__return_empty_string', 10, 4 );

			// cat_sort not needed for some fields
			add_filter( 'geodir_cfa_cat_sort_html', '__return_empty_string', 10, 4 );
			add_filter( 'geodir_cfa_cat_sort_file', '__return_empty_string', 10, 4 );
			add_filter( 'geodir_cfa_cat_sort_url', '__return_empty_string', 10, 4 );
			add_filter( 'geodir_cfa_cat_sort_fieldset', '__return_empty_string', 10, 4 );
			add_filter( 'geodir_cfa_cat_sort_multiselect', '__return_empty_string', 10, 4 );
			add_filter( 'geodir_cfa_cat_sort_textarea', '__return_empty_string', 10, 4 );
			add_filter( 'geodir_cfa_cat_sort_taxonomy', '__return_empty_string', 10, 4 );
			add_filter( 'geodir_cfa_cat_sort_address', '__return_empty_string', 10, 4 );
			add_filter( 'geodir_cfa_cat_sort_business_hours', '__return_empty_string', 10, 4 );

			// address field is always required
			//add_filter( 'geodir_cfa_is_required_address', array($this,'is_required'), 10, 4 );

			// No of tags.
			add_filter( 'geodir_cfa_extra_fields_tags', array( $this, 'no_of_tags_input_type' ), 10, 4 );
		}

		/**
		 * Output hidden required field.
		 *
		 * @return string
		 */
		public function is_required(){
			return "<input type=\"hidden\" name=\"is_required\" value=\"1\" />";
		}

		/**
		 * Price fields.
		 *
		 * @since 2.0.0
		 *
		 * @param string $output Price fields html output.
		 * @param string $result_str Price fields results.
		 * @param array $cf Custom fields value.
		 * @param object $field_info Price fields information.
		 * @return string $output.
		 */
		public static function price_fields( $output, $result_str, $cf, $field_info ) {
			$extra_fields = isset( $field_info->extra_fields ) && $field_info->extra_fields != '' ? maybe_unserialize( $field_info->extra_fields ) : '';
			$radio_id = isset( $field_info->htmlvar_name ) ? $field_info->htmlvar_name : rand( 5, 500 );

			$data_type = isset( $field_info->data_type ) ? $field_info->data_type : '';

			$is_int = $data_type == 'INT' ? 1 : 0;
			$is_float = $data_type == 'FLOAT' || $data_type == 'DECIMAL' ? 1 : 0;
			$is_char = ! $is_int && ! $is_float ? 1 : 0;

			// is_price
			$is_price = 0;
			if ( ! empty( $extra_fields ) && isset( $extra_fields['is_price'] ) ) {
				$is_price = (int) $extra_fields['is_price'];
			} else if ( isset( $cf['defaults']['extra_fields']['is_price'] ) && $cf['defaults']['extra_fields']['is_price'] ) {
				$is_price = (int) $cf['defaults']['extra_fields']['is_price'];
			}

			// thousand_separator
			$thousand_separator = 'comma';
			if ( $extra_fields && isset( $extra_fields['thousand_separator'] ) ) {
				$thousand_separator = esc_attr( $extra_fields['thousand_separator'] );
			} else if ( isset( $cf['defaults']['extra_fields']['thousand_separator'] ) && $cf['defaults']['extra_fields']['thousand_separator'] ) {
				$thousand_separator = esc_attr( $cf['defaults']['extra_fields']['thousand_separator'] );
			}

			// decimal_separator
			$decimal_separator = '.';
			if ( $extra_fields && isset( $extra_fields['decimal_separator'] ) ) {
				$decimal_separator = esc_attr( $extra_fields['decimal_separator'] );
			} else if ( isset( $cf['defaults']['extra_fields']['decimal_separator'] ) && $cf['defaults']['extra_fields']['decimal_separator'] ) {
				$decimal_separator = esc_attr( $cf['defaults']['extra_fields']['decimal_separator'] );
			}

			// decimal_point
			$decimal_point = 2;
			if ( ! empty( $field_info ) && isset( $field_info->decimal_point ) ) {
				$decimal_point = (int) $field_info->decimal_point;
			} else if ( $extra_fields && isset( $extra_fields['decimal_point'] ) ) {
				$decimal_point = (int) $extra_fields['decimal_point'];
			} else if ( isset( $cf['defaults']['decimal_point'] ) && $cf['defaults']['decimal_point'] ) {
				$decimal_point = (int) $cf['defaults']['decimal_point'];
			} else if ( isset( $cf['defaults']['extra_fields']['decimal_point'] ) && $cf['defaults']['extra_fields']['decimal_point'] ) {
				$decimal_point = (int) $cf['defaults']['extra_fields']['decimal_point'];
			}

			// decimal_display
			$decimal_display = '';
			if ( $extra_fields && isset( $extra_fields['decimal_display'] ) ) {
				$decimal_display = esc_attr( $extra_fields['decimal_display'] );
			} else if ( isset( $cf['defaults']['extra_fields']['decimal_display'] ) && $cf['defaults']['extra_fields']['decimal_display'] ) {
				$decimal_display = esc_attr( $cf['defaults']['extra_fields']['decimal_display'] );
			}

			// currency_symbol
			$currency_symbol = '';
			if ( $extra_fields && isset( $extra_fields['currency_symbol'] ) ) {
				$currency_symbol = esc_attr( $extra_fields['currency_symbol'] );
			} else if ( isset( $cf['defaults']['extra_fields']['currency_symbol'] ) && $cf['defaults']['extra_fields']['currency_symbol'] ) {
				$currency_symbol = esc_attr( $cf['defaults']['extra_fields']['currency_symbol'] );
			}

			// currency_symbol_placement
			$currency_symbol_placement = '';
			if ( $extra_fields && isset( $extra_fields['currency_symbol_placement'] ) ) {
				$currency_symbol_placement = esc_attr( $extra_fields['currency_symbol_placement'] );
			} else if ( isset( $cf['defaults']['extra_fields']['currency_symbol_placement'] ) && $cf['defaults']['extra_fields']['currency_symbol_placement'] ) {
				$currency_symbol_placement = esc_attr( $cf['defaults']['extra_fields']['currency_symbol_placement'] );
			}

			$price_heading_style = '';
			if ( ! ( $is_int || $is_float ) ) {
				$is_price = 0;
				$thousand_separator = 'none';

				$price_heading_style = 'style="display:none;"';
			}

			if ( ! $is_float ) {
				$decimal_point = '';
			}

			if ( ! $is_price ) {
				$currency_symbol = '';
			}
			ob_start();
			?>
			<h3 class="gd-advanced-setting border-bottom text-dark h4 pt-3 pb-2 mb-3 aui-conditional-field" data-element-require="jQuery(form).find(&#039;[data-argument=&quot;data_type&quot;]&#039;).find(&#039;input,select,textarea&#039;).val() == &quot;INT&quot; || jQuery(form).find(&#039;[data-argument=&quot;data_type&quot;]&#039;).find(&#039;input,select,textarea&#039;).val() == &quot;DECIMAL&quot;" data-setting="price_heading"><?php _e( 'Number Options', 'geodirectory' ); ?></h3>
			<?php
			echo aui()->input(
				array(
					'id'               => 'is_price',
					'name'             => 'extra[is_price]',
					'label_type'       => 'horizontal',
					'label_col'        => '4',
					'label'            => __( 'Display as price', 'geodirectory' ),
					'type'             => 'checkbox',
					'checked'          => $is_price,
					'value'            => '1',
					'switch'           => 'md',
					'label_force_left' => true,
					'help_text'        => geodir_help_tip( __( 'Select if this field should be displayed as a price value.', 'geodirectory' ) ),
					'element_require'  => '[%data_type%] == "INT" || [%data_type%] == "DECIMAL"'
				)
			);

			echo aui()->input(
				array(
					'id'              => 'currency_symbol',
					'name'            => 'extra[currency_symbol]',
					'label_type'      => 'top',
					'label'           => __( 'Currency symbol', 'geodirectory' ) . geodir_help_tip( __( 'Select the currency symbol.', 'geodirectory' ) ),
					'type'            => 'text',
					'value'           => $currency_symbol,
					'element_require' => '[%is_price%:checked]'
				)
			);

			echo aui()->select(
				array(
					'id'              => 'currency_symbol_placement',
					'name'            => 'extra[currency_symbol_placement]',
					'label_type'      => 'top',
					'multiple'        => false,
					'class'           => ' mw-100',
					'options'         => array(
						'left'  => __( 'Left', 'geodirectory' ),
						'right' => __( 'Right', 'geodirectory' ),
					),
					'label'           => __( 'Currency symbol placement', 'geodirectory' ) . geodir_help_tip( __( 'Select the currency symbol placement.', 'geodirectory' ) ),
					'value'           => $currency_symbol_placement,
					'element_require' => '[%is_price%:checked]'
				)
			);

			echo aui()->select(
				array(
					'id'               => 'thousand_separator',
					'name'             => 'extra[thousand_separator]',
					'label_type'       => 'top',
					'multiple'         => false,
					'class'            => ' mw-100',
					'options'          => array(
						'comma'  => __( ', (comma)', 'geodirectory' ),
						'slash'  => __( '\ (slash)', 'geodirectory' ),
						'period' => __( '. (period)', 'geodirectory' ),
						'space'  => __( ' (space)', 'geodirectory' ),
						'none'   => __( '(none)', 'geodirectory' ),
					),
					'label'           => __( 'Thousand separator', 'geodirectory' ) . geodir_help_tip( __( 'Select the thousand separator.', 'geodirectory' ) ),
					'value'           => $thousand_separator,
					'element_require' => '([%is_price%:checked] || [%data_type%] == "INT" || [%data_type%] == "DECIMAL")'
				)
			);

			echo aui()->select(
				array(
					'id'                => 'decimal_separator',
					'name'              => 'extra[decimal_separator]',
					'label_type'        => 'top',
					'multiple'   => false,
					'class'             => ' mw-100',
					'options'       => array(
						'period'   => __( '. (period)', 'geodirectory' ),
						'comma'   => __( ', (comma)', 'geodirectory' ),
					),
					'label'              => __('Decimal separator','geodirectory') . geodir_help_tip( __( 'Decimal point to display after point.', 'geodirectory' )),
					'value'         => $decimal_separator,
					'element_require'   => '[%data_type%] == "DECIMAL"'
				)
			);

			echo aui()->select(
				array(
					'id'                => 'decimal_point',
					'name'              => 'decimal_point',
					'label_type'        => 'top',
					'multiple'   => false,
					'class'             => ' mw-100',
					'options'       => array(
						''   => __( 'Select', 'geodirectory' ),
						'1'   => '1',
						'2'   => '2',
						'3'   => '3',
						'4'   => '4',
						'5'   => '5',
						'6'   => '6',
						'7'   => '7',
						'8'   => '8',
						'9'   => '9',
						'10'   => '10',
					),
					'label'              => __('Decimal points','geodirectory') . geodir_help_tip( __( 'Decimals to display after point.', 'geodirectory' )),
					'value'         => $decimal_point,
					'element_require'   => '[%data_type%] == "DECIMAL"'
				)
			);

			echo aui()->select(
				array(
					'id'                => 'decimal_display',
					'name'              => 'extra[decimal_display]',
					'label_type'        => 'top',
					'multiple'   => false,
					'class'             => ' mw-100',
					'options'       => array(
						'if'   => __( 'Not show if not used', 'geodirectory' ),
						'always'   => __( 'Always (.00)', 'geodirectory' ),
					),
					'label'              => __('Decimal display','geodirectory') . geodir_help_tip( __( 'Select how the decimal is displayed if empty.', 'geodirectory' )),
					'value'         => $decimal_display,
					'element_require'   => '[%data_type%] == "DECIMAL" && [%decimal_point%] != "" '
				)
			);

			$output .= ob_get_clean();

			return $output;
		}

	    /**
         * File type input.
         *
         * @since 2.0.0
         *
         * @param string $output File input html output.
         * @param string $result_str File type result.
         * @param array $cf File type input custom fields.
         * @param object $field_info File type fields information.
         * @return string $output.
         */
		public static function file_types( $output, $result_str, $cf, $field_info ) {
			$allowed_file_types = geodir_allowed_mime_types();

			$extra_fields = isset($field_info->extra_fields) && $field_info->extra_fields != '' ? maybe_unserialize($field_info->extra_fields) : '';
			$gd_file_types = !empty($extra_fields) && !empty($extra_fields['gd_file_types']) ? maybe_unserialize($extra_fields['gd_file_types']) : array('*');
			if ( ! empty( $gd_file_types ) ) {
				if ( is_scalar( $gd_file_types ) ) {
					$gd_file_types = explode( ",", $gd_file_types );
				}

				$gd_file_types = array_filter( $gd_file_types );
			}

			$options = array(
				'*' => __('All types', 'geodirectory')
			);

			foreach ( $allowed_file_types as $format => $types ) {
				if ( ! empty( $types ) ) {
					$options[] = array(
						'optgroup'  => 'start',
						'label'     => esc_attr( wp_sprintf(__('%s formats', 'geodirectory'), __($format, 'geodirectory') ) )
					);

					foreach ( $types as $ext => $type ) {
						$options[] = array(
							'value'  => esc_attr($ext) ,
							'label'     => ".".esc_attr($ext)
						);
					}

					$options[] = array(
						'optgroup'  => 'end',
					);
				}
			}

			$output .= aui()->select(
				array(
					'id'                => 'gd_file_types',
					'name'              => 'extra[gd_file_types][]',
					'label_type'        => 'top',
					'multiple'   => true,
					'select2'   => true,
					'class'             => ' mw-100',
					'options'       => $options,
					'label'              => __('Allowed file types','geodirectory') . geodir_help_tip( __( 'Select file types to allowed for file uploading. (Select multiple file types by holding down "Ctrl" key.)', 'geodirectory' )),
					'value'         => $gd_file_types,
//					'element_require'   => '[%data_type%] == "DECIMAL" && [%decimal_point%] != "" '
				)
			);

			return $output;
		}

		/**
		 * File limit input.
		 *
		 * @since 2.0.0
		 *
		 * @param string $output
		 * @param string $result_str
		 * @param array $cf
		 * @param object $field_info
		 * @return string $output.
		 */
		public static function file_limit( $output, $result_str, $cf, $field_info ) {
			$extra_fields = ! empty( $field_info->extra_fields ) ? maybe_unserialize( $field_info->extra_fields ) : '';
			if ( strpos( $result_str, 'new-' ) === 0 && ! ( ! empty( $extra_fields ) && isset( $extra_fields['file_limit'] ) ) ) {
				$gd_file_limit = 1;
			} else {
				$gd_file_limit = ! empty( $extra_fields ) && ! empty( $extra_fields['file_limit'] ) ? absint( $extra_fields['file_limit'] ) : 0;
			}

			$output .= aui()->input(
				array(
					'id'                => 'gd_file_limit',
					'name'              => 'extra[file_limit]',
					'label_type'        => 'top',
					'label'              => __('File upload limit','geodirectory') . geodir_help_tip( __( 'Select the number of files that can be uploaded, Leave blank or 0 to allow unlimited files.', 'geodirectory' ) ),
					'type'              =>   'number',
					'value' => $gd_file_limit,
					'extra_attributes'  => array(
						'step'  => "1",
						'min'   => "0"
					)
//					'placeholder' =>  $field->field_type == 'email' ? __( 'info@mysite.com', 'geodirectory' ) : ''
				)
			);

			return $output;
		}

		/**
         * Datepicker date format.
         *
         * @since 2.0.0
         *
         * @param string $output Datepicker html output.
         * @param string $result_str Results string.
         * @param array $cf Datepicker custom fields values.
         * @param object $field_info Datepicker fields information.
         * @return string $output.
         */
		public static function date_format($output,$result_str,$cf,$field_info){
			ob_start();
			$extra = array();
			if ( isset( $field_info->extra_fields ) && $field_info->extra_fields != '' ) {
				$extra = maybe_unserialize( $field_info->extra_fields );
			}

			if ( is_array( $extra ) && empty( $extra['date_format'] ) ) {
				$extra['date_format'] = geodir_date_format();
			}

			$date_formats = array(
				'm/d/Y',
				'd/m/Y',
				'Y/m/d',
				'm-d-Y',
				'd-m-Y',
				'Y-m-d',
				'F j, Y',
			);
			/**
			 * Filter the custom field date format options.
			 *
			 * @since 1.6.5
			 * @param array $date_formats The PHP date format array.
			 */
			$date_formats = apply_filters('geodir_date_formats',$date_formats);
			$date_formats_rendered = array();
			foreach($date_formats as $format){
				$date_formats_rendered[$format] = $format . ' ('.date_i18n( $format, time()).')';
			}

			echo aui()->select(
				array(
					'id'                => 'date_format',
					'name'              => 'extra[date_format]',
					'label_type'        => 'top',
					'multiple'   => false,
					'class'             => ' mw-100',
					'options'       => $date_formats_rendered,
					'label'              => __('Date Format','geodirectory').geodir_help_tip( __( 'Select the date format.', 'geodirectory' )),
					'value'         => $extra['date_format'],
				)
			);

			$output .= ob_get_clean();
			return $output;
		}

		/**
		 * Datepicker date range.
		 *
		 * @since 2.0.0.46
		 *
		 * @param string $output Datepicker html output.
		 * @param string $result_str Results string.
		 * @param array $cf Datepicker custom fields values.
		 * @param object $field_info Datepicker fields information.
		 * @return string $output.
		 */
		public static function date_range( $output, $result_str, $cf, $field_info ) {
			$extra_fields = array();
			if ( ! empty( $field_info->extra_fields ) ) {
				$extra_fields = is_array( $field_info->extra_fields ) ? $field_info->extra_fields : maybe_unserialize( $field_info->extra_fields );
			}

			$value = '';
			if ( isset( $extra_fields['date_range'] ) ) {
				$value = esc_attr( $extra_fields['date_range'] );
			} elseif ( isset( $cf['defaults']['extra_fields']['date_range'] ) && $cf['defaults']['extra_fields']['date_range'] ) {
				$value = esc_attr( $cf['defaults']['extra_fields']['date_range'] );
			}

			$output .= aui()->input(
				array(
					'id'                => 'date_range',
					'name'              => 'extra[date_range]',
					'label_type'        => 'top',
					'label'              => __('Date Range','geodirectory') . geodir_help_tip( __( 'Set the date range, eg: 1920:2020 or for current dates: c-100:c+5', 'geodirectory' )),
					'type'              =>   'text',
					///'wrap_class' => geodir_advanced_toggle_class(),
					'value' => $value,
				)
			);

			return $output;
		}

		/**
		 * Render select field option values.
		 *
		 * @since 2.0.0
		 *
		 * @param string $output Html output.
		 * @param string $result_str Results string.
		 * @param array  $cf Input custom fields.
		 * @param object $field_info Fields information.
		 * @return string $output.
		 */
		public function option_values( $output, $result_str, $cf, $field_info ) {
			$field_type = isset( $field_info->field_type ) ? $field_info->field_type : '';
			$value = '';

			if ( isset( $field_info->option_values ) ) {
				$value = $field_info->option_values;
			} else if ( isset( $cf['defaults']['option_values'] ) && $cf['defaults']['option_values'] ) {
				$value = $cf['defaults']['option_values'];
			}

			// Converts old option values format to new format.
			$value = geodir_convert_old_option_values( $value );

			$placeholder = esc_html__( 'For Sale
under-offer : Under Offer
optgroup : Electronics
TV
Laptop
optgroup-close', 'geodirectory' );

			$output .= aui()->textarea(
				array(
					'id' => 'option_values',
					'name' => 'option_values',
					'label' => __( 'Option Values', 'geodirectory' ),
					'help_text' => esc_html__( 'Enter each option on a new line with format "LABEL" OR "VALUE : LABEL". To grouping options use "optgroup : OPTGROUP-LABEL" & "optgroup-close".', 'geodirectory' ),
					'label_type' => 'top',
					'placeholder' => $placeholder,
					'value' => $value,
					'rows' => 4
				)
			);

			return $output;
		}

        /**
         * The multiselect input setting.
         *
         * @since 2.0.0
         *
         * @param string $output Html output.
         * @param string $result_str Results string.
         * @param array $cf Custom fields values.
         * @param object $field_info fields information.
         * @return string $output.
         */
		public static function multiselect_input($output,$result_str,$cf,$field_info){

			$extra_fields = !empty($field_info->extra_fields) ? maybe_unserialize($field_info->extra_fields) : NULL;
			$multi_display_type = isset($extra_fields['multi_display_type']) ? $extra_fields['multi_display_type'] : 'select';

			$output .= aui()->select(
				array(
					'id'                => 'multi_display_type',
					'name'              => 'extra[multi_display_type]',
					'label_type'        => 'top',
					'multiple'   => false,
					'class'             => ' mw-100',
					'options'       => array(
						'select'   => __( 'Select', 'geodirectory' ),
						'checkbox'   => __( 'Checkbox', 'geodirectory' ),
						'radio'   => __( 'Radio', 'geodirectory' ),
					),
					'label'              => __('Multiselect display type','geodirectory') . geodir_help_tip( __( 'Show multiselect input as multiselect,checkbox or radio.', 'geodirectory' )),
					'value'         => $multi_display_type,
				)
			);

			return $output;
		}

        /**
         * The address setting fields.
         *
         * @since 2.0.0
         *
         * @param string $output Html output.
         * @param string $result_str Results string.
         * @param array $cf Custom fields values.
         * @param object $field_info Fields information.
         * @return string $output.
         */
		public static function address_fields( $output, $result_str, $cf, $field_info ) {
			if ( isset( $field_info->extra_fields ) && $field_info->extra_fields != '' ) {
				$address = stripslashes_deep( maybe_unserialize( $field_info->extra_fields ) );
			} else {
				$address = array();
			}

			ob_start();
			/**
			 * Called on the add custom fields settings page before the address field is output.
			 *
			 * @since 1.0.0
			 * @param array $address The address settings array.
			 * @param object $field_info Extra fields info.
			 */
			do_action('geodir_address_extra_admin_fields', $address, $field_info);

			// setup address line 2 if not set
			if(!isset($address['show_street2'])){
				$address['show_street2'] = 0;
			}
			if(!isset($address['street2_lable'])){
				$address['street2_lable'] = __('Address line 2 (optional)', 'geodirectory');
			}

			echo aui()->input(
				array(
					'id'                => 'show_street2',
					'name'              => 'extra[show_street2]',
					'label_type'        => 'horizontal',
					'label_col'        => '4',
					'label'              => __('Display Address line 2','geodirectory') ,
					'type'              => 'checkbox',
					'checked' => $address['show_street2'],
					'value' => '1',
					'switch'    => 'md',
					'label_force_left'  => true,
					'wrap_class' => geodir_advanced_toggle_class(),
					'help_text' => geodir_help_tip( __( 'Select if you want to show address line 2 field in address section.', 'geodirectory' ))
				)
			);

			echo aui()->input(
				array(
					'id'                => 'street2_lable',
					'name'              => 'extra[street2_lable]',
					'label_type'        => 'top',
					'label'              => __('Address line 2 label','geodirectory') . geodir_help_tip( __( 'Enter Address line 2 field label in address section.', 'geodirectory' )),
					'type'              => 'text',
					'wrap_class' => geodir_advanced_toggle_class(),
					'value' => $address['street2_lable'],
					'element_require'   => '[%show_street2%:checked]'

				)
			);

			echo aui()->input(
				array(
					'id'                => 'show_zip',
					'name'              => 'extra[show_zip]',
					'label_type'        => 'horizontal',
					'label_col'         => '4',
					'label'             => __( 'Display zip/post code', 'geodirectory' ) ,
					'type'              => 'checkbox',
					'checked'           => ( isset( $address['show_zip'] ) ? $address['show_zip'] : '' ),
					'value'             => '1',
					'switch'            => 'md',
					'label_force_left'  => true,
					'wrap_class'        => geodir_advanced_toggle_class(),
					'help_text'         => geodir_help_tip( __( 'Select if you want to show zip/post code field in address section.', 'geodirectory' ))
				)
			);

			echo aui()->input(
				array(
					'id'                => 'zip_required',
					'name'              => 'extra[zip_required]',
					'label_type'        => 'horizontal',
					'label_col'        => '4',
					'label'              => __('Make zip code required','geodirectory') ,
					'type'              => 'checkbox',
					'checked' => isset($address['zip_required']) ? $address['zip_required'] : '',
					'value' => '1',
					'switch'    => 'md',
					'label_force_left'  => true,
					'wrap_class' => geodir_advanced_toggle_class(),
					'element_require'   => '[%show_zip%:checked]',
					'help_text' => geodir_help_tip( __( 'Tick to set zip/post code field as required. Some countries do not use ZIP codes, please only enable if your directory is limited to countries that do.', 'geodirectory' ))
				)
			);

			echo aui()->input(
				array(
					'id'                => 'zip_lable',
					'name'              => 'extra[zip_lable]',
					'label_type'        => 'top',
					'label'              => __('Zip/Post code label','geodirectory') . geodir_help_tip( __( 'Enter zip/post code field label in address section.', 'geodirectory' )),
					'type'              => 'text',
					'wrap_class' => geodir_advanced_toggle_class(),
					'value' => isset($address['zip_lable']) ? $address['zip_lable'] : '',
					'element_require'   => '[%show_zip%:checked]'

				)
			);

			echo aui()->input(
				array(
					'id'                => 'map_lable',
					'name'              => 'extra[map_lable]',
					'label_type'        => 'top',
					'label'              => __('Map button label','geodirectory') . geodir_help_tip( __( 'Enter text for `set address on map` button in address section.', 'geodirectory' )),
					'type'              => 'text',
					'wrap_class' => geodir_advanced_toggle_class(),
					'value' => isset($address['map_lable']) ? $address['map_lable'] : '',
//					'element_require'   => '[%show_zip%:checked]'
				)
			);

			echo aui()->input(
				array(
					'id'                => 'show_mapzoom',
					'name'              => 'extra[show_mapzoom]',
					'label_type'        => 'horizontal',
					'label_col'        => '4',
					'label'              => __('Use user zoom level','geodirectory') ,
					'type'              => 'checkbox',
					'checked' => isset($address['show_mapzoom']) ? $address['show_mapzoom'] : '',
					'value' => '1',
					'switch'    => 'md',
					'label_force_left'  => true,
					'wrap_class' => geodir_advanced_toggle_class(),
//					'element_require'   => '[%show_zip%:checked]',
					'help_text' => geodir_help_tip( __( 'Do you want to use the user defined map zoom level from the add listing page?', 'geodirectory' ))
				)
			);

			echo aui()->input(
				array(
					'id'                => 'show_mapview',
					'name'              => 'extra[show_mapview]',
					'label_type'        => 'horizontal',
					'label_col'        => '4',
					'label'              => __('Display map view','geodirectory') ,
					'type'              => 'checkbox',
					'checked' => isset($address['show_mapview']) ? $address['show_mapview'] : '',
					'value' => '1',
					'switch'    => 'md',
					'label_force_left'  => true,
					'wrap_class' => geodir_advanced_toggle_class(),
//					'element_require'   => '[%show_zip%:checked]',
					'help_text' => geodir_help_tip( __( 'Select if you want to show `set default map` options in address section. ( Satellite Map, Hybrid Map, Terrain Map)', 'geodirectory' ))
				)
			);

			echo aui()->input(
				array(
					'id'                => 'mapview_lable',
					'name'              => 'extra[mapview_lable]',
					'label_type'        => 'top',
					'label'              => __('Map view label','geodirectory') . geodir_help_tip( __( 'Enter mapview field label in address section.', 'geodirectory' )),
					'type'              => 'text',
					'wrap_class' => geodir_advanced_toggle_class(),
					'value' => isset($address['mapview_lable']) ? $address['mapview_lable'] : '',
					'element_require'   => '[%show_mapview%:checked]'
				)
			);

			echo aui()->input(
				array(
					'id'                => 'show_latlng',
					'name'              => 'extra[show_latlng]',
					'label_type'        => 'horizontal',
					'label_col'        => '4',
					'label'              => __('Show latitude and longitude','geodirectory') ,
					'type'              => 'checkbox',
					'checked' => isset($address['show_latlng']) ? $address['show_latlng'] : '',
					'value' => '1',
					'switch'    => 'md',
					'label_force_left'  => true,
					'wrap_class' => geodir_advanced_toggle_class(),
//					'element_require'   => '[%show_zip%:checked]',
					'help_text' => geodir_help_tip( __( 'This will show/hide the longitude fields in the address section add listing form.', 'geodirectory' ))
				)
			);
			?>

			<input type="hidden" name="extra[show_map]" value="1" />

			<?php

			$html = ob_get_clean();
			return $output.$html;
		}

        /**
         * The category display_type setting.
         *
         * @since 2.0.0
         *
         * @param string $output Html output.
         * @param string $result_str Results string.
         * @param array $cf Custom fields values
         * @param object $field Extra fields information.
         * @return string $output.
         */
		public static function category_input_type( $output, $result_str, $cf, $field ) {

			if ( $field->htmlvar_name == 'post_category' ) {

				$extra = maybe_unserialize( $field->extra_fields );

				if ( is_array( $extra ) && ! empty( $extra['cat_display_type'] ) ) {
					$cat_display_type = $extra['cat_display_type'];
				} else {
					$cat_display_type = 'select';
				}

				$output .= aui()->select(
					array(
						'id'                => 'cat_display_type',
						'name'              => 'extra[cat_display_type]',
						'label_type'        => 'top',
						'multiple'   => false,
						'class'             => ' mw-100',
						'options'       => array(
							'select'   => __( 'Select', 'geodirectory' ),
							'multiselect'   => __( 'Multiselect', 'geodirectory' ),
							'checkbox'   => __( 'Checkbox', 'geodirectory' ),
							'radio'   => __( 'Radio', 'geodirectory' ),
						),
						'wrap_class' => geodir_advanced_toggle_class(),
						'label'              => __('Category display type','geodirectory') . geodir_help_tip( __( 'Show categories list as select, multiselect, checkbox or radio', 'geodirectory' )),
						'value'         => $cat_display_type,
					)
				);

			}

			return $output;
		}
		/**
		 * The no of tags setting.
		 *
		 * @since 2.1.0.7
		 *
		 * @param string $output Html output.
		 * @param string $result_str Results string.
		 * @param array  $cf Custom fields values.
		 * @param object $field Extra fields information.
		 * @return string $output.
		 */
		public static function no_of_tags_input_type( $output, $result_str, $cf, $field ) {

			if ( $field->htmlvar_name == 'post_tags' ) {
				$extra = maybe_unserialize( $field->extra_fields );

				if ( geodir_design_style() ) {
					// Disable New Tags
					$output .= aui()->input(
						array(
							'id'               => 'disable_new_tags',
							'type'             => 'checkbox',
							'name'             => 'extra[disable_new_tags]',
							'label_type'       => 'horizontal',
							'label_col'        => '4',
							'label'            => __( 'Disable New Tags', 'geodirectory' ) ,
							'checked'          => ( is_array( $extra ) && ! empty( $extra['disable_new_tags'] ) ? 1 : 0 ),
							'value'            => '1',
							'switch'           => 'md',
							'label_force_left' => true,
							'wrap_class'       => geodir_advanced_toggle_class(),
							'help_text'        => geodir_help_tip( __( 'Disable create a new tags dynamically from frontend users.', 'geodirectory' ) )
						)
					);
				}

				// Spell Check
				$output .= aui()->input(
					array(
						'id'               => 'spellcheck',
						'type'             => 'checkbox',
						'name'             => 'extra[spellcheck]',
						'label_type'       => 'horizontal',
						'label_col'        => '4',
						'label'            => __( 'Spell Check', 'geodirectory' ) ,
						'checked'          => ( is_array( $extra ) && ! empty( $extra['spellcheck'] ) ? 1 : 0 ),
						'value'            => '1',
						'switch'           => 'md',
						'label_force_left' => true,
						'wrap_class'       => geodir_advanced_toggle_class(),
						'help_text'        => geodir_help_tip( __( 'Enable spell check for the new tag.', 'geodirectory' ) )
					)
				);

				if ( is_array( $extra ) && ! empty( $extra['no_of_tag'] ) ) {
					$no_of_tag = $extra['no_of_tag'];
				} else {
					$no_of_tag = '';
				}
				$output .= aui()->input(
					array(
						'id'                => 'no_of_tag',
						'name'              => 'extra[no_of_tag]',
						'label_type'        => 'top',
						'label'              => __('Number of allowed tags','geodirectory') . geodir_help_tip( __( 'Enter number of allowed tags', 'geodirectory' ) ),
						'type'              => 'number',
						'value' => $no_of_tag,
						'wrap_class' => geodir_advanced_toggle_class(),
						'extra_attributes'  => array(
							'step'  => "1",
							'min'   => "0"
						)
//					'placeholder' =>  $field->field_type == 'email' ? __( 'info@mysite.com', 'geodirectory' ) : ''
					)
				);
			}

			return $output;
		}

        /**
         * Add HTML5 validation pattern fields.
         *
         * @since 2.0.0
         *
         * @param string $output Html output.
         * @param string $result_str Result string.
         * @param array $cf Custom fields values.
         * @param object $field_info Extra fields information.
         * @return string $output.
         */
		public static function validation_pattern( $output, $result_str, $cf, $field_info ) {
			ob_start();

			$value = '';
			if ( isset( $field_info->validation_pattern ) ) {
				$value = esc_attr( $field_info->validation_pattern );
			} elseif ( isset( $cf['defaults']['validation_pattern'] ) && $cf['defaults']['validation_pattern'] ) {
				$value = esc_attr( $cf['defaults']['validation_pattern'] );
			}
			echo aui()->input(
				array(
					'id'         => 'validation_pattern',
					'name'       => 'validation_pattern',
					'label_type' => 'top',
					'label'      => __('Validation Pattern','geodirectory') . geodir_help_tip( __( 'Enter regex expression for HTML5 pattern validation.', 'geodirectory' )),
					'type'       => 'text',
					'wrap_class' => geodir_advanced_toggle_class(),
					'value'      => addslashes_gpc( $value ), // Keep slashes
				)
			);

			$value = '';
			if ( isset( $field_info->validation_msg ) ) {
				$value = esc_attr( $field_info->validation_msg );
			} elseif ( isset( $cf['defaults']['validation_msg'] ) && $cf['defaults']['validation_msg'] ) {
				$value = esc_attr( $cf['defaults']['validation_msg'] );
			}

			echo aui()->input(
				array(
					'id'                => 'validation_msg',
					'name'              => 'validation_msg',
					'label_type'        => 'top',
					'label'              => __('Validation Message','geodirectory') . geodir_help_tip( __( 'Enter a extra validation message to show to the user if validation fails.', 'geodirectory' )),
					'type'              => 'text',
					'wrap_class' => geodir_advanced_toggle_class(),
					'value' => $value,
				)
			);

			$output = ob_get_clean();

			return $output;
		}

        /**
         * Advanced WYSIWYG editor option.
         *
         * @since 2.0.0
         *
         * @param string $output Html output.
         * @param string $result_str Results string.
         * @param array $cf Custom fields values.
         * @param object $field_info Extra fields information.
         * @return string $output.
         */
		public static function advanced_editor( $output, $result_str, $cf, $field_info ) {

			$value = '';
			$extra = ! empty( $field_info->extra_fields ) ? maybe_unserialize( $field_info->extra_fields ) : array();
			if ( is_array( $extra ) && isset( $extra['advanced_editor'] ) ) {
				$value = absint( $extra['advanced_editor'] );
			} elseif ( isset( $cf['defaults']['advanced_editor'] ) && $cf['defaults']['advanced_editor'] ) {
				$value = absint( $cf['defaults']['advanced_editor'] );
			}
			$output .= aui()->input(
				array(
					'id'                => 'advanced_editor',
					'name'              => 'extra[advanced_editor]',
					'label_type'        => 'horizontal',
					'label_col'        => '4',
					'label'              => __('Advanced editor','geodirectory') ,
					'type'              => 'checkbox',
					'checked' => $value,
					'value' => '1',
					'switch'    => 'md',
					'label_force_left'  => true,
					'wrap_class' => geodir_advanced_toggle_class(),
					'help_text' => geodir_help_tip( __( 'Select if you want to show the advanced editor on add listing page.', 'geodirectory' ))
				)
			);

			return $output;
		}

        /**
         * Add a data type selector fro text inputs.
         *
         * @since 2.0.0
         *
         * @param string $output Html output.
         * @param string $result_str Results string.
         * @param array $cf Custom fields value.
         * @param object $field_info Extra fields information.
         * @return string $output.
         */
		public static function data_type( $output, $result_str, $cf, $field_info ) {
			ob_start();

			$dt_value = '';
			if ( isset( $field_info->data_type ) ) {
				$dt_value = esc_attr( $field_info->data_type );
			} elseif ( isset( $cf['defaults']['data_type'] ) && $cf['defaults']['data_type'] ) {
				$dt_value = $cf['defaults']['data_type'];
			}

			// fix some values
			if ( $dt_value == 'VARCHAR' ) {
				$dt_value = 'XVARCHAR';
			} elseif ( $dt_value == 'FLOAT' ) {
				$dt_value = 'DECIMAL';
			}

			echo aui()->select(
				array(
					'id'                => "data_type",
					'name'              => "data_type",
					'label_type'        => 'top',
					'multiple'   => false,
					'wrap_class' => geodir_advanced_toggle_class(),
					'class'             => 'mw-100',
					'options'       => array(
						'XVARCHAR'   => __( 'CHARACTER', 'geodirectory' ),
						'INT'   => __( 'NUMBER', 'geodirectory' ),
						'DECIMAL'   => __( 'DECIMAL', 'geodirectory' ),
					),
					'label'              => __('Data Type','geodirectory').geodir_help_tip( __( 'Select the data type for the field. This can affect things like search filtering.', 'geodirectory' )),
					'value'         => $dt_value,
					'extra_attributes'  => array(
						'onchange'  => "javascript:gd_data_type_changed(this, '$result_str');"
					)
				)
			);

			$output = ob_get_clean();

			return $output;
		}

		/**
         * Input to enable embed option for videos, images, tweets, audio, and other content.
         *
         * @since 2.0.0
         *
         * @param string $output Html output.
         * @param string $result_str Results string.
         * @param array $cf Custom fields values.
         * @param object $field_info Extra fields information.
         * @return string $output.
         */
		public static function embed_input( $output, $result_str, $cf, $field_info ) {
			ob_start();
			if ( ! empty( $field_info->htmlvar_name ) && $field_info->htmlvar_name == 'video' ) {
				?>
				<input type="hidden" name="extra[embed]" value="1" />
				<?php
			} else {

				$extra = ! empty( $field_info->extra_fields ) ? maybe_unserialize( $field_info->extra_fields ) : array();

				if ( is_array( $extra ) && isset( $extra['embed'] ) ) {
					$value = absint( $extra['embed'] );
				} elseif ( isset( $cf['defaults']['embed'] ) && $cf['defaults']['embed'] ) {
					$value = absint( $cf['defaults']['embed'] );
				} else {
					$value = 0;
				}
				echo aui()->input(
					array(
						'id'                => 'embed',
						'name'              => 'extra[embed]',
						'label_type'        => 'horizontal',
						'label_col'        => '4',
						'label'              => __('Embed Media URLs','geodirectory') ,
						'type'              => 'checkbox',
						'checked' => $value,
						'value' => '1',
						'switch'    => 'md',
						'label_force_left'  => true,
						'wrap_class' => geodir_advanced_toggle_class(),
						'help_text' => geodir_help_tip( __( 'Tick to allow embed videos, images, tweets, audio, and other content.', 'geodirectory' ))
					)
				);

			}
			$output .= ob_get_clean();

			return $output;
		}
	}

endif;
