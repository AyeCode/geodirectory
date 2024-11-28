<?php
/**
 * Tools page
 *
 * @author      GeoDirectory
 * @category    Admin
 * @package     GeoDirectory/Admin/Tools
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GeoDir_Admin_Tools Class.
 */
class GeoDir_Admin_Tools {

	/**
	 * GeoDir_Admin_Tools constructor.
	 */
	public function __construct() {
		// Gather DB texts
		add_filter('geodir_load_db_language', array($this,'load_custom_field_translation') );
		add_filter('geodir_load_db_language', array($this,'load_cpt_text_translation') );
		add_filter('geodir_load_db_language', array($this,'load_gd_options_text_translation') );
		add_filter('geodir_debug_tools', array( $this, 'extra_debug_tools' ), 99, 1 );
		add_action('geodir_status_tool_after_desc', array( $this, 'tool_extra_content' ), 10, 2 );
	}

	/**
	 * A list of available tools for use in the system status section.
	 * 'button' becomes 'action' in the API.
	 *
	 * @return array
	 */
	public function get_tools() {
		global $geodir_count_attachments, $geodirectory;

		if ( empty( $geodir_count_attachments ) ) {
			$geodir_count_attachments = GeoDir_Media::count_image_attachments();
		}

		$is_big_data_active = !empty($geodirectory->settings['enable_big_data']);
		$big_data_status = $is_big_data_active ? __('(active)', 'geodirectory') : __('(not active)', 'geodirectory');

		$tools = array(
			'clear_version_numbers' => array(
				'name'    => __( 'Clear version numbers', 'geodirectory' ),
				'button'  => __( 'Run', 'geodirectory' ),
				'desc'    => __( 'This will force install/upgrade functions to run.', 'geodirectory' ),
			),
			'check_reviews' => array(
				'name'    => __( 'Check reviews', 'geodirectory' ),
				'button'  => __( 'Run', 'geodirectory' ),
				'desc'    => __( 'Check reviews for correct location and content settings.', 'geodirectory' ),
			),
			'install_pages' => array(
				'name'    => __( 'Create default GeoDirectory pages', 'geodirectory' ),
				'button'  => __( 'Run', 'geodirectory' ),
				'desc'    => sprintf(
					'<strong class="red">%1$s</strong> %2$s',
					__( 'Note:', 'geodirectory' ),
					__( 'This tool will install all the missing GeoDirectory pages. Pages already defined and set up will not be replaced.', 'geodirectory' )
				),
			),
			'merge_missing_terms' => array(
				'name'    => __( 'Merge Missing Categories', 'geodirectory' ),
				'button'  => __( 'Run', 'geodirectory' ),
				'desc'    => __( 'Merge missing listing categories from WP terms relationships.', 'geodirectory' ),
			),
			'recount_terms' => array(
				'name'    => __( 'Term counts', 'geodirectory' ),
				'button'  => __( 'Run', 'geodirectory' ),
				'desc'    => __( 'This tool will recount the listing terms.', 'geodirectory' ),
			),
			'generate_keywords' => array(
				'name'    => __( 'Generate Keywords', 'geodirectory' ),
				'button'  => __( 'Run', 'geodirectory' ),
				'desc'    => __( 'Generate keywords from post title to enhance searching.', 'geodirectory' ),
			),
			'generate_thumbnails' => array(
				'name'    => __( 'Regenerate Thumbnails', 'geodirectory' ),
				'button'  => __( 'Run', 'geodirectory' ),
				'desc'    => wp_sprintf( __( 'Regenerate thumbnails & metadata for the post images. Total image attachments found: %s', 'geodirectory' ), '<b>' . (int) $geodir_count_attachments . '</b>' ) . '<div class="geodir-tool-stats gd-hidden" data-total="' . (int) $geodir_count_attachments . '" data-per-page="10"><div id="gd_progressbar_box"><div id="gd_progressbar" class="gd_progressbar"><div class="gd-progress-label"></div></div></div><span style="display:inline-block">' . __( 'Elapsed Time:', 'geodirectory' ) . '</span>&nbsp;&nbsp;<span id="gd_timer" class="gd_timer">00:00:00</span></div>',
			),
			'export_db_texts' => array(
				'name'    => __( 'DB text translation', 'geodirectory' ),
				'button'  => __( 'Run', 'geodirectory' ),
				'desc'    => __( 'This tool will collect any texts stored in the DB and put them in the file db-language.php so they can then be used to translate them by translations tools.', 'geodirectory' ),
			),
			'clear_paging_cache' => array(
				'name'    => __( 'Clear paging cache', 'geodirectory' ),
				'button'  => __( 'Clear', 'geodirectory' ),
				'desc'    => __( 'This tool will delete paging cache when the BIG Data option is enabled', 'geodirectory' ) . ' ' . esc_attr( $big_data_status ),
			),
			'search_replace_cf' => array(
				'name'    => __( 'Search & Replace Custom Field Value', 'geodirectory' ),
				'button'  => __( 'Replace', 'geodirectory' ),
				'desc'    => __( 'Search & replace custom field values in post type details database table for SELECT, MULTISELECT, RADIO, CHECKBOX field types.', 'geodirectory' ),
				'link'    => '#search_replace_cf'
			),
		);

		return apply_filters( 'geodir_debug_tools', $tools );
	}

