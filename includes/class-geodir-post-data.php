<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Post Data.
 *
 * Standardises certain post data on save.
 *
 * @class        GeoDir_Post_Data
 * @version        2.0.0
 * @package        GeoDirectory/Classes/Data
 * @category    Class
 * @author        AyeCode
 */
class GeoDir_Post_Data {

	/**
	 * Temporarily save the GD post data.
	 *
	 * @var array
	 */
	private static $post_temp = null;

	/**
	 * Editing term.
	 *
	 * @var object
	 */
	private static $editing_term = null;

	/**
	 * Hook in methods.
	 */
	public static function init() {

		add_filter( 'wp_insert_post_data', array( __CLASS__, 'wp_insert_post_data' ), 10, 2 );

		// Status transitions
		add_action( 'before_delete_post', array( __CLASS__, 'delete_post' ) );

		// Add hook to post insert
		add_action( 'save_post', array( __CLASS__, 'save_post' ), 10, 3 );

		// set up $gd_post;
		add_action( 'wp', array( __CLASS__, 'init_gd_post' ), 5 );
		add_action( 'the_post', array( __CLASS__, 'the_gd_post' ), 10, 2 );

		if ( ! is_admin() ) {
			add_filter( 'pre_get_posts', array( __CLASS__, 'show_public_preview' ) );

			add_filter( 'posts_results', array( __CLASS__, 'set_public_status' ), 999, 2 );
			add_filter( 'the_posts', array( __CLASS__, 'reset_public_status' ), 999, 2 );
		}

		// add mandatory not to add listing page
//		add_action( 'geodir_add_listing_form_start', array( __CLASS__, 'add_listing_mandatory_note' ), - 10, 3 ); // i don't really think we need this

		// add schema
		add_action( 'wp_head', array( __CLASS__, 'schema' ), 10 );

		// wp_restore_post_revision
		add_action( 'wp_restore_post_revision', array( __CLASS__, 'restore_post_revision' ), 10, 2 );

		// make GD post meta available through the standard get_post_meta() function if prefixed with `geodir_`
		add_filter( 'get_post_metadata', array( __CLASS__, 'dynamically_add_post_meta' ), 10, 4 );

		// Init
		add_action( 'pre_get_posts', array( __CLASS__, 'setup_guest_cookie' ), 1 );

		// Set embed post thumbnail.
		add_filter( 'embed_thumbnail_id', array( __CLASS__, 'embed_thumbnail_id' ), 20, 1 );

		// Transition post status.
		add_action( 'transition_post_status', array( __CLASS__, 'transition_post_status' ), 6, 3 );

		// GD post saved.
		add_action( 'geodir_post_saved', array( __CLASS__, 'on_gd_post_saved' ), 999, 4 );

		// Private Address
		add_filter( 'geodir_check_display_map', array( __CLASS__, 'check_display_map' ), 11, 2 );
		add_action( 'clean_post_cache', array( __CLASS__, 'on_clean_post_cache' ), 10, 2 );

		add_filter( 'geodir_extra_sanitize_textarea_field', array( __CLASS__, 'extra_sanitize_textarea_field' ), 10, 2 );
	}

	/**
	 * Make GD post meta available through the standard get_post_meta() function if prefixed with `geodir_`
	 *
	 * @param $metadata
	 * @param $object_id
	 * @param $meta_key
	 * @param $single
	 *
	 * @return bool|mixed|null|string
	 */
	public static function dynamically_add_post_meta( $metadata, $object_id, $meta_key, $single ) {
		if ( $meta_key && strpos( $meta_key, 'geodir_' ) === 0 ) { //strpos is faster and since we have to do it with every query we do the fast one first and the slower now only if we have to
			$meta_key = substr( $meta_key, 7 );

			global $post, $gd_post;

			// first check we have the info in the global (means no DB query)
			if ( ! empty( $gd_post->ID ) && $gd_post->ID == $object_id && isset( $gd_post->{$meta_key} ) ) {
				$metadata = $gd_post->{$meta_key};
			} else {
				$maybe_meta = geodir_get_post_meta( $object_id, $meta_key, $single );
				if ( ! empty( $maybe_meta ) ) {
					$metadata = $maybe_meta;
				}
			}
		}

		return $metadata;
	}

	/**
	 * Restore the post revision, here we are just restoring the post meta table info.
	 *
	 * @global int|null $geodir_post_author Post author.
	 * @global array    $geodir_post_before Array of previous posts.
	 *
	 * @param $post_id
	 * @param $revision_id
	 */
	public static function restore_post_revision( $post_id, $revision_id ) {
		global $wpdb, $geodir_post_author, $geodir_post_before;

		$post_type = get_post_type( $post_id );

		if ( geodir_is_gd_post_type( $post_type ) ) {
			$table = geodir_db_cpt_table( $post_type );

			// Backup the main row first
			$result = $wpdb->update(
				$table,
				array( 'post_id' => "-" . $post_id ),
				array( 'post_id' => $post_id ),
				array( '%d' ),
				array( '%d' )
			);

			// Restore the revision meta
			if ( $result ) {
				$post_status = get_post_status( $post_id );
				$result      = $wpdb->update(
					$table,
					array(
						'post_id'     => $post_id,
						'post_status' => $post_status
					),
					array( 'post_id' => $revision_id ),
					array(
						'%d',
						'%s'
					),
					array( '%d' )
				);

				// Set the old info as the revision info so it is then deleted with the revision
				if ( $result ) {
					$result = $wpdb->update(
						$table,
						array( 'post_id' => $revision_id ),
						array( 'post_id' => "-" . $post_id ),
						array( '%d' ),
						array( '%d' )
					);

					if ( $result ) {
						if ( empty( $geodir_post_before ) ) {
							$geodir_post_before = array();
						}

						// Set previous post data.
						$geodir_post_before[ $post_id ] = geodir_get_post_info( (int) $revision_id, false );

						// Save the revisions media values
						$temp_media = get_post_meta( $post_id, "__" . $revision_id, true );

						// Set post images
						if ( isset( $temp_media['post_images'] ) ) {
							$current_files = GeoDir_Media::get_field_edit_string( $post_id, 'post_images' );

							// If post_images data is the same then we just copy the original feature image data
							if ( $current_files == $temp_media['post_images'] ) {
								$old_featured_image = geodir_get_post_meta( $revision_id, 'featured_image' );

								if ( $old_featured_image ) {
									geodir_save_post_meta( $post_id, 'featured_image', $old_featured_image );
								}
							} else {
								$featured_image = self::save_files( $revision_id, $temp_media['post_images'], 'post_images', false, false );

								if ( ! empty( $featured_image ) ) {
									geodir_save_post_meta( $post_id, 'featured_image', $featured_image );
								}
							}
						}

						if ( isset( $temp_media['post_images'] ) ) {
							unset( $temp_media['post_images'] ); // Unset the post_images as we save it in another table.
						}

						// Process other attachments
						$file_fields = GeoDir_Media::get_file_fields( $post_type );
						if ( ! empty( $file_fields ) ) { // We have file fields
							foreach ( $file_fields as $key => $extensions ) {
								if ( isset( $temp_media[ $key ] ) ) { // Its a attachment
									self::save_files( $revision_id, $temp_media[ $key ], $key, false, false );
								}
							}
						}

						if ( is_wp_error( $result ) ) {
							return;
						}

						// If the post saved then do some house keeping.
						$user_id = get_current_user_id();
						if ( $geodir_post_author && $geodir_post_author != $user_id && ( $geodir_post_author == geodir_get_listing_author( $revision_id ) ) ) {
							if ( $user_id ) {
								$user_id .= ',' . $geodir_post_author;
							} else {
								$user_id = $geodir_post_author;
							}
						}

						if ( $user_id ) {
							self::remove_post_revisions( $post_id, $user_id );
							GeoDir_Comments::update_post_rating( $post_id, $post_type);
						}

						/*
						 * Handle GD post revision restore.
						 *
						 * @since 2.8.99
						 *
						 * @param int $post_id The post ID.
						 * @param int $revision_id The revision ID.
						 */
						do_action( 'geodir_post_revision_restored', $post_id, $revision_id );
					}
				}
			}
		}
	}

	/**
	 * Save post attachments.
	 *
	 * @since 2.0.0
	 *
	 * @param int $post_id Optional. Post id. Default 0.
	 * @param array $files Optional. Files. Array.
	 * @param string $field Optional. Field. Default null.
	 * @param bool $dummy Optional. Dummy. Default false.
	 *
	 * @return bool|null|string
	 */
	public static function save_files( $post_id = 0, $files = array(), $field = '', $dummy = false, $auto_save = '' ) {
		// Check post revision exists.
		if ( ! empty( $post_id ) && ! get_post_type( $post_id ) ) {
			return false;
		}

		if ( $auto_save === '' ) {
			$auto_save = defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ? true : false;
		}
		$revision     = wp_is_post_revision( $post_id );
		$main_post_id = $revision ? wp_get_post_parent_id( $post_id ) : $post_id;

		// check for changes, maybe we don't need to run the whole function if there are no changes
		$current_files = GeoDir_Media::get_field_edit_string( $main_post_id, $field );

		if ( stripslashes_deep( $current_files ) == stripslashes_deep( $files ) ) {
			return false;
		}

		// Re-assign revision images to parent if main save
		if ( $revision && ! $auto_save ) {
			$revision_id = absint( $post_id );
			GeoDir_Media::revision_to_parent( $main_post_id, $revision_id );
			$post_id = $main_post_id;
		}

		$featured_image = '';

		// if no post id then bail
		if ( ! $post_id ) {
			return null;
		}

		// If array is empty then we delete all files.
		if ( empty( $files ) ) {
			if ( GeoDir_Media::delete_files( $post_id, $field ) ) {
				return '';
			} else {
				return false;
			}
		} else {

			// convert to array if not already an array
			if ( ! is_array( $files ) ) {
				$files = explode( "::", $files );
			}

			$file_ids = array();

			foreach ( $files as $order => $file_string ) {
				$file_info = array();
				// check if the string contains more info
				if ( strpos( $file_string, '|' ) !== false ) {
					$file_info = explode( "|", $file_string );
				} else {
					$file_info[0] = $file_string;
				}

				/*
				 * $file_info[0] = file_url;
				 * $file_info[1] = file_id;
				 * $file_info[2] = file_title;
				 * $file_info[3] = file_caption;
				 */
				$file_url     = isset( $file_info[0] ) ? sanitize_text_field( $file_info[0] ) : '';
				$file_id      = ! empty( $file_info[1] ) ? absint( $file_info[1] ) : '';
				$file_title   = ! empty( $file_info[2] ) ? sanitize_text_field( $file_info[2] ) : '';
				$file_caption = ! empty( $file_info[3] ) ? sanitize_text_field( $file_info[3] ) : '';
				$approved     = $auto_save ? '-1' : 1; // we approve all files on save, not auto-save

				// check if we already have the file.
				if ( $file_url && $file_id ) { // we already have the image so just update the title, caption and order id
					// update the image
					$file       = GeoDir_Media::update_attachment( $file_id, $post_id, $field, $file_url, $file_title, $file_caption, $order, $approved );
					$file_ids[] = $file_id;
				} else { // its a new image we have to insert.
					// If doing import and its not a full url then add placeholder attachment OR when finds # in strat of the url.
					if ( ( defined( 'GEODIR_DOING_IMPORT' ) && ( ! geodir_is_full_url( $file_url ) || strpos( $file_url, '#' ) === 0 ) ) || ( ( strpos( $file_url, '#https://' ) === 0 || strpos( $file_url, '#http://' ) === 0 ) && GeoDir_API::is_rest() && geodir_get_option( 'rest_api_external_image' ) ) ) {
						// insert the image
						$file = GeoDir_Media::insert_attachment( $post_id, $field, $file_url, $file_title, $file_caption, $order, $approved, true );
					} else {
						// insert the image
						$file = GeoDir_Media::insert_attachment( $post_id, $field, $file_url, $file_title, $file_caption, $order, $approved );
					}
				}

				// check for error
				if ( is_wp_error( $file ) ) {
					// fail silently so the rest of the post data can be inserted
				} else {
					// its featured so assign it
					if ( $order == 0 && $field == 'post_images' && isset( $file['file'] ) ) {
						$featured_image = $file['file'];
					}
				}
			}

			// During import it don't deletes previous attachment when adding the new attachment.
			$delete_previous = empty( $file_ids ) && defined( 'GEODIR_DOING_IMPORT' ) ? true : false;

			// Check if there are any missing file ids we need to delete
			if ( ! empty( $current_files ) && ! empty( $files ) && ( ! empty( $file_ids ) || $delete_previous ) ) {
				$current_files_arr = explode( "::", $current_files );

				foreach ( $current_files_arr as $current_file ) {
					$current_file_arr = explode( "|", $current_file );

					if ( ! empty( $current_file_arr[1] ) && ( ! in_array( $current_file_arr[1], $file_ids ) || $delete_previous ) ) {
						GeoDir_Media::delete_attachment( (int) $current_file_arr[1], $post_id );
					}
				}
			}
		}

		return $featured_image;
	}

