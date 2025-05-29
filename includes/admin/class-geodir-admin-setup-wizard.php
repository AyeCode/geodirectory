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

		// skip intro
		if ( empty( $_GET['step'] ) ) {
			$_GET['step'] = 'maps';
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
			'features'             => array(
				'name'    => __( "Extra Features", 'geodirectory' ),
				'view'    => array( $this, 'setup_features' ),
				'handler' => array( $this, 'setup_features_save' ),
			),
//			'type'             => array(
//				'name'    => __( "Industry", 'geodirectory' ),
//				'view'    => array( $this, 'setup_type' ),
//				'handler' => array( $this, 'setup_type_save' ),
//			),
//			'type'        => array(
//				'name'    => __( 'Directory', 'geodirectory' ),
//				'view'    => array( $this, 'setup_recommend' ),
//				'handler' => array( $this, 'setup_recommend_save' ),
//			),
//			'features'        => array(
//				'name'    => __( 'Features', 'geodirectory' ),
//				'view'    => array( $this, 'setup_recommend' ),
//				'handler' => array( $this, 'setup_recommend_save' ),
//			),
//			'recommend'        => array(
//				'name'    => __( 'Recommend', 'geodirectory' ),
//				'view'    => array( $this, 'setup_recommend' ),
//				'handler' => array( $this, 'setup_recommend_save' ),
//			),
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


		// remove default location step if LM installed
		if ( defined( 'GEODIRLOCATION_VERSION' ) ) {
			unset( $default_steps['default_location'] );
		}

		$this->steps     = apply_filters( 'geodirectory_setup_wizard_steps', $default_steps );
		$this->step      = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : current( array_keys( $this->steps ) );
		$suffix          = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$geodir_map_name = GeoDir_Maps::active_map();

		// AUI
		$design_style = geodir_design_style();
		// enqueue the script
		$aui_settings = AyeCode_UI_Settings::instance();
		$aui_settings->enqueue_scripts();
		$aui_settings->enqueue_style();


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
			'bootstrap-js-bundle',
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

		// demo import
		if ( defined( 'AYECODE_CONNECT_VERSION' ) ) {
			global $ayecode_connect;


			if ( $ayecode_connect->is_registered() ) {
				$connected = true;

				if ( defined( 'AYECODE_CONNECT_PLUGIN_DIR' ) ) {
					require_once AYECODE_CONNECT_PLUGIN_DIR . '/includes/class-ayecode-demo-content.php';

					$demo_content = AyeCode_Demo_Content::instance();
					$demo_content->scripts();

					$required_scripts[] ='ayecode-connect';

				}
			}
		}


		wp_register_script( 'geodir-setup', GEODIRECTORY_PLUGIN_URL . '/assets/js/setup-wizard' . $suffix . '.js', $required_scripts, GEODIRECTORY_VERSION );


		wp_localize_script( 'geodir-setup', 'geodir_params', geodir_params() );
		if ( in_array( 'geodir-google-maps', $required_scripts ) ) {
			wp_add_inline_script( 'geodir-google-maps', GeoDir_Maps::google_map_callback(), 'before' );
		}

		wp_enqueue_style( 'geodir-admin-css', geodir_plugin_url() . '/assets/css/admin.css', array(), GEODIRECTORY_VERSION );
		wp_enqueue_style( 'geodir-jquery-ui-css', geodir_plugin_url() . '/assets/css/jquery-ui.css', array(), GEODIRECTORY_VERSION );
		wp_enqueue_style( 'jquery-ui-core' );
		wp_enqueue_style( 'font-awesome' );
		wp_enqueue_style( 'geodir-setup-wizard', GEODIRECTORY_PLUGIN_URL . '/assets/css/setup-wizard.css', array(
			'dashicons',
//			'install',
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
//		$this->setup_wizard_steps();
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
	global $aui_bs5;

	$bs_prefix = $aui_bs5 ? 'bs-' : '';
	?>
	<!DOCTYPE html>
	<html <?php language_attributes(); ?> class="bsui">
	<head>
		<meta name="viewport" content="width=device-width"/>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<title><?php esc_html_e( 'GeoDirectory &rsaquo; Setup Wizard', 'geodirectory' ); ?></title>
		<?php wp_print_scripts( 'geodir-setup' ); ?>
		<?php do_action( 'admin_print_styles' ); ?>
		<?php do_action( 'admin_head' ); ?>
		<style>
			body,p{
				font-size: 16px;
				font-weight: normal;
			}
			.gd-blur {
				filter: blur(2px);
			}
			.gd-blur:hover {
				filter: blur(0);
			}
		</style>
	</head>
	<body class="modal-open " style="background: #f1f1f1;">


	<div id="gd-setup-container" class="gd-setup wp-core-ui bg-whitex mx-auto mt-4x modal fade show overflow-auto"  style="display: block;z-index: inherit;">

		<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm <?php echo $aui_bs5 ? 'px-3 py-2' : '';?>">
			<a class="navbar-brand" href="#">
				<h1 class="h5 p-0 m-0">
					<i class="fas fa-globe-americas text-primary bg-white rounded-circle" style="color:#ff8333 !important;"></i>
							<span class="" style="color:#52565a;">
								<span class="" style="color:#ff8333 !important;">Geo</span>Directory
							</span>
				</h1>
			</a>
			<button class="navbar-toggler" type="button" data-<?php echo $bs_prefix;?>toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>

			<div class="collapse navbar-collapse" id="navbarSupportedContent">
				<?php $this->setup_wizard_steps();?>

			</div>
		</nav>



		<div class="modal-dialog flex-column modal-dialog-scrollablex modal-dialog-centered modal-lg" style=" padding-right: 15px;">

			<?php
			if(!empty($_GET['step']) && $_GET['step']=='maps'){
				$this->setup_introduction();


			}
			?>

			<div class="modal-content border-0 shadow">

				<div class="modal-body p-5">


	<?php
	}

	/**
	 * Output the steps.
	 *
	 * @since 2.0.0
	 */
	public function setup_wizard_steps() {
		global $aui_bs5;

		$ouput_steps = $this->steps;
		array_shift( $ouput_steps );
		/*?>
		<ol class="gd-setup-steps mb-0 pb-4 text-uppercase font-weight-bold fw-bold small">
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
*/

		$tick_class = 'text-black-50';
		?>
		<ol class="nav nav-pills nav-fill w-100 text-uppercase font-weight-bold fw-bold small text-muted">
			<?php foreach ( $ouput_steps as $step_key => $step ) : ?>
				<li class="nav-link border-0 d-flex align-content-center justify-content-center <?php
				if ( $step_key === $this->step ) {
					echo 'active ' . ( $aui_bs5 ? 'bg-primary text-white rounded fw-bold' : 'text-white' );
					$tick_class = '';
				} elseif ( array_search( $this->step, array_keys( $this->steps ) ) > array_search( $step_key, array_keys( $this->steps ) ) ) {
					$tick_class = 'text-primary';
					echo $aui_bs5 ? 'bg-white rounded shadow-0 fw-bold' : '' ;
				}else{
					echo 'text-muted ' . ( $aui_bs5 ? 'bg-white rounded shadow-0 fw-bold' : '' );
				}
				?>"><span class="h5 p-0 m-0"><i class="fas fa-check-circle <?php echo $tick_class;?>"></i></span> <span class="pl-1 ps-1 align-self-center"><?php echo esc_html( $step['name'] ); ?></span>

				</li>
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
		<p class="gd-return-to-dashboard-wrapper text-center mb-0 mt-5"><a class="gd-return-to-dashboard"
		                                          href="<?php echo esc_url( admin_url() ); ?>"><?php esc_html_e( 'Return to the WordPress Dashboard', 'geodirectory' ); ?></a>
		</p>
	<?php endif; ?>
	</div>
	</div>
	</div>
	</div>
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
		global $aui_bs5;

		$bs_prefix = $aui_bs5 ? 'bs-' : '';
		?>

		<div class="modal-content border-0 shadow mb-4 collapse show gd-setup-welcome" >
			<div class="modal-body p-5">
				<div class="gd-setup-content">
					<h1 class="h3"><?php esc_html_e( 'Welcome to the world of GeoDirectory!', 'geodirectory' ); ?></h1>
					<p class="mt-3"><?php _e( "This quick setup wizard will help you <b>configure your directory</b>. It's <b>completely optional</b> and shouldn't take longer than <b>five minutes<b/>.", 'geodirectory' ); ?></p>
					<p class="gd-setup-actions step text-right text-end">
						<a href="<?php echo esc_url( admin_url() ); ?>"
						   class="btn btn-link text-muted "><?php esc_html_e( 'Not right now', 'geodirectory' ); ?></a>
						<a href="#" onclick="jQuery('.gd-setup-welcome').collapse('hide');return false;"
						   class="btn btn-primary button-next">
							<svg viewbox="0 0 250 250" style="    height: auto;
    width: 20px;
    margin-left: -6px;
    margin-right: 4px;
    margin-top: -2px;
    transform: scaleX(-1);">
								<path id="loader" transform="translate(125, 125) scale(.84)" style="fill: #ffffff7d"/>
							</svg>
							<?php esc_html_e( 'Let\'s go!', 'geodirectory' ); ?></a>
					</p>
					<script>

//						jQuery('.gd-setup-welcome').collapse(toggle: false);
						var loader = document.getElementById('loader')
							, α = 360
							, π = Math.PI
							, t = 30;

						(function draw() {
							α--;
							α %= 360;
							var r = ( α * π / 180 )
								, x = Math.sin(r) * 125
								, y = Math.cos(r) * -125
								, mid = ( α > 180 ) ? 1 : 0
								, anim = 'M 0 0 v -125 A 125 125 1 '
								+ mid + ' 1 '
								+ x + ' '
								+ y + ' z';

							loader.setAttribute('d', anim);

							if(α==0){
								jQuery('.gd-setup-welcome').collapse('hide');
							}else{
								setTimeout(draw, t); // Redraw
							}
						})();

					</script>

				</div>
			</div>
		</div>
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

	public function get_ach(){
		$strings = array(
			'connect_title' => __("GeoDirectory - an AyeCode product!","geodirectory"),
			'connect_external'  => __( "Please confirm you wish to connect your site?","geodirectory" ),
			'connect'           => sprintf( __( "<strong>Have a license?</strong> Forget about entering license keys or downloading zip files, connect your site for instant access. %slearn more%s","geodirectory" ),"<a href='https://ayecode.io/introducing-ayecode-connect/' target='_blank'>","</a>" ),
			'connect_button'    => __("Connect Site","geodirectory"),
			'connecting_button'    => __("Connecting...","geodirectory"),
			'error_localhost'   => __( "This service will only work with a live domain, not a localhost.","geodirectory" ),
			'error'             => __( "Something went wrong, please refresh and try again.","geodirectory" ),
		);
		$ach = new AyeCode_Connect_Helper($strings,array('gd-addons'));

		return $ach;
	}

	public function setup_features() {
		global $aui_bs5;

		$bs_prefix = $aui_bs5 ? 'bs-' : '';

		include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' ); //for plugins_api..

		$recommend_wp_plugins = GeoDir_Admin_Addons::get_recommend_wp_plugins();
		$paid_types = GeoDir_Admin_Addons::get_wizard_paid_addons();

		$installed_text = "<i class=\"fas fa-check-circle\" aria-hidden=\"true\"></i> " . __( 'Installed', 'geodirectory' );
		echo "<input type='hidden' id='gd-installing-text' value='<i class=\"fas fa-sync fa-spin\" aria-hidden=\"true\"></i> " . __( 'Installing', 'geodirectory' ) . "' >";
		echo "<input type='hidden' id='gd-installed-text' value='$installed_text' >";
		?>
		<div class="text-center mb-5">
			<h1 class="h3"><?php _e("What Directory features do you need?","geodirectory");?></h1>
		</div>

		<div class="list-group mb-5" >
			<?php


			// recommended
			foreach ( $recommend_wp_plugins as $product ) {
				$nonce = wp_create_nonce( 'updates' );
				$title = esc_attr($product['name']);
				$slug = esc_attr($product['slug']);
				$file = esc_attr($product['file']);
				$selected = false;
				$info_url = admin_url( "plugin-install.php?gd_wizard_recommend=true&tab=plugin-information&plugin=" . $slug );
				$status = install_plugin_install_status( array( "slug" => $slug, "version" => "" ) );

				$plugin_status = isset( $status['status'] ) ? $status['status'] : '';
				$url           = isset( $status['url'] ) ? $status['url'] : '';

				$active = false;
				$active_badge = '';
				$activate_url = wp_nonce_url(admin_url('plugins.php?action=activate&plugin='.$file), 'activate-plugin_' . $file);
				$more_info = ' <a href="#" class="text-muted" onclick="aui_modal_iframe(\''.$title.'\',\''.$info_url.'\',\'\',true,\'\',\'modal-lg\')"><i class="fas fa-info-circle"></i></a>';

				if ( $plugin_status == 'install' ) {// required installation
					$checked        = "checked";
					$disabled       = "";
					$checkbox_class = " gd_install_plugins";
				} else {
					$active = is_plugin_active( $file );
					$checked        = "checked";
					$disabled       = $active ? "disabled" : '';
					$checkbox_class = $active ? "" : ' gd_install_plugins';
					$active_badge = $active ? '<span class="badge ' . ( $aui_bs5 ? 'rounded-pill bg-success' : 'badge-pill badge-success' ) . '">'.__("Active","geodirectory").'</span> ' : '';
				}
				?>
				<div class="list-group-item gd-addon list-group-item-action <?php echo $slug;?> <?php echo $selected ? 'active' : '';?>">
					<div class="d-flex w-100 justify-content-between">
						<div class="<?php echo ( $aui_bs5 ? 'form-check' : 'custom-control custom-checkbox' ); ?>">
							<input type="checkbox" class="<?php echo ( $aui_bs5 ? 'form-check-input' : 'custom-control-input' ); ?><?php echo $checkbox_class;?>" id="<?php echo $slug;?>" <?php echo $checked;?> <?php echo $disabled;?> data-status="<?php echo esc_attr($plugin_status);?>" data-slug="<?php echo esc_attr($slug);?>" data-activateurl="<?php echo esc_attr($activate_url);?>">
							<label class="<?php echo ( $aui_bs5 ? 'form-check-label' : 'custom-control-label' ); ?>" for="<?php echo $slug;?>"><?php echo $title.$more_info;?></label>
						</div>
						<small class="">
							<span class="badge <?php echo ( $aui_bs5 ? 'rounded-pill bg-secondary' : 'badge-pill badge-secondary' ); ?> gd-plugin-status d-none"><?php _e( "Installing", "geodirectory" ); ?></span>
							<?php
							echo $active_badge;

							if ( ! empty( $product['required'] ) ) {
								?>
								<span class="badge <?php echo ( $aui_bs5 ? 'rounded-pill bg-warning' : 'badge-pill badge-warning' ); ?>"><?php _e("Required","geodirectory");?></span>
								<?php
							}

							?>
							<span class="badge <?php echo ( $aui_bs5 ? 'rounded-pill bg-success' : 'badge-pill badge-success' ); ?>"><?php _e("Free","geodirectory");?></span>
						</small>
					</div>
					<small class="text-muted"><?php echo !empty( $product['desc'] ) ? esc_attr($product['desc']) : ''; ?></small>
				</div>
			<?php } ?>
		</div>
		<?php

		$ach = self::get_ach();

		// scripts
		$ach->script();

		$is_local = $ach->is_localhost();

		if ( ! $is_local ) {
			$connected = false;
			$all_licences    = get_option( "ayecode_connect_licences" );
			$actual_licences = get_option( "exup_keys" );

			$membership_status = false;
			if ( isset( $actual_licences['wpgeodirectory.com']->status ) && $actual_licences['wpgeodirectory.com']->status == 'active' ) {
				$membership_status = 'active';
			}
			if ( defined( 'AYECODE_CONNECT_VERSION' ) ) {
				global $ayecode_connect;

				if($ayecode_connect->is_registered()){
					$connected = true;
				}else{
					?>
					<div class="text-center pb-5">
						<button class="btn btn-primary" data-connecting="<?php echo esc_attr( $ach->strings['connecting_button'] ); ?>" onclick="ayecode_connect_helper(this);">
							<i class="fas fa-plug"></i> <?php _e( "Connect Site for more free and premium features", "geodirectory" ); ?>
						</button>
					</div>
					<?php
				}
			}
			?>

			<?php
			$blur_class = $connected ? '' : 'gd-blur';
			$disabled = $connected ? '' : 'disabled';
			?>

			<div class="" style="">
				<div class="list-group pb-4">

					<?php
					if($membership_status == 'active') {
						echo aui()->alert(array(
								'type'=> 'success',
								'content'=> __("You have a valid Membership!","geodirectory")
							)
						);
					}else {
						$info_url = 'https://wpgeodirectory.com/checkout/?edd_action=add_to_cart&download_id=66235&edd_options[price_id]=1';

						//@todo we can't iframe this until we update our PHP version ont he server so we can allow CORS cookies.
						//$buy_now = '<span class="badge badge-pill badge-primary" onclick="aui_modal_iframe(\''.__("Buy membership","geodirectory").'\',\''.$info_url.'\',\'\',true,\'\',\'modal-lg\')">'.__("Buy now","geodirectory").'</span>';
						$buy_now = '<a class="" href="'.$info_url.'" target="_blank"><span class="badge ' . ( $aui_bs5 ? 'rounded-pill bg-primary' : 'badge-pill badge-primary' ) . '" href="'.$info_url.'" target="_blank">'.__("Buy now","geodirectory").'</span></a>';
						?>
						<script>
							function gd_ayecode_connect_licences($input){
								jQuery.ajax({
									url: ajaxurl,
									type: 'POST',
									dataType: 'json',
									data: {
										action: 'ayecode_connect_licences',
										security: '<?php echo wp_create_nonce( 'ayecode-connect' );?>',
										state: 1
									},
									beforeSend: function() {
										jQuery($input).replaceWith('<div class="spinner-border spinner-border-sm" role="status"></div>');

//										jQuery($input).closest('li').find('.spinner-border').toggleClass('d-none');
									},
									success: function(data, textStatus, xhr) {
										location.reload();
									},
									error: function(xhr, textStatus, errorThrown) {
										alert(textStatus);
									}
								}); // end of ajax
							}
						</script>
						<span  class="list-group-item list-group-item-action bg-light border-primary ">
							<div class="d-flex w-100 justify-content-between <?php echo $blur_class; ?>">
								<div class="<?php echo ( $aui_bs5 ? 'form-check' : 'custom-control custom-checkbox' ); ?> c-pointer">
									<input type="checkbox" class="<?php echo ( $aui_bs5 ? 'form-check-input' : 'custom-control-input' ); ?>" id="membership" <?php echo $disabled; ?>>
									<label class="<?php echo ( $aui_bs5 ? 'form-check-label' : 'custom-control-label' ); ?>" for="membership"><?php _e( "Membership (includes all extensions on unlimited sites)", "geodirectory" ); ?> <small class="d-block text-info"><?php _e( "Recent purchase?", "geodirectory" ); ?> <span class="badge <?php echo ( $aui_bs5 ? 'rounded-pill bg-primary' : 'badge-pill badge-primary' ); ?> c-pointer" onclick="gd_ayecode_connect_licences(this);"><?php _e( "Refresh Licenses", "geodirectory" ); ?></span></small></label>
								</div>
								<small class="gd-price-year d-none"><?php echo $buy_now;?> <span class="badge <?php echo ( $aui_bs5 ? 'rounded-pill bg-secondary' : 'badge-pill badge-secondary' ); ?>">$199 / year</span></small>
								<small class="gd-price-month"><?php echo $buy_now;?> <span class="badge <?php echo ( $aui_bs5 ? 'rounded-pill bg-secondary' : 'badge-pill badge-secondary' ); ?>">$16.59 / month</span></small>
							</div>
						</span>
						<?php
					}

					if ( ! empty( $paid_types ) ) {
						$count = 1;

						foreach ( $paid_types as $product ) {
							// skip free
							if ( empty( $product->pricing->singlesite ) ) {
								continue;
							}

							$id         = absint( $product->info->id );
							$has_license = false;
							if ($id && !empty($all_licences) && !empty($all_licences['wpgeodirectory.com'][$id]->download_id) ) {
								$has_license = $all_licences['wpgeodirectory.com'][$id]->key;
							}
							$title         = esc_attr( $product->info->title );
							$slug          = esc_attr( $product->info->slug );
							$selected      = false;//!empty($type['selected']) ? true : false;
							$price_single  = absint( $product->pricing->singlesite );
							$price_monthly = round( $price_single / 12, 2 );

							$hidden_class = $count > 4 ? 'collapse gd-paid-product-collapse' : '';

							$url           = isset( $status['url'] ) ? $status['url'] : '';

							$nonce = wp_create_nonce( 'updates' );

							//=geodir_advance_search_filters/geodir_advance_search_filters.php&width=600&height=550&update_url=https://wpgeodirectory.com&item_id=65056&TB_iframe=true
							$info_url = admin_url( "plugin-install.php?gd_wizard_recommend=true&tab=plugin-information&plugin=" . $slug . "&update_url=https://wpgeodirectory.com&item_id=". $id );

							$status = GeoDir_Admin_Addons::install_plugin_install_status( $product );
							//print_r($status);

							$plugin_status = isset( $status['status'] ) ? $status['status'] : '';
							$url           = isset( $status['url'] ) ? $status['url'] : '';
							$file          = isset( $status['file'] ) ? $status['file'] : '';

							$nonce = wp_create_nonce( 'updates' );

							$active = false;
							$active_badge = '';
							$more_info = ' <a href="#" class="text-muted" onclick="aui_modal_iframe(\''.$title.'\',\''.$info_url.'\',\'\',true,\'\',\'modal-lg\')"><i class="fas fa-info-circle"></i></a>';

							$activate_url = wp_nonce_url(admin_url('plugins.php?action=activate&plugin='.$file), 'activate-plugin_' . $file);
							if ( $plugin_status == 'install' ) {// required installation
								$checked        = "checked";
								$disabled       = "";
								$checkbox_class = "gd_install_plugins";
							} else {
								$active = is_plugin_active( $file );
								$checked        = "checked";
								$disabled       = $active ? "disabled" : '';
								$checkbox_class = $active ? "" : 'gd_install_plugins';
								$active_badge = $active ? '<small><span class="badge ' . ( $aui_bs5 ? 'rounded-pill bg-success' : 'badge-pill badge-success' ) . '">'.__("Active","geodirectory").'</span></small> ' : '';
							}
							?>
							<div
							   class="list-group-item gd-addon list-group-item-action <?php echo $slug;?> <?php echo $hidden_class; ?> ">
								<div class="d-flex w-100 justify-content-between <?php echo $blur_class; ?>">
									<div class="<?php echo ( $aui_bs5 ? 'form-check' : 'custom-control custom-checkbox' ); ?> c-pointer">
										<input type="checkbox" class="<?php echo ( $aui_bs5 ? 'form-check-input' : 'custom-control-input' ); ?> <?php echo $checkbox_class;?>" id="<?php echo $slug; ?>"  <?php echo $disabled;?> data-status="<?php echo esc_attr($plugin_status);?>" data-slug="<?php echo esc_attr($slug);?>" data-activateurl="<?php echo esc_attr($activate_url);?>" data-id="<?php echo absint($id);?>" data-update_url="https://wpgeodirectory.com/" data-key="<?php echo esc_attr($has_license);?>">
										<label class="<?php echo ( $aui_bs5 ? 'form-check-label' : 'custom-control-label' ); ?>" for="<?php echo $slug; ?>"><?php echo $title.$more_info; ?></label>
									</div>

									<?php
									if($active_badge ){
										echo $active_badge ;
									}elseif( $membership_status == 'active' || $has_license) {
										?>
										<small class="gd-addon-valid">
											<span class="badge <?php echo ( $aui_bs5 ? 'rounded-pill bg-secondary' : 'badge-pill badge-secondary' ); ?> gd-plugin-status d-none"><?php _e( "Installing", "geodirectory" ); ?></span>
											<span class="badge <?php echo ( $aui_bs5 ? 'rounded-pill bg-success' : 'badge-pill badge-success' ); ?>"><?php _e( "Valid", "geodirectory" ); ?></span>
										</small>
										<?php
									}else{
									?>
									<small class="gd-price-year d-none"><span
											class="badge <?php echo ( $aui_bs5 ? 'rounded-pill bg-secondary' : 'badge-pill badge-secondary' ); ?>"><?php echo sprintf( __( '$%s / year', 'geodirectory' ), $price_single ); ?></span>
									</small>
									<small class="gd-price-month"><span
											class="badge <?php echo ( $aui_bs5 ? 'rounded-pill bg-secondary' : 'badge-pill badge-secondary' ); ?>"><?php echo sprintf( __( '$%s / month', 'geodirectory' ), $price_monthly ); ?></span>
									</small>
									<small class="gd-price-included d-none"><span
											class="badge <?php echo ( $aui_bs5 ? 'rounded-pill bg-secondary' : 'badge-pill badge-secondary' ); ?>"><?php _e( "Included", "geodirectory" ); ?></span>
									</small>
									<?php
									}
									?>
								</div>
							</div>
							<?php
							$count ++;
						}
						?>
						<a href="#" class="list-group-item gd-addon list-group-item-action gd-addons-show-more" data-<?php echo $bs_prefix;?>toggle="collapse" role="button" aria-expanded="false" data-<?php echo $bs_prefix;?>target=".gd-paid-product-collapse" onclick="jQuery(this).find('span').toggleClass('d-none');">
							<div class=" w-100  text-center">
								<span class="gd-show-more"><?php _e( 'Show more', 'geodirectory' ); ?> <i class="fas fa-angle-down"></i></span>
								<span class="gd-show-less d-none"><?php _e( 'Show less', 'geodirectory' ); ?> <i class="fas fa-angle-up"></i></span>
							</div>
						</a>
						<?php
					}
					?>
				</div>
				<?php
				if($membership_status != 'active') {
					?>
					<div class="text-centerx" onclick="gd_price_display()">
						<div class="<?php echo ( $aui_bs5 ? 'form-check form-switch' : 'custom-control custom-switch' ); ?> custom-switch-md ">
							<input type="checkbox" class="<?php echo ( $aui_bs5 ? 'form-check-input' : 'custom-control-input' ); ?> c-pointer" id="gd-price-show-month" checked>
							<label class="<?php echo ( $aui_bs5 ? 'form-check-label' : 'custom-control-label' ); ?> c-pointer" for="gd-price-show-month"><?php _e( 'Display monthly prices', 'geodirectory' ); ?></label>
						</div>
					</div>
					<?php
				}
				?>
			</div>
			<?php
		}
		?>

		<form method="post" >
			<?php
			//				echo aui()->alert(array(
			//						'type'=> 'info',
			//						'content'=> __("Open Street Maps will be used if no Google API key is added.","geodirectory")
			//					)
			//				);
			?>
			<div class="gd-setup-maps w-100 <?php // if($active_map=='osm') echo 'collapse';?>"></div>
			<p class="gd-setup-actions step text-right text-end mt-4">
				<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
				   class="btn btn-link text-muted gd-install-skip"><?php esc_html_e( 'Skip this step', 'geodirectory' ); ?></a>
				<?php wp_nonce_field( 'gd-setup' ); ?>
				<input type="submit" class="btn btn-primary gd-install-recommend"
				       value="<?php esc_attr_e( 'Install', 'geodirectory' ); ?>" name="install_recommend"
				       onclick="gd_wizard_install_plugins('<?php echo $nonce; ?>');return false;"/>
				<button type="submit" class="btn btn-primary button-next gd-continue-recommend" onclick="jQuery(this).html('<span class=\'spinner-border spinner-border-sm\' role=\'status\'></span> <?php esc_attr_e( 'Saving', 'geodirectory' ); ?>').addClass('disabled');"
				        value="<?php esc_attr_e( 'Continue', 'geodirectory' ); ?>" name="save_step" ><?php esc_attr_e( 'Continue', 'geodirectory' ); ?></button>
				<button class="btn btn-primary d-none gd-installing" type="button" disabled>
					<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
					<?php esc_attr_e( 'Installing...', 'geodirectory' ); ?>
				</button>
			</p>
		</form>

		<script>
			function gd_price_display(){


				if(jQuery('#membership').is(':checked')){

					if(jQuery('#gd-price-show-month').is(':checked')) {
						jQuery('.gd-price-year').addClass('d-none');
						jQuery('.gd-price-month').removeClass('d-none');
					}else{
						jQuery('.gd-price-year').removeClass('d-none');
						jQuery('.gd-price-month').addClass('d-none');
					}

					jQuery('.gd-addon .gd-price-year,.gd-addon .gd-price-month').addClass('d-none');
					jQuery('.gd-addon .gd-price-included').removeClass('d-none');


				}else{

					jQuery('.gd-addon .gd-price-included').addClass('d-none');

					if(jQuery('#gd-price-show-month').is(':checked')) {
						jQuery('.gd-price-year').addClass('d-none');
						jQuery('.gd-price-month').removeClass('d-none');
					}else{
						jQuery('.gd-price-year').removeClass('d-none');
						jQuery('.gd-price-month').addClass('d-none');
					}
				}



			}

			jQuery("input").change( function() {
				gd_price_display()
			});
		</script>
		<?php
	}

	public function setup_type() {
		global $aui_bs5;

		$bs_prefix = $aui_bs5 ? 'bs-' : '';

		$types = array(
			'standard_places'   => __("General Business","geodirectory"),
			'events'   => __("Events","geodirectory"),
			'property_sale'   => __("Property for Sale","geodirectory"),
			'property_rent'   => __("Property for Rent","geodirectory"),
			'classifieds'   => __("Classifieds","geodirectory"),
			'car_sales'   => __("Car Sales","geodirectory"),
			'jobs'   => __("Job Board","geodirectory"),
			'yoga_studios'   => __("Yoga Studios","geodirectory"),
			'restaurants'   => __("Restaurant Guide","geodirectory"),
			'doctors'   => __("Doctors Directory","geodirectory"),
			'accommodation'   => __("Accommodation","geodirectory"),
			'staff'   => __("Staff Directory","geodirectory"),
//			''   => __("","geodirectory"),
//			''   => __("","geodirectory"),
//			''   => __("","geodirectory"),
//			''   => __("","geodirectory"),
//			''   => __("","geodirectory"),
//			''   => __("","geodirectory"),
//			''   => __("","geodirectory"),
//			''   => __("","geodirectory"),
//			''   => __("","geodirectory"),
//			''   => __("","geodirectory"),
//			''   => __("","geodirectory"),
//			''   => __("","geodirectory"),
//			''   => __("","geodirectory"),
//			''   => __("","geodirectory"),
//			''   => __("","geodirectory"),
		);

		?>

		<script>
			function gd_setup_set_types($type){
				//jQuery($type).toggleClass('border-primary');//.find('input').click();
			}
		</script>

		<div class="text-center mb-4">
			<h1 class="h3"><?php _e("What Directory types are you setting up?","geodirectory");?></h1>
			<small class="text-muted"><?php _e("Select any that apply","geodirectory");?></small>
		</div>

		<div class="row row-cols-2 justify-content-end pb-4">
			<?php
			foreach ( $types as $key => $type ) {
				$selected = $key=='standard_places' ? true : false;

				$disabled = $key=='events' && !defined('GEODIR_EVENT_VERSION') ? 'disabled' : '';
				?>
				<div class="col">
					<div class="<?php echo ( $aui_bs5 ? 'form-check' : 'custom-control custom-radio' ); ?> border <?php if($selected)echo 'border-primary'; ?> rounded-sm rounded-1 px-3 pl-5 ps-5 py-2  mb-3 <?php if($disabled)echo 'bg-light'; ?>"
						<?php if(!$disabled){ echo 'onclick="gd_setup_set_types(this);"';}?>
						<?php if($disabled){ echo 'data-' . $bs_prefix . 'toggle="tooltip" data-placement="top" title="'. __("Enable our Events addon to be able to use events","geodirectory").'"'; ?>>
						<input <?php if($selected)echo 'checked' ?> type="radio" class="<?php echo ( $aui_bs5 ? 'form-check-input' : 'custom-control-input' ); ?>" name="<?php echo esc_attr($key);?>" id="<?php echo esc_attr($key);}?>"
							<?php echo $disabled;?> >
						<label class="<?php echo ( $aui_bs5 ? 'form-check-label' : 'custom-control-label' ); ?> c-pointer w-100" for="<?php echo esc_attr($key);?>" <?php if(!$disabled){ echo 'onclick="jQuery(this).parent().toggleClass(\'border-primary\');"';}?>><?php echo esc_attr($type);?></label>
					</div>
				</div>
				<?php
			}
			?>

		</div>


		<form method="post" >
			<?php
			//				echo aui()->alert(array(
			//						'type'=> 'info',
			//						'content'=> __("Open Street Maps will be used if no Google API key is added.","geodirectory")
			//					)
			//				);
			?>


			<div class="gd-setup-maps w-100 <?php // if($active_map=='osm') echo 'collapse';?>">


			</div>


			<p class="gd-setup-actions step text-right text-end mt-4">
				<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
				   class="btn btn-link text-muted"><?php esc_html_e( 'Skip this step', 'geodirectory' ); ?></a>
				<?php wp_nonce_field( 'gd-setup' ); ?>
				<button type="submit" class="btn btn-primary button-next" onclick="jQuery(this).html('<span class=\'spinner-border spinner-border-sm\' role=\'status\'></span> <?php esc_attr_e( 'Saving', 'geodirectory' ); ?>').addClass('disabled');"
				        value="<?php esc_attr_e( 'Continue', 'geodirectory' ); ?>" name="save_step" ><?php esc_attr_e( 'Continue', 'geodirectory' ); ?></button>
			</p>
		</form>
		<?php
	}

	/**
	 * Setup maps api.
	 *
	 * @since 2.0.0
	 */
	public function setup_maps() {
		global $aui_bs5;

		$active_class = "border border-primary border-width-4 shadow pointer";
		$active_map = 'osm';
		$maps_api = geodir_get_option( 'maps_api' );
		$maps_api_key = geodir_get_option( 'google_maps_api_key' );
		if ( ( $maps_api == 'auto' || $maps_api == 'google')  && $maps_api_key ) {
			$active_map = 'google';
		}
		?>
		<script>
			$temp_map_key = jQuery('#google_maps_api_key').val();
			function gd_setup_wiz_map_select($type) {
				if ($type == 'google') {
					jQuery('.gd-wiz-map-osm').removeClass('<?php echo $active_class;?>');
					jQuery('.gd-wiz-map-google').addClass('<?php echo $active_class;?>');
					jQuery('.gd-setup-maps').collapse('show');
					jQuery('#google_maps_api_key').val($temp_map_key);
				} else if ($type == 'osm') {
					jQuery('.gd-wiz-map-osm').addClass('<?php echo $active_class;?>');
					jQuery('.gd-wiz-map-google').removeClass('<?php echo $active_class;?>');
					jQuery('.gd-setup-maps').collapse('hide');
					$temp_map_key = jQuery('#google_maps_api_key').val();
					jQuery('#google_maps_api_key').val('');
				}
			}
		</script>

		<div class="row row-cols-2 justify-content-end pb-4">
			<div class="col">
				<div class="gd-wiz-map-google rounded overflow-hidden position-relative  hover-shadow <?php if($active_map=='google') echo $active_class;?>" onclick="gd_setup_wiz_map_select('google');">
					<img class="img-fluid hover-zoom c-pointer" src="<?php echo geodir_plugin_url() . '/assets/images/google-maps.jpg'; ?>" >
					<h5 class="ab-top-right"><span class="badge <?php echo ( $aui_bs5 ? 'bg-warning' : 'badge-warning' ); ?> text-dark shadow"><?php _e("Requires API key","geodirectory");?></span> <span class="badge <?php echo ( $aui_bs5 ? 'bg-success' : 'badge-success' ); ?> shadow">Free Quota</span></h5>

				</div>
				<h5 class="text-center pt-3"><?php _e("Google Maps","geodirectory");?></h5>
			</div>
			<div class="col">
				<div class="gd-wiz-map-osm rounded overflow-hidden position-relative  hover-shadow <?php if($active_map=='osm') echo $active_class;?>" onclick="gd_setup_wiz_map_select('osm');">
					<img class="img-fluid hover-zoom c-pointer" src="<?php echo geodir_plugin_url() . '/assets/images/osm.jpg'; ?>" >
					<h5 class="ab-top-right"><span class="badge <?php echo ( $aui_bs5 ? 'bg-success' : 'badge-success' ); ?> shadow">Free</span></h5>
				</div>
				<h5 class="text-center pt-3"><?php _e("Open Street Maps","geodirectory");?></h5>
			</div>
		</div>

<!--		<small class="text-center">--><?php //_e("* You can change maps later in settings.","geodirectory");?><!--</small>-->

		<form method="post" id="gd-wizard-save-map-key" >
			<?php
//				echo aui()->alert(array(
//						'type'=> 'info',
//						'content'=> __("Open Street Maps will be used if no Google API key is added.","geodirectory")
//					)
//				);
				?>


			<div class="gd-setup-maps w-100 collapse <?php if($active_map=='google') echo 'show';?>">

				<?php
				$settings   = array();
				$settings[] = GeoDir_Settings_General::get_maps_api_setting();
				$settings[] = GeoDir_Settings_General::get_map_language_setting();
				$api_arr    = GeoDir_Settings_General::get_google_maps_api_key_setting();

				$api_arr['name'] = '';
				$api_arr['desc'] = '';
				$api_arr['desc_tip'] = 0;
				$api_arr['placeholder'] = __("Enter your Google Maps API key","geodirectory");
//				print_r($api_arr);exit;
				// change the tooltip description/
//				$api_arr['desc'] = __( 'This is a requirement to use Google Maps. If you would prefer to use the Open Street Maps API then leave this blank.', 'geodirectory' );

				$settings[] = $api_arr;

				ob_start();
				GeoDir_Admin_Settings::output_fields( $settings );
				$settings_output = ob_get_clean();

				$settings_output = str_replace(array('regular-text geodir-select'),array('form-control w-100 mw-100'),$settings_output );
				echo $settings_output;
				?>

			</div>


			<p class="gd-setup-actions step text-right text-end mt-4">
				<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
				   class="btn btn-link text-muted"><?php esc_html_e( 'Skip this step', 'geodirectory' ); ?></a>
				<?php wp_nonce_field( 'gd-setup' ); ?>
				<button type="submit" class="btn btn-primary button-next submit-btn" onclick="jQuery(this).html('<span class=\'spinner-border spinner-border-sm\' role=\'status\'></span> <?php esc_attr_e( 'Saving', 'geodirectory' ); ?>').addClass('disabled');"
				   data-continue-text="<?php esc_attr_e( 'Continue', 'geodirectory' ); ?>"     value="<?php esc_attr_e( 'Continue', 'geodirectory' ); ?>" name="save_step" ><?php esc_attr_e( 'Continue', 'geodirectory' ); ?></button>
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


		echo aui()->alert(array(
				'type'=> 'info',
				'content'=> __("Drag the map or marker to the center of your default town/city.","geodirectory")
			)
		);
		?>

<!--		<h4 class="text-center">--><?php //_e( "Drag the map or marker to your default location", "geodirectory" );?><!--</h4>-->

		<form id='geodir-set-default-location' method="post">
			<?php
			$generalSettings = new GeoDir_Settings_General();
			$settings        = $generalSettings->get_settings( 'location' );

			// Change the description
			foreach ( $settings as $key => $setting ) {
				if ( $setting['type'] == 'title' || $setting['type'] == 'sectionend' ) {
					unset( $settings[ $key ] );
				}elseif($setting['id']=='multi_city'){
					$settings[ $key ]['desc'] = __( "Allow listings to be placed outside the default city?", "geodirectory" );
				}
			}
//			unset( $settings[0] );
//			unset( $settings[9] );
//			$settings[0]['title'] = '';
//			$settings[0]['desc']  = '';//__( 'Drag the map or the marker to set the city/town you wish to use as the default location.', 'geodirectory' );

//			print_r($settings);

			ob_start();
			GeoDir_Admin_Settings::output_fields( $settings );
			$settings_output = ob_get_clean();
//			echo $settings_output;
			$settings_output = str_replace(array('btn btn-primary'),array('btn btn-primary mb-4'),$settings_output );
			echo str_replace(array('regular-text','geodir-select','button-primary'),array('form-control w-100 mw-100','form-control w-100 mw-100','btn btn-primary btn-sm mb-4'),$settings_output );

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
						jQuery(".button-next").on("click",function () {
							if (default_location_city && default_location_city != jQuery("#default_location_city").val()) {
								return confirm("<?php _e( "Are you sure? This can break current listings.", "geodirectory" );?>");
							}
						});
					});
				</script>
				<?php
			}


			?>
			<div class="gd-setup-actions step text-right text-end d-flex justify-content-between mt-4">
				<div>
					<?php $generalSettings->output_toggle_advanced(); ?>
				</div>
				<div>
					<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
					   class="btn btn-link text-muted"><?php esc_html_e( 'Skip this step', 'geodirectory' ); ?></a>
					<?php wp_nonce_field( 'gd-setup' ); ?>
					<button type="submit" class="btn btn-primary button-next" onclick="jQuery(this).html('<span class=\'spinner-border spinner-border-sm\' role=\'status\'></span> <?php esc_attr_e( 'Saving', 'geodirectory' ); ?>').addClass('disabled');"
					        value="<?php esc_attr_e( 'Continue', 'geodirectory' ); ?>" name="save_step" ><?php esc_attr_e( 'Continue', 'geodirectory' ); ?></button>

				</div>
			</div>
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

	public function setup_features_save() {
		check_admin_referer( 'gd-setup' );

		do_action( 'geodir_setup_wizard_features_saved' );

		wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;
	}

	/**
	 * Dummy Data setup.
	 *
	 * @since 2.0.0
	 */
	public function setup_content() {
		global $aui_bs5;

		$bs_prefix = $aui_bs5 ? 'bs-' : '';

		$wizard_content = array(
			'dummy_data' => __( "Dummy Data", "geodirectory" ),
			'sidebars'   => __( "Sidebars", "geodirectory" ),
			'menus'   => __( "Menus", "geodirectory" ),
		);

		// If a block theme remove some parts that don't apply
		if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
			unset( $wizard_content['sidebars'] );
			unset( $wizard_content['menus'] );
		}

		$wizard_content = apply_filters( 'geodir_wizard_content', $wizard_content );

		$page_counts = wp_count_posts( 'page' );
		$active_tab = !empty($page_counts->publish) && $page_counts->publish < 10 ? 'full' : 'add';
		?>

		<div class="list-group list-group-horizontal-sm mb-4 text-center" id="gd-import-tabs" role="tablist">
			<a class="list-group-item w-50 <?php if($active_tab=='full'){echo 'active';} ?>" data-<?php echo $bs_prefix;?>toggle="tab" href="#gd-import-site" role="tab" aria-controls="gd-import-site"><i class="fas fa-cloud-download-alt"></i> <?php _e( "Import full demo site", "geodirectory" ); ?></a>
			<a class="list-group-item w-50 <?php if($active_tab=='add'){echo 'active';} ?>" data-<?php echo $bs_prefix;?>toggle="tab" href="#gd-import-bits" role="tab" aria-controls="gd-import-bits"><i class="fas fa-plus"></i> <?php _e( "Add to existing site", "geodirectory" ); ?></a>
		</div>

		<div class="tab-content" id="myTabContent">
			<div class="tab-pane fade <?php if($active_tab=='add'){echo 'show active';} ?>" id="gd-import-bits" role="tabpanel" aria-labelledby="home-tab">


				<form method="post">
					<?php
					foreach ( $wizard_content as $slug => $title ) {
						echo '<h2 class="gd-settings-title h3 mt-4"><a id="' . esc_attr( $slug ) . '"></a>' . esc_attr( $title ) . '</h2>' . " \n"; // line break adds a nice spacing
						echo do_action( "geodir_wizard_content_{$slug}" );
					}
					?>
				</form>


			</div>
			<div class="tab-pane fade <?php if($active_tab=='full'){echo 'show active';} ?>" id="gd-import-site" role="tabpanel" aria-labelledby="profile-tab">

				<?php
				$ach = self::get_ach();

				// scripts
				$ach->script();

				$is_local = $ach->is_localhost();

				if ( $is_local ) {
					echo aui()->alert(array(
							'type'=> 'danger',
							'content'=> __("This importer will not work on localhost","geodirectory")
						)
					);
				}else{
					if ( defined( 'AYECODE_CONNECT_VERSION' ) ) {
						global $ayecode_connect;


						if($ayecode_connect->is_registered()){
							$connected = true;

							if ( defined( 'AYECODE_CONNECT_PLUGIN_DIR' ) ) {
								require_once AYECODE_CONNECT_PLUGIN_DIR . '/includes/class-ayecode-demo-content.php';

								$demo_content = AyeCode_Demo_Content::instance();
								$demo_content->scripts();
								ob_start();
								$demo_content->settings_page(true);
								$content = ob_get_clean();

								echo str_replace(
									array(
										"card-title ",
										"card-footer ",
										"card ",
										),
									array(
										"card-title small ",
										"card-footer px-2 ",
										"card hover-shadow ",
										),
									$content
								);
							}else{
								echo aui()->alert(array(
										'type'=> 'danger',
										'content'=> __("Please Update AyeCode Connect Plugin to Continue.","geodirectory")
									)
								);
							}
						}else{
							?>
							<div class="text-center pb-5">
								<button class="btn btn-primary" data-connecting="<?php echo esc_attr( $ach->strings['connecting_button'] ); ?>" onclick="ayecode_connect_helper(this);">
									<i class="fas fa-plug"></i> <?php _e( "Connect Site for more free and premium features", "geodirectory" ); ?>
								</button>
							</div>
							<?php
						}
					}
				}
				?>

			</div>
		</div>


		<form method="post">
		<p class="gd-setup-actions step text-right text-end mt-4">
			<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
			   class="btn btn-link text-muted"><?php esc_html_e( 'Skip this step', 'geodirectory' ); ?></a>
			<?php wp_nonce_field( 'gd-setup' ); ?>
			<button type="submit" class="btn btn-primary button-next" onclick="jQuery(this).html('<span class=\'spinner-border spinner-border-sm\' role=\'status\'></span> <?php esc_attr_e( 'Saving', 'geodirectory' ); ?>').addClass('disabled');"
			        value="<?php esc_attr_e( 'Continue', 'geodirectory' ); ?>" name="save_step" ><?php esc_attr_e( 'Continue', 'geodirectory' ); ?></button>
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

				<h2 class="gd-settings-title h3 "><?php _e( "Recommend Plugins", "geodirectory" ); ?></h2>

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

			<p class="gd-setup-actions step text-right text-end mt-4">
				<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
				   class="btn btn-link text-muted"><?php esc_html_e( 'Skip this step', 'geodirectory' ); ?></a>
				<?php wp_nonce_field( 'gd-setup' ); ?>
				<input type="submit" class="btn btn-primary gd-install-recommend"
				       value="<?php esc_attr_e( 'Install', 'geodirectory' ); ?>" name="install_recommend"
				       onclick="gd_wizard_install_plugins('<?php echo $nonce; ?>');return false;"/>
				<button type="submit" class="btn btn-primary button-next gd-continue-recommend" onclick="jQuery(this).html('<span class=\'spinner-border spinner-border-sm\' role=\'status\'></span> <?php esc_attr_e( 'Saving', 'geodirectory' ); ?>').addClass('disabled');"
				        value="<?php esc_attr_e( 'Continue', 'geodirectory' ); ?>" name="save_step" ><?php esc_attr_e( 'Continue', 'geodirectory' ); ?></button>

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

		<div class="text-center mb-3">
			<h1 class="h2"><?php esc_html_e( 'Awesome, your directory is ready!', 'geodirectory' ); ?></h1>
			<div class="geodirectory-message geodirectory-tracker">
				<p><?php _e( 'Thank you for using GeoDirectory!', 'geodirectory' ); ?> 😃</p>
			</div>

		</div>

		<?php if ( 'unknown' === geodir_get_option( 'usage_tracking', 'unknown' ) || '' === geodir_get_option( 'usage_tracking' ) ) { ?>
			<div class="geodirectory-message geodirectory-tracker mb-3">
				<p><?php printf( __( 'Want to help make GeoDirectory even more awesome? Allow GeoDirectory to collect non-sensitive diagnostic data and usage information. %1$sFind out more%2$s.', 'geodirectory' ), '<a href="https://wpgeodirectory.com/usage-tracking/" target="_blank">', '</a>' ); ?></p>
				<p class="">
					<a class="btn btn-primary btn-sm"
					   href="<?php echo esc_url( wp_nonce_url( remove_query_arg( 'gd_tracker_optout', add_query_arg( 'gd_tracker_optin', 'true' ) ), 'gd_tracker_optin', 'gd_tracker_nonce' ) ); ?>"><?php esc_html_e( 'Allow', 'geodirectory' ); ?></a>
					<a class="btn btn-link btn-sm"
					   href="<?php echo esc_url( wp_nonce_url( remove_query_arg( 'gd_tracker_optin', add_query_arg( 'gd_tracker_optout', 'true' ) ), 'gd_tracker_optout', 'gd_tracker_nonce' ) ); ?>"><?php esc_html_e( 'No thanks', 'geodirectory' ); ?></a>
				</p>
			</div>
		<?php } ?>

		<div class="gd-setup-next-steps">
			<div class="gd-setup-next-steps-first mb-4">
				<h2 class="h3"><?php esc_html_e( 'Next steps', 'geodirectory' ); ?></h2>
				<ul>
					<li class="setup-listing"><a class="btn btn-primary btn-sm"
					                             href="<?php echo esc_url( admin_url( 'post-new.php?post_type=gd_place' ) ); ?>"><?php esc_html_e( 'Create your first listing!', 'geodirectory' ); ?></a>
					</li>
				</ul>
			</div>
			<div class="gd-setup-next-steps-last">
				<h2 class="h3"><?php _e( 'Learn more', 'geodirectory' ); ?></h2>
				<div>
					<a
						class="btn btn-sm btn-outline-primary gd-getting-started"
						href="https://wpgeodirectory.com/documentation/article/category/getting-started/?utm_source=setupwizard&utm_medium=product&utm_content=getting-started&utm_campaign=geodirectoryplugin"
						target="_blank"><?php esc_html_e( 'Getting started guide', 'geodirectory' ); ?></a>
					<a
						class="btn btn-sm btn-outline-primary gd-newsletter"
						href="https://wpgeodirectory.com/newsletter-signup/?utm_source=setupwizard&utm_medium=product&utm_content=newsletter&utm_campaign=geodirectoryplugin"
						target="_blank"><?php esc_html_e( 'Get GeoDirectory advice in your inbox', 'geodirectory' ); ?></a>
					<a
						class="btn btn-sm btn-outline-primary gd-get-help"
						href="https://wpgeodirectory.com/support/?utm_source=setupwizard&utm_medium=product&utm_content=docs&utm_campaign=geodirectoryplugin"
						target="_blank"><?php esc_html_e( 'Have questions? Get help.', 'geodirectory' ); ?></a>
				</div>
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
		geodir_update_option( 'wizard_complete', time() );

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

		ob_start();
		GeoDir_Admin_Settings::output_fields( $settings );
		$settings_output = ob_get_clean();
		echo str_replace(
			array(
				'gd-dummy-table',
				'regular-text',
				'geodir-select',
				'gd-dummy-table ',
				'card p-0 ',
				'card-body',
				' shadow-sm',
				'accordion',
			),
			array(
				'gd-dummy-table gd-dummy-data',
				'form-control w-100 mw-100',
				'form-control form-control-sm w-100 mw-100',
				'gd-dummy-table table table-borderless table-sm ',
				'p-0 ',
				'',
				'',
				'accordion mx-n1',
			),
			$settings_output
		);

	}

	/**
	 * Output the setup wizard content sidebars settings.
	 */
	public static function content_sidebars() {
		global $aui_bs5;

		$bs_select_class = $aui_bs5 ? 'form-select form-select-sm' : ' form-control form-control-sm';
		$gd_sidebar_top = get_theme_support( 'geodirectory-sidebar-top' );
		if ( isset( $gd_sidebar_top[0] ) ) {
			?>
			<div class="form-table gd-dummy-table gd-dummy-widgets">
				<div class="d-flex justify-content-between">
					<div class="pb-1 flex-fill">
						<div class="<?php echo ( $aui_bs5 ? 'mb-3' : 'form-group' ); ?>">
							<label for="geodir-wizard-widgets-top"><?php _e( "Select the theme top sidebar", "geodirectory" ); ?></label>
							<select id='geodir-wizard-widgets-top' class="geodir-select <?php echo $bs_select_class; ?> w-100 mw-100 ">
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
						</div>
					</div>
					<div class="pl-2 ps-2 pb-1">
						<div class="<?php echo ( $aui_bs5 ? 'mb-3' : 'form-group' ); ?> text-right text-end">
							<label
								for="geodir-wizard-widgets-top-submit" class="invisible"><?php _e( "Action", "geodirectory" ); ?></label>
							<input type="button" id="geodir-wizard-widgets-top-submit"
							       value="<?php _e( "Insert widgets", "geodirectory" ); ?>"
							       class="btn btn-primary btn-sm geodir_dummy_button d-block"
							       onclick="gd_wizard_add_widgets_top('<?php echo wp_create_nonce( "geodir-wizard-widgets-top" ); ?>');return false;">
						</div>
					</div>
				</div>
				<div class="geodir-wizard-widgets-top-result w-100"></div>
			</div>



			<?php
		}
		?>

		<div class="form-table gd-dummy-table gd-dummy-widgets">
			<div class="d-flex justify-content-between">
				<div class="pb-1 flex-fill">
					<div class="<?php echo ( $aui_bs5 ? 'mb-3' : 'form-group' ); ?>">
						<label for="geodir-wizard-widgets"><?php _e( "Select the theme sidebar", "geodirectory" ); ?></label>
						<select id='geodir-wizard-widgets' class="geodir-select <?php echo $bs_select_class; ?> w-100 mw-100">
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
					</div>
				</div>
				<div class="<?php echo ( $aui_bs5 ? 'ps-2' : 'pl-2' ); ?> pb-1">
					<div class="<?php echo ( $aui_bs5 ? 'mb-3' : 'form-group' ); ?> text-right text-end">
						<label
							for="geodir-wizard-widgets-top-submit" class="invisible"><?php _e( "Action", "geodirectory" ); ?></label>
						<input type="button" id="geodir-wizard-widgets-submit" value="<?php _e( "Insert widgets", "geodirectory" ); ?>"
						       class="btn btn-primary btn-sm geodir_dummy_button d-block"
						       onclick="gd_wizard_add_widgets('<?php echo wp_create_nonce( "geodir-wizard-widgets" ); ?>');return false;">
					</div>
				</div>
			</div>
			<div class="geodir-wizard-widgets-result w-100"></div>
		</div>
		<?php
	}

	/**
	 * Output the setup wizard content menu settings.
	 */
	public static function content_menus() {
		global $aui_bs5;
		$bs_select_class = $aui_bs5 ? 'form-select form-select-sm' : ' form-control form-control-sm';
		?>
		<div class="form-table gd-dummy-table gd-dummy-widgets gd-dummy-posts">
			<div class="d-flex justify-content-between">
				<div class="pb-1 flex-fill">
					<div class="<?php echo ( $aui_bs5 ? 'mb-3' : 'form-group' ); ?>">
						<label for="geodir-wizard-menus"><?php _e( "Select the theme main menu", "geodirectory" ); ?></label>
						<?php

						$set_menus = get_nav_menu_locations();
						$set_menus = array_filter( $set_menus );
						//echo '##';
						//print_r($set_menus);

						if ( ! empty( $set_menus ) ) {
							//echo '##1';
							echo "<select id='geodir-wizard-menu-id' data-type='add' class='geodir-select $bs_select_class w-100 mw-100' >";

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
								echo "<select id='geodir-wizard-menu-location' data-type='create' class='geodir-select $bs_select_class w-100 mw-100' >";

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
						}
						//					echo geodir_notification( array( 'geodir-wizard-menu-result' => '' ) );
						?>
					</div>
				</div>
				<div class="pl-2 ps-2 pb-1">
					<div class="<?php echo ( $aui_bs5 ? 'mb-3' : 'form-group' ); ?> text-right text-end">
						<label for="geodir-wizard-menu-submit" class="invisible"><?php _e( "Action", "geodirectory" ); ?></label>
						<input type="button" id="geodir-wizard-menu-submit" value="<?php _e( "Insert menu items", "geodirectory" ); ?>" class="btn btn-primary btn-sm geodir_dummy_button d-block" onclick="gd_wizard_setup_menu('<?php echo wp_create_nonce( "geodir-wizard-setup-menu" ); ?>');return false;">
					</div>
				</div>
			</div>
			<div class="geodir-wizard-menu-result w-100"></div>
		</div>
		<?php
	}

}

new GeoDir_Admin_Setup_Wizard();
