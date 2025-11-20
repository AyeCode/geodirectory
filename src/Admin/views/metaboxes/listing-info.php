<?php
/**
 * GeoDirectory Listing Information Meta Box View
 *
 * @package GeoDirectory\Admin\Views\MetaBoxes
 * @since   3.0.0
 * @author  AyeCode Ltd
 *
 * @var int    $post_id     The post ID.
 * @var string $post_type   The post type.
 * @var int    $package_id  The package ID for this post.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="geodir_wrapper" class="bsui">
	<?php
	/**
	 * Called before the GD custom fields are output in the wp-admin area.
	 *
	 * @since 1.0.0
	 * @see 'geodir_after_default_field_in_meta_box'
	 */
	do_action( 'geodir_before_default_field_in_meta_box' );

	/**
	 * V3 REFACTOR: Use FieldsService to render inputs.
	 * * Context: 'admin' ensures we render all fields appropriate for the backend.
	 * Note: We pass $post_id so the fields can pre-populate their values.
	 */
	geodirectory()->fields->render_fields( $post_id, $post_type, 'admin', (string) $package_id );

	/**
	 * Called after the GD custom fields are output in the wp-admin area.
	 *
	 * @since 1.0.0
	 * @see 'geodir_before_default_field_in_meta_box'
	 */
	do_action( 'geodir_after_default_field_in_meta_box' );
	?>
</div>
