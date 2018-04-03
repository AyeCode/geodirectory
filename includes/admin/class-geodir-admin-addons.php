<?php
/**
 * Addons Page
 * 
 *
 * @author   AyeCode
 * @category Admin
 * @package  GeoDirectoru/Admin
 * @version  2.0.0
 * @info     Derived from GeoDir_Admin_Addons
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GeoDir_Admin_Addons Class.
 */
class GeoDir_Admin_Addons {


	/**
	 * Get the extensions page tabs.
	 *
	 * @return array of tabs.
	 */
	public static function get_tabs(){
		$tabs = array(
			'addons' => __("Addons", "geodirectory"),
			'themes' => __("Themes", "geodirectory"),
			' recommended_plugins' => __("Recommended plugins", "geodirectory"),
		);

		return $tabs;
	}

	/**
	 * Get featured for the addons screen
	 *
	 * @return array of objects
	 */
	public static function get_featured() {
		if ( false === ( $featured = get_transient( 'geodir_addons_featured' ) ) ) {
			$raw_featured = wp_safe_remote_get( 'https://d3t0oesq8995hv.cloudfront.net/add-ons/featured.json', array( 'user-agent' => 'GeoDirectory Addons Page' ) );
			if ( ! is_wp_error( $raw_featured ) ) {
				$featured = json_decode( wp_remote_retrieve_body( $raw_featured ) );
				if ( $featured ) {
					set_transient( 'geodir_addons_featured', $featured, WEEK_IN_SECONDS );
				}
			}
		}

		if ( is_object( $featured ) ) {
			self::output_featured_sections( $featured->sections );
			return $featured;
		}
	}

	/**
	 * Get sections for the addons screen
	 *
	 * @return array of objects
	 */
	public static function get_sections() {

		return array(); //@todo we prob don't need these yet.

		if ( false === ( $sections = get_transient( 'geodir_addons_sections' ) ) ) {
			$raw_sections = wp_safe_remote_get( 'https://d3t0oesq8995hv.cloudfront.net/addon-sections.json', array( 'user-agent' => 'GeoDirectory Addons Page' ) );
			if ( ! is_wp_error( $raw_sections ) ) {
				$sections = json_decode( wp_remote_retrieve_body( $raw_sections ) );

				if ( $sections ) {
					set_transient( 'geodir_addons_sections', $sections, WEEK_IN_SECONDS );
				}
			}
		}

		$addon_sections = array();

		if ( $sections ) {
			foreach ( $sections as $sections_id => $section ) {
				if ( empty( $sections_id ) ) {
					continue;
				}
				$addon_sections[ $sections_id ]           = new stdClass;
				$addon_sections[ $sections_id ]->title    = geodir_clean( $section->title );
				$addon_sections[ $sections_id ]->endpoint = geodir_clean( $section->endpoint );
			}
		}



		return apply_filters( 'geodir_addons_sections', $addon_sections );
	}

	/**
	 * Get section for the addons screen.
	 *
	 * @param  string $section_id
	 *
	 * @return object|bool
	 */
	public static function get_tab( $tab_id ) {
		$tabs = self::get_tabs();
		if ( isset( $tabs[ $tab_id ] ) ) {
			return $tabs[ $tab_id ] ;
		}
		return false;
	}

	/**
	 * Get section for the addons screen.
	 *
	 * @param  string $section_id
	 *
	 * @return object|bool
	 */
	public static function get_section( $section_id ) {
		$sections = self::get_sections();
		if ( isset( $sections[ $section_id ] ) ) {
			return $sections[ $section_id ];
		}
		return false;
	}

	/**
	 * Get section content for the addons screen.
	 *
	 * @param  string $section_id
	 *
	 * @return array
	 */
	public static function get_section_data( $section_id ) {
		$section      = self::get_tab( $section_id );
		$api_url = "https://wpgeodirectory.com/edd-api/v2/products/";
		$section_data = '';

		if ( ! empty( $section ) ) {
			if ( false === ( $section_data = get_transient( 'gd_addons_section_' . $section_id ) ) ) { //@todo restore after testing
			//if ( 1==1) {
				$raw_section = wp_safe_remote_get( esc_url_raw( add_query_arg( array( 'category' => $section_id, 'number' => 100),$api_url) ), array( 'user-agent' => 'GeoDirectory Addons Page' ) );

				if ( ! is_wp_error( $raw_section ) ) {
					$section_data = json_decode( wp_remote_retrieve_body( $raw_section ) );

					if ( ! empty( $section_data->products ) ) {
						set_transient( 'gd_addons_section_' . $section_id, $section_data, WEEK_IN_SECONDS );
					}
				}
			}
		}

		//print_r($section_data);
		$products = isset($section_data->products) ? $section_data->products : '';

		return apply_filters( 'geodir_addons_section_data', $products, $section_id );
	}


