<?php
/**
 * Opening Hours
 *
 * Display the opening hours meta box.
 *
 * @author      GeoDirectory
 * @package     GeoDirectory/Admin/Meta Boxes
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * GeoDir_Meta_Box_Opening_Hours Class.
 */
class GeoDir_Meta_Box_Opening_Hours {

	/**
	 * Output the metabox.
	 *
	 * @param WP_Post $post
	 */
	public static function output( $post ) {
		wp_nonce_field( 'geodir_save_opening_hours', 'geodir_meta_nonce' );

		$post = geodir_get_post_info( $post->ID );
		?>
		<div id="gd_opening_hours" class="panel-wrap gd-opening-hours">
		</div>
		<?php
	}

	/**
	 * Save meta box data.
	 *
	 * @param int $post_id
	 * @param WP_Post $post
	 */
	public static function save( $post_id, $post ) {

		do_action( 'geodir_opening_hours_save', $post_id, $post );
	}
}