	/**
	 * Remove any old post revisions.
	 *
	 * @since 2.0.0
	 *
	 * @param int $post_id Post id.
	 * @param int|string $user_id User id.
	 */
	public static function remove_post_revisions( $post_id, $user_id ) {
		$posts = wp_get_post_revisions( $post_id, array( 'check_enabled' => false, 'author' => $user_id ) );

		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				if ( $post->ID ) {
					wp_delete_post( $post->ID, true );
					delete_post_meta( $post->post_parent, "__" . $post->ID ); // delete any temp stored media values from auto saves
				}
			}
		}
	}

	/**
	 * Init the global $gd_post variable.
	 */
	public static function init_gd_post() {
		global $post, $gd_post;

		if ( isset( $post->post_type ) && in_array( $post->post_type, geodir_get_posttypes() ) ) {
			$gd_post = geodir_get_post_info( $post->ID );
		}

	}

	/**
	 * Save auto draft.
	 *
	 * @since 2.0.0
	 *
	 * @param array $post_info {
	 *      An array for post info.
	 *
	 * @type string $ID Post id.
	 * }
	 */
	public static function save_auto_draft( $post_info ) {

		// check if we already have an auto draft
		if ( isset( $post_info['ID'] ) && $post_info['ID'] ) {

		}
		$result = wp_insert_post( $post_info, true ); // we hook into the save_post hook
	}

	/**
	 * Save post metadata when a post is saved.
	 *
	 * @since 2.0.0
	 *
	 * @param int $post_id The post ID.
	 * @param WP_Post $post The post object.
	 * @param bool $update Whether this is an existing post being updated or not.
	 */
	public static function save_post( $post_id, $post, $update ) {
		global $wpdb, $plugin_prefix, $geodirectory;

		// Non GD post
		if ( ! empty( $post->post_type ) && $post->post_type != 'revision' && ! geodir_is_gd_post_type( $post->post_type ) ) {
			return;
		}

		// only fire if $post_temp is set
		if ( $gd_post = self::$post_temp ) {
			$gd_post = apply_filters( 'geodir_save_post_temp_data', $gd_post, $post, $update );

//			$is_dummy = isset( $gd_post['post_dummy'] ) && $gd_post['post_dummy'] && isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'geodir_insert_dummy_data' ? true : false;
			$is_dummy = isset( $gd_post['post_dummy'] ) && $gd_post['post_dummy'] ? true : false;

			// POST REVISION :  grab the original info
			if ( isset( $gd_post['ID'] ) && $gd_post['ID'] === 0 && $gd_post['post_type'] == 'revision' ) {
				$gd_post = (array) geodir_get_post_info( $gd_post['post_parent'] );
			} elseif ( $gd_post['post_type'] == 'revision' ) {
				$gd_post['post_type'] = get_post_type( $gd_post['post_parent'] );
			}

			$post_type = esc_attr( $gd_post['post_type'] ); // set the post type early

			// unhook this function so it doesn't loop infinitely
			remove_action( 'save_post', array( __CLASS__, 'save_post' ), 10 );

			$postarr = array();
			$table   = $plugin_prefix . sanitize_key( $post_type ) . "_detail";


			// Set the custom fields info
			$custom_fields = GeoDir_Settings_Cpt_Cf::get_cpt_custom_fields( $post_type );
			foreach ( $custom_fields as $cf ) {

				if ( isset( $gd_post[ $cf->htmlvar_name ] ) ) {
					$gd_post_value = $gd_post[ $cf->htmlvar_name ];

					// check for empty numbers and set to NULL so a default 0 or 0.00 is not set
					if ( isset( $cf->data_type ) && ( $cf->data_type == 'DECIMAL' || $cf->data_type == 'INT' ) && $gd_post_value === '' ) {
						$gd_post_value = null;
					}

					$gd_post_value = apply_filters( "geodir_custom_field_value_{$cf->field_type}", $gd_post_value, $gd_post, $cf, $post_id, $post, $update );
					if ( is_array( $gd_post_value ) ) {
						$gd_post_value = ! empty( $gd_post_value ) ? implode( ',', $gd_post_value ) : '';
					}
					if ( ! empty( $gd_post_value ) ) {
						$gd_post_value = stripslashes_deep( $gd_post_value ); // stripslashes
					}

					$postarr[ $cf->htmlvar_name ] = $gd_post_value;
				}

			}

			// Set the defaults.
			$postarr['post_id']     = $post_id;
			$postarr['post_status'] = $post->post_status;
			if ( isset( $gd_post['featured'] ) ) {
				$postarr['featured'] = sanitize_text_field( $gd_post['featured'] );
			}
			if ( ! $update ) {
				$postarr['submit_ip'] = sanitize_text_field( $_SERVER['REMOTE_ADDR'] );
			}

			// unset the post content as we don't save it here
			unset( $postarr['post_content'] );


			//check for dummy data categories
			if ( $is_dummy && isset( $gd_post['post_category'] ) ) {
				$categories = array_map( 'sanitize_text_field', $gd_post['post_category'] );
				$cat_ids    = array();
				foreach ( $categories as $cat_name ) {
					$temp_term = get_term_by( 'name', $cat_name, $post_type . 'category' );
					if ( isset( $temp_term->term_id ) ) {
						$cat_ids[] = $temp_term->term_id;
					}
				}
				if ( ! empty( $cat_ids ) ) {
					$categories = $cat_ids;
				}
				$post_categories = array_map( 'trim', $categories );
				wp_set_post_terms( $post_id, $categories, $post_type . 'category' );
			}

			// Set categories
			if ( isset( $gd_post['tax_input'][ $post_type . 'category' ] ) && ! empty( $gd_post['tax_input'][ $post_type . 'category' ] ) ) {
				$post_categories = $gd_post['tax_input'][ $post_type . 'category' ];
			}
			if ( empty( $post_categories ) && isset( $gd_post['post_category'] ) ) {
				$post_categories = $gd_post['post_category'];
			}

			// default category
			if ( isset( $gd_post['default_category'] ) ) {
				$postarr['default_category'] = absint( $gd_post['default_category'] );
			}

			if ( isset( $post_categories ) ) {
				$post_categories = ! is_array( $post_categories ) ? array_filter( explode( ",", $post_categories ) ) : $post_categories;
				$categories      = array_map( 'absint', $post_categories );
				$categories      = array_filter( array_unique( $categories ) );// remove duplicates and empty values

				// if the listing has no cat try to set it as Uncategorized.
				if ( empty( $categories ) ) {
					$uncategorized = get_term_by( 'name', "Uncategorized", $post_type . 'category' );
					if ( isset( $uncategorized->term_id ) ) {
						$categories[] = $uncategorized->term_id;
						wp_set_post_terms( $post_id, $categories, $post_type . 'category' );
					}
				}

				if ( ! empty( $categories ) ) {
					$postarr['post_category'] = "," . implode( ",", $categories ) . ",";
					$default_category         = isset( $categories[0] ) ? $categories[0] : $categories[1];
				} else {
					$postarr['post_category'] = '';
					$default_category         = '';
				}

				if ( empty( $postarr['default_category'] ) && ! empty( $default_category ) ) {
					$postarr['default_category'] = $default_category; // set first category as a default if default category not found
				}

				// if logged out user we need to manually add cats
				if ( ! get_current_user_id() ) {
					wp_set_post_terms( $post_id, $categories, $post_type . 'category' );
				}
			}

			// Set tags

			// check for dummy data tags
			if ( empty( $gd_post['post_tags'] ) && isset( $gd_post['tax_input'][ $post_type . '_tags' ] ) && ! empty( $gd_post['tax_input'][ $post_type . '_tags' ] ) ) {

				// quick edit returns tag ids, we need the strings
				if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'inline-save' ) {
					$post_tags = isset( $_REQUEST['tax_input'][ $post_type . '_tags' ] ) ? sanitize_text_field( $_REQUEST['tax_input'][ $post_type . '_tags' ] ) : '';
					if ( $post_tags ) {
						$post_tags = explode( ",", $post_tags );
					}
				} else {
					$post_tags = $gd_post['tax_input'][ $post_type . '_tags' ];
				}

			} elseif ( isset( $gd_post['post_tags'] ) && is_array( $gd_post['post_tags'] ) ) {
				$post_tags = $gd_post['post_tags'];
			} else {
				$post_tags = '';
			}

			if ( $post_tags ) {

				if ( ! get_current_user_id() || $is_dummy ) {
					$tags = array_map( 'sanitize_text_field', $post_tags );
					$tags = array_map( 'trim', $tags );
					wp_set_post_terms( $post_id, $tags, $post_type . '_tags' );
				} else {
					$tag_terms = wp_get_object_terms( $post_id, $post_type . '_tags', array( 'fields' => 'names' ) ); // Save tag names in detail table.
					if ( ! empty( $tag_terms ) && ! is_wp_error( $tag_terms ) ) {
						$post_tags = $tag_terms;
					} else {
						$post_tags = array();
					}

					$tags = array_map( 'trim', $post_tags );
				}
				$tags = array_filter( array_unique( $tags ) );
				// we need tags as a string
				$postarr['post_tags'] = implode( ",", $tags );
			} else {
				// Save empty tags
				if ( ( isset( $gd_post['post_tags' ] ) || isset( $gd_post['tax_input'][ $post_type . '_tags' ] ) ) && empty( $gd_post['post_tags' ] ) && empty( $gd_post['tax_input'][ $post_type . '_tags' ] ) ) {
					$postarr['post_tags'] = '';
				}
			}

			// Save location info
			if ( isset( $gd_post['street'] ) ) {
				$postarr['street'] = sanitize_text_field( stripslashes( $gd_post['street'] ) );
			}
			if ( isset( $gd_post['street2'] ) ) {
				$postarr['street2'] = sanitize_text_field( stripslashes( $gd_post['street2'] ) );
			}
			if ( ! isset( $gd_post['city'] ) && isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'inline-save' ) {
				// if inline save then don't adjust the location info
			} elseif ( isset( $gd_post['city'] ) ) {
				$postarr['city'] = sanitize_text_field( stripslashes( $gd_post['city'] ) );
			} else {
				// Check if address is required
				$address_required = geodir_cpt_requires_address( $post_type );
				if ( ! $update && $address_required ) {
					$default_location   = $geodirectory->location->get_default_location();
					$postarr['city']    = stripslashes( $default_location->city );
					$postarr['region']  = stripslashes( $default_location->region );
					$postarr['country'] = stripslashes( $default_location->country );
				}
			}
			if ( isset( $gd_post['region'] ) ) {
				$postarr['region'] = sanitize_text_field( stripslashes( $gd_post['region'] ) );
			}
			if ( isset( $gd_post['country'] ) ) {
				$postarr['country'] = sanitize_text_field( stripslashes( $gd_post['country'] ) );
			}
			if ( isset( $gd_post['zip'] ) ) {
				$postarr['zip'] = sanitize_text_field( stripslashes( $gd_post['zip'] ) );
			}
			if ( isset( $gd_post['latitude'] ) ) {
				$postarr['latitude'] = sanitize_text_field( stripslashes( $gd_post['latitude'] ) );
			}
			if ( isset( $gd_post['longitude'] ) ) {
				$postarr['longitude'] = sanitize_text_field( stripslashes( $gd_post['longitude'] ) );
			}
			if ( isset( $gd_post['mapview'] ) ) {
				$postarr['mapview'] = sanitize_text_field( $gd_post['mapview'] );
			}
			if ( isset( $gd_post['mapzoom'] ) ) {
				$postarr['mapzoom'] = sanitize_text_field( $gd_post['mapzoom'] );
			}
			if ( isset( $gd_post['post_dummy'] ) ) {
				$postarr['post_dummy'] = $gd_post['post_dummy'];
			}


			// set post images
			$i_post_id = ! empty( $gd_post['revision_ID'] ) && wp_is_post_revision( absint( $gd_post['revision_ID'] ) ) === $post_id ? absint( $gd_post['revision_ID'] ) : $post_id;
			if ( isset( $gd_post['post_images'] ) && ! wp_is_post_revision( absint( $post_id ) ) ) {
				$featured_image = self::save_files( $i_post_id, $gd_post['post_images'], 'post_images', $is_dummy );

				if ( ! empty( $featured_image ) || $featured_image === '' ) {
					$postarr['featured_image'] = $featured_image;
				}
			}
			unset( $postarr['post_images'] ); // unset the post_images as we save it in another table.

			// process attachments
			$file_fields = GeoDir_Media::get_file_fields( $post_type );

			if ( ! empty( $file_fields ) ) {// we have file fields
				foreach ( $file_fields as $key => $extensions ) {
					if ( isset( $postarr[ $key ] ) ) { // its a attachment
						self::save_files( $i_post_id, $postarr[ $key ], $key );
					}
				}
			}

			// Copy post_title to _search_title.
			if ( isset( $postarr['post_title'] ) ) {
				$postarr['_search_title'] = geodir_sanitize_keyword( $postarr['post_title'], $post_type );
			}

			$postarr = apply_filters( 'geodir_save_post_data', $postarr, $gd_post, $post, $update );

			$format = array_fill( 0, count( $postarr ), '%s' );

			if ( $update ) { // Update in the database.
				$result = $wpdb->update(
					$table,
					$postarr,
					array( 'post_id' => $post_id ),
					$format
				);

				if ( false === $result && ! empty( $wpdb->last_error ) ) {
					geodir_error_log( wp_sprintf( __( 'Could not update post in the database. %s', 'geodirectory' ), $wpdb->last_error ) );
				}
			} else { // Insert in the database.
				$result = $wpdb->insert(
					$table,
					$postarr,
					$format
				);

				if ( false === $result && ! empty( $wpdb->last_error ) ) {
					geodir_error_log( wp_sprintf( __( 'Could not insert post into the database. %s', 'geodirectory' ), $wpdb->last_error ) );
				}
			}

			// Clear the post cache
			wp_cache_delete( "gd_post_" . $post_id, 'gd_post' );

			if ( $result ) {
				/**
				 * @since 2.0.0.95
				 */
				do_action( 'geodir_post_saved', $postarr, $gd_post, $post, $update );
			}

			/**
			 * @since 2.3.54
			 */
			do_action( 'geodir_after_post_save', $result, $postarr, $format, $gd_post, $post, $update );

			// re-hook this function
			add_action( 'save_post', array( __CLASS__, 'save_post' ), 10, 3 );
		}

		// clear the temp data so any further posts in the same request don't use it
		if ( isset( $post->post_type ) && $post->post_type == 'revision' && isset( $post->post_status ) && $post->post_status == 'inherit' ) {
			// we might be saving a revision AND THEN saving the main post so don't clear the temp data yet.
		} else {
			self::$post_temp = null;
		}
	}

	/**
	 * Not needed at present.
	 */
	public static function set_object_terms( $object_id, $terms, $tt_ids, $taxonomy, $append = false, $old_tt_ids = array() ) {

	}

	/**
	 * If is a GD post then save the post data to temp array for later `save_post` hook.
	 *
	 * @since 2.0.0
	 *
	 * @param array $data {
	 *      An array for post data.
	 *
	 * @type string $post_type post type.
	 * @type string $post_parent post parent.
	 * }
	 *
	 * @return array
	 */
	public static function wp_insert_post_data( $data, $postarr ) {
		// Non GD post
		if ( ! empty( $data['post_type'] ) && $data['post_type'] != 'revision' && ! geodir_is_gd_post_type( $data['post_type'] ) ) {
			return $data;
		}

		// Check its a GD CPT first
		if (
			( isset( $data['post_type'] ) && in_array( $data['post_type'], geodir_get_posttypes() ) )
			|| ( isset( $data['post_type'] ) && $data['post_type'] == 'revision' && in_array( get_post_type( $data['post_parent'] ), geodir_get_posttypes() ) && ( ! isset( self::$post_temp ) || empty( self::$post_temp ) ) )
		) {
			// If the post_category or tags_input are empty and not sent as a $_REQUEST we remove them so they don't blank values
			if ( empty( $postarr['post_category'] ) && ! isset( $_REQUEST['post_category'] ) && ! isset( $_REQUEST['tax_input'] ) ) {
				unset( $postarr['post_category'] );
			}

			if ( empty( $postarr['tags_input'] ) && ! isset( $_REQUEST['tags_input'] ) && ! isset( $_REQUEST['tax_input'] ) ) {
				unset( $postarr['tags_input'] );
			}

			if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'geodir_save_post' && empty( $postarr['tax_input'] ) && empty( $postarr['post_category'] ) && empty( $postarr['tags_input'] ) ) {
				unset( $postarr['post_category'] );
				unset( $postarr['tags_input'] );
			}

			// Assign the temp post data
			self::$post_temp = $postarr;

			if ( ! empty( $data['post_content'] ) ) {
				/**
				 * Set filter for textarea extra sanitization.
				 *
				 * @since 2.8.120
				 *
				 * @param string $post_content The post content.
				 * @param array  $args Args array.
				 */
				$data['post_content'] = apply_filters( 'geodir_extra_sanitize_textarea_field', $data['post_content'], array( 'default' => $data['post_content'], 'field_key' => 'post_content', 'postdata' => $data, 'postarr' => $postarr ) );
			}
		} else if ( ! empty( self::$post_temp ) && $data['post_type'] == 'revision' && isset( $data['post_parent'] ) && $data['post_parent'] == self::$post_temp['ID'] ) {
			// We might be saving a post revision at the same time so we don't blank the post_temp here
		} else {
			self::$post_temp = null;
		}

		return $data;
	}

	public static function update_post_meta() {

	}

	public static function get_post_autosave( $post_id ) {

	}

	/**
	 * Outputs the add listing form HTML content.
	 *
	 * Other things are needed to output a working add listing form, you should use the add listing shortcode if needed.
	 *
	 * @since 1.0.0
	 * @package GeoDirectory
	 * @global object $current_user Current user object.
	 * @global object $post The current post object.
	 * @global object $post_images Image objects of current post if available.
	 * @todo make the form work in sections with fieldsets, all collapsed apart from the one our on.
	 */
	public static function add_listing_form( $params = array() ) {
		global $aui_bs5, $cat_display, $post_cat, $current_user, $gd_post,$geodir_label_type;

		$page_id       = get_the_ID();
		$post          = '';
		$submit_button = '';
		$post_id       = '';
		$post_parent   = '';
		$user_notes    = array();

		$user_id = get_current_user_id();

		// if we have the post id.
		if ( $user_id && isset( $_REQUEST['pid'] ) && $_REQUEST['pid'] != '' ) {
			global $post;

			$post_id        = absint( $_REQUEST['pid'] );

			// check if user has privileges to edit the post
			$maybe_parent = wp_get_post_parent_id( $post_id  );
			$parent_id = $maybe_parent ? absint( $maybe_parent ) : '';
			if ( ! self::can_edit( $post_id, get_current_user_id(), $parent_id ) ) {
				echo self::output_user_notes( array( 'gd-error' => __( 'You do not have permission to edit this post.', 'geodirectory' ) ) );
				return;
			}

			$post           = $gd_post = geodir_get_post_info( $post_id );
			$listing_type   = $post->post_type;
			$post_revisions = wp_get_post_revisions( $post_id, array(
				'check_enabled' => false,
				'author'        => $user_id
			) );

			// if we have a post revision
			if ( ! empty( $post_revisions ) ) {
				$revision    = reset( $post_revisions );
				$post_parent = $post_id;
				$post_id     = absint( $revision->ID );
				$post        = $gd_post = geodir_get_post_info( $post_id );

				$user_notes['has-revision'] = sprintf( __( 'Hey, we found some unsaved changes from earlier and are showing them below. If you would prefer to start again then please %sclick here%s to remove this revision.', 'geodirectory' ), "<a href='javascript:void(0)' onclick='geodir_delete_revision();'>", "</a>" );

			} // create a post revision
			else {
				$revision_id = _wp_put_post_revision( $post );
				$post_parent = $post_id;
				$post_id     = absint( $revision_id );
				$post        = $gd_post = geodir_get_post_info( $post_id );
			}

		} // New post
		elseif ( isset( $_REQUEST['listing_type'] ) && $_REQUEST['listing_type'] != '' ) {

			$listing_type = sanitize_text_field( $_REQUEST['listing_type'] );
			$auto_drafts  = self::get_user_auto_drafts( $user_id, $listing_type );

			// if we have a user auto-draft then populate it
			if ( ! empty( $auto_drafts ) && isset( $auto_drafts[0] ) ) {
				$post        = $auto_drafts[0];
				$post_parent = $post_id;
				$post_id     = absint( $post->ID );
				$post        = $gd_post = geodir_get_post_info( $post_id );

				if ( $post->post_modified_gmt != '0000-00-00 00:00:00' ) {
					$user_notes['has-auto-draft'] = sprintf( __( 'Hey, we found a post you started earlier and are showing it below. If you would prefer to start again then please %sclick here%s to remove this revision.', 'geodirectory' ), "<a href='javascript:void(0)' onclick='geodir_delete_revision();'>", "</a>" );
				}
			} else {
				// Create the auto draft
				$post    = $gd_post = self::create_auto_draft( $listing_type );
				$post_id = absint( $post->ID );
				$post    = $gd_post = geodir_get_post_info( $post_id );
			}

		} else {
			echo '### a post type could not be determined.';

			return;
		}


		$post_type_info = geodir_get_posttype_info( $listing_type );

		$cpt_singular_name = ( isset( $post_type_info['labels']['singular_name'] ) && $post_type_info['labels']['singular_name'] ) ? __( $post_type_info['labels']['singular_name'], 'geodirectory' ) : __( 'Listing', 'geodirectory' );

		$package = geodir_get_post_package( $post, $listing_type );

		// user notes
		if ( ! empty( $user_notes ) ) {
			echo self::output_user_notes( $user_notes );
		}

		/*
		 * Create the security nonce, we also use this for logged out user preview.
		 */
		$security_nonce = wp_create_nonce( "geodir-save-post" );

		$design_style =  geodir_design_style();
		$horizontal = false;
		if($design_style){
			$horizontal = $geodir_label_type == 'horizontal' ? true : false;
		}

		// wrap class
		$wrap_class = geodir_build_aui_class($params);

		do_action( 'geodir_before_add_listing_form', $listing_type, $post, $package );
		?>
		<form name="geodirectory-add-post" id="geodirectory-add-post" class="<?php echo $wrap_class;?>"
		      action="<?php echo get_page_link( $post->ID ); ?>" method="post"
		      enctype="multipart/form-data">
			<input type="hidden" name="action" value="geodir_save_post"/>
			<input type="hidden" name="preview" value="<?php echo esc_attr( $listing_type ); ?>"/>
			<input type="hidden" name="post_type" value="<?php echo esc_attr( $listing_type ); ?>"/>
			<input type="hidden" name="post_parent" value="<?php echo esc_attr( $post_parent ); ?>"/>
			<input type="hidden" name="ID" value="<?php echo esc_attr( $post_id ); ?>"/>
			<input type="hidden" name="security"
			       value="<?php echo esc_attr( $security_nonce ); ?>"/>


			<?php if ( $page_id ) { ?>
				<input type="hidden" name="add_listing_page_id" value="<?php echo $page_id; ?>"/>
			<?php }
			if ( isset( $_REQUEST['pid'] ) && $_REQUEST['pid'] != '' ) { ?>
			<?php }

			if ( ! empty( $params['container'] ) ) {
				?>
				<input type="hidden" id="gd-add-listing-replace-container"
				       value="<?php echo esc_attr( $params['container'] ); ?>"/>
			<?php }

			do_action( 'geodir_add_listing_form_start', $listing_type, $post, $package );

			/*
			 * Add the register fields if no user_id
			 */
			if ( ! $user_id && geodir_get_option( "post_logged_out" ) && get_option( 'users_can_register' ) ) {

				if($design_style ){

					echo '<fieldset class="' . ( $aui_bs5 ? 'mb-3' : 'form-group' ) . '" id="geodir_fieldset_your_details">';
					echo '<h3 class="h3">'.__( "Your Details", "geodirectory" ).'</h3>';
					echo '</fieldset>';

					echo aui()->input(
						array(
							'id'                => "user_login",
							'name'              => "user_login",
							'required'          => true,
							'label'              => __("Name", 'geodirectory').' <span class="text-danger">*</span>',
							'label_type'       => !empty($geodir_label_type) ? $geodir_label_type : 'horizontal',
							'type'              => 'text',
//							'placeholder'       => esc_html__( $cf['placeholder_value'], 'geodirectory'),
							'class'             => '',
							'help_text'         => __("Enter your name.", 'geodirectory'),
						)
					);

					echo aui()->input(
						array(
							'id'                => "user_email",
							'name'              => "user_email",
							'required'          => true,
							'label'              => __("Email", 'geodirectory').' <span class="text-danger">*</span>',
							'label_type'       => !empty($geodir_label_type) ? $geodir_label_type : 'horizontal',
							'type'              => 'email',
//							'placeholder'       => esc_html__( $cf['placeholder_value'], 'geodirectory'),
							'class'             => '',
							'help_text'         => __("Enter your email address.", 'geodirectory'),
						)
					);
				}else{

				?>
				<h5 id="geodir_fieldset_details" class="geodir-fieldset-row" gd-fieldset="user_details"><?php _e( "Your Details", "geodirectory" ); ?></h5>

				<div id="user_login_row" class="required_field geodir_form_row clearfix gd-fieldset-details">
					<label><?php _e( "Name", "geodirectory" ); ?> <span>*</span></label>
					<input field_type="text" name="user_login" id="user_login" value="" type="text"
					       class="geodir_textfield">
					<span class="geodir_message_note"><?php _e( "Enter your name.", "geodirectory" ); ?></span>
					<span class="geodir_message_error"></span>
				</div>
				<div id="user_email_row" class="required_field geodir_form_row clearfix gd-fieldset-details">
					<label><?php _e( "Email", "geodirectory" ); ?> <span>*</span></label>
					<input field_type="text" name="user_email" id="user_email" value="" type="text"
					       class="geodir_textfield">
					<span class="geodir_message_note"><?php _e( "Enter your email address.", "geodirectory" ); ?></span>
					<span class="geodir_message_error"></span>
				</div>
				<?php

				}
			}

			/**
			 * Called at the very top of the add listing page form for frontend.
			 *
			 * This is called just before the "Enter Listing Details" text.
			 *
			 * @since 1.0.0
			 */
			do_action( 'geodir_before_detail_fields' );

			/**
			 * Filter details fieldset title.
			 *
			 * @since 2.0.0.68
			 *
			 * @param string $listing_type Listing type.
			 * @param object $post Post object.
			 * @param object $package Package object.
			 */
			$details_header = apply_filters( 'geodir_add_listing_details_header', __( 'Enter Listing Details', 'geodirectory' ), $listing_type, $post, $package );
			if ( $details_header != '' ) {

				if($design_style ) {
					$conditional_attrs = geodir_conditional_field_attrs( array( 'type' => 'fieldset' ), 'details', 'fieldset' );

					echo '<fieldset class="' . ( $aui_bs5 ? 'mb-3' : 'form-group' ) . '" id="geodir_fieldset_details"' . $conditional_attrs . '>';
					echo '<h3 class="h3">' . $details_header . '</h3>';
					echo '</fieldset>';
				}else {
					?>
					<h5 id="geodir_fieldset_details" class="geodir-fieldset-row" gd-fieldset="details"><?php echo $details_header; ?></h5>
					<?php
				}
			}
			/**
			 * Called at the top of the add listing page form for frontend.
			 *
			 * This is called after the "Enter Listing Details" text.
			 *
			 * @since 1.0.0
			 */
			do_action( 'geodir_before_main_form_fields' );


			geodir_get_custom_fields_html( $package->id, 'all', $listing_type );

			/**
			 * Called on the add listing page form for frontend just after the image upload field.
			 *
			 * @since 1.0.0
			 */
			do_action( 'geodir_after_main_form_fields' ); ?>

			<?php if ( ! self::skip_spamblocker() ) { ?>
				<!-- add captcha code -->
				<script>
					/*<!--<script>-->*/
					document.write('<inp' + 'ut type="hidden" id="geodir_sp' + 'amblocker_top_form" name="geodir_sp' + 'amblocker" value="64"/>');
				</script>
				<noscript aria-hidden="true">
					<div>
						<label><?php _e( 'Type 64 into this box', 'geodirectory' ); ?></label>
						<input type="text" id="geodir_spamblocker_top_form" <?php echo $design_style? 'class="d-none"' :'';?> name="geodir_spamblocker" value=""
						       maxlength="10"/>
					</div>
				</noscript>
				<input type="text" id="geodir_filled_by_spam_bot_top_form" <?php echo $design_style? 'class="d-none"' :'';?> name="geodir_filled_by_spam_bot" value=""
				       aria-label="<?php esc_attr_e( 'Type 64 into this box', 'geodirectory' ); ?>"/>
				<!-- end captcha code -->
			<?php } ?>
			<div id="geodir-add-listing-submit" class="geodir_form_row clear_both <?php echo $design_style && $horizontal? ( $aui_bs5 ? 'mb-3' : 'form-group' ) . ' row' :'';?>"
			     style="<?php echo $design_style ? '' :'padding:2px;text-align:center;';?>">

				<?php echo $design_style && $horizontal ? '<label class="  col-sm-2 col-form-label"></label>' :'';?>

				<?php echo $design_style && $horizontal ? '<div class="col-sm-10">' :'';?>

				<button type="submit" class="geodir_button <?php echo $design_style ? 'btn btn-primary' :'';?>">
					<?php echo apply_filters( 'geodir_add_listing_btn_text', __( 'Submit Listing', 'geodirectory' ) ); ?>
				</button>

				<?php
				/*
				 * Show the preview button is its set to show.
				 */
				if ( geodir_get_option( 'post_preview' ) ) {
					$preview_link = self::get_preview_link( $post );
					$preview_id   = ! empty( $post->post_parent ) ? $post->post_parent : $post->ID;
					$preview_class = $design_style ? 'btn btn-outline-primary' :'';
					/**
					 * Filter preview action text.
					 *
					 * @since 2.1.1.12
					 *
					 * @param string $preview_text Preview action text.
					 * @param int    $preview_id Preview id.
					 */
					$preview_text = apply_filters( 'geodir_add_listing_preview_btn_text', __( 'Preview Listing', 'geodirectory' ), $preview_id );
					$preview_action = "<a href='$preview_link' target='wp-preview-" . $preview_id . "' class='geodir_button geodir_preview_button $preview_class'>" . $preview_text . " <i class=\"fas fa-external-link-alt\" aria-hidden=\"true\"></i></a>";
					/**
					 * Filter preview action.
					 *
					 * @since 2.1.1.12
					 *
					 * @param string $preview_action Preview action.
					 * @param int    $preview_id Preview id.
					 * @param string $preview_link Preview link.
					 */
					echo apply_filters( 'geodir_add_listing_preview_action', $preview_action, $preview_id, $preview_link );
				}
				?>
				<span class="geodir_message_note"
				      style="padding-left:0px;"> <?php //_e( 'Note: You will be able to see a preview in the next page', 'geodirectory' ); ?></span>

				<?php echo $design_style && $horizontal? '</div>' :'';?>
			</div>
			<?php do_action( 'geodir_add_listing_form_end', $listing_type, $post, $package ); ?>
		</form>

		<?php


		do_action( 'geodir_after_add_listing_form', $listing_type, $post, $package );
		wp_reset_query();
	}

	/**
	 * Get the auto drafts for the user.
	 *
	 * @since 2.0.0
	 *
	 * @param string $user_id Optional. User id. Default null.
	 * @param string $post_type Optional. Post type. Default null.
	 * @param int $post_parent Optional. Post parent. Default 0.
	 *
	 * @return array $posts_array.
	 */
	public static function get_user_auto_drafts( $user_id = '', $post_type = '', $post_parent = 0 ) {

		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}


		if ( $user_id ) {
			$args        = array(
				'posts_per_page'   => - 1,
				'orderby'          => 'date',
				'order'            => 'DESC',
				'post_type'        => $post_type,
				'post_parent'      => $post_parent,
				'author'           => $user_id,
				'post_status'      => 'auto-draft',
				'suppress_filters' => true
			);
			$posts_array = get_posts( $args );
		} else {
			// if its a logged out user the add current nonce as post meta
			$current_nonce = sanitize_text_field( geodir_getcookie( '_gd_logged_out_post_author' ) );
			$args          = array(
				'posts_per_page'   => - 1,
				'orderby'          => 'date',
				'order'            => 'DESC',
				'post_type'        => $post_type,
				'meta_key'         => '_gd_logged_out_post_author',
				'meta_value'       => $current_nonce,
				'post_status'      => 'auto-draft',
				'suppress_filters' => true
			);
			$posts_array   = get_posts( $args );
		}

		//print_r($posts_array);echo '#####';exit;


		return $posts_array;
	}

	/**
	 * Create the auto draft and return the post object with the title blank.
	 *
	 * @since 2.0.0
	 *
	 * @param string $post_type Post type.
	 *
	 * @return object $post.
	 */
	public static function create_auto_draft( $post_type ) {
		require_once( ABSPATH . 'wp-admin/includes/post.php' );
		$post = get_default_post_to_edit( $post_type, true );

		// if its a logged out user the add current nonce as post meta
		if ( $post->post_author == 0 && ( $current_nonce = geodir_getcookie( '_gd_logged_out_post_author' ) ) ) {
			update_post_meta( $post->ID, '_gd_logged_out_post_author', $current_nonce );
		}

		$post->post_title = ''; // don't show title as "Auto Draft"
		return $post;
	}

	/**
	 * Output the add listing user notes.
	 *
	 * @since 2.0.0
	 *
	 * @param array $user_notes User notes.
	 *
	 * @return string $notes
	 */
	public static function output_user_notes( $user_notes ) {

		$design_style = geodir_design_style();
		/**
		 * Filters the add listing user notes.
		 *
		 * @since 2.0.0.59
		 *
		 * @param array $user_notes An array of user notes.
		 */
		$user_notes = apply_filters( 'geodir_post_output_user_notes', $user_notes );

		$notes = '';
		if ( ! empty( $user_notes ) ) {
			foreach ( $user_notes as $key => $user_note ) {
				if($design_style){
					$notes .= "<div class='gd-notification alert alert-info $key' role='alert'>";
					$notes .= $user_note;
					$notes .= "</div>";
				}else{
					$notes .= "<div class='gd-notification $key'>";
					$notes .= $user_note;
					$notes .= "</div>";
				}

			}
		}

		return $notes;
	}

	public static function skip_spamblocker() {
		if ( class_exists( 'FLBuilder' ) && isset( $_REQUEST['fl_builder'] ) ) {
			return true; // Skip spam blocker in Beaver Builder page edit mode.
		}

		return false;
	}

	/**
	 * Get the preview link for the post.
	 *
	 * @since 2.0.0
	 *
	 * @param $post
	 *
	 * @return null|string
	 */
	public static function get_preview_link( $post ) {

		$query_args = array();

		if ( isset( $post->post_parent ) && $post->post_parent ) {
			$query_args['preview_id']    = $post->post_parent;
			$query_args['preview_nonce'] = wp_create_nonce( 'post_preview_' . $post->post_parent );
			$post_id                     = $post->post_parent;
		} else {
			$post_id = $post->ID;
		}

		// logged out user check
		if ( empty( $post->post_author ) && ! get_current_user_id() ) {
			$query_args['preview'] = true;
		}

		return get_preview_post_link( $post_id, $query_args );
	}

	/**
	 * Delete the post revision.
	 *
	 * @since 2.0.0
	 *
	 * @param array $post_data {
	 *      An array for Post data.
	 *
	 * @type string $ID Post id.
	 * }
	 * @return bool|WP_Error
	 */
	public static function delete_revision( $post_data ) {
		if ( ! self::owner_check( $post_data['ID'], get_current_user_id() ) ) {
			return new WP_Error( 'gd-not-owner', __( "You do not own this post", "geodirectory" ) );
		}

		$result = wp_delete_post( $post_data['ID'], true );
		if ( ! empty( $post_data['post_parent'] ) ) {
			delete_post_meta( (int) $post_data['post_parent'], "__" . (int) $post_data['ID'] ); // Delete any temp stored media values from auto saves.
		}

		if ( $result == false ) {
			return new WP_Error( 'gd-delete-failed', __( "Delete revision failed.", "geodirectory" ) );
		} else {
			return true;
		}
	}

	/**
	 * Check if the user owns the post.
	 *
	 * @since 2.0.0
	 *
	 * @param int $post_id Post ID.
	 * @param int $user_id User ID.
	 *
	 * @return bool
	 */
	public static function owner_check( $post_id, $user_id ) {
		$owner = false;
		if ( ! $post_id ) {
			return false;
		}
		$author_id = get_post_field( 'post_author', $post_id );

		if ( ! $user_id ) {// check if the current nonce owns the post with no author
			$post_current_nonce = get_post_meta( $post_id, '_gd_logged_out_post_author', true );
			if ( $post_current_nonce && $post_current_nonce == geodir_getcookie( '_gd_logged_out_post_author' ) ) {
				$owner = true;
			}
		} elseif ( $author_id == $user_id ) {
			$owner = true;
		} elseif ( current_user_can( 'edit_others_posts' ) ) {
			$owner = true;
		}

		return $owner;
	}

	/**
	 * Try to get the preview id from the post parent id.
	 *
	 * @since 2.0.0
	 *
	 * @param int $parent_id Parent ID.
	 *
	 * @return int|null|string
	 */
	public static function get_post_preview_id( $parent_id ) {
		$parent_id = absint( $parent_id );
		if ( $parent_id ) {
			global $wpdb;
			$sql     = "SELECT $wpdb->posts.ID
					FROM $wpdb->posts
					WHERE 1=1
					AND $wpdb->posts.post_parent = %d
					AND $wpdb->posts.post_type = 'revision'
					AND (($wpdb->posts.post_status = 'inherit'))
					ORDER BY $wpdb->posts.post_date DESC, $wpdb->posts.ID DESC";
			$post_id = $wpdb->get_var( $wpdb->prepare( $sql, $parent_id ) );

			if ( $post_id ) {
				return $post_id;
			} else {
				return $parent_id;
			}

		}


	}

	/**
	 * Function to auto save a post if auto-draft or revision.
	 *
	 * @since 2.0.0
	 *
	 * @param array $post_data {
	 *      An array for post data.
	 *
	 * @type string $post_parent Post Parent.
	 * @type string $post_type Post Type.
	 * }
	 * @return int|WP_Error
	 */
	public static function auto_save_post( $post_data, $doing_autosave = true ) {

		// check if user has privileges to edit the post
		$post_id   = isset( $post_data['ID'] ) ? absint( $post_data['ID'] ) : '';
		$parent_id = isset( $post_data['post_parent'] ) ? absint( $post_data['post_parent'] ) : '';
		if ( ! self::can_edit( $post_id, get_current_user_id(), $parent_id ) ) {
			return new WP_Error( 'save_post', __( "You do not have the privileges to perform this action.", "geodirectory" ) );
		}

		// set that we are doing an auto save
		if ( ! defined( 'DOING_AUTOSAVE' ) ) {
			if ( $doing_autosave ) {
				define( 'DOING_AUTOSAVE', true );
			} else {
				define( 'DOING_AUTOSAVE', false );
			}
		}

		// its a post revision
		if ( isset( $post_data['post_parent'] ) && $post_data['post_parent'] ) {
			$post_data['post_type'] = 'revision'; //  post type is not sent but we know if it has a parent then its a revision.
			$post_data['post_name'] = $post_data['post_parent'] . "-autosave-v1";


			// save file temp info
			$file_meta = array();
			// set post images
			if ( isset( $post_data['post_images'] ) ) {
				$file_meta['post_images'] = $post_data['post_images'];
			}

//			// process attachments
			$post_type   = get_post_type( $post_data['post_parent'] );
			$file_fields = GeoDir_Media::get_file_fields( $post_type );

			if ( ! empty( $file_fields ) ) {// we have file fields
				foreach ( $file_fields as $key => $extensions ) {
					if ( isset( $post_data[ $key ] ) ) { // its a attachment
						$file_meta[ $key ] = $post_data[ $key ];
					}
				}
			}

			if ( ! empty( $file_meta ) ) {
				update_post_meta( $post_data['post_parent'], '__' . $post_data['ID'], $file_meta );
			}

		} // its a new auto draft
		else {
			/*
			 * Check if its a logged out user and if we have details to register the user
			 */
			$post_data = self::check_logged_out_author( $post_data );
		}

		$post_data = apply_filters( 'geodir_auto_save_post_data', $post_data );

		// Pre validation
		$validate = ! empty( $post_data['geodir_auto_save_post_error'] ) && is_wp_error( $post_data['geodir_auto_save_post_error'] ) ? $post_data['geodir_auto_save_post_error'] : true;
		$validate = apply_filters( 'geodir_validate_auto_save_post_data', $validate, $post_data, ! empty( $post_data['post_parent'] ), $doing_autosave );

		if ( is_wp_error( $validate ) ) {
			return $validate;
		}

		// Save the post.
		$result = wp_update_post( $post_data, true );

		// get the message response.
		if ( ! is_wp_error( $result ) ) {
			do_action( 'geodir_ajax_post_auto_saved', $post_data, ! empty( $post_data['post_parent'] ) );
		}

		return apply_filters( 'geodir_auto_post_save_message', $result, $post_data, ! empty( $post_data['post_parent'] ) );
	}

	/**
	 * Check if the user has privileges to edit the post.
	 *
	 * @since 2.0.0.58
	 *
	 * @param int $post_id Post ID.
	 * @param int $user_id User ID.
	 * @param int $parent_id Post parent ID.
	 *
	 * @return bool
	 */
	public static function can_edit( $post_id, $user_id, $parent_id = 0 ) {
		$owner = false;

		// check main post_id
		if ( ! $post_id ) {
			return false;
		}

		$author_id = get_post_field( 'post_author', $post_id );
		$post_type = '';

		// if we have a parent_id then we must do extra checks
		if ( $parent_id ) {
			// make sure the parent id is for the post id.
			if ( $parent_id != wp_get_post_parent_id( $post_id ) ) {
				// something is not right, bail.
				return false;
			}
			// set the author and post type from parent
			$author_id = get_post_field( 'post_author', $parent_id );
			$post_type = get_post_type( $parent_id );
		}

		if ( ! $post_type ) {
			$post_type = get_post_type( $post_id );
		}

		// check the post_type of the post being edited
		if ( ! in_array( $post_type, geodir_get_posttypes() ) ) {
			return false;
		}

		// if a post_type is being posted check that matches
		if ( ! empty( $_POST['post_type'] ) && $post_type != $_POST['post_type'] ) {
			return false;
		}

		if ( $author_id == $user_id ) {
			$owner = true;
		} elseif ( current_user_can( 'edit_others_posts' ) ) {
			$owner = true;
		} elseif ( ! $user_id ) {// check if the current nonce owns the post with no author
			$post_current_nonce = get_post_meta( $post_id, '_gd_logged_out_post_author', true );
			if ( $post_current_nonce && $post_current_nonce == geodir_getcookie( '_gd_logged_out_post_author' ) ) {
				$owner = true;
			}
		}

		return $owner;
	}

	/*
	 * Check if its a logged out user and if we have details to register the user
	 */
	public static function check_logged_out_author( $post_data ) {
		if ( ! get_current_user_id()
		     && geodir_get_option( "post_logged_out" )
		     && get_option( 'users_can_register' )
		     && isset( $post_data['user_login'] )
		     && isset( $post_data['user_email'] )
		     && $post_data['user_login']
		     && $post_data['user_email']
		) {
			$prev_post_author = isset( $post_data['post_author'] ) ? $post_data['post_author'] : 0;
			$user_name = preg_replace( '/\s+/', '', sanitize_user( $post_data['user_login'], true ) );
			$user_email = sanitize_email( $post_data['user_email'] );

			$error = '';
			if ( strlen( $user_name ) < 3 ) {
				$error = new WP_Error( 'geodir_invalid_username', __( 'User name must have atleast 3 characters.', 'geodirectory' ) );
			} elseif ( ! is_email( $user_email ) ) {
				$error = new WP_Error( 'geodir_invalid_user_email', __( 'Invalid user email address.', 'geodirectory' ) );
			} else {
				$user_id_from_username = username_exists( $user_name );
				$user_id_from_email = email_exists( $user_email );
				$post_author = 0;

				if ( $user_id_from_username && $user_id_from_email && $user_id_from_username == $user_id_from_email ) { // user already exists
					$post_author = $user_id_from_email;
				} elseif ( $user_id_from_email ) { // user exists from email
					$post_author = $user_id_from_email;
				} else { // register new user
					$user_name = geodir_generate_unique_username( $user_name );
					if ( empty( $user_name ) ) {
						$user_name = $user_email; // Use email as username
					}

					$random_password = wp_generate_password( $length = 12, $include_standard_special_chars = false );
					$user_id = wp_create_user( $user_name, $random_password, $user_email );

					if ( is_wp_error( $user_id ) ) {
						$error = $user_id;
					} elseif ( $user_id ) {
						$post_author = $user_id;
						update_user_option( $user_id, 'default_password_nag', true, true ); //Set up the Password change nag.
						do_action( 'register_new_user', $user_id ); // fire the new set registration action so the standard notifications are sent.
					} else {
						$error = new WP_Error( 'geodir_register_new_user', __( 'Something wrong! Fail to register a new user.', 'geodirectory' ) );
					}
				}

				if ( $post_author ) {
					$post_data['post_author'] = $post_author;

					// Check posts limit.
					$args = array( 'post_type' => $post_data['post_type'], 'post_author' => $post_author );
					if ( ! empty( $post_data['package_id'] ) ) {
						$args['package_id'] = (int) $post_data['package_id'];
					}

					$can_add_post = GeoDir_Post_Limit::user_can_add_post( $args, true );

					if ( is_wp_error( $can_add_post ) ) {
						$error = new WP_Error( 'add_listing_error', $can_add_post->get_error_message(), array( 'status' => 400 ) );
					}
				}
			}

			// Set error
			if ( ! empty( $error ) ) {
				$post_data['geodir_auto_save_post_error'] = $error;
			} else {
				/**
				 * Fires when user id assigned to post data for guest user.
				 *
				 * @sinc 2.0.0.71
				 *
				 * @param int $post_data['post_author'] The post author.
				 * @param array $post_data The post data.
				 * @param int $prev_post_author The previous post author.
				 */
				do_action( 'geodir_assign_logged_out_post_author', $post_data['post_author'], $post_data, $prev_post_author );
			}
		}

		return $post_data;
	}

	/**
	 * Save the post from frontend ajax.
	 *
	 * @since 2.0.0
	 *
	 * @param array $post_data {
	 *      An array for post data.
	 *
	 * @type string $post_parent Post Parent.
	 * @type string $ID Post ID.
	 * @type string $post_status Post status.
	 * @type string $user_login Post User login.
	 * @type string $user_email Post User email.
	 * @type string $post_author Post author.
	 * }
	 *
	 * @return int|WP_Error $result
	 */
	public static function ajax_save_post( $post_data ) {

		// Check if user has privileges to edit the post
		$post_id   = isset( $post_data['ID'] ) ? absint( $post_data['ID'] ) : '';
		$parent_id = isset( $post_data['post_parent'] ) ? absint( $post_data['post_parent'] ) : '';
		if ( ! self::can_edit( $post_id, get_current_user_id(), $parent_id ) ) {
			return new WP_Error( 'save_post', __( "You do not have the privileges to perform this action.", "geodirectory" ) );
		}

		// Check if address is required
		$post_type = isset( $post_data['post_type'] ) ? esc_attr( $post_data['post_type'] ) : '';
		$address_required = geodir_cpt_requires_address( $post_type );

		// Pre validation
		$has_error = false;
		if ( isset( $post_data['post_title'] ) && sanitize_text_field( $post_data['post_title'] ) == '' ) {
			$has_error = true;
			$field_title = __( 'Title', 'geodirectory' );
		} elseif ( $address_required && isset( $post_data['street'] ) && sanitize_text_field( $post_data['street'] ) == '' && isset( $post_data['post_type'] ) && GeoDir_Post_types::supports( sanitize_text_field( $post_data['post_type'] ), 'location' ) ) {
			$has_error = true;
			$field_title = __( 'Address', 'geodirectory' );
		} elseif ( isset( $post_data['cat_limit'] ) && isset( $post_data['post_type'] ) && isset( $post_data['tax_input'] ) && empty( $post_data['tax_input'][ $post_data['post_type'] . 'category' ][0] ) ) {
			$has_error = true;
			$field_title = __( 'Category', 'geodirectory' );
		}

		if ( $has_error ) {
			return new WP_Error( 'save_post', wp_sprintf( __( '%s is empty but is a mandatory field, please check and try again.', 'geodirectory' ), $field_title ) );
		}

		$validate = ! empty( $post_data['geodir_auto_save_post_error'] ) && is_wp_error( $post_data['geodir_auto_save_post_error'] ) ? $post_data['geodir_auto_save_post_error'] : true;
		$validate = apply_filters( 'geodir_validate_ajax_save_post_data', $validate, $post_data, ! empty( $post_data['post_parent'] ) );

		if ( is_wp_error( $validate ) ) {
			return $validate;
		}

		/**
		 * Allow to override the ajax save action
		 */
		$override = apply_filters( 'geodir_ajax_save_post_override', array(), $post_data );
		if ( ! empty( $override ) ) {
			do_action( 'geodir_ajax_post_saved', $post_data, ! empty( $post_data['post_parent'] ) );

			return $override;
		}

		//if its a revision we need to swap the post ids.
		if ( isset( $post_data['post_parent'] ) && $post_data['post_parent'] ) {
			$post_data['revision_ID'] = $post_data['ID'];
			$post_data['ID']          = $post_data['post_parent'];
		}

		// get current status
		$post_status = get_post_status( $post_data['ID'] );

		// new post
		if ( $post_status == 'auto-draft' ) {
			$post_data['post_status'] = geodir_new_post_default_status();

			/*
			 * Check if its a logged out user and if we have details to register the user
			 */
			$post_data = self::check_logged_out_author( $post_data );
		} else {
			$post_data['post_status'] = $post_status;
		}

		// Error
		if ( ! empty( $post_data['geodir_auto_save_post_error'] ) && is_wp_error( $post_data['geodir_auto_save_post_error'] ) ) {
			return $post_data['geodir_auto_save_post_error'];
		}

		/**
		 * @since 2.1.1.5
		 */
		$post_data = apply_filters( 'geodir_ajax_update_post_data', $post_data, ! empty( $post_data['post_parent'] ) );

		// Save the post.
		$result = wp_update_post( $post_data, true );

		// If the post saved then do some house keeping.
		if ( ! is_wp_error( $result ) && $user_id = get_current_user_id() ) {
			self::remove_post_revisions( $post_data['ID'], $user_id );
		}

		// get the message response.
		if ( ! is_wp_error( $result ) ) {
			do_action( 'geodir_ajax_post_saved', $post_data, ! empty( $post_data['post_parent'] ) );

			return self::ajax_save_post_message( $post_data );
		}

		return $result;

	}

	/**
	 * Get the message to display on ajax post save.
	 *
	 * @since 2.0.0
	 *
	 * @param array $post_data {
	 *      An array for post data.
	 *
	 * @type string $post_parent Post parent.
	 * @type string $post_status Post status.
	 * @type string $ID Post ID.
	 *
	 * }
	 * @return string
	 */
	public static function ajax_save_post_message( $post_data ) {
		$message = '';

		// if its an update.
		if ( isset( $post_data['post_parent'] ) && $post_data['post_parent'] ) {
			// live changes have been made.
			if ( in_array( $post_data['post_status'], geodir_get_publish_statuses( $post_data ) ) ) {
				$link    = get_permalink( $post_data['ID'] );
				$message = sprintf( __( 'Update received, your changes are now live and can be viewed %shere%s.', 'geodirectory' ), "<a href='$link' >", "</a>" );
			} // changes are not live
			else {
				$message = __( 'Update received, your changes may need to be reviewed before going live.', 'geodirectory' );
			}
		} // if its a new post.
		else {
			// post published
			if ( in_array( $post_data['post_status'], geodir_get_publish_statuses( $post_data ) ) ) {
				$link    = get_permalink( $post_data['ID'] );
				$message = sprintf( __( 'Post received, your listing is now live and can be viewed %shere%s.', 'geodirectory' ), "<a href='$link' >", "</a>" );
			} else {
				// post needs review
				$post         = new stdClass();
				$post->ID     = $post_data['ID'];
				$preview_link = self::get_preview_link( $post );
				$message      = sprintf( __( 'Post received, your listing may need to be reviewed before going live, you can preview it %shere%s.', 'geodirectory' ), "<a href='$preview_link' >", "</a>" );
			}
		}

		$message = apply_filters( 'geodir_ajax_save_post_message', $message, $post_data );

		return self::output_user_notes( array( 'gd-info' => $message ) );
	}

	/**
	 * Get the default status for new listings.
	 *
	 * @since 2.0.0
	 *
	 * @return mixed|string
	 */
	public static function get_post_default_status() {
		return geodir_get_option( 'default_status', 'publish' );
	}

	/**
	 * Removes the post meta and attachments.
	 *
	 * @since 2.0.0
	 *
	 * @global int|null $geodir_post_author Post author.
	 *
	 * @param int $id Post id.
	 *
	 * @return bool|void
	 */
	public static function delete_post( $id ) {
		global $wpdb, $plugin_prefix, $geodir_post_author;

		if ( empty( $id ) ) {
			return false;
		}

		// Check if user owns the post
		if ( ! ( self::owner_check( $id, get_current_user_id() ) || ( ! empty( $geodir_post_author ) && self::owner_check( $id, $geodir_post_author ) ) ) ) {
			return false;
		}

		// Check for multisite deletions
		if ( strpos( $plugin_prefix, $wpdb->prefix ) === false ) {
			return false;
		}

		$post_type = get_post_type( $id );

		// Check for revisions
		if ( $post_type == 'revision' ) {
			$post_type = get_post_type( wp_get_post_parent_id( $id ) );
		}

		if ( ! geodir_is_gd_post_type( $post_type ) ) {
			return false;
		}

		$table = $plugin_prefix . $post_type . '_detail';

		/* Delete custom post meta*/
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM " . $table . " WHERE `post_id` = %d",
				array( $id )
			)
		);

		/* Delete Attachments if not revision*/
		if ( ! wp_is_post_revision( absint( $id ) ) ) {
			GeoDir_Media::delete_files( $id, 'all' );
		}

		return true;
	}


