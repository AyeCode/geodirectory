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
			'wizard_insert_widgets'       => false,
			'wizard_setup_menu'       => false,
			'post_attachment_upload'       => true,
			'get_category_select'       => false,
			'user_add_fav'       => false,
			'save_post'       => true,
			'auto_save_post'       => true,
			'delete_revision'       => true,
			'user_delete_post'       => false,
			'import_export'         => false,
			'save_api_key'			=> false,
			'bestof'			=> true,
			'cpt_categories' => true,
			'json_search_users' => false,
			'ninja_forms' => true,
		);

		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_geodir_' . $ajax_event, array( __CLASS__, $ajax_event ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_geodir_' . $ajax_event, array( __CLASS__, $ajax_event ) );

				// GeoDir AJAX can be used for frontend ajax requests.
				add_action( 'geodir_ajax_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}
		}
	}

	public static function ninja_forms(){

		check_ajax_referer( 'geodir_basic_nonce', 'security' );

		$post_id = isset($_REQUEST['p']) ? absint($_REQUEST['p']) : url_to_postid( wp_get_referer() );
		$form_id = isset($_REQUEST['extra']) ? absint($_REQUEST['extra']) : '';
		if(!$post_id || !$form_id){return 'no post id';}
		global $post;
		$post = get_post( $post_id );
		// fake the post_id for ninja forms
		add_filter( 'url_to_postid', function ($url){global $post;return add_query_arg( 'p', $post->ID, $url );});

		/*
		 * We only need the form and its basic CSS/JS so we hack away all lots of others stuff in a naughty way.
		 */
		remove_all_actions( 'wp_footer');
		add_action( 'wp_footer', 'wp_print_footer_scripts', 20 );
		add_action('wp_print_styles', function () {global $wp_styles; $wp_styles->queue = array('font-awesome');}, 1000);
		add_action('wp_print_scripts', function () {global $wp_scripts; $wp_scripts->queue = array('jquery');}, 1000);

		echo '<!DOCTYPE html><html lang="en-US"><head>';
		wp_head();
		echo "<style>body { background: #fff;padding: 20px 50px;}</style>";
		echo '</head><body>';
		//echo "<div class='lity-show'>";
		echo do_shortcode( "[ninja_form id=$form_id]" );
		//echo "</div>";
		wp_footer();
		echo '</body></html>';
		wp_die();
	}

	public static function wizard_setup_menu(){
		// security
		check_ajax_referer( 'geodir-wizard-setup-menu', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

		$menu_id = isset($_REQUEST['menu_id']) ?  sanitize_title_with_dashes($_REQUEST['menu_id']) : '';
		$menu_location = isset($_REQUEST['menu_location']) ?  sanitize_title_with_dashes($_REQUEST['menu_location']) : '';

		
		$result = GeoDir_Admin_Dummy_Data::setup_menu( $menu_id, $menu_location);

		if(is_wp_error( $result ) ){
			wp_send_json_error( $result->get_error_message() );
		}else{
			//wp_send_json($result);
			wp_send_json_success($result);
		}

		//GeoDir_Widget_Best_Of::best_of(array(), $_REQUEST);

		wp_die();
	}
	
	/**
	 * Adds widgets to sidebar during setup wizard.
	 */
	public static function wizard_insert_widgets(){
		// security
		check_ajax_referer( 'geodir-wizard-widgets', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

		$sidebar_id = isset($_REQUEST['sidebar_id']) ?  sanitize_title_with_dashes($_REQUEST['sidebar_id']) : '';

		//print_r($_REQUEST);exit;
		$result = GeoDir_Admin_Dummy_Data::insert_widgets( $sidebar_id);

		if(is_wp_error( $result ) ){
			wp_send_json_error( $result->get_error_message() );
		}else{
			//wp_send_json($result);
			wp_send_json_success($result);
		}

		//GeoDir_Widget_Best_Of::best_of(array(), $_REQUEST);

		wp_die();
	}

	/**
	 * Best of listings widget ajax.
	 */
	public static function bestof(){
		//print_r($_REQUEST);exit;
		// security
		check_ajax_referer( 'geodir_basic_nonce', 'security' );

		GeoDir_Widget_Best_Of::best_of(array(), $_REQUEST);

		wp_die();
	}
	
	/**
	 * GD Categories widget ajax.
	 */
	public static function cpt_categories(){
		// security
		check_ajax_referer( 'geodir_basic_nonce', 'security' );

		GeoDir_Widget_Categories::get_categories($_POST);

		wp_die();
	}

	/**
	 * User delete post.
	 */
	public static function user_delete_post(){
		//print_r($_REQUEST);exit;
		// security
		check_ajax_referer( 'geodir_basic_nonce', 'security' );

		$post_id = isset($_POST['post_id']) && $_POST['post_id'] ? absint($_POST['post_id']) : 0;

		if(!$post_id){
			wp_send_json_error( __("No post_id provided.","geodirectory") );
		}else{
			$result = GeoDir_User::delete_post( $post_id );

			if(is_wp_error( $result ) ){
				wp_send_json_error( $result->get_error_message() );
			}else{
				wp_send_json_success();
			}
		}

		wp_die();
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
		
		$result = GeoDir_Settings_Cpt_Sorting::save_custom_field($_POST);

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
	
	/**
	 * Create/Update API key.
	 */
	public static function save_api_key() {
		ob_start();

		global $wpdb;

		check_ajax_referer( 'save-api-key', 'security' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

		try {
			if ( empty( $_POST['description'] ) ) {
				throw new Exception( __( 'Description is missing.', 'geodirectory' ) );
			}
			if ( empty( $_POST['user'] ) ) {
				throw new Exception( __( 'User is missing.', 'geodirectory' ) );
			}
			if ( empty( $_POST['permissions'] ) ) {
				throw new Exception( __( 'Permissions is missing.', 'geodirectory' ) );
			}

			$key_id      = absint( $_POST['key_id'] );
			$description = sanitize_text_field( wp_unslash( $_POST['description'] ) );
			$permissions = ( in_array( $_POST['permissions'], array( 'read', 'write', 'read_write' ) ) ) ? sanitize_text_field( $_POST['permissions'] ) : 'read';
			$user_id     = absint( $_POST['user'] );

			if ( 0 < $key_id ) {
				$data = array(
					'user_id'     => $user_id,
					'description' => $description,
					'permissions' => $permissions,
				);

				$wpdb->update(
					GEODIR_API_KEYS_TABLE,
					$data,
					array( 'key_id' => $key_id ),
					array(
						'%d',
						'%s',
						'%s',
					),
					array( '%d' )
				);

				$data['consumer_key']    = '';
				$data['consumer_secret'] = '';
				$data['message']         = __( 'API Key updated successfully.', 'geodirectory' );
			} else {
				$consumer_key    = 'ck_' . geodir_rand_hash();
				$consumer_secret = 'cs_' . geodir_rand_hash();

				$data = array(
					'user_id'         => $user_id,
					'description'     => $description,
					'permissions'     => $permissions,
					'consumer_key'    => geodir_api_hash( $consumer_key ),
					'consumer_secret' => $consumer_secret,
					'truncated_key'   => substr( $consumer_key, -7 ),
				);

				$wpdb->insert(
					GEODIR_API_KEYS_TABLE,
					$data,
					array(
						'%d',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
					)
				);

				$key_id                  = $wpdb->insert_id;
				$data['consumer_key']    = $consumer_key;
				$data['consumer_secret'] = $consumer_secret;
				$data['message']         = __( 'API Key generated successfully. Make sure to copy your new keys now as the secret key will be hidden once you leave this page.', 'geodirectory' );
				$data['revoke_url']      = '<a style="color: #a00; text-decoration: none;" href="' . esc_url( wp_nonce_url( add_query_arg( array( 'revoke-key' => $key_id ), admin_url( 'admin.php?page=gd-settings&tab=api&section=keys' ) ), 'revoke' ) ) . '">' . __( 'Revoke key', 'geodirectory' ) . '</a>';
			}

			wp_send_json_success( $data );
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}
	}
	
	/**
	 * Search for customers and return json.
	 */
	public static function json_search_users() {
		ob_start();

		check_ajax_referer( 'search-users', 'security' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( -1 );
		}

		$term    = geodir_clean( stripslashes( $_GET['term'] ) );
		$exclude = array();
		$limit   = '';

		if ( empty( $term ) ) {
			wp_die();
		}

		$user_ids = array();
		// Search by ID.
		if ( is_numeric( $term ) ) {
			$user = get_user_by( 'id', intval( $term ) );

			// Customer does not exists.
			if ( ! empty( $user ) ) {
				$user_ids = array( $user->ID );
			}
		}

		if ( empty( $user_ids ) ) {
			if ( 3 > strlen( $term ) ) {
				$limit = 20;
			}
			$user_ids = GeoDir_AJAX::search_users( $term, $limit );
		}

		$exclude_ids = ! empty( $_GET['exclude'] ) && is_array( $_GET['exclude'] ) ? $_GET['exclude'] : array();

		$found_users = array();
		if ( ! empty( $user_ids ) ) {
			foreach ( $user_ids as $user_id ) {
				$user = get_user_by( 'id', $user_id );
				if ( empty( $user ) ) {
					continue;
				}
				if ( ! empty( $exclude_ids ) && in_array( $user_id, $exclude_ids ) ) {
					continue;
				}

				/* translators: 1: user display name 2: user ID 3: user email */
				$found_users[ $user_id ] = wp_sprintf(
					esc_html__( '%1$s (#%2$s &ndash; %3$s)', 'geodirectory' ),
					$user->display_name,
					absint( $user->ID ),
					$user->user_email
				);
			}
		}

		wp_send_json( apply_filters( 'geodir_json_search_found_users', $found_users ) );
	}

	/**
	 * Search users and return user IDs.
	 *
	 * @param  string     $term
	 * @param  int|string $limit
	 *
	 * @return array
	 */
	public static function search_users( $term, $limit = '' ) {
		$results = apply_filters( 'geodir_user_pre_search_customers', false, $term, $limit );
		if ( is_array( $results ) ) {
			return $results;
		}

		$main_query = new WP_User_Query( apply_filters( 'geodir_user_search_users', array(
			'search'         => '*' . esc_attr( $term ) . '*',
			'search_columns' => array( 'user_login', 'user_url', 'user_email', 'user_nicename', 'display_name' ),
			'fields'         => 'ID',
			'number'         => $limit,
		), $term, $limit, 'main_query' ) );

		$meta_query = new WP_User_Query( apply_filters( 'geodir_user_search_users', array(
			'fields'         => 'ID',
			'number'         => $limit,
			'meta_query'     => array(
				'relation' => 'OR',
				array(
					'key'     => 'first_name',
					'value'   => $term,
					'compare' => 'LIKE',
				),
				array(
					'key'     => 'last_name',
					'value'   => $term,
					'compare' => 'LIKE',
				),
			),
		), $term, $limit, 'meta_query' ) );

		$results = wp_parse_id_list( array_merge( (array) $main_query->get_results(), (array) $meta_query->get_results() ) );

		if ( $limit && count( $results ) > $limit ) {
			$results = array_slice( $results, 0, $limit );
		}

		return $results;
	}	
}


