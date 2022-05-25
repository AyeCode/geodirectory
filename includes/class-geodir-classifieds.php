<?php
/**
 * GeoDir_Classifieds class
 *
 * @package GeoDirectory
 * @since   2.1.1.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * GeoDir_Classifieds class.
 */
class GeoDir_Classifieds {

	/**
	 * Setup.
	 */
	public static function init() {
		// Admin CPT settings
		add_filter( 'geodir_get_settings_cpt', array( __CLASS__, 'get_settings_cpt' ), 20, 3 );
		add_filter( 'geodir_custom_fields_predefined', array( __CLASS__, 'custom_fields_predefined' ), 20, 2 );

		add_filter( 'geodir_save_post_type', array( __CLASS__, 'sanitize_post_type' ), 10, 3 );
		add_action( 'geodir_post_type_saved', array( __CLASS__, 'post_type_saved' ), 10, 3 );
		add_action( 'geodir_pt_classified_features_changed', array( __CLASS__, 'on_pt_classified_features_changed' ), 10, 3 );
		add_filter( 'geodir_custom_field_input_select_sale_status', array( __CLASS__, 'input_sale_status' ), 10, 2 );
		add_filter( 'geodir_custom_field_output_select_var_sale_status', array( __CLASS__, 'output_sale_status' ), 10, 4 );

		add_filter( 'geodir_listing_custom_statuses', array( __CLASS__, 'set_custom_statuses' ), 10, 2 );
		add_filter( 'geodir_register_post_statuses', array( __CLASS__, 'register_post_statuses' ), 10, 1 );
		add_filter( 'geodir_get_publish_statuses', array( __CLASS__, 'filter_publish_statuses' ), 10, 2 );
		add_filter( 'geodir_get_post_stati', array( __CLASS__, 'filter_post_stati' ), 10, 3 );
		add_filter( 'geodir_author_actions', array( __CLASS__, 'author_actions' ), 9, 2 );
		add_filter( 'wp_head', array( __CLASS__, 'show_notifications' ) );
		add_filter( 'geodir_post_author_action_sale-agreed', array( __CLASS__, 'post_author_action' ), 10, 3 );
		add_filter( 'geodir_post_author_action_under-offer', array( __CLASS__, 'post_author_action' ), 10, 3 );
		add_filter( 'geodir_post_author_action_sold', array( __CLASS__, 'post_author_action' ), 10, 3 );
		add_filter( 'geodir_post_author_action_undo-sale-agreed', array( __CLASS__, 'post_author_action_undo' ), 10, 3 );
		add_filter( 'geodir_post_author_action_undo-under-offer', array( __CLASS__, 'post_author_action_undo' ), 10, 3 );
		add_filter( 'geodir_post_author_action_undo-sold', array( __CLASS__, 'post_author_action_undo' ), 10, 3 );
		add_filter( 'geodir_post_status_author_page', array( __CLASS__, 'author_post_status_title' ), 10, 3 );
		add_filter( 'geodir_post_status_icon_author_page', array( __CLASS__, 'author_post_status_icon' ), 10, 3 );
		add_filter( 'geodir_ajax_update_post_data', array( __CLASS__, 'ajax_update_post_data' ), 10, 2 );
	}

