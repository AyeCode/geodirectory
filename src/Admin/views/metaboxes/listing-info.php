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

$wrapper_class = geodir_design_style() ? 'bsui' : '';
?>
<div id="geodir_wrapper" class="<?php echo esc_attr( $wrapper_class ); ?>">
	<?php
	/**
	 * Called before the GD custom fields are output in the wp-admin area.
	 *
	 * @since 1.0.0
	 * @see 'geodir_after_default_field_in_meta_box'
	 */
	do_action( 'geodir_before_default_field_in_meta_box' );

	// Display all fields in one information box.
	geodir_get_custom_fields_html( $package_id, 'all', $post_type );

	/**
	 * Called after the GD custom fields are output in the wp-admin area.
	 *
	 * @since 1.0.0
	 * @see 'geodir_before_default_field_in_meta_box'
	 */
	do_action( 'geodir_after_default_field_in_meta_box' );
	?>
</div>
