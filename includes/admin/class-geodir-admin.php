<?php
/**
 * GeoDirectory Admin
 *
 * @class    GeoDir_Admin
 * @author   AyeCode
 * @category Admin
 * @package  GeoDirectory/Admin
 * @version  2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * GeoDir_Admin class.
 */
class GeoDir_Admin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'includes' ) );
		add_action( 'current_screen', array( $this, 'conditional_includes' ) );
		add_action( 'admin_init', array( $this, 'buffer' ), 1 );
		add_action( 'admin_init', array( $this, 'preview_emails' ) );
		add_action( 'admin_init', array( $this, 'prevent_admin_access' ) );
		add_action( 'admin_init', array( $this, 'admin_redirects' ) );
		add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ), 1 );
		add_action( 'wp_ajax_geodirectory_rated', array( $this,'geodirectory_rated') );

		// Add labels to the GD pages.
		add_filter('display_post_states',array( $this, 'set_page_labels' ),10,2);

		global $pagenow;
//		echo $pagenow.'###';exit;
		if ( ( ! empty( $_POST['action'] ) && ( $_POST['action'] == 'save-widget' || $_POST['action'] == 'update-widget' ) ) || $pagenow == 'widgets.php' || $pagenow == 'customize.php' ) { // handle save widget
			GeoDir_Admin_Widgets::init();
		}

		// hide the plugin install button on setup wizard plugin more info iframe
		add_action("admin_print_footer_scripts-plugin-install.php",array($this,'hide_plugin_install'));
		

		// clear extrnsion transients if activating/deactivating
		if(isset($_REQUEST['exup_action']) && ($_REQUEST['exup_action']=='activate_membership_key'|| $_REQUEST['exup_action']=='deactivate_membership_key' )){
			delete_transient( 'gd_addons_section_addons' );
			delete_transient( 'gd_addons_section_themes' );
		}

		if ( ! empty( $_REQUEST['action'] ) && in_array( $_REQUEST['action'], array( 'geodir_post_attachment_upload', 'geodir_import_export' ) ) ) {
			add_filter( 'upload_mimes', array( $this, 'upload_mimes' ), 11, 2 );
			add_filter( 'wp_check_filetype_and_ext', array( $this, 'wp_check_filetype_and_ext' ), 10, 5 );
		}

		// disable GD pages from being able to be selected for some settings
		add_filter( 'wp_dropdown_pages',array( $this, 'dropdown_pages_disable' ), 10,3 );

		// Deactivate legacy plugins
		$this->deactivate_plugin();

		// Register with the deactivation survey class.
		AyeCode_Deactivation_Survey::instance(array(
			'slug'		=> 'geodirectory',
			'version'	=> GEODIRECTORY_VERSION,
			'support_url'=> 'https://wpgeodirectory.com/support/',
			'documentation_url'=> 'https://docs.wpgeodirectory.com/',
			'activated' => 0 // a timestamp of when original activated @todo implement
		));
	}
	
	/**
	 * Disable some GD pages for some WP page settings.
	 *
	 * @param $output
	 * @param $r
	 * @param $pages
	 *
	 * @return mixed
	 */
	public function dropdown_pages_disable($output, $r, $pages){

		$disable_for = array('page_on_front','page_for_posts');
		$name = isset($r['name']) ? $r['name'] : '';
		if($output && $name && in_array($name,$disable_for)){

			$pages = array();
			$pages[] = geodir_location_page_id(); // location
			$pages[] = geodir_search_page_id(); // search
			$pages[] = geodir_archive_page_id(); // archive
			$pages[] = geodir_archive_item_page_id(); // archive item
			$pages[] = geodir_details_page_id(); // details
			$pages[] = geodir_add_listing_page_id(); // add listing

			$pages = array_filter($pages); // remove any empty ids

			if(!empty($pages)){
				foreach($pages as $id){
					if($id){
						$output = str_replace(' value="'.$id.'">', ' value="'.$id.'" disabled >',$output);
					}
				}
			}
		}

		return $output;
	}


	/**
	 * This hides the install plugin from more info iframe on the setup wizard.
	 */
	public function hide_plugin_install(){
		if(
			isset($_REQUEST['tab']) && $_REQUEST['tab']=='plugin-information'
			&& isset($_REQUEST['gd_wizard_recommend']) && $_REQUEST['gd_wizard_recommend']=='true'
		){
			echo "<style>#plugin-information-footer{display: none;}</style>";
		}
	}

	/**
	 * Set the rating flag so we don't ask the user for rating anymore
	 *
	 * @since 2.0.0
	 */
	public function geodirectory_rated() {
		if ( current_user_can( 'manage_options' ) && ! empty( $_REQUEST['_gdnonce'] ) && wp_verify_nonce( $_REQUEST['_gdnonce'], 'geodirectory_rated' ) ) {
			update_option( 'geodirectory_admin_footer_text_rated', true );

			wp_die();
		}

		wp_die( -1 );
	}

	/**
	 * Add labels to the GD pages to help identify them.
	 *
	 * @param $post_states
	 * @param $post
	 * @since 2.0.0
	 *
	 * @return mixed
	 */
	public function set_page_labels( $post_states, $post ) {
		if ( $post->ID == geodir_add_listing_page_id() ) {
			$post_states['geodir_add_listing_page'] = __( 'GD Add listing page', 'geodirectory' ) .
			                                          geodir_help_tip( __( 'This is where users will add listings via the frontend if enabled.', 'geodirectory' ) );
		} elseif ( $post->ID == geodir_location_page_id() ) {
			$post_states['geodir_location_page'] = __( 'GD Location page', 'geodirectory' ) .
			                                       geodir_help_tip( __( 'This page can be used as the main directory page and is also used by some addons.', 'geodirectory' ) );
		} elseif ( $post->ID == geodir_search_page_id() ) {
			$post_states['geodir_search_page'] = __( 'GD Search page', 'geodirectory' ) .
			                                     geodir_help_tip( __( 'This is the GD search results page.', 'geodirectory' ) );
		} elseif ( $post->ID == geodir_archive_page_id() ) {
			$post_states['geodir_archive_page'] = __( 'GD Archive template', 'geodirectory' ) .
			                                      geodir_help_tip( __( 'Used to design the archive pages but should never be linked to directly.', 'geodirectory' ) );
		} elseif ( $post->ID == geodir_archive_item_page_id() ) {
			$post_states['geodir_archive_item_page'] = __( 'GD Archive Item template', 'geodirectory' ) .
			                                      geodir_help_tip( __( 'Used to design the archive items but should never be linked to directly.', 'geodirectory' ) );
		} elseif ( $post->ID == geodir_details_page_id() ) {
			$post_states['geodir_details_page'] = __( 'GD Details template', 'geodirectory' ) .
			                                      geodir_help_tip( __( 'Used to design the details page but should never be linked to directly.', 'geodirectory' ) );
		} elseif ( $post->ID == geodir_terms_and_conditions_page_id() ) {
			$post_states['geodir_terms_and_conditions_page'] = __( 'GD T&Cs', 'geodirectory' ) .
			                                                   geodir_help_tip( __( 'This is the page that will be used for your terms and conditions.', 'geodirectory' ) );
		}

		$post_types = geodir_get_posttypes( 'array' );
		foreach ( $post_types as $post_type => $post_type_arr ) {
			$name = $post_type_arr['labels']['singular_name'];

			if ( ! empty( $post_type_arr[ 'page_archive' ] ) && $post->ID == $post_type_arr[ 'page_archive' ] ) {
				$post_states['geodir_archive_page_' . $post_type] = wp_sprintf( __( 'GD Archive template (%s)', 'geodirectory' ), $name ) .
													  geodir_help_tip( wp_sprintf( __( 'Used to design the %s archive pages but should never be linked to directly.', 'geodirectory' ), $name ) );
			} else if ( ! empty( $post_type_arr[ 'page_archive_item' ] ) && $post->ID == $post_type_arr[ 'page_archive_item' ] ) {
				$post_states['geodir_archive_item_page_' . $post_type] = wp_sprintf( __( 'GD Archive Item template (%s)', 'geodirectory' ), $name ) .
													  geodir_help_tip( wp_sprintf( __( 'Used to design the %s archive items but should never be linked to directly.', 'geodirectory' ), $name ) );
			} else if ( ! empty( $post_type_arr[ 'page_details' ] ) && $post->ID == $post_type_arr[ 'page_details' ] ) {
				$post_states['geodir_details_page_' . $post_type] = wp_sprintf( __( 'GD Details template (%s)', 'geodirectory' ), $name ) .
													  geodir_help_tip( wp_sprintf( __( 'Used to design the %s details page but should never be linked to directly.', 'geodirectory' ), $name ) );
			} else if ( ! empty( $post_type_arr[ 'page_add' ] ) && $post->ID == $post_type_arr[ 'page_add' ] ) {
				$post_states['geodir_add_listing_page_' . $post_type] = wp_sprintf( __( 'GD Add listing page (%s)', 'geodirectory' ), $name ) .
													  geodir_help_tip( wp_sprintf( __( 'Used to design the add %s page for the frontend.', 'geodirectory' ), $name ) );
			}
		}

		return $post_states;
	}

	/**
	 * Output buffering allows admin screens to make redirects later on.
	 */
	public function buffer() {
		// Prevent plugin activation redirect on plugin install during setup wizard.
		if ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'gd-setup' && ! empty( $_REQUEST['step'] ) ) {
			if ( $_REQUEST['step'] == 'features' || $_REQUEST['step'] == 'content' ) {
				if ( get_option( 'uwp_activation_redirect', false ) ) {
					delete_option( 'uwp_activation_redirect' );
					update_option( "uwp_setup_wizard_notice", 1 );
				}
			}
		}

		ob_start();
	}

	/**
	 * Include any classes we need within admin.
	 */
	public function includes() {
		include_once( dirname( __FILE__ ) . '/admin-functions.php' );
		include_once( dirname( __FILE__ ) . '/class-geodir-admin-settings.php' );
		include_once( dirname( __FILE__ ) . '/class-geodir-admin-comments.php' );
		include_once( dirname( __FILE__ ) . '/class-geodir-admin-menus.php' );
		include_once( dirname( __FILE__ ) . '/class-geodir-admin-notices.php' );
		include_once( dirname( __FILE__ ) . '/class-geodir-admin-assets.php' );
		include_once( dirname( __FILE__ ) . '/class-geodir-admin-api-keys.php' );
		include_once( dirname( __FILE__ ) . '/class-geodir-admin-blocks.php' );
		if ( geodir_design_style() ) {
			include_once( dirname( __FILE__ ) . '/class-geodir-admin-conditional-fields.php' );
		}

		// Help Tabs
		if ( apply_filters( 'geodir_enable_admin_help_tab', true ) ) {
			include_once( dirname( __FILE__ ) . '/class-geodir-admin-help.php' );
		}

		// Setup/welcome
		if ( ! empty( $_GET['page'] ) ) {
			switch ( $_GET['page'] ) {
				case 'gd-setup' :
					include_once( dirname( __FILE__ ) . '/class-geodir-admin-setup-wizard.php' );
				break;
			}
		}

		// Importers
		if ( defined( 'WP_LOAD_IMPORTERS' ) ) {
			//include_once( dirname( __FILE__ ) . '/class-wc-admin-importers.php' );
		}

		// load the user class only on the users.php page
		global $pagenow;
		if($pagenow=='users.php'){
			new GeoDir_Admin_Users();
		}

		// AyeCode Connect notice
		if ( is_admin() ){
			// set the strings so they can be translated
			$strings = array(
				'connect_title' => __("GeoDirectory - an AyeCode product!","geodirectory"),
				'connect_external'  => __( "Please confirm you wish to connect your site?","geodirectory" ),
				'connect'           => sprintf( __( "<strong>Have a license?</strong> Forget about entering license keys or downloading zip files, connect your site for instant access. %slearn more%s","geodirectory" ),"<a href='https://ayecode.io/introducing-ayecode-connect/' target='_blank'>","</a>" ),
				'connect_button'    => __("Connect Site","geodirectory"),
				'connecting_button'    => __("Connecting...","geodirectory"),
				'error_localhost'   => __( "This service will only work with a live domain, not a localhost.","geodirectory" ),
				'error'             => __( "Something went wrong, please refresh and try again.","geodirectory" ),
			);
			new AyeCode_Connect_Helper($strings,array('gd-addons'));
		}
	}

	/**
	 * Include admin files conditionally.
	 */
	public function conditional_includes() {
		if ( ! $screen = get_current_screen() ) {
			return;
		}

		switch ( $screen->id ) {
			case 'dashboard' :
				include( 'class-geodir-wp-dashboard.php' );
			break;
			case 'options-permalink' :
				include( 'class-geodir-admin-permalink-settings.php' );
			break;
			case 'users' :
			case 'user' :
			case 'profile' :
			case 'user-edit' :
				//include( 'class-wc-admin-profile.php' );
			break;
			case 'customize':
			case 'widgets' :
				GeoDir_Admin_Widgets::init();
			break;
		}
	}

	/**
	 * Handle redirects to setup/welcome page after install and updates.
	 *
	 * For setup wizard, transient must be present, the user must have access rights, and we must ignore the network/bulk plugin updaters.
	 */
	public function admin_redirects() {
		// Nonced plugin install redirects (whitelisted)
		if ( ! empty( $_GET['gd-install-plugin-redirect'] ) ) {
			$plugin_slug = geodir_clean( $_GET['gd-install-plugin-redirect'] );

			$url = admin_url( 'plugin-install.php?tab=search&type=term&s=' . $plugin_slug );

			wp_safe_redirect( $url );
			exit;
		}

		// Setup wizard redirect
		if ( get_transient( '_gd_activation_redirect' ) ) {
			delete_transient( '_gd_activation_redirect' );

			if ( ( ! empty( $_GET['page'] ) && in_array( $_GET['page'], array( 'gd-setup' ) ) ) || is_network_admin() || isset( $_GET['activate-multi'] ) || ! current_user_can( 'manage_options' ) || apply_filters( 'geodir_prevent_automatic_wizard_redirect', false ) ) {
				return;
			}

			// If the user needs to install, send them to the setup wizard
			if ( GeoDir_Admin_Notices::has_notice( 'install' ) ) {
				wp_safe_redirect( admin_url( 'index.php?page=gd-setup' ) );
				exit;
			}
		}
	}

	/**
	 * Restrict the wp-admin area from specific user roles if set to do so.
	 */
	public function prevent_admin_access() {
		$restricted_roles = geodir_get_option( 'admin_blocked_roles', array() );

		// Checking action in request to allow ajax request go through
		if ( ! empty ( $restricted_roles ) && is_user_logged_in() && ! wp_doing_ajax() && ! wp_doing_cron() ) {
			$roles = wp_get_current_user()->roles;

			$prevent = false;

			// Always allow administrator role.
			if ( ! ( ! empty( $roles ) && in_array( 'administrator', $roles ) ) ) {
				foreach( $restricted_roles as $role ) {
					if ( in_array( $role, $roles ) ) {
						$prevent = true;
						break;
					}
				}
			}

			/*
			 * Check and prevent admin access based on user role.
			 *
			 * @since 2.1.0.16
			 *
			 * @param bool $prevent True to prevent admin access.
			 */
			$prevent = apply_filters( 'geodir_prevent_wp_admin_access', $prevent );

			if ( $prevent ) {
				wp_safe_redirect( home_url() );
				exit;
			}
		}
	}

	/**
	 * Preview email template.
	 *
	 * @return string
	 */
	public function preview_emails() {
		if ( isset( $_GET['geodir_preview_mail'] ) ) {
			if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'geodir-preview-mail' ) ) {
				die( 'Security check' );
			}

			$email_name = 'preview_mail';
			$email_vars = array();
			$plain_text = GeoDir_Email::get_email_type() != 'html' ? true : false;

			// Get the preview email content.
			ob_start();
			include( 'views/html-email-template-preview.php' );
			$message = ob_get_clean();
			
			$message 	= GeoDir_Email::email_wrap_message( $message, $email_name, $email_vars, '', $plain_text );
			$message 	= GeoDir_Email::style_body( $message, $email_name, $email_vars );
			$message 	= apply_filters( 'geodir_mail_content', $message, $email_name, $email_vars );

			// Print the preview email content.
			if ( $plain_text ) {
				echo '<div style="white-space:pre-wrap;font-family:sans-serif">';
			}
			echo $message;
			if ( $plain_text ) {
				echo '</div>';
			}
			exit;
		}
	}

	/**
	 * Change the admin footer text on GeoDirectory admin pages.
	 *
	 * @since  2.0.0
	 * @param  string $footer_text
	 * @return string
	 */
	public function admin_footer_text( $footer_text ) {
		if ( ! current_user_can( 'manage_options' ) || ! function_exists( 'geodir_get_screen_ids' ) ) {
			return $footer_text;
		}
		$current_screen = get_current_screen();
		$gd_pages       = geodir_get_screen_ids();


		// Set only GD pages.
		$gd_pages = array_diff( $gd_pages, array( 'profile', 'user-edit' ) );

		// Check to make sure we're on a GeoDirectory admin page.
		if ( isset( $current_screen->id ) && apply_filters( 'geodirectory_display_admin_footer_text', in_array( $current_screen->id, $gd_pages ) ) ) {
			// Change the footer text
			if ( ! get_option( 'geodirectory_admin_footer_text_rated' ) ) {
				/* translators: %s: five stars */
				$footer_text = sprintf( __( 'If you like <strong>GeoDirectory</strong> please leave us a %s rating. A huge thanks in advance!', 'geodirectory' ), '<a href="https://wordpress.org/support/plugin/geodirectory/reviews?rate=5#new-post" target="_blank" class="gd-rating-link" data-rated="' . esc_attr__( 'Thanks :)', 'geodirectory' ) . '" data-nonce="' . esc_attr( wp_create_nonce( 'geodirectory_rated' ) ) . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>' );
			} else {
				$footer_text = __( 'Thank you for using GeoDirectory!', 'geodirectory' );
			}
		}

		return $footer_text;
	}

	/**
	 * Attempt to deactivate the legacy plugins.
	 *
	 * @since  2.0.0.62
	 */
	public function deactivate_plugin() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		if ( ! function_exists( 'deactivate_plugins' ) ) {
			return;
		}

		// Deactivate GD Dashboard plugin.
		if ( function_exists( 'GD_Dashboard_init' ) && is_plugin_active( 'geodir_dashboard/geodir_dashboard.php' ) ) {
			deactivate_plugins( 'geodir_dashboard/geodir_dashboard.php' );
		}
	}

	/**
	 * Filter mime types to allow json file type for import settings.
	 *
	 * @since 2.2.9
	 *
	 * @param array            $mimes Mime types keyed by the file extension regex corresponding to those types.
	 * @param int|WP_User|null $user User ID, User object or null if not provided (indicates current user).
	 */
	public function upload_mimes( $mimes, $user ) {
		if ( empty( $mimes['json'] ) && ! empty( $_POST['imgid'] ) && $_POST['imgid'] == 'gd_im_settings' && ! empty( $_POST['name'] ) && strtolower( substr( strrchr( $_POST['name'], '.' ), 1 ) ) == 'json' && current_user_can( 'manage_options' ) ) {
			$mimes['json'] = 'application/json';
		}

		return $mimes;
	}

	/**
	 * Filter the "real" file type of the CSV file during import.
	 *
	 * @since 2.1.0.5
	 *
	 * @param array       $data Values for the extension, mime type, and corrected filename.
	 * @param string      $file Full path to the file.
	 * @param string      $filename The name of the file (may differ from $file due to $file being in a tmp directory).
	 * @param string[]    $mimes Array of mime types keyed by their file extension regex.
	 * @param string|bool $real_mime The actual mime type or false if the type cannot be determined.
	 */
	public function wp_check_filetype_and_ext( $data, $file, $filename, $mimes, $real_mime = '' ) {
		$imgid = ! empty( $_REQUEST['imgid'] ) ? sanitize_text_field( $_REQUEST['imgid'] ) : '';

		if ( ( ( strpos( $imgid, 'gd_im_' ) === 0 && ! empty( $_REQUEST['name'] ) ) || defined( 'GEODIR_DOING_IMPORT' ) ) && current_user_can( 'manage_options' ) && ( strtolower( substr( strrchr( $filename, '.' ), 1 ) ) == 'csv' || ( $imgid == 'gd_im_settings' && strtolower( substr( strrchr( $filename, '.' ), 1 ) ) == 'json' ) ) ) {
			$wp_filetype = wp_check_filetype( $filename, $mimes );

			$ext = $wp_filetype['ext'];
			$type = $wp_filetype['type'];
			$proper_filename = $data['proper_filename'];

			$data = compact( 'ext', 'type', 'proper_filename' );
		}

		return $data;
	}
}