	/**
	 * Actually executes a tool.
	 *
	 * @param  string $tool
	 * @return array
	 */
	public function execute_tool( $tool ) {
		global $wpdb;
		$ran = true;
		switch ( $tool ) {
			case 'clear_version_numbers' :
				if ($message = $this->clear_version_numbers()) {
				} else {
					$message = __( 'Something went wrong.', 'geodirectory' );
					$ran     = false;
				}
				break;
			case 'check_reviews' :
				if ($this->check_reviews()) {
					$message = __( 'Reviews checked.', 'geodirectory' );
				} else {
					$message = __( 'No reviews checked.', 'geodirectory' );
				}
				break;
			case 'merge_missing_terms' :
				$count = geodir_merge_missing_terms();

				if ( $count > 0 ) {
					$message = wp_sprintf( _n( 'Missing categories merged for %d listing.', 'Missing categories merged for %d listings.', $count, 'geodirectory' ), $count );
				} else {
					$message = __( 'No listing found with missing terms.', 'geodirectory' );
				}

				break;
			case 'recount_terms' : // TODO
				$post_types = geodir_get_posttypes();
				foreach ( $post_types as $post_type ) {
					$cats = get_terms( $post_type . 'category', array( 'hide_empty' => false, 'fields' => 'id=>parent' ) );
					geodir_term_recount( $cats, get_taxonomy( $post_type . 'category' ), $post_type, true, false );
					$tags = get_terms( $post_type . '_tags', array( 'hide_empty' => false, 'fields' => 'id=>parent' ) );
					geodir_term_recount( $tags, get_taxonomy( $post_type . '_tags' ), $post_type, true, false );
				}
				$message = __( 'Terms successfully recounted', 'geodirectory' );
				break;
			case 'generate_keywords' :
				$generated = (int) geodir_generate_title_keywords();

				if ( $generated > 0 ) {
					$message = wp_sprintf( _n( '%d keyword generated.', '%d keywords generated.', $generated, 'geodirectory' ), $generated );
				} else {
					$message = __( 'No keyword generated.', 'geodirectory' );
				}
				break;
			case 'install_pages' :
				GeoDir_Admin_Install::create_pages();
				$message = __( 'All missing GeoDirectory pages successfully installed', 'geodirectory' );
				break;
			case 'export_db_texts' :
				if ($this->load_db_language()) {
					$message = __( 'File successfully created: ', 'geodirectory' ). geodir_plugin_path() . 'db-language.php';
				} else {
					$message = __( 'There was a problem creating the file, please check file permissions: ', 'geodirectory' ). geodir_plugin_path() . 'db-language.php';
					$ran     = false;
				}
				break;
			case 'clear_paging_cache' :
				if ($this->clear_paging_cache()) {
					$message = __( 'Cache successfully cleared', 'geodirectory' );
				} else {
					$message = __( 'There was a problem clearing the cache', 'geodirectory' );
					$ran     = false;
				}
				break;
			case 'search_replace_cf' :
				if ( $replaced = $this->search_replace_cf_value() ) {
					$message = wp_sprintf( __( '%d items has been successfully updated.', 'geodirectory' ), $replaced );
				} else {
					$message = __( 'No matching items found.', 'geodirectory' );
					$ran = false;
				}
				break;
			case 'cleanup' :
				$return = $this->remove_unused_data();

				$message = __( 'Unused options data removed.', 'geodirectory' );
				break;
			default :
				$tools = $this->get_tools();
				if ( isset( $tools[ $tool ]['callback'] ) ) {
					$callback = $tools[ $tool ]['callback'];
					$return = call_user_func( $callback );
					if ( is_string( $return ) ) {
						$message = $return;
					} elseif ( false === $return ) {
						$callback_string = is_array( $callback ) ? get_class( $callback[0] ) . '::' . $callback[1] : $callback;
						$ran = false;
						$message = sprintf( __( 'There was an error calling %s', 'geodirectory' ), $callback_string );
					} else {
						$message = __( 'Tool ran.', 'geodirectory' );
					}
				} else {
					$ran     = false;
					$message = __( 'There was an error calling this tool. There is no callback present.', 'geodirectory' );
				}
				break;
		}

		do_action("geodir_tool_{$tool}",$ran,$message);

		return array( 'success' => $ran, 'message' => $message );
	}

	/**
	 * Clear version numbers so install/upgrade functions will run.
	 *
	 * @return string|void
	 */
	public function clear_version_numbers(){
		delete_site_option( 'wp_country_database_version' ); // Delete countries database version.
		delete_option( 'geodirectory_version' );
		wp_cache_delete( 'geodir_noindex_page_ids' );
		do_action( 'geodir_clear_version_numbers');
		return __( 'Version numbers cleared. Install/upgrade functions will run on next page load.', 'geodirectory' );
	}

	/**
	 * Check reviews.
	 *
	 * @since 2.0.0
	 *
	 * @global object $wpdb WordPress Database object.
	 *
	 * @return bool $checked
	 */
	public function check_reviews() {
		global $wpdb;

		$checked = false;

		if ($wpdb->get_results("SELECT * FROM " . GEODIR_REVIEW_TABLE . " WHERE latitude IS NULL OR latitude = '' OR longitude IS NULL OR longitude = '' OR city IS NULL OR city = ''")) {
			if ($this->check_reviews_location()) {
				$checked = true;
			}
		}

		return $checked;
	}

