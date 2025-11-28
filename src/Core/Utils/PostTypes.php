<?php
/**
 * GeoDirectory Post Types Utility Class
 *
 * Pure utility functions for post type data transformation and formatting.
 * For stateful operations, DB queries, and caching, use PostTypes Service.
 *
 * @package GeoDirectory\Core\Utils
 * @since   3.0.0
 * @author  AyeCode Ltd
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core\Utils;

use stdClass;

/**
 * A container for post type-related pure utility functions.
 *
 * @since 3.0.0
 */
final class PostTypes {

	/**
	 * Get post type options array.
	 *
	 * @since 2.0.0
	 * @since 3.0.0 Moved to Utils, renamed from post_type_options().
	 *
	 * @param array|object $post_types Post types data.
	 * @param bool $plural_name True to get plural post type name. Default false.
	 * @param bool $translated True to get translated name. Default false.
	 * @return array GD post types options array.
	 */
	public static function get_options( $post_types, bool $plural_name = false, bool $translated = false ): array {
		if ( is_array( $post_types ) ) {
			$post_types = json_decode( json_encode( $post_types ), FALSE );
		}

		$options = array();
		if ( ! empty( $post_types ) ) {
			foreach ( $post_types as $key => $post_type_obj ) {
				$name = $plural_name ? $post_type_obj->labels->name : $post_type_obj->labels->singular_name;
				if ( $translated ) {
					$name = __( $name, 'geodirectory' );
				}
				$options[ $key ] = $name;
			}

			if ( ! empty( $options ) ) {
				$options = array_unique( $options );
			}
		}

		return $options;
	}

	/**
	 * Check given post type is GD post type or not.
	 *
	 * @since 2.0.0
	 * @since 3.0.0 Moved to Utils, renamed from is_gd_post_type().
	 *
	 * @param string $post_type The post type.
	 * @param array $valid_types Array of valid GD post types.
	 * @return bool True if given post type is GD post type, otherwise False.
	 */
	public static function is_geodirectory( string $post_type, array $valid_types ): bool {
		if ( empty( $post_type ) || is_array( $post_type ) ) {
			return false;
		}

		if ( strpos( $post_type, 'gd_' ) !== 0 ) {
			return false;
		}

		return in_array( $post_type, $valid_types );
	}

	/**
	 * Get posttype object by posttype.
	 *
	 * @since 2.0.0
	 * @since 3.0.0 Moved to Utils, renamed from post_type_object().
	 *
	 * @param string $post_type Get post type.
	 * @param array $gd_post_types GD post types array.
	 * @return object|null $post_type_obj.
	 */
	public static function get_object( string $post_type, array $gd_post_types ) {
		if ( self::is_geodirectory( $post_type, array_keys( $gd_post_types ) ) ) {
			$post_types = json_decode( json_encode( $gd_post_types ), FALSE );
			$post_type_obj = ! empty( $post_types->{$post_type} ) ? $post_types->{$post_type} : NULL;
		} else {
			$post_type_obj = get_post_type_object( $post_type );
		}

		return $post_type_obj;
	}

	/**
	 * Get posttype name by posttype.
	 *
	 * Check if $translated is true then post name get in translated
	 * else post name without translated.
	 *
	 * @since 2.0.0
	 * @since 3.0.0 Moved to Utils, renamed from post_type_name().
	 *
	 * @param string $post_type Get posttype.
	 * @param array $gd_post_types GD post types array.
	 * @param bool $translated Optional. Default false.
	 * @return string Posttype name.
	 */
	public static function get_name( string $post_type, array $gd_post_types, bool $translated = false ): string {
		$post_type_obj = self::get_object( $post_type, $gd_post_types );

		if ( ! ( ! empty( $post_type_obj ) && ! empty( $post_type_obj->labels->name ) ) ) {
			return $post_type;
		}

		$name = $post_type_obj->labels->name;
		if ( $translated ) {
			$name = __( $name, 'geodirectory' );
		}

		return apply_filters( 'geodir_post_type_name', $name, $post_type, $translated );
	}

