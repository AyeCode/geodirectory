<?php
/**
 * Post Save Service
 *
 * Handles the orchestration of saving GeoDirectory post data to the CPT detail tables.
 * This service coordinates between custom fields, taxonomies, location, media, and the
 * database layer when a post is saved.
 *
 * @package GeoDirectory\Core\Services
 * @since   3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core\Services;

use AyeCode\GeoDirectory\Database\Repository\CustomFieldRepository;
use AyeCode\GeoDirectory\Database\Repository\PostRepository;

final class PostSaveService {

	/**
	 * Temporary storage for post data passed between wp_insert_post_data and save_post hooks.
	 *
	 * @var array|null
	 */
	private static $post_temp = null;

	/**
	 * Post repository for database operations.
	 *
	 * @var PostRepository
	 */
	private PostRepository $post_repository;

	/**
	 * Custom field repository for field definitions.
	 *
	 * @var CustomFieldRepository
	 */
	private CustomFieldRepository $field_repository;

	/**
	 * Media service for file operations.
	 *
	 * @var Media
	 */
	private Media $media;

	/**
	 * Constructor.
	 *
	 * @param PostRepository        $post_repository Post repository.
	 * @param CustomFieldRepository $field_repository Custom field repository.
	 * @param Media                 $media Media service.
	 */
	public function __construct( PostRepository $post_repository, CustomFieldRepository $field_repository, Media $media ) {
		$this->post_repository  = $post_repository;
		$this->field_repository = $field_repository;
		$this->media            = $media;
	}

	/**
	 * Set post data to temporary storage.
	 *
	 * Called by wp_insert_post_data filter to capture post data.
	 *
	 * @param array $data Post data array.
	 * @return void
	 */
	public function set_post_data( array $data ): void {
		self::$post_temp = $data;
	}

	/**
	 * Get post data from temporary storage.
	 *
	 * @return array|null Post data or null if not set.
	 */
	public function get_post_data(): ?array {
		return self::$post_temp;
	}

	/**
	 * Clear post data from temporary storage.
	 *
	 * @return void
	 */
	public function clear_post_data(): void {
		self::$post_temp = null;
	}

	/**
	 * Filter post data before WordPress inserts/updates it.
	 *
	 * This is called by the wp_insert_post_data filter. We store the data
	 * for later use in handle_save_post().
	 *
	 * @param array $data    Post data to be inserted/updated.
	 * @param array $postarr Unmodified post data array.
	 * @return array Modified post data.
	 */
	public function filter_insert_post_data( array $data, array $postarr ): array {
//		print_r($data);print_r($postarr);
//		echo 'LLL';exit;
		// Non GD post.
		if ( ! empty( $data['post_type'] ) && $data['post_type'] !== 'revision' && ! geodir_is_gd_post_type( $data['post_type'] ) ) {
			return $data;
		}

		// Check it's a GD CPT first.
		if (
			( isset( $data['post_type'] ) && in_array( $data['post_type'], geodir_get_posttypes(), true ) )
			|| ( isset( $data['post_type'] ) && $data['post_type'] === 'revision' && in_array( get_post_type( $data['post_parent'] ), geodir_get_posttypes(), true ) && ( ! isset( self::$post_temp ) || empty( self::$post_temp ) ) )
		) {
			// If the post_category or tags_input are empty and not sent as a $_REQUEST we remove them so they don't blank values.
			if ( empty( $postarr['post_category'] ) && ! isset( $_REQUEST['post_category'] ) && ! isset( $_REQUEST['tax_input'] ) ) {
				unset( $postarr['post_category'] );
			}

			if ( empty( $postarr['tags_input'] ) && ! isset( $_REQUEST['tags_input'] ) && ! isset( $_REQUEST['tax_input'] ) ) {
				unset( $postarr['tags_input'] );
			}

			if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'geodir_save_post' && empty( $postarr['tax_input'] ) && empty( $postarr['post_category'] ) && empty( $postarr['tags_input'] ) ) {
				unset( $postarr['post_category'] );
				unset( $postarr['tags_input'] );
			}

			// Assign the temp post data.
			self::$post_temp = $postarr;

			if ( ! empty( $data['post_content'] ) ) {
				/**
				 * Set filter for textarea extra sanitization.
				 *
				 * @since 2.8.120
				 *
				 * @param string $post_content The post content.
				 * @param array  $args Args array.
				 */
				$data['post_content'] = apply_filters(
					'geodir_extra_sanitize_textarea_field',
					$data['post_content'],
					array(
						'default'   => $data['post_content'],
						'field_key' => 'post_content',
						'postdata'  => $data,
						'postarr'   => $postarr,
					)
				);
			}
		} elseif ( ! empty( self::$post_temp ) && $data['post_type'] === 'revision' && isset( $data['post_parent'] ) && $data['post_parent'] === self::$post_temp['ID'] ) {
			// We might be saving a post revision at the same time so we don't blank the post_temp here.
		} else {
			self::$post_temp = null;
		}

		return $data;
	}

	/**
	 * Main handler for the save_post action.
	 *
	 * This orchestrates the entire save process: processing custom fields,
	 * categories, tags, location, media, and finally saving to the detail table.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 * @param bool     $update  Whether this is an update or new post.
	 * @return void
	 */
	public function handle_save_post( int $post_id, \WP_Post $post, bool $update ): void {
		// Non GD post.
		if ( ! empty( $post->post_type ) && $post->post_type !== 'revision' && ! geodir_is_gd_post_type( $post->post_type ) ) {
			return;
		}

		// Only fire if $post_temp is set.
		$gd_post = self::$post_temp;
		if ( ! $gd_post ) {
			return;
		}

		$gd_post = apply_filters( 'geodir_save_post_temp_data', $gd_post, $post, $update );


//		print_r($gd_post);print_r($_POST);print_r($post);exit;

		$is_dummy = isset( $gd_post['post_dummy'] ) && $gd_post['post_dummy'] ? true : false;

		// POST REVISION: grab the original info.
		if ( isset( $gd_post['ID'] ) && $gd_post['ID'] === 0 && $gd_post['post_type'] === 'revision' ) {
			$gd_post = (array) geodir_get_post_info( $gd_post['post_parent'] );
		} elseif ( $gd_post['post_type'] === 'revision' ) {
			$gd_post['post_type'] = get_post_type( $gd_post['post_parent'] );
		}

		$post_type = esc_attr( $gd_post['post_type'] ); // set the post type early.

		// Unhook this function so it doesn't loop infinitely.
		remove_action( 'save_post', array( $this, 'handle_save_post' ), 10 );

		// Start with defaults.
		$postarr = array(
			'post_id'     => $post_id,
			'post_status' => $post->post_status,
		);

		if ( isset( $gd_post['featured'] ) ) {
			$postarr['featured'] = sanitize_text_field( $gd_post['featured'] );
		}
		if ( ! $update ) {
			$postarr['submit_ip'] = sanitize_text_field( $_SERVER['REMOTE_ADDR'] );
		}

		// Process custom fields.
		$custom_field_data = $this->process_custom_fields( $gd_post, $post_type, $post_id, $post, $update );
		$postarr           = array_merge( $postarr, $custom_field_data );

		// Process categories.
		$category_data = $this->process_categories( $post_id, $gd_post, $post_type, $is_dummy );
		$postarr       = array_merge( $postarr, $category_data );

		// Process tags.
		$tag_data = $this->process_tags( $post_id, $gd_post, $post_type, $is_dummy );
		$postarr  = array_merge( $postarr, $tag_data );

		// Process location.
		$location_data = $this->process_location( $gd_post, $post_type, $update );
		$postarr       = array_merge( $postarr, $location_data );

		// Process media (also removes file field keys from $postarr).
		$media_data = $this->process_media( $post_id, $gd_post, $post_type, $is_dummy, $postarr );
		$postarr    = array_merge( $postarr, $media_data );

		// Copy post_title to _search_title.
		if ( isset( $postarr['post_title'] ) ) {
			$postarr['_search_title'] = geodir_sanitize_keyword( $postarr['post_title'], $post_type );
		}

		// Unset the post content as we don't save it in detail table.
		unset( $postarr['post_content'] );

		// Allow filtering of final data.
		$postarr = apply_filters( 'geodir_save_post_data', $postarr, $gd_post, $post, $update );

//		print_r($postarr);exit;


		// Save to detail table.
		$result = $this->post_repository->save_post_data( $post_id, $postarr, $post_type, $update );

		if ( false === $result ) {
			geodir_error_log( wp_sprintf( __( 'Could not save post data to detail table. Post ID: %d', 'geodirectory' ), $post_id ) );
		}

		// Clear the post cache.
		wp_cache_delete( "gd_post_" . $post_id, 'gd_post' );

		if ( $result ) {
			/**
			 * Fires after post data is successfully saved to detail table.
			 *
			 * @since 2.0.0.95
			 *
			 * @param array    $postarr Post data array saved.
			 * @param array    $gd_post Original post data from form.
			 * @param \WP_Post $post    WordPress post object.
			 * @param bool     $update  Whether this is an update.
			 */
			do_action( 'geodir_post_saved', $postarr, $gd_post, $post, $update );
		}

		/**
		 * Fires after post save attempt (success or failure).
		 *
		 * @since 2.3.54
		 *
		 * @param bool     $result   Result of save operation.
		 * @param array    $postarr  Post data array.
		 * @param array    $gd_post  Original post data from form.
		 * @param \WP_Post $post     WordPress post object.
		 * @param bool     $update   Whether this is an update.
		 */
		do_action( 'geodir_after_post_save', $result, $postarr, $gd_post, $post, $update );

		// Re-hook this function.
		add_action( 'save_post', array( $this, 'handle_save_post' ), 10, 3 );

		// Clear the temp data so any further posts in the same request don't use it.
		if ( isset( $post->post_type ) && $post->post_type === 'revision' && isset( $post->post_status ) && $post->post_status === 'inherit' ) {
			// We might be saving a revision AND THEN saving the main post so don't clear the temp data yet.
		} else {
			self::$post_temp = null;
		}
	}

	/**
	 * Process custom field values.
	 *
	 * Loops through all custom fields for the post type, applies sanitization,
	 * and returns an array of field_name => value pairs.
	 *
	 * @param array    $gd_post  Post data array.
	 * @param string   $post_type Post type slug.
	 * @param int      $post_id   Post ID.
	 * @param \WP_Post $post      WordPress post object.
	 * @param bool     $update    Whether this is an update.
	 * @return array Array of custom field data.
	 */
	protected function process_custom_fields( array $gd_post, string $post_type, int $post_id, \WP_Post $post, bool $update ): array {
		$postarr = array();

		// Get custom fields for this post type.
		$custom_fields = $this->field_repository->get_fields(
			array(
				'post_type' => $post_type,
				'location'  => 'all',
			)
		);

		foreach ( $custom_fields as $cf ) {
			if ( ! isset( $gd_post[ $cf['htmlvar_name'] ] ) ) {
				continue;
			}

			$gd_post_value = $gd_post[ $cf['htmlvar_name'] ];

			// Check for empty numbers and set to NULL so a default 0 or 0.00 is not set.
			if ( isset( $cf['data_type'] ) && ( $cf['data_type'] === 'DECIMAL' || $cf['data_type'] === 'INT' ) && $gd_post_value === '' ) {
				$gd_post_value = null;
			}

			// Apply field-type-specific filter.
			// Convert arrays to objects for backward compatibility with v2 hooks.
			$gd_post_value = apply_filters( "geodir_custom_field_value_{$cf['field_type']}", $gd_post_value, (object) $gd_post, (object) $cf, $post_id, $post, $update );

			// Handle arrays.
			if ( is_array( $gd_post_value ) ) {
				$gd_post_value = ! empty( $gd_post_value ) ? implode( ',', $gd_post_value ) : '';
			}

			// Strip slashes.
			if ( ! empty( $gd_post_value ) ) {
				$gd_post_value = stripslashes_deep( $gd_post_value );
			}

			$postarr[ $cf['htmlvar_name'] ] = $gd_post_value;
		}

		return $postarr;
	}

	/**
	 * Process categories and set default category.
	 *
	 * @param int    $post_id   Post ID.
	 * @param array  $gd_post   Post data array.
	 * @param string $post_type Post type slug.
	 * @param bool   $is_dummy  Whether this is dummy data.
	 * @return array Array with 'post_category' and 'default_category' keys.
	 */
	protected function process_categories( int $post_id, array $gd_post, string $post_type, bool $is_dummy ): array {
		$postarr = array();


		// Check for dummy data categories.
		if ( $is_dummy && isset( $gd_post['post_category'] ) ) {
			$categories = array_map( 'sanitize_text_field', $gd_post['post_category'] );
			$cat_ids    = array();
			foreach ( $categories as $cat_name ) {
				$temp_term = get_term_by( 'name', $cat_name, $post_type . 'category' );
				if ( isset( $temp_term->term_id ) ) {
					$cat_ids[] = $temp_term->term_id;
				}
			}
			if ( ! empty( $cat_ids ) ) {
				$categories = $cat_ids;
			}
			$post_categories = array_map( 'trim', $categories );
			wp_set_post_terms( $post_id, $categories, $post_type . 'category' );
		}

		// Set categories.
		if ( isset( $gd_post['tax_input'][ $post_type . 'category' ] ) && ! empty( $gd_post['tax_input'][ $post_type . 'category' ] ) ) {
			$post_categories = $gd_post['tax_input'][ $post_type . 'category' ];
		}
		if ( empty( $post_categories ) && isset( $gd_post['post_category'] ) ) {
			$post_categories = $gd_post['post_category'];
		}

		// Default category.
		if ( isset( $gd_post['default_category'] ) ) {
			$postarr['default_category'] = absint( $gd_post['default_category'] );
		}

//		echo $post_categories.$post_type.'@@@';
//		print_r($post_categories);
//		print_r($gd_post);exit;
		if ( isset( $post_categories ) ) {
			$post_categories = ! is_array( $post_categories ) ? array_filter( explode( ',', $post_categories ) ) : $post_categories;
			$categories      = array_map( 'absint', $post_categories );
			$categories      = array_filter( array_unique( $categories ) ); // Remove duplicates and empty values.

			// If the listing has no cat try to set it as Uncategorized.
			if ( empty( $categories ) ) {
				$uncategorized = get_term_by( 'name', 'Uncategorized', $post_type . 'category' );
				if ( isset( $uncategorized->term_id ) ) {
					$categories[] = $uncategorized->term_id;
					wp_set_post_terms( $post_id, $categories, $post_type . 'category' );
				}
			}

			if ( ! empty( $categories ) ) {
				$postarr['post_category'] = ',' . implode( ',', $categories ) . ',';
				$default_category         = isset( $categories[0] ) ? $categories[0] : $categories[1];
			} else {
				$postarr['post_category'] = '';
				$default_category         = '';
			}

			if ( empty( $postarr['default_category'] ) && ! empty( $default_category ) ) {
				$postarr['default_category'] = $default_category; // Set first category as default if not found.
			}

			// If logged out user we need to manually add cats.
			if ( ! get_current_user_id() ) {
				wp_set_post_terms( $post_id, $categories, $post_type . 'category' );
			}
		}

		return $postarr;
	}

	/**
	 * Process tags.
	 *
	 * @param int    $post_id   Post ID.
	 * @param array  $gd_post   Post data array.
	 * @param string $post_type Post type slug.
	 * @param bool   $is_dummy  Whether this is dummy data.
	 * @return array Array with 'post_tags' key.
	 */
	protected function process_tags( int $post_id, array $gd_post, string $post_type, bool $is_dummy ): array {
		$postarr = array();

		// Check for dummy data tags.
		if ( empty( $gd_post['post_tags'] ) && isset( $gd_post['tax_input'][ $post_type . '_tags' ] ) && ! empty( $gd_post['tax_input'][ $post_type . '_tags' ] ) ) {
			// Quick edit returns tag ids, we need the strings.
			if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'inline-save' ) {
				$post_tags = isset( $_REQUEST['tax_input'][ $post_type . '_tags' ] ) ? sanitize_text_field( $_REQUEST['tax_input'][ $post_type . '_tags' ] ) : '';
				if ( $post_tags ) {
					$post_tags = explode( ',', $post_tags );
				}
			} else {
				$post_tags = $gd_post['tax_input'][ $post_type . '_tags' ];
			}
		} elseif ( isset( $gd_post['post_tags'] ) && is_array( $gd_post['post_tags'] ) ) {
			$post_tags = $gd_post['post_tags'];
		} else {
			$post_tags = '';
		}

		if ( $post_tags ) {
			if ( ! get_current_user_id() || $is_dummy ) {
				$tags = array_map( 'sanitize_text_field', $post_tags );
				$tags = array_map( 'trim', $tags );
				wp_set_post_terms( $post_id, $tags, $post_type . '_tags' );
			} else {
				$tag_terms = wp_get_object_terms( $post_id, $post_type . '_tags', array( 'fields' => 'names' ) ); // Save tag names in detail table.
				if ( ! empty( $tag_terms ) && ! is_wp_error( $tag_terms ) ) {
					$post_tags = $tag_terms;
				} else {
					$post_tags = array();
				}

				$tags = array_map( 'trim', $post_tags );
			}
			$tags = array_filter( array_unique( $tags ) );
			// We need tags as a string.
			$postarr['post_tags'] = implode( ',', $tags );
		} else {
			// Save empty tags.
			if ( ( isset( $gd_post['post_tags'] ) || isset( $gd_post['tax_input'][ $post_type . '_tags' ] ) ) && empty( $gd_post['post_tags'] ) && empty( $gd_post['tax_input'][ $post_type . '_tags' ] ) ) {
				$postarr['post_tags'] = '';
			}
		}

		return $postarr;
	}

	/**
	 * Process location data (address, coordinates, map settings).
	 *
	 * @param array  $gd_post   Post data array.
	 * @param string $post_type Post type slug.
	 * @param bool   $update    Whether this is an update.
	 * @return array Array of location data.
	 */
	protected function process_location( array $gd_post, string $post_type, bool $update ): array {
		$postarr = array();

		// Save location info.
		if ( isset( $gd_post['street'] ) ) {
			$postarr['street'] = sanitize_text_field( stripslashes( $gd_post['street'] ) );
		}
		if ( isset( $gd_post['street2'] ) ) {
			$postarr['street2'] = sanitize_text_field( stripslashes( $gd_post['street2'] ) );
		}
		if ( ! isset( $gd_post['city'] ) && isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'inline-save' ) {
			// If inline save then don't adjust the location info.
		} elseif ( isset( $gd_post['city'] ) ) {
			$postarr['city'] = sanitize_text_field( stripslashes( $gd_post['city'] ) );
		} else {
			// Check if address is required.
			$address_required = geodir_cpt_requires_address( $post_type );
			if ( ! $update && $address_required ) {
				$default_location   = geodirectory()->locations->get_default();
				$postarr['city']    = stripslashes( $default_location->city );
				$postarr['region']  = stripslashes( $default_location->region );
				$postarr['country'] = stripslashes( $default_location->country );
			}
		}
		if ( isset( $gd_post['region'] ) ) {
			$postarr['region'] = sanitize_text_field( stripslashes( $gd_post['region'] ) );
		}
		if ( isset( $gd_post['country'] ) ) {
			$postarr['country'] = sanitize_text_field( stripslashes( $gd_post['country'] ) );
		}
		if ( isset( $gd_post['zip'] ) ) {
			$postarr['zip'] = sanitize_text_field( stripslashes( $gd_post['zip'] ) );
		}
		if ( isset( $gd_post['latitude'] ) ) {
//			print_r($gd_post);
			$postarr['latitude'] = sanitize_text_field( $gd_post['latitude'] );
		}
		if ( isset( $gd_post['longitude'] ) ) {
			$postarr['longitude'] = sanitize_text_field( $gd_post['longitude']  );
		}
		if ( isset( $gd_post['mapview'] ) ) {
			$postarr['mapview'] = sanitize_text_field( $gd_post['mapview'] );
		}
		if ( isset( $gd_post['mapzoom'] ) ) {
			$postarr['mapzoom'] = sanitize_text_field( $gd_post['mapzoom'] );
		}
		if ( isset( $gd_post['post_dummy'] ) ) {
			$postarr['post_dummy'] = $gd_post['post_dummy'];
		}

		return $postarr;
	}

	/**
	 * Process media uploads (images and attachments).
	 *
	 * Media/file fields are stored in the geodir_attachments table, NOT in the CPT detail table.
	 * This method processes the files and removes their keys from $postarr to prevent DB errors.
	 * The only exception is 'featured_image' which IS stored in the CPT detail table.
	 *
	 * @param int    $post_id   Post ID.
	 * @param array  $gd_post   Post data array.
	 * @param string $post_type Post type slug.
	 * @param bool   $is_dummy  Whether this is dummy data.
	 * @param array  &$postarr  Reference to post data array (will have file keys removed).
	 * @return array Array containing only 'featured_image' for CPT detail table.
	 */
	protected function process_media( int $post_id, array $gd_post, string $post_type, bool $is_dummy, array &$postarr ): array {
		$result = [];

		// Handle revision ID for post images
		$i_post_id = ! empty( $gd_post['revision_ID'] ) && wp_is_post_revision( absint( $gd_post['revision_ID'] ) ) === $post_id
			? absint( $gd_post['revision_ID'] )
			: $post_id;

		// Skip media processing for revisions
		if ( wp_is_post_revision( absint( $post_id ) ) ) {
			return $result;
		}

		// Get all file field keys for this post type
		$file_fields = $this->media->get_file_fields( $post_type );
		$file_field_keys = array_keys( $file_fields );

		// Always include post_images even if not in custom fields
		if ( ! in_array( 'post_images', $file_field_keys ) ) {
			$file_field_keys[] = 'post_images';
		}

		// Process files using the Media service
		$featured_image = $this->media->save_post_files( $i_post_id, $gd_post, $post_type, $is_dummy );

		// Remove all file field keys from $postarr (they shouldn't be in CPT detail table)
		foreach ( $file_field_keys as $field_key ) {
			unset( $postarr[ $field_key ] );
		}

		// Only return featured_image path for CPT detail table
		if ( ! empty( $featured_image ) || $featured_image === '' ) {
			$result['featured_image'] = $featured_image;
		}

		return $result;
	}
}