	public static function get_settings_cpt( $settings, $current_section = '', $post_type_values = array() ) {
		$post_type = ! empty( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : '';

		if ( ! empty( $settings ) ) {
			$new_settings = array();
			foreach ( $settings as $key => $setting ) {
				$new_settings[] = $setting;
				if ( ! empty( $setting['id'] ) && $setting['id'] == 'cpt_settings_page' && $setting['type'] == 'sectionend' ) {
					$classified_features = ! empty( $post_type_values['classified_features'] ) && is_array( $post_type_values['classified_features'] ) ? $post_type_values['classified_features'] : array();

					$new_settings[] = array(
						'title' => __( 'Classifieds/Real-Estate Sold Settings', 'geodirectory' ),
						'type' => 'title',
						'desc' => __( 'Add a sold functionality which would include the following listing statuses like sold, under offer, sale agreed etc.', 'geodirectory' ),
						'id' => 'cpt_settings_classifieds',
						'desc_tip' => true,
					);
					$new_settings[] = array(
						'type' => 'multiselect',
						'id' => 'classified_features',
						'name' => __( 'Enable Classifieds Features', 'geodirectory' ),
						'desc' => __( 'Select statuses to enable functionality for this post type. <span style="color:red;">(WARNING: disabling any status for the post type will move all existing posts to draft.)</span>', 'geodirectory' ),
						'placeholder' => __( 'Select features&hellip;', 'geodirectory' ),
						'options' => geodir_get_classified_statuses(),
						'class' => 'geodir-select',
						'advanced' => false,
						'desc_tip' => true,
						'value' => $classified_features
					);
					$new_settings[] = array(
						'name' => '',
						'desc' => '',
						'id' => 'prev_classified_features',
						'type' => 'hidden',
						'value' => implode( ',', $classified_features )
					);
					$new_settings[] = array( 
						'type' => 'sectionend', 
						'id' => 'cpt_settings_classifieds' 
					);
				}
			}
			$settings = $new_settings;
		}

		return $settings;
	}

	public static function sanitize_post_type( $data, $post_type, $request ) {
		if ( isset( $request['classified_features'] ) ) {
			$data[ $post_type ]['classified_features'] = ! empty( $request['classified_features'] ) && is_array( $request['classified_features'] ) ? geodir_clean( $request['classified_features'] ) : array();
			$data[ $post_type ]['prev_classified_features'] = ! empty( $request['prev_classified_features'] ) ? geodir_clean( $request['prev_classified_features'] ) : '';
		}

		return $data;
	}

	public static function post_type_saved( $post_type, $args, $new = false ) {
		if ( isset( $args['classified_features'] ) && isset( $args['prev_classified_features'] ) ) {
			$previous_features = ! empty( $args['prev_classified_features'] ) ? explode( ',', $args['prev_classified_features'] ) : array();
			$current_features = $args['classified_features'];

			if ( $previous_features != $current_features ) {
				do_action( 'geodir_pt_classified_features_changed', $post_type, $current_features, $previous_features );
			}
		}
	}

	public static function on_pt_classified_features_changed( $post_type, $current, $previous ) {
		global $wpdb;

		// Create custom fields.
		if ( empty( $previous ) && ! empty( $current ) ) {
			$fields = self::get_custom_fields( $post_type, 0 );

			if ( ! empty( $fields ) ) {
				$sort_order = (int) $wpdb->get_var( $wpdb->prepare( "SELECT MAX( sort_order ) FROM " . GEODIR_CUSTOM_FIELDS_TABLE . " WHERE post_type = %s LIMIT 1", array( $post_type ) ) );

				foreach ( $fields as $key => $field ) {
					$sort_order++;

					$field['sort_order'] = $sort_order;

					geodir_custom_field_save( $field );
				}
			}
		}

		// Change post status to draft.
		if ( ! empty( $previous ) ) {
			$table = geodir_db_cpt_table( $post_type );

			$features = geodir_register_classified_statuses();
			if ( ! empty( $current ) ) {
				foreach ( $current as $_current ) {
					if ( isset( $features[ $_current ] ) ) {
						unset( $features[ $_current ] );
					}
				}
			}

			if ( ! empty( $features ) ) {
				$features = array_keys( $features );
				$where = count( $features ) > 1 ? "post_status IN( '" . implode( "', '", $features ) . "' )" : "post_status = '" . $features[0] . "'";

				$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->posts} SET post_status = %s WHERE post_type = %s AND {$where}", array( 'draft', $post_type ) ) );
				$wpdb->query( $wpdb->prepare( "UPDATE {$table} SET post_status = %s WHERE {$where}", array( 'draft' ) ) );
			}
		}
	}

	public static function register_post_statuses( $statuses ) {
		$statuses = $statuses + geodir_register_classified_statuses();

		return $statuses;
	}

	public static function set_custom_statuses( $statuses, $post_type = '' ) {
		$statuses = $statuses + geodir_get_classified_statuses( $post_type );

		return $statuses;
	}

	public static function filter_publish_statuses( $statuses, $args ) {
		if ( ! empty( $args['post_type'] ) ) {
			$active_statuses = geodir_classified_active_statuses( $args['post_type'] );

			if ( ! empty( $active_statuses ) ) {
				$custom_statuses = geodir_register_classified_statuses();

				foreach ( $custom_statuses as $status => $data ) {
					if ( in_array( $status, $active_statuses ) && ( isset( $data['public'] ) && $data['public'] === true ) && ( isset( $data['exclude_from_search'] ) && $data['exclude_from_search'] !== true ) ) {
						$statuses[] = $status;
					}
				}
			}
		}

		return $statuses;
	}

	public static function filter_post_stati( $statuses, $context, $args ) {
		if ( ! empty( $args['post_type'] ) ) {
			if ( ! empty( $statuses ) ) {
				$active_statuses = geodir_classified_active_statuses( $args['post_type'] );
				$classified_statuses = array_keys( geodir_register_classified_statuses() );
				$_statuses = array();

				foreach ( $statuses as $status ) {
					if ( in_array( $status, $classified_statuses ) && ! in_array( $status, $active_statuses ) ) {
						continue;
					}
	
					$_statuses[] = $status;
				}

				$statuses = $_statuses;
			}
		}

		return $statuses;
	}