	/**
	 * Check if a plugin is installed (only works if WPEU is installed and active)
	 *
	 * @param $id
	 *
	 * @return bool
	 */
	public static function is_plugin_installed( $id ){
		$all_plugins = get_plugins();

		foreach($all_plugins as $plugin ){
			if(!isset($plugin['Update ID'])){
				return false;
			}else{
				if($id == $plugin['Update ID']){
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Check if a plugin is installed (only works if WPEU is installed and active)
	 *
	 * @param $id
	 *
	 * @return bool
	 */
	public static function is_theme_installed( $addon ){
		$all_themes = wp_get_themes();

		//manuall checks
		if($addon->info->title =="Whoop!"){
			$addon->info->title = "Whoop";
		}

		foreach($all_themes as $theme ){
//			print_r($theme);
//			echo '####'.$addon->info->title;
			if($addon->info->title == $theme->get( 'Name' )){
				return true;
			}
		}


		//print_r($all_themes);


		return false;
	}



	/**
	 * Outputs a button.
	 *
	 * @param string $url
	 * @param string $text
	 * @param string $theme
	 * @param string $plugin
	 */
	public static function output_button( $addon ) {
		$current_tab     = empty( $_GET['tab'] ) ? 'addons' : sanitize_title( $_GET['tab'] );
		$button_text = __('Free','geodirectory');
		$url = isset($addon->info->link) ? $addon->info->link : '';
		$class = 'button-primary';
		$installed = false;

		if($current_tab == 'addons' && isset($addon->info->id) && $addon->info->id){
			$installed = self::is_plugin_installed($addon->info->id);
		}elseif($current_tab == 'themes' && isset($addon->info->id) && $addon->info->id) {
//			print_r($addon);
			$installed = self::is_theme_installed($addon);
		}


		//print_r($addon);

		if(isset($addon->info->id) && $addon->info->id && $installed ){
			$button_text = __('Installed','geodirectory');
			$class = ' button-secondary disabled';
		}elseif(isset($addon->pricing) && !empty($addon->pricing)){
			if(is_object($addon->pricing)){
				$prices = (Array)$addon->pricing;
				$price = reset($prices);
				if($price!='0.00'){
					$button_text = sprintf( __('From: $%d', 'geodirectory'), $price);
				}
			}else{
				$button_text = sprintf( __('From: $%d', 'geodirectory'), $addon->pricing);
			}
		}

		?>
		<a
			class="addons-button  <?php echo esc_attr( $class ); ?>"
			href="<?php echo esc_url( $url ); ?>">
			<?php echo esc_html( $button_text ); ?>
		</a>
		<?php
	}


	/**
	 * Handles output of the addons page in admin.
	 */
	public static function output() {
		$tabs            = self::get_tabs();
		$sections        = self::get_sections();
		$theme           = wp_get_theme();
		$section_keys    = array_keys( $sections );
		$current_section = isset( $_GET['section'] ) ? sanitize_text_field( $_GET['section'] ) : current( $section_keys );
		$current_tab     = empty( $_GET['tab'] ) ? 'addons' : sanitize_title( $_GET['tab'] );
		include_once( dirname( __FILE__ ) . '/views/html-admin-page-addons.php' );
	}

	public static function get_recommend_wp_plugins(){
		$plugins = array(
			'ninja-forms' => array(
				'url'   => 'https://wordpress.org/plugins/ninja-forms/',
				'slug'   => 'ninja-forms',
				'name'   => 'Ninja Forms',
				'desc'   => __('Setup forms such as contact or booking forms for your listings.','geodirectory'),
			),
			'userswp' => array(
				'url'   => 'https://wordpress.org/plugins/userswp/',
				'slug'   => 'userswp',
				'name'   => 'UsersWP',
				'desc'   => __('Allow frontend user login and registration as well as slick profile pages.','geodirectory'),
			),
			// just testing script for below plugins
//			'ewww-image-optimizer' => array(
//				'url'   => 'https://wordpress.org/plugins/ewww-image-optimizer/',
//				'slug'   => 'ewww-image-optimizer',
//				'name'   => 'EWWW Image Optimizer',
//				'desc'   => __('testing','geodirectory'),
//			),
//			'hide-admin-bar' => array(
//				'url'   => 'https://wordpress.org/plugins/hide-admin-bar/',
//				'slug'   => 'hide-admin-bar',
//				'name'   => 'Hide admin bar',
//				'desc'   => __('testing','geodirectory'),
//			),
		);

		return $plugins;
	}
}
