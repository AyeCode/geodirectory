<?php
/**
 * Posts Service
 *
 * Handles the business logic for GeoDirectory posts, acting as a coordinator
 * between WordPress hooks and the database repository.
 *
 * @package GeoDirectory\Core\Services
 * @since   3.0.0
 * @author  AyeCode Ltd
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core\Services;

use AyeCode\GeoDirectory\Database\Repository\PostRepository;

/**
 * Service for GeoDirectory post operations.
 *
 * @since 3.0.0
 */
final class Posts {
	private PostRepository $repository;
	private Settings $settings;
	private Statuses $statuses;
	private PostTypes $post_types;

	/**
	 * Constructor.
	 *
	 * All dependencies are injected here via the DI container.
	 *
	 * @param PostRepository $repository  The repository for database access.
	 * @param Settings       $settings    The settings utility.
	 * @param Statuses       $statuses    The statuses service.
	 * @param PostTypes      $post_types  The post types service.
	 */
	public function __construct(
		PostRepository $repository,
		Settings $settings,
		Statuses $statuses,
		PostTypes $post_types
	) {
		$this->statuses   = $statuses;
		$this->settings   = $settings;
		$this->repository = $repository;
		$this->post_types = $post_types;
	}

	/**
	 * Get post custom fields with caching.
	 *
	 * @param int  $post_id The post ID. If empty, uses current global post.
	 * @param bool $cached  Use cached data. Default true.
	 * @return object|null Returns full post details as an object or null if not found.
	 */
	public function get_info( int $post_id = 0, bool $cached = true ): ?object {
		global $post, $preview;

		// Use global post if no ID provided.
		if ( ! $post_id && ! empty( $post ) ) {
			$post_id = $post->ID;
		}

		if ( ! $post_id ) {
			return null;
		}

		// Check cache first.
		if ( $cached ) {
			$cache = wp_cache_get( 'gd_post_' . $post_id, 'gd_post' );
			if ( $cache ) {
				return $cache;
			}
		}

		$post_type = get_post_type( $post_id );

		if ( $post_type === 'revision' ) {
			$post_type = get_post_type( wp_get_post_parent_id( $post_id ) );
		}

		// Check if preview mode.
		if ( $preview && ! empty( $post ) && $post->ID === $post_id ) {
			$post_id = class_exists( 'GeoDir_Post_Data' ) ? \GeoDir_Post_Data::get_post_preview_id( $post_id ) : $post_id;
		}

		if ( ! function_exists( 'geodir_is_gd_post_type' ) || ! geodir_is_gd_post_type( $post_type ) ) {
			return new \stdClass();
		}

		$post_detail = $this->repository->get_post_info( $post_id );

		if ( empty( $post_detail ) ) {
			return null;
		}

		// Check for distance setting from global post.
		if ( ! empty( $post->distance ) ) {
			$post_detail->distance = $post->distance;
		}

		/**
		 * Filter GeoDirectory post info object.
		 *
		 * @since 2.1.0.4
		 * @since 3.0.0 Moved to Posts service.
		 *
		 * @param object $post_detail The GeoDirectory post object.
		 * @param int    $post_id     The post ID.
		 */
		$post_detail = apply_filters( 'geodir_get_post_info', $post_detail, $post_id );

		// Set cache.
		if ( $cached ) {
			wp_cache_set( 'gd_post_' . $post_id, $post_detail, 'gd_post' );
		}

		return $post_detail;
	}

