<?php
/**
 * Addons Page
 *
 *
 * @author   AyeCode
 * @category Admin
 * @package  GeoDirectory/Admin
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
			'recommended_plugins' => __("Recommended plugins", "geodirectory"),
			'membership' => __("Membership", "geodirectory"),
		);



		return $tabs;
	}

	/**
	 * Get sections for the addons screen
	 *
	 * @return array of objects
	 */
	public static function get_sections() {

		return array(); //@todo we prob don't need these yet.

		if ( false === ( $sections = get_transient( 'geodir_addons_sections' ) ) ) {
			$raw_sections = wp_safe_remote_get( '#url', array( 'user-agent' => 'GeoDirectory Addons Page' ) );
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
		$section_data = new stdClass();

		//delete_transient( 'gd_addons_section_' . $section_id );

		if($section_id=='recommended_plugins'){
			$section_data->products = self::get_recommend_wp_plugins_edd_formatted();
		}
		elseif ( ! empty( $section ) ) {
			if ( false === ( $section_data = get_transient( 'gd_addons_section_' . $section_id ) ) ) { //@todo restore after testing
//			if ( 1==1) {

				$query_args = array( 'category' => $section_id, 'number' => 100, 'orderby' => 'sales', 'order' => 'desc');
				$query_args = apply_filters('wpeu_edd_api_query_args',$query_args,$api_url,$section_id);

				$raw_section = wp_safe_remote_get( esc_url_raw( add_query_arg($query_args ,$api_url) ), array( 'user-agent' => 'GeoDirectory Addons Page','timeout'     => 15, ) );

				if ( ! is_wp_error( $raw_section ) ) {
					$section_data = json_decode( wp_remote_retrieve_body( $raw_section ) );

					if ( ! empty( $section_data->products ) ) {
						set_transient( 'gd_addons_section_' . $section_id, $section_data, DAY_IN_SECONDS );
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
	public static function is_plugin_installed( $id, $addon = '' ){
		$all_plugins = get_plugins();

		//print_r($all_plugins);exit;

		$installed = false;

		foreach($all_plugins as $p_slug => $plugin ){

			if( isset($plugin['Update ID']) && $id == $plugin['Update ID']){
				$installed = true;
			}elseif(!empty($addon)){

			}

		}

		return $installed;
	}

	public static function install_plugin_install_status($addon){

		// Default to a "new" plugin
		$status = 'install';
		$url = isset($addon->info->link) ? $addon->info->link : false;
		$file = false;
		$version = '';


		// url


		$slug = isset($addon->info->slug) ? $addon->info->slug : '';
		if(!empty($addon->licensing->edd_slug)){$slug = $addon->licensing->edd_slug;}
		$id = !empty($addon->info->id) ? absint($addon->info->id) : '';
		$version = isset($addon->licensing->version) ? $addon->licensing->version : '';

		// check if we are looking for beta versions
		if(geodir_get_option('admin_enable_beta', 1) && !empty($addon->licensing->beta_version)){
			$version = $addon->licensing->beta_version;
		}

		// get the slug

		$all_plugins = get_plugins();
		foreach($all_plugins as $p_slug => $plugin ){

			if( $id && isset($plugin['Update ID']) && $id == $plugin['Update ID']){
				$status = 'installed';
				$file = $p_slug;break;
			}elseif(!empty($addon->licensing->edd_slug)){
				if (strpos($p_slug, $addon->licensing->edd_slug.'/') === 0) {
					$status = 'installed';
					$file = $p_slug;break;
				}
			}
		}


		// if no file then try to guess it
		if ( ! $file && isset($addon->licensing->edd_slug)) {
			$file = esc_attr($addon->licensing->edd_slug)."/".esc_attr($addon->licensing->edd_slug).".php";
		}



		return compact( 'status', 'url', 'version', 'file' );
	}

	/**
	 * Check if a theme is installed.
	 *
	 * @param $id
	 *
	 * @return bool
	 */
	public static function is_theme_installed( $addon ){
		$all_themes = wp_get_themes();

		$slug = isset($addon->info->slug) ? $addon->info->slug : '';
		if(!empty($addon->licensing->edd_slug)){$slug = $addon->licensing->edd_slug;}


		foreach($all_themes as $key => $theme ){
			if($slug == $key){
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if a theme is active.
	 *
	 * @param $addon
	 *
	 * @return bool
	 */
	public static function is_theme_active( $addon ){
		$theme = wp_get_theme();

		//manuall checks
		if($addon->info->title =="Whoop!"){
			$addon->info->title = "Whoop";
		}


		if($addon->info->title == $theme->get( 'Name' )){
			return true;
		}

		return false;
	}

	/**
	 * Get theme activation url.
	 *
	 * @param $addon
	 *
	 * @return bool
	 */
	public static function get_theme_activation_url( $addon ){
		$themes = wp_prepare_themes_for_js();

		//manuall checks
		if($addon->info->title =="Whoop!"){
			$addon->info->title = "Whoop";
		}


		foreach($themes as $theme){
			if($addon->info->title == $theme['name']){
				return $theme['actions']['activate'];
			}
		}

		return false;
	}

	/**
	 * Get theme install url.
	 *
	 * @param $addon
	 *
	 * @return bool
	 */
	public static function get_theme_install_url( $slug ){

		$install_url = add_query_arg( array(
			'action' => 'install-theme',
			'theme'  => urlencode( $slug ),
		), admin_url( 'update.php' ) );
		$install_url = wp_nonce_url( $install_url, 'install-theme_' . $slug );

		return $install_url;
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
//		$button_text = __('Free','geodirectory');
//		$licensing = false;
//		$installed = false;
//		$price = '';
//		$license = '';
//		$slug = '';
//		$url = isset($addon->info->link) ? $addon->info->link : '';
//		$class = 'button-primary';
//		$install_status = 'get';
//		$onclick = '';

		$wp_org_themes = array('supreme-directory','directory-starter','whoop');

		$button_args = array(
			'type' => $current_tab,
			'id' => isset($addon->info->id) ? absint($addon->info->id) : '',
			'title' => isset($addon->info->title) ? $addon->info->title : '',
			'button_text' => __('Free','geodirectory'),
			'price_text' => __('Free','geodirectory'),
			'link' => isset($addon->info->link) ? $addon->info->link : '', // link to product
			'url' => isset($addon->info->link) ? $addon->info->link : '', // button url
			'class' => 'button-primary',
			'install_status' => 'get',
			'installed' => false,
			'price' => '',
			'licensing' => isset($addon->licensing->enabled) && $addon->licensing->enabled ? true : false,
			'license' => isset($addon->licensing->license) && $addon->licensing->license ? $addon->licensing->license : '',
			'onclick' => '',
			'slug' => isset($addon->info->slug) ? $addon->info->slug : '',
			'active' => false,
			'file' => '',
			'update_url' => '',
		);

		if($current_tab == 'addons' && isset($addon->info->id) && $addon->info->id){
			include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' ); //for plugins_api..
			if(!empty($addon->licensing->edd_slug)){$button_args['slug'] = $addon->licensing->edd_slug;}
			$status = self::install_plugin_install_status($addon);
			$button_args['file'] = isset($status['file']) ? $status['file'] : '';
			if(isset($status['status'])){$button_args['install_status'] = $status['status'];}
			$button_args['update_url'] = "https://wpgeodirectory.com";
		}elseif($current_tab == 'themes' && isset($addon->info->id) && $addon->info->id) {
			if(!empty($addon->licensing->edd_slug)){$button_args['slug'] = $addon->licensing->edd_slug;}
			$button_args['installed'] = self::is_theme_installed($addon);
			if(!in_array($button_args['slug'],$wp_org_themes)){
				$button_args['update_url'] = "https://wpgeodirectory.com";
			}
		}elseif($current_tab == 'recommended_plugins' && isset($addon->info->slug) && $addon->info->slug){
			include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' ); //for plugins_api..
			$status = install_plugin_install_status(array("slug"=>$button_args['slug'],"version"=>""));
			$button_args['install_status'] = isset($status['status']) ? $status['status'] : 'install';
			$button_args['file'] = isset($status['file']) ? $status['file'] : '';
		}

		// set price
		if(isset($addon->pricing) && !empty($addon->pricing)){
			if(is_object($addon->pricing)){
				$prices = (Array)$addon->pricing;
				$button_args['price'] = reset($prices);
			}elseif(isset($addon->pricing)){
				$button_args['price'] = $addon->pricing;
			}
		}

		// set price text
		if( $button_args['price'] && $button_args['price'] != '0.00' ){
			$button_args['price_text'] = sprintf( __('From: $%d', 'geodirectory'), $button_args['price']);
		}


		// set if installed
		if(in_array($button_args['install_status'], array('installed','latest_installed','update_available','newer_installed'))){
			$button_args['installed'] = true;
		}

//		print_r($button_args);
		// set if active
		if($button_args['installed'] && ($button_args['file'] || $button_args['type'] == 'themes')){
			if($button_args['type'] != 'themes'){
				$button_args['active'] = is_plugin_active($button_args['file']);
			}else{
				$button_args['active'] = self::is_theme_active($addon);
			}
		}

		// set button text and class
		if($button_args['active']){
			$button_args['button_text'] = __('Active','geodirectory');
			$button_args['class'] = ' button-secondary disabled ';
		}elseif($button_args['installed']){
			$button_args['button_text'] = __('Activate','geodirectory');

			if($button_args['type'] != 'themes'){
				if ( current_user_can( 'manage_options' ) ) {
					$button_args['url'] = wp_nonce_url(admin_url('plugins.php?action=activate&plugin='.$button_args['file']), 'activate-plugin_' . $button_args['file']);
				}else{
					$button_args['url'] = '#';
				}
			}else{
				if ( current_user_can( 'switch_themes' ) ) {
					$button_args['url'] = self::get_theme_activation_url($addon);
				}else{
					$button_args['url'] = '#';
				}
			}

		}else{
			if($button_args['type'] == 'recommended_plugins'){
				$button_args['button_text'] = __('Install','geodirectory');
				$button_args['onclick'] = 'gd_recommended_install_plugin(this,"'.$button_args['slug'].'","'.wp_create_nonce( 'updates' ).'");return false;';
			}else{
				$button_args['button_text'] = __('Get it','geodirectory');

				if($button_args['type'] == 'themes' && in_array($button_args['slug'],$wp_org_themes) ){
					$button_args['button_text'] = __('Install','geodirectory');
					$button_args['url'] = self::get_theme_install_url($button_args['slug']);
					$button_args['onclick'] = 'gd_set_button_installing(this);';
				}

			}
		}


		// filter the button arguments
		$button_args = apply_filters('edd_api_button_args',$button_args);




		// set price text
		if(isset($button_args['price_text'])){
			?>
			<a
				target="_blank"
				class="addons-price-text"
				href="<?php echo esc_url( $button_args['link'] ); ?>">
				<?php echo esc_html( $button_args['price_text'] ); ?>
			</a>
			<?php
		}


		$target = '';
		if ( ! empty( $button_args['url'] ) ) {
			$target = strpos($button_args['url'], get_site_url()) !== false ? '' : ' target="_blank" ';
		}

		?>
		<a
			data-licence="<?php echo esc_attr($button_args['license']);?>"
			data-licensing="<?php echo $button_args['licensing'] ? 1 : 0;?>"
			data-title="<?php echo esc_attr($button_args['title']);?>"
			data-type="<?php echo esc_attr($button_args['type']);?>"
			data-text-error-message="<?php _e('Something went wrong!','geodirectory');?>"
			data-text-activate="<?php _e('Activate','geodirectory');?>"
			data-text-activating="<?php _e('Activating','geodirectory');?>"
			data-text-deactivate="<?php _e('Deactivate','geodirectory');?>"
			data-text-installed="<?php _e('Installed','geodirectory');?>"
			data-text-install="<?php _e('Install','geodirectory');?>"
			data-text-installing="<?php _e('Installing','geodirectory');?>"
			data-text-error="<?php _e('Error','geodirectory');?>"
			<?php if(!empty($button_args['onclick'])){echo " onclick='".$button_args['onclick']."' ";}?>
			<?php echo $target;?>
			class="addons-button  <?php echo esc_attr( $button_args['class'] ); ?>"
			href="<?php echo esc_url( $button_args['url'] ); ?>">
			<?php echo esc_html( $button_args['button_text'] ); ?>
		</a>
		<?php


	}


	/**
	 * Handles output of the addons page in admin.
	 */
	public static function output() {
		add_thickbox();
		$tabs            = self::get_tabs();
		$sections        = self::get_sections();
		$theme           = wp_get_theme();
		$section_keys    = array_keys( $sections );
		$current_section = isset( $_GET['section'] ) ? sanitize_text_field( $_GET['section'] ) : current( $section_keys );
		$current_tab     = empty( $_GET['tab'] ) ? 'addons' : sanitize_title( $_GET['tab'] );
		include_once( dirname( __FILE__ ) . '/views/html-admin-page-addons.php' );
	}

	/**
	 * A list of recommended wp.org plugins.
	 * @return array
	 */
	public static function get_recommend_wp_plugins(){
		$plugins = array(
			'ayecode-connect' => array(
				'url'   => 'https://wordpress.org/plugins/ayecode-connect/',
				'slug'   => 'ayecode-connect',
				'name'   => 'AyeCode Connect',
				'file'  => 'ayecode-connect/ayecode-connect.php',
				'desc'   => __( 'Support & documentation right from your dashboard, easily install any purchased add-ons without a zip file or license key.', 'geodirectory' ),
			),
			'ninja-forms' => array(
				'url'   => 'https://wordpress.org/plugins/ninja-forms/',
				'slug'   => 'ninja-forms',
				'name'   => 'Ninja Forms',
				'file'  => 'ninja-forms/ninja-forms.php',
				'desc'   => __('Setup forms such as contact or booking forms for your listings.','geodirectory'),
			),
			'userswp' => array(
				'url'   => 'https://wordpress.org/plugins/userswp/',
				'slug'   => 'userswp',
				'name'   => 'UsersWP',
				'file'  => 'userswp/userswp.php',
				'desc'   => __('Allow frontend user login and registration as well as have slick profile pages.','geodirectory'),
			),
		);


		/**
		 * A collection of WP plugins to recommend.
		 *
		 * @var array $plugins An array containing all the plugins.
		 */
		return apply_filters('geodir_recommend_wp_plugins', $plugins );
	}

	/**
	 * Format the recommended list of wp.org plugins for our extensions section output.
	 *
	 * @return array
	 */
	public static function get_recommend_wp_plugins_edd_formatted(){
		$formatted = array();
		$plugins = self::get_recommend_wp_plugins();

		foreach($plugins as $plugin){
			$product = new stdClass();
			$product->info = new stdClass();
			$product->info->id = '';
			$product->info->slug = isset($plugin['slug']) ? $plugin['slug'] : '';
			$product->info->title = isset($plugin['name']) ? $plugin['name'] : '';
			$product->info->excerpt = isset($plugin['desc']) ? $plugin['desc'] : '';
			$product->info->link = isset($plugin['url']) ? $plugin['url'] : '';
			$product->info->thumbnail = isset($plugin['thumbnail']) ? $plugin['thumbnail'] : "https://ps.w.org/".$plugin['slug']."/assets/banner-772x250.png";
			$formatted[] = $product;
		}

		return $formatted;
	}

	public static function get_wizard_paid_addons(){
		$addons = self::get_section_data( 'addons' );

		return $addons;
	}
}
