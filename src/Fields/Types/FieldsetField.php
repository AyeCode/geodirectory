<?php
namespace AyeCode\GeoDirectory\Fields\Types;

use AyeCode\GeoDirectory\Fields\Abstracts\AbstractFieldType;

/**
 * Class FieldsetField
 *
 * Handles the visual grouping of fields.
 * Replaces geodir_cfi_fieldset in input-functions-aui.php.
 */
class FieldsetField extends AbstractFieldType {

	public function render_input() {
		$cf = $this->field_data;
		$htmlvar_name = $cf['htmlvar_name'];

		// Allow field-specific filters (Legacy support)
		$hook_name = "geodir_custom_field_input_fieldset_{$htmlvar_name}";
		if ( has_filter( $hook_name ) ) {
			return apply_filters( $hook_name, '', $cf );
		}

		$bs5   = isset( $GLOBALS['aui_bs5'] ) && $GLOBALS['aui_bs5'];
		$class = $bs5 ? 'mb-3' : 'form-group';

		// Handle Description (DB column is 'frontend_desc', legacy arrays often mapped this to 'desc')
		$desc = ! empty( $cf['frontend_desc'] ) ? $cf['frontend_desc'] : ( isset( $cf['desc'] ) ? $cf['desc'] : '' );

		// Conditional Attributes
		$conditional_attrs = '';
		if ( function_exists( 'geodir_conditional_field_attrs' ) ) {
			$conditional_attrs = geodir_conditional_field_attrs( $cf );
		}

		ob_start();
		?>
		<fieldset class="<?php echo esc_attr( $class ); ?>" id="geodir_fieldset_<?php echo (int) $cf['id']; ?>" <?php echo $conditional_attrs; ?>>
			<h3 class="h3"><?php echo __( $cf['frontend_title'], 'geodirectory' ); ?></h3>
			<?php if ( $desc != '' ) {
				echo '<small class="text-muted">( ' . __( $desc, 'geodirectory' ) . ' )</small>';
			} ?>
		</fieldset>
		<?php
		return ob_get_clean();
	}

	/**
	 * Fieldsets are structural and do not save value data.
	 */
	public function sanitize( $value ) {
		return '';
	}

	/**
	 * Fieldsets are always valid as they have no input.
	 */
	public function validate( $value ) {
		return true;
	}
}
