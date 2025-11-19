<?php
/**
 * GeoDirectory Owner Meta Box View
 *
 * @package GeoDirectory\Admin\Views\MetaBoxes
 * @since   3.0.0
 * @author  AyeCode Ltd
 *
 * @var int    $current_user_id   The current user ID.
 * @var string $current_user_name The formatted user name string.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<label class="screen-reader-text" for="post_author_override"><?php _e( 'User', 'geodirectory' ); ?></label>
<select class="geodir-user-search" name="post_author_override" id="post_author_override" data-placeholder="<?php esc_attr_e( 'Search for a user&hellip;', 'geodirectory' ); ?>" data-allow_clear="false">
	<option value="<?php echo esc_attr( $current_user_id ); ?>" selected="selected"><?php echo esc_attr( $current_user_name ); ?></option>
</select>
