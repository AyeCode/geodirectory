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
 * WC_Admin_Menus Class.
 */
class GeoDir_Admin_Dummy_Data {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {

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

		if(!empty($categories)){
			foreach($categories as $slug => $category ){

				// If term exists just continue to the next one
				if ( term_exists( $category['name'], $post_type . 'category' ) ) { continue; }

				// insert the category
				$category_return = wp_insert_term( $category['name'], $post_type . 'category' );

				// attach the icon
				if(!empty($category['icon']) && isset($category_return['term_id']) ){
					$uploaded = (array) geodir_fetch_remote_file( $category['icon'] );

					if ( !empty( $uploaded['error'] ) ) {
						continue;
					}

					if ( empty( $uploaded['file'] ) ) {
						continue;
					}

					$wp_filetype = wp_check_filetype( basename( $uploaded['file'] ), null );

					$attachment = array(
						'guid'           => $uploads['baseurl'] . '/' . basename( $uploaded['file'] ),
						'post_mime_type' => $wp_filetype['type'],
						'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $uploaded['file'] ) ),
						'post_content'   => '',
						'post_status'    => 'inherit'
					);
					$attach_id  = wp_insert_attachment( $attachment, $uploaded['file'] );

					// you must first include the image.php file
					// for the function wp_generate_attachment_metadata() to work
					require_once( ABSPATH . 'wp-admin/includes/image.php' );
					$attach_data = wp_generate_attachment_metadata( $attach_id, $uploaded['file'] );
					wp_update_attachment_metadata( $attach_id, $attach_data );

					if(isset($attach_data['file'])){
						update_term_meta( $category_return['term_id'], 'ct_cat_icon' , array( 'id' => $attach_id , 'src' => $attach_data['file'] ) );
					}


				}

			}
		}

		return true;
	}

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
	public static function delete_dummy_posts( $post_type) {
		global $wpdb, $plugin_prefix;


		$post_ids = $wpdb->get_results( "SELECT post_id FROM " . $plugin_prefix . $post_type . "_detail WHERE post_dummy='1'" );


		foreach ( $post_ids as $post_ids_obj ) {
			wp_delete_post( $post_ids_obj->post_id );
		}

		//double check posts are deleted
		$wpdb->get_results( "DELETE FROM " . $plugin_prefix . $post_type . "_detail WHERE post_dummy='1'" );

		geodir_update_option( $post_type . '_dummy_data_type', '' );
	}

