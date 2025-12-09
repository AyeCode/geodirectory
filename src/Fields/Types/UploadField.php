<?php
namespace AyeCode\GeoDirectory\Fields\Types;

use AyeCode\GeoDirectory\Fields\Abstracts\AbstractFieldType;
use AyeCode_UI_Component_Helper;
use GeoDir_Media;

class UploadField extends AbstractFieldType {

	use FileFieldOutputTrait;

	public function render_input() {
		// Admin check: WP uses standard metaboxes for images usually, but GD uses custom logic.
		if ( is_admin() && $this->field_data['htmlvar_name'] == 'post_images' ) {
		//	return ''; // Often handled by a separate meta box in legacy, ensure this matches v3 needs.
		}

		$args         = $this->get_aui_args();
		$extra_fields = $this->get_extra_fields();
		$htmlvar_name = $this->field_data['htmlvar_name'];
		$post_id      = $this->post_id;

		// 1. Calculate Limits and Types
		$file_limit = ! empty( $extra_fields['file_limit'] ) ? absint( $extra_fields['file_limit'] ) : 0;
		$file_limit = apply_filters( "geodir_custom_field_file_limit", $file_limit, $this->field_data, get_post( $post_id ) );

		$file_types = isset( $extra_fields['gd_file_types'] ) ? maybe_unserialize( $extra_fields['gd_file_types'] ) : geodir_image_extensions();
		if ( is_string( $file_types ) ) {
			$file_types = explode( ",", $file_types );
		}
		$file_types = array_filter( (array) $file_types );

		$display_types = ! empty( $file_types ) ? '.' . implode( ", .", $file_types ) : '';
		$allowed_types = ! empty( $file_types ) ? implode( ",", $file_types ) : '';

		$multiple = ( $file_limit != 1 );


		$revision_id = isset( $this->field_data['revision_id'] ) ? $this->field_data['revision_id'] : '';

		// 2. Retrieve Existing Files (Edit Mode)
		// Logic: Check for temp media (autosave) first, then saved media.
		// NOTE: You might need to inject the 'revision_id' logic if managing drafts/revisions.
		$files_string = $files_string = geodirectory()->media->get_field_edit_string( $post_id, $htmlvar_name, $revision_id );
		$total_files  = ! empty( $files_string ) ? count( explode( '::', $files_string ) ) : 0;

		// 3. Visibility Filter
		$show_input = apply_filters( 'geodir_file_uploader_on_add_listing', true, $this->field_data['post_type'] );
		if ( ! $show_input ) {
			return '';
		}

		// 4. Render Template
		// We wrap it in the standard AUI structure manually because AUI doesn't have a complex drag-drop uploader component like GD's.
		$wrapper_class = ( isset($GLOBALS['aui_bs5']) && $GLOBALS['aui_bs5'] ? 'mb-3' : 'form-group' );
		$horizontal    = ( $args['label_type'] !== 'vertical' );

		ob_start();
		?>
		<div id="<?php echo esc_attr( $htmlvar_name ); ?>_row" class="<?php echo $args['required'] ? 'required_field' : ''; ?> <?php echo $wrapper_class; ?> row" <?php echo isset($args['wrap_attributes']) ? $args['wrap_attributes'] : ''; ?>>

			<label for="<?php echo esc_attr( $htmlvar_name ); ?>" class="<?php echo $horizontal ? 'col-sm-2 col-form-label' : ''; ?>">
				<?php echo $args['label']; ?>
			</label>

			<?php if ( $horizontal ) { echo '<div class="col-sm-10">'; } ?>

			<?php
			if ( ! empty( $args['help_text'] ) && class_exists( 'AUI_Component_Helper' ) ) {
				echo \AUI_Component_Helper::help_text( $args['help_text'] );
			}
			?>

			<div class="<?php echo $horizontal ? 'w-100' : 'mx-3 w-100'; ?>">
				<?php
				// Call the legacy template or refactor the template code into here.
				// For now, calling the template maintains compatibility with the JS uploader logic.
				echo geodir_get_template_html( "bootstrap/file-upload.php", array(
					'id'                  => $htmlvar_name,
					'is_required'         => $args['required'],
					'files'               => $files_string,
					'image_limit'         => $file_limit,
					'total_files'         => $total_files,
					'allowed_file_types'  => $allowed_types,
					'display_file_types'  => $display_types,
					'multiple'            => $multiple,
					'extra_attributes'    => '' // Helper::extra_attributes() logic can go here if needed
				) );
				?>
			</div>

			<?php if ( $horizontal ) { echo '</div>'; } ?>
		</div>
		<?php

		return ob_get_clean();
	}
}
