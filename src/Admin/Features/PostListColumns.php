<?php
/**
 * GeoDirectory Post List Columns Feature
 *
 * @package GeoDirectory\Admin\Features
 * @since   3.0.0
 * @author  AyeCode Ltd
 */

// Use strict types for better code quality.
declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Admin\Features;

/**
 * Manages custom columns in the post list table for GeoDirectory CPTs.
 *
 * Adds image, location, categories, and tags columns to the admin post list.
 *
 * @since 3.0.0
 */
final class PostListColumns {
	/**
	 * Registers the necessary WordPress hooks for this feature.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		// Only add hooks if we have GD post types.
		$post_types = $this->get_post_types();
		if ( empty( $post_types ) ) {
			return;
		}

		foreach ( $post_types as $post_type ) {
			// Add custom columns to the post list table.
			add_filter( "manage_edit-{$post_type}_columns", [ $this, 'edit_post_columns' ], 100 );

			// Populate the custom columns with content.
			add_action( "manage_{$post_type}_posts_custom_column", [ $this, 'manage_post_columns' ], 10, 2 );

			// Make columns sortable.
			add_filter( "manage_edit-{$post_type}_sortable_columns", [ $this, 'post_sortable_columns' ] );
		}

		// Additional column hooks.
		add_action( 'post_date_column_status', [ $this, 'posts_column_status' ], 10, 4 );
		add_filter( 'post_row_actions', [ $this, 'post_row_actions' ], 20, 2 );
	}

	/**
	 * Modify admin post listing page columns.
	 *
	 * @param array $columns The column array.
	 *
	 * @return array Altered column array.
	 */
	public function edit_post_columns( array $columns ): array {
		$new_columns = [
			'image'         => __( 'Image', 'geodirectory' ),
			'location'      => __( 'Location', 'geodirectory' ),
			'gd_categories' => __( 'Categories', 'geodirectory' ),
			'gd_tags'       => __( 'Tags', 'geodirectory' ),
		];

		// Insert new columns after the checkbox and title columns (position 2).
		$offset = 2;
		$columns = array_merge(
			array_slice( $columns, 0, $offset ),
			$new_columns,
			array_slice( $columns, $offset )
		);

		return $columns;
	}

	/**
	 * Adds content to our custom post listing page columns.
	 *
	 * @global \wpdb    $wpdb    WordPress Database object.
	 * @global \WP_Post $post    WordPress Post object.
	 * @global object   $gd_post GeoDirectory post object.
	 *
	 * @param string $column  The column name.
	 * @param int    $post_id The post ID.
	 *
	 * @return void
	 */
	public function manage_post_columns( string $column, int $post_id ): void {
		global $post, $wpdb, $gd_post;

		// Load GD post data if not already loaded.
		if ( empty( $gd_post ) || $gd_post->ID !== $post_id ) {
//			$gd_post = geodir_get_post_info( $post_id );
			$gd_post = geodirectory()->posts->get_info( $post_id );

		}

		switch ( $column ) {
			case 'location':
				$this->render_location_column( $gd_post );
				break;

			case 'gd_categories':
				$this->render_categories_column( $post, $post_id );
				break;

			case 'gd_tags':
				$this->render_tags_column( $post, $post_id );
				break;

			case 'image':
				$this->render_image_column( $gd_post );
				break;
		}
	}

	/**
	 * Makes admin post listing page columns sortable.
	 *
	 * @param array $columns The column array.
	 *
	 * @return array Altered column array.
	 */
	public function post_sortable_columns( array $columns ): array {
		$columns['expire'] = 'expire';
		return $columns;
	}

	/**
	 * Posts column status.
	 *
	 * @param string    $status Post column status.
	 * @param \WP_Post  $post   Post object.
	 * @param string    $column Post column.
	 * @param string    $mode   Post mode.
	 *
	 * @return string The filtered status.
	 */
	public function posts_column_status( string $status, \WP_Post $post, string $column, string $mode ): string {
		if ( $column === 'date' && ! empty( $post->post_type ) && $this->is_gd_post_type( $post->post_type ) ) {
			$statuses = geodir_get_custom_statuses( $post->post_type );
			if ( ! empty( $statuses[ $post->post_status ] ) ) {
				$status = $statuses[ $post->post_status ];
			}
		}

		return $status;
	}

