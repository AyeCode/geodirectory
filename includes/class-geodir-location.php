<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GeoDirectory GeoDir_Location.
 *
 * Locations functions.
 *
 * @class    GeoDir_Location
 * @package  GeoDirectory/Classes
 * @category Class
 * @author   AyeCode
 */
class GeoDir_Location {

	public $country;
	public $country_slug;
	public $country_iso2;
	public $country_iso3;
	public $region;
	public $region_slug;
	public $city;
	public $city_slug;
	public $latitude;
	public $longitude;
	public $type;
	public $is_default;
	public $id;



	public function __construct( $data = array()) {

		if(empty($data)){
			//$this->set_current();
			add_action( 'wp', array( $this, 'set_current' ), 0 );
			add_action( 'geodir_settings_save_general', array( $this, 'clear_cache' ));

		}

		add_action( 'geodir_address_extra_listing_fields', array( $this, 'set_multi_city' ), 1, 1 );
	}

	public function clear_cache(){
		wp_cache_delete("geodir_get_default_location");
	}

	public function set_current(){
		$location = new stdClass();
		$location->city = geodir_get_option('default_location_city');
		$location->region = geodir_get_option('default_location_region');
		$location->country = geodir_get_option('default_location_country');
		$location->latitude = geodir_get_option('default_location_latitude');
		$location->longitude = geodir_get_option('default_location_longitude');

		// slugs
		$location->city_slug = urldecode( sanitize_title( $location->city ) );
		$location->region_slug = urldecode( sanitize_title( $location->region ) );
		$location->country_slug = urldecode( sanitize_title( $location->country ) );

		// id
		$location->id = 0;

		// type
		$location->type = 'city';

		// is_default
		$location->is_default = 1;


		// set search values
		if(isset($_REQUEST['geodir_search']) && $_REQUEST['geodir_search'] && geodir_is_page('search')){
			$location->city = '';
			$location->region = '';
			$location->country = '';
			$location->latitude = '';
			$location->longitude = '';
			$location->city_slug = '';
			$location->region_slug = '';
			$location->country_slug = '';
			$location->id = 0;
			$location->type = 'search';
			$location->is_default = 0;

			// set GPS
			$latlon = $this->get_latlon();
			if(!empty($latlon['lat'])){$location->latitude = $latlon['lat'];}
			if(!empty($latlon['lon'])){$location->longitude = $latlon['lon'];}
		}


		/**
		 * Filter the default location.
		 *
		 * @since 1.0.0
		 * @package GeoDirectory
		 *
		 * @param string $location_result The default location object.
		 */
		$location_result = apply_filters('geodir_set_current_location', $location );

		$this->country_slug = $location_result->country_slug;
		$this->country = $location_result->country;
		$this->region_slug = $location_result->region_slug;
		$this->region = $location_result->region;
		$this->city_slug  = $location_result->city_slug ;
		$this->city = $location_result->city;
		$this->latitude = $location_result->latitude;
		$this->longitude = $location_result->longitude;
		$this->type = $location_result->type;
		$this->is_default = $location_result->is_default;
		$this->id = $location_result->id;

	}

	/**
	 * Returns the default location.
	 *
	 * @since 1.0.0
	 * @package GeoDirectory
	 * @return object
	 */
	public function get_default_location()
	{

		// check cache
		$cache = wp_cache_get("geodir_get_default_location");
		if($cache !== false){
			return $cache;
		}

		$location = new stdClass();
		$location->city = geodir_get_option('default_location_city');
		$location->region = geodir_get_option('default_location_region');
		$location->country = geodir_get_option('default_location_country');
		$location->latitude = geodir_get_option('default_location_latitude');
		$location->longitude = geodir_get_option('default_location_longitude');

		// slugs
		$location->city_slug = sanitize_title($location->city);
		$location->region_slug = sanitize_title($location->region);
		$location->country_slug = sanitize_title($location->country);

		/**
		 * Filter the default location.
		 *
		 * @since 1.0.0
		 * @package GeoDirectory
		 *
		 * @param string $location_result The default location object.
		 */
		$location_result = apply_filters('geodir_get_default_location', $location );

		wp_cache_set("geodir_get_default_location", $location_result );


		return $location_result;
	}


