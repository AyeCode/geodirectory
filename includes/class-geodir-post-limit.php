<?php
/**
 * User posts limit integration class.
 *
 * @since 2.0.0.98
 * @package GeoDirectory
 * @author AyeCode Ltd
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * GeoDir_Post_Limit class.
 */
class GeoDir_Post_Limit {

	/**
	 * Setup.
	 */
	public static function init() {
		// Rest API
		add_filter( 'rest_api_init', array( __CLASS__, 'rest_api_init' ), 20 );

		// Admin CPT settings
		add_filter( 'geodir_pre_add_listing_shortcode_output', array( __CLASS__, 'check_add_listing_output' ), 20, 4 );
	}

	public static function rest_api_init() {
		$post_types = geodir_get_posttypes( 'names' );

		foreach ( $post_types as $post_type ) {
			add_filter( 'rest_pre_insert_' . $post_type, array( __CLASS__, 'check_rest_api_post' ), 20, 2 );
		}
	}

	public static function cpt_posts_limit( $args ) {
		$defaults = array(
			'post_type' => '',
			'post_author' => 0
		);

		$args = wp_parse_args( $args, $defaults );

		$post_type_info = geodir_get_posttype_info( $args['post_type'] );
		$limit = 0;

		if ( ! empty( $post_type_info ) && isset( $post_type_info['limit_posts'] ) ) {
			$limit = (int) $post_type_info['limit_posts'];
		}

		return (int) apply_filters( 'geodir_cpt_posts_limit', $limit, $args );
	}

	public static function cpt_posts_limits( $args = array() ) {
		$defaults = array(
			'post_type' => '',
			'post_author' => 0
		);

		$params = wp_parse_args( $args, $defaults );

		$limits = array(
			'total' => (int) self::cpt_posts_limit( $params )
		);

		return apply_filters( 'geodir_cpt_posts_limits', $limits, $params );
	}

	public static function count_user_cpt_posts( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'post_type' => '',
			'post_author' => null,
			'post_status' => 'any'
		);

		$query_args = wp_parse_args( $args, $defaults );

		$count = 0;

		// Non GD post type.
		if ( ! geodir_is_gd_post_type( $query_args['post_type'] ) ) {
			return $count;
		}

		// Post author
		if ( $query_args['post_author'] === null && is_user_logged_in() ) {
			$query_args['post_author'] = (int) get_current_user_id();
		}

		// Post status
		if ( ! empty( $query_args['post_status'] ) ) {
			if ( is_array( $query_args['post_status'] ) ) {
				$query_args['post_status'] = array_map( 'sanitize_key', $query_args['post_status'] );
			} else {
				$query_args['post_status'] = preg_replace( '|[^a-z0-9_,-]|', '', $query_args['post_status'] );
				$query_args['post_status'] = $query_args['post_status'] == 'any' ? '' : array( $query_args['post_status'] );
			}
		}

		if ( empty( $query_args['post_status'] ) ) {
			$query_args['post_status'] = array_keys( geodir_get_post_statuses( $query_args['post_type'] ) );
		}

		$table = geodir_db_cpt_table( $query_args['post_type'] );
		$query_args['post_status'] = apply_filters( 'geodir_count_user_cpt_posts_statuses', $query_args['post_status'], $query_args, $args );

		$fields = "COUNT(*) AS `num_posts`";
		$fields = apply_filters( 'geodir_count_user_cpt_posts_fields', $fields, $query_args, $args );

		$join = "LEFT JOIN `{$table}` AS `pd` ON `pd`.`post_id` = `p`.`ID`";
		$join = apply_filters( 'geodir_count_user_cpt_posts_join', $join, $query_args, $args );

		$where = $wpdb->prepare( "AND `p`.`post_author` = %d", absint( $query_args['post_author'] ) );
		$where .= " AND `p`.`post_status` IN( '" . implode( "', '", $query_args['post_status'] ) . "' )";
		$where = apply_filters( 'geodir_count_user_cpt_posts_where', $where, $query_args, $args );

		$sql = "SELECT {$fields} FROM `{$wpdb->posts}` AS `p` {$join} WHERE `p`.`post_type` = '" . $query_args['post_type'] . "' {$where}";
		$sql = apply_filters( 'geodir_count_user_cpt_posts_sql', $sql, $query_args );

		$count = (int) $wpdb->get_var( $sql );

