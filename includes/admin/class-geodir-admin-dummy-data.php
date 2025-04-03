<?php
/**
 * GeoDirectory class for adding dummy data.
 *
 * @author   AyeCode
 * @category Admin
 * @package  GeoDirectory/Admin
 * @version  2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * GeoDir_Admin_Dummy_Data Class.
 */
class GeoDir_Admin_Dummy_Data {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {

	}

	public static function create_sort_fields(){

	}

	/**
	 * Default taxonomies
	 *
	 * Adds the default terms for taxonomies - placecategory. Modify at your own risk.
	 *
	 * @since 2.0.0
	 * @package GeoDirectory
	 */
	public static function create_taxonomies( $post_type, $categories ) {
		$uploads = wp_upload_dir(); // Array of key => value pairs

		if ( ! empty( $categories ) ) {
			foreach( $categories as $slug => $category ) {
				// If term exists just continue to the next one
				if ( term_exists( $category['name'], $post_type . 'category' ) ) {
					continue;
				}

				$args = array();

				// Add parent id if present
				if ( ! empty( $category['parent-name'] ) ) {
					$parent = get_term_by( 'name', $category['parent-name'], $post_type . 'category' );

					if ( ! empty( $parent->term_id ) ) {
						$args['parent'] = absint( $parent->term_id );
					}
				}

				// Insert the category
				$category_return = wp_insert_term( $category['name'], $post_type . 'category', $args );

				if ( is_wp_error( $category_return ) ) {
					geodir_error_log( $category_return->get_error_message(), 'dummy_data', __FILE__, __LINE__ );
				} else {
					// Attach the meta data
					if ( isset( $category_return['term_id'] ) ) {
						// schema
						if ( ! empty( $category['schema_type'] ) ) {
							update_term_meta( $category_return['term_id'], 'ct_cat_schema', $category['schema_type'] );
						}

						// font icon
						if ( ! empty( $category['font_icon'] ) ) {
							update_term_meta( $category_return['term_id'], 'ct_cat_font_icon', $category['font_icon'] );
						}

						// color
						if ( ! empty( $category['color'] ) ) {
							update_term_meta( $category_return['term_id'], 'ct_cat_color', $category['color'] );
						}
					}

					if ( ( ! empty( $category['icon'] ) || ! empty( $category['default_img'] ) ) && ! empty( $category_return['term_id'] ) ) {
						if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
							require_once( ABSPATH . 'wp-admin/includes/image.php' );
						}

						// Temp allow svg
						add_filter( 'upload_mimes', function( $mimes ) {
							$mimes['svg'] = 'image/svg';

							return $mimes;
						}, 21, 1 );

						add_filter( 'wp_check_filetype_and_ext', function( $filetype_and_ext, $file, $filename, $mimes, $real_mime ) {
							if ( empty( $filetype_and_ext['ext'] ) && strpos( $filename, '.svg' ) !== false ) {
								$wp_filetype = wp_check_filetype( $filename, $mimes );

								if ( ! empty( $wp_filetype['ext'] ) && $wp_filetype['ext'] == 'svg' ) {
									$filetype_and_ext['ext'] = $wp_filetype['ext'];
									$filetype_and_ext['type'] = 'image/svg';
									$filetype_and_ext['proper_filename'] = ! empty( $wp_filetype['proper_filename'] ) ? $wp_filetype['proper_filename'] : $filename;
								}
							}

							return $filetype_and_ext;
						}, 21, 5 );

						add_filter( 'geodir_allowed_mime_types', function( $mime_types ) {
							$mime_types['Image']['svg'] = 'image/svg';

							return $mime_types;
						}, 21, 1 );

						// Allowed image types.
						$image_types = geodir_image_mime_types();

						// Attach the icon
						if ( ! empty( $category['icon'] ) ) {
							$uploaded = (array) GeoDir_Media::get_external_media( $category['icon'], '', $image_types );

							if ( empty( $uploaded['error'] ) && ! empty( $uploaded['file'] ) ) {
								$wp_filetype = wp_check_filetype( basename( $uploaded['file'] ), null );

								$attachment = array(
									'guid'           => $uploads['baseurl'] . '/' . basename( $uploaded['file'] ),
									'post_mime_type' => $wp_filetype['type'],
									'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $uploaded['file'] ) ),
									'post_content'   => '',
									'post_status'    => 'inherit'
								);

								$attach_id  = wp_insert_attachment( $attachment, $uploaded['file'] );
								$attach_data = wp_generate_attachment_metadata( $attach_id, $uploaded['file'] );

								if ( empty( $attach_data['file'] ) && isset( $wp_filetype['ext'] ) && 'svg' == $wp_filetype['ext'] ) {
									$attach_data['file'] = str_replace( $uploads['basedir'], '', $uploaded['file'] );
								}

								wp_update_attachment_metadata( $attach_id, $attach_data );

								if ( isset( $attach_data['file'] ) ) {
									update_term_meta( $category_return['term_id'], 'ct_cat_icon', array( 'id'  => $attach_id, 'src' => $attach_data['file'] ) );
								}
							}
						}

						// Attach category default image
						if ( ! empty( $category['default_img'] ) ) {
							$uploaded = (array) GeoDir_Media::get_external_media( $category['default_img'], '', $image_types );

							if ( empty( $uploaded['error'] ) && ! empty( $uploaded['file'] ) ) {
								$wp_filetype = wp_check_filetype( basename( $uploaded['file'] ), null );

								$attachment = array(
									'guid'           => $uploads['baseurl'] . '/' . basename( $uploaded['file'] ),
									'post_mime_type' => $wp_filetype['type'],
									'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $uploaded['file'] ) ),
									'post_content'   => '',
									'post_status'    => 'inherit'
								);

								$attach_id  = wp_insert_attachment( $attachment, $uploaded['file'] );
								$attach_data = wp_generate_attachment_metadata( $attach_id, $uploaded['file'] );

								if ( empty( $attach_data['file'] ) && isset( $wp_filetype['ext'] ) && 'svg' == $wp_filetype['ext'] ) {
									$attach_data['file'] = str_replace( $uploads['basedir'], '', $uploaded['file'] );
								}

								wp_update_attachment_metadata( $attach_id, $attach_data );

								if ( isset( $attach_data['file'] ) ) {
									update_term_meta( $category_return['term_id'], 'ct_cat_default_img', array( 'id'  => $attach_id, 'src' => $attach_data['file'] ) );
								}
							}
						}
					}
				}
			}

			if ( isset( $_REQUEST['data_type'] ) ) {
				// Set the dummy data type
				geodir_update_option( $post_type . '_dummy_data_type', sanitize_text_field( $_REQUEST['data_type'] ) );
			}

			// Rebuild the icon cache
			geodir_get_term_icon_rebuild();
		}

