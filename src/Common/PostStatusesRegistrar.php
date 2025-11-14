<?php
/**
 * Post Status Registrar
 *
 * @package GeoDirectory\Common
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Common;

use AyeCode\GeoDirectory\Core\Services\Statuses;

final class PostStatusesRegistrar {
	private Statuses $statuses_service;

	public function __construct( Statuses $statuses_service ) {
		$this->statuses_service = $statuses_service;
	}

	/**
	 * Registers the custom post statuses with WordPress.
	 */
	public function register(): void {
		$statuses = $this->statuses_service->get_registration_args();

		foreach ( $statuses as $status => $args ) {
			register_post_status( $status, $args );
		}
	}
}