	/**
	 * Check reviews location.
	 *
	 * @since 2.0.0
	 *
	 * @global object $wpdb WordPress Database object.
	 *
	 * @return bool
	 */
	public function check_reviews_location() {
		global $wpdb;

		$post_types = geodir_get_posttypes();

		if ( !empty( $post_types ) ) {
			foreach ( $post_types as $post_type ) {
				$wpdb->query( "UPDATE " . GEODIR_REVIEW_TABLE . " AS gdr JOIN " . $wpdb->prefix . "geodir_" . $post_type . "_detail d ON gdr.post_id=d.post_id SET gdr.latitude=d.latitude, gdr.longitude=d.longitude, gdr.city=d.city, gdr.region=d.region, gdr.country=d.country WHERE gdr.latitude IS NULL OR gdr.latitude = '' OR gdr.longitude IS NULL OR gdr.longitude = '' OR gdr.city IS NULL OR gdr.city = ''" );

			}
			return true;
		}

		return false;
	}

	public function clear_paging_cache()
	{
		delete_option('gd_found_posts_cache');
		return true;
	}

	/**
	 * Load language strings in to file to translate via po editor
	 *
	 * @since   1.4.2
	 * @package GeoDirectory
	 *
	 * @global null|object $wp_filesystem WP_Filesystem object.
	 *
	 * @return bool True if file created otherwise false
	 */
	public function load_db_language() {
		$wp_filesystem = geodir_init_filesystem();

		$language_file = geodir_plugin_path() . 'db-language.php';

		if ( is_file( $language_file ) && ! is_writable( $language_file ) ) {
			return false;
		} // Not possible to create.

		if ( ! is_file( $language_file ) && ! is_writable( dirname( $language_file ) ) ) {
			return false;
		} // Not possible to create.

		$contents_strings = array();

		/**
		 * Filter the language string from database to translate via po editor
		 *
		 * @since 1.4.2
		 * @since 1.6.16 Register the string for WPML translation.
		 *
		 * @param array $contents_strings Array of strings.
		 */
		$contents_strings = apply_filters( 'geodir_load_db_language', $contents_strings );

		$contents_strings = array_unique( $contents_strings );

		$contents_head   = array();
		$contents_head[] = "<?php";
		$contents_head[] = "/**";
		$contents_head[] = " * Translate language string stored in database. Ex: Custom Fields";
		$contents_head[] = " *";
		$contents_head[] = " * @package GeoDirectory";
		$contents_head[] = " * @since ".GEODIRECTORY_VERSION;
		$contents_head[] = " */";
		$contents_head[] = "";
		$contents_head[] = "// Language keys";

		$contents_foot   = array();
		$contents_foot[] = "";
		$contents_foot[] = "";

		$contents = implode( PHP_EOL, $contents_head );

		if ( ! empty( $contents_strings ) ) {
			foreach ( $contents_strings as $string ) {
				if ( is_scalar( $string ) && $string != '' ) {
					do_action( 'geodir_language_file_add_string', $string );

					$string = str_replace( "'", "\'", $string );

					$contents .= PHP_EOL . "__('" . $string . "', 'geodirectory');";
				}
			}
		}

		$contents .= implode( PHP_EOL, $contents_foot );

		if ( ! $wp_filesystem->put_contents( $language_file, $contents, FS_CHMOD_FILE ) ) {
			return false;
		} // Failure; could not write file.

		return true;
	}