	/**
	 * Get the posttype singular name by posttype.
	 *
	 * Check if $translated is true then display translated singular name
	 * else without translated name.
	 *
	 * @since 2.0.0
	 * @since 3.0.0 Moved to Utils, renamed from post_type_singular_name().
	 *
	 * @param string $post_type Get posttype.
	 * @param array $gd_post_types GD post types array.
	 * @param bool $translated Optional. Default false.
	 * @return string posttype singular name.
	 */
	public static function get_singular_name( string $post_type, array $gd_post_types, bool $translated = false ): string {
		$post_type_obj = self::get_object( $post_type, $gd_post_types );

		if ( ! ( ! empty( $post_type_obj ) && ! empty( $post_type_obj->labels->singular_name ) ) ) {
			return $post_type;
		}

		$singular_name = $post_type_obj->labels->singular_name;
		if ( $translated ) {
			$singular_name = __( $singular_name, 'geodirectory' );
		}

		return apply_filters( 'geodir_post_type_singular_name', $singular_name, $post_type, $translated );
	}

	/**
	 * Get the listing owner label for the post type.
	 *
	 * @since 2.3.7
	 * @since 3.0.0 Moved to Utils, renamed from listing_owner_label().
	 *
	 * @param string $post_type The post type.
	 * @param array $gd_post_types GD post types array.
	 * @param bool $translated Optional. Default true.
	 * @return string Listing owner label.
	 */
	public static function get_owner_label( string $post_type, array $gd_post_types, bool $translated = true ): string {
		$label = 'Listing Owner';

		if ( ! empty( $post_type ) ) {
			$post_type_obj = self::get_object( $post_type, $gd_post_types );

			if ( ! empty( $post_type_obj ) && ! empty( $post_type_obj->labels->listing_owner ) ) {
				$label = $post_type_obj->labels->listing_owner;
			}
		}

		if ( $translated ) {
			$label = __( $label, 'geodirectory' );
		}

		/**
		 * Filter the listing owner label for the post type.
		 *
		 * @since 2.3.7
		 *
		 * @param string $label Listing owner label.
		 * @param string $post_type The post type.
		 * @param bool $translated Optional. Default true.
		 */
		return apply_filters( 'geodir_listing_owner_label', $label, $post_type, $translated );
	}

	/**
	 * Get and array of CPTs allowed to be added from the frontend.
	 *
	 * @since 3.0.0 Moved to Utils, renamed from add_listing_allowed_post_types().
	 *
	 * @param array $post_types Post types array.
	 * @return array Allowed post types.
	 */
	public static function get_add_listing_allowed( array $post_types ): array {
		$allowed_post_types = array();
		if ( ! empty( $post_types ) ) {
			foreach ( $post_types as $cpt => $post_type ) {
				if ( ! isset( $post_type['disable_frontend_add'] ) || $post_type['disable_frontend_add'] == '0' ) {
					$allowed_post_types[] = $cpt;
				}
			}
		}

		return apply_filters( 'geodir_add_listing_allowed_post_types', $allowed_post_types );
	}

	/**
	 * Get default listing posttype for add listing.
	 *
	 * @since 2.0.0
	 * @since 3.0.0 Moved to Utils, renamed from add_listing_default_post_type().
	 *
	 * @param array $allowed_post_types Allowed post types.
	 * @return string Default post type.
	 */
	public static function get_add_listing_default( array $allowed_post_types ): string {
		$post_type = ! empty( $allowed_post_types ) && is_array( $allowed_post_types ) ? $allowed_post_types[0] : '';

		return apply_filters( 'geodir_add_listing_default_post_type', $post_type );
	}

	/**
	 * Function for check listing posttype is allowed for add listing.
	 *
	 * @since 2.0.0
	 * @since 3.0.0 Moved to Utils, renamed from add_listing_check_post_type().
	 *
	 * @param string $post_type Get posttype.
	 * @param array $valid_types Array of valid GD post types.
	 * @param array $allowed_post_types Allowed post types for add listing.
	 * @return bool True if allowed.
	 */
	public static function is_add_listing_allowed( string $post_type, array $valid_types, array $allowed_post_types ): bool {
		if ( ! self::is_geodirectory( $post_type, $valid_types ) ) {
			return false;
		}

		if ( ! empty( $allowed_post_types ) && is_array( $allowed_post_types ) && in_array( $post_type, $allowed_post_types ) ) {
			$return = true;
		} else {
			$return = false;
		}

		return apply_filters( 'geodir_add_listing_check_post_type', $return, $post_type );
	}

