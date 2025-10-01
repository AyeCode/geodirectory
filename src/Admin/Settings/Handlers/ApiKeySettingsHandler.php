<?php
/**
 * Handles persistence of API Key settings.
 *
 * @package GeoDirectory\Admin\Settings\Handlers
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Admin\Settings\Handlers;

use AyeCode\GeoDirectory\Admin\Utils\DataMapper;
use AyeCode\GeoDirectory\Database\Repository\ApiKeyRepository;
use AyeCode\GeoDirectory\Database\Schema\ApiKeySchema;

class ApiKeySettingsHandler {

	/** @var ApiKeyRepository */
	private $repository;

	/** @var DataMapper */
	private $mapper;

	public function __construct() {
		$this->repository = new ApiKeyRepository();
		$this->mapper     = new DataMapper( $this->get_data_map() );
	}

	private function get_data_map(): array {
		$schema_definition = ( new ApiKeySchema() )->get_schema();
		$base_map          = [];

		foreach ( $schema_definition as $db_column => $props ) {
			$ui_key      = $props['ui_key'] ?? $db_column;
			$ui_sanitize = $props['ui_sanitize'] ?? 'esc_attr';
			$db_sanitize = $props['db_sanitize'] ?? 'sanitize_text_field';

			$base_map[ $db_column ] = [ $ui_key, $ui_sanitize, $db_sanitize, null, null ];
		}

		return $base_map;
	}

	public function get_keys(): array {
		$raw_data = $this->repository->get_all();
		return $this->mapper->transform( $raw_data, 'to_ui' );
	}

	public function create_key( array $ui_data ): array {
		$db_data = $this->mapper->transform( [$ui_data], 'to_db' )[0];

		$consumer_key    = 'ck_' . geodirectory()->utils->rand_hash();
		$consumer_secret = 'cs_' . geodirectory()->utils->rand_hash();

		$db_data['consumer_key']    = geodirectory()->utils->api_hash( $consumer_key );
		$db_data['consumer_secret'] = $consumer_secret;
		$db_data['truncated_key']   = substr( $consumer_key, -7 );

		$new_key_id = $this->repository->add_key( $db_data );

		$saved_data = $this->repository->get_by_id( $new_key_id );
		$saved_data['consumer_key']    = $consumer_key;
		$saved_data['consumer_secret'] = $consumer_secret;

		$response = $this->mapper->transform( [$saved_data], 'to_ui' )[0];
		$response['message'] = __( 'API Key generated successfully.', 'geodirectory' );

		return $response;
	}

	public function update_key( int $id, array $ui_data ): bool {
		$db_data = $this->mapper->transform( [$ui_data], 'to_db' )[0];
		return $this->repository->update_key( $id, $db_data );
	}

	public function delete_key( int $id ): bool {
		return $this->repository->delete_key( $id );
	}
}