	/**
	 * Get the custom fields texts for translation
	 *
	 * @since   1.4.2
	 * @since   1.5.7 Option values are translatable via db translation.
	 * @since   1.6.11 Some new labels translation for advance custom fields.
	 * @package GeoDirectory
	 *
	 * @global object $wpdb             WordPress database abstraction object.
	 *
	 * @param  array $translation_texts Array of text strings.
	 *
	 * @return array Translation texts.
	 */
	public function load_custom_field_translation( $translation_texts = array() ) {
		global $wpdb;

		// Custom fields table
		$sql  = "SELECT htmlvar_name, admin_title, frontend_desc, frontend_title, clabels, required_msg, placeholder_value, default_value, option_values, validation_msg, extra_fields FROM " . GEODIR_CUSTOM_FIELDS_TABLE;
		$rows = $wpdb->get_results( $sql );

		if ( ! empty( $rows ) ) {
			foreach ( $rows as $row ) {
				if ( ! empty( $row->admin_title ) ) {
					$translation_texts[] = stripslashes_deep( $row->admin_title );
				}

				if ( ! empty( $row->frontend_desc ) ) {
					$translation_texts[] = stripslashes_deep( $row->frontend_desc );
				}

				if ( ! empty( $row->frontend_title ) ) {
					$translation_texts[] = stripslashes_deep( $row->frontend_title );
				}

				if ( ! empty( $row->clabels ) ) {
					$translation_texts[] = stripslashes_deep( $row->clabels );
				}

				if ( ! empty( $row->required_msg ) ) {
					$translation_texts[] = stripslashes_deep( $row->required_msg );
				}

				if ( ! empty( $row->placeholder_value ) ) {
					$translation_texts[] = stripslashes_deep( $row->placeholder_value );
				}

				if ( ! empty( $row->validation_msg ) ) {
					$translation_texts[] = stripslashes_deep( $row->validation_msg );
				}

				if ( ! empty( $row->default_value ) ) {
					$translation_texts[] = stripslashes_deep( $row->default_value );
				}

				if ( ! empty( $row->option_values ) ) {
					$option_values = geodir_string_values_to_options( stripslashes_deep( $row->option_values ) );

					if ( ! empty( $option_values ) ) {
						foreach ( $option_values as $option_value ) {
							if ( ! empty( $option_value['label'] ) ) {
								$translation_texts[] = $option_value['label'];
							}
						}
					}
				}

				if ( $row->htmlvar_name == 'address' && ! empty( $row->extra_fields ) && ( $extra_fields = maybe_unserialize( $row->extra_fields ) ) ) {
					$address_labels = array( 'city_lable', 'region_lable', 'country_lable', 'neighbourhood_lable', 'street2_lable', 'zip_lable', 'map_lable', 'mapview_lable' );

					foreach ( $address_labels as $label ) {
						if ( ! empty( $extra_fields[ $label ] ) ) {
							$translation_texts[] = stripslashes( $extra_fields[ $label ] );
						}
					}
				}
			}
		}

		// Custom sorting fields table
		$sql  = "SELECT frontend_title FROM " . GEODIR_CUSTOM_SORT_FIELDS_TABLE;
		$rows = $wpdb->get_results( $sql );

		if ( ! empty( $rows ) ) {
			foreach ( $rows as $row ) {
				if ( ! empty( $row->frontend_title ) ) {
					$translation_texts[] = stripslashes_deep( $row->frontend_title );
				}
			}
		}

		// Single listing tabs
		$sql  = "SELECT tab_name FROM " . GEODIR_TABS_LAYOUT_TABLE;
		$rows = $wpdb->get_results( $sql );

		if ( ! empty( $rows ) ) {
			foreach ( $rows as $row ) {
				if ( ! empty( $row->tab_name ) ) {
					$translation_texts[] = stripslashes( $row->tab_name );
				}
			}
		}

		// Advance search filter fields table @todo this should be in the advanced search addon
		if ( defined( 'GEODIR_ADVANCE_SEARCH_TABLE' ) ) {
			$sql  = "SELECT frontend_title, admin_title, description, range_from_title, range_to_title, extra_fields FROM " . GEODIR_ADVANCE_SEARCH_TABLE;
			$rows = $wpdb->get_results( $sql );

			if ( ! empty( $rows ) ) {
				foreach ( $rows as $row ) {
					if ( ! empty( $row->frontend_title ) ) {
						$translation_texts[] = stripslashes_deep( $row->frontend_title );
					}

					if ( ! empty( $row->admin_title ) ) {
						$translation_texts[] = stripslashes_deep( $row->admin_title );
					}

					if ( ! empty( $row->description ) ) {
						$translation_texts[] = stripslashes_deep( $row->description );
					}

					if ( ! empty( $row->range_from_title ) ) {
						$translation_texts[] = stripslashes_deep( $row->range_from_title );
					}

					if ( ! empty( $row->range_to_title ) ) {
						$translation_texts[] = stripslashes_deep( $row->range_to_title );
					}

					if ( ! empty( $row->extra_fields ) && ( $extra_fields = maybe_unserialize( $row->extra_fields ) ) ) {
						if ( ! empty( $extra_fields['asc_title'] ) ) {
							$translation_texts[] = stripslashes_deep( $extra_fields['asc_title'] );
						}

						if ( ! empty( $extra_fields['desc_title'] ) ) {
							$translation_texts[] = stripslashes_deep( $extra_fields['desc_title'] );
						}
					}
				}
			}
		}

		$translation_texts = ! empty( $translation_texts ) ? array_unique( $translation_texts ) : $translation_texts;

		return $translation_texts;
	}