	public static function add_dummy_address($post_info = array()){
		global $city_bound_lat1, $city_bound_lng1, $city_bound_lat2, $city_bound_lng2;

		$default_location = geodir_get_default_location();
		if ( $city_bound_lat1 > $city_bound_lat2 ) {
			$dummy_post_latitude = geodir_random_float( random_float( $city_bound_lat1, $city_bound_lat2 ), geodir_random_float( $city_bound_lat2, $city_bound_lat1 ) );
		} else {
			$dummy_post_latitude = geodir_random_float( geodir_random_float( $city_bound_lat2, $city_bound_lat1 ), geodir_random_float( $city_bound_lat1, $city_bound_lat2 ) );
		}


		if ( $city_bound_lng1 > $city_bound_lng2 ) {
			$dummy_post_longitude = geodir_random_float( geodir_random_float( $city_bound_lng1, $city_bound_lng2 ), geodir_random_float( $city_bound_lng2, $city_bound_lng1 ) );
		} else {
			$dummy_post_longitude = geodir_random_float( geodir_random_float( $city_bound_lng2, $city_bound_lng1 ), geodir_random_float( $city_bound_lng1, $city_bound_lng2 ) );
		}

		$load_map = geodir_get_option( 'geodir_load_map' );

		if ( $load_map == 'osm' ) {
			$post_address = geodir_get_osm_address_by_lat_lan( $dummy_post_latitude, $dummy_post_longitude );
		} else {
			$post_address = geodir_get_address_by_lat_lan( $dummy_post_latitude, $dummy_post_longitude );
		}

		//print_r($post_address);echo $dummy_post_latitude.'####'.$dummy_post_longitude;
		$postal_code = '';
		if ( ! empty( $post_address ) ) {
			if ( $load_map == 'osm' ) {
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


		global $city_bound_lat1, $city_bound_lng1, $city_bound_lat2, $city_bound_lng2,$dummy_post_index,$dummy_image_url,$plugin_prefix;
//		$city_bound_lat1 = geodir_is_valid_lat($request['city_bound_lat1']) ? $request['city_bound_lat1'] : '';
//		$city_bound_lng1 = geodir_is_valid_lon($request['city_bound_lng1']) ? $request['city_bound_lng1'] : '';
//		$city_bound_lat2 = geodir_is_valid_lat($request['city_bound_lat2']) ? $request['city_bound_lat2'] : '';
//		$city_bound_lng2 = geodir_is_valid_lon($request['city_bound_lng2']) ? $request['city_bound_lng2'] : '';

		$city_bound_lat1 = $request['city_bound_lat1'];
		$city_bound_lng1 = $request['city_bound_lng1'];
		$city_bound_lat2 = $request['city_bound_lat2'];
		$city_bound_lng2 = $request['city_bound_lng2'];
		$post_type = sanitize_key($request['post_type']);
		$item_index = absint($request['dummy_post_index']);
		$data_type = sanitize_key($request['data_type']);

		ini_set( 'max_execution_time', 999999 ); //300 seconds = 5 minutes
		$data_types = self::dummy_data_types();

		$total_count = 0;
		$dummy_post_index = $item_index;
		
		$dummy_categories = array();
		$dummy_custom_fields = array();
		$dummy_posts = array();
		$dummy_image_url = '';
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
					/**
					 * Contains dummy property for sale post content.
					 *
					 * @since 1.6.11
					 * @package GeoDirectory
					 */
					include_once( 'dummy-data/property_sale.php' );
				} elseif ( $key == 'property_rent' ) {
					/**
					 * Contains dummy property for sale post content.
					 *
					 * @since 1.6.11
					 * @package GeoDirectory
					 */
					include_once( 'dummy-data/property_rent.php' );
				}
							

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

			// insert categories
			if( !empty($dummy_categories) ){
				self::create_taxonomies( $post_type, $dummy_categories );
			}

			return true;

		}else{ // if index is not 0 then we are starting on posts.

			$post_index = $item_index-1; // arrays start with 0
			
			if(!empty($dummy_posts) && isset($dummy_posts[$post_index]) ){
				$post_info = self::add_dummy_address($dummy_posts[$post_index]);

				// Set the status to publish
				if(isset($post_info['post_dummy']) && $post_info['post_dummy'] && !isset($post_info['post_status'])){
					$post_info['post_status'] = 'publish';
				}

				wp_insert_post($post_info, true); // we hook into the save_post hook
			}
		}


		// delete image cache on last entry
		if ( $total_count == $item_index ) {
			delete_transient( 'cached_dummy_images' );
			flush_rewrite_rules();
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

//		$x = geodir_get_external_media( 'http://wpgeodirectory.com/dummy/cat_icon/Attractions.png' );
//		print_r($x);exit;

		wp_enqueue_script( 'jquery-ui-progressbar' );
		global $wpdb, $plugin_prefix;

		if ( ! geodir_is_default_location_set() ) {
			echo '<div class="updated fade"><p><strong>' . sprintf( __( 'Please %sclick here%s to set a default location, this will help to set location of all dummy data.', 'geodirectory' ), '<a href=\'' . admin_url( 'admin.php?page=geodirectory&tab=default_location_settings' ) . '\'>', '</a>' ) . '</strong></p></div>';
		} else {

			?>
			<table class="form-table gd-dummy-table">
				<tbody>
				<tr>
					<td><strong><?php _e( 'CPT', 'geodirectory' ); ?></strong></td>
					<td><strong><?php _e( 'Data Type', 'geodirectory' ); ?></strong></td>
					<td><strong><?php _e( 'Action', 'geodirectory' ); ?></strong></td>
				</tr>

				<?php

				$cpts = geodir_get_posttypes( 'array' );

				$data_types = self::dummy_data_types();

				$nonce = wp_create_nonce( 'geodir_dummy_data' );

				foreach ( $cpts as $post_type => $cpt ) {

					$data_types_for = apply_filters( 'geodir_dummy_date_types_for', $data_types, $post_type );


					$set_dt = geodir_get_option( $post_type . '_dummy_data_type' );

					$count = 30;

					if(geodir_column_exist($plugin_prefix . $post_type . "_detail", "post_dummy")){
						$post_counts = $wpdb->get_var( "SELECT count(post_id) FROM " . $plugin_prefix . $post_type . "_detail WHERE post_dummy='1'" );
					}else{
						$post_counts = 0;
					}


					echo "<tr>";
					echo "<td><strong>" . $cpt['labels']['name'] . "</strong></td>";


					$select_disabled = $post_counts > 0 ? 'disabled' : '';
					echo "<td>";
					echo "<select id='" . $post_type . "_data_type' onchange='geodir_dummy_set_count(this,\"$post_type\");' $select_disabled>";

					foreach ( $data_types_for as $key => $val ) {
						$selected = ( $key == $set_dt ) ? "selected='selected'" : '';
						if ( $selected || count( $data_types_for ) == 1 ) {
							$count = $val['count'];
						}
						echo "<option $selected value='$key' data-count='" . $val['count'] . "'>" . $val['name'] . "</option>";
					}
					echo "</select>";

					$select_display = $post_counts > 0 ? 'display:none;' : '';
					echo "<select id='" . $post_type . "_data_type_count' style='$select_display' >";
					$x = 1;
					while ( $x <= $count ) {
						$selected = ( $x == $count ) ? "selected='selected'" : '';
						echo "<option $selected value='$x'>" . $x . "</option>";
						$x ++;
					}
					echo "</select>";
					echo "</td>";


					if ( $post_counts > 0 ) {
						echo '<td><input type="button" value="' . __( 'Remove data', 'geodirectory' ) . '" class="button-primary geodir_dummy_button gd-remove-data" onclick="gdInstallDummyData(this,\'' . $nonce . '\',\'' . $post_type . '\'); return false;" ></td>';
					} else {
						echo '<td><input type="button" value="' . __( 'Insert data', 'geodirectory' ) . '" class="button-primary geodir_dummy_button" onclick="gdInstallDummyData(this,\'' . $nonce . '\',\'' . $post_type . '\'); return false;" ></td>';
					}

					echo "</tr>";
					//print_r($cpt);
				}

				?>
				</tbody>
			</table>
			<?php


			$default_location = geodir_get_default_location();

			//echo '###';print_r($default_location );
			$city           = isset( $default_location->city ) ? $default_location->city : '';
			$region         = isset( $default_location->region ) ? $default_location->region : '';
			$country        = isset( $default_location->country ) ? $default_location->country : '';
			$city_latitude  = isset( $default_location->latitude ) ? $default_location->latitude : '';
			$city_longitude = isset( $default_location->longitude ) ? $default_location->longitude : '';
			?>
			<script type="text/javascript">

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
							'<div class="gd_progressbar_container_' + posttype + '">' +
							'<div id="gd_progressbar" class="gd_progressbar_' + posttype + '">' +
							'<div class="gd-progress-label"></div>' +
							'</div>' +
							'</div>' +
							'</td>' +
							'</tr>').insertAfter(jQuery(obj).parents('tr'));

						jQuery('.gd_progressbar_' + posttype).progressbar({value: 0});

						gd_progressbar('.gd_progressbar_container_' + posttype, 0, '<i class="fa fa-refresh fa-spin"></i><?php echo esc_attr( __( 'Removing data...', 'geodirlocation' ) );?>');




						var data = {
							'action':           'geodir_delete_dummy_data',
							'security':         '<?php echo $nonce;?>',
							'post_type':        posttype
						};
						jQuery.post(ajaxurl,
							data,
							function (data) {
								gd_progressbar('.gd_progressbar_container_' + posttype, 100, '<i class="fa fa-check"></i><?php echo esc_attr( __( 'Complete!', 'geodirlocation' ) );?>');
								jQuery(obj).removeClass('gd-remove-data');
								jQuery(obj).val('<?php _e( 'Insert data', 'geodirectory' );?>');
								jQuery(obj).prop('disabled', false);
								jQuery('#' + posttype + '_data_type_count').show();
								jQuery('#' + posttype + '_data_type').prop('disabled', false);
								geodir_dummy_set_count(jQuery('#' + posttype + '_data_type'), posttype);
							});
						return true;
					}
				}


