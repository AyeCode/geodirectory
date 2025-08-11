<?php
/**
 * Class CheckReviewsAction
 * Handles the process of reviewing and ensuring the accuracy of review-related data within the GeoDirectory plugin.
 *
 * @since 3.0.0
 */

// Define the namespace for the class. This helps prevent conflicts.
namespace GeoDirectory\Ajax\Actions;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CheckReviewsAction
 * Responsible for reviewing and correcting incomplete or missing review data within the GeoDirectory plugin.
 */
class MergeMissingTermsAction {

	/**
	 * Handles the dispatch process.
	 *
	 * @return void
	 */
	public static function dispatch() {
		$count = self::merge_missing_terms();
		if ( $count > 0 ) {
			$message = wp_sprintf( _n( 'Missing categories merged for %d listing.', 'Missing categories merged for %d listings.', $count, 'geodirectory' ), $count );
		} else {
			$message = __( 'No listing found with missing terms.', 'geodirectory' );
		}

		wp_send_json_success( array(
			'message'  => $message,
			'progress' => 100
		) );
	}

	/**
	 * Merge missing categories in details table.
	 *
	 * @return int No. of updated items.
	 * @since 2.3.57
	 *
	 */
	public static function merge_missing_terms( $post_types = array() ) {
		global $wpdb;

		$post_types = ! empty( $post_types ) && is_array( $post_types ) ? $post_types : geodir_get_posttypes();
		$updated    = 0;

		foreach ( $post_types as $post_type ) {
			$table = geodir_db_cpt_table( $post_type );

			$results = $wpdb->get_results( $wpdb->prepare( "SELECT p.ID, pd.post_category, pd.default_category, pd.post_tags FROM {$wpdb->posts} AS p INNER JOIN {$table} pd ON pd.post_id = p.ID WHERE p.post_type = %s AND p.post_status NOT IN( 'draft', 'auto-draft', 'trash', 'inherit' ) AND ( pd.post_category IS NULL OR pd.post_category = '' ) ORDER BY p.ID ASC", $post_type ) );

			if ( ! empty( $results ) ) {
				foreach ( $results as $k => $row ) {
					$_results = $wpdb->get_results( $wpdb->prepare( "SELECT t.term_id, t.name, tt.taxonomy FROM {$wpdb->term_relationships} AS tr LEFT JOIN {$wpdb->term_taxonomy} AS tt ON tt.term_taxonomy_id = tr.term_taxonomy_id LEFT JOIN {$wpdb->terms} AS t ON t.term_id = tt.term_id WHERE ( tt.taxonomy = %s OR tt.taxonomy = %s ) AND tr.object_id = %d ORDER BY t.name ASC", $post_type . 'category', $post_type . '_tags', $row->ID ) );

					if ( ! empty( $_results ) ) {
						$cats = array();
						$tags = array();

						foreach ( $_results as $_k => $_row ) {
							if ( $_row->taxonomy == $post_type . 'category' ) {
								$cats[] = $_row->term_id;
							} else if ( $_row->taxonomy == $post_type . '_tags' ) {
								$tags[] = $_row->name;
							}
						}

						$data   = array();
						$format = array();

						if ( ! empty( $cats ) ) {
							$data['post_category'] = ',' . implode( ",", $cats ) . ',';
							$format[]              = '%s';

							if ( empty( $row->default_category ) ) {
								$data['default_category'] = $cats[0];
								$format[]                 = '%d';
							}
						}

						if ( ! empty( $tags ) ) {
							$post_tags = implode( ",", $tags );

							if ( $post_tags != $post_tags ) {
								$data['post_tags'] = $row->post_tags;
								$format[]          = '%s';
							}
						}

						if ( ! empty( $data ) ) {
							$_updated = $wpdb->update( $table, $data, array( 'post_id' => $row->ID ), $format, array( '%d' ) );

							if ( $_updated ) {
								$updated ++;

								clean_post_cache( $row->ID );
							}
						}
					}
				}
			}
		}

		return $updated;
	}

}