	/**
	 * Get the cpt texts for translation.
	 *
	 * @since   1.5.5
	 * @package GeoDirectory
	 *
	 * @param  array $translation_texts Array of text strings.
	 *
	 * @return array Translation texts.
	 */
	public function load_cpt_text_translation( $translation_texts = array() ) {
		$gd_post_types = geodir_get_posttypes( 'array' );

		if ( ! empty( $gd_post_types ) ) {
			foreach ( $gd_post_types as $post_type => $cpt_info ) {
				$labels      = isset( $cpt_info['labels'] ) ? $cpt_info['labels'] : '';
				$description = isset( $cpt_info['description'] ) ? $cpt_info['description'] : '';
				$seo         = isset( $cpt_info['seo'] ) ? $cpt_info['seo'] : '';

				if ( ! empty( $labels ) ) {
					if ( $labels['name'] != '' && ! in_array( $labels['name'], $translation_texts ) ) {
						$translation_texts[] = $labels['name'];
					}
					if ( $labels['singular_name'] != '' && ! in_array( $labels['singular_name'], $translation_texts ) ) {
						$translation_texts[] = $labels['singular_name'];
					}
					if ( $labels['add_new'] != '' && ! in_array( $labels['add_new'], $translation_texts ) ) {
						$translation_texts[] = $labels['add_new'];
					}
					if ( $labels['add_new_item'] != '' && ! in_array( $labels['add_new_item'], $translation_texts ) ) {
						$translation_texts[] = $labels['add_new_item'];
					}
					if ( $labels['edit_item'] != '' && ! in_array( $labels['edit_item'], $translation_texts ) ) {
						$translation_texts[] = $labels['edit_item'];
					}
					if ( $labels['new_item'] != '' && ! in_array( $labels['new_item'], $translation_texts ) ) {
						$translation_texts[] = $labels['new_item'];
					}
					if ( $labels['view_item'] != '' && ! in_array( $labels['view_item'], $translation_texts ) ) {
						$translation_texts[] = $labels['view_item'];
					}
					if ( $labels['search_items'] != '' && ! in_array( $labels['search_items'], $translation_texts ) ) {
						$translation_texts[] = $labels['search_items'];
					}
					if ( $labels['not_found'] != '' && ! in_array( $labels['not_found'], $translation_texts ) ) {
						$translation_texts[] = $labels['not_found'];
					}
					if ( $labels['not_found_in_trash'] != '' && ! in_array( $labels['not_found_in_trash'], $translation_texts ) ) {
						$translation_texts[] = $labels['not_found_in_trash'];
					}
					if ( isset( $labels['listing_owner'] ) && $labels['listing_owner'] != '' && ! in_array( $labels['listing_owner'], $translation_texts ) ) {
						$translation_texts[] = $labels['listing_owner'];
					}
					if ( isset( $labels['label_post_profile'] ) && $labels['label_post_profile'] != '' && ! in_array( $labels['label_post_profile'], $translation_texts ) ) {
						$translation_texts[] = $labels['label_post_profile'];
					}
					if ( isset( $labels['label_post_info'] ) && $labels['label_post_info'] != '' && ! in_array( $labels['label_post_info'], $translation_texts ) ) {
						$translation_texts[] = $labels['label_post_info'];
					}
					if ( isset( $labels['label_post_images'] ) && $labels['label_post_images'] != '' && ! in_array( $labels['label_post_images'], $translation_texts ) ) {
						$translation_texts[] = $labels['label_post_images'];
					}
					if ( isset( $labels['label_post_map'] ) && $labels['label_post_map'] != '' && ! in_array( $labels['label_post_map'], $translation_texts ) ) {
						$translation_texts[] = $labels['label_post_map'];
					}
					if ( isset( $labels['label_reviews'] ) && $labels['label_reviews'] != '' && ! in_array( $labels['label_reviews'], $translation_texts ) ) {
						$translation_texts[] = $labels['label_reviews'];
					}
					if ( isset( $labels['label_related_listing'] ) && $labels['label_related_listing'] != '' && ! in_array( $labels['label_related_listing'], $translation_texts ) ) {
						$translation_texts[] = $labels['label_related_listing'];
					}
				}

				if ( $description != '' && ! in_array( $description, $translation_texts ) ) {
					$translation_texts[] = normalize_whitespace( $description );
				}

				if ( ! empty( $seo ) ) {
					if ( isset( $seo['title'] ) && $seo['title'] != '' && ! in_array( $seo['title'], $translation_texts ) ) {
						$translation_texts[] = normalize_whitespace( $seo['title'] );
					}

					if ( isset( $seo['meta_title'] ) && $seo['meta_title'] != '' && ! in_array( $seo['meta_title'], $translation_texts ) ) {
						$translation_texts[] = normalize_whitespace( $seo['meta_title'] );
					}

					if ( isset( $seo['meta_description'] ) && $seo['meta_description'] != '' && ! in_array( $seo['meta_description'], $translation_texts ) ) {
						$translation_texts[] = normalize_whitespace( $seo['meta_description'] );
					}
				}
			}
		}
		$translation_texts = ! empty( $translation_texts ) ? array_unique( $translation_texts ) : $translation_texts;

		return $translation_texts;
	}

	/**
	 * Get the geodirectory notification subject & content texts for translation.
	 *
	 * @since 1.5.7
	 * @package GeoDirectory
	 *
	 * @param  array $translation_texts Array of text strings.
	 * @return array Translation texts.
	 */
	public function load_gd_options_text_translation($translation_texts = array()) {
		$translation_texts = !empty( $translation_texts ) && is_array( $translation_texts ) ? $translation_texts : array();

		$gd_options = array(
			'seo_cpt_title',
			'seo_cpt_meta_title',
			'seo_cpt_meta_description',
			'seo_cat_archive_title',
			'seo_cat_archive_meta_title',
			'seo_cat_archive_meta_description',
			'seo_tag_archive_title',
			'seo_tag_archive_meta_title',
			'seo_tag_archive_meta_description',
			'seo_single_title',
			'seo_single_meta_title',
			'seo_single_meta_description',
			'seo_location_title',
			'seo_location_meta_title',
			'seo_location_meta_description',
			'seo_search_title',
			'seo_search_meta_title',
			'seo_search_meta_description',
			'seo_add_listing_title',
			'seo_add_listing_title_edit',
			'seo_add_listing_meta_title',
			'seo_add_listing_meta_description',
			'search_default_text',
			'search_default_near_text',
			'email_name',
			'email_admin_pending_post_subject',
			'email_admin_pending_post_body',
			'email_admin_post_edit_subject',
			'email_admin_post_edit_body',
			'email_admin_moderate_comment_subject',
			'email_admin_moderate_comment_body',
			'email_user_pending_post_subject',
			'email_user_pending_post_body',
			'email_user_publish_post_subject',
			'email_user_publish_post_body',
			'email_owner_comment_submit_subject',
			'email_owner_comment_submit_body',
			'email_owner_comment_approved_subject',
			'email_owner_comment_approved_body',
			'email_author_comment_approved_subject',
			'email_author_comment_approved_body',
			'rating_text_1',
			'rating_text_2',
			'rating_text_3',
			'rating_text_4',
			'rating_text_5',
			'email_footer_text',
		);

		/**
		 * Filters the geodirectory option names that requires to add for translation.
		 *
		 * @since 1.5.7
		 * @package GeoDirectory
		 *
		 * @param  array $gd_options Array of option names.
		 */
		$gd_options = apply_filters('geodir_gd_options_for_translation', $gd_options);
		$gd_options = array_unique($gd_options);

		if (!empty($gd_options)) {
			foreach ($gd_options as $gd_option) {
				if ($gd_option != '' && $option_value = geodir_get_option($gd_option)) {
					$option_value = is_string($option_value) ? stripslashes_deep($option_value) : '';

					if ($option_value != '' && !in_array($option_value, $translation_texts)) {
						$translation_texts[] = stripslashes_deep($option_value);
					}
				}
			}
		}

		$translation_texts = !empty($translation_texts) ? array_unique($translation_texts) : $translation_texts;

		return $translation_texts;
	}