	public static function author_actions( $author_actions, $post_id ) {
		$post_status = get_post_status( $post_id );

		if ( ! in_array( $post_status, geodir_get_post_stati( 'unpublished', array( 'post_type' => get_post_type( $post_id ) ) ) ) ) {
			$features = geodir_classified_active_statuses( get_post_type( $post_id ) );

			if ( ! empty( $features ) ) {
				if ( in_array( 'gd-sale-agreed', $features ) ) {
					if ( $post_status != 'gd-sale-agreed' ) {
						$author_actions['sale-agreed'] = array(
							'icon' => 'fas fa-square',
							'title' => __( 'Mark as Sale Agreed', 'geodirectory' ),
							'url' => 'javascript:void(0);',
							'onclick' => 'geodir_post_author_action(this, ' . absint( $post_id ) . ', \'sale-agreed\');'
						);
					} else {
						$author_actions['undo-sale-agreed'] = array(
							'icon' => 'fas fa-undo',
							'title' => __( 'Undo Sale Agreed', 'geodirectory' ),
							'url' => 'javascript:void(0);',
							'onclick' => 'geodir_post_author_action(this, ' . absint( $post_id ) . ', \'undo-sale-agreed\');',
							'color' => 'warning'
						);
					}
				}

				if ( in_array( 'gd-under-offer', $features ) ) {
					if ( $post_status != 'gd-under-offer' ) {
						$author_actions['under-offer'] = array(
							'icon' => 'fas fa-square',
							'title' => __( 'Mark as Under Offer', 'geodirectory' ),
							'url' => 'javascript:void(0);',
							'onclick' => 'geodir_post_author_action(this, ' . absint( $post_id ) . ', \'under-offer\');'
						);
					} else {
						$author_actions['undo-under-offer'] = array(
							'icon' => 'fas fa-undo',
							'title' => __( 'Undo Under Offer', 'geodirectory' ),
							'url' => 'javascript:void(0);',
							'onclick' => 'geodir_post_author_action(this, ' . absint( $post_id ) . ', \'undo-under-offer\');',
							'color' => 'warning'
						);
					}
				}

				if ( in_array( 'gd-sold', $features ) ) {
					if ( $post_status != 'gd-sold' ) {
						$author_actions['sold'] = array(
							'icon' => 'fas fa-square',
							'title' => __( 'Mark as Sold', 'geodirectory' ),
							'url' => 'javascript:void(0);',
							'onclick' => 'geodir_post_author_action(this, ' . absint( $post_id ) . ', \'sold\');'
						);
					} else {
						$author_actions['undo-sold'] = array(
							'icon' => 'fas fa-undo',
							'title' => __( 'Undo Sold', 'geodirectory' ),
							'url' => 'javascript:void(0);',
							'onclick' => 'geodir_post_author_action(this, ' . absint( $post_id ) . ', \'undo-sold\');',
							'color' => 'warning'
						);
					}
				}
			}
		}

		return $author_actions;
	}

	public static function show_notifications() {
		global $geodirectory, $post, $gd_post;

		if ( empty( $geodirectory->notifications ) ) {
			return;
		}

		if ( geodir_is_page( 'single' ) ) {
			$features = geodir_register_classified_statuses();

			if ( ! empty( $post ) && ! empty( $post->post_status ) && ! empty( $features[ $post->post_status ]['notification'] ) ) {
				$name = geodir_strtolower( geodir_post_type_singular_name( $post->post_type ) );

				$geodirectory->notifications->add(
					'post_is_' . sanitize_key( $post->post_status ),
					array(
						'type' => 'warning',
						'note' => wp_sprintf( __( $features[ $post->post_status ]['notification'], 'geodirectory' ), $name )
					)
				);
			}
		}
	}

	public static function post_author_action( $data, $action, $gd_post ) {
		if ( ! geodir_listing_belong_to_current_user( $gd_post->ID ) ) {
			return new WP_Error( 'geodir-post-action-failed', __( 'You do not have permission to perform this action.', 'geodirectory' ) );
		}

		switch ( $action ) {
			case 'sale-agreed':
				$result = self::set_post_status( $gd_post, 'gd-sale-agreed' );

				if ( $result === false ) {
					$result = new WP_Error( 'geodir-post-action-failed', __( 'Failed to perform action "Mark as Sale Agreed".', 'geodirectory' ) );
				} else {
					$result = array(
						'message' => __( 'Post has been marked as Sale Agreed successfully.', 'geodirectory' ),
						'redirect_to' => true
					);
				}
				break;
			case 'under-offer':
				$result = self::set_post_status( $gd_post, 'gd-under-offer' );

				if ( $result === false ) {
					$result = new WP_Error( 'geodir-post-action-failed', __( 'Failed to perform action "Mark as Under Offer".', 'geodirectory' ) );
				} else {
					$result = array(
						'message' => __( 'Post has been marked as Under Offer successfully.', 'geodirectory' ),
						'redirect_to' => true
					);
				}
				break;
			case 'sold':
				$result = self::set_post_status( $gd_post, 'gd-sold' );

				if ( $result === false ) {
					$result = new WP_Error( 'geodir-post-action-failed', __( 'Failed to perform action "Mark as Sold".', 'geodirectory' ) );
				} else {
					$result = array(
						'message' => __( 'Post has been marked as Sold successfully.', 'geodirectory' ),
						'redirect_to' => true
					);
				}
				break;
		}

		return $result;
	}

