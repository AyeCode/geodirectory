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

	}

	/**
	 * A list of available tools for use in the system status section.
	 * 'button' becomes 'action' in the API.
	 *
	 * @return array
	 */
	public function get_tools() {
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
			'export_db_texts' => array(
				'name'    => __( 'DB text translation', 'geodirectory' ),
				'button'  => __( 'Run', 'geodirectory' ),
				'desc'    => __( 'This tool will collect any texts stored in the DB and put them in the file db-language.php so they can then be used to translate them by translations tools.', 'geodirectory' ),
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
					$string = str_replace( "'", "\'", $string );

					do_action( 'geodir_language_file_add_string', $string );

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
		$sql  = "SELECT admin_title, frontend_desc, frontend_title, clabels, required_msg, default_value, option_values, validation_msg FROM " . GEODIR_CUSTOM_FIELDS_TABLE;
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
}