	/**
	 * Tool to remove unused GDv1 options.
	 *
	 * @since 2.1.0.12
	 */
	public function extra_debug_tools( $tools ) {
		if ( get_option( 'geodir_post_types' ) && version_compare( get_option( 'geodirectory_db_version' ), '2.0.0.0', '>=' ) ) {
			$tools['cleanup'] = array(
				'name'    => __( 'Delete Unused Options Data', 'geodirectory' ),
				'button'  => __( 'Run', 'geodirectory' ),
				'desc'    => __( 'Remove unused GDv1 options data which are no longer required in GDv2. This minimizes options database table to load options data more faster.', 'geodirectory' ),
			);
		}

		return $tools;
	}

	/**
	 * Remove unused GDv1 options.
	 *
	 * @since 2.1.0.12
	 */
	public function remove_unused_data() {
		global $wpdb;

		$table = $wpdb->options;

		// Matching option names
		$wpdb->query( "DELETE FROM `{$table}` WHERE `option_name` LIKE 'tax_meta_gd_%' OR `option_name` LIKE 'geodir_cat_loc_%' OR `option_name` LIKE 'geodir_revision_%'" );
		$wpdb->query( "DELETE FROM `{$table}` WHERE `option_name` LIKE 'geodir_changes_%' OR `option_name` LIKE 'geodir_bcc_%' OR `option_name` LIKE 'geodir_listing_%' OR `option_name` LIKE 'geodir_meta_%' OR `option_name` LIKE 'geodir_show_%' OR `option_name` LIKE 'geodir_un_%' OR `option_name` LIKE 'geodir_width_%' OR `option_name` LIKE 'geodir_disable_%' OR `option_name` LIKE 'geodir_default_%' OR `option_name` LIKE 'geodir_sidebars%' OR `option_name` LIKE 'geodir_theme_location_%' OR ( `option_name` LIKE 'geodir_post_%' AND `option_name` LIKE '%_email_%' )" );

		// Addons options
		$wpdb->query( "DELETE FROM `{$table}` WHERE `option_name` NOT LIKE '%_version%' AND ( `option_name` LIKE 'geodir_buddypress_%' OR `option_name` LIKE 'geodir_claim_%' OR `option_name` LIKE 'geodir_event_%' OR `option_name` LIKE 'geodir_franchise_%' OR `option_name` LIKE 'geodir_ga_%' OR `option_name` LIKE 'geodir_location_%' OR `option_name` LIKE 'geodir_payment_%' OR `option_name` LIKE 'geodir_recaptcha_%' OR `option_name` LIKE 'geodir_reviewrating_%' OR `option_name` LIKE 'geodir_search_%' OR `option_name` LIKE 'geodir_social_%' )" );

		// Core options
		$wpdb->query( "DELETE FROM `{$table}` WHERE `option_name` IN ( 'gd_place_dummy_data_type', 'gd_event_dummy_data_type', 'gd_term_icons', 'gd_theme_compat', 'gd_theme_compats', 'gdevents_installed', 'geodir_accept_term_condition', 'geodir_add_categories_url', 'geodir_add_list_page', 'geodir_add_listing_link_add_listing_nav', 'geodir_add_listing_link_user_dashboard', 'geodir_add_listing_mouse_scroll', 'geodir_add_listing_page', 'geodir_add_location_url', 'geodir_add_posttype_in_listing_nav', 'geodir_add_related_listing_posttypes', 'geodir_allow_cpass', 'geodir_allow_posttype_frontend', 'geodir_allow_wpadmin', 'geodir_author_desc_word_limit', 'geodir_author_view', 'geodir_autocompleter_autosubmit', 'geodir_autocompleter_matches_label', 'geodir_autocompleter_max_results', 'geodir_autocompleter_min_chars', 'geodir_checkout_page', 'geodir_coustem_css', 'geodir_custom_gmaps_detail', 'geodir_custom_gmaps_home', 'geodir_custom_gmaps_listing', 'geodir_desc_word_limit', 'geodir_detail_sidebar_left_section', 'geodir_email_enquiry_content', 'geodir_email_enquiry_subject', 'geodir_enable_autocompleter', 'geodir_enable_city', 'geodir_enable_country', 'geodir_enable_map_cache', 'geodir_enable_region', 'geodir_everywhere_in_city_dropdown', 'geodir_everywhere_in_country_dropdown', 'geodir_everywhere_in_region_dropdown', 'geodir_exclude_cat_on_map', 'geodir_exclude_cat_on_map_upgrade', 'geodir_exclude_post_type_on_map', 'geodir_favorite_link_user_dashboard', 'geodir_footer_scripts', 'geodir_forgot_password_content', 'geodir_forgot_password_subject', 'geodir_gd_booster_options', 'geodir_global_review_count', 'geodir_google_api_key', 'geodir_header_scripts', 'geodir_home_go_to', 'geodir_home_page', 'geodir_info_page', 'geodir_installed', 'geodir_invoices_page', 'geodir_lazy_load', 'geodir_load_map', 'geodir_login_page', 'geodir_map_onoff_dragging', 'geodir_marker_cluster_size', 'geodir_marker_cluster_type' )" );

		$wpdb->query( "DELETE FROM `{$table}` WHERE `option_name` IN ( 'geodir_marker_cluster_zoom', 'geodir_near_field_default_text', 'geodir_near_me_dist', 'geodir_new_post_default_status', 'geodir_notify_post_edited', 'geodir_notify_post_submit', 'geodir_page_title_add-listing', 'geodir_page_title_author', 'geodir_page_title_cat-listing', 'geodir_page_title_edit-listing', 'geodir_page_title_favorite', 'geodir_page_title_pt', 'geodir_page_title_tag-listing', 'geodir_pagination_advance_info', 'geodir_paid_listing_status', 'geodir_post_added_success_msg_content', 'geodir_post_types', 'geodir_post_types_claim_listing', 'geodir_preview_page', 'geodir_registration_success_email_content', 'geodir_registration_success_email_subject', 'geodir_related_post_count', 'geodir_related_post_excerpt', 'geodir_related_post_listing_view', 'geodir_related_post_location_filter', 'geodir_related_post_relate_to', 'geodir_related_post_sortby', 'geodir_remove_unnecessary_fields', 'geodir_remove_url_seperator', 'geodir_renew_email_content', 'geodir_renew_email_content2', 'geodir_renew_email_content3', 'geodir_renew_email_subject', 'geodir_renew_email_subject2', 'geodir_renew_email_subject3', 'geodir_review_count_force_update', 'geodir_selected_cities', 'geodir_selected_countries', 'geodir_selected_regions', 'geodir_success_page', 'geodir_tabs', 'geodir_taxonomies', 'geodir_term_condition_page', 'geodir_tiny_editor', 'geodir_tiny_editor_event_reg_on_add_listing', 'geodir_update_locations_default_options', 'geodir_upload_max_filesize', 'geodir_use_wp_media_large_size', 'skip_install_geodir_pages' )" );

		delete_option( 'geodir_post_types' );

		return true;
	}