	public function get_country_name_from_slug($slug){
		return geodir_get_option('default_location_country');
	}

	public function get_region_name_from_slug($slug){
		return geodir_get_option('default_location_region');
	}

	public function get_city_name_from_slug($slug){
		return geodir_get_option('default_location_city');
	}

	public function get_post_location( $gd_post ) {
		if ( geodir_core_multi_city() ) {
			return $this->multi_city_location( $gd_post );
		}

		return $this->get_default_location();
	}


	/**
	 * Returns location slug using location string.
	 *
	 * @since 1.0.0
	 * @package GeoDirectory
	 * @param string $location_string The location string.
	 * @return string The location slug.
	 */
	public function create_location_slug($location_string)
	{

		/**
		 * Filter the location slug.
		 *
		 * @since 1.0.0
		 * @package GeoDirectory
		 *
		 * @param string $location_string Sanitized location string.
		 */
		return urldecode(apply_filters('geodir_location_slug_check', sanitize_title($location_string)));
	}

	/**
	 * Checks whether the default location is set or not.
	 *
	 * @since 1.0.0
	 * @package GeoDirectory
	 * @return bool
	 */
	public function is_default_location_set()
	{
		$default_location = $this->get_default_location();
		if (!empty($default_location))
			return true;
		else
			return false;
	}

	/**
	 * Get ISO2 of the country.
	 *
	 * @since 1.6.16
	 * @package GeoDirectory
	 * @param string $country The country name.
	 * @return string Country ISO2 code.
	 */
	function get_country_iso2( $country ) {
		global $wp_country_database;

		if ( in_array( strtolower( $country ), array( "england", "northern ireland", "scotland", "wales" ) ) ) {
			$country = 'United Kingdom';
		}

		if ( $result = $wp_country_database->get_country_iso2( $country ) ) {
			return $result;
		}

		return $country;
	}

	/**
	 * Get ISO2 of the country.
	 *
	 * @since 1.6.16
	 * @package GeoDirectory
	 * @param string $country The country name.
	 * @return string Country ISO2 code.
	 */
	function get_country_iso3( $country ) {
		global $wp_country_database;

		if ( in_array( strtolower( $country ), array( "england", "northern ireland", "scotland", "wales" ) ) ) {
			$country = 'United Kingdom';
		}

		if ( $result = $wp_country_database->get_country_iso3( $country ) ) {
			return $result;
		}

		return $country;
	}