		return apply_filters( 'geodir_count_user_cpt_posts', $count, $query_args, $args );
	}

	public static function user_can_add_post( $args = array(), $wp_error = false ) {
		global $wpdb;

		$defaults = array(
			'post_type' => '',
			'post_author' => null
		);

		$params = wp_parse_args( $args, $defaults );

		$can_add = true;

		// Non GD post type.
		if ( ! geodir_is_gd_post_type( $params['post_type'] ) ) {
			return $can_add;
		}

		// Post author
		if ( $params['post_author'] === null && is_user_logged_in() ) {
			$params['post_author'] = (int) get_current_user_id();
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			if ( ! empty( $params['post_author'] ) && ! user_can( (int) $params['post_author'], 'manage_options' ) ) {
				$message = '';
				$posts_count = 0;
				$posts_limits = self::cpt_posts_limits( $params );
				$posts_limit = ! empty( $posts_limits['total'] ) ? (int) $posts_limits['total'] : 0;

				if ( $posts_limit > 0 ) {
					$posts_count = (int) self::count_user_cpt_posts( $params );

					// Limit exceed.
					if ( $posts_limit <= $posts_count ) {
						$can_add = false;
						$message = wp_sprintf( __( 'You have reached the limit of %s you can add at this time.', 'geodirectory' ), geodir_strtolower( geodir_post_type_name( $params['post_type'], true ) ) );
					}
				} else if ( $posts_limit < 0 ) {
					// Disabled from CPT
					$can_add = false;
					$message = wp_sprintf( __( 'You are not allowed to add the listing under %s.', 'geodirectory' ), geodir_strtolower( geodir_post_type_name( $params['post_type'], true ) ) );
				}

				if ( $can_add === false && $wp_error && $message ) {
					$message = apply_filters( 'geodir_user_posts_limit_message', $message, $posts_limit, $posts_count, $posts_limits, $params );

					if ( $message ) {
						$can_add = new WP_Error( 'geodir_user_posts_limit', $message );
					}
				}

				$can_add = apply_filters( 'geodir_check_user_posts_limits', $can_add, $posts_limits, $params, $args, $wp_error );
			}
		}

		return apply_filters( 'geodir_user_can_add_post', $can_add, $params, $args, $wp_error );
	}

	public static function posts_limit_message( $args = array() ) {
		$defaults = array(
			'post_type' => '',
			'post_author' => 0
		);

		$args = wp_parse_args( $args, $defaults );

		$posts_limits = self::cpt_posts_limits( $args );
		$post_type_name = geodir_strtolower( geodir_post_type_name( $args['post_type'] ) );

		if ( isset( $posts_limits['total'] ) && (int) $posts_limits['total'] < 0 ) {
			$message = wp_sprintf( __( 'You are not allowed to add the listing under %s.', 'geodirectory' ), $post_type_name );
		} else {
			$message = wp_sprintf( __( 'You have reached the limit of %s you can add at this time.', 'geodirectory' ), $post_type_name );
		}

		return apply_filters( 'geodir_user_posts_limit_message', $message, $args, $posts_limits );
	}

	public static function check_add_listing_output( $output, $args, $widget_args, $content ) {
		if ( ! is_admin() && geodir_is_page( 'add-listing' ) && ! geodir_is_page( 'edit-listing' ) ) {
			$post_type = geodir_get_current_posttype();

			$_args = array( 
				'post_type' => $post_type,
				'post_author' => null,
				'group' => 'add'
			);

			$package = geodir_get_post_package( array(), $post_type );

			if ( ! empty( $package ) && ! empty( $package->id ) ) {
				$_args['package_id'] = $package->id;
			}

			$can_add_post = self::user_can_add_post( $_args, true );

			if ( is_wp_error( $can_add_post ) ) {
				if ( geodir_design_style() ) {
					$message = aui()->alert( array(
						'type' => 'info',
						'content' => $can_add_post->get_error_message(),
						'class' => 'mb-0'
					) );
				} else {
					$message = geodir_notification( array( 'add_listing_error' => $can_add_post->get_error_message() ) );
				}

				$output = apply_filters( 'geodir_posts_limit_add_listing_message', $message, $post_type, $_args );
			}
		}

		return $output;
	}

	public static function check_rest_api_post( $prepared_post, $request ) {
		if ( empty( $prepared_post->ID ) ) {
			$args = array( 
				'post_type' => $prepared_post->post_type,
				'post_author' => ! empty( $prepared_post->post_author ) ? absint( $prepared_post->post_author ) : null,
				'package_id' => ! empty( $prepared_post->package_id ) ? absint( $prepared_post->package_id ) : 0
			);

			$can_add_post = self::user_can_add_post( $args, true );

			if ( is_wp_error( $can_add_post ) ) {
				return new WP_Error( 'rest_posts_limit', $can_add_post->get_error_message(), array( 'status' => 400 ) );
			}
		}

		return $prepared_post;
	}
}