	/**
	 * Get post custom meta.
	 *
	 * @param int    $post_id The post ID.
	 * @param string $meta_key The meta key to retrieve.
	 * @param bool   $single Whether to return a single value. Default false.
	 * @return mixed|null Will be value of meta data field.
	 */
	public function get_meta( int $post_id, string $meta_key, bool $single = false ) {
		global $preview;

		if ( ! $post_id ) {
			return null;
		}

		$post_type = get_post_type( $post_id );

		if ( $post_type === 'revision' ) {
			$post_type = get_post_type( wp_get_post_parent_id( $post_id ) );
		}

		// Check if preview mode.
		if ( $preview ) {
			$post_id = class_exists( 'GeoDir_Post_Data' ) ? \GeoDir_Post_Data::get_post_preview_id( $post_id ) : $post_id;
		}

		$all_post_types = function_exists( 'geodir_get_posttypes' ) ? geodir_get_posttypes() : [];

		if ( ! in_array( $post_type, $all_post_types, true ) ) {
			return null;
		}

		/**
		 * Short circuit the DB query if needed.
		 *
		 * @since 1.6.20
		 * @since 3.0.0 Moved to Posts service.
		 *
		 * @param mixed|null $pre_value Default null. Return non-null to short-circuit.
		 * @param int        $post_id   The post ID.
		 * @param string     $meta_key  The meta key.
		 * @param bool       $single    Whether to return a single value.
		 */
		$pre_value = apply_filters( 'geodir_pre_get_post_meta', null, $post_id, $meta_key, $single );
		if ( $pre_value !== null ) {
			return $pre_value;
		}

		$meta_value = $this->repository->get_meta( $post_id, $meta_key, $post_type );

		/**
		 * Filter the listing custom meta.
		 *
		 * @since 1.6.20
		 * @since 3.0.0 Moved to Posts service.
		 *
		 * @param mixed  $meta_value The meta value.
		 * @param int    $post_id    The post ID.
		 * @param string $meta_key   The meta key.
		 * @param bool   $single     Whether to return a single value.
		 */
		return apply_filters( 'geodir_get_post_meta', $meta_value, $post_id, $meta_key, $single );
	}

	/**
	 * Check if a given post has a given custom meta.
	 *
	 * @param int         $post_id  The post ID. If null, uses current post.
	 * @param string      $meta_key The meta key to check.
	 * @return bool True if the key exists and has a non-empty value, false otherwise.
	 */
	public function has_meta( int $post_id, string $meta_key ): bool {
		$value = $this->get_meta( $post_id, $meta_key, true );
		return ! empty( $value );
	}

	/**
	 * Save or update post custom meta.
	 *
	 * @param int    $post_id    The post ID.
	 * @param string $meta_key   Detail table column name.
	 * @param mixed  $meta_value Detail table column value.
	 * @return bool True on success, false on failure.
	 */
	public function save_meta( int $post_id, string $meta_key, $meta_value ): bool {
		if ( ! $meta_key || ! $post_id ) {
			return false;
		}

		$post_type = get_post_type( $post_id );

		if ( ! $post_type ) {
			return false;
		}

		$result = $this->repository->save_meta( $post_id, $meta_key, $meta_value, $post_type );

		// Clear the post cache.
		if ( $result ) {
			wp_cache_delete( 'gd_post_' . $post_id, 'gd_post' );
		}

		return $result;
	}

	/**
	 * Delete post custom meta.
	 *
	 * @param int          $post_id   The post ID.
	 * @param string|array $meta_keys Detail table column name(s).
	 * @return bool True on success, false on failure.
	 */
	public function delete_meta( int $post_id, $meta_keys ): bool {
		if ( ! $post_id || empty( $meta_keys ) ) {
			return false;
		}

		$post_type = get_post_type( $post_id );

		if ( ! $post_type ) {
			return false;
		}

		$result = $this->repository->delete_meta( $post_id, $meta_keys, $post_type );

		// Clear the post cache.
		if ( $result ) {
			wp_cache_delete( 'gd_post_' . $post_id, 'gd_post' );
		}

		return $result;
	}

	/**
	 * Setup $gd_post global variable.
	 *
	 * @param int|object $the_post The post ID or post object.
	 * @return void
	 */
	public function setup_postdata( $the_post ): void {
		global $post;

		if ( is_int( $the_post ) && $the_post > 0 ) {
			$the_post = $this->get_info( $the_post );
		} elseif ( is_object( $the_post ) ) {
			if ( ! isset( $the_post->post_category ) ) {
				$post_id  = isset( $the_post->ID ) ? $the_post->ID : ( ! empty( $post->ID ) ? $post->ID : 0 );
				$the_post = $this->get_info( $post_id );
			}
		}

		if ( empty( $the_post->ID ) ) {
			return;
		}

		$GLOBALS['gd_post'] = $the_post;

		if ( empty( $post ) ) {
			$post = get_post( $the_post->ID );
			setup_postdata( $post );
			$GLOBALS['post'] = $post;
		} elseif ( ! empty( $post ) && $post->ID !== $the_post->ID ) {
			setup_postdata( $the_post->ID );
			if ( $post->ID !== $the_post->ID ) {
				$GLOBALS['post'] = get_post( $the_post->ID );
			}
		}
	}