	public static function post_author_action_undo( $data, $action, $gd_post ) {
		if ( ! geodir_listing_belong_to_current_user( $gd_post->ID ) ) {
			return new WP_Error( 'geodir-post-action-failed', __( 'You do not have permission to perform this action.', 'geodirectory' ) );
		}

		switch ( $action ) {
			case 'undo-sale-agreed':
				$result = self::undo_post_status( $gd_post, 'gd-sale-agreed' );

				if ( $result === false ) {
					$result = new WP_Error( 'geodir-post-action-failed', __( 'Failed to perform action "Undo Sale Agreed".', 'geodirectory' ) );
				} else {
					$result = array(
						'message' => __( 'Undo Sale Agreed is successful & post is marked as live.', 'geodirectory' ),
						'redirect_to' => true
					);
				}
				break;
			case 'undo-under-offer':
				$result = self::undo_post_status( $gd_post, 'gd-under-offer' );

				if ( $result === false ) {
					$result = new WP_Error( 'geodir-post-action-failed', __( 'Failed to perform action "Undo Under Offer".', 'geodirectory' ) );
				} else {
					$result = array(
						'message' => __( 'Undo Under Offer is successful & post is marked as live.', 'geodirectory' ),
						'redirect_to' => true
					);
				}
				break;
			case 'undo-sold':
				$result = self::undo_post_status( $gd_post, 'gd-sold' );

				if ( $result === false ) {
					$result = new WP_Error( 'geodir-post-action-failed', __( 'Failed to perform action "Undo Sold".', 'geodirectory' ) );
				} else {
					$result = array(
						'message' => __( 'Undo Sold is successful & post is marked as live.', 'geodirectory' ),
						'redirect_to' => true
					);
				}
				break;
		}

		return $result;
	}

	public static function set_post_status( $gd_post, $post_status ) {
		if ( ! empty( $gd_post ) && ! is_object( $gd_post ) ) {
			$gd_post = geodir_get_post_info( $gd_post );
		}

		if ( empty( $gd_post ) ) {
			return false;
		}

		// Check for revision post.
		if ( ! ( ! empty( $gd_post->post_type ) && geodir_is_gd_post_type( $gd_post->post_type ) ) ) {
			return false;
		}

		$features = geodir_classified_active_statuses( get_post_type( $gd_post->ID ) );

		if ( ! ( ! empty( $features ) && in_array( $post_status, $features ) ) ) {
			return false;
		}

		if ( ! empty( $post_status ) && $post_status != $gd_post->post_status && ! in_array( $post_status, geodir_get_post_stati( 'unpublished', (array) $gd_post ) ) ) {
			if ( apply_filters( 'geodir_skip_classified_set_status', false, $gd_post, $post_status ) ) {
				return false;
			}

			do_action( 'geodir_skip_classified_before_set_status', $gd_post, $post_status );

			$post_data = array();
			$post_data['ID'] = $gd_post->ID;
			$post_data['post_status'] = $post_status;

			$post_data = apply_filters( 'geodir_skip_classified_set_status_data', $post_data, $gd_post, $post_status );

			wp_update_post( $post_data );

			do_action( 'geodir_skip_classified_after_set_status', $gd_post, $post_status );

			return true;
		}

		return false;
	}

	public static function undo_post_status( $gd_post, $post_status ) {
		if ( ! empty( $gd_post ) && ! is_object( $gd_post ) ) {
			$gd_post = geodir_get_post_info( $gd_post );
		}

		if ( empty( $gd_post ) ) {
			return false;
		}

		// Check for revision post.
		if ( ! ( ! empty( $gd_post->post_type ) && geodir_is_gd_post_type( $gd_post->post_type ) ) ) {
			return false;
		}

		// Check the current post status.
		if ( $post_status != $gd_post->post_status ) {
			return false;
		}

		$features = geodir_classified_active_statuses( get_post_type( $gd_post->ID ) );

		if ( ! ( ! empty( $features ) && in_array( $post_status, $features ) ) ) {
			return false;
		}

		if ( apply_filters( 'geodir_skip_classified_undo_status', false, $gd_post, $post_status ) ) {
			return false;
		}

		do_action( 'geodir_skip_classified_before_undo_status', $gd_post, $post_status );

		$post_data = array();
		$post_data['ID'] = $gd_post->ID;
		$post_data['post_status'] = 'publish';
		$post_data['_from_post_status'] = $post_status;

		$post_data = apply_filters( 'geodir_skip_classified_undo_status_data', $post_data, $gd_post, $post_status );

		wp_update_post( $post_data );

		do_action( 'geodir_skip_classified_after_undo_status', $gd_post, $post_status );

		return true;
	}

	public static function author_post_status_title( $status, $real_status, $post_ID ) {
		$features = geodir_register_classified_statuses();

		if ( ! empty( $features[ $real_status ]['label'] ) ) {
			$status = $features[ $real_status ]['label'];
		}

		return $status;
	}

	public static function author_post_status_icon( $status_icon, $real_status, $post_ID ) {
		$features = geodir_register_classified_statuses();

		if ( ! empty( $features[ $real_status ]['icon'] ) ) {
			$status_icon = $features[ $real_status ]['icon'];
		}

		return $status_icon;
	}

