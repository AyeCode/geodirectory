<?php
namespace AyeCode\GeoDirectory\Fields;

use AyeCode\GeoDirectory\Core\Container;
use AyeCode\GeoDirectory\Database\Repository\CustomFieldRepository;

class FieldsService {

	/**
	 * @var CustomFieldRepository
	 */
	protected $repository;

	/**
	 * @var FieldRegistry
	 */
	protected $registry;

	public function __construct( CustomFieldRepository $repository, FieldRegistry $registry ) {
		$this->repository = $repository;
		$this->registry   = $registry;
	}

	/**
	 * Get a single field by a specific column value.
	 *
	 * This provides a convenient API for retrieving field data, delegating to the repository.
	 * * Replaces: geodir_get_field_infoby()
	 *
	 * @param string $column       Column name to query by (e.g., 'id', 'htmlvar_name').
	 * @param mixed  $value        Value to search for.
	 * @param string $post_type    Post type slug.
	 * @param bool   $stripslashes Whether to stripslashes the result. Default true.
	 * @return array|false Field data array or false if not found.
	 */
	public function get_field_info( string $column, $value, string $post_type, bool $stripslashes = true ) {
		return $this->repository->get_field_by( $column, $value, $post_type, $stripslashes );
	}

	/**
	 * Get custom fields based on criteria.
	 * * Replaces: geodir_post_custom_fields()
	 *
	 * @param int|string $package_id      Optional. The package ID.
	 * @param string     $default         Optional. 'all', 'default', or 'custom'. Default 'all'.
	 * @param string     $post_type       Optional. The post type slug. Default 'gd_place'.
	 * @param string     $fields_location Optional. Location context for show_in filtering. Default 'none'.
	 *
	 * @return array Array of custom fields with normalized structure.
	 */
	public function get_custom_fields( $package_id = '', $default = 'all', $post_type = 'gd_place', $fields_location = 'none' ) {
		// Build cache key
		$cache_stored = $post_type . '_' . $package_id . '_' . $default . '_' . $fields_location;
		$geodir_post_custom_fields_cache = get_transient( 'geodir_post_custom_fields' );

		if ( ! is_array( $geodir_post_custom_fields_cache ) ) {
			$geodir_post_custom_fields_cache = array();
		}

		// Return cached if available
		if ( ! empty( $geodir_post_custom_fields_cache ) && array_key_exists( $cache_stored, $geodir_post_custom_fields_cache ) ) {
			$custom_fields = $geodir_post_custom_fields_cache[ $cache_stored ];
		} else {
			// Build repository query args
			$args = [
				'post_type' => $post_type,
				'active'    => 1
			];

			// Handle location filtering
			if ( $fields_location !== 'none' ) {
				$args['location'] = $fields_location;
			}

			// Get fields from repository
			$fields = $this->repository->get_fields( $args );

			$custom_fields = array();

			if ( $fields ) {
				foreach ( $fields as $field ) {
					// Handle default/custom filtering
					if ( $default === 'default' && empty( $field['is_default'] ) ) {
						continue;
					} else if ( $default === 'custom' && ! empty( $field['is_default'] ) ) {
						continue;
					}

					// Apply skip filters
					$skip = apply_filters( 'geodir_post_custom_fields_skip_field', false, $field, $package_id, $default, $fields_location );
					$skip = apply_filters( 'geodir_post_custom_fields_skip_field_' . $field['htmlvar_name'], $skip, $field, $package_id, $default, $fields_location );

					if ( $skip ) {
						continue;
					}

					// Normalize field structure to match v2 format
					$custom_field = array(
						"name"      => $field['htmlvar_name'],
						"label"     => ! empty( $field['clabels'] ) ? $field['clabels'] : $field['frontend_title'],
						"default"   => $field['default_value'],
						"type"      => $field['field_type'],
						"desc"      => ! empty( $field['frontend_desc'] ) ? $field['frontend_desc'] : '',
						"post_type" => $field['post_type'],
					);

					// Add options if field has option values
					if ( ! empty( $field['option_values'] ) ) {
						$options = explode( ',', $field['option_values'] );
						$custom_field["options"] = $options;
					}

					// Merge all field data into custom_field
					foreach ( $field as $field_key => $val ) {
						$custom_field[ $field_key ] = $val;
					}

					// Use same array key format as v2: {sort_order}{post_type}{htmlvar_name}
					$custom_fields[ $field['sort_order'] . $field['post_type'] . $field['htmlvar_name'] ] = $custom_field;
				}
			}

			// Cache the results
			$geodir_post_custom_fields_cache[ $cache_stored ] = $custom_fields;
			set_transient( 'geodir_post_custom_fields', $geodir_post_custom_fields_cache, DAY_IN_SECONDS );
		}

		// Apply legacy filter
		if ( has_filter( 'geodir_filter_geodir_post_custom_fields' ) ) {
			$custom_fields = apply_filters( 'geodir_filter_geodir_post_custom_fields', $custom_fields, $package_id, $post_type, $fields_location );
		}

		return $custom_fields;
	}