	/**
	 * Get default search post type.
	 *
	 * @since 2.1.0.17
	 * @since 3.0.0 Moved to Utils, renamed from search_default_post_type().
	 *
	 * @param array $post_types Post types array (names only).
	 * @return string Default post type.
	 */
	public static function get_search_default( array $post_types ): string {
		$post_type = ! empty( $post_types ) && is_array( $post_types ) ? $post_types[0] : 'gd_place';

		return apply_filters( 'geodir_search_default_post_type', $post_type );
	}

	/**
	 * Get the post types that have favourites enabled.
	 *
	 * @since 2.0.0
	 * @since 3.0.0 Moved to Utils, renamed from fav_allowed_post_types().
	 *
	 * @param array $post_types Post types array.
	 * @return array Allowed CPTs.
	 */
	public static function get_favorites_allowed( array $post_types ): array {
		$allowed_cpts = array();
		if ( ! empty( $post_types ) ) {
			foreach ( $post_types as $cpt => $postype ) {
				if ( ! isset( $postype['disable_favorites'] ) || ! $postype['disable_favorites'] ) {
					$allowed_cpts[] = $cpt;
				}
			}
		}

		return $allowed_cpts;
	}

	/**
	 * Returns the post type link with location parameters.
	 *
	 * @since 1.0.0
	 * @since 1.5.5 Fixed post type archive link for selected location.
	 * @since 3.0.0 Moved to Utils, renamed from posttype_link().
	 *
	 * @param string $link The post link.
	 * @param string $post_type The post type.
	 * @param array $valid_types Array of valid GD post types.
	 * @return string The modified link.
	 */
	public static function build_link( string $link, string $post_type, array $valid_types ): string {
		if ( in_array( $post_type, $valid_types ) ) {
			if ( get_option( 'geodir_add_location_url' ) ) {
				if ( geodir_is_page( 'detail' ) && ! empty( $GLOBALS['post'] ) && isset( $GLOBALS['post']->country_slug ) ) {
					$location_terms = array(
						'gd_country' => $GLOBALS['post']->country_slug,
						'gd_region' => $GLOBALS['post']->region_slug,
						'gd_city' => $GLOBALS['post']->city_slug
					);
				} else {
					$location_terms = geodir_get_current_location_terms( 'query_vars' );
				}

				$location_terms = geodir_remove_location_terms( $location_terms );

				if ( ! empty( $location_terms ) ) {
					if ( get_option( 'permalink_structure' ) != '' ) {
						$location_terms = implode( "/", $location_terms );
						$location_terms = rtrim( $location_terms, '/' );

						$link .= urldecode( $location_terms ) . '/';
					} else {
						$link = geodir_getlink( $link, $location_terms );
					}
				}
			}
		}

		return $link;
	}

	/**
	 * Get post type singular label.
	 *
	 * @since 2.1.0.5
	 * @since 3.0.0 Moved to Utils, renamed from get_post_type_singular_label().
	 *
	 * @param string $post_type The post type.
	 * @param bool $translate Returns translated label if True. Default false.
	 * @return string Label.
	 */
	public static function get_singular_label( string $post_type, bool $translate = false ): string {
		$obj_post_type = get_post_type_object( $post_type );
		if ( ! is_object( $obj_post_type ) ) {
			return '';
		}

		$label = $translate ? __( $obj_post_type->labels->singular_name, 'geodirectory' ) : $obj_post_type->labels->singular_name;

		return $label;
	}

	/**
	 * Get post type plural label.
	 *
	 * @since 2.1.0.5
	 * @since 3.0.0 Moved to Utils, renamed from get_post_type_plural_label().
	 *
	 * @param string $post_type The post type.
	 * @param array $valid_types Array of valid GD post types.
	 * @param bool $translate Returns translated label if True. Default false.
	 * @return string|bool Label or false.
	 */
	public static function get_plural_label( string $post_type, array $valid_types, bool $translate = false ) {
		if ( ! in_array( $post_type, $valid_types ) ) {
			return false;
		}

		$obj_post_type = get_post_type_object( $post_type );

		$label = $translate ? __( $obj_post_type->labels->name, 'geodirectory' ) : $obj_post_type->labels->name;

		return $label;
	}

