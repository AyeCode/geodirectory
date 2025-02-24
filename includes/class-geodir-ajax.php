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
		//add_action( 'init', array( __CLASS__, 'define_ajax' ), 0 );
		//add_action( 'template_redirect', array( __CLASS__, 'do_gd_ajax' ), 0 );
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
			'auto_save_custom_field'  => false,
			'save_custom_field'       => false,
			'save_custom_sort_field'       => false,
			'delete_custom_field'     => false,
			'delete_custom_sort_field' => false,
			'order_custom_fields'     => false,
			'order_custom_sort_fields'     => false,
			'insert_dummy_data'       => false,
			'delete_dummy_data'       => false,
			'wizard_insert_widgets_top'       => false,
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
			'cpt_tags' => true,
			'json_search_users' => false,
			'ninja_forms' => true,
			'get_tabs_form' => false,
			'save_tab_item' => false,
			'save_tabs_order' => false,
			'delete_tab' => false,
			'manual_map' => true,
			'widget_listings' => true,
			'recently_viewed_listings' => true,
			'embed_widget' => true,
			'embed_script' => true,
			'timezone_data' => true,
			'get_sort_options' => false,
			'tool_regenerate_thumbnails' => true,
			'regenerate_thumbnails' => true,
			'post_author_action' => false,
			'report_post_form' => true,
			'submit_report_post' => true,
			'new_wp_template' => true,
			'business_hours_post_meta' => true,
		);

		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_geodir_' . $ajax_event, array( __CLASS__, $ajax_event ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_geodir_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}

			// GeoDir AJAX can be used for frontend ajax requests.
			add_action( 'geodir_ajax_geodir_' . $ajax_event, array( __CLASS__, $ajax_event ) );
		}
	}

	/**
	 * Get the CPT sort options for the listings widget.
	 */
	public static function get_sort_options(){
		$post_type = !empty($_REQUEST['post_type']) ? sanitize_key( $_REQUEST['post_type'] ) : 'gd_place';
		$sort_options = geodir_sort_by_options( $post_type );

		echo json_encode($sort_options);
		exit;
	}

	public static function embed_script(){
		$html = GeoDir_External_Embed::get_embed_script();
		if($html ){
			echo $html;
		}
		exit;
	}

	public static function embed_widget(){
		$html = GeoDir_External_Embed::get_embed_widget();
		if($html ){
			echo $html;
		}
		exit;
	}

	/**
	 * Set GD AJAX constant and headers.
	 */
	public static function define_ajax() {
		if ( ! empty( $_GET['gd-ajax'] ) ) {
			if(!defined('DOING_AJAX'))define( 'DOING_AJAX', true );
			if(!defined('GD_DOING_AJAX'))define( 'GD_DOING_AJAX', true );
			if ( ! WP_DEBUG || ( WP_DEBUG && ! WP_DEBUG_DISPLAY ) ) {
				/** @scrutinizer ignore-unhandled */ @ini_set( 'display_errors', 0 ); // Turn off display_errors during AJAX events to prevent malformed JSON.
			}
			$GLOBALS['wpdb']->hide_errors();
		}
	}

	/**
	 * Check for WC Ajax request and fire action.
	 */
	public static function do_gd_ajax() {
		global $wp_query;

		if ( ! empty( $_GET['gd-ajax'] ) ) {
			$wp_query->set( 'gd-ajax', sanitize_text_field( wp_unslash( $_GET['gd-ajax'] ) ) );
		}

		$action = $wp_query->get( 'gd-ajax' );

		if ( $action ) {
			self::gd_ajax_headers();
			$action = sanitize_text_field( $action );
			do_action( 'gd_ajax_' . $action );
			wp_die();
		}
	}

	/**
	 * Send headers for GD Ajax Requests.
	 *
	 * @since 2.0.0.58
	 */
	private static function gd_ajax_headers() {
		send_origin_headers();
		/** @scrutinizer ignore-unhandled */ @header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) ); // @codingStandardsIgnoreLine
		/** @scrutinizer ignore-unhandled */ @header( 'X-Robots-Tag: noindex' ); // @codingStandardsIgnoreLine
		send_nosniff_header();
		nocache_headers();
		status_header( 200 );
	}

	/**
	 * Manual map
	 *
	 * @since 2.0.0
	 */
	public static function manual_map() {
		global $geodirectory, $mapzoom, $geodir_manual_map,$aui_bs5;

		$prefix = isset( $_POST['trigger'] ) ? sanitize_title( geodir_clean( $_POST['trigger'] ) ) : 'geodir_manual_location_';
		$prefix = esc_attr( $prefix );

		$map_title = __( "Select Your Location", 'geodirectory' );
		$location = $geodirectory->location->get_default_location();
		$country = isset( $location->country ) ? $location->country : '';
		$region = isset( $location->region ) ? $location->region : '';
		$city = isset( $location->city ) ? $location->city : '';
		$lat = isset( $location->latitude ) ? $location->latitude : '';
		$lng = isset( $location->longitude ) ? $location->longitude : '';
		$mapzoom = 8;

		$design_style = geodir_design_style();
		$geodir_manual_map = true;

		// Try and center the map as close to the user as possible.
		if ( $ip = geodir_get_ip() ) {
			$geo = geodir_geo_by_ip( $ip );

			if ( ! empty( $geo ) && ! empty( $geo['latitude'] ) && ! empty( $geo['longitude'] ) ) {
				$lat = $geo['latitude'];
				$lng = $geo['longitude'];
			}
		}

		add_filter( 'geodir_add_listing_map_restrict', '__return_false' );

		if ( $design_style ) {
			echo aui()->alert(array(
					'type'=> 'info',
					'content'=> __("Auto location detection failed, please manually set your location below by dragging the map / marker.","geodirectory")
				)
			);

			$template = $design_style . '/map/map-add-listing.php';

			$tmpl_args = array(
				'prefix' => $prefix,
				'map_title' => $map_title,
				'country' => $country,
				'region' => $region,
				'city' => $city,
				'lat' => $lat,
				'lng' => $lng,
				'design_style' => $design_style
			);

			echo geodir_get_template_html( $template, $tmpl_args );
		} else {
			echo "<style>.lity-show #" . esc_attr( $prefix ) . "set_address_button,.lity-show .TopLeft,.lity-show .TopRight,.lity-show .BottomRight,.lity-show .BottomLeft{display:none}.lity-show .geodir_map_container{margin-top:0 !important}</style>";

			include_once( GEODIRECTORY_PLUGIN_DIR . 'templates/map.php' );
		}
		?>
		<input type="hidden" id="<?php echo esc_attr( $prefix ) . 'latitude'; ?>">
		<input type="hidden" id="<?php echo esc_attr( $prefix ) . 'longitude'; ?>">
		<?php
		if( $design_style ) {
			?>
			<div class="text-right text-end">
			<button type="button" class="btn btn-link" data<?php echo $aui_bs5 ? '-bs' : ''; ?>-dismiss="modal"><?php _e("Cancel","geodirectory");?></button>
			<button class="btn btn-primary"
			        onclick="if(jQuery('#<?php echo esc_attr( $prefix ) . 'latitude'; ?>').val()==''){alert('<?php _e( 'Please drag the marker or the map to set the position.', 'geodirectory' ); ?>');}else{jQuery(window).triggerHandler('<?php echo esc_attr( $prefix ); ?>', [jQuery('#<?php echo esc_attr( $prefix ) . 'latitude'; ?>').val(), jQuery('#<?php echo esc_attr( $prefix ) . 'longitude'; ?>').val()]);}"><?php _e( 'Set my location', 'geodirectory' ); ?></button>
			</div><?php
		}else{
			?>
			<button style="float: right;margin: 10px 0 0 0;"
			        onclick="if(jQuery('#<?php echo esc_attr( $prefix ) . 'latitude'; ?>').val()==''){alert('<?php _e( 'Please drag the marker or the map to set the position.', 'geodirectory' ); ?>');}else{jQuery(window).triggerHandler('<?php echo esc_attr( $prefix ); ?>', [jQuery('#<?php echo esc_attr( $prefix ) . 'latitude'; ?>').val(), jQuery('#<?php echo esc_attr( $prefix ) . 'longitude'; ?>').val()]);}"><?php _e( 'Set my location', 'geodirectory' ); ?></button>
			<?php
		}
		wp_die();
	}

    /**
     * Delete Tab input form
     *
     * @since 2.0.0
     */
	public static function delete_tab(){
		// security
		check_ajax_referer( 'gd_new_field_nonce', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

		$tab_id = isset($_POST['tab_id']) && $_POST['tab_id'] ? absint($_POST['tab_id']) : '';

		if(!$tab_id){
			wp_send_json_error( __("No tab_id provided.","geodirectory") );
		}else{
			$post_type = isset($_POST['post_type']) && $_POST['post_type'] ? esc_attr($_POST['post_type']) : '';
			$result = GeoDir_Settings_Cpt_Tabs::delete_tab($tab_id,$post_type);

			if(is_wp_error( $result ) ){
				wp_send_json_error( $result->get_error_message() );
			}else{
				wp_send_json_success();
			}
		}

		wp_die();
	}

	public static function recently_viewed_listings(){
		global $post, $gd_post, $geodir_item_tmpl, $gd_layout_class;

		$design_style = geodir_design_style();
		$list_per_page = ! empty( $_REQUEST['list_per_page'] ) ? absint( $_REQUEST['list_per_page'] ) : '';
		$post_type = ! empty( $_REQUEST['post_type'] ) ? sanitize_key( $_REQUEST['post_type'] ) : '';

		if ( ! geodir_is_gd_post_type( $post_type ) ) {
			$post_type = 'gd_place';
		}

		$layout = ! empty( $_REQUEST['layout'] ) ? absint( $_REQUEST['layout'] ) : 2;
		$post_ids = ! empty( $_REQUEST['viewed_post_id'] ) ? json_decode( stripslashes( $_REQUEST['viewed_post_id'] ), true ) : '';

		// Templates
		$template_type = ! empty( $_REQUEST['template_type'] ) ? sanitize_text_field( $_REQUEST['template_type'] ) : '';
		$template_page = ! empty( $_REQUEST['tmpl_page'] ) ? sanitize_text_field( $_REQUEST['tmpl_page'] ) : '';
		$template_part = ! empty( $_REQUEST['tmpl_part'] ) ? sanitize_text_field( $_REQUEST['tmpl_part'] ) : '';

		// Elementor pro
		$skin_id = ! empty( $_REQUEST['skin_id'] ) ? absint( $_REQUEST['skin_id'] ) : '';
		$skin_column_gap = ! empty( $_REQUEST['skin_column_gap'] ) ? absint( $_REQUEST['skin_column_gap'] ) : '';
		$skin_row_gap = ! empty( $_REQUEST['skin_row_gap'] ) ? absint( $_REQUEST['skin_row_gap'] ) : '';

		// AUI
		$column_gap = ! empty( $_REQUEST['column_gap'] ) ? absint( $_REQUEST['column_gap'] ) : '';
		$row_gap = ! empty( $_REQUEST['row_gap'] ) ? absint( $_REQUEST['row_gap'] ) : '';
		$card_border = ! empty( $_REQUEST['card_border'] ) ? sanitize_html_class( $_REQUEST['card_border'] ) : '';
		$card_shadow = ! empty( $_REQUEST['card_shadow'] ) ? sanitize_html_class( $_REQUEST['card_shadow'] ) : '';

		$listings_ids = array();

		if ( ! empty( $post_type ) ) {
			if ( ! empty( $post_ids ) && is_array( $post_ids ) && ! empty( $post_ids[ $post_type ] ) ) {
				$listings_ids = $post_ids[ $post_type ];
			}

			if ( ! empty( $listings_ids ) ) {
				$listings_ids = array_reverse( array_unique( $listings_ids ) );
				$listings_ids = array_slice( $listings_ids, 0, $list_per_page );
			}
		}

		$widget_listings = array();

		if ( ! empty( $listings_ids ) ) {
			foreach( $listings_ids as $post_id ) {
				$post_id = absint( $post_id );

				if ( $post_id > 0 ) {
					$listing = geodir_get_post_info( $post_id );

					if ( ! empty( $listing ) && ! empty( $listing->post_type ) && $listing->post_type == $post_type ) {
						$widget_listings[] = $listing;
					}
				}
			}
		}

		$output = '';

		if ( ! empty( $widget_listings ) ) {
			$geodir_item_tmpl = array();

			if ( ! empty( $template_page ) && get_post_type( $template_page ) == 'page' && get_post_status( $template_page ) == 'publish' ) {
				$geodir_item_tmpl = array(
					'id'   => $template_page,
					'type' => 'page',
				);
			} else if ( ! empty( $template_part ) && ( $_template_part = geodir_get_template_part_by_slug( $template_part ) ) ) {
				$geodir_item_tmpl = array(
					'id'      => $_template_part->slug,
					'content' => $_template_part->content,
					'type'    => 'template_part',
				);
			}

			// Elementor Pro
			$skin_active = false;
			$elementor_wrapper_class = '';

			if ( defined( 'ELEMENTOR_PRO_VERSION' ) && $skin_id ) {
				if ( get_post_status( $skin_id ) == 'publish' ) {
					$skin_active = true;

					$geodir_item_tmpl = array(
						'id'   => $skin_id,
						'type' => 'elementor_skin',
					);
				}

				if ( $skin_active ) {
					$columns = isset( $layout ) ? absint( $layout ) : 1;

					if ( $columns < 1 ) {
						$columns = 6; // We have no 6 row option to lets use list view
					}

					$elementor_wrapper_class = ' elementor-element elementor-element-9ff57fdx elementor-posts--thumbnail-top elementor-grid-' . $columns . ' elementor-grid-tablet-2 elementor-grid-mobile-1 elementor-widget elementor-widget-posts ';
				}
			}

			$gd_layout_class = geodir_convert_listing_view_class( $layout );

			// Card border class
			$card_border_class = '';

			if ( ! empty( $args['card_border'] ) ) {
				if ( $args['card_border'] == 'none' ) {
					$card_border_class = 'border-0';
				} else {
					$card_border_class = 'border-' . sanitize_html_class( $args['card_border'] );
				}
			}

			// Card shadow
			$card_shadow_class = '';

			if ( ! empty( $args['card_shadow'] ) ) {
				if ( $args['card_shadow'] == 'small' ) {
					$card_shadow_class = 'shadow-sm';
				} else if ( $args['card_shadow'] == 'medium' ) {
					$card_shadow_class = 'shadow';
				} else if ( $args['card_shadow'] == 'large' ) {
					$card_shadow_class = 'shadow-lg';
				}
			}

			if ( $skin_active ) {
				ob_start();

				geodir_get_template(
					'elementor/content-widget-listing.php',
					array(
						'widget_listings' => $widget_listings,
						'skin_id'         => $skin_id,
						'columns'         => $columns,
						'column_gap'      => $skin_column_gap,
						'row_gap'         => $skin_row_gap,
					)
				);

				$output = ob_get_clean();
			} else {
				$template = $design_style ? $design_style . '/content-widget-listing.php' : 'content-widget-listing.php';

				$output = geodir_get_template_html(
					$template,
					array(
						'widget_listings'   => $widget_listings,
						'column_gap_class'  => $column_gap ? 'mb-' . absint( $column_gap ) : 'mb-4',
						'row_gap_class'     => $row_gap ? 'px-' . absint( $row_gap ) : '',
						'card_border_class' => $card_border_class,
						'card_shadow_class' => $card_shadow_class,
					)
				);
			}
		} else {
			$output = aui()->alert( array(
					'type'=> 'info',
					'content'=> __( "Your recently viewed listings will show here.", "geodirectory" )
				)
			);
		}

		if ( $output ) {
			echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		wp_die();
	}

	/**
	 * Save tabs order input form.
     *
     * @since 2.0.0
	 */
	public static function save_tabs_order(){
		// security
		check_ajax_referer( 'gd_new_field_nonce', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

		$tabs = isset($_POST['tabs']) && $_POST['tabs'] ? $_POST['tabs'] : '';

		if(!$tabs){
			wp_send_json_error( __("No tabs provided.","geodirectory") );
		}else{
			$result = GeoDir_Settings_Cpt_Tabs::set_tabs_orders($tabs);

			if(is_wp_error( $result ) ){
				wp_send_json_error( $result->get_error_message() );
			}else{
				wp_send_json_success();
			}
		}

		wp_die();
	}

	/**
	 * Save tab item.
     *
     * @since 2.0.0
	 */
	public static function save_tab_item(){
		// security
		check_ajax_referer( 'gd_new_field_nonce', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

		$tab_type = isset($_POST['tab_type']) && $_POST['tab_type'] ? esc_attr($_POST['tab_type']) : '';

		if(!$tab_type){
			wp_send_json_error( __("No tab_type provided.","geodirectory") );
		}else{
			$result = GeoDir_Settings_Cpt_Tabs::save_tab_item($_POST);

			if(is_wp_error( $result ) ){
				wp_send_json_error( $result->get_error_message() );
			}else{
				wp_send_json_success($result);
			}
		}

		wp_die();
	}

	/**
	 * Get tabs input form.
     *
     * @since 2.0.0
	 */
	public static function get_tabs_form(){
		// security
		check_ajax_referer( 'gd_new_field_nonce', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

		$tab_type = isset($_POST['tab_type']) && $_POST['tab_type'] ? esc_attr($_POST['tab_type']) : '';

		if(!$tab_type){
			wp_send_json_error( __("No tab_type provided.","geodirectory") );
		}else{
			$result = GeoDir_Settings_Cpt_Tabs::get_tab_item($_POST);

			if(is_wp_error( $result ) ){
				wp_send_json_error( $result->get_error_message() );
			}else{
				wp_send_json_success($result);
			}
		}

		wp_die();
	}

    /**
     * Get Ninja forms html.
     *
     * @since 2.0.0
     *
     * @return string
     */
	public static function ninja_forms(){
		check_ajax_referer( 'geodir_basic_nonce', 'security' );

		$post_id = isset($_REQUEST['p']) ? absint($_REQUEST['p']) : url_to_postid( wp_get_referer() );
		$form_id = isset($_REQUEST['extra']) ? absint($_REQUEST['extra']) : '';

		if ( ! $post_id || ! $form_id ) {
			return 'no post id';
		}

		global $post;
		$the_post = get_post( $post_id );
		$post = $the_post;

		// fake the post_id for ninja forms
		add_filter( 'url_to_postid', function ( $url ) {
			global $post;
			return add_query_arg( 'p', $post->ID, $url );
		});

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

		// Set post back.
		if ( $post->ID != $the_post->ID ) {
			$post = $the_post;
		}

		// allow other plugins to override the call
		$override_html = apply_filters('geodir_ajax_ninja_forms_override','',$post_id,$form_id);
		if(!empty($override_html)){
			echo $override_html;
		}else{
			echo do_shortcode( "[ninja_form id=$form_id]" );
		}

		wp_footer();
		echo '</body></html>';
		wp_die();
	}

    /**
     * Setup wizard menu.
     *
     * @since 2.0.0
     */
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
			wp_send_json_success(geodir_notification( array( 'info' => $result) ));
		}

		wp_die();
	}

	/**
	 * Adds widgets to sidebar during setup wizard.
     *
     * @since 2.0.0
	 */
	public static function wizard_insert_widgets_top(){
		// security
		check_ajax_referer( 'geodir-wizard-widgets-top', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

		$sidebar_id = isset($_REQUEST['sidebar_id']) ?  sanitize_title_with_dashes($_REQUEST['sidebar_id']) : '';

		$result = GeoDir_Admin_Dummy_Data::insert_widgets( $sidebar_id, 'top');

		if(is_wp_error( $result ) ){
			wp_send_json_error( $result->get_error_message() );
		}else{
			wp_send_json_success(geodir_notification( array( 'info' => $result) ));
		}

		wp_die();
	}

	/**
	 * Adds widgets to sidebar during setup wizard.
	 *
	 * @since 2.0.0
	 */
	public static function wizard_insert_widgets(){
		// security
		check_ajax_referer( 'geodir-wizard-widgets', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

		$sidebar_id = isset($_REQUEST['sidebar_id']) ?  sanitize_title_with_dashes($_REQUEST['sidebar_id']) : '';

		$result = GeoDir_Admin_Dummy_Data::insert_widgets( $sidebar_id);

		if(is_wp_error( $result ) ){
			wp_send_json_error( $result->get_error_message() );
		}else{
			wp_send_json_success(geodir_notification( array( 'info' => $result) ));
		}

		wp_die();
	}

	/**
	 * Best of listings widget ajax.
     *
     * @since 2.0.0
	 */
	public static function bestof(){
		// security
		check_ajax_referer( 'geodir_basic_nonce', 'security' );

		GeoDir_Widget_Best_Of::best_of(array(), $_REQUEST);

		wp_die();
	}

	/**
	 * GD Categories widget ajax.
     *
     * @since 2.0.0
	 */
	public static function cpt_categories(){
		// security
		check_ajax_referer( 'geodir_basic_nonce', 'security' );

		GeoDir_Widget_Categories::get_categories($_POST);

		wp_die();
	}

	/**
	 * Get GD > Tags widget ajax content.
	 *
	 * @since 2.8.103
	 */
	public static function cpt_tags() {
		check_ajax_referer( 'geodir_basic_nonce', 'security' );

		GeoDir_Widget_Tags::get_tags_ajax_content( $_POST );

		wp_die();
	}

	/**
	 * User delete post.
     *
     * @since 2.0.0
	 */
	public static function user_delete_post(){
		// security
		check_ajax_referer( 'geodir_basic_nonce', 'security' );

		$post_id = isset($_POST['post_id']) && $_POST['post_id'] ? absint($_POST['post_id']) : 0;

		$data = array();
		if(!$post_id){
			$data['message'] = __( "No post_id provided.", "geodirectory" );
			wp_send_json_error( $data );
		}else{
			$post_type = get_post_type( $post_id );

			$result = GeoDir_User::delete_post( $post_id );

			if(is_wp_error( $result ) ){
				$data['message'] = $result->get_error_message();
				wp_send_json_error( $data );
			}else{
			    $data['message'] = __( 'You have successfully deleted the Listing.', 'geodirectory' );
				$data['redirect_to'] = get_post_type_archive_link( $post_type );
				wp_send_json_success( $data );
			}
		}

		wp_die();
	}

    /**
     * Import export.
     *
     * @since 2.0.0
     */
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
		}

		wp_die();
	}

	/**
	 * Auto save post revisions and auto-drafts.
     *
     * @since 2.0.0
	 */
	public static function auto_save_post(){
		// security
		check_ajax_referer( 'geodir-save-post', 'security' );

		$result = GeoDir_Post_Data::auto_save_post( $_REQUEST );

		if(is_wp_error( $result ) ){
			wp_send_json_error( $result->get_error_message() );
		}else{
			wp_send_json_success($result);
		}

		wp_die();
	}

	/**
	 * Delete post revisions and auto-drafts.
     *
     * @since 2.0.0
     *
	 */
	public static function delete_revision(){
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
     *
     * @since 2.0.0
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
     *
     * @since 2.0.0
	 */
	public static function user_add_fav() {
		// security
		check_ajax_referer( 'geodir_basic_nonce', 'security' );

		$user_id = get_current_user_id();
		$post_id = absint( $_REQUEST['pid'] );
		$type_action = isset( $_REQUEST['type_action'] ) && $_REQUEST['type_action'] == 'add' ? 'add' : 'remove';

		if ( $user_id && $post_id ) {
			$data = array();

			if ( $type_action == 'add' ) {
				$result = GeoDir_User::add_fav( $post_id, $user_id );
				if ( $result ) {
					$data['action_text'] = apply_filters( 'geodir_favourite_text', __( 'Unfavorite', 'geodirectory' ) );
				}
			} else {
				$result = GeoDir_User::remove_fav( $post_id, $user_id );
				if ( $result ) {
					$data['action_text'] = apply_filters( 'geodir_unfavourite_text', __( 'Favorite', 'geodirectory' ) );
				}
			}

			if ( $result ){
				wp_send_json_success( $data );
			} else {
				wp_send_json_error( __( 'Action failed.', 'geodirectory' ) );
			}
		} else {
			wp_send_json_error( __( 'Action failed.', 'geodirectory' ) );
		}
		wp_die();
	}

	/**
	 * Get category select.
	 *
	 * @since 2.0.0
	 */
	public static function get_category_select(){
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

		$post_type = ! empty( $_REQUEST['post_type'] ) ? sanitize_text_field( $_REQUEST['post_type'] ) : '';
		$selected = isset( $_REQUEST['selected'] ) ? sanitize_text_field( $_REQUEST['selected'] ) : '';

		$tax = new GeoDir_Admin_Taxonomies();
		$tax->get_category_select( $post_type, $selected, false, true );

		wp_die();
	}

	/**
	 * Admin action to get a custom field sorting form.
     *
     * @since 2.0.0
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
		$field->htmlvar_name = isset($_REQUEST['field_type_key']) ? sanitize_text_field($_REQUEST['field_type_key']) : '';

		echo $cfs->output_custom_field_setting_item('',$field);
		wp_die();
	}

    /**
     * Post attachment upload.
     *
     * @since 2.0.0
     */
	public static function post_attachment_upload(){
		// security
		check_ajax_referer( 'geodir_attachment_upload', '_ajax_nonce' );

		GeoDir_Media::post_attachment_upload();
		wp_die();
	}

	/**
	 * Admin action to insert dummy data.
     *
     * @since 2.0.0
	 */
	public static function insert_dummy_data(){
		// security
		check_ajax_referer( 'geodir_dummy_data', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

		// defind doing import
		if( ! defined( 'GEODIR_DOING_IMPORT' ) ){
			define( 'GEODIR_DOING_IMPORT', true );
		}

		//Suspend cache additions
		wp_suspend_cache_addition(true);

		$result = GeoDir_Admin_Dummy_Data::create_dummy_posts( $_REQUEST );

		if(is_wp_error( $result ) ){
			wp_send_json_error( $result->get_error_message() );
		}else{
			wp_send_json_success();
		}

		wp_die();
	}

	/**
	 * Admin action to delete dummy data.
	 *
	 * @since 2.0.0
	 */
	public static function delete_dummy_data(){
		// Security
		check_ajax_referer( 'geodir_dummy_data', 'security' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

		$post_type = ! empty( $_REQUEST['post_type'] ) ? sanitize_text_field( $_REQUEST['post_type'] ) : '';

		if ( ! geodir_is_gd_post_type( $post_type ) ) {
			wp_send_json_error( __( 'Invalid post type', 'geodirectory' ) );
		}

		$result = GeoDir_Admin_Dummy_Data::delete_dummy_posts( $post_type );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		} else {
			wp_send_json_success();
		}

		wp_die();
	}

	/**
	 * Admin action to get a custom field form.
     *
     * @since 2.0.0
	 */
	public static function get_custom_field_form(){
		// security
		check_ajax_referer( 'gd_new_field_nonce', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

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
     *
     * @since 2.0.0
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

			global $wpdb;
			$fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . GEODIR_CUSTOM_SORT_FIELDS_TABLE . " WHERE id = %d OR tab_parent = %d ORDER BY sort_order ASC", array( $result, $result ) ) );
			$cfs = new GeoDir_Settings_Cpt_Sorting();
			if(count($fields) > 1){
				echo $cfs->loop_fields_output($fields);
			}else{
				echo $cfs->output_custom_field_setting_item($result);
			}

		}
		wp_die();
	}

	/**
	 * Admin action to delete a custom sort field.
     *
     * @since 2.0.0
	 */
	public static function delete_custom_sort_field(){
		// security
		$field_id = absint($_REQUEST['field_id']);
		check_ajax_referer( 'custom_fields_'.$field_id, 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

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
	 * Auto save custom field.
	 *
	 * @since 2.1.0.17
	 */
	public static function auto_save_custom_field() {
		// Security
		check_ajax_referer( 'custom_fields_' . sanitize_text_field( $_POST['field_id'] ), 'security' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

		$result = geodir_custom_field_save( $_POST );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		} else {
			$field = GeoDir_Settings_Cpt_Cf::get_item( $result );

			$data = array();
			if ( ! empty( $field ) ) {
				$data['field_id'] = $field->id;
				$data['htmlvar_name'] = $field->htmlvar_name;
				$data['admin_title'] = stripslashes( $field->admin_title );
				$data['field_icon'] = ! empty( $field->field_icon ) && geodir_is_fa_icon( $field->field_icon ) ? $field->field_icon : '';
				$data['nonce'] = wp_create_nonce( 'custom_fields_' . $field->id );
			}

			wp_send_json_success( $data );
		}
		wp_die();
	}

	/**
	 * Admin action to save a custom field.
     *
     * @since 2.0.0
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
			$field = GeoDir_Settings_Cpt_Cf::get_item( $result );
			$childs = GeoDir_Settings_Cpt_Cf::get_childs( $result );

			$cfs = new GeoDir_Settings_Cpt_Cf();
			$output = $cfs->output_custom_field_setting_item( $result, $field );

			// Child fields
			if ( ! empty( $childs ) ) {
				$output = str_replace( "</li>", "", $output );

				$output .= "<ul>";
				foreach ( $childs as $key => $child ) {
					$output .= $cfs::output_custom_field_setting_item( $child->id, $child );
				}
				$output .= "</ul>";

				$output .= "</li>";
			}

			echo $output;
		}
		wp_die();
	}

	/**
	 * Admin action to delete a custom field.
     *
     * @since 2.0.0
	 */
	public static function delete_custom_field(){
		// security
		$field_id = absint($_REQUEST['field_id']);
		check_ajax_referer( 'custom_fields_'.$field_id, 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

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
     *
     * @since 2.0.0
	 */
	public static function order_custom_fields(){
		// security
		check_ajax_referer( 'gd_new_field_nonce', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

		//print_r($_REQUEST);exit;

		$tabs = isset($_POST['tabs']) && $_POST['tabs'] ? $_POST['tabs'] : '';

		if(!$tabs){
			wp_send_json_error( __("No tabs provided.","geodirectory") );
		}else{

			$cfs = new GeoDir_Settings_Cpt_Cf();
			$result = $cfs->set_field_orders($tabs);

			//$result = GeoDir_Settings_Cpt_Tabs::set_tabs_orders($tabs);

			if(is_wp_error( $result ) ){
				wp_send_json_error( $result->get_error_message() );
			}else{
				wp_send_json_success();
			}
		}

		wp_die();
	}

	/**
	 * Admin action to save the custom sort fields sort order.
     *
     * @since 2.0.0
	 */
	public static function order_custom_sort_fields(){
		// security
		check_ajax_referer( 'gd_new_field_nonce', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

		$tabs = isset($_POST['tabs']) && $_POST['tabs'] ? $_POST['tabs'] : '';

		if(!$tabs){
			wp_send_json_error( __("No tabs provided.","geodirectory") );
		}else{
			$cfs = new GeoDir_Settings_Cpt_Sorting();
			$result = $cfs->set_field_orders($tabs);

			if(is_wp_error( $result ) ){
				wp_send_json_error( $result->get_error_message() );
			}else{
				wp_send_json_success();
			}
		}

		wp_die();
	}

	/**
	 * Create/Update API key.
     *
     * @since 2.0.0
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
				$data['revoke_url']      = '<a class="btn btn-danger" href="' . esc_url( wp_nonce_url( add_query_arg( array( 'revoke-key' => $key_id ), admin_url( 'admin.php?page=gd-settings&tab=api&section=keys' ) ), 'revoke' ) ) . '">' . __( 'Revoke key', 'geodirectory' ) . '</a>';
			}

			wp_send_json_success( $data );
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}
	}

	/**
	 * Search for customers and return json.
     *
     * @since 2.0.0
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

	/**
	 * GD Listings widget ajax pagination.
     *
     * @since 2.0.0
	 */
	public static function widget_listings(){
		check_ajax_referer( 'geodir_basic_nonce', 'security' );

		$widget_listings = new GeoDir_Widget_Listings();
		$widget_listings->ajax_listings( $_POST );

		wp_die();
	}

	/**
	 * Get timezone data for latitude & longitude.
     *
     * @since 2.0.0.66
	 */
	public static function timezone_data(){
		// security
		check_ajax_referer( 'geodir_basic_nonce', 'security' );

		$latitude = isset( $_POST['lat'] ) ? sanitize_text_field( $_POST['lat'] ) : '';
		$longitude = isset( $_POST['lon'] ) ? sanitize_text_field( $_POST['lon'] ) : '';
		$timestamp = isset( $_POST['ts'] ) ? absint( $_POST['ts'] ) : 0;

		$data = geodir_get_timezone_by_lat_lon( $latitude, $longitude, $timestamp = 0 );

		if ( is_wp_error( $data ) ) {
			wp_send_json_error( array( 'error' => $data->get_error_message() ) );
		} else {
			wp_send_json_success( $data );
		}

		wp_die();
	}

	/**
	 * Regenerate thumbnails for bulk attachments.
	 *
	 * @since 2.1.0.10
	 *
	 * @return mixed
	 */
	public static function tool_regenerate_thumbnails() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

		$page = ! empty( $_POST['per_page'] ) ? absint( $_POST['page'] ) : 1;
		$per_page = ! empty( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : 10;

		$data = GeoDir_Media::generate_bulk_attachment_metadata( $page, $per_page );

		if ( is_wp_error( $data ) ) {
			wp_send_json_error( array( 'error' => $data->get_error_message() ) );
		} else {
			wp_send_json_success( $data );
		}

		wp_die();
	}

	/**
	 * Regenerate thumbnails.
	 *
	 * @since 2.1.0.10
	 *
	 * @return mixed
	 */
	public static function regenerate_thumbnails() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

		$post_id = ! empty( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;

		$data = GeoDir_Media::generate_post_attachment_metadata( $post_id );

		if ( is_wp_error( $data ) ) {
			wp_send_json_error( array( 'error' => $data->get_error_message() ) );
		} else {
			wp_send_json_success( $data );
		}

		wp_die();
	}

	/**
	 * Handle user post action.
	 *
	 * @since 2.1.1.5
	 *
	 * @return mixed
	 */
	public static function post_author_action() {
		// Security
		check_ajax_referer( 'geodir_basic_nonce', 'security' );

		$the_post = ! empty( $_POST['post_id'] ) ? geodir_get_post_info( absint( $_POST['post_id'] ) ) : array();
		$action = ! empty( $_POST['_action'] ) ? geodir_clean( $_POST['_action'] ) : '';

		$data = array();
		if ( empty( $the_post ) ) {
			$data['message'] = __( 'Invalid post.', 'geodirectory' );
			wp_send_json_error( $data );
		} else {
			if ( ! geodir_listing_belong_to_current_user( $the_post->ID ) ) {
				$data['message'] = __( 'Invalid post.', 'geodirectory' );
				wp_send_json_error( $data );
			} else {
				$result = GeoDir_User::post_author_action( $action, $the_post );

				if ( is_wp_error( $result ) ) {
					$data['message'] = $result->get_error_message();

					wp_send_json_error( $data );
				} else {
					wp_send_json_success( $result );
				}
			}
		}

		wp_die();
	}

	/**
	 * Handle report post action.
	 *
	 * @since 2.1.1.12
	 *
	 * @return mixed
	 */
	public static function report_post_form() {
		// Security
		check_ajax_referer( 'geodir_basic_nonce', 'security' );

		$the_post = ! empty( $_POST['post_id'] ) ? geodir_get_post_info( absint( $_POST['post_id'] ) ) : array();
		$extra = ! empty( $_POST['extra'] ) ? geodir_clean( $_POST['extra'] ) : '';

		$success = false;
		$data = array(
			'title' => __( 'Report Post', 'geodirectory' )
		);

		if ( empty( $the_post ) ) {
			$data['body'] = aui()->alert(
				array(
					'type'=> 'warning',
					'content'=> __( 'Invalid post.', 'geodirectory' ),
					'class' => 'mb-0'
				)
			);
		} else {
			if ( ! apply_filters( 'geodir_allow_report_post', true, $the_post ) ) {
				$data['body'] = aui()->alert(
					array(
						'type'=> 'warning',
						'content'=> __( 'You can\' report this post.', 'geodirectory' ),
						'class' => 'mb-0'
					)
				);
			} else {
				$success = true;
				$data['body'] = GeoDir_Report_Post::get_form( $the_post );
			}
		}

		$data = apply_filters( 'geodir_report_post_form_response', $data, $success );

		if ( $success ) {
			wp_send_json_success( $data );
		} else {
			wp_send_json_error( $data );
		}

		wp_die();
	}

	/**
	 * Handle report post request.
	 *
	 * @since 2.1.1.12
	 *
	 * @return mixed
	 */
	public static function submit_report_post() {
		// Security
		check_ajax_referer( 'geodir_basic_nonce', 'security' );

		GeoDir_Report_Post::handle_request( $_POST );

		wp_die();
	}

	/**
	 * Handle new template request.
	 *
	 * @since 2.2.4
	 *
	 * @return mixed
	 */
	public static function new_wp_template() {
		if ( ! class_exists( 'GeoDir_Block_Theme' ) ) {
			wp_die( -1 );
		}

		// Security
		check_ajax_referer( 'geodir_basic_nonce', 'security' );

		GeoDir_Block_Theme::handle_new_template_request( $_POST );

		wp_die();
	}

	/**
	 * Handle business hours today post meta.
	 *
	 * @since 2.2.12
	 *
	 * @return mixed
	 */
	public static function business_hours_post_meta() {
		// Security
		check_ajax_referer( 'geodir_basic_nonce', 'security' );

		$data = geodir_business_hours_post_meta( $_POST );

		wp_send_json_success( $data );

		wp_die();
	}
}
