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
		//add_action( 'admin_footer', 'wc_print_js', 25 );
		add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ), 1 );
		add_action( 'wp_ajax_geodirectory_rated', array( $this,'geodirectory_rated') );

		// Add labels to the GD pages.
		add_filter('display_post_states',array( $this, 'set_page_labels' ),10,2);



	}

	/**
	 * Set the rating flag so we don't ask the user for rating anymore
	 *
	 * @since 2.0.0
	 */
	public function geodirectory_rated(){
		update_option( 'geodirectory_admin_footer_text_rated', true );
		wp_die();
	}

	/**
	 * Add labels tot he GD pages to help identify them.
	 *
	 * @param $post_states
	 * @param $post
	 * @since 2.0.0
	 *
	 * @return mixed
	 */
	public function set_page_labels( $post_states, $post ) {
		if ( $post->ID == geodir_home_page_id() ) {
			$post_states['geodir_home_page'] = __( 'GD Home Page', 'geodirectory' ) .
			                                   gd_help_tip( __( 'If you wish to use GD as your homepage you should use this page in the settings.', 'geodirectory' ) );
		} elseif ( $post->ID == geodir_add_listing_page_id() ) {
			$post_states['geodir_add_listing_page'] = __( 'GD Add listing page', 'geodirectory' ) .
			                                          gd_help_tip( __( 'This is where users will add listings via the frontend if enabled.', 'geodirectory' ) );
		} elseif ( $post->ID == geodir_location_page_id() ) {
			$post_states['geodir_location_page'] = __( 'GD Location page', 'geodirectory' ) .
			                                       gd_help_tip( __( 'This page can be used as the main directory page and is also used by some addons.', 'geodirectory' ) );
		} elseif ( $post->ID == geodir_search_page_id() ) {
			$post_states['geodir_search_page'] = __( 'GD Search page', 'geodirectory' ) .
			                                     gd_help_tip( __( 'This is the GD search results page.', 'geodirectory' ) );
		} elseif ( $post->ID == geodir_archive_page_id() ) {
			$post_states['geodir_archive_page'] = __( 'GD Archive template', 'geodirectory' ) .
			                                      gd_help_tip( __( 'Used to design the archive pages but should never be linked to directly.', 'geodirectory' ) );
		} elseif ( $post->ID == geodir_details_page_id() ) {
			$post_states['geodir_details_page'] = __( 'GD Details template', 'geodirectory' ) .
			                                      gd_help_tip( __( 'Used to design the details page but should never be linked to directly.', 'geodirectory' ) );
		} elseif ( $post->ID == geodir_terms_and_conditions_page_id() ) {
			$post_states['geodir_terms_and_conditions_page'] = __( 'GD T&Cs', 'geodirectory' ) .
			                                                   gd_help_tip( __( 'This is the page that will be used for your terms and conditions.', 'geodirectory' ) );
		}

		return $post_states;
	}

	/**
	 * Output buffering allows admin screens to make redirects later on.
	 */
	public function buffer() {
		ob_start();
	}

	/**
	 * Include any classes we need within admin.
	 */
	public function includes() {
		include_once( dirname( __FILE__ ) . '/gd-admin-functions.php' );
		include_once( dirname( __FILE__ ) . '/class-geodir-admin-settings.php' );
//		include_once( dirname( __FILE__ ) . '/wc-meta-box-functions.php' );
//		include_once( dirname( __FILE__ ) . '/class--admin-post-types.php' );
//		include_once( dirname( __FILE__ ) . '/class-wc-admin-taxonomies.php' );
		include_once( dirname( __FILE__ ) . '/class-geodir-admin-menus.php' );
		include_once( dirname( __FILE__ ) . '/class-geodir-admin-notices.php' );
		include_once( dirname( __FILE__ ) . '/class-geodir-admin-assets.php' );
//		include_once( dirname( __FILE__ ) . '/class-wc-admin-api-keys.php' );
//		include_once( dirname( __FILE__ ) . '/class-wc-admin-webhooks.php' );
//		include_once( dirname( __FILE__ ) . '/class-wc-admin-pointers.php' );

		// Help Tabs @todo to we want to use the help tabs?
		if ( apply_filters( 'geodirectory_enable_admin_help_tab', true ) ) {
			//include_once( dirname( __FILE__ ) . '/class-wc-admin-help.php' );
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
				include( 'class-wc-admin-profile.php' );
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
		if ( ! empty( $_GET['wc-install-plugin-redirect'] ) ) {
			$plugin_slug = wc_clean( $_GET['wc-install-plugin-redirect'] );

			if ( current_user_can( 'install_plugins' ) && in_array( $plugin_slug, array( 'woocommerce-gateway-stripe' ) ) ) {
				$nonce = wp_create_nonce( 'install-plugin_' . $plugin_slug );
				$url   = self_admin_url( 'update.php?action=install-plugin&plugin=' . $plugin_slug . '&_wpnonce=' . $nonce );
			} else {
				$url = admin_url( 'plugin-install.php?tab=search&type=term&s=' . $plugin_slug );
			}

			wp_safe_redirect( $url );
			exit;
		}

		// Setup wizard redirect
		if ( get_transient( '_wc_activation_redirect' ) ) {
			delete_transient( '_wc_activation_redirect' );

			if ( ( ! empty( $_GET['page'] ) && in_array( $_GET['page'], array( 'gd-setup' ) ) ) || is_network_admin() || isset( $_GET['activate-multi'] ) || ! current_user_can( 'manage_woocommerce' ) || apply_filters( 'woocommerce_prevent_automatic_wizard_redirect', false ) ) {
				return;
			}

			// If the user needs to install, send them to the setup wizard
			if ( WC_Admin_Notices::has_notice( 'install' ) ) {
				wp_safe_redirect( admin_url( 'index.php?page=gd-setup' ) );
				exit;
			}
		}
	}

	/**
	 * Prevent any user who cannot 'edit_posts' (subscribers, customers etc) from accessing admin.
	 */
	public function prevent_admin_access() {
		$prevent_access = false;
return;//
		if ( 'yes' === get_option( 'woocommerce_lock_down_admin', 'yes' ) && ! is_ajax() && basename( $_SERVER["SCRIPT_FILENAME"] ) !== 'admin-post.php' ) {
			$has_cap     = false;
			$access_caps = array( 'edit_posts', 'manage_woocommerce', 'view_admin_dashboard' );

			foreach ( $access_caps as $access_cap ) {
				if ( current_user_can( $access_cap ) ) {
					$has_cap = true;
					break;
				}
			}

			if ( ! $has_cap ) {
				$prevent_access = true;
			}
		}

		if ( apply_filters( 'woocommerce_prevent_admin_access', $prevent_access ) ) {
			wp_safe_redirect( wc_get_page_permalink( 'myaccount' ) );
			exit;
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

			// Get the preview email content.
			ob_start();
			include( 'views/html-email-template-preview.php' );
			$message = ob_get_clean();
			
			$message 	= geodir_email_wrap_message( $message, $email_name, $email_vars );
			$message 	= geodir_email_style_body( $message, $email_name, $email_vars );
			$message 	= apply_filters( 'geodir_mail_content', $message, $email_name, $email_vars );

			// Print the preview email content.
			echo $message;
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
		if ( ! current_user_can( 'manage_options' ) || ! function_exists( 'gd_get_screen_ids' ) ) {
			return $footer_text;
		}
		$current_screen = get_current_screen();
		$gd_pages       = gd_get_screen_ids();


		// Set only GD pages.
		$gd_pages = array_diff( $gd_pages, array( 'profile', 'user-edit' ) );

		// Check to make sure we're on a WooCommerce admin page.
		if ( isset( $current_screen->id ) && apply_filters( 'geodirectory_display_admin_footer_text', in_array( $current_screen->id, $gd_pages ) ) ) {
			// Change the footer text
			if ( ! get_option( 'geodirectory_admin_footer_text_rated' ) ) {
				/* translators: %s: five stars */
				$footer_text = sprintf( __( 'If you like <strong>GeoDirectory</strong> please leave us a %s rating. A huge thanks in advance!', 'geodirectory' ), '<a href="https://wordpress.org/support/plugin/geodirectory/reviews?rate=5#new-post" target="_blank" class="gd-rating-link" data-rated="' . esc_attr__( 'Thanks :)', 'geodirectory' ) . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>' );

			} else {
				$footer_text = __( 'Thank you for using GeoDirectory!', 'geodirectory' );
			}
		}

		return $footer_text;
	}
}