		return true;
	}

    /**
     * Create dummy fields.
     *
     * @since 2.0.0
     *
     * @param array $fields Dummy fields values.
     */
	public static function create_dummy_fields( $fields ) {

		/**
		 * Filter the array of default custom fields DB table data.
		 *
		 * @since 1.0.0
		 *
		 * @param string $fields The default custom fields as an array.
		 */
		$fields = apply_filters( 'geodir_before_dummy_custom_fields_saved', $fields );
		foreach ( $fields as $field_index => $field ) {
			geodir_custom_field_save( $field );

		}
	}

	/**
	 * Deletes GeoDirectory dummy data.
	 *
	 * @since 1.0.0
	 * @package GeoDirectory
	 * @global object $wpdb WordPress Database object.
	 * @global string $plugin_prefix Geodirectory plugin table prefix.
	 */
	public static function delete_dummy_posts( $post_type ) {
		global $wpdb, $plugin_prefix;

		$table = geodir_db_cpt_table( $post_type );

		$post_ids = $wpdb->get_results( "SELECT post_id FROM `" . $table . "` WHERE post_dummy = '1'" );

		if ( ! empty( $post_ids ) ) {
			foreach ( $post_ids as $post_ids_obj ) {
				wp_delete_post( (int) $post_ids_obj->post_id );
			}

			// Double check posts are deleted
			$wpdb->delete( $table, array( 'post_dummy' => '1' ) );
		}

		geodir_update_option( $post_type . '_dummy_data_type', '' );
	}

    /**
     * Add dummy address.
     *
     * @since 2.0.0
     *
     * @param array $post_info Optional. Post Information. Default array().
     * @return array $post_info.
     */
	public static function add_dummy_address($post_info = array()){
		global $city_bound_lat1, $city_bound_lng1, $city_bound_lat2, $city_bound_lng2,$geodirectory;

		$default_location = $geodirectory->location->get_default_location();
		if ( $city_bound_lat1 > $city_bound_lat2 ) {
			$dummy_post_latitude = geodir_random_float( geodir_random_float( $city_bound_lat1, $city_bound_lat2 ), geodir_random_float( $city_bound_lat2, $city_bound_lat1 ) );
		} else {
			$dummy_post_latitude = geodir_random_float( geodir_random_float( $city_bound_lat2, $city_bound_lat1 ), geodir_random_float( $city_bound_lat1, $city_bound_lat2 ) );
		}


		if ( $city_bound_lng1 > $city_bound_lng2 ) {
			$dummy_post_longitude = geodir_random_float( geodir_random_float( $city_bound_lng1, $city_bound_lng2 ), geodir_random_float( $city_bound_lng2, $city_bound_lng1 ) );
		} else {
			$dummy_post_longitude = geodir_random_float( geodir_random_float( $city_bound_lng2, $city_bound_lng1 ), geodir_random_float( $city_bound_lng1, $city_bound_lng2 ) );
		}

		$api = GeoDir_Maps::active_map();
		/**
		 * Filter the API used for Geocode service.
		 *
		 * @since 2.0.0.68
		 *
		 * @param string $api The API used for Geocode service.
		 */
		$api = apply_filters( 'geodir_post_address_from_gps_api', $api );

		if ( $api == 'osm' ) {
			$post_address = geodir_get_osm_address_by_lat_lan( $dummy_post_latitude, $dummy_post_longitude );
		} else {
			$post_address = geodir_get_address_by_lat_lan( $dummy_post_latitude, $dummy_post_longitude );
		}

		$postal_code = '';
		if ( ! empty( $post_address ) ) {
			if ( $api == 'osm' ) {
				$address     = ! empty( $post_address->formatted_address ) ? $post_address->formatted_address : '';
				$postal_code = ! empty( $post_address->address->postcode ) ? $post_address->address->postcode : '';
			} else {
				$addresses         = array();
				$addresses_default = array();

				foreach ( $post_address as $add_key => $add_value ) {
					if ( $add_key < 2 && ! empty( $add_value->long_name ) ) {
						$addresses_default[] = $add_value->long_name;
					}
					if ( $add_value->types[0] == 'postal_code' ) {
						$postal_code = $add_value->long_name;
					}
					if ( $add_value->types[0] == 'street_number' ) {
						$addresses[] = $add_value->long_name;
					}
					if ( $add_value->types[0] == 'route' ) {
						$addresses[] = $add_value->long_name;
					}
					if ( $add_value->types[0] == 'neighborhood' ) {
						$addresses[] = $add_value->long_name;
					}
					if ( $add_value->types[0] == 'sublocality' ) {
						$addresses[] = $add_value->long_name;
					}
				}
				$address = ! empty( $addresses ) ? implode( ', ', $addresses ) : ( ! empty( $addresses_default ) ? implode( ', ', $addresses_default ) : '' );
			}

			$post_info['street']   = ! empty( $address ) ? $address : $default_location->city;
			$post_info['city']      = $default_location->city;
			$post_info['region']    = $default_location->region;
			$post_info['country']   = $default_location->country;
			$post_info['zip']       = $postal_code;
			$post_info['latitude']  = $dummy_post_latitude;
			$post_info['longitude'] = $dummy_post_longitude;
		}else{
			$post_info['street']   = "123 ".$default_location->city;
			$post_info['city']      = $default_location->city;
			$post_info['region']    = $default_location->region;
			$post_info['country']   = $default_location->country;
			$post_info['zip']       = $postal_code;
			$post_info['latitude']  = $dummy_post_latitude;
			$post_info['longitude'] = $dummy_post_longitude;
		}

		return $post_info;
	}

	/**
	 * Inserts GeoDirectory dummy posts.
	 *
	 * @since 1.0.0
	 * @package GeoDirectory
	 * @global object $wpdb WordPress Database object.
	 * @global object $current_user Current user object.
	 */
	public static function create_dummy_posts( $request ) {

		global $city_bound_lat1, $city_bound_lng1, $city_bound_lat2, $city_bound_lng2,$dummy_post_index,$dummy_image_url,$plugin_prefix, $dummy_categories, $dummy_custom_fields, $dummy_posts,$dummy_sort_fields,$dummy_page_templates,$dummy_advanced_search_fields,$cpt_changes;

		$city_bound_lat1 = $request['city_bound_lat1'];
		$city_bound_lng1 = $request['city_bound_lng1'];
		$city_bound_lat2 = $request['city_bound_lat2'];
		$city_bound_lng2 = $request['city_bound_lng2'];
		$post_type = sanitize_key($request['post_type']);
		$item_index = absint($request['dummy_post_index']);
		$data_type = sanitize_key($request['data_type']);
		$update_templates = absint($request['update_templates']);

		ini_set( 'max_execution_time', 999999 ); // @codingStandardsIgnoreLine //300 seconds = 5 minutes
		$data_types = self::dummy_data_types($post_type);

		$total_count = 0;
		$dummy_post_index = $item_index;

		$dummy_categories = array();
		$dummy_custom_fields = array();
		$dummy_sort_fields = array();
		$dummy_posts = array();
		$dummy_page_templates = array();
		$dummy_advanced_search_fields = array();
		$dummy_image_url = '';
		$cpt_changes = array();
		foreach ( $data_types as $key => $val ) {
			if ( $key == $data_type ) {
				$total_count = $val['count'];
				if ( $key == 'standard_places' ) {
					/**
					 * Contains dummy post content.
					 *
					 * @since 1.0.0
					 * @package GeoDirectory
					 */
					include_once( 'dummy-data/standard_places.php' );
				} elseif ( $key == 'property_sale' ) {
					add_filter( 'geodir_extra_custom_fields', 'geodir_extra_custom_fields_' . $key, 10, 3 );

					/**
					 * Contains dummy property for sale post content.
					 *
					 * @since 1.6.11
					 * @package GeoDirectory
					 */
					include_once( 'dummy-data/property_sale.php' );
				} elseif ( $key == 'property_rent' ) {
					add_filter( 'geodir_extra_custom_fields', 'geodir_extra_custom_fields_' . $key, 10, 3 );

					/**
					 * Contains dummy property for sale post content.
					 *
					 * @since 1.6.11
					 * @package GeoDirectory
					 */
					include_once( 'dummy-data/property_rent.php' );
				}  elseif ( $key == 'classifieds' ) {
					add_filter( 'geodir_extra_custom_fields', 'geodir_extra_custom_fields_' . $key, 10, 3 );

					/**
					 * Contains dummy property for classifieds.
					 *
					 * @since 2.0.0.59
					 * @package GeoDirectory
					 */
					include_once( 'dummy-data/classifieds.php' );
				} elseif ( $key == 'freelancer' ) {
					add_filter( 'geodir_extra_custom_fields', 'geodir_extra_custom_fields_' . $key, 10, 3 );

					/**
					 * Contains dummy data for freelancers.
					 *
					 * @since 2.0.0.59
					 * @package GeoDirectory
					 */
					include_once( 'dummy-data/freelancer.php' );
				} elseif ( $key == 'job_board' ) {
					add_filter( 'geodir_extra_custom_fields', 'geodir_extra_custom_fields_' . $key, 10, 3 );

					/**
					 * Contains dummy data for freelancers.
					 *
					 * @since 2.0.0.59
					 * @package GeoDirectory
					 */
					include_once( 'dummy-data/job_board.php' );
				} else {
					do_action( 'geodir_dummy_data_include_file', $post_type, $data_type, $val, $item_index );
				}

				/**
				 * Variable representing dummy posts data.
				 *
				 * This variable holds an array of dummy posts data, often used for testing and development purposes.
				 *
				 * @var array $dummy_posts
				 * @since 2.3.45
				 */
				$dummy_posts = apply_filters( 'geodir_dummy_data_posts', $dummy_posts, $key );
			}

			$dummy_image_url = apply_filters( 'dummy_image_url', $dummy_image_url,$post_type, $data_type, $item_index );

			/**
			 * Fires action before each dummy data item.
			 *
			 * @since 2.0.0
			 *
			 */
			do_action( 'geodir_insert_dummy_data_loop', $post_type, $data_type, $item_index );
		}

		// Do the data insert
		if($dummy_post_index === 0){

			// insert the dummy data column
			geodir_add_column_if_not_exist( $plugin_prefix . $post_type . "_detail", 'post_dummy', "TINYINT(1) NULL DEFAULT '0'" );

			// insert custom fields
			if( !empty($dummy_custom_fields) ){
				foreach ($dummy_custom_fields as $field_index => $field) {
					geodir_custom_field_save($field);
				}
			}

			// insert sort fields
			if( !empty($dummy_sort_fields) ){
				foreach ($dummy_sort_fields as $field_index => $field) {
					GeoDir_Settings_Cpt_Sorting::save_custom_field($field);
				}
			}

			// Insert categories
			if ( ! empty( $dummy_categories ) ) {
				self::create_taxonomies( $post_type, $dummy_categories );
			}

			// maybe update templates
			if($update_templates && !empty($dummy_page_templates)){
				self::set_page_templates($post_type,$dummy_page_templates);
			}

			// Maybe insert advanced search filters
			if ( class_exists( 'GeoDir_Adv_Search_Settings_Cpt_Search' ) && ! empty( $dummy_advanced_search_fields ) ) {
				$as = new GeoDir_Adv_Search_Settings_Cpt_Search();
				foreach ( $dummy_advanced_search_fields as $advanced_search_field ) {
					$exists = $as::field_exists( $advanced_search_field['htmlvar_name'], $post_type );
					if ( ! $exists ) {
						$as::save_field( $advanced_search_field );
					}
				}
			}


			// maybe update CPT names
			if ( $update_templates && !empty($cpt_changes)) {
				self::update_cpt( $post_type, $cpt_changes );
			}

			return true;

		} else { // if index is not 0 then we are starting on posts.
			$post_index = $item_index - 1; // arrays start with 0

			if ( ! empty( $dummy_posts ) && isset( $dummy_posts[ $post_index ] ) ) {
				$post_info = $dummy_posts[ $post_index ];
				if ( GeoDir_Post_types::supports( $post_type, 'location' ) ) {
					$post_info = self::add_dummy_address( $post_info );
				}

				// Set the status to publish
				if ( isset( $post_info['post_dummy'] ) && $post_info['post_dummy'] && ! isset( $post_info['post_status'] ) ) {
					$post_info['post_status'] = 'publish';
				}

				// Set all images except first one to be external for speed
				if( !empty($post_info['post_images'] ) ){
					for ($i = 1; $i < count($post_info['post_images']); $i++) {
						$post_info['post_images'][$i] = '#' . $post_info['post_images'][$i];
					}
				}

				wp_insert_post( $post_info, true ); // we hook into the save_post hook
			}
		}

		// delete image cache on last entry
		if ( $total_count == $item_index ) {
			delete_transient( 'cached_dummy_images' );
			flush_rewrite_rules();
		}
	}

	/**
	 * @param $post_type
	 * @param $cpt_changes
	 *
	 * @return void
	 */
	public static function update_cpt( $post_type, $cpt_changes ) {
		$post_types = geodir_get_option('post_types', array());

		// bail if not a GD CPT
		if(empty($post_types[$post_type])){
			return;
		}

		$cpt_changes_escaped[$post_type]  = map_deep( $cpt_changes, 'sanitize_text_field' );

		if ( ! empty( $cpt_changes_escaped ) ) {
			$post_types = array_replace_recursive($post_types,$cpt_changes_escaped);
//			print_r($post_types);exit;
			//Update custom post types
			geodir_update_option( 'post_types', $post_types );
		}

	}

	/**
	 * Update the template content if set to do so.
	 *
	 * @param $post_type
	 * @param $page_templates
	 */
	public static function set_page_templates($post_type,$page_templates){
		if(!empty($page_templates)){
			foreach($page_templates as $page => $content){
				$page_id = 0;
				if($page=='archive_item'){
					$page_id = geodir_archive_item_page_id($post_type);
				}

				if($page_id && $content){
					$args = array(
						'ID'           => $page_id,
						'post_content' => $content
					);
					wp_update_post( $args );
				}
			}
		}
	}

	/**
	 * GeoDirectory dummy data installation.
	 *
	 * @since 1.0.0
	 * @package GeoDirectory
	 * @global object $wpdb WordPress Database object.
	 * @global string $plugin_prefix Geodirectory plugin table prefix.
	 *
	 * @param string $post_type The post type.
	 */
	public static function dummy_data_ui() {
		global $aui_bs5;
		wp_enqueue_script( 'jquery-ui-progressbar' );
		global $wpdb, $plugin_prefix,$geodirectory;

		if ( ! $geodirectory->location->is_default_location_set() ) {
			echo '<div class="updated fade"><p><strong>' . sprintf( __( 'Please %sclick here%s to set a default location, this will help to set location of all dummy data.', 'geodirectory' ), '<a href=\'' . admin_url( 'admin.php?page=geodirectory&tab=default_location_settings' ) . '\'>', '</a>' ) . '</strong></p></div>';
		} else {

			?>
			<table class="form-table gd-dummy-table table table-borderless table-sm gd-dummy-data">
				<tbody>
				<tr>
					<td><strong><?php _e( 'CPT', 'geodirectory' ); ?></strong></td>
					<td><strong><?php _e( 'Data Type', 'geodirectory' ); ?></strong></td>
					<td class="text-right text-end invisible"><strong><?php _e( 'Action', 'geodirectory' ); ?></strong></td>
				</tr>

				<?php

				$cpts = geodir_get_posttypes( 'array' );

				$nonce = wp_create_nonce( 'geodir_dummy_data' );


				$bs_select_class = $aui_bs5 ? 'form-select form-select-sm' : ' form-control form-control-sm';
				$data_tooltip = $aui_bs5 ? 'data-bs-toggle="tooltip"' : 'data-toggle="tooltip"';

				foreach ( $cpts as $post_type => $cpt ) {

					$data_types = self::dummy_data_types( $post_type );

					$set_dt = geodir_get_option( $post_type . '_dummy_data_type' );

					$set_dt = apply_filters( 'geodir_default_dummy_data_type', $set_dt, $post_type );

					$count = 30;

					if(geodir_column_exist($plugin_prefix . $post_type . "_detail", "post_dummy")){
						$post_counts = $wpdb->get_var( "SELECT count(post_id) FROM " . $plugin_prefix . $post_type . "_detail WHERE post_dummy='1'" );
					}else{
						$post_counts = 0;
					}


					echo "<tr>";
					echo "<td><strong>" . $cpt['labels']['name'] . "</strong></td>";


					$select_disabled = $post_counts > 0 ? 'disabled' : '';
					echo "<td class='d-flex flex-row mb-0'>";
					echo "<select title='".__( "Select the data type", "geodirectory" )."' $data_tooltip id='" . $post_type . "_data_type' onchange='geodir_dummy_set_count(this,\"$post_type\");' $select_disabled class='flex-fill $bs_select_class' style='min-width:180px'>";

					$c = 0;
					foreach ( $data_types as $key => $val ) {
						$c++;
						$selected = ( $key == $set_dt ) ? "selected='selected'" : '';
						if ( $selected || $c == 1 ) {
							$count = $val['count'];
						}
						echo "<option $selected value='$key' data-count='" . $val['count'] . "'>" . $val['name'] . "</option>";
					}
					echo "</select>";

					$select_display = $post_counts > 0 ? 'display:none;' : '';
					$select_display_bs5 = $aui_bs5 && $select_display ? 'display:none;' : '';
					$select_count_class_bs5 = $aui_bs5 ? 'mx-1' : '';
					echo $aui_bs5 ? '' : "<span title='".__( "Set the number of listings", "geodirectory" )."' data-toggle=\"tooltip\" class='gd-data-type-count flex-shrink-1 flex-fill ml-1 ms-1' style='$select_display'>";
					echo "<select id='" . $post_type . "_data_type_count' style='min-width:65px;$select_display_bs5 ' class='$bs_select_class gd-data-type-count $select_count_class_bs5' data-bs-toggle=\"tooltip\" data-bs-title='".__( "Set the number of listings", "geodirectory" )."'>";
					$x = 1;
					while ( $x <= $count ) {
						$selected = ( $x == $count ) ? "selected='selected'" : '';
						echo "<option $selected value='$x'>" . $x . "</option>";
						$x ++;
					}
					echo "</select>";
					echo $aui_bs5 ? '' : "</span>";

					// Page templates styles
					echo $aui_bs5 ? '' : "<span title='".__( "Overwrite the template designs to suit the data type", "geodirectory" )."' data-toggle=\"tooltip\" class='gd-data-type-templates ml-1 ms-1 flex-fill' style='$select_display'>";
					//echo "<label>";
//					echo "<input value='1' style='width: auto;height: 16px;' id='" . $post_type . "_data_type_templates' type='checkbox' name='gd-data-templates' checked />".__("(Update page templates)","geodirectory");
					echo "<select style='$select_display_bs5'  id='" . $post_type . "_data_type_templates' name='gd-data-templates' class='$bs_select_class gd-data-type-templates' data-bs-toggle=\"tooltip\" data-bs-title='".__( "Overwrite the template designs to suit the data type", "geodirectory" )."'>";
					echo "<option value='1'>".__("Update page templates","geodirectory")."</option>";
					echo "<option value='0'>".__("Do not update page templates","geodirectory")."</option>";
					echo "</select>";
					//echo "</label>";
					echo $aui_bs5 ? '' : "</span>";

					echo "</td>";


					if ( $post_counts > 0 ) {
						echo '<td class="text-right text-end"><input type="button" value="' . __( 'Remove posts', 'geodirectory' ) . '" class="btn btn-danger btn-sm geodir_dummy_button gd-remove-data" onclick="gdInstallDummyData(this,\'' . $nonce . '\',\'' . $post_type . '\'); return false;" ></td>';
					} else {
						echo '<td class="text-right text-end"><input type="button" value="' . __( 'Insert posts', 'geodirectory' ) . '" class="btn btn-primary btn-sm geodir_dummy_button" onclick="gdInstallDummyData(this,\'' . $nonce . '\',\'' . $post_type . '\'); return false;" ></td>';
					}

					echo "</tr>";
					//print_r($cpt);
				}

				?>
				</tbody>
			</table>
			<?php


			$default_location = $geodirectory->location->get_default_location();

			//echo '###';print_r($default_location );
			$city           = isset( $default_location->city ) ? $default_location->city : '';
			$region         = isset( $default_location->region ) ? $default_location->region : '';
			$country        = isset( $default_location->country ) ? $default_location->country : '';
			$city_latitude  = ! empty( $default_location->latitude ) ? $default_location->latitude : '""';
			$city_longitude = ! empty( $default_location->longitude ) ? $default_location->longitude : '""';
			?>
			<script type="text/javascript">

				/**
				 * Prevent navigation away if installing dummy data.
				 */
				var geodir_installing_dummy_data = false;
				window.onbeforeunload = function() {
					return geodir_installing_dummy_data  ? "<?php esc_attr_e( 'Dummy data has not fully installed yet!', 'geodirectory' ); ?>" : null;
				};

				function geodir_dummy_set_count(data, cpt) {

					var dateTypeCount = jQuery(data).find(':selected').data('count');

					var optionsAsString = "";
					for (var i = 0; i < dateTypeCount; i++) {
						var v = i + 1;
						var selected = v == dateTypeCount ? 'selected' : '';
						optionsAsString += "<option value='" + v + "' " + selected + ">" + v + "</option>";
					}
					jQuery('#' + cpt + '_data_type_count').empty().append(optionsAsString);

				}

				var CITY_ADDRESS = '<?php echo addslashes( $city . ',' . $region . ',' . $country );?>';
				var bound_lat_lng;
				var latlng = ['<?php echo $city_latitude; ?>', <?php echo $city_longitude; ?>];
				var lat = <?php echo $city_latitude; ?>;
				var lng = <?php echo $city_longitude; ?>;

				jQuery(document).ready(function () {
					var geocoder = window.gdMaps == 'google' ? new google.maps.Geocoder() : null;
					if (window.gdMaps == 'google') {
						console.log('gmaps');
						latlng = new google.maps.LatLng(lat, lng);

						geocoder.geocode({'address': CITY_ADDRESS},
							function (results, status) {
								if (status == google.maps.GeocoderStatus.OK) {
									// Bounds for North America
									if (results[0].geometry.bounds == null) {
										bound_lat_lng1 = String(results[0].geometry.viewport.getSouthWest());
										bound_lat_lng1 = bound_lat_lng1.replace(/[()]/g, "");
										bound_lat_lng2 = String(results[0].geometry.viewport.getNorthEast());
										bound_lat_lng2 = bound_lat_lng2.replace(/[()]/g, "");
										bound_lat_lng2 = bound_lat_lng1 + "," + bound_lat_lng2;
										bound_lat_lng = bound_lat_lng2.split(',');
									} else {
										bound_lat_lng = String(results[0].geometry.bounds);
										bound_lat_lng = bound_lat_lng.replace(/[()]/g, "");
										bound_lat_lng = bound_lat_lng.split(',');
									}

									bound_lat_lng = bound_lat_lng.map(function (x) {
										return x.replace(" ", '');
									}); // remove spaces from lat/lon
								} else {
									alert("<?php _e( 'Geocode was not successful for the following reason:', 'geodirectory' );?> " + status);
								}
							});
					} else if (window.gdMaps == 'osm') {
						console.log('osm');
						latlng = L.latLng(lat, lng);

						geocodePositionOSM(false, CITY_ADDRESS, false, false, function (geodata) {
							if (typeof geodata == 'object' && geodata.boundingbox) {
								bound_lat_lng = [geodata.boundingbox[0], geodata.boundingbox[2], geodata.boundingbox[1], geodata.boundingbox[3]];
							} else {
								geocodePositionOSM(latlng, false, false, false, function (geodata) {
									if (typeof geodata == 'object' && geodata.boundingbox) {
										bound_lat_lng = [geodata.boundingbox[0], geodata.boundingbox[2], geodata.boundingbox[1], geodata.boundingbox[3]];
									}
								});
							}
						});
					}
				});

				var dummy_post_index = 0;

				function gdRemoveDummyData(obj, nonce, posttype) {
					if (confirm('<?php _e( 'Are you sure you want to delete dummy data?', 'geodirectory' ); ?>')) {
						jQuery(obj).prop('disabled', true);
						jQuery('.gd-dummy-data-results-' + posttype).remove();
						jQuery('<tr class="gd-dummy-data-results gd-dummy-data-results-' + posttype + '" >' +
							'<td colspan="3">' +
							'<div class="gd_progressbar_container_' + posttype + '  progress" style="height: 2em;">' +
							'<div id="gd_progressbar_dummy" class="gd_progressbar_' + posttype + ' progress-bar bg-success progress-bar-striped progress-bar-animated" style="height: 2em;"></div>' +
							'<div class="gd-dummy-progress-label w-100 text-center text-dark position-absolute" style="left:0;height: 2em;padding-top: 5px;"></div>' +
							'</div>' +
							'</td>' +
							'</tr>').insertAfter(jQuery(obj).parents('tr'));

//						jQuery('.gd_progressbar_' + posttype).progressbar({value: 0});



						jQuery('.gd_progressbar_container_' + posttype + ' .progress-bar' ).width(0).parent().find('.gd-dummy-progress-label').html(  '<i class="fas fa-circle-notch fa-spin" aria-hidden="true"></i> <?php echo esc_attr( __( 'Removing data...', 'geodirectory' ) );?>');




						var data = {
							'action':           'geodir_delete_dummy_data',
							'security':         '<?php echo $nonce;?>',
							'post_type':        posttype
						};
						jQuery.post(ajaxurl,
							data,
							function (data) {
								geodir_installing_dummy_data = false;
								jQuery('.gd_progressbar_container_' + posttype + ' .progress-bar' ).removeClass('progress-bar-striped progress-bar-animated').width('100%').parent().find('.gd-dummy-progress-label').html( '<i class="fas fa-check" aria-hidden="true"></i> <?php echo esc_attr( __( 'Complete!', 'geodirectory' ) );?>');
								jQuery(obj).removeClass('gd-remove-data btn-danger').addClass('btn-primary');
								jQuery(obj).val('<?php esc_attr_e( 'Insert posts', 'geodirectory' );?>');
								jQuery(obj).prop('disabled', false);
								jQuery('#' + posttype + '_data_type_count.gd-data-type-count').show();
								jQuery('#' + posttype + '_data_type_templates.gd-data-type-templates').show();
								jQuery('#' + posttype + '_data_type_count').closest('.gd-data-type-count').show();
								jQuery('#' + posttype + '_data_type_templates').closest('.gd-data-type-templates').show();
								jQuery('#' + posttype + '_data_type_count').prop('disabled', false);
								jQuery('#' + posttype + '_data_type').prop('disabled', false);
								geodir_dummy_set_count(jQuery('#' + posttype + '_data_type'), posttype);
							});
						return true;
					}
				}


				function gdInstallDummyData(obj, nonce, posttype, insertedCount) {

					geodir_installing_dummy_data = true;

					if (jQuery(obj).hasClass('gd-remove-data')) {
						gdRemoveDummyData(obj, nonce, posttype);
						return;
					}

					jQuery(obj).prop('disabled', true);
					jQuery('#' + posttype + '_data_type').prop('disabled', true);
					jQuery('#' + posttype + '_data_type_count').prop('disabled', true);
					jQuery('#' + posttype + '_data_type_count.gd-data-type-count').hide();
					jQuery('#' + posttype + '_data_type_count').closest('.gd-data-type-count').hide();
					jQuery('#' + posttype + '_data_type_templates').closest('.gd-data-type-templates').hide();


					if (insertedCount == 0) {
						insertedCount++;
						jQuery('.gd-dummy-data-results-' + posttype).remove();
					}else if (!insertedCount) {
						insertedCount = 0;
						jQuery('.gd-dummy-data-results-' + posttype).remove();
					}else{
						insertedCount++;
					}


					var active_tab = jQuery(obj).closest('form').find('dl dd.gd-tab-active').attr('id');
					var dateType = jQuery('#' + posttype + '_data_type').val();
					//var dateTypeCount = jQuery('#'+posttype+'_data_type').find(':selected').data('count');
					var dateTypeCount = jQuery('#' + posttype + '_data_type_count').val();
					var dateTypeTemplates = jQuery('#' + posttype + '_data_type_templates').val();

					var result_container = jQuery('.gd-dummy-data-results-' + posttype);
					if (!result_container.length) {

						jQuery('<tr class="gd-dummy-data-results gd-dummy-data-results-' + posttype + '" >' +
							'<td colspan="3">' +
							'<div class="gd_progressbar_container_' + posttype + '  progress" style="height: 2em;">' +
							'<div id="gd_progressbar_dummy" class="gd_progressbar_' + posttype + ' progress-bar bg-success progress-bar-striped progress-bar-animated" style="height: 2em;"></div>' +
							'<div class="gd-dummy-progress-label w-100 text-center text-dark position-absolute" style="left:0;height: 2em;padding-top: 5px;"></div>' +
							'</div>' +
							'</td>' +
							'</tr>').insertAfter(jQuery(obj).parents('tr'));

//						jQuery('.gd_progressbar_' + posttype).progressbar({value: 0});

						jQuery('.gd_progressbar_container_' + posttype + ' .progress-bar' ).width(0).parent().find('.gd-dummy-progress-label').html( '0% (0 / ' + dateTypeCount + ') <i class="fas fa-circle-notch fa-spin" aria-hidden="true"></i> <?php echo esc_attr( __( 'Creating categories and custom fields...', 'geodirectory' ) );?>');
					}

					if (!(typeof bound_lat_lng == 'object' && bound_lat_lng.length == 4)) {
						bound_lat_lng = ['<?php echo $city_latitude; ?>', <?php echo $city_longitude; ?>, '<?php echo $city_latitude; ?>', <?php echo $city_longitude; ?>];
					}

//					if(!insertedCount){
//						var dummy_post_index = 0;
//					}else{
//						var dummy_post_index = insertedCount;
//						dummy_post_index++;
//					}


					var data = {
						'action':           'geodir_insert_dummy_data',
						'security':         '<?php echo $nonce;?>',
						'data_type':        dateType,
						'post_type':        posttype,
						'dummy_post_index': insertedCount,
						'update_templates': dateTypeTemplates,
						'city_bound_lat1':  bound_lat_lng[0],
						'city_bound_lng1':  bound_lat_lng[1],
						'city_bound_lat2':  bound_lat_lng[2],
						'city_bound_lng2':  bound_lat_lng[3]
					};
					jQuery.post(ajaxurl,
						data,
						function (data) {
						var percentage = 0;

						if (insertedCount < dateTypeCount) {
							//insertedCount++;
							var percentage = Math.round((insertedCount / dateTypeCount ) * 100);
							percentage = percentage > 100 ? 100 : percentage;

							jQuery('.gd_progressbar_container_' + posttype + ' .progress-bar' ).width(percentage+"%").parent().find('.gd-dummy-progress-label').html( percentage + '% (' + insertedCount + ' / ' + dateTypeCount + ') <i class="fas fa-circle-notch fa-spin" aria-hidden="true"></i> <?php echo esc_attr( __( 'Inserting data...', 'geodirectory' ) );?>');
							console.log(insertedCount);
							gdInstallDummyData(obj, nonce, posttype, insertedCount);
						}
						else {
							geodir_installing_dummy_data = false;
							percentage = 100;
							jQuery('.gd_progressbar_container_' + posttype + ' .progress-bar' ).removeClass('progress-bar-striped progress-bar-animated').width(percentage+"%").parent().find('.gd-dummy-progress-label').html( percentage + '% (' + insertedCount + ' / ' + dateTypeCount + ') <i class="fas fa-check" aria-hidden="true"></i> <?php echo esc_attr( __( 'Complete!', 'geodirectory' ) );?>');
							jQuery(obj).removeClass('btn-primary').addClass('gd-remove-data btn-danger');
							jQuery(obj).val('<?php esc_attr_e( 'Remove posts', 'geodirectory' );?>');
							jQuery(obj).prop('disabled', false);

						}
					});

				}
			</script>
			<?php
		}
	}

	/**
	 * The types of dummy data available.
	 *
	 * @return array
	 */
	public static function dummy_data_types( $post_type = 'gd_place' ) {
		$data = array(
			'standard_places' => array(
				'name'  => __( 'Default', 'geodirectory' ),
				'count' => 30
			),
			'property_sale'   => array(
				'name'  => __( 'Property for sale', 'geodirectory' ),
				'count' => 10
			),
			'property_rent'   => array(
				'name'  => __( 'Property for rent', 'geodirectory' ),
				'count' => 10
			),
			'classifieds'   => array(
				'name'  => __( 'Classifieds', 'geodirectory' ),
				'count' => 20,
				'has_templates' => true
			),
			'job_board'   => array(
				'name'  => __( 'Job Board', 'geodirectory' ),
				'count' => 20,
				'has_templates' => true
			),
//            'freelancer'   => array(
//                'name'  => __( 'Freelancer', 'geodirectory' ),
//                'count' => 20,
//                'has_templates' => true
//            )
		);

		return apply_filters( 'geodir_dummy_data_types', $data, $post_type );
	}

	/**
	 * The extra fields we use for dummy data.
	 *
	 * @param string $post_type
	 * @param string $package_id
	 *
	 * @return array
	 */
	public static function extra_custom_fields($post_type='gd_place',$package_id=''){
		$fields = array();
		$package = ($package_id=='') ? '' : array($package_id);

		$fields[] = array('post_type' => $post_type,
		                  'data_type' => 'VARCHAR',
		                  'field_type' => 'phone',
		                  'admin_title' => __('Phone', 'geodirectory'),
		                  'frontend_desc' => __('You can enter phone number,cell phone number etc.', 'geodirectory'),
		                  'frontend_title' => __('Phone', 'geodirectory'),
		                  'htmlvar_name' => 'phone',
		                  'default_value' => '',
		                  'is_active' => '1',
		                  'option_values' => '',
		                  'is_default' => '0',
		                  'show_in' =>  '[detail],[mapbubble]',
		                  'show_on_pkg' => $package,
		                  'clabels' => __('Phone', 'geodirectory'));

		$fields[] = array('post_type' => $post_type,
		                  'data_type' => 'VARCHAR',
		                  'field_type' => 'email',
		                  'admin_title' => __('Email', 'geodirectory'),
		                  'frontend_desc' => __('You can enter your business or listing email.', 'geodirectory'),
		                  'frontend_title' => __('Email', 'geodirectory'),
		                  'htmlvar_name' => 'email',
		                  'is_active' => '1',
		                  'default_value' => '',
		                  'option_values' => '',
		                  'is_default' => '0',
		                  'show_in' => '[detail]',
		                  'show_on_pkg' => $package,
		                  'clabels' => __('Email', 'geodirectory'));

		$fields[] = array('post_type' => $post_type,
		                  'data_type' => 'TEXT',
		                  'field_type' => 'url',
		                  'admin_title' => __('Website', 'geodirectory'),
		                  'frontend_desc' => __('You can enter your business or listing website.', 'geodirectory'),
		                  'frontend_title' => __('Website', 'geodirectory'),
		                  'htmlvar_name' => 'website',
		                  'default_value' => '',
		                  'is_active' => '1',
		                  'option_values' => '',
		                  'is_default' => '0',
		                  'show_in' => '[detail]',
		                  'show_on_pkg' => $package,
		                  'clabels' => __('Website', 'geodirectory'));

		$fields[] = array('post_type' => $post_type,
		                  'data_type' => 'TEXT',
		                  'field_type' => 'url',
		                  'admin_title' => __('X', 'geodirectory'),
		                  'frontend_desc' => __('You can enter your business or listing X url.', 'geodirectory'),
		                  'frontend_title' => __('X', 'geodirectory'),
		                  'htmlvar_name' => 'twitter',
		                  'default_value' => '',
		                  'is_active' => '1',
		                  'option_values' => '',
		                  'is_default' => '0',
		                  'show_in' => '[detail]',
		                  'show_on_pkg' => $package,
		                  'clabels' => __('X', 'geodirectory'));

		$fields[] = array('post_type' => $post_type,
		                  'data_type' => 'TEXT',
		                  'field_type' => 'url',
		                  'admin_title' => __('Facebook', 'geodirectory'),
		                  'frontend_desc' => __('You can enter your business or listing facebook url.', 'geodirectory'),
		                  'frontend_title' => __('Facebook', 'geodirectory'),
		                  'htmlvar_name' => 'facebook',
		                  'default_value' => '',
		                  'is_active' => '1',
		                  'option_values' => '',
		                  'is_default' => '0',
		                  'show_in' => '[detail]',
		                  'show_on_pkg' => $package,
		                  'clabels' => __('Facebook', 'geodirectory'));

		$fields[] = array('post_type' => $post_type,
		                  'data_type' => 'TEXT',
		                  'field_type' => 'textarea',
		                  'admin_title' => __('Video', 'geodirectory'),
		                  'frontend_desc' => __('Add video code here, YouTube etc.', 'geodirectory'),
		                  'frontend_title' => __('Video', 'geodirectory'),
		                  'htmlvar_name' => 'video',
		                  'default_value' => '',
		                  'is_active' => '1',
		                  'option_values' => '',
		                  'is_default' => '0',
		                  'show_in' => '[owntab]',
		                  'show_on_pkg' => $package,
		                  'clabels' => __('Video', 'geodirectory'));

		$fields[] = array('post_type' => $post_type,
		                  'data_type' => 'TEXT',
		                  'field_type' => 'textarea',
		                  'admin_title' => __('Special Offers', 'geodirectory'),
		                  'frontend_desc' => __('Note: List out any special offers (optional)', 'geodirectory'),
		                  'frontend_title' => __('Special Offers', 'geodirectory'),
		                  'htmlvar_name' => 'special_offers',
		                  'default_value' => '',
		                  'is_active' => '1',
		                  'option_values' => '',
		                  'is_default' => '0',
		                  'show_in' => '[owntab]',
		                  'show_on_pkg' => $package,
		                  'clabels' => __('Special Offers', 'geodirectory'));


		/**
		 * Filter the array of default custom fields DB table data.
		 *
		 * @since 1.6.6
		 * @param string $fields The default custom fields as an array.
		 */
		$fields = apply_filters('geodir_extra_custom_fields', $fields, $post_type, $package_id);

		return  $fields;
	}

	/**
	 * Get the default custom fields used for every CPT.
	 *
	 * @param string $post_type
	 * @param string $package_id
	 *
	 * @return array
	 */
	public static function default_custom_fields($post_type='gd_place',$package_id=''){
		$fields = array();
		$package = ($package_id=='') ? '' : array($package_id);

		$post_type_info = geodir_get_posttype_info($post_type);

		$cpt_singular_name = (isset($post_type_info['labels']['singular_name']) && $post_type_info['labels']['singular_name']) ? __($post_type_info['labels']['singular_name'], 'geodirectory') : __('Listing','geodirectory');


		$fields[] = array('post_type' => $post_type,
		                  'data_type' => 'VARCHAR',
		                  'field_type' => 'text',
		                  'admin_title' => __('Title', 'geodirectory'),
		                  'frontend_desc' => __('Enter the title.', 'geodirectory'),
		                  'frontend_title' => sprintf( __('%s Title', 'geodirectory'), $cpt_singular_name ),
		                  'htmlvar_name' => 'post_title',
		                  'default_value' => '',
		                  'option_values' => '',
		                  'is_default' => '1',
		                  'is_active' => '1',
		                  'is_required' => '1',
		                  'show_in' =>  '[mapbubble]',
		                  'show_on_pkg' => $package,
		                  'field_icon' => 'fas fa-minus',
		                  'clabels' => __('Title', 'geodirectory'));

		$fields[] = array('post_type' => $post_type,
		                  'data_type' => 'TEXT',
		                  'field_type' => 'textarea',
		                  'admin_title' => __('Description', 'geodirectory'),
		                  'frontend_desc' => __('Enter a description', 'geodirectory'),
		                  'frontend_title' => sprintf( __('%s Description', 'geodirectory'), $cpt_singular_name ),
		                  'htmlvar_name' => 'post_content',
		                  'default_value' => '',
		                  'option_values' => '',
		                  'is_default' => '1',
		                  'is_active' => '1',
		                  'is_required' => '1',
		                  'show_in' => '',
		                  'show_on_pkg' => $package,
		                  'field_icon' => 'fas fa-minus',
		                  'clabels' => __('Description', 'geodirectory'));

		$fields[] = array('post_type' => $post_type,
		                  'data_type' => 'TEXT',
		                  'field_type' => 'tags',
		                  'admin_title' => __('Tags', 'geodirectory'),
		                  'frontend_desc' => __('Tags are short keywords, with no space within.(eg: tag1, tag2, tag3).', 'geodirectory'),
		                  'frontend_title' => __('Tags', 'geodirectory'),
		                  'htmlvar_name' => 'post_tags',
		                  'default_value' => '',
		                  'is_default' => '1',
		                  'is_required' => '0',
		                  'is_active' => '1',
		                  'show_in'   =>  '[detail]',
		                  'show_on_pkg' => $package,
		                  'field_icon' => 'fas fa-tags',
		                  'clabels' => __('Tags', 'geodirectory'));

		$fields[] = array('post_type' => $post_type,
		                  'data_type' => 'VARCHAR',
		                  'field_type' => 'categories',
		                  'admin_title' => __('Category', 'geodirectory'),
		                  'frontend_desc' => __('SELECT listing category FROM here. SELECT at least one CATEGORY', 'geodirectory'),
		                  'frontend_title' => __('Category', 'geodirectory'),
		                  'htmlvar_name' => 'post_category',
		                  'default_value' => '',
		                  'is_default' => '1',
		                  'is_required' => '1',
		                  'is_active' => '1',
		                  'show_in'   =>  '[detail]',
		                  'show_on_pkg' => $package,
		                  'field_icon' => 'fas fa-folder-open',
		                  'clabels' => __('Category', 'geodirectory'),
						  'extra' => array(
							'cat_display_type' => 'multiselect'
						  )
					);

		$fields[] = array('post_type' => $post_type,
		                  'data_type' => 'VARCHAR',
		                  'field_type' => 'address',
		                  'admin_title' => __('Address', 'geodirectory'),
		                  'frontend_desc' => __('Please enter the listing street address. eg. : 230 Vine Street', 'geodirectory'),
		                  'frontend_title' => __('Address', 'geodirectory'),
		                  'htmlvar_name' => 'address',
		                  'default_value' => '',
		                  'option_values' => '',
		                  'is_default' => '1',
		                  'is_active' => '1',
		                  'is_required' => '1',
		                  'show_in'   =>  '[detail],[mapbubble]',
		                  'show_on_pkg' => $package,
		                  'required_msg' => __('Address fields are required', 'geodirectory'),
		                  'clabels' => __('Address', 'geodirectory'),
		                  'field_icon' => 'fas fa-map-marker-alt',
		                  'extra' => array('show_city' => 1, 'city_lable' => __('City', 'geodirectory'),
		                                   'show_region' => 1, 'region_lable' => __('Region', 'geodirectory'),
		                                   'show_country' => 1, 'country_lable' => __('Country', 'geodirectory'),
		                                   'show_zip' => 1, 'zip_lable' => __('Zip/Post Code', 'geodirectory'),
		                                   'show_map' => 1, 'map_lable' => __('Set Address On Map', 'geodirectory'),
		                                   'show_mapview' => 1, 'mapview_lable' => __('Select Map View', 'geodirectory'),
		                                   'show_mapzoom' => 1, 'mapzoom_lable' => 'hidden',
		                                   'show_latlng' => 1));

		$fields[] = array('post_type' => $post_type,
		                  'data_type' => 'TEXT',
		                  'field_type' => 'images',
		                  'admin_title' => __('Images', 'geodirectory'),
		                  'frontend_desc' => __('You can upload more than one image to create a image gallery on the details page.', 'geodirectory'),
		                  'frontend_title' => __('Images', 'geodirectory'),
		                  'htmlvar_name' => 'post_images',
		                  'default_value' => '',
		                  'option_values' => '',
		                  'is_default' => '1',
		                  'is_active' => '1',
		                  'is_required' => '0',
		                  'show_in'   =>  '',
		                  'show_on_pkg' => $package,
		                  'clabels' => __('Images', 'geodirectory'),
		                  'field_icon' => 'far fa-image');



		/**
		 * Filter the array of default custom fields DB table data.
		 *
		 * @since 1.6.6
		 * @param string $fields The default custom fields as an array.
		 */
		$fields = apply_filters('geodir_default_custom_fields', $fields, $post_type, $package_id);

		return  $fields;
	}

	/**
	 * Insert our dummy widgets.
	 *
	 * @param $sidebar_id
	 *
	 * @return string|void|WP_Error
	 */
	public static function insert_widgets($sidebar_id, $type = ''){

		$sidebar_id = sanitize_title_with_dashes($sidebar_id);

		// confirm the sidebar_id is valid
		if(empty($sidebar_id) || !array_key_exists($sidebar_id,$GLOBALS['wp_registered_sidebars'])){
			return new WP_Error( 'gd-dummy-widgets-insert', __( "The sidebar id is not valid.", "geodirectory" ) );
		}

		if($type == 'top'){
			$widgets = self::get_dummy_widgets('top');
		}else{
			$widgets = self::get_dummy_widgets();
		}
		$widgets = array_reverse($widgets);// flip them as we add them to the start one by one
		$sidebars_widgets = get_option( 'sidebars_widgets', array() );
		$inserted = 0;
		$exist = 0;

		if(!empty($widgets)){
			foreach($widgets as $widget_id => $widget_data){

				// only add if not already there:
				if(isset($sidebars_widgets[$sidebar_id]) && !empty($sidebars_widgets[$sidebar_id])){
					foreach($sidebars_widgets[$sidebar_id] as $current_widget_id){
						if(strpos($current_widget_id, $widget_id) !== false){
							// it already exists so continue
							$exist++; continue 2;
						}
					}
				}

				self::insert_widget_in_sidebar( $widget_id, $widget_data, $sidebar_id );
				$inserted++;
			}
		}

		if($inserted == 0 && $exist > 0){
			return __( 'Widgets already exist, none added.' , 'geodirectory' );
		}elseif($inserted > 0){
			return __( 'Widgets inserted' , 'geodirectory' );
		}else{
			return __( 'Something went wrong and no inserted, you can do this manually in Appearance > Widgets' , 'geodirectory' );
		}

	}

	/**
	 * The dummy widgets we want to install.
	 *
	 * @return mixed|void
	 */
	public static function get_dummy_widgets($type = ''){

		if($type=='top'){
			$widgets = array(
				// show map
				'gd_map' => array(
					'width' => '100%',
					'height' => '425px',
					'maptype' => 'ROADMAP',
					'zoom' => '0',
					'map_type' => 'auto',
					'map_directions' => '0',
					'gd_wgt_showhide'   => 'show_on',
					'gd_wgt_restrict'   => array('gd-pt','gd-search','gd-listing','gd-location'),
				),
				// show GD search
				'gd_search' => array(
					'gd_wgt_showhide'   => 'show_on',
					'gd_wgt_restrict'   => array('gd-pt','gd-search','gd-listing','gd-location'),
				),

			);
		}else{
			$widgets = array(
				// show the author action on the details sidebar
				'gd_author_actions' => array(
					'hide_edit'          => false,
					'hide_delete'          => false,
					'gd_wgt_showhide'   => 'show_on',
					'gd_wgt_restrict'   => array('gd-detail'),
				),
				// show details sidebar
				'gd_output_location' => array(
					'location'          => '[detail]',
					'gd_wgt_showhide'   => 'show_on',
					'gd_wgt_restrict'   => array('gd-detail'),
				),
				// show map
				'gd_map' => array(
					'width' => '100%',
					'height' => '425px',
					'maptype' => 'ROADMAP',
					'zoom' => '0',
					'map_type' => 'auto',
					'map_directions' => '1',
					'gd_wgt_showhide'   => 'show_on',
					'gd_wgt_restrict'   => array('gd-detail','gd-author','gd-pt','gd-search','gd-listing'),
				),
				// show GD Dashboard
				'gd_dashboard' => array(
					'dashboard_title'   => __('GD Dashboard','geodirectory'),
					'show_login'        => true,
					'login_title'        => __('Login','geodirectory'),
					'gd_wgt_showhide'   => 'show',
					'gd_wgt_restrict'   => array(),
				),

			);
		}

		return apply_filters('geodir_dummy_widgets',$widgets, $type);
	}

	/**
	 * Insert a widget in a sidebar.
	 *
	 * @param string $widget_id   ID of the widget (search, recent-posts, etc.)
	 * @param array $widget_data  Widget settings.
	 * @param string $sidebar     ID of the sidebar.
	 */
	public static function insert_widget_in_sidebar( $widget_id, $widget_data, $sidebar ) {
		// Retrieve sidebars, widgets and their instances
		$sidebars_widgets = get_option( 'sidebars_widgets', array() );
		$widget_instances = get_option( 'widget_' . $widget_id, array() );

		// Retrieve the key of the next widget instance
		$numeric_keys = array_filter( array_keys( $widget_instances ), 'is_int' );
		$next_key = $numeric_keys ? max( $numeric_keys ) + 1 : 2;
		// Add this widget to the sidebar
		if ( ! isset( $sidebars_widgets[ $sidebar ] ) ) {
			$sidebars_widgets[ $sidebar ] = array();
		}

		// add the widget to the start
		array_unshift($sidebars_widgets[$sidebar], $widget_id . '-' . $next_key);

		// add array to end
		//$sidebars_widgets[ $sidebar ][] = $widget_id . '-' . $next_key;

		// Add the new widget instance
		$widget_instances[ $next_key ] = $widget_data;
		// Store updated sidebars, widgets and their instances
		update_option( 'sidebars_widgets', $sidebars_widgets );
		update_option( 'widget_' . $widget_id, $widget_instances );
	}

    /**
     * Setup menu.
     *
     * @since 2.0.0
     *
     * @param string $menu_id Optional. Menu id. Default null.
     * @param string $menu_location Optional. Menu location. Default null.
     * @return string|WP_Error.
     */
	public static function setup_menu($menu_id = '',$menu_location = ''){

		$menu_id = sanitize_title_with_dashes($menu_id);
		$menu_location = sanitize_title_with_dashes($menu_location);

		// confirm the sidebar_id is valid
		if(!$menu_id && !$menu_location){
			return new WP_Error( 'gd-wizard-setup-menu', __( "The menu is not valid.", "geodirectory" ) );
		}

		$items_added = 0;
		$items_exist= 0;

		if($menu_id){



			$menu_exists = wp_get_nav_menu_object( $menu_id );

			if(!$menu_exists){
				return new WP_Error( 'gd-wizard-setup-menu', __( "The menu is not valid.", "geodirectory" ) );
			}

			$current_menu_items = wp_get_nav_menu_items( $menu_id );
			$current_menu_titles = array();
			// get a list of current slugs so we don't add things twice.
			if(!empty($current_menu_items)){
				foreach($current_menu_items as $current_menu_item){
					if(!empty($current_menu_item->post_name)){
						$current_menu_titles[] = $current_menu_item->title;
					}
				}
			}

			$gd_menus = new GeoDir_Admin_Menus();

			$gd_menu_items = $gd_menus->get_endpoints();

			if(!empty($gd_menu_items)){
				foreach($gd_menu_items as $menu_item_type){
					if(!empty($menu_item_type)){

						$menu_item_type = array_map('wp_setup_nav_menu_item', $menu_item_type);

						foreach($menu_item_type as $menu_item){

							if(!empty($current_menu_titles) && (in_array($menu_item->title,$current_menu_titles) || in_array(str_replace(" page",'',$menu_item->title),$current_menu_titles))){
								$items_exist++; continue 2;
							}

							// setup standard menu stuff
							$menu_item->{'menu-item-object-id'} = $menu_item->object_id;
							$menu_item->{'menu-item-object'} = $menu_item->object;
							$menu_item->{'menu-item-type'} = $menu_item->type;
							$menu_item->{'menu-item-status'} = 'publish';
							$menu_item->{'menu-item-classes'} = !empty($menu_item->classes) ? implode(" ",$menu_item->classes) : 'gd-menu-item';
							if($menu_item->type=='custom'){
								$menu_item->{'menu-item-url'} = $menu_item->url;
								$menu_item->{'menu-item-title'} = $menu_item->title;
							}
//
							// insert the menu item
							wp_update_nav_menu_item($menu_id, 0, $menu_item);
							$items_added++;
						}
					}
				}
			}

		}elseif($menu_location){

			$menuname = "GD Menu";

			// Does the menu exist already?
			$menu_exists = wp_get_nav_menu_object( $menuname );

			// If it doesn't exist, let's create it.
			if( !$menu_exists) {
				$menu_id = wp_create_nav_menu( $menuname );

				$locations = get_theme_mod( 'nav_menu_locations' );

				if($menu_id){
					$locations[$menu_location] = $menu_id;
					set_theme_mod('nav_menu_locations', $locations);
					return self::setup_menu($menu_id);
				}

			}else{
				return new WP_Error( 'gd-wizard-setup-menu', __( "Menu already exists.", "geodirectory" ) );
			}
		}


		if($items_added == 0 && $items_exist > 0){
			return __( 'Menu items already exist, none added.' , 'geodirectory' );
		}elseif($items_added  > 0){
			return __( 'Menu items added successfully.' , 'geodirectory' );
		}else{
			return __( 'Something went wrong, you can manually add items in Appearance > Menus' , 'geodirectory' );
		}

	}
}