	public function tool_extra_content( $action, $tool ) {
		global $geodir_tool_render;

		if ( empty( $geodir_tool_render ) ) {
			$geodir_tool_render = array();
		}

		if ( ! empty( $geodir_tool_render[ $action ] ) ) {
			return;
		}

		$geodir_tool_render[ $action ] = true;

		if ( $action == 'search_replace_cf' ) {
			$post_type = ! empty( $_REQUEST['srcf_pt'] ) ? sanitize_text_field( $_REQUEST['srcf_pt'] ) : '';
			$field = ! empty( $_REQUEST['srcf_cf'] ) ? sanitize_text_field( $_REQUEST['srcf_cf'] ) : '';
			$search = isset( $_REQUEST['srcf_s'] ) && $_REQUEST['srcf_s'] != "" ? sanitize_text_field( wp_unslash( $_REQUEST['srcf_s'] ) ) : '';
			$replace = isset( $_REQUEST['srcf_r'] ) && $_REQUEST['srcf_r'] != "" ? sanitize_text_field( wp_unslash( $_REQUEST['srcf_r'] ) ) : '';

			$output = '<div class="bsui">';
				$output .= '<div class="row mt-3">';
					$output .= '<div class="col col-md-3 col-sm-12">';
					$output .= aui()->select(
						array(
							'id' => 'gd_srcf_pt',
							'name' => 'gd_srcf_pt',
							'placeholder' => esc_html__( 'Select Post Type...', 'geodirectory' ),
							'title' => esc_html__( 'Post Type', 'geodirectory' ),
							'label' => esc_html__( 'Post Type', 'geodirectory' ),
							'label_type' => 'hidden',
							'value' => $post_type,
							'size' => 'sm',
							'options' => geodir_get_posttypes( 'options-plural' ),
							'required' => false
						)
					);
					$output .= '</div>';
					$output .= '<div class="col col-md-3 col-sm-12">';
					$output .= aui()->select(
						array(
							'id' => 'gd_srcf_cf',
							'name' => 'gd_srcf_cf',
							'placeholder' => esc_html__( 'Select Field...', 'geodirectory' ),
							'title' => esc_html__( 'Custom Field', 'geodirectory' ),
							'label' => esc_html__( 'Custom Field', 'geodirectory' ),
							'label_type' => 'hidden',
							'value' => $field,
							'size' => 'sm',
							'options' => $this->get_cf_with_option_values(),
							'required' => false
						)
					);
					$output .= '</div>';
					$output .= '<div class="col col-md-3 col-sm-12">';
					$output .= aui()->input(
						array(
							'id' => 'gd_srcf_s',
							'name' => 'gd_srcf_s',
							'type' => 'text',
							'placeholder' => esc_html__( 'Search Keyword', 'geodirectory' ),
							'title' => esc_html__( 'Search', 'geodirectory' ),
							'label' => esc_html__( 'Search', 'geodirectory' ),
							'label_type' => 'hidden',
							'value' => $search,
							'size' => 'sm',
							'required' => true
						)
					);
					$output .= '</div>';
					$output .= '<div class="col col-md-3 col-sm-12">';
					$output .= aui()->input(
						array(
							'id' => 'gd_srcf_r',
							'name' => 'gd_srcf_r',
							'type' => 'text',
							'placeholder' => esc_html__( 'Replace Keyword', 'geodirectory' ),
							'title' => esc_html__( 'Replace', 'geodirectory' ),
							'label' => esc_html__( 'Replace', 'geodirectory' ),
							'label_type' => 'hidden',
							'value' => $replace,
							'size' => 'sm',
							'required' => false
						)
					);
					$output .= '</div>';
				$output .= '</div>';
			$output .= '</div>';
			$output .= '<script type="text/javascript">jQuery(function() {jQuery("a.search_replace_cf").on("click", function(e){';
				$output .= 'e.preventDefault();var srcf = "";';
				$output .= 'if (!jQuery("select#gd_srcf_pt").val()) {jQuery("select#gd_srcf_pt").trigger("focus");return false;}srcf += "&srcf_pt=" + jQuery("select#gd_srcf_pt").val();';
				$output .= 'if (!jQuery("select#gd_srcf_cf").val()) {jQuery("select#gd_srcf_cf").trigger("focus");return false;}srcf += "&srcf_cf=" + jQuery("select#gd_srcf_cf").val();';
				$output .= 'if (!jQuery("input#gd_srcf_s").val() != "") {jQuery("input#gd_srcf_s").trigger("focus");return false;}srcf += "&srcf_s=" + jQuery("input#gd_srcf_s").val();';
				$output .= 'if (!jQuery("input#gd_srcf_r").val() != "") {jQuery("input#gd_srcf_r").trigger("focus");return false;}srcf += "&srcf_r=" + jQuery("input#gd_srcf_r").val();';
				$output .= 'window.location = "' . admin_url( 'admin.php?page=gd-status&tab=tools&action=search_replace_cf" + srcf + "&_wpnonce=' . esc_attr( wp_create_nonce( 'debug_action' ) ) ) . '";';
			$output .= '});});</script>';

			echo $output;
		}
	}

