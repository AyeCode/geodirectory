<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GeoDirectory GeoDir_AJAX.
 *
 * AJAX Event Handler.
 *
 * @class    GeoDir_AJAX
 * @package  GeoDirectory/Classes
 * @category Class
 * @author   AyeCode
 */
class GeoDir_AJAX {

	/**
	 * Hook in ajax handlers.
	 */
	public static function init() {
		self::add_ajax_events();
	}




	/**
	 * Hook in methods - uses WordPress ajax handlers (admin-ajax).
	 */
	public static function add_ajax_events() {
		// geodirectory_EVENT => nopriv
		$ajax_events = array(
			'get_custom_field_form'   => false,
			'get_custom_field_sorting_form'   => false,
			'save_custom_field'       => false,
			'save_custom_sort_field'       => false,
			'delete_custom_field'     => false,
			'delete_custom_sort_field' => false,
			'order_custom_fields'     => false,
			'order_custom_sort_fields'     => false,
			'insert_dummy_data'       => false,
			'delete_dummy_data'       => false,
			'post_attachment_upload'       => true,
			'get_category_select'       => false,
			'user_add_fav'       => false,
			'save_post'       => true,
			'auto_save_post'       => true,
			'delete_revision'       => true,
			'import_export'         => false,
		);

		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_geodir_' . $ajax_event, array( __CLASS__, $ajax_event ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_geodir_' . $ajax_event, array( __CLASS__, $ajax_event ) );

