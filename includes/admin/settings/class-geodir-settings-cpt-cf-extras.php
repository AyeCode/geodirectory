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
			add_filter('geodir_cfa_extra_fields_multiselect', array( $this, 'option_values'),10,4);
			add_filter('geodir_cfa_extra_fields_select',array( $this, 'option_values'),10,4);
			add_filter('geodir_cfa_extra_fields_radio',array( $this, 'option_values'),10,4);

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
			add_filter( 'geodir_cfa_is_required_address', array($this,'is_required'), 10, 4 );

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
         * @param object $field_info Price fiels information.
         * @return string $output.
         */
		public static function price_fields($output,$result_str,$cf,$field_info){
			ob_start();

			$extra_fields = isset($field_info->extra_fields) && $field_info->extra_fields != '' ? maybe_unserialize($field_info->extra_fields) : '';
			$radio_id = (isset($field_info->htmlvar_name)) ? $field_info->htmlvar_name : rand(5, 500);

			$value = '';
			if ($extra_fields && isset($extra_fields['is_price'])) {
				$value = esc_attr($extra_fields['is_price']);
			}elseif(isset($cf['defaults']['extra_fields']['is_price']) && $cf['defaults']['extra_fields']['is_price']){
				$value = esc_attr($cf['defaults']['extra_fields']['is_price']);
			}


			$show_price_extra = ($value==1) ? 1 : 0;

			$show_price = (isset($field_info->data_type) && ($field_info->data_type=='INT' || $field_info->data_type=='FLOAT')) ? 1 : 0;
			?>
			<p class="gdcf-price-extra-set" <?php if(!$show_price){ echo "style='display:none;'";}?>  data-gdat-display-switch-set="gdat-extra_is_price" data-setting="is_price">
				<label for="is_price" class="dd-setting-name">
					<?php
					echo geodir_help_tip( __( 'Select if this field should be displayed as a price value.', 'geodirectory' ));
					_e('Display as price', 'geodirectory'); ?>
					<input type="hidden" name="extra[is_price]" value="0" />
					<input type="checkbox" name="extra[is_price]" value="1" <?php checked( $value, 1, true );?> onclick="gd_show_hide_radio(this,'show','gdat-extra_is_price');" />
				</label>
			</p>

			<?php

			$value = '';
			if ($extra_fields && isset($extra_fields['thousand_separator'])) {
				$value = esc_attr($extra_fields['thousand_separator']);
			}elseif(isset($cf['defaults']['extra_fields']['thousand_separator']) && $cf['defaults']['extra_fields']['thousand_separator']){
				$value = esc_attr($cf['defaults']['extra_fields']['thousand_separator']);
			}
			?>
			<p class="gd-advanced-setting gdat-extra_is_price" data-setting="thousand_separator">
				<label for="thousand_separator" class="dd-setting-name">
					<?php
					echo geodir_help_tip( __( 'Select the thousand separator.', 'geodirectory' ));
					_e('Thousand separator', 'geodirectory');?>
					<select name="extra[thousand_separator]" id="thousand_separator">
						<option value="comma" <?php selected(true, $value == 'comma');?>><?php _e(', (comma)', 'geodirectory'); ?></option>
						<option value="slash" <?php selected(true, $value == "slash");?>><?php _e('\ (slash)', 'geodirectory'); ?></option>
						<option value="period" <?php selected(true, $value == 'period');?>><?php _e('. (period)', 'geodirectory'); ?></option>
						<option value="space" <?php selected(true, $value == 'space');?>><?php _e(' (space)', 'geodirectory'); ?></option>
						<option value="none" <?php selected(true, $value == 'none');?>><?php _e('(none)', 'geodirectory'); ?></option>
					</select>
				</label>
			</p>


			<?php

			$value = '';
			if ($extra_fields && isset($extra_fields['decimal_separator'])) {
				$value = esc_attr($extra_fields['decimal_separator']);
			}elseif(isset($cf['defaults']['extra_fields']['decimal_separator']) && $cf['defaults']['extra_fields']['decimal_separator']){
				$value = esc_attr($cf['defaults']['extra_fields']['decimal_separator']);
			}
			?>
			<p class="gd-advanced-setting gdat-extra_is_price" data-setting="decimal_separator">
				<label for="decimal_separator" class="dd-setting-name">
					<?php
					echo geodir_help_tip( __( 'Select the decimal separator.', 'geodirectory' ));
					_e('Decimal separator:', 'geodirectory');?>
					<select name="extra[decimal_separator]" id="decimal_separator">
						<option value="period" <?php selected(true, $value == 'period');?>><?php _e('. (period)', 'geodirectory'); ?></option>
						<option value="comma" <?php selected(true, $value == "comma");?>><?php _e(', (comma)', 'geodirectory'); ?></option>
					</select>
				</label>
			</p>

			<?php

			$value = '';
			if ($extra_fields && isset($extra_fields['decimal_display'])) {
				$value = esc_attr($extra_fields['decimal_display']);
			}elseif(isset($cf['defaults']['extra_fields']['decimal_display']) && $cf['defaults']['extra_fields']['decimal_display']){
				$value = esc_attr($cf['defaults']['extra_fields']['decimal_display']);
			}
			?>
			<p class="gd-advanced-setting gdat-extra_is_price" data-setting="decimal_display">
				<label for="decimal_display" class="dd-setting-name">
					<?php
					echo geodir_help_tip( __( 'Select how the decimal is displayed.', 'geodirectory' ));
					_e('Decimal display:', 'geodirectory');?>
					<select name="extra[decimal_display]" id="decimal_display">
						<option value="if" <?php selected(true, $value == 'if');?>><?php _e('If used (not .00)', 'geodirectory'); ?></option>
						<option value="allways" <?php selected(true, $value == "allways");?>><?php _e('Always (.00)', 'geodirectory'); ?></option>
					</select>
				</label>
			</p>

			<?php

			$value = '';
			if ($extra_fields && isset($extra_fields['currency_symbol'])) {
				$value = esc_attr($extra_fields['currency_symbol']);
			}elseif(isset($cf['defaults']['extra_fields']['currency_symbol']) && $cf['defaults']['extra_fields']['currency_symbol']){
				$value = esc_attr($cf['defaults']['extra_fields']['currency_symbol']);
			}
			?>
			<p class="gdat-extra_is_price" data-setting="currency_symbol">
				<label for="currency_symbol" class="dd-setting-name">
					<?php
					echo geodir_help_tip( __( 'Select the currency symbol.', 'geodirectory' ));
					_e('Currency symbol:', 'geodirectory');?>
					<input type="text" name="extra[currency_symbol]" id="currency_symbol" value="<?php echo esc_attr($value); ?>"/>
				</label>
			</p>

			<?php

			$value = '';
			if ($extra_fields && isset($extra_fields['currency_symbol_placement'])) {
				$value = esc_attr($extra_fields['currency_symbol_placement']);
			}elseif(isset($cf['defaults']['extra_fields']['currency_symbol_placement']) && $cf['defaults']['extra_fields']['currency_symbol_placement']){
				$value = esc_attr($cf['defaults']['extra_fields']['currency_symbol_placement']);
			}
			?>
			<p class="gd-advanced-setting gdat-extra_is_price" data-setting="currency_symbol_placement">
				<label for="currency_symbol_placement" class="dd-setting-name">
					<?php
					echo geodir_help_tip( __( 'Select the currency symbol placement.', 'geodirectory' ));
					_e('Currency symbol placement:', 'geodirectory');?>
					<select name="extra[currency_symbol_placement]" id="currency_symbol_placement">
						<option value="left" <?php selected(true, $value == 'left');?>><?php _e('Left', 'geodirectory'); ?></option>
						<option value="right" <?php selected(true, $value == "right");?>><?php _e('Right', 'geodirectory'); ?></option>
					</select>
				</label>
			</p>


			<?php

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
		public static function file_types($output,$result_str,$cf,$field_info){
			ob_start();
			$allowed_file_types = geodir_allowed_mime_types();

			$extra_fields = isset($field_info->extra_fields) && $field_info->extra_fields != '' ? maybe_unserialize($field_info->extra_fields) : '';
			$gd_file_types = !empty($extra_fields) && !empty($extra_fields['gd_file_types']) ? maybe_unserialize($extra_fields['gd_file_types']) : array('*');
			?>
			<p data-setting="gd_file_types">
				<label for="gd_file_types" class="dd-setting-name">
					<?php
					echo geodir_help_tip( __( 'Select file types to allowed for file uploading. (Select multiple file types by holding down "Ctrl" key.)', 'geodirectory' ));
					_e('Allowed file types', 'geodirectory'); ?>
					<select name="extra[gd_file_types][]" id="gd_file_types" multiple="multiple" style="height:100px;width:90%;">
						<option value="*" <?php selected(true, in_array('*', $gd_file_types));?>><?php _e('All types', 'geodirectory') ;?></option>
						<?php foreach ( $allowed_file_types as $format => $types ) { ?>
							<optgroup label="<?php echo esc_attr( wp_sprintf(__('%s formats', 'geodirectory'), __($format, 'geodirectory') ) ) ;?>">
								<?php foreach ( $types as $ext => $type ) { ?>
									<option value="<?php echo esc_attr($ext) ;?>" <?php selected(true, in_array($ext, $gd_file_types));?>><?php echo '.' . $ext ;?></option>
								<?php } ?>
							</optgroup>
						<?php } ?>
					</select>
				</label>
			</p>

			<?php

			$output .= ob_get_clean();
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

			ob_start();
			?>
			<p data-setting="file_limit">
				<label for="gd_file_limit" class="dd-setting-name">
					<?php
					echo geodir_help_tip( __( 'Select the number of files that can be uploaded, Leave blank or 0 to allow unlimited files.', 'geodirectory' ) );
					_e( 'File upload limit', 'geodirectory' ); ?>
					<input type="number" name="extra[file_limit]" id="gd_file_limit" value="<?php echo $gd_file_limit; ?>" step="1" min="0">
				</label>
			</p>
			<?php
			$output .= ob_get_clean();

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
			if (isset($field_info->extra_fields) && $field_info->extra_fields != '') {
				$extra = unserialize($field_info->extra_fields);
			}
			?>
			<p data-setting="date_format">
				<label for="date_format" class="dd-setting-name">
					<?php
					echo geodir_help_tip( __( 'Select the date format.', 'geodirectory' ));
					_e('Date Format', 'geodirectory');

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
					?>
					<select name="extra[date_format]" id="date_format">
						<?php
						foreach($date_formats as $format){
							$selected = '';
							if(!empty($extra) && esc_attr($extra['date_format'])==$format){
								$selected = "selected='selected'";
							}
							echo "<option $selected value='$format'>$format       (".date_i18n( $format, time()).")</option>";
						}
						?>
					</select>
				</label>
			</p>
			<?php

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

			ob_start();
			?>
			<p data-setting="date_range">
				<label for="date_range" class="dd-setting-name">
					<?php
					echo geodir_help_tip( __( 'Set the date range, eg: 1920:2020 or for current dates: c-100:c+5', 'geodirectory' ));
					_e('Date Range', 'geodirectory');
					?>
					<input type="text" name="extra[date_range]" id="date_range" value="<?php echo $value;?>">
				</label>
			</p>
			<?php
			$output .= ob_get_clean();

			return $output;
		}

        /**
         * Multiple input option values.
         *
         * @since 2.0.0
         *
         * @param string $output Html output.
         * @param string $result_str Results string.
         * @param array $cf Input custom fields.
         * @param object $field_info Fields information.
         * @return string $output.
         */
		public static function option_values($output,$result_str,$cf,$field_info){

			ob_start();

			$value = '';
			if (isset($field_info->option_values)) {
				$value = esc_attr($field_info->option_values);
			}elseif(isset($cf['defaults']['option_values']) && $cf['defaults']['option_values']){
				$value = esc_attr($cf['defaults']['option_values']);
			}

			$field_type = isset($field_info->field_type) ? $field_info->field_type : '';
			?>
			<p data-setting="option_values">
				<label for="option_values" class="dd-setting-name">
					<?php
					$option_values_tool_top = __( 'Option Values should be separated by comma.', 'geodirectory' ).'<br/>';
					$option_values_tool_top .= __( 'If using for a "tick filter" place a / and then either a 1 for true or 0 for false', 'geodirectory' ).'<br/>';
					$option_values_tool_top .= __( 'eg: "No Dogs Allowed/0,Dogs Allowed/1" (Select only, not multiselect)', 'geodirectory' ).'<br/>';
					if ($field_type == 'multiselect' || $field_type == 'select') {
						$option_values_tool_top .= '<small><span>'.__( '- If using OPTGROUP tag to grouping options, use "{optgroup}OPTGROUP-LABEL|OPTION-1,OPTION-2{/optgroup}"', 'geodirectory' ).'</span>';
						$option_values_tool_top .= '<span>'.__( '- If using OPTGROUP tag to grouping options, use "{optgroup}OPTGROUP-LABEL|OPTION-1,OPTION-2{/optgroup}"', 'geodirectory' ).'</span></small>';
					}
					echo geodir_help_tip( $option_values_tool_top );
					_e('Option Values:', 'geodirectory'); ?>
					<input type="text" name="option_values" id="option_values" value="<?php echo $value;?>"/>
				</label>
			</p>
			<?php

			$output .= ob_get_clean();
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
			ob_start();

			$extra_fields = !empty($field_info->extra_fields) ? maybe_unserialize($field_info->extra_fields) : NULL;
			$multi_display_type = isset($extra_fields['multi_display_type']) ? $extra_fields['multi_display_type'] : 'select';
			?>
			<p class="gd-advanced-setting" data-setting="multi_display_type">
				<label for="multi_display_type" class="dd-setting-name">
					<?php
					echo geodir_help_tip( __( 'Show multiselect input as multiselect,checkbox or radio.', 'geodirectory' ));
					_e('Multiselect display type', 'geodirectory'); ?>
					<select name="extra[multi_display_type]" id="multi_display_type">
						<option <?php selected($multi_display_type,'select');?> value="select"><?php _e('Select', 'geodirectory');?></option>
						<option <?php selected($multi_display_type,'checkbox');?> value="checkbox"><?php _e('Checkbox', 'geodirectory');?></option>
						<option <?php selected($multi_display_type,'radio');?> value="radio"><?php _e('Radio', 'geodirectory');?></option>
					</select>
				</label>
			</p>
			<?php

			$output .= ob_get_clean();
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
		public static function address_fields($output,$result_str,$cf,$field_info){

			ob_start();
			if (isset($field_info->extra_fields) && $field_info->extra_fields != '') {
				$address = stripslashes_deep(unserialize($field_info->extra_fields));
			}

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

			?>

			<p class="gd-advanced-setting" data-setting="show_street2">
				<label for="show_zip" class="dd-setting-name">
					<?php
					echo geodir_help_tip( __( 'Select if you want to show address line 2 field in address section.', 'geodirectory' ));
					_e('Display Address line 2', 'geodirectory'); ?>
					<input type="hidden" name="extra[show_street2]" value="0" />
					<input type="checkbox" name="extra[show_street2]" value="1" <?php checked( $address['show_street2'], 1, true );?> onclick="gd_show_hide_radio(this,'show','cf-street2-lable');" />
				</label>
			</p>

			<p  class="cf-street2-lable gd-advanced-setting"  <?php if ((isset($address['show_street2']) && !$address['show_street2']) || !isset($address['show_street2'])) {echo "style='display:none;'";}?> data-setting="street2_lable">
				<label for="street2_lable" class="dd-setting-name">
					<?php
					echo geodir_help_tip( __( 'Enter Address line 2 field label in address section.', 'geodirectory' ));
					_e('Address line 2 label', 'geodirectory'); ?>
					<input type="text" name="extra[street2_lable]" id="street2_lable"
					       value="<?php if (isset($address['street2_lable'])) {
						       echo esc_attr($address['street2_lable']);
					       }?>"/>
				</label>
			</p>

			<p class="gd-advanced-setting" data-setting="show_zip">
				<label for="show_zip" class="dd-setting-name">
					<?php
					echo geodir_help_tip( __( 'Select if you want to show zip/post code field in address section.', 'geodirectory' ));
					_e('Display zip/post code', 'geodirectory'); ?>
					<input type="hidden" name="extra[show_zip]" value="0" />
					<input type="checkbox" name="extra[show_zip]" value="1" <?php checked( $address['show_zip'], 1, true );?> onclick="gd_show_hide_radio(this,'show','cf-zip-lable');" />
				</label>
			</p>

			<p class="gd-advanced-setting" data-setting="zip_required" <?php if ((isset($address['show_zip']) && !$address['show_zip']) || !isset($address['show_zip'])) {echo "style='display:none;'";}?>>
				<label for="zip_required" class="dd-setting-name">
					<?php
					echo geodir_help_tip( __( 'Tick to set zip/post code field as required. Some countries do not use ZIP codes, please only enable if your directory is limited to countries that do.', 'geodirectory' ));
					_e( 'Make zip/post code mandatory?', 'geodirectory' ); ?>
					<input type="hidden" name="extra[zip_required]" value="0" />
					<input type="checkbox" name="extra[zip_required]" value="1" <?php checked( ! empty( $address['zip_required'] ), true, true );?>/>
				</label>
			</p>

			<p  class="cf-zip-lable gd-advanced-setting"  <?php if ((isset($address['show_zip']) && !$address['show_zip']) || !isset($address['show_zip'])) {echo "style='display:none;'";}?> data-setting="zip_lable">
				<label for="zip_lable" class="dd-setting-name">
					<?php
					echo geodir_help_tip( __( 'Enter zip/post code field label in address section.', 'geodirectory' ));
					_e('Zip/Post code label', 'geodirectory'); ?>
					<input type="text" name="extra[zip_lable]" id="zip_lable"
					       value="<?php if (isset($address['zip_lable'])) {
						       echo esc_attr($address['zip_lable']);
					       }?>"/>
				</label>
			</p>

			<input type="hidden" name="extra[show_map]" value="1" />
			<p class="gd-advanced-setting" data-setting="map_lable">
				<label for="map_lable" class="dd-setting-name">
					<?php
					echo geodir_help_tip( __( 'Enter text for `set address on map` button in address section.', 'geodirectory' ));
					_e('Map button label', 'geodirectory'); ?>
					<input type="text" name="extra[map_lable]" id="map_lable"
					       value="<?php if (isset($address['map_lable'])) {
						       echo esc_attr($address['map_lable']);
					       }?>"/>
				</label>
			</p>

			<p class="gd-advanced-setting" data-setting="show_mapzoom">
				<label for="show_mapzoom" class="dd-setting-name">
					<?php
					echo geodir_help_tip( __( 'Do you want to use the user defined map zoom level from the add listing page?', 'geodirectory' ));
					_e('Use user zoom level', 'geodirectory'); ?>
					<input type="hidden" name="extra[show_mapzoom]" value="0" />
					<input type="checkbox" name="extra[show_mapzoom]" value="1" <?php if(isset($address['show_mapzoom'])){checked( $address['show_mapzoom'], 1, true );}?> />
				</label>
			</p>

			<p class="gd-advanced-setting" data-setting="show_mapview">
				<label for="show_mapview" class="dd-setting-name">
					<?php
					echo geodir_help_tip( __( 'Select if you want to show `set default map` options in address section. ( Satellite Map, Hybrid Map, Terrain Map)', 'geodirectory' ));
					_e('Display map view', 'geodirectory'); ?>
					<input type="hidden" name="extra[show_mapview]" value="0" />
					<input type="checkbox" name="extra[show_mapview]" value="1" <?php checked( $address['show_mapview'], 1, true );?> />
				</label>
			</p>


			<p class="gd-advanced-setting" data-setting="mapview_lable">
				<label for="mapview_lable" class="dd-setting-name">
					<?php
					echo geodir_help_tip( __( 'Enter mapview field label in address section.', 'geodirectory' ));
					_e('Map view label', 'geodirectory'); ?>
					<input type="text" name="extra[mapview_lable]" id="mapview_lable"
					       value="<?php if (isset($address['mapview_lable'])) {
						       echo esc_attr($address['mapview_lable']);
					       }?>"/>
				</label>
			</p>

			<p class="gd-advanced-setting" data-setting="show_latlng">
				<label for="show_latlng" class="dd-setting-name">
					<?php
					echo geodir_help_tip( __( 'This will show/hide the longitude fields in the address section add listing form.', 'geodirectory' ));
					_e('Show latitude and longitude', 'geodirectory'); ?>
					<input type="hidden" name="extra[show_latlng]" value="0" />
					<input type="checkbox" name="extra[show_latlng]" value="1" <?php checked( $address['show_latlng'], 1, true );?> />
				</label>
			</p>
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
				ob_start();
				//print_r($field->extra_fields);echo '###';
				$extra = maybe_unserialize( $field->extra_fields );

				if ( is_array( $extra ) && ! empty( $extra['cat_display_type'] ) ) {
					$cat_display_type = $extra['cat_display_type'];
				} else {
					$cat_display_type = 'select';
				}

				?>
				<p class="gd-advanced-setting" data-setting="cat_display_type">
					<label for="cat_display_type" class="dd-setting-name">
						<?php
						echo geodir_help_tip( __( 'Show categories list as select, multiselect, checkbox or radio', 'geodirectory' ));
						_e( 'Category display type', 'geodirectory' ); ?>
						<select name="extra[cat_display_type]" id="cat_display_type">
							<option <?php if ( $cat_display_type == 'select' ) {
								echo 'selected="selected"';
							} ?> value="select"><?php _e( 'Select', 'geodirectory' ); ?></option>
							<option <?php if ( $cat_display_type == 'multiselect' ) {
								echo 'selected="selected"';
							} ?> value="multiselect"><?php _e( 'Multiselect', 'geodirectory' ); ?></option>
							<option <?php if ( $cat_display_type == 'checkbox' ) {
								echo 'selected="selected"';
							} ?> value="checkbox"><?php _e( 'Checkbox', 'geodirectory' ); ?></option>
							<option <?php if ( $cat_display_type == 'radio' ) {
								echo 'selected="selected"';
							} ?> value="radio"><?php _e( 'Radio', 'geodirectory' ); ?></option>
						</select>
					</label>
				</p>
				<?php
				$output .= ob_get_clean();

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
			?>
			<p class="gd-advanced-setting" data-setting="validation_pattern">
				<label for="validation_pattern" class="dd-setting-name">
					<?php
					echo geodir_help_tip( __( 'Enter regex expression for HTML5 pattern validation.', 'geodirectory' ));
					_e( 'Validation Pattern:', 'geodirectory' ); ?>
					<input type="text" name="validation_pattern" id="validation_pattern" value="<?php echo $value; ?>"/>
				</label>
			</p>
			<?php
			$value = '';
			if ( isset( $field_info->validation_msg ) ) {
				$value = esc_attr( $field_info->validation_msg );
			} elseif ( isset( $cf['defaults']['validation_msg'] ) && $cf['defaults']['validation_msg'] ) {
				$value = esc_attr( $cf['defaults']['validation_msg'] );
			}
			?>
			<p class="gd-advanced-setting" data-setting="validation_msg">
				<label for="validation_msg" class="dd-setting-name">
					<?php
					echo geodir_help_tip( __( 'Enter a extra validation message to show to the user if validation fails.', 'geodirectory' ));
					_e( 'Validation Message:', 'geodirectory' ); ?>
					<input type="text" name="validation_msg" id="validation_msg" value="<?php echo $value; ?>"/>
				</label>
			</p>
			<?php

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

			ob_start();
			?>
			<p class="gd-advanced-setting" data-setting="advanced_editor">
				<label for="advanced_editor" class="dd-setting-name">
					<?php
					echo geodir_help_tip( __( 'Select if you want to show the advanced editor on add listing page.', 'geodirectory' ));
					_e( 'Show advanced editor :', 'geodirectory' );
					$extra = ! empty( $field_info->extra_fields ) ? maybe_unserialize( $field_info->extra_fields ) : array();

					$value = '';
					if ( is_array( $extra ) && isset( $extra['advanced_editor'] ) ) {
						$value = absint( $extra['advanced_editor'] );
					} elseif ( isset( $cf['defaults']['advanced_editor'] ) && $cf['defaults']['advanced_editor'] ) {
						$value = absint( $cf['defaults']['advanced_editor'] );
					}
					?>
					<input type="hidden" name="extra[advanced_editor]" value="0" />
					<input type="checkbox" name="extra[advanced_editor]" value="1" <?php checked( $value, 1, true );?> />
				</label>
			</p>
			<?php

			$output .= ob_get_clean();

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
			?>
			<p class="dd-setting-name gd-advanced-setting" data-setting="data_type">
				<label for="data_type">
                <?php
					echo geodir_help_tip(__( 'Select the data type for the field. This can affect things like search filtering.', 'geodirectory' ));
					_e( 'Field Data Type :', 'geodirectory' ); ?>
						<select name="data_type" id="data_type"
						        onchange="javascript:gd_data_type_changed(this, '<?php echo $result_str; ?>');">
							<option
								value="XVARCHAR" <?php if ( $dt_value == 'VARCHAR' ) {
								echo 'selected="selected"';
							} ?>><?php _e( 'CHARACTER', 'geodirectory' ); ?></option>
							<option
								value="INT" <?php if ( $dt_value == 'INT' ) {
								echo 'selected="selected"';
							} ?>><?php _e( 'NUMBER', 'geodirectory' ); ?></option>
							<option
								value="DECIMAL" <?php if ( $dt_value == 'FLOAT' || $dt_value == 'DECIMAL' ) {
								echo 'selected="selected"';
							} ?>><?php _e( 'DECIMAL', 'geodirectory' ); ?></option>
						</select>
				</label>
			</p>

			<?php
			$value = '';
			if ( isset( $field_info->decimal_point ) ) {
				$value = esc_attr( $field_info->decimal_point );
			} elseif ( isset( $cf['defaults']['decimal_point'] ) && $cf['defaults']['decimal_point'] ) {
				$value = $cf['defaults']['decimal_point'];
			}
			?>

			<p class="dd-setting-name decimal-point-wrapper"
			    style="<?php echo ( $dt_value == 'FLOAT' ) ? '' : 'display:none' ?>" data-setting="decimal_point">
				<label for="decimal_point">
					<?php
					echo geodir_help_tip(__( 'Decimal point to display after point.', 'geodirectory' ));
					_e( 'Select decimal point :', 'geodirectory' ); ?>
					<select name="decimal_point" id="decimal_point">
						<option value=""><?php echo _e( 'Select', 'geodirectory' ); ?></option>
						<?php for ( $i = 1; $i <= 10; $i ++ ) {
							$selected = $i == $value ? 'selected="selected"' : ''; ?>
							<option value="<?php echo $i; ?>" <?php echo $selected; ?>><?php echo $i; ?></option>
						<?php } ?>
					</select>
				</label>
			</p>
			<?php

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
				?>
				<p class="gd-advanced-setting" data-setting="embed">
					<label for="embed" class="dd-setting-name">
						<?php
						echo geodir_help_tip( __( 'Tick to allow embed videos, images, tweets, audio, and other content.', 'geodirectory' ));
						_e( 'Embed Media URLs:', 'geodirectory' );
						$extra = ! empty( $field_info->extra_fields ) ? maybe_unserialize( $field_info->extra_fields ) : array();

						if ( is_array( $extra ) && isset( $extra['embed'] ) ) {
							$value = absint( $extra['embed'] );
						} elseif ( isset( $cf['defaults']['embed'] ) && $cf['defaults']['embed'] ) {
							$value = absint( $cf['defaults']['embed'] );
						} else {
							$value = 0;
						}
						?>
						<input type="hidden" name="extra[embed]" value="0" />
						<input type="checkbox" name="extra[embed]" value="1" <?php checked( $value, 1, true );?> />
					</label>
				</p>
				<?php
			}
			$output .= ob_get_clean();

			return $output;
		}
	}

endif;