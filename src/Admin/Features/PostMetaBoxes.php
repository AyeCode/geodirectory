<?php
/**
 * GeoDirectory Post Meta Boxes Feature
 *
 * @package GeoDirectory\Admin\Features
 * @since   3.0.0
 * @author  AyeCode Ltd
 */

// Use strict types for better code quality.
declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Admin\Features;

/**
 * Manages custom meta boxes for GeoDirectory CPTs.
 *
 * Adds listing information, attachments, and owner meta boxes.
 *
 * @since 3.0.0
 */
final class PostMetaBoxes {
	/**
	 * Registers the necessary WordPress hooks for this feature.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
	}

	/**
	 * Adds meta boxes to the GD post types.
	 *
	 * @return void
	 */
	public function add_meta_boxes(): void {
		global $post;

		if ( empty( $post->post_type ) || ! $this->is_gd_post_type( $post->post_type ) ) {
			return;
		}

		$post_type        = $post->post_type;
		$post_types_array = $this->get_post_types_array();

		if ( ! isset( $post_types_array[ $post_type ] ) ) {
			return;
		}

		$post_typename = __( $post_types_array[ $post_type ]['labels']['singular_name'], 'geodirectory' );
		$post_typename = geodir_ucwords( $post_typename );

		// Add attachments meta box.
		add_meta_box(
			'geodir_post_images',
			$post_typename . ' ' . __( 'Attachments', 'geodirectory' ),
			[ $this, 'render_attachment_meta_box' ],
			$post_type,
			'side'
		);

		// Add listing information meta box.
		add_meta_box(
			'geodir_post_info',
			$post_typename . ' ' . __( 'Information', 'geodirectory' ),
			[ $this, 'render_listing_info_meta_box' ],
			$post_type,
			'normal',
			'high'
		);

		// Add owner meta box if supported.
		$post_type_object = get_post_type_object( $post_type );
		if ( post_type_supports( $post_type, 'author' ) && current_user_can( $post_type_object->cap->edit_others_posts ) ) {
			add_meta_box(
				'geodir_mbox_owner',
				wp_sprintf( __( '%s Owner', 'geodirectory' ), $post_typename ),
				[ $this, 'render_owner_meta_box' ],
				$post_type,
				'normal',
				'core'
			);
		}
	}

	/**
	 * Renders the listing information meta box.
	 *
	 * @param \WP_Post $post The post object.
	 *
	 * @return void
	 */
	public function render_listing_info_meta_box( \WP_Post $post ): void {
		$post_id    = $post->ID;
		$post_type  = $post->post_type;
		$package_id = geodir_get_post_package_id( $post, $post_type );

		wp_nonce_field( plugin_basename( __FILE__ ), 'geodir_post_info_noncename' );

		// Load the view file.
		$this->load_view( 'metaboxes/listing-info', [
			'post_id'    => $post_id,
			'post_type'  => $post_type,
			'package_id' => $package_id,
		] );
	}

	/**
	 * Renders the owner meta box.
	 *
	 * @param \WP_Post $post The post object.
	 *
	 * @return void
	 */
	public function render_owner_meta_box( \WP_Post $post ): void {
		global $user_ID;

		$current_user_id = empty( $post->ID ) ? $user_ID : $post->post_author;
		$user            = get_user_by( 'id', $current_user_id );

		// Format the user name for the select2 dropdown.
		$current_user_name = sprintf(
			/* translators: 1: user display name 2: user ID 3: user email */
			esc_html__( '%1$s (#%2$s &ndash; %3$s)', 'geodirectory' ),
			preg_replace( '/[^a-zA-Z0-9\s]/', '', $user->display_name ), // Remove special characters for security.
			absint( $user->ID ),
			sanitize_email( $user->user_email )
		);

		// Load the view file.
		$this->load_view( 'metaboxes/owner', [
			'current_user_id'   => $current_user_id,
			'current_user_name' => $current_user_name,
		] );
	}

	/**
	 * Renders the attachments meta box.
	 *
	 * @param \WP_Post $post The post object.
	 *
	 * @return void
	 */
	public function render_attachment_meta_box( \WP_Post $post ): void {
		global $post_id;

		// If the image input in edit post backend is placed below the comment settings then $post_id is set to 0.
		if ( ! $post_id && ! empty( $post->ID ) ) {
			$post_id = $post->ID;
		}

		wp_nonce_field( plugin_basename( __FILE__ ), 'geodir_post_attachments_noncename' );

		// Get featured image HTML.
		$featured_image = get_the_post_thumbnail( $post_id, 'medium' );

		// Get current images.
		$cur_images = GeoDir_Media::get_field_edit_string( $post_id, 'post_images' );
		$cur_images = stripslashes_deep( $cur_images );

		// Image upload limit (currently set to 0 = unlimited).
		$image_limit = 0;

		// Field ID for plupload.
		$field_id = 'post_images';

		// Load the view file.
		$this->load_view( 'metaboxes/attachments', [
			'post_id'        => $post_id,
			'featured_image' => $featured_image,
			'image_limit'    => $image_limit,
			'cur_images'     => $cur_images,
			'field_id'       => $field_id,
		] );
	}

	/**
	 * Loads a view file with extracted variables.
	 *
	 * @param string $view_name The view name (without .php extension).
	 * @param array  $vars      Variables to extract into the view scope.
	 *
	 * @return void
	 */
	private function load_view( string $view_name, array $vars = [] ): void {
		$view_path = dirname( __DIR__ ) . '/views/' . $view_name . '.php';

		if ( ! file_exists( $view_path ) ) {
			return;
		}

		// Extract variables into the local scope.
		extract( $vars, EXTR_SKIP );

		// Include the view file.
		include $view_path;
	}

	/**
	 * Gets the list of GeoDirectory post types as an associative array.
	 *
	 * @return array Post types with their configuration.
	 */
	private function get_post_types_array(): array {
		return function_exists( 'geodir_get_posttypes' ) ? (array) geodir_get_posttypes( 'array' ) : [];
	}

	/**
	 * Checks if a post type is a GeoDirectory post type.
	 *
	 * @param string $post_type The post type to check.
	 *
	 * @return bool True if it's a GD post type.
	 */
	private function is_gd_post_type( string $post_type ): bool {
		$post_types = $this->get_post_types_array();
		return isset( $post_types[ $post_type ] );
	}
}
