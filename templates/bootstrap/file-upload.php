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
	$drop_file_label = __( 'Drop files here', 'geodirectory' );
	$drop_file_button = __( 'Select Files', 'geodirectory' );

	if ( $image_limit > 1 ) {
		$file_limit_message = wp_sprintf( __( '(You can upload %d files)', 'geodirectory' ), $image_limit );
	} else {
		$file_limit_message = __( '(You can upload unlimited files with this package)', 'geodirectory' );
	}
} else { 
	$drop_file_label = __( 'Drop file here', 'geodirectory' );
	$drop_file_button = __( 'Select File', 'geodirectory' );
	$file_limit_message = '';
}
?>
<div class="geodir-add-files w-100 m-0 mb-3 p-0 bg-light text-center container" style="border: 4px dashed #ccc;">
	<div class="geodir_form_row clearfix geodir-files-dropbox position-relative p-3" id="<?php echo $id; ?>dropbox" >
		<input type="hidden" name="<?php echo $id; ?>" id="<?php echo $id; ?>" value="<?php echo $files; ?>" class="<?php if ( $is_required ) { echo 'gd_image_required_field'; } ?>"/>
		<input type="hidden" name="<?php echo $id; ?>image_limit" id="<?php echo $id; ?>image_limit" value="<?php echo $image_limit; ?>"/>
		<input type="hidden" name="<?php echo $id; ?>totImg" id="<?php echo $id; ?>totImg" value="<?php echo $total_files; ?>"/>
		<?php if ( $allowed_file_types != '' ) { ?>
			<input type="hidden" name="<?php echo $id; ?>_allowed_types" id="<?php echo $id; ?>_allowed_types" value="<?php echo esc_attr( $allowed_file_types ); ?>" data-exts="<?php echo esc_attr( $display_file_types ); ?>"/>
		<?php } ?>
		<div class="plupload-upload-uic hide-if-no-js <?php if ( $multiple ) { echo "plupload-upload-uic-multiple"; } ?>" id="<?php echo $id; ?>plupload-upload-ui">
			<div class="geodir-dropbox-title text-muted h3 m-0"><?php echo $drop_file_label; ?></div>
			<p class="text-muted mb-2">or</p>
			<input id="<?php echo $id; ?>plupload-browse-button" type="button" value="<?php echo esc_attr( $drop_file_button ); ?>" class="btn btn-primary mb-2 "/>
			<div class="geodir-dropbox-file-types text-muted"><?php echo( $display_file_types != '' ? __( 'Allowed file types:', 'geodirectory' ) . ' ' . $display_file_types : '' ); ?></div>
			<div class="geodir-dropbox-file-limit text-muted geodir-msg-file-limit-<?php echo $image_limit; ?>"><?php echo $file_limit_message;?></div>
			<span class="ajaxnonceplu" id="ajaxnonceplu<?php echo wp_create_nonce( $id . 'pluploadan' ); ?>"></span>
			<div class="filelist"></div>
		</div>
		<div class="plupload-thumbs mt-3 <?php if ( $multiple ) { echo "plupload-thumbs-multiple"; } ?> row row-cols-3 mx-auto px-1" id="<?php echo $id; ?>plupload-thumbs"></div>
		<?php if ( $multiple ) { ?>
		<span id="upload-msg" class="text-muted"><?php _e( 'Please drag &amp; drop the files to rearrange the order', 'geodirectory' ); ?></span>
		<?php } ?>
		
		<span id="<?php echo $id; ?>upload-error" class="d-none alert alert-danger" role="alert"></span>

		<div class="modal fade" id="gd-image-meta-input" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title"><?php _e('Set Image Texts','geodirectory'); ?></h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body text-left">
					</div>
					<div class="modal-footer">
					</div>
				</div>
			</div>
		</div>

		<div class="bg-dark overlay overlay-blue position-absolute z-index-1 gd-drop-overlay" style="display: none;">
			<div class="col text-center justify-content-center align-self-center container d-flex h-100" >
				<p class="display-1 text-white row justify-content-center align-self-center"><?php _e("Drop Here","geodirectory");?></p>
			</div>
		</div>

	</div>



</div>

<style>
	.geodir-add-files .geodir-files-dropbox.dragover .gd-drop-overlay{display: block !important;}
	.geodir-add-files .geodir-files-dropbox.dragover *{pointer-events:none !important}
</style>