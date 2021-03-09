<?php
/**
 * GeoDirectory export data class.
 *
 * @since 2.1.0.4
 * @package GeoDirectory
 * @author AyeCode Ltd
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Export multiple types data.
 */
//$export = new GeoDir_Export( array( 'custom_fields', 'sort_fields', 'tabs', 'search_fields', 'price_packages' ) );
//$array = $export->export(); // Retrieve PHP array
//$json = $export->export_json(); // Retrieve JSON

/**
 * Export single type data.
 */
//$export = new GeoDir_Export( array( 'custom_fields' ) );
//$array = $export->export(); // Retrieve PHP array
//$json = $export->export_json(); // Retrieve JSON

/**
 * Class used to export GeoDirectory data.
 *
 */
class GeoDir_Export {

	/**
	 * Export types.
	 *
	 * @var array
	 */
	public $types = array();

	/**
	 * Export params.
	 *
	 * @var array
	 */
	public $params = array();

	/**
	 * Export data.
	 *
	 * @var array
	 */
	public $data = array();

	/**
	 * Constructor.
	 *
	 */
	public function __construct( $types = array(), $params = array() ) {
		$this->set_types( $types );
		$this->set_params( $params );

		do_action( 'geodir_export_init', $this );
	}

	/**
	 * Retrieves types associated with the export.
	 *
	 * @return array Export types.
	 */
	public function get_types() {
		return $this->types;
	}

	/**
	 * Sets the types.
	 *
	 * @param array $types Export types.
	 */
	private function set_types( $types ) {
		$this->types = $types;
	}

	/**
	 * Retrieves params associated with the export.
	 *
	 * @return array Export params.
	 */
	public function get_params() {
		return $this->params;
	}

	/**
	 * Sets the params.
	 *
	 * @param array $params Export params.
	 */
	private function set_params( $params ) {
		$this->params = $params;
	}

	/**
	 * Export data in PHP array format.
	 */
	public function export() {
		$this->setup_export();

		return $this->get_data();
	}

	/**
	 * Export data in json format.
	 */
	public function export_json() {
		return wp_json_encode( $this->export() );
	}

	/**
	 * Setup export data.
	 */
	private function setup_export() {
		if ( ! empty( $this->types ) ) {
			foreach ( $this->types as $type ) {
				$this->setup_export_type( $type );
			}
		}
	}

	/**
	 * Setup export type.
	 */
	private function setup_export_type( $type ) {
		if ( isset( $this->data[ $type ] ) ) {
			return $this->data[ $type ];
		}

		$data = array();

		$_data = apply_filters( 'geodir_check_export_type_data', null, $type, $this );

		if ( $_data !== null ) {
			$data = $_data;
		} else if( method_exists( $this, 'export_' . $type ) ) {
			$method_name = 'export_' . $type;
			$data = $this->$method_name();
		}

		/**
		 * Filter export type data.
		 *
		 * @since 2.1.0.4
		 *
		 * @param array $data Export data.
		 * @param string $type Export type.
		 * @param GeoDir_Export $this GeoDirectory Export object.
		 */
		$this->data[ $type ] = apply_filters( 'geodir_get_export_type_data', $data, $type, $this );
	}

	/**
	 * Get export data.
	 */
	public function get_data() {
		return $this->data;
	}

	/**
	 * Export custom fields.
	 *
	 * @global object $wpdb WordPress Database object.
	 */
	private function export_custom_fields() {
		global $wpdb;

		$sql = $wpdb->prepare( "SELECT * FROM " . GEODIR_CUSTOM_FIELDS_TABLE . " WHERE is_active = %d ORDER BY id ASC", array( 1 ) );
		/**
		 * Filter custom fields export data sql.
		 *
		 * @since 2.1.0.4
		 *
		 * @param string $sql Custom fields sql query.
		 * @param GeoDir_Export $this GeoDirectory Export object.
		 */
		$sql = apply_filters( 'geodir_export_custom_fields_sql', $sql, $this );

		$results = $sql ? $wpdb->get_results( $sql ) : array();

		/**
		 * Filter custom fields export data.
		 *
		 * @since 2.1.0.4
		 *
		 * @param string $sql Custom fields sql query.
		 * @param GeoDir_Export $this GeoDirectory Export object.
		 */
		return apply_filters( 'geodir_export_custom_fields_data', $results, $this );
	}

	/**
	 * Export sorting fields.
	 *
	 * @global object $wpdb WordPress Database object.
	 */
	private function export_sort_fields() {
		global $wpdb;

		$sql = $wpdb->prepare( "SELECT * FROM " . GEODIR_CUSTOM_SORT_FIELDS_TABLE . " WHERE is_active = %d ORDER BY id ASC", array( 1 ) );
		/**
		 * Filter sorting fields export data sql.
		 *
		 * @since 2.1.0.4
		 *
		 * @param string $sql Sorting fields sql query.
		 * @param GeoDir_Export $this GeoDirectory Export object.
		 */
		$sql = apply_filters( 'geodir_export_sort_fields_sql', $sql, $this );

		$results = $sql ? $wpdb->get_results( $sql ) : array();

		/**
		 * Filter sorting fields export data.
		 *
		 * @since 2.1.0.4
		 *
		 * @param array $results Sorting fields data.
		 * @param GeoDir_Export $this GeoDirectory Export object.
		 */
		return apply_filters( 'geodir_export_sort_fields_data', $results, $this );
	}

