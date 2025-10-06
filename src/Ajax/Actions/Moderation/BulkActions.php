<?php
/**
 * AJAX action to get all post reports.
 *
 * @package GeoDirectory\Ajax\Actions\Moderation
 */

declare( strict_types=1 );

namespace AyeCode\GeoDirectory\Ajax\Actions\Moderation;

use AyeCode\GeoDirectory\Admin\Settings\Handlers\ModerationSettingsHandler;

class BulkActions {
	/**
	 * Handles the AJAX request.
	 */
	public function dispatch(): void {
		$data = !empty($_POST['data']) ? json_decode(wp_unslash($_POST['data']), true) : [];


		$action = !empty($data['action']) ? sanitize_key( $data['action'] ) : '';
		$item_ids = !empty($data['item_ids']) ? array_map( 'absint', $data['item_ids'] ) : [];

		if($action && !empty($item_ids)) {

			$handler = new ModerationSettingsHandler();

			// we have a bulk delete
			if('delete-reports' === $action) {
				wp_send_json_success( $handler->bulk_delete( $item_ids ) );
				wp_die();
			}else{
				$result = false;
				foreach ($item_ids as $id) {
					switch ( $action ) {
						case 'resolved':
							$result = $handler->set_status( 'resolved', (int) $id );
							break;
						case 'rejected':
							$result = $handler->set_status( 'rejected', (int) $id );
							break;
						case 'pending':
							$result = $handler->set_post_status( 'pending', (int) $id );
							break;
						case 'draft':
							$result = $handler->set_post_status( 'draft', (int) $id );
							break;
						case 'trash':
							$result = $handler->delete_post( (int) $id );
							break;
						case 'delete':
							$result = $handler->delete_post( (int) $id, true );
							break;
					}
				}

				if ( $result ) {
					wp_send_json_success();
				}
			}

		}else{
			wp_send_json_error();
		}


		// if we got here then something went really wrong
		wp_send_json_error();

	}
}
