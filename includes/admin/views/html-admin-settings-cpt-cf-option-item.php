<li class="gd-cf-tooltip-wrap">
	<?php
//	if ( isset( $cf['description'] ) && $cf['description'] ) {
//		echo '<div class="gdcf-tooltip">' . $cf['description'] . '</div>';
//	} ?>


	<a id="gd-<?php echo $id; ?>"
	   data-field-custom-type="<?php echo sanitize_text_field($cf['field_type']); ?>"
	   data-field-type-key="<?php echo sanitize_text_field($id); ?>"
	   data-field-type="<?php echo sanitize_text_field($cf['field_type']); ?>"
	   class="gd-draggable-form-items <?php echo sanitize_text_field($cf['class']); ?>"
	   href="javascript:void(0);">

		<?php if ( isset( $cf['icon'] ) && strpos( $cf['icon'], 'fa fa-' ) !== false ) {
			echo '<i class="' . sanitize_text_field($cf['icon']) . '" aria-hidden="true"></i>';
		} elseif ( isset( $cf['icon'] ) && $cf['icon'] ) {
			echo '<b style="background-image: url("' . sanitize_text_field($cf['icon']) . '")"></b>';
		} else {
			echo '<i class="fa fa-cog" aria-hidden="true"></i>';
		} ?>
		<?php echo sanitize_text_field($cf['name']); ?>
		
		<span class="gd-help-tip gd-help-tip-no-margin dashicons dashicons-editor-help" title="<?php echo sanitize_text_field($cf['description']);?>"></span>

	</a>
</li>