	public function search_replace_cf_value() {
		global $wpdb;

		$post_type = ! empty( $_REQUEST['srcf_pt'] ) ? sanitize_text_field( $_REQUEST['srcf_pt'] ) : '';
		$field = ! empty( $_REQUEST['srcf_cf'] ) ? sanitize_text_field( $_REQUEST['srcf_cf'] ) : '';
		$search = isset( $_REQUEST['srcf_s'] ) && $_REQUEST['srcf_s'] != "" ? sanitize_text_field( wp_unslash( $_REQUEST['srcf_s'] ) ) : '';
		$replace = isset( $_REQUEST['srcf_r'] ) && $_REQUEST['srcf_r'] != "" ? sanitize_text_field( wp_unslash( $_REQUEST['srcf_r'] ) ) : '';

		$found = 0;

		if ( geodir_is_gd_post_type( $post_type ) && ! empty( $field ) && ! empty( $search ) && geodir_column_exist( geodir_db_cpt_table( $post_type ), $field ) ) {
			$found = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `" . geodir_db_cpt_table( $post_type ) ."` WHERE FIND_IN_SET( `" . esc_sql( $field ) . "`, %s )", array( $search ) ) );
			if ( $found > 0 ) {
				$found = $wpdb->query( $wpdb->prepare( "UPDATE `" . geodir_db_cpt_table( $post_type ) ."` SET `" . esc_sql( $field ) . "` = TRIM( BOTH ',' FROM ( SELECT REPLACE( CONCAT( ',', `" . esc_sql( $field ) . "`, ',' ), %s, %s ) ) ) WHERE FIND_IN_SET( %s, `" . esc_sql( $field ) . "` )", array( ',' . $search . ',', ',' . $replace . ',', $search ) ) );
			}
		}

		return $found;
	}

	public function get_cf_with_option_values() {
		global $wpdb;

		$results = $wpdb->get_results( "SELECT DISTINCT `htmlvar_name`, `frontend_title`, `admin_title` FROM `" . GEODIR_CUSTOM_FIELDS_TABLE . "` WHERE `option_values` != '' AND `option_values` IS NOT NULL AND `field_type` IN ('select', 'multiselect', 'radio', 'checkbox') ORDER BY `admin_title` ASC" );

		$options = array();

		if ( ! empty( $results ) ) {
			foreach ( $results as $row ) {
				if ( ! empty( $row->htmlvar_name ) ) {
					$title = ! empty( $row->admin_title ) ? __( wp_unslash( $row->admin_title ), 'geodirectory' ) : __( wp_unslash( $row->frontend_title ), 'geodirectory' );

					$options[ $row->htmlvar_name ] = $title . ' (' . $row->htmlvar_name . ')';
				}
			}
		}

		return $options;
	}
}
