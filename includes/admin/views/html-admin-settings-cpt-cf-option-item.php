<li class="gd-cf-tooltip-wrap">
	<?php
//print_r($cf);
	?>


	<a id="gd-<?php echo $id; ?>"
	   data-field-custom-type="<?php echo esc_attr($cf['field_type']); ?>"
	   data-field-type-key="<?php echo esc_attr($id); ?>"
	   data-field-type="<?php echo esc_attr($cf['field_type']); ?>"
	   data-field-single-use="<?php echo isset($cf['single_use']) && $cf['single_use'] ? esc_attr($cf['single_use']) : 0; ?>"
	   class="gd-draggable-form-items <?php echo esc_attr($cf['class']); ?>"
	   href="javascript:void(0);">

		<?php if ( isset( $cf['icon'] ) && geodir_is_fa_icon( $cf['icon'] ) ) {
			echo '<i class="' . esc_attr($cf['icon']) . '" aria-hidden="true"></i>';
		} elseif ( isset( $cf['icon'] ) && geodir_is_icon_url( $cf['icon'] ) ) {
			echo '<b style="background-image: url("' . esc_attr($cf['icon']) . '")"></b>';
		} else {
			echo '<i class="fas fa-cog" aria-hidden="true"></i>';
		} ?>
		<?php echo esc_attr($cf['name']); ?>
		
		<span class="gd-help-tip gd-help-tip-no-margin dashicons dashicons-editor-help" title="<?php echo esc_attr($cf['description']);?>"></span>

	</a>
</li>