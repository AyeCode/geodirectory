<?php
/**
 * GeoDirectory Post Meta Utility Class
 *
 * Pure utility functions for post meta field definitions and configurations.
 * These are static helpers with no state or database operations.
 *
 * @package GeoDirectory\Core\Utils
 * @since   3.0.0
 * @author  AyeCode Ltd
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core\Utils;

/**
 * A container for post meta-related pure utility functions.
 *
 * @since 3.0.0
 */
final class PostMeta {
	/**
	 * Get the post meta standard fields.
	 *
	 * Returns field definitions for standard GeoDirectory post meta fields
	 * like default_category, overall_rating, post_date, etc.
	 *
	 * @param string $post_type The post type. Default 'gd_place'.
	 * @return array Standard fields configuration array.
	 */
	public static function get_standard_fields( string $post_type = 'gd_place' ): array {
		$fields = [
			'default_category' => [
				'type'           => 'custom',
				'name'           => 'default_category',
				'htmlvar_name'   => 'default_category',
				'frontend_title' => __( 'Default Category', 'geodirectory' ),
				'field_icon'     => 'fas fa-folder-open',
				'field_type_key' => '',
				'css_class'      => '',
				'extra_fields'   => '',
			],
			'overall_rating' => [
				'type'           => 'custom',
				'name'           => 'overall_rating',
				'htmlvar_name'   => 'overall_rating',
				'frontend_title' => __( 'Overall Rating', 'geodirectory' ),
				'field_icon'     => 'fas fa-star',
				'field_type_key' => '',
				'css_class'      => '',
				'extra_fields'   => '',
			],
			'rating_count' => [
				'type'           => 'custom',
				'name'           => 'rating_count',
				'htmlvar_name'   => 'rating_count',
				'frontend_title' => __( 'Rating Count', 'geodirectory' ),
				'field_icon'     => 'fas fa-comments',
				'field_type_key' => '',
				'css_class'      => '',
				'extra_fields'   => '',
			],
			'post_type' => [
				'type'           => 'custom',
				'name'           => 'post_type',
				'htmlvar_name'   => 'post_type',
				'frontend_title' => __( 'Post Type', 'geodirectory' ),
				'field_icon'     => 'fas fa-list',
				'field_type_key' => '',
				'css_class'      => '',
				'extra_fields'   => '',
			],
			'post_date' => [
				'name'           => 'post_date',
				'htmlvar_name'   => 'post_date',
				'frontend_title' => __( 'Published', 'geodirectory' ),
				'type'           => 'datepicker',
				'field_icon'     => 'fas fa-calendar-alt',
				'field_type_key' => '',
				'css_class'      => '',
				'extra_fields'   => [ 'date_format' => function_exists( 'geodir_date_format' ) ? geodir_date_format() : 'Y-m-d' ],
			],
			'post_date_gmt' => [
				'name'           => 'post_date_gmt',
				'htmlvar_name'   => 'post_date_gmt',
				'frontend_title' => __( 'Published', 'geodirectory' ),
				'type'           => 'datepicker',
				'field_icon'     => 'fas fa-calendar-alt',
				'field_type_key' => '',
				'css_class'      => '',
				'extra_fields'   => [ 'date_format' => function_exists( 'geodir_date_format' ) ? geodir_date_format() : 'Y-m-d' ],
			],
			'post_modified' => [
				'name'           => 'post_modified',
				'htmlvar_name'   => 'post_modified',
				'frontend_title' => __( 'Modified', 'geodirectory' ),
				'type'           => 'datepicker',
				'field_icon'     => 'fas fa-calendar-alt',
				'field_type_key' => '',
				'css_class'      => '',
				'extra_fields'   => [ 'date_format' => function_exists( 'geodir_date_format' ) ? geodir_date_format() : 'Y-m-d' ],
			],
			'post_modified_gmt' => [
				'name'           => 'post_modified_gmt',
				'htmlvar_name'   => 'post_modified_gmt',
				'frontend_title' => __( 'Modified', 'geodirectory' ),
				'type'           => 'datepicker',
				'field_icon'     => 'fas fa-calendar-alt',
				'field_type_key' => '',
				'css_class'      => '',
				'extra_fields'   => [ 'date_format' => function_exists( 'geodir_date_format' ) ? geodir_date_format() : 'Y-m-d' ],
			],
			'post_author' => [
				'name'           => 'post_author',
				'htmlvar_name'   => 'post_author',
				'frontend_title' => __( 'Author', 'geodirectory' ),
				'type'           => 'author',
				'field_icon'     => 'fas fa-user',
				'field_type_key' => '',
				'css_class'      => '',
				'extra_fields'   => '',
			],
			'post_status' => [
				'type'           => 'custom',
				'name'           => 'post_status',
				'htmlvar_name'   => 'post_status',
				'frontend_title' => __( 'Status', 'geodirectory' ),
				'field_icon'     => 'fas fa-play',
				'field_type_key' => '',
				'css_class'      => '',
				'extra_fields'   => '',
			],
			'post_id' => [
				'type'           => 'custom',
				'name'           => 'post_id',
				'htmlvar_name'   => 'post_id',
				'frontend_title' => __( 'ID', 'geodirectory' ),
				'field_icon'     => 'fas fa-thumbtack',
				'field_type_key' => '',
				'css_class'      => '',
				'extra_fields'   => '',
			],
			'post_link' => [
				'type'           => 'custom',
				'name'           => 'post_link',
				'htmlvar_name'   => 'post_link',
				'frontend_title' => __( 'Post Link', 'geodirectory' ),
				'field_icon'     => 'fas fa-link',
				'field_type_key' => '',
				'css_class'      => '',
				'extra_fields'   => '',
			],
		];

		/**
		 * Filter the post meta standard fields info.
		 *
		 * @since 2.0.0.49
		 * @since 3.0.0 Moved to PostMeta utils class.
		 *
		 * @param array  $fields    Standard fields array.
		 * @param string $post_type The post type.
		 */
		return apply_filters( 'geodir_post_meta_standard_fields', $fields, $post_type );
	}