######################## functions to show preview to logged out user ###########################

	/**
	 * Outputs the add listing page mandatory message.
	 *
	 * @since 1.0.0
	 * @package GeoDirectory
	 */
	public static function add_listing_mandatory_note( $listing_type = '', $post = array(), $package = array() ) {
		?><p class="geodir-note "><span class="geodir-required">*</span>
		&nbsp;<?php echo __( 'Indicates mandatory fields', 'geodirectory' ); ?></p><?php
	}

	/**
	 * Registers the filter to handle a public preview.
	 *
	 * Filter will be set if it's the main query, a preview, a singular page
	 * and the query var `_ppp` exists.
	 *
	 * @since 2.0.0
	 *
	 * @param object $query The WP_Query object.
	 *
	 * @return object The WP_Query object, unchanged.
	 */
	public static function show_public_preview( $query ) {
		if (
			$query->is_main_query() &&
			$query->is_preview() &&
			$query->is_singular()
		) {
			if ( ! headers_sent() ) {
				nocache_headers();
			}

			add_filter( 'posts_results', array( __CLASS__, 'set_post_to_publish' ), 10, 2 );
		}

		return $query;
	}

	/**
	 * Sets the post status of the first post to publish, so we don't have to do anything
	 * *too* hacky to get it to load the preview.
	 *
	 * @since 2.0.0
	 *
	 * @param  array $posts The post to preview.
	 *
	 * @return array The post that is being previewed.
	 */
	public static function set_post_to_publish( $posts ) {
		// Remove the filter again, otherwise it will be applied to other queries too.
		remove_filter( 'posts_results', array( __CLASS__, 'set_post_to_publish' ), 10 );

		if ( empty( $posts ) ) {
			return $posts;
		}

		$user_id = get_current_user_id();

		// Check id post has no author and if the current user owns it
		if (
			( ! $user_id && self::owner_check( $posts[0]->ID, 0 ) )
			|| ( ! isset( $_REQUEST['preview_nonce'] ) && $user_id && self::owner_check( $posts[0]->ID, $user_id ) )
			|| ( isset( $_GET['preview_id'] ) && isset( $_GET['preview_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_GET['preview_nonce'] ), 'post_preview_' . (int) $_GET['preview_id'] ) )
		) {
			$posts[0]->post_status = 'publish';

			// Disable comments and pings for this post.
			add_filter( 'comments_open', '__return_false' );
			add_filter( 'pings_open', '__return_false' );
		}

		return $posts;
	}

	/**
	 * Set closed status.
	 *
	 * @since 2.0.0
	 *
	 * @param object $posts Posts object.
	 * @param object $wp_query Wordpress query object.
	 *
	 * @return object $posts.
	 */
	public static function set_closed_status( $posts, $wp_query ) {
		global $wp_post_statuses, $gd_reset_closed;

		if ( isset( $wp_post_statuses['gd-closed'] ) && ! empty( $wp_query->is_single ) && ! empty( $posts ) && ! empty( $posts[0]->post_type ) && geodir_is_gd_post_type( $posts[0]->post_type ) && ! empty( $posts[0]->post_status ) && geodir_post_is_closed( $posts[0] ) ) {
			$wp_post_statuses['gd-closed']->public = true;
			$gd_reset_closed                       = true;
		}

		return $posts;
	}

	/**
	 * Reset closed status.
	 *
	 * @since 2.0.0
	 *
	 * @param object $posts Post object.
	 * @param object $wp_query Wordpress query object.
	 *
	 * @return object $posts.
	 */
	public static function reset_closed_status( $posts, $wp_query ) {
		global $wp_post_statuses, $gd_reset_closed;

		if ( $gd_reset_closed && isset( $wp_post_statuses['gd-closed'] ) ) {
			$wp_post_statuses['gd-closed']->public = false;
			$gd_reset_closed                       = false;
		}

		return $posts;
	}

	/**
	 * Set public status.
	 *
	 * @since 2.1.1.5
	 *
	 * @param object $posts Posts object.
	 * @param object $wp_query Wordpress query object.
	 *
	 * @return object $posts.
	 */
	public static function set_public_status( $posts, $wp_query ) {
		global $wp_post_statuses, $geodir_set_public;

		if ( ! empty( $wp_query->is_single ) && ! empty( $posts ) && ! empty( $posts[0]->post_type ) && geodir_is_gd_post_type( $posts[0]->post_type ) && ! empty( $posts[0]->post_status ) && isset( $wp_post_statuses[ $posts[0]->post_status ] ) && ( $non_public_statuses = geodir_get_post_stati( 'non-public', array( 'post_type' => $posts[0]->post_type ) ) ) ) {
			if ( in_array( $posts[0]->post_status, $non_public_statuses ) ) {
				$wp_post_statuses[ $posts[0]->post_status ]->public = true;
				$geodir_set_public = $posts[0]->post_status;
			}
		}

		return $posts;
	}

	/**
	 * Reset public status.
	 *
	 * @since 2.1.1.5
	 *
	 * @param object $posts Post object.
	 * @param object $wp_query Wordpress query object.
	 *
	 * @return object $posts.
	 */
	public static function reset_public_status( $posts, $wp_query ) {
		global $wp_post_statuses, $geodir_set_public;

		if ( $geodir_set_public && isset( $wp_post_statuses[ $geodir_set_public ] ) ) {
			$wp_post_statuses[ $geodir_set_public ]->public = false;
			$geodir_set_public = false;
		}

		return $posts;
	}

	/**
	 * Set global $gd_post data.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post $post The Post object (passed by reference).
	 * @param WP_Query $this The current Query object (passed by reference).
	 *
	 * @return WP_Post The Post object.
	 */
	public static function the_gd_post( $post, $wp_query = array() ) {
		global $gd_post;

		if ( ! empty( $post->post_type ) && in_array( $post->post_type, geodir_get_posttypes() ) ) {
			if ( ! ( ! empty( $gd_post ) && is_object( $gd_post ) && $gd_post->ID == $post->ID && isset( $post->post_category ) ) ) {
				$GLOBALS['gd_post'] = geodir_get_post_info( $post->ID );
			}
		}

		return $post;
	}

	/**
	 * Output the posts microdata in the source code.
	 *
	 * This micordata is used by things like Google as a standard way of declaring things like telephone numbers etc.
	 *
	 * @global bool $preview True of on a preview page. False if not.
	 * @global object $post The current post object.
	 *
	 * @param object $post Optional. The post object or blank.
	 */
	public static function schema( $post = '' ) {
		global $gd_post, $post;

		if ( ! geodir_is_page( 'detail' ) ) {
			return;
		}

		$can_see_address = isset( $gd_post->post_type ) && GeoDir_Post_types::supports( $gd_post->post_type, 'location' ) && geodir_user_can( 'see_private_address', array( 'post' => $gd_post ) ) ? true : false;

		// post reviews
		if ( empty( $gd_post->rating_count ) ) {
			$reviews = '';
		} else {
			$reviews = array();
			$post_reviews = get_comments( array( 'post_id' => $post->ID, 'status' => 'approve' ) );

			foreach ( $post_reviews as $review ) {
				if ( $rating_value = GeoDir_Comments::get_comment_rating( $review->comment_ID ) ) {
					$reviews[] = array(
						"@type"         => "Review",
						"author"        => array(
							"@type" => "Person",
							"name" => $review->comment_author
						),
						"datePublished" => $review->comment_date,
						"description"   => $review->comment_content,
						"reviewRating"  => array(
							"@type"       => "Rating",
							"bestRating"  => "5",
							// @todo this will need to be filtered for review manager if user changes the score.
							"ratingValue" => $rating_value,
							"worstRating" => "1"
						)
					);
				}
			}
		}

		// post images
		$post_images = geodir_get_images( $post->ID, '10' );

		if ( empty( $post_images ) ) {
			$images = array();
		} else {
			$_images = array();

			foreach ( $post_images as $attachment ) {
				$image_meta = maybe_unserialize( $attachment->metadata );

				if ( strpos( $attachment->file, '#' ) === 0 ) {
					$attachment->file = ltrim( $attachment->file, '#' );
				}

				$_image = array(
					"@type"                => "ImageObject",
					"author"               => ! empty( $attachment->user_id ) ? get_the_author_meta( 'display_name', $attachment->user_id ) : '',
					"contentLocation"      => isset( $gd_post->street ) ? $gd_post->street . ", " . $gd_post->city . ", " . $gd_post->country : '',
					"url"                  => geodir_get_image_src( $attachment, 'full' ),
					"datePublished"        => ! empty( $attachment->date_gmt ) && strpos( $attachment->date_gmt, '0000-00-00' ) === false ? $attachment->date_gmt : $post->post_date,
					"caption"              => stripslashes_deep( $attachment->caption ),
					"name"                 => stripslashes_deep( $attachment->title ),
					"representativeOfPage" => true,
					"thumbnail"            => geodir_get_image_src( $attachment, 'medium' )
				);

				// Don't show private address.
				if ( ! $can_see_address ) {
					unset( $_image['contentLocation'] );
				}

				$_images[] = $_image;
			}

			if ( count( $_images ) == 1 ) {
				$images = $_images[0];
			} else {
				$images = $_images;
			}
		}

		// external links
		$external_links = array();
		if ( ! empty( $gd_post->website ) ) {
			$external_links[] = $gd_post->website;
		}
		if ( ! empty( $gd_post->twitter ) ) {
			$external_links[] = $gd_post->twitter;
		}
		if ( ! empty( $gd_post->facebook ) ) {
			$external_links[] = $gd_post->facebook;
		}
		$external_links = array_filter( $external_links );

		if ( ! empty( $external_links ) ) {
			$external_links = array_values( $external_links );
		}

		// schema type
		$schema_type = 'LocalBusiness';
		if ( isset( $gd_post->default_category ) && $gd_post->default_category ) {
			$cat_schema = get_term_meta( $gd_post->default_category, 'ct_cat_schema', true );
			if ( $cat_schema ) {
				$schema_type = $cat_schema;
			}
			if ( ! $cat_schema && $schema_type == 'LocalBusiness' && $post->post_type == 'gd_event' ) {
				$schema_type = 'Event';
			}
		}

		$schema                = array();
		$schema['@context']    = "https://schema.org";
		$schema['@type']       = $schema_type;
		$schema['name']        = $post->post_title;
		$schema['description'] = wp_strip_all_tags( $post->post_content, true );
		if ( ! empty( $gd_post->phone ) ) {
			$schema['telephone'] = $gd_post->phone;
		}
		$schema['url']    = geodir_curPageURL();
		$schema['sameAs'] = $external_links;
		$schema['image']  = $images;

		if ( $can_see_address ) {
			$schema['address'] = array(
				"@type"           => "PostalAddress",
				"streetAddress"   => $gd_post->street,
				"addressLocality" => $gd_post->city,
				"addressRegion"   => $gd_post->region,
				"addressCountry"  => $gd_post->country,
				"postalCode"      => $gd_post->zip
			);
		}
		if ( ! empty( $gd_post->business_hours ) ) {
			$business_hours         = explode( ",[", $gd_post->business_hours );
			$business_hours         = isset( $business_hours[0] ) ? $business_hours[0] : $business_hours;
			$business_hours         = str_replace( array( '["', '"]' ), '', $business_hours );
			$business_hours         = explode( '","', $business_hours );
			$schema['openingHours'] = $business_hours;
		}

		if ( ! empty( $gd_post->latitude ) && ! empty( $gd_post->longitude ) && $can_see_address ) {
			$schema['geo'] = array(
				"@type"     => "GeoCoordinates",
				"latitude"  => $gd_post->latitude,
				"longitude" => $gd_post->longitude
			);
		}

		if ( $gd_post->overall_rating ) {
			$schema['aggregateRating'] = array(
				"@type"       => "AggregateRating",
				"ratingValue" => $gd_post->overall_rating,
				"bestRating"  => "5",
				// @todo this will need to be filtered for review manager if user changes the score.
				"worstRating" => "1",
				"ratingCount" => $gd_post->rating_count,
			);
		}
		$schema['review'] = $reviews;

		// PriceRange
		if ( ! empty( $gd_post->price_range ) ) {
			$schema['priceRange'] = esc_attr( $gd_post->price_range );
		}

		// VacationRental
		if ( 'VacationRental' === $schema_type ) {
			$schema['identifier'] = absint( $gd_post->ID );

			$accommodation = array();
			$accommodation['@type'] = "Accommodation";

			if ( ! empty( $gd_post->accommodates ) ) {
				$accommodation['occupancy'] = array(
					"@type"=> "QuantitativeValue",
					"value" => esc_attr( $gd_post->accommodates ),

				);
			}

			$schema['containsPlace'] = $accommodation;
		}

		/**
		 * Allow the schema JSON-LD info to be filtered.
		 *
		 * @since 1.5.4
		 * @since 1.5.7 Added $post variable.
		 *
		 * @param array $schema The array of schema data to be filtered.
		 * @param object $post The post object.
		 */
		$schema = apply_filters( 'geodir_details_schema', $schema, $post );

		echo '<script type="application/ld+json">' . json_encode( $schema ) . '</script>';

		$facebook_og = '';

		if( isset( $gd_post->featured_image ) && $gd_post->featured_image ){

			if(substr($gd_post->featured_image, 0, 4 ) === "http"){
				$image_url = esc_url_raw($gd_post->featured_image);
			}else{
				$uploads     = wp_upload_dir();
				$image_url = esc_url_raw($uploads['baseurl'] . $gd_post->featured_image);
			}
			$facebook_og = '<meta property="og:image" content="' . $image_url . '"/>';

		}

		/**
		 * Show facebook open graph meta info
		 *
		 * @since 1.6.6
		 *
		 * @param string $facebook_og The open graph html to be filtered.
		 * @param object $post The post object.
		 */
		echo apply_filters( 'geodir_details_facebook_og', $facebook_og, $post );
	}

	/**
	 * Displays the classes for the post container element.
	 *
	 * @since 2.0.0
	 *
	 * @param string|array $class One or more classes to add to the class list.
	 * @param int|WP_Post $post_id Optional. Post ID or post object. Defaults to the global `$post`.
	 */
	public static function post_class( $class = '', $post_id = null ) {
		// Separates classes with a single space, collates classes for post DIV
		echo 'class="' . join( ' ', self::get_post_class( $class, $post_id ) ) . '"';
	}

	/**
	 * Simplified version of the get_post_class() function.
	 *
	 * @param string $class
	 * @param null $post_id
	 *
	 * @return array
	 */
	public static function get_post_class( $class = '', $post_id = null ) {
		global $gd_post;
		$post = $gd_post;

		$classes = array();

		if ( $class ) {
			if ( ! is_array( $class ) ) {
				$class = preg_split( '#\s+#', $class );
			}
			$classes = array_map( 'esc_attr', $class );
		} else {
			// Ensure that we always coerce class to being an array.
			$class = array();
		}

		if ( ! $post ) {
			return $classes;
		}

		$classes[] = 'geodir-post';
		$classes[] = 'post-' . $post->ID;
		if ( ! is_admin() || wp_doing_ajax() ) {
			$classes[] = $post->post_type;
		}
		$classes[] = 'type-' . $post->post_type;
		$classes[] = 'status-' . $post->post_status;

		if ( ! empty( $post->post_password ) ) {
			$classes[] = 'post-password-protected';
		}

		// Post thumbnails.
		if ( ! empty( $gd_post->featured_image ) ) {
			$classes[] = 'has-post-thumbnail';
		}

		// sticky for Sticky Posts
		if ( is_sticky( $post->ID ) ) {
			if ( is_home() && ! is_paged() ) {
				$classes[] = 'sticky';
			} elseif ( is_admin() && ! wp_doing_ajax() ) {
				$classes[] = 'status-sticky';
			}
		}

		$classes = array_map( 'esc_attr', $classes );

		/**
		 * Filters the list of CSS class names for the current post.
		 *
		 * @since 2.7.0
		 *
		 * @param string[] $classes An array of post class names.
		 * @param string[] $class An array of additional class names added to the post.
		 * @param int $post_id The post ID.
		 */
		$classes = apply_filters( 'post_class', $classes, $class, $post->ID );

		return array_unique( $classes );
	}

	/**
	 * Setup guest cookie.
	 *
	 * @since 2.0.0.68
	 *
	 * @param string[] $classes An array of post class names.
	 * @param string[] $class An array of additional class names added to the post.
	 * @param int $post_id The post ID.
	 */
	public static function setup_guest_cookie() {
		if ( ! get_current_user_id() ) {
			$nonce = geodir_getcookie( '_gd_logged_out_post_author' );

			if ( empty( $nonce ) && geodir_is_page('add-listing') ) {
				$nonce = substr( wp_hash( time(), 'nonce' ), -12, 10 );
				geodir_setcookie( '_gd_logged_out_post_author', $nonce );
				$nonce = geodir_getcookie( '_gd_logged_out_post_author' );
			}
		}
	}

	/**
	 * Set the thumbnail image ID for use in the embed template.
	 *
	 * @since 2.0.0.85
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 * @global WP_Post $post The post object.
	 *
	 * @param int $thumbnail_id Attachment ID.
	 * @return int Attachment ID.
	 */
	public static function embed_thumbnail_id( $thumbnail_id ) {
		global $wpdb, $post;

		if ( ! empty( $post ) && geodir_is_gd_post_type( $post->post_type ) ) {
			$thumbnail_id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM `{$wpdb->postmeta}` WHERE meta_key = '_thumbnail_id' AND post_id = %d ORDER BY meta_id ASC LIMIT 1", $post->ID ) );
		}

		return $thumbnail_id;
	}

	/**
	 * Managing post status transition.
	 *
	 * @since 2.0.0.98
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param string  $new_status New post status.
	 * @param string  $old_status Previous post status.
	 * @param WP_Post $post       Post object.
	 */
	public static function transition_post_status( $new_status, $old_status, $post ) {
		// Handle publish future post via cron.
		if ( wp_doing_cron() && 'future' === $old_status && geodir_is_gd_post_type( $post->post_type ) && in_array( $new_status, geodir_get_publish_statuses( (array) $post ) ) ) {
			// Update post status in detail table.
			geodir_save_post_meta( $post->ID, 'post_status', $new_status );

			/**
			 * Handle future to publish post status transition.
			 *
			 * @since 2.0.0.98
			 *
			 * @param WP_Post $post Post object.
			 */
			do_action( 'geodir_future_to_publish_post', $post );

			$gd_post = geodir_get_post_info( $post->ID );

			if ( ! empty( $gd_post ) ) {
				// Send email to user
				GeoDir_Email::send_user_publish_post_email( $gd_post );
			}
		}
	}

	/**
	 * Handle things after GD post saved.
	 *
	 * @since 2.1.0.8
	 *
	 * @global array $geodir_post_published Post ids being published.
	 *
	 * @param array  $data Post data.
	 * @param array  $gd_post GD post array.
	 * @param object $post The post object.
	 * @param bool   $update True if post updated.
	 */
	public static function on_gd_post_saved( $data, $gd_post, $post, $update = false ) {
		global $geodir_post_published;

		if ( ! empty( $geodir_post_published ) && is_array( $geodir_post_published ) && ! empty( $post->ID ) && ! empty( $geodir_post_published[ $post->ID ] ) ) {
			unset( $geodir_post_published[ $post->ID ] );

			$gd_post = geodir_get_post_info( $post->ID );

			if ( ! empty( $gd_post ) ) {
				/**
				 * Set GD post published.
				 *
				 * @since 2.1.0.8
				 *
				 * @param object $gd_post GD post object.
				 * @param array  $data Post data.
				 */
				do_action( 'geodir_post_published', $gd_post, $data );
			}
		}
	}

	/**
	 * Whether display map or not.
	 *
	 * @since 2.1.1.9
	 *
	 * @param bool  $display The post.
	 * @param array $params Map parameters.
	 * @return bool True to display map, otherwise false.
	 */
	public static function check_display_map( $display, $params ) {
		if ( $display && ! empty( $params['map_type'] ) && $params['map_type'] == 'post' && ! empty( $params['posts'] ) ) {
			if ( ! geodir_user_can( 'see_private_address', array( 'post' => absint( $params['posts'] ) ) ) ) {
				$display = false;
			}
		}

		return $display;
	}

	/**
	 * Filters whether post have private address.
	 *
	 * @since 2.1.1.9
	 *
	 * @param int|object $post The post.
	 * @return string True when post has private address, otherwise false.
	 */
	public static function has_private_address( $post ) {
		global $geodir_private_address;

		if ( empty( $post ) ) {
			return false;
		}

		if ( is_array( $post ) ) {
			$gd_post = (object) $post;
		} else if( is_scalar( $post ) ) {
			$gd_post = geodir_get_post_info( absint( $post ) );
		} else {
			$gd_post = $post;
		}

		// Check for a valid post.
		if ( ! ( is_object( $gd_post ) && ! empty( $gd_post->ID ) && ! empty( $gd_post->post_type ) ) ) {
			return false;
		}

		$is_private = false;

		// Cache the value.
		if ( empty( $geodir_private_address ) || ! is_array( $geodir_private_address ) ) {
			$geodir_private_address = array();
		}

		if ( isset( $geodir_private_address[ $gd_post->ID ] ) ) {
			$is_private = $geodir_private_address[ $gd_post->ID ];
		} else {
			// Check private address enabled or not.
			if ( GeoDir_Post_types::supports( $gd_post->post_type, 'private_address' ) ) {
				if ( empty( $gd_post->post_id ) ) {
					$gd_post = geodir_get_post_info( $gd_post->ID );
				}

				if ( ! empty( $gd_post->private_address ) ) {
					$is_private = true;
				}
			}

			$geodir_private_address[ $gd_post->ID ] = $is_private;
		}

		/**
		 * Filters whether post have private address.
		 *
		 * @since 2.1.1.9
		 *
		 * @param bool   $is_private True when post has private address, otherwise false.
		 * @param object $gd_post The post.
		 */
		return apply_filters( 'geodir_post_has_private_address', $is_private, $gd_post );
	}

	/**
	 * Fires on post cache is cleaned.
	 *
	 * @since 2.3.5
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public static function on_clean_post_cache( $post_ID, $post ) {
		if ( ! empty( $post->post_type ) && geodir_is_gd_post_type( $post->post_type ) ) {
			// Flush widget listings cache.
			geodir_cache_flush_group( 'widget_listings_' . $post->post_type );
		}
	}

	/**
	 * Textarea field extra sanitization.
	 *
	 * @since 2.8.120
	 *
	 * @param array|string $content The content.
	 * @param array  $args Args array.
	 * @return mixed Sanitized content.
	 */
	public static function extra_sanitize_textarea_field( $content, $args = array() ) {
		if ( empty( $content ) ) {
			return $content;
		}

		/**
		 * Check to strip shortcodes for a given content.
		 *
		 * @since 2.9.120
		 *
		 * @param bool   $strip_shortcodes True to strip shortcodes.
		 * @param string $htmlvar_name Custom field name.
		 * @param string $value Field value.
		 * @param array  $args Extra args.
		 */
		$strip_shortcodes = apply_filters( 'geodir_textarea_field_strip_shortcodes', true, $content, $args );

		if ( $strip_shortcodes ) {
			if ( is_array( $content ) ) {
				$content = array_map( 'geodir_strip_shortcodes', $content );
			} else if ( is_scalar( $content ) ) {
				$content = geodir_strip_shortcodes( $content );
			}
		}

		return $content;
	}
}