	public static function get_custom_fields( $post_type, $package_id ) {
		$package = is_array( $package_id ) && ! empty( $package_id ) ? $package_id : ( $package_id !== '' ? array( $package_id ) : '' );

		$fields = array();
		// price
		$fields[] = array(
			'post_type' => $post_type,
			'field_type' => 'text',
			'data_type' => 'FLOAT',
			'decimal_point' => '2',
			'admin_title' => 'Price',
			'frontend_title' => 'Price',
			'frontend_desc' => 'Enter the price in $ (no currency symbol)',
			'htmlvar_name' => 'price',
			'is_active' => true,
			'for_admin_use' => false,
			'default_value' => '',
			'option_values' => '',
			'show_in' => '[detail],[listing]',
			'is_required' => false,
			'validation_pattern' => addslashes_gpc( '\d+(\.\d{2})?' ),
			'validation_msg' => 'Please enter number and decimal only ie: 100.50',
			'required_msg' => '',
			'field_icon' => 'fas fa-dollar-sign',
			'css_class' => '',
			'cat_sort' => true,
			'cat_filter' => true,
			'extra' => array(
				'is_price' => 1,
				'thousand_separator' => 'comma',
				'decimal_separator' => 'period',
				'decimal_display' => 'if',
				'currency_symbol' => '$',
				'currency_symbol_placement' => 'left'
			),
			'show_on_pkg' => $package,
			'clabels' => 'Price',
			'single_use' => true
		);

		// property status
		$fields[] = array(
			'post_type' => $post_type,
			'data_type' => 'VARCHAR',
			'field_type' => 'select',
			'field_type_key' => 'select',
			'is_active' => 1,
			'for_admin_use' => 0,
			'is_default' => 0,
			'admin_title' => __( 'Property Status', 'geodirectory' ),
			'frontend_desc' => __( 'Enter the status of the property.', 'geodirectory' ),
			'frontend_title' => __( 'Property Status', 'geodirectory' ),
			'htmlvar_name' => 'property_status',
			'default_value' => '',
			'is_required' => '1',
			'required_msg' => '',
			'show_in' => '[detail],[listing]',
			'show_on_pkg' => $package,
			'option_values' => 'Select Status/,For Sale,Sold,Under Offer',
			'field_icon' => 'fas fa-home',
			'css_class' => '',
			'cat_sort' => 1,
			'cat_filter' => 1,
			'clabels' => 'Property Status',
			'single_use' => true
		);

		// property furnishing
		$fields[] = array(
			'post_type' => $post_type,
			'field_type' => 'select',
			'data_type' => 'VARCHAR',
			'admin_title' => __( 'Furnishing', 'geodirectory' ),
			'frontend_title' => __( 'Furnishing', 'geodirectory' ),
			'frontend_desc' => __( 'Enter the furnishing status of the property.', 'geodirectory' ),
			'htmlvar_name' => 'property_furnishing',
			'is_active' => true,
			'for_admin_use' => false,
			'default_value' => '',
			'show_in' => '[detail],[listing]',
			'is_required' => true,
			'option_values' => 'Select Status/,Unfurnished,Furnished,Partially furnished,Optional',
			'validation_pattern' => '',
			'validation_msg' => '',
			'required_msg' => '',
			'field_icon' => 'fas fa-th-large',
			'css_class' => '',
			'cat_sort' => true,
			'cat_filter' => true,
			'show_on_pkg' => $package,
			'clabels' => 'Furnishing',
			'single_use' => true
		);

		// property type
		$fields[] = array(
			'post_type' => $post_type,
			'field_type' => 'select',
			'data_type' => 'VARCHAR',
			'admin_title' => __( 'Property Type', 'geodirectory' ),
			'frontend_title' => __( 'Property Type', 'geodirectory' ),
			'frontend_desc' => __( 'Select the property type.', 'geodirectory' ),
			'htmlvar_name' => 'property_type',
			'is_active' => true,
			'for_admin_use' => false,
			'default_value' => '',
			'show_in' => '[detail],[listing]',
			'is_required' => true,
			'option_values' => 'Select Type/,Detached house,Semi-detached house,Apartment,Bungalow,Semi-detached bungalow,Chalet,Town House,End-terrace house,Terrace house,Cottage,Hotel,Land',
			'validation_pattern' => '',
			'validation_msg' => '',
			'required_msg' => '',
			'field_icon' => 'fas fa-home',
			'css_class' => '',
			'cat_sort' => true,
			'cat_filter' => true,
			'show_on_pkg' => $package,
			'clabels' => 'Property Type',
			'single_use' => true
		);

		// property bedrooms
		$fields[] = array(
			'post_type' => $post_type,
			'field_type' => 'select',
			'data_type' => 'VARCHAR',
			'admin_title' => __( 'Property Bedrooms', 'geodirectory' ),
			'frontend_title' => __( 'Bedrooms', 'geodirectory' ),
			'frontend_desc' => __( 'Select the number of bedrooms', 'geodirectory' ),
			'htmlvar_name' => 'property_bedrooms',
			'is_active' => true,
			'for_admin_use' => false,
			'default_value' => '',
			'show_in' => '[detail],[listing]',
			'is_required' => true,
			'option_values' => 'Select Bedrooms/,1,2,3,4,5,6,7,8,9,10',
			'validation_pattern' => '',
			'validation_msg' => '',
			'required_msg' => '',
			'field_icon' => 'fas fa-bed',
			'css_class' => '',
			'cat_sort' => true,
			'cat_filter' => true,
			'show_on_pkg' => $package,
			'clabels' => 'Property Bedrooms',
			'single_use' => true
		);

		// property bathrooms
		$fields[] = array(
			'post_type' => $post_type,
			'field_type' => 'select',
			'data_type' => 'VARCHAR',
			'admin_title' => __( 'Property Bathrooms', 'geodirectory' ),
			'frontend_title' => __( 'Bathrooms', 'geodirectory' ),
			'frontend_desc' => __( 'Select the number of bathrooms', 'geodirectory' ),
			'htmlvar_name' => 'property_bathrooms',
			'is_active' => true,
			'for_admin_use' => false,
			'default_value' => '',
			'show_in' => '[detail],[listing]',
			'is_required' => true,
			'option_values' => 'Select Bathrooms/,1,2,3,4,5,6,7,8,9,10',
			'validation_pattern' => '',
			'validation_msg' => '',
			'required_msg' => '',
			'field_icon' => 'fas fa-bold',
			'css_class' => '',
			'cat_sort' => true,
			'cat_filter' => true,
			'show_on_pkg' => $package,
			'clabels' => 'Property Bathrooms',
			'single_use' => true
		);

		// property area
		$fields[] = array(
			'post_type' => $post_type,
			'field_type' => 'text',
			'data_type' => 'INT',
			'admin_title' => __( 'Property Area', 'geodirectory' ),
			'frontend_title' => __( 'Area (Sq Ft)', 'geodirectory' ),
			'frontend_desc' => __( 'Enter the Sq Ft value for the property', 'geodirectory' ),
			'htmlvar_name' => 'property_area',
			'is_active' => true,
			'for_admin_use' => false,
			'default_value' => '',
			'show_in' => '[detail],[listing]',
			'is_required' => false,
			'validation_pattern' => addslashes_gpc('\d+(\.\d{2})?' ), // add slashes required
			'validation_msg' => 'Please enter the property area in numbers only: 1500',
			'required_msg' => '',
			'field_icon' => 'fas fa-chart-area',
			'css_class' => '',
			'cat_sort' => true,
			'cat_filter' => true,
			'show_on_pkg' => $package,
			'clabels' => 'Property Area',
			'single_use' => true
		);

		// property features
		$fields[] = array(
			'post_type' => $post_type,
			'field_type' => 'multiselect',
			'data_type' => 'VARCHAR',
			'admin_title' => __( 'Property Features', 'geodirectory' ),
			'frontend_title' => __( 'Features', 'geodirectory' ),
			'frontend_desc' => __( 'Select the property features.', 'geodirectory' ),
			'htmlvar_name' => 'property_features',
			'is_active' => true,
			'for_admin_use' => false,
			'default_value' => '',
			'show_in' => '[detail],[listing]',
			'is_required' => false,
			'option_values' => 'Gas Central Heating,Oil Central Heating,Double Glazing,Triple Glazing,Front Garden,Garage,Private driveway,Off Road Parking,Fireplace',
			'validation_pattern' => '',
			'validation_msg' => '',
			'required_msg' => '',
			'field_icon' => 'fas fa-plus-square',
			'css_class' => 'gd-comma-list',
			'cat_sort' => true,
			'cat_filter' => true,
			'show_on_pkg' => $package,
			'clabels' => 'Property Features',
			'single_use' => true
		);

		$features = geodir_register_classified_statuses();
		$options = 'Select Status/';
		foreach ( $features as $value => $feature ) {
			$options .= ',' . $feature['label'] . '/' . $value;
		}

		// sale_status
		$fields[] = array(
			'post_type' => $post_type,
			'field_type' => 'select',
			'data_type' => 'VARCHAR',
			'admin_title' => __( 'Sale Status', 'geodirectory' ),
			'frontend_title' => __( 'Sale Status', 'geodirectory' ),
			'frontend_desc' => __( 'Select a sale status of the listing.', 'geodirectory' ),
			'placeholder_value'  => __( 'Select Status', 'geodirectory' ),
			'htmlvar_name' => 'sale_status',
			'is_active' => true,
			'for_admin_use' => false,
			'default_value' => '',
			'show_in' => '[detail],[listing]',
			'is_required' => false,
			'option_values' => $options,
			'validation_pattern' => '',
			'validation_msg' => '',
			'required_msg' => '',
			'field_icon' => 'far fa-pause-circle',
			'css_class' => 'gd-sale-status',
			'cat_sort' => false,
			'cat_filter' => true,
			'show_on_pkg' => $package,
			'clabels' => 'Sale Status',
			'single_use' => true
		);

		return apply_filters( 'geodir_classified_custom_fields', $fields, $post_type, $package );
	}

