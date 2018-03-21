<?php
/**
 * GeoDirectory CPT Settings
 *
 * @author      AyeCode
 * @category    Admin
 * @package     GeoDirectory/Admin
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'GeoDir_Settings_Cpt_Cf', false ) ) :

	/**
	 * GeoDir_Admin_Settings_General.
	 */
	class GeoDir_Settings_Cpt_Cf extends GeoDir_Settings_Page {

		/**
		 * Page.
		 *
		 * @var string
		 */
		private static $page = '';
		
		/**
		 * Post type.
		 *
		 * @var string
		 */
		private static $post_type = '';

		/**
		 * Sub tab.
		 *
		 * @var string
		 */
		private static $sub_tab = '';

		/**
		 * Constructor.
		 */
		public function __construct() {

			self::$page = ! empty( $_REQUEST['page'] ) ? sanitize_title( $_REQUEST['page'] ) : '';
			self::$post_type = ! empty( $_REQUEST['post_type'] ) ? sanitize_title( $_REQUEST['post_type'] ) : 'gd_place';
			self::$sub_tab   = ! empty( $_REQUEST['tab'] ) ? sanitize_title( $_REQUEST['tab'] ) : 'general';


			$this->id    = 'general';
			$this->label = __( 'Custom Fields', 'geodirectory' );

			// init the CF extra fields
			GeoDir_Settings_Cpt_Cf_Extras::instance();

			if ( self::$page == 'gd-cpt-settings' ) {
				add_filter( 'geodir_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
				add_action( 'geodir_settings_' . $this->id, array( $this, 'output' ) );
				add_action( 'geodir_sections_' . $this->id, array( $this, 'output_toggle_advanced' ) );
				//add_action( 'geodir_settings_save_' . $this->id, array( $this, 'save' ) );
				//add_action( 'geodir_sections_' . $this->id, array( $this, 'output_sections' ) );
				//add_action( 'geodir_manage_available_fields', array( $this, 'standard_fields' ) );

				add_action( 'geodir_manage_available_fields', array( $this, 'output_standard_fields' ) );
				add_action( 'geodir_manage_available_fields_predefined', array( $this, 'output_predefined_fields' ) );
				add_action( 'geodir_manage_available_fields_custom', array( $this, 'output_custom_fields' ) );
			}

		}

		/**
		 * Get sections.
		 *
		 * @return array
		 */
		public function get_sections() {

			$sections = array(
				'' => __( 'Custom Fields', 'geodirectory' ),
				//	'location'       => __( 'Custom fields', 'geodirectory' ),
				//	'pages' 	=> __( 'Sorting options', 'geodirectory' ),
				//'dummy_data' 	=> __( 'Dummy Data', 'geodirectory' ),
				//'uninstall' 	=> __( 'Uninstall', 'geodirectory' ),
			);

			return apply_filters( 'geodir_get_sections_' . $this->id, $sections );
		}
		

		/**
		 * Output the settings.
		 */
		public function output() {

			global $hide_save_button;

			$hide_save_button = true;

			$listing_type = self::$post_type;

			$sub_tab = self::$sub_tab;

			include( dirname( __FILE__ ) . '/../views/html-admin-settings-cpt-cf.php' );

		}

		/**
		 * Returns heading for the CPT settings left panel.
		 *
		 * @since 2.0.0
		 * @package GeoDirectory
		 * @return string The page heading.
		 */
		public static function left_panel_title() {
			return sprintf( __( 'Add new %s form field', 'geodirectory' ), get_post_type_singular_label( self::$post_type, false, true ) );
		}


		/**
		 * Returns description for given sub tab - available fields box.
		 *
		 * @since 2.0.0
		 * @package GeoDirectory
		 * @return string The box description.
		 */
		public function left_panel_note() {
			return sprintf( __( 'Click on any box below to add a field of that type to the add %s listing form. You can use a fieldset to group your fields.', 'geodirectory' ), get_post_type_singular_label( self::$post_type, false, true ) );
		}

		/**
		 * Output the admin cpt settings fields left panel content.
		 *
		 * @since 2.0.0
		 * @package GeoDirectory
		 */
		public function left_panel_content() {

			?>
			<h3><?php _e( 'Standard Fields', 'geodirectory' ); ?></h3>

			<div class="inside">

				<div id="gd-form-builder-tab" class="gd-tabs-panel">

					<?php
					/**
					 * Adds the available fields to the custom fields settings page per post type.
					 *
					 * @since 1.0.0
					 *
					 * @param string $sub_tab The current settings tab name.
					 */
					do_action( 'geodir_manage_available_fields', self::$sub_tab ); ?>

					<div style="clear:both"></div>
				</div>

			</div>

			<h3><?php _e( 'Predefined Fields', 'geodirectory' ); ?></h3>
			<div class="inside">

				<div id="gd-form-builder-tab" class="gd-tabs-panel">

					<?php
					/**
					 * Adds the available fields to the custom fields predefined settings page per post type.
					 *
					 * @since 1.6.9
					 *
					 * @param string $sub_tab The current settings tab name.
					 */
					do_action( 'geodir_manage_available_fields_predefined', self::$sub_tab ); ?>

					<div style="clear:both"></div>
				</div>

			</div>

			<h3><?php _e( 'Custom Fields', 'geodirectory' ); ?></h3>
			<div class="inside">

				<div id="gd-form-builder-tab" class="gd-tabs-panel">

					<?php
					/**
					 * Adds the available fields to the custom fields custom added settings page per post type.
					 *
					 * @since 1.6.9
					 *
					 * @param string $sub_tab The current settings tab name.
					 */
					do_action( 'geodir_manage_available_fields_custom', self::$sub_tab ); ?>

					<div style="clear:both"></div>
				</div>

			</div>
			<?php

		}


		/**
		 * Adds admin html for custom fields available fields.
		 *
		 * @since 1.0.0
		 * @since 1.6.9 Added
		 *
		 * @param string $type The custom field type, predefined, custom or blank for default
		 *
		 * @package GeoDirectory
		 */
		public function output_standard_fields() {
			$listing_type = self::$post_type;

			$cfs = self::fields_standard( self::$post_type );
			?>
			<ul class="full gd-cf-tooltip-wrap">
				<li>
					<a id="gd-fieldset"
					   class="gd-draggable-form-items gd-fieldset"
					   href="javascript:void(0);"
					   data-field-custom-type=""
					   data-field-type="fieldset"
					   data-field-type-key="fieldset">

						<i class="fa fa-long-arrow-left " aria-hidden="true"></i>
						<i class="fa fa-long-arrow-right " aria-hidden="true"></i>
						<?php _e( 'Fieldset (section separator)', 'geodirectory' ); ?>

						<span class="gd-help-tip gd-help-tip-no-margin dashicons dashicons-editor-help" title="<?php _e( 'This adds a section separator with a title.', 'geodirectory' );?>"></span>
					</a>
				</li>
			</ul>

			<?php


			if ( ! empty( $cfs ) ) {
				echo '<ul>';
				foreach ( $cfs as $id => $cf ) {
					include( dirname( __FILE__ ) . '/../views/html-admin-settings-cpt-cf-option-item.php' );
				}
				echo '</ul>';
			} else {
				_e( 'There are no custom fields here yet.', 'geodirectory' );
			}

		}

		/**
		 * Adds admin html for custom fields available fields.
		 *
		 * @since 1.0.0
		 * @since 1.6.9 Added
		 *
		 * @param string $type The custom field type, predefined, custom or blank for default
		 *
		 * @package GeoDirectory
		 */
		public function output_predefined_fields() {
			$listing_type = self::$post_type;

			$cfs = self::fields_predefined( $listing_type );

			if ( ! empty( $cfs ) ) {
				echo '<ul>';
				foreach ( $cfs as $id => $cf ) {
					include( dirname( __FILE__ ) . '/../views/html-admin-settings-cpt-cf-option-item.php' );
				}
				echo '</ul>';
			} else {
				_e( 'There are no custom fields here yet.', 'geodirectory' );
			}

		}

		/**
		 * Adds admin html for custom fields available fields.
		 *
		 * @since 1.0.0
		 * @since 1.6.9 Added
		 *
		 * @param string $type The custom field type, predefined, custom or blank for default
		 *
		 * @package GeoDirectory
		 */
		public function output_custom_fields() {
			$listing_type = self::$post_type;

			$cfs = self::fields_custom( $listing_type );

			if ( ! empty( $cfs ) ) {
				echo '<ul>';
				foreach ( $cfs as $id => $cf ) {
					include( dirname( __FILE__ ) . '/../views/html-admin-settings-cpt-cf-option-item.php' );
				}
				echo '</ul>';
			} else {
				_e( 'There are no custom fields here yet.', 'geodirectory' );
			}

		}

		/**
		 * Returns the array of custom fields that can be used.
		 *
		 * @since 1.6.9
		 * @package GeoDirectory
		 */
		public function fields_custom( $post_type = '' ) {

			$custom_fields = array();

			/**
			 * @see `geodir_custom_fields`
			 */
			return apply_filters( 'geodir_custom_fields_custom', $custom_fields, $post_type );
		}


		/**
		 * Returns the array of custom fields that can be used.
		 *
		 * @since 1.6.6
		 * @package GeoDirectory
		 */
		public static function fields_standard( $post_type ) {

			$custom_fields = array(
				'text'        => array(
					'field_type'  => 'text',
					'class'       => 'gd-text',
					'icon'        => 'fa fa-minus',
					'name'        => __( 'Text', 'geodirectory' ),
					'description' => __( 'Add any sort of text field, text or numbers', 'geodirectory' )
				),
				'datepicker'  => array(
					'field_type'  => 'datepicker',
					'class'       => 'gd-datepicker',
					'icon'        => 'fa fa-calendar',
					'name'        => __( 'Date', 'geodirectory' ),
					'description' => __( 'Adds a date picker.', 'geodirectory' )
				),
				'textarea'    => array(
					'field_type'  => 'textarea',
					'class'       => 'gd-textarea',
					'icon'        => 'fa fa-bars',
					'name'        => __( 'Textarea', 'geodirectory' ),
					'description' => __( 'Adds a textarea', 'geodirectory' )
				),
				'time'        => array(
					'field_type'  => 'time',
					'class'       => 'gd-time',
					'icon'        => 'fa fa-clock-o',
					'name'        => __( 'Time', 'geodirectory' ),
					'description' => __( 'Adds a time picker', 'geodirectory' )
				),
				'checkbox'    => array(
					'field_type'  => 'checkbox',
					'class'       => 'gd-checkbox',
					'icon'        => 'fa fa-check-square-o',
					'name'        => __( 'Checkbox', 'geodirectory' ),
					'description' => __( 'Adds a checkbox', 'geodirectory' )
				),
				'phone'       => array(
					'field_type'  => 'phone',
					'class'       => 'gd-phone',
					'icon'        => 'fa fa-phone',
					'name'        => __( 'Phone', 'geodirectory' ),
					'description' => __( 'Adds a phone input', 'geodirectory' )
				),
				'radio'       => array(
					'field_type'  => 'radio',
					'class'       => 'gd-radio',
					'icon'        => 'fa fa-dot-circle-o',
					'name'        => __( 'Radio', 'geodirectory' ),
					'description' => __( 'Adds a radio input', 'geodirectory' )
				),
				'email'       => array(
					'field_type'  => 'email',
					'class'       => 'gd-email',
					'icon'        => 'fa fa-envelope-o',
					'name'        => __( 'Email', 'geodirectory' ),
					'description' => __( 'Adds a email input', 'geodirectory' )
				),
				'select'      => array(
					'field_type'  => 'select',
					'class'       => 'gd-select',
					'icon'        => 'fa fa-caret-square-o-down',
					'name'        => __( 'Select', 'geodirectory' ),
					'description' => __( 'Adds a select input', 'geodirectory' )
				),
				'multiselect' => array(
					'field_type'  => 'multiselect',
					'class'       => 'gd-multiselect',
					'icon'        => 'fa fa-caret-square-o-down',
					'name'        => __( 'Multi Select', 'geodirectory' ),
					'description' => __( 'Adds a multiselect input', 'geodirectory' )
				),
				'url'         => array(
					'field_type'  => 'url',
					'class'       => 'gd-url',
					'icon'        => 'fa fa-link',
					'name'        => __( 'URL', 'geodirectory' ),
					'description' => __( 'Adds a url input', 'geodirectory' )
				),
				'html'        => array(
					'field_type'  => 'html',
					'class'       => 'gd-html',
					'icon'        => 'fa fa-code',
					'name'        => __( 'HTML', 'geodirectory' ),
					'description' => __( 'Adds a html input textarea', 'geodirectory' )
				),
				'file'        => array(
					'field_type'  => 'file',
					'class'       => 'gd-file',
					'icon'        => 'fa fa-file',
					'name'        => __( 'File Upload', 'geodirectory' ),
					'description' => __( 'Adds a file input', 'geodirectory' )
				)
			);

			/**
			 * Filter the custom fields array to be able to add or remove items.
			 *
			 * @since 1.6.6
			 *
			 * @param array $custom_fields {
			 *     The custom fields array to be filtered.
			 *
			 * @type string $field_type The type of field, eg: text, datepicker, textarea, time, checkbox, phone, radio, email, select, multiselect, url, html, file.
			 * @type string $class The class for the field in backend.
			 * @type string $icon Can be font-awesome class name or icon image url.
			 * @type string $name The name of the field.
			 * @type string $description A short description about the field.
			 * @type array $defaults {
			 *                    Optional. Used to set the default value of the field.
			 *
			 * @type string data_type The SQL data type for the field. VARCHAR, TEXT, TIME, TINYINT, INT, FLOAT, DATE
			 * @type int decimal_point limit if using FLOAT data_type
			 * @type string admin_title The admin title for the field.
			 * @type string frontend_title This will be the title for the field on the frontend.
			 * @type string frontend_desc This will be shown below the field on the add listing form.
			 * @type string htmlvar_name This is a unique identifier used in the HTML, it MUST NOT contain spaces or special characters.
			 * @type bool is_active If false the field will not be displayed anywhere.
			 * @type bool for_admin_use If true then only site admin can see and edit this field.
			 * @type string default_value The default value for the input on the add listing page.
			 * @type string show_in The locations to show in. [detail],[moreinfo],[listing],[owntab],[mapbubble]
			 * @type bool is_required If true the field will be required on the add listing page.
			 * @type string option_values The option values for select and multiselect only
			 * @type string validation_pattern HTML5 validation pattern (text input only by default).
			 * @type string validation_msg HTML5 validation message (text input only by default).
			 * @type string required_msg Required warning message.
			 * @type string field_icon Icon url or font awesome class.
			 * @type string css_class Field custom css class for field custom style.
			 * @type bool cat_sort If true the field will appear in the category sort options, if false the field will be hidden, leave blank to show option.
			 * @type bool cat_sort If true the field will appear in the advanced search sort options, if false the field will be hidden, leave blank to show option. (advanced search addon required)
			 *     }
			 * }
			 *
			 * @param string $post_type The post type requested.
			 */
			return apply_filters( 'geodir_custom_fields', $custom_fields, $post_type );
		}


		/**
		 * Returns the array of custom fields that can be used.
		 *
		 * @param string $post_type The post type being added.
		 *
		 * @since 1.6.9
		 * @package GeoDirectory
		 * @see `geodir_custom_field_save` for array details.
		 */
		function fields_predefined( $post_type ) {

			$custom_fields = array();


			// Business Hours
			$custom_fields['business_hours'] = array(
                'field_type'  => 'business_hours',
                'class'       => 'gd-business-hours',
                'icon'        => 'fa fa-clock-o',
                'name'        => __( 'Business Hours', 'geodirectory' ),
                'description' => __( 'Adds a business hours input. This can display when the listing is open/closed/', 'geodirectory' ),
                'defaults'    => array(
	                'data_type'          => 'TEXT',
	                'admin_title'        => 'Business Hours',
	                'frontend_title'     => 'Business Hours',
	                'frontend_desc'      => 'Select your business opening/operating hours.',
	                'htmlvar_name'       => 'business_hours',
	                'is_active'          => true,
	                'for_admin_use'      => false,
	                'default_value'      => '',
	                'show_in'            => '[detail]',
	                'is_required'        => false,
	                'option_values'      => '',
	                'validation_pattern' => '',
	                'validation_msg'     => '',
	                'required_msg'       => '',
	                'field_icon'         => 'fa fa-clock-o',
	                'css_class'          => '',
	                'cat_sort'           => false,
	                'cat_filter'         => false
                )

			);

			// Email
			$custom_fields['contact_email'] = array( // The key value should be unique and not contain any spaces.
				'field_type'  => 'email',
				'class'       => 'gd-email',
				'icon'        => 'fa fa-envelope-o',
				'name'        => __( 'Contact Email', 'geodirectory' ),
				'description' => __( 'Adds a email input. This can be used by other plugins if the htmlvar remains `email`. It will also be used int he contact form.', 'geodirectory' ),
				'defaults'    => array(
					'data_type'          => 'TEXT',
					'admin_title'        => 'Contact Email',
					'frontend_title'     => 'Email',
					'frontend_desc'      => 'You can enter your business or listing website.',
					'htmlvar_name'       => 'email',
					'is_active'          => true,
					'for_admin_use'      => false,
					'default_value'      => '',
					'show_in'            => '[detail]',
					'is_required'        => true,
					'option_values'      => '',
					'validation_pattern' => '',
					'validation_msg'     => '',
					'required_msg'       => '',
					'field_icon'         => 'fa fa-envelope-o',
					'css_class'          => '',
					'cat_sort'           => false,
					'cat_filter'         => false
				)
			);

			// Website
			$custom_fields['website'] = array( // The key value should be unique and not contain any spaces.
				'field_type'  => 'url',
				'class'       => 'gd-website',
				'icon'        => 'fa fa-external-link',
				'name'        => __( 'Website', 'geodirectory' ),
				'description' => __( 'Adds a website input. This can be used by other plugins if the htmlvar remains `website`.', 'geodirectory' ),
				'defaults'    => array(
					'data_type'          => 'TEXT',
					'admin_title'        => 'Website',
					'frontend_title'     => 'Website',
					'frontend_desc'      => 'You can enter your business or listing website.',
					'htmlvar_name'       => 'website',
					'is_active'          => true,
					'for_admin_use'      => false,
					'default_value'      => '',
					'show_in'            => '[detail]',
					'is_required'        => false,
					'option_values'      => '',
					'validation_pattern' => '',
					'validation_msg'     => '',
					'required_msg'       => '',
					'field_icon'         => 'fa fa-external-link',
					'css_class'          => '',
					'cat_sort'           => false,
					'cat_filter'         => false
				)
			);


			// Facebook
			$custom_fields['facebook'] = array( // The key value should be unique and not contain any spaces.
				'field_type'  => 'url',
				'class'       => 'gd-facebook',
				'icon'        => 'fa fa-facebook-official',
				'name'        => __( 'Facebook', 'geodirectory' ),
				'description' => __( 'Adds a facebook url input. This can be used by other plugins if the htmlvar remains `facebook`.', 'geodirectory' ),
				'defaults'    => array(
					'data_type'          => 'TEXT',
					'admin_title'        => 'Facebook',
					'frontend_title'     => 'Facebook',
					'frontend_desc'      => 'You can enter your business or listing facebook url.',
					'htmlvar_name'       => 'facebook',
					'is_active'          => true,
					'for_admin_use'      => false,
					'default_value'      => '',
					'show_in'            => '[detail]',
					'is_required'        => false,
					'option_values'      => '',
					'validation_pattern' => '',
					'validation_msg'     => '',
					'required_msg'       => '',
					'field_icon'         => 'fa fa-external-link',
					'css_class'          => '',
					'cat_sort'           => false,
					'cat_filter'         => false
				)
			);

			// Twitter
			$custom_fields['twitter'] = array( // The key value should be unique and not contain any spaces.
				'field_type'  => 'url',
				'class'       => 'gd-twitter',
				'icon'        => 'fa fa-twitter',
				'name'        => __( 'Twitter', 'geodirectory' ),
				'description' => __( 'Adds a twitter url input. This can be used by other plugins if the htmlvar remains `twitter`.', 'geodirectory' ),
				'defaults'    => array(
					'data_type'          => 'TEXT',
					'admin_title'        => 'Twitter',
					'frontend_title'     => 'Twitter',
					'frontend_desc'      => 'You can enter your business or listing twitter url.',
					'htmlvar_name'       => 'twitter',
					'is_active'          => true,
					'for_admin_use'      => false,
					'default_value'      => '',
					'show_in'            => '[detail]',
					'is_required'        => false,
					'option_values'      => '',
					'validation_pattern' => '',
					'validation_msg'     => '',
					'required_msg'       => '',
					'field_icon'         => 'fa fa-twitter',
					'css_class'          => '',
					'cat_sort'           => false,
					'cat_filter'         => false
				)
			);

			// Instagram
			$custom_fields['instagram'] = array( // The key value should be unique and not contain any spaces.
				'field_type'  => 'url',
				'class'       => 'gd-instagram',
				'icon'        => 'fa fa-instagram',
				'name'        => __( 'Instagram', 'geodirectory' ),
				'description' => __( 'Adds a instagram url input. This can be used by other plugins if the htmlvar remains `instagram`.', 'geodirectory' ),
				'defaults'    => array(
					'data_type'          => 'TEXT',
					'admin_title'        => 'Instagram',
					'frontend_title'     => 'Instagram',
					'frontend_desc'      => 'You can enter your business or listing instagram url.',
					'htmlvar_name'       => 'instagram',
					'is_active'          => true,
					'for_admin_use'      => false,
					'default_value'      => '',
					'show_in'            => '[detail]',
					'is_required'        => false,
					'option_values'      => '',
					'validation_pattern' => '',
					'validation_msg'     => '',
					'required_msg'       => '',
					'field_icon'         => 'fa fa-instagram',
					'css_class'          => '',
					'cat_sort'           => false,
					'cat_filter'         => false
				)
			);

			// Terms &amp; Conditions
			$custom_fields['terms_conditions'] = array( // The key value should be unique and not contain any spaces.
				'field_type'  => 'checkbox',
				'class'       => 'gd-terms-conditions',
				'icon'        => 'fa fa-file-text-o',
				'name'        => __( 'Terms &amp; Conditions', 'geodirectory' ),
				'description' => __( 'Adds a terms and conditions checkbox to your add listing page, the text links to your GD terms and conditions page set in the pages settings.', 'geodirectory' ),
				'defaults'    => array(
					'data_type'          => 'TINYINT',
					'admin_title'        => 'Terms &amp; Conditions',
					'frontend_title'     => 'Terms &amp; Conditions',
					'frontend_desc'      => __('Please accept our terms and conditions','geodirectory'),
					'htmlvar_name'       => 'terms_conditions',
					'is_active'          => true,
					'for_admin_use'      => false,
					'default_value'      => '',
					'show_in'            => '',
					'is_required'        => true,
					'option_values'      => '',
					'validation_pattern' => '',
					'validation_msg'     => '',
					'required_msg'       => __('You MUST accept our terms and conditions to continue.','geodirectory'),
					'field_icon'         => 'fa fa-file-text-o',
					'css_class'          => '',
					'cat_sort'           => false,
					'cat_filter'         => false
				)
			);

			// Video
			$custom_fields['video'] = array( // The key value should be unique and not contain any spaces.
				'field_type'  => 'textarea',
				'class'       => 'gd-video',
				'icon'        => 'fa fa-video-camera',
				'name'        => __( 'Video', 'geodirectory' ),
				'description' => __( 'Adds a video url/code input. This can be used by other plugins if the htmlvar remains `video`.', 'geodirectory' ),
				'defaults'    => array(
					'data_type'          => 'TEXT',
					'admin_title'        => 'Video',
					'frontend_title'     => 'Video',
					'frontend_desc'      => 'Add video url or code here, YouTube, Vimeo etc.',
					'htmlvar_name'       => 'video',
					'is_active'          => true,
					'for_admin_use'      => false,
					'default_value'      => '',
					'show_in'            => '[owntab]',
					'is_required'        => false,
					'option_values'      => '',
					'validation_pattern' => '',
					'validation_msg'     => '',
					'required_msg'       => '',
					'field_icon'         => 'fa fa-instagram',
					'css_class'          => '',
					'cat_sort'           => false,
					'cat_filter'         => false
				)
			);

			// Special offers
			$custom_fields['special_offers'] = array( // The key value should be unique and not contain any spaces.
				'field_type'  => 'textarea',
				'class'       => 'gd-special-offers',
				'icon'        => 'fa fa-gift',
				'name'        => __( 'Special Offers', 'geodirectory' ),
				'description' => __( 'Adds a Special Offers textarea input. This can be used by other plugins if the htmlvar remains `special_offers`.', 'geodirectory' ),
				'defaults'    => array(
					'data_type'          => 'TEXT',
					'admin_title'        => 'Special Offers',
					'frontend_title'     => 'Special Offers',
					'frontend_desc'      => 'Note: List any special offers (optional)',
					'htmlvar_name'       => 'special_offers',
					'is_active'          => true,
					'for_admin_use'      => false,
					'default_value'      => '',
					'show_in'            => '[owntab]',
					'is_required'        => false,
					'option_values'      => '',
					'validation_pattern' => '',
					'validation_msg'     => '',
					'required_msg'       => '',
					'field_icon'         => 'fa fa-gift',
					'css_class'          => '',
					'cat_sort'           => false,
					'cat_filter'         => false
				)
			);



			// price
			$custom_fields['price'] = array( // The key value should be unique and not contain any spaces.
				'field_type'  => 'text',
				'class'       => 'gd-price',
				'icon'        => 'fa fa-usd',
				'name'        => __( 'Price', 'geodirectory' ),
				'description' => __( 'Adds a input for a price field. This will let you filter and sort by price.', 'geodirectory' ),
				'defaults'    => array(
					'data_type'          => 'FLOAT',
					'decimal_point'      => '2',
					'admin_title'        => 'Price',
					'frontend_title'         => 'Price',
					'frontend_desc'         => 'Enter the price in $ (no currency symbol)',
					'htmlvar_name'       => 'price',
					'is_active'          => true,
					'for_admin_use'      => false,
					'default_value'      => '',
					'show_in'            => '[detail],[listing]',
					'is_required'        => false,
					'validation_pattern' => '\d+(\.\d{2})?',
					'validation_msg'     => 'Please enter number and decimal only ie: 100.50',
					'required_msg'       => '',
					'field_icon'         => 'fa fa-usd',
					'css_class'          => '',
					'cat_sort'           => true,
					'cat_filter'         => true,
					'extra_fields'       => array(
						'is_price'                  => 1,
						'thousand_separator'        => 'comma',
						'decimal_separator'         => 'period',
						'decimal_display'           => 'if',
						'currency_symbol'           => '$',
						'currency_symbol_placement' => 'left'
					)
				)
			);

			// property status
			$custom_fields['property_status'] = array( // The key value should be unique and not contain any spaces.
				'field_type'  => 'select',
				'class'       => 'gd-property-status',
				'icon'        => 'fa fa-home',
				'name'        => __( 'Property Status', 'geodirectory' ),
				'description' => __( 'Adds a select input to be able to set the status of a property ie: For Sale, For Rent', 'geodirectory' ),
				'defaults'    => array(
					'data_type'          => 'VARCHAR',
					'admin_title'        => 'Property Status',
					'frontend_title'         => 'Property Status',
					'frontend_desc'         => 'Enter the status of the property.',
					'htmlvar_name'       => 'property_status',
					'is_active'          => true,
					'for_admin_use'      => false,
					'default_value'      => '',
					'show_in'            => '[detail],[listing]',
					'is_required'        => true,
					'option_values'      => __( 'Select Status/,For Sale,For Rent,Sold,Let', 'geodirectory' ),
					'validation_pattern' => '',
					'validation_msg'     => '',
					'required_msg'       => '',
					'field_icon'         => 'fa fa-home',
					'css_class'          => '',
					'cat_sort'           => true,
					'cat_filter'         => true
				)
			);

			// property furnishing
			$custom_fields['property_furnishing'] = array( // The key value should be unique and not contain any spaces.
				'field_type'  => 'select',
				'class'       => 'gd-property-furnishing',
				'icon'        => 'fa fa-home',
				'name'        => __( 'Property Furnishing', 'geodirectory' ),
				'description' => __( 'Adds a select input to be able to set the furnishing status of a property ie: Unfurnished, Furnished', 'geodirectory' ),
				'defaults'    => array(
					'data_type'          => 'VARCHAR',
					'admin_title'        => 'Furnishing',
					'frontend_title'         => 'Furnishing',
					'frontend_desc'         => 'Enter the furnishing status of the property.',
					'htmlvar_name'       => 'property_furnishing',
					'is_active'          => true,
					'for_admin_use'      => false,
					'default_value'      => '',
					'show_in'            => '[detail],[listing]',
					'is_required'        => true,
					'option_values'      => __( 'Select Status/,Unfurnished,Furnished,Partially furnished,Optional', 'geodirectory' ),
					'validation_pattern' => '',
					'validation_msg'     => '',
					'required_msg'       => '',
					'field_icon'         => 'fa fa-th-large',
					'css_class'          => '',
					'cat_sort'           => true,
					'cat_filter'         => true
				)
			);

			// property type
			$custom_fields['property_type'] = array( // The key value should be unique and not contain any spaces.
				'field_type'  => 'select',
				'class'       => 'gd-property-type',
				'icon'        => 'fa fa-home',
				'name'        => __( 'Property Type', 'geodirectory' ),
				'description' => __( 'Adds a select input for the property type ie: Detached house, Apartment', 'geodirectory' ),
				'defaults'    => array(
					'data_type'          => 'VARCHAR',
					'admin_title'        => 'Property Type',
					'frontend_title'         => 'Property Type',
					'frontend_desc'         => 'Select the property type.',
					'htmlvar_name'       => 'property_type',
					'is_active'          => true,
					'for_admin_use'      => false,
					'default_value'      => '',
					'show_in'            => '[detail],[listing]',
					'is_required'        => true,
					'option_values'      => __( 'Select Type/,Detached house,Semi-detached house,Apartment,Bungalow,Semi-detached bungalow,Chalet,Town House,End-terrace house,Terrace house,Cottage', 'geodirectory' ),
					'validation_pattern' => '',
					'validation_msg'     => '',
					'required_msg'       => '',
					'field_icon'         => 'fa fa-home',
					'css_class'          => '',
					'cat_sort'           => true,
					'cat_filter'         => true
				)
			);

			// property bedrooms
			$custom_fields['property_bedrooms'] = array( // The key value should be unique and not contain any spaces.
				'field_type'  => 'select',
				'class'       => 'gd-property-bedrooms',
				'icon'        => 'fa fa-home',
				'name'        => __( 'Property Bedrooms', 'geodirectory' ),
				'description' => __( 'Adds a select input for the number of bedrooms.', 'geodirectory' ),
				'defaults'    => array(
					'data_type'          => 'VARCHAR',
					'admin_title'        => 'Property Bedrooms',
					'frontend_title'         => 'Bedrooms',
					'frontend_desc'         => 'Select the number of bedrooms',
					'htmlvar_name'       => 'property_bedrooms',
					'is_active'          => true,
					'for_admin_use'      => false,
					'default_value'      => '',
					'show_in'            => '[detail],[listing]',
					'is_required'        => true,
					'option_values'      => __( 'Select Bedrooms/,1,2,3,4,5,6,7,8,9,10', 'geodirectory' ),
					'validation_pattern' => '',
					'validation_msg'     => '',
					'required_msg'       => '',
					'field_icon'         => 'fa fa-bed',
					'css_class'          => '',
					'cat_sort'           => true,
					'cat_filter'         => true
				)
			);

			// property bathrooms
			$custom_fields['property_bathrooms'] = array( // The key value should be unique and not contain any spaces.
				'field_type'  => 'select',
				'class'       => 'gd-property-bathrooms',
				'icon'        => 'fa fa-home',
				'name'        => __( 'Property Bathrooms', 'geodirectory' ),
				'description' => __( 'Adds a select input for the number of bathrooms.', 'geodirectory' ),
				'defaults'    => array(
					'data_type'          => 'VARCHAR',
					'admin_title'        => 'Property Bathrooms',
					'frontend_title'         => 'Bathrooms',
					'frontend_desc'         => 'Select the number of bathrooms',
					'htmlvar_name'       => 'property_bathrooms',
					'is_active'          => true,
					'for_admin_use'      => false,
					'default_value'      => '',
					'show_in'            => '[detail],[listing]',
					'is_required'        => true,
					'option_values'      => __( 'Select Bathrooms/,1,2,3,4,5,6,7,8,9,10', 'geodirectory' ),
					'validation_pattern' => '',
					'validation_msg'     => '',
					'required_msg'       => '',
					'field_icon'         => 'fa fa-bold',
					'css_class'          => '',
					'cat_sort'           => true,
					'cat_filter'         => true
				)
			);

			// property area
			$custom_fields['property_area'] = array( // The key value should be unique and not contain any spaces.
				'field_type'  => 'text',
				'class'       => 'gd-area',
				'icon'        => 'fa fa-home',
				'name'        => __( 'Property Area', 'geodirectory' ),
				'description' => __( 'Adds a input for the property area.', 'geodirectory' ),
				'defaults'    => array(
					'data_type'          => 'FLOAT',
					'admin_title'        => 'Property Area',
					'frontend_title'         => 'Area (Sq Ft)',
					'frontend_desc'         => 'Enter the Sq Ft value for the property',
					'htmlvar_name'       => 'property_area',
					'is_active'          => true,
					'for_admin_use'      => false,
					'default_value'      => '',
					'show_in'            => '[detail],[listing]',
					'is_required'        => false,
					'validation_pattern' => '\d+(\.\d{2})?',
					'validation_msg'     => 'Please enter the property area in numbers only: 1500',
					'required_msg'       => '',
					'field_icon'         => 'fa fa-area-chart',
					'css_class'          => '',
					'cat_sort'           => true,
					'cat_filter'         => true
				)
			);

			// property features
			$custom_fields['property_features'] = array( // The key value should be unique and not contain any spaces.
				'field_type'  => 'multiselect',
				'class'       => 'gd-property-features',
				'icon'        => 'fa fa-home',
				'name'        => __( 'Property Features', 'geodirectory' ),
				'description' => __( 'Adds a select input for the property features.', 'geodirectory' ),
				'defaults'    => array(
					'data_type'          => 'VARCHAR',
					'admin_title'        => 'Property Features',
					'frontend_title'         => 'Features',
					'frontend_desc'         => 'Select the property features.',
					'htmlvar_name'       => 'property_features',
					'is_active'          => true,
					'for_admin_use'      => false,
					'default_value'      => '',
					'show_in'            => '[detail],[listing]',
					'is_required'        => true,
					'option_values'      => __( 'Select Features/,Gas Central Heating,Oil Central Heating,Double Glazing,Triple Glazing,Front Garden,Garage,Private driveway,Off Road Parking,Fireplace', 'geodirectory' ),
					'validation_pattern' => '',
					'validation_msg'     => '',
					'required_msg'       => '',
					'field_icon'         => 'fa fa-plus-square',
					'css_class'          => '',
					'cat_sort'           => true,
					'cat_filter'         => true
				)
			);

			// Twitter feed
			$custom_fields['twitter_feed'] = array( // The key value should be unique and not contain any spaces.
				'field_type'  => 'text',
				'class'       => 'gd-twitter',
				'icon'        => 'fa fa-twitter',
				'name'        => __( 'Twitter feed', 'geodirectory' ),
				'description' => __( 'Adds a input for twitter username and outputs feed.', 'geodirectory' ),
				'defaults'    => array(
					'data_type'          => 'VARCHAR',
					'admin_title'        => 'Twitter',
					'frontend_title'         => 'Twitter',
					'frontend_desc'         => 'Enter your Twitter username',
					'htmlvar_name'       => 'twitterusername',
					'is_active'          => true,
					'for_admin_use'      => false,
					'default_value'      => '',
					'show_in'            => '[detail],[owntab]',
					'is_required'        => false,
					'validation_pattern' => '^[A-Za-z0-9_]{1,32}$',
					'validation_msg'     => 'Please enter a valid twitter username.',
					'required_msg'       => '',
					'field_icon'         => 'fa fa-twitter',
					'css_class'          => '',
					'cat_sort'           => false,
					'cat_filter'         => false
				)
			);

			// Get directions link
			$custom_fields['get_directions'] = array( // The key value should be unique and not contain any spaces.
				'field_type'  => 'text',
				'class'       => 'gd-get-directions',
				'icon'        => 'fa fa-location-arrow',
				'name'        => __( 'Get Directions Link', 'geodirectory' ),
				'description' => __( 'Adds a input for twitter username and outputs feed.', 'geodirectory' ),
				'defaults'    => array(
					'data_type'          => 'VARCHAR',
					'admin_title'        => 'Get Directions',
					'frontend_title'         => 'Get Directions',
					'frontend_desc'         => '',
					'htmlvar_name'       => 'get_directions',
					'is_active'          => true,
					'for_admin_use'      => true,
					'default_value'      => 'Get Directions',
					'show_in'            => '[detail],[listing]',
					'is_required'        => false,
					'validation_pattern' => '',
					'validation_msg'     => '',
					'required_msg'       => '',
					'field_icon'         => 'fa fa-location-arrow',
					'css_class'          => '',
					'cat_sort'           => false,
					'cat_filter'         => false
				)
			);


			// JOB TYPE CF

			// job type
			$custom_fields['job_type'] = array( // The key value should be unique and not contain any spaces.
				'field_type'  => 'select',
				'class'       => 'gd-job-type',
				'icon'        => 'fa fa-briefcase',
				'name'        => __( 'Job Type', 'geodirectory' ),
				'description' => __( 'Adds a select input to be able to set the type of a job ie: Full Time, Part Time', 'geodirectory' ),
				'defaults'    => array(
					'data_type'          => 'VARCHAR',
					'admin_title'        => __( 'Job Type', 'geodirectory' ),
					'frontend_title'         => __( 'Job Type', 'geodirectory' ),
					'frontend_desc'         => __( 'Select the type of job.', 'geodirectory' ),
					'htmlvar_name'       => 'job_type',
					'is_active'          => true,
					'for_admin_use'      => false,
					'default_value'      => '',
					'show_in'            => '[detail],[listing]',
					'is_required'        => true,
					'option_values'      => __( 'Select Type/,Freelance,Full Time,Internship,Part Time,Temporary,Other', 'geodirectory' ),
					'validation_pattern' => '',
					'validation_msg'     => '',
					'required_msg'       => '',
					'field_icon'         => 'fa fa-briefcase',
					'css_class'          => '',
					'cat_sort'           => true,
					'cat_filter'         => true
				)
			);

			// job sector
			$custom_fields['job_sector'] = array( // The key value should be unique and not contain any spaces.
				'field_type'  => 'select',
				'class'       => 'gd-job-type',
				'icon'        => 'fa fa-briefcase',
				'name'        => __( 'Job Sector', 'geodirectory' ),
				'description' => __( 'Adds a select input to be able to set the type of a job Sector ie: Private Sector,Public Sector', 'geodirectory' ),
				'defaults'    => array(
					'data_type'          => 'VARCHAR',
					'admin_title'        => __( 'Job Sector', 'geodirectory' ),
					'frontend_title'         => __( 'Job Sector', 'geodirectory' ),
					'frontend_desc'         => __( 'Select the job sector.', 'geodirectory' ),
					'htmlvar_name'       => 'job_sector',
					'is_active'          => true,
					'for_admin_use'      => false,
					'default_value'      => '',
					'show_in'            => '[detail]',
					'is_required'        => true,
					'option_values'      => __( 'Select Sector/,Private Sector,Public Sector,Agencies', 'geodirectory' ),
					'validation_pattern' => '',
					'validation_msg'     => '',
					'required_msg'       => '',
					'field_icon'         => 'fa fa-briefcase',
					'css_class'          => '',
					'cat_sort'           => true,
					'cat_filter'         => true
				)
			);

			// Post Badge
			$custom_fields['post_badge'] = array(
                'field_type'  => 'post_badge',
                'class'       => 'gd-post-badge',
                'icon'        => 'fa fa-certificate',
                'name'        => __( 'Post Badge', 'geodirectory' ),
                'description' => __( 'Adds a badge like Sale, New, Featured etc on the listing image.', 'geodirectory' ),
                'defaults'    => array(
	                'data_type'          => 'TEXT',
	                'admin_title'        => 'Post Badge',
	                'frontend_title'     => 'Post Badge',
	                'frontend_desc'      => '',
	                'htmlvar_name'       => 'post_badge',
	                'is_active'          => true,
	                'for_admin_use'      => false,
	                'default_value'      => '',
	                'show_in'            => '[listing]',
	                'is_required'        => false,
	                'option_values'      => '',
	                'validation_pattern' => '',
	                'validation_msg'     => '',
	                'required_msg'       => '',
	                'field_icon'         => 'fa fa-certificate',
	                'css_class'          => '',
	                'cat_sort'           => false,
	                'cat_filter'         => false
                )

			);


			/**
			 * @see `geodir_custom_fields`
			 */
			return apply_filters( 'geodir_custom_fields_predefined', $custom_fields, $post_type );
		}


		/**
		 * Returns heading for the CPT settings left panel.
		 *
		 * @since 2.0.0
		 * @package GeoDirectory
		 * @return string The page heading.
		 */
		public static function right_panel_title() {
			return sprintf( __( 'List of fields that will appear on add new %s listing form', 'geodirectory' ), get_post_type_singular_label( self::$post_type, false, true ) );
		}


		/**
		 * Returns description for given sub tab - available fields box.
		 *
		 * @since 2.0.0
		 * @package GeoDirectory
		 * @return string The box description.
		 */
		public function right_panel_note() {
			return sprintf( __( 'Click to expand and view field related settings. You may drag and drop to arrange fields order on add %s listing form too.', 'geodirectory' ), get_post_type_singular_label( self::$post_type, false, true ) );
		}

		/**
		 * Output the admin cpt settings fields left panel content.
		 *
		 * @since 2.0.0
		 * @package GeoDirectory
		 */
		public function right_panel_content() {
			global $wpdb;

			$listing_type = self::$post_type;
			$post_type = self::$post_type;
			$sub_tab =  self::$sub_tab;
			?>
			<div class="inside">

				<div id="gd-form-builder-tab" class="gd-tabs-panel">
					<div class="field_row_main">
						<ul class="core">
							<?php
							global $wpdb;
							$fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . GEODIR_CUSTOM_FIELDS_TABLE . " WHERE post_type = %s ORDER BY sort_order ASC", array($listing_type)));

							if (!empty($fields)) {


								$cf_arr = self::get_all_fields($post_type);


								foreach ($fields as $field) {

									$cf = (isset($cf_arr[$field->field_type_key])) ? $cf_arr[$field->field_type_key] : ''; // the field type


									self::output_custom_field_setting_item('',$field,$cf);

									//include( dirname( __FILE__ ) . '/../views/html-admin-settings-cpt-cf-setting-item.php' );
								}
							}
							?></ul>

					</div>
					<div style="clear:both"></div>
				</div>

			</div>
			<?php
		}

		public function get_all_fields($post_type){
			$cf_arr1 = self::fields_standard($post_type);
			$cf_arr2 = self::fields_predefined($post_type);
			$cf_arr3 = self::fields_custom($post_type);

			return $cf_arr1 + $cf_arr2 + $cf_arr3; // this way defaults can't be overwritten
		}


		/**
		 * Adds admin html for custom fields.
		 *
		 * @since 1.0.0
		 * @package GeoDirectory
		 * @global object $wpdb WordPress Database object.
		 * @param string $field_type The form field type.
		 * @param object|int $result_str The custom field results object or row id.
		 * @param string $field_ins_upd When set to "submit" displays form.
		 * @param string $field_type_key The key of the custom field.
		 */
		function output_custom_field_setting_item($field_id = '',$field = '',$cf = array())
		{

			// if field not provided get it
			if (!is_object($field) && $field_id) {
				global $wpdb;
				$field = $wpdb->get_row($wpdb->prepare("select * from " . GEODIR_CUSTOM_FIELDS_TABLE . " where id= %d", array($field_id)));
			}

			// if field template not provided get it
			if(empty($cf)){
				$cf_arr  = self::get_all_fields($field->post_type);
				$cf = (isset($cf_arr[$field->field_type_key])) ? $cf_arr[$field->field_type_key] : ''; // the field type
			}

			//print_r($cf);

			// set defaults
			if(isset($cf['defaults'])){
				foreach($cf['defaults'] as $key => $val ){
					if(!isset($field->{$key})){
						$field->{$key} = $val;
					}
				}
			}

			if(!isset( $field->is_default ))$field->is_default = 0;

			// Setup some variables


			// Strip slashes but not from json extra_fields
			if ( isset( $field->extra_fields ) ) {
				$extra_fields = $field->extra_fields;
			}
			$field = stripslashes_deep( $field ); // strip slashes from labels
			if ( isset( $field->extra_fields ) ) {
				$field->extra_fields = $extra_fields;
			}

			//print_r($field);
			// Set nonce
			$nonce = wp_create_nonce( 'custom_fields_' . $field->id );

			// Set if this is a default field
			$default = isset( $field->is_admin ) ? $field->is_admin : '';

			// Remove Send Enquiry | Send To Friend from listings page
			$display_on_listing = true;
			$htmlvar_name = isset( $field->htmlvar_name ) && $field->htmlvar_name != '' ? $field->htmlvar_name : '';
			if ( $htmlvar_name == 'geodir_email' ) {
				$display_on_listing  = false;
			}

			// Hide the field from custom fields (i.e. address field from location less CPT)
			if ( has_filter( "geodir_cfa_skip_item_output_{$field->field_type}" ) ) {
				if ( apply_filters( "geodir_cfa_skip_item_output_{$field->field_type}", false, $field_id, $field, $cf ) === true ) {
					return;
				}
			}
			

			// @todo do we need this?
			$field_display = $field->field_type == 'address' && $field->htmlvar_name == 'post' ? 'style="display:none"' : '';

			// set a unique id for radio fields
			$radio_id = ( isset( $field->htmlvar_name ) && $field->htmlvar_name ) ? $field->htmlvar_name : rand( 5, 500 );

			// field icon
			$icon = isset( $cf['icon'] ) ? $cf['icon'] : ( isset( $field->field_icon ) ? $field->field_icon : '' );

			// Set the field icon
			if ( strpos( $icon, 'fa fa-' ) !== false ) {
				$field_icon = '<i class="' . $icon . '" aria-hidden="true"></i>';
			} elseif ( $icon ) {
				$field_icon = '<b style="background-image: url("' . $icon . '")"></b>';
			} else {
				$field_icon = '<i class="fa fa-cog" aria-hidden="true"></i>';
			}

			// if field type name is missing set it from main settings
			if ( isset( $cf['name'] ) && $cf['name'] ) {
				$field->field_type_name = $cf['name'];
			} else {
				$field->field_type_name = $field->field_type;
			}

			//print_r($field);

			/**
			 * Contains custom field html.
			 *
			 * @since 2.0.0
			 */
			include( dirname( __FILE__ ) . '/../views/html-admin-settings-cpt-cf-setting-item.php' );

		}

		/**
		 * Sanatize the custom field
		 *
		 * @param array/object $input {
		 *    Attributes of the request field array.
		 *
		 *    @type string $action Ajax Action name. Default "geodir_ajax_action".
		 *    @type string $manage_field_type Field type Default "custom_fields".
		 *    @type string $create_field Create field Default "true".
		 *    @type string $field_ins_upd Field ins upd Default "submit".
		 *    @type string $_wpnonce WP nonce value.
		 *    @type string $listing_type Listing type Example "gd_place".
		 *    @type string $field_type Field type Example "radio".
		 *    @type string $field_id Field id Example "12".
		 *    @type string $data_type Data type Example "VARCHAR".
		 *    @type string $is_active Either "1" or "0". If "0" is used then the field will not be displayed anywhere.
		 *    @type array $show_on_pkg Package list to display this field.
		 *    @type string $admin_title Personal comment, it would not be displayed anywhere except in custom field settings.
		 *    @type string $frontend_title Section title which you wish to display in frontend.
		 *    @type string $frontend_desc Section description which will appear in frontend.
		 *    @type string $htmlvar_name Html variable name. This should be a unique name.
		 *    @type string $clabels Section Title which will appear in backend.
		 *    @type string $default_value The default value (for "link" this will be used as the link text).
		 *    @type string $sort_order The display order of this field in backend. e.g. 5.
		 *    @type string $is_default Either "1" or "0". If "0" is used then the field will be displayed as main form field or additional field.
		 *    @type string $for_admin_use Either "1" or "0". If "0" is used then only site admin can edit this field.
		 *    @type string $is_required Use "1" to set field as required.
		 *    @type string $required_msg Enter text for error message if field required and have not full fill requirement.
		 *    @type string $show_in What locations to show the custom field in.
		 *    @type string $show_as_tab Want to display this as a tab on detail page? If "1" then "Show on detail page?" must be Yes.
		 *    @type string $option_values Option Values should be separated by comma.
		 *    @type string $field_icon Upload icon using media and enter its url path, or enter font awesome class.
		 *    @type string $css_class Enter custom css class for field custom style.
		 *    @type array $extra_fields An array of extra fields to store.
		 *
		 * }
		 */
		private static function sanatize_custom_field($input){

			// if object convert to array
			if(is_object($input)){
				$input = json_decode(json_encode($input), true);
			}


			$field = new stdClass();

			// sanatize
			$field->field_id = isset( $input['field_id'] ) ? absint( $input['field_id'] ) : '';
			$field->post_type = isset( $input['post_type'] ) ? sanitize_text_field( $input['post_type'] ) : null;
			$field->admin_title = isset( $input['admin_title'] ) ? sanitize_text_field( $input['admin_title'] ) : null;
			$field->frontend_title = isset( $input['frontend_title'] ) ? sanitize_text_field( $input['frontend_title'] ) : null;
			$field->field_type = isset( $input['field_type'] ) ? sanitize_text_field( $input['field_type'] ) : null;
			$field->field_type_key = isset( $input['field_type_key'] ) ? sanitize_text_field( $input['field_type_key'] ) : $field->field_type;
			$field->htmlvar_name = isset( $input['htmlvar_name'] ) ? str_replace(array('-',' ','"',"'"), array('_','','',''), sanitize_title_with_dashes( $input['htmlvar_name'] ) ) : null;
			$field->frontend_desc = isset( $input['frontend_desc'] ) ? sanitize_text_field( $input['frontend_desc'] ) : '';
			$field->clabels = isset( $input['clabels'] ) ? sanitize_text_field( $input['clabels'] ) : null;
			$field->default_value = isset( $input['default_value'] ) ? sanitize_text_field( $input['default_value'] ) : '';
			$field->sort_order = isset( $input['sort_order'] ) ? absint( $input['sort_order'] ) : self::default_sort_order();
			$field->is_active = isset( $input['is_active'] ) ? absint( $input['is_active'] ) : 0;
			$field->is_default  = isset( $input['is_default'] ) ? absint( $input['is_default'] ) : 0;
			$field->is_required = isset( $input['is_required'] ) ? absint( $input['is_required'] ) : 0;
			$field->required_msg = isset( $input['required_msg'] ) ? sanitize_text_field( $input['required_msg'] ) : '';
			$field->css_class = isset( $input['css_class'] ) ? sanitize_text_field( $input['css_class'] ) : '';
			$field->field_icon = isset( $input['field_icon'] ) ? sanitize_text_field( $input['field_icon'] ) : '';
			$field->show_in = isset( $input['show_in'] ) ? self::sanatize_show_in( $input['show_in'] ) : '';
			$field->option_values = isset( $input['option_values'] ) ? self::sanitize_option_values( $input['option_values'] ) : '';
			$field->packages = isset( $input['show_on_pkg'] ) ? self::sanatize_show_on_pkg( $input['show_on_pkg'] ) : array(); //@todo maybe have sanatize function
			$field->cat_sort = isset( $input['cat_sort'] ) ? absint( $input['cat_sort'] ) : 0;
			$field->cat_filter = isset( $input['cat_filter'] ) ? absint( $input['cat_filter'] ) : 0;
			$field->data_type = isset( $input['data_type'] ) ? sanitize_text_field( $input['data_type'] ) : '';
			$field->extra_fields = isset( $input['extra'] ) ? self::sanatize_extra( $input['extra'] ) : '';//@todo
			$field->decimal_point = isset( $input['decimal_point'] ) ? absint( $input['decimal_point'] ) : 0;
			$field->validation_pattern = isset( $input['validation_pattern'] ) ? sanitize_text_field( $input['validation_pattern'] ) : '';//@todo
			$field->validation_msg = isset( $input['validation_msg'] ) ? sanitize_text_field( $input['validation_msg'] ) : '';
			$field->for_admin_use = isset( $input['for_admin_use'] ) ? absint( $input['for_admin_use'] ) : 0;

			// Set some default after sanitation
			$field->data_type = self::sanitize_data_type($field);
			if(!$field->admin_title){$field->admin_title = $field->frontend_title;}
			if(!$field->htmlvar_name){$field->htmlvar_name =str_replace(array('-',' ','"',"'"), array('_','','',''), sanitize_title_with_dashes( $input['frontend_title'] ) );} // we use original input so the special chars are no converted already

			return $field;

		}

		/**
		 * Get the sort order if not set.
		 *
		 * @return int
		 */
		public static function default_sort_order(){
			global $wpdb;
			$last_order = $wpdb->get_var("SELECT MAX(sort_order) as last_order FROM " . GEODIR_CUSTOM_FIELDS_TABLE);

			return (int)$last_order + 1;
		}

		private static function sanatize_extra( $value ){
			if( is_array($value) ){
				if(empty($value)){$value = '';}else{
					$value = maybe_serialize(array_map( 'sanitize_text_field', $value ));
				}
			}else{
				$value = sanitize_text_field($value );
			}

			return $value;
		}

		private static function sanatize_show_on_pkg( $value ){
			if(is_array($value)  ){
				if(empty($value)){$value = '';}else{
					$value = implode(",",array_map( 'absint', $value  ));
				}
			}

			return $value;
		}

		private static function sanatize_show_in( $value ){
			if(is_array($value)){
				if(empty($value)){$value = '';}else {
					$value = implode( ",", array_map( 'sanitize_text_field', $value ) );
				}
			}

			return $value;
		}

		/**
		 * Sanatize data type.
		 *
		 * Sanatize option values.
		 * @param $value
		 *
		 * @return mixed
		 */
		private static function sanitize_data_type( $field ){

			$value = 'VARCHAR';

			if($field->data_type == ''){

				switch ($field->field_type){

					case 'checkbox':
						$value = 'TINYINT';
						break;
					case 'textarea':
					case 'html':
					case 'url':
					case 'file':
					$value = 'TEXT';
						break;
					default:
						$value = 'VARCHAR';
				}

			}else{
				// Strip X if first character, this is added as some servers will flag security rules if a data type is posted via form.
				$value = ltrim($field->data_type, 'X');
			}

			return sanitize_text_field( $value);
		}

		/**
		 * Sanatize option values.
		 *
		 * @param $value
		 *
		 * @return mixed
		 */
		private static function sanitize_option_values( $value ){

			return sanitize_text_field( $value);
		}

		/**
		 * Check if the field already exists.
		 *
		 * @param $field
		 *
		 * @return WP_Error
		 */
		public static function field_exists($field){
			global $wpdb;

			if( isset($field->htmlvar_name) && isset($field->post_type)){
				$result = $wpdb->get_var(
					$wpdb->prepare(
						"select htmlvar_name from " . GEODIR_CUSTOM_FIELDS_TABLE . " where htmlvar_name = %s and post_type = %s ",
						array($field->htmlvar_name, $field->post_type )
					)
				);


			}else{
				$result = new WP_Error( 'invalid', __( "Invalid field.", "geodirectory" ) );
			}

			return $result;

		}

		private static function save_sort_item($field){
			global $wpdb;

			$exists = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT htmlvar_name from " . GEODIR_CUSTOM_SORT_FIELDS_TABLE . " where post_type = %s and htmlvar_name = %s",
					array($field->post_type, $field->htmlvar_name)
				)
			);

			if($exists){

				if (!$field->cat_sort) {
					$wpdb->query(
						$wpdb->prepare(
							"delete from " . GEODIR_CUSTOM_SORT_FIELDS_TABLE . " where post_type = %s and htmlvar_name = %s",
							array( $field->post_type, $field->htmlvar_name )
						)
					);
				}else{
					$wpdb->query(
						$wpdb->prepare(
							"update " . GEODIR_CUSTOM_SORT_FIELDS_TABLE . " set 
					 	frontend_title=%s
					where post_type = %s and htmlvar_name = %s",
							array($field->frontend_title, $field->post_type, $field->htmlvar_name)
						)
					);
				}

			}


		}

		/**
		 * Save the custom field.
		 *
		 * @param array $field
		 * @param bool $default
		 *
		 * @return int|string
		 */
		public static function delete_custom_field($field_id){
			global $wpdb,$plugin_prefix;

			$field_id = absint($field_id);

			if ($field = $wpdb->get_row($wpdb->prepare("select htmlvar_name,post_type,field_type from " . GEODIR_CUSTOM_FIELDS_TABLE . " where id= %d", array($field_id)))) {
				$wpdb->query($wpdb->prepare("delete from " . GEODIR_CUSTOM_FIELDS_TABLE . " where id= %d ", array($field_id)));

				$post_type = $field->post_type;
				$htmlvar_name = $field->htmlvar_name;

				if ($post_type != '' && $htmlvar_name != '') {
					$wpdb->query($wpdb->prepare("DELETE FROM " . GEODIR_CUSTOM_SORT_FIELDS_TABLE . " WHERE htmlvar_name=%s AND post_type=%s LIMIT 1", array($htmlvar_name, $post_type)));
				}

				/**
				 * Called after a custom field is deleted.
				 *
				 * @since 1.0.0
				 * @param string $field_id The fields ID.
				 * @param string $field->htmlvar_name The html variable name for the field.
				 * @param string $post_type The post type the field belongs to.
				 */
				do_action('geodir_after_custom_field_deleted', $field_id, $field->htmlvar_name, $post_type);

				if ($field->field_type == 'address') {
					$wpdb->query("ALTER TABLE " . $plugin_prefix . $post_type . "_detail DROP `" . $field->htmlvar_name . "_address`");
					$wpdb->query("ALTER TABLE " . $plugin_prefix . $post_type . "_detail DROP `" . $field->htmlvar_name . "_city`");
					$wpdb->query("ALTER TABLE " . $plugin_prefix . $post_type . "_detail DROP `" . $field->htmlvar_name . "_region`");
					$wpdb->query("ALTER TABLE " . $plugin_prefix . $post_type . "_detail DROP `" . $field->htmlvar_name . "_country`");
					$wpdb->query("ALTER TABLE " . $plugin_prefix . $post_type . "_detail DROP `" . $field->htmlvar_name . "_zip`");
					$wpdb->query("ALTER TABLE " . $plugin_prefix . $post_type . "_detail DROP `" . $field->htmlvar_name . "_latitude`");
					$wpdb->query("ALTER TABLE " . $plugin_prefix . $post_type . "_detail DROP `" . $field->htmlvar_name . "_longitude`");
					$wpdb->query("ALTER TABLE " . $plugin_prefix . $post_type . "_detail DROP `" . $field->htmlvar_name . "_mapview`");
					$wpdb->query("ALTER TABLE " . $plugin_prefix . $post_type . "_detail DROP `" . $field->htmlvar_name . "_mapzoom`");
				} else {
					if ($field->field_type != 'fieldset') {
						$wpdb->query("ALTER TABLE " . $plugin_prefix . $post_type . "_detail DROP `" . $field->htmlvar_name . "`");
					}
				}

				return $field_id;
			}else{
				return new WP_Error( 'invalid', __( "Invalid field.", "geodirectory" ) );
			}
		}

		/**
		 * Set custom field order
		 *
		 * @since 1.0.0
		 * @package GeoDirectory
		 * @global object $wpdb WordPress Database object.
		 * @param array $field_ids List of field ids.
		 * @return array|bool Returns field ids when success, else returns false.
		 */
		public function set_field_orders($field_ids = array()){
			global $wpdb;

			$count = 0;
			if (!empty($field_ids)) {
				$post_meta_info = false;
				foreach ( $field_ids as $id ) {
					$post_meta_info = $wpdb->update(
						GEODIR_CUSTOM_FIELDS_TABLE,
						array('sort_order' => $count),
						array('id' => absint($id)),
						array('%d')
					);
					$count ++;
				}
				if($post_meta_info !== false){
					return true;
				}else{
					return new WP_Error( 'failed', __( "Failed to sort custom fields.", "geodirectory" ) );
				}
			}else{
				return new WP_Error( 'failed', __( "Failed to sort custom fields.", "geodirectory" ) );
			}
		}

		/**
		 * An array of fields that dont need to add a new column
		 * 
		 * @return array
		 */
		public static function get_default_field_htmlvars(){
			$htmlvars = array();
			$custom_fields = GeoDir_Admin_Dummy_Data::default_custom_fields();
			foreach($custom_fields as $custom_field){
				$htmlvars[] = $custom_field['htmlvar_name'];
			}
			return $htmlvars;
		}

		/**
		 * Save the custom field.
		 * 
		 * @param array $field
		 *
		 * @return int|string
		 */
		public static function save_custom_field($field = array()){
			global $wpdb, $plugin_prefix;

			$field = self::sanatize_custom_field($field);

			//print_r($field);//exit;
			
			// Check field exists.
			$exists = self::field_exists($field);

			///if($exists){echo '###exizts';}else{echo '###nonexists';}

			if(is_wp_error( $exists ) ){
				return new WP_Error( 'failed', $exists->get_error_message() );
			}elseif( $exists && !$field->field_id ){
				return new WP_Error( 'failed', __( "Field HTML variable name MUST be unique, duplicate field detected, please fix and re-save.", "geodirectory" ) );
			}

			$column_attr = $field->data_type;
			switch ($field->field_type){
//				case 'address':
//					echo $field->field_type; //@todo we need to do stuff here
//					break;
				case 'checkbox':
					$column_attr .= "( 1 ) NOT NULL ";
					if ((int)$field->default_value === 1) {
						$column_attr .= " DEFAULT '1'";
					}
					break;
				case 'multiselect':
				case 'select':
					$op_size = '500';

					// only make the field as big as it needs to be.
					if (isset($field->option_values) && $field->option_values && $field->field_type == 'select') {
						$option_values_arr = explode(',', $field->option_values);

						if (is_array($option_values_arr)) {
							$op_max = 0;

							foreach ($option_values_arr as $op_val) {
								if (strlen($op_val) && strlen($op_val) > $op_max) {
									$op_max = strlen($op_val);
								}
							}

							if ($op_max) {
								$op_size = $op_max;
							}
						}
					} elseif (isset($field->option_values) && $field->option_values && $field->field_type == 'multiselect') {
						if (strlen($field->option_values)) {
							$op_size =  strlen($field->option_values);
						}

						if (isset($request_field['multi_display_type'])) {
							$field->extra_fields = $request_field['multi_display_type'];
						}
					}

					$column_attr .= "( $op_size ) NULL ";
					if ($field->default_value != '') {
						$column_attr.= $wpdb->prepare(" DEFAULT %s ",$field->default_value);
					}
					break;
				case 'textarea':
				case 'html':
				case 'url':
				case 'file':
					$column_attr .= " NULL ";
					break;
				case 'categories':

					break;
				case 'text':
					if(isset($field->data_type) && ($field->data_type == "FLOAT" || $field->data_type == "DECIMAL")){
						$decimal_place = isset($field->decimal_point) && $field->decimal_point ? $field->decimal_point : 2;
						$column_attr = "DECIMAL(15,$decimal_place)";
					}elseif(isset($field->data_type) && $field->data_type == "INT" ){
						$column_attr = "INT";
					}else{
						$column_attr .= "( 254 ) NULL ";
					}
					//print_r($extra_fields);print_r($field);exit;
				break;
				case 'fieldset':
					// Nothing happenes for fieldset
					break;
				default:
					$column_attr .= "( 254 ) NULL ";
			}



			// Serialise the extra info for the DB if needed
//			$extra_field_query = '';
//			if (!empty($extra_fields)) {
//				$extra_field_query = serialize($extra_fields);
//			}


			$db_data = array(
				'post_type' => $field->post_type,
				'admin_title' => $field->admin_title,
				'frontend_title' => $field->frontend_title,
				'field_type' => $field->field_type,
				'field_type_key' => $field->field_type_key,
				'htmlvar_name' => $field->htmlvar_name,
				'frontend_desc' => $field->frontend_desc,
				'clabels' => $field->clabels,
				'default_value' => $field->default_value,
				'sort_order' => $field->sort_order,
				'is_active' => $field->is_active,
				'is_default' => $field->is_default,
				'is_required' => $field->is_required,
				'required_msg' => $field->required_msg,
				'css_class' => $field->css_class,
				'field_icon' => $field->field_icon,
				'show_in' => $field->show_in,
				'option_values' => $field->option_values,
				'packages' => $field->packages,
				'cat_sort' => $field->cat_sort,
				'cat_filter' => $field->cat_filter,
				'data_type' => $field->data_type,
				'extra_fields' => $field->extra_fields,
				'decimal_point' => $field->decimal_point,
				'validation_pattern' => $field->validation_pattern,
				'validation_msg' => $field->validation_msg,
				'for_admin_use' => $field->for_admin_use
			);

			$db_format = array(
				'%s', // post_type
				'%s', // admin_title
				'%s', // frontend_title
				'%s', // field_type
				'%s', // field_type_key
				'%s', // htmlvar_name
				'%s', // frontend_desc
				'%s', // clabels
				'%s', // default_value
				'%d', // sort_order
				'%d', // is_active
				'%d', // is_default
				'%d', // is_required
				'%s', // required_msg
				'%s', // css_class
				'%s', // field_icon
				'%s', // show_in
				'%s', // option_values
				'%s', // packages
				'%d', // cat_sort
				'%d', // cat_filter
				'%s', // data_type
				'%s', // extra_fields
				'%d', // decimal_point
				'%s', // validation_pattern
				'%s', // validation_msg
				'%d', // for_admin_use
			);

			if($exists){

				// Update the field settings.
				$result = $wpdb->update(
					GEODIR_CUSTOM_FIELDS_TABLE,
					$db_data,
					array('id' => $field->field_id),
					$db_format
				);
				
				if($result === false){
					return new WP_Error( 'failed', __( "Field update failed.x", "geodirectory" ) );
				}

				// @todo, should we ALTER the field type here to see if we can improve it, ie VARCHAR(123)

			}else{
				// Insert the field settings.
				$result = $wpdb->insert(
					GEODIR_CUSTOM_FIELDS_TABLE,
					$db_data,
					$db_format
				);

				if($result === false){
					return new WP_Error( 'failed', __( "Field create failed.", "geodirectory" ) );
				}else{
					$field->field_id = $wpdb->insert_id;
				}

				// check if its a default field that does not need a column added
				$default_fields = self::get_default_field_htmlvars();
				if(!in_array($field->htmlvar_name,$default_fields)){

					// Add the new column to the details table.
					$add_details_column = geodir_add_column_if_not_exist($plugin_prefix . $field->post_type . '_detail', $field->htmlvar_name, $column_attr);
					if ($add_details_column === false) {
						return new WP_Error( 'failed', __('Column creation failed, you may have too many columns or the default value does not match with field data type.', 'geodirectory'));
					}

				}



			}


			// update or delete the sort order field.
			self::save_sort_item($field);


			/**
			 * Called after all custom fields are saved for a post.
			 *
			 * @since 1.0.0
			 * @param int $lastid The post ID.
			 */
			do_action('geodir_after_custom_fields_updated', $field->field_id);


			return $field->field_id;

		}


		public static function get_cpt_custom_fields($post_type = 'gd_place'){
			global $wpdb;
			return $wpdb->get_results($wpdb->prepare("SELECT * FROM ".GEODIR_CUSTOM_FIELDS_TABLE." WHERE  post_type = %s",$post_type));
		}

		/**
		 * @param object $field
		 * @param string $field_type
		 *
		 * @return mixed|void
		 */
		public static function show_in_locations($field = '', $field_type=''){

			/*
			 * We wrap the key values in [] so we can search the DB easier with a LIKE query.
			 */
			$show_in_locations = array(
				"[detail]"    => __( "Details page sidebar", 'geodirectory' ),
				"[moreinfo]"  => __( "More info tab", 'geodirectory' ),
				"[listing]"   => __( "Listings page", 'geodirectory' ),
				"[owntab]"    => __( "Details page own tab", 'geodirectory' ),
				"[mapbubble]" => __( "Map bubble", 'geodirectory' ),
			);

			/**
			 * Filter the locations array for where to display custom fields.
			 *
			 * @since 1.6.6
			 *
			 * @param array $show_in_locations The array of locations and descriptions.
			 * @param object $field The field being displayed info.
			 * @param string $field The type of field.
			 */
			return apply_filters( 'geodir_show_in_locations', $show_in_locations, $field, $field_type );
		}

	}

endif;

return new GeoDir_Settings_Cpt_Cf();