	/**
	 * Get listing author id.
	 *
	 * @param int $post_id The post ID. If empty, tries to get from request.
	 * @return int The author ID or 0 if not found.
	 */
	public function get_author_id( int $post_id = 0 ): int {
		if ( ! $post_id ) {
			if ( isset( $_REQUEST['pid'] ) && $_REQUEST['pid'] !== '' ) {
				$post_id = absint( $_REQUEST['pid'] );
			}
		}

		if ( ! $post_id ) {
			return 0;
		}

		$listing = get_post( $post_id );

		return $listing ? (int) $listing->post_author : 0;
	}

	/**
	 * Check whether a listing belongs to a user or not.
	 *
	 * @param int $post_id The post ID.
	 * @param int $user_id The user ID.
	 * @return bool True if listing belongs to user, false otherwise.
	 */
	public function belongs_to_user( int $post_id, int $user_id ): bool {
		if ( ! $post_id || ! $user_id ) {
			return false;
		}

		$author_id = $this->get_author_id( $post_id );

		return $author_id === $user_id;
	}

	/**
	 * Check whether a listing belongs to current user or not.
	 *
	 * @param int  $post_id       The post ID.
	 * @param bool $exclude_admin Do you want to exclude admin from the check? Default true.
	 * @return bool True if listing belongs to current user, false otherwise.
	 */
	public function belongs_to_current_user( int $post_id = 0, bool $exclude_admin = true ): bool {
		global $current_user;

		if ( $exclude_admin ) {
			foreach ( $current_user->caps as $key => $caps ) {
				if ( strtolower( $key ) === 'administrator' ) {
					return true;
				}
			}
		}

		$belongs = $this->belongs_to_user( $post_id, (int) $current_user->ID );

		/**
		 * Filter whether a listing belongs to current user or not.
		 *
		 * @since 2.0.0.65
		 * @since 3.0.0 Moved to Posts service.
		 *
		 * @param bool $belongs       True if listing belongs to current user, false otherwise.
		 * @param int  $post_id       The post ID.
		 * @param bool $exclude_admin If true, excludes admin from the check.
		 */
		return apply_filters( 'geodir_listing_belong_to_current_user', $belongs, $post_id, $exclude_admin );
	}

	/**
	 * Replace custom variables in text.
	 *
	 * @param string $text    The text containing variables to replace.
	 * @param int    $post_id The post ID (currently unused, uses global $gd_post).
	 * @return string The text with variables replaced.
	 */
	public function replace_variables( string $text, int $post_id = 0 ): string {
		global $gd_post;

		// Only run if we have a GD post and the start of a var.
		if ( empty( $gd_post->ID ) || strpos( $text, '%%' ) === false ) {
			return $text;
		}

		$non_replace = \AyeCode\GeoDirectory\Core\Utils\PostMeta::get_no_replace_fields();

		foreach ( $gd_post as $key => $val ) {
			if ( in_array( $key, $non_replace, true ) ) {
				continue;
			}

			// Replace plain variables.
			if ( strpos( $text, '%%' . $key . '%%' ) !== false ) {
				/**
				 * Filter variable replacement value.
				 *
				 * @since 2.0.0
				 * @since 3.0.0 Moved to Posts service.
				 *
				 * @param mixed  $val  The field value.
				 * @param string $text The full text being processed.
				 */
				$val  = apply_filters( 'geodir_replace_variables_' . $key, $val, $text );
				$text = str_replace( '%%' . $key . '%%', $val, $text );
			}

			// Replace encoded variables.
			if ( strpos( $text, '%%' . $key . '_encode%%' ) !== false ) {
				$encode_val = ! empty( $val ) ? urlencode( trim( $val ) ) : '';
				/**
				 * Filter encoded variable replacement value.
				 *
				 * @since 2.0.0
				 * @since 3.0.0 Moved to Posts service.
				 *
				 * @param string $encode_val The encoded value.
				 * @param string $text       The full text being processed.
				 */
				$encode_val = apply_filters( 'geodir_replace_variables_encode_' . $key, $encode_val, $text );
				$text       = str_replace( '%%' . $key . '_encode%%', $encode_val, $text );
			}
		}

		return $text;
	}

