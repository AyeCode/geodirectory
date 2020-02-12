<?php
/**
 * Classifieds integration class.
 *
 * @since 2.0.0.71
 * @package GeoDIrectory
 * @author AyeCode Ltd
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

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
	}

	public static function get_settings_cpt( $settings, $current_section = '', $post_type_values = array() ) {
		$post_type = ! empty( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : '';

		if ( ! empty( $settings ) ) {
			$new_settings = array();
			foreach ( $settings as $key => $setting ) {
				$new_settings[] = $setting;
				if ( ! empty( $setting['id'] ) && $setting['id'] == 'cpt_settings_page' && $setting['type'] == 'sectionend' ) {
					$new_settings[] = array(
						'title' => __( 'Classifieds/Real-Estate Sold Settings', 'geodirectory' ),
						'type' => 'title',
						'desc' => __( 'Add a sold functionality which would include the following listing statuses like sold, under offer, sale agreed etc.', 'geodirectory' ),
						'id' => 'cpt_settings_classifieds',
						'desc_tip' => true,
					);
					$new_settings[] = array(
						'name' => __( 'Enable "Sold" Feature?', 'geodirectory' ),
						'desc' => __( 'Tick to enable sold functionality for this post type. <span style="color:red;">(WARNING: disabling sold feature for the post type will move all existing posts to draft.)</span>', 'geodirectory' ),
						'id' => 'supports_sold',
						'type' => 'checkbox',
						'std' => '0',
						'advanced' => true,
						'value' => ( ! empty( $post_type_values['supports_sold'] ) ? '1' : '0' )
					);
					$new_settings[] = array(
						'name' => '',
						'desc' => '',
						'id' => 'prev_supports_sold',
						'type' => 'hidden',
						'value' => ( ! empty( $post_type_values['supports_sold'] ) ? 'y' : 'n' )
					);
					$new_settings[] = array(
						'name' => __( 'Enable "Under Offer" Feature?', 'geodirectory' ),
						'desc' => __( 'Tick to enable "under offer" functionality for this post type. <span style="color:red;">(WARNING: disabling "under offer" feature for the post type will move all existing posts to draft.)</span>', 'geodirectory' ),
						'id' => 'supports_under_offer',
						'type' => 'checkbox',
						'std'  => '0',
						'advanced' => true,
						'value' => ( ! empty( $post_type_values['supports_under_offer'] ) ? '1' : '0' )
					);
					$new_settings[] = array(
						'name' => '',
						'desc' => '',
						'id' => 'prev_supports_under_offer',
						'type' => 'hidden',
						'value' => ( ! empty( $post_type_values['supports_under_offer'] ) ? 'y' : 'n' )
					);
					$new_settings[] = array(
						'name' => __( 'Enable "Sale Agreed" Feature?', 'geodirectory' ),
						'desc' => __( 'Tick to enable "sale agreed" functionality for this post type. <span style="color:red;">(WARNING: disabling "sale agreed" feature for the post type will move all existing posts to draft.)</span>', 'geodirectory' ),
						'id' => 'supports_sale_agreed',
						'type' => 'checkbox',
						'std'  => '0',
						'advanced' => true,
						'value' => ( ! empty( $post_type_values['supports_sale_agreed'] ) ? '1' : '0' )
					);
					$new_settings[] = array(
						'name' => '',
						'desc' => '',
						'id' => 'prev_supports_sale_agreed',
						'type' => 'hidden',
						'value' => ( ! empty( $post_type_values['supports_sale_agreed'] ) ? 'y' : 'n' )
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
}