	/**
	 * Get the lat and lon from the query var
	 *
	 * @return array
	 */
	public function get_latlon(){
		global $wp;
		$latlon = array();
		if(!empty($wp->query_vars['latlon'])){
			$gps = explode(",",$wp->query_vars['latlon']);
			$latlon['lat'] = isset($gps[0]) ? filter_var($gps[0], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : '';
			$latlon['lon'] = isset($gps[1]) ? filter_var($gps[1], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : '';
		}elseif( !empty($_REQUEST['sgeo_lat']) && !empty($_REQUEST['sgeo_lon'])){
			$latlon['lat'] = isset($_REQUEST['sgeo_lat']) ? filter_var($_REQUEST['sgeo_lat'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : '';
			$latlon['lon'] = isset($_REQUEST['sgeo_lon']) ? filter_var($_REQUEST['sgeo_lon'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : '';
		}
		return $latlon;
	}


	/**
	 * An array of the used location vars.
	 *
	 * These are used in the where queries.
	 *
	 * @return array
	 */
	public function allowed_query_variables(){
		return array('country','region','city');
	}

	public function is_type_gps() {
		$gps_type = false;

		if ( $this->type && in_array( $this->type, array( 'gps', 'me', 'search' ) ) && ! empty( $this->get_latlon() ) ) {
			$gps_type = true;
		}

		return $gps_type;
	}

	/**
	 * This is used to put country, region & city fields on add/edit listing page.
	 *
	 * @since 2.1.0.7
	 *
	 * @global object $gd_post The post object.
	 *
	 * @param array $field The array of setting for the custom field.
	 */
	public function set_multi_city( $field ) {
		global $gd_post, $geodir_label_type;

		if ( ! geodir_core_multi_city() ) {
			return;
		}

		$location = $this->get_default_location();
		$name = $field['name'];
		$prefix = $name . '_';
		$is_required = ! empty( $field['is_required'] ) ? ' <span class="text-danger">*</span>' : '';

		if ( ! empty( $gd_post->country ) ) {
			$country = $gd_post->country;
		}  else if ( ! empty( $location->country ) && $is_required ) {
			$country = $location->country;
		} else {
			$country = '';
		}

		if ( ! empty( $gd_post->region ) ) {
			$region = $gd_post->region;
		}  else if ( ! empty( $location->region ) && $is_required ) {
			$region = $location->region;
		} else {
			$region = '';
		}

		if ( ! empty( $gd_post->city ) ) {
			$city = $gd_post->city;
		} else if ( ! empty( $location->city ) && $is_required ) {
			$city = $location->city;
		} else {
			$city = '';
		}

		$design_style = geodir_design_style();

		if ( $design_style ) {
			$required = ! empty( $is_required ) ? ' <span class="text-danger">*</span>' : '';

			// Country
			echo aui()->select( array(
				'id'               => $prefix . "country",
				'name'             => "country",
				'placeholder'      => esc_attr__( 'Choose a country&hellip;', 'geodirectory' ),
				'value'            => esc_attr( stripslashes( $country ) ),
				'required'         => $is_required,
				'label'            => esc_attr__( 'Country', 'geodirectory' ) . $required,
				'label_type'       => ! empty( $geodir_label_type ) ? $geodir_label_type : 'horizontal',
				'help_text'        => __( 'Click on above field and type to filter list.', 'geodirectory' ),
				'options'          => geodir_get_country_dl( $country, $prefix ),
				'extra_attributes' => array(
					'data-address-type' => 'country',
					'field_type'        => $field['type']
				),
				'wrap_attributes' => geodir_conditional_field_attrs( $field, 'country', 'select' )
			) );

			// Region
			echo aui()->input( array(
				'type'             => 'text',
				'id'               => $prefix . "region",
				'name'             => "region",
				'value'            => esc_attr( stripslashes( $region ) ),
				'required'         => $is_required,
				'label_show'       => true,
				'label'            => esc_attr__( 'Region', 'geodirectory' ) . $required,
				'label_type'       => ! empty( $geodir_label_type ) ? $geodir_label_type : 'horizontal',
				'placeholder'      => ( ! empty( $location->region ) ? esc_attr( stripslashes( $location->region ) ) : '' ),
				'help_text'        => __( 'Enter listing region.', 'geodirectory' ),
				'extra_attributes' => array(
					'data-address-type' => 'region',
					'field_type'        => 'text',
					'data-tags'         => "false"
				),
				'wrap_attributes' => geodir_conditional_field_attrs( $field, 'region', 'select' )
			) );

			// City
			echo aui()->input( array(
				'type'             => 'text',
				'id'               => $prefix . "city",
				'name'             => "city",
				'value'            => esc_attr( stripslashes( $city ) ),
				'required'         => $is_required,
				'label_show'       => true,
				'label'            => esc_attr__( 'City', 'geodirectory' ) . $required,
				'label_type'       => ! empty( $geodir_label_type ) ? $geodir_label_type : 'horizontal',
				'placeholder'      => ( ! empty( $location->city ) ? esc_attr( stripslashes( $location->city ) ) : '' ),
				'help_text'        => __( 'Enter listing city.', 'geodirectory' ),
				'extra_attributes' => array(
					'data-address-type' => 'city',
					'field_type'        => 'text',
					'data-tags'         => "false"
				),
				'wrap_attributes' => geodir_conditional_field_attrs( $field, 'city', 'select' )
			) );
		} else {
			?>
			<div id="geodir_<?php echo $prefix . 'country'; ?>_row"
				 class="geodir_form_row clearfix gd-fieldset-details geodir-address-row-multi<?php echo ( $is_required ? ' required_field' : '' ); ?>">
				<label for="<?php echo $prefix ?>country"><?php echo esc_attr__( 'Country', 'geodirectory' ) . ( $is_required ? ' <span>*</span>' : '' ); ?></label>
				<select id="<?php echo $prefix ?>country" name="country" data-placeholder="<?php esc_attr_e( 'Choose a country&hellip;', 'geodirectory' ); ?>" class="geodir_textfield textfield_x geodir-select" field_type="<?php echo $field['type']; ?>" data-address-type="country">
					<?php echo geodir_get_country_dl( $country, $prefix ); ?>
				</select>
				<span
					class="geodir_message_note"><?php _e( 'Click on above field and type to filter list.', 'geodirectory' ); ?></span>
				<?php if ( $is_required ) { ?>
					<span class="geodir_message_error"><?php _e( 'Listing country is required.', 'geodirectory' ); ?></span>
				<?php } ?>
			</div>
			<div id="geodir_<?php echo $prefix . 'region'; ?>_row"
				 class="geodir_form_row clearfix gd-fieldset-details geodir-address-row-multi<?php echo ( $is_required ? ' required_field' : '' ); ?>">
				<label for="<?php echo $prefix ?>region"><?php echo esc_attr__( 'Region', 'geodirectory' ) . ( $is_required ? ' <span>*</span>' : '' ); ?></label>
				<input type="text" id="<?php echo $prefix ?>region" name="region" value="<?php echo esc_attr( stripslashes( $region ) ); ?>" placeholder="<?php echo ( ! empty( $location->region ) ? esc_attr( stripslashes( $location->region ) ) : '' ); ?>" class="geodir_textfield textfield_x" field_type="text" data-address-type="region" />
				<span class="geodir_message_note"><?php _e( 'Enter listing region.', 'geodirectory' ); ?></span>
				<?php if ( $is_required ) { ?>
					<span class="geodir_message_error"><?php _e( 'Listing region is required.', 'geodirectory' ); ?></span>
				<?php } ?>
			</div>
			<div id="geodir_<?php echo $prefix . 'city'; ?>_row"
				 class="geodir_form_row clearfix gd-fieldset-details geodir-address-row-multi<?php echo ( $is_required ? ' required_field' : '' ); ?>">
				<label for="<?php echo $prefix ?>city"><?php echo esc_attr__( 'City', 'geodirectory' ) . ( $is_required ? ' <span>*</span>' : '' ); ?></label>
				<input type="text" id="<?php echo $prefix ?>city" name="city" value="<?php echo esc_attr( stripslashes( $city ) ); ?>" placeholder="<?php echo ( ! empty( $location->city ) ? esc_attr( stripslashes( $location->city ) ) : '' ); ?>" class="geodir_textfield textfield_x" field_type="text" data-address-type="city" />
				<span class="geodir_message_note"><?php _e( 'Enter listing city.', 'geodirectory' ); ?></span>
				<?php if ( $is_required ) { ?>
					<span class="geodir_message_error"><?php _e( 'Listing city is required.', 'geodirectory' ); ?></span>
				<?php } ?>
			</div>
			<?php
		}
	}

	/**
	 * Set post location.
	 *
	 * @since 2.1.0.7
	 *
	 * @param  object $gd_post The post object.
	 * @return object Post location object.
	 */
	public function multi_city_location( $gd_post ) {
		$location = new stdClass();

		// Names
		$location->city = ! empty( $gd_post->city ) ? stripslashes( $gd_post->city ) : '';
		$location->region = ! empty( $gd_post->region ) ? stripslashes( $gd_post->region ) : '';
		$location->country = ! empty( $gd_post->country ) ? stripslashes( $gd_post->country ) : '';

		// Slugs
		$location->city_slug = $location->city ? sanitize_title( $location->city ) : '';
		$location->region_slug = $location->region ? sanitize_title( $location->region ) : '';
		$location->country_slug = $location->country ? sanitize_title( $location->country ) : '';

		// GPS
		$location->latitude = ! empty( $gd_post->latitude ) ? $gd_post->latitude : '';
		$location->longitude = ! empty( $gd_post->longitude ) ? $gd_post->longitude : '';

		return $location;
	}
}
