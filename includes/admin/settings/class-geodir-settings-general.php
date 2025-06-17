<?php
/**
 * GeoDirectory General Settings
 *
 * @author      AyeCode
 * @category    Admin
 * @package     GeoDirectory/Admin
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'GeoDir_Settings_General', false ) ) :

/**
 * GeoDir_Settings_General.
 */
class GeoDir_Settings_General extends GeoDir_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'general';
		$this->label = __( 'General', 'geodirectory' );

		add_filter( 'geodir_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'geodir_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'geodir_sections_' . $this->id, array( $this, 'output_sections' ) );
		add_action( 'geodir_settings_save_' . $this->id, array( $this, 'save' ) );
	}

	/**
	 * Get sections.
	 *
	 * @return array
	 */
	public function get_sections() {

		$sections = array(
			''          	=> __( 'General', 'geodirectory' ),
			'location'      => __( 'Default location', 'geodirectory' ),
			'pages' 	    => __( 'Pages', 'geodirectory' ),
			'seo' 	        => __( 'Titles & Meta', 'geodirectory' ),
			'search' 	    => __( 'Search', 'geodirectory' ),
			'dummy_data' 	=> __( 'Dummy Data', 'geodirectory' ),
			'developer' 	=> __( 'Developer', 'geodirectory' ),
			'uninstall' 	=> __( 'Uninstall', 'geodirectory' ),
		);

		return apply_filters( 'geodir_get_sections_' . $this->id, $sections );
	}

	/**
	 * Output the settings.
	 */
	public function output() {
		global $current_section, $hide_save_button;

		$settings = $this->get_settings( $current_section );

		GeoDir_Admin_Settings::output_fields( $settings );

		// hide save button on dummy data page
		if ( 'dummy_data' == $current_section ) {
			$hide_save_button = true;
		}elseif('location' == $current_section ) {
			// check if there are already listing before saving new location
			global $wpdb;
			$post_types        = geodir_get_posttypes();
			$cpt_count   = count( $post_types );
			$cptp        = array_fill( 0, $cpt_count, "%s" );
			$cptp_string = implode( ",", $cptp );
			$has_posts   = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type IN ($cptp_string) LIMIT 1", $post_types ) );
			if ( $has_posts ) {
				?>
				<script>
					jQuery(function () {
						var default_location_city = jQuery("#default_location_city").val();
						jQuery(".geodir-save-button").on("click",function () {
							if(default_location_city && default_location_city != jQuery("#default_location_city").val()){
								return confirm("<?php _e( "Are you sure? This can break current listings.", "geodirectory" );?>");
							}
						});
					});
				</script>
				<?php
			}
		}
	}

	/**
	 * Save settings.
	 */
	public function save() {
		global $current_section, $geodir_fajax_error;

		$settings = $this->get_settings( $current_section );
		GeoDir_Admin_Settings::save_fields( $settings );

		// Check & copy / remove Fast AJAX mu-plugin.
		if ( $current_section == 'developer' && isset( $_REQUEST['fast_ajax'] ) ) {
			$response = geodir_check_fast_ajax_file( ! empty( $_REQUEST['fast_ajax'] ) );

			if ( ! empty( $response ) && is_wp_error( $response ) ) {
				$geodir_fajax_error = $response->get_error_message();
			}
		}
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings( $current_section = '' ) {
		global $geodir_fajax_error, $geodir_fajax_check, $aui_bs5;

		if ( 'developer' == $current_section ) {
			// Fast AJAX file check.
			if ( empty( $geodir_fajax_check ) && empty( $geodir_fajax_error ) && ! isset( $_REQUEST['fast_ajax'] ) && geodir_get_option( 'fast_ajax' ) ) {
				$geodir_fajax_check = true;
				$response = geodir_check_fast_ajax_file( true );

				if ( ! empty( $response ) && is_wp_error( $response ) ) {
					$geodir_fajax_error = $response->get_error_message();
				}
			}

			if ( $geodir_fajax_error ) {
				$geodir_fajax_error = '<br><div class="font-weight-bold fw-bold text-danger"><i class="fa fa-exclamation-circle" aria-hidden="true"></i> ' . strip_tags( $geodir_fajax_error ) . '</div>';
			}

			/**
			 * Filter GD general settings array.
			 *
			 * @since 1.0.0
			 * @package GeoDirectory
			 */
			$settings = apply_filters( 'geodir_developer_options', array(
				array(
					'title' => __( 'Developer Settings', 'geodirectory' ),
					'type'  => 'title',
					'desc'  => '',
					'id'    => 'developer_options',
					//'desc_tip' => true,
				),

				array(
					'id' => 'fast_ajax',
					'type' => 'checkbox',
					'name' => __( 'Enable Fast AJAX', 'geodirectory' ),
					'desc' => __( 'This will speed up AJAX requests to improve AJAX performance within GeoDirectory plugins by using MU plugin.', 'geodirectory' ) . $geodir_fajax_error,
					'default' => '1',
				),

				array(
					'name'     => __( 'Advanced settings', 'geodirectory' ),
					'desc'     => __( 'Disable advanced toggle, show advanced settings at all times (not recommended).', 'geodirectory' ),
					'id'       => 'admin_disable_advanced',
					'type'     => 'checkbox',
				),

				array(
					'name'     => __( 'Enable admin hints', 'geodirectory' ),
					'desc'     => __( 'This will enable admin hint notifications throughout the site to help new GD users find their way around, this will only be visible to admins.', 'geodirectory' ),
					'id'       => 'enable_hints',
					'type'     => 'checkbox',
					'default'  => '1',
				),

				array(
					'name'     => __( 'Enable 404 rescue', 'geodirectory' ),
					'desc'     => __( 'This will check 404 pages where a GD CPT is identified and try and redirect to the correct url. This should help tell search engines about term or post permalink changes.', 'geodirectory' ),
					'id'       => 'enable_404_rescue',
					'type'     => 'checkbox',
					'default'  => '1',
				),

				array(
					'name'     => __( 'Enable beta addons', 'geodirectory' ),
					'desc'     => __( 'This will allow beta addons to be installed.', 'geodirectory' ),
					'id'       => 'admin_enable_beta',
					'type'     => 'checkbox',
					'default'  => '1',
				),

				array(
					'name'     => __( 'Enable BIG Data Optimizations', 'geodirectory' ),
					'desc'     => __( 'This will help with directories over 50k listings (can slow down smaller directories)', 'geodirectory' ),
					'id'       => 'enable_big_data',
					'type'     => 'checkbox',
//					'default'  => '0',
				),

				array(
					'name' => __( 'Disable Scripts On Call', 'geodirectory' ),
					'desc' => __( 'Scripts on call feature loads JavaScript files(ex: map) only when required on the pages on frontend. Tick to disable if any JavaScript error on the page.', 'geodirectory' ),
					'id' => 'disable_scripts_on_call',
					'type' => 'checkbox',
				),

				// @todo to be move to own design section
				array(
					'id' => 'design_style',
					'name' => __('Default Design Style', 'geodirectory'),
					'desc' => wp_sprintf( __( 'The default design style to use. Go to Settings > %s AyeCode UI %s to adjust AyeCode UI settings if you are having compatibility issues with Bootstrap style.', 'geodirectory' ), '<a href="' . admin_url( 'options-general.php?page=ayecode-ui-settings' ) . '">', '</a>' ),
					'type' => 'select',
					'options' => array(
						'bootstrap' =>  __('Bootstrap', 'geodirectory'),
						'' =>  __('Legacy (non-bootstrap)', 'geodirectory'),
					),
					'class' => 'uwp-select',
					'desc_tip' => false,
					'default' => 'bootstrap',
				),
				array( 'type' => 'sectionend', 'id' => 'developer_options' ),
			));
		}elseif ( 'uninstall' == $current_section ) {

			/**
			 * Filter GD general settings array.
			 *
			 * @since 1.0.0
			 * @package GeoDirectory
			 */
			$settings = apply_filters( 'geodir_uninstall_options', array(
				array(
					'title' => __( 'Uninstall Settings', 'geodirectory' ),
					'type'  => 'title',
					'desc'  => '<b>' . __( 'NOTE: Addons should be deleted before core to ensure complete uninstall.', 'geodirectory' ) . '</b>',
					'id'    => 'uninstall_options'
				),

				array(
					'name'     => __( 'Remove Data on Uninstall?', 'geodirectory' ),
					'desc'     => __( 'Check this box if you would like GeoDirectory to completely remove all of its data when the plugin is deleted.', 'geodirectory' ),
					'id'       => 'admin_uninstall',
					'type'     => 'checkbox'
				),
				array( 'type' => 'sectionend', 'id' => 'uninstall_options' ),
			));
		}
		elseif ( 'dummy_data' == $current_section ) {

			/**
			 * Filter GD general settings array.
			 *
			 * @since 1.0.0
			 * @package GeoDirectory
			 */
			$settings = apply_filters( 'geodir_dummy_data', array(
				array(
					'title' => __( 'Dummy data installer', 'geodirectory' ),
					'type'  => 'title',
					'desc'  => '*Hint*: Installing our Advanced Search addon FIRST will add extra search fields to non-default data types.',
					'id'    => 'dummy_data'
				),

				array(
					'name' => '',
					'desc' => '',
					'id' => 'geodir_dummy_data_installer',
					'type' => 'dummy_installer',
					'css' => 'min-width:300px;',
					'std' => '40'
				),
				array( 'type' => 'sectionend', 'id' => 'dummy_data' ),
			));
		}
		else if ( 'pages' == $current_section ) {

			$gutenberg = geodir_is_gutenberg();

			/**
			 * Filter GD general settings array.
			 *
			 * @since 1.0.0
			 * @package GeoDirectory
			 */
			$settings = apply_filters( 'geodir_page_options', array(
				array(
					'title' => __( 'Page Settings', 'geodirectory' ),
					'type'  => 'title',
					'desc'  => 'These are essential pages used by GD, you can set the pages here and edit the title/slug of the page via WP page settings.',
					'id'    => 'page_options',
					'desc_tip' => true,
				),
				array(
					'name'     => __( 'Location page', 'geodirectory' ),
					'desc'     => __( 'Select the page to use for locations', 'geodirectory' ),
					'id'       => 'page_location',
					'type'     => 'single_select_page',
					'class'      => 'geodir-select',
					'desc_tip' => true,
					'default_content' => GeoDir_Defaults::page_location_content( false, $gutenberg ),
				),
				array(
					'name'     => __( 'Add listing page', 'geodirectory' ),
					'desc'     => __( 'Select the page to use for adding listings', 'geodirectory' ),
					'id'       => 'page_add',
					'type'     => 'single_select_page',
					'class'      => 'geodir-select',
					'desc_tip' => true,
					'default_content' => GeoDir_Defaults::page_add_content( false, $gutenberg ),
				),
				array(
					'name'     => __( 'Search Page', 'geodirectory' ),
					'desc'     => __( 'Select the page to use as the GD search page', 'geodirectory' ),
					'id'       => 'page_search',
					'type'     => 'single_select_page',
					'class'      => 'geodir-select',
					'desc_tip' => true,
					'default_content' => GeoDir_Defaults::page_search_content( false, $gutenberg ),
				),
				array(
					'name'     => __( 'Terms and Conditions page', 'geodirectory' ),
					'desc'     => __( 'Select the page to use for your terms and conditions.', 'geodirectory' ),
					'id'       => 'page_terms_conditions',
					'type'     => 'single_select_page',
					'class'      => 'geodir-select',
					'desc_tip' => true,
					'default_content' => __('ENTER YOUR SITE TERMS AND CONDITIONS HERE','geodirectory')
				),
				array( 'type' => 'sectionend', 'id' => 'page_options' ),

				array(
					'title' => __( 'Template Page Settings', 'geodirectory' ),
					'type'  => 'title',
					'desc'  => 'Template pages are used to design the respective pages and should never be linked to directly.',
					'id'    => 'page_template_options',
					'desc_tip' => true,
				),

				array(
					'name'     => __( 'Details Page', 'geodirectory' ),
					'desc'     => __( 'Select the page to use as the GD details page template', 'geodirectory' ),
					'id'       => 'page_details',
					'type'     => 'single_select_page',
					'is_template_page'     => true,
					'class'      => 'geodir-select',
					'desc_tip' => true,
					'default_content' => GeoDir_Defaults::page_details_content(false, $gutenberg ),
				),
				array(
					'name'     => __( 'Archive page', 'geodirectory' ),
					'desc'     => __( 'Select the page to use for GD archives such as taxonomy and CPT pages', 'geodirectory' ),
					'id'       => 'page_archive',
					'type'     => 'single_select_page',
					'is_template_page'     => true,
					'class'      => 'geodir-select',
					'desc_tip' => true,
					'default_content' => GeoDir_Defaults::page_archive_content(false, $gutenberg ),
				),
				array(
					'name'     => __( 'Archive item page', 'geodirectory' ),
					'desc'     => __( 'Select the page to use for GD archive items, this is the item template used on taxonomy and CPT pages', 'geodirectory' ),
					'id'       => 'page_archive_item',
					'type'     => 'single_select_page',
					'is_template_page'     => true,
					'class'      => 'geodir-select',
					'desc_tip' => true,
					'default_content' => GeoDir_Defaults::page_archive_item_content( false, $gutenberg ),
				),

				array( 'type' => 'sectionend', 'id' => 'page_template_options' ),
			));
		}
		else if ( 'seo' == $current_section ) {
			/**
			 * Filter GD general settings array.
			 *
			 * @since 1.0.0
			 * @package GeoDirectory
			 */
			$settings = apply_filters( 'geodir_seo_options', array(
				array(
					'title' => __( 'Titles & Meta Settings', 'geodirectory' ),
					'type'  => 'title',
					'desc'  => 'Here you can set the titles and meta info for your GeoDirectory pages. <b>Click the tags to copy to clipboard</b>',
					'id'    => 'seo_options',
					//'desc_tip' => true,
				),

				array( 'type' => 'sectionend', 'id' => 'seo_archive_options' ),

				// CPT archive
				array(
					'title' => __( 'Post type page', 'geodirectory' ),
					'type'  => 'title',
					'desc'  => 'The root page of a GD CPT eg: /places/',
					'id'    => 'seo_cpt',
					'desc_tip' => true,
					'seo_helper_tags' => 'pt'
				),

				array(
					'name'     => __( 'Title', 'geodirectory' ),
					'desc'     => __( 'Enter the title to use for the page', 'geodirectory' ),
					'id'       => 'seo_cpt_title',
					'type'     => 'text',
					'placeholder' => GeoDir_Defaults::seo_cpt_title(),
					'class'     => 'active-placeholder',
					'desc_tip' => true,
				),

				array(
					'name'     => __( 'Meta Title', 'geodirectory' ),
					'desc'     => __( 'Enter the meta title to use for the page', 'geodirectory' ),
					'id'       => 'seo_cpt_meta_title',
					'type'     => 'text',
					'placeholder' => GeoDir_Defaults::seo_cpt_meta_title(),
					'class'     => 'active-placeholder',
					'desc_tip' => true,
				),

				array(
					'name'     => __( 'Meta Description', 'geodirectory' ),
					'desc'     => __( 'Enter the meta description to use for the page', 'geodirectory' ),
					'id'       => 'seo_cpt_meta_description',
					'type'     => 'textarea',
					'placeholder' => GeoDir_Defaults::seo_cpt_meta_description(),
					'class'     => 'active-placeholder',
					'desc_tip' => true,
				),

				array( 'type' => 'sectionend', 'id' => 'seo_cpt' ),

				// Archive
				array(
					'title' => __( 'Archive pages', 'geodirectory' ),
					'type'  => 'title',
					'desc'  => 'The GD category and tags pages.',
					'id'    => 'seo_archive',
					'desc_tip' => true,
					'seo_helper_tags' => 'archive'
				),

				array(
					'name'     => __( 'Category Title', 'geodirectory' ),
					'desc'     => __( 'Enter the title to use for the page', 'geodirectory' ),
					'id'       => 'seo_cat_archive_title',
					'type'     => 'text',
					'placeholder' => GeoDir_Defaults::seo_cat_archive_title(),
					'class'     => 'active-placeholder',
					'desc_tip' => true,
				),

				array(
					'name'     => __( 'Category Meta Title', 'geodirectory' ),
					'desc'     => __( 'Enter the meta title to use for the page', 'geodirectory' ),
					'id'       => 'seo_cat_archive_meta_title',
					'type'     => 'text',
					'placeholder' => GeoDir_Defaults::seo_cat_archive_meta_title(),
					'class'     => 'active-placeholder',
					'desc_tip' => true,
				),

				array(
					'name'     => __( 'Category Meta Description', 'geodirectory' ),
					'desc'     => __( 'Enter the meta description to use for the page', 'geodirectory' ),
					'id'       => 'seo_cat_archive_meta_description',
					'type'     => 'textarea',
					'placeholder' => GeoDir_Defaults::seo_cat_archive_meta_description(),
					'class'     => 'active-placeholder',
					'desc_tip' => true,
				),

				array(
					'name'     => __( 'Tag Title', 'geodirectory' ),
					'desc'     => __( 'Enter the title to use for the page', 'geodirectory' ),
					'id'       => 'seo_tag_archive_title',
					'type'     => 'text',
					'placeholder' => GeoDir_Defaults::seo_tag_archive_title(),
					'class'     => 'active-placeholder',
					'desc_tip' => true,
				),

				array(
					'name'     => __( 'Tag Meta Title', 'geodirectory' ),
					'desc'     => __( 'Enter the meta title to use for the page', 'geodirectory' ),
					'id'       => 'seo_tag_archive_meta_title',
					'type'     => 'text',
					'placeholder' => GeoDir_Defaults::seo_tag_archive_meta_title(),
					'class'     => 'active-placeholder',
					'desc_tip' => true,
				),

				array(
					'name'     => __( 'Tag Meta Description', 'geodirectory' ),
					'desc'     => __( 'Enter the meta description to use for the page', 'geodirectory' ),
					'id'       => 'seo_tag_archive_meta_description',
					'type'     => 'textarea',
					'placeholder' => GeoDir_Defaults::seo_tag_archive_meta_description(),
					'class'     => 'active-placeholder',
					'desc_tip' => true,
				),

				array( 'type' => 'sectionend', 'id' => 'seo_archive' ),

				// Single (details)
				array(
					'title' => __( 'Single post pages', 'geodirectory' ),
					'type'  => 'title',
					'desc'  => 'The GD single post details page.',
					'id'    => 'seo_single',
					'desc_tip' => true,
					'seo_helper_tags' => 'single'
				),

				array(
					'name'     => __( 'Title', 'geodirectory' ),
					'desc'     => __( 'Enter the title to use for the page', 'geodirectory' ),
					'id'       => 'seo_single_title',
					'type'     => 'text',
					'placeholder' => GeoDir_Defaults::seo_single_title(),
					'class'     => 'active-placeholder',
					'desc_tip' => true,
				),

				array(
					'name'     => __( 'Meta Title', 'geodirectory' ),
					'desc'     => __( 'Enter the meta title to use for the page', 'geodirectory' ),
					'id'       => 'seo_single_meta_title',
					'type'     => 'text',
					'placeholder' => GeoDir_Defaults::seo_single_meta_title(),
					'class'     => 'active-placeholder',
					'desc_tip' => true,
				),

				array(
					'name'     => __( 'Meta Description', 'geodirectory' ),
					'desc'     => __( 'Enter the meta description to use for the page', 'geodirectory' ),
					'id'       => 'seo_single_meta_description',
					'type'     => 'textarea',
					'placeholder' => GeoDir_Defaults::seo_single_meta_description(),
					'class'     => 'active-placeholder',
					'desc_tip' => true,
				),

				array( 'type' => 'sectionend', 'id' => 'seo_single' ),


				// location page
				array(
					'title' => __( 'Location page', 'geodirectory' ),
					'type'  => 'title',
					'desc'  => 'The location page.',
					'id'    => 'seo_location',
					'desc_tip' => true,
					'seo_helper_tags' => 'location'
				),

				array(
					'name'     => __( 'Title', 'geodirectory' ),
					'desc'     => __( 'Enter the title to use for the page', 'geodirectory' ),
					'id'       => 'seo_location_title',
					'type'     => 'text',
					'placeholder' => GeoDir_Defaults::seo_location_title(),
					'class'     => 'active-placeholder',
					'desc_tip' => true,
				),

				array(
					'name'     => __( 'Meta Title', 'geodirectory' ),
					'desc'     => __( 'Enter the meta title to use for the page', 'geodirectory' ),
					'id'       => 'seo_location_meta_title',
					'type'     => 'text',
					'placeholder' => GeoDir_Defaults::seo_location_meta_title(),
					'class'     => 'active-placeholder',
					'desc_tip' => true,
				),

				array(
					'name'     => __( 'Meta Description', 'geodirectory' ),
					'desc'     => __( 'Enter the meta description to use for the page', 'geodirectory' ),
					'id'       => 'seo_location_meta_description',
					'type'     => 'textarea',
					'placeholder' => GeoDir_Defaults::seo_location_meta_description(),
					'class'     => 'active-placeholder',
					'desc_tip' => true,
				),

				array( 'type' => 'sectionend', 'id' => 'seo_location' ),

				// search page
				array(
					'title' => __( 'Search page', 'geodirectory' ),
					'type'  => 'title',
					'desc'  => 'The search page.',
					'id'    => 'seo_search',
					'desc_tip' => true,
					'seo_helper_tags' => 'search'
				),

				array(
					'name'     => __( 'Title', 'geodirectory' ),
					'desc'     => __( 'Enter the title to use for the page', 'geodirectory' ),
					'id'       => 'seo_search_title',
					'type'     => 'text',
					'placeholder' => GeoDir_Defaults::seo_search_title(),
					'class'     => 'active-placeholder',
					'desc_tip' => true,
				),

				array(
					'name'     => __( 'Meta Title', 'geodirectory' ),
					'desc'     => __( 'Enter the meta title to use for the page', 'geodirectory' ),
					'id'       => 'seo_search_meta_title',
					'type'     => 'text',
					'placeholder' => GeoDir_Defaults::seo_search_meta_title(),
					'class'     => 'active-placeholder',
					'desc_tip' => true,
				),

				array(
					'name'     => __( 'Meta Description', 'geodirectory' ),
					'desc'     => __( 'Enter the meta description to use for the page', 'geodirectory' ),
					'id'       => 'seo_search_meta_description',
					'type'     => 'textarea',
					'placeholder' => GeoDir_Defaults::seo_search_meta_description(),
					'class'     => 'active-placeholder',
					'desc_tip' => true,
				),

				array( 'type' => 'sectionend', 'id' => 'seo_search' ),

				// add listing
				array(
					'title' => __( 'Add listing page', 'geodirectory' ),
					'type'  => 'title',
					'desc'  => 'The add listing page.',
					'id'    => 'seo_add_listing',
					'desc_tip' => true,
					'seo_helper_tags' => 'add-listing'
				),

				array(
					'name'     => __( 'Add Title', 'geodirectory' ),
					'desc'     => __( 'Enter the title to use for the page', 'geodirectory' ),
					'id'       => 'seo_add_listing_title',
					'type'     => 'text',
					'placeholder' => GeoDir_Defaults::seo_add_listing_title(),
					'class'     => 'active-placeholder',
					'desc_tip' => true,
				),

				array(
					'name'     => __( 'Edit Title', 'geodirectory' ),
					'desc'     => __( 'Enter the title to use for the page', 'geodirectory' ),
					'id'       => 'seo_add_listing_title_edit',
					'type'     => 'text',
					'placeholder' => GeoDir_Defaults::seo_add_listing_title_edit(),
					'class'     => 'active-placeholder',
					'desc_tip' => true,
				),

				array(
					'name'     => __( 'Meta Title', 'geodirectory' ),
					'desc'     => __( 'Enter the meta title to use for the page', 'geodirectory' ),
					'id'       => 'seo_add_listing_meta_title',
					'type'     => 'text',
					'placeholder' => GeoDir_Defaults::seo_add_listing_meta_title(),
					'class'     => 'active-placeholder',
					'desc_tip' => true,
				),

				array(
					'name'     => __( 'Meta Description', 'geodirectory' ),
					'desc'     => __( 'Enter the meta description to use for the page', 'geodirectory' ),
					'id'       => 'seo_add_listing_meta_description',
					'type'     => 'textarea',
					'placeholder' => GeoDir_Defaults::seo_add_listing_meta_description(),
					'class'     => 'active-placeholder',
					'desc_tip' => true,
				),

				array( 'type' => 'sectionend', 'id' => 'seo_add_listing' ),
			));
		}

		else if ( 'search' == $current_section ) {
			/**
			 * Filter GD general settings array.
			 *
			 * @since 1.0.0
			 * @package GeoDirectory
			 */
			$settings = apply_filters( 'geodir_search_options', array(
				array(
					'title' => __( 'Search bar settings', 'geodirectory' ),
					'type'  => 'title',
					'id'    => 'search_options',
				),

				array(
					'name' => __('Search field placeholder text', 'geodirectory'),
					'desc' => __('Show the search text box `placeholder` value on search form.', 'geodirectory'),
					'id' => 'search_default_text',
					'type' => 'text',
					'placeholder' => geodir_get_search_default_text(),
					'desc_tip' => true,
					'default'  => '',
					'advanced' => false
				),
				array(
					'name' => __('Near field placeholder text', 'geodirectory'),
					'desc' => __('Show the near text box \'placeholder\' value on search form.', 'geodirectory'),
					'id' => 'search_default_near_text',
					'type' => 'text',
					'placeholder' => geodir_get_search_default_near_text(),
					'desc_tip' => true,
					'default'  => '',
					'advanced' => false
				),
				array(
					'name' => __('Search button label', 'geodirectory'),
					'desc' => __('Show the search button label on search form. You can use a font awesome class here.', 'geodirectory'),
					'id' => 'search_default_button_text',
					'type' => 'font-awesome',
					'placeholder' => geodir_get_search_default_button_text(),
					'desc_tip' => true,
					'default'  => '',
					'advanced' => true
				),

				array( 'type' => 'sectionend', 'id' => 'search_options' ),

				array(
					'title' => __( 'Search results settings', 'geodirectory' ),
					'type'  => 'title',
					'id'    => 'search_results_options',
				),

				array(
					'name' => __('Search near radius', 'geodirectory'),
					'desc' => __('Limits the search radius to X miles/km (lower numbers help with speed) ', 'geodirectory'),
					'id' => 'search_radius',
					'type' => 'number',
					'default'  => '7', // largest city in the world is 6.33
					'desc_tip' => true,
					'advanced' => false,
					'custom_attributes' => array(
						'min' => '0.1',
						'step' => '0.01',
						'lang' => 'EN'
					)
				),
				array(
					'name' => __('Search distances', 'geodirectory'),
					'desc' => __('Show search distances in miles or km', 'geodirectory'),
					'id' => 'search_distance_long',
					'type' => 'select',
					'class' => 'geodir-select',
					'options' => array(
						'miles' => __('Miles', 'geodirectory'),
						'km' => __('Kilometers', 'geodirectory')
					),
					'desc_tip' => true,
					'default'  => 'miles',
					'advanced' => false
				),
				array(
					'name' => __('Search distances short', 'geodirectory'),
					'desc' => __('If distance is less than 0.01 show distance in meters or feet', 'geodirectory'),
					'id' => 'search_distance_short',
					'type' => 'select',
					'class' => 'geodir-select',
					'options' => array(
						'feet' => __('Feet', 'geodirectory'),
						'meters' => __('Meters', 'geodirectory')
					),
					'default'  => 'feet',
					'desc_tip' => true,
					'advanced' => false
				),
				array(
					'name' => __('Search near additional', 'geodirectory'),
					'desc' => __('This is useful if your directory is limited to one location such as: New York or Australia (this setting should be blank if using default country, regions etc with multilocation addon as it will automatically add them)', 'geodirectory'),
					'id' => 'search_near_addition',
					'type' => 'text',
					'placeholder' => __('New York','geodirectory'),
					'default'  => '',
					'desc_tip' => true,
					'advanced' => true
				),
				array(
					'name' => __('Individual word search limit', 'geodirectory'),
					'desc' => __('With this option you can limit individual words being searched for, for example searching for `Jo Brown` would return results with words like `Jones`, you can exclude these types of small character words if you wish.', 'geodirectory'),
					'id' => 'search_word_limit',
					'type' => 'select',
					'class' => 'geodir-select',
					'options' => array_unique(array(
						'0' => __('Disabled', 'geodirectory'),
						'1' => __('1 Character words excluded', 'geodirectory'),
						'2' => __('2 Character words and less excluded', 'geodirectory'),
						'3' => __('3 Character words and less excluded', 'geodirectory'),
					)),
					'default'  => '0',
					'desc_tip' => true,
					'advanced' => true
				),

				array( 'type' => 'sectionend', 'id' => 'search_results_options' ),
			));
		}
		else if ( 'location' == $current_section ) {
			/**
			 * Filter GD general settings array.
			 *
			 * @since 1.0.0
			 * @package GeoDirectory
			 */
			$settings = apply_filters( 'geodir_default_location', array(
				array(
					'title' => __( 'Set default location', 'geodirectory' ),
					'type'  => 'title',
					'desc'  => 'Drag the map or the marker to set the city/town you wish to use as the default location, then click save changes.',
					'id'    => 'default_location'
				),

				array(
					'name'     => __( 'City', 'geodirectory' ),
					'desc'     => __( 'The default location city name.', 'geodirectory' ),
					'id'       => 'default_location_city',
					'type'     => 'text',
					'css'      => 'min-width:300px;',
					'desc_tip' => true,
					'default'  => 'Philadelphia',
					'advanced' => true
				),
				array(
					'name'     => __( 'Region', 'geodirectory' ),
					'desc'     => __( 'The default location region name.', 'geodirectory' ),
					'id'       => 'default_location_region',
					'type'     => 'text',
					'css'      => 'min-width:300px;',
					'desc_tip' => true,
					'default'  => 'Pennsylvania',
					'advanced' => true
				),
				array(
					'name'     => __( 'Country', 'geodirectory' ),
					'desc'     => __( 'The default location country name.', 'geodirectory' ),
					'id'       => 'default_location_country',
					'css'      => 'min-width:300px;',
					'desc_tip' => true,
					'advanced' => true,
					'type'       => 'single_select_country',
					'class'      => $aui_bs5 ? 'aui-select2' : 'geodir-select',
					'default'  => 'United States',
					'options'    => geodir_get_countries()
				),

				array(
					'name'     => __( 'City Latitude', 'geodirectory' ),
					'desc'     => __( 'The latitude of the default location.', 'geodirectory' ),
					'id'       => 'default_location_latitude',
					'type' => 'number',
					'custom_attributes' => array(
						'min'           => '-90',
						'max'           => '90',
						'step'          => 'any',
						'lang'          => 'EN'
					),
					'desc_tip' => true,
					'default'  => '39.9523894183957',
					'advanced' => true
				),

				array(
					'name'     => __( 'City Longitude', 'geodirectory' ),
					'desc'     => __( 'The longitude of the default location.', 'geodirectory' ),
					'id'       => 'default_location_longitude',
					'type' => 'number',
					'custom_attributes' => array(
						'min'           => '-180',
						'max'           => '180',
						'step'          => 'any',
						'lang'          => 'EN'
					),
					'desc_tip' => true,
					'default'  => '-75.16359824536897',
					'advanced' => true
				),
				array(
					'name'     => __( 'Timezone', 'geodirectory' ),
					'desc'     => __( 'Select a city/timezone.', 'geodirectory' ),
					'id'       => 'default_location_timezone_string',
					'css'      => 'min-width:300px;',
					'desc_tip' => true,
					'advanced' => true,
					'type'     => 'single_select_timezone',
					'class'    => 'geodir-select',
					'default'  => geodir_timezone_string(),
					'options'  => array()
				),
				array(
					'type'     => 'checkbox',
					'id'       => 'multi_city',
					'name'     => __( 'Remove default city limit', 'geodirectory' ),
					'desc'     => __( 'This will allow listings to be added anywhere (outside default location).', 'geodirectory' ),
					'default'  => '1',
					'desc_tip' => false,
					'advanced' => false,
				),

				array(
					'id'       => 'default_location_map',
					'type'     => 'default_location_map',
				),

				array( 'type' => 'sectionend', 'id' => 'default_location' ),
			));
		} else {
			/**
			 * Filter GD general pre settings array.
			 *
			 * @since 2.1.1.12
			 *
			 * @param array  $settings Settings array.
			 * @param string $current_section Current section.
			 */
			$settings = apply_filters( 'geodir_general_default_options', array(), $current_section );

			if ( empty( $settings ) ) {
				/**
				 * Filter GD general settings array.
				 *
				 * @since 1.0.0
				 * @package GeoDirectory
				 */
				$settings = apply_filters( 'geodir_general_options', array(
					array(
						'title' => __( 'Site Settings', 'geodirectory' ),
						'type'  => 'title',
						'desc'  => '',
						'id'    => 'general_options'
					),

				array(
					'name'       => __( 'Restrict wp-admin', 'geodirectory' ),
					'desc'       => __( 'The user roles that should be restricted from the wp-admin area.', 'geodirectory' ),
					'id'         => 'admin_blocked_roles',
					'default'    => array('subscriber'),
					'type'       => 'multiselect',
					'placeholder'=> __('Select roles to restrict from the wp-admin','geodirectory'),
					'class'      => $aui_bs5 ? 'aui-select2' : 'geodir-select',
					'options'    => geodir_user_roles(array('administrator')),
					'desc_tip'   => true
				),

				array(
					'name'       => __( 'Allow shortcodes in description to', 'geodirectory' ),
					'desc'       => __( 'The user roles that should be allowed to use shortcodes/blocks in listing description.', 'geodirectory' ),
					'id'         => 'shortcodes_allowed_roles',
					'default'    => array( 'administrator' ),
					'type'       => 'multiselect',
					'placeholder'=> __( 'Select User Roles...', 'geodirectory' ),
					'class'      => $aui_bs5 ? 'aui-select2' : 'geodir-select',
					'options'    => geodir_user_roles(),
					'desc_tip'   => true
				),

				array( 'type' => 'sectionend', 'id' => 'general_options' ),

				array(
					'title' => __( 'Listing Settings', 'geodirectory' ),
					'type'  => 'title',
					'desc'  => '',
					'id'    => 'general_options_add'
				),

				array(
					'name' => __( 'User deleted posts', 'geodirectory' ),
					'desc' => __( 'If checked a user deleted post will go to trash, otherwise it will be permanently deleted', 'geodirectory' ),
					'id'   => 'user_trash_posts',
					'type' => 'checkbox',
					'default'  => '1'

				),
				array(
					'name'       => __( 'New listing default status', 'geodirectory' ),
					'desc'       => __( 'This is the post status a new listing will get when submitted from the frontend.', 'geodirectory' ),
					'id'         => 'default_status',
					'default'    => 'pending',
					'type'       => 'select',
					'options' => array_unique(array(
						'pending' => __('Pending Review', 'geodirectory'),
						'publish' => __('Publish', 'geodirectory'),

					)),
					'desc_tip' => true
				),
				array(
					'name' => __( 'Allow posting without logging in?', 'geodirectory' ),
					'desc' => defined('WPE_PLUGIN_VERSION') ? __( 'If checked non logged in users will be able to post listings from the frontend.', 'geodirectory' ) . " <span style='color:red'>" . sprintf( __( 'WP ENGINE DETECTED: please see %sthis guide%s for this feature to work properly.', 'geodirectory' ),'<a href="https://wpgeodirectory.com/documentation/article/troubleshooting/how-to-allow-posting-without-logging-in-when-using-wp-engine-hosting">','</a>' ) . "</span>" : __( 'If checked non logged in users will be able to post listings from the frontend.', 'geodirectory' ),
					'id'   => 'post_logged_out',
					'type' => 'checkbox',
					'default'  => '0',
					'advanced' => true
				),

				array(
					'name' => __( 'Show preview button?', 'geodirectory' ),
					'desc' => __( 'If checked a preview button will be shown on the add listing page so uses can preview their post.', 'geodirectory' ),
					'id'   => 'post_preview',
					'type' => 'checkbox',
					'default'  => '1',
					'advanced' => true
				),

				array(
					'name' => __( 'Max upload file size(in mb)', 'geodirectory' ),
					'desc' => __( '(Maximum upload file size in MB, 1 MB = 1024 KB. Must be greater then 0(ZERO), for ex: 2. This setting will overwrite the max upload file size limit in image/file upload & import listings for entire GeoDirectory core + GeoDirectory plugins.)', 'geodirectory' ).wp_max_upload_size(),
					'id'   => 'upload_max_filesize',
					'type' => 'number',
					'css'  => 'min-width:300px;',
					'default'  => '2',
					'custom_attributes' => array(
						'min' => '0.1',
						'step' => '0.1',
						'lang' => 'EN'
					),
					'desc_tip' => true,
					'advanced' => true
				),

				array(
					'name' => __( 'Noindex empty archives?', 'geodirectory' ),
					'desc' => __( 'If checked this will attempt to add `noindex` tags to empty GD archive pages.', 'geodirectory' ),
					'id'   => 'noindex_archives',
					'type' => 'checkbox',
					'default'  => '0',
					'advanced' => true
				),

				array( 'type' => 'sectionend', 'id' => 'general_options_add' ),

				array(
					'title' => __( 'Map Settings', 'geodirectory' ),
					'type'  => 'title',
					'desc'  => '',
					'id'    => 'general_options_map'
				),

				self::get_google_maps_api_key_setting(),

				self::get_google_geocode_api_key_setting(),

				self::get_maps_api_setting(),

				self::get_maps_lazy_load_setting(),

				self::get_map_language_setting(),

				array(
					'name'     => __( 'Default marker icon', 'geodirectory' ),
					'desc'     => __( 'This is the marker icon used if the category does not have a marker icon set.', 'geodirectory' ),
					'id'       => 'map_default_marker_icon',
					'type'     => 'image',
					'default'  => '',
					'desc_tip' => true,
					'advanced' => true
				),

				array(
					'id' => 'split_uk',
					'type' => 'checkbox',
					'name' => __( 'Split United Kingdom', 'geodirectory' ),
					'desc' => __( 'Split the United Kingdom into England, Northern Ireland, Scotland & Wales. <span style="color:red;">(NOTE: If enabled then existing records will need to be updated manually or via import/export.)</span>', 'geodirectory' ),
					'default' => '0',
					'desc_tip' => false,
					'advanced' => true
				),

				array(
					'name' => __('Enable map cache', 'geodirectory'), // @todo we need to port this over from GDv1
					'desc' => __('This will cache the map JSON for 24 hours or until a GD listing is saved.', 'geodirectory'),
					'id' => 'map_cache',
					'type' => 'checkbox',
					'default'  => '0',
					'desc_tip' => false,
					'advanced' => true
				),

				array( 'type' => 'sectionend', 'id' => 'general_options_map' ),

				array(
					'title' => __( 'Tracking Settings', 'geodirectory' ),
					'type'  => 'title',
					'desc'  => '',
					'id'    => 'general_options_tracking',
					'advanced' => true
				),

				array(
					'name' => __( 'Allow Usage Tracking?', 'geodirectory' ),
					'desc' => sprintf( __( 'Want to help make GeoDirectory even more awesome? Allow GeoDirectory to collect non-sensitive diagnostic data and usage information. %1$sFind out more%2$s.', 'geodirectory' ), '<a href="https://wpgeodirectory.com/usage-tracking/" target="_blank">', '</a>' ),
					'id'   => 'usage_tracking',
					'type' => 'checkbox',
					'default'  => '',
					'advanced' => true
				),

				array( 'type' => 'sectionend', 'id' => 'general_options_map' ),

				) );/* General Options End*/
			}
		}

		return apply_filters( 'geodir_get_settings_' . $this->id, $settings );
	}

    /**
     * Get map language settings.
     *
     * @since 2.0.0
     *
     * @return array Language settings.
     */
	public static function get_map_language_setting(){
		global $aui_bs5;
		return array(
			'name'       => __( 'Default map language', 'geodirectory' ),
			'desc'       => __( 'URLs will only be in one language, this will determine the language location slugs get. You should avoid changing this after listings have been added.', 'geodirectory' ),
			'id'         => 'map_language',
			'default'    => 'en',
			'type'       => 'select',
			'class'      => $aui_bs5 ? 'aui-select2' : 'geodir-select',
			'options'    => self::supported_map_languages(),
			'desc_tip' => true,
			'advanced' => true
		);
	}

    /**
     * Get Maps api settings.
     *
     * @since 2.0.0
     *
     * @return array Map Api settings.
     */
	public static function get_maps_api_setting(){
		global $aui_bs5;
		return array(
			'name'       => __( 'Maps API', 'geodirectory' ),
			'desc'       => __( "- Google Maps API will force to load Google JS library only.
- OpenStreetMap API will force to load OpenStreetMap JS library only.
- Load Automatic will load Google JS library first, but if Google maps JS library not loaded it then loads the OpenStreetMap JS library to load the maps (recommended for regions where Google maps banned).
- Disable Maps will disable and hides maps for entire site.", 'geodirectory' ),
			'id'         => 'maps_api',
			'default'    => 'auto',
			'type'       => 'select',
			'class'      => $aui_bs5 ? 'aui-select2' : 'geodir-select',
			'options'    => self::supported_maps_apis(),
			'desc_tip' => true,
			'advanced' => true
		);
	}

	/**
	 * Map lazy load settings.
	 *
	 * @since 2.1.0.0
	 *
	 * @return array Settings.
	 */
	public static function get_maps_lazy_load_setting(){
		global $aui_bs5;
		return array(
			'name'       => __( 'Lazy Load Maps', 'geodirectory' ),
			'desc'       => __( "How to load maps on frontend.", 'geodirectory' ),
			'id'         => 'maps_lazy_load',
			'default'    => '',
			'type'       => 'select',
			'class'      => $aui_bs5 ? 'aui-select2' : 'geodir-select',
			'options'    => array(
				'' => __( 'Off (no lazy loading)', 'geodirectory' ),
				'auto' => __( 'Auto (load when map visible on page scroll)', 'geodirectory' ),
				'click' => __( 'Click to Load (show a button to load map)', 'geodirectory' ),
			),
			'desc_tip' => true,
			'advanced' => true
		);
	}

    /**
     * Get google maps api key settings.
     *
     * @since 2.0.0
     *
     * @return array Google maps api key settings.
     */
	public static function get_google_maps_api_key_setting(){
		return array(
			'name' => __( 'Google Maps API KEY', 'geodirectory' ),
			'desc' => __( 'Google Maps <b>requires</b> an API key. Use the button to create a properly configured key using your Google account. </br>Advanced: Open Street Map (OSM) API options.', 'geodirectory' ),
			'id'   => 'google_maps_api_key',
			'type' => 'map_key',
			'default'  => '',
			'desc_tip' => true,
			'placeholder' => __( 'Leave this blank to use Open Street Maps (OSM)', 'geodirectory' )
			//'advanced' => true
		);
	}

	/**
     * Get Google Geocoding API key settings.
     *
     * @since 2.0.0.64
     *
     * @return array Google Geocoding api key settings.
     */
	public static function get_google_geocode_api_key_setting(){
		return array(
			'type' => 'geocode_key',
			'id' => 'google_geocode_api_key',
			'name' => __( 'Google Geocoding API Key', 'geodirectory' ),
			'desc' => __( 'If above Google MAPs API key is restricted by HTTP referrers then it requires to use separate API key for Geocoding & Timezone services.', 'geodirectory' ),
			'default'  => '',
			'placeholder' => geodir_get_option( 'google_maps_api_key' ),
			'desc_tip' => true,
			'advanced' => true
		);
	}

	/**
	 * Output a color picker input box.
	 *
	 * @param mixed $name
	 * @param string $id
	 * @param mixed $value
	 * @param string $desc (default: '')
	 */
	public function color_picker( $name, $id, $value, $desc = '' ) {
		echo '<div class="color_box">' . geodir_help_tip( $desc ) . '
			<input name="' . esc_attr( $id ) . '" id="' . esc_attr( $id ) . '" type="text" value="' . esc_attr( $value ) . '" class="colorpick" /> <div id="colorPickerDiv_' . esc_attr( $id ) . '" class="colorpickdiv"></div>
		</div>';
	}

	/**
	 * The list of supported maps api's.
	 * @return array
	 */
	public static function supported_maps_apis(){
		return array(
			'auto' => __('Automatic (recommended)', 'geodirectory'),
			'google' => __('Google Maps API', 'geodirectory'),
			'osm' => __('OpenStreetMap API', 'geodirectory'),
			'none' => __('Disable Maps', 'geodirectory'),
		);
	}

	/**
	 * The list of supported Google maps api languages.
	 *
	 * @return array
	 */
	public static function supported_map_languages(){
		return array(
			'ar' => __('ARABIC', 'geodirectory'),
			'eu' => __('BASQUE', 'geodirectory'),
			'bg' => __('BULGARIAN', 'geodirectory'),
			'bn' => __('BENGALI', 'geodirectory'),
			'ca' => __('CATALAN', 'geodirectory'),
			'cs' => __('CZECH', 'geodirectory'),
			'da' => __('DANISH', 'geodirectory'),
			'de' => __('GERMAN', 'geodirectory'),
			'el' => __('GREEK', 'geodirectory'),
			'en' => __('ENGLISH', 'geodirectory'),
			'en-AU' => __('ENGLISH (AUSTRALIAN)', 'geodirectory'),
			'en-GB' => __('ENGLISH (GREAT BRITAIN)', 'geodirectory'),
			'es' => __('SPANISH', 'geodirectory'),
			'fa' => __('FARSI', 'geodirectory'),
			'fi' => __('FINNISH', 'geodirectory'),
			'fil' => __('FILIPINO', 'geodirectory'),
			'fr' => __('FRENCH', 'geodirectory'),
			'gl' => __('GALICIAN', 'geodirectory'),
			'gu' => __('GUJARATI', 'geodirectory'),
			'hi' => __('HINDI', 'geodirectory'),
			'hr' => __('CROATIAN', 'geodirectory'),
			'hu' => __('HUNGARIAN', 'geodirectory'),
			'id' => __('INDONESIAN', 'geodirectory'),
			'it' => __('ITALIAN', 'geodirectory'),
			'iw' => __('HEBREW', 'geodirectory'),
			'ja' => __('JAPANESE', 'geodirectory'),
			'kn' => __('KANNADA', 'geodirectory'),
			'ko' => __('KOREAN', 'geodirectory'),
			'lt' => __('LITHUANIAN', 'geodirectory'),
			'lv' => __('LATVIAN', 'geodirectory'),
			'ml' => __('MALAYALAM', 'geodirectory'),
			'mr' => __('MARATHI', 'geodirectory'),
			'nl' => __('DUTCH', 'geodirectory'),
			'no' => __('NORWEGIAN', 'geodirectory'),
			'pl' => __('POLISH', 'geodirectory'),
			'pt' => __('PORTUGUESE', 'geodirectory'),
			'pt-BR' => __('PORTUGUESE (BRAZIL)', 'geodirectory'),
			'pt-PT' => __('PORTUGUESE (PORTUGAL)', 'geodirectory'),
			'ro' => __('ROMANIAN', 'geodirectory'),
			'ru' => __('RUSSIAN', 'geodirectory'),
			'sk' => __('SLOVAK', 'geodirectory'),
			'sl' => __('SLOVENIAN', 'geodirectory'),
			'sr' => __('SERBIAN', 'geodirectory'),
			'sv' => __('SWEDISH', 'geodirectory'),
			'tl' => __('TAGALOG', 'geodirectory'),
			'ta' => __('TAMIL', 'geodirectory'),
			'te' => __('TELUGU', 'geodirectory'),
			'th' => __('THAI', 'geodirectory'),
			'tr' => __('TURKISH', 'geodirectory'),
			'uk' => __('UKRAINIAN', 'geodirectory'),
			'vi' => __('VIETNAMESE', 'geodirectory'),
			'zh-CN' => __('CHINESE (SIMPLIFIED)', 'geodirectory'),
			'zh-TW' => __('CHINESE (TRADITIONAL)', 'geodirectory'),
		);
	}
}

endif;

return new GeoDir_Settings_General();
