<?php
/**
 * V3 Tools Settings for GeoDirectory
 *
 * @package     GeoDirectory
 * @since       3.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_big_data_active = ! empty( $geodirectory->settings['enable_big_data'] );
$big_data_status    = $is_big_data_active ? __( '(active)', 'geodirectory' ) : __( '(not active)', 'geodirectory' );

$post_type = ! empty( $_REQUEST['srcf_pt'] ) ? sanitize_text_field( $_REQUEST['srcf_pt'] ) : '';
$field = ! empty( $_REQUEST['srcf_cf'] ) ? sanitize_text_field( $_REQUEST['srcf_cf'] ) : '';
$search = isset( $_REQUEST['srcf_s'] ) && $_REQUEST['srcf_s'] != "" ? sanitize_text_field( wp_unslash( $_REQUEST['srcf_s'] ) ) : '';
$replace = isset( $_REQUEST['srcf_r'] ) && $_REQUEST['srcf_r'] != "" ? sanitize_text_field( wp_unslash( $_REQUEST['srcf_r'] ) ) : '';
//exit;
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
//		'size' => 'sm',
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
//		'size' => 'sm',
		'required' => false
	)
);
$output .= '</div>';
$output .= '</div>';
$output .= '</div>';

return array(
	'id'     => 'tools',
	'name'   => __( 'Tools', 'geodirectory' ),
	'icon'   => 'fa-solid fa-screwdriver-wrench',
	'fields' => array(
		array(
			'id'           => 'clear_version_numbers',
			'type'         => 'action_button',
			'label'        => __( 'Clear version numbers', 'geodirectory' ),
			'description'  => __( 'This will force install/upgrade functions to run.', 'geodirectory' ),
			'button_text'  => __( 'Run', 'geodirectory' ),
			'button_class' => 'btn-primary',
			'ajax_action'  => 'clear_version_numbers'
		),
		array(
			'id'           => 'check_reviews',
			'type'         => 'action_button',
			'label'        => __( 'Check reviews', 'geodirectory' ),
			'description'  => __( 'Check reviews for correct location and content settings.', 'geodirectory' ),
			'button_text'  => __( 'Run', 'geodirectory' ),
			'button_class' => 'btn-primary',
			'ajax_action'  => 'check_reviews'
		),
		array(
			'id'           => 'install_pages',
			'type'         => 'action_button',
			'label'        => __( 'Create default GeoDirectory pages', 'geodirectory' ),
			'description'  => sprintf(
				'<strong class="text-danger">%1$s</strong> %2$s',
				__( 'Note:', 'geodirectory' ),
				__( 'This tool will install all the missing GeoDirectory pages. Pages already defined and set up will not be replaced.', 'geodirectory' )
			),
			'button_text'  => __( 'Run', 'geodirectory' ),
			'button_class' => 'btn-primary',
			'ajax_action'  => 'install_pages'
		),
		array(
			'id'           => 'merge_missing_terms',
			'type'         => 'action_button',
			'label'        => __( 'Merge Missing Categories', 'geodirectory' ),
			'description'  => __( 'Merge missing listing categories from WP terms relationships.', 'geodirectory' ),
			'button_text'  => __( 'Run', 'geodirectory' ),
			'button_class' => 'btn-primary',
			'ajax_action'  => 'merge_missing_terms'
		),
		array(
			'id'           => 'recount_terms',
			'type'         => 'action_button',
			'label'        => __( 'Term counts', 'geodirectory' ),
			'description'  => __( 'This tool will recount the listing terms.', 'geodirectory' ),
			'button_text'  => __( 'Run', 'geodirectory' ),
			'button_class' => 'btn-primary',
			'ajax_action'  => 'recount_terms'
		),
		array(
			'id'           => 'generate_keywords',
			'type'         => 'action_button',
			'label'        => __( 'Generate Keywords', 'geodirectory' ),
			'description'  => __( 'Generate keywords from post title to enhance searching. helps fix searches for `cafe` when the title might be `Café` (with accent)', 'geodirectory' ),
			'button_text'  => __( 'Run', 'geodirectory' ),
			'button_class' => 'btn-primary',
			'ajax_action'  => 'generate_keywords'
		),
		array(
			'id'           => 'generate_thumbnails',
			'type'         => 'action_button',
			'label'        => __( 'Regenerate Thumbnails', 'geodirectory' ),
			'description'  => wp_sprintf( __( 'Regenerate thumbnails & metadata for the post images. Total image attachments found: %s', 'geodirectory' ), '<b>' . (int) geodirectory()->media->count_image_attachments() . '</b>' ),
			'button_text'  => __( 'Run', 'geodirectory' ),
			'button_class' => 'btn-primary',
			'ajax_action'  => 'generate_thumbnails'
		),
		array(
			'id'           => 'export_db_texts',
			'type'         => 'action_button',
			'label'        => __( 'DB text translation', 'geodirectory' ),
			'description'  => __( 'This tool will collect any texts stored in the DB and put them in the file db-language.php so they can then be used to translate them by translations tools.', 'geodirectory' ),
			'button_text'  => __( 'Run', 'geodirectory' ),
			'button_class' => 'btn-primary',
			'ajax_action'  => 'export_db_texts'
		),
		array(
			'id'           => 'clear_paging_cache',
			'type'         => 'action_button',
			'label'        => __( 'Clear paging cache', 'geodirectory' ),
			'description'  => __( 'This tool will delete paging cache when the BIG Data option is enabled', 'geodirectory' ) . ' ' . esc_attr( $big_data_status ),
			'button_text'  => __( 'Run', 'geodirectory' ),
			'button_class' => 'btn-primary',
			'ajax_action'  => 'clear_paging_cache'
		),

		array(
			'id'           => 'search_replace_cf',
			'type'         => 'action_button',
			'label'        => __( 'Search & Replace Custom Field Value', 'geodirectory' ),
			'description'  => __( 'Search & replace custom field values in post type details database table for SELECT, MULTISELECT, RADIO, CHECKBOX field types.', 'geodirectory' ),
			'button_text'  => __( 'Replace', 'geodirectory' ),
			'button_class' => 'btn-primary',
			'ajax_action'  => 'search_replace_cf',
			'custom_desc'  => $output,
		),
	)
);