	public static function ajax_update_post_data( $post_data, $update = false ) {
		if ( isset( $_POST['sale_status'] ) ) {
			if ( ! empty( $_POST['sale_status'] ) ) {
				if ( isset( $post_data['post_status'] ) && $post_data['post_status'] == $_POST['sale_status'] ) {
					return $post_data;
				}

				if ( ! empty( $_POST['post_parent'] ) && ! empty( $post_data['post_parent'] ) && $_POST['post_parent'] == $post_data['post_parent'] ) {
					$post_type = get_post_type( absint( $_POST['post_parent'] ) );
				} elseif ( ! empty( $_POST['ID'] ) && ! empty( $post_data['ID'] ) && $_POST['ID'] == $post_data['ID'] ) {
					$post_type = get_post_type( absint( $_POST['ID'] ) );
				} else {
					$post_type = '';
				}

				if ( $post_type && ( $active_features = geodir_classified_active_statuses( $post_type ) ) ) {
					if ( in_array( $_POST['sale_status'], $active_features ) ) {
						if ( isset( $post_data['post_status'] ) ) {
							$post_data['_from_post_status'] = $post_data['post_status'];
						}

						$post_data['post_status'] = sanitize_text_field( $_POST['sale_status'] );
					}
				}
			} else {
				// Undo Classifieds/Real-Estate Sold status
				if ( ! $update ) {
					return $post_data;
				}

				if ( ! empty( $_POST['post_parent'] ) && ! empty( $post_data['post_parent'] ) && $_POST['post_parent'] == $post_data['post_parent'] ) {
					$post_type = get_post_type( absint( $_POST['post_parent'] ) );
					$post_status = get_post_status( absint( $_POST['post_parent'] ) );
				} elseif ( ! empty( $_POST['ID'] ) && ! empty( $post_data['ID'] ) && $_POST['ID'] == $post_data['ID'] ) {
					$post_type = get_post_type( absint( $_POST['ID'] ) );
					$post_status = get_post_status( absint( $_POST['ID'] ) );
				} else {
					$post_type = '';
					$post_status = '';
				}

				if ( $post_type && $post_status && ( $active_features = geodir_classified_active_statuses( $post_type ) ) ) {
					if ( in_array( $post_status, $active_features ) ) {
						if ( isset( $post_data['post_status'] ) ) {
							$post_data['_from_post_status'] = $post_data['post_status'];
						}

						$post_data['post_status'] = 'publish';
					}
				}
			}
		}

		return $post_data;
	}

