<?php
namespace AyeCode\GeoDirectory\Fields\Types;

use AyeCode\GeoDirectory\Fields\Abstracts\AbstractFieldType;
use AyeCode_UI_Component_Helper;

/**
 * Class TaxonomyField
 *
 * Handles Category and Custom Taxonomy inputs.
 * Replaces geodir_cfi_categories and geodir_cfi_taxonomy.
 */
class TaxonomyField extends AbstractFieldType {

	public function render_input() {
		// Admin: Post Tags are handled by WP normally, skip if specifically 'post_tags'
		if ( is_admin() && $this->field_data['htmlvar_name'] === 'post_tags' ) {
			return '';
		}

		$args         = $this->get_aui_args();
		$extra_fields = $this->get_extra_fields();

		// Determine Taxonomy Name
		// Legacy logic often constructed it as "gd_place" + "category"
		$taxonomy = $this->field_data['post_type'] . 'category';
		if ( isset( $this->field_data['taxonomy'] ) ) {
			$taxonomy = $this->field_data['taxonomy'];
		}

		// Display Type (Select, Multiselect, Radio, Checkbox)
		$cat_display = ! empty( $extra_fields['cat_display_type'] ) ? $extra_fields['cat_display_type'] : 'select';

		// Calculate Limits
		// Note: We use the global wrapper or service for package retrieval
		$package = \geodir_get_post_package( get_post( $this->post_id ), $this->field_data['post_type'] );
		$limit   = ! empty( $package->category_limit ) ? absint( $package->category_limit ) : 0;
		$limit   = (int) apply_filters( 'geodir_cfi_post_categories_limit', $limit, get_post( $this->post_id ), $package );

		// Enforce Single Select if limit is 1
		if ( $limit === 1 && in_array( $cat_display, [ 'checkbox', 'multiselect' ] ) ) {
			$cat_display = ( $cat_display === 'checkbox' ) ? 'radio' : 'select';
		}

		$horizontal   = ( $args['label_type'] !== 'vertical' );
		$bs5          = isset( $GLOBALS['aui_bs5'] ) && $GLOBALS['aui_bs5'];
		$required_msg = $args['required'] ? __( 'Select at least one category from the list!', 'geodirectory' ) : '';

		// Get Current Selected Terms
		$selected_terms = [];
		if ( $this->post_id ) {
			$terms = wp_get_object_terms( $this->post_id, $taxonomy, [ 'fields' => 'ids' ] );
			if ( ! is_wp_error( $terms ) ) {
				$selected_terms = $terms;
			}
		}

		// Get Default Category (Primary Category)
		$default_cat_val = (int) \geodir_get_post_meta( $this->post_id, 'default_category', true );

		// Walker Arguments
		$walker_args = [
			'display_type' => $cat_display,
			'selected'     => $selected_terms,
			'exclude'      => [], // You can pass global $exclude_cats here if needed
			'hide_empty'   => false,
		];

		ob_start();
		?>
		<div id="<?php echo esc_attr( $taxonomy ); ?>_row" class="<?php echo esc_attr( $args['class'] ); ?> <?php echo $args['required'] ? 'required_field' : ''; ?> <?php echo $bs5 ? 'mb-3' : 'form-group'; ?> <?php echo $horizontal ? 'row' : ''; ?>" data-argument="<?php echo esc_attr( $taxonomy ); ?>" <?php echo isset($args['wrap_attributes']) ? $args['wrap_attributes'] : ''; ?>>

			<label for="cat_limit" class="<?php echo $horizontal ? 'col-sm-2 col-form-label' : ( $bs5 ? 'form-label' : '' ); ?>">
				<?php echo $args['label']; ?>
			</label>

			<div id="<?php echo esc_attr( $taxonomy ); ?>_wrap" class="geodir_taxonomy_field <?php echo $horizontal ? 'col-sm-10' : ''; ?>">
				<?php
				// Hidden inputs for JS validation limits
				echo '<input type="hidden" cat_limit="' . $limit . '" id="cat_limit" value="" name="cat_limit[' . esc_attr( $taxonomy ) . ']" />';

				// If using radio/checkbox, we render the hidden default_category input here to ensure a value exists.
				// For Select/Multiselect, the AUI select below handles the value submission.
				if ( $cat_display !== 'select' ) {
					echo '<input type="hidden" name="default_category" value="' . esc_attr( $default_cat_val ) . '">';
				}

				// --- RENDER INPUT ---

				if ( in_array( $cat_display, [ 'select', 'multiselect' ] ) ) {
					// Select2 Attributes
					$data_attrs = ' style="width:100%"';
					if ( $limit > 0 ) {
						$data_attrs .= ' data-maximum-selection-length="' . $limit . '"';
					}
					if ( $args['required'] ) {
						$data_attrs .= ' required oninvalid="setCustomValidity(\'' . esc_attr( $required_msg ) . '\')" onchange="try{setCustomValidity(\'\')}catch(e){}"';
					}

					$multiple = ( $cat_display === 'multiselect' ) ? 'multiple="multiple"' : '';
					$default_field_attr = ( $cat_display === 'multiselect' ) ? 'data-aui-cmultiselect="default_category"' : 'data-cselect="default_category"';

					// Render Select Wrapper
					echo '<select id="' . esc_attr( $taxonomy ) . '" ' . $multiple . ' name="tax_input[' . esc_attr( $taxonomy ) . '][]" class="geodir-category-select aui-select2" data-placeholder="' . esc_attr( $args['placeholder'] ) . '" ' . $default_field_attr . $data_attrs . '>';

					if ( $cat_display === 'select' ) {
						echo '<option value="">' . esc_html( $args['placeholder'] ) . '</option>';
					}

					// Call the V3 Taxonomies Service Walker
					echo geodirectory()->taxonomies->render_walker( $taxonomy, 0, 0, $walker_args );

					echo '</select>';

				} else {
					// Checkbox / Radio List
					// Call the V3 Taxonomies Service Walker
					echo geodirectory()->taxonomies->render_walker( $taxonomy, 0, 0, $walker_args );
				}

				// Help text
				if ( ! empty( $args['help_text'] ) && class_exists( 'AyeCode_UI_Component_Helper' ) ) {
					echo AyeCode_UI_Component_Helper::help_text( $args['help_text'] );
				}
				?>
				<div class="geodir_message_error alert alert-danger my-2 px-3 py-2" style="display:none"></div>
			</div>
		</div>
		<?php
		$html = ob_get_clean();

		// 2. Append "Default Category" selector if needed (Multiselect/Checkbox mode)
		if ( in_array( $cat_display, [ 'multiselect', 'checkbox' ] ) ) {

			$html .= aui()->select( array(
				'id'              => "default_category",
				'name'            => "default_category",
				'placeholder'     => esc_attr__( "Select Default Category", 'geodirectory' ),
				'value'           => $default_cat_val,
				'required'        => true,
				'label_type'      => ! empty( $geodir_label_type ) ? $geodir_label_type : 'horizontal',
				'label'           => __( "Default Category", 'geodirectory' ),// . $required,
				'help_text'       => esc_attr__( "The default category can affect the listing URL and map marker.", 'geodirectory' ),
				'multiple'        => false,
				'options'         => $default_cat_val ? array( $default_cat_val => '' ) : array(),
				'element_require' => '[%' . $taxonomy . '%]!=null',
				'wrap_attributes' => geodir_conditional_field_attrs( $this->field_data, 'default_category', 'select' )
			) );

		} else {
			// For single select, we just ensure the value is synced via hidden input if not handled above
			// (Note: The hidden input was already printed above inside the wrapper for non-select types,
			// but for SELECT types, the logic in legacy code implies the select itself acts as default,
			// or this hidden input holds the fallback).
			if ( $cat_display === 'select' ) {
				$html .= '<input type="hidden" id="default_category" name="default_category" value="' . esc_attr( $default_cat_val ) . '">';
			}
		}

		return $html;
	}
}
