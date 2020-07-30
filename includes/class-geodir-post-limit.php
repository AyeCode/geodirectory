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

	public static function cpt_posts_limit( $post_type, $post_author = 0 ) {
		$post_type_info = geodir_get_posttype_info( $post_type );

		$limit = 0;

		if ( ! empty( $post_type_info ) && isset( $post_type_info['limit_posts'] ) ) {
			$limit = (int) $post_type_info['limit_posts'];
		}

		return (int) apply_filters( 'geodir_cpt_posts_limit', $limit, $post_type, $post_author );
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
			$query_args['post_status'] = array_keys( geodir_get_post_statuses() );
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

	public static function user_can_add_post( $args = array() ) {
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
				$posts_limit = (int) self::cpt_posts_limit( $params['post_type'], $params['post_author'] );

				if ( $posts_limit > 0 ) {
					$posts_count = (int) self::count_user_cpt_posts( $params );

					// Limit exceed.
					if ( $posts_limit <= $posts_count ) {
						$can_add = false;
					}
				} else if ( $posts_limit < 0 ) {
					$can_add = false; // Disabled from CPT
				}
			}
		}

		return apply_filters( 'geodir_user_can_add_post', $can_add, $params, $args );
	}

	public static function posts_limit_message( $post_type, $post_author = 0 ) {
		$posts_limit = (int) self::cpt_posts_limit( $post_type, $post_author );
		$post_type_name = geodir_strtolower( geodir_post_type_name( $post_type ) );

		if ( $posts_limit < 0 ) {
			$message = wp_sprintf( __( 'You are not allowed to add the listing under %s.', 'geodirectory' ), $post_type_name );
		} else {
			$message = wp_sprintf( __( 'You have reached the limit of %s you can add at this time.', 'geodirectory' ), $post_type_name );
		}

		return apply_filters( 'geodir_user_posts_limit_message', $message, $post_type, $posts_limit );
	}

	public static function check_add_listing_output( $output, $args, $widget_args, $content ) {
		if ( ! is_admin() && geodir_is_page( 'add-listing' ) && ! geodir_is_page( 'edit-listing' ) ) {
			$post_type = geodir_get_current_posttype();

			$can_add_post = self::user_can_add_post( array( 'post_type' => $post_type ) );

			if ( ! $can_add_post ) {
				$message = geodir_notification( array( 'add_listing_error' => self::posts_limit_message( $post_type, (int) get_current_user_id() ) ) );

				$output = apply_filters( 'geodir_posts_limit_add_listing_message', $message, $post_type );
			}
		}

		return $output;
	}

	public static function check_rest_api_post( $prepared_post, $request ) {
		if ( empty( $prepared_post->ID ) ) {
			$args = array( 'post_type' => $prepared_post->post_type );
			if ( ! empty( $prepared_post->post_author ) ) {
				$args['post_author'] = absint( $prepared_post->post_author );
			}

			$can_add_post = self::user_can_add_post( $args );
			if ( ! $can_add_post ) {
				$message = self::posts_limit_message( $prepared_post->post_type, ( isset( $args['post_author'] ) ? $args['post_author'] : 0 ) );

				return new WP_Error( 'rest_posts_limit', $message, array( 'status' => 400 ) );
			}
		}

		return $prepared_post;
	}
}