				// WC AJAX can be used for frontend ajax requests.
				add_action( 'wc_ajax_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}
		}
	}

	public static function import_export(){
		// security
		check_ajax_referer( 'geodir_import_export_nonce', '_nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}


		$result = GeoDir_Admin_Import_Export::start_import_export();

		if(is_wp_error( $result ) ){
			wp_send_json_error( $result->get_error_message() );
		}elseif(isset($result['error']) && !empty($result['error'])){
			wp_send_json_error( $result['error'] );
		}else{
			wp_send_json($result);
			//wp_send_json_success($result);
		}

		wp_die();
	}

	/**
	 * Auto save post revisions and auto-drafts.
	 */
	public static function auto_save_post(){
		//print_r($_REQUEST);exit;
		// security
		check_ajax_referer( 'geodir-save-post', 'security' );

		$result = GeoDir_Post_Data::auto_save_post( $_REQUEST );

		if(is_wp_error( $result ) ){
			wp_send_json_error( $result->get_error_message() );
		}else{
			wp_send_json_success();
		}

		wp_die();
	}

	/**
	 * Auto save post revisions and auto-drafts.
	 */
	public static function delete_revision(){
		//print_r($_REQUEST);exit;
		// security
		check_ajax_referer( 'geodir-save-post', 'security' );

		$result = GeoDir_Post_Data::delete_revision( $_REQUEST );

		if(is_wp_error( $result ) ){
			wp_send_json_error( $result->get_error_message() );
		}else{
			wp_send_json_success();
		}

		wp_die();
	}

	/**
	 * Save the post.
	 */
	public static function save_post(){
		// security
		check_ajax_referer( 'geodir-save-post', 'security' );

		$result = GeoDir_Post_Data::ajax_save_post( $_REQUEST );

		if(is_wp_error( $result ) ){
			wp_send_json_error( $result->get_error_message() );
		}else{
			wp_send_json_success($result);
		}

		wp_die();

	}

	/**
	 * Add post to user favs.
	 */
	public static function user_add_fav(){
		// security
		check_ajax_referer( 'geodir_basic_nonce', 'security' );

		$user_id = get_current_user_id();
		$post_id = absint($_REQUEST['pid']);
		$type_action = isset($_REQUEST['type_action']) && $_REQUEST['type_action']=='add' ? 'add' : 'remove';

		if($user_id && $post_id ){
			if($type_action=='add'){
				$result = GeoDir_User::add_fav($post_id,$user_id);
			}else{
				$result = GeoDir_User::remove_fav($post_id,$user_id);
			}

			if($result){
				wp_send_json_success();
			}else{
				wp_send_json_error( __('Action failed.'));
			}
		}else{
			wp_send_json_error( __('Action failed.'));
		}
		wp_die();
	}
	
	
	public static function get_category_select(){
		// security
		//check_ajax_referer( 'geodir_get_category_select');
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}
		//print_r($_REQUEST);exit;
		$selected = isset($_REQUEST['selected']) ? $_REQUEST['selected'] : '';
		$tax = new GeoDir_Admin_Taxonomies();
		$tax->get_category_select($_REQUEST['post_type'], $selected, false, true);
		wp_die();
	}
	

	/**
	 * Admin action to get a custom field sorting form.
	 */
	public static function get_custom_field_sorting_form(){
		// security
		check_ajax_referer( 'gd_new_field_nonce', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

		$cfs = new GeoDir_Settings_Cpt_Sorting();

		$field = new stdClass();
		$field->id = isset($_REQUEST['field_id']) ? sanitize_text_field($_REQUEST['field_id']) : '';
		$field->post_type = isset($_REQUEST['listing_type']) ? sanitize_text_field($_REQUEST['listing_type']) : '';
		$field->field_type = isset($_REQUEST['field_type']) ? sanitize_text_field($_REQUEST['field_type']) : '';
		$field->field_type_key = isset($_REQUEST['field_type_key']) ? sanitize_text_field($_REQUEST['field_type_key']) : '';

		echo $cfs->output_custom_field_setting_item('',$field);
		wp_die();
	}
	
	public static function post_attachment_upload(){
		// security
		check_ajax_referer( 'geodir_attachment_upload', '_ajax_nonce' );
		
		//echo '###';
		//GeoDir_Media::post_image_upload();
		GeoDir_Media::post_attachment_upload();
		wp_die();
	}

	/**
	 * Admin action to insert dummy data.
	 */
	public static function insert_dummy_data(){
		// security
		check_ajax_referer( 'geodir_dummy_data', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

		$result = GeoDir_Admin_Dummy_Data::create_dummy_posts( $_REQUEST );

		if(is_wp_error( $result ) ){
			wp_send_json_error( $result->get_error_message() );
		}else{
			wp_send_json_success();
		}

		//print_r($_REQUEST);
		wp_die();
	}

	/**
	 * Admin action to delete dummy data.
	 */
	public static function delete_dummy_data(){
		// security
		check_ajax_referer( 'geodir_dummy_data', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}
		//print_r($_REQUEST);exit;
		$result = GeoDir_Admin_Dummy_Data::delete_dummy_posts( $_REQUEST['post_type'] );

		if(is_wp_error( $result ) ){
			wp_send_json_error( $result->get_error_message() );
		}else{
			wp_send_json_success();
		}

		//print_r($_REQUEST);
		wp_die();
	}

	/**
	 * Admin action to get a custom field form.
	 */
	public static function get_custom_field_form(){
		// security
		check_ajax_referer( 'gd_new_field_nonce', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

		//include( dirname( __FILE__ ) . '/admin/settings/class-geodir-settings-page.php' );
		//include( dirname( __FILE__ ) . '/admin/settings/class-geodir-settings-cpt-cf.php' );
		$cfs = new GeoDir_Settings_Cpt_Cf();

		$field = new stdClass();
		$field->id = isset($_REQUEST['field_id']) ? sanitize_text_field($_REQUEST['field_id']) : '';
		$field->post_type = isset($_REQUEST['listing_type']) ? sanitize_text_field($_REQUEST['listing_type']) : '';
		$field->field_type = isset($_REQUEST['field_type']) ? sanitize_text_field($_REQUEST['field_type']) : '';
		$field->field_type_key = isset($_REQUEST['field_type_key']) ? sanitize_text_field($_REQUEST['field_type_key']) : '';

		echo $cfs->output_custom_field_setting_item('',$field);
		wp_die();
	}

	/**
	 * Admin action to save a custom sort field.
	 */
	public static function save_custom_sort_field(){
		// security
		check_ajax_referer( 'custom_fields_'.sanitize_text_field($_POST['field_id']), 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

		//print_r($_REQUEST);exit;

		$result = geodir_custom_sort_field_save($_POST);

		if(is_wp_error( $result ) ){
			wp_send_json_error( $result->get_error_message() );
		}else{
			$cfs = new GeoDir_Settings_Cpt_Sorting();
			$cfs->output_custom_field_setting_item($result);
		}
		wp_die();
	}

	/**
	 * Admin action to delete a custom sort field.
	 */
	public static function delete_custom_sort_field(){
		// security
		//print_r($_REQUEST);
		$field_id = absint($_REQUEST['field_id']);
		check_ajax_referer( 'custom_fields_'.$field_id, 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

		//print_r($_REQUEST);

		$cfs = new GeoDir_Settings_Cpt_Sorting();
		$result = $cfs->delete_custom_field($field_id);
		if(is_wp_error( $result ) ){
			$data = array(
				'error' => $result->get_error_message()
			);
		}else{
			$data = array(
				'success' => true
			);
		}
		wp_send_json( $data );
	}

	/**
	 * Admin action to save a custom field.
	 */
	public static function save_custom_field(){
		// security
		check_ajax_referer( 'custom_fields_'.sanitize_text_field($_POST['field_id']), 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

		$result = geodir_custom_field_save($_POST);

		if(is_wp_error( $result ) ){
			wp_send_json_error( $result->get_error_message() );
		}else{
			$cfs = new GeoDir_Settings_Cpt_Cf();
			$cfs->output_custom_field_setting_item($result);
		}
		wp_die();
	}

	/**
	 * Admin action to delete a custom field.
	 */
	public static function delete_custom_field(){
		// security
		//print_r($_REQUEST);
		$field_id = absint($_REQUEST['field_id']);
		check_ajax_referer( 'custom_fields_'.$field_id, 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

		//print_r($_REQUEST);

		$cfs = new GeoDir_Settings_Cpt_Cf();
		$result = $cfs->delete_custom_field($field_id);
		if(is_wp_error( $result ) ){
			$data = array(
				'error' => $result->get_error_message()
			);
		}else{
			$data = array(
				'success' => true
			);
		}
		wp_send_json( $data );
	}

	/**
	 * Admin action to save the custom fields sort order.
	 */
	public static function order_custom_fields(){
		// security
		check_ajax_referer( 'gd_new_field_nonce', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

		$field_ids = array();
		if (!empty($_REQUEST['licontainer']) && is_array($_REQUEST['licontainer'])) {
			foreach ($_REQUEST['licontainer'] as $field_id) {
				$field_ids[] = absint($field_id);
			}
		}

		$cfs = new GeoDir_Settings_Cpt_Cf();
		$result = $cfs->set_field_orders($field_ids);

		if(is_wp_error( $result ) ){
			wp_send_json_error( $result->get_error_message() );
		}else{
			wp_send_json_success();
		}
	}

	/**
	 * Admin action to save the custom sort fields sort order.
	 */
	public static function order_custom_sort_fields(){
		// security
		check_ajax_referer( 'gd_new_field_nonce', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

		$field_ids = array();
		if (!empty($_REQUEST['licontainer']) && is_array($_REQUEST['licontainer'])) {
			foreach ($_REQUEST['licontainer'] as $field_id) {
				$field_ids[] = absint($field_id);
			}
		}

		$cfs = new GeoDir_Settings_Cpt_Sorting();
		$result = $cfs->set_field_orders($field_ids);

		if(is_wp_error( $result ) ){
			wp_send_json_error( $result->get_error_message() );
		}else{
			wp_send_json_success();
		}
	}
	
}