	/**
	 * Filters the array of row action links on the Posts list table.
	 *
	 * @param array    $actions An array of row action links.
	 * @param \WP_Post $post    The post object.
	 *
	 * @return array An array of row action links.
	 */
	public function post_row_actions( array $actions, \WP_Post $post ): array {
		if ( ! empty( $post->post_type ) && $this->is_gd_post_type( $post->post_type ) && current_user_can( 'manage_options' ) ) {
			$actions['geodir-regenerate-thumbnails bsui'] = '<button type="button" class="button-link" aria-label="' . esc_attr__( 'Regenerate Thumbnails', 'geodirectory' ) . '" aria-expanded="false" data-action="geodir-regenerate-thumbnails" data-post-id="' . $post->ID . '">' . __( 'Regenerate Thumbnails', 'geodirectory' ) . '</button>';
		}

		return $actions;
	}

	/**
	 * Renders the location column content.
	 *
	 * @param object|null $location The GeoDirectory post location data.
	 *
	 * @return void
	 */
	private function render_location_column( ?object $location ): void {
		if ( empty( $location ) ) {
			_e( 'Unknown', 'geodirectory' );
			return;
		}

		echo esc_html(
			sprintf(
				'%s, %s, %s',
				__( $location->country, 'geodirectory' ),
				$location->region,
				$location->city
			)
		);
	}

	/**
	 * Renders the categories column content.
	 *
	 * @param \WP_Post $post    The post object.
	 * @param int      $post_id The post ID.
	 *
	 * @return void
	 */
	private function render_categories_column( \WP_Post $post, int $post_id ): void {
		$terms = wp_get_object_terms( $post_id, get_object_taxonomies( $post ) );

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			_e( 'No Categories', 'geodirectory' );
			return;
		}

		$out = [];
		foreach ( $terms as $term ) {
			// Skip tags.
			if ( strpos( $term->taxonomy, 'tag' ) !== false ) {
				continue;
			}

			$out[] = sprintf(
				'<a href="%s">%s</a>',
				esc_url( add_query_arg(
					[
						'post_type'     => $post->post_type,
						$term->taxonomy => $term->slug,
					],
					'edit.php'
				) ),
				esc_html( sanitize_term_field( 'name', $term->name, $term->term_id, $term->taxonomy, 'display' ) )
			);
		}

		if ( ! empty( $out ) ) {
			echo implode( ', ', $out );
		} else {
			_e( 'No Categories', 'geodirectory' );
		}
	}

	/**
	 * Renders the tags column content.
	 *
	 * @param \WP_Post $post    The post object.
	 * @param int      $post_id The post ID.
	 *
	 * @return void
	 */
	private function render_tags_column( \WP_Post $post, int $post_id ): void {
		$terms = wp_get_object_terms( $post_id, get_object_taxonomies( $post ) );

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			_e( 'No Tags', 'geodirectory' );
			return;
		}

		$out = [];
		foreach ( $terms as $term ) {
			// Only include tags.
			if ( strpos( $term->taxonomy, 'tag' ) === false ) {
				continue;
			}

			$out[] = sprintf(
				'<a href="%s">%s</a>',
				esc_url( add_query_arg(
					[
						'post_type'     => $post->post_type,
						$term->taxonomy => $term->slug,
					],
					'edit.php'
				) ),
				esc_html( sanitize_term_field( 'name', $term->name, $term->term_id, $term->taxonomy, 'display' ) )
			);
		}

		if ( ! empty( $out ) ) {
			echo implode( ', ', $out );
		} else {
			_e( 'No Tags', 'geodirectory' );
		}
	}

	/**
	 * Renders the image column content.
	 *
	 * @param object|null $gd_post The GeoDirectory post object.
	 *
	 * @return void
	 */
	private function render_image_column( ?object $gd_post ): void {
		$image_raw = isset( $gd_post->featured_image ) && ! empty( $gd_post->featured_image ) ? $gd_post->featured_image : '';

		if ( empty( $image_raw ) ) {
			_e( 'N/A', 'geodirectory' );
			return;
		}

		$post_images = geodir_get_images($gd_post->ID, 1, false, '', array('post_images') );
		if ( ! empty( $post_images[0] ) ) {
			echo geodir_get_image_tag($post_images[0], 'thumbnail' );
		}
	}

	/**
	 * Gets the list of GeoDirectory post types.
	 *
	 * @return array List of post type slugs.
	 */
	private function get_post_types(): array {
		return function_exists( 'geodir_get_posttypes' ) ? (array) geodir_get_posttypes() : [];
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
