<?php
/**
 * The template for uploading a file.
 *
 * The area of the page that contains the file upload section.
 *
 * @since 2.0.0.24
 *
 * @package GeoDirectory
 * @var string $id The input id string.
 * @var bool $is_required If the item is required or not.
 * @var string $files The files string.
 * @var int $image_limit The max number of images.
 */

if ( $multiple ) {
	$drop_file_label = __( 'Drop files here <small>or</small>', 'geodirectory' );
	$drop_file_button = __( 'Select Files', 'geodirectory' );

	if ( $image_limit > 1 ) {
		$file_limit_message = wp_sprintf( __( '(You can upload %d files)', 'geodirectory' ), $image_limit );
	} else {
		$file_limit_message = __( '(You can upload unlimited files with this package)', 'geodirectory' );
	}
} else { 
	$drop_file_label = __( 'Drop file here <small>or</small>', 'geodirectory' ); 
	$drop_file_button = __( 'Select File', 'geodirectory' );
	$file_limit_message = '';
}
?>
<div class="geodir-add-files">
	<div class="geodir_form_row clearfix geodir-files-dropbox" id="<?php echo $id; ?>dropbox">
		<input type="hidden" name="<?php echo $id; ?>" id="<?php echo $id; ?>" value="<?php echo $files; ?>" class="<?php if ( $is_required ) { echo 'gd_image_required_field'; } ?>"/>
		<input type="hidden" name="<?php echo $id; ?>image_limit" id="<?php echo $id; ?>image_limit" value="<?php echo $image_limit; ?>"/>
		<input type="hidden" name="<?php echo $id; ?>totImg" id="<?php echo $id; ?>totImg" value="<?php echo $total_files; ?>"/>
		<?php if ( $allowed_file_types != '' ) { ?>
			<input type="hidden" name="<?php echo $id; ?>_allowed_types" id="<?php echo $id; ?>_allowed_types" value="<?php echo esc_attr( $allowed_file_types ); ?>" data-exts="<?php echo esc_attr( $display_file_types ); ?>"/>
		<?php } ?>
		<div class="plupload-upload-uic hide-if-no-js <?php if ( $multiple ) { echo "plupload-upload-uic-multiple"; } ?>" id="<?php echo $id; ?>plupload-upload-ui">
			<div class="geodir-dropbox-title"><?php echo $drop_file_label; ?></div>
			<input id="<?php echo $id; ?>plupload-browse-button" type="button" value="<?php echo esc_attr( $drop_file_button ); ?>" class="geodir_button button "/>
			<div class="geodir-dropbox-file-types"><?php echo( $display_file_types != '' ? __( 'Allowed file types:', 'geodirectory' ) . ' ' . $display_file_types : '' ); ?></div>
			<div class="geodir-dropbox-file-limit geodir-msg-file-limit-<?php echo $image_limit; ?>"><?php echo $file_limit_message;?></div>
			<span class="ajaxnonceplu" id="ajaxnonceplu<?php echo wp_create_nonce( $id . 'pluploadan' ); ?>"></span>
			<div class="filelist"></div>
		</div>
		<div class="plupload-thumbs <?php if ( $multiple ) { echo "plupload-thumbs-multiple"; } ?> clearfix" id="<?php echo $id; ?>plupload-thumbs"></div>
		<?php if ( $multiple ) { ?>
		<span id="upload-msg"><?php _e( 'Please drag & drop the files to rearrange the order', 'geodirectory' ); ?></span>
		<?php } ?>
		<span id="<?php echo $id; ?>upload-error" style="display:none"></span>
		<span style="display: none" id="gd-image-meta-input" class="lity-hide lity-show"></span>
	</div>
</div>