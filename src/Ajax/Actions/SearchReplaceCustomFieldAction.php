<?php
/**
 * Handles the search and replace operations for custom fields in a GeoDirectory custom post type.
 */

// Define the namespace for the class. This helps prevent conflicts.
namespace GeoDirectory\Ajax\Actions;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles search and replace actions for custom fields in a GeoDirectory custom post type.
 */
class SearchReplaceCustomFieldAction {

	/**
	 * Handles the dispatch process.
	 *
	 * @return void Outputs a JSON success response with a message and progress value.
	 */
	public static function dispatch() {


		$input_data = ! empty( $_POST['input_data'] ) ? json_decode( wp_unslash( $_POST['input_data'] ), true ) : array();

		$post_type = ! empty( $input_data['gd_srcf_pt'] ) ? sanitize_text_field( $input_data['gd_srcf_pt'] ) : '';
		$field     = ! empty( $input_data['gd_srcf_cf'] ) ? sanitize_text_field( $input_data['gd_srcf_cf'] ) : '';
		$search    = isset( $input_data['gd_srcf_s'] ) && $input_data['gd_srcf_s'] != "" ? sanitize_text_field( wp_unslash( $input_data['gd_srcf_s'] ) ) : '';
		$replace   = isset( $input_data['gd_srcf_r'] ) && $input_data['gd_srcf_r'] != "" ? sanitize_text_field( wp_unslash( $input_data['gd_srcf_r'] ) ) : '';

		// check we have data
		if (
			empty( $post_type ) ||
			empty( $field ) ||
			empty( $search ) ||
			empty( $replace )
		) {
			wp_send_json_error( array(
				'message' => __( 'Please fill in all fields', 'geodirectory' ),
			) );
		}

		if ( $replaced = self::search_replace_cf_value( $post_type, $field, $search, $replace ) ) {
			$message = wp_sprintf( __( '%d items has been successfully updated.', 'geodirectory' ), $replaced );
		} else {
			$message = __( 'No matching items found.', 'geodirectory' );
		}

		wp_send_json_success( array(
			'message'  => $message,
			'progress' => 100
		) );
	}

	/**
	 * Searches for a specific value in a custom field within a custom post type and replaces it with a new value.
	 *
	 * @param string $post_type The custom post type where the operation should be performed.
	 * @param string $field The custom field key to search and update values in.
	 * @param string $search The value to search for within the specified custom field.
	 * @param string $replace The value to replace the search term with.
	 *
	 * @return int|null The number of items updated, or null if the operation cannot be performed.
	 */
	public static function search_replace_cf_value( $post_type, $field, $search, $replace ) {
		global $wpdb;

		// Ensure all required variables are present and the post type is valid.
		if ( geodir_is_gd_post_type( $post_type ) && ! empty( $field ) && ! empty( $search ) ) {

			$table_name = geodir_db_cpt_table( $post_type );

			// Security: Ensure the dynamic column name is valid before using it in the query.
			if ( ! geodir_column_exist( $table_name, $field ) ) {
				return;
			}

			$field_name = "`" . $field . "`";

			// Check if any records match the search term.
			$count_sql = $wpdb->prepare( "SELECT COUNT(*) FROM `{$table_name}` WHERE FIND_IN_SET(%s, {$field_name})", $search );
			$found = $wpdb->get_var( $count_sql );

			if ( $found > 0 ) {
				/*
				 * The CONCAT/REPLACE/TRIM pattern safely replaces a value within a
				 * comma-separated string, avoiding partial matches (e.g., replacing 'cat' inside 'category').
				 */
				$update_sql = $wpdb->prepare(
					"UPDATE `{$table_name}` SET {$field_name} = TRIM( BOTH ',' FROM REPLACE( CONCAT( ',', {$field_name}, ',' ), %s, %s ) ) WHERE FIND_IN_SET( %s, {$field_name} )",
					',' . $search . ',',
					',' . $replace . ',',
					$search
				);

				$found = $wpdb->query( $update_sql );
			}
		}

		return $found;
	}
}