	/**
	 * Render all custom fields for a specific location/context.
	 * * Replaces: geodir_get_custom_fields_html()
	 *
	 * @param int    $post_id    The ID of the post (if editing) or 0.
	 * @param string $post_type  The CPT slug (gd_place, etc).
	 * @param string $location   Where are we? (listing_form, admin, search).
	 * @param string $package_id Current package ID (for visibility checks).
	 *
	 * @return void (Echoes HTML)
	 */
	public function render_fields( $post_id, $post_type, $location = 'listing', $package_id = '' ) {

		$args = [
			'post_type' => $post_type,
			'package_id'=> $package_id,
			'active'    => 1
		];

		// If specific location is requested (e.g. 'sidebar'), add it to query.
		// If 'admin', we generally want ALL fields so we can edit them.
		if ( $location !== 'admin' && $location !== 'all' ) {
			$args['location'] = $location;
		}

		$fields_data = $this->repository->get_fields( $args );

//		print_r($fields_data);

		// 2. Loop and Render
		foreach ( $fields_data as $field_data ) {

			// Skip logic (Hooks from old functions.php)
			$skip = apply_filters( 'geodir_post_custom_fields_skip_field', false, $field_data, $package_id, 'all', $location );
			if ( $skip ) {
//				echo $field_data['htmlvar_name'].'skip###<br>'."\n\n";
				continue;
			}

			// Resolve Class
			$field_class = $this->registry->get( $field_data['field_type'] );

			if ( $field_class && class_exists( $field_class ) ) {

				// Instantiate the specific Field Type
				$field = new $field_class( $field_data, $post_id );

				// Legacy Hook: Before
				do_action( 'geodir_before_custom_form_field_' . $field_data['htmlvar_name'], $post_type, $package_id, $field_data );

				// RENDER
				// We might want to capture this to a variable if we want to wrap it,
				// but for now we echo to match old behavior.
//				echo $field_data['htmlvar_name'].'###<br>'."\n\n";

				echo $field->render_input();

				// Legacy Hook: After
				do_action( 'geodir_after_custom_form_field_' . $field_data['htmlvar_name'], $post_type, $package_id, $field_data );
			}else{
				echo $field_data['htmlvar_name'].' NO CLASS###'.$field_data['field_type'].'<br>'."\n\n";
			}
		}
	}

	/**
	 * Render field output for displaying on frontend.
	 *
	 * @param array|string $field Field data array or htmlvar_name.
	 * @param mixed        $post  Post ID or post object.
	 * @param array        $args  Output arguments:
	 *                            - 'location' (string): Output location (detail, listing, etc). Default ''.
	 *                            - 'show' (string|array): What to display (icon, label, value, raw, strip, etc). Default ''.
	 *
	 * @return string HTML output.
	 */
	public function render_field_output( $field, $post, $args = [] ) {
		// Parse args with defaults
		$args = wp_parse_args( $args, [
			'location' => '',
			'show'     => '',
		] );

//		echo '###'.$post;

		// this is the default but is not set in widget args
//		if(empty($args['show'])) $args['show'] = 'icon-label-value';
//		print_r($args);
		// Get field data if htmlvar_name provided
		if ( is_string( $field ) ) {
			$post_type = is_object( $post ) && isset( $post->post_type )
				? $post->post_type
				: get_post_type( $post );

			$field_data = $this->get_field_info( 'htmlvar_name', $field, $post_type );

			if ( ! $field_data ) {
				return '';
			}
		} else {
			$field_data = $field;
		}

		// Get post ID
		$post_id = is_object( $post ) && isset( $post->ID ) ? $post->ID : absint( $post );

		if ( empty( $post_id ) ) {
			return '';
		}

		// Try to use global $gd_post if it matches this post (avoids DB call)
		global $gd_post;
		if ( ! empty( $gd_post ) && isset( $gd_post->ID ) && $gd_post->ID == $post_id ) {
			// Use the global $gd_post - already has all custom fields loaded!
			$gd_post_data = $gd_post;
		} else {
			// Need to load it (fallback for cases outside the loop)
			$gd_post_data = geodir_get_post_info( $post_id );
			if ( ! $gd_post_data ) {
				return '';
			}
		}

		// check for custom
		if ( empty( $field_data['field_type'] ) && !empty($field['type']) ) {
//			echo print_r( $field ).'###' . print_r( $field_data );
			$field_data['field_type'] = esc_attr( $field['type'] );
		}

		// Get field type class
//		print_r( $field_data );
		$field_class = $this->registry->get( $field_data['field_type'] );

		if ( ! $field_class || ! class_exists( $field_class ) ) {
			return '';
		}

		// Instantiate field type with $gd_post (has custom fields)
		$field_instance = new $field_class( $field_data, $gd_post_data );

		// Render output - pass $gd_post and args
		return $field_instance->render_output( $gd_post_data, $args );
	}
}
