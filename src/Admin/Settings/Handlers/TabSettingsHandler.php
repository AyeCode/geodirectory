<?php

namespace AyeCode\GeoDirectory\Admin\Settings\Handlers;

use AyeCode\GeoDirectory\Admin\Settings\PersistenceHandlerInterface;
use AyeCode\GeoDirectory\Database\TabRepository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TabSettingsHandler
 *
 * Handles persistence for the detail page tabs builder.
 *
 * @package AyeCode\GeoDirectory\Admin\Settings\Handlers
 * @since 2.1.0
 */
class TabSettingsHandler implements PersistenceHandlerInterface {

	/**
	 * @var TabRepository
	 */
	private $repository;

	public function __construct() {
		$this->repository = new TabRepository();
	}

	/**
	 * Retrieves tab settings for a CPT.
	 *
	 * @param string $post_type The post type slug.
	 *
	 * @return array The tab settings, formatted for the UI.
	 */
	public function get( string $post_type ): array {
		$raw_data = $this->repository->get_by_post_type( $post_type );
//print_r($this->format_data_for_ui( $raw_data ));exit;
		return $this->format_data_for_ui( $raw_data );
	}

	/**
	 * Saves tab settings for a CPT.
	 *
	 * @param string $post_type The post type slug.
	 * @param array $data_from_ui The settings data from the UI.
	 *
	 * @return bool Result of the save operation.
	 */
	public function save( string $post_type, array $data_from_ui ): bool {

//		echo '###'.$post_type;
//		print_r($data_from_ui);exit;


		$data_for_db = $this->unformat_data_for_db( $data_from_ui );

		return $this->repository->sync_by_post_type( $post_type, $data_for_db );
	}

	/**
	 * Formats raw DB data into the structure the UI repeater expects.
	 *
	 * @param array $db_results Raw data from the database.
	 *
	 * @return array Formatted data for the UI.
	 */
	private function format_data_for_ui( array $db_results ): array {
		$formatted = [];
		if ( ! empty( $db_results ) ) {
			foreach ( $db_results as $row ) {
				$formatted[] = [
					'_uid'        => absint( $row['id'] ),
					'_parent_id' => !empty($row['tab_parent']) ? absint( $row['tab_parent'] ) : null,
					'label'       => esc_attr( $row['tab_name'] ),
					'icon'        => esc_attr( $row['tab_icon'] ),
					'type'        => esc_attr( $row['tab_key'] ),
					'template_id' => esc_attr( $row['tab_key'] ),
					'tab_type'    => esc_attr( $row['tab_type'] ),
					'tab_content' => esc_attr( $row['tab_content'] ),
					// Add any other fields the UI needs from the raw DB row
				];
			}
		}

		return $formatted;
	}

	/**
	 * Converts UI data back into the raw format needed for the database.
	 *
	 * @param array $ui_data Data from the settings form.
	 *
	 * @return array Data formatted for the database repository.
	 */
	private function unformat_data_for_db( array $ui_data ): array {
//		print_r( $ui_data );
		$unformatted = [];
		if ( ! empty( $ui_data ) ) {
			foreach ( $ui_data as $row ) {
				$unformatted[] = [
					'id'          => $row['_uid'],
					'tab_name'    => $row['label'],
					'tab_icon'    => $row['icon'],
					'tab_key'     => $row['type'],
					'tab_type'    => $row['tab_type'],
					'tab_content' => $row['tab_content'],
					'tab_parent'  => $row['_parent_id'],
					// Add any other DB fields derived from the UI row
				];
			}
		}

		return $unformatted;
	}





//	/**
//	 * Retrieves tab settings for a CPT.
//	 *
//	 * @param string $post_type The post type slug.
//	 *
//	 * @return array The tab settings.
//	 */
//	public function get( string $post_type ): array {
//
//		$results = $this->get_data($post_type);
////		print_r($results);exit;
//
//
//		return $results ? $results : [];
//
//	}
//
//	public function get_data($post_type): array {
//		global $wpdb;
//		$table_name = geodirectory()->tables->get( 'tabs_layout' );
//		$results = $wpdb->get_results(
//			$wpdb->prepare(
//				"SELECT * FROM {$table_name} WHERE post_type = %s ORDER BY sort_order ASC",
//				$post_type
//			),
//			ARRAY_A // Return as an array of associative arrays
//		);
//
//		return !empty($results) ? $this->formated_data($results) : [];
//
//
//	}
//
//	public function formated_data($results): array {
//
//
//		if(!empty($results)){
//			foreach ($results as $key => $result) {
//				$temp = $result;
//				unset( $results[ $key ] );
//				// required fields
//				$results[$key]['_uid'] = absint($temp['id']);
//				$results[$key]['label'] = esc_attr($temp['tab_name']);
//				$results[$key]['icon'] = esc_attr($temp['tab_icon']);
//				$results[$key]['type'] = esc_attr($temp['tab_key']);
//				$results[$key]['template_id'] = esc_attr($temp['tab_key']);
//
//				// optional fields
//				$results[$key]['tab_type'] = esc_attr($temp['tab_type']);
//				$results[$key]['tab_content'] = esc_attr($temp['tab_content']);
////				$results[$key]['tab_key'] = esc_attr($temp['tab_key']);
//
////				$results[$key]['icon'] = esc_attr($temp['tab_icon']);
//			}
//		}
//
//		return $results;
//	}

//	/**
//	 * Saves tab settings for a CPT.
//	 *
//	 * @param string $post_type The post type slug.
//	 * @param array  $data      The settings data to save.
//	 *
//	 * @return bool Result of the update_option call.
//	 */
//	public function save( string $post_type, array $data ): bool {
//		echo '###'.$post_type;
//		print_r($data);exit;
//		return update_option( 'geodir_tabs_' . $post_type, $data );
//	}

}