	/**
	 * Get the post meta address fields.
	 *
	 * Returns field definitions for address-related fields like street,
	 * city, region, country, latitude, longitude, etc.
	 *
	 * @param string $post_type The post type. Default 'gd_place'.
	 * @return array Address fields configuration array.
	 */
	public static function get_address_fields( string $post_type = 'gd_place' ): array {
		static $cached_fields = [];

		if ( empty( $post_type ) ) {
			$post_type = 'gd_place';
		} elseif ( class_exists( 'GeoDir_Post_types' ) && ! \GeoDir_Post_types::supports( $post_type, 'location' ) ) {
			return [];
		}

		// Return cached fields.
		if ( ! empty( $cached_fields[ $post_type ] ) ) {
			return $cached_fields[ $post_type ];
		}

		$field        = function_exists( 'geodir_get_field_infoby' ) ? geodir_get_field_infoby( 'htmlvar_name', 'address', $post_type, false ) : [];
		$extra_fields = ! empty( $field['extra_fields'] ) ? stripslashes_deep( maybe_unserialize( $field['extra_fields'] ) ) : null;
		$field_icon   = ! empty( $field['field_icon'] ) ? $field['field_icon'] : 'fas fa-map-marker-alt';

		$fields = [
			'street' => [
				'type'           => 'custom',
				'name'           => 'street',
				'htmlvar_name'   => 'street',
				'frontend_title' => ( ! empty( $field['frontend_title'] ) ? __( $field['frontend_title'], 'geodirectory' ) : __( 'Address', 'geodirectory' ) ),
				'field_icon'     => $field_icon,
				'field_type_key' => '',
				'css_class'      => '',
				'extra_fields'   => '',
			],
			'street2' => [
				'type'           => 'custom',
				'name'           => 'street2',
				'htmlvar_name'   => 'street2',
				'frontend_title' => __( 'Address line 2', 'geodirectory' ),
				'field_icon'     => $field_icon,
				'field_type_key' => '',
				'css_class'      => '',
				'extra_fields'   => '',
			],
			'city' => [
				'type'           => 'custom',
				'name'           => 'city',
				'htmlvar_name'   => 'city',
				'frontend_title' => ( ! empty( $extra_fields['city_lable'] ) ? __( $extra_fields['city_lable'], 'geodirectory' ) : __( 'City', 'geodirectory' ) ),
				'field_icon'     => $field_icon,
				'field_type_key' => '',
				'css_class'      => '',
				'extra_fields'   => '',
			],
			'region' => [
				'type'           => 'custom',
				'name'           => 'region',
				'htmlvar_name'   => 'region',
				'frontend_title' => ( ! empty( $extra_fields['region_lable'] ) ? __( $extra_fields['region_lable'], 'geodirectory' ) : __( 'Region', 'geodirectory' ) ),
				'field_icon'     => $field_icon,
				'field_type_key' => '',
				'css_class'      => '',
				'extra_fields'   => '',
			],
			'country' => [
				'type'           => 'custom',
				'name'           => 'country',
				'htmlvar_name'   => 'country',
				'frontend_title' => ( ! empty( $extra_fields['country_lable'] ) ? __( $extra_fields['country_lable'], 'geodirectory' ) : __( 'Country', 'geodirectory' ) ),
				'field_icon'     => $field_icon,
				'field_type_key' => '',
				'css_class'      => '',
				'extra_fields'   => '',
			],
			'zip' => [
				'type'           => 'custom',
				'name'           => 'zip',
				'htmlvar_name'   => 'zip',
				'frontend_title' => ( ! empty( $extra_fields['zip_lable'] ) ? __( $extra_fields['zip_lable'], 'geodirectory' ) : __( 'Zip/Post Code', 'geodirectory' ) ),
				'field_icon'     => $field_icon,
				'field_type_key' => '',
				'css_class'      => '',
				'extra_fields'   => '',
			],
			'neighbourhood' => [
				'type'           => 'custom',
				'name'           => 'neighbourhood',
				'htmlvar_name'   => 'neighbourhood',
				'frontend_title' => __( 'Neighbourhood', 'geodirectory' ),
				'field_icon'     => $field_icon,
				'field_type_key' => '',
				'css_class'      => '',
				'extra_fields'   => '',
			],
			'latitude' => [
				'type'           => 'custom',
				'name'           => 'latitude',
				'htmlvar_name'   => 'latitude',
				'frontend_title' => __( 'Latitude', 'geodirectory' ),
				'field_icon'     => $field_icon,
				'field_type_key' => '',
				'css_class'      => '',
				'extra_fields'   => '',
			],
			'longitude' => [
				'type'           => 'custom',
				'name'           => 'longitude',
				'htmlvar_name'   => 'longitude',
				'frontend_title' => __( 'Longitude', 'geodirectory' ),
				'field_icon'     => $field_icon,
				'field_type_key' => '',
				'css_class'      => '',
				'extra_fields'   => '',
			],
			'map_directions' => [
				'type'           => 'custom',
				'name'           => 'map_directions',
				'htmlvar_name'   => 'map_directions',
				'frontend_title' => __( 'Map Directions', 'geodirectory' ),
				'field_icon'     => 'fas fa-directions',
				'field_type_key' => '',
				'css_class'      => '',
				'extra_fields'   => '',
			],
		];

		/**
		 * Filter the post meta address fields.
		 *
		 * @since 2.0.0.86
		 * @since 3.0.0 Moved to PostMeta utils class.
		 *
		 * @param array  $fields    Address fields array.
		 * @param string $post_type The post type.
		 */
		$fields = apply_filters( 'geodir_post_meta_address_fields', $fields, $post_type );

		$cached_fields[ $post_type ] = $fields;

		return $fields;
	}