	/**
	 * Returns the edit post link.
	 *
	 * @param int $post_id The post ID. If empty, uses current post.
	 * @return string The edit post URL.
	 */
	public function get_edit_link( int $post_id = 0 ): string {
		if ( ! $post_id ) {
			global $post;
			$post_id = ! empty( $post->ID ) ? $post->ID : 0;
		}

		if ( ! $post_id ) {
			return '';
		}

		$post_type = get_post_type( $post_id );

		if ( ! $post_type || ! function_exists( 'geodir_add_listing_page_url' ) ) {
			return '';
		}

		return geodir_add_listing_page_url( $post_type, $post_id );
	}

	/**
	 * Returns package information as an object.
	 *
	 * @param object|int $post      The post object or post ID.
	 * @param string     $post_type The post type.
	 * @return object Package info object.
	 */
	public function get_package( $post = '', string $post_type = '' ): object {
		$package = [
			'id' => 0,
		];

		/**
		 * Filter post package information.
		 *
		 * @since 2.0.0
		 * @since 3.0.0 Moved to Posts service.
		 *
		 * @param object        $package   The package object.
		 * @param object|string $post      The post object or post ID.
		 * @param string        $post_type The post type.
		 */
		return (object) apply_filters( 'geodir_get_post_package', (object) $package, $post, $post_type );
	}

	/**
	 * Returns package ID.
	 *
	 * @param object|int $post      The post object or post ID.
	 * @param string     $post_type The post type.
	 * @return int The package ID.
	 */
	public function get_package_id( $post = '', string $post_type = '' ): int {
		$package = $this->get_package( $post, $post_type );

		return ! empty( $package->id ) ? (int) $package->id : 0;
	}

	/**
	 * Check if a post has a private address.
	 *
	 * @since 3.0.0
	 *
	 * @param int|object|array $post The post ID, post object, or post array.
	 * @return bool True if the post has a private address, false otherwise.
	 */
	public function has_private_address( $post ): bool {
		global $geodir_private_address;

		if ( empty( $post ) ) {
			return false;
		}

		// Normalize post to object
		if ( is_array( $post ) ) {
			$gd_post = (object) $post;
		} elseif ( is_scalar( $post ) ) {
			$gd_post = $this->get_info( absint( $post ) );
		} else {
			$gd_post = $post;
		}

		// Check for a valid post
		if ( ! ( is_object( $gd_post ) && ! empty( $gd_post->ID ) && ! empty( $gd_post->post_type ) ) ) {
			return false;
		}

		$is_private = false;

		// Cache the value
		if ( empty( $geodir_private_address ) || ! is_array( $geodir_private_address ) ) {
			$geodir_private_address = array();
		}

		if ( isset( $geodir_private_address[ $gd_post->ID ] ) ) {
			$is_private = $geodir_private_address[ $gd_post->ID ];
		} else {
			// Check private address enabled or not
			if ( $this->post_types->supports( $gd_post->post_type, 'private_address' ) ) {
				if ( empty( $gd_post->post_id ) ) {
					$gd_post = $this->get_info( $gd_post->ID );
				}

				if ( ! empty( $gd_post->private_address ) ) {
					$is_private = true;
				}
			}

			$geodir_private_address[ $gd_post->ID ] = $is_private;
		}

		/**
		 * Filters whether post have private address.
		 *
		 * @since 2.1.1.9
		 *
		 * @param bool   $is_private True when post has private address, otherwise false.
		 * @param object $gd_post The post.
		 */
		return apply_filters( 'geodir_post_has_private_address', $is_private, $gd_post );
	}

	/**
	 * Default post status for new posts.
	 *
	 * @return string The default post status.
	 */
	public function get_default_status(): string {
		return class_exists( 'GeoDir_Post_Data' ) ? \GeoDir_Post_Data::get_post_default_status() : 'draft';
	}
}