	/**
	 * Custom post type messages for admin actions.
	 *
	 * Replaces "Post" in the update messages for custom post types on the "Edit" post screen.
	 * For example "Post updated. View Post." becomes "Place updated. View Place".
	 *
	 * @since 1.0.0
	 * @since 3.0.0 Moved to Utils, renamed from custom_update_messages().
	 *
	 * @param array $messages Messages.
	 * @return array $messages.
	 */
	public static function get_update_messages( array $messages ): array {
		$post = $GLOBALS['post'];
		$post_ID = $GLOBALS['post_ID'];

		$post_types = get_post_types( array( 'show_ui' => true, '_builtin' => false ), 'objects' );

		foreach ( $post_types as $post_type => $post_object ) {
			$messages[ $post_type ] = array(
				0 => '', // Unused. Messages start at index 1.
				1 => sprintf( __( '%s updated. <a href="%s">View %s</a>', 'geodirectory' ), $post_object->labels->singular_name, esc_url( get_permalink( $post_ID ) ), $post_object->labels->singular_name ),
				2 => __( 'Custom field updated.', 'geodirectory' ),
				3 => __( 'Custom field deleted.', 'geodirectory' ),
				4 => sprintf( __( '%s updated.', 'geodirectory' ), $post_object->labels->singular_name ),
				5 => isset( $_GET['revision'] ) ? sprintf( __( '%s restored to revision from %s', 'geodirectory' ), $post_object->labels->singular_name, wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
				6 => sprintf( __( '%s published. <a href="%s">View %s</a>', 'geodirectory' ), $post_object->labels->singular_name, esc_url( get_permalink( $post_ID ) ), $post_object->labels->singular_name ),
				7 => sprintf( __( '%s saved.', 'geodirectory' ), $post_object->labels->singular_name ),
				8 => sprintf( __( '%s submitted. <a target="_blank" href="%s">Preview %s</a>', 'geodirectory' ), $post_object->labels->singular_name, esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ), $post_object->labels->singular_name ),
				9 => sprintf( __( '%s scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview %s</a>', 'geodirectory' ), $post_object->labels->singular_name, date_i18n( __( 'M j, Y @ G:i', 'geodirectory' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ), $post_object->labels->singular_name ),
				10 => sprintf( __( '%s draft updated. <a target="_blank" href="%s">Preview %s</a>', 'geodirectory' ), $post_object->labels->singular_name, esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ), $post_object->labels->singular_name ),
			);
		}

		return $messages;
	}

	/**
	 * Get the slug for a given CPT.
	 *
	 * @since 3.0.0 Moved to Utils, renamed from get_ctp_slug().
	 *
	 * @param string $post_type The CPT string.
	 * @param array $post_types Post types array.
	 * @return string|bool The slug or false.
	 */
	public static function get_slug( string $post_type, array $post_types ) {
		if ( isset( $post_types[ $post_type ] ) && isset( $post_types[ $post_type ]['rewrite']['slug'] ) && $post_types[ $post_type ]['rewrite']['slug'] ) {
			return $post_types[ $post_type ]['rewrite']['slug'];
		}

		return false;
	}

	/**
	 * Get the show in locations options.
	 *
	 * @since 3.0.0 Moved to Utils, renamed from show_in_locations().
	 *
	 * @param string $field Field name.
	 * @param string $field_type Field type.
	 * @return array Location options.
	 */
	public static function get_location_options( string $field = '', string $field_type = '' ): array {
		/*
		 * We wrap the key values in [] so we can search the DB easier with a LIKE query.
		 */
		$show_in_locations = array(
			"[detail]"    => __( "Details page sidebar", 'geodirectory' ),
			"[listing]"   => __( "Listings page", 'geodirectory' ),
			"[mapbubble]" => __( "Map bubble", 'geodirectory' ),
		);

		/**
		 * Filter the locations array for where to display custom fields.
		 *
		 * @since 1.6.6
		 *
		 * @param array $show_in_locations The array of locations and descriptions.
		 * @param object $field The field being displayed info.
		 * @param string $field The type of field.
		 */
		return apply_filters( 'geodir_show_in_locations', $show_in_locations, $field, $field_type );
	}

