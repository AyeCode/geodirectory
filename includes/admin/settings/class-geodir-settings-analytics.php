<?php
/**
 * GeoDirectory Analytics Settings
 *
 * @author   AyeCode
 * @category Admin
 * @package  GeoDirectory/Admin
 * @version  2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'GeoDir_Settings_Analytics', false ) ) :

	/**
	 * GD_Settings_Products.
	 */
	class GeoDir_Settings_Analytics extends GeoDir_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {

			$this->id    = 'analytics';
			$this->label = __( 'Google Analytics', 'woocommerce' );

			add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
			add_action( 'woocommerce_sections_' . $this->id, array( $this, 'output_toggle_advanced' ) );

			add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
			add_action( 'woocommerce_sections_' . $this->id, array( $this, 'output_sections' ) );
		}

		/**
		 * Get sections.
		 *
		 * @return array
		 */
		public function get_sections() {

			$sections = array(
				''          	=> __( 'General', 'woocommerce' ),
				'display'       => __( 'Display', 'woocommerce' ),
				'inventory' 	=> __( 'Inventory', 'woocommerce' ),
				'downloadable' 	=> __( 'Downloadable products', 'woocommerce' ),
			);
			$sections = array();
			return apply_filters( 'woocommerce_get_sections_' . $this->id, $sections );
		}

		/**
		 * Output the settings.
		 */
		public function output() {
			global $current_section;

			$settings = $this->get_settings( $current_section );

			GeoDir_Admin_Settings::output_fields( $settings );
		}

		/**
		 * Save settings.
		 */
		public function save() {
			global $current_section;

			$settings = $this->get_settings( $current_section );
			GeoDir_Admin_Settings::save_fields( $settings );
		}

		/**
		 * Get settings array.
		 *
		 * @return array
		 */
		public function get_settings( $current_section = '' ) {

				$settings = apply_filters( 'woocommerce_product_settings', array(


					array('name' => __('Google Analytics', 'geodirectory'), 'type' => 'title', 'desc' => '', 'id' => 'google_analytic_settings'),

					array('name' => __('Google Analytic Settings', 'geodirectory'), 'type' => 'sectionstart', 'id' => 'google_analytic_settings'),
					array(
						'name' => __('Show business owner?', 'geodirectory'),
						'desc' => __('Show the business owner google analytics stats?', 'geodirectory'),
						'id' => 'ga_stats',
						'std' => '0',
						'type' => 'checkbox',
					),
					array(
						'name' => __('Google analytics access', 'geodirectory'),
						'desc' => '',
						'id' => 'ga_token',
						'type' => 'google_analytics',
						'css' => 'min-width:300px;',
						'std' => ''
					),
					array(
						'name' => __('Google analytics Auth Code', 'geodirectory'),
						'desc' => __('You must save this setting before accounts will show.', 'geodirectory'),
						'id' => 'ga_auth_code',
						'type' => 'text',
						'css' => 'min-width:300px;',
						'std' => ''
					),
					array(
						'name' => __('Analytics Account', 'geodirectory'),
						'desc' => __('Select the account that you setup for this site.', 'geodirectory'),
						'id' => 'ga_account_id',
						'css' => 'min-width:300px;',
						'std' => 'gridview_onehalf',
						'type' => 'select',
						'class' => 'geodir-select',
						'options' => geodir_gd_accounts()
					),
					array(
						'name' => __('Add tracking code to site?', 'geodirectory'),
						'desc' => __('This will automatically add the correct tracking code to your site', 'geodirectory'),
						'id' => 'ga_add_tracking_code',
						'std' => '0',
						'type' => 'checkbox',
					),
					array(
						'name' => __('Anonymize user IP?', 'geodirectory'),
						'desc' => __('In most cases this is not required, this is to comply with certain country laws such as Germany.', 'geodirectory'),
						'id' => 'ga_anonymize_ip',
						'type' => 'checkbox',
						'std' => '0',
						'advanced' => true
					),
					array(
						'name' => __('Auto refresh active users?', 'geodirectory'),
						'desc' => __('If ticked it uses the auto refresh time below, if not it never refreshes unless the refresh button is clicked.', 'geodirectory'),
						'id' => 'geodir_ga_auto_refresh',
						'type' => 'checkbox',
						'std' => '0',
						'advanced' => true
					),
					array(
						'name' => __('Time interval for auto refresh active users', 'geodirectory'),
						'desc' => __('Time interval in seconds to auto refresh active users. The active users will be auto refreshed after this time interval. Leave blank or use 0(zero) to disable auto refresh. Default: 5', 'geodirectory'),
						'id' => 'ga_refresh_time',
						'type' => 'text',
						'std' => '5',
						'class'    => 'gd-advanced-setting',
						'advanced' => true
					),
					array('type' => 'sectionend', 'id' => 'google_analytic_settings'),

				));

			return apply_filters( 'woocommerce_get_settings_' . $this->id, $settings, $current_section );
		}
	}

endif;

return new GeoDir_Settings_Analytics();