	/**
	 * Get the post meta advance custom fields.
	 *
	 * This returns a merged array of standard and address fields.
	 *
	 * @param string $post_type The post type. Default 'gd_place'.
	 * @return array Advance fields (standard + address) array.
	 */
	public static function get_advance_fields( string $post_type = 'gd_place' ): array {
		$fields = [];

		// Standard fields.
		$standard_fields = self::get_standard_fields( $post_type );
		if ( ! empty( $standard_fields ) ) {
			$fields = $standard_fields;
		}

		// Address fields.
		$address_fields = self::get_address_fields( $post_type );
		if ( ! empty( $address_fields ) ) {
			$fields = ! empty( $fields ) ? array_merge( $fields, $address_fields ) : $address_fields;
		}

		/**
		 * Filter the post meta advance fields.
		 *
		 * @since 2.0.0.86
		 * @since 3.0.0 Moved to PostMeta utils class.
		 *
		 * @param array  $fields    Advance fields array.
		 * @param string $post_type The post type.
		 */
		return apply_filters( 'geodir_post_meta_advance_fields', $fields, $post_type );
	}

	/**
	 * Get list of fields that should not be auto replaced.
	 *
	 * These fields contain sensitive data that should not be used in
	 * variable replacement operations.
	 *
	 * @return array Field names that should not be replaced.
	 */
	public static function get_no_replace_fields(): array {
		return [
			'post_password',
			'submit_ip',
		];
	}
}