	/**
	 * Export tabs.
	 *
	 * @global object $wpdb WordPress Database object.
	 */
	private function export_tabs() {
		global $wpdb;

		$sql = "SELECT * FROM " . GEODIR_TABS_LAYOUT_TABLE . " ORDER BY id ASC";
		/**
		 * Filter tabs export data sql.
		 *
		 * @since 2.1.0.4
		 *
		 * @param string $sql Tabs sql query.
		 * @param GeoDir_Export $this GeoDirectory Export object.
		 */
		$sql = apply_filters( 'geodir_export_tabs_sql', $sql, $this );

		$results = $sql ? $wpdb->get_results( $sql ) : array();

		/**
		 * Filter tabs export data.
		 *
		 * @since 2.1.0.4
		 *
		 * @param array $results Tabs data.
		 * @param GeoDir_Export $this GeoDirectory Export object.
		 */
		return apply_filters( 'geodir_export_tabs_data', $results, $this );
	}

	/**
	 * Export search fields.
	 *
	 * @global object $wpdb WordPress Database object.
	 */
	private function export_search_fields() {
		global $wpdb;

		if ( ! defined( 'GEODIR_ADVANCE_SEARCH_TABLE' ) ) {
			return array();
		}

		$sql = "SELECT * FROM " . GEODIR_ADVANCE_SEARCH_TABLE . " ORDER BY id ASC";
		/**
		 * Filter search fields export data sql.
		 *
		 * @since 2.1.0.4
		 *
		 * @param string $sql Search fields sql query.
		 * @param GeoDir_Export $this GeoDirectory Export object.
		 */
		$sql = apply_filters( 'geodir_export_search_fields_sql', $sql, $this );

		$results = $sql ? $wpdb->get_results( $sql ) : array();

		/**
		 * Filter search fields export data.
		 *
		 * @since 2.1.0.4
		 *
		 * @param array $results Search fields data.
		 * @param GeoDir_Export $this GeoDirectory Export object.
		 */
		return apply_filters( 'geodir_export_search_fields_data', $results, $this );
	}

	/**
	 * Export price packages.
	 *
	 * @global object $wpdb WordPress Database object.
	 */
	private function export_price_packages() {
		global $wpdb;

		if ( ! defined( 'GEODIR_PRICING_PACKAGES_TABLE' ) ) {
			return array();
		}

		$sql = $wpdb->prepare( "SELECT * FROM " . GEODIR_PRICING_PACKAGES_TABLE . " WHERE status = %d ORDER BY id ASC", array( 1 ) );
		/**
		 * Filter price packages export data sql.
		 *
		 * @since 2.1.0.4
		 *
		 * @param string $sql Price packages sql query.
		 * @param GeoDir_Export $this GeoDirectory Export object.
		 */
		$sql = apply_filters( 'geodir_export_price_packages_sql', $sql, $this );

		$results = $sql ? $wpdb->get_results( $sql ) : array();

		if ( ! empty( $results ) ) {
			foreach( $results as $i => $row ) {
				$sql = $wpdb->prepare( "SELECT * FROM " . GEODIR_PRICING_PACKAGE_META_TABLE . " WHERE package_id = %d ORDER BY meta_id ASC", array( $row->id ) );
				/**
				 * Filter package metadata export data sql.
				 *
				 * @since 2.1.0.4
				 *
				 * @param string $sql Package meta sql query.
				 * @param object $row Package object.
				 * @param GeoDir_Export $this GeoDirectory Export object.
				 */
				$sql = apply_filters( 'geodir_export_price_package_meta_sql', $sql, $row, $this );
				$metadata = $sql ? $wpdb->get_results( $sql ) : array();

				/**
				 * Filter price package export metadata.
				 *
				 * @since 2.1.0.4
				 *
				 * @param array $metadata Price package metadata.
				 * @param object $row Package object.
				 * @param GeoDir_Export $this GeoDirectory Export object.
				 */
				$results[ $i ]->metadata = apply_filters( 'geodir_export_price_package_meta_data', $metadata, $row, $this );
			}
		}

		/**
		 * Filter price packages export data.
		 *
		 * @since 2.1.0.4
		 *
		 * @param array $results Price packages data.
		 * @param GeoDir_Export $this GeoDirectory Export object.
		 */
		return apply_filters( 'geodir_export_price_packages_data', $results, $this );
	}
}