	public static function custom_fields_predefined( $custom_fields, $post_type ) {
		$active_features = geodir_get_classified_statuses( $post_type );

		if ( ! empty( $active_features ) ) {
			$features = geodir_register_classified_statuses();

			$options = 'Select Status/';
			foreach ( $features as $value => $feature ) {
				$options .= ',' . $feature['label'] . '/' . $value;
			}

			$custom_fields['sale_status'] = array(
				'field_type'  => 'select',
				'class'       => 'gd-sale-status',
				'icon'        => 'far fa-pause-circle',
				'name'        => __( 'Sale Status', 'geodirectory' ),
				'description' => __( 'Adds a select input to set a sale status of the listing.', 'geodirectory' ),
				'defaults'    => array(
					'data_type'          => 'VARCHAR',
					'admin_title'        => 'Sale Status',
					'frontend_title'     => 'Sale Status',
					'frontend_desc'      => 'Select a sale status of the listing.',
					'placeholder_value'  => 'Select Status',
					'htmlvar_name'       => 'sale_status',
					'is_active'          => true,
					'for_admin_use'      => false,
					'default_value'      => '',
					'show_in'            => '[detail],[listing]',
					'is_required'        => false,
					'option_values'      => $options,
					'validation_pattern' => '',
					'validation_msg'     => '',
					'required_msg'       => '',
					'field_icon'         => 'far fa-pause-circle',
					'css_class'          => 'gd-sale-status',
					'cat_sort'           => false,
					'cat_filter'         => true,
					'single_use'         => true
				)
			);
		}

		return $custom_fields;
	}

