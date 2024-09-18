<?php
/**
 * File Upload
 *
 * This template can be overridden by copying it to yourtheme/geodirectory/file-upload.php.
 *
 * HOWEVER, on occasion GeoDirectory will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see        https://docs.wpgeodirectory.com/article/346-customizing-templates/
 * @package    GeoDirectory
 * @version    2.1.1.5
 *
 * @var string $id The input id string.
 * @var bool $is_required If the item is required or not.
 * @var string $files The files string.
 * @var int $image_limit The max number of images.
 */

defined( 'ABSPATH' ) || exit;

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
	<div class="geodir_form_row clearfix geodir-files-dropbox" id="<?php echo esc_attr( $id ); ?>dropbox">
		<input type="hidden" name="<?php echo esc_attr( $id ); ?>" id="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $files ); ?>" class="<?php if ( $is_required ) { echo 'gd_image_required_field'; } ?>"/>
		<input type="hidden" name="<?php echo esc_attr( $id ); ?>image_limit" id="<?php echo esc_attr( $id ); ?>image_limit" value="<?php echo esc_attr( $image_limit ); ?>"/>
		<input type="hidden" name="<?php echo esc_attr( $id ); ?>totImg" id="<?php echo esc_attr( $id ); ?>totImg" value="<?php echo esc_attr( $total_files ); ?>"/>
		<?php if ( $allowed_file_types != '' ) { ?>
			<input type="hidden" name="<?php echo esc_attr( $id ); ?>_allowed_types" id="<?php echo esc_attr( $id ); ?>_allowed_types" value="<?php echo esc_attr( $allowed_file_types ); ?>" data-exts="<?php echo esc_attr( $display_file_types ); ?>"/>
		<?php } ?>
		<div class="plupload-upload-uic hide-if-no-js <?php if ( $multiple ) { echo "plupload-upload-uic-multiple"; } ?>" id="<?php echo esc_attr( $id ); ?>plupload-upload-ui">
			<div class="geodir-dropbox-title"><?php echo $drop_file_label; ?></div>
			<input id="<?php echo esc_attr( $id ); ?>plupload-browse-button" type="button" value="<?php echo esc_attr( $drop_file_button ); ?>" class="geodir_button button "/>
			<div class="geodir-dropbox-file-types"><?php echo( $display_file_types != '' ? __( 'Allowed file types:', 'geodirectory' ) . ' ' . $display_file_types : '' ); ?></div>
			<div class="geodir-dropbox-file-limit geodir-msg-file-limit-<?php echo $image_limit; ?>"><?php echo $file_limit_message;?></div>
			<span class="ajaxnonceplu" id="ajaxnonceplu<?php echo wp_create_nonce( $id . 'pluploadan' ); ?>"></span>
			<div class="filelist"></div>
		</div>
		<div class="plupload-thumbs <?php if ( $multiple ) { echo "plupload-thumbs-multiple"; } ?> clearfix" id="<?php echo esc_attr( $id ); ?>plupload-thumbs"></div>
		<?php if ( $multiple ) { ?>
		<span id="upload-msg"><?php _e( 'Please drag & drop the files to rearrange the order', 'geodirectory' ); ?></span>
		<?php } ?>
		<span id="<?php echo esc_attr( $id ); ?>upload-error" style="display:none"></span>
		<span style="display: none" id="gd_image_meta_<?php echo esc_attr( $id ); ?>" class="lity-hide lity-show"></span>
	</div>
</div>