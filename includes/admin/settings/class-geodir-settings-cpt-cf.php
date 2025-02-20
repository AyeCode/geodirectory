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
			self::$post_type = ( ! empty( $_REQUEST['post_type'] ) && is_scalar( $_REQUEST['post_type'] ) ) ? sanitize_title( $_REQUEST['post_type'] ) : 'gd_place';
			self::$sub_tab   = ! empty( $_REQUEST['tab'] ) ? sanitize_title( $_REQUEST['tab'] ) : 'general';


			$this->id    = 'general';
			$this->label = __( 'Custom Fields', 'geodirectory' );

			// Init hooks
			$this->init_hooks();
		}

		/**
		 * Init custom fields hooks.
		 *
		 * @since 2.1.1.4
		 *
		 * @return mixed
		 */
		public function init_hooks() {
			global $geodir_cpt_cf_init;

			// Prevent executing hooks twice.
			if ( $geodir_cpt_cf_init ) {
				return;
			}

			$geodir_cpt_cf_init = true;

			// Init the CF extra fields
			GeoDir_Settings_Cpt_Cf_Extras::instance();

			if ( self::$page == self::$post_type.'-settings' ) {
				add_filter( 'geodir_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
				add_action( 'geodir_settings_' . $this->id, array( $this, 'output' ) );
				//add_action( 'geodir_sections_' . $this->id, array( $this, 'output_toggle_advanced' ) );
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
			return sprintf( __( 'Fields', 'geodirectory' ), geodir_get_post_type_singular_label( self::$post_type, false, true ) );
		}


		/**
		 * Returns description for given sub tab - available fields box.
		 *
		 * @since 2.0.0
		 * @package GeoDirectory
		 * @return string The box description.
		 */
		public function left_panel_note() {
			return sprintf( __( 'Click on any box below to add a field of that type to the add %s listing form. You can use a fieldset to group your fields.', 'geodirectory' ), geodir_get_post_type_singular_label( self::$post_type, false, true ) );
		}

		/**
		 * Output the admin cpt settings fields left panel content.
		 *
		 * @since 2.0.0
		 * @package GeoDirectory
		 */
		public function left_panel_content() {

			?>
			<h3 class="h6 text-muted"><?php _e( 'Standard Fields', 'geodirectory' ); ?></h3>

			<div class="inside">

				<div id="gd-form-builder-tab" class="gd-form-builder-tab gd-tabs-panel">

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

			<h3 class="h6  text-muted"><?php _e( 'Predefined Fields', 'geodirectory' ); ?></h3>
			<div class="inside">

				<div id="gd-form-builder-tab-predefined" class="gd-form-builder-tab gd-tabs-panel">

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

			<?php
			$listing_type = self::$post_type;

			$cfs = self::fields_custom( $listing_type );

			if ( ! empty( $cfs ) ) {
			?>

			<h3 class="h6  text-muted"><?php _e( 'Custom Fields', 'geodirectory' ); ?></h3>
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
			global $aui_bs5;
			$listing_type = self::$post_type;

			$cfs = self::fields_standard( self::$post_type );

			?>
			<ul class="row row-cols-1 px-2 mb-0">
				<li class="gd-cf-tooltip-wrap col px-1">
					<a id="gd-fieldset"
					   class="gd-draggable-form-items gd-fieldset btn btn-sm d-block m-0 btn-outline-gray text-dark <?php echo $aui_bs5 ? 'text-start' : 'text-left';?>"
					   href="javascript:void(0);"
					   data-field-custom-type=""
					   data-field-type="fieldset"
					   data-field-type-key="fieldset">

						<i class="fas fa-long-arrow-alt-left " aria-hidden="true"></i>
						<i class="fas fa-long-arrow-alt-right " aria-hidden="true"></i>
						<?php _e( 'Fieldset (section separator)', 'geodirectory' ); ?>

						<span class="gd-help-tip gd-help-tip-no-margin dashicons dashicons-editor-help text-muted <?php echo $aui_bs5 ? 'float-end' : 'float-right';?>" data-toggle="tooltip" title="<?php _e( 'This adds a section separator with a title.', 'geodirectory' );?>"></span>
					</a>
				</li>
			</ul>

			<?php


			if ( ! empty( $cfs ) ) {
				echo '<ul class="row row-cols-2 px-2">';
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
				echo '<ul class="row row-cols-2 px-2">';
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
				echo '<ul class="row row-cols-2 px-2">';
				foreach ( $cfs as $id => $cf ) {
					include( dirname( __FILE__ ) . '/../views/html-admin-settings-cpt-cf-option-item.php' );
				}
				echo '</ul>';
			} else {
				//_e( 'There are no custom fields here yet.', 'geodirectory' );
			}

		}

		/**
		 * Returns the array of custom fields that can be used.
		 *
		 * @since 1.6.9
		 * @package GeoDirectory
		 */
		public static function fields_custom( $post_type = '' ) {

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
					'icon'        => 'fas fa-minus',
					'name'        => __( 'Text', 'geodirectory' ),
					'description' => __( 'Add any sort of text field, text or numbers', 'geodirectory' )
				),
				'datepicker'  => array(
					'field_type'  => 'datepicker',
					'class'       => 'gd-datepicker',
					'icon'        => 'fas fa-calendar',
					'name'        => __( 'Date', 'geodirectory' ),
					'description' => __( 'Adds a date picker.', 'geodirectory' )
				),
				'textarea'    => array(
					'field_type'  => 'textarea',
					'class'       => 'gd-textarea',
					'icon'        => 'fas fa-bars',
					'name'        => __( 'Textarea', 'geodirectory' ),
					'description' => __( 'Adds a textarea', 'geodirectory' )
				),
				'time'        => array(
					'field_type'  => 'time',
					'class'       => 'gd-time',
					'icon'        => 'fas fa-clock',
					'name'        => __( 'Time', 'geodirectory' ),
					'description' => __( 'Adds a time picker', 'geodirectory' )
				),
				'checkbox'    => array(
					'field_type'  => 'checkbox',
					'class'       => 'gd-checkbox',
					'icon'        => 'fas fa-check-square',
					'name'        => __( 'Checkbox', 'geodirectory' ),
					'description' => __( 'Adds a checkbox', 'geodirectory' )
				),
				'phone'       => array(
					'field_type'  => 'phone',
					'class'       => 'gd-phone',
					'icon'        => 'fas fa-phone',
					'name'        => __( 'Phone', 'geodirectory' ),
					'description' => __( 'Adds a phone input', 'geodirectory' )
				),
				'radio'       => array(
					'field_type'  => 'radio',
					'class'       => 'gd-radio',
					'icon'        => 'far fa-dot-circle',
					'name'        => __( 'Radio', 'geodirectory' ),
					'description' => __( 'Adds a radio input', 'geodirectory' )
				),
				'email'       => array(
					'field_type'  => 'email',
					'class'       => 'gd-email',
					'icon'        => 'far fa-envelope',
					'name'        => __( 'Email', 'geodirectory' ),
					'description' => __( 'Adds a email input', 'geodirectory' )
				),
				'select'      => array(
					'field_type'  => 'select',
					'class'       => 'gd-select',
					'icon'        => 'fas fa-caret-square-down',
					'name'        => __( 'Select', 'geodirectory' ),
					'description' => __( 'Adds a select input', 'geodirectory' )
				),
				'multiselect' => array(
					'field_type'  => 'multiselect',
					'class'       => 'gd-multiselect',
					'icon'        => 'fas fa-caret-square-down',
					'name'        => __( 'Multi Select', 'geodirectory' ),
					'description' => __( 'Adds a multiselect input', 'geodirectory' )
				),
				'url'         => array(
					'field_type'  => 'url',
					'class'       => 'gd-url',
					'icon'        => 'fas fa-link',
					'name'        => __( 'URL', 'geodirectory' ),
					'description' => __( 'Adds a url input', 'geodirectory' )
				),
				'html'        => array(
					'field_type'  => 'html',
					'class'       => 'gd-html',
					'icon'        => 'fas fa-code',
					'name'        => __( 'HTML', 'geodirectory' ),
					'description' => __( 'Adds a html input textarea', 'geodirectory' )
				),
				'file'        => array(
					'field_type'  => 'file',
					'class'       => 'gd-file',
					'icon'        => 'far fa-file',
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
		public static function fields_predefined( $post_type ) {

			$custom_fields = array();


			// Business Hours
			$custom_fields['business_hours'] = array(
                'field_type'  => 'business_hours',
                'class'       => 'gd-business-hours',
                'icon'        => 'fas fa-clock',
                'name'        => __( 'Business Hours', 'geodirectory' ),
                'description' => __( 'Adds a business hours input. This can display when the listing is open/closed/', 'geodirectory' ),
                'single_use'         => 'business_hours',
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
	                'field_icon'         => 'fas fa-clock',
	                'css_class'          => '',
	                'cat_sort'           => false,
	                'cat_filter'         => false,
	                'single_use'         => true
                )

			);

			// Email
			$custom_fields['contact_email'] = array( // The key value should be unique and not contain any spaces.
				'field_type'  => 'email',
				'class'       => 'gd-email',
				'icon'        => 'far fa-envelope',
				'name'        => __( 'Contact Email', 'geodirectory' ),
				'description' => __( 'Adds a email input. This can be used by other plugins if the field key remains `email`, for example by Ninja Forms.', 'geodirectory' ),
				'single_use'  => 'email',
				'defaults'    => array(
					'data_type'          => 'TEXT',
					'admin_title'        => 'Contact Email',
					'frontend_title'     => 'Email',
					'frontend_desc'      => 'You can enter the contact email for your listing.',
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
					'field_icon'         => 'far fa-envelope',
					'css_class'          => '',
					'cat_sort'           => false,
					'cat_filter'         => false,
			        'single_use'         => true

				)
			);

			// Email
			$custom_fields['logo'] = array( // The key value should be unique and not contain any spaces.
				'field_type'  => 'file',
				'class'       => 'gd-logo',
				'icon'        => 'far fa-image',
				'name'        => __( 'Company Logo', 'geodirectory' ),
				'description' => __( 'Adds a logo input. This can be used in conjunction with the `GD > Post Images` widget, there is a setting to allow it to use the logo if available. This can also be used by other plugins if the field key remains `logo`.', 'geodirectory' ),
				'single_use'  => 'logo',
				'defaults'    => array(
					'data_type'          => 'TEXT',
					'admin_title'        => 'Logo',
					'frontend_title'     => 'Logo',
					'frontend_desc'      => 'You can upload your company logo.',
					'htmlvar_name'       => 'logo',
					'is_active'          => true,
					'for_admin_use'      => false,
					'default_value'      => '',
					'show_in'            => '',
					'is_required'        => false,
					'option_values'      => '',
					'validation_pattern' => '',
					'validation_msg'     => '',
					'required_msg'       => '',
					'field_icon'         => 'far fa-image',
					'css_class'          => '',
					'cat_sort'           => false,
					'cat_filter'         => false,
					'extra_fields'       => array(
						'gd_file_types'     => geodir_image_extensions(),
						'file_limit'        => 1,
					),
					'single_use'         => true,
				)
			);

			// Website
			$custom_fields['website'] = array( // The key value should be unique and not contain any spaces.
				'field_type'  => 'url',
				'class'       => 'gd-website',
				'icon'        => 'fas fa-external-link-alt',
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
					'field_icon'         => 'fas fa-external-link-alt',
					'css_class'          => '',
					'cat_sort'           => false,
					'cat_filter'         => false
				)
			);


			// Facebook
			$custom_fields['facebook'] = array( // The key value should be unique and not contain any spaces.
				'field_type'  => 'url',
				'class'       => 'gd-facebook',
				'icon'        => 'fab fa-facebook',
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
					'field_icon'         => 'fas fa-external-link-alt',
					'css_class'          => '',
					'cat_sort'           => false,
					'cat_filter'         => false
				)
			);

			// Twitter
			$custom_fields['twitter'] = array( // The key value should be unique and not contain any spaces.
				'field_type'  => 'url',
				'class'       => 'gd-twitter',
				'icon'        => 'fab fa-twitter',
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
					'field_icon'         => 'fab fa-twitter',
					'css_class'          => '',
					'cat_sort'           => false,
					'cat_filter'         => false
				)
			);

			// Instagram
			$custom_fields['instagram'] = array( // The key value should be unique and not contain any spaces.
				'field_type'  => 'url',
				'class'       => 'gd-instagram',
				'icon'        => 'fab fa-instagram',
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
					'field_icon'         => 'fab fa-instagram',
					'css_class'          => '',
					'cat_sort'           => false,
					'cat_filter'         => false
				)
			);

			// TikTok
			$custom_fields['tiktok'] = array( // The key value should be unique and not contain any spaces.
				'field_type'  => 'url',
				'class'       => 'gd-tiktok',
				'icon'        => 'fab fa-tiktok',
				'name'        => __( 'TikTok', 'geodirectory' ),
				'description' => __( 'Adds a TikTok url input. This can be used by other plugins if the htmlvar remains `tiktok`.', 'geodirectory' ),
				'defaults'    => array(
					'data_type'          => 'TEXT',
					'admin_title'        => 'TikTok',
					'frontend_title'     => 'TikTok',
					'frontend_desc'      => 'You can enter your TikTok url.',
					'htmlvar_name'       => 'tiktok',
					'is_active'          => true,
					'for_admin_use'      => false,
					'default_value'      => '',
					'show_in'            => '[detail]',
					'is_required'        => false,
					'option_values'      => '',
					'validation_pattern' => '',
					'validation_msg'     => '',
					'required_msg'       => '',
					'field_icon'         => 'fab fa-tiktok',
					'css_class'          => '',
					'cat_sort'           => false,
					'cat_filter'         => false
				)
			);

			// Terms &amp; Conditions
			$custom_fields['terms_conditions'] = array( // The key value should be unique and not contain any spaces.
				'field_type'  => 'checkbox',
				'class'       => 'gd-terms-conditions',
				'icon'        => 'fas fa-file-alt',
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
					'field_icon'         => 'fas fa-file-alt',
					'css_class'          => '',
					'cat_sort'           => false,
					'cat_filter'         => false
				)
			);

			// Video
			$custom_fields['video'] = array( // The key value should be unique and not contain any spaces.
				'field_type'  => 'textarea',
				'class'       => 'gd-video',
				'icon'        => 'fas fa-video',
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
					'field_icon'         => 'fab fa-instagram',
					'css_class'          => '',
					'cat_sort'           => false,
					'cat_filter'         => false
				)
			);

			// Special offers
			$custom_fields['special_offers'] = array( // The key value should be unique and not contain any spaces.
				'field_type'  => 'textarea',
				'class'       => 'gd-special-offers',
				'icon'        => 'fas fa-gift',
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
					'field_icon'         => 'fas fa-gift',
					'css_class'          => '',
					'cat_sort'           => false,
					'cat_filter'         => false
				)
			);



			// price
			$custom_fields['price'] = array( // The key value should be unique and not contain any spaces.
				'field_type'  => 'text',
				'class'       => 'gd-price',
				'icon'        => 'fas fa-dollar-sign',
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
					'field_icon'         => 'fas fa-dollar-sign',
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

			// price rage (used as priceRange schema)
			$custom_fields['price_range'] = array( // The key value should be unique and not contain any spaces.
				'field_type'  => 'select',
				'class'       => 'gd-price-range',
				'icon'        => 'fas fa-dollar-sign',
				'name'        => __( 'Price Range', 'geodirectory' ),
				'description' => __( 'Adds a schema price range input.', 'geodirectory' ),
				'defaults'    => array(
					'data_type'          => 'VARCHAR',
					'admin_title'        => 'Price Range',
					'frontend_title'     => 'Price Range',
					'frontend_desc'      => 'Enter the price range for the business.',
					'htmlvar_name'       => 'price_range',
					'is_active'          => true,
					'for_admin_use'      => false,
					'default_value'      => '',
					'show_in'            => '[detail],[listing]',
					'is_required'        => false,
					'option_values'      => __( 'Select Price Range/', 'geodirectory' ).',$,$$,$$$,$$$$',
					'validation_pattern' => '',
					'validation_msg'     => '',
					'required_msg'       => '',
					'field_icon'         => 'fas fa-dollar-sign',
					'css_class'          => '',
					'cat_sort'           => true,
					'cat_filter'         => true
				)
			);

			// property status
			$custom_fields['property_status'] = array( // The key value should be unique and not contain any spaces.
				'field_type'  => 'select',
				'class'       => 'gd-property-status',
				'icon'        => 'fas fa-home',
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
					'field_icon'         => 'fas fa-home',
					'css_class'          => '',
					'cat_sort'           => true,
					'cat_filter'         => true
				)
			);

			// property furnishing
			$custom_fields['property_furnishing'] = array( // The key value should be unique and not contain any spaces.
				'field_type'  => 'select',
				'class'       => 'gd-property-furnishing',
				'icon'        => 'fas fa-home',
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
					'field_icon'         => 'fas fa-th-large',
					'css_class'          => '',
					'cat_sort'           => true,
					'cat_filter'         => true
				)
			);

			// property type
			$custom_fields['property_type'] = array( // The key value should be unique and not contain any spaces.
				'field_type'  => 'select',
				'class'       => 'gd-property-type',
				'icon'        => 'fas fa-home',
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
					'field_icon'         => 'fas fa-home',
					'css_class'          => '',
					'cat_sort'           => true,
					'cat_filter'         => true
				)
			);

			// property bedrooms
			$custom_fields['property_bedrooms'] = array( // The key value should be unique and not contain any spaces.
				'field_type'  => 'select',
				'class'       => 'gd-property-bedrooms',
				'icon'        => 'fas fa-home',
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
					'field_icon'         => 'fas fa-bed',
					'css_class'          => '',
					'cat_sort'           => true,
					'cat_filter'         => true
				)
			);

			// property bathrooms
			$custom_fields['property_bathrooms'] = array( // The key value should be unique and not contain any spaces.
				'field_type'  => 'select',
				'class'       => 'gd-property-bathrooms',
				'icon'        => 'fas fa-home',
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
					'field_icon'         => 'fas fa-bold',
					'css_class'          => '',
					'cat_sort'           => true,
					'cat_filter'         => true
				)
			);

			// property area
			$custom_fields['property_area'] = array( // The key value should be unique and not contain any spaces.
				'field_type'  => 'text',
				'class'       => 'gd-area',
				'icon'        => 'fas fa-home',
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
					'field_icon'         => 'fas fa-chart-area',
					'css_class'          => '',
					'cat_sort'           => true,
					'cat_filter'         => true
				)
			);

			// property features
			$custom_fields['property_features'] = array( // The key value should be unique and not contain any spaces.
				'field_type'  => 'multiselect',
				'class'       => 'gd-property-features',
				'icon'        => 'fas fa-home',
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
					'field_icon'         => 'fas fa-plus-square',
					'css_class'          => '',
					'cat_sort'           => true,
					'cat_filter'         => true
				)
			);

			// Twitter feed
			$custom_fields['twitter_feed'] = array( // The key value should be unique and not contain any spaces.
				'field_type'  => 'text',
				'class'       => 'gd-twitter',
				'icon'        => 'fab fa-twitter',
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
					'field_icon'         => 'fab fa-twitter',
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
				'icon'        => 'fas fa-briefcase',
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
					'field_icon'         => 'fas fa-briefcase',
					'css_class'          => '',
					'cat_sort'           => true,
					'cat_filter'         => true
				)
			);

			// job sector
			$custom_fields['job_sector'] = array( // The key value should be unique and not contain any spaces.
				'field_type'  => 'select',
				'class'       => 'gd-job-type',
				'icon'        => 'fas fa-briefcase',
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
					'field_icon'         => 'fas fa-briefcase',
					'css_class'          => '',
					'cat_sort'           => true,
					'cat_filter'         => true
				)
			);

			$custom_fields['dob'] = array( // The key value should be unique and not contain any spaces.
				'field_type'  => 'datepicker',
				'class'       => 'gd-dob',
				'icon'        => 'fas fa-birthday-cake',
				'name'        => __( 'Date of birth', 'geodirectory' ),
				'description' => __( 'Adds a date input for users to enter their date of birth.', 'geodirectory' ),
				'defaults'    => array(
					'data_type'          => 'DATE',
					'admin_title'        => __( 'Date of birth', 'geodirectory' ),
					'frontend_title'     => __( 'Date of birth', 'geodirectory' ),
					'frontend_desc'      => __( 'Enter your date of birth.', 'geodirectory' ),
					'htmlvar_name'       => 'dob',
					'is_active'          => true,
					'for_admin_use'      => false,
					'default_value'      => '',
					'show_in'            => '[detail]',
					'is_required'        => false,
					'validation_pattern' => '',
					'validation_msg'     => '',
					'required_msg'       => '',
					'field_icon'         => 'fas fa-birthday-cake',
					'css_class'          => '',
					'cat_sort'           => true,
					'cat_filter'         => true,
					'extra_fields'       => array(
						'date_range'        => 'c-100:c+0'
					)
				)
			);

			// Featured
			$custom_fields['featured'] = array(
               'field_type'  => 'checkbox',
               'class'       => 'gd-checkbox',
               'icon'        => 'fas fa-certificate',
               'name'        => __( 'Featured', 'geodirectory' ),
               'description' => __( 'Mark listing as a featured.', 'geodirectory' ),
               'single_use'         => 'featured',
               'defaults'    => array(
	                'data_type'          => 'TINYINT',
	                'admin_title'        => 'Featured',
	                'frontend_title'     => 'Is Featured?',
	                'frontend_desc'      => __( 'Mark listing as a featured.', 'geodirectory' ),
	                'htmlvar_name'       => 'featured',
	                'is_active'          => true,
	                'for_admin_use'      => true,
	                'default_value'      => '0',
	                'show_in'            => '',
	                'is_required'        => false,
	                'option_values'      => '',
	                'validation_pattern' => '',
	                'validation_msg'     => '',
	                'required_msg'       => '',
	                'field_icon'         => '',
	                'css_class'          => '',
	                'cat_sort'           => true,
	                'cat_filter'         => true,
	                'single_use'         => true

               )
			);

			// distanceto
			$custom_fields['distanceto'] = array(
				'field_type'  => 'text',
				'class'       => 'gd-distance-to',
				'icon'        => 'fas fa-road',
				'name'        => __( 'Distance To', 'geodirectory' ),
				'description' => __( 'Adds a input for GPS coordinates that will then output the place distance to that point.', 'geodirectory' ),
				'defaults'    => array(
					'data_type'          => 'VARCHAR',
					'admin_title'        => 'Distance To',
					'frontend_title'     => 'Distance To',
					'frontend_desc'      => 'Enter GPS coordinates like `53.347302,-6.258953`',
					'htmlvar_name'       => 'distanceto',
					'is_active'          => true,
					'for_admin_use'      => false,
					'default_value'      => '',
					'show_in'            => '[detail]',
					'is_required'        => false,
					'validation_pattern' => '(-?\d{1,3}\.\d+),(-?\d{1,3}\.\d+)',
					'validation_msg'     => 'Please enter valid GPS coordinates.',
					'required_msg'       => '',
					'field_icon'         => 'fas fa-road',
					'css_class'          => 'gd-distance-to',
					'cat_sort'           => false,
					'cat_filter'         => false,
	                'single_use'         => true
				)
			);

			$custom_fields['service_distance'] = array(
				'field_type'  => 'text',
				'class'       => 'gd-service-distance',
				'icon'        => 'fas fa-arrows-alt-h',
				'name'        => __( 'Service Distance', 'geodirectory' ),
				'description' => __( 'Adds a input to set service area in distance.', 'geodirectory' ),
				'defaults'    => array(
					'data_type'          => 'VARCHAR',
					'admin_title'        => 'Service Distance',
					'frontend_title'     => 'Service Distance',
					'frontend_desc'      => 'Enter your service area in distance. Ex: 10',
					'htmlvar_name'       => 'service_distance',
					'is_active'          => true,
					'for_admin_use'      => false,
					'default_value'      => '',
					'show_in'            => '[detail]',
					'is_required'        => false,
					'validation_pattern' => '\d+(\.\d{2})?',
					'validation_msg'     => 'Please enter valid service area in distance.',
					'required_msg'       => '',
					'field_icon'         => 'fas fa-arrows-alt-h',
					'css_class'          => 'gd-service-distance',
					'cat_sort'           => false,
					'cat_filter'         => false,
					'single_use'         => true
				)
			);

			// Private Address
			$custom_fields['private_address'] = array(
				'field_type'  => 'checkbox',
				'class'       => 'gd-private-address',
				'icon'        => 'fas fa-eye-slash',
				'name'        => __( 'Private Address', 'geodirectory' ),
				'description' => __( 'Adds a checkbox in add listing page to allow users to mark their listings address as a private.', 'geodirectory' ),
				'defaults'    => array(
					'data_type'          => 'TINYINT',
					'admin_title'        => 'Private Address',
					'frontend_title'     => 'Private Address',
					'frontend_desc'      => __( 'This will prevent address and location info from displaying to the users.', 'geodirectory' ),
					'htmlvar_name'       => 'private_address',
					'is_active'          => true,
					'for_admin_use'      => false,
					'is_required'        => false,
					'default_value'      => '0',
					'show_in'            => '',
					'option_values'      => '',
					'validation_pattern' => '',
					'validation_msg'     => '',
					'required_msg'       => '',
					'field_icon'         => 'fas fa-eye-slash',
					'css_class'          => 'gd-private-address',
					'cat_sort'           => false,
					'cat_filter'         => false,
					'single_use'         => true
				)
			);

			// Temporarily Closed
			$custom_fields['temp_closed'] = array(
				'field_type'  => 'checkbox',
				'class'       => 'gd-checkbox',
				'icon'        => 'fas fa-exclamation-circle',
				'name'        => __( 'Temporarily Closed', 'geodirectory' ),
				'description' => __( 'Mark listing as temporarily closed, this will set business hours as closed and show a message in the notifications section.', 'geodirectory' ),
				'single_use'         => 'temp_closed',
				'defaults'    => array(
					'data_type'          => 'TINYINT',
					'admin_title'        => __( 'Temporarily Closed', 'geodirectory' ),
					'frontend_title'     => __( 'Temporarily Closed', 'geodirectory' ),
					'frontend_desc'      => __( 'If your business is temporarily closed select this to let customers and search engines know.', 'geodirectory' ),
					'htmlvar_name'       => 'temp_closed',
					'is_active'          => true,
					'for_admin_use'      => true,
					'default_value'      => '0',
					'show_in'            => '[detail],[listing],[mapbubble]',
					'is_required'        => false,
					'option_values'      => '',
					'validation_pattern' => '',
					'validation_msg'     => '',
					'required_msg'       => '',
					'field_icon'         => 'fas fa-exclamation-circle',
					'css_class'          => '',
					'cat_sort'           => true,
					'cat_filter'         => true,
					'single_use'         => true
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
			return sprintf( __( 'Add listing form', 'geodirectory' ), geodir_get_post_type_singular_label( self::$post_type, false, true ) );
		}


		/**
		 * Returns description for given sub tab - available fields box.
		 *
		 * @since 2.0.0
		 * @package GeoDirectory
		 * @return string The box description.
		 */
		public function right_panel_note() {
			return sprintf( __( 'Click to expand and view field related settings. You may drag and drop to arrange fields order on add %s listing form too.', 'geodirectory' ), geodir_get_post_type_singular_label( self::$post_type, false, true ) );
		}

		/**
		 * Output the admin cpt settings fields left panel content.
		 *
		 * @since 2.0.0
		 * @package GeoDirectory
		 */
		public function right_panel_content() {
			global $wpdb;
			$post_type = self::$post_type;
			$fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . GEODIR_CUSTOM_FIELDS_TABLE . " WHERE post_type = %s ORDER BY sort_order ASC", array($post_type)));

			//print_r($tabs);
			?>
			<div class="inside">

				<div id="gd-form-builder-tab" class="gd-form-builder-tab gd-tabs-panel">
					<div class="field_row_main">
						<div class="dd gd-tabs-layout" >

							<?php


							echo '<ul class="dd-list gd-tabs-sortable gd-custom-fields-sortable geodir-cpt-cf-items ps-0 list-group">';

							if ( ! empty( $fields ) ) {

								echo self::loop_fields_output($fields);

							} else {
								_e( 'No tab items have been added yet.', 'geodirectory' );
							}
							echo '</ul>';

							?>

						</div>
					</div>
				</div>
			</div>

			<?php
		}

		/**
		 * Loop through the base to output them with the different levels.
		 * @param $tabs
		 * @param string $tab_id
		 *
		 * @return string
		 */
		public static function loop_fields_output( $tabs, $tab_id = '' ) {
			ob_start();

			if ( ! empty( $tabs ) ) {
				foreach ( $tabs as $key => $tab ) {
					if ( $tab_id && $tab->id != $tab_id ) {
						continue;
					} elseif ( $tab_id && $tab->id == $tab_id && $tab->tab_level > 0 ) {
						echo self::output_custom_field_setting_item( $tab->id, $tab );
						break;
					}

					if ( $tab->tab_level == '1' ) {
						continue;
					}

					$tab_content = self::output_custom_field_setting_item( $tab->id, $tab );
					$tab_content = $tab_content ? str_replace( "</li>", "", $tab_content ) : '';
					$child_tabs = '';

					foreach ( $tabs as $child_tab ) {
						if ( $child_tab->tab_parent == $tab->id ) {
							$child_tab_content = self::output_custom_field_setting_item( $child_tab->id, $child_tab );

							if ( $child_tab_content ) {
								$child_tabs .= $child_tab_content;
							}
						}
					}

					if ( $child_tabs ) {
						$tab_content .= "<ul>";
						$tab_content .= $child_tabs;
						$tab_content .= "</ul>";
					}

					echo $tab_content;
					echo "</li>";

					unset( $tabs[ $key ] );
				}
			}

			$content = ob_get_clean();
			$content = trim( $content );

			return $content;
		}

        /**
         * GeoDir get all fields by posttype.
         *
         * @since 2.0.0
         *
         * @param string $post_type Post type.
         * @return string Return all fields.
         */
		public static function get_all_fields($post_type){
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
		public static function output_custom_field_setting_item( $field_id = '', $field = '', $cf = array() ) {
			ob_start();

			// If field not provided get it.
			if ( ! is_object( $field ) && $field_id ) {
				$field = self::get_item( $field_id );
			}

			// If field template not provided get it.
			if ( empty( $cf ) ) {
				$cf_arr = self::get_all_fields( $field->post_type );

				$cf = isset( $cf_arr[ $field->field_type_key ] ) ? $cf_arr[ $field->field_type_key ] : '';
			}

			// Set defaults
			if ( isset( $cf['defaults'] ) ) {
				foreach ( $cf['defaults'] as $key => $val ) {
					if ( ! isset( $field->{$key} ) ) {
						$field->{$key} = $val;
					}
				}
			}

			if ( ! isset( $field->is_default ) ) {
				$field->is_default = 0;
			}

			// Strip slashes but not from json extra_fields.
			if ( isset( $field->extra_fields ) ) {
				$extra_fields = $field->extra_fields;
			}

			$validation_pattern = isset( $field->validation_pattern ) ? $field->validation_pattern : '';
			$field = wp_unslash( $field ); // Strip slashes from labels.
			$field->validation_pattern = str_replace( '\\\\', '\\', $validation_pattern ); // We need the validation pattern without slashes stripped.

			if ( isset( $field->extra_fields ) ) {
				$field->extra_fields = $extra_fields;
			}

			// Set nonce.
			$nonce = wp_create_nonce( 'custom_fields_' . $field->id );

			// Set if this is a default field.
			$default = isset( $field->is_admin ) ? $field->is_admin : '';

			// Remove Send Enquiry from listings page.
			$display_on_listing = true;
			$htmlvar_name = isset( $field->htmlvar_name ) && $field->htmlvar_name != '' ? $field->htmlvar_name : '';

			if ( $htmlvar_name == 'geodir_email' ) {
				$display_on_listing  = false;
			}

			// Hide the field from custom fields (i.e. address field from location less CPT).
			if ( has_filter( "geodir_cfa_skip_item_output_{$field->field_type}" ) ) {
				if ( apply_filters( "geodir_cfa_skip_item_output_{$field->field_type}", false, $field_id, $field, $cf ) === true ) {
					$content = ob_get_clean();
					$content = trim( $content );

					return $content;
				}
			}

			$icon = isset( $cf['icon'] ) ? $cf['icon'] : ( isset( $field->field_icon ) ? $field->field_icon : '' );

			// Set the field icon.
			if ( geodir_is_fa_icon( $icon ) ) {
				$field_icon = '<i class="' . esc_attr( $icon ) . '" aria-hidden="true"></i>';
			} elseif ( geodir_is_icon_url( $icon ) ) {
				$field_icon = '<b style="background-image: url("' . esc_attr( $icon ) . '")"></b>';
			} else {
				$field_icon = '<i class="fas fa-cog" aria-hidden="true"></i>';
			}

			// If field type name is missing set it from main settings.
			if ( isset( $cf['name'] ) && $cf['name'] ) {
				$field->field_type_name = $cf['name'];
			} else {
				$field->field_type_name = $field->field_type;
			}

			// Make new field active status ticked by default.
			if ( empty( $field->is_active ) && isset( $field->id ) && strpos( $field->id, 'new-' ) !== false ) {
				$field->is_active = 1;
			}

			/**
			 * Contains custom field html.
			 *
			 * @since 2.0.0
			 */
			include( dirname( __FILE__ ) . '/../views/html-admin-settings-cpt-cf-setting-item.php' );

			$content = ob_get_clean();
			$content = trim( $content );

			return $content;
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
		 *    @type string $placeholder_value The placeholder text to be displayed in the input before user input.
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
		private static function sanatize_custom_field( $input ) {
			// If object convert to array
			if ( is_object( $input ) ) {
				$input = json_decode( json_encode( $input ), true );
			}

			$field = new stdClass();

			// sanatize
			$field->field_id = isset( $input['field_id'] ) ? absint( $input['field_id'] ) : '';

			$field_item = ! empty( $field->field_id ) ? self::get_item( $field->field_id ) : NULL;

			$field->post_type = isset( $input['post_type'] ) ? sanitize_text_field( $input['post_type'] ) : null;
			$field->admin_title = isset( $input['admin_title'] ) ? sanitize_text_field( $input['admin_title'] ) : null;
			$field->frontend_title = isset( $input['frontend_title'] ) ? sanitize_text_field( $input['frontend_title'] ) : null;
			$field->field_type = isset( $input['field_type'] ) ? sanitize_text_field( $input['field_type'] ) : null;
			$field->field_type_key = isset( $input['field_type_key'] ) ? sanitize_text_field( $input['field_type_key'] ) : $field->field_type;
			$field->htmlvar_name = isset( $input['htmlvar_name'] ) ? str_replace(array('-',' ','"',"'"), array('_','','',''), sanitize_title_with_dashes( $input['htmlvar_name'] ) ) : null;
			$field->frontend_desc = isset( $input['frontend_desc'] ) ? stripslashes( wp_kses_post( esc_attr( $input['frontend_desc'] ) ) ) : '';

			$field->clabels = isset( $input['clabels'] ) ? sanitize_text_field( $input['clabels'] ) : null;
			// default_value
			$default_value = isset( $input['default_value'] ) ? $input['default_value'] : '';
			if ( $default_value !== '' ) {
				if ( $field->field_type == 'html' ) {
					$default_value = geodir_sanitize_html_field( $default_value );
				} else if ( $field->field_type == 'textarea' ) {
					$default_value = geodir_sanitize_textarea_field( $default_value );
				} else {
					$default_value = sanitize_text_field( $default_value );
				}
			}
			$field->default_value = $default_value;
			$field->db_default = isset( $input['db_default'] ) ? sanitize_text_field( esc_attr( $input['db_default'] ) ) : '';
			$field->placeholder_value = isset( $input['placeholder_value'] ) ? sanitize_text_field( esc_attr( $input['placeholder_value'] ) ) : '';
			$field->sort_order = isset( $input['sort_order'] ) ? intval( $input['sort_order'] ) : self::default_sort_order();
			$field->is_active = isset( $input['is_active'] ) ? absint( $input['is_active'] ) : 0;
			$field->is_default  = isset( $input['is_default'] ) ? absint( $input['is_default'] ) : 0;
			$field->is_required = isset( $input['is_required'] ) ? absint( $input['is_required'] ) : 0;
			$field->required_msg = isset( $input['required_msg'] ) ? sanitize_text_field( $input['required_msg'] ) : '';
			$field->css_class = isset( $input['css_class'] ) ? sanitize_text_field( $input['css_class'] ) : '';
			$field->field_icon = isset( $input['field_icon'] ) ? sanitize_text_field( $input['field_icon'] ) : '';
			$field->show_in = isset( $input['show_in'] ) ? self::sanatize_show_in( $input['show_in'] ) : '';
			$field->option_values = isset( $input['option_values'] ) ? self::sanitize_option_values( $input['option_values'] ) : '';
			$field->packages = isset( $input['show_on_pkg'] ) ? self::sanatize_show_on_pkg( $input['show_on_pkg'] ) : '';
			$field->cat_sort = isset( $input['cat_sort'] ) ? absint( $input['cat_sort'] ) : 0;
			$field->cat_filter = isset( $input['cat_filter'] ) ? absint( $input['cat_filter'] ) : 0;
			$field->data_type = isset( $input['data_type'] ) ? sanitize_text_field( $input['data_type'] ) : '';
			$field->extra_fields = isset( $input['extra'] ) ? self::sanatize_extra( $input['extra'] ) : '';
			$field->decimal_point = isset( $input['decimal_point'] ) ? absint( $input['decimal_point'] ) : 0;
			$field->validation_pattern = isset( $input['validation_pattern'] ) ? sanitize_text_field( $input['validation_pattern'] ) : '';
			$field->validation_msg = isset( $input['validation_msg'] ) ? sanitize_text_field( $input['validation_msg'] ) : '';
			$field->for_admin_use = isset( $input['for_admin_use'] ) ? absint( $input['for_admin_use'] ) : 0;
			$field->add_column = !empty( $input['add_column'] ) ? 1 : 0;

			if ( isset( $input['tab_parent'] ) && isset( $input['tab_level'] ) ) {
				$field->tab_parent = (int) $input['tab_parent'];
				$field->tab_level = (int) $input['tab_level'];
			}

			// Set some default after sanitation
			$field->data_type = self::sanitize_data_type($field);
			if(!$field->admin_title){$field->admin_title = $field->frontend_title;}
			//if(!$field->htmlvar_name){$field->htmlvar_name =str_replace(array('-',' ','"',"'"), array('_','','',''), sanitize_title_with_dashes( $input['frontend_title'] ) );} // Don't change fieldset htmlvar_name(key) on edit
			if ( empty( $field->htmlvar_name ) && $field->field_type == 'fieldset' && ! empty( $field_item ) ) {
				$field->htmlvar_name = $field_item->htmlvar_name;
			}

			// we use original input so the special chars are no converted already
			if ( empty( $field->htmlvar_name ) ) {
				$htmlvar_name = sanitize_key( str_replace( array( ' - ', '-', ' ', '"', "'" ), array( '_', '_', '_', '', '' ), $input['frontend_title'] ) );
				if ( str_replace( '_', '', $htmlvar_name ) != '' ) {
					$field->htmlvar_name = substr( $htmlvar_name, 0, 50 );
				} else {
					$field->htmlvar_name = 'cf' . time();
				}
			}

			if ( empty( $field->field_id ) && is_numeric( substr( $field->htmlvar_name, 0, 1 ) ) ) {
				$field->htmlvar_name = 'cf' . $field->htmlvar_name; // Integer as column name is not accepted & ID's should not start with a number.
			}

			// Check for reserved fields before assign generated htmlvar_name.
			if ( ! empty( $field->post_type ) && empty( $field->field_id ) && ! empty( $field->htmlvar_name ) && empty( $input['htmlvar_name'] ) ) {
				$reserved_fields = self::reserved_fields( $field->post_type );

				if ( in_array( $field->htmlvar_name, $reserved_fields ) ) {
					$table =  geodir_db_cpt_table( $field->post_type );
					$exists = geodir_column_exist( $table, $field->htmlvar_name );

					if ( $exists ) {
						$suffix = 1;

						do {
							$_column = $field->htmlvar_name . "_$suffix";
							$exists = geodir_column_exist( $table, $_column );
							$suffix++;
						} while ( $exists );

						$field->htmlvar_name = $_column;
					}
				}
			}

			return apply_filters( 'geodir_cpt_cf_sanatize_custom_field', $field, $input );

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

		/**
		 * GeoDir sanitize extra values.
		 *
		 * @since 2.0.0
		 *
		 * @param string|array $value Extra values.
		 * @return array|string.
		 */
		private static function sanatize_extra( $value ) {
			if ( is_array( $value ) ) {
				if ( empty( $value ) ) {
					$value = '';
				} else {
					foreach ( $value as $key => $val ) {
						if ( $key == 'gd_file_types' && is_array( $val ) ) {
							$val = array_filter( $val );
						}

						$value[ $key ] = self::sanatize_extra( $val );
					}

					$value = maybe_serialize( $value );
				}
			} else {
				$value = sanitize_text_field( $value );
			}

			return $value;
		}

        /**
         * GeoDir sanitize show on pkg.
         *
         * @since 2.0.0
         *
         * @param array $value Show log values.
         * @return string $value.
         */
		private static function sanatize_show_on_pkg( $value ){
			if(is_array($value)  ){
				if(empty($value)){$value = '';}else{
					$value = implode(",",array_map( 'absint', $value  ));
				}
			}

			return $value;
		}

        /**
         * GeoDir sanitize show in text fields values.
         *
         * @since 2.0.0
         *
         * @param array $value Array values.
         * @return string $value.
         */
		private static function sanatize_show_in( $value ) {
			if ( is_array( $value ) ) {
				if ( empty( $value ) ) {
					$value = '';
				} else {
					$value = array_map( 'sanitize_text_field', $value );
					$value = array_filter( $value );
					$value = ! empty( $value ) ? implode( ",", $value ) : '';
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
					case 'badge':
					case 'checkbox':
						$value = 'TINYINT';
						break;
					case 'textarea':
					case 'html':
					case 'url':
					case 'file':
					$value = 'TEXT';
						break;
					case 'datepicker':
						$value = 'DATE';
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
		private static function sanitize_option_values( $value ) {
			if ( strpos( $value, PHP_EOL ) !== false ) {
				$value = geodir_sanitize_textarea_field( $value );
			} else {
				$value = sanitize_text_field( $value);
			}

			return $value;
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

        /**
         * GeoDir save sort item.
         *
         * @since 2.0.0
         *
         * @global object $wpdb WordPress Database object.
         *
         * @param object $field Item field object.
         */
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
		public static function delete_custom_field( $field_id ) {
			global $wpdb;

			$field_id = absint( $field_id );

			if ( $field = $wpdb->get_row( $wpdb->prepare( "SELECT htmlvar_name, post_type, field_type FROM " . GEODIR_CUSTOM_FIELDS_TABLE . " WHERE id = %d LIMIT 1", array( $field_id ) ) ) ) {
				$wpdb->query( $wpdb->prepare( "DELETE FROM " . GEODIR_CUSTOM_FIELDS_TABLE . " WHERE id = %d", array( $field_id ) ) );

				$post_type = $field->post_type;
				$htmlvar_name = $field->htmlvar_name;

				if ( $post_type != '' && $htmlvar_name != '' ) {
					$wpdb->query( $wpdb->prepare( "DELETE FROM " . GEODIR_CUSTOM_SORT_FIELDS_TABLE . " WHERE htmlvar_name = %s AND post_type = %s LIMIT 1", array( $htmlvar_name, $post_type ) ) );
				}

				/**
				 * Called after a custom field is deleted.
				 *
				 * @since 1.0.0
				 * @param string $field_id The fields ID.
				 * @param string $field->htmlvar_name The html variable name for the field.
				 * @param string $post_type The post type the field belongs to.
				 */
				do_action( 'geodir_after_custom_field_deleted', $field_id, $field->htmlvar_name, $post_type );

				$table =  geodir_db_cpt_table( $post_type );

				if ( $field->field_type == 'address' ) {
					$wpdb->query( "ALTER TABLE {$table} DROP `street`" );
					$wpdb->query( "ALTER TABLE {$table} DROP `city`" );
					$wpdb->query( "ALTER TABLE {$table} DROP `region`" );
					$wpdb->query( "ALTER TABLE {$table} DROP `country`" );
					$wpdb->query( "ALTER TABLE {$table} DROP `zip`" );
					$wpdb->query( "ALTER TABLE {$table} DROP `latitude`" );
					$wpdb->query( "ALTER TABLE {$table} DROP `longitude`" );
					$wpdb->query( "ALTER TABLE {$table} DROP `mapview`" );
					$wpdb->query( "ALTER TABLE {$table} DROP `mapzoom`" );
				} else {
					$is_system = in_array( $field->htmlvar_name, self::system_fields( $field->post_type ) ); // Prevent deleting system fields.
					$is_reserved = in_array( $field->htmlvar_name, self::reserved_fields( $field->post_type ) ); // Prevent deleting reserved fields.

					if ( $field->field_type != 'fieldset' && $field->field_type != 'link_posts' && ! $is_system && ! $is_reserved ) {
						$wpdb->query( "ALTER TABLE {$table} DROP `" . $field->htmlvar_name . "`" );
					}
				}

				// clear cache
				delete_transient( 'geodir_post_custom_fields' );

				return $field_id;
			} else {
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
		public static function set_field_orders($tabs = array()){
			global $wpdb;

			$count = 0;
			if (!empty($tabs)) {
				$result = false;
				foreach ( $tabs as $index => $info ) {
					$result = $wpdb->update(
						GEODIR_CUSTOM_FIELDS_TABLE,
						array('sort_order' => $index,'tab_level' => $info['tab_level'],'tab_parent' => $info['tab_parent']),
						array('id' => absint($info['id'])),
						array('%d','%d','%d')
					);
					$count ++;
				}

				// clear cache
				delete_transient( 'geodir_post_custom_fields' );

				if($result !== false){
					return true;
				}else{
					return new WP_Error( 'failed', __( "Failed to sort tab items.", "geodirectory" ) );
				}
			}else{
				return new WP_Error( 'failed', __( "Failed to sort tab items.", "geodirectory" ) );
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
		public static function save_custom_field( $field = array() ) {
			global $wpdb;

			$field = self::sanatize_custom_field( $field );

			//Ensure that field key/html var does not match standard wordpress columns
			$wp_post_cols = array(
				'ID',
				'post_author',
				'post_date',
				'post_date_gmt',
				'post_excerpt',
				'post_status',
				'comment_status',
				'ping_status',
				'post_password',
				'post_name',
				'to_ping',
				'pinged',
				'post_modified',
				'post_modified_gmt',
				'post_content_filtered',
				'post_parent',
				'guid',
				'menu_order',
				'post_type',
				'post_mime_type',
				'comment_count',
				'geodir_search',
				'type',
				'near',
				'geo_lat',
				'geo_lon',
				'action',
				'security',
				'preview'
			);

			if ( in_array( $field->htmlvar_name, $wp_post_cols ) ) {
				return new WP_Error( 'failed', __( "Field key name MUST NOT match a standard WordPress field, please use another key and re-save.", "geodirectory" ) );
			}

			// Check field exists.
			$exists = self::field_exists( $field );

			if ( is_wp_error( $exists ) ) {
				return new WP_Error( 'failed', $exists->get_error_message() );
			} else if ( $exists && !$field->field_id ) {
				if ( $field->htmlvar_name == 'featured' ) {
					return new WP_Error( 'failed', wp_sprintf( __( "%s field already exists, it can not be used twice.", "geodirectory" ),  $field->htmlvar_name ) );
				} else {
					return new WP_Error( 'failed', __( "Field key name MUST be unique, duplicate field detected, please fix and re-save.", "geodirectory" ) );
				}
			}

			$table =  geodir_db_cpt_table( $field->post_type );
			$old_field = geodir_get_field_infoby( 'htmlvar_name', $field->htmlvar_name, $field->post_type );
			$column_attr = $field->data_type;

			switch ($field->field_type){
//				case 'address':
//					echo $field->field_type; //@todo we need to do stuff here
//					break;
				case 'checkbox':
					$column_attr .= "( 1 ) NULL ";
					if (isset($field->db_default) && (int)$field->db_default === 1) {
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
					}

					if ( $field->data_type == 'TEXT' || $field->field_type == 'multiselect' ) {
						// Set TEXT data type to prevent row size database error.
						if ( $field->field_type == 'multiselect' ) {
							$column_attr = "TEXT";
						}
						$column_attr .= " NULL ";
					} else {
						$column_attr .= "( $op_size ) NULL ";
					}

					// Update the field size to new max
					if ( $exists ) {
						$meta_field_add = "ALTER TABLE {$table} CHANGE `" . $field->htmlvar_name . "` `" . $field->htmlvar_name . "` {$column_attr}";
						$alter_result = $wpdb->query( $meta_field_add );

						if ( $alter_result === false ) {
							if ( ! empty( $wpdb->last_error ) ) {
								$db_error = '[' . $table . '] ' . $wpdb->last_error;
							} else {
								$db_error = __( "Column change failed, you may have too many columns.", "geodirectory" );
							}

							return new WP_Error( 'failed', $db_error );
						}
					}

					if ( $field->db_default != '' ) {
						$column_attr.= $wpdb->prepare(" DEFAULT %s ",$field->db_default);
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
					$column_format = '';

					if ( isset( $field->data_type ) && ( $field->data_type == "FLOAT" || $field->data_type == "DECIMAL" ) ) {
						$decimal_place = isset( $field->decimal_point ) && $field->decimal_point ? absint( $field->decimal_point ) : 2;
						$column_attr = "DECIMAL(" . ( 14 + $decimal_place ) . ",$decimal_place)";
						$column_format = $column_attr;

						if ( $field->db_default !== '' ) {
							$db_default = $wpdb->prepare( " DEFAULT %f ", $field->db_default );
						} else {
							$db_default = " DEFAULT NULL ";
						}
					} else if ( isset( $field->data_type ) && $field->data_type == "INT" ) {
						$column_attr = "BIGINT";
						$column_format .= "BIGINT (20) NULL";

						if ( $field->db_default !== '' ) {
							$db_default = $wpdb->prepare( " DEFAULT %d ", $field->db_default );
						} else {
							$db_default = " DEFAULT NULL ";
						}
					} else {
						$column_attr .= "( 254 ) NULL ";
						$column_format .= "VARCHAR (254) NULL";

						if ( $field->db_default !== '' ) {
							$db_default = $wpdb->prepare( " DEFAULT %s ", $field->db_default );
						} else {
							$db_default = " DEFAULT NULL ";
						}
					}

					$column_attr .= $db_default;
					$column_format .= $db_default;

					// Update field data type if changed
					if ( $exists && ! empty( $old_field ) && ! empty( $field->data_type ) && ( ( ! empty( $old_field->data_type ) && $field->data_type != $old_field->data_type ) || ( $field->data_type == "FLOAT" || $field->data_type == "DECIMAL" || $field->data_type == "INT" ) || ( $field->field_type == 'text' && ! empty( $old_field['data_type'] ) && $old_field['data_type'] != $field->data_type ) ) ) {
						$wpdb->query( "ALTER TABLE {$table} CHANGE `" . $field->htmlvar_name . "` `" . $field->htmlvar_name . "` " . trim( $column_format ) );
					}
				break;
				case 'int':
				case 'INT':
					$column_attr = "INT";
					break;
				case 'datepicker':
					$column_attr = "DATE";
					break;
				case 'fieldset':
					// Nothing happenes for fieldset
					break;
				default:
					$column_attr .= "( 254 ) NULL ";
			}

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
				'placeholder_value' => $field->placeholder_value,
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
				'%s', // placeholder_value
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

			$cf_data = array(
				'db_data' => $db_data,
				'db_format' => $db_format
			);

			/**
			 * Filter the custom fields data to save in database.
			 *
			 * @since 2.2.25
			 *
			 * @param array  $cf_data Field data.
			 * @param object $field Custom field object.
			 */
			$cf_data = apply_filters( 'geodir_cpt_cf_save_data', $cf_data, $field );

			if ( ! empty( $cf_data['db_data'] ) && ! empty( $cf_data['db_format'] ) ) {
				$db_data = $cf_data['db_data'];
				$db_format = $cf_data['db_format'];
			}

			if ( $exists ) {
				// Update the field settings.
				$result = $wpdb->update(
					GEODIR_CUSTOM_FIELDS_TABLE,
					$db_data,
					array('id' => $field->field_id),
					$db_format
				);

				if ( $result === false ) {
					return new WP_Error( 'failed', __( "Field update failed.x", "geodirectory" ) );
				}
				// @todo, should we ALTER the field type here to see if we can improve it, ie VARCHAR(123)
			} else {
				// Insert the field settings.
				$result = $wpdb->insert(
					GEODIR_CUSTOM_FIELDS_TABLE,
					$db_data,
					$db_format
				);

				if ( $result === false ) {
					return new WP_Error( 'failed', __( "Field create failed.", "geodirectory" ) );
				} else {
					$field->field_id = $wpdb->insert_id;
				}

				// Check if its a default field that does not need a column added
				$default_fields = self::get_default_field_htmlvars();

				if ( ( ! in_array( $field->htmlvar_name, $default_fields ) && ! apply_filters( 'geodir_cfa_skip_column_add', $field->field_type == 'fieldset', $field ) ) || ! empty( $field->add_column ) ) {
					// Add the new column to the details table.
					$add_details_column = geodir_add_column_if_not_exist( $table, $field->htmlvar_name, $column_attr );

					if ( $add_details_column === false ) {
						// Delete CF if column creation fails.
						if ( ! empty( $field->field_id ) ) {
							$wpdb->query( $wpdb->prepare( "DELETE FROM `" . GEODIR_CUSTOM_FIELDS_TABLE . "` WHERE id = %d", array( $field->field_id ) ) );
						}

						return new WP_Error( 'failed', __( 'Column creation failed, you may have too many columns or the default value does not match with field data type.', 'geodirectory' ) );
					}
				}
			}

			// update or delete the sort order field.
			self::save_sort_item($field);

			/**
			 * Called after all custom fields are saved for a post.
			 *
			 * @since 1.0.0
			 * @param int $field_id The field ID.
			 * @param object $field The field object.
			 */
			do_action( 'geodir_after_custom_fields_updated', $field->field_id, $field );

			// clear cache
			delete_transient( 'geodir_post_custom_fields' );

			return $field->field_id;

		}


        /**
         * GeoDir get custom post type custom fields values.
         *
         * @since 2.0.0
         *
         * @param string $post_type Optional. Post type values . Default gd_place.
         *
         * @global object $wpdb WordPress Database object.
         *
         * @return array|object Return results in array or object.
         */
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
			return geodir_show_in_locations( $field, $field_type );
		}

		/**
		 * Get the field object.
		 *
		 * @param int $id Field id. default 0.
		 *
		 * @return object Field object.
		 */
		public static function get_item( $id = 0 ) {
			global $wpdb;

			return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . GEODIR_CUSTOM_FIELDS_TABLE . " WHERE id = %d LIMIT 1", array( $id ) ) );
		}

		/**
		 * Get the child fields.
		 *
		 * @param int $parent Parent field id. default 0.
		 *
		 * @return object Field object.
		 */
		public static function get_childs( $parent = 0 ) {
			global $wpdb;

			return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . GEODIR_CUSTOM_FIELDS_TABLE . " WHERE tab_parent = %d ORDER BY sort_order ASC, tab_level ASC", array( $parent ) ) );
		}

		/**
		 * Get the list of GD system fields.
		 *
		 * @since 2.2.4
		 *
		 * @param string $post_type The post type.
		 * @return array List of system fields.
		 */
		public static function system_fields( $post_type ) {
			$fields = array(
				'post_title',
				'post_content',
				'post_category',
				'post_tags',
				'post_images'
			);

			/**
			 * Filter the list of GD system fields.
			 *
			 * @since 2.2.4
			 *
			 * @param array List of system fields.
			 * @param string $post_type The post type.
			 */
			return apply_filters( 'geodir_cpt_cf_system_fields', $fields, $post_type );
		}

		/**
		 * Get the list of GD reserved fields.
		 *
		 * @since 2.2.4
		 *
		 * @param string $post_type The post type.
		 * @return array List of reserved fields.
		 */
		public static function reserved_fields( $post_type ) {
			$fields = array(
				'id',
				'post_id',
				'_search_title',
				'post_status',
				'default_category',
				'featured_image',
				'overall_rating',
				'rating_count',
				'ratings'
			);

			if ( GeoDir_Post_types::supports( $post_type, 'location' ) ) {
				$fields = array_merge( $fields, array(
					'city',
					'region',
					'country',
					'neighbourhood',
					'zip',
					'latitude',
					'longitude',
					'mapview',
					'mapzoom'
				) );
			}

			/**
			 * Filter the list of GD reserved fields.
			 *
			 * @since 2.2.4
			 *
			 * @param array List of reserved fields.
			 * @param string $post_type The post type.
			 */
			return apply_filters( 'geodir_cpt_cf_reserved_fields', $fields, $post_type );
		}
	}

endif;

return new GeoDir_Settings_Cpt_Cf();
