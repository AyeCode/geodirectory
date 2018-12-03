<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_Font_Awesome_Settings' ) ) {


	/**
	 * A Class to be able to change settings for Font Awesome.
	 *
	 * Class WP_Font_Awesome_Settings
	 * @ver 1.0.0
	 * @todo decide how to implement textdomain
	 */
	class WP_Font_Awesome_Settings {



		/**
		 * Class version version.
		 *
		 * @var string
		 */
		public $version = '0.0.1-dev';

		public $latest = "5.5.0";

		public $name = 'Font Awesome';

		/**
		 * Holds the values to be used in the fields callbacks
		 */
		private $settings;

		/**
		 * WP_Font_Awesome_Settings instance.
		 *
		 * @access private
		 * @since  1.0.0
		 * @var    WP_Font_Awesome_Settings The one true WP_Font_Awesome_Settings
		 */
		private static $instance = null;


		/**
		 * Main WP_Font_Awesome_Settings Instance.
		 *
		 * Ensures only one instance of WP_Font_Awesome_Settings is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @static
		 * @return WP_Font_Awesome_Settings - Main instance.
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WP_Font_Awesome_Settings ) ) {
				self::$instance = new WP_Font_Awesome_Settings;

				add_action( 'init', array( self::$instance, 'init' ) ); // set settings

				if(is_admin()){
					add_action( 'admin_menu', array( self::$instance, 'menu_item' ) );
					add_action( 'admin_init', array( self::$instance, 'register_settings' ) );
				}

				do_action( 'wp_font_awesome_settings_loaded' );
			}

			return self::$instance;
		}

		public function enqueue_style(){
			// build url
			$url = $this->get_url();

			wp_deregister_style( 'font-awesome' ); // deregister in case its already there
			wp_register_style( 'font-awesome', $url,array(), null  );
			wp_enqueue_style( 'font-awesome' );

			if($this->settings['shims']){
				$url = $this->get_url(true);
				wp_deregister_style( 'font-awesome-shims' ); // deregister in case its already there
				wp_register_style( 'font-awesome-shims', $url, array(), null );
				wp_enqueue_style( 'font-awesome-shims' );
			}
		}

		public function enqueue_scripts(){
			// build url
			$url = $this->get_url();

			wp_deregister_script( 'font-awesome' ); // deregister in case its already there
			wp_register_script( 'font-awesome', $url,array(), null );
			wp_enqueue_script( 'font-awesome' );

			if($this->settings['shims']){
				$url = $this->get_url(true);
				wp_deregister_script( 'font-awesome-shims' ); // deregister in case its already there
				wp_register_script( 'font-awesome-shims', $url, array(), null );
				wp_enqueue_script( 'font-awesome-shims' );
			}
		}

		public function get_url($shims = false){
			$script = $shims ? 'v4-shims' : 'all';
			$type = $this->settings['type'];
			$version = $this->settings['version'];

			$url = "https://use.fontawesome.com/releases/"; // CDN
			$url .= $type=='css' ? 'css/' : 'js/'; // type
			$url .= !empty($version) ? $version.'/' : $this->latest.'/'; // version
			$url .= $type=='css' ? $script.'.css' : $script.'.js'; // type
			$url .= "?wpfas=true"; // set our var so our version is not removed

			return $url;
		}

		public function init(){
			$this->settings =$this->get_settings();

			if($this->settings['type']=='CSS'){

				if($this->settings['enqueue'] == '' || $this->settings['frontend']){
					add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_style'), 5000 );//echo '###';exit;
				}

				if($this->settings['enqueue'] == '' || $this->settings['backend']){
					add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_style'), 5000 );
				}

			}else{

				if($this->settings['enqueue'] == '' || $this->settings['frontend']){
					add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts'), 5000 );//echo '###';exit;
				}

				if($this->settings['enqueue'] == '' || $this->settings['backend']){
					add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts'), 5000 );
				}
			}

			// remove font awesome if set to do so
			if($this->settings['dequeue']=='1'){
				add_action( 'clean_url', array( $this, 'remove_font_awesome'), 5000, 3 );
			}

		}

		public function remove_font_awesome($url, $original_url, $_context){

			if ($_context=='display' &&  strstr( $url, "fontawesome" ) !== false || strstr( $url, "font-awesome" ) !== false ) {// it's a font-awesome-url

				if(strstr( $url, "wpfas=true" ) !== false){
					if($this->settings['type']=='JS'){
						if($this->settings['js-pseudo']){
							$url .= "' data-search-pseudo-elements defer='defer";
						}else{
							$url .= "' defer='defer";
						}
					}
				}
				else{
					$url = ''; // removing the url removes the file
				}

			}

			return $url;
		}


		public function register_settings() {
			register_setting( 'wp-font-awesome-settings', 'wp-font-awesome-settings' );
			register_setting( 'wp-font-awesome-settings', 'some_other_option' );
			register_setting( 'wp-font-awesome-settings', 'option_etc' );
		}

		public function menu_item(){
			add_options_page( $this->name, $this->name, 'manage_options', 'wp-font-awesome-settings', array($this,'settings_page') );
		}

		public function get_settings(){

			$db_settings = get_option( 'wp-font-awesome-settings' );

			$defaults = array(
				'type'  => 'CSS',
				'version'  => '', // latest
				'enqueue'  => '', // front and backend
				'shims'  => '1', // default on for now, maybe change to off in 2020
				'js-pseudo'  => '0',
				'dequeue'  => '0',
			);

			$settings = wp_parse_args($db_settings,$defaults);

			return $this->settings = apply_filters('wp-font-awesome-settings',$settings,$db_settings,$defaults);
		}


		public function settings_page() {
			if ( !current_user_can( 'manage_options' ) )  {
				wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
			}
			?>
			<div class="wrap">
				<h1><?php echo $this->name; ?></h1>
				<form method="post" action="options.php">
					<?php
					settings_fields( 'wp-font-awesome-settings' );
					do_settings_sections( 'wp-font-awesome-settings' );
					?>
					<table class="form-table">
						<tr valign="top">
							<th scope="row"><label for="wpfas-type"><?php _e('Type');?></label></th>
							<td>
								<select name="wp-font-awesome-settings[type]" id="wpfas-type">
									<option value="CSS" <?php selected( $this->settings['type'], 'CSS' ); ?>><?php _e('CSS (default)');?></option>
									<option value="JS" <?php selected( $this->settings['type'], 'JS' ); ?>>JS</option>
								</select>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row"><label for="wpfas-version"><?php _e('Version');?></label></th>
							<td>
								<select name="wp-font-awesome-settings[version]" id="wpfas-version">
									<option value="" <?php selected( $this->settings['version'], '' ); ?>><?php _e('Latest (default)');?></option>
									<option value="5.5.0" <?php selected( $this->settings['version'], '5.5.0' ); ?>>5.5.0</option>
									<option value="5.4.0" <?php selected( $this->settings['version'], '5.4.0' ); ?>>5.4.0</option>
									<option value="5.3.0" <?php selected( $this->settings['version'], '5.3.0' ); ?>>5.3.0</option>
									<option value="5.2.0" <?php selected( $this->settings['version'], '5.2.0' ); ?>>5.2.0</option>
									<option value="5.1.0" <?php selected( $this->settings['version'], '5.1.0' ); ?>>5.1.0</option>
									<option value="4.7.0" <?php selected( $this->settings['version'], '4.7.0' ); ?>>4.7.1 (CSS only)</option>
								</select>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row"><label for="wpfas-enqueue"><?php _e('Enqueue');?></label></th>
							<td>
								<select name="wp-font-awesome-settings[enqueue]" id="wpfas-enqueue">
									<option value="" <?php selected( $this->settings['enqueue'], '' ); ?>><?php _e('Frontend + Backend (default)');?></option>
									<option value="frontend" <?php selected( $this->settings['enqueue'], 'frontend' ); ?>><?php _e('Frontend');?></option>
									<option value="backend" <?php selected( $this->settings['enqueue'], 'backend' ); ?>><?php _e('Backend');?></option>
								</select>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row"><label for="wpfas-shims"><?php _e('Enable v4 shims compatibility');?></label></th>
							<td>
								<input type="hidden" name="wp-font-awesome-settings[shims]" value="0" />
								<input type="checkbox" name="wp-font-awesome-settings[shims]" value="1" <?php checked( $this->settings['shims'], '1' ); ?> id="wpfas-shims" />
								<span><?php _e('This enables v4 classes to work with v5, sort of like a band-aid until everyone has updated everything to v5.');?></span>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row"><label for="wpfas-js-pseudo"><?php _e('Enable JS pseudo elements (not recommended)');?></label></th>
							<td>
								<input type="hidden" name="wp-font-awesome-settings[js-pseudo]" value="0" />
								<input type="checkbox" name="wp-font-awesome-settings[js-pseudo]" value="1" <?php checked( $this->settings['js-pseudo'], '1' ); ?> id="wpfas-js-pseudo" />
								<span><?php _e('Used only with the JS version, this will make pseudo-elements work but can be CPU intensive on some sites.');?></span>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row"><label for="wpfas-dequeue"><?php _e('Dequeue');?></label></th>
							<td>
								<input type="hidden" name="wp-font-awesome-settings[dequeue]" value="0" />
								<input type="checkbox" name="wp-font-awesome-settings[dequeue]" value="1" <?php checked( $this->settings['dequeue'], '1' ); ?> id="wpfas-dequeue" />
								<span><?php _e('This will try to dequeue any other Font Awesome versions loaded by other sources if they are added with `font-awesome` or `fontawesome` in the name.');?></span>
							</td>
						</tr>


					</table>
					<?php
					submit_button();
					?>
				</form>
			</div>

			<?php
		}


	}
	WP_Font_Awesome_Settings::instance();
}

