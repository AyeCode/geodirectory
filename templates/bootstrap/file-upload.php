<?php
/**
 * File Upload
 *
 * This template can be overridden by copying it to yourtheme/geodirectory/bootstrap/file-upload.php.
 *
 * HOWEVER, on occasion GeoDirectory will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see        https://docs.wpgeodirectory.com/article/346-customizing-templates/
 * @package    GeoDirectory
 * @version    2.2.19
 *
 * @var string $id The input id string.
 * @var bool $is_required If the item is required or not.
 * @var string $files The files string.
 * @var int $image_limit The max number of images.
 */

defined( 'ABSPATH' ) || exit;

global $aui_bs5;

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
<div class="geodir-add-files w-100 m-0 mb-3 p-0 bg-light text-center container overflow-hidden" style="border:4px dashed #ccc">
	<div class="geodir_form_row clearfix geodir-files-dropbox position-relative p-3" id="<?php echo esc_attr( $id ); ?>dropbox" >
		<input type="<?php echo ( ! empty( $is_required ) ? 'text' : "hidden" ); ?>" name="<?php echo esc_attr( $id ); ?>" id="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $files ); ?>" class="<?php if ( $is_required ) { echo 'gd_image_required_field'; } ?>" <?php echo ( ! empty( $extra_attributes ) ? $extra_attributes : "" ); ?>/>
		<input type="hidden" name="<?php echo esc_attr( $id ); ?>image_limit" id="<?php echo esc_attr( $id ); ?>image_limit" value="<?php echo esc_attr( $image_limit ); ?>"/>
		<input type="hidden" name="<?php echo esc_attr( $id ); ?>totImg" id="<?php echo esc_attr( $id ); ?>totImg" value="<?php echo esc_attr( $total_files ); ?>"/>
		<?php if ( $allowed_file_types != '' ) { ?>
			<input type="hidden" name="<?php echo esc_attr( $id ); ?>_allowed_types" id="<?php echo esc_attr( $id ); ?>_allowed_types" value="<?php echo esc_attr( $allowed_file_types ); ?>" data-exts="<?php echo esc_attr( $display_file_types ); ?>"/>
		<?php } ?>
		<div class="plupload-upload-uic hide-if-no-js <?php if ( $multiple ) { echo "plupload-upload-uic-multiple"; } ?>" id="<?php echo esc_attr( $id ); ?>plupload-upload-ui">
			<div class="geodir-dropbox-title text-muted h3 m-0"><?php echo $drop_file_label; ?></div>
			<p class="text-muted mb-2"><?php _e( 'OR', 'geodirectory' ); ?></p>
			<input id="<?php echo esc_attr( $id ); ?>plupload-browse-button" type="button" value="<?php echo esc_attr( $drop_file_button ); ?>" class="btn btn-primary mb-2 "/>
			<div class="geodir-dropbox-file-types text-muted"><?php echo( $display_file_types != '' ? __( 'Allowed file types:', 'geodirectory' ) . ' ' . $display_file_types : '' ); ?></div>
			<div class="geodir-dropbox-file-limit text-muted geodir-msg-file-limit-<?php echo esc_attr( $image_limit ); ?>"><?php echo $file_limit_message;?></div>
			<span class="ajaxnonceplu" id="ajaxnonceplu<?php echo wp_create_nonce( $id . 'pluploadan' ); ?>"></span>
			<div class="filelist"></div>
		</div>
		<div class="plupload-thumbs mt-3 <?php if ( $multiple ) { echo "plupload-thumbs-multiple"; } ?> row row-cols-3 mx-auto px-1" id="<?php echo esc_attr( $id ); ?>plupload-thumbs"></div>
		<?php if ( $multiple ) { ?>
		<span id="upload-msg" class="text-muted"><?php _e( 'Please drag &amp; drop the files to rearrange the order', 'geodirectory' ); ?></span>
		<?php } ?>
		<span id="<?php echo esc_attr( $id ); ?>upload-error" class="d-none alert alert-danger" role="alert"></span>
		<div class="modal bsui fade" id="gd_image_meta_<?php echo esc_attr( $id ); ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title mt-0"><?php _e('Set Image Texts','geodirectory'); ?></h5>
						<?php
						if ( $aui_bs5 ) {
						?>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						<?php }else{ ?>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						<?php } ?>
					</div>
					<div class="modal-body <?php echo ( $aui_bs5 ? 'text-start' : 'text-left' ); ?>"></div>
					<div class="modal-footer"></div>
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
