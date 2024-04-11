<?php
/**
 * GeoDirectory CPT Sorting Settings
 *
 * @author      AyeCode
 * @category    Admin
 * @package     GeoDirectory/Admin
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'GeoDir_Settings_Cpt_Sorting', false ) ) :

	/**
	 * GeoDir_Admin_Settings_General.
	 */
	class GeoDir_Settings_Cpt_Sorting extends GeoDir_Settings_Page {

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

			self::$post_type = ( ! empty( $_REQUEST['post_type'] ) && is_scalar( $_REQUEST['post_type'] ) ) ? sanitize_title( $_REQUEST['post_type'] ) : 'gd_place';
			self::$sub_tab   = ! empty( $_REQUEST['tab'] ) ? sanitize_title( $_REQUEST['tab'] ) : 'general';

			$this->id    = 'cpt-sorting';
			$this->label = __( 'Sorting', 'geodirectory' );

			add_filter( 'geodir_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'geodir_settings_' . $this->id, array( $this, 'output' ) );
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
			return sprintf( __( 'Click on any box below to make it appear in the sorting option dropdown on %s listing and search results.<br />To make a field available here, go to custom fields tab and expand any field from selected fields panel and tick the checkbox saying \'Include this field in sort option\'.', 'geodirectory' ), geodir_get_post_type_singular_label( self::$post_type, false, true ) );
		}

		/**
		 * Output the admin settings cpt sorting left panel content.
		 *
		 * @since 2.0.0
		 * @package GeoDirectory
		 */
		public function left_panel_content() {
			?>
			<div class="inside">

				<div id="gd-form-builder-tab" class="gd-form-builder-tab gd-tabs-panel">
					<ul class="row row-cols-2 px-2">
						<?php
						$sort_options = self::custom_sort_options( self::$post_type );


						if(!empty($sort_options)){
							foreach ( $sort_options as $key => $val ) {
								$val = stripslashes_deep( $val ); // strip slashes

								$display             = '';
								?>

								<li class="col px-1" <?php echo $display; ?>>
									<a id="gd-<?php echo esc_attr( $val['field_type'] . '-_-' . $val['htmlvar_name'] ); ?>"
									   data-field-type-key="<?php echo esc_attr( $val['htmlvar_name'] ); ?>"
									   data-field-type="<?php echo esc_attr( $val['field_type'] ); ?>"
									   class="gd-draggable-form-items  gd-<?php echo esc_attr( $val['field_type'] ); ?> geodir-sort-<?php echo esc_attr( $val['htmlvar_name'] ); ?> btn btn-sm d-block m-0 btn-outline-gray text-dark text-left text-start"
									   href="javascript:void(0);">
										<?php if ( isset( $val['field_icon'] ) && strpos( $val['field_icon'], 'fa-' ) !== false ) {
											echo '<i class="fas ' . esc_attr( $val['field_icon'] ) . '" aria-hidden="true"></i>';
										} elseif ( isset( $val['field_icon'] ) && $val['field_icon'] ) {
											echo '<b style="background-image: url("' . esc_attr( $val['field_icon'] ) . '")"></b>';
										} else {
											echo '<i class="fas fa-cog" aria-hidden="true"></i>';
										} ?>
										<?php echo esc_attr( $val['frontend_title'] ); ?>
										<?php if ( ! empty( $val['description'] ) ) { ?>
										<span class="dashicons dashicons-editor-help text-muted float-right float-end" data-toggle="tooltip"
										      title="<?php echo esc_attr( $val['description'] ); ?>">
										<?php } ?>
								</span>
									</a>
								</li>

								<?php
							}
						}
						?>
					</ul>
					<div style="clear:both"></div>

				</div>
			</div>
			<?php

		}


		/**
		 * Returns heading for the CPT settings left panel.
		 *
		 * @since 2.0.0
		 * @package GeoDirectory
		 * @return string The page heading.
		 */
		public static function right_panel_title() {
			return sprintf( __( 'Sorting options', 'geodirectory' ), geodir_get_post_type_singular_label( self::$post_type, false, true ) );
		}

		/**
		 * Returns description for given sub tab - available fields box.
		 *
		 * @since 2.0.0
		 * @package GeoDirectory
		 * @return string The box description.
		 */
		public function right_panel_note() {
			return sprintf( __( 'Click to expand and view field related settings. You may drag and drop to arrange fields order in sorting option dropdown box on %s listing and search results page.', 'geodirectory' ), geodir_get_post_type_singular_label( self::$post_type, false, true ) );
		}

		/**
		 * Output the admin cpt settings fields left panel content.
		 *
		 * @since 2.0.0
		 * @package GeoDirectory
		 */
		public function right_panel_content() {
			?>
			<form></form> <!-- chrome removes the first form inside a form for some reason so we need this ?> -->
			<div class="inside">

				<div id="gd-form-builder-tab" class="gd-form-builder-tab gd-tabs-panel">
					<div class="field_row_main">
						<div class="dd gd-tabs-layout" >
							<ul class="dd-list gd-tabs-sortable gd-sortable-sortable ps-0 list-group">
								<?php
								global $wpdb;

								$fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . GEODIR_CUSTOM_SORT_FIELDS_TABLE . " WHERE post_type = %s AND field_type != 'address' ORDER BY sort_order ASC", array( self::$post_type ) ) );

								if ( ! empty( $fields ) ) {

									echo self::loop_fields_output($fields);

								} else {
									_e( 'Select fields from the left to be able to add new sort options.', 'geodirectory' );
								}



