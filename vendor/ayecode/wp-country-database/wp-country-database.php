<?php
/**
 * A class to get information about countries.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_Country_Database' ) ) {


	/**
	 * A class to get information about countries.
	 */
	class WP_Country_Database {


		public $version = "1.0.4";
		public $db_version = "1.0.2";
		public $db_version_current;
		public $db_table;
		private static $instance = null;

		/**
		 * Main WP_Country_Database Instance.
		 *
		 * Ensures only one instance of WP_Country_Database is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @static
		 * @see GeoDir()
		 * @return WP_Country_Database - Main instance.
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WP_Country_Database ) ) {
				self::$instance = new WP_Country_Database;

				global $wpdb;
				$prefix = isset($wpdb->base_prefix) ? $wpdb->base_prefix : $wpdb->prefix;
				self::$instance->db_table = $prefix."countries";

				// get current db version
				self::$instance->db_version_current = get_site_option('wp_country_database_version', false);

				if(!self::$instance->db_version_current || self::$instance->db_version_current < self::$instance->db_version){
					self::$instance->upgrade();
				}
			}

			return self::$instance;
		}

		public function upgrade(){
			$this->create_table();
			$this->insert_countries();
		}

		public function create_table(){
			global $wpdb;

			$wpdb->hide_errors();

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			$collate = '';

			if ( $wpdb->has_cap( 'collation' ) ) {
				$collate = $wpdb->get_charset_collate();
			}

			// Countries table
			$table = "CREATE TABLE " . $this->db_table . " (
						ID smallint AUTO_INCREMENT NOT NULL ,
						name varchar (50) NOT NULL ,
						slug varchar (50) NOT NULL ,
						alpha2Code varchar (2) NOT NULL ,
						alpha3Code varchar (3) NOT NULL ,
						callingCodes varchar (25) NOT NULL ,
						capital varchar (50) NOT NULL ,
						region varchar (50) NULL ,
						subregion varchar (50) NULL ,
						population bigint NULL ,
						latlng varchar (35) NULL ,
						demonym varchar (50) NULL ,
						timezones varchar (255) NULL ,
						currency_name varchar (50) NULL ,
						currency_code varchar (3) NULL ,
						currency_symbol varchar (3) NULL ,
						flag varchar (255) NULL ,
						address_format varchar (255) NULL ,
						PRIMARY KEY  (ID)) $collate; ";

			dbDelta( $table  );
		}

		public function table_keys(){
			return array(
				"ID",
				"name",
				"slug",
				"alpha2Code",
				"alpha3Code",
				"callingCodes",
				"capital",
				"region",
				"subregion",
				"population",
				"latlng",
				"demonym",
				"timezones",
				"currency_name",
				"currency_code",
				"currency_symbol",
				"flag",
				"address_format"
			);
		}
		
		public function insert_countries(){
			global $wpdb;

			// empty table first
			$this->empty_table();

			$current_countries = $this->get_countries();

			// make sure the table is empty
			if(empty($current_countries)){
				$countries = $this->get_latest_countries();
				$table_keys = $this->table_keys();
				$countries_sql = "INSERT INTO `" . $this->db_table . "` (`".implode("`,`",$table_keys)."`) VALUES ";
				$first = '';
				foreach($countries as $country){
					$fields_count   = count( $country );
					$prepare_values = array_fill( 0, $fields_count, "%s" );
					$prepare_string = implode( ",", $prepare_values );
					$sql = $wpdb->prepare($prepare_string,$country);
					$countries_sql .= " $first(".$sql. ") ";
					$first = ',';
				}

				$result = $wpdb->query($countries_sql);
				if ( is_wp_error( $result ) ) {
					$error_string = $result->get_error_message();
					echo '<div id="message" class="error"><p>' . $error_string . '</p></div>';
				}else{
					update_site_option( 'wp_country_database_version', $this->db_version );
						return true;
				}

			}

			return false;
		}

		/**
		 * Empty the country database.
		 */
		private function empty_table(){
			global $wpdb;
			$wpdb->query("DELETE FROM `" . $this->db_table . "` WHERE 1 = 1;");
		}

		public function get_latest_countries(){
			$wp_countries = array();
			include_once(__DIR__."/wp-country-database-data.php");
			return $wp_countries;
		}

		public function get_country_address_format($ISO2){


			return '';
		}

		/**
		 * Get the countries.
		 *
		 * @param array $params
		 *
		 * @return array|null|object
		 */
		public function get_countries($params = array()){
			global $wpdb;

			$defaults = array(
				'fields'        => array(), // an array of fields
				'where'         => array(),
				'like'          => array(),
				'in'            => array(),
				'order'         => 'name',
				'orderby'       => 'ASC',
				'limit'			=> '' // All
			);

			$args = wp_parse_args( $params, $defaults );

			// fields
			if(is_array($args['fields']) && !empty($args['fields'])){
				$fields_arr = array();
				foreach ($args['fields'] as $field){
					if(in_array($field,$this->table_keys())){
						$fields_arr[] = $field;
					}
				}

				if(!empty($fields_arr)){
					$fields = implode(",",$fields_arr);
				}else{
					$fields = '*';
				}
			}else{
				$fields = '*';
			}

			//limit
			$limit = $args['limit'] ? " LIMIT " . absint( $args['limit'] ) : '';

			$order = in_array($args['order'],$this->table_keys()) ? $args['order'] : 'name';
			$orderby = $args['orderby']=='ASC' ? 'ASC' : 'DESC';

			// where
			$where = '';
			if(is_array($args['where']) && !empty($args['where'])){
				foreach($args['where'] as $w_field => $w_value){
					if(in_array($w_field,$this->table_keys())){
						$where .= $wpdb->prepare(" AND $w_field = %s ",$w_value);
					}
				}
			}

			// like
			if(is_array($args['like']) && !empty($args['like'])){
				foreach($args['like'] as $l_field => $l_value){
					if(in_array($l_field,$this->table_keys())){
						$where .= $wpdb->prepare(" AND $l_field LIKE %s ",'%'.$wpdb->esc_like($l_value) .'%' );
					}
				}
			}

			// IN
			if(is_array($args['in']) && !empty($args['in'])){
				foreach($args['in'] as $i_field => $i_value){
					if(in_array($i_field,$this->table_keys())){
						if(is_array($i_value) && !empty($i_value)){
							$i_values = implode(',', array_fill(0, count($i_value), '%s'));
							$where .= $wpdb->prepare(" AND $i_field IN ($i_values) ",$i_value);
						}
					}
				}
			}

			$query = "SELECT $fields FROM " . $this->db_table . " WHERE 1=1 $where ORDER BY $order $orderby $limit";

//			echo '###'.$query;

			$countries = $wpdb->get_results($query);

			return $countries;
		}


		/**
		 * Get the country iso2 cod from country name.
		 * 
		 * @param $country_name
		 *
		 * @return null|string
		 */
		public function get_country_iso2($country_name){
			global $wpdb;
			return $wpdb->get_var($wpdb->prepare("SELECT alpha2Code FROM " . $this->db_table . " WHERE name LIKE %s", $country_name));
		}

		/**
		 * Get the country iso3 cod from country name.
		 *
		 * @param $country_name
		 *
		 * @return null|string
		 */
		public function get_country_iso3($country_name){
			global $wpdb;
			return $wpdb->get_var($wpdb->prepare("SELECT alpha3Code FROM " . $this->db_table . " WHERE name LIKE %s", $country_name));
		}

		/**
		 * Get the country iso2 cod from country name.
		 *
		 * @param $country_name
		 *
		 * @return null|string
		 */
		public function get_country_slug($country_name){
			global $wpdb;
			return $wpdb->get_var($wpdb->prepare("SELECT slug FROM " . $this->db_table . " WHERE name LIKE %s", $country_name));
		}


	}

	/**
	 * The main function responsible for returning the one true WP_Country_Database
	 * Instance to functions everywhere.
	 *
	 * Use this function like you would a global variable, except without needing
	 * to declare the global.
	 *
	 * Example: <?php $wp_country_database = wp_country_database(); ?>
	 *
	 * @since 1.0.0
	 * @return WP_Country_Database The one true WP_Country_Database Instance
	 */
	function wp_country_database() {
		return WP_Country_Database::instance();
	}

	global $wp_country_database;
	// Global for backwards compatibility.
	$GLOBALS['wp_country_database'] = $wp_country_database = wp_country_database();
}