	public static function input_sale_status( $output, $cf ) {
		global $post, $gd_post, $geodir_label_type;

		$features = geodir_get_classified_statuses( $cf['post_type'] );

		if ( ! empty( $features ) ) {
			if ( is_admin() ) {
				return '<!-- -->';
			}

			if ( empty( $gd_post ) && ! empty( $post ) ) {
				$gd_post = geodir_get_post_info( $post->ID );
			}

			if ( ! empty( $gd_post->post_parent ) ) {
				$value = get_post_status( $gd_post->post_parent );
			} elseif ( ! empty( $gd_post->ID ) ) {
				$value = $gd_post->post_status;
			} else {
				$value = '';
			}

			// placeholder
			$placeholder = ! empty( $cf['placeholder_value'] ) ? __( $cf['placeholder_value'], 'geodirectory' ) : __( 'Select Status', 'geodirectory' );

			$options = array_merge( array( '' => $placeholder ), $features );

			if ( geodir_design_style() ) {
				// validation message
				$title = ! empty( $cf['validation_msg'] ) ? $cf['validation_msg'] : '';

				// required
				$required = ! empty( $cf['is_required'] ) ? ' <span class="text-danger">*</span>' : '';

				$extra_attributes = array();
				// validation
				if ( isset( $cf['validation_pattern'] ) && $cf['validation_pattern'] ) {
					$extra_attributes['pattern'] = $cf['validation_pattern'];
				}

				// extra
				$extra_attributes['data-placeholder'] = esc_attr( $placeholder );
				$extra_attributes['option-ajaxchosen'] = 'false';

				// admin only
				$admin_only = geodir_cfi_admin_only( $cf );
				$conditional_attrs = geodir_conditional_field_attrs( $cf );

				$output .= aui()->select( array(
					'id'               => $cf['name'],
					'name'             => $cf['name'],
					'title'            => $title,
					'placeholder'      => $placeholder,
					'value'            => $value,
					'required'         => ! empty( $cf['is_required'] ) ? true : false,
					'label_show'       => true,
					'label_type'       => ! empty( $geodir_label_type ) ? $geodir_label_type : 'horizontal',
					'label'            => __( $cf['frontend_title'], 'geodirectory' ) . $admin_only . $required,
					'validation_text'  => ! empty( $cf['validation_msg'] ) ? $cf['validation_msg'] : '',
					'validation_pattern' => ! empty( $cf['validation_pattern'] ) ? $cf['validation_pattern'] : '',
					'help_text'        => __( $cf['desc'], 'geodirectory' ),
					'extra_attributes' => $extra_attributes,
					'options'          => $options,
					'select2'          => true,
					'data-allow-clear' => true,
					'wrap_attributes'  => $conditional_attrs
				) );
			} else {
				$select_options = '';
				foreach ( $options as $val => $label ) {
					$select_options .= '<option value="' . esc_attr( $val ) . '" ' . selected( $val, $value, false ) . '>' . $label . '</option>';
				}
ob_start();
?>
<div id="<?php echo $cf['name'];?>_row" class="<?php echo ( ! empty( $cf['is_required'] ) ? 'required_field' : '' ); ?> geodir_form_row geodir_custom_fields clearfix gd-fieldset-details">
	<label for="<?php echo esc_attr( $cf['name'] ); ?>"><?php echo __( $cf['frontend_title'], 'geodirectory' ); ?><?php if ( $cf['is_required'] ) { echo '<span>*</span>'; } ?></label>
	<select field_type="<?php echo esc_attr( $cf['type'] ); ?>" name="<?php echo esc_attr( $cf['type'] ); ?>" id="<?php echo esc_attr( $cf['type'] ); ?>" class="geodir_textfield textfield_x geodir-select" data-placeholder="<?php echo esc_attr( $placeholder ); ?>" option-ajaxchosen="false" data-allow_clear="true"><?php echo $select_options;?></select>
	<span class="geodir_message_note"><?php _e( $cf['desc'], 'geodirectory' ); ?></span>
	<?php if ( ! empty( $cf['is_required'] ) ) { ?><span class="geodir_message_error"><?php _e( $cf['required_msg'], 'geodirectory' ); ?></span><?php } ?>
</div>
<?php
$output = ob_get_clean();
			}
		}

		return $output;
	}

	public static function output_sale_status( $html, $location, $cf, $output ) {
		global $post, $gd_post;

		$features = geodir_get_classified_statuses( $cf['post_type'] );

		if ( ! empty( $features ) && isset( $features[ $gd_post->post_status ] ) ) {
			$output = geodir_field_output_process( $output );
			$value = strip_tags( $gd_post->{$cf['htmlvar_name']} );

			// Database value.
			if ( ! empty( $output ) && isset( $output['raw'] ) ) {
				return $value;
			}

			$value = geodir_get_post_status_name( $gd_post->post_status );
			if ( ! empty( $output ) && isset( $output['strip'] ) ) {
				return stripslashes( $value );
			}

			$field_icon = geodir_field_icon_proccess( $cf );
			if ( strpos( $field_icon, 'http' ) !== false ) {
				$field_icon_af = '';
			} elseif ( $field_icon == '' ) {
				$field_icon_af = '';
			} else {
				$field_icon_af = $field_icon;
				$field_icon = '';
			}

			$html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '">';

			$maybe_secondary_class = isset( $output['icon'] ) ? 'gv-secondary' : '';

			if($output=='' || isset($output['icon'])) $html .= '<span class="geodir_post_meta_icon geodir-i-select" style="' . $field_icon . '">' . $field_icon_af;
			if($output=='' || isset($output['label']))$html .= $cf['frontend_title'] ? '<span class="geodir_post_meta_title ' . $maybe_secondary_class . '" >' . __( $cf['frontend_title'], 'geodirectory' ) . ': '.'</span>' : '';
			if($output=='' || isset($output['icon']))$html .= '</span>';
			if($output=='' || isset($output['value']))$html .= stripslashes( $value );

			$html .= '</div>';
		}

		return $html;
	}
}
