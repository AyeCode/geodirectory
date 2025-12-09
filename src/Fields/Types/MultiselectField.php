<?php
namespace AyeCode\GeoDirectory\Fields\Types;

use AyeCode\GeoDirectory\Fields\Abstracts\AbstractFieldType;
use AUI_Component_Helper;

/**
 * Class MultiselectField
 *
 * Handles fields where multiple options can be selected.
 * Supports: Select2 (Multiple), Checkbox List, and Radio List (Radiox).
 * Replaces geodir_cfi_multiselect() and geodir_cf_multiselect().
 */
class MultiselectField extends AbstractFieldType {

	use MultiselectFieldOutputTrait;

	public function render_input() {
		$args         = $this->get_aui_args();
		$extra_fields = $this->get_extra_fields();

		// Determine display mode: 'select', 'checkbox', or 'radiox'
		$display_type = ! empty( $extra_fields['multi_display_type'] ) ? $extra_fields['multi_display_type'] : 'select';

		// Prepare Value (ensure it's an array)
		$value = $this->value;
		if ( ! is_array( $value ) && $value !== '' ) {
			$value = explode( ',', $value );
		}
		if ( ! empty( $value ) && is_array( $value ) ) {
			$value = array_map( 'trim', $value );
		}
		$args['value'] = $value;

		// Filters
		$hook_name = "geodir_custom_field_input_multiselect_{$this->field_data['htmlvar_name']}";
		if ( has_filter( $hook_name ) ) {
			return apply_filters( $hook_name, '', $this->field_data );
		}

		// --- RENDER MODE: SELECT ---
		if ( $display_type === 'select' ) {
			$args['options']          = geodir_string_to_options( $this->field_data['option_values'], true );
			$args['select2']          = true;
			$args['multiple']         = true;
			$args['data-allow-clear'] = false;

			// Placeholder
			if ( empty( $args['placeholder'] ) ) {
				$args['placeholder'] = wp_sprintf( __( 'Select %s&hellip;', 'geodirectory' ), $args['label'] );
			}
			$args['extra_attributes']['data-placeholder'] = $args['placeholder'];

			// JS Validation hooks for Select2
			if ( ! empty( $args['validation_text'] ) ) {
				$msg = esc_attr( addslashes( $args['validation_text'] ) );
				$args['extra_attributes']['oninvalid'] = "try{this.setCustomValidity('$msg')}catch(e){}";
				$args['extra_attributes']['onchange']  = "try{this.setCustomValidity('')}catch(e){}";
			}

			return aui()->select( $args );
		}

		// --- RENDER MODE: RADIOX (Single select displayed as buttons/list) ---
		if ( $display_type === 'radiox' ) {
			// Prepare options flat
			$options_deep = geodir_string_to_options( $this->field_data['option_values'], true );
			$options = [];
			if ( ! empty( $options_deep ) ) {
				foreach( $options_deep as $option ) {
					$options[ $option['value'] ] = $option['label'];
				}
			}
			$args['options'] = $options;
			$args['type']    = 'radio';
			$args['inline']  = false;

			// Update conditional attrs to look for 'radio' not 'multiselect'
			if ( function_exists( 'geodir_conditional_field_attrs' ) ) {
				$args['wrap_attributes'] = geodir_conditional_field_attrs( $this->field_data, '', 'radio' );
			}

			return aui()->radio( $args );
		}

		// --- RENDER MODE: CHECKBOX LIST (Custom Loop) ---
		// This replicates the manual loop in geodir_cfi_multiselect

		$horizontal = ( $args['label_type'] !== 'hidden' );
		$label      = $args['label'];
		$id         = $args['id'];
		$wrapper_class = ( isset($GLOBALS['aui_bs5']) && $GLOBALS['aui_bs5'] ? 'mb-3' : 'form-group' );

		ob_start();
		?>
		<div id="<?php echo $args['name']; ?>_row" class="<?php echo $args['required'] ? 'required_field' : ''; ?> <?php echo $wrapper_class; ?> row" <?php echo $this->get_conditional_attrs( $display_type ); ?>>

			<label for="<?php echo $id; ?>" class="<?php echo $horizontal ? 'col-sm-2 col-form-label' : '';?>">
				<?php echo $label; ?>
			</label>

			<?php
			// Hidden input to clear values if none checked
			echo '<input type="hidden" name="' . esc_attr( $args['name'] ) . '" value=""/>';

			if ( $horizontal ) {
				echo "<div class='col-sm-10 mt-2' ><div class='border rounded px-2 scrollbars-ios' style='max-height: 150px;overflow-y:auto;overflow-x: hidden;'>";
			}

			$option_values_arr = geodir_string_to_options( $this->field_data['option_values'], true );

			if ( ! empty( $option_values_arr ) ) {
				foreach ( $option_values_arr as $i => $option_row ) {
					// Handle Optgroups headings
					if ( isset( $option_row['optgroup'] ) ) {
						if ( $option_row['optgroup'] === 'start' ) {
							echo '<h6>' . esc_html( $option_row['label'] ) . '</h6>';
						}
						continue;
					}

					$opt_label = $option_row['label'];
					$opt_val   = $option_row['value'];
					$checked   = ( is_array( $value ) && in_array( $opt_val, $value ) ) ? true : false;

					// Checkbox Input via AUI
					echo aui()->input([
						'name'      => $args['name'] . '[]',
						'id'        => $args['name'] . '_' . $i,
						'type'      => 'checkbox',
						'value'     => $opt_val,
						'label'     => $opt_label,
						'label_type'=> 'hidden', // We just want the input + label next to it, AUI handles this for checkbox type
						'no_wrap'   => true,
						'checked'   => $checked,
						// Add required logic to first item only usually, simplified here
						'extra_attributes' => $i === 0 && $args['required'] ? $this->get_checkbox_validation_js($args['name'], $args['validation_text']) : []
					]);
				}
			}

			if ( $horizontal ) {
				echo "</div>";
				if ( ! empty( $args['help_text'] ) && class_exists( 'AUI_Component_Helper' ) ) {
					echo AUI_Component_Helper::help_text( $args['help_text'] );
				}
				echo "</div>";
			}
			?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Generate custom JS for checkbox group validation
	 */
	protected function get_checkbox_validation_js( $name, $msg ) {
		$cf_name = esc_attr( $name );
		$msg = esc_attr( addslashes( $msg ) );

		return [
			'required' => true,
			'onchange' => "if(jQuery('[name=\"{$cf_name}[]\"]:checked').length || !jQuery('input#{$cf_name}_0').is(':visible')){jQuery('#{$cf_name}_0').removeAttr('required')}else{jQuery('#{$cf_name}_0').attr('required',true)}",
			'oninput'  => "try{document.getElementById('{$cf_name}_0').setCustomValidity('')}catch(e){}",
			'oninvalid'=> "try{document.getElementById('{$cf_name}_0').setCustomValidity('{$msg}')}catch(e){}"
		];
	}

	protected function get_conditional_attrs( $type ) {
		if ( function_exists( 'geodir_conditional_field_attrs' ) ) {
			return geodir_conditional_field_attrs( $this->field_data, '', $type );
		}
		return '';
	}

	public function sanitize( $value ) {
		if ( is_array( $value ) ) {
			return array_map( 'sanitize_text_field', $value );
		}
		return sanitize_text_field( $value );
	}
}