				function gdInstallDummyData(obj, nonce, posttype, insertedCount) {


					if (jQuery(obj).hasClass('gd-remove-data')) {
						gdRemoveDummyData(obj, nonce, posttype);
						return;
					}

					jQuery(obj).prop('disabled', true);
					jQuery('#' + posttype + '_data_type').prop('disabled', true);
					jQuery('#' + posttype + '_data_type_count').hide();

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

					var result_container = jQuery('.gd-dummy-data-results-' + posttype);
					if (!result_container.length) {

						jQuery('<tr class="gd-dummy-data-results gd-dummy-data-results-' + posttype + '" >' +
							'<td colspan="3">' +
							'<div class="gd_progressbar_container_' + posttype + '">' +
							'<div id="gd_progressbar" class="gd_progressbar_' + posttype + '">' +
							'<div class="gd-progress-label"></div>' +
							'</div>' +
							'</div>' +
							'</td>' +
							'</tr>').insertAfter(jQuery(obj).parents('tr'));

						jQuery('.gd_progressbar_' + posttype).progressbar({value: 0});

						gd_progressbar('.gd_progressbar_container_' + posttype, 0, '0% (0 / ' + dateTypeCount + ') <i class="fa fa-refresh fa-spin"></i><?php echo esc_attr( __( 'Creating categories and custom fields...', 'geodirlocation' ) );?>');
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


							gd_progressbar('.gd_progressbar_container_' + posttype, percentage, percentage + '% (' + insertedCount + ' / ' + dateTypeCount + ') <i class="fa fa-refresh fa-spin"></i><?php echo esc_attr( __( 'Inserting data...', 'geodirlocation' ) );?>');
							console.log(insertedCount);
							gdInstallDummyData(obj, nonce, posttype, insertedCount);
						}
						else {
							percentage = 100;
							gd_progressbar('.gd_progressbar_container_' + posttype, percentage, percentage + '% (' + insertedCount + ' / ' + dateTypeCount + ') <i class="fa fa-check"></i><?php echo esc_attr( __( 'Complete!', 'geodirlocation' ) );?>');
							jQuery(obj).addClass('gd-remove-data');
							jQuery(obj).val('<?php _e( 'Remove data', 'geodirectory' );?>');
							jQuery(obj).prop('disabled', false);

						}
					});

				}
			</script>
			<?php
		}
	}

	public static function dummy_data_types() {
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
			)
		);

		return apply_filters( 'geodir_dummy_data_types', $data );
	}

}