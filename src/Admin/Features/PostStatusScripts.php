<?php
/**
 * GeoDirectory Post Status Scripts Feature
 *
 * @package GeoDirectory\Admin\Features
 * @since   3.0.0
 * @author  AyeCode Ltd
 */

// Use strict types for better code quality.
declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Admin\Features;

/**
 * Injects custom post status options into the WordPress admin.
 *
 * This class adds custom GeoDirectory post statuses to the status
 * dropdowns on both the post list and edit screens.
 *
 * @since 3.0.0
 */
final class PostStatusScripts {
	/**
	 * Registers the necessary WordPress hooks for this feature.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		// Add scripts to post list footer.
		add_action( 'admin_footer-edit.php', [ $this, 'posts_footer' ] );

		// Add scripts to post edit/new footer.
		add_action( 'admin_footer-post.php', [ $this, 'post_form_footer' ] );
		add_action( 'admin_footer-post-new.php', [ $this, 'post_form_footer' ] );
	}

	/**
	 * Adds custom status options to the bulk edit dropdown on post list page.
	 *
	 * @return void
	 */
	public function posts_footer(): void {
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		// Only run on GeoDirectory post type screens.
		if ( ! $this->is_gd_screen( $screen_id ) ) {
			return;
		}

		$post_type = isset( $screen->post_type ) ? $screen->post_type : '';
		if ( empty( $post_type ) ) {
			return;
		}

		$statuses    = geodir_get_custom_statuses( $post_type );
		$status_list = '';

		foreach ( $statuses as $status => $label ) {
			$status_list .= '<option value="' . esc_attr( $status ) . '">' . esc_html( $label ) . '</option>';
		}

		if ( empty( $status_list ) ) {
			return;
		}
		?>
		<script type="text/javascript">
		jQuery(function($) {
		   $('select[name="_status"]').append('<?php echo addslashes( $status_list ); ?>');
		});
		</script>
		<?php
	}

	/**
	 * Adds custom status options to the post status dropdown on edit/new post page.
	 *
	 * @return void
	 */
	public function post_form_footer(): void {
		global $post;

		if ( empty( $post->post_type ) || ! $this->is_gd_post_type( $post->post_type ) ) {
			return;
		}

		$statuses      = geodir_get_custom_statuses( $post->post_type );
		$status_list   = '';
		$current_label = '';

		foreach ( $statuses as $status => $label ) {
			if ( $post->post_status === $status ) {
				$current_label = $label;
			}

			$status_list .= sprintf(
				'<option data-save-text="%s" value="%s" %s>%s</option>',
				esc_attr( wp_sprintf( __( 'Save as %s', 'geodirectory' ), $label ) ),
				esc_attr( $status ),
				selected( $post->post_status === $status, true, false ),
				esc_html( $label )
			);
		}

		if ( empty( $status_list ) ) {
			return;
		}
		?>
		<script type="text/javascript">
		jQuery(function($) {
		   var $mbox = $("#submitdiv");
		   $("select#post_status", $mbox).append('<?php echo addslashes( $status_list ); ?>');
		   <?php if ( $current_label ) : ?>
		   $(".misc-pub-section #post-status-display", $mbox).text('<?php echo esc_js( $current_label ); ?>');
		   <?php endif; ?>
		   $('.save-post-status', $mbox).on("click", function(e) {
			   var txt = $("select#post_status option:selected", $mbox).data('save-text');
			   if (txt) {
				   $('#save-post', $mbox).show().val(txt);
			   }
		   });
		   $('.save-post-status', $mbox).trigger('click');
		});
		</script>
		<?php
	}

	/**
	 * Checks if the current screen is a GeoDirectory screen.
	 *
	 * @param string $screen_id The screen ID.
	 *
	 * @return bool True if it's a GD screen.
	 */
	private function is_gd_screen( string $screen_id ): bool {
		if ( empty( $screen_id ) ) {
			return false;
		}

		$screen_ids = function_exists( 'geodir_get_screen_ids' ) ? geodir_get_screen_ids() : [];
		return in_array( $screen_id, $screen_ids, true );
	}

	/**
	 * Checks if a post type is a GeoDirectory post type.
	 *
	 * @param string $post_type The post type to check.
	 *
	 * @return bool True if it's a GD post type.
	 */
	private function is_gd_post_type( string $post_type ): bool {
		return function_exists( 'geodir_is_gd_post_type' ) && geodir_is_gd_post_type( $post_type );
	}
}