	/**
	 * Get the post type rewrite slug.
	 *
	 * @since 3.0.0 Moved to Utils, renamed from cpt_permalink_rewrite_slug().
	 *
	 * @param string $post_type The post type being checked.
	 * @param object|null $post_type_obj The post type object.
	 * @return string The post type slug.
	 */
	public static function get_permalink_slug( string $post_type, $post_type_obj = NULL ): string {
		$slug = self::get_rewrite_slug( $post_type, $post_type_obj );

		return apply_filters( 'geodir_cpt_permalink_rewrite_slug', $slug, $post_type, $post_type_obj );
	}

	/**
	 * Get the post type rewrite slug.
	 *
	 * @param string $post_type The post type being checked.
	 * @param object $post_type_obj   The post type object.
	 * @return string The post type slug.
	 */
	public static function get_rewrite_slug( $post_type, $post_type_obj = NULL ) {
		if ( empty( $post_type_obj ) || ! is_object( $post_type_obj ) ) {
			$post_type_obj = geodir_post_type_object( $post_type );
		}

		$slug = '';
		if ( empty( $post_type_obj ) ) {
			return $slug;
		}

		if ( ! empty( $post_type_obj->rewrite ) ) {
			if ( is_array( $post_type_obj->rewrite ) && ! empty( $post_type_obj->rewrite['slug'] ) ) {
				$slug = trim( $post_type_obj->rewrite['slug'], '/' );
			} else if ( is_object( $post_type_obj->rewrite ) && ! empty( $post_type_obj->rewrite->slug ) ) {
				$slug = trim( $post_type_obj->rewrite->slug, '/' );
			}
		} else {
			if ( ! empty( $post_type_obj->has_archive ) ) {
				$slug = $post_type_obj->has_archive;
			} else if ( ! empty( $post_type_obj->name ) ) {
				$slug = $post_type_obj->name;
			}
		}

		return $slug;
	}

	/**
	 * Get post type with rewrite slug options.
	 *
	 * @since 2.3.70
	 * @since 3.0.0 Moved to Utils, renamed from cpt_rewrite_slug_options().
	 *
	 * @param array $post_types Post types array.
	 * @return array Post type options with slugs.
	 */
	public static function get_rewrite_slugs( array $post_types ): array {
		$options = array();

		if ( ! empty( $post_types ) ) {
			foreach ( $post_types as $key => $info ) {
				$options[ $key ] = $info['rewrite']['slug'];
			}
		}

		return apply_filters( 'geodir_cpt_rewrite_slug_options', $options );
	}

	/**
	 * Display list of sort options available in front end using dropdown.
	 *
	 * @since 1.0.0
	 * @since 3.0.0 Moved to Utils, remains display_sort_options().
	 *
	 * @param string $post_type Post type.
	 * @param array $args Display arguments.
	 */
	public static function display_sort_options( string $post_type, array $args = array() ): void {
		global $wp_query;

		/**
		 * On search pages there should be no sort options, sorting is done by search criteria.
		 *
		 * @since 1.4.4
		 */
		if ( is_search() ) {
			return;
		}

		// Get sort options from service
		$sort_options_raw = geodirectory()->post_types->get_sort_options( $post_type );

		$sort_options = array();

		if ( ! empty( $sort_options_raw ) && count( $sort_options_raw ) > 1 ) {
			foreach ( $sort_options_raw as $sort ) {
				$sort = stripslashes_deep( $sort );

				$sort->frontend_title = __( $sort->frontend_title, 'geodirectory' );

				if ( $sort->htmlvar_name == 'comment_count' ) {
					$sort->htmlvar_name = 'rating_count';
				}

				$sort_options[] = $sort;
			}
		}

		if ( ! empty( $sort_options ) ) {
			$design_style = geodir_design_style();

			$template = $design_style ? $design_style . "/loop/select-sort.php" : "loop/select-sort.php";

			echo geodir_get_template_html( $template, array(
				'sort_options' => $sort_options,
				'args' => $args
			) );
		}
	}
}

add_filter( 'post_updated_messages', 'AyeCode\GeoDirectory\Core\Utils\PostTypes::get_update_messages' );
add_action( 'geodir_extra_loop_actions', 'AyeCode\GeoDirectory\Core\Utils\PostTypes::display_sort_options', 5, 2 );
