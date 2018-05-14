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

			// validation pattern
			add_filter( 'geodir_cfa_validation_pattern_text', array( $this, 'validation_pattern' ), 10, 4 );

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

			// file type input
			add_filter('geodir_cfa_extra_fields_file',array( $this,'file_types'),10,4);
			add_filter('geodir_cfa_extra_fields_file',array( $this,'file_limit'),10,4);

			// post_images
			add_filter('geodir_cfa_extra_fields_67yimages',array( $this,'file_limit'),10,4);

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
			<li class="gdcf-price-extra-set" <?php if(!$show_price){ echo "style='display:none;'";}?>  data-gdat-display-switch-set="gdat-extra_is_price">
				<label for="is_price" class="gd-cf-tooltip-wrap">
					<span
						class="gd-help-tip gd-help-tip-float-none gd-help-tip-no-margin dashicons dashicons-editor-help"
						title='<?php _e( 'Select if this field should be displayed as a price value.', 'geodirectory' ); ?>'>
                    </span>
					<?php _e('Display as price:', 'geodirectory'); ?>
				</label>
				<div class="gd-cf-input-wrap gd-switch" >

					<input type="radio" id="is_price_yes<?php echo $radio_id;?>" name="extra[is_price]" class="gdri-enabled"  value="1"
						<?php if ($value == '1') {
							echo 'checked';
						} ?>/>
					<label onclick="gd_show_hide_radio(this,'show','gdcf-price-extra');" for="is_price_yes<?php echo $radio_id;?>" class="gdcb-enable"><span><?php _e('Yes', 'geodirectory'); ?></span></label>

					<input type="radio" id="is_price_no<?php echo $radio_id;?>" name="extra[is_price]" class="gdri-disabled" value="0"
						<?php if ($value == '0' || !$value) {
							echo 'checked';
						} ?>/>
					<label onclick="gd_show_hide_radio(this,'hide','gdcf-price-extra');" for="is_price_no<?php echo $radio_id;?>" class="gdcb-disable"><span><?php _e('No', 'geodirectory'); ?></span></label>

				</div>
			</li>

			<?php

			$value = '';
			if ($extra_fields && isset($extra_fields['thousand_separator'])) {
				$value = esc_attr($extra_fields['thousand_separator']);
			}elseif(isset($cf['defaults']['extra_fields']['thousand_separator']) && $cf['defaults']['extra_fields']['thousand_separator']){
				$value = esc_attr($cf['defaults']['extra_fields']['thousand_separator']);
			}
			?>
			<li class="gd-advanced-setting gdat-extra_is_price">
				<label for="thousand_separator" class="gd-cf-tooltip-wrap">
					<span
						class="gd-help-tip gd-help-tip-float-none gd-help-tip-no-margin dashicons dashicons-editor-help"
						title='<?php _e( 'Select the thousand separator.', 'geodirectory' ); ?>'>
                    </span>
					<?php _e('Thousand separator:', 'geodirectory');?>
				</label>
				<div class="gd-cf-input-wrap">
					<select name="extra[thousand_separator]" id="thousand_separator">
						<option value="comma" <?php selected(true, $value == 'comma');?>><?php _e(', (comma)', 'geodirectory'); ?></option>
						<option value="slash" <?php selected(true, $value == "slash");?>><?php _e('\ (slash)', 'geodirectory'); ?></option>
						<option value="period" <?php selected(true, $value == 'period');?>><?php _e('. (period)', 'geodirectory'); ?></option>
						<option value="space" <?php selected(true, $value == 'space');?>><?php _e(' (space)', 'geodirectory'); ?></option>
						<option value="none" <?php selected(true, $value == 'none');?>><?php _e('(none)', 'geodirectory'); ?></option>
					</select>
				</div>
			</li>


			<?php

			$value = '';
			if ($extra_fields && isset($extra_fields['decimal_separator'])) {
				$value = esc_attr($extra_fields['decimal_separator']);
			}elseif(isset($cf['defaults']['extra_fields']['decimal_separator']) && $cf['defaults']['extra_fields']['decimal_separator']){
				$value = esc_attr($cf['defaults']['extra_fields']['decimal_separator']);
			}
			?>
			<li class="gd-advanced-setting gdat-extra_is_price" >
				<label for="decimal_separator" class="gd-cf-tooltip-wrap">
					<span
						class="gd-help-tip gd-help-tip-float-none gd-help-tip-no-margin dashicons dashicons-editor-help"
						title='<?php _e( 'Select the decimal separator.', 'geodirectory' ); ?>'>
                    </span>
					<?php _e('Decimal separator:', 'geodirectory');?>
				</label>
				<div class="gd-cf-input-wrap">
					<select name="extra[decimal_separator]" id="decimal_separator">
						<option value="period" <?php selected(true, $value == 'period');?>><?php _e('. (period)', 'geodirectory'); ?></option>
						<option value="comma" <?php selected(true, $value == "comma");?>><?php _e(', (comma)', 'geodirectory'); ?></option>
					</select>
				</div>
			</li>

			<?php

			$value = '';
			if ($extra_fields && isset($extra_fields['decimal_display'])) {
				$value = esc_attr($extra_fields['decimal_display']);
			}elseif(isset($cf['defaults']['extra_fields']['decimal_display']) && $cf['defaults']['extra_fields']['decimal_display']){
				$value = esc_attr($cf['defaults']['extra_fields']['decimal_display']);
			}
			?>
			<li class="gd-advanced-setting gdat-extra_is_price" >
				<label for="decimal_display" class="gd-cf-tooltip-wrap">
					<span
						class="gd-help-tip gd-help-tip-float-none gd-help-tip-no-margin dashicons dashicons-editor-help"
						title='<?php _e( 'Select how the decimal is displayed.', 'geodirectory' ); ?>'>
                    </span>
					<?php _e('Decimal display:', 'geodirectory');?>
				</label>
				<div class="gd-cf-input-wrap">
					<select name="extra[decimal_display]" id="decimal_display">
						<option value="if" <?php selected(true, $value == 'if');?>><?php _e('If used (not .00)', 'geodirectory'); ?></option>
						<option value="allways" <?php selected(true, $value == "allways");?>><?php _e('Always (.00)', 'geodirectory'); ?></option>
					</select>
				</div>
			</li>

			<?php

			$value = '';
			if ($extra_fields && isset($extra_fields['currency_symbol'])) {
				$value = esc_attr($extra_fields['currency_symbol']);
			}elseif(isset($cf['defaults']['extra_fields']['currency_symbol']) && $cf['defaults']['extra_fields']['currency_symbol']){
				$value = esc_attr($cf['defaults']['extra_fields']['currency_symbol']);
			}
			?>
			<li class="gdat-extra_is_price">
				<label for="currency_symbol" class="gd-cf-tooltip-wrap">
					<span
						class="gd-help-tip gd-help-tip-float-none gd-help-tip-no-margin dashicons dashicons-editor-help"
						title='<?php _e( 'Select the currency symbol.', 'geodirectory' ); ?>'>
                    </span>
					<?php _e('Currency symbol:', 'geodirectory');?>
				</label>
				<div class="gd-cf-input-wrap">
					<input type="text" name="extra[currency_symbol]" id="currency_symbol"
					       value="<?php echo esc_attr($value); ?>"/>
				</div>
			</li>

			<?php

			$value = '';
			if ($extra_fields && isset($extra_fields['currency_symbol_placement'])) {
				$value = esc_attr($extra_fields['currency_symbol_placement']);
			}elseif(isset($cf['defaults']['extra_fields']['currency_symbol_placement']) && $cf['defaults']['extra_fields']['currency_symbol_placement']){
				$value = esc_attr($cf['defaults']['extra_fields']['currency_symbol_placement']);
			}
			?>
			<li class="gd-advanced-setting gdat-extra_is_price" >
				<label for="currency_symbol_placement" class="gd-cf-tooltip-wrap">
					<span
						class="gd-help-tip gd-help-tip-float-none gd-help-tip-no-margin dashicons dashicons-editor-help"
						title='<?php _e( 'Select the currency symbol placement.', 'geodirectory' ); ?>'>
                    </span>
					<?php _e('Currency symbol placement:', 'geodirectory');?>
				</label>
				<div class="gd-cf-input-wrap">
					<select name="extra[currency_symbol_placement]" id="currency_symbol_placement">
						<option value="left" <?php selected(true, $value == 'left');?>><?php _e('Left', 'geodirectory'); ?></option>
						<option value="right" <?php selected(true, $value == "right");?>><?php _e('Right', 'geodirectory'); ?></option>
					</select>
				</div>
			</li>


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
			<li>
				<label for="gd_file_types" class="gd-cf-tooltip-wrap">
					<span
						class="gd-help-tip gd-help-tip-float-none gd-help-tip-no-margin dashicons dashicons-editor-help"
						title='<?php _e( 'Select file types to allowed for file uploading. (Select multiple file types by holding down "Ctrl" key.)', 'geodirectory' ); ?>'>
                    </span>
					<?php _e('Allowed file types :', 'geodirectory'); ?>
				</label>
				<div class="gd-cf-input-wrap">
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
				</div>
			</li>

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
		public static function file_limit($output,$result_str,$cf,$field_info){
			ob_start();

			$extra_fields = isset($field_info->extra_fields) && $field_info->extra_fields != '' ? maybe_unserialize($field_info->extra_fields) : '';
			$gd_file_limit = !empty($extra_fields) && !empty($extra_fields['file_limit']) ? maybe_unserialize($extra_fields['file_limit']) : '';

			?>
			<li>
				<label for="gd_file_limit" class="gd-cf-tooltip-wrap">
					<span
						class="gd-help-tip gd-help-tip-float-none gd-help-tip-no-margin dashicons dashicons-editor-help"
						title='<?php _e( 'Select the number of files that can be uploaded, 0 = unlimited.', 'geodirectory' ); ?>'>
                    </span>
					<?php _e('File upload limit:', 'geodirectory'); ?>
				</label>
				<div class="gd-cf-input-wrap">
					<input type="number" name="extra[file_limit]" id="gd_file_limit" value="<?php echo esc_attr($gd_file_limit);?>">
				</div>
			</li>
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
			<li>
				<label for="date_format" class="gd-cf-tooltip-wrap">
					<span
						class="gd-help-tip gd-help-tip-float-none gd-help-tip-no-margin dashicons dashicons-editor-help"
						title='<?php _e( 'Select the date format.', 'geodirectory' ); ?>'>
                    </span>
					<?php _e('Date Format:', 'geodirectory'); ?>
				</label>
				<div class="gd-cf-input-wrap" style="overflow:inherit;">
					<?php
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

				</div>
			</li>
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
			<li>
				<label for="option_values" class="gd-cf-tooltip-wrap">
					<span
						class="gd-help-tip gd-help-tip-float-none gd-help-tip-no-margin dashicons dashicons-editor-help"
						title='<?php _e( 'Option Values should be separated by comma.', 'geodirectory' ); ?><br/>
						<?php _e('If using for a "tick filter" place a / and then either a 1 for true or 0 for false', 'geodirectory');?><br/>
						<?php _e('eg: "No Dogs Allowed/0,Dogs Allowed/1" (Select only, not multiselect)', 'geodirectory');?><br/>
						<small><?php if ($field_type == 'multiselect' || $field_type == 'select') { ?>
								<span><?php _e('- If using OPTGROUP tag to grouping options, use "{optgroup}OPTGROUP-LABEL|OPTION-1,OPTION-2{/optgroup}"', 'geodirectory'); ?></span>
								<span><?php _e('eg: "{optgroup}Pets Allowed|No Dogs Allowed/0,Dogs Allowed/1{/optgroup},{optgroup}Sports|Cricket/Cricket,Football/Football,Hockey{/optgroup}"', 'geodirectory'); ?></span>
							<?php } ?></small>
						'>
                    </span>
					<?php _e('Option Values:', 'geodirectory'); ?>
				</label>
				<div class="gd-cf-input-wrap">
					<input type="text" name="option_values" id="option_values"
					       value="<?php echo $value;?>"/>
					<br/>

				</div>
			</li>
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
			<li class="gd-advanced-setting">
				<label for="multi_display_type" class="gd-cf-tooltip-wrap">
					<span
						class="gd-help-tip gd-help-tip-float-none gd-help-tip-no-margin dashicons dashicons-editor-help"
						title='<?php _e( 'Show multiselect input as multiselect,checkbox or radio.', 'geodirectory' ); ?>'>
                    </span>
					<?php _e('Multiselect display type :', 'geodirectory'); ?>
				</label>
				<div class="gd-cf-input-wrap">

					<select name="extra[multi_display_type]" id="multi_display_type">
						<option <?php selected($multi_display_type,'select');?> value="select"><?php _e('Select', 'geodirectory');?></option>
						<option <?php selected($multi_display_type,'checkbox');?> value="checkbox"><?php _e('Checkbox', 'geodirectory');?></option>
						<option <?php selected($multi_display_type,'radio');?> value="radio"><?php _e('Radio', 'geodirectory');?></option>
					</select>

					<br/>
				</div>
			</li>
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

			$radio_id = (isset($field_info->htmlvar_name)) ? $field_info->htmlvar_name : rand(5, 500);
			?>
			<?php
			/**
			 * Called on the add custom fields settings page before the address field is output.
			 *
			 * @since 1.0.0
			 * @param array $address The address settings array.
			 * @param object $field_info Extra fields info.
			 */
			do_action('geodir_address_extra_admin_fields', $address, $field_info); ?>

			<li class="gd-advanced-setting">
				<label for="show_zip" class="gd-cf-tooltip-wrap">
            <span
	            class="gd-help-tip gd-help-tip-float-none gd-help-tip-no-margin dashicons dashicons-editor-help"
	            title='<?php _e( 'Select if you want to show zip/post code field in address section.', 'geodirectory' ); ?>'>
            </span>
					<?php _e('Display zip/post code:', 'geodirectory'); ?>
				</label>
				<div class="gd-cf-input-wrap gd-switch">

					<input type="radio" id="show_zip_yes<?php echo $radio_id;?>" name="extra[show_zip]" class="gdri-enabled"  value="1"
						<?php if (isset($address['show_zip']) && $address['show_zip'] == '1') {
							echo 'checked';
						} ?>/>
					<label onclick="gd_show_hide_radio(this,'show','cf-zip-lable');" for="show_zip_yes<?php echo $radio_id;?>" class="gdcb-enable"><span><?php _e('Yes', 'geodirectory'); ?></span></label>

					<input type="radio" id="show_zip_no<?php echo $radio_id;?>" name="extra[show_zip]" class="gdri-disabled" value="0"
						<?php if ((isset($address['show_zip']) && !$address['show_zip']) || !isset($address['show_zip'])) {
							echo 'checked';
						} ?>/>
					<label onclick="gd_show_hide_radio(this,'hide','cf-zip-lable');" for="show_zip_no<?php echo $radio_id;?>" class="gdcb-disable"><span><?php _e('No', 'geodirectory'); ?></span></label>


				</div>
			</li>

			<li  class="cf-zip-lable gd-advanced-setting"  <?php if ((isset($address['show_zip']) && !$address['show_zip']) || !isset($address['show_zip'])) {echo "style='display:none;'";}?> >
				<label for="zip_lable" class="gd-cf-tooltip-wrap">
					<span
						class="gd-help-tip gd-help-tip-float-none gd-help-tip-no-margin dashicons dashicons-editor-help"
						title='<?php _e( 'Enter zip/post code field label in address section.', 'geodirectory' ); ?>'>
                </span>
					<?php _e('Zip/Post code label :', 'geodirectory'); ?>
				</label>
				<div class="gd-cf-input-wrap">
					<input type="text" name="extra[zip_lable]" id="zip_lable"
					       value="<?php if (isset($address['zip_lable'])) {
						       echo esc_attr($address['zip_lable']);
					       }?>"/>
				</div>
			</li>

			<input type="hidden" name="extra[show_map]" value="1" />


			<li class="gd-advanced-setting">
				<label for="map_lable" class="gd-cf-tooltip-wrap">
					<span
						class="gd-help-tip gd-help-tip-float-none gd-help-tip-no-margin dashicons dashicons-editor-help"
						title='<?php _e( 'Enter text for `set address on map` button in address section.', 'geodirectory' ); ?>'>
					</span>
					<?php _e('Map button label :', 'geodirectory'); ?>
				</label>
				<div class="gd-cf-input-wrap">
					<input type="text" name="extra[map_lable]" id="map_lable"
					       value="<?php if (isset($address['map_lable'])) {
						       echo esc_attr($address['map_lable']);
					       }?>"/>
				</div>
			</li>

			<li class="gd-advanced-setting">
				<label for="show_mapzoom" class="gd-cf-tooltip-wrap">
					<span
						class="gd-help-tip gd-help-tip-float-none gd-help-tip-no-margin dashicons dashicons-editor-help"
						title='<?php _e( 'Do you want to use the user defined map zoom level from the add listing page?', 'geodirectory' ); ?>'>
					</span>
					<?php _e('Use user zoom level:', 'geodirectory'); ?>
				</label>
				<div class="gd-cf-input-wrap gd-switch">

					<input type="radio" id="show_mapzoom_yes<?php echo $radio_id;?>" name="extra[show_mapzoom]" class="gdri-enabled"  value="1"
						<?php if (isset($address['show_mapzoom']) && $address['show_mapzoom'] == '1') {
							echo 'checked';
						} ?>/>
					<label for="show_mapzoom_yes<?php echo $radio_id;?>" class="gdcb-enable"><span><?php _e('Yes', 'geodirectory'); ?></span></label>

					<input type="radio" id="show_mapzoom_no<?php echo $radio_id;?>" name="extra[show_mapzoom]" class="gdri-disabled" value="0"
						<?php if ((isset($address['show_mapzoom']) && !$address['show_mapzoom']) || !isset($address['show_mapzoom'])) {
							echo 'checked';
						} ?>/>
					<label for="show_mapzoom_no<?php echo $radio_id;?>" class="gdcb-disable"><span><?php _e('No', 'geodirectory'); ?></span></label>

				</div>
			</li>

			<li class="gd-advanced-setting">
				<label for="show_mapview" class="gd-cf-tooltip-wrap">
					<span
						class="gd-help-tip gd-help-tip-float-none gd-help-tip-no-margin dashicons dashicons-editor-help"
						title='<?php _e( 'Select if you want to show `set default map` options in address section. ( Satellite Map, Hybrid Map, Terrain Map)', 'geodirectory' ); ?>'>
					</span>
					<?php _e('Display map view:', 'geodirectory'); ?>
				</label>
				<div class="gd-cf-input-wrap gd-switch">

					<input type="radio" id="show_mapview_yes<?php echo $radio_id;?>" name="extra[show_mapview]" class="gdri-enabled"  value="1"
						<?php if (isset($address['show_mapview']) && $address['show_mapview'] == '1') {
							echo 'checked';
						} ?>/>
					<label for="show_mapview_yes<?php echo $radio_id;?>" class="gdcb-enable"><span><?php _e('Yes', 'geodirectory'); ?></span></label>

					<input type="radio" id="show_mapview_no<?php echo $radio_id;?>" name="extra[show_mapview]" class="gdri-disabled" value="0"
						<?php if ((isset($address['show_mapview']) && !$address['show_mapview']) || !isset($address['show_mapview'])) {
							echo 'checked';
						} ?>/>
					<label for="show_mapview_no<?php echo $radio_id;?>" class="gdcb-disable"><span><?php _e('No', 'geodirectory'); ?></span></label>

				</div>
			</li>


			<li class="gd-advanced-setting">
				<label for="mapview_lable" class="gd-cf-tooltip-wrap">
					<span
						class="gd-help-tip gd-help-tip-float-none gd-help-tip-no-margin dashicons dashicons-editor-help"
						title='<?php _e( 'Enter mapview field label in address section.', 'geodirectory' ); ?>'>
					</span>
					<?php _e('Map view label:', 'geodirectory'); ?>
				</label>
				<div class="gd-cf-input-wrap">
					<input type="text" name="extra[mapview_lable]" id="mapview_lable"
					       value="<?php if (isset($address['mapview_lable'])) {
						       echo esc_attr($address['mapview_lable']);
					       }?>"/>
				</div>
			</li>

			<li class="gd-advanced-setting">
				<label for="show_latlng" class="gd-cf-tooltip-wrap">
					<span
						class="gd-help-tip gd-help-tip-float-none gd-help-tip-no-margin dashicons dashicons-editor-help"
						title='<?php _e( 'This will show/hide the longitude fields in the address section add listing form.', 'geodirectory' ); ?>'>
					</span>
					<?php _e('Show latitude and longitude', 'geodirectory'); ?>
				</label>
				<div class="gd-cf-input-wrap gd-switch">

					<input type="radio" id="show_latlng_yes<?php echo $radio_id;?>" name="extra[show_latlng]" class="gdri-enabled"  value="1"
						<?php if (isset($address['show_latlng']) && $address['show_latlng'] == '1') {
							echo 'checked';
						} ?>/>
					<label for="show_latlng_yes<?php echo $radio_id;?>" class="gdcb-enable"><span><?php _e('Yes', 'geodirectory'); ?></span></label>

					<input type="radio" id="show_latlng_no<?php echo $radio_id;?>" name="extra[show_latlng]" class="gdri-disabled" value="0"
						<?php if ((isset($address['show_latlng']) && !$address['show_latlng']) || !isset($address['show_latlng'])) {
							echo 'checked';
						} ?>/>
					<label for="show_latlng_no<?php echo $radio_id;?>" class="gdcb-disable"><span><?php _e('No', 'geodirectory'); ?></span></label>

				</div>
			</li>
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
				<li class="gd-advanced-setting">
					<label for="cat_display_type" class="gd-cf-tooltip-wrap">
                <span
	                class="gd-help-tip gd-help-tip-float-none gd-help-tip-no-margin dashicons dashicons-editor-help"
	                title='<?php _e( 'Show categories list as select, multiselect, checkbox or radio', 'geodirectory' ); ?>'>
                </span>
						<?php _e( 'Category display type :', 'geodirectory' ); ?>
					</label>
					<div class="gd-cf-input-wrap">

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
					</div>
				</li>
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
			<li class="gd-advanced-setting">
				<label for="validation_pattern" class="gd-cf-tooltip-wrap">
            <span
	            class="gd-help-tip gd-help-tip-float-none gd-help-tip-no-margin dashicons dashicons-editor-help"
	            title='<?php _e( 'Enter regex expression for HTML5 pattern validation.', 'geodirectory' ); ?>'>
            </span>
					<?php _e( 'Validation Pattern:', 'geodirectory' ); ?>
				</label>
				<div class="gd-cf-input-wrap">
					<input type="text" name="validation_pattern" id="validation_pattern"
					       value="<?php echo $value; ?>"/>
				</div>
			</li>
			<?php
			$value = '';
			if ( isset( $field_info->validation_msg ) ) {
				$value = esc_attr( $field_info->validation_msg );
			} elseif ( isset( $cf['defaults']['validation_msg'] ) && $cf['defaults']['validation_msg'] ) {
				$value = esc_attr( $cf['defaults']['validation_msg'] );
			}
			?>
			<li class="gd-advanced-setting">
				<label for="validation_msg" class="gd-cf-tooltip-wrap">
            <span
	            class="gd-help-tip gd-help-tip-float-none gd-help-tip-no-margin dashicons dashicons-editor-help"
	            title='<?php _e( 'Enter a extra validation message to show to the user if validation fails.', 'geodirectory' ); ?>'>
            </span>
					<?php _e( 'Validation Message:', 'geodirectory' ); ?>
				</label>
				<div class="gd-cf-input-wrap">
					<input type="text" name="validation_msg" id="validation_msg"
					       value="<?php echo $value; ?>"/>
				</div>
			</li>
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
			<li class="gd-advanced-setting">
				<label for="advanced_editor" class="gd-cf-tooltip-wrap">
		            <span
			            class="gd-help-tip gd-help-tip-float-none gd-help-tip-no-margin dashicons dashicons-editor-help"
			            title='<?php _e( 'Select if you want to show the advanced editor on add listing page.', 'geodirectory' ); ?>'>
		            </span>
					<?php _e( 'Show advanced editor :', 'geodirectory' ); ?>
				</label>

				<div class="gd-cf-input-wrap gd-switch">

					<?php
					$extra = maybe_unserialize( $field_info->extra_fields );

					// set a unique id for radio fields
					$radio_id = ( isset( $field_info->htmlvar_name ) && $field_info->htmlvar_name ) ? $field_info->htmlvar_name : rand( 5, 500 );

					$value = '';
					if ( is_array( $extra ) && isset( $extra['advanced_editor'] ) ) {
						$value = absint( $extra['advanced_editor'] );
					} elseif ( isset( $cf['defaults']['advanced_editor'] ) && $cf['defaults']['advanced_editor'] ) {
						$value = absint( $cf['defaults']['advanced_editor'] );
					}
					?>

					<input type="radio" id="advanced_editor_yes<?php echo $radio_id; ?>" name="extra[advanced_editor]"
					       class="gdri-enabled" value="1"
						<?php if ( $value == '1' ) {
							echo 'checked';
						} ?>/>
					<label for="advanced_editor_yes<?php echo $radio_id; ?>"
					       class="gdcb-enable"><span><?php _e( 'Yes', 'geodirectory' ); ?></span></label>

					<input type="radio" id="advanced_editor_no<?php echo $radio_id; ?>" name="extra[advanced_editor]"
					       class="gdri-disabled" value="0"
						<?php if ( $value == '0' || ! $value ) {
							echo 'checked';
						} ?>/>
					<label for="advanced_editor_no<?php echo $radio_id; ?>"
					       class="gdcb-disable"><span><?php _e( 'No', 'geodirectory' ); ?></span></label>

				</div>

			</li>
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
			<li class="gd-advanced-setting">
				<label for="data_type">
            <span
	            class="gd-help-tip gd-help-tip-float-none gd-help-tip-no-margin dashicons dashicons-editor-help"
	            title='<?php _e( 'Select the data type for the field. This can affect things like search filtering.', 'geodirectory' ); ?>'>
            </span>
					<?php _e( 'Field Data Type :', 'geodirectory' ); ?>
				</label>
				<div class="gd-cf-input-wrap">
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
				</div>
			</li>

			<?php
			$value = '';
			if ( isset( $field_info->decimal_point ) ) {
				$value = esc_attr( $field_info->decimal_point );
			} elseif ( isset( $cf['defaults']['decimal_point'] ) && $cf['defaults']['decimal_point'] ) {
				$value = $cf['defaults']['decimal_point'];
			}
			?>

			<li class="decimal-point-wrapper"
			    style="<?php echo ( $dt_value == 'FLOAT' ) ? '' : 'display:none' ?>">
				<label for="decimal_point">
            <span
	            class="gd-help-tip gd-help-tip-float-none gd-help-tip-no-margin dashicons dashicons-editor-help"
	            title='<?php _e( 'Decimal point to display after point.', 'geodirectory' ); ?>'>
            </span>
					<?php _e( 'Select decimal point :', 'geodirectory' ); ?></label>
				<div class="gd-cf-input-wrap">
					<select name="decimal_point" id="decimal_point">
						<option value=""><?php echo _e( 'Select', 'geodirectory' ); ?></option>
						<?php for ( $i = 1; $i <= 10; $i ++ ) {
							$selected = $i == $value ? 'selected="selected"' : ''; ?>
							<option value="<?php echo $i; ?>" <?php echo $selected; ?>><?php echo $i; ?></option>
						<?php } ?>
					</select>
				</div>
			</li>
			<?php

			$output = ob_get_clean();

			return $output;
		}
	}

endif;