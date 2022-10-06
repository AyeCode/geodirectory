<?php
/**
 * Classifieds Functions
 *
 * All functions related to Classifieds/Real-Estate sold functionality.
 *
 * @package GeoDirectory
 * @since   2.1.1.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

function geodir_register_classified_statuses() {
	$statuses = array(
		'gd-sale-agreed' => array(
			'label'                     => _x( 'Sale Agreed', 'Listing status', 'geodirectory' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Sale Agreed <span class="count">(%s)</span>', 'Sale Agreed <span class="count">(%s)</span>', 'geodirectory' ),
			'notification'              => __( 'The sale of this %s has been agreed and is being finalized.', 'geodirectory' ),
			'icon'                      => 'fas fa-lock'
		),
		'gd-under-offer' => array(
			'label'                     => _x( 'Under Offer', 'Listing status', 'geodirectory' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Under Offer <span class="count">(%s)</span>', 'Under Offer <span class="count">(%s)</span>', 'geodirectory' ),
			'notification'              => __( 'An Offer has been made on this %s and is being considered.', 'geodirectory' ),
			'icon'                      => 'fas fa-lock'
		),
		'gd-sold' => array(
			'label'                     => _x( 'Sold', 'Listing status', 'geodirectory' ),
			'public'                    => false,
			'exclude_from_search'       => true,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Sold <span class="count">(%s)</span>', 'Sold <span class="count">(%s)</span>', 'geodirectory' ),
			'notification'              => __( 'This %s has sold and is no longer available for purchase.', 'geodirectory' ),
			'icon'                      => 'fas fa-lock'
		)
	);

	return apply_filters( 'geodir_register_classified_statuses', $statuses );
}
/**
 * Get Classifieds / Real-estate statuses.
 *
 * @since 2.1.1.5
 *
 * @return array $statuses.
 */
function geodir_get_classified_statuses( $post_type = '' ) {
	$active_statuses = array();

	if ( $post_type ) {
		$active_statuses = geodir_classified_active_statuses( $post_type );

		if ( empty( $active_statuses ) ) {
			return array();
		}
	}

	$_statuses = geodir_register_classified_statuses();

	$statuses = array();

	foreach ( $_statuses as $status => $data ) {
		if ( ! empty( $active_statuses ) && ! in_array( $status, $active_statuses ) ) {
			continue;
		}

		$statuses[ $status ] = $data['label'];
	}

	return apply_filters( 'geodir_get_classified_statuses', $statuses, $post_type );
}

function geodir_classified_active_statuses( $post_type ) {
	$post_types = geodir_get_posttypes( 'array' );

	$post_type_array = ! empty( $post_types[ $post_type ] ) ? $post_types[ $post_type ] : array();
	$statuses = isset( $post_type_array['classified_features'] ) ? $post_type_array['classified_features'] : array();

	return apply_filters( 'geodir_classified_active_statuses', $statuses, $post_type );
}