<?php
/**
 * Setup Wizard Class
 *
 * Takes new users through some basic steps to setup their directory.
 *
 * @author      AyeCode
 * @category    Admin
 * @package     GeoDirectroy/Admin
 * @version     2.0.0
 * @info        GeoDirectory Class used as a base.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GeoDir_Admin_Setup_Wizard class.
 */
class GeoDir_Admin_Setup_Wizard {

	/** @var string Current Step */
	private $step = '';

	/** @var array Steps for the setup wizard */
	private $steps = array();

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		if ( apply_filters( 'geodir_enable_setup_wizard', true ) && current_user_can( 'manage_options' ) ) {
			add_action( 'admin_menu', array( $this, 'admin_menus' ) );
			add_action( 'current_screen', array( $this, 'setup_wizard' ) );

			// add default content action
			add_action( 'geodir_wizard_content_dummy_data', array( __CLASS__, 'content_dummy_data' ) );
			add_action( 'geodir_wizard_content_sidebars', array( __CLASS__, 'content_sidebars' ) );
			add_action( 'geodir_wizard_content_menus', array( __CLASS__, 'content_menus' ) );
		}
	}

	/**
	 * Add admin menus/screens.
	 */
	public function admin_menus() {
		add_dashboard_page( '', '', 'manage_options', 'gd-setup', '' );
	}

	/**
	 * Show the setup wizard.
	 *
	 * @since 2.0.0
	 */
	public function setup_wizard() {
		if ( empty( $_GET['page'] ) || 'gd-setup' !== $_GET['page'] ) {
			return;
		}
		$default_steps = array(
			'introduction'     => array(
				'name'    => __( 'Introduction', 'geodirectory' ),
				'view'    => array( $this, 'setup_introduction' ),
				'handler' => '',
			),
			'maps'             => array(
				'name'    => __( "Map's", 'geodirectory' ),
				'view'    => array( $this, 'setup_maps' ),
				'handler' => array( $this, 'setup_maps_save' ),
			),
			'default_location' => array(
				'name'    => __( 'Default Location', 'geodirectory' ),
				'view'    => array( $this, 'setup_default_location' ),
				'handler' => array( $this, 'setup_default_location_save' ),
			),
			'recommend'        => array(
				'name'    => __( 'Recommend', 'geodirectory' ),
				'view'    => array( $this, 'setup_recommend' ),
				'handler' => array( $this, 'setup_recommend_save' ),
			),
			'content'          => array(
				'name'    => __( 'Content', 'geodirectory' ),
				'view'    => array( $this, 'setup_content' ),
				'handler' => array( $this, 'setup_content_save' ),
			),
			'next_steps'       => array(
				'name'    => __( 'Ready!', 'geodirectory' ),
				'view'    => array( $this, 'setup_ready' ),
				'handler' => '',
			),
		);

		$this->steps     = apply_filters( 'geodirectory_setup_wizard_steps', $default_steps );
		$this->step      = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : current( array_keys( $this->steps ) );
		$suffix          = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$geodir_map_name = GeoDir_Maps::active_map();

		// load OSM styles if needed.
		if ( $geodir_map_name == 'osm' ) {
			wp_enqueue_style( 'geodir-leaflet-style' );
		}


		// map arguments
		$map_lang = "&language=" . GeoDir_Maps::map_language();
		$map_key  = GeoDir_Maps::google_api_key( true );
		/**
		 * Filter the variables that are added to the end of the google maps script call.
		 *
		 * This i used to change things like google maps language etc.
		 *
		 * @since 1.0.0
		 *
		 * @param string $var The string to filter, default is empty string.
		 */
		$map_extra = apply_filters( 'geodir_googlemap_script_extra', '' );

		wp_register_script( 'geodir-goMap', geodir_plugin_url() . '/assets/js/goMap' . $suffix . '.js', array(), GEODIRECTORY_VERSION, true );
		wp_register_script( 'geodir-google-maps', 'https://maps.google.com/maps/api/js?' . $map_lang . $map_key . $map_extra, array(), GEODIRECTORY_VERSION );
		wp_register_script( 'geodir-g-overlappingmarker-script', geodir_plugin_url() . '/assets/jawj/oms' . $suffix . '.js', array(), GEODIRECTORY_VERSION );
		wp_register_script( 'geodir-o-overlappingmarker-script', geodir_plugin_url() . '/assets/jawj/oms-leaflet' . $suffix . '.js', array(), GEODIRECTORY_VERSION );
		wp_register_script( 'geodir-leaflet-script', geodir_plugin_url() . '/assets/leaflet/leaflet' . $suffix . '.js', array(), GEODIRECTORY_VERSION );
		wp_register_script( 'geodir-leaflet-geo-script', geodir_plugin_url() . '/assets/leaflet/osm.geocode' . $suffix . '.js', array( 'geodir-leaflet-script' ), GEODIRECTORY_VERSION );
		wp_register_script( 'select2', geodir_plugin_url() . '/assets/js/select2/select2.full' . $suffix . '.js', array(), GEODIRECTORY_VERSION );
		wp_register_script( 'geodir-admin-script', geodir_plugin_url() . '/assets/js/admin' . $suffix . '.js', array(
			'jquery',
			'jquery-ui-tooltip',
			'thickbox'
		), GEODIRECTORY_VERSION );
		wp_register_script( 'geodir-lity', geodir_plugin_url() . '/assets/js/libraries/gd_lity' . $suffix . '.js', array(), GEODIRECTORY_VERSION );
		wp_register_style( 'font-awesome', 'https://use.fontawesome.com/releases/v5.13.0/css/all.css', array( 'font-awesome-shim' ), GEODIRECTORY_VERSION );
		wp_register_style( 'font-awesome-shim', 'https://use.fontawesome.com/releases/v5.13.0/css/v4-shims.css', array(), GEODIRECTORY_VERSION );
		wp_add_inline_script( 'geodir-admin-script', "window.gdSetMap = window.gdSetMap || '" . GeoDir_Maps::active_map() . "';", 'before' );
		wp_add_inline_script( 'geodir-admin-script', "var ajaxurl = '" . admin_url( 'admin-ajax.php' ) . "';", 'before' );


		wp_register_script( 'geodir-google-maps', 'https://maps.google.com/maps/api/js?' . $map_lang . $map_key . $map_extra, array(), GEODIRECTORY_VERSION );
		wp_register_script( 'geodir-leaflet-script', geodir_plugin_url() . '/assets/leaflet/leaflet' . $suffix . '.js', array(), GEODIRECTORY_VERSION );


		$required_scripts = array(
			'jquery',
			'jquery-ui-tooltip',
			'select2',
			'geodir-admin-script',
			'jquery-ui-progressbar',
			'geodir-lity',
		);

		// add maps if needed
		if ( in_array( $geodir_map_name, array( 'auto', 'google' ) ) ) {
			$required_scripts[] = 'geodir-google-maps';
			$required_scripts[] = 'geodir-g-overlappingmarker-script';
		} elseif ( $geodir_map_name == 'osm' ) {
			$required_scripts[] = 'geodir-leaflet-script';
			$required_scripts[] = 'geodir-leaflet-geo-script';
			$required_scripts[] = 'geodir-o-overlappingmarker-script';
		}

		$osm_extra = GeoDir_Maps::footer_script();
		wp_add_inline_script( 'geodir-goMap', "window.gdSetMap = window.gdSetMap || '" . GeoDir_Maps::active_map() . "';" . $osm_extra, 'before' );
		$required_scripts[] = 'geodir-goMap';


		wp_register_script( 'geodir-setup', GEODIRECTORY_PLUGIN_URL . '/assets/js/setup-wizard' . $suffix . '.js', $required_scripts, GEODIRECTORY_VERSION );


		wp_localize_script( 'geodir-setup', 'geodir_params', geodir_params() );

		wp_enqueue_style( 'geodir-admin-css', geodir_plugin_url() . '/assets/css/admin.css', array(), GEODIRECTORY_VERSION );
		wp_enqueue_style( 'geodir-jquery-ui-css', geodir_plugin_url() . '/assets/css/jquery-ui.css', array(), GEODIRECTORY_VERSION );
		wp_enqueue_style( 'jquery-ui-core' );
		wp_enqueue_style( 'font-awesome' );
		wp_enqueue_style( 'geodir-setup-wizard', GEODIRECTORY_PLUGIN_URL . '/assets/css/setup-wizard.css', array(
			'dashicons',
			'install',
			'thickbox'
		), GEODIRECTORY_VERSION );
		wp_enqueue_style( 'select2', GEODIRECTORY_PLUGIN_URL . '/assets/css/select2/select2.css', array(), GEODIRECTORY_VERSION );
		wp_register_style( 'geodir-leaflet-style', geodir_plugin_url() . '/assets/leaflet/leaflet.css', array(), GEODIRECTORY_VERSION );

		// load OSM styles if needed.
		if ( $geodir_map_name == 'osm' ) {
			wp_enqueue_style( 'geodir-leaflet-style', geodir_plugin_url() . '/assets/leaflet/leaflet.css', array(), GEODIRECTORY_VERSION );
		}


		if ( ! empty( $_POST['save_step'] ) && isset( $this->steps[ $this->step ]['handler'] ) ) {
			call_user_func( $this->steps[ $this->step ]['handler'], $this );
		}


		ob_start();
		$this->setup_wizard_header();
		$this->setup_wizard_steps();
		$this->setup_wizard_content();
		$this->setup_wizard_footer();
		exit;
	}

	/**
	 * Setup Wizard Header.
	 *
	 * @since 2.0.0
	 */
