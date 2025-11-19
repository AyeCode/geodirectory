<?php
/**
 * GeoDirectory Attachments Meta Box View
 *
 * @package GeoDirectory\Admin\Views\MetaBoxes
 * @since   3.0.0
 * @author  AyeCode Ltd
 *
 * @var int    $post_id        The post ID.
 * @var string $featured_image The featured image HTML.
 * @var int    $image_limit    The image upload limit for the package.
 * @var string $cur_images     The current images field value.
 * @var string $field_id       The field ID (usually "post_images").
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $aui_bs5;
?>
<?php if ( ! empty( $featured_image ) ) : ?>
	<h4><?php _e( 'Featured Image', 'geodirectory' ); ?></h4>
	<?php echo $featured_image; ?>
<?php endif; ?>

<h5 class="form_title">
	<?php if ( $image_limit === 1 ) : ?>
		<br /><small>(<?php echo esc_html( sprintf( __( 'You can upload %d image with this package', 'geodirectory' ), $image_limit ) ); ?>)</small>
	<?php elseif ( $image_limit > 1 ) : ?>
		<br /><small>(<?php echo esc_html( sprintf( __( 'You can upload %d images with this package', 'geodirectory' ), $image_limit ) ); ?>)</small>
	<?php else : ?>
		<br /><small>(<?php _e( 'You can upload unlimited images with this package', 'geodirectory' ); ?>)</small>
	<?php endif; ?>
</h5>

<div class="gtd-form_row clearfix" id="<?php echo esc_attr( $field_id ); ?>dropbox" style="border:1px solid #999999;padding:5px;text-align:center;">
	<input type="hidden" name="<?php echo esc_attr( $field_id ); ?>" id="<?php echo esc_attr( $field_id ); ?>" value="<?php echo esc_attr( $cur_images ); ?>"/>

	<div class="plupload-upload-uic hide-if-no-js plupload-upload-uic-multiple" id="<?php echo esc_attr( $field_id ); ?>plupload-upload-ui">
		<h4><?php _e( 'Drop files to upload', 'geodirectory' ); ?></h4>
		<input id="<?php echo esc_attr( $field_id ); ?>plupload-browse-button" type="button" value="<?php esc_attr_e( 'Select Files', 'geodirectory' ); ?>" class="button"/>
		<span class="ajaxnonceplu" id="ajaxnonceplu<?php echo wp_create_nonce( $field_id . 'pluploadan' ); ?>"></span>
		<div class="filelist"></div>
	</div>

	<?php if ( geodir_design_style() ) : ?>
		<div class="bsui">
			<span id="<?php echo esc_attr( $field_id ); ?>upload-error" class="d-none alert alert-danger" role="alert"></span>
		</div>
	<?php else : ?>
		<span id="<?php echo esc_attr( $field_id ); ?>upload-error" style="display:none"></span>
	<?php endif; ?>

	<div class="plupload-thumbs plupload-thumbs-multiple clearfix" id="<?php echo esc_attr( $field_id ); ?>plupload-thumbs" style="border-top:1px solid #ccc; padding-top:10px;">
	</div>

	<span id="upload-msg"><?php _e( 'Please drag &amp; drop the images to rearrange the order', 'geodirectory' ); ?></span>

	<span class="geodir-regenerate-thumbnails bsui" style="margin:25px 0 10px 0;display:block;">
		<button type="button" class="button-secondary" aria-label="<?php esc_attr_e( 'Regenerate Thumbnails', 'geodirectory' ); ?>" aria-expanded="false" data-action="geodir-regenerate-thumbnails" data-post-id="<?php echo esc_attr( $post_id ); ?>">
			<?php _e( 'Regenerate Thumbnails', 'geodirectory' ); ?>
		</button>
		<span style="margin-top:5px;display:block;"><?php _e( 'Regenerate thumbnails &amp; metadata.', 'geodirectory' ); ?></span>
	</span>

	<?php if ( geodir_design_style() ) : ?>
		<div class="modal fade bsui" id="gd_image_meta_<?php echo esc_attr( $field_id ); ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title mt-0"><?php _e( 'Set Image Texts', 'geodirectory' ); ?></h5>
						<?php if ( $aui_bs5 ) : ?>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						<?php else : ?>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						<?php endif; ?>
					</div>
					<div class="modal-body <?php echo $aui_bs5 ? 'text-start' : 'text-left'; ?>"></div>
					<div class="modal-footer"></div>
				</div>
			</div>
		</div>
	<?php else : ?>
		<span id="gd_image_meta_<?php echo esc_attr( $field_id ); ?>" class="lity-hide lity-show" style="display: none"></span>
	<?php endif; ?>
</div>
