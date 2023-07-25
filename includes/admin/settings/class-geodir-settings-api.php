<?php
/**
 * GeoDirectory API Settings
 *
 * @author   AyeCode
 * @category Admin
 * @package  GeoDirectory/Admin
 * @version  2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'GeoDir_Settings_Rest_API', false ) ) :

/**
 * GeoDir_Settings_Rest_API.
 */
class GeoDir_Settings_Rest_API extends GeoDir_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'api';
		$this->label = __( 'API', 'geodirectory' );

		add_action( 'geodir_settings_form_method_tab_' . $this->id, array( $this, 'form_method' ) );

		parent::__construct();

		$this->notices();
	}

	/**
	 * Get sections.
	 *
	 * @return array
	 */
	public function get_sections() {
		$sections = array(
			''         => __( 'Settings', 'geodirectory' ),
			'keys'     => __( 'Keys', 'geodirectory' ),
		);

		return apply_filters( 'geodir_get_sections_' . $this->id, $sections );
	}

	/**
	 * Get settings array.
	 *
	 * @param string $current_section
	 * @return array
	 */
	public function get_settings( $current_section = '' ) {
		$settings = array();

		if ( '' === $current_section ) {
			$settings = apply_filters( 'geodir_settings_rest_api', array(
				array(
					'title' => __( 'General options', 'geodirectory' ),
					'type'  => 'title',
					'desc'  => '',
					'id'    => 'general_options',
				),

				array(
					'title'   => __( 'API', 'geodirectory' ),
					'desc'    => __( 'Enable the REST API', 'geodirectory' ),
					'id'      => 'rest_api_enabled',
					'type'    => 'checkbox',
					'default' => 'yes',
				),

				array(
					'id'      => 'rest_api_external_image',
					'type'    => 'checkbox',
					'title'   => __( 'External Images', 'geodirectory' ),
					'desc'    => __( 'Allow users to store external images without uploading to the site for the listing created via API. Image src starts with # will be used as an external image. Ex: #https://mysite.com/assets/myimage.png', 'geodirectory' ),
					'default' => ''
				),

				array(
					'type' => 'sectionend',
					'id' => 'general_options',
				),
			) );
		}

		return apply_filters( 'geodir_get_settings_' . $this->id, $settings, $current_section );
	}

	/**
	 * Form method.
	 *
	 * @param  string $method
	 *
	 * @return string
	 */
	public function form_method( $method ) {
		global $current_section;

		if ( 'keys' == $current_section ) {
			if ( isset( $_GET['create-key'] ) || isset( $_GET['edit-key'] ) ) {
				return 'post';
			}

			return 'get';
		}

		return 'post';
	}

	/**
	 * Notices.
	 */
	private function notices() {
		if ( isset( $_GET['section'] ) && 'keys' == $_GET['section'] ) {
			GeoDir_Admin_API_Keys::notices();
		}
	}

	/**
	 * Output the settings.
	 */
	public function output() {
		global $current_section;

		if ( 'keys' === $current_section ) {
			GeoDir_Admin_API_Keys::page_output();
		} else {
			$settings = $this->get_settings( $current_section );
			GeoDir_Admin_Settings::output_fields( $settings );
		}
	}

	/**
	 * Save settings.
	 */
	public function save() {
		global $current_section;

		if ( apply_filters( 'geodir_rest_api_valid_to_save', ! in_array( $current_section, array( 'keys' ) ) ) ) {
			$settings = $this->get_settings();
			GeoDir_Admin_Settings::save_fields( $settings );
		}
	}
}

endif;

return new GeoDir_Settings_Rest_API();