//								if ( ! empty( $fields ) ) {
//									foreach ( $fields as $field ) {
//										//$result_str = $field->id;
//										$result_str    = $field;
//										$field_type    = $field->field_type;
//										$field_ins_upd = 'display';
//
//										$default = false;
//										self::output_custom_field_setting_item( $field_type, $result_str);
//
//										//geodir_custom_sort_field_adminhtml( $field_type, $result_str, $field_ins_upd, $default );
//									}
//								}else{
//									_e("Select fields from the left to be able to add new sort options.","geodirectory");
//								}
								?>
							</ul>
						</div>
					</div>
					<div style="clear:both"></div>
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
		public static function loop_fields_output($tabs,$tab_id = ''){
			ob_start();

			if(!empty($tabs)){
				foreach($tabs as $key => $tab){

					if($tab_id && $tab->id!=$tab_id){
						continue;
					}elseif($tab_id && $tab->id==$tab_id && $tab->tab_level > 0){
						echo self::output_custom_field_setting_item($tab->id,$tab); break;
					}

					if($tab->tab_level=='1' ){continue;}


					$tab_rendered = self::output_custom_field_setting_item($tab->id,$tab);
					$tab_rendered = str_replace("</li>","",$tab_rendered);
					$child_tabs = '';
					foreach($tabs as $child_tab){
						if($child_tab->tab_parent==$tab->id){
							$child_tabs .= self::output_custom_field_setting_item($child_tab->id,$child_tab);
						}
					}

					if($child_tabs){
						$tab_rendered .= "<ul>";
						$tab_rendered .= $child_tabs;
						$tab_rendered .= "</ul>";
					}

					echo $tab_rendered;
					echo "</li>";

					unset($tabs[$key]);

				}
			}
			return ob_get_clean();
		}


		/**
		 * Get sort options based on post type.
		 *
		 * @since 1.0.0
		 * @package GeoDirectory
		 * @global object $wpdb WordPress Database object.
		 *
		 * @param string $post_type The post type.
		 *
		 * @return bool|mixed|void Returns sort options when post type available. Otherwise returns false.
		 */
		public static function custom_sort_options( $post_type = '' ) {

			global $wpdb;

			if ( $post_type != '' ) {

				$all_postypes = geodir_get_posttypes();

				if ( ! in_array( $post_type, $all_postypes ) ) {
					return false;
				}

				$fields = array();

				$fields['random'] = array(
					'post_type'      => $post_type,
					'data_type'      => '',
					'field_type'     => 'random',
					'frontend_title' => 'Random',
					'htmlvar_name'   => 'post_status',
					'field_icon'     => 'fas fa-random',
					'description'    => __( 'Random sort (not recommended for large sites)', 'geodirectory' )
				);

				$fields['datetime'] = array(
					'post_type'      => $post_type,
					'data_type'      => '',
					'field_type'     => 'datetime',
					'frontend_title' => __( 'Add date', 'geodirectory' ),
					'htmlvar_name'   => 'post_date',
					'field_icon'     => 'fas fa-calendar',
					'description'    => __( 'Sort by date added', 'geodirectory' )
				);
				$fields['bigint'] = array(
					'post_type'      => $post_type,
					'data_type'      => '',
					'field_type'     => 'bigint',
					'frontend_title' => __( 'Review', 'geodirectory' ),
					'htmlvar_name'   => 'comment_count',
					'field_icon'     => 'far fa-comment-dots',
					'description'    => __( 'Sort by the number of reviews', 'geodirectory' )
				);
				$fields['float'] = array(
					'post_type'      => $post_type,
					'data_type'      => '',
					'field_type'     => 'float',
					'frontend_title' => __( 'Rating', 'geodirectory' ),
					'htmlvar_name'   => 'overall_rating',
					'field_icon'     => 'fas fa-star',
					'description'    => __( 'Sort by the overall rating value', 'geodirectory' )
				);
				$fields['text'] = array(
					'post_type'      => $post_type,
					'data_type'      => '',
					'field_type'     => 'text',
					'frontend_title' => __( 'Title', 'geodirectory' ),
					'htmlvar_name'   => 'post_title',
					'field_icon'     => 'fas fa-sort-alpha-up',
					'description'    => __( 'Sort alphabetically by title', 'geodirectory' )
				);

				/**
				 * Hook to add custom sort options.
				 *
				 * @since 1.0.0
				 *
				 * @param array $fields Unmodified sort options array.
				 * @param string $post_type Post type.
				 */
				return $fields = apply_filters( 'geodir_add_custom_sort_options', $fields, $post_type );

			}

			return false;
		}

		/**
		 * Check if the field already exists.
		 *
		 * @param $field
		 *
		 * @return WP_Error
		 */
		public static function field_exists( $htmlvar_name, $post_type, $sort = 'asc' ) {
			global $wpdb;

			$check_html_variable = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT htmlvar_name FROM " . GEODIR_CUSTOM_SORT_FIELDS_TABLE . " WHERE htmlvar_name = %s AND post_type = %s AND sort = %s",
					array( $htmlvar_name, $post_type, $sort )
				)
			);

			return $check_html_variable;

		}

		/**
		 * Adds admin html for custom sorting fields.
		 *
		 * @since 1.0.0
		 * @package GeoDirectory
		 * @global object $wpdb WordPress Database object.
		 * @param string $field_type The form field type.
		 * @param object|int $result_str The custom field results object or row id.
		 * @param string $field_ins_upd When set to "submit" displays form.
		 * @param string $field_type_key The key of the custom field.
		 */
		public static function output_custom_field_setting_item($field_id = '',$field = '',$cf = array())
		{
			ob_start();
			// if field not provided get it
			if (!is_object($field) && $field_id) {
				global $wpdb;
				$field = $wpdb->get_row($wpdb->prepare("select * from " . GEODIR_CUSTOM_SORT_FIELDS_TABLE . " where id= %d", array($field_id)));
			}

			// if field template not provided get it
			if(empty($cf)){
				$cf_arr  = self::custom_sort_options($field->post_type);
				//$cf = (isset($cf_arr[$field->field_type])) ? $cf_arr[$field->field_type] : ''; // the field type
				if(!$cf){
					foreach ($cf_arr as $cf_temp){
						if(isset($field->htmlvar_name) && $cf_temp['htmlvar_name']==$field->htmlvar_name){
							$cf = $cf_temp;
							//$field = (object) array_merge((array) $field, (array) $cf_temp);
							$field = (object) array_merge((array) $cf_temp, (array)  $field);
							break;
						}
					}
				}
			}

			//print_r(print_r($field));



			$field = stripslashes_deep( $field );

//			print_r($field);
//			echo '###';
//			print_r($cf_arr);
//			echo '###';
//			print_r($cf );echo '####';exit;

			####################


			//$htmlvar_name = isset( $field_type_key ) ? $field_type_key : '';

			///print_r($field);

			$frontend_title = '';
			if ( $frontend_title == '' ) {
				$frontend_title = isset( $field->frontend_title ) ? $field->frontend_title : '';
			}

			if ( $frontend_title == '' ) {
				$frontend_title = isset( $cf['frontend_title'] ) ? $cf['frontend_title'] : '';
			}


			$nonce = wp_create_nonce( 'custom_fields_' . $field->id );

			if ( isset( $cf['field_icon'] ) && geodir_is_fa_icon( $cf['field_icon'] ) ) {
				$field_icon = '<i class="' . $cf['field_icon'] . '" aria-hidden="true"></i>';
			} elseif ( isset( $cf['field_icon'] ) && geodir_is_icon_url( $cf['field_icon'] ) ) {
				$field_icon = '<b style="background-image: url("' . $cf['field_icon'] . '")"></b>';
			} else {
				$field_icon = '<i class="fas fa-cog" aria-hidden="true"></i>';
			}

			$radio_id = ( isset( $field->htmlvar_name ) ) ? $field->htmlvar_name . $field->field_type : rand( 5, 500 );

			/**
			 * Contains custom field html.
			 *
			 * @since 2.0.0
			 */
			include( dirname( __FILE__ ) . '/../views/html-admin-settings-cpt-sorting-setting-item.php' );
			return ob_get_clean();

		}

		/**
		 * Get the sort order if not set.
		 *
		 * @return int
		 */
		public static function default_sort_order(){
			global $wpdb;
			$last_order = $wpdb->get_var("SELECT MAX(sort_order) as last_order FROM " . GEODIR_CUSTOM_SORT_FIELDS_TABLE);

			return (int)$last_order + 1;
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
			$field->post_type = isset( $input['post_type'] ) ? sanitize_text_field( $input['post_type'] ) : null;
			$field->field_type = isset( $input['field_type'] ) ? sanitize_text_field( $input['field_type'] ) : null;
			$field->field_id = isset( $input['field_id'] ) ? absint( $input['field_id'] ) : '';
			$field->data_type = isset( $input['data_type'] ) ? sanitize_text_field( $input['data_type'] ) : '';
			$field->htmlvar_name = isset( $input['htmlvar_name'] ) ? str_replace(array('-',' ','"',"'"), array('_','','',''), sanitize_title_with_dashes( $input['htmlvar_name'] ) ) : null;
			$field->frontend_title = isset( $input['frontend_title'] ) ? sanitize_text_field( $input['frontend_title'] ) : null;
			$field->sort = isset( $input['sort'] ) ? sanitize_text_field( $input['sort'] ) : 'asc';
			//$field->asc = isset( $input['asc'] ) ? absint( $input['asc'] ) : 0;
			//$field->asc_title = isset( $input['asc_title'] ) ? sanitize_text_field( $input['asc_title'] ) : $field->frontend_title." ASC";
			//$field->desc = isset( $input['desc'] ) ? absint( $input['desc'] ) : 0;
			//$field->desc_title = isset( $input['desc_title'] ) ? sanitize_text_field( $input['desc_title'] ) : $field->frontend_title." DESC";
			$field->is_active = isset( $input['is_active'] ) ? absint( $input['is_active'] ) : 0;
			$field->is_default = isset( $input['is_default'] ) && $input['is_default'] ? 1 : 0;
			//$field->default_order = isset( $input['default_order'] ) ? sanitize_text_field( $input['default_order'] ) : '';
			$field->sort_order = isset( $input['sort_order'] ) ? absint( $input['sort_order'] ) : self::default_sort_order();

			// Set some default after sanitation
			$field->data_type = self::sanitize_data_type($field);
			if(!$field->htmlvar_name){$field->htmlvar_name =str_replace(array('-',' ','"',"'"), array('_','','',''), sanitize_title_with_dashes( $input['frontend_title'] ) );} // we use original input so the special chars are no converted already

			// setup the default sort
			//if( !$field->default_order && $field->is_default ){$field->default_order = sanitize_text_field($input['is_default']);}

			return $field;

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
		 * Save the custom field.
		 *
		 * @param array $field
		 *
		 * @return int|string
		 */
		public static function save_custom_field( $field = array() ) {
			global $wpdb, $plugin_prefix;

			$field = self::sanatize_custom_field( $field );

			// Check field exists.
			$exists = self::field_exists($field->htmlvar_name,$field->post_type, $field->sort );

			if ( is_wp_error( $exists ) ) {
				return new WP_Error( 'failed', $exists->get_error_message() );
			} else if ( $exists && $field->field_id==='' ) {
				// Field id blank for dummy data and 0 for new
				return ''; // Return blank, probably dummy data being inserted.
			} else if ( $exists && $field->field_id === 0 ) {
				// Its new
				$exists = false;
			}

			// If this is set as the default blank all the others first just incase.
			if ( $field->is_default ) {
				self::blank_default( $field->post_type );
			}

			$db_data = array(
				'post_type' => $field->post_type,
				'data_type' => $field->data_type,
				'field_type' => $field->field_type,
				'frontend_title' => $field->frontend_title,
				'htmlvar_name' => $field->htmlvar_name,
				'sort_order' => $field->sort_order,
				'sort' => $field->sort,
				'is_active' => $field->is_active,
				'is_default' => $field->is_default,
			);

			$db_format = array(
				'%s', // post_type
				'%s', // data_type
				'%s', // field_type
				'%s', // frontend_title
				'%s', // htmlvar_name
				'%d', // sort_order
				'%s', // sort
				'%d', // is_active
				'%d', // is_default
			);

			if ( $exists ) {
				// Update the field settings.
				$result = $wpdb->update(
					GEODIR_CUSTOM_SORT_FIELDS_TABLE,
					$db_data,
					array( 'id' => $field->field_id ),
					$db_format
				);

				if ( $result === false ) {
					return new WP_Error( 'failed', __( "Field update failed.", "geodirectory" ) );
				}
			} else {
				// Insert the field settings.
				$result = $wpdb->insert(
					GEODIR_CUSTOM_SORT_FIELDS_TABLE,
					$db_data,
					$db_format
				);

				if ( $result === false ) {
					return new WP_Error( 'failed', __( "Field create failed.", "geodirectory" ) );
				} else {
					$field->field_id = $wpdb->insert_id;
				}
			}

			// Clear cache.
			self::clear_sort_cache( $field->post_type );

			/**
			 * Called after all custom sort fields are saved for a post.
			 *
			 * @since 1.0.0
			 * @param int $lastid The post ID.
			 */
			do_action( 'geodir_after_custom_sort_fields_updated', $field->field_id, $field );

			return $field->field_id;
		}

        /**
         * Blank all defaults for a post type.
         *
         * @since 2.0.0
         *
         * @global object $wpdb WordPress Database object.
         *
         * @param $post_type Post type value.
         */
		public static function blank_default($post_type){
			global $wpdb;

			// blank all first
			$wpdb->query( $wpdb->prepare( "UPDATE " . GEODIR_CUSTOM_SORT_FIELDS_TABLE . " SET is_default='0' WHERE post_type = %s", array( $post_type ) ) );
		}


		/**
		 * Delete a custom sort field using field id.
		 * @since 1.0.0
		 * @package GeoDirectory
		 * @global object $wpdb WordPress Database object.
		 * @global string $plugin_prefix Geodirectory plugin table prefix.
		 * @param string $field_id The field ID.
		 * @return int|string Returns field id when successful deletion, else returns 0.
		 */
		public static function delete_custom_field( $field_id = '' ) {
			global $wpdb;

			if ( $field_id != '' ) {
				$cf = trim( $field_id, '_' );

				$field = self::get_field( $cf );

				$wpdb->query( $wpdb->prepare( "DELETE FROM " . GEODIR_CUSTOM_SORT_FIELDS_TABLE . " WHERE id = %d OR tab_parent = %d", array( $cf, $cf ) ) );

				if ( ! empty( $field ) ) {
					// Clear cache.
					self::clear_sort_cache( $field->post_type );
				}

				return $field_id;
			} else {
				return 0;
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
		public function set_field_orders( $tabs = array() ) {
			global $wpdb;

			$count = 0;
			if ( ! empty( $tabs ) ) {
				$result = false;
				$field_id = 0;

				foreach ( $tabs as $index => $info ) {
					if ( empty( $field_id ) ) {
						$field_id = absint( $info['id'] );
					}

					$result = $wpdb->update(
						GEODIR_CUSTOM_SORT_FIELDS_TABLE,
						array( 'sort_order' => $index, 'tab_level' => $info['tab_level'], 'tab_parent' => $info['tab_parent'] ),
						array( 'id' => absint( $info['id'] ) ),
						array( '%d', '%d', '%d' )
					);
					$count ++;
				}

				if ( $result !== false ) {
					if ( $field_id && ( $field = self::get_field( $field_id ) ) ) {
						// Clear cache.
						self::clear_sort_cache( $field->post_type );
					}

					return true;
				} else {
					return new WP_Error( 'failed', __( "Failed to sort tab items.", "geodirectory" ) );
				}
			} else {
				return new WP_Error( 'failed', __( "Failed to sort tab items.", "geodirectory" ) );
			}
		}

		public static function get_field( $field_id ) {
			global $wpdb;

			if ( empty( $field_id ) ) {
				return array();
			}

			$field = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . GEODIR_CUSTOM_SORT_FIELDS_TABLE . " WHERE id = %d", array( (int) $field_id ) ) );

			return $field;
		}

		/**
		 * Clear the post type sorting cache.
		 *
		 * @since 2.2.4
		 *
		 * @param string $post_type The post type.
		 * @return mixed.
		 */
		public static function clear_sort_cache( $post_type ) {
			wp_cache_delete( "geodir_get_posts_default_sort_{$post_type}" );
			wp_cache_delete( "geodir_get_sort_options_{$post_type}" );
		}
	}

endif;

return new GeoDir_Settings_Cpt_Sorting();