public function setup_wizard_header() {
	?>
	<!DOCTYPE html>
	<html <?php language_attributes(); ?>>
	<head>
		<meta name="viewport" content="width=device-width"/>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<title><?php esc_html_e( 'GeoDirectory &rsaquo; Setup Wizard', 'geodirectory' ); ?></title>
		<?php wp_print_scripts( 'geodir-setup' ); ?>
		<?php do_action( 'admin_print_styles' ); ?>
		<?php do_action( 'admin_head' ); ?>
	</head>
	<body class="gd-setup wp-core-ui">
	<h1 id="gd-logo"><a href="https://wpgeodirectory.com/"><img
				src="<?php echo GEODIRECTORY_PLUGIN_URL; ?>/assets/images/gd-logo-grey.png" alt="GeoDirectory"/></a>
	</h1>
	<?php
	}

	/**
	 * Output the steps.
	 *
	 * @since 2.0.0
	 */
	public function setup_wizard_steps() {
		$ouput_steps = $this->steps;
		array_shift( $ouput_steps );
		?>
		<ol class="gd-setup-steps">
			<?php foreach ( $ouput_steps as $step_key => $step ) : ?>
				<li class="<?php
				if ( $step_key === $this->step ) {
					echo 'active';
				} elseif ( array_search( $this->step, array_keys( $this->steps ) ) > array_search( $step_key, array_keys( $this->steps ) ) ) {
					echo 'done';
				}
				?>"><?php echo esc_html( $step['name'] ); ?></li>
			<?php endforeach; ?>
		</ol>
		<?php
	}

	/**
	 * Output the content for the current step.
	 *
	 * @since 2.0.0
	 */
	public function setup_wizard_content() {
		echo '<div class="gd-setup-content">';
		call_user_func( $this->steps[ $this->step ]['view'], $this );
		echo '</div>';
	}

	/**
	 * Setup Wizard Footer.
	 *
	 * @since 2.0.0
	 */
	public function setup_wizard_footer() {
	?>
	<?php if ( 'next_steps' === $this->step ) : ?>
		<p class="gd-return-to-dashboard-wrap"><a class="gd-return-to-dashboard"
		                                          href="<?php echo esc_url( admin_url() ); ?>"><?php esc_html_e( 'Return to the WordPress Dashboard', 'geodirectory' ); ?></a>
		</p>
	<?php endif; ?>
	</body>
	</html>
	<?php
}

	/**
	 * Introduction step.
	 *
	 * @since 2.0.0
	 */
	public function setup_introduction() {
		?>
		<h1><?php esc_html_e( 'Welcome to the world of GeoDirectory!', 'geodirectory' ); ?></h1>
		<p><?php _e( "Thank you for choosing GeoDirectory to power your online directory! This quick setup wizard will help you configure the basic settings. <strong>It's completely optional and should not take longer than five minutes.</strong>", 'geodirectory' ); ?></p>
		<p><?php esc_html_e( "No time right now? If you don't want to go through the wizard, you can skip and return to the WordPress dashboard. Come back anytime if you change your mind!", 'geodirectory' ); ?></p>
		<p class="gd-setup-actions step">
			<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
			   class="button-primary button button-large button-next"><?php esc_html_e( 'Let\'s go!', 'geodirectory' ); ?></a>
			<a href="<?php echo esc_url( admin_url() ); ?>"
			   class="button button-large"><?php esc_html_e( 'Not right now', 'geodirectory' ); ?></a>
		</p>
		<?php
	}

	/**
	 * Get the URL for the next step's screen.
	 *
	 * @param string step   slug (default: current step)
	 *
	 * @return string       URL for next step if a next step exists.
	 *                      Admin URL if it's the last step.
	 *                      Empty string on failure.
	 * @since 3.0.0
	 */
	public function get_next_step_link( $step = '' ) {
		if ( ! $step ) {
			$step = $this->step;
		}

		$keys = array_keys( $this->steps );
		if ( end( $keys ) === $step ) {
			return admin_url();
		}

		$step_index = array_search( $step, $keys );
		if ( false === $step_index ) {
			return '';
		}

		return add_query_arg( 'step', $keys[ $step_index + 1 ] );
	}

	/**
	 * Setup maps api.
	 *
	 * @since 2.0.0
	 */
	public function setup_maps() {
		?>
		<form method="post">
			<p><?php esc_html_e( 'To get maps to work properly in your directory please fill out the below details.', 'geodirectory' ); ?></p>


			<table class="gd-setup-maps" cellspacing="0">

				<tbody>

				<?php
				$settings   = array();
				$settings[] = GeoDir_Settings_General::get_maps_api_setting();
				$settings[] = GeoDir_Settings_General::get_map_language_setting();
				$api_arr    = GeoDir_Settings_General::get_google_maps_api_key_setting();
				// change the tooltip description/
				$api_arr['desc'] = __( 'This is a requirement to use Google Maps. If you would prefer to use the Open Street Maps API then leave this blank.', 'geodirectory' );

				$settings[] = $api_arr;

				GeoDir_Admin_Settings::output_fields( $settings );
				?>


				</tbody>
			</table>

			<p><?php esc_html_e( '( The Google maps API key is essential unless you are using OSM or no maps )', 'geodirectory' ); ?></p>

			<p class="gd-setup-actions step">
				<input type="submit" class="button-primary button button-large button-next"
				       value="<?php esc_attr_e( 'Continue', 'geodirectory' ); ?>" name="save_step"/>
				<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
				   class="button button-large button-next"><?php esc_html_e( 'Skip this step', 'geodirectory' ); ?></a>
				<?php wp_nonce_field( 'gd-setup' ); ?>
			</p>
		</form>
		<?php
	}

	/**
	 * Save Maps Settings.
	 *
	 * @since 2.0.0
	 */
	public function setup_maps_save() {
		check_admin_referer( 'gd-setup' );

		$settings   = array();
		$settings[] = GeoDir_Settings_General::get_maps_api_setting();
		$settings[] = GeoDir_Settings_General::get_map_language_setting();
		$settings[] = GeoDir_Settings_General::get_google_maps_api_key_setting();

		GeoDir_Admin_Settings::save_fields( $settings );
		wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;
	}

	/**
	 * Default Location settings.
	 *
	 * @since 2.0.0
	 */
	public function setup_default_location() {

		$this->google_maps_api_check();
		?>

		<form id='geodir-set-default-location' method="post">
			<?php
			$generalSettings = new GeoDir_Settings_General();
			$settings        = $generalSettings->get_settings( 'location' );

			// Change the description
			$settings[0]['title'] = '';
			$settings[0]['desc']  = __( 'Drag the map or the marker to set the city/town you wish to use as the default location.', 'geodirectory' );
			GeoDir_Admin_Settings::output_fields( $settings );

			// check if there are already listing before saving new location
			global $wpdb;
			$post_types  = geodir_get_posttypes();
			$cpt_count   = count( $post_types );
			$cptp        = array_fill( 0, $cpt_count, "%s" );
			$cptp_string = implode( ",", $cptp );
			$has_posts   = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type IN ($cptp_string) LIMIT 1", $post_types ) );
			if ( $has_posts ) {
				?>
				<script>
					jQuery(function () {
						var default_location_city = jQuery("#default_location_city").val();
						jQuery(".button-next").click(function () {
							if (default_location_city && default_location_city != jQuery("#default_location_city").val()) {
								return confirm("<?php _e( "Are you sure? This can break current listings.", "geodirectory" );?>");
							}
						});
					});
				</script>
				<?php
			}


			?>
			<p class="gd-setup-actions step">
				<?php $generalSettings->output_toggle_advanced(); ?>
				<input type="submit" class="button-primary button button-large button-next"
				       value="<?php esc_attr_e( 'Continue', 'geodirectory' ); ?>" name="save_step"/>
				<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
				   class="button button-large button-next"><?php esc_html_e( 'Skip this step', 'geodirectory' ); ?></a>
				<?php wp_nonce_field( 'gd-setup' ); ?>
			</p>
		</form>

		<?php
	}

	/**
	 * Shows an error message if there is a problem with the google maps api key settings.
	 *
	 * @since 2.0.0
	 */
	public function google_maps_api_check() {
		//maps_api

		$display  = "display: none;";
		$maps_api = geodir_get_option( 'maps_api' );
		if ( $maps_api == 'auto' || $maps_api == 'google' ) {
			$maps_api_key = geodir_get_option( 'google_maps_api_key' );
			if ( $maps_api == 'google' && empty( $maps_api_key ) ) {
				$message = esc_html__( 'You have not set a Google Maps API key, please press the back button in your browser and add a key otherwise Open Street Maps (OSM) will be used instead.', 'geodirectory' );
				$display = '';
			} elseif ( empty( $maps_api_key ) ) {
				$message = esc_html__( 'You have not set a Google Maps API key, please press the back button in your browser and add a key.', 'geodirectory' );
			} else {
				$message = esc_html__( 'There is a problem with the Google Maps API key you have set, please press the back button in your browser and add a valid key.', 'geodirectory' );
			}

			?>
			<p class="gd-google-api-error" style="<?php echo $display; ?>">
				<?php echo '<i class="fas fa-exclamation-triangle" aria-hidden="true"></i> ' . $message; ?>
			</p>
			<script>
				function gm_authFailure() {
					jQuery('.gd-google-api-error').show();
				}
			</script>
			<?php
		}
	}

	/**
	 * Save Default Location Settings.
	 *
	 * @since 2.0.0
	 */
	public function setup_default_location_save() {
		check_admin_referer( 'gd-setup' );

		$generalSettings = new GeoDir_Settings_General();
		$settings        = $generalSettings->get_settings( 'location' );
		GeoDir_Admin_Settings::save_fields( $settings );

		do_action( 'geodir_setup_wizard_default_location_saved', $settings );

		wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;
	}

	/**
	 * Dummy Data setup.
	 *
	 * @since 2.0.0
	 */
	public function setup_content() {

		$wizard_content = array(
			'dummy_data' => __( "Dummy Data", "geodirectory" ),
			'sidebars'   => __( "Sidebars", "geodirectory" ),
			'menus'   => __( "Menus", "geodirectory" ),
		);

		$wizard_content = apply_filters( 'geodir_wizard_content', $wizard_content );
		?>
		<div class="geodir-wizard-content-parts">
			<ul>
				<?php
				foreach ( $wizard_content as $slug => $title ) {
					echo '<li><a href="#' . esc_attr( $slug ) . '">' . esc_attr( $title ) . '</a></li>' . " \n"; // line break adds a nice spacing
				}
				?>
			</ul>
		</div>

		<form method="post">
			<?php
			foreach ( $wizard_content as $slug => $title ) {
				echo '<h2 class="gd-settings-title "><a id="' . esc_attr( $slug ) . '"></a>' . esc_attr( $title ) . '</h2>' . " \n"; // line break adds a nice spacing
				echo do_action( "geodir_wizard_content_{$slug}" );
			}
			?>

			<p class="gd-setup-actions step">
				<input type="submit" class="button-primary button button-large button-next"
				       value="<?php esc_attr_e( 'Continue', 'geodirectory' ); ?>" name="save_step"/>
				<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
				   class="button button-large button-next"><?php esc_html_e( 'Skip this step', 'geodirectory' ); ?></a>
				<?php wp_nonce_field( 'gd-setup' ); ?>
			</p>
		</form>


		<?php
	}

	/**
	 * Dummy data save.
	 *
	 * This is done via ajax so we just pass onto the next step.
	 *
	 * @since 2.0.0
	 */
	public function setup_content_save() {
		check_admin_referer( 'gd-setup' );
		wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;
	}

	/**
	 * Dummy Data setup.
	 *
	 * @since 2.0.0
	 */
	public function setup_recommend() {
		?>
		<form method="post">
			<div class="gd-wizard-recommend">

				<h2 class="gd-settings-title "><?php _e( "Recommend Plugins", "geodirectory" ); ?></h2>

				<p><?php _e( "Below are a few recommend plugins that will help you with your directory.", "geodirectory" ); ?></p>

				<?php

				include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' ); //for plugins_api..

				$recommend_wp_plugins = GeoDir_Admin_Addons::get_recommend_wp_plugins();

				//			$status = install_plugin_install_status( array("slug"=>"two-factor","version"=>""));
				//			print_r($status);

				if ( ! empty( $recommend_wp_plugins ) ) {
					echo "<ul>";
					$installed_text = "<i class=\"fas fa-check-circle\" aria-hidden=\"true\"></i> " . __( 'Installed', 'geodirectory' );
					echo "<input type='hidden' id='gd-installing-text' value='<i class=\"fas fa-sync fa-spin\" aria-hidden=\"true\"></i> " . __( 'Installing', 'geodirectory' ) . "' >";
					echo "<input type='hidden' id='gd-installed-text' value='$installed_text' >";
					foreach ( $recommend_wp_plugins as $plugin ) {
//					print_r($plugin);
						$status = install_plugin_install_status( array( "slug" => $plugin['slug'], "version" => "" ) );
						//print_r($status);

						$plugin_status = isset( $status['status'] ) ? $status['status'] : '';
						$url           = isset( $status['url'] ) ? $status['url'] : '';

						$nonce = wp_create_nonce( 'updates' );

						if ( $plugin_status == 'install' ) {// required installation
							$checked        = "checked";
							$disabled       = "";
							$checkbox_class = "class='gd_install_plugins'";
						} else {
							$checked        = "checked";
							$disabled       = "disabled";
							$checkbox_class = "";
						}


						echo "<li class='" . $plugin['slug'] . "'>";
						echo "<input type='checkbox' id='" . $plugin['slug'] . "' $checked $disabled $checkbox_class />";
						echo $plugin['name'] . " " . geodir_help_tip( $plugin['desc'] );
						echo " | <a href='" . admin_url( "plugin-install.php?gd_wizard_recommend=true&tab=plugin-information&plugin=" . $plugin['slug'] ) . "' data-lity>more info</a>";
						if ( $plugin_status == 'install' && $url ) {
							//echo " | <a href='#' onclick='gd_wizard_install_plugin(\"".$plugin['slug']."\",\"$nonce\");return false;'>install</a>";
							echo " | <span class='gd-plugin-status' >( " . __( 'Tick to install', 'geodirectory' ) . " )</span>";
						} else {
							if ( ! empty( $plugin_status ) ) {
								$plugin_status = $installed_text;
							}
							echo " | <span class='gd-plugin-status'>$plugin_status</span>";
						}
						echo "</li>";

					}
					echo "</ul>";
				}


				// GD addons
				/*
				?>

					<h2 class="gd-settings-title "><?php _e("GeoDirectory Addons","geodirectory");?></h2>

					<p><?php _e("Below are the GeoDirectory addons that you may with to install at this point.","geodirectory");?></p>

					<?php

					$gd_plugins = GeoDir_Admin_Addons::get_section_data( 'addons' );

					//print_r($addons);

					//			$status = install_plugin_install_status( array("slug"=>"two-factor","version"=>""));
					//			print_r($status);

					if(!empty($gd_plugins)){
						echo "<ul>";
						$installed_text = "<i class=\"fas fa-check-circle\" aria-hidden=\"true\"></i> ".__('Installed','geodirectory');
						echo "<input type='hidden' id='gd-installing-text' value='<i class=\"fas fa-sync fa-spin\" aria-hidden=\"true\"></i> ".__('Installing','geodirectory')."' >";
						echo "<input type='hidden' id='gd-installed-text' value='$installed_text' >";
						foreach ($gd_plugins  as $plugin){

							// convert to array
							$plugin = (array)$plugin->info;
							$plugin['name'] = $plugin['title'];

	//						print_r($plugin);exit;

							$status = install_plugin_install_status( array("slug"=>$plugin['slug'],"version"=>""));
							//print_r($status);


							$all_plugins = get_plugins();
							//print_r($gd_plugins);
	//						echo '###';
	//						print_r($all_plugins);exit;


							$plugin_status = isset($status['status']) ? $status['status'] : '';
							$url = isset($status['url']) ? $status['url'] : '';

							$nonce = wp_create_nonce( 'updates' );

							if($plugin_status=='install'){// required installation
								$checked = "checked";
								$disabled = "";
								$checkbox_class = "class='gd_install_plugins'";
							}else{
								$checked = "checked";
								$disabled = "disabled";
								$checkbox_class = "";
							}

	//http://localhost/wp-admin/plugin-install.php?gd_wizard_recommend=true&tab=plugin-information&plugin=list-manager&item_id=69994&update_url=https://wpgeodirectory.com
							echo "<li class='".$plugin['slug']."'>";
							echo "<input type='checkbox' id='".$plugin['slug']."' $checked $disabled $checkbox_class />";
							echo $plugin['name']." "; echo !empty($plugin['desc']) ? geodir_help_tip($plugin['desc']) : '';
							echo " | <a href='".admin_url( "plugin-install.php?gd_wizard_recommend=true&tab=plugin-information&plugin=".$plugin['slug'])."&item_id=".$plugin['id']."&update_url=https://wpgeodirectory.com' data-lity>more info</a>";
							if($plugin_status=='install' && $url){
								//echo " | <a href='#' onclick='gd_wizard_install_plugin(\"".$plugin['slug']."\",\"$nonce\");return false;'>install</a>";
								echo " | <span class='gd-plugin-status' >( ".__('Tick to install','geodirectory')." )</span>";
							}else{
								if(!empty($plugin_status)){
									$plugin_status = $installed_text;
								}
								echo " | <span class='gd-plugin-status'>$plugin_status</span>";
							}
							echo "</li>";

						}
						echo "</ul>";
					}

				*/
				?>


			</div>

			<p class="gd-setup-actions step">
				<input type="submit" class="button-primary button button-large button-next gd-install-recommend"
				       value="<?php esc_attr_e( 'Install', 'geodirectory' ); ?>" name="install_recommend"
				       onclick="gd_wizard_install_plugins('<?php echo $nonce; ?>');return false;"/>
				<input type="submit" class="button-primary button button-large button-next gd-continue-recommend"
				       value="<?php esc_attr_e( 'Continue', 'geodirectory' ); ?>" name="save_step"/>
				<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
				   class="button button-large button-next"><?php esc_html_e( 'Skip this step', 'geodirectory' ); ?></a>
				<?php wp_nonce_field( 'gd-setup' ); ?>
			</p>
		</form>
		<?php
	}

	/**
	 * Dummy data save.
	 *
	 * This is done via ajax so we just pass onto the next step.
	 *
	 * @since 2.0.0
	 */
	public function setup_recommend_save() {
		check_admin_referer( 'gd-setup' );
		wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;
	}

	/**
	 * Final step.
	 *
	 * @since 2.0.0
	 */
	public function setup_ready() {
		$this->setup_ready_actions();
		?>

		<h1><?php esc_html_e( 'Awesome, your directory is ready!', 'geodirectory' ); ?></h1>

		<?php if ( 'unknown' === geodir_get_option( 'usage_tracking', 'unknown' ) || '' === geodir_get_option( 'usage_tracking' ) ) { ?>
			<div class="geodirectory-message geodirectory-tracker">
				<p><?php printf( __( 'Want to help make GeoDirectory even more awesome? Allow GeoDirectory to collect non-sensitive diagnostic data and usage information. %1$sFind out more%2$s.', 'geodirectory' ), '<a href="https://wpgeodirectory.com/usage-tracking/" target="_blank">', '</a>' ); ?></p>
				<p class="submit">
					<a class="button-primary button button-large"
					   href="<?php echo esc_url( wp_nonce_url( remove_query_arg( 'gd_tracker_optout', add_query_arg( 'gd_tracker_optin', 'true' ) ), 'gd_tracker_optin', 'gd_tracker_nonce' ) ); ?>"><?php esc_html_e( 'Allow', 'geodirectory' ); ?></a>
					<a class="button-secondary button button-large skip"
					   href="<?php echo esc_url( wp_nonce_url( remove_query_arg( 'gd_tracker_optin', add_query_arg( 'gd_tracker_optout', 'true' ) ), 'gd_tracker_optout', 'gd_tracker_nonce' ) ); ?>"><?php esc_html_e( 'No thanks', 'geodirectory' ); ?></a>
				</p>
			</div>
		<?php } else { ?>
			<div class="geodirectory-message geodirectory-tracker">
				<p><?php _e( 'Thank you for using GeoDirectory! :)', 'geodirectory' ); ?></p>
			</div>
		<?php } ?>

		<div class="gd-setup-next-steps">
			<div class="gd-setup-next-steps-first">
				<h2><?php esc_html_e( 'Next steps', 'geodirectory' ); ?></h2>
				<ul>
					<li class="setup-listing"><a class="button button-primary button-large"
					                             href="<?php echo esc_url( admin_url( 'post-new.php?post_type=gd_place' ) ); ?>"><?php esc_html_e( 'Create your first listing!', 'geodirectory' ); ?></a>
					</li>
				</ul>
			</div>
			<div class="gd-setup-next-steps-last">
				<h2><?php _e( 'Learn more', 'geodirectory' ); ?></h2>
				<ul>
					<li class="gd-getting-started"><a
							href="https://wpgeodirectory.com/docs-v2/geodirectory/getting-started/?utm_source=setupwizard&utm_medium=product&utm_content=getting-started&utm_campaign=geodirectoryplugin"
							target="_blank"><?php esc_html_e( 'Getting started guide', 'geodirectory' ); ?></a></li>
					<li class="gd-newsletter"><a
							href="https://wpgeodirectory.com/newsletter-signup/?utm_source=setupwizard&utm_medium=product&utm_content=newsletter&utm_campaign=geodirectoryplugin"
							target="_blank"><?php esc_html_e( 'Get GeoDirectory advice in your inbox', 'geodirectory' ); ?></a>
					</li>
					<li class="gd-get-help"><a
							href="https://wpgeodirectory.com/support/?utm_source=setupwizard&utm_medium=product&utm_content=docs&utm_campaign=geodirectoryplugin"
							target="_blank"><?php esc_html_e( 'Have questions? Get help.', 'geodirectory' ); ?></a></li>
				</ul>
			</div>
		</div>
		<?php
	}

	/**
	 * Actions on the final step.
	 *
	 * @since 2.0.0
	 */
	private function setup_ready_actions() {
		GeoDir_Admin_Notices::remove_notice( 'install' );

		if ( isset( $_GET['gd_tracker_optin'] ) && isset( $_GET['gd_tracker_nonce'] ) && wp_verify_nonce( $_GET['gd_tracker_nonce'], 'gd_tracker_optin' ) ) {
			geodir_update_option( 'usage_tracking', true );
			GeoDir_Admin_Tracker::send_tracking_data( true );

		} elseif ( isset( $_GET['gd_tracker_optout'] ) && isset( $_GET['gd_tracker_nonce'] ) && wp_verify_nonce( $_GET['gd_tracker_nonce'], 'gd_tracker_optout' ) ) {
			geodir_update_option( 'usage_tracking', false );
		}
	}

	/**
	 * Output the setup wizard content dummy data settings.
	 */
	public static function content_dummy_data() {

		$generalSettings = new GeoDir_Settings_General();
		$settings        = $generalSettings->get_settings( 'dummy_data' );

		// Change the description
		$settings[0]['title'] = '';//__("Demo content","geodirectory");
		$settings[0]['desc']  = '';//__( 'Drag the map or the marker to set the city/town you wish to use as the default location.', 'geodirectory' );
		GeoDir_Admin_Settings::output_fields( $settings );
	}

	/**
	 * Output the setup wizard content sidebars settings.
	 */
	public static function content_sidebars() {

		$gd_sidebar_top = get_theme_support( 'geodirectory-sidebar-top' );
		if ( isset( $gd_sidebar_top[0] ) ) {
			?>
			<table class="form-table gd-dummy-table gd-dummy-widgets">
				<tbody>
				<tr>
					<td><strong><?php _e( "Select the theme top sidebar", "geodirectory" ); ?></strong></td>
					<td><strong><?php _e( "Action", "geodirectory" ); ?></strong></td>
				</tr>

				<tr>
					<td>
						<select id='geodir-wizard-widgets-top' class="geodir-select">
							<?php
							$is_sidebar    = '';
							$maybe_sidebar = '';
							$gd_sidebar    = get_theme_support( 'geodirectory-sidebar-top' );
							if ( isset( $gd_sidebar[0] ) ) {
								$gd_sidebar = $gd_sidebar[0];
							}

							// get the sidebars
							foreach ( $GLOBALS['wp_registered_sidebars'] as $sidebar ) {

								if ( $gd_sidebar && $gd_sidebar == strtolower( $sidebar['id'] ) ) {
									$is_sidebar = $sidebar['id'];
									break;
								}
								// Check if its called 'sidebar' by name or id.
								if ( strtolower( $sidebar['id'] ) == 'sidebar' || strtolower( $sidebar['name'] ) == __( 'sidebar', 'geodirectory' ) ) {
									$is_sidebar = $sidebar['id'];
									break;
								}

								if ( ! $maybe_sidebar && strpos( strtolower( $sidebar['name'] ), __( 'sidebar', 'geodirectory' ) ) !== false ) {
									$maybe_sidebar = $sidebar['id'];
								}

								if ( strpos( strtolower( $sidebar['name'] ), __( 'sidebar page', 'geodirectory' ) ) !== false ) {
									$maybe_sidebar = $sidebar['id'];
								}
							}

							// set if we have a guess
							if ( ! $is_sidebar && $maybe_sidebar ) {
								$is_sidebar = $maybe_sidebar;
							}

							foreach ( $GLOBALS['wp_registered_sidebars'] as $sidebar ) { ?>
								<option
									value="<?php echo esc_attr( $sidebar['id'] ); ?>" <?php selected( $is_sidebar, $sidebar['id'] ); ?>>
									<?php echo esc_attr( ucwords( $sidebar['name'] ) );
									if ( $is_sidebar == $sidebar['id'] ) {
										echo ' ';
										_e( '( Auto detected )', 'geodirectory' );
									} ?>
								</option>
							<?php }

							?>
						</select>
						<?php echo geodir_notification( array( 'geodir-wizard-widgets-top-result' => '' ) ); ?>
					</td>
					<td><input type="button" value="<?php _e( "Insert widgets", "geodirectory" ); ?>"
					           class="button-primary geodir_dummy_button"
					           onclick="gd_wizard_add_widgets_top('<?php echo wp_create_nonce( "geodir-wizard-widgets-top" ); ?>');return false;">
					</td>
				</tr>
				</tbody>
			</table>
			<?php
		}
		?>
		<table class="form-table gd-dummy-table gd-dummy-widgets">
			<tbody>
			<tr>
				<td><strong><?php _e( "Select the theme sidebar", "geodirectory" ); ?></strong></td>
				<td><strong><?php _e( "Action", "geodirectory" ); ?></strong></td>
			</tr>

			<tr>
				<td>
					<select id='geodir-wizard-widgets' class="geodir-select">
						<?php
						$is_sidebar    = '';
						$maybe_sidebar = '';
						$gd_sidebar    = get_theme_support( 'geodirectory-sidebar' );
						if ( isset( $gd_sidebar[0] ) ) {
							$gd_sidebar = $gd_sidebar[0];
						}
						//							print_r($gd_sidebar);
						//							echo '###';exit;
						// get the sidebars
						foreach ( $GLOBALS['wp_registered_sidebars'] as $sidebar ) {

							if ( $gd_sidebar && $gd_sidebar == strtolower( $sidebar['id'] ) ) {
								$is_sidebar = $sidebar['id'];
								break;
							}
							// Check if its called 'sidebar' by name or id.
							if ( strtolower( $sidebar['id'] ) == 'sidebar' || strtolower( $sidebar['name'] ) == __( 'sidebar', 'geodirectory' ) ) {
								$is_sidebar = $sidebar['id'];
								break;
							}

							if ( ! $maybe_sidebar && strpos( strtolower( $sidebar['name'] ), __( 'sidebar', 'geodirectory' ) ) !== false ) {
								$maybe_sidebar = $sidebar['id'];
							}

							if ( strpos( strtolower( $sidebar['name'] ), __( 'sidebar page', 'geodirectory' ) ) !== false ) {
								$maybe_sidebar = $sidebar['id'];
							}
						}

						// set if we have a guess
						if ( ! $is_sidebar && $maybe_sidebar ) {
							$is_sidebar = $maybe_sidebar;
						}

						foreach ( $GLOBALS['wp_registered_sidebars'] as $sidebar ) { ?>
							<option
								value="<?php echo esc_attr( $sidebar['id'] ); ?>" <?php selected( $is_sidebar, $sidebar['id'] ); ?>>
								<?php echo esc_attr( ucwords( $sidebar['name'] ) );
								if ( $is_sidebar == $sidebar['id'] ) {
									echo ' ';
									_e( '( Auto detected )', 'geodirectory' );
								} ?>
							</option>
						<?php }

						?>
					</select>
					<?php echo geodir_notification( array( 'geodir-wizard-widgets-result' => '' ) ); ?>
				</td>
				<td><input type="button" value="<?php _e( "Insert widgets", "geodirectory" ); ?>"
				           class="button-primary geodir_dummy_button"
				           onclick="gd_wizard_add_widgets('<?php echo wp_create_nonce( "geodir-wizard-widgets" ); ?>');return false;">
				</td>
			</tr>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Output the setup wizard content menu settings.
	 */
	public static function content_menus() {

		?>
		<table class="form-table gd-dummy-table gd-dummy-widgets gd-dummy-posts">
			<tbody>
			<tr>
				<td><strong><?php _e( "Select the theme main menu", "geodirectory" ); ?></strong></td>
				<td><strong><?php _e( "Action", "geodirectory" ); ?></strong></td>
			</tr>

			<tr>
				<td>
					<?php

					$set_menus = get_nav_menu_locations();
					$set_menus = array_filter( $set_menus );
					//echo '##';
					//print_r($set_menus);

					if ( ! empty( $set_menus ) ) {
						//echo '##1';
						echo "<select id='geodir-wizard-menu-id' data-type='add' class='geodir-select' >";

						foreach ( $set_menus as $menu_location => $menu_id ) {
							$selected = '';

							if ( strpos( strtolower( $menu_location ), 'primary' ) !== false || strpos( strtolower( $menu_location ), 'main' ) !== false ) {
								$selected = 'selected="selected"';
							}

							$menu_item = wp_get_nav_menus( $menu_id )[0];

							?>
							<option value="<?php echo esc_attr( $menu_id ); ?>" <?php echo $selected; ?>>
								<?php echo esc_attr( $menu_item->name );
								if ( $selected ) {
									echo ' ';
									_e( '( Auto detected )', 'geodirectory' );
								} ?>
							</option>
							<?php
						}

						echo "</select>";


					} else {//echo '##2';
						// add new menu to a menu location.
						$menus = get_registered_nav_menus();

						//print_r($menus );

						if ( ! empty( $menus ) ) {
							echo "<select id='geodir-wizard-menu-location' data-type='create' class='geodir-select' >";

							foreach ( $menus as $menu_slug => $menu_name ) {
								$selected = '';

								if ( strpos( strtolower( $menu_slug ), 'primary' ) !== false || strpos( strtolower( $menu_slug ), 'main' ) !== false ) {
									$selected = 'selected="selected"';
								}
								?>
								<option value="<?php echo esc_attr( $menu_slug ); ?>" <?php echo $selected; ?>>
									<?php _e( 'Create new menu in:', 'geodirectory' );
									echo ' ' . esc_attr( $menu_name );
									if ( $selected ) {
										echo ' ';
										_e( '( Auto detected )', 'geodirectory' );
									} ?>
								</option>
								<?php
							}
							echo "</select>";

						}

						//print_r($menus);
					}

					echo geodir_notification( array( 'geodir-wizard-menu-result' => '' ) );

					?>
				</td>
				<td><input type="button" value="<?php _e( "Insert menu items", "geodirectory" ); ?>"
				           class="button-primary geodir_dummy_button"
				           onclick="gd_wizard_setup_menu('<?php echo wp_create_nonce( "geodir-wizard-setup-menu" ); ?>');return false;">
				</td>
			</tr>
			</tbody>
		</table>
		<?php

	}

}

new GeoDir_Admin_Setup_Wizard();
