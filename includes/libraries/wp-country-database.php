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


		public $version = "1.0.0.0-dev";
		public $db_version = "1.0.0.0-dev";
		public $db_version_current;
		public $db_table;


		/**
		 *
		 */
		public function __construct() {
			global $wpdb;
			$this->db_table = $wpdb->prefix."countries";

			// get current db version
			$this->db_version_current = get_option('wp_country_database_version', false);

			if(!$this->db_version_current){
				$this->install();
			}


		}

		public function install(){
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
			// make sure the table is empty
			if(empty($this->get_countries())){
				$countries = $this->get_latest_countries();
				$table_keys = $this->table_keys();
				if (($key = array_search("ID", $table_keys)) !== false) {
					unset($table_keys[$key]);
				}
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

				echo $countries_sql;
				//$prepare_sql = $wpdb->prepare();

				$result = $wpdb->query($countries_sql);

				print_r($result);

//				print_r($countries);
				exit;
			}

		}

		public function get_latest_countries(){
			global $wpdb;

			$args = array('timeout' => 120);

			$response = wp_remote_get("https://restcountries.eu/rest/v2/all",$args);

			if ( is_wp_error( $response ) ) {
				// have a backup if it all goes peat tong
				$error_message = $response->get_error_message();
				echo "Something went wrong: $error_message";
			} else {
//				print_r(json_decode($response['body']));exit;
				$countries = json_decode($response['body']);

				if(!empty($countries)){
					return $this->format_countries($countries);
				}
			}

		}

		public function format_countries($countries){
			$formatted_countries = array();

			foreach($countries as $country){
				$formatted_countries[] = array(
					"name" => isset($country->name) ? esc_attr( $country->name ): '',
					"slug" => isset($country->name) ? sanitize_title( $country->name ): '',
					"alpha2Code" => isset($country->alpha2Code) ? esc_attr( $country->alpha2Code ): '',
					"alpha3Code" => isset($country->alpha3Code) ? esc_attr( $country->alpha3Code ): '',
					"callingCodes" => !empty($country->callingCodes) ? implode(",",$country->callingCodes ): '',
					"capital" => isset($country->capital) ? esc_attr( $country->capital ): '',
					"region" => isset($country->region) ? esc_attr( $country->region ): '',
					"subregion" => isset($country->subregion) ? esc_attr( $country->subregion ): '',
					"population" => isset($country->population) ? absint( $country->population ): '',
					"latlng" => isset($country->latlng) ? implode(",",$country->latlng ): '',
					"demonym" => isset($country->demonym) ? esc_attr( $country->demonym): '',
					"timezones" => isset($country->timezones) ? implode(",",$country->timezones ): '',
					"currency_name" => isset($country->currencies[0]->name) ? esc_attr( $country->currencies[0]->name): '',
					"currency_code" => isset($country->currencies[0]->code) ? esc_attr( $country->currencies[0]->code): '',
					"currency_symbol" => isset($country->currencies[0]->symbol) ? esc_attr( $country->currencies[0]->symbol): '',
					"flag" => isset($country->flag) ? esc_attr( $country->flag ): '',
					"address_format" => $this->get_country_address_format(esc_attr( $country->alpha2Code ))
				);
			}
			
			//print_r($formatted_countries);exit;
			return $formatted_countries;
		}

		public function get_country_address_format($ISO2){


			return '';
		}

		public function get_countries(){
			global $wpdb;

			$countries = $wpdb->get_results("SELECT * FROM " . $this->db_table . " ORDER BY name ASC");

			return $countries;
		}



	}

}