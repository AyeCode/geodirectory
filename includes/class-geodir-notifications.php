<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Notifications Class
 *
 * @since 2.0.0.38
 */
class GeoDir_Notifications {

	public $notifications = array();

	public function __construct() {
		add_filter( 'geodir_notifications', array( $this, 'output' ) ); // add notifications to the output widget
		add_action( 'template_redirect', array( $this, 'post_closed' ) ); // post closed down notification

		$this->notifications = array();
	}

	public function add( $key = '', $notification = array(), $restrict = '' ) {
		if ( $key && ! empty( $notification ) ) {
			$this->notifications[ $key ] = $notification;
		}
	}

	public function output( $notifications ) {
		if ( ! empty( $this->notifications ) ) {
			$notifications = array_merge( $notifications, $this->notifications );
		}

		return $notifications;
	}

	/**
	 * A notification for when a post is marked as `closed down`.
	 *
	 * @param $notifications
	 *
	 * @return mixed
	 */
	public function post_closed() {
		global $post, $gd_post;

		if ( geodir_is_page( 'single' ) ) {
			if ( geodir_post_is_closed( $post ) ) {
				if ( ! empty( $post ) && ! empty( $post->post_type ) ) {
					$cpt_name = geodir_strtolower( geodir_post_type_singular_name( $post->post_type ) );
				} else {
					$cpt_name = __( 'business', 'geodirectory' );
				}

				$this->add( 'post_is_closed', array(
					'type' => 'warning',
					'note' => wp_sprintf( __( 'This %s appears to have closed down and may be removed soon.', 'geodirectory' ), $cpt_name )
				) );
			} else if ( ! empty( $gd_post->temp_closed ) ) {
				if ( ! empty( $post ) && ! empty( $post->post_type ) ) {
					$cpt_name = geodir_strtolower( geodir_post_type_singular_name( $post->post_type ) );
				} else {
					$cpt_name = __( 'business', 'geodirectory' );
				}

				// custom field message
				$cf = geodir_get_field_infoby( 'htmlvar_name', 'temp_closed', $post->post_type );

				$closed_message = ! empty( $cf['placeholder_value'] ) ? esc_attr( $cf['placeholder_value'] ) : __( 'This %s is temporarily closed.', 'geodirectory' );

				$this->add( 'post_is_temp_closed', array(
					'type' => 'warning',
					'note' => wp_sprintf( $closed_message, $cpt_name )
				) );
			}
		}
